<?php
// ============================================================
//  CotizaApp — modules/radar/descartar_comp.php
//  POST /radar/descartar-comp
//  "Ya revisé" — limpia alerta, reaparece si hay actividad nueva
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();

header('Content-Type: application/json; charset=utf-8');
csrf_check();

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

$tipo       = $body['tipo'] ?? '';
$valor      = trim($body['valor'] ?? '');
$empresa_id = (int)($body['empresa_id'] ?? EMPRESA_ID);

if ($valor === '') json_error('Valor requerido');
if (!in_array($tipo, ['ip', 'user', 'device'], true)) json_error('Tipo no válido');

try {
    DB::execute(
        "INSERT INTO radar_comp_reviewed (empresa_id, tipo, valor, reviewed_at)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE reviewed_at = NOW()",
        [$empresa_id, $tipo, $valor]
    );
} catch (Throwable $e) {
    json_error('Error al guardar revisión');
}

json_ok(['revisado' => true]);
