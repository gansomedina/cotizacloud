<?php
// ============================================================
//  ontime2.php — Radar v2.4 para On Time
//  Archivo principal: auth, fetch, 2 pasadas, UI
// ============================================================
require_once __DIR__ . '/wp-load.php';
require_once __DIR__ . '/ontime2_config.php';
require_once __DIR__ . '/ontime2_engine.php';

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

// ─── Modo de sensibilidad ───
$modo = isset($_GET['modo']) ? strtolower(sanitize_text_field($_GET['modo'])) : 'medio';
if (!in_array($modo, ['agresivo','medio','ligero'], true)) $modo = 'medio';

// ─── Parámetros de vista ───
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

// ─── TERMÓMETRO ───
$radar_usage_table = $wpdb->prefix . 'radar_usage_events';
$radar_usage_admin_only = true;

function radar_usage_table_exists($table_name, $wpdb){
  return ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name);
}

function radar_usage_client_ip_bin() {
  $ip_raw = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
  if ($ip_raw && filter_var($ip_raw, FILTER_VALIDATE_IP)) return @inet_pton($ip_raw);
  return null;
}

function radar_usage_client_ua() {
  return isset($_SERVER['HTTP_USER_AGENT'])
    ? substr(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])), 0, 255) : '';
}

function radar_usage_insert_event($wpdb, $table, $user_id, $user_login, $event_type, $event_key = '', $quote_id = 0, $session_id = '', $page_id = '', $meta = []) {
  if (!radar_usage_table_exists($table, $wpdb)) return false;
  $meta_json = !empty($meta) ? wp_json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
  return $wpdb->insert($table, [
    'user_id'=>(int)$user_id, 'user_login'=>(string)$user_login,
    'event_type'=>(string)$event_type, 'event_key'=>(string)$event_key,
    'quote_id'=>(int)$quote_id, 'session_id'=>substr((string)$session_id,0,64),
    'page_id'=>substr((string)$page_id,0,64), 'meta_json'=>$meta_json,
    'ip'=>radar_usage_client_ip_bin(), 'ua'=>radar_usage_client_ua(),
    'created_at'=>current_time('mysql'), 'created_ts'=>time(),
  ], ['%d','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%d']);
}

function radar_is_business_day($ts) { $w = (int)date('N', (int)$ts); return ($w >= 1 && $w <= 5); }

function radar_last_business_days($count = 5, $from_ts = null) {
  if (!$from_ts) $from_ts = time();
  $days = []; $cursor = strtotime(date('Y-m-d 12:00:00', (int)$from_ts));
  while (count($days) < $count) {
    if (radar_is_business_day($cursor)) $days[] = date('Y-m-d', $cursor);
    $cursor = strtotime('-1 day', $cursor);
  }
  return array_reverse($days);
}

function radar_current_business_days_elapsed($from_ts = null) {
  if (!$from_ts) $from_ts = time();
  $todayDow = (int)date('N', (int)$from_ts);
  return ($todayDow >= 6) ? 5 : $todayDow;
}

function radar_usage_get_continuous_summary($wpdb, $table, $user_id, $business_days = 5) {
  $days = radar_last_business_days($business_days);
  $empty = ['score'=>0,'label'=>'Hay oportunidad','valid_days'=>0,'target_days'=>1,
    'today_useful'=>0,'recent_useful'=>0,'quality_points'=>0,'last_activity_ts'=>0,'last_activity_human'=>'-'];
  if (empty($days)) return $empty;

  $target_days = max(1, min((int)$business_days, count($days)));
  $start_ts = strtotime($days[0].' 00:00:00');
  $now_ts = time(); $today_key = date('Y-m-d', $now_ts);
  $today_start = strtotime(date('Y-m-d 00:00:00', $now_ts));
  $today_hm = date('H:i', $now_ts);
  $today_is_biz = radar_is_business_day($now_ts);
  $last_48h_ts = $now_ts - 48*3600;

  $rows = $wpdb->get_results($wpdb->prepare(
    "SELECT event_type, event_key, created_ts FROM {$table} WHERE user_id=%d AND created_ts>=%d ORDER BY id ASC",
    (int)$user_id, (int)$start_ts
  ), ARRAY_A);

  $by_day = []; $last_activity_ts = 0;
  $recent_useful = 0; $recent_scroll_hits = 0; $recent_ping_hits = 0;
  $today_weekend_activity = 0;

  foreach ($rows as $r) {
    $etype = (string)($r['event_type'] ?? ''); $cts = (int)($r['created_ts'] ?? 0);
    if ($cts <= 0) continue;
    if ($cts > $last_activity_ts) $last_activity_ts = $cts;
    $day = date('Y-m-d', $cts); $hm = date('H:i', $cts);

    if ($cts >= $last_48h_ts) {
      if (in_array($etype, ['radar_open','radar_refresh','radar_ping','radar_scroll'], true)) $recent_useful = 1;
      if ($etype === 'radar_scroll') $recent_scroll_hits++;
      if ($etype === 'radar_ping') $recent_ping_hits++;
    }
    if (!$today_is_biz && $cts >= $today_start) {
      if (in_array($etype, ['radar_ping','radar_scroll'], true) && $cts >= ($now_ts - 3600)) $today_weekend_activity = 1;
    }
    if (!in_array($day, $days, true)) continue;
    if (!isset($by_day[$day])) $by_day[$day] = ['morning_base'=>0,'morning_proof'=>0,'afternoon_base'=>0,'afternoon_proof'=>0];

    $is_morning = ($hm >= '08:00' && $hm <= '13:00');
    $is_afternoon = ($hm >= '13:01' && $hm <= '19:30');
    if (in_array($etype, ['radar_open','radar_refresh'], true)) {
      if ($is_morning) $by_day[$day]['morning_base'] = 1;
      if ($is_afternoon) $by_day[$day]['afternoon_base'] = 1;
    }
    if (in_array($etype, ['radar_ping','radar_scroll'], true)) {
      if ($is_morning) $by_day[$day]['morning_proof'] = 1;
      if ($is_afternoon) $by_day[$day]['afternoon_proof'] = 1;
    }
  }

  $valid_days = 0; $daily_completion_sum = 0.0;
  foreach ($days as $day) {
    $morning_ok = 0; $afternoon_ok = 0;
    if (!empty($by_day[$day])) {
      $morning_ok = (!empty($by_day[$day]['morning_base']) && !empty($by_day[$day]['morning_proof'])) ? 1 : 0;
      $afternoon_ok = (!empty($by_day[$day]['afternoon_base']) && !empty($by_day[$day]['afternoon_proof'])) ? 1 : 0;
    }
    $dc = 0.0;
    if ($morning_ok) $dc += 0.5; if ($afternoon_ok) $dc += 0.5;
    $daily_completion_sum += $dc;
    if ($dc >= 1.0) $valid_days++;
  }

  $today_morning_ok = 0; $today_afternoon_ok = 0; $today_blocks_done = 0; $today_useful = 0;
  if ($today_is_biz) {
    if (!empty($by_day[$today_key])) {
      $today_morning_ok = (!empty($by_day[$today_key]['morning_base']) && !empty($by_day[$today_key]['morning_proof'])) ? 1 : 0;
      $today_afternoon_ok = (!empty($by_day[$today_key]['afternoon_base']) && !empty($by_day[$today_key]['afternoon_proof'])) ? 1 : 0;
    }
    if ($today_morning_ok) $today_blocks_done++;
    if ($today_afternoon_ok) $today_blocks_done++;
    if ($today_hm >= '08:00' && $today_hm <= '13:00') $today_useful = $today_morning_ok ? 1 : 0;
    elseif ($today_hm >= '13:01' && $today_hm <= '19:30') $today_useful = ($today_morning_ok || $today_afternoon_ok) ? 1 : 0;
    else $today_useful = ($today_blocks_done > 0) ? 1 : 0;
  } else {
    $today_useful = $today_weekend_activity ? 1 : 0;
  }

  $consistency_points = (int)round(70 * min(1, $daily_completion_sum / $target_days));
  $recent_points = 0;
  if ($today_is_biz) {
    if ($today_blocks_done >= 2) $recent_points = 15;
    elseif ($today_blocks_done === 1) $recent_points = 8;
    elseif ($recent_useful) $recent_points = 4;
  } else {
    if ($today_useful) $recent_points = 8;
    elseif ($recent_useful) $recent_points = 4;
  }
  $quality_base = ($recent_ping_hits * 1) + ($recent_scroll_hits * 2);
  $quality_points = 0;
  if ($quality_base >= 12) $quality_points = 15;
  elseif ($quality_base >= 8) $quality_points = 10;
  elseif ($quality_base >= 4) $quality_points = 5;
  elseif ($quality_base >= 1) $quality_points = 2;

  $score = min(100, $consistency_points + $recent_points + $quality_points);
  $label = 'Hay oportunidad';
  if ($score >= 85) $label = 'Excelente';
  elseif ($score >= 70) $label = 'Muy bien';
  elseif ($score >= 55) $label = 'Bien';
  elseif ($score >= 35) $label = 'En seguimiento';

  return ['score'=>$score,'label'=>$label,'valid_days'=>$valid_days,'target_days'=>$target_days,
    'today_useful'=>$today_useful?1:0,'recent_useful'=>$recent_useful?1:0,'quality_points'=>$quality_points,
    'last_activity_ts'=>$last_activity_ts,'last_activity_human'=>$last_activity_ts ? hace($last_activity_ts) : '-'];
}

