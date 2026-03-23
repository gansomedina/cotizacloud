<?php
// ============================================================
//  OnTime — ontimetermo.php  v1.0
//  APC: Algoritmo de Productividad Comercial (adaptado de CotizaCloud)
//
//  3 DIMENSIONES:
//    Activación   (20%) — ¿las cotizaciones llegan al cliente?
//    Seguimiento  (35%) — ¿usa el radar? ¿reacciona a señales?
//    Conversión   (45%) — close rate + calidad de cierres + velocidad
//
//  Contexto: 2 usuarios (admin + vendedor), WordPress + Sliced Invoices
// ============================================================
require_once __DIR__ . '/wp-load.php';

$current_user = wp_get_current_user();
$login = strtolower($current_user->user_login ?? '');

if (!is_user_logged_in() || !(current_user_can('manage_options') || $login === 'ontime')) {
    status_header(403);
    exit('No autorizado');
}

date_default_timezone_set(wp_timezone_string());
$now = time();
global $wpdb;

// ============================================================
//  CONFIG
// ============================================================
$PERIODO = 30; // días rolling
$GRACIA_DIAS = 7;

// Usuario a evaluar: admin ve al vendedor, vendedor se ve a sí mismo
$is_admin = current_user_can('manage_options');

// Buscar el vendedor (usuario no-admin con acceso al radar)
$vendedor_user = null;
$all_users = get_users(['role__not_in' => ['subscriber']]);
foreach ($all_users as $u) {
    if (!user_can($u, 'manage_options') || strtolower($u->user_login) === 'ontime') {
        $vendedor_user = $u;
        break;
    }
}
// Fallback: si no encuentra vendedor separado, evalúa al usuario actual
if (!$vendedor_user) {
    $vendedor_user = $current_user;
}

$target_user_id = $is_admin ? (int)$vendedor_user->ID : (int)$current_user->ID;
$target_login = $is_admin ? $vendedor_user->user_login : $current_user->user_login;

// Tablas
$usage_table = $wpdb->prefix . 'radar_usage_events';
$events_table = $wpdb->prefix . 'sliced_quote_events';
$transitions_table = $wpdb->prefix . 'radar_bucket_transitions';

$table_exists = function($t) use ($wpdb) {
    return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $t)) === $t;
};

$has_usage = $table_exists($usage_table);
$has_events = $table_exists($events_table);
$has_transitions = $table_exists($transitions_table);

// ============================================================
//  PERÍODO DE GRACIA
// ============================================================
$primer_uso = $has_usage ? $wpdb->get_var($wpdb->prepare(
    "SELECT MIN(created_ts) FROM {$usage_table} WHERE user_id=%d", $target_user_id
)) : null;

$dias_en_plataforma = $primer_uso
    ? (int)ceil(($now - (int)$primer_uso) / 86400)
    : 0;

$en_gracia = ($dias_en_plataforma < $GRACIA_DIAS);

// ============================================================
//  BENCHMARKS DE LA EMPRESA (auto-calculados)
// ============================================================
$periodo_start = date('Y-m-d', $now - $PERIODO * 86400);

// Total cotizaciones (no borrador)
$total_cots = (int)$wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts}
     WHERE post_type='sliced_quote'
     AND post_status IN ('publish','draft','private')"
);

// Cotizaciones del período
$cots_periodo = (int)$wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts}
     WHERE post_type='sliced_quote'
     AND post_status IN ('publish','draft','private')
     AND post_date >= %s",
    $periodo_start
));

// Ventas (accepted) del período
$accepted_term = get_term_by('slug', 'accepted', 'quote_status');
$ventas_periodo = 0;
$ventas_total = 0;
if ($accepted_term) {
    $ventas_periodo = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT tr.object_id)
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tr.term_taxonomy_id = %d
         AND p.post_type = 'sliced_quote'
         AND p.post_date >= %s",
        $accepted_term->term_taxonomy_id,
        $periodo_start
    ));
    $ventas_total = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT tr.object_id)
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tr.term_taxonomy_id = %d
         AND p.post_type = 'sliced_quote'",
        $accepted_term->term_taxonomy_id
    ));
}

