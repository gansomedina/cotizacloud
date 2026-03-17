<?php
// ============================================================
//  CotizaApp — modules/ventas/recibo.php
//  GET /ventas/recibos/:id
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();
$recibo_id  = (int)($id ?? 0);
if (!$recibo_id) redirect('/ventas');

$recibo = DB::row(
    "SELECT r.*,
            v.numero AS venta_numero, v.titulo AS venta_titulo,
            v.total  AS venta_total, v.id AS venta_id,
            cl.nombre AS cliente_nombre, cl.telefono AS cliente_telefono,
            cl.email  AS cliente_email,
            u.nombre  AS usuario_nombre
     FROM recibos r
     JOIN ventas v   ON v.id  = r.venta_id
     LEFT JOIN clientes cl ON cl.id = v.cliente_id
     LEFT JOIN usuarios u  ON u.id  = r.usuario_id
     WHERE r.id = ? AND r.empresa_id = ?",
    [$recibo_id, $empresa_id]
);
if (!$recibo) { flash('error', 'Recibo no encontrado'); redirect('/ventas'); }

$es_cancelacion = $recibo['tipo'] === 'cancelacion';
$cancelado      = (bool)$recibo['cancelado'];
$url_publica    = 'https://' . EMPRESA_SLUG . '.' . BASE_DOMAIN . '/r/' . $recibo['token'];

