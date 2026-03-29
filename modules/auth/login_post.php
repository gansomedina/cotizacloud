<?php
// ============================================================
//  CotizaApp — modules/auth/login_post.php
//  POST /login — Login centralizado con slug de empresa
// ============================================================

defined('COTIZAAPP') or die;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/login');
}

csrf_check();

$empresa_slug = trim(strtolower($_POST['empresa_slug'] ?? ''));
$usuario_str  = trim($_POST['usuario'] ?? '');
$password     = $_POST['password'] ?? '';
$recordar     = !empty($_POST['recordar']);

// Rate limit: máximo 5 intentos de login por IP cada 15 minutos
$rate = rate_check('login', 5, 15);
if (!$rate['ok']) {
    flash('error', $rate['error']);
    redirect('/login?error=rate&empresa=' . urlencode($empresa_slug));
}

if (empty($empresa_slug)) {
    redirect('/login?error=empresa&empresa=' . urlencode($empresa_slug));
}

if (empty($usuario_str) || empty($password)) {
    redirect('/login?error=credenciales&empresa=' . urlencode($empresa_slug));
}

$resultado = Auth::login($empresa_slug, $usuario_str, $password, $recordar);

if (!$resultado['ok']) {
    rate_hit('login'); // Registrar intento fallido

    $error_msg = $resultado['error'] ?? '';
    if (str_contains($error_msg, 'Empresa')) {
        $error = 'empresa';
    } elseif (str_contains($error_msg, 'desactivad')) {
        $error = 'inactivo';
    } else {
        $error = 'credenciales';
    }

    redirect('/login?error=' . $error . '&empresa=' . urlencode($empresa_slug));
}

// Login exitoso — registrar visitor_id como interno (skip para superadmin _system)
$visitor_id_post = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)($_POST['visitor_id'] ?? '')), 0, 64);
$emp_slug_check = $resultado['empresa']['slug'] ?? '';
if ($visitor_id_post !== '' && $emp_slug_check !== '_system') {
    require_once MODULES_PATH . '/radar/Radar.php';
    $ip_login = ip_real();
    $ua_login = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $emp = $resultado['empresa'];
    Radar::marcar_visitor_interno(
        (int)$emp['id'],
        $visitor_id_post,
        'login',
        (int)Auth::id(),
        $ip_login,
        $ua_login
    );
    Radar::aprender_ip_radar((int)$emp['id'], $ip_login);
}

// Redirigir: superadmin con _admin va al panel, otros al dashboard
$redirect_to = $_SESSION['redirect_after_login'] ?? '/dashboard';
unset($_SESSION['redirect_after_login']);

// Validar que el redirect sea interno (prevenir open redirect / phishing)
if (!empty($redirect_to) && (str_contains($redirect_to, '://') || str_starts_with($redirect_to, '//'))) {
    $redirect_to = '/dashboard';
}

if ($resultado['usuario']['rol'] === 'superadmin' && $empresa_slug === '_admin') {
    $redirect_to = '/superadmin';
}

redirect($redirect_to);