$bench_close_rate = $total_cots > 0 ? $ventas_total / $total_cots : 0.10;
$bench_apertura = 0.60; // % cotizaciones que el cliente abrió (default)

// Cotizaciones con al menos 1 vista
$cots_con_vista = (int)$wpdb->get_var(
    "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sliced_log'
     WHERE p.post_type = 'sliced_quote'
     AND p.post_status IN ('publish','draft','private')"
);
if ($total_cots > 0) {
    $bench_apertura = max(0.10, $cots_con_vista / $total_cots);
}

// Radar sessions por semana (benchmark)
$bench_radar_weekly = 3.0; // default: 3 sesiones/semana
if ($has_usage) {
    $total_radar = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$usage_table}
         WHERE event_type IN ('radar_open','radar_refresh')
         AND created_ts >= %d",
        $now - $PERIODO * 86400
    ));
    $semanas = max($PERIODO / 7, 1);
    $bench_radar_weekly = max(1.0, $total_radar / $semanas);
}

// ============================================================
//  SEÑALES CRUDAS DEL VENDEDOR
// ============================================================

// Cotizaciones del vendedor en el período
// En OnTime con 1 vendedor, todas las cotizaciones son suyas
$cot_asignadas = $cots_periodo;

// Cotizaciones vistas por el cliente (tienen log de quote_viewed)
$cot_vistas = (int)$wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sliced_log'
     WHERE p.post_type = 'sliced_quote'
     AND p.post_status IN ('publish','draft','private')
     AND p.post_date >= %s",
    $periodo_start
));

// Cotizaciones dormidas (enviadas sin vista, >7 días)
$dormidas_7d = 0;
$dormidas_14d = 0;
// Buscar cotizaciones sin vistas en el período
$sin_vista = $wpdb->get_results($wpdb->prepare(
    "SELECT p.ID, p.post_date FROM {$wpdb->posts} p
     WHERE p.post_type = 'sliced_quote'
     AND p.post_status IN ('publish','draft','private')
     AND p.post_date >= %s
     AND NOT EXISTS (
         SELECT 1 FROM {$wpdb->postmeta} pm
         WHERE pm.post_id = p.ID AND pm.meta_key = '_sliced_log'
         AND pm.meta_value IS NOT NULL AND pm.meta_value != ''
     )",
    $periodo_start
), ARRAY_A);

foreach ($sin_vista as $sv) {
    $age = ($now - strtotime($sv['post_date'])) / 86400;
    if ($age >= 14) $dormidas_14d++;
    elseif ($age >= 7) $dormidas_7d++;
}

// Cierres del período
$cierres_total = $ventas_periodo;

// Sesiones de radar del vendedor
$radar_sessions = 0;
$dias_activos = 0;
if ($has_usage) {
    $radar_sessions = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$usage_table}
         WHERE user_id=%d AND event_type IN ('radar_open','radar_refresh')
         AND created_ts >= %d",
        $target_user_id, $now - $PERIODO * 86400
    ));
    $dias_activos = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT DATE(FROM_UNIXTIME(created_ts))) FROM {$usage_table}
         WHERE user_id=%d AND event_type IN ('radar_open','radar_refresh','radar_ping','radar_scroll')
         AND created_ts >= %d",
        $target_user_id, $now - $PERIODO * 86400
    ));
}

// Señales calientes ignoradas
$senales_ignoradas = 0;
if ($has_events) {
    // Cotizaciones con 3+ sesiones en últimos 7 días = cliente interesado
    $cot_calientes = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT quote_id) FROM {$events_table}
         WHERE ts_unix >= %d
         GROUP BY quote_id HAVING COUNT(DISTINCT session_id) >= 3",
        $now - 7 * 86400
    ));

    if ($cot_calientes > 0 && $has_usage) {
        $ultimo_radar_ts = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT MAX(created_ts) FROM {$usage_table}
             WHERE user_id=%d AND event_type IN ('radar_open','radar_refresh')",
            $target_user_id
        ));
        $horas_desde_radar = $ultimo_radar_ts > 0 ? ($now - $ultimo_radar_ts) / 3600.0 : 999;

        if ($horas_desde_radar <= 24) $senales_ignoradas = 0;
        elseif ($horas_desde_radar <= 48) $senales_ignoradas = (int)ceil($cot_calientes * 0.50);
        elseif ($horas_desde_radar <= 72) $senales_ignoradas = (int)ceil($cot_calientes * 0.75);
        else $senales_ignoradas = $cot_calientes;
    }
}

