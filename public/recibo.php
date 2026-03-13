<?php
// ============================================================
//  CotizaApp — public/recibo.php
//  GET /r/:token  (sin login)
// ============================================================

defined('COTIZAAPP') or die;

$token = $token ?? '';
if (!$token) { http_response_code(404); die('No encontrado'); }

// ─── Cargar recibo ───────────────────────────────────────
$rec = DB::row(
    "SELECT r.*,
            v.numero  AS venta_numero, v.titulo   AS venta_titulo,
            v.total   AS venta_total,
            e.nombre  AS emp_nombre, e.ciudad    AS emp_ciudad,
            e.telefono AS emp_tel,   e.email     AS emp_email,
            cl.nombre AS cli_nombre, cl.telefono AS cli_tel,
            u.nombre  AS reg_por
     FROM recibos r
     JOIN ventas    v  ON v.id  = r.venta_id
     JOIN empresas  e  ON e.id  = r.empresa_id
     LEFT JOIN clientes cl ON cl.id = v.cliente_id
     LEFT JOIN usuarios  u  ON u.id = r.usuario_id
     WHERE r.token = ? AND r.empresa_id = ?",
    [$token, EMPRESA_ID]
);
if (!$rec) { http_response_code(404); die('Recibo no encontrado'); }

// ─── Helpers ─────────────────────────────────────────────
function fmt_r(float $n): string {
    return '$' . number_format(abs($n), 2, '.', ',');
}
function ini_r(string $nombre): string {
    $p = array_filter(explode(' ', $nombre));
    $i = '';
    foreach (array_slice($p,0,2) as $w) $i .= strtoupper($w[0]);
    return $i ?: 'CO';
}

$es_cancelacion = $rec['tipo'] === 'cancelacion';
$es_cancelado   = (bool)$rec['cancelado'];
$monto          = (float)$rec['monto'];
$pagado_antes   = (float)($rec['pagado_antes']   ?? 0);
$saldo_despues  = (float)($rec['saldo_despues']  ?? 0);
$venta_total    = (float)$rec['venta_total'];
$pagado_despues = $venta_total - $saldo_despues;

// Colores según tipo
if ($es_cancelado) {
    $color = '#c53030'; $bg = '#fff5f5'; $bd = '#fca5a5';
    $tipo_lbl = 'Cancelado';
} elseif ($es_cancelacion) {
    $color = '#6d28d9'; $bg = '#ede9fe'; $bd = '#c4b5fd';
    $tipo_lbl = 'Cancelación de pago';
} else {
    $color = '#1a5c38'; $bg = '#eef7f2'; $bd = '#b8ddc8';
    $tipo_lbl = 'Recibo de pago';
}

$forma_lbl = [
    'efectivo'     => 'Efectivo',
    'transferencia'=> 'Transferencia bancaria',
    'tarjeta'      => 'Tarjeta',
][$rec['forma_pago'] ?? 'efectivo'] ?? ucfirst($rec['forma_pago'] ?? '');

