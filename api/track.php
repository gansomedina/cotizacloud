<?php
// ============================================================
//  CotizaApp — api/track.php
//  POST /api/track  (sin login requerido — llamado por sendBeacon)
//  Portado fielmente de ontime-quote-events.php (mu-plugin WP)
//  Filtro de internos: visitor_id (cz_vid) > usuario_logueado (sesión)
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
$device_sig = substr(preg_replace('/[^a-zA-Z0-9|\/\-_., ():]/', '', (string)($data['device_sig'] ?? '')), 0, 120);
$session_id = substr(preg_replace('/[^a-zA-Z0-9\-]/',  '', (string)($data['session_id'] ?? '')), 0, 36);
$page_id    = substr(preg_replace('/[^a-zA-Z0-9\-]/',  '', (string)($data['page_id']    ?? '')), 0, 36);
$max_scroll = min(100, max(0, (int)($data['max_scroll'] ?? 0)));
$visible_ms = min(600000, max(0, (int)($data['visible_ms'] ?? 0)));
$open_ms    = min(600000, max(0, (int)($data['open_ms']    ?? 0)));

if (!$cot_id || !$tipo) exit;

$tipos_validos = [
    'quote_open','quote_close','quote_scroll',
    'coupon_validate_click','coupon_valid','coupon_invalid',
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

// ================================================================
//  FILTROS DE CALIDAD — Portado fielmente de ontime-quote-events.php
//  Orden: bot_ip → bot_ua → visitor_interno → usuario_logueado
// ================================================================

// ── Anti-bot por IP prefix ───────────────────────────────────────
if ($rcfg['filtrar_bots'] ?? true) {
    foreach (Radar::BOT_IP as $prefix) {
        if (str_starts_with($ip, $prefix)) exit;
    }
}

// ── Anti-bot por User-Agent ──────────────────────────────────────
if (($rcfg['filtrar_bots'] ?? true) && es_bot($ua)) exit;

// ── Pre-calcular flags de interno ────────────────────────────────
$es_superadmin = Auth::id() !== null && (Auth::usuario()['rol'] ?? '') === 'superadmin';
$es_usuario_interno = (Auth::id() !== null && (int)(Auth::empresa()['id'] ?? 0) === $empresa_id) || $es_superadmin;
$es_visitor_interno = ($visitor_id !== '' && ($rcfg['excluir_internos'] ?? true))
    ? Radar::es_visitor_interno($empresa_id, $visitor_id)
    : false;

// CAPA 1 — visitor_id ya conocido como interno (consulta más barata, primera)
// Si el UUID del navegador ya está marcado como interno → descartar inmediatamente
if ($es_visitor_interno) exit;

// CAPA 2 — Usuario logueado de la empresa
// Certeza máxima: aprender visitor_id + ip + ua, luego descartar
if ($es_usuario_interno) {
    if ($visitor_id !== '') {
        Radar::marcar_visitor_interno($empresa_id, $visitor_id, 'internal_user', (int)Auth::id(), $ip, $ua);
    }
    Radar::aprender_ip_radar($empresa_id, $ip);
    // Aprender device_sig SOLO en la sesión actual del navegador (identificada por token).
    // No usar "más reciente" porque puede ser de otro dispositivo del mismo usuario.
    if ($device_sig !== '' && Auth::id()) {
        $tok_track = $_COOKIE[SESSION_NAME] ?? '';
        if ($tok_track !== '') {
            try { DB::execute("UPDATE user_sessions SET device_sig=COALESCE(device_sig,?) WHERE token=? AND usuario_id=?", [$device_sig, $tok_track, (int)Auth::id()]); } catch (Throwable $e) {}
        }
    }
    exit;
}

// Capa 2.5 eliminada: device_sig NO debe usarse para identificar asesores.
// Colisiona entre dispositivos iguales (iPhones del mismo modelo = mismo dsig).
// Causaba que eventos de clientes reales se descartaran silenciosamente
// cuando su device_sig coincidía con un asesor → sesión sin eventos →
// ghost cleanup la borraba → visita real perdida.
// Las capas 1, 2 y 3 cubren detección de internos sin ese riesgo.

// CAPA 3 (IP interna) eliminada: las IPs de carrier rotan — una IP que fue
// del asesor pasa a un cliente real, descartando su visita y marcándolo
// interno 365 días. El asesor se detecta por Capa 1 (cz_vid) y Capa 2 (sesión).

// ── Pasa todos los filtros → evento de un cliente real ───────────

// Estado válido
if (!in_array($cot['estado'], ['enviada','vista','aceptada','rechazada'], true)) exit;

// Sesión activa (ventana de deduplicación)
$dedupe_min = ($rcfg['deduplicar_30min'] ?? true) ? 30 : 60;
$sess = null;
if ($visitor_id !== '') {
    $sess = DB::row(
        "SELECT id FROM quote_sessions
         WHERE cotizacion_id=? AND visitor_id=? AND activa=1
           AND updated_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
         ORDER BY updated_at DESC LIMIT 1",
        [$cot_id, $visitor_id, $dedupe_min]
    );
}
if (!$sess) {
    $sess = DB::row(
        "SELECT id FROM quote_sessions
         WHERE cotizacion_id=? AND ip=? AND activa=1
           AND updated_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
         ORDER BY updated_at DESC LIMIT 1",
        [$cot_id, $ip, $dedupe_min]
    );
}

$ts_now = time();

if (!$sess) {
    try {
        $sess_id = DB::insert(
            "INSERT INTO quote_sessions (cotizacion_id, ip, user_agent, visitor_id, device_sig, session_id, page_id, activa, scroll_max, visible_ms)
             VALUES (?,?,?,?,?,?,?,1,?,?)",
            [$cot_id, $ip, substr($ua,0,300), $visitor_id ?: null, $device_sig ?: null, $session_id ?: null, $page_id ?: null, $max_scroll, $visible_ms]
        );
    } catch (Throwable $e) {
        error_log("[track.php] quote_sessions INSERT con device_sig falló: " . $e->getMessage() . " — cot={$cot_id} vid={$visitor_id} ip={$ip}");
        $sess_id = DB::insert(
            "INSERT INTO quote_sessions (cotizacion_id, ip, user_agent, visitor_id, session_id, page_id, activa, scroll_max, visible_ms)
             VALUES (?,?,?,?,?,?,1,?,?)",
            [$cot_id, $ip, substr($ua,0,300), $visitor_id ?: null, $session_id ?: null, $page_id ?: null, $max_scroll, $visible_ms]
        );
    }
} else {
    $sess_id = (int)$sess['id'];
    try {
        DB::execute(
            "UPDATE quote_sessions SET updated_at=NOW(),
                visitor_id=COALESCE(?, visitor_id),
                device_sig=COALESCE(?, device_sig),
                scroll_max=GREATEST(COALESCE(scroll_max,0),?),
                visible_ms=GREATEST(COALESCE(visible_ms,0),?)
             WHERE id=?",
            [$visitor_id ?: null, $device_sig ?: null, $max_scroll, $visible_ms, $sess_id]
        );
    } catch (Throwable $e) {
        error_log("[track.php] quote_sessions UPDATE con device_sig falló: " . $e->getMessage() . " — sess_id={$sess_id} vid={$visitor_id}");
        DB::execute(
            "UPDATE quote_sessions SET updated_at=NOW(),
                visitor_id=COALESCE(?, visitor_id),
                scroll_max=GREATEST(COALESCE(scroll_max,0),?),
                visible_ms=GREATEST(COALESCE(visible_ms,0),?)
             WHERE id=?",
            [$visitor_id ?: null, $max_scroll, $visible_ms, $sess_id]
        );
    }
}

// Registrar evento
try {
    DB::execute(
        "INSERT INTO quote_events (cotizacion_id, session_id, visitor_id, device_sig, page_id, tipo, max_scroll, visible_ms, open_ms, ua, ip, ts_unix)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
        [$cot_id, $session_id ?: null, $visitor_id ?: null, $device_sig ?: null, $page_id ?: null, $tipo, $max_scroll, $visible_ms, $open_ms, substr($ua,0,255), $ip, $ts_now]
    );
} catch (Throwable $e) {
    error_log("[track.php] quote_events INSERT con device_sig falló: " . $e->getMessage() . " — cot={$cot_id} vid={$visitor_id} ip={$ip}");
    try {
        DB::execute(
            "INSERT INTO quote_events (cotizacion_id, session_id, visitor_id, page_id, tipo, max_scroll, visible_ms, open_ms, ua, ip, ts_unix)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            [$cot_id, $session_id ?: null, $visitor_id ?: null, $page_id ?: null, $tipo, $max_scroll, $visible_ms, $open_ms, substr($ua,0,255), $ip, $ts_now]
        );
    } catch (Throwable $e2) {
        error_log("[track.php] quote_events INSERT fallback también falló: " . $e2->getMessage() . " — cot={$cot_id} vid={$visitor_id} ip={$ip}");
        exit;
    }
}

// Marcar como vista — solo cambio de estado, NO incrementar visitas
// El contador visitas lo maneja cotizacion.php server-side con deduplicación correcta
// Si llegó quote_open pero la página no incrementó (edge case: JS muy rápido), actualizar ultima_vista_at
if ($cot['estado'] === 'enviada' && $tipo === 'quote_open') {
    DB::execute("UPDATE cotizaciones SET estado='vista', vista_at=NOW(), ultima_vista_at=NOW() WHERE id=? AND estado='enviada'", [$cot_id]);
    $cot['estado'] = 'vista';
}

// Update ultima_vista_at en cualquier evento con engagement real, indep.
// del cambio de estado. Antes solo se actualizaba cuando estado='enviada';
// si la cot ya estaba en 'vista' y el cliente volvía a scrollear u otro
// evento real, ultima_vista_at quedaba desactualizado.
// Throttle 1 min para no spamear UPDATEs con cada quote_scroll/section_view.
if (in_array($cot['estado'], ['enviada','vista'], true)
    && ($max_scroll > 0 || $visible_ms >= 200)) {
    DB::execute(
        "UPDATE cotizaciones SET ultima_vista_at = NOW()
         WHERE id = ?
           AND (ultima_vista_at IS NULL OR ultima_vista_at < DATE_SUB(NOW(), INTERVAL 1 MINUTE))",
        [$cot_id]
    );
}

// Acciones especiales
switch ($tipo) {
    case 'accept_confirm':
    case 'reject_confirm':
        // NO-OP a propósito (seguridad, auditoría 17-jul). Este beacon NUNCA
        // debe mutar dinero/estado: llega con un cotizacion_id ENTERO sin token
        // ni slug ni binding de host — enumerando IDs cualquiera aceptaba/
        // rechazaba cotizaciones ajenas, y creaba la venta con el total viejo
        // (sin DI, sin bloquear cupón, sin checar suspendida), pisando lo que
        // quote_action ya había cobrado bien (carrera). El flujo REAL de
        // aceptar/rechazar es api/quote_action.php (recalcula server-side,
        // aplica el DI congelado, valida cupón, exige nombre). El slug siempre
        // lo llama ANTES de este beacon (doAcc/doRej en cotizacion.php); este
        // evento solo se conserva para la analítica del embudo (ya se registró
        // como quote_event arriba). NO restaurar la creación de venta aquí.
        break;
}

// ── Limpieza de sesiones fantasma ───────────────────────────────────
// Detecta previews modernos (WhatsApp/iMessage/Android Chrome) que
// ejecutan JS por <100ms y dejan sesiones con engagement mínimo.
//
// Distinguimos 3 tipos por los valores de quote_sessions:
//   - visible_ms IS NULL  → cliente con adblocker (track.php nunca tocó
//     la sesión). MANTENER: server-side ya registró visita legítima.
//   - visible_ms < 200 AND scroll_max = 0 → preview moderno con JS
//     limitado que actualizó la sesión con engagement mínimo. BORRAR.
//   - scroll_max > 0 OR visible_ms >= 200 → cliente real. MANTENER.
//
// Verificamos contra la sesión misma (no contra eventos por vid) porque
// un mismo visitor_id puede tener múltiples quote_sessions (cliente
// abre la cot varias veces fuera de la ventana de dedupe 30min). Eventos
// con engagement de OTRA sesión no deben bloquear el cleanup de esta.
//
// Después del DELETE, recalcular visitas/vista_at/ultima_vista_at desde
// quote_sessions reales para que el header del editor coincida con el
// historial visible.
try {
    $ghosts = DB::query(
        "SELECT id FROM quote_sessions
         WHERE cotizacion_id = ?
           AND scroll_max = 0
           AND visible_ms IS NOT NULL
           AND visible_ms < 200
           AND created_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE)",
        [$cot_id]
    );
    if ($ghosts) {
        $ghost_ids = array_column($ghosts, 'id');
        $placeholders = implode(',', array_fill(0, count($ghost_ids), '?'));
        DB::execute("DELETE FROM quote_sessions WHERE id IN ($placeholders)", $ghost_ids);

        DB::execute(
            "UPDATE cotizaciones c SET
                c.visitas         = (SELECT COUNT(*)           FROM quote_sessions qs WHERE qs.cotizacion_id = c.id AND qs.es_interno = 0),
                c.vista_at        = (SELECT MIN(qs.created_at) FROM quote_sessions qs WHERE qs.cotizacion_id = c.id AND qs.es_interno = 0),
                c.ultima_vista_at = (SELECT MAX(qs.created_at) FROM quote_sessions qs WHERE qs.cotizacion_id = c.id AND qs.es_interno = 0)
             WHERE c.id = ?",
            [$cot_id]
        );
    }
} catch (Throwable $e) {}


// Recalcular Radar
if (in_array($cot['estado'], ['enviada','vista'], true)) {
    try { Radar::recalcular($cot_id, $empresa_id); } catch (Throwable $e) {}
}
exit;
