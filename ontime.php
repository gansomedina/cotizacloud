<?php
require_once __DIR__ . '/wp-load.php';

$current_user = wp_get_current_user();
$login = strtolower($current_user->user_login ?? '');

if (!is_user_logged_in() || !(current_user_can('manage_options') || $login === 'ontime')) {
  status_header(403);
  exit('No autorizado');
}

$radar_request_ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));

date_default_timezone_set(wp_timezone_string());
$now = time();
global $wpdb;

/** =========================
 *  TERMÓMETRO / USO RADAR
 *  ========================= */
$radar_usage_table = $wpdb->prefix . 'radar_usage_events';

/**
 * true  = solo admin ve el termómetro
 * false = todos los usuarios autorizados del radar lo ven
 */
$radar_usage_admin_only = true;

function radar_usage_table_exists($table_name, $wpdb){
  return ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name);
}

function radar_usage_client_ip_bin() {
  $ip_raw = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
  if ($ip_raw && filter_var($ip_raw, FILTER_VALIDATE_IP)) {
    return @inet_pton($ip_raw);
  }
  return null;
}

function radar_usage_client_ua() {
  return isset($_SERVER['HTTP_USER_AGENT'])
    ? substr(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])), 0, 255)
    : '';
}

function radar_usage_insert_event($wpdb, $table, $user_id, $user_login, $event_type, $event_key = '', $quote_id = 0, $session_id = '', $page_id = '', $meta = []) {
  if (!radar_usage_table_exists($table, $wpdb)) return false;

  $created_ts = time();
  $created_at = current_time('mysql');
  $meta_json = !empty($meta) ? wp_json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

  return $wpdb->insert(
    $table,
    [
      'user_id'    => (int)$user_id,
      'user_login' => (string)$user_login,
      'event_type' => (string)$event_type,
      'event_key'  => (string)$event_key,
      'quote_id'   => (int)$quote_id,
      'session_id' => substr((string)$session_id, 0, 64),
      'page_id'    => substr((string)$page_id, 0, 64),
      'meta_json'  => $meta_json,
      'ip'         => radar_usage_client_ip_bin(),
      'ua'         => radar_usage_client_ua(),
      'created_at' => $created_at,
      'created_ts' => $created_ts,
    ],
    [
      '%d','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%d'
    ]
  );
}

function radar_is_business_day($ts) {
  $w = (int)date('N', (int)$ts); // 1=lun ... 7=dom
  return ($w >= 1 && $w <= 5);
}

function radar_last_business_days($count = 5, $from_ts = null) {
  if (!$from_ts) $from_ts = time();

  $days = [];
  $cursor = strtotime(date('Y-m-d 12:00:00', (int)$from_ts));

  while (count($days) < $count) {
    if (radar_is_business_day($cursor)) {
      $days[] = date('Y-m-d', $cursor);
    }
    $cursor = strtotime('-1 day', $cursor);
  }

  return array_reverse($days);
}

function radar_current_business_days_elapsed($from_ts = null) {
  if (!$from_ts) $from_ts = time();

  $todayDow = (int)date('N', (int)$from_ts); // 1=lun ... 7=dom

  if ($todayDow >= 6) {
    return 5; // sábado/domingo muestra semana laboral completa
  }

  return $todayDow; // lun=1, mar=2, mie=3, jue=4, vie=5
}

function radar_usage_get_continuous_summary($wpdb, $table, $user_id, $business_days = 5) {
  $days = radar_last_business_days($business_days);

  if (empty($days)) {
    return [
      'score' => 0,
      'label' => 'Hay oportunidad',
      'valid_days' => 0,
      'target_days' => 1,
      'today_useful' => 0,
      'recent_useful' => 0,
      'quality_points' => 0,
      'last_activity_ts' => 0,
      'last_activity_human' => '-',
    ];
  }

  $target_days = max(1, min((int)$business_days, count($days)));

  $start_ts      = strtotime($days[0] . ' 00:00:00');
  $now_ts        = time();
  $today_key     = date('Y-m-d', $now_ts);
  $today_start   = strtotime(date('Y-m-d 00:00:00', $now_ts));
  $today_hm      = date('H:i', $now_ts);
  $today_is_biz  = radar_is_business_day($now_ts);
  $last_48h_ts   = $now_ts - (48 * 3600);

  $rows = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT event_type, event_key, created_ts
       FROM {$table}
       WHERE user_id = %d
         AND created_ts >= %d
       ORDER BY id ASC",
      (int)$user_id,
      (int)$start_ts
    ),
    ARRAY_A
  );

  $by_day = [];
  $last_activity_ts = 0;

  $recent_useful = 0;
  $recent_scroll_hits = 0;
  $recent_ping_hits = 0;

  $today_weekend_activity = 0;

  foreach ($rows as $r) {
    $etype = (string)($r['event_type'] ?? '');
    $cts   = (int)($r['created_ts'] ?? 0);
    if ($cts <= 0) continue;

    if ($cts > $last_activity_ts) {
      $last_activity_ts = $cts;
    }

    $day = date('Y-m-d', $cts);
    $hm  = date('H:i', $cts);

    // Actividad reciente global (incluye sábado/domingo)
    if ($cts >= $last_48h_ts) {
      if (in_array($etype, ['radar_open','radar_refresh','radar_ping','radar_scroll'], true)) {
        $recent_useful = 1;
      }

      if ($etype === 'radar_scroll') $recent_scroll_hits++;
      if ($etype === 'radar_ping')   $recent_ping_hits++;
    }

// Si hoy es fin de semana, detectar actividad útil de hoy
if (!$today_is_biz && $cts >= $today_start) {
  if (in_array($etype, ['radar_ping','radar_scroll'], true) && $cts >= ($now_ts - 3600)) {
    $today_weekend_activity = 1;
  }
}

    // Solo bloques para últimos días hábiles
    if (!in_array($day, $days, true)) {
      continue;
    }

    if (!isset($by_day[$day])) {
      $by_day[$day] = [
        'morning_base'    => 0,
        'morning_proof'   => 0,
        'afternoon_base'  => 0,
        'afternoon_proof' => 0,
      ];
    }

    $is_morning   = ($hm >= '08:00' && $hm <= '13:00');
    $is_afternoon = ($hm >= '13:01' && $hm <= '19:30');

    // Base del bloque
    if (in_array($etype, ['radar_open','radar_refresh'], true)) {
      if ($is_morning)   $by_day[$day]['morning_base'] = 1;
      if ($is_afternoon) $by_day[$day]['afternoon_base'] = 1;
    }

    // Prueba real de uso
    if (in_array($etype, ['radar_ping','radar_scroll'], true)) {
      if ($is_morning)   $by_day[$day]['morning_proof'] = 1;
      if ($is_afternoon) $by_day[$day]['afternoon_proof'] = 1;
    }
  }

  $valid_days = 0;
  $daily_completion_sum = 0.0;

  foreach ($days as $day) {
    $morning_ok = 0;
    $afternoon_ok = 0;

    if (!empty($by_day[$day])) {
      $morning_ok   = (!empty($by_day[$day]['morning_base']) && !empty($by_day[$day]['morning_proof'])) ? 1 : 0;
      $afternoon_ok = (!empty($by_day[$day]['afternoon_base']) && !empty($by_day[$day]['afternoon_proof'])) ? 1 : 0;
    }

    $day_completion = 0.0;
    if ($morning_ok)   $day_completion += 0.5;
    if ($afternoon_ok) $day_completion += 0.5;

    $daily_completion_sum += $day_completion;

    if ($day_completion >= 1.0) {
      $valid_days++;
    }
  }

  // Estado de hoy
  $today_morning_ok = 0;
  $today_afternoon_ok = 0;
  $today_blocks_done = 0;
  $today_useful = 0;

  if ($today_is_biz) {
    if (!empty($by_day[$today_key])) {
      $today_morning_ok   = (!empty($by_day[$today_key]['morning_base']) && !empty($by_day[$today_key]['morning_proof'])) ? 1 : 0;
      $today_afternoon_ok = (!empty($by_day[$today_key]['afternoon_base']) && !empty($by_day[$today_key]['afternoon_proof'])) ? 1 : 0;
    }

    if ($today_morning_ok)   $today_blocks_done++;
    if ($today_afternoon_ok) $today_blocks_done++;

    // Si aún va en mañana, solo exigir mañana.
    // Si ya va en tarde, puede valer mañana o tarde.
    if ($today_hm >= '08:00' && $today_hm <= '13:00') {
      $today_useful = $today_morning_ok ? 1 : 0;
    } elseif ($today_hm >= '13:01' && $today_hm <= '19:30') {
      $today_useful = ($today_morning_ok || $today_afternoon_ok) ? 1 : 0;
    } else {
      $today_useful = ($today_blocks_done > 0) ? 1 : 0;
    }
  } else {
    // Fin de semana: no exigir bloques de horario hábil
    $today_useful = $today_weekend_activity ? 1 : 0;
  }

  // 1) Constancia semanal = 70 pts
  $consistency_ratio  = min(1, ($daily_completion_sum / $target_days));
  $consistency_points = (int)round(70 * $consistency_ratio);

  // 2) Actividad reciente = 15 pts
  $recent_points = 0;
  if ($today_is_biz) {
    if ($today_blocks_done >= 2) {
      $recent_points = 15;
    } elseif ($today_blocks_done === 1) {
      $recent_points = 8;
    } elseif ($recent_useful) {
      $recent_points = 4;
    }
  } else {
    if ($today_useful) {
      $recent_points = 8;
    } elseif ($recent_useful) {
      $recent_points = 4;
    }
  }

  // 3) Calidad de uso = 15 pts
  $quality_base = ($recent_ping_hits * 1) + ($recent_scroll_hits * 2);

  $quality_points = 0;
  if ($quality_base >= 12) {
    $quality_points = 15;
  } elseif ($quality_base >= 8) {
    $quality_points = 10;
  } elseif ($quality_base >= 4) {
    $quality_points = 5;
  } elseif ($quality_base >= 1) {
    $quality_points = 2;
  }

  $score = $consistency_points + $recent_points + $quality_points;
  if ($score > 100) $score = 100;

  $label = 'Hay oportunidad';
  if ($score >= 85) $label = 'Excelente';
  elseif ($score >= 70) $label = 'Muy bien';
  elseif ($score >= 55) $label = 'Bien';
  elseif ($score >= 35) $label = 'En seguimiento';

  return [
    'score' => $score,
    'label' => $label,
    'valid_days' => $valid_days,
    'target_days' => $target_days,
    'today_useful' => $today_useful ? 1 : 0,
    'recent_useful' => $recent_useful ? 1 : 0,
    'quality_points' => $quality_points,
    'last_activity_ts' => $last_activity_ts,
    'last_activity_human' => $last_activity_ts ? hace($last_activity_ts) : '-',
  ];
}


/** =========================
 *  CONFIG
 *  ========================= */

// Excluir usuarios internos
$exclude_bys = ['admin','ontime','mlimon','nog'];

// Anti-bot por IP
$bot_ip_prefixes = [
  '66.249.',
  '40.77.','52.167.','157.55.','207.46.',
  '31.13.','66.220.','173.252.','69.171.','57.141.',
  '104.28.',
  '154.12.','185.191.','85.208.',
  '54.39.','15.235.','167.114.',
  '51.161.','51.222.','142.44.','148.113.',
];

// Anti-bot por UA
$bot_ua_contains = [
  'bot','spider','crawler','scan',
  'curl','wget','python-requests','httpclient','go-http-client','java/',
  'headless','lighthouse','pagespeed',
  'googlebot','bingbot','yandex','duckduckbot','baiduspider',
  'facebookexternalhit','meta-externalagent',
  'whatsapp','slackbot','telegrambot',
];

// INTERNAL CACHE
$INTERNAL_USER_IDS = [1, 815, 816];
$internal_ip_ttl_days = 7;
$internal_ips_file = __DIR__ . '/internal_ips.json';
$internal_visitors_file = __DIR__ . '/internal_visitors.json';

// Lookback de eventos JS
$events_js_lookback_days = 150;
$events_js_min_ts = $now - ($events_js_lookback_days * 86400);

/** =========================
 *  FASE 2: SISTEMA DE 3 MODOS
 *  Cada umbral tiene [agresivo, medio, ligero]
 *  "medio"    = valores originales de On Time (tal cual estaban)
 *  "agresivo" = umbrales más permisivos (más cotizaciones clasificadas)
 *  "ligero"   = umbrales más exigentes  (solo señales sólidas)
 *  ========================= */

/** =========================
 *  FASE 4: Config persistente (JSON en wp_options)
 *  ========================= */
function radar_config_load() {
  $defaults = [
    'sensibilidad'     => 'medio',
    'calibracion_auto' => true,
    'excluir_internos' => true,
    'filtrar_bots'     => true,
    'deduplicar_30min' => true,
  ];

  $raw = get_option('radar_config', '');
  if ($raw) {
    $cfg = json_decode($raw, true);
    if (is_array($cfg)) {
      return array_merge($defaults, $cfg);
    }
  }
  return $defaults;
}

function radar_config_save($cfg) {
  update_option('radar_config', wp_json_encode($cfg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), false);
}

$radar_config = radar_config_load();

// Admin puede cambiar modo via ?modo=agresivo|medio|ligero
if (
  current_user_can('manage_options') &&
  isset($_GET['modo']) &&
  in_array($_GET['modo'], ['agresivo', 'medio', 'ligero'], true)
) {
  $radar_config['sensibilidad'] = sanitize_text_field($_GET['modo']);
  radar_config_save($radar_config);
}

$radar_modo = $radar_config['sensibilidad'];
if (!in_array($radar_modo, ['agresivo', 'medio', 'ligero'], true)) {
  $radar_modo = 'medio';
}

// Índice del modo en los arrays: 0=agresivo, 1=medio, 2=ligero
$MODO_IDX = ['agresivo' => 0, 'medio' => 1, 'ligero' => 2];
$mi = $MODO_IDX[$radar_modo];

// Helper: obtener umbral por modo
function u($key) {
  global $U, $mi;
  return $U[$key][$mi];
}

$U = [
  // ── Deduplicación de vistas (ventana de sesión) ──────
  'dedupe_seconds'             => [1200,  1800,  3600 ],

  // ── Bucket: Probable cierre ──────────────────────────
  'hot_close_last_hours'       => [48,    24,    12   ],
  'hot_close_min_views24'      => [1,     1,     2    ],
  'hot_close_min_views7d'      => [2,     2,     3    ],

  // ── Bucket: Inminente ────────────────────────────────
  'imminent_recent_hours'      => [36,    24,    24   ],
  'imminent_min_fit_pct'       => [6.0,   8.5,   11.0 ],
  'imminent_min_age_hours'     => [2.0,   2.0,   6.0  ],
  'imminent_min_guest'         => [1,     1,     2    ],
  'imminent_ip_window_min'     => [240,   180,   120  ],
  'imminent_signal_views24'    => [1,     2,     3    ],
  'imminent_signal_ips_120m'   => [1,     2,     3    ],
  'imminent_signal_closes'     => [1,     1,     2    ],
  'imminent_signal_scroll_pct' => [70,    90,    90   ],
  'imminent_signal_vis_max'    => [8000,  15000, 20000],
  'imminent_signal_vis_sum'    => [10000, 18000, 25000],
  'imminent_signal_views48'    => [2,     3,     4    ],
  'imminent_signal_span_h'     => [3,     6,     8    ],
  'imminent_signal_coupon'     => [1,     1,     1    ],
  'imminent_min_signals'       => [1,     2,     3    ],
  'imminent_min_strong'        => [1,     1,     2    ],

  // ── Bucket: On Fire ──────────────────────────────────
  'onfire_recent_hours'        => [96,    72,    48   ],
  'onfire_min_sessions'        => [2,     2,     3    ],
  'onfire_min_scroll_pct'      => [70,    90,    90   ],
  'onfire_min_vis_sum'         => [20000, 30000, 40000],
  'onfire_min_vis_max'         => [15000, 22000, 30000],
  'onfire_min_gap_days'        => [1,     1,     1    ],
  'onfire_min_views48'         => [2,     3,     4    ],
  'onfire_min_span_h'          => [4,     6,     8    ],

  // ── Bucket: Validando precio ─────────────────────────
  'priceval_recent_hours'      => [96,    72,    48   ],
  'priceval_vis_soft'          => [2000,  4000,  6000 ],
  'priceval_vis_hard'          => [5000,  8000,  12000],
  'priceval_vis_sum'           => [10000, 14000, 20000],
  'priceval_scroll_soft'       => [40,    50,    70   ],
  'priceval_scroll_hard'       => [70,    90,    90   ],

  // ── Bucket: Predicción alta ──────────────────────────
  'predict_min_fit_pct'        => [10.0,  14.0,  18.0 ],
  'predict_recent_days'        => [45,    30,    21   ],

  // ── Bucket: Decisión activa ──────────────────────────
  'decision_window_h'          => [72,    48,    48   ],
  'decision_min_views48'       => [2,     4,     5    ],
  'decision_min_span_h'        => [3,     6,     8    ],

  // ── Bucket: Re-enganche ──────────────────────────────
  'reeng_gap_days'             => [2,     4,     6    ],
  'reeng_recent_hours'         => [240,   168,   120  ],
  'reeng_min_guest_24h'        => [1,     1,     1    ],
  'reeng_min_views24'          => [1,     1,     2    ],

  // ── Bucket: Multi-persona ────────────────────────────
  'multip_recent_hours'        => [96,    72,    48   ],
  'multip_ip_window_min'       => [720,   90,    360  ],
  'multip_min_ips_post_guest'  => [2,     3,     4    ],
  'multip_min_guest_total'     => [1,     2,     3    ],
  'multip_boost_vis_max'       => [8000,  12000, 16000],

  // ── Bucket: Revisión profunda ────────────────────────
  // Fix: medio aflojado para funcionar con volumen bajo de JS events
  // agresivo: 2 views, 0.5h span | medio: 2 views, 0.5h span | ligero: 3 views, 2h span
  'deep_recent_hours'          => [96,    72,    48   ],
  'deep_min_views48'           => [2,     2,     3    ],
  'deep_min_span_h'            => [0.5,   0.5,   2    ],
  'deep_min_guest_48h'         => [1,     1,     2    ],
  'deep_min_vis_max'           => [5000,  6000,  15000],
  'deep_min_vis_sum'           => [8000,  10000, 25000],

  // ── Bucket: Hesitación ───────────────────────────────
  'hes_min_guest_7d'           => [1,     2,     3    ],
  'hes_last_min_hours'         => [24,    24,    48   ],
  'hes_last_max_days'          => [14,    7,     7    ],
  'hes_max_ips_total'          => [3,     2,     2    ],
  'hes_max_span_h'             => [8,     6,     4    ],

  // ── Bucket: Sobre-análisis ───────────────────────────
  'over_min_sessions'          => [12,    20,    28   ],
  'over_min_guest'             => [5,     8,     12   ],
  'over_min_age_days'          => [5,     7,     10   ],
  'over_recent_days'           => [30,    21,    14   ],
  'over_max_fit_pct'           => [18.0,  14.0,  10.0 ],
  'over_max_ips_post_guest'    => [5,     4,     3    ],

  // ── Bucket: Regreso ──────────────────────────────────
  'return_gap_days'            => [2,     4,     6    ],
  'return_recent_hours'        => [72,    48,    36   ],

  // ── Bucket: Revivió ──────────────────────────────────
  'revive_gap_days'            => [15,    30,    45   ],
  'revive_recent_hours'        => [72,    48,    36   ],

  // ── Bucket: Comparando ───────────────────────────────
  'compare_min_ips'            => [2,     2,     3    ],
  'compare_window_h'           => [36,    24,    24   ],

  // ── Bucket: Enfriándose ──────────────────────────────
  'cooling_min_sessions'       => [3,     4,     5    ],
  'cooling_days'               => [10,    7,     5    ],
  'cooling_min_silence_h'      => [60,    48,    36   ],

  // ── Bucket: Alto importe ─────────────────────────────
  'high_amount_threshold'      => [80000, 120000, 160000],
  'high_amount_recent_hours'   => [72,    48,    36   ],

  // ── Bucket: Vistas múltiples ─────────────────────────
  'multi_min_ips'              => [2,     2,     3    ],
  'multi_min_views24'          => [2,     3,     4    ],
  'multi_recent_hours'         => [36,    24,    24   ],

  // ── NO ABIERTA ───────────────────────────────────────
  'not_opened_max_age_days'    => [10,    7,     5    ],
  'not_opened_min_age_min'     => [720,   1440,  2880 ],
];

// Variables derivadas
$dedupe_window_seconds     = u('dedupe_seconds');
$active_hours              = 48;

/** =========================
 *  FASE 2: MODELO FIT — Auto-calibración con Laplace smoothing
 *  ========================= */

