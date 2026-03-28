<?php
// ============================================================
//  CotizaApp — api/radar_feedback.php
//  POST /api/radar-feedback
//  Registra feedback del vendedor sobre señales calientes del Radar
// ============================================================

defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::logueado()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$cot_id = (int)($body['cotizacion_id'] ?? 0);
$tipo   = $body['tipo'] ?? '';

if (!$cot_id || !in_array($tipo, ['con_interes', 'sin_interes'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

$empresa_id = EMPRESA_ID;
$usuario_id = Auth::id();

// Verificar que la cotización existe y pertenece a la empresa
$cot = DB::row(
    "SELECT id, COALESCE(vendedor_id, usuario_id) AS vendedor_id
     FROM cotizaciones WHERE id=? AND empresa_id=?",
    [$cot_id, $empresa_id]
);
if (!$cot) {
    echo json_encode(['ok' => false, 'error' => 'Cotización no encontrada']);
    exit;
}

// Solo el vendedor asignado o admin pueden dar feedback
if ((int)$cot['vendedor_id'] !== $usuario_id && !Auth::es_admin()) {
    echo json_encode(['ok' => false, 'error' => 'Sin permiso']);
    exit;
}

// Upsert: insertar o actualizar feedback
DB::execute(
    "INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE tipo = VALUES(tipo), updated_at = NOW()",
    [$cot_id, $usuario_id, $empresa_id, $tipo]
);

echo json_encode(['ok' => true, 'tipo' => $tipo]);
