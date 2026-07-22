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
// Gate: admin → mesa completa (todas las mesas + reporte + recuperado);
// asesor → SU mesa, solo si la empresa abrió el rollout (mesa_activa>=1:
// 0=off, 1=UI asesores, 2=UI+score). La columna puede no existir aún.
$mesa_es_admin  = !empty($es_admin_dash);
$mesa_ui_asesor = !$mesa_es_admin && ((int)($empresa['mesa_activa'] ?? 0) >= 1);
if (!$mesa_es_admin && !$mesa_ui_asesor) return;

if ($mesa_es_admin) {
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
} else {
    // El asesor SIEMPRE es su propia mesa — aunque hoy no tenga cartera
    // activa (así conserva su widget de cobertura de la quincena)
    $mesa_vendedores = [['id' => Auth::id(), 'nombre' => '']];
}

$mmoney = fn(float $n) => '$' . number_format($n, 0);
$mesa_dias = (int)($_GET['mesa_dias'] ?? 30);
if (!in_array($mesa_dias, [7, 15, 30, 60, 90], true)) $mesa_dias = 30;
// Admin: recuperado de toda la empresa. Asesor: SU recuperado (el gancho
// de adopción — "tú recuperaste $X de cotizaciones que dabas por muertas")
$mrec = Mesa::recuperado($empresa_id, $mesa_dias, $mesa_es_admin ? null : Auth::id());

// Cobertura de señales del asesor — MISMA fuente que lo examina el score
// (helper único) y mismo período efectivo del termómetro
$mesa_cob = null; $mesa_cob_det = [];
if (!$mesa_es_admin) {
    try { $mesa_per = ActividadScore::periodo_efectivo($empresa_id); }
    catch (Throwable $e) { $mesa_per = 15; }
    $mesa_cob     = Mesa::cobertura_senales($empresa_id, Auth::id(), $mesa_per);
    $mesa_cob_det = Mesa::cobertura_detalle($empresa_id, Auth::id(), $mesa_per);
}

// Una mesa POR ASESOR — se incrusta en su tarjeta del ranking, abajo del tip.
// Este include NO emite nada: llena $MESA_SHARED (empresa-wide: recuperado,
// playbook, reporte, CSS/JS) y $MESA_BLOQUES[uid] (la mesa de cada asesor).
$MESA_EMITIDO = false;
$mesa_all = []; $mesa_nombres = [];
foreach ($mesa_vendedores as $mv) {
    $mesa_all[(int)$mv['id']] = Mesa::armar($empresa_id, (int)$mv['id']);
    $mesa_nombres[(int)$mv['id']] = $mv['nombre'];
}
$mesa_first = reset($mesa_all);
$mp75 = max(1, (int)$mesa_first['p75']);

// Historial de toques por cotización (panel izquierdo del cajón). UNA consulta
// batcheada para TODAS las cotizaciones visibles (no N+1). Solo lectura de
// mesa_estados (que ya es insert-only) — backend intacto.
$mesa_hist = [];
$mesa_hist_cids = [];
foreach ($mesa_all as $ma) foreach (($ma['rows'] ?? []) as $rr) $mesa_hist_cids[] = (int)$rr['id'];
if ($mesa_hist_cids) {
    $in_h = implode(',', array_map('intval', array_unique($mesa_hist_cids)));
    try {
        foreach (DB::query("SELECT cotizacion_id, area, estado, razon, created_at FROM mesa_estados WHERE cotizacion_id IN ($in_h) AND (razon IS NULL OR razon <> 'auto') ORDER BY id") as $h) {
            $mesa_hist[(int)$h['cotizacion_id']][] = $h;
        }
    } catch (\Throwable $e) {}
}

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
    'compromiso' => ['compromiso' => 'Quedamos en algo', 'nos_citamos' => 'Nos citamos', 'propuse_no_quiso' => 'Propuse, no quiso', 'sin_compromiso' => 'Nada concreto'],
    'postura'    => ['decidiendo' => 'Decidiendo', 'objecion_precio' => 'Objeción precio', 'pidio_cambios' => 'Pidió cambios', 'en_el_aire' => 'En el aire', 'descartada' => 'Descartar'],
];
$MESA_SHORT = [
    'no_contesta' => 'No contestó', 'hablamos' => 'Hablamos',
    'compromiso' => 'Quedamos', 'nos_citamos' => 'Cita', 'propuse_no_quiso' => 'No quiso', 'sin_compromiso' => 'Nada',
    'decidiendo' => 'Decidiendo', 'objecion_precio' => 'Precio', 'pidio_cambios' => 'Cambios',
    'en_el_aire' => 'En el aire', 'descartada' => 'Descartada',
];

// Límites del picker de agenda (el backend re-valida: 15 días … 6 meses).
// Piso de 15 días: la agenda es para compra a futuro, no para posponer unos
// días — reaparece 7 días antes, con menos de 15 no alcanza a parquearse.
$mag_min = date('Y-m-d', strtotime('+15 days'));
$mag_max = date('Y-m-d', strtotime('+183 days'));

