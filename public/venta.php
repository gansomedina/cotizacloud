<?php
// ============================================================
//  CotizaApp — public/venta.php
//  GET /v/:slug   (sin login)
// ============================================================

defined('COTIZAAPP') or die;

$slug = $slug ?? '';
if (!$slug) { http_response_code(404); die('No encontrado'); }

// ─── Cargar venta ────────────────────────────────────────
$venta = DB::row(
    "SELECT v.*,
            e.nombre AS emp_nombre, e.ciudad AS emp_ciudad,
            e.telefono AS emp_tel, e.email AS emp_email,
            e.moneda, e.logo_url AS emp_logo, e.vta_terminos AS terminos,
            cl.nombre AS cliente_nombre, cl.telefono AS cli_tel,

            c.numero  AS cot_numero,
            c.cupon_monto AS cupon_amt, c.cupon_codigo, c.cupon_pct,
            c.subtotal AS cot_subtotal,
            c.descuento_auto_amt, c.descuento_auto_pct,
            c.impuesto_pct, c.impuesto_modo, c.impuesto_amt,
            0 AS descuento_manual_amt
     FROM ventas v
     JOIN empresas    e  ON e.id  = v.empresa_id
     LEFT JOIN clientes cl ON cl.id = v.cliente_id

     LEFT JOIN cotizaciones c ON c.id = v.cotizacion_id
     WHERE v.slug = ? AND v.empresa_id = ?",
    [$slug, EMPRESA_ID]
);
if (!$venta) { http_response_code(404); die('Venta no encontrada'); }

// ─── Líneas ──────────────────────────────────────────────
$lineas = DB::query(
    "SELECT * FROM cotizacion_lineas WHERE cotizacion_id = (
        SELECT cotizacion_id FROM ventas WHERE id = ? LIMIT 1
     ) ORDER BY orden ASC",
    [$venta['id']]
);
// Fallback: líneas propias de la venta si existen
if (empty($lineas)) {
    $lineas = DB::query(
        "SELECT * FROM cotizacion_lineas WHERE venta_id = ? ORDER BY orden ASC",
        [$venta['id']]
    );
}

// ─── Recibos activos (abonos no cancelados) ──────────────
$recibos = DB::query(
    "SELECT r.*, u.nombre AS registrado_por
     FROM recibos r
     LEFT JOIN usuarios u ON u.id = r.usuario_id
     WHERE r.venta_id = ?
     ORDER BY r.created_at ASC",
    [$venta['id']]
);

// ─── Helpers ─────────────────────────────────────────────
function fmt_v(float $n): string {
    return '$' . number_format($n, 2, '.', ',');
}
function ini_v(string $nombre): string {
    $p = array_filter(explode(' ', $nombre));
    $i = '';
    foreach (array_slice($p, 0, 2) as $w) $i .= strtoupper($w[0]);
    return $i ?: 'CO';
}

$ini_emp = ini_v($venta['emp_nombre']);
$pagado  = (float)$venta['pagado'];
$saldo   = (float)$venta['saldo'];
$total   = (float)$venta['total'];
$pct_pag = $total > 0 ? min(100, round($pagado / $total * 100)) : 0;

$estado_map = [
    'pendiente' => ['s-pendiente', 'Pendiente'],
    'parcial'   => ['s-parcial',   'Parcialmente pagada'],
    'pagada'    => ['s-pagada',    'Pagada'],
    'entregada' => ['s-entregada', 'Entregada'],
    'cancelada' => ['s-cancelada', 'Cancelada'],
];
[$est_cls, $est_lbl] = $estado_map[$venta['estado']] ?? ['s-pendiente', ucfirst($venta['estado'])];

