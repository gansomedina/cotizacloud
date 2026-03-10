<?php
// ============================================================
//  CotizaApp — modules/radar/index.php
//  GET /radar
// ============================================================

defined('COTIZAAPP') or die;

require_once MODULES_PATH . '/radar/Radar.php';

$empresa_id = EMPRESA_ID;
$usuario    = Auth::usuario();
$es_admin   = Auth::es_admin();

// ── Aprender IP del asesor que abre el radar ──────────────────
// Equivalente a register_internal_ip() del mu-plugin On Time.
// La IP queda en radar_ips_internas → sus vistas futuras de cotizaciones
// se excluyen automáticamente del scoring.
$ip_actual = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
$ip_actual = trim(explode(',', $ip_actual)[0]);
if ($ip_actual !== '') {
    Radar::aprender_ip_radar($empresa_id, $ip_actual);
}

// Solo ver las suyas si no es admin
$uid_filtro = (!$es_admin && !Auth::puede('ver_todas_cots')) ? Auth::id() : null;

// ─── Recalcular (throttle manual sin APCu) ───────────────────
// Usamos radar_updated_at de la empresa para no recalcular más de 1 vez por minuto
$ultima_calc = DB::val(
    "SELECT MAX(radar_updated_at) FROM cotizaciones WHERE empresa_id=?",
    [$empresa_id]
);
$hace_un_minuto = date('Y-m-d H:i:s', time() - 60);
if (!$ultima_calc || $ultima_calc < $hace_un_minuto) {
    try {
        Radar::check_auto_calibrar($empresa_id);
        Radar::recalcular_empresa($empresa_id);
    } catch (Exception $e) {
        // silencioso
    }
}

// ─── Datos ──────────────────────────────────────────────────
$cots = Radar::lista_activas($empresa_id, $uid_filtro);
$config = Radar::config($empresa_id);

// ─── Agrupar por bucket ──────────────────────────────────────
$por_bucket = ['onfire' => [], 'inminente' => [], 'probable' => [], 'sin_bucket' => []];
foreach ($cots as $c) {
    $b = $c['radar_bucket'] ?? 'sin_bucket';
    if (!array_key_exists($b, $por_bucket)) $b = 'sin_bucket';
    $por_bucket[$b][] = $c;
}

// ─── Calibración activa ──────────────────────────────────────
$cal = DB::row(
    "SELECT * FROM radar_fit_calibracion WHERE empresa_id=? AND activa=1 ORDER BY created_at DESC LIMIT 1",
    [$empresa_id]
);

// ─── IPs internas ────────────────────────────────────────────
$ips_internas = DB::query("SELECT * FROM radar_ips_internas WHERE empresa_id=? ORDER BY created_at DESC", [$empresa_id]);

// ─── Helpers ────────────────────────────────────────────────
function ini_r2(string $n): string {
    $p = array_filter(explode(' ', $n));
    $i = ''; foreach (array_slice($p,0,2) as $w) $i .= strtoupper($w[0]);
    return $i ?: '?';
}
function fmt_r2(float $n): string {
    if ($n >= 1_000_000) return '$' . number_format($n/1_000_000,1) . 'M';
    if ($n >= 1_000)     return '$' . number_format($n/1_000,0) . 'K';
    return '$' . number_format($n,0);
}
function heat_dots(int $score): string {
    $html = '<div class="heat">';
    $hot  = min(5, (int)round($score/20));
    for ($i = 0; $i < 5; $i++) {
        if ($i < $hot) {
            $cls = $score >= 80 ? 'hd-hot' : ($score >= 60 ? 'hd-mid' : 'hd-low');
        } else { $cls = 'hd-off'; }
        $html .= '<div class="heat-dot ' . $cls . '"></div>';
    }
    return $html . '</div>';
}
function score_color(int $score): string {
    if ($score >= 80) return '#ef4444';
    if ($score >= 60) return '#f97316';
    if ($score >= 35) return '#f59e0b';
    return 'var(--t3)';
}

$page_title = 'Radar';
ob_start();
?>
<style>
.slabel{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t2);margin:24px 0 10px;display:flex;align-items:center;gap:10px}
.slabel::after{content:'';flex:1;height:1.5px;background:var(--border)}
.slabel:first-child{margin-top:0}

