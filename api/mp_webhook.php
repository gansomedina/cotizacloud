<?php
// ============================================================
//  CotizaCloud — api/mp_webhook.php
//  POST /api/mp/webhook
//  Endpoint público para webhooks de MercadoPago
//  Valida HMAC, procesa eventos, idempotente por mp_payment_id
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json');

// DEBUG: log todas las peticiones entrantes (método, headers, body, IP)
$_debug_headers = [];
foreach ($_SERVER as $k => $v) {
    if (strpos($k, 'HTTP_') === 0 || in_array($k, ['CONTENT_TYPE','CONTENT_LENGTH','REQUEST_METHOD','REQUEST_URI','REMOTE_ADDR','HTTPS','SERVER_PROTOCOL'], true)) {
        $_debug_headers[$k] = $v;
    }
}
$_debug_body = file_get_contents('php://input');
error_log('[MP Webhook IN] ' . json_encode([
    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    'ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
    'ua'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'headers'=> $_debug_headers,
    'body'   => $_debug_body,
]));

// Health check — permite que MP y tú verifiquen que el endpoint existe
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);
    echo json_encode(['ok' => true, 'endpoint' => 'mp_webhook', 'method' => 'POST expected']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$input = file_get_contents('php://input');
$body  = json_decode($input, true);

if (!$body || empty($body['type'])) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'note' => 'ignored']);
    exit;
}

if (!MercadoPago::validarWebhook()) {
    error_log('[MP Webhook] Firma inválida: ' . ($_SERVER['HTTP_X_SIGNATURE'] ?? 'none'));
    http_response_code(200);
    echo json_encode(['ok' => true, 'note' => 'signature_skipped']);
    exit;
}

try {
    $result = MercadoPago::procesarWebhook($body);

    error_log('[MP Webhook] ' . json_encode([
        'type'   => $body['type'] ?? '',
        'action' => $body['action'] ?? '',
        'result' => $result,
    ]));

    http_response_code(200);
    echo json_encode(['ok' => true, 'result' => $result]);
} catch (Throwable $e) {
    error_log('[MP Webhook] Error: ' . $e->getMessage());
    http_response_code(200);
    echo json_encode(['ok' => true, 'error' => 'internal']);
}
