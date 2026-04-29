<?php
// ============================================================
//  CotizaApp — api/cot_feedback.php
//  POST /api/cot-feedback (público — sin login)
//  El cliente califica la atención recibida (1-5 estrellas + comentario)
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$cot_id     = (int)($body['cotizacion_id'] ?? 0);
$stars      = (int)($body['stars'] ?? 0);
$comentario = trim((string)($body['comentario'] ?? ''));
$visitor_id = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)($body['visitor_id'] ?? '')), 0, 64);
$device_sig = substr(preg_replace('/[^a-fA-F0-9]/', '', (string)($body['device_sig'] ?? '')), 0, 20);

if (!$cot_id || $stars < 1 || $stars > 5) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Datos inválidos']);
    exit;
}

// Cargar cotización (de la empresa del host)
$cot = DB::row(
    "SELECT id, empresa_id, vendedor_id, usuario_id FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, EMPRESA_ID]
);
if (!$cot) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'Cotización no encontrada']);
    exit;
}

// Verificar que la empresa tenga el feedback activo
$activo = (int)DB::val("SELECT feedback_activo FROM empresas WHERE id = ?", [EMPRESA_ID]);
if (!$activo) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Feedback no disponible']);
    exit;
}

// Bloquear si la cotización ya fue calificada
$ya = DB::val("SELECT id FROM cot_feedbacks WHERE cotizacion_id = ?", [$cot_id]);
if ($ya) {
    echo json_encode(['ok'=>false,'error'=>'ya_calificado']);
    exit;
}

$ip = ip_real();
$ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

// Superadmin puede calificar (para testing/demo) — incluso desde dominios custom
// donde la cookie de sesión no viaja: detectamos por IP/visitor/device_sig
$es_superadmin_fb = Auth::es_superadmin();
if (!$es_superadmin_fb && $ip) {
    $es_superadmin_fb = (bool)DB::val(
        "SELECT 1 FROM radar_ips_internas ri JOIN usuarios u ON u.id = ri.usuario_id
          WHERE ri.ip = ? AND u.rol = 'superadmin' LIMIT 1",
        [$ip]
    );
}
if (!$es_superadmin_fb && $visitor_id) {
    $es_superadmin_fb = (bool)DB::val(
        "SELECT 1 FROM radar_visitors_internos rv JOIN usuarios u ON u.id = rv.usuario_id
          WHERE rv.visitor_id = ? AND u.rol = 'superadmin' LIMIT 1",
        [$visitor_id]
    );
}
if (!$es_superadmin_fb && $device_sig) {
    $es_superadmin_fb = (bool)DB::val(
        "SELECT 1 FROM user_sessions us JOIN usuarios u ON u.id = us.usuario_id
          WHERE us.device_sig = ? AND u.rol = 'superadmin'
            AND us.device_sig IS NOT NULL AND us.device_sig != '' LIMIT 1",
        [$device_sig]
    );
}

// Bloquear si IP o visitor_id están marcados como internos (asesores no califican)
// Superadmin se salta este check
if (!$es_superadmin_fb && $ip) {
    $ip_interna = (int)DB::val(
        "SELECT 1 FROM radar_ips_internas WHERE empresa_id = ? AND ip = ? LIMIT 1",
        [EMPRESA_ID, $ip]
    );
    if ($ip_interna) {
        http_response_code(403);
        echo json_encode(['ok'=>false,'error'=>'interno']);
        exit;
    }
}
if (!$es_superadmin_fb && $visitor_id) {
    $vid_interno = (int)DB::val(
        "SELECT 1 FROM radar_visitors_internos WHERE empresa_id = ? AND visitor_id = ? LIMIT 1",
        [EMPRESA_ID, $visitor_id]
    );
    if ($vid_interno) {
        http_response_code(403);
        echo json_encode(['ok'=>false,'error'=>'interno']);
        exit;
    }
}
if (!$es_superadmin_fb && $device_sig) {
    $dsig_interno = (int)DB::val(
        "SELECT 1 FROM user_sessions us
          JOIN usuarios u ON u.id = us.usuario_id
          WHERE us.device_sig = ? AND (u.empresa_id = ? OR u.rol = 'superadmin')
            AND us.device_sig IS NOT NULL AND us.device_sig != ''
          LIMIT 1",
        [$device_sig, EMPRESA_ID]
    );
    if ($dsig_interno) {
        http_response_code(403);
        echo json_encode(['ok'=>false,'error'=>'interno']);
        exit;
    }
}

$comentario_limpio = $comentario !== '' ? mb_substr($comentario, 0, 1000) : null;
$vendedor_id = $cot['vendedor_id'] ?? $cot['usuario_id'];

try {
    DB::execute(
        "INSERT INTO cot_feedbacks
            (cotizacion_id, empresa_id, vendedor_id, stars, comentario, visitor_id, device_sig, ip, ua)
         VALUES (?,?,?,?,?,?,?,?,?)",
        [$cot_id, EMPRESA_ID, $vendedor_id, $stars, $comentario_limpio,
         $visitor_id ?: null, $device_sig ?: null, $ip ?: null, $ua ?: null]
    );
} catch (Throwable $e) {
    // Race condition: alguien acaba de calificar. UNIQUE evita duplicados.
    echo json_encode(['ok'=>false,'error'=>'ya_calificado']);
    exit;
}

// Push notification al equipo de la empresa cuando se respeta el toggle
try {
    $ncfg_fb = notif_config(EMPRESA_ID);
    if (!empty($ncfg_fb['feedback_recibido'])) {
        // Cargar título de la cot y nombre del asesor para mensaje
        $info_fb = DB::row(
            "SELECT c.titulo, c.numero, COALESCE(u.nombre, '') AS asesor
             FROM cotizaciones c LEFT JOIN usuarios u ON u.id = ?
             WHERE c.id = ?",
            [(int)$vendedor_id, $cot_id]
        );
        $stars_str = str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
        $titulo_push = "Calificación {$stars_str}";
        $cuerpo_partes = [];
        if (!empty($info_fb['asesor'])) $cuerpo_partes[] = $info_fb['asesor'];
        $cuerpo_partes[] = $info_fb['titulo'] ?? ('cot ' . ($info_fb['numero'] ?? ''));
        if ($comentario_limpio) {
            $cuerpo_partes[] = '"' . mb_substr($comentario_limpio, 0, 80) . (mb_strlen($comentario_limpio) > 80 ? '…' : '') . '"';
        }
        $cuerpo_push = implode(' — ', $cuerpo_partes);

        PushNotification::enviar_a_empresa(
            EMPRESA_ID,
            'feedback_recibido',
            $titulo_push,
            $cuerpo_push,
            ['cotizacion_id' => $cot_id, 'stars' => $stars, 'url' => '/reportes?tab=feedback']
        );
    }
} catch (Throwable $e) { /* no bloquear */ }

echo json_encode(['ok' => true]);