$ini_emp = ini_r($rec['emp_nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title><?= e($rec['numero']) ?> · <?= e($rec['emp_nombre']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f4f4f0;--white:#fff;--border:#e2e2dc;
  --text:#1a1a18;--t2:#4a4a46;--t3:#6a6a64;
  --g:#1a5c38;--g-bg:#eef7f2;--g-border:#b8ddc8;
  --r:12px;--r-sm:9px;--sh:0 1px 3px rgba(0,0,0,.06);
  --max:480px;--num:'DM Sans',sans-serif;--body:'Plus Jakarta Sans',sans-serif;
  --accent:<?= $color ?>;--accent-bg:<?= $bg ?>;--accent-bd:<?= $bd ?>;
}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
body{font-family:var(--body);background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased;min-height:100vh}

.page{max-width:var(--max);margin:0 auto;padding:20px 16px 60px}

/* HEADER EMPRESA */
.emp-hdr{text-align:center;padding:20px 16px 16px;background:var(--white);border-bottom:1px solid var(--border);margin:-20px -16px 20px}
.emp-logo{width:52px;height:52px;border-radius:13px;background:var(--g);display:inline-flex;align-items:center;justify-content:center;font:700 18px var(--body);color:#fff;margin-bottom:8px}
.emp-nombre{font:800 18px var(--body);letter-spacing:-.02em}
.emp-contact{font-size:13px;color:var(--t3);margin-top:4px;line-height:1.7}
.emp-contact a{color:var(--t3);text-decoration:none}

/* TIPO DE RECIBO */
.rec-type-banner{text-align:center;margin-bottom:16px}
.rec-tipo{display:inline-block;padding:5px 14px;border-radius:20px;font:700 12px var(--body);background:var(--accent-bg);color:var(--accent);border:1px solid var(--accent-bd)}
.rec-folio{font:400 13px var(--num);color:var(--t3);margin-top:4px}
<?php if ($es_cancelado): ?>
.rec-folio::before{content:'⚠ ';color:var(--accent)}
<?php endif; ?>

/* MONTO PRINCIPAL */
.monto-box{background:var(--accent-bg);border:2px solid var(--accent-bd);border-radius:var(--r);padding:20px 16px;text-align:center;margin-bottom:16px}
.monto-lbl{font:700 10px var(--body);letter-spacing:.09em;text-transform:uppercase;color:var(--accent);margin-bottom:6px}
.monto-val{font:300 42px var(--num);color:var(--accent);letter-spacing:-.02em;line-height:1}
.monto-tachado{text-decoration:line-through;opacity:.5}
.monto-fecha{font:400 12px var(--num);color:var(--accent);opacity:.7;margin-top:6px}

/* CARDS DE DETALLE */
.det-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:12px}
.det-row{display:flex;justify-content:space-between;align-items:baseline;padding:10px 14px;border-bottom:1px solid var(--border);gap:12px}
.det-row:last-child{border-bottom:none}
.det-lbl{font:400 13px var(--body);color:var(--t2);flex-shrink:0}
.det-val{font:500 14px var(--num);color:var(--text);text-align:right}
.det-row.section-title{background:var(--bg);padding:7px 14px}
.det-row.section-title .det-lbl{font:700 10px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3)}

/* DESGLOSE SALDO */
.saldo-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:12px}
.sal-row{display:flex;justify-content:space-between;align-items:center;padding:9px 14px;border-bottom:1px solid var(--border)}
.sal-row:last-child{border-bottom:none}
.sal-lbl{font:400 13px var(--body);color:var(--t2)}
.sal-val{font:500 14px var(--num);color:var(--text)}
.sal-row.antes .sal-val{color:var(--t3)}
.sal-row.este   .sal-lbl{font:600 13px var(--body)}
.sal-row.este   .sal-val{font:600 15px var(--num);color:var(--accent)}
.sal-row.despues .sal-lbl{font:700 13px var(--body)}
.sal-row.despues .sal-val{font:600 16px var(--num)}

/* SELLO */
.sello{background:var(--bg);border:1px dashed var(--border);border-radius:var(--r-sm);padding:10px 14px;text-align:center;font:400 10px var(--num);color:var(--t3);margin-bottom:12px;line-height:1.7}

