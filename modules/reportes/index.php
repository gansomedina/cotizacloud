<?php
// ============================================================
//  cotiza.cloud — modules/reportes/index.php
//  GET /reportes[?tab=financiero|asesores|cotizaciones|costos&periodo=...]
//  Solo admin; asesores ven solo sus propios datos
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_login();

// Permiso de módulo
if (!Auth::es_admin() && !Auth::puede('ver_reportes')) { redirect('/dashboard'); }

$empresa_id   = EMPRESA_ID;
$usuario      = Auth::usuario();
$es_admin     = Auth::es_admin();
$empresa_data = Auth::empresa();
$costos_modo  = $empresa_data['costos_modo'] ?? 'venta';

// ── Período ──────────────────────────────────────────────────
$periodo_val  = $_GET['periodo'] ?? 'mes_actual';
$periodos_ok  = ['mes_actual','mes_ant','30_dias','90_dias','anio','anio_ant','todo','rango'];
if (!in_array($periodo_val, $periodos_ok)) $periodo_val = 'mes_actual';

$now = new DateTimeImmutable('now', new DateTimeZone('America/Hermosillo'));
switch ($periodo_val) {
    case 'mes_ant':
        $f_ini = $now->modify('first day of last month')->format('Y-m-d');
        $f_fin = $now->modify('last day of last month')->format('Y-m-d');
        break;
    case '30_dias':
        $f_ini = $now->modify('-29 days')->format('Y-m-d');
        $f_fin = $now->format('Y-m-d');
        break;
    case '90_dias':
        $f_ini = $now->modify('-89 days')->format('Y-m-d');
        $f_fin = $now->format('Y-m-d');
        break;
    case 'anio':
        $f_ini = $now->format('Y') . '-01-01';
        $f_fin = $now->format('Y-m-d');
        break;
    case 'anio_ant':
        $y     = (int)$now->format('Y') - 1;
        $f_ini = "$y-01-01";
        $f_fin = "$y-12-31";
        break;
    case 'todo':
        $f_ini = '2020-01-01';
        $f_fin = $now->format('Y-m-d');
        break;
    case 'rango':
        $f_ini = $_GET['f_ini'] ?? $now->format('Y-m') . '-01';
        $f_fin = $_GET['f_fin'] ?? $now->format('Y-m-d');
        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_ini)) $f_ini = $now->format('Y-m') . '-01';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_fin)) $f_fin = $now->format('Y-m-d');
        break;
    default: // mes_actual
        $f_ini = $now->format('Y-m') . '-01';
        $f_fin = $now->format('Y-m-d');
}
$f_ini_dt = $f_ini . ' 00:00:00';
$f_fin_dt = $f_fin . ' 23:59:59';

// Filtro asesor si no es admin
$usr_filter     = $es_admin ? '' : "AND (v.usuario_id = {$usuario['id']} OR v.vendedor_id = {$usuario['id']})";
$usr_filter_c   = $es_admin ? '' : "AND (c.usuario_id = {$usuario['id']} OR c.vendedor_id = {$usuario['id']})";

$tab = in_array($_GET['tab'] ?? '', ['financiero','asesores','cotizaciones','costos','recibos','proveedores'])
    ? $_GET['tab'] : 'financiero';

// Proveedores solo Business
$plan_rep = trial_info($empresa_id);
$es_business_rep = $plan_rep['es_business'];
if ($tab === 'proveedores' && !$es_business_rep) $tab = 'financiero';

// ─────────────────────────────────────────────────────────────
//  TAB 1: FINANCIERO
// ─────────────────────────────────────────────────────────────

// KPIs principales
$kfi = DB::row(
    "SELECT
        COUNT(*)                                        AS num_ventas,
        COALESCE(SUM(v.total), 0)                       AS ingresos,
        COALESCE(SUM(v.pagado), 0)                      AS cobrado,
        COALESCE(SUM(v.saldo), 0)                       AS por_cobrar,
        COALESCE(SUM(COALESCE(gv.total_gasto,0)), 0)    AS total_costos
     FROM ventas v
     LEFT JOIN (
         SELECT venta_id, SUM(importe) AS total_gasto
         FROM gastos_venta WHERE empresa_id=?
         GROUP BY venta_id
     ) gv ON gv.venta_id = v.id
     WHERE v.empresa_id=? AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ?
       $usr_filter",
    [$empresa_id, $empresa_id, $f_ini_dt, $f_fin_dt]
);
$ingresos     = (float)$kfi['ingresos'];
$num_ventas   = (int)$kfi['num_ventas'];
$total_costos = (float)$kfi['total_costos'];

// En modo empresa, usar gastos generales para utilidad/margen del financiero
if ($costos_modo === 'empresa') {
    $total_costos_fin = (float)DB::val(
        "SELECT COALESCE(SUM(importe),0) FROM gastos_venta
         WHERE empresa_id=? AND venta_id IS NULL AND fecha BETWEEN ? AND ?",
        [$empresa_id, $f_ini, $f_fin]
    );
} elseif ($costos_modo === 'ambos') {
    $total_costos_fin = (float)DB::val(
        "SELECT COALESCE(SUM(importe),0) FROM gastos_venta
         WHERE empresa_id=? AND fecha BETWEEN ? AND ?",
        [$empresa_id, $f_ini, $f_fin]
    );
} else {
    $total_costos_fin = $total_costos;
}
$utilidad_bruta = $ingresos - $total_costos_fin;
$margen_pct   = $ingresos > 0 ? round($utilidad_bruta / $ingresos * 100, 1) : 0;

// Cotizaciones del período
$kfc = DB::row(
    "SELECT
        SUM(estado NOT IN ('borrador') AND suspendida = 0)            AS total,
        SUM(CASE WHEN estado='aceptada' OR estado='convertida' THEN 1 ELSE 0 END) AS aceptadas,
        SUM(CASE WHEN estado='rechazada' THEN 1 ELSE 0 END)          AS rechazadas,
        SUM(CASE WHEN estado IN ('enviada','vista') AND suspendida = 0 THEN 1 ELSE 0 END) AS activas,
        COALESCE(SUM(CASE WHEN estado NOT IN ('borrador') AND suspendida = 0 THEN total ELSE 0 END), 0) AS monto_total
     FROM cotizaciones c
     WHERE empresa_id=? AND created_at BETWEEN ? AND ? $usr_filter_c",
    [$empresa_id, $f_ini_dt, $f_fin_dt]
);
$tasa_conv = ($kfc['total'] ?? 0) > 0
    ? round($kfc['aceptadas'] / $kfc['total'] * 100, 1) : 0;

// Serie mensual (últimos 12 meses) para gráfica de barras
// Combina ventas reales + historial importado
$serie_meses = DB::query(
    "SELECT mes, SUM(monto) AS monto, SUM(num) AS num FROM (
        SELECT DATE_FORMAT(v.created_at, '%Y-%m') AS mes,
               COALESCE(SUM(v.total), 0) AS monto,
               COUNT(*) AS num
        FROM ventas v
        WHERE v.empresa_id=? AND v.estado != 'cancelada'
          AND v.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
          $usr_filter
        GROUP BY mes
        UNION ALL
        SELECT CONCAT(h.anio, '-', LPAD(h.mes, 2, '0')) AS mes,
               h.ventas_monto AS monto,
               h.ventas_cantidad AS num
        FROM historial_mensual h
        WHERE h.empresa_id=?
          AND STR_TO_DATE(CONCAT(h.anio, '-', LPAD(h.mes, 2, '0'), '-01'), '%Y-%m-%d') >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    ) combined GROUP BY mes ORDER BY mes ASC",
    [$empresa_id, $empresa_id]
);

// Serie costos mensual — filtrar según modo
$serie_costos_filter = '';
if ($costos_modo === 'venta')   $serie_costos_filter = 'AND venta_id IS NOT NULL';
if ($costos_modo === 'empresa') $serie_costos_filter = 'AND venta_id IS NULL';
$serie_costos = DB::query(
    "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
            COALESCE(SUM(importe), 0)   AS monto
     FROM gastos_venta
     WHERE empresa_id=? AND fecha >= DATE_SUB(NOW(), INTERVAL 12 MONTH) $serie_costos_filter
     GROUP BY mes ORDER BY mes ASC",
    [$empresa_id]
);
// Indexar costos por mes
$costos_por_mes = [];
foreach ($serie_costos as $sc) $costos_por_mes[$sc['mes']] = (float)$sc['monto'];

// ─────────────────────────────────────────────────────────────
//  TAB 2: ASESORES (solo admin)
// ─────────────────────────────────────────────────────────────
$por_asesor = [];
if ($es_admin) {
    $por_asesor = DB::query(
        "SELECT u.nombre AS asesor, u.id AS usr_id,
                COALESCE(sv.num_ventas, 0)  AS num_ventas,
                COALESCE(sv.ingresos, 0)    AS ingresos,
                COALESCE(sv.costos, 0)      AS costos,
                COALESCE(sc.num_cots, 0)    AS num_cots,
                COALESCE(sc.aceptadas, 0)   AS aceptadas
         FROM usuarios u
         LEFT JOIN (
             SELECT COALESCE(v.vendedor_id, v.usuario_id) AS vid,
                    COUNT(*)              AS num_ventas,
                    SUM(v.total)          AS ingresos,
                    COALESCE(SUM(gv.total_gasto), 0) AS costos
             FROM ventas v
             LEFT JOIN (
                 SELECT venta_id, SUM(importe) AS total_gasto
                 FROM gastos_venta WHERE empresa_id=?
                 GROUP BY venta_id
             ) gv ON gv.venta_id = v.id
             WHERE v.empresa_id=? AND v.estado != 'cancelada'
               AND v.created_at BETWEEN ? AND ?
             GROUP BY vid
         ) sv ON sv.vid = u.id
         LEFT JOIN (
             SELECT COALESCE(c.vendedor_id, c.usuario_id) AS vid,
                    COUNT(*)              AS num_cots,
                    SUM(CASE WHEN c.estado IN ('aceptada','convertida') THEN 1 ELSE 0 END) AS aceptadas
             FROM cotizaciones c
             WHERE c.empresa_id=? AND c.created_at BETWEEN ? AND ?
             GROUP BY vid
         ) sc ON sc.vid = u.id
         WHERE u.empresa_id=? AND u.activo=1
         ORDER BY ingresos DESC",
        [$empresa_id, $empresa_id, $f_ini_dt, $f_fin_dt,
         $empresa_id, $f_ini_dt, $f_fin_dt,
         $empresa_id]
    );
}

