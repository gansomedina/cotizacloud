<?php
// ============================================================
//  Supervisor — Reportes por sucursal
//
//  SOLO REPORTES. No es el dashboard ni da acceso a la operación: no hay
//  cotizaciones, ni clientes, ni ventas, ni configuración. El supervisor lee
//  el desempeño del equipo y nada más.
//
//  CERO CAMBIOS AL SISTEMA. Este archivo y ejecutivo.php son todo lo que
//  existe del supervisor. No se toca dashboard/, ni config/, ni el Router más
//  allá de registrar sus dos rutas.
//
//  Los DATOS y los TEXTOS salen de RitmoAsesor::empresa() y
//  ActividadScore::promedio_mensual() — las mismas funciones que alimentan al
//  dueño (ambas verificadas sin EMPRESA_ID, así que aceptan cualquier
//  empresa). El marcado es el mismo de dashboard/_ritmo.php, y como esta
//  página usa el layout normal de la app se ve idéntico.
//
//  Los privilegios son de la PÁGINA, no del usuario: el alcance sale del
//  archivo data/supervisores.json, no del rol. Así su cuenta puede no tener
//  permiso de nada en ninguna empresa.
// ============================================================
defined('COTIZAAPP') or die;
supervisor_requerir();

$sv_ids = supervisor_empresas();
$ph  = implode(',', array_fill(0, count($sv_ids), '?'));
$suc = DB::query("SELECT id, nombre FROM empresas WHERE id IN ($ph) ORDER BY nombre", $sv_ids);

// Sucursal activa, validada contra SU lista: ?emp=99 cae al default en vez de
// mostrar datos de una empresa que no supervisa.
$sel = (int)($_GET['emp'] ?? 0);
if (!in_array($sel, $sv_ids, true)) $sel = (int)($suc[0]['id'] ?? 0);
$sel_nombre = '';
foreach ($suc as $s) if ((int)$s['id'] === $sel) $sel_nombre = $s['nombre'];

// ── Los 5 pilares (misma fuente que el dueño) ───────────────
$mesa_on = false; $filas = []; $win = 20; $business = false;
if ($sel) {
    try { $mesa_on = (int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$sel]) >= 1; }
    catch (Throwable $e) {}
    $business = !empty(trial_info($sel)['es_business']);
    if ($mesa_on) {
        try { $filas = RitmoAsesor::empresa($sel); } catch (Throwable $e) { $filas = []; }
    }
    try {
        if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
        $c = Radar::ciclo_venta($sel);
        if (!empty($c['auto']) && !empty($c['p75'])) $win = 2 * max(3, (int)$c['p75']);
    } catch (Throwable $e) {}
}

// ── La Mesa de Trabajo REAL, incluyendo el archivo del sistema ──
//
// dashboard/_mesa.php NO se modifica: sus condiciones dependen de variables
// ($empresa, $empresa_id, $es_admin_dash, $trial) que aquí se le preparan con
// la sucursal elegida. El include no emite nada: llena $MESA_SHARED (CSS, JS y
// playbook, una sola vez) y $MESA_BLOQUES[uid] (la mesa de cada asesor), que
// abajo se pintan bajo los pilares de su dueño.
//
// OJO — EFECTO CONOCIDO: ese archivo llama a Mesa::armar() SIN solo_lectura, y
// esa llamada escribe en mesa_vencidos (Mesa.php:687), que alimenta el castigo
// de Seguimiento. Es la MISMA escritura que hace el dashboard del dueño y es
// idempotente por PK (cotización + fecha), así que no duplica nada. Pero si un
// día el dueño no abre su panel y el supervisor sí, ese día queda registrado
// igual. Evitarlo exigiría tocar _mesa.php, y la instrucción es no tocar el
// producto.
$MESA_SHARED = ''; $MESA_BLOQUES = []; $MESA_ASSETS = ''; $MESA_ASESOR = '';
$empresa = null;
if ($sel) {
    try { $empresa = DB::row("SELECT * FROM empresas WHERE id = ?", [$sel]); } catch (Throwable $e) {}
}
$es_admin_dash = true;          // la mesa completa de la sucursal, no la de un asesor
$empresa_id    = $sel;          // los partials del dashboard leen esta variable
$trial         = $sel ? trial_info($sel) : [];

