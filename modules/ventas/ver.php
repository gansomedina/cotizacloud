<?php
// ============================================================
//  CotizaApp — modules/ventas/ver.php
//  GET /ventas/:id
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();
$venta_id   = (int)($id ?? 0);
if (!$venta_id) redirect('/ventas');

// ─── Cargar venta ────────────────────────────────────────
$venta = DB::row(
    "SELECT v.*,
            cl.nombre AS cliente_nombre, cl.telefono AS cliente_telefono, cl.email AS cliente_email,
            u.nombre  AS asesor_nombre,
            c.numero  AS cot_numero, c.id AS cot_id, c.slug AS cot_slug
     FROM ventas v
     LEFT JOIN clientes cl    ON cl.id = v.cliente_id
     LEFT JOIN usuarios u     ON u.id  = v.usuario_id
     LEFT JOIN cotizaciones c ON c.id  = v.cotizacion_id
     WHERE v.id = ? AND v.empresa_id = ?",
    [$venta_id, $empresa_id]
);
if (!$venta) { flash('error', 'Venta no encontrada'); redirect('/ventas'); }

if (!Auth::puede('ver_todas_ventas') && (int)$venta['usuario_id'] !== (int)Auth::id()) {
    flash('error', 'Sin acceso'); redirect('/ventas');
}

// ─── Líneas de artículos ─────────────────────────────────
$lineas = DB::query(
    "SELECT * FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden ASC",
    [$venta['cotizacion_id'] ?: 0]
);

// ─── Abonos (recibos tipo 'abono') ───────────────────────
$abonos = DB::query(
    "SELECT r.*, u.nombre AS usuario_nombre
     FROM recibos r
     LEFT JOIN usuarios u ON u.id = r.usuario_id
     WHERE r.venta_id = ? AND r.tipo = 'abono'
     ORDER BY r.created_at ASC",
    [$venta_id]
);

// ─── Siguiente folio recibo (para preview en sheet) ──────
$sig_rec = DB::siguiente_folio($empresa_id, 'REC', $empresa['rec_prefijo'] ?? 'REC', peek: true);

$url_vta    = 'https://' . EMPRESA_SLUG . '.' . BASE_DOMAIN . '/v/' . $venta['slug'];
$puede_abonar = Auth::es_admin();
$puede_cancelar_venta = Auth::es_admin();
$puede_cambiar_estado = Auth::es_admin();
$puede_agregar_items  = Auth::es_admin();

$pct = $venta['total'] > 0 ? round(($venta['pagado'] / $venta['total']) * 100) : 0;
$pct = min(100, $pct);

$page_title = e($venta['numero']) . ' — ' . e($venta['titulo']);

