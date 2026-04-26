<?php
// ============================================================
//  CotizaApp — modules/superadmin/equilibrio.php
//  POST /superadmin/equilibrio
//  Guarda metas de equilibrio en archivo JSON (sin BD)
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$body = $raw ? json_decode($raw, true) : null;
if (!is_array($body) || empty($body)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Payload inválido', 'raw_length' => strlen($raw ?: '')]);
    exit;
}

$metas = [];
foreach ($body as $eid => $val) {
    $metas[(string)(int)$eid] = max(0, (float)$val);
}

$dir = dirname(__DIR__, 2) . '/config';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$file = $dir . '/equilibrio.json';
$ok = file_put_contents($file, json_encode($metas, JSON_PRETTY_PRINT));

if ($ok === false) json_error('No se pudo guardar el archivo');

json_ok(['guardado' => true]);