// Por qué NO va en un try/catch mudo: si el include falla o sale temprano, la
// página se veía completa pero SIN mesa, y sin una sola pista de por qué.
// Aquí se guarda el motivo y se muestra abajo — un fallo silencioso en una
// herramienta de diagnóstico es peor que ningún diagnóstico.
$mesa_motivo = '';
if (!$sel)                 $mesa_motivo = 'sin sucursal seleccionada';
elseif (!$empresa)         $mesa_motivo = 'no se pudo leer la empresa';
elseif (!$business)        $mesa_motivo = 'la sucursal no está en plan Business (la Mesa es de ese plan)';
else {
    // Las mismas condiciones que _mesa.php evalúa al entrar, comprobadas aquí
    // para poder DECIR cuál falló en vez de mostrar un hueco.
    $mesa_vend_n = 0;
    try {
        $mesa_vend_n = (int)DB::val(
            "SELECT COUNT(DISTINCT u.id)
               FROM usuarios u
               JOIN cotizaciones c ON COALESCE(c.vendedor_id, c.usuario_id) = u.id
              WHERE u.empresa_id = ? AND u.activo = 1
                AND c.empresa_id = ? AND c.estado IN ('enviada','vista') AND c.suspendida = 0",
            [$sel, $sel]);
    } catch (Throwable $e) { $mesa_motivo = 'error leyendo asesores: ' . $e->getMessage(); }

    if ($mesa_motivo === '' && $mesa_vend_n === 0) {
        $mesa_motivo = 'ningún asesor de esta sucursal tiene cotizaciones enviadas o vistas';
    }
    if ($mesa_motivo === '') {
        // El include va en su PROPIO búfer: _mesa.php no debería emitir nada
        // directo (llena variables), pero esto corre antes del ob_start() de
        // la página y cualquier salida suelta —hasta un salto de línea tras
        // un cierre de etiqueta PHP— se colaría delante del DOCTYPE y rompería
        // el layout sin dejar rastro.
        ob_start();
        try {
            include MODULES_PATH . '/dashboard/_mesa.php';
        } catch (Throwable $e) {
            $mesa_motivo = 'la Mesa falló: ' . $e->getMessage();
            error_log('[Supervisor] _mesa.php falló para empresa ' . $sel . ': ' . $e->getMessage());
        }
        $mesa_suelto = trim((string)ob_get_clean());
        if ($mesa_motivo === '' && $MESA_SHARED === '' && !$MESA_BLOQUES) {
            $mesa_motivo = 'la Mesa salió temprano por alguna de sus condiciones internas'
                         . ($mesa_suelto !== '' ? ' — emitió: ' . mb_substr(strip_tags($mesa_suelto), 0, 120) : '');
        }
    }
}

// ── Score mensual de esa sucursal ───────────────────────────
$sc = []; $meses = [];
foreach (ActividadScore::promedio_mensual($sel, 6) as $r) {
    $meses[$r['mes']] = true;
    $sc[(int)$r['usuario_id']]['nombre'] = $r['asesor'];
    $sc[(int)$r['usuario_id']]['m'][$r['mes']] = $r;
}
krsort($meses);
$cols = array_slice(array_keys($meses), 0, 6);
$MES = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
uasort($sc, function ($a, $b) {
    $pa = array_sum(array_column($a['m'], 'score_prom')) / max(count($a['m']), 1);
    $pb = array_sum(array_column($b['m'], 'score_prom')) / max(count($b['m']), 1);
    return $pb <=> $pa;
});

$colmap = ['rojo' => '#dc2626', 'amarillo' => '#d97706', 'verde' => '#16a34a'];
$n_alerta = count(array_filter($filas, fn($x) => $x['semaforo'] !== 'verde'));

