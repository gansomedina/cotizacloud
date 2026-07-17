<?php
// ============================================================
//  CotizaCloud — public/cotizacion_inmueble.php
//  Template de slug público para giro=inmuebles
//  Included from cotizacion.php when empresa.giro === 'inmuebles'
//
//  Variables disponibles del caller (cotizacion.php):
//    $cot, $lineas, $lineas_extra, $subtotal, $total_base,
//    $desc_auto_amt, $cupon_monto_guardado, $impuesto_amt,
//    $adc_on, $adc_pct, $adc_exp, $cupones, $adjuntos,
//    $ini_emp, $estado, $es_activa, $badge_bg, $badge_color, $badge_lbl,
//    $ocultar_cp, $th (theme colors)
// ============================================================
defined('COTIZAAPP') or die;

$propiedad = null;
if (!empty($lineas[0]['articulo_id'])) {
    $propiedad = DB::row(
        "SELECT p.* FROM propiedades p WHERE p.articulo_id = ? LIMIT 1",
        [(int)$lineas[0]['articulo_id']]
    );
}

$fotos = ($propiedad && $propiedad['fotos']) ? json_decode($propiedad['fotos'], true) : [];
$fotos = is_array($fotos) ? $fotos : [];

$tipo_op_labels = ['venta'=>'Venta','renta'=>'Renta','renta_temporal'=>'Renta temporal'];
$tipo_prop_labels = ['casa'=>'Casa','departamento'=>'Departamento','terreno'=>'Terreno','local_comercial'=>'Local comercial','oficina'=>'Oficina','bodega'=>'Bodega'];
$tipo_op = $tipo_op_labels[$propiedad['tipo_operacion'] ?? 'venta'] ?? 'Venta';
$tipo_prop = $tipo_prop_labels[$propiedad['tipo_propiedad'] ?? 'casa'] ?? 'Casa';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title><?= e($cot['titulo']) ?> · <?= e($cot['emp_nombre']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--g:<?=$th['g']?>;--glt:<?=$th['glt']?>;--gbd:<?=$th['gbd']?>;--text:#111;--t2:#444;--t3:#888;--bd:#d8d8d8;--bg:#f7f7f5;--white:#fff;--amb:#92400e;--red:#b91c1c;--r:6px}
*{box-sizing:border-box;margin:0;padding:0}
html{font-size:17px;-webkit-text-size-adjust:100%}
body{font-family:'Plus Jakarta Sans',-apple-system,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased;overflow-x:hidden}

.hdr{background:var(--white);border-bottom:2px solid var(--text);text-align:center;padding:12px 20px 0}
.hdr-inner{max-width:960px;margin:0 auto}
.hdr-logo{width:160px;height:70px;background:var(--g);color:#fff;font:700 28px 'Plus Jakarta Sans',sans-serif;display:inline-flex;align-items:center;justify-content:center;margin-bottom:6px}
.hdr-co{font:800 22px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.02em;margin-bottom:2px}
.hdr-tag{font-size:13px;color:var(--t3);line-height:1.3}
.hdr-rfc{font:500 11px 'DM Sans',sans-serif;color:var(--t3);letter-spacing:.04em;margin-top:1px;margin-bottom:4px}
.hdr-cnt{display:flex;flex-wrap:wrap;justify-content:center;gap:3px 16px;font-size:13px;color:var(--t2);padding-bottom:10px}
.hdr-cnt a{color:var(--t2);text-decoration:none}

.body{padding:0 0 60px}
.wrap{max-width:960px;margin:0 auto;padding:0 20px}
.slbl{font:700 14px 'Plus Jakarta Sans',sans-serif;letter-spacing:.04em;text-transform:uppercase;color:var(--text);margin:28px 0 12px;display:flex;align-items:center;gap:12px}
.slbl::after{content:'';flex:1;height:1px;background:var(--bd)}

/* Gallery */
.gallery{position:relative;width:100%;border-radius:var(--r);overflow:hidden;background:var(--bg);border:1px solid var(--bd);margin-top:20px}
.gallery-main{width:100%;aspect-ratio:16/10;object-fit:cover;display:block}
.gallery-empty{width:100%;aspect-ratio:16/10;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--glt),var(--bg));font:600 16px 'Plus Jakarta Sans',sans-serif;color:var(--t3)}
.gallery-thumbs{display:flex;gap:6px;padding:8px;overflow-x:auto;background:var(--white)}
.gallery-thumbs img{width:72px;height:54px;object-fit:cover;border-radius:4px;border:2px solid transparent;cursor:pointer;flex-shrink:0;transition:border-color .15s}
.gallery-thumbs img.on{border-color:var(--g)}
.gallery-nav{position:absolute;top:50%;transform:translateY(-70%);width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.85);border:none;font:700 18px sans-serif;color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.12)}
.gallery-nav.prev{left:10px}
.gallery-nav.next{right:10px}

/* Property card */
.prop-card{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;margin-top:16px}
.prop-top{padding:24px 22px 20px}
.prop-badges{display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap}
.prop-badge{padding:5px 14px;border-radius:99px;font:600 12px 'Plus Jakarta Sans',sans-serif;letter-spacing:.02em}
.prop-title{font:800 26px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.025em;line-height:1.2;margin-bottom:8px}
.prop-address{font-size:15px;color:var(--t2);line-height:1.6}

