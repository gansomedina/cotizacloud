<?php
// ============================================================
//  CotizaApp — modules/clientes/crear.php
//  POST /clientes
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

$empresa_id = EMPRESA_ID;

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

// ─── Validar ─────────────────────────────────────────────
$nombre   = trim($body['nombre']   ?? '');
$telefono = trim($body['telefono'] ?? '');
$email    = trim($body['email']    ?? '');
$nota     = trim($body['nota']     ?? '');

if (empty($nombre))   json_error('El nombre es requerido');
if (empty($telefono)) json_error('El teléfono es requerido');
if (strlen($nombre)   > 150) json_error('Nombre muy largo');
if (strlen($telefono) > 30)  json_error('Teléfono muy largo');
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Email inválido');

// ─── Duplicado por teléfono ──────────────────────────────
$existe = DB::val(
    "SELECT id FROM clientes WHERE empresa_id=? AND telefono=?",
    [$empresa_id, $telefono]
);
if ($existe) json_error('Ya existe un cliente con ese teléfono', 409);

// ─── Insertar ────────────────────────────────────────────
try {
    $id = DB::insert(
        "INSERT INTO clientes (empresa_id, usuario_id, nombre, telefono, email, nota)
         VALUES (?, ?, ?, ?, ?, ?)",
        [
            $empresa_id,
            Auth::id(),
            $nombre,
            $telefono,
            $email   ?: null,
            $nota    ?: null,
        ]
    );
} catch (Exception $e) {
    if (DEBUG) throw $e;
    json_error('Error al crear cliente', 500);
}

json_ok(['id' => $id, 'nombre' => $nombre]);
