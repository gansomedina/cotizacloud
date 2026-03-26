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

// ─── Cargar venta + cotización (para descuentos y asesor) ─
$venta = DB::row(
    "SELECT v.*,
            cl.nombre AS cliente_nombre, cl.telefono AS cliente_telefono, cl.email AS cliente_email,
            uc.nombre AS asesor_nombre,
            c.numero  AS cot_numero, c.id AS cot_id,
            c.subtotal AS cot_subtotal,
            c.cupon_pct, c.cupon_codigo, c.cupon_monto AS cupon_amt,
            c.descuento_auto_activo, c.descuento_auto_pct, c.descuento_auto_amt,
            c.impuesto_pct, c.impuesto_modo, c.impuesto_amt
     FROM ventas v
     LEFT JOIN clientes cl    ON cl.id = v.cliente_id
     LEFT JOIN cotizaciones c ON c.id  = v.cotizacion_id
     LEFT JOIN usuarios uc    ON uc.id = COALESCE(v.vendedor_id, c.usuario_id)
     WHERE v.id = ? AND v.empresa_id = ?",
    [$venta_id, $empresa_id]
);
if (!$venta) { flash('error', 'Venta no encontrada'); redirect('/ventas'); }

// ─── Líneas ──────────────────────────────────────────────
$venta_log = VentaLog::obtener($venta_id, 40);

$lineas = DB::query(
    "SELECT * FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden ASC",
    [$venta['cotizacion_id'] ?: 0]
);

// ─── Abonos activos ──────────────────────────────────────
$abonos = DB::query(
    "SELECT * FROM recibos WHERE venta_id = ? AND cancelado = 0 ORDER BY created_at ASC",
    [$venta_id]
);

// ─── Catálogo de artículos (para sheet agregar) ──────────
$catalogo = DB::query(
    "SELECT id, titulo, sku, precio, unidad FROM articulos WHERE empresa_id=? AND activo=1 ORDER BY titulo ASC",
    [$empresa_id]
);
$clientes_lista = DB::query(
    "SELECT id, nombre, telefono, email FROM clientes WHERE empresa_id=? ORDER BY nombre ASC",
    [$empresa_id]
);

// ─── Siguiente folio recibo ──────────────────────────────
$total_rec_empresa = (int)DB::val("SELECT COUNT(*) FROM recibos WHERE empresa_id=?", [$empresa_id]);
$sig_rec = 'REC-' . date('Y') . '-' . str_pad($total_rec_empresa + 1, 4, '0', STR_PAD_LEFT);

$url_vta      = Router::url_publica('/v/' . $venta['slug']);
$puede_admin  = Auth::es_admin();
$puede_pagos  = Auth::es_admin() || Auth::puede('capturar_pagos');
$puede_cancel_rec = Auth::es_admin() || Auth::puede('cancelar_recibos');
$puede_descuento  = Auth::es_admin() || Auth::puede('aplicar_descuentos');
$puede_extras     = Auth::es_admin() || Auth::puede('agregar_extras');
$folio        = $venta['numero'] ?? 'VTA-' . $venta_id;
$pct          = $venta['total'] > 0 ? min(100, round($venta['pagado'] / $venta['total'] * 100)) : 0;

// Descuentos de la cotización origen
$cot_subtotal    = (float)($venta['cot_subtotal'] ?? 0);
$cupon_amt       = (float)($venta['cupon_amt'] ?? 0);
$cupon_pct       = (float)($venta['cupon_pct'] ?? 0);
$cupon_codigo    = $venta['cupon_codigo'] ?? '';
$desc_auto_amt   = (float)($venta['descuento_auto_amt'] ?? 0);
$desc_auto_pct   = (float)($venta['descuento_auto_pct'] ?? 0);
$impuesto_amt    = (float)($venta['impuesto_amt'] ?? 0);
$impuesto_modo   = $venta['impuesto_modo'] ?? 'ninguno';
$impuesto_pct    = (float)($venta['impuesto_pct'] ?? 0);
$impuesto_nombre = $empresa['impuesto_nombre'] ?? 'IVA'; // viene de empresas

// Descuento manual en ventas (puede no existir aún en BD)
$desc_manual_amt  = (float)($venta['descuento_manual_amt'] ?? 0);
// Variables para PDF
$pagado     = (float)$venta['pagado'];
$saldo      = (float)$venta['saldo'];
$total      = (float)$venta['total'];
$subtotal_v = $cot_subtotal; // alias para sección PDF
$ini_emp    = strtoupper(substr($empresa['nombre'] ?? '?', 0, 2));
$est_lbl    = ucfirst($venta['estado']);
$es_c       = $venta['estado'] === 'cancelada';

// Subtotal calculado desde líneas si cot_subtotal es 0
if ($cot_subtotal <= 0) {
    $cot_subtotal = array_sum(array_column($lineas, 'subtotal'));
}

$page_title = $folio . ' — ' . $venta['titulo'];

function icono_forma(string $f): string {
    return match($f) { 'efectivo'=>ico('money',14,'#16a34a'),'transferencia'=>ico('bank',14,'#2563eb'),'tarjeta'=>ico('card',14,'#7c3aed'), default=>ico('money',14) };
}
function bg_forma(string $f): string {
    return match($f) { 'efectivo'=>'#dcfce7','transferencia'=>'#dbeafe','tarjeta'=>'#f3e8ff', default=>'#f1f5f9' };
}

ob_start();
?>
<style>
/* ── OVERRIDE TAILWIND PREFLIGHT ── */
html { font-size: 16px !important; }
body { font-size: 16px !important; font-family: var(--body) !important; }
*, *::before, *::after { box-sizing: border-box; }

/* ── LAYOUT ── */
.detail-layout{display:flex;gap:20px;align-items:flex-start}
.col-main{flex:1;min-width:0}
.col-side{width:280px;flex-shrink:0;display:flex;flex-direction:column;gap:10px;position:sticky;top:72px;max-height:calc(100vh - 90px);overflow-y:auto}
@media(max-width:900px){.detail-layout{flex-direction:column}.col-side{width:100%;position:static;max-height:none;overflow-y:visible}}
@media(max-width:768px){
    .detail-layout{padding-bottom:0}
}

/* ── TOPBAR ── */
.page-top{display:flex;align-items:center;gap:10px;margin-bottom:16px}
.back-btn{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:1px solid var(--border);background:var(--white);font-size:16px;color:var(--t2);text-decoration:none;flex-shrink:0;box-shadow:var(--sh)}
.back-btn:hover{border-color:var(--g);color:var(--g)}
.tb-folio{font:600 12px var(--num);color:var(--t3);margin-bottom:1px}
.tb-title{font:700 17px var(--body);letter-spacing:-.01em}

/* ── CARD base ── */
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}

/* ── SEC-LBL ── */
.sec-lbl{font:700 13px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t2);margin:18px 0 8px;display:flex;align-items:center;gap:10px}
.sec-lbl::after{content:'';flex:1;height:1.5px;background:var(--border)}
.sec-lbl:first-child{margin-top:0}

/* ── STATUS ── */
.status{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font:700 12px var(--body)}
.status-dot{width:6px;height:6px;border-radius:3px;flex-shrink:0}
.s-pendiente{background:#f1f5f9;color:#475569}.s-pendiente .status-dot{background:#94a3b8}
.s-parcial{background:var(--amb-bg);color:var(--amb)}.s-parcial .status-dot{background:#f59e0b}
.s-pagada{background:var(--g-bg);color:var(--g)}.s-pagada .status-dot{background:var(--g)}
.s-entregada{background:var(--blue-bg);color:var(--blue)}.s-entregada .status-dot{background:var(--blue)}
.s-cancelada{background:var(--danger-bg);color:var(--danger)}.s-cancelada .status-dot{background:var(--danger)}

/* ── HEADER CARD ── */
.vhdr{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px;box-shadow:var(--sh)}
.vhdr-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap}
.vhdr-num{font:600 12px var(--num);color:var(--t3);margin-bottom:3px}
.vhdr-title{font:700 19px var(--body);letter-spacing:-.01em}
.vhdr-meta{display:flex;gap:20px;margin-top:12px;flex-wrap:wrap}
.meta-item{display:flex;flex-direction:column;gap:2px}
.meta-lbl{font:700 12px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3)}
.meta-val{font:600 15px var(--body);color:var(--text)}

/* ── ARTÍCULOS — grid 4 columnas desktop ── */
.item-hdr{display:grid;grid-template-columns:1fr 56px 80px 80px;gap:6px;padding:6px 14px;background:var(--bg);border-bottom:1px solid var(--border)}
.line-edit-btn,.line-del-btn{background:none;border:none;cursor:pointer;font-size:13px;opacity:.55;padding:0 3px;transition:opacity .1s;line-height:1}
.line-edit-btn:hover,.line-del-btn:hover{opacity:1}
.item-hdr span{font:700 10px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3)}
.item-row{display:grid;grid-template-columns:1fr 56px 80px 80px;gap:6px;padding:10px 14px;border-bottom:1px solid var(--border);align-items:start}
.item-row:last-child{border-bottom:none}
.item-name{font:600 15px var(--body);line-height:1.3}
.item-sku{font:400 11px var(--num);color:var(--t3);margin-top:2px}
.item-desc{font:400 13px var(--body);color:var(--t3);margin-top:4px;line-height:1.5;white-space:pre-line}
.item-cell{text-align:right;font:400 13px var(--num);color:var(--t2);padding-top:2px}
.item-total{text-align:right;font:700 13px var(--num);color:var(--g);padding-top:2px}
.item-add-btn{width:100%;padding:13px;border:none;background:transparent;font:600 14px var(--body);color:var(--t2);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .12s;border-top:1.5px dashed var(--border2)}
.item-add-btn:hover{background:var(--g-bg);color:var(--g)}

