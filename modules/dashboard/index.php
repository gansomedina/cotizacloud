<?php
// ============================================================
//  CotizaApp — modules/dashboard/index.php
//  GET / y GET /dashboard
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();
$usuario    = Auth::usuario();
$moneda     = $empresa['moneda'] ?? 'MXN';

// ─── Termómetro de actividad (cache 5 min) ────────────────
$mi_score = ActividadScore::obtener(Auth::id());
if (!$mi_score || (time() - strtotime($mi_score['updated_at'])) > 300) {
    $mi_score = ActividadScore::calcular(Auth::id(), $empresa_id);
}

// ─── Leaderboard (solo admin, recalcula toda la empresa) ──
$es_admin_dash = Auth::es_admin();
$equipo_scores = [];
if ($es_admin_dash) {
    // Recalcular empresa si el score más viejo tiene >10 min
    $oldest = DB::val(
        "SELECT MIN(updated_at) FROM usuario_score WHERE empresa_id=?",
        [$empresa_id]
    );
    if (!$oldest || (time() - strtotime($oldest)) > 600) {
        ActividadScore::recalcular_empresa($empresa_id);
    }
    $equipo_scores = ActividadScore::equipo($empresa_id);
}




// ─── Período seleccionado ────────────────────────────────
$periodo = $_GET['periodo'] ?? 'mes_actual';
$periodos_validos = ['mes_actual','mes_ant','30_dias','90_dias','anio'];
if (!in_array($periodo, $periodos_validos)) $periodo = 'mes_actual';

$ahora   = new DateTimeImmutable('now', new DateTimeZone('America/Hermosillo'));
$mes_lbl = '';

switch ($periodo) {
    case 'mes_actual':
        $desde   = $ahora->format('Y-m-01 00:00:00');
        $hasta   = $ahora->format('Y-m-t 23:59:59');
        $mes_lbl = $ahora->format('F Y');
        break;
    case 'mes_ant':
        $ant     = $ahora->modify('first day of last month');
        $desde   = $ant->format('Y-m-01 00:00:00');
        $hasta   = $ant->format('Y-m-t 23:59:59');
        $mes_lbl = $ant->format('F Y');
        break;
    case '30_dias':
        $desde   = $ahora->modify('-30 days')->format('Y-m-d 00:00:00');
        $hasta   = $ahora->format('Y-m-d 23:59:59');
        $mes_lbl = 'Últimos 30 días';
        break;
    case '90_dias':
        $desde   = $ahora->modify('-90 days')->format('Y-m-d 00:00:00');
        $hasta   = $ahora->format('Y-m-d 23:59:59');
        $mes_lbl = 'Últimos 90 días';
        break;
    case 'anio':
        $desde   = $ahora->format('Y-01-01 00:00:00');
        $hasta   = $ahora->format('Y-12-31 23:59:59');
        $mes_lbl = 'Este año ' . $ahora->format('Y');
        break;
}

// Período anterior equivalente para comparativo
$dur_dias = (int)round((strtotime($hasta) - strtotime($desde)) / 86400) + 1;
$desde_ant = date('Y-m-d H:i:s', strtotime($desde) - $dur_dias * 86400);
$hasta_ant = date('Y-m-d H:i:s', strtotime($desde) - 1);

// ─── Filtro de asesor (si no admin, solo ve las suyas) ───
$solo_mias = !Auth::es_admin() && !Auth::puede('ver_todas_ventas');
$user_id   = (int)Auth::id();
$v_where   = $solo_mias ? "AND (v.usuario_id = " . intval($user_id) . " OR v.vendedor_id = " . intval($user_id) . ")" : '';
$c_where   = $solo_mias ? "AND (c.usuario_id = " . intval($user_id) . " OR c.vendedor_id = " . intval($user_id) . ")" : '';

// ═══════════════════════════════════════════════════════
//  BLOQUE 1: KPIs FINANCIEROS
// ═══════════════════════════════════════════════════════

// Ventas del período
$kpi_ventas = DB::row(
    "SELECT
        COUNT(*) AS num_ventas,
        COALESCE(SUM(total), 0) AS monto_ventas,
        COALESCE(SUM(pagado), 0) AS monto_cobrado,
        COALESCE(SUM(saldo), 0)  AS monto_saldo
     FROM ventas v
     WHERE v.empresa_id = ? AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ? $v_where",
    [$empresa_id, $desde, $hasta]
);

// Comparativo período anterior
$kpi_ventas_ant = DB::row(
    "SELECT COALESCE(SUM(total), 0) AS monto_ventas
     FROM ventas v
     WHERE v.empresa_id = ? AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ? $v_where",
    [$empresa_id, $desde_ant, $hasta_ant]
);

$delta_ventas = 0;
if ((float)$kpi_ventas_ant['monto_ventas'] > 0) {
    $delta_ventas = round(
        (((float)$kpi_ventas['monto_ventas'] - (float)$kpi_ventas_ant['monto_ventas'])
        / (float)$kpi_ventas_ant['monto_ventas']) * 100
    );
}

// Cotizaciones del período
$kpi_cots = DB::row(
    "SELECT
        SUM(estado NOT IN ('borrador') AND suspendida = 0) AS num_cots,
        COALESCE(SUM(CASE WHEN estado NOT IN ('borrador') AND suspendida = 0 THEN total ELSE 0 END), 0) AS monto_cots,
        SUM(estado IN ('enviada','vista','aceptada','convertida') AND suspendida = 0) AS num_enviadas
     FROM cotizaciones c
     WHERE c.empresa_id = ? AND c.created_at BETWEEN ? AND ? $c_where",
    [$empresa_id, $desde, $hasta]
);

$kpi_cots_ant = DB::row(
    "SELECT COUNT(*) AS num_cots
     FROM cotizaciones c
     WHERE c.empresa_id = ? AND c.created_at BETWEEN ? AND ? $c_where",
    [$empresa_id, $desde_ant, $hasta_ant]
);
$delta_cots = (int)$kpi_cots['num_cots'] - (int)$kpi_cots_ant['num_cots'];

$num_ventas_pagadas = (int)DB::val(
    "SELECT COUNT(*) FROM ventas v WHERE v.empresa_id=? AND v.estado='pagada'
     AND v.created_at BETWEEN ? AND ? $v_where",
    [$empresa_id, $desde, $hasta]
);
$num_ventas_saldo = (int)$kpi_ventas['num_ventas'] - $num_ventas_pagadas;

// Ventas sin ningún pago (aceptadas/pendientes con pagado=0)
$ventas_sin_pago = DB::query(
    "SELECT v.id, v.numero, v.titulo, v.total, v.created_at,
            cl.nombre AS cliente
     FROM ventas v
     LEFT JOIN clientes cl ON cl.id = v.cliente_id
     WHERE v.empresa_id = ? AND v.estado IN ('pendiente','parcial')
       AND v.pagado = 0 $v_where
     ORDER BY v.created_at ASC
     LIMIT 10",
    [$empresa_id]
);

// ═══════════════════════════════════════════════════════
//  BLOQUE 2: EMBUDO DE CONVERSIÓN
// ═══════════════════════════════════════════════════════

$funnel = DB::row(
    "SELECT
        COUNT(*) AS total,
        SUM(estado NOT IN ('borrador') AND suspendida = 0) AS enviadas,
        SUM(estado IN ('vista','aceptada','rechazada','vencida','convertida') AND suspendida = 0) AS abiertas,
        SUM(estado IN ('aceptada','convertida')) AS cerradas,
        SUM(estado = 'rechazada') AS rechazadas,
        SUM(suspendida = 1) AS suspendidas
     FROM cotizaciones c
     WHERE c.empresa_id = ? AND c.created_at BETWEEN ? AND ? $c_where",
    [$empresa_id, $desde, $hasta]
);

// Tasa de cierre = cerradas / enviadas (no sobre total con borradores)
$base_conversion = max(1, (int)$funnel['enviadas']);
$tasa_cierre = $funnel['enviadas'] > 0
    ? round(($funnel['cerradas'] / $funnel['enviadas']) * 100, 1)
    : 0;

$ticket_prom = $kpi_ventas['num_ventas'] > 0
    ? round($kpi_ventas['monto_ventas'] / $kpi_ventas['num_ventas'])
    : 0;

// Tiempo promedio de cierre (días entre created y aceptada_at)
$tiempo_cierre = (float)(DB::val(
    "SELECT AVG(DATEDIFF(aceptada_at, created_at))
     FROM cotizaciones c
     WHERE c.empresa_id=? AND estado IN ('aceptada','convertida')
       AND aceptada_at IS NOT NULL AND c.created_at BETWEEN ? AND ? $c_where",
    [$empresa_id, $desde, $hasta]
) ?? 0);

// Sin abrir (enviadas pero nunca vistas)
$sin_abrir = (int)DB::val(
    "SELECT COUNT(*) FROM cotizaciones c
     WHERE c.empresa_id=? AND estado='enviada' AND c.suspendida = 0
       AND vista_at IS NULL AND c.created_at BETWEEN ? AND ?
       AND c.created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR) $c_where",
    [$empresa_id, $desde, $hasta]
);

