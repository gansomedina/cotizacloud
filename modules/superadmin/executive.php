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

// ─── Periodo ────────────────────────────────────────────────
$now = new DateTimeImmutable('now', new DateTimeZone('America/Hermosillo'));
$mes_ini = $now->format('Y-m') . '-01';
$mes_fin = $now->format('Y-m-d');
$mes_ant_ini = $now->modify('first day of last month')->format('Y-m-d');
$mes_ant_fin = $now->modify('last day of last month')->format('Y-m-d');

// ─── KPIs GLOBALES ──────────────────────────────────────────
$kpi_mes = DB::row(
    "SELECT COALESCE(SUM(total),0) AS ventas,
            COUNT(*) AS num
     FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?",
    [$mes_ini . ' 00:00:00', $mes_fin . ' 23:59:59']
);

$kpi_ant = DB::row(
    "SELECT COALESCE(SUM(total),0) AS ventas, COUNT(*) AS num
     FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?",
    [$mes_ant_ini . ' 00:00:00', $mes_ant_fin . ' 23:59:59']
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
    "SELECT empresa_id, COALESCE(SUM(total),0) AS monto, COUNT(*) AS num
     FROM ventas WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$mes_ini . ' 00:00:00', $mes_fin . ' 23:59:59']
);
foreach ($rows as $r) $ve_act[(int)$r['empresa_id']] = $r;

$ve_ant = [];
$rows = DB::query(
    "SELECT empresa_id, COALESCE(SUM(total),0) AS monto, COUNT(*) AS num
     FROM ventas WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$mes_ant_ini . ' 00:00:00', $mes_ant_fin . ' 23:59:59']
);
foreach ($rows as $r) $ve_ant[(int)$r['empresa_id']] = $r;

// Tasa cierre por empresa
$ce = [];
$rows = DB::query(
    "SELECT empresa_id, COUNT(*) AS total,
            SUM(CASE WHEN estado='aceptada' THEN 1 ELSE 0 END) AS aceptadas
     FROM cotizaciones WHERE empresa_id IN ({$emp_ids}) AND COALESCE(suspendida,0)=0
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$mes_ini . ' 00:00:00', $mes_fin . ' 23:59:59']
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
@media(max-width:900px){.kpi-grid{grid-template-columns:repeat(2,1fr)}}
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
        <div class="hdr-live"><span class="hdr-dot"></span>LIVE</div>
        <a href="/superadmin" class="hdr-back">← SuperAdmin</a>
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-lbl">Ventas del mes</div>
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
        <div class="sec-count"><?= $now->format('F Y') ?></div>
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
    foreach ($empresas_cfg as $eid => $ec):
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

</div><!-- /wrap -->

<script>
// ─── Trend Chart ────────────────────────────────────────────
const ctx = document.getElementById('trendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [
            <?php foreach ($empresas_cfg as $eid => $ec):
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
                    label: function(ctx) {
                        return ctx.dataset.label + ': $' + ctx.parsed.y.toLocaleString('en-US', {maximumFractionDigits:0});
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
</script>
</body>
</html>