/* ── ARTÍCULOS — tarjeta mobile ≤ 768px ── */
@media(max-width:768px){
  .item-hdr{display:none}
  .item-row{
    display:block;
    padding:14px 16px;
    border-bottom:1px solid var(--border);
  }
  .item-row:last-child{border-bottom:none}
  /* nombre + acciones en la misma fila */
  .item-name{font:700 15px var(--body);line-height:1.3;display:flex;align-items:flex-start;justify-content:space-between;gap:8px}
  .item-name-text{flex:1}
  .item-actions{display:flex;gap:4px;flex-shrink:0;margin-top:1px}
  .item-sku{margin-top:3px}
  .item-desc{margin-top:6px;font:400 14px var(--body);color:var(--t2);line-height:1.55;white-space:pre-line}
  /* fila de 3 métricas abajo: cant / p.unit / total */
  .item-nums-row{
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:0;
    margin-top:10px;
    border-top:1px solid var(--border);
    border-radius:var(--r-sm);
    overflow:hidden;
    background:var(--bg);
  }
  .item-num-cell{
    padding:8px 10px;
    border-right:1px solid var(--border);
    display:flex;
    flex-direction:column;
    gap:2px;
  }
  .item-num-cell:last-child{border-right:none}
  .item-num-lbl{font:700 9px var(--body);letter-spacing:.06em;text-transform:uppercase;color:var(--t3)}
  .item-num-val{font:600 14px var(--num);color:var(--text)}
  .item-num-val.total{color:var(--g);font-weight:700}
  /* ocultar las celdas de tabla en mobile */
  .item-cell{display:none}
  .item-total{display:none}
}
/* ocultar fila de métricas mobile en desktop */
@media(min-width:769px){
  .item-nums-row{display:none}
  .item-name{display:block}
  .item-name-text{display:inline}
  .item-actions{display:inline}
}

/* ── DESCUENTOS/TOTALES ── */
.tot-row{display:flex;justify-content:space-between;padding:8px 14px;border-bottom:1px solid var(--border);font:400 16px var(--body)}
.tot-row:last-child{border-bottom:none}
.tot-lbl{color:var(--t2)}
.tot-val{font:500 13px var(--num)}
.tot-row.disc .tot-val{color:var(--amb)}
.tot-row.final-row{background:var(--bg)}
.tot-row.final-row .tot-lbl{font:700 17px var(--body);color:var(--text)}
.tot-row.final-row .tot-val{font:700 17px var(--num);color:var(--g)}

/* ── ABONOS ── */
.abono-row{display:flex;align-items:center;gap:10px;padding:11px 14px;border-bottom:1px solid var(--border)}
.abono-row:last-child{border-bottom:none}
.abono-ico{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.abono-info{flex:1;min-width:0}
.abono-forma{font:600 15px var(--body)}
.abono-fecha{font:400 13px var(--num);color:var(--t3);margin-top:1px}
.abono-nota{font:400 13px var(--body);color:var(--t3);margin-top:1px}
.abono-rec{font-size:12px;font-weight:700;color:var(--blue);background:var(--blue-bg);padding:2px 7px;border-radius:4px;margin-top:4px;display:inline-block;text-decoration:none;border:none;cursor:pointer}
.abono-monto{font:700 14px var(--num);color:var(--g);flex-shrink:0;text-align:right}
.abono-btn{width:24px;height:24px;border-radius:6px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;color:var(--t3);transition:all .12s;margin-top:4px;margin-left:auto}
.abono-btn:hover{border-color:var(--danger);color:var(--danger)}
.add-row-btn{width:100%;padding:13px;border-radius:var(--r);border:1.5px dashed var(--border);background:transparent;display:flex;align-items:center;justify-content:center;gap:8px;font:600 13px var(--body);color:var(--t2);cursor:pointer;transition:all .15s}
.add-row-btn:hover{border-color:var(--g);color:var(--g);background:var(--g-bg)}

/* ── FINANCIERO LATERAL ── */
.fin-row{display:flex;justify-content:space-between;align-items:center;padding:8px 14px;border-bottom:1px solid var(--border)}
.fin-row:last-child{border-bottom:none}
.fin-lbl{font:400 15px var(--body);color:var(--t2)}
.fin-val{font:500 13px var(--num)}
.fin-disc .fin-lbl,.fin-disc .fin-val{color:var(--amb)}
.fin-total .fin-lbl{font:700 14px var(--body);color:var(--text)}
.fin-total .fin-val{font:700 16px var(--num);color:var(--g)}
.fin-saldo .fin-lbl,.fin-saldo .fin-val{color:var(--amb);font-weight:600}
.fin-ok .fin-lbl,.fin-ok .fin-val{color:var(--g);font-weight:600}

/* ── PROGRESO ── */
.prog-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:12px 14px;box-shadow:var(--sh)}
.prog-hdr{display:flex;justify-content:space-between;margin-bottom:7px}
.prog-lbl{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3)}
.prog-pct{font:700 13px var(--num);color:var(--g)}
.prog-bar{height:7px;border-radius:4px;background:var(--border);overflow:hidden}
.prog-fill{height:100%;border-radius:4px;background:var(--g)}
.prog-nums{display:flex;justify-content:space-between;font:400 11px var(--num);color:var(--t3);margin-top:5px}

/* ── BOTONES LATERAL ── */
.action-btn{width:100%;padding:11px 14px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:600 13px var(--body);color:var(--t2);cursor:pointer;display:flex;align-items:center;gap:8px;box-shadow:var(--sh);transition:all .12s;text-decoration:none;justify-content:flex-start}
.action-btn:hover{border-color:var(--g);color:var(--g);background:var(--g-bg)}
.action-btn.danger:hover{border-color:var(--danger);color:var(--danger);background:var(--danger-bg)}