// $rank = número de orden de ataque (solo secciones pendientes) · $ql = mostrar
// la línea "→ qué hacer" (sugerencia) en la fila colapsada (idea 3 del mockup)
$mesa_row = function (array $r, ?int $rank = null, bool $ql = false) use ($MESA_BUCKET_LBL, $MESA_AREAS, $MESA_SHORT, $mmoney, $mp75, $mag_min, $mag_max, $mesa_hist) {
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
    <?php $has_ql = $ql && !empty($r['sugerencia']); ?>
    <div class="mrow<?= $es_milagro ? ' milagro' : '' ?><?= !empty($r['atendida_hoy']) ? ' done' : '' ?><?= $has_ql ? ' has-ql' : '' ?>" data-drawer="md<?= (int)$r['id'] ?>">
      <?php if ($rank !== null): ?><span class="mrank" title="Orden sugerido de ataque"><?= (int)$rank ?></span><?php else: ?><span class="mrank off"></span><?php endif; ?>
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
        <button type="button" class="fbi<?= $r['postura'] === 'sin_info' ? ' on' : '' ?>"
          title="Sin comunicación — intentaste y el cliente no responde: cuenta como evaluación sin juzgarlo. Solo con &quot;No contestó&quot; marcado; cuando logres contacto, cámbialo a 👍/👎"
          onclick="mesaFb(<?= (int)$r['id'] ?>,'sin_info',this)">📵</button>
      </span>
      <?php $sg = $r['seguimiento'] ?? null;
            $sg_ult = $udd === null ? 'sin declaraciones' : ($udd === 0 ? 'última declaración hoy' : "última declaración hace {$udd}d");
            $sg_f = $udd === null ? 'sin actualizar' : ($udd === 0 ? 'hoy' : "hace {$udd}d");
            $sg_h = (int)($r['venc_huella'] ?? 0);
            $sg_hf = $sg_h > 0 ? ' <small style="opacity:.75" title="Acumuló ' . $sg_h . 'd sin seguimiento en el período — no se borra al ponerse al corriente; se drena con los días">⏰' . $sg_h . 'd</small>' : '';
            if (!empty($r['cita_vencida'])): ?>
      <span class="mfresh bad" title="La cita pasó su límite (<?= e($sg['vence']) ?>) sin actualizarse — solo baja registrando el desenlace (Quedamos / No quiso / Nada), descartándola, o Hablamos + re-citar si la pospusieron. <?= e($sg_ult) ?>"><?= e($sg_f) ?> · 🔴 cita sin actualizar <?= (int)$sg['dias'] ?>d</span>
      <?php elseif ($sg && $sg['estado'] === 'vencida'): ?>
      <span class="mfresh bad" title="Pasó su límite de seguimiento (<?= e($sg['vence']) ?>) — un contacto declarado (Hablamos / No contestó) la pone al corriente. <?= e($sg_ult) ?>"><?= e($sg_f) ?> · 🔴 sin seguimiento <?= (int)$sg['dias'] ?>d</span>
      <?php elseif ($sg && $sg['estado'] === 'hoy'): ?>
      <span class="mfresh warn" title="HOY es el límite de seguimiento — un contacto declarado la pone al corriente. <?= e($sg_ult) ?>"><?= e($sg_f) ?> · 🟠 límite HOY<?= $sg_hf ?></span>
      <?php elseif ($sg): ?>
      <span class="mfresh<?= $udd === 0 ? ' ok' : '' ?>" title="Al corriente — límite de seguimiento: <?= e($sg['vence']) ?>. <?= e($sg_ult) ?>"><?= e($sg_f) ?> · límite <?= e(date('d/m', strtotime($sg['vence']))) ?><?= $sg_hf ?></span>
      <?php else: ?>
      <span class="mfresh<?= $udd === null ? ' warn' : ($udd >= 3 ? ' bad' : ($udd === 0 ? ' ok' : '')) ?>">
        <?= $udd === null ? 'sin actualizar' : ($udd === 0 ? 'hoy' : "hace {$udd}d") ?></span>
      <?php endif; ?>
      <span class="msp"></span>
      <span class="mchev">▶</span>
      <?php if ($has_ql): ?><span class="mql">→ <?= e($r['sugerencia']) ?></span><?php endif; ?>
    </div>
    <div class="mdrawer" id="md<?= (int)$r['id'] ?>">
      <div class="msug">
        <?php if (!empty($r['agenda_fecha'])): ?><span class="mtag" style="background:#dbeafe;color:#1d4ed8">📅 la agendaste para <?= e(date('d/m/Y', strtotime($r['agenda_fecha']))) ?></span>
        <?php elseif ($r['revivida']): ?><span class="mtag" style="background:#fef3c7;color:#92400e">⚡ revivió tras descarte</span>
        <?php elseif ($bl && $r['es_hot']): ?><span class="mtag" style="background:<?= $bl[1] ?>18;color:<?= $bl[1] ?>"><?= e($bl[0]) ?></span><?php endif; ?>
        <span class="mlbl">→</span><span class="msx"><?= e($r['sugerencia']) ?></span>
      </div>
      <?php
          // ── Intentos de contacto (solo GHOST: sin 👍 y sin 👎). "Intento N de 4";
          //    al 4º "no contestó" sin lograr contacto → botón de SUSPENDER asistido
          //    (nunca automático). 👍 se persigue (interes_muriendo) · 👎 se descarta
          //    (slug vivo para revival) · 📵/sin-manita = ghost declarado. intentos_nc
          //    ya resetea con "Hablamos" y tiene ventana de 30d (Mesa.php).
          $manita_r  = $r['postura'] ?? null;               // con_interes/sin_interes/sin_info/null
          $es_ghost_r = ($manita_r === null || $manita_r === 'sin_info');
          $nc_r = (int)($r['intentos_nc'] ?? 0);
          // Las BOLITAS (progreso de intentos) se muestran a todo ghost con
          // intentos, incluso caliente — son contexto útil. Solo se ocultan en
          // filas ya resueltas (descartada/atendida hoy), donde serían ruido.
          $mostrar_int_r = $es_ghost_r && $nc_r >= 1
              && ($r['cat'] ?? '') !== 'descartada_hoy' && empty($r['atendida_hoy']);
          // El BOTÓN de suspender solo en el ghost FRÍO (guard de la auditoría):
          // en milagro/revivida/agendada/caliente hay señal viva del cliente —
          // ofrecer suspender ahí sería suspender una venta viva.
          $suspendible_r = $mostrar_int_r
              && empty($r['es_hot']) && empty($r['revivida']) && empty($r['milagro'])
              && empty($r['agenda_fecha']);
          if ($mostrar_int_r):
      ?>
      <div class="mintentos<?= $nc_r >= 4 && $suspendible_r ? ' full' : '' ?>">
        <div class="mint-top">
          <span class="mint-h">Intentos de contacto sin respuesta</span>
          <span class="mint-n"><?= min($nc_r, 4) ?> de 4</span>
          <span class="mint-beads"><?php for ($i = 1; $i <= 4; $i++): ?><span class="mint-b<?= $i <= $nc_r ? '' : ' off' ?><?= $i === 4 ? ' susp' : '' ?>"></span><?php endfor; ?></span>
        </div>
        <?php if ($nc_r >= 4 && $suspendible_r): ?>
        <div class="mint-msg">Llevas <b><?= $nc_r ?> «no contestó»</b> sin lograr contacto. <b>Para conservarla:</b> háblale y contesta (reinicia el conteo), márcala 👍 (hay interés) o reenvíale/edítale algo. Si ya no responde, suspéndela.</div>
        <button type="button" class="mint-susp" onclick="mesaSuspender(<?= (int)$r['id'] ?>)">Suspender cotización</button>
        <?php elseif ($nc_r >= 4): ?>
        <div class="mint-msg">Llevas <b><?= $nc_r ?> «no contestó»</b> — pero hay señal viva del cliente (te lee, volvió o está agendada), así que suspender no aplica: <b>insiste ahora que está activo</b>.</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php
          // Candados del panel de CAPTURA: siempre FRESCO (1→2→3). Compromiso y
          // ¿Cómo lo ves? arrancan BLOQUEADOS y se abren con la SELECCIÓN de hoy
          // (mesaSelLocks), no con lo ya declarado — cada captura es un toque
          // nuevo, así que el asesor declara primero el contacto.
          $mdf = fn($at) => $at ? date('d/m', strtotime($at)) : '';
      ?>
      <div class="mdrawer-cols">
        <!-- DERECHA (visual, via order:2): seguimiento declarado — display + fecha + historial -->
        <div class="mcol mcol-info">
          <div class="mcol-h">Seguimiento declarado</div>
          <div class="mdecl">
            <div class="mdln"><span class="mdk">1 · Contacto</span><span class="mdv<?= ($d['contacto']['estado'] ?? '') ? '' : ' none' ?>"><?= ($d['contacto']['estado'] ?? '') ? e($MESA_SHORT[$d['contacto']['estado']] ?? '') : 'sin declarar' ?></span><span class="mdd"><?= $mdf($d['contacto']['at'] ?? null) ?></span></div>
            <div class="mdln"><span class="mdk">2 · Compromiso</span><span class="mdv<?= ($d['compromiso']['estado'] ?? '') ? '' : ' none' ?>"><?= ($d['compromiso']['estado'] ?? '') ? e($MESA_SHORT[$d['compromiso']['estado']] ?? '') : '—' ?></span><span class="mdd"><?= $mdf($d['compromiso']['at'] ?? null) ?></span></div>
            <div class="mdln"><span class="mdk">3 · ¿Cómo lo ves?</span><span class="mdv<?= ($d['postura']['estado'] ?? '') ? '' : ' none' ?>"><?= ($d['postura']['estado'] ?? '') ? e($MESA_SHORT[$d['postura']['estado']] ?? '') : 'sin declarar' ?></span><span class="mdd"><?= $mdf($d['postura']['at'] ?? null) ?></span></div>
          </div>
          <?php $mhh = $mesa_hist[(int)$r['id']] ?? []; if (count($mhh) > 1): ?>
          <div class="mhist">
            <button type="button" class="mhist-t" onclick="this.closest('.mhist').classList.toggle('open')"><span class="chev">▶</span> Historial (<?= count($mhh) ?> toques)</button>
            <div class="mhist-b">
              <?php foreach (array_reverse($mhh) as $he): ?>
              <div class="mhist-i"><span class="mho"><?= e($MESA_SHORT[$he['estado']] ?? $he['estado']) ?></span><span class="mhd"><?= date('d/m', strtotime($he['created_at'])) ?></span></div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- IZQUIERDA (visual, via order:1): capturar el siguiente — reemplaza el re-tap -->
        <div class="mcol mcol-cap">
          <div class="mcol-h">Capturar siguiente seguimiento</div>
          <div class="mareas">
            <div class="marea" data-area="contacto"><span class="man">1 · Contacto</span>
              <?php foreach ($MESA_AREAS['contacto'] as $ek => $el): ?>
              <button type="button" data-e="<?= $ek ?>" class="mpill" onclick="mesaSel(this,<?= (int)$r['id'] ?>)"><?= e($el) ?></button>
              <?php endforeach; ?></div>
            <div class="marea lock" data-area="compromiso"<?= !empty($r['cita_vencida']) ? ' style="border:1px solid #dc2626;border-radius:6px;padding:4px 6px;background:#fef2f2"' : '' ?>><span class="man">2 · Compromiso</span>
              <?php if (!empty($r['cita_vencida'])): ?><span style="display:block;color:#b91c1c;font-weight:700;font-size:12px;margin:2px 0">❓ ¿Qué pasó con la cita? — registra el desenlace; si la pospusieron: Hablamos + re-citar</span><?php endif; ?>
              <?php foreach ($MESA_AREAS['compromiso'] as $ek => $el): ?>
              <button type="button" data-e="<?= $ek ?>" class="mpill" onclick="mesaSel(this,<?= (int)$r['id'] ?>)"><?= e($el) ?></button>
              <?php endforeach; ?>
              <span class="mlockmsg">primero el contacto (si hablaron)</span></div>
            <div class="marea lock" data-area="postura"><span class="man">3 · ¿Cómo lo ves?</span>
              <?php foreach ($MESA_AREAS['postura'] as $ek => $el): if ($ek === 'descartada') continue; ?>
              <button type="button" data-e="<?= $ek ?>" class="mpill" onclick="mesaSel(this,<?= (int)$r['id'] ?>)"><?= e($el) ?></button>
              <?php endforeach; ?>
              <button type="button" data-e="descartada" class="mpill mdesc" onclick="mesaRz(this)">Descartar</button>
              <span class="mrz">
                <span class="mrz-l">¿motivo?</span>
                <?php foreach (['precio' => 'Muy caro', 'competencia' => 'Se fue con otro', 'despues' => 'Lo dejó para después', 'no_responde' => 'Dejó de responder', 'no_comprador' => 'No era comprador', 'otro' => 'Otro'] as $rk => $rl): ?>
                <button type="button" data-e="descartada" data-rz="<?= $rk ?>" class="mpill mrz-b" onclick="mesaSel(this,<?= (int)$r['id'] ?>)"><?= e($rl) ?></button>
                <?php endforeach; ?>
              </span>
              <span class="mlockmsg">primero el paso anterior</span></div>
          </div>
          <div class="mcap">
            <button type="button" class="mcapbtn" id="mcap<?= (int)$r['id'] ?>" onclick="mesaCapturar(<?= (int)$r['id'] ?>)" disabled>Capturar seguimiento</button>
            <span class="mcaphint" id="mcaph<?= (int)$r['id'] ?>">Marca al menos el contacto</span>
          </div>
        </div>
      </div>
      <div class="magenda">
        <span class="man">📅 Agendar</span>
        <?php if (!empty($r['agenda_fecha'])): ?>
        <span class="magcur">Agendada para <b><?= e(date('d/m/Y', strtotime($r['agenda_fecha']))) ?></b></span>
        <button type="button" class="mpill" onclick="mesaDesagendar(<?= (int)$r['id'] ?>,this)">Traer a hoy</button>
        <span class="maghint">El cliente pidió seguimiento para ~esa fecha — ya reapareció en tu mesa.</span>
        <?php else: ?>
        <input type="date" class="magin" id="mag<?= (int)$r['id'] ?>" min="<?= $mag_min ?>" max="<?= $mag_max ?>">
        <button type="button" class="mpill" onclick="mesaAgendar(<?= (int)$r['id'] ?>,this)">Guardar fecha</button>
        <span class="maghint">Para clientes que compran más adelante (obra, entrega, presupuesto futuro): mínimo 15 días, máximo 6 meses. La saco de tu mesa y no te penaliza; vuelve sola 7 días antes de la fecha.</span>
        <?php endif; ?>
      </div>
    </div>
    <?php
};
?>
<?php
// ── Bloque por asesor: franja expandible dentro de su tarjeta del ranking ──
$MESA_BLOQUES = [];
foreach ($mesa_all as $mesa_vid => $mesa):
    $mr = $mesa['resumen'];
    // Frías (viejas ya trabajadas) van a su propia sección — fuera de la principal.
    $mesa_frias = array_values(array_filter($mesa['rows'], fn($r) => !empty($r['es_fria'])));
    $mesa_desc = array_values(array_filter($mesa['rows'], fn($r) => empty($r['es_fria']) && $r['cat'] === 'descartada_hoy'));
    $mesa_pend = array_values(array_filter($mesa['rows'], fn($r) => empty($r['es_fria']) && empty($r['atendida_hoy']) && $r['cat'] !== 'descartada_hoy'));
    $mesa_aten = array_values(array_filter($mesa['rows'], fn($r) => empty($r['es_fria']) && !empty($r['atendida_hoy']) && $r['cat'] !== 'descartada_hoy'));
    // Pendientes se parte en dos, ALINEADO al score: cubierta = feedback 👍👎 +
    // postura (= atendida de cobertura). "Por trabajar" = tus fallas (súbelas);
    // "En seguimiento" = ya cuentan, solo hay que nutrirlas hasta que cierren.
    $es_cov    = fn($r) => (($r['postura'] ?? null) !== null) && !empty($r['decl']['postura'] ?? null);
    $mesa_sin  = array_values(array_filter($mesa_pend, fn($r) => !$es_cov($r)));
    $mesa_seg  = array_values(array_filter($mesa_pend, $es_cov));
    ob_start();