// ─── Helpers de UI ───
$exclude_bys = ['admin','ontime','mlimon','nog'];
$INTERNAL_USER_IDS = [1, 815, 816];
$internal_ip_ttl_days = 7;
$internal_ips_file = __DIR__ . '/internal_ips.json';
$internal_visitors_file = __DIR__ . '/internal_visitors.json';
$events_js_lookback_days = 150;
$events_js_min_ts = $now - ($events_js_lookback_days * 86400);

function toggle_dir($current, $target, $dir){ return ($current !== $target) ? 'desc' : ($dir === 'desc' ? 'asc' : 'desc'); }
function url_q($params = []){ $q = $_GET; foreach ($params as $k => $v) $q[$k] = $v; return '?' . http_build_query($q); }
function hace($ts){ $d = time() - (int)$ts; if ($d < 60) return $d.'s'; if ($d < 3600) return floor($d/60).'m'; if ($d < 86400) return floor($d/3600).'h'; return floor($d/86400).'d'; }
function money_to_float($raw){ if ($raw === null || $raw === '') return 0.0; $num = preg_replace('/[^0-9\.\-]/', '', (string)$raw); return ($num === '' || !is_numeric($num)) ? 0.0 : (float)$num; }
function fmt_money($raw){ return '$' . number_format(money_to_float($raw), 2); }
function row_class($last_ts){ $n = time(); if ($last_ts >= $n - 30*60) return 'hot30'; if ($last_ts >= $n - 4*3600) return 'hot4h'; return ''; }

function internal_ips_load($file, $ttl_days){
  $now = time(); $ttl = max(1,(int)$ttl_days)*86400;
  if (!file_exists($file)) return [];
  $raw = @file_get_contents($file); if ($raw === false || trim($raw) === '') return [];
  $data = json_decode($raw, true); if (!is_array($data)) return [];
  foreach ($data as $ip => $ts) { if ((int)$ts <= 0 || ($now - (int)$ts) > $ttl) unset($data[$ip]); }
  return $data;
}
function internal_ips_save($file, $data){
  $tmp = $file.'.tmp'; $json = json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  if ($json === false) return false;
  $fp = @fopen($tmp,'wb'); if (!$fp) return false;
  if (!flock($fp, LOCK_EX)) { fclose($fp); return false; }
  fwrite($fp, $json); fflush($fp); flock($fp, LOCK_UN); fclose($fp);
  return @rename($tmp, $file);
}
function internal_visitors_load($file){
  if (!file_exists($file)) return [];
  $raw = @file_get_contents($file); if ($raw === false || trim($raw) === '') return [];
  $data = json_decode($raw, true); return is_array($data) ? $data : [];
}

function normalize_by($by_raw, &$user_login_cache){
  $by = strtolower(trim((string)$by_raw));
  if ($by === '' || $by === '0') return '';
  if (ctype_digit($by)) {
    $id = (int)$by;
    if ($id > 0) {
      if (array_key_exists($id, $user_login_cache)) return $user_login_cache[$id];
      $u = get_user_by('id', $id);
      $login = ($u && !empty($u->user_login)) ? strtolower(trim((string)$u->user_login)) : '';
      $user_login_cache[$id] = $login; return $login;
    }
    return '';
  }
  return $by;
}
function is_excluded_by($by, $exclude_bys){ $by = strtolower(trim((string)$by)); if ($by === '') return false; return in_array($by, $exclude_bys, true) || str_starts_with($by, 'bot_'); }

function sort_cmp($a, $b, $sort, $dir){
  $mult = ($dir === 'asc') ? 1 : -1;
  return match($sort) {
    'title'    => $mult * strcmp($a['title'], $b['title']),
    'sessions' => $mult * ($a['sessions'] <=> $b['sessions']),
    'last'     => $mult * ($a['last_ts'] <=> $b['last_ts']),
    'date'     => $mult * ($a['created_ts'] <=> $b['created_ts']),
    'amount'   => $mult * ($a['amount_num'] <=> $b['amount_num']),
    'fit'      => $mult * ($a['fit_pct'] <=> $b['fit_pct']),
    default    => $mult * ($a['priority_pct'] <=> $b['priority_pct']),
  };
}

