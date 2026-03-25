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
        $mail->Host       = defined('SMTP_HOST')      ? SMTP_HOST      : 'mail.cotiza.cloud';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER')      ? SMTP_USER      : 'noreply@cotiza.cloud';
        $mail->Password   = defined('SMTP_PASS')      ? SMTP_PASS      : '';
        $mail->SMTPSecure = defined('SMTP_SECURE')    ? SMTP_SECURE    : 'ssl';
        $mail->Port       = defined('SMTP_PORT')      ? SMTP_PORT      : 465;
        $mail->CharSet    = 'UTF-8';
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
            if (DEBUG) error_log('Mailer error: ' . $e->getMessage());
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
        string $url_recibo
    ): bool {
        $monto_fmt = format_money($monto, $moneda);
        $saldo_fmt = format_money($saldo_pendiente, $moneda);
        $forma_lbl = ucfirst($forma_pago);
        $estado_lbl = $saldo_pendiente <= 0 ? '<span style="color:#16a34a;font-weight:700">PAGADA</span>' : "Saldo pendiente: <strong>{$saldo_fmt}</strong>";

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
}