// Tasas base neutras (fallback antes de calibrar)
$FIT_DEFAULTS = [
  'global' => 0.0815,
  'sess' => [
    '1'    => 0.0345,
    '2'    => 0.0833,
    '3-4'  => 0.0585,
    '5-7'  => 0.0968,
    '8-12' => 0.1085,
    '13+'  => 0.0740,
  ],
  'ips' => [
    '1'  => 0.0345,
    '2'  => 0.1316,
    '3'  => 0.0333,
    '4+' => 0.0823,
  ],
  'gap' => [
    'sin'  => 0.0979,
    '1-3d' => 0.0873,
    '4+d'  => 0.0775,
  ],
];

$FIT_MIN = 0.005;
$FIT_MAX = 0.25;

// Laplace smoothing alpha — previene overfitting con pocos datos
$FIT_LAPLACE_ALPHA = 5;

/**
 * Fase 2: Auto-calibración FIT
 * Calcula tasas de cierre reales desde quotes accepted en WordPress.
 * Guarda en wp_options como JSON. Recalibra proporcionalmente.
 */
function radar_fit_calibrar($wpdb, $quote_ids_all, $accepted_ids_all) {
  global $FIT_DEFAULTS, $FIT_LAPLACE_ALPHA;

  $total = count($quote_ids_all);
  $sales = count($accepted_ids_all);

  // Necesita ≥3 ventas para calibrar
  if ($sales < 3) {
    return null;
  }

  $global_rate = $sales / max(1, $total);

  // Clasificar cada quote en buckets de sesiones, IPs, gap
  $by_sess = [];
  $by_ips  = [];
  $by_gap  = [];

  $events_table = $wpdb->prefix . 'sliced_quote_events';
  $table_exists = ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $events_table)) === $events_table);

  foreach ($quote_ids_all as $qid) {
    $qid = (int)$qid;
    $is_sale = isset($accepted_ids_all[$qid]);

    // Contar sesiones y IPs desde log
    $log_val = get_post_meta($qid, '_sliced_log', true);
    $log = [];
    if ($log_val) {
      $log = is_array($log_val) ? $log_val : @unserialize($log_val);
      if (!is_array($log)) $log = [];
    }

    $sess_count = 0;
    $ips_seen = [];
    $sess_ts = [];

    foreach ($log as $ts => $entry) {
      if (!is_array($entry)) continue;
      if (($entry['type'] ?? '') !== 'quote_viewed') continue;
      $ip = trim((string)($entry['ip'] ?? ''));
      if ($ip === '') continue;
      $sess_count++;
      $ips_seen[$ip] = true;
      $sess_ts[] = (int)$ts;
    }

    // Sesiones desde JS events si hay
    if ($table_exists) {
      $ev_sess = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT session_id) FROM {$events_table} WHERE quote_id = %d",
        $qid
      ));
      if ($ev_sess > $sess_count) $sess_count = $ev_sess;
    }

    if ($sess_count <= 0) continue;

    // Excluir "compradores decididos" de la calibración:
    // Quotes con ≤1 sesión que cerraron son clientes recomendados,
    // repetidores, o que cerraron por teléfono. No tienen patrón de
    // engagement medible → contaminan los buckets si se incluyen.
    if ($sess_count <= 1 && $is_sale) continue;

    // También excluir quotes sin engagement (0-1 sesión sin venta)
    // — no aportan señal al modelo
    if ($sess_count <= 1) continue;

    $ip_count = count($ips_seen);
    sort($sess_ts);
    $gap = null;
    if (count($sess_ts) >= 2) {
      $gap = max(0, (int)floor(($sess_ts[count($sess_ts)-1] - $sess_ts[count($sess_ts)-2]) / 86400));
    }

    $sl = _bucket_sessions_label($sess_count);
    $il = _bucket_ips_label($ip_count);
    $gl = _bucket_gap_label($gap);

    if (!isset($by_sess[$sl])) $by_sess[$sl] = [0, 0];
    $by_sess[$sl][0]++;
    if ($is_sale) $by_sess[$sl][1]++;

    if (!isset($by_ips[$il])) $by_ips[$il] = [0, 0];
    $by_ips[$il][0]++;
    if ($is_sale) $by_ips[$il][1]++;

    if (!isset($by_gap[$gl])) $by_gap[$gl] = [0, 0];
    $by_gap[$gl][0]++;
    if ($is_sale) $by_gap[$gl][1]++;
  }

  // Laplace smoothing: rate = (sales + α * prior) / (total + α)
  $alpha = $FIT_LAPLACE_ALPHA;
  $smooth = function($bucket_data, $defaults, $global) use ($alpha) {
    $result = [];
    foreach ($defaults as $key => $prior) {
      $t = (int)($bucket_data[$key][0] ?? 0);
      $s = (int)($bucket_data[$key][1] ?? 0);
      $result[$key] = ($t > 0)
        ? round(($s + $alpha * $prior) / ($t + $alpha), 4)
        : $prior;
    }
    return $result;
  };

  // Isotonic monotonicity: fuerza que buckets de mayor engagement
  // tengan rate >= que los de menor engagement.
  // Si "13+" tiene rate menor que "8-12", se sube al rate de "8-12".
  // Esto corrige la inversión causada por bots/curiosos residuales.
  $isotonic = function($rates, $ordered_keys) {
    $vals = [];
    foreach ($ordered_keys as $k) {
      $vals[] = $rates[$k] ?? 0;
    }
    // Pool Adjacent Violators (PAV) — algoritmo isotónico estándar
    $n = count($vals);
    $blocks = [];
    for ($i = 0; $i < $n; $i++) {
      $blocks[] = ['sum' => $vals[$i], 'cnt' => 1];
      // Merge hacia atrás si viola monotonicidad
      while (count($blocks) >= 2) {
        $last = $blocks[count($blocks) - 1];
        $prev = $blocks[count($blocks) - 2];
        $avg_last = $last['sum'] / $last['cnt'];
        $avg_prev = $prev['sum'] / $prev['cnt'];
        if ($avg_last >= $avg_prev) break; // OK, monótono
        // Merge: fusionar último con penúltimo
        array_pop($blocks);
        $blocks[count($blocks) - 1] = [
          'sum' => $prev['sum'] + $last['sum'],
          'cnt' => $prev['cnt'] + $last['cnt'],
        ];
      }
    }
    // Reconstruir resultado
    $result = [];
    $idx = 0;
    foreach ($blocks as $b) {
      $avg = round($b['sum'] / $b['cnt'], 4);
      for ($j = 0; $j < $b['cnt']; $j++) {
        $result[$ordered_keys[$idx]] = $avg;
        $idx++;
      }
    }
    return $result;
  };

  $raw_sess = $smooth($by_sess, $FIT_DEFAULTS['sess'], $global_rate);
  $raw_ips  = $smooth($by_ips,  $FIT_DEFAULTS['ips'],  $global_rate);
  $raw_gap  = $smooth($by_gap,  $FIT_DEFAULTS['gap'],  $global_rate);

  // Aplicar monotonicidad: rates deben crecer con engagement
  $sess_keys = ['1', '2', '3-4', '5-7', '8-12', '13+'];
  $ips_keys  = ['1', '2', '3', '4+'];
  // Gap: menor gap = más reciente = mejor señal → orden inverso
  $gap_keys  = ['4+d', '1-3d', 'sin'];

  $cal = [
    'version' => 3, // v3 = power-scaled NB + isotonic PAV + excluir decididos
    'global'  => round($global_rate, 4),
    'sess'    => $isotonic($raw_sess, $sess_keys),
    'ips'     => $isotonic($raw_ips,  $ips_keys),
    'gap'     => $isotonic($raw_gap,  $gap_keys),
    'total'   => $total,
    'sales'   => $sales,
    'updated' => time(),
  ];

  update_option('radar_fit_calibracion', wp_json_encode($cal), false);

  return $cal;
}

/**
 * Fase 2: Cargar calibración (de cache wp_options o default)
 */
function radar_fit_load() {
  global $FIT_DEFAULTS;

  $raw = get_option('radar_fit_calibracion', '');
  if ($raw) {
    $cal = json_decode($raw, true);
    // Invalidar cache si es versión vieja (pre-isotonic)
    if (is_array($cal) && !empty($cal['global']) && ((int)($cal['version'] ?? 0)) >= 3) {
      return $cal;
    }
  }

  return $FIT_DEFAULTS;
}

/**
 * Fase 2: Verificar si hay que recalibrar (trigger proporcional)
 */
function radar_fit_check_auto($wpdb, $quote_ids_all, $accepted_ids_all) {
  // Forzar recalibración si cache es versión vieja (pre-isotonic)
  $raw = get_option('radar_fit_calibracion', '');
  if ($raw) {
    $check = json_decode($raw, true);
    if (is_array($check) && ((int)($check['version'] ?? 0)) < 3) {
      return radar_fit_calibrar($wpdb, $quote_ids_all, $accepted_ids_all);
    }
  }

  $cal = radar_fit_load();
  $prev_sales = (int)($cal['sales'] ?? 0);
  $prev_updated = (int)($cal['updated'] ?? 0);
  $current_sales = count($accepted_ids_all);

  // Trigger proporcional: recalibrar si 20% más ventas (mín 5, máx 50)
  $delta = max(5, min(50, (int)round($prev_sales * 0.20)));

  // Trigger temporal: >90 días sin recalibrar
  $age_days = (time() - $prev_updated) / 86400;

  if (($current_sales - $prev_sales) >= $delta || $age_days > 90) {
    return radar_fit_calibrar($wpdb, $quote_ids_all, $accepted_ids_all);
  }

  return null;
}

// Helpers de bucket labels (prefijo _ para evitar colisión con funciones existentes en scope global)
function _bucket_sessions_label($n) {
  $n = (int)$n;
  if ($n <= 1) return '1';
  if ($n === 2) return '2';
  if ($n <= 4) return '3-4';
  if ($n <= 7) return '5-7';
  if ($n <= 12) return '8-12';
  return '13+';
}

function _bucket_ips_label($n) {
  $n = (int)$n;
  if ($n <= 1) return '1';
  if ($n === 2) return '2';
  if ($n === 3) return '3';
  return '4+';
}

function _bucket_gap_label($g) {
  if ($g === null) return 'sin';
  $g = (int)$g;
  if ($g <= 0) return 'sin';
  if ($g <= 3) return '1-3d';
  return '4+d';
}

/**
 * Fase 2: P80 dinámico para alto importe
 * Calcula percentil 80 de cotizaciones reales
 */
function radar_p80_alto_importe($wpdb) {
  static $cache = null;
  if ($cache !== null) return $cache;

  $amounts = $wpdb->get_col(
    "SELECT DISTINCT pm.meta_value
     FROM {$wpdb->postmeta} pm
     INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
     WHERE pm.meta_key = '_sliced_totals_for_ordering'
       AND p.post_type = 'sliced_quote'
       AND p.post_status IN ('publish','draft','private')
       AND pm.meta_value IS NOT NULL
       AND pm.meta_value != ''
     ORDER BY CAST(pm.meta_value AS DECIMAL(20,2)) ASC"
  );

  $nums = [];
  foreach ($amounts as $a) {
    $v = (float)preg_replace('/[^0-9.\-]/', '', (string)$a);
    if ($v > 0) $nums[] = $v;
  }

  if (count($nums) < 5) {
    $cache = u('high_amount_threshold');
    return $cache;
  }

  sort($nums);
  $idx = (int)floor(count($nums) * 0.80);
  $p80 = $nums[min($idx, count($nums) - 1)];

  // Redondear a miles
  $cache = max(10000, round($p80 / 1000) * 1000);
  return $cache;
}

/**
 * Fase 2: Ciclo de venta adaptativo
 * Calcula mediana de días desde creación hasta accepted
 */
function radar_ciclo_venta($wpdb, $accepted_ids_all) {
  static $cache = null;
  if ($cache !== null) return $cache;

  if (count($accepted_ids_all) < 3) {
    $cache = ['dias' => 30, 'p25' => 15, 'p75' => 45, 'n' => 0];
    return $cache;
  }

  $diffs = [];
  foreach ($accepted_ids_all as $qid => $_) {
    $post = get_post($qid);
    if (!$post) continue;

    $created = strtotime($post->post_date_gmt . ' GMT');
    if ($created <= 0) continue;

    // Buscar fecha de accepted en log
    $log_val = get_post_meta($qid, '_sliced_log', true);
    $log = [];
    if ($log_val) {
      $log = is_array($log_val) ? $log_val : @unserialize($log_val);
      if (!is_array($log)) $log = [];
    }

    // Usar último timestamp del log como proxy de fecha de cierre
    $last_ts = 0;
    foreach ($log as $ts => $entry) {
      $ts = (int)$ts;
      if ($ts > $last_ts) $last_ts = $ts;
    }

    if ($last_ts <= $created) continue;

    $diff = max(1, (int)round(($last_ts - $created) / 86400));
    if ($diff <= 180) {
      $diffs[] = $diff;
    }
  }

  if (count($diffs) < 3) {
    $cache = ['dias' => 30, 'p25' => 15, 'p75' => 45, 'n' => 0];
    return $cache;
  }

  sort($diffs);
  $n = count($diffs);
  $mediana = $diffs[(int)floor($n / 2)];
  $p25 = $diffs[(int)floor($n * 0.25)];
  $p75 = $diffs[(int)floor($n * 0.75)];

  $cache = [
    'dias' => max(1, min(180, $mediana)),
    'p25'  => max(1, $p25),
    'p75'  => max(1, $p75),
    'n'    => $n,
  ];
  return $cache;
}

/** =========================
 *  HELPERS
 *  ========================= */
function internal_ips_load($file, $ttl_days){
  $now = time();
  $ttl = max(1, (int)$ttl_days) * 86400;

  if (!file_exists($file)) return [];

  $raw = @file_get_contents($file);
  if ($raw === false || trim($raw) === '') return [];

  $data = json_decode($raw, true);
  if (!is_array($data)) return [];

  foreach ($data as $ip => $ts) {
    $ts = (int)$ts;
    if ($ts <= 0 || ($now - $ts) > $ttl) unset($data[$ip]);
  }
  return $data;
}

function internal_ips_save($file, $data){
  $tmp = $file . '.tmp';
  $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  if ($json === false) return false;

  $fp = @fopen($tmp, 'wb');
  if (!$fp) return false;

  if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    return false;
  }

  fwrite($fp, $json);
  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);

  return @rename($tmp, $file);
}

