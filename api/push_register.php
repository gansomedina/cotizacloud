<?php
// ============================================================
//  CotizaApp — api/push_register.php
//  POST /api/push/register   (requiere login)
//  Registra token de dispositivo para push notifications
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

// Requiere login
if (!Auth::logueado()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Payload inválido']);
    exit;
}

$token      = trim($body['token'] ?? '');
$plataforma = trim($body['plataforma'] ?? 'ios');

if (!$token) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Token requerido']);
    exit;
}

if (!in_array($plataforma, ['ios', 'android', 'web'])) {
    $plataforma = 'ios';
}

$usuario = Auth::usuario();

try {
    PushNotification::registrar_token(
        (int)$usuario['empresa_id'],
        (int)$usuario['id'],
        $token,
        $plataforma
    );
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    if (DEBUG) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Error al registrar']);
    }
}
