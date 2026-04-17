<?php
// ============================================================
//  CotizaCloud — api/mp_webhook.php
//  POST /api/mp/webhook
//  Endpoint público para webhooks de MercadoPago
//  Valida HMAC, procesa eventos, idempotente por mp_payment_id
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json');

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
