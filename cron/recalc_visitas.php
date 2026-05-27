<?php
// ============================================================
//  CotizaCloud — cron/recalc_visitas.php
//  Cron diario: recalcula cotizaciones.visitas desde quote_sessions
//  filtrado por engagement real (scroll>0 OR visible_ms>=2000).
//
//  Razón: cotizacion.php incrementa visitas en cada INSERT de
//  quote_session, sin chequear engagement. Los ghosts (previews
//  WhatsApp/iMessage/Teams que ejecutan JS por <100ms) inflan
//  el contador.
//
//  ActividadScore.php usa c.visitas en lógica del termómetro:
//    - dormidas_7d/14d/21d: visitas>0 + estado='vista' + sin actividad
//      → cot ghost-inflada penaliza al asesor injustamente
//    - no_abiertas_5d: visitas=0 + estado='enviada' + >5d
//      → cot ghost-inflada escapa penalización merecida
//    - emp_vistas (benchmark close_rate): visitas>0 OR aceptada
//      → distorsiona benchmark
//
//  El Radar usa su propio filtro de ghosts (Radar::score con
//  quote_sessions). Este script alinea el contador con esa
//  fuente de verdad.
//
//  Ejecutar: php /path/to/cron/recalc_visitas.php
//  Crontab:  15 3 * * * php /home/cotizacl/public_html/cron/recalc_visitas.php
// ============================================================

define('COTIZAAPP', true);
require_once dirname(__DIR__) . '/config.php';

$log = function(string $msg) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
};

$log('Recalc de cotizaciones.visitas desde quote_sessions con engagement');

// Snapshot antes (para auditoría)
$antes = DB::row(
    "SELECT COUNT(*) AS cots_con_visitas, SUM(visitas) AS total_visitas
     FROM cotizaciones WHERE estado IN ('enviada','vista','aceptada','convertida')"
);
$log("Antes: {$antes['cots_con_visitas']} cots con visitas, total={$antes['total_visitas']}");

// Recalcular en una sola query (eficiente)
$start = microtime(true);
$afectadas = DB::execute(
    "UPDATE cotizaciones c
     SET c.visitas = (
        SELECT COUNT(*) FROM quote_sessions qs
        WHERE qs.cotizacion_id = c.id
          AND qs.es_interno = 0
          AND (qs.scroll_max > 0 OR qs.visible_ms >= 2000)
     )
     WHERE c.estado IN ('enviada','vista','aceptada','convertida')"
);
$elapsed = round((microtime(true) - $start) * 1000);

$despues = DB::row(
    "SELECT COUNT(*) AS cots_con_visitas, SUM(visitas) AS total_visitas
     FROM cotizaciones WHERE estado IN ('enviada','vista','aceptada','convertida')"
);
$log("Después: {$despues['cots_con_visitas']} cots con visitas, total={$despues['total_visitas']}");
$log("Rows afectadas: {$afectadas} ({$elapsed}ms)");

$delta = (int)$antes['total_visitas'] - (int)$despues['total_visitas'];
if ($delta > 0) {
    $log("Ghosts limpiados: {$delta} visitas falsas eliminadas");
}

$log('Done');
