<?php
// ============================================================
//  CotizaApp — modules/costos/index.php
//  GET /costos
// ============================================================

defined('COTIZAAPP') or die;

// Permiso de módulo
if (!Auth::es_admin() && !Auth::puede('ver_costos')) { redirect('/dashboard'); }

$empresa_id   = EMPRESA_ID;
$es_admin     = Auth::es_admin();
$solo_mias    = !$es_admin && !Auth::puede('ver_todas_ventas');
$uid          = Auth::id();
$empresa_data = Auth::empresa();
$costos_modo  = $empresa_data['costos_modo'] ?? 'venta';
$v_where    = $solo_mias ? "AND (v.usuario_id = $uid OR v.vendedor_id = $uid)" : '';

// ─── Período ────────────────────────────────────────────────
// ─── Siempre mostramos todas las ventas; filtro solo por categoría ──
$ahora = new DateTimeImmutable('now', new DateTimeZone('America/Hermosillo'));
$fecha_where = '';
$cat_f = (int)($_GET['cat'] ?? 0); // 0 = todas

// ─── Búsqueda ────────────────────────────────────────────────
$q = trim($_GET['q'] ?? '');
$q_where = '';
$q_bind  = [];
if ($q !== '') {
    $q_like   = '%' . $q . '%';
    $q_where  = "AND (v.titulo LIKE ? OR v.numero LIKE ? OR cl.nombre LIKE ?)";
    $q_bind   = [$q_like, $q_like, $q_like];
}

// ─── Filtro categoría ────────────────────────────────────────
$margen_where = ''; // sin filtro HAVING

// ─── Categorías activas ──────────────────────────────────────
$categorias = DB::query(
    "SELECT id, nombre, color, activa,
            (SELECT COUNT(*) FROM gastos_venta gv WHERE gv.categoria_id = cc.id AND gv.empresa_id = ?) AS num_costos
     FROM categorias_costos cc
     WHERE cc.empresa_id = ? ORDER BY cc.nombre ASC",
    [$empresa_id, $empresa_id]
);
$cats_activas = array_filter($categorias, fn($c) => $c['activa']);
$cats_map     = array_column($categorias, null, 'id');

// ─── Lista de ventas con costos ──────────────────────────────
// Filtro por categoría
$cat_join  = '';
$cat_where = '';
$bind_cat  = [];
if ($cat_f > 0) {
    $cat_join  = "INNER JOIN gastos_venta gv2 ON gv2.venta_id = v.id AND gv2.categoria_id = ?";
    $bind_cat  = [$cat_f];
}

$bind_ventas = array_merge([$empresa_id], $bind_cat, $q_bind);
$ventas_raw  = DB::query(
    "SELECT v.id, v.numero, v.titulo, v.total, v.estado, v.created_at,
            cl.nombre AS cliente_nombre, cl.telefono AS cli_tel,
            COALESCE(SUM(gv.importe), 0) AS total_costos
     FROM ventas v
     LEFT JOIN clientes cl   ON cl.id = v.cliente_id
     LEFT JOIN gastos_venta gv ON gv.venta_id = v.id AND gv.empresa_id = v.empresa_id
     $cat_join
     WHERE v.empresa_id = ? AND v.estado != 'cancelada'
       $v_where $q_where
     GROUP BY v.id
     ORDER BY v.created_at DESC",
    $bind_ventas
);

// Calcular margen para cada venta
foreach ($ventas_raw as &$v) {
    $total  = (float)$v['total'];
    $costos = (float)$v['total_costos'];
    if ($total > 0 && $costos > 0) {
        $v['margen_pct']   = round((($total - $costos) / $total) * 100, 1);
        $v['utilidad']     = $total - $costos;
        $v['margen_nivel'] = $v['margen_pct'] >= 30 ? 'ok' : ($v['margen_pct'] >= 15 ? 'med' : 'mal');
    } else {
        $v['margen_pct']   = null;
        $v['utilidad']     = $total;
        $v['margen_nivel'] = 'sin_costo';
    }
}
unset($v);

// ─── KPIs este mes ───────────────────────────────────────────
$mes_desde    = $ahora->format('Y-m-01 00:00:00');
$mes_hasta    = $ahora->format('Y-m-t 23:59:59');
$mes_label    = strtolower($ahora->format('M Y')); // "mar 2026"

// Mes anterior para comparativo
$ant          = $ahora->modify('first day of last month');
$ant_desde    = $ant->format('Y-m-01 00:00:00');
$ant_hasta    = $ant->format('Y-m-t 23:59:59');
$ant_label    = strtolower($ant->format('M Y')); // "feb 2026"

$kpi_row = DB::row(
    "SELECT
        COALESCE(SUM(v.total), 0) AS total_ventas,
        COALESCE(SUM(gv.importe), 0) AS total_costos,
        COUNT(DISTINCT v.id) AS num_ventas
     FROM ventas v
     LEFT JOIN gastos_venta gv ON gv.venta_id = v.id AND gv.empresa_id = v.empresa_id
     WHERE v.empresa_id = ? AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ?",
    [$empresa_id, $mes_desde, $mes_hasta]
);
$kpi_total_ventas = (float)$kpi_row['total_ventas'];
$kpi_total_costos = (float)$kpi_row['total_costos'];
$kpi_num_ventas   = (int)$kpi_row['num_ventas'];
$kpi_utilidad     = $kpi_total_ventas - $kpi_total_costos;
$kpi_margen_prom  = $kpi_total_ventas > 0
    ? round((($kpi_total_ventas - $kpi_total_costos) / $kpi_total_ventas) * 100, 1)
    : 0;

// Margen mes anterior
$ant_row = DB::row(
    "SELECT
        COALESCE(SUM(v.total), 0) AS total_ventas,
        COALESCE(SUM(gv.importe), 0) AS total_costos
     FROM ventas v
     LEFT JOIN gastos_venta gv ON gv.venta_id = v.id AND gv.empresa_id = v.empresa_id
     WHERE v.empresa_id = ? AND v.estado != 'cancelada'
       AND v.created_at BETWEEN ? AND ?",
    [$empresa_id, $ant_desde, $ant_hasta]
);
$ant_ventas  = (float)$ant_row['total_ventas'];
$ant_costos  = (float)$ant_row['total_costos'];
$ant_margen  = $ant_ventas > 0
    ? round((($ant_ventas - $ant_costos) / $ant_ventas) * 100, 1)
    : null;

// Para tabla seguimos usando todos (sin filtro de fecha)
$kpi_global_ventas = array_sum(array_column($ventas_raw, 'total'));
$kpi_global_costos = array_sum(array_column($ventas_raw, 'total_costos'));

// ─── Análisis avanzado (Business) ──────────────────────────
$plan_costos = trial_info($empresa_id);
$es_business = $plan_costos['es_business'];

$analisis_por_cat    = [];
$analisis_por_prov   = [];
$analisis_tendencia  = [];

