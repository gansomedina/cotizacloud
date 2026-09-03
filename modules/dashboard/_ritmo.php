<?php
// ============================================================
//  Dashboard partial — Rendimiento del equipo (desde la Mesa)
//  SOLO ADMIN. Solo lectura (RitmoAsesor). 5 PILARES etiquetados,
//  cada uno con su mini-semáforo y TODOS sus parámetros:
//    Conversión · Descartadas · Citas · Seguimiento · Contacto.
//  NOTA: comparte scope con index.php — variables con prefijo rt_.
// ============================================================
defined('COTIZAAPP') or die;

if (!Auth::es_admin()) return;

$rt_on = false;
try { $rt_on = (int) DB::val("SELECT mesa_activa FROM empresas WHERE id = ?", [EMPRESA_ID]) >= 1; }
catch (Throwable $rt_e) { $rt_on = false; }
if (!$rt_on) return;

$rt_filas = RitmoAsesor::empresa(EMPRESA_ID);
// La ventana REAL de los pilares es 2×p75 del ciclo de la empresa (típico 20d),
// no "esta semana" como decía el título. Solo el pilar Citas usa 7 días y ya lo
// dice en su texto.
$rt_win = 20;
try {
    if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
    $rt_c = Radar::ciclo_venta(EMPRESA_ID);
    if (!empty($rt_c['auto']) && !empty($rt_c['p75'])) $rt_win = 2 * max(3, (int)$rt_c['p75']);
} catch (Throwable $rt_e2) {}
if (!$rt_filas) return;

// El botón "Generar reporte" es solo Business (el endpoint también lo exige).
$rt_business = !empty(trial_info(EMPRESA_ID)['es_business']);

$rt_colmap   = ['rojo' => '#dc2626', 'amarillo' => '#d97706', 'verde' => '#16a34a'];
$rt_n_alerta = count(array_filter($rt_filas, fn($x) => $x['semaforo'] !== 'verde'));

// Un pilar: mini-semáforo (dot del color de SU estado) + etiqueta + texto.
$rt_pill = function (string $estado, string $label, string $txt) use ($rt_colmap): string {
    $dc = $rt_colmap[$estado] ?? '#9ca3af';
    $ok = str_starts_with($txt, '✓');
    $tc = $ok ? 'var(--t3)' : $dc;
    return '<div style="display:flex;align-items:baseline;gap:7px;margin-top:3px;font:500 12px var(--body)">'
         . '<span style="width:8px;height:8px;border-radius:50%;background:' . $dc . ';flex:none;align-self:center"></span>'
         . '<span style="color:var(--t2);font-weight:700;min-width:88px">' . $label . '</span>'
         . '<span style="color:' . $tc . '">' . e($txt) . '</span></div>';
};
?>
<div class="slabel">Rendimiento del equipo · <?= (int)$rt_win ?> días
  <?php if ($rt_n_alerta > 0): ?><span style="font:700 11px var(--body);color:#dc2626"><?= $rt_n_alerta ?> por atender</span><?php endif; ?>