// ─── ENDPOINT RADAR USAGE ───
if (isset($_POST['radar_usage_action']) && is_user_logged_in() && (current_user_can('manage_options') || $login === 'ontime')) {
  $action_type = sanitize_text_field(wp_unslash($_POST['radar_usage_action']));
  $session_id  = sanitize_text_field(wp_unslash($_POST['session_id'] ?? ''));
  $page_id     = sanitize_text_field(wp_unslash($_POST['page_id'] ?? ''));
  $event_key   = sanitize_text_field(wp_unslash($_POST['event_key'] ?? ''));
  $quote_id    = (int)($_POST['quote_id'] ?? 0);

  $allowed = ['radar_open','radar_refresh','filter_change','radar_ping','radar_scroll'];
  if (!in_array($action_type, $allowed, true)) wp_send_json_error(['message'=>'Evento inválido'], 400);
  if (!radar_usage_table_exists($radar_usage_table, $wpdb)) wp_send_json_error(['message'=>'Tabla no existe'], 500);

  $uid = (int)($current_user->ID ?? 0);
  $ulogin = strtolower((string)($current_user->user_login ?? ''));
  $now_ts = time(); $day_start = strtotime(date('Y-m-d 00:00:00'));

  if ($action_type === 'radar_refresh') {
    $last_refresh = (int)$wpdb->get_var($wpdb->prepare("SELECT MAX(created_ts) FROM {$radar_usage_table} WHERE user_id=%d AND event_type='radar_refresh'", $uid));
    if ($last_refresh > 0 && ($now_ts - $last_refresh) < 600) wp_send_json_success(['ok'=>true,'deduped'=>1]);
  }
  if ($action_type === 'filter_change' && $event_key !== '') {
    $exists = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$radar_usage_table} WHERE user_id=%d AND event_type='filter_change' AND event_key=%s AND created_ts>=%d", $uid, $event_key, $now_ts-300));
    if ($exists > 0) wp_send_json_success(['ok'=>true,'deduped'=>1]);
  }
  if ($action_type === 'radar_open') {
    $exists = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$radar_usage_table} WHERE user_id=%d AND event_type='radar_open' AND session_id=%s AND created_ts>=%d", $uid, $session_id, $day_start));
    if ($exists > 0) wp_send_json_success(['ok'=>true,'deduped'=>1]);
  }
  if ($action_type === 'radar_ping') {
    $last_ping = (int)$wpdb->get_var($wpdb->prepare("SELECT MAX(created_ts) FROM {$radar_usage_table} WHERE user_id=%d AND event_type='radar_ping' AND session_id=%s AND page_id=%s", $uid, $session_id, $page_id));
    if ($last_ping > 0 && ($now_ts - $last_ping) < 45) wp_send_json_success(['ok'=>true,'deduped'=>1]);
  }
  if ($action_type === 'radar_scroll' && $event_key !== '') {
    $exists = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$radar_usage_table} WHERE user_id=%d AND event_type='radar_scroll' AND event_key=%s AND session_id=%s AND page_id=%s", $uid, $event_key, $session_id, $page_id));
    if ($exists > 0) wp_send_json_success(['ok'=>true,'deduped'=>1]);
  }

  radar_usage_insert_event($wpdb, $radar_usage_table, $uid, $ulogin, $action_type, $event_key, $quote_id, $session_id, $page_id, ['range'=>$range??'','sort'=>$sort??'','dir'=>$dir??'','limit'=>$limit??0]);
  wp_send_json_success(['ok'=>true]);
}

// ═══════════════════════════════════════════════════════════
//  PARTE 2: FETCH BASE + EVENTOS JS + INTERNAL IPS
// ═══════════════════════════════════════════════════════════

$dedupe_window = (int) ot2_u('dedupe_seconds', $modo);
$not_opened_max_age_days = 7;
$not_opened_min_age_minutes = 1440;

$internal_ips = internal_ips_load($internal_ips_file, $internal_ip_ttl_days);
$internal_visitors = internal_visitors_load($internal_visitors_file);
$internal_ips_dirty = false;
$user_login_cache = [];

if ($radar_request_ip !== '' && filter_var($radar_request_ip, FILTER_VALIDATE_IP)) {
  $prev_ts = isset($internal_ips[$radar_request_ip]) ? (int)$internal_ips[$radar_request_ip] : 0;
  if ($prev_ts <= 0 || ($now - $prev_ts) > 300) { $internal_ips[$radar_request_ip] = $now; $internal_ips_dirty = true; }
}

// Fetch all quotes
$quote_ids = get_posts([
  'post_type'=>'sliced_quote','post_status'=>['publish','draft','private'],
  'posts_per_page'=>8000,'orderby'=>'ID','order'=>'DESC','fields'=>'ids'
]);

// Accepted IDs bulk
$accepted_ids = [];
$tax = get_taxonomy('quote_status');
if ($tax && !empty($tax->query_var)) {
  $accepted_posts = get_posts([
    'post_type'=>'sliced_quote','post_status'=>['publish','draft','private'],
    'posts_per_page'=>8000,'fields'=>'ids',
    'tax_query'=>[['taxonomy'=>'quote_status','field'=>'slug','terms'=>['accepted']]]
  ]);
  foreach ($accepted_posts as $aqid) $accepted_ids[(int)$aqid] = true;
}

// Events JS table
$events_table = $wpdb->prefix . 'sliced_quote_events';
$ev_table_exists = ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $events_table)) === $events_table);
$event_rows_by_quote = [];

if ($ev_table_exists) {
  $event_rows_raw = $wpdb->get_results($wpdb->prepare(
    "SELECT quote_id, event_type, ts_unix, max_scroll, open_ms, visible_ms, session_id, page_id, visitor_id
     FROM {$events_table} WHERE ts_unix >= %d ORDER BY quote_id ASC, id ASC",
    $events_js_min_ts
  ), ARRAY_A);
  foreach ($event_rows_raw as $er) {
    $qid = (int)($er['quote_id'] ?? 0);
    if ($qid > 0) $event_rows_by_quote[$qid][] = $er;
  }
  unset($event_rows_raw);
}

// ═══════════════════════════════════════════════════════════
//  PARTE 3: PASADA 1 (calibración) + PASADA 2 (scoring)
// ═══════════════════════════════════════════════════════════

// Primero recorremos todas las cotizaciones para extraer datos base
$quotes_data = []; // datos intermedios por quote
$cal_data = [];    // para calibración FIT
$all_amounts = []; // para P80

$compare_window_sec = (int)ot2_u('compare_window_h', $modo) * 3600;
$multi_window_sec   = (int)ot2_u('multi_recent_hours', $modo) * 3600;
$ip_window_sec      = (int)ot2_u('imminent_ip_window_min', $modo) * 60;
$multip_win_sec     = (int)ot2_u('multip_ip_window_min', $modo) * 60;