/* Tabs */
.rtabs{display:flex;gap:0;background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:20px;flex-wrap:wrap}
.rtab{padding:10px 18px;font:600 13px var(--body);color:var(--t2);cursor:pointer;border:none;background:none;border-right:1px solid var(--border);transition:all .12s;display:flex;align-items:center;gap:6px}
.rtab:last-child{border-right:none}
.rtab.on{background:var(--g);color:#fff}
.rtab:hover:not(.on){background:var(--bg)}
.rtab-count{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:9px;font:700 11px var(--body)}
.rtab.on .rtab-count{background:rgba(255,255,255,.25);color:#fff}
.rtab:not(.on) .rtab-count{background:var(--bg);color:var(--t2);border:1px solid var(--border)}
.tab-panel{display:none}.tab-panel.on{display:block}

/* Tabla radar */
.radar-tbl{width:100%;border-collapse:collapse}
.radar-tbl th{font:700 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);padding:8px 14px;border-bottom:2px solid var(--border);background:var(--bg);text-align:left;white-space:nowrap}
.radar-tbl th.r{text-align:right}
.radar-tbl td{padding:10px 14px;border-bottom:1px solid var(--border);vertical-align:top}
.radar-tbl tr:last-child td{border-bottom:none}
.radar-tbl tr:hover td{background:#fafaf8;cursor:pointer}

.av-cel{display:flex;align-items:center;gap:10px}
.av-dot{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font:700 12px var(--body);color:#fff;flex-shrink:0}
.client-name{font:600 14px var(--body);line-height:1.3}
.client-meta{font:400 11px var(--num);color:var(--t3);margin-top:1px}

.score-val{font:500 18px var(--num);line-height:1}
.score-sub{font:400 10px var(--body);color:var(--t3);margin-top:2px;letter-spacing:.04em;text-transform:uppercase}

.senales-list{display:flex;flex-direction:column;gap:2px}
.senal{display:inline-flex;align-items:center;gap:5px;padding:2px 7px;border-radius:4px;font:500 11px var(--body);white-space:nowrap}
.s-pos{background:var(--g-light);color:var(--g)}
.s-neg{background:var(--danger-bg);color:var(--danger)}
.s-fit{background:var(--blue-bg);color:var(--blue)}
.s-neu{background:var(--bg);color:var(--t2);border:1px solid var(--border)}

.heat{display:flex;gap:3px;align-items:center}
.heat-dot{width:7px;height:7px;border-radius:50%}
.hd-off{background:var(--border)}.hd-low{background:#fbbf24}.hd-mid{background:#f97316}.hd-hot{background:#ef4444}

.bucket-header-row{background:var(--bg);border-bottom:1px solid var(--border)}
.bucket-header-cell{padding:8px 14px;font:700 12px var(--body);display:flex;align-items:center;gap:8px}
.bh-total{font:400 12px var(--num);color:var(--t3);margin-left:auto}

/* Empty state */
.radar-empty{text-align:center;padding:40px 20px}
.radar-empty-ico{font-size:40px;margin-bottom:12px}
.radar-empty-tit{font:700 16px var(--body);margin-bottom:6px}
.radar-empty-sub{font:400 13px var(--body);color:var(--t3);max-width:400px;margin:0 auto;line-height:1.6}

/* Actividad inusual (IPs) */
.ip-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:12px}
.ip-row{display:flex;align-items:center;gap:12px;padding:11px 14px;border-bottom:1px solid var(--border)}
.ip-row:last-child{border-bottom:none}
.ip-badge{padding:2px 8px;border-radius:4px;font:700 10px var(--body);background:var(--purple-bg);color:var(--purple);flex-shrink:0}
.ip-info{flex:1;min-width:0}
.ip-addr{font:600 13px var(--num)}
.ip-meta{font:400 11px var(--body);color:var(--t3);margin-top:1px}
.ip-r{text-align:right;flex-shrink:0}
.ip-cots{font:700 13px var(--num);color:var(--purple)}
.ip-sub{font:400 10px var(--body);color:var(--t3);margin-top:1px}

/* Config Radar (en tab Settings) */
.modo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px}
.modo-opt{padding:14px 16px;border:1.5px solid var(--border);border-radius:var(--r-sm);cursor:pointer;transition:all .12s;background:var(--white)}
.modo-opt:hover{border-color:var(--border2)}
.modo-opt.sel{border-color:var(--g);background:var(--g-bg)}
.modo-opt-tit{font:700 14px var(--body);margin-bottom:4px}
.modo-opt-sub{font:400 12px var(--body);color:var(--t3);line-height:1.5}

.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border)}
.toggle-row:last-child{border-bottom:none}
.toggle-lbl{font:600 13px var(--body)}
.toggle-sub{font:400 12px var(--body);color:var(--t3);margin-top:2px}
.toggle{position:relative;display:inline-block;width:42px;height:24px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0}
.toggle-track{position:absolute;inset:0;background:var(--border2);border-radius:12px;transition:background .2s}
.toggle input:checked + .toggle-track{background:var(--g)}
.toggle-thumb{position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:left .2s;pointer-events:none;box-shadow:0 1px 3px rgba(0,0,0,.18)}
.toggle input:checked ~ .toggle-thumb{left:21px}