/* ── SHEETS ── */
.sh-overlay{position:fixed;top:0;left:0;right:0;bottom:0;z-index:490;background:rgba(0,0,0,.5);opacity:0;pointer-events:none;transition:opacity .25s;display:none}
.sh-overlay.open{opacity:1;pointer-events:all;display:block}
.bottom-sheet{display:none;position:fixed;bottom:0;left:0;right:0;z-index:500;background:var(--white);border-radius:20px 20px 0 0;max-height:92vh;flex-direction:column;box-shadow:0 -8px 32px rgba(0,0,0,.1);max-width:640px;margin:0 auto}
.bottom-sheet.open{display:flex;animation:sheetUp .28s cubic-bezier(.32,0,.15,1)}
@keyframes sheetUp{from{transform:translateY(100%)}to{transform:translateY(0)}}
@media(max-width:768px){
  .sh-overlay{bottom:64px}
  .bottom-sheet{bottom:64px;border-radius:16px 16px 0 0;max-height:80vh}
}
.sh-handle{width:34px;height:4px;border-radius:2px;background:var(--border);margin:12px auto 0;flex-shrink:0}
.sh-header{padding:14px 18px 12px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;border-bottom:1px solid var(--border)}
.sh-title{font:800 17px var(--body)}
.sh-close{width:30px;height:30px;border-radius:999px;border:none;background:var(--bg);font-size:15px;cursor:pointer;color:var(--t2)}
.sh-body{overflow-y:auto;flex:1;padding:0 0 16px}
.sh-field{padding:12px 18px;border-bottom:1px solid var(--border)}
.sh-field:last-child{border-bottom:none}
.sh-lbl{font:700 11px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);margin-bottom:6px}
.sh-input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:var(--r-sm);padding:10px 13px;font:400 15px var(--body);color:var(--text);outline:none;transition:border-color .15s}
.sh-input:focus{border-color:var(--g)}
.sh-row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sh-footer{padding:13px 18px;border-top:1px solid var(--border);flex-shrink:0;display:flex;gap:10px}
.sh-btn-save{flex:1;padding:12px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 14px var(--body);color:#fff;cursor:pointer}
.sh-btn-cancel{padding:12px 16px;border-radius:var(--r-sm);border:1px solid var(--border);background:transparent;font:600 14px var(--body);color:var(--t2);cursor:pointer}
.sh-note{font-size:13px;color:var(--t3);margin-top:5px;line-height:1.5}
.forma-opts{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.forma-opt{padding:11px 8px;border-radius:var(--r-sm);border:1.5px solid var(--border);background:var(--bg);cursor:pointer;text-align:center;transition:all .15s;user-select:none}
.forma-opt.selected{border-color:var(--g);background:var(--g-bg)}
.forma-opt-ico{font-size:19px;display:block;margin-bottom:3px}
.forma-opt-lbl{font:700 12px var(--body);color:var(--t2)}
.forma-opt.selected .forma-opt-lbl{color:var(--g)}

/* ── CATALOGO LIST ── */
.art-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .1s}
.art-item:last-child{border-bottom:none}
.art-item:hover{background:var(--g-bg)}
.art-name{font:600 13px var(--body)}
.art-sku{font:400 11px var(--num);color:var(--t3)}
.art-precio{font:700 13px var(--num);color:var(--g);margin-left:auto;white-space:nowrap}

.hist-row{display:flex;gap:8px;padding:9px 12px;border-bottom:1px solid var(--border)}
.hist-row:last-child{border-bottom:none}
.hist-ico{width:24px;height:24px;border-radius:6px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;margin-top:2px}
.hist-body{flex:1;min-width:0}
.hist-lbl{font:600 13px var(--body);color:var(--text);line-height:1.3}
.hist-who{font-weight:400;color:var(--t3);font-size:13px}
.hist-det{font:400 12px var(--body);color:var(--t2);margin-top:1px;line-height:1.4}
.hist-ts{font:400 12px var(--num);color:var(--t3);margin-top:3px}
/* ── PRINT ── */
.print-only{display:none}
.recibo-print-only{display:none}
.modal-overlay{display:none;position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.45);align-items:flex-end;justify-content:center}
.modal-overlay.open{display:flex}
@media print{
  .page-top,.col-side,.sh-overlay,.bottom-sheet,#urlOv,
  .item-add-btn,.add-row-btn,.abono-btn,.sec-lbl,
  nav,#nav-lateral,.detail-layout,
  #sidebar,#sidebar-overlay,#topbar,#bottom-nav,
  #more-drawer,#more-overlay,.topbar-right,
  .flash{display:none!important}
  .venta-print-only{display:block!important}
  .recibo-print-only{display:none!important}
  body.modo-recibo .venta-print-only,
  body.modo-recibo .print-only:not(#recibo-print-tpl){display:none!important}
  body.modo-recibo #recibo-print-tpl{display:block!important}
  body{background:#fff;margin:0;padding:0}
  #main{margin-left:0!important}
  #content{padding:0!important}
  .fac,#recibo-print-tpl{font-size:10pt}
  /* Estilos del recibo individual (compacto, 2 copias por página) */
  .rp-copia{padding:8pt 0}
  .rp-copia-lbl{font:700 7pt sans-serif;letter-spacing:.08em;text-transform:uppercase;color:#999;text-align:right;margin-bottom:4pt}
  .rp-corte{border:none;border-top:1pt dashed #aaa;margin:8pt 0}
  .rp-header{text-align:center;padding-bottom:6pt;border-bottom:1.5pt solid #000;margin-bottom:6pt}
  .rp-empresa{font:800 13pt sans-serif}
  .rp-sub{font:400 8pt sans-serif;color:#555;margin:1pt 0}
  .rp-tipo{font:300 15pt sans-serif;margin-top:4pt}
  .rp-folio{font:700 9pt sans-serif;color:#555}
  .rp-table{width:100%;border-collapse:collapse;margin:6pt 0;font:400 9pt sans-serif}
  .rp-table td{padding:3pt 5pt;border-bottom:.5pt solid #eee}
  .rp-table td:first-child{font-weight:700;width:80pt;color:#555}
  .rp-monto-box{border:1.5pt solid #2a7;border-radius:4pt;padding:6pt;text-align:center;margin:6pt 0}
  .rp-monto-lbl{font:600 8pt sans-serif;color:#555}
  .rp-monto-val{font:800 18pt sans-serif;color:#1a6}
  .rp-foot{font:400 8pt sans-serif;color:#555;text-align:center;margin-top:6pt;padding-top:4pt;border-top:.5pt solid #ddd}
  .rp-sello{font:400 7pt sans-serif;color:#aaa;text-align:center;margin-top:2pt}
    @page{margin:14mm 16mm 14mm 16mm;size:letter}
    .web-only{display:none!important}
    .modal-overlay{display:none!important}
    .print-only{display:block!important}
    .fac{font-size:10pt;font-family:var(--body)}
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
}
</style>

<!-- TOPBAR (punto 2: compacto, sin avatar) -->
<div class="page-top">
  <a href="/ventas" class="back-btn">&#8592;</a>
  <div>
    <div class="tb-folio">
      <?= e($folio) ?>
      <?php if ($venta['cot_numero']): ?>
        · de <a href="/cotizaciones/<?= (int)$venta['cot_id'] ?>" style="color:var(--g);text-decoration:none"><?= e($venta['cot_numero']) ?></a>
      <?php endif ?>
    </div>
    <div class="tb-title"><?= e($venta['titulo']) ?></div>
  </div>
</div>

<?php if ($venta['estado'] === 'cancelada'): ?>
<div style="background:var(--danger-bg);border:1px solid #fca5a5;border-radius:var(--r);padding:11px 16px;font:600 13px var(--body);color:var(--danger);margin-bottom:14px">
  <?= ico('alert',14,'#c53030') ?> Venta cancelada<?= $venta['cancelado_motivo'] ? ' — '.e($venta['cancelado_motivo']) : '' ?>
</div>
<?php endif ?>

<div class="detail-layout">

<!-- ══ COL PRINCIPAL ══ -->
<div class="col-main">

  <!-- HEADER -->
  <div class="vhdr">
    <div class="vhdr-top">
      <div style="flex:1;min-width:0">
        <div class="vhdr-num"><?= e($folio) ?></div>
        <div class="vhdr-title"><?= e($venta['titulo']) ?></div>
      </div>
      <?php
      $bmap = ['pendiente'=>'s-pendiente','parcial'=>'s-parcial','pagada'=>'s-pagada','entregada'=>'s-entregada','cancelada'=>'s-cancelada'];
      $bcls = $bmap[$venta['estado']] ?? 's-pendiente';
      ?>
      <span class="status <?= $bcls ?>"><span class="status-dot"></span><?= ucfirst(e($venta['estado'])) ?></span>
    </div>
    <div class="vhdr-meta">
      <div class="meta-item">
        <div class="meta-lbl">Cliente
          <?php if ($puede_admin && $venta['estado'] !== 'cancelada'): ?>
          <button onclick="openSheet('shCliente')" style="margin-left:6px;font:600 10px var(--body);color:var(--g);background:none;border:none;cursor:pointer;padding:0">✏️ cambiar</button>
          <?php endif ?>
        </div>
        <div class="meta-val"><?= e($venta['cliente_nombre'] ?? '—') ?><?= $venta['cliente_telefono'] ? ' · '.e($venta['cliente_telefono']) : '' ?></div>
      </div>
      <div class="meta-item">
        <div class="meta-lbl">Fecha</div>
        <div class="meta-val"><?= date('d M Y', strtotime($venta['created_at'])) ?></div>
      </div>
      <!-- Punto 3: asesor de cotización, no de venta -->
      <div class="meta-item">
        <div class="meta-lbl">Asesor</div>
        <div class="meta-val"><?= e($venta['asesor_nombre'] ?: ($empresa['nombre'] ?? '—')) ?></div>
      </div>
      <?php if ($venta['cot_numero']): ?>
      <div class="meta-item">
        <div class="meta-lbl">Cotización</div>
        <div class="meta-val"><a href="/cotizaciones/<?= (int)$venta['cot_id'] ?>" style="color:var(--g);text-decoration:none"><?= e($venta['cot_numero']) ?> →</a></div>
      </div>
      <?php endif ?>
    </div>
  </div>

  <!-- ARTÍCULOS -->
  <div class="sec-lbl">Artículos</div>
  <div class="card" id="lineas-card">
    <div class="item-hdr">
      <span>Descripción</span>
      <?php if (Auth::es_admin() || Auth::puede('ver_cantidades')): ?>
      <span style="text-align:right">Cant.</span>
      <span style="text-align:right">P. Unit.</span>
      <?php endif; ?>
      <span style="text-align:right">Total</span>
    </div>
    <div id="lineas-list"><!-- renderizado por JS --></div>
    <?php if ($puede_admin && $venta['estado'] !== 'cancelada'): ?>
    <button class="item-add-btn" onclick="openSheet('shItem')">+ Agregar artículo</button>
    <?php endif ?>
  </div>

  <!-- SUBTOTALES / DESCUENTOS -->
  <div class="sec-lbl">Resumen de montos</div>
  <div class="card" id="totales-card"><!-- renderizado por JS --></div>

  <!-- HISTORIAL DE PAGOS (punto 5: rutas correctas) -->
  <div class="sec-lbl">Historial de pagos</div>
  <div class="card">
    <?php if (empty($abonos)): ?>
    <div style="padding:20px 16px;text-align:center;font:400 13px var(--body);color:var(--t3)">Sin pagos registrados</div>
    <?php else: ?>
    <?php foreach ($abonos as $ab): ?>
    <div class="abono-row">
      <div class="abono-ico" style="background:<?= bg_forma($ab['concepto'] ?? '') ?>"><?= icono_forma($ab['concepto'] ?? '') ?></div>
      <div class="abono-info">
        <div class="abono-forma"><?= e($ab['concepto'] ?: 'Abono') ?></div>
        <div class="abono-fecha"><?= date('d M Y, g:i A', strtotime($ab['created_at'])) ?></div>
        <?php if ($ab['notas'] ?? ''): ?><div class="abono-nota"><?= e($ab['notas']) ?></div><?php endif ?>
        <button class="abono-rec" onclick="imprimirRecibo(<?= htmlspecialchars(json_encode([
            'numero'   => $ab['numero'],
            'concepto' => $ab['concepto'] ?? 'Pago',
            'monto'    => (float)$ab['monto'],
            'fecha'    => date('d M Y · g:i A', strtotime($ab['created_at'])),
            'notas'    => $ab['notas'] ?? '',
            'cliente'  => $venta['cliente_nombre'] ?? '',
            'venta'    => $venta['numero'],
            'empresa'  => $empresa['nombre'],
            'ciudad'   => $empresa['ciudad'] ?? '',
            'tel'      => $empresa['telefono'] ?? '',
        ]), ENT_QUOTES) ?>)">🖨 PDF</button>
      </div>
      <div style="flex-shrink:0;text-align:right">
        <div class="abono-monto"><?= format_money($ab['monto'], $empresa['moneda']) ?></div>
        <?php if ($puede_cancel_rec): ?>
        <button class="abono-btn" onclick="cancelarRec(<?= (int)$ab['id'] ?>,'<?= e($ab['numero']) ?>','<?= format_money($ab['monto'],$empresa['moneda']) ?>')">✕</button>
        <?php endif ?>
      </div>
    </div>
    <?php endforeach ?>
    <?php endif ?>
    <?php if ($puede_pagos && !in_array($venta['estado'],['cancelada','entregada'])): ?>
    <div style="padding:11px 14px;border-top:1px solid var(--border)">
      <button class="add-row-btn" onclick="openSheet('shAbono')">+ Registrar abono</button>
    </div>
    <?php endif ?>
  </div>

  <!-- NOTAS -->
  <div class="sec-lbl">Notas internas</div>
  <div class="card" style="padding:13px 16px">
    <textarea id="notas-txt"
      style="width:100%;background:transparent;border:none;outline:none;font:400 14px var(--body);color:var(--text);resize:none;overflow:hidden;line-height:1.6;min-height:56px"
      placeholder="Producción, entrega, observaciones…"
      oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px';guardarNotas()"><?= e($venta['notas_internas'] ?? '') ?></textarea>
  </div>



<!-- ══ SECCIÓN IMPRESIÓN/PDF ══ -->
<div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeRec()">
  <div class="rec-sheet">
    <div class="sh-handle"></div>
    <div class="sh-hdr">
      <div class="sh-hdr-title" id="rec-sh-title">Recibo</div>
      <button class="sh-close" onclick="closeRec()">✕</button>
    </div>
    <div class="rec-body">
      <div class="rec-emp-row">
        <div style="font:700 18px var(--body)"><?= e($ini_emp) ?></div>
        <div style="text-align:right">
          <div class="rec-emp-name"><?= e($empresa['nombre']) ?></div>
          <div class="rec-emp-sub"><?= e($empresa['ciudad']??'') ?><?php if($empresa['telefono']): ?> · <?= e($empresa['telefono']) ?><?php endif; ?></div>
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
      <div class="rec-row"><span class="rec-lbl">Total de la venta</span><span class="rec-val"><?= format_money($total, $empresa['moneda']) ?></span></div>
      <div class="rec-row"><span class="rec-lbl">Pagado anteriormente</span><span class="rec-val" id="rec-prev">—</span></div>
      <div class="rec-row"><span class="rec-lbl">Saldo restante</span><span class="rec-val" id="rec-saldo">—</span></div>
      <div class="rec-monto-box" id="rec-monto-box">
        <span class="rec-monto-lbl" id="rec-monto-lbl">Este pago</span>
        <span class="rec-monto-val" id="rec-monto">—</span>
      </div>
      <div class="rec-foot"><?= e($empresa['nombre']) ?> · gracias por su preferencia</div>
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

</div><!-- /col-main -->

<!-- ══ COL LATERAL ══ -->
<div class="col-side">

  <!-- RESUMEN FINANCIERO -->
  <div class="card">
    <div class="fin-row">
      <span class="fin-lbl">Total</span>
      <span class="fin-val" style="font:700 16px var(--num);color:var(--g)"><?= format_money($venta['total'], $empresa['moneda']) ?></span>
    </div>
    <div class="fin-row">
      <span class="fin-lbl">Pagado</span>
      <span class="fin-val" style="color:var(--g)"><?= format_money($venta['pagado'], $empresa['moneda']) ?></span>
    </div>
    <?php if ($venta['saldo'] > 0): ?>
    <div class="fin-row fin-saldo">
      <span class="fin-lbl">Saldo pendiente</span>
      <span class="fin-val"><?= format_money($venta['saldo'], $empresa['moneda']) ?></span>
    </div>
    <?php else: ?>
    <div class="fin-row fin-ok">
      <span class="fin-lbl">✓ Pagado completo</span>
      <span class="fin-val">$0.00</span>
    </div>
    <?php endif ?>
  </div>

  <!-- PROGRESO -->
  <div class="prog-card">
    <div class="prog-hdr">
      <span class="prog-lbl">Progreso de pago</span>
      <span class="prog-pct"><?= $pct ?>%</span>
    </div>
    <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
    <div class="prog-nums">
      <span><?= format_money($venta['pagado'], $empresa['moneda']) ?></span>
      <span>de <?= format_money($venta['total'], $empresa['moneda']) ?></span>
    </div>
  </div>

  <!-- ACCIONES (punto 7: copiar URL en vez de compartir) -->
  <?php if ($puede_pagos && !in_array($venta['estado'],['cancelada','entregada'])): ?>
  <button class="action-btn" onclick="openSheet('shAbono')"><?= ico('money',14) ?> Registrar abono</button>
  <?php endif ?>
  <button class="action-btn" id="btn-copiar" onclick="copiarUrl()"><?= ico('link',14) ?> Copiar URL del cliente</button>
  <?php if ($puede_descuento && $venta['estado'] !== 'cancelada'): ?>
  <button class="action-btn" onclick="openSheet('shDescuento')"><?= ico('tag',14) ?> Agregar descuento</button>
  <?php endif ?>
  <?php if ($puede_extras && $venta['estado'] !== 'cancelada'): ?>
  <button class="action-btn" onclick="openSheet('shExtra')"><?= ico('edit',14) ?> Agregar extra</button>
  <?php endif ?>

  <button class="action-btn" id="btn-guardar"
    onclick="guardarCambios()"
    style="display:none;background:var(--g);color:#fff;border-color:var(--g);font-weight:700">
    <?= ico('check',14,'#fff') ?> Guardar cambios
  </button>
  <button class="action-btn" onclick="window.print()">🖨️ Imprimir / PDF</button>

  <!-- HISTORIAL DE ACTIVIDAD -->
  <?php if (!empty($venta_log)): ?>
  <div style="margin-top:4px">
    <div class="sec-lbl" style="margin-top:6px;margin-bottom:6px">Historial</div>
    <div class="card" style="padding:0;max-height:340px;overflow-y:auto">
      <?php foreach ($venta_log as $entry):
        [$ico, $lbl] = VentaLog::label($entry['evento']);
      ?>
      <div class="hist-row">
        <div class="hist-ico"><?= $ico ?></div>
        <div class="hist-body">
          <div class="hist-lbl"><?= e($lbl) ?>
            <?php if ($entry['usuario_nombre']): ?>
            <span class="hist-who">· <?= e($entry['usuario_nombre']) ?></span>
            <?php endif ?>
          </div>
          <?php if ($entry['detalle']): ?>
          <div class="hist-det"><?= e($entry['detalle']) ?></div>
          <?php endif ?>
          <div class="hist-ts"><?= tiempo_relativo($entry['created_at']) ?></div>
        </div>
      </div>
      <?php endforeach ?>
    </div>
  </div>
  <?php endif ?>

  <?php if ($puede_admin && $venta['estado'] !== 'cancelada'): ?>
  <!-- punto 1: cancelar solo si no tiene pagos -->
  <?php if (empty($abonos)): ?>
  <button class="action-btn danger" onclick="openSheet('shCancelar')">✕ Cancelar venta</button>
  <?php else: ?>
  <div style="font:400 12px var(--body);color:var(--t3);padding:8px 2px">Para cancelar la venta primero cancela los <?= count($abonos) ?> pago(s) registrado(s).</div>
  <?php endif ?>
  <?php endif ?>

</div>

</div><!-- /detail-layout -->

<!-- Plantilla print recibo individual -->
<div id="recibo-print-tpl" class="recibo-print-only"></div>

<!-- ══ SECCIÓN IMPRESIÓN/PDF (venta completa) ══ -->
<div class="print-only venta-print-only">
<div class="fac">

  <div class="fac-header">
    <div>
      <div class="fac-emp-name"><?= e($empresa['nombre']) ?></div>
      <div class="fac-emp-sub">
        <?= e($empresa['ciudad'] ?? '') ?>
        <?php if ($empresa['telefono']): ?> · <?= e($empresa['telefono']) ?><?php endif; ?>
        <?php if ($empresa['email']): ?><br><?= e($empresa['email']) ?><?php endif; ?>
      </div>
    </div>
    <div>
      <div class="fac-doc-tipo">Venta</div>
      <div class="fac-doc-folio"><?= e($venta['numero']) ?></div>
    </div>
  </div>

  <div class="fac-info-row">
    <div class="fac-info-cell"><div class="fac-info-lbl">Cliente</div><div class="fac-info-val"><?= e($venta['cliente_nombre'] ?? '—') ?></div></div>
    <?php if (($venta['cliente_telefono'] ?? '')): ?><div class="fac-info-cell"><div class="fac-info-lbl">Teléfono</div><div class="fac-info-val"><?= e(($venta['cliente_telefono'] ?? '')) ?></div></div><?php endif; ?>
    <div class="fac-info-cell"><div class="fac-info-lbl">Fecha</div><div class="fac-info-val"><?= date('d M Y', strtotime($venta['created_at'])) ?></div></div>
    <?php if (''): ?><div class="fac-info-cell"><div class="fac-info-lbl">Asesor</div><div class="fac-info-val"><?= e('') ?></div></div><?php endif; ?>
    <div class="fac-info-cell"><div class="fac-info-lbl">Estado</div><div class="fac-info-val"><span class="fac-status"><?= $est_lbl ?></span></div></div>
  </div>

  <?php if (!empty($lineas)): ?>
  <table class="fac-tbl">
    <?php $ocultar_cp_pdf = !empty($empresa['ocultar_cant_pu']); ?>
    <thead><tr><th style="width:16pt">#</th><th>Descripción</th><?php if (!$ocultar_cp_pdf): ?><th class="r" style="width:60pt">Cant.</th><th class="r" style="width:70pt">P. Unit.</th><?php endif; ?><th class="r" style="width:80pt">Total</th></tr></thead>
    <tbody>
    <?php foreach ($lineas as $i => $l): ?>
    <tr>
      <td style="color:#333"><?= $i+1 ?></td>
      <td>
        <div class="td-name"><?= e($l['titulo']) ?></div>
        <?php if ($l['sku']): ?><div class="td-sku"><?= e($l['sku']) ?></div><?php endif; ?>
        <?php if ($l['descripcion']): ?><div class="td-desc"><?= nl2br(e($l['descripcion'])) ?></div><?php endif; ?>
      </td>
      <?php if (!$ocultar_cp_pdf): ?>
      <td class="td-qty"><?= number_format($l['cantidad'],2) ?> pz.</td>
      <td class="td-pu"><?= $l['precio_unit'] > 0 ? format_money($l['precio_unit'], $empresa['moneda']) : '—' ?></td>
      <?php endif; ?>
      <td class="td-total"><?= $l['precio_unit'] > 0 ? format_money($l['subtotal'], $empresa['moneda']) : format_money(0, $empresa['moneda']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <div class="fac-bottom">
    <div class="fac-pagos">
      <div class="fac-pagos-title">Historial de pagos</div>
      <?php foreach ($abonos as $r): $es_c = (bool)$r['cancelado']; ?>
      <div class="fac-pago-row <?= $es_c?'fac-pago-cancelado':'' ?>">
        <div>
          <div class="fac-pago-lbl"><?= e($r['concepto'] ?? 'Pago') ?> — <?= e($r['numero']) ?><?= $es_c?' (cancelado)':'' ?></div>
          <div class="fac-pago-sub"><?= date('d M Y', strtotime($r['created_at'])) ?><?= ($r['notas']??'') ? ' · '.e($r['notas']) : '' ?></div>
        </div>
        <div class="fac-pago-monto"><?= format_money(abs((float)$r['monto']), $empresa['moneda']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="fac-totales">
      <div class="fac-tot-row"><span class="fac-tot-lbl">Subtotal</span><span class="fac-tot-val"><?= format_money($subtotal_v, $empresa['moneda']) ?></span></div>
      <?php if ((float)($cupon_amt ?? 0) > 0): ?>
      <div class="fac-tot-row"><span class="fac-tot-lbl">Cupón<?= ($venta['cupon_codigo'] ?? '') ? ' ('.($venta['cupon_codigo'] ?? '').')' : '' ?></span><span class="fac-tot-val" style="color:#c05">-<?= format_money($cupon_amt, $empresa['moneda']) ?></span></div>
      <?php endif; ?>
      <?php if ($desc_auto_amt > 0): ?>
      <div class="fac-tot-row"><span class="fac-tot-lbl">Descuento<?= $desc_auto_pct > 0 ? ' ('.$desc_auto_pct.'%)' : '' ?></span><span class="fac-tot-val" style="color:#c05">-<?= format_money($desc_auto_amt, $empresa['moneda']) ?></span></div>
      <?php endif; ?>
      <div class="fac-tot-row final"><span class="fac-tot-lbl">Total</span><span class="fac-tot-val"><?= format_money($total, $empresa['moneda']) ?></span></div>
      <div class="fac-saldo-box">
        <?php if ($saldo > 0): ?>
        <div class="fac-saldo-lbl">Saldo pendiente</div>
        <div class="fac-saldo-val"><?= format_money($saldo, $empresa['moneda']) ?></div>
        <?php else: ?>
        <div class="fac-saldo-lbl">Estado</div>
        <div class="fac-saldo-val" style="font-size:12pt">Pagada ✓</div>
        <?php endif; ?>
        <div class="fac-pagado-row">
          <span class="fac-pagado-lbl">Pagado</span>
          <span class="fac-pagado-val"><?= format_money($pagado, $empresa['moneda']) ?></span>
        </div>
      </div>
    </div>
  </div>

  <?php if (($empresa['vta_terminos'] ?? '')): ?>
  <hr class="fac-divider">
  <div class="fac-terminos-lbl">Términos y condiciones</div>
  <div class="fac-terminos"><?= e(mb_substr(strip_tags(($empresa['vta_terminos'] ?? '')),0,400)) ?></div>
  <?php endif; ?>

  <div class="fac-footer">
    <div class="fac-footer-l"><?= e($empresa['nombre']) ?> · <?= e($empresa['ciudad']??'') ?><?php if($empresa['telefono']): ?><br><?= e($empresa['telefono']) ?><?php endif; ?></div>
    <div class="fac-footer-r"><?= e($venta['numero']) ?> · generado con Cotiza.cloud<br>Impreso: <?= date('d/m/Y') ?></div>
  </div>
  <div class="fac-nota">Este documento es un comprobante interno de venta. No es una factura fiscal.</div>

</div>
</div><!-- /print-only -->

<!-- MODAL RECIBO en pantalla -->


<!-- ══ SHEET: ABONO (punto 5: ruta correcta /ventas/:id/abono) ══ -->
<div class="sh-overlay" id="ov-shAbono" onclick="closeSheet('shAbono')"></div>
<div class="bottom-sheet" id="shAbono">
  <div class="sh-handle"></div>
  <div class="sh-header"><div class="sh-title">Registrar abono</div><button class="sh-close" onclick="closeSheet('shAbono')">✕</button></div>
  <div class="sh-body">
    <div class="sh-field">
      <div class="sh-lbl">Forma de pago</div>
      <div class="forma-opts">
        <div class="forma-opt selected" onclick="selForma(this,'efectivo')"><span class="forma-opt-ico">💵</span><span class="forma-opt-lbl">Efectivo</span></div>
        <div class="forma-opt" onclick="selForma(this,'transferencia')"><span class="forma-opt-ico">🏦</span><span class="forma-opt-lbl">Transferencia</span></div>
        <div class="forma-opt" onclick="selForma(this,'tarjeta')"><span class="forma-opt-ico">💳</span><span class="forma-opt-lbl">Tarjeta</span></div>
      </div>
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Monto *</div>
      <input class="sh-input" type="number" id="ab-monto" placeholder="0.00" step="0.01" min="0.01">
      <div class="sh-note">Fecha y hora se registran automáticamente</div>
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Concepto</div>
      <input class="sh-input" type="text" id="ab-concepto" placeholder="Anticipo 50%, Pago final…">
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Referencia / nota (opcional)</div>
      <input class="sh-input" type="text" id="ab-notas" placeholder="BBVA ref. 8823…">
    </div>
    <div class="sh-field">
      <div style="background:var(--g-bg);border:1px solid var(--g-border);border-radius:var(--r-sm);padding:10px 13px;font-size:13px;color:var(--g)">
        Se generará el recibo <strong><?= e($sig_rec) ?></strong>
      </div>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shAbono')">Cancelar</button>
    <button class="sh-btn-save" onclick="doAbono()">Guardar abono</button>
  </div>
</div>



<!-- ══ SHEET: EDITAR LÍNEA ══ -->
<div class="sh-overlay" id="ov-shEditLinea" onclick="closeSheet('shEditLinea')"></div>
<div class="bottom-sheet" id="shEditLinea">
  <div class="sh-handle"></div>
  <div class="sh-header"><div class="sh-title">Editar artículo</div><button class="sh-close" onclick="closeSheet('shEditLinea')">✕</button></div>
  <div class="sh-body">
    <input type="hidden" id="edit-linea-id">
    <div class="sh-field"><div class="sh-lbl">Nombre *</div><input class="sh-input" type="text" id="el-titulo" placeholder="Nombre del artículo"></div>
    <div class="sh-field"><div class="sh-lbl">SKU</div><input class="sh-input" type="text" id="el-sku"></div>
    <div class="sh-field"><div class="sh-lbl">Descripción</div><textarea class="sh-input" id="el-desc" style="min-height:50px;resize:none"></textarea></div>
    <?php if (Auth::es_admin() || Auth::puede('ver_cantidades')): ?>
    <div class="sh-field sh-row2">
      <div><div class="sh-lbl">Cantidad</div><input class="sh-input" type="number" id="el-cant" value="1" min="0.01" step="0.01"></div>
      <div><div class="sh-lbl">Precio unitario</div><input class="sh-input" type="number" id="el-precio" placeholder="0.00" step="0.01"></div>
    </div>
    <?php else: ?>
    <input type="hidden" id="el-cant" value="1">
    <input type="hidden" id="el-precio" value="0">
    <?php endif; ?>
    <div id="el-preview" style="text-align:right;font:600 13px var(--num);color:var(--g);padding:4px 0"></div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shEditLinea')">Cancelar</button>
    <button class="sh-btn-save" onclick="doEditarLinea()">Guardar cambios</button>
  </div>
</div>

<!-- ══ SHEET: CAMBIAR CLIENTE ══ -->
<div class="sh-overlay" id="ov-shCliente" onclick="closeSheet('shCliente')"></div>
<div class="bottom-sheet" id="shCliente">
  <div class="sh-handle"></div>
  <div class="sh-header"><div class="sh-title">Cambiar cliente</div><button class="sh-close" onclick="closeSheet('shCliente')">✕</button></div>
  <div class="sh-body">
    <div style="padding:8px 0 10px"><input class="sh-input" type="text" id="cli-buscar" placeholder="Buscar cliente…" oninput="filtrarClientes(this.value)" style="font-size:13px;padding:8px 12px"></div>
    <div id="cli-lista" style="max-height:300px;overflow-y:auto">
      <?php foreach ($clientes_lista as $cl): ?>
      <div class="art-item" onclick="selCliente(this,<?= (int)$cl['id'] ?>,'<?= e(addslashes($cl['nombre'])) ?>')">
        <div style="flex:1">
          <div style="font:600 13px var(--body)"><?= e($cl['nombre']) ?></div>
          <?php if ($cl['telefono']): ?><div style="font:400 11px var(--num);color:var(--t3)"><?= e($cl['telefono']) ?></div><?php endif ?>
        </div>
      </div>
      <?php endforeach ?>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shCliente')">Cancelar</button>
    <button class="sh-btn-save" id="btn-guardar-cliente" disabled onclick="doGuardarCliente()">Asignar cliente</button>
  </div>
</div>

<!-- ══ SHEET: AGREGAR ARTÍCULO (punto 11a: catálogo + 11b: descuento) ══ -->
<div class="sh-overlay" id="ov-shItem" onclick="closeSheet('shItem')"></div>
<div class="bottom-sheet" id="shItem">
  <div class="sh-handle"></div>
  <div class="sh-header"><div class="sh-title">Agregar artículo</div><button class="sh-close" onclick="closeSheet('shItem')">✕</button></div>
  <div class="sh-body">
    <!-- Tab: Catálogo / Manual -->
    <div style="display:flex;border-bottom:1px solid var(--border)">
      <button id="tab-cat-btn" onclick="switchItemTab('catalogo')" style="flex:1;padding:12px;font:600 13px var(--body);border:none;background:transparent;cursor:pointer;border-bottom:2.5px solid var(--g);color:var(--g)">Del catálogo</button>
      <button id="tab-man-btn" onclick="switchItemTab('manual')" style="flex:1;padding:12px;font:600 13px var(--body);border:none;background:transparent;cursor:pointer;border-bottom:2.5px solid transparent;color:var(--t3)">Manual</button>
    </div>

    <!-- Catálogo -->
    <div id="tab-catalogo">
      <div style="padding:10px 14px;border-bottom:1px solid var(--border)">
        <input class="sh-input" type="text" id="art-buscar" placeholder="Buscar artículo…" oninput="filtrarCat(this.value)" style="font-size:13px;padding:8px 12px">
      </div>
      <div id="art-lista">
        <?php foreach ($catalogo as $art): ?>
        <div class="art-item" onclick="selArt(this,<?= (int)$art['id'] ?>,'<?= e(addslashes($art['titulo'])) ?>','<?= e($art['sku'] ?? '') ?>',<?= (float)$art['precio'] ?>)">
          <div style="flex:1;min-width:0">
            <div class="art-name"><?= e($art['titulo']) ?></div>
            <?php if ($art['sku']): ?><div class="art-sku"><?= e($art['sku']) ?></div><?php endif ?>
          </div>
          <div class="art-precio"><?= format_money($art['precio'], $empresa['moneda']) ?></div>
        </div>
        <?php endforeach ?>
        <?php if (empty($catalogo)): ?>
        <div style="padding:20px;text-align:center;font:400 13px var(--body);color:var(--t3)">No hay artículos en el catálogo</div>
        <?php endif ?>
      </div>
    </div>

    <!-- Manual -->
    <div id="tab-manual" style="display:none">
      <div class="sh-field"><div class="sh-lbl">Nombre *</div><input class="sh-input" type="text" id="item-titulo" placeholder="Nombre del artículo"></div>
      <div class="sh-field"><div class="sh-lbl">SKU (opcional)</div><input class="sh-input" type="text" id="item-sku" placeholder="COC-01"></div>
      <div class="sh-field"><div class="sh-lbl">Descripción (opcional)</div><textarea class="sh-input" id="item-desc" style="min-height:50px;resize:none" placeholder="Descripción…"></textarea></div>
      <div class="sh-field sh-row2">
        <div><div class="sh-lbl">Cantidad</div><input class="sh-input" type="number" id="item-qty" value="1" min="0.01" step="0.01"></div>
        <div><div class="sh-lbl">Precio unitario</div><input class="sh-input" type="number" id="item-precio" placeholder="0.00" step="0.01"></div>
      </div>
    </div>

    <!-- Cantidad (para catálogo seleccionado) -->
    <div id="art-qty-wrap" style="display:none" class="sh-field">
      <div class="sh-lbl">Cantidad</div>
      <input class="sh-input" type="number" id="art-qty" value="1" min="0.01" step="0.01">
      <div class="sh-note" id="art-preview"></div>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shItem')">Cancelar</button>
    <button class="sh-btn-save" onclick="doAgregarItem()">Agregar</button>
  </div>
</div>


<!-- ══ SHEET: DESCUENTO MANUAL (punto 11b) ══ -->
<div class="sh-overlay" id="ov-shDescuento" onclick="closeSheet('shDescuento')"></div>
<div class="bottom-sheet" id="shDescuento">
  <div class="sh-handle"></div>
  <div class="sh-header"><div class="sh-title">Agregar descuento</div><button class="sh-close" onclick="closeSheet('shDescuento')">✕</button></div>
  <div class="sh-body">
    <div class="sh-field">
      <div class="sh-lbl">Importe del descuento *</div>
      <input class="sh-input" type="number" id="disc-monto" placeholder="0.00" step="0.01" min="0" value="<?= $desc_manual_amt > 0 ? $desc_manual_amt : '' ?>">
      <div class="sh-note">Se aplica directo al total de la venta. Escribe 0 para quitar el descuento.</div>
    </div>
    <div class="sh-field">
      <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-sm);padding:10px 13px;font-size:13px;color:var(--t2)">
        Total actual: <strong><?= format_money($venta['total'], $empresa['moneda']) ?></strong>
      </div>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shDescuento')">Cancelar</button>
    <button class="sh-btn-save" onclick="doDescuento()">Aplicar descuento</button>
  </div>
</div>


<!-- ══ SHEET: AGREGAR EXTRA ══ -->
<div class="sh-overlay" id="ov-shExtra" onclick="closeSheet('shExtra')"></div>
<div class="bottom-sheet" id="shExtra">
  <div class="sh-handle"></div>
  <div class="sh-header"><div class="sh-title">Agregar extra</div><button class="sh-close" onclick="closeSheet('shExtra')">✕</button></div>
  <div class="sh-body">
    <div class="sh-field"><div class="sh-lbl">Nombre del extra *</div><input class="sh-input" type="text" id="extra-titulo" placeholder="Ej: Instalación, Flete, Accesorio..."></div>
    <div class="sh-field"><div class="sh-lbl">Descripción (opcional)</div><textarea class="sh-input" id="extra-desc" style="min-height:50px;resize:none" placeholder="Detalle del extra..."></textarea></div>
    <div class="sh-field"><div class="sh-lbl">Total *</div><input class="sh-input" type="number" id="extra-total" placeholder="0.00" step="0.01" min="0"></div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shExtra')">Cancelar</button>
    <button class="sh-btn-save" onclick="doAgregarExtra()">Agregar</button>
  </div>
</div>

<!-- ══ SHEET: CANCELAR RECIBO ══ -->
<div class="sh-overlay" id="ov-shCancelRec" onclick="closeSheet('shCancelRec')"></div>
<div class="bottom-sheet" id="shCancelRec">
  <div class="sh-handle"></div>
  <div class="sh-header"><div class="sh-title">Cancelar recibo</div><button class="sh-close" onclick="closeSheet('shCancelRec')">✕</button></div>
  <div class="sh-body">
    <div class="sh-field"><div id="cancelrec-info" style="background:var(--danger-bg);border:1px solid #fca5a5;border-radius:var(--r-sm);padding:12px;font-size:13px;color:var(--danger);line-height:1.6"></div></div>
    <div class="sh-field"><div class="sh-lbl">Motivo *</div><textarea class="sh-input" id="cancelrec-motivo" style="min-height:64px;resize:none" placeholder="Explica el motivo…"></textarea></div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shCancelRec')">No cancelar</button>
    <button class="sh-btn-save" id="cancelrec-btn" style="background:var(--danger)">Confirmar</button>
  </div>
</div>


<!-- ══ SHEET: CANCELAR VENTA ══ -->
<div class="sh-overlay" id="ov-shCancelar" onclick="closeSheet('shCancelar')"></div>
<div class="bottom-sheet" id="shCancelar">
  <div class="sh-handle"></div>
  <div class="sh-header"><div class="sh-title">Cancelar venta</div><button class="sh-close" onclick="closeSheet('shCancelar')">✕</button></div>
  <div class="sh-body">
    <div class="sh-field">
      <div style="background:var(--danger-bg);border:1px solid #fca5a5;border-radius:var(--r-sm);padding:12px;font-size:13px;color:var(--danger);line-height:1.6">
        <?= ico('alert',14,'#c53030') ?> Se cancelará <strong><?= e($folio) ?></strong>. Solo es posible si no tiene pagos registrados.
      </div>
    </div>
    <div class="sh-field"><div class="sh-lbl">Motivo *</div><textarea class="sh-input" id="cancelar-motivo" style="min-height:64px;resize:none" placeholder="Razón…"></textarea></div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shCancelar')">No cancelar</button>
    <button class="sh-btn-save" style="background:var(--danger)" onclick="doCancelarVenta()">Confirmar</button>
  </div>
</div>


<script>
const VENTA_ID   = <?= $venta_id ?>;
const URL_VTA    = '<?= e($url_vta) ?>';
const CSRF_TOKEN = '<?= csrf_token() ?>';
const ES_ADMIN   = <?= $puede_admin ? 'true' : 'false' ?>;
const PUEDE_VER_CANT = <?= (Auth::es_admin() || Auth::puede('ver_cantidades')) ? 'true' : 'false' ?>;
const MONEDA     = '<?= e($empresa['moneda']) ?>';
const IMP_MODO   = '<?= e($impuesto_modo) ?>';
const IMP_PCT    = <?= (float)$impuesto_pct ?>;
const COT_ID     = <?= (int)$venta['cotizacion_id'] ?>;
// Estado inicial de líneas
const LINEAS_INIT = <?= json_encode(array_map(fn($l) => [
    'id'          => (int)$l['id'],
    'titulo'      => $l['titulo'],
    'sku'         => $l['sku'] ?? '',
    'descripcion' => $l['descripcion'] ?? '',
    'cantidad'    => (float)$l['cantidad'],
    'precio_unit' => (float)$l['precio_unit'],
    'subtotal'    => (float)$l['subtotal'],
], $lineas)) ?>;
const CUPON_AMT   = <?= $cupon_amt ?>;
const CUPON_PCT   = <?= $cupon_pct ?>;
const CUPON_COD   = '<?= e($cupon_codigo) ?>';
const DESC_AUTO_AMT_INIT = <?= $desc_auto_amt ?>;
const DESC_AUTO_PCT_INIT = <?= $desc_auto_pct ?>;
let formaSeleccionada = 'efectivo';
let artSelId = null, artSelPrecio = 0;

// ════════════════════════════════════════════════════════════
// MOTOR DE ESTADO — cambios pendientes (batch save)
// ════════════════════════════════════════════════════════════
let lineas        = JSON.parse(JSON.stringify(LINEAS_INIT)); // copia mutable
let descAutoAmt   = DESC_AUTO_AMT_INIT;
let descAutoPct   = DESC_AUTO_PCT_INIT;
let dirty         = false;

function fmtMoney(n){
  return '$' + parseFloat(n||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function calcTotales(){
  const sub = lineas.reduce((s,l) => s + l.subtotal, 0);
  const cupon = CUPON_AMT;
  const desc  = descAutoAmt;
  let base = sub - cupon - desc;
  let imp = 0;
  if(IMP_MODO === 'suma')     imp = base * IMP_PCT / 100;
  if(IMP_MODO === 'incluido') imp = base - (base / (1 + IMP_PCT/100));
  const total = IMP_MODO === 'suma' ? base + imp : base;
  return {sub, cupon, desc, imp, total: Math.max(0, total)};
}

function renderLineas(){
  const el = document.getElementById('lineas-list');
  if(!el) return;
  if(!lineas.length){ el.innerHTML = '<div style="padding:16px;text-align:center;color:var(--t3);font-size:13px">Sin artículos</div>'; return; }
  el.innerHTML = lineas.map((l,i) => {
    const acciones = ES_ADMIN
      ? `<div class="item-actions">
           <button onclick="uiEditarLinea(${i})" class="line-edit-btn" title="Editar">✏️</button>
           <button onclick="uiEliminarLinea(${i})" class="line-del-btn" title="Eliminar">🗑</button>
         </div>`
      : '';
    const sku  = l.sku  ? `<div class="item-sku">${escHtml(l.sku)}</div>` : '';
    const desc = l.descripcion ? `<div class="item-desc">${escHtml(l.descripcion).replace(/\n/g,'<br>')}</div>` : '';
    const cant = fmtCant(l.cantidad);
    const pu   = l.precio_unit > 0 ? fmtMoney(l.precio_unit) : '—';
    const tot  = fmtMoney(l.subtotal);
    return `
    <div class="item-row">
      <!-- DESKTOP: nombre en celda 1 -->
      <div>
        <div class="item-name">
          <span class="item-name-text">${escHtml(l.titulo)}</span>
          ${acciones}
        </div>
        ${sku}
        ${desc}
        <!-- MOBILE: fila de métricas debajo de descripción -->
        <div class="item-nums-row">
          ${PUEDE_VER_CANT ? `<div class="item-num-cell">
            <span class="item-num-lbl">Cant.</span>
            <span class="item-num-val">${cant}</span>
          </div>
          <div class="item-num-cell">
            <span class="item-num-lbl">P. Unit.</span>
            <span class="item-num-val">${pu}</span>
          </div>` : ''}
          <div class="item-num-cell">
            <span class="item-num-lbl">Total</span>
            <span class="item-num-val total">${tot}</span>
          </div>
        </div>
      </div>
      <!-- DESKTOP: celdas 2-4 -->
      ${PUEDE_VER_CANT ? `<div class="item-cell">${cant}</div>
      <div class="item-cell">${pu}</div>` : ''}
      <div class="item-total">${tot}</div>
    </div>`;
  }).join('');
}

function fmtCant(n){ const s=parseFloat(n||0).toFixed(4); return s.replace(/\.?0+$/,''); }
function escHtml(s){ const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

function renderTotales(){
  const el = document.getElementById('totales-card');
  if(!el) return;
  const t = calcTotales();
  let html = `<div class="tot-row"><span class="tot-lbl">Subtotal artículos</span><span class="tot-val">${fmtMoney(t.sub)}</span></div>`;
  if(t.cupon > 0) html += `<div class="tot-row disc"><span class="tot-lbl">Cupón ${CUPON_COD ? '('+CUPON_COD+')' : ''} ${CUPON_PCT > 0 ? '('+CUPON_PCT+'%)' : ''}</span><span class="tot-val">-${fmtMoney(t.cupon)}</span></div>`;
  if(t.desc > 0)  html += `<div class="tot-row disc"><span class="tot-lbl">Descuento especial${descAutoPct > 0 ? ' ('+descAutoPct+'%)' : ''}
    ${ES_ADMIN ? '<button onclick="uiEliminarDescuento()" style="margin-left:6px;border:none;background:none;cursor:pointer;font-size:11px;color:var(--danger);opacity:.7" title="Eliminar descuento">✕</button>' : ''}
  </span><span class="tot-val">-${fmtMoney(t.desc)}</span></div>`;
  if(IMP_MODO !== 'ninguno' && t.imp > 0) html += `<div class="tot-row"><span class="tot-lbl">IVA (${IMP_PCT}%)</span><span class="tot-val">${fmtMoney(t.imp)}</span></div>`;
  html += `<div class="tot-row final-row"><span class="tot-lbl">Total</span><span class="tot-val">${fmtMoney(t.total)}</span></div>`;
  el.innerHTML = html;
}

function markDirty(isDirty){
  dirty = isDirty;
  const btn = document.getElementById('btn-guardar');
  if(btn) btn.style.display = isDirty ? 'block' : 'none';
}

function render(){ renderLineas(); renderTotales(); }

// ── Acciones sobre líneas (sin guardar aún) ──
function uiEliminarLinea(idx){
  if(!confirm('¿Eliminar este artículo?')) return;
  lineas.splice(idx, 1);
  markDirty(true);
  render();
}

function uiEditarLinea(idx){
  const l = lineas[idx];
  editLineaIdx = idx; // índice en array (no ID de BD)
  document.getElementById('edit-linea-id').value = idx; // reusar el campo
  document.getElementById('el-titulo').value = l.titulo;
  document.getElementById('el-sku').value    = l.sku || '';
  document.getElementById('el-desc').value   = l.descripcion || '';
  document.getElementById('el-cant').value   = l.cantidad;
  document.getElementById('el-precio').value = l.precio_unit;
  actualizarElPreview();
  openSheet('shEditLinea');
}

function uiEliminarDescuento(){
  if(!confirm('¿Eliminar el descuento de '+fmtMoney(descAutoAmt)+'?')) return;
  descAutoAmt = 0;
  descAutoPct = 0;
  markDirty(true);
  render();
}

// ── Sheets ──
function openSheet(id){
    document.getElementById('ov-'+id).classList.add('open');
    document.getElementById(id).classList.add('open');
}
function closeSheet(id){
    document.getElementById('ov-'+id).classList.remove('open');
    document.getElementById(id).classList.remove('open');
}

// ── Forma pago ──
function selForma(el,f){document.querySelectorAll('.forma-opt').forEach(o=>o.classList.remove('selected'));el.classList.add('selected');formaSeleccionada=f}

// ── Copiar URL (punto 7) ──
function copiarUrl(){
  navigator.clipboard.writeText(URL_VTA).then(()=>{
    const b=document.getElementById('btn-copiar');
    b.textContent='URL copiada';
    setTimeout(()=>b.innerHTML='<?= addslashes(ico('link',14)) ?> Copiar URL del cliente',2000);
  }).catch(()=>alert(URL_VTA));
}

// ── Abono (punto 5: ruta /ventas/:id/abono) ──
async function doAbono(){
  const monto=parseFloat(document.getElementById('ab-monto').value);
  if(!monto||monto<=0){alert('Ingresa un monto válido');return}
  const btn=event.target;btn.disabled=true;btn.textContent='Guardando…';
  try{
    const r=await fetch('/ventas/'+VENTA_ID+'/abono',{
      method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
      body:JSON.stringify({
        forma_pago:formaSeleccionada,
        monto,
        concepto:document.getElementById('ab-concepto').value.trim(),
        referencia:document.getElementById('ab-notas').value.trim()
      })
    });
    const d=await r.json();
    if(d.ok){closeSheet('shAbono');location.reload();}
    else{alert(d.error||'Error al guardar');btn.disabled=false;btn.textContent='Guardar abono';}
  }catch(e){alert('Error de conexión: '+e.message);btn.disabled=false;btn.textContent='Guardar abono';}
}

// ── Cancelar recibo ──
function cancelarRec(id,numero,monto){
  document.getElementById('cancelrec-info').innerHTML=`<?= addslashes(ico('alert',14,'#c53030')) ?> Cancelarás <strong>${numero}</strong> por <strong>${monto}</strong>. El saldo de la venta se ajustará.`;
  document.getElementById('cancelrec-motivo').value='';
  document.getElementById('cancelrec-btn').onclick=async()=>{
    const motivo=document.getElementById('cancelrec-motivo').value.trim();
    if(!motivo){alert('Ingresa el motivo');return;}
    const r=await fetch('/ventas/recibos/'+id+'/cancelar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({motivo})});
    const d=await r.json();
    if(d.ok){closeSheet('shCancelRec');location.reload();}
    else alert(d.error||'Error al cancelar');
  };
  openSheet('shCancelRec');
}

// ── Cancelar venta ──
async function doCancelarVenta(){
  const motivo=document.getElementById('cancelar-motivo').value.trim();
  if(!motivo){alert('Ingresa el motivo');return;}
  const r=await fetch('/ventas/'+VENTA_ID+'/cancelar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({motivo})});
  const d=await r.json();
  if(d.ok){closeSheet('shCancelar');location.reload();}
  else alert(d.error||'Error: '+d.error);
}

// ── Tab catálogo/manual ──
function switchItemTab(t){
  document.getElementById('tab-catalogo').style.display=t==='catalogo'?'block':'none';
  document.getElementById('tab-manual').style.display=t==='manual'?'block':'none';
  document.getElementById('art-qty-wrap').style.display='none';
  document.getElementById('tab-cat-btn').style.borderBottomColor=t==='catalogo'?'var(--g)':'transparent';
  document.getElementById('tab-cat-btn').style.color=t==='catalogo'?'var(--g)':'var(--t3)';
  document.getElementById('tab-man-btn').style.borderBottomColor=t==='manual'?'var(--g)':'transparent';
  document.getElementById('tab-man-btn').style.color=t==='manual'?'var(--g)':'var(--t3)';
  artSelId=null;
}

// ── Seleccionar del catálogo ──
let artSelTitulo='', artSelSku='';
function selArt(el,id,titulo,sku,precio){
  artSelId=id; artSelPrecio=precio; artSelTitulo=titulo; artSelSku=sku;
  document.querySelectorAll('.art-item').forEach(a=>a.classList.remove('art-sel'));
  el.classList.add('art-sel');
  document.getElementById('art-qty-wrap').style.display='block';
  document.getElementById('art-qty').value='1';
  document.getElementById('art-preview').textContent='1 × '+precio.toFixed(2)+' = $'+precio.toFixed(2);
}



function filtrarCat(v){
  v=v.toLowerCase();
  document.querySelectorAll('.art-item').forEach(el=>{
    el.style.display=el.textContent.toLowerCase().includes(v)?'flex':'none';
  });
}

// ── Agregar artículo ──
function doAgregarItem(){
  let titulo, sku, desc, cantidad, precio;
  if(artSelId){
    // Del catálogo — buscar en artSelData
    titulo   = artSelTitulo;
    sku      = artSelSku;
    desc     = '';
    cantidad = parseFloat(document.getElementById('art-qty').value)||1;
    precio   = artSelPrecio;
  } else {
    titulo   = document.getElementById('item-titulo').value.trim();
    if(!titulo){alert('El nombre es requerido');return;}
    sku      = document.getElementById('item-sku').value.trim();
    desc     = document.getElementById('item-desc').value.trim();
    cantidad = parseFloat(document.getElementById('item-qty').value)||1;
    precio   = parseFloat(document.getElementById('item-precio').value)||0;
  }
  lineas.push({id:null, titulo, sku, descripcion:desc, cantidad, precio_unit:precio, subtotal:cantidad*precio});
  markDirty(true);
  render();
  closeSheet('shItem');
  // Reset
  artSelId=null; artSelTitulo=''; artSelSku='';
  document.getElementById('art-qty-wrap').style.display='none';
  document.getElementById('item-titulo').value='';
  document.getElementById('item-sku').value='';
  document.getElementById('item-desc').value='';
  document.getElementById('item-qty').value='1';
  document.getElementById('item-precio').value='';
  document.querySelectorAll('.art-item').forEach(a=>a.classList.remove('art-sel'));
}

// ── Agregar extra (simplificado: nombre + total) ──
function doAgregarExtra(){
  const titulo = document.getElementById('extra-titulo').value.trim();
  if(!titulo){alert('El nombre es requerido');return;}
  const desc = document.getElementById('extra-desc').value.trim();
  const total = parseFloat(document.getElementById('extra-total').value)||0;
  if(total <= 0){alert('El total debe ser mayor a 0');return;}
  lineas.push({id:null, titulo, sku:'', descripcion:desc, cantidad:1, precio_unit:total, subtotal:total});
  markDirty(true);
  render();
  closeSheet('shExtra');
  document.getElementById('extra-titulo').value='';
  document.getElementById('extra-desc').value='';
  document.getElementById('extra-total').value='';
}

// ── Descuento ──
function doDescuento(){
  const monto=parseFloat(document.getElementById('disc-monto').value)||0;
  if(monto <= 0){ alert('Ingresa un monto mayor a 0'); return; }
  descAutoAmt = descAutoAmt + monto; // ACUMULA sobre el descuento existente
  descAutoPct = 0;
  document.getElementById('disc-monto').value = '';
  markDirty(true);
  render();
  closeSheet('shDescuento');
}


// ── Editar línea ──
let editLineaId = null;
function editarLinea(id, data){
  editLineaId = id;
  document.getElementById('edit-linea-id').value = id;
  document.getElementById('el-titulo').value = data.titulo;
  document.getElementById('el-sku').value   = data.sku || '';
  document.getElementById('el-desc').value  = data.descripcion || '';
  document.getElementById('el-cant').value  = data.cantidad;
  document.getElementById('el-precio').value= data.precio_unit;
  actualizarElPreview();
  openSheet('shEditLinea');
}
function actualizarElPreview(){
  const c=parseFloat(document.getElementById('el-cant').value)||0;
  const p=parseFloat(document.getElementById('el-precio').value)||0;
  document.getElementById('el-preview').textContent = c > 0 && p > 0 ? c+' × $'+p.toFixed(2)+' = $'+(c*p).toFixed(2) : '';
}

let editLineaIdx = null;
function doEditarLinea(){
  const titulo=document.getElementById('el-titulo').value.trim();
  if(!titulo){alert('El nombre es requerido');return;}
  const cantidad = parseFloat(document.getElementById('el-cant').value)||1;
  const precio   = parseFloat(document.getElementById('el-precio').value)||0;
  if(editLineaIdx !== null && lineas[editLineaIdx]){
    lineas[editLineaIdx] = {
      ...lineas[editLineaIdx],
      titulo, sku:document.getElementById('el-sku').value.trim(),
      descripcion:document.getElementById('el-desc').value.trim(),
      cantidad, precio_unit:precio, subtotal:cantidad*precio
    };
    markDirty(true);
    render();
    closeSheet('shEditLinea');
  }
}

// ── Cambiar cliente ──
let clienteSelId = null, pendingClienteId = null;
function selCliente(el,id,nombre){
  clienteSelId=id; clienteNombre=nombre;
  document.querySelectorAll('#cli-lista .art-item').forEach(a=>a.classList.remove('art-sel'));
  el.classList.add('art-sel');
  document.getElementById('btn-guardar-cliente').disabled=false;
}
function filtrarClientes(v){
  v=v.toLowerCase();
  document.querySelectorAll('#cli-lista .art-item').forEach(el=>{
    el.style.display=el.textContent.toLowerCase().includes(v)?'flex':'none';
  });
}
let clienteNombre = '';
function doGuardarCliente(){
  if(!clienteSelId) return;
  // Actualizar visualmente el meta-val del cliente
  const el = document.querySelector('.meta-val');
  if(el) el.textContent = clienteNombre;
  // Guardar clienteSelId para el batch save
  pendingClienteId = clienteSelId;
  markDirty(true);
  closeSheet('shCliente');
}

// ── Guardar todos los cambios ──
async function guardarCambios(){
  const btn = document.getElementById('btn-guardar');
  if(btn){ btn.disabled=true; btn.textContent='Guardando…'; }
  const t = calcTotales();
  const payload = {
    lineas: lineas.map(l => ({
      id:          l.id || null,
      titulo:      l.titulo,
      sku:         l.sku || '',
      descripcion: l.descripcion || '',
      cantidad:    l.cantidad,
      precio_unit: l.precio_unit,
      subtotal:    l.subtotal,
    })),
    descuento_auto_amt: descAutoAmt,
    descuento_auto_pct: descAutoPct,
    nuevo_total: t.total,
    cliente_id:  pendingClienteId,
  };
  try {
    const r = await fetch('/ventas/'+VENTA_ID+'/guardar', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
      body:JSON.stringify(payload)
    });
    const d = await r.json();
    if(d.ok){ location.reload(); }
    else {
      alert(d.error || 'Error al guardar');
      if(btn){ btn.disabled=false; btn.textContent='💾 Guardar cambios'; }
    }
  } catch(e){
    alert('Error de conexión');
    if(btn){ btn.disabled=false; btn.textContent='💾 Guardar cambios'; }
  }
}

// ── PDF de recibo individual ──
function imprimirRecibo(d){
  const el = document.getElementById('recibo-print-tpl');
  const monto = '$'+parseFloat(d.monto||0).toLocaleString('en-US',{minimumFractionDigits:2});
  const bloque = `
    <div class="rp-header">
      <div class="rp-empresa">${escHtml(d.empresa)}</div>
      <div class="rp-sub">${escHtml(d.ciudad)}${d.tel?' · '+escHtml(d.tel):''}</div>
      <div class="rp-tipo">Recibo de pago</div>
      <div class="rp-folio">${escHtml(d.numero)}</div>
    </div>
    <table class="rp-table">
      <tr><td>Cliente</td><td>${escHtml(d.cliente)}</td></tr>
      <tr><td>Venta</td><td>${escHtml(d.venta)}</td></tr>
      <tr><td>Fecha</td><td>${escHtml(d.fecha)}</td></tr>
      <tr><td>Concepto</td><td>${escHtml(d.concepto)}</td></tr>
      ${d.notas?'<tr><td>Forma</td><td>'+escHtml(d.notas)+'</td></tr>':''}
    </table>
    <div class="rp-monto-box">
      <div class="rp-monto-lbl">Total pagado</div>
      <div class="rp-monto-val">${monto}</div>
    </div>
    <div class="rp-foot">${escHtml(d.empresa)} · gracias por su preferencia</div>
    <div class="rp-sello">✓ ${escHtml(d.numero)} · ${escHtml(d.fecha)}</div>`;
  el.innerHTML = `
    <div class="rp-copia"><div class="rp-copia-lbl">Copia empresa</div>${bloque}</div>
    <hr class="rp-corte">
    <div class="rp-copia"><div class="rp-copia-lbl">Copia cliente</div>${bloque}</div>`;
  document.body.classList.add('modo-recibo');
  window.print();
  document.body.classList.remove('modo-recibo');
}

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
  render();
  // input listener para el editor de línea
  ['el-cant','el-precio'].forEach(id => {
    const el = document.getElementById(id);
    if(el) el.addEventListener('input', actualizarElPreview);
  });
  const q = document.getElementById('art-qty');
  if(q) q.addEventListener('input', () => {
    const n = parseFloat(q.value)||1;
    document.getElementById('art-preview').textContent = n+' × '+artSelPrecio.toFixed(2)+' = $'+(n*artSelPrecio).toFixed(2);
  });
});

// ── Notas ──
let _nt=null;
function guardarNotas(){
  clearTimeout(_nt);
  _nt=setTimeout(async()=>{
    const notas=document.getElementById('notas-txt').value;
    await fetch('/ventas/'+VENTA_ID+'/notas',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({notas_internas:notas})});
  },800);
}
</script>
<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
