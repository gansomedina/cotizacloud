<?php
// ============================================================
//  Mesa de Trabajo — v2 BETA (solo admin/superadmin)
//  Fila delgada + cajón (diseño aprobado en mockup):
//  - una línea por cotización: dot de calor, título, ciclo, monto,
//    3 columnitas con lo declarado, frescura
//  - tap en la fila abre el cajón: sugerencia + 3 áreas con pills
//  - la sugerencia se recalcula en el servidor con cada tap
//    (MesaSugerencias: mezcla + Radar + negocio + arquetipo)
//  - filas declaradas hoy → sección "Atendidas hoy" al recargar
// ============================================================
defined('COTIZAAPP') or die;
if (empty($es_admin_dash)) return;

$mesa_vendedores = DB::query(
    "SELECT DISTINCT u.id, u.nombre
     FROM usuarios u
     JOIN cotizaciones c ON COALESCE(c.vendedor_id, c.usuario_id) = u.id
     WHERE u.empresa_id = ? AND u.activo = 1
       AND c.empresa_id = ? AND c.estado IN ('enviada','vista') AND c.suspendida = 0
     ORDER BY u.nombre",
    [$empresa_id, $empresa_id]
);
if (!$mesa_vendedores) return;

$mesa_uid = (int)($_GET['mesa_uid'] ?? 0);
$mesa_ids = array_map(fn($v) => (int)$v['id'], $mesa_vendedores);
if (!in_array($mesa_uid, $mesa_ids, true)) $mesa_uid = $mesa_ids[0];

$mesa = Mesa::armar($empresa_id, $mesa_uid);
$mr   = $mesa['resumen'];
$mp75 = max(1, (int)$mesa['p75']);
$mmoney = fn(float $n) => '$' . number_format($n, 0);

$MESA_BUCKET_LBL = [
    'probable_cierre' => ['Probable cierre', '#dc2626'], 'onfire' => ['On fire', '#dc2626'],
    'inminente' => ['Inminente', '#dc2626'], 'validando_precio' => ['Validando precio', '#d97706'],
    'prediccion_alta' => ['Predicción alta', '#16a34a'], 'lectura_comprometida' => ['Lectura comprometida', '#7c3aed'],
    'multi_persona' => ['Multi-persona', '#dc2626'], 'alto_importe' => ['Alto importe', '#1d4ed8'],
    'hesitacion' => ['Hesitación', '#d97706'], 'enfriandose' => ['Enfriándose', '#64748b'],
    'sobre_analisis' => ['Sobre-análisis', '#92400e'], 'comparando' => ['Comparando', '#ea580c'],
    'regreso' => ['Regreso', '#7c3aed'], 'revivio' => ['Revivió', '#7c3aed'],
    're_enganche' => ['Re-enganche', '#7c3aed'], 're_enganche_caliente' => ['Re-enganche 🔥', '#dc2626'],
    'revision_profunda' => ['Revisión profunda', '#4f46e5'], 'vistas_multiples' => ['Vistas múltiples', '#16a34a'],
    'decision_activa' => ['Decisión activa', '#1d4ed8'], 'no_abierta' => ['Sin abrir', '#dc2626'],
];

// Áreas: opciones (label completo en el cajón) y label corto (columnita)
$MESA_AREAS = [
    'contacto'   => ['no_contesta' => 'No contestó', 'hablamos' => 'Hablamos'],
    'compromiso' => ['compromiso' => 'Quedamos en algo', 'propuse_no_quiso' => 'Propuse, no quiso', 'sin_compromiso' => 'Nada concreto'],
    'postura'    => ['decidiendo' => 'Decidiendo', 'objecion_precio' => 'Objeción precio', 'pidio_cambios' => 'Pidió cambios', 'en_el_aire' => 'En el aire', 'descartada' => 'Descartar'],
];
$MESA_SHORT = [
    'no_contesta' => 'No contestó', 'hablamos' => 'Hablamos',
    'compromiso' => 'Quedamos', 'propuse_no_quiso' => 'No quiso', 'sin_compromiso' => 'Nada',
    'decidiendo' => 'Decidiendo', 'objecion_precio' => 'Precio', 'pidio_cambios' => 'Cambios',
    'en_el_aire' => 'En el aire', 'descartada' => 'Descartada',
];

