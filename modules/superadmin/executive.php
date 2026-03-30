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
    12 => ['nombre' => 'OnTime HMO',    'color' => '#1a5c38', 'short' => 'HMO'],
    13 => ['nombre' => 'OnTime CEN',    'color' => '#1d4ed8', 'short' => 'CEN'],
    14 => ['nombre' => 'OnTime NOG',    'color' => '#6d28d9', 'short' => 'NOG'],
    2  => ['nombre' => 'Closet Factory','color' => '#c2410c', 'short' => 'CF'],
    7  => ['nombre' => 'Granito Depot', 'color' => '#475569', 'short' => 'GD'],
];
$emp_ids = implode(',', array_keys($empresas_cfg));

// ─── Periodo ────────────────────────────────────────────────
$now = new DateTimeImmutable('now', new DateTimeZone('America/Hermosillo'));
$mes_ini = $now->format('Y-m') . '-01';
$mes_fin = $now->format('Y-m-d');
$mes_ant_ini = $now->modify('first day of last month')->format('Y-m-d');
$mes_ant_fin = $now->modify('last day of last month')->format('Y-m-d');

// ─── 1. KPIs GLOBALES ──────────────────────────────────────
$kpi_global = DB::row(
    "SELECT COALESCE(SUM(total),0) AS ventas_mes,
            COALESCE(SUM(pagado),0) AS cobrado_mes,
            COALESCE(SUM(saldo),0)  AS por_cobrar,
            COUNT(*) AS num_ventas
     FROM ventas
     WHERE empresa_id IN ({$emp_ids})
       AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?",
    [$mes_ini . ' 00:00:00', $mes_fin . ' 23:59:59']
);

$cobrado_hoy = (float)DB::val(
    "SELECT COALESCE(SUM(monto),0)
     FROM recibos
     WHERE empresa_id IN ({$emp_ids}) AND tipo='abono' AND cancelado=0
       AND fecha = CURDATE()"
);

$pipeline = DB::row(
    "SELECT COUNT(*) AS num, COALESCE(SUM(total),0) AS monto
     FROM cotizaciones
     WHERE empresa_id IN ({$emp_ids})
       AND estado IN ('enviada','vista')
       AND COALESCE(suspendida,0) = 0"
);

$por_cobrar_total = (float)DB::val(
    "SELECT COALESCE(SUM(saldo),0)
     FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado IN ('pendiente','parcial')"
);

// ─── 2. VENTAS POR EMPRESA (mes actual vs anterior) ─────────
$ventas_empresa = DB::query(
    "SELECT empresa_id,
            COALESCE(SUM(total),0) AS monto,
            COUNT(*) AS num
     FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$mes_ini . ' 00:00:00', $mes_fin . ' 23:59:59']
);
$ve = [];
foreach ($ventas_empresa as $v) $ve[(int)$v['empresa_id']] = $v;

$ventas_ant = DB::query(
    "SELECT empresa_id,
            COALESCE(SUM(total),0) AS monto,
            COUNT(*) AS num
     FROM ventas
     WHERE empresa_id IN ({$emp_ids}) AND estado != 'cancelada'
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$mes_ant_ini . ' 00:00:00', $mes_ant_fin . ' 23:59:59']
);
$va = [];
foreach ($ventas_ant as $v) $va[(int)$v['empresa_id']] = $v;

// Cotizaciones del mes por empresa (para tasa cierre)
$cots_empresa = DB::query(
    "SELECT empresa_id, COUNT(*) AS total,
            SUM(CASE WHEN estado IN ('aceptada') THEN 1 ELSE 0 END) AS aceptadas
     FROM cotizaciones
     WHERE empresa_id IN ({$emp_ids})
       AND COALESCE(suspendida,0) = 0
       AND created_at BETWEEN ? AND ?
     GROUP BY empresa_id",
    [$mes_ini . ' 00:00:00', $mes_fin . ' 23:59:59']
);
$ce = [];
foreach ($cots_empresa as $c) $ce[(int)$c['empresa_id']] = $c;

