<?php
// ============================================================
//  CotizaApp — modules/superadmin/executive.php
//  Dashboard Ejecutivo — Vista consolidada multi-empresa
//  Solo SuperAdmin
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

// ─── Empresas monitoreadas ──────────────────────────────────
$empresas_cfg = [
    12 => ['nombre' => 'OnTime HMO',    'color' => '#22c55e', 'short' => 'HMO'],
    13 => ['nombre' => 'OnTime CEN',    'color' => '#3b82f6', 'short' => 'CEN'],
    14 => ['nombre' => 'OnTime NOG',    'color' => '#a855f7', 'short' => 'NOG'],
    2  => ['nombre' => 'Closet Factory','color' => '#f97316', 'short' => 'CF'],
    7  => ['nombre' => 'Granito Depot', 'color' => '#64748b', 'short' => 'GD'],
];
$emp_ids = implode(',', array_keys($empresas_cfg));

// ─── Periodo seleccionable ──────────────────────────────────
$now = new DateTimeImmutable('now', new DateTimeZone('America/Hermosillo'));
$periodo = $_GET['periodo'] ?? 'mes_actual';
$periodos_ok = ['mes_actual','mes_ant','3_meses','6_meses','anio','todo'];
if (!in_array($periodo, $periodos_ok)) $periodo = 'mes_actual';

switch ($periodo) {
    case 'mes_ant':
        $p_ini = $now->modify('first day of last month')->format('Y-m-d');
        $p_fin = $now->modify('last day of last month')->format('Y-m-d');
        $p_label = $now->modify('first day of last month')->format('F Y');
        break;
    case '3_meses':
        $p_ini = $now->modify('-2 months')->modify('first day of this month')->format('Y-m-d');
        $p_fin = $now->format('Y-m-d');
        $p_label = 'Últimos 3 meses';
        break;
    case '6_meses':
        $p_ini = $now->modify('-5 months')->modify('first day of this month')->format('Y-m-d');
        $p_fin = $now->format('Y-m-d');
        $p_label = 'Últimos 6 meses';
        break;
    case 'anio':
        $p_ini = $now->format('Y') . '-01-01';
        $p_fin = $now->format('Y-m-d');
        $p_label = 'Año ' . $now->format('Y');
        break;
    case 'todo':
        $p_ini = '2021-01-01';
        $p_fin = $now->format('Y-m-d');
        $p_label = 'Todo el historial';
        break;
    default:
        $p_ini = $now->format('Y-m') . '-01';
        $p_fin = $now->format('Y-m-d');
        $p_label = $now->format('F Y');
}
$p_ini_dt = $p_ini . ' 00:00:00';
$p_fin_dt = $p_fin . ' 23:59:59';

// Periodo anterior (mes anterior completo para comparación)
$ant_ini = $now->modify('first day of last month')->format('Y-m-d');
$ant_fin = $now->modify('last day of last month')->format('Y-m-d');
$ant_ini_dt = $ant_ini . ' 00:00:00';
$ant_fin_dt = $ant_fin . ' 23:59:59';

// ─── KPIs GLOBALES ──────────────────────────────────────────
$kpi_mes = DB::row(
    "SELECT COALESCE(SUM(total),0) AS ventas,
            COUNT(*) AS num
     FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?",
    [$p_ini_dt, $p_fin_dt]
);

$kpi_ant = DB::row(
    "SELECT COALESCE(SUM(total),0) AS ventas, COUNT(*) AS num
     FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?",
    [$ant_ini_dt, $ant_fin_dt]
);

$cobrado_hoy = (float)DB::val(
    "SELECT COALESCE(SUM(monto),0) FROM recibos
     WHERE empresa_id IN ({$emp_ids}) AND tipo='abono' AND cancelado=0 AND fecha = CURDATE()"
);
$num_pagos_hoy = (int)DB::val(
    "SELECT COUNT(*) FROM recibos
     WHERE empresa_id IN ({$emp_ids}) AND tipo='abono' AND cancelado=0 AND fecha = CURDATE()"
);

$por_cobrar = (float)DB::val(
    "SELECT COALESCE(SUM(saldo),0) FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado IN ('pendiente','parcial')"
);

$pipeline = DB::row(
    "SELECT COUNT(*) AS num, COALESCE(SUM(total),0) AS monto
     FROM cotizaciones
     WHERE empresa_id IN ({$emp_ids}) AND estado IN ('enviada','vista')
       AND COALESCE(suspendida,0) = 0"
);

// Acumulado del mes (ventas reales + historial)
$mes_actual = $now->format('Y-m');
$acum_mes_real = (float)DB::val(
    "SELECT COALESCE(SUM(total),0) FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?",
    [$now->format('Y-m') . '-01 00:00:00', $now->format('Y-m-d') . ' 23:59:59']
);
$acum_mes_hist = (float)DB::val(
    "SELECT COALESCE(SUM(ventas_monto),0) FROM historial_mensual
     WHERE empresa_id IN ({$emp_ids}) AND anio = ? AND mes = ?",
    [(int)$now->format('Y'), (int)$now->format('n')]
);
$acum_mes = $acum_mes_real + $acum_mes_hist;

// Acumulado del año (ventas reales + historial)
$anio_actual = (int)$now->format('Y');
$acum_anio_real = (float)DB::val(
    "SELECT COALESCE(SUM(total),0) FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?",
    [$anio_actual . '-01-01 00:00:00', $now->format('Y-m-d') . ' 23:59:59']
);
$acum_anio_hist = (float)DB::val(
    "SELECT COALESCE(SUM(ventas_monto),0) FROM historial_mensual
     WHERE empresa_id IN ({$emp_ids}) AND anio = ?",
    [$anio_actual]
);
$acum_anio = $acum_anio_real + $acum_anio_hist;

$var_ventas = (float)$kpi_ant['ventas'] > 0
    ? round(((float)$kpi_mes['ventas'] - (float)$kpi_ant['ventas']) / (float)$kpi_ant['ventas'] * 100, 1)
    : ((float)$kpi_mes['ventas'] > 0 ? 100 : 0);

// ─── VENTAS POR EMPRESA (reales + historial) ───────────────
$ve_act = [];
$rows = DB::query(
    "SELECT empresa_id, SUM(monto) AS monto, SUM(num) AS num FROM (
        SELECT empresa_id, COALESCE(SUM(total),0) AS monto, COUNT(*) AS num
        FROM ventas WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
          AND created_at BETWEEN ? AND ?
        GROUP BY empresa_id
        UNION ALL
        SELECT empresa_id, ventas_monto AS monto, ventas_cantidad AS num
        FROM historial_mensual
        WHERE empresa_id IN ({$emp_ids})
          AND STR_TO_DATE(CONCAT(anio,'-',LPAD(mes,2,'0'),'-01'),'%Y-%m-%d') BETWEEN ? AND ?
    ) combined GROUP BY empresa_id",
    [$p_ini_dt, $p_fin_dt, $p_ini, $p_fin]
);
foreach ($rows as $r) $ve_act[(int)$r['empresa_id']] = $r;

$ve_ant = [];
$rows = DB::query(
    "SELECT empresa_id, SUM(monto) AS monto, SUM(num) AS num FROM (
        SELECT empresa_id, COALESCE(SUM(total),0) AS monto, COUNT(*) AS num
        FROM ventas WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
          AND created_at BETWEEN ? AND ?
        GROUP BY empresa_id
        UNION ALL
        SELECT empresa_id, ventas_monto AS monto, ventas_cantidad AS num
        FROM historial_mensual
        WHERE empresa_id IN ({$emp_ids})
          AND STR_TO_DATE(CONCAT(anio,'-',LPAD(mes,2,'0'),'-01'),'%Y-%m-%d') BETWEEN ? AND ?
    ) combined GROUP BY empresa_id",
    [$ant_ini_dt, $ant_fin_dt, $ant_ini, $ant_fin]
);
foreach ($rows as $r) $ve_ant[(int)$r['empresa_id']] = $r;

// Tasa cierre por empresa (excluir borradores, contar aceptadas+convertidas)
$ce = [];
$rows = DB::query(
    "SELECT empresa_id, COUNT(*) AS total,
            SUM(CASE WHEN estado IN ('aceptada','convertida') THEN 1 ELSE 0 END) AS aceptadas
     FROM cotizaciones WHERE empresa_id IN ({$emp_ids}) AND COALESCE(suspendida,0)=0
       AND estado != 'borrador'
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$p_ini_dt, $p_fin_dt]
);

// Ticket promedio por empresa
$ticket_global = (float)$kpi_mes['num'] > 0 ? (float)$kpi_mes['ventas'] / (float)$kpi_mes['num'] : 0;

// Funnel global
$funnel = DB::row(
    "SELECT COUNT(*) AS total,
            SUM(CASE WHEN estado != 'borrador' THEN 1 ELSE 0 END) AS enviadas,
            SUM(CASE WHEN estado IN ('vista','aceptada','convertida','rechazada') THEN 1 ELSE 0 END) AS vistas,
            SUM(CASE WHEN estado IN ('aceptada','convertida') THEN 1 ELSE 0 END) AS aceptadas,
            SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) AS rechazadas
     FROM cotizaciones WHERE empresa_id IN ({$emp_ids}) AND COALESCE(suspendida,0)=0
       AND created_at BETWEEN ? AND ?",
    [$p_ini_dt, $p_fin_dt]
);

// Pagos del periodo (completos)
$pagos_periodo = DB::query(
    "SELECT r.empresa_id, r.monto, r.forma_pago, r.fecha, r.created_at,
            v.titulo AS venta_titulo, v.numero AS venta_numero,
            c.nombre AS cliente_nombre
     FROM recibos r
     JOIN ventas v ON v.id = r.venta_id
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE r.empresa_id IN ({$emp_ids}) AND r.tipo='abono' AND r.cancelado=0
       AND r.fecha BETWEEN ? AND ?
     ORDER BY r.fecha DESC, r.created_at DESC
     LIMIT 200",
    [$p_ini, $p_fin]
);
$total_pagos_periodo = 0;
foreach ($pagos_periodo as $pp) $total_pagos_periodo += (float)$pp['monto'];

// Ventas por cobrar (no 100% pagadas)
$ventas_por_cobrar = DB::query(
    "SELECT v.empresa_id, v.titulo, v.numero, v.total, v.pagado, v.saldo, v.created_at,
            c.nombre AS cliente_nombre
     FROM ventas v
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE v.empresa_id IN ({$emp_ids}) AND v.estado IN ('pendiente','parcial')
       AND v.saldo > 0
     ORDER BY v.saldo DESC LIMIT 100"
);
$total_por_cobrar_lista = 0;
foreach ($ventas_por_cobrar as $vpc) $total_por_cobrar_lista += (float)$vpc['saldo'];

// Ventas 100% cobradas del periodo
$ventas_cobradas = DB::query(
    "SELECT v.empresa_id, v.titulo, v.numero, v.total, v.created_at,
            c.nombre AS cliente_nombre
     FROM ventas v
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE v.empresa_id IN ({$emp_ids}) AND v.estado IN ('pagada','entregada')
       AND v.created_at BETWEEN ? AND ?
     ORDER BY v.created_at DESC LIMIT 100",
    [$p_ini_dt, $p_fin_dt]
);
$total_cobradas = 0;
foreach ($ventas_cobradas as $vc) $total_cobradas += (float)$vc['total'];

// Serie de 12 meses (necesario para tasa_trend y tendencias)
$meses_12 = [];
for ($i = 11; $i >= 0; $i--) $meses_12[] = date('Y-m', strtotime("-$i months"));

