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
//  Orden: bot_ip → bot_ua → visitor_interno → usuario_logueado → ip_interna
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
$es_ip_interna = ($rcfg['excluir_internos'] ?? true)
    ? (bool)DB::val("SELECT 1 FROM radar_ips_internas WHERE empresa_id=? AND ip=? LIMIT 1", [$empresa_id, $ip])
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
    // También aprender la IP de este acceso como interna
    Radar::aprender_ip_radar($empresa_id, $ip);
    exit;
}

// CAPA 3 — IP interna (aunque no esté logueado — home office, revisar cotiz sin login)
// Aprender el visitor_id de este navegador para futuras visitas, aunque no esté logueado
if ($es_ip_interna) {
    if ($visitor_id !== '') {
        Radar::marcar_visitor_interno($empresa_id, $visitor_id, 'internal_ip', null, $ip, $ua);
    }
    exit;
}

// ── Pasa todos los filtros → evento de un cliente real ───────────

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

// Marcar como vista — solo cambio de estado, NO incrementar visitas
// El contador visitas lo maneja cotizacion.php server-side con deduplicación correcta
// Si llegó quote_open pero la página no incrementó (edge case: JS muy rápido), actualizar ultima_vista_at
if ($cot['estado'] === 'enviada' && $tipo === 'quote_open') {
    DB::execute("UPDATE cotizaciones SET estado='vista', vista_at=NOW(), ultima_vista_at=NOW() WHERE id=? AND estado='enviada'", [$cot_id]);
    $cot['estado'] = 'vista';
}

// Acciones especiales
switch ($tipo) {
    case 'accept_confirm':
        // accept_confirm llega de track.php solo como fallback del evento JS
        // El flujo principal de aceptación es quote_action.php que ya crea la venta.
        // Este caso solo actualiza el estado si aún no está aceptada.
        if (in_array($cot['estado'], ['enviada','vista'], true)) {
            try {
                DB::beginTransaction();
                DB::execute("UPDATE cotizaciones SET estado='aceptada', aceptada_at=NOW() WHERE id=? AND estado IN ('enviada','vista')", [$cot_id]);
                // Crear venta si no existe
                $venta_ok = DB::val("SELECT id FROM ventas WHERE cotizacion_id=? LIMIT 1", [$cot_id]);
                if (!$venta_ok) {
                    $empresa_row = DB::row("SELECT vta_prefijo FROM empresas WHERE id=? LIMIT 1", [$empresa_id]);
                    $num_vta     = DB::siguiente_folio($empresa_id, 'VTA', $empresa_row['vta_prefijo'] ?? 'VTA');
                    $cot_full    = DB::row("SELECT titulo, cliente_id, total FROM cotizaciones WHERE id=?", [$cot_id]);
                    DB::insert(
                        "INSERT INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at)
                         VALUES (?,?,?,NULL,?,?,?,?,?,0,?,'pendiente',NOW())",
                        [
                            $empresa_id, $cot_id, $cot_full['cliente_id'],
                            $num_vta, $cot_full['titulo'],
                            slug_unico($cot_full['titulo'], 'ventas', 'slug', $empresa_id),
                            generar_token(32),
                            $cot_full['total'], $cot_full['total'],
                        ]
                    );
                }
                DB::execute("INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, detalle) VALUES (?,NULL,'aceptada_cliente','Aceptada desde vista pública')", [$cot_id]);
                DB::commit();
            } catch (Throwable $ex) { DB::rollback(); }
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

// ── Limpieza de sesiones fantasma (bots de preview) ────────────────
// Bots de link-preview (WhatsApp, iMessage, Teams) hacen fetch server-side
// pero NO ejecutan JS. Resultado: sesión con scroll=0, visible_ms=0, sin eventos.
// Al recibir un evento JS real, limpiar esas sesiones huérfanas y ajustar visitas.
try {
    $ghosts = DB::query(
        "SELECT qs.id
         FROM quote_sessions qs
         LEFT JOIN quote_events qe ON qe.cotizacion_id = qs.cotizacion_id AND qe.ip = qs.ip
         WHERE qs.cotizacion_id = ?
           AND COALESCE(qs.scroll_max, 0) = 0
           AND COALESCE(qs.visible_ms, 0) = 0
           AND qs.created_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE)
           AND qe.id IS NULL",
        [$cot_id]
    );
    if ($ghosts) {
        $ghost_ids = array_column($ghosts, 'id');
        $placeholders = implode(',', array_fill(0, count($ghost_ids), '?'));
        DB::execute("DELETE FROM quote_sessions WHERE id IN ($placeholders)", $ghost_ids);
        // Ajustar contador de visitas (CAST evita underflow en BIGINT UNSIGNED)
        DB::execute(
            "UPDATE cotizaciones SET visitas = CASE WHEN visitas >= ? THEN visitas - ? ELSE 0 END WHERE id = ?",
            [count($ghost_ids), count($ghost_ids), $cot_id]
        );
    }
} catch (Throwable $e) {}

// Recalcular Radar
if (in_array($cot['estado'], ['enviada','vista'], true)) {
    try { Radar::recalcular($cot_id, $empresa_id); } catch (Throwable $e) {}
}
exit;