// ─────────────────────────────────────────────────────────────
//  TAB 3: COTIZACIONES
// ─────────────────────────────────────────────────────────────
$lista_cots = DB::query(
    "SELECT c.id, c.numero, c.titulo, c.total, c.estado, c.suspendida,
            c.created_at, c.aceptada_at, c.rechazada_at, c.enviada_at,
            c.valida_hasta, c.visitas,
            cl.nombre AS cliente_nombre,
            u.nombre  AS asesor_nombre
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     LEFT JOIN usuarios u  ON u.id  = c.usuario_id
     WHERE c.empresa_id=? AND c.created_at BETWEEN ? AND ? $usr_filter_c
     ORDER BY c.created_at DESC
     LIMIT 200",
    [$empresa_id, $f_ini_dt, $f_fin_dt]
);

// ─────────────────────────────────────────────────────────────
//  TAB 4: COSTOS / MÁRGENES
// ─────────────────────────────────────────────────────────────
// Costos por categoría — filtrar según modo
$cat_venta_filter = '';
if ($costos_modo === 'venta')   $cat_venta_filter = 'AND gv.venta_id IS NOT NULL';
if ($costos_modo === 'empresa') $cat_venta_filter = 'AND gv.venta_id IS NULL';

$costos_por_cat = DB::query(
    "SELECT cc.nombre AS categoria, cc.color,
            COUNT(gv.id)          AS num_gastos,
            COALESCE(SUM(gv.importe), 0) AS total
     FROM gastos_venta gv
     LEFT JOIN categorias_costos cc ON cc.id = gv.categoria_id
     WHERE gv.empresa_id=? AND gv.fecha BETWEEN ? AND ? $cat_venta_filter
     GROUP BY gv.categoria_id, cc.nombre, cc.color
     ORDER BY total DESC",
    [$empresa_id, $f_ini, $f_fin]
);

// Ventas con margen — solo en modo venta o ambos
$ventas_con_margen = [];
if (in_array($costos_modo, ['venta', 'ambos'])) {
    $ventas_con_margen = DB::query(
        "SELECT v.id, v.numero, v.titulo, v.total, v.created_at,
                cl.nombre AS cliente,
                u.nombre  AS asesor,
                COALESCE(SUM(gv.importe), 0) AS costos,
                v.total - COALESCE(SUM(gv.importe), 0) AS utilidad
         FROM ventas v
         LEFT JOIN clientes cl       ON cl.id = v.cliente_id
         LEFT JOIN usuarios u        ON u.id  = v.usuario_id
         LEFT JOIN gastos_venta gv   ON gv.venta_id = v.id
         WHERE v.empresa_id=? AND v.estado != 'cancelada'
           AND v.created_at BETWEEN ? AND ? $usr_filter
         GROUP BY v.id, v.numero, v.titulo, v.total, v.created_at, cl.nombre, u.nombre
         ORDER BY v.created_at DESC
         LIMIT 200",
        [$empresa_id, $f_ini_dt, $f_fin_dt]
    );
}

// Gastos generales — solo en modo empresa o ambos
$gastos_generales_rep = [];
$total_gastos_gen = 0;
if (in_array($costos_modo, ['empresa', 'ambos'])) {
    $gastos_generales_rep = DB::query(
        "SELECT gv.id, gv.concepto, gv.importe, gv.fecha, gv.nota,
                cc.nombre AS categoria, cc.color,
                p.nombre AS proveedor
         FROM gastos_venta gv
         LEFT JOIN categorias_costos cc ON cc.id = gv.categoria_id
         LEFT JOIN proveedores p ON p.id = gv.proveedor_id
         WHERE gv.empresa_id=? AND gv.venta_id IS NULL
           AND gv.fecha BETWEEN ? AND ?
         ORDER BY gv.fecha DESC
         LIMIT 200",
        [$empresa_id, $f_ini, $f_fin]
    );
    $total_gastos_gen = array_sum(array_column($gastos_generales_rep, 'importe'));
}

// ─────────────────────────────────────────────────────────────
//  TAB 6: PROVEEDORES (Business)
// ─────────────────────────────────────────────────────────────
$prov_top = [];
$prov_mensual = [];
$prov_total_periodo = 0;
$prov_lista_rep = [];

if ($es_business_rep) {
    // Top proveedores por monto total en el período
    $prov_top = DB::query(
        "SELECT p.id, p.nombre,
                COUNT(gv.id) AS num_gastos,
                COALESCE(SUM(gv.importe), 0) AS total
         FROM proveedores p
         INNER JOIN gastos_venta gv ON gv.proveedor_id = p.id AND gv.empresa_id = p.empresa_id
         WHERE p.empresa_id = ? AND gv.fecha BETWEEN ? AND ?
         GROUP BY p.id, p.nombre
         ORDER BY total DESC",
        [$empresa_id, $f_ini, $f_fin]
    );
    $prov_total_periodo = array_sum(array_column($prov_top, 'total'));

    // Gastos por proveedor por mes (últimos 6 meses) para gráfica
    $prov_mensual = DB::query(
        "SELECT p.nombre AS proveedor,
                DATE_FORMAT(gv.fecha, '%Y-%m') AS mes,
                COALESCE(SUM(gv.importe), 0) AS total
         FROM gastos_venta gv
         INNER JOIN proveedores p ON p.id = gv.proveedor_id
         WHERE gv.empresa_id = ? AND gv.proveedor_id IS NOT NULL
           AND gv.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY p.nombre, mes
         ORDER BY mes ASC, total DESC",
        [$empresa_id]
    );

    // Lista detallada de gastos con proveedor en el período
    $prov_lista_rep = DB::query(
        "SELECT gv.id, gv.concepto, gv.importe, gv.fecha, gv.nota,
                p.nombre AS proveedor,
                cc.nombre AS categoria, cc.color,
                v.numero AS venta_numero
         FROM gastos_venta gv
         INNER JOIN proveedores p ON p.id = gv.proveedor_id
         LEFT JOIN categorias_costos cc ON cc.id = gv.categoria_id
         LEFT JOIN ventas v ON v.id = gv.venta_id
         WHERE gv.empresa_id = ? AND gv.fecha BETWEEN ? AND ?
         ORDER BY gv.fecha DESC
         LIMIT 300",
        [$empresa_id, $f_ini, $f_fin]
    );
}

// ─────────────────────────────────────────────────────────────
//  TAB 5: RECIBOS
// ─────────────────────────────────────────────────────────────
$usr_filter_r = $es_admin ? '' : "AND r.usuario_id = {$usuario['id']}";
$lista_recibos = DB::query(
    "SELECT r.id, r.numero, r.monto, r.tipo, r.cancelado, r.fecha,
            r.forma_pago,
            r.concepto, r.cancelado_at,
            v.numero AS venta_numero, v.titulo AS venta_titulo, v.id AS venta_id,
            cl.nombre AS cliente_nombre,
            COALESCE(ur.nombre, uv.nombre) AS asesor_nombre
     FROM recibos r
     LEFT JOIN ventas v ON v.id = r.venta_id
     LEFT JOIN clientes cl ON cl.id = v.cliente_id
     LEFT JOIN usuarios ur ON ur.id = r.usuario_id
     LEFT JOIN usuarios uv ON uv.id = v.usuario_id
     WHERE r.empresa_id = ? AND r.fecha BETWEEN ? AND ? $usr_filter_r
     ORDER BY r.created_at DESC
     LIMIT 300",
    [$empresa_id, $f_ini, $f_fin]
);
$total_recibos_pagados = 0;
$total_recibos_cancelados = 0;
$num_cancelados = 0;
foreach ($lista_recibos as $lr) {
    if ((int)($lr['cancelado'] ?? 0)) {
        $num_cancelados++;
    } else {
        $total_recibos_pagados += (float)$lr['monto'];
    }
    if ($lr['tipo'] === 'cancelacion') {
        $total_recibos_cancelados += (float)$lr['monto'];
    }
}