$ico_forma = ['efectivo'=>'💵','transferencia'=>'🏦','tarjeta'=>'💳'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title>Venta · <?= e($venta['emp_nombre']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f4f4f0;--white:#fff;--border:#e2e2dc;--border2:#c8c8c0;
  --text:#1a1a18;--t2:#4a4a46;--t3:#6a6a64;
  --g:#1a5c38;--g-bg:#eef7f2;--g-border:#b8ddc8;--g-light:#e6f4ed;
  --amb:#92400e;--amb-bg:#fef3c7;
  --blue:#1d4ed8;--blue-bg:#dbeafe;
  --danger:#c53030;--danger-bg:#fff5f5;
  --purple:#6d28d9;--purple-bg:#ede9fe;
  --r:12px;--r-sm:9px;--sh:0 1px 3px rgba(0,0,0,.06);
  --max:800px;--num:'DM Sans',sans-serif;--body:'Plus Jakarta Sans',sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
body{font-family:var(--body);background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}

.web-only{display:block}.print-only{display:none}

/* Header */
.empresa-header{background:var(--white);border-bottom:1px solid var(--border);padding:18px 20px 0}
.empresa-inner{max-width:var(--max);margin:0 auto;text-align:center;padding-bottom:14px}
.empresa-logo{width:56px;height:56px;border-radius:13px;background:var(--g);display:inline-flex;align-items:center;justify-content:center;font:700 20px var(--body);color:#fff;margin-bottom:8px}
.empresa-nombre{font:800 20px var(--body);letter-spacing:-.02em}
.empresa-contacto{display:flex;align-items:center;justify-content:center;gap:14px;margin-top:6px;flex-wrap:wrap}
.empresa-contacto a{font-size:15px;color:var(--t2);text-decoration:none}

.main{max-width:var(--max);margin:0 auto;padding:16px 16px 60px}
.slabel{font:700 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t2);margin:16px 0 7px;display:flex;align-items:center;gap:8px}
.slabel::after{content:'';flex:1;height:1.5px;background:var(--border)}
.slabel:first-child{margin-top:0}
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}

/* Header venta */
.venta-hdr{padding:14px 16px}
.venta-num{font:400 12px var(--num);color:var(--t3);margin-bottom:4px}
.venta-title{font:700 19px var(--body);letter-spacing:-.01em;margin-bottom:10px;line-height:1.3}
.venta-grid{display:grid;grid-template-columns:auto auto auto 1fr;gap:8px 18px;align-items:start}
.meta-lbl{font:600 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);margin-bottom:2px}
.meta-val{font:600 15px var(--body)}
.status{display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:20px;font:700 12px var(--body);justify-self:end;align-self:start;white-space:nowrap}
.status-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.s-parcial{background:var(--amb-bg);color:var(--amb)}.s-parcial .status-dot{background:#f59e0b}
.s-pagada{background:var(--g-light);color:var(--g)}.s-pagada .status-dot{background:var(--g)}
.s-pendiente{background:#f1f5f9;color:#475569}.s-pendiente .status-dot{background:#94a3b8}
.s-entregada{background:var(--blue-bg);color:var(--blue)}.s-entregada .status-dot{background:var(--blue)}
.s-cancelada{background:var(--danger-bg);color:var(--danger)}.s-cancelada .status-dot{background:var(--danger)}

/* Progreso */
.prog-inner{padding:14px 16px}
.prog-top{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:8px}
.prog-lbl{font:600 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);margin-bottom:3px}
.prog-total{font:500 19px var(--num);letter-spacing:-.01em;line-height:1}
.prog-pct{font:500 15px var(--num);color:var(--g)}
.prog-bar{height:6px;border-radius:3px;background:var(--border);overflow:hidden;margin-bottom:10px}
.prog-fill{height:100%;border-radius:3px;background:var(--g)}
.prog-nums{display:flex;justify-content:space-between}
.prog-num-lbl{font:600 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);margin-bottom:1px}
.prog-num-val{font:500 16px var(--num)}
.prog-num-val.pag{color:var(--g)}
.prog-num-val.sal{color:var(--amb)}
.prog-num-r{text-align:right}

/* Artículos */
.item-row{padding:11px 16px;border-bottom:1px solid var(--border);display:flex;gap:10px}
.item-row:last-child{border-bottom:none}
.item-body{flex:1;min-width:0}
.item-name{font:600 16px var(--body);line-height:1.3}
.item-sku{font:400 12px var(--num);color:var(--t3);margin-top:1px}
.item-desc{font-size:14px;color:var(--t2);margin-top:3px;line-height:1.4}
.item-r{text-align:right;flex-shrink:0;min-width:90px}
.item-qty{font:400 12px var(--num);color:var(--t3)}
.item-amt{font:500 15px var(--num);margin-top:2px}

/* Totales */
.tot-row{display:flex;justify-content:space-between;align-items:center;padding:9px 16px;border-bottom:1px solid var(--border)}
.tot-row:last-child{border-bottom:none}
.tot-lbl{font-size:15px;color:var(--t2)}
.tot-val{font:400 15px var(--num)}
.tot-row.disc .tot-lbl,.tot-row.disc .tot-val{color:var(--amb)}
.tot-row.final .tot-lbl{font:700 16px var(--body)}
.tot-row.final .tot-val{font:500 18px var(--num);color:var(--g)}
.tot-row.pag-row .tot-val{color:var(--g)}
.tot-row.sal-row .tot-lbl{font:600 15px var(--body);color:var(--amb)}
.tot-row.sal-row .tot-val{font:600 17px var(--num);color:var(--amb)}

