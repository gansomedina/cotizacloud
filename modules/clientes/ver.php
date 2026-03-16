<?php
// ============================================================
//  CotizaApp — modules/clientes/ver.php
//  GET /clientes/:id
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();
$cliente_id = (int)($id ?? 0);
if (!$cliente_id) redirect('/clientes');

// ─── Cargar cliente ──────────────────────────────────────
$cliente = DB::row(
    "SELECT cl.*, u.nombre AS asesor_nombre
     FROM clientes cl
     LEFT JOIN usuarios u ON u.id = cl.usuario_id
     WHERE cl.id = ? AND cl.empresa_id = ?",
    [$cliente_id, $empresa_id]
);
if (!$cliente) { flash('error', 'Cliente no encontrado'); redirect('/clientes'); }

// ─── Stats ───────────────────────────────────────────────
$stats = DB::row(
    "SELECT
        COUNT(DISTINCT c.id) AS num_cots,
        COUNT(DISTINCT v.id) AS num_ventas,
        COALESCE(SUM(CASE WHEN v.estado != 'cancelada' THEN v.total ELSE 0 END), 0) AS total_comprado,
        COALESCE(SUM(CASE WHEN v.estado != 'cancelada' THEN v.saldo ELSE 0 END), 0) AS saldo_pendiente
     FROM clientes cl
     LEFT JOIN cotizaciones c ON c.cliente_id = cl.id AND c.empresa_id = cl.empresa_id
     LEFT JOIN ventas       v ON v.cliente_id = cl.id AND v.empresa_id = cl.empresa_id
     WHERE cl.id = ?",
    [$cliente_id]
);

// ─── Cotizaciones recientes ──────────────────────────────
$cotizaciones = DB::query(
    "SELECT id, numero, titulo, total, estado, created_at
     FROM cotizaciones
     WHERE cliente_id = ? AND empresa_id = ?
     ORDER BY created_at DESC
     LIMIT 20",
    [$cliente_id, $empresa_id]
);

// ─── Ventas ──────────────────────────────────────────────
$ventas = DB::query(
    "SELECT id, numero, titulo, total, saldo, estado, created_at
     FROM ventas
     WHERE cliente_id = ? AND empresa_id = ?
     ORDER BY created_at DESC
     LIMIT 20",
    [$cliente_id, $empresa_id]
);

// ─── Helpers ─────────────────────────────────────────────
$conversion = ($stats['num_cots'] > 0)
    ? round(($stats['num_ventas'] / $stats['num_cots']) * 100)
    : 0;

// Iniciales del avatar
$iniciales = strtoupper(substr($cliente['nombre'], 0, 1));
if (strpos($cliente['nombre'], ' ') !== false) {
    $partes    = explode(' ', $cliente['nombre']);
    $iniciales = strtoupper($partes[0][0] . ($partes[1][0] ?? ''));
}

function badge_cot(string $estado): string {
    $map = [
        'borrador'  => ['st-borrador',  'Borrador'],
        'enviada'   => ['st-enviada',   'Enviada'],
        'vista'     => ['st-vista',     'Vista'],
        'aceptada'  => ['st-aceptada',  'Aceptada'],
        'rechazada' => ['st-rechazada', 'Rechazada'],
        'vencida'   => ['st-vencida',   'Vencida'],
        'convertida'=> ['st-aceptada',  'Convertida'],
    ];
    [$cls, $lbl] = $map[$estado] ?? ['st-borrador', ucfirst($estado)];
    return "<span class=\"st $cls\"><span class=\"st-dot\"></span>$lbl</span>";
}

function badge_venta(string $estado): string {
    $map = [
        'pendiente' => ['st-pendiente', 'Pendiente'],
        'parcial'   => ['st-parcial',   'Parcial'],
        'pagada'    => ['st-pagada',    'Pagada'],
        'entregada' => ['st-entregada', 'Entregada'],
        'cancelada' => ['st-cancelada', 'Cancelada'],
    ];
    [$cls, $lbl] = $map[$estado] ?? ['st-pendiente', ucfirst($estado)];
    return "<span class=\"st $cls\"><span class=\"st-dot\"></span>$lbl</span>";
}