// ── Helpers de formato ────────────────────────────────────────
function rp(float $n): string { return '$' . number_format($n, 0, '.', ','); }
function rpp(float $n): string { return number_format($n, 1) . '%'; }
function delta(float $a, float $b): string {
    if ($b == 0) return '';
    $d = round(($a - $b) / abs($b) * 100, 1);
    $sign = $d >= 0 ? '+' : '';
    $col  = $d >= 0 ? 'var(--g)' : 'var(--danger)';
    return "<span style='color:$col;font:600 11px var(--num)'>$sign$d%</span>";
}
function estado_badge(string $e): string {
    return match($e) {
        'borrador'   => "<span class='badge badge-gray'>Borrador</span>",
        'enviada'    => "<span class='badge badge-blue'>Enviada</span>",
        'vista'      => "<span class='badge badge-blue'>Vista</span>",
        'aceptada'   => "<span class='badge badge-green'>Aceptada</span>",
        'convertida' => "<span class='badge badge-green'>Convertida</span>",
        'rechazada'  => "<span class='badge badge-red'>Rechazada</span>",
        'vencida'    => "<span class='badge badge-amber'>Vencida</span>",
        default      => "<span class='badge badge-gray'>$e</span>",
    };
}
function margen_bar(float $total, float $costos): string {
    if ($total <= 0) return '<span style="color:var(--t3);font-size:12px">—</span>';
    $util = $total - $costos;
    $pct  = round($util / $total * 100, 1);
    $col  = $pct >= 30 ? 'var(--g)' : ($pct >= 15 ? '#b45309' : 'var(--danger)');
    $w    = max(0, min(100, $pct));
    return "<div style='display:flex;align-items:center;gap:8px'>
      <div style='flex:1;height:5px;border-radius:3px;background:var(--border);overflow:hidden'>
        <div style='height:100%;width:{$w}%;background:{$col};border-radius:3px'></div>
      </div>
      <span style='font:700 12px var(--num);color:{$col};width:44px;text-align:right'>{$pct}%</span>
    </div>";
}

$page_title = 'Reportes';
ob_start();
?>
<style>
/* ─── Tabs ───────────────────────────────────────────────── */
.rep-tabs-wrap{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:54px;z-index:90;overflow-x:auto;scrollbar-width:none;margin:-16px 0 24px}
.rep-tabs-wrap::-webkit-scrollbar{display:none}
.rep-tabs{display:flex;max-width:var(--max);margin:0 auto}
.rep-tab{padding:13px 18px;font:600 13px var(--body);color:var(--t3);cursor:pointer;border-bottom:2.5px solid transparent;white-space:nowrap;transition:all .15s;background:none;border-top:none;border-left:none;border-right:none}
.rep-tab:hover{color:var(--t2)}
.rep-tab.on{color:var(--g);border-bottom-color:var(--g)}
.tab-panel{display:none}.tab-panel.on{display:block}

/* ─── Período selector ───────────────────────────────────── */
.periodo-wrap{display:flex;align-items:center;gap:10px;margin-bottom:24px;flex-wrap:wrap}
.periodo-lbl{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3)}
.periodo-select{background:var(--white);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:8px 14px;font:600 13px var(--body);color:var(--text);outline:none;cursor:pointer;box-shadow:var(--sh)}
.periodo-select:focus{border-color:var(--g)}
.periodo-date{background:var(--white);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:6px 10px;font:600 13px var(--num);color:var(--text);outline:none;box-shadow:var(--sh)}
.periodo-date:focus{border-color:var(--g)}
.periodo-apply{padding:6px 14px;border-radius:var(--r-sm);border:none;background:var(--g);color:#fff;font:700 12px var(--body);cursor:pointer}
.periodo-apply:hover{opacity:.9}
.export-btn{margin-left:auto;padding:8px 16px;border-radius:var(--r-sm);border:1.5px solid var(--border);background:var(--white);font:600 12px var(--body);color:var(--t2);cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .12s;box-shadow:var(--sh)}
.export-btn:hover{border-color:var(--g);color:var(--g)}

/* ─── KPI Cards ──────────────────────────────────────────── */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.kpi-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;box-shadow:var(--sh)}
.kpi-label{font:700 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);margin-bottom:6px}
.kpi-val{font:800 26px var(--num);letter-spacing:-.02em;color:var(--text);line-height:1}
.kpi-val.green{color:var(--g)}
.kpi-val.amber{color:#b45309}
.kpi-val.danger{color:var(--danger)}
.kpi-sub{font:400 12px var(--body);color:var(--t3);margin-top:5px;display:flex;align-items:center;gap:6px}

/* ─── Gráfica barras ─────────────────────────────────────── */
.chart-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:18px;box-shadow:var(--sh);margin-bottom:20px}
.chart-title{font:700 13px var(--body);margin-bottom:16px;display:flex;align-items:center;justify-content:space-between}
.chart-legend{display:flex;gap:14px;font:500 11px var(--body);color:var(--t3)}
.chart-legend span{display:flex;align-items:center;gap:5px}
.chart-legend i{width:10px;height:10px;border-radius:3px;display:inline-block}
.bar-chart{display:flex;align-items:flex-end;gap:4px;height:120px;padding:0 2px}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;min-width:0}
.bar-wrap{width:100%;display:flex;gap:2px;align-items:flex-end;height:100px}
.bar{flex:1;border-radius:4px 4px 0 0;min-height:2px;transition:opacity .15s;cursor:default}
.bar:hover{opacity:.8}
.bar-lbl{font:500 10px var(--num);color:var(--t3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;text-align:center}

/* ─── Sección ────────────────────────────────────────────── */
.sec-lbl{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t2);margin-bottom:10px;display:flex;align-items:center;gap:10px}
.sec-lbl::after{content:'';flex:1;height:1.5px;background:var(--border)}

/* ─── Embudo 2 cols ──────────────────────────────────────── */
.two-cols{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.stat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;box-shadow:var(--sh)}
.stat-row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)}
.stat-row:last-child{border-bottom:none}
.stat-lbl{font:500 13px var(--body);color:var(--t2)}
.stat-val{font:700 14px var(--num)}

