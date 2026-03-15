<?php
// ============================================================
//  CotizaApp — core/Auth.php
//  Sesión en DB, login, permisos, middleware
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

        // Cargar empresa por subdominio
        $slug_empresa = detectar_empresa_slug();

        if ($slug_empresa !== null) {
            self::$empresa = DB::row(
                "SELECT * FROM empresas WHERE slug = ? AND activa = 1",
                [$slug_empresa]
            );

            if (!self::$empresa) {
                // Subdominio no existe o licencia suspendida
                self::empresa_inactiva($slug_empresa);
            }

            define('EMPRESA_ID',   (int) self::$empresa['id']);
            define('EMPRESA_SLUG', self::$empresa['slug']);
        } else {
            // Dominio raíz — sitio público / registro
            define('EMPRESA_ID',   0);
            define('EMPRESA_SLUG', '');
        }

        // Cargar usuario si hay token en cookie
        $token = $_COOKIE[SESSION_NAME] ?? null;
        if ($token && EMPRESA_ID > 0) {
            self::cargar_usuario_por_token($token);
        }
    }

    // ─── Login ───────────────────────────────────────────────
    public static function login(string $usuario_str, string $password): array
    {
        if (EMPRESA_ID === 0) {
            return ['ok' => false, 'error' => 'Empresa no identificada'];
        }

        $usuario = DB::row(
            "SELECT * FROM usuarios
             WHERE empresa_id = ? AND email = ? AND activo = 1",
            [EMPRESA_ID, trim($usuario_str)]
        );

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            return ['ok' => false, 'error' => 'Usuario o contraseña incorrectos'];
        }

        // Crear sesión en DB
        $token     = generar_token(32);
        $expira    = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        $ip        = ip_real();
        $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';

        DB::insert(
            "INSERT INTO user_sessions (usuario_id, empresa_id, token, ip, user_agent, expires_at)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$usuario['id'], EMPRESA_ID, $token, $ip, $ua, $expira]
        );

        // Actualizar ultimo_login
        DB::execute(
            "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?",
            [$usuario['id']]
        );

        // Setear cookie
        setcookie(SESSION_NAME, $token, [
            'expires'  => time() + SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '.' . BASE_DOMAIN,
            'secure'   => !DEBUG,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        self::$usuario = $usuario;

        return ['ok' => true, 'usuario' => $usuario];
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
        session_destroy();
    }

    // ─── Cargar usuario por token ────────────────────────────
    private static function cargar_usuario_por_token(string $token): void
    {
        $sesion = DB::row(
            "SELECT s.*, u.*
             FROM user_sessions s
             JOIN usuarios u ON u.id = s.usuario_id
             WHERE s.token = ?
               AND s.empresa_id = ?
               AND s.expires_at > NOW()
               AND u.activo = 1",
            [$token, EMPRESA_ID]
        );

        if ($sesion) {
            self::$usuario = $sesion;
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
        return self::$usuario && self::$usuario['rol'] === 'admin';
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
            http_response_code(403);
            // TODO: renderizar vista 403
            die('Acceso denegado');
        }
    }

    // ─── Middleware: verificar permiso específico ────────────
    public static function requerir_permiso(string $permiso): void
    {
        self::requerir_login();
        if (!self::puede($permiso)) {
            http_response_code(403);
            die('No tienes permisos para esta acción');
        }
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
            // TODO: render 404 bonito
            die('Empresa no encontrada');
        }

        // Existe pero inactiva
        http_response_code(402);
        // TODO: render vista "licencia suspendida"
        die('Licencia suspendida. Contacta a soporte.');
    }

    // ─── Limpiar sesiones expiradas (llamar desde cron) ──────
    public static function limpiar_sesiones_expiradas(): int
    {
        return DB::execute("DELETE FROM user_sessions WHERE expires_at < NOW()");
    }
}