// Tasa cierre mensual por empresa (para gráfica)
$tasa_mensual = DB::query(
    "SELECT DATE_FORMAT(created_at,'%Y-%m') AS mes, empresa_id,
            COUNT(*) AS total,
            SUM(CASE WHEN estado IN ('aceptada','convertida') THEN 1 ELSE 0 END) AS aceptadas
     FROM cotizaciones
     WHERE empresa_id IN ({$emp_ids}) AND COALESCE(suspendida,0)=0 AND estado != 'borrador'
       AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY mes, empresa_id ORDER BY mes ASC"
);
$tasa_trend = [];
foreach ($empresas_cfg as $eid => $ec) {
    $tasa_trend[$eid] = [];
    foreach ($meses_12 as $m) $tasa_trend[$eid][$m] = 0;
}
foreach ($tasa_mensual as $tm) {
    $eid = (int)$tm['empresa_id'];
    $t = (int)$tm['total'] > 0 ? round((int)$tm['aceptadas'] / (int)$tm['total'] * 100, 1) : 0;
    if (isset($tasa_trend[$eid][$tm['mes']])) $tasa_trend[$eid][$tm['mes']] = $t;
}
foreach ($rows as $r) $ce[(int)$r['empresa_id']] = $r;

// ─── TENDENCIAS 12 MESES ────────────────────────────────────
$tendencias = DB::query(
    "SELECT mes, empresa_id, SUM(monto) AS monto FROM (
        SELECT DATE_FORMAT(v.created_at,'%Y-%m') AS mes, v.empresa_id, v.total AS monto
        FROM ventas v
        WHERE v.empresa_id IN ({$emp_ids}) AND v.estado != 'cancelada'
          AND v.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        UNION ALL
        SELECT CONCAT(h.anio,'-',LPAD(h.mes,2,'0')) AS mes, h.empresa_id, h.ventas_monto AS monto
        FROM historial_mensual h
        WHERE h.empresa_id IN ({$emp_ids})
          AND STR_TO_DATE(CONCAT(h.anio,'-',LPAD(h.mes,2,'0'),'-01'),'%Y-%m-%d') >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    ) t GROUP BY mes, empresa_id ORDER BY mes ASC"
);

// $meses_12 ya definido arriba

$trend = [];
foreach ($empresas_cfg as $eid => $ec) {
    $trend[$eid] = [];
    foreach ($meses_12 as $m) $trend[$eid][$m] = 0;
}
foreach ($tendencias as $t) {
    $eid = (int)$t['empresa_id'];
    if (isset($trend[$eid][$t['mes']])) {
        $trend[$eid][$t['mes']] += (float)$t['monto'];
    }
}

// Labels para Chart.js
$chart_labels = [];
foreach ($meses_12 as $m) $chart_labels[] = date('M Y', strtotime($m . '-01'));

// ─── PAGOS DEL DÍA ─────────────────────────────────────────
$pagos_hoy = DB::query(
    "SELECT r.empresa_id, r.monto, r.forma_pago, r.created_at, r.concepto,
            v.titulo AS venta_titulo, v.numero AS venta_numero,
            c.nombre AS cliente_nombre
     FROM recibos r
     JOIN ventas v ON v.id = r.venta_id
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE r.empresa_id IN ({$emp_ids}) AND r.tipo='abono' AND r.cancelado=0
       AND r.fecha = CURDATE()
     ORDER BY r.created_at DESC
     LIMIT 30"
);

// ─── VENTAS SIN PAGOS ───────────────────────────────────────
$sin_pagos = DB::query(
    "SELECT v.empresa_id, v.titulo, v.numero, v.total, v.saldo, v.created_at,
            c.nombre AS cliente_nombre,
            DATEDIFF(NOW(), v.created_at) AS dias
     FROM ventas v
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE v.empresa_id IN ({$emp_ids})
       AND v.estado IN ('pendiente','parcial')
       AND v.saldo > 0 AND v.total > 0
     ORDER BY v.created_at ASC LIMIT 30"
);
$total_sin_cobrar = 0;
foreach ($sin_pagos as $sp) $total_sin_cobrar += (float)$sp['saldo'];

// ─── VENTAS SIN NINGÚN PAGO (pagado=0) ─────────────────────
$sin_cobrar = DB::query(
    "SELECT v.empresa_id, v.titulo, v.numero, v.total, v.created_at,
            c.nombre AS cliente_nombre,
            DATEDIFF(NOW(), v.created_at) AS dias
     FROM ventas v
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE v.empresa_id IN ({$emp_ids})
       AND v.estado IN ('pendiente','parcial')
       AND v.pagado = 0 AND v.total > 0
     ORDER BY v.created_at ASC LIMIT 30"
);
$total_sin_cobrar_cero = 0;
foreach ($sin_cobrar as $sc) $total_sin_cobrar_cero += (float)$sc['total'];

// ─── COTIZACIONES SIN ABRIR ────────────────────────────────
$sin_abrir = DB::query(
    "SELECT c.empresa_id, c.titulo, c.numero, c.total, c.created_at,
            cl.nombre AS cliente_nombre,
            DATEDIFF(NOW(), c.created_at) AS dias
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.empresa_id IN ({$emp_ids})
       AND c.estado = 'enviada'
       AND COALESCE(c.suspendida, 0) = 0
     ORDER BY c.created_at ASC LIMIT 30"
);

// ─── PERFORMANCE ASESORES ───────────────────────────────────
$asesores = DB::query(
    "SELECT u.id, u.nombre, u.empresa_id,
            us.score, us.nivel, us.s_activacion, us.s_seguimiento, us.s_conversion
     FROM usuarios u
     LEFT JOIN usuario_score us ON us.usuario_id = u.id AND us.empresa_id = u.empresa_id
     WHERE u.empresa_id IN ({$emp_ids}) AND u.empresa_id != 7 AND u.activo = 1 AND u.rol = 'asesor'
     ORDER BY us.score DESC"
);

// ─── COMPENSACIONES ─────────────────────────────────────────
$compensaciones = DB::query(
    "SELECT c.id, c.empresa_id, c.codigo, c.monto_fijo, c.descripcion, c.usos, c.activo,
            c.created_at, c.vencimiento_fecha,
            e.nombre AS emp_nombre
     FROM cupones c
     JOIN empresas e ON e.id = c.empresa_id
     WHERE c.empresa_id IN ({$emp_ids})
       AND c.descripcion LIKE 'Compensación:%'
     ORDER BY c.created_at DESC LIMIT 30"
);
$comp_activas = 0; $comp_usadas = 0;
foreach ($compensaciones as $cc) {
    if ($cc['usos'] > 0) $comp_usadas++;
    elseif ($cc['activo']) $comp_activas++;
}

// Ventas por asesor este mes
$va_asesor = [];
$rows = DB::query(
    "SELECT COALESCE(v.vendedor_id, v.usuario_id) AS uid,
            COUNT(*) AS num, COALESCE(SUM(v.total),0) AS monto
     FROM ventas v
     WHERE v.empresa_id IN ({$emp_ids}) AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ?
     GROUP BY uid",
    [$p_ini_dt, $p_fin_dt]
);
foreach ($rows as $r) $va_asesor[(int)$r['uid']] = $r;

// ─── DISTRIBUCIÓN INGRESOS (para donut) ─────────────────────
$donut_data = [];
$donut_labels = [];
$donut_colors = [];
foreach ($empresas_cfg as $eid => $ec) {
    $monto = (float)($ve_act[$eid]['monto'] ?? 0);
    $donut_data[] = $monto;
    $donut_labels[] = $ec['short'];
    $donut_colors[] = $ec['color'];
}

// ─── Empresas ordenadas por monto (para gráficas y leyendas) ──
$emp_sorted = [];
foreach ($empresas_cfg as $eid => $ec) {
    $total_12 = 0;
    foreach ($meses_12 as $m) $total_12 += $trend[$eid][$m] ?? 0;
    $emp_sorted[] = ['eid'=>$eid, 'ec'=>$ec, 'total'=>$total_12];
}
usort($emp_sorted, fn($a,$b) => $b['total'] <=> $a['total']);

// Datos para gráfica por empresa (JSON para JS)
$emp_chart_data = [];
foreach ($empresas_cfg as $eid => $ec) {
    $ventas_arr = [];
    $tasa_arr = [];
    $var_arr = [];
    $sum = 0;
    $prev = null;
    foreach ($meses_12 as $m) {
        $v = $trend[$eid][$m] ?? 0;
        $ventas_arr[] = $v;
        $sum += $v;
        $tasa_arr[] = $tasa_trend[$eid][$m] ?? 0;
        if ($prev !== null && $prev > 0) {
            $var_arr[] = round(($v - $prev) / $prev * 100, 1);
        } else {
            $var_arr[] = 0;
        }
        $prev = $v;
    }
    $media = count($meses_12) > 0 ? round($sum / count($meses_12), 2) : 0;
    $emp_chart_data[$eid] = [
        'nombre' => $ec['nombre'],
        'short'  => $ec['short'],
        'color'  => $ec['color'],
        'ventas' => $ventas_arr,
        'tasa'   => $tasa_arr,
        'variacion' => $var_arr,
        'media'  => $media,
    ];
}

// ─── COMISIONES: ventas 100% pagadas del periodo ────────────
$comisiones = DB::query(
    "SELECT v.id, v.empresa_id, v.titulo, v.numero, v.total, v.pagado, v.created_at,
            COALESCE(v.vendedor_id, v.usuario_id) AS asesor_id,
            u.nombre AS asesor_nombre,
            cl.nombre AS cliente_nombre,
            (SELECT MAX(r.fecha) FROM recibos r
             WHERE r.venta_id = v.id AND r.tipo='abono' AND r.cancelado=0) AS ultimo_pago,
            (SELECT COALESCE(SUM(r.monto),0) FROM recibos r
             WHERE r.venta_id = v.id AND r.tipo='abono' AND r.cancelado=0) AS pagado_recibos
     FROM ventas v
     LEFT JOIN usuarios u ON u.id = COALESCE(v.vendedor_id, v.usuario_id)
     LEFT JOIN clientes cl ON cl.id = v.cliente_id
     WHERE v.empresa_id IN ({$emp_ids}) AND v.estado IN ('pagada','entregada')
       AND v.created_at BETWEEN ? AND ?
     ORDER BY v.empresa_id, ultimo_pago DESC, v.created_at DESC",
    [$p_ini_dt, $p_fin_dt]
);
$comi_por_empresa = [];
foreach ($comisiones as $cm) {
    $eid = (int)$cm['empresa_id'];
    if (!isset($empresas_cfg[$eid])) continue;
    $comi_por_empresa[$eid][] = $cm;
}