foreach ($quote_ids as $id) {
  $log_val = get_post_meta($id, '_sliced_log', true);
  $log = [];
  if ($log_val) { $log = is_array($log_val) ? $log_val : @unserialize($log_val); if (!is_array($log)) $log = []; }

  $events = [];
  $has_raw_quote_viewed = false;

  foreach ($log as $ts => $entry) {
    if (!is_array($entry)) continue;
    if (($entry['type'] ?? '') !== 'quote_viewed') continue;

    $by_num = (int)($entry['by'] ?? 0);
    $by_raw = (string)($entry['by'] ?? $entry['user'] ?? $entry['username'] ?? $entry['user_login'] ?? $entry['by_name'] ?? $entry['display_name'] ?? 'guest');
    $by = normalize_by($by_raw, $user_login_cache);
    $ip = trim((string)($entry['ip'] ?? '')); if ($ip === '') $ip = 'sin_ip';

    if ($ip !== 'sin_ip' && ot2_bot_ip($ip)) continue;
    if ($ip !== 'sin_ip' && in_array($by_num, $INTERNAL_USER_IDS, true)) {
      $internal_ips[$ip] = time(); $internal_ips_dirty = true;
    }
    if (is_excluded_by($by, $exclude_bys)) continue;
    if ($by_num === 0 && $ip !== 'sin_ip' && isset($internal_ips[$ip])) continue;

    $ua = (string)($entry['ua'] ?? $entry['user_agent'] ?? $entry['agent'] ?? '');
    if (ot2_bot_ua($ua)) continue;

    $has_raw_quote_viewed = true;
    $events[] = ['ts'=>(int)$ts, 'ip'=>$ip, 'is_guest'=>($by_num === 0)];
  }

  if ($events) usort($events, fn($a,$b) => $a['ts'] <=> $b['ts']);

  // Dedupe + session counting
  $lastSeenByIp = []; $sessions = 0; $views24 = 0; $views48 = 0;
  $last_ts = 0; $session_ts = [];
  $guest_sessions = 0; $guest_24h = 0; $guest_48h = 0; $guest_7d = 0;
  $first_guest_ts = 0;
  $compare_ips_set = []; $multi_ips_set = []; $ips_120m_set = [];

  foreach ($events as $e) {
    $ts = $e['ts']; $ip = $e['ip'];
    if (!isset($lastSeenByIp[$ip]) || ($ts - $lastSeenByIp[$ip]) >= $dedupe_window) {
      $sessions++; $lastSeenByIp[$ip] = $ts; $session_ts[] = $ts;
      if (!empty($e['is_guest'])) {
        $guest_sessions++;
        if ($first_guest_ts <= 0) $first_guest_ts = $ts;
        if ($ts >= $now - 24*3600) $guest_24h++;
        if ($ts >= $now - 48*3600) $guest_48h++;
        if ($ts >= $now - 7*86400) $guest_7d++;
      }
      if ($ts >= $now - 24*3600) $views24++;
      if ($ts >= $now - 48*3600) $views48++;
      if ($ts > $last_ts) $last_ts = $ts;
    }
    if ($ts >= $now - $compare_window_sec) $compare_ips_set[$ip] = true;
    if ($ts >= $now - $multi_window_sec)   $multi_ips_set[$ip] = true;
    if ($ts >= $now - $ip_window_sec)      $ips_120m_set[$ip] = true;
  }

  if ($sessions > 0 && $minLastViewTs && $last_ts < $minLastViewTs) continue;

  $uniq_ips_total = count($lastSeenByIp);

  // IPs post first guest
  $ips_post_guest_win = [];
  if ($first_guest_ts > 0) {
    $win_end = $first_guest_ts + $multip_win_sec;
    foreach ($events as $e2) {
      if ($e2['ts'] < $first_guest_ts || $e2['ts'] > $win_end) continue;
      if ($e2['ip'] !== 'sin_ip' && $e2['ip'] !== '') $ips_post_guest_win[$e2['ip']] = true;
    }
  }

  // Span 48h
  $ts48 = [];
  $win48 = (int)ot2_u('decision_window_h', $modo) * 3600;
  foreach ($session_ts as $tss) { if ($tss >= $now - $win48) $ts48[] = $tss; }
  $span48 = (count($ts48) >= 2) ? (max($ts48) - min($ts48)) : 0;

  // Gap
  $gap_days = null;
  if (count($session_ts) >= 2) {
    $prev_ts = $session_ts[count($session_ts) - 2];
    $gap_days = (int)floor(($last_ts - $prev_ts) / 86400);
    if ($gap_days < 0) $gap_days = 0;
  }

  $post = get_post($id); if (!$post) continue;
  $created_ts = strtotime($post->post_date_gmt . ' GMT');
  $age_days = max(0, (int)floor(($now - $created_ts) / 86400));
  $age_hours = ($now - $created_ts) / 3600.0;

  $amount_raw = get_post_meta($id, '_sliced_totals_for_ordering', true);
  $amount_num = money_to_float($amount_raw);
  $accepted = isset($accepted_ids[(int)$id]);

  // Eventos JS para esta cotización
  $ev_rows = $event_rows_by_quote[$id] ?? [];
  $es = ot2_agregar_eventos($ev_rows, $internal_visitors);

  // Not opened
  $not_opened_age_ok = ($created_ts >= ($now - $not_opened_max_age_days*86400) && $age_hours >= ($not_opened_min_age_minutes/60));
  $not_opened_has_views = !empty($has_raw_quote_viewed);
  $not_opened_has_js = ($es['opens']>0||$es['closes']>0||$es['tot_views']>0||$es['tot_rev']>0||$es['loops']>0||$es['coupons']>0||$es['scroll_any']>0||$es['vis_max']>0||$es['vis_sum']>0||$es['uniq_v']>0);
  $is_not_opened = (!$accepted && $not_opened_age_ok && !$not_opened_has_views && !$not_opened_has_js);

  // Guardar datos intermedios
  $quotes_data[$id] = [
    'id'=>$id, 'title'=>get_the_title($id), 'amount_raw'=>$amount_raw, 'amount_num'=>$amount_num,
    'sessions'=>$sessions, 'guest_sessions'=>$guest_sessions,
    'guest_24h'=>$guest_24h, 'guest_48h'=>$guest_48h, 'guest_7d'=>$guest_7d,
    'views24'=>$views24, 'views48'=>count($ts48), 'span48'=>$span48,
    'last_ts'=>$last_ts, 'gap_days'=>$gap_days, 'age_days'=>$age_days, 'age_hours'=>$age_hours,
    'accepted'=>$accepted, 'uniq_ips_total'=>$uniq_ips_total,
    'ips_120m_count'=>count($ips_120m_set),
    'ips_post_guest_count'=>count($ips_post_guest_win),
    'compare_ips_count'=>count($compare_ips_set),
    'multi_ips_24h'=>count($multi_ips_set),
    'first_guest_ts'=>$first_guest_ts,
    'created_ts'=>$created_ts, 'post_date'=>$post->post_date,
    'link'=>get_permalink($id), 'es'=>$es,
    'is_not_opened'=>$is_not_opened,
  ];

  $all_amounts[] = $amount_num;

  // Calibración: solo cotizaciones con >= 1 sesión y >= 30 días
  if ($sessions >= 1 && $age_days >= 30) {
    $cal_data[] = ['sessions'=>$sessions, 'ips'=>$uniq_ips_total, 'gap'=>$gap_days, 'accepted'=>$accepted];
  }
}

// ─── Calibrar FIT ───
$cal = ot2_calibrate($cal_data);

// ─── P80 dinámico ───
$p80_threshold = ot2_p80($all_amounts, $modo);

// ═══════════════════════════════════════════════════════════
//  PASADA 2: SCORING + BUCKET ASSIGNMENT
// ═══════════════════════════════════════════════════════════

$rows = [];
$buckets_grouped = []; // bucket_name => [rows]
foreach (BUCKET_PRIORITY as $bp) $buckets_grouped[$bp] = [];
$buckets_grouped['no_abierta'] = [];

$band_counts = [
  '0-4.99'=>['total'=>0,'sales'=>0],'5-7.99'=>['total'=>0,'sales'=>0],
  '8-9.99'=>['total'=>0,'sales'=>0],'10-11.99'=>['total'=>0,'sales'=>0],'12+'=>['total'=>0,'sales'=>0],
];
$total_all = 0; $total_sales = 0;

