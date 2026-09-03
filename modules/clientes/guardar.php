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
// SELECT * y no columnas sueltas: abajo se conserva el valor actual de cada
// campo que el payload no traiga, y para eso hay que conocerlos todos.
$cliente = DB::row(
    "SELECT * FROM clientes WHERE id = ? AND empresa_id = ?",
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
// Un campo que el payload NO trae conserva su valor actual; solo se borra si
// viene explícitamente vacío. Sin esto, cualquier pantalla que guarde un
// subconjunto de campos borraría los demás en silencio — pasaba con el correo
// al guardar desde la hoja del listado, que nunca lo mandaba.
$campo = function (string $k, $actual) use ($body) {
    return array_key_exists($k, $body) ? trim((string)$body[$k]) : (string)($actual ?? '');
};

$nombre    = $campo('nombre',           $cliente['nombre']);
$telefono  = $campo('telefono',         $cliente['telefono']);
$tel_emp   = $campo('telefono_empresa', $cliente['telefono_empresa'] ?? '');
$email     = $campo('email',            $cliente['email']);
$direccion = $campo('direccion',        $cliente['direccion']);
$nota      = $campo('nota',             $cliente['nota']);

if (empty($nombre))   json_error('El nombre es requerido');
if (empty($telefono)) json_error('El teléfono es requerido');
if (strlen($nombre)   > 150) json_error('Nombre muy largo');
if (strlen($telefono) > 30)  json_error('Teléfono muy largo');
if (strlen($tel_emp)  > 30)  json_error('Teléfono de empresa muy largo');
if (strlen($email)    > 190) json_error('Email muy largo');
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Email inválido');

// ─── Duplicado teléfono (otro cliente) ───────────────────
$dup = DB::val(
    "SELECT id FROM clientes WHERE empresa_id=? AND telefono=? AND id!=?",
    [$empresa_id, $telefono, $cliente_id]
);
if ($dup) json_error('Ya existe otro cliente con ese teléfono', 409);

// ─── Actualizar ──────────────────────────────────────────
try {
    DB::execute(
        "UPDATE clientes SET nombre=?, telefono=?, telefono_empresa=?, email=?, direccion=?, nota=?, updated_at=NOW() WHERE id=?",
        [
            $nombre,
            $telefono,
            $tel_emp   ?: null,
            $email     ?: null,
            $direccion ?: null,
            $nota      ?: null,
            $cliente_id,
        ]
    );
} catch (Exception $e) {
    // Base sin migrar: se guarda sin telefono_empresa en vez de fallar.
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
}

json_ok([
    'id'       => $cliente_id,
    'nombre'   => $nombre,
    'telefono' => $telefono,
    'email'    => $email,
    'wa'       => tel_whatsapp($telefono, lada_pais($empresa_id)),
]);
