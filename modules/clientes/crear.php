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
$nombre    = trim($body['nombre']    ?? '');
$telefono  = trim($body['telefono']  ?? '');
$tel_emp   = trim($body['telefono_empresa'] ?? '');
$email     = trim($body['email']     ?? '');
$direccion = trim($body['direccion'] ?? '');
$nota      = trim($body['nota']      ?? '');

if (empty($nombre))   json_error('El nombre es requerido');
if (empty($telefono)) json_error('El teléfono es requerido');
if (strlen($nombre)   > 150) json_error('Nombre muy largo');
if (strlen($telefono) > 30)  json_error('Teléfono muy largo');
if (strlen($tel_emp)  > 30)  json_error('Teléfono de empresa muy largo');
// El correo es OPCIONAL a propósito: exigirlo frena al asesor y muchos clientes
// no lo dan. Cuando haga falta para enviar la cotización, el botón lo pide ahí
// mismo y lo guarda. Pero si viene, tiene que ser válido.
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Email inválido');
if (strlen($email) > 190) json_error('Email muy largo');

// ─── Duplicado por teléfono ──────────────────────────────
$existe = DB::val(
    "SELECT id FROM clientes WHERE empresa_id=? AND telefono=?",
    [$empresa_id, $telefono]
);
if ($existe) json_error('Ya existe un cliente con ese teléfono', 409);

// ─── Insertar ────────────────────────────────────────────
try {
    // El correo NUNCA se guardaba al crear (solo al editar) — por eso el botón
    // "Enviar correo" no tenía a dónde mandar en clientes recién dados de alta.
    $id = DB::insert(
        "INSERT INTO clientes (empresa_id, usuario_id, nombre, telefono, telefono_empresa, email, direccion, nota)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $empresa_id,
            Auth::id(),
            $nombre,
            $telefono,
            $tel_emp   ?: null,
            $email     ?: null,
            $direccion ?: null,
            $nota      ?: null,
        ]
    );
} catch (Exception $e) {
    // Base sin migrar: telefono_empresa aún no existe. Se guarda el cliente sin
    // ella en vez de fallar — el alta de clientes no se puede caer por una
    // columna nueva.
    try {
        $id = DB::insert(
            "INSERT INTO clientes (empresa_id, usuario_id, nombre, telefono, email, direccion, nota)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $empresa_id,
                Auth::id(),
                $nombre,
                $telefono,
                $email     ?: null,
                $direccion ?: null,
                $nota      ?: null,
            ]
        );
    } catch (Exception $e2) {
        if (DEBUG) throw $e2;
        json_error('Error al crear cliente', 500);
    }
}

// El front necesita estos campos para armar los botones de envío sin recargar:
// 'wa' viene ya normalizado desde PHP para no duplicar esa lógica en JavaScript.
json_ok([
    'id'       => $id,
    'nombre'   => $nombre,
    'telefono' => $telefono,
    'email'    => $email,
    'wa'       => tel_whatsapp($telefono, lada_pais($empresa_id)),
]);
