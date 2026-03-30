<?php
// ============================================================
//  CotizaApp — modules/config/guardar_historial.php
//  POST /config/historial
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

Auth::requerir_admin();
csrf_check();

$empresa_id = EMPRESA_ID;
$plan = trial_info($empresa_id);
if (!$plan['es_business']) json_error('Función solo disponible en plan Business', 403);

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$anio = (int)($body['anio'] ?? 0);
$mes  = (int)($body['mes'] ?? 0);

if ($anio < 2018 || $anio > 2030) json_error('Año inválido (2018-2030)');
if ($mes < 1 || $mes > 12) json_error('Mes inválido (1-12)');

$cots_cant    = max(0, (int)($body['cotizaciones_cantidad'] ?? 0));
$cots_monto   = max(0, (float)($body['cotizaciones_monto'] ?? 0));
$ventas_cant  = max(0, (int)($body['ventas_cantidad'] ?? 0));
$ventas_monto = max(0, (float)($body['ventas_monto'] ?? 0));
$tasa         = $cots_cant > 0 ? round(($ventas_cant / $cots_cant) * 100, 2) : 0;

DB::execute(
    "INSERT INTO historial_mensual (empresa_id, anio, mes, cotizaciones_cantidad, cotizaciones_monto, ventas_cantidad, ventas_monto, tasa_cierre)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        cotizaciones_cantidad = VALUES(cotizaciones_cantidad),
        cotizaciones_monto = VALUES(cotizaciones_monto),
        ventas_cantidad = VALUES(ventas_cantidad),
        ventas_monto = VALUES(ventas_monto),
        tasa_cierre = VALUES(tasa_cierre)",
    [$empresa_id, $anio, $mes, $cots_cant, $cots_monto, $ventas_cant, $ventas_monto, $tasa]
);

json_ok(['saved' => true]);