// ─── 3. PAGOS DEL DÍA ──────────────────────────────────────
$pagos_hoy = DB::query(
    "SELECT r.id, r.empresa_id, r.monto, r.forma_pago, r.created_at, r.concepto,
            v.titulo AS venta_titulo, v.numero AS venta_numero,
            c.nombre AS cliente_nombre
     FROM recibos r
     JOIN ventas v ON v.id = r.venta_id
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE r.empresa_id IN ({$emp_ids}) AND r.tipo='abono' AND r.cancelado=0
       AND r.fecha = CURDATE()
     ORDER BY r.created_at DESC"
);

// Pagos hoy por empresa
$pagos_hoy_emp = [];
foreach ($pagos_hoy as $p) {
    $eid = (int)$p['empresa_id'];
    $pagos_hoy_emp[$eid] = ($pagos_hoy_emp[$eid] ?? 0) + (float)$p['monto'];
}

// ─── 4. VENTAS SIN PAGOS ───────────────────────────────────
$sin_pagos = DB::query(
    "SELECT v.id, v.empresa_id, v.titulo, v.numero, v.total, v.created_at,
            c.nombre AS cliente_nombre,
            DATEDIFF(NOW(), v.created_at) AS dias
     FROM ventas v
     LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE v.empresa_id IN ({$emp_ids})
       AND v.estado IN ('pendiente')
       AND v.pagado = 0
       AND v.total > 0
     ORDER BY v.created_at ASC
     LIMIT 50"
);

// ─── 5. PERFORMANCE POR ASESOR ─────────────────────────────
$asesores = DB::query(
    "SELECT u.id, u.nombre, u.email, u.empresa_id,
            us.score, us.nivel, us.cot_asignadas, us.cot_vistas, us.conversiones,
            us.s_activacion, us.s_seguimiento, us.s_conversion
     FROM usuarios u
     LEFT JOIN usuario_score us ON us.usuario_id = u.id AND us.empresa_id = u.empresa_id
     WHERE u.empresa_id IN ({$emp_ids})
       AND u.activo = 1
       AND u.rol != 'superadmin'
     ORDER BY us.score DESC"
);

// Ventas por asesor este mes
$ventas_asesor = DB::query(
    "SELECT COALESCE(v.vendedor_id, v.usuario_id) AS uid,
            COUNT(*) AS num, COALESCE(SUM(v.total),0) AS monto
     FROM ventas v
     WHERE v.empresa_id IN ({$emp_ids}) AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ?
     GROUP BY uid",
    [$mes_ini . ' 00:00:00', $mes_fin . ' 23:59:59']
);
$va_asesor = [];
foreach ($ventas_asesor as $v) $va_asesor[(int)$v['uid']] = $v;

// ─── 6. PIPELINE ────────────────────────────────────────────
$pipeline_emp = DB::query(
    "SELECT empresa_id,
            COUNT(*) AS num,
            COALESCE(SUM(total),0) AS monto,
            ROUND(AVG(DATEDIFF(NOW(), created_at)),0) AS dias_prom
     FROM cotizaciones
     WHERE empresa_id IN ({$emp_ids})
       AND estado IN ('enviada','vista')
       AND COALESCE(suspendida,0) = 0
     GROUP BY empresa_id"
);
$pip = [];
foreach ($pipeline_emp as $p) $pip[(int)$p['empresa_id']] = $p;

