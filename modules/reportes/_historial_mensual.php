<?php
// ============================================================
//  cotiza.cloud — modules/reportes/_historial_mensual.php
//  Tabla "Historial mensual" de Reportes › Financiero.
//
//  Primero los meses DEL SISTEMA (del más reciente hacia atrás) y debajo
//  los meses IMPORTADOS (historial_mensual), en una sola lista sin columna
//  de origen (CEO, 28 sep 2026).
//
//  Meses del sistema = del mes siguiente al último importado hasta hoy. Si la
//  empresa no importó nada, desde su primera cotización o venta. Así nunca se
//  enciman con lo importado (y las cotizaciones de un import masivo, que
//  pueden traer fechas viejas, no inventan meses "del sistema").
//
//  Cuenta de ventas = la MISMA de la gráfica de barras de Financiero
//  (no canceladas, sin filtro de pago), para que tabla y gráfica no digan dos
//  números distintos en la misma pantalla. Cotizaciones = enviadas del
//  embudo (no borrador, no suspendidas).
// ============================================================
defined('COTIZAAPP') or die;

/**
 * @param string $usr_filter   filtro de asesor sobre ventas `v` ('' = admin)
 * @param string $usr_filter_c filtro de asesor sobre cotizaciones `c`
 * @return array filas [anio, mes, cotizaciones, ventas, monto, tasa], más reciente primero
 */
function rep_historial_mensual(int $empresa_id, string $usr_filter, string $usr_filter_c,
                               int $max_filas = 24, ?string $hoy = null): array
{
    $hoy = $hoy ?? date('Y-m-d');
    $mes_hoy = (int)date('Y', strtotime($hoy)) * 100 + (int)date('n', strtotime($hoy));

    $imp = [];
    try {
        $imp = DB::query(
            "SELECT anio, mes, cotizaciones_cantidad, ventas_cantidad, ventas_monto, tasa_cierre
               FROM historial_mensual WHERE empresa_id = ? AND (anio * 100 + mes) <= ?
              ORDER BY anio DESC, mes DESC",
            [$empresa_id, $mes_hoy]);
    } catch (\Throwable $e) { $imp = []; }

    // ── Desde qué mes cuenta el sistema ──
    if ($imp) {
        $ult = (int)$imp[0]['anio'] * 100 + (int)$imp[0]['mes'];
        $desde = date('Y-m-01', strtotime(sprintf('%04d-%02d-01 +1 month', intdiv($ult, 100), $ult % 100)));
    } else {
        $desde = DB::val(
            "SELECT DATE_FORMAT(LEAST(
                 COALESCE((SELECT MIN(created_at) FROM ventas WHERE empresa_id = ? AND estado <> 'cancelada'), '9999-12-31'),
                 COALESCE((SELECT MIN(created_at) FROM cotizaciones WHERE empresa_id = ? AND estado <> 'borrador' AND suspendida = 0), '9999-12-31')
             ), '%Y-%m-01')",
            [$empresa_id, $empresa_id]);
    }

    $filas = [];
    if ($desde && $desde <= $hoy) {
        $hasta = date('Y-m-d 00:00:00', strtotime($hoy . ' +1 day'));
        $vtas = [];
        foreach (DB::query(
            "SELECT DATE_FORMAT(v.created_at, '%Y-%m') AS ym, COUNT(*) AS n, COALESCE(SUM(v.total), 0) AS monto
               FROM ventas v
              WHERE v.empresa_id = ? AND v.estado != 'cancelada'
                AND v.created_at >= ? AND v.created_at < ? $usr_filter
              GROUP BY ym", [$empresa_id, $desde, $hasta]) as $r) $vtas[$r['ym']] = $r;
        $cots = [];
        foreach (DB::query(
            "SELECT DATE_FORMAT(c.created_at, '%Y-%m') AS ym, COUNT(*) AS n
               FROM cotizaciones c
              WHERE c.empresa_id = ? AND c.estado <> 'borrador' AND c.suspendida = 0
                AND c.created_at >= ? AND c.created_at < ? $usr_filter_c
              GROUP BY ym", [$empresa_id, $desde, $hasta]) as $r) $cots[$r['ym']] = (int)$r['n'];

        // Del mes actual hacia atrás: los meses sin movimiento también aparecen.
        for ($t = strtotime(date('Y-m-01', strtotime($hoy))); $t >= strtotime($desde); $t = strtotime('-1 month', $t)) {
            $ym = date('Y-m', $t);
            $nc = $cots[$ym] ?? 0;
            $nv = (int)($vtas[$ym]['n'] ?? 0);
            $filas[] = [
                'anio' => (int)date('Y', $t), 'mes' => (int)date('n', $t),
                'cotizaciones' => $nc, 'ventas' => $nv,
                'monto' => (float)($vtas[$ym]['monto'] ?? 0),
                'tasa' => $nc > 0 ? round($nv / $nc * 100, 1) : 0.0,
            ];
        }
    }

    foreach ($imp as $h) {
        $filas[] = [
            'anio' => (int)$h['anio'], 'mes' => (int)$h['mes'],
            'cotizaciones' => (int)$h['cotizaciones_cantidad'], 'ventas' => (int)$h['ventas_cantidad'],
            'monto' => (float)$h['ventas_monto'], 'tasa' => (float)$h['tasa_cierre'],
        ];
    }
    return array_slice($filas, 0, $max_filas);
}
