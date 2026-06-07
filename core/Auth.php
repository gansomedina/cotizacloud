<?php
// ============================================================
//  CotizaApp — core/Auth.php
//  Sesión en DB, login, permisos, middleware
//  v2: Login centralizado — empresa por sesión, no por subdominio
// ============================================================

defined('COTIZAAPP') or die;

// SESSION_VERSION — bumpear esta constante en config.php cuando se deployan
// cambios que requieran invalidar TODAS las sesiones activas (ej. cambios al
// Escudo, formato de cookies, lógica de auth). Las sesiones creadas antes de
// esta fecha quedan invalidadas automáticamente al cargar Auth::init().
// Por defecto: fecha en el pasado lejano → check inerte si no se define.
defined('SESSION_VERSION') or define('SESSION_VERSION', '2000-01-01');

// SESSION_BROWSER_SECONDS — duración de sesión browser (14 días)
// SESSION_APP_SECONDS — duración de sesión app nativa (30 días, sin cambio)
defined('SESSION_BROWSER_SECONDS') or define('SESSION_BROWSER_SECONDS', 60 * 60 * 24 * 14);
defined('SESSION_APP_SECONDS')     or define('SESSION_APP_SECONDS',     60 * 60 * 24 * 30);

class Auth
{
    private static $usuario  = null;
    private static $empresa  = null;

    // ─── Iniciar sesión PHP + cargar usuario desde cookie ────
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Cookie de sesión PHP — el token real en BD valida la expiración
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_BROWSER_SECONDS,
                'path'     => '/',
                'domain'   => '.' . BASE_DOMAIN,
                'secure'   => !DEBUG,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        // Detectar si estamos en un subdominio
        $slug_empresa = detectar_empresa_slug();
        define('IS_SUBDOMAIN', $slug_empresa !== null);