$page_title = e($cliente['nombre']);
ob_start();
?>
<style>
/* LAYOUT */
.detail-layout { display:flex; gap:20px; align-items:flex-start; }
.col-main      { flex:1; min-width:0; }
.col-side      { width:260px; flex-shrink:0; display:flex; flex-direction:column; gap:12px; position:sticky; top:76px; }

/* SECCIÓN */
.sec-lbl { font:700 11px var(--body); letter-spacing:.07em; text-transform:uppercase; color:var(--t2); margin:20px 0 10px; display:flex; align-items:center; gap:10px; }
.sec-lbl::after { content:''; flex:1; height:1.5px; background:var(--border); }
.sec-lbl:first-child { margin-top:0; }

/* CARD */
.card { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }

/* HEADER CLIENTE */
.det-header { background:var(--white); border:1px solid var(--border); border-radius:var(--r); padding:18px; box-shadow:var(--sh); }
.det-av     { width:50px; height:50px; border-radius:12px; background:var(--g); display:flex; align-items:center; justify-content:center; font:700 18px var(--body); color:#fff; flex-shrink:0; }
.det-top    { display:flex; align-items:center; gap:14px; }
.det-nombre { font:700 20px var(--body); letter-spacing:-.01em; }
.det-tel    { font:400 14px var(--num); color:var(--t3); margin-top:4px; }
.det-email  { font:400 13px var(--body); color:var(--blue); margin-top:2px; }
.det-datos  { display:flex; gap:20px; margin-top:14px; flex-wrap:wrap; padding-top:14px; border-top:1px solid var(--border); }
.det-dato   { display:flex; flex-direction:column; gap:2px; }
.det-lbl    { font:700 10px var(--body); letter-spacing:.08em; text-transform:uppercase; color:var(--t3); }
.det-val    { font:600 13px var(--num); color:var(--text); }

/* NOTA */
.det-nota { margin-top:12px; padding:10px 13px; background:var(--bg); border-radius:var(--r-sm); font:400 13px var(--body); color:var(--t2); line-height:1.6; border:1px solid var(--border); }

/* ITEMS COT/VENTA */
.item-row   { display:flex; align-items:center; gap:10px; padding:12px 16px; border-bottom:1px solid var(--border); text-decoration:none; color:inherit; transition:background .1s; }
.item-row:last-child { border-bottom:none; }
.item-row:hover      { background:#fafaf8; }
.item-body  { flex:1; min-width:0; }
.item-folio { font:500 12px var(--num); color:var(--t3); margin-bottom:2px; }
.item-titulo{ font:600 14px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.item-fecha { font:400 12px var(--num); color:var(--t3); margin-top:2px; }
.item-r     { text-align:right; flex-shrink:0; }
.item-monto { font:600 14px var(--num); color:var(--text); }

/* STATUS BADGES */
.st     { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font:700 11px var(--body); white-space:nowrap; }
.st-dot { width:5px; height:5px; border-radius:50%; flex-shrink:0; }
.st-borrador  { background:var(--slate-bg); color:var(--slate); }  .st-borrador .st-dot  { background:#94a3b8; }
.st-enviada   { background:var(--blue-bg);  color:var(--blue); }   .st-enviada .st-dot   { background:var(--blue); }
.st-vista     { background:var(--purple-bg);color:var(--purple); } .st-vista .st-dot     { background:var(--purple); }
.st-aceptada  { background:var(--g-light);  color:var(--g); }      .st-aceptada .st-dot  { background:var(--g); }
.st-rechazada { background:var(--danger-bg);color:var(--danger); } .st-rechazada .st-dot { background:var(--danger); }
.st-vencida   { background:var(--amb-bg);   color:var(--amb); }    .st-vencida .st-dot   { background:#f59e0b; }
.st-cancelada { background:var(--danger-bg);color:var(--danger); } .st-cancelada .st-dot { background:var(--danger); }
.st-pagada    { background:var(--g-light);  color:var(--g); }      .st-pagada .st-dot    { background:var(--g); }
.st-entregada { background:var(--blue-bg);  color:var(--blue); }   .st-entregada .st-dot { background:var(--blue); }
.st-parcial   { background:var(--amb-bg);   color:var(--amb); }    .st-parcial .st-dot   { background:#f59e0b; }
.st-pendiente { background:var(--slate-bg); color:var(--slate); }  .st-pendiente .st-dot { background:#94a3b8; }

/* STAT CARD */
.stat-card { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }
.stat-row  { display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid var(--border); }
.stat-row:last-child { border-bottom:none; }
.stat-lbl  { font:400 13px var(--body); color:var(--t2); }
.stat-val  { font:600 14px var(--num); color:var(--text); }
.stat-val.green { color:var(--g); }
.stat-val.amber { color:var(--amb); }

/* ACCIONES */
.action-btn { width:100%; padding:11px 14px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 13px var(--body); color:var(--t2); cursor:pointer; transition:all .12s; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:var(--sh); text-decoration:none; }
.action-btn:hover   { border-color:var(--g); color:var(--g); background:var(--g-bg); }
.action-btn.primary { background:var(--g); color:#fff; border-color:var(--g); }
.action-btn.primary:hover { opacity:.9; }
.action-btn.danger:hover  { border-color:var(--danger); color:var(--danger); background:var(--danger-bg); }

/* SHEET */
.sh-overlay  { position:fixed; inset:0; z-index:200; background:rgba(0,0,0,.4); backdrop-filter:blur(4px); opacity:0; pointer-events:none; transition:opacity .25s; }
.sh-overlay.open { opacity:1; pointer-events:all; }
.bottom-sheet { position:fixed; bottom:0; left:0; right:0; z-index:201; background:var(--white); border-radius:20px 20px 0 0; max-height:92vh; display:flex; flex-direction:column; transform:translateY(100%); transition:transform .3s cubic-bezier(.32,0,.15,1); box-shadow:0 -8px 32px rgba(0,0,0,.1); max-width:640px; margin:0 auto; }
.bottom-sheet.open { transform:translateY(0); }
.sh-handle   { width:34px; height:4px; border-radius:2px; background:var(--border2); margin:12px auto 0; flex-shrink:0; }
.sh-header   { padding:14px 18px 12px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; border-bottom:1px solid var(--border); }
.sh-title    { font:800 17px var(--body); }
.sh-close    { width:30px; height:30px; border-radius:999px; border:none; background:var(--bg); font-size:15px; cursor:pointer; color:var(--t2); display:flex; align-items:center; justify-content:center; }
.sh-body     { overflow-y:auto; flex:1; padding:0 0 8px; }
.sh-field    { padding:13px 18px; border-bottom:1px solid var(--border); }
.sh-field:last-child { border-bottom:none; }
.sh-lbl      { font:700 10px var(--body); letter-spacing:.08em; text-transform:uppercase; color:var(--t3); margin-bottom:6px; }
.sh-input    { width:100%; background:var(--bg); border:1.5px solid var(--border); border-radius:var(--r-sm); padding:11px 13px; font:400 15px var(--body); color:var(--text); outline:none; transition:border-color .15s; }
.sh-input:focus { border-color:var(--g); }
.sh-footer   { padding:14px 18px; border-top:1px solid var(--border); flex-shrink:0; display:flex; gap:10px; }
.sh-btn-save   { flex:1; padding:13px; border-radius:var(--r-sm); border:none; background:var(--g); font:700 14px var(--body); color:#fff; cursor:pointer; }
.sh-btn-cancel { padding:13px 18px; border-radius:var(--r-sm); border:1px solid var(--border); background:transparent; font:600 14px var(--body); color:var(--t2); cursor:pointer; }
.sh-btn-danger { flex:1; padding:13px; border-radius:var(--r-sm); border:1px solid var(--danger); background:var(--danger-bg); font:700 14px var(--body); color:var(--danger); cursor:pointer; transition:all .12s; }
.sh-btn-danger:hover { background:var(--danger); color:#fff; }

@media(max-width:640px) {
    .detail-layout { flex-direction:column; }
    .col-side { width:100%; position:static; }
}
</style>

<!-- TOPBAR: back + título + editar -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:12px;">
    <div style="display:flex; align-items:center; gap:10px;">
        <a href="/clientes"
           style="width:34px;height:34px;border-radius:8px;border:1px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--t2);box-shadow:var(--sh);">
            &#8592;
        </a>
        <div>
            <h1 style="font:700 18px var(--body); letter-spacing:-.01em;"><?= e($cliente['nombre']) ?></h1>
            <div style="font:400 12px var(--body); color:var(--t3); margin-top:1px;">Cliente</div>
        </div>
    </div>
    <button onclick="openEditSheet()"
            style="padding:9px 16px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:600 13px var(--body);color:var(--t2);cursor:pointer;box-shadow:var(--sh);transition:all .12s;"
            onmouseover="this.style.borderColor='var(--g)';this.style.color='var(--g)'"
            onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--t2)'">
        Editar
    </button>
</div>

<div class="detail-layout">

<!-- ══ COLUMNA PRINCIPAL ══ -->
<div class="col-main">

    <!-- HEADER CLIENTE -->
    <div class="det-header">
        <div class="det-top">
            <div class="det-av"><?= e($iniciales) ?></div>
            <div>
                <div class="det-nombre"><?= e($cliente['nombre']) ?></div>
                <?php if ($cliente['telefono']): ?>
                <div class="det-tel">
                    <a href="tel:<?= e($cliente['telefono']) ?>" style="color:inherit;text-decoration:none;"><?= e($cliente['telefono']) ?></a>
                </div>
                <?php endif; ?>
                <?php if ($cliente['email']): ?>
                <div class="det-email">
                    <a href="mailto:<?= e($cliente['email']) ?>" style="color:var(--blue);text-decoration:none;"><?= e($cliente['email']) ?></a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="det-datos">
            <div class="det-dato">
                <div class="det-lbl">Registrado</div>
                <div class="det-val"><?= date('d M Y', strtotime($cliente['created_at'])) ?></div>
            </div>
            <?php if ($cliente['asesor_nombre']): ?>
            <div class="det-dato">
                <div class="det-lbl">Asesor</div>
                <div class="det-val"><?= e($cliente['asesor_nombre']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($cliente['ultima_actividad'] ?? null): ?>
            <div class="det-dato">
                <div class="det-lbl">Última actividad</div>
                <div class="det-val"><?= fecha_humana($cliente['ultima_actividad']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($cliente['nota']): ?>
        <div class="det-nota"><?= nl2br(e($cliente['nota'])) ?></div>
        <?php endif; ?>
    </div>

    <!-- COTIZACIONES -->
    <div class="sec-lbl">
        Cotizaciones
        <span style="font:600 12px var(--num);color:var(--t3);text-transform:none;letter-spacing:0;"><?= (int)$stats['num_cots'] ?></span>
    </div>

    <?php if (empty($cotizaciones)): ?>
    <div class="card" style="padding:24px 16px;text-align:center;color:var(--t3);font:400 13px var(--body);">
        Sin cotizaciones aún.
        <a href="/cotizaciones/nueva?cliente_id=<?= $cliente_id ?>" style="color:var(--g);text-decoration:none;font-weight:600;"> Crear una →</a>
    </div>
    <?php else: ?>
    <div class="card">
        <?php foreach ($cotizaciones as $cot): ?>
        <a href="/cotizaciones/<?= (int)$cot['id'] ?>" class="item-row">
            <div class="item-body">
                <div class="item-folio"><?= e($cot['numero']) ?></div>
                <div class="item-titulo"><?= e($cot['titulo']) ?></div>
                <div class="item-fecha"><?= fecha_humana($cot['created_at']) ?></div>
            </div>
            <div class="item-r">
                <div class="item-monto"><?= format_money($cot['total'], $empresa['moneda']) ?></div>
                <div style="margin-top:5px;"><?= badge_cot($cot['estado']) ?></div>
            </div>
        </a>
        <?php endforeach; ?>
        <div style="padding:11px 16px;background:var(--bg);border-top:1px solid var(--border);">
            <a href="/cotizaciones/nueva?cliente_id=<?= $cliente_id ?>"
               style="font:600 13px var(--body);color:var(--g);text-decoration:none;">
                + Nueva cotización para este cliente
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- VENTAS -->
    <div class="sec-lbl">
        Ventas
        <span style="font:600 12px var(--num);color:var(--t3);text-transform:none;letter-spacing:0;"><?= (int)$stats['num_ventas'] ?></span>
    </div>

    <?php if (empty($ventas)): ?>
    <div class="card" style="padding:24px 16px;text-align:center;color:var(--t3);font:400 13px var(--body);">
        Sin ventas. Las ventas se crean al convertir una cotización aceptada.
    </div>
    <?php else: ?>
    <div class="card">
        <?php foreach ($ventas as $v): ?>
        <a href="/ventas/<?= (int)$v['id'] ?>" class="item-row">
            <div class="item-body">
                <div class="item-folio"><?= e($v['numero']) ?></div>
                <div class="item-titulo"><?= e($v['titulo']) ?></div>
                <div class="item-fecha"><?= fecha_humana($v['created_at']) ?></div>
            </div>
            <div class="item-r">
                <div class="item-monto"><?= format_money($v['total'], $empresa['moneda']) ?></div>
                <?php if ($v['saldo'] > 0 && $v['estado'] !== 'cancelada'): ?>
                <div style="font:400 12px var(--body);color:var(--amb);margin-top:2px;"><?= format_money($v['saldo'], $empresa['moneda']) ?> pend.</div>
                <?php endif; ?>
                <div style="margin-top:5px;"><?= badge_venta($v['estado']) ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div><!-- /col-main -->

<!-- ══ COLUMNA LATERAL ══ -->
<div class="col-side">

    <!-- STATS -->
    <div class="stat-card">
        <div class="stat-row">
            <span class="stat-lbl">Cotizaciones</span>
            <span class="stat-val"><?= (int)$stats['num_cots'] ?></span>
        </div>
        <div class="stat-row">
            <span class="stat-lbl">Ventas</span>
            <span class="stat-val"><?= (int)$stats['num_ventas'] ?></span>
        </div>
        <div class="stat-row">
            <span class="stat-lbl">Conversión</span>
            <span class="stat-val <?= $conversion >= 50 ? 'green' : 'amber' ?>"><?= $conversion ?>%</span>
        </div>
        <div class="stat-row">
            <span class="stat-lbl">Total comprado</span>
            <span class="stat-val green">
                <?= $stats['total_comprado'] > 0 ? format_money($stats['total_comprado'], $empresa['moneda']) : '—' ?>
            </span>
        </div>
        <?php if ($stats['saldo_pendiente'] > 0): ?>
        <div class="stat-row">
            <span class="stat-lbl">Saldo pendiente</span>
            <span class="stat-val amber"><?= format_money($stats['saldo_pendiente'], $empresa['moneda']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- ACCIONES -->
    <button class="action-btn primary" onclick="openEditSheet()">Editar cliente</button>


    <a href="/cotizaciones/nueva?cliente_id=<?= $cliente_id ?>" class="action-btn">
        + Nueva cotización
    </a>

    <?php if (Auth::es_admin() && $stats['num_cots'] == 0 && $stats['num_ventas'] == 0): ?>
    <button class="action-btn danger" onclick="eliminarCliente()">Eliminar cliente</button>
    <?php endif; ?>

</div><!-- /col-side -->

</div><!-- /detail-layout -->


<!-- ══ SHEET: EDITAR CLIENTE ══ -->
<div class="sh-overlay" id="ov-shEditCliente" onclick="closeSheet('shEditCliente')"></div>
<div class="bottom-sheet" id="shEditCliente">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <div class="sh-title">Editar cliente</div>
        <button class="sh-close" onclick="closeSheet('shEditCliente')">✕</button>
    </div>
    <div class="sh-body">
        <div class="sh-field">
            <div class="sh-lbl">Nombre completo <span style="color:var(--danger)">*</span></div>
            <input class="sh-input" type="text" id="edit-nombre"
                   value="<?= e($cliente['nombre']) ?>" autocomplete="name">
        </div>
        <div class="sh-field" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <div class="sh-lbl">Teléfono <span style="color:var(--danger)">*</span></div>
                <input class="sh-input" type="tel" id="edit-telefono"
                       value="<?= e($cliente['telefono'] ?? '') ?>" style="font-family:var(--num);">
            </div>
            <div>
                <div class="sh-lbl">Email (opcional)</div>
                <input class="sh-input" type="email" id="edit-email"
                       value="<?= e($cliente['email'] ?? '') ?>">
            </div>
        </div>
        <div class="sh-field" style="border-bottom:none;">
            <div class="sh-lbl">Nota (opcional)</div>
            <textarea class="sh-input" id="edit-nota"
                      style="min-height:70px;resize:none;"><?= e($cliente['nota'] ?? '') ?></textarea>
        </div>
    </div>
    <div class="sh-footer">
        <button class="sh-btn-cancel" onclick="closeSheet('shEditCliente')">Cancelar</button>
        <button class="sh-btn-save" onclick="guardarEdicion()">Guardar cambios</button>
    </div>
</div>


<script>
const CSRF_TOKEN  = '<?= csrf_token() ?>';
const CLIENTE_ID  = <?= $cliente_id ?>;

function openSheet(id) {
    document.getElementById('ov-' + id).classList.add('open');
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeSheet(id) {
    document.getElementById('ov-' + id).classList.remove('open');
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
function openEditSheet() { openSheet('shEditCliente'); }

// ─── Guardar edición ─────────────────────────────────────
async function guardarEdicion() {
    const nombre   = document.getElementById('edit-nombre').value.trim();
    const telefono = document.getElementById('edit-telefono').value.trim();
    const email    = document.getElementById('edit-email').value.trim();
    const nota     = document.getElementById('edit-nota').value.trim();

    if (!nombre)   { alert('El nombre es requerido'); return; }
    if (!telefono) { alert('El teléfono es requerido'); return; }

    const btn = document.querySelector('#shEditCliente .sh-btn-save');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const r = await fetch('/clientes/' + CLIENTE_ID, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ nombre, telefono, email, nota })
        });
        const d = await r.json();
        if (!d.ok) { alert(d.error || 'Error al guardar'); btn.disabled=false; btn.textContent='Guardar cambios'; return; }
        window.location.reload();
    } catch(e) {
        alert('Error de conexión');
        btn.disabled=false; btn.textContent='Guardar cambios';
    }
}

// ─── Eliminar cliente ────────────────────────────────────
async function eliminarCliente() {
    if (!confirm('¿Eliminar este cliente? Esta acción no se puede deshacer.')) return;

    const r = await fetch('/clientes/' + CLIENTE_ID + '/eliminar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({})
    });
    const d = await r.json();
    if (d.ok) window.location.href = '/clientes';
    else alert(d.error || 'Error al eliminar');
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
