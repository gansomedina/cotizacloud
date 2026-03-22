<?php
// ============================================================
//  CotizaApp — modules/costos/index.php
//  GET /costos
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$es_admin   = Auth::es_admin();
$solo_mias  = !$es_admin && !Auth::puede('ver_todas_ventas');
$uid        = Auth::id();
$v_where    = $solo_mias ? "AND (v.usuario_id = $uid OR v.vendedor_id = $uid)" : '';

// ─── Período ────────────────────────────────────────────────
$periodo = $_GET['periodo'] ?? 'todas';
$periodos_validos = ['todas','este_mes','mes_ant','90_dias'];
if (!in_array($periodo, $periodos_validos)) $periodo = 'todas';

$fecha_desde = null;
$fecha_hasta = null;
$ahora = new DateTimeImmutable('now', new DateTimeZone('America/Hermosillo'));

switch ($periodo) {
    case 'este_mes':
        $fecha_desde = $ahora->format('Y-m-01 00:00:00');
        $fecha_hasta = $ahora->format('Y-m-t 23:59:59');
        break;
    case 'mes_ant':
        $ant = $ahora->modify('first day of last month');
        $fecha_desde = $ant->format('Y-m-01 00:00:00');
        $fecha_hasta = $ant->format('Y-m-t 23:59:59');
        break;
    case '90_dias':
        $fecha_desde = $ahora->modify('-90 days')->format('Y-m-d 00:00:00');
        $fecha_hasta = $ahora->format('Y-m-d 23:59:59');
        break;
}

$fecha_where = '';
if ($fecha_desde) {
    $fecha_where = "AND v.created_at BETWEEN " . DB::quote($fecha_desde) . " AND " . DB::quote($fecha_hasta);
}

// ─── Búsqueda ────────────────────────────────────────────────
$q = trim($_GET['q'] ?? '');
$q_where = '';
$q_bind  = [];
if ($q !== '') {
    $q_like   = '%' . $q . '%';
    $q_where  = "AND (v.titulo LIKE ? OR v.numero LIKE ? OR cl.nombre LIKE ?)";
    $q_bind   = [$q_like, $q_like, $q_like];
}