function internal_visitors_load($file){
  if (!file_exists($file)) return [];
  $raw = @file_get_contents($file);
  if ($raw === false || trim($raw) === '') return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function recency_bonus_pct($last_ts){
  $now = time();
  if (!$last_ts) return 0.0;
  if ($last_ts >= $now - 30*60)   return 12.0;
  if ($last_ts >= $now - 4*3600)  return 8.0;
  if ($last_ts >= $now - 24*3600) return 4.0;
  if ($last_ts >= $now - 48*3600) return 2.0;
  return 0.0;
}

$limit = isset($_GET['limit']) ? max(10, min(300, (int)$_GET['limit'])) : 50;
$range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : 'all';
$debug_mode = current_user_can('manage_options') && (isset($_GET['debug']) && $_GET['debug'] == '1');

$rangeSeconds = 0;
if ($range === '48h') $rangeSeconds = 48 * 3600;
if ($range === '4h')  $rangeSeconds = 4 * 3600;
if ($range === '30m') $rangeSeconds = 30 * 60;
$minLastViewTs = $rangeSeconds ? ($now - $rangeSeconds) : 0;

$sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'priority';
$dir  = isset($_GET['dir']) ? strtolower(sanitize_text_field($_GET['dir'])) : 'desc';
$dir  = in_array($dir, ['asc','desc'], true) ? $dir : 'desc';

function toggle_dir($current, $target, $dir){
  if ($current !== $target) return 'desc';
  return $dir === 'desc' ? 'asc' : 'desc';
}

function url_q($params = []){
  $q = $_GET;
  foreach ($params as $k => $v) $q[$k] = $v;
  return '?' . http_build_query($q);
}

function hace($ts){
  $d = time() - (int)$ts;
  if ($d < 60) return $d.'s';
  if ($d < 3600) return floor($d/60).'m';
  if ($d < 86400) return floor($d/3600).'h';
  return floor($d/86400).'d';
}

function money_to_float($raw){
  if ($raw === null || $raw === '') return 0.0;
  $num = preg_replace('/[^0-9\.\-]/', '', (string)$raw);
  if ($num === '' || !is_numeric($num)) return 0.0;
  return (float)$num;
}

function fmt_money($raw){
  $v = money_to_float($raw);
  return '$' . number_format($v, 2);
}

function starts_with_any($str, $prefixes){
  foreach ($prefixes as $p) {
    if ($p !== '' && str_starts_with($str, $p)) return true;
  }
  return false;
}

function ua_is_bot($ua, $needles){
  $ua = strtolower(trim((string)$ua));
  if ($ua === '') return false;
  foreach ($needles as $n) {
    if ($n !== '' && str_contains($ua, $n)) return true;
  }
  return false;
}

function normalize_by($by_raw, &$user_login_cache){
  $by = strtolower(trim((string)$by_raw));
  if ($by === '' || $by === '0') return '';

  if (ctype_digit($by)) {
    $id = (int)$by;
    if ($id > 0) {
      if (array_key_exists($id, $user_login_cache)) {
        return $user_login_cache[$id];
      }

      $u = get_user_by('id', $id);
      $login = ($u && !empty($u->user_login))
        ? strtolower(trim((string)$u->user_login))
        : '';

      $user_login_cache[$id] = $login;
      return $login;
    }
    return '';
  }

  return $by;
}

function is_excluded_by($by, $exclude_bys){
  $by = strtolower(trim((string)$by));
  if ($by === '') return false;
  if (in_array($by, $exclude_bys, true)) return true;
  if (str_starts_with($by, 'bot_')) return true;
  return false;
}

function row_class($last_ts){
  $now = time();
  if ($last_ts >= $now - 30*60) return 'hot30';
  if ($last_ts >= $now - 4*3600) return 'hot4h';
  return '';
}

function apc_bucket_emoji($bucket) {
  $map = [
    'inminente' => '🔥', 'onfire' => '🔥',
    'validando_precio' => '💸', 'probable_cierre' => '🎯', 'probable_cierre_base' => '📈',
    'decision_activa' => '🧠', 'prediccion_alta' => '📊',
    're_enganche_caliente' => '🔥', 're_enganche' => '💜',
    'multi_persona' => '👥', 'revision_profunda' => '🔍',
    'alto_importe' => '💰', 'vistas_multiples' => '👀',
    'revivio' => '💜', 'regreso' => '🟣',
    'comparando' => '⚖️', 'sobre_analisis' => '🔄',
    'hesitacion' => '⏸️', 'enfriandose' => '🧊',
    'no_abierta' => '❌', 'activo48' => '📈',
  ];
  return $map[$bucket] ?? '🎯';
}

function hot_reason_priority($is_imminent, $is_decision, $is_revive, $is_multi, $is_return4d, $is_price_validating = false){
  if ($is_imminent) return '🔥 inminente';
  if ($is_price_validating) return '💸 precio';
  if ($is_decision) return '🧠 decisión';
  if ($is_revive)   return '💜 revivió';
  if ($is_multi)    return '🟩 múltiples';
  if ($is_return4d) return '🟣 regreso';
  return '📈 activo';
}

function bucket_sessions_label($sessions){
  $s = (int)$sessions;
  if ($s <= 1) return '1';
  if ($s === 2) return '2';
  if ($s <= 4) return '3-4';
  if ($s <= 7) return '5-7';
  if ($s <= 12) return '8-12';
  return '13+';
}

function bucket_ips_label($ips){
  $i = (int)$ips;
  if ($i <= 1) return '1';
  if ($i === 2) return '2';
  if ($i === 3) return '3';
  return '4+';
}

function bucket_gap_label($gap_days){
  if ($gap_days === null) return 'sin';
  $g = (int)$gap_days;
  if ($g <= 0) return 'sin';
  if ($g <= 3) return '1-3d';
  return '4+d';
}

/**
 * Fase 2: compute_fit_prob con calibración + Laplace + caps
 * Usa datos calibrados de la empresa si existen, sino defaults
 */
function compute_fit_prob($sessions, $uniq_ips_total, $gap_days, $GLOBAL_CLOSE_RATE, $RATE_SESS, $RATE_IPS, $RATE_GAP, $FIT_MIN, $FIT_MAX){

  $ls = bucket_sessions_label($sessions);
  $li = bucket_ips_label($uniq_ips_total);
  $lg = bucket_gap_label($gap_days);

  $rs = $RATE_SESS[$ls] ?? $GLOBAL_CLOSE_RATE;
  $ri = $RATE_IPS[$li]  ?? $GLOBAL_CLOSE_RATE;
  $rg = $RATE_GAP[$lg]  ?? $GLOBAL_CLOSE_RATE;

  // Power-scaled Naive Bayes — corrige correlación entre sesiones e IPs
  // Exponentes suman 1.0 → dampea la inflación multiplicativa
  // Sesiones: 0.50 (más informativo), IPs: 0.30, Gap: 0.20
  $GR = max(0.001, $GLOBAL_CLOSE_RATE);

  $lift_s = max(0.1, $rs / $GR);
  $lift_i = max(0.1, $ri / $GR);
  $lift_g = max(0.1, $rg / $GR);

  $fit = $GR * pow($lift_s, 0.50) * pow($lift_i, 0.30) * pow($lift_g, 0.20);

  if ($fit < $FIT_MIN) $fit = $FIT_MIN;
  if ($fit > $FIT_MAX) $fit = $FIT_MAX;

  return $fit;
}

function sort_cmp($a, $b, $sort, $dir){
  $mult = ($dir === 'asc') ? 1 : -1;

  if ($sort === 'title')    return $mult * strcmp($a['title'], $b['title']);
  if ($sort === 'sessions') return $mult * ($a['sessions'] <=> $b['sessions']);
  if ($sort === 'last')     return $mult * ($a['last_ts'] <=> $b['last_ts']);
  if ($sort === 'date')     return $mult * ($a['created_ts'] <=> $b['created_ts']);
  if ($sort === 'amount')   return $mult * ($a['amount_num'] <=> $b['amount_num']);
  if ($sort === 'fit')      return $mult * ($a['fit_prob'] <=> $b['fit_prob']);
  return $mult * ($a['priority_pct'] <=> $b['priority_pct']);
}

function visitor_short($v){
  $v = trim((string)$v);
  if ($v === '') return '-';
  return substr($v, 0, 8) . '...';
}

function render_bucket_table($title, $hint, $items, $show_gap = false, $sort = 'priority', $dir = 'desc', $show_reason_col = false){
  global $debug_mode;

  echo '<table><thead><tr>';

  echo '<th style="width:40%;">'
    .'<a href="'.esc_url(url_q(['sort'=>'title','dir'=>toggle_dir($sort,'title',$dir)])).'">Título</a> / '
    .'<a href="'.esc_url(url_q(['sort'=>'amount','dir'=>toggle_dir($sort,'amount',$dir)])).'">Importe</a>'
    .'</th>';

  if ($show_reason_col) echo '<th style="width:10%;">Motivo</th>';

  echo '<th style="width:8%;" class="center">Venta</th>';
  echo '<th style="width:8%;" class="center"><a href="'.esc_url(url_q(['sort'=>'fit','dir'=>toggle_dir($sort,'fit',$dir)])).'">Score%</a></th>';
  echo '<th style="width:10%;" class="center"><a href="'.esc_url(url_q(['sort'=>'priority','dir'=>toggle_dir($sort,'priority',$dir)])).'">Prioridad%</a></th>';
  echo '<th style="width:8%;" class="num"><a href="'.esc_url(url_q(['sort'=>'sessions','dir'=>toggle_dir($sort,'sessions',$dir)])).'">Vistas</a></th>';
  echo '<th style="width:16%;"><a href="'.esc_url(url_q(['sort'=>'last','dir'=>toggle_dir($sort,'last',$dir)])).'">Última vista</a></th>';
  echo '<th style="width:6%;"><a href="'.esc_url(url_q(['sort'=>'date','dir'=>toggle_dir($sort,'date',$dir)])).'">Creada</a></th>';
  echo '<th style="width:4%;" class="center">Ver</th>';
  echo '</tr></thead><tbody>';

  if (!$items) {
    $colspan = $show_reason_col ? 10 : 9;
    echo '<tr><td colspan="'.$colspan.'" class="center" style="color:#666;">Sin registros.</td></tr>';
  } else {
    $items = array_slice($items, 0, 12);
    foreach($items as $r){
      $cls = row_class($r['last_ts']);
      $last_extra = '';
      if ($show_gap && isset($r['gap_days']) && $r['gap_days'] !== null) {
        $last_extra = ' <span style="color:#6a1b9a; font-weight:bold;">| gap '.(int)$r['gap_days'].'d</span>';
      }

      $badge = !empty($r['accepted'])
        ? '<span class="badge badge-ok">ACCEPTED</span>'
        : '<span class="badge badge-no">no</span>';

      echo '<tr class="'.esc_attr($cls).'">';
      $title_icons = '';
      // Fase 3: momentum indicator
      $m = $r['momentum'] ?? 'none';
      if ($m === 'stable')  $title_icons .= '↑';
      elseif ($m === 'cooling') $title_icons .= '↓';
      // Fase 3: probable cierre icon
      if (!empty($r['is_probable_cierre'])) $title_icons .= '🎯';
      if (!empty($r['is_reengage_hot']))    $title_icons .= '🔥';
      if (!empty($r['has_coupon_icon'])) $title_icons .= '🎟️';
      if (!empty($r['has_promo_icon']))  $title_icons .= '💣';
      if (!empty($r['has_price_icon']))  $title_icons .= '💸';
      if (!empty($r['event_same_visitor_price_focus_flag'])) $title_icons .= '👤';
      if (!empty($r['event_multi_visitor_price_flag'])) $title_icons .= '👥';
      if (!empty($r['is_not_opened'])) $title_icons .= '❌';
      $title_show = trim($title_icons . ' ' . ($r['title'] ?? ''));

      // Fase 3: señales como tooltip
      $senales_text = '';
      if (!empty($r['senales']) && is_array($r['senales'])) {
        $parts = [];
        foreach ($r['senales'] as $s) {
          $parts[] = ($s['desc'] ?? '');
        }
        $senales_text = implode(' | ', $parts);
      }

      echo '<td><div class="titlewrap"><div class="titletext" '.($senales_text ? 'title="'.esc_attr($senales_text).'"' : '').'>'.esc_html($title_show).'</div><div class="amount">'.esc_html($r['amount_fmt']).'</div></div></td>';

      if ($show_reason_col) echo '<td>'.esc_html($r['reason'] ?? '').'</td>';

      echo '<td class="center">'.$badge.'</td>';
      echo '<td class="center"><b>'.number_format((float)$r['fit_pct'], 2).'%</b></td>';
      echo '<td class="center"><b>'.number_format((float)$r['priority_pct'], 2).'%</b></td>';
      echo '<td class="num"><b>'.(int)$r['sessions'].'</b></td>';
      if (!empty($r['last_ts'])) {
        echo '<td>'.esc_html($r['last']).' <span style="color:#666;">(hace '.esc_html(hace($r['last_ts'])).')</span>'.$last_extra.'</td>';
      } else {
        echo '<td>-</td>';
      }
      echo '<td>'.esc_html($r['created']).'</td>';
      echo '<td class="center">';
      echo '<a href="'.esc_url($r['link']).'" target="_blank">Abrir</a>';

      if ($debug_mode && current_user_can('manage_options')) {
        echo '<div style="margin-top:4px;font-size:10px;color:#777;line-height:1.15;white-space:nowrap;">';
        echo 'VU '.(int)($r['event_uniq_visitors'] ?? 0).' · ';
        echo 'VS '.(int)($r['event_uniq_sessions'] ?? 0).' · ';
        echo 'VP '.(int)($r['event_main_visitor_price_events'] ?? 0);
        echo '</div>';
      }

      echo '</td>';
      echo '</tr>';
    }
  }

  echo '</tbody></table>';
}

function render_bucket_fixed($title, $hint, $items, $show_gap = false, $sort = 'priority', $dir = 'desc', $show_reason_col = false){
  echo '<div class="section-title">'.esc_html($title).' ('.(int)(is_array($items) ? count($items) : 0).')</div>';
  echo '<div class="hint">'.esc_html($hint).'</div>';
  render_bucket_table($title, $hint, $items, $show_gap, $sort, $dir, $show_reason_col);
}

/** =========================
 *  FASE 4: Endpoint de configuración (admin only)
 *  POST radar_config_action=toggle&key=calibracion_auto
 *  ========================= */
if (
  isset($_POST['radar_config_action']) &&
  current_user_can('manage_options') &&
  is_user_logged_in()
) {
  $cfg_action = sanitize_text_field(wp_unslash($_POST['radar_config_action']));
  $cfg_key    = sanitize_text_field(wp_unslash($_POST['config_key'] ?? ''));

  if ($cfg_action === 'toggle' && in_array($cfg_key, ['calibracion_auto','excluir_internos','filtrar_bots','deduplicar_30min'], true)) {
    $cfg = radar_config_load();
    $cfg[$cfg_key] = empty($cfg[$cfg_key]);
    radar_config_save($cfg);
    wp_send_json_success(['key' => $cfg_key, 'value' => $cfg[$cfg_key]]);
  }

  if ($cfg_action === 'recalibrar') {
    $recal = radar_fit_calibrar($wpdb, $quote_ids, $accepted_ids);
    wp_send_json_success(['recalibrado' => !empty($recal), 'data' => $recal]);
  }

  wp_send_json_error(['message' => 'Acción inválida'], 400);
}

/** =========================
 *  ENDPOINT DIRECTO USO RADAR
 *  ========================= */
if (
  isset($_POST['radar_usage_action']) &&
  is_user_logged_in() &&
  (current_user_can('manage_options') || $login === 'ontime')
) {
  $action_type = sanitize_text_field(wp_unslash($_POST['radar_usage_action']));
  $session_id  = sanitize_text_field(wp_unslash($_POST['session_id'] ?? ''));
  $page_id     = sanitize_text_field(wp_unslash($_POST['page_id'] ?? ''));
  $event_key   = sanitize_text_field(wp_unslash($_POST['event_key'] ?? ''));
  $quote_id    = (int)($_POST['quote_id'] ?? 0);

  $allowed = ['radar_open','radar_refresh','filter_change','radar_ping','radar_scroll'];
  if (!in_array($action_type, $allowed, true)) {
    wp_send_json_error(['message' => 'Evento inválido'], 400);
  }

  if (!radar_usage_table_exists($radar_usage_table, $wpdb)) {
    wp_send_json_error(['message' => 'Tabla de uso no existe'], 500);
  }

  $uid = (int)($current_user->ID ?? 0);
  $ulogin = strtolower((string)($current_user->user_login ?? ''));
  $now_ts = time();
  $day_start = strtotime(date('Y-m-d 00:00:00'));

  if ($action_type === 'radar_refresh') {
    $last_refresh = (int)$wpdb->get_var($wpdb->prepare(
      "SELECT MAX(created_ts)
       FROM {$radar_usage_table}
       WHERE user_id = %d
         AND event_type = 'radar_refresh'",
      $uid
    ));
    if ($last_refresh > 0 && ($now_ts - $last_refresh) < 600) {
      wp_send_json_success(['ok' => true, 'deduped' => 1]);
    }
  }

  if ($action_type === 'filter_change' && $event_key !== '') {
    $exists = (int)$wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*)
       FROM {$radar_usage_table}
       WHERE user_id = %d
         AND event_type = 'filter_change'
         AND event_key = %s
         AND created_ts >= %d",
      $uid, $event_key, $now_ts - 300
    ));
    if ($exists > 0) {
      wp_send_json_success(['ok' => true, 'deduped' => 1]);
    }
  }

  if ($action_type === 'radar_open') {
    $exists = (int)$wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*)
       FROM {$radar_usage_table}
       WHERE user_id = %d
         AND event_type = 'radar_open'
         AND session_id = %s
         AND created_ts >= %d",
      $uid, $session_id, $day_start
    ));
    if ($exists > 0) {
      wp_send_json_success(['ok' => true, 'deduped' => 1]);
    }
  }

  if ($action_type === 'radar_ping') {
    $last_ping = (int)$wpdb->get_var($wpdb->prepare(
      "SELECT MAX(created_ts)
       FROM {$radar_usage_table}
       WHERE user_id = %d
         AND event_type = 'radar_ping'
         AND session_id = %s
         AND page_id = %s",
      $uid, $session_id, $page_id
    ));
    if ($last_ping > 0 && ($now_ts - $last_ping) < 45) {
      wp_send_json_success(['ok' => true, 'deduped' => 1]);
    }
  }

  if ($action_type === 'radar_scroll' && $event_key !== '') {
    $exists = (int)$wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*)
       FROM {$radar_usage_table}
       WHERE user_id = %d
         AND event_type = 'radar_scroll'
         AND event_key = %s
         AND session_id = %s
         AND page_id = %s",
      $uid, $event_key, $session_id, $page_id
    ));
    if ($exists > 0) {
      wp_send_json_success(['ok' => true, 'deduped' => 1]);
    }
  }

  radar_usage_insert_event(
    $wpdb,
    $radar_usage_table,
    $uid,
    $ulogin,
    $action_type,
    $event_key,
    $quote_id,
    $session_id,
    $page_id,
    [
      'range' => $range ?? '',
      'sort'  => $sort ?? '',
      'dir'   => $dir ?? '',
      'limit' => $limit ?? 0,
    ]
  );

  wp_send_json_success(['ok' => true]);
}

/** =========================
 *  FETCH BASE
 *  ========================= */
$quote_ids = get_posts([
  'post_type'      => 'sliced_quote',
  'post_status'    => ['publish','draft','private'],
  'posts_per_page' => 8000,
  'orderby'        => 'ID',
  'order'          => 'DESC',
  'fields'         => 'ids'
]);

$rows = [];

$bucket_hot_close        = [];
$bucket_imminent         = [];
$bucket_predict_high     = [];
$bucket_high_amount      = [];
$bucket_decision         = [];
$bucket_multi            = [];
$bucket_compare          = [];
$bucket_revive_old       = [];
$bucket_return4d         = [];
$bucket_active48         = [];
$bucket_cooling          = [];
$bucket_onfire           = [];
$bucket_price_validating = [];
$bucket_not_opened       = [];

$bucket_reengage_decisive = [];
$bucket_reengage_hot      = [];
$bucket_multi_persona     = [];
$bucket_deep_review       = [];
$bucket_hesitation        = [];
$bucket_over_analysis     = [];
$bucket_probable_cierre   = [];

$band_counts = [
  '0-4.99'   => ['total'=>0,'sales'=>0],
  '5-7.99'   => ['total'=>0,'sales'=>0],
  '8-9.99'   => ['total'=>0,'sales'=>0],
  '10-11.99' => ['total'=>0,'sales'=>0],
  '12+'      => ['total'=>0,'sales'=>0],
];

$total_all = 0;
$total_sales = 0;

$dbg_total_view_events = 0;
$dbg_bot_skipped = 0;
$dbg_bot_ua_skipped = 0;

$internal_ips = internal_ips_load($internal_ips_file, $internal_ip_ttl_days);
$internal_visitors = internal_visitors_load($internal_visitors_file);
$internal_ips_dirty = false;
$user_login_cache = [];

if ($radar_request_ip !== '' && filter_var($radar_request_ip, FILTER_VALIDATE_IP)) {
  $prev_ts = isset($internal_ips[$radar_request_ip]) ? (int)$internal_ips[$radar_request_ip] : 0;
  $now_ts  = time();
  if ($prev_ts <= 0 || ($now_ts - $prev_ts) > 300) {
    $internal_ips[$radar_request_ip] = $now_ts;
    $internal_ips_dirty = true;
  }
}

$dbg_internal_ips_count = count($internal_ips);
$dbg_internal_learned = 0;
$dbg_internal_guest_skipped = 0;
$dbg_reeng = ['c0'=>0,'c1'=>0,'c2'=>0,'c3'=>0,'c4'=>0,'final'=>0];
$dbg_multi = ['c0'=>0,'c1'=>0,'c2'=>0,'c3'=>0,'final'=>0];
$dbg_deep  = ['c0'=>0,'c1'=>0,'c2'=>0,'c3'=>0,'final'=>0];
$dbg_over = ['c0'=>0,'c1'=>0,'c2'=>0,'c3'=>0,'c4'=>0,'c5'=>0,'c6'=>0,'final'=>0];

/** =========================
 *  ACCEPTED IDS BULK
 *  ========================= */
$accepted_ids = [];
$tax = get_taxonomy('quote_status');
if ($tax && !empty($tax->query_var)) {
  $accepted_posts = get_posts([
    'post_type'      => 'sliced_quote',
    'post_status'    => ['publish','draft','private'],
    'posts_per_page' => 8000,
    'fields'         => 'ids',
    'tax_query'      => [
      [
        'taxonomy' => 'quote_status',
        'field'    => 'slug',
        'terms'    => ['accepted'],
      ]
    ],
  ]);
  if (!empty($accepted_posts)) {
    foreach ($accepted_posts as $aqid) {
      $accepted_ids[(int)$aqid] = true;
    }
  }
}

/** =========================
 *  FASE 2: Auto-calibración FIT + P80 + Ciclo de venta
 *  ========================= */
// Cargar calibración existente o defaults
$fit_cal = radar_fit_load();
$GLOBAL_CLOSE_RATE = (float)($fit_cal['global'] ?? $FIT_DEFAULTS['global']);
$RATE_SESS = $fit_cal['sess'] ?? $FIT_DEFAULTS['sess'];
$RATE_IPS  = $fit_cal['ips']  ?? $FIT_DEFAULTS['ips'];
$RATE_GAP  = $fit_cal['gap']  ?? $FIT_DEFAULTS['gap'];

// Auto-calibrar si corresponde (trigger proporcional: 20% más ventas o >90 días)
radar_fit_check_auto($wpdb, $quote_ids, $accepted_ids);

// Recargar después de posible recalibración
$fit_cal = radar_fit_load();

// Safety: si después de check_auto la calibración aún no es v2, forzar ahora
if (((int)($fit_cal['version'] ?? 0)) < 3 && count($accepted_ids) >= 3) {
  $fit_cal = radar_fit_calibrar($wpdb, $quote_ids, $accepted_ids);
}
$GLOBAL_CLOSE_RATE = (float)($fit_cal['global'] ?? $FIT_DEFAULTS['global']);
$RATE_SESS = $fit_cal['sess'] ?? $FIT_DEFAULTS['sess'];
$RATE_IPS  = $fit_cal['ips']  ?? $FIT_DEFAULTS['ips'];
$RATE_GAP  = $fit_cal['gap']  ?? $FIT_DEFAULTS['gap'];

// P80 dinámico: percentil 80 de cotizaciones reales
$p80_alto_importe = radar_p80_alto_importe($wpdb);

// Ciclo de venta adaptativo: mediana de días quote→accepted
$ciclo_venta = radar_ciclo_venta($wpdb, $accepted_ids);
// Persistir para que el termómetro APC lo use
update_option('radar_ciclo_venta', $ciclo_venta, false);

// Debug info
$dbg_fit_source = (!empty($fit_cal['updated'])) ? 'calibrado' : 'defaults';
$dbg_fit_sales  = (int)($fit_cal['sales'] ?? 0);
$dbg_p80        = $p80_alto_importe;
$dbg_ciclo      = $ciclo_venta;

/** =========================
 *  FASE 4: Bucket transitions log
 *  Tabla para auditoría de cambios de bucket
 *  ========================= */
$transitions_table = $wpdb->prefix . 'radar_bucket_transitions';
$transitions_table_exists = ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $transitions_table)) === $transitions_table);

if (!$transitions_table_exists && current_user_can('manage_options')) {
  $charset = $wpdb->get_charset_collate();
  $wpdb->query("
    CREATE TABLE {$transitions_table} (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      quote_id bigint(20) unsigned NOT NULL,
      bucket_anterior varchar(64) DEFAULT NULL,
      bucket_nuevo varchar(64) DEFAULT NULL,
      score_anterior decimal(6,2) DEFAULT NULL,
      score_nuevo decimal(6,2) DEFAULT NULL,
      fit_anterior decimal(6,4) DEFAULT NULL,
      fit_nuevo decimal(6,4) DEFAULT NULL,
      created_at datetime NOT NULL,
      created_ts int(10) unsigned NOT NULL,
      PRIMARY KEY (id),
      KEY idx_quote (quote_id),
      KEY idx_created (created_ts)
    ) {$charset}
  ");
  $transitions_table_exists = ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $transitions_table)) === $transitions_table);
}

// Cargar buckets previos (último bucket por quote, últimas 24h)
$prev_buckets = [];
if ($transitions_table_exists) {
  $prev_rows = $wpdb->get_results($wpdb->prepare(
    "SELECT quote_id, bucket_nuevo
     FROM {$transitions_table}
     WHERE created_ts >= %d
     ORDER BY id DESC",
    $now - 86400
  ), ARRAY_A);

  foreach ($prev_rows as $pr) {
    $qid = (int)$pr['quote_id'];
    if (!isset($prev_buckets[$qid])) {
      $prev_buckets[$qid] = (string)$pr['bucket_nuevo'];
    }
  }
}

$dbg_transitions_logged = 0;

/** =========================
 *  EVENTS TABLE AGGREGATION
 *  ========================= */
$events_table = $wpdb->prefix . 'sliced_quote_events';
$table_exists = ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $events_table)) === $events_table);

$event_stats_by_quote = [];

