<?php
// ============================================================
//  CotizaApp — modules/radar/index.php
//  GET /radar
//  Portado fielmente de radar_3_.php (On Time / WordPress)
//  17 buckets · FIT model · filtros IP/UA · debug funnels
// ============================================================

defined('COTIZAAPP') or die;

// Evitar cache agresivo en iOS WKWebView
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

require_once MODULES_PATH . '/radar/Radar.php';
require_once MODULES_PATH . '/radar/playbook-data.php';

$empresa_id = EMPRESA_ID;
$usuario    = Auth::usuario();
$es_admin   = Auth::es_admin();

// Registrar actividad: consulta al Radar
ActividadScore::registrar(Auth::id(), $empresa_id, 'radar_view');

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
$debug_mode = Auth::es_superadmin() && (($_GET['debug'] ?? '') === '1');
$GLOBALS['debug_mode'] = $debug_mode;

$range_secs = ['all'=>0,'48h'=>48*3600,'4h'=>4*3600,'30m'=>30*60];
$min_last   = ($range !== 'all' && isset($range_secs[$range])) ? time() - $range_secs[$range] : 0;

// Recalcular si >1 min o si faltan icons (migración one-time)
$ult = DB::val("SELECT MAX(radar_updated_at) FROM cotizaciones WHERE empresa_id=?", [$empresa_id]);
$_icons_missing = (int)DB::val(
    "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND radar_bucket IS NOT NULL AND (radar_senales IS NULL OR radar_senales NOT LIKE '%\"icons\"%')",
    [$empresa_id]
);
if (!$ult || $ult < date('Y-m-d H:i:s', time()-60) || $_icons_missing > 0 || $debug_mode) {
    try { Radar::check_auto_calibrar($empresa_id); Radar::recalcular_empresa($empresa_id); } catch(Throwable $e){}
}

// Stats globales (sin LIMIT)
$uw = $uid_filtro ? "AND c.vendedor_id=" . intval($uid_filtro) : '';
$stat_total = (int)DB::val(
    "SELECT COUNT(*) FROM cotizaciones c
     WHERE c.empresa_id=? AND c.estado NOT IN ('borrador') $uw",
    [$empresa_id]
);
$stat_aceptadas = (int)DB::val(
    "SELECT COUNT(*) FROM ventas
     WHERE empresa_id=? AND estado != 'cancelada'",
    [$empresa_id]
);
$stat_cierre    = $stat_total > 0 ? round(100 * $stat_aceptadas / $stat_total, 2) : 0;

// Cargar cotizaciones
$raw = DB::query(
    "SELECT c.id, c.titulo, c.numero, c.slug, c.total, c.estado,
            c.radar_score, c.radar_bucket, c.radar_senales, c.radar_updated_at,
            c.visitas,
            c.ultima_vista_at AS raw_vista_at,
            COALESCE(c.ultima_vista_at,
                     (SELECT FROM_UNIXTIME(MAX(qe.ts_unix)) FROM quote_events qe WHERE qe.cotizacion_id=c.id),
                     c.created_at) AS ultima_vista_at,
            c.created_at,
            cl.nombre AS cnombre, cl.telefono AS ctel,
            u.nombre  AS asesor,
            COALESCE(c.vendedor_id, c.usuario_id) AS vendedor_id
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id=c.cliente_id
     LEFT JOIN usuarios  u  ON u.id=COALESCE(c.vendedor_id, c.usuario_id)
     WHERE c.empresa_id=? AND c.suspendida = 0 $uw
       AND (
         c.estado IN ('enviada','vista')
         OR (c.estado IN ('aceptada','rechazada')
             AND COALESCE(c.accion_at, c.ultima_vista_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY))
       )
     ORDER BY c.radar_score IS NULL ASC, c.radar_score DESC, c.ultima_vista_at DESC
     LIMIT 500",
    [$empresa_id]
);

// Cargar feedbacks — superadmin ve todos los de la empresa, asesor solo los suyos
$feedback_map = [];
if (Auth::es_superadmin()) {
    $fb_rows = DB::query(
        "SELECT rf.cotizacion_id, rf.tipo, u.nombre AS asesor_nombre
         FROM radar_feedback rf
         LEFT JOIN usuarios u ON u.id = rf.usuario_id
         WHERE rf.empresa_id=?",
        [$empresa_id]
    );
} else {
    $fb_rows = DB::query(
        "SELECT cotizacion_id, tipo, NULL AS asesor_nombre FROM radar_feedback WHERE usuario_id=? AND empresa_id=?",
        [Auth::id(), $empresa_id]
    );
}
foreach ($fb_rows as $fb) {
    $feedback_map[(int)$fb['cotizacion_id']] = [
        'tipo' => $fb['tipo'],
        'asesor' => $fb['asesor_nombre'] ?? null,
    ];
}
$GLOBALS['feedback_map'] = $feedback_map;
$GLOBALS['fb_shown'] = []; // track qué cotizaciones ya mostraron botones

