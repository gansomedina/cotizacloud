<?php
// ============================================================
//  CotizaApp — api/track.php
//  POST /api/track  (sin login requerido — llamado por sendBeacon)
//  Portado fielmente de ontime-quote-events.php (mu-plugin WP)
//  3 capas de filtro de internos: usuario_logueado > visitor_id > IP
// ============================================================

defined('COTIZAAPP') or die;

// Respuesta vacía siempre — nunca romper el beacon del cliente
http_response_code(204);
header('Content-Length: 0');

$raw  = file_get_contents('php://input');
$data = $raw ? json_decode($raw, true) : [];
if (empty($data) || empty($data['cotizacion_id'])) exit;

$cot_id     = (int)$data['cotizacion_id'];
$tipo       = preg_replace('/[^a-z0-9_]/', '', strtolower((string)($data['tipo'] ?? '')));
$visitor_id = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)($data['visitor_id'] ?? '')), 0, 64);
$session_id = substr(preg_replace('/[^a-zA-Z0-9\-]/',  '', (string)($data['session_id'] ?? '')), 0, 36);
$page_id    = substr(preg_replace('/[^a-zA-Z0-9\-]/',  '', (string)($data['page_id']    ?? '')), 0, 36);
$max_scroll = min(100, max(0, (int)($data['max_scroll'] ?? 0)));
$visible_ms = max(0, (int)($data['visible_ms'] ?? 0));
$open_ms    = max(0, (int)($data['open_ms']    ?? 0));

if (!$cot_id || !$tipo) exit;

$tipos_validos = [
    'quote_open','quote_close','quote_scroll',
    'coupon_validate_click',
    'section_view_totals','section_revisit_totals',
    'quote_price_review_loop','promo_timer_present',
    'accept_open','accept_confirm',
    'reject_open','reject_confirm',
    'tab_d','tab_t','print','share_wa',
];
if (!in_array($tipo, $tipos_validos, true)) exit;

$cot = DB::row("SELECT id, empresa_id, estado FROM cotizaciones WHERE id=?", [$cot_id]);
if (!$cot) exit;
$empresa_id = (int)$cot['empresa_id'];

require_once MODULES_PATH . '/radar/Radar.php';
$rcfg = Radar::config($empresa_id);

$ip = ip_real();
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Bot por IP prefix
if ($rcfg['filtrar_bots'] ?? true) {
    foreach (Radar::BOT_IP as $prefix) {
        if (str_starts_with($ip, $prefix)) exit;
    }
}
// Bot por UA
if (($rcfg['filtrar_bots'] ?? true) && es_bot($ua)) exit;

// CAPA 1 — Usuario logueado de la empresa → aprender visitor_id y salir
if (Auth::id() !== null && Auth::empresa() === $empresa_id) {
    if ($visitor_id !== '') {
        Radar::marcar_visitor_interno($empresa_id, $visitor_id, 'internal_user', Auth::id(), $ip);
    }
    exit;
}

// CAPA 2 — visitor_id ya conocido como interno
if (($rcfg['excluir_internos'] ?? true) && $visitor_id !== '') {
    if (Radar::es_visitor_interno($empresa_id, $visitor_id)) exit;
}

// CAPA 3 — IP interna → aprender visitor_id y salir
if ($rcfg['excluir_internos'] ?? true) {
    if ((bool)DB::val("SELECT 1 FROM radar_ips_internas WHERE empresa_id=? AND ip=? LIMIT 1", [$empresa_id, $ip])) {
        if ($visitor_id !== '') Radar::marcar_visitor_interno($empresa_id, $visitor_id, 'internal_ip', null, $ip);
        exit;
    }
}

// Estado válido
if (!in_array($cot['estado'], ['enviada','vista','aceptada','rechazada'], true)) exit;

// Sesión activa (ventana de deduplicación)
$dedupe_min = ($rcfg['deduplicar_30min'] ?? true) ? 30 : 60;
$sess = DB::row(
    "SELECT id FROM quote_sessions
     WHERE cotizacion_id=? AND ip=? AND activa=1
       AND updated_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
     ORDER BY updated_at DESC LIMIT 1",
    [$cot_id, $ip, $dedupe_min]
);

$ts_now = time();

if (!$sess) {
    $sess_id = DB::insert(
        "INSERT INTO quote_sessions (cotizacion_id, ip, user_agent, visitor_id, session_id, page_id, activa, scroll_max, visible_ms)
         VALUES (?,?,?,?,?,?,1,?,?)",
        [$cot_id, $ip, substr($ua,0,300), $visitor_id ?: null, $session_id ?: null, $page_id ?: null, $max_scroll, $visible_ms]
    );
} else {
    $sess_id = (int)$sess['id'];
    DB::execute(
        "UPDATE quote_sessions SET updated_at=NOW(),
            scroll_max=GREATEST(COALESCE(scroll_max,0),?),
            visible_ms=GREATEST(COALESCE(visible_ms,0),?)
         WHERE id=?",
        [$max_scroll, $visible_ms, $sess_id]
    );
}

// Registrar evento
try {
    DB::execute(
        "INSERT INTO quote_events (cotizacion_id, session_id, visitor_id, page_id, tipo, max_scroll, visible_ms, open_ms, ua, ip, ts_unix)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        [$cot_id, $session_id ?: null, $visitor_id ?: null, $page_id ?: null, $tipo, $max_scroll, $visible_ms, $open_ms, substr($ua,0,255), $ip, $ts_now]
    );
} catch (Throwable $e) { exit; }

// Marcar como vista
if ($cot['estado'] === 'enviada' && $tipo === 'quote_open') {
    DB::execute("UPDATE cotizaciones SET estado='vista', vista_at=NOW() WHERE id=? AND estado='enviada'", [$cot_id]);
    $cot['estado'] = 'vista';
}
if ($tipo === 'quote_open') {
    DB::execute("UPDATE cotizaciones SET ultima_vista_at=NOW(), visitas=visitas+1 WHERE id=?", [$cot_id]);
}

// Acciones especiales
switch ($tipo) {
    case 'accept_confirm':
        if (in_array($cot['estado'], ['enviada','vista'], true)) {
            DB::execute("UPDATE cotizaciones SET estado='aceptada', aceptada_at=NOW() WHERE id=? AND estado IN ('enviada','vista')", [$cot_id]);
            DB::execute("INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, detalle) VALUES (?,NULL,'aceptada_cliente','Aceptada desde vista pública')", [$cot_id]);
        }
        break;
    case 'reject_confirm':
        $motivo = substr(preg_replace('/[^\w\s\-\.,áéíóúüñÁÉÍÓÚÜÑ]/u', '', (string)($data['motivo'] ?? '')), 0, 200);
        if (in_array($cot['estado'], ['enviada','vista','aceptada'], true)) {
            DB::execute("UPDATE cotizaciones SET estado='rechazada', rechazada_at=NOW() WHERE id=? AND estado IN ('enviada','vista','aceptada')", [$cot_id]);
            DB::execute("INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, detalle) VALUES (?,NULL,'rechazada_cliente',?)", [$cot_id, 'Rechazada desde vista pública: '.$motivo]);
        }
        break;
}

// Recalcular Radar
if (in_array($cot['estado'], ['enviada','vista'], true)) {
    try { Radar::recalcular($cot_id, $empresa_id); } catch (Throwable $e) {}
}
exit;