if ($table_exists) {
  $event_rows = $wpdb->get_results(
    $wpdb->prepare(
      "
      SELECT
        quote_id,
        event_type,
        ts_unix,
        max_scroll,
        open_ms,
        visible_ms,
        engaged_ms,
        session_id,
        page_id,
        visitor_id,
        INET6_NTOA(ip) AS ip_text
      FROM {$events_table}
      WHERE ts_unix >= %d
      ORDER BY quote_id ASC, id ASC
      ",
      $events_js_min_ts
    ),
    ARRAY_A
  );

  foreach ($event_rows as $er) {
    $qid = (int)($er['quote_id'] ?? 0);
    if ($qid <= 0) continue;

    $visitor_id = trim((string)($er['visitor_id'] ?? ''));
    if ($visitor_id !== '' && isset($internal_visitors[$visitor_id])) {
      continue;
    }

    if (!isset($event_stats_by_quote[$qid])) {
      $event_stats_by_quote[$qid] = [
        'quote_opens_count'           => 0,
        'quote_closes_count'          => 0,
        'quote_coupon_clicks'         => 0,
        'quote_totals_views_count'    => 0,
        'quote_totals_revisits_count' => 0,
        'quote_price_loops_count'     => 0,
        'quote_promo_present_count'   => 0,

        'quote_max_scroll_any'        => 0,
        'quote_max_scroll_close'      => 0,
        'quote_visible_ms_max'        => 0,
        'visible_ms_by_page'          => [],

        'uniq_visitors'               => [],
        'uniq_sessions'               => [],
        'uniq_pages'                  => [],

        'visitor_event_counts'        => [],
        'visitor_price_event_counts'  => [],
        'visitor_session_ids'         => [],
        'visitor_page_ids'            => [],
        'price_visitors'              => [],

        // ── Fase 1: métricas temporales desde events ──
        'ev_session_first_ts'         => [],   // session_id => primer ts
        'ev_session_last_ts'          => [],   // session_id => último ts
        'ev_all_ts'                   => [],   // todos los ts (para gap/span)
        'ev_last_ts'                  => 0,    // último ts global
        'ev_first_ts'                 => PHP_INT_MAX, // primer ts global
        'ev_open_ts_list'             => [],   // timestamps de quote_open events
        'ev_has_engagement'           => false, // al menos 1 evento con scroll>0 o visible>0
      ];
    }

    $st = &$event_stats_by_quote[$qid];

    $etype   = (string)($er['event_type'] ?? '');
    $mscroll = max(0, (int)($er['max_scroll'] ?? 0));
    $vms     = max(0, (int)($er['visible_ms'] ?? 0));
    $ems     = max(0, (int)($er['engaged_ms'] ?? 0));
    $sid     = trim((string)($er['session_id'] ?? ''));
    $pid     = trim((string)($er['page_id'] ?? ''));
    $ev_ts   = (int)($er['ts_unix'] ?? 0);

    // ── Fase 1: recopilar timestamps por session y globales ──
    if ($ev_ts > 0) {
      $st['ev_all_ts'][] = $ev_ts;
      if ($ev_ts > $st['ev_last_ts'])  $st['ev_last_ts'] = $ev_ts;
      if ($ev_ts < $st['ev_first_ts']) $st['ev_first_ts'] = $ev_ts;

      if ($sid !== '') {
        if (!isset($st['ev_session_first_ts'][$sid]) || $ev_ts < $st['ev_session_first_ts'][$sid]) {
          $st['ev_session_first_ts'][$sid] = $ev_ts;
        }
        if (!isset($st['ev_session_last_ts'][$sid]) || $ev_ts > $st['ev_session_last_ts'][$sid]) {
          $st['ev_session_last_ts'][$sid] = $ev_ts;
        }
      }

      if ($etype === 'quote_open') {
        $st['ev_open_ts_list'][] = $ev_ts;
      }
    }

    // Engagement real: scroll > 0 o visible_ms > 0 o engaged_ms > 0
    if ($mscroll > 0 || $vms > 0 || $ems > 0) {
      $st['ev_has_engagement'] = true;
    }

    if ($etype === 'quote_open') $st['quote_opens_count']++;
    if ($etype === 'quote_close') $st['quote_closes_count']++;
    if ($etype === 'coupon_validate_click') $st['quote_coupon_clicks']++;
    if ($etype === 'section_view_totals') $st['quote_totals_views_count']++;
    if ($etype === 'section_revisit_totals') $st['quote_totals_revisits_count']++;
    if ($etype === 'quote_price_review_loop') $st['quote_price_loops_count']++;
    if ($etype === 'promo_timer_present') $st['quote_promo_present_count']++;

    if ($mscroll > $st['quote_max_scroll_any']) $st['quote_max_scroll_any'] = $mscroll;
    if ($etype === 'quote_close' && $mscroll > $st['quote_max_scroll_close']) {
      $st['quote_max_scroll_close'] = $mscroll;
    }

    if ($vms > $st['quote_visible_ms_max']) {
      $st['quote_visible_ms_max'] = $vms;
    }

    if ($pid === '') {
      $pid = 'no_page_' . md5(($sid ?: 'no_session') . '|' . ($er['ts_unix'] ?? '0') . '|' . $etype);
    }

    if (!isset($st['visible_ms_by_page'][$pid]) || $vms > $st['visible_ms_by_page'][$pid]) {
      $st['visible_ms_by_page'][$pid] = $vms;
    }

    if ($visitor_id !== '') {
      $st['uniq_visitors'][$visitor_id] = true;

      if (!isset($st['visitor_event_counts'][$visitor_id])) {
        $st['visitor_event_counts'][$visitor_id] = 0;
      }
      $st['visitor_event_counts'][$visitor_id]++;

      if (!isset($st['visitor_session_ids'][$visitor_id])) {
        $st['visitor_session_ids'][$visitor_id] = [];
      }
      if (!isset($st['visitor_page_ids'][$visitor_id])) {
        $st['visitor_page_ids'][$visitor_id] = [];
      }

      if ($sid !== '') {
        $st['visitor_session_ids'][$visitor_id][$sid] = true;
      }
      if ($pid !== '') {
        $st['visitor_page_ids'][$visitor_id][$pid] = true;
      }
    }

    if ($sid !== '') $st['uniq_sessions'][$sid] = true;
    if ($pid !== '') $st['uniq_pages'][$pid] = true;

    $is_price_event = in_array($etype, [
      'section_view_totals',
      'section_revisit_totals',
      'quote_price_review_loop',
      'coupon_validate_click',
    ], true);

    if ($is_price_event && $visitor_id !== '') {
      if (!isset($st['visitor_price_event_counts'][$visitor_id])) {
        $st['visitor_price_event_counts'][$visitor_id] = 0;
      }
      $st['visitor_price_event_counts'][$visitor_id]++;
      $st['price_visitors'][$visitor_id] = true;
    }

    unset($st);
  }

  foreach ($event_stats_by_quote as $qid => &$st) {
    $st['quote_visible_ms_sum'] = array_sum($st['visible_ms_by_page']);

    $st['event_uniq_visitors'] = count($st['uniq_visitors']);
    $st['event_uniq_sessions'] = count($st['uniq_sessions']);
    $st['event_uniq_pages']    = count($st['uniq_pages']);

    $st['event_main_visitor_events'] = 0;
    $st['event_main_visitor_price_events'] = 0;
    $st['event_main_visitor'] = '';

    foreach ($st['visitor_event_counts'] as $visitor_id => $cnt) {
      if ($cnt > $st['event_main_visitor_events']) {
        $st['event_main_visitor_events'] = (int)$cnt;
        $st['event_main_visitor'] = (string)$visitor_id;
      }
    }

    foreach ($st['visitor_price_event_counts'] as $visitor_id => $cnt) {
      if ($cnt > $st['event_main_visitor_price_events']) {
        $st['event_main_visitor_price_events'] = (int)$cnt;
      }
    }

    $same_visitor_multi_session = false;
    foreach ($st['visitor_session_ids'] as $visitor_id => $ids) {
      if (count($ids) >= 2) {
        $same_visitor_multi_session = true;
        break;
      }
    }

    $same_visitor_multi_page = false;
    foreach ($st['visitor_page_ids'] as $visitor_id => $ids) {
      if (count($ids) >= 2) {
        $same_visitor_multi_page = true;
        break;
      }
    }

    $same_visitor_price_focus = false;
    foreach ($st['visitor_price_event_counts'] as $visitor_id => $cnt) {
      if ($cnt >= 2) {
        $same_visitor_price_focus = true;
        break;
      }
    }

    $st['event_same_visitor_multi_session_flag'] = $same_visitor_multi_session ? 1 : 0;
    $st['event_same_visitor_multi_page_flag'] = $same_visitor_multi_page ? 1 : 0;
    $st['event_same_visitor_price_focus_flag'] = $same_visitor_price_focus ? 1 : 0;
    $st['event_multi_visitor_flag'] = ($st['event_uniq_visitors'] >= 2) ? 1 : 0;
    $st['event_multi_visitor_price_flag'] = (count($st['price_visitors']) >= 2) ? 1 : 0;

    // ── Fase 1: métricas derivadas de events ──
    // Sesiones = session_ids únicos (mucho más preciso que IP dedup)
    $st['ev_sessions'] = count($st['uniq_sessions']);

    // Visitors únicos (mejor proxy de "personas" que IPs)
    $st['ev_visitors'] = count($st['uniq_visitors']);

    // Ordenar timestamps de inicio de sesión para calcular gap y ventanas
    $session_start_ts = array_values($st['ev_session_first_ts']);
    sort($session_start_ts);
    $st['ev_session_start_ts_sorted'] = $session_start_ts;

    // Views en ventanas temporales desde events
    $st['ev_views24'] = 0;
    $st['ev_views48'] = 0;
    $st['ev_views7d'] = 0;
    $ev_ts48_list = [];

    foreach ($session_start_ts as $sts) {
      if ($sts >= $now - (24*3600)) $st['ev_views24']++;
      if ($sts >= $now - (48*3600)) { $st['ev_views48']++; $ev_ts48_list[] = $sts; }
      if ($sts >= $now - (7*86400)) $st['ev_views7d']++;
    }

    // Span48 desde events
    $st['ev_span48'] = 0;
    if (count($ev_ts48_list) >= 2) {
      $st['ev_span48'] = max($ev_ts48_list) - min($ev_ts48_list);
    }

    // Gap days desde events (distancia entre penúltima y última sesión)
    $st['ev_gap_days'] = null;
    if (count($session_start_ts) >= 2) {
      $prev = $session_start_ts[count($session_start_ts) - 2];
      $last = $session_start_ts[count($session_start_ts) - 1];
      $st['ev_gap_days'] = max(0, (int)floor(($last - $prev) / 86400));
    }

    // Guest sessions desde events: en quote_events TODOS son guest
    // (el cliente viendo su cotización — no hay login)
    $st['ev_guest_sessions'] = $st['ev_sessions'];
    $st['ev_guest_24h'] = $st['ev_views24'];
    $st['ev_guest_48h'] = $st['ev_views48'];
    $st['ev_guest_7d']  = $st['ev_views7d'];

    // First guest ts (primer evento = primer guest)
    $st['ev_first_guest_ts'] = ($st['ev_first_ts'] < PHP_INT_MAX) ? $st['ev_first_ts'] : 0;

    // Flag: este quote tiene datos JS confiables
    $st['ev_has_js_data'] = ($st['ev_sessions'] > 0);
  }
  unset($st);

  // ── Fase 1: Ghost session filter ──
  // Construir índice de IPs que generaron eventos JS reales por quote
  // Se usa en el fallback de log para descartar preview bots
  // (WhatsApp, Teams, iMessage cargan URL pero no ejecutan JS)
  $event_ips_by_quote = [];
  foreach ($event_rows as $er) {
    $qid = (int)($er['quote_id'] ?? 0);
    $eip = trim((string)($er['ip_text'] ?? ''));
    if ($qid > 0 && $eip !== '') {
      $event_ips_by_quote[$qid][$eip] = true;
    }
  }
}

// Contadores debug para ghost sessions
$dbg_ghost_filtered = 0;

/** =========================
 *  MAIN LOOP
 *  Events-first: métricas core desde quote_events (session_id/visitor_id)
 *  Log (_sliced_log) como fallback para quotes sin JS events
 *  IPs del log como capa complementaria para multi-persona
 *  ========================= */