if ($es_business) {
    // Costos por categoría con margen
    $analisis_por_cat = DB::query(
        "SELECT cc.id, cc.nombre, cc.color,
                COUNT(gv.id) AS num_gastos,
                COALESCE(SUM(gv.importe), 0) AS total_cat
         FROM categorias_costos cc
         LEFT JOIN gastos_venta gv ON gv.categoria_id = cc.id AND gv.empresa_id = cc.empresa_id
         WHERE cc.empresa_id = ? AND cc.activa = 1
         GROUP BY cc.id
         HAVING total_cat > 0
         ORDER BY total_cat DESC",
        [$empresa_id]
    );

    // Top proveedores
    $has_prov_col = DB::val(
        "SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='gastos_venta' AND COLUMN_NAME='proveedor_id' LIMIT 1"
    );
    $has_prov_table = DB::val(
        "SELECT 1 FROM information_schema.TABLES
         WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='proveedores' LIMIT 1"
    );
    if ($has_prov_col && $has_prov_table) {
        $analisis_por_prov = DB::query(
            "SELECT p.id, p.nombre,
                    COUNT(gv.id) AS num_gastos,
                    COALESCE(SUM(gv.importe), 0) AS total_prov
             FROM proveedores p
             INNER JOIN gastos_venta gv ON gv.proveedor_id = p.id AND gv.empresa_id = p.empresa_id
             WHERE p.empresa_id = ? AND p.activo = 1
             GROUP BY p.id
             ORDER BY total_prov DESC
             LIMIT 10",
            [$empresa_id]
        );
    }

    // Tendencia últimos 6 meses
    $analisis_tendencia = DB::query(
        "SELECT DATE_FORMAT(gv.fecha, '%Y-%m') AS mes,
                COALESCE(SUM(gv.importe), 0) AS total_costos,
                COALESCE(SUM(v.total), 0) AS total_ventas_raw
         FROM gastos_venta gv
         LEFT JOIN ventas v ON v.id = gv.venta_id
         WHERE gv.empresa_id = ? AND gv.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY mes
         ORDER BY mes ASC",
        [$empresa_id]
    );
    // Also get ventas totals per month for proper margin calc
    $ventas_por_mes = DB::query(
        "SELECT DATE_FORMAT(v.created_at, '%Y-%m') AS mes,
                COALESCE(SUM(v.total), 0) AS total_ventas
         FROM ventas v
         WHERE v.empresa_id = ? AND v.estado != 'cancelada'
               AND v.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY mes
         ORDER BY mes ASC",
        [$empresa_id]
    );
    $ventas_mes_map = array_column($ventas_por_mes, 'total_ventas', 'mes');

    // Proveedores for sheet selector
    $proveedores_lista = [];
    if ($has_prov_table) {
        $proveedores_lista = DB::query(
            "SELECT id, nombre FROM proveedores WHERE empresa_id=? AND activo=1 ORDER BY nombre",
            [$empresa_id]
        );
    }

    // Proveedores full list for tab
    $proveedores_full = [];
    if ($has_prov_table) {
        $proveedores_full = DB::query(
            "SELECT p.*,
                    COALESCE(g.num_gastos, 0)    AS num_gastos,
                    COALESCE(g.total_gastos, 0)  AS total_gastos
             FROM proveedores p
             LEFT JOIN (
                 SELECT proveedor_id, COUNT(*) AS num_gastos, SUM(importe) AS total_gastos
                 FROM gastos_venta WHERE empresa_id = ?
                 GROUP BY proveedor_id
             ) g ON g.proveedor_id = p.id
             WHERE p.empresa_id = ? AND p.activo = 1
             ORDER BY p.nombre ASC",
            [$empresa_id, $empresa_id]
        );
    }
}

// ─── Gastos generales (sin venta asociada) ─────────────────
// Check if venta_id allows NULL
$permite_null = DB::val(
    "SELECT IS_NULLABLE FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='gastos_venta' AND COLUMN_NAME='venta_id' LIMIT 1"
) === 'YES';

$gastos_generales = [];
$total_generales  = 0;
$ver_generales    = $permite_null && in_array($costos_modo, ['empresa', 'ambos']);
if ($ver_generales) {
    $gastos_generales = DB::query(
        "SELECT gv.*, cc.nombre AS cat_nombre, cc.color AS cat_color" .
        ($has_prov_col ?? false ? ", p.nombre AS prov_nombre" : "") .
        " FROM gastos_venta gv
         LEFT JOIN categorias_costos cc ON cc.id = gv.categoria_id" .
        ($has_prov_col ?? false ? " LEFT JOIN proveedores p ON p.id = gv.proveedor_id" : "") .
        " WHERE gv.empresa_id = ? AND gv.venta_id IS NULL
         ORDER BY gv.fecha DESC, gv.id DESC
         LIMIT 100",
        [$empresa_id]
    );
    $total_generales = array_sum(array_column($gastos_generales, 'importe'));
}

// ─── Helpers ────────────────────────────────────────────────
function ini_c(string $n): string {
    $p = array_filter(explode(' ', $n));
    $i = ''; foreach (array_slice($p,0,2) as $w) $i .= strtoupper($w[0]);
    return $i ?: '?';
}
function fmt_c(float $n): string {
    return '$' . number_format($n, 0, '.', ',');
}
function fmt_c_short(float $n): string {
    if ($n >= 1_000_000) return '$' . number_format($n/1_000_000,1) . 'M';
    if ($n >= 1_000)     return '$' . number_format($n/1_000,0) . 'K';
    return '$' . number_format($n,0);
}
function margen_cls(string $nivel): string {
    return match($nivel) { 'ok' => 'margen-ok', 'med' => 'margen-med', 'mal' => 'margen-mal', default => '' };
}
function fill_cls(string $nivel): string {
    return match($nivel) { 'ok' => 'fill-ok', 'med' => 'fill-med', 'mal' => 'fill-mal', default => '' };
}
function color_hex(string $color): string {
    // Si ya es hex lo devuelve; si es nombre de color conocido lo mapea
    if (preg_match('/^#[0-9a-f]{3,6}$/i', $color)) return $color;
    return '#94a3b8'; // fallback slate
}

$page_title = 'Costos';
ob_start();
?>
<style>
/* ── Reset Tailwind interference ── */
#content *, #content *::before, #content *::after {
    box-sizing: border-box;
    border-width: 0;
    border-style: solid;
}
#content { font-size: 14px; font-family: var(--body); }
#content a { color: inherit; text-decoration: none; }
#content button { cursor: pointer; }

/* tabs */
.page-toolbar{display:flex;align-items:center;justify-content:space-between;margin:-16px -24px 16px;padding:0 24px;background:var(--white);border-bottom:1px solid var(--border)}
.tab-bar{display:flex;gap:0}
.ctab{padding:13px 18px;font:600 13px var(--body);color:var(--t3);cursor:pointer;border-bottom:2px solid transparent;white-space:nowrap;transition:all .15s;background:none;border-top:none;border-left:none;border-right:none}
.ctab.on{color:var(--g);border-bottom-color:var(--g)}
.ctab:hover:not(.on){color:var(--t2)}
.tab-panel{display:none}.tab-panel.on{display:block}
.new-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 13px var(--body);color:#fff;cursor:pointer;white-space:nowrap;transition:opacity .15s;text-decoration:none}
.new-btn:hover{opacity:.88}

