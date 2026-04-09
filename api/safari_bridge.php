<?php
// ============================================================
//  CotizaApp — api/safari_bridge.php
//  GET /api/safari-bridge — Pone cz_vid cookie y marca visitor interno
//  Funciona en dos contextos:
//    1. Redirect chain del login en navegador (dominio a dominio)
//    2. SFSafariViewController desde app Capacitor (puente a Safari)
//  Usa token firmado HMAC — no requiere sesión activa
// ============================================================

defined('COTIZAAPP') or die;

$token_raw = $_GET['t'] ?? '';
if ($token_raw === '') {
    http_response_code(400);
    exit('missing token');
}

// Token format: base64(json).hmac_hex
$dot = strrpos($token_raw, '.');
if ($dot === false || $dot === 0) {
    http_response_code(400);
    exit('bad token');
}

$data_b64 = substr($token_raw, 0, $dot);
$sig      = substr($token_raw, $dot + 1);

// Verificar HMAC
$expected = hash_hmac('sha256', $data_b64, APP_SECRET);
if (!hash_equals($expected, $sig)) {
    http_response_code(403);
    exit('invalid signature');
}

$data = json_decode(base64_decode($data_b64), true);
if (!$data) {
    http_response_code(400);
    exit('bad payload');
}

// Verificar expiración
if (($data['exp'] ?? 0) < time()) {
    http_response_code(403);
    exit('expired');
}

$vid      = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)($data['vid'] ?? '')), 0, 64);
$uid      = (int)($data['uid'] ?? 0);
$eid      = (int)($data['eid'] ?? 0);
$es_super = !empty($data['super']);

if ($vid === '') {
    http_response_code(400);
    exit('no vid');
}

// ── Poner cookie cz_vid en este dominio ──────────────────────
// HttpOnly=false para que JS pueda leerlo y sincronizar con localStorage
setcookie('cz_vid', $vid, [
    'expires'  => time() + 730 * 86400,
    'path'     => '/',
    'secure'   => true,
    'httponly'  => false,
    'samesite' => 'Lax',
]);

// ── Marcar visitor como interno ──────────────────────────────
require_once MODULES_PATH . '/radar/Radar.php';
$ip = ip_real();
$ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

if ($es_super) {
    $todas = DB::query("SELECT id FROM empresas WHERE activa = 1 AND slug != '_system'");
    if ($todas) {
        foreach ($todas as $te) {
            Radar::marcar_visitor_interno((int)$te['id'], $vid, 'safari_bridge', $uid, $ip, $ua);
        }
    }
} else if ($eid > 0) {
    Radar::marcar_visitor_interno($eid, $vid, 'safari_bridge', $uid, $ip, $ua);
}

// ── Si hay siguiente dominio en la cadena, redirigir ─────────
$next = $_GET['next'] ?? '';
if ($next !== '' && str_starts_with($next, 'https://')) {
    header('Location: ' . $next, true, 302);
    exit;
}

// ── Página final — el usuario ve esto brevemente ─────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Listo</title>
    <style>
        body {
            display: flex; align-items: center; justify-content: center;
            height: 100vh; margin: 0;
            font-family: -apple-system, system-ui, sans-serif;
            background: #f4f4f0; color: #1a1a18;
        }
        .done { text-align: center; }
        .check {
            width: 56px; height: 56px; border-radius: 50%;
            background: #eef7f2; display: flex;
            align-items: center; justify-content: center;
            margin: 0 auto 12px;
        }
        .msg { font-size: 15px; color: #4a4a46; }
    </style>
</head>
<body>
<div class="done">
    <div class="check">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1a5c38" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <div class="msg">Sesion sincronizada</div>
</div>
</body>
</html>