/* Specs grid */
.specs{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:10px;margin-top:20px}
.spec{background:var(--white);border:1px solid var(--bd);border-radius:14px;padding:18px 12px;text-align:center;transition:border-color .15s}
.spec:hover{border-color:var(--gbd)}
.spec-ico{font-size:26px;margin-bottom:6px;line-height:1}
.spec-val{font:700 18px 'Plus Jakarta Sans',sans-serif;color:var(--text);margin-bottom:2px}
.spec-lbl{font:500 11px 'Plus Jakarta Sans',sans-serif;color:var(--t3)}

/* Price section — separated to force scroll */
.prop-price-section{background:var(--glt);border:1.5px solid var(--gbd);border-radius:var(--r);padding:28px 24px;margin-top:24px;text-align:center}
.prop-price-lbl{font:600 11px 'Plus Jakarta Sans',sans-serif;letter-spacing:.08em;text-transform:uppercase;color:var(--g);opacity:.7;margin-bottom:6px}
.prop-price-amount{font:800 36px 'Plus Jakarta Sans',sans-serif;color:var(--g);letter-spacing:-.02em;margin-bottom:4px}
.prop-price-curr{font:500 14px 'Plus Jakarta Sans',sans-serif;color:var(--t3)}

/* Description */
.desc-block{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);padding:20px 22px;margin-top:16px;font-size:16px;color:var(--t2);line-height:1.7}