/* Abonos */
.abo-row{padding:12px 16px;border-bottom:1px solid var(--border);display:flex;gap:10px}
.abo-row:last-child{border-bottom:none}
.abo-ico{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;margin-top:2px}
.ico-ef{background:#dcfce7}.ico-tr{background:#dbeafe}.ico-ta{background:#f3e8ff}
.abo-body{flex:1}
.abo-concepto{font:600 16px var(--body)}
.abo-fecha{font:400 13px var(--num);color:var(--t3);margin-top:1px}
.abo-forma{font-size:14px;color:var(--t2);margin-top:3px}
.abo-btns{margin-top:6px}
.abo-folio{display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:var(--bg);border:1px solid var(--border);border-radius:var(--r-sm);font:400 12px var(--num);color:var(--t2);cursor:pointer;transition:all .12s}
.abo-folio:hover{border-color:var(--g);color:var(--g)}
.abo-folio-cancel{color:var(--purple);border-color:var(--purple-bg)}
.abo-folio-cancel:hover{border-color:var(--purple)}
.abo-r{text-align:right;flex-shrink:0}
.abo-monto{font:500 17px var(--num);color:var(--g)}
.abo-cancelado .abo-concepto{text-decoration:line-through;color:var(--t3)}
.abo-cancelado .abo-monto{text-decoration:line-through;color:var(--danger)}
.badge{padding:2px 7px;border-radius:4px;font:700 10px var(--body);vertical-align:middle;margin-left:4px}
.badge-c{background:var(--danger-bg);color:var(--danger)}
.badge-k{background:var(--purple-bg);color:var(--purple)}

/* Botones */
.wa-btn{width:100%;padding:13px;border:none;border-radius:var(--r-sm);font:700 14px var(--body);cursor:pointer;margin-top:8px;display:flex;align-items:center;justify-content:center;gap:7px;background:#25d366;color:#fff;text-decoration:none;transition:opacity .15s}
.wa-btn:hover{opacity:.88}
.pdf-btn{width:100%;padding:13px;border:none;border-radius:var(--r-sm);font:700 14px var(--body);cursor:pointer;margin-top:8px;display:flex;align-items:center;justify-content:center;gap:7px;background:var(--text);color:#fff;transition:opacity .15s}
.pdf-btn:hover{opacity:.88}

/* Términos */
.terminos-card{padding:13px 16px;font-size:14px;color:var(--t3);line-height:1.7}
.page-footer{text-align:center;padding:18px 16px;font-size:13px;color:var(--t3);line-height:1.8;border-top:1px solid var(--border);margin-top:20px}

/* Modal recibo */
.modal-overlay{position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.5);backdrop-filter:blur(6px);opacity:0;pointer-events:none;transition:opacity .25s;display:flex;align-items:flex-end;justify-content:center}
.modal-overlay.open{opacity:1;pointer-events:all}
.rec-sheet{background:var(--white);border-radius:20px 20px 0 0;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;transform:translateY(100%);transition:transform .3s cubic-bezier(.32,0,.15,1);padding-bottom:32px}
.modal-overlay.open .rec-sheet{transform:translateY(0)}
.sh-handle{width:34px;height:4px;border-radius:2px;background:var(--border2);margin:11px auto}
.sh-hdr{display:flex;align-items:center;justify-content:space-between;padding:0 16px 10px;border-bottom:1px solid var(--border)}
.sh-hdr-title{font:700 14px var(--body)}
.sh-close{width:27px;height:27px;border-radius:50%;border:none;background:var(--bg);cursor:pointer;color:var(--t2);font-size:13px;display:flex;align-items:center;justify-content:center}
.rec-body{padding:14px 16px 0}
.rec-emp-row{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px}
.rec-emp-name{font:700 12px var(--body)}
.rec-emp-sub{font-size:10px;color:var(--t3);margin-top:1px}
.rec-h-type{font:700 17px var(--body);letter-spacing:-.01em;margin-bottom:2px}
.rec-h-folio{font:400 11px var(--num);color:var(--t3);margin-bottom:10px}
.rec-divider{height:1px;background:var(--border);margin:8px 0}
.rec-row{display:flex;justify-content:space-between;padding:3px 0;font-size:13px;gap:12px}
.rec-lbl{color:var(--t2)}
.rec-val{font:400 13px var(--num);text-align:right}
.rec-monto-box{display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding:12px 14px;border-radius:var(--r-sm);border:1px solid}
.rec-monto-lbl{font:600 11px var(--body)}
.rec-monto-val{font:300 24px var(--num)}
.rec-foot{margin-top:10px;font-size:10px;color:var(--t3);text-align:center;line-height:1.6}
.rec-sello{margin-top:7px;padding:6px;background:var(--bg);border-radius:var(--r-sm);font:400 9px var(--num);color:var(--t3);text-align:center}
.rec-nodl{margin-top:8px;padding:8px;background:var(--bg);border-radius:var(--r-sm);font-size:11px;color:var(--t3);text-align:center}

@media(max-width:500px){
  .venta-grid{grid-template-columns:1fr 1fr;grid-template-rows:auto auto}
  .venta-grid .status{grid-column:span 2;justify-self:start}
}

/* ── IMPRESIÓN ───────────────────────────────── */
@media print{
  @page{margin:14mm 16mm 14mm 16mm;size:letter}
  .web-only{display:none!important}
  .modal-overlay{display:none!important}
  .print-only{display:block!important}
  body{background:#fff;font-size:10pt}
  .fac{font-family:var(--body)}
  .fac-header{display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:10pt;border-bottom:2pt solid #000;margin-bottom:10pt}
  .fac-emp-name{font:800 17pt var(--body);letter-spacing:-.02em}
  .fac-emp-sub{font:400 9pt var(--body);color:#555;margin-top:2pt;line-height:1.5}
  .fac-doc-tipo{font:300 22pt var(--num);text-align:right}
  .fac-doc-folio{font:400 9pt var(--num);color:#555;text-align:right;margin-top:2pt}
  .fac-info-row{display:flex;border:1pt solid #ccc;border-radius:3pt;overflow:hidden;margin-bottom:8pt}
  .fac-info-cell{flex:1;padding:6pt 10pt;border-right:1pt solid #ccc}
  .fac-info-cell:last-child{border-right:none}
  .fac-info-lbl{font:700 7pt var(--body);letter-spacing:.07em;text-transform:uppercase;color:#444;margin-bottom:2pt}
  .fac-info-val{font:600 10pt var(--body)}
  .fac-status{display:inline-block;padding:2pt 7pt;border:1pt solid #000;border-radius:10pt;font:700 8pt var(--body)}
  .fac-tbl{width:100%;border-collapse:collapse;margin-bottom:0}
  .fac-tbl th{font:700 8pt var(--body);letter-spacing:.07em;text-transform:uppercase;padding:5pt 8pt;border-bottom:1.5pt solid #000;text-align:left}
  .fac-tbl th.r{text-align:right}
  .fac-tbl td{padding:5pt 8pt;border-bottom:.5pt solid #ddd;vertical-align:top;font:400 10pt var(--body)}
  .fac-tbl .td-name{font:600 10pt var(--body)}
  .fac-tbl .td-sku{font:400 8pt var(--num);color:#444}
  .fac-tbl .td-desc{font:400 8.5pt var(--body);color:#555}
  .fac-tbl .td-qty,.fac-tbl .td-pu{font:400 10pt var(--num)}
  .fac-tbl .td-pu,.fac-tbl .td-total{text-align:right}
  .fac-tbl .td-total{font:600 10pt var(--num)}
  .fac-bottom{display:flex;gap:14pt;margin-top:10pt;align-items:flex-start}
  .fac-pagos{flex:1}
  .fac-pagos-title{font:700 8pt var(--body);letter-spacing:.07em;text-transform:uppercase;margin-bottom:4pt;padding-bottom:3pt;border-bottom:1pt solid #ccc}
  .fac-pago-row{display:flex;justify-content:space-between;padding:3pt 0;border-bottom:.5pt solid #eee}
  .fac-pago-row:last-child{border-bottom:none}
  .fac-pago-lbl{font:400 10pt var(--body)}
  .fac-pago-sub{font:400 8.5pt var(--body);color:#444}
  .fac-pago-monto{font:500 10pt var(--num)}
  .fac-pago-cancelado .fac-pago-lbl,.fac-pago-cancelado .fac-pago-monto{text-decoration:line-through;color:#888}
  .fac-totales{width:200pt;flex-shrink:0}
  .fac-tot-row{display:flex;justify-content:space-between;padding:3pt 0;border-bottom:.5pt solid #eee}
  .fac-tot-row:last-child{border-bottom:none}
  .fac-tot-lbl{font:400 10pt var(--body);color:#333}
  .fac-tot-val{font:400 10pt var(--num)}
  .fac-tot-row.final .fac-tot-lbl{font:700 10pt var(--body)}
  .fac-tot-row.final .fac-tot-val{font:500 15pt var(--num)}
  .fac-saldo-box{margin-top:6pt;padding:6pt 8pt;border:1.5pt solid #000;border-radius:3pt}
  .fac-saldo-lbl{font:700 8pt var(--body);letter-spacing:.07em;text-transform:uppercase}
  .fac-saldo-val{font:500 15pt var(--num);margin-top:1pt}
  .fac-pagado-row{display:flex;justify-content:space-between;margin-top:4pt;padding-top:4pt;border-top:.5pt solid #ccc}
  .fac-pagado-lbl{font:400 8pt var(--body);color:#333}
  .fac-pagado-val{font:400 10pt var(--num)}
  .fac-divider{border:none;border-top:1pt solid #ccc;margin:10pt 0 6pt}
  .fac-terminos-lbl{font:700 8pt var(--body);letter-spacing:.07em;text-transform:uppercase;color:#555;margin-bottom:3pt}
  .fac-terminos{font:400 9pt var(--body);color:#444;line-height:1.6}
  .fac-footer{display:flex;justify-content:space-between;margin-top:8pt;padding-top:6pt;border-top:1pt solid #ccc}
  .fac-footer-l,.fac-footer-r{font:400 8.5pt var(--body);color:#444;line-height:1.5}
  .fac-footer-r{text-align:right}
  .fac-nota{font:400 8pt var(--body);color:#555;margin-top:4pt}
}
</style>
</head>
<body>

<!-- ══ VISTA WEB ══ -->
<div class="web-only">

<div class="empresa-header">
  <div class="empresa-inner">
    <?php if (!empty($venta['emp_logo'])): ?>
    <div class="empresa-logo" style="background:none"><img src="<?= e($venta['emp_logo']) ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain;border-radius:inherit"></div>
    <?php else: ?>
    <div class="empresa-logo"><?= e($ini_emp) ?></div>
    <?php endif; ?>
    <div class="empresa-nombre"><?= e($venta['emp_nombre']) ?></div>
    <div class="empresa-contacto">
      <?php if ($venta['emp_tel']): ?>
      <a href="tel:<?= e($venta['emp_tel']) ?>">📞 <?= e($venta['emp_tel']) ?></a>
      <?php endif; ?>
      <?php if ($venta['emp_tel']): ?>
      <span><?= e($venta['emp_tel']) ?></span>
      <?php endif; ?>
      <?php if ($venta['emp_ciudad']): ?>
      <a href="#">📍 <?= e($venta['emp_ciudad']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="main">

  <!-- HEADER VENTA -->
  <div class="slabel">Tu venta</div>
  <div class="card">
    <div class="venta-hdr">
      <div class="venta-num">
        <?= e($venta['numero']) ?>
        <?php if ($venta['cot_numero']): ?> · de <?= e($venta['cot_numero']) ?><?php endif; ?>
      </div>
      <div class="venta-title"><?= e($venta['titulo']) ?></div>
      <div class="venta-grid">
        <div><div class="meta-lbl">Cliente</div><div class="meta-val"><?= e($venta['cliente_nombre'] ?? '—') ?></div></div>
        <div><div class="meta-lbl">Fecha</div><div class="meta-val"><?= date('d M Y', strtotime($venta['created_at'])) ?></div></div>
        <?php if (''): ?>
        <div><div class="meta-lbl">Asesor</div><div class="meta-val"><?= e('') ?></div></div>
        <?php endif; ?>
        <span class="status <?= $est_cls ?>"><span class="status-dot"></span><?= $est_lbl ?></span>
      </div>
    </div>
  </div>

  <!-- PROGRESO DE PAGO -->
  <div class="slabel">Progreso de pago</div>
  <div class="card">
    <div class="prog-inner">
      <div class="prog-top">
        <div>
          <div class="prog-lbl">Total de tu venta</div>
          <div class="prog-total"><?= fmt_v($total) ?></div>
        </div>
        <div class="prog-pct"><?= $pct_pag ?>% pagado</div>
      </div>
      <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct_pag ?>%"></div></div>
      <div class="prog-nums">
        <div>
          <div class="prog-num-lbl">Pagado</div>
          <div class="prog-num-val pag"><?= fmt_v($pagado) ?></div>
        </div>
        <?php if ($saldo > 0): ?>
        <div class="prog-num-r">
          <div class="prog-num-lbl">Saldo pendiente</div>
          <div class="prog-num-val sal"><?= fmt_v($saldo) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ARTÍCULOS -->
  <?php if (!empty($lineas)): ?>
  <div class="slabel">Artículos</div>
  <div class="card">
    <!-- Header columnas -->
    <div style="display:grid;grid-template-columns:1fr 60px 80px 80px;gap:6px;padding:7px 16px;border-bottom:1px solid var(--border);background:var(--bg)">
      <span style="font:700 10px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3)">Descripción</span>
      <span style="font:700 10px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);text-align:center">Cant.</span>
      <span style="font:700 10px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);text-align:right">P.Unit.</span>
      <span style="font:700 10px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);text-align:right">Total</span>
    </div>


    <?php foreach ($lineas as $l): ?>
    <div style="display:grid;grid-template-columns:1fr 60px 80px 80px;gap:6px;align-items:start;padding:11px 16px;border-bottom:1px solid var(--border)">
      <div>
        <div class="item-name"><?= e($l['titulo']) ?></div>
        <?php if ($l['sku']): ?><div class="item-sku"><?= e($l['sku']) ?></div><?php endif; ?>
        <?php if ($l['descripcion']): ?><div class="item-desc"><?= nl2br(e($l['descripcion'])) ?></div><?php endif; ?>
      </div>
      <div style="text-align:center;font:400 14px var(--num);color:var(--t2);padding-top:2px">
        <?= rtrim(rtrim(number_format($l['cantidad'],4),'0'),'.') ?>
      </div>
      <div style="text-align:right;font:400 14px var(--num);color:var(--t2);padding-top:2px">
        <?= $l['precio_unit'] > 0 ? fmt_v($l['precio_unit']) : '—' ?>
      </div>
      <div style="text-align:right;font:600 14px var(--num);padding-top:2px">
        <?= fmt_v($l['subtotal']) ?>
      </div>
    </div>
    <?php endforeach; ?>
    <!-- Total de artículos -->
    <div style="display:flex;justify-content:flex-end;padding:9px 16px;background:var(--bg)">
      <span style="font:700 14px var(--num)">Subtotal: <?= fmt_v(array_sum(array_column($lineas,'subtotal'))) ?></span>
    </div>
  </div>
  <?php endif; ?>

  <!-- RESUMEN TOTALES -->
  <div class="slabel">Resumen</div>
  <div class="card">
    <?php
    $subtotal_v = (float)($venta['cot_subtotal'] ?? 0);
    if ($subtotal_v <= 0) $subtotal_v = array_sum(array_column($lineas,'subtotal'));
    ?>
    <div class="tot-row"><span class="tot-lbl">Subtotal</span><span class="tot-val"><?= fmt_v($subtotal_v) ?></span></div>
    <?php if ((float)($venta['cupon_amt'] ?? 0) > 0): ?>
    <div class="tot-row disc"><span class="tot-lbl">Cupón <?= $venta['cupon_codigo'] ? '('.e($venta['cupon_codigo']).')' : '' ?> <?= $venta['cupon_pct'] > 0 ? $venta['cupon_pct'].'%' : '' ?></span><span class="tot-val">-<?= fmt_v($venta['cupon_amt']) ?></span></div>
    <?php endif; ?>
    <?php if ((float)($venta['descuento_auto_amt'] ?? 0) > 0): ?>
    <div class="tot-row disc"><span class="tot-lbl">Descuento<?= ($venta['descuento_auto_pct'] ?? 0) > 0 ? ' ('.$venta['descuento_auto_pct'].'%)' : '' ?></span><span class="tot-val">-<?= fmt_v((float)$venta['descuento_auto_amt']) ?></span></div>
    <?php endif; ?>
    <div class="tot-row final"><span class="tot-lbl">Total</span><span class="tot-val"><?= fmt_v($total) ?></span></div>
    <div class="tot-row pag-row"><span class="tot-lbl">Pagado</span><span class="tot-val"><?= fmt_v($pagado) ?></span></div>
    <?php if ($saldo > 0): ?>
    <div class="tot-row sal-row"><span class="tot-lbl">Saldo pendiente</span><span class="tot-val"><?= fmt_v($saldo) ?></span></div>
    <?php endif; ?>
  </div>

  <!-- HISTORIAL DE PAGOS -->
  <?php if (!empty($recibos)): ?>
  <div class="slabel">Historial de pagos</div>
  <div class="card">
    <?php foreach ($recibos as $r):
        // notas guarda forma+referencia ej: "Transferencia · REF123" o solo concepto
        $es_cancelado   = (bool)$r['cancelado'];
        $es_cancelacion = false; // columna tipo no existe, nunca es cancelacion
        $notas_r        = $r['notas'] ?? '';
        // Detectar forma desde notas (primer token antes de ' · ' o default efectivo)
        $partes_nota    = explode(' · ', $notas_r, 2);
        $forma_raw      = strtolower(trim($partes_nota[0]));
        $formas_validas = ['efectivo','transferencia','tarjeta'];
        $forma          = in_array($forma_raw, $formas_validas) ? $forma_raw : 'efectivo';
        $ref_nota       = $partes_nota[1] ?? '';
        $ico_cls        = $forma === 'efectivo' ? 'ico-ef' : ($forma === 'tarjeta' ? 'ico-ta' : 'ico-tr');
        $ico_emoji      = $ico_forma[$forma] ?? '💵';

        // Datos para el modal
        $rec_data = json_encode([
            'folio'     => $r['numero'],
            'concepto'  => $r['concepto'] ?? 'Pago',
            'monto'     => fmt_v(abs((float)$r['monto'])),
            'forma'     => $notas_r ?: ucfirst($forma),
            'ref'       => $ref_nota ?: '—',
            'fecha'     => date('d M Y, g:i A', strtotime($r['created_at'])),
            'prev'      => '—',
            'saldo'     => '—',
            'cancelacion' => false,
        ]);
        $rec_cancel_data = null;
    ?>
    <div class="abo-row <?= ($es_cancelado || $es_cancelacion) ? 'abo-cancelado' : '' ?>">
      <div class="abo-ico <?= $ico_cls ?>" style="<?= ($es_cancelado||$es_cancelacion)?'opacity:.35':'' ?>"><?= $ico_emoji ?></div>
      <div class="abo-body">
        <div class="abo-concepto">
          <?= e($r['concepto'] ?? 'Pago') ?>
          <?php if ($es_cancelado): ?><span class="badge badge-c">Cancelado</span><?php endif; ?>
          <?php if ($es_cancelacion): ?><span class="badge badge-k">Cancelación</span><?php endif; ?>
        </div>
        <div class="abo-fecha"><?= date('d M Y · g:i A', strtotime($r['created_at'])) ?></div>
        <div class="abo-forma"><?= $notas_r ?: ucfirst($forma) ?></div>
        <div class="abo-btns">
          <div class="abo-folio <?= $es_cancelacion?'abo-folio-cancel':'' ?>"
               onclick="openRec(<?= htmlspecialchars($rec_data, ENT_QUOTES) ?>)">
               🧾 Ver recibo <?= e($r['numero']) ?>
          </div>
          <?php if ($rec_cancel_data): ?>
          <div class="abo-folio abo-folio-cancel" style="margin-left:6px"
               onclick="openRec(<?= htmlspecialchars($rec_cancel_data, ENT_QUOTES) ?>)">
               Cancelación →
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="abo-r">
        <div class="abo-monto" style="<?= $es_cancelacion?'color:var(--purple)':($es_cancelado?'color:var(--danger)':'color:var(--g)') ?>">
          <?= $es_cancelacion ? '-' : '' ?><?= fmt_v(abs($r['monto'])) ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- BOTONES -->
  <button class="pdf-btn" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>

  <!-- TÉRMINOS -->
  <?php if ($venta['terminos']): ?>
  <div class="slabel" style="margin-top:20px">Términos</div>
  <div class="card">
    <div class="terminos-card"><?= nl2br(e($venta['terminos'])) ?></div>
  </div>
  <?php endif; ?>

  <div class="page-footer">
    <?= e($venta['emp_nombre']) ?> · <?= e($venta['emp_ciudad'] ?? '') ?>
    <?php if ($venta['emp_tel']): ?><br><?= e($venta['emp_tel']) ?><?php endif; ?>
  </div>

</div><!-- /main -->
</div><!-- /web-only -->


<!-- ══ VERSIÓN IMPRESA ══ -->
<div class="print-only">
<div class="fac">

  <div class="fac-header">
    <div>
      <div class="fac-emp-name"><?= e($venta['emp_nombre']) ?></div>
      <div class="fac-emp-sub">
        <?= e($venta['emp_ciudad'] ?? '') ?>
        <?php if ($venta['emp_tel']): ?> · <?= e($venta['emp_tel']) ?><?php endif; ?>
        <?php if ($venta['emp_email']): ?><br><?= e($venta['emp_email']) ?><?php endif; ?>
      </div>
    </div>
    <div>
      <div class="fac-doc-tipo">Venta</div>
      <div class="fac-doc-folio"><?= e($venta['numero']) ?></div>
    </div>
  </div>

  <div class="fac-info-row">
    <div class="fac-info-cell"><div class="fac-info-lbl">Cliente</div><div class="fac-info-val"><?= e($venta['cliente_nombre'] ?? '—') ?></div></div>
    <?php if ($venta['cli_tel']): ?><div class="fac-info-cell"><div class="fac-info-lbl">Teléfono</div><div class="fac-info-val"><?= e($venta['cli_tel']) ?></div></div><?php endif; ?>
    <div class="fac-info-cell"><div class="fac-info-lbl">Fecha</div><div class="fac-info-val"><?= date('d M Y', strtotime($venta['created_at'])) ?></div></div>
    <?php if (''): ?><div class="fac-info-cell"><div class="fac-info-lbl">Asesor</div><div class="fac-info-val"><?= e('') ?></div></div><?php endif; ?>
    <div class="fac-info-cell"><div class="fac-info-lbl">Estado</div><div class="fac-info-val"><span class="fac-status"><?= $est_lbl ?></span></div></div>
  </div>

  <?php if (!empty($lineas)): ?>
  <table class="fac-tbl">
    <thead><tr><th style="width:16pt">#</th><th>Descripción</th><th class="r" style="width:60pt">Cant.</th><th class="r" style="width:70pt">P. Unit.</th><th class="r" style="width:80pt">Total</th></tr></thead>
    <tbody>
    <?php foreach ($lineas as $i => $l): ?>
    <tr>
      <td style="color:#333"><?= $i+1 ?></td>
      <td>
        <div class="td-name"><?= e($l['titulo']) ?></div>
        <?php if ($l['sku']): ?><div class="td-sku"><?= e($l['sku']) ?></div><?php endif; ?>
        <?php if ($l['descripcion']): ?><div class="td-desc"><?= nl2br(e($l['descripcion'])) ?></div><?php endif; ?>
      </td>
      <td class="td-qty"><?= number_format($l['cantidad'],2) ?> pz.</td>
      <td class="td-pu"><?= $l['precio_unit'] > 0 ? fmt_v($l['precio_unit']) : '—' ?></td>
      <td class="td-total"><?= $l['precio_unit'] > 0 ? fmt_v($l['subtotal']) : fmt_v(0) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <div class="fac-bottom">
    <div class="fac-pagos">
      <div class="fac-pagos-title">Historial de pagos</div>
      <?php foreach ($recibos as $r): $es_c = (bool)$r['cancelado']; ?>
      <div class="fac-pago-row <?= $es_c?'fac-pago-cancelado':'' ?>">
        <div>
          <div class="fac-pago-lbl"><?= e($r['concepto'] ?? 'Pago') ?> — <?= e($r['numero']) ?><?= $es_c?' (cancelado)':'' ?></div>
           <div class="fac-pago-sub"><?= date('d M Y', strtotime($r['created_at'])) ?><?= ($r['notas'] ?? '') ? ' · '.e($r['notas']) : '' ?></div>
        </div>
        <div class="fac-pago-monto"><?= fmt_v(abs($r['monto'])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="fac-totales">
      <div class="fac-tot-row"><span class="fac-tot-lbl">Subtotal</span><span class="fac-tot-val"><?= fmt_v($subtotal_v) ?></span></div>
      <?php if ((float)($venta['cupon_amt'] ?? 0) > 0): ?>
      <div class="fac-tot-row"><span class="fac-tot-lbl">Cupón<?= $venta['cupon_codigo'] ? ' ('.$venta['cupon_codigo'].')' : '' ?></span><span class="fac-tot-val" style="color:#c05">-<?= fmt_v($venta['cupon_amt']) ?></span></div>
      <?php endif; ?>
      <?php if ((float)($venta['descuento_auto_amt'] ?? 0) > 0): ?>
      <div class="fac-tot-row"><span class="fac-tot-lbl">Descuento<?= ($venta['descuento_auto_pct'] ?? 0) > 0 ? ' ('.$venta['descuento_auto_pct'].'%)' : '' ?></span><span class="fac-tot-val" style="color:#c05">-<?= fmt_v((float)$venta['descuento_auto_amt']) ?></span></div>
      <?php endif; ?>
      <div class="fac-tot-row final"><span class="fac-tot-lbl">Total</span><span class="fac-tot-val"><?= fmt_v($total) ?></span></div>
      <div class="fac-saldo-box">
        <?php if ($saldo > 0): ?>
        <div class="fac-saldo-lbl">Saldo pendiente</div>
        <div class="fac-saldo-val"><?= fmt_v($saldo) ?></div>
        <?php else: ?>
        <div class="fac-saldo-lbl">Estado</div>
        <div class="fac-saldo-val" style="font-size:12pt">Pagada ✓</div>
        <?php endif; ?>
        <div class="fac-pagado-row">
          <span class="fac-pagado-lbl">Pagado</span>
          <span class="fac-pagado-val"><?= fmt_v($pagado) ?></span>
        </div>
      </div>
    </div>
  </div>

  <?php if ($venta['terminos']): ?>
  <hr class="fac-divider">
  <div class="fac-terminos-lbl">Términos y condiciones</div>
  <div class="fac-terminos"><?= e(mb_substr(strip_tags($venta['terminos']),0,400)) ?></div>
  <?php endif; ?>

  <div class="fac-footer">
    <div class="fac-footer-l"><?= e($venta['emp_nombre']) ?> · <?= e($venta['emp_ciudad']??'') ?><?php if($venta['emp_tel']): ?><br><?= e($venta['emp_tel']) ?><?php endif; ?></div>
    <div class="fac-footer-r"><?= e($venta['numero']) ?> · generado con CotizaApp<br>Impreso: <?= date('d/m/Y') ?></div>
  </div>
  <div class="fac-nota">Este documento es un comprobante interno de venta. No es una factura fiscal.</div>

</div>
</div><!-- /print-only -->


<!-- MODAL RECIBO en pantalla -->
<div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeRec()">
  <div class="rec-sheet">
    <div class="sh-handle"></div>
    <div class="sh-hdr">
      <div class="sh-hdr-title" id="rec-sh-title">Recibo</div>
      <button class="sh-close" onclick="closeRec()">✕</button>
    </div>
    <div class="rec-body">
      <div class="rec-emp-row">
        <?php if (!empty($venta['emp_logo'])): ?>
        <img src="<?= e($venta['emp_logo']) ?>" alt="Logo" style="height:36px;object-fit:contain">
        <?php else: ?>
        <div style="font:700 18px var(--body)"><?= e($ini_emp) ?></div>
        <?php endif; ?>
        <div style="text-align:right">
          <div class="rec-emp-name"><?= e($venta['emp_nombre']) ?></div>
          <div class="rec-emp-sub"><?= e($venta['emp_ciudad']??'') ?><?php if($venta['emp_tel']): ?> · <?= e($venta['emp_tel']) ?><?php endif; ?></div>
        </div>
      </div>
      <div class="rec-h-type" id="rec-type">Recibo de pago</div>
      <div class="rec-h-folio" id="rec-folio">—</div>
      <div class="rec-divider"></div>
      <div class="rec-row"><span class="rec-lbl">Cliente</span><span class="rec-val"><?= e($venta['cliente_nombre'] ?? '—') ?></span></div>
      <div class="rec-row"><span class="rec-lbl">Fecha</span><span class="rec-val" id="rec-fecha">—</span></div>
      <div class="rec-row"><span class="rec-lbl">Concepto</span><span class="rec-val" id="rec-concepto">—</span></div>
      <div class="rec-row"><span class="rec-lbl">Venta</span><span class="rec-val"><?= e($venta['numero']) ?></span></div>
      <div class="rec-row"><span class="rec-lbl">Proyecto</span><span class="rec-val"><?= e(mb_substr($venta['titulo'],0,50)) ?></span></div>
      <div class="rec-row"><span class="rec-lbl">Forma de pago</span><span class="rec-val" id="rec-forma">—</span></div>
      <div class="rec-row"><span class="rec-lbl">Referencia</span><span class="rec-val" id="rec-ref">—</span></div>
      <div class="rec-divider"></div>
      <div class="rec-row"><span class="rec-lbl">Total de la venta</span><span class="rec-val"><?= fmt_v($total) ?></span></div>
      <div class="rec-row"><span class="rec-lbl">Pagado anteriormente</span><span class="rec-val" id="rec-prev">—</span></div>
      <div class="rec-row"><span class="rec-lbl">Saldo restante</span><span class="rec-val" id="rec-saldo">—</span></div>
      <div class="rec-monto-box" id="rec-monto-box">
        <span class="rec-monto-lbl" id="rec-monto-lbl">Este pago</span>
        <span class="rec-monto-val" id="rec-monto">—</span>
      </div>
      <div class="rec-foot"><?= e($venta['emp_nombre']) ?> · gracias por su preferencia</div>
      <div class="rec-sello" id="rec-sello">—</div>
      <div class="rec-nodl">🔒 Para obtener el PDF, pide a tu asesor que te lo envíe.</div>
    </div>
  </div>
</div>

<script>
function openRec(d){
    const r = (typeof d === 'string') ? JSON.parse(d) : d;
    const isCancel = r.cancelacion;
    document.getElementById('rec-sh-title').textContent = r.folio;
    document.getElementById('rec-folio').textContent    = r.folio;
    document.getElementById('rec-type').textContent     = isCancel ? 'Recibo de cancelación' : 'Recibo de pago';
    document.getElementById('rec-concepto').textContent = r.concepto;
    document.getElementById('rec-monto').textContent    = r.monto;
    document.getElementById('rec-forma').textContent    = r.forma;
    document.getElementById('rec-ref').textContent      = r.ref;
    document.getElementById('rec-fecha').textContent    = r.fecha;
    document.getElementById('rec-prev').textContent     = r.prev;
    document.getElementById('rec-saldo').textContent    = r.saldo;
    document.getElementById('rec-sello').textContent    = '✓ ' + r.folio + ' · ' + r.fecha;
    document.getElementById('rec-monto-lbl').textContent = isCancel ? 'Monto cancelado' : 'Este pago';
    const box = document.getElementById('rec-monto-box');
    const c = isCancel ? 'var(--purple)' : 'var(--g)';
    box.style.background  = isCancel ? 'var(--purple-bg)' : 'var(--g-bg)';
    box.style.borderColor = isCancel ? 'var(--purple)' : 'var(--g-border)';
    document.getElementById('rec-monto').style.color       = c;
    document.getElementById('rec-monto-lbl').style.color   = c;
    document.getElementById('modalOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeRec(){
    document.getElementById('modalOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
</script>
</body>
</html>
