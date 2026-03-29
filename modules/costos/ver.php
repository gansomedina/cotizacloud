<?php
// ============================================================
//  CotizaApp — modules/costos/ver.php
//  GET /costos/:id
// ============================================================

defined('COTIZAAPP') or die;

// Permiso de módulo
if (!Auth::es_admin() && !Auth::puede('ver_costos')) { redirect('/dashboard'); }

$venta_id   = (int)($id ?? 0);
$empresa_id = EMPRESA_ID;

$venta = DB::row(
    "SELECT v.*, cl.nombre AS cliente_nombre, cl.telefono AS cli_tel,
            u.nombre AS asesor_nombre
     FROM ventas v
     LEFT JOIN clientes cl ON cl.id = v.cliente_id
     LEFT JOIN usuarios  u  ON u.id = v.usuario_id
     WHERE v.id = ? AND v.empresa_id = ?",
    [$venta_id, $empresa_id]
);
if (!$venta) { redirect('/costos'); }

// Permiso: el asesor solo ve las suyas
if (!Auth::es_admin() && !Auth::puede('ver_todas_ventas')) {
    if ((int)$venta['usuario_id'] !== Auth::id()) redirect('/costos');
}

// ─── Categorías ──────────────────────────────────────────────
$categorias = DB::query(
    "SELECT id, nombre, color FROM categorias_costos WHERE empresa_id=? AND activa=1 ORDER BY nombre",
    [$empresa_id]
);
$cats_map = array_column($categorias, null, 'id');

// ─── Proveedores (Business) ─────────────────────────────────
$plan_info = trial_info($empresa_id);
$proveedores = [];
if ($plan_info['es_business']) {
    $prov_tabla = DB::val("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='proveedores' LIMIT 1");
    if ($prov_tabla) {
        $proveedores = DB::query(
            "SELECT id, nombre FROM proveedores WHERE empresa_id=? AND activo=1 ORDER BY nombre",
            [$empresa_id]
        );
    }
}

// ─── Gastos de esta venta ─────────────────────────────────────
$gastos = DB::query(
    "SELECT gv.*, cc.nombre AS cat_nombre, cc.color AS cat_color
     FROM gastos_venta gv
     LEFT JOIN categorias_costos cc ON cc.id = gv.categoria_id
     WHERE gv.venta_id = ? AND gv.empresa_id = ?
     ORDER BY gv.fecha DESC, gv.id DESC",
    [$venta_id, $empresa_id]
);

$total_costos = array_sum(array_column($gastos, 'importe'));
$total_venta  = (float)$venta['total'];
$utilidad     = $total_venta - $total_costos;
$margen_pct   = ($total_venta > 0 && $total_costos > 0)
    ? round(($utilidad / $total_venta) * 100, 1)
    : null;
$margen_nivel = match(true) {
    $margen_pct === null                 => 'sin_costo',
    $margen_pct >= 30                    => 'ok',
    $margen_pct >= 15                    => 'med',
    default                              => 'mal',
};

// Desglose por categoría
$por_cat = [];
foreach ($gastos as $g) {
    $cid = (int)$g['categoria_id'];
    if (!isset($por_cat[$cid])) {
        $por_cat[$cid] = ['nombre' => $g['cat_nombre'] ?? '—', 'color' => $g['cat_color'] ?? '#94a3b8', 'total' => 0, 'count' => 0];
    }
    $por_cat[$cid]['total'] += (float)$g['importe'];
    $por_cat[$cid]['count']++;
}
arsort($por_cat);

// Helpers
function fmt_v(float $n): string { return '$' . number_format($n, 0, '.', ','); }
function fmt_v2(float $n): string {
    if ($n >= 1_000_000) return '$' . number_format($n/1_000_000,1) . 'M';
    if ($n >= 1_000)     return '$' . number_format($n/1_000,0) . 'K';
    return '$' . number_format($n,0);
}
function ini_v(string $n): string {
    $p = array_filter(explode(' ', $n));
    $i = ''; foreach (array_slice($p,0,2) as $w) $i .= strtoupper($w[0]);
    return $i ?: '?';
}
function color_hex_v(string $c): string {
    return preg_match('/^#[0-9a-f]{3,6}$/i',$c) ? $c : '#94a3b8';
}

