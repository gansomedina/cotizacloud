<?php
// ============================================================
//  CotizaApp — modules/superadmin/equilibrio.php
//  POST /superadmin/equilibrio
//  Guarda metas de equilibrio en archivo JSON (sin BD)
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

header('Content-Type: application/json; charset=utf-8');

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) json_error('Payload inválido', 400);

$metas = [];
foreach ($body as $eid => $val) {
    $metas[(string)(int)$eid] = max(0, (float)$val);
}

$file = dirname(__DIR__, 2) . '/config/equilibrio.json';
$ok = file_put_contents($file, json_encode($metas, JSON_PRETTY_PRINT));

if ($ok === false) json_error('No se pudo guardar el archivo');

json_ok(['guardado' => true]);
