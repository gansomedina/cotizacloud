<?php
// ============================================================
//  CotizaApp — core/Router.php
//  Routing simple por path → módulos
//  v2: Panel centralizado en dominio raíz + subdominios para URLs públicas
// ============================================================

defined('COTIZAAPP') or die;

class Router
{
    private static $routes = [];
    private static $path   = '';

    // ─── Registrar rutas ─────────────────────────────────────
    public static function get(string $pattern, callable $handler): void
    {
        self::$routes[] = ['GET', $pattern, $handler];
    }

    public static function post(string $pattern, callable $handler): void
    {
        self::$routes[] = ['POST', $pattern, $handler];
    }

    public static function any(string $pattern, callable $handler): void
    {
        self::$routes[] = ['ANY', $pattern, $handler];
    }

    // ─── Despachar ───────────────────────────────────────────
    public static function dispatch(): void
    {
        self::$path   = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $method       = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes as [$rMethod, $pattern, $handler]) {
            if ($rMethod !== 'ANY' && $rMethod !== $method) {
                continue;
            }

            $params = self::match_pattern($pattern, self::$path);

            if ($params !== null) {
                call_user_func($handler, $params);
                return;
            }
        }

        // 404
        self::not_found();
    }

    // ─── Match de patrón ─────────────────────────────────────
    private static function match_pattern(string $pattern, string $path): ?array
    {
        $regex = preg_replace('/\/:([a-zA-Z_]+)/', '/(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $k => $v) {
            if (is_string($k)) {
                $params[$k] = $v;
            }
        }

        return $params;
    }

    // ─── Rutas del sistema ───────────────────────────────────
    public static function register_all(): void
    {
        if (IS_SUBDOMAIN) {
            // ══ SUBDOMINIO: solo URLs públicas + API ══════════════
            self::get('/c/:slug',  fn($p) => self::load_public('cotizacion', $p));
            self::get('/v/:slug',  fn($p) => self::load_public('venta',      $p));
            self::get('/r/:token', fn($p) => self::load_public('recibo',     $p));

            self::post('/api/track',        fn() => self::load_api('track'));
            self::post('/api/quote-action', fn() => self::load_api('quote_action'));
            self::post('/api/push/register',   fn() => self::load_api('push_register'));
            self::post('/api/push/unregister', fn() => self::load_api('push_unregister'));

            // Backward compat: si alguien accede al subdominio, redirigir al login centralizado
            self::get('/login', fn() => redirect(BASE_URL . '/login'));
            self::get('/',      fn() => redirect(BASE_URL . '/login'));

            self::not_found_handler(fn() => redirect(BASE_URL));
            return;
        }

        // ══ DOMINIO RAÍZ: login + registro + panel ═══════════

        // ── Auth (público) ─────────────────────────────────
        self::get('/login',    fn() => self::load('auth', 'login'));
        self::post('/login',   fn() => self::load('auth', 'login_post'));
        self::get('/logout',   fn() => self::load('auth', 'logout'));
        self::get('/registro', fn() => self::load('auth', 'registro'));
        self::post('/registro', fn() => self::load('auth', 'registro_post'));
        self::get('/recuperar',       fn() => self::load('auth', 'recuperar'));
        self::post('/recuperar',      fn() => self::load('auth', 'recuperar_post'));
        self::get('/reset-password',  fn() => self::load('auth', 'reset_password'));
        self::post('/reset-password', fn() => self::load('auth', 'reset_password_post'));
        self::get('/verificar-email', fn() => self::load('auth', 'verificar_email'));
        self::post('/verificar-email',fn() => self::load('auth', 'verificar_email_post'));

        // Landing page pública
        self::get('/landing', fn() => Auth::logueado()
            ? redirect('/dashboard')
            : self::load('auth', 'landing')
        );

        // Raíz: dashboard si logueado, login si no
        self::get('/', fn() => Auth::logueado()
            ? redirect('/dashboard')
            : redirect('/login')
        );

        // ── Push notifications API ───────────────────────────
        self::post('/api/push/register',   fn() => self::load_api('push_register'));
        self::post('/api/push/unregister', fn() => self::load_api('push_unregister'));

        // ── Páginas legales (público) ───────────────────────
        self::get('/privacidad', fn() => self::load_public('privacidad'));

        // ── Solicitar licencia (público — desde página de empresa suspendida) ──
        self::post('/solicitar-licencia', fn() => self::load('auth', 'solicitar_licencia'));

        // ── Fallback: URLs públicas en dominio raíz → redirigir al subdominio ──
        self::get('/c/:slug', fn($p) => self::redirect_to_subdomain('cotizaciones', 'slug', $p['slug'], '/c/'));
        self::get('/v/:slug', fn($p) => self::redirect_to_subdomain('ventas',       'slug', $p['slug'], '/v/'));
        self::get('/r/:token',fn($p) => self::redirect_to_subdomain('recibos',      'token',$p['token'],'/r/'));

        // ── App (requiere login) ───────────────────────────
        self::get('/dashboard',               fn()  => self::app('dashboard',    'index'));

        self::get('/cotizaciones',            fn()  => self::app('cotizaciones', 'lista'));
        self::get('/cotizaciones/nueva',      fn()  => self::app('cotizaciones', 'nueva'));
        self::post('/cotizaciones/nueva',     fn()  => self::app('cotizaciones', 'crear'));
        self::get('/cotizaciones/:id',        fn($p)=> self::app('cotizaciones', 'ver',    $p));
        self::post('/cotizaciones/:id',       fn($p)=> self::app('cotizaciones', 'guardar', $p));
        self::post('/cotizaciones/:id/cliente',fn($p)=> self::app('cotizaciones', 'asignar_cliente', $p));
        self::post('/cotizaciones/:id/enviar',fn($p)=> self::app('cotizaciones', 'enviar',  $p));
        self::post('/cotizaciones/:id/convertir', fn($p) => self::app('cotizaciones', 'convertir', $p));
        self::post('/cotizaciones/:id/eliminar',  fn($p) => self::app('cotizaciones', 'eliminar',  $p));
        self::post('/cotizaciones/:id/suspender', fn($p) => self::app('cotizaciones', 'suspender', $p));
        self::post('/cotizaciones/:id/adjuntos',       fn($p) => self::app('cotizaciones', 'adjuntos', $p));
        self::post('/cotizaciones/:id/adjuntos/quitar', fn($p) => self::app('cotizaciones', 'adjuntos', $p));

        self::get('/clientes',               fn()   => self::app('clientes', 'lista'));
        self::post('/clientes',              fn()   => self::app('clientes', 'crear'));
        self::get('/clientes/:id',           fn($p) => self::app('clientes', 'ver',      $p));
        self::post('/clientes/:id',          fn($p) => self::app('clientes', 'guardar',  $p));
        self::post('/clientes/:id/eliminar', fn($p) => self::app('clientes', 'eliminar', $p));

        self::get('/ventas',                          fn()   => self::app('ventas', 'lista'));
        self::get('/ventas/recibos/:id',              fn($p) => self::app('ventas', 'recibo',         $p));
        self::post('/ventas/recibos/:id/cancelar',    fn($p) => self::app('ventas', 'cancelar_recibo',$p));
        self::get('/ventas/:id',                      fn($p) => self::app('ventas', 'ver',            $p));
        self::post('/ventas/:id/abono',               fn($p) => self::app('ventas', 'abono',          $p));
        self::post('/ventas/:id/estado',              fn($p) => self::app_extra('ventas', 'acciones', $p, ['accion'=>'estado']));
        self::post('/ventas/:id/cancelar',            fn($p) => self::app_extra('ventas', 'acciones', $p, ['accion'=>'cancelar']));
        self::post('/ventas/:id/agregar-item',        fn($p) => self::app_extra('ventas', 'acciones', $p, ['accion'=>'agregar-item']));
        self::post('/ventas/:id/notas',               fn($p) => self::app_extra('ventas', 'acciones', $p, ['accion'=>'notas']));
        self::post('/ventas/:id/descuento',           fn($p) => self::app_extra('ventas', 'acciones', $p, ['accion'=>'descuento']));
        self::post('/ventas/:id/agregar-item',        fn($p) => self::app_extra('ventas', 'acciones', $p, ['accion'=>'agregar-item']));
        self::post('/ventas/:id/editar-linea',        fn($p) => self::app_extra('ventas', 'acciones', $p, ['accion'=>'editar-linea']));
        self::post('/ventas/:id/agregar-extra',       fn($p) => self::app('ventas', 'agregar_extra', $p));
        self::post('/ventas/:id/eliminar-extra',      fn($p) => self::app('ventas', 'eliminar_extra', $p));
        self::post('/ventas/:id/cliente',             fn($p) => self::app_extra('ventas', 'acciones', $p, ['accion'=>'cliente']));
        self::post('/ventas/:id/guardar',             fn($p) => self::app_extra('ventas', 'guardar', $p));

        self::get('/radar',                  fn()   => self::app('radar',       'index'));

        // ── Super Admin ──────────────────────────────────
        self::get('/superadmin',                     fn()   => self::superadmin('index'));
        self::get('/superadmin/empresa/:id',         fn($p) => self::superadmin('empresa', $p));
        self::post('/superadmin/impersonar',         fn()   => self::superadmin('impersonar'));
        self::post('/superadmin/empresa/:id/toggle', fn($p) => self::superadmin('toggle_empresa', $p));
        self::post('/superadmin/empresa/:id/plan',    fn($p) => self::superadmin('toggle_plan', $p));
        self::post('/superadmin/empresa/:id/dominio', fn($p) => self::superadmin('dominio_custom', $p));
        self::post('/superadmin/ticket/:id/estado',   fn($p) => self::superadmin('ticket_estado', $p));

        self::get('/proveedores',                          fn()   => self::app('proveedores', 'lista'));
        self::post('/proveedores',                         fn()   => self::app('proveedores', 'crear'));
        self::get('/proveedores/:id',                      fn($p) => self::app('proveedores', 'ver',    $p));
        self::post('/proveedores/:id',                     fn($p) => self::app('proveedores', 'crear',  $p));
        self::post('/proveedores/:id/toggle',              fn($p) => self::app('proveedores', 'toggle', $p));

        self::get('/costos',                             fn()   => self::app('costos', 'index'));
        self::get('/costos/:id',                         fn($p) => self::app('costos', 'ver',            $p));
        self::post('/costos/gasto',                      fn()   => self::app('costos', 'nuevo_gasto'));
        self::post('/costos/gasto/:id',                  fn($p) => self::app('costos', 'nuevo_gasto',    $p));
        self::post('/costos/gasto/:id/eliminar',         fn($p) => self::app('costos', 'eliminar_gasto', $p));
        self::post('/costos/categoria',                  fn()   => self::app('costos', 'categoria'));
        self::post('/costos/categoria/:id',              fn($p) => self::app('costos', 'categoria',      $p));
        self::post('/costos/categoria/:id/toggle',       fn($p) => self::app_extra('costos', 'categoria', $p, ['accion'=>'toggle']));

        self::get('/reportes',               fn()   => self::app('reportes',    'index'));
        self::get('/ayuda',                  fn()   => self::app('ayuda',       'index'));
        self::post('/ayuda/ticket',          fn()   => self::app('ayuda',       'ticket'));
        self::get('/licencia',               fn()   => self::app('ayuda',       'licencia'));

        self::get('/config',                              fn()   => self::app('config', 'index'));
        self::post('/config/empresa',                     fn()   => self::app('config', 'guardar_empresa'));
        self::post('/config/logo',                        fn()   => self::app_extra('config', 'logo', [], ['accion'=>'subir']));
        self::post('/config/logo/quitar',                 fn()   => self::app_extra('config', 'logo', [], ['accion'=>'quitar']));
        self::post('/config/articulo',                    fn()   => self::app('config', 'articulo'));
        self::post('/config/articulo/:id',                fn($p) => self::app('config', 'articulo', $p));
        self::post('/config/articulo/:id/eliminar',       fn($p) => self::app_extra('config', 'articulo', $p, ['accion'=>'eliminar']));
        self::post('/config/cupon',                       fn()   => self::app('config', 'cupon'));
        self::post('/config/cupon/:id',                   fn($p) => self::app('config', 'cupon', $p));
        self::post('/config/cupon/:id/eliminar',          fn($p) => self::app_extra('config', 'cupon', $p, ['accion'=>'eliminar']));
        self::post('/config/usuario',                     fn()   => self::app('config', 'usuario'));
        self::post('/config/usuario/:id',                 fn($p) => self::app('config', 'usuario', $p));
        self::post('/config/radar',                       fn()   => self::app('config', 'guardar_radar'));
        self::post('/config/radar/calibrar',              fn()   => self::app('config', 'calibrar_radar'));
        self::post('/config/costos-modo',                  fn()   => self::app('config', 'guardar_costos_modo'));
        self::post('/config/marketing',                    fn()   => self::app('config', 'guardar_marketing'));
        self::post('/config/ip-interna',                  fn()   => self::app_extra('config', 'ip_interna', [],  ['accion'=>'crear']));
        self::post('/config/ip-interna/:id/eliminar',     fn($p) => self::app_extra('config', 'ip_interna', $p, ['accion'=>'eliminar']));
    }

    // ─── Loaders ─────────────────────────────────────────────

    // Vista pública (sin login)
    private static function load_public(string $modulo, array $params = []): void
    {
        $file = PUBLIC_PATH . '/' . $modulo . '.php';
        if (!file_exists($file)) self::not_found();
        extract($params);
        require $file;
    }

    // API endpoint
    private static function load_api(string $endpoint): void
    {
        $file = ROOT_PATH . '/api/' . $endpoint . '.php';
        if (!file_exists($file)) {
            json_error('Endpoint no encontrado', 404);
        }
        require $file;
    }

    // Módulo auth (no requiere login)
    private static function load(string $modulo, string $accion): void
    {
        $file = MODULES_PATH . '/' . $modulo . '/' . $accion . '.php';
        if (!file_exists($file)) self::not_found();
        require $file;
    }

    // Módulo de app con variables extra (ej: accion)
    private static function app_extra(string $modulo, string $archivo, array $params = [], array $extra = []): void
    {
        Auth::requerir_login('/login');

        $file = MODULES_PATH . '/' . $modulo . '/' . $archivo . '.php';
        if (!file_exists($file)) self::not_found();

        extract($params);
        extract($extra);
        require $file;
    }

    // Módulo de app (requiere login)
    private static function app(string $modulo, string $accion, array $params = []): void
    {
        Auth::requerir_login('/login');

        $file = MODULES_PATH . '/' . $modulo . '/' . $accion . '.php';
        if (!file_exists($file)) self::not_found();

        extract($params);
        require $file;
    }

    // Módulo superadmin (requiere rol superadmin)
    private static function superadmin(string $accion, array $params = []): void
    {
        Auth::requerir_superadmin();

        $file = MODULES_PATH . '/superadmin/' . $accion . '.php';
        if (!file_exists($file)) self::not_found();

        extract($params);
        require $file;
    }

    // ─── 404 ─────────────────────────────────────────────────
    private static $not_found_cb = null;

    public static function not_found_handler(callable $cb): void
    {
        self::$not_found_cb = $cb;
    }

    private static function not_found(): void
    {
        if (self::$not_found_cb) {
            call_user_func(self::$not_found_cb);
        }

        http_response_code(404);
        die('Página no encontrada');
    }

    // ─── URL helper ──────────────────────────────────────────
    public static function url(string $path = ''): string
    {
        // Siempre usa dominio raíz para el panel
        return BASE_URL . '/' . ltrim($path, '/');
    }

    // URL pública de cotización (usa subdominio o dominio custom)
    public static function url_publica(string $path = ''): string
    {
        if (EMPRESA_ID > 0 && EMPRESA_SLUG !== '') {
            return url_publica($path);
        }
        return BASE_URL . '/' . ltrim($path, '/');
    }

    public static function path(): string
    {
        return self::$path;
    }

    public static function is(string $pattern): bool
    {
        return self::match_pattern($pattern, self::$path) !== null;
    }

    // ─── Redirect public URL from root domain to correct subdomain/custom domain ──
    private static function redirect_to_subdomain(string $tabla, string $columna, string $valor, string $prefix): void
    {
        $row = DB::row(
            "SELECT e.slug AS empresa_slug, e.dominio_custom
             FROM `$tabla` t
             JOIN empresas e ON e.id = t.empresa_id AND e.activa = 1
             WHERE t.`$columna` = ?
             LIMIT 1",
            [$valor]
        );

        if ($row && !empty($row['empresa_slug'])) {
            $domain = !empty($row['dominio_custom'])
                ? $row['dominio_custom']
                : $row['empresa_slug'] . '.' . BASE_DOMAIN;
            $url = 'https://' . $domain . $prefix . $valor;
            header('Location: ' . $url, true, 301);
            exit;
        }

        self::not_found();
    }
}
