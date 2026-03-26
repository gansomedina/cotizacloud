<?php
// ============================================================
//  CotizaApp — modules/auth/reset_password_post.php
//  POST /reset-password — Procesa el cambio de contraseña
// ============================================================

defined('COTIZAAPP') or die;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/login');

csrf_check();

$token    = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

if (empty($token)) redirect('/login');

// Validar token
$reset = DB::row(
    "SELECT pr.*, u.id AS uid
     FROM password_resets pr
     JOIN usuarios u ON u.id = pr.usuario_id AND u.activo = 1
     WHERE pr.token = ? AND pr.usado = 0 AND pr.expires_at > NOW()",
    [$token]
);

if (!$reset) {
    redirect('/reset-password?token=' . urlencode($token));
}

// Validar contraseña
if (strlen($password) < 6) {
    $_SESSION['reset_error'] = 'La contraseña debe tener al menos 6 caracteres.';
    redirect('/reset-password?token=' . urlencode($token));
}

if ($password !== $password2) {
    $_SESSION['reset_error'] = 'Las contraseñas no coinciden.';
    redirect('/reset-password?token=' . urlencode($token));
}

// Actualizar contraseña
DB::execute(
    "UPDATE usuarios SET password_hash = ? WHERE id = ?",
    [password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $reset['uid']]
);

// Marcar token como usado
DB::execute("UPDATE password_resets SET usado = 1 WHERE id = ?", [$reset['id']]);

// Invalidar solo las sesiones del usuario afectado (no toda la empresa)
DB::execute("DELETE FROM user_sessions WHERE usuario_id = ?", [$reset['uid']]);

redirect('/reset-password?exito=1&token=used');