.cal-bandas{display:grid;grid-template-columns:repeat(5,1fr);gap:8px}
.banda-cel{background:var(--bg);border-radius:var(--r-sm);padding:10px;text-align:center}
.banda-lbl{font:700 10px var(--body);letter-spacing:.05em;text-transform:uppercase;color:var(--t3);margin-bottom:4px}
.banda-val{font:700 16px var(--num);color:var(--text)}
.banda-sub{font:400 10px var(--num);color:var(--t3);margin-top:2px}

/* IPs internas manager */
.ips-list{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden}
.ip-int-row{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--border)}
.ip-int-row:last-child{border-bottom:none}
.ip-int-addr{font:500 14px var(--num);flex:1}
.ip-int-lbl{font:400 12px var(--body);color:var(--t3)}
.ip-int-del{background:none;border:none;color:var(--danger);cursor:pointer;font-size:13px;padding:4px 8px;border-radius:4px}
.ip-int-del:hover{background:var(--danger-bg)}

@media(max-width:900px){
  .modo-grid{grid-template-columns:1fr}
  .cal-bandas{grid-template-columns:repeat(3,1fr)}
  .radar-tbl .col-senales{display:none}
}
@media(max-width:600px){
  .rtab{padding:8px 12px;font-size:12px}
  .cal-bandas{grid-template-columns:repeat(2,1fr)}
}
</style>

<!-- Tabs -->
<div class="rtabs" id="radarTabs">
  <?php
  $tab_defs = [
      'onfire'    => ['🔴', 'On Fire',          count($por_bucket['onfire'])],
      'inminente' => ['🟠', 'Inminente',         count($por_bucket['inminente'])],
      'probable'  => ['🟡', 'Probable',          count($por_bucket['probable'])],
      'todos'     => ['📡', 'Todos',             count($cots)],
      'ruido'     => ['🔍', 'Actividad inusual', null],
      'config'    => ['⚙️', 'Configuración',     null],
  ];
  $tab_activo = array_key_first(array_filter($por_bucket, fn($b) => !empty($b)));
  $tab_activo = $tab_activo ?? 'todos';
  foreach ($tab_defs as $tid => [$tco, $tnm, $tcnt]):
  ?>
  <button class="rtab <?= $tid===$tab_activo?'on':'' ?>" onclick="rTab('<?= $tid ?>',this)">
    <?= $tco ?> <?= $tnm ?>
    <?php if ($tcnt !== null && $tcnt > 0): ?>
    <span class="rtab-count"><?= $tcnt ?></span>
    <?php endif; ?>
  </button>
  <?php endforeach; ?>
</div>

<!-- ══ TAB: BUCKETS (on fire / inminente / probable / todos) ══ -->
<?php
foreach (['onfire','inminente','probable','todos'] as $tid):
    $items = $tid === 'todos' ? $cots : $por_bucket[$tid];
    $activo = $tid === $tab_activo;

    $bucket_meta = [
        'onfire'    => ['color'=>'#991b1b', 'bg'=>'#fff1f2', 'ico'=>'🔴', 'lbl'=>'On Fire'],
        'inminente' => ['color'=>'#c2410c', 'bg'=>'#fff7ed', 'ico'=>'🟠', 'lbl'=>'Inminente'],
        'probable'  => ['color'=>'var(--g)', 'bg'=>'#fffbeb', 'ico'=>'🟡', 'lbl'=>'Probable'],
        'todos'     => ['color'=>'var(--g)', 'bg'=>'var(--g-bg)', 'ico'=>'📡', 'lbl'=>'Activas'],
    ];
    $bm = $bucket_meta[$tid];
