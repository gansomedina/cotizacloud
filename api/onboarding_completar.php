<?php
// ============================================================
//  CotizaApp — api/onboarding_completar.php
//  POST /api/onboarding/completar   (requiere login)
//  Marca el wizard de bienvenida como visto para la empresa.
// ============================================================

defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::logueado()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}
if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit;
}

$eid = (int)(Auth::empresa()['id'] ?? 0);
$ok = false;
if ($eid) {
    try {
        DB::execute("UPDATE empresas SET onboarding_completo = 1 WHERE id = ?", [$eid]);
        $ok = true;
    } catch (\Throwable $e) {
        error_log('[onboarding] no se pudo marcar completo eid=' . $eid . ': ' . $e->getMessage());
    }
}
echo json_encode(['ok' => $ok]);
