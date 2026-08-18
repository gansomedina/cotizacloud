<?php
// ============================================================
//  AUDITORÍA de close_rate y close_rate_hist
//
//  Son las dos varas con las que el score juzga la Conversión de TODOS los
//  asesores (perf_ratio = tasa_cierre / close_rate_hist). Si la vara está
//  torcida, todo lo que cuelga de ella lo está.
//
//  Este script REPRODUCE literalmente las queries de
//  core/ActividadScore::_benchmarks() (líneas 1691-1735) y además descompone
//  de dónde sale cada número, para poder ver si el resultado es un cierre
//  real o un artefacto de cómo está armada la cuenta.
//
//  Lo que revisa, con datos, no con teoría:
//   (a) ASIMETRÍA: el denominador cuenta cotizaciones por created_at y el
//       numerador por accion_at. Una cotización nacida hace 3 meses y cerrada
//       ayer SUMA al numerador y NO al denominador. El propio código lo admite
//       ("cerrar backlog puede dar ratio >1") y lo tapa con un tope de 0.90.
//       Aquí se cuenta cuántas son.
//   (b) TOPE: si el ratio salió recortado a 0.90, ese 90% no es una medición,
//       es un techo. Hay que saberlo.
//   (c) MUESTRA: close_rate_hist se acepta con apenas 5 cotizaciones y sin
//       límite de antigüedad. Se imprime el tamaño y desde cuándo.
//
//  SOLO LEE. No escribe nada.
//    cd /var/www/cotizacloud && php tools/auditar_close_rate.php config.php
// ============================================================
define('COTIZAAPP', 1);
$cfg = $argv[1] ?? '/var/www/cotizacloud/config.php';
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

$PERIODO_BASE = 15;
$CAP = 0.90;   // ActividadScore.php:1710 y :1735
$MIN_MUESTRA = 5;

function periodo_de(int $eid, int $base): int {
    if (class_exists('ActividadScore') && method_exists('ActividadScore', 'periodo_efectivo')) {
        try { return max(1, (int)ActividadScore::periodo_efectivo($eid)); } catch (Throwable $e) {}
    }
    return $base;
}

// Filtros COPIADOS de _benchmarks(). Si el motor cambia, esto queda obsoleto
// y hay que actualizarlo — por eso van citadas las líneas de origen.
$ABIERTA = "total > 0 AND suspendida = 0 AND estado != 'borrador'
            AND (visitas > 0 OR estado IN ('aceptada','convertida','aceptada_cliente'))";
$CERRADA = "total > 0 AND suspendida = 0
            AND estado IN ('aceptada','convertida','aceptada_cliente')
            AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = cotizaciones.id
                        AND v.pagado > 0 AND v.estado != 'cancelada')";

echo "AUDITORÍA — close_rate (ventana actual) y close_rate_hist (todo lo anterior)\n";
echo "Reproduce ActividadScore::_benchmarks() línea por línea. Solo lectura.\n";
echo "perf_ratio de cada asesor = su tasa_cierre ÷ close_rate_hist de su empresa.\n\n";

$emps = DB::query("SELECT id, nombre FROM empresas WHERE slug <> '_system' ORDER BY nombre");
$alertas = [];