foreach ($quote_ids as $id) {
  $es = $event_stats_by_quote[$id] ?? [];
  $has_js_data = !empty($es['ev_has_js_data']);

  // ─────────────────────────────────────────────────────────
  //  CAPA 1 (log): siempre leer para IPs y has_raw_quote_viewed
  //  También es fuente primaria si no hay JS events
  // ─────────────────────────────────────────────────────────
  $log_val = get_post_meta($id, '_sliced_log', true);

  if (!$log_val) {
    $log = [];
  } else {
    $log = is_array($log_val) ? $log_val : @unserialize($log_val);
    if (!is_array($log)) $log = [];
  }

  $log_events = [];
  $has_raw_quote_viewed = false;

  foreach ($log as $ts => $entry) {
    if (!is_array($entry)) continue;
    if (($entry['type'] ?? '') !== 'quote_viewed') continue;

    if ($debug_mode) $dbg_total_view_events++;

    $by_num = (int)($entry['by'] ?? 0);
    $by_raw = (string)($entry['by']
      ?? $entry['user']
      ?? $entry['username']
      ?? $entry['user_login']
      ?? $entry['by_name']
      ?? $entry['display_name']
      ?? 'guest'
    );

    $by = normalize_by($by_raw, $user_login_cache);

    $ip = trim((string)($entry['ip'] ?? ''));
    if ($ip === '') $ip = 'sin_ip';

    if ($ip !== 'sin_ip' && starts_with_any($ip, $bot_ip_prefixes)) {
      if ($debug_mode) $dbg_bot_skipped++;
      continue;
    }

    if ($ip !== 'sin_ip' && in_array($by_num, $INTERNAL_USER_IDS, true)) {
      $internal_ips[$ip] = time();
      $internal_ips_dirty = true;
      $dbg_internal_learned++;
    }

    if (is_excluded_by($by, $exclude_bys)) continue;

    if ($by_num === 0 && $ip !== 'sin_ip' && isset($internal_ips[$ip])) {
      $dbg_internal_guest_skipped++;
      continue;
    }

    $ua = (string)($entry['ua'] ?? $entry['user_agent'] ?? $entry['agent'] ?? '');
    if (ua_is_bot($ua, $bot_ua_contains)) {
      $dbg_bot_ua_skipped++;
      continue;
    }

    // ── Fase 1: Ghost session filter ──
    // Si este quote tiene eventos JS y esta IP NO generó ningún evento JS,
    // y el log entry es guest y reciente (>2min), probablemente es un preview bot
    // (WhatsApp, Teams, iMessage, etc. cargan la URL pero no ejecutan JS)
    if ($by_num === 0 && $ip !== 'sin_ip' && isset($event_ips_by_quote[$id])) {
      $log_entry_ts = (int)$ts;

      if (!isset($event_ips_by_quote[$id][$ip]) && ($now - $log_entry_ts) > 120) {
        $dbg_ghost_filtered++;
        continue;
      }
    }

    $has_raw_quote_viewed = true;

    $log_events[] = [
      'ts'       => (int)$ts,
      'ip'       => $ip,
      'is_guest' => ($by_num === 0),
    ];
  }

  if ($log_events) {
    usort($log_events, fn($a,$b) => $a['ts'] <=> $b['ts']);
  }

  // ─────────────────────────────────────────────────────────
  //  CAPA 2 (IPs del log): siempre calcular para multi-persona
  //  Las IPs son complementarias, no primarias
  // ─────────────────────────────────────────────────────────
  $lastSeenByIp = [];
  $compare_ips_set = [];
  $multi_ips_set   = [];
  $ips_120m_set    = [];

  $compare_window = u('compare_window_h') * 3600;
  $multi_window   = u('multi_recent_hours') * 3600;
  $win_ip         = u('imminent_ip_window_min') * 60;

  $log_first_guest_ts = 0;

  foreach ($log_events as $e) {
    $ts = $e['ts'];
    $ip = $e['ip'];

    if (!isset($lastSeenByIp[$ip]) || ($ts - $lastSeenByIp[$ip]) >= $dedupe_window_seconds) {
      $lastSeenByIp[$ip] = $ts;

      if (!empty($e['is_guest']) && $log_first_guest_ts <= 0) {
        $log_first_guest_ts = $ts;
      }
    }

    if ($ts >= $now - $compare_window) $compare_ips_set[$ip] = true;
    if ($ts >= $now - $multi_window)   $multi_ips_set[$ip]   = true;
    if ($ts >= $now - $win_ip)         $ips_120m_set[$ip]    = true;
  }

  $uniq_ips_total = count($lastSeenByIp);
  $compare_ips    = count($compare_ips_set);
  $multi_ips      = count($multi_ips_set);
  $ips_120m       = count($ips_120m_set);

  // IPs post primer guest (para multi-persona)
  $ips_post_guest_win = [];
  $multip_win = u('multip_ip_window_min') * 60;
  $first_guest_ts_for_ips = $has_js_data
    ? (int)($es['ev_first_guest_ts'] ?? 0)
    : $log_first_guest_ts;

  if ($first_guest_ts_for_ips > 0) {
    $win_end = $first_guest_ts_for_ips + $multip_win;
    foreach ($log_events as $e2) {
      $ts2 = (int)$e2['ts'];
      $ip2 = (string)$e2['ip'];
      if ($ts2 < $first_guest_ts_for_ips) continue;
      if ($ts2 > $win_end) continue;
      if ($ip2 !== 'sin_ip' && $ip2 !== '') $ips_post_guest_win[$ip2] = true;
    }
  }

  $ips_post_first_guest_180m = count($ips_post_guest_win);

  // ─────────────────────────────────────────────────────────
  //  MÉTRICAS CORE: events-first, log como fallback
  // ─────────────────────────────────────────────────────────
  if ($has_js_data) {
    // ═══ FUENTE PRIMARIA: JS events ═══
    $sessions       = (int)($es['ev_sessions'] ?? 0);
    $views24        = (int)($es['ev_views24'] ?? 0);
    $views7d        = (int)($es['ev_views7d'] ?? 0);
    $views48        = (int)($es['ev_views48'] ?? 0);
    $span48         = (int)($es['ev_span48'] ?? 0);
    $last_ts        = (int)($es['ev_last_ts'] ?? 0);
    $gap_days       = $es['ev_gap_days'];  // puede ser null
    $guest_sessions = (int)($es['ev_guest_sessions'] ?? 0);
    $guest_6h       = 0; // no usado en buckets críticos
    $guest_24h      = (int)($es['ev_guest_24h'] ?? 0);
    $guest_48h      = (int)($es['ev_guest_48h'] ?? 0);
    $guest_7d       = (int)($es['ev_guest_7d'] ?? 0);
    $first_guest_ts = (int)($es['ev_first_guest_ts'] ?? 0);
    $session_ts     = $es['ev_session_start_ts_sorted'] ?? [];
    $data_source    = 'events';

    // Complementar: si hay más IPs únicas que visitors, usar max
    // (captura personas que no tienen JS — navegador viejo, etc.)
    if ($uniq_ips_total > (int)($es['ev_visitors'] ?? 0)) {
      // No reemplazar sessions, solo registrar la diferencia
      // Las IPs extras se usan solo para multi-persona (ya calculado arriba)
    }
  } else {
    // ═══ FALLBACK: _sliced_log (IP dedup) ═══
    $sessions = 0;
    $views24 = 0;
    $views7d = 0;
    $last_ts = 0;
    $session_ts = [];
    $guest_sessions = 0;
    $guest_6h  = 0;
    $guest_24h = 0;
    $guest_48h = 0;
    $guest_7d  = 0;
    $first_guest_ts = 0;

    $lastSeenByIp_fb = [];
    foreach ($log_events as $e) {
      $ts = $e['ts'];
      $ip = $e['ip'];

      if (!isset($lastSeenByIp_fb[$ip]) || ($ts - $lastSeenByIp_fb[$ip]) >= $dedupe_window_seconds) {
        $sessions++;
        $lastSeenByIp_fb[$ip] = $ts;
        $session_ts[] = $ts;

        if (!empty($e['is_guest'])) {
          $guest_sessions++;
          if ($first_guest_ts <= 0) $first_guest_ts = $ts;
          if ($ts >= $now - (6*3600))  $guest_6h++;
          if ($ts >= $now - (24*3600)) $guest_24h++;
          if ($ts >= $now - (48*3600)) $guest_48h++;
          if ($ts >= $now - (7*86400)) $guest_7d++;
        }

        if ($ts >= $now - (24*3600)) $views24++;
        if ($ts >= $now - (7*86400)) $views7d++;
        if ($ts > $last_ts) $last_ts = $ts;
      }
    }

    // Gap days desde log
    $gap_days = null;
    if (count($session_ts) >= 2) {
      $prev_ts_fb = $session_ts[count($session_ts) - 2];
      $gap_days = max(0, (int)floor(($last_ts - $prev_ts_fb) / 86400));
    }

    $views48 = 0;
    $ts48 = [];
    $win48 = u('decision_window_h') * 3600;
    foreach ($session_ts as $tss) {
      if ($tss >= $now - $win48) {
        $views48++;
        $ts48[] = $tss;
      }
    }
    $span48 = 0;
    if (count($ts48) >= 2) $span48 = max($ts48) - min($ts48);

    $data_source = 'log';
  }

  if ($sessions > 0 && $minLastViewTs && $last_ts < $minLastViewTs) continue;

  $quote_opens_count           = (int)($es['quote_opens_count'] ?? 0);
  $quote_closes_count          = (int)($es['quote_closes_count'] ?? 0);
  $quote_coupon_clicks         = (int)($es['quote_coupon_clicks'] ?? 0);
  $quote_totals_views_count    = (int)($es['quote_totals_views_count'] ?? 0);
  $quote_totals_revisits_count = (int)($es['quote_totals_revisits_count'] ?? 0);
  $quote_price_loops_count     = (int)($es['quote_price_loops_count'] ?? 0);
  $quote_promo_present_count   = (int)($es['quote_promo_present_count'] ?? 0);
  $quote_max_scroll_any        = (int)($es['quote_max_scroll_any'] ?? 0);
  $quote_max_scroll_close      = (int)($es['quote_max_scroll_close'] ?? 0);
  $quote_visible_ms_max        = (int)($es['quote_visible_ms_max'] ?? 0);
  $quote_visible_ms_sum        = (int)($es['quote_visible_ms_sum'] ?? 0);

  $event_uniq_visitors                = (int)($es['event_uniq_visitors'] ?? 0);
  $event_uniq_sessions                = (int)($es['event_uniq_sessions'] ?? 0);
  $event_uniq_pages                   = (int)($es['event_uniq_pages'] ?? 0);
  $event_main_visitor_events          = (int)($es['event_main_visitor_events'] ?? 0);
  $event_main_visitor_price_events    = (int)($es['event_main_visitor_price_events'] ?? 0);
  $event_main_visitor                 = (string)($es['event_main_visitor'] ?? '');
  $event_same_visitor_multi_session_flag = !empty($es['event_same_visitor_multi_session_flag']) ? 1 : 0;
  $event_same_visitor_multi_page_flag    = !empty($es['event_same_visitor_multi_page_flag']) ? 1 : 0;
  $event_same_visitor_price_focus_flag   = !empty($es['event_same_visitor_price_focus_flag']) ? 1 : 0;
  $event_multi_visitor_flag              = !empty($es['event_multi_visitor_flag']) ? 1 : 0;
  $event_multi_visitor_price_flag        = !empty($es['event_multi_visitor_price_flag']) ? 1 : 0;

  $has_totals_view    = ($quote_totals_views_count > 0);
  $has_totals_revisit = ($quote_totals_revisits_count > 0);
  $has_price_loop     = ($quote_price_loops_count > 0);

  // ── Fase 1: engagement gate (anti-bot behavioral filter) ──
  // Requiere al menos 1 señal de interacción JS real
  $has_any_engagement = (
    $quote_opens_count > 0 ||
    $quote_max_scroll_any > 0 ||
    $quote_visible_ms_max > 0 ||
    !empty($es['ev_has_engagement'])
  );
  $has_promo_timer    = ($quote_promo_present_count > 0);

  $price_signal_strong = ($has_price_loop || $has_totals_revisit);
  $price_signal_medium = ($has_totals_view && !$price_signal_strong);

  $price_signal_score = 0.0;
  if ($has_totals_view)    $price_signal_score += 1.0;
  if ($has_totals_revisit) $price_signal_score += 2.0;
  if ($has_price_loop)     $price_signal_score += 3.0;
  if ($quote_coupon_clicks > 0) $price_signal_score += 0.75;
  if ($has_promo_timer)         $price_signal_score += 0.25;

  if ($event_same_visitor_price_focus_flag)   $price_signal_score += 1.50;
  if ($event_multi_visitor_price_flag)        $price_signal_score += 1.25;
  if ($event_same_visitor_multi_session_flag) $price_signal_score += 0.50;
  if ($event_same_visitor_multi_page_flag)    $price_signal_score += 0.50;

  $post = get_post($id);
  if (!$post) continue;

  $created_ts = strtotime($post->post_date_gmt . ' GMT');
  $age_days = (int)floor(($now - $created_ts) / 86400);
  if ($age_days < 0) $age_days = 0;
  $age_hours = ($now - $created_ts) / 3600.0;

  $gap_days = null;
  if (count($session_ts) >= 2) {
    $prev_ts = $session_ts[count($session_ts) - 2];
    $gap_days = (int)floor(($last_ts - $prev_ts) / 86400);
    if ($gap_days < 0) $gap_days = 0;
  }

  $amount_raw = get_post_meta($id, '_sliced_totals_for_ordering', true);
  $amount_num = money_to_float($amount_raw);
  $amount_fmt = fmt_money($amount_raw);

  $accepted = isset($accepted_ids[(int)$id]);

  // NO ABIERTA
  $not_opened_age_ok = (
    $created_ts >= ($now - (u('not_opened_max_age_days') * 86400)) &&
    $age_hours >= (u('not_opened_min_age_min') / 60)
  );

  $not_opened_has_external_views = !empty($has_raw_quote_viewed);

  $not_opened_has_js_open = (
    $quote_opens_count > 0 ||
    $quote_closes_count > 0 ||
    $quote_totals_views_count > 0 ||
    $quote_totals_revisits_count > 0 ||
    $quote_price_loops_count > 0 ||
    $quote_coupon_clicks > 0 ||
    $quote_max_scroll_any > 0 ||
    $quote_visible_ms_max > 0 ||
    $quote_visible_ms_sum > 0 ||
    $event_uniq_visitors > 0 ||
    $event_uniq_sessions > 0
  );

  $is_not_opened = (
    !$accepted &&
    $not_opened_age_ok &&
    !$not_opened_has_external_views &&
    !$not_opened_has_js_open
  );

  $is_active48 = ($last_ts >= $now - ($active_hours * 3600));

  $is_hot_close = ($last_ts >= $now - (u('hot_close_last_hours') * 3600)) &&
                  ($views24 >= u('hot_close_min_views24') || $views7d >= u('hot_close_min_views7d'));

  // Fase 1: engagement gate — requiere interacción JS real para evitar falsos positivos de bots
  $is_compare = ($compare_ips >= u('compare_min_ips')) &&
                ($last_ts >= $now - (u('compare_window_h') * 3600)) &&
                $has_any_engagement;

  $is_multi = (
    (
      $multi_ips >= u('multi_min_ips') &&
      $last_ts >= $now - (u('multi_recent_hours') * 3600)
    ) || (
      $views24 >= u('multi_min_views24') &&
      $last_ts >= $now - (u('multi_recent_hours') * 3600)
    )
  );

  $is_decision = ($views48 >= u('decision_min_views48')) &&
                 ($span48 >= (u('decision_min_span_h') * 3600)) &&
                 ($last_ts >= $now - (u('decision_window_h') * 3600));

  $is_return4d = ($gap_days !== null) &&
                 ($gap_days >= u('return_gap_days')) &&
                 ($last_ts >= $now - (u('return_recent_hours') * 3600));

  $is_revive = ($gap_days !== null) &&
               ($gap_days >= u('revive_gap_days')) &&
               ($last_ts >= $now - (u('revive_recent_hours') * 3600));

  // Fase 2: P80 dinámico para alto importe
  $is_high_amount = ($amount_num >= (float)$p80_alto_importe) &&
                    ($last_ts >= $now - (u('high_amount_recent_hours') * 3600));

  // Fase 1: engagement gate — solo "enfriándose" si tuvo engagement real previo
  $is_cooling = ($sessions >= u('cooling_min_sessions')) &&
                ($last_ts <  $now - ($active_hours*3600)) &&
                ($last_ts >= $now - (u('cooling_days')*86400)) &&
                $has_any_engagement;

  $cooling_price_touched = (
    $has_totals_view ||
    $has_totals_revisit ||
    $has_price_loop ||
    $quote_coupon_clicks > 0 ||
    $price_signal_score >= 2.0 ||
    $event_same_visitor_price_focus_flag ||
    $event_multi_visitor_price_flag
  );

  $cooling_reason = $cooling_price_touched ? '💸 con precio' : '🧊 sin precio';

  // Compradores decididos: ≤1 sesión → FIT neutral (sin patrón medible)
  // Son clientes recomendados, repetidores, o que cerraron por otro canal.
  // Su engagement real puede estar en otra cotización anterior.
  $is_decided_buyer = ($sessions <= 1);

  $fit_prob = $is_decided_buyer
    ? $GLOBAL_CLOSE_RATE  // neutral: sin señal de engagement
    : compute_fit_prob($sessions, $uniq_ips_total, $gap_days, $GLOBAL_CLOSE_RATE, $RATE_SESS, $RATE_IPS, $RATE_GAP, $FIT_MIN, $FIT_MAX);
  $fit_pct  = max(0.0, min(100.0, $fit_prob * 100.0));

  $priority_pct = $fit_pct + recency_bonus_pct($last_ts);
  $priority_pct += min(4.0, $price_signal_score * 0.55);
  if ($event_same_visitor_price_focus_flag) $priority_pct += 0.75;
  if ($event_multi_visitor_price_flag)      $priority_pct += 0.50;
  if ($priority_pct > 100.0) $priority_pct = 100.0;

  /** =========================
   *  ON FIRE
   *  ========================= */
  $onfire_scroll_ok = (
    $quote_max_scroll_close >= u('onfire_min_scroll_pct') ||
    $quote_max_scroll_any   >= u('onfire_min_scroll_pct')
  );

  $onfire_visible_ok = (
    $quote_visible_ms_sum >= u('onfire_min_vis_sum') ||
    $quote_visible_ms_max >= u('onfire_min_vis_max')
  );

  $onfire_return_ok = (
    ($gap_days !== null && $gap_days >= u('onfire_min_gap_days')) ||
    (
      $views48 >= u('onfire_min_views48') &&
      $span48 >= (u('onfire_min_span_h') * 3600)
    )
  );

  $onfire_price_ok = (
    $has_price_loop ||
    $has_totals_revisit ||
    $price_signal_score >= 4.0
  );

  $onfire_visitor_ok = (
    $event_same_visitor_price_focus_flag ||
    $event_multi_visitor_price_flag ||
    $event_main_visitor_events >= 4 ||
    $event_same_visitor_multi_session_flag ||
    $event_same_visitor_multi_page_flag
  );

  $is_onfire = (
    $last_ts >= $now - (u('onfire_recent_hours') * 3600) &&
    $sessions >= u('onfire_min_sessions') &&
    $onfire_scroll_ok &&
    $onfire_visible_ok &&
    $onfire_price_ok &&
    $onfire_return_ok &&
    $onfire_visitor_ok &&
    !$accepted
  );

  /** =========================
   *  INMINENTE
   *  ========================= */
  $imminent_signal_scroll = (
    $quote_max_scroll_close >= u('imminent_signal_scroll_pct') ||
    $quote_max_scroll_any   >= u('imminent_signal_scroll_pct')
  );

  $imminent_signal_visible = (
    (
      $quote_visible_ms_max >= u('imminent_signal_vis_max') ||
      $quote_visible_ms_sum >= u('imminent_signal_vis_sum')
    ) &&
    (
      $has_totals_revisit ||
      $has_price_loop ||
      $quote_totals_views_count >= 2
    )
  );

  $imminent_signal_coupon = ($quote_coupon_clicks >= u('imminent_signal_coupon'));

  $imminent_signal_review48 = (
    $views48 >= u('imminent_signal_views48') &&
    $span48 >= (u('imminent_signal_span_h') * 3600)
  );

  $imminent_signal_views = ($views24 >= u('imminent_signal_views24'));
  $imminent_signal_ips   = ($ips_120m >= u('imminent_signal_ips_120m'));
  $imminent_signal_close = ($quote_closes_count >= u('imminent_signal_closes'));

  $imminent_signal_price_strong = (
    $has_price_loop ||
    $has_totals_revisit
  );

  $imminent_signal_price_medium = (
    !$imminent_signal_price_strong &&
    (
      $quote_totals_views_count >= 2 ||
      $price_signal_score >= 2.0
    )
  );

  $imminent_signal_promo_boost = $has_promo_timer;

  $imminent_signal_same_visitor = (
    $event_same_visitor_price_focus_flag &&
    ($event_same_visitor_multi_session_flag || $event_same_visitor_multi_page_flag || $event_main_visitor_events >= 4)
  );

  $imminent_signal_multi_visitor_price = (
    $event_multi_visitor_price_flag
  );

  $imminent_signals_total = 0;
  $imminent_signals_strong = 0;

  if ($imminent_signal_scroll)             { $imminent_signals_total++; $imminent_signals_strong++; }
  if ($imminent_signal_visible)            { $imminent_signals_total++; $imminent_signals_strong++; }
  if ($imminent_signal_review48)           { $imminent_signals_total++; $imminent_signals_strong++; }
  if ($imminent_signal_price_strong)       { $imminent_signals_total++; $imminent_signals_strong++; }
  if ($imminent_signal_same_visitor)       { $imminent_signals_total++; $imminent_signals_strong++; }

  if ($imminent_signal_views)               $imminent_signals_total++;
  if ($imminent_signal_ips)                 $imminent_signals_total++;
  if ($imminent_signal_close)               $imminent_signals_total++;
  if ($imminent_signal_coupon)              $imminent_signals_total++;
  if ($imminent_signal_price_medium)        $imminent_signals_total++;
  if ($imminent_signal_multi_visitor_price) $imminent_signals_total++;

  if (
    !$imminent_signal_price_strong &&
    $imminent_signal_price_medium &&
    $imminent_signal_scroll
  ) {
    $imminent_signals_total++;
  }

  $imminent_with_promo_boost = (
    $imminent_signal_promo_boost &&
    $imminent_signals_strong >= 1 &&
    $imminent_signals_total >= max(1, (u('imminent_min_signals') - 1))
  );

  $is_imminent = (
    $last_ts >= $now - (u('imminent_recent_hours') * 3600) &&
    $fit_pct >= (float)u('imminent_min_fit_pct') &&
    $age_hours >= (float)u('imminent_min_age_hours') &&
    $guest_sessions >= u('imminent_min_guest') &&
    (
      $imminent_signals_total >= u('imminent_min_signals') ||
      $imminent_with_promo_boost
    ) &&
    $imminent_signals_strong >= u('imminent_min_strong') &&
    !$accepted
  );

  /** =========================
   *  VALIDANDO PRECIO
   *  ========================= */
  $priceval_recent_ok = (
    $last_ts >= $now - (u('priceval_recent_hours') * 3600)
  );

  $priceval_guest_ok = (
    $guest_sessions >= 1 &&
    (
      $guest_sessions >= 2 ||
      $event_same_visitor_price_focus_flag ||
      $event_multi_visitor_price_flag
    )
  );

  $priceval_read_ok = (
    $quote_visible_ms_max >= u('priceval_vis_hard') ||
    $quote_visible_ms_sum >= u('priceval_vis_sum') ||
    (
      $quote_visible_ms_max >= u('priceval_vis_soft') &&
      $quote_max_scroll_any >= u('priceval_scroll_soft')
    )
  );

  $priceval_loop_ok = ($quote_price_loops_count >= 1);
  $priceval_revisit_ok = ($quote_totals_revisits_count >= 1);
  $priceval_view_repeat_ok = ($quote_totals_views_count >= 2);

  $priceval_same_visitor_ok = (
    $event_same_visitor_price_focus_flag ||
    $event_main_visitor_price_events >= 2
  );

  $priceval_multi_visitor_ok = (
    $event_multi_visitor_price_flag
  );

  $is_price_validating = (
    $priceval_recent_ok &&
    !$accepted &&
    $priceval_guest_ok &&
    (
      $priceval_read_ok ||
      $priceval_same_visitor_ok ||
      $priceval_multi_visitor_ok
    ) &&
    (
      $priceval_loop_ok ||
      $priceval_revisit_ok ||
      $priceval_view_repeat_ok ||
      $priceval_same_visitor_ok ||
      $priceval_multi_visitor_ok
    )
  );

  // Fase 2: Predicción alta con ciclo de venta adaptativo
  $ciclo_mult = match($radar_modo) { 'agresivo' => 1.5, 'ligero' => 0.7, default => 1.0 };
  $predict_window = max(7, min(120, (int)round($ciclo_venta['dias'] * $ciclo_mult)));
  $is_predict_high = (
    $fit_pct >= (float)u('predict_min_fit_pct') &&
    $age_days <= $predict_window
  );

  $last_between_24h_7d = (
    $last_ts <  $now - (u('hes_last_min_hours') * 3600) &&
    $last_ts >= $now - (u('hes_last_max_days') * 86400)
  );

  $reeng_recent_interest_ok = (
    $guest_24h >= u('reeng_min_guest_24h') ||
    $views24 >= u('reeng_min_views24') ||
    $has_totals_view ||
    $has_totals_revisit ||
    $has_price_loop ||
    $price_signal_score >= 2.0 ||
    $event_same_visitor_price_focus_flag
  );

  $is_reengage_decisive = (
    $gap_days !== null &&
    $gap_days >= u('reeng_gap_days') &&
    $last_ts >= $now - (u('reeng_recent_hours') * 3600) &&
    $reeng_recent_interest_ok &&
    !$accepted
  );

  $multi_validation_boost_ok = (
    $has_totals_revisit ||
    $has_price_loop ||
    $price_signal_score >= 3.0 ||
    $quote_opens_count >= 2 ||
    $quote_closes_count >= 1 ||
    $quote_visible_ms_max >= 12000 ||
    $event_multi_visitor_price_flag ||
    $event_uniq_visitors >= 2
  );

  $is_multi_persona = ($last_ts >= $now - (u('multip_recent_hours') * 3600)) &&
                      (
                        ($event_uniq_visitors >= 2 && ($event_multi_visitor_price_flag || $event_uniq_sessions >= 2)) ||
                        ($ips_post_first_guest_180m >= u('multip_min_ips_post_guest')) ||
                        (
                          $ips_post_first_guest_180m >= max(2, (u('multip_min_ips_post_guest') - 1)) &&
                          $multi_validation_boost_ok
                        )
                      ) &&
                      ($guest_sessions >= u('multip_min_guest_total')) &&
                      !$accepted;

  $deep_read_ok = (
    $quote_visible_ms_max >= u('deep_min_vis_max') ||
    $quote_visible_ms_sum >= u('deep_min_vis_sum')
  );

  $deep_price_focus_ok = (
    $has_totals_view ||
    $has_totals_revisit ||
    $has_price_loop ||
    $price_signal_score >= 2.5 ||
    $event_same_visitor_price_focus_flag
  );

  // Fix: deep_read_ok OR deep_price_focus_ok (antes era AND, demasiado restrictivo)
  $is_deep_review = (
    $views48 >= u('deep_min_views48') &&
    $span48 >= (u('deep_min_span_h') * 3600) &&
    $last_ts >= $now - (u('deep_recent_hours') * 3600) &&
    $guest_48h >= u('deep_min_guest_48h') &&
    ($deep_read_ok || $deep_price_focus_ok) &&
    !$accepted
  );

  $hes_price_friction_ok = (
    $has_totals_view ||
    $has_totals_revisit ||
    $has_price_loop ||
    $quote_coupon_clicks > 0 ||
    $price_signal_score >= 2.0 ||
    $event_same_visitor_price_focus_flag
  );

  $is_hesitation = (
    $guest_7d >= u('hes_min_guest_7d') &&
    $last_between_24h_7d &&
    $uniq_ips_total <= u('hes_max_ips_total') &&
    $span48 < (u('hes_max_span_h') * 3600) &&
    $hes_price_friction_ok &&
    !$accepted
  );

  $overanalysis_price_friction = (
    $has_totals_revisit ||
    $has_price_loop ||
    $quote_coupon_clicks > 0 ||
    $price_signal_score >= 3.0 ||
    $event_same_visitor_price_focus_flag
  );

  $over_soft_fit_ok = (
    ($fit_pct < (float)u('over_max_fit_pct')) ||
    (
      $event_same_visitor_price_focus_flag &&
      !$event_multi_visitor_flag &&
      $event_main_visitor_events >= 6
    )
  );

  $is_over_analysis = (
      ($guest_sessions >= u('over_min_guest')) &&
      ($sessions >= u('over_min_sessions'))
    ) &&
    ($age_days >= u('over_min_age_days')) &&
    ($last_ts >= $now - (u('over_recent_days') * 86400)) &&
    ($ips_post_first_guest_180m <= u('over_max_ips_post_guest')) &&
    $over_soft_fit_ok &&
    !$accepted;

  /** =========================
   *  FASE 3: Re-enganche caliente
   *  Variante del re-enganche: regresó + interacción con precio
   *  ========================= */
  $is_reengage_hot = (
    $is_reengage_decisive &&
    (
      $has_price_loop ||
      $has_totals_revisit ||
      $event_same_visitor_price_focus_flag ||
      $event_multi_visitor_price_flag ||
      $price_signal_score >= 3.0
    )
  );

  /** =========================
   *  FASE 3: Probable cierre (meta-bucket)
   *  Combina señales de múltiples categorías
   *  Requiere ≥2 categorías con ≥1 fuerte
   *  ========================= */

  // Fuentes calificantes: solo buckets de alta intención
  $pc_qualifying_source = (
    $is_onfire || $is_imminent || $is_price_validating ||
    $is_decision || $is_reengage_hot || $is_predict_high ||
    $is_multi_persona || $is_high_amount
  );

  // Categorías de señales
  $pc_cat_engagement = (
    ($quote_max_scroll_any >= 50 || $quote_max_scroll_close >= 50) &&
    ($quote_visible_ms_max >= 5000 || $quote_visible_ms_sum >= 10000) &&
    $has_totals_view
  );

  $pc_cat_precio = (
    $has_price_loop ||
    $has_totals_revisit ||
    $quote_coupon_clicks > 0 ||
    $event_same_visitor_price_focus_flag ||
    $price_signal_score >= 3.0
  );

  $pc_cat_persistencia = (
    $sessions >= 2 ||
    ($gap_days !== null && $gap_days >= 1)
  );

  $pc_cat_social = (
    $event_multi_visitor_flag ||
    $event_multi_visitor_price_flag ||
    $multi_ips >= 2
  );

  // Contar categorías y fuertes
  $pc_cats = 0;
  $pc_strong = 0;
  if ($pc_cat_engagement)   { $pc_cats++; $pc_strong++; }
  if ($pc_cat_precio)       { $pc_cats++; $pc_strong++; }
  if ($pc_cat_persistencia) { $pc_cats++; }
  if ($pc_cat_social)       { $pc_cats++; }

  $is_probable_cierre = (
    !$accepted &&
    $pc_qualifying_source &&
    $sessions >= 2 &&
    $last_ts >= $now - (72 * 3600) &&
    $pc_cats >= 2 &&
    $pc_strong >= 1
  );

  // Fuente del probable cierre (para debug/display)
  $pc_source = '';
  if ($is_probable_cierre) {
    if ($is_onfire)           $pc_source = 'onfire';
    elseif ($is_imminent)     $pc_source = 'inminente';
    elseif ($is_price_validating) $pc_source = 'validando_precio';
    elseif ($is_decision)     $pc_source = 'decision_activa';
    elseif ($is_reengage_hot) $pc_source = 're_enganche_caliente';
    elseif ($is_predict_high) $pc_source = 'prediccion_alta';
    elseif ($is_multi_persona) $pc_source = 'multi_persona';
    elseif ($is_high_amount)  $pc_source = 'alto_importe';
  }

  /** =========================
   *  FASE 3: Momentum (frescura por bucket)
   *  stable = dentro de vigencia, cooling = excede vigencia
   *  No modifica score ni bucket — puramente informativo
   *  ========================= */
  $momentum = 'none';
  if ($last_ts > 0 && !$accepted) {
    $elapsed_h = ($now - $last_ts) / 3600.0;

    // Determinar bucket principal para calcular vigencia
    $bucket_principal = null;
    if ($is_probable_cierre)      $bucket_principal = 'probable_cierre';
    elseif ($is_onfire)           $bucket_principal = 'onfire';
    elseif ($is_imminent)         $bucket_principal = 'inminente';
    elseif ($is_price_validating) $bucket_principal = 'validando_precio';
    elseif ($is_decision)         $bucket_principal = 'decision_activa';
    elseif ($is_reengage_hot)     $bucket_principal = 're_enganche_caliente';
    elseif ($is_reengage_decisive) $bucket_principal = 're_enganche';
    elseif ($is_multi_persona)    $bucket_principal = 'multi_persona';
    elseif ($is_deep_review)      $bucket_principal = 'revision_profunda';
    elseif ($is_hot_close)        $bucket_principal = 'probable_cierre_base';
    elseif ($is_return4d)         $bucket_principal = 'regreso';
    elseif ($is_revive)           $bucket_principal = 'revivio';
    elseif ($is_compare)          $bucket_principal = 'comparando';
    elseif ($is_active48)         $bucket_principal = 'activo48';

    // Mapa de bucket → clave de ventana temporal en U[]
    $bucket_window_map = [
      'probable_cierre'       => 72,  // fijo 72h
      'onfire'                => u('onfire_recent_hours'),
      'inminente'             => u('imminent_recent_hours'),
      'validando_precio'      => u('priceval_recent_hours'),
      'decision_activa'       => u('decision_window_h'),
      're_enganche_caliente'  => u('reeng_recent_hours'),
      're_enganche'           => u('reeng_recent_hours'),
      'multi_persona'         => u('multip_recent_hours'),
      'revision_profunda'     => u('deep_recent_hours'),
      'probable_cierre_base'  => u('hot_close_last_hours'),
      'regreso'               => u('return_recent_hours'),
      'revivio'               => u('revive_recent_hours'),
      'comparando'            => u('compare_window_h'),
      'activo48'              => 48,
    ];

    if ($bucket_principal !== null && isset($bucket_window_map[$bucket_principal])) {
      $window_h = (float)$bucket_window_map[$bucket_principal];
      $vigencia_h = $window_h / 2.0; // mitad de la ventana = vigencia estable
      $momentum = ($elapsed_h <= $vigencia_h) ? 'stable' : 'cooling';
    }
  }

  /** =========================
   *  FASE 3: Señales legibles
   *  Array de explicaciones humanas del score
   *  ========================= */
  $senales = [];

  // Sesiones
  if ($sessions >= 1) {
    $senales[] = ['pts' => min(5, $sessions) * 8, 'desc' => $sessions . ' visitas únicas'];
  }

  // Visitors
  if ($event_uniq_visitors >= 2) {
    $senales[] = ['pts' => 10, 'desc' => $event_uniq_visitors . ' personas distintas vieron la cotización'];
  }

  // Precio
  if ($has_price_loop) {
    $senales[] = ['pts' => 10, 'desc' => 'Revisó precio varias veces (loop)'];
  } elseif ($has_totals_revisit) {
    $senales[] = ['pts' => 8, 'desc' => 'Regresó a ver totales'];
  } elseif ($has_totals_view) {
    $senales[] = ['pts' => 4, 'desc' => 'Vio sección de totales'];
  }

  // Cupón
  if ($quote_coupon_clicks > 0) {
    $senales[] = ['pts' => 10, 'desc' => 'Intentó aplicar cupón'];
  }

  // Scroll profundo
  if ($quote_max_scroll_any >= 90) {
    $senales[] = ['pts' => 6, 'desc' => 'Leyó hasta el final (scroll ' . $quote_max_scroll_any . '%)'];
  } elseif ($quote_max_scroll_any >= 50) {
    $senales[] = ['pts' => 3, 'desc' => 'Leyó más de la mitad (scroll ' . $quote_max_scroll_any . '%)'];
  }

  // Tiempo de lectura
  $vis_sec = (int)round($quote_visible_ms_max / 1000);
  if ($vis_sec >= 60) {
    $vis_min = (int)round($vis_sec / 60);
    $senales[] = ['pts' => min(8, $vis_min * 2), 'desc' => 'Dedicó ~' . $vis_min . ' min leyendo'];
  } elseif ($vis_sec >= 15) {
    $senales[] = ['pts' => 2, 'desc' => 'Dedicó ~' . $vis_sec . 's leyendo'];
  }

  // Gap / regreso
  if ($gap_days !== null && $gap_days >= 1) {
    $senales[] = ['pts' => min(12, $gap_days * 2), 'desc' => 'Regresó tras ' . $gap_days . ' días'];
  }

  // Multi-persona
  if ($event_multi_visitor_price_flag) {
    $senales[] = ['pts' => 10, 'desc' => 'Varias personas revisaron el precio'];
  } elseif ($event_multi_visitor_flag) {
    $senales[] = ['pts' => 6, 'desc' => 'Compartida con otras personas'];
  }

  // Mismo visitor insistente
  if ($event_same_visitor_price_focus_flag) {
    $senales[] = ['pts' => 8, 'desc' => 'Misma persona insistió en el precio'];
  }

  // FIT alto
  if ($fit_pct >= 14.0) {
    $senales[] = ['pts' => 0, 'desc' => 'FIT ' . round($fit_pct, 1) . '% — patrón de cierre alto'];
  } elseif ($fit_pct >= 8.5) {
    $senales[] = ['pts' => 0, 'desc' => 'FIT ' . round($fit_pct, 1) . '% — patrón moderado'];
  }

  // Promo
  if ($has_promo_timer) {
    $senales[] = ['pts' => 2, 'desc' => 'Vio temporizador de promoción'];
  }

  // Probable cierre
  if ($is_probable_cierre) {
    $senales[] = ['pts' => 15, 'desc' => 'Probable cierre — ' . str_replace('_', ' ', $pc_source)];
  }

  /** =========================
   *  DEBUG FUNNELS
   *  ========================= */
  $dbg_over['c0']++;
  if ($guest_sessions >= u('over_min_guest')) {
    $dbg_over['c1']++;
    if ($sessions >= u('over_min_sessions')) {
      $dbg_over['c2']++;
      if ($age_days >= u('over_min_age_days')) {
        $dbg_over['c3']++;
        if ($last_ts >= $now - (u('over_recent_days') * 86400)) {
          $dbg_over['c4']++;
          if ($ips_post_first_guest_180m <= u('over_max_ips_post_guest')) {
            $dbg_over['c5']++;
            if ($over_soft_fit_ok && !$accepted) {
              $dbg_over['c6']++;
              if ($is_over_analysis) $dbg_over['final']++;
            }
          }
        }
      }
    }
  }

  $dbg_reeng['c0']++;
  if ($gap_days !== null && $gap_days >= u('reeng_gap_days')) {
    $dbg_reeng['c1']++;
    if ($last_ts >= $now - (u('reeng_recent_hours') * 3600)) {
      $dbg_reeng['c2']++;
      if (!empty($reeng_recent_interest_ok)) {
        $dbg_reeng['c3']++;
        if (!$accepted) {
          $dbg_reeng['c4']++;
          if ($is_reengage_decisive) $dbg_reeng['final']++;
        }
      }
    }
  }

  $dbg_multi['c0']++;
  if ($last_ts >= $now - (u('multip_recent_hours') * 3600)) {
    $dbg_multi['c1']++;
    if (($ips_post_first_guest_180m >= u('multip_min_ips_post_guest')) || $event_uniq_visitors >= 2) {
      $dbg_multi['c2']++;
      if ($guest_sessions >= u('multip_min_guest_total') && !$accepted) {
        $dbg_multi['c3']++;
        if ($is_multi_persona) $dbg_multi['final']++;
      }
    }
  }

  $dbg_deep['c0']++;
  if ($views48 >= u('deep_min_views48')) {
    $dbg_deep['c1']++;
    if ($span48 >= (u('deep_min_span_h') * 3600)) {
      $dbg_deep['c2']++;
      if (
        $last_ts >= $now - (u('deep_recent_hours') * 3600) &&
        $guest_48h >= u('deep_min_guest_48h') &&
        !$accepted &&
        (!empty($deep_read_ok) || !empty($deep_price_focus_ok))
      ) {
        $dbg_deep['c3']++;
        if ($is_deep_review) $dbg_deep['final']++;
      }
    }
  }

  $row = [
    'quote_id'      => (int)$id,
    'title'         => get_the_title($id),
    'amount_raw'    => $amount_raw,
    'amount_num'    => $amount_num,
    'amount_fmt'    => $amount_fmt,
    'sessions'      => (int)$sessions,
    'last_ts'       => (int)$last_ts,
    'last'          => $last_ts ? date_i18n('Y-m-d H:i', $last_ts) : '-',
    'created_ts'    => (int)$created_ts,
    'created'       => date_i18n('Y-m-d', strtotime($post->post_date)),
    'link'          => get_permalink($id),
    'gap_days'      => $gap_days,
    'accepted'      => $accepted ? 1 : 0,
    'fit_prob'      => $fit_prob,
    'data_source'   => $data_source,  // 'events' o 'log'
    'fit_pct'       => $fit_pct,
    'is_decided_buyer' => $is_decided_buyer,
    'priority_pct'  => $priority_pct,
    'reason'        => $is_probable_cierre
      ? apc_bucket_emoji($pc_source) . ' ' . str_replace('_', ' ', $pc_source)
      : ($is_hot_close ? hot_reason_priority($is_imminent, $is_decision, $is_revive, $is_multi, $is_return4d, $is_price_validating) : ''),

    'event_uniq_visitors'                => $event_uniq_visitors,
    'event_uniq_sessions'                => $event_uniq_sessions,
    'event_uniq_pages'                   => $event_uniq_pages,
    'event_main_visitor_events'          => $event_main_visitor_events,
    'event_main_visitor_price_events'    => $event_main_visitor_price_events,
    'event_main_visitor'                 => $event_main_visitor,
    'event_same_visitor_multi_session_flag' => $event_same_visitor_multi_session_flag,
    'event_same_visitor_multi_page_flag'    => $event_same_visitor_multi_page_flag,
    'event_same_visitor_price_focus_flag'   => $event_same_visitor_price_focus_flag,
    'event_multi_visitor_flag'              => $event_multi_visitor_flag,
    'event_multi_visitor_price_flag'        => $event_multi_visitor_price_flag,

    'onfire_scroll_close' => (int)$quote_max_scroll_close,
    'onfire_scroll_any'   => (int)$quote_max_scroll_any,
    'onfire_visible_sum'  => (int)$quote_visible_ms_sum,
    'onfire_visible_max'  => (int)$quote_visible_ms_max,
    'onfire_flag'         => $is_onfire ? 1 : 0,

    'imminent_signals_total'        => (int)$imminent_signals_total,
    'imminent_signals_strong'       => (int)$imminent_signals_strong,
    'imminent_coupon_clicks'        => (int)$quote_coupon_clicks,
    'has_coupon_icon'               => ($quote_coupon_clicks > 0) ? 1 : 0,
    'has_promo_icon'                => $has_promo_timer ? 1 : 0,
    'has_price_icon'                => ($price_signal_score >= 3.0) ? 1 : 0,

    'quote_totals_views_count'      => (int)$quote_totals_views_count,
    'quote_totals_revisits_count'   => (int)$quote_totals_revisits_count,
    'quote_price_loops_count'       => (int)$quote_price_loops_count,
    'quote_promo_present_count'     => (int)$quote_promo_present_count,

    'has_totals_view'               => $has_totals_view ? 1 : 0,
    'has_totals_revisit'            => $has_totals_revisit ? 1 : 0,
    'has_price_loop'                => $has_price_loop ? 1 : 0,
    'has_promo_timer'               => $has_promo_timer ? 1 : 0,
    'price_signal_strong'           => $price_signal_strong ? 1 : 0,
    'price_signal_medium'           => $price_signal_medium ? 1 : 0,
    'price_signal_score'            => (float)$price_signal_score,
    'multi_validation_boost_ok'     => !empty($multi_validation_boost_ok) ? 1 : 0,
    'overanalysis_price_friction'   => !empty($overanalysis_price_friction) ? 1 : 0,

    'is_price_validating'           => $is_price_validating ? 1 : 0,
    'priceval_guest_ok'             => $priceval_guest_ok ? 1 : 0,
    'priceval_read_ok'              => $priceval_read_ok ? 1 : 0,
    'priceval_loop_ok'              => $priceval_loop_ok ? 1 : 0,
    'priceval_revisit_ok'           => $priceval_revisit_ok ? 1 : 0,
    'priceval_view_repeat_ok'       => $priceval_view_repeat_ok ? 1 : 0,
    'priceval_same_visitor_ok'      => $priceval_same_visitor_ok ? 1 : 0,
    'priceval_multi_visitor_ok'     => $priceval_multi_visitor_ok ? 1 : 0,

    'guest_sessions'                => (int)$guest_sessions,
    'age_hours'                     => (float)$age_hours,

    'imminent_signal_scroll'        => $imminent_signal_scroll ? 1 : 0,
    'imminent_signal_visible'       => $imminent_signal_visible ? 1 : 0,
    'imminent_signal_review48'      => $imminent_signal_review48 ? 1 : 0,
    'imminent_signal_views'         => $imminent_signal_views ? 1 : 0,
    'imminent_signal_ips'           => $imminent_signal_ips ? 1 : 0,
    'imminent_signal_close'         => $imminent_signal_close ? 1 : 0,
    'imminent_signal_coupon'        => $imminent_signal_coupon ? 1 : 0,
    'imminent_signal_price_strong'  => $imminent_signal_price_strong ? 1 : 0,
    'imminent_signal_price_medium'  => $imminent_signal_price_medium ? 1 : 0,
    'imminent_signal_promo_boost'   => $imminent_signal_promo_boost ? 1 : 0,
    'imminent_signal_same_visitor'  => $imminent_signal_same_visitor ? 1 : 0,
    'imminent_signal_multi_visitor_price' => $imminent_signal_multi_visitor_price ? 1 : 0,
    'imminent_with_promo_boost'     => $imminent_with_promo_boost ? 1 : 0,
    'is_imminent'                   => $is_imminent ? 1 : 0,

    'onfire_scroll_ok'              => $onfire_scroll_ok ? 1 : 0,
    'onfire_visible_ok'             => $onfire_visible_ok ? 1 : 0,
    'onfire_return_ok'              => $onfire_return_ok ? 1 : 0,
    'onfire_price_ok'               => $onfire_price_ok ? 1 : 0,
    'onfire_visitor_ok'             => $onfire_visitor_ok ? 1 : 0,
    'is_onfire'                     => $is_onfire ? 1 : 0,

    'deep_read_ok'                  => !empty($deep_read_ok) ? 1 : 0,
    'deep_price_focus_ok'           => !empty($deep_price_focus_ok) ? 1 : 0,
    'hes_price_friction_ok'         => !empty($hes_price_friction_ok) ? 1 : 0,
    'cooling_price_touched'         => !empty($cooling_price_touched) ? 1 : 0,
    'cooling_reason'                => $cooling_reason ?? '',

    'is_not_opened'                 => $is_not_opened ? 1 : 0,
    'not_opened_age_ok'             => $not_opened_age_ok ? 1 : 0,
    'not_opened_has_external_views' => $not_opened_has_external_views ? 1 : 0,
    'not_opened_has_js_open'        => $not_opened_has_js_open ? 1 : 0,

    // Fase 3
    'is_probable_cierre'            => $is_probable_cierre ? 1 : 0,
    'pc_source'                     => $pc_source,
    'pc_cats'                       => (int)$pc_cats,
    'pc_strong'                     => (int)$pc_strong,
    'is_reengage_hot'               => $is_reengage_hot ? 1 : 0,
    'momentum'                      => $momentum,
    'senales'                       => $senales,
  ];

  // ── Fase 4: Determinar bucket principal y loggear transición ──
  $current_bucket = null;
  if ($is_probable_cierre)      $current_bucket = 'probable_cierre';
  elseif ($is_onfire)           $current_bucket = 'onfire';
  elseif ($is_imminent)         $current_bucket = 'inminente';
  elseif ($is_price_validating) $current_bucket = 'validando_precio';
  elseif ($is_reengage_hot)     $current_bucket = 're_enganche_caliente';
  elseif ($is_predict_high)     $current_bucket = 'prediccion_alta';
  elseif ($is_decision)         $current_bucket = 'decision_activa';
  elseif ($is_reengage_decisive) $current_bucket = 're_enganche';
  elseif ($is_multi_persona)    $current_bucket = 'multi_persona';
  elseif ($is_deep_review)      $current_bucket = 'revision_profunda';
  elseif ($is_high_amount)      $current_bucket = 'alto_importe';
  elseif ($is_hot_close)        $current_bucket = 'probable_cierre_base';
  elseif ($is_hesitation)       $current_bucket = 'hesitacion';
  elseif ($is_over_analysis)    $current_bucket = 'sobre_analisis';
  elseif ($is_revive)           $current_bucket = 'revivio';
  elseif ($is_return4d)         $current_bucket = 'regreso';
  elseif ($is_compare)          $current_bucket = 'comparando';
  elseif ($is_cooling)          $current_bucket = 'enfriandose';
  elseif ($is_not_opened)       $current_bucket = 'no_abierta';
  elseif ($is_active48)         $current_bucket = 'activo48';

  $row['bucket'] = $current_bucket;

  // Log transición si cambió de bucket
  if ($transitions_table_exists && $current_bucket !== null) {
    $old_bucket = $prev_buckets[(int)$id] ?? null;

    if ($old_bucket !== null && $old_bucket !== $current_bucket) {
      $wpdb->insert($transitions_table, [
        'quote_id'        => (int)$id,
        'bucket_anterior' => $old_bucket,
        'bucket_nuevo'    => $current_bucket,
        'score_anterior'  => null,
        'score_nuevo'     => round($priority_pct, 2),
        'fit_anterior'    => null,
        'fit_nuevo'       => round($fit_prob, 4),
        'created_at'      => current_time('mysql'),
        'created_ts'      => $now,
      ], ['%d','%s','%s','%s','%s','%s','%s','%s','%d']);
      $dbg_transitions_logged++;
    }
  }

  $rows[] = $row;

  $total_all++;
  if ($accepted) $total_sales++;

  // Excluir compradores decididos de validación por bandas —
  // no tienen patrón de engagement, contaminan la correlación
  if (!$is_decided_buyer) {
    $band = '12+';
    if ($fit_pct < 5) $band = '0-4.99';
    else if ($fit_pct < 8) $band = '5-7.99';
    else if ($fit_pct < 10) $band = '8-9.99';
    else if ($fit_pct < 12) $band = '10-11.99';

    $band_counts[$band]['total']++;
    if ($accepted) $band_counts[$band]['sales']++;
  }

  if ($is_active48)          $bucket_active48[] = $row;
  if ($is_hot_close)         $bucket_hot_close[] = $row;
  if ($is_imminent)          $bucket_imminent[] = $row;
  if ($is_predict_high)      $bucket_predict_high[] = $row;
  if ($is_high_amount)       $bucket_high_amount[] = $row;
  if ($is_multi)             $bucket_multi[] = $row;
  if ($is_decision)          $bucket_decision[] = $row;
  if ($is_onfire)            $bucket_onfire[] = $row;
  if ($is_price_validating)  $bucket_price_validating[] = $row;
  if ($is_not_opened)        $bucket_not_opened[] = $row;

  if ($is_reengage_decisive) $bucket_reengage_decisive[] = $row;
  if ($is_reengage_hot)      $bucket_reengage_hot[]      = $row;
  if ($is_multi_persona)     $bucket_multi_persona[]     = $row;
  if ($is_deep_review)       $bucket_deep_review[]       = $row;
  if ($is_hesitation)        $bucket_hesitation[]        = $row;
  if ($is_over_analysis)     $bucket_over_analysis[]     = $row;
  if ($is_probable_cierre)   $bucket_probable_cierre[]   = $row;

  if ($is_revive) {
    $bucket_revive_old[] = $row;
  } elseif ($is_return4d) {
    $bucket_return4d[] = $row;
  } elseif ($is_compare) {
    $bucket_compare[] = $row;
  } elseif ($is_cooling) {
    $bucket_cooling[] = $row;
  }
}