?>
<details class="mesa-emb mesa-strip" id="mesa-emb-<?= (int)$mesa_vid ?>" <?= isset($_GET['mesa_uid']) && (int)$_GET['mesa_uid'] === (int)$mesa_vid ? 'open' : '' ?>>
  <summary class="mstrip">
    <span style="font-weight:800;color:#3f3f3a">📋 Mesa de trabajo</span>
    <span style="color:#a8a8a2;font-size:11.5px"><?= e($mesa_nombres[$mesa_vid] ?? '') ?></span>
    <span>
      <?php // Agendadas del título = parqueadas a futuro + las que ya reaparecieron
            // (cat='agendada', vueltas a la mesa) — así el contador refleja TODAS
            // las que agendaste, no solo las que están fuera.
            $mesa_ag_reap = count(array_filter($mesa['rows'], fn($r) => ($r['cat'] ?? '') === 'agendada'));
            $mesa_ag_park = count($mesa['agendadas']);
            $mesa_ag_n = $mesa_ag_park + $mesa_ag_reap; $mesa_fr_n = (int)($mr['frias'] ?? 0);
            $n_sin = count($mesa_sin); $n_seg = count($mesa_seg); ?>
      <?php if ($n_sin > 0 || $n_seg > 0 || !empty($mr['atendidas']) || !empty($mr['descartadas']) || $mesa_ag_n > 0 || $mesa_fr_n > 0): ?>
        <?php if ($n_sin > 0): ?><b style="color:#b45309"><?= $n_sin ?></b> por trabajar<?php else: ?><span style="color:#16a34a;font-weight:700">✓ por trabajar en cero</span><?php endif; ?><?php if ($n_seg > 0): ?> · <b><?= $n_seg ?></b> en seguimiento<?php endif; ?> · <b><?= $mmoney($mr['monto']) ?></b> en juego<?php
          if (($mr['universo'] ?? 0) > count($mesa['rows'])): ?> <span style="color:#a8a8a2">(top <?= count($mesa['rows']) ?> de <?= (int)$mr['universo'] ?>)</span><?php endif; ?>
        <?php if (!empty($mr['atendidas'])): ?>
          · <span style="color:#16a34a;font-weight:700">✓ <?= (int)$mr['atendidas'] ?> atendida<?= $mr['atendidas'] > 1 ? 's' : '' ?> hoy</span>
        <?php endif; ?>
        <?php if (!empty($mr['vencidas'])): ?>
          · <span style="color:#dc2626;font-weight:700" title="Filas que pasaron su límite de seguimiento — un contacto declarado las pone al corriente; si es una CITA, registra el desenlace o re-cítala tras hablar">⏰ <?= (int)$mr['vencidas'] ?> sin seguimiento</span>
        <?php endif; ?>
        <?php if (!empty($mr['descartadas'])): ?>
          · <span style="color:#b91c1c;font-weight:700">🗑 <?= (int)$mr['descartadas'] ?> descartada<?= $mr['descartadas'] > 1 ? 's' : '' ?> hoy</span>
        <?php endif; ?>
        <?php if ($mesa_ag_n > 0): ?>
          · <span style="color:#1d4ed8;font-weight:700">📅 <?= $mesa_ag_n ?> agendada<?= $mesa_ag_n > 1 ? 's' : '' ?><?php
            if ($mesa_ag_reap > 0): ?> (<?= $mesa_ag_reap ?> ya <?= $mesa_ag_reap > 1 ? 'volvieron' : 'volvió' ?>)<?php endif; ?></span>
        <?php endif; ?>
        <?php if ($mesa_fr_n > 0): ?>
          · <span style="color:#0369a1;font-weight:700">❄️ <?= $mesa_fr_n ?> fría<?= $mesa_fr_n > 1 ? 's' : '' ?></span>
        <?php endif; ?>
      <?php else: ?>
        <span style="color:#16a34a;font-weight:700">✓ al corriente</span>
      <?php endif; ?>
    </span>
    <span style="margin-left:auto;color:#a8a8a2;font-size:11px">tap para expandir ▾</span>
  </summary>
  <div class="mstrip-body">
    <?php if (!$mesa['rows']): ?>
      <div style="color:#16a34a;padding:12px 0;font-weight:600">✓ Sin filas accionables hoy — la mesa solo muestra lo trabajable
        (con señal del cliente o dentro de ventana). La cartera completa, incluidas las que nadie ha tocado, está en el 📊 Reporte.</div>
    <?php else: ?>

      <?php
        // ── "Mesa de hoy" (idea 2 del mockup): fecha + contadores + por dónde
        //    empezar. Los contadores reusan lo ya calculado ($mesa_sin/$mesa_seg/
        //    $mr) — cero queries nuevas. "Empieza por" = primera fila pendiente
        //    (el orden ya viene priorizado de Mesa::armar: vencidas primero).
        $mday_dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
        $mday_meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $mday_txt   = $mday_dias[(int)date('w')] . ' ' . (int)date('j') . ' ' . $mday_meses[(int)date('n') - 1];
        $mday_first = $mesa_sin[0] ?? ($mesa_seg[0] ?? null);
      ?>
      <div class="mday">
        <div class="mday-top">
          <span class="mday-t">Mesa de hoy</span><span class="mday-f"><?= e($mday_txt) ?></span>
          <span class="mday-chips">
            <?php if ($n_sin > 0): ?><span class="mday-c warn"><b><?= $n_sin ?></b> por trabajar</span><?php endif; ?>
            <?php if ($n_seg > 0): ?><span class="mday-c ok2"><b><?= $n_seg ?></b> en seguimiento</span><?php endif; ?>
            <?php if (!empty($mr['vencidas'])): ?><span class="mday-c bad">⏰ <b><?= (int)$mr['vencidas'] ?></b> sin seguimiento</span><?php endif; ?>
            <?php if (!empty($mr['atendidas'])): ?><span class="mday-c ok">✓ <b><?= (int)$mr['atendidas'] ?></b> atendida<?= $mr['atendidas'] > 1 ? 's' : '' ?></span><?php endif; ?>
          </span>
        </div>
        <?php if ($mday_first): ?>
        <?php // Solo el NOMBRE — la sugerencia ya se ve en la fila #1 justo abajo
              // (y en su cajón al abrir); repetirla aquí triplicaba el texto. ?>
        <div class="mday-start">Empieza por <b><?= e($mday_first['titulo'] ?: $mday_first['cliente']) ?></b> — es la #1 de tu lista</div>
        <?php endif; ?>
      </div>

      <?php if ($mesa_sin || $mesa_seg): ?>
      <div class="mhead">
        <span class="mh-rank"></span><span class="mh-dot"></span><span class="mh-cot">Cotización</span><span class="mh-flag"></span><span class="mh-check"></span>
        <span class="mh-ciclo">Ciclo</span><span class="mh-money">Monto</span>
        <span class="mh-decl"><span class="s1">Contacto</span><span class="s2">Compromiso</span><span class="s3">Cómo lo ves</span></span>
        <span class="mh-marc">Feedback<br>Radar</span>
        <span class="mh-fresh" title="Tu frescura y el límite de seguimiento de cada cotización — pasarse del límite descuenta puntos del termómetro">Actividad (cuida tu score)</span><span class="msp"></span><span class="mh-chev"></span>
      </div>
      <?php endif; ?>

      <?php $mesa_rank = 0; ?>
      <?php if ($mesa_sin): ?>
      <div class="msect" style="color:#b45309">🔴 Por trabajar (<?= count($mesa_sin) ?>) — dales feedback 👍👎 + postura; estas son las que te faltan</div>
      <div class="mlist"><?php foreach ($mesa_sin as $r) $mesa_row($r, ++$mesa_rank, true); ?></div>
      <?php endif; ?>

      <?php if ($mesa_seg): ?>
      <div class="msect" style="color:#15803d">🌱 En seguimiento (<?= count($mesa_seg) ?>) — ya calificadas; nútrelas hasta que cierren</div>
      <div class="mlist"><?php foreach ($mesa_seg as $r) $mesa_row($r, ++$mesa_rank, true); ?></div>
      <?php endif; ?>

      <?php if (!$mesa_sin && !$mesa_seg): ?>
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

    <?php if ($mesa_frias): ?>
    <div class="msect" style="color:#0369a1">❄️ Frías (<?= count($mesa_frias) ?>) — ya trabajadas y fuera de tu ventana; descansan aquí sin quitarte el foco hasta el día <?= 2 * $mp75 ?> (2× tu ventana) y luego salen solas</div>
    <div class="mlist mfrias-zone"><?php foreach ($mesa_frias as $r) $mesa_row($r); ?></div>
    <?php endif; ?>

    <?php if (!empty($mesa['agendadas'])): ?>
    <div class="msect" style="color:#1d4ed8">📅 Agendadas (<?= count($mesa['agendadas']) ?>) — fuera de la mesa hasta 7 días antes de su fecha</div>
    <div class="maglist">
      <?php foreach ($mesa['agendadas'] as $ag): ?>
      <div class="magrow">
        <a href="/cotizaciones/<?= (int)$ag['id'] ?>"><?= e($ag['titulo'] ?: $ag['cliente']) ?></a>
        <span class="magfolio"><?= e($ag['numero']) ?><?= $ag['cliente'] && $ag['titulo'] ? ' · ' . e($ag['cliente']) : '' ?></span>
        <span class="magf">para <b><?= e(date('d/m/Y', strtotime($ag['fecha']))) ?></b> · en <?= (int)$ag['dias_para'] ?>d</span>
        <span class="magm"><?= $mmoney($ag['total']) ?></span>
        <button type="button" class="mpill" onclick="mesaDesagendar(<?= (int)$ag['id'] ?>,this)">Traer a hoy</button>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($mesa_es_admin && $mesa['limpieza']['n'] >= 10): ?>
    <div style="margin-top:12px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:12.5px;color:#7f1d1d">
      🗑 <b><?= (int)$mesa['limpieza']['n'] ?></b> cotizaciones (<?= $mmoney($mesa['limpieza']['monto']) ?>) tienen más de
      <b><?= (int)$mesa['limpieza']['linea_dias'] ?> días</b> — tu empresa jamás ha cerrado una de esa edad.
      Ya no son pipeline, son ruido. <span style="color:#9a3412">(Suspensión en lote: próxima versión.)</span>
    </div>
    <?php endif; ?>


  </div>
</details>
<?php $MESA_BLOQUES[(int)$mesa_vid] = ob_get_clean(); endforeach; ?>

<?php // ── Piezas reutilizables (admin y asesor) ──────────── ?>
<?php ob_start(); ?>
<?php $mc = $mesa_first['ciclo']; ?>
    <div style="font-size:12px;color:#6a6a64;margin-bottom:10px">
      <?php if (!empty($mc['auto'])): ?>
      Ciclo real de la empresa: la mitad de tus ventas cierra en <b><?= (int)$mc['mediana'] ?>d</b>,
      el 75% antes del día <b><?= (int)$mc['p75'] ?></b>.
      <?php endif; ?>
      Cada cotización vive en la mesa hasta el día <b><?= 2 * $mp75 ?></b> (2× tu ventana) — pasada tu ventana
      el consejo pide definición, no seguimiento eterno.<?php if ($mesa_es_admin): ?> El aviso de limpieza corre en el día
      <b><?= (int)$mesa_first['limpieza']['linea_dias'] ?></b> (tu cierre más tardío registrado o 2× tu ventana, lo que sea mayor).<?php endif; ?> Tapea una fila para trabajarla y actualízala en cada toque.
      <span style="white-space:nowrap;margin-left:6px">
        <span class="mleg" style="background:#dc2626"></span>caliente
        <span class="mleg" style="background:#d97706"></span>actividad reciente
        <span class="mleg" style="background:#94a3b8"></span>enfriándose
        <span class="mleg off"></span>sin señal
      </span>
      <a href="#" onclick="event.preventDefault();var p=document.getElementById('mesa-pb');p.style.display=p.style.display==='none'?'block':'none'"
         style="margin-left:10px;color:#1a5c38;font-weight:700;text-decoration:none;white-space:nowrap">📖 ¿Cómo funciona?</a>
      <?php if ($mesa_es_admin): // el reporte del equipo es SOLO admin ?>
      <?php if (isset($_GET['mesa_dias'])): ?>
      <a href="#" onclick="event.preventDefault();var p=document.getElementById('mesa-rp');p.style.display=p.style.display==='none'?'block':'none'"
         style="margin-left:8px;color:#1a5c38;font-weight:700;text-decoration:none;white-space:nowrap">📊 Reporte del equipo</a>
      <?php else: ?>
      <a href="?<?= e(http_build_query(array_merge($_GET, ['mesa_dias' => 30]))) ?>#mesa-shared"
         style="margin-left:8px;color:#1a5c38;font-weight:700;text-decoration:none;white-space:nowrap">📊 Reporte del equipo</a>
      <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php $MESA_CICLO = ob_get_clean(); ?>

<?php ob_start(); ?>
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
      descarte); después de ese día salen solas — salvo las que se vuelven a calentar (⚡ milagros), que se
      quedan mientras tengan señal viva — y, pasado tu récord histórico, se cuentan como ruido en el
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
      <p style="margin:0 0 8px"><b>💰 Recuperado.</b> Suma las ventas del período elegido (<?= (int)$mrec['dias'] ?> días) cuya cotización
      YA estaba descartada (👎 o "Descartar") antes de cerrarse. Ese dinero se daba por muerto y aun así se
      cerró — sea porque el Radar te la regresó con ⚡ o porque alguien la retomó por su lado; el dato duro es
      solo ese: descartada antes, vendida después. <?= $mesa_es_admin
        ? 'Es de toda la empresa. "Cerrado tras trabajarse aquí" suma las demás ventas que pasaron por la mesa (con capturas) antes de cerrar.'
        : 'Es TU dinero: cotizaciones tuyas que se daban por muertas y aun así cerraste.' ?></p>
      <p style="margin:0 0 8px"><b>Lo que declaras se coteja solo.</b> La mesa no califica lo que
      declaras — califica que trabajes tus señales. Un "quedamos en algo" se confirma si el cliente
      se mueve en 5 días, y un descarte que el cliente desmiente reabriéndola cuenta en tu contra.
      Declarar lo que de verdad pasó siempre te da mejor consejo — y mejor score — que declarar lo
      que se ve bien. Y las fallas no son eternas: una cotización vieja que YA trabajaste
      (manita + postura) se va a ❄️ Frías y deja de contar; la que ignoras por completo sigue
      contando en tu contra hasta que la trabajes o la descartes.</p>
      <p style="margin:0 0 8px"><b>📅 Agendar.</b> ¿El cliente compra más adelante? (le entregan la casa en 2 meses,
      arranca la obra en marzo, tiene el presupuesto el próximo trimestre). Abre la fila y ponle una fecha: la cotización
      <b>sale de tu mesa y deja de contar</b> mientras tanto — no te penaliza por no tocarla. Vuelve sola a tu mesa
      <b>7 días antes</b> de la fecha, ya re-anclada, para que la retomes a tiempo. La fecha va de <b>15 días</b> a
      <b>6 meses</b> (menos de 15 no es agenda, es posponer el seguimiento), y no puedes reagendar la misma dentro de
      15 días (para que no sea un botón de "esconder"). Mientras está agendada la ves en la bandeja
      <b>📅 Agendadas</b> y puedes traerla a hoy cuando quieras.</p>
      <p style="margin:0 0 8px"><b>🔴 Por trabajar vs 🌱 En seguimiento.</b> Tu lista se parte en dos:
      <b>Por trabajar</b> son las que aún NO tienen feedback 👍👎 + postura — es tu pendiente real y lo que
      te falta para el score; empieza por ahí. <b>En seguimiento</b> son las que ya calificaste: cuentan a tu
      favor y solo hay que nutrirlas hasta que cierren. No las re-trabajes de gancho todos los días.</p>
      <p style="margin:0 0 8px"><b>⏱ La columna "Actividad" te marca el ritmo.</b> Cada cotización trae su
      <b>límite de seguimiento</b>: si el cliente no contesta, el siguiente intento va a los <b>2 días</b>; en lo demás,
      a la mitad de tu ciclo de venta. Pasarte del límite la pone <b>🔴 sin seguimiento</b> y cada día acumula
      descuento de puntos a tu termómetro. Te pones al corriente con un <b>contacto declarado</b> (Hablamos / No contestó);
      la <b>cita</b> es aparte: solo se resuelve registrando el desenlace o re-citando tras hablar. El ⏰ es tu huella:
      los días vencidos que ya acumulaste — no se borran al tocar, se drenan solos con los días.
      <b>¿Todo sigue igual con el cliente? Re-tapea "Hablamos" (o "No contestó") después de cada toque, aunque
      la pill ya esté en verde</b> — cada tap se guarda con su fecha y ESO resetea tu límite. Tu postura y tu
      manita no se tocan si no cambió nada: repetir tu opinión no es seguimiento, hablar con el cliente sí.</p>
      <p style="margin:0 0 8px"><b>¿Qué pasa mañana con lo de hoy?</b> Las que trabajaste hoy vuelven a
      <b>En seguimiento</b> (con "hace 1d", frescas) — NO se castigan, conservan su calificación y siguen contando.
      Un trato no se cierra por tocarlo una vez; se nutre hasta que cae. Las que crucen tu ventana ya trabajadas
      pasan solas a <b>❄️ Frías</b>.</p>
      <p style="margin:0"><b>✓ Atendidas hoy.</b> Lo que trabajas hoy Y calificas (manita 👍👎/📵 + postura) baja a su propia sección al recargar.
      La meta del día es simple: dejar el "Por trabajar" en cero.</p>
    </div>

    <?php $MESA_PLAYBOOK = ob_get_clean(); ?>

