<?php
// ============================================================
//  CotizaApp — api/soporte.php
//  Chat de soporte casero. Acciones: enviar, cerrar, leido, responder.
//  POST /api/soporte   (requiere login)
// ============================================================

defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::logueado()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];

// CSRF
if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit;
}

$accion  = trim($body['accion'] ?? '');
$usuario = Auth::usuario();
$uid     = (int)$usuario['id'];
$eid     = (int)$usuario['empresa_id'];
$rol     = Auth::rol();

// ── Acción de agente: responder ─────────────────────────────
if ($accion === 'responder') {
    if ($rol !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Solo superadmin']);
        exit;
    }
    $conv_id = (int)($body['conversacion_id'] ?? 0);
    $cuerpo  = trim((string)($body['cuerpo'] ?? ''));
    if (!$conv_id || $cuerpo === '') {
        echo json_encode(['ok' => false, 'error' => 'Datos requeridos']); exit;
    }
    $cuerpo = mb_substr($cuerpo, 0, 4000);
    $conv = DB::row("SELECT id, empresa_id, usuario_id FROM soporte_conversaciones WHERE id = ?", [$conv_id]);
    if (!$conv) { echo json_encode(['ok' => false, 'error' => 'Conversación no existe']); exit; }

    DB::insert(
        "INSERT INTO soporte_mensajes (conversacion_id, autor, cuerpo) VALUES (?, 'agente', ?)",
        [$conv_id, $cuerpo]
    );
    DB::execute(
        "UPDATE soporte_conversaciones
         SET ultimo_mensaje_at = NOW(), no_leidos_usuario = no_leidos_usuario + 1,
             no_leidos_agente = 0, estado = 'abierta'
         WHERE id = ?",
        [$conv_id]
    );

    // Push solo al usuario que escribió
    try {
        PushNotification::enviar_a_usuario(
            (int)$conv['usuario_id'],
            'soporte_respuesta',
            'Soporte CotizaCloud',
            mb_substr($cuerpo, 0, 120),
            ['url' => '/dashboard', 'soporte' => 1]
        );
    } catch (\Throwable $e) {}

    echo json_encode(['ok' => true]);
    exit;
}

// ── A partir de aquí: acciones del usuario (admin) ──────────
if ($rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No disponible']);
    exit;
}

// Helper: obtener conversación abierta del usuario, o reabrir <24h, o crear.
function soporte_conversacion_activa(int $uid, int $eid, bool $crear): ?int {
    $conv = DB::row(
        "SELECT id FROM soporte_conversaciones
         WHERE usuario_id = ? AND estado = 'abierta'
         ORDER BY id DESC LIMIT 1",
        [$uid]
    );
    if ($conv) return (int)$conv['id'];

    // Reabrir la última cerrada si fue hace < 24h
    $reciente = DB::row(
        "SELECT id FROM soporte_conversaciones
         WHERE usuario_id = ? AND estado = 'cerrada'
           AND COALESCE(ultimo_mensaje_at, created_at) > DATE_SUB(NOW(), INTERVAL 24 HOUR)
         ORDER BY id DESC LIMIT 1",
        [$uid]
    );
    if ($reciente) {
        DB::execute("UPDATE soporte_conversaciones SET estado = 'abierta' WHERE id = ?", [(int)$reciente['id']]);
        return (int)$reciente['id'];
    }

    if (!$crear) return null;
    return DB::insert(
        "INSERT INTO soporte_conversaciones (empresa_id, usuario_id, estado, ultimo_mensaje_at)
         VALUES (?, ?, 'abierta', NOW())",
        [$eid, $uid]
    );
}

if ($accion === 'enviar') {
    $cuerpo = trim((string)($body['cuerpo'] ?? ''));
    if ($cuerpo === '') { echo json_encode(['ok' => false, 'error' => 'Mensaje vacío']); exit; }
    $cuerpo = mb_substr($cuerpo, 0, 4000);

    // Rate limit: máx 12 mensajes/min por usuario
    $recientes = (int)DB::val(
        "SELECT COUNT(*) FROM soporte_mensajes m
         JOIN soporte_conversaciones c ON c.id = m.conversacion_id
         WHERE c.usuario_id = ? AND m.autor = 'usuario'
           AND m.created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
        [$uid]
    );
    if ($recientes >= 12) {
        echo json_encode(['ok' => false, 'error' => 'Demasiados mensajes, espera un momento']); exit;
    }

    // Lock por usuario para evitar crear 2 conversaciones abiertas si llegan
    // dos envíos casi simultáneos (otra pestaña/dispositivo del mismo usuario).
    $lock_key = 'soporte_conv_' . $uid;
    DB::val("SELECT GET_LOCK(?, 5)", [$lock_key]);
    try {
        $conv_id = soporte_conversacion_activa($uid, $eid, true);
        $msg_id = DB::insert(
            "INSERT INTO soporte_mensajes (conversacion_id, autor, cuerpo) VALUES (?, 'usuario', ?)",
            [$conv_id, $cuerpo]
        );
        DB::execute(
            "UPDATE soporte_conversaciones
             SET ultimo_mensaje_at = NOW(), no_leidos_agente = no_leidos_agente + 1, estado = 'abierta'
             WHERE id = ?",
            [$conv_id]
        );
    } finally {
        DB::val("SELECT RELEASE_LOCK(?)", [$lock_key]);
    }

    // Aviso al superadmin: push + email
    // Sanitizar saltos de línea para evitar inyección de headers en el mail.
    $ref = ($usuario['nombre'] ?? 'Usuario') . ' · ' . (Auth::empresa()['nombre'] ?? '');
    $ref = trim(str_replace(["\r", "\n"], ' ', $ref));
    try {
        PushNotification::enviar_a_superadmin(
            'soporte_mensaje',
            'Nuevo mensaje de soporte',
            mb_substr($ref . ': ' . $cuerpo, 0, 150),
            ['url' => '/superadmin/soporte', 'conversacion_id' => $conv_id]
        );
    } catch (\Throwable $e) {}
    if (defined('SUPERADMIN_EMAIL') && SUPERADMIN_EMAIL) {
        try {
            @mail(
                SUPERADMIN_EMAIL,
                'Soporte CotizaCloud — ' . $ref,
                "Mensaje:\n{$cuerpo}\n\nResponder: https://cotiza.cloud/superadmin/soporte",
                "From: noreply@cotiza.cloud\r\nContent-Type: text/plain; charset=utf-8"
            );
        } catch (\Throwable $e) {}
    }

    echo json_encode(['ok' => true, 'conversacion_id' => $conv_id, 'mensaje_id' => $msg_id]);
    exit;
}

if ($accion === 'cerrar') {
    DB::execute(
        "UPDATE soporte_conversaciones SET estado = 'cerrada' WHERE usuario_id = ? AND estado = 'abierta'",
        [$uid]
    );
    echo json_encode(['ok' => true]);
    exit;
}

if ($accion === 'leido') {
    DB::execute(
        "UPDATE soporte_conversaciones SET no_leidos_usuario = 0 WHERE usuario_id = ? AND estado = 'abierta'",
        [$uid]
    );
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Acción inválida']);
