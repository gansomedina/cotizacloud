<?php
// ============================================================
//  CotizaApp — modules/config/guardar_costos_modo.php
//  POST /config/costos-modo
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

Auth::requerir_admin();
csrf_check();

$empresa_id = EMPRESA_ID;
$plan = trial_info($empresa_id);

if (!$plan['es_pro_o_superior']) json_error('Función no disponible en tu plan', 403);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$modo = $body['costos_modo'] ?? '';

// Pro: solo venta o empresa. Business: venta, empresa o ambos
$validos = $plan['es_business'] ? ['venta', 'empresa', 'ambos'] : ['venta', 'empresa'];

if (!in_array($modo, $validos, true)) {
    json_error('Modo no válido');
}

DB::execute(
    "UPDATE empresas SET costos_modo = ? WHERE id = ?",
    [$modo, $empresa_id]
);

json_ok(['costos_modo' => $modo]);
