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

// Periodo anterior (para comparación)
$diff_days = (new DateTime($p_ini))->diff(new DateTime($p_fin))->days + 1;
$ant_fin = (new DateTime($p_ini))->modify('-1 day')->format('Y-m-d');
$ant_ini = (new DateTime($ant_fin))->modify("-{$diff_days} days")->format('Y-m-d');
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

$var_ventas = (float)$kpi_ant['ventas'] > 0
    ? round(((float)$kpi_mes['ventas'] - (float)$kpi_ant['ventas']) / (float)$kpi_ant['ventas'] * 100, 1)
    : ((float)$kpi_mes['ventas'] > 0 ? 100 : 0);

// ─── VENTAS POR EMPRESA ─────────────────────────────────────
$ve_act = [];
$rows = DB::query(
    "SELECT empresa_id, COALESCE(SUM(total),0) AS monto, COUNT(*) AS num,
            COALESCE(SUM(pagado),0) AS cobrado, COALESCE(SUM(saldo),0) AS por_cobrar
     FROM ventas WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$p_ini_dt, $p_fin_dt]
);
foreach ($rows as $r) $ve_act[(int)$r['empresa_id']] = $r;

$ve_ant = [];
$rows = DB::query(
    "SELECT empresa_id, COALESCE(SUM(total),0) AS monto, COUNT(*) AS num
     FROM ventas WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$ant_ini_dt, $ant_fin_dt]
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

// Top clientes del periodo
$top_clientes = DB::query(
    "SELECT c.nombre, v.empresa_id, COUNT(*) AS num_ventas, COALESCE(SUM(v.total),0) AS monto
     FROM ventas v
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE v.empresa_id IN ({$emp_ids}) AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ?
       AND c.nombre IS NOT NULL
     GROUP BY c.id, c.nombre, v.empresa_id
     ORDER BY monto DESC LIMIT 10",
    [$p_ini_dt, $p_fin_dt]
);
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

// Construir serie de 12 meses
$meses_12 = [];
for ($i = 11; $i >= 0; $i--) $meses_12[] = date('Y-m', strtotime("-$i months"));

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
    "SELECT v.empresa_id, v.titulo, v.numero, v.total, v.created_at,
            c.nombre AS cliente_nombre,
            DATEDIFF(NOW(), v.created_at) AS dias
     FROM ventas v
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE v.empresa_id IN ({$emp_ids})
       AND v.estado IN ('pendiente')
       AND v.pagado = 0 AND v.total > 0
     ORDER BY v.created_at ASC LIMIT 30"
);

// ─── PERFORMANCE ASESORES ───────────────────────────────────
$asesores = DB::query(
    "SELECT u.id, u.nombre, u.empresa_id,
            us.score, us.nivel, us.s_activacion, us.s_seguimiento, us.s_conversion
     FROM usuarios u
     LEFT JOIN usuario_score us ON us.usuario_id = u.id AND us.empresa_id = u.empresa_id
     WHERE u.empresa_id IN ({$emp_ids}) AND u.activo = 1 AND u.rol = 'asesor'
     ORDER BY us.score DESC"
);

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
.kpi-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:28px}
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

/* Grid 3 */
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
@media(max-width:1100px){.grid-3{grid-template-columns:1fr}}

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
</div>

<!-- TENDENCIAS -->
<div class="chart-card">
    <div class="chart-title">Tendencia de ingresos</div>
    <div class="chart-sub">Últimos 12 meses — por empresa</div>
    <div class="chart-canvas">
        <canvas id="trendChart"></canvas>
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

<!-- DISTRIBUCIÓN + VENTAS SIN PAGOS + ASESORES -->
<div class="grid-3">

<!-- Donut distribución -->
<div class="sec">
    <div class="sec-hdr"><div class="sec-title">Distribución</div></div>
    <div class="tbl-card" style="padding:24px">
        <div class="donut-wrap">
            <div class="donut-canvas"><canvas id="donutChart"></canvas></div>
        </div>
        <div class="donut-legend" style="margin-top:16px">
        <?php
        // Ordenar por monto desc
        $sorted = [];
        foreach ($empresas_cfg as $eid => $ec) $sorted[] = ['eid'=>$eid,'nombre'=>$ec['nombre'],'short'=>$ec['short'],'color'=>$ec['color'],'monto'=>(float)($ve_act[$eid]['monto']??0)];
        usort($sorted, fn($a,$b) => $b['monto'] <=> $a['monto']);
        foreach ($sorted as $s):
        ?>
        <div class="donut-item">
            <span class="donut-dot" style="background:<?= $s['color'] ?>"></span>
            <span><?= $s['nombre'] ?></span>
            <span class="donut-val" style="color:var(--g)"><?= xm($s['monto']) ?></span>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Ventas sin pagos -->
