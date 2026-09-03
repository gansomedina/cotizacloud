<?php
// ============================================================
//  CotizaApp — core/Mailer.php
//  Clase centralizada de envío de emails via SMTP (PHPMailer)
// ============================================================

defined('COTIZAAPP') or die;

require_once ROOT_PATH . '/vendor/phpmailer/Exception.php';
require_once ROOT_PATH . '/vendor/phpmailer/PHPMailer.php';
require_once ROOT_PATH . '/vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class Mailer
{
    private static function crear(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        // El respaldo era 'mail.cotiza.cloud', el servidor de correo del hosting
        // viejo (cPanel Limitless). Ese nombre ya no existe y su IP la reciclara
        // el proveedor para otro cliente: si algun dia se pierde SMTP_HOST del
        // config, PHPMailer intentaria autenticarse contra el servidor de un
        // desconocido. Ahora el respaldo es el relay que de verdad se usa.
        $mail->Host       = defined('SMTP_HOST')      ? SMTP_HOST      : 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER')      ? SMTP_USER      : 'noreply@cotiza.cloud';
        $mail->Password   = defined('SMTP_PASS')      ? SMTP_PASS      : '';
        // Coherentes con el Host de respaldo: Brevo es 587 + STARTTLS ('tls'),
        // NO 465 + 'ssl'. Un respaldo a medias (host nuevo, puerto viejo) parece
        // correcto y no conecta.
        $mail->SMTPSecure = defined('SMTP_SECURE')    ? SMTP_SECURE    : 'tls';
        $mail->Port       = defined('SMTP_PORT')      ? SMTP_PORT      : 587;
        $mail->CharSet    = 'UTF-8';
        // Sin esto PHPMailer espera 300 s por operacion SMTP: un relay lento
        // retiene el worker de PHP-FPM y basta un puñado para tumbar el sitio.
        $mail->Timeout    = defined('SMTP_TIMEOUT')   ? SMTP_TIMEOUT   : 10;
        $from      = defined('SMTP_FROM')      ? SMTP_FROM      : 'noreply@cotiza.cloud';
        $from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'CotizaCloud';
        $mail->setFrom($from, $from_name);
        return $mail;
    }

    /**
     * Enviar email con template HTML
     */
    public static function enviar(string $para, string $nombre, string $asunto, string $body_html): bool
    {
        try {
            $mail = self::crear();
            $mail->addAddress($para, $nombre);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = self::wrap_template($asunto, $body_html);
            $mail->AltBody = strip_tags(str_replace(['<br>','<br/>','<br />','</p>'], "\n", $body_html));
            $mail->send();
            return true;
        } catch (MailException $e) {
            // SIEMPRE se registra, no solo con DEBUG. Antes solo se logueaba en
            // desarrollo, o sea NUNCA en producción — y ninguno de los ~20
            // llamadores comprueba el bool de retorno. Resultado: si el relay
            // SMTP rechaza (p.ej. Brevo bloquea la IP del servidor, que es su
            // comportamiento documentado al cambiar de hosting), el prospecto
            // que se registra nunca recibe su código de verificación y no queda
            // rastro en ningún lado. Se incluye el destinatario y el asunto para
            // poder reconstruir qué se perdió.
            error_log('[Mailer] FALLO envío a ' . $para . ' — asunto: ' . $asunto
                    . ' — error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Template HTML base para todos los emails
     */
    private static function wrap_template(string $titulo, string $contenido): string
    {
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$titulo}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f0;font-family:'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f0;padding:32px 16px">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:12px;border:1px solid #e2e2dc;overflow:hidden">

<!-- Header -->
<tr><td style="background:#1a5c38;padding:24px 32px;text-align:center">
    <span style="font-size:22px;font-weight:800;color:#fff;letter-spacing:-.02em">Cotiza<span style="color:#4ade80">.cloud</span></span>
</td></tr>

<!-- Body -->
<tr><td style="padding:32px;font-size:15px;line-height:1.6;color:#1a1a18">
{$contenido}
</td></tr>

<!-- Footer -->
<tr><td style="padding:20px 32px;background:#f9f9f7;border-top:1px solid #e2e2dc;text-align:center;font-size:12px;color:#6a6a64">
    &copy; {$year} CotizaCloud &mdash; cotiza.cloud
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * Template para correos que ve el CLIENTE FINAL.
     *
     * El template normal lleva el encabezado de CotizaCloud, y eso está bien
     * para los correos que recibe el asesor. Pero el cliente de "Comercializadora
     * Reyes" no tiene por qué ver nuestra marca donde debería ir la de su
     * proveedor: aquí manda el nombre y el color de la empresa emisora.
     *
     * El pie con "Enviado con CotizaCloud" solo aparece cuando la cotización
     * vive en un subdominio nuestro. Si la empresa usa dominio propio, se
     * respeta su marca por completo — misma regla que ya se decidió para los
     * slugs públicos.
     */
    private static function wrap_template_empresa(
        string $titulo,
        string $contenido,
        string $empresa_nombre,
        string $color,
        bool   $mostrar_marca
    ): string {
        $year  = date('Y');
        $emp   = htmlspecialchars($empresa_nombre, ENT_QUOTES, 'UTF-8');
        $color = preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#1a6b3c';

        $marca = $mostrar_marca
            ? '<div style="margin-top:6px;font-size:11px;color:#9a9a94">Enviado con <a href="https://cotiza.cloud" style="color:#9a9a94;text-decoration:underline">CotizaCloud</a></div>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$titulo}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f0;font-family:'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f0;padding:32px 16px">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:12px;border:1px solid #e2e2dc;overflow:hidden">

<tr><td style="background:{$color};padding:24px 32px;text-align:center">
    <span style="font-size:20px;font-weight:800;color:#fff;letter-spacing:-.01em">{$emp}</span>
</td></tr>

<tr><td style="padding:32px;font-size:15px;line-height:1.6;color:#1a1a18">
{$contenido}
</td></tr>

<tr><td style="padding:20px 32px;background:#f9f9f7;border-top:1px solid #e2e2dc;text-align:center;font-size:12px;color:#6a6a64">
    &copy; {$year} {$emp}
    {$marca}
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * Manda la cotización al cliente final, a nombre de la empresa.
     *
     * Tres decisiones que están aquí a propósito:
     *
     * - El REMITENTE es una dirección nuestra (no se puede firmar el dominio del
     *   cliente sin su DKIM; hacerlo sería suplantación y acabaría en spam).
     *   Lo que el cliente ve en su bandeja es el NOMBRE de la empresa, que es lo
     *   que se muestra en Gmail, Outlook y Mail. Idealmente sale de un subdominio
     *   aparte (SMTP_FROM_ENVIOS) para que un rebote no contamine la reputación
     *   del correo crítico: verificaciones y recuperación de contraseña.
     *
     * - El RESPONDER-A es el asesor. Una respuesta del cliente es una señal de
     *   compra; si cae en un buzón general no tiene dueño y se muere.
     *
     * - NO se incluye el total. El correo existe para que el cliente ABRA la
     *   cotización: ahí es donde el Radar mide interés y donde puede aceptar.
     *   Poniendo el monto en el correo, muchos no entran y perdemos la señal.
     */
    public static function enviar_cotizacion(array $a): bool
    {
        $para = trim((string)($a['para'] ?? ''));
        if ($para === '' || !filter_var($para, FILTER_VALIDATE_EMAIL)) {
            error_log('[Mailer] enviar_cotizacion sin destinatario válido: ' . $para);
            return false;
        }

        $emp_nombre = trim((string)($a['empresa_nombre'] ?? '')) ?: 'Tu proveedor';
        $cli_nombre = trim((string)($a['para_nombre'] ?? ''));
        $saludo     = $cli_nombre !== '' ? 'Hola ' . explode(' ', $cli_nombre)[0] . ',' : 'Hola,';
        $numero     = trim((string)($a['numero'] ?? ''));
        $url        = (string)($a['url'] ?? '');
        $color      = (string)($a['color'] ?? '#1a6b3c');
        $vigencia   = trim((string)($a['vigencia'] ?? ''));

        $asunto = $numero !== ''
            ? "Tu cotización {$numero} — {$emp_nombre}"
            : "Tu cotización — {$emp_nombre}";

        $eNom = htmlspecialchars($emp_nombre, ENT_QUOTES, 'UTF-8');
        $eSal = htmlspecialchars($saludo,     ENT_QUOTES, 'UTF-8');
        $eNum = htmlspecialchars($numero,     ENT_QUOTES, 'UTF-8');
        $eUrl = htmlspecialchars($url,        ENT_QUOTES, 'UTF-8');
        $eCol = preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#1a6b3c';

        $linea_num = $eNum !== '' ? " <strong>{$eNum}</strong>" : '';
        $linea_vig = $vigencia !== ''
            ? '<p style="margin:18px 0 0;font-size:13px;color:#6a6a64">Vigente hasta ' . htmlspecialchars($vigencia, ENT_QUOTES, 'UTF-8') . '.</p>'
            : '';

        $contenido = <<<HTML
<p style="margin:0 0 14px">{$eSal}</p>
<p style="margin:0 0 24px">Aquí está tu cotización{$linea_num} de <strong>{$eNom}</strong>. Puedes verla completa, resolver dudas y aceptarla desde este enlace:</p>
<table cellpadding="0" cellspacing="0" style="margin:0 auto"><tr><td style="border-radius:8px;background:{$eCol}">
    <a href="{$eUrl}" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:700;color:#fff;text-decoration:none;border-radius:8px">Ver mi cotización</a>
</td></tr></table>
<p style="margin:24px 0 0;font-size:12px;color:#9a9a94;word-break:break-all">Si el botón no funciona, copia esta dirección en tu navegador:<br>{$eUrl}</p>
{$linea_vig}
HTML;

        try {
            $mail = self::crear();

            // Subdominio de envíos si está configurado. Aislar la reputación es
            // lo que evita que un rebote de una cotización mande a spam el correo
            // de recuperación de contraseña de otro cliente.
            $from_addr = defined('SMTP_FROM_ENVIOS') ? SMTP_FROM_ENVIOS
                       : (defined('SMTP_FROM') ? SMTP_FROM : 'noreply@cotiza.cloud');
            $mail->setFrom($from_addr, $emp_nombre);

            $reply = trim((string)($a['reply_to'] ?? ''));
            if ($reply !== '' && filter_var($reply, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($reply, trim((string)($a['reply_nombre'] ?? '')) ?: $emp_nombre);
            }

            $mail->addAddress($para, $cli_nombre !== '' ? $cli_nombre : $para);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = self::wrap_template_empresa(
                $asunto, $contenido, $emp_nombre, $eCol, (bool)($a['mostrar_marca'] ?? true)
            );
            // Un correo HTML que es casi solo un enlace, sin versión de texto,
            // es una firma clásica de spam. Esta alternativa la evita.
            $mail->AltBody = $saludo . "\n\n"
                . "Aquí está tu cotización" . ($numero !== '' ? " {$numero}" : '') . " de {$emp_nombre}.\n\n"
                . $url . "\n"
                . ($vigencia !== '' ? "\nVigente hasta {$vigencia}.\n" : '');

            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log('[Mailer] FALLO cotización a ' . $para . ' — ' . $asunto . ' — ' . $e->getMessage());
            return false;
        }
    }

    // ─── Emails específicos ────────────────────────────────────

    /**
     * Email de verificación al registrarse
     */
    public static function enviar_verificacion(string $email, string $nombre, string $codigo): bool
    {
        $asunto = 'Verifica tu cuenta — CotizaCloud';
        $body = <<<HTML
<h2 style="margin:0 0 16px;font-size:20px;color:#1a5c38">Bienvenido a CotizaCloud</h2>
<p>Hola <strong>{$nombre}</strong>,</p>
<p>Gracias por registrarte. Para activar tu cuenta, ingresa el siguiente código de verificación:</p>
<div style="text-align:center;margin:24px 0">
    <div style="display:inline-block;background:#eef7f2;border:2px solid #1a5c38;border-radius:12px;padding:16px 32px;font-size:32px;font-weight:800;letter-spacing:.15em;color:#1a5c38;font-family:'Courier New',monospace">{$codigo}</div>
</div>
<p style="color:#6a6a64;font-size:13px">Este código expira en <strong>30 minutos</strong>.</p>
<p style="color:#6a6a64;font-size:13px">Si no creaste esta cuenta, ignora este mensaje.</p>
HTML;
        return self::enviar($email, $nombre, $asunto, $body);
    }

    /**
     * Email de recuperación de contraseña
     */
    public static function enviar_recovery(string $email, string $nombre, string $url_reset): bool
    {
        $asunto = 'Recuperar contraseña — CotizaCloud';
        $body = <<<HTML
<h2 style="margin:0 0 16px;font-size:20px;color:#1a5c38">Recuperar contraseña</h2>
<p>Hola <strong>{$nombre}</strong>,</p>
<p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el botón para crear una nueva:</p>
<div style="text-align:center;margin:24px 0">
    <a href="{$url_reset}" style="display:inline-block;background:#1a5c38;color:#fff;padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px;text-decoration:none">Restablecer contraseña</a>
</div>
<p style="color:#6a6a64;font-size:13px">Este enlace expira en <strong>1 hora</strong>.</p>
<p style="color:#6a6a64;font-size:13px">Si no solicitaste esto, ignora este mensaje. Tu contraseña no cambiará.</p>
HTML;
        return self::enviar($email, $nombre, $asunto, $body);
    }

    /**
     * Email de notificación de abono registrado
     */
    public static function enviar_abono(
        string $email,
        string $nombre_cliente,
        string $empresa_nombre,
        string $numero_recibo,
        float  $monto,
        string $moneda,
        float  $saldo_pendiente,
        string $forma_pago,
        string $url_recibo,
        string $concepto = '',
        string $titulo_venta = ''
    ): bool {
        $monto_fmt = format_money($monto, $moneda);
        $saldo_fmt = format_money($saldo_pendiente, $moneda);
        $forma_lbl = ucfirst($forma_pago);
        $estado_lbl = $saldo_pendiente <= 0 ? '<span style="color:#16a34a;font-weight:700">PAGADA</span>' : "Saldo pendiente: <strong>{$saldo_fmt}</strong>";
        $concepto_safe = htmlspecialchars($concepto);
        $concepto_row = $concepto !== '' ? '<tr style="border-bottom:1px solid #e2e2dc"><td style="padding:10px 0;color:#6a6a64">Concepto</td><td style="padding:10px 0;text-align:right">' . $concepto_safe . '</td></tr>' : '';
        $titulo_safe = htmlspecialchars($titulo_venta);
        $titulo_row = $titulo_venta !== '' ? '<tr style="border-bottom:1px solid #e2e2dc"><td style="padding:10px 0;color:#6a6a64">Proyecto</td><td style="padding:10px 0;text-align:right;font-weight:600">' . $titulo_safe . '</td></tr>' : '';

        $asunto = "Abono registrado {$monto_fmt} — {$empresa_nombre}";
        $body = <<<HTML
<h2 style="margin:0 0 16px;font-size:20px;color:#1a5c38">Abono registrado</h2>
<p>Hola <strong>{$nombre_cliente}</strong>,</p>
<p><strong>{$empresa_nombre}</strong> ha registrado un abono a tu cuenta:</p>
<table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:14px">
    <tr style="border-bottom:1px solid #e2e2dc">
        <td style="padding:10px 0;color:#6a6a64">Recibo</td>
        <td style="padding:10px 0;text-align:right;font-weight:600">{$numero_recibo}</td>
    </tr>
    <tr style="border-bottom:1px solid #e2e2dc">
        <td style="padding:10px 0;color:#6a6a64">Monto</td>
        <td style="padding:10px 0;text-align:right;font-weight:700;color:#1a5c38;font-size:18px">{$monto_fmt}</td>
    </tr>
    {$titulo_row}
    {$concepto_row}
    <tr style="border-bottom:1px solid #e2e2dc">
        <td style="padding:10px 0;color:#6a6a64">Forma de pago</td>
        <td style="padding:10px 0;text-align:right">{$forma_lbl}</td>
    </tr>
    <tr>
        <td style="padding:10px 0;color:#6a6a64">Estado</td>
        <td style="padding:10px 0;text-align:right">{$estado_lbl}</td>
    </tr>
</table>
<div style="text-align:center;margin:24px 0">
    <a href="{$url_recibo}" style="display:inline-block;background:#1a5c38;color:#fff;padding:12px 28px;border-radius:8px;font-weight:700;font-size:14px;text-decoration:none">Ver recibo</a>
</div>
HTML;
        return self::enviar($email, $nombre_cliente, $asunto, $body);
    }

    /**
     * Email de notificación de cotización aceptada (al vendedor)
     */
    public static function enviar_cotizacion_aceptada(
        string $email_vendedor,
        string $nombre_vendedor,
        string $titulo_cotizacion,
        string $nombre_cliente,
        float  $total,
        string $moneda
    ): bool {
        $total_fmt = format_money($total, $moneda);
        $asunto = "Cotización aceptada: {$titulo_cotizacion}";
        $body = <<<HTML
<h2 style="margin:0 0 16px;font-size:20px;color:#1a5c38">Cotización aceptada</h2>
<p>Hola <strong>{$nombre_vendedor}</strong>,</p>
<p>Tu cotización ha sido aceptada:</p>
<table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:14px">
    <tr style="border-bottom:1px solid #e2e2dc">
        <td style="padding:10px 0;color:#6a6a64">Cotización</td>
        <td style="padding:10px 0;text-align:right;font-weight:600">{$titulo_cotizacion}</td>
    </tr>
    <tr style="border-bottom:1px solid #e2e2dc">
        <td style="padding:10px 0;color:#6a6a64">Cliente</td>
        <td style="padding:10px 0;text-align:right">{$nombre_cliente}</td>
    </tr>
    <tr>
        <td style="padding:10px 0;color:#6a6a64">Total</td>
        <td style="padding:10px 0;text-align:right;font-weight:700;color:#1a5c38;font-size:18px">{$total_fmt}</td>
    </tr>
</table>
<div style="text-align:center;margin:20px 0">
    <div style="display:inline-block;background:#eef7f2;border:1px solid #b8ddc8;border-radius:8px;padding:12px 20px;color:#1a5c38;font-weight:600">Se ha creado una venta automáticamente</div>
</div>
HTML;
        return self::enviar($email_vendedor, $nombre_vendedor, $asunto, $body);
    }

    /**
     * Email de cotización rechazada (al vendedor)
     */
    public static function enviar_cotizacion_rechazada(
        string $email_vendedor,
        string $nombre_vendedor,
        string $titulo_cotizacion,
        string $motivo
    ): bool {
        $motivo_html = $motivo ? "<p><strong>Motivo:</strong> " . htmlspecialchars($motivo) . "</p>" : '<p style="color:#6a6a64">No se indicó motivo.</p>';
        $asunto = "Cotización rechazada: {$titulo_cotizacion}";
        $body = <<<HTML
<h2 style="margin:0 0 16px;font-size:20px;color:#c53030">Cotización rechazada</h2>
<p>Hola <strong>{$nombre_vendedor}</strong>,</p>
<p>La cotización <strong>{$titulo_cotizacion}</strong> fue rechazada.</p>
{$motivo_html}
<p style="color:#6a6a64;font-size:13px;margin-top:20px">Puedes editar y reenviar la cotización desde tu panel.</p>
HTML;
        return self::enviar($email_vendedor, $nombre_vendedor, $asunto, $body);
    }

    /**
     * Email de notificación al superadmin
     */
    public static function enviar_superadmin(
        string $email,
        string $tipo,
        string $titulo,
        string $detalle
    ): bool {
        $color = match($tipo) {
            'nueva_empresa'       => '#1a5c38',
            'solicitud_licencia'  => '#b45309',
            'nuevo_ticket'        => '#2563eb',
            default               => '#475569',
        };
        $label = match($tipo) {
            'nueva_empresa'       => 'Nueva Empresa',
            'solicitud_licencia'  => 'Solicitud de Licencia',
            'nuevo_ticket'        => 'Ticket de Soporte',
            default               => 'Notificación',
        };
        $asunto = "[CotizaCloud Admin] {$label}: {$titulo}";
        $body = <<<HTML
<h2 style="margin:0 0 16px;font-size:20px;color:{$color}">{$label}</h2>
<p style="font-size:15px;margin:0 0 12px"><strong>{$titulo}</strong></p>
<p style="font-size:14px;color:#6a6a64;margin:0 0 20px">{$detalle}</p>
<div style="text-align:center;margin:24px 0">
    <a href="https://cotiza.cloud/superadmin" style="display:inline-block;background:{$color};color:#fff;padding:12px 28px;border-radius:8px;font-weight:700;font-size:14px;text-decoration:none">Ver en panel</a>
</div>
HTML;
        return self::enviar($email, 'Admin', $asunto, $body);
    }
}
