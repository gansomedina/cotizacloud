<?php
// ============================================================
//  SuperAdmin — Impersonar empresa
//  Crea una sesión del superadmin vinculada a la empresa target
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

csrf_check();

$target_id = (int)($_POST['empresa_id'] ?? 0);
if ($target_id <= 0) {
    flash('error', 'Empresa no válida');
    redirect('/superadmin');
}

$empresa = DB::row("SELECT * FROM empresas WHERE id = ?", [$target_id]);
if (!$empresa) {
    flash('error', 'Empresa no encontrada');
    redirect('/superadmin');
}

$usuario = Auth::usuario();
if (!$usuario) {
    redirect('/login');
}

// Invalidar sesión actual
$old_token = $_COOKIE[SESSION_NAME] ?? null;
if ($old_token) {
    DB::execute("DELETE FROM user_sessions WHERE token = ?", [$old_token]);
}

// Crear nueva sesión del superadmin pero con la empresa target
$token   = generar_token(32);
$expira  = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
$ip      = ip_real();
$ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';

DB::insert(
    "INSERT INTO user_sessions (usuario_id, empresa_id, token, ip, user_agent, expires_at)
     VALUES (?, ?, ?, ?, ?, ?)",
    [(int)$usuario['id'], $target_id, $token, $ip, $ua, $expira]
);

// Setear cookie
setcookie(SESSION_NAME, $token, [
    'expires'  => time() + SESSION_LIFETIME,
    'path'     => '/',
    'domain'   => '.' . BASE_DOMAIN,
    'secure'   => !DEBUG,
    'httponly'  => true,
    'samesite'  => 'Lax',
]);

// Redirigir al dashboard de esa empresa
redirect('/dashboard');
