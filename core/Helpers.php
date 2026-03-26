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
        redirect_back('/');
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
    $host = parse_url($ref, PHP_URL_HOST);
    $safe = !empty($ref) && (!$host || $host === ($_SERVER['HTTP_HOST'] ?? ''));
    redirect($safe ? $ref : $fallback);
}

// ─── Rate limiting ────────────────────────────────────────────
/**
 * Verifica si una IP excedió el límite de intentos para una acción.
 * @return array ['ok'=>true] si puede continuar, ['ok'=>false,'error'=>string,'wait'=>int] si bloqueado
 */
function rate_check(string $accion, int $max_intentos = 5, int $ventana_min = 15): array
{
    $ip = ip_real();
    $ventana_seg = $ventana_min * 60;

    // Limpiar intentos viejos (>24h) aprovechando el check
    DB::execute("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

    // Contar intentos en la ventana
    $intentos = (int)DB::val(
        "SELECT COUNT(*) FROM rate_limits
         WHERE ip=? AND accion=? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)",
        [$ip, $accion, $ventana_seg]
    );

    if ($intentos >= $max_intentos) {
        // Calcular cuánto falta para que se libere el primer intento
        $primer = DB::val(
            "SELECT created_at FROM rate_limits
             WHERE ip=? AND accion=? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
             ORDER BY created_at ASC LIMIT 1",
            [$ip, $accion, $ventana_seg]
        );
        $wait = $primer ? max(1, $ventana_seg - (time() - strtotime($primer))) : $ventana_seg;
        $wait_min = (int)ceil($wait / 60);
        return ['ok' => false, 'error' => "Demasiados intentos. Espera {$wait_min} minutos.", 'wait' => $wait];
    }

    return ['ok' => true];
}

