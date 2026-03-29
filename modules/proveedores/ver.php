<?php
// ============================================================
//  CotizaApp — modules/proveedores/ver.php
//  GET /proveedores/:id
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id   = EMPRESA_ID;
$empresa      = Auth::empresa();
$proveedor_id = (int)($id ?? 0);

// ── Plan check ──────────────────────────────────────────────
$plan = trial_info($empresa_id);
if (!$plan['es_business']) { redirect('/costos'); }

// Permiso de módulo por usuario
if (!Auth::es_admin() && !Auth::puede('ver_proveedores')) { redirect('/dashboard'); }

if (!$proveedor_id) redirect('/proveedores');

$prov = DB::row(
    "SELECT * FROM proveedores WHERE id = ? AND empresa_id = ?",
    [$proveedor_id, $empresa_id]
);
if (!$prov) { flash('error', 'Proveedor no encontrado'); redirect('/proveedores'); }

// ── Gastos asociados ────────────────────────────────────────
// Check if proveedor_id column exists in gastos_venta
$col_existe = DB::val(
    "SELECT 1 FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gastos_venta' AND COLUMN_NAME = 'proveedor_id' LIMIT 1"
);

$gastos = [];
$total_gastos = 0;
$num_gastos   = 0;

if ($col_existe) {
    $gastos = DB::query(
        "SELECT gv.*, cc.nombre AS cat_nombre, cc.color AS cat_color,
                v.numero AS venta_numero, v.titulo AS venta_titulo
         FROM gastos_venta gv
         LEFT JOIN categorias_costos cc ON cc.id = gv.categoria_id
         LEFT JOIN ventas v ON v.id = gv.venta_id
         WHERE gv.proveedor_id = ? AND gv.empresa_id = ?
         ORDER BY gv.fecha DESC, gv.id DESC
         LIMIT 50",
        [$proveedor_id, $empresa_id]
    );
    $total_gastos = array_sum(array_column($gastos, 'importe'));
    $num_gastos   = count($gastos);
}

// Por categoría
$por_cat = [];
foreach ($gastos as $g) {
    $cid = (int)$g['categoria_id'];
    if (!isset($por_cat[$cid])) {
        $por_cat[$cid] = ['nombre' => $g['cat_nombre'] ?? '—', 'color' => $g['cat_color'] ?? '#94a3b8', 'total' => 0];
    }
    $por_cat[$cid]['total'] += (float)$g['importe'];
}
arsort($por_cat);

function fmt_p(float $n): string { return '$' . number_format($n, 0, '.', ','); }

$page_title = 'Proveedor · ' . $prov['nombre'];
ob_start();
?>
<style>
.detail-layout{display:flex;gap:20px;align-items:flex-start}
.col-main{flex:1;min-width:0}
.col-side{width:272px;flex-shrink:0;display:flex;flex-direction:column;gap:12px;position:sticky;top:80px}
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}
.slabel{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t2);margin:20px 0 10px;display:flex;align-items:center;gap:10px}
.slabel::after{content:'';flex:1;height:1.5px;background:var(--border)}
.slabel:first-child{margin-top:0}

