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
<div class="slabel">Rendimiento del equipo · esta semana
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
          style="margin-left:auto;flex:none;font:700 11px var(--body);color:var(--white);background:#1a5c38;border:0;border-radius:7px;padding:5px 11px;cursor:pointer">Generar reporte</button><?php endif; ?>
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
      <strong id="rt-modal-title" style="flex:1;font:800 15px var(--body);color:var(--text)">Reporte</strong>
      <button type="button" onclick="rtPrint()" style="font:700 11px var(--body);color:var(--t2);background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:5px 10px;cursor:pointer">Imprimir</button>
      <button type="button" onclick="rtClose()" style="font:700 16px var(--body);color:var(--t3);background:none;border:0;cursor:pointer;line-height:1">✕</button>
    </div>
    <div style="display:flex;gap:8px;align-items:flex-end;padding:11px 16px;background:var(--bg);border-bottom:1px solid var(--border);flex-wrap:wrap">
      <label style="font:600 10px var(--body);color:var(--t3)">Desde<br><input type="date" id="rt-desde" style="font:500 12px var(--body);padding:5px 7px;border:1px solid var(--border);border-radius:6px;background:var(--white);color:var(--text)"></label>
      <label style="font:600 10px var(--body);color:var(--t3)">Hasta<br><input type="date" id="rt-hasta" style="font:500 12px var(--body);padding:5px 7px;border:1px solid var(--border);border-radius:6px;background:var(--white);color:var(--text)"></label>
      <button type="button" id="rt-gen" onclick="rtGen(false)" style="font:700 12px var(--body);color:#fff;background:#1a5c38;border:0;border-radius:7px;padding:7px 13px;cursor:pointer">Generar</button>
    </div>
    <div id="rt-body" style="padding:16px;min-height:120px"></div>
  </div>
</div>

<style>
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
  #rt-modal .rr-foot{font:400 10.5px var(--body);color:var(--t3);border-top:1px solid var(--border);padding-top:8px;margin-top:6px}
  @media print{body *{visibility:hidden}#rt-modal,#rt-modal *{visibility:visible}#rt-modal{position:absolute;inset:0;background:#fff;padding:0}#rt-modal>div{box-shadow:none;margin:0;max-width:100%}}
</style>

<script>
(function(){
  const CSRF = <?= json_encode(csrf_token()) ?>;
  let uid = 0;
  const $ = id => document.getElementById(id);
  const ymd = d => d.toISOString().slice(0,10);

  document.querySelectorAll('.rt-rep-btn').forEach(b => b.addEventListener('click', () => {
    uid = parseInt(b.dataset.uid, 10);
    $('rt-modal-title').textContent = 'Reporte — ' + b.dataset.name;
    const hoy = new Date(), d15 = new Date(Date.now() - 14*864e5);
    $('rt-hasta').value = ymd(hoy);
    $('rt-desde').value = ymd(d15);
    $('rt-body').innerHTML = '';
    $('rt-modal').style.display = 'block';
    rtGen(true);
  }));

  window.rtClose = () => { $('rt-modal').style.display = 'none'; };
  window.rtPrint = () => window.print();
  $('rt-modal').addEventListener('click', e => { if (e.target === $('rt-modal')) rtClose(); });

  window.rtGen = async function(){
    if (!uid) return;
    const btn = $('rt-gen'); btn.disabled = true; btn.textContent = 'Generando…';
    $('rt-body').innerHTML = '<div style="padding:24px;text-align:center;color:var(--t3);font:500 13px var(--body)">Cruzando la data del asesor…</div>';
    try {
      const r = await fetch('/api/reporte-asesor', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF,'Accept':'application/json'},
        body: JSON.stringify({ asesor_id: uid, desde: $('rt-desde').value, hasta: $('rt-hasta').value })
      });
      const d = await r.json();
      if (!d.ok) { $('rt-body').innerHTML = '<div style="padding:20px;color:#b91c1c;font:600 13px var(--body)">No se pudo generar ('+(d.error||'error')+').</div>'; }
      else {
        let banner = '';
        if (d.data.cache) banner = '<div style="margin-bottom:10px;padding:7px 10px;background:var(--bg);border-radius:7px;font:500 11px var(--body);color:var(--t3)">'+(d.msg||'Reporte de esta semana.')+'</div>';
        $('rt-body').innerHTML = banner + d.data.html;
      }
    } catch(e){ $('rt-body').innerHTML = '<div style="padding:20px;color:#b91c1c">Error de red.</div>'; }
    btn.disabled = false; btn.textContent = 'Generar';
  };
})();
</script>
<?php endif; ?>