$sorter = function($a, $b) use ($sort, $dir){
  $r = sort_cmp($a, $b, $sort, $dir);
  if ($r !== 0) return $r;
  $t = ($b['priority_pct'] <=> $a['priority_pct']);
  if ($t !== 0) return $t;
  $u = ($b['fit_prob'] <=> $a['fit_prob']);
  if ($u !== 0) return $u;
  return ($b['last_ts'] <=> $a['last_ts']);
};

usort($bucket_hot_close,         $sorter);
usort($bucket_imminent,          $sorter);
usort($bucket_predict_high,      $sorter);
usort($bucket_high_amount,       $sorter);
usort($bucket_decision,          $sorter);
usort($bucket_multi,             $sorter);
usort($bucket_compare,           $sorter);
usort($bucket_revive_old,        $sorter);
usort($bucket_return4d,          $sorter);
usort($bucket_active48,          $sorter);
usort($bucket_cooling,           $sorter);
usort($bucket_onfire,            $sorter);
usort($bucket_price_validating,  $sorter);
usort($bucket_not_opened,        $sorter);

usort($bucket_reengage_decisive, $sorter);
usort($bucket_reengage_hot,      $sorter);
usort($bucket_multi_persona,     $sorter);
usort($bucket_deep_review,       $sorter);
usort($bucket_hesitation,        $sorter);
usort($bucket_over_analysis,     $sorter);
usort($bucket_probable_cierre,   $sorter);