// ─── 7. TENDENCIAS (últimos 12 meses) ──────────────────────
$tendencias = DB::query(
    "SELECT mes, empresa_id, SUM(monto) AS monto FROM (
        SELECT DATE_FORMAT(v.created_at, '%Y-%m') AS mes, v.empresa_id, v.total AS monto
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

// Organizar por empresa y mes
$trend_data = [];
$trend_meses = [];
foreach ($tendencias as $t) {
    $trend_data[(int)$t['empresa_id']][$t['mes']] = (float)$t['monto'];
    $trend_meses[$t['mes']] = true;
}
ksort($trend_meses);

// ─── Helpers ────────────────────────────────────────────────
function ex_fmt(float $n): string {
    if ($n >= 1000000) return '$' . number_format($n/1000000, 1) . 'M';
    if ($n >= 1000) return '$' . number_format($n/1000, 0) . 'K';
    return '$' . number_format($n, 0);
}
function ex_money(float $n): string { return '$' . number_format($n, 0); }
function ex_pct(float $n): string { return number_format($n, 1) . '%'; }
function ex_nivel_color(string $nivel): string {
    return match($nivel) { 'top'=>'#16a34a', 'activo'=>'#1d4ed8', 'regular'=>'#d97706', 'bajo'=>'#dc2626', default=>'#94a3b8' };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Executive Dashboard — CotizaCloud</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f0f0f;--card:#1a1a1a;--card2:#222;--border:#333;--text:#f0f0f0;--t2:#a0a0a0;--t3:#666;--g:#22c55e;--r:#ef4444;--b:#3b82f6;--a:#f59e0b;--r-sm:10px;--r:14px}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.5}
.wrap{max-width:1400px;margin:0 auto;padding:20px 24px 60px}
.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px}
.header h1{font-size:24px;font-weight:800;letter-spacing:-.03em}
.header .sub{font-size:13px;color:var(--t2)}
.back{color:var(--t2);text-decoration:none;font-size:13px;font-weight:600}
.back:hover{color:var(--g)}

/* KPIs */
.kpi-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.kpi{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:18px 20px}
.kpi-lbl{font-size:11px;font-weight:600;color:var(--t2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
.kpi-val{font:800 28px 'DM Sans',sans-serif;letter-spacing:-.02em}
.kpi-sub{font-size:12px;color:var(--t2);margin-top:4px}
.green{color:var(--g)}.red{color:var(--r)}.blue{color:var(--b)}.amber{color:var(--a)}

/* Section */
.sec{margin-bottom:28px}
.sec-title{font:700 15px 'Plus Jakarta Sans',sans-serif;color:var(--text);margin-bottom:12px;display:flex;align-items:center;gap:10px}
.sec-title::after{content:'';flex:1;height:1px;background:var(--border)}

/* Tables */
.tbl-wrap{background:var(--card);border:1px solid var(--border);border-radius:var(--r);overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{text-align:left;font:700 10px 'Plus Jakarta Sans',sans-serif;letter-spacing:.06em;text-transform:uppercase;color:var(--t2);padding:10px 14px;border-bottom:1px solid var(--border);white-space:nowrap}
th.r{text-align:right}
td{padding:10px 14px;border-bottom:1px solid var(--border);font-size:13px}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--card2)}
.num{font-family:'DM Sans',sans-serif}

/* Badge */
.badge{display:inline-block;padding:3px 9px;border-radius:99px;font:700 10px 'Plus Jakarta Sans',sans-serif;text-transform:uppercase;letter-spacing:.04em}

/* Empresa tag */
.emp-tag{display:inline-block;padding:3px 8px;border-radius:6px;font:700 10px 'Plus Jakarta Sans',sans-serif;color:#fff;letter-spacing:.03em}

/* Chart */
.chart-wrap{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:20px}
.chart-legend{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:14px}
.chart-legend span{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--t2)}
.chart-legend i{width:10px;height:10px;border-radius:3px;display:inline-block}
.bar-chart{display:flex;align-items:flex-end;gap:3px;height:140px}
.bar-group{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px}
.bar-stack{display:flex;align-items:flex-end;gap:1px;width:100%;justify-content:center}
.bar-stack .bar{min-width:4px;max-width:14px;flex:1;border-radius:3px 3px 0 0;transition:height .3s}
.bar-lbl{font:500 8px 'DM Sans',sans-serif;color:var(--t3);text-align:center;white-space:nowrap}

/* Score bar */
.score-bar{width:60px;height:6px;background:var(--border);border-radius:3px;overflow:hidden;display:inline-block;vertical-align:middle}
.score-fill{height:100%;border-radius:3px}

/* Two columns */
.two-cols{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:900px){.two-cols{grid-template-columns:1fr}}

/* Var change */
.chg{font-size:11px;font-weight:700}
.chg.up{color:var(--g)}.chg.dn{color:var(--r)}.chg.eq{color:var(--t3)}
</style>
</head>
<body>
<div class="wrap">

<div class="header">
    <div>
        <h1>Executive Dashboard</h1>
        <div class="sub"><?= $now->format('d M Y, H:i') ?> — Hermosillo</div>
    </div>
    <a href="/superadmin" class="back">← SuperAdmin</a>
</div>

<!-- ══ 1. KPIs GLOBALES ══════════════════════════════════════ -->
<div class="kpi-row">
    <div class="kpi">
        <div class="kpi-lbl">Ventas del mes</div>
        <div class="kpi-val green"><?= ex_fmt((float)$kpi_global['ventas_mes']) ?></div>
        <div class="kpi-sub"><?= $kpi_global['num_ventas'] ?> ventas</div>
    </div>
    <div class="kpi">
        <div class="kpi-lbl">Cobrado hoy</div>
        <div class="kpi-val"><?= ex_money($cobrado_hoy) ?></div>
        <div class="kpi-sub"><?= count($pagos_hoy) ?> pagos</div>
    </div>
    <div class="kpi">
        <div class="kpi-lbl">Por cobrar</div>
        <div class="kpi-val amber"><?= ex_fmt($por_cobrar_total) ?></div>
    </div>
    <div class="kpi">
        <div class="kpi-lbl">Pipeline activo</div>
        <div class="kpi-val blue"><?= ex_fmt((float)$pipeline['monto']) ?></div>
        <div class="kpi-sub"><?= $pipeline['num'] ?> cotizaciones vivas</div>
    </div>
</div>

<!-- ══ 2. VENTAS POR EMPRESA ═════════════════════════════════ -->
<div class="sec">
    <div class="sec-title">Ventas por empresa — <?= $now->format('F Y') ?></div>
    <div class="tbl-wrap">
    <table>
    <thead>
        <tr><th>Empresa</th><th class="r">Ventas</th><th class="r">Monto</th><th class="r">Mes anterior</th><th class="r">Variación</th><th class="r">Tasa cierre</th><th class="r">Pipeline</th></tr>
    </thead>
    <tbody>
    <?php foreach ($empresas_cfg as $eid => $ecfg):
        $v_act = (float)($ve[$eid]['monto'] ?? 0);
        $v_num = (int)($ve[$eid]['num'] ?? 0);
        $v_ant = (float)($va[$eid]['monto'] ?? 0);
        $var_pct = $v_ant > 0 ? round(($v_act - $v_ant) / $v_ant * 100, 1) : ($v_act > 0 ? 100 : 0);
        $cots_t = (int)($ce[$eid]['total'] ?? 0);
        $cots_a = (int)($ce[$eid]['aceptadas'] ?? 0);
        $tasa = $cots_t > 0 ? round($cots_a / $cots_t * 100, 1) : 0;
        $pip_m = (float)($pip[$eid]['monto'] ?? 0);
        $pip_n = (int)($pip[$eid]['num'] ?? 0);
    ?>
    <tr>
        <td><span class="emp-tag" style="background:<?= $ecfg['color'] ?>"><?= $ecfg['short'] ?></span> <?= $ecfg['nombre'] ?></td>
        <td class="r num" style="font-weight:600"><?= $v_num ?></td>
        <td class="r num" style="font-weight:700;color:var(--g)"><?= ex_money($v_act) ?></td>
        <td class="r num" style="color:var(--t2)"><?= ex_money($v_ant) ?></td>
        <td class="r"><span class="chg <?= $var_pct > 0 ? 'up' : ($var_pct < 0 ? 'dn' : 'eq') ?>"><?= $var_pct > 0 ? '+' : '' ?><?= $var_pct ?>%</span></td>
        <td class="r num" style="color:<?= $tasa >= 15 ? 'var(--g)' : ($tasa >= 8 ? 'var(--a)' : 'var(--r)') ?>"><?= ex_pct($tasa) ?></td>
        <td class="r num" style="color:var(--b)"><?= $pip_n ?> / <?= ex_fmt($pip_m) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>

<!-- ══ 3. PAGOS DEL DÍA ═════════════════════════════════════ -->
<div class="sec">
    <div class="sec-title">Pagos de hoy — <?= $now->format('d M') ?> (<?= count($pagos_hoy) ?> pagos, <?= ex_money($cobrado_hoy) ?>)</div>
    <?php if ($pagos_hoy): ?>
    <div class="tbl-wrap">
    <table>
    <thead><tr><th>Sucursal</th><th>Cliente</th><th>Venta</th><th>Forma</th><th class="r">Monto</th><th>Hora</th></tr></thead>
    <tbody>
    <?php foreach ($pagos_hoy as $p):
        $ecfg = $empresas_cfg[(int)$p['empresa_id']] ?? ['short'=>'?','color'=>'#666','nombre'=>'?'];
    ?>
    <tr>
        <td><span class="emp-tag" style="background:<?= $ecfg['color'] ?>"><?= $ecfg['short'] ?></span></td>
        <td><?= e($p['cliente_nombre'] ?? '—') ?></td>
        <td style="font-size:12px"><?= e($p['venta_titulo'] ?? $p['venta_numero']) ?></td>
        <td style="font-size:12px;color:var(--t2)"><?= e($p['forma_pago'] ?? 'efectivo') ?></td>
        <td class="r num" style="font-weight:700;color:var(--g)"><?= ex_money((float)$p['monto']) ?></td>
        <td class="num" style="font-size:12px;color:var(--t2)"><?= date('H:i', strtotime($p['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
    <div class="tbl-wrap" style="padding:24px;text-align:center;color:var(--t3)">Sin pagos registrados hoy</div>
    <?php endif; ?>
</div>

<div class="two-cols">

<!-- ══ 4. VENTAS SIN PAGOS ═══════════════════════════════════ -->
<div class="sec">
    <div class="sec-title">Ventas sin pagos (<?= count($sin_pagos) ?>)</div>
    <div class="tbl-wrap" style="max-height:400px;overflow-y:auto">
    <table>
    <thead><tr><th>Sucursal</th><th>Venta</th><th class="r">Total</th><th class="r">Días</th></tr></thead>
    <tbody>
    <?php if ($sin_pagos): foreach ($sin_pagos as $sp):
        $ecfg = $empresas_cfg[(int)$sp['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
    ?>
    <tr>
        <td><span class="emp-tag" style="background:<?= $ecfg['color'] ?>"><?= $ecfg['short'] ?></span></td>
        <td style="font-size:12px"><?= e($sp['titulo']) ?><br><span style="color:var(--t3);font-size:11px"><?= e($sp['cliente_nombre'] ?? '—') ?></span></td>
        <td class="r num" style="font-weight:600"><?= ex_money((float)$sp['total']) ?></td>
        <td class="r num" style="color:<?= $sp['dias'] > 7 ? 'var(--r)' : ($sp['dias'] > 3 ? 'var(--a)' : 'var(--t2)') ?>"><?= $sp['dias'] ?>d</td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--t3)">Sin ventas pendientes</td></tr>
    <?php endif; ?>
    </tbody>
    </table>
    </div>
</div>

<!-- ══ 5. PERFORMANCE ASESORES ═══════════════════════════════ -->
<div class="sec">
    <div class="sec-title">Performance asesores</div>
    <div class="tbl-wrap" style="max-height:400px;overflow-y:auto">
    <table>
    <thead><tr><th>Asesor</th><th>Sucursal</th><th class="r">Ventas</th><th class="r">Monto</th><th class="r">Score</th></tr></thead>
    <tbody>
    <?php foreach ($asesores as $a):
        $ecfg = $empresas_cfg[(int)$a['empresa_id']] ?? ['short'=>'?','color'=>'#666'];
        $va_d = $va_asesor[(int)$a['id']] ?? ['num'=>0,'monto'=>0];
        $score = (int)($a['score'] ?? 0);
        $nivel = $a['nivel'] ?? 'nuevo';
        $nc = ex_nivel_color($nivel);
    ?>
    <tr>
        <td style="font-weight:600"><?= e($a['nombre']) ?></td>
        <td><span class="emp-tag" style="background:<?= $ecfg['color'] ?>"><?= $ecfg['short'] ?></span></td>
        <td class="r num"><?= (int)$va_d['num'] ?></td>
        <td class="r num" style="color:var(--g)"><?= ex_money((float)$va_d['monto']) ?></td>
        <td class="r">
            <div class="score-bar"><div class="score-fill" style="width:<?= $score ?>%;background:<?= $nc ?>"></div></div>
            <span class="num" style="font-weight:700;color:<?= $nc ?>;margin-left:6px"><?= $score ?></span>
            <span class="badge" style="background:<?= $nc ?>20;color:<?= $nc ?>;margin-left:4px"><?= $nivel ?></span>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>

</div><!-- /two-cols -->

<!-- ══ 7. TENDENCIAS ═════════════════════════════════════════ -->
<div class="sec">
    <div class="sec-title">Tendencias — últimos 12 meses</div>
    <div class="chart-wrap">
        <div class="chart-legend">
            <?php foreach ($empresas_cfg as $eid => $ecfg): ?>
            <span><i style="background:<?= $ecfg['color'] ?>"></i><?= $ecfg['short'] ?></span>
            <?php endforeach; ?>
        </div>
        <?php
        $max_trend = 1;
        foreach ($trend_meses as $m => $_) {
            foreach ($empresas_cfg as $eid => $ecfg) {
                $v = $trend_data[$eid][$m] ?? 0;
                if ($v > $max_trend) $max_trend = $v;
            }
        }
        ?>
        <div class="bar-chart">
        <?php foreach ($trend_meses as $m => $_): ?>
            <div class="bar-group">
                <div class="bar-stack">
                <?php foreach ($empresas_cfg as $eid => $ecfg):
                    $v = $trend_data[$eid][$m] ?? 0;
                    $h = round($v / $max_trend * 120);
                ?>
                    <div class="bar" style="height:<?= max(2,$h) ?>px;background:<?= $ecfg['color'] ?>" title="<?= $ecfg['short'] ?>: <?= ex_money($v) ?>"></div>
                <?php endforeach; ?>
                </div>
                <div class="bar-lbl"><?= date('M', strtotime($m.'-01')) ?></div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ══ 6. PIPELINE POR EMPRESA ═══════════════════════════════ -->
<div class="sec">
    <div class="sec-title">Pipeline por empresa</div>
    <div class="kpi-row">
    <?php foreach ($empresas_cfg as $eid => $ecfg):
        $p = $pip[$eid] ?? ['num'=>0,'monto'=>0,'dias_prom'=>0];
    ?>
    <div class="kpi" style="border-left:3px solid <?= $ecfg['color'] ?>">
        <div class="kpi-lbl"><?= $ecfg['nombre'] ?></div>
        <div class="kpi-val blue"><?= ex_fmt((float)$p['monto']) ?></div>
        <div class="kpi-sub"><?= $p['num'] ?> cotizaciones · ~<?= $p['dias_prom'] ?> días prom</div>
    </div>
    <?php endforeach; ?>
    </div>
</div>

</div><!-- /wrap -->
</body>
</html>
