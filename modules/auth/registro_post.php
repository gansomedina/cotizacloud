<?php
// ============================================================
//  CotizaApp — modules/auth/registro_post.php
//  POST /registro — Procesa la creación de nueva empresa
// ============================================================

defined('COTIZAAPP') or die;

// Bloquear registro desde app nativa (Apple Guideline 3.1.1)
if (str_contains($_SERVER['HTTP_USER_AGENT'] ?? '', 'CotizaCloud')) {
    redirect('/login');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/registro');
}

// CSRF
csrf_check();

// Solo en dominio raíz
if (EMPRESA_ID > 0) {
    redirect('/');
}

// ─── Anti-spam: Honeypot ─────────────────────────────────
if (!empty($_POST['website_url'])) {
    // Bot detected — fail silently (redirect as if success)
    sleep(2);
    redirect('/login?nuevo=1&empresa=demo&u=bot');
}

// ─── Anti-spam: Rate limit (3 registros por IP por hora) ─
$rate = rate_check('registro', 3, 60);
if (!$rate['ok']) {
    $_SESSION['registro_errores'] = ['general' => $rate['error']];
    redirect('/registro');
}
rate_hit('registro');

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

if (strlen($password) < 8)
    $errores['password'] = 'La contraseña debe tener al menos 8 caracteres';

if (!in_array($moneda, ['MXN','USD','EUR']))                 $moneda = 'MXN';
if (!in_array($impuesto_modo, ['ninguno','suma','incluido'])) $impuesto_modo = 'ninguno';
if ($impuesto_pct < 0 || $impuesto_pct > 100)               $impuesto_pct = 16.00;

if (!empty($errores)) {
    $_SESSION['registro_errores'] = $errores;
    $_SESSION['registro_valores'] = $valores;
    redirect('/registro');
}

// ─── Auto-crear tabla si no existe ──────────────────────
try {
    DB::execute("SELECT 1 FROM email_verificacion LIMIT 0");
} catch (\PDOException $e) {
    DB::execute("CREATE TABLE IF NOT EXISTS email_verificacion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        empresa_id INT DEFAULT NULL,
        codigo VARCHAR(10) NOT NULL,
        intentos TINYINT NOT NULL DEFAULT 0,
        verificado TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        INDEX idx_email (email),
        INDEX idx_codigo (codigo),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// ─── Generar código de verificación ─────────────────────
$codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Invalidar códigos previos para este email
DB::execute("UPDATE email_verificacion SET verificado = 1 WHERE email = ? AND verificado = 0", [$email]);

DB::execute(
    "INSERT INTO email_verificacion (email, codigo, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))",
    [$email, $codigo]
);

// ─── Guardar datos de registro en sesión (pendiente verificación) ──
$_SESSION['registro_pendiente'] = [
    'nombre_empresa' => $nombre_empresa,
    'slug'           => $slug_raw,
    'nombre'         => $nombre,
    'email'          => $email,
    'password'       => $password,
    'moneda'         => $moneda,
    'impuesto_modo'  => $impuesto_modo,
    'impuesto_pct'   => $impuesto_pct,
];

// ─── Enviar código por email ────────────────────────────
Mailer::enviar_verificacion($email, $nombre, $codigo);

// ─── Redirigir a verificación de email ──────────────────
redirect('/verificar-email');
