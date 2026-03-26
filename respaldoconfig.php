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

// ─── Detección de empresa por subdominio o dominio custom ────
// empresa.cotiza.cloud       → slug = 'empresa'
// hmo.ontimecocinas.com      → slug (buscado por dominio_custom en BD)
// cotiza.cloud               → slug = null (sitio principal)
function detectar_empresa_slug(): ?string
{
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    $host = preg_replace('/:\d+$/', '', $host);        // quitar puerto si existe
    $base = strtolower(BASE_DOMAIN);

    // Es el dominio raíz o www
    if ($host === $base || $host === 'www.' . $base) {
        return null;
    }

    // Es un subdominio de cotiza.cloud
    if (str_ends_with($host, '.' . $base)) {
        $sub = substr($host, 0, strlen($host) - strlen('.' . $base));
        // Solo un nivel de subdominio, solo alfanumérico y guión
        if (preg_match('/^[a-z0-9][a-z0-9\-]{1,58}[a-z0-9]$/', $sub)) {
            return $sub;
        }
    }

    // Dominio custom (ej: hmo.ontimecocinas.com) → buscar en BD
    // Se hace antes del bootstrap de DB, así que usamos conexión directa
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare("SELECT slug FROM empresas WHERE dominio_custom = ? AND activa = 1 LIMIT 1");
        $stmt->execute([$host]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Guardar el dominio custom para uso posterior en generación de URLs
            define('DOMINIO_CUSTOM', $host);
            return $row['slug'];
        }
    } catch (Throwable $e) {
        // Si falla la consulta, continuar sin dominio custom
    }

    return null;
}

// ─── Bootstrap inicial ───────────────────────────────────────
require_once ROOT_PATH . '/core/DB.php';
require_once ROOT_PATH . '/core/Helpers.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/modules/radar/Radar.php';