usort($rows, $sorter);
$rows = array_slice($rows, 0, $limit);

$close_global_pct = ($total_all > 0) ? (100.0 * $total_sales / $total_all) : 0.0;

// Persistir stats para el termómetro APC (ontimetermo.php)
// Cuenta quotes con vistas reales (sessions > 0, ya filtrados bots/internos)
$apc_quotes_con_vista = 0;
$apc_quotes_periodo = 0;
$apc_accepted_periodo = 0;
$periodo_start_ts = $now - (30 * 86400);
foreach ($rows as $r) {
    if ((int)($r['created_ts'] ?? 0) >= $periodo_start_ts) {
        $apc_quotes_periodo++;
        if ((int)($r['sessions'] ?? 0) > 0) $apc_quotes_con_vista++;
        if (!empty($r['accepted'])) $apc_accepted_periodo++;
    }
}
update_option('apc_radar_stats', [
    'total_quotes'       => $total_all,
    'total_sales'        => $total_sales,
    'close_rate'         => $close_global_pct,
    'ciclo_venta'        => $ciclo_venta,
    'p80_alto_importe'   => $p80_alto_importe,
    'quotes_periodo'     => $apc_quotes_periodo,
    'quotes_con_vista'   => $apc_quotes_con_vista,
    'accepted_periodo'   => $apc_accepted_periodo,
    'updated_at'         => $now,
], false);

/** =========================
 *  FASE 4: Retorno estructurado (JSON API)
 *  ?format=json devuelve datos puros sin HTML
 *  Desacopla scoring de rendering — permite mobile, integraciones, etc.
 *  ========================= */