/* header */
.det-header{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;box-shadow:var(--sh);margin-bottom:4px}
.det-av{width:42px;height:42px;border-radius:12px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 16px var(--body);color:#fff;flex-shrink:0}
.det-nombre{font:700 18px var(--body);letter-spacing:-.01em}
.det-info{display:flex;flex-direction:column;gap:4px;margin-top:10px}
.det-info-row{display:flex;align-items:center;gap:8px;font:400 13px var(--body);color:var(--t2)}
.det-info-row i{width:14px;height:14px;color:var(--t3)}

/* gastos list */
.gasto-row{display:flex;align-items:center;gap:10px;padding:11px 14px;border-bottom:1px solid var(--border);transition:background .12s}
.gasto-row:last-child{border-bottom:none}
.gasto-row:hover{background:#fafaf8}
.g-dot{width:8px;height:8px;border-radius:4px;flex-shrink:0}
.g-info{flex:1;min-width:0}
.g-titulo{font:600 13px var(--body)}
.g-meta{font:400 11px var(--num);color:var(--t3);margin-top:2px}
.g-monto{font:600 14px var(--num);color:var(--danger);flex-shrink:0;white-space:nowrap}

/* sidebar */
.fin-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}
.fin-row{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid var(--border)}
.fin-row:last-child{border-bottom:none}
.fin-lbl{font:400 13px var(--body);color:var(--t2)}
.fin-val{font:500 14px var(--num);color:var(--text)}
.fin-row.destacado .fin-lbl{font:700 14px var(--body);color:var(--text)}
.fin-row.destacado .fin-val{font:700 18px var(--num);color:var(--danger)}

.action-btn{width:100%;padding:11px 14px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:600 13px var(--body);color:var(--t2);cursor:pointer;transition:all .12s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:var(--sh);text-decoration:none}
.action-btn:hover{border-color:var(--g);color:var(--g);background:var(--g-bg)}
.action-btn.primary{background:var(--g);color:#fff;border-color:var(--g)}
.action-btn.primary:hover{opacity:.9}
.action-btn.danger:hover{border-color:var(--danger);color:var(--danger);background:var(--danger-bg)}

/* Sheet */
.sh-overlay{position:fixed;inset:0;z:200;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .25s}
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
.sh-input{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:11px 13px;font:400 15px var(--body);color:var(--text);outline:none;transition:border-color .15s;box-sizing:border-box}
.sh-input:focus{border-color:var(--g)}
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
  <a href="/proveedores" style="font:500 13px var(--body);color:var(--g);text-decoration:none">← Proveedores</a>
  <span style="color:var(--border2)">/</span>
  <span style="font:500 13px var(--body);color:var(--t3)"><?= e($prov['nombre']) ?></span>
</div>

<div class="detail-layout">

  <!-- ── Columna principal ── -->
  <div class="col-main">

    <!-- Header -->
    <div class="det-header">
      <div style="display:flex;align-items:center;gap:12px">
        <?php
          $ini = strtoupper(substr($prov['nombre'], 0, 1));
          if (strpos($prov['nombre'], ' ') !== false) {
              $pp = explode(' ', $prov['nombre']);
              $ini = strtoupper($pp[0][0] . ($pp[1][0] ?? ''));
          }
        ?>
        <div class="det-av"><?= e($ini) ?></div>
        <div>
          <div class="det-nombre"><?= e($prov['nombre']) ?></div>
          <?php if (!$prov['activo']): ?>
          <span style="font:600 11px var(--body);color:var(--danger);background:var(--danger-bg);padding:2px 8px;border-radius:4px;margin-top:4px;display:inline-block">Inactivo</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="det-info">
        <?php if ($prov['contacto']): ?>
        <div class="det-info-row"><i data-feather="user"></i> <?= e($prov['contacto']) ?></div>
        <?php endif; ?>
        <?php if ($prov['telefono']): ?>
        <div class="det-info-row"><i data-feather="phone"></i> <a href="tel:<?= e($prov['telefono']) ?>" style="color:var(--g);text-decoration:none"><?= e($prov['telefono']) ?></a></div>
        <?php endif; ?>
        <?php if ($prov['email']): ?>
        <div class="det-info-row"><i data-feather="mail"></i> <a href="mailto:<?= e($prov['email']) ?>" style="color:var(--g);text-decoration:none"><?= e($prov['email']) ?></a></div>
        <?php endif; ?>
        <?php if ($prov['direccion']): ?>
        <div class="det-info-row"><i data-feather="map-pin"></i> <?= e($prov['direccion']) ?></div>
        <?php endif; ?>
        <?php if ($prov['nota']): ?>
        <div class="det-info-row" style="margin-top:4px"><i data-feather="file-text"></i> <?= e($prov['nota']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Gastos -->
    <div class="slabel">Gastos registrados (<?= $num_gastos ?>)</div>
    <div class="card">
      <?php if (empty($gastos)): ?>
      <div style="text-align:center;padding:32px 20px;color:var(--t3);font:400 13px var(--body)">
        Sin gastos asociados a este proveedor aún.
        <?php if (!$col_existe): ?>
        <br><span style="font:500 12px var(--body);color:var(--amb)">Ejecuta la migración para habilitar la columna proveedor_id en gastos.</span>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <?php foreach ($gastos as $g):
        $g_color = preg_match('/^#[0-9a-f]{3,6}$/i', $g['cat_color'] ?? '') ? $g['cat_color'] : '#94a3b8';
        $fecha_fmt = $g['fecha'] ? date('j M Y', strtotime($g['fecha'])) : '—';
      ?>
      <a href="/costos/<?= (int)$g['venta_id'] ?>" class="gasto-row" style="text-decoration:none;color:inherit">
        <div class="g-dot" style="background:<?= $g_color ?>"></div>
        <div class="g-info">
          <div class="g-titulo"><?= e($g['concepto']) ?></div>
          <div class="g-meta"><?= e($g['cat_nombre'] ?? '—') ?> · <?= e($g['venta_numero'] ?? '') ?> <?= e($g['venta_titulo'] ?? '') ?> · <?= $fecha_fmt ?></div>
        </div>
        <div class="g-monto"><?= fmt_p((float)$g['importe']) ?></div>
      </a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div><!-- /col-main -->

  <!-- ── Sidebar ── -->
  <div class="col-side">

    <div class="fin-card">
      <div class="fin-row destacado">
        <span class="fin-lbl">Total pagado</span>
        <span class="fin-val"><?= fmt_p($total_gastos) ?></span>
      </div>
      <div class="fin-row">
        <span class="fin-lbl">Gastos</span>
        <span class="fin-val"><?= $num_gastos ?></span>
      </div>
      <div class="fin-row">
        <span class="fin-lbl">Desde</span>
        <span class="fin-val"><?= date('j M Y', strtotime($prov['created_at'])) ?></span>
      </div>
    </div>

    <!-- Desglose por categoría -->
    <?php if (!empty($por_cat)): ?>
    <div class="card" style="padding:14px 16px">
      <div style="font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);margin-bottom:10px">Por categoría</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach ($por_cat as $cat): ?>
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:8px;height:8px;border-radius:4px;flex-shrink:0;background:<?= e(preg_match('/^#[0-9a-f]{3,6}$/i', $cat['color']) ? $cat['color'] : '#94a3b8') ?>"></div>
          <div style="flex:1;font:500 12px var(--body);color:var(--t2)"><?= e($cat['nombre']) ?></div>
          <div style="font:600 13px var(--num)"><?= fmt_p($cat['total']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <button class="action-btn primary" onclick="openEditSheet()">Editar proveedor</button>
    <?php if ($prov['activo']): ?>
    <button class="action-btn danger" onclick="toggleActivo(0)">Desactivar</button>
    <?php else: ?>
    <button class="action-btn" onclick="toggleActivo(1)" style="border-color:var(--g);color:var(--g)">Reactivar</button>
    <?php endif; ?>
    <a href="/proveedores" class="action-btn">← Lista de proveedores</a>

  </div><!-- /col-side -->

</div><!-- /detail-layout -->


<!-- SHEET: EDITAR PROVEEDOR -->
<div class="sh-overlay" id="ov-shEdit" onclick="closeSheet()"></div>
<div class="bottom-sheet" id="shEdit">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <div class="sh-title">Editar proveedor</div>
        <button class="sh-close" onclick="closeSheet()">✕</button>
    </div>
    <div class="sh-body">
        <div class="sh-field">
            <div class="sh-lbl">Nombre / empresa <span style="color:var(--danger)">*</span></div>
            <input class="sh-input" type="text" id="ed-nombre" value="<?= e($prov['nombre']) ?>" maxlength="150">
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Persona de contacto</div>
            <input class="sh-input" type="text" id="ed-contacto" value="<?= e($prov['contacto'] ?? '') ?>" maxlength="150">
        </div>
        <div class="sh-field sh-row2">
            <div>
                <div class="sh-lbl">Teléfono</div>
                <input class="sh-input" type="tel" id="ed-telefono" value="<?= e($prov['telefono'] ?? '') ?>" style="font-family:var(--num);" maxlength="30">
            </div>
            <div>
                <div class="sh-lbl">Email</div>
                <input class="sh-input" type="email" id="ed-email" value="<?= e($prov['email'] ?? '') ?>" maxlength="150">
            </div>
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Dirección</div>
            <input class="sh-input" type="text" id="ed-direccion" value="<?= e($prov['direccion'] ?? '') ?>" maxlength="255">
        </div>
        <div class="sh-field" style="border-bottom:none;">
            <div class="sh-lbl">Nota</div>
            <textarea class="sh-input" id="ed-nota" style="min-height:60px;resize:none;" maxlength="500"><?= e($prov['nota'] ?? '') ?></textarea>
        </div>
    </div>
    <div class="sh-footer">
        <button class="sh-btn-cancel" onclick="closeSheet()">Cancelar</button>
        <button class="sh-btn-save" id="btnGuardar" onclick="guardarEdit()">Guardar</button>
    </div>
</div>


<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
const PROV_ID = <?= $proveedor_id ?>;

function openEditSheet() {
    document.getElementById('ov-shEdit').classList.add('open');
    document.getElementById('shEdit').classList.add('open');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('ed-nombre').focus(), 100);
}
function closeSheet() {
    document.querySelectorAll('.sh-overlay,.bottom-sheet').forEach(el => el.classList.remove('open'));
    document.body.style.overflow = '';
}

async function guardarEdit() {
    const nombre    = document.getElementById('ed-nombre').value.trim();
    const contacto  = document.getElementById('ed-contacto').value.trim();
    const telefono  = document.getElementById('ed-telefono').value.trim();
    const email     = document.getElementById('ed-email').value.trim();
    const direccion = document.getElementById('ed-direccion').value.trim();
    const nota      = document.getElementById('ed-nota').value.trim();

    if (!nombre) { alert('El nombre es requerido'); return; }

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const r = await fetch('/proveedores/' + PROV_ID, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ nombre, contacto, telefono, email, direccion, nota })
        });
        const d = await r.json();
        if (d.ok) location.reload();
        else { alert(d.error || 'Error'); btn.disabled=false; btn.textContent='Guardar'; }
    } catch(e) {
        alert('Error de conexión');
        btn.disabled=false; btn.textContent='Guardar';
    }
}

async function toggleActivo(activo) {
    const msg = activo ? '¿Reactivar este proveedor?' : '¿Desactivar este proveedor?';
    if (!confirm(msg)) return;

    try {
        const r = await fetch('/proveedores/' + PROV_ID + '/toggle', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN } });
        const d = await r.json();
        if (d.ok) location.reload();
        else alert(d.error || 'Error');
    } catch(e) { alert('Error de conexión'); }
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
