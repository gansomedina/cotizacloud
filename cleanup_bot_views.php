<?php
/**
 * cleanup_bot_views.php — Elimina vistas de bots/crawlers de _sliced_log
 *
 * Uso:
 *   php cleanup_bot_views.php          → modo preview (no modifica nada)
 *   php cleanup_bot_views.php --apply  → aplica los cambios
 *
 * Subir a la raíz de WordPress (junto a wp-load.php) y ejecutar.
 */
require_once __DIR__ . '/wp-load.php';
global $wpdb;

$apply = in_array('--apply', $argv ?? []);

// ══════════════════════════════════════════════════════
//  IPs conocidas de bots/crawlers (prefijos)
// ══════════════════════════════════════════════════════
$bot_prefixes = [
    // Bingbot / Microsoft
    '40.77.167.', '40.77.168.', '157.55.39.', '52.167.144.', '207.46.13.',
    // Googlebot
    '66.249.', '64.233.',
    // OVH crawlers
    '54.39.', '142.44.', '148.113.', '51.222.', '51.161.', '167.114.',
    // SEMrush
    '185.191.171.',
    // Hetzner
    '136.243.',
    // Ahrefs
    '54.36.',
    // Apple
    '17.241.', '17.22.',
    // Otros crawlers comunes
    '57.141.', '85.208.96.', '15.235.',
    // Facebook preview
    '66.220.149.',
];

// ══════════════════════════════════════════════════════
//  Regla de burst: vistas sin IP (N/A) en ráfaga
//  Si un quote tiene >5 vistas sin IP en un día → son bots
// ══════════════════════════════════════════════════════

$stats = [
    'quotes_revisadas'  => 0,
    'quotes_modificadas' => 0,
    'vistas_bot_ip'     => 0,
    'vistas_sin_ip'     => 0,
    'vistas_burst'      => 0,
    'vistas_conservadas' => 0,
    'total_eliminadas'   => 0,
];

// Traer todos los _sliced_log
$logs = $wpdb->get_results(
    "SELECT pm.meta_id, pm.post_id, pm.meta_value
     FROM {$wpdb->postmeta} pm
     INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
     WHERE pm.meta_key = '_sliced_log'
     AND p.post_type = 'sliced_quote'",
    ARRAY_A
);

echo "=== Limpieza de vistas de bots en _sliced_log ===\n";
echo "Modo: " . ($apply ? "APLICAR CAMBIOS" : "PREVIEW (sin cambios)") . "\n";
echo "Quotes con _sliced_log: " . count($logs) . "\n\n";

foreach ($logs as $row) {
    $meta_id = (int)$row['meta_id'];
    $post_id = (int)$row['post_id'];
    $log = @unserialize($row['meta_value']);
    if (!is_array($log)) continue;

    $stats['quotes_revisadas']++;
    $original_count = count($log);
    $cleaned = [];
    $removed = 0;

    foreach ($log as $ts => $entry) {
        if (!is_array($entry)) {
            $cleaned[$ts] = $entry;
            continue;
        }

        $type = $entry['type'] ?? '';

        // Solo filtrar quote_viewed
        if ($type !== 'quote_viewed') {
            $cleaned[$ts] = $entry;
            continue;
        }

        $ip = trim($entry['ip'] ?? '');
        $by = (int)($entry['by'] ?? 0);

        // Solo filtrar guests (by=0), no usuarios internos (ya filtrados por ontime.php)
        if ($by !== 0) {
            $cleaned[$ts] = $entry;
            continue;
        }

        // Regla 1: IP conocida de bot
        $is_bot_ip = false;
        if ($ip !== '') {
            foreach ($bot_prefixes as $prefix) {
                if (str_starts_with($ip, $prefix)) {
                    $is_bot_ip = true;
                    break;
                }
            }
        }

        if ($is_bot_ip) {
            $stats['vistas_bot_ip']++;
            $removed++;
            continue; // No agregar a $cleaned
        }

        // Regla 2: Sin IP = sospechoso (Sliced no capturó IP)
        if ($ip === '') {
            $stats['vistas_sin_ip']++;
            $removed++;
            continue;
        }

        // Vista legítima (guest con IP no-bot)
        $cleaned[$ts] = $entry;
        $stats['vistas_conservadas']++;
    }

    // Regla 3: Detección de burst — si quedan >10 vistas guest del mismo día, eliminar
    $by_day = [];
    foreach ($cleaned as $ts => $entry) {
        if (!is_array($entry)) continue;
        if (($entry['type'] ?? '') !== 'quote_viewed') continue;
        if ((int)($entry['by'] ?? 0) !== 0) continue;
        $day = date('Y-m-d', (int)$ts);
        $by_day[$day][] = $ts;
    }
    foreach ($by_day as $day => $timestamps) {
        if (count($timestamps) > 10) {
            // Burst: >10 vistas guest en un día = bot con IP nueva
            foreach ($timestamps as $ts) {
                unset($cleaned[$ts]);
                $stats['vistas_burst']++;
                $removed++;
            }
        }
    }

    if ($removed === 0) continue;

    $stats['quotes_modificadas']++;
    $stats['total_eliminadas'] += $removed;

    $new_count = count($cleaned);
    echo "  Quote #{$post_id}: {$original_count} → {$new_count} entries (-{$removed} bots)\n";

    if ($apply) {
        $new_value = serialize($cleaned);
        $wpdb->update(
            $wpdb->postmeta,
            ['meta_value' => $new_value],
            ['meta_id' => $meta_id]
        );
    }
}

echo "\n=== Resumen ===\n";
echo "Quotes revisadas:   {$stats['quotes_revisadas']}\n";
echo "Quotes modificadas: {$stats['quotes_modificadas']}\n";
echo "Eliminadas por IP bot:  {$stats['vistas_bot_ip']}\n";
echo "Eliminadas sin IP:      {$stats['vistas_sin_ip']}\n";
echo "Eliminadas por burst:   {$stats['vistas_burst']}\n";
echo "Total eliminadas:       {$stats['total_eliminadas']}\n";
echo "Vistas conservadas:     {$stats['vistas_conservadas']}\n";

if (!$apply) {
    echo "\n>>> Para aplicar: php cleanup_bot_views.php --apply\n";
} else {
    echo "\n>>> Cambios aplicados. Limpia cache del radar:\n";
    echo "    wp option delete apc_radar_stats\n";
}