// Ventas con descuento
$ventas_con_desc = (int)DB::val(
    "SELECT COUNT(*) FROM ventas v WHERE v.empresa_id=? AND v.estado != 'cancelada'
     AND (descuento_auto_amt > 0 OR cupon_monto > 0)
     AND v.created_at BETWEEN ? AND ? $v_where",
    [$empresa_id, $desde, $hasta]
);
$total_descuentos = (float)(DB::val(
    "SELECT COALESCE(SUM(COALESCE(descuento_auto_amt,0) + COALESCE(cupon_monto,0)), 0)
     FROM ventas v WHERE v.empresa_id=? AND v.estado != 'cancelada'
     AND v.created_at BETWEEN ? AND ? $v_where",
    [$empresa_id, $desde, $hasta]
) ?? 0);

// ═══════════════════════════════════════════════════════
//  BLOQUE 3: ALERTAS
// ═══════════════════════════════════════════════════════

// Aceptadas recientemente (últimos 14 días)
$aceptadas = DB::query(
    "SELECT c.id, c.titulo, c.numero AS cot_numero, c.aceptada_at,
            v.numero AS vta_numero, v.total, v.id AS venta_id,
            cl.nombre AS cliente_nombre
     FROM cotizaciones c
     LEFT JOIN ventas  v  ON v.cotizacion_id = c.id
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.empresa_id=? AND c.estado IN ('aceptada','convertida')
       AND c.aceptada_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) $c_where
     ORDER BY c.aceptada_at DESC LIMIT 6",
    [$empresa_id]
);

// Rechazadas recientemente (últimos 14 días)
$rechazadas = DB::query(
    "SELECT c.id, c.titulo, c.numero, c.rechazada_at, c.total, c.motivo_rechazo,
            cl.nombre AS cliente_nombre
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.empresa_id=? AND c.estado='rechazada'
       AND c.rechazada_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) $c_where
     ORDER BY c.rechazada_at DESC LIMIT 6",
    [$empresa_id]
);

// Próximas a vencer (enviadas/vistas, vence en ≤7 días)
$por_vencer = DB::query(
    "SELECT c.id, c.titulo, c.numero, c.valida_hasta, c.total,
            cl.nombre AS cliente_nombre,
            DATEDIFF(c.valida_hasta, CURDATE()) AS dias_restantes
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.empresa_id=? AND c.estado IN ('enviada','vista') AND c.suspendida = 0
       AND c.valida_hasta IS NOT NULL
       AND c.valida_hasta BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) $c_where
     ORDER BY c.valida_hasta ASC LIMIT 6",
    [$empresa_id]
);

// Sin abrir (enviadas, estado 'enviada', sin vista_at)
$sin_abrir_list = DB::query(
    "SELECT c.id, c.titulo, c.numero, c.enviada_at, c.total,
            cl.nombre AS cliente_nombre,
            DATEDIFF(CURDATE(), DATE(c.enviada_at)) AS dias_sin_abrir
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.empresa_id=? AND c.estado='enviada' AND c.suspendida = 0
       AND c.vista_at IS NULL AND c.enviada_at IS NOT NULL
       AND c.created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR) $c_where
     ORDER BY c.enviada_at ASC LIMIT 6",
    [$empresa_id]
);

// ═══════════════════════════════════════════════════════
//  BLOQUE 4: RADAR BUCKETS
// ═══════════════════════════════════════════════════════

// Recalcular radar si datos tienen >5 min de antigüedad
$_radar_ult = DB::val("SELECT MAX(radar_updated_at) FROM cotizaciones WHERE empresa_id=?", [$empresa_id]);
if (!$_radar_ult || $_radar_ult < date('Y-m-d H:i:s', time()-300)) {
    try { Radar::recalcular_empresa($empresa_id); } catch(Throwable $e){}
}

// Cotizaciones activas con su bucket radar
$radar_buckets_raw = DB::query(
    "SELECT c.id, c.titulo, c.numero, c.total,
            c.radar_bucket, c.radar_score, c.radar_senales, c.visitas, c.ultima_vista_at,
            cl.nombre AS cliente_nombre,
            qs.sesiones, qs.scroll_max
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     LEFT JOIN (
         SELECT cotizacion_id,
                COUNT(*) AS sesiones,
                MAX(scroll_max) AS scroll_max
         FROM quote_sessions
         GROUP BY cotizacion_id
     ) qs ON qs.cotizacion_id = c.id
     WHERE c.empresa_id=? AND c.estado IN ('enviada','vista') AND c.suspendida = 0 $c_where
       AND c.radar_bucket IS NOT NULL
     ORDER BY c.radar_score DESC LIMIT 20",
    [$empresa_id]
);

// Agrupar por bucket — replicar lógica del Radar (probable_cierre va a ambos)
$buckets = ['onfire' => [], 'inminente' => [], 'probable_cierre' => [], 'validando_precio' => []];
foreach ($radar_buckets_raw as $r) {
    $b = $r['radar_bucket'];
    $senales = is_string($r['radar_senales']) ? (json_decode($r['radar_senales'], true) ?? []) : [];
    $all_b   = $senales['buckets'] ?? [];
    $pc_src  = $senales['pc_source'] ?? null;

    if ($b === 'probable_cierre' && $pc_src) {
        $buckets['probable_cierre'][] = $r;
        if (isset($buckets[$pc_src])) $buckets[$pc_src][] = $r;
    } elseif (isset($buckets[$b])) {
        $buckets[$b][] = $r;
    }
}

// ═══════════════════════════════════════════════════════
//  BLOQUE 4b: RECIBOS DEL DÍA
// ═══════════════════════════════════════════════════════

$hoy = $ahora->format('Y-m-d');
$recibos_hoy = DB::query(
    "SELECT r.id, r.numero, r.monto, r.tipo, r.cancelado, r.fecha,
            r.forma_pago,
            v.numero AS venta_numero, v.titulo AS venta_titulo, v.id AS venta_id,
            cl.nombre AS cliente_nombre
     FROM recibos r
     LEFT JOIN ventas v ON v.id = r.venta_id
     LEFT JOIN clientes cl ON cl.id = v.cliente_id
     WHERE r.empresa_id = ?
       AND (r.fecha = ? OR (r.cancelado = 1 AND DATE(r.cancelado_at) = ?))
     ORDER BY r.created_at DESC LIMIT 10",
    [$empresa_id, $hoy, $hoy]
);

// ═══════════════════════════════════════════════════════
//  BLOQUE 5: ACTIVIDAD DEL MES
// ═══════════════════════════════════════════════════════

$act_cots = DB::row(
    "SELECT
        SUM(estado NOT IN ('borrador') AND suspendida = 0) AS total,
        COALESCE(SUM(CASE WHEN estado NOT IN ('borrador') AND suspendida = 0 THEN total ELSE 0 END), 0) AS monto_total,
        SUM(estado IN ('aceptada','convertida')) AS cerradas,
        SUM(estado = 'rechazada') AS rechazadas,
        SUM(estado IN ('enviada','vista') AND suspendida = 0) AS pendientes
     FROM cotizaciones c
     WHERE c.empresa_id=? AND c.created_at BETWEEN ? AND ? $c_where",
    [$empresa_id, $desde, $hasta]
);

$act_ventas = DB::row(
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(total), 0) AS monto_total,
        SUM(estado = 'pagada' OR estado = 'entregada') AS pagadas,
        SUM(estado IN ('parcial','pendiente')) AS con_saldo,
        COALESCE(SUM(pagado), 0) AS cobrado
     FROM ventas v
     WHERE v.empresa_id=? AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ? $v_where",
    [$empresa_id, $desde, $hasta]
);

// ─── Helpers ─────────────────────────────────────────────
function fmt_dash(float $n, string $moneda = 'MXN'): string {
    if ($n >= 1_000_000) return '$' . number_format($n / 1_000_000, 1) . 'M';
    if ($n >= 1_000)     return '$' . number_format($n / 1_000, 0) . 'K';
    return '$' . number_format($n, 0);
}
function fmt_full(float $n): string {
    return '$' . number_format($n, 0, '.', ',');
}
function iniciales_d(string $nombre): string {
    $p = array_filter(explode(' ', $nombre));
    $i = '';
    foreach (array_slice($p, 0, 2) as $w) $i .= strtoupper($w[0]);
    return $i ?: '?';
}
function dias_lbl(int $dias, bool $pasado = false): array {
    if ($pasado) {
        if ($dias === 0)  return ['Hoy',        'dias-green'];
        if ($dias === 1)  return ['Hace 1 día',  'dias-green'];
        if ($dias <= 7)   return ["Hace $dias días", 'dias-green'];
        return ["Hace $dias días", 'dias-amb'];
    } else {
        if ($dias < 0)    return ['Venció hace ' . abs($dias) . ' d.', 'dias-red'];
        if ($dias === 0)  return ['Vence hoy',    'dias-red'];
        if ($dias <= 2)   return ["Vence en $dias días", 'dias-red'];
        if ($dias <= 5)   return ["Vence en $dias días", 'dias-amb'];
        return ["Vence en $dias días", 'dias-green'];
    }
}

$mes_lbl_cap = ucfirst($mes_lbl);

$trial = trial_info($empresa_id);

