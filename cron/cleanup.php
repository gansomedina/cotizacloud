<?php
// ============================================================
//  CotizaCloud — cron/cleanup.php
//  Limpieza periódica de tablas que crecen sin límite.
//
//  Configurar en crontab (cada 6 horas):
//    0 */6 * * * php /home/cotizacl/public_html/cron/cleanup.php >> /home/cotizacl/logs/cleanup.log 2>&1
//
//  O diario a las 3am:
//    0 3 * * * php /home/cotizacl/public_html/cron/cleanup.php >> /home/cotizacl/logs/cleanup.log 2>&1
// ============================================================

// Bloquear acceso web
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

define('COTIZAAPP', true);
require_once __DIR__ . '/../core/DB.php';

$start = microtime(true);
$log = [];

function cleanup_log(string $msg): void {
    global $log;
    $log[] = date('Y-m-d H:i:s') . ' ' . $msg;
}

// ─── 1. Sesiones expiradas ──────────────────────────────────
$n = DB::execute("DELETE FROM user_sessions WHERE expires_at < NOW()");
cleanup_log("user_sessions: eliminadas {$n} sesiones expiradas");

// ─── 2. Rate limits > 24h ──────────────────────────────────
$n = DB::execute("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
cleanup_log("rate_limits: eliminados {$n} registros antiguos");

// ─── 3. quote_events > 180 días ─────────────────────────────
// El Radar usa 150 días; dejamos 180 de margen
$n = DB::execute("DELETE FROM quote_events WHERE ts_unix < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 180 DAY))");
cleanup_log("quote_events: eliminados {$n} eventos > 180 días");

// ─── 4. quote_sessions inactivas > 90 días ──────────────────
$n = DB::execute("DELETE FROM quote_sessions WHERE updated_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
cleanup_log("quote_sessions: eliminadas {$n} sesiones > 90 días");

// ─── 5. actividad_log > 365 días ────────────────────────────
$n = DB::execute("DELETE FROM actividad_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)");
cleanup_log("actividad_log: eliminados {$n} registros > 1 año");

// ─── 6. bucket_transitions > 180 días ───────────────────────
$n = DB::execute("DELETE FROM bucket_transitions WHERE created_at < DATE_SUB(NOW(), INTERVAL 180 DAY)");
cleanup_log("bucket_transitions: eliminadas {$n} transiciones > 180 días");

// ─── 7. notificaciones_push > 90 días ───────────────────────
try {
    $n = DB::execute("DELETE FROM notificaciones_push WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    cleanup_log("notificaciones_push: eliminadas {$n} notificaciones > 90 días");
} catch (\PDOException $e) {
    cleanup_log("notificaciones_push: tabla no existe (skip)");
}

// ─── 8. cotizacion_log > 365 días ───────────────────────────
$n = DB::execute("DELETE FROM cotizacion_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)");
cleanup_log("cotizacion_log: eliminados {$n} logs > 1 año");

// ─── 9. venta_log > 365 días ────────────────────────────────
$n = DB::execute("DELETE FROM venta_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)");
cleanup_log("venta_log: eliminados {$n} logs > 1 año");

// ─── Resumen ─────────────────────────────────────────────────
$elapsed = round(microtime(true) - $start, 2);
cleanup_log("Completado en {$elapsed}s");
cleanup_log(str_repeat('─', 50));

echo implode("\n", $log) . "\n";