// Un pilar — marcado idéntico al de dashboard/_ritmo.php
$pill = function (string $estado, string $label, string $txt) use ($colmap): string {
    $dc = $colmap[$estado] ?? '#9ca3af';
    $ok = str_starts_with($txt, '✓');
    return '<div style="display:flex;align-items:baseline;gap:7px;margin-top:3px;font:500 12px var(--body)">'
         . '<span style="width:8px;height:8px;border-radius:50%;background:' . $dc . ';flex:none;align-self:center"></span>'
         . '<span style="color:var(--t2);font-weight:700;min-width:88px">' . $label . '</span>'
         . '<span style="color:' . ($ok ? 'var(--t3)' : $dc) . '">' . e($txt) . '</span></div>';
};
$scol = function (?float $v): string {
    if ($v === null) return 'var(--t3)';
    if ($v >= 86) return '#15803d';
    if ($v >= 61) return '#16a34a';
    if ($v >= 31) return '#d97706';
    return '#dc2626';
};

ob_start();
?>
<style>
.svt{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:10px}
.svt a{padding:9px 15px;border-radius:9px;font:700 13px var(--body);text-decoration:none;color:var(--t2);
       border:1px solid transparent;white-space:nowrap}
.svt a:hover{background:rgba(0,0,0,.04)}
.svt a.on{background:var(--white);border-color:var(--border);color:var(--text);box-shadow:var(--sh)}
.svt a.exe{margin-left:auto;color:var(--g)}
.sv-vac{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:24px 18px;
        text-align:center;color:var(--t3);font:400 13px var(--body);margin-bottom:16px}
.sv-tb{width:100%;border-collapse:collapse}
.sv-tb th{font:700 10.5px var(--body);text-transform:uppercase;letter-spacing:.04em;color:var(--t3);
          padding:9px 14px;border-bottom:1px solid var(--border);white-space:nowrap;text-align:right}
.sv-tb th:first-child{text-align:left}
.sv-tb td{font:400 13px var(--body);padding:10px 14px;border-bottom:1px solid var(--border);text-align:right;white-space:nowrap}
.sv-tb td:first-child{text-align:left;font-weight:600;color:var(--text)}
.sv-tb tr:last-child td{border-bottom:none}
</style>

<div class="svt">
    <?php foreach ($suc as $s): ?>
    <a href="/supervisor?emp=<?= (int)$s['id'] ?>" class="<?= (int)$s['id'] === $sel ? 'on' : '' ?>"><?= e($s['nombre']) ?></a>
    <?php endforeach; ?>
    <a href="/supervisor/ejecutivo" class="exe">Ejecutivo consolidado →</a>
</div>

<?php if (!$sel): ?>
<div class="sv-vac">No tienes sucursales asignadas.</div>
<?php else: ?>

<!-- ── Rendimiento del equipo: los 5 pilares ── -->
<div class="slabel">Rendimiento del equipo · <?= (int)$win ?> días
  <?php if ($n_alerta > 0): ?><span style="font:700 11px var(--body);color:#dc2626"><?= $n_alerta ?> por atender</span><?php endif; ?>
</div>

<?php if (!$business): ?>
<div class="sv-vac"><strong><?= e($sel_nombre) ?></strong> no está en plan Business: el Ritmo del equipo y el reporte del Director son de ese plan.</div>
<?php elseif (!$mesa_on): ?>
<div class="sv-vac"><strong><?= e($sel_nombre) ?></strong> tiene la Mesa de Trabajo apagada, que es de donde salen estos indicadores.</div>
<?php elseif (!$filas && !$MESA_SHARED): ?>
<div class="sv-vac">Sin asesores con cartera activa en esta sucursal.</div>
<?php else: ?>
<?php
// El bloque compartido de la Mesa (CSS, JS y playbook) va UNA sola vez y antes
// que los bloques de cada asesor, que dependen de él para verse y funcionar.
if ($MESA_SHARED) echo $MESA_SHARED . $MESA_ASSETS;
?>
<?php if ($mesa_motivo !== ''): ?>
<div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:var(--r);padding:12px 16px;
            margin-bottom:14px;font:400 12.5px var(--body);color:#9a3412;line-height:1.55">
    <strong>La Mesa de Trabajo no se está mostrando.</strong> Motivo: <?= e($mesa_motivo) ?>.