if (isset($_GET['format']) && $_GET['format'] === 'json' && (current_user_can('manage_options') || $login === 'ontime')) {
  // Limpiar senales para serialización (ya son arrays simples)
  $clean_row = function($r) {
    // Remover campos internos pesados que no se necesitan en API
    unset($r['amount_raw']);
    return $r;
  };

  $json_response = [
    'meta' => [
      'modo'           => $radar_modo,
      'config'         => $radar_config,
      'fit_source'     => $dbg_fit_source,
      'global_rate'    => round($GLOBAL_CLOSE_RATE, 4),
      'p80'            => $p80_alto_importe,
      'ciclo_venta'    => $ciclo_venta,
      'total_quotes'   => $total_all,
      'total_sales'    => $total_sales,
      'close_pct'      => round($close_global_pct, 2),
      'band_counts'    => $band_counts,
      'generated_at'   => current_time('mysql'),
      'transitions_logged' => $dbg_transitions_logged,
    ],
    'buckets' => [
      'probable_cierre'       => array_map($clean_row, $bucket_probable_cierre),
      'hot_close'             => array_map($clean_row, $bucket_hot_close),
      'inminente'             => array_map($clean_row, $bucket_imminent),
      'onfire'                => array_map($clean_row, $bucket_onfire),
      'validando_precio'      => array_map($clean_row, $bucket_price_validating),
      'no_abierta'            => array_map($clean_row, $bucket_not_opened),
      'prediccion_alta'       => array_map($clean_row, $bucket_predict_high),
      'alto_importe'          => array_map($clean_row, $bucket_high_amount),
      'decision_activa'       => array_map($clean_row, $bucket_decision),
      're_enganche'           => array_map($clean_row, $bucket_reengage_decisive),
      're_enganche_caliente'  => array_map($clean_row, $bucket_reengage_hot),
      'multi_persona'         => array_map($clean_row, $bucket_multi_persona),
      'revision_profunda'     => array_map($clean_row, $bucket_deep_review),
      'hesitacion'            => array_map($clean_row, $bucket_hesitation),
      'sobre_analisis'        => array_map($clean_row, $bucket_over_analysis),
      'vistas_multiples'      => array_map($clean_row, $bucket_multi),
      'comparando'            => array_map($clean_row, $bucket_compare),
      'revivio'               => array_map($clean_row, $bucket_revive_old),
      'regreso'               => array_map($clean_row, $bucket_return4d),
      'enfriandose'           => array_map($clean_row, $bucket_cooling),
      'activos48'             => array_map($clean_row, $bucket_active48),
    ],
    'ranking' => array_map($clean_row, $rows),
  ];

  header('Content-Type: application/json; charset=utf-8');
  echo wp_json_encode($json_response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  exit;
}

$thermo_user_id = (int)$current_user->ID;
$thermo_user_login = $login;

if (current_user_can('manage_options') && isset($_GET['thermo_user'])) {
  $candidate_user_id = (int)$_GET['thermo_user'];
  if ($candidate_user_id > 0) {
    $candidate_user = get_user_by('id', $candidate_user_id);
    if ($candidate_user && !empty($candidate_user->ID)) {
      $thermo_user_id = (int)$candidate_user->ID;
      $thermo_user_login = strtolower((string)$candidate_user->user_login);
    }
  }
}

$radar_usage_summary = [
  'score' => 0,
  'label' => 'Hay oportunidad',
  'valid_days' => 0,
  'target_days' => 1,
  'today_useful' => 0,
  'recent_useful' => 0,
  'quality_points' => 0,
  'last_activity_ts' => 0,
  'last_activity_human' => '-',
];

$show_radar_thermometer = false;
if (is_user_logged_in() && radar_usage_table_exists($radar_usage_table, $wpdb)) {
  $radar_usage_summary = radar_usage_get_continuous_summary($wpdb, $radar_usage_table, $thermo_user_id, 5);
}

if ($radar_usage_admin_only) {
  $show_radar_thermometer = ($login === 'admin');
} else {
  $show_radar_thermometer = is_user_logged_in();
}

if ($internal_ips_dirty) {
  internal_ips_save($internal_ips_file, $internal_ips);
  $dbg_internal_ips_count = count($internal_ips);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Radar de Cotizaciones</title>
<style>
  body{font-family:Arial; padding:20px;}
  table{border-collapse:collapse; width:100%; table-layout:fixed; margin-bottom:18px;}
  th,td{border:1px solid #ddd; padding:7px; font-size:13px; vertical-align:middle;}
  th{background:#f4f4f4;}
  th a{color:#000; text-decoration:none;}
  th a:hover{text-decoration:underline;}
  .num{text-align:right;}
  .center{text-align:center;}
  .titlewrap{display:flex; gap:10px; align-items:center; min-width:0;}
  .titletext{flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
  .amount{flex:0 0 auto; font-weight:bold; color:#111; background:#eef2ff; border:1px solid #dbe3ff; padding:2px 8px; border-radius:999px; font-size:12px;}
  .btns a{display:inline-block; padding:6px 10px; border:1px solid #ccc; margin-right:6px; text-decoration:none; border-radius:6px; color:#000; background:#fff;}
  .btns a:hover{background:#eee;}
  .btns a.active{text-decoration:underline; font-weight:bold; background:#fff; border-color:#bbb;}
  .hot4h{background:#fff7cc;}
  .hot30{background:#ffd9d9; font-weight:bold;}
  .section-title{margin:10px 0 8px; font-size:16px;}
  .hint{color:#666; font-size:12px; margin-top:-2px; margin-bottom:10px;}
  .badge{
    display:inline-block;
    padding:2px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:bold;
    border:1px solid #ddd;
    background:#f3f4f6;
    color:#111;
    white-space:nowrap;
  }
  .badge-ok{background:#dcfce7;border-color:#86efac;color:#166534;}
  .badge-no{background:#f3f4f6;border-color:#e5e7eb;color:#374151;}
  .mini{font-size:12px;color:#444;margin:6px 0 14px;}

  .radar-thermo-wrap{
    display:flex;
    justify-content:flex-start;
    margin:10px 0 18px;
  }
  .radar-thermo-card{
    width:100%;
    max-width:480px;
    border:1px solid #e5e7eb;
    background:#fff;
    border-radius:16px;
    padding:14px 16px;
    box-shadow:0 4px 14px rgba(0,0,0,.06);
  }
  .radar-thermo-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:14px;
  }
  .radar-thermo-title{
    font-size:15px;
    font-weight:700;
    color:#111827;
    margin-bottom:4px;
  }
  .radar-thermo-sub{
    font-size:12px;
    color:#6b7280;
    line-height:1.35;
  }
  .radar-thermo-score{
    flex:0 0 auto;
    font-size:30px;
    font-weight:800;
    line-height:1;
    color:#111827;
  }
  .radar-thermo-bar{
    margin-top:12px;
    height:10px;
    background:#edf2f7;
    border-radius:999px;
    overflow:hidden;
  }
  .radar-thermo-fill{
    height:100%;
    background:linear-gradient(90deg,#f59e0b 0%, #22c55e 100%);
    border-radius:999px;
  }
  .radar-thermo-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
    margin-top:14px;
  }
  .radar-stat-pill{
    background:#f9fafb;
    border:1px solid #eef2f7;
    border-radius:12px;
    padding:10px 10px 9px;
  }
  .radar-stat-label{
    display:block;
    font-size:10px;
    color:#6b7280;
    margin-bottom:2px;
  }
  .radar-stat-value{
    display:block;
    font-size:12px;
    font-weight:700;
    color:#111827;
  }
  .radar-thermo-foot{
    margin-top:10px;
    font-size:11px;
    color:#6b7280;
  }

  .bucket-shell{
    border:1px solid #e5e7eb;
    border-radius:12px;
    margin:14px 0;
    overflow:hidden;
    background:#fff;
  }
  .bucket-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:12px 14px;
    background:#fafafa;
    cursor:pointer;
  }
  .bucket-head-left{
    min-width:0;
  }
  .bucket-head-title{
    font-size:15px;
    font-weight:700;
  }
  .bucket-head-meta{
    font-size:12px;
    color:#666;
    margin-top:3px;
  }
  .bucket-head-right{
    flex:0 0 auto;
    font-size:12px;
    font-weight:700;
    color:#111;
  }
  .bucket-body{
    padding:0 0 10px;
  }
  .bucket-collapsed-preview{
    padding:0 14px 10px;
  }
  .bucket-preview-item{
    font-size:12px;
    color:#333;
    padding:6px 0;
    border-top:1px dashed #eee;
  }
  .bucket-hidden{
    display:none;
  }

  @media (max-width: 900px){
    .radar-thermo-grid{
      grid-template-columns:repeat(2,minmax(0,1fr));
    }
  }

  @media (max-width: 640px){
    .radar-thermo-card{
      max-width:none;
    }
    .radar-thermo-grid{
      grid-template-columns:1fr;
    }
  }
</style>
</head>
<body>

<h2>Radar de Cotizaciones</h2>

<?php if (current_user_can('manage_options')): ?>
<div style="margin:0 0 10px;">
  <form method="get" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
    <?php foreach($_GET as $k=>$v){ if($k==='thermo_user') continue; ?>
      <input type="hidden" name="<?php echo esc_attr($k); ?>" value="<?php echo esc_attr($v); ?>">
    <?php } ?>

    <label for="thermo_user"><b>Ver termómetro de:</b></label>
    <select name="thermo_user" id="thermo_user">
      <option value="1" <?php selected((int)($thermo_user_id ?? 0), 1); ?>>admin</option>
      <option value="815" <?php selected((int)($thermo_user_id ?? 0), 815); ?>>ontime</option>
    </select>

    <button type="submit">Ver</button>
  </form>
</div>
<?php endif; ?>

<?php if ($show_radar_thermometer): ?>
<div class="radar-thermo-wrap">
  <div class="radar-thermo-card">
    <div class="radar-thermo-top">
      <div class="radar-thermo-head">
        <div>
          <div class="radar-thermo-title">
            Termómetro de uso continuo<?php echo current_user_can('manage_options') ? ' · '.esc_html($thermo_user_login).' (#'.(int)$thermo_user_id.')' : ''; ?>
          </div>
          <div class="radar-thermo-sub">
            Constancia semanal: <?php echo (int)$radar_usage_summary['valid_days']; ?>/<?php echo (int)$radar_usage_summary['target_days']; ?> días hábiles útiles · <?php echo esc_html($radar_usage_summary['label']); ?>
          </div>
        </div>
        <div class="radar-thermo-score"><?php echo (int)$radar_usage_summary['score']; ?>%</div>
      </div>

      <div class="radar-thermo-bar">
        <div class="radar-thermo-fill" style="width:<?php echo (int)$radar_usage_summary['score']; ?>%;"></div>
      </div>
    </div>

    <div class="radar-thermo-grid">
      <div class="radar-stat-pill">
        <span class="radar-stat-label">Días útiles</span>
        <span class="radar-stat-value"><?php echo (int)$radar_usage_summary['valid_days']; ?>/<?php echo (int)$radar_usage_summary['target_days']; ?></span>
      </div>

      <div class="radar-stat-pill">
        <span class="radar-stat-label">Hoy activo</span>
        <span class="radar-stat-value"><?php echo !empty($radar_usage_summary['today_useful']) ? 'Sí' : 'No'; ?></span>
      </div>

      <div class="radar-stat-pill">
        <span class="radar-stat-label">Uso reciente</span>
        <span class="radar-stat-value"><?php echo !empty($radar_usage_summary['recent_useful']) ? 'Sí' : 'No'; ?></span>
      </div>

      <div class="radar-stat-pill">
        <span class="radar-stat-label">Calidad</span>
        <span class="radar-stat-value"><?php echo (int)$radar_usage_summary['quality_points']; ?>/15</span>
      </div>
    </div>

    <div class="radar-thermo-foot">
      Última actividad: <?php echo esc_html($radar_usage_summary['last_activity_human']); ?>
      &nbsp;·&nbsp;
      <a href="ontimetermo.php" style="color:#6d28d9; font-weight:600; text-decoration:none;">📊 Ver Termómetro APC completo →</a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($debug_mode): ?>
  <div class="mini" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin:10px 0;">
    <b>DEBUG bots</b><br>
    total eventos quote_viewed revisados: <?php echo (int)$dbg_total_view_events; ?><br>
    filtrados por bot_ip_prefixes: <?php echo (int)$dbg_bot_skipped; ?> |
    filtrados por UA bot: <?php echo (int)$dbg_bot_ua_skipped; ?> |
    ghost sessions filtradas: <?php echo (int)$dbg_ghost_filtered; ?>

    <br><br><b>DEBUG events-first</b><br>
    quotes con JS events: <?php echo (int)count(array_filter($rows, fn($r) => ($r['data_source'] ?? '') === 'events')); ?> |
    quotes con log fallback: <?php echo (int)count(array_filter($rows, fn($r) => ($r['data_source'] ?? '') === 'log')); ?>

    <br><br><b>DEBUG Fase 2: Scoring</b><br>
    Modo: <b><?php echo esc_html($radar_modo); ?></b> |
    FIT source: <?php echo esc_html($dbg_fit_source); ?> |
    FIT ventas calibradas: <?php echo (int)$dbg_fit_sales; ?> |
    Global rate: <?php echo number_format($GLOBAL_CLOSE_RATE * 100, 2); ?>%<br>
    P80 alto importe: $<?php echo number_format($dbg_p80, 0); ?> |
    Ciclo venta: <?php echo (int)$dbg_ciclo['dias']; ?>d (P25=<?php echo (int)$dbg_ciclo['p25']; ?>d, P75=<?php echo (int)$dbg_ciclo['p75']; ?>d, n=<?php echo (int)$dbg_ciclo['n']; ?>)<br>
    FIT model: <b>power-scaled NB (s^0.5 × i^0.3 × g^0.2) + isotonic PAV</b><br>
    Sess rates (isotonic): <?php echo esc_html(implode(', ', array_map(fn($k,$v) => "$k=" . number_format($v*100,2) . "%", array_keys($RATE_SESS), $RATE_SESS))); ?><br>
    IPs rates (isotonic): <?php echo esc_html(implode(', ', array_map(fn($k,$v) => "$k=" . number_format($v*100,2) . "%", array_keys($RATE_IPS), $RATE_IPS))); ?><br>
    Gap rates (isotonic): <?php echo esc_html(implode(', ', array_map(fn($k,$v) => "$k=" . number_format($v*100,2) . "%", array_keys($RATE_GAP), $RATE_GAP))); ?>

    <br><br><b>DEBUG Fase 4: Infraestructura</b><br>
    Config: JSON en wp_options |
    Transitions tabla: <?php echo $transitions_table_exists ? 'OK' : 'NO'; ?> |
    Transiciones loggeadas: <?php echo (int)$dbg_transitions_logged; ?> |
    Previos cargados: <?php echo (int)count($prev_buckets); ?> |
    API JSON: <a href="<?php echo esc_url(url_q(['format'=>'json'])); ?>">?format=json</a>

    <br><br><b>DEBUG internal ips</b><br>
    internal_ips en cache: <?php echo (int)$dbg_internal_ips_count; ?> |
    learned hoy: <?php echo (int)$dbg_internal_learned; ?> |
    guest internos filtrados: <?php echo (int)$dbg_internal_guest_skipped; ?>

    <br><br><b>DEBUG visitor layer</b><br>
    internal_visitors en cache: <?php echo (int)count($internal_visitors); ?><br>
    lookback eventos JS: <?php echo (int)$events_js_lookback_days; ?> días<br>
    accepted bulk set: <?php echo (int)count($accepted_ids); ?>

    <br><br><b>DEBUG funnels (post-filtro)</b><br>
    Re-enganche: <?php echo (int)$dbg_reeng['c0']; ?> | c1 <?php echo (int)$dbg_reeng['c1']; ?> | c2 <?php echo (int)$dbg_reeng['c2']; ?> | c3 <?php echo (int)$dbg_reeng['c3']; ?> | c4 <?php echo (int)$dbg_reeng['c4']; ?> | final <?php echo (int)$dbg_reeng['final']; ?><br>
    Multi-persona: <?php echo (int)$dbg_multi['c0']; ?> | c1 <?php echo (int)$dbg_multi['c1']; ?> | c2 <?php echo (int)$dbg_multi['c2']; ?> | c3 <?php echo (int)$dbg_multi['c3']; ?> | final <?php echo (int)$dbg_multi['final']; ?><br>
    Profunda: <?php echo (int)$dbg_deep['c0']; ?> | c1 <?php echo (int)$dbg_deep['c1']; ?> | c2 <?php echo (int)$dbg_deep['c2']; ?> | c3 <?php echo (int)$dbg_deep['c3']; ?> | final <?php echo (int)$dbg_deep['final']; ?>

    <br><br><b>DEBUG funnel Sobre-análisis</b><br>
    SA: <?php echo (int)$dbg_over['c0']; ?> |
    c1 <?php echo (int)$dbg_over['c1']; ?> |
    c2 <?php echo (int)$dbg_over['c2']; ?> |
    c3 <?php echo (int)$dbg_over['c3']; ?> |
    c4 <?php echo (int)$dbg_over['c4']; ?> |
    c5 <?php echo (int)$dbg_over['c5']; ?> |
    c6 <?php echo (int)$dbg_over['c6']; ?> |
    final <?php echo (int)$dbg_over['final']; ?>
  </div>
<?php endif; ?>

<div class="mini">
  <?php
    $decided_total = count(array_filter($rows, fn($r) => !empty($r['is_decided_buyer'])));
    $decided_sales = count(array_filter($rows, fn($r) => !empty($r['is_decided_buyer']) && !empty($r['accepted'])));
    $band_total_sum = 0; $band_sales_sum = 0;
    foreach ($band_counts as $bc) { $band_total_sum += $bc['total']; $band_sales_sum += $bc['sales']; }
  ?>
  <b>Validación Score% vs Ventas (Accepted)</b> — Total: <?php echo (int)$total_all; ?> | Ventas: <?php echo (int)$total_sales; ?> | Cierre global: <?php echo number_format($close_global_pct, 2); ?>%<br>
  <b>Compradores decididos</b> (≤1 sesión, excluidos de bandas): <?php echo (int)$decided_total; ?> cotiz. | <?php echo (int)$decided_sales; ?> ventas<?php echo $decided_total > 0 ? ' | cierre: ' . number_format(100.0 * $decided_sales / $decided_total, 2) . '%' : ''; ?><br>
  <b>En bandas FIT</b> (≥2 sesiones): <?php echo (int)$band_total_sum; ?> cotiz. | <?php echo (int)$band_sales_sum; ?> ventas<?php echo $band_total_sum > 0 ? ' | cierre: ' . number_format(100.0 * $band_sales_sum / $band_total_sum, 2) . '%' : ''; ?><br>
  <b>Score%</b> = <b>FIT%</b> (probabilidad “fría” por patrón accepted: sesiones + IPs + gap, con caps). <b>Prioridad%</b> = FIT% + recencia + intención de precio/visitor.<br>
  <b>Visitor</b> se usa como capa de identidad operativa. Los eventos JS se leen con ventana de <?php echo (int)$events_js_lookback_days; ?> días.
</div>

<?php if(current_user_can('manage_options')): ?>
<table>
<thead>
<tr>
  <th style="width:15%;">Banda FIT%</th>
  <th style="width:15%;" class="center">Cotizaciones</th>
  <th style="width:20%;" class="center">Ventas (Accepted)</th>
  <th style="width:20%;" class="center">Tasa cierre</th>
</tr>
</thead>
<tbody>
<?php
foreach (['0-4.99','5-7.99','8-9.99','10-11.99','12+'] as $b) {
  $t = (int)$band_counts[$b]['total'];
  $s = (int)$band_counts[$b]['sales'];
  $rate = $t > 0 ? (100.0 * $s / $t) : 0.0;
  echo '<tr>';
  echo '<td><b>'.esc_html($b).'</b></td>';
  echo '<td class="center">'.(int)$t.'</td>';
  echo '<td class="center">'.(int)$s.'</td>';
  echo '<td class="center"><b>'.number_format($rate, 2).'%</b></td>';
  echo '</tr>';
}
?>
</tbody>
</table>
<?php endif; ?>

<?php // Fase 2: Selector de modo ?>
<div style="margin:10px 0; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
  <b>Sensibilidad:</b>
  <?php foreach (['agresivo'=>'Agresivo','medio'=>'Medio','ligero'=>'Ligero'] as $mk=>$ml): ?>
    <a href="<?php echo esc_url(url_q(['modo'=>$mk])); ?>"
       style="padding:4px 12px; border:1px solid <?php echo $radar_modo===$mk?'#1a5c38':'#ccc'; ?>; border-radius:6px; text-decoration:none; color:<?php echo $radar_modo===$mk?'#fff':'#333'; ?>; background:<?php echo $radar_modo===$mk?'#1a5c38':'#fff'; ?>; font-size:13px; font-weight:<?php echo $radar_modo===$mk?'bold':'normal'; ?>;">
      <?php echo esc_html($ml); ?>
    </a>
  <?php endforeach; ?>
  <span style="color:#666; font-size:12px; margin-left:8px;">
    FIT: <?php echo esc_html($dbg_fit_source); ?> (<?php echo number_format($GLOBAL_CLOSE_RATE*100,2); ?>%) |
    P80: $<?php echo number_format($p80_alto_importe,0); ?> |
    Ciclo: <?php echo (int)$ciclo_venta['dias']; ?>d
  </span>
</div>

<div class="btns" style="margin:10px 0;">
  <a href="<?php echo esc_url(url_q(['range'=>'all'])); ?>" class="<?php echo $range==='all'?'active':''; ?>">Todas</a>
  <a href="<?php echo esc_url(url_q(['range'=>'48h'])); ?>" class="<?php echo $range==='48h'?'active':''; ?>">48 horas</a>
  <a href="<?php echo esc_url(url_q(['range'=>'4h'])); ?>" class="<?php echo $range==='4h'?'active':''; ?>">4 horas</a>
  <a href="<?php echo esc_url(url_q(['range'=>'30m'])); ?>" class="<?php echo $range==='30m'?'active':''; ?>">30 minutos</a>
</div>

<form method="get" style="margin:10px 0;">
  <?php foreach($_GET as $k=>$v){ if($k==='limit') continue; ?>
    <input type="hidden" name="<?php echo esc_attr($k); ?>" value="<?php echo esc_attr($v); ?>">
  <?php } ?>
  Mostrar
  <input type="number" name="limit" value="<?php echo (int)$limit; ?>" style="width:70px" min="10" max="300">
  <button>Actualizar</button>
</form>

<?php
render_bucket_fixed(
  '🎯 Probable cierre (META)',
  'Combina señales de múltiples categorías: engagement + precio + persistencia + social. Requiere ≥2 categorías con ≥1 fuerte. Fuentes: onfire, inminente, validando precio, decisión, re-enganche caliente, predicción alta, multi-persona, alto importe.',
  $bucket_probable_cierre,
  false,
  $sort,
  $dir,
  true
);

render_bucket_fixed(
  '🔥 Probable cierre (PRIORIDAD)',
  'Ventana: últimas '.u('hot_close_last_hours').'h + momentum ('.u('hot_close_min_views24').'+ vistas/24h o '.u('hot_close_min_views7d').'+ vistas/7d). Modo: '.$radar_modo,
  $bucket_hot_close,
  false,
  $sort,
  $dir,
  true
);

render_bucket_fixed(
  '🔥 Cierre inminente',
  'Actividad en '.u('imminent_recent_hours').'h + FIT >= '.number_format((float)u('imminent_min_fit_pct'),2).' + edad>='.u('imminent_min_age_hours').'h + guest>='.u('imminent_min_guest').' + mínimo '.u('imminent_min_signals').' señales ('.u('imminent_min_strong').' fuerte). Modo: '.$radar_modo,
  $bucket_imminent,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🔥😱 ON FIRE (tutu)',
  'Señal premium: actividad en '.u('onfire_recent_hours').'h + '.u('onfire_min_sessions').'+ sesiones + scroll >= '.u('onfire_min_scroll_pct').' + lectura real + foco en precio + validación por visitor. Modo: '.$radar_modo,
  $bucket_onfire,
  true,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '💸 Validando precio',
  'Detecta foco real en precio: exige base guest + validación individual o compartida. Modo: '.$radar_modo,
  $bucket_price_validating,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '❌ No abierta',
  'Cotizaciones creadas en los últimos '.u('not_opened_max_age_days').' días sin evidencia técnica de apertura.',
  $bucket_not_opened,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🔮 Predicción alta (Accepted)',
  'FIT >= '.number_format((float)u('predict_min_fit_pct'),2).'% y ventana adaptativa: '.$predict_window.'d (ciclo venta: '.$ciclo_venta['dias'].'d, modo: '.$radar_modo.').',
  $bucket_predict_high,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '💰 Alto importe',
  'Importe >= $'.number_format($p80_alto_importe,0).' (P80 dinámico) y vista en últimas '.u('high_amount_recent_hours').'h. Modo: '.$radar_modo,
  $bucket_high_amount,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🧠 Decisión activa',
  'Señal: '.u('decision_min_views48').'+ vistas en '.u('decision_window_h').'h y regresos reales (span >= '.u('decision_min_span_h').'h). Modo: '.$radar_modo,
  $bucket_decision,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🟣 Re-enganche decisivo',
  'Gap >= '.u('reeng_gap_days').'d y last < '.u('reeng_recent_hours').'h + interés reciente. Modo: '.$radar_modo,
  $bucket_reengage_decisive,
  true,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🔥🟣 Re-enganche caliente',
  'Re-enganche + interacción de precio (loop, revisita totales, precio focus, o PSS >= 3.0). Modo: '.$radar_modo,
  $bucket_reengage_hot,
  true,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '👥 Revisión multi-persona',
  'Last < '.u('multip_recent_hours').'h + 2+ visitors o IPs post primer guest/'.u('multip_ip_window_min').'m + guest_total >= '.u('multip_min_guest_total').'. Modo: '.$radar_modo,
  $bucket_multi_persona,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🧾 Revisión profunda',
  'Lectura real (visible) y foco en precio/totales. Modo: '.$radar_modo,
  $bucket_deep_review,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🟠 Hesitación',
  'Pausa entre '.u('hes_last_min_hours').'h y '.u('hes_last_max_days').'d, con fricción real en precio/totales. Modo: '.$radar_modo,
  $bucket_hesitation,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🟤 Sobre-análisis',
  'guest >= '.u('over_min_guest').' y sesiones >= '.u('over_min_sessions').' y edad >= '.u('over_min_age_days').'d y last < '.u('over_recent_days').'d. Modo: '.$radar_modo,
  $bucket_over_analysis,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🟩 Vistas múltiples',
  'Señal: ('.u('multi_min_ips').'+ IPs en '.u('multi_recent_hours').'h) O ('.u('multi_min_views24').'+ vistas en 24h). Modo: '.$radar_modo,
  $bucket_multi,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🟠 Comparando / Compartiendo (señal exclusiva)',
  'Señal: '.u('compare_min_ips').'+ IPs distintas en '.u('compare_window_h').'h + engagement gate. Modo: '.$radar_modo,
  $bucket_compare,
  false,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '💜 Revivió cotización vieja (señal exclusiva)',
  'Volvió tras '.u('revive_gap_days').'+ días y última vista en '.u('revive_recent_hours').'h. Modo: '.$radar_modo,
  $bucket_revive_old,
  true,
  $sort,
  $dir,
  false
);

render_bucket_fixed(
  '🟣 Regreso después de +'.u('return_gap_days').' días (señal exclusiva)',
  'Volvió tras '.u('return_gap_days').'+ días y última vista en '.u('return_recent_hours').'h. Modo: '.$radar_modo,
  $bucket_return4d,
  true,
  $sort,
  $dir,
  false
);

foreach ($bucket_cooling as &$cool_row) {
  $cool_row['reason'] = $cool_row['cooling_reason'] ?? '';
}
unset($cool_row);

render_bucket_fixed(
  '🔵 Enfriándose (señal exclusiva)',
  'Tuvo '.u('cooling_min_sessions').'+ vistas + engagement real pero no se ha visto en '.u('cooling_days').'d. Modo: '.$radar_modo,
  $bucket_cooling,
  false,
  $sort,
  $dir,
  true
);

render_bucket_fixed(
  '🟡 Activos 48h (incluye todos los activos)',
  'Lista completa de todo lo visto en últimas '.$active_hours.' horas (aunque también esté en otros buckets).',
  $bucket_active48,
  false,
  $sort,
  $dir,
  false
);
?>

<div class="section-title">Ranking general (ordenable)</div>
<div class="hint">
  Orden recomendado: <b>Prioridad%</b> (operativo). Score% = FIT% (probabilidad). Visitor añade lectura de intención individual vs consenso.
</div>

<table>
<thead>
<tr>
  <th style="width:40%;">
    <a href="<?php echo esc_url(url_q(['sort'=>'title','dir'=>toggle_dir($sort,'title',$dir)])); ?>">Título</a> /
    <a href="<?php echo esc_url(url_q(['sort'=>'amount','dir'=>toggle_dir($sort,'amount',$dir)])); ?>">Importe</a>
  </th>
  <th style="width:8%;" class="center">Venta</th>
  <th style="width:8%;" class="center"><a href="<?php echo esc_url(url_q(['sort'=>'fit','dir'=>toggle_dir($sort,'fit',$dir)])); ?>">Score%</a></th>
  <th style="width:10%;" class="center"><a href="<?php echo esc_url(url_q(['sort'=>'priority','dir'=>toggle_dir($sort,'priority',$dir)])); ?>">Prioridad%</a></th>
  <th style="width:8%;" class="num"><a href="<?php echo esc_url(url_q(['sort'=>'sessions','dir'=>toggle_dir($sort,'sessions',$dir)])); ?>">Vistas</a></th>
  <th style="width:16%;"><a href="<?php echo esc_url(url_q(['sort'=>'last','dir'=>toggle_dir($sort,'last',$dir)])); ?>">Última vista</a></th>
  <th style="width:6%;"><a href="<?php echo esc_url(url_q(['sort'=>'date','dir'=>toggle_dir($sort,'date',$dir)])); ?>">Creada</a></th>
  <th style="width:4%;" class="center">Ver</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): $cls = row_class($r['last_ts']); ?>
<tr class="<?php echo esc_attr($cls); ?>">
  <td>
    <div class="titlewrap">
      <?php
      $title_icons = '';
      // Fase 3: momentum + meta-bucket icons
      $m = $r['momentum'] ?? 'none';
      if ($m === 'stable')  $title_icons .= '↑';
      elseif ($m === 'cooling') $title_icons .= '↓';
      if (!empty($r['is_probable_cierre'])) $title_icons .= '🎯';
      if (!empty($r['is_reengage_hot']))    $title_icons .= '🔥';
      if (!empty($r['has_coupon_icon'])) $title_icons .= '🎟️';
      if (!empty($r['has_promo_icon']))  $title_icons .= '💣';
      if (!empty($r['has_price_icon']))  $title_icons .= '💸';
      if (!empty($r['event_same_visitor_price_focus_flag'])) $title_icons .= '👤';
      if (!empty($r['event_multi_visitor_price_flag'])) $title_icons .= '👥';
      if (!empty($r['is_not_opened'])) $title_icons .= '❌';
      $title_show = trim($title_icons . ' ' . ($r['title'] ?? ''));

      // Señales tooltip
      $senales_text = '';
      if (!empty($r['senales']) && is_array($r['senales'])) {
        $parts = [];
        foreach ($r['senales'] as $s) { $parts[] = ($s['desc'] ?? ''); }
        $senales_text = implode(' | ', $parts);
      }
      ?>
      <div class="titletext" <?php echo $senales_text ? 'title="'.esc_attr($senales_text).'"' : ''; ?>><?php echo esc_html($title_show); ?></div>
      <div class="amount"><?php echo esc_html($r['amount_fmt']); ?></div>
    </div>
  </td>
  <td class="center">
    <?php if(!empty($r['accepted'])): ?>
      <span class="badge badge-ok">ACCEPTED</span>
    <?php else: ?>
      <span class="badge badge-no">no</span>
    <?php endif; ?>
  </td>
  <td class="center"><b><?php echo number_format((float)$r['fit_pct'], 2); ?>%</b></td>
  <td class="center"><b><?php echo number_format((float)$r['priority_pct'], 2); ?>%</b></td>
  <td class="num"><b><?php echo (int)$r['sessions']; ?></b></td>
  <td>
    <?php if (!empty($r['last_ts'])): ?>
      <?php echo esc_html($r['last']); ?> <span style="color:#666;">(hace <?php echo esc_html(hace($r['last_ts'])); ?>)</span>
    <?php else: ?>
      -
    <?php endif; ?>
  </td>
  <td><?php echo esc_html($r['created']); ?></td>
  <td class="center">
    <a href="<?php echo esc_url($r['link']); ?>" target="_blank">Abrir</a>
    <?php if (current_user_can('manage_options')): ?>
      <div style="margin-top:4px;font-size:11px;color:#666;line-height:1.25;">
        VU: <?php echo (int)$r['event_uniq_visitors']; ?> |
        VSs: <?php echo (int)$r['event_uniq_sessions']; ?> |
        VP: <?php echo (int)$r['event_main_visitor_price_events']; ?>
        <?php if (!empty($r['event_main_visitor'])): ?>
          <br>M: <?php echo esc_html(visitor_short($r['event_main_visitor'])); ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<script>
(function(){
  var ajaxUrl = window.location.href;
  var sessionKey = 'radar_usage_session_id';
  var pageKey = 'radar_usage_page_id';

  function uuidv4() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random() * 16 | 0;
      var v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }

  function getSessionId(){
    try {
      var v = sessionStorage.getItem(sessionKey);
      if (!v) {
        v = uuidv4();
        sessionStorage.setItem(sessionKey, v);
      }
      return v;
    } catch(e) {
      return uuidv4();
    }
  }

  function getPageId(){
    try {
      var v = sessionStorage.getItem(pageKey);
      if (!v) {
        v = uuidv4();
        sessionStorage.setItem(pageKey, v);
      }
      return v;
    } catch(e) {
      return uuidv4();
    }
  }

  var sessionId = getSessionId();
  var pageId = getPageId();
  var sentScrollMarks = {};
  var lastPingAt = 0;

  function postUsage(action, extra) {
    extra = extra || {};
    var payload = {
      radar_usage_action: action,
      session_id: sessionId,
      page_id: pageId
    };
    Object.keys(extra).forEach(function(k){ payload[k] = extra[k]; });

    if ((action === 'filter_change' || action === 'radar_refresh' || action === 'radar_ping' || action === 'radar_scroll') && navigator.sendBeacon) {
      try {
        var fd = new FormData();
        Object.keys(payload).forEach(function(k){
          fd.append(k, payload[k]);
        });
        navigator.sendBeacon(ajaxUrl, fd);
        return;
      } catch(e) {}
    }

    fetch(ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      keepalive: true,
      headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
      body: new URLSearchParams(payload).toString()
    }).catch(function(){});
  }

  function currentScrollPct() {
    var doc = document.documentElement;
    var body = document.body;
    var scrollTop = window.pageYOffset || doc.scrollTop || body.scrollTop || 0;
    var scrollHeight = Math.max(
      body.scrollHeight, doc.scrollHeight,
      body.offsetHeight, doc.offsetHeight,
      body.clientHeight, doc.clientHeight
    );
    var clientHeight = window.innerHeight || doc.clientHeight || 0;
    var maxScroll = Math.max(1, scrollHeight - clientHeight);
    return Math.round((scrollTop / maxScroll) * 100);
  }

  function trackScrollMarks() {
    var pct = currentScrollPct();
    [75, 90].forEach(function(mark){
      if (pct >= mark && !sentScrollMarks[mark]) {
        sentScrollMarks[mark] = true;
        postUsage('radar_scroll', { event_key: 'scroll_' + mark });
      }
    });
  }

  function heartbeat(force) {
    var now = Date.now();
    if (!force && (now - lastPingAt) < 30000) return;
    if (document.hidden) return;
    lastPingAt = now;
    postUsage('radar_ping', { event_key: 'visible_heartbeat' });
  }

  postUsage('radar_open');

  var usp = new URLSearchParams(location.search);
  postUsage('radar_refresh', {
    event_key: [
      usp.get('range') || 'all',
      usp.get('sort') || 'priority',
      usp.get('dir') || 'desc',
      usp.get('limit') || ''
    ].join('|')
  });

  document.querySelectorAll('.btns a, th a').forEach(function(a){
    a.addEventListener('click', function(){
      var href = a.getAttribute('href') || '';
      postUsage('filter_change', { event_key: href });
    });
  });

  var limitForm = document.querySelector('form[method="get"]');
  if (limitForm) {
    limitForm.addEventListener('submit', function(){
      postUsage('filter_change', { event_key: 'limit_submit' });
    });
  }

  window.addEventListener('scroll', function(){
    trackScrollMarks();
    heartbeat(false);
  }, { passive: true });

  window.addEventListener('mousemove', function(){
    heartbeat(false);
  }, { passive: true });

  window.addEventListener('keydown', function(){
    heartbeat(false);
  });

  window.addEventListener('focus', function(){
    heartbeat(true);
  });

  document.addEventListener('visibilitychange', function(){
    if (!document.hidden) {
      heartbeat(true);
    }
  });

  window.addEventListener('load', function(){
    trackScrollMarks();
    heartbeat(true);
  });

  setInterval(function(){
    heartbeat(false);
  }, 30000);
})();
</script>

</body>
</html>