// ─── Helpers ─────────────────────────────────────────────
function icono_forma_pago(string $f): string {
    return match($f) { 'efectivo' => '💵', 'transferencia' => '🏦', 'tarjeta' => '💳', default => '💰' };
}
function bg_forma_pago(string $f): string {
    return match($f) { 'efectivo' => '#dcfce7', 'transferencia' => '#dbeafe', 'tarjeta' => '#f3e8ff', default => '#f1f5f9' };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?= e($venta['numero']) ?> — <?= e($empresa['nombre']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --bg:#f4f4f0; --white:#fff; --border:#e2e2dc; --border2:#c8c8c0;
        --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
        --g:#1a5c38; --g-bg:#eef7f2; --g-border:#b8ddc8; --g-light:#e6f4ed;
        --amb:#92400e; --amb-bg:#fef3c7;
        --blue:#1d4ed8; --blue-bg:#dbeafe;
        --danger:#c53030; --danger-bg:#fff5f5;
        --purple:#6d28d9; --purple-bg:#ede9fe;
        --slate:#475569; --slate-bg:#f1f5f9;
        --r:12px; --r-sm:9px;
        --sh:0 1px 3px rgba(0,0,0,.06);
        --sh-md:0 4px 16px rgba(0,0,0,.08);
        --body:'Plus Jakarta Sans',sans-serif;
        --num:'DM Sans',sans-serif;
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:var(--body); background:var(--bg); color:var(--text); -webkit-font-smoothing:antialiased; }

    /* TOPBAR */
    .topbar       { position:sticky; top:0; z-index:100; background:var(--white); border-bottom:1px solid var(--border); height:54px; display:flex; align-items:center; padding:0 20px; }
    .topbar-inner { width:100%; max-width:1080px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .topbar-l     { display:flex; align-items:center; gap:10px; }
    .back-btn     { width:34px; height:34px; border-radius:8px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--t2); text-decoration:none; }
    .topbar-title { font:700 15px var(--body); }
    .topbar-num   { font:500 12px var(--num); color:var(--t3); margin-left:6px; }

    /* PAGE */
    .page-wrap     { max-width:1080px; margin:0 auto; padding:24px 20px 80px; }
    .detail-layout { display:flex; gap:20px; align-items:flex-start; }
    .col-main      { flex:1; min-width:0; }
    .col-side      { width:280px; flex-shrink:0; display:flex; flex-direction:column; gap:12px; position:sticky; top:70px; }

    /* SECCIÓN */
    .sec-lbl { font:700 11px var(--body); letter-spacing:.07em; text-transform:uppercase; color:var(--t2); margin:20px 0 10px; display:flex; align-items:center; gap:10px; }
    .sec-lbl::after { content:''; flex:1; height:1.5px; background:var(--border); }
    .sec-lbl:first-child { margin-top:0; }

    /* CARD */
    .card { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }

    /* STATUS */
    .status     { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:6px; font:700 12px var(--body); }
    .status-dot { width:6px; height:6px; border-radius:3px; flex-shrink:0; }
    .s-pendiente { background:var(--slate-bg); color:var(--slate); } .s-pendiente .status-dot { background:#94a3b8; }
    .s-parcial   { background:var(--amb-bg);   color:var(--amb); }   .s-parcial .status-dot   { background:#f59e0b; }
    .s-pagada    { background:var(--g-bg);     color:var(--g); }     .s-pagada .status-dot    { background:var(--g); }
    .s-entregada { background:var(--blue-bg);  color:var(--blue); }  .s-entregada .status-dot { background:var(--blue); }
    .s-cancelada { background:var(--danger-bg);color:var(--danger); } .s-cancelada .status-dot { background:var(--danger); }

    /* VENTA HEADER */
    .vhdr-card   { background:var(--white); border:1px solid var(--border); border-radius:var(--r); padding:18px; box-shadow:var(--sh); }
    .vhdr-top    { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .vhdr-num    { font:600 12px var(--num); color:var(--t3); margin-bottom:4px; }
    .vhdr-title  { font:700 19px var(--body); letter-spacing:-.01em; }
    .vhdr-client { display:flex; align-items:center; gap:8px; margin-top:10px; }
    .vhdr-av     { width:32px; height:32px; border-radius:8px; background:var(--g); display:flex; align-items:center; justify-content:center; font:700 13px var(--body); color:#fff; flex-shrink:0; }
    .vhdr-cname  { font:600 14px var(--body); }
    .vhdr-cphone { font:400 13px var(--num); color:var(--t3); }
    .vhdr-meta   { display:flex; gap:20px; margin-top:14px; flex-wrap:wrap; padding-top:14px; border-top:1px solid var(--border); }
    .meta-item   { display:flex; flex-direction:column; gap:2px; }
    .meta-lbl    { font:700 10px var(--body); letter-spacing:.08em; text-transform:uppercase; color:var(--t3); }
    .meta-val    { font:600 13px var(--body); color:var(--text); }

    /* ARTÍCULOS */
    .item-row  { display:flex; align-items:flex-start; gap:10px; padding:12px 16px; border-bottom:1px solid var(--border); }
    .item-row:last-child { border-bottom:none; }
    .item-num  { font:500 12px var(--num); color:var(--t3); width:18px; flex-shrink:0; padding-top:2px; }
    .item-body { flex:1; min-width:0; }
    .item-name { font:600 14px var(--body); }
    .item-sku  { font:400 12px var(--num); color:var(--t3); margin-top:2px; }
    .item-desc { font:400 13px var(--body); color:var(--t3); margin-top:3px; }
    .item-r    { text-align:right; flex-shrink:0; }
    .item-qty  { font:400 12px var(--num); color:var(--t3); }
    .item-amt  { font:700 14px var(--num); color:var(--text); margin-top:2px; }

    /* ABONOS */
    .abono-row    { display:flex; align-items:center; gap:12px; padding:13px 16px; border-bottom:1px solid var(--border); }
    .abono-row:last-child { border-bottom:none; }
    .abono-ico    { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
    .abono-info   { flex:1; min-width:0; }
    .abono-forma  { font:600 13px var(--body); }
    .abono-fecha  { font:400 12px var(--num); color:var(--t3); margin-top:2px; }
    .abono-nota   { font:400 12px var(--body); color:var(--t3); margin-top:2px; }
    .abono-recibo { font:600 12px var(--body); color:var(--blue); background:var(--blue-bg); padding:2px 7px; border-radius:4px; margin-top:4px; display:inline-block; cursor:pointer; text-decoration:none; }
    .abono-monto  { font:700 15px var(--num); color:var(--g); flex-shrink:0; text-align:right; }
    .abono-del    { width:26px; height:26px; border-radius:6px; border:1px solid var(--border); background:transparent; display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--t3); font-size:13px; transition:all .12s; margin-top:4px; }
    .abono-del:hover { border-color:var(--danger); color:var(--danger); background:var(--danger-bg); }

    /* RESUMEN FIN */
    .fin-card { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }
    .fin-row  { display:flex; justify-content:space-between; align-items:center; padding:10px 16px; border-bottom:1px solid var(--border); }
    .fin-row:last-child { border-bottom:none; }
    .fin-lbl  { font:400 13px var(--body); color:var(--t2); }
    .fin-val  { font:500 14px var(--num); color:var(--text); }
    .fin-row.total-row .fin-lbl { font:700 14px var(--body); color:var(--text); }
    .fin-row.total-row .fin-val { font:700 18px var(--num); color:var(--g); }
    .fin-row.saldo-row .fin-lbl { font:600 13px var(--body); color:var(--amb); }
    .fin-row.saldo-row .fin-val { font:700 15px var(--num); color:var(--amb); }
    .fin-row.saldo-ok  .fin-lbl { color:var(--g); }
    .fin-row.saldo-ok  .fin-val { color:var(--g); }

    /* PROGRESO */
    .prog-card   { background:var(--white); border:1px solid var(--border); border-radius:var(--r); padding:14px 16px; box-shadow:var(--sh); }
    .prog-bar    { height:8px; border-radius:4px; background:var(--border); overflow:hidden; margin:8px 0; }
    .prog-fill   { height:100%; border-radius:4px; background:var(--g); }
    .prog-labels { display:flex; justify-content:space-between; font:400 12px var(--body); color:var(--t3); }

    /* ESTADO CHANGER */
    .status-changer { display:flex; flex-wrap:wrap; gap:6px; }
    .status-opt     { padding:7px 12px; border-radius:var(--r-sm); border:1.5px solid var(--border); background:var(--bg); font:600 12px var(--body); color:var(--t2); cursor:pointer; transition:all .12s; }
    .status-opt:hover { border-color:var(--border2); }
    .status-opt.active-opt { border-color:var(--g); background:var(--g-bg); color:var(--g); }
    .status-opt.danger-opt { border-color:var(--danger-bg); color:var(--danger); }
    .status-opt.danger-opt:hover { background:var(--danger-bg); }

    /* BOTONES ACCIÓN */
    .action-btn { width:100%; padding:12px 14px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 13px var(--body); color:var(--t2); cursor:pointer; transition:all .12s; display:flex; align-items:center; gap:8px; box-shadow:var(--sh); }
    .action-btn:hover { border-color:var(--g); color:var(--g); background:var(--g-bg); }
    .action-btn.primary { background:var(--g); color:#fff; border-color:var(--g); }
    .action-btn.primary:hover { opacity:.9; }
    .action-btn.danger-btn:hover { border-color:var(--danger); color:var(--danger); background:var(--danger-bg); }

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
    .sh-lbl      { font:700 11px var(--body); letter-spacing:.08em; text-transform:uppercase; color:var(--t3); margin-bottom:6px; }
    .sh-input    { width:100%; background:var(--bg); border:1.5px solid var(--border); border-radius:var(--r-sm); padding:11px 13px; font:400 15px var(--body); color:var(--text); outline:none; transition:border-color .15s; }
    .sh-input:focus { border-color:var(--g); }
    .sh-row2     { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .sh-footer   { padding:14px 18px; border-top:1px solid var(--border); flex-shrink:0; display:flex; gap:10px; }
    .sh-btn-save { flex:1; padding:13px; border-radius:var(--r-sm); border:none; background:var(--g); font:700 14px var(--body); color:#fff; cursor:pointer; }
    .sh-btn-cancel { padding:13px 18px; border-radius:var(--r-sm); border:1px solid var(--border); background:transparent; font:600 14px var(--body); color:var(--t2); cursor:pointer; }
    .sh-note     { font:400 12px var(--body); color:var(--t3); margin-top:5px; line-height:1.5; }

    /* FORMA PAGO */
    .forma-opts { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
    .forma-opt  { padding:12px 8px; border-radius:var(--r-sm); border:1.5px solid var(--border); background:var(--bg); cursor:pointer; text-align:center; transition:all .15s; user-select:none; }
    .forma-opt.selected { border-color:var(--g); background:var(--g-bg); }
    .forma-opt-ico { font-size:22px; display:block; margin-bottom:4px; }
    .forma-opt-lbl { font:700 12px var(--body); color:var(--t2); }
    .forma-opt.selected .forma-opt-lbl { color:var(--g); }

    @media(max-width:760px) {
        .page-wrap { padding:16px 14px 90px; }
        .detail-layout { flex-direction:column; }
        .col-side { width:100%; position:static; }
        .sh-row2  { grid-template-columns:1fr; }
    }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-l">
            <a href="/ventas" class="back-btn">&#8592;</a>
            <div>
                <div class="topbar-title"><?= e($venta['titulo']) ?></div>
                <span class="topbar-num"><?= e($venta['numero']) ?></span>
            </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <button onclick="document.getElementById('url-overlay').classList.add('open')"
                    style="padding:8px 14px;border-radius:var(--r-sm);border:1px solid var(--border2);background:transparent;font:600 13px var(--body);color:var(--t2);cursor:pointer;">
                Compartir
            </button>
        </div>
    </div>
</div>

<div class="page-wrap">
<div class="detail-layout">

<!-- ══ COLUMNA PRINCIPAL ══ -->
<div class="col-main">

    <!-- HEADER VENTA -->
    <div class="vhdr-card">
        <div class="vhdr-top">
            <div>
                <div class="vhdr-num">
                    <?= e($venta['numero']) ?>
                    <?php if ($venta['cot_numero']): ?>
                        · generada de
                        <a href="/cotizaciones/<?= (int)$venta['cot_id'] ?>" style="color:var(--g);text-decoration:none;">
                            <?= e($venta['cot_numero']) ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="vhdr-title"><?= e($venta['titulo']) ?></div>
                <?php if ($venta['cliente_nombre']): ?>
                <div class="vhdr-client">
                    <div class="vhdr-av"><?= strtoupper(substr($venta['cliente_nombre'], 0, 1)) ?></div>
                    <div>
                        <div class="vhdr-cname"><?= e($venta['cliente_nombre']) ?></div>
                        <div class="vhdr-cphone"><?= e($venta['cliente_telefono'] ?? '') ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php
            $map = ['pendiente'=>'s-pendiente','parcial'=>'s-parcial','pagada'=>'s-pagada','entregada'=>'s-entregada','cancelada'=>'s-cancelada'];
            $cls = $map[$venta['estado']] ?? 's-pendiente';
            ?>
            <span class="status <?= $cls ?>"><span class="status-dot"></span><?= ucfirst(e($venta['estado'])) ?></span>
        </div>
        <div class="vhdr-meta">
            <div class="meta-item">
                <div class="meta-lbl">Fecha</div>
                <div class="meta-val"><?= date('d M Y', strtotime($venta['created_at'])) ?></div>
            </div>
            <div class="meta-item">
                <div class="meta-lbl">Asesor</div>
                <div class="meta-val"><?= e($venta['asesor_nombre'] ?? '—') ?></div>
            </div>
            <?php if ($venta['cot_numero']): ?>
            <div class="meta-item">
                <div class="meta-lbl">Cotización</div>
                <div class="meta-val">
                    <a href="/cotizaciones/<?= (int)$venta['cot_id'] ?>" style="color:var(--g);text-decoration:none;">
                        <?= e($venta['cot_numero']) ?> →
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ARTÍCULOS -->
    <?php if (!empty($lineas)): ?>
    <div class="sec-lbl">Artículos</div>
    <div class="card">
        <?php foreach ($lineas as $l): ?>
        <div class="item-row">
            <div class="item-num"><?= (int)$l['orden'] ?></div>
            <div class="item-body">
                <div class="item-name"><?= e($l['titulo']) ?></div>
                <?php if ($l['sku']): ?><div class="item-sku"><?= e($l['sku']) ?></div><?php endif; ?>
                <?php if ($l['descripcion']): ?><div class="item-desc"><?= e(substr($l['descripcion'], 0, 100)) ?></div><?php endif; ?>
            </div>
            <div class="item-r">
                <div class="item-qty"><?= number_format($l['cantidad'], 2) ?> × <?= format_money($l['precio_unit'], $empresa['moneda']) ?></div>
                <div class="item-amt"><?= format_money($l['subtotal'], $empresa['moneda']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (!Auth::es_admin()): ?>
        <div style="padding:10px 16px;background:var(--bg);border-top:1px solid var(--border);font:400 12px var(--body);color:var(--t3);">
            Solo administradores pueden agregar artículos a una venta en curso.
        </div>
        <?php elseif ($venta['estado'] !== 'cancelada'): ?>
        <button onclick="openSheet('shAgregarItem')"
                style="width:100%;padding:12px;border:none;background:var(--bg);font:600 13px var(--body);color:var(--t2);cursor:pointer;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:center;gap:6px;transition:all .12s;"
                onmouseover="this.style.background='var(--g-bg)';this.style.color='var(--g)'"
                onmouseout="this.style.background='var(--bg)';this.style.color='var(--t2)'">
            + Agregar artículo
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ABONOS -->
    <div class="sec-lbl">Abonos registrados</div>
    <div class="card">
        <?php if (empty($abonos)): ?>
        <div style="padding:20px 16px;text-align:center;color:var(--t3);font:400 13px var(--body);">
            Sin abonos registrados
        </div>
        <?php else: ?>
        <?php foreach ($abonos as $ab): ?>
        <div class="abono-row" id="abono-<?= (int)$ab['id'] ?>">
            <div class="abono-ico" style="background:<?= bg_forma_pago($ab['forma_pago']) ?>">
                <?= icono_forma_pago($ab['forma_pago']) ?>
            </div>
            <div class="abono-info">
                <div class="abono-forma"><?= ucfirst(e($ab['forma_pago'])) ?></div>
                <div class="abono-fecha"><?= date('d M Y, g:i A', strtotime($ab['created_at'])) ?></div>
                <?php if ($ab['concepto']): ?><div class="abono-nota"><?= e($ab['concepto']) ?></div><?php endif; ?>
                <?php if ($ab['referencia']): ?><div class="abono-nota" style="color:var(--t3)">Ref: <?= e($ab['referencia']) ?></div><?php endif; ?>
                <a href="/ventas/recibos/<?= (int)$ab['id'] ?>" class="abono-recibo"><?= e($ab['numero']) ?> →</a>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                <div class="abono-monto <?= $ab['cancelado'] ? 'style="text-decoration:line-through;color:var(--danger)"' : '' ?>">
                    <?= format_money($ab['monto'], $empresa['moneda']) ?>
                </div>
                <?php if ($ab['cancelado']): ?>
                    <span style="font:600 11px var(--body);background:var(--danger-bg);color:var(--danger);padding:2px 7px;border-radius:4px;">Cancelado</span>
                <?php elseif ($puede_abonar && $venta['estado'] !== 'cancelada'): ?>
                    <button class="abono-del" onclick="cancelarAbono(<?= (int)$ab['id'] ?>, '<?= e($ab['numero']) ?>', <?= number_format($ab['monto'],2,'.','') ?>)"
                            title="Cancelar recibo">✕</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($puede_abonar && $venta['estado'] !== 'cancelada' && $venta['estado'] !== 'entregada'): ?>
        <div style="padding:13px 16px;border-top:1px solid var(--border);">
            <button onclick="openSheet('shAbono')"
                    style="padding:10px 16px;border-radius:var(--r-sm);border:1px solid var(--g-border);background:var(--g-bg);font:600 13px var(--body);color:var(--g);cursor:pointer;transition:all .12s;"
                    onmouseover="this.style.background='var(--g)';this.style.color='#fff'"
                    onmouseout="this.style.background='var(--g-bg)';this.style.color='var(--g)'">
                + Registrar abono
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- CAMBIO DE ESTADO -->
    <?php if ($puede_cambiar_estado && $venta['estado'] !== 'cancelada'): ?>
    <div class="sec-lbl">Estado de la venta</div>
    <div class="card" style="padding:14px 16px;">
        <div class="status-changer">
            <?php
            $opts = [
                'pendiente' => 'Pendiente',
                'parcial'   => 'Parcialmente pagada',
                'pagada'    => 'Pagada',
                'entregada' => 'Entregada',
            ];
            foreach ($opts as $key => $lbl): ?>
            <div class="status-opt <?= $venta['estado'] === $key ? 'active-opt' : '' ?>"
                 onclick="cambiarEstado('<?= $key ?>')">
                <?= e($lbl) ?>
            </div>
            <?php endforeach; ?>
            <?php if ($puede_cancelar_venta): ?>
            <div class="status-opt danger-opt" onclick="cancelarVenta()">✕ Cancelar venta</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- NOTAS INTERNAS -->
    <div class="sec-lbl">Notas internas</div>
    <div class="card" style="padding:14px 16px;">
        <textarea id="notas-internas"
                  style="width:100%;background:transparent;border:none;outline:none;font:400 14px var(--body);color:var(--text);resize:none;overflow:hidden;line-height:1.6;min-height:60px;"
                  placeholder="Notas de producción, entrega, detalles del proyecto…"
                  oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px';debounceSaveNotas()"><?= e($venta['notas_internas'] ?? '') ?></textarea>
    </div>

</div><!-- /col-main -->

<!-- ══ COLUMNA LATERAL ══ -->
<div class="col-side">

    <!-- RESUMEN FINANCIERO -->
    <div class="fin-card">
        <?php
        $cot_data = $venta['cotizacion_id']
            ? DB::row("SELECT subtotal, cupon_pct, cupon_codigo, descuento_auto_pct, descuento_auto_activo, impuesto_modo, impuesto_pct, impuesto_amt FROM cotizaciones WHERE id=?", [$venta['cotizacion_id']])
            : null;
        ?>
        <?php if ($cot_data): ?>
        <div class="fin-row">
            <span class="fin-lbl">Subtotal</span>
            <span class="fin-val"><?= format_money($cot_data['subtotal'], $empresa['moneda']) ?></span>
        </div>
        <?php if ($cot_data['cupon_pct'] > 0): ?>
        <div class="fin-row">
            <span class="fin-lbl" style="color:var(--amb)">Cupón <?= e($cot_data['cupon_codigo']) ?> (-<?= number_format($cot_data['cupon_pct'],1) ?>%)</span>
            <span class="fin-val" style="color:var(--amb)">-<?= format_money($cot_data['subtotal'] * $cot_data['cupon_pct'] / 100, $empresa['moneda']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($cot_data['descuento_auto_activo'] && $cot_data['descuento_auto_pct'] > 0): ?>
        <div class="fin-row">
            <span class="fin-lbl" style="color:var(--amb)">Desc. especial (-<?= number_format($cot_data['descuento_auto_pct'],1) ?>%)</span>
            <span class="fin-val" style="color:var(--amb)">—</span>
        </div>
        <?php endif; ?>
        <?php if ($cot_data['impuesto_modo'] !== 'ninguno' && $cot_data['impuesto_amt'] > 0): ?>
        <div class="fin-row">
            <span class="fin-lbl"><?= e($empresa['impuesto_label'] ?? 'IVA') ?></span>
            <span class="fin-val"><?= format_money($cot_data['impuesto_amt'], $empresa['moneda']) ?></span>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="fin-row total-row">
            <span class="fin-lbl">Total</span>
            <span class="fin-val"><?= format_money($venta['total'], $empresa['moneda']) ?></span>
        </div>
        <div class="fin-row">
            <span class="fin-lbl" style="color:var(--g)">Pagado</span>
            <span class="fin-val" style="color:var(--g)"><?= format_money($venta['pagado'], $empresa['moneda']) ?></span>
        </div>
        <?php if ($venta['saldo'] > 0): ?>
        <div class="fin-row saldo-row">
            <span class="fin-lbl">Saldo pendiente</span>
            <span class="fin-val"><?= format_money($venta['saldo'], $empresa['moneda']) ?></span>
        </div>
        <?php else: ?>
        <div class="fin-row saldo-ok">
            <span class="fin-lbl">Pagado completo</span>
            <span class="fin-val">✓</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- PROGRESO -->
    <div class="prog-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
            <span style="font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);">Progreso de pago</span>
            <span style="font:700 13px var(--num);color:<?= $pct >= 100 ? 'var(--g)' : 'var(--amb)' ?>;"><?= $pct ?>%</span>
        </div>
        <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;"></div></div>
        <div class="prog-labels">
            <span><?= format_money($venta['pagado'], $empresa['moneda']) ?> pagado</span>
            <span><?= format_money($venta['total'],  $empresa['moneda']) ?> total</span>
        </div>
    </div>

    <!-- ACCIONES -->
    <button class="action-btn primary" onclick="document.getElementById('url-overlay').classList.add('open')">
        Compartir con cliente
    </button>
    <?php if ($puede_abonar && !in_array($venta['estado'], ['cancelada','entregada'])): ?>
    <button class="action-btn" onclick="openSheet('shAbono')">
        + Registrar abono
    </button>
    <?php endif; ?>
    <?php if ($puede_cancelar_venta && $venta['estado'] !== 'cancelada'): ?>
    <button class="action-btn danger-btn" onclick="cancelarVenta()">
        ✕ Cancelar venta
    </button>
    <?php endif; ?>

</div><!-- /col-side -->

</div><!-- /detail-layout -->
</div><!-- /page-wrap -->


<!-- ══ URL OVERLAY ══ -->
<div id="url-overlay"
     style="position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);transition:opacity .25s;display:flex;align-items:flex-end;justify-content:center;opacity:0;pointer-events:none;"
     onclick="if(event.target===this)this.classList.remove('open')">
    <div onclick="event.stopPropagation()" style="background:var(--white);border-radius:20px 20px 0 0;padding:20px 20px 40px;width:100%;max-width:560px;transform:translateY(0);">
        <div style="width:34px;height:4px;border-radius:2px;background:var(--border2);margin:0 auto 18px;"></div>
        <div style="font:800 18px var(--body);margin-bottom:4px;">Compartir con cliente</div>
        <div style="font:400 13px var(--body);color:var(--t3);margin-bottom:12px;">El cliente puede ver su venta, abonos y recibos</div>
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-sm);padding:12px 14px;display:flex;align-items:center;gap:8px;margin-bottom:14px;">
            <span style="flex:1;font:500 12px var(--num);color:var(--g);word-break:break-all;"><?= e($url_vta) ?></span>
            <button onclick="navigator.clipboard.writeText('<?= e($url_vta) ?>');this.textContent='¡Copiado!';setTimeout(()=>this.textContent='Copiar',2000)"
                    style="padding:8px 13px;border-radius:7px;border:none;background:var(--g);font:700 12px var(--body);color:#fff;cursor:pointer;flex-shrink:0;">Copiar</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <a href="https://wa.me/?text=<?= urlencode($url_vta) ?>" target="_blank"
               style="padding:14px;border-radius:var(--r-sm);border:1px solid #a8e6a3;background:#dcf8c6;display:flex;flex-direction:column;align-items:center;gap:5px;text-decoration:none;">
                <span style="font-size:24px;">💬</span>
                <span style="font:700 12px var(--body);color:var(--t2);">WhatsApp</span>
            </a>
            <a href="mailto:<?= e($venta['cliente_email'] ?? '') ?>?subject=Tu+venta+<?= urlencode($venta['numero']) ?>&body=<?= urlencode($url_vta) ?>"
               style="padding:14px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--bg);display:flex;flex-direction:column;align-items:center;gap:5px;text-decoration:none;">
                <span style="font-size:24px;">✉️</span>
                <span style="font:700 12px var(--body);color:var(--t2);">Correo</span>
            </a>
        </div>
    </div>
</div>


<!-- ══ SHEET: ABONO ══ -->
<div class="sh-overlay" id="ov-shAbono" onclick="closeSheet('shAbono')"></div>
<div class="bottom-sheet" id="shAbono">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <div class="sh-title">Registrar abono</div>
        <button class="sh-close" onclick="closeSheet('shAbono')">✕</button>
    </div>
    <div class="sh-body">
        <div class="sh-field">
            <div class="sh-lbl">Forma de pago</div>
            <div class="forma-opts">
                <div class="forma-opt selected" data-forma="efectivo" onclick="selectForma(this)">
                    <span class="forma-opt-ico">💵</span>
                    <span class="forma-opt-lbl">Efectivo</span>
                </div>
                <div class="forma-opt" data-forma="transferencia" onclick="selectForma(this)">
                    <span class="forma-opt-ico">🏦</span>
                    <span class="forma-opt-lbl">Transferencia</span>
                </div>
                <div class="forma-opt" data-forma="tarjeta" onclick="selectForma(this)">
                    <span class="forma-opt-ico">💳</span>
                    <span class="forma-opt-lbl">Tarjeta</span>
                </div>
            </div>
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Monto <span style="color:var(--danger)">*</span></div>
            <input class="sh-input" type="number" id="abono-monto"
                   placeholder="0.00" min="0.01" step="0.01"
                   style="font-family:var(--num);">
            <?php if ($venta['saldo'] > 0): ?>
            <div class="sh-note">Saldo pendiente: <strong><?= format_money($venta['saldo'], $empresa['moneda']) ?></strong></div>
            <?php endif; ?>
            <div class="sh-note">La fecha y hora se registran automáticamente del servidor al guardar.</div>
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Concepto</div>
            <input class="sh-input" type="text" id="abono-concepto" placeholder="Ej. Anticipo 25%, Pago final…">
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Referencia / nota (opcional)</div>
            <input class="sh-input" type="text" id="abono-referencia" placeholder="Ej. BBVA ref. 8823, folio tarjeta…">
        </div>
        <div class="sh-field" style="border-bottom:none;">
            <div style="background:var(--g-bg);border:1px solid var(--g-border);border-radius:var(--r-sm);padding:11px 13px;font:400 13px var(--body);color:var(--g);">
                Al guardar se generará automáticamente el recibo <strong><?= e($sig_rec) ?></strong>
            </div>
        </div>
    </div>
    <div class="sh-footer">
        <button class="sh-btn-cancel" onclick="closeSheet('shAbono')">Cancelar</button>
        <button class="sh-btn-save" onclick="guardarAbono()">Guardar abono</button>
    </div>
</div>


<!-- ══ SHEET: AGREGAR ITEM ══ -->
<?php if ($puede_agregar_items): ?>
<div class="sh-overlay" id="ov-shAgregarItem" onclick="closeSheet('shAgregarItem')"></div>
<div class="bottom-sheet" id="shAgregarItem">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <div class="sh-title">Agregar artículo</div>
        <button class="sh-close" onclick="closeSheet('shAgregarItem')">✕</button>
    </div>
    <div class="sh-body">
        <div class="sh-field">
            <div class="sh-lbl">Nombre <span style="color:var(--danger)">*</span></div>
            <input class="sh-input" type="text" id="item-titulo" placeholder="Nombre del artículo">
        </div>
        <div class="sh-field">
            <div class="sh-lbl">SKU (opcional)</div>
            <input class="sh-input" type="text" id="item-sku" placeholder="Ej. COC-01" style="font-family:var(--num);">
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Descripción (opcional)</div>
            <textarea class="sh-input" id="item-descripcion" style="min-height:60px;resize:none;" placeholder="Descripción…"></textarea>
        </div>
        <div class="sh-field sh-row2">
            <div>
                <div class="sh-lbl">Cantidad</div>
                <input class="sh-input" type="number" id="item-cantidad" value="1" min="0" step="any" style="font-family:var(--num);">
            </div>
            <div>
                <div class="sh-lbl">Precio unitario</div>
                <input class="sh-input" type="number" id="item-precio" placeholder="0.00" min="0" step="any" style="font-family:var(--num);">
            </div>
        </div>
    </div>
    <div class="sh-footer">
        <button class="sh-btn-cancel" onclick="closeSheet('shAgregarItem')">Cancelar</button>
        <button class="sh-btn-save" onclick="agregarItem()">Agregar a la venta</button>
    </div>
</div>
<?php endif; ?>


<!-- ══ SHEET: CANCELAR RECIBO ══ -->
<div class="sh-overlay" id="ov-shCancelarRecibo" onclick="closeSheet('shCancelarRecibo')"></div>
<div class="bottom-sheet" id="shCancelarRecibo">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <div class="sh-title">Cancelar recibo</div>
        <button class="sh-close" onclick="closeSheet('shCancelarRecibo')">✕</button>
    </div>
    <div class="sh-body">
        <div class="sh-field">
            <div style="background:var(--danger-bg);border:1px solid #fca5a5;border-radius:var(--r-sm);padding:13px;font:400 13px var(--body);color:var(--danger);line-height:1.6;" id="cancel-recibo-aviso"></div>
        </div>
        <div class="sh-field">
            <div class="sh-lbl">Motivo de cancelación <span style="color:var(--danger)">*</span></div>
            <textarea class="sh-input" id="cancel-recibo-motivo" style="min-height:70px;resize:none;" placeholder="Explica por qué se cancela este recibo…"></textarea>
            <div class="sh-note">El motivo queda registrado en el historial y es visible para todos los usuarios.</div>
        </div>
    </div>
    <div class="sh-footer">
        <button class="sh-btn-cancel" onclick="closeSheet('shCancelarRecibo')">No cancelar</button>
        <button class="sh-btn-save" style="background:var(--danger);" onclick="confirmarCancelarRecibo()">Confirmar cancelación</button>
    </div>
</div>


<script>
const CSRF_TOKEN  = '<?= csrf_token() ?>';
const VENTA_ID    = <?= $venta_id ?>;
const SALDO       = <?= (float)$venta['saldo'] ?>;
const MONEDA      = '<?= e($empresa['moneda']) ?>';

let _abonoReciboId   = null;
let _abonoReciboNum  = null;
let _abonoReciboMonto = 0;

// ─── Sheets ─────────────────────────────────────────────
function openSheet(id) {
    document.getElementById('ov-' + id).classList.add('open');
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeSheet(id) {
    document.getElementById('ov-' + id)?.classList.remove('open');
    document.getElementById(id)?.classList.remove('open');
    document.body.style.overflow = '';
}

// URL overlay
const urlOverlay = document.getElementById('url-overlay');
urlOverlay.addEventListener('transitionend', () => {
    if (!urlOverlay.classList.contains('open')) {
        urlOverlay.style.opacity = '0'; urlOverlay.style.pointerEvents = 'none';
    }
});
document.addEventListener('DOMContentLoaded', () => {
    urlOverlay.classList.add('_ready');
});
const _origAdd = urlOverlay.classList.add.bind(urlOverlay.classList);
urlOverlay.classList.add = function(cls) {
    _origAdd(cls);
    if (cls === 'open') { urlOverlay.style.opacity='1'; urlOverlay.style.pointerEvents='all'; }
};
const _origRm = urlOverlay.classList.remove.bind(urlOverlay.classList);
urlOverlay.classList.remove = function(cls) {
    _origRm(cls); urlOverlay.style.opacity='0'; urlOverlay.style.pointerEvents='none';
};

// ─── Forma de pago ──────────────────────────────────────
function selectForma(el) {
    document.querySelectorAll('.forma-opt').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
}

// ─── Guardar abono ──────────────────────────────────────
async function guardarAbono() {
    const forma      = document.querySelector('.forma-opt.selected')?.dataset.forma || 'efectivo';
    const monto      = parseFloat(document.getElementById('abono-monto').value);
    const concepto   = document.getElementById('abono-concepto').value.trim();
    const referencia = document.getElementById('abono-referencia').value.trim();

    if (!monto || monto <= 0) { alert('El monto es requerido'); return; }

    const btn = document.querySelector('#shAbono .sh-btn-save');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const r = await fetch('/ventas/' + VENTA_ID + '/abono', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ forma_pago: forma, monto, concepto, referencia })
        });
        const d = await r.json();
        if (!d.ok) { alert(d.error || 'Error al guardar'); btn.disabled=false; btn.textContent='Guardar abono'; return; }
        window.location.reload();
    } catch(e) {
        alert('Error de conexión');
        btn.disabled=false; btn.textContent='Guardar abono';
    }
}

// ─── Cancelar recibo ────────────────────────────────────
function cancelarAbono(id, numero, monto) {
    _abonoReciboId    = id;
    _abonoReciboNum   = numero;
    _abonoReciboMonto = monto;
    const montoFmt = '$' + parseFloat(monto).toLocaleString('es-MX', {minimumFractionDigits:2});
    document.getElementById('cancel-recibo-aviso').innerHTML =
        `⚠️ Esta acción cancelará el recibo <strong>${numero}</strong> por <strong>${montoFmt}</strong>. Se generará automáticamente un recibo de cancelación y el saldo de la venta se ajustará.`;
    document.getElementById('cancel-recibo-motivo').value = '';
    openSheet('shCancelarRecibo');
}

async function confirmarCancelarRecibo() {
    const motivo = document.getElementById('cancel-recibo-motivo').value.trim();
    if (!motivo) { alert('El motivo de cancelación es requerido'); return; }

    const btn = document.querySelector('#shCancelarRecibo .sh-btn-save');
    btn.disabled = true; btn.textContent = 'Cancelando…';

    try {
        const r = await fetch('/ventas/recibos/' + _abonoReciboId + '/cancelar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ motivo })
        });
        const d = await r.json();
        if (!d.ok) { alert(d.error || 'Error'); btn.disabled=false; btn.textContent='Confirmar cancelación'; return; }
        window.location.reload();
    } catch(e) {
        alert('Error de conexión');
        btn.disabled=false; btn.textContent='Confirmar cancelación';
    }
}

// ─── Cambiar estado ─────────────────────────────────────
async function cambiarEstado(nuevo) {
    document.querySelectorAll('.status-opt').forEach(o => o.classList.remove('active-opt'));
    event.target.classList.add('active-opt');

    const r = await fetch('/ventas/' + VENTA_ID + '/estado', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ estado: nuevo })
    });
    const d = await r.json();
    if (!d.ok) { alert(d.error || 'Error'); window.location.reload(); }
}

// ─── Cancelar venta ─────────────────────────────────────
async function cancelarVenta() {
    const motivo = prompt('¿Motivo de cancelación? (requerido)');
    if (!motivo || !motivo.trim()) return;

    const r = await fetch('/ventas/' + VENTA_ID + '/cancelar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ motivo: motivo.trim() })
    });
    const d = await r.json();
    if (d.ok) window.location.reload();
    else alert(d.error || 'Error al cancelar');
}

// ─── Agregar item (admin) ────────────────────────────────
async function agregarItem() {
    const titulo    = document.getElementById('item-titulo').value.trim();
    const sku       = document.getElementById('item-sku').value.trim();
    const desc      = document.getElementById('item-descripcion').value.trim();
    const cantidad  = parseFloat(document.getElementById('item-cantidad').value) || 1;
    const precio    = parseFloat(document.getElementById('item-precio').value)   || 0;
    if (!titulo) { alert('El nombre es requerido'); return; }

    const btn = document.querySelector('#shAgregarItem .sh-btn-save');
    btn.disabled = true; btn.textContent = 'Agregando…';

    const r = await fetch('/ventas/' + VENTA_ID + '/agregar-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ titulo, sku, descripcion: desc, cantidad, precio_unit: precio })
    });
    const d = await r.json();
    if (d.ok) window.location.reload();
    else { alert(d.error || 'Error'); btn.disabled=false; btn.textContent='Agregar a la venta'; }
}

// ─── Notas internas (debounce) ───────────────────────────
let _notaTimer = null;
function debounceSaveNotas() {
    clearTimeout(_notaTimer);
    _notaTimer = setTimeout(async () => {
        const notas = document.getElementById('notas-internas').value;
        await fetch('/ventas/' + VENTA_ID + '/notas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ notas_internas: notas })
        });
    }, 800);
}
</script>
</body>
</html>
