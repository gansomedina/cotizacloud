<?php
// ============================================================
//  CotizaApp — core/Auth.php
//  Sesión en DB, login, permisos, middleware
//  v2: Login centralizado — empresa por sesión, no por subdominio
// ============================================================

defined('COTIZAAPP') or die;

class Auth
{
    private static $usuario  = null;
    private static $empresa  = null;

    // ─── Iniciar sesión PHP + cargar usuario desde cookie ────
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
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
    public static function login(string $empresa_slug, string $email, string $password, bool $recordar = false): array
    {
        // Buscar empresa por slug
        $empresa = DB::row(
            "SELECT * FROM empresas WHERE slug = ? AND activa = 1",
            [trim($empresa_slug)]
        );

        // Superadmin puede entrar con slug '_admin' directo al panel
        if (!$empresa && trim($empresa_slug) === '_admin') {
            $sa_user = DB::row(
                "SELECT * FROM usuarios WHERE email = ? AND rol = 'superadmin' AND activo = 1",
                [trim($email)]
            );
            if ($sa_user && password_verify($password, $sa_user['password_hash'])) {
                $empresa = DB::row("SELECT * FROM empresas WHERE slug = '_system'");
                if (!$empresa) {
                    return ['ok' => false, 'error' => 'Sistema no configurado'];
                }
            }
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

        // Duración: 30 días si "Recordarme", 8 horas normal
        $duracion = $recordar ? (60 * 60 * 24 * 30) : SESSION_LIFETIME;

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

        return ['ok' => true, 'usuario' => $usuario, 'empresa' => $empresa];
    }

    // ─── Logout ──────────────────────────────────────────────
    public static function logout(): void
    {
        $token = $_COOKIE[SESSION_NAME] ?? null;
        if ($token) {
            DB::execute("DELETE FROM user_sessions WHERE token = ?", [$token]);
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
            "SELECT s.*, u.*
             FROM user_sessions s
             JOIN usuarios u ON u.id = s.usuario_id
             WHERE s.token = ?
               AND s.empresa_id = ?
               AND s.expires_at > NOW()
               AND u.activo = 1",
            [$token, $empresa_id]
        );

        if ($sesion) {
            self::$usuario = $sesion;
        }
    }

    // ─── Cargar usuario + empresa desde token (dominio raíz) ──
    private static function cargar_usuario_desde_token_completo(string $token): void
    {
        $sesion = DB::row(
            "SELECT s.usuario_id, s.empresa_id, s.expires_at,
                    u.id, u.nombre, u.email, u.usuario, u.rol, u.activo,
                    u.puede_editar_precios, u.puede_aplicar_descuentos,
                    u.puede_ver_todas_cots, u.puede_ver_todas_ventas,
                    u.puede_eliminar_items_venta, u.puede_cancelar_recibos,
                    u.puede_capturar_pagos,
                    u.ultimo_login, u.password_hash
             FROM user_sessions s
             JOIN usuarios u ON u.id = s.usuario_id
             WHERE s.token = ?
               AND s.expires_at > NOW()
               AND u.activo = 1",
            [$token]
        );

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
        }
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
                    password_hash($datos['password'], PASSWORD_BCRYPT),
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
        $existe = DB::val("SELECT activa FROM empresas WHERE slug = ?", [$slug]);

        if ($existe === null) {
            http_response_code(404);
            die('Empresa no encontrada');
        }

        // Existe pero inactiva
        http_response_code(402);
        die('Licencia suspendida. Contacta a soporte.');
    }

    // ─── Limpiar sesiones expiradas (llamar desde cron) ──────
    public static function limpiar_sesiones_expiradas(): int
    {
        return DB::execute("DELETE FROM user_sessions WHERE expires_at < NOW()");
    }
}