$ini = ini_v($venta['cliente_nombre'] ?? '?');
$fill_cls = match($margen_nivel) { 'ok'=>'fill-ok','med'=>'fill-med','mal'=>'fill-mal', default=>'' };
$margen_color = match($margen_nivel) { 'ok'=>'var(--g)','med'=>'#b45309','mal'=>'var(--danger)', default=>'var(--t3)' };

$page_title = 'Costos · ' . ($venta['numero'] ?? '');
ob_start();
?>
<style>
/* layout */
.detail-layout{display:flex;gap:20px;align-items:flex-start}
.col-main{flex:1;min-width:0}
.col-side{width:272px;flex-shrink:0;display:flex;flex-direction:column;gap:12px;position:sticky;top:80px}

.slabel{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t2);margin:20px 0 10px;display:flex;align-items:center;gap:10px}
.slabel::after{content:'';flex:1;height:1.5px;background:var(--border)}
.slabel:first-child{margin-top:0}
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}

/* header venta */
.det-header{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;box-shadow:var(--sh);margin-bottom:4px}
.det-folio{font:500 11px var(--num);color:var(--t3);margin-bottom:3px}
.det-titulo{font:700 18px var(--body);letter-spacing:-.01em}
.det-cliente{display:flex;align-items:center;gap:8px;margin-top:8px}
.det-av{width:26px;height:26px;border-radius:6px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 10px var(--body);color:#fff;flex-shrink:0}
.det-cli-name{font:500 13px var(--body);color:var(--t2)}
.det-meta{display:flex;gap:20px;margin-top:10px;flex-wrap:wrap}
.det-meta-item{display:flex;flex-direction:column;gap:2px}
.det-meta-lbl{font:700 9px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3)}
.det-meta-val{font:600 14px var(--num)}

