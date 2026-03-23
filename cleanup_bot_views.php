<?php
/**
 * cleanup_bot_views.php — Elimina vistas de bots/crawlers de _sliced_log
 *
 * Abrir en navegador:
 *   tudominio.com/cleanup_bot_views.php?key=limpiar2026   → preview
 *   tudominio.com/cleanup_bot_views.php?key=limpiar2026&apply=1  → aplicar
 *
 * IMPORTANTE: Borrar este archivo del servidor después de usarlo.
 */
require_once __DIR__ . '/wp-load.php';
global $wpdb;

// ═══ Protección: solo con clave correcta ═══
$secret = 'limpiar2026';
if (($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    die('Acceso denegado.');
}

// Detectar modo (web o CLI)
$is_web = php_sapi_name() !== 'cli';
if ($is_web) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Cleanup Bot Views</title>'
       . '<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;line-height:1.6}'
       . '.removed{color:#ff6b6b}.kept{color:#51cf66}.title{color:#74c0fc;font-size:18px}'
       . '.btn{display:inline-block;margin-top:20px;padding:10px 20px;background:#e03131;color:#fff;text-decoration:none;border-radius:5px;font-weight:bold}'
       . '.btn:hover{background:#c92a2a}.summary{background:#2d2d44;padding:15px;border-radius:8px;margin-top:15px}'
       . '</style></head><body>';
}

$apply = isset($_GET['apply']) || in_array('--apply', $argv ?? []);
$nl = $is_web ? '<br>' : "\n";
$bold = function($t) use ($is_web) { return $is_web ? "<b>$t</b>" : $t; };

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

echo $is_web ? '<div class="title">' : '';
echo "=== Limpieza de vistas de bots en _sliced_log ==={$nl}";
echo "Modo: " . ($apply ? "⚡ APLICAR CAMBIOS" : "👁 PREVIEW (sin cambios)") . $nl;
echo "Quotes con _sliced_log: " . count($logs) . $nl . $nl;
echo $is_web ? '</div>' : '';

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

        if ($type !== 'quote_viewed') {
            $cleaned[$ts] = $entry;
            continue;
        }

        $ip = trim($entry['ip'] ?? '');
        $by = (int)($entry['by'] ?? 0);

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
            continue;
        }

        // Regla 2: Sin IP
        if ($ip === '') {
            $stats['vistas_sin_ip']++;
            $removed++;
            continue;
        }

        // Vista legítima
        $cleaned[$ts] = $entry;
        $stats['vistas_conservadas']++;
    }

    // Regla 3: Detección de burst
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
    $line = "Quote #{$post_id}: {$original_count} → {$new_count} entries ";
    $line .= $is_web
        ? "<span class=\"removed\">(-{$removed} bots)</span>"
        : "(-{$removed} bots)";
    echo $line . $nl;

    if ($apply) {
        $new_value = serialize($cleaned);
        $wpdb->update(
            $wpdb->postmeta,
            ['meta_value' => $new_value],
            ['meta_id' => $meta_id]
        );
    }
}

echo $is_web ? '<div class="summary">' : '';
echo "{$nl}=== Resumen ==={$nl}";
echo "Quotes revisadas:   {$stats['quotes_revisadas']}{$nl}";
echo "Quotes modificadas: {$stats['quotes_modificadas']}{$nl}";
echo $is_web ? "<span class=\"removed\">" : '';
echo "Eliminadas por IP bot:  {$stats['vistas_bot_ip']}{$nl}";
echo "Eliminadas sin IP:      {$stats['vistas_sin_ip']}{$nl}";
echo "Eliminadas por burst:   {$stats['vistas_burst']}{$nl}";
echo "Total eliminadas:       {$stats['total_eliminadas']}{$nl}";
echo $is_web ? "</span>" : '';
echo $is_web ? "<span class=\"kept\">" : '';
echo "Vistas conservadas:     {$stats['vistas_conservadas']}{$nl}";
echo $is_web ? "</span>" : '';
echo $is_web ? '</div>' : '';

if (!$apply && $stats['total_eliminadas'] > 0) {
    $apply_url = '?key=' . urlencode($secret) . '&apply=1';
    if ($is_web) {
        echo "<a class=\"btn\" href=\"{$apply_url}\" onclick=\"return confirm('¿Seguro? Esto modificará la base de datos.')\">⚡ APLICAR CAMBIOS</a>";
    } else {
        echo "{$nl}>>> Para aplicar: php cleanup_bot_views.php --apply{$nl}";
    }
} elseif ($apply) {
    echo "{$nl}✅ Cambios aplicados exitosamente.{$nl}";
}

if ($is_web) echo '</body></html>';
