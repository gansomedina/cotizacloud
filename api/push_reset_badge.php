<?php
// ============================================================
//  CotizaApp — api/push_reset_badge.php
//  POST /api/push/reset-badge — Resetear badge count al abrir la app
// ============================================================

defined('COTIZAAPP') or die;

if (!Auth::id()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once ROOT_PATH . '/core/PushNotification.php';
PushNotification::reset_badge((int)Auth::id());

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