foreach ($emps as $e) {
    $eid = (int)$e['id'];
    $p   = periodo_de($eid, $PERIODO_BASE);

    // ── VENTANA ACTUAL ──
    $ab  = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND $ABIERTA
                         AND created_at >= DATE_SUB(NOW(), INTERVAL $p DAY)", [$eid]);
    $ci  = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND $CERRADA
                         AND accion_at >= DATE_SUB(NOW(), INTERVAL $p DAY)", [$eid]);
    // (a) cuántas de las cerradas NACIERON fuera de la ventana → no están en el denominador
    $fuera = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND $CERRADA
                           AND accion_at >= DATE_SUB(NOW(), INTERVAL $p DAY)
                           AND created_at < DATE_SUB(NOW(), INTERVAL $p DAY)", [$eid]);

    $crudo = $ab > 0 ? $ci / $ab : null;
    $cr    = $ab >= $MIN_MUESTRA ? min($crudo, $CAP) : 0.15;

    // ── HISTÓRICO ──
    $abh = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND $ABIERTA
                         AND created_at < DATE_SUB(NOW(), INTERVAL $p DAY)", [$eid]);
    $cih = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND $CERRADA
                         AND accion_at < DATE_SUB(NOW(), INTERVAL $p DAY)", [$eid]);
    $crudo_h = $abh > 0 ? $cih / $abh : null;
    $crh = $abh >= $MIN_MUESTRA ? min($crudo_h, $CAP) : $cr;

    // (c) antigüedad de la muestra histórica
    $rango = DB::row("SELECT MIN(created_at) AS d1, MAX(created_at) AS d2 FROM cotizaciones
                      WHERE empresa_id=? AND $ABIERTA
                      AND created_at < DATE_SUB(NOW(), INTERVAL $p DAY)", [$eid]);

    if ($ab === 0 && $abh === 0) continue;

    printf("── %s   (período %dd)\n", $e['nombre'], $p);

    // ACTUAL
    $nota = [];
    if ($ab < $MIN_MUESTRA)                 $nota[] = "muestra < $MIN_MUESTRA → se usa 0.15 fijo";
    if ($crudo !== null && $crudo > $CAP)   $nota[] = sprintf("RECORTADO: el ratio crudo era %.2f", $crudo);
    if ($fuera > 0)                         $nota[] = "$fuera de las $ci cerradas nacieron ANTES de la ventana";
    printf("   actual     abiertas %3d · cerradas %3d · close_rate %.2f%s\n",
        $ab, $ci, $cr, $nota ? '   ← ' . implode(' · ', $nota) : '');

    // HISTÓRICO
    // El tope solo puede haber actuado si la muestra alcanzó el mínimo; por
    // debajo de eso el valor ni siquiera se usa (cae al actual). Avisar
    // "RECORTADO" ahí era una falsa alarma — la daba Apple Review Demo, que
    // con 2 cotizaciones tiene ratio crudo 1.00 y jamás llega al tope.
    $notah = [];
    if ($abh < $MIN_MUESTRA)                  $notah[] = "sin historial → cae al actual";
    elseif ($abh < 20)                        $notah[] = "muestra CHICA ($abh)";
    if ($abh >= $MIN_MUESTRA && $crudo_h !== null && $crudo_h > $CAP)
                                              $notah[] = sprintf("RECORTADO: el ratio crudo era %.2f", $crudo_h);
    if (!empty($rango['d1']))                 $notah[] = 'desde ' . substr($rango['d1'], 0, 10);
    printf("   histórico  abiertas %3d · cerradas %3d · close_rate %.2f%s\n",
        $abh, $cih, $crh, $notah ? '   ← ' . implode(' · ', $notah) : '');

    // Efecto sobre los asesores: la vara con la que se les mide
    try {
        $ases = DB::query(
            "SELECT u.nombre, s.tasa_cierre, s.s_conversion, s.score
               FROM usuario_score s JOIN usuarios u ON u.id = s.usuario_id
              WHERE s.empresa_id = ? AND COALESCE(u.rol,'') <> 'superadmin'
              ORDER BY u.nombre", [$eid]);
        foreach ($ases as $a) {
            if ($a['tasa_cierre'] === null) continue;
            $tc = (float)$a['tasa_cierre'];
            $pr = min($tc / max($crh, 0.01), 1.0);
            printf("      %-20s cierra %.0f%% vs vara %.0f%% → perf %.2f%s\n",
                mb_substr($a['nombre'], 0, 20), $tc * 100, $crh * 100, $pr,
                $pr >= 1.0 ? '  (inmune a penalizaciones)' : '');
        }
    } catch (Throwable $ex) {}

    // Alertas para el resumen
    if ($ab  >= $MIN_MUESTRA && $crudo   !== null && $crudo   > $CAP) $alertas[] = "{$e['nombre']}: close_rate ACTUAL recortado al tope (crudo " . number_format($crudo, 2) . ")";
    if ($abh >= $MIN_MUESTRA && $crudo_h !== null && $crudo_h > $CAP) $alertas[] = "{$e['nombre']}: close_rate HISTÓRICO recortado al tope (crudo " . number_format($crudo_h, 2) . ")";
    if ($fuera > 0)                           $alertas[] = "{$e['nombre']}: $fuera cierre(s) suman al numerador sin estar en el denominador";
    if ($abh >= $MIN_MUESTRA && $abh < 20)    $alertas[] = "{$e['nombre']}: la vara histórica se fija con solo $abh cotizaciones";
    echo "\n";
}

echo "═══ RESUMEN ═══\n";
if (!$alertas) {
    echo "Sin anomalías: ningún ratio recortado, ningún cierre fuera del denominador,\n";
    echo "ninguna muestra histórica chica. Los saltos entre actual e histórico son reales.\n";
} else {
    foreach ($alertas as $a) echo "  · $a\n";
    echo "\nQué significa cada una:\n";
    echo "  · 'recortado al tope': el 0.90 NO es una medición, es el techo que puso el\n";
    echo "    código (ActividadScore.php:1710) para que un ratio >1 no invierta los pesos.\n";
    echo "  · 'suman al numerador sin estar en el denominador': el denominador cuenta por\n";
    echo "    created_at y el numerador por accion_at. Cerrar backlog viejo sube el ratio\n";
    echo "    sin que ninguna cotización nueva lo respalde.\n";
    echo "  · 'muestra chica': close_rate_hist se acepta con 5 cotizaciones y sin límite de\n";
    echo "    antigüedad. Con pocas, la vara es ruido, y contra ella se mide a todo el equipo.\n";
}
