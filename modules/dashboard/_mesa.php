<?php
// ============================================================
//  Mesa de Trabajo — v1.4 BETA (solo admin/superadmin)
//  Cola de trabajo del asesor: cada área muestra UN chip con el
//  status declarado; tap para desplegar opciones y actualizarlo.
//  Las filas trabajadas hoy pasan a "Atendidas hoy" (al recargar).
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

// Un chip por área: label de lo declarado; tap despliega las opciones
$MESA_AREAS = [
    'contacto'   => ['no_contesta' => 'No contestó', 'hablamos' => 'Hablamos'],
    'compromiso' => ['compromiso' => 'Quedamos en algo', 'propuse_no_quiso' => 'Propuse, no quiso', 'sin_compromiso' => 'Nada concreto'],
    'postura'    => ['decidiendo' => 'Decidiendo', 'objecion_precio' => 'Objeción precio', 'pidio_cambios' => 'Pidió cambios', 'en_el_aire' => 'En el aire', 'descartada' => 'Descartada'],
];

$mesa_pend = array_values(array_filter($mesa['rows'], fn($r) => empty($r['atendida_hoy'])));
$mesa_aten = array_values(array_filter($mesa['rows'], fn($r) => !empty($r['atendida_hoy'])));

$mesa_row = function (array $r) use ($MESA_BUCKET_LBL, $MESA_AREAS, $mmoney) {
    $d  = $r['decl'] ?? [];
    $bl = $r['bucket'] ? ($MESA_BUCKET_LBL[$r['bucket']] ?? [$r['bucket'], '#64748b']) : null;
    $es_milagro = $r['revivida'] || $r['milagro'];
    ?>
    <tr style="border-top:1px solid #eeeee9;vertical-align:top<?= $es_milagro ? ';background:#fefce8' : '' ?>">
      <td style="padding:8px;white-space:nowrap">
        <a href="/cotizaciones/<?= (int)$r['id'] ?>" style="font-weight:700;color:#1a1a18;text-decoration:none"><?= e($r['cliente']) ?></a>
        <span class="mesa-done" style="<?= empty($r['atendida_hoy']) ? 'display:none' : '' ?>">✓</span>
        <div style="font-size:11px;color:#8a8a84"><?= e($r['numero']) ?><?= $r['dormida'] ? ' · 😴 ' . (int)$r['dias_sin_vista'] . 'd sin volver' : '' ?></div>
        <div style="font-size:10.5px;<?= $r['ult_decl_dias'] === null ? 'color:#d97706' : ($r['ult_decl_dias'] >= 3 ? 'color:#dc2626' : 'color:#a8a8a2') ?>">
          <?= $r['ult_decl_dias'] === null ? 'sin actualizar' : ($r['ult_decl_dias'] === 0 ? 'actualizada hoy' : 'actualizada hace ' . (int)$r['ult_decl_dias'] . 'd') ?></div>
      </td>
      <td style="padding:8px;font-weight:700;white-space:nowrap"><?= $mmoney($r['total']) ?></td>
      <td style="padding:8px;white-space:nowrap;<?= ($r['fuera_ventana'] && !$es_milagro) ? 'color:#dc2626;font-weight:700' : 'color:#4a4a46' ?>">
        <?= $es_milagro ? '⚡ ' : '' ?>día <?= (int)$r['edad'] ?>
        <?php if ($es_milagro): ?><div style="font-size:11px;color:#92400e;font-weight:600"><?= (int)$r['dias_sin_vista'] <= 1 ? 'volvió hoy' : 'volvió hace ' . (int)$r['dias_sin_vista'] . 'd' ?></div><?php endif; ?></td>
      <td style="padding:8px;white-space:nowrap">
        <?php if ($r['revivida']): ?><span style="font-size:11px;background:#fef3c7;color:#92400e;padding:2px 7px;border-radius:9px;font-weight:700">⚡ revivió tras descarte</span>
        <?php elseif ($bl): ?><span style="font-size:11px;background:<?= $bl[1] ?>18;color:<?= $bl[1] ?>;padding:2px 7px;border-radius:9px;font-weight:700"><?= e($bl[0]) ?></span>
        <?php else: ?><span style="color:#a8a8a2;font-size:11px">sin señal</span><?php endif; ?>
      </td>
      <?php foreach ($MESA_AREAS as $area => $opciones):
          $cur = $d[$area]['estado'] ?? '';
          $lbl = $opciones[$cur] ?? null; ?>
      <td style="padding:8px">
        <div class="mesa-decl" data-area="<?= $area ?>">
          <button type="button" class="chip<?= $lbl ? ' set' : '' ?>" onclick="mesaOpen(this)"><?= $lbl ? e($lbl) : '— declarar' ?></button>
          <div class="opts" hidden>
            <?php foreach ($opciones as $ek => $el): ?>
            <button type="button"<?= $cur === $ek ? ' class="on"' : '' ?> onclick="mesaTap(<?= (int)$r['id'] ?>,'<?= $area ?>','<?= $ek ?>',this)"><?= e($el) ?></button>
            <?php endforeach; ?>
          </div>
        </div>
      </td>
      <?php endforeach; ?>
      <td style="padding:8px;color:#4a4a46;min-width:240px">
        <?php if (!empty($r['alerta'])): ?><div style="color:#dc2626;font-weight:700;margin-bottom:2px">⚠ <?= e($r['alerta']) ?></div><?php endif; ?>
        <?= e($r['sugerencia']) ?></td>
    </tr>
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
          · <span style="color:#dc2626;font-weight:700"><?= (int)$mr['sin_postura'] ?> sin postura<?= $mr['mas_viejo_dias'] > 0 ? ' (la más vieja: ' . (int)$mr['mas_viejo_dias'] . 'd)' : '' ?></span>
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
      Aquí capturas el <b>status actual</b> de cada cotización — tapea un chip para declararlo
      y actualízalo en cada toque; siempre puedes cambiarlo.
    </div>

    <?php if (!$mesa['rows']): ?>
      <div style="color:#16a34a;padding:12px 0;font-weight:600">✓ Sin pendientes: todo lo activo está juzgado y dentro de ventana.</div>
    <?php else: ?>

      <?php if ($mesa_pend): ?>
      <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead><tr style="text-align:left;color:#6a6a64;font-size:11px;text-transform:uppercase;letter-spacing:.04em">
          <th style="padding:6px 8px">Cliente</th><th style="padding:6px 8px">Monto</th>
          <th style="padding:6px 8px">Ciclo</th><th style="padding:6px 8px">Radar</th>
          <th style="padding:6px 8px">Contacto</th><th style="padding:6px 8px">Compromiso</th><th style="padding:6px 8px">¿Cómo lo ves?</th><th style="padding:6px 8px">Sugerencia</th>
        </tr></thead>
        <tbody><?php foreach ($mesa_pend as $r) $mesa_row($r); ?></tbody>
      </table>
      </div>
      <?php else: ?>
      <div style="color:#16a34a;padding:12px 0;font-weight:600">✓ Pendientes en cero — todo lo de hoy ya está atendido.</div>
      <?php endif; ?>

      <?php if ($mesa_aten): ?>
      <div style="margin-top:14px;font-size:12px;color:#16a34a;font-weight:700;text-transform:uppercase;letter-spacing:.04em">
        ✓ Atendidas hoy (<?= count($mesa_aten) ?>)</div>
      <div style="overflow-x:auto;opacity:.72">
      <table style="width:100%;border-collapse:collapse;font-size:13px">
        <tbody><?php foreach ($mesa_aten as $r) $mesa_row($r); ?></tbody>
      </table>
      </div>
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
.mesa-done{color:#16a34a;font-weight:800;margin-left:4px}
.mesa-decl{position:relative;min-width:110px}
.mesa-decl .chip{
  border:1px dashed #d6d6d0;background:#fff;color:#8a8a84;
  border-radius:999px;padding:4px 12px;cursor:pointer;
  font:600 11.5px 'Plus Jakarta Sans',system-ui,sans-serif;
  white-space:nowrap;line-height:1.4;transition:all .12s}
.mesa-decl .chip:hover{border-color:#1a5c38;color:#1a5c38}
.mesa-decl .chip.set{
  border:1px solid #bbdcc8;background:#eef7f1;color:#14532d;font-weight:700}
.mesa-decl .opts{
  display:flex;flex-wrap:wrap;gap:4px;margin-top:6px;
  padding:8px;background:#fff;border:1px solid #e2e2dc;border-radius:10px;
  box-shadow:0 4px 14px rgba(0,0,0,.08)}
.mesa-decl .opts button{
  border:1px solid #e2e2dc;background:#fafaf8;color:#57534e;
  border-radius:999px;padding:4px 11px;cursor:pointer;
  font:600 11px 'Plus Jakarta Sans',system-ui,sans-serif;
  white-space:nowrap;line-height:1.4;transition:all .12s}
.mesa-decl .opts button:hover{border-color:#1a5c38;color:#1a5c38;background:#fff}
.mesa-decl .opts button.on{background:#1a5c38;border-color:#1a5c38;color:#fff}
.mesa-decl .opts button:disabled{opacity:.5}
</style>
<script>
function mesaOpen(chip){
  var box  = chip.closest('.mesa-decl');
  var opts = box.querySelector('.opts');
  document.querySelectorAll('#mesa-card .mesa-decl .opts').forEach(function(o){
    if(o !== opts) o.hidden = true;
  });
  opts.hidden = !opts.hidden;
}
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
    if(d.ok){
      var box = btn.closest('.mesa-decl');
      box.querySelectorAll('.opts button').forEach(function(b){ b.classList.remove('on'); });
      btn.classList.add('on');
      var chip = box.querySelector('.chip');
      chip.textContent = btn.textContent;
      chip.classList.add('set');
      box.querySelector('.opts').hidden = true;
      var done = btn.closest('tr').querySelector('.mesa-done');
      if(done) done.style.display = '';
    } else { alert('No se pudo guardar: ' + (d.error || 'error')); }
  }).catch(function(){ btn.disabled = false; alert('No se pudo guardar (red o sesión). Recarga la página e intenta de nuevo.'); });
}
</script>
