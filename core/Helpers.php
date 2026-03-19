<?php
// ============================================================
//  CotizaApp — core/Helpers.php
//  Funciones utilitarias globales
// ============================================================

defined('COTIZAAPP') or die;

// ─── Slugs ───────────────────────────────────────────────────
function slug(string $texto): string
{
    $texto = mb_strtolower(trim($texto), 'UTF-8');

    // Transliterar acentos y caracteres especiales
    $mapa = [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
        'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u',
        'â'=>'a','ê'=>'e','î'=>'i','ô'=>'o','û'=>'u',
        'ñ'=>'n','ç'=>'c','ß'=>'ss','ø'=>'o','å'=>'a',
        'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u',
        'Ñ'=>'n',
    ];
    $texto = strtr($texto, $mapa);

    // Solo alfanumérico y guión
    $texto = preg_replace('/[^a-z0-9\s\-]/', '', $texto);
    $texto = preg_replace('/[\s\-]+/', '-', $texto);
    return trim($texto, '-');
}

// Slug único en tabla/columna para la empresa dada
function slug_unico(string $base, string $tabla, string $columna, int $empresa_id, ?int $excluir_id = null): string
{
    $base   = slug($base);
    $slug   = $base;
    $suffix = 2;

    while (true) {
        $sql    = "SELECT id FROM `$tabla` WHERE empresa_id = ? AND `$columna` = ?";
        $params = [$empresa_id, $slug];

        if ($excluir_id !== null) {
            $sql    .= " AND id != ?";
            $params[] = $excluir_id;
        }

        $existe = DB::val($sql, $params);

        if (!$existe) {
            break;
        }

        $slug = $base . '-' . $suffix;
        $suffix++;
    }

    return $slug;
}

// ─── Tokens seguros ──────────────────────────────────────────
function generar_token(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));   // 64 chars hex
}

function generar_session_key(): string
{
    return bin2hex(random_bytes(16));       // 32 chars hex
}

// ─── Formato dinero ──────────────────────────────────────────
function format_money(float|int|string $monto, string $moneda = 'MXN'): string
{
    $n = number_format((float) $monto, 2, '.', ',');

    return match ($moneda) {
        'USD'   => "USD $n",
        'EUR'   => "€$n",
        default => "\$$n",          // MXN y otros
    };
}

// ─── Fechas ──────────────────────────────────────────────────
function fecha_humana(?string $datetime, bool $con_hora = false): string
{
    if (empty($datetime)) return '—';

    $ts = strtotime($datetime);
    if ($ts === false) return '—';

    $meses = ['','ene','feb','mar','abr','may','jun',
              'jul','ago','sep','oct','nov','dic'];
    $d = (int) date('j', $ts);
    $m = $meses[(int) date('n', $ts)];
    $a = date('Y', $ts);

    $base = "$d $m $a";

    if ($con_hora) {
        $base .= ' ' . date('H:i', $ts);
    }

    return $base;
}

function tiempo_relativo(?string $datetime): string
{
    if (empty($datetime)) return '—';

    $ts   = strtotime($datetime);
    $diff = time() - $ts;

    if ($diff < 60)           return 'hace un momento';
    if ($diff < 3600)         return 'hace ' . (int)($diff/60) . ' min';
    if ($diff < 86400)        return 'hace ' . (int)($diff/3600) . ' h';
    if ($diff < 86400 * 7)   return 'hace ' . (int)($diff/86400) . ' días';
    if ($diff < 86400 * 30)  return 'hace ' . (int)($diff/86400/7) . ' sem';
    if ($diff < 86400 * 365) return 'hace ' . (int)($diff/86400/30) . ' meses';

    return 'hace ' . (int)($diff/86400/365) . ' años';
}