$page_title = 'Inicio';
ob_start();
?>
<style>
/* TERMÓMETRO */
.thermo{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:14px 18px;box-shadow:var(--sh);margin-bottom:16px;display:flex;align-items:center;gap:16px}
.thermo-gauge{position:relative;width:48px;height:48px;flex-shrink:0}
.thermo-gauge svg{transform:rotate(-90deg)}
.thermo-gauge-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font:800 16px var(--num);letter-spacing:-.02em}
.thermo-info{flex:1;min-width:0}
.thermo-nivel{font:700 13px var(--body);margin-bottom:2px}
.thermo-detail{font:400 12px var(--body);color:var(--t3);line-height:1.4}
.thermo-detail b{color:var(--text);font-weight:600}
.thermo-bars{display:flex;gap:6px;margin-top:6px}
.thermo-bar{flex:1;height:4px;border-radius:2px;background:var(--border)}
.thermo-bar-fill{height:100%;border-radius:2px;transition:width .4s}
.thermo-bar-lbl{font:500 9px var(--body);color:var(--t3);margin-top:2px;text-align:center}
.thermo-diag{font:400 12px var(--body);color:var(--t3);margin-top:8px;line-height:1.4}
@media(max-width:600px){.thermo{flex-direction:column;text-align:center;gap:10px}.thermo-bars{justify-content:center}}

