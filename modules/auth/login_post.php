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

// Login exitoso — registrar visitor_id como interno en WKWebView/browser
$visitor_id_post = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)($_POST['visitor_id'] ?? '')), 0, 64);
$emp = $resultado['empresa'];
$es_super = ($resultado['usuario']['rol'] ?? '') === 'superadmin';

if ($visitor_id_post !== '') {
    require_once MODULES_PATH . '/radar/Radar.php';
    $ip_login = ip_real();
    $ua_login = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    if ($es_super) {
        // Superadmin: marcar en TODAS las empresas
        $todas = DB::query("SELECT id FROM empresas WHERE activa = 1 AND slug != '_system'");
        if ($todas) foreach ($todas as $te) {
            Radar::marcar_visitor_interno((int)$te['id'], $visitor_id_post, 'login', (int)Auth::id(), $ip_login, $ua_login);
            Radar::aprender_ip_radar((int)$te['id'], $ip_login);
        }
    } else {
        Radar::marcar_visitor_interno((int)$emp['id'], $visitor_id_post, 'login', (int)Auth::id(), $ip_login, $ua_login);
        Radar::aprender_ip_radar((int)$emp['id'], $ip_login);
    }
}

// Redirigir: superadmin con _admin va al panel, otros al dashboard
$redirect_to = $_SESSION['redirect_after_login'] ?? '/dashboard';
unset($_SESSION['redirect_after_login']);

// Validar que el redirect sea interno (prevenir open redirect / phishing)
if (!empty($redirect_to) && (str_contains($redirect_to, '://') || str_starts_with($redirect_to, '//'))) {
    $redirect_to = '/dashboard';
}

if ($es_super && $empresa_slug === '_admin') {
    $redirect_to = '/superadmin';
}

// ── Cross-domain sync: poner cz_vid en dominios custom + Safari ──
// La app Capacitor envía is_app=1 desde JS (detección confiable)
$is_native_app = !empty($_POST['is_app']);

if ($visitor_id_post !== '') {
    // Token firmado HMAC para el bridge (válido 5 minutos)
    $bridge_payload = base64_encode(json_encode([
        'vid'   => $visitor_id_post,
        'uid'   => (int)Auth::id(),
        'eid'   => (int)$emp['id'],
        'super' => $es_super ? 1 : 0,
        'exp'   => time() + 300,
    ]));
    $bridge_sig   = hash_hmac('sha256', $bridge_payload, APP_SECRET);
    $bridge_token = $bridge_payload . '.' . $bridge_sig;
    $t_encoded    = urlencode($bridge_token);

    // Obtener dominios custom
    if ($es_super) {
        $dominios_custom = DB::query(
            "SELECT dominio_custom FROM empresas WHERE dominio_custom IS NOT NULL AND dominio_custom != '' AND activa = 1"
        );
    } else {
        $dominios_custom = DB::query(
            "SELECT dominio_custom FROM empresas WHERE id = ? AND dominio_custom IS NOT NULL AND dominio_custom != '' AND activa = 1",
            [(int)$emp['id']]
        );
    }

    if (!$is_native_app && $dominios_custom) {
        // ── NAVEGADOR: redirect chain dominio a dominio → dashboard ──
        $final_url = BASE_URL . $redirect_to;
        $chain_url = $final_url;
        foreach (array_reverse($dominios_custom) as $dc) {
            $chain_url = 'https://' . $dc['dominio_custom']
                       . '/api/safari-bridge?t=' . $t_encoded
                       . '&next=' . urlencode($chain_url);
        }
        header('Location: ' . $chain_url, true, 302);
        exit;
    }

    if ($is_native_app) {
        // ── APP CAPACITOR: guardar token para disparar bridge desde dashboard ──
        // No se puede abrir SFSafariViewController desde la página inline del
        // login POST — Capacitor lo escala a Safari externo. El bridge se
        // dispara desde el dashboard (página Capacitor normal).
        $_SESSION['safari_bridge_url'] = BASE_URL . '/api/safari-bridge?t=' . $t_encoded;
    }
}

redirect($redirect_to);