</div>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:16px">
  <?php foreach ($rt_filas as $rt_f):
      $rt_c = $rt_colmap[$rt_f['semaforo']] ?? '#9ca3af';
  ?>
  <div style="display:flex;align-items:flex-start;gap:12px;padding:13px 16px;border-bottom:1px solid var(--border)">
    <span style="width:12px;height:12px;border-radius:50%;background:<?= $rt_c ?>;flex-shrink:0;margin-top:4px"></span>
    <div style="flex:1;min-width:0">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px">
        <span style="font:700 14px var(--body);color:var(--text)"><?= e($rt_f['nombre']) ?></span>
        <?php if (!empty($rt_f['flag'])): ?><span style="font:700 10px var(--body);color:#b91c1c;background:#fdeaea;padding:2px 7px;border-radius:999px">no sigue el proceso<?= !empty($rt_f['flag_pilares']) ? ': ' . e($rt_f['flag_pilares']) : '' ?></span><?php endif; ?>
        <?php if ($rt_business): ?><button type="button" class="rt-rep-btn" data-uid="<?= (int)$rt_f['usuario_id'] ?>" data-name="<?= e($rt_f['nombre']) ?>"
          style="margin-left:auto;flex:none;font:800 11px var(--body);color:#fff;background:linear-gradient(135deg,#1a5c38,#2ea043);border:0;border-radius:999px;padding:6px 13px;cursor:pointer;letter-spacing:.02em;box-shadow:0 2px 7px rgba(26,92,56,.32)">✨ Generar reporte</button><?php endif; ?>
      </div>
      <?= $rt_pill($rt_f['conv_estado'],  'Conversión',  $rt_f['conv_txt']) ?>
      <?= $rt_pill($rt_f['desc_estado'],  'Descartadas', $rt_f['desc_txt']) ?>
      <?= $rt_pill($rt_f['citas_estado'], 'Citas',       $rt_f['citas_txt']) ?>
      <?= $rt_pill($rt_f['venc_estado'],  'Seguimiento', $rt_f['venc_txt']) ?>
      <?= $rt_pill($rt_f['cont_estado'],  'Contacto',    $rt_f['cont_txt']) ?>
      <div style="font:600 12.5px var(--body);color:<?= $rt_f['semaforo'] === 'verde' ? 'var(--t3)' : $rt_c ?>;margin-top:6px">→ <?= e($rt_f['motivo']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>5 pilares</strong> (cada uno con su semáforo): <b>Conversión</b> (cerró vs cotizaciones) · <b>Descartadas</b> (vs cierres, sin cita, muy rápido) · <b>Citas</b> (su ritmo de agendar) · <b>Seguimiento</b> (el cronómetro de vencidas) · <b>Contacto</b> (a cuántos no le contestan). Medido contra las reglas de la Mesa — hechos, no acusaciones.
  </div>
</div>

<?php if ($rt_business): ?>
<!-- ── Modal del reporte del Director (una sola vez) ── -->
<div id="rt-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,20,25,.55);padding:20px;overflow:auto">
  <div style="max-width:640px;margin:24px auto;background:var(--white);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.35);overflow:hidden">
    <div style="display:flex;align-items:center;gap:10px;padding:13px 16px;border-bottom:1px solid var(--border)">
      <strong id="rt-modal-title" style="font:800 15px var(--body);color:var(--text)">Reporte</strong>
      <span class="rt-ai-badge">✨ CotizaCloud AI</span>
      <span style="flex:1"></span>
      <button type="button" onclick="rtPrint()" style="font:700 11px var(--body);color:var(--t2);background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:5px 10px;cursor:pointer">Imprimir</button>
      <button type="button" onclick="rtClose()" style="font:700 16px var(--body);color:var(--t3);background:none;border:0;cursor:pointer;line-height:1">✕</button>
    </div>
    <div id="rt-body" style="padding:16px;min-height:120px"></div>
  </div>
</div>

<style id="rt-css">
  #rt-modal .rr{font:400 13px var(--body);color:var(--text);line-height:1.5}
  #rt-modal .rr-hd{display:flex;align-items:center;gap:12px;margin-bottom:12px}
  #rt-modal .rr-k{font:700 10px var(--body);letter-spacing:.05em;text-transform:uppercase;color:#1a5c38}
  #rt-modal .rr-name{font:800 18px var(--body);color:var(--text);letter-spacing:-.01em}
  #rt-modal .rr-score{margin-left:auto;text-align:center;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:5px 12px}
  #rt-modal .rr-sn{font:800 22px var(--body);color:var(--text);line-height:1}
  #rt-modal .rr-sl{font:700 9px var(--body);text-transform:uppercase;color:var(--t3);letter-spacing:.05em}
  #rt-modal .rr-dims{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px}
  #rt-modal .rr-dim{font:600 11px var(--body);color:var(--t2);background:var(--bg);border:1px solid var(--border);border-radius:999px;padding:3px 9px}
  #rt-modal .rr-dim b{color:var(--text)}
  #rt-modal .rr-note{font:400 10.5px var(--body);color:var(--t3);margin-bottom:14px;line-height:1.4}
  #rt-modal .rr-sec{margin-bottom:14px}
  #rt-modal .rr-st{font:800 11px var(--body);text-transform:uppercase;letter-spacing:.05em;color:var(--t2);margin-bottom:6px;border-bottom:1px solid var(--border);padding-bottom:4px}
  #rt-modal .rr-list{margin:0;padding-left:18px} #rt-modal .rr-list li{margin:2px 0}
  #rt-modal .rr-foco{margin-bottom:8px;padding:8px 11px;background:#fbe9e9;border-radius:8px}
  :root[data-theme="dark"] #rt-modal .rr-foco{background:#301718}
  #rt-modal .rr-ft{font:750 12.5px var(--body);color:#b91c1c}
  :root[data-theme="dark"] #rt-modal .rr-ft{color:#f0736f}
  #rt-modal .rr-casos{margin:5px 0 0;padding-left:16px;font-variant-numeric:tabular-nums}
  #rt-modal .rr-casos li{margin:1px 0;color:var(--t2);font-size:12px}
  .rt-ai-badge{background:linear-gradient(135deg,#1a5c38,#2ea043);color:#fff;font:800 10px var(--body);padding:4px 10px;border-radius:999px;letter-spacing:.04em;white-space:nowrap}
  #rt-modal .rr-sub{list-style:none;color:var(--t3);font-size:11.5px;margin-left:-6px}
  #rt-modal .rr-consejo{background:linear-gradient(135deg,rgba(26,92,56,.09),rgba(46,160,67,.05));border:1px solid rgba(26,92,56,.25);border-radius:10px;padding:11px 13px;margin-bottom:14px}
  #rt-modal .rr-consejo .rr-st{color:#1a5c38;border-bottom:0;padding-bottom:0;margin-bottom:5px}
  #rt-modal .rr-consejo .rr-list{padding-left:0;list-style:none}
  #rt-modal .rr-consejo .rr-list li{font:600 13px var(--body);color:var(--text);margin:0}
  :root[data-theme="dark"] #rt-modal .rr-consejo{background:rgba(46,160,67,.12);border-color:rgba(67,200,119,.32)}
  :root[data-theme="dark"] #rt-modal .rr-consejo .rr-st{color:#43c877}
  #rt-modal .rr-pil{display:flex;align-items:baseline;gap:8px;font:500 13px var(--body);margin:4px 0}
  #rt-modal .rr-pil b{color:var(--t2);min-width:92px;font-weight:750}
  #rt-modal .rr-dot{width:9px;height:9px;border-radius:50%;flex:none;align-self:center}
  #rt-modal .rr-tip{background:linear-gradient(135deg,rgba(217,119,6,.10),rgba(245,158,11,.05));border:1px solid rgba(217,119,6,.28);border-radius:10px;padding:11px 13px;margin-bottom:14px}
  #rt-modal .rr-tip-h{font:800 11px var(--body);text-transform:uppercase;letter-spacing:.05em;color:#b45309;margin-bottom:4px}
  #rt-modal .rr-tip-b{font:500 13px var(--body);color:var(--text);line-height:1.5}
  :root[data-theme="dark"] #rt-modal .rr-tip{background:rgba(217,119,6,.14);border-color:rgba(245,158,11,.3)}
  :root[data-theme="dark"] #rt-modal .rr-tip-h{color:#dca247}
  #rt-modal .rr-foot{font:400 10.5px var(--body);color:var(--t3);border-top:1px solid var(--border);padding-top:8px;margin-top:6px}
  @media print{body *{visibility:hidden}#rt-modal,#rt-modal *{visibility:visible;-webkit-print-color-adjust:exact;print-color-adjust:exact}#rt-modal{position:static!important;inset:auto;overflow:visible!important;height:auto;background:#fff;padding:0}#rt-modal>div{max-width:100%!important;max-height:none!important;overflow:visible!important}#rt-modal>div{box-shadow:none;margin:0;max-width:100%}}
</style>

<script>
(function(){
  const CSRF = <?= json_encode(csrf_token()) ?>;
  let uid = 0;
  const $ = id => document.getElementById(id);

  document.querySelectorAll('.rt-rep-btn').forEach(b => b.addEventListener('click', () => {
    uid = parseInt(b.dataset.uid, 10);
    $('rt-modal-title').textContent = 'Reporte — ' + b.dataset.name;
    $('rt-body').innerHTML = '';
    $('rt-modal').style.display = 'block';
    rtGen();
  }));

  window.rtClose = () => { $('rt-modal').style.display = 'none'; };
  // Imprimir desde una VENTANA LIMPIA con solo el reporte.
  //
  // Por qué no window.print() sobre el dashboard: la regla @media print usaba
  // 'body *{visibility:hidden}', que oculta pero NO quita el espacio — el
  // dashboard entero (tarjetas, leaderboard, mesas) sigue paginando en blanco y
  // el reporte cae varias páginas abajo, recortado por los contenedores que lo
  // envuelven. De ahí el "solo imprime un pedazo". Pelear ese CSS desde afuera
  // es interminable; una ventana propia es determinista.
  //
  // Se copia el <style> del reporte tal cual y se replican SOLO las variables de
  // color que usa (viven en el layout, que aquí no existe). El HTML se envuelve
  // en #rt-modal para que los mismos selectores apliquen sin tocar el render.
  var RT_CSS_PRINT = <?= json_encode(class_exists('RitmoReporte') ? RitmoReporte::css_impresion() : '', JSON_HEX_TAG | JSON_HEX_APOS) ?>;

  window.rtPrint = function () {
    var cont = $('rt-body');
    if (!cont || !cont.innerHTML.trim()) return;
    var css = (document.getElementById('rt-css') || {}).textContent || '';
    var w = window.open('', '_blank');
    if (!w) { window.print(); return; }   // popup bloqueado → comportamiento viejo
    var titulo = ($('rt-modal-title') || {}).textContent || 'Reporte';
    w.document.open();
    w.document.write(
      '<!doctype html><html lang="es"><head><meta charset="utf-8">' +
      '<title>' + titulo.replace(/[<>&]/g, '') + '</title><style>' +
      ':root{--bg:#f4f4f0;--white:#fff;--border:#e2e2dc;--text:#1a1a18;' +
      '--t2:#4a4a46;--t3:#6a6a64;--g:#1a5c38;--danger:#c53030;' +
      "--body:'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',sans-serif}" +
      'html,body{margin:0;padding:0;background:#fff}' +
      '#rt-modal{padding:0}#rt-modal>div{max-width:100%;margin:0;box-shadow:none}' +
      '#rt-body{padding:0}' +
      '*{-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
      css +
      // Compactado a una hoja: dos columnas + tipografía apretada.
      // Vive en RitmoReporte::css_impresion() para que el dashboard y el
      // panel del supervisor no se separen — los dos arman esta ventana.
      RT_CSS_PRINT +
      '</style></head><body><div id="rt-modal"><div><div id="rt-body">' +
      cont.innerHTML +
      '</div></div></div></body></html>'
    );
    w.document.close();
    // esperar al layout antes de abrir el diálogo (si no, imprime en blanco)
    var go = function () { try { w.focus(); w.print(); } catch (e) {} };
    if (w.document.readyState === 'complete') setTimeout(go, 120);
    else w.onload = function () { setTimeout(go, 120); };
  };
  $('rt-modal').addEventListener('click', e => { if (e.target === $('rt-modal')) rtClose(); });

  window.rtGen = async function(){
    if (!uid) return;
    $('rt-body').innerHTML = '<div style="padding:24px;text-align:center;color:var(--t3);font:500 13px var(--body)">Cruzando la data del asesor…</div>';
    try {
      const r = await fetch('/api/reporte-asesor', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF,'Accept':'application/json'},
        body: JSON.stringify({ asesor_id: uid })
      });
      const d = await r.json();
      if (!d.ok) { $('rt-body').innerHTML = '<div style="padding:20px;color:#b91c1c;font:600 13px var(--body)">No se pudo generar ('+(d.error||'error')+').</div>'; }
      else {
        let banner = '';
        if (d.data.cache) {
          var f = d.data.fecha ? (' · generado el ' + String(d.data.fecha).slice(0,16).replace('T',' ')) : '';
          banner = '<div style="margin-bottom:10px;padding:7px 10px;background:var(--bg);border-radius:7px;font:500 11px var(--body);color:var(--t3)">'+(d.msg||'Reporte de esta semana.')+f+'</div>';
        }
        $('rt-body').innerHTML = banner + d.data.html;
      }
    } catch(e){ $('rt-body').innerHTML = '<div style="padding:20px;color:#b91c1c">Error de red.</div>'; }
  };
})();
</script>
<?php endif; ?>
