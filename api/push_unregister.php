<?php
// ============================================================
//  CotizaApp — api/push_unregister.php
//  POST /api/push/unregister  (requiere login)
//  Desactiva token de dispositivo
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

if (!Auth::logueado()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$token = trim($body['token'] ?? '');

if (!$token) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Token requerido']);
    exit;
}

try {
    PushNotification::desactivar_token($token);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => DEBUG ? $e->getMessage() : 'Error']);
}