// ─── Filtro margen ────────────────────────────────────────────
$margen_f = $_GET['margen'] ?? 'todas';
$margen_where = match($margen_f) {
    'bien'    => 'HAVING margen_pct >= 30 OR total_costos = 0',
    'medio'   => 'HAVING margen_pct >= 15 AND margen_pct < 30',
    'mal'     => 'HAVING margen_pct < 15 AND total_costos > 0',
    'sin_costo'=> 'HAVING total_costos = 0',
    default   => '',
};

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
$bind_ventas = array_merge([$empresa_id], $q_bind);
$ventas_raw  = DB::query(
    "SELECT v.id, v.numero, v.titulo, v.total, v.estado, v.created_at,
            cl.nombre AS cliente_nombre, cl.telefono AS cli_tel,
            COALESCE(SUM(gv.importe), 0) AS total_costos
     FROM ventas v
     LEFT JOIN clientes cl   ON cl.id = v.cliente_id
     LEFT JOIN gastos_venta gv ON gv.venta_id = v.id AND gv.empresa_id = v.empresa_id
     WHERE v.empresa_id = ? AND v.estado != 'cancelada'
       $v_where $fecha_where $q_where
     GROUP BY v.id
     $margen_where
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

// ─── KPIs globales ───────────────────────────────────────────
$kpi_total_ventas = array_sum(array_column($ventas_raw, 'total'));
$kpi_total_costos = array_sum(array_column($ventas_raw, 'total_costos'));
$kpi_utilidad     = $kpi_total_ventas - $kpi_total_costos;
$kpi_margen_prom  = $kpi_total_ventas > 0
    ? round((($kpi_total_ventas - $kpi_total_costos) / $kpi_total_ventas) * 100, 1)
    : 0;

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
/* tabs */
.ctabs-wrap{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:54px;z-index:90;overflow-x:auto;scrollbar-width:none;margin:-16px -0px 16px}
.ctabs-wrap::-webkit-scrollbar{display:none}
.ctabs-inner{display:flex;padding:0;max-width:var(--max);margin:0 auto}
.ctab{padding:13px 18px;font:600 13px var(--body);color:var(--t3);cursor:pointer;border-bottom:2.5px solid transparent;white-space:nowrap;transition:all .15s;background:none;border-top:none;border-left:none;border-right:none}
.ctab.on{color:var(--g);border-bottom-color:var(--g)}
.ctab:hover:not(.on){color:var(--t2)}
.tab-panel{display:none}.tab-panel.on{display:block}

/* header acciones */
.c-page-hdr{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px}
.c-page-hdr h1{font:800 22px var(--body);letter-spacing:-.02em}
.new-btn{display:flex;align-items:center;gap:6px;padding:9px 16px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 13px var(--body);color:#fff;cursor:pointer;transition:opacity .15s}
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
.venta-row{display:flex;flex-direction:column;padding:12px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;text-decoration:none;color:inherit}
.venta-row:last-child{border-bottom:none}
.venta-row:hover{background:#fafaf8}
.vr-top{display:flex;align-items:flex-start;gap:10px}
.vr-av{width:36px;height:36px;border-radius:10px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 13px var(--body);color:#fff;flex-shrink:0}
.vr-info{flex:1;min-width:0}
.vr-folio{font:500 11px var(--num);color:var(--t3);margin-bottom:2px}
.vr-titulo{font:600 14px var(--body);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vr-cliente{font:400 12px var(--body);color:var(--t3);margin-top:1px}
.vr-meta{display:flex;justify-content:space-between;align-items:center;margin-top:8px}
.vr-costos-lbl{font:500 12px var(--body);color:var(--t3)}
.vr-costos-val{font:600 14px var(--num);color:var(--danger)}
.vr-right{text-align:right;flex-shrink:0}
.vr-venta{font:600 14px var(--num);color:var(--text)}
.vr-margen{font:500 11px var(--num);margin-top:3px}
.margen-ok{color:var(--g)}.margen-med{color:#b45309}.margen-mal{color:var(--danger)}
.margen-bar-wrap{margin-top:8px}
.margen-bar{height:4px;border-radius:2px;background:var(--border);overflow:hidden}
.margen-fill{height:100%;border-radius:2px}
.fill-ok{background:var(--g)}.fill-med{background:#f59e0b}.fill-mal{background:var(--danger)}

/* DESKTOP tabla */
@media(min-width:641px){
  .tbl-header{display:grid;grid-template-columns:minmax(0,2.2fr) 150px 110px 110px 110px 90px;align-items:center;padding:8px 16px;border-bottom:2px solid var(--border);background:var(--bg)}
  .tbl-header span{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3)}
  .venta-row{display:grid;grid-template-columns:minmax(0,2.2fr) 150px 110px 110px 110px 90px;align-items:center;padding:11px 16px;flex-direction:unset}
  .vr-top{align-items:center}
  .vr-meta,.margen-bar-wrap{display:none}
  .vr-col-cliente{min-width:0;padding-right:12px}
  .vr-col-venta,.vr-col-costos{font:600 14px var(--num);white-space:nowrap}
  .vr-col-venta{color:var(--text)}.vr-col-costos{color:var(--danger)}
  .vr-col-margen .pct{font:700 14px var(--num)}
  .vr-col-margen .pct.ok{color:var(--g)}.vr-col-margen .pct.med{color:#b45309}.vr-col-margen .pct.mal{color:var(--danger)}
  .vr-col-margen .bar{height:4px;border-radius:2px;background:var(--border);overflow:hidden;margin-top:4px;width:80px}
  .vr-col-margen .bar-fill{height:100%;border-radius:2px}
  .vr-col-accion{display:flex;justify-content:flex-end}
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
</style>

<!-- Tabs -->
<div class="ctabs-wrap">
  <div class="ctabs-inner">
    <button class="ctab on" id="ctab-ventas"      onclick="cTab('ventas',this)">Costos por venta</button>
    <button class="ctab"    id="ctab-categorias"  onclick="cTab('categorias',this)">Categorías</button>
  </div>
</div>

<!-- ══ TAB: COSTOS POR VENTA ══ -->
<div class="tab-panel on" id="ctab-panel-ventas">

  <div class="c-page-hdr">
    <div>
      <h1>Costos</h1>
      <p style="font:400 13px var(--body);color:var(--t3);margin-top:2px"><?= count($ventas_raw) ?> venta<?= count($ventas_raw)!=1?'s':'' ?></p>
    </div>
    <button class="new-btn" onclick="openSheet('shCosto', null)">+ Registrar costo</button>
  </div>

  <!-- KPIs -->
  <div class="kpi-row">
    <div class="kpi-card">
      <div class="kpi-lbl">Total en ventas</div>
      <div class="kpi-val"><?= fmt_c_short($kpi_total_ventas) ?></div>
      <div class="kpi-sub"><?= count($ventas_raw) ?> ventas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-lbl">Total costos</div>
      <div class="kpi-val red"><?= fmt_c_short($kpi_total_costos) ?></div>
      <div class="kpi-sub"><?= $kpi_total_costos > 0 ? round($kpi_total_costos/$kpi_total_ventas*100,1).'% del total' : 'Sin costos aún' ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-lbl">Margen promedio</div>
      <div class="kpi-val <?= $kpi_margen_prom >= 30 ? 'green' : ($kpi_margen_prom >= 15 ? 'amber' : 'red') ?>"><?= $kpi_total_costos > 0 ? $kpi_margen_prom.'%' : '—' ?></div>
      <div class="kpi-sub">Utilidad <?= fmt_c_short($kpi_utilidad) ?></div>
    </div>
  </div>

  <!-- Filtros -->
  <form method="get" id="frmFiltros" style="display:contents">
    <input type="hidden" name="periodo" value="<?= e($periodo) ?>">
    <div class="filter-bar">
      <?php
      $chips = ['todas'=>'Todas','este_mes'=>'Este mes','mes_ant'=>'Mes anterior','90_dias'=>'90 días'];
      foreach ($chips as $ck => $cl):
      ?>
      <button type="button" class="chip <?= $periodo===$ck?'on':'' ?>"
              onclick="setFiltro('periodo','<?= $ck ?>')"><?= $cl ?></button>
      <?php endforeach; ?>
      <span style="margin-left:4px;color:var(--border2)">|</span>
      <?php
      $mchips = ['todas'=>'Todos','bien'=>'Margen ≥30%','medio'=>'Margen 15–30%','mal'=>'Margen <15%','sin_costo'=>'Sin costos'];
      foreach ($mchips as $mk => $ml):
      ?>
      <button type="button" class="chip <?= $margen_f===$mk?'on':'' ?>"
              onclick="setFiltro('margen','<?= $mk ?>')"><?= $ml ?></button>
      <?php endforeach; ?>
    </div>
    <input type="hidden" name="margen" id="inp_margen" value="<?= e($margen_f) ?>">
  </form>

  <!-- Búsqueda -->
  <div class="search-wrap">
    <span class="search-ico">🔍</span>
    <input type="text" id="busqueda" placeholder="Buscar venta o cliente…"
           value="<?= e($q) ?>"
           oninput="debounce(()=>buscar(this.value), 280)">
  </div>

  <!-- Lista -->
  <?php if (empty($ventas_raw)): ?>
  <div class="list-card">
    <div class="empty-state">
      <div class="empty-ico">📊</div>
      <div class="empty-tit">Sin ventas que mostrar</div>
      <div class="empty-sub">Cuando conviertas cotizaciones en ventas, podrás registrar sus costos aquí.</div>
    </div>
  </div>
  <?php else: ?>
  <div class="list-card">
    <div class="tbl-header">
      <span>Proyecto / Cliente</span>
      <span>Cliente</span>
      <span style="text-align:right">Venta</span>
      <span style="text-align:right">Costos</span>
      <span style="text-align:right">Margen</span>
      <span></span>
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
      <div class="vr-col-cliente" style="display:none">
        <div style="font:500 13px var(--body);color:var(--t2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($v['cliente_nombre'] ?? '—') ?></div>
        <div style="font:400 11px var(--num);color:var(--t3);margin-top:2px"><?= e($v['cli_tel'] ?? '') ?></div>
      </div>
      <div class="vr-col-venta" style="display:none;text-align:right"><?= fmt_c((float)$v['total']) ?></div>
      <div class="vr-col-costos" style="display:none;text-align:right;font:600 14px var(--num);color:<?= (float)$v['total_costos']>0?'var(--danger)':'var(--t3)' ?>">
        <?= (float)$v['total_costos'] > 0 ? fmt_c((float)$v['total_costos']) : '$0' ?>
      </div>
      <div class="vr-col-margen" style="display:none;text-align:right">
        <?php if ($pct !== null): ?>
        <div class="pct <?= $nivel ?>"><?= $pct ?>%</div>
        <div class="bar"><div class="bar-fill <?= $fill ?>" style="width:<?= min(100,(int)$pct) ?>%"></div></div>
        <?php else: ?>
        <div class="pct" style="color:var(--t3)">—</div>
        <?php endif; ?>
      </div>
      <div class="vr-col-accion" style="display:none">
        <button onclick="event.stopPropagation();openSheet('shCosto', <?= (int)$v['id'] ?>)"
                class="new-btn" style="<?= (float)$v['total_costos']==0?'':'background:var(--white);color:var(--t2);border:1px solid var(--border)' ?>;padding:6px 12px;font-size:12px;box-shadow:var(--sh)">
          + Costo
        </button>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div><!-- /ctab-panel-ventas -->

<!-- ══ TAB: CATEGORÍAS ══ -->
<div class="tab-panel" id="ctab-panel-categorias">

  <div class="c-page-hdr">
    <div>
      <h1>Categorías</h1>
      <p style="font:400 13px var(--body);color:var(--t3);margin-top:2px">Las activas aparecen al registrar un costo</p>
    </div>
  </div>

  <?php if (empty($categorias)): ?>
  <div class="list-card">
    <div class="empty-state">
      <div class="empty-ico">🏷️</div>
      <div class="empty-tit">Sin categorías aún</div>
      <div class="empty-sub">Crea categorías para organizar tus costos por tipo.</div>
    </div>
  </div>
  <?php else: ?>
  <div class="list-card" id="catList">
    <?php foreach ($categorias as $cat): ?>
    <div class="cat-row" data-cat-id="<?= (int)$cat['id'] ?>">
      <div class="cat-dot" style="background:<?= e(color_hex($cat['color'] ?? '#94a3b8')) ?>"></div>
      <div class="cat-nombre" style="<?= !$cat['activa']?'color:var(--t3)':'' ?>"><?= e($cat['nombre']) ?></div>
      <div class="cat-count"><?= (int)$cat['num_costos'] ?> costo<?= $cat['num_costos']!=1?'s':'' ?></div>
      <label class="toggle" title="Activar/Desactivar">
        <input type="checkbox" <?= $cat['activa']?'checked':'' ?>
               onchange="toggleCategoria(<?= (int)$cat['id'] ?>, this.checked)">
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
      <button class="cat-edit-btn" onclick="editarCategoria(<?= (int)$cat['id'] ?>, <?= htmlspecialchars(json_encode(['nombre'=>$cat['nombre'],'color'=>$cat['color']??'#3b82f6']), ENT_QUOTES) ?>)">✎</button>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <button class="add-row-btn" onclick="openSheetCategoria(null)">+ Nueva categoría</button>

</div><!-- /ctab-panel-categorias -->


<!-- SHEET: REGISTRAR / EDITAR COSTO -->
<div class="sh-overlay" id="ov-shCosto" onclick="closeSheet('shCosto')"></div>
<div class="bottom-sheet" id="shCosto">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shCostoTitulo">Registrar costo</div>
    <button class="sh-close" onclick="closeSheet('shCosto')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shCostoId" value="">
    <div class="sh-field">
      <div class="sh-lbl">Venta <span style="color:var(--danger)">*</span></div>
      <select class="sh-select" id="shCostoVenta">
        <option value="">Seleccionar venta…</option>
        <?php foreach ($ventas_raw as $vc): ?>
        <option value="<?= (int)$vc['id'] ?>"><?= e($vc['numero']) ?> · <?= e(mb_substr($vc['titulo'],0,40)) ?> — <?= e($vc['cliente_nombre']??'') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Categoría <span style="color:var(--danger)">*</span></div>
      <select class="sh-select" id="shCostoCat">
        <option value="">Seleccionar categoría…</option>
        <?php foreach ($cats_activas as $cat): ?>
        <option value="<?= (int)$cat['id'] ?>"><?= e($cat['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
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
    <button class="sh-btn-cancel" onclick="closeSheet('shCosto')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarCosto()">Guardar costo</button>
  </div>
</div>

<!-- SHEET: CATEGORÍA -->
<div class="sh-overlay" id="ov-shCategoria" onclick="closeSheet('shCategoria')"></div>
<div class="bottom-sheet" id="shCategoria">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shCatTitulo">Nueva categoría</div>
    <button class="sh-close" onclick="closeSheet('shCategoria')">✕</button>
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
    <button class="sh-btn-cancel" onclick="closeSheet('shCategoria')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarCategoria()">Guardar</button>
  </div>
</div>

<script>
// ── Tabs ────────────────────────────────────────────────────
function cTab(id, el) {
    document.querySelectorAll('.ctab').forEach(t => t.classList.remove('on'));
    el.classList.add('on');
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('on'));
    document.getElementById('ctab-panel-' + id)?.classList.add('on');
}

// ── Sheets ─────────────────────────────────────────────────
function openSheet(id) {
    document.getElementById('ov-' + id).classList.add('open');
    document.getElementById(id).classList.add('open');
}
function closeSheet(id) {
    document.getElementById('ov-' + id).classList.remove('open');
    document.getElementById(id).classList.remove('open');
}

// ── Sheet costo ─────────────────────────────────────────────
function openSheet(id, ventaId) {
    if (id === 'shCosto') {
        document.getElementById('shCostoId').value    = '';
        document.getElementById('shCostoTitulo').textContent = 'Registrar costo';
        document.getElementById('shCostoConcepto').value = '';
        document.getElementById('shCostoImporte').value  = '';
        document.getElementById('shCostoNota').value     = '';
        document.getElementById('shCostoFecha').value    = '<?= date('Y-m-d') ?>';
        if (ventaId) document.getElementById('shCostoVenta').value = ventaId;
        else document.getElementById('shCostoVenta').value = '';
        document.getElementById('shCostoCat').value = '';
    }
    document.getElementById('ov-' + id).classList.add('open');
    document.getElementById(id).classList.add('open');
}
function openSheetEdit(costoId, data) {
    document.getElementById('shCostoId').value        = costoId;
    document.getElementById('shCostoTitulo').textContent = 'Editar costo';
    document.getElementById('shCostoVenta').value     = data.venta_id;
    document.getElementById('shCostoCat').value       = data.categoria_id;
    document.getElementById('shCostoConcepto').value  = data.concepto;
    document.getElementById('shCostoImporte').value   = data.importe;
    document.getElementById('shCostoFecha').value     = data.fecha;
    document.getElementById('shCostoNota').value      = data.nota || '';
    document.getElementById('ov-shCosto').classList.add('open');
    document.getElementById('shCosto').classList.add('open');
}
function closeSheet(id) {
    document.getElementById('ov-' + id)?.classList.remove('open');
    document.getElementById(id)?.classList.remove('open');
}

// ── Guardar costo ───────────────────────────────────────────
async function guardarCosto() {
    const id       = document.getElementById('shCostoId').value;
    const venta_id = document.getElementById('shCostoVenta').value;
    const cat_id   = document.getElementById('shCostoCat').value;
    const concepto = document.getElementById('shCostoConcepto').value.trim();
    const importe  = parseFloat(document.getElementById('shCostoImporte').value);
    const fecha    = document.getElementById('shCostoFecha').value;
    const nota     = document.getElementById('shCostoNota').value.trim();

    if (!venta_id || !cat_id || !concepto || !importe || importe <= 0) {
        alert('Completa los campos obligatorios.');
        return;
    }

    const url = id ? '/costos/gasto/' + id : '/costos/gasto';
    try {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ venta_id: parseInt(venta_id), categoria_id: parseInt(cat_id), concepto, importe, fecha, nota })
        });
        const d = await r.json();
        if (d.ok) { closeSheet('shCosto'); location.reload(); }
        else alert(d.error || 'Error al guardar.');
    } catch { alert('Error de conexión.'); }
}

// ── Eliminar costo ──────────────────────────────────────────
async function eliminarCosto(id, el) {
    if (!confirm('¿Eliminar este costo?')) return;
    try {
        const r = await fetch('/costos/gasto/' + id + '/eliminar', { method: 'POST' });
        const d = await r.json();
        if (d.ok) el.closest('.cost-row')?.remove();
        else alert(d.error || 'Error al eliminar.');
    } catch { alert('Error de conexión.'); }
}

// ── Categorías ──────────────────────────────────────────────
function openSheetCategoria(id) {
    document.getElementById('shCatId').value = id || '';
    document.getElementById('shCatTitulo').textContent = id ? 'Editar categoría' : 'Nueva categoría';
    document.getElementById('shCatNombre').value = '';
    document.getElementById('shCatColor').value  = '#3b82f6';
    document.querySelectorAll('.color-swatch').forEach(s => {
        s.classList.remove('sel');
        if (s.dataset.color === '#3b82f6') s.classList.add('sel');
    });
    openSheet('shCategoria');
}
function editarCategoria(id, data) {
    document.getElementById('shCatId').value    = id;
    document.getElementById('shCatTitulo').textContent = 'Editar categoría';
    document.getElementById('shCatNombre').value = data.nombre;
    document.getElementById('shCatColor').value  = data.color;
    document.querySelectorAll('.color-swatch').forEach(s => {
        s.classList.toggle('sel', s.dataset.color === data.color);
    });
    document.getElementById('ov-shCategoria').classList.add('open');
    document.getElementById('shCategoria').classList.add('open');
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
    const url = id ? '/costos/categoria/' + id : '/costos/categoria';
    try {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, color })
        });
        const d = await r.json();
        if (d.ok) { closeSheet('shCategoria'); location.reload(); }
        else alert(d.error || 'Error al guardar.');
    } catch { alert('Error de conexión.'); }
}
async function toggleCategoria(id, activa) {
    try {
        await fetch('/costos/categoria/' + id + '/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ activa })
        });
    } catch { /* silencioso */ }
}

// ── Filtros ─────────────────────────────────────────────────
function setFiltro(nombre, valor) {
    document.getElementById('inp_' + nombre).value = valor;
    document.getElementById('frmFiltros').submit();
}

// ── Búsqueda ─────────────────────────────────────────────────
let debounceTimer;
function debounce(fn, ms) { clearTimeout(debounceTimer); debounceTimer = setTimeout(fn, ms); }
function buscar(q) {
    const url = new URL(window.location.href);
    url.searchParams.set('q', q);
    url.searchParams.set('periodo', '<?= e($periodo) ?>');
    url.searchParams.set('margen',  '<?= e($margen_f) ?>');
    window.location.href = url.toString();
}

// ── Desktop cols ─────────────────────────────────────────────
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
