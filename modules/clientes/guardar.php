<?php
// ============================================================
//  CotizaApp — modules/clientes/guardar.php
//  POST /clientes/:id
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

$empresa_id = EMPRESA_ID;
$cliente_id = (int)($id ?? 0);
if (!$cliente_id) json_error('ID inválido', 400);

// ─── Cargar y verificar propiedad ────────────────────────
$cliente = DB::row(
    "SELECT id, usuario_id FROM clientes WHERE id = ? AND empresa_id = ?",
    [$cliente_id, $empresa_id]
);
if (!$cliente) json_error('Cliente no encontrado', 404);

// Admin siempre puede, asesor necesita permiso o ser el dueño del cliente
if (!Auth::es_admin()) {
    if (!Auth::puede('editar_clientes')) {
        json_error('Sin permiso para editar clientes', 403);
    }
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

// ─── Validar ─────────────────────────────────────────────
$nombre    = trim($body['nombre']    ?? '');
$telefono  = trim($body['telefono']  ?? '');
$email     = trim($body['email']     ?? '');
$direccion = trim($body['direccion'] ?? '');
$nota      = trim($body['nota']      ?? '');

if (empty($nombre))   json_error('El nombre es requerido');
if (empty($telefono)) json_error('El teléfono es requerido');
if (strlen($nombre)   > 150) json_error('Nombre muy largo');
if (strlen($telefono) > 30)  json_error('Teléfono muy largo');
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Email inválido');

// ─── Duplicado teléfono (otro cliente) ───────────────────
$dup = DB::val(
    "SELECT id FROM clientes WHERE empresa_id=? AND telefono=? AND id!=?",
    [$empresa_id, $telefono, $cliente_id]
);
if ($dup) json_error('Ya existe otro cliente con ese teléfono', 409);

// ─── Actualizar ──────────────────────────────────────────
DB::execute(
    "UPDATE clientes SET nombre=?, telefono=?, email=?, direccion=?, nota=?, updated_at=NOW() WHERE id=?",
    [
        $nombre,
        $telefono,
        $email     ?: null,
        $direccion ?: null,
        $nota      ?: null,
        $cliente_id,
    ]
);

json_ok(['id' => $cliente_id, 'nombre' => $nombre]);
