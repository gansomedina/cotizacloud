<?php
// POST /api/track-tip — trackea:
//   {expand: 1|2|3}        → expansión "ver más" del diagnóstico
//   {tipo:'radar_why', cot_id: N} → click ❓ en Radar (1 vez por cot)
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::id()) { echo json_encode(['ok'=>false]); exit; }

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$uid = (int)Auth::id();

// ── Tipo 1: expansión del diagnóstico (tip_expand_1/2/3)
$expand = (int)($body['expand'] ?? 0);
if ($expand >= 1 && $expand <= 3) {
    $tipo = 'tip_expand_' . $expand;
    $ya = DB::val(
        "SELECT id FROM actividad_log
         WHERE usuario_id=? AND tipo=? AND DATE(created_at) = CURDATE() LIMIT 1",
        [$uid, $tipo]
    );
    if (!$ya) {
        ActividadScore::registrar($uid, EMPRESA_ID, $tipo);
    }
    echo json_encode(['ok' => true]);
    exit;
}

// ── Tipo 2: click ❓ en Radar (radar_why_click, 1 vez por cot)
$tipo_body = trim((string)($body['tipo'] ?? ''));
$cot_id = (int)($body['cot_id'] ?? 0);
if ($tipo_body === 'radar_why' && $cot_id > 0) {
    $ya = DB::val(
        "SELECT id FROM actividad_log
         WHERE usuario_id=? AND tipo='radar_why_click' AND ref_id=? LIMIT 1",
        [$uid, $cot_id]
    );
    if (!$ya) {
        ActividadScore::registrar($uid, EMPRESA_ID, 'radar_why_click', $cot_id);
    }
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false]);