?>
<div class="tab-panel <?= $activo?'on':'' ?>" id="tab-<?= $tid ?>">

  <?php if (empty($items)): ?>
  <div class="card">
    <div class="radar-empty">
      <div class="radar-empty-ico"><?= $bm['ico'] ?></div>
      <div class="radar-empty-tit">Sin cotizaciones en <?= $bm['lbl'] ?></div>
      <div class="radar-empty-sub">
        <?php if ($tid === 'onfire'): ?>
          Cuando un prospecto revise una cotización varias veces con señales fuertes de intención, aparecerá aquí.
        <?php elseif ($tid === 'inminente'): ?>
          Las cotizaciones con actividad sostenida y señales claras de decisión aparecerán aquí.
        <?php elseif ($tid === 'probable'): ?>
          Cotizaciones con actividad básica pero sin señales decisivas aún.
        <?php else: ?>
          No hay cotizaciones enviadas o vistas actualmente.
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php else: ?>

  <div class="card" style="overflow:auto">
    <table class="radar-tbl">
      <thead>
        <tr>
          <th>Cliente / Proyecto</th>
          <th class="r" style="width:80px">Score</th>
          <th style="width:90px">Calor</th>
          <th class="col-senales">Señales</th>
          <th class="r" style="width:80px">Total</th>
          <th style="width:110px">Última vista</th>
          <?php if ($tid === 'todos'): ?><th style="width:90px">Bucket</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php
      // En "todos" agrupar visualmente por bucket
      $cur_bucket = '__ninguno__';
      foreach ($items as $c):
          $ini   = ini_r2($c['cliente_nombre'] ?? '?');
          $score = (int)($c['radar_score'] ?? 0);
          $bkt   = $c['radar_bucket'] ?? null;
          $senales_raw = $c['radar_senales'] ? json_decode($c['radar_senales'], true) : [];

          // Separador de grupo en vista "todos"
          if ($tid === 'todos' && $bkt !== $cur_bucket) {
              $cur_bucket = $bkt ?? 'sin_bucket';
              $bh_map = [
                  'onfire'    => ['🔴 On Fire',          '#fff1f2'],
                  'inminente' => ['🟠 Cierre inminente',  '#fff7ed'],
                  'probable'  => ['🟡 Probable cierre',   '#fffbeb'],
                  'sin_bucket'=> ['⬜ Sin clasificar',    'var(--bg)'],
              ];
              [$bh_lbl, $bh_bg] = $bh_map[$cur_bucket] ?? ['—', 'var(--bg)'];
              $bh_items = $cur_bucket !== 'sin_bucket' ? array_filter($items, fn($x) => ($x['radar_bucket'] ?? 'sin_bucket') === $cur_bucket) : [];
              $bh_total = array_sum(array_column(iterator_to_array((function() use ($bh_items){yield from $bh_items;})(), false), 'total'));
              echo '<tr class="bucket-header-row"><td colspan="7" style="padding:0"><div class="bucket-header-cell" style="background:'.$bh_bg.'">'.$bh_lbl.'<span class="bh-total">'.fmt_r2($bh_total).'</span></div></td></tr>';
          }

          // Color de avatar según bucket
          $av_colors = ['onfire'=>'#991b1b','inminente'=>'#c2410c','probable'=>'var(--g)','sin_bucket'=>'#94a3b8'];
          $av_bg = $av_colors[$bkt ?? 'sin_bucket'];

          // Tiempo desde última vista
          $ul_vista = $c['ultima_vista_at'];
          if ($ul_vista) {
              $hace = time() - strtotime($ul_vista);
              if ($hace < 3600)     $ul_lbl = 'Hace ' . floor($hace/60) . ' min';
              elseif ($hace < 86400) $ul_lbl = 'Hace ' . floor($hace/3600) . 'h';
              else                   $ul_lbl = 'Hace ' . floor($hace/86400) . 'd';
          } else {
              $ul_lbl = '—';
          }
      ?>
      <tr onclick="window.location='/cotizaciones/<?= (int)$c['id'] ?>'">
        <td>
          <div class="av-cel">
            <div class="av-dot" style="background:<?= $av_bg ?>"><?= e($ini) ?></div>
            <div>
              <div class="client-name"><?= e($c['cliente_nombre'] ?? '—') ?></div>
              <div class="client-meta">
                <?= e(mb_substr($c['titulo'],0,42)) ?>
                · <?= e($c['numero']) ?>
                <?php if ($c['asesor_nombre'] && Auth::es_admin()): ?>
                · <span style="color:var(--blue)"><?= e($c['asesor_nombre']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </td>
        <td style="text-align:right">
          <div class="score-val" style="color:<?= score_color($score) ?>"><?= $score ?></div>
          <div class="score-sub">/ 100</div>
        </td>
        <td><?= heat_dots($score) ?></td>
        <td class="col-senales">
          <div class="senales-list">
            <?php foreach ($senales_raw as $sk => $sv):
              if ($sk === 'fit') $cls = 's-fit';
              elseif (($sv['pts'] ?? 0) < 0) $cls = 's-neg';
              else $cls = 's-pos';
            ?>
            <span class="senal <?= $cls ?>"><?= e($sv['desc'] ?? $sk) ?></span>
            <?php endforeach; ?>
            <?php if (empty($senales_raw)): ?>
            <span style="font:400 11px var(--body);color:var(--t3)">Sin señales aún</span>
            <?php endif; ?>
          </div>
        </td>
        <td style="text-align:right;font:500 14px var(--num)"><?= fmt_r2((float)$c['total']) ?></td>
        <td>
          <div style="font:500 12px var(--num);color:var(--t2)"><?= $ul_lbl ?></div>
          <div style="font:400 10px var(--body);color:var(--t3);margin-top:1px">
            <?= (int)$c['num_sesiones'] ?> sesión<?= $c['num_sesiones']!=1?'es':'' ?>
            · <?= (int)$c['num_ips'] ?> IP<?= $c['num_ips']!=1?'s':'' ?>
          </div>
        </td>
        <?php if ($tid === 'todos'): ?>
        <td>
          <?php if ($bkt): ?>
          <?php $bkt_label_map = ['onfire'=>['🔴','On Fire','#fff1f2','#ef4444'],'inminente'=>['🟠','Inminente','#fff7ed','#c2410c'],'probable'=>['🟡','Probable','#fffbeb','#b45309']]; ?>
          <?php [$bico,$blbl,$bbg,$bcol] = $bkt_label_map[$bkt] ?? ['⬜','—','var(--bg)','var(--t3)']; ?>
          <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;background:<?= $bbg ?>;font:700 11px var(--body);color:<?= $bcol ?>"><?= $bico ?> <?= $blbl ?></span>
          <?php else: ?>
          <span style="font:400 11px var(--body);color:var(--t3)">—</span>
          <?php endif; ?>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- ══ TAB: ACTIVIDAD INUSUAL ══ -->
<?php
// IPs que visitaron cotizaciones de ≥2 clientes distintos en los últimos 30 días
$ips_inusuales = DB::query(
    "SELECT qs.ip,
            COUNT(DISTINCT c.cliente_id) AS clientes_distintos,
            COUNT(DISTINCT qs.cotizacion_id) AS cots_vistas,
            MAX(qs.updated_at) AS ultima_actividad,
            GROUP_CONCAT(DISTINCT cl.nombre ORDER BY cl.nombre SEPARATOR ', ' LIMIT 3) AS clientes_nombres
     FROM quote_sessions qs
     JOIN cotizaciones c  ON c.id  = qs.cotizacion_id
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.empresa_id=?
       AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
       AND qs.ip NOT IN (SELECT ip FROM radar_ips_internas WHERE empresa_id=?)
     GROUP BY qs.ip
     HAVING clientes_distintos >= 2
     ORDER BY clientes_distintos DESC, cots_vistas DESC
     LIMIT 20",
    [$empresa_id, $empresa_id]
);
?>
<div class="tab-panel <?= $tab_activo==='ruido'?'on':'' ?>" id="tab-ruido">

  <div class="slabel">IPs con actividad inusual</div>
  <p style="font:400 13px var(--body);color:var(--t3);margin-bottom:14px;line-height:1.6">
    IPs externas que visitaron cotizaciones de 2 o más clientes distintos en los últimos 30 días. Puede ser un competidor, un familiar o alguien que usa un comparativo.
  </p>

  <?php if (empty($ips_inusuales)): ?>
  <div class="card">
    <div class="radar-empty">
      <div class="radar-empty-ico">🔍</div>
      <div class="radar-empty-tit">Sin actividad inusual</div>
      <div class="radar-empty-sub">No se detectaron IPs visitando cotizaciones de múltiples clientes distintos.</div>
    </div>
  </div>
  <?php else: ?>
  <div class="ip-card">
    <?php foreach ($ips_inusuales as $ip_row):
      $hace = time() - strtotime($ip_row['ultima_actividad']);
      $hace_lbl = $hace < 3600 ? 'Hace ' . floor($hace/60) . ' min' : ($hace < 86400 ? 'Hace ' . floor($hace/3600) . 'h' : 'Hace ' . floor($hace/86400) . 'd');
    ?>
    <div class="ip-row">
      <div class="ip-badge">IP ext.</div>
      <div class="ip-info">
        <div class="ip-addr"><?= e($ip_row['ip']) ?></div>
        <div class="ip-meta">Clientes: <?= e($ip_row['clientes_nombres']) ?> · <?= $hace_lbl ?></div>
      </div>
      <div class="ip-r">
        <div class="ip-cots"><?= (int)$ip_row['cots_vistas'] ?> cotizaciones</div>
        <div class="ip-sub"><?= (int)$ip_row['clientes_distintos'] ?> clientes distintos</div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="slabel">IPs internas registradas</div>
  <p style="font:400 13px var(--body);color:var(--t3);margin-bottom:14px;line-height:1.6">
    Estas IPs se excluyen automáticamente del cálculo de Radar.
  </p>

  <?php if (!empty($ips_internas)): ?>
  <div class="ips-list card" style="margin-bottom:14px">
    <?php foreach ($ips_internas as $ipi): ?>
    <div class="ip-int-row">
      <div class="ip-int-addr"><?= e($ipi['ip']) ?></div>
      <div class="ip-int-lbl"><?= e($ipi['descripcion'] ?? '') ?></div>
      <?php if (Auth::es_admin()): ?>
      <button class="ip-int-del" onclick="eliminarIP(<?= (int)$ipi['id'] ?>, this)" title="Eliminar">✕</button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (Auth::es_admin()): ?>
  <div style="display:flex;gap:8px;max-width:500px">
    <input type="text" id="nuevaIP" placeholder="Ej: 192.168.1.0" maxlength="45"
           style="flex:1;padding:10px 12px;border:1.5px solid var(--border);border-radius:var(--r-sm);font:400 14px var(--num);background:var(--white);outline:none"
           onfocus="this.style.borderColor='var(--g)'" onblur="this.style.borderColor='var(--border)'">
    <input type="text" id="nuevaIPDesc" placeholder="Descripción (opcional)" maxlength="80"
           style="flex:1;padding:10px 12px;border:1.5px solid var(--border);border-radius:var(--r-sm);font:400 14px var(--body);background:var(--white);outline:none"
           onfocus="this.style.borderColor='var(--g)'" onblur="this.style.borderColor='var(--border)'">
    <button onclick="agregarIP()"
            style="padding:10px 18px;background:var(--g);color:#fff;border:none;border-radius:var(--r-sm);font:700 13px var(--body);cursor:pointer">
      Agregar
    </button>
  </div>
  <?php endif; ?>

</div><!-- /tab-ruido -->

<!-- ══ TAB: CONFIGURACIÓN ══ -->
<div class="tab-panel <?= $tab_activo==='config'?'on':'' ?>" id="tab-config">

  <form id="radarConfigForm" onsubmit="guardarConfig(event)">

  <div class="slabel">Sensibilidad del radar</div>
  <p style="font:400 13px var(--body);color:var(--t3);margin-bottom:14px;line-height:1.6">
    Controla qué tan exigente es el radar para mostrar cotizaciones en cada bucket.
    <strong style="color:var(--text)">Agresivo</strong> muestra más con menos señales.
    <strong style="color:var(--text)">Ligero</strong> solo muestra las más sólidas.
  </p>

  <div class="modo-grid">
    <?php
    $modos = [
        'agresivo' => ['🔥 Agresivo', 'Más cotizaciones visibles. Ideal con volumen bajo y para no perder ninguna señal.'],
        'medio'    => ['⚖️ Medio',    'Balance entre precisión y cobertura. Recomendado para la mayoría.'],
        'ligero'   => ['🎯 Ligero',   'Solo cotizaciones con señales muy sólidas. Ideal con alto volumen.'],
    ];
    $modo_actual = $config['sensibilidad'] ?? 'medio';
    foreach ($modos as $mk => [$mtit, $msub]):
    ?>
    <div class="modo-opt <?= $mk===$modo_actual?'sel':'' ?>" onclick="selModo('<?= $mk ?>',this)" id="modo-<?= $mk ?>">
      <div class="modo-opt-tit"><?= $mtit ?></div>
      <div class="modo-opt-sub"><?= $msub ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <input type="hidden" name="sensibilidad" id="inp_sensibilidad" value="<?= e($modo_actual) ?>">

  <div class="slabel">Calibración del modelo</div>
  <p style="font:400 13px var(--body);color:var(--t3);margin-bottom:14px;line-height:1.6">
    El radar aprende de tus históricos para ajustar el score. Entre más cotizaciones y ventas, más preciso.
  </p>

  <div class="card" style="margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--border)">
      <div>
        <div style="font:600 14px var(--body);margin-bottom:3px">Última calibración</div>
        <div style="font:400 13px var(--num);color:var(--t3)">
          <?php if ($cal): ?>
            <?= date('d M Y', strtotime($cal['created_at'])) ?> —
            basado en <?= (int)$cal['num_cotizaciones'] ?> cotizaciones,
            <?= (int)$cal['num_ventas'] ?> ventas cerradas
          <?php else: ?>
            Sin calibrar aún
          <?php endif; ?>
        </div>
      </div>
      <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;background:<?= $cal?'var(--g-light)':'var(--amb-bg)' ?>;color:<?= $cal?'var(--g)':'var(--amb)' ?>;font:700 11px var(--body)">
        <span style="width:5px;height:5px;border-radius:50%;background:currentColor;flex-shrink:0"></span>
        <?= $cal ? 'Activa' : 'Sin datos' ?>
      </span>
    </div>

    <?php if ($cal): ?>
    <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
      <div style="font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t3);margin-bottom:10px">
        Tasas de cierre por banda de monto (tus datos)
      </div>
      <div class="cal-bandas">
        <?php
        $bandas = json_decode($cal['bandas_json'] ?? '[]', true);
        foreach ($bandas as $b):
          $tasa_pct = round(($b['tasa_cierre'] ?? 0) * 100, 1);
          $es_alta  = $tasa_pct > ((float)$cal['tasa_base'] * 100 * 1.1);
        ?>
        <div class="banda-cel">
          <div class="banda-lbl"><?= e($b['label']) ?></div>
          <div class="banda-val" style="color:<?= $es_alta?'var(--g)':'var(--text)' ?>"><?= $tasa_pct ?>%</div>
          <div class="banda-sub"><?= (int)($b['total'] ?? 0) ?> cots</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div style="padding:14px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div>
        <div style="font:600 13px var(--body);margin-bottom:2px">Recalibrar con datos actuales</div>
        <div style="font:400 12px var(--body);color:var(--t3)">Se recomienda recalibrar cada vez que cierres 5+ ventas nuevas</div>
      </div>
      <button type="button" onclick="recalibrar()" id="btnRecal"
              style="padding:9px 18px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 13px var(--body);color:#fff;cursor:pointer;white-space:nowrap">
        Recalibrar ahora
      </button>
    </div>
  </div>

  <!-- Calibración automática -->
  <div class="card" style="margin-bottom:20px">
    <div class="toggle-row">
      <div>
        <div class="toggle-lbl">Calibración automática</div>
        <div class="toggle-sub">El sistema recalibra solo cada vez que se acumulan 10 nuevas ventas cerradas</div>
      </div>
      <label class="toggle">
        <input type="checkbox" name="calibracion_auto" <?= ($config['calibracion_auto']??true)?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
  </div>

  <div class="slabel">Filtros de ruido <span style="font:400 11px var(--body);color:var(--t3);text-transform:none;letter-spacing:0">(se sugiere no mover)</span></div>
  <div class="card" style="margin-bottom:20px">
    <div class="toggle-row">
      <div>
        <div class="toggle-lbl">Excluir visitas del equipo interno</div>
        <div class="toggle-sub">Filtra IPs y usuarios registrados como internos automáticamente</div>
      </div>
      <label class="toggle">
        <input type="checkbox" name="excluir_internos" <?= ($config['excluir_internos']??true)?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
    <div class="toggle-row">
      <div>
        <div class="toggle-lbl">Filtrar bots conocidos</div>
        <div class="toggle-sub">Google, Bing, Meta, crawlers y scrapers comunes</div>
      </div>
      <label class="toggle">
        <input type="checkbox" name="filtrar_bots" <?= ($config['filtrar_bots']??true)?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
    <div class="toggle-row">
      <div>
        <div class="toggle-lbl">Deduplicar vistas (ventana 30 min)</div>
        <div class="toggle-sub">Vistas de la misma IP en menos de 30 min cuentan como una sola sesión</div>
      </div>
      <label class="toggle">
        <input type="checkbox" name="deduplicar_30min" <?= ($config['deduplicar_30min']??true)?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
  </div>

  <button type="submit" style="padding:13px 28px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 14px var(--body);color:#fff;cursor:pointer">
    Guardar configuración
  </button>
  <span id="confMsg" style="font:500 13px var(--body);color:var(--g);margin-left:12px;display:none">✓ Guardado</span>

  </form>
</div><!-- /tab-config -->


<script>
// ─── Tabs ────────────────────────────────────────────────
function rTab(id, el) {
    document.querySelectorAll('.rtab').forEach(t => t.classList.remove('on'));
    el.classList.add('on');
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('on'));
    document.getElementById('tab-' + id)?.classList.add('on');
}