/* ─── Tabla ──────────────────────────────────────────────── */
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:20px}
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th{font:700 10px var(--body);letter-spacing:.06em;text-transform:uppercase;color:var(--t3);padding:9px 14px;border-bottom:2px solid var(--border);background:var(--bg);white-space:nowrap;text-align:left}
.tbl th.r{text-align:right}
.tbl td{padding:10px 14px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:#fafaf8}
.tbl-num{font:600 13px var(--num);text-align:right}
.tbl-sub{font:400 11px var(--body);color:var(--t3);margin-top:2px}

/* ─── Badges ─────────────────────────────────────────────── */
.badge{padding:3px 9px;border-radius:20px;font:700 10px var(--body);letter-spacing:.04em;white-space:nowrap}
.badge-green{background:var(--g-bg);color:var(--g)}
.badge-blue{background:var(--blue-bg);color:var(--blue)}
.badge-red{background:var(--danger-bg);color:var(--danger)}
.badge-amber{background:var(--amb-bg);color:var(--amb)}
.badge-gray{background:var(--bg);color:var(--t3);border:1px solid var(--border)}

/* ─── Asesor card ────────────────────────────────────────── */
.asesor-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;margin-bottom:20px}
.asesor-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;box-shadow:var(--sh)}
.asesor-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.asesor-av{width:38px;height:38px;border-radius:10px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 14px var(--body);color:#fff;flex-shrink:0}
.asesor-name{font:700 14px var(--body)}
.asesor-stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
.asesor-stat{text-align:center;padding:8px 6px;background:var(--bg);border-radius:var(--r-sm)}
.asesor-stat-val{font:800 16px var(--num);color:var(--text)}
.asesor-stat-lbl{font:500 10px var(--body);color:var(--t3);margin-top:2px}

/* ─── Donut costos ────────────────────────────────────────── */
.costos-layout{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.donut-wrap{display:flex;align-items:center;gap:20px;padding:16px}
.donut-legend{display:flex;flex-direction:column;gap:8px;flex:1}
.donut-item{display:flex;align-items:center;gap:8px}
.donut-dot{width:10px;height:10px;border-radius:3px;flex-shrink:0}
.donut-lbl{font:500 12px var(--body);flex:1}
.donut-pct{font:700 12px var(--num);color:var(--t2)}
.donut-amt{font:400 11px var(--num);color:var(--t3)}

/* ─── Vacío ──────────────────────────────────────────────── */
.empty{text-align:center;padding:40px 20px;color:var(--t3);font:400 13px var(--body)}

/* ─── Responsive ─────────────────────────────────────────── */
@media(max-width:640px){
  .kpi-grid{grid-template-columns:1fr 1fr}
  .two-cols{grid-template-columns:1fr}
  .costos-layout{grid-template-columns:1fr}
  .asesor-grid{grid-template-columns:1fr}
}
@media(max-width:400px){
  .kpi-grid{grid-template-columns:1fr}
}
</style>

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;flex-wrap:wrap;gap:10px">
  <h1 style="font:800 22px var(--body);letter-spacing:-.02em">Reportes</h1>
</div>

<!-- Tabs -->
<div class="rep-tabs-wrap">
  <div class="rep-tabs">
    <button class="rep-tab <?= $tab==='financiero'   ?'on':'' ?>" onclick="repTab('financiero',this)">Financiero</button>
    <?php if ($es_admin): ?>
    <button class="rep-tab <?= $tab==='asesores'     ?'on':'' ?>" onclick="repTab('asesores',this)">Por asesor</button>
    <?php endif; ?>
    <button class="rep-tab <?= $tab==='cotizaciones' ?'on':'' ?>" onclick="repTab('cotizaciones',this)">Cotizaciones</button>
    <button class="rep-tab <?= $tab==='recibos'       ?'on':'' ?>" onclick="repTab('recibos',this)">Recibos</button>
    <button class="rep-tab <?= $tab==='costos'       ?'on':'' ?>" onclick="repTab('costos',this)">Costos y márgenes</button>
    <?php if ($es_business_rep): ?>
    <button class="rep-tab <?= $tab==='proveedores'  ?'on':'' ?>" onclick="repTab('proveedores',this)">Proveedores</button>
    <?php endif; ?>
  </div>
</div>

<!-- Selector de período (común a todos los tabs) -->
<form method="get" id="fPeriodo">
  <input type="hidden" name="tab" id="hTab" value="<?= e($tab) ?>">
  <div class="periodo-wrap">
    <span class="periodo-lbl">Período</span>
    <select name="periodo" class="periodo-select" onchange="toggleRango(this.value)">
      <option value="mes_actual" <?= $periodo_val==='mes_actual'?'selected':'' ?>>Este mes</option>
      <option value="mes_ant"    <?= $periodo_val==='mes_ant'   ?'selected':'' ?>>Mes anterior</option>
      <option value="30_dias"    <?= $periodo_val==='30_dias'   ?'selected':'' ?>>Últimos 30 días</option>
      <option value="90_dias"    <?= $periodo_val==='90_dias'   ?'selected':'' ?>>Últimos 90 días</option>
      <option value="anio"       <?= $periodo_val==='anio'      ?'selected':'' ?>>Este año</option>
      <option value="anio_ant"   <?= $periodo_val==='anio_ant'  ?'selected':'' ?>>Año anterior</option>
      <option value="todo"       <?= $periodo_val==='todo'      ?'selected':'' ?>>Todo el historial</option>
      <option value="rango"      <?= $periodo_val==='rango'     ?'selected':'' ?>>Rango de fechas</option>
    </select>
    <span id="rangoFechas" style="display:<?= $periodo_val==='rango' ? 'flex' : 'none' ?>;align-items:center;gap:6px">
      <input type="date" name="f_ini" value="<?= e($f_ini) ?>" class="periodo-date">
      <span style="color:var(--t3)">—</span>
      <input type="date" name="f_fin" value="<?= e($f_fin) ?>" class="periodo-date">
      <button type="submit" class="periodo-apply">Aplicar</button>
    </span>
    <span id="rangoLabel" style="font:400 12px var(--body);color:var(--t3);<?= $periodo_val==='rango' ? 'display:none' : '' ?>">
      <?= date('d M Y', strtotime($f_ini)) ?> — <?= date('d M Y', strtotime($f_fin)) ?>
    </span>
    <button type="button" class="export-btn" onclick="exportarCSV()">
      ↓ Exportar CSV
    </button>
  </div>
</form>


<!-- ══ TAB: FINANCIERO ═══════════════════════════════════════ -->
<div class="tab-panel <?= $tab==='financiero'?'on':'' ?>" id="panel-financiero">

  <!-- KPIs -->
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Ingresos del período</div>
      <div class="kpi-val green"><?= rp($ingresos) ?></div>
      <div class="kpi-sub"><?= $kfi['num_ventas'] ?> venta<?= $kfi['num_ventas']!=1?'s':'' ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Cobrado</div>
      <div class="kpi-val"><?= rp((float)$kfi['cobrado']) ?></div>
      <div class="kpi-sub" style="color:<?= $kfi['por_cobrar']>0?'#b45309':'var(--t3)' ?>">
        <?= $kfi['por_cobrar'] > 0 ? rp((float)$kfi['por_cobrar']) . ' pendiente' : 'Todo cobrado' ?>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Utilidad bruta</div>
      <div class="kpi-val <?= $utilidad_bruta>=0?'green':'danger' ?>"><?= rp($utilidad_bruta) ?></div>
      <div class="kpi-sub">Costos: <?= rp($total_costos_fin) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Margen bruto</div>
      <div class="kpi-val <?= $margen_pct>=30?'green':($margen_pct>=15?'amber':'danger') ?>">
        <?= rpp($margen_pct) ?>
      </div>
      <div class="kpi-sub">Tasa de cierre: <?= rpp($tasa_conv) ?></div>
    </div>
  </div>

  <!-- Gráfica ingresos vs costos últimos 12 meses -->
  <div class="chart-card">
    <div class="chart-title">
      <span>Ingresos y costos — últimos 12 meses</span>
      <div class="chart-legend">
        <span><i style="background:var(--g)"></i>Ingresos</span>
        <span><i style="background:#f59e0b"></i>Costos</span>
      </div>
    </div>
    <?php
    // Construir serie completa de 12 meses
    $meses_labels = [];
    $meses_ing    = [];
    $meses_cos    = [];
    $max_val = 1;
    for ($i = 11; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $meses_labels[] = date('M', strtotime($m . '-01'));
        $ing = 0;
        foreach ($serie_meses as $sm) { if ($sm['mes'] === $m) { $ing = (float)$sm['monto']; break; } }
        $cos = $costos_por_mes[$m] ?? 0;
        $meses_ing[] = $ing;
        $meses_cos[] = $cos;
        if ($ing > $max_val) $max_val = $ing;
        if ($cos > $max_val) $max_val = $cos;
    }
    ?>
    <div class="bar-chart">
      <?php for ($i = 0; $i < 12; $i++):
        $h_ing = round($meses_ing[$i] / $max_val * 96);
        $h_cos = round($meses_cos[$i] / $max_val * 96);
        $tip_i = rp($meses_ing[$i]);
        $tip_c = rp($meses_cos[$i]);
      ?>
      <div class="bar-col">
        <div class="bar-wrap">
          <div class="bar" style="height:<?= max(2,$h_ing) ?>px;background:var(--g)" title="Ingresos: <?= $tip_i ?>"></div>
          <div class="bar" style="height:<?= max(2,$h_cos) ?>px;background:#f59e0b" title="Costos: <?= $tip_c ?>"></div>
        </div>
        <div class="bar-lbl"><?= $meses_labels[$i] ?></div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Resumen cotizaciones + métricas -->
  <div class="two-cols">
    <div class="stat-card">
      <div class="sec-lbl" style="margin-bottom:12px">Cotizaciones del período</div>
      <div class="stat-row">
        <span class="stat-lbl">Total generadas</span>
        <span class="stat-val"><?= (int)$kfc['total'] ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Aceptadas / Convertidas</span>
        <span class="stat-val" style="color:var(--g)"><?= (int)$kfc['aceptadas'] ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Rechazadas</span>
        <span class="stat-val" style="color:var(--danger)"><?= (int)$kfc['rechazadas'] ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Activas (enviada / vista)</span>
        <span class="stat-val" style="color:var(--blue)"><?= (int)$kfc['activas'] ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Tasa de conversión</span>
        <span class="stat-val" style="color:<?= $tasa_conv>=40?'var(--g)':($tasa_conv>=20?'#b45309':'var(--danger)') ?>"><?= rpp($tasa_conv) ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Monto total cotizado</span>
        <span class="stat-val"><?= rp((float)$kfc['monto_total']) ?></span>
      </div>
    </div>
    <div class="stat-card">
      <div class="sec-lbl" style="margin-bottom:12px">Finanzas del período</div>
      <div class="stat-row">
        <span class="stat-lbl">Ingresos brutos</span>
        <span class="stat-val" style="color:var(--g)"><?= rp($ingresos) ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Costos registrados</span>
        <span class="stat-val" style="color:#b45309"><?= rp($total_costos_fin) ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Utilidad bruta</span>
        <span class="stat-val" style="color:<?= $utilidad_bruta>=0?'var(--g)':'var(--danger)' ?>"><?= rp($utilidad_bruta) ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Margen bruto</span>
        <span class="stat-val"><?= rpp($margen_pct) ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Cobrado</span>
        <span class="stat-val"><?= rp((float)$kfi['cobrado']) ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-lbl">Por cobrar (saldo)</span>
        <span class="stat-val" style="color:<?= $kfi['por_cobrar']>0?'#b45309':'var(--t3)' ?>"><?= rp((float)$kfi['por_cobrar']) ?></span>
      </div>
    </div>
  </div>

  <?php
  // Historial importado
  $hist_rep = DB::query(
      "SELECT * FROM historial_mensual WHERE empresa_id = ? ORDER BY anio DESC, mes DESC LIMIT 24",
      [$empresa_id]
  );
  if ($hist_rep):
    $mn = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $hist_total_ventas_monto = array_sum(array_column($hist_rep, 'ventas_monto'));
    $hist_total_ventas_cant  = array_sum(array_column($hist_rep, 'ventas_cantidad'));
    $hist_total_cots_cant    = array_sum(array_column($hist_rep, 'cotizaciones_cantidad'));
    $hist_tasa = $hist_total_cots_cant > 0 ? round($hist_total_ventas_cant / $hist_total_cots_cant * 100, 1) : 0;
  ?>
  <div class="sec-lbl" style="margin:28px 0 12px">Historial importado</div>
  <div class="kpi-grid" style="margin-bottom:16px">
    <div class="kpi-card">
      <div class="kpi-label">Ventas históricas</div>
      <div class="kpi-val green"><?= rp($hist_total_ventas_monto) ?></div>
      <div class="kpi-sub"><?= number_format($hist_total_ventas_cant) ?> ventas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Cotizaciones históricas</div>
      <div class="kpi-val"><?= number_format($hist_total_cots_cant) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Tasa cierre histórica</div>
      <div class="kpi-val <?= $hist_tasa>=15?'green':($hist_tasa>=8?'amber':'danger') ?>"><?= rpp($hist_tasa) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <table class="tbl" style="font-size:12px">
      <thead>
        <tr>
          <th>Periodo</th>
          <th style="text-align:right">Cotizaciones</th>
          <th style="text-align:right">Ventas</th>
          <th style="text-align:right">Monto Ventas</th>
          <th style="text-align:right">Tasa</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($hist_rep as $hr): ?>
        <tr>
          <td style="font:600 12px var(--body)"><?= $mn[(int)$hr['mes']] ?> <?= $hr['anio'] ?></td>
          <td style="text-align:right;font:500 12px var(--num)"><?= number_format($hr['cotizaciones_cantidad']) ?></td>
          <td style="text-align:right;font:600 12px var(--num)"><?= number_format($hr['ventas_cantidad']) ?></td>
          <td style="text-align:right;font:600 12px var(--num);color:var(--g)"><?= rp((float)$hr['ventas_monto']) ?></td>
          <td style="text-align:right;font:700 12px var(--num);color:<?= $hr['tasa_cierre']>=15?'var(--g)':($hr['tasa_cierre']>=8?'#b45309':'var(--danger)') ?>"><?= number_format($hr['tasa_cierre'],1) ?>%</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div><!-- /panel-financiero -->


<!-- ══ TAB: ASESORES ═════════════════════════════════════════ -->
<?php if ($es_admin): ?>
<div class="tab-panel <?= $tab==='asesores'?'on':'' ?>" id="panel-asesores">

  <?php if (empty($por_asesor)): ?>
    <div class="empty">Sin datos para el período seleccionado</div>
  <?php else: ?>

  <div class="asesor-grid">
    <?php foreach ($por_asesor as $a):
      $ini = '';
      $ps = array_filter(explode(' ', $a['asesor']));
      foreach (array_slice($ps,0,2) as $w) $ini .= strtoupper($w[0]);
      $a_ing     = (float)$a['ingresos'];
      $a_cos     = (float)$a['costos'];
      $a_util    = $a_ing - $a_cos;
      $a_margen  = $a_ing > 0 ? round($a_util / $a_ing * 100, 1) : 0;
      $a_tc      = $a['num_cots'] > 0 ? round($a['aceptadas'] / $a['num_cots'] * 100, 1) : 0;
    ?>
    <div class="asesor-card">
      <div class="asesor-head">
        <div class="asesor-av"><?= e($ini) ?></div>
        <div>
          <div class="asesor-name"><?= e($a['asesor']) ?></div>
          <div style="font:400 12px var(--body);color:var(--t3);margin-top:2px"><?= (int)$a['num_ventas'] ?> venta<?= $a['num_ventas']!=1?'s':'' ?> · <?= (int)$a['num_cots'] ?> cot<?= $a['num_cots']!=1?'s':'' ?></div>
        </div>
      </div>
      <div class="asesor-stats">
        <div class="asesor-stat">
          <div class="asesor-stat-val" style="font-size:14px;color:var(--g)"><?= rp($a_ing) ?></div>
          <div class="asesor-stat-lbl">Ingresos</div>
        </div>
        <div class="asesor-stat">
          <div class="asesor-stat-val" style="font-size:14px"><?= rpp($a_margen) ?></div>
          <div class="asesor-stat-lbl">Margen</div>
        </div>
        <div class="asesor-stat">
          <div class="asesor-stat-val" style="font-size:14px"><?= rpp($a_tc) ?></div>
          <div class="asesor-stat-lbl">Conversión</div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Tabla comparativa -->
  <div class="sec-lbl">Comparativo detallado</div>
  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl" id="tbl-asesores">
        <thead>
          <tr>
            <th>Asesor</th>
            <th class="r">Ventas</th>
            <th class="r">Ingresos</th>
            <th class="r">Costos</th>
            <th class="r">Utilidad</th>
            <th class="r">Margen</th>
            <th class="r">Cotizaciones</th>
            <th class="r">Conversión</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($por_asesor as $a):
            $a_ing  = (float)$a['ingresos'];
            $a_cos  = (float)$a['costos'];
            $a_util = $a_ing - $a_cos;
            $a_mg   = $a_ing > 0 ? round($a_util/$a_ing*100,1) : 0;
            $a_tc   = $a['num_cots'] > 0 ? round($a['aceptadas']/$a['num_cots']*100,1) : 0;
          ?>
          <tr>
            <td><?= e($a['asesor']) ?></td>
            <td class="tbl-num"><?= (int)$a['num_ventas'] ?></td>
            <td class="tbl-num" style="color:var(--g)"><?= rp($a_ing) ?></td>
            <td class="tbl-num" style="color:#b45309"><?= rp($a_cos) ?></td>
            <td class="tbl-num" style="color:<?= $a_util>=0?'var(--g)':'var(--danger)' ?>"><?= rp($a_util) ?></td>
            <td class="tbl-num"><?= rpp($a_mg) ?></td>
            <td class="tbl-num"><?= (int)$a['num_cots'] ?></td>
            <td class="tbl-num"><?= rpp($a_tc) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php endif; ?>
</div><!-- /panel-asesores -->
<?php endif; ?>


<!-- ══ TAB: COTIZACIONES ═════════════════════════════════════ -->
<div class="tab-panel <?= $tab==='cotizaciones'?'on':'' ?>" id="panel-cotizaciones">

  <?php if (empty($lista_cots)): ?>
    <div class="empty card" style="padding:40px">Sin cotizaciones en el período seleccionado</div>
  <?php else: ?>

  <!-- Resumen rápido por estado -->
  <div class="kpi-grid" style="grid-template-columns:repeat(auto-fit,minmax(100px,1fr))">
    <?php
    $est_counts = ['enviada'=>0,'suspendida'=>0,'vista'=>0,'aceptada'=>0,'rechazada'=>0,'vencida'=>0];
    foreach ($lista_cots as $lc) {
        if (!empty($lc['suspendida'])) {
            $est_counts['suspendida']++;
            continue;
        }
        $e = $lc['estado'];
        if ($e === 'convertida') $e = 'aceptada';
        if (isset($est_counts[$e])) $est_counts[$e]++;
    }
    $est_info = [
        'enviada'    => ['lbl'=>'Sin abrir',    'col'=>'var(--blue)'],
        'suspendida' => ['lbl'=>'Suspendidas',  'col'=>'#94a3b8'],
        'vista'      => ['lbl'=>'Abiertas',     'col'=>'var(--purple)'],
        'aceptada'   => ['lbl'=>'Aceptadas',    'col'=>'var(--g)'],
        'rechazada'  => ['lbl'=>'Rechazadas',   'col'=>'var(--danger)'],
        'vencida'    => ['lbl'=>'Vencidas',     'col'=>'var(--amb)'],
    ];
    foreach ($est_info as $ek => $ev):
    ?>
    <div class="kpi-card" style="padding:12px 14px">
      <div class="kpi-label"><?= $ev['lbl'] ?></div>
      <div class="kpi-val" style="font-size:22px;color:<?= $ev['col'] ?>"><?= $est_counts[$ek] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="sec-lbl">Detalle de cotizaciones</div>
  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl" id="tbl-cotizaciones">
        <thead>
          <tr>
            <th>Número</th>
            <th>Título / Cliente</th>
            <?php if ($es_admin): ?><th>Asesor</th><?php endif; ?>
            <th class="r">Total</th>
            <th>Estado</th>
            <th class="r">Visitas</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista_cots as $lc): ?>
          <tr>
            <td>
              <a href="/cotizaciones/<?= (int)$lc['id'] ?>"
                 style="font:700 12px var(--num);color:var(--g);text-decoration:none">
                <?= e($lc['numero']) ?>
              </a>
            </td>
            <td>
              <div style="font:600 13px var(--body)"><?= e(mb_substr($lc['titulo'],0,50)) ?></div>
              <?php if ($lc['cliente_nombre']): ?>
              <div class="tbl-sub"><?= e($lc['cliente_nombre']) ?></div>
              <?php endif; ?>
            </td>
            <?php if ($es_admin): ?>
            <td style="font-size:12px;color:var(--t3)"><?= e($lc['asesor_nombre']) ?></td>
            <?php endif; ?>
            <td class="tbl-num"><?= rp((float)$lc['total']) ?></td>
            <td><?= estado_badge($lc['estado']) ?></td>
            <td class="tbl-num"><?= (int)$lc['visitas'] ?></td>
            <td style="font:400 12px var(--num);color:var(--t3);white-space:nowrap">
              <?= date('d/m/Y', strtotime($lc['created_at'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /panel-cotizaciones -->


<!-- ══ TAB: RECIBOS ═════════════════════════════════════════ -->
<div class="tab-panel <?= $tab==='recibos'?'on':'' ?>" id="panel-recibos">

  <?php if (empty($lista_recibos)): ?>
    <div class="empty card" style="padding:40px">Sin recibos en el período seleccionado</div>
  <?php else: ?>

  <!-- KPIs recibos -->
  <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="kpi-card">
      <div class="kpi-label">Total recibos</div>
      <div class="kpi-val"><?= count($lista_recibos) ?></div>
      <div class="kpi-sub"><?= $num_cancelados ?> cancelado<?= $num_cancelados!=1?'s':'' ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Monto cobrado</div>
      <div class="kpi-val green"><?= rp($total_recibos_pagados) ?></div>
      <div class="kpi-sub">Recibos activos</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Cancelaciones</div>
      <div class="kpi-val danger"><?= rp($total_recibos_cancelados) ?></div>
      <div class="kpi-sub"><?= $num_cancelados ?> recibo<?= $num_cancelados!=1?'s':'' ?></div>
    </div>
  </div>

  <div class="sec-lbl">Detalle de recibos</div>
  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl" id="tbl-recibos">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Cliente / Venta</th>
            <?php if ($es_admin): ?><th>Asesor</th><?php endif; ?>
            <th class="r">Monto</th>
            <th>Tipo</th>
            <th>Forma pago</th>
            <th>Estado</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista_recibos as $rec):
            $es_cancel = (int)($rec['cancelado'] ?? 0);
            $es_tipo_cancel = $rec['tipo'] === 'cancelacion';
            $fp_raw = trim($rec['forma_pago'] ?? '');
            $forma = match($fp_raw) {
                'efectivo' => 'Efectivo',
                'transferencia' => 'Transferencia',
                'tarjeta' => 'Tarjeta',
                '' => '—',
                default => ucfirst($fp_raw),
            };
          ?>
          <tr>
            <td>
              <a href="/ventas/<?= (int)$rec['venta_id'] ?>"
                 style="font:700 12px var(--num);color:var(--g);text-decoration:none">
                <?= e($rec['numero']) ?>
              </a>
            </td>
            <td>
              <div style="font:600 13px var(--body)"><?= e($rec['cliente_nombre'] ?? '—') ?></div>
              <div class="tbl-sub"><?= e(mb_substr($rec['venta_titulo'] ?? '',0,40)) ?> · <?= e($rec['venta_numero'] ?? '') ?></div>
            </td>
            <?php if ($es_admin): ?>
            <td style="font-size:12px;color:var(--t3)"><?= e($rec['asesor_nombre'] ?? '—') ?></td>
            <?php endif; ?>
            <td class="tbl-num" style="color:<?= $es_cancel ? 'var(--danger)' : 'var(--g)' ?>">
              <?= $es_cancel ? '-' : '' ?><?= rp((float)$rec['monto']) ?>
            </td>
            <td>
              <?php if ($es_tipo_cancel): ?>
                <span class="badge badge-purple" style="background:var(--purple-bg);color:var(--purple)">Cancelación</span>
              <?php else: ?>
                <span class="badge badge-green">Abono</span>
              <?php endif; ?>
            </td>
            <td style="font-size:12px;color:var(--t3)"><?= e($forma) ?></td>
            <td>
              <?php if ($es_cancel): ?>
                <span class="badge badge-red">Cancelado</span>
              <?php else: ?>
                <span class="badge badge-green">Activo</span>
              <?php endif; ?>
            </td>
            <td style="font:400 12px var(--num);color:var(--t3);white-space:nowrap">
              <?= date('d/m/Y', strtotime($rec['fecha'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /panel-recibos -->


<!-- ══ TAB: COSTOS Y MÁRGENES ════════════════════════════════ -->
<div class="tab-panel <?= $tab==='costos'?'on':'' ?>" id="panel-costos">

  <?php
  $total_costos_tab = array_sum(array_column($costos_por_cat, 'total'));
  ?>

  <?php if (empty($ventas_con_margen) && empty($costos_por_cat) && empty($gastos_generales_rep)): ?>
    <div class="empty card" style="padding:40px">Sin costos registrados en el período</div>
  <?php else: ?>

  <!-- ── KPIs según modo ── -->
  <?php if ($costos_modo === 'empresa'): ?>
  <!-- MODO EMPRESA: Estado de resultados simplificado -->
  <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
    <div class="kpi-card">
      <div class="kpi-label">Ingresos del período</div>
      <div class="kpi-val green"><?= rp($ingresos) ?></div>
      <div class="kpi-sub"><?= $num_ventas ?> venta<?= $num_ventas!=1?'s':'' ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Gastos operativos</div>
      <div class="kpi-val amber"><?= rp($total_gastos_gen) ?></div>
      <div class="kpi-sub"><?= count($gastos_generales_rep) ?> gasto<?= count($gastos_generales_rep)!=1?'s':'' ?></div>
    </div>
    <div class="kpi-card">
      <?php $utilidad_neta = $ingresos - $total_gastos_gen; ?>
      <div class="kpi-label">Utilidad neta</div>
      <div class="kpi-val <?= $utilidad_neta>=0?'green':'danger' ?>"><?= rp($utilidad_neta) ?></div>
    </div>
    <div class="kpi-card">
      <?php $margen_neto = $ingresos > 0 ? round($utilidad_neta / $ingresos * 100, 1) : 0; ?>
      <div class="kpi-label">Margen neto</div>
      <div class="kpi-val <?= $margen_neto>=30?'green':($margen_neto>=15?'amber':'danger') ?>"><?= rpp($margen_neto) ?></div>
    </div>
  </div>

  <?php elseif ($costos_modo === 'ambos'): ?>
  <!-- MODO AMBOS: Costos directos + gastos operativos -->
  <?php
    $costos_directos = array_sum(array_map(fn($v) => (float)$v['costos'], $ventas_con_margen));
    $utilidad_bruta_ambos = $ingresos - $costos_directos;
    $utilidad_neta_ambos  = $utilidad_bruta_ambos - $total_gastos_gen;
    $margen_neto_ambos    = $ingresos > 0 ? round($utilidad_neta_ambos / $ingresos * 100, 1) : 0;
  ?>
  <div class="kpi-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:20px">
    <div class="kpi-card">
      <div class="kpi-label">Ingresos</div>
      <div class="kpi-val green"><?= rp($ingresos) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Costos directos</div>
      <div class="kpi-val amber"><?= rp($costos_directos) ?></div>
      <div class="kpi-sub">Asignados a ventas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Gastos operativos</div>
      <div class="kpi-val amber"><?= rp($total_gastos_gen) ?></div>
      <div class="kpi-sub">Gastos generales</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Utilidad neta</div>
      <div class="kpi-val <?= $utilidad_neta_ambos>=0?'green':'danger' ?>"><?= rp($utilidad_neta_ambos) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Margen neto</div>
      <div class="kpi-val <?= $margen_neto_ambos>=30?'green':($margen_neto_ambos>=15?'amber':'danger') ?>"><?= rpp($margen_neto_ambos) ?></div>
    </div>
  </div>

  <?php else: ?>
  <!-- MODO VENTA: KPIs originales -->
  <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
    <div class="kpi-card">
      <div class="kpi-label">Total costos</div>
      <div class="kpi-val amber"><?= rp($total_costos_tab) ?></div>
      <div class="kpi-sub"><?= count($costos_por_cat) ?> categoría<?= count($costos_por_cat)!=1?'s':'' ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Utilidad bruta</div>
      <?php $ub2 = $ingresos - $total_costos_tab; ?>
      <div class="kpi-val <?= $ub2>=0?'green':'danger' ?>"><?= rp($ub2) ?></div>
      <div class="kpi-sub">Sobre <?= rp($ingresos) ?> de ingresos</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Margen bruto promedio</div>
      <?php $mg2 = $ingresos > 0 ? round(($ingresos-$total_costos_tab)/$ingresos*100,1) : 0; ?>
      <div class="kpi-val <?= $mg2>=30?'green':($mg2>=15?'amber':'danger') ?>"><?= rpp($mg2) ?></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Costos por categoría + evolución mensual (todos los modos) ── -->
  <div class="costos-layout">
    <div class="stat-card">
      <div class="sec-lbl" style="margin-bottom:12px">Por categoría</div>
      <?php if (empty($costos_por_cat)): ?>
        <div style="color:var(--t3);font-size:13px;padding:10px 0">Sin gastos registrados</div>
      <?php else: ?>
        <?php foreach ($costos_por_cat as $cc):
          $pct_cat = $total_costos_tab > 0 ? round((float)$cc['total'] / $total_costos_tab * 100, 1) : 0;
          $color   = $cc['color'] ?? '#6b7280';
        ?>
        <div class="donut-item" style="padding:8px 0;border-bottom:1px solid var(--border)">
          <div class="donut-dot" style="background:<?= e($color) ?>"></div>
          <div class="donut-lbl"><?= e($cc['categoria'] ?? 'Sin categoría') ?></div>
          <div style="text-align:right">
            <div class="donut-pct"><?= rp((float)$cc['total']) ?></div>
            <div class="donut-amt"><?= $pct_cat ?>% · <?= (int)$cc['num_gastos'] ?> gasto<?= $cc['num_gastos']!=1?'s':'' ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="height:10px;border-radius:5px;overflow:hidden;display:flex;margin-top:14px;gap:2px">
          <?php foreach ($costos_por_cat as $cc):
            $w = $total_costos_tab > 0 ? round((float)$cc['total'] / $total_costos_tab * 100, 1) : 0;
            $color = $cc['color'] ?? '#6b7280';
          ?>
          <div style="width:<?= $w ?>%;background:<?= e($color) ?>;min-width:2px" title="<?= e($cc['categoria']??'Sin cat') ?>: <?= rp((float)$cc['total']) ?>"></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="stat-card">
      <div class="sec-lbl" style="margin-bottom:12px">Evolución mensual</div>
      <?php
      $max_cos_chart = max(1, ...(array_map('floatval', array_column($serie_costos, 'monto') ?: [0])));
      $meses6_l = []; $meses6_c = [];
      for ($i = 5; $i >= 0; $i--) {
          $m = date('Y-m', strtotime("-$i months"));
          $meses6_l[] = date('M', strtotime($m.'-01'));
          $c = 0;
          foreach ($serie_costos as $sc) { if ($sc['mes']===$m) { $c=(float)$sc['monto']; break; } }
          $meses6_c[] = $c;
          if ($c > $max_cos_chart) $max_cos_chart = $c;
      }
      ?>
      <div class="bar-chart" style="height:100px">
        <?php for ($i=0;$i<6;$i++):
          $h = $max_cos_chart>0 ? round($meses6_c[$i]/$max_cos_chart*90) : 2;
        ?>
        <div class="bar-col">
          <div class="bar-wrap" style="height:90px">
            <div class="bar" style="height:<?= max(2,$h) ?>px;background:#f59e0b;flex:1" title="<?= rp($meses6_c[$i]) ?>"></div>
          </div>
          <div class="bar-lbl"><?= $meses6_l[$i] ?></div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <?php if (in_array($costos_modo, ['venta', 'ambos']) && !empty($ventas_con_margen)): ?>
  <!-- ── Tabla ventas con margen (modo venta y ambos) ── -->
  <div class="sec-lbl">Ventas — detalle de margen</div>
  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl" id="tbl-costos">
        <thead>
          <tr>
            <th>Venta</th>
            <th>Cliente</th>
            <?php if ($es_admin): ?><th>Asesor</th><?php endif; ?>
            <th class="r">Total venta</th>
            <th class="r">Costos</th>
            <th class="r">Utilidad</th>
            <th style="min-width:140px">Margen</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ventas_con_margen as $vm): ?>
          <tr>
            <td>
              <a href="/ventas/<?= (int)$vm['id'] ?>"
                 style="font:700 12px var(--num);color:var(--g);text-decoration:none">
                <?= e($vm['numero']) ?>
              </a>
              <div class="tbl-sub"><?= e(mb_substr($vm['titulo'],0,40)) ?></div>
            </td>
            <td style="font-size:12px;color:var(--t3)"><?= e($vm['cliente'] ?? '—') ?></td>
            <?php if ($es_admin): ?>
            <td style="font-size:12px;color:var(--t3)"><?= e($vm['asesor'] ?? '—') ?></td>
            <?php endif; ?>
            <td class="tbl-num"><?= rp((float)$vm['total']) ?></td>
            <td class="tbl-num" style="color:#b45309"><?= rp((float)$vm['costos']) ?></td>
            <td class="tbl-num" style="color:<?= $vm['utilidad']>=0?'var(--g)':'var(--danger)' ?>"><?= rp((float)$vm['utilidad']) ?></td>
            <td><?= margen_bar((float)$vm['total'], (float)$vm['costos']) ?></td>
            <td style="font:400 12px var(--num);color:var(--t3);white-space:nowrap">
              <?= date('d/m/Y', strtotime($vm['created_at'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($costos_modo, ['empresa', 'ambos']) && !empty($gastos_generales_rep)): ?>
  <!-- ── Tabla gastos generales (modo empresa y ambos) ── -->
  <div class="sec-lbl"><?= $costos_modo === 'ambos' ? 'Gastos operativos (generales)' : 'Detalle de gastos' ?></div>
  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl" id="tbl-gastos-gen">
        <thead>
          <tr>
            <th>Concepto</th>
            <th>Categoría</th>
            <th>Proveedor</th>
            <th class="r">Importe</th>
            <th>Fecha</th>
            <th>Nota</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($gastos_generales_rep as $gg): ?>
          <tr>
            <td style="font:600 13px var(--body)"><?= e($gg['concepto']) ?></td>
            <td>
              <?php if ($gg['categoria']): ?>
              <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= e($gg['color'] ?? '#6b7280') ?>;display:inline-block"></span>
                <?= e($gg['categoria']) ?>
              </span>
              <?php else: ?>
              <span style="color:var(--t3);font-size:12px">Sin categoría</span>
              <?php endif; ?>
            </td>
            <td style="font-size:12px;color:var(--t3)"><?= e($gg['proveedor'] ?? '—') ?></td>
            <td class="tbl-num" style="color:#b45309"><?= rp((float)$gg['importe']) ?></td>
            <td style="font:400 12px var(--num);color:var(--t3);white-space:nowrap">
              <?= date('d/m/Y', strtotime($gg['fecha'])) ?>
            </td>
            <td style="font-size:12px;color:var(--t3);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?= e($gg['nota'] ?? '') ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($costos_modo, ['empresa', 'ambos'])): ?>
  <!-- ── Punto de equilibrio (modo empresa y ambos) ── -->
  <?php
    // Calcular promedio mensual de gastos generales (últimos 6 meses)
    $gastos_6m = DB::query(
        "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(importe) AS total
         FROM gastos_venta
         WHERE empresa_id=? AND venta_id IS NULL
           AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY mes ORDER BY mes DESC",
        [$empresa_id]
    );
    $meses_con_gastos = count($gastos_6m);
    $promedio_gastos_mes = $meses_con_gastos > 0
        ? array_sum(array_column($gastos_6m, 'total')) / $meses_con_gastos
        : 0;

    // Promedio de ingresos mensuales (últimos 6 meses)
    $ingresos_6m = DB::query(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes, SUM(total) AS total
         FROM ventas
         WHERE empresa_id=? AND estado != 'cancelada'
           AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY mes ORDER BY mes DESC",
        [$empresa_id]
    );
    $meses_con_ventas = count($ingresos_6m);
    $promedio_ingresos_mes = $meses_con_ventas > 0
        ? array_sum(array_column($ingresos_6m, 'total')) / $meses_con_ventas
        : 0;

    // Ventas promedio por mes (cantidad)
    $promedio_num_ventas = $meses_con_ventas > 0
        ? (int)DB::val(
            "SELECT COUNT(*) FROM ventas
             WHERE empresa_id=? AND estado != 'cancelada'
               AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)",
            [$empresa_id]
          ) / $meses_con_ventas
        : 0;

    // Ticket promedio
    $ticket_prom = $promedio_num_ventas > 0
        ? $promedio_ingresos_mes / $promedio_num_ventas
        : 0;

    // Si modo ambos, restar también costos directos promedio
    $costos_directos_prom = 0;
    if ($costos_modo === 'ambos' && $meses_con_ventas > 0) {
        $costos_directos_prom = (float)DB::val(
            "SELECT COALESCE(SUM(importe),0) / ? FROM gastos_venta
             WHERE empresa_id=? AND venta_id IS NOT NULL
               AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)",
            [$meses_con_ventas, $empresa_id]
        );
    }

    // Margen bruto % (después de costos directos)
    $margen_prom_pct = $promedio_ingresos_mes > 0
        ? ($promedio_ingresos_mes - $costos_directos_prom) / $promedio_ingresos_mes
        : 0;

    // Punto de equilibrio = Gastos fijos / Margen bruto %
    $punto_eq = ($margen_prom_pct > 0) ? $promedio_gastos_mes / $margen_prom_pct : 0;
    $ventas_eq = ($ticket_prom > 0) ? ceil($punto_eq / $ticket_prom) : 0;

    // Cobertura actual: ingresos del período / gastos del período
    $cobertura = ($promedio_gastos_mes > 0)
        ? round($promedio_ingresos_mes / $promedio_gastos_mes, 2)
        : 0;
  ?>
  <?php if ($promedio_gastos_mes > 0): ?>
  <div class="sec-lbl">Punto de equilibrio</div>
  <div class="card" style="padding:20px">
    <div class="kpi-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:16px">
      <div class="kpi-card" style="border:none;padding:12px">
        <div class="kpi-label">Gastos fijos mensuales</div>
        <div class="kpi-val amber" style="font-size:18px"><?= rp($promedio_gastos_mes) ?></div>
        <div class="kpi-sub">Promedio <?= $meses_con_gastos ?> mes<?= $meses_con_gastos!=1?'es':'' ?></div>
      </div>
      <div class="kpi-card" style="border:none;padding:12px">
        <div class="kpi-label">Necesitas vender</div>
        <div class="kpi-val green" style="font-size:18px"><?= rp($punto_eq) ?>/mes</div>
        <div class="kpi-sub"><?= $ventas_eq ?> venta<?= $ventas_eq!=1?'s':'' ?> al ticket prom. de <?= rp($ticket_prom) ?></div>
      </div>
      <div class="kpi-card" style="border:none;padding:12px">
        <div class="kpi-label">Cobertura actual</div>
        <div class="kpi-val <?= $cobertura>=1.0?'green':'danger' ?>" style="font-size:18px"><?= number_format($cobertura, 1) ?>x</div>
        <div class="kpi-sub"><?= $cobertura >= 1.0 ? 'Cubres tus gastos' : 'No cubres gastos fijos' ?></div>
      </div>
    </div>
    <!-- Barra visual de punto de equilibrio -->
    <?php
      $pct_eq = ($punto_eq > 0 && $promedio_ingresos_mes > 0)
          ? min(150, round($promedio_ingresos_mes / $punto_eq * 100))
          : 0;
      $bar_color = $pct_eq >= 100 ? 'var(--g)' : 'var(--danger)';
    ?>
    <div style="position:relative;margin-top:8px">
      <div style="font:400 12px var(--body);color:var(--t3);margin-bottom:6px">Ingresos promedio vs punto de equilibrio</div>
      <div style="height:24px;background:#f1f5f9;border-radius:6px;overflow:hidden;position:relative">
        <div style="height:100%;width:<?= min(100, $pct_eq) ?>%;background:<?= $bar_color ?>;border-radius:6px;transition:width .3s"></div>
        <div style="position:absolute;left:<?= min(95, round($punto_eq / max(1,$promedio_ingresos_mes,$punto_eq) * 100)) ?>%;top:0;bottom:0;width:2px;background:#dc2626" title="Punto de equilibrio: <?= rp($punto_eq) ?>"></div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:4px;font:400 11px var(--num);color:var(--t3)">
        <span>$0</span>
        <span style="color:#dc2626;font-weight:600">P.E. <?= rp($punto_eq) ?></span>
        <span><?= rp(max($promedio_ingresos_mes, $punto_eq)) ?></span>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <?php if (in_array($costos_modo, ['venta', 'ambos']) && count($ventas_con_margen) > 1): ?>
  <!-- ── Ranking de rentabilidad (modo venta y ambos) ── -->
  <?php
    // Filtrar ventas que tienen costos registrados y ordenar por margen
    $ventas_con_costos = array_filter($ventas_con_margen, fn($v) => (float)$v['costos'] > 0);
    usort($ventas_con_costos, function($a, $b) {
        $ma = (float)$a['total'] > 0 ? ((float)$a['total'] - (float)$a['costos']) / (float)$a['total'] : 0;
        $mb = (float)$b['total'] > 0 ? ((float)$b['total'] - (float)$b['costos']) / (float)$b['total'] : 0;
        return $mb <=> $ma;
    });
    $top_rentables  = array_slice($ventas_con_costos, 0, 5);
    $peor_rentables = array_slice(array_reverse($ventas_con_costos), 0, 5);
  ?>
  <?php if (count($ventas_con_costos) >= 2): ?>
  <div class="sec-lbl">Ranking de rentabilidad</div>
  <div class="costos-layout">
    <!-- Top 5 más rentables -->
    <div class="stat-card">
      <div style="font:700 13px var(--body);color:var(--g);margin-bottom:12px"><?= ico('check', 14, '#16a34a') ?> Más rentables</div>
      <?php foreach ($top_rentables as $tr):
        $mg_r = (float)$tr['total'] > 0 ? round(((float)$tr['total'] - (float)$tr['costos']) / (float)$tr['total'] * 100, 1) : 0;
      ?>
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)">
        <div style="flex:1;min-width:0">
          <div style="font:600 12px var(--num);color:var(--g)"><?= e($tr['numero']) ?></div>
          <div style="font:400 12px var(--body);color:var(--t3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e(mb_substr($tr['titulo'],0,35)) ?></div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font:700 13px var(--num);color:var(--g)"><?= rpp($mg_r) ?></div>
          <div style="font:400 11px var(--num);color:var(--t3)">Util. <?= rp((float)$tr['utilidad']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Top 5 menos rentables -->
    <div class="stat-card">
      <div style="font:700 13px var(--body);color:var(--danger);margin-bottom:12px"><?= ico('alert', 14, '#dc2626') ?> Menos rentables</div>
      <?php foreach ($peor_rentables as $pr):
        $mg_p = (float)$pr['total'] > 0 ? round(((float)$pr['total'] - (float)$pr['costos']) / (float)$pr['total'] * 100, 1) : 0;
      ?>
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)">
        <div style="flex:1;min-width:0">
          <div style="font:600 12px var(--num);color:var(--danger)"><?= e($pr['numero']) ?></div>
          <div style="font:400 12px var(--body);color:var(--t3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e(mb_substr($pr['titulo'],0,35)) ?></div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font:700 13px var(--num);color:<?= $mg_p>=15?'#b45309':'var(--danger)' ?>"><?= rpp($mg_p) ?></div>
          <div style="font:400 11px var(--num);color:var(--t3)">Util. <?= rp((float)$pr['utilidad']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <?php endif; ?>
</div><!-- /panel-costos -->


<?php if ($es_business_rep): ?>
<!-- ══ TAB: PROVEEDORES ════════════════════════════════════ -->
<div class="tab-panel <?= $tab==='proveedores'?'on':'' ?>" id="panel-proveedores">

  <?php if (empty($prov_top)): ?>
    <div class="empty card" style="padding:40px">Sin gastos con proveedor asignado en el período</div>
  <?php else: ?>

  <!-- KPIs -->
  <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
    <div class="kpi-card">
      <div class="kpi-label">Total pagado a proveedores</div>
      <div class="kpi-val amber"><?= rp($prov_total_periodo) ?></div>
      <div class="kpi-sub"><?= array_sum(array_column($prov_top, 'num_gastos')) ?> transacciones</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Proveedores activos</div>
      <div class="kpi-val" style="color:var(--text)"><?= count($prov_top) ?></div>
      <div class="kpi-sub">Con gastos en el período</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Ticket promedio</div>
      <?php $prov_num_total = array_sum(array_column($prov_top, 'num_gastos'));
            $prov_ticket = $prov_num_total > 0 ? $prov_total_periodo / $prov_num_total : 0; ?>
      <div class="kpi-val" style="color:var(--text)"><?= rp($prov_ticket) ?></div>
      <div class="kpi-sub">Por transacción</div>
    </div>
  </div>

  <!-- Top proveedores + evolución mensual -->
  <div class="costos-layout">
    <!-- Ranking de proveedores -->
    <div class="stat-card">
      <div class="sec-lbl" style="margin-bottom:12px">Top proveedores</div>
      <?php foreach ($prov_top as $i => $pt):
        $pct_prov = $prov_total_periodo > 0 ? round((float)$pt['total'] / $prov_total_periodo * 100, 1) : 0;
      ?>
      <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
        <div style="width:24px;height:24px;border-radius:50%;background:<?= $i < 3 ? '#f59e0b' : '#e2e2dc' ?>;color:<?= $i < 3 ? '#fff' : 'var(--t3)' ?>;display:flex;align-items:center;justify-content:center;font:700 11px var(--num);flex-shrink:0">
          <?= $i + 1 ?>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font:600 13px var(--body);color:var(--text)"><?= e($pt['nombre']) ?></div>
          <div style="font:400 11px var(--body);color:var(--t3)"><?= (int)$pt['num_gastos'] ?> gasto<?= $pt['num_gastos']!=1?'s':'' ?></div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font:700 13px var(--num);color:#b45309"><?= rp((float)$pt['total']) ?></div>
          <div style="font:400 11px var(--num);color:var(--t3)"><?= $pct_prov ?>%</div>
        </div>
      </div>
      <?php endforeach; ?>
      <!-- Barra apilada -->
      <div style="height:10px;border-radius:5px;overflow:hidden;display:flex;margin-top:14px;gap:2px">
        <?php
          $colors_prov = ['#f59e0b','#f97316','#ef4444','#8b5cf6','#3b82f6','#06b6d4','#10b981','#6b7280'];
          foreach ($prov_top as $i => $pt):
            $w = $prov_total_periodo > 0 ? round((float)$pt['total'] / $prov_total_periodo * 100, 1) : 0;
            $c = $colors_prov[$i % count($colors_prov)];
        ?>
        <div style="width:<?= $w ?>%;background:<?= $c ?>;min-width:2px" title="<?= e($pt['nombre']) ?>: <?= rp((float)$pt['total']) ?>"></div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Evolución mensual por proveedor -->
    <div class="stat-card">
      <div class="sec-lbl" style="margin-bottom:12px">Pagos mensuales</div>
      <?php
        // Construir datos: meses como columnas, proveedores como series
        $meses_prov = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-$i months"));
            $meses_prov[$m] = ['label' => date('M', strtotime($m.'-01')), 'total' => 0];
        }
        foreach ($prov_mensual as $pm) {
            if (isset($meses_prov[$pm['mes']])) {
                $meses_prov[$pm['mes']]['total'] += (float)$pm['total'];
            }
        }
        $max_prov_chart = max(1, ...array_column($meses_prov, 'total'));
      ?>
      <div class="bar-chart" style="height:100px">
        <?php foreach ($meses_prov as $mp):
          $h = $max_prov_chart > 0 ? round($mp['total'] / $max_prov_chart * 90) : 2;
        ?>
        <div class="bar-col">
          <div class="bar-wrap" style="height:90px">
            <div class="bar" style="height:<?= max(2, $h) ?>px;background:#f59e0b;flex:1" title="<?= rp($mp['total']) ?>"></div>
          </div>
          <div class="bar-lbl"><?= $mp['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Tabla detallada de pagos -->
  <div class="sec-lbl">Detalle de pagos a proveedores</div>
  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl" id="tbl-proveedores">
        <thead>
          <tr>
            <th>Proveedor</th>
            <th>Concepto</th>
            <th>Categoría</th>
            <th>Venta</th>
            <th class="r">Importe</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($prov_lista_rep as $pl): ?>
          <tr>
            <td style="font:600 13px var(--body)"><?= e($pl['proveedor']) ?></td>
            <td style="font-size:12px"><?= e($pl['concepto']) ?></td>
            <td>
              <?php if ($pl['categoria']): ?>
              <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= e($pl['color'] ?? '#6b7280') ?>;display:inline-block"></span>
                <?= e($pl['categoria']) ?>
              </span>
              <?php else: ?>
              <span style="color:var(--t3);font-size:12px">—</span>
              <?php endif; ?>
            </td>
            <td style="font:400 12px var(--num);color:var(--t3)"><?= e($pl['venta_numero'] ?? 'General') ?></td>
            <td class="tbl-num" style="color:#b45309"><?= rp((float)$pl['importe']) ?></td>
            <td style="font:400 12px var(--num);color:var(--t3);white-space:nowrap">
              <?= date('d/m/Y', strtotime($pl['fecha'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php endif; ?>
</div><!-- /panel-proveedores -->
<?php endif; ?>


<script>
// ── Rango de fechas ─────────────────────────────────────────
function toggleRango(val) {
    var rango = document.getElementById('rangoFechas');
    var label = document.getElementById('rangoLabel');
    if (val === 'rango') {
        rango.style.display = 'flex';
        if (label) label.style.display = 'none';
    } else {
        rango.style.display = 'none';
        if (label) label.style.display = '';
        document.getElementById('fPeriodo').submit();
    }
}

// ── Tabs ────────────────────────────────────────────────────
function repTab(id, el) {
    document.querySelectorAll('.rep-tab').forEach(t => t.classList.remove('on'));
    el.classList.add('on');
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('on'));
    document.getElementById('panel-' + id)?.classList.add('on');
    document.getElementById('hTab').value = id;
    history.replaceState(null, '', '/reportes?tab=' + id + '&periodo=<?= e($periodo_val) ?>');
}

// ── Exportar CSV ─────────────────────────────────────────────
function exportarCSV() {
    // Detectar tabla activa
    const activePanel = document.querySelector('.tab-panel.on');
    if (!activePanel) return;
    const tbl = activePanel.querySelector('table[id]');
    if (!tbl) { alert('Sin datos para exportar en esta vista.'); return; }

    const rows = [];
    tbl.querySelectorAll('tr').forEach(tr => {
        const cols = [...tr.querySelectorAll('th, td')].map(td => {
            // Limpiar: quitar HTML de badges y barras
            let t = td.innerText.trim().replace(/\n+/g,' ');
            // Escapar comillas
            t = '"' + t.replace(/"/g,'""') + '"';
            return t;
        });
        if (cols.length > 0) rows.push(cols.join(','));
    });

    const csv     = '\uFEFF' + rows.join('\r\n'); // BOM para Excel en español
    const blob    = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url     = URL.createObjectURL(blob);
    const a       = document.createElement('a');
    a.href        = url;
    a.download    = 'reporte_<?= $periodo_val ?>_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
