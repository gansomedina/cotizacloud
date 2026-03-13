<?php
// ============================================================
//  CotizaApp — modules/config/calibrar_radar.php
//  POST /config/radar/calibrar (JSON)
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();
require_once MODULES_PATH . '/radar/Radar.php';

header('Content-Type: application/json');

$resultado = Radar::calibrar(EMPRESA_ID);
if (!$resultado['ok']) {
    echo json_encode(['ok' => false, 'error' => $resultado['msg']]);
    exit;
}

// Recalcular scores con nueva calibración
Radar::recalcular_empresa(EMPRESA_ID);

echo json_encode([
    'ok'            => true,
    'tasa_base'     => $resultado['tasa_base'],
    'total_cots'    => $resultado['total_cots'],
    'ventas_cerradas'=> $resultado['ventas_cerradas'],
]);
