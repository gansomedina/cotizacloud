<?php
// ============================================================
//  CotizaApp — modules/auth/login_post.php
//  POST /login — Procesa el formulario de login
// ============================================================

defined('COTIZAAPP') or die;

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/login');
}

// CSRF
csrf_check();

$usuario_str = trim($_POST['usuario'] ?? '');
$password    = $_POST['password'] ?? '';

if (empty($usuario_str) || empty($password)) {
    redirect('/login?error=credenciales');
}

$resultado = Auth::login($usuario_str, $password);

if (!$resultado['ok']) {
    // Distinguir error de credenciales vs inactivo
    $error = str_contains($resultado['error'] ?? '', 'desactivad')
           ? 'inactivo'
           : 'credenciales';

    redirect('/login?error=' . $error);
}

// Login exitoso — registrar visitor_id como interno ANTES de redirigir
// Esta es la capa 0: certeza absoluta de que este navegador es del equipo.
$visitor_id_post = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)($_POST['visitor_id'] ?? '')), 0, 64);
if ($visitor_id_post !== '') {
    require_once MODULES_PATH . '/radar/Radar.php';
    $ip_login = ip_real();
    $ua_login = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    Radar::marcar_visitor_interno(
        (int)(Auth::empresa()['id'] ?? 0),
        $visitor_id_post,
        'login',
        (int)Auth::id(),
        $ip_login,
        $ua_login
    );
    // También aprender la IP de este login como interna
    Radar::aprender_ip_radar((int)(Auth::empresa()['id'] ?? 0), $ip_login);
}

// Redirigir a donde quería ir o al dashboard
$redirect_to = $_SESSION['redirect_after_login'] ?? '/';
unset($_SESSION['redirect_after_login']);

redirect($redirect_to);
