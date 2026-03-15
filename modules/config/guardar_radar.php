<?php
// ============================================================
//  CotizaApp — modules/config/guardar_radar.php
//  POST /config/radar (JSON)
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();
require_once MODULES_PATH . '/radar/Radar.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { echo json_encode(['ok'=>false,'error'=>'Payload inválido']); exit; }

$sens  = $body['sensibilidad'] ?? $body['modo'] ?? 'medio';
if (!in_array($sens, ['agresivo','medio','ligero'])) $sens = 'medio';

$config = [
    'sensibilidad'     => $sens,
    'calibracion_auto' => (bool)($body['calibracion_auto'] ?? $body['auto_calibrar'] ?? true),
    'excluir_internos' => (bool)($body['excluir_internos'] ?? true),
    'filtrar_bots'     => (bool)($body['filtrar_bots']     ?? true),
    'deduplicar_30min' => (bool)($body['deduplicar_30min'] ?? true),
];

DB::execute(
    "UPDATE empresas SET radar_config=? WHERE id=?",
    [json_encode($config), EMPRESA_ID]
);

// Forzar recálculo con nueva sensibilidad
Radar::recalcular_empresa(EMPRESA_ID);

echo json_encode(['ok'=>true]);
