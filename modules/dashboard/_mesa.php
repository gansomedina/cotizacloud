<?php
// ============================================================
//  Mesa de Trabajo — v1.1 BETA (solo admin/superadmin)
//  Solo muestra FALTAS y CONTRADICCIONES del asesor — lo que va
//  bien y lo fresco vive en el Radar, no aquí.
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

// Categoría → [emoji, título de sección, la jugada (una vez, no por fila)]
$MESA_CATS = [
    'revivida'         => ['⚡', 'La descartaste — y volvió a calentarse esta semana',
                           'Tu 👎 dice muerto, el cliente dice vivo. Un mensaje directo hoy — los milagros no se repiten.'],
    'milagro'          => ['⚡', 'Fuera de tu ciclo, pero la está viendo AHORA',
                           'Ya no debería estar viva y lo está. Es ahora o se va.'],
    'interes_muriendo' => ['⚠️', 'Dijiste "con interés" — y se está apagando',
                           'Tu juicio y el Radar se contradicen. Rescátala hoy o corrige tu postura.'],
    'sin_postura'      => ['❓', 'El cliente ya se movió y falta tu juicio',
                           'Ya la abrió y no la has calificado. ¿Cómo lo ves? (👍/👎 en el Radar)'],
    'ultimo_tramo'     => ['⏳', 'En serio, pero saliendo de tu ventana de cierre',
                           'Último tramo útil — un toque con motivo concreto, no un "sigo pendiente".'],
];
$MESA_B_SHORT = [
    'probable_cierre' => 'probable cierre', 'onfire' => 'on fire', 'inminente' => 'inminente',
    'validando_precio' => 'validando precio', 'prediccion_alta' => 'predicción alta',
    'lectura_comprometida' => 'lectura comprometida', 'multi_persona' => 'multi-persona',
    'alto_importe' => 'alto importe', 'hesitacion' => 'hesitación', 'enfriandose' => 'enfriándose',
];

// Agrupar filas por categoría (ya vienen ordenadas del motor)
$mesa_grupos = [];
foreach ($mesa['rows'] as $r) $mesa_grupos[$r['cat']][] = $r;
?>
<details class="card" id="mesa-card" style="margin-bottom:16px" <?= isset($_GET['mesa_uid']) ? 'open' : '' ?>>
  <summary style="cursor:pointer;padding:14px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;list-style:none">
    <span style="font-weight:800">📋 Mesa de trabajo</span>
    <span style="font-size:11px;background:#ede9fe;color:#6d28d9;padding:2px 8px;border-radius:10px;font-weight:700">BETA · solo admin</span>
    <span style="color:#4a4a46;font-size:13.5px">
      <?php if ($mr['n'] > 0): ?>
        <b><?= (int)$mr['n'] ?></b> pendientes · <b><?= $mmoney($mr['monto']) ?></b> en juego
        <?php if ($mr['sin_postura'] > 0): ?>
          · <span style="color:#dc2626;font-weight:700"><?= (int)$mr['sin_postura'] ?> sin juicio</span>
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
    <div style="font-size:12px;color:#6a6a64;margin-bottom:12px">
      Ciclo real: la mitad de tus ventas cierra en <b><?= (int)$mc['mediana'] ?>d</b>,
      el 75% antes del día <b><?= (int)$mc['p75'] ?></b>. La mesa solo enseña faltas y contradicciones — lo que va bien vive en el Radar.
    </div>
    <?php endif; ?>

    <?php if (!$mesa['rows']): ?>
      <div style="color:#16a34a;padding:10px 0;font-weight:600">✓ Sin faltas: todo lo activo está juzgado y dentro de ventana.</div>
    <?php else: ?>
      <?php foreach ($MESA_CATS as $catk => $cmeta): if (empty($mesa_grupos[$catk])) continue; ?>
      <div style="margin-bottom:14px">
        <div style="font-weight:800;font-size:13.5px;margin-bottom:2px"><?= $cmeta[0] ?> <?= e($cmeta[1]) ?> <span style="color:#8a8a84;font-weight:600">(<?= count($mesa_grupos[$catk]) ?>)</span></div>
        <div style="font-size:12px;color:#6a6a64;margin-bottom:6px"><?= e($cmeta[2]) ?></div>
        <?php foreach ($mesa_grupos[$catk] as $r):
            $senal = $r['dormida'] ? '😴 ' . (int)$r['dias_sin_vista'] . 'd sin volver'
                   : ($r['bucket'] ? ($MESA_B_SHORT[$r['bucket']] ?? $r['bucket']) : '');
        ?>
        <div style="display:flex;align-items:baseline;gap:10px;padding:6px 10px;border-left:3px solid <?= in_array($catk,['revivida','milagro'],true) ? '#d97706' : ($catk==='interes_muriendo' ? '#dc2626' : '#c8c8c0') ?>;background:#fafaf8;border-radius:0 8px 8px 0;margin-bottom:4px;font-size:13px;flex-wrap:wrap">
          <a href="/cotizaciones/<?= (int)$r['id'] ?>" style="font-weight:700;color:#1a1a18;text-decoration:none"><?= e($r['cliente']) ?></a>
          <span style="font-weight:800;white-space:nowrap"><?= $mmoney($r['total']) ?></span>
          <span style="color:#6a6a64;white-space:nowrap">día <?= (int)$r['edad'] ?></span>
          <?php if ($senal): ?><span style="color:#8a8a84;font-size:12px;white-space:nowrap"><?= e($senal) ?></span><?php endif; ?>
          <span style="color:#a8a8a2;font-size:11px;margin-left:auto;white-space:nowrap"><?= e($r['numero']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($mesa['limpieza']['n'] >= 10): ?>
    <div style="margin-top:6px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:12.5px;color:#7f1d1d">
      🗑 <b><?= (int)$mesa['limpieza']['n'] ?></b> cotizaciones (<?= $mmoney($mesa['limpieza']['monto']) ?>) rebasan los
      <b><?= (int)$mesa['limpieza']['linea_dias'] ?> días</b> — tu empresa jamás ha cerrado una de esa edad.
      No son pipeline, son ruido. <span style="color:#9a3412">(Suspensión en lote: próxima versión.)</span>
    </div>
    <?php endif; ?>

  </div>
</details>
