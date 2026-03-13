<?php
// ============================================================
//  CotizaApp — modules/auth/registro_post.php
//  POST /registro — Procesa la creación de nueva empresa
// ============================================================

defined('COTIZAAPP') or die;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/registro');
}

// CSRF
csrf_check();

// Solo en dominio raíz
if (EMPRESA_ID > 0) {
    redirect('/');
}

// ─── Recoger y limpiar valores ───────────────────────────
$nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
$slug_raw       = trim(strtolower($_POST['slug'] ?? ''));
$nombre         = trim($_POST['nombre'] ?? '');
$email          = trim(strtolower($_POST['email'] ?? ''));
$password       = $_POST['password'] ?? '';
$moneda         = $_POST['moneda'] ?? 'MXN';
$impuesto_modo  = $_POST['impuesto_modo'] ?? 'ninguno';
$impuesto_pct   = (float)($_POST['impuesto_pct'] ?? 16);

// ─── Validaciones ────────────────────────────────────────
$errores = [];
$valores = compact(
    'nombre_empresa','slug_raw','nombre',
    'email','moneda','impuesto_modo','impuesto_pct'
);
$valores['slug'] = $slug_raw;

if (empty($nombre_empresa) || strlen($nombre_empresa) < 2)
    $errores['nombre_empresa'] = 'Nombre de empresa requerido (mínimo 2 caracteres)';

if (empty($slug_raw)) {
    $errores['slug'] = 'El subdominio es requerido';
} elseif (!preg_match('/^[a-z0-9]{3,60}$/', $slug_raw)) {
    $errores['slug'] = 'Solo letras minúsculas y números. Mínimo 3 caracteres.';
} else {
    $existe = DB::val("SELECT id FROM empresas WHERE slug = ?", [$slug_raw]);
    if ($existe) $errores['slug'] = "El subdominio «{$slug_raw}» ya está en uso. Elige otro.";
}

if (empty($nombre) || strlen($nombre) < 2)
    $errores['nombre'] = 'Tu nombre es requerido';

if (empty($email))
    $errores['email'] = 'El email es requerido';
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errores['email'] = 'Email inválido';

if (strlen($password) < 6)
    $errores['password'] = 'La contraseña debe tener al menos 6 caracteres';

if (!in_array($moneda, ['MXN','USD','EUR']))                 $moneda = 'MXN';
if (!in_array($impuesto_modo, ['ninguno','suma','incluido'])) $impuesto_modo = 'ninguno';
if ($impuesto_pct < 0 || $impuesto_pct > 100)               $impuesto_pct = 16.00;

if (!empty($errores)) {
    $_SESSION['registro_errores'] = $errores;
    $_SESSION['registro_valores'] = $valores;
    redirect('/registro');
}

// ─── Crear empresa y usuario admin ───────────────────────
try {
    DB::beginTransaction();

    $empresa_id = DB::insert(
        "INSERT INTO empresas
         (slug, nombre, moneda, impuesto_modo, impuesto_pct, activa)
         VALUES (?, ?, ?, ?, ?, 1)",
        [$slug_raw, $nombre_empresa, $moneda, $impuesto_modo, $impuesto_pct]
    );

    foreach ([
        ['Material extra',      '#3b82f6'],
        ['Mano de obra',        '#10b981'],
        ['Transporte',          '#8b5cf6'],
        ['Instalación',         '#f59e0b'],
        ['Garantía / servicio', '#06b6d4'],
    ] as [$cat_nombre, $cat_color]) {
        DB::execute(
            "INSERT INTO categorias_costos (empresa_id, nombre, color, activa) VALUES (?, ?, ?, 1)",
            [$empresa_id, $cat_nombre, $cat_color]
        );
    }

    DB::insert(
        "INSERT INTO usuarios
         (empresa_id, nombre, email, password_hash, rol, activo)
         VALUES (?, ?, ?, ?, 'admin', 1)",
        [
            $empresa_id,
            $nombre,
            $email,
            password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        ]
    );

    DB::commit();

} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    $_SESSION['registro_errores'] = ['general' => 'Error al crear la cuenta. Intenta de nuevo.'];
    $_SESSION['registro_valores'] = $valores;
    redirect('/registro');
}

// ─── Redirigir al login del subdominio nuevo ─────────────
$url_login = 'https://' . $slug_raw . '.' . BASE_DOMAIN . '/login?nuevo=1&u=' . urlencode($email);
redirect($url_login);