</div>
<?php endif; ?>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:16px">
  <?php foreach ($filas as $f): $c = $colmap[$f['semaforo']] ?? '#9ca3af'; ?>
  <div style="display:flex;align-items:flex-start;gap:12px;padding:13px 16px;border-bottom:1px solid var(--border)">
    <span style="width:12px;height:12px;border-radius:50%;background:<?= $c ?>;flex-shrink:0;margin-top:4px"></span>
    <div style="flex:1;min-width:0">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px">
        <span style="font:700 14px var(--body);color:var(--text)"><?= e($f['nombre']) ?></span>
        <?php if (!empty($f['flag'])): ?><span style="font:700 10px var(--body);color:#b91c1c;background:#fdeaea;padding:2px 7px;border-radius:999px">no sigue el proceso<?= !empty($f['flag_pilares']) ? ': ' . e($f['flag_pilares']) : '' ?></span><?php endif; ?>
        <button type="button" class="rt-rep-btn" data-uid="<?= (int)$f['usuario_id'] ?>" data-eid="<?= $sel ?>" data-name="<?= e($f['nombre']) ?>"
          style="margin-left:auto;flex:none;font:800 11px var(--body);color:#fff;background:linear-gradient(135deg,#1a5c38,#2ea043);border:0;border-radius:999px;padding:6px 13px;cursor:pointer;letter-spacing:.02em;box-shadow:0 2px 7px rgba(26,92,56,.32)">✨ Generar reporte</button>
      </div>
      <?= $pill($f['conv_estado'],  'Conversión',  $f['conv_txt']) ?>
      <?= $pill($f['desc_estado'],  'Descartadas', $f['desc_txt']) ?>
      <?= $pill($f['citas_estado'], 'Citas',       $f['citas_txt']) ?>
      <?= $pill($f['venc_estado'],  'Seguimiento', $f['venc_txt']) ?>
      <?= $pill($f['cont_estado'],  'Contacto',    $f['cont_txt']) ?>
      <div style="font:600 12.5px var(--body);color:<?= $f['semaforo'] === 'verde' ? 'var(--t3)' : $c ?>;margin-top:6px">→ <?= e($f['motivo']) ?></div>
    </div>
  </div>
  <?php
  // La mesa de ESTE asesor va FUERA de la fila de pilares y al nivel de la
  // tarjeta, exactamente como en el dashboard del dueño (index.php:1173, donde
  // se emite después de cerrar el <div> de su fila del ranking).
  //
  // Meterla DENTRO del div de los pilares la metía en un flex con su propio
  // padding: el .mstrip ya trae padding-left:52px pensado para el ancho de la
  // tarjeta, así que se comprimía y el "tap para expandir" —que va con
  // margin-left:auto al final del <summary>— se caía a una segunda línea.
  $mu = (int)$f['usuario_id'];
  if (!empty($MESA_BLOQUES[$mu])) { echo $MESA_BLOQUES[$mu]; unset($MESA_BLOQUES[$mu]); }
  ?>
  <?php endforeach; ?>
  <?php // Asesores con mesa pero sin fila de pilares (sin cartera para el Ritmo)
  if (!empty($MESA_BLOQUES)): foreach ($MESA_BLOQUES as $mb): ?>
  <div style="padding:13px 16px;border-bottom:1px solid var(--border)"><?= $mb ?></div>
  <?php endforeach; $MESA_BLOQUES = []; endif; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>5 pilares</strong> (cada uno con su semáforo): <b>Conversión</b> (cerró vs cotizaciones) · <b>Descartadas</b> (vs cierres, sin cita, muy rápido) · <b>Citas</b> (su ritmo de agendar) · <b>Seguimiento</b> (el cronómetro de vencidas) · <b>Contacto</b> (a cuántos no le contestan). Medido contra las reglas de la Mesa — hechos, no acusaciones.
  </div>
</div>
<?php endif; ?>

<!-- ── Score mensual ── -->
<div class="slabel">Score mensual · promedio real del mes</div>
<?php if (!$cols): ?>
<div class="sv-vac">Todavía no hay historial diario de scores en esta sucursal.</div>
<?php else: ?>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:auto;box-shadow:var(--sh);margin-bottom:10px">
<table class="sv-tb">
<thead><tr>
    <th>Asesor</th>
    <?php foreach ($cols as $m): [$a, $mm] = explode('-', $m); ?><th><?= $MES[(int)$mm] ?> <?= substr($a, 2) ?></th><?php endforeach; ?>
    <th>Promedio</th>