foreach ($quotes_data as $id => $qd) {
  // Compute FIT
  $fit_prob = ot2_fit_prob($qd['sessions'], $qd['uniq_ips_total'], $qd['gap_days'],
    $cal['global'], $cal['rate_sess'], $cal['rate_ips'], $cal['rate_gap']);
  $fit_pct = max(0.0, min(100.0, $fit_prob * 100.0));

  // Score
  $params = array_merge($qd, [
    'now'=>$now, 'fit_pct'=>$fit_pct, 'cot_total'=>$qd['amount_num'],
    'p80_threshold'=>$p80_threshold,
  ]);
  $result = ot2_score($params, $modo);

  $row = [
    'quote_id'     => $id,
    'title'        => $qd['title'],
    'amount_raw'   => $qd['amount_raw'],
    'amount_num'   => $qd['amount_num'],
    'amount_fmt'   => fmt_money($qd['amount_raw']),
    'sessions'     => $qd['sessions'],
    'last_ts'      => $qd['last_ts'],
    'last'         => $qd['last_ts'] ? date_i18n('Y-m-d H:i', $qd['last_ts']) : '-',
    'created_ts'   => $qd['created_ts'],
    'created'      => date_i18n('Y-m-d', strtotime($qd['post_date'])),
    'link'         => $qd['link'],
    'gap_days'     => $qd['gap_days'],
    'accepted'     => $qd['accepted'] ? 1 : 0,
    'fit_prob'     => $fit_prob,
    'fit_pct'      => $fit_pct,
    'priority_pct' => $result['priority_pct'],
    'bucket'       => $result['bucket'],
    'buckets'      => $result['buckets'],
    'momentum'     => $result['momentum'],
    'pc_source'    => $result['pc_source'],
    'icons'        => $result['icons'],
    'pss'          => $result['pss'],
    'score'        => $result['score'],
    'cooling_reason'=> $result['cooling_reason'],
    'is_not_opened' => $qd['is_not_opened'] ? 1 : 0,
    'debug'        => $result['debug'],
    'es'           => $qd['es'],
  ];

  $rows[] = $row;
  $total_all++;
  if ($qd['accepted']) $total_sales++;

  // Band FIT
  $band = '12+';
  if ($fit_pct < 5) $band = '0-4.99';
  elseif ($fit_pct < 8) $band = '5-7.99';
  elseif ($fit_pct < 10) $band = '8-9.99';
  elseif ($fit_pct < 12) $band = '10-11.99';
  $band_counts[$band]['total']++;
  if ($qd['accepted']) $band_counts[$band]['sales']++;

  // Agrupar en buckets
  if ($qd['is_not_opened']) $buckets_grouped['no_abierta'][] = $row;
  foreach ($result['buckets'] as $bk) {
    if (isset($buckets_grouped[$bk])) $buckets_grouped[$bk][] = $row;
  }
}

// Sorter
$sorter = function($a, $b) use ($sort, $dir) {
  $r = sort_cmp($a, $b, $sort, $dir);
  if ($r !== 0) return $r;
  $t = ($b['priority_pct'] <=> $a['priority_pct']);
  if ($t !== 0) return $t;
  return ($b['last_ts'] <=> $a['last_ts']);
};

foreach ($buckets_grouped as &$bg) usort($bg, $sorter);
unset($bg);
usort($rows, $sorter);
$rows = array_slice($rows, 0, $limit);

$close_global_pct = ($total_all > 0) ? (100.0 * $total_sales / $total_all) : 0.0;

// Termómetro
$thermo_user_id = (int)$current_user->ID;
$thermo_user_login = $login;
if (current_user_can('manage_options') && isset($_GET['thermo_user'])) {
  $cuid = (int)$_GET['thermo_user'];
  if ($cuid > 0) { $cu = get_user_by('id', $cuid); if ($cu) { $thermo_user_id = (int)$cu->ID; $thermo_user_login = strtolower((string)$cu->user_login); } }
}
$radar_usage_summary = ['score'=>0,'label'=>'Hay oportunidad','valid_days'=>0,'target_days'=>1,'today_useful'=>0,'recent_useful'=>0,'quality_points'=>0,'last_activity_ts'=>0,'last_activity_human'=>'-'];
$show_radar_thermometer = false;
if (is_user_logged_in() && radar_usage_table_exists($radar_usage_table, $wpdb)) {
  $radar_usage_summary = radar_usage_get_continuous_summary($wpdb, $radar_usage_table, $thermo_user_id, 5);
}
$show_radar_thermometer = $radar_usage_admin_only ? ($login === 'admin') : is_user_logged_in();

if ($internal_ips_dirty) internal_ips_save($internal_ips_file, $internal_ips);

// ═══════════════════════════════════════════════════════════
//  PARTE 4: HTML / CSS / JS
// ═══════════════════════════════════════════════════════════