/* KPIs */
.kpi-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px}
.kpi-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;box-shadow:var(--sh)}
.kpi-lbl{font:600 10px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);margin-bottom:6px}
.kpi-val{font:600 18px var(--num);color:var(--text)}
.kpi-val.red{color:var(--danger)}.kpi-val.green{color:var(--g)}.kpi-val.amber{color:#b45309}
.kpi-sub{font:400 11px var(--num);color:var(--t3);margin-top:3px}

/* filtros */
.filter-bar{display:flex;gap:6px;margin-bottom:12px;overflow-x:auto;padding-bottom:2px;scrollbar-width:none;align-items:center;flex-wrap:wrap}
.chip{padding:7px 13px;border-radius:20px;border:1px solid var(--border);background:var(--white);font:600 12px var(--body);color:var(--t2);cursor:pointer;white-space:nowrap;transition:all .12s}
.chip.on{background:var(--g);border-color:var(--g);color:#fff}

/* búsqueda */
.search-wrap{position:relative;margin-bottom:12px}
.search-wrap input{width:100%;background:var(--white);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:10px 14px 10px 38px;font:400 14px var(--body);color:var(--text);outline:none;transition:border-color .15s;box-shadow:var(--sh)}
.search-wrap input:focus{border-color:var(--g)}
.search-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;color:var(--t3);pointer-events:none}

/* tabla ventas */
.list-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}
.tbl-header{display:none}
.venta-row{display:flex;flex-direction:column;padding:13px 16px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;text-decoration:none;color:inherit}
.venta-row:last-child{border-bottom:none}
.venta-row:hover{background:#fafaf8}
.vr-top{display:flex;align-items:flex-start;gap:10px}
.vr-av{display:none}
.vr-info{flex:1;min-width:0}
.vr-folio{font:500 11px var(--num) !important;color:var(--t3);margin-bottom:3px}
.vr-titulo{font:600 16px var(--body) !important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vr-cliente{font:400 13px var(--body);color:var(--t3);margin-top:2px}
@media(min-width:641px){.vr-cliente{display:none}}
.vr-meta{display:flex;justify-content:space-between;align-items:center;margin-top:8px}
.vr-costos-lbl{font:500 12px var(--body);color:var(--t3)}
.vr-costos-val{font:600 14px var(--num);color:var(--danger)}
.vr-right{text-align:right;flex-shrink:0}
.vr-venta{font:600 14px var(--num);color:var(--text)}
.vr-margen{font:500 11px var(--num);margin-top:3px}
/* Desktop cols — ocultas en mobile */
.vr-col-cliente,.vr-col-venta,.vr-col-costos,.vr-col-margen,.vr-col-accion{display:none}
.margen-ok{color:var(--g)}.margen-med{color:#b45309}.margen-mal{color:var(--danger)}
.margen-bar-wrap{margin-top:8px}
.margen-bar{height:4px;border-radius:2px;background:var(--border);overflow:hidden}
.margen-fill{height:100%;border-radius:2px}
.fill-ok{background:var(--g)}.fill-med{background:#f59e0b}.fill-mal{background:var(--danger)}

/* DESKTOP tabla */
@media(min-width:641px){
  .tbl-header{display:grid;grid-template-columns:minmax(0,2.2fr) 150px 110px 110px 110px 90px;align-items:center;padding:8px 16px;border-bottom:2px solid var(--border);background:var(--bg)}
  .tbl-header span{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3)}
  .venta-row{display:grid;grid-template-columns:minmax(0,2.2fr) 150px 110px 110px 110px 90px;align-items:center;padding:18px 16px;flex-direction:unset}
  .vr-top{align-items:center}
  .vr-meta,.margen-bar-wrap{display:none !important}
  .vr-top{grid-column:1}
  .vr-col-cliente{grid-column:2}
  .vr-col-venta{grid-column:3}
  .vr-col-costos{grid-column:4}
  .vr-col-margen{grid-column:5}
  .vr-col-accion{grid-column:6}
  .vr-col-cliente{display:block;min-width:0;padding-right:12px}
  .vr-col-cliente div:first-child{font:500 14px var(--body) !important;color:var(--text)}
  .vr-col-cliente div:last-child{font:400 12px var(--num) !important;color:var(--t3);margin-top:3px}
  .vr-col-venta,.vr-col-costos,.vr-col-margen{display:block}
  .vr-col-accion{display:flex;justify-content:flex-start}
  .vr-col-venta{font:600 15px var(--num) !important;color:var(--text);white-space:nowrap}
  .vr-col-costos{font:700 15px var(--num) !important;color:var(--danger);white-space:nowrap}
  .vr-col-venta{color:var(--text)}.vr-col-costos{color:var(--danger)}
  .vr-col-margen .pct{font:700 15px var(--num) !important;display:block}
  .vr-col-margen .pct.ok{color:var(--g)}.vr-col-margen .pct.med{color:#b45309}.vr-col-margen .pct.mal{color:var(--danger)}
  .vr-col-margen .bar{height:4px;border-radius:2px;background:var(--border);overflow:hidden;margin-top:6px;width:80px}
  .vr-col-margen .bar-fill{height:100%;border-radius:2px}
  .vr-right{display:none}
}

/* Categorías */
.cat-row{display:flex;align-items:center;gap:12px;padding:11px 14px;border-bottom:1px solid var(--border);transition:background .12s}
.cat-row:last-child{border-bottom:none}
.cat-row:hover{background:#fafaf8}
.cat-dot{width:10px;height:10px;border-radius:5px;flex-shrink:0}
.cat-nombre{flex:1;font:500 14px var(--body)}
.cat-count{font:400 12px var(--num);color:var(--t3);min-width:60px;text-align:right}
.toggle{position:relative;display:inline-block;width:38px;height:22px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;border-radius:11px;background:var(--border2);transition:background .2s}
.toggle input:checked + .toggle-track{background:var(--g)}
.toggle-thumb{position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:left .2s;pointer-events:none;box-shadow:0 1px 2px rgba(0,0,0,.18)}
.toggle input:checked ~ .toggle-thumb{left:19px}
.cat-edit-btn{width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:13px;color:var(--t3);transition:all .12s;flex-shrink:0}
.cat-edit-btn:hover{border-color:var(--g);color:var(--g)}
.add-row-btn{width:100%;padding:12px;border-radius:var(--r);border:1.5px dashed var(--border2);background:transparent;display:flex;align-items:center;justify-content:center;gap:8px;font:600 13px var(--body);color:var(--t2);cursor:pointer;transition:all .15s;margin-top:8px}
.add-row-btn:hover{border-color:var(--g);color:var(--g);background:var(--g-bg)}

/* Paleta colores */
.color-swatch{width:28px;height:28px;border-radius:50%;cursor:pointer;border:2.5px solid transparent;transition:border-color .12s;flex-shrink:0}
.color-swatch.sel{border-color:#1a1a18}

/* Sheets */
.sh-overlay{position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .25s}
.sh-overlay.open{opacity:1;pointer-events:all}
.bottom-sheet{position:fixed;bottom:0;left:0;right:0;z-index:201;background:var(--white);border-radius:20px 20px 0 0;max-height:92vh;display:flex;flex-direction:column;transform:translateY(100%);transition:transform .3s cubic-bezier(.32,0,.15,1);box-shadow:0 -8px 32px rgba(0,0,0,.1);max-width:640px;margin:0 auto}
.bottom-sheet.open{transform:translateY(0)}
.sh-handle{width:34px;height:4px;border-radius:2px;background:var(--border2);margin:12px auto 0;flex-shrink:0}
.sh-header{padding:14px 18px 12px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;border-bottom:1px solid var(--border)}
.sh-title{font:800 17px var(--body)}
.sh-close{width:30px;height:30px;border-radius:999px;border:none;background:var(--bg);font-size:15px;cursor:pointer;color:var(--t2)}
.sh-body{overflow-y:auto;flex:1;padding:0 0 16px}
.sh-field{padding:13px 18px;border-bottom:1px solid var(--border)}
.sh-field:last-child{border-bottom:none}
.sh-lbl{font:700 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);margin-bottom:6px}
.sh-input{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:11px 13px;font:400 15px var(--body);color:var(--text);outline:none;transition:border-color .15s}
.sh-input:focus{border-color:var(--g)}
.sh-select{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:11px 13px;font:400 15px var(--body);color:var(--text);outline:none;appearance:none;cursor:pointer}
.sh-select:focus{border-color:var(--g)}
.sh-row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sh-footer{padding:14px 18px;border-top:1px solid var(--border);flex-shrink:0;display:flex;gap:10px}
.sh-btn-save{flex:1;padding:13px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 14px var(--body);color:#fff;cursor:pointer}
.sh-btn-cancel{padding:13px 18px;border-radius:var(--r-sm);border:1px solid var(--border);background:transparent;font:600 14px var(--body);color:var(--t2);cursor:pointer}

/* Análisis */
.slabel{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t2);margin:20px 0 10px;display:flex;align-items:center;gap:10px}
.slabel::after{content:'';flex:1;height:1.5px;background:var(--border)}
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}

/* Vacío */
.empty-state{text-align:center;padding:40px 20px}
.empty-ico{font-size:36px;margin-bottom:10px}
.empty-tit{font:700 15px var(--body);margin-bottom:5px}
.empty-sub{font:400 13px var(--body);color:var(--t3);max-width:380px;margin:0 auto;line-height:1.6}

@media(max-width:640px){
  .kpi-row{grid-template-columns:1fr 1fr}
  .kpi-row .kpi-card:last-child{grid-column:1/-1}
  .sh-row2{grid-template-columns:1fr}
}

/* Proveedores list items */
.prov-item { display:flex; align-items:center; gap:12px; padding:13px 16px; border-bottom:1px solid var(--border); cursor:pointer; transition:background .12s; }
.prov-item:last-child { border-bottom:none; }
.prov-item:hover { background:#fafaf8; }
</style>

<!-- Toolbar: tabs + botón -->
<div class="page-toolbar">
  <?php
    $ver_tab_ventas    = in_array($costos_modo, ['venta', 'ambos']);
    $ver_tab_generales = $ver_generales;
    // Tab por default: el primero visible
    $tab_default = $ver_tab_ventas ? 'ventas' : 'generales';
  ?>
  <div class="tab-bar">
    <?php if ($ver_tab_ventas): ?>
    <button class="ctab <?= $tab_default==='ventas'?'on':'' ?>" id="ctab-ventas" onclick="cTab('ventas',this)">Costos por venta</button>
    <?php endif; ?>
    <?php if ($ver_tab_generales): ?>
    <button class="ctab <?= $tab_default==='generales'?'on':'' ?>" id="ctab-generales" onclick="cTab('generales',this)">Gastos generales<?php if ($total_generales > 0): ?> <span style="font:600 11px var(--num);color:var(--danger);margin-left:2px"><?= fmt_c_short($total_generales) ?></span><?php endif; ?></button>
    <?php endif; ?>
    <button class="ctab"   id="ctab-categorias"  onclick="cTab('categorias',this)">Categorías</button>
    <?php if ($es_business): ?>
    <button class="ctab"   id="ctab-analisis"    onclick="cTab('analisis',this)">Análisis</button>
    <button class="ctab"   id="ctab-proveedores" onclick="cTab('proveedores',this)">Proveedores<?php if (!empty($proveedores_full)): ?> <span style="font:600 11px var(--num);color:var(--t3);margin-left:2px"><?= count($proveedores_full) ?></span><?php endif; ?></button>
    <?php endif; ?>
  </div>
  <?php if ($costos_modo === 'empresa'): ?>
  <button class="new-btn" onclick="abrirGastoGeneral()">+ Registrar gasto</button>
  <?php else: ?>
  <button class="new-btn" onclick="abrirCosto()">+ Registrar costo</button>
  <?php endif; ?>
</div>

<?php if ($ver_tab_ventas): ?>
<!-- ══ TAB: COSTOS POR VENTA ══ -->
<div class="tab-panel <?= $tab_default==='ventas'?'on':'' ?>" id="ctab-panel-ventas">

  <!-- KPIs -->
  <div class="kpi-row">
    <div class="kpi-card">
      <div class="kpi-lbl">Ventas del mes</div>
      <div class="kpi-val"><?= fmt_c_short($kpi_total_ventas) ?></div>
      <div class="kpi-sub"><?= $kpi_num_ventas ?> venta<?= $kpi_num_ventas != 1 ? 's' : '' ?> activa<?= $kpi_num_ventas != 1 ? 's' : '' ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-lbl">Costos del mes</div>
      <div class="kpi-val red"><?= fmt_c_short($kpi_total_costos) ?></div>
      <div class="kpi-sub"><?= $mes_label ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-lbl">Margen promedio</div>
      <div class="kpi-val <?= $kpi_margen_prom >= 30 ? 'green' : ($kpi_margen_prom >= 15 ? 'amber' : 'red') ?>"><?= $kpi_total_costos > 0 ? $kpi_margen_prom.'%' : '—' ?></div>
      <?php if ($ant_margen !== null): ?>
      <div class="kpi-sub">vs <?= $ant_margen ?>% <?= $ant_label ?></div>
      <?php else: ?>
      <div class="kpi-sub">Sin datos previos</div>
      <?php endif ?>
    </div>
  </div>

  <!-- Filtros por categoría -->
  <div class="filter-bar">
    <a href="<?= '?q='.urlencode($q) ?>" class="chip <?= $cat_f===0?'on':'' ?>">Todas</a>
    <?php foreach ($cats_activas as $cat): ?>
    <a href="<?= '?cat='.(int)$cat['id'].'&q='.urlencode($q) ?>" class="chip <?= $cat_f===(int)$cat['id']?'on':'' ?>">
      <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= e($cat['color']) ?>;margin-right:4px;vertical-align:middle"></span><?= e($cat['nombre']) ?>
    </a>
    <?php endforeach ?>
  </div>

  <!-- Búsqueda -->
  <div class="search-wrap">
    <span class="search-ico"><?= ico('search', 16, '#6a6a64') ?></span>
    <input type="text" id="busqueda" placeholder="Buscar venta o cliente…"
           value="<?= e($q) ?>"
           oninput="debounce(()=>buscar(this.value), 280)">
  </div>

  <!-- Lista -->
  <?php if (empty($ventas_raw)): ?>
  <div class="list-card">
    <div class="empty-state">
      <div class="empty-ico"><?= ico('chart', 32, '#94a3b8') ?></div>
      <div class="empty-tit">Sin ventas que mostrar</div>
      <div class="empty-sub">Cuando conviertas cotizaciones en ventas, podrás registrar sus costos aquí.</div>
    </div>
  </div>
  <?php else: ?>
  <div class="list-card">
    <div class="tbl-header">
      <span>Proyecto / Cliente</span>
      <span>Cliente</span>
      <span>Venta</span>
      <span>Costos</span>
      <span>Margen</span>
      <span>Acciones</span>
    </div>
    <?php foreach ($ventas_raw as $v):
      $ini   = ini_c($v['cliente_nombre'] ?? '?');
      $pct   = $v['margen_pct'];
      $nivel = $v['margen_nivel'];
      $fill  = fill_cls($nivel);
      $mcls  = margen_cls($nivel);
    ?>
    <a class="venta-row" href="/costos/<?= (int)$v['id'] ?>">
      <!-- Mobile layout -->
      <div class="vr-top">
        <div class="vr-av"><?= e($ini) ?></div>
        <div class="vr-info">
          <div class="vr-folio"><?= e($v['numero']) ?></div>
          <div class="vr-titulo"><?= e(mb_substr($v['titulo'],0,52)) ?></div>
          <div class="vr-cliente"><?= e($v['cliente_nombre'] ?? '—') ?></div>
        </div>
        <div class="vr-right">
          <div class="vr-venta"><?= fmt_c_short((float)$v['total']) ?></div>
          <?php if ($pct !== null): ?>
          <div class="vr-margen <?= $mcls ?>"><?= $pct ?>% margen</div>
          <?php else: ?>
          <div class="vr-margen" style="color:var(--t3)">Sin costos</div>
          <?php endif; ?>
        </div>
      </div>
      <div class="vr-meta">
        <?php if ((float)$v['total_costos'] > 0): ?>
        <span class="vr-costos-lbl">Costos registrados</span>
        <span class="vr-costos-val"><?= fmt_c_short((float)$v['total_costos']) ?></span>
        <?php else: ?>
        <span class="vr-costos-lbl">Sin costos registrados</span>
        <span class="vr-costos-val" style="color:var(--t3)">$0</span>
        <?php endif; ?>
      </div>
      <?php if ((float)$v['total_costos'] > 0): ?>
      <div class="margen-bar-wrap">
        <div class="margen-bar">
          <div class="margen-fill <?= $fill ?>" style="width:<?= min(100, max(0, (int)$pct)) ?>%"></div>
        </div>
      </div>
      <?php endif; ?>
      <!-- Desktop cols -->
      <div class="vr-col-cliente">
        <div style="font:500 13px var(--body);color:var(--t2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($v['cliente_nombre'] ?? '—') ?></div>
        <div style="font:400 11px var(--num);color:var(--t3);margin-top:2px"><?= e($v['cli_tel'] ?? '') ?></div>
      </div>
      <div class="vr-col-venta"><?= fmt_c((float)$v['total']) ?></div>
      <div class="vr-col-costos" style="font:600 14px var(--num);color:<?= (float)$v['total_costos']>0?'var(--danger)':'var(--t3)' ?>">
        <?= (float)$v['total_costos'] > 0 ? fmt_c((float)$v['total_costos']) : '$0' ?>
      </div>
      <div class="vr-col-margen">
        <?php if ($pct !== null): ?>
        <div class="pct <?= $nivel ?>"><?= $pct ?>%</div>
        <div class="bar"><div class="bar-fill <?= $fill ?>" style="width:<?= min(100,(int)$pct) ?>%"></div></div>
        <?php else: ?>
        <div class="pct" style="color:var(--t3)">—</div>
        <?php endif; ?>
      </div>
      <div class="vr-col-accion">
        <button onclick="event.stopPropagation();abrirCosto(<?= (int)$v['id'] ?>)"
                class="new-btn" style="<?= (float)$v['total_costos']==0?'':'background:var(--white);color:var(--t2);border:1px solid var(--border)' ?>;padding:6px 12px;font-size:12px;box-shadow:var(--sh)">
          + Costo
        </button>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div><!-- /ctab-panel-ventas -->
<?php endif; /* ver_tab_ventas */ ?>

<?php if ($ver_tab_generales): ?>
<!-- ══ TAB: GASTOS GENERALES ══ -->
<div class="tab-panel <?= $tab_default==='generales'?'on':'' ?>" id="ctab-panel-generales">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
    <p style="font:400 13px var(--body);color:var(--t3);line-height:1.6">
      Gastos que no están asociados a una venta específica: renta, nómina, servicios, seguros, etc.
    </p>
    <?php if ($costos_modo === 'ambos'): ?>
    <button class="new-btn" onclick="abrirGastoGeneral()" style="flex-shrink:0">+ Gasto general</button>
    <?php endif; ?>
  </div>

  <?php if ($total_generales > 0): ?>
  <div class="kpi-row" style="grid-template-columns:repeat(2,1fr);margin-bottom:14px">
    <div class="kpi-card">
      <div class="kpi-lbl">Total gastos generales</div>
      <div class="kpi-val red"><?= fmt_c($total_generales) ?></div>
      <div class="kpi-sub"><?= count($gastos_generales) ?> gasto<?= count($gastos_generales) != 1 ? 's' : '' ?></div>
    </div>
    <?php if ($kpi_global_ventas > 0): ?>
    <div class="kpi-card">
      <div class="kpi-lbl">% sobre ventas totales</div>
      <div class="kpi-val amber"><?= round(($total_generales / $kpi_global_ventas) * 100, 1) ?>%</div>
      <div class="kpi-sub">de <?= fmt_c_short($kpi_global_ventas) ?> en ventas</div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="list-card">
    <?php if (empty($gastos_generales)): ?>
    <div class="empty-state">
      <div class="empty-ico" style="font-size:28px">📋</div>
      <div class="empty-tit">Sin gastos generales</div>
      <div class="empty-sub">Registra gastos fijos como renta, nómina, servicios, seguros, etc.</div>
    </div>
    <?php else: ?>
    <div class="tbl-header" style="grid-template-columns:10px minmax(0,2fr) 130px 100px 90px 70px">
      <span></span>
      <span>Concepto</span>
      <span>Categoría</span>
      <span>Fecha</span>
      <span style="text-align:right">Importe</span>
      <span></span>
    </div>
    <?php foreach ($gastos_generales as $gg):
      $gg_color = preg_match('/^#[0-9a-f]{3,6}$/i', $gg['cat_color'] ?? '') ? $gg['cat_color'] : '#94a3b8';
      $gg_fecha = $gg['fecha'] ? date('j M Y', strtotime($gg['fecha'])) : '—';
    ?>
    <div class="cost-row" style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-bottom:1px solid var(--border)">
      <div style="width:8px;height:8px;border-radius:4px;flex-shrink:0;background:<?= $gg_color ?>"></div>
      <div style="flex:1;min-width:0">
        <div style="font:600 13px var(--body)"><?= e($gg['concepto']) ?></div>
        <div style="font:400 11px var(--num);color:var(--t3);margin-top:2px">
          <?= e($gg['cat_nombre'] ?? '—') ?>
          <?php if (!empty($gg['prov_nombre'])): ?> · <?= e($gg['prov_nombre']) ?><?php endif; ?>
          · <?= $gg_fecha ?>
        </div>
      </div>
      <div style="font:600 14px var(--num);color:var(--danger);flex-shrink:0;white-space:nowrap"><?= fmt_c((float)$gg['importe']) ?></div>
      <div style="display:flex;gap:4px;flex-shrink:0">
        <button style="width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;color:var(--t3)"
                onclick='editarGastoGeneral(<?= (int)$gg["id"] ?>, <?= htmlspecialchars(json_encode(["categoria_id"=>(int)$gg["categoria_id"],"proveedor_id"=>(int)($gg["proveedor_id"]??0),"concepto"=>$gg["concepto"],"importe"=>$gg["importe"],"fecha"=>$gg["fecha"],"nota"=>$gg["nota"]??""]), ENT_QUOTES) ?>)'>✎</button>
        <button style="width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;color:var(--t3)"
                onclick="eliminarGastoGeneral(<?= (int)$gg['id'] ?>, this)">✕</button>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div><!-- /ctab-panel-generales -->
<?php endif; ?>

<!-- ══ TAB: CATEGORÍAS ══ -->
<div class="tab-panel" id="ctab-panel-categorias">

  <p style="font:400 13px var(--body);color:var(--t3);margin:14px 0 12px">Las activas aparecen al registrar un costo</p>

  <?php if (empty($categorias)): ?>
  <div class="list-card">
    <div class="empty-state">
      <div class="empty-ico"><?= ico('tag', 32, '#94a3b8') ?></div>
      <div class="empty-tit">Sin categorías aún</div>
      <div class="empty-sub"><?= $es_admin ? 'Crea categorías para organizar tus costos por tipo.' : 'El administrador aún no ha creado categorías.' ?></div>
    </div>
  </div>
  <?php else: ?>
  <div class="list-card" id="catList">
    <?php foreach ($categorias as $cat): ?>
    <div class="cat-row" data-cat-id="<?= (int)$cat['id'] ?>">
      <div class="cat-dot" style="background:<?= e(color_hex($cat['color'] ?? '#94a3b8')) ?>"></div>
      <div class="cat-nombre" style="<?= !$cat['activa']?'color:var(--t3)':'' ?>"><?= e($cat['nombre']) ?></div>
      <div class="cat-count"><?= (int)$cat['num_costos'] ?> costo<?= $cat['num_costos']!=1?'s':'' ?></div>
      <?php if ($es_admin): ?>
      <label class="toggle" title="Activar/Desactivar">
        <input type="checkbox" <?= $cat['activa']?'checked':'' ?>
               onchange="toggleCategoria(<?= (int)$cat['id'] ?>, this.checked)">
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
      <button class="cat-edit-btn" onclick="abrirCategoria(<?= (int)$cat['id'] ?>, <?= htmlspecialchars(json_encode(['nombre'=>$cat['nombre'],'color'=>$cat['color']??'#3b82f6']), ENT_QUOTES) ?>)">✎</button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($es_admin): ?>
  <button class="add-row-btn" onclick="abrirCategoria(null)">+ Nueva categoría</button>
  <?php endif; ?>

</div><!-- /ctab-panel-categorias -->


<?php if ($es_business): ?>
<!-- ══ TAB: ANÁLISIS (Business) ══ -->
<div class="tab-panel" id="ctab-panel-analisis">

  <!-- ── Costos por categoría ──────────────────────────────── -->
  <div class="slabel" style="margin-top:4px">Costos por categoría</div>
  <?php if (empty($analisis_por_cat)): ?>
  <div class="card" style="padding:30px 20px;text-align:center;color:var(--t3);font:400 13px var(--body)">
    Sin costos registrados aún.
  </div>
  <?php else: ?>
  <?php
    $total_all_cats = array_sum(array_column($analisis_por_cat, 'total_cat'));
    $max_cat = max(array_column($analisis_por_cat, 'total_cat'));
  ?>
  <div class="card">
    <?php foreach ($analisis_por_cat as $ac):
      $pct_cat = $total_all_cats > 0 ? round(($ac['total_cat'] / $total_all_cats) * 100, 1) : 0;
      $bar_w   = $max_cat > 0 ? round(($ac['total_cat'] / $max_cat) * 100) : 0;
      $c_hex   = preg_match('/^#[0-9a-f]{3,6}$/i', $ac['color']) ? $ac['color'] : '#94a3b8';
    ?>
    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border)">
      <div style="width:10px;height:10px;border-radius:5px;flex-shrink:0;background:<?= $c_hex ?>"></div>
      <div style="flex:1;min-width:0">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
          <span style="font:600 13px var(--body)"><?= e($ac['nombre']) ?></span>
          <span style="font:600 14px var(--num);color:var(--danger)"><?= fmt_c((float)$ac['total_cat']) ?></span>
        </div>
        <div style="height:6px;border-radius:3px;background:var(--border);overflow:hidden">
          <div style="height:100%;border-radius:3px;background:<?= $c_hex ?>;width:<?= $bar_w ?>%"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:3px">
          <span style="font:400 11px var(--num);color:var(--t3)"><?= (int)$ac['num_gastos'] ?> gasto<?= $ac['num_gastos'] != 1 ? 's' : '' ?></span>
          <span style="font:600 11px var(--num);color:var(--t3)"><?= $pct_cat ?>%</span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg);font:700 13px var(--body)">
      <span>Total costos</span>
      <span style="font:700 15px var(--num);color:var(--danger)"><?= fmt_c($total_all_cats) ?></span>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Margen por categoría ──────────────────────────────── -->
  <?php if (!empty($analisis_por_cat) && $kpi_global_ventas > 0): ?>
  <div class="slabel">Impacto en margen</div>
  <div class="card" style="padding:16px">
    <div style="display:flex;flex-direction:column;gap:10px">
      <?php foreach ($analisis_por_cat as $ac):
        $impacto = round(((float)$ac['total_cat'] / $kpi_global_ventas) * 100, 1);
        $c_hex = preg_match('/^#[0-9a-f]{3,6}$/i', $ac['color']) ? $ac['color'] : '#94a3b8';
      ?>
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:8px;height:8px;border-radius:4px;flex-shrink:0;background:<?= $c_hex ?>"></div>
        <span style="flex:1;font:500 13px var(--body);min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($ac['nombre']) ?></span>
        <span style="font:700 13px var(--num);color:var(--danger);white-space:nowrap">-<?= $impacto ?>%</span>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <span style="font:700 13px var(--body)">Margen neto</span>
      <?php
        $margen_neto = round((($kpi_global_ventas - $kpi_global_costos) / $kpi_global_ventas) * 100, 1);
        $mn_color = $margen_neto >= 30 ? 'var(--g)' : ($margen_neto >= 15 ? '#b45309' : 'var(--danger)');
      ?>
      <span style="font:700 18px var(--num);color:<?= $mn_color ?>"><?= $margen_neto ?>%</span>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Top proveedores ───────────────────────────────────── -->
  <div class="slabel">Top proveedores</div>
  <?php if (empty($analisis_por_prov)): ?>
  <div class="card" style="padding:30px 20px;text-align:center;color:var(--t3);font:400 13px var(--body)">
    Sin gastos asociados a proveedores.
    <br><a href="javascript:void(0)" onclick="cTab('proveedores',document.getElementById('ctab-proveedores'))" style="color:var(--g);font-weight:600;text-decoration:none">Ver Proveedores →</a>
  </div>
  <?php else: ?>
  <?php $max_prov = max(array_column($analisis_por_prov, 'total_prov')); ?>
  <div class="card">
    <?php foreach ($analisis_por_prov as $i => $ap):
      $bar_pw = $max_prov > 0 ? round(($ap['total_prov'] / $max_prov) * 100) : 0;
      $ini_p = strtoupper(substr($ap['nombre'], 0, 1));
      if (strpos($ap['nombre'], ' ') !== false) {
          $pp = explode(' ', $ap['nombre']);
          $ini_p = strtoupper($pp[0][0] . ($pp[1][0] ?? ''));
      }
    ?>
    <a href="/proveedores/<?= (int)$ap['id'] ?>" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;transition:background .12s" onmouseover="this.style.background='#fafaf8'" onmouseout="this.style.background=''">
      <div style="width:32px;height:32px;border-radius:8px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 12px var(--body);color:#fff;flex-shrink:0"><?= e($ini_p) ?></div>
      <div style="flex:1;min-width:0">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
          <span style="font:600 13px var(--body)"><?= e($ap['nombre']) ?></span>
          <span style="font:600 14px var(--num);color:var(--danger)"><?= fmt_c((float)$ap['total_prov']) ?></span>
        </div>
        <div style="height:4px;border-radius:2px;background:var(--border);overflow:hidden">
          <div style="height:100%;border-radius:2px;background:var(--g);width:<?= $bar_pw ?>%"></div>
        </div>
        <div style="font:400 11px var(--num);color:var(--t3);margin-top:3px"><?= (int)$ap['num_gastos'] ?> gasto<?= $ap['num_gastos'] != 1 ? 's' : '' ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ── Tendencia mensual ─────────────────────────────────── -->
  <?php if (!empty($analisis_tendencia)): ?>
  <div class="slabel">Tendencia mensual</div>
  <div class="card" style="padding:16px">
    <?php
      $max_tend = 0;
      foreach ($analisis_tendencia as $t) {
          $max_tend = max($max_tend, (float)$t['total_costos']);
      }
      $meses_es = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
    ?>
    <div style="display:flex;flex-direction:column;gap:8px">
      <?php foreach ($analisis_tendencia as $t):
        $parts = explode('-', $t['mes']);
        $mes_lbl = ($meses_es[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
        $bar_tw = $max_tend > 0 ? round(((float)$t['total_costos'] / $max_tend) * 100) : 0;
        $venta_mes = (float)($ventas_mes_map[$t['mes']] ?? 0);
        $margen_mes = $venta_mes > 0 ? round((($venta_mes - (float)$t['total_costos']) / $venta_mes) * 100, 1) : null;
        $m_color = $margen_mes !== null ? ($margen_mes >= 30 ? 'var(--g)' : ($margen_mes >= 15 ? '#b45309' : 'var(--danger)')) : 'var(--t3)';
      ?>
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
          <span style="font:500 12px var(--body);color:var(--t2)"><?= $mes_lbl ?></span>
          <div style="display:flex;gap:12px;align-items:center">
            <span style="font:600 13px var(--num);color:var(--danger)"><?= fmt_c_short((float)$t['total_costos']) ?></span>
            <?php if ($margen_mes !== null): ?>
            <span style="font:600 11px var(--num);color:<?= $m_color ?>"><?= $margen_mes ?>%</span>
            <?php endif; ?>
          </div>
        </div>
        <div style="height:6px;border-radius:3px;background:var(--border);overflow:hidden">
          <div style="height:100%;border-radius:3px;background:var(--danger);opacity:.6;width:<?= $bar_tw ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /ctab-panel-analisis -->

<!-- ══ TAB: PROVEEDORES (Business) ══ -->
<div class="tab-panel" id="ctab-panel-proveedores">

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
    <p style="font:400 13px var(--body);color:var(--t3);margin:0">
        <?= count($proveedores_full) ?> proveedor<?= count($proveedores_full) != 1 ? 'es' : '' ?> activo<?= count($proveedores_full) != 1 ? 's' : '' ?>
    </p>
    <button onclick="abrirProv(null)"
            style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 12px var(--body);color:#fff;cursor:pointer;transition:opacity .15s"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        + Nuevo proveedor
    </button>
  </div>

  <?php if (empty($proveedores_full)): ?>
  <div class="card" style="text-align:center;padding:50px 20px;color:var(--t3)">
    <div style="font:700 15px var(--body);color:var(--t2);margin-bottom:6px">Sin proveedores aún</div>
    <div style="font:400 13px var(--body)">Agrega tu primer proveedor con el botón de arriba.</div>
  </div>
  <?php else: ?>
  <div class="card">
    <?php foreach ($proveedores_full as $pv):
        $ini_p = strtoupper(substr($pv['nombre'], 0, 1));
        if (strpos($pv['nombre'], ' ') !== false) {
            $pp = explode(' ', $pv['nombre']);
            $ini_p = strtoupper($pp[0][0] . ($pp[1][0] ?? ''));
        }
        $total_fmt = $pv['total_gastos'] > 0 ? fmt_c((float)$pv['total_gastos']) : '—';
    ?>
    <div class="prov-item" onclick="abrirProv(<?= htmlspecialchars(json_encode([
        'id'        => (int)$pv['id'],
        'nombre'    => $pv['nombre'],
        'contacto'  => $pv['contacto'] ?? '',
        'telefono'  => $pv['telefono'] ?? '',
        'email'     => $pv['email'] ?? '',
        'direccion' => $pv['direccion'] ?? '',
        'nota'      => $pv['nota'] ?? '',
    ]), ENT_QUOTES) ?>)">
      <div style="width:36px;height:36px;border-radius:9px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 13px var(--body);color:#fff;flex-shrink:0"><?= e($ini_p) ?></div>
      <div style="flex:1;min-width:0">
        <div style="font:600 14px var(--body);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($pv['nombre']) ?></div>
        <div style="font:400 12px var(--body);color:var(--t3);margin-top:2px">
          <?= e($pv['contacto'] ?? $pv['telefono'] ?? $pv['email'] ?? '') ?>
          <?php if ((int)$pv['num_gastos'] > 0): ?>
           · <?= (int)$pv['num_gastos'] ?> gasto<?= $pv['num_gastos'] != 1 ? 's' : '' ?>
          <?php endif; ?>
        </div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="font:600 14px var(--num);color:<?= $pv['total_gastos'] > 0 ? 'var(--danger)' : 'var(--t3)' ?>"><?= $total_fmt ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div><!-- /ctab-panel-proveedores -->
<?php endif; ?>


<!-- SHEET: REGISTRAR / EDITAR COSTO -->
<div class="sh-overlay" id="ov-shCosto" onclick="cerrarSheet('shCosto')"></div>
<div class="bottom-sheet" id="shCosto">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shCostoTitulo">Registrar costo</div>
    <button class="sh-close" onclick="cerrarSheet('shCosto')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shCostoId" value="">
    <?php if ($costos_modo !== 'empresa'): /* Mostrar selector de venta en modo 'venta' y 'ambos' */ ?>
    <div class="sh-field" id="shCostoVentaWrap">
      <div class="sh-lbl">Venta <?= $costos_modo === 'ambos' ? '(dejar vacío para gasto general)' : '<span style="color:var(--danger)">*</span>' ?></div>
      <select class="sh-select" id="shCostoVenta">
        <?php if ($costos_modo === 'ambos'): ?>
        <option value="">— Gasto general (sin venta) —</option>
        <?php else: ?>
        <option value="">Seleccionar venta…</option>
        <?php endif; ?>
        <?php foreach ($ventas_raw as $vc): ?>
        <option value="<?= (int)$vc['id'] ?>"><?= e($vc['numero']) ?> · <?= e(mb_substr($vc['titulo'],0,40)) ?> — <?= e($vc['cliente_nombre']??'') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php else: /* modo empresa: venta_id siempre vacío */ ?>
    <input type="hidden" id="shCostoVenta" value="">
    <?php endif; ?>
    <div class="sh-field">
      <div class="sh-lbl">Categoría <span style="color:var(--danger)">*</span></div>
      <select class="sh-select" id="shCostoCat">
        <option value="">Seleccionar categoría…</option>
        <?php foreach ($cats_activas as $cat): ?>
        <option value="<?= (int)$cat['id'] ?>"><?= e($cat['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if ($es_business && !empty($proveedores_lista)): ?>
    <div class="sh-field">
      <div class="sh-lbl">Proveedor (opcional)</div>
      <select class="sh-select" id="shCostoProv">
        <option value="">Sin proveedor</option>
        <?php foreach ($proveedores_lista as $pv): ?>
        <option value="<?= (int)$pv['id'] ?>"><?= e($pv['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="sh-field">
      <div class="sh-lbl">Concepto <span style="color:var(--danger)">*</span></div>
      <input class="sh-input" type="text" id="shCostoConcepto" placeholder="Ej. Herrajes, Flete materiales…" maxlength="200">
    </div>
    <div class="sh-field sh-row2">
      <div>
        <div class="sh-lbl">Importe <span style="color:var(--danger)">*</span></div>
        <input class="sh-input" type="number" id="shCostoImporte" placeholder="0.00" min="0.01" step="0.01">
      </div>
      <div>
        <div class="sh-lbl">Fecha</div>
        <input class="sh-input" type="date" id="shCostoFecha" value="<?= date('Y-m-d') ?>">
      </div>
    </div>
    <div class="sh-field" style="border-bottom:none">
      <div class="sh-lbl">Nota (opcional)</div>
      <textarea class="sh-input" id="shCostoNota" style="min-height:60px;resize:none" placeholder="Observaciones, número de factura…" maxlength="500"></textarea>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="cerrarSheet('shCosto')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarCosto()">Guardar costo</button>
  </div>
</div>

<!-- SHEET: CATEGORÍA -->
<div class="sh-overlay" id="ov-shCategoria" onclick="cerrarSheet('shCategoria')"></div>
<div class="bottom-sheet" id="shCategoria">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shCatTitulo">Nueva categoría</div>
    <button class="sh-close" onclick="cerrarSheet('shCategoria')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shCatId" value="">
    <div class="sh-field">
      <div class="sh-lbl">Nombre <span style="color:var(--danger)">*</span></div>
      <input class="sh-input" type="text" id="shCatNombre" placeholder="Ej. Subcontrato, Garantía…" maxlength="80">
    </div>
    <div class="sh-field" style="border-bottom:none">
      <div class="sh-lbl">Color</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px" id="colorSwatches">
        <?php
        $colores = ['#3b82f6','#f59e0b','#10b981','#8b5cf6','#ef4444','#06b6d4','#f97316','#84cc16'];
        foreach ($colores as $col):
        ?>
        <div class="color-swatch" style="background:<?= $col ?>"
             data-color="<?= $col ?>" onclick="selectColor(this)"></div>
        <?php endforeach; ?>
      </div>
      <input type="hidden" id="shCatColor" value="#3b82f6">
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="cerrarSheet('shCategoria')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarCategoria()">Guardar</button>
  </div>
</div>

<?php if ($es_business): ?>
<!-- SHEET: PROVEEDOR -->
<div class="sh-overlay" id="ov-shProv" onclick="cerrarSheet('shProv')"></div>
<div class="bottom-sheet" id="shProv">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shProvTitulo">Nuevo proveedor</div>
    <button class="sh-close" onclick="cerrarSheet('shProv')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shProvId" value="">
    <div class="sh-field">
      <div class="sh-lbl">Nombre / empresa <span style="color:var(--danger)">*</span></div>
      <input class="sh-input" type="text" id="shProvNombre" placeholder="Ej. Ferretería López" maxlength="150">
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Persona de contacto</div>
      <input class="sh-input" type="text" id="shProvContacto" placeholder="Nombre del vendedor o contacto" maxlength="150">
    </div>
    <div class="sh-field sh-row2">
      <div>
        <div class="sh-lbl">Teléfono</div>
        <input class="sh-input" type="tel" id="shProvTelefono" placeholder="662 000 0000" style="font-family:var(--num)" maxlength="30">
      </div>
      <div>
        <div class="sh-lbl">Email</div>
        <input class="sh-input" type="email" id="shProvEmail" placeholder="ventas@proveedor.com" maxlength="150">
      </div>
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Dirección</div>
      <input class="sh-input" type="text" id="shProvDireccion" placeholder="Calle, colonia, ciudad…" maxlength="255">
    </div>
    <div class="sh-field" style="border-bottom:none">
      <div class="sh-lbl">Nota (opcional)</div>
      <textarea class="sh-input" id="shProvNota" style="min-height:60px;resize:none" placeholder="Horarios, condiciones de pago, RFC…" maxlength="500"></textarea>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="cerrarSheet('shProv')">Cancelar</button>
    <button class="sh-btn-save" id="btnGuardarProv" onclick="guardarProv()">Guardar</button>
  </div>
</div>
<?php endif; ?>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';

// ── Helper fetch JSON ──────────────────────────────────────
async function api(url, body={}) {
    const r = await fetch(url, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
        body: JSON.stringify(body)
    });
    return r.json();
}

// ── Tabs ──────────────────────────────────────────────────
function cTab(id, el) {
    document.querySelectorAll('.ctab').forEach(t => t.classList.remove('on'));
    el.classList.add('on');
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('on'));
    document.getElementById('ctab-panel-' + id)?.classList.add('on');
}

// ── Sheets ────────────────────────────────────────────────
function abrirSheet(id) {
    document.getElementById('ov-' + id)?.classList.add('open');
    document.getElementById(id)?.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function cerrarSheet(id) {
    document.getElementById('ov-' + id)?.classList.remove('open');
    document.getElementById(id)?.classList.remove('open');
    document.body.style.overflow = '';
}

// ── Costo sheet ───────────────────────────────────────────
const HAS_PROV_IDX = !!document.getElementById('shCostoProv');

function abrirCosto(ventaId) {
    document.getElementById('shCostoId').value = '';
    document.getElementById('shCostoTitulo').textContent = 'Registrar costo';
    document.getElementById('shCostoConcepto').value = '';
    document.getElementById('shCostoImporte').value  = '';
    document.getElementById('shCostoNota').value     = '';
    document.getElementById('shCostoFecha').value    = '<?= date('Y-m-d') ?>';
    document.getElementById('shCostoVenta').value    = ventaId || '';
    document.getElementById('shCostoCat').value      = '';
    if (HAS_PROV_IDX) document.getElementById('shCostoProv').value = '';
    abrirSheet('shCosto');
}
function abrirCostoEditar(costoId, data) {
    document.getElementById('shCostoId').value = costoId;
    document.getElementById('shCostoTitulo').textContent = 'Editar costo';
    document.getElementById('shCostoVenta').value    = data.venta_id;
    document.getElementById('shCostoCat').value      = data.categoria_id;
    document.getElementById('shCostoConcepto').value = data.concepto;
    document.getElementById('shCostoImporte').value  = data.importe;
    document.getElementById('shCostoFecha').value    = data.fecha;
    document.getElementById('shCostoNota').value     = data.nota || '';
    abrirSheet('shCosto');
}
async function guardarCosto() {
    const id       = document.getElementById('shCostoId').value;
    const venta_id = document.getElementById('shCostoVenta').value;
    const cat_id   = document.getElementById('shCostoCat').value;
    const concepto = document.getElementById('shCostoConcepto').value.trim();
    const importe  = parseFloat(document.getElementById('shCostoImporte').value);
    const fecha    = document.getElementById('shCostoFecha').value;
    const nota     = document.getElementById('shCostoNota').value.trim();
    const COSTOS_MODO = '<?= $costos_modo ?>';
    const venta_requerida = (COSTOS_MODO === 'venta');
    if ((venta_requerida && !venta_id) || !cat_id || !concepto || !importe || importe <= 0) {
        alert('Completa los campos obligatorios.'); return;
    }
    const url = id ? '/costos/gasto/'+id : '/costos/gasto';
    const prov_id = HAS_PROV_IDX ? parseInt(document.getElementById('shCostoProv')?.value) || 0 : 0;
    const d = await api(url, {venta_id:+venta_id, categoria_id:+cat_id, proveedor_id:prov_id, concepto, importe, fecha, nota});
    if (d.ok) { cerrarSheet('shCosto'); location.reload(); }
    else alert(d.error || 'Error al guardar.');
}
async function eliminarCosto(id, el) {
    if (!confirm('¿Eliminar este costo?')) return;
    const d = await api('/costos/gasto/'+id+'/eliminar');
    if (d.ok) el.closest('.cost-row')?.remove();
    else alert(d.error || 'Error.');
}

// ── Categoría sheet ───────────────────────────────────────
function abrirCategoria(id, data) {
    document.getElementById('shCatId').value    = id || '';
    document.getElementById('shCatTitulo').textContent = id ? 'Editar categoría' : 'Nueva categoría';
    document.getElementById('shCatNombre').value = data?.nombre || '';
    const color = data?.color || '#3b82f6';
    document.getElementById('shCatColor').value = color;
    document.querySelectorAll('.color-swatch').forEach(s => {
        s.classList.toggle('sel', s.dataset.color === color);
    });
    abrirSheet('shCategoria');
}
function selectColor(el) {
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('shCatColor').value = el.dataset.color;
}
async function guardarCategoria() {
    const id     = document.getElementById('shCatId').value;
    const nombre = document.getElementById('shCatNombre').value.trim();
    const color  = document.getElementById('shCatColor').value;
    if (!nombre) { alert('El nombre es obligatorio.'); return; }
    const url = id ? '/costos/categoria/'+id : '/costos/categoria';
    const d = await api(url, {nombre, color});
    if (d.ok) { cerrarSheet('shCategoria'); location.reload(); }
    else alert(d.error || 'Error al guardar.');
}
async function toggleCategoria(id, activa) {
    await api('/costos/categoria/'+id+'/toggle', {activa});
}

// ── Búsqueda ──────────────────────────────────────────────
let _dbt;
function buscar(q) {
    clearTimeout(_dbt);
    _dbt = setTimeout(() => {
        const u = new URL(location.href);
        u.searchParams.set('q', q);
        location.href = u.toString();
    }, 320);
}

// ── Gastos generales ─────────────────────────────────────
function abrirGastoGeneral() {
    document.getElementById('shCostoId').value = '';
    document.getElementById('shCostoTitulo').textContent = 'Gasto general';
    document.getElementById('shCostoConcepto').value = '';
    document.getElementById('shCostoImporte').value  = '';
    document.getElementById('shCostoNota').value     = '';
    document.getElementById('shCostoFecha').value    = '<?= date('Y-m-d') ?>';
    document.getElementById('shCostoVenta').value    = '';  // sin venta
    document.getElementById('shCostoCat').value      = '';
    if (HAS_PROV_IDX) document.getElementById('shCostoProv').value = '';
    abrirSheet('shCosto');
}
function editarGastoGeneral(id, data) {
    document.getElementById('shCostoId').value = id;
    document.getElementById('shCostoTitulo').textContent = 'Editar gasto general';
    document.getElementById('shCostoVenta').value    = '';
    document.getElementById('shCostoCat').value      = data.categoria_id;
    if (HAS_PROV_IDX) document.getElementById('shCostoProv').value = data.proveedor_id || '';
    document.getElementById('shCostoConcepto').value = data.concepto;
    document.getElementById('shCostoImporte').value  = data.importe;
    document.getElementById('shCostoFecha').value    = data.fecha;
    document.getElementById('shCostoNota').value     = data.nota || '';
    abrirSheet('shCosto');
}
async function eliminarGastoGeneral(id, el) {
    if (!confirm('¿Eliminar este gasto?')) return;
    const d = await api('/costos/gasto/'+id+'/eliminar');
    if (d.ok) location.reload();
    else alert(d.error || 'Error.');
}

// ── Proveedor sheet ──────────────────────────────────────
function abrirProv(data) {
    document.getElementById('shProvId').value        = data?.id        ?? '';
    document.getElementById('shProvNombre').value    = data?.nombre    ?? '';
    document.getElementById('shProvContacto').value  = data?.contacto  ?? '';
    document.getElementById('shProvTelefono').value  = data?.telefono  ?? '';
    document.getElementById('shProvEmail').value     = data?.email     ?? '';
    document.getElementById('shProvDireccion').value = data?.direccion ?? '';
    document.getElementById('shProvNota').value      = data?.nota      ?? '';
    document.getElementById('shProvTitulo').textContent = data?.id ? 'Editar proveedor' : 'Nuevo proveedor';
    abrirSheet('shProv');
    setTimeout(() => document.getElementById('shProvNombre').focus(), 100);
}
async function guardarProv() {
    const id        = document.getElementById('shProvId').value;
    const nombre    = document.getElementById('shProvNombre').value.trim();
    const contacto  = document.getElementById('shProvContacto').value.trim();
    const telefono  = document.getElementById('shProvTelefono').value.trim();
    const email     = document.getElementById('shProvEmail').value.trim();
    const direccion = document.getElementById('shProvDireccion').value.trim();
    const nota      = document.getElementById('shProvNota').value.trim();

    if (!nombre) { alert('El nombre es requerido'); return; }

    const btn = document.getElementById('btnGuardarProv');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const url = id ? '/proveedores/' + id : '/proveedores';
        const d = await api(url, { nombre, contacto, telefono, email, direccion, nota });
        if (!d.ok) { alert(d.error || 'Error al guardar'); btn.disabled=false; btn.textContent='Guardar'; return; }
        cerrarSheet('shProv');
        location.reload();
    } catch(e) {
        alert('Error de conexión');
        btn.disabled=false; btn.textContent='Guardar';
    }
}

// ── Responsive ───────────────────────────────────────────
function applyDesktop() {
    const d = window.innerWidth >= 641;
    document.querySelectorAll('.vr-col-cliente,.vr-col-venta,.vr-col-costos,.vr-col-margen,.vr-col-accion').forEach(el => {
        el.style.display = d ? '' : 'none';
    });
    document.querySelectorAll('.vr-right,.vr-meta,.margen-bar-wrap').forEach(el => {
        el.style.display = d ? 'none' : '';
    });
}
applyDesktop();
window.addEventListener('resize', applyDesktop);
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