        if (IS_SUBDOMAIN) {
            // ── Subdominio: cargar empresa por host (URLs públicas /c/, /v/, /r/) ──
            self::$empresa = DB::row(
                "SELECT * FROM empresas WHERE slug = ? AND activa = 1",
                [$slug_empresa]
            );

            if (!self::$empresa) {
                self::empresa_inactiva($slug_empresa);
            }

            define('EMPRESA_ID',   (int) self::$empresa['id']);
            define('EMPRESA_SLUG', self::$empresa['slug']);

            // Cargar usuario si tiene cookie
            $token = $_COOKIE[SESSION_NAME] ?? null;
            if ($token) {
                self::cargar_usuario_por_token($token, EMPRESA_ID);
            }
        } else {
            // ── Dominio raíz: cargar empresa desde sesión del usuario ──
            $token = $_COOKIE[SESSION_NAME] ?? null;
            if ($token) {
                self::cargar_usuario_desde_token_completo($token);
            }

            if (!defined('EMPRESA_ID')) {
                define('EMPRESA_ID',   0);
                define('EMPRESA_SLUG', '');
            }
        }
    }

    // ─── Login (centralizado: recibe slug de empresa) ───────
    public static function login(string $empresa_slug, string $email, string $password, bool $is_app = false): array
    {
        // Buscar empresa por slug
        $empresa = DB::row(
            "SELECT * FROM empresas WHERE slug = ? AND activa = 1",
            [trim($empresa_slug)]
        );

        // Superadmin puede entrar con slug '_admin' directo al panel
        if (!$empresa && trim($empresa_slug) === '_admin') {
            $empresa = DB::row("SELECT * FROM empresas WHERE slug = '_system'");
        }

        if (!$empresa) {
            return ['ok' => false, 'error' => 'Empresa no encontrada'];
        }

        $empresa_id = (int) $empresa['id'];

        $usuario = DB::row(
            "SELECT * FROM usuarios
             WHERE empresa_id = ? AND email = ? AND activo = 1",
            [$empresa_id, trim($email)]
        );

        // Si no existe en esa empresa, buscar superadmin global
        if (!$usuario) {
            $usuario = DB::row(
                "SELECT * FROM usuarios
                 WHERE email = ? AND rol = 'superadmin' AND activo = 1",
                [trim($email)]
            );
        }

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            return ['ok' => false, 'error' => 'Usuario o contraseña incorrectos'];
        }

        $duracion = $is_app ? SESSION_APP_SECONDS : SESSION_BROWSER_SECONDS;

        // Crear sesión en DB
        $token  = generar_token(32);
        $expira = date('Y-m-d H:i:s', time() + $duracion);
        $ip     = ip_real();
        $ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';

        DB::insert(
            "INSERT INTO user_sessions (usuario_id, empresa_id, token, ip, user_agent, expires_at)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$usuario['id'], $empresa_id, $token, $ip, $ua, $expira]
        );

        // Actualizar ultimo_login
        DB::execute(
            "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?",
            [$usuario['id']]
        );

        // Registrar actividad: login
        ActividadScore::registrar((int)$usuario['id'], (int)$usuario['empresa_id'], 'login');

        // Regenerar ID de sesión para prevenir session fixation
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // Setear cookie
        setcookie(SESSION_NAME, $token, [
            'expires'  => time() + $duracion,
            'path'     => '/',
            'domain'   => '.' . BASE_DOMAIN,
            'secure'   => !DEBUG,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        self::$usuario = $usuario;
        self::$empresa = $empresa;

        return ['ok' => true, 'usuario' => $usuario, 'empresa' => $empresa, 'token' => $token];
    }

    // ─── Logout ──────────────────────────────────────────────
    public static function logout(): void
    {
        $token = $_COOKIE[SESSION_NAME] ?? null;
        if ($token) {
            DB::execute("UPDATE user_sessions SET expires_at = NOW() WHERE token = ?", [$token]);
        }

        setcookie(SESSION_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => '.' . BASE_DOMAIN,
            'secure'   => !DEBUG,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        self::$usuario = null;
        self::$empresa = null;
        session_destroy();
    }

    // ─── Cargar usuario por token (con empresa_id conocido) ──
    private static function cargar_usuario_por_token(string $token, int $empresa_id): void
    {
        $sesion = DB::row(
            "SELECT s.*, s.created_at AS session_created_at, u.*
             FROM user_sessions s
             JOIN usuarios u ON u.id = s.usuario_id
             WHERE s.token = ?
               AND (s.empresa_id = ? OR u.rol = 'superadmin')
               AND s.expires_at > NOW()
               AND u.activo = 1",
            [$token, $empresa_id]
        );

        if (!$sesion) return;

        // SESSION_VERSION: rechazar sesiones creadas antes del version-bump
        if (!self::session_version_valida($sesion)) return;

        // Capturar expires_at para que refrescar_actividad respete sesiones
        // largas de app nativa (30d) sin acortarlas a 14d (browser default).
        $current_expires = $sesion['expires_at'] ?? null;

        // Evitar filtrar el token de sesión via Auth::usuario()['token']
        // (preexistente: SELECT s.*, u.* dejaba token en el array).
        unset($sesion['token']);

        self::$usuario = $sesion;
        self::refrescar_actividad($token, $current_expires);
    }

    // ─── Cargar usuario + empresa desde token (dominio raíz) ──
    private static function cargar_usuario_desde_token_completo(string $token): void
    {
        $sesion = DB::row(
            "SELECT s.usuario_id, s.empresa_id, s.expires_at,
                    s.created_at AS session_created_at,
                    u.id, u.nombre, u.email, u.usuario, u.rol, u.activo,
                    u.puede_editar_precios, u.puede_aplicar_descuentos,
                    u.puede_ver_todas_cots, u.puede_ver_todas_ventas,
                    u.puede_eliminar_items_venta, u.puede_cancelar_recibos,
                    u.puede_capturar_pagos, u.puede_asignar_cotizaciones,
                    u.puede_ver_costos, u.puede_ver_proveedores,
                    u.puede_crear_cotizaciones, u.puede_editar_cotizaciones,
                    u.puede_ver_cantidades, u.puede_agregar_extras,
                    u.puede_ver_reportes, u.puede_adjuntar,
                    u.puede_editar_clientes,
                    u.ultimo_login, u.password_hash
             FROM user_sessions s
             JOIN usuarios u ON u.id = s.usuario_id
             WHERE s.token = ?
               AND s.expires_at > NOW()
               AND u.activo = 1",
            [$token]
        );

        if ($sesion && !self::session_version_valida($sesion)) {
            $sesion = null;
        }

        if ($sesion) {
            $empresa_id = (int) $sesion['empresa_id'];
            self::$empresa = DB::row(
                "SELECT * FROM empresas WHERE id = ?",
                [$empresa_id]
            );

            if (self::$empresa) {
                self::$usuario = $sesion;
                define('EMPRESA_ID',   $empresa_id);
                define('EMPRESA_SLUG', self::$empresa['slug']);
            } elseif ($sesion['rol'] === 'superadmin') {
                // Superadmin sin empresa activa — permitir acceso al panel admin
                self::$usuario = $sesion;
                self::$empresa = ['id' => 0, 'slug' => '_system', 'nombre' => 'CotizaCloud Admin'];
                define('EMPRESA_ID',   0);
                define('EMPRESA_SLUG', '_system');
            }

            // Activity refresh — extender expiración si user está logueado
            if (self::$usuario !== null) {
                self::refrescar_actividad($token, $sesion['expires_at'] ?? null);
            }
        }
    }

    // ─── SESSION_VERSION: validar que la sesión no es anterior al bump ────
    // Fail-CLOSED: si SESSION_VERSION es inválida o fecha de sesión no parsea,
    // rechazar la sesión y alertar via error_log. Mejor forzar re-login que
    // dejar pasar silenciosamente algo que el deployer pensaba que invalidaría.
    private static function session_version_valida(array $sesion): bool
    {
        $version_ts = strtotime(SESSION_VERSION);
        if ($version_ts === false) {
            error_log('[Auth] SESSION_VERSION inválida: ' . SESSION_VERSION . ' — fail-closed (rechazando sesión)');
            return false;
        }

        // Si el array no trae el alias (query antigua sin él), pasar para
        // no romper retro-compatibilidad. El check solo aplica si tenemos dato.
        if (!array_key_exists('session_created_at', $sesion) || $sesion['session_created_at'] === null) {
            return true;
        }

        $sesion_ts = strtotime((string)$sesion['session_created_at']);
        if ($sesion_ts === false) {
            // Fecha de sesión malformada — rechazar defensivamente
            return false;
        }
        return $sesion_ts >= $version_ts;
    }

    // ─── Activity refresh: extender sesión en cada request autenticado ────
    // Throttle: solo refresca si queda < mitad del lifetime para no saturar la BD.
    // GREATEST en UPDATE y MAX en cookie preservan sesiones de app nativa (30d).
    // UPDATE filtra por expires_at > NOW() para NO revivir sesiones expiradas
    // por logout o por cron de limpieza (race condition).
    private static function refrescar_actividad(string $token, ?string $current_expires = null): void
    {
        // THROTTLE: si la sesión tiene > 7 días por delante, no refrescar.
        // Asesores activos: 1 refresh por semana en vez de uno por request.
        if ($current_expires !== null) {
            $current_ts = strtotime($current_expires);
            $half_duration = (int)(SESSION_BROWSER_SECONDS / 2);
            if ($current_ts !== false && ($current_ts - time()) > $half_duration) {
                return;
            }
        }

        try {
            // WHERE expires_at > NOW(): no revivir sesiones que ya estaban
            // expiradas (logout, cron limpieza). Race condition cerrada.
            DB::execute(
                "UPDATE user_sessions
                 SET expires_at = GREATEST(expires_at, DATE_ADD(NOW(), INTERVAL ? SECOND))
                 WHERE token = ? AND expires_at > NOW()",
                [SESSION_BROWSER_SECONDS, $token]
            );
        } catch (Throwable $e) {
            // Silencioso — no bloquear request por error de refresh
        }

        // Refrescar cookie solo si headers no enviados
        if (headers_sent()) return;

        // Dominio dinámico — host-only en custom, .cotiza.cloud en subdominio
        $host_cur = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $host_cur = preg_replace('/:\d+$/', '', $host_cur);
        $cookie_domain = (str_ends_with($host_cur, '.' . BASE_DOMAIN) || $host_cur === BASE_DOMAIN)
            ? '.' . BASE_DOMAIN
            : '';

        // Cookie expires: MAX(now + 14d, expires_at de sesión actual). Esto
        // preserva las cookies de app nativa que fueron seteadas a 30d sin
        // acortarlas al refrescarse desde browser default de 14d.
        $cookie_expires = time() + SESSION_BROWSER_SECONDS;
        if ($current_expires !== null) {
            $current_ts = strtotime($current_expires);
            if ($current_ts !== false && $current_ts > $cookie_expires) {
                $cookie_expires = $current_ts;
            }
        }

        setcookie(SESSION_NAME, $token, [
            'expires'  => $cookie_expires,
            'path'     => '/',
            'domain'   => $cookie_domain,
            'secure'   => !DEBUG,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    // ─── Getters ─────────────────────────────────────────────
    public static function usuario(): ?array
    {
        return self::$usuario;
    }

    public static function empresa(): ?array
    {
        return self::$empresa;
    }

    public static function id(): ?int
    {
        return self::$usuario ? (int) self::$usuario['id'] : null;
    }

    public static function logueado(): bool
    {
        return self::$usuario !== null;
    }

    public static function es_admin(): bool
    {
        return self::$usuario && in_array(self::$usuario['rol'], ['admin', 'superadmin'], true);
    }

    public static function es_superadmin(): bool
    {
        return self::$usuario && self::$usuario['rol'] === 'superadmin';
    }

    public static function rol(): string
    {
        return self::$usuario['rol'] ?? '';
    }

    // ─── Permisos granulares ─────────────────────────────────
    public static function puede(string $permiso): bool
    {
        if (!self::$usuario) return false;

        // Admin tiene todos los permisos
        if (self::es_admin()) return true;

        $permisos_validos = [
            'editar_precios',
            'aplicar_descuentos',
            'ver_todas_cots',
            'ver_todas_ventas',
            'eliminar_items_venta',
            'cancelar_recibos',
            'capturar_pagos',
            'asignar_cotizaciones',
            'ver_costos',
            'ver_proveedores',
            'crear_cotizaciones',
            'editar_cotizaciones',
            'ver_cantidades',
            'agregar_extras',
            'ver_reportes',
            'adjuntar',
            'editar_clientes',
        ];

        if (!in_array($permiso, $permisos_validos)) return false;

        return (bool)(self::$usuario['puede_' . $permiso] ?? false);
    }

    // ─── Middleware: requiere login ───────────────────────────
    public static function requerir_login(string $redirect = '/login'): void
    {
        if (!self::logueado()) {
            redirect($redirect);
        }
    }

    // ─── Middleware: requiere admin ───────────────────────────
    public static function requerir_admin(): void
    {
        self::requerir_login();
        if (!self::es_admin()) {
            self::acceso_denegado('Solo los administradores pueden acceder a esta sección.');
        }
    }

    // ─── Middleware: requiere superadmin ───────────────────────
    public static function requerir_superadmin(): void
    {
        self::requerir_login();
        if (!self::es_superadmin()) {
            self::acceso_denegado('Acceso exclusivo para administradores del sistema.');
        }
    }

    // ─── Middleware: verificar permiso específico ────────────
    public static function requerir_permiso(string $permiso): void
    {
        self::requerir_login();
        if (!self::puede($permiso)) {
            self::acceso_denegado('No tienes permisos para esta acción. Contacta a tu administrador.');
        }
    }

    // ─── Página de acceso denegado con estilo ────────────────
    private static function acceso_denegado(string $mensaje): never
    {
        http_response_code(403);
        ?><!DOCTYPE html>
<html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Acceso denegado — cotiza.cloud</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f7f7f5;min-height:100vh;display:flex;align-items:center;justify-content:center}
.box{background:#fff;border:1px solid #e5e5e3;border-radius:16px;padding:40px 32px;max-width:420px;width:90%;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.04)}
.ico{width:56px;height:56px;border-radius:14px;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:26px}
h1{font:800 20px 'DM Sans',sans-serif;color:#1a1a18;margin-bottom:8px}
p{font:400 14px 'DM Sans',sans-serif;color:#6b7280;line-height:1.6;margin-bottom:24px}
.btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.btn{padding:11px 22px;border-radius:10px;font:700 13px 'DM Sans',sans-serif;text-decoration:none;cursor:pointer;transition:all .15s;border:none}
.btn-back{background:#f7f7f5;color:#1a1a18;border:1.5px solid #e5e5e3}
.btn-back:hover{border-color:#2d7a50;color:#2d7a50}
.btn-home{background:#2d7a50;color:#fff}
.btn-home:hover{opacity:.9}
</style></head><body>
<div class="box">
  <div class="ico">🔒</div>
  <h1>Acceso denegado</h1>
  <p><?= htmlspecialchars($mensaje) ?></p>
  <div class="btns">
    <a href="javascript:history.back()" class="btn btn-back">← Regresar</a>
    <a href="/dashboard" class="btn btn-home">Ir al inicio</a>
  </div>
</div>
</body></html><?php
        exit;
    }

    // ─── Registrar nueva empresa + admin ─────────────────────
    public static function registrar(array $datos): array
    {
        // Validaciones
        if (empty($datos['nombre_empresa'])) {
            return ['ok' => false, 'error' => 'Nombre de empresa requerido'];
        }
        if (empty($datos['usuario'])) {
            return ['ok' => false, 'error' => 'Usuario requerido'];
        }
        if (empty($datos['password']) || strlen($datos['password']) < 6) {
            return ['ok' => false, 'error' => 'Contraseña mínimo 6 caracteres'];
        }

        $slug_empresa = slug($datos['nombre_empresa']);
        if (empty($slug_empresa) || strlen($slug_empresa) < 3) {
            return ['ok' => false, 'error' => 'Nombre de empresa inválido'];
        }

        // Verificar slug único
        $existe_slug = DB::val(
            "SELECT id FROM empresas WHERE slug = ?",
            [$slug_empresa]
        );
        if ($existe_slug) {
            $slug_empresa .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        try {
            DB::beginTransaction();

            // Crear empresa
            $empresa_id = DB::insert(
                "INSERT INTO empresas (slug, nombre, moneda, impuesto_modo)
                 VALUES (?, ?, ?, 'ninguno')",
                [$slug_empresa, trim($datos['nombre_empresa']), $datos['moneda'] ?? 'MXN']
            );

            // Insertar folio inicial para COT/VTA/REC
            $anio = (int) date('Y');
            foreach (['COT', 'VTA', 'REC'] as $tipo) {
                DB::execute(
                    "INSERT IGNORE INTO folios (empresa_id, tipo, anio, ultimo) VALUES (?, ?, ?, 0)",
                    [$empresa_id, $tipo, $anio]
                );
            }

            // Crear usuario admin
            DB::insert(
                "INSERT INTO usuarios
                 (empresa_id, nombre, usuario, email, password_hash, rol,
                  puede_editar_precios, puede_aplicar_descuentos, puede_ver_todas_cots,
                  puede_eliminar_items_venta, puede_cancelar_recibos)
                 VALUES (?, ?, ?, ?, ?, 'admin', 1, 1, 1, 1, 1)",
                [
                    $empresa_id,
                    trim($datos['nombre'] ?? $datos['usuario']),
                    trim($datos['usuario']),
                    trim($datos['email'] ?? ''),
                    password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                ]
            );

            DB::commit();

            return [
                'ok'           => true,
                'empresa_id'   => $empresa_id,
                'slug_empresa' => $slug_empresa,
            ];

        } catch (Exception $e) {
            DB::rollback();
            if (DEBUG) throw $e;
            return ['ok' => false, 'error' => 'Error al crear la cuenta'];
        }
    }

    // ─── Empresa inactiva / suspendida ───────────────────────
    private static function empresa_inactiva(string $slug): never
    {
        // Ver si existe pero está inactiva
        $emp = DB::row("SELECT activa, plan, plan_vence FROM empresas WHERE slug = ?", [$slug]);

        if ($emp === null) {
            http_response_code(404);
            die('Empresa no encontrada');
        }

        // Determinar si venció la licencia
        $plan = $emp['plan'] ?? 'free';
        $es_pagado = in_array($plan, ['lite', 'pro', 'business']);
        $plan_label = match($plan) { 'lite' => 'Lite', 'pro' => 'Pro', 'business' => 'Business', default => 'Free' };
        $vencida = ($es_pagado && $emp['plan_vence'] && $emp['plan_vence'] < date('Y-m-d'));
        $titulo = $vencida ? 'Licencia Vencida' : 'Licencia Suspendida';
        $msg = $vencida
            ? 'Tu licencia ' . $plan_label . ' venció el <strong>' . date('d/m/Y', strtotime($emp['plan_vence'])) . '</strong>. Renueva tu plan para continuar usando CotizaCloud.'
            : 'La cuenta <span class="slug">' . htmlspecialchars($slug) . '</span> se encuentra suspendida.';
        $sub = $vencida
            ? 'Contacta a soporte para renovar tu licencia.'
            : 'Para reactivar tu acceso, contacta a nuestro equipo de soporte.';

        // Existe pero inactiva — mostrar pantalla con formulario de solicitud
        $base = rtrim(BASE_URL, '/');
        http_response_code(402);
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">';
        echo '<title>' . $titulo . ' — CotizaCloud</title>';
        echo '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">';
        echo '<style>';
        echo '*{box-sizing:border-box;margin:0;padding:0}';
        echo 'body{font-family:"Plus Jakarta Sans",sans-serif;background:#f4f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}';
        echo '.card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:520px;width:100%;padding:48px 40px;text-align:center}';
        echo '.icon{width:64px;height:64px;border-radius:50%;background:#fff5f5;display:flex;align-items:center;justify-content:center;margin:0 auto 24px}';
        echo '.icon svg{width:32px;height:32px;stroke:#c53030;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}';
        echo 'h1{font-size:22px;font-weight:800;color:#1a1a18;margin-bottom:8px}';
        echo 'p{font-size:14px;color:#4a4a46;line-height:1.6;margin-bottom:20px}';
        echo '.slug{font-family:monospace;background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:13px;color:#475569}';
        echo '.form-section{background:#f8faf9;border:1px solid #e2e8e4;border-radius:12px;padding:24px;margin-top:24px;text-align:left}';
        echo '.form-section h3{font-size:15px;font-weight:700;color:#1a1a18;margin:0 0 4px}';
        echo '.form-section .hint{font-size:12px;color:#6a6a64;margin-bottom:16px}';
        echo '.field{margin-bottom:14px}';
        echo '.field label{display:block;font-size:12px;font-weight:600;color:#4a4a46;margin-bottom:4px}';
        echo '.field select,.field textarea{width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font:400 13px "Plus Jakarta Sans",sans-serif;color:#1a1a18;background:#fff;outline:none}';
        echo '.field select:focus,.field textarea:focus{border-color:#1a5c38}';
        echo '.field textarea{resize:vertical;min-height:60px}';
        echo '.btn-submit{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:11px 24px;border-radius:9px;font:600 14px "Plus Jakarta Sans",sans-serif;background:#1a5c38;color:#fff;border:none;cursor:pointer;transition:opacity .12s}';
        echo '.btn-submit:hover{opacity:.85}';
        echo '.back{display:block;margin-top:16px;font-size:13px;color:#6a6a64;text-decoration:none}';
        echo '.back:hover{color:#1a5c38}';
        echo '</style></head><body>';
        echo '<div class="card">';
        echo '<div class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>';
        echo '<h1>' . $titulo . '</h1>';
        echo '<p>' . $msg . '</p>';

        // Formulario de solicitud de licencia
        echo '<div class="form-section">';
        echo '<h3>Activar licencia</h3>';
        echo '<div class="hint">Selecciona la duración y serás contactado a la brevedad con la liga de cobro.</div>';
        echo '<form method="POST" action="' . $base . '/solicitar-licencia">';
        echo '<input type="hidden" name="slug" value="' . htmlspecialchars($slug) . '">';
        echo '<div class="field"><label>Duración</label><select name="duracion">';
        echo '<option value="1_mes">1 mes</option>';
        echo '<option value="3_meses">3 meses</option>';
        echo '<option value="6_meses">6 meses</option>';
        echo '<option value="1_anio">1 año</option>';
        echo '</select></div>';
        echo '<div class="field"><label>Mensaje (opcional)</label><textarea name="mensaje" placeholder="Información adicional..." rows="3"></textarea></div>';
        echo '<button type="submit" class="btn-submit">Solicitar activación</button>';
        echo '</form>';
        echo '</div>';

        echo '<a href="/login" class="back">Volver al inicio de sesión</a>';
        echo '</div></body></html>';
        exit;
    }

    // ─── Limpiar sesiones expiradas (llamar desde cron) ──────
    public static function limpiar_sesiones_expiradas(): int
    {
        return DB::execute("DELETE FROM user_sessions WHERE expires_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    }
}