// Transiciones de bucket
$transiciones_up = 0;
$transiciones_down = 0;
if ($has_transitions) {
    $bucket_temp = [
        'enfriandose' => 'frio', 'no_abierta' => 'frio', 'hesitacion' => 'frio',
        'sobre_analisis' => 'tibio', 'comparando' => 'tibio', 'regreso' => 'tibio',
        'revivio' => 'tibio', 're_enganche' => 'tibio', 'revision_profunda' => 'tibio',
        'vistas_multiples' => 'tibio', 're_enganche_caliente' => 'caliente',
        'multi_persona' => 'caliente', 'alto_importe' => 'caliente',
        'decision_activa' => 'caliente', 'prediccion_alta' => 'caliente',
        'validando_precio' => 'caliente', 'inminente' => 'caliente',
        'onfire' => 'caliente', 'probable_cierre' => 'caliente',
    ];
    $temp_order = ['frio' => 0, 'tibio' => 1, 'caliente' => 2];

    $trans = $wpdb->get_results($wpdb->prepare(
        "SELECT bucket_anterior, bucket_nuevo FROM {$transitions_table}
         WHERE created_ts >= %d",
        $now - $PERIODO * 86400
    ), ARRAY_A);

    foreach ($trans as $t) {
        $ta = $bucket_temp[$t['bucket_anterior']] ?? null;
        $tn = $bucket_temp[$t['bucket_nuevo']] ?? null;
        if (!$ta || !$tn) continue;
        if (($temp_order[$tn] ?? 0) > ($temp_order[$ta] ?? 0)) $transiciones_up++;
        elseif (($temp_order[$tn] ?? 0) < ($temp_order[$ta] ?? 0)) $transiciones_down++;
    }
}

// ============================================================
//  SIGMOID HELPER
// ============================================================
function apc_sigmoid(float $x, float $midpoint, float $steepness): float {
    return 1.0 / (1.0 + exp(-$steepness * ($x - $midpoint)));
}

// ============================================================
//  DIMENSIÓN 1: ACTIVACIÓN (20%)
// ============================================================
$asignadas_safe = max($cot_asignadas, 1);
$tasa_apertura = $cot_vistas / $asignadas_safe;

$pen_dormidas = 0.0;
if ($asignadas_safe > 0) {
    $pen_dormidas = (($dormidas_7d * 6) + ($dormidas_14d * 10)) / ($asignadas_safe * 15);
}
$pen_dormidas = min($pen_dormidas, 1.0);

$s_activacion = apc_sigmoid($tasa_apertura, $bench_apertura, 2.0 / max($bench_apertura, 0.1))
                - ($pen_dormidas * 0.4);
$s_activacion = max(0.0, min(1.0, $s_activacion));

// ============================================================
//  DIMENSIÓN 2: SEGUIMIENTO (35%)
// ============================================================
$semanas = max($PERIODO / 7, 1);
$radar_por_semana = $radar_sessions / $semanas;

$s_radar = apc_sigmoid($radar_por_semana, $bench_radar_weekly, 2.0 / max($bench_radar_weekly, 0.1));

// Penalizaciones
$pen_senales = min($senales_ignoradas * 0.10, 0.4);
$pen_trans_down = min($transiciones_down * 0.05, 0.2);
$bonus_trans_up = min($transiciones_up * 0.08, 0.2);

$s_seguimiento = ($s_radar * 0.60 + $bonus_trans_up * 0.40) - ($pen_senales + $pen_trans_down);
$s_seguimiento = max(0.0, min(1.0, $s_seguimiento));

// ============================================================
//  DIMENSIÓN 3: CONVERSIÓN (45%)
// ============================================================
$cot_vistas_safe = max($cot_vistas, 1);
$tasa_cierre = $cierres_total / $cot_vistas_safe;

