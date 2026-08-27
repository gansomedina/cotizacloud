<?php
// ============================================================
//  Prueba el envío de correo con la MISMA configuración que usa la app.
//
//  Existe porque cuando un correo no llega no queda claro dónde se rompió:
//  ¿faltan credenciales?, ¿el relay rechaza la IP?, ¿el buzón lo mandó a spam?
//  Los llamadores de Mailer no revisan el bool de retorno y el error solo vive
//  en el log de PHP, que después de una migración no siempre está donde uno
//  cree. Esto lo imprime en pantalla, con el error crudo del servidor SMTP.
//
//  SOLO LECTURA sobre la configuración: no toca base de datos ni escribe nada.
//  Nunca imprime la contraseña — solo dice si está definida y cuánto mide.
//
//    php tools/probar_correo.php destino@ejemplo.com
//    php tools/probar_correo.php destino@ejemplo.com /ruta/a/config.php
// ============================================================
define('COTIZAAPP', 1);

$para = $argv[1] ?? '';
$cfg  = $argv[2] ?? '/var/www/cotizacloud/config.php';

if ($para === '' || !filter_var($para, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php tools/probar_correo.php destino@ejemplo.com [config.php]\n");
    exit(1);
}
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));

echo "\n═══ CONFIGURACIÓN SMTP ═══\n";
$campos = ['SMTP_HOST' => 'mail.cotiza.cloud', 'SMTP_USER' => 'noreply@cotiza.cloud',
           'SMTP_PORT' => 465, 'SMTP_SECURE' => 'ssl', 'SMTP_FROM' => 'noreply@cotiza.cloud',
           'SMTP_TIMEOUT' => 10];
foreach ($campos as $k => $default) {
    $definida = defined($k);
    printf("  %-13s %s%s\n", $k,
        $definida ? (string)constant($k) : (string)$default,
        $definida ? '' : '   ← NO está en config.php, se usa el valor por defecto');
}
// La contraseña NO se imprime: solo si existe y cuánto mide. Vacía es la causa
// más común de "conecta pero no manda" tras mover de servidor.
if (!defined('SMTP_PASS')) {
    echo "  SMTP_PASS     ← NO DEFINIDA en config.php\n";
} else {
    $n = strlen((string)SMTP_PASS);
    echo "  SMTP_PASS     " . ($n === 0 ? '← VACÍA' : "definida ($n caracteres)") . "\n";
}

echo "\n═══ ENVIANDO A $para ═══\n";
require_once ROOT_PATH . '/vendor/phpmailer/Exception.php';
require_once ROOT_PATH . '/vendor/phpmailer/PHPMailer.php';
require_once ROOT_PATH . '/vendor/phpmailer/SMTP.php';
require_once ROOT_PATH . '/core/Mailer.php';

// El diálogo completo con el servidor: aquí sale el motivo real del rechazo
// (credenciales, relay denegado, IP bloqueada...) en vez de un "no se pudo".
$t0 = microtime(true);
$ok = false; $err = '';
try {
    $m = (new ReflectionClass('Mailer'))->getMethod('crear');
    $m->setAccessible(true);
    $mail = $m->invoke(null);
    $mail->SMTPDebug  = 2;                                  // 2 = cliente y servidor
    $mail->Debugoutput = function ($str, $level) { echo '  ' . rtrim($str) . "\n"; };
    $mail->addAddress($para);
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo — CotizaCloud';
    $mail->Body    = '<p>Si estás leyendo esto, el envío de correo del servidor funciona.</p>';
    $mail->AltBody = 'Si estás leyendo esto, el envío de correo del servidor funciona.';
    $ok = $mail->send();
} catch (Throwable $e) {
    $err = $e->getMessage();
}
$ms = (int)round((microtime(true) - $t0) * 1000);

echo "\n═══ RESULTADO ═══\n";
if ($ok) {
    echo "  ✓ El servidor ACEPTÓ el mensaje ({$ms} ms).\n";
    echo "    Si aun así no llega, ya no es la app: revisa spam y las reglas del buzón.\n";
} else {
    echo "  ✗ NO se pudo enviar ({$ms} ms).\n";
    if ($err !== '') echo "    Error: $err\n";
    echo "    El diálogo de arriba dice en qué punto se cortó.\n";
}
echo "\n";
exit($ok ? 0 : 1);
