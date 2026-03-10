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

// Login exitoso — redirigir a donde quería ir o al dashboard
$redirect_to = $_SESSION['redirect_after_login'] ?? '/';
unset($_SESSION['redirect_after_login']);

redirect($redirect_to);