$mesa_pend = array_values(array_filter($mesa['rows'], fn($r) => empty($r['atendida_hoy'])));
$mesa_aten = array_values(array_filter($mesa['rows'], fn($r) => !empty($r['atendida_hoy'])));

$mesa_row = function (array $r) use ($MESA_BUCKET_LBL, $MESA_AREAS, $MESA_SHORT, $mmoney, $mp75) {
    $d  = $r['decl'] ?? [];
    $bl = $r['bucket'] ? ($MESA_BUCKET_LBL[$r['bucket']] ?? [$r['bucket'], '#64748b']) : null;
    $es_milagro = $r['revivida'] || $r['milagro'];
    $dot = $es_milagro ? '#d97706' : ($bl[1] ?? null);
    $udd = $r['ult_decl_dias'];
    ?>
    <div class="mrow<?= $es_milagro ? ' milagro' : '' ?><?= !empty($r['atendida_hoy']) ? ' done' : '' ?>" data-drawer="md<?= (int)$r['id'] ?>">
      <?php if ($dot): ?><span class="mdot" style="background:<?= $dot ?>" title="<?= e($r['revivida'] ? 'Revivió tras descarte' : ($bl[0] ?? '')) ?>"></span>
      <?php else: ?><span class="mdot off" title="Sin señal del Radar"></span><?php endif; ?>
      <span class="mcli">
        <a href="/cotizaciones/<?= (int)$r['id'] ?>" onclick="event.stopPropagation()"><?= e($r['titulo'] ?: $r['cliente']) ?></a>
        <span class="mfolio"><?= e($r['numero']) ?><?= $r['cliente'] && $r['titulo'] ? ' · ' . e($r['cliente']) : '' ?></span>
      </span>
      <?php if ($es_milagro): ?><span class="mflag">⚡</span><?php elseif ($r['dormida']): ?><span class="mflag" title="<?= (int)$r['dias_sin_vista'] ?>d sin volver a abrirla">😴</span><?php endif; ?>
      <span class="mcheck">✓</span>
      <span class="msp"></span>
      <span class="mciclo<?= ($r['fuera_ventana'] && !$es_milagro) ? ' late' : '' ?>">día <?= (int)$r['edad'] ?> de <?= $mp75 ?></span>
      <span class="mmoney"><?= $mmoney($r['total']) ?></span>
      <span class="mdecl3">
        <?php foreach (['contacto' => 's1', 'compromiso' => 's2', 'postura' => 's3'] as $a => $cls):
            $cur = $d[$a]['estado'] ?? ''; ?>
        <span class="<?= $cls ?><?= $cur ? ' f' : '' ?>"><?= $cur ? e($MESA_SHORT[$cur] ?? $cur) : '—' ?></span>
        <?php endforeach; ?>
      </span>
      <span class="mfresh<?= $udd === null ? ' warn' : ($udd >= 3 ? ' bad' : ($udd === 0 ? ' ok' : '')) ?>">
        <?= $udd === null ? 'sin actualizar' : ($udd === 0 ? 'hoy' : "hace {$udd}d") ?></span>
      <span class="mchev">▶</span>
    </div>
    <div class="mdrawer" id="md<?= (int)$r['id'] ?>">
      <div class="msug">
        <?php if ($r['revivida']): ?><span class="mtag" style="background:#fef3c7;color:#92400e">⚡ revivió tras descarte</span>
        <?php elseif ($bl && $r['es_hot']): ?><span class="mtag" style="background:<?= $bl[1] ?>18;color:<?= $bl[1] ?>"><?= e($bl[0]) ?></span><?php endif; ?>
        <span class="mlbl">→</span><span class="msx"><?= e($r['sugerencia']) ?></span>
      </div>
      <div class="mareas">
        <div class="marea"><span class="man">Contacto</span>
          <?php foreach ($MESA_AREAS['contacto'] as $ek => $el): ?>
          <button type="button" class="mpill<?= ($d['contacto']['estado'] ?? '') === $ek ? ' on' : '' ?>" onclick="mesaTap(<?= (int)$r['id'] ?>,'contacto','<?= $ek ?>',this)"><?= e($el) ?></button>
          <?php endforeach; ?></div>
        <div class="marea"><span class="man">Compromiso</span>
          <?php foreach ($MESA_AREAS['compromiso'] as $ek => $el): ?>
          <button type="button" class="mpill<?= ($d['compromiso']['estado'] ?? '') === $ek ? ' on' : '' ?>" onclick="mesaTap(<?= (int)$r['id'] ?>,'compromiso','<?= $ek ?>',this)"><?= e($el) ?></button>
          <?php endforeach; ?></div>
        <div class="marea"><span class="man">¿Cómo lo ves?</span>
          <?php foreach ($MESA_AREAS['postura'] as $ek => $el): ?>
          <button type="button" class="mpill<?= ($d['postura']['estado'] ?? '') === $ek ? ' on' : '' ?>" onclick="mesaTap(<?= (int)$r['id'] ?>,'postura','<?= $ek ?>',this)"><?= e($el) ?></button>
          <?php endforeach; ?></div>
      </div>
    </div>
    <?php
};
?>
<details class="card" id="mesa-card" style="margin-bottom:16px" <?= isset($_GET['mesa_uid']) ? 'open' : '' ?>>
  <summary style="cursor:pointer;padding:14px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;list-style:none">
    <span style="font-weight:800">📋 Mesa de trabajo</span>
    <span style="font-size:11px;background:#ede9fe;color:#6d28d9;padding:2px 8px;border-radius:10px;font-weight:700">BETA · solo admin</span>
    <span style="color:#4a4a46;font-size:13.5px">
      <?php if ($mr['n'] > 0 || !empty($mr['atendidas'])): ?>
        <b><?= (int)$mr['n'] ?></b> pendientes · <b><?= $mmoney($mr['monto']) ?></b> en juego
        <?php if ($mr['sin_postura'] > 0): ?>
          · <span style="color:#dc2626;font-weight:700"><?= (int)$mr['sin_postura'] ?> sin calificar</span>
        <?php endif; ?>
        <?php if (!empty($mr['atendidas'])): ?>
          · <span style="color:#16a34a;font-weight:700">✓ <?= (int)$mr['atendidas'] ?> atendida<?= $mr['atendidas'] > 1 ? 's' : '' ?> hoy</span>
        <?php endif; ?>
      <?php else: ?>
        <span style="color:#16a34a;font-weight:700">✓ al corriente</span>
      <?php endif; ?>
    </span>
    <span style="margin-left:auto;color:#6a6a64;font-size:12px">tap para expandir ▾</span>
  </summary>
  <div style="padding:0 16px 16px">

    <?php if (count($mesa_vendedores) > 1): ?>
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px">
      <?php foreach ($mesa_vendedores as $mv): $act = (int)$mv['id'] === $mesa_uid; ?>
        <a href="?mesa_uid=<?= (int)$mv['id'] ?>#mesa-card"
           style="padding:5px 12px;border-radius:16px;font-size:12.5px;font-weight:600;text-decoration:none;
                  <?= $act ? 'background:#1a5c38;color:#fff' : 'background:#f4f4f0;color:#4a4a46;border:1px solid #e2e2dc' ?>">
          <?= e($mv['nombre']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php $mc = $mesa['ciclo']; ?>
    <div style="font-size:12px;color:#6a6a64;margin-bottom:10px">
      <?php if (!empty($mc['auto'])): ?>
      Ciclo real de la empresa: la mitad de tus ventas cierra en <b><?= (int)$mc['mediana'] ?>d</b>,
      el 75% antes del día <b><?= (int)$mc['p75'] ?></b>.
      <?php endif; ?>
      Aquí capturas el <b>status actual</b> de cada cotización — tapea una fila para trabajarla
      y actualízala en cada toque.
    </div>

    <?php if (!$mesa['rows']): ?>
      <div style="color:#16a34a;padding:12px 0;font-weight:600">✓ Sin pendientes: todo lo activo está trabajado y dentro de ventana.</div>
    <?php else: ?>

      <?php if ($mesa_pend): ?>
      <div class="mhead">
        <span class="mh-cot">Cotización</span><span class="msp"></span>
        <span class="mh-ciclo">Ciclo</span><span class="mh-money">Monto</span>
        <span class="mh-decl"><span class="s1">Contacto</span><span class="s2">Compromiso</span><span class="s3">Cómo lo ves</span></span>
        <span class="mh-fresh">Captura</span><span class="mh-chev"></span>
      </div>
      <div class="mlist"><?php foreach ($mesa_pend as $r) $mesa_row($r); ?></div>
      <?php else: ?>
      <div style="color:#16a34a;padding:12px 0;font-weight:600">✓ Pendientes en cero — todo lo de hoy ya está atendido.</div>
      <?php endif; ?>

      <?php if ($mesa_aten): ?>
      <div class="msect">✓ Atendidas hoy (<?= count($mesa_aten) ?>)</div>
      <div class="mlist mdone-zone"><?php foreach ($mesa_aten as $r) $mesa_row($r); ?></div>
      <?php endif; ?>

    <?php endif; ?>

    <?php if ($mesa['limpieza']['n'] >= 10): ?>
    <div style="margin-top:12px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:12.5px;color:#7f1d1d">
      🗑 <b><?= (int)$mesa['limpieza']['n'] ?></b> cotizaciones (<?= $mmoney($mesa['limpieza']['monto']) ?>) tienen más de
      <b><?= (int)$mesa['limpieza']['linea_dias'] ?> días</b> — tu empresa jamás ha cerrado una de esa edad.
      Ya no son pipeline, son ruido. <span style="color:#9a3412">(Suspensión en lote: próxima versión.)</span>
    </div>
    <?php endif; ?>

  </div>
</details>

<style>
#mesa-card .mlist{border:1px solid #eeeee9;border-radius:10px;overflow:hidden}
#mesa-card .mrow{display:flex;align-items:center;gap:10px;padding:0 12px;height:38px;cursor:pointer;background:#fafaf8}
#mesa-card .mrow + .mdrawer + .mrow, #mesa-card .mrow + .mrow{border-top:1px solid #eeeee9}
#mesa-card .mdrawer + .mrow{border-top:1px solid #eeeee9}
#mesa-card .mrow:hover{background:#f4f4ef}
#mesa-card .mrow.open{background:#fff}
#mesa-card .mrow.milagro{background:#fefce8}
#mesa-card .mdone-zone .mrow{opacity:.72}
#mesa-card .mdot{width:9px;height:9px;border-radius:50%;flex:none}
#mesa-card .mdot.off{background:transparent;border:1.5px solid #c9c9c2}
#mesa-card .mcli{font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0;flex:0 1 auto}
#mesa-card .mcli a{color:#1a1a18;text-decoration:none}
#mesa-card .mcli a:hover{color:#1a5c38;text-decoration:underline}
#mesa-card .mfolio{font-weight:500;color:#a3a39d;font-size:11px;margin-left:6px}
#mesa-card .mflag{font-size:11px;flex:none}
#mesa-card .mcheck{color:#16a34a;font-weight:800;display:none;flex:none}
#mesa-card .mrow.done .mcheck{display:inline}
#mesa-card .msp{flex:1}
#mesa-card .mciclo{font-size:12px;color:#57534e;font-variant-numeric:tabular-nums;flex:none;width:86px;text-align:right;white-space:nowrap}
#mesa-card .mciclo.late{color:#dc2626;font-weight:700}
#mesa-card .mmoney{font-weight:700;font-variant-numeric:tabular-nums;flex:none;width:82px;text-align:right}
#mesa-card .mdecl3{display:flex;gap:6px;flex:none}
#mesa-card .mdecl3 span{font-size:10.5px;line-height:1.3;color:#c9c9c2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#mesa-card .mdecl3 .s1{width:80px}#mesa-card .mdecl3 .s2{width:88px}#mesa-card .mdecl3 .s3{width:86px}
#mesa-card .mdecl3 span.f{color:#1a5c38;font-weight:700}
#mesa-card .mfresh{font-size:10.5px;flex:none;width:82px;text-align:right;color:#a8a8a2;white-space:nowrap}
#mesa-card .mfresh.warn{color:#d97706;font-weight:700}
#mesa-card .mfresh.bad{color:#dc2626;font-weight:700}
#mesa-card .mfresh.ok{color:#16a34a;font-weight:700}
#mesa-card .mchev{color:#c9c9c2;flex:none;font-size:11px;transition:transform .15s}
#mesa-card .mrow.open .mchev{transform:rotate(90deg)}
#mesa-card .mdrawer{display:none;background:#fff;padding:12px 14px 14px 31px;border-top:1px solid #f4f4ef}
#mesa-card .mdrawer.open{display:block}
#mesa-card .mtag{font-size:11px;padding:2px 7px;border-radius:9px;font-weight:700;margin-right:6px}
#mesa-card .msug{font-size:13px;color:#3f3f3a;margin-bottom:10px}
#mesa-card .mlbl{color:#1a5c38;font-weight:800;margin-right:4px}
#mesa-card .mareas{display:flex;flex-direction:column;gap:7px}
#mesa-card .marea{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
#mesa-card .man{font-size:10.5px;font-weight:800;color:#8a8a84;text-transform:uppercase;letter-spacing:.05em;width:112px;flex:none}
#mesa-card .mpill{border:1px solid #e2e2dc;background:#fafaf8;color:#57534e;border-radius:999px;padding:4px 12px;cursor:pointer;font:600 11.5px 'Plus Jakarta Sans',system-ui,sans-serif;white-space:nowrap;line-height:1.4;transition:all .12s}
#mesa-card .mpill:hover{border-color:#1a5c38;color:#1a5c38;background:#fff}
#mesa-card .mpill.on{background:#1a5c38;border-color:#1a5c38;color:#fff}
#mesa-card .mpill:disabled{opacity:.5}
#mesa-card .mhead{display:flex;align-items:center;gap:10px;padding:0 12px 5px;font-size:10px;font-weight:800;color:#a8a8a2;text-transform:uppercase;letter-spacing:.05em}
#mesa-card .mhead .mh-cot{width:auto}
#mesa-card .mhead .mh-ciclo{flex:none;width:86px;text-align:right}
#mesa-card .mhead .mh-money{flex:none;width:82px;text-align:right}
#mesa-card .mhead .mh-decl{display:flex;gap:6px;flex:none}
#mesa-card .mhead .mh-decl .s1{width:80px}#mesa-card .mhead .mh-decl .s2{width:88px}#mesa-card .mhead .mh-decl .s3{width:86px}
#mesa-card .mhead .mh-decl span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#mesa-card .mhead .mh-fresh{flex:none;width:82px;text-align:right}
#mesa-card .mhead .mh-chev{flex:none;width:11px}
#mesa-card .msect{margin-top:14px;margin-bottom:6px;font-size:11px;color:#16a34a;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
#mesa-toast{position:fixed;left:50%;bottom:22px;transform:translateX(-50%);background:#1a1a18;color:#fff;font-size:12.5px;padding:9px 16px;border-radius:10px;opacity:0;pointer-events:none;transition:opacity .25s;z-index:9999}
#mesa-toast.show{opacity:.95}
@media (max-width:640px){
  #mesa-card .mfolio,#mesa-card .mfresh{display:none}
  #mesa-card .mhead{display:none}
  #mesa-card .mdecl3 .s2,#mesa-card .mdecl3 .s3{display:none}
  #mesa-card .mdecl3 .s1{width:64px}
  #mesa-card .mmoney{width:auto}
  #mesa-card .mdrawer{padding-left:14px}
  #mesa-card .man{width:100%}
}
</style>
<div id="mesa-toast"></div>
<script>
document.querySelectorAll('#mesa-card .mrow').forEach(function(row){
  row.addEventListener('click', function(){
    var d = document.getElementById(row.dataset.drawer);
    if(!d) return;
    var was = d.classList.contains('open');
    document.querySelectorAll('#mesa-card .mdrawer').forEach(function(x){x.classList.remove('open')});
    document.querySelectorAll('#mesa-card .mrow').forEach(function(x){x.classList.remove('open')});
    if(!was){ d.classList.add('open'); row.classList.add('open'); }
  });
});

var mesaToastT;
function mesaToast(msg){
  var t = document.getElementById('mesa-toast');
  t.textContent = msg; t.classList.add('show');
  clearTimeout(mesaToastT); mesaToastT = setTimeout(function(){t.classList.remove('show')}, 2600);
}

var MESA_SHORT = <?= json_encode($MESA_SHORT, JSON_UNESCAPED_UNICODE) ?>;
var MESA_IDX   = {contacto:0, compromiso:1, postura:2};

function mesaTap(cotId, area, estado, btn){
  var razon = null;
  if(estado === 'descartada'){
    var r = prompt('Razón: 1=Muy caro  2=Se fue con otro  3=Lo dejó para después  4=Dejó de responder  5=No era comprador  6=Otro','1');
    var map = {'1':'precio','2':'competencia','3':'despues','4':'no_responde','5':'no_comprador','6':'otro'};
    razon = map[(r||'').trim()]; if(!razon) return;
  }
  btn.disabled = true;
  fetch('/api/mesa/estado', {method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
    body: JSON.stringify({cotizacion_id:cotId, area:area, estado:estado, razon:razon})
  }).then(function(r){return r.json();}).then(function(d){
    btn.disabled = false;
    if(!d.ok){ mesaToast('No se pudo guardar: ' + (d.error || 'error')); return; }
    var drawer = btn.closest('.mdrawer');
    var row = document.querySelector('#mesa-card .mrow[data-drawer="'+drawer.id+'"]');
    // pill exclusivo dentro del área
    btn.closest('.marea').querySelectorAll('.mpill').forEach(function(x){x.classList.remove('on')});
    btn.classList.add('on');
    // columnita con el label corto
    var slot = row.querySelectorAll('.mdecl3 span')[MESA_IDX[area]];
    if(slot){ slot.textContent = MESA_SHORT[estado] || estado; slot.classList.add('f'); }
    // frescura
    var fr = row.querySelector('.mfresh');
    if(fr){ fr.textContent = 'hoy'; fr.className = 'mfresh ok'; }
    // sugerencia recalculada por el servidor (mezcla + Radar + arquetipo)
    if(d.sugerencia){
      var sx = drawer.querySelector('.msx');
      if(sx) sx.textContent = d.sugerencia;
    }
    if(!row.classList.contains('done')){
      row.classList.add('done');
      mesaToast('✓ Atendida — al recargar pasa a "Atendidas hoy"');
    }
  }).catch(function(){ btn.disabled = false; mesaToast('No se pudo guardar (red o sesión) — recarga e intenta de nuevo.'); });
}
</script>