// Helpers
function rhace(int $ts): string {
    $d=time()-$ts; if($d<=0) return 'ahora'; if($d<60) return $d.'s'; if($d<3600) return floor($d/60).'m';
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

$GLOBALS['BM'] = $BM = [
    'onfire'                => ['🔴','#991b1b','#fff1f2','On Fire'],
    'inminente'             => ['🟠','#c2410c','#fff7ed','Inminente'],
    'probable_cierre'       => ['🎯','#92400e','#fffbeb','Probable cierre'],
    'decision_activa'       => ['🟡','#92400e','#fffbeb','Decisión activa'],
    'validando_precio'      => ['💸','#92400e','#fffbeb','Validando precio'],
    'prediccion_alta'       => ['🔮','#166534','#f0fdf4','Predicción alta'],
    'alto_importe'          => ['💰','#1d4ed8','#dbeafe','Alto importe'],
    're_enganche_caliente'  => ['🔥','#6d28d9','#ede9fe','Re-enganche caliente'],
    're_enganche'           => ['🟣','#6d28d9','#ede9fe','Re-enganche'],
    'multi_persona'         => ['👥','#1d4ed8','#dbeafe','Multi-persona'],
    'revision_profunda'     => ['🧾','#1d4ed8','#dbeafe','Revisión profunda'],
    'vistas_multiples'      => ['🟩','#166534','#f0fdf4','Vistas múltiples'],
    'hesitacion'            => ['🟠','#c2410c','#fff7ed','Hesitación'],
    'sobre_analisis'        => ['🟤','#64748b','#f1f5f9','Sobre-análisis'],
    'revivio'               => ['💜','#6d28d9','#ede9fe','Revivió'],
    'regreso'               => ['🟣','#6d28d9','#ede9fe','Regreso'],
    'comparando'            => ['🔘','#94a3b8','#f1f5f9','Comparando'],
    'enfriandose'           => ['🔵','#0284c7','#e0f2fe','Enfriándose'],
    'no_abierta'            => ['❌','#dc2626','#fef2f2','No abierta'],
];
// Descripciones cortas de cada bucket (para playbook modal)
$GLOBALS['BKT_HINTS'] = [
    'onfire'               => 'Actividad en 72h · 2+ sesiones · scroll ≥ 90% · lectura real · foco en precio · validación por visitor',
    'inminente'            => 'Actividad en 36h · FIT ≥ 5% · edad ≥ 3h · guest ≥ 1 · mínimo 1 señal fuerte · misma huella insistiendo en precio',
    'probable_cierre'      => 'Cross-bucket: confirma intención real con 2+ categorías de señal + lectura real (≥15s) + foco en precio',
    'validando_precio'     => 'Foco real en precio: exige mín 2 sesiones guest + señal de lectura/precio + intent (loop/revisita)',
    'prediccion_alta'      => 'FIT ≥ 14% + edad ≤ ciclo venta real + actividad reciente. Ciclo auto-calculado con mediana de días envío→cierre',
    'decision_activa'      => 'Sesiones recientes con señales de decisión: scroll profundo, revisión de precio, múltiples vistas',
    'alto_importe'         => 'Cotizaciones de alto valor con actividad reciente y señales de interés genuino',
    're_enganche_caliente' => 'Regresó después de inactividad con señales fuertes: scroll, precio, múltiples páginas',
    're_enganche'          => 'Regresó después de inactividad moderada con al menos una señal de interés',
    'multi_persona'        => 'Last < 72h + 2+ visitor_ids o IPs post primer guest · decisión compartida en curso',
    'revision_profunda'    => 'Scroll profundo + tiempo de lectura alto en una sola sesión',
    'vistas_multiples'     => 'Varias vistas en poco tiempo, pero sin señales fuertes de decisión',
    'hesitacion'           => 'Vistas intermitentes sin avanzar — el cliente duda pero no descarta',
    'sobre_analisis'       => 'Demasiadas vistas sin acción — posible parálisis de decisión',
    'revivio'              => 'Cotización vieja que volvió a recibir actividad después de mucho tiempo',
    'regreso'              => 'Regreso después de +4 días sin actividad',
    'comparando'           => 'Patrón de comparación: vistas cortas, posiblemente compartiendo con terceros',
    'enfriandose'          => 'Actividad en declive — cada vez menos interacción',
    'no_abierta'           => 'Cotización enviada pero nunca abierta por el cliente',
];

function rbadge(?string $b,?int $sc,array $BM,string $momentum='stable'): string {
    if(!$b) return '<span style="color:var(--t3);font-size:11px">—</span>';
    [$ico,$col,$bg,$lbl]=$BM[$b]??['⬜','#64748b','#f1f5f9',ucfirst($b)];
    $s=$sc?" · {$sc}":'';
    $decay = $momentum==='cooling' ? ' <span title="Sin actividad reciente — perdiendo momentum" style="font-size:13px;opacity:.7">↓</span>' : '';
    return "<span style='display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;font-weight:700;font-size:11px;background:{$bg};color:{$col};white-space:nowrap'>{$ico} {$lbl}{$decay}{$s}</span>";
}

// PRIORIDAD v3: probable_cierre es #1 (cross-bucket agregador)
$PRIO = ['probable_cierre',
         'onfire','inminente','validando_precio',
         'prediccion_alta','alto_importe','decision_activa','revivio',
         'no_abierta','re_enganche_caliente','re_enganche','multi_persona',
         'revision_profunda','vistas_multiples','hesitacion','sobre_analisis',
         'regreso','comparando','enfriandose'];

$buckets = array_fill_keys($PRIO, []);
$activos48 = [];
$rows_all  = [];
$total_all = $total_aceptadas = 0;

foreach ($raw as $c) {
    $last_ts = $c['ultima_vista_at'] ? strtotime($c['ultima_vista_at']) : strtotime($c['created_at']);
    if ($min_last && $last_ts < $min_last) continue;

    $score    = (int)($c['radar_score'] ?? 0);
    $bucket   = $c['radar_bucket'] ?? null;
    $accepted = $c['estado'] === 'aceptada';
    $total    = (float)($c['total'] ?? 0);
    $senales  = is_string($c['radar_senales']) ? (json_decode($c['radar_senales'],true) ?? []) : [];

    $all_buckets = $senales['buckets'] ?? [];
    $pc_source = $senales['pc_source'] ?? null;

    $momentum = $senales['momentum'] ?? 'stable';

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
        'momentum'    => $momentum,
        'senales'     => $senales,
        'last_ts'     => $last_ts,
        'visitas'     => (int)($c['visitas'] ?? 0),
        'vendedor_id' => (int)($c['vendedor_id'] ?? 0),
    ];

    $rows_all[] = $row;
    $total_all++;
    if ($accepted) $total_aceptadas++;
    if ($c['raw_vista_at'] && $last_ts >= time()-48*3600) $activos48[] = $row;

    // Probable cierre es cross-bucket: la cotización aparece AQUÍ y en su bucket origen
    if (in_array('probable_cierre', $all_buckets, true) && $pc_source) {
        $row['reason'] = $pc_source; // bucket que la activó
        $buckets['probable_cierre'][] = $row;
        // También asignar al bucket origen (sin duplicar en probable_cierre)
        $origin_bucket = $pc_source;
        if ($origin_bucket && isset($buckets[$origin_bucket])) {
            $buckets[$origin_bucket][] = $row;
        }
    } elseif ($bucket && $bucket !== 'probable_cierre' && isset($buckets[$bucket])) {
        $buckets[$bucket][] = $row;
    }
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
$cierre_pct   = $stat_cierre;
$ciclo_venta  = Radar::ciclo_venta($empresa_id);

// Config + IPs internas
$config = Radar::config($empresa_id);
$ips_internas = DB::query("SELECT * FROM radar_ips_internas WHERE empresa_id=? ORDER BY created_at DESC LIMIT 50",[$empresa_id]);

// Render de cada bucket
function render_bkt(string $tit, string $hint, array $items, string $s, string $d, bool $gap=false, bool $motivo=false, string $bkt_key=''): void {
    $debug_mode = $GLOBALS['debug_mode'] ?? false;
    $PB = $GLOBALS['PLAYBOOK'] ?? [];
    $BM = $GLOBALS['BM'] ?? [];
    $bm = $BM[$bkt_key] ?? null;
    $border_color = $bm ? $bm[1] : '#94a3b8';
    $bg_color = $bm ? $bm[2] : '#f8fafc';
    $has_items = count($items) > 0;
    $border_style = $has_items ? "border-left:4px solid {$border_color}" : "border-left:4px solid #d1d5db";
    echo "<div class='rbk' style='{$border_style}'>";
    echo "<div class='rbk-hd' style='".($has_items ? "background:{$bg_color}" : "")."'>";
    echo "<span class='rbk-tit'>".htmlspecialchars($tit)."</span>";
    echo "<span class='rbk-hint'>".htmlspecialchars($hint)."</span>";
    echo "<span class='rbk-right'>";
    if ($bkt_key && isset($PB[$bkt_key])) {
        echo "<button class='pb-btn' onclick=\"openPlaybook('{$bkt_key}')\">📖 Playbook</button>";
    }
    $n = count($items);
    $n_class = $n > 0 ? 'rbk-n rbk-n-active' : 'rbk-n';
    echo "<span class='{$n_class}'>{$n}</span>";
    echo "</span>";
    echo "</div>";
    if (!$items) { echo "<div class='rbk-em'>Sin registros.</div></div>"; return; }
    $items = array_slice($items,0,12);
    echo "<div class='rdrs'><table class='rdrt'><thead><tr>";
    echo "<th>Título / Cliente</th>";
    if ($motivo) echo "<th style='width:100px'>Motivo</th>";
    echo "<th class='tc col-estado' style='width:72px'>Estado</th>";
    echo "<th class='tr' style='width:70px'><a href='".rurlq(['sort'=>'fit','dir'=>rtdir($s,'fit',$d)])."'>Score%</a></th>";
    echo "<th class='tr col-prior' style='width:70px'><a href='".rurlq(['sort'=>'priority','dir'=>rtdir($s,'priority',$d)])."'>Prior%</a></th>";
    echo "<th class='tr' style='width:68px'><a href='".rurlq(['sort'=>'amount','dir'=>rtdir($s,'amount',$d)])."'>Importe</a></th>";
    echo "<th class='col-vista' style='width:120px'><a href='".rurlq(['sort'=>'last','dir'=>rtdir($s,'last',$d)])."'>Última vista</a></th>";
    echo "<th class='col-ver' style='width:55px'>Ver</th>";
    echo "</tr></thead><tbody>";
    foreach ($items as $r) {
        $ago = time()-$r['last_ts'];
        $rc = $ago<1800?'hot30':($ago<14400?'hot4h':'');
        $ab = $r['accepted']?"<span class='bok'>ACCEPTED</span>":"<span class='bno'>".$r['estado']."</span>";
        echo "<tr class='$rc'>";
        $r_icons = $r['senales']['icons'] ?? [];
        $r_ico_str = '';
        if (!empty($r_icons['coupon']))     $r_ico_str .= '🎟️';
        if (!empty($r_icons['promo']))      $r_ico_str .= '💣';
        if (!empty($r_icons['price']))      $r_ico_str .= '💸';
        if (!empty($r_icons['sv_price']))   $r_ico_str .= '👤';
        if (!empty($r_icons['mv_price']))   $r_ico_str .= '👥';
        if (!empty($r_icons['not_opened'])) $r_ico_str .= '❌';
        $r_momentum = $r['momentum'] ?? 'stable';
        $r_decay_ico = $r_momentum === 'cooling' ? '<span class="momentum-down" title="Sin actividad reciente — perdiendo momentum">↓</span>' : '';
        $r_title_show = ($r_ico_str ? $r_ico_str.' ' : '').htmlspecialchars($r['titulo']);
        $cot_url = '/cotizaciones/'.(int)$r['id'];
        // Botones de feedback al lado del título
        $r_bucket_fb = $r['bucket'] ?? '';
        $r_vendedor_fb = (int)($r['vendedor_id'] ?? 0);
        $r_fb_data = ($GLOBALS['feedback_map'] ?? [])[(int)$r['id']] ?? null;
        $r_fb_tipo = $r_fb_data['tipo'] ?? null;
        $r_fb_asesor = $r_fb_data['asesor'] ?? null;
        $hot_bkts_fb = ['probable_cierre','onfire','inminente','validando_precio','prediccion_alta'];
        $show_fb_td = in_array($r_bucket_fb, $hot_bkts_fb) && ($r_vendedor_fb === Auth::id() || Auth::es_admin());
        $fb_html = '';
        $cot_id_fb = (int)$r['id'];
        $already_shown = isset($GLOBALS['fb_shown'][$cot_id_fb]);
        // Mostrar badge de señalado para superadmin (aunque no tenga botones)
        if ($r_fb_tipo && Auth::es_superadmin() && !$show_fb_td && !$already_shown) {
            $GLOBALS['fb_shown'][$cot_id_fb] = true;
            $fb_lbl = $r_fb_tipo === 'con_interes' ? '👍' : '👎';
            $fb_who = $r_fb_asesor ? ' '.htmlspecialchars($r_fb_asesor) : '';
            $fb_html = "<span class='fb-badge' title='Señalado por{$fb_who}' style='font-size:12px;opacity:.7'>{$fb_lbl}</span>";
        }
        if ($show_fb_td && !$already_shown) {
            $GLOBALS['fb_shown'][$cot_id_fb] = true;
            $cls_ci = $r_fb_tipo === 'con_interes' ? 'fb-active fb-pos' : '';
            $cls_si = $r_fb_tipo === 'sin_interes' ? 'fb-active fb-neg' : '';
            $fb_html = "<div class='fb-btns' style='flex-shrink:0'>"
                . "<button class='fb-btn {$cls_ci}' onclick=\"event.preventDefault();event.stopPropagation();radarFb({$cot_id_fb},'con_interes',this)\" title='Con interés'>👍</button>"
                . "<button class='fb-btn {$cls_si}' onclick=\"event.preventDefault();event.stopPropagation();radarFb({$cot_id_fb},'sin_interes',this)\" title='Sin interés'>👎</button>"
                . "</div>";
        }
        echo "<td><a href='{$cot_url}' class='rtit-link'><div style='display:flex;align-items:center;gap:4px'><div class='rtit' style='flex:1;min-width:0'>{$r_title_show}</div>{$r_decay_ico}{$fb_html}</div><div class='rsub'>".htmlspecialchars($r['cliente'])."</div></a></td>";
        if ($motivo) {
            $reason_key = $r['reason'] ?? '';
            $reason_meta = $BM[$reason_key] ?? null;
            if ($reason_meta) {
                echo "<td>".rbadge($reason_key, null, $BM)."</td>";
            } else {
                echo "<td><span class='rmot'>".htmlspecialchars($reason_key)."</span></td>";
            }
        }
        echo "<td class='tc col-estado'>$ab</td>";
        echo "<td class='tr'><b>".number_format($r['fit_pct'],1)."%</b></td>";
        echo "<td class='tr col-prior'><b>".number_format($r['priority_pct'],1)."%</b></td>";
        echo "<td class='tr'>".rmoney($r['total'])."</td>";
        $last_fmt = date('m-d H:i',$r['last_ts'])." <span class='ago'>(".rhace($r['last_ts']).")</span>";
        if ($gap && isset($r['gap_days'])) $last_fmt .= " <b style='color:#6a1b9a'>gap ".(int)$r['gap_days']."d</b>";
        echo "<td class='col-vista'>$last_fmt</td>";
        echo "<td class='col-ver'><a href='{$cot_url}' class='rlnk'>Editar</a></td>";
        echo "</tr>";
        // Debug row: show all scoring internals
        if ($debug_mode) {
            $dbg = $r['senales']['debug'] ?? [];
            $sn  = $r['senales']['senales'] ?? [];
            $bks = $r['senales']['buckets'] ?? [];
            $ics = $r['senales']['icons'] ?? [];
            $pcs = $r['senales']['pc_source'] ?? null;
            $col_span = 6 + ($motivo ? 1 : 0);
            echo "<tr class='dbg-row'><td colspan='{$col_span}' style='padding:6px 12px 10px;background:#fffbeb;border-bottom:2px solid #fde68a'>";
            echo "<div class='dbg-grid'>";
            // Internals
            echo "<div class='dbg-sec'><div class='dbg-lbl'>Internos</div><div class='dbg-val'>";
            echo "sess:<b>".($dbg['sessions']??'?')."</b> ";
            echo "guest:<b>".($dbg['guest']??'?')."</b> ";
            echo "ips:<b>".($dbg['uniq_ips']??'?')."</b> ";
            echo "gap:<b>".(isset($dbg['gap_days']) && $dbg['gap_days']!==null?$dbg['gap_days'].'d':'—')."</b> ";
            echo "v24:<b>".($dbg['views24']??'?')."</b> ";
            echo "v48:<b>".($dbg['views48']??'?')."</b> ";
            echo "span48:<b>".($dbg['span48h']??'?')."</b> ";
            echo "pss:<b>".($dbg['pss']??'?')."</b> ";
            echo "ev_v:<b>".($dbg['ev_uniq_v']??'?')."</b> ";
            echo "vids:<b>".($dbg['vids_post']??'0')."</b> ";
            echo "mvid:<b>".($dbg['multi_vid']??'0')."</b> ";
            echo "modo:<b>".($dbg['modo']??'?')."</b>";
            echo "</div></div>";
            // Devices
            if (!empty($dbg['devices'])) {
                echo "<div class='dbg-sec'><div class='dbg-lbl'>Dispositivos</div><div class='dbg-val'>";
                echo implode(' · ', array_map(fn($d) => "<b>{$d}</b>", $dbg['devices']));
                echo "</div></div>";
            }
            // Signals
            if ($sn) {
                echo "<div class='dbg-sec'><div class='dbg-lbl'>Señales</div><div class='dbg-val'>";
                $sn_parts = [];
                foreach ($sn as $sk => $sv) {
                    $sn_parts[] = "<span class='dbg-tag".($sv?' dbg-on':'')."'>$sk</span>";
                }
                echo implode(' ', $sn_parts);
                echo "</div></div>";
            }
            // Buckets
            echo "<div class='dbg-sec'><div class='dbg-lbl'>Buckets</div><div class='dbg-val'>";
            if ($bks) {
                foreach ($bks as $bk) {
                    $is_main = ($bk === ($r['bucket'] ?? ''));
                    echo "<span class='dbg-bkt".($is_main?' dbg-main':'')."'>$bk</span> ";
                }
            } else {
                echo "<span style='color:#9ca3af'>ninguno</span>";
            }
            if ($pcs) echo " <span style='color:#6b7280;font-size:11px'>pc_source=$pcs</span>";
            echo "</div></div>";
            // Icons
            if ($ics) {
                echo "<div class='dbg-sec'><div class='dbg-lbl'>Icons</div><div class='dbg-val'>";
                foreach ($ics as $ik => $iv) {
                    if ($iv) echo "<span class='dbg-tag dbg-on'>$ik</span> ";
                }
                echo "</div></div>";
            }
            // Probable cierre categories
            $pc = $dbg['pc_cats'] ?? null;
            if ($pc) {
                $sess_ok = $pc['min_sess_ok'] ?? false;
                $strong_ok = $pc['has_strong'] ?? false;
                echo "<div class='dbg-sec'><div class='dbg-lbl'>Prob. Cierre ({$pc['total']}/4 cats)</div><div class='dbg-val'>";
                foreach (['engagement','precio'] as $cat) {
                    $on = $pc[$cat] ?? false;
                    echo $on ? "<span class='dbg-tag dbg-on'>⚡$cat</span> " : "<span class='dbg-tag dbg-off'>$cat</span> ";
                }
                foreach (['persistencia','social'] as $cat) {
                    $on = $pc[$cat] ?? false;
                    echo "<span class='dbg-tag".($on?' dbg-on':' dbg-off')."'>$cat</span> ";
                }
                echo "<span class='dbg-tag".($sess_ok?' dbg-on':' dbg-fail')."'>sess≥2</span> ";
                echo "<span class='dbg-tag".($strong_ok?' dbg-on':' dbg-fail')."'>cat_fuerte</span>";
                echo "</div></div>";
            }
            // Extra scoring details
            if (isset($dbg['scroll_cls'])) {
                echo "<div class='dbg-sec'><div class='dbg-lbl'>Engagement</div><div class='dbg-val'>";
                echo "scroll_cls:<b>".($dbg['scroll_cls']??0)."%</b> ";
                echo "scroll_any:<b>".($dbg['scroll_any']??0)."%</b> ";
                echo "vis_max:<b>".($dbg['vis_max']??0)."ms</b> ";
                echo "vis_sum:<b>".($dbg['vis_sum']??0)."ms</b> ";
                echo "ips_post:<b>".($dbg['ips_post_guest']??0)."</b>";
                echo "</div></div>";
            }
            echo "</div></td></tr>";
        }
    }
    echo "</tbody></table></div></div>";
}

$page_title = 'Radar';
ob_start();
?>
<style>
.rdr-bar{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
.rdr-bt{padding:7px 13px;border-radius:20px;border:1px solid var(--border);background:var(--white);font-weight:600;font-size:12px;color:var(--t2);cursor:pointer;white-space:nowrap;transition:all .12s;text-decoration:none}
.rdr-bt.active,.rdr-bt:hover:not(.active){background:var(--g);border-color:var(--g);color:#fff}
.rdr-bt:hover:not(.active){opacity:.85}
.rdr-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
.rdr-sv{font-weight:800;font-size:22px;font-family:var(--num);margin-bottom:2px}
.rdr-sl{font-weight:500;font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.06em}

.rtabs{display:flex;gap:0;background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:20px;flex-wrap:wrap}
.rtab{padding:10px 16px;font-weight:600;font-size:13px;color:var(--t2);cursor:pointer;border:none;background:none;border-right:1px solid var(--border);transition:all .12s;display:flex;align-items:center;gap:6px}
.rtab:last-child{border-right:none}
.rtab.on{background:var(--g);color:#fff}
.rtab:hover:not(.on){background:var(--bg)}
.rtab-c{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:9px;font-weight:700;font-size:11px}
.rtab.on .rtab-c{background:rgba(255,255,255,.25);color:#fff}
.rtab:not(.on) .rtab-c{background:var(--bg);color:var(--t2);border:1px solid var(--border)}
.tab-panel{display:none}.tab-panel.on{display:block}

.rbk{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:14px}
.rbk-hd{display:flex;align-items:center;gap:8px;padding:10px 16px;border-bottom:1px solid var(--border);background:var(--bg);flex-wrap:wrap}
.rbk-tit{font-weight:700;font-size:13px;white-space:nowrap}
.rbk-hint{font-weight:400;font-size:11px;color:var(--t3);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.rbk-right{display:flex;align-items:center;gap:8px;flex-shrink:0;margin-left:auto}
.rbk-n{font-weight:700;font-size:13px;font-family:var(--num);color:var(--t3);white-space:nowrap;min-width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;border-radius:12px}
.rbk-n-active{background:var(--g);color:#fff;padding:0 8px}
.rbk-em{padding:14px 16px;font-weight:400;font-size:13px;color:var(--t3)}
@media(max-width:600px){.rbk-hint{display:none}}

.rdrs{overflow-x:auto}
.rdrt{width:100%;border-collapse:collapse;min-width:520px}
.rdrt th{font-weight:700;font-size:10px;letter-spacing:.06em;text-transform:uppercase;color:var(--t3);padding:7px 12px;border-bottom:1.5px solid var(--border);background:var(--bg);white-space:nowrap}
.rdrt th a{color:inherit;text-decoration:none}.rdrt th a:hover{text-decoration:underline}
.rdrt td{padding:8px 12px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle}
.rdrt tr:last-child td{border-bottom:none}
.rdrt tr.hot30{background:#ffd9d9;font-weight:600}
.rdrt tr.hot4h{background:#fff7cc}
.rdrt tr:hover td{background:#fafaf8;cursor:pointer}
.tc{text-align:center}.tr{text-align:right}
.rtit{font-weight:600;font-size:13px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px}
.rsub{font-weight:400;font-size:11px;color:var(--t3);margin-top:1px}
.ago{color:#888;font-size:11px}
.rmot{font-weight:500;font-size:11px;color:var(--t2)}
.bok{display:inline-block;padding:1px 6px;border-radius:8px;font-weight:700;font-size:10px;background:#dcfce7;border:1px solid #86efac;color:#166534}
.bno{display:inline-block;padding:1px 6px;border-radius:8px;font-weight:700;font-size:10px;background:var(--bg);border:1px solid var(--border);color:var(--t2)}
.rlnk{font-weight:600;font-size:12px;color:var(--g);text-decoration:none}
.rlnk:hover{text-decoration:underline}

/* Momentum decay indicator */
.momentum-down{display:inline-block;color:#dc2626;font-weight:800;font-size:14px;line-height:1;vertical-align:middle;margin-left:3px;animation:pulseDown 2s ease-in-out infinite}
@keyframes pulseDown{0%,100%{opacity:.5}50%{opacity:1}}

/* Debug rows */
.dbg-row td{cursor:default!important}
.dbg-row:hover td{background:#fffbeb!important}
.dbg-grid{display:flex;flex-wrap:wrap;gap:6px 16px}
.dbg-sec{min-width:0}
.dbg-lbl{font-weight:700;font-size:9px;text-transform:uppercase;letter-spacing:.08em;color:#92400e;margin-bottom:2px}
.dbg-val{font-weight:400;font-size:11px;font-family:var(--num);color:#78350f;line-height:1.6;word-break:break-all}
.dbg-val b{font-weight:700;color:#451a03}
.dbg-tag{display:inline-block;padding:0 5px;border-radius:4px;font-weight:500;font-size:10px;background:#e5e7eb;color:#6b7280;margin:1px}
.dbg-tag.dbg-on{background:#dcfce7;color:#166534;font-weight:700}
.dbg-tag.dbg-fail{background:#fee2e2;color:#991b1b;font-weight:700}
.dbg-tag.dbg-off{background:#f3f4f6;color:#9ca3af;text-decoration:line-through;font-weight:400}
.dbg-bkt{display:inline-block;padding:1px 6px;border-radius:6px;font-weight:600;font-size:10px;background:#e0e7ff;color:#3730a3;margin:1px}
.dbg-bkt.dbg-main{background:#4f46e5;color:#fff}

.modo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px}
.modo-opt{padding:14px 16px;border:1.5px solid var(--border);border-radius:var(--r-sm);cursor:pointer;transition:all .12s;background:var(--white)}
.modo-opt:hover{border-color:var(--border2)}
.modo-opt.sel{border-color:var(--g);background:var(--g-bg)}
.modo-opt-tit{font-weight:700;font-size:14px;margin-bottom:4px}
.modo-opt-sub{font-weight:400;font-size:12px;color:var(--t3);line-height:1.5}
.tog-row{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border)}
.tog-row:last-child{border-bottom:none}
.tog-lbl{font-weight:600;font-size:13px}
.tog-sub{font-weight:400;font-size:12px;color:var(--t3);margin-top:2px}
.tog{position:relative;display:inline-block;width:42px;height:24px;flex-shrink:0}
.tog input{opacity:0;width:0;height:0}
.tog-track{position:absolute;inset:0;background:var(--border2);border-radius:12px;transition:background .2s}
.tog input:checked + .tog-track{background:var(--g)}
.tog-thumb{position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:left .2s;pointer-events:none;box-shadow:0 1px 3px rgba(0,0,0,.18)}
.tog input:checked ~ .tog-thumb{left:21px}
.ip-irow{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--border)}
.ip-irow:last-child{border-bottom:none}
/* Playbook button */
.pb-btn{
  padding:4px 12px;border-radius:14px;border:1px solid #c7d2fe;
  background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:#4338ca;
  font-weight:700;font-size:11px;cursor:pointer;transition:all .15s;
  display:inline-flex;align-items:center;gap:4px;white-space:nowrap;flex-shrink:0;
}
.pb-btn:hover{background:linear-gradient(135deg,#c7d2fe,#a5b4fc);color:#312e81;border-color:#a5b4fc;transform:scale(1.04)}
/* Playbook modal */
.pb-overlay{
  display:none;position:fixed;inset:0;z-index:9999;
  background:rgba(0,0,0,.45);backdrop-filter:blur(4px);
  justify-content:center;align-items:flex-start;padding:32px 16px;overflow-y:auto;
}
.pb-overlay.open{display:flex}
.pb-modal{
  background:#fff;border-radius:20px;max-width:680px;width:100%;
  box-shadow:0 24px 64px rgba(0,0,0,.18);animation:pbSlide .2s ease;
  max-height:90vh;overflow-y:auto;
}
@keyframes pbSlide{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.pb-modal-hd{
  position:sticky;top:0;z-index:1;
  padding:20px 24px 16px;border-bottom:1px solid #e5e7eb;
  background:linear-gradient(180deg,#fafaff,#fff);border-radius:20px 20px 0 0;
  display:flex;align-items:flex-start;justify-content:space-between;gap:12px;
}
.pb-modal-hd h2{margin:0;font-weight:800;font-size:20px;letter-spacing:-.02em;line-height:1.2}
.pb-modal-hd p{margin:4px 0 0;font-weight:400;font-size:13px;color:#6b7280;line-height:1.5}
.pb-close{
  background:none;border:1px solid #e5e7eb;border-radius:10px;
  width:34px;height:34px;font-size:18px;cursor:pointer;color:#6b7280;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.pb-close:hover{background:#f3f4f6;color:#111}
.pb-body{padding:20px 24px 28px}
.pb-section{margin-bottom:18px}
.pb-section h4{
  margin:0 0 8px;font-weight:700;font-size:11px;text-transform:uppercase;
  letter-spacing:.12em;color:#6366f1;
}
.pb-psych{
  background:#fafaff;border:1px solid #e0e7ff;border-radius:14px;
  padding:14px 16px;font-weight:400;font-size:13.5px;line-height:1.65;color:#1f2937;
  margin-bottom:18px;
}
.pb-cols{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.pb-mini{border:1px solid #e5e7eb;border-radius:14px;padding:14px 16px;background:#fff}
.pb-mini h4{margin:0 0 8px;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:#4b5563}
.pb-mini.do-card{border-color:#bbf7d0;background:#f0fdf4}
.pb-mini.do-card h4{color:#166534}
.pb-mini.dont-card{border-color:#fecaca;background:#fef2f2}
.pb-mini.dont-card h4{color:#991b1b}
.pb-mini ul{margin:0;padding-left:16px}
.pb-mini li{margin:0 0 6px;font-weight:400;font-size:13px;line-height:1.5;color:#1f2937}
.pb-msg{border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;margin-bottom:10px}
.pb-msg-canal{
  padding:7px 14px;font-weight:700;font-size:11px;text-transform:uppercase;
  letter-spacing:.08em;border-bottom:1px solid #e5e7eb;
}
.pb-msg-canal.wa{background:#dcf8c6;color:#166534}
.pb-msg-canal.call{background:#ede9fe;color:#5b21b6}
.pb-msg-texto{padding:12px 16px;font-weight:400;font-size:14px;line-height:1.65;color:#111827;background:#fff;border-bottom:1px solid #f3f4f6}
.pb-msg-nota{padding:8px 14px;font-style:italic;font-weight:400;font-size:12px;color:#6b7280;background:#fafafa}
.pb-footer{
  display:flex;gap:16px;flex-wrap:wrap;padding-top:14px;margin-top:14px;
  border-top:1px solid #e5e7eb;
}
.pb-footer-item{font-weight:400;font-size:12px;color:#6b7280}
.pb-footer-item b{color:#111827;font-weight:700}
.pb-priority{
  display:inline-block;padding:3px 10px;border-radius:999px;
  font-weight:700;font-size:11px;margin-top:8px;
}
.pb-priority.critica{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.pb-priority.alta{background:#fffbeb;color:#92400e;border:1px solid #fde68a}
.pb-priority.media{background:#f3f4f6;color:#374151;border:1px solid #d1d5db}
.rtit-link{text-decoration:none;color:inherit;display:block}
.rtit-link:hover .rtit{text-decoration:underline}
@media(max-width:760px){
  .rdr-stats{grid-template-columns:repeat(2,1fr)}
  .modo-grid{grid-template-columns:1fr}
  .pb-cols{grid-template-columns:1fr}
  .pb-modal{border-radius:14px}
  .rbk-hd{gap:6px}
  .rtit{max-width:160px}
}
/* Feedback buttons */
.fb-btns{display:flex;gap:3px}
.fb-btn{width:24px;height:24px;border:1px solid var(--border);border-radius:6px;background:#fff;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;opacity:.5;transition:all .15s}
.fb-btn:hover{opacity:1;transform:scale(1.1)}
.fb-active{opacity:1;border-width:2px;font-weight:600}
.fb-pos{border-color:#16a34a;background:#f0fdf4;color:#16a34a}
.fb-neg{border-color:#dc2626;background:#fef2f2;color:#dc2626}
</style>

<!-- Cabecera -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div>
    <h1 style="font-weight:800;font-size:22px;letter-spacing:-.02em">📡 Radar</h1>
    <p style="font-weight:400;font-size:13px;color:var(--t3);margin-top:3px">
      Total: <?= $stat_total ?> · Aceptadas: <?= $stat_aceptadas ?> · Cierre global: <b><?= $cierre_pct ?>%</b>
      · Ciclo venta: <b><?= $ciclo_venta['dias'] ?>d</b><?= $ciclo_venta['auto'] ? '' : ' <span style="opacity:.6">(estimado)</span>' ?>
      · Modo: <b><?= ucfirst($config['sensibilidad'] ?? 'medio') ?></b>
    </p>
  </div>
  <?php if ($debug_mode): ?><span style="padding:4px 10px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;font-weight:700;font-size:11px;color:#92400e">DEBUG ON</span><?php endif; ?>
</div>

<!-- Alerta Posible Competencia -->
<?php
// ── Helper para renderizar detalle de competencia ──
function render_comp_row($cv, $empresa_id, $tipo) {
    if ($tipo === 'user') {
        $where_main = "qs.visitor_id";
        $param_val = $cv['visitor_id'];
        $key = 'u_' . ($cv['visitor_id'] ?? '');
    } elseif ($tipo === 'device') {
        $where_main = "qs.device_sig";
        $param_val = $cv['device_sig'];
        $key = 'd_' . ($cv['device_sig'] ?? '');
    } else {
        $where_main = "qs.ip";
        $param_val = $cv['ip'];
        $key = 'ip_' . $cv['ip'];
    }

    $cv_detail = DB::query(
        "SELECT cl.nombre AS cliente,
                SUBSTRING_INDEX(GROUP_CONCAT(c.titulo ORDER BY qs.created_at DESC SEPARATOR '|||'), '|||', 1) AS cotizacion,
                MAX(qs.created_at) AS ultima_vista,
                COUNT(DISTINCT c.id) AS num_cots
         FROM quote_sessions qs
         JOIN cotizaciones c ON c.id = qs.cotizacion_id
         LEFT JOIN clientes cl ON cl.id = c.cliente_id
         WHERE {$where_main} = ? AND c.empresa_id = ?
           AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
           AND (qs.visible_ms > 3000 OR qs.scroll_max > 10)
         GROUP BY c.cliente_id
         ORDER BY ultima_vista DESC",
        [$param_val, $empresa_id]
    );
    $cv_devs = DB::query(
        "SELECT DISTINCT SUBSTRING(qs.user_agent, 1, 120) AS ua
         FROM quote_sessions qs
         JOIN cotizaciones c ON c.id = qs.cotizacion_id
         WHERE {$where_main} = ? AND c.empresa_id = ?
           AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
         LIMIT 5",
        [$param_val, $empresa_id]
    );
    $devices = [];
    foreach ($cv_devs as $d) {
        $ua = $d['ua'];
        if (str_contains($ua, 'iPhone')) $devices[] = 'iPhone';
        elseif (str_contains($ua, 'Android')) $devices[] = 'Android';
        elseif (str_contains($ua, 'iPad')) $devices[] = 'iPad';
        elseif (str_contains($ua, 'Mac')) $devices[] = 'Mac';
        elseif (str_contains($ua, 'Windows')) $devices[] = 'Windows';
        else $devices[] = 'Otro';
    }
    $devices = array_unique($devices);
    $dev_str = implode(', ', $devices);
    $visitors_lbl = isset($cv['visitors_distintos']) && $cv['visitors_distintos'] > 1
        ? ' · '.(int)$cv['visitors_distintos'].' dispositivos' : '';
    $safe_key = htmlspecialchars($key, ENT_QUOTES);
    $dismiss_tipo = $tipo;
    $dismiss_val = $param_val;
    ?>
    <div class="comp-row" data-comp-key="<?= $safe_key ?>" style="background:#fee2e2;border-radius:8px;padding:10px 14px;margin-bottom:6px">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div style="font:700 12px var(--body);color:#991b1b">
                <?= e($cv['device_sig'] ?? $cv['ip'] ?? $cv['visitor_id'] ?? '?') ?> · <?= (int)$cv['clientes_distintos'] ?> clientes · <?= (int)$cv['cots_vistas'] ?> cots<?= $visitors_lbl ?>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font:500 11px var(--num);color:#7f1d1d"><?= $dev_str ?></span>
                <button onclick="descartarComp('<?= $dismiss_tipo ?>','<?= e($dismiss_val) ?>',this)" style="background:none;border:1px solid #fca5a5;border-radius:5px;padding:2px 8px;font:500 10px var(--body);color:#991b1b;cursor:pointer" title="Ya revisé — limpiar alerta">✓ Revisado</button>
            </div>
        </div>
        <?php foreach ($cv_detail as $det): ?>
        <div style="display:flex;justify-content:space-between;padding:3px 0;font:400 12px var(--body);color:#7f1d1d;border-bottom:1px solid rgba(252,165,165,.3)">
            <span><b><?= e($det['cliente'] ?? 'Sin cliente') ?></b> — <?= e(mb_substr($det['cotizacion'],0,40)) ?><?= (int)($det['num_cots'] ?? 1) > 1 ? ' <span style="opacity:.6">(+'.(($det['num_cots'])-1).' cots)</span>' : '' ?></span>
            <span style="font-family:var(--num);flex-shrink:0;margin-left:8px"><?= date('d/m H:i', strtotime($det['ultima_vista'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}

// ── 1. Alerta por Usuario (visitor_id) ──
$comp_by_user = DB::query(
    "SELECT qs.visitor_id, qs.ip,
            COUNT(DISTINCT c.cliente_id) AS clientes_distintos,
            COUNT(DISTINCT qs.cotizacion_id) AS cots_vistas,
            MAX(qs.created_at) AS ultima_visita
     FROM quote_sessions qs
     JOIN cotizaciones c ON c.id = qs.cotizacion_id
     WHERE c.empresa_id = ?
       AND qs.visitor_id IS NOT NULL AND qs.visitor_id != ''
       AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
       AND (qs.visible_ms > 3000 OR qs.scroll_max > 10)
       AND qs.visitor_id NOT IN (SELECT visitor_id FROM radar_visitors_internos WHERE empresa_id = ?)
     GROUP BY qs.visitor_id
     HAVING clientes_distintos > 1
       AND ultima_visita > COALESCE((SELECT reviewed_at FROM radar_comp_reviewed WHERE empresa_id = ? AND tipo = 'user' AND valor = qs.visitor_id), '2000-01-01')
     ORDER BY clientes_distintos DESC, ultima_visita DESC
     LIMIT 10",
    [$empresa_id, $empresa_id, $empresa_id]
);

// ── 2. Alerta por IP ──
$comp_by_ip = DB::query(
    "SELECT qs.ip,
            COUNT(DISTINCT c.cliente_id) AS clientes_distintos,
            COUNT(DISTINCT qs.cotizacion_id) AS cots_vistas,
            COUNT(DISTINCT qs.visitor_id) AS visitors_distintos,
            MAX(qs.created_at) AS ultima_visita
     FROM quote_sessions qs
     JOIN cotizaciones c ON c.id = qs.cotizacion_id
     WHERE c.empresa_id = ?
       AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
       AND (qs.visible_ms > 3000 OR qs.scroll_max > 10)
       AND qs.ip NOT IN (SELECT ip FROM radar_ips_internas WHERE empresa_id = ?)
     GROUP BY qs.ip
     HAVING clientes_distintos > 1
       AND ultima_visita > COALESCE((SELECT reviewed_at FROM radar_comp_reviewed WHERE empresa_id = ? AND tipo = 'ip' AND valor = qs.ip), '2000-01-01')
     ORDER BY clientes_distintos DESC, ultima_visita DESC
     LIMIT 10",
    [$empresa_id, $empresa_id, $empresa_id]
);

// ── 3. Alerta por Device Signature (descarte) ──
// Excluir device_sigs de empleados del sistema
$comp_by_device = DB::query(
    "SELECT qs.device_sig,
            COUNT(DISTINCT c.cliente_id) AS clientes_distintos,
            COUNT(DISTINCT qs.cotizacion_id) AS cots_vistas,
            COUNT(DISTINCT qs.visitor_id) AS visitors_distintos,
            MAX(qs.created_at) AS ultima_visita
     FROM quote_sessions qs
     JOIN cotizaciones c ON c.id = qs.cotizacion_id
     WHERE c.empresa_id = ?
       AND qs.device_sig IS NOT NULL AND qs.device_sig != ''
       AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
       AND (qs.visible_ms > 3000 OR qs.scroll_max > 10)
       AND qs.device_sig NOT IN (
           SELECT DISTINCT us.device_sig FROM user_sessions us
           JOIN usuarios u ON u.id = us.usuario_id
           WHERE (u.empresa_id = ? OR u.rol = 'superadmin')
             AND us.device_sig IS NOT NULL AND us.device_sig != ''
       )
     GROUP BY qs.device_sig
     HAVING clientes_distintos > 1
       AND ultima_visita > COALESCE((SELECT reviewed_at FROM radar_comp_reviewed WHERE empresa_id = ? AND tipo = 'device' AND valor = qs.device_sig), '2000-01-01')
     ORDER BY clientes_distintos DESC, ultima_visita DESC
     LIMIT 10",
    [$empresa_id, $empresa_id, $empresa_id]
);

$total_comp = count($comp_by_user ?: []) + count($comp_by_ip ?: []) + count($comp_by_device ?: []);
if ($total_comp): ?>
<div id="comp-alert" style="background:#fff5f5;border:1.5px solid #fca5a5;border-radius:var(--r);padding:14px 18px;margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;cursor:pointer" onclick="document.getElementById('comp-body').style.display=document.getElementById('comp-body').style.display==='none'?'block':'none'">
        <div style="font:700 14px var(--body);color:#991b1b">⚠️ Posible Competencia (<?= $total_comp ?>)</div>
        <span style="font:400 12px var(--body);color:#991b1b">▼ ver detalle</span>
    </div>
    <div id="comp-body" style="display:none;margin-top:10px">

    <?php if ($comp_by_user): ?>
    <div style="font:700 11px var(--body);color:#991b1b;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;padding:4px 0;border-bottom:1px solid #fca5a5">
        🔍 Mismo navegador vio multiples clientes (<?= count($comp_by_user) ?>)
    </div>
    <?php foreach ($comp_by_user as $cv) render_comp_row($cv, $empresa_id, 'user'); ?>
    <?php endif; ?>

    <?php if ($comp_by_ip): ?>
    <div style="font:700 11px var(--body);color:#991b1b;margin:<?= $comp_by_user ? '12px' : '0' ?> 0 6px;text-transform:uppercase;letter-spacing:.05em;padding:4px 0;border-bottom:1px solid #fca5a5">
        🌐 Misma red vio multiples clientes (<?= count($comp_by_ip) ?>)
    </div>
    <?php foreach ($comp_by_ip as $cv) render_comp_row($cv, $empresa_id, 'ip'); ?>
    <?php endif; ?>

    <?php if ($comp_by_device): ?>
    <div style="font:700 11px var(--body);color:#991b1b;margin:<?= ($comp_by_user || $comp_by_ip) ? '12px' : '0' ?> 0 6px;text-transform:uppercase;letter-spacing:.05em;padding:4px 0;border-bottom:1px solid #fca5a5">
        📱 Mismo dispositivo vio multiples clientes (<?= count($comp_by_device) ?>)
    </div>
    <?php foreach ($comp_by_device as $cv) render_comp_row($cv, $empresa_id, 'device'); ?>
    <?php endif; ?>

    </div>
</div>
<script>
async function descartarComp(tipo, valor, btn) {
    if (!confirm('¿Marcar como interno? No volverá a generar alertas.')) return;
    btn.disabled = true; btn.textContent = '...';
    try {
        const r = await fetch('/radar/descartar-comp', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-Token':'<?= csrf_token() ?>'},
            body: JSON.stringify({tipo, valor, empresa_id: <?= $empresa_id ?>})
        });
        const d = await r.json();
        if (d.ok) {
            btn.closest('.comp-row').style.display = 'none';
            var visible = document.querySelectorAll('.comp-row:not([style*="display: none"])');
            if (!visible.length && document.getElementById('comp-alert')) {
                document.getElementById('comp-alert').style.display = 'none';
            }
        } else { alert(d.error || 'Error'); btn.disabled = false; btn.textContent = '✕'; }
    } catch(e) { alert('Error de conexión'); btn.disabled = false; btn.textContent = '✕'; }
}
</script>
<?php endif; ?>

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
    <div class="rdr-sv"><?= $stat_aceptadas ?></div>
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
           style="width:68px;padding:6px 10px;border:1px solid var(--border);border-radius:var(--r-sm);font-weight:500;font-size:13px;font-family:var(--num)">
    <button class="rdr-bt" style="border-radius:var(--r-sm);padding:7px 14px">Actualizar</button>
    <?php if (Auth::es_superadmin()): ?>
    <a href="<?= rurlq(['debug'=>$debug_mode?'0':'1']) ?>" class="rdr-bt <?= $debug_mode?'active':'' ?>">Debug</a>
    <?php endif; ?>
  </form>
  <span style="font:400 11px var(--body);color:var(--t3);margin-left:auto;display:flex;align-items:center;gap:8px">
    <span>👍 Con interés</span>
    <span>👎 Sin interés</span>
  </span>
</div>

<!-- ===== TAB: ALTA PRIORIDAD ===== -->
<div class="tab-panel on" id="tab-urgentes">
<?php
render_bkt('🎯 Probable cierre',
    'Cross-bucket: confirma intención real con 2+ categorías de señal + lectura real (≥15s) + foco en precio',
    $buckets['probable_cierre'],$sort,$dir,false,true,'probable_cierre');

render_bkt('🔥😱 ON FIRE',
    'Actividad en 72h · 2+ sesiones · scroll ≥ 90% · lectura real · foco en precio · validación por visitor',
    $buckets['onfire'],$sort,$dir,false,false,'onfire');

render_bkt('🔥 Cierre inminente',
    'Actividad en 36h · FIT ≥ 5% · edad ≥ 3h · guest ≥ 1 · mínimo 1 señal fuerte · misma huella insistiendo en precio',
    $buckets['inminente'],$sort,$dir,false,false,'inminente');

render_bkt('💸 Validando precio',
    'Detecta foco real en precio: exige base guest + validación individual (misma huella) o compartida (multi-visitor)',
    $buckets['validando_precio'],$sort,$dir,false,false,'validando_precio');

render_bkt('🔮 Predicción alta (ciclo: '.$ciclo_venta['dias'].'d)',
    'v2.3: FIT ≥ 14% + edad ≤ ciclo venta real ('.$ciclo_venta['dias'].'d) + actividad reciente. El ciclo se auto-calcula con la mediana de días envío→cierre de tu empresa.',
    $buckets['prediccion_alta'],$sort,$dir,false,false,'prediccion_alta');
?>
</div>

<!-- ===== TAB: TODOS LOS BUCKETS ===== -->
<div class="tab-panel" id="tab-buckets">
<?php
render_bkt('🧠 Decisión activa',
    '4+ vistas en 48h y regresos reales (span ≥ 6h)',
    $buckets['decision_activa'],$sort,$dir,false,false,'decision_activa');

render_bkt('💰 Alto importe',
    'v2.2: Umbral dinámico P80 de la empresa (auto-calculado). Vista reciente.',
    $buckets['alto_importe'],$sort,$dir,false,false,'alto_importe');

render_bkt('🔥 Re-enganche caliente',
    'v2.3: Regresó tras gap + interactuó con precio (revisó totales, loop, cupón o sv_price). Señal de compra fuerte.',
    $buckets['re_enganche_caliente'] ?? [],$sort,$dir,true,false,'re_enganche_caliente');

render_bkt('🟣 Re-enganche',
    'v2.3: Regresó tras gap + señal de interés, pero sin foco directo en precio. Oportunidad de seguimiento.',
    $buckets['re_enganche'],$sort,$dir,true,false,'re_enganche');

render_bkt('👥 Multi-persona',
    '2+ visitor_ids o IPs desde diferentes dispositivos · decisión compartida · booster +1/+2 en score',
    $buckets['multi_persona'],$sort,$dir,false,false,'multi_persona');

render_bkt('🧾 Revisión profunda',
    'Exige lectura real (visible) y foco en precio/totales. Análisis serio, no solo muchas vistas.',
    $buckets['revision_profunda'],$sort,$dir,false,false,'revision_profunda');

render_bkt('🟩 Vistas múltiples',
    '(2+ IPs en 24h) O (3+ vistas en 24h) y última vista en 24h',
    $buckets['vistas_multiples'],$sort,$dir,false,false,'vistas_multiples');

render_bkt('🟠 Hesitación',
    'Pausa entre 24h y 7d, con repetición guest limitada y al menos una señal de fricción real en precio/totales',
    $buckets['hesitacion'],$sort,$dir,false,false,'hesitacion');

render_bkt('🟤 Sobre-análisis',
    'v2.3: Muchas sesiones + muchos guests + edad alta + FIT bajo (umbral por modo). Posible parálisis de decisión.',
    $buckets['sobre_analisis'],$sort,$dir,false,false,'sobre_analisis');

render_bkt('💜 Revivió cotización vieja (señal exclusiva)',
    'Volvió tras 30+ días sin verla y última vista en 48h',
    $buckets['revivio'],$sort,$dir,true,false,'revivio');

render_bkt('🟣 Regreso después de +4 días (señal exclusiva)',
    'Volvió tras 4+ días sin verla y última vista en 48h',
    $buckets['regreso'],$sort,$dir,true,false,'regreso');

render_bkt('🟠 Comparando / Compartiendo (señal exclusiva)',
    'v2.3: 2+ IPs distintas en ventana + al menos 1 evento JS (anti-bot). Indica comité o compartido.',
    $buckets['comparando'],$sort,$dir,false,false,'comparando');

// Enfriándose con motivo
$cooling = $buckets['enfriandose'];
foreach ($cooling as &$cr) {
    $keys = is_array($cr['senales']) ? array_keys($cr['senales']) : [];
    $with_p = array_intersect(['price_loop','tot_rev','tot_view','cupon','sv_price','mv_price'], $keys);
    $cr['reason'] = count($with_p) ? '💸 con precio' : '🧊 sin precio';
}
unset($cr);
render_bkt('🔵 Enfriándose (señal exclusiva)',
    'v2.3: Tuvo sesiones + engagement real (scroll/visible/open) pero dejó de ver. Distingue precio/sin precio. Sin engagement previo = no aparece (está perdido, no enfriándose).',
    $cooling,$sort,$dir,false,true,'enfriandose');

render_bkt('❌ No abierta',
    'Cotización con más de 24h y dentro de su vigencia, sin evidencia de apertura por el cliente — ni vistas externas ni eventos JS.',
    $buckets['no_abierta'] ?? [],$sort,$dir,false,false,'no_abierta');

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
    <span class="rbk-n"><?= $stat_total ?> total</span>
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
      <?php
        $rg_icons = $r['senales']['icons'] ?? [];
        $rg_ico = '';
        if (!empty($rg_icons['coupon']))     $rg_ico .= '🎟️';
        if (!empty($rg_icons['promo']))      $rg_ico .= '💣';
        if (!empty($rg_icons['price']))      $rg_ico .= '💸';
        if (!empty($rg_icons['sv_price']))   $rg_ico .= '👤';
        if (!empty($rg_icons['mv_price']))   $rg_ico .= '👥';
        if (!empty($rg_icons['not_opened'])) $rg_ico .= '❌';
      ?>
      <td><div class="rtit"><?= $rg_ico ? $rg_ico.' ' : '' ?><?= e($r['titulo']) ?></div><div class="rsub"><?= e($r['cliente']) ?></div></td>
      <td class="tc"><?= $ab ?></td>
      <td class="tr"><b><?= number_format($r['fit_pct'],1) ?>%</b></td>
      <td class="tr"><b><?= number_format($r['priority_pct'],1) ?>%</b></td>
      <td class="tr"><?= $r['visitas']>0 ? '<b>'.$r['visitas'].'</b>' : '—' ?></td>
      <td><?= date('m-d H:i',$r['last_ts']) ?> <span class="ago">(<?= rhace($r['last_ts']) ?>)</span></td>
      <td><?= rbadge($r['bucket'],$r['score'],$BM,$r['momentum'] ?? 'stable') ?></td>
      <td><a href="/cotizaciones/<?= (int)$r['id'] ?>" class="rlnk">Editar</a></td>
    </tr>
    <?php if ($debug_mode):
        $dbg = $r['senales']['debug'] ?? [];
        $sn  = $r['senales']['senales'] ?? [];
        $bks = $r['senales']['buckets'] ?? [];
        $ics = $r['senales']['icons'] ?? [];
        $pcs = $r['senales']['pc_source'] ?? null;
    ?>
    <tr class="dbg-row"><td colspan="8" style="padding:6px 12px 10px;background:#fffbeb;border-bottom:2px solid #fde68a">
      <div class="dbg-grid">
        <div class="dbg-sec"><div class="dbg-lbl">Internos</div><div class="dbg-val">
          sess:<b><?= $dbg['sessions']??'?' ?></b>
          guest:<b><?= $dbg['guest']??'?' ?></b>
          ips:<b><?= $dbg['uniq_ips']??'?' ?></b>
          gap:<b><?= isset($dbg['gap_days']) && $dbg['gap_days']!==null ? $dbg['gap_days'].'d' : '—' ?></b>
          v24:<b><?= $dbg['views24']??'?' ?></b>
          v48:<b><?= $dbg['views48']??'?' ?></b>
          span48:<b><?= $dbg['span48h']??'?' ?></b>
          pss:<b><?= $dbg['pss']??'?' ?></b>
          ev_v:<b><?= $dbg['ev_uniq_v']??'?' ?></b>
          vids:<b><?= $dbg['vids_post']??'0' ?></b>
          mvid:<b><?= $dbg['multi_vid']??'0' ?></b>
          modo:<b><?= $dbg['modo']??'?' ?></b>
        </div></div>
        <?php if (!empty($dbg['devices'])): ?>
        <div class="dbg-sec"><div class="dbg-lbl">Dispositivos</div><div class="dbg-val">
          <?= implode(' · ', array_map(fn($d) => "<b>{$d}</b>", $dbg['devices'])) ?>
        </div></div>
        <?php endif; ?>
        <?php if ($sn): ?>
        <div class="dbg-sec"><div class="dbg-lbl">Señales</div><div class="dbg-val">
          <?php foreach ($sn as $sk=>$sv): ?><span class="dbg-tag<?= $sv?' dbg-on':'' ?>"><?= $sk ?></span> <?php endforeach; ?>
        </div></div>
        <?php endif; ?>
        <div class="dbg-sec"><div class="dbg-lbl">Buckets</div><div class="dbg-val">
          <?php if ($bks): foreach ($bks as $bk): $is_main=($bk===($r['bucket']??'')); ?>
            <span class="dbg-bkt<?= $is_main?' dbg-main':'' ?>"><?= $bk ?></span>
          <?php endforeach; else: ?>
            <span style="color:#9ca3af">ninguno</span>
          <?php endif; ?>
          <?php if ($pcs): ?> <span style="color:#6b7280;font-size:11px">pc_source=<?= $pcs ?></span><?php endif; ?>
        </div></div>
        <?php if ($ics): ?>
        <div class="dbg-sec"><div class="dbg-lbl">Icons</div><div class="dbg-val">
          <?php foreach ($ics as $ik=>$iv): if($iv): ?><span class="dbg-tag dbg-on"><?= $ik ?></span> <?php endif; endforeach; ?>
        </div></div>
        <?php endif; ?>
        <?php $pc_d = $dbg['pc_cats'] ?? null; if ($pc_d):
            $sess_ok = $pc_d['min_sess_ok'] ?? false;
            $strong_ok = $pc_d['has_strong'] ?? false;
        ?>
        <div class="dbg-sec"><div class="dbg-lbl">Prob. Cierre (<?= $pc_d['total'] ?>/4 cats)</div><div class="dbg-val">
          <?php foreach (['engagement','precio'] as $cat): $on=$pc_d[$cat]??false; ?>
            <?php if ($on): ?><span class="dbg-tag dbg-on">⚡<?= $cat ?></span>
            <?php else: ?><span class="dbg-tag dbg-off"><?= $cat ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php foreach (['persistencia','social'] as $cat): $on=$pc_d[$cat]??false; ?>
            <span class="dbg-tag<?= $on?' dbg-on':' dbg-off' ?>"><?= $cat ?></span>
          <?php endforeach; ?>
          <span class="dbg-tag<?= $sess_ok?' dbg-on':' dbg-fail' ?>">sess≥2</span>
          <span class="dbg-tag<?= $strong_ok?' dbg-on':' dbg-fail' ?>">cat_fuerte</span>
        </div></div>
        <?php endif; ?>
        <?php if (isset($dbg['scroll_cls'])): ?>
        <div class="dbg-sec"><div class="dbg-lbl">Engagement</div><div class="dbg-val">
          scroll_cls:<b><?= $dbg['scroll_cls']??0 ?>%</b>
          scroll_any:<b><?= $dbg['scroll_any']??0 ?>%</b>
          vis_max:<b><?= $dbg['vis_max']??0 ?>ms</b>
          vis_sum:<b><?= $dbg['vis_sum']??0 ?>ms</b>
          ips_post:<b><?= $dbg['ips_post_guest']??0 ?></b>
        </div></div>
        <?php endif; ?>
      </div>
    </td></tr>
    <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
</div>

<!-- ===== TAB: CONFIG ===== -->
<div class="tab-panel" id="tab-config">

<div class="card" style="margin-bottom:16px">
  <div style="font-weight:700;font-size:14px;margin-bottom:12px">Sensibilidad del motor</div>
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
  <div style="font-weight:700;font-size:14px;margin-bottom:6px">IPs internas (excluidas del radar)</div>
  <div style="font-weight:400;font-size:12px;color:var(--t3);margin-bottom:12px">
    Tu IP actual <b><?= e($ip_actual) ?></b> se registró automáticamente al abrir el radar.
  </div>
  <?php if (empty($ips_internas)): ?>
  <div style="padding:14px;color:var(--t3);font-weight:400;font-size:13px">Sin IPs registradas.</div>
  <?php else: ?>
  <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
    <?php foreach ($ips_internas as $ip): ?>
    <div class="ip-irow">
      <span style="font-weight:500;font-size:14px;font-family:var(--num);flex:1"><?= e($ip['ip']) ?></span>
      <span style="font-weight:400;font-size:12px;color:var(--t3)"><?= e($ip['fuente']??'') ?> · <?= date('d M Y',strtotime($ip['created_at'])) ?></span>
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
           style="flex:1;min-width:160px;padding:8px 12px;border:1px solid var(--border);border-radius:var(--r-sm);font-weight:400;font-size:13px">
    <button class="btn btn-primary" onclick="addIp()">Agregar</button>
  </div>
  <?php endif; ?>
</div>

</div><!-- /tab-config -->

<script>
const CSRF_R='<?= csrf_token() ?>';

// Feedback del Radar
async function radarFb(cotId, tipo, btn) {
    try {
        const r = await fetch('/api/radar-feedback', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-Token':CSRF_R},
            body: JSON.stringify({cotizacion_id: cotId, tipo: tipo})
        });
        const d = await r.json();
        if (d.ok) {
            // Actualizar UI
            const wrap = btn.parentElement;
            wrap.querySelectorAll('.fb-btn').forEach(b => {
                b.classList.remove('fb-active','fb-pos','fb-neg');
            });
            btn.classList.add('fb-active');
            btn.classList.add(tipo === 'con_interes' ? 'fb-pos' : 'fb-neg');
        }
    } catch(e) {}
}

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
  fetch('/config/radar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_R},body:JSON.stringify({sensibilidad:modo})})
    .then(()=>location.reload());
}
function togCfg(k,v){
  fetch('/config/radar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_R},body:JSON.stringify({[k]:v})});
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
function openPlaybook(key){
  const el=document.getElementById('pb-'+key);
  if(el) el.classList.add('open');
  document.body.style.overflow='hidden';
}
function closePlaybook(key){
  const el=document.getElementById('pb-'+key);
  if(el) el.classList.remove('open');
  document.body.style.overflow='';
}
document.addEventListener('keydown',function(e){
  if(e.key==='Escape'){
    document.querySelectorAll('.pb-overlay.open').forEach(function(o){o.classList.remove('open')});
    document.body.style.overflow='';
  }
});
</script>

<?php
// ─── Render playbook modals ─────────────────────────────────
$_PB = $GLOBALS['PLAYBOOK'] ?? [];
$_BH = $GLOBALS['BKT_HINTS'] ?? [];
foreach ($_PB as $pb_key => $pb):
    $pb_pclass = str_replace('í','i',$pb['priority']); // critica, alta, media
    $pb_hint = $_BH[$pb_key] ?? '';
?>
<div class="pb-overlay" id="pb-<?= $pb_key ?>" onclick="if(event.target===this)closePlaybook('<?= $pb_key ?>')">
  <div class="pb-modal">
    <div class="pb-modal-hd">
      <div>
        <h2>📖 <?= e($BM[$pb_key][3] ?? ucfirst(str_replace('_',' ',$pb_key))) ?></h2>
        <?php if ($pb_hint): ?><p style="font-weight:400;font-size:12px;color:#6b7280;margin:4px 0 0;line-height:1.4"><?= e($pb_hint) ?></p><?php endif; ?>
        <p><?= e($pb['summary']) ?></p>
        <span class="pb-priority <?= $pb_pclass ?>"><?= e(ucfirst($pb['priority'])) ?></span>
      </div>
      <button class="pb-close" onclick="closePlaybook('<?= $pb_key ?>')">✕</button>
    </div>
    <div class="pb-body">
      <div class="pb-section">
        <h4>🧠 Qué está pensando el cliente</h4>
        <div class="pb-psych"><?= e($pb['psychology']) ?></div>
      </div>

      <div class="pb-section">
        <h4>Qué significa esta señal</h4>
        <div class="pb-mini">
          <ul><?php foreach($pb['meaning'] as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
        </div>
      </div>

      <div class="pb-cols">
        <div class="pb-mini do-card">
          <h4>✅ Qué hacer</h4>
          <ul><?php foreach($pb['do'] as $d): ?><li><?= e($d) ?></li><?php endforeach; ?></ul>
        </div>
        <div class="pb-mini dont-card">
          <h4>🚫 Qué NO hacer</h4>
          <ul><?php foreach($pb['dont'] as $d): ?><li><?= e($d) ?></li><?php endforeach; ?></ul>
        </div>
      </div>

      <div class="pb-section">
        <h4>💬 Mensajes sugeridos</h4>
        <?php foreach($pb['messages'] as $msg):
            $isCall = str_contains($msg['canal'], '📞') || str_contains(strtolower($msg['canal']), 'llamad');
        ?>
        <div class="pb-msg">
          <div class="pb-msg-canal <?= $isCall ? 'call' : 'wa' ?>"><?= e($msg['canal']) ?></div>
          <div class="pb-msg-texto"><?= e($msg['texto']) ?></div>
          <div class="pb-msg-nota">→ <?= e($msg['nota']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="pb-footer">
        <div class="pb-footer-item">🎯 Objetivo: <b><?= e($pb['goal']) ?></b></div>
        <div class="pb-footer-item">📱 Canal: <b><?= e($pb['best_channel']) ?></b></div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
