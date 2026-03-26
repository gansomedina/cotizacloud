<?php
// ============================================================
//  CotizaApp — modules/proveedores/lista.php
//  GET /proveedores
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();

// Permiso de módulo por usuario
if (!Auth::es_admin() && !Auth::puede('ver_proveedores')) { redirect('/dashboard'); }

// Proveedores ahora es un tab dentro de Costos
redirect('/costos');

// ── Auto-migración tabla proveedores ────────────────────────
$tabla_existe = DB::val(
    "SELECT 1 FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'proveedores' LIMIT 1"
);
if (!$tabla_existe) {
    DB::exec("CREATE TABLE IF NOT EXISTS `proveedores` (
        `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `empresa_id`  INT UNSIGNED NOT NULL,
        `nombre`      VARCHAR(150) NOT NULL,
        `contacto`    VARCHAR(150) DEFAULT NULL,
        `telefono`    VARCHAR(30)  DEFAULT NULL,
        `email`       VARCHAR(150) DEFAULT NULL,
        `direccion`   VARCHAR(255) DEFAULT NULL,
        `nota`        TEXT         DEFAULT NULL,
        `activo`      TINYINT(1)   NOT NULL DEFAULT 1,
        `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_prov_empresa` (`empresa_id`, `activo`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// ── Parámetros ──────────────────────────────────────────────
$busqueda = trim($_GET['q'] ?? '');
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = 30;
$ver_inactivos = ($_GET['inactivos'] ?? '') === '1';

// ── Query ───────────────────────────────────────────────────
$where  = ["p.empresa_id = ?"];
$params = [$empresa_id];

if (!$ver_inactivos) {
    $where[] = "p.activo = 1";
}

if ($busqueda !== '') {
    $where[]  = "(p.nombre LIKE ? OR p.contacto LIKE ? OR p.telefono LIKE ? OR p.email LIKE ?)";
    $like     = '%' . $busqueda . '%';
    $params   = array_merge($params, [$like, $like, $like, $like]);
}

$where_sql = implode(' AND ', $where);

$total_rows = (int) DB::val("SELECT COUNT(*) FROM proveedores p WHERE $where_sql", $params);
$pag = paginar($total_rows, $pagina, $por_pag);

$proveedores = DB::query(
    "SELECT p.*,
            COALESCE(g.num_gastos, 0)    AS num_gastos,
            COALESCE(g.total_gastos, 0)  AS total_gastos
     FROM proveedores p
     LEFT JOIN (
         SELECT proveedor_id, COUNT(*) AS num_gastos, SUM(importe) AS total_gastos
         FROM gastos_venta WHERE empresa_id = ?
         GROUP BY proveedor_id
     ) g ON g.proveedor_id = p.id
     WHERE $where_sql
     ORDER BY p.nombre ASC
     LIMIT ? OFFSET ?",
    array_merge([$empresa_id], $params, [$por_pag, $pag['offset']])
);

$page_title = 'Proveedores';
ob_start();
?>
<style>
.toolbar      { display:flex; gap:8px; margin-bottom:14px; align-items:center; flex-wrap:wrap; }
.search-wrap  { flex:1; min-width:200px; position:relative; }
.search-wrap input { width:100%; background:var(--white); border:1.5px solid var(--border); border-radius:var(--r-sm); padding:10px 14px 10px 38px; font:400 14px var(--body); color:var(--text); outline:none; transition:border-color .15s; box-shadow:var(--sh); }
.search-wrap input:focus { border-color:var(--g); }
.search-ico   { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:14px; color:var(--t3); pointer-events:none; }

.list-card    { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }
.tbl-header   { display:none; }
.prov-row     { display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border); cursor:pointer; transition:background .1s; text-decoration:none; color:inherit; }
.prov-row:last-child { border-bottom:none; }
.prov-row:hover      { background:#fafaf8; }
.prov-row.inactivo   { opacity:.5; }

.prov-av      { width:38px; height:38px; border-radius:10px; background:var(--g); display:flex; align-items:center; justify-content:center; font:700 14px var(--body); color:#fff; flex-shrink:0; }
.prov-info    { flex:1; min-width:0; }
.prov-nombre  { font:600 14px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.prov-sub     { font:400 13px var(--num); color:var(--t3); margin-top:2px; }
.prov-meta    { display:flex; gap:8px; margin-top:4px; flex-wrap:wrap; }
.prov-badge   { font:500 12px var(--body); color:var(--t3); }
.prov-badge span { font:600 12px var(--num); color:var(--text); }
.prov-r       { text-align:right; flex-shrink:0; }
.prov-monto   { font:600 15px var(--num); color:var(--text); }
.prov-monto-lbl{ font:400 11px var(--body); color:var(--t3); margin-top:2px; }

@media(min-width:641px) {
    .tbl-header {
        display:grid;
        grid-template-columns: minmax(0,2fr) 150px 130px 100px 130px 90px;
        padding:8px 16px; border-bottom:2px solid var(--border); background:var(--bg);
    }
    .tbl-header span { font:700 11px var(--body); letter-spacing:.07em; text-transform:uppercase; color:var(--t3); }
    .prov-row {
        display:grid;
        grid-template-columns: minmax(0,2fr) 150px 130px 100px 130px 90px;
        align-items:center; gap:0; padding:11px 16px;
    }
    .prov-av   { display:none; }
    .prov-sub  { display:none; }
    .prov-meta { display:none; }
    .prov-r    { display:none; }
    .prov-col-contacto { font:400 13px var(--body); color:var(--t2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .prov-col-tel      { font:400 13px var(--num); color:var(--t3); white-space:nowrap; }
    .prov-col-gastos   { font:500 13px var(--num); color:var(--text); text-align:center; }
    .prov-col-monto    { font:600 14px var(--num); color:var(--text); white-space:nowrap; }
    .prov-col-accion   { display:flex; justify-content:flex-end; }
    .act-btn { height:30px; padding:0 12px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 12px var(--body); color:var(--t2); cursor:pointer; transition:all .12s; }
    .act-btn:hover { border-color:var(--g); color:var(--g); background:var(--g-bg); }
}
@media(max-width:640px) {
    .prov-col-contacto,.prov-col-tel,.prov-col-gastos,.prov-col-monto,.prov-col-accion { display:none; }
}

/* Bottom Sheet */
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
.sh-input{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:11px 13px;font:400 15px var(--body);color:var(--text);outline:none;transition:border-color .15s;box-sizing:border-box}
.sh-input:focus{border-color:var(--g)}
.sh-row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sh-footer{padding:14px 18px;border-top:1px solid var(--border);flex-shrink:0;display:flex;gap:10px}
.sh-btn-save{flex:1;padding:13px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 14px var(--body);color:#fff;cursor:pointer}
.sh-btn-cancel{padding:13px 18px;border-radius:var(--r-sm);border:1px solid var(--border);background:transparent;font:600 14px var(--body);color:var(--t2);cursor:pointer}

.pagination { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:20px; }
.pag-btn    { padding:7px 13px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 13px var(--body); color:var(--t2); text-decoration:none; transition:all .12s; }
.pag-btn:hover  { border-color:var(--g); color:var(--g); }
.pag-btn.active { background:var(--g); border-color:var(--g); color:#fff; }
.pag-btn.disabled { opacity:.4; pointer-events:none; }

@media(max-width:640px) { .sh-row2{grid-template-columns:1fr} }
</style>

<!-- ENCABEZADO -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
    <div>
        <h1 style="font:800 22px var(--body); letter-spacing:-.02em;">Proveedores</h1>
        <p style="font:400 13px var(--body); color:var(--t3); margin-top:3px;">
            <?= number_format($total_rows) ?> en total
        </p>
    </div>
    <button onclick="openSheet(null)"
            style="display:flex;align-items:center;gap:6px;padding:10px 16px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 13px var(--body);color:#fff;cursor:pointer;transition:opacity .15s;"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        + Nuevo proveedor
    </button>
</div>

<!-- TOOLBAR -->
<div class="toolbar">
    <div class="search-wrap">
        <span class="search-ico">&#128269;</span>
        <input type="text" id="search-input"
               value="<?= e($busqueda) ?>"
               placeholder="Buscar por nombre, contacto, teléfono, email…"
               onkeydown="if(event.key==='Enter')filtrar('q',this.value)">
        <?php if ($busqueda !== ''): ?><button onclick="filtrar('q','')" style="background:none;border:none;cursor:pointer;font-size:16px;color:var(--t3);padding:0 4px" title="Limpiar">✕</button><?php endif; ?>
    </div>
    <label style="display:flex;align-items:center;gap:6px;font:500 13px var(--body);color:var(--t2);cursor:pointer;white-space:nowrap;">
        <input type="checkbox" <?= $ver_inactivos ? 'checked' : '' ?> onchange="filtrar('inactivos', this.checked ? '1' : '')"> Ver inactivos
    </label>
</div>

<!-- TABLA -->
<div class="list-card">
    <div class="tbl-header">
        <span>Nombre</span>
        <span>Contacto</span>
        <span>Teléfono</span>
        <span style="text-align:center">Gastos</span>
        <span>Total pagado</span>
        <span style="text-align:right">Acciones</span>
    </div>

    <?php if (empty($proveedores)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--t3);">
        <div style="font:700 16px var(--body);color:var(--t2);margin-bottom:6px;">
            <?= $busqueda ? 'Sin resultados' : 'Sin proveedores aún' ?>
        </div>
        <div style="font:400 13px var(--body);">
            <?= $busqueda ? 'Prueba otro término.' : 'Agrega tu primer proveedor con el botón de arriba.' ?>
        </div>
    </div>
    <?php else: ?>

    <?php foreach ($proveedores as $pv):
        $ini = strtoupper(substr($pv['nombre'], 0, 1));
        if (strpos($pv['nombre'], ' ') !== false) {
            $pp = explode(' ', $pv['nombre']);
            $ini = strtoupper($pp[0][0] . ($pp[1][0] ?? ''));
        }
        $total_fmt = $pv['total_gastos'] > 0
            ? format_money($pv['total_gastos'], $empresa['moneda'])
            : '—';
    ?>
    <a href="/proveedores/<?= (int)$pv['id'] ?>" class="prov-row <?= !$pv['activo'] ? 'inactivo' : '' ?>">
        <div class="prov-av"><?= e($ini) ?></div>
        <div class="prov-info">
            <div class="prov-nombre"><?= e($pv['nombre']) ?></div>
            <div class="prov-sub"><?= e($pv['contacto'] ?? $pv['telefono'] ?? '') ?></div>
            <div class="prov-meta">
                <span class="prov-badge">Gastos: <span><?= (int)$pv['num_gastos'] ?></span></span>
            </div>
        </div>

        <div class="prov-col-contacto"><?= e($pv['contacto'] ?? '—') ?></div>
        <div class="prov-col-tel"><?= e($pv['telefono'] ?? '—') ?></div>
        <div class="prov-col-gastos"><?= (int)$pv['num_gastos'] ?></div>
        <div class="prov-col-monto" style="<?= $pv['total_gastos'] <= 0 ? 'color:var(--t3)' : '' ?>">
            <?= $total_fmt ?>
        </div>
        <div class="prov-col-accion">
            <button class="act-btn">Ver</button>
        </div>

        <div class="prov-r">
            <div class="prov-monto" style="<?= $pv['total_gastos'] <= 0 ? 'color:var(--t3)' : '' ?>">
                <?= $total_fmt ?>
            </div>
            <div class="prov-monto-lbl">
                <?= $pv['total_gastos'] > 0 ? 'total pagado' : 'sin gastos' ?>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Paginación -->
<?php if ($pag['total_pags'] > 1): ?>
<div class="pagination">
    <?php $qs = http_build_query(array_filter(['q' => $busqueda, 'inactivos' => $ver_inactivos ? '1' : ''])); ?>
    <a href="/proveedores?<?= $qs ?>&p=<?= $pag['pagina'] - 1 ?>" class="pag-btn <?= !$pag['hay_prev'] ? 'disabled' : '' ?>">← Anterior</a>
    <?php for ($i = max(1,$pag['pagina']-2); $i <= min($pag['total_pags'],$pag['pagina']+2); $i++): ?>
        <a href="/proveedores?<?= $qs ?>&p=<?= $i ?>" class="pag-btn <?= $i===$pag['pagina'] ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="/proveedores?<?= $qs ?>&p=<?= $pag['pagina'] + 1 ?>" class="pag-btn <?= !$pag['hay_next'] ? 'disabled' : '' ?>">Siguiente →</a>
</div>
<?php endif; ?>


<!-- ══ SHEET: NUEVO/EDITAR PROVEEDOR ══ -->
<div class="sh-overlay" id="ov-shProv" onclick="closeSheet()"></div>
<div class="bottom-sheet" id="shProv">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <div class="sh-title" id="shProv-title">Nuevo proveedor</div>
        <button class="sh-close" onclick="closeSheet()">✕</button>
    </div>
    <div class="sh-body">
        <input type="hidden" id="prov-id" value="">
        <div class="sh-field">
            <div class="sh-lbl">Nombre / empresa <span style="color:var(--danger)">*</span></div>
            <input class="sh-input" type="text" id="prov-nombre" placeholder="Ej. Ferretería López" maxlength="150">
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Persona de contacto</div>
            <input class="sh-input" type="text" id="prov-contacto" placeholder="Nombre del vendedor o contacto" maxlength="150">
        </div>
        <div class="sh-field sh-row2">
            <div>
                <div class="sh-lbl">Teléfono</div>
                <input class="sh-input" type="tel" id="prov-telefono" placeholder="662 000 0000" style="font-family:var(--num);" maxlength="30">
            </div>
            <div>
                <div class="sh-lbl">Email</div>
                <input class="sh-input" type="email" id="prov-email" placeholder="ventas@proveedor.com" maxlength="150">
            </div>
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Dirección</div>
            <input class="sh-input" type="text" id="prov-direccion" placeholder="Calle, colonia, ciudad…" maxlength="255">
        </div>
        <div class="sh-field" style="border-bottom:none;">
            <div class="sh-lbl">Nota (opcional)</div>
            <textarea class="sh-input" id="prov-nota" style="min-height:60px;resize:none;" placeholder="Horarios, condiciones de pago, RFC…" maxlength="500"></textarea>
        </div>
    </div>
    <div class="sh-footer">
        <button class="sh-btn-cancel" onclick="closeSheet()">Cancelar</button>
        <button class="sh-btn-save" id="btnGuardar" onclick="guardarProv()">Guardar</button>
    </div>
</div>


<script>
function openSheet(data) {
    document.getElementById('prov-id').value        = data?.id        ?? '';
    document.getElementById('prov-nombre').value    = data?.nombre    ?? '';
    document.getElementById('prov-contacto').value  = data?.contacto  ?? '';
    document.getElementById('prov-telefono').value  = data?.telefono  ?? '';
    document.getElementById('prov-email').value     = data?.email     ?? '';
    document.getElementById('prov-direccion').value = data?.direccion ?? '';
    document.getElementById('prov-nota').value      = data?.nota      ?? '';
    document.getElementById('shProv-title').textContent = data?.id ? 'Editar proveedor' : 'Nuevo proveedor';
    document.getElementById('ov-shProv').classList.add('open');
    document.getElementById('shProv').classList.add('open');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('prov-nombre').focus(), 100);
}
function closeSheet() {
    document.getElementById('ov-shProv').classList.remove('open');
    document.getElementById('shProv').classList.remove('open');
    document.body.style.overflow = '';
}

async function guardarProv() {
    const id        = document.getElementById('prov-id').value;
    const nombre    = document.getElementById('prov-nombre').value.trim();
    const contacto  = document.getElementById('prov-contacto').value.trim();
    const telefono  = document.getElementById('prov-telefono').value.trim();
    const email     = document.getElementById('prov-email').value.trim();
    const direccion = document.getElementById('prov-direccion').value.trim();
    const nota      = document.getElementById('prov-nota').value.trim();

    if (!nombre) { alert('El nombre es requerido'); return; }

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const url = id ? '/proveedores/' + id : '/proveedores';
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, contacto, telefono, email, direccion, nota })
        });
        const d = await r.json();
        if (!d.ok) { alert(d.error || 'Error al guardar'); btn.disabled=false; btn.textContent='Guardar'; return; }
        window.location.href = '/proveedores/' + d.id;
    } catch(e) {
        alert('Error de conexión');
        btn.disabled=false; btn.textContent='Guardar';
    }
}

function filtrar(k, v) {
    const p = new URLSearchParams(window.location.search);
    if (v) p.set(k, v); else p.delete(k);
    if (k !== 'p') p.delete('p');
    window.location.href = '/proveedores?' + p.toString();
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
