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
$mrec = Mesa::recuperado($empresa_id); // empresa-wide: la prueba en pesos de la mesa

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

$mesa_desc = array_values(array_filter($mesa['rows'], fn($r) => $r['cat'] === 'descartada_hoy'));
$mesa_pend = array_values(array_filter($mesa['rows'], fn($r) => empty($r['atendida_hoy']) && $r['cat'] !== 'descartada_hoy'));
$mesa_aten = array_values(array_filter($mesa['rows'], fn($r) => !empty($r['atendida_hoy']) && $r['cat'] !== 'descartada_hoy'));

$mesa_row = function (array $r) use ($MESA_BUCKET_LBL, $MESA_AREAS, $MESA_SHORT, $mmoney, $mp75) {
    $d  = $r['decl'] ?? [];
    $bl = $r['bucket'] ? ($MESA_BUCKET_LBL[$r['bucket']] ?? [$r['bucket'], '#64748b']) : null;
    $es_milagro = $r['revivida'] || $r['milagro'];
    // Semáforo de calor (4 estados, con leyenda arriba de la lista)
    if ($es_milagro)                          { $dot = '#d97706'; $dott = 'Volvió a calentarse' . ($bl ? ' — ' . $bl[0] : ''); }
    elseif (!empty($r['es_hot']))             { $dot = '#dc2626'; $dott = 'Caliente ahora — ' . ($bl[0] ?? ''); }
    elseif (in_array($r['bucket'], ['enfriandose', 'sobre_analisis'], true)) { $dot = '#94a3b8'; $dott = 'Enfriándose'; }
    elseif ($r['bucket'])                     { $dot = '#d97706'; $dott = 'Actividad reciente — ' . ($bl[0] ?? ''); }
    else                                      { $dot = null;      $dott = 'Sin señal del Radar'; }
    $udd = $r['ult_decl_dias'];
    ?>
    <div class="mrow<?= $es_milagro ? ' milagro' : '' ?><?= !empty($r['atendida_hoy']) ? ' done' : '' ?>" data-drawer="md<?= (int)$r['id'] ?>">
      <?php if ($dot): ?><span class="mdot" style="background:<?= $dot ?>" title="<?= e($dott) ?>"></span>
      <?php else: ?><span class="mdot off" title="<?= e($dott) ?>"></span><?php endif; ?>
      <span class="mcli">
        <a href="/cotizaciones/<?= (int)$r['id'] ?>" onclick="event.stopPropagation()"><?= e($r['titulo'] ?: $r['cliente']) ?></a>
        <span class="mfolio"><?= e($r['numero']) ?><?= $r['cliente'] && $r['titulo'] ? ' · ' . e($r['cliente']) : '' ?></span>
      </span>
      <span class="mflag"><?= $es_milagro ? '⚡' : ($r['dormida'] ? '<span title="' . (int)$r['dias_sin_vista'] . 'd sin volver a abrirla">😴</span>' : '') ?></span>
      <span class="mcheck"><?= !empty($r['atendida_hoy']) ? '✓' : '' ?></span>
      <span class="mciclo<?= ($r['fuera_ventana'] && !$es_milagro) ? ' late' : '' ?>">día <?= (int)$r['edad'] ?> de <?= $mp75 ?>
        <?php if ($es_milagro): ?><span class="mvolvio"><?= (int)$r['dias_sin_vista'] <= 0 ? 'la vio hoy' : ((int)$r['dias_sin_vista'] === 1 ? 'la vio ayer' : 'volvió hace ' . (int)$r['dias_sin_vista'] . 'd') ?></span><?php endif; ?>
      </span>
      <span class="mmoney"><?= $mmoney($r['total']) ?></span>
      <span class="mdecl3">
        <?php foreach (['contacto' => 's1', 'compromiso' => 's2', 'postura' => 's3'] as $a => $cls):
            $cur = $d[$a]['estado'] ?? ''; ?>
        <span class="<?= $cls ?><?= $cur ? ' f' : '' ?>"><?= $cur ? e($MESA_SHORT[$cur] ?? $cur) : '—' ?></span>
        <?php endforeach; ?>
      </span>
      <span class="mmarc" onclick="event.stopPropagation()">
        <button type="button" class="fbi<?= $r['postura'] === 'con_interes' ? ' on' : '' ?>"
          title="Con interés — se marca también en el Radar"
          onclick="mesaFb(<?= (int)$r['id'] ?>,'con_interes',this)">👍</button>
        <button type="button" class="fbi<?= $r['postura'] === 'sin_interes' ? ' on' : '' ?>"
          title="Sin interés — se descarta de la mesa; si el cliente revive, vuelve sola"
          onclick="mesaFb(<?= (int)$r['id'] ?>,'sin_interes',this)">👎</button>
      </span>
      <span class="mfresh<?= $udd === null ? ' warn' : ($udd >= 3 ? ' bad' : ($udd === 0 ? ' ok' : '')) ?>">
        <?= $udd === null ? 'sin actualizar' : ($udd === 0 ? 'hoy' : "hace {$udd}d") ?></span>
      <span class="msp"></span>
      <span class="mchev">▶</span>
    </div>
    <div class="mdrawer" id="md<?= (int)$r['id'] ?>">
      <div class="msug">
        <?php if ($r['revivida']): ?><span class="mtag" style="background:#fef3c7;color:#92400e">⚡ revivió tras descarte</span>
        <?php elseif ($bl && $r['es_hot']): ?><span class="mtag" style="background:<?= $bl[1] ?>18;color:<?= $bl[1] ?>"><?= e($bl[0]) ?></span><?php endif; ?>
        <span class="mlbl">→</span><span class="msx"><?= e($r['sugerencia']) ?></span>
      </div>
      <?php
          // Candados secuenciales 1→2→3 (área con valor previo = siempre editable)
          $con_e0 = $d['contacto']['estado'] ?? '';
          $lock2 = empty($d['compromiso']) && $con_e0 !== 'hablamos';
          $lock3 = empty($d['postura']) && empty($d['compromiso']) && $con_e0 !== 'no_contesta';
      ?>
      <div class="mareas">
        <div class="marea" data-area="contacto"><span class="man">1 · Contacto</span>
          <?php foreach ($MESA_AREAS['contacto'] as $ek => $el): ?>
          <button type="button" data-e="<?= $ek ?>" class="mpill<?= ($d['contacto']['estado'] ?? '') === $ek ? ' on' : '' ?>" onclick="mesaTap(<?= (int)$r['id'] ?>,'contacto','<?= $ek ?>',this)"><?= e($el) ?></button>
          <?php endforeach; ?></div>
        <div class="marea<?= $lock2 ? ' lock' : '' ?>" data-area="compromiso"><span class="man">2 · Compromiso</span>
          <?php foreach ($MESA_AREAS['compromiso'] as $ek => $el): ?>
          <button type="button" data-e="<?= $ek ?>" class="mpill<?= ($d['compromiso']['estado'] ?? '') === $ek ? ' on' : '' ?>" onclick="mesaTap(<?= (int)$r['id'] ?>,'compromiso','<?= $ek ?>',this)"><?= e($el) ?></button>
          <?php endforeach; ?>
          <span class="mlockmsg">primero el contacto (si hablaron)</span></div>
        <div class="marea<?= $lock3 ? ' lock' : '' ?>" data-area="postura"><span class="man">3 · ¿Cómo lo ves?</span>
          <?php foreach ($MESA_AREAS['postura'] as $ek => $el): if ($ek === 'descartada') continue; ?>
          <button type="button" data-e="<?= $ek ?>" class="mpill<?= ($d['postura']['estado'] ?? '') === $ek ? ' on' : '' ?>" onclick="mesaTap(<?= (int)$r['id'] ?>,'postura','<?= $ek ?>',this)"><?= e($el) ?></button>
          <?php endforeach; ?>
          <button type="button" data-e="descartada" class="mpill mdesc<?= ($d['postura']['estado'] ?? '') === 'descartada' ? ' on' : '' ?>" onclick="mesaRz(this)">Descartar</button>
          <span class="mrz<?= ($d['postura']['estado'] ?? '') === 'descartada' ? ' show' : '' ?>">
            <span class="mrz-l">¿motivo?</span>
            <?php foreach (['precio' => 'Muy caro', 'competencia' => 'Se fue con otro', 'despues' => 'Lo dejó para después', 'no_responde' => 'Dejó de responder', 'no_comprador' => 'No era comprador', 'otro' => 'Otro'] as $rk => $rl): ?>
            <button type="button" data-e="descartada" class="mpill mrz-b<?= ($d['postura']['estado'] ?? '') === 'descartada' && ($d['postura']['razon'] ?? '') === $rk ? ' on' : '' ?>" onclick="mesaTap(<?= (int)$r['id'] ?>,'postura','descartada',this,'<?= $rk ?>')"><?= e($rl) ?></button>
            <?php endforeach; ?>
          </span>
          <span class="mlockmsg">primero el paso anterior</span></div>
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
        <?php if (!empty($mr['descartadas'])): ?>
          · <span style="color:#b91c1c;font-weight:700">🗑 <?= (int)$mr['descartadas'] ?> descartada<?= $mr['descartadas'] > 1 ? 's' : '' ?> hoy</span>
        <?php endif; ?>
      <?php else: ?>
        <span style="color:#16a34a;font-weight:700">✓ al corriente</span>
      <?php endif; ?>
      <?php if ($mrec['rec_monto'] > 0): ?>
        · <span style="color:#15803d;font-weight:800">💰 <?= $mmoney($mrec['rec_monto']) ?> recuperado</span>
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
      Cada cotización vive en la mesa hasta el día <b><?= 2 * $mp75 ?></b> (2× tu ventana) porque
      tu cierre más tardío fue a los <b><?= (int)$mesa['limpieza']['linea_dias'] ?> días</b> — pasada tu ventana
      el consejo pide definición, no seguimiento eterno. Tapea una fila para trabajarla y actualízala en cada toque.
      <span style="white-space:nowrap;margin-left:6px">
        <span class="mleg" style="background:#dc2626"></span>caliente
        <span class="mleg" style="background:#d97706"></span>actividad reciente
        <span class="mleg" style="background:#94a3b8"></span>enfriándose
        <span class="mleg off"></span>sin señal
      </span>
      <a href="#" onclick="event.preventDefault();var p=document.getElementById('mesa-pb');p.style.display=p.style.display==='none'?'block':'none'"
         style="margin-left:10px;color:#1a5c38;font-weight:700;text-decoration:none;white-space:nowrap">📖 ¿Cómo funciona?</a>
      <a href="#" onclick="event.preventDefault();var p=document.getElementById('mesa-rp');p.style.display=p.style.display==='none'?'block':'none'"
         style="margin-left:8px;color:#1a5c38;font-weight:700;text-decoration:none;white-space:nowrap">📊 Reporte del equipo</a>
    </div>

    <?php if ($mrec['rec_n'] > 0 || $mrec['trab_n'] > 0): ?>
    <div style="margin-bottom:12px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;font-size:13px;color:#14532d">
      <?php if ($mrec['rec_n'] > 0): ?>
      💰 <b>Recuperado (últimos <?= (int)$mrec['dias'] ?> días): <?= $mmoney($mrec['rec_monto']) ?></b>
      — <?= (int)$mrec['rec_n'] ?> venta<?= $mrec['rec_n'] > 1 ? 's' : '' ?> que ya estaba<?= $mrec['rec_n'] > 1 ? 'n' : '' ?>
      descartada<?= $mrec['rec_n'] > 1 ? 's' : '' ?> y aun así se cerr<?= $mrec['rec_n'] > 1 ? 'aron' : 'ó' ?>.
      <?php endif; ?>
      <?php if ($mrec['trab_n'] > 0): ?>
      <?= $mrec['rec_n'] > 0 ? '<span style="color:#16a34a">·</span> ' : '' ?>Cerrado tras trabajarse aquí:
      <b><?= $mmoney($mrec['trab_monto']) ?></b> (<?= (int)$mrec['trab_n'] ?> venta<?= $mrec['trab_n'] > 1 ? 's' : '' ?>).
      <?php endif; ?>
      <span style="color:#3f6212;font-size:12px">Datos de toda la empresa, no solo de este asesor.</span>
    </div>
    <?php endif; ?>

    <div id="mesa-pb" style="display:none;margin-bottom:12px;padding:14px 16px;background:#fff;border:1px solid #e2e2dc;border-radius:10px;font-size:12.5px;color:#3f3f3a;line-height:1.6">
      <div style="font-weight:800;margin-bottom:6px">📖 Playbook de la Mesa de Trabajo</div>
      <p style="margin:0 0 8px"><b>Qué es.</b> Tu lista de trabajo del día. Se arma sola con 3 datos:
      qué tan caliente está el cliente (el Radar lee cómo abre y lee tu cotización), en qué día va
      contra el <b>ciclo real</b> de tu empresa, y cuánto vale. No tienes que armarla ni ordenarla — solo trabajarla.</p>
      <p style="margin:0 0 8px"><b>Qué haces aquí.</b> Después de cada toque al cliente, tapea la fila y
      declara el resultado en 3 pasos: <b>Contacto</b> (¿te respondió?), <b>Compromiso</b> (¿quedaron en
      algo?) y <b>Cómo lo ves</b> (tu lectura). Siempre puedes cambiarlo — la mesa guarda la historia y el
      consejo se rehace al instante con tu mezcla + lo que el cliente hace en la cotización.</p>
      <p style="margin:0 0 8px"><b>El semáforo.</b>
      <span class="mleg" style="background:#dc2626"></span><b>Caliente</b>: la está leyendo con intención AHORA — primero de la fila.
      <span class="mleg" style="background:#d97706"></span><b>Actividad reciente</b>: se movió hace poco (o revivió tras descartarla).
      <span class="mleg" style="background:#94a3b8"></span><b>Enfriándose</b>: cada vez la abre menos.
      <span class="mleg off"></span><b>Sin señal</b>: el cliente está quieto — si sigue dentro de tu ventana, tocarla es tu chamba: nadie más la va a mover.</p>
      <p style="margin:0 0 8px"><b>"Día X de Y".</b> La Y es tu ventana real: el 75% de tus ventas cierra antes de ese día
      (dato de tus cierres, no teoría). Dentro de la ventana el consejo empuja a cerrar; pasada la ventana
      te pide definición — fecha límite o descarte, no seguimiento eterno.</p>
      <p style="margin:0 0 8px"><b>El orden es 1 → 2 → 3, como la venta.</b> Primero el contacto;
      si hablaron se abre el Compromiso, y con el desenlace se abre tu lectura. Si NO contestó,
      el Compromiso no aplica (no hubo conversación) y pasas directo al paso 3 — ahí viven
      "En el aire" y "Descartar" para el que te evade. Una vez capturada, cualquier área se puede
      modificar cuando quieras: la declaración más reciente manda y el consejo se rearma con cada cambio.</p>
      <p style="margin:0 0 8px"><b>¿Por qué salen cotizaciones pasadas de la ventana (venta tardía)?</b>
      Porque tu propia historia dice que sí cierras algunas tarde — tu récord es el que marca el aviso de
      limpieza. La mesa las mantiene hasta el día 2× tu ventana con un consejo de ultimátum (fecha límite o
      descarte); después de ese día salen solas y, pasado tu récord histórico, se cuentan como ruido en el
      aviso rojo de abajo.</p>
      <p style="margin:0 0 8px"><b>"⚡ Revivió".</b> La descartaste y el cliente volvió a abrirla esta semana.
      La mesa no afirma por qué volvió (pudo ser un toque tuyo o movimiento del cliente) — solo te avisa que
      la señal existe. Esas se atienden HOY: una cotización descartada que se mueve no se deja pasar.</p>
      <p style="margin:0 0 8px"><b>El consejo (→).</b> No te repite lo que tú declaraste — te dice lo que el cliente
      hizo y tú no puedes ver (cuántas veces la abrió, cuántos días lleva callado, cuánta gente la está viendo)
      y la jugada concreta para el siguiente toque.</p>
      <p style="margin:0 0 8px"><b>👍👎 Feedback Radar.</b> Tu calificación de la cotización — es la MISMA
      del Radar: lo que marcas aquí aparece allá y viceversa. En el Radar los botones solo salen en señales
      calientes; aquí puedes calificar cualquiera. El 👎 la manda a "Descartadas hoy" (visible solo hoy, para que veas qué mataste); mañana sale de
      la mesa. El Radar la sigue vigilando y si el cliente revive, te la regresa con ⚡.</p>
      <p style="margin:0 0 8px"><b>💰 Recuperado.</b> Suma las ventas de los últimos 30 días cuya cotización
      YA estaba descartada (👎 o "Descartar") antes de cerrarse. Ese dinero se daba por muerto y aun así se
      cerró — sea porque el Radar te la regresó con ⚡ o porque alguien la retomó por su lado; el dato duro es
      solo ese: descartada antes, vendida después. Es de toda la empresa. "Cerrado tras trabajarse aquí" suma
      las demás ventas que pasaron por la mesa (con capturas) antes de cerrar.</p>
      <p style="margin:0"><b>✓ Atendidas hoy.</b> Lo que declaras hoy baja a su propia sección al recargar.
      La meta del día es simple: dejar los pendientes en cero.</p>
    </div>

    <?php
    $mrep = Mesa::reporte($empresa_id);
    $mpct = fn(int $n, int $d) => $d > 0 ? round(100 * $n / $d) . '%' : '—';
    ?>
    <div id="mesa-rp" style="display:none;margin-bottom:12px;padding:14px 16px;background:#fff;border:1px solid #e2e2dc;border-radius:10px;font-size:12.5px;color:#3f3f3a">
      <div style="font-weight:800;margin-bottom:2px">📊 Reporte del equipo</div>
      <div style="color:#6a6a64;margin-bottom:10px">Qué cartera carga cada asesor, qué NO ha hecho con ella, y qué está
        declarando en la mesa. Los taps que das desde la mesa de un asesor cuentan a nombre de ese asesor.</div>
      <?php if (!$mrep['asesores']): ?>
        <div style="color:#6a6a64">Sin cotizaciones activas ni capturas — el reporte se llena conforme hay cartera y se usa la mesa.</div>
      <?php else: ?>

      <div style="font-weight:800;font-size:11px;color:#a8a8a2;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Cartera hoy — lo que tiene y lo que NO ha hecho</div>
      <div style="overflow-x:auto;margin-bottom:14px">
      <table style="width:100%;border-collapse:collapse;font-size:12.5px;min-width:820px">
        <thead><tr style="text-align:left;color:#a8a8a2;font-size:10.5px;text-transform:uppercase;letter-spacing:.04em">
          <th style="padding:4px 8px 4px 0">Asesor</th>
          <th style="padding:4px 8px">Activas</th>
          <th style="padding:4px 8px">Sin calificar</th>
          <th style="padding:4px 8px">Sin trabajar</th>
          <th style="padding:4px 8px">Se le fueron</th>
          <th style="padding:4px 8px">Señales 🔥 desatendidas</th>
        </tr></thead>
        <tbody>
        <?php foreach ($mrep['asesores'] as $ru): ?>
        <tr style="border-top:1px solid #eeeee9;vertical-align:top">
          <td style="padding:7px 8px 7px 0;font-weight:700;white-space:nowrap"><?= e($ru['nombre'] ?: '—') ?></td>
          <td style="padding:7px 8px"><b><?= (int)$ru['activas'] ?></b></td>
          <td style="padding:7px 8px;white-space:nowrap">
            <?php if ($ru['sin_calificar']): ?>
              <span style="color:#dc2626;font-weight:700"><?= (int)$ru['sin_calificar'] ?></span> de <?= (int)$ru['activas'] ?>
              (<?= $mpct($ru['sin_calificar'], $ru['activas']) ?>)
            <?php else: ?><span style="color:#16a34a;font-weight:700">0 ✓</span><?php endif; ?>
          </td>
          <td style="padding:7px 8px;white-space:nowrap">
            <?php if ($ru['sin_trabajar']): ?>
              <span style="color:#dc2626;font-weight:700"><?= (int)$ru['sin_trabajar'] ?></span>
              · <?= $mmoney($ru['monto_sin_trabajar']) ?>
            <?php else: ?><span style="color:#16a34a;font-weight:700">0 ✓</span><?php endif; ?>
          </td>
          <td style="padding:7px 8px;white-space:nowrap">
            <?php if ($ru['se_fueron']): ?>
              <span style="color:#b91c1c;font-weight:800"><?= (int)$ru['se_fueron'] ?></span>
              · <?= $mmoney($ru['monto_se_fueron']) ?>
            <?php else: ?><span style="color:#16a34a;font-weight:700">0 ✓</span><?php endif; ?>
          </td>
          <td style="padding:7px 8px;white-space:nowrap">
            <?php if ($ru['hot_total']): ?>
              <span style="<?= $ru['hot_desatendidas'] > 0 ? 'color:#b91c1c;font-weight:800' : 'color:#16a34a;font-weight:700' ?>"><?= (int)$ru['hot_desatendidas'] ?></span> de <?= (int)$ru['hot_total'] ?>
            <?php else: ?><span style="color:#a8a8a2">sin señales</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>

      <div style="font-weight:800;font-size:11px;color:#a8a8a2;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Trabajo declarado — últimos <?= (int)$mrep['dias'] ?> días</div>
      <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:12.5px;min-width:820px">
        <thead><tr style="text-align:left;color:#a8a8a2;font-size:10.5px;text-transform:uppercase;letter-spacing:.04em">
          <th style="padding:4px 8px 4px 0">Asesor</th>
          <th style="padding:4px 8px">Le contesta</th>
          <th style="padding:4px 8px">Genera compromiso</th>
          <th style="padding:4px 8px">Compromisos cumplidos</th>
          <th style="padding:4px 8px">¿Cómo lo ve?</th>
          <th style="padding:4px 8px">👎 que revivieron</th>
          <th style="padding:4px 8px;text-align:right">Recuperado</th>
        </tr></thead>
        <tbody>
        <?php foreach ($mrep['asesores'] as $ru):
            $toques = $ru['hablamos'] + $ru['no_contesta'];
            $pos_tot = array_sum($ru['postura']);
        ?>
        <tr style="border-top:1px solid #eeeee9;vertical-align:top">
          <td style="padding:7px 8px 7px 0;font-weight:700;white-space:nowrap"><?= e($ru['nombre'] ?: '—') ?></td>
          <td style="padding:7px 8px;white-space:nowrap">
            <?php if ($toques): ?><b><?= (int)$ru['hablamos'] ?></b> de <?= $toques ?> toques
              <span style="color:<?= $ru['hablamos'] / $toques >= .5 ? '#16a34a' : '#dc2626' ?>;font-weight:700">(<?= $mpct($ru['hablamos'], $toques) ?>)</span>
            <?php else: ?><span style="color:#dc2626;font-weight:700">sin toques declarados</span><?php endif; ?>
          </td>
          <td style="padding:7px 8px;white-space:nowrap">
            <?php if ($ru['hablamos']): ?><b><?= (int)$ru['con_compromiso'] ?></b> de <?= (int)$ru['hablamos'] ?> pláticas
              <span style="color:<?= $ru['con_compromiso'] / $ru['hablamos'] >= .4 ? '#16a34a' : '#dc2626' ?>;font-weight:700">(<?= $mpct($ru['con_compromiso'], $ru['hablamos']) ?>)</span>
            <?php else: ?><span style="color:#a8a8a2">—</span><?php endif; ?>
          </td>
          <td style="padding:7px 8px;white-space:nowrap">
            <?php if ($ru['comp_maduros']): ?><b><?= (int)$ru['comp_cumplidos'] ?></b> de <?= (int)$ru['comp_maduros'] ?>
              (<?= $mpct($ru['comp_cumplidos'], $ru['comp_maduros']) ?>)<?= $ru['comp_en_curso'] ? ' · ' . (int)$ru['comp_en_curso'] . ' en curso' : '' ?>
            <?php elseif ($ru['comp_en_curso']): ?><?= (int)$ru['comp_en_curso'] ?> en curso
            <?php else: ?><span style="color:#a8a8a2">—</span><?php endif; ?>
          </td>
          <td style="padding:7px 8px">
            <?php if ($pos_tot): $pz = [];
                foreach ($ru['postura'] as $pe => $pn) $pz[] = e($MESA_SHORT[$pe] ?? $pe) . ' ' . $mpct($pn, $pos_tot);
                echo implode(' · ', $pz);
            ?><span style="color:#a8a8a2"> (<?= $pos_tot ?> cot.)</span>
            <?php else: ?><span style="color:#a8a8a2">—</span><?php endif; ?>
          </td>
          <td style="padding:7px 8px;white-space:nowrap">
            <?php if ($ru['descartes']): ?>
              <span style="<?= $ru['revividos'] > 0 ? 'color:#d97706;font-weight:700' : '' ?>"><?= (int)$ru['revividos'] ?> de <?= (int)$ru['descartes'] ?></span>
            <?php else: ?><span style="color:#a8a8a2">—</span><?php endif; ?>
          </td>
          <td style="padding:7px 8px;text-align:right;white-space:nowrap">
            <?php if ($ru['rec_n']): ?><b style="color:#15803d"><?= $mmoney($ru['rec_monto']) ?></b> (<?= (int)$ru['rec_n'] ?>)
            <?php else: ?><span style="color:#a8a8a2">—</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>

      <div style="margin-top:10px;color:#6a6a64;font-size:11.5px;line-height:1.55">
        <b>Cartera:</b>
        <b>Activas</b>: cotizaciones vivas asignadas (enviadas/vistas, sin venta).
        <b>Sin calificar</b>: activas donde el asesor no ha dado NINGÚN juicio (ni "¿Cómo lo ves?" ni 👍👎).
        <b>Sin trabajar</b>: activas sin una sola captura en la mesa — cartera que nadie está tocando, con su monto.
        <b>Se le fueron</b>: pasaron la ventana de cierre (día <?= $mp75 ?>) sin una sola captura — envejecieron sin que las trabajara.
        <b>Señales 🔥 desatendidas</b>: el Radar avisó que el cliente se calentó (con 1+ día para reaccionar) y no hubo ninguna acción después: ni captura, ni calificación, ni venta.
        <br><b>Trabajo:</b>
        <b>Le contesta</b>: de los toques declarados, en cuántos hubo plática.
        <b>Genera compromiso</b>: de las pláticas, cuántas amarraron "Quedamos en algo".
        <b>Cumplidos</b>: de los acuerdos con 5+ días, en cuántos el cliente se movió en los 5 días siguientes (abrió la cotización o compró) — dato observado, no juicio.
        <b>¿Cómo lo ve?</b>: su última lectura declarada por cotización.
        <b>👎 que revivieron</b>: descartes donde el cliente volvió a calentarse después — muchos = está matando ventas vivas.
        <b>Recuperado</b>: ventas que ya estaban descartadas y aun así se cerraron.
      </div>
    <?php endif; ?>
    </div>

    <?php if (!$mesa['rows']): ?>
      <div style="color:#16a34a;padding:12px 0;font-weight:600">✓ Sin pendientes: todo lo activo está trabajado y dentro de ventana.</div>
    <?php else: ?>

      <?php if ($mesa_pend): ?>
      <div class="mhead">
        <span class="mh-dot"></span><span class="mh-cot">Cotización</span><span class="mh-flag"></span><span class="mh-check"></span>
        <span class="mh-ciclo">Ciclo</span><span class="mh-money">Monto</span>
        <span class="mh-decl"><span class="s1">Contacto</span><span class="s2">Compromiso</span><span class="s3">Cómo lo ves</span></span>
        <span class="mh-marc">Feedback<br>Radar</span>
        <span class="mh-fresh">Actividad</span><span class="msp"></span><span class="mh-chev"></span>
      </div>
      <div class="mlist"><?php foreach ($mesa_pend as $r) $mesa_row($r); ?></div>
      <?php else: ?>
      <div style="color:#16a34a;padding:12px 0;font-weight:600">✓ Pendientes en cero — todo lo de hoy ya está atendido.</div>
      <?php endif; ?>

      <?php if ($mesa_aten): ?>
      <div class="msect">✓ Atendidas hoy (<?= count($mesa_aten) ?>)</div>
      <div class="mlist mdone-zone"><?php foreach ($mesa_aten as $r) $mesa_row($r); ?></div>
      <?php endif; ?>

      <?php if ($mesa_desc): ?>
      <div class="msect" style="color:#b91c1c">🗑 Descartadas hoy (<?= count($mesa_desc) ?>) — mañana salen de la mesa</div>
      <div class="mlist mdone-zone"><?php foreach ($mesa_desc as $r) $mesa_row($r); ?></div>
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
#mesa-card .mhead{margin-bottom:5px}
#mesa-card .mleg{display:inline-block;width:8px;height:8px;border-radius:50%;margin:0 4px 0 10px;vertical-align:baseline}
#mesa-card .mleg.off{background:transparent;border:1.5px solid #c9c9c2;width:7px;height:7px}
#mesa-card .mvolvio{display:block;font-size:10.5px;color:#92400e;font-weight:600}
#mesa-card .mrow{display:flex;align-items:center;gap:10px;padding:2px 12px;min-height:38px;cursor:pointer;background:#fafaf8}
#mesa-card .mrow + .mdrawer + .mrow, #mesa-card .mrow + .mrow{border-top:1px solid #eeeee9}
#mesa-card .mdrawer + .mrow{border-top:1px solid #eeeee9}
#mesa-card .mrow:hover{background:#f4f4ef}
#mesa-card .mrow.open{background:#fff}
#mesa-card .mrow.milagro{background:#fefce8}
#mesa-card .mrow.done{opacity:.78}
#mesa-card .mdone-zone .mrow{opacity:.72}
#mesa-card .mdot{width:9px;height:9px;border-radius:50%;flex:none}
#mesa-card .mdot.off{background:transparent;border:1.5px solid #c9c9c2}
#mesa-card .mcli{font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0;flex:1 1 380px;max-width:520px}
#mesa-card .mcli a{color:#1a1a18;text-decoration:none}
#mesa-card .mcli a:hover{color:#1a5c38;text-decoration:underline}
#mesa-card .mfolio{font-weight:500;color:#a3a39d;font-size:11px;margin-left:6px}
#mesa-card .mflag{font-size:11px;flex:none;width:18px;text-align:center}
#mesa-card .mcheck{color:#16a34a;font-weight:800;flex:none;width:16px;text-align:center}

#mesa-card .mciclo{font-size:12px;color:#57534e;font-variant-numeric:tabular-nums;flex:none;width:92px;text-align:right;white-space:nowrap}
#mesa-card .mciclo.late{color:#dc2626;font-weight:700}
#mesa-card .mmoney{font-weight:700;font-variant-numeric:tabular-nums;flex:none;width:82px;text-align:right}
#mesa-card .mdecl3{display:flex;gap:6px;flex:none}
#mesa-card .mdecl3 span{font-size:10.5px;line-height:1.3;color:#c9c9c2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#mesa-card .mdecl3 .s1{width:80px}#mesa-card .mdecl3 .s2{width:88px}#mesa-card .mdecl3 .s3{width:86px}
#mesa-card .mdecl3 span.f{color:#1a5c38;font-weight:700}
#mesa-card .mmarc{flex:none;width:74px;display:flex;gap:2px;justify-content:center}
#mesa-card .mmarc .fbi{border:none;background:none;cursor:pointer;font-size:13px;line-height:1;padding:3px 4px;filter:grayscale(1);opacity:.4;transition:all .12s}
#mesa-card .mmarc .fbi:hover{filter:none;opacity:.8}
#mesa-card .mmarc .fbi.on{filter:none;opacity:1}
#mesa-card .mfresh{font-size:10.5px;flex:none;width:82px;text-align:right;color:#a8a8a2;white-space:nowrap}
#mesa-card .mfresh.warn{color:#d97706;font-weight:700}
#mesa-card .mfresh.bad{color:#dc2626;font-weight:700}
#mesa-card .mfresh.ok{color:#16a34a;font-weight:700}
#mesa-card .msp{flex:1}
#mesa-card .mchev{color:#c9c9c2;flex:none;font-size:11px;transition:transform .15s}
#mesa-card .mrow.open .mchev{transform:rotate(90deg)}
#mesa-card .mdrawer{display:none;background:#fff;padding:12px 14px 14px 31px;border-top:1px solid #f4f4ef}
#mesa-card .mdrawer.open{display:block}
#mesa-card .mtag{font-size:11px;padding:2px 7px;border-radius:9px;font-weight:700;margin-right:6px}
#mesa-card .msug{font-size:13px;color:#3f3f3a;margin-bottom:10px}
#mesa-card .mlbl{color:#1a5c38;font-weight:800;margin-right:4px}
#mesa-card .mareas{display:flex;flex-direction:column;gap:7px}
#mesa-card .marea{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
#mesa-card .man{font-size:10.5px;font-weight:800;color:#8a8a84;text-transform:uppercase;letter-spacing:.05em;width:132px;flex:none;white-space:nowrap}
#mesa-card .mpill{border:1px solid #e2e2dc;background:#fafaf8;color:#57534e;border-radius:999px;padding:4px 12px;cursor:pointer;font:600 11.5px 'Plus Jakarta Sans',system-ui,sans-serif;white-space:nowrap;line-height:1.4;transition:all .12s}
#mesa-card .mpill:hover{border-color:#1a5c38;color:#1a5c38;background:#fff}
#mesa-card .mpill.on{background:#1a5c38;border-color:#1a5c38;color:#fff}
#mesa-card .mpill:disabled{opacity:.5}
#mesa-card .mdesc{border-color:#fecaca;color:#b91c1c;background:#fff}
#mesa-card .mdesc.on{background:#b91c1c;border-color:#b91c1c;color:#fff}
#mesa-card .mrz{display:none;align-items:baseline;gap:4px;flex-wrap:wrap;margin-left:8px;padding-left:10px;border-left:2px solid #fecaca}
#mesa-card .mrz.show{display:inline-flex}
#mesa-card .mrz-l{font-size:10.5px;color:#b91c1c;font-weight:800}
#mesa-card .mrz-b{border-color:#fecaca}
#mesa-card .mrz-b:hover{border-color:#b91c1c;color:#b91c1c}
#mesa-card .mrz-b.on{background:#b91c1c;border-color:#b91c1c;color:#fff}
#mesa-card .marea .mlockmsg{display:none;font-size:10.5px;color:#a8a8a2;font-style:italic}
#mesa-card .marea.lock .mpill{opacity:.35;pointer-events:none}
#mesa-card .marea.lock .mlockmsg{display:inline}
#mesa-card .mhead{display:flex;align-items:center;gap:10px;padding:0 12px;font-size:10px;font-weight:800;color:#a8a8a2;text-transform:uppercase;letter-spacing:.05em}
#mesa-card .mhead .mh-dot{flex:none;width:9px}
#mesa-card .mhead .mh-cot{flex:1 1 380px;max-width:520px;min-width:0}
#mesa-card .mhead .mh-flag{flex:none;width:18px}
#mesa-card .mhead .mh-check{flex:none;width:16px}
#mesa-card .mhead .mh-ciclo{flex:none;width:92px;text-align:right}
#mesa-card .mhead .mh-money{flex:none;width:82px;text-align:right}
#mesa-card .mhead .mh-decl{display:flex;gap:6px;flex:none}
#mesa-card .mhead .mh-decl .s1{width:80px}#mesa-card .mhead .mh-decl .s2{width:88px}#mesa-card .mhead .mh-decl .s3{width:86px}
#mesa-card .mhead .mh-decl span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#mesa-card .mhead .mh-marc{flex:none;width:74px;text-align:center;line-height:1.2}
#mesa-card .mhead .mh-fresh{flex:none;width:82px;text-align:right}
#mesa-card .mhead .mh-chev{flex:none;width:11px}
#mesa-card .msect{margin-top:14px;margin-bottom:6px;font-size:11px;color:#16a34a;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
#mesa-toast{position:fixed;left:50%;bottom:22px;transform:translateX(-50%);background:#1a1a18;color:#fff;font-size:12.5px;padding:9px 16px;border-radius:10px;opacity:0;pointer-events:none;transition:opacity .25s;z-index:9999}
#mesa-toast.show{opacity:.95}
@media (max-width:640px){
  #mesa-card .mfolio,#mesa-card .mfresh,#mesa-card .mmarc{display:none}
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

// Feedback Radar desde la mesa — se guarda a nombre del asesor dueño de la
// cotización (una sola marca: el descarte voltea el 👍 a 👎 automáticamente)
function mesaFb(cotId, tipo, btn){
  btn.disabled = true;
  fetch('/api/mesa/estado', {method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
    body: JSON.stringify({cotizacion_id:cotId, area:'feedback', estado:tipo})
  }).then(function(r){return r.json();}).then(function(d){
    btn.disabled = false;
    if(!d.ok){ mesaToast('No se pudo guardar: ' + (d.error || 'error')); return; }
    btn.parentElement.querySelectorAll('.fbi').forEach(function(x){x.classList.remove('on')});
    btn.classList.add('on');
    // reflejar en la fila y actualizar el consejo recalculado por el API
    var row = btn.closest('.mrow');
    var drawer = row ? document.getElementById(row.dataset.drawer) : null;
    if(drawer && d.sugerencia){
      var sx = drawer.querySelector('.msx'); if(sx) sx.textContent = d.sugerencia;
    }
    if(tipo === 'sin_interes' && row){
      row.style.opacity = '.72';
      var s3 = row.querySelectorAll('.mdecl3 span')[2];
      if(s3){ s3.textContent = 'Descartada'; s3.classList.add('f'); }
    }
    mesaToast(tipo === 'con_interes'
      ? '👍 marcado — también quedó en el Radar'
      : '👎 marcado — pasa a \"Descartadas hoy\" y mañana sale de la mesa; si el cliente revive, vuelve sola');
  }).catch(function(){ btn.disabled = false; mesaToast('No se pudo guardar (red o sesión).'); });
}

// Candados 1→2→3: un área con valor siempre es editable; sin valor,
// se desbloquea cuando el paso anterior aplica (no_contesta salta el 2)
function mesaLocks(drawer){
  var val = function(area){
    var b = drawer.querySelector('.marea[data-area="'+area+'"] .mpill.on');
    return b ? (b.dataset.e || '') : '';
  };
  var con = val('contacto'), com = val('compromiso'), pos = val('postura');
  var a2 = drawer.querySelector('.marea[data-area="compromiso"]');
  var a3 = drawer.querySelector('.marea[data-area="postura"]');
  if(a2) a2.classList.toggle('lock', !com && con !== 'hablamos');
  if(a3) a3.classList.toggle('lock', !pos && !com && con !== 'no_contesta');
}

var MESA_SHORT = <?= json_encode($MESA_SHORT, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
var MESA_IDX   = {contacto:0, compromiso:1, postura:2};

// Descartar: despliega los motivos a la derecha (misma línea), sin popup
function mesaRz(btn){
  btn.closest('.marea').querySelector('.mrz').classList.toggle('show');
}
function mesaTap(cotId, area, estado, btn, razon){
  razon = razon || null;
  var areaBtns = btn.closest('.marea') ? btn.closest('.marea').querySelectorAll('.mpill') : [btn];
  areaBtns.forEach(function(b){ b.disabled = true; });
  fetch('/api/mesa/estado', {method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
    body: JSON.stringify({cotizacion_id:cotId, area:area, estado:estado, razon:razon})
  }).then(function(r){return r.json();}).then(function(d){
    areaBtns.forEach(function(b){ b.disabled = false; });
    if(!d.ok){ mesaToast('No se pudo guardar: ' + (d.error || 'error')); return; }
    var drawer = btn.closest('.mdrawer');
    var row = document.querySelector('#mesa-card .mrow[data-drawer="'+drawer.id+'"]');
    // pill exclusivo dentro del área
    var areaEl = btn.closest('.marea');
    areaEl.querySelectorAll('.mpill').forEach(function(x){x.classList.remove('on')});
    if(estado === 'descartada'){
      var md = areaEl.querySelector('.mdesc'); if(md) md.classList.add('on');
      mesaToast('Descartada — pasa a \"Descartadas hoy\" al recargar y mañana sale de la mesa. Si el cliente revive, vuelve sola con ⚡');
    }
    btn.classList.add('on');
    // columnita con el label corto
    var slot = row.querySelectorAll('.mdecl3 span')[MESA_IDX[area]];
    if(slot){ slot.textContent = MESA_SHORT[estado] || estado; slot.classList.add('f'); }
    // frescura
    var fr = row.querySelector('.mfresh');
    if(fr){ fr.textContent = 'hoy'; fr.className = 'mfresh ok'; }
    // compromiso sin contacto → el sistema marcó "Hablamos" solo
    if(d.auto_contacto){
      var slot0 = row.querySelectorAll('.mdecl3 span')[0];
      if(slot0){ slot0.textContent = 'Hablamos'; slot0.classList.add('f'); }
      var conArea = drawer.querySelectorAll('.marea')[0];
      if(conArea) conArea.querySelectorAll('.mpill').forEach(function(x){
        x.classList.toggle('on', x.dataset.e === 'hablamos');
      });
    }
    // sugerencia recalculada por el servidor (mezcla + Radar + arquetipo)
    if(d.sugerencia){
      var sx = drawer.querySelector('.msx');
      if(sx) sx.textContent = d.sugerencia;
    }
    mesaLocks(drawer);
    if(estado !== 'descartada' && !row.classList.contains('done')){
      row.classList.add('done');
      var mc = row.querySelector('.mcheck'); if(mc) mc.textContent = '✓';
      mesaToast('✓ Atendida — al recargar pasa a "Atendidas hoy"');
    }
  }).catch(function(){ areaBtns.forEach(function(b){ b.disabled = false; }); mesaToast('No se pudo guardar (red o sesión) — recarga e intenta de nuevo.'); });
}
</script>