function render_bucket_v2($bucket_key, $items, $sort, $dir, $show_gap = false) {
  global $debug_mode;
  $meta = BM[$bucket_key] ?? ['?','#666','#f5f5f5',$bucket_key];
  $emoji = $meta[0]; $color = $meta[1]; $bg = $meta[2]; $label = $meta[3];
  $count = count($items);
  $accepted_count = 0;
  foreach ($items as $r) { if (!empty($r['accepted'])) $accepted_count++; }
  $rate = $count > 0 ? round(100.0 * $accepted_count / $count, 1) : 0;

  $id_attr = 'bk_' . $bucket_key;
  echo '<div class="bucket-shell" id="'.esc_attr($id_attr).'">';
  echo '<div class="bucket-head" onclick="toggleBucket(\''.esc_attr($id_attr).'\')" style="border-left:4px solid '.esc_attr($color).';background:'.esc_attr($bg).';">';
  echo '<div class="bucket-head-left">';
  echo '<div class="bucket-head-title">'.esc_html($emoji.' '.$label).' <span style="font-weight:400;color:#666;">('.$count.')</span></div>';
  echo '<div class="bucket-head-meta">Cierre: '.number_format($rate,1).'% ('.$accepted_count.'/'.$count.')</div>';
  echo '</div>';
  echo '<div class="bucket-head-right"><span class="bk-arrow" id="arrow_'.esc_attr($id_attr).'">▶</span></div>';
  echo '</div>';

  // Preview (top 3 collapsed)
  echo '<div class="bucket-collapsed-preview" id="preview_'.esc_attr($id_attr).'">';
  $preview = array_slice($items, 0, 3);
  foreach ($preview as $pr) {
    $mom_icon = match($pr['momentum'] ?? 'stable') { 'cooling'=>' ↘', default=>'' };
    echo '<div class="bucket-preview-item">'.esc_html($pr['title']??'').' — '.esc_html($pr['amount_fmt']??'').' — P:'.number_format($pr['priority_pct'],1).'%'.$mom_icon.'</div>';
  }
  echo '</div>';

  // Full table (hidden by default)
  echo '<div class="bucket-body bucket-hidden" id="body_'.esc_attr($id_attr).'">';
  echo '<table><thead><tr>';
  echo '<th style="width:35%;">Título / Importe</th>';
  echo '<th style="width:7%;" class="center">Venta</th>';
  echo '<th style="width:7%;" class="center"><a href="'.esc_url(url_q(['sort'=>'fit','dir'=>toggle_dir($sort,'fit',$dir)])).'">Score%</a></th>';
  echo '<th style="width:9%;" class="center"><a href="'.esc_url(url_q(['sort'=>'priority','dir'=>toggle_dir($sort,'priority',$dir)])).'">Prior%</a></th>';
  echo '<th style="width:5%;" class="center">Mom</th>';
  echo '<th style="width:6%;" class="num"><a href="'.esc_url(url_q(['sort'=>'sessions','dir'=>toggle_dir($sort,'sessions',$dir)])).'">Vistas</a></th>';
  echo '<th style="width:14%;"><a href="'.esc_url(url_q(['sort'=>'last','dir'=>toggle_dir($sort,'last',$dir)])).'">Última</a></th>';
  if ($show_gap) echo '<th style="width:5%;">Gap</th>';
  echo '<th style="width:5%;" class="center">Ver</th>';
  echo '</tr></thead><tbody>';

  if (!$items) {
    $cols = $show_gap ? 9 : 8;
    echo '<tr><td colspan="'.$cols.'" class="center" style="color:#666;">Sin registros.</td></tr>';
  } else {
    $shown = array_slice($items, 0, 15);
    foreach ($shown as $r) {
      $cls = row_class($r['last_ts']);
      $badge = !empty($r['accepted']) ? '<span class="badge badge-ok">OK</span>' : '<span class="badge badge-no">no</span>';
      $icons_str = '';
      foreach (($r['icons']??[]) as $ik=>$_) {
        $icons_str .= match($ik) { 'coupon'=>'🎟️','promo'=>'💣','price'=>'💸','sv_price'=>'👤','mv_price'=>'👥', default=>'' };
      }
      if (!empty($r['is_not_opened'])) $icons_str .= '❌';
      $title_show = trim($icons_str . ' ' . ($r['title']??''));

      $mom_label = match($r['momentum']??'stable') { 'cooling'=>'↘','stable'=>'→', default=>'–' };
      $mom_color = match($r['momentum']??'stable') { 'cooling'=>'#f59e0b','stable'=>'#22c55e', default=>'#999' };

      echo '<tr class="'.esc_attr($cls).'">';
      echo '<td><div class="titlewrap"><div class="titletext">'.esc_html($title_show).'</div><div class="amount">'.esc_html($r['amount_fmt']).'</div></div></td>';
      echo '<td class="center">'.$badge.'</td>';
      echo '<td class="center"><b>'.number_format($r['fit_pct'],2).'%</b></td>';
      echo '<td class="center"><b>'.number_format($r['priority_pct'],2).'%</b></td>';
      echo '<td class="center" style="color:'.esc_attr($mom_color).';font-weight:bold;">'.esc_html($mom_label).'</td>';
      echo '<td class="num"><b>'.(int)$r['sessions'].'</b></td>';
      echo '<td>';
      if (!empty($r['last_ts'])) echo esc_html($r['last']).' <span style="color:#666;">(hace '.esc_html(hace($r['last_ts'])).')</span>';
      else echo '-';
      echo '</td>';
      if ($show_gap) echo '<td class="center">'.($r['gap_days']!==null ? (int)$r['gap_days'].'d' : '-').'</td>';
      echo '<td class="center"><a href="'.esc_url($r['link']).'" target="_blank">Abrir</a></td>';
      echo '</tr>';
    }
  }
  echo '</tbody></table></div></div>';
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Radar v2.4 — On Time</title>
<style>
  body{font-family:Arial;padding:20px;background:#fafafa;}
  table{border-collapse:collapse;width:100%;table-layout:fixed;margin-bottom:10px;}
  th,td{border:1px solid #ddd;padding:7px;font-size:13px;vertical-align:middle;}
  th{background:#f4f4f4;} th a{color:#000;text-decoration:none;} th a:hover{text-decoration:underline;}
  .num{text-align:right;} .center{text-align:center;}
  .titlewrap{display:flex;gap:10px;align-items:center;min-width:0;}
  .titletext{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
  .amount{flex:0 0 auto;font-weight:bold;color:#111;background:#eef2ff;border:1px solid #dbe3ff;padding:2px 8px;border-radius:999px;font-size:12px;}
  .btns a{display:inline-block;padding:6px 10px;border:1px solid #ccc;margin-right:6px;text-decoration:none;border-radius:6px;color:#000;background:#fff;}
  .btns a:hover{background:#eee;} .btns a.active{text-decoration:underline;font-weight:bold;background:#fff;border-color:#bbb;}
  .hot4h{background:#fff7cc;} .hot30{background:#ffd9d9;font-weight:bold;}
  .section-title{margin:10px 0 8px;font-size:16px;} .hint{color:#666;font-size:12px;margin-top:-2px;margin-bottom:10px;}
  .badge{display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:bold;border:1px solid #ddd;background:#f3f4f6;color:#111;white-space:nowrap;}
  .badge-ok{background:#dcfce7;border-color:#86efac;color:#166534;}
  .badge-no{background:#f3f4f6;border-color:#e5e7eb;color:#374151;}
  .mini{font-size:12px;color:#444;margin:6px 0 14px;}

  .modo-selector{display:flex;gap:4px;margin:8px 0 16px;}
  .modo-btn{padding:6px 14px;border:1px solid #ccc;border-radius:8px;text-decoration:none;color:#333;background:#fff;font-size:13px;}
  .modo-btn:hover{background:#f0f0f0;} .modo-btn.active{background:#111;color:#fff;border-color:#111;}

  .radar-thermo-wrap{display:flex;justify-content:flex-start;margin:10px 0 18px;}
  .radar-thermo-card{width:100%;max-width:480px;border:1px solid #e5e7eb;background:#fff;border-radius:16px;padding:14px 16px;box-shadow:0 4px 14px rgba(0,0,0,.06);}
  .radar-thermo-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;}
  .radar-thermo-title{font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;}
  .radar-thermo-sub{font-size:12px;color:#6b7280;line-height:1.35;}
  .radar-thermo-score{flex:0 0 auto;font-size:30px;font-weight:800;line-height:1;color:#111827;}
  .radar-thermo-bar{margin-top:12px;height:10px;background:#edf2f7;border-radius:999px;overflow:hidden;}
  .radar-thermo-fill{height:100%;background:linear-gradient(90deg,#f59e0b 0%,#22c55e 100%);border-radius:999px;}
  .radar-thermo-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:14px;}
  .radar-stat-pill{background:#f9fafb;border:1px solid #eef2f7;border-radius:12px;padding:10px 10px 9px;}
  .radar-stat-label{display:block;font-size:10px;color:#6b7280;margin-bottom:2px;}
  .radar-stat-value{display:block;font-size:12px;font-weight:700;color:#111827;}
  .radar-thermo-foot{margin-top:10px;font-size:11px;color:#6b7280;}

  .bucket-shell{border:1px solid #e5e7eb;border-radius:12px;margin:10px 0;overflow:hidden;background:#fff;}
  .bucket-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;cursor:pointer;}
  .bucket-head-left{min-width:0;} .bucket-head-title{font-size:15px;font-weight:700;}
  .bucket-head-meta{font-size:12px;color:#666;margin-top:3px;}
  .bucket-head-right{flex:0 0 auto;font-size:14px;color:#999;}
  .bucket-body{padding:0 0 10px;}
  .bucket-collapsed-preview{padding:0 14px 10px;}
  .bucket-preview-item{font-size:12px;color:#333;padding:5px 0;border-top:1px dashed #eee;}
  .bucket-hidden{display:none;}

  .cal-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:12px 16px;margin:10px 0;font-size:13px;}
  .cal-card b{color:#111;} .cal-card .cal-ok{color:#16a34a;} .cal-card .cal-no{color:#dc2626;}

  @media(max-width:900px){.radar-thermo-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
  @media(max-width:640px){.radar-thermo-card{max-width:none;}.radar-thermo-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>

<h2>Radar v2.4 — On Time</h2>

<!-- Modo selector -->
<div class="modo-selector">
  <?php foreach (['agresivo','medio','ligero'] as $m): ?>
    <a href="<?php echo esc_url(url_q(['modo'=>$m])); ?>" class="modo-btn <?php echo $modo===$m?'active':''; ?>"><?php echo ucfirst($m); ?></a>
  <?php endforeach; ?>
</div>

<!-- Calibración FIT -->
<div class="cal-card">
  <b>FIT calibrado:</b>
  <?php if ($cal['calibrated']): ?>
    <span class="cal-ok">Sí</span> — base: <?php echo number_format($cal['global']*100,2); ?>% (<?php echo (int)$cal['cerrados']; ?>/<?php echo (int)$cal['total']; ?> accepted)
  <?php else: ?>
    <span class="cal-no">No</span> — usando tasas fallback (<?php echo (int)$cal['total']; ?> cotizaciones, <?php echo (int)$cal['cerrados']; ?> accepted, mínimo 5/3)
  <?php endif; ?>
  · P80 alto importe: $<?php echo number_format($p80_threshold,0); ?>
  · Modo: <b><?php echo esc_html($modo); ?></b>
</div>

<!-- Admin: termómetro selector -->
<?php if (current_user_can('manage_options')): ?>
<div style="margin:0 0 10px;">
  <form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
    <?php foreach($_GET as $k=>$v){ if($k==='thermo_user') continue; ?>
      <input type="hidden" name="<?php echo esc_attr($k); ?>" value="<?php echo esc_attr($v); ?>">
    <?php } ?>
    <label><b>Termómetro de:</b></label>
    <select name="thermo_user">
      <option value="1" <?php selected($thermo_user_id, 1); ?>>admin</option>
      <option value="815" <?php selected($thermo_user_id, 815); ?>>ontime</option>
    </select>
    <button type="submit">Ver</button>
  </form>
</div>
<?php endif; ?>

<!-- Termómetro -->
<?php if ($show_radar_thermometer): ?>
<div class="radar-thermo-wrap">
  <div class="radar-thermo-card">
    <div class="radar-thermo-head">
      <div>
        <div class="radar-thermo-title">Termómetro de uso<?php echo current_user_can('manage_options')?' · '.esc_html($thermo_user_login):''; ?></div>
        <div class="radar-thermo-sub">Constancia: <?php echo (int)$radar_usage_summary['valid_days']; ?>/<?php echo (int)$radar_usage_summary['target_days']; ?> días · <?php echo esc_html($radar_usage_summary['label']); ?></div>
      </div>
      <div class="radar-thermo-score"><?php echo (int)$radar_usage_summary['score']; ?>%</div>
    </div>
    <div class="radar-thermo-bar"><div class="radar-thermo-fill" style="width:<?php echo (int)$radar_usage_summary['score']; ?>%;"></div></div>
    <div class="radar-thermo-grid">
      <div class="radar-stat-pill"><span class="radar-stat-label">Días útiles</span><span class="radar-stat-value"><?php echo (int)$radar_usage_summary['valid_days']; ?>/<?php echo (int)$radar_usage_summary['target_days']; ?></span></div>
      <div class="radar-stat-pill"><span class="radar-stat-label">Hoy activo</span><span class="radar-stat-value"><?php echo !empty($radar_usage_summary['today_useful'])?'Sí':'No'; ?></span></div>
      <div class="radar-stat-pill"><span class="radar-stat-label">Uso reciente</span><span class="radar-stat-value"><?php echo !empty($radar_usage_summary['recent_useful'])?'Sí':'No'; ?></span></div>
      <div class="radar-stat-pill"><span class="radar-stat-label">Calidad</span><span class="radar-stat-value"><?php echo (int)$radar_usage_summary['quality_points']; ?>/15</span></div>
    </div>
    <div class="radar-thermo-foot">Última actividad: <?php echo esc_html($radar_usage_summary['last_activity_human']); ?></div>
  </div>
</div>
<?php endif; ?>

<!-- FIT bands (admin) -->
<?php if (current_user_can('manage_options')): ?>
<div class="mini">
  <b>Score% vs Ventas</b> — Total: <?php echo (int)$total_all; ?> | Ventas: <?php echo (int)$total_sales; ?> | Cierre: <?php echo number_format($close_global_pct,2); ?>%
</div>
<table>
<thead><tr><th>Banda FIT%</th><th class="center">Cotizaciones</th><th class="center">Ventas</th><th class="center">Tasa cierre</th></tr></thead>
<tbody>
<?php foreach (['0-4.99','5-7.99','8-9.99','10-11.99','12+'] as $b):
  $t=(int)$band_counts[$b]['total']; $s=(int)$band_counts[$b]['sales']; $rate=$t>0?(100.0*$s/$t):0; ?>
<tr><td><b><?php echo esc_html($b); ?></b></td><td class="center"><?php echo $t; ?></td><td class="center"><?php echo $s; ?></td><td class="center"><b><?php echo number_format($rate,2); ?>%</b></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<!-- Filtros -->
<div class="btns" style="margin:10px 0;">
  <a href="<?php echo esc_url(url_q(['range'=>'all'])); ?>" class="<?php echo $range==='all'?'active':''; ?>">Todas</a>
  <a href="<?php echo esc_url(url_q(['range'=>'48h'])); ?>" class="<?php echo $range==='48h'?'active':''; ?>">48h</a>
  <a href="<?php echo esc_url(url_q(['range'=>'4h'])); ?>" class="<?php echo $range==='4h'?'active':''; ?>">4h</a>
  <a href="<?php echo esc_url(url_q(['range'=>'30m'])); ?>" class="<?php echo $range==='30m'?'active':''; ?>">30m</a>
</div>

<form method="get" style="margin:10px 0;">
  <?php foreach($_GET as $k=>$v){ if($k==='limit') continue; ?>
    <input type="hidden" name="<?php echo esc_attr($k); ?>" value="<?php echo esc_attr($v); ?>">
  <?php } ?>
  Mostrar <input type="number" name="limit" value="<?php echo (int)$limit; ?>" style="width:70px" min="10" max="300"> <button>Actualizar</button>
</form>

<!-- BUCKETS -->
<?php
$gap_buckets = ['re_enganche','re_enganche_caliente','revivio','regreso'];

foreach (BUCKET_PRIORITY as $bk) {
  if (empty($buckets_grouped[$bk])) continue;
  render_bucket_v2($bk, $buckets_grouped[$bk], $sort, $dir, in_array($bk, $gap_buckets, true));
}

// No abierta
if (!empty($buckets_grouped['no_abierta'])) {
  render_bucket_v2('no_abierta', $buckets_grouped['no_abierta'], $sort, $dir, false);
}
?>

<!-- Ranking general -->
<div class="section-title">Ranking general</div>
<div class="hint">Prioridad% = FIT% + recencia + intención precio. Modo: <?php echo esc_html($modo); ?>.</div>

<table>
<thead><tr>
  <th style="width:35%;"><a href="<?php echo esc_url(url_q(['sort'=>'title','dir'=>toggle_dir($sort,'title',$dir)])); ?>">Título</a> / <a href="<?php echo esc_url(url_q(['sort'=>'amount','dir'=>toggle_dir($sort,'amount',$dir)])); ?>">Importe</a></th>
  <th style="width:7%;" class="center">Bucket</th>
  <th style="width:6%;" class="center">Venta</th>
  <th style="width:7%;" class="center"><a href="<?php echo esc_url(url_q(['sort'=>'fit','dir'=>toggle_dir($sort,'fit',$dir)])); ?>">Score%</a></th>
  <th style="width:9%;" class="center"><a href="<?php echo esc_url(url_q(['sort'=>'priority','dir'=>toggle_dir($sort,'priority',$dir)])); ?>">Prior%</a></th>
  <th style="width:5%;" class="center">Mom</th>
  <th style="width:6%;" class="num"><a href="<?php echo esc_url(url_q(['sort'=>'sessions','dir'=>toggle_dir($sort,'sessions',$dir)])); ?>">Vistas</a></th>
  <th style="width:14%;"><a href="<?php echo esc_url(url_q(['sort'=>'last','dir'=>toggle_dir($sort,'last',$dir)])); ?>">Última</a></th>
  <th style="width:6%;"><a href="<?php echo esc_url(url_q(['sort'=>'date','dir'=>toggle_dir($sort,'date',$dir)])); ?>">Creada</a></th>
  <th style="width:4%;" class="center">Ver</th>
</tr></thead>
<tbody>
<?php foreach($rows as $r): $cls = row_class($r['last_ts']); ?>
<tr class="<?php echo esc_attr($cls); ?>">
  <td>
    <div class="titlewrap">
      <?php
      $icons_str = '';
      foreach (($r['icons']??[]) as $ik=>$_) {
        $icons_str .= match($ik) { 'coupon'=>'🎟️','promo'=>'💣','price'=>'💸','sv_price'=>'👤','mv_price'=>'👥', default=>'' };
      }
      if (!empty($r['is_not_opened'])) $icons_str .= '❌';
      $title_show = trim($icons_str . ' ' . ($r['title']??''));
      ?>
      <div class="titletext"><?php echo esc_html($title_show); ?></div>
      <div class="amount"><?php echo esc_html($r['amount_fmt']); ?></div>
    </div>
  </td>
  <td class="center" style="font-size:11px;">
    <?php
    $bk = $r['bucket'];
    if ($bk) {
      $bm = BM[$bk] ?? ['?','#666','#f5f5f5',$bk];
      echo '<span style="background:'.esc_attr($bm[2]).';color:'.esc_attr($bm[1]).';padding:2px 6px;border-radius:6px;font-size:10px;font-weight:700;white-space:nowrap;">'.esc_html($bm[0].' '.$bm[3]).'</span>';
    } else { echo '-'; }
    ?>
  </td>
  <td class="center"><?php echo !empty($r['accepted'])?'<span class="badge badge-ok">OK</span>':'<span class="badge badge-no">no</span>'; ?></td>
  <td class="center"><b><?php echo number_format($r['fit_pct'],2); ?>%</b></td>
  <td class="center"><b><?php echo number_format($r['priority_pct'],2); ?>%</b></td>
  <td class="center" style="color:<?php echo match($r['momentum']??'stable'){'cooling'=>'#f59e0b','stable'=>'#22c55e',default=>'#999'}; ?>;font-weight:bold;">
    <?php echo match($r['momentum']??'stable'){'cooling'=>'↘','stable'=>'→',default=>'–'}; ?>
  </td>
  <td class="num"><b><?php echo (int)$r['sessions']; ?></b></td>
  <td><?php echo !empty($r['last_ts']) ? esc_html($r['last']).' <span style="color:#666;">(hace '.esc_html(hace($r['last_ts'])).')</span>' : '-'; ?></td>
  <td><?php echo esc_html($r['created']); ?></td>
  <td class="center"><a href="<?php echo esc_url($r['link']); ?>" target="_blank">Abrir</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<script>
function toggleBucket(id) {
  var body = document.getElementById('body_' + id);
  var preview = document.getElementById('preview_' + id);
  var arrow = document.getElementById('arrow_' + id);
  if (body.classList.contains('bucket-hidden')) {
    body.classList.remove('bucket-hidden');
    preview.style.display = 'none';
    arrow.textContent = '▼';
  } else {
    body.classList.add('bucket-hidden');
    preview.style.display = '';
    arrow.textContent = '▶';
  }
}

// Radar usage tracking
(function(){
  var ajaxUrl = window.location.href;
  var sessionKey = 'radar_usage_session_id';
  var pageKey = 'radar_usage_page_id';

  function uuidv4() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random() * 16 | 0; return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
  }

  function getOrSet(key) {
    try { var v = sessionStorage.getItem(key); if (!v) { v = uuidv4(); sessionStorage.setItem(key, v); } return v; }
    catch(e) { return uuidv4(); }
  }

  var sessionId = getOrSet(sessionKey);
  var pageId = getOrSet(pageKey);
  var sentScrollMarks = {};
  var lastPingAt = 0;

  function postUsage(action, extra) {
    extra = extra || {};
    var payload = { radar_usage_action: action, session_id: sessionId, page_id: pageId };
    Object.keys(extra).forEach(function(k){ payload[k] = extra[k]; });

    if (['filter_change','radar_refresh','radar_ping','radar_scroll'].indexOf(action) >= 0 && navigator.sendBeacon) {
      try { var fd = new FormData(); Object.keys(payload).forEach(function(k){ fd.append(k, payload[k]); }); navigator.sendBeacon(ajaxUrl, fd); return; } catch(e){}
    }
    fetch(ajaxUrl, { method:'POST', credentials:'same-origin', keepalive:true,
      headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
      body: new URLSearchParams(payload).toString()
    }).catch(function(){});
  }

  function scrollPct() {
    var d=document.documentElement, b=document.body;
    var st=window.pageYOffset||d.scrollTop||b.scrollTop||0;
    var sh=Math.max(b.scrollHeight,d.scrollHeight,b.offsetHeight,d.offsetHeight,b.clientHeight,d.clientHeight);
    return Math.round((st/Math.max(1,sh-(window.innerHeight||d.clientHeight||0)))*100);
  }

  function trackScroll() {
    var p = scrollPct();
    [75,90].forEach(function(m){ if(p>=m && !sentScrollMarks[m]){ sentScrollMarks[m]=true; postUsage('radar_scroll',{event_key:'scroll_'+m}); } });
  }

  function heartbeat(force) {
    var n=Date.now(); if(!force && (n-lastPingAt)<30000) return; if(document.hidden) return;
    lastPingAt=n; postUsage('radar_ping',{event_key:'visible_heartbeat'});
  }

  postUsage('radar_open');
  var usp = new URLSearchParams(location.search);
  postUsage('radar_refresh', { event_key: [usp.get('range')||'all',usp.get('sort')||'priority',usp.get('dir')||'desc',usp.get('limit')||''].join('|') });

  document.querySelectorAll('.btns a, th a').forEach(function(a){ a.addEventListener('click', function(){ postUsage('filter_change',{event_key:a.getAttribute('href')||''}); }); });

  window.addEventListener('scroll', function(){ trackScroll(); heartbeat(false); }, {passive:true});
  window.addEventListener('mousemove', function(){ heartbeat(false); }, {passive:true});
  window.addEventListener('keydown', function(){ heartbeat(false); });
  window.addEventListener('focus', function(){ heartbeat(true); });
  document.addEventListener('visibilitychange', function(){ if(!document.hidden) heartbeat(true); });
  window.addEventListener('load', function(){ trackScroll(); heartbeat(true); });
  setInterval(function(){ heartbeat(false); }, 30000);
})();
</script>

</body>
</html>