</tr></thead>
<tbody>
<?php foreach ($sc as $a2):
    $v = array_map(fn($x) => (float)$x['score_prom'], $a2['m']);
    $p = $v ? array_sum($v) / count($v) : null;
?>
<tr>
    <td><?= e($a2['nombre']) ?></td>
    <?php foreach ($cols as $m): $f2 = $a2['m'][$m] ?? null; $val = $f2 ? (float)$f2['score_prom'] : null; ?>
    <td>
        <?php if ($val === null): ?><span style="color:var(--t3)">—</span>
        <?php else: ?>
        <span style="font:800 15px var(--body);color:<?= $scol($val) ?>"><?= number_format($val, 1) ?></span>
        <div style="font:400 10px var(--body);color:var(--t3)"><?= (int)$f2['score_min'] ?>–<?= (int)$f2['score_max'] ?> · <?= (int)$f2['dias'] ?>d</div>
        <?php endif; ?>
    </td>
    <?php endforeach; ?>
    <td><span style="font:800 15px var(--body);color:<?= $scol($p) ?>"><?= $p === null ? '—' : number_format($p, 1) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<div style="font:400 11.5px var(--body);color:var(--t3);line-height:1.55;margin-bottom:16px">
    El score del termómetro es una ventana móvil de 15 días que se sobrescribe; esto es el promedio
    de los puntos diarios, que sí es el mes. Bajo cada número, el rango (mínimo–máximo) y los días
    con registro: pocos días = promedio menos confiable.
</div>
<?php endif; ?>

<?php endif; // $sel ?>

<!-- ── Reporte del Director: mismo modal, mismo CSS, mismo JS ── -->
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
</style>

<script>
(function(){
  const CSRF = <?= json_encode(csrf_token()) ?>;
  let uid = 0, eid = 0;
  const $ = id => document.getElementById(id);

  document.querySelectorAll('.rt-rep-btn').forEach(b => b.addEventListener('click', () => {
    uid = parseInt(b.dataset.uid, 10);
    eid = parseInt(b.dataset.eid, 10);   // el supervisor cruza sucursales
    $('rt-modal-title').textContent = 'Reporte — ' + b.dataset.name;
    $('rt-body').innerHTML = '';
    $('rt-modal').style.display = 'block';
    rtGen();
  }));

  window.rtClose = () => { $('rt-modal').style.display = 'none'; };

  // Ventana limpia para imprimir: con 'body *{visibility:hidden}' la página
  // entera sigue paginando en blanco y el reporte sale recortado.
  window.rtPrint = function () {
    var cont = $('rt-body');
    if (!cont || !cont.innerHTML.trim()) return;
    var css = (document.getElementById('rt-css') || {}).textContent || '';
    var w = window.open('', '_blank');
    if (!w) { window.print(); return; }
    var titulo = ($('rt-modal-title') || {}).textContent || 'Reporte';
    w.document.open();
    w.document.write(
      '<!doctype html><html lang="es"><head><meta charset="utf-8">' +
      '<title>' + titulo.replace(/[<>&]/g, '') + '</title><style>' +
      ':root{--bg:#f4f4f0;--white:#fff;--border:#e2e2dc;--text:#1a1a18;' +
      '--t2:#4a4a46;--t3:#6a6a64;--g:#1a5c38;--danger:#c53030;' +
      "--body:'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',sans-serif}" +
      'html,body{margin:0;padding:0;background:#fff}' +
      '@page{margin:14mm}' +
      '#rt-modal{padding:0;position:static}#rt-modal>div{max-width:100%;margin:0;box-shadow:none}' +
      '#rt-body{padding:0}' +
      '.rr-sec,.rr-tip,.rr-pil{break-inside:avoid;page-break-inside:avoid}' +
      '*{-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
      css +
      '</style></head><body><div id="rt-modal"><div><div id="rt-body">' +
      cont.innerHTML +
      '</div></div></div></body></html>'
    );
    w.document.close();
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
        body: JSON.stringify({ asesor_id: uid, empresa_id: eid })
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
<?php
$content = ob_get_clean();
$titulo  = 'Supervisión';
require ROOT_PATH . '/core/layout.php';
