<?php
// ============================================================
//  CotizaApp — modules/clientes/lista.php
//  GET /clientes
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();

// ─── Parámetros ──────────────────────────────────────────
$busqueda = trim($_GET['q']     ?? '');
$orden    = $_GET['orden']      ?? 'reciente';
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = 30;

$ordenes_validos = ['reciente','nombre','monto_desc','cots_desc'];
if (!in_array($orden, $ordenes_validos)) $orden = 'reciente';

// ─── Query ───────────────────────────────────────────────
$where  = ["cl.empresa_id = ?"];
$params = [$empresa_id];

if ($busqueda !== '') {
    $where[]  = "(cl.nombre LIKE ? OR cl.telefono LIKE ? OR cl.email LIKE ?)";
    $like     = '%' . $busqueda . '%';
    $params   = array_merge($params, [$like, $like, $like]);
}

$where_sql = implode(' AND ', $where);

$order_sql = match($orden) {
    'nombre'     => 'cl.nombre ASC',
    'monto_desc' => 'total_comprado DESC',
    'cots_desc'  => 'num_cots DESC',
    default      => 'cl.created_at DESC',
};

$total_rows = (int) DB::val(
    "SELECT COUNT(*) FROM clientes cl WHERE $where_sql",
    $params
);
$pag = paginar($total_rows, $pagina, $por_pag);

$clientes = DB::query(
    "SELECT cl.*,
            COUNT(DISTINCT c.id)  AS num_cots,
            COUNT(DISTINCT v.id)  AS num_ventas,
            COALESCE(SUM(CASE WHEN v.estado != 'cancelada' THEN v.total ELSE 0 END), 0) AS total_comprado,
            COALESCE(SUM(CASE WHEN v.estado != 'cancelada' THEN v.saldo ELSE 0 END), 0) AS saldo_pendiente,
            u.nombre AS asesor_nombre
     FROM clientes cl
     LEFT JOIN cotizaciones c ON c.cliente_id = cl.id AND c.empresa_id = cl.empresa_id
     LEFT JOIN ventas       v ON v.cliente_id = cl.id AND v.empresa_id = cl.empresa_id
     LEFT JOIN usuarios     u ON u.id = cl.usuario_id
     WHERE $where_sql
     GROUP BY cl.id
     ORDER BY $order_sql
     LIMIT ? OFFSET ?",
    array_merge($params, [$por_pag, $pag['offset']])
);

$page_title = 'Clientes';
ob_start();
?>
<style>
/* TOOLBAR */
.toolbar      { display:flex; gap:8px; margin-bottom:14px; align-items:center; flex-wrap:wrap; }
.search-wrap  { flex:1; min-width:200px; position:relative; }
.search-wrap input { width:100%; background:var(--white); border:1.5px solid var(--border); border-radius:var(--r-sm); padding:10px 14px 10px 38px; font:400 14px var(--body); color:var(--text); outline:none; transition:border-color .15s; box-shadow:var(--sh); }
.search-wrap input:focus { border-color:var(--g); }
.search-wrap input::placeholder { color:var(--t3); }
.search-ico   { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:14px; color:var(--t3); pointer-events:none; }