// ─── Modo sensibilidad ───────────────────────────────────
function selModo(modo, el) {
    document.querySelectorAll('.modo-opt').forEach(o => o.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('inp_sensibilidad').value = modo;
}

// ─── Guardar config ──────────────────────────────────────
async function guardarConfig(e) {
    e.preventDefault();
    const form = document.getElementById('radarConfigForm');
    const data = {
        sensibilidad:     document.getElementById('inp_sensibilidad').value,
        calibracion_auto: form.querySelector('[name=calibracion_auto]').checked,
        excluir_internos: form.querySelector('[name=excluir_internos]').checked,
        filtrar_bots:     form.querySelector('[name=filtrar_bots]').checked,
        deduplicar_30min: form.querySelector('[name=deduplicar_30min]').checked,
    };
    try {
        const r = await fetch('/config/radar', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) });
        if (!r.ok) throw new Error();
        const msg = document.getElementById('confMsg');
        msg.style.display = 'inline';
        setTimeout(() => msg.style.display = 'none', 2500);
    } catch {
        alert('Error al guardar. Intenta de nuevo.');
    }
}

// ─── Recalibrar ──────────────────────────────────────────
async function recalibrar() {
    const btn = document.getElementById('btnRecal');
    btn.disabled = true; btn.textContent = 'Calibrando...';
    try {
        const r  = await fetch('/config/radar/calibrar', { method:'POST', headers:{'Content-Type':'application/json'} });
        const d  = await r.json();
        if (d.ok) {
            btn.textContent = '✓ Calibrado';
            setTimeout(() => location.reload(), 800);
        } else {
            btn.disabled = false; btn.textContent = 'Recalibrar ahora';
            alert(d.error || 'No hay suficientes datos para calibrar.');
        }
    } catch { btn.disabled = false; btn.textContent = 'Recalibrar ahora'; }
}

// ─── IPs internas ────────────────────────────────────────
async function agregarIP() {
    const ip  = document.getElementById('nuevaIP').value.trim();
    const desc = document.getElementById('nuevaIPDesc').value.trim();
    if (!ip) return;
    try {
        const r = await fetch('/config/ip-interna', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ ip, descripcion: desc })
        });
        if (!r.ok) throw new Error();
        location.reload();
    } catch { alert('Error al agregar IP.'); }
}

async function eliminarIP(id, btn) {
    if (!confirm('¿Eliminar esta IP interna?')) return;
    try {
        const r = await fetch('/config/ip-interna/' + id + '/eliminar', { method:'POST' });
        if (!r.ok) throw new Error();
        btn.closest('.ip-int-row').remove();
    } catch { alert('Error al eliminar.'); }
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
