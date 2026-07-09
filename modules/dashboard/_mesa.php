<?php
// ============================================================
//  Mesa de Trabajo — v1.3 BETA (solo admin/superadmin)
//  Tabla completa (versión original) con los fixes: solo faltas/
//  contradicciones (no repite el Radar), excluye vendidas,
//  resurrección solo con calor <=7d. Solo lectura.
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
$POSTURA_LBL = ['con_interes' => '👍 con interés', 'sin_interes' => '👎 descartada'];
?>
<details class="card" id="mesa-card" style="margin-bottom:16px" <?= isset($_GET['mesa_uid']) ? 'open' : '' ?>>
  <summary style="cursor:pointer;padding:14px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;list-style:none">
    <span style="font-weight:800">📋 Mesa de trabajo</span>
    <span style="font-size:11px;background:#ede9fe;color:#6d28d9;padding:2px 8px;border-radius:10px;font-weight:700">BETA · solo admin</span>
    <span style="color:#4a4a46;font-size:13.5px">
      <?php if ($mr['n'] > 0): ?>
        <b><?= (int)$mr['n'] ?></b> pendientes · <b><?= $mmoney($mr['monto']) ?></b> en juego
        <?php if ($mr['sin_postura'] > 0): ?>
          · <span style="color:#dc2626;font-weight:700"><?= (int)$mr['sin_postura'] ?> sin postura<?= $mr['mas_viejo_dias'] > 0 ? ' (la más vieja: ' . (int)$mr['mas_viejo_dias'] . 'd)' : '' ?></span>
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

    <?php $mc = $mesa['ciclo']; if (!empty($mc['auto'])): ?>
    <div style="font-size:12px;color:#6a6a64;margin-bottom:10px">
      Ciclo real de la empresa: la mitad de tus ventas cierra en <b><?= (int)$mc['mediana'] ?>d</b>,
      el 75% antes del día <b><?= (int)$mc['p75'] ?></b> (<?= (int)$mc['n'] ?> cierres).
      La mesa solo enseña faltas y contradicciones — lo que va bien vive en el Radar.
    </div>
    <?php endif; ?>

    <?php if (!$mesa['rows']): ?>
      <div style="color:#16a34a;padding:12px 0;font-weight:600">✓ Sin faltas: todo lo activo está juzgado y dentro de ventana.</div>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <thead><tr style="text-align:left;color:#6a6a64;font-size:11px;text-transform:uppercase;letter-spacing:.04em">
        <th style="padding:6px 8px">Cliente</th><th style="padding:6px 8px">Monto</th>
        <th style="padding:6px 8px">Ciclo</th><th style="padding:6px 8px">Radar</th>
        <th style="padding:6px 8px">Postura</th><th style="padding:6px 8px">Sugerencia</th>
      </tr></thead>
      <tbody>
      <?php foreach ($mesa['rows'] as $r):
          $bl = $r['bucket'] ? ($MESA_BUCKET_LBL[$r['bucket']] ?? [$r['bucket'], '#64748b']) : null;
          $es_milagro = $r['revivida'] || $r['milagro'];
      ?>
      <tr style="border-top:1px solid #eeeee9;vertical-align:top<?= $es_milagro ? ';background:#fefce8' : '' ?>">
        <td style="padding:8px;white-space:nowrap">
          <a href="/cotizaciones/<?= (int)$r['id'] ?>" style="font-weight:700;color:#1a1a18;text-decoration:none"><?= e($r['cliente']) ?></a>
          <div style="font-size:11px;color:#8a8a84"><?= e($r['numero']) ?><?= $r['dormida'] ? ' · 😴 ' . (int)$r['dias_sin_vista'] . 'd sin volver' : '' ?></div>
        </td>
        <td style="padding:8px;font-weight:700;white-space:nowrap"><?= $mmoney($r['total']) ?></td>
        <td style="padding:8px;white-space:nowrap;<?= ($r['fuera_ventana'] && !$es_milagro) ? 'color:#dc2626;font-weight:700' : 'color:#4a4a46' ?>">
          <?= $es_milagro ? '⚡ ' : '' ?>día <?= (int)$r['edad'] ?>
          <?php if ($es_milagro): ?><div style="font-size:11px;color:#92400e;font-weight:600"><?= (int)$r['dias_sin_vista'] <= 1 ? 'volvió hoy' : 'volvió hace ' . (int)$r['dias_sin_vista'] . 'd' ?></div><?php endif; ?></td>
        <td style="padding:8px;white-space:nowrap">
          <?php if ($r['revivida']): ?><span style="font-size:11px;background:#fef3c7;color:#92400e;padding:2px 7px;border-radius:9px;font-weight:700">⚡ revivió tras descarte</span>
          <?php elseif ($bl): ?><span style="font-size:11px;background:<?= $bl[1] ?>18;color:<?= $bl[1] ?>;padding:2px 7px;border-radius:9px;font-weight:700"><?= e($bl[0]) ?></span>
          <?php else: ?><span style="color:#a8a8a2;font-size:11px">—</span><?php endif; ?>
        </td>
        <td style="padding:8px;white-space:nowrap;font-size:12px">
          <select onchange="mesaEstado(<?= (int)$r['id'] ?>, this)" style="font-size:12px;padding:3px 6px;border:1px solid #d4d4ce;border-radius:8px;background:#fff;max-width:150px">
            <option value=""><?= $r['postura'] ? e($POSTURA_LBL[$r['postura']] ?? $r['postura']) : '— ¿qué pasó? —' ?></option>
            <option value="no_contesta">📵 No contesta</option>
            <option value="en_cita">📅 Quedamos en cita</option>
            <option value="decidiendo">💬 Está decidiendo</option>
            <option value="objecion_precio">💰 Objeción precio</option>
            <option value="pidio_cambios">✏️ Pidió cambios</option>
            <option value="sin_compromiso">😶 Hablamos, sin nada concreto</option>
            <option value="propuse_no_quiso">🚫 Propuse cita, no quiso</option>
            <option value="descartada">🗑 Descartar…</option>
          </select></td>
        <td style="padding:8px;color:#4a4a46;min-width:240px"><?= e($r['sugerencia']) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>

    <?php if ($mesa['limpieza']['n'] >= 10): ?>
    <div style="margin-top:12px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:12.5px;color:#7f1d1d">
      🗑 <b><?= (int)$mesa['limpieza']['n'] ?></b> cotizaciones (<?= $mmoney($mesa['limpieza']['monto']) ?>) tienen más de
      <b><?= (int)$mesa['limpieza']['linea_dias'] ?> días</b> — tu empresa jamás ha cerrado una de esa edad.
      Ya no son pipeline, son ruido. <span style="color:#9a3412">(Suspensión en lote: próxima versión.)</span>
    </div>
    <?php endif; ?>

  </div>
</details>\n
<script>
function mesaEstado(cotId, sel){
  var estado = sel.value; if(!estado) return;
  var razon = null;
  if(estado==='descartada'){
    var r = prompt('Razón: 1=Muy caro  2=Se fue con otro  3=Lo dejó para después  4=Dejó de responder  5=No era comprador  6=Otro','1');
    var map = {'1':'precio','2':'competencia','3':'despues','4':'no_responde','5':'no_comprador','6':'otro'};
    razon = map[(r||'').trim()]; if(!razon){ sel.value=''; return; }
  }
  sel.disabled = true;
  fetch('/api/mesa/estado', {method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
    body: JSON.stringify({cotizacion_id:cotId, estado:estado, razon:razon})
  }).then(function(r){return r.json();}).then(function(d){
    sel.disabled = false;
    if(d.ok){ sel.style.borderColor='#16a34a'; sel.style.background='#f0fdf4'; }
    else { alert('No se pudo guardar: '+(d.error||'')); sel.value=''; }
  }).catch(function(){ sel.disabled=false; sel.value=''; });
}
</script>