/* TABLA */
.list-card    { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }
.tbl-header   { display:none; }
.cli-row      { display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border); cursor:pointer; transition:background .1s; text-decoration:none; color:inherit; }
.cli-row:last-child { border-bottom:none; }
.cli-row:hover      { background:#fafaf8; }

/* Mobile */
.cli-av       { width:38px; height:38px; border-radius:10px; background:var(--g); display:flex; align-items:center; justify-content:center; font:700 14px var(--body); color:#fff; flex-shrink:0; }
.cli-info     { flex:1; min-width:0; }
.cli-nombre   { font:600 14px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cli-tel      { font:400 13px var(--num); color:var(--t3); margin-top:2px; }
.cli-meta     { display:flex; gap:8px; margin-top:4px; flex-wrap:wrap; }
.cli-badge    { font:500 12px var(--body); color:var(--t3); }
.cli-badge span { font:600 12px var(--num); color:var(--text); }
.cli-r        { text-align:right; flex-shrink:0; }
.cli-monto    { font:600 15px var(--num); color:var(--text); }
.cli-monto-lbl{ font:400 11px var(--body); color:var(--t3); margin-top:2px; }

/* Desktop */
@media(min-width:641px) {
    .tbl-header {
        display:grid;
        grid-template-columns: minmax(0,2fr) 150px 80px 80px 130px 90px;
        padding:8px 16px;
        border-bottom:2px solid var(--border);
        background:var(--bg);
    }
    .tbl-header span { font:700 11px var(--body); letter-spacing:.07em; text-transform:uppercase; color:var(--t3); }

    .cli-row {
        display:grid;
        grid-template-columns: minmax(0,2fr) 150px 80px 80px 130px 90px;
        align-items:center; gap:0; padding:11px 16px;
    }
    .cli-av    { display:none; }
    .cli-info  { padding-right:14px; }
    .cli-tel   { display:none; }
    .cli-meta  { display:none; }
    .cli-r     { display:none; }

    .cli-col-tel    { font:400 13px var(--num); color:var(--t3); white-space:nowrap; }
    .cli-col-cots   { font:500 13px var(--num); color:var(--text); text-align:center; }
    .cli-col-ventas { font:500 13px var(--num); color:var(--text); text-align:center; }
    .cli-col-monto  { font:600 14px var(--num); color:var(--text); white-space:nowrap; }
    .cli-col-accion { display:flex; justify-content:flex-end; }
    .act-btn { height:30px; padding:0 12px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 12px var(--body); color:var(--t2); cursor:pointer; transition:all .12s; }
    .act-btn:hover { border-color:var(--g); color:var(--g); background:var(--g-bg); }
}

@media(max-width:640px) {
    .cli-col-tel,.cli-col-cots,.cli-col-ventas,.cli-col-monto,.cli-col-accion { display:none; }
}

/* ── Bottom Sheet ───────────────────────────────────────── */
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
.sh-lbl{font:700 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);margin-bottom:6px}
.sh-note{font:400 11px var(--body);color:var(--t3);margin-top:5px;line-height:1.5}
.sh-input{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:11px 13px;font:400 15px var(--body);color:var(--text);outline:none;transition:border-color .15s;box-sizing:border-box}
.sh-input:focus{border-color:var(--g)}
.sh-footer{padding:14px 18px;border-top:1px solid var(--border);flex-shrink:0;display:flex;gap:10px}
.sh-btn-save{flex:1;padding:13px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 14px var(--body);color:#fff;cursor:pointer}
.sh-btn-cancel{padding:13px 18px;border-radius:var(--r-sm);border:1px solid var(--border);background:transparent;font:600 14px var(--body);color:var(--t2);cursor:pointer}

/* Paginación */
.pagination { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:20px; }
.pag-btn    { padding:7px 13px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 13px var(--body); color:var(--t2); text-decoration:none; transition:all .12s; }
.pag-btn:hover  { border-color:var(--g); color:var(--g); }
.pag-btn.active { background:var(--g); border-color:var(--g); color:#fff; }
.pag-btn.disabled { opacity:.4; pointer-events:none; }
</style>

<!-- ENCABEZADO -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
    <div>
        <h1 style="font:800 22px var(--body); letter-spacing:-.02em;">Clientes</h1>
        <p style="font:400 13px var(--body); color:var(--t3); margin-top:3px;">
            <?= number_format($total_rows) ?> en total
        </p>
    </div>
    <button onclick="openSheet('shCliente', null)"
            style="display:flex;align-items:center;gap:6px;padding:10px 16px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 13px var(--body);color:#fff;cursor:pointer;transition:opacity .15s;"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        + Nuevo cliente
    </button>
</div>

<!-- TOOLBAR -->
<div class="toolbar">
    <div class="search-wrap">
        <span class="search-ico">&#128269;</span>
        <input type="text" id="search-input"
               value="<?= e($busqueda) ?>"
               placeholder="Buscar por nombre, teléfono, email…"
               oninput="debounceSearch(this.value)">
    </div>
    <select onchange="filtrar('orden', this.value)"
            style="padding:10px 12px;border-radius:var(--r-sm);border:1.5px solid var(--border);font:500 13px var(--body);color:var(--t2);background:var(--white);box-shadow:var(--sh);">
        <option value="reciente" <?= $orden==='reciente'   ?'selected':''?>>Más reciente</option>
        <option value="nombre"   <?= $orden==='nombre'     ?'selected':''?>>Nombre A–Z</option>
        <option value="monto_desc" <?= $orden==='monto_desc'?'selected':''?>>Mayor compra</option>
        <option value="cots_desc"  <?= $orden==='cots_desc' ?'selected':''?>>Más cotizaciones</option>
    </select>
</div>

<div style="font:400 13px var(--num);color:var(--t3);margin-bottom:8px;">
    <?= $busqueda ? number_format($total_rows) . ' resultado' . ($total_rows !== 1 ? 's' : '') : number_format($total_rows) . ' cliente' . ($total_rows !== 1 ? 's' : '') ?>
</div>

<!-- TABLA -->
<div class="list-card">
    <div class="tbl-header">
        <span>Nombre</span>
        <span>Teléfono</span>
        <span style="text-align:center">Cots.</span>
        <span style="text-align:center">Ventas</span>
        <span>Total comprado</span>
        <span style="text-align:right">Acciones</span>
    </div>

    <?php if (empty($clientes)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--t3);">
        <div style="font:700 16px var(--body);color:var(--t2);margin-bottom:6px;">
            <?= $busqueda ? 'Sin resultados' : 'Sin clientes aún' ?>
        </div>
        <div style="font:400 13px var(--body);">
            <?= $busqueda ? 'Prueba otro término de búsqueda.' : 'Agrega tu primer cliente con el botón de arriba.' ?>
        </div>
    </div>
    <?php else: ?>

    <?php foreach ($clientes as $cl):
        $iniciales = strtoupper(substr($cl['nombre'], 0, 1));
        if (strpos($cl['nombre'], ' ') !== false) {
            $partes = explode(' ', $cl['nombre']);
            $iniciales = strtoupper($partes[0][0] . ($partes[1][0] ?? ''));
        }
        $total_c = $cl['total_comprado'] > 0
            ? format_money($cl['total_comprado'], $empresa['moneda'])
            : '—';
        $conversion = $cl['num_cots'] > 0
            ? round(($cl['num_ventas'] / $cl['num_cots']) * 100)
            : 0;
    ?>
    <a href="/clientes/<?= (int)$cl['id'] ?>" class="cli-row">
        <!-- Mobile: avatar + info + monto -->
        <div class="cli-av"><?= e($iniciales) ?></div>
        <div class="cli-info">
            <div class="cli-nombre"><?= e($cl['nombre']) ?></div>
            <div class="cli-tel"><?= e($cl['telefono'] ?? '') ?></div>
            <div class="cli-meta">
                <span class="cli-badge">Cots: <span><?= (int)$cl['num_cots'] ?></span></span>
                <span class="cli-badge">Ventas: <span><?= (int)$cl['num_ventas'] ?></span></span>
            </div>
        </div>

        <!-- Desktop cols -->
        <div class="cli-col-tel"><?= e($cl['telefono'] ?? '—') ?></div>
        <div class="cli-col-cots"><?= (int)$cl['num_cots'] ?></div>
        <div class="cli-col-ventas"><?= (int)$cl['num_ventas'] ?></div>
        <div class="cli-col-monto" style="<?= $cl['total_comprado'] <= 0 ? 'color:var(--t3)' : '' ?>">
            <?= $total_c ?>
        </div>
        <div class="cli-col-accion">
            <button class="act-btn" onclick="event.preventDefault();">Ver</button>
        </div>

        <!-- Mobile right -->
        <div class="cli-r">
            <div class="cli-monto" style="<?= $cl['total_comprado'] <= 0 ? 'color:var(--t3)' : '' ?>">
                <?= $total_c ?>
            </div>
            <div class="cli-monto-lbl">
                <?= $cl['total_comprado'] > 0 ? 'total comprado' : 'sin ventas' ?>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Paginación -->
<?php if ($pag['total_pags'] > 1): ?>
<div class="pagination">
    <?php $qs = http_build_query(['q' => $busqueda, 'orden' => $orden]); ?>
    <a href="/clientes?<?= $qs ?>&p=<?= $pag['pagina'] - 1 ?>" class="pag-btn <?= !$pag['hay_prev'] ? 'disabled' : '' ?>">← Anterior</a>
    <?php for ($i = max(1,$pag['pagina']-2); $i <= min($pag['total_pags'],$pag['pagina']+2); $i++): ?>
        <a href="/clientes?<?= $qs ?>&p=<?= $i ?>" class="pag-btn <?= $i===$pag['pagina'] ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="/clientes?<?= $qs ?>&p=<?= $pag['pagina'] + 1 ?>" class="pag-btn <?= !$pag['hay_next'] ? 'disabled' : '' ?>">Siguiente →</a>
</div>
<?php endif; ?>


<!-- ══ SHEET: NUEVO CLIENTE ══ -->
<div class="sh-overlay" id="ov-shCliente" onclick="closeSheet('shCliente')"></div>
<div class="bottom-sheet" id="shCliente">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <div class="sh-title" id="shCliente-title">Nuevo cliente</div>
        <button class="sh-close" onclick="closeSheet('shCliente')">✕</button>
    </div>
    <div class="sh-body">
        <input type="hidden" id="cli-id" value="">
        <div class="sh-field">
            <div class="sh-lbl">Nombre completo <span style="color:var(--danger)">*</span></div>
            <input class="sh-input" type="text" id="cli-nombre" placeholder="Nombre del cliente" autocomplete="name">
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Teléfono <span style="color:var(--danger)">*</span></div>
            <input class="sh-input" type="tel" id="cli-telefono" placeholder="662 000 0000" style="font-family:var(--num);">
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Dirección (opcional)</div>
            <input class="sh-input" type="text" id="cli-direccion" placeholder="Calle, colonia, ciudad…">
        </div>
        <div class="sh-field" style="border-bottom:none;">
            <div class="sh-lbl">Nota (opcional)</div>
            <textarea class="sh-input" id="cli-nota" style="min-height:60px;resize:none;" placeholder="Referencias u observaciones…"></textarea>
        </div>
    </div>
    <div class="sh-footer">
        <button class="sh-btn-cancel" onclick="closeSheet('shCliente')">Cancelar</button>
        <button class="sh-btn-save" onclick="guardarCliente()">Guardar cliente</button>
    </div>
</div>


<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';

// ─── Sheet ───────────────────────────────────────────────
function openSheet(id, data) {
    if (id === 'shCliente') {
        document.getElementById('cli-id').value       = data?.id       ?? '';
        document.getElementById('cli-nombre').value   = data?.nombre   ?? '';
        document.getElementById('cli-telefono').value = data?.telefono ?? '';
        document.getElementById('cli-direccion').value= data?.direccion ?? '';
        document.getElementById('cli-nota').value     = data?.nota     ?? '';
        document.getElementById('shCliente-title').textContent = data?.id ? 'Editar cliente' : 'Nuevo cliente';
    }
    document.getElementById('ov-' + id).classList.add('open');
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
    // Focus primer campo
    setTimeout(() => document.querySelector('#' + id + ' input:not([type=hidden])').focus(), 100);
}
function closeSheet(id) {
    document.getElementById('ov-' + id).classList.remove('open');
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// ─── Guardar cliente ─────────────────────────────────────
async function guardarCliente() {
    const id        = document.getElementById('cli-id').value;
    const nombre    = document.getElementById('cli-nombre').value.trim();
    const telefono  = document.getElementById('cli-telefono').value.trim();
    const direccion = document.getElementById('cli-direccion').value.trim();
    const nota      = document.getElementById('cli-nota').value.trim();

    if (!nombre)   { alert('El nombre es requerido'); return; }
    if (!telefono) { alert('El teléfono es requerido'); return; }

    const btn = document.querySelector('#shCliente .sh-btn-save');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const url = id ? '/clientes/' + id : '/clientes';
        const r   = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ nombre, telefono, direccion, nota })
        });
        const d = await r.json();
        if (!d.ok) { alert(d.error || 'Error al guardar'); btn.disabled=false; btn.textContent='Guardar cliente'; return; }
        window.location.href = '/clientes/' + d.id;
    } catch(e) {
        alert('Error de conexión');
        btn.disabled=false; btn.textContent='Guardar cliente';
    }
}

// ─── Búsqueda debounce ───────────────────────────────────
let _st = null;
function debounceSearch(v) { clearTimeout(_st); _st = setTimeout(() => filtrar('q', v), 350); }
function filtrar(k, v) {
    const p = new URLSearchParams(window.location.search);
    p.set(k, v); if (k !== 'p') p.delete('p');
    window.location.href = '/clientes?' + p.toString();
}

// ─── Sheet: estilos ──────────────────────────────────────
document.querySelectorAll('.sh-overlay').forEach(ov => {
    ov.addEventListener('click', () => {
        const id = ov.id.replace('ov-', '');
        closeSheet(id);
    });
});
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
