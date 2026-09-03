<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/enviar_correo.php
//  POST /cotizaciones/:id/enviar-correo
//
//  Manda la cotización al correo del cliente, a nombre de la empresa.
//  Distinto de enviar.php, que solo marca la cotización como enviada y
//  devuelve la liga para que el asesor la comparta él mismo.
//
//  Está APAGADO por default (notif_config['envio_correo_cliente']). Se prende
//  por empresa desde Configuración. Razón: el correo sale desde nuestra
//  infraestructura, y prenderlo para todos de golpe pondría a nueve empresas a
//  mandar correo sin haberlo pedido — con los rebotes cayendo sobre la
//  reputación del dominio que también manda las recuperaciones de contraseña.
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

// ─── Interruptor por empresa ─────────────────────────────
$ncfg = notif_config($empresa_id);
if (empty($ncfg['envio_correo_cliente'])) {
    json_error('El envío por correo no está activado para esta empresa', 403);
}

// ─── Cargar y verificar propiedad ────────────────────────
$cot = DB::row(
    "SELECT c.id, c.numero, c.slug, c.estado, c.cliente_id, c.valida_hasta,
            c.vendedor_id, c.usuario_id,
            cl.nombre AS cliente_nombre, cl.email AS cliente_email
       FROM cotizaciones c
       LEFT JOIN clientes cl ON cl.id = c.cliente_id
      WHERE c.id = ? AND c.empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('No encontrada', 404);

// Mismos estados que enviar.php: no tiene sentido mandarle al cliente una
// cotización suspendida o ya convertida en venta.
if (!in_array($cot['estado'], ['borrador','enviada','vista'], true)) {
    json_error('No se puede enviar en estado: ' . $cot['estado'], 422);
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];

// ─── Destinatario ────────────────────────────────────────
// El correo es opcional al dar de alta un cliente, así que muchos no lo tienen.
// En vez de bloquear, la pantalla lo pide en el momento y lo manda aquí: se
// guarda en el cliente para no volver a preguntarlo nunca más.
$email_nuevo = trim((string)($body['email'] ?? ''));
$destino     = trim((string)($cot['cliente_email'] ?? ''));

if ($email_nuevo !== '') {
    if (!filter_var($email_nuevo, FILTER_VALIDATE_EMAIL)) json_error('El correo no es válido', 422);
    if (strlen($email_nuevo) > 190) json_error('Correo muy largo', 422);
    $destino = $email_nuevo;

    if (!empty($cot['cliente_id'])) {
        DB::execute(
            "UPDATE clientes SET email=?, updated_at=NOW() WHERE id=? AND empresa_id=?",
            [$destino, (int)$cot['cliente_id'], $empresa_id]
        );
    }
}

if ($destino === '') json_error('El cliente no tiene correo registrado', 422);

// ─── Límite de envíos ────────────────────────────────────
// Sin esto, un doble clic o un asesor impaciente le manda al cliente el mismo
// correo cinco veces, y esa es la vía rápida a que lo marque como spam.
$rl = rate_check('cot_correo_' . $cot_id, 3, 10);
if (!$rl['ok']) json_error($rl['error'], 429);

// ─── Datos de la empresa y del asesor ────────────────────
$empresa = DB::row(
    "SELECT nombre, email, cot_theme, dominio_custom FROM empresas WHERE id=?",
    [$empresa_id]
);

// Responder-A. Cuando es el asesor, es el ASIGNADO a la cotización y no quien
// aprieta el botón: si un admin la manda, la respuesta del cliente tiene que
// llegarle igual al asesor, que es quien le va a dar seguimiento.
// La empresa elige a quién en Configuración › Envío al cliente.
$asesor_id = (int)($cot['vendedor_id'] ?: $cot['usuario_id'] ?: Auth::id());
$asesor    = $asesor_id
    ? DB::row("SELECT nombre, email FROM usuarios WHERE id=? AND empresa_id=?", [$asesor_id, $empresa_id])
    : null;

$mail_asesor  = trim((string)($asesor['email'] ?? ''));
$nom_asesor   = trim((string)($asesor['nombre'] ?? ''));
$mail_empresa = trim((string)($empresa['email'] ?? ''));
$nom_empresa  = trim((string)($empresa['nombre'] ?? ''));

// Sea cual sea la preferencia, si esa opción no tiene correo se usa la otra:
// un correo sin Responder-A deja al cliente sin forma de contestar.
if (($ncfg['correo_responde_a'] ?? 'asesor') === 'empresa') {
    $reply_to     = $mail_empresa ?: $mail_asesor;
    $reply_nombre = $mail_empresa ? $nom_empresa : ($nom_asesor ?: $nom_empresa);
} else {
    $reply_to     = $mail_asesor ?: $mail_empresa;
    $reply_nombre = $mail_asesor ? ($nom_asesor ?: $nom_empresa) : $nom_empresa;
}

$themes = [
    'verde' => '#1a6b3c', 'azul'  => '#1d4ed8', 'rojo'   => '#b91c1c',
    'naranja' => '#e8a317', 'dorado' => '#92400e', 'morado' => '#6d28d9',
    'oscuro' => '#1e293b',
];
$color = $themes[$empresa['cot_theme'] ?? 'verde'] ?? '#1a6b3c';

$vigencia = '';
if (!empty($cot['valida_hasta']) && $cot['valida_hasta'] !== '0000-00-00') {
    $vigencia = date('d/m/Y', strtotime($cot['valida_hasta']));
}

// ─── Enviar ──────────────────────────────────────────────
$enviado = Mailer::enviar_cotizacion([
    'para'           => $destino,
    'para_nombre'    => (string)($cot['cliente_nombre'] ?? ''),
    'empresa_nombre' => (string)($empresa['nombre'] ?? ''),
    'numero'         => (string)($cot['numero'] ?? ''),
    'url'            => Router::url_publica('/c/' . $cot['slug']),
    'color'          => $color,
    'vigencia'       => $vigencia,
    'reply_to'       => $reply_to,
    'reply_nombre'   => $reply_nombre,
    // Dominio propio = se respeta la marca del cliente, sin pie de CotizaCloud.
    'mostrar_marca'  => empty($empresa['dominio_custom']),
]);

rate_hit('cot_correo_' . $cot_id);

if (!$enviado) {
    // Mailer ya dejó el detalle en el log de errores.
    json_error('No se pudo enviar el correo. Intenta de nuevo en un momento.', 502);
}

// ─── Registrar ───────────────────────────────────────────
// Mandarlo por correo ES enviarlo: la cotización pasa a 'enviada' y se sella la
// fecha si no la tenía, para que el Radar y el termómetro cuenten desde aquí.
try {
    DB::execute(
        "UPDATE cotizaciones
            SET estado = CASE WHEN estado='borrador' THEN 'enviada' ELSE estado END,
                enviada_at = COALESCE(enviada_at, NOW())
          WHERE id=?",
        [$cot_id]
    );
} catch (\Throwable $e) {
    error_log('[enviar_correo] no se pudo sellar enviada_at cot=' . $cot_id . ' — ' . $e->getMessage());
}

// El log es para que la pantalla pueda decir "enviado hace 2 h" y el asesor no
// lo repita. Si falla, el correo YA se mandó: no se convierte en error.
try {
    DB::execute(
        "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, ip) VALUES (?,?,'correo_enviado',?)",
        [$cot_id, Auth::id(), ip_real()]
    );
} catch (\Throwable $e) {
    error_log('[enviar_correo] log falló cot=' . $cot_id . ' — ' . $e->getMessage());
}

json_ok(['email' => $destino]);