/* Info pills */
.info-pills{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px;justify-content:center}
.info-pill{padding:10px 16px;background:var(--white);border:1px solid var(--bd);border-radius:10px;display:flex;flex-direction:column;white-space:nowrap}
.info-pill-lbl{font:600 10px 'Plus Jakarta Sans',sans-serif;color:var(--t3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px}
.info-pill-val{font:600 14px 'Plus Jakarta Sans',sans-serif;color:var(--text)}

/* Totals */
.tots{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;margin-top:16px}
.tr{display:flex;justify-content:space-between;align-items:center;padding:13px 20px;border-bottom:1px solid var(--bd);font-size:16px}
.tr:last-child{border-bottom:none}
.tl{color:var(--t2)}
.tv{font:600 16px 'Plus Jakarta Sans',sans-serif;font-variant-numeric:tabular-nums}
.td .tl,.td .tv{color:var(--amb)}
.tf{background:var(--bg);border-top:2px solid var(--text)!important}
.tf .tl{font:700 17px 'Plus Jakarta Sans',sans-serif}
.tf .tv{font:800 24px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.02em}

/* CTAs */
.cta{display:flex;flex-direction:column;gap:10px;margin-top:20px}
.bacc{width:100%;padding:16px;background:var(--g);border:none;border-radius:var(--r);font:700 16px 'Plus Jakarta Sans',sans-serif;color:#fff;cursor:pointer;letter-spacing:-.01em}
.brej{width:100%;padding:13px;background:var(--white);border:1.5px solid var(--bd);border-radius:var(--r);font:500 15px 'Plus Jakarta Sans',sans-serif;color:var(--t2);cursor:pointer}
.bprt{width:100%;padding:12px;background:var(--bg);border:1px solid var(--bd);border-radius:var(--r);font:500 14px 'Plus Jakarta Sans',sans-serif;color:var(--t2);cursor:pointer}

/* Notes / Terms */
.notes{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);padding:16px 20px;font-size:16px;color:var(--t2);line-height:1.7}
.terms{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden}
.term{padding:13px 18px;border-bottom:1px solid var(--bd)}
.term:last-child{border-bottom:none}
.terml{font:700 10px 'Plus Jakarta Sans',sans-serif;letter-spacing:.09em;text-transform:uppercase;color:var(--t3);margin-bottom:5px}
.termv{font-size:15px;color:var(--t2);line-height:1.6}
.termv a{color:var(--g)}

/* Footer */
.footer{background:var(--white);border-top:2px solid var(--text);padding:20px 20px 40px;text-align:center}
.footer-inner{max-width:960px;margin:0 auto}
.flogo{width:80px;height:80px;border-radius:14px;background:var(--g);color:#fff;font:700 22px 'Plus Jakarta Sans',sans-serif;display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px}
.fname2{font:700 15px 'Plus Jakarta Sans',sans-serif;margin-bottom:2px}
.fsub{font-size:13px;color:var(--t3);margin-bottom:14px}
.fnotice{font-size:12px;color:var(--t3);line-height:1.65;background:#f7f5f0;border:1px solid var(--bd);border-radius:8px;padding:14px 18px;margin:22px auto 0;max-width:560px;text-align:left}
.fnotice a{color:var(--g);text-decoration:underline}

/* Success screen */
.succ{display:none;padding:60px 20px 40px;text-align:center;max-width:480px;margin:0 auto}
.succ.on{display:block}
.sico{font-size:48px;margin-bottom:14px}
.stit{font:800 22px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.02em;margin-bottom:10px}
.smsg{font-size:16px;color:var(--t2);line-height:1.6;margin-bottom:18px}
.sbox{background:var(--glt);border:1px solid var(--gbd);border-radius:var(--r);padding:14px 18px;font-size:14px;color:var(--g)}

/* Modals */
.ov{position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);display:flex;align-items:flex-end;justify-content:center;opacity:0;pointer-events:none;transition:opacity .22s}
.ov.on{opacity:1;pointer-events:all}
.modal{background:var(--white);border-radius:16px 16px 0 0;border-top:2px solid var(--text);padding:20px 20px 48px;width:100%;max-width:520px;transform:translateY(100%);transition:transform .28s cubic-bezier(.32,0,.15,1);max-height:92vh;overflow-y:auto}
.ov.on .modal{transform:translateY(0)}
.mpull{width:32px;height:3px;border-radius:2px;background:var(--bd);margin:0 auto 18px}
.mtit{font:800 20px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.02em;margin-bottom:6px}
.msub{font-size:14px;color:var(--t2);margin-bottom:16px;line-height:1.5}
.sbox2{background:var(--bg);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;margin-bottom:16px}
.sr{display:flex;justify-content:space-between;padding:10px 14px;font-size:14px;color:var(--t2);border-bottom:1px solid var(--bd)}
.sr:last-child{border-bottom:none}
.sr.tot{font:700 17px 'Plus Jakarta Sans',sans-serif;color:var(--text);background:var(--white)}
.acc-msg{margin-top:12px;padding:12px 14px;background:var(--bg);border-radius:10px;font:400 13px 'Plus Jakarta Sans',sans-serif;color:var(--t2);line-height:1.6;white-space:pre-wrap}
.flbl{font:700 10px 'Plus Jakarta Sans',sans-serif;letter-spacing:.09em;text-transform:uppercase;color:var(--t3);margin-bottom:7px}
.finp{width:100%;padding:13px 14px;border:1.5px solid var(--bd);border-radius:var(--r);font:400 16px 'Plus Jakarta Sans',sans-serif;color:var(--text);background:var(--bg);outline:none;margin-bottom:14px;transition:border-color .15s}
.finp:focus{border-color:var(--g)}
.mbok{width:100%;padding:15px;background:var(--g);border:none;border-radius:var(--r);font:700 15px 'Plus Jakarta Sans',sans-serif;color:#fff;cursor:pointer;margin-bottom:8px}
.mbno{width:100%;padding:13px;border:1.5px solid var(--bd);background:transparent;border-radius:var(--r);font:500 14px 'Plus Jakarta Sans',sans-serif;color:var(--t2);cursor:pointer}
.ropt{width:100%;padding:13px 16px;border:1.5px solid var(--bd);border-radius:var(--r);background:var(--bg);font:400 15px 'Plus Jakarta Sans',sans-serif;color:var(--text);cursor:pointer;text-align:left;margin-bottom:8px;display:block;transition:all .12s}
.ropt.on{border-color:var(--g);background:var(--glt);color:var(--g);font-weight:600}
.rtxt{width:100%;padding:12px 14px;border:1.5px solid var(--bd);border-radius:var(--r);background:var(--bg);font:400 15px 'Plus Jakarta Sans',sans-serif;color:var(--text);resize:none;outline:none;margin-bottom:14px;display:none}
.rtxt.on{display:block}
.mbrej{width:100%;padding:15px;background:var(--red);border:none;border-radius:var(--r);font:700 15px 'Plus Jakarta Sans',sans-serif;color:#fff;cursor:pointer;margin-bottom:8px}

@media print{
  @page{margin:12mm 14mm;size:letter}
  body{background:#fff}
  .gallery-nav,.gallery-thumbs,.cta,.ov,.succ,.footer{display:none!important}
  .gallery-main{max-height:300px;object-fit:contain}
}
</style>
<?= MarketingPixels::scripts_base(EMPRESA_ID) ?>
</head>
<body>

<!-- HEADER -->
<div class="hdr">
  <div class="hdr-inner">
    <?php if (!empty($cot['emp_logo'])): ?>
    <div class="hdr-logo" style="background:none;width:auto;height:auto;max-width:200px;max-height:80px"><img src="<?= e($cot['emp_logo']) ?>" alt="Logo" style="max-width:200px;max-height:80px;object-fit:contain"></div>
    <?php else: ?>
    <div class="hdr-logo"><?= e($ini_emp) ?></div>
    <?php endif; ?>
    <div class="hdr-co"><?= e($cot['emp_nombre']) ?></div>
    <?php if (!empty($cot['emp_direccion']) || !empty($cot['emp_ciudad'])): ?>
    <div class="hdr-tag"><?= e(implode(', ', array_filter([$cot['emp_direccion'] ?? '', $cot['emp_ciudad'] ?? '']))) ?></div>
    <?php endif; ?>
    <?php if (!empty($cot['emp_rfc'])): ?>
    <div class="hdr-rfc">RFC: <?= e($cot['emp_rfc']) ?></div>
    <?php endif; ?>
    <div class="hdr-cnt">
        <?php if ($cot['emp_tel']): ?><a href="tel:<?= e($cot['emp_tel']) ?>"><?= e($cot['emp_tel']) ?></a><?php endif; ?>
        <?php if ($cot['emp_email']): ?><a href="mailto:<?= e($cot['emp_email']) ?>"><?= e($cot['emp_email']) ?></a><?php endif; ?>
        <?php if (!empty($cot['emp_web'])): ?><a href="<?= e($cot['emp_web']) ?>" target="_blank"><?= e(preg_replace('#^https?://#','',$cot['emp_web'])) ?></a><?php endif; ?>
    </div>
  </div>
</div>

<div class="body" id="mainBody">
<div class="wrap">

<!-- INFO COTIZACIÓN — arriba para forzar scroll al precio -->
<?php
  $vts = $cot['valida_hasta'] ? strtotime($cot['valida_hasta']) : 0;
  $vd  = $vts ? ($vts - strtotime('today')) / 86400 : 999;
?>
<div class="info-pills" style="margin-top:20px">
  <div class="info-pill">
    <span class="info-pill-lbl">Cotización</span>
    <span class="info-pill-val"><?= e($cot['numero']) ?></span>
  </div>
  <div class="info-pill">
    <span class="info-pill-lbl">Asesor</span>
    <span class="info-pill-val"><?= e($cot['asesor_nombre'] ?? '—') ?></span>
  </div>
  <?php if ($cot['cliente_nombre']): ?>
  <div class="info-pill">
    <span class="info-pill-lbl">Cliente</span>
    <span class="info-pill-val"><?= e($cot['cliente_nombre']) ?></span>
  </div>
  <?php endif; ?>
  <div class="info-pill">
    <span class="info-pill-lbl">Fecha</span>
    <span class="info-pill-val"><?= date('d/m/Y', strtotime($cot['created_at'])) ?></span>
  </div>
  <?php if ($vts): ?>
  <div class="info-pill" <?php if ($vd < 0): ?>style="border-color:#fca5a5;background:#fff5f5"<?php elseif ($vd <= 3): ?>style="border-color:#fcd34d;background:#fffbeb"<?php endif; ?>>
    <span class="info-pill-lbl"><?= $vd < 0 ? 'Venció' : 'Vencimiento' ?></span>
    <span class="info-pill-val" <?php if ($vd<0): ?>style="color:#c53030"<?php elseif ($vd<=3): ?>style="color:#92400e"<?php endif; ?>><?= date('d/m/Y', $vts) ?></span>
  </div>
  <?php endif; ?>
</div>

<!-- GALLERY -->
<?php if (!empty($fotos)): ?>
<div class="gallery">
  <img class="gallery-main" id="galleryMain" src="<?= e(BASE_URL . UPLOADS_URL . '/' . $fotos[0]) ?>" alt="<?= e($cot['titulo']) ?>">
  <?php if (count($fotos) > 1): ?>
  <button class="gallery-nav prev" onclick="galNav(-1)">‹</button>
  <button class="gallery-nav next" onclick="galNav(1)">›</button>
  <div class="gallery-thumbs">
    <?php foreach ($fotos as $i => $f): ?>
    <img src="<?= e(BASE_URL . UPLOADS_URL . '/' . $f) ?>" class="<?= $i===0?'on':'' ?>" onclick="galGo(<?= $i ?>)" alt="">
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="gallery">
  <div class="gallery-empty"><?= e($tipo_prop) ?></div>
</div>
<?php endif; ?>

<!-- PROPERTY INFO -->
<div class="prop-card">
  <div class="prop-top">
    <div class="prop-badges">
      <span class="prop-badge" style="background:var(--glt);color:var(--g)"><?= e($tipo_op) ?></span>
      <span class="prop-badge" style="background:var(--bg);color:var(--t2)"><?= e($tipo_prop) ?></span>
    </div>
    <div class="prop-title"><?= e($cot['titulo']) ?></div>
    <?php if (!empty($lineas) && ($lineas[0]['descripcion'] ?? '')): ?>
    <div class="prop-address"><?= nl2br(e($lineas[0]['descripcion'])) ?></div>
    <?php endif; ?>
  </div>
</div>

<!-- SPECS -->
<?php
$specs = [];
if ($propiedad) {
    if ($propiedad['m2_terreno'])      $specs[] = ['ico'=>'📐', 'val'=>number_format($propiedad['m2_terreno'],0).' m²', 'lbl'=>'Terreno'];
    if ($propiedad['m2_construccion']) $specs[] = ['ico'=>'🏗', 'val'=>number_format($propiedad['m2_construccion'],0).' m²', 'lbl'=>'Construcción'];
    if ($propiedad['recamaras'])       $specs[] = ['ico'=>'🛏', 'val'=>$propiedad['recamaras'], 'lbl'=>'Recámaras'];
    if ($propiedad['banos'])           $specs[] = ['ico'=>'🚿', 'val'=>number_format($propiedad['banos'],1), 'lbl'=>'Baños'];
}
?>
<?php if ($specs): ?>
<div class="specs" id="itemsBlock">
  <?php foreach ($specs as $s): ?>
  <div class="spec">
    <div class="spec-ico"><?= $s['ico'] ?></div>
    <div class="spec-val"><?= e($s['val']) ?></div>
    <div class="spec-lbl"><?= e($s['lbl']) ?></div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ENCABEZADO / SALUDO -->
<?php
$encabezado_raw = trim($cot['cot_encabezado'] ?? '');
if ($encabezado_raw !== ''):
    $encabezado = str_replace(
        ['{{cliente}}', '{{empresa}}', '{{asesor}}'],
        [e($cot['cliente_nombre'] ?? ''), e($cot['emp_nombre']), e($cot['asesor_nombre'] ?? '')],
        e_html($encabezado_raw)
    );
?>
<div class="desc-block" style="margin-top:20px">
  <?= nl2br($encabezado) ?>
</div>
<?php endif; ?>

<!-- PRECIO — separado para forzar scroll y activar Radar -->
<div class="prop-price-section" id="totalsScreen">
  <div class="prop-price-lbl">Precio</div>
  <div class="prop-price-amount"><?= fmt_pub((float)$cot['total']) ?></div>
  <div class="prop-price-curr"><?= e($cot['moneda'] ?? 'MXN') ?> · <?= e($tipo_op) ?></div>
</div>


<!-- TOTALS -->
<?php if ($desc_auto_amt > 0 || $cupon_monto_guardado > 0 || $cot['impuesto_modo'] !== 'ninguno' || !empty($lineas_extra)): ?>
<div class="slbl">Resumen</div>
<div class="tots" id="totalsScreen">
  <div class="tr"><span class="tl">Precio</span><span class="tv" id="tSub"><?= fmt_pub($subtotal) ?></span></div>
  <?php if ($desc_auto_amt > 0): ?>
  <div class="tr td" id="tAR">
    <span class="tl" id="tAL">Descuento<?= $adc_pct > 0 ? ' (' . number_format($adc_pct,0) . '%)' : '' ?></span>
    <span class="tv" id="tAV">-<?= fmt_pub($desc_auto_amt) ?></span>
  </div>
  <?php else: ?>
  <div class="tr td" id="tAR" style="display:none"><span class="tl" id="tAL">Descuento</span><span class="tv" id="tAV">—</span></div>
  <?php endif; ?>
  <?php if ($cupon_monto_guardado > 0): ?>
  <div class="tr td" id="tCR">
    <span class="tl" id="tCL">Cupón <?= e($cot['cupon_codigo'] ?? '') ?></span>
    <span class="tv" id="tCV">-<?= fmt_pub($cupon_monto_guardado) ?></span>
  </div>
  <?php else: ?>
  <div class="tr td" id="tCR" style="display:none"><span class="tl" id="tCL">Cupón</span><span class="tv" id="tCV">—</span></div>
  <?php endif; ?>
  <?php if ($cot['impuesto_modo'] !== 'ninguno'): ?>
  <div class="tr"><span class="tl"><?= e($cot['impuesto_label'] ?: 'IVA') ?> (<?= (float)$cot['impuesto_pct'] ?>%)</span><span class="tv"><?= fmt_pub($impuesto_amt) ?></span></div>
  <?php endif; ?>
  <div class="tr tf"><span class="tl">Total</span><span class="tv" id="tTot"><?= fmt_pub($total_base) ?></span></div>
</div>
<?php endif; ?>

<!-- ADJUNTOS -->
<?php if (!empty($adjuntos)): ?>
<div class="slbl">Archivos adjuntos</div>
<div style="display:flex;flex-direction:column;gap:8px">
  <?php $adj_num = 0; foreach ($adjuntos as $adj):
      $adj_num++;
      $ext = strtolower(pathinfo($adj['nombre_original'], PATHINFO_EXTENSION));
      $is_img = in_array($ext, ['jpg','jpeg','png','gif','webp']);
      $ico = $is_img ? '🖼' : '📎';
      $size_kb = round($adj['tamano_bytes'] / 1024);
      $size_txt = $size_kb >= 1024 ? number_format($size_kb/1024, 1).' MB' : $size_kb.' KB';
      $file_url = BASE_URL . UPLOADS_URL . '/' . $adj['nombre_archivo'];
      $label = $is_img ? 'Ver Imagen Adjunta ' . $adj_num : 'Ver Documento Adjunto ' . $adj_num;
  ?>
  <a href="<?= e($file_url) ?>" target="_blank" rel="noopener"
     style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--white);border:1.5px solid var(--bd);border-radius:var(--r);text-decoration:none;transition:border-color .15s"
     onmouseover="this.style.borderColor='var(--g)'" onmouseout="this.style.borderColor='var(--bd)'">
    <span style="font-size:24px;flex-shrink:0"><?= $ico ?></span>
    <div style="flex:1;min-width:0">
      <div style="font:600 14px 'Plus Jakarta Sans',sans-serif;color:var(--text)"><?= $label ?></div>
      <div style="font:400 12px 'Plus Jakarta Sans',sans-serif;color:var(--t3);margin-top:2px"><?= $size_txt ?> · <?= strtoupper($ext) ?></div>
    </div>
    <span style="font:600 12px 'Plus Jakarta Sans',sans-serif;color:var(--g);white-space:nowrap;padding:6px 12px;background:var(--glt);border-radius:var(--r)">Abrir</span>
  </a>
  <?php endforeach ?>
</div>
<?php endif ?>

<!-- CTAs -->
<?php if ($es_activa): ?>
<div class="cta">
  <button class="bacc" onclick="openM('acceptOv')">✓ Me interesa</button>
  <button class="brej" onclick="openM('rejectOv')">No es lo que busco</button>
  <button class="bprt" onclick="window.print()">Imprimir / Guardar PDF</button>
</div>
<?php elseif ($estado === 'aceptada'): ?>
<div style="margin-top:20px;padding:20px 24px;background:var(--glt);border:1px solid var(--gbd);border-radius:var(--r);text-align:center;">
  <div style="font:700 18px 'Plus Jakarta Sans',sans-serif;color:var(--g);margin-bottom:8px">✓ Propiedad apartada</div>
  <?php if (!empty($cot['texto_aceptar'])): ?>
  <div style="font-size:15px;color:var(--t2);line-height:1.6"><?= nl2br(e_html($cot['texto_aceptar'])) ?></div>
  <?php else: ?>
  <div style="font-size:14px;color:var(--g);opacity:.8">Gracias por su interés. Nos pondremos en contacto.</div>
  <?php endif; ?>
</div>
<div style="margin-top:12px">
  <button class="bprt" onclick="window.print()">Imprimir / Guardar PDF</button>
</div>
<?php elseif ($estado === 'rechazada'): ?>
<div style="margin-top:20px;padding:20px 24px;background:#fff5f5;border:1px solid #fca5a5;border-radius:var(--r);text-align:center;">
  <div style="font:700 16px 'Plus Jakarta Sans',sans-serif;color:#c53030;margin-bottom:6px">Cotización rechazada</div>
  <?php if (!empty($cot['texto_rechazar'])): ?>
  <div style="font-size:14px;color:var(--t2);line-height:1.6"><?= nl2br(e_html($cot['texto_rechazar'])) ?></div>
  <?php else: ?>
  <div style="font-size:14px;color:#c53030;opacity:.8">Gracias por su tiempo.</div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- NOTAS CLIENTE -->
<?php if (!empty($cot['notas_cliente'])): ?>
<div class="slbl">Notas</div>
<div class="notes"><?= nl2br(e($cot['notas_cliente'])) ?></div>
<?php endif; ?>

<!-- TÉRMINOS Y CONDICIONES -->
<?php if ($cot['terminos']): ?>
<div class="slbl">Términos y condiciones</div>
<div class="terms">
  <?php if (str_contains($cot['terminos'], '<')): ?>
    <div class="term"><div class="termv"><?= nl2br(e_html($cot['terminos'])) ?></div></div>
  <?php else:
  $terminos_lines = array_filter(explode("\n", trim($cot['terminos'])));
  foreach ($terminos_lines as $linea):
      $linea = trim($linea);
      if (!$linea) continue;
      if (str_starts_with($linea, '##')) {
          echo '<div class="term"><div class="terml">' . e(ltrim($linea,'# ')) . '</div></div>';
      } else {
          echo '<div class="term"><div class="termv">' . nl2br(e($linea)) . '</div></div>';
      }
  endforeach;
  endif; ?>
</div>
<?php endif; ?>

<div style="height:10px"></div>
</div><!-- /wrap -->
</div><!-- /body -->

<!-- SUCCESS SCREEN -->
<div class="succ" id="succWrap">
  <div class="sico" id="sIco"><?= ico('check', 48, '#16a34a') ?></div>
  <div class="stit" id="sTit"></div>
  <div class="smsg" id="sMsg"></div>
  <div class="sbox" id="sBox"></div>
</div>

<!-- MODAL: ACEPTAR -->
<div class="ov" id="acceptOv" onclick="if(event.target===this)closeM('acceptOv')">
  <div class="modal">
    <div class="mpull"></div>
    <div class="mtit">¿Te interesa esta propiedad?</div>
    <div class="msub">Al confirmar, tu asesor recibirá una notificación y se pondrá en contacto contigo.</div>
    <div class="sbox2">
      <div class="sr"><span>Propiedad</span><span style="font-weight:600"><?= e($cot['titulo']) ?></span></div>
      <div class="sr"><span><?= e($tipo_op) ?></span><span style="font-weight:600"><?= e($tipo_prop) ?></span></div>
      <div class="sr tot"><span>Precio</span><span id="mTot"><?= fmt_pub($total_base) ?></span></div>
    </div>
    <div class="flbl">Tu nombre</div>
    <input class="finp" id="accNombre" placeholder="Nombre completo" value="<?= e($cot['cliente_nombre'] ?? '') ?>">
    <div class="flbl">Comentario (opcional)</div>
    <textarea class="finp" id="accMsg" rows="2" placeholder="¿Alguna duda o comentario?" style="resize:none"></textarea>
    <button class="mbok" id="btnAcc" onclick="doAccept()">Confirmar interés</button>
    <button class="mbno" onclick="closeM('acceptOv')">Cancelar</button>
  </div>
</div>

<!-- MODAL: RECHAZAR -->
<div class="ov" id="rejectOv" onclick="if(event.target===this)closeM('rejectOv')">
  <div class="modal">
    <div class="mpull"></div>
    <div class="mtit">¿Qué no te convenció?</div>
    <div class="msub">Tu respuesta ayuda al asesor a encontrar mejores opciones.</div>
    <button class="ropt" onclick="selR(this,'Precio fuera de mi presupuesto')">Precio fuera de mi presupuesto</button>
    <button class="ropt" onclick="selR(this,'La ubicación no me conviene')">La ubicación no me conviene</button>
    <button class="ropt" onclick="selR(this,'Busco algo diferente')">Busco algo diferente</button>
    <button class="ropt" onclick="selR(this,'Otro motivo')">Otro motivo</button>
    <textarea class="rtxt" id="rejTxt" rows="3" placeholder="Cuéntanos más…"></textarea>
    <button class="mbrej" id="btnRej" onclick="doReject()">Enviar respuesta</button>
    <button class="mbno" onclick="closeM('rejectOv')">Cancelar</button>
  </div>
</div>

<!-- FOOTER -->
<div class="footer">
  <div class="footer-inner">
    <?php if (!empty($cot['emp_logo'])): ?>
    <div class="flogo" style="background:none;width:auto;height:auto;max-width:120px;max-height:80px;margin:0 auto 10px"><img src="<?= e($cot['emp_logo']) ?>" alt="" style="max-width:120px;max-height:80px;object-fit:contain"></div>
    <?php else: ?>
    <div class="flogo"><?= e($ini_emp) ?></div>
    <?php endif; ?>
    <div class="fname2"><?= e($cot['emp_nombre']) ?></div>
    <?php if (!empty($cot['emp_ciudad'])): ?>
    <div class="fsub"><?= e($cot['emp_ciudad']) ?></div>
    <?php endif; ?>
    <div class="fnotice">
      Esta cotización fue solicitada por usted. Usted acepta el uso de cookies y tecnologías de medición. Puede solicitar la cancelación de esta cotización o de sus datos en cualquier momento. <a href="https://cotiza.cloud/privacidad" target="_blank" rel="noopener">Más información y Aviso de Privacidad</a>.
    </div>
  </div>
</div>

<script>
// ── Gallery ──
<?php if (count($fotos) > 1): ?>
var galIdx = 0;
var galFotos = <?= json_encode(array_map(fn($f) => BASE_URL . UPLOADS_URL . '/' . $f, $fotos)) ?>;
function galGo(i) {
    galIdx = i;
    document.getElementById('galleryMain').src = galFotos[i];
    document.querySelectorAll('.gallery-thumbs img').forEach(function(t,j){ t.classList.toggle('on', j===i); });
}
function galNav(d) {
    galGo((galIdx + d + galFotos.length) % galFotos.length);
}
<?php endif; ?>

// ── Modals ──
function openM(id) { document.getElementById(id).classList.add('on'); if(window.czTrack) czTrack(id==='acceptOv'?'accept_open':'reject_open'); }
function closeM(id) { document.getElementById(id).classList.remove('on'); }

// ── Reject ──
var rejSel = '';
function selR(btn, txt) {
    document.querySelectorAll('.ropt').forEach(function(b){ b.classList.remove('on'); });
    btn.classList.add('on');
    rejSel = txt;
    document.getElementById('rejTxt').classList.toggle('on', txt === 'Otro motivo');
}

// ── Accept ──
function doAccept() {
    var btn = document.getElementById('btnAcc');
    if (btn.disabled) return;
    btn.disabled = true; btn.textContent = 'Enviando…';
    var body = {
        cotizacion_id: <?= (int)$cot['id'] ?>,
        accion: 'aceptar',
        nombre: document.getElementById('accNombre').value.trim(),
        mensaje: document.getElementById('accMsg').value.trim()
    };
    fetch('/api/quote-action', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.ok) {
            closeM('acceptOv');
            document.getElementById('mainBody').style.display = 'none';
            var w = document.getElementById('succWrap');
            w.classList.add('on');
            document.getElementById('sTit').textContent = '¡Excelente!';
            document.getElementById('sMsg').textContent = 'Tu asesor ha sido notificado y se pondrá en contacto contigo.';
            document.getElementById('sBox').textContent = '<?= e($cot['emp_nombre']) ?> · <?= e($cot['emp_tel'] ?? '') ?>';
            if(window.czTrack) czTrack('quote_accept');
        } else {
            alert(d.error || 'Error al enviar.');
            btn.disabled = false; btn.textContent = 'Confirmar interés';
        }
    }).catch(function(){
        alert('Error de conexión.');
        btn.disabled = false; btn.textContent = 'Confirmar interés';
    });
}

// ── Reject action ──
function doReject() {
    var btn = document.getElementById('btnRej');
    if (btn.disabled) return;
    if (!rejSel) { alert('Selecciona un motivo.'); return; }
    btn.disabled = true; btn.textContent = 'Enviando…';
    var motivo = rejSel === 'Otro motivo' ? document.getElementById('rejTxt').value.trim() : rejSel;
    fetch('/api/quote-action', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cotizacion_id: <?= (int)$cot['id'] ?>, accion: 'rechazar', motivo: motivo })
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.ok) {
            closeM('rejectOv');
            document.getElementById('mainBody').style.display = 'none';
            var w = document.getElementById('succWrap');
            w.classList.add('on');
            document.getElementById('sIco').innerHTML = '';
            document.getElementById('sIco').textContent = '📩';
            document.getElementById('sTit').textContent = 'Respuesta enviada';
            document.getElementById('sMsg').textContent = 'Gracias por tu tiempo. Tu asesor buscará mejores opciones para ti.';
            if(window.czTrack) czTrack('quote_reject');
        } else {
            alert(d.error || 'Error al enviar.');
            btn.disabled = false; btn.textContent = 'Enviar respuesta';
        }
    }).catch(function(){
        alert('Error de conexión.');
        btn.disabled = false; btn.textContent = 'Enviar respuesta';
    });
}
</script>

