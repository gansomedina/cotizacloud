<?php
// ============================================================
//  CotizaApp — tools/import_radar_json.php
//  Importa internal_ips.json e internal_visitors.json del
//  sistema On Time (WordPress) a las tablas MySQL de cotiza.cloud.
//
//  USO: php import_radar_json.php --empresa=1 [--ips=../internal_ips.json] [--visitors=../internal_visitors.json]
//  O desde el servidor: https://empresa.cotiza.cloud/tools/import_radar_json.php?key=SECRET&empresa_id=1
//
//  SEGURIDAD: Proteger con .htaccess o borrar después de usar.
// ============================================================

// ── Modo CLI vs Web ──────────────────────────────────────────
$cli = (php_sapi_name() === 'cli');

if (!$cli) {
    // Protección básica en web — cambiar este key o bloquear con .htaccess
    $secret = getenv('IMPORT_KEY') ?: 'cambiar_este_secreto_antes_de_usar';
    if (($_GET['key'] ?? '') !== $secret) {
        http_response_code(403);
        die('Acceso denegado. Provee ?key=SECRET en la URL.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

// ── Parámetros ───────────────────────────────────────────────
if ($cli) {
    $opts = getopt('', ['empresa:', 'ips:', 'visitors:', 'dry-run']);
    $empresa_id  = (int)($opts['empresa']   ?? 0);
    $ips_file    = $opts['ips']      ?? __DIR__ . '/../../../internal_ips.json';
    $vis_file    = $opts['visitors'] ?? __DIR__ . '/../../../internal_visitors.json';
    $dry_run     = isset($opts['dry-run']);
} else {
    $empresa_id = (int)($_GET['empresa_id'] ?? 0);
    $ips_file   = __DIR__ . '/../../../internal_ips.json';
    $vis_file   = __DIR__ . '/../../../internal_visitors.json';
    $dry_run    = isset($_GET['dry_run']);
}

if (!$empresa_id) {
    die("Error: empresa_id requerido (--empresa=N o ?empresa_id=N)\n");
}

// ── Bootstrap de la app ──────────────────────────────────────
define('COTIZAAPP', true);
require_once __DIR__ . '/../core/bootstrap.php';

$log = [];
$now = time();

// ════════════════════════════════════════════════════════════
//  1. IMPORTAR IPs INTERNAS
// ════════════════════════════════════════════════════════════
$log[] = "=== IMPORTANDO IPs INTERNAS ===";

if (!file_exists($ips_file)) {
    $log[] = "⚠️  Archivo no encontrado: $ips_file — saltando.";
} else {
    $ips_data = json_decode(file_get_contents($ips_file), true);
    if (!is_array($ips_data)) {
        $log[] = "❌ Error al parsear $ips_file";
    } else {
        $imported = 0;
        $skipped  = 0;

        foreach ($ips_data as $ip => $ts) {
            $ip = trim((string)$ip);
            $ts = (int)$ts;

            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $log[] = "  ⚠️  IP inválida ignorada: $ip";
                $skipped++;
                continue;
            }

            // TTL del JSON original: 7 días
            // Si el timestamp es muy viejo, no importar (ya caducó)
            if ($ts > 0 && ($now - $ts) > (7 * 86400)) {
                $skipped++;
                continue;
            }

            if (!$dry_run) {
                try {
                    DB::execute(
                        "INSERT INTO radar_ips_internas (empresa_id, ip, aprendida_ts, fuente)
                         VALUES (?, ?, ?, 'json_import')
                         ON DUPLICATE KEY UPDATE aprendida_ts=GREATEST(aprendida_ts, VALUES(aprendida_ts)), fuente='json_import'",
                        [$empresa_id, $ip, $ts ?: $now]
                    );
                    $imported++;
                } catch (Throwable $e) {
                    $log[] = "  ❌ Error al importar IP $ip: " . $e->getMessage();
                }
            } else {
                $log[] = "  [DRY] IP: $ip (ts=$ts)";
                $imported++;
            }
        }

        $log[] = "  ✅ IPs importadas: $imported | Saltadas (caducadas/inválidas): $skipped";
    }
}

// ════════════════════════════════════════════════════════════
//  2. IMPORTAR VISITORS INTERNOS
// ════════════════════════════════════════════════════════════
$log[] = "";
$log[] = "=== IMPORTANDO VISITORS INTERNOS ===";

if (!file_exists($vis_file)) {
    $log[] = "⚠️  Archivo no encontrado: $vis_file — saltando.";
} else {
    $vis_data = json_decode(file_get_contents($vis_file), true);
    if (!is_array($vis_data)) {
        $log[] = "❌ Error al parsear $vis_file";
    } else {
        $imported = 0;
        $skipped  = 0;

        foreach ($vis_data as $visitor_id => $row) {
            $visitor_id = trim((string)$visitor_id);
            if (strlen($visitor_id) < 8 || strlen($visitor_id) > 64) {
                $skipped++;
                continue;
            }

            $source     = (string)($row['source']     ?? 'json_import');
            $user_id    = (int)($row['user_id']       ?? 0) ?: null;
            $ip         = (string)($row['ip']         ?? '');
            $label      = substr((string)($row['label'] ?? ''), 0, 255);
            $first_seen = (int)($row['first_seen']    ?? $now);
            $last_seen  = (int)($row['last_seen']     ?? $now);

            // TTL del JSON original: 365 días
            if ($last_seen > 0 && ($now - $last_seen) > (365 * 86400)) {
                $skipped++;
                continue;
            }

            if (!$dry_run) {
                try {
                    DB::execute(
                        "INSERT INTO radar_visitors_internos (empresa_id, visitor_id, source, usuario_id, ip, label, first_seen, last_seen)
                         VALUES (?,?,?,?,?,?,?,?)
                         ON DUPLICATE KEY UPDATE
                           last_seen  = GREATEST(last_seen, VALUES(last_seen)),
                           source     = CASE WHEN source='internal_ip' THEN VALUES(source) ELSE source END,
                           label      = CASE WHEN VALUES(label) != '' THEN VALUES(label) ELSE label END",
                        [$empresa_id, substr($visitor_id,0,64), $source, $user_id, $ip, $label, $first_seen, $last_seen]
                    );
                    $imported++;
                    $log[] = "  ✅ Visitor: " . substr($visitor_id, 0, 18) . "... | $source | $ip";
                } catch (Throwable $e) {
                    $log[] = "  ❌ Error al importar visitor $visitor_id: " . $e->getMessage();
                }
            } else {
                $log[] = "  [DRY] Visitor: " . substr($visitor_id, 0, 18) . "... | $source | $ip";
                $imported++;
            }
        }

        $log[] = "  ✅ Visitors importados: $imported | Saltados (caducados/inválidos): $skipped";
    }
}

$log[] = "";
$log[] = $dry_run ? "=== DRY RUN — No se escribió nada ===" : "=== IMPORTACIÓN COMPLETA ===";

echo implode("\n", $log) . "\n";
