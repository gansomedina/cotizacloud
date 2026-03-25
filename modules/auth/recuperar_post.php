<?php
// ============================================================
//  CotizaApp — modules/auth/recuperar_post.php
//  POST /recuperar — Envía email de recuperación de contraseña
// ============================================================

defined('COTIZAAPP') or die;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/recuperar');

csrf_check();

// Rate limit: 3 intentos por IP cada 15 min
$rate = rate_check('recuperar', 3, 15);
if (!$rate['ok']) {
    $_SESSION['recuperar_error'] = $rate['error'];
    redirect('/recuperar');
}
rate_hit('recuperar');

$empresa_slug = trim(strtolower($_POST['empresa_slug'] ?? ''));
$email        = trim(strtolower($_POST['email'] ?? ''));

// Siempre redirigir con ?enviado=1 para no revelar si el email existe
$redirect_ok = '/recuperar?enviado=1&empresa=' . urlencode($empresa_slug);

if (empty($empresa_slug) || empty($email)) {
    redirect($redirect_ok);
}

// Buscar empresa
$empresa = DB::row("SELECT id FROM empresas WHERE slug = ? AND activa = 1", [$empresa_slug]);
if (!$empresa) {
    redirect($redirect_ok);
}

// Buscar usuario
$usuario = DB::row(
    "SELECT id, nombre, email FROM usuarios WHERE empresa_id = ? AND email = ? AND activo = 1",
    [$empresa['id'], $email]
);
if (!$usuario) {
    redirect($redirect_ok);
}

// Auto-crear tabla si no existe
try {
    DB::execute("SELECT 1 FROM password_resets LIMIT 0");
} catch (\PDOException $e) {
    DB::execute("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        empresa_id INT NOT NULL,
        token VARCHAR(128) NOT NULL,
        usado TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        INDEX idx_token (token),
        INDEX idx_usuario (usuario_id),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Invalidar tokens previos
DB::execute("UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0", [$usuario['id']]);

// Crear token
$token = generar_token(32);
DB::execute(
    "INSERT INTO password_resets (usuario_id, empresa_id, token, expires_at)
     VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))",
    [$usuario['id'], $empresa['id'], $token]
);

// Enviar email
$url_reset = BASE_URL . '/reset-password?token=' . $token;
Mailer::enviar_recovery($usuario['email'], $usuario['nombre'], $url_reset);

redirect($redirect_ok);