/** Registra un intento de rate limit */
function rate_hit(string $accion): void
{
    DB::execute(
        "INSERT INTO rate_limits (ip, accion) VALUES (?, ?)",
        [ip_real(), $accion]
    );
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
define('TRIAL_LIMIT', 25);

function trial_info(int $empresa_id): array
{
    // Auto-migrar columnas plan y plan_vence si no existen
    static $migrated = false;
    if (!$migrated) {
        try {
            DB::execute("ALTER TABLE empresas ADD COLUMN plan ENUM('free','pro','business') NOT NULL DEFAULT 'free'");
        } catch (\PDOException $e) {
            // Columna ya existe — intentar ampliar ENUM si es el antiguo trial/pro
            try {
                DB::execute("ALTER TABLE empresas MODIFY COLUMN plan ENUM('free','pro','business') NOT NULL DEFAULT 'free'");
            } catch (\PDOException $e2) {
                // Ya tiene el ENUM correcto — OK
            }
        }
        // Migrar registros con plan='trial' a 'free'
        try {
            DB::execute("UPDATE empresas SET plan = 'free' WHERE plan = 'trial'");
        } catch (\PDOException $e) {
            // trial ya no existe en ENUM o no hay registros — OK
        }
        try {
            DB::execute("ALTER TABLE empresas ADD COLUMN plan_vence DATE DEFAULT NULL");
        } catch (\PDOException $e) {
            // Columna ya existe — OK
        }
        $migrated = true;
    }

    $row = DB::row("SELECT plan, plan_vence, activa FROM empresas WHERE id = ?", [$empresa_id]);
    $plan = $row['plan'] ?? 'free';
    // Compatibilidad: si aún hay registros con 'trial', tratar como 'free'
    if ($plan === 'trial') $plan = 'free';
    $plan_vence = $row['plan_vence'] ?? null;
    $activa = (int)($row['activa'] ?? 1);
    $es_pagado = in_array($plan, ['pro', 'business']);

    // Auto-suspender si el plan pagado venció
    $vencido = false;
    if ($es_pagado && $plan_vence && $plan_vence < date('Y-m-d')) {
        $vencido = true;
        if ($activa) {
            DB::execute("UPDATE empresas SET activa = 0 WHERE id = ?", [$empresa_id]);
        }
    }

    $usadas = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id = ?", [$empresa_id]);

    // Calcular días restantes de licencia
    $dias_restantes = null;
    if ($es_pagado && $plan_vence && !$vencido) {
        $dias_restantes = (int)((strtotime($plan_vence) - strtotime(date('Y-m-d'))) / 86400);
    }

    $plan_label = match($plan) {
        'free' => 'Free',
        'pro' => 'Pro',
        'business' => 'Business',
        default => 'Free',
    };

    return [
        'plan'            => $plan,
        'plan_label'      => $plan_label,
        'es_free'         => $plan === 'free',
        'es_trial'        => $plan === 'free',  // compatibilidad con código existente
        'es_pro'          => $plan === 'pro',
        'es_business'     => $plan === 'business',
        'es_pagado'       => $es_pagado,
        'usadas'          => $usadas,
        'limite'          => TRIAL_LIMIT,
        'restantes'       => max(0, TRIAL_LIMIT - $usadas),
        'agotado'         => $plan === 'free' && $usadas >= TRIAL_LIMIT,
        'cerca'           => $plan === 'free' && $usadas >= (TRIAL_LIMIT * 0.8),
        'pct'             => $plan === 'free' ? min(100, round($usadas / TRIAL_LIMIT * 100)) : 0,
        'plan_vence'      => $plan_vence,
        'vencido'         => $vencido,
        'dias_restantes'  => $dias_restantes,
        'por_vencer'      => $dias_restantes !== null && $dias_restantes <= 7,
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

// ─── Configuración de notificaciones de la empresa ──────────
function notif_config(int $empresa_id): array
{
    static $cache = [];
    if (isset($cache[$empresa_id])) return $cache[$empresa_id];

    $empresa = DB::row("SELECT notif_config, notif_email_acepta, notif_email_rechaza FROM empresas WHERE id=?", [$empresa_id]);

    // Si hay JSON, usar eso
    $json = $empresa['notif_config'] ?? null;
    if ($json) {
        $cfg = json_decode($json, true) ?: [];
    } else {
        // Compatibilidad con los booleanos anteriores
        $cfg = [
            'cotizacion_aceptada'  => (bool)($empresa['notif_email_acepta'] ?? 1),
            'cotizacion_rechazada' => (bool)($empresa['notif_email_rechaza'] ?? 0),
            'abono_registrado'     => true,
            'radar_alerta'         => true,
        ];
    }

    // Defaults: todo activo si no está definido
    $defaults = [
        'cotizacion_aceptada'  => true,
        'cotizacion_rechazada' => true,
        'abono_registrado'     => true,
        'radar_alerta'         => true,
    ];

    $result = array_merge($defaults, $cfg);
    $cache[$empresa_id] = $result;
    return $result;
}

// ─── Íconos SVG inline (reemplazo de emojis para WebView iOS) ──
function ico(string $name, int $size = 16, string $color = 'currentColor'): string
{
    static $icons = [
        'money'    => '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'check'    => '<polyline points="20 6 9 17 4 12"/>',
        'clock'    => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'link'     => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        'search'   => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'eye'      => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'fire'     => '<path d="M12 23c-3.9 0-7-2.7-7-6.8 0-3.1 1.7-5.1 3.2-7.2.7-1 1.4-2 1.8-3.2.1-.2.3-.4.5-.4s.4.1.5.3c.8 1.6 1 3.3.5 4.8-.1.3.1.5.3.6.2.1.5 0 .6-.2 1.3-2.1 3.6-3.5 3.6-7 0-.3.2-.5.4-.5.2-.1.5 0 .6.2C19.2 7.4 19 11 19 12.5c0 .5 0 1.1.2 1.5.2.5.6.8 1 .5.3-.2.5-.6.5-1 0-.3.2-.5.4-.5.2-.1.5 0 .6.2.5 1.1.3 2.3-.3 3.3C20 19 16.8 23 12 23z"/>',
        'target'   => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'mail'     => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'mailbox'  => '<path d="M22 17H2a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h20a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2z"/><path d="M6 21v-4M18 21v-4M2 10h20"/>',
        'chart'    => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'tag'      => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
        'x'        => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'alert'    => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'zap'      => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10"/>',
        'bulb'     => '<path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/>',
        'edit'     => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'bank'     => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'card'     => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'copy'     => '<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
    ];

    // Colores para puntos de estado (reemplazo de 🔴🟡🟠🟢🔵🟣🔘)
    static $dots = [
        'red'    => '#dc2626',
        'yellow' => '#eab308',
        'orange' => '#ea580c',
        'green'  => '#16a34a',
        'blue'   => '#2563eb',
        'purple' => '#7c3aed',
        'gray'   => '#94a3b8',
    ];

    // Punto de color
    if (isset($dots[$name])) {
        $r = round($size / 2);
        return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'" style="display:inline-block;vertical-align:middle"><circle cx="'.$r.'" cy="'.$r.'" r="'.$r.'" fill="'.$dots[$name].'"/></svg>';
    }

    // Ícono SVG
    if (!isset($icons[$name])) return '<span>?</span>';
    return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle">'.$icons[$name].'</svg>';
}

// ─── Dominio público para URLs de cotizaciones/ventas/recibos ───
// Si la empresa tiene dominio_custom → usa ese dominio
// Si no → usa slug.cotiza.cloud (comportamiento original)
function dominio_publico(): string
{
    // Si estamos en un dominio custom, ya lo sabemos
    if (defined('DOMINIO_CUSTOM') && DOMINIO_CUSTOM) {
        return DOMINIO_CUSTOM;
    }

    // Revisar si la empresa tiene dominio custom en BD
    if (defined('EMPRESA_ID') && EMPRESA_ID > 0) {
        static $cache = null;
        if ($cache === null) {
            $dc = DB::val("SELECT dominio_custom FROM empresas WHERE id = ? LIMIT 1", [EMPRESA_ID]);
            $cache = $dc ?: false;
        }
        if ($cache) {
            return $cache;
        }
    }

    // Fallback: slug.cotiza.cloud
    return (defined('EMPRESA_SLUG') ? EMPRESA_SLUG : '') . '.' . BASE_DOMAIN;
}

function url_publica(string $path = ''): string
{
    return 'https://' . dominio_publico() . '/' . ltrim($path, '/');
}