<div class="sec">
    <div class="sec-hdr">
        <div class="sec-title">Sin cobrar</div>
        <div class="sec-count"><?= count($sin_pagos) ?> ventas</div>
    </div>
    <div class="tbl-card" style="max-height:420px;overflow-y:auto">
    <table>
    <thead><tr><th></th><th>Venta</th><th class="r">Total</th><th class="r">Días</th></tr></thead>
    <tbody>
    <?php if ($sin_pagos): foreach ($sin_pagos as $sp):
        $ec = $empresas_cfg[(int)$sp['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $dias = (int)$sp['dias'];
        $dias_color = $dias > 7 ? 'var(--r)' : ($dias > 3 ? 'var(--a)' : 'var(--t2)');
    ?>
    <tr>
        <td><span class="tag" style="background:<?= $ec['color'] ?>"><?= $ec['short'] ?></span></td>
        <td>
            <div style="font-weight:600;font-size:12px"><?= e(mb_substr($sp['titulo'],0,35)) ?></div>
            <div style="font-size:11px;color:var(--t3)"><?= e($sp['cliente_nombre'] ?? '—') ?></div>
        </td>
        <td class="r mono" style="font-weight:600"><?= xf((float)$sp['total']) ?></td>
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

<!-- Performance asesores -->
<div class="sec">
    <div class="sec-hdr">
        <div class="sec-title">Asesores</div>
        <div class="sec-count"><?= count($asesores) ?> activos</div>
    </div>
    <div class="tbl-card" style="max-height:420px;overflow-y:auto">
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

<!-- FUNNEL + TOP CLIENTES -->
<div class="grid-2">

<!-- Funnel de conversión -->
<div class="sec">
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

<!-- Top clientes -->
<div class="sec">
    <div class="sec-hdr">
        <div class="sec-title">Top clientes</div>
        <div class="sec-count"><?= $p_label ?></div>
    </div>
    <div class="tbl-card" style="max-height:420px;overflow-y:auto">
    <table>
    <thead><tr><th>#</th><th></th><th>Cliente</th><th class="r">Ventas</th><th class="r">Monto</th></tr></thead>
    <tbody>
    <?php if ($top_clientes): $pos = 1; foreach ($top_clientes as $tc):
        $ec = $empresas_cfg[(int)$tc['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
    ?>
    <tr>
        <td style="font:800 14px 'Inter',sans-serif;color:<?= $pos <= 3 ? 'var(--a)' : 'var(--t3)' ?>"><?= $pos ?></td>
        <td><span class="tag" style="background:<?= $ec['color'] ?>;font-size:8px;padding:3px 6px"><?= $ec['short'] ?></span></td>
        <td style="font-weight:600"><?= e($tc['nombre']) ?></td>
        <td class="r mono"><?= (int)$tc['num_ventas'] ?></td>
        <td class="r mono" style="font-weight:700;color:var(--g)"><?= xf((float)$tc['monto']) ?></td>
    </tr>
    <?php $pos++; endforeach; else: ?>
    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--t3)">Sin datos</td></tr>
    <?php endif; ?>
    </tbody>
    </table>
    </div>
</div>

</div><!-- /grid-2 funnel+clientes -->

</div><!-- /wrap -->

<script>
<?php
// Ordenar empresas por monto desc para leyenda de gráfica
$emp_sorted = [];
foreach ($empresas_cfg as $eid => $ec) {
    $total_12 = 0;
    foreach ($meses_12 as $m) $total_12 += $trend[$eid][$m] ?? 0;
    $emp_sorted[] = ['eid'=>$eid, 'ec'=>$ec, 'total'=>$total_12];
}
usort($emp_sorted, fn($a,$b) => $b['total'] <=> $a['total']);
?>
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
</script>
</body>
</html>
