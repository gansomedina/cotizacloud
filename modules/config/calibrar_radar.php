<?php
// ============================================================
//  CotizaApp — modules/config/calibrar_radar.php
//  POST /config/radar/calibrar (JSON)
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();
require_once MODULES_PATH . '/radar/Radar.php';

ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    $resultado = Radar::calibrar(EMPRESA_ID);
    if (!$resultado['ok']) {
        ob_end_clean();
        echo json_encode(['ok' => false, 'error' => $resultado['msg']]);
        exit;
    }

    // Recalcular scores con nueva calibración
    Radar::recalcular_empresa(EMPRESA_ID);

    ob_end_clean();
    echo json_encode([
        'ok'              => true,
        'tasa_base'       => $resultado['global_rate'] ?? 0,
        'total_cots'      => $resultado['total'] ?? 0,
        'ventas_cerradas' => $resultado['cierres'] ?? 0,
    ]);
} catch (Throwable $e) {
    ob_end_clean();
    echo json_encode(['ok' => false, 'error' => DEBUG ? $e->getMessage() : 'Error al calibrar']);
}