/* BOTONES */
.btn-row{display:flex;gap:8px;margin-bottom:10px}
.btn-print{flex:1;padding:12px;border:none;border-radius:var(--r-sm);font:700 13px var(--body);cursor:pointer;background:var(--text);color:#fff;transition:opacity .15s}
.btn-print:hover{opacity:.85}
.btn-wa{flex:1;padding:12px;border:none;border-radius:var(--r-sm);font:700 13px var(--body);cursor:pointer;background:#25d366;color:#fff;text-decoration:none;display:flex;align-items:center;justify-content:center;transition:opacity .15s}
.btn-wa:hover{opacity:.85}
<?php if ($es_cancelado): ?>
.cancelado-aviso{padding:12px 14px;background:var(--accent-bg);border:1px solid var(--accent-bd);border-radius:var(--r-sm);font-size:13px;color:var(--accent);text-align:center;margin-bottom:12px}
<?php endif; ?>

/* Nota pie */
.pie{text-align:center;font-size:12px;color:var(--t3);line-height:1.7;margin-top:12px}
.pie a{color:var(--t3);text-decoration:none}

/* ── PRINT ───────────────────── */
@media print{
  @page{margin:10mm 14mm;size:letter portrait}
  *{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  body{background:#fff}
  .btn-row,.pie{display:none!important}
  .page{padding:0;max-width:100%}
  .emp-hdr{margin:0 0 12pt;padding:0 0 8pt;border-bottom:1.5pt solid #000;background:transparent}
  .emp-logo{width:34px;height:34px;font-size:13px;background:#111!important}
  .emp-nombre{font-size:14pt}
  .emp-contact{font-size:9pt}
  .monto-box{border:1.5pt solid #000;padding:12pt}
  .monto-val{font-size:30pt}
  .det-card,.saldo-card{border:1pt solid #ccc;border-radius:3pt;box-shadow:none}
  .det-row,.sal-row{padding:5pt 10pt}
  .det-lbl,.sal-lbl{font-size:9pt}
  .det-val,.sal-val{font-size:9pt}
  .sello{border:1pt dashed #aaa;padding:6pt 10pt;font-size:8pt}
}
</style>
</head>
<body>
<div class="page">

  <!-- HEADER EMPRESA -->
  <div class="emp-hdr">
    <div class="emp-logo"><?= e($ini_emp) ?></div>
    <div class="emp-nombre"><?= e($rec['emp_nombre']) ?></div>
    <div class="emp-contact">
      <?= e($rec['emp_ciudad'] ?? '') ?>
      <?php if ($rec['emp_tel']): ?> · <a href="tel:<?= e($rec['emp_tel']) ?>"><?= e($rec['emp_tel']) ?></a><?php endif; ?>
      <?php if ($rec['emp_email']): ?><br><a href="mailto:<?= e($rec['emp_email']) ?>"><?= e($rec['emp_email']) ?></a><?php endif; ?>
    </div>
  </div>

  <!-- TIPO + FOLIO -->
  <div class="rec-type-banner">
    <div class="rec-tipo"><?= $tipo_lbl ?></div>
    <div class="rec-folio"><?= e($rec['numero']) ?><?= $es_cancelado ? ' · CANCELADO' : '' ?></div>
  </div>

  <!-- CANCELADO AVISO -->
  <?php if ($es_cancelado): ?>
  <div class="cancelado-aviso">
    ⚠ Este recibo fue cancelado y no representa un pago válido.
  </div>
  <?php endif; ?>

  <!-- MONTO PRINCIPAL -->
  <div class="monto-box">
    <div class="monto-lbl"><?= $es_cancelacion ? 'Monto cancelado' : 'Monto pagado' ?></div>
    <div class="monto-val <?= $es_cancelado ? 'monto-tachado' : '' ?>"><?= fmt_r($monto) ?></div>
    <div class="monto-fecha"><?= date('d \d\e M \d\e Y, g:i A', strtotime($rec['created_at'])) ?></div>
  </div>

  <!-- DETALLE -->
  <div class="det-card">
    <div class="det-row section-title"><span class="det-lbl">Información del pago</span></div>
    <div class="det-row"><span class="det-lbl">Cliente</span><span class="det-val"><?= e($rec['cli_nombre'] ?? '—') ?></span></div>
    <?php if ($rec['cli_tel']): ?>
    <div class="det-row"><span class="det-lbl">Teléfono</span><span class="det-val"><?= e($rec['cli_tel']) ?></span></div>
    <?php endif; ?>
    <div class="det-row"><span class="det-lbl">Concepto</span><span class="det-val"><?= e($rec['concepto'] ?? 'Pago') ?></span></div>
    <div class="det-row"><span class="det-lbl">Forma de pago</span><span class="det-val"><?= $forma_lbl ?></span></div>
    <?php if ($rec['referencia']): ?>
    <div class="det-row"><span class="det-lbl">Referencia</span><span class="det-val"><?= e($rec['referencia']) ?></span></div>
    <?php endif; ?>
    <div class="det-row section-title"><span class="det-lbl">Referencia de venta</span></div>
    <div class="det-row"><span class="det-lbl">Venta</span><span class="det-val"><?= e($rec['venta_numero']) ?></span></div>
    <div class="det-row"><span class="det-lbl">Proyecto</span><span class="det-val"><?= e(mb_substr($rec['venta_titulo'],0,60)) ?></span></div>
    <div class="det-row"><span class="det-lbl">Total de la venta</span><span class="det-val"><?= fmt_r($venta_total) ?></span></div>
    <?php if ($rec['reg_por']): ?>
    <div class="det-row"><span class="det-lbl">Registrado por</span><span class="det-val"><?= e($rec['reg_por']) ?></span></div>
    <?php endif; ?>
  </div>

  <!-- DESGLOSE SALDO -->
  <div class="saldo-card">
    <div class="det-row section-title"><span class="det-lbl">Desglose de saldo</span></div>
    <div class="sal-row antes">
      <span class="sal-lbl">Pagado anteriormente</span>
      <span class="sal-val"><?= fmt_r($pagado_antes) ?></span>
    </div>
    <div class="sal-row este">
      <span class="sal-lbl"><?= $es_cancelacion ? '− Cancelación' : '+ Este pago' ?></span>
      <span class="sal-val"><?= ($es_cancelacion ? '−' : '+') ?> <?= fmt_r($monto) ?></span>
    </div>
    <div class="sal-row despues">
      <span class="sal-lbl">Pagado total</span>
      <span class="sal-val" style="color:<?= $pagado_despues >= $venta_total ? 'var(--g)' : 'inherit' ?>"><?= fmt_r($pagado_despues) ?></span>
    </div>
    <?php if ($saldo_despues > 0): ?>
    <div class="sal-row" style="background:var(--bg)">
      <span class="sal-lbl" style="color:var(--t2)">Saldo pendiente</span>
      <span class="sal-val" style="color:#92400e;font-weight:600"><?= fmt_r($saldo_despues) ?></span>
    </div>
    <?php else: ?>
    <div class="sal-row" style="background:var(--g-bg)">
      <span class="sal-lbl" style="color:var(--g);font-weight:700">✓ Venta pagada completamente</span>
      <span class="sal-val" style="color:var(--g)">$0.00</span>
    </div>
    <?php endif; ?>
  </div>

  <!-- SELLO DIGITAL -->
  <div class="sello">
    ✓ <?= e($rec['numero']) ?> · <?= date('d/m/Y H:i', strtotime($rec['created_at'])) ?> · <?= e($rec['emp_nombre']) ?><br>
    Verificado en CotizaApp
  </div>

  <!-- BOTONES -->
  <?php if (!$es_cancelado): ?>
  <div class="btn-row">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / PDF</button>
    <?php if ($rec['emp_tel']): ?>
    <a href="https://wa.me/52<?= preg_replace('/\D/','',$rec['emp_tel']) ?>?text=<?= urlencode('Hola, tengo mi recibo '.$rec['numero'].' de '.$rec['emp_nombre']) ?>"
       target="_blank" class="btn-wa">💬 WhatsApp</a>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <div class="btn-row">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / PDF</button>
  </div>
  <?php endif; ?>

  <div class="pie">
    <?= e($rec['emp_nombre']) ?> · <?= e($rec['emp_ciudad'] ?? '') ?><br>
    <?php if ($rec['emp_tel']): ?><a href="tel:<?= e($rec['emp_tel']) ?>"><?= e($rec['emp_tel']) ?></a><?php if ($rec['emp_email']): ?> · <?php endif; ?><?php endif; ?>
    <?php if ($rec['emp_email']): ?><a href="mailto:<?= e($rec['emp_email']) ?>"><?= e($rec['emp_email']) ?></a><?php endif; ?><br>
    Este recibo es un comprobante de pago. No es una factura fiscal.
  </div>

</div>
</body>
</html>
