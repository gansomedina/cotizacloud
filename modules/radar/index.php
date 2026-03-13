<?php
// ============================================================
//  CotizaApp — modules/radar/index.php
//  GET /radar
//  Portado fielmente de radar_3_.php (On Time / WordPress)
//  17 buckets · FIT model · filtros IP/UA · debug funnels
// ============================================================

defined('COTIZAAPP') or die;

require_once MODULES_PATH . '/radar/Radar.php';

$empresa_id = EMPRESA_ID;
$usuario    = Auth::usuario();
$es_admin   = Auth::es_admin();

// Aprender IP del asesor
$ip_actual = trim(explode(',', (string)($_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR'] ?? ''))[0]);
if ($ip_actual !== '') Radar::aprender_ip_radar($empresa_id, $ip_actual);

$uid_filtro = (!$es_admin && !Auth::puede('ver_todas_cots')) ? Auth::id() : null;

// Parametros UI
$range  = $_GET['range'] ?? 'all';
$limit  = isset($_GET['limit']) ? max(10, min(300, (int)$_GET['limit'])) : 50;
$sort   = in_array($_GET['sort'] ?? '', ['titulo','last','amount','fit','priority']) ? $_GET['sort'] : 'priority';
$dir    = in_array($_GET['dir']  ?? '', ['asc','desc']) ? $_GET['dir'] : 'desc';
$debug_mode = $es_admin && (($_GET['debug'] ?? '') === '1');

$range_secs = ['all'=>0,'48h'=>48*3600,'4h'=>4*3600,'30m'=>30*60];
$min_last   = ($range !== 'all' && isset($range_secs[$range])) ? time() - $range_secs[$range] : 0;

// Recalcular si >1 min
$ult = DB::val("SELECT MAX(radar_updated_at) FROM cotizaciones WHERE empresa_id=?", [$empresa_id]);
if (!$ult || $ult < date('Y-m-d H:i:s', time()-60)) {
    try { Radar::check_auto_calibrar($empresa_id); Radar::recalcular_empresa($empresa_id); } catch(Throwable $e){}
}

// Cargar cotizaciones
$uw = $uid_filtro ? "AND c.usuario_id=$uid_filtro" : '';
$raw = DB::query(
    "SELECT c.id, c.titulo, c.numero, c.slug, c.total, c.estado,
            c.radar_score, c.radar_bucket, c.radar_senales, c.radar_updated_at,
            c.visitas, c.ultima_vista_at, c.created_at,
            cl.nombre AS cnombre, cl.telefono AS ctel,
            u.nombre  AS asesor
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id=c.cliente_id
     LEFT JOIN usuarios  u  ON u.id=c.usuario_id
     WHERE c.empresa_id=? AND c.estado IN ('enviada','vista','aceptada','rechazada') $uw
     ORDER BY c.radar_score IS NULL ASC, c.radar_score DESC, c.ultima_vista_at DESC
     LIMIT 500",
    [$empresa_id]
);

// Helpers
function rhace(int $ts): string {
    $d=time()-$ts; if($d<60) return $d.'s'; if($d<3600) return floor($d/60).'m';
    if($d<86400) return floor($d/3600).'h'; return floor($d/86400).'d';
}
function rurlq(array $p=[]): string {
    $q=$_GET; foreach($p as $k=>$v) $q[$k]=$v; return '?'.http_build_query($q);
}
function rtdir(string $c,string $t,string $d): string {
    if($c!==$t) return 'desc'; return $d==='desc'?'asc':'desc';
}
function rmoney(float $n): string {
    if($n>=1000000) return '$'.number_format($n/1000000,1).'M';
    if($n>=1000)    return '$'.number_format($n/1000,0).'K';
    return '$'.number_format($n,0);
}

$BM = [
    'onfire'           => ['🔴','#991b1b','#fff1f2','On Fire'],
    'inminente'        => ['🟠','#c2410c','#fff7ed','Inminente'],
    'probable_cierre'  => ['🟡','#92400e','#fffbeb','Probable cierre'],
    'decision_activa'  => ['🟡','#92400e','#fffbeb','Decisión activa'],
    'validando_precio' => ['💸','#92400e','#fffbeb','Validando precio'],
    'prediccion_alta'  => ['🔮','#166534','#f0fdf4','Predicción alta'],
    'alto_importe'     => ['💰','#1d4ed8','#dbeafe','Alto importe'],
    're_enganche'      => ['🟣','#6d28d9','#ede9fe','Re-enganche'],
    'multi_persona'    => ['👥','#1d4ed8','#dbeafe','Multi-persona'],
    'revision_profunda'=> ['🧾','#1d4ed8','#dbeafe','Revisión profunda'],
    'vistas_multiples' => ['🟩','#166534','#f0fdf4','Vistas múltiples'],
    'hesitacion'       => ['🟠','#c2410c','#fff7ed','Hesitación'],
    'sobre_analisis'   => ['🟤','#64748b','#f1f5f9','Sobre-análisis'],
    'revivio'          => ['💜','#6d28d9','#ede9fe','Revivió'],
    'regreso'          => ['🟣','#6d28d9','#ede9fe','Regreso'],
    'comparando'       => ['🔘','#94a3b8','#f1f5f9','Comparando'],
    'enfriandose'      => ['🔵','#0284c7','#e0f2fe','Enfriándose'],
];
function rbadge(?string $b,?int $sc,array $BM): string {
    if(!$b) return '<span style="color:var(--t3);font-size:11px">—</span>';
    [$ico,$col,$bg,$lbl]=$BM[$b]??['⬜','#64748b','#f1f5f9',ucfirst($b)];
    $s=$sc?" · {$sc}":'';
    return "<span style='display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;font:700 11px var(--body);background:{$bg};color:{$col};white-space:nowrap'>{$ico} {$lbl}{$s}</span>";
}

// PRIORIDAD idéntica a radar_3_.php
$PRIO = ['onfire','inminente','probable_cierre','decision_activa','validando_precio',
         'prediccion_alta','re_enganche','multi_persona','revision_profunda',
         'alto_importe','vistas_multiples','hesitacion','sobre_analisis',
         'revivio','regreso','comparando','enfriandose'];

$buckets = array_fill_keys($PRIO, []);
$activos48 = [];
$rows_all  = [];
$total_all = $total_aceptadas = 0;

foreach ($raw as $c) {
    $last_ts = $c['ultima_vista_at'] ? strtotime($c['ultima_vista_at']) : 0;
    if (!$last_ts) continue;
    if ($min_last && $last_ts < $min_last) continue;

    $score    = (int)($c['radar_score'] ?? 0);
    $bucket   = $c['radar_bucket'] ?? null;
    $accepted = $c['estado'] === 'aceptada';
    $total    = (float)($c['total'] ?? 0);
    $senales  = is_string($c['radar_senales']) ? (json_decode($c['radar_senales'],true) ?? []) : [];

    $row = [
        'id'          => (int)$c['id'],
        'titulo'      => $c['titulo'] ?? '—',
        'numero'      => $c['numero'] ?? '',
        'cliente'     => $c['cnombre'] ?? '—',
        'ctel'        => $c['ctel'] ?? '',
        'total'       => $total,
        'estado'      => $c['estado'],
        'accepted'    => $accepted,
        'score'       => $score,
        'fit_pct'     => max(0,min(100,(float)$score*0.65)),
        'priority_pct'=> (float)$score,
        'bucket'      => $bucket,
        'senales'     => $senales,
        'last_ts'     => $last_ts,
        'visitas'     => (int)($c['visitas'] ?? 0),
    ];

    $rows_all[] = $row;
    $total_all++;
    if ($accepted) $total_aceptadas++;
    if ($last_ts >= time()-48*3600) $activos48[] = $row;
    if ($bucket && isset($buckets[$bucket])) $buckets[$bucket][] = $row;
}

// Función de orden
function rsort_arr(array &$arr, string $s, string $d): void {
    $m = $d==='asc'?1:-1;
    usort($arr, fn($a,$b) => match($s) {
        'titulo' => $m*strcmp($a['titulo'],$b['titulo']),
        'last'   => $m*($a['last_ts']<=>$b['last_ts']),
        'amount' => $m*($a['total']<=>$b['total']),
        'fit'    => $m*($a['fit_pct']<=>$b['fit_pct']),
        default  => ($m*($a['priority_pct']<=>$b['priority_pct'])) ?: ($b['last_ts']<=>$a['last_ts']),
    });
}

foreach ($buckets as &$bk) rsort_arr($bk,$sort,$dir); unset($bk);
rsort_arr($rows_all,$sort,$dir);
rsort_arr($activos48,$sort,$dir);
$rows_all = array_slice($rows_all,0,$limit);

$cnt_urgentes = count($buckets['onfire'])+count($buckets['inminente'])+count($buckets['probable_cierre']);
$cierre_pct   = $total_all>0 ? round(100*$total_aceptadas/$total_all,2) : 0;

// Config + IPs internas
$config = Radar::config($empresa_id);
$ips_internas = DB::query("SELECT * FROM radar_ips_internas WHERE empresa_id=? ORDER BY created_at DESC LIMIT 50",[$empresa_id]);

// Render de cada bucket
function render_bkt(string $tit, string $hint, array $items, string $s, string $d, bool $gap=false, bool $motivo=false): void {
    global $BM;
    echo "<div class='rbk'>";
    echo "<div class='rbk-hd'><span class='rbk-tit'>".htmlspecialchars($tit)."</span><span class='rbk-n'>".count($items)."</span></div>";
    echo "<div class='rbk-hint'>".htmlspecialchars($hint)."</div>";
    if (!$items) { echo "<div class='rbk-em'>Sin registros.</div></div>"; return; }
    $items = array_slice($items,0,12);
    echo "<div class='rdrs'><table class='rdrt'><thead><tr>";
    echo "<th>Título / Cliente</th>";
    if ($motivo) echo "<th style='width:100px'>Motivo</th>";
    echo "<th class='tc' style='width:72px'>Estado</th>";
    echo "<th class='tr' style='width:70px'><a href='".rurlq(['sort'=>'fit','dir'=>rtdir($s,'fit',$d)])."'>Score%</a></th>";
    echo "<th class='tr' style='width:70px'><a href='".rurlq(['sort'=>'priority','dir'=>rtdir($s,'priority',$d)])."'>Prior%</a></th>";
    echo "<th class='tr' style='width:68px'><a href='".rurlq(['sort'=>'amount','dir'=>rtdir($s,'amount',$d)])."'>Importe</a></th>";
    echo "<th style='width:120px'><a href='".rurlq(['sort'=>'last','dir'=>rtdir($s,'last',$d)])."'>Última vista</a></th>";
    echo "<th style='width:55px'>Ver</th>";
    echo "</tr></thead><tbody>";
    foreach ($items as $r) {
        $ago = time()-$r['last_ts'];
        $rc = $ago<1800?'hot30':($ago<14400?'hot4h':'');
        $ab = $r['accepted']?"<span class='bok'>ACCEPTED</span>":"<span class='bno'>".$r['estado']."</span>";
        echo "<tr class='$rc'>";
        echo "<td><div class='rtit'>".htmlspecialchars($r['titulo'])."</div><div class='rsub'>".htmlspecialchars($r['cliente'])."</div></td>";
        if ($motivo) echo "<td><span class='rmot'>".htmlspecialchars($r['reason']??'')."</span></td>";
        echo "<td class='tc'>$ab</td>";
        echo "<td class='tr'><b>".number_format($r['fit_pct'],1)."%</b></td>";
        echo "<td class='tr'><b>".number_format($r['priority_pct'],1)."%</b></td>";
        echo "<td class='tr'>".rmoney($r['total'])."</td>";
        $last_fmt = date('m-d H:i',$r['last_ts'])." <span class='ago'>(".rhace($r['last_ts']).")</span>";
        if ($gap && isset($r['gap_days'])) $last_fmt .= " <b style='color:#6a1b9a'>gap ".(int)$r['gap_days']."d</b>";
        echo "<td>$last_fmt</td>";
        echo "<td><a href='/cotizaciones/".(int)$r['id']."' class='rlnk'>Editar</a></td>";
        echo "</tr>";
    }
    echo "</tbody></table></div></div>";
}

$page_title = 'Radar';
ob_start();
?>
<style>
.rdr-bar{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
.rdr-bt{padding:7px 13px;border-radius:20px;border:1px solid var(--border);background:var(--white);font:600 12px var(--body);color:var(--t2);cursor:pointer;white-space:nowrap;transition:all .12s;text-decoration:none}
.rdr-bt.active,.rdr-bt:hover:not(.active){background:var(--g);border-color:var(--g);color:#fff}
.rdr-bt:hover:not(.active){opacity:.85}
.rdr-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
.rdr-sv{font:800 22px var(--num);margin-bottom:2px}
.rdr-sl{font:500 11px var(--body);color:var(--t3);text-transform:uppercase;letter-spacing:.06em}

.rtabs{display:flex;gap:0;background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:20px;flex-wrap:wrap}
.rtab{padding:10px 16px;font:600 13px var(--body);color:var(--t2);cursor:pointer;border:none;background:none;border-right:1px solid var(--border);transition:all .12s;display:flex;align-items:center;gap:6px}
.rtab:last-child{border-right:none}
.rtab.on{background:var(--g);color:#fff}
.rtab:hover:not(.on){background:var(--bg)}
.rtab-c{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:9px;font:700 11px var(--body)}
.rtab.on .rtab-c{background:rgba(255,255,255,.25);color:#fff}
.rtab:not(.on) .rtab-c{background:var(--bg);color:var(--t2);border:1px solid var(--border)}
.tab-panel{display:none}.tab-panel.on{display:block}

.rbk{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:12px}
.rbk-hd{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--border);background:var(--bg)}
.rbk-tit{font:700 13px var(--body)}
.rbk-n{font:700 13px var(--num);color:var(--t3)}
.rbk-hint{padding:6px 16px;font:400 11px var(--body);color:var(--t3);border-bottom:1px solid var(--border);background:#fafaf8}
.rbk-em{padding:14px 16px;font:400 13px var(--body);color:var(--t3)}

.rdrs{overflow-x:auto}
.rdrt{width:100%;border-collapse:collapse;min-width:520px}
.rdrt th{font:700 10px var(--body);letter-spacing:.06em;text-transform:uppercase;color:var(--t3);padding:7px 12px;border-bottom:1.5px solid var(--border);background:var(--bg);white-space:nowrap}
.rdrt th a{color:inherit;text-decoration:none}.rdrt th a:hover{text-decoration:underline}
.rdrt td{padding:8px 12px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle}
.rdrt tr:last-child td{border-bottom:none}
.rdrt tr.hot30{background:#ffd9d9;font-weight:600}
.rdrt tr.hot4h{background:#fff7cc}
.rdrt tr:hover td{background:#fafaf8;cursor:pointer}
.tc{text-align:center}.tr{text-align:right}
.rtit{font:600 13px var(--body);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px}
.rsub{font:400 11px var(--body);color:var(--t3);margin-top:1px}
.ago{color:#888;font-size:11px}
.rmot{font:500 11px var(--body);color:var(--t2)}
.bok{display:inline-block;padding:1px 6px;border-radius:8px;font:700 10px var(--body);background:#dcfce7;border:1px solid #86efac;color:#166534}
.bno{display:inline-block;padding:1px 6px;border-radius:8px;font:700 10px var(--body);background:var(--bg);border:1px solid var(--border);color:var(--t2)}
.rlnk{font:600 12px var(--body);color:var(--g);text-decoration:none}
.rlnk:hover{text-decoration:underline}

.modo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px}
.modo-opt{padding:14px 16px;border:1.5px solid var(--border);border-radius:var(--r-sm);cursor:pointer;transition:all .12s;background:var(--white)}
.modo-opt:hover{border-color:var(--border2)}
.modo-opt.sel{border-color:var(--g);background:var(--g-bg)}
.modo-opt-tit{font:700 14px var(--body);margin-bottom:4px}
.modo-opt-sub{font:400 12px var(--body);color:var(--t3);line-height:1.5}
.tog-row{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border)}
.tog-row:last-child{border-bottom:none}
.tog-lbl{font:600 13px var(--body)}
.tog-sub{font:400 12px var(--body);color:var(--t3);margin-top:2px}
.tog{position:relative;display:inline-block;width:42px;height:24px;flex-shrink:0}
.tog input{opacity:0;width:0;height:0}
.tog-track{position:absolute;inset:0;background:var(--border2);border-radius:12px;transition:background .2s}
.tog input:checked + .tog-track{background:var(--g)}
.tog-thumb{position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:left .2s;pointer-events:none;box-shadow:0 1px 3px rgba(0,0,0,.18)}
.tog input:checked ~ .tog-thumb{left:21px}
.ip-irow{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--border)}
.ip-irow:last-child{border-bottom:none}
@media(max-width:760px){.rdr-stats{grid-template-columns:repeat(2,1fr)}.modo-grid{grid-template-columns:1fr}}
</style>

<!-- Cabecera -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div>
    <h1 style="font:800 22px var(--body);letter-spacing:-.02em">📡 Radar</h1>
    <p style="font:400 13px var(--body);color:var(--t3);margin-top:3px">
      Total: <?= $total_all ?> · Aceptadas: <?= $total_aceptadas ?> · Cierre global: <b><?= $cierre_pct ?>%</b>
    </p>
  </div>
  <?php if ($debug_mode): ?><span style="padding:4px 10px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;font:700 11px var(--body);color:#92400e">DEBUG ON</span><?php endif; ?>
</div>

<!-- Stats -->
<div class="rdr-stats">
  <div class="card" style="padding:12px 16px">
    <div class="rdr-sv" style="color:#991b1b"><?= $cnt_urgentes ?></div>
    <div class="rdr-sl">🔥 Alta prioridad</div>
  </div>
  <div class="card" style="padding:12px 16px">
    <div class="rdr-sv"><?= count($activos48) ?></div>
    <div class="rdr-sl">⏱ Activos 48h</div>
  </div>
  <div class="card" style="padding:12px 16px">
    <div class="rdr-sv"><?= $total_aceptadas ?></div>
    <div class="rdr-sl">✅ Aceptadas</div>
  </div>
  <div class="card" style="padding:12px 16px">
    <div class="rdr-sv"><?= $cierre_pct ?>%</div>
    <div class="rdr-sl">📊 Tasa cierre</div>
  </div>
</div>

<!-- Tabs -->
<div class="rtabs" id="radarTabs">
  <button class="rtab on" onclick="rTab('urgentes',this)">
    🔥 Alta prioridad
    <?php if ($cnt_urgentes>0): ?><span class="rtab-c"><?= $cnt_urgentes ?></span><?php endif; ?>
  </button>
  <button class="rtab" onclick="rTab('buckets',this)">
    📡 Todos los buckets
    <?php if (count($activos48)>0): ?><span class="rtab-c"><?= count($activos48) ?></span><?php endif; ?>
  </button>
  <button class="rtab" onclick="rTab('ranking',this)">📋 Ranking</button>
  <button class="rtab" onclick="rTab('config',this)">⚙️ Config</button>
</div>

<!-- Filtros -->
<div class="rdr-bar">
  <a href="<?= rurlq(['range'=>'all']) ?>"  class="rdr-bt <?= $range==='all'?'active':'' ?>">Todas</a>
  <a href="<?= rurlq(['range'=>'48h']) ?>" class="rdr-bt <?= $range==='48h'?'active':'' ?>">48 horas</a>
  <a href="<?= rurlq(['range'=>'4h']) ?>"  class="rdr-bt <?= $range==='4h'?'active':'' ?>">4 horas</a>
  <a href="<?= rurlq(['range'=>'30m']) ?>" class="rdr-bt <?= $range==='30m'?'active':'' ?>">30 min</a>
  <form method="get" style="display:flex;align-items:center;gap:8px;margin:0">
    <?php foreach($_GET as $k=>$v): if($k==='limit') continue; ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endforeach; ?>
    <input type="number" name="limit" value="<?= $limit ?>" min="10" max="300"
           style="width:68px;padding:6px 10px;border:1px solid var(--border);border-radius:var(--r-sm);font:500 13px var(--num)">
    <button class="rdr-bt" style="border-radius:var(--r-sm);padding:7px 14px">Actualizar</button>
    <?php if ($es_admin): ?>
    <a href="<?= rurlq(['debug'=>$debug_mode?'0':'1']) ?>" class="rdr-bt <?= $debug_mode?'active':'' ?>">Debug</a>
    <?php endif; ?>
  </form>
</div>

<!-- ===== TAB: ALTA PRIORIDAD ===== -->
<div class="tab-panel on" id="tab-urgentes">
<?php
render_bkt('🔥😱 ON FIRE',
    'Actividad en 72h · 2+ sesiones · scroll ≥ 90% · lectura real · foco en precio · validación por visitor',
    $buckets['onfire'],$sort,$dir);

render_bkt('🔥 Cierre inminente',
    'Actividad en 24h · FIT ≥ 8.5% · edad ≥ 2h · guest ≥ 1 · mínimo 2 señales (≥1 fuerte) · misma huella insistiendo en precio',
    $buckets['inminente'],$sort,$dir);

render_bkt('🔥 Probable cierre (PRIORIDAD)',
    'Ventana: últimas 24h + momentum (1+ vistas/24h o 2+ vistas/7d)',
    $buckets['probable_cierre'],$sort,$dir,false,true);

render_bkt('💸 Validando precio',
    'Detecta foco real en precio: exige base guest + validación individual (misma huella) o compartida (multi-visitor)',
    $buckets['validando_precio'],$sort,$dir);

render_bkt('🔮 Predicción alta',
    'FIT ≥ 14% y cotización reciente (30 días)',
    $buckets['prediccion_alta'],$sort,$dir);
?>
</div>

<!-- ===== TAB: TODOS LOS BUCKETS ===== -->
<div class="tab-panel" id="tab-buckets">
<?php
render_bkt('🧠 Decisión activa',
    '4+ vistas en 48h y regresos reales (span ≥ 6h)',
    $buckets['decision_activa'],$sort,$dir);

render_bkt('💰 Alto importe 48h',
    'Importe ≥ $120,000 y vista en últimas 48h',
    $buckets['alto_importe'],$sort,$dir);

render_bkt('🟣 Re-enganche decisivo',
    'Gap ≥ 4d y last < 168h + (guest_24h ≥ 1 o vistas24 ≥ 1) + FIT% ≥ 5%',
    $buckets['re_enganche'],$sort,$dir,true);

render_bkt('👥 Revisión multi-persona',
    'Last < 72h + 2+ visitors o IPs post primer guest/90min + guest_total ≥ 2',
    $buckets['multi_persona'],$sort,$dir);

render_bkt('🧾 Revisión profunda',
    'Exige lectura real (visible) y foco en precio/totales. Análisis serio, no solo muchas vistas.',
    $buckets['revision_profunda'],$sort,$dir);

render_bkt('🟩 Vistas múltiples',
    '(2+ IPs en 24h) O (3+ vistas en 24h) y última vista en 24h',
    $buckets['vistas_multiples'],$sort,$dir);

render_bkt('🟠 Hesitación',
    'Pausa entre 24h y 7d, con repetición guest limitada y al menos una señal de fricción real en precio/totales',
    $buckets['hesitacion'],$sort,$dir);

render_bkt('🟤 Sobre-análisis',
    'guest_total ≥ 8, sesiones ≥ 20, edad ≥ 7d, last < 21d + poca expansión post guest',
    $buckets['sobre_analisis'],$sort,$dir);

render_bkt('💜 Revivió cotización vieja (señal exclusiva)',
    'Volvió tras 30+ días sin verla y última vista en 48h',
    $buckets['revivio'],$sort,$dir,true);

render_bkt('🟣 Regreso después de +4 días (señal exclusiva)',
    'Volvió tras 4+ días sin verla y última vista en 48h',
    $buckets['regreso'],$sort,$dir,true);

render_bkt('🟠 Comparando / Compartiendo (señal exclusiva)',
    '2+ IPs distintas en 24h y última vista en 24h',
    $buckets['comparando'],$sort,$dir);

// Enfriándose con motivo
$cooling = $buckets['enfriandose'];
foreach ($cooling as &$cr) {
    $keys = is_array($cr['senales']) ? array_keys($cr['senales']) : [];
    $with_p = array_intersect(['price_loop','tot_rev','tot_view','cupon','sv_price','mv_price'], $keys);
    $cr['reason'] = count($with_p) ? '💸 con precio' : '🧊 sin precio';
}
unset($cr);
render_bkt('🔵 Enfriándose (señal exclusiva)',
    'Tuvo 4+ vistas históricas pero no se ha visto en 48h. Distingue si ya había foco en precio o no.',
    $cooling,$sort,$dir,false,true);

render_bkt('🟡 Activos 48h (todos los activos)',
    'Lista completa de todo lo visto en las últimas 48 horas',
    $activos48,$sort,$dir);
?>
</div>

<!-- ===== TAB: RANKING ===== -->
<div class="tab-panel" id="tab-ranking">
<div class="rbk">
  <div class="rbk-hd">
    <span class="rbk-tit">Ranking general — <?= count($rows_all) ?> cotizaciones (orden: <b>Prioridad%</b>)</span>
    <span class="rbk-n"><?= $total_all ?> total</span>
  </div>
  <div class="rdrs"><table class="rdrt">
    <thead><tr>
      <th><a href="<?= rurlq(['sort'=>'titulo','dir'=>rtdir($sort,'titulo',$dir)]) ?>">Título</a> / <a href="<?= rurlq(['sort'=>'amount','dir'=>rtdir($sort,'amount',$dir)]) ?>">Importe</a></th>
      <th class="tc" style="width:72px">Estado</th>
      <th class="tr" style="width:70px"><a href="<?= rurlq(['sort'=>'fit','dir'=>rtdir($sort,'fit',$dir)]) ?>">Score%</a></th>
      <th class="tr" style="width:74px"><a href="<?= rurlq(['sort'=>'priority','dir'=>rtdir($sort,'priority',$dir)]) ?>">Prior%</a></th>
      <th class="tr" style="width:56px">Vistas</th>
      <th style="width:120px"><a href="<?= rurlq(['sort'=>'last','dir'=>rtdir($sort,'last',$dir)]) ?>">Última vista</a></th>
      <th style="width:115px">Bucket</th>
      <th style="width:55px">Ver</th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows_all as $r):
      $ago2=time()-$r['last_ts'];
      $rc=$ago2<1800?'hot30':($ago2<14400?'hot4h':'');
      $ab=$r['accepted']?"<span class='bok'>ACCEPTED</span>":"<span class='bno'>".$r['estado']."</span>";
    ?>
    <tr class="<?= $rc ?>" onclick="window.location='/cotizaciones/<?= (int)$r['id'] ?>'">
      <td><div class="rtit"><?= e($r['titulo']) ?></div><div class="rsub"><?= e($r['cliente']) ?></div></td>
      <td class="tc"><?= $ab ?></td>
      <td class="tr"><b><?= number_format($r['fit_pct'],1) ?>%</b></td>
      <td class="tr"><b><?= number_format($r['priority_pct'],1) ?>%</b></td>
      <td class="tr"><?= $r['visitas']>0 ? '<b>'.$r['visitas'].'</b>' : '—' ?></td>
      <td><?= date('m-d H:i',$r['last_ts']) ?> <span class="ago">(<?= rhace($r['last_ts']) ?>)</span></td>
      <td><?= rbadge($r['bucket'],$r['score'],$BM) ?></td>
      <td><a href="/cotizaciones/<?= (int)$r['id'] ?>" class="rlnk">Editar</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
</div>

<!-- ===== TAB: CONFIG ===== -->
<div class="tab-panel" id="tab-config">

<div class="card" style="margin-bottom:16px">
  <div style="font:700 14px var(--body);margin-bottom:12px">Sensibilidad del motor</div>
  <div class="modo-grid">
    <?php
    $modos = [
        'agresivo' => ['Agresivo', 'Más cotizaciones clasificadas. Umbrales permisivos. Útil al inicio.'],
        'medio'    => ['Medio ✓',  'Valores calibrados del radar On Time. Recomendado.'],
        'ligero'   => ['Ligero',   'Solo señales sólidas. Reduce falsos positivos.'],
    ];
    $modo_actual = $config['sensibilidad'] ?? 'medio';
    foreach ($modos as $k => [$tit,$sub]):
    ?>
    <div class="modo-opt <?= $k===$modo_actual?'sel':'' ?>" onclick="setModo('<?= $k ?>',this)">
      <div class="modo-opt-tit"><?= $tit ?></div>
      <div class="modo-opt-sub"><?= $sub ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="tog-row">
    <div>
      <div class="tog-lbl">Filtrar bots</div>
      <div class="tog-sub">Excluye visitas de Google, Bing, Meta y otros crawlers</div>
    </div>
    <label class="tog"><input type="checkbox" <?= ($config['filtrar_bots']??true)?'checked':'' ?> onchange="togCfg('filtrar_bots',this.checked)"><div class="tog-track"></div><div class="tog-thumb"></div></label>
  </div>
  <div class="tog-row">
    <div>
      <div class="tog-lbl">Excluir internos</div>
      <div class="tog-sub">Ignora visitas de IPs internas y del equipo</div>
    </div>
    <label class="tog"><input type="checkbox" <?= ($config['excluir_internos']??true)?'checked':'' ?> onchange="togCfg('excluir_internos',this.checked)"><div class="tog-track"></div><div class="tog-thumb"></div></label>
  </div>
  <div class="tog-row">
    <div>
      <div class="tog-lbl">Deduplicar 30 min</div>
      <div class="tog-sub">Una vista por IP cada 30 min (más preciso)</div>
    </div>
    <label class="tog"><input type="checkbox" <?= ($config['deduplicar_30min']??true)?'checked':'' ?> onchange="togCfg('deduplicar_30min',this.checked)"><div class="tog-track"></div><div class="tog-thumb"></div></label>
  </div>
</div>

<div class="card">
  <div style="font:700 14px var(--body);margin-bottom:6px">IPs internas (excluidas del radar)</div>
  <div style="font:400 12px var(--body);color:var(--t3);margin-bottom:12px">
    Tu IP actual <b><?= e($ip_actual) ?></b> se registró automáticamente al abrir el radar.
  </div>
  <?php if (empty($ips_internas)): ?>
  <div style="padding:14px;color:var(--t3);font:400 13px var(--body)">Sin IPs registradas.</div>
  <?php else: ?>
  <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
    <?php foreach ($ips_internas as $ip): ?>
    <div class="ip-irow">
      <span style="font:500 14px var(--num);flex:1"><?= e($ip['ip']) ?></span>
      <span style="font:400 12px var(--body);color:var(--t3)"><?= e($ip['fuente']??'') ?> · <?= date('d M Y',strtotime($ip['created_at'])) ?></span>
      <?php if ($es_admin): ?>
      <button onclick="delIp(<?= (int)$ip['id'] ?>,this)" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:13px;padding:4px 8px;border-radius:4px">✕</button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php if ($es_admin): ?>
  <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
    <input type="text" id="newIp" placeholder="Agregar IP manualmente…"
           style="flex:1;min-width:160px;padding:8px 12px;border:1px solid var(--border);border-radius:var(--r-sm);font:400 13px var(--body)">
    <button class="btn btn-primary" onclick="addIp()">Agregar</button>
  </div>
  <?php endif; ?>
</div>

</div><!-- /tab-config -->

<script>
const CSRF_R='<?= csrf_token() ?>';
function rTab(id,btn){
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('on'));
  document.querySelectorAll('.rtab').forEach(b=>b.classList.remove('on'));
  const p=document.getElementById('tab-'+id);
  if(p) p.classList.add('on');
  if(btn) btn.classList.add('on');
}
function setModo(modo,el){
  document.querySelectorAll('.modo-opt').forEach(e=>e.classList.remove('sel'));
  el.classList.add('sel');
  fetch('/radar/config',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_R},body:JSON.stringify({sensibilidad:modo})})
    .then(()=>location.reload());
}
function togCfg(k,v){
  fetch('/radar/config',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_R},body:JSON.stringify({[k]:v})});
}
async function delIp(id,btn){
  if(!confirm('¿Quitar esta IP?')) return;
  const r=await fetch('/radar/ip/'+id,{method:'DELETE',headers:{'X-CSRF-Token':CSRF_R}});
  const d=await r.json();
  if(d.ok) btn.closest('.ip-irow').remove();
  else alert(d.error||'Error');
}
async function addIp(){
  const ip=document.getElementById('newIp').value.trim();
  if(!ip) return;
  const r=await fetch('/radar/ip',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_R},body:JSON.stringify({ip})});
  const d=await r.json();
  if(d.ok) location.reload();
  else alert(d.error||'Error');
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
