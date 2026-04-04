<?php
// ============================================================
//  SuperAdmin — Generar cupón de compensación
//  POST /superadmin/compensacion
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

header('Content-Type: application/json; charset=utf-8');

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$empresa_id = (int)($body['empresa_id'] ?? 0);
$monto      = (float)($body['monto'] ?? 0);
$cliente    = trim($body['cliente'] ?? '');
$slug       = trim($body['slug'] ?? '');

if (!$empresa_id) { echo json_encode(['ok'=>false,'error'=>'Empresa inválida']); exit; }
if (!in_array($monto, [1000, 2000, 4000])) { echo json_encode(['ok'=>false,'error'=>'Monto inválido']); exit; }
if ($cliente === '') { echo json_encode(['ok'=>false,'error'=>'El nombre del cliente es requerido']); exit; }

// Verificar empresa existe
$emp = DB::row("SELECT id, slug, dominio_custom FROM empresas WHERE id = ? AND activa = 1", [$empresa_id]);
if (!$emp) { echo json_encode(['ok'=>false,'error'=>'Empresa no encontrada']); exit; }

// Generar código único
$codigo = 'COMP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

// Verificar que no exista
while (DB::val("SELECT id FROM cupones WHERE empresa_id = ? AND codigo = ?", [$empresa_id, $codigo])) {
    $codigo = 'COMP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

// Insertar cupón
$cupon_id = DB::insert(
    "INSERT INTO cupones (empresa_id, codigo, porcentaje, monto_fijo, descripcion, activo, usos_max, vencimiento_tipo, vencimiento_fecha)
     VALUES (?, ?, 0, ?, ?, 1, 1, 'fecha_fija', DATE_ADD(CURDATE(), INTERVAL 90 DAY))",
    [$empresa_id, $codigo, $monto, 'Compensación: ' . $cliente]
);

// Generar URL
$dominio = $emp['dominio_custom'] ?: $emp['slug'] . '.' . BASE_DOMAIN;
$url = 'https://' . $dominio . '/w/' . $codigo;

echo json_encode([
    'ok'     => true,
    'codigo' => $codigo,
    'url'    => $url,
    'id'     => $cupon_id,
]);
