<?php
// POST /api/track-tip — trackea expansión de "ver más" del diagnóstico
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::id()) { echo json_encode(['ok'=>false]); exit; }

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$expand = (int)($body['expand'] ?? 0);
if ($expand < 1 || $expand > 3) { echo json_encode(['ok'=>false]); exit; }

$tipo = 'tip_expand_' . $expand;

// Throttle: máximo 1 por tipo por día
$ya = DB::val(
    "SELECT id FROM actividad_log
     WHERE usuario_id=? AND tipo=? AND DATE(created_at) = CURDATE()
     LIMIT 1",
    [(int)Auth::id(), $tipo]
);

if (!$ya) {
    ActividadScore::registrar((int)Auth::id(), EMPRESA_ID, $tipo);
}

echo json_encode(['ok' => true]);