/* tabla costos */
.cost-tbl-header{display:none}
.cost-row{display:flex;align-items:center;gap:10px;padding:11px 14px;border-bottom:1px solid var(--border);transition:background .12s}
.cost-row:last-child{border-bottom:none}
.cost-row:hover{background:#fafaf8}
.cost-cat-dot{width:8px;height:8px;border-radius:4px;flex-shrink:0}
.cost-info{flex:1;min-width:0}
.cost-titulo{font:600 13px var(--body)}
.cost-meta{font:400 11px var(--num);color:var(--t3);margin-top:2px}
.cost-monto{font:600 14px var(--num);color:var(--danger);flex-shrink:0;white-space:nowrap}
.cost-actions{display:flex;gap:4px;flex-shrink:0}
.cost-btn{width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;color:var(--t3);transition:all .12s}
.cost-btn.del:hover{border-color:var(--danger);color:var(--danger);background:var(--danger-bg)}
.cost-btn.edi:hover{border-color:var(--g);color:var(--g)}
.cost-add-btn{width:100%;padding:12px;border:none;background:var(--bg);font:600 13px var(--body);color:var(--t2);cursor:pointer;transition:all .12s;display:flex;align-items:center;justify-content:center;gap:6px}
.cost-add-btn:hover{background:var(--g-bg);color:var(--g)}

@media(min-width:641px){
  .cost-tbl-header{display:grid;grid-template-columns:10px minmax(0,2fr) 130px 100px 90px 70px;align-items:center;padding:7px 14px;border-bottom:2px solid var(--border);background:var(--bg)}
  .cost-tbl-header span{font:700 11px var(--body);letter-spacing:.06em;text-transform:uppercase;color:var(--t3)}
  .cost-row{display:grid;grid-template-columns:10px minmax(0,2fr) 130px 100px 90px 70px;align-items:center}
  .cost-col-cat,.cost-col-fecha{display:block !important}
  .cost-info{padding-right:12px}
}

/* sidebar financiero */
.fin-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}
.fin-row{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid var(--border)}
.fin-row:last-child{border-bottom:none}
.fin-lbl{font:400 13px var(--body);color:var(--t2)}
.fin-val{font:500 14px var(--num);color:var(--text)}
.fin-row.destacado .fin-lbl{font:700 14px var(--body);color:var(--text)}
.fin-row.destacado .fin-val{font:700 18px var(--num);color:var(--g)}
.fin-row.costos-row .fin-val{color:var(--danger)}
.margen-big{padding:12px 14px}
.margen-big-bar{height:8px;border-radius:4px;background:var(--border);overflow:hidden;margin:6px 0}
.margen-fill{height:100%;border-radius:2px}
.fill-ok{background:var(--g)}.fill-med{background:#f59e0b}.fill-mal{background:var(--danger)}
.margen-big-labels{display:flex;justify-content:space-between;font:400 11px var(--num);color:var(--t3)}

/* accion btns sidebar */
.action-btn{width:100%;padding:11px 14px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:600 13px var(--body);color:var(--t2);cursor:pointer;transition:all .12s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:var(--sh)}
.action-btn:hover{border-color:var(--g);color:var(--g);background:var(--g-bg)}
.action-btn.primary{background:var(--g);color:#fff;border-color:var(--g)}
.action-btn.primary:hover{opacity:.9}

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

@media(max-width:640px){
  .detail-layout{flex-direction:column}
  .col-side{width:100%;position:static}
  .sh-row2{grid-template-columns:1fr}
}
</style>

<!-- Breadcrumb -->
<div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap">
  <a href="/costos" style="font:500 13px var(--body);color:var(--g);text-decoration:none">← Costos</a>
  <span style="color:var(--border2)">/</span>
  <span style="font:500 13px var(--body);color:var(--t3)"><?= e($venta['numero']) ?></span>
</div>

<div class="detail-layout">

  <!-- ── Columna principal ── -->
  <div class="col-main">

    <!-- Header venta -->
    <div class="det-header">
      <div class="det-folio"><?= e($venta['numero']) ?> · <?= ucfirst($venta['estado']) ?></div>
      <div class="det-titulo"><?= e($venta['titulo']) ?></div>
      <div class="det-cliente">
        <div class="det-av"><?= e($ini) ?></div>
        <span class="det-cli-name"><?= e($venta['cliente_nombre'] ?? '—') ?></span>
        <?php if ($venta['asesor_nombre'] && Auth::es_admin()): ?>
        <span style="font:400 12px var(--body);color:var(--t3)">· <?= e($venta['asesor_nombre']) ?></span>
        <?php endif; ?>
      </div>
      <div class="det-meta">
        <div class="det-meta-item">
          <div class="det-meta-lbl">Total venta</div>
          <div class="det-meta-val"><?= fmt_v($total_venta) ?></div>
        </div>
        <div class="det-meta-item">
          <div class="det-meta-lbl">Total costos</div>
          <div class="det-meta-val" style="color:var(--danger)"><?= fmt_v($total_costos) ?></div>
        </div>
        <div class="det-meta-item">
          <div class="det-meta-lbl">Margen</div>
          <div class="det-meta-val" style="color:<?= $margen_color ?>">
            <?= $margen_pct !== null ? $margen_pct . '%' : '—' ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de costos -->
    <div class="slabel">Costos registrados</div>
    <div class="card" id="costosCard">
      <div class="cost-tbl-header">
        <span></span>
        <span>Concepto</span>
        <span class="cost-col-cat">Categoría</span>
        <span class="cost-col-fecha">Fecha</span>
        <span style="text-align:right">Importe</span>
        <span></span>
      </div>

      <?php if (empty($gastos)): ?>
      <div style="text-align:center;padding:32px 20px;color:var(--t3);font:400 13px var(--body)">
        Sin costos registrados para esta venta aún.
      </div>
      <?php else: ?>
      <?php foreach ($gastos as $g):
        $g_color = color_hex_v($g['cat_color'] ?? '#94a3b8');
        $fecha_fmt = $g['fecha'] ? date('j M Y', strtotime($g['fecha'])) : '—';
      ?>
      <div class="cost-row" data-gasto-id="<?= (int)$g['id'] ?>">
        <div class="cost-cat-dot" style="background:<?= $g_color ?>"></div>
        <div class="cost-info">
          <div class="cost-titulo"><?= e($g['concepto']) ?></div>
          <div class="cost-meta"><?= e($g['cat_nombre'] ?? '—') ?> · <?= $fecha_fmt ?></div>
        </div>
        <div class="cost-col-cat" style="display:none">
          <span style="font:500 12px var(--body);color:var(--t2)"><?= e($g['cat_nombre'] ?? '—') ?></span>
        </div>
        <div class="cost-col-fecha" style="display:none">
          <span style="font:400 12px var(--num);color:var(--t3)"><?= $fecha_fmt ?></span>
        </div>
        <div class="cost-monto"><?= fmt_v((float)$g['importe']) ?></div>
        <div class="cost-actions">
          <button class="cost-btn edi"
                  onclick='openSheetEdit(<?= (int)$g['id'] ?>, <?= htmlspecialchars(json_encode(['venta_id'=>$venta_id,'categoria_id'=>(int)$g['categoria_id'],'proveedor_id'=>(int)($g['proveedor_id']??0),'concepto'=>$g['concepto'],'importe'=>$g['importe'],'fecha'=>$g['fecha'],'nota'=>$g['nota']??'']), ENT_QUOTES) ?>)'
                  title="Editar">✎</button>
          <button class="cost-btn del"
                  onclick="eliminarCosto(<?= (int)$g['id'] ?>, this)"
                  title="Eliminar">✕</button>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

      <button class="cost-add-btn" onclick="openSheetNew()">+ Agregar costo</button>
    </div>

  </div><!-- /col-main -->

  <!-- ── Sidebar ── -->
  <div class="col-side">

    <div class="fin-card">
      <div class="fin-row destacado">
        <span class="fin-lbl">Total venta</span>
        <span class="fin-val"><?= fmt_v($total_venta) ?></span>
      </div>
      <div class="fin-row costos-row">
        <span class="fin-lbl">Total costos</span>
        <span class="fin-val"><?= fmt_v($total_costos) ?></span>
      </div>
      <div class="fin-row">
        <span class="fin-lbl">Utilidad</span>
        <span class="fin-val" style="color:<?= $margen_color ?>"><?= fmt_v($utilidad) ?></span>
      </div>
      <div class="margen-big">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3)">Margen</span>
          <span style="font:700 16px var(--num);color:<?= $margen_color ?>">
            <?= $margen_pct !== null ? $margen_pct . '%' : '—' ?>
          </span>
        </div>
        <?php if ($margen_pct !== null): ?>
        <div class="margen-big-bar">
          <div class="margen-fill <?= $fill_cls ?>" style="width:<?= min(100, max(0, (int)$margen_pct)) ?>%"></div>
        </div>
        <div class="margen-big-labels"><span>0%</span><span>100%</span></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Desglose por categoría -->
    <?php if (!empty($por_cat)): ?>
    <div class="card" style="padding:14px 16px">
      <div style="font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);margin-bottom:10px">Por categoría</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach ($por_cat as $cid => $cat): ?>
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:8px;height:8px;border-radius:4px;flex-shrink:0;background:<?= e(color_hex_v($cat['color'])) ?>"></div>
          <div style="flex:1;font:500 12px var(--body);color:var(--t2)"><?= e($cat['nombre']) ?></div>
          <div style="font:600 13px var(--num)"><?= fmt_v2($cat['total']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <button class="action-btn primary" onclick="openSheetNew()">+ Agregar costo</button>
    <a href="/ventas/<?= $venta_id ?>" class="action-btn" style="text-decoration:none;justify-content:center">Ver venta</a>
    <a href="/costos" class="action-btn" style="text-decoration:none;justify-content:center">← Lista de costos</a>

  </div><!-- /col-side -->

</div><!-- /detail-layout -->


<!-- SHEET: COSTO -->
<div class="sh-overlay" id="ov-shCosto" onclick="closeSheet('shCosto')"></div>
<div class="bottom-sheet" id="shCosto">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shCostoTit">Agregar costo</div>
    <button class="sh-close" onclick="closeSheet('shCosto')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shCostoId" value="">
    <div class="sh-field">
      <div class="sh-lbl">Categoría <span style="color:var(--danger)">*</span></div>
      <select class="sh-select" id="shCostoCat">
        <option value="">Seleccionar…</option>
        <?php foreach ($categorias as $cat): ?>
        <option value="<?= (int)$cat['id'] ?>"><?= e($cat['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if (!empty($proveedores)): ?>
    <div class="sh-field">
      <div class="sh-lbl">Proveedor (opcional)</div>
      <select class="sh-select" id="shCostoProv">
        <option value="">Sin proveedor</option>
        <?php foreach ($proveedores as $pv): ?>
        <option value="<?= (int)$pv['id'] ?>"><?= e($pv['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="sh-field">
      <div class="sh-lbl">Concepto <span style="color:var(--danger)">*</span></div>
      <input class="sh-input" type="text" id="shCostoConcepto" placeholder="Ej. Herrajes, Instalación…" maxlength="200">
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
    <button class="sh-btn-save" onclick="guardarCosto()">Guardar</button>
  </div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
const VENTA_ID = <?= $venta_id ?>;

const HAS_PROV = !!document.getElementById('shCostoProv');

function openSheetNew() {
    document.getElementById('shCostoId').value      = '';
    document.getElementById('shCostoTit').textContent = 'Agregar costo';
    document.getElementById('shCostoCat').value     = '';
    if (HAS_PROV) document.getElementById('shCostoProv').value = '';
    document.getElementById('shCostoConcepto').value= '';
    document.getElementById('shCostoImporte').value = '';
    document.getElementById('shCostoFecha').value   = '<?= date('Y-m-d') ?>';
    document.getElementById('shCostoNota').value    = '';
    document.getElementById('ov-shCosto').classList.add('open');
    document.getElementById('shCosto').classList.add('open');
}
function openSheetEdit(id, data) {
    document.getElementById('shCostoId').value       = id;
    document.getElementById('shCostoTit').textContent = 'Editar costo';
    document.getElementById('shCostoCat').value      = data.categoria_id;
    if (HAS_PROV) document.getElementById('shCostoProv').value = data.proveedor_id || '';
    document.getElementById('shCostoConcepto').value = data.concepto;
    document.getElementById('shCostoImporte').value  = data.importe;
    document.getElementById('shCostoFecha').value    = data.fecha || '<?= date('Y-m-d') ?>';
    document.getElementById('shCostoNota').value     = data.nota || '';
    document.getElementById('ov-shCosto').classList.add('open');
    document.getElementById('shCosto').classList.add('open');
}
function closeSheet(id) {
    document.getElementById('ov-' + id)?.classList.remove('open');
    document.getElementById(id)?.classList.remove('open');
}

async function guardarCosto() {
    const id      = document.getElementById('shCostoId').value;
    const cat_id  = document.getElementById('shCostoCat').value;
    const concepto= document.getElementById('shCostoConcepto').value.trim();
    const importe = parseFloat(document.getElementById('shCostoImporte').value);
    const fecha   = document.getElementById('shCostoFecha').value;
    const nota    = document.getElementById('shCostoNota').value.trim();

    if (!cat_id || !concepto || !importe || importe <= 0) {
        alert('Completa los campos obligatorios.');
        return;
    }

    const prov_id = HAS_PROV ? parseInt(document.getElementById('shCostoProv').value) || 0 : 0;

    const url = id ? '/costos/gasto/' + id : '/costos/gasto';
    try {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ venta_id: VENTA_ID, categoria_id: parseInt(cat_id), proveedor_id: prov_id, concepto, importe, fecha, nota })
        });
        const d = await r.json();
        if (d.ok) { closeSheet('shCosto'); location.reload(); }
        else alert(d.error || 'Error al guardar.');
    } catch { alert('Error de conexión.'); }
}

async function eliminarCosto(id, btn) {
    if (!confirm('¿Eliminar este costo?')) return;
    try {
        const r = await fetch('/costos/gasto/' + id + '/eliminar', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN } });
        const d = await r.json();
        if (d.ok) {
            btn.closest('.cost-row')?.remove();
            location.reload(); // para actualizar totales
        } else alert(d.error || 'Error al eliminar.');
    } catch { alert('Error de conexión.'); }
}

// Desktop cols
function applyDesktop() {
    const d = window.innerWidth >= 641;
    document.querySelectorAll('.cost-col-cat,.cost-col-fecha').forEach(el => el.style.display = d ? '' : 'none');
}
applyDesktop();
window.addEventListener('resize', applyDesktop);
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