$page_title = e($recibo['numero']);
ob_start();
?>
<style>
.recibo-actions { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.rbtn { padding:9px 16px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 13px var(--body); color:var(--t2); cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; box-shadow:var(--sh); transition:all .12s; }
.rbtn:hover { border-color:var(--g); color:var(--g); }
.rbtn.primary { background:var(--g); border-color:var(--g); color:#fff; }
.rbtn.primary:hover { opacity:.88; }

.recibo-preview { background:var(--white); border:1px solid var(--border); border-radius:var(--r); padding:28px; box-shadow:var(--sh-md); max-width:480px; margin:0 auto; }
.rp-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; }
.rp-empresa { font:700 14px var(--body); text-align:right; color:var(--text); }
.rp-empresa-sub { font:400 13px var(--body); color:var(--t3); margin-top:2px; text-align:right; }
.rp-title { font:800 22px var(--body); letter-spacing:-.02em; margin-bottom:4px; }
.rp-title.cancelado { color:var(--danger); }
.rp-title.cancelacion { color:var(--purple); }
.rp-num { font:600 13px var(--num); color:var(--t3); margin-bottom:16px; }
.rp-divider { height:1px; background:var(--border); margin:14px 0; }
.rp-row { display:flex; justify-content:space-between; padding:5px 0; font:400 13px var(--body); }
.rp-lbl { color:var(--t2); }
.rp-val { font:500 13px var(--num); color:var(--text); }
.rp-monto-big { display:flex; justify-content:space-between; align-items:center; margin-top:14px; padding:14px 16px; border-radius:var(--r-sm); }
.rp-monto-lbl { font:700 14px var(--body); }
.rp-monto-val { font:800 24px var(--num); }
.rp-footer { margin-top:16px; font:400 13px var(--body); color:var(--t3); text-align:center; line-height:1.6; }
.rp-sello { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:10px; padding:8px; background:var(--bg); border-radius:var(--r-sm); font:600 12px var(--num); color:var(--t3); }
.badge-cancelado { background:var(--danger-bg); color:var(--danger); padding:3px 9px; border-radius:5px; font:700 12px var(--body); }
.badge-cancelacion { background:var(--purple-bg); color:var(--purple); padding:3px 9px; border-radius:5px; font:700 12px var(--body); }

@media print {
    .recibo-actions, .topbar, .sidebar, .layout-sidebar { display:none !important; }
    body { background:#fff; }
    .recibo-preview { box-shadow:none; border:none; max-width:100%; }
}
</style>

<!-- ACCIONES -->
<div class="recibo-actions">
    <a href="/ventas/<?= (int)$recibo['venta_id'] ?>" class="rbtn">← Volver a venta</a>
    <button onclick="window.print()" class="rbtn primary">Imprimir / PDF</button>
    <button onclick="navigator.clipboard.writeText('<?= e($url_publica) ?>').then(()=>{this.textContent='¡Copiado!';setTimeout(()=>this.textContent='Copiar enlace',2000)})" class="rbtn">
        Copiar enlace
    </button>
    <a href="https://wa.me/?text=<?= urlencode($url_publica) ?>" target="_blank" class="rbtn">WhatsApp</a>
</div>

<!-- RECIBO -->
<div class="recibo-preview" id="recibo-imprimir">

    <div class="rp-header">
        <div style="font-size:30px;"><?= $empresa['logo_emoji'] ?? '🏠' ?></div>
        <div>
            <div class="rp-empresa"><?= e($empresa['nombre']) ?></div>
            <div class="rp-empresa-sub"><?= e($empresa['ciudad'] ?? '') ?> · <?= e($empresa['telefono'] ?? '') ?></div>
        </div>
    </div>

    <div class="rp-title <?= $cancelado ? 'cancelado' : ($es_cancelacion ? 'cancelacion' : '') ?>">
        <?= $es_cancelacion ? 'Nota de cancelación' : 'Recibo de pago' ?>
    </div>
    <div class="rp-num" style="display:flex;align-items:center;gap:8px;">
        <?= e($recibo['numero']) ?>
        <?php if ($cancelado): ?><span class="badge-cancelado">Cancelado</span><?php endif; ?>
        <?php if ($es_cancelacion): ?><span class="badge-cancelacion">Cancelación</span><?php endif; ?>
    </div>

    <div class="rp-divider"></div>

    <div class="rp-row">
        <span class="rp-lbl">Cliente</span>
        <span class="rp-val"><?= e($recibo['cliente_nombre'] ?? '—') ?></span>
    </div>
    <div class="rp-row">
        <span class="rp-lbl">Fecha</span>
        <span class="rp-val"><?= date('d M Y, g:i A', strtotime($recibo['created_at'])) ?></span>
    </div>
    <?php if ($recibo['concepto']): ?>
    <div class="rp-row">
        <span class="rp-lbl">Concepto</span>
        <span class="rp-val"><?= e($recibo['concepto']) ?></span>
    </div>
    <?php endif; ?>
    <div class="rp-row">
        <span class="rp-lbl">Venta</span>
        <span class="rp-val" style="color:var(--g);"><?= e($recibo['venta_numero']) ?></span>
    </div>
    <div class="rp-row">
        <span class="rp-lbl">Proyecto</span>
        <span class="rp-val"><?= e($recibo['venta_titulo']) ?></span>
    </div>
    <?php if (!$es_cancelacion): ?>
    <div class="rp-row">
        <span class="rp-lbl">Forma de pago</span>
        <span class="rp-val"><?= ucfirst(e($recibo['forma_pago'])) ?></span>
    </div>
    <?php if ($recibo['referencia']): ?>
    <div class="rp-row">
        <span class="rp-lbl">Referencia</span>
        <span class="rp-val"><?= e($recibo['referencia']) ?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <?php if ($recibo['usuario_nombre']): ?>
    <div class="rp-row">
        <span class="rp-lbl">Atendió</span>
        <span class="rp-val"><?= e($recibo['usuario_nombre']) ?></span>
    </div>
    <?php endif; ?>

    <div class="rp-divider"></div>

    <div class="rp-row">
        <span class="rp-lbl">Total de la venta</span>
        <span class="rp-val"><?= format_money($recibo['venta_total'], $empresa['moneda']) ?></span>
    </div>
    <div class="rp-row">
        <span class="rp-lbl">Pagado anteriormente</span>
        <span class="rp-val"><?= format_money($recibo['pagado_antes'] ?? 0, $empresa['moneda']) ?></span>
    </div>
    <?php $saldo_despues = $recibo['saldo_despues'] ?? 0; ?>
    <div class="rp-row">
        <span class="rp-lbl">Saldo restante</span>
        <span class="rp-val" style="color:<?= $saldo_despues <= 0 ? 'var(--g)' : 'var(--amb)' ?>">
            <?= $saldo_despues <= 0 ? 'Pagado completo ✓' : format_money($saldo_despues, $empresa['moneda']) ?>
        </span>
    </div>

    <?php
    $monto_abs    = abs((float)$recibo['monto']);
    $bg_monto     = $cancelado       ? 'var(--danger-bg)' : ($es_cancelacion ? 'var(--purple-bg)' : 'var(--g-bg)');
    $border_monto = $cancelado       ? '#fca5a5'          : ($es_cancelacion ? '#c4b5fd'          : 'var(--g-border)');
    $color_monto  = $cancelado       ? 'var(--danger)'    : ($es_cancelacion ? 'var(--purple)'    : 'var(--g)');
    $lbl_monto    = $es_cancelacion  ? 'Monto cancelado'  : 'Este pago';
    ?>
    <div class="rp-monto-big" style="background:<?= $bg_monto ?>;border:1px solid <?= $border_monto ?>;">
        <span class="rp-monto-lbl" style="color:<?= $color_monto ?>;"><?= $lbl_monto ?></span>
        <span class="rp-monto-val" style="color:<?= $color_monto ?>;<?= $cancelado ? 'text-decoration:line-through;' : '' ?>">
            <?= $es_cancelacion ? '-' : '' ?><?= format_money($monto_abs, $empresa['moneda']) ?>
        </span>
    </div>

    <?php if ($cancelado && $recibo['cancelado_motivo']): ?>
    <div style="margin-top:12px;padding:10px 13px;background:var(--danger-bg);border:1px solid #fca5a5;border-radius:var(--r-sm);font:400 13px var(--body);color:var(--danger);line-height:1.5;">
        Cancelado: <?= e($recibo['cancelado_motivo']) ?>
    </div>
    <?php endif; ?>

    <?php if ($empresa['recibo_pie'] ?? ''): ?>
    <div class="rp-footer"><?= e($empresa['recibo_pie']) ?></div>
    <?php else: ?>
    <div class="rp-footer"><?= e($empresa['nombre']) ?> · gracias por su preferencia</div>
    <?php endif; ?>

    <div class="rp-sello">✓ <?= e($recibo['numero']) ?> · <?= date('d M Y', strtotime($recibo['created_at'])) ?></div>
</div>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
