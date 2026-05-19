<?php
// ============================================================
//  CotizaCloud — modules/config/propiedad_foto.php
//  POST /config/propiedad/:id/foto      → subir foto
//  POST /config/propiedad/:id/foto/eliminar → eliminar foto
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
csrf_check();
header('Content-Type: application/json');

$eid    = EMPRESA_ID;
$art_id = isset($id) ? (int)$id : 0;
$accion = $accion ?? 'subir';

if (!$art_id) json_error('ID requerido', 400);

$a = DB::row("SELECT id FROM articulos WHERE id=? AND empresa_id=?", [$art_id, $eid]);
if (!$a) json_error('Propiedad no encontrada', 404);

$prop = DB::row("SELECT fotos FROM propiedades WHERE articulo_id=?", [$art_id]);
$fotos = ($prop && $prop['fotos']) ? json_decode($prop['fotos'], true) : [];
if (!is_array($fotos)) $fotos = [];

// ── Eliminar foto ──
if ($accion === 'eliminar') {
    $body = json_decode(file_get_contents('php://input'), true);
    $idx  = isset($body['index']) ? (int)$body['index'] : -1;
    if ($idx < 0 || $idx >= count($fotos)) json_error('Foto no encontrada', 404);

    $foto_path = ROOT_PATH . '/' . ltrim(UPLOADS_URL, '/') . '/' . $fotos[$idx];
    if (is_file($foto_path)) @unlink($foto_path);

    array_splice($fotos, $idx, 1);
    DB::execute("UPDATE propiedades SET fotos=? WHERE articulo_id=?", [json_encode($fotos), $art_id]);
    json_ok(['fotos' => $fotos]);
}

// ── Subir foto ──
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    json_error('No se recibió el archivo', 400);
}

if (count($fotos) >= 10) {
    json_error('Máximo 10 fotos por propiedad', 400);
}

$result = upload_archivo($_FILES['foto'], $eid, 'propiedades');
if (!$result['ok']) json_error($result['error'], 400);

$fotos[] = $result['nombre_archivo'];
DB::execute("UPDATE propiedades SET fotos=? WHERE articulo_id=?", [json_encode($fotos), $art_id]);

json_ok([
    'url'   => $result['url'],
    'fotos' => $fotos,
]);