<?php ob_start(); ?>
<style>
.mesa-emb.mesa-strip{border-top:1px dashed #e2e2dc;background:#fdfdfb}
.mesa-emb .mstrip{cursor:pointer;list-style:none;display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:8px 14px 8px 52px;font-size:12.5px;color:#4a4a46}
.mesa-emb .mstrip::-webkit-details-marker{display:none}
.mesa-emb .mstrip:hover{background:#f7f7f2}
.mesa-emb .mstrip-body{padding:4px 16px 14px 52px}
#mesa-shared{padding:10px 16px 4px;border-bottom:1px solid #eeeee9}
@media (max-width:640px){.mesa-emb .mstrip{padding-left:14px}.mesa-emb .mstrip-body{padding-left:14px}}
.mesa-emb .mlist{border:1px solid #eeeee9;border-radius:10px;overflow:hidden}
.mesa-emb .mhead{margin-bottom:5px}
.mesa-emb .mleg{display:inline-block;width:8px;height:8px;border-radius:50%;margin:0 4px 0 10px;vertical-align:baseline}
.mesa-emb .mleg.off{background:transparent;border:1.5px solid #c9c9c2;width:7px;height:7px}
.mesa-emb .mvolvio{display:block;font-size:10.5px;color:#92400e;font-weight:600}
.mesa-emb .mrow{display:flex;align-items:center;gap:10px;padding:2px 12px;min-height:38px;cursor:pointer;background:#fafaf8}
.mesa-emb .mrow + .mdrawer + .mrow, .mesa-emb .mrow + .mrow{border-top:1px solid #eeeee9}
.mesa-emb .mdrawer + .mrow{border-top:1px solid #eeeee9}
.mesa-emb .mrow:hover{background:#f4f4ef}
.mesa-emb .mrow.open{background:#fff}
.mesa-emb .mrow.milagro{background:#fefce8}
.mesa-emb .mrow.done{opacity:.78}
.mesa-emb .mdone-zone .mrow{opacity:.72}
.mesa-emb .mfrias-zone{border-color:#dbeafe}
.mesa-emb .mfrias-zone .mrow{opacity:.66;background:#f7fafd}
.mesa-emb .mdot{width:9px;height:9px;border-radius:50%;flex:none}
.mesa-emb .mdot.off{background:transparent;border:1.5px solid #c9c9c2}
.mesa-emb .mcli{font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0;flex:1 1 380px;max-width:560px}
.mesa-emb .mcli a{color:#1a1a18;text-decoration:none}
.mesa-emb .mcli a:hover{color:#1a5c38;text-decoration:underline}
.mesa-emb .mfolio{font-weight:500;color:#a3a39d;font-size:11px;margin-left:6px}
.mesa-emb .mflag{font-size:11px;flex:none;width:18px;text-align:center}
.mesa-emb .mcheck{color:#16a34a;font-weight:800;flex:none;width:16px;text-align:center}

.mesa-emb .mciclo{font-size:12px;color:#57534e;font-variant-numeric:tabular-nums;flex:none;width:92px;text-align:right;white-space:nowrap}
.mesa-emb .mciclo.late{color:#dc2626;font-weight:700}
.mesa-emb .mmoney{font-weight:700;font-variant-numeric:tabular-nums;flex:none;width:82px;text-align:right}
.mesa-emb .mdecl3{display:flex;gap:6px;flex:1 1 254px;max-width:430px;min-width:0}
.mesa-emb .mdecl3 span{font-size:10.5px;line-height:1.3;color:#c9c9c2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mesa-emb .mdecl3 .s1,.mesa-emb .mdecl3 .s2,.mesa-emb .mdecl3 .s3{flex:1 1 0;min-width:0}
.mesa-emb .mdecl3 span.f{color:#1a5c38;font-weight:700}
.mesa-emb .mmarc{flex:none;width:74px;display:flex;gap:2px;justify-content:center}
.mesa-emb .mmarc .fbi{border:none;background:none;cursor:pointer;font-size:13px;line-height:1;padding:3px 4px;filter:grayscale(1);opacity:.4;transition:all .12s}
.mesa-emb .mmarc .fbi:hover{filter:none;opacity:.8}
.mesa-emb .mmarc .fbi.on{filter:none;opacity:1}
.mesa-emb .mfresh{font-size:11px;flex:1 1 190px;min-width:130px;max-width:320px;text-align:center;color:#a8a8a2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mesa-emb .mfresh.warn{color:#d97706;font-weight:700}
.mesa-emb .mfresh.bad{color:#dc2626;font-weight:700}
.mesa-emb .mfresh.ok{color:#16a34a;font-weight:700}
.mesa-emb .msp{flex:none}
.mesa-emb .mchev{color:#c9c9c2;flex:none;font-size:11px;transition:transform .15s}
.mesa-emb .mrow.open .mchev{transform:rotate(90deg)}
.mesa-emb .mdrawer{display:none;background:#fff;padding:12px 14px 14px 31px;border-top:1px solid #f4f4ef}
.mesa-emb .mdrawer.open{display:block}
.mesa-emb .mtag{font-size:11px;padding:2px 7px;border-radius:9px;font-weight:700;margin-right:6px}
.mesa-emb .msug{font-size:13px;color:#3f3f3a;margin-bottom:10px}
.mesa-emb .mlbl{color:#1a5c38;font-weight:800;margin-right:4px}
.mesa-emb .mareas{display:flex;flex-direction:column;gap:7px}
.mesa-emb .marea{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
.mesa-emb .man{font-size:10.5px;font-weight:800;color:#8a8a84;text-transform:uppercase;letter-spacing:.05em;width:132px;flex:none;white-space:nowrap}
.mesa-emb .mpill{border:1px solid #e2e2dc;background:#fafaf8;color:#57534e;border-radius:999px;padding:4px 12px;cursor:pointer;font:600 11.5px 'Plus Jakarta Sans',system-ui,sans-serif;white-space:nowrap;line-height:1.4;transition:all .12s}
.mesa-emb .mpill:hover{border-color:#1a5c38;color:#1a5c38;background:#fff}
.mesa-emb .mpill.on{background:#1a5c38;border-color:#1a5c38;color:#fff}
.mesa-emb .mpill:disabled{opacity:.5}
.mesa-emb .mdesc{border-color:#fecaca;color:#b91c1c;background:#fff}
.mesa-emb .mdesc.on{background:#b91c1c;border-color:#b91c1c;color:#fff}
.mesa-emb .mrz{display:none;align-items:baseline;gap:4px;flex-wrap:wrap;margin-left:8px;padding-left:10px;border-left:2px solid #fecaca}
.mesa-emb .mrz.show{display:inline-flex}
.mesa-emb .mrz-l{font-size:10.5px;color:#b91c1c;font-weight:800}
.mesa-emb .mrz-b{border-color:#fecaca}
.mesa-emb .mrz-b:hover{border-color:#b91c1c;color:#b91c1c}
.mesa-emb .mrz-b.on{background:#b91c1c;border-color:#b91c1c;color:#fff}
.mesa-emb .marea .mlockmsg{display:none;font-size:10.5px;color:#a8a8a2;font-style:italic}
.mesa-emb .marea.lock .mpill{opacity:.35;pointer-events:none}
.mesa-emb .marea.lock .mlockmsg{display:inline}
/* ── Cajón en 2 columnas: izquierda declarado (display) / derecha capturar ── */
.mesa-emb .mdrawer-cols{display:grid;grid-template-columns:1.15fr 1fr;gap:14px;margin:8px 0 4px}
.mesa-emb .mcol-h{font-size:10.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#a8a8a2;margin-bottom:8px}
/* invertido: capturar a la IZQUIERDA (order 1), declarado a la DERECHA (order 2) */
.mesa-emb .mcol-cap{order:1}
.mesa-emb .mcol-info{order:2;border-left:2px solid #e6efe9;padding-left:14px}
.mesa-emb .mdecl{display:flex;flex-direction:column;gap:7px}
.mesa-emb .mdln{display:grid;grid-template-columns:auto 1fr auto;gap:8px;align-items:baseline;font-size:12px}
.mesa-emb .mdk{color:#a8a8a2;font-weight:700;white-space:nowrap}
.mesa-emb .mdv{font-weight:700;color:#292524}
.mesa-emb .mdv.none{color:#c4c4bd;font-weight:600;font-style:italic}
.mesa-emb .mdd{color:#1a5c38;font-weight:700;font-variant-numeric:tabular-nums;white-space:nowrap}
.mesa-emb .mhist{margin-top:10px;border-top:1px dashed #e6e6df;padding-top:8px}
.mesa-emb .mhist-t{background:none;border:0;cursor:pointer;font:700 11px 'Plus Jakarta Sans',system-ui,sans-serif;color:#a8a8a2;padding:0;display:flex;align-items:center;gap:6px}
.mesa-emb .mhist-t:hover{color:#1a5c38}
.mesa-emb .mhist-t .chev{font-size:9px;transition:transform .15s}
.mesa-emb .mhist.open .mhist-t .chev{transform:rotate(90deg)}
.mesa-emb .mhist-b{display:none;margin-top:6px}
.mesa-emb .mhist.open .mhist-b{display:block}
.mesa-emb .mhist-i{display:flex;justify-content:space-between;gap:10px;padding:2px 0;font-size:11.5px}
.mesa-emb .mhist-i .mho{font-weight:600;color:#44403c}
.mesa-emb .mhist-i .mhd{font-variant-numeric:tabular-nums;color:#a8a8a2}
.mesa-emb .mpill.sel{background:#1a5c38;border-color:#1a5c38;color:#fff}
.mesa-emb .mdesc.sel,.mesa-emb .mrz-b.sel{background:#b91c1c;border-color:#b91c1c;color:#fff}
.mesa-emb .mcap{margin-top:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.mesa-emb .mcapbtn{border:0;background:#1a5c38;color:#fff;border-radius:8px;padding:8px 15px;cursor:pointer;font:750 12.5px 'Plus Jakarta Sans',system-ui,sans-serif}
.mesa-emb .mcapbtn:hover{background:#22a05a}
.mesa-emb .mcapbtn:disabled{background:#dcdcd5;color:#a8a8a2;cursor:not-allowed}
.mesa-emb .mcaphint{font-size:11px;color:#a8a8a2}
/* ── Intentos de contacto + suspender asistido (ghost) ── */
.mesa-emb .mintentos{margin:10px 0 4px;padding:10px 12px;border:1px solid #f0e6d6;background:#fdf9f0;border-radius:10px}
.mesa-emb .mintentos.full{border-color:#f3c9c4;background:#fdf3f2}
.mesa-emb .mint-top{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.mesa-emb .mint-h{font-size:10.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#a8a8a2}
.mesa-emb .mint-n{font-size:12.5px;font-weight:800;color:#b45309}
.mesa-emb .mintentos.full .mint-n{color:#b91c1c}
.mesa-emb .mint-beads{display:inline-flex;gap:4px}
.mesa-emb .mint-b{width:9px;height:9px;border-radius:50%;background:#d97706}
.mesa-emb .mint-b.off{background:#e2ddd2}
.mesa-emb .mint-b.susp{background:#dc2626}
.mesa-emb .mint-b.susp.off{background:#e9d3d0}
.mesa-emb .mint-msg{font-size:12px;color:#57534e;margin-top:8px;line-height:1.5}
.mesa-emb .mint-msg.mut{color:#a8a8a2}
.mesa-emb .mint-susp{margin-top:10px;border:0;background:#dc2626;color:#fff;border-radius:8px;padding:8px 15px;cursor:pointer;font:800 12.5px 'Plus Jakarta Sans',system-ui,sans-serif}
.mesa-emb .mint-susp:hover{background:#b91c1c}
/* ── Mesa de hoy (encabezado con fecha + contadores + por dónde empezar) ── */
.mesa-emb .mday{border:1px solid #eeeee9;background:#fafaf8;border-radius:10px;padding:10px 14px;margin:2px 0 12px}
.mesa-emb .mday-top{display:flex;align-items:baseline;gap:9px;flex-wrap:wrap}
.mesa-emb .mday-t{font-weight:800;font-size:13.5px;color:#3f3f3a}
.mesa-emb .mday-f{font-size:12px;color:#a8a8a2;font-weight:600}
.mesa-emb .mday-chips{display:inline-flex;gap:7px;flex-wrap:wrap;margin-left:auto}
.mesa-emb .mday-c{font-size:11.5px;font-weight:650;padding:2px 9px;border-radius:999px;background:#f1f1ec;color:#57534e;white-space:nowrap}
.mesa-emb .mday-c.warn{background:#fef6e7;color:#b45309}
.mesa-emb .mday-c.ok2{background:#e9f5ee;color:#15803d}
.mesa-emb .mday-c.bad{background:#fef2f2;color:#dc2626}
.mesa-emb .mday-c.ok{background:#e9f5ee;color:#16a34a}
.mesa-emb .mday-start{margin-top:8px;padding:7px 10px;background:#fff;border-left:3px solid #1a5c38;border-radius:6px;font-size:12.5px;color:#44403c;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
/* ── Orden de ataque (número) + línea "→ qué hacer" en fila colapsada ── */
.mesa-emb .mrank{width:20px;height:20px;border-radius:50%;background:#1a5c38;color:#fff;font-size:11px;font-weight:800;display:grid;place-items:center;flex:none}
.mesa-emb .mrank.off{background:transparent}
.mesa-emb .mhead .mh-rank{flex:none;width:20px}
.mesa-emb .mrow.done .mrank{background:#c9c9c2}
.mesa-emb .mrow.has-ql{flex-wrap:wrap;padding-bottom:7px}
/* cajón abierto ya muestra la sugerencia (msug) — la línea colapsada sobra */
.mesa-emb .mrow.open .mql{display:none}
.mesa-emb .mrow.open.has-ql{padding-bottom:2px}
/* tip: texto completo y legible (es herramienta de trabajo — decisión CEO:
   sin píldora, sin recorte, sin alto extra). Solo una rayita izquierda sutil
   para separarlo visualmente del renglón de datos, a costo cero de espacio. */
.mesa-emb .mql{flex:1 1 100%;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#78716c;margin:-3px 0 0 49px;border-left:2px solid #e4e4dc;padding-left:8px}
@media (max-width:640px){.mesa-emb .mql{padding-left:0}.mesa-emb .mday-chips{margin-left:0}}
@media (max-width:620px){
  .mesa-emb .mdrawer-cols{grid-template-columns:1fr}
  /* capturar (order 1) arriba, declarado (order 2) abajo con su divisor */
  .mesa-emb .mcol-info{border-left:0;border-top:2px solid #e6efe9;padding-left:0;padding-top:12px}
}
.mesa-emb .mhead{display:flex;align-items:center;gap:10px;padding:0 12px;font-size:10px;font-weight:800;color:#a8a8a2;text-transform:uppercase;letter-spacing:.05em}
.mesa-emb .mhead .mh-dot{flex:none;width:9px}
.mesa-emb .mhead .mh-cot{flex:1 1 380px;max-width:560px;min-width:0}
.mesa-emb .mhead .mh-flag{flex:none;width:18px}
.mesa-emb .mhead .mh-check{flex:none;width:16px}
.mesa-emb .mhead .mh-ciclo{flex:none;width:92px;text-align:right}
.mesa-emb .mhead .mh-money{flex:none;width:82px;text-align:right}
.mesa-emb .mhead .mh-decl{display:flex;gap:6px;flex:1 1 254px;max-width:430px;min-width:0}
.mesa-emb .mhead .mh-decl .s1,.mesa-emb .mhead .mh-decl .s2,.mesa-emb .mhead .mh-decl .s3{flex:1 1 0;min-width:0}
.mesa-emb .mhead .mh-decl span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mesa-emb .mhead .mh-marc{flex:none;width:74px;text-align:center;line-height:1.2}
.mesa-emb .mhead .mh-fresh{flex:1 1 190px;min-width:130px;max-width:320px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mesa-emb .mhead .mh-chev{flex:none;width:11px}
.mesa-emb .msect{margin-top:14px;margin-bottom:6px;font-size:11px;color:#16a34a;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.mesa-emb .magenda{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:11px;padding-top:11px;border-top:1px dashed #eeeee9}
.mesa-emb .magin{border:1px solid #e2e2dc;border-radius:8px;padding:4px 9px;font:600 12px 'Plus Jakarta Sans',system-ui,sans-serif;color:#3f3f3a;background:#fff}
.mesa-emb .maghint{font-size:10.5px;color:#a8a8a2;font-style:italic;flex:1 1 100%;line-height:1.45}
.mesa-emb .magcur{font-size:12px;color:#1d4ed8;font-weight:600}
.mesa-emb .maglist{border:1px solid #dbeafe;border-radius:10px;overflow:hidden;background:#f8faff}
.mesa-emb .magrow{display:flex;align-items:center;gap:10px;padding:7px 12px;font-size:12.5px;border-top:1px solid #eef4ff}
.mesa-emb .magrow:first-child{border-top:none}
.mesa-emb .magrow a{color:#1a1a18;text-decoration:none;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1 1 220px;min-width:0}
.mesa-emb .magrow a:hover{color:#1a5c38;text-decoration:underline}
.mesa-emb .magfolio{color:#a3a39d;font-size:11px;white-space:nowrap}
.mesa-emb .magf{color:#1d4ed8;white-space:nowrap;font-size:11.5px}
.mesa-emb .magm{font-weight:700;font-variant-numeric:tabular-nums;margin-left:auto;white-space:nowrap}
@media (max-width:640px){.mesa-emb .magrow{flex-wrap:wrap;row-gap:3px}.mesa-emb .magfolio{display:none}.mesa-emb .magm{margin-left:0}}
#mesa-toast{position:fixed;left:50%;bottom:22px;transform:translateX(-50%);background:#1a1a18;color:#fff;font-size:12.5px;padding:9px 16px;border-radius:10px;opacity:0;pointer-events:none;transition:opacity .25s;z-index:9999}
#mesa-toast.show{opacity:.95}
@media (max-width:640px){
  /* El asesor usa teléfono: los 👍👎 NO se ocultan y la fila va en 2 líneas
     (nombre arriba; ciclo/monto/estado/pulgares abajo) */
  .mesa-emb .mfolio,.mesa-emb .mfresh{display:none}
  .mesa-emb .mhead{display:none}
  .mesa-emb .mrow{flex-wrap:wrap;row-gap:2px;padding-top:6px;padding-bottom:6px}
  .mesa-emb .mcli{flex:1 1 calc(100% - 70px);min-width:0}
  .mesa-emb .mdecl3 .s2,.mesa-emb .mdecl3 .s3{display:none}
  .mesa-emb .mdecl3{flex:none;max-width:none}
  .mesa-emb .mdecl3 .s1{flex:none;width:64px}
  .mesa-emb .mmoney{width:auto}
  .mesa-emb .mmarc{width:auto}
  .mesa-emb .mdrawer{padding-left:14px}
  .mesa-emb .man{width:100%}
  .mesa-emb .msp{flex:1}
}
</style>
<div id="mesa-toast"></div>
<script>
// Los bloques por asesor se emiten DESPUÉS de este script (dentro del
// ranking) — el binding va diferido a DOMContentLoaded.
document.addEventListener('DOMContentLoaded', function(){
  // Deep link ?mesa_uid=X: el details llega abierto — llevar la vista ahí
  var abierto = document.querySelector('details.mesa-emb.mesa-strip[open]');
  if (abierto && location.search.indexOf('mesa_uid=') !== -1) {
    abierto.scrollIntoView({block: 'start', behavior: 'smooth'});
  }
  document.querySelectorAll('.mesa-emb .mrow').forEach(function(row){
    row.addEventListener('click', function(){
      var d = document.getElementById(row.dataset.drawer);
      if(!d) return;
      var was = d.classList.contains('open');
      document.querySelectorAll('.mesa-emb .mdrawer').forEach(function(x){x.classList.remove('open')});
      document.querySelectorAll('.mesa-emb .mrow').forEach(function(x){x.classList.remove('open')});
      if(!was){ d.classList.add('open'); row.classList.add('open'); }
    });
  });
});

var mesaToastT;
function mesaToast(msg){
  var t = document.getElementById('mesa-toast');
  t.textContent = msg; t.classList.add('show');
  clearTimeout(mesaToastT); mesaToastT = setTimeout(function(){t.classList.remove('show')}, 2600);
}
// Códigos de error del API → mensajes humanos (no mostrar 'rate'/'cerrada' crudos)
var MESA_ERR = {rate:'Vas muy rápido — espera un momento e intenta de nuevo',
                cerrada:'Esta cotización ya está cerrada o suspendida',
                permiso:'No tienes permiso sobre esta cotización',
                mesa_off:'La mesa no está activa para tu empresa',
                sesion:'Tu sesión expiró — recarga la página',
                datos:'Datos inválidos', razon:'Falta la razón del descarte',
                no_encontrada:'No se encontró la cotización',
                fecha_invalida:'Fecha inválida',
                fecha_cerca:'La fecha debe ser de al menos 15 días',
                fecha_lejana:'Máximo 6 meses',
                sin_info_gate:'📵 es para clientes que no responden — marca primero "No contestó" en Contacto; si ya hablaron, califícalo 👍/👎.',
                guardar:'Error al guardar — intenta de nuevo'};
function mesaErr(code){ return MESA_ERR[code] || ('No se pudo guardar: ' + (code || 'error')); }

// ¿Bajaría a "Atendidas hoy" al recargar? Mismo criterio que Mesa::armar:
// calificada (manita+postura) + postura FRESCA de hoy + al corriente (reloj
// NO rojo). El color del reloj se lee del DOM ya renderizado — y arriba, en
// mesaTap, se actualiza a 'ok' si el tap fue un contacto. Evita prometer
// "Atendidas hoy" cuando la recarga la dejaría en "Por trabajar".
function mesaAtendida(row, d){
  var fr = row ? row.querySelector('.mfresh') : null;
  var enRojo = !!(fr && fr.classList.contains('bad'));
  return !!(d && d.calificada && d.postura_fresca && !enRojo);
}

// Feedback Radar desde la mesa — se guarda a nombre del asesor dueño de la
// cotización (una sola marca: el descarte voltea el 👍 a 👎 automáticamente)
function mesaFb(cotId, tipo, btn){
  // 📵 solo aplica a clientes que no responden: pre-check en el cliente (el
  // servidor re-valida) para explicar el candado sin viaje a la red
  if(tipo === 'sin_info'){
    var row0 = btn.closest('.mrow');
    var dr0 = row0 ? document.getElementById(row0.dataset.drawer) : null;
    var conOn = dr0 ? dr0.querySelector('.marea[data-area="contacto"] .mpill.on') : null;
    if(!conOn || conOn.dataset.e !== 'no_contesta'){
      mesaToast('📵 es para clientes que no responden — marca primero "No contestó" en Contacto; si ya hablaron, califícalo 👍/👎.');
      return;
    }
  }
  // Pulgares fuera durante el vuelo: dos taps rápidos serían dos fetch en
  // carrera y la marca final dependería del orden de commit, no del último tap
  var thumbs = btn.parentElement.querySelectorAll('.fbi');
  thumbs.forEach(function(b){ b.disabled = true; });
  fetch('/api/mesa/estado', {method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
    body: JSON.stringify({cotizacion_id:cotId, area:'feedback', estado:tipo})
  }).then(function(r){return r.json();}).then(function(d){
    thumbs.forEach(function(b){ b.disabled = false; });
    if(!d.ok){ mesaToast(mesaErr(d.error)); return; }
    btn.parentElement.querySelectorAll('.fbi').forEach(function(x){x.classList.remove('on')});
    btn.classList.add('on');
    // reflejar en la fila y actualizar el consejo recalculado por el API
    var row = btn.closest('.mrow');
    var drawer = row ? document.getElementById(row.dataset.drawer) : null;
    if(drawer && d.sugerencia){
      var sx = drawer.querySelector('.msx'); if(sx) sx.textContent = d.sugerencia;
    }
    if(row){ row.style.opacity = (tipo === 'sin_interes') ? '.72' : ''; }
    // La manita COMPLETA la calificación → ✓ SOLO si bajaría a "Atendidas hoy"
    // (calificada + postura fresca de hoy + al corriente). La manita NO refresca
    // la postura ni apaga el reloj: sobre una postura vieja o un reloj rojo, la
    // fila se queda en "Por trabajar" (igual que la recarga).
    var atendida = mesaAtendida(row, d);
    if(tipo !== 'sin_interes' && atendida && row && !row.classList.contains('done')){
      row.classList.add('done');
      var mc2 = row.querySelector('.mcheck'); if(mc2) mc2.textContent = '✓';
    }
    mesaToast(tipo === 'con_interes'
      ? (atendida ? '👍 marcado — ✓ atendida; al recargar pasa a \"Atendidas hoy\" (también quedó en el Radar)'
          : (d.calificada ? '👍 marcado — para bajar a \"Atendidas hoy\" necesita tu postura de HOY y estar al corriente de seguimiento' : '👍 marcado — también quedó en el Radar; falta tu postura en "¿Cómo lo ves?" para que cuente como atendida'))
      : (tipo === 'sin_info'
        ? (atendida ? '📵 Sin comunicación — ✓ atendida; cuando logres contacto cámbialo a 👍/👎'
            : (d.calificada ? '📵 Sin comunicación — para \"Atendidas hoy\" pon tu postura de HOY y declara un contacto para el seguimiento' : '📵 Sin comunicación — cuenta como evaluación; remata con tu lectura en "¿Cómo lo ves?" y cuando logres contacto cámbialo a 👍/👎'))
        : '👎 marcado — pasa a \"Descartadas hoy\" y mañana sale de la mesa; si el cliente revive, vuelve sola — y un descarte que el cliente desmiente cuenta en tu contra'));
  }).catch(function(){ thumbs.forEach(function(b){ b.disabled = false; }); mesaToast('No se pudo guardar (red o sesión).'); });
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

// ── Captura de seguimiento (reemplaza el re-tap). Las pills del panel derecho
//    SELECCIONAN (no postean); "Capturar seguimiento" manda las áreas marcadas
//    en orden al MISMO endpoint y recarga → el servidor re-renderiza el estado
//    autoritativo (declarado, historial, reloj) sin cirugía optimista del DOM. ──
function mesaSel(btn, cotId){
  var marea = btn.closest('.marea');
  if(!marea || marea.classList.contains('lock')) return;
  var area = marea.dataset.area;
  var drawer = document.getElementById('md'+cotId);
  var was = btn.classList.contains('sel');
  drawer.querySelectorAll('.marea[data-area="'+area+'"] .mpill').forEach(function(x){ x.classList.remove('sel'); });
  if(!was) btn.classList.add('sel');
  mesaSelLocks(drawer);
  mesaCapSync(cotId);
}
// Candados por SELECCIÓN (toggle, 1→2→3 fresco): sin nada marcado, Compromiso y
// ¿Cómo lo ves? quedan bloqueados. Hablamos abre Compromiso; Compromiso o
// No contestó abre ¿Cómo lo ves? (No contestó salta el 2). Re-bloquea al
// deseleccionar. Mismo criterio que el candado original, pero sobre la selección.
//
// El candado depende SOLO del prerequisito (NO de la propia selección del área):
// si dependiera de `!pos`/`!com` de sí mismo, una vez elegida el área nunca se
// re-bloquearía aunque retires el prerequisito (Hablamos→Quedamos→Decidiendo→
// cambio a No contestó dejaba las 3 marcadas y la captura mandaba un contacto
// contradictorio). Al re-bloquear, se LIMPIA la selección huérfana del área;
// se evalúa en cascada (contacto → compromiso → postura) para propagar.
function mesaSelLocks(drawer){
  var sel = function(a){ var b = drawer.querySelector('.marea[data-area="'+a+'"] .mpill.sel'); return b ? (b.dataset.e || '') : ''; };
  var clr = function(a){ drawer.querySelectorAll('.marea[data-area="'+a+'"] .mpill.sel').forEach(function(x){ x.classList.remove('sel'); }); };
  var con = sel('contacto');
  // Compromiso: se abre solo si hablaron (prerequisito = contacto 'hablamos')
  var a2 = drawer.querySelector('.marea[data-area="compromiso"]');
  var l2 = con !== 'hablamos';
  if(a2){ a2.classList.toggle('lock', l2); if(l2) clr('compromiso'); }
  // Postura: se abre con un compromiso declarado o si no contestó (salta el 2)
  var com = sel('compromiso'); // re-leído: pudo limpiarse arriba
  var a3 = drawer.querySelector('.marea[data-area="postura"]');
  var l3 = !com && con !== 'no_contesta';
  if(a3){ a3.classList.toggle('lock', l3); if(l3) clr('postura'); }
}
function mesaCapSels(drawer){
  var out = [];
  ['contacto','compromiso','postura'].forEach(function(area){
    var b = drawer.querySelector('.marea[data-area="'+area+'"] .mpill.sel');
    if(b) out.push({area:area, estado:b.dataset.e, razon:b.dataset.rz || null});
  });
  return out;
}
function mesaCapSync(cotId){
  var drawer = document.getElementById('md'+cotId);
  var sels = mesaCapSels(drawer);
  var btn = document.getElementById('mcap'+cotId), hint = document.getElementById('mcaph'+cotId);
  if(!btn) return;
  btn.disabled = sels.length === 0;
  // Sin aviso del reloj/próximo toque aquí: eso ya lo muestra la columna
  // Actividad (el chip .mfresh). Solo el "por qué está deshabilitado".
  hint.textContent = sels.length === 0 ? 'Marca al menos el contacto' : '';
}
function mesaCapturar(cotId){
  var drawer = document.getElementById('md'+cotId);
  var sels = mesaCapSels(drawer);
  if(sels.length === 0){ mesaToast('Marca al menos el contacto'); return; }
  var btn = document.getElementById('mcap'+cotId);
  btn.disabled = true; btn.textContent = 'Capturando…';
  var i = 0;
  (function next(){
    if(i >= sels.length){ location.reload(); return; }
    var s = sels[i++];
    fetch('/api/mesa/estado', {method:'POST',
      headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
      body: JSON.stringify({cotizacion_id:cotId, area:s.area, estado:s.estado, razon:s.razon})
    }).then(function(r){ return r.json(); }).then(function(d){
      if(!d.ok){ throw new Error(d.error || 'guardar'); }
      next();
    }).catch(function(e){
      btn.disabled = false; btn.textContent = 'Capturar seguimiento';
      mesaToast(typeof mesaErr === 'function' ? mesaErr(String((e && e.message) || e)) : 'No se pudo guardar.');
    });
  })();
}
// Suspender asistido del ghost (nunca automático). Reusa el endpoint existente
// /cotizaciones/:id/suspender (toggle; aquí la fila está activa → suspende).
// Sale de la mesa + del Radar; el cliente ya no puede abrir el slug (por diseño:
// fuerza al cliente a comunicarse). Solo el asesor la reactiva a mano.
function mesaSuspender(cotId){
  if(!confirm('¿Suspender esta cotización?\n\nSale de tu mesa y del Radar, y el cliente ya no podrá abrir el enlace. Solo tú podrás reactivarla.')) return;
  fetch('/cotizaciones/'+cotId+'/suspender', {method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
    body: JSON.stringify({accion:'suspender'})  // explícito, NO toggle (evita reactivar por carrera)
  }).then(function(r){ return r.json(); }).then(function(d){
    if(d.ok){ location.reload(); }
    else { mesaToast(typeof mesaErr === 'function' ? mesaErr(d.error) : (d.error || 'No se pudo suspender.')); }
  }).catch(function(){ mesaToast('No se pudo suspender (red o sesión).'); });
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
    if(!d.ok){ mesaToast(mesaErr(d.error)); return; }
    var drawer = btn.closest('.mdrawer');
    var row = document.querySelector('.mesa-emb .mrow[data-drawer="'+drawer.id+'"]');
    // pill exclusivo dentro del área
    var areaEl = btn.closest('.marea');
    areaEl.querySelectorAll('.mpill').forEach(function(x){x.classList.remove('on')});
    if(estado === 'descartada'){
      var md = areaEl.querySelector('.mdesc'); if(md) md.classList.add('on');
      // La manita 👎 se marcó sola (auto_manita): reflejarla abajo para que el
      // asesor VEA que el descarte con motivo ya cuenta completo, sin doble tap.
      if(d.auto_manita){
        var fbis = row.querySelectorAll('.mmarc .fbi');
        fbis.forEach(function(b,i){ b.classList.toggle('on', i === 1); }); // 👎 = índice 1 (👍👎📵)
      }
      mesaToast('Descartada — pasa a \"Descartadas hoy\" al recargar y mañana sale de la mesa. Se marcó 👎 por ti (ya cuenta completo). Si el cliente revive, vuelve sola con ⚡ — y un descarte que el cliente desmiente cuenta en tu contra');
    }
    btn.classList.add('on');
    // columnita con el label corto
    var slot = row.querySelectorAll('.mdecl3 span')[MESA_IDX[area]];
    if(slot){ slot.textContent = MESA_SHORT[estado] || estado; slot.classList.add('f'); }
    // frescura/reloj: SOLO un toque real la pone al corriente (contacto
    // declarado o el implícito del compromiso) — un tap de postura NO re-ancla
    // el reloj del servidor; y la cita firme solo baja con el desenlace
    var fr = row.querySelector('.mfresh');
    if(fr && (area === 'contacto' || d.auto_contacto) && fr.textContent.indexOf('cita') === -1){
      fr.textContent = 'hoy \u00b7 al corriente'; fr.className = 'mfresh ok';
    }
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
    // ✓ SOLO si bajaría a "Atendidas hoy": calificada + postura fresca + al
    // corriente (reloj no rojo). El reloj se actualizó arriba si fue contacto.
    var atendida = mesaAtendida(row, d);
    if(estado !== 'descartada' && atendida && !row.classList.contains('done')){
      row.classList.add('done');
      var mc = row.querySelector('.mcheck'); if(mc) mc.textContent = '✓';
      mesaToast('✓ Atendida — al recargar pasa a "Atendidas hoy"');
    } else if(estado !== 'descartada' && d.calificada && !atendida){
      // calificada pero aún no baja. Si YA se declaró un contacto (auto del
      // compromiso) o es una CITA, no ordenar "declara un contacto" —el reloj
      // de cita lo re-ancla el servidor y la recarga lo acomoda; el JS no puede
      // replicar esa lógica con certeza. Solo cuando NO hubo contacto ni es cita
      // damos la instrucción concreta.
      var esCita = !!(fr && fr.textContent.indexOf('cita') !== -1);
      if(d.auto_contacto || esCita){
        mesaToast('✓ Calificada — al recargar se acomoda tu seguimiento');
      } else {
        mesaToast('✓ Calificada — pero sigue 🔴 sin seguimiento; declara un contacto (Hablamos/No contestó) para bajarla a "Atendidas hoy"');
      }
    } else if(area === 'postura' && estado !== 'descartada' && !d.calificada){
      // declaró postura pero falta la manita — el momento exacto de avisar
      mesaToast('Postura guardada — falta la manita 👍👎/📵 para que cuente como atendida');
    }
    // Tap positivo con 👎 vigente: los taps ya NO corrigen la manita — se avisa
    if(d.fb_hint){
      mesaToast('Ojo: tu 👎 sigue puesto en esta cotización — si tu juicio cambió, cámbialo tú a 👍; ningún tap lo cambia por ti.');
    }
  }).catch(function(){ areaBtns.forEach(function(b){ b.disabled = false; }); mesaToast('No se pudo guardar (red o sesión) — recarga e intenta de nuevo.'); });
}

// Agenda: parquea la cotización con una fecha futura (sale de la mesa y del
// score; vuelve sola 7 días antes). El backend re-valida futura/≤6m/cooldown.
function mesaAgendar(cotId, btn){
  var inp = document.getElementById('mag' + cotId);
  var fecha = inp ? inp.value : '';
  if(!fecha){ mesaToast('Elige una fecha primero'); if(inp) inp.focus(); return; }
  btn.disabled = true;
  fetch('/api/mesa/agendar', {method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
    body: JSON.stringify({cotizacion_id:cotId, fecha:fecha})
  }).then(function(r){return r.json();}).then(function(d){
    btn.disabled = false;
    if(!d.ok){ mesaToast(d.msg || mesaErr(d.error)); return; }
    mesaToast('📅 Agendada — sale de tu mesa y vuelve sola 7 días antes. Recargando…');
    setTimeout(function(){ location.reload(); }, 1400);
  }).catch(function(){ btn.disabled = false; mesaToast('No se pudo guardar (red o sesión).'); });
}
function mesaDesagendar(cotId, btn){
  btn.disabled = true;
  fetch('/api/mesa/agendar', {method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
    body: JSON.stringify({cotizacion_id:cotId, cancelar:1})
  }).then(function(r){return r.json();}).then(function(d){
    btn.disabled = false;
    if(!d.ok){ mesaToast(d.msg || mesaErr(d.error)); return; }
    mesaToast('Regresó a tu mesa. Recargando…');
    setTimeout(function(){ location.reload(); }, 900);
  }).catch(function(){ btn.disabled = false; mesaToast('No se pudo guardar (red o sesión).'); });
}
</script>
<?php $MESA_ASSETS = ob_get_clean(); ?>

<?php if ($mesa_es_admin): ?>
<?php ob_start(); // ── Bloque compartido ADMIN (encabezado del ranking) ── ?>
<div class="mesa-emb" id="mesa-shared">
  <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:12.5px;color:#4a4a46;margin-bottom:6px">
    <span style="font-weight:800;color:#3f3f3a">📋 Mesa de trabajo</span>
    <span>la mesa de cada asesor está en su tarjeta, abajo del diagnóstico</span>
    <?php if ($mrec['rec_monto'] > 0): ?>
      <span style="color:#15803d;font-weight:800" title="Toda la empresa, últimos <?= (int)$mrec['dias'] ?> días">💰 <?= $mmoney($mrec['rec_monto']) ?> recuperado (<?= (int)$mrec['dias'] ?>d)</span>
    <?php endif; ?>
  </div>
  <?= $MESA_CICLO ?>
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

    <?= $MESA_PLAYBOOK ?>
<?php if (isset($_GET['mesa_dias'])): // ~8 queries pesadas — solo bajo demanda
    $mrep = Mesa::reporte($empresa_id, $mesa_dias);
    $mpct = fn(int $n, int $d) => $d > 0 ? round(100 * $n / $d) . '%' : '—';
    ?>
    <div id="mesa-rp" style="display:block;margin-bottom:12px;padding:14px 16px;background:#fff;border:1px solid #e2e2dc;border-radius:10px;font-size:12.5px;color:#3f3f3a">
      <div style="font-weight:800;margin-bottom:2px">📊 Reporte del equipo</div>
      <div style="color:#6a6a64;margin-bottom:8px">Qué cartera carga cada asesor, qué NO ha hecho con ella, y qué está
        declarando en la mesa. Los taps que das desde la mesa de un asesor cuentan a nombre de ese asesor.</div>
      <div style="margin-bottom:10px;font-size:12px;color:#6a6a64">Período:
        <?php foreach ([7, 15, 30, 60, 90] as $md): $act = $md === $mesa_dias; ?>
        <a href="?<?= e(http_build_query(array_merge($_GET, ['mesa_dias' => $md]))) ?>#mesa-shared"
           style="margin-left:4px;padding:2px 10px;border-radius:12px;text-decoration:none;font-weight:700;
                  <?= $act ? 'background:#1a5c38;color:#fff' : 'background:#f4f4f0;color:#4a4a46;border:1px solid #e2e2dc' ?>"><?= $md ?>d</a>
        <?php endforeach; ?>
        <span style="margin-left:8px;color:#a8a8a2">⚠️ Cartera, Sin calificar/trabajar, Se le fueron y Mesa sin calificar son la <b>foto de HOY</b> — no cambian con el período; el período aplica a Ventas, toques, pláticas, acuerdos, cumplidos, revividos y Recuperado</span>
      </div>
      <?php if (!$mrep['asesores']): ?>
        <div style="color:#6a6a64">Sin cotizaciones activas ni capturas — el reporte se llena conforme hay cartera y se usa la mesa.</div>
      <?php else: ?>

      <?php
      // ── TABLA UNIFICADA (decisión CEO 16-jul): una fila por asesor con
      //    resultado (ventas) + cartera + trabajo. Totales del equipo al pie. ──
      $mfolios = function (array $nums, string $lbl = '¿cuáles?'): string {
          if (!$nums) return '';
          $h = '<details style="display:inline-block;vertical-align:top"><summary style="cursor:pointer;color:#1a5c38;font-size:11px;list-style:none">' . $lbl . '</summary><div style="font-size:11.5px;color:#4a4a46;padding:2px 0;white-space:normal;max-width:220px">';
          foreach ($nums as $nn) $h .= '<div>' . e((string)$nn) . '</div>';
          return $h . '</div></details>';
      };
      $tot = ['ventas_n'=>0,'ventas_monto'=>0.0,'activas'=>0,'monto_activas'=>0.0,'sin_calificar'=>0,
              'sin_trabajar'=>0,'monto_sin_trabajar'=>0.0,'se_fueron'=>0,'monto_se_fueron'=>0.0,
              'hot_total'=>0,'hot_desatendidas'=>0,'hablamos'=>0,'no_contesta'=>0,'hablamos_cots'=>0,
              'con_compromiso'=>0,'citas'=>0,'no_quiso'=>0,'sin_compromiso'=>0,
              'comp_maduros'=>0,'comp_cumplidos'=>0,'comp_en_curso'=>0,
              'descartes'=>0,'revividos'=>0,'rec_n'=>0,'rec_monto'=>0.0];
      foreach ($mrep['asesores'] as $ru) { foreach ($tot as $tk => $tv) $tot[$tk] += $ru[$tk] ?? 0; }
      $mth = 'padding:4px 8px;white-space:nowrap';
      $mtd = 'padding:7px 8px;white-space:nowrap;vertical-align:top';
      ?>
      <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:12.5px;min-width:1180px">
        <thead><tr style="text-align:left;color:#a8a8a2;font-size:10.5px;text-transform:uppercase;letter-spacing:.04em">
          <th style="padding:4px 8px 4px 0">Asesor</th>
          <th style="<?= $mth ?>" title="Ventas del período con pago recibido. Es el RESULTADO — todo lo demás es el camino.">💰 Ventas</th>
          <th style="<?= $mth ?>" title="Cotizaciones vivas asignadas HOY y su valor. No cambia con el período.">Cartera hoy</th>
          <th style="<?= $mth ?>" title="De su cartera, cuántas no tienen NINGÚN juicio (ni manita 👍👎 ni postura). Foto de hoy.">Sin calificar</th>
          <th style="<?= $mth ?>" title="De su cartera, cuántas no tienen UNA SOLA captura ni calificación — nadie las está tocando. Foto de hoy.">Sin trabajar</th>
          <th style="<?= $mth ?>" title="Pasaron la ventana de cierre y llevan días sin ninguna atención (captura, manita, edición o reenvío). Mide atención, no ventas. Foto de hoy.">Se le fueron</th>
          <th style="<?= $mth ?>" title="Filas visibles HOY en su mesa a las que falta manita y/o postura. Mismo número que examina el termómetro.">Mesa sin calificar</th>
          <th style="<?= $mth ?>" title="Toques de contacto declarados en el período: Hablamos vs No contesta. Verde desde 50%.">Le contesta</th>
          <th style="<?= $mth ?>" title="De sus pláticas del período, en cuántas el desenlace vigente es 'Quedamos en algo' o 'Nos citamos'. Verde desde 40%.">Acuerdos</th>
          <th style="<?= $mth ?>" title="De sus pláticas del período, cuántas terminaron en cita agendada (física, virtual o telefónica).">📅 Citas</th>
          <th style="<?= $mth ?>" title="Pláticas cuyo desenlace vigente es 'No quiso' o 'Sin compromiso' — la mitad negativa del embudo.">Mueren</th>
          <th style="<?= $mth ?>" title="De los acuerdos con 5+ días, en cuántos el cliente SE MOVIÓ en los 5 días siguientes (abrió o compró). Verde desde 50%.">Cumplidos</th>
          <th style="<?= $mth ?>" title="Descartes del período que el cliente recalentó en los últimos 7 días (misma regla que la ⚡ de la mesa).">👎 Revividos</th>
          <th style="<?= $mth ?>;text-align:right" title="Ventas del período cuya cotización YA estaba descartada — dinero que se daba por muerto.">Recuperado</th>
        </tr></thead>
        <tbody>
        <?php foreach ($mrep['asesores'] as $ru):
            $toques  = $ru['hablamos'] + $ru['no_contesta'];
            $pos_tot = array_sum($ru['postura']);
            $acuerdos = (int)$ru['con_compromiso'] + (int)($ru['citas'] ?? 0);
            $mueren   = (int)($ru['no_quiso'] ?? 0) + (int)($ru['sin_compromiso'] ?? 0);
        ?>
        <tr style="border-top:1px solid #eeeee9;vertical-align:top">
          <td style="padding:7px 8px 7px 0;font-weight:700;white-space:nowrap"><?= e($ru['nombre'] ?: '—') ?>
            <?php if ($pos_tot): $pz = [];
                foreach ($ru['postura'] as $pe => $pn) $pz[] = e($MESA_SHORT[$pe] ?? $pe) . ' ' . $mpct($pn, $pos_tot);
            ?><div style="font-weight:400;font-size:10.5px;color:#a8a8a2;white-space:normal;max-width:150px" title="¿Cómo lo ve? — su última lectura declarada por cotización, en el período"><?= implode(' · ', $pz) ?></div>
            <?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['ventas_n']): ?><b style="color:#15803d"><?= (int)$ru['ventas_n'] ?> · <?= $mmoney($ru['ventas_monto']) ?></b>
            <?php else: ?><span style="color:#b91c1c;font-weight:700">0 ventas</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>"><b><?= (int)$ru['activas'] ?></b> · <?= $mmoney($ru['monto_activas']) ?></td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['sin_calificar']): ?>
              <span style="color:#dc2626;font-weight:700"><?= (int)$ru['sin_calificar'] ?></span> de <?= (int)$ru['activas'] ?>
              (<?= $mpct($ru['sin_calificar'], $ru['activas']) ?>)
            <?php else: ?><span style="color:#16a34a;font-weight:700">0 ✓</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['sin_trabajar']): ?>
              <span style="color:#dc2626;font-weight:700"><?= (int)$ru['sin_trabajar'] ?></span>
              · <?= $mmoney($ru['monto_sin_trabajar']) ?> <?= $mfolios($ru['sin_trabajar_cots'] ?? []) ?>
            <?php else: ?><span style="color:#16a34a;font-weight:700">0 ✓</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['se_fueron']): ?>
              <span style="color:#b91c1c;font-weight:800"><?= (int)$ru['se_fueron'] ?></span>
              · <?= $mmoney($ru['monto_se_fueron']) ?> <?= $mfolios($ru['se_fueron_cots'] ?? []) ?>
            <?php else: ?><span style="color:#16a34a;font-weight:700">0 ✓</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['hot_total']): ?>
              <span style="<?= $ru['hot_desatendidas'] > 0 ? 'color:#b91c1c;font-weight:800' : 'color:#16a34a;font-weight:700' ?>"><?= (int)$ru['hot_desatendidas'] ?></span> de <?= (int)$ru['hot_total'] ?>
              <?= $mfolios($ru['fallas_cots'] ?? []) ?>
            <?php else: ?><span style="color:#a8a8a2">mesa vacía</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($toques): ?><b><?= (int)$ru['hablamos'] ?></b> de <?= $toques ?>
              <span style="color:<?= $ru['hablamos'] / $toques >= .5 ? '#16a34a' : '#dc2626' ?>;font-weight:700">(<?= $mpct($ru['hablamos'], $toques) ?>)</span>
            <?php else: ?><span style="color:#dc2626;font-weight:700">sin toques</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['hablamos_cots']): ?><b><?= $acuerdos ?></b> de <?= (int)$ru['hablamos_cots'] ?>
              <span style="color:<?= $acuerdos / $ru['hablamos_cots'] >= .4 ? '#16a34a' : '#dc2626' ?>;font-weight:700">(<?= $mpct($acuerdos, $ru['hablamos_cots']) ?>)</span>
              <?php if ($ru['compromiso_cots']): ?>
              <details style="display:inline-block;vertical-align:top"><summary style="cursor:pointer;color:#1a5c38;font-size:11px;list-style:none">¿cuáles?</summary>
                <div style="font-size:11.5px;color:#4a4a46;padding:2px 0;white-space:normal;max-width:240px">
                <?php foreach ($ru['compromiso_cots'] as $cc): ?>
                  <div><?= ($cc['tipo'] ?? '') === 'cita' ? '📅 ' : '' ?><?= e($cc['numero']) ?> — <span style="<?= $cc['donde'] === 'vendida' || $cc['donde'] === 'aceptada' ? 'color:#15803d;font-weight:700' : ($cc['donde'] === 'activa' ? '' : 'color:#b91c1c') ?>"><?= e(ucfirst($cc['donde'])) ?><?= $cc['donde'] !== 'activa' ? ' (ya no está en la mesa)' : '' ?></span></div>
                <?php endforeach; ?>
                </div>
              </details>
              <?php endif; ?>
            <?php else: ?><span style="color:#a8a8a2">sin pláticas</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['hablamos_cots']): ?>
              <?php if (!empty($ru['citas'])): ?><b style="color:#1a5c38">📅 <?= (int)$ru['citas'] ?></b>
                <span style="font-weight:700">(<?= $mpct((int)$ru['citas'], $ru['hablamos_cots']) ?>)</span>
              <?php else: ?><span style="color:#a8a8a2">0 (0%)</span><?php endif; ?>
            <?php else: ?><span style="color:#a8a8a2">sin pláticas</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['hablamos_cots']): ?>
              <?php if ($mueren): ?><span style="color:#b91c1c;font-weight:700"><?= $mueren ?></span> (<?= $mpct($mueren, $ru['hablamos_cots']) ?>)
              <?php else: ?><span style="color:#16a34a;font-weight:700">0 ✓</span><?php endif; ?>
            <?php else: ?><span style="color:#a8a8a2">sin pláticas</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['comp_maduros']): ?><b><?= (int)$ru['comp_cumplidos'] ?></b> de <?= (int)$ru['comp_maduros'] ?>
              <span style="color:<?= $ru['comp_cumplidos'] / $ru['comp_maduros'] >= .5 ? '#16a34a' : '#dc2626' ?>;font-weight:700">(<?= $mpct($ru['comp_cumplidos'], $ru['comp_maduros']) ?>)</span><?= $ru['comp_en_curso'] ? ' · ' . (int)$ru['comp_en_curso'] . ' en curso' : '' ?>
            <?php elseif ($ru['comp_en_curso']): ?><?= (int)$ru['comp_en_curso'] ?> en curso
            <?php else: ?><span style="color:#a8a8a2">sin acuerdos aún</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>">
            <?php if ($ru['descartes']): ?>
              <span style="<?= $ru['revividos'] > 0 ? 'color:#d97706;font-weight:700' : '' ?>"><?= (int)$ru['revividos'] ?> de <?= (int)$ru['descartes'] ?></span>
            <?php else: ?><span style="color:#a8a8a2">sin descartes</span><?php endif; ?>
          </td>
          <td style="<?= $mtd ?>;text-align:right">
            <?php if ($ru['rec_n']): ?><b style="color:#15803d"><?= $mmoney($ru['rec_monto']) ?></b> (<?= (int)$ru['rec_n'] ?>)
            <?php else: ?><span style="color:#a8a8a2">$0</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php $ttoq = $tot['hablamos'] + $tot['no_contesta']; $tacu = $tot['con_compromiso'] + $tot['citas']; $tmue = $tot['no_quiso'] + $tot['sin_compromiso']; ?>
        <tr style="border-top:2px solid #d6d6cf;vertical-align:top;background:#fafaf7;font-weight:700">
          <td style="padding:7px 8px 7px 0;white-space:nowrap">EQUIPO</td>
          <td style="<?= $mtd ?>"><?= (int)$tot['ventas_n'] ?> · <?= $mmoney($tot['ventas_monto']) ?></td>
          <td style="<?= $mtd ?>"><?= (int)$tot['activas'] ?> · <?= $mmoney($tot['monto_activas']) ?></td>
          <td style="<?= $mtd ?>"><?= (int)$tot['sin_calificar'] ?> de <?= (int)$tot['activas'] ?> (<?= $mpct($tot['sin_calificar'], $tot['activas']) ?>)</td>
          <td style="<?= $mtd ?>"><?= (int)$tot['sin_trabajar'] ?> · <?= $mmoney($tot['monto_sin_trabajar']) ?></td>
          <td style="<?= $mtd ?>"><?= (int)$tot['se_fueron'] ?> · <?= $mmoney($tot['monto_se_fueron']) ?></td>
          <td style="<?= $mtd ?>"><?= (int)$tot['hot_desatendidas'] ?> de <?= (int)$tot['hot_total'] ?></td>
          <td style="<?= $mtd ?>"><?= (int)$tot['hablamos'] ?> de <?= $ttoq ?> (<?= $mpct($tot['hablamos'], $ttoq) ?>)</td>
          <td style="<?= $mtd ?>"><?= $tacu ?> de <?= (int)$tot['hablamos_cots'] ?> (<?= $mpct($tacu, $tot['hablamos_cots']) ?>)</td>
          <td style="<?= $mtd ?>">📅 <?= (int)$tot['citas'] ?> (<?= $mpct($tot['citas'], $tot['hablamos_cots']) ?>)</td>
          <td style="<?= $mtd ?>"><?= $tmue ?> (<?= $mpct($tmue, $tot['hablamos_cots']) ?>)</td>
          <td style="<?= $mtd ?>"><?= (int)$tot['comp_cumplidos'] ?> de <?= (int)$tot['comp_maduros'] ?> (<?= $mpct($tot['comp_cumplidos'], $tot['comp_maduros']) ?>)</td>
          <td style="<?= $mtd ?>"><?= (int)$tot['revividos'] ?> de <?= (int)$tot['descartes'] ?></td>
          <td style="<?= $mtd ?>;text-align:right"><?= $mmoney($tot['rec_monto']) ?> (<?= (int)$tot['rec_n'] ?>)</td>
        </tr>
        </tbody>
      </table>
      </div>

      <div style="margin-top:10px;color:#6a6a64;font-size:11.5px;line-height:1.6">
        <div style="margin-bottom:4px"><b>Cómo leer la tabla</b> (cada encabezado también explica su columna al pasar el mouse):</div>
        <b>💰 Ventas</b>: cerradas en el período CON pago recibido — el resultado; todo lo demás es el camino.
        <b>Cartera hoy</b>: vivas asignadas y su valor (foto de hoy, no cambia con el período).
        <b>Sin calificar</b>: sin ningún juicio (ni manita 👍👎 ni postura).
        <b>Sin trabajar</b>: ni una captura ni calificación — nadie las toca.
        <b>Se le fueron</b>: pasaron la ventana (día <?= $mp75 ?>) y llevan <?= max(3, (int)ceil($mp75 / 2)) ?>+ días sin ninguna atención; mide atención, no ventas — descartarla con 👎 cuenta como decisión y la saca.
        <b>Mesa sin calificar</b>: filas visibles HOY en su mesa a las que falta manita y/o postura — el mismo número que examina el 25% del Seguimiento en el termómetro.
        <b>Le contesta</b>: toques declarados del período (Hablamos vs No contesta) — verde desde 50%. Ojo: cuenta declaraciones, no clientes.
        <b>Acuerdos</b>: de sus pláticas (cotizaciones con conversación en el período), en cuántas el desenlace vigente es "Quedamos en algo" o 📅 cita — verde desde 40%; el color y el % son el mismo número.
        <b>📅 Citas</b>: de esas mismas pláticas, cuántas terminaron en cita agendada.
        <b>Mueren</b>: pláticas cuyo desenlace vigente es "No quiso" o "Sin compromiso" — la mitad negativa del embudo.
        <b>Cumplidos</b>: acuerdos con 5+ días donde el cliente se movió en los 5 días siguientes (abrió o compró) — verde desde 50%; "en curso" = aún no maduran, no cuentan ni a favor ni en contra.
        <b>👎 Revividos</b>: descartes del período que el cliente recalentó en los últimos 7 días (misma regla que la ⚡ de la mesa) — muchos = está matando ventas vivas.
        <b>Recuperado</b>: ventas del período que ya estaban descartadas y aun así cerraron; una venta recuperada NO aparece en Revividos (se juzga aquí, no allá).
        <br><b>Reglas parejas de toda la sección:</b> las <b>descartadas</b> salen completas de pláticas/acuerdos (se juzgan en Revividos y Recuperado); las <b>vendidas/aceptadas siguen contando</b> en acuerdos (son su éxito — el "¿cuáles?" las desglosa); los <b>toques cuentan siempre</b> (el esfuerzo fue real); las cotizaciones con <b>Descuento Inteligente</b> las tomó el sistema y salen de TODAS las columnas (su venta sí suma en 💰 Ventas).
      </div>
    <?php endif; ?>
    </div>
    <?php endif; // reporte bajo demanda ?>

    
</div>
<?php $MESA_SHARED = ob_get_clean(); ?>
<?php else: ?>
<?php ob_start(); // ── Bloque del asesor (su mesa + su cobertura) — vive DENTRO
// de la tarjeta del termómetro, abajo del tip (no es tarjeta aparte) ── ?>
<div class="mesa-emb" id="mesa-shared" style="margin-top:12px;padding-top:12px;border-top:1px solid #e2e2dc">
  <div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:12.5px;color:#4a4a46;margin-bottom:6px">
      <span style="font-weight:800;color:#3f3f3a;font-size:14px">📋 Tu mesa de trabajo</span>
      <?php if ($mrec['rec_monto'] > 0): ?>
        <span style="color:#15803d;font-weight:800" title="Cotizaciones tuyas descartadas que aun así cerraste — últimos <?= (int)$mrec['dias'] ?> días">💰 Recuperaste <?= $mmoney($mrec['rec_monto']) ?></span>
      <?php endif; ?>
    </div>
    <details style="margin-bottom:8px">
      <summary style="cursor:pointer;color:#1a5c38;font-weight:700;font-size:11.5px;list-style:none">🧭 ¿Mesa o Radar? — en qué se diferencian</summary>
      <div style="font-size:12px;color:#4a4a46;line-height:1.6;padding:6px 0 2px;max-width:720px">
        <div style="margin-bottom:4px"><b>El Radar observa al cliente.</b> Clasifica cada cotización por lo que el cliente HACE
          (Probable cierre, Validando precio, Enfriándose…). Es tu mapa completo: ahí ves todo, incluso lo que va bien.</div>
        <div style="margin-bottom:4px"><b>La mesa es tu lista de trabajo de HOY.</b> Te forma solo las cotizaciones donde falta
          TU siguiente jugada: señales 🔥 nuevas, las que revivieron tras un descarte y las que aún no calificas.
          Lo que ya va bien no sale aquí — por eso la mesa se vacía cuando trabajas.</div>
        <div><b>"Probable cierre" sigue siendo tu prioridad — eso no cambió.</b> Cuando una cotización se calienta,
          la mesa te la pone al frente como señal 🔥 para que le des un toque el mismo día y declares qué pasó.
          La mesa no reemplaza al Radar: te ahorra decidir por dónde empezar.</div>
      </div>
    </details>
    <?php if ($mesa_cob !== null):
        // MISMO cálculo que el score (proporcional 3 escalones — fuente única):
        // <50% bajo · 50–80% medio (0.5) · ≥80% completo. Mesa vacía = al día.
        // El desglose lista EXACTAMENTE las filas que cuenta el contador
        // (cobertura_detalle realineado a la fuente única, auditoría 16-jul) —
        // antes decía "1 de 2" y listaba 5 episodios de otra fuente.
        $cob_vacia = ($mesa_cob['pedidas'] === 0);
        $cob_cov   = !$cob_vacia ? $mesa_cob['atendidas'] / $mesa_cob['pedidas'] : 1.0;
        $cob_pct   = (int)round($cob_cov * 100);
        $cob_niv   = $cob_vacia ? 'ok' : ($cob_cov >= 0.80 ? 'ok' : ($cob_cov >= 0.50 ? 'medio' : 'bajo'));
        $cob_col   = $cob_niv === 'ok' ? '#15803d' : ($cob_niv === 'medio' ? '#b45309' : '#b91c1c');
        $cob_tag   = $cob_niv === 'ok' ? '✓ completo' : ($cob_niv === 'medio' ? 'medio (cuenta 50%)' : 'bajo (no cuenta)');
        $cob_pend  = max(0, (int)$mesa_cob['pedidas'] - (int)$mesa_cob['atendidas']);
    ?>
    <div style="margin-bottom:8px;font-size:12.5px">
      <span style="font-weight:700;color:<?= $cob_col ?>">
        🔥 Señales de tu mesa: <?php if ($cob_vacia): ?>sin pendientes ✓<?php else: ?><?= (int)$mesa_cob['atendidas'] ?> de <?= (int)$mesa_cob['pedidas'] ?> atendidas (<?= $cob_pct ?>%) · <?= $cob_tag ?><?php endif; ?></span>
      <?php if ($cob_pend > 0): ?>
        · <span style="color:#d97706;font-weight:700"><?= $cob_pend ?> pendiente<?= $cob_pend > 1 ? 's' : '' ?></span>
      <?php endif; ?>
      <?php if ($mesa_cob_det): ?>
      <details style="display:inline-block;vertical-align:top;margin-left:6px">
        <summary style="cursor:pointer;color:#1a5c38;font-weight:700;font-size:11.5px;list-style:none">ver desglose</summary>
        <div style="font-size:11.5px;color:#4a4a46;padding:4px 0;line-height:1.7">
          <?php foreach ($mesa_cob_det as $cd): ?>
          <div><a href="/cotizaciones/<?= (int)$cd['cotizacion_id'] ?>" style="color:inherit"><?= e($cd['numero']) ?></a>
            — <?php if ((int)$cd['atendida']): ?><span style="color:#15803d;font-weight:700">✓ atendida</span>
              <?php else: ?><span style="color:#d97706;font-weight:700">falta <?= e($cd['falta']) ?></span><?php endif; ?></div>
          <?php endforeach; ?>
          <div style="color:#a8a8a2;margin-top:4px"><?= (int)($empresa['mesa_activa'] ?? 0) >= 2
              ? 'Cuenta para tu termómetro: cubrir ≥80% = completo · 50–80% = medio (mitad) · menos de 50% = no cuenta.'
              : 'Pronto contará para tu termómetro: ≥80% = completo · 50–80% = medio · menos de 50% = no cuenta.' ?> Las viejas que YA trabajaste (manita + postura) se van a ❄️ Frías y dejan de contar; las que ignoras siguen contando hasta que las trabajes o descartes.</div>
        </div>
      </details>
      <?php endif; ?>
    </div>
    <?php endif; // mesa_cob ?>
    <?= $MESA_CICLO ?>
    <?= $MESA_PLAYBOOK ?>
  </div>
  <?php // Su mesa, abierta por default: es su lista de trabajo, no un cajón
    $mesa_mio = $MESA_BLOQUES[Auth::id()] ?? '';
    echo str_replace('<details class="mesa-emb mesa-strip" ', '<details open class="mesa-emb mesa-strip" ', $mesa_mio);
  ?>
</div>
<?php $MESA_ASESOR = ob_get_clean(); endif; ?>

