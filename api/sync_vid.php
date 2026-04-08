<?php
// ============================================================
//  CotizaApp — api/sync_vid.php
//  GET /api/sync-vid?r=URL_RETORNO
//  Sincroniza visitor_id entre cotiza.cloud y dominios custom
//  Se ejecuta 1 sola vez por navegador por dominio custom
// ============================================================

defined('COTIZAAPP') or die;

$return_url = trim($_GET['r'] ?? '');
if (!$return_url) { http_response_code(400); die('Missing return URL'); }

// Validar que el return URL sea un dominio custom registrado
$parsed = parse_url($return_url);
$host = strtolower($parsed['host'] ?? '');
if (!$host) { http_response_code(400); die('Invalid URL'); }

// Verificar que sea un dominio custom registrado (no cualquier dominio)
$empresa = DB::row(
    "SELECT id FROM empresas WHERE dominio_custom = ? AND activa = 1",
    [$host]
);
if (!$empresa) {
    // No es dominio custom registrado — redirigir sin sync
    header('Location: ' . $return_url, true, 302);
    exit;
}

// Leer visitor_id de cotiza.cloud (cookie disponible aquí)
$vid = '';
if (!empty($_COOKIE['cz_vid'])) {
    $vid = preg_replace('/[^a-zA-Z0-9\-_]/', '', $_COOKIE['cz_vid']);
}

// Verificar si es interno
$es_interno = false;
if ($vid) {
    $es_interno = (bool)DB::val(
        "SELECT 1 FROM radar_visitors_internos
         WHERE empresa_id = ? AND visitor_id = ?
         AND last_seen > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 365 DAY))
         LIMIT 1",
        [(int)$empresa['id'], $vid]
    );
}

// Si es interno, pasar el vid como parámetro temporal
if ($es_interno && $vid) {
    $separator = str_contains($return_url, '?') ? '&' : '?';
    $return_url .= $separator . '_sv=' . urlencode($vid);
}

// Redirigir de vuelta al dominio custom
header('Location: ' . $return_url, true, 302);
exit;
