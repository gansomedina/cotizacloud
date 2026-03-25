<?php
// ============================================================
//  Test de envío de email SMTP
//  Ejecutar en servidor: php test_email.php
//  O acceder via web: https://cotiza.cloud/test_email.php
//  ELIMINAR DESPUÉS DE PROBAR
// ============================================================

define('COTIZAAPP', true);
require_once __DIR__ . '/config.php';

// Cargar core
require_once __DIR__ . '/core/DB.php';
require_once __DIR__ . '/core/Helpers.php';
require_once __DIR__ . '/core/Mailer.php';

echo "<pre>\n";
echo "=== Test de Email SMTP ===\n\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "User: " . SMTP_USER . "\n";
echo "Pass: " . (SMTP_PASS === 'CAMBIAR_CONTRASENA' ? '*** NO CONFIGURADA ***' : str_repeat('*', strlen(SMTP_PASS))) . "\n\n";

if (SMTP_PASS === 'CAMBIAR_CONTRASENA') {
    echo "ERROR: Configura SMTP_PASS en config.php primero.\n";
    exit;
}

// Email de prueba — cambia el destinatario
$destinatario = $_GET['to'] ?? '';
if (!$destinatario) {
    echo "Uso: ?to=tu@email.com\n";
    echo "Ejemplo: https://cotiza.cloud/test_email.php?to=tu@email.com\n";
    exit;
}

echo "Enviando email de prueba a: {$destinatario}\n\n";

$resultado = Mailer::enviar(
    $destinatario,
    'Test Usuario',
    'Test de email — CotizaCloud',
    '<h2 style="color:#1a5c38">Funciona!</h2>
     <p>Este es un email de prueba del sistema de notificaciones de CotizaCloud.</p>
     <p>Si recibes esto, el SMTP está configurado correctamente.</p>
     <p style="color:#6a6a64;font-size:13px">Enviado: ' . date('Y-m-d H:i:s') . '</p>'
);

if ($resultado) {
    echo "EXITO: Email enviado correctamente.\n";
    echo "Revisa tu bandeja de entrada (y spam).\n";
} else {
    echo "ERROR: No se pudo enviar el email.\n";
    echo "Revisa los logs de error para más detalles.\n";
}
echo "</pre>\n";