<?php
// ── Tracking JS (same as cotizacion.php) ──
$cot_id_js   = (int)$cot['id'];
$eid_js      = (int)EMPRESA_ID;
$vid_js      = htmlspecialchars($visitor_id_cookie ?? '', ENT_QUOTES);
?>
<script>
(function(){
    var COT=<?=$cot_id_js?>,EID=<?=$eid_js?>,VID='<?=$vid_js?>';
    var visibleStart=Date.now(),visibleAccum=0,maxScroll=0,closeSent=false;
    function updateMaxScroll(){var h=document.documentElement;var s=Math.round((h.scrollTop/(h.scrollHeight-h.clientHeight||1))*100);if(s>maxScroll)maxScroll=s;}
    document.addEventListener('visibilitychange',function(){if(document.visibilityState==='hidden'){if(visibleStart){visibleAccum+=Date.now()-visibleStart;visibleStart=0;}}else{visibleStart=Date.now();}});
    function sendEvent(tipo,beacon){
        if(visibleStart){visibleAccum+=Date.now()-visibleStart;visibleStart=Date.now();}
        var d={cotizacion_id:COT,empresa_id:EID,tipo:tipo,visible_ms:visibleAccum,scroll_max:maxScroll,visitor_id:VID};
        var url='/api/track';
        if(beacon&&navigator.sendBeacon){navigator.sendBeacon(url,JSON.stringify(d));}
        else{fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d),keepalive:true}).catch(function(){});}
    }

    function inView(el,thr){if(!el)return false;thr=typeof thr==='number'?thr:0.5;var r=el.getBoundingClientRect();var vh=window.innerHeight||document.documentElement.clientHeight;var h=Math.max(r.height,1);var px=Math.max(0,Math.min(r.bottom,vh)-Math.max(r.top,0));return(px/h)>=thr;}
    var totalsEl=document.getElementById('totalsScreen');
    var itemsEl=document.getElementById('itemsBlock');
    var totalsOnce=false,totalsCanRev=false,lastRevAt=0,COOLDOWN_MS=6000;
    function checkTotals(){if(!totalsEl)return;var iv=inView(totalsEl,0.45);if(iv&&!totalsOnce){totalsOnce=true;totalsCanRev=false;sendEvent('section_view_totals',false);return;}if(!iv&&totalsOnce){totalsCanRev=true;}if(iv&&totalsOnce&&totalsCanRev){var now=Date.now();if((now-lastRevAt)>=COOLDOWN_MS){lastRevAt=now;totalsCanRev=false;sendEvent('section_revisit_totals',false);}}}
    var loopState='idle',loopSent=false;
    function checkPriceLoop(){if(!totalsEl||!itemsEl)return;var tiv=inView(totalsEl,0.45);var iiv=inView(itemsEl,0.25);if(tiv&&loopState==='idle'){loopState='saw_totals';}else if(iiv&&loopState==='saw_totals'){loopState='saw_items_after_totals';}else if(tiv&&loopState==='saw_items_after_totals'){if(!loopSent){loopSent=true;sendEvent('quote_price_review_loop',false);}loopState='saw_totals';}}
    var sentMilestones={};
    function checkScrollMilestones(){[50,90].forEach(function(m){if(!sentMilestones[m]&&maxScroll>=m){sentMilestones[m]=true;sendEvent('quote_scroll',false);}});}

    window.addEventListener('scroll',function(){updateMaxScroll();checkScrollMilestones();checkTotals();checkPriceLoop();},{passive:true});
    window.addEventListener('resize',function(){checkTotals();checkPriceLoop();});
    document.addEventListener('visibilitychange',function(){if(document.visibilityState==='visible'){checkTotals();checkPriceLoop();}});
    function sendClose(){if(closeSent)return;closeSent=true;updateMaxScroll();if(visibleStart){visibleAccum+=Date.now()-visibleStart;visibleStart=0;}sendEvent('quote_close',true);}
    window.addEventListener('beforeunload',sendClose);
    window.addEventListener('pagehide',sendClose);
    window.czTrack=function(tipo){sendEvent(tipo,false);};
    updateMaxScroll();
    sendEvent('quote_open',false);
    checkTotals();
    checkPriceLoop();
})();
</script>

<?= MarketingPixels::evento_view(EMPRESA_ID, $cot['numero'], (float)$total_base, $cot['moneda'] ?? 'MXN') ?>
</body>
</html>
