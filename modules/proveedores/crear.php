<?php
// ============================================================
//  CotizaApp — modules/proveedores/crear.php
//  POST /proveedores      → crear nuevo
//  POST /proveedores/:id  → editar existente
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

$empresa_id = EMPRESA_ID;

// Plan check
$plan = trial_info($empresa_id);
if (!$plan['es_business']) json_error('Función exclusiva del plan Business', 403);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

// ── Validar ─────────────────────────────────────────────────
$nombre    = trim($body['nombre']    ?? '');
$contacto  = trim($body['contacto']  ?? '');
$telefono  = trim($body['telefono']  ?? '');
$email     = trim($body['email']     ?? '');
$direccion = trim($body['direccion'] ?? '');
$nota      = trim($body['nota']      ?? '');

if (empty($nombre))       json_error('El nombre es requerido');
if (strlen($nombre) > 150) json_error('Nombre muy largo');

// Email validation
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Email inválido');
}

$edit_id = (int)($id ?? 0);

if ($edit_id) {
    // ── Editar ──────────────────────────────────────────────
    $existe = DB::val(
        "SELECT id FROM proveedores WHERE id = ? AND empresa_id = ?",
        [$edit_id, $empresa_id]
    );
    if (!$existe) json_error('Proveedor no encontrado', 404);

    try {
        DB::exec(
            "UPDATE proveedores SET nombre=?, contacto=?, telefono=?, email=?, direccion=?, nota=?
             WHERE id=? AND empresa_id=?",
            [$nombre, $contacto ?: null, $telefono ?: null, $email ?: null, $direccion ?: null, $nota ?: null,
             $edit_id, $empresa_id]
        );
    } catch (Exception $e) {
        if (defined('DEBUG') && DEBUG) throw $e;
        json_error('Error al actualizar', 500);
    }

    json_ok(['id' => $edit_id]);

} else {
    // ── Crear ───────────────────────────────────────────────
    try {
        $new_id = DB::insert(
            "INSERT INTO proveedores (empresa_id, nombre, contacto, telefono, email, direccion, nota)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$empresa_id, $nombre, $contacto ?: null, $telefono ?: null, $email ?: null, $direccion ?: null, $nota ?: null]
        );
    } catch (Exception $e) {
        if (defined('DEBUG') && DEBUG) throw $e;
        json_error('Error al crear proveedor', 500);
    }

    json_ok(['id' => $new_id, 'nombre' => $nombre]);
}