// ─── Helpers ────────────────────────────────────────────────
function xm(float $n): string {
    if (abs($n) >= 1000000) return '$' . number_format($n/1000000, 1) . 'M';
    if (abs($n) >= 1000) return '$' . number_format($n/1000, 0) . 'K';
    return '$' . number_format($n, 0);
}
function xf(float $n): string { return '$' . number_format($n, 0); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Executive Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#09090b;--card:#18181b;--card-hover:#1f1f23;--border:#27272a;--border2:#3f3f46;--text:#fafafa;--t2:#a1a1aa;--t3:#52525b;--g:#22c55e;--g2:#16a34a;--r:#ef4444;--b:#3b82f6;--a:#f59e0b;--p:#a855f7;--radius:12px}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;-webkit-font-smoothing:antialiased}

.wrap{max-width:1400px;margin:0 auto;padding:24px 28px 60px}

/* Header */
.hdr{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:12px}
.hdr h1{font-size:28px;font-weight:900;letter-spacing:-.04em;line-height:1}
.hdr-sub{font-size:13px;color:var(--t2);margin-top:4px}
.hdr-right{display:flex;align-items:center;gap:12px}
.hdr-back{color:var(--t2);text-decoration:none;font-size:13px;font-weight:600;padding:8px 16px;border:1px solid var(--border);border-radius:8px;transition:all .15s}
.hdr-back:hover{border-color:var(--g);color:var(--g)}
.hdr-live{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--g);font-weight:600;letter-spacing:.02em}
.hdr-dot{width:7px;height:7px;border-radius:50%;background:var(--g);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

/* KPI Cards */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px}
.kpi{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 22px;transition:border-color .15s}
.kpi:hover{border-color:var(--border2)}
.kpi-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.kpi-lbl{font-size:12px;font-weight:600;color:var(--t2);text-transform:uppercase;letter-spacing:.05em}
.kpi-badge{font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px}
.kpi-badge.up{background:#22c55e18;color:var(--g)}
.kpi-badge.dn{background:#ef444418;color:var(--r)}
.kpi-badge.eq{background:#3f3f4630;color:var(--t2)}
.kpi-val{font:900 32px 'Inter',sans-serif;letter-spacing:-.03em;line-height:1.1}
.kpi-sub{font-size:12px;color:var(--t3);margin-top:6px}
.kpi-sub b{color:var(--t2)}

/* Chart */
.chart-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:28px}
.chart-title{font:700 15px 'Inter',sans-serif;margin-bottom:4px}
.chart-sub{font-size:12px;color:var(--t2);margin-bottom:18px}
.chart-canvas{position:relative;height:280px}

/* Table */
.sec{margin-bottom:28px}
.sec-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.sec-title{font:700 16px 'Inter',sans-serif;letter-spacing:-.02em}
.sec-count{font-size:12px;color:var(--t2);background:var(--card);border:1px solid var(--border);padding:4px 10px;border-radius:6px}
.tbl-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
table{width:100%;border-collapse:collapse}
thead th{font:600 11px 'Inter',sans-serif;letter-spacing:.05em;text-transform:uppercase;color:var(--t3);padding:12px 16px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap}
thead th.r{text-align:right}
tbody td{padding:12px 16px;border-bottom:1px solid var(--border);font-size:13px}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:var(--card-hover)}
.mono{font-family:'Inter',sans-serif;font-variant-numeric:tabular-nums}

/* Company tag */
.tag{display:inline-flex;align-items:center;justify-content:center;min-width:36px;padding:4px 10px;border-radius:6px;font:700 10px 'Inter',sans-serif;color:#fff;letter-spacing:.03em}

/* Variation */
.var{font:700 12px 'Inter',sans-serif}
.var.up{color:var(--g)}.var.up::before{content:'↑ '}
.var.dn{color:var(--r)}.var.dn::before{content:'↓ '}
.var.eq{color:var(--t3)}

/* Two cols */
.grid-2{display:grid;grid-template-columns:3fr 2fr;gap:14px}

/* Responsive */
@media(max-width:1100px){.grid-2{grid-template-columns:1fr}}
/* Metric toggle buttons */
.metric-btn{padding:6px 12px;border:1px solid var(--border);border-radius:6px;background:transparent;color:var(--t3);font:600 11px 'Inter',sans-serif;cursor:pointer;transition:all .15s}
.metric-btn.on{background:var(--card-hover);color:var(--text);border-color:var(--border2)}
.metric-btn:hover:not(.on){background:var(--card-hover)}

/* Compensation buttons */
.comp-btn{padding:10px 18px;background:var(--card);border:1px solid var(--border);border-radius:8px;color:var(--text);font:600 13px 'Inter',sans-serif;cursor:pointer;transition:all .15s}
.comp-btn:hover{border-color:var(--g);color:var(--g)}
.comp-monto{flex:1;padding:12px;background:var(--bg);border:2px solid var(--border);border-radius:10px;color:var(--text);font:700 16px 'Inter',sans-serif;cursor:pointer;transition:all .15s}
.comp-monto:hover{border-color:var(--g)}
.comp-monto.sel{border-color:var(--g);background:var(--g);color:#fff}

/* Operation tabs */
.op-tab{padding:8px 16px;border:none;background:transparent;color:var(--t2);font:600 12px 'Inter',sans-serif;border-radius:7px;cursor:pointer;transition:all .15s}
.op-tab.on{background:var(--g);color:#fff}
.op-tab:hover:not(.on){background:var(--card-hover)}
.op-panel{display:none}
.op-panel.on{display:block}

/* Periodo selector */
.periodo-sel{background:var(--card);color:var(--text);border:1px solid var(--border);border-radius:8px;padding:8px 14px;font:600 13px 'Inter',sans-serif;cursor:pointer;outline:none}
.periodo-sel:focus{border-color:var(--g)}
.periodo-sel option{background:var(--card);color:var(--text)}

/* Funnel */
.funnel{display:flex;flex-direction:column;gap:6px}
.funnel-row{display:flex;align-items:center;gap:12px}
.funnel-label{font:600 12px 'Inter',sans-serif;min-width:90px;color:var(--t2)}
.funnel-bar{flex:1;height:28px;background:var(--border);border-radius:6px;overflow:hidden;position:relative}
.funnel-fill{height:100%;border-radius:6px;display:flex;align-items:center;padding-left:10px;font:700 11px 'Inter',sans-serif;color:#fff;min-width:30px;transition:width .5s}
.funnel-num{min-width:50px;text-align:right;font:700 14px 'Inter',sans-serif}

/* Score */
.score-wrap{display:flex;align-items:center;gap:8px}
.score-bar{width:50px;height:5px;background:var(--border);border-radius:3px;overflow:hidden}
.score-fill{height:100%;border-radius:3px}
.score-num{font:800 14px 'Inter',sans-serif;min-width:24px}
.score-badge{font:700 9px 'Inter',sans-serif;padding:2px 7px;border-radius:4px;text-transform:uppercase;letter-spacing:.03em}

/* Alert dot */
.alert-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px}

/* Donut container */
.donut-wrap{display:flex;align-items:center;justify-content:center;gap:24px}
.donut-canvas{width:200px;height:200px}
.donut-legend{display:flex;flex-direction:column;gap:8px}
.donut-item{display:flex;align-items:center;gap:8px;font-size:13px}
.donut-dot{width:10px;height:10px;border-radius:3px;flex-shrink:0}
.donut-val{font:700 13px 'Inter',sans-serif;margin-left:auto;font-variant-numeric:tabular-nums}

/* Comisiones */
.btn-comi{padding:6px 12px;border:1px solid var(--border);border-radius:6px;background:var(--card);color:var(--g);font:700 11px 'Inter',sans-serif;cursor:pointer;transition:all .15s;white-space:nowrap}
.btn-comi:hover{background:var(--g);color:#fff;border-color:var(--g)}

/* Grid 3 */
.grid-3{display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start}
@media(max-width:900px){.grid-3{grid-template-columns:1fr}}

@media(max-width:1100px){.kpi-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:700px){.kpi-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:500px){.kpi-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">

<!-- HEADER -->
<div class="hdr">
    <div>
        <h1>Executive Dashboard</h1>
        <div class="hdr-sub"><?= $now->format('l, d F Y · H:i') ?> · Hermosillo, Son.</div>
    </div>
    <div class="hdr-right">
        <select class="periodo-sel" onchange="location.href='/superadmin/executive?periodo='+this.value">
            <option value="mes_actual" <?= $periodo==='mes_actual'?'selected':'' ?>>Este mes</option>
            <option value="mes_ant" <?= $periodo==='mes_ant'?'selected':'' ?>>Mes anterior</option>
            <option value="3_meses" <?= $periodo==='3_meses'?'selected':'' ?>>3 meses</option>
            <option value="6_meses" <?= $periodo==='6_meses'?'selected':'' ?>>6 meses</option>
            <option value="anio" <?= $periodo==='anio'?'selected':'' ?>>Este año</option>
            <option value="todo" <?= $periodo==='todo'?'selected':'' ?>>Todo</option>
        </select>
        <div class="hdr-live"><span class="hdr-dot"></span>LIVE</div>
        <a href="/superadmin" class="hdr-back">← SuperAdmin</a>
    </div>
</div>

<!-- Botones de compensación -->
<div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap">
    <button class="comp-btn" onclick="openComp(12,'OnTime Hermosillo','hermosillo')" style="border-left:3px solid #22c55e">Compensación HMO</button>
    <button class="comp-btn" onclick="openComp(13,'OnTime Obregón','obregon')" style="border-left:3px solid #3b82f6">Compensación CEN</button>
    <button class="comp-btn" onclick="openComp(14,'OnTime Nogales','nogales')" style="border-left:3px solid #a855f7">Compensación NOG</button>
    <div style="margin-left:auto;display:flex;gap:12px;align-items:center">
        <span style="font:600 12px 'Inter',sans-serif;color:var(--g)">● <?= $comp_activas ?> activa<?= $comp_activas!=1?'s':'' ?></span>
        <span style="font:600 12px 'Inter',sans-serif;color:var(--t3)">✓ <?= $comp_usadas ?> usada<?= $comp_usadas!=1?'s':'' ?></span>
        <?php if ($compensaciones): ?>
        <button class="comp-btn" onclick="document.getElementById('compReport').style.display=document.getElementById('compReport').style.display==='none'?'block':'none'" style="font-size:11px;padding:6px 12px">Ver historial</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($compensaciones): ?>
<div id="compReport" style="display:none;margin-bottom:24px">
<div class="tbl-card">
<table>
<thead><tr><th></th><th>Cliente</th><th>Código</th><th class="r">Monto</th><th>Estado</th><th>Creada</th><th>Vence</th></tr></thead>
<tbody>
<?php foreach ($compensaciones as $cc):
    $ec = $empresas_cfg[(int)$cc['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
    $usado = (int)$cc['usos'] > 0;
    $vencido = $cc['vencimiento_fecha'] && strtotime($cc['vencimiento_fecha']) < time();
    $dias_rest = $cc['vencimiento_fecha'] ? max(0, (int)((strtotime($cc['vencimiento_fecha']) - time()) / 86400)) : null;
?>
<tr>
    <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
    <td style="font-size:12px"><?= e(str_replace('Compensación: ', '', $cc['descripcion'] ?? '—')) ?></td>
    <td style="font:700 13px 'Inter',sans-serif;letter-spacing:.06em;color:<?= $usado ? 'var(--t3)' : 'var(--g)' ?>"><?= e($cc['codigo']) ?></td>
    <td class="r mono" style="font-weight:700"><?= $cc['monto_fijo'] ? xf((float)$cc['monto_fijo']) : (float)$cc['porcentaje'].'%' ?></td>
    <td>
        <?php if ($usado): ?>
            <span style="font:700 11px 'Inter',sans-serif;color:var(--b);background:var(--b);background:rgba(59,130,246,.15);padding:3px 8px;border-radius:6px">USADO</span>
        <?php elseif ($vencido): ?>
            <span style="font:700 11px 'Inter',sans-serif;color:var(--r);background:rgba(239,68,68,.15);padding:3px 8px;border-radius:6px">VENCIDO</span>
        <?php else: ?>
            <span style="font:700 11px 'Inter',sans-serif;color:var(--g);background:rgba(34,197,94,.15);padding:3px 8px;border-radius:6px">ACTIVO · <?= $dias_rest ?>d</span>
        <?php endif; ?>
    </td>
    <td class="mono" style="font-size:11px;color:var(--t2)"><?= date('d/m/Y', strtotime($cc['created_at'])) ?></td>
    <td class="mono" style="font-size:11px;color:var(--t3)"><?= $cc['vencimiento_fecha'] ? date('d/m/Y', strtotime($cc['vencimiento_fecha'])) : '—' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endif; ?>

<!-- Modal compensación -->
<div id="compOverlay" style="display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);align-items:center;justify-content:center" onclick="if(event.target===this)closeComp()">
    <div style="background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:420px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <div style="font:700 18px 'Inter',sans-serif" id="compTitle">Compensación</div>
            <button onclick="closeComp()" style="background:none;border:none;color:var(--t3);font-size:20px;cursor:pointer">✕</button>
        </div>
        <input type="hidden" id="compEmpId">
        <input type="hidden" id="compSlug">
        <div style="margin-bottom:16px">
            <div style="font:600 12px 'Inter',sans-serif;color:var(--t2);margin-bottom:4px">Nombre del cliente</div>
            <input type="text" id="compCliente" placeholder="Nombre del cliente" style="width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font:500 14px 'Inter',sans-serif;outline:none">
        </div>
        <div style="margin-bottom:20px">
            <div style="font:600 12px 'Inter',sans-serif;color:var(--t2);margin-bottom:8px">Monto de compensación</div>
            <div style="display:flex;gap:8px">
                <button class="comp-monto" onclick="selMonto(this,1000)" data-monto="1000">$1,000</button>
                <button class="comp-monto" onclick="selMonto(this,2000)" data-monto="2000">$2,000</button>
                <button class="comp-monto" onclick="selMonto(this,4000)" data-monto="4000">$4,000</button>
            </div>
        </div>
        <button id="compGenerar" onclick="generarComp()" style="width:100%;padding:12px;border:none;border-radius:10px;background:var(--g);color:#fff;font:700 14px 'Inter',sans-serif;cursor:pointer">Generar compensación</button>
        <div id="compResult" style="display:none;margin-top:16px">
            <div style="font:600 12px 'Inter',sans-serif;color:var(--t2);margin-bottom:6px">URL de compensación</div>
            <div style="display:flex;gap:6px">
                <input type="text" id="compURL" readonly style="flex:1;padding:8px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--g);font:500 12px 'Inter',sans-serif;outline:none">
                <button onclick="navigator.clipboard.writeText(document.getElementById('compURL').value);this.textContent='✓';setTimeout(()=>this.textContent='Copiar',1500)" style="padding:8px 14px;border:none;border-radius:8px;background:var(--g);color:#fff;font:700 12px 'Inter',sans-serif;cursor:pointer">Copiar</button>
            </div>
            <div style="font-size:11px;color:var(--t3);margin-top:8px">Código: <span id="compCodigo" style="font-weight:700;color:var(--text)"></span></div>
        </div>
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-lbl">Ventas — <?= $p_label ?></div>
            <span class="kpi-badge <?= $var_ventas > 0 ? 'up' : ($var_ventas < 0 ? 'dn' : 'eq') ?>"><?= $var_ventas > 0 ? '+' : '' ?><?= $var_ventas ?>%</span>
        </div>
        <div class="kpi-val" style="color:var(--g)"><?= xm((float)$kpi_mes['ventas']) ?></div>
        <div class="kpi-sub"><b><?= $kpi_mes['num'] ?></b> ventas · Ant: <?= xm((float)$kpi_ant['ventas']) ?></div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-lbl">Cobrado hoy</div>
        </div>
        <div class="kpi-val"><?= xf($cobrado_hoy) ?></div>
        <div class="kpi-sub"><b><?= $num_pagos_hoy ?></b> pagos registrados</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-lbl">Por cobrar</div>
        </div>
        <div class="kpi-val" style="color:var(--a)"><?= xm($por_cobrar) ?></div>
        <div class="kpi-sub">Saldos pendientes</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-lbl">Pipeline activo</div>
        </div>
        <div class="kpi-val" style="color:var(--b)"><?= xm((float)$pipeline['monto']) ?></div>
        <div class="kpi-sub"><b><?= $pipeline['num'] ?></b> cotizaciones vivas</div>
    </div>
    <div class="kpi">
        <div class="kpi-top"><div class="kpi-lbl">Ticket promedio</div></div>
        <div class="kpi-val"><?= xf($ticket_global) ?></div>
        <div class="kpi-sub">Por venta</div>
    </div>
    <div class="kpi">
        <?php
        $tasa_global = (int)($funnel['enviadas'] ?? 0) > 0
            ? round((int)$funnel['aceptadas'] / (int)$funnel['enviadas'] * 100, 1) : 0;
        ?>
        <div class="kpi-top"><div class="kpi-lbl">Tasa de cierre</div></div>
        <div class="kpi-val" style="color:<?= $tasa_global >= 15 ? 'var(--g)' : ($tasa_global >= 8 ? 'var(--a)' : 'var(--r)') ?>"><?= $tasa_global ?>%</div>
        <div class="kpi-sub"><b><?= $funnel['aceptadas'] ?></b> de <b><?= $funnel['enviadas'] ?></b> cotizaciones</div>
    </div>
    <div class="kpi">
        <div class="kpi-top"><div class="kpi-lbl">Acumulado mes</div></div>
        <div class="kpi-val" style="color:var(--g)"><?= xm($acum_mes) ?></div>
        <div class="kpi-sub"><?= $now->format('F Y') ?></div>
    </div>
    <div class="kpi">
        <div class="kpi-top"><div class="kpi-lbl">Acumulado año</div></div>
        <div class="kpi-val" style="color:var(--g)"><?= xm($acum_anio) ?></div>
        <div class="kpi-sub"><?= $anio_actual ?></div>
    </div>
</div>

<!-- GRÁFICA GLOBAL -->
<div class="chart-card">
    <div class="chart-title">Tendencia de ingresos</div>
    <div class="chart-sub">Últimos 12 meses — todas las empresas</div>
    <div class="chart-canvas">
        <canvas id="trendChart"></canvas>
    </div>
</div>

<!-- GRÁFICA POR EMPRESA (tabs) -->
<div class="chart-card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:10px">
        <div class="chart-title" style="margin:0">Detalle por empresa</div>
        <div style="display:flex;gap:4px;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:3px">
            <?php foreach ($emp_sorted as $i => $es): ?>
            <button class="op-tab emp-chart-btn <?= $i===0?'on':'' ?>" data-eid="<?= $es['eid'] ?>" onclick="toggleEmpChart(this)"><?= $es['ec']['short'] ?></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div style="display:flex;gap:4px;margin-bottom:14px">
        <button class="metric-btn on" data-metric="ingresos" onclick="toggleMetric(this)" style="border-left:3px solid var(--b)">Ingresos</button>
        <button class="metric-btn" data-metric="media" onclick="toggleMetric(this)" style="border-left:3px solid #ef4444">Media</button>
        <button class="metric-btn" data-metric="variacion" onclick="toggleMetric(this)" style="border-left:3px solid #22c55e">Variación %</button>
        <button class="metric-btn" data-metric="tasa" onclick="toggleMetric(this)" style="border-left:3px solid #f59e0b">Tasa cierre</button>
    </div>
    <div class="chart-canvas" style="height:320px">
        <canvas id="empChart"></canvas>
    </div>
</div>

<!-- GRÁFICA VENTAS HISTÓRICAS -->
<?php
// Años disponibles
$anios_hist = DB::query(
    "SELECT DISTINCT anio FROM historial_mensual WHERE empresa_id IN ({$emp_ids})
     UNION
     SELECT DISTINCT YEAR(created_at) AS anio FROM ventas WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
     ORDER BY anio ASC"
);
$anios_list = array_column($anios_hist ?: [], 'anio');
$anio_sel = $_GET['anio_hist'] ?? 'todo';
if ($anio_sel !== 'todo') $anio_sel = (int)$anio_sel;
if ($anio_sel !== 'todo' && !in_array($anio_sel, $anios_list)) $anio_sel = 'todo';

// Determinar rango de meses
if ($anio_sel === 'todo') {
    $anio_min = min($anios_list ?: [date('Y')]);
    $anio_max = max($anios_list ?: [date('Y')]);
} else {
    $anio_min = $anio_sel;
    $anio_max = $anio_sel;
}

// Construir lista de meses en el rango
$hist_months = [];
for ($y = $anio_min; $y <= $anio_max; $y++) {
    for ($m = 1; $m <= 12; $m++) {
        if ($y == $anio_max && $anio_sel === 'todo' && $y == date('Y') && $m > date('n')) break;
        $hist_months[] = ['y' => $y, 'm' => $m, 'key' => sprintf('%d-%02d', $y, $m)];
    }
}

// Cargar datos: historial + ventas reales, sumados por mes
$hist_bars = [];
foreach ($hist_months as $hm) $hist_bars[$hm['key']] = 0.0;

// Historial importado
$h_all = DB::query(
    "SELECT anio, mes, SUM(ventas_monto) AS monto
     FROM historial_mensual
     WHERE empresa_id IN ({$emp_ids}) AND anio BETWEEN ? AND ?
     GROUP BY anio, mes",
    [$anio_min, $anio_max]
);
$h_meses_con_historial = [];
foreach ($h_all ?: [] as $h) {
    $k = sprintf('%d-%02d', $h['anio'], $h['mes']);
    if (isset($hist_bars[$k])) {
        $hist_bars[$k] += (float)$h['monto'];
        $h_meses_con_historial[$k] = true;
    }
}

// Ventas reales (no duplicar meses con historial)
$v_all = DB::query(
    "SELECT YEAR(created_at) AS anio, MONTH(created_at) AS mes, SUM(total) AS monto
     FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND YEAR(created_at) BETWEEN ? AND ?
     GROUP BY YEAR(created_at), MONTH(created_at)",
    [$anio_min, $anio_max]
);
foreach ($v_all ?: [] as $v) {
    $k = sprintf('%d-%02d', $v['anio'], $v['mes']);
    if (isset($hist_bars[$k]) && !isset($h_meses_con_historial[$k])) {
        $hist_bars[$k] += (float)$v['monto'];
    }
}

// Labels y datos para Chart.js — saltar meses sin datos al inicio
$hist_labels = [];
$hist_values = [];
$meses_es = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
$hist_started = false;
foreach ($hist_months as $hm) {
    $val = round($hist_bars[$hm['key']], 2);
    if (!$hist_started && $val == 0 && $anio_sel === 'todo') continue;
    $hist_started = true;
    $hist_labels[] = $meses_es[$hm['m']] . ' ' . $hm['y'];
    $hist_values[] = $val;
}

// Promedio
$hist_vals_nz = array_filter($hist_values, fn($v) => $v > 0);
$hist_promedio = count($hist_vals_nz) > 0 ? array_sum($hist_vals_nz) / count($hist_vals_nz) : 0;
$hist_prom_line = array_fill(0, count($hist_values), round($hist_promedio, 2));
$hist_total = array_sum($hist_values);
?>
<div class="chart-card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:4px">
        <div>
            <div class="chart-title">Ventas mensuales <?= $anio_sel === 'todo' ? 'históricas' : $anio_sel ?></div>
            <div class="chart-sub">Todas las empresas — total: $<?= number_format($hist_total, 0) ?> · promedio: $<?= number_format($hist_promedio, 0) ?>/mes</div>
        </div>
        <div style="display:flex;gap:4px;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:3px;flex-wrap:wrap">
            <a href="?<?= http_build_query(array_merge($_GET, ['anio_hist' => 'todo'])) ?>" class="op-tab <?= $anio_sel === 'todo' ? 'on' : '' ?>" style="text-decoration:none">Todo</a>
            <?php foreach ($anios_list as $a): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['anio_hist' => $a])) ?>" class="op-tab <?= $anio_sel === $a ? 'on' : '' ?>" style="text-decoration:none"><?= $a ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="chart-canvas" style="height:<?= $anio_sel === 'todo' ? '340' : '300' ?>px">
        <canvas id="histChart"></canvas>
    </div>
</div>

<!-- TABLA + PAGOS -->
<div class="grid-2">

<!-- Ventas por empresa -->
<div class="sec">
    <div class="sec-hdr">
        <div class="sec-title">Ventas por empresa</div>
        <div class="sec-count"><?= $p_label ?></div>
    </div>
    <div class="tbl-card">
    <table>
    <thead>
        <tr>
            <th>Empresa</th>
            <th class="r">Ventas</th>
            <th class="r">Monto</th>
            <th class="r">Anterior</th>
            <th class="r">Var</th>
            <th class="r">Tasa</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $total_act = 0; $total_ant_sum = 0;
    // Ordenar empresas por monto desc en la tabla
    $emp_tabla = [];
    foreach ($empresas_cfg as $eid => $ec) $emp_tabla[] = ['eid'=>$eid,'ec'=>$ec,'monto'=>(float)($ve_act[$eid]['monto']??0)];
    usort($emp_tabla, fn($a,$b) => $b['monto'] <=> $a['monto']);
    foreach ($emp_tabla as $et):
        $eid = $et['eid']; $ec = $et['ec'];
        $act = (float)($ve_act[$eid]['monto'] ?? 0);
        $num = (int)($ve_act[$eid]['num'] ?? 0);
        $ant = (float)($ve_ant[$eid]['monto'] ?? 0);
        $var = $ant > 0 ? round(($act - $ant) / $ant * 100, 1) : ($act > 0 ? 100 : 0);
        $ct = (int)($ce[$eid]['total'] ?? 0);
        $ca = (int)($ce[$eid]['aceptadas'] ?? 0);
        $tasa = $ct > 0 ? round($ca / $ct * 100, 1) : 0;
        $total_act += $act;
        $total_ant_sum += $ant;
    ?>
    <tr>
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span> <span style="margin-left:6px;font-weight:600"><?= $ec['nombre'] ?></span></td>
        <td class="r mono" style="font-weight:600"><?= $num ?></td>
        <td class="r mono" style="font-weight:700;color:var(--g)"><?= xf($act) ?></td>
        <td class="r mono" style="color:var(--t2)"><?= xf($ant) ?></td>
        <td class="r"><span class="var <?= $var > 0 ? 'up' : ($var < 0 ? 'dn' : 'eq') ?>"><?= abs($var) ?>%</span></td>
        <td class="r mono" style="font-weight:600;color:<?= $tasa >= 15 ? 'var(--g)' : ($tasa >= 8 ? 'var(--a)' : 'var(--r)') ?>"><?= number_format($tasa,1) ?>%</td>
    </tr>
    <?php endforeach; ?>
    <tr style="border-top:2px solid var(--border2)">
        <td style="font-weight:800">TOTAL</td>
        <td class="r mono" style="font-weight:800"><?= (int)$kpi_mes['num'] ?></td>
        <td class="r mono" style="font-weight:800;color:var(--g)"><?= xf($total_act) ?></td>
        <td class="r mono" style="font-weight:800;color:var(--t2)"><?= xf($total_ant_sum) ?></td>
        <td class="r"><span class="var <?= $var_ventas > 0 ? 'up' : ($var_ventas < 0 ? 'dn' : 'eq') ?>"><?= abs($var_ventas) ?>%</span></td>
        <td></td>
    </tr>
    </tbody>
    </table>
    </div>
</div>

<!-- Pagos del día -->
<div class="sec">
    <div class="sec-hdr">
        <div class="sec-title">Pagos de hoy</div>
        <div class="sec-count"><?= $num_pagos_hoy ?> pagos · <?= xf($cobrado_hoy) ?></div>
    </div>
    <div class="tbl-card" style="max-height:420px;overflow-y:auto">
    <table>
    <thead><tr><th></th><th>Detalle</th><th class="r">Monto</th><th class="r">Hora</th></tr></thead>
    <tbody>
    <?php if ($pagos_hoy): foreach ($pagos_hoy as $p):
        $ec = $empresas_cfg[(int)$p['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
    ?>
    <tr>
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td>
            <div style="font-weight:600;font-size:13px"><?= e($p['cliente_nombre'] ?? '—') ?></div>
            <div style="font-size:11px;color:var(--t3);margin-top:1px"><?= e(mb_substr($p['venta_titulo'] ?? $p['venta_numero'], 0, 40)) ?> · <?= e($p['forma_pago'] ?? 'efectivo') ?></div>
        </td>
        <td class="r mono" style="font-weight:700;color:var(--g)"><?= xf((float)$p['monto']) ?></td>
        <td class="r mono" style="font-size:12px;color:var(--t3)"><?= date('H:i', strtotime($p['created_at'])) ?></td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--t3)">Sin pagos hoy</td></tr>
    <?php endif; ?>
    </tbody>
    </table>
    </div>
</div>

</div><!-- /grid-2 -->

<!-- GRID 2 COLUMNAS -->
<div class="grid-3">

<!-- COLUMNA IZQUIERDA -->
<div style="display:flex;flex-direction:column;gap:14px">

<!-- Sin cobrar -->
<div class="sec" style="margin:0">
    <div class="sec-hdr">
        <div class="sec-title">Sin cobrar · <span style="color:var(--r)"><?= xf($total_sin_cobrar_cero) ?></span></div>
        <div class="sec-count"><?= count($sin_cobrar) ?> ventas</div>
    </div>
    <div class="tbl-card" style="max-height:350px;overflow-y:auto">
    <table>
    <thead><tr><th></th><th>Venta</th><th class="r">Total</th><th class="r">Días</th></tr></thead>
    <tbody>
    <?php if ($sin_cobrar): foreach ($sin_cobrar as $sc):
        $ec = $empresas_cfg[(int)$sc['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $dias = (int)$sc['dias'];
        $dias_color = $dias > 7 ? 'var(--r)' : ($dias > 3 ? 'var(--a)' : 'var(--t2)');
    ?>
    <tr>
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td>
            <div style="font-weight:600;font-size:12px"><?= e(mb_substr($sc['titulo'],0,35)) ?></div>
            <div style="font-size:11px;color:var(--t3)"><?= e($sc['cliente_nombre'] ?? '—') ?></div>
        </td>
        <td class="r mono" style="font-weight:700;color:var(--r)"><?= xf((float)$sc['total']) ?></td>
        <td class="r mono" style="font-weight:700;color:<?= $dias_color ?>">
            <span class="alert-dot" style="background:<?= $dias_color ?>"></span><?= $dias ?>d
        </td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--t3)">Todo cobrado</td></tr>
    <?php endif; ?>
    </tbody>
    </table>
    </div>
</div>

<!-- Sin abrir -->
<div class="sec" style="margin:0">
    <div class="sec-hdr">
        <div class="sec-title">Sin abrir</div>
        <div class="sec-count"><?= count($sin_abrir) ?> cotizaciones</div>
    </div>
    <div class="tbl-card" style="max-height:300px;overflow-y:auto">
    <table>
    <thead><tr><th></th><th>Cotización</th><th class="r">Total</th><th class="r">Días</th></tr></thead>
    <tbody>
    <?php if ($sin_abrir): foreach ($sin_abrir as $sa):
        $ec = $empresas_cfg[(int)$sa['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $dias = (int)$sa['dias'];
        $dias_color = $dias > 7 ? 'var(--r)' : ($dias > 3 ? 'var(--a)' : 'var(--t2)');
    ?>
    <tr>
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td>
            <div style="font-weight:600;font-size:12px"><?= e(mb_substr($sa['titulo'],0,30)) ?></div>
            <div style="font-size:10px;color:var(--t3)"><?= e($sa['numero']) ?></div>
        </td>
        <td class="r mono" style="font-weight:600"><?= xf((float)$sa['total']) ?></td>
        <td class="r mono" style="font-weight:700;color:<?= $dias_color ?>">
            <span class="alert-dot" style="background:<?= $dias_color ?>"></span><?= $dias ?>d
        </td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--t3)">Todas abiertas</td></tr>
    <?php endif; ?>
    </tbody>
    </table>
    </div>
</div>

<!-- Ventas con saldo -->
<div class="sec" style="margin:0">
    <div class="sec-hdr">
        <div class="sec-title">Ventas con saldo · <span style="color:var(--a)"><?= xf($total_sin_cobrar) ?></span></div>
        <div class="sec-count"><?= count($sin_pagos) ?> ventas</div>
    </div>
    <div class="tbl-card" style="max-height:300px;overflow-y:auto">
    <table>
    <thead><tr><th></th><th>Venta</th><th class="r">Pendiente</th><th class="r">Días</th></tr></thead>
    <tbody>
    <?php if ($sin_pagos): foreach ($sin_pagos as $sp):
        $ec = $empresas_cfg[(int)$sp['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $dias = (int)$sp['dias'];
        $dias_color = $dias > 7 ? 'var(--r)' : ($dias > 3 ? 'var(--a)' : 'var(--t2)');
    ?>
    <tr>
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td>
            <div style="font-weight:600;font-size:12px"><?= e(mb_substr($sp['titulo'],0,30)) ?></div>
            <div style="font-size:10px;color:var(--t3)"><?= e($sp['cliente_nombre'] ?? '—') ?></div>
        </td>
        <td class="r mono" style="font-weight:700;color:var(--a)"><?= xf((float)$sp['saldo']) ?></td>
        <td class="r mono" style="font-weight:700;color:<?= $dias_color ?>">
            <span class="alert-dot" style="background:<?= $dias_color ?>"></span><?= $dias ?>d
        </td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--t3)">Todo cobrado</td></tr>
    <?php endif; ?>
    </tbody>
    </table>
    </div>
</div>

</div><!-- /col izquierda -->

<!-- COLUMNA DERECHA: Asesores -->
<div class="sec" style="margin:0">
    <div class="sec-hdr">
        <div class="sec-title">Asesores</div>
        <div class="sec-count"><?= count($asesores) ?> activos</div>
    </div>
    <div class="tbl-card" style="max-height:350px;overflow-y:auto">
    <table>
    <thead><tr><th>Asesor</th><th class="r">Ventas</th><th class="r">Score</th></tr></thead>
    <tbody>
    <?php foreach ($asesores as $a):
        $ec = $empresas_cfg[(int)$a['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $vad = $va_asesor[(int)$a['id']] ?? ['num'=>0,'monto'=>0];
        $score = (int)($a['score'] ?? 0);
        $nivel = $a['nivel'] ?? 'nuevo';
        $nc = match($nivel) { 'top'=>'var(--g)', 'activo'=>'var(--b)', 'regular'=>'var(--a)', 'bajo'=>'var(--r)', default=>'var(--t3)' };
    ?>
    <tr>
        <td>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="tag" style="background:<?= $ec['color'] ?>;font-size:8px;min-width:28px;padding:3px 6px"><?= $ec['short'] ?></span>
                <div>
                    <div style="font-weight:600;font-size:13px"><?= e($a['nombre']) ?></div>
                    <div style="font-size:11px;color:var(--t3)"><?= xf((float)$vad['monto']) ?></div>
                </div>
            </div>
        </td>
        <td class="r mono" style="font-weight:700"><?= (int)$vad['num'] ?></td>
        <td class="r">
            <div class="score-wrap" style="justify-content:flex-end">
                <div class="score-bar"><div class="score-fill" style="width:<?= $score ?>%;background:<?= $nc ?>"></div></div>
                <span class="score-num" style="color:<?= $nc ?>"><?= $score ?></span>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>

</div><!-- /grid-3 -->

<?php
$sorted = [];
foreach ($empresas_cfg as $eid => $ec) $sorted[] = ['eid'=>$eid,'nombre'=>$ec['nombre'],'short'=>$ec['short'],'color'=>$ec['color'],'monto'=>(float)($ve_act[$eid]['monto']??0)];
usort($sorted, fn($a,$b) => $b['monto'] <=> $a['monto']);
?>

<!-- Embudo + Distribución -->
<div class="grid-3">

<div class="sec" style="margin:0">
    <div class="sec-hdr"><div class="sec-title">Embudo de conversión</div></div>
    <div class="tbl-card" style="padding:24px">
    <?php
    $f_env = (int)($funnel['enviadas'] ?? 0);
    $f_vis = (int)($funnel['vistas'] ?? 0);
    $f_ace = (int)($funnel['aceptadas'] ?? 0);
    $f_rec = (int)($funnel['rechazadas'] ?? 0);
    $f_max = max($f_env, 1);
    ?>
    <div class="funnel">
        <div class="funnel-row">
            <div class="funnel-label">Enviadas</div>
            <div class="funnel-bar"><div class="funnel-fill" style="width:100%;background:var(--b)"><?= $f_env ?></div></div>
            <div class="funnel-num" style="color:var(--b)"><?= $f_env ?></div>
        </div>
        <div class="funnel-row">
            <div class="funnel-label">Abiertas</div>
            <div class="funnel-bar"><div class="funnel-fill" style="width:<?= $f_env > 0 ? round($f_vis/$f_max*100) : 0 ?>%;background:var(--a)"><?= $f_vis ?></div></div>
            <div class="funnel-num" style="color:var(--a)"><?= $f_vis ?></div>
        </div>
        <div class="funnel-row">
            <div class="funnel-label">Aceptadas</div>
            <div class="funnel-bar"><div class="funnel-fill" style="width:<?= $f_env > 0 ? round($f_ace/$f_max*100) : 0 ?>%;background:var(--g)"><?= $f_ace ?></div></div>
            <div class="funnel-num" style="color:var(--g)"><?= $f_ace ?></div>
        </div>
        <div class="funnel-row">
            <div class="funnel-label">Rechazadas</div>
            <div class="funnel-bar"><div class="funnel-fill" style="width:<?= $f_env > 0 ? round($f_rec/$f_max*100) : 0 ?>%;background:var(--r)"><?= $f_rec ?></div></div>
            <div class="funnel-num" style="color:var(--r)"><?= $f_rec ?></div>
        </div>
    </div>
    <div style="margin-top:16px;display:flex;gap:16px;justify-content:center">
        <div style="text-align:center">
            <div style="font:800 20px 'Inter',sans-serif;color:var(--g)"><?= $f_env > 0 ? round($f_vis/$f_env*100) : 0 ?>%</div>
            <div style="font-size:10px;color:var(--t3);text-transform:uppercase;letter-spacing:.05em">Apertura</div>
        </div>
        <div style="text-align:center">
            <div style="font:800 20px 'Inter',sans-serif;color:var(--b)"><?= $f_env > 0 ? round($f_ace/$f_env*100) : 0 ?>%</div>
            <div style="font-size:10px;color:var(--t3);text-transform:uppercase;letter-spacing:.05em">Cierre</div>
        </div>
        <div style="text-align:center">
            <div style="font:800 20px 'Inter',sans-serif;color:var(--r)"><?= $f_env > 0 ? round($f_rec/$f_env*100) : 0 ?>%</div>
            <div style="font-size:10px;color:var(--t3);text-transform:uppercase;letter-spacing:.05em">Rechazo</div>
        </div>
    </div>
    </div>
</div>

<!-- DER: Distribución -->
<div class="sec" style="margin:0">
    <div class="sec-hdr"><div class="sec-title">Distribución</div></div>
    <div class="tbl-card" style="padding:24px">
        <div class="donut-wrap">
            <div class="donut-canvas"><canvas id="donutChart"></canvas></div>
            <div class="donut-legend">
            <?php foreach ($sorted as $s): ?>
            <div class="donut-item">
                <span class="donut-dot" style="background:<?= $s['color'] ?>"></span>
                <span><?= $s['nombre'] ?></span>
                <span class="donut-val" style="color:var(--g)"><?= xm($s['monto']) ?></span>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

</div><!-- /grid-3 -->

<!-- ══ TABS OPERACIONES ══════════════════════════════════════ -->
<div class="sec">
    <div style="display:flex;gap:4px;margin-bottom:14px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:4px;width:fit-content">
        <button class="op-tab on" onclick="opTab('pagos',this)">Pagos (<?= count($pagos_periodo) ?>)</button>
        <button class="op-tab" onclick="opTab('porcobrar',this)">Por cobrar (<?= count($ventas_por_cobrar) ?>)</button>
        <button class="op-tab" onclick="opTab('cobradas',this)">Cobradas (<?= count($ventas_cobradas) ?>)</button>
    </div>

    <!-- Tab: Pagos -->
    <div class="op-panel on" id="op-pagos">
    <div class="sec-hdr" style="margin-bottom:8px">
        <div style="font-size:13px;color:var(--t2)">Total: <b style="color:var(--g)"><?= xf($total_pagos_periodo) ?></b> — <?= $p_label ?></div>
        <div style="display:flex;gap:6px;align-items:center">
            <select class="periodo-sel" id="filterPagos" onchange="filterTable('pagos',this.value)" style="font-size:11px;padding:5px 10px">
                <option value="">Todas</option>
                <?php foreach ($empresas_cfg as $eid => $ec): ?>
                <option value="<?= $eid ?>"><?= $ec['short'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="tbl-card" style="max-height:500px;overflow-y:auto">
    <table id="tbl-pagos">
    <thead><tr><th></th><th>Cliente</th><th>Detalle</th><th>Forma</th><th class="r">Monto</th><th class="r">Fecha</th></tr></thead>
    <tbody>
    <?php foreach ($pagos_periodo as $pp):
        $ec = $empresas_cfg[(int)$pp['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
    ?>
    <tr data-emp="<?= $pp['empresa_id'] ?>">
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td style="font-weight:600;font-size:12px"><?= e($pp['cliente_nombre'] ?? '—') ?></td>
        <td style="font-size:11px;color:var(--t2)"><?= e(mb_substr($pp['venta_titulo'] ?? $pp['venta_numero'],0,40)) ?></td>
        <td style="font-size:11px;color:var(--t3)"><?= e($pp['forma_pago'] ?? 'efectivo') ?></td>
        <td class="r mono" style="font-weight:700;color:var(--g)"><?= xf((float)$pp['monto']) ?></td>
        <td class="r mono" style="font-size:12px;color:var(--t2)"><?= date('d/m', strtotime($pp['fecha'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    </div>

    <!-- Tab: Por cobrar -->
    <div class="op-panel" id="op-porcobrar">
    <div class="sec-hdr" style="margin-bottom:8px">
        <div style="font-size:13px;color:var(--t2)">Saldo pendiente: <b style="color:var(--a)"><?= xf($total_por_cobrar_lista) ?></b></div>
        <select class="periodo-sel" id="filterPorcobrar" onchange="filterTable('porcobrar',this.value)" style="font-size:11px;padding:5px 10px">
            <option value="">Todas</option>
            <?php foreach ($empresas_cfg as $eid => $ec): ?>
            <option value="<?= $eid ?>"><?= $ec['short'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="tbl-card" style="max-height:500px;overflow-y:auto">
    <table id="tbl-porcobrar">
    <thead><tr><th></th><th>Venta</th><th>Cliente</th><th class="r">Total</th><th class="r">Pagado</th><th class="r">Saldo</th><th class="r">Fecha</th></tr></thead>
    <tbody>
    <?php foreach ($ventas_por_cobrar as $vpc):
        $ec = $empresas_cfg[(int)$vpc['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $pct_pagado = (float)$vpc['total'] > 0 ? round((float)$vpc['pagado'] / (float)$vpc['total'] * 100) : 0;
    ?>
    <tr data-emp="<?= $vpc['empresa_id'] ?>">
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td style="font-size:12px;font-weight:600"><?= e(mb_substr($vpc['titulo'],0,35)) ?><br><span style="color:var(--t3);font-size:10px"><?= e($vpc['numero']) ?></span></td>
        <td style="font-size:12px"><?= e($vpc['cliente_nombre'] ?? '—') ?></td>
        <td class="r mono" style="font-size:12px"><?= xf((float)$vpc['total']) ?></td>
        <td class="r mono" style="font-size:12px;color:var(--g)"><?= xf((float)$vpc['pagado']) ?> <span style="color:var(--t3);font-size:10px"><?= $pct_pagado ?>%</span></td>
        <td class="r mono" style="font-weight:700;color:var(--a)"><?= xf((float)$vpc['saldo']) ?></td>
        <td class="r mono" style="font-size:11px;color:var(--t2)"><?= date('d/m/Y', strtotime($vpc['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    </div>

    <!-- Tab: Cobradas -->
    <div class="op-panel" id="op-cobradas">
    <div class="sec-hdr" style="margin-bottom:8px">
        <div style="font-size:13px;color:var(--t2)">Total cobrado: <b style="color:var(--g)"><?= xf($total_cobradas) ?></b> — <?= $p_label ?></div>
        <select class="periodo-sel" id="filterCobradas" onchange="filterTable('cobradas',this.value)" style="font-size:11px;padding:5px 10px">
            <option value="">Todas</option>
            <?php foreach ($empresas_cfg as $eid => $ec): ?>
            <option value="<?= $eid ?>"><?= $ec['short'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="tbl-card" style="max-height:500px;overflow-y:auto">
    <table id="tbl-cobradas">
    <thead><tr><th></th><th>Venta</th><th>Cliente</th><th class="r">Total</th><th class="r">Fecha</th></tr></thead>
    <tbody>
    <?php foreach ($ventas_cobradas as $vc):
        $ec = $empresas_cfg[(int)$vc['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
    ?>
    <tr data-emp="<?= $vc['empresa_id'] ?>">
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td style="font-size:12px;font-weight:600"><?= e(mb_substr($vc['titulo'],0,35)) ?></td>
        <td style="font-size:12px"><?= e($vc['cliente_nombre'] ?? '—') ?></td>
        <td class="r mono" style="font-weight:700;color:var(--g)"><?= xf((float)$vc['total']) ?></td>
        <td class="r mono" style="font-size:12px;color:var(--t2)"><?= date('d/m/Y', strtotime($vc['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    </div>
</div>

<!-- ÚLTIMO LOGIN POR ASESOR -->
<?php
$logins_asesores = DB::query(
    "SELECT u.id, u.nombre, u.email, u.empresa_id, u.activo,
            GREATEST(
                COALESCE(u.ultimo_login, '2000-01-01'),
                COALESCE((SELECT MAX(us2.created_at) FROM user_sessions us2 WHERE us2.usuario_id = u.id), '2000-01-01'),
                COALESCE((SELECT MAX(created_at) FROM cotizaciones WHERE usuario_id = u.id OR vendedor_id = u.id), '2000-01-01'),
                COALESCE((SELECT MAX(created_at) FROM recibos WHERE usuario_id = u.id), '2000-01-01')
            ) AS ultima_actividad
     FROM usuarios u
     WHERE u.empresa_id IN ({$emp_ids}) AND u.rol = 'asesor'
     ORDER BY ultima_actividad DESC"
);
?>
<div class="sec" style="margin-top:28px">
    <div class="sec-hdr">
        <div class="sec-title">Ultima actividad por asesor</div>
        <div class="sec-count"><?= count($logins_asesores) ?> asesores</div>
    </div>
    <div class="tbl-card">
    <table>
    <thead><tr><th></th><th>Asesor</th><th>Email</th><th>Estado</th><th class="r">Ultima actividad</th><th class="r">Hace</th></tr></thead>
    <tbody>
    <?php foreach ($logins_asesores as $la):
        $ec = $empresas_cfg[(int)$la['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $ultimo = $la['ultima_actividad'];
        if ($ultimo && $ultimo <= '2000-01-02') $ultimo = null;
        if ($ultimo) {
            $diff = (int)((time() - strtotime($ultimo)) / 3600);
            if ($diff < 1) $hace = 'ahora';
            elseif ($diff < 24) $hace = $diff . 'h';
            elseif ($diff < 720) $hace = (int)($diff/24) . 'd';
            else $hace = (int)($diff/720) . 'mes';
            $hace_color = $diff < 24 ? 'var(--g)' : ($diff < 168 ? 'var(--a)' : 'var(--r)');
        } else {
            $hace = 'nunca';
            $hace_color = 'var(--r)';
        }
    ?>
    <tr>
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td style="font-weight:600"><?= e($la['nombre']) ?></td>
        <td style="font-size:12px;color:var(--t2)"><?= e($la['email'] ?? '—') ?></td>
        <td>
            <?php if ($la['activo']): ?>
            <span style="font:700 10px 'Inter',sans-serif;color:var(--g);background:rgba(34,197,94,.15);padding:3px 8px;border-radius:6px">ACTIVO</span>
            <?php else: ?>
            <span style="font:700 10px 'Inter',sans-serif;color:var(--r);background:rgba(239,68,68,.15);padding:3px 8px;border-radius:6px">INACTIVO</span>
            <?php endif; ?>
        </td>
        <td class="r mono" style="font-size:12px;color:var(--t2)"><?= $ultimo ? date('d/m/Y H:i', strtotime($ultimo)) : '—' ?></td>
        <td class="r" style="font:700 13px 'Inter',sans-serif;color:<?= $hace_color ?>"><?= $hace ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>

<!-- MONITOR: Sesiones internas no filtradas -->
<?php
$fugas = DB::query(
    "SELECT qs.id, qs.cotizacion_id, qs.ip, qs.visitor_id, qs.created_at, qs.es_interno,
            c.empresa_id, c.titulo,
            CASE WHEN ri.id IS NOT NULL THEN 'IP' ELSE '' END AS match_ip,
            CASE WHEN vi.id IS NOT NULL THEN 'VID' ELSE '' END AS match_vid
     FROM quote_sessions qs
     JOIN cotizaciones c ON c.id = qs.cotizacion_id
     LEFT JOIN radar_ips_internas ri ON ri.empresa_id = c.empresa_id AND ri.ip = qs.ip
     LEFT JOIN radar_visitors_internos vi ON vi.empresa_id = c.empresa_id AND vi.visitor_id = qs.visitor_id
     WHERE c.empresa_id IN ({$emp_ids})
       AND (ri.id IS NOT NULL OR vi.id IS NOT NULL)
       AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     ORDER BY qs.es_interno ASC, qs.created_at DESC LIMIT 20"
);
$fugas_activas = array_filter($fugas, fn($f) => !(int)$f['es_interno']);
$fugas_limpias = array_filter($fugas, fn($f) => (int)$f['es_interno']);
?>
<div class="sec" style="margin-top:28px">
    <div class="sec-hdr">
        <div class="sec-title">Monitor de filtrado <?php
            if (!$fugas) echo '<span style="color:var(--g)">✓ Limpio</span>';
            elseif ($fugas_activas) echo '<span style="color:var(--r)">⚠ ' . count($fugas_activas) . ' fugas activas</span>';
            else echo '<span style="color:var(--g)">✓ ' . count($fugas_limpias) . ' detectadas y limpiadas</span>';
        ?></div>
        <div class="sec-count">últimos 7 días</div>
    </div>
    <?php if ($fugas): ?>
    <div class="tbl-card">
    <table>
    <thead><tr><th></th><th>Cotización</th><th>IP</th><th>Visitor ID</th><th>Detectado por</th><th>Estado</th><th class="r">Fecha</th></tr></thead>
    <tbody>
    <?php foreach ($fugas as $fg):
        $ec = $empresas_cfg[(int)$fg['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $matched = trim($fg['match_ip'] . ($fg['match_ip'] && $fg['match_vid'] ? '+' : '') . $fg['match_vid']);
        $limpia = (int)$fg['es_interno'];
    ?>
    <tr style="<?= $limpia ? 'opacity:.5' : '' ?>">
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td style="font-size:12px"><?= e(mb_substr($fg['titulo'],0,30)) ?></td>
        <td class="mono" style="font-size:11px;color:var(--t2)"><?= e($fg['ip']) ?></td>
        <td class="mono" style="font-size:10px;color:var(--t3)"><?= e(substr($fg['visitor_id'] ?? 'NULL',0,12)) ?>…</td>
        <td><span style="font:700 10px 'Inter',sans-serif;color:var(--r);background:rgba(239,68,68,.15);padding:2px 8px;border-radius:4px"><?= $matched ?></span></td>
        <td><?php if ($limpia): ?><span style="font:700 10px 'Inter',sans-serif;color:var(--g);background:rgba(34,197,94,.15);padding:2px 8px;border-radius:4px">Limpiada</span><?php else: ?><span style="font:700 10px 'Inter',sans-serif;color:var(--r);background:rgba(239,68,68,.15);padding:2px 8px;border-radius:4px">Activa</span><?php endif; ?></td>
        <td class="r mono" style="font-size:11px;color:var(--t2)"><?= date('d/m H:i', strtotime($fg['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
    <div class="tbl-card" style="padding:24px;text-align:center;color:var(--g);font:600 14px 'Inter',sans-serif">✓ Sin fugas — el filtrado funciona correctamente</div>
    <?php endif; ?>
</div>

<!-- Alerta Competencia por empresa -->
<?php
$comp_por_empresa = [];
foreach ($empresas_cfg as $eid => $ecfg) {
    $comp_cnt = (int)DB::val(
        "SELECT COUNT(DISTINCT qs.visitor_id)
         FROM quote_sessions qs
         JOIN cotizaciones c ON c.id = qs.cotizacion_id
         WHERE c.empresa_id = ?
           AND qs.visitor_id IS NOT NULL AND qs.visitor_id != ''
           AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
           AND (qs.visible_ms > 3000 OR qs.scroll_max > 10)
           AND qs.visitor_id NOT IN (SELECT visitor_id FROM radar_visitors_internos WHERE empresa_id = ?)
         GROUP BY qs.visitor_id
         HAVING COUNT(DISTINCT c.cliente_id) > 1",
        [$eid, $eid]
    ) ? DB::val(
        "SELECT COUNT(*) FROM (
            SELECT qs.visitor_id
            FROM quote_sessions qs
            JOIN cotizaciones c ON c.id = qs.cotizacion_id
            WHERE c.empresa_id = ?
              AND qs.visitor_id IS NOT NULL AND qs.visitor_id != ''
              AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
              AND (qs.visible_ms > 3000 OR qs.scroll_max > 10)
              AND qs.visitor_id NOT IN (SELECT visitor_id FROM radar_visitors_internos WHERE empresa_id = ?)
            GROUP BY qs.visitor_id
            HAVING COUNT(DISTINCT c.cliente_id) > 1
        ) sub",
        [$eid, $eid]
    ) : 0;
    $comp_ip_cnt = (int)(DB::val(
        "SELECT COUNT(*) FROM (
            SELECT qs.ip
            FROM quote_sessions qs
            JOIN cotizaciones c ON c.id = qs.cotizacion_id
            WHERE c.empresa_id = ?
              AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
              AND (qs.visible_ms > 3000 OR qs.scroll_max > 10)
              AND qs.ip NOT IN (SELECT ip FROM radar_ips_internas WHERE empresa_id = ?)
            GROUP BY qs.ip
            HAVING COUNT(DISTINCT c.cliente_id) > 1
        ) sub",
        [$eid, $eid]
    ) ?: 0);
    if ($comp_cnt || $comp_ip_cnt) {
        $comp_por_empresa[$eid] = ['user' => (int)$comp_cnt, 'ip' => (int)$comp_ip_cnt];
    }
}
?>
<div class="sec" style="margin-top:28px">
    <div class="sec-hdr">
        <div class="sec-title">⚠️ Posible Competencia <?= $comp_por_empresa ? '<span style="color:var(--r)">' . count($comp_por_empresa) . ' empresas</span>' : '<span style="color:var(--g)">✓ Sin alertas</span>' ?></div>
        <div class="sec-count">últimos 6 meses</div>
    </div>
    <?php if ($comp_por_empresa): ?>
    <div class="tbl-card">
    <table>
    <thead><tr><th></th><th>Empresa</th><th class="r">Por Usuario</th><th class="r">Por IP</th><th class="r">Total</th></tr></thead>
    <tbody>
    <?php foreach ($comp_por_empresa as $eid => $cc):
        $ec = $empresas_cfg[$eid];
    ?>
    <tr>
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td><?= e($ec['nombre']) ?></td>
        <td class="r"><?= $cc['user'] ?: '—' ?></td>
        <td class="r"><?= $cc['ip'] ?: '—' ?></td>
        <td class="r" style="font-weight:700;color:var(--r)"><?= $cc['user'] + $cc['ip'] ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
    <div class="tbl-card" style="padding:24px;text-align:center;color:var(--g);font:600 14px 'Inter',sans-serif">✓ Sin actividad sospechosa en los últimos 6 meses</div>
    <?php endif; ?>
</div>

<!-- ─── COMISIONES ─────────────────────────────────────────── -->
<?php
$total_comi_empresas = count($comi_por_empresa);
$total_comi_rows = count($comisiones);
?>
<div class="sec" style="margin-top:28px" id="sec-comisiones">
    <div class="sec-hdr">
        <div class="sec-title">💰 Comisiones por pagar</div>
        <div class="sec-count">
            <span id="comi-pendientes-count"><?= $total_comi_rows ?></span> ventas pagadas ·
            <?= $total_comi_empresas ?> empresa<?= $total_comi_empresas === 1 ? '' : 's' ?> · <?= $p_label ?>
        </div>
    </div>

    <?php if ($total_comi_rows === 0): ?>
    <div class="tbl-card" style="padding:24px;text-align:center;color:var(--t2);font:600 14px 'Inter',sans-serif">
        Sin ventas pagadas en el periodo seleccionado
    </div>
    <?php else: ?>
        <?php foreach ($comi_por_empresa as $eid => $filas):
            $ec = $empresas_cfg[$eid];
            $sum_contrato = 0; $sum_pagado = 0;
            foreach ($filas as $f) {
                $sum_contrato += (float)$f['total'];
                $sum_pagado   += (float)$f['pagado_recibos'];
            }
        ?>
        <div class="comi-empresa" data-empresa="<?= $eid ?>" style="margin-bottom:18px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span>
                <div style="font:700 14px 'Inter',sans-serif"><?= e($ec['nombre']) ?></div>
                <div style="font-size:12px;color:var(--t2)">
                    · <span class="comi-empresa-count"><?= count($filas) ?></span> pendientes
                    · <?= xf($sum_pagado) ?> cobrado
                </div>
            </div>
            <div class="tbl-card">
            <table>
            <thead><tr>
                <th>Asesor</th>
                <th>Cliente / Venta</th>
                <th class="r">Contrato</th>
                <th class="r">Total pagado</th>
                <th class="r">Último pago</th>
                <th class="r">Acción</th>
            </tr></thead>
            <tbody>
            <?php foreach ($filas as $f):
                $asesor = $f['asesor_nombre'] ?: '—';
                $cliente = $f['cliente_nombre'] ?: 'Sin cliente';
                $titulo = $f['titulo'] ?: ('Venta #' . $f['numero']);
                $contrato = (float)$f['total'];
                $pag_tot  = (float)$f['pagado_recibos'];
                $fecha_up = $f['ultimo_pago'] ? date('d/m/Y', strtotime($f['ultimo_pago'])) : '—';
            ?>
            <tr class="comi-row" data-comi-id="<?= (int)$f['id'] ?>" data-empresa="<?= $eid ?>">
                <td style="font-weight:600"><?= e($asesor) ?></td>
                <td>
                    <div style="font-weight:600"><?= e($cliente) ?></div>
                    <div style="font-size:11px;color:var(--t3)"><?= e($titulo) ?> · #<?= e($f['numero']) ?></div>
                </td>
                <td class="r mono"><?= xf($contrato) ?></td>
                <td class="r mono" style="font-weight:700;color:var(--g)"><?= xf($pag_tot) ?></td>
                <td class="r" style="font-size:12px;color:var(--t2)"><?= $fecha_up ?></td>
                <td class="r">
                    <button class="btn-comi" type="button" onclick="marcarComision(<?= (int)$f['id'] ?>)">
                        Comisión pagada
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</div><!-- /wrap -->

<script>
// ─── Comisiones (persistencia en localStorage) ─────────────
const COMI_KEY = 'cz_comisiones_pagadas';
function comiLoad() {
    try { return JSON.parse(localStorage.getItem(COMI_KEY) || '{}') || {}; }
    catch(e) { return {}; }
}
function comiSave(map) { localStorage.setItem(COMI_KEY, JSON.stringify(map)); }
function comiRefresh() {
    const map = comiLoad();
    let totalVisibles = 0;
    const countsPorEmpresa = {};
    document.querySelectorAll('.comi-row').forEach(row => {
        const id = row.dataset.comiId;
        const eid = row.dataset.empresa;
        if (map[id]) {
            row.style.display = 'none';
        } else {
            row.style.display = '';
            totalVisibles++;
            countsPorEmpresa[eid] = (countsPorEmpresa[eid] || 0) + 1;
        }
    });
    const elTotal = document.getElementById('comi-pendientes-count');
    if (elTotal) elTotal.textContent = totalVisibles;
    document.querySelectorAll('.comi-empresa').forEach(bloque => {
        const eid = bloque.dataset.empresa;
        const n = countsPorEmpresa[eid] || 0;
        bloque.style.display = n === 0 ? 'none' : '';
        const c = bloque.querySelector('.comi-empresa-count');
        if (c) c.textContent = n;
    });
}
function marcarComision(id) {
    if (!confirm('¿Marcar esta comisión como pagada? Ya no aparecerá en el listado.')) return;
    const map = comiLoad();
    map[id] = { t: Date.now() };
    comiSave(map);
    comiRefresh();
}
document.addEventListener('DOMContentLoaded', comiRefresh);

<?php // $emp_sorted ya definido arriba ?>
// ─── Trend Chart ────────────────────────────────────────────
new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [
            <?php foreach ($emp_sorted as $es):
                $eid = $es['eid']; $ec = $es['ec'];
                $data = [];
                foreach ($meses_12 as $m) $data[] = $trend[$eid][$m] ?? 0;
            ?>
            {
                label: '<?= $ec['nombre'] ?>',
                data: <?= json_encode($data) ?>,
                borderColor: '<?= $ec['color'] ?>',
                backgroundColor: '<?= $ec['color'] ?>18',
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: '<?= $ec['color'] ?>',
                tension: 0.3,
                fill: false
            },
            <?php endforeach; ?>
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: '#a1a1aa',
                    font: { family: 'Inter', size: 11, weight: '600' },
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 16
                }
            },
            tooltip: {
                backgroundColor: '#18181b',
                borderColor: '#3f3f46',
                borderWidth: 1,
                titleFont: { family: 'Inter', size: 12, weight: '700' },
                bodyFont: { family: 'Inter', size: 12 },
                padding: 12,
                cornerRadius: 8,
                itemSort: function(a, b) { return b.parsed.y - a.parsed.y; },
                callbacks: {
                    label: function(c) {
                        return c.dataset.label + ': $' + c.parsed.y.toLocaleString('en-US', {maximumFractionDigits:0});
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { color: '#27272a', drawBorder: false },
                ticks: { color: '#52525b', font: { family: 'Inter', size: 10 } }
            },
            y: {
                grid: { color: '#27272a', drawBorder: false },
                ticks: {
                    color: '#52525b',
                    font: { family: 'Inter', size: 10 },
                    callback: function(v) {
                        if (v >= 1000000) return '$' + (v/1000000).toFixed(1) + 'M';
                        if (v >= 1000) return '$' + (v/1000).toFixed(0) + 'K';
                        return '$' + v;
                    }
                }
            }
        }
    }
});

// ─── Donut Chart ────────────────────────────────────────────
new Chart(document.getElementById('donutChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($sorted, 'short')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($sorted, 'monto')) ?>,
            backgroundColor: <?= json_encode(array_column($sorted, 'color')) ?>,
            borderColor: '#18181b',
            borderWidth: 3,
            hoverBorderColor: '#3f3f46'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#18181b',
                borderColor: '#3f3f46',
                borderWidth: 1,
                titleFont: { family: 'Inter', size: 12, weight: '700' },
                bodyFont: { family: 'Inter', size: 12 },
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: function(c) {
                        return c.label + ': $' + c.parsed.toLocaleString('en-US', {maximumFractionDigits:0});
                    }
                }
            }
        }
    }
});
// ─── Gráfica por empresa ────────────────────────────────────
const empData = <?= json_encode($emp_chart_data) ?>;
const chartLabels = <?= json_encode($chart_labels) ?>;
let empChartInstance = null;

function toggleEmpChart(btn) {
    btn.classList.toggle('on');
    rebuildEmpChart();
}
function toggleMetric(btn) {
    btn.classList.toggle('on');
    rebuildEmpChart();
}

function rebuildEmpChart() {
    const activeEmps = [];
    document.querySelectorAll('.emp-chart-btn.on').forEach(b => activeEmps.push(parseInt(b.dataset.eid)));

    const metrics = {};
    document.querySelectorAll('.metric-btn.on').forEach(b => metrics[b.dataset.metric] = true);

    if (empChartInstance) empChartInstance.destroy();
    if (activeEmps.length === 0) { empChartInstance = null; return; }

    const datasets = [];
    let needY1 = false;

    activeEmps.forEach(eid => {
        const d = empData[eid];
        if (!d) return;

        if (metrics.ingresos) {
            datasets.push({
                label: d.short + ' Ingresos',
                data: d.ventas,
                borderColor: d.color,
                backgroundColor: activeEmps.length === 1 ? d.color + '12' : 'transparent',
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: d.color,
                tension: 0.3,
                fill: activeEmps.length === 1
            });
        }

        if (metrics.media) {
            datasets.push({
                label: d.short + ' Media $' + Math.round(d.media).toLocaleString(),
                data: Array(12).fill(d.media),
                borderColor: d.color,
                borderWidth: 1.5,
                borderDash: [8, 4],
                pointRadius: 0,
                fill: false
            });
        }

        if (metrics.variacion) {
            needY1 = true;
            datasets.push({
                label: d.short + ' Var %',
                data: d.variacion,
                borderColor: d.color,
                borderWidth: 2,
                borderDash: [3, 3],
                pointRadius: 3,
                pointBackgroundColor: d.color,
                pointBorderColor: '#22c55e',
                pointBorderWidth: 2,
                tension: 0.3,
                fill: false,
                yAxisID: 'y1'
            });
        }

        if (metrics.tasa) {
            needY1 = true;
            datasets.push({
                label: d.short + ' Tasa %',
                data: d.tasa,
                borderColor: d.color,
                borderWidth: 2,
                borderDash: [6, 3],
                pointRadius: 3,
                pointBackgroundColor: d.color,
                pointBorderColor: '#f59e0b',
                pointBorderWidth: 2,
                tension: 0.3,
                fill: false,
                yAxisID: 'y1'
            });
        }
    });

    // Si solo hay métricas de % sin ingresos, ocultar eje Y izquierdo
    const showY = metrics.ingresos || metrics.media;

    empChartInstance = new Chart(document.getElementById('empChart').getContext('2d'), {
        type: 'line',
        data: { labels: chartLabels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { color: '#a1a1aa', font: { family: 'Inter', size: 11, weight: '600' }, usePointStyle: true, pointStyle: 'circle', padding: 12 }
                },
                tooltip: {
                    backgroundColor: '#18181b', borderColor: '#3f3f46', borderWidth: 1,
                    titleFont: { family: 'Inter', size: 12, weight: '700' },
                    bodyFont: { family: 'Inter', size: 12 }, padding: 12, cornerRadius: 8,
                    callbacks: {
                        label: function(c) {
                            if (c.dataset.yAxisID === 'y1') return c.dataset.label + ': ' + c.parsed.y.toFixed(1) + '%';
                            if (c.dataset.label.includes('Media')) return c.dataset.label;
                            return c.dataset.label + ': $' + c.parsed.y.toLocaleString('en-US', {maximumFractionDigits:0});
                        }
                    }
                }
            },
            scales: {
                x: { grid: { color: '#27272a' }, ticks: { color: '#52525b', font: { family: 'Inter', size: 10 } } },
                y: {
                    display: showY,
                    position: 'left', grid: { color: '#27272a' },
                    ticks: { color: '#52525b', font: { family: 'Inter', size: 10 },
                        callback: function(v) { return v >= 1000000 ? '$'+(v/1000000).toFixed(1)+'M' : v >= 1000 ? '$'+(v/1000).toFixed(0)+'K' : '$'+v; }
                    }
                },
                y1: {
                    display: needY1,
                    position: 'right', grid: { display: false },
                    ticks: { color: '#a1a1aa', font: { family: 'Inter', size: 10 }, callback: function(v) { return v + '%'; } }
                }
            }
        }
    });
}

// Renderizar al cargar
rebuildEmpChart();

// ─── Operation tabs ─────────────────────────────────────────
// ─── Compensaciones ─────────────────────────────────────────
let compMonto = 0;
function openComp(empId, nombre, slug) {
    document.getElementById('compTitle').textContent = 'Compensación ' + nombre;
    document.getElementById('compEmpId').value = empId;
    document.getElementById('compSlug').value = slug;
    document.getElementById('compCliente').value = '';
    document.getElementById('compResult').style.display = 'none';
    document.getElementById('compGenerar').style.display = 'block';
    document.querySelectorAll('.comp-monto').forEach(b => b.classList.remove('sel'));
    compMonto = 0;
    document.getElementById('compOverlay').style.display = 'flex';
}
function closeComp() { document.getElementById('compOverlay').style.display = 'none'; }
function selMonto(btn, m) {
    document.querySelectorAll('.comp-monto').forEach(b => b.classList.remove('sel'));
    btn.classList.add('sel');
    compMonto = m;
}
async function generarComp() {
    const cliente = document.getElementById('compCliente').value.trim();
    if (!cliente) { alert('Ingresa el nombre del cliente'); return; }
    if (!compMonto) { alert('Selecciona un monto'); return; }
    const btn = document.getElementById('compGenerar');
    btn.disabled = true; btn.textContent = 'Generando...';
    try {
        const r = await fetch('/superadmin/compensacion', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                empresa_id: parseInt(document.getElementById('compEmpId').value),
                slug: document.getElementById('compSlug').value,
                monto: compMonto,
                cliente
            })
        });
        const d = await r.json();
        if (d.ok) {
            document.getElementById('compURL').value = d.url;
            document.getElementById('compCodigo').textContent = d.codigo;
            document.getElementById('compResult').style.display = 'block';
            btn.style.display = 'none';
        } else {
            alert(d.error || 'Error al generar');
            btn.disabled = false; btn.textContent = 'Generar compensación';
        }
    } catch(e) {
        alert('Error de conexión');
        btn.disabled = false; btn.textContent = 'Generar compensación';
    }
}

// ─── Operation tabs ─────────────────────────────────────────
function opTab(id, btn) {
    document.querySelectorAll('.op-tab').forEach(t => t.classList.remove('on'));
    document.querySelectorAll('.op-panel').forEach(p => p.classList.remove('on'));
    btn.classList.add('on');
    document.getElementById('op-' + id).classList.add('on');
}

function filterTable(tab, empId) {
    const rows = document.querySelectorAll('#tbl-' + tab + ' tbody tr');
    rows.forEach(r => {
        r.style.display = (!empId || r.dataset.emp === empId) ? '' : 'none';
    });
}

// ── Gráfica ventas históricas (barras + promedio) ──
new Chart(document.getElementById('histChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($hist_labels) ?>,
        datasets: [
            {
                label: 'Total',
                data: <?= json_encode($hist_values) ?>,
                backgroundColor: 'rgba(147,197,253,.6)',
                borderColor: 'rgba(96,165,250,.8)',
                borderWidth: 1,
                borderRadius: 3,
            },
            {
                label: 'Promedio',
                data: <?= json_encode($hist_prom_line) ?>,
                type: 'line',
                borderColor: 'rgba(239,68,68,.5)',
                borderWidth: 2,
                borderDash: [6,4],
                pointRadius: 0,
                fill: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, padding: 14, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return ctx.dataset.label + ': $' + ctx.parsed.y.toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(v) { return '$' + (v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v); },
                    font: { size: 11 }
                },
                grid: { color: 'rgba(0,0,0,.06)' }
            },
            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 45 } }
        }
    }
});
</script>
</body>
</html>