$s_conversion = apc_sigmoid($tasa_cierre, $bench_close_rate, 2.0 / max($bench_close_rate, 0.01));
$s_conversion = max(0.0, min(1.0, $s_conversion));

// ============================================================
//  SCORE FINAL
// ============================================================
$w_act = 0.20;
$w_seg = 0.35;
$w_conv = 0.45;

// Si no hay datos de uso del radar, redistribuir peso
if (!$has_usage || $radar_sessions === 0) {
    $w_act = 0.25;
    $w_seg = 0.00;
    $w_conv = 0.75;
}

$score_raw = $s_activacion * $w_act + $s_seguimiento * $w_seg + $s_conversion * $w_conv;
$score_raw = max(0.05, $score_raw);
$score = (int)round($score_raw * 100);
$score = min(100, max(0, $score));

// Nivel y color
$nivel = 'frio';
$nivel_label = 'Hay oportunidad';
$nivel_color = '#3b82f6'; // azul
$nivel_bg = '#eff6ff';
$nivel_emoji = '🧊';

if ($score >= 85) {
    $nivel = 'onfire'; $nivel_label = 'On Fire'; $nivel_color = '#dc2626'; $nivel_bg = '#fef2f2'; $nivel_emoji = '🔥';
} elseif ($score >= 70) {
    $nivel = 'caliente'; $nivel_label = 'Caliente'; $nivel_color = '#ea580c'; $nivel_bg = '#fff7ed'; $nivel_emoji = '🟠';
} elseif ($score >= 55) {
    $nivel = 'tibio'; $nivel_label = 'Tibio'; $nivel_color = '#ca8a04'; $nivel_bg = '#fefce8'; $nivel_emoji = '🟡';
} elseif ($score >= 35) {
    $nivel = 'fresco'; $nivel_label = 'En seguimiento'; $nivel_color = '#0891b2'; $nivel_bg = '#ecfeff'; $nivel_emoji = '🔵';
}

// Datos para debug
$debug = [
    'periodo' => $PERIODO,
    'target_user' => $target_login,
    'cot_asignadas' => $cot_asignadas,
    'cot_vistas' => $cot_vistas,
    'dormidas_7d' => $dormidas_7d,
    'dormidas_14d' => $dormidas_14d,
    'cierres' => $cierres_total,
    'radar_sessions' => $radar_sessions,
    'dias_activos' => $dias_activos,
    'senales_ignoradas' => $senales_ignoradas,
    'trans_up' => $transiciones_up,
    'trans_down' => $transiciones_down,
    'bench_close_rate' => round($bench_close_rate * 100, 2),
    'bench_apertura' => round($bench_apertura * 100, 2),
    'bench_radar_weekly' => round($bench_radar_weekly, 1),
    's_activacion' => round($s_activacion, 3),
    's_seguimiento' => round($s_seguimiento, 3),
    's_conversion' => round($s_conversion, 3),
    'pesos' => "act={$w_act} seg={$w_seg} conv={$w_conv}",
];

