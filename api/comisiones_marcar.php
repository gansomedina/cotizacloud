<?php
// ============================================================
//  POST /api/comisiones/marcar
//  Marca/desmarca una venta como comisión pagada.
//  Solo superadmin. Persistencia en /data/comisiones_pagadas_{uid}.json
//  No toca BD — sigue el patrón de equilibrio.json
// ============================================================
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');
Auth::requerir_superadmin();

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$uid = (int)Auth::id();
$accion = trim((string)($body['accion'] ?? ''));

$file = dirname(__DIR__) . '/data/comisiones_pagadas_' . $uid . '.json';
@mkdir(dirname($file), 0755, true);

$map = [];
if (file_exists($file)) {
    $map = json_decode(file_get_contents($file), true);
    if (!is_array($map)) $map = [];
}

if ($accion === 'leer') {
    echo json_encode(['ok' => true, 'pagadas' => array_keys($map)]);
    exit;
}

$venta_id = (int)($body['venta_id'] ?? 0);
if ($venta_id <= 0) {
    echo json_encode(['ok'=>false,'error'=>'venta_id requerido']);
    exit;
}

if ($accion === 'pagar') {
    $map[(string)$venta_id] = time();
} elseif ($accion === 'revertir') {
    unset($map[(string)$venta_id]);
} elseif ($accion === 'importar') {
    // Importa una lista de IDs desde localStorage (migración inicial)
    $ids = $body['ids'] ?? [];
    if (is_array($ids)) {
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id > 0) $map[(string)$id] = time();
        }
    }
} else {
    echo json_encode(['ok'=>false,'error'=>'accion inválida']);
    exit;
}

if (file_put_contents($file, json_encode($map, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['ok'=>false,'error'=>'no se pudo guardar']);
    exit;
}

echo json_encode(['ok' => true, 'pagadas' => array_keys($map)]);
