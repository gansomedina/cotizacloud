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
// Si estamos en cotiza.cloud (o subdominio), poner dominio .cotiza.cloud
// para que la cookie sea visible en empresa.cotiza.cloud
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$cookie_domain = str_ends_with($host, '.' . BASE_DOMAIN) || $host === BASE_DOMAIN
    ? '.' . BASE_DOMAIN   // .cotiza.cloud → visible en todos los subdominios
    : '';                  // dominio custom → solo este dominio exacto
setcookie('cz_vid', $vid, [
    'expires'  => time() + 730 * 86400,
    'path'     => '/',
    'domain'   => $cookie_domain,
    'secure'   => true,
    'httponly'  => false,
    'samesite' => 'Lax',
]);

// ── Marcar visitor como interno ──────────────────────────────
require_once MODULES_PATH . '/radar/Radar.php';
$ip = ip_real();
$ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
error_log("[Safari Bridge] vid={$vid} uid={$uid} eid={$eid} super=" . ($es_super?'1':'0') . " ip={$ip} host={$host}");

if ($es_super) {
    $todas = DB::query("SELECT id FROM empresas WHERE activa = 1 AND slug != '_system'");
    if ($todas) {
        foreach ($todas as $te) {
            Radar::marcar_visitor_interno((int)$te['id'], $vid, 'safari_bridge', $uid, $ip, $ua);
            Radar::aprender_ip_radar((int)$te['id'], $ip);
        }
    }
} else if ($eid > 0) {
    Radar::marcar_visitor_interno($eid, $vid, 'safari_bridge', $uid, $ip, $ua);
    Radar::aprender_ip_radar($eid, $ip);
}

// ── Si hay siguiente dominio en la cadena, redirigir ─────────
$next = $_GET['next'] ?? '';
if ($next !== '' && str_starts_with($next, 'https://')) {
    header('Location: ' . $next, true, 302);
    exit;
}

// ── Página final — el usuario ve esto cuando termina la cadena ──
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Escudo Radar</title>
    <style>
        body {
            display: flex; align-items: center; justify-content: center;
            height: 100vh; margin: 0;
            font-family: -apple-system, system-ui, sans-serif;
            background: #f4f4f0; color: #1a1a18;
        }
        .done { text-align: center; max-width: 300px; }
        .shield {
            width: 64px; height: 64px; border-radius: 50%;
            background: #eef7f2; display: flex;
            align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }
        h2 { font-size: 20px; margin: 0 0 8px; color: #1a5c38; }
        .msg { font-size: 14px; color: #4a4a46; line-height: 1.5; }
    </style>
</head>
<body>
<div class="done">
    <div class="shield">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1a5c38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <h2>Escudo Radar activado</h2>
    <p class="msg">Tus visitas a cotizaciones ya no contaminaran las metricas. Vuelve a la app.</p>
</div>
</body>
</html>