/* LEADERBOARD */
.lb{background:var(--white);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);margin-bottom:16px;overflow:hidden}
.lb-head{padding:10px 16px 8px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.lb-title{font:700 13px var(--body);letter-spacing:-.01em}
.lb-sub{font:400 11px var(--body);color:var(--t3)}
.lb-row{display:grid;grid-template-columns:24px 30px 1fr auto auto;align-items:center;gap:10px;padding:8px 16px;border-bottom:1px solid var(--border);transition:background .1s}
.lb-row:last-child{border-bottom:none}
.lb-row:hover{background:var(--bg)}
.lb-rank{font:800 14px var(--num);color:var(--t3);text-align:center}
.lb-rank-1{color:#f59e0b}
.lb-rank-2{color:#94a3b8}
.lb-rank-3{color:#cd7f32}
.lb-av{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font:700 11px var(--body);color:#fff}
.lb-name{font:600 13px var(--body);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0}
.lb-diag{font:400 11px var(--body);color:var(--t3);white-space:normal;line-height:1.3;margin-top:2px}
.lb-stats{display:grid;grid-template-columns:repeat(4,52px);gap:4px}
.lb-stat{text-align:center}
.lb-stat-val{font:700 12px var(--num);display:block}
.lb-stat-lbl{font:400 9px var(--body);color:var(--t3);display:block}
.lb-score{display:flex;align-items:center;gap:5px;min-width:70px;justify-content:flex-end}
.lb-score-num{font:800 16px var(--num)}
.lb-nivel{font:600 9px var(--body);letter-spacing:.04em;text-transform:uppercase;padding:1px 6px;border-radius:8px}
.lb-info{max-height:0;overflow:hidden;transition:max-height .25s ease}
.lb-info.lb-info-open{max-height:400px}
.lb-info-inner{padding:10px 16px 12px;background:var(--bg);border-bottom:1px solid var(--border);font:400 12px/1.6 var(--body);color:var(--t2)}
.lb-info-inner b{font-weight:600;color:var(--text)}
.lb-info-inner p{margin:6px 0}
.lb-info-inner ul{margin:4px 0;padding-left:18px}
.lb-info-inner li{margin-bottom:2px}
/* Leaderboard collapsible */
#lb-body{overflow:hidden;max-height:2000px;transition:max-height .3s ease}
#lb-body.lb-collapsed{max-height:0}
.lb-chevron{flex-shrink:0;transition:transform .25s;transform:rotate(-90deg)}
.lb-chevron.lb-chevron-open{transform:rotate(0)}
@media(max-width:700px){.lb-stats{display:none}.lb-row{grid-template-columns:24px 30px 1fr auto}}
/* KPI GRID */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.kpi-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px;box-shadow:var(--sh);position:relative;overflow:hidden}
.kpi-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px}
.kpi-ventas::before{background:var(--g)}
.kpi-cobrado::before{background:#059669}
.kpi-pendiente::before{background:#f59e0b}
.kpi-cots::before{background:var(--blue)}
.kpi-label{font:700 10px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);margin-bottom:6px}
.kpi-val{font:300 26px var(--num);letter-spacing:-.02em;line-height:1;color:var(--text)}
.kpi-val.green{color:var(--g)}.kpi-val.amber{color:#b45309}.kpi-val.blue{color:var(--blue)}
.kpi-sub{font:400 12px var(--num);color:var(--t3);margin-top:5px}
.kpi-sub span{color:var(--g);font-weight:500}
.kpi-sub span.neg{color:var(--danger)}
.kpi-ico{position:absolute;top:14px;right:14px;font-size:18px;opacity:.22}

/* CONVERSIÓN */
.conv-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.conv-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px;box-shadow:var(--sh)}
.conv-title{font:700 13px var(--body);margin-bottom:12px}
.conv-funnel{display:flex;flex-direction:column;gap:6px}
.funnel-row{display:flex;align-items:center;gap:10px}
.funnel-lbl{font:500 12px var(--body);color:var(--t2);width:90px;flex-shrink:0}
.funnel-bar-wrap{flex:1;height:20px;background:var(--bg);border-radius:4px;overflow:hidden}
.funnel-bar{height:100%;border-radius:4px;transition:width .6s ease}
.fb-total{background:#cbd5e1}.fb-enviadas{background:#60a5fa}.fb-vistas{background:#34d399}.fb-cerradas{background:var(--g)}
.funnel-num{font:600 12px var(--num);color:var(--text);width:26px;text-align:right;flex-shrink:0}
.funnel-pct{font:400 11px var(--num);color:var(--t3);width:36px;text-align:right;flex-shrink:0}
.conv-metrics{display:flex;flex-direction:column;gap:8px}
.conv-metric{display:flex;justify-content:space-between;align-items:center;padding:9px 12px;border-radius:var(--r-sm);background:var(--bg)}
.conv-metric-lbl{font:500 13px var(--body);color:var(--t2)}
.conv-metric-val{font:600 15px var(--num);color:var(--text)}
.conv-metric-val.g{color:var(--g)}.conv-metric-val.b{color:var(--blue)}.conv-metric-val.a{color:#b45309}

/* ALERTAS */
.alert-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.alert-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden}
.alert-header{padding:11px 14px 10px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.alert-title{font:700 13px var(--body);display:flex;align-items:center;gap:7px}
.alert-badge{padding:2px 8px;border-radius:20px;font:700 11px var(--body)}
.ab-green{background:var(--g-light);color:var(--g)}.ab-red{background:var(--danger-bg);color:var(--danger)}.ab-amber{background:var(--amb-bg);color:var(--amb)}
.alert-row{display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;transition:background .1s}
.alert-row:last-child{border-bottom:none}
.alert-row:hover{background:#fafaf8}
.alert-empty{padding:20px 14px;text-align:center;font:400 13px var(--body);color:var(--t3)}
.alert-av{width:32px;height:32px;border-radius:8px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 12px var(--body);color:#fff;flex-shrink:0}
.alert-av.red{background:var(--danger)}.alert-av.amber{background:#d97706}.alert-av.slate{background:#64748b}
.alert-info{flex:1;min-width:0}
.alert-name{font:600 13px var(--body);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.alert-meta{font:400 11px var(--num);color:var(--t3);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.alert-r{text-align:right;flex-shrink:0}
.alert-monto{font:500 13px var(--num);color:var(--text)}
.alert-dias{font:700 11px var(--body);margin-top:2px}
.dias-red{color:var(--danger)}.dias-amb{color:#b45309}.dias-green{color:var(--g)}

/* RADAR BUCKETS */
.buckets-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.bucket-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}
.bucket-header{padding:12px 14px 10px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.bucket-title{font:700 13px var(--body);display:flex;align-items:center;gap:7px}
.b-probable .bucket-header{background:#fffbeb}.b-inminente .bucket-header{background:#fff7ed}.b-onfire .bucket-header{background:#fff1f2}.b-precio .bucket-header{background:#eff6ff}
.bucket-total{font:600 12px var(--num);color:var(--t3)}
.bucket-row{display:flex;align-items:center;gap:8px;padding:9px 14px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;transition:background .1s}
.bucket-row:last-child{border-bottom:none}
.bucket-row:hover{background:#fafaf8}
.bucket-av{width:28px;height:28px;border-radius:7px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 11px var(--body);color:#fff;flex-shrink:0}
.bucket-info{flex:1;min-width:0}
.bucket-client{font:600 12px var(--body);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bucket-proyecto{font:400 11px var(--body);color:var(--t3);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bucket-r{text-align:right;flex-shrink:0}
.bucket-monto{font:500 12px var(--num)}
.heat{display:flex;gap:2px;margin-top:3px;justify-content:flex-end}
.heat-dot{width:6px;height:6px;border-radius:50%}
.hd-off{background:var(--border)}.hd-low{background:#fbbf24}.hd-mid{background:#f97316}.hd-hot{background:#ef4444}
.bucket-empty{padding:20px 14px;text-align:center;font:400 13px var(--body);color:var(--t3)}

/* RECIBOS DEL DÍA */
.recibo-row{display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;transition:background .1s}
.recibo-row:last-child{border-bottom:none}
.recibo-row:hover{background:#fafaf8}
.recibo-ico{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font:700 12px var(--body);flex-shrink:0}
.recibo-ico.pago{background:var(--g-bg);color:var(--g)}.recibo-ico.cancel{background:var(--danger-bg);color:var(--danger)}
.recibo-info{flex:1;min-width:0}
.recibo-name{font:600 13px var(--body);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.recibo-meta{font:400 11px var(--num);color:var(--t3);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.recibo-r{text-align:right;flex-shrink:0}
.recibo-monto{font:500 13px var(--num)}
.recibo-badge{font:700 10px var(--body);padding:2px 7px;border-radius:10px;margin-top:2px;display:inline-block}

/* ACTIVIDAD MENSUAL */
.monthly-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.monthly-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px;box-shadow:var(--sh)}
.monthly-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.monthly-title{font:700 13px var(--body)}
.monthly-big{font:300 32px var(--num);letter-spacing:-.03em;line-height:1;margin-bottom:4px}
.monthly-big.g{color:var(--g)}
.monthly-sub{font:400 12px var(--num);color:var(--t3)}
.monthly-divider{height:1px;background:var(--border);margin:12px 0}
.monthly-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0}
.monthly-row-lbl{font:500 12px var(--body);color:var(--t2)}
.monthly-row-val{font:500 13px var(--num);color:var(--text)}

/* SECTION LABEL */
.slabel{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t2);margin:24px 0 10px;display:flex;align-items:center;gap:10px}
.slabel::after{content:'';flex:1;height:1.5px;background:var(--border)}
.slabel:first-child{margin-top:0}

/* RESPONSIVE */
@media(max-width:900px){
  .kpi-grid{grid-template-columns:repeat(2,1fr)}
  .conv-grid,.monthly-grid{grid-template-columns:1fr}
  .alert-grid{grid-template-columns:1fr}
  .buckets-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:600px){
  .kpi-grid{grid-template-columns:1fr 1fr}
  .kpi-val{font-size:20px}
}
</style>

<?php if ($trial['agotado'] || $trial['vencido']): ?>
<div style="background:<?= $trial['vencido'] ? '#fff5f5' : 'var(--amb-bg)' ?>;border:1px solid <?= $trial['vencido'] ? '#fca5a5' : '#fcd34d' ?>;border-radius:var(--r);padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="width:48px;height:48px;border-radius:50%;background:<?= $trial['vencido'] ? '#fee2e2' : '#fde68a' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg viewBox="0 0 24 24" fill="none" stroke="<?= $trial['vencido'] ? '#c53030' : '#92400e' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div style="flex:1">
        <?php if ($trial['vencido']): ?>
            <div style="font:700 15px var(--body);color:#c53030;margin-bottom:2px">Licencia vencida</div>
            <div style="font:400 13px var(--body);color:#991b1b;line-height:1.5">Tu plan <?= $trial['plan_label'] ?> venció el <?= date('d/m/Y', strtotime($trial['plan_vence'])) ?>. Renueva para seguir creando cotizaciones.</div>
        <?php else: ?>
            <div style="font:700 15px var(--body);color:#92400e;margin-bottom:2px">Plan Free agotado</div>
            <div style="font:400 13px var(--body);color:#78350f;line-height:1.5">Has usado las <?= TRIAL_LIMIT ?> cotizaciones gratuitas. Activa tu plan Pro para continuar.</div>
        <?php endif; ?>
    </div>
    <a href="/licencia" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:var(--r-sm);font:600 13px var(--body);background:<?= $trial['vencido'] ? '#c53030' : '#92400e' ?>;color:#fff;text-decoration:none;white-space:nowrap;transition:opacity .12s" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        Activar licencia
    </a>
</div>
<?php endif; ?>

<!-- SELECTOR DE PERÍODO (en topbar via slot extra) -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
  <div>
    <h1 style="font:800 22px var(--body); letter-spacing:-.02em;">Inicio</h1>
    <p style="font:400 13px var(--body); color:var(--t3); margin-top:2px;"><?= e($mes_lbl_cap) ?></p>
  </div>
  <form method="get" style="display:flex; align-items:center; gap:6px;">
    <select name="periodo" onchange="this.form.submit()"
            style="padding:8px 12px; border-radius:var(--r-sm); border:1.5px solid var(--border); font:600 13px var(--body); color:var(--t2); background:var(--white); box-shadow:var(--sh); cursor:pointer;">
      <option value="mes_actual"  <?= $periodo==='mes_actual' ?'selected':''?>>Este mes</option>
      <option value="mes_ant"     <?= $periodo==='mes_ant'    ?'selected':''?>>Mes anterior</option>
      <option value="30_dias"     <?= $periodo==='30_dias'    ?'selected':''?>>Últimos 30 días</option>
      <option value="90_dias"     <?= $periodo==='90_dias'    ?'selected':''?>>Últimos 90 días</option>
      <option value="anio"        <?= $periodo==='anio'       ?'selected':''?>>Este año</option>
    </select>
  </form>
</div>

<!-- ══ TERMÓMETRO + LEADERBOARD ══ -->
<?php
$ts = $mi_score;
$ts_en_gracia = ($ts['nivel'] ?? '') === 'nuevo' || !empty($ts['en_gracia']);

if ($ts_en_gracia):
  $ts_dias_rest = (int)($ts['dias_restantes'] ?? 0);
?>
<div class="thermo" style="justify-content:center;text-align:center;padding:24px 18px">
    <div style="display:flex;flex-direction:column;align-items:center;gap:10px">
      <div style="width:48px;height:48px;border-radius:50%;background:#f0f9ff;display:flex;align-items:center;justify-content:center">
        <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div>
        <div style="font:700 14px var(--body);color:#1e40af;margin-bottom:4px">Analizando tu actividad</div>
        <div style="font:400 13px var(--body);color:var(--t2);line-height:1.5;max-width:360px">
          Estamos recopilando información para calcular tu índice de productividad.
          <?php if ($ts_dias_rest > 0): ?>
          Tu score se activará en <b><?= $ts_dias_rest ?> día<?= $ts_dias_rest > 1 ? 's' : '' ?></b>.
          <?php else: ?>
          Tu score se activará pronto.
          <?php endif; ?>
        </div>
        <div style="font:400 12px var(--body);color:var(--t3);margin-top:8px">Sigue cotizando y usando la plataforma con normalidad.</div>
      </div>
    </div>
</div>
<?php else:
$ts_color = match($ts['nivel']) {
    'top'     => '#2563eb',
    'activo'  => '#16a34a',
    'regular' => '#d97706',
    default   => '#dc2626',
};
$ts_label = match($ts['nivel']) {
    'top'     => 'Excepcional',
    'activo'  => 'Buen ritmo',
    'regular' => 'Puede mejorar',
    default   => 'Necesita atención',
};
$ts_pct   = $ts['score'];
$ts_circ  = 2 * M_PI * 20;
$ts_dash  = $ts_circ * ($ts_pct / 100);
$ts_mom   = (float)($ts['momentum'] ?? 1);
$ts_arrow = $ts_mom >= 1.05 ? '↑' : ($ts_mom <= 0.95 ? '↓' : '→');
$ts_mom_c = $ts_mom >= 1.05 ? '#16a34a' : ($ts_mom <= 0.95 ? '#dc2626' : '#6b7280');

// Dimensiones para barras
$ts_act = min(100, round((float)($ts['s_activacion'] ?? 0) * 100));
$ts_eng = min(100, round((float)($ts['s_engagement'] ?? 0) * 100));
$ts_seg = min(100, round((float)($ts['s_seguimiento'] ?? 0) * 100));
$ts_hlt = min(100, round((float)($ts['s_radar_health'] ?? 0) * 100));
$ts_con = min(100, round((float)($ts['s_conversion'] ?? 0) * 100));

// Métricas de detalle
$ts_asig = (int)($ts['cot_asignadas'] ?? 0);
$ts_vist = (int)($ts['cot_vistas'] ?? 0);
$ts_dorm = (int)($ts['cot_dormidas'] ?? 0);
$ts_cierres = (int)($ts['conversiones'] ?? 0);
$ts_cbuck = (int)($ts['cierres_bucket'] ?? 0);
$ts_sdto  = (int)($ts['cierres_sin_dto'] ?? 0);
$ts_cal   = (int)($ts['cots_calientes'] ?? $ts['radar_benchmark'] ?? 0);
$ts_fb    = (int)($ts['fb_total'] ?? $ts['radar_views'] ?? 0);
$ts_ign   = max(0, $ts_cal - $ts_fb); // calientes sin feedback
$ts_pen   = (float)($ts['penalizaciones'] ?? 0);
$ts_diag  = ActividadScore::diagnostico($ts);
?>
<div class="thermo">
    <div class="thermo-gauge">
      <svg width="48" height="48" viewBox="0 0 48 48">
        <circle cx="24" cy="24" r="20" fill="none" stroke="var(--border)" stroke-width="4"/>
        <circle cx="24" cy="24" r="20" fill="none" stroke="<?= $ts_color ?>" stroke-width="4"
                stroke-dasharray="<?= round($ts_dash, 1) ?> <?= round($ts_circ, 1) ?>"
                stroke-linecap="round"/>
      </svg>
      <div class="thermo-gauge-num" style="color:<?= $ts_color ?>"><?= $ts_pct ?></div>
    </div>
    <div class="thermo-info">
      <div class="thermo-nivel" style="color:<?= $ts_color ?>"><?= $ts_label ?> <span style="color:<?= $ts_mom_c ?>;font-size:14px"><?= $ts_arrow ?></span></div>
      <div class="thermo-detail">
        <b><?= $ts_vist ?></b>/<?= $ts_asig ?> abiertas · <b><?= $ts_cierres ?></b> cierres<?php if($ts_cbuck): ?> (<b><?= $ts_cbuck ?></b> desde radar)<?php endif; ?><?php if($ts_dorm): ?> · <span style="color:var(--danger)"><?= $ts_dorm ?> dormidas</span><?php endif; ?><?php if($ts_ign): ?> · <span style="color:var(--danger)"><?= $ts_ign ?> sin feedback</span><?php endif; ?>
      </div>
      <div class="thermo-bars">
        <div style="flex:1">
          <div class="thermo-bar"><div class="thermo-bar-fill" style="width:<?= $ts_act ?>%;background:<?= $ts_act >= 60 ? '#16a34a' : ($ts_act >= 30 ? '#d97706' : '#dc2626') ?>"></div></div>
          <div class="thermo-bar-lbl">Activación</div>
        </div>
        <div style="flex:1">
          <div class="thermo-bar"><div class="thermo-bar-fill" style="width:<?= $ts_eng ?>%;background:<?= $ts_eng >= 60 ? '#16a34a' : ($ts_eng >= 30 ? '#d97706' : '#dc2626') ?>"></div></div>
          <div class="thermo-bar-lbl">Engagement</div>
        </div>
        <div style="flex:1">
          <div class="thermo-bar"><div class="thermo-bar-fill" style="width:<?= $ts_seg ?>%;background:<?= $ts_seg >= 60 ? '#16a34a' : ($ts_seg >= 30 ? '#d97706' : '#dc2626') ?>"></div></div>
          <div class="thermo-bar-lbl">Seguimiento</div>
        </div>
        <div style="flex:1">
          <div class="thermo-bar"><div class="thermo-bar-fill" style="width:<?= $ts_hlt ?>%;background:<?= $ts_hlt >= 60 ? '#16a34a' : ($ts_hlt >= 30 ? '#d97706' : '#dc2626') ?>"></div></div>
          <div class="thermo-bar-lbl">Pipeline</div>
        </div>
        <div style="flex:1">
          <div class="thermo-bar"><div class="thermo-bar-fill" style="width:<?= $ts_con ?>%;background:<?= $ts_con >= 60 ? '#16a34a' : ($ts_con >= 30 ? '#d97706' : '#dc2626') ?>"></div></div>
          <div class="thermo-bar-lbl">Conversión</div>
        </div>
      </div>
      <div class="thermo-diag"><?= e($ts_diag) ?></div>
    </div>
  </div>

<?php endif; ?>

<style>.dbg-chev-open{transform:rotate(90deg)}.dbg-open{display:block!important}.dbg-row{display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding:2px 0}.dbg-lbl{color:var(--t3)}.dbg-val{font-weight:600}.dbg-neg{color:var(--danger)}.dbg-sec{font:700 11px var(--body);letter-spacing:.06em;text-transform:uppercase;color:var(--t3);margin:10px 0 4px;padding-top:8px;border-top:1px solid var(--border)}</style>

<?php if ($es_admin_dash && count($equipo_scores) > 0): ?>
  <div class="lb">
    <div class="lb-head" onclick="var b=document.getElementById('lb-body');b.classList.toggle('lb-collapsed');this.querySelector('.lb-chevron').classList.toggle('lb-chevron-open')" style="cursor:pointer;user-select:none">
      <div style="flex:1">
        <div class="lb-title">Ranking del equipo</div>
        <div class="lb-sub">15 días · auto-ajustable · <?= count($equipo_scores) ?> miembros</div>
      </div>
      <svg class="lb-chevron lb-chevron-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--t3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      <button onclick="event.stopPropagation();document.getElementById('lb-info').classList.toggle('lb-info-open')" style="background:none;border:1px solid var(--border);border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--t3);font:600 12px var(--body);flex-shrink:0;margin-left:6px" title="¿Cómo funciona?">?</button>
    </div>
    <div id="lb-body">
    <div id="lb-info" class="lb-info">
      <div class="lb-info-inner">
        <b>¿Qué mide este ranking?</b>
        <p>Algoritmo APC v5.1 — 15 días rolling, 100% auto-ajustable:</p>
        <ul>
          <li><b>Activación (8%)</b> — ¿Las cotizaciones llegan al cliente? Penaliza no abiertas (×1/tasa cierre) y dormidas (escalado por tiempo promedio de cierre de la empresa).</li>
          <li><b>Engagement (17%)</b> — Capa de penalizaciones: ventas sin cobrar (×1/tasa cierre, fuerte), descuentos y enfriamiento del pipeline (×tasa cierre, suave), ventas por debajo del promedio de la empresa.</li>
          <li><b>Seguimiento (25%)</b> — ¿Das feedback a las señales calientes del Radar? Se evalúa el esfuerzo (dar feedback) y el resultado (¿acertaste?). Con más feedbacks, la calidad pesa más que el esfuerzo.</li>
          <li><b>Radar Health (15%)</b> — ¿Tu pipeline mejora o empeora? Cuenta transiciones de temperatura de tus cotizaciones: frío→caliente suma, caliente→frío resta.</li>
          <li><b>Conversión (35%)</b> — ¿Cierras ventas? Tasa de cierre vs empresa, calidad (cerrar ventas difíciles vale más), velocidad vs promedio, tendencia de volumen (ventas actuales vs período anterior), consistencia semanal.</li>
        </ul>
        <p><b>Auto-ajuste:</b> Todas las penalizaciones escalan con la tasa de cierre de la empresa. Sin valores fijos — cada empresa tiene su propia escala.</p>
        <p><b>Score final:</b> Los pesos del score se ajustan automáticamente: con pocos vendedores domina el proporcional. Con equipo grande, el percentil gana peso. La tendencia (momentum) escala con la tasa de cierre. Flechas: ↑ mejorando, → estable, ↓ decayendo.</p>
        <p><b>Niveles:</b> Top (86-100) · Activo (61-85) · Regular (31-60) · Bajo (0-30) · Nuevo (primeros días).</p>
        <p style="color:var(--t3);font-style:italic;margin-bottom:0">Nota: Índice algorítmico basado en datos de uso de la plataforma. Referencia de productividad comercial, no evaluación personal.</p>
      </div>
    </div>
    <?php
    $rank = 0;
    foreach ($equipo_scores as $es):
      $rank++;
      $es_score = (int)$es['score'];
      $es_color = match($es['nivel']) {
          'top' => '#2563eb', 'activo' => '#16a34a', 'regular' => '#d97706', 'nuevo' => '#6b7280', default => '#dc2626'
      };
      $es_bg = match($es['nivel']) {
          'top' => '#eff6ff', 'activo' => '#f0fdf4', 'regular' => '#fffbeb', 'nuevo' => '#f9fafb', default => '#fef2f2'
      };
      $es_lbl = match($es['nivel']) {
          'top' => 'Top', 'activo' => 'Activo', 'regular' => 'Regular', 'nuevo' => 'Nuevo', default => 'Bajo'
      };
      $es_ini = strtoupper(mb_substr($es['nombre'], 0, 1));
      $es_av_bg = $es['rol'] === 'admin' ? 'var(--g)' : '#64748b';
      $es_mom = (float)$es['momentum'];
      $es_arrow = $es_mom >= 1.05 ? '↑' : ($es_mom <= 0.95 ? '↓' : '→');
      $es_mom_c = $es_mom >= 1.05 ? '#16a34a' : ($es_mom <= 0.95 ? '#dc2626' : '#9ca3af');
      $rank_cls = $rank <= 3 ? "lb-rank-{$rank}" : '';
      $es_diag = ActividadScore::diagnostico($es);
    ?>
    <div class="lb-row">
      <div class="lb-rank <?= $rank_cls ?>"><?= $rank ?></div>
      <div class="lb-av" style="background:<?= $es_av_bg ?>"><?= e($es_ini) ?></div>
      <div class="lb-name">
        <?= e($es['nombre']) ?>
        <div class="lb-diag"><?= e($es_diag) ?></div>
      </div>
      <?php if ($es['nivel'] !== 'nuevo'): ?>
      <div class="lb-stats">
        <div class="lb-stat"><span class="lb-stat-val"><?= (int)($es['cot_vistas'] ?? 0) ?>/<?= (int)($es['cot_asignadas'] ?? 0) ?></span><span class="lb-stat-lbl">Abiertas</span></div>
        <div class="lb-stat"><span class="lb-stat-val"><?= (int)$es['conversiones'] ?></span><span class="lb-stat-lbl">Cierres</span></div>
        <div class="lb-stat"><span class="lb-stat-val"><?= (int)($es['cierres_bucket'] ?? 0) ?></span><span class="lb-stat-lbl">Radar</span></div>
        <div class="lb-stat"><span class="lb-stat-val" style="color:<?= (int)($es['cot_dormidas'] ?? 0) > 0 ? 'var(--danger)' : 'inherit' ?>"><?= (int)($es['cot_dormidas'] ?? 0) ?></span><span class="lb-stat-lbl">Dormidas</span></div>
      </div>
      <div class="lb-score">
        <span class="lb-score-num" style="color:<?= $es_color ?>"><?= $es_score ?></span>
        <span style="color:<?= $es_mom_c ?>;font-size:12px"><?= $es_arrow ?></span>
        <span class="lb-nivel" style="color:<?= $es_color ?>;background:<?= $es_bg ?>"><?= $es_lbl ?></span>
      </div>
      <?php else: ?>
      <div class="lb-stats"></div>
      <div class="lb-score">
        <span class="lb-nivel" style="color:<?= $es_color ?>;background:<?= $es_bg ?>"><?= $es_lbl ?></span>
      </div>
      <?php endif; ?>
    </div>
    <?php if (Auth::es_superadmin()): ?>
    <!-- Debug expandible por vendedor (solo superadmin) -->
    <div style="border-top:1px dashed var(--border);padding:2px 14px 2px 52px">
      <span onclick="var p=this.nextElementSibling;p.style.display=p.style.display==='none'?'block':'none'" style="font:600 10px var(--body);color:var(--t3);cursor:pointer;letter-spacing:.05em;text-transform:uppercase;opacity:.6">▶ debug</span>
      <div style="display:none;padding:6px 0;font:400 11px var(--num);color:var(--t2);line-height:1.7">
        <div class="dbg-row"><span class="dbg-lbl">Activación (10%)</span><span class="dbg-val"><?= round(($es['s_activacion'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Engagement (17%)</span><span class="dbg-val"><?= round(($es['s_engagement'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">  pen sin pago</span><span class="dbg-val dbg-neg"><?= ($es['eng_pen_sin_pago'] ?? 0) > 0 ? '-'.round(($es['eng_pen_sin_pago'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">  pen descuento</span><span class="dbg-val dbg-neg"><?= ($es['eng_pen_descuento'] ?? 0) > 0 ? '-'.round(($es['eng_pen_descuento'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">  pen enfriamiento</span><span class="dbg-val dbg-neg"><?= ($es['eng_pen_enfriamiento'] ?? 0) > 0 ? '-'.round(($es['eng_pen_enfriamiento'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">  pen bajo benchmark</span><span class="dbg-val dbg-neg"><?php $epbb = $es['eng_pen_bajo_benchmark'] ?? 0; if ($epbb > 0) { echo '-'.round($epbb * 100, 1).'% ('.($es['ventas_periodo'] ?? '?').' vs '.($es['bench_ventas'] ?? '?').')'; } else echo '—'; ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Seguimiento (25%)</span><span class="dbg-val"><?= round(($es['s_seguimiento'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Radar Health (15%)</span><span class="dbg-val"><?= round(($es['s_radar_health'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">  pipeline ↑ / ↓</span><span class="dbg-val"><?= (int)($es['health_up'] ?? $es['transiciones_up'] ?? 0) ?> / <?= (int)($es['health_down'] ?? $es['senales_ignoradas'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Conversión (35%)</span><span class="dbg-val"><?= round(($es['s_conversion'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Penalizaciones</span><span class="dbg-val dbg-neg"><?= ($es['penalizaciones'] ?? 0) > 0 ? '-'.round(($es['penalizaciones'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Bonuses</span><span class="dbg-val"><?= round(($es['bonuses'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Proporcional (×<?= round(($es['w_proporcional'] ?? 0.9) * 100) ?>%)</span><span class="dbg-val"><?= round(($es['tasa_gestion'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Momentum (×<?= round(($es['w_momentum'] ?? 0.1) * 100) ?>%)</span><span class="dbg-val"><?= number_format($es['momentum'] ?? 1, 2) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Percentil (×<?= round(($es['w_percentil'] ?? 0) * 100) ?>%)</span><span class="dbg-val"><?= round(($es['percentil'] ?? 0) * 100) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Asig / Vistas / Cierres</span><span class="dbg-val"><?= (int)($es['cot_asignadas'] ?? 0) ?> / <?= (int)($es['cot_vistas'] ?? 0) ?> / <?= (int)($es['conversiones'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Dormidas 7d</span><span class="dbg-val"><?= (int)($es['cot_dormidas'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">No abiertas 5d</span><span class="dbg-val dbg-neg"><?= (int)($es['no_abiertas_5d'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Pen no abiertas</span><span class="dbg-val dbg-neg"><?= ($es['pen_no_abiertas'] ?? 0) > 0 ? '-'.round(($es['pen_no_abiertas'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Cierres radar / Sin dto</span><span class="dbg-val"><?= (int)($es['cierres_bucket'] ?? 0) ?> / <?= (int)($es['cierres_sin_dto'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Calientes / con feedback</span><span class="dbg-val"><?= (int)($es['radar_benchmark'] ?? 0) ?> / <?= (int)($es['radar_views'] ?? 0) ?></span></div>
      </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
    </div><!-- /lb-body -->
  </div>
<?php endif; ?>

<!-- ══ KPIs FINANCIEROS ══ -->
<div class="slabel">Resumen financiero · <?= e($mes_lbl_cap) ?></div>
<div class="kpi-grid">

  <div class="kpi-card kpi-ventas">
    <div class="kpi-ico"><?= ico('money', 22, '#16a34a') ?></div>
    <div class="kpi-label">Ventas del período</div>
    <div class="kpi-val green"><?= fmt_dash((float)$kpi_ventas['monto_ventas']) ?></div>
    <div class="kpi-sub">
      <?= (int)$kpi_ventas['num_ventas'] ?> venta<?= $kpi_ventas['num_ventas']!=1?'s':'' ?> ·
      <?php if ($delta_ventas !== 0): ?>
      <span class="<?= $delta_ventas >= 0 ? '' : 'neg' ?>">
        <?= $delta_ventas >= 0 ? '↑' : '↓' ?> <?= abs($delta_ventas) ?>% vs anterior
      </span>
      <?php else: ?>
      <span>sin cambio</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="kpi-card kpi-cobrado">
    <div class="kpi-ico"><?= ico('check', 22, '#16a34a') ?></div>
    <div class="kpi-label">Cobrado</div>
    <div class="kpi-val green"><?= fmt_dash((float)$kpi_ventas['monto_cobrado']) ?></div>
    <div class="kpi-sub">
      <?php
      $pct_cob = $kpi_ventas['monto_ventas'] > 0
          ? round(($kpi_ventas['monto_cobrado'] / $kpi_ventas['monto_ventas']) * 100, 1) : 0;
      ?>
      <?= $pct_cob ?>% del total ·
      <span><?= $num_ventas_pagadas ?> venta<?= $num_ventas_pagadas!=1?'s':'' ?> completa<?= $num_ventas_pagadas!=1?'s':'' ?></span>
    </div>
  </div>

  <div class="kpi-card kpi-pendiente">
    <div class="kpi-ico"><?= ico('clock', 22, '#d97706') ?></div>
    <div class="kpi-label">Por cobrar</div>
    <div class="kpi-val amber"><?= fmt_dash((float)$kpi_ventas['monto_saldo']) ?></div>
    <div class="kpi-sub">
      <?= $num_ventas_saldo ?> venta<?= $num_ventas_saldo!=1?'s':'' ?> con saldo ·
      <?php $pct_pend = 100 - $pct_cob; ?>
      <span class="<?= $pct_pend > 50 ? 'neg' : '' ?>"><?= $pct_pend ?>% pendiente</span>
    </div>
  </div>

  <div class="kpi-card kpi-cots">
    <div class="kpi-ico"><?= ico('file', 22, '#2563eb') ?></div>
    <div class="kpi-label">Cotizaciones creadas</div>
    <div class="kpi-val blue"><?= (int)$kpi_cots['num_cots'] ?></div>
    <div class="kpi-sub">
      <?= fmt_dash((float)$kpi_cots['monto_cots']) ?> total cotizado ·
      <?php if ($delta_cots !== 0): ?>
      <span class="<?= $delta_cots < 0 ? 'neg' : '' ?>">
        <?= $delta_cots >= 0 ? '↑' : '↓' ?> <?= abs($delta_cots) ?> vs anterior
      </span>
      <?php else: ?>
      <span>igual que antes</span>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php if (!empty($ventas_sin_pago)): ?>
<!-- ══ VENTAS SIN PAGOS ══ -->
<div class="slabel"><?= ico('alert', 14, '#d97706') ?> Ventas sin pagos <span style="font:400 12px var(--body);color:var(--t3)">(<?= count($ventas_sin_pago) ?>)</span></div>
<div class="card" style="overflow:hidden">
  <?php foreach ($ventas_sin_pago as $vsp):
    $dias_sin = max(0, (int)(new DateTime($ahora->format('Y-m-d')))->diff(new DateTime(substr($vsp['created_at'], 0, 10)))->days);
    $color_dias = $dias_sin > 14 ? 'var(--danger)' : ($dias_sin > 7 ? '#d97706' : 'var(--t3)');
  ?>
  <a href="/ventas/<?= (int)$vsp['id'] ?>" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;transition:background .12s" onmouseover="this.style.background='#fafaf8'" onmouseout="this.style.background=''">
    <div style="flex:1;min-width:0">
      <div style="font:600 13px var(--num);color:var(--g)"><?= e($vsp['numero']) ?></div>
      <div style="font:400 12px var(--body);color:var(--t2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($vsp['titulo']) ?></div>
      <div style="font:400 11px var(--body);color:var(--t3)"><?= e($vsp['cliente'] ?? '—') ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <div style="font:700 14px var(--num)"><?= fmt_dash((float)$vsp['total']) ?></div>
      <div style="font:400 11px var(--body);color:<?= $color_dias ?>"><?= $dias_sin ?> día<?= $dias_sin!=1?'s':'' ?> sin pago</div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ══ CONVERSIÓN ══ -->
<div class="slabel">Métricas de conversión</div>
<div class="conv-grid">

  <div class="conv-card">
    <div class="conv-title">Embudo del período</div>
    <div class="conv-funnel">
      <?php
      $total_f = max(1, (int)$funnel['enviadas']);
      $rows_f  = [
          ['Enviadas',  'fb-total',    $funnel['enviadas'],  100],
          ['Abiertas',  'fb-vistas',   $funnel['abiertas'],  round(($funnel['abiertas'] ?? 0)/$total_f*100)],
          ['Aceptadas', 'fb-cerradas', $funnel['cerradas'],  round(($funnel['cerradas'] ?? 0)/$total_f*100)],
          ['Rechazadas','fb-rechazadas',$funnel['rechazadas'],round(($funnel['rechazadas'] ?? 0)/$total_f*100)],
      ];
      foreach ($rows_f as [$lbl, $cls, $num, $pct]):
      ?>
      <div class="funnel-row">
        <div class="funnel-lbl"><?= $lbl ?></div>
        <div class="funnel-bar-wrap">
          <div class="funnel-bar <?= $cls ?>" style="width:<?= $pct ?>%"></div>
        </div>
        <div class="funnel-num"><?= (int)$num ?></div>
        <div class="funnel-pct"><?= $pct ?>%</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="conv-card">
    <div class="conv-title">Indicadores clave</div>
    <div class="conv-metrics">
      <div class="conv-metric">
        <span class="conv-metric-lbl">Tasa de cierre</span>
        <span class="conv-metric-val g"><?= $tasa_cierre ?>%</span>
      </div>
      <div class="conv-metric">
        <span class="conv-metric-lbl">Ticket promedio</span>
        <span class="conv-metric-val"><?= $ticket_prom > 0 ? fmt_full($ticket_prom) : '—' ?></span>
      </div>
      <div class="conv-metric">
        <span class="conv-metric-lbl">Tiempo promedio de cierre</span>
        <span class="conv-metric-val b"><?= $tiempo_cierre > 0 ? number_format($tiempo_cierre, 1) . ' días' : '—' ?></span>
      </div>
      <div class="conv-metric">
        <span class="conv-metric-lbl">Cotizaciones rechazadas</span>
        <span class="conv-metric-val a"><?= (int)$funnel['rechazadas'] ?></span>
      </div>
      <div class="conv-metric">
        <span class="conv-metric-lbl">Cotizaciones sin abrir</span>
        <span class="conv-metric-val a"><?= $sin_abrir ?></span>
      </div>
      <?php if ($kpi_ventas['num_ventas'] > 0): ?>
      <div class="conv-metric" style="flex-direction:column; align-items:flex-start; gap:3px;">
        <div style="display:flex; justify-content:space-between; width:100%;">
          <span class="conv-metric-lbl">Ventas con descuento</span>
          <span class="conv-metric-val a"><?= $ventas_con_desc ?> de <?= (int)$kpi_ventas['num_ventas'] ?></span>
        </div>
        <?php if ($total_descuentos > 0): ?>
        <div style="font:400 12px var(--num); color:var(--t3);"><?= fmt_full($total_descuentos) ?> en descuentos aplicados</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ══ ALERTAS ══ -->
<div class="slabel">Alertas</div>
<div class="alert-grid">

  <!-- Aceptadas recientemente -->
  <div class="alert-card">
    <div class="alert-header">
      <div class="alert-title"><?= ico('check', 16, '#16a34a') ?> Aceptadas recientemente <span class="alert-badge ab-green"><?= count($aceptadas) ?></span></div>
    </div>
    <?php if (empty($aceptadas)): ?>
    <div class="alert-empty">Sin cotizaciones aceptadas recientemente.</div>
    <?php else: ?>
    <?php foreach ($aceptadas as $a):
      $ini = iniciales_d($a['cliente_nombre'] ?? '?');
      $dias = max(0, (int)(new DateTime($ahora->format('Y-m-d')))->diff(new DateTime(substr($a['aceptada_at'], 0, 10)))->days);
      [$dlbl, $dcls] = dias_lbl($dias, true);
      $href = $a['venta_id'] ? '/ventas/'.$a['venta_id'] : '/cotizaciones/'.$a['id'];
    ?>
    <a href="<?= $href ?>" class="alert-row">
      <div class="alert-info">
        <div class="alert-name"><?= e($a['cliente_nombre'] ?? '—') ?></div>
        <div class="alert-meta"><?= e(mb_substr($a['titulo'],0,45)) ?> · <?= e($a['vta_numero'] ?? $a['cot_numero']) ?></div>
      </div>
      <div class="alert-r">
        <div class="alert-monto"><?= fmt_dash((float)$a['total']) ?></div>
        <div class="alert-dias <?= $dcls ?>"><?= $dlbl ?></div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Rechazadas -->
  <div class="alert-card">
    <div class="alert-header">
      <div class="alert-title">✕ Rechazadas <span class="alert-badge ab-red"><?= count($rechazadas) ?></span></div>
    </div>
    <?php if (empty($rechazadas)): ?>
    <div class="alert-empty">Sin rechazos recientes.</div>
    <?php else: ?>
    <?php foreach ($rechazadas as $r):
      $ini = iniciales_d($r['cliente_nombre'] ?? '?');
      $dias = max(0, (int)(new DateTime($ahora->format('Y-m-d')))->diff(new DateTime(substr($r['rechazada_at'], 0, 10)))->days);
      [$dlbl, $dcls] = dias_lbl($dias, true);
      $dcls = $dias <= 2 ? 'dias-red' : 'dias-amb';
    ?>
    <a href="/cotizaciones/<?= (int)$r['id'] ?>" class="alert-row">
      <div class="alert-info">
        <div class="alert-name"><?= e($r['cliente_nombre'] ?? '—') ?></div>
        <div class="alert-meta"><?= e(mb_substr($r['titulo'],0,45)) ?> · <?= e($r['numero']) ?></div>
      </div>
      <div class="alert-r">
        <div class="alert-monto"><?= fmt_dash((float)$r['total']) ?></div>
        <div class="alert-dias <?= $dcls ?>"><?= $dlbl ?></div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Próximas a vencer -->
  <div class="alert-card">
    <div class="alert-header">
      <div class="alert-title">⏰ Próximas a vencer <span class="alert-badge ab-amber"><?= count($por_vencer) ?></span></div>
    </div>
    <?php if (empty($por_vencer)): ?>
    <div class="alert-empty">Sin cotizaciones próximas a vencer.</div>
    <?php else: ?>
    <?php foreach ($por_vencer as $pv):
      $ini = iniciales_d($pv['cliente_nombre'] ?? '?');
      $dias = (int)$pv['dias_restantes'];
      [$dlbl, $dcls] = dias_lbl($dias, false);
    ?>
    <a href="/cotizaciones/<?= (int)$pv['id'] ?>" class="alert-row">
      <div class="alert-info">
        <div class="alert-name"><?= e($pv['cliente_nombre'] ?? '—') ?></div>
        <div class="alert-meta"><?= e(mb_substr($pv['titulo'],0,45)) ?> · <?= e($pv['numero']) ?></div>
      </div>
      <div class="alert-r">
        <div class="alert-monto"><?= fmt_dash((float)$pv['total']) ?></div>
        <div class="alert-dias <?= $dcls ?>"><?= $dlbl ?></div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Sin abrir -->
  <div class="alert-card">
    <div class="alert-header">
      <div class="alert-title"><?= ico('mail', 16, '#d97706') ?> Sin abrir <span class="alert-badge ab-amber"><?= count($sin_abrir_list) ?></span></div>
    </div>
    <?php if (empty($sin_abrir_list)): ?>
    <div class="alert-empty">Todas las cotizaciones enviadas han sido abiertas.</div>
    <?php else: ?>
    <?php foreach ($sin_abrir_list as $sa):
      $ini  = iniciales_d($sa['cliente_nombre'] ?? '?');
      $dias = max(0, (int)$sa['dias_sin_abrir']);
      $dcls = $dias >= 5 ? 'dias-red' : ($dias >= 3 ? 'dias-amb' : 'dias-amb');
    ?>
    <a href="/cotizaciones/<?= (int)$sa['id'] ?>" class="alert-row">
      <div class="alert-info">
        <div class="alert-name"><?= e($sa['cliente_nombre'] ?? '—') ?></div>
        <div class="alert-meta"><?= e(mb_substr($sa['titulo'],0,45)) ?> · <?= e($sa['numero']) ?></div>
      </div>
      <div class="alert-r">
        <div class="alert-monto"><?= fmt_dash((float)$sa['total']) ?></div>
        <div class="alert-dias <?= $dcls ?>"><?= $dias ?> día<?= $dias!=1?'s':'' ?> sin abrir</div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<!-- ══ RADAR BUCKETS ══ -->
<?php
$hay_radar = !empty($buckets['onfire']) || !empty($buckets['inminente']) || !empty($buckets['probable_cierre']) || !empty($buckets['validando_precio']);
?>
<div class="slabel">Radar · oportunidades activas</div>
<div class="buckets-grid">

  <?php
  $bucket_def = [
      'probable_cierre'  => [ico('yellow',10), 'Probable cierre',    'b-probable'],
      'inminente'        => [ico('orange',10), 'Cierre inminente',   'b-inminente'],
      'onfire'           => [ico('red',10),    'On Fire',             'b-onfire'],
      'validando_precio' => [ico('blue',10),   'Validando precio',    'b-precio'],
  ];
  foreach ($bucket_def as $bkey => [$ico, $blbl, $bcls]):
      $items     = $buckets[$bkey];
      $total_b   = array_sum(array_column($items, 'total'));
      $count_b   = count($items);

      // Colores de avatar según bucket
      $av_colors = ['probable_cierre'=>'var(--g)', 'inminente'=>'#c2410c', 'onfire'=>'#991b1b', 'validando_precio'=>'#2563eb'];
      $av_bg     = $av_colors[$bkey] ?? '#6b7280';
  ?>
  <div class="bucket-card <?= $bcls ?>">
    <div class="bucket-header">
      <div class="bucket-title"><span class="bucket-icon"><?= $ico ?></span> <?= $blbl ?></div>
      <div class="bucket-total">
        <?= $count_b > 0 ? fmt_dash($total_b) . ' · ' . $count_b . ' cot' . ($count_b!=1?'s.':'.') : '—' ?>
      </div>
    </div>
    <?php if (empty($items)): ?>
    <div class="bucket-empty">Sin cotizaciones en este bucket.</div>
    <?php else: ?>
    <?php foreach ($items as $item):
      $ini_b = iniciales_d($item['cliente_nombre'] ?? '?');
      // Heat dots basados en score o visitas
      $score = (float)($item['radar_score'] ?? 0);
      $dots  = [];
      $dot_count = 5;
      $hot_count = min(5, max(0, (int)round($score / 20)));
      for ($d = 0; $d < $dot_count; $d++) {
          if ($d < $hot_count) {
              $dots[] = $score >= 80 ? 'hd-hot' : ($score >= 60 ? 'hd-hot' : ($score >= 40 ? 'hd-mid' : 'hd-low'));
          } else {
              $dots[] = 'hd-off';
          }
      }
    ?>
    <a href="/cotizaciones/<?= (int)$item['id'] ?>" class="bucket-row">
      <div class="bucket-info">
        <div class="bucket-client"><?= e($item['cliente_nombre'] ?? '—') ?></div>
        <div class="bucket-proyecto"><?= e(mb_substr($item['titulo'],0,40)) ?></div>
      </div>
      <div class="bucket-r">
        <div class="bucket-monto"><?= fmt_dash((float)$item['total']) ?></div>
        <div class="heat">
          <?php foreach ($dots as $dc): ?><div class="heat-dot <?= $dc ?>"></div><?php endforeach; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

</div>

<?php if (!$hay_radar): ?>
<div style="background:var(--white); border:1px solid var(--border); border-radius:var(--r); padding:20px; text-align:center; margin-top:-4px;">
  <div style="font:600 14px var(--body); color:var(--t2); margin-bottom:4px;">El Radar no tiene datos aún</div>
  <div style="font:400 13px var(--body); color:var(--t3);">Los buckets se llenan automáticamente cuando se envían cotizaciones y los clientes las visitan.</div>
</div>
<?php endif; ?>

<!-- ══ RECIBOS DEL DÍA ══ -->
<div class="slabel">Recibos del día · <?= date('d/m/Y') ?></div>
<div class="alert-card" style="margin-bottom:12px">
  <div class="alert-header">
    <div class="alert-title"><?= ico('file', 16, '#16a34a') ?> Recibos de hoy <span class="alert-badge ab-green"><?= count($recibos_hoy) ?></span></div>
  </div>
  <?php if (empty($recibos_hoy)): ?>
  <div class="alert-empty">Sin recibos registrados hoy.</div>
  <?php else: ?>
  <?php foreach ($recibos_hoy as $rec):
    $es_cancelado = (int)($rec['cancelado'] ?? 0);
    $es_cancelacion = $rec['tipo'] === 'cancelacion';
    $ico_cls = ($es_cancelado || $es_cancelacion) ? 'cancel' : 'pago';
    $ico_txt = ($es_cancelado || $es_cancelacion) ? '✕' : '$';
    $monto_col = ($es_cancelado || $es_cancelacion) ? 'var(--danger)' : 'var(--g)';
    $forma = match($rec['forma_pago'] ?? '') {
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
        'tarjeta' => 'Tarjeta',
        default => $rec['forma_pago'] ?? '',
    };
  ?>
  <a href="/ventas/<?= (int)$rec['venta_id'] ?>" class="recibo-row">
    <div class="recibo-ico <?= $ico_cls ?>"><?= $ico_txt ?></div>
    <div class="recibo-info">
      <div class="recibo-name"><?= e($rec['cliente_nombre'] ?? '—') ?></div>
      <div class="recibo-meta"><?= e($rec['numero']) ?> · <?= e(mb_substr($rec['venta_titulo'] ?? '',0,35)) ?><?= $forma ? ' · '.$forma : '' ?></div>
    </div>
    <div class="recibo-r">
      <div class="recibo-monto" style="color:<?= $monto_col ?>"><?= ($es_cancelado || $es_cancelacion) ? '-' : '+' ?><?= fmt_dash((float)$rec['monto']) ?></div>
      <?php if ($es_cancelado): ?>
      <div class="recibo-badge" style="background:var(--danger-bg);color:var(--danger)">Cancelado</div>
      <?php elseif ($es_cancelacion): ?>
      <div class="recibo-badge" style="background:var(--purple-bg);color:var(--purple)">Cancelación</div>
      <?php endif; ?>
    </div>
  </a>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- ══ ACTIVIDAD DEL MES ══ -->
<div class="slabel">Actividad del período</div>
<div class="monthly-grid">

  <div class="monthly-card">
    <div class="monthly-header">
      <div class="monthly-title"><?= ico('file', 16, '#2563eb') ?> Cotizaciones</div>
    </div>
    <div class="monthly-big"><?= (int)$act_cots['total'] ?></div>
    <div class="monthly-sub">cotizaciones generadas</div>
    <div class="monthly-divider"></div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Total cotizado</span>
      <span class="monthly-row-val"><?= fmt_full((float)$act_cots['monto_total']) ?></span>
    </div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Ticket promedio</span>
      <span class="monthly-row-val">
        <?= $act_cots['total'] > 0 ? fmt_full($act_cots['monto_total'] / $act_cots['total']) : '—' ?>
      </span>
    </div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Cerradas / convertidas</span>
      <span class="monthly-row-val">
        <?= (int)$act_cots['cerradas'] ?>
        <?php $base_act = max(1, (int)$act_cots['total']); ?>
        <?= $act_cots['total'] > 0 ? '(' . round($act_cots['cerradas']/$base_act*100, 1) . '%)' : '' ?>
      </span>
    </div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Rechazadas</span>
      <span class="monthly-row-val"><?= (int)$act_cots['rechazadas'] ?></span>
    </div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Pendientes de respuesta</span>
      <span class="monthly-row-val"><?= (int)$act_cots['pendientes'] ?></span>
    </div>
  </div>

  <div class="monthly-card">
    <div class="monthly-header">
      <div class="monthly-title"><?= ico('money', 16, '#16a34a') ?> Ventas</div>
    </div>
    <div class="monthly-big g"><?= (int)$act_ventas['total'] ?></div>
    <div class="monthly-sub">ventas generadas</div>
    <div class="monthly-divider"></div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Total en ventas</span>
      <span class="monthly-row-val"><?= fmt_full((float)$act_ventas['monto_total']) ?></span>
    </div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Ticket promedio</span>
      <span class="monthly-row-val">
        <?= $act_ventas['total'] > 0 ? fmt_full($act_ventas['monto_total'] / $act_ventas['total']) : '—' ?>
      </span>
    </div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Completamente pagadas</span>
      <span class="monthly-row-val"><?= (int)$act_ventas['pagadas'] ?></span>
    </div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Con saldo pendiente</span>
      <span class="monthly-row-val"><?= (int)$act_ventas['con_saldo'] ?></span>
    </div>
    <div class="monthly-row">
      <span class="monthly-row-lbl">Total cobrado</span>
      <span class="monthly-row-val"><?= fmt_full((float)$act_ventas['cobrado']) ?></span>
    </div>
  </div>

</div>


<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