// ─── Seguridad / Output ──────────────────────────────────────
function e(mixed $val): string
{
    return htmlspecialchars((string)($val ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ej(mixed $val): string
{
    return json_encode($val, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

// ─── CSRF ────────────────────────────────────────────────────
function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = generar_token(32);
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

function csrf_verify(): bool
{
    $token = $_POST[CSRF_TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return hash_equals(csrf_token(), $token);
}

function csrf_check(): void
{
    if (!csrf_verify()) {
        $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        if ($is_ajax) {
            http_response_code(419);
            header('Content-Type: application/json');
            die(json_encode(['ok' => false, 'error' => 'Token inválido, recarga la página']));
        }
        flash('error', 'Sesión expirada, intenta de nuevo.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}

// ─── Input sanitizing ────────────────────────────────────────
function input(string $key, string $default = ''): string
{
    return trim($_POST[$key] ?? $_GET[$key] ?? $default);
}

function input_int(string $key, int $default = 0): int
{
    return (int) ($input = input($key)) !== '' ? (int)$input : $default;
}

function input_float(string $key, float $default = 0.0): float
{
    return (float) ($input = input($key)) !== '' ? (float)$input : $default;
}

// ─── JSON response ───────────────────────────────────────────
function json_ok(mixed $data = [], string $msg = ''): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'msg' => $msg, 'data' => $data]);
    exit;
}

function json_error(string $msg, int $code = 400, mixed $data = []): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $msg, 'data' => $data]);
    exit;
}

// ─── Redirect ────────────────────────────────────────────────
function redirect(string $url, int $code = 302): never
{
    header('Location: ' . $url, true, $code);
    exit;
}

function redirect_back(string $fallback = '/'): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    redirect(!empty($ref) ? $ref : $fallback);
}

// ─── Flash messages ──────────────────────────────────────────
function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

// ─── Paginación ──────────────────────────────────────────────
function paginar(int $total, int $pagina, int $por_pagina = 20): array
{
    $total_pags = max(1, (int) ceil($total / $por_pagina));
    $pagina     = max(1, min($pagina, $total_pags));
    $offset     = ($pagina - 1) * $por_pagina;

    return [
        'total'       => $total,
        'por_pagina'  => $por_pagina,
        'pagina'      => $pagina,
        'total_pags'  => $total_pags,
        'offset'      => $offset,
        'hay_prev'    => $pagina > 1,
        'hay_next'    => $pagina < $total_pags,
    ];
}

// ─── IP real del visitante ───────────────────────────────────
function ip_real(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

// ─── Detección de bots ───────────────────────────────────────
function es_bot(?string $ua = null): bool
{
    $ua = strtolower($ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    // User-agent vacío o muy corto = bot o herramienta
    if (strlen($ua) < 10) return true;

    $bots = [
        // Crawlers y buscadores
        'bot','crawl','spider','slurp','scraper','scanner',
        'googlebot','bingbot','yandex','baidu','duckduck',
        'ahrefsbot','semrushbot','mj12bot','dotbot','rogerbot',
        // Redes sociales (previews)
        'facebookexternalhit','whatsapp','telegram','twitterbot',
        'linkedinbot','slackbot','discordbot','skype','iframely',
        'opengraph','embedly','prerender',
        // Herramientas de desarrollo y testing
        'curl','wget','python-requests','python-urllib',
        'java/','ruby','go-http','axios','postman','insomnia',
        'httpie','pycurl','libwww','guzzle','okhttp',
        // Herramientas de performance y monitoreo
        'lighthouse','pagespeed','gtmetrix','pingdom','uptimerobot',
        'newrelic','datadog','zabbix','nagios','statuspage',
        // Headless / automatización
        'headlesschrome','phantomjs','selenium','puppeteer',
        'playwright','nightwatch','cypress',
    ];

    foreach ($bots as $b) {
        if (str_contains($ua, $b)) return true;
    }

    return false;
}

// ─── Validaciones básicas ────────────────────────────────────
function validar_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validar_url(string $url): bool
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// ─── Trial / Plan ───────────────────────────────────────────
define('TRIAL_LIMIT', 100);

function trial_info(int $empresa_id): array
{
    // Auto-migrar columna plan si no existe
    static $migrated = false;
    if (!$migrated) {
        try {
            DB::execute("ALTER TABLE empresas ADD COLUMN plan ENUM('trial','pro') NOT NULL DEFAULT 'trial'");
        } catch (\PDOException $e) {
            // Columna ya existe — OK
        }
        $migrated = true;
    }

    $plan = DB::val("SELECT plan FROM empresas WHERE id = ?", [$empresa_id]) ?: 'trial';
    $usadas = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id = ?", [$empresa_id]);

    return [
        'plan'       => $plan,
        'es_trial'   => $plan === 'trial',
        'usadas'     => $usadas,
        'limite'     => TRIAL_LIMIT,
        'restantes'  => max(0, TRIAL_LIMIT - $usadas),
        'agotado'    => $plan === 'trial' && $usadas >= TRIAL_LIMIT,
        'cerca'      => $plan === 'trial' && $usadas >= (TRIAL_LIMIT * 0.8),
        'pct'        => $plan === 'trial' ? min(100, round($usadas / TRIAL_LIMIT * 100)) : 0,
    ];
}

// ─── Upload de archivos ──────────────────────────────────────
function upload_archivo(array $file, int $empresa_id, string $sub = 'adjuntos'): array
{
    $max_bytes  = MAX_UPLOAD_MB * 1024 * 1024;
    $permitidos = ['jpg','jpeg','png','gif','pdf','doc','docx','xls','xlsx','zip'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Error al subir archivo'];
    }

    if ($file['size'] > $max_bytes) {
        return ['ok' => false, 'error' => 'Archivo mayor a ' . MAX_UPLOAD_MB . 'MB'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $permitidos)) {
        return ['ok' => false, 'error' => 'Tipo de archivo no permitido'];
    }

    $dir = UPLOADS_PATH . '/' . $empresa_id . '/' . $sub;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $nombre_disco = bin2hex(random_bytes(16)) . '.' . $ext;
    $destino      = $dir . '/' . $nombre_disco;

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        return ['ok' => false, 'error' => 'No se pudo guardar el archivo'];
    }

    return [
        'ok'             => true,
        'nombre_original' => $file['name'],
        'nombre_archivo'  => $empresa_id . '/' . $sub . '/' . $nombre_disco,
        'mime_type'       => $file['type'],
        'tamano_bytes'    => $file['size'],
        'url'             => UPLOADS_URL . '/' . $empresa_id . '/' . $sub . '/' . $nombre_disco,
    ];
}