// ============================================================
//  VISTA HTML
// ============================================================
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Termómetro APC — <?php echo esc_html($target_login); ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 20px; }

  .container { max-width: 700px; margin: 0 auto; }
  .header { text-align: center; margin-bottom: 24px; }
  .header h1 { font-size: 22px; color: #334155; }
  .header .subtitle { color: #64748b; font-size: 14px; margin-top: 4px; }

  /* Termómetro circular */
  .thermo-wrap { display: flex; justify-content: center; margin: 24px 0; }
  .thermo-circle {
    width: 180px; height: 180px; border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    border: 8px solid <?php echo $nivel_color; ?>;
    background: <?php echo $nivel_bg; ?>;
    transition: all 0.3s;
  }
  .thermo-score { font-size: 48px; font-weight: 800; color: <?php echo $nivel_color; ?>; line-height: 1; }
  .thermo-label { font-size: 14px; font-weight: 600; color: <?php echo $nivel_color; ?>; margin-top: 4px; }
  .thermo-emoji { font-size: 20px; margin-top: 2px; }

  /* Dimensiones */
  .dims { display: flex; gap: 12px; margin: 24px 0; }
  .dim-card {
    flex: 1; background: #fff; border-radius: 12px; padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08); text-align: center;
  }
  .dim-title { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
  .dim-bar-wrap { height: 8px; background: #e2e8f0; border-radius: 4px; margin: 10px 0 6px; overflow: hidden; }
  .dim-bar { height: 100%; border-radius: 4px; transition: width 0.5s; }
  .dim-val { font-size: 20px; font-weight: 700; }
  .dim-peso { font-size: 11px; color: #94a3b8; }

  /* KPIs */
  .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 24px 0; }
  .kpi { background: #fff; border-radius: 10px; padding: 14px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
  .kpi-val { font-size: 24px; font-weight: 700; color: #334155; }
  .kpi-label { font-size: 11px; color: #94a3b8; margin-top: 2px; }

  /* Gracia */
  .gracia { text-align: center; padding: 40px; background: #fff; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
  .gracia h2 { color: #64748b; font-size: 18px; }
  .gracia p { color: #94a3b8; margin-top: 8px; }

  /* Debug */
  .debug { margin-top: 24px; background: #fff; border-radius: 10px; padding: 14px; font-size: 12px; color: #64748b; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
  .debug b { color: #334155; }

  /* Back link */
  .back { display: inline-block; margin-bottom: 16px; color: #64748b; text-decoration: none; font-size: 13px; }
  .back:hover { color: #334155; }
</style>
</head>
<body>
<div class="container">

<a href="ontimeradar.php" class="back">← Volver al Radar</a>

<div class="header">
  <h1>Termómetro de Productividad</h1>
  <div class="subtitle"><?php echo esc_html($target_login); ?> — últimos <?php echo $PERIODO; ?> días</div>
</div>

<?php if ($en_gracia): ?>
<div class="gracia">
  <h2>📊 Recopilando información</h2>
  <p>Faltan <?php echo ($GRACIA_DIAS - $dias_en_plataforma); ?> días para tener datos suficientes.<br>
  Sigue usando el radar normalmente.</p>
</div>

<?php else: ?>

<div class="thermo-wrap">
  <div class="thermo-circle">
    <div class="thermo-score"><?php echo $score; ?></div>
    <div class="thermo-label"><?php echo esc_html($nivel_label); ?></div>
    <div class="thermo-emoji"><?php echo $nivel_emoji; ?></div>
  </div>
</div>

<div class="dims">
  <?php
  $dims = [
      ['Activación', $s_activacion, $w_act, '#3b82f6'],
      ['Seguimiento', $s_seguimiento, $w_seg, '#8b5cf6'],
      ['Conversión', $s_conversion, $w_conv, '#10b981'],
  ];
  foreach ($dims as [$name, $val, $peso, $color]):
      $pct = (int)round($val * 100);
      $peso_pct = (int)round($peso * 100);
  ?>
  <div class="dim-card">
    <div class="dim-title"><?php echo $name; ?></div>
    <div class="dim-bar-wrap">
      <div class="dim-bar" style="width:<?php echo $pct; ?>%; background:<?php echo $color; ?>;"></div>
    </div>
    <div class="dim-val" style="color:<?php echo $color; ?>;"><?php echo $pct; ?>%</div>
    <div class="dim-peso">Peso: <?php echo $peso_pct; ?>%</div>
  </div>
  <?php endforeach; ?>
</div>

<div class="kpis">
  <div class="kpi">
    <div class="kpi-val"><?php echo $cot_asignadas; ?></div>
    <div class="kpi-label">Cotizaciones</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo $cot_vistas; ?></div>
    <div class="kpi-label">Vistas por cliente</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo $cierres_total; ?></div>
    <div class="kpi-label">Cierres</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo $radar_sessions; ?></div>
    <div class="kpi-label">Sesiones radar</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo $dias_activos; ?></div>
    <div class="kpi-label">Días activos</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo round($bench_close_rate * 100, 1); ?>%</div>
    <div class="kpi-label">Close rate empresa</div>
  </div>
</div>

<?php if ($is_admin): ?>
<div class="debug">
  <b>DEBUG APC</b><br>
  <?php foreach ($debug as $k => $v): ?>
    <?php echo esc_html($k); ?>: <b><?php echo esc_html($v); ?></b> |
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>
