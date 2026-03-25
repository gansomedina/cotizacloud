<?php
// ============================================================
//  CotizaApp — config.php
//  Configuración global, constantes, detección de empresa
// ============================================================

defined('COTIZAAPP') or define('COTIZAAPP', true);

// ─── Entorno ─────────────────────────────────────────────────
define('ENV',         getenv("APP_ENV") ?: "development");
define('DEBUG',       ENV === 'development');
define('BASE_DOMAIN', getenv('BASE_DOMAIN') ?: 'cotiza.cloud');
define('BASE_URL',    getenv('BASE_URL')    ?: 'https://cotiza.cloud');

// ─── Base de datos ───────────────────────────────────────────
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'cotizacl_cotizacloud');
define('DB_USER', getenv('DB_USER') ?: 'cotizacl_cotizacloud');
define('DB_PASS', getenv('DB_PASS') ?: 'Jalfonso234');

// ─── Seguridad ───────────────────────────────────────────────
define('APP_SECRET',       getenv('APP_SECRET') ?: 'cambiar-en-produccion-32chars');
define('SESSION_NAME',     'cza_session');
define('SESSION_LIFETIME', 60 * 60 * 8);   // 8 horas
define('CSRF_TOKEN_NAME',  'cza_csrf');

// ─── APNs Push Notifications ────────────────────────────────
// Configurar estos valores al tener la cuenta Apple Developer:
// APNS_KEY_PATH: ruta al archivo .p8 descargado de Apple
// APNS_KEY_ID:   Key ID del portal de Apple
// APNS_TEAM_ID:  Team ID de tu cuenta Apple Developer
define('APNS_KEY_PATH',  getenv('APNS_KEY_PATH')  ?: '/home/cotizacl/key/AuthKey_D2AW3CT2UF.p8');
define('APNS_KEY_ID',    getenv('APNS_KEY_ID')    ?: 'D2AW3CT2UF');
define('APNS_TEAM_ID',   getenv('APNS_TEAM_ID')   ?: 'T3LPNPVHZ2');
define('APNS_BUNDLE_ID', getenv('APNS_BUNDLE_ID') ?: 'com.cotizacloud.app');
define('APNS_ENV',       getenv('APNS_ENV')       ?: 'production');

// ─── Email SMTP — definir en config.php del SERVIDOR ────────
// define('SMTP_HOST',     'mail.cotiza.cloud');
// define('SMTP_PORT',     465);
// define('SMTP_SECURE',   'ssl');
// define('SMTP_USER',     'noreply@cotiza.cloud');
// define('SMTP_PASS',     'tu_contraseña');
// define('SMTP_FROM',     'noreply@cotiza.cloud');
// define('SMTP_FROM_NAME','CotizaCloud');

// ─── Paths ───────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__FILE__));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('UPLOADS_PATH', ROOT_PATH . '/public/assets/uploads');
define('UPLOADS_URL',  '/assets/uploads');
define('MAX_UPLOAD_MB', 10);

// ─── Error handling ──────────────────────────────────────────
if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}

// ─── Timezone ────────────────────────────────────────────────
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Hermosillo');

// ─── Autoload ────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $file = ROOT_PATH . '/core/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ─── Detección de empresa por subdominio ─────────────────────
// empresa.cotiza.cloud → slug = 'empresa'
// cotiza.cloud         → slug = null (sitio principal)
function detectar_empresa_slug(): ?string
{
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    $host = preg_replace('/:\d+$/', '', $host);        // quitar puerto si existe
    $base = strtolower(BASE_DOMAIN);

    // Es el dominio raíz o www
    if ($host === $base || $host === 'www.' . $base) {
        return null;
    }

    // Es un subdominio
    if (str_ends_with($host, '.' . $base)) {
        $sub = substr($host, 0, strlen($host) - strlen('.' . $base));
        // Solo un nivel de subdominio, solo alfanumérico y guión
        if (preg_match('/^[a-z0-9][a-z0-9\-]{1,58}[a-z0-9]$/', $sub)) {
            return $sub;
        }
    }

    return null;
}

// ─── Bootstrap inicial ───────────────────────────────────────
require_once ROOT_PATH . '/core/DB.php';
require_once ROOT_PATH . '/core/Helpers.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/VentaLog.php';
require_once ROOT_PATH . '/core/ActividadScore.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/modules/radar/Radar.php';
