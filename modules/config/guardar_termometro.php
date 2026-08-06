<?php
defined('COTIZAAPP') or die;
Auth::requerir_admin();
header('Content-Type: application/json; charset=utf-8');
csrf_check();

// Paquetes 23-jul: el termómetro es BUSINESS — sin este check, cualquier admin
// Free/Pro encendía el toggle y el dashboard lo mostraba (puerta trasera).
if (empty(trial_info(EMPRESA_ID)['es_business'])) {
    echo json_encode(['ok' => false, 'error' => 'El Termómetro es exclusivo del plan Business']); exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$visible = !empty($body['termometro_visible']) ? 1 : 0;

DB::execute("UPDATE empresas SET termometro_visible=? WHERE id=?", [$visible, EMPRESA_ID]);

echo json_encode(['ok' => true]);
