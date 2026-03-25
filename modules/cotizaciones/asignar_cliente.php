<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/asignar_cliente.php
//  POST /cotizaciones/:id/cliente
//  Permite asignar/cambiar el cliente de una cotización
//  (funciona en cualquier estado, no solo editables)
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Método no permitido', 405);

csrf_check();

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

// Verificar que existe y pertenece a la empresa
$cot = DB::row(
    "SELECT id, usuario_id, vendedor_id FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('Cotización no encontrada', 404);

// Verificar acceso
if (!Auth::puede('ver_todas_cots') && (int)$cot['usuario_id'] !== (int)Auth::id() && (int)($cot['vendedor_id'] ?? 0) !== (int)Auth::id()) {
    json_error('Sin acceso', 403);
}

// Validar cliente
$cliente_id = isset($body['cliente_id']) ? (int)$body['cliente_id'] : null;
if (!$cliente_id) json_error('El cliente es requerido');

$ok = DB::val("SELECT id FROM clientes WHERE id = ? AND empresa_id = ?", [$cliente_id, $empresa_id]);
if (!$ok) json_error('Cliente no válido');

// Actualizar solo el cliente
DB::execute(
    "UPDATE cotizaciones SET cliente_id = ? WHERE id = ? AND empresa_id = ?",
    [$cliente_id, $cot_id, $empresa_id]
);

echo json_encode(['ok' => true]);
