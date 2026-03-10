<?php
// ============================================================
//  cotiza.cloud — modules/config/logo.php
//  POST /config/logo         → subir
//  POST /config/logo/quitar  → quitar
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
header('Content-Type: application/json');

$eid    = EMPRESA_ID;
$accion = $accion ?? 'subir'; // inyectado por Router

// ── QUITAR ──────────────────────────────────────────────────
if ($accion === 'quitar') {
    $emp = DB::row("SELECT logo_url FROM empresas WHERE id=?", [$eid]);
    if ($emp['logo_url']) {
        $path = ROOT_PATH . '/public' . parse_url($emp['logo_url'], PHP_URL_PATH);
        if (file_exists($path)) @unlink($path);
    }
    DB::execute("UPDATE empresas SET logo_url=NULL WHERE id=?", [$eid]);
    echo json_encode(['ok' => true]);
    exit;
}

// ── SUBIR ────────────────────────────────────────────────────
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok'=>false,'error'=>'No se recibió el archivo']); exit;
}

$file     = $_FILES['logo'];
$max_size = 2 * 1024 * 1024; // 2 MB

if ($file['size'] > $max_size) {
    echo json_encode(['ok'=>false,'error'=>'El archivo no debe superar 2 MB']); exit;
}

$mime = mime_content_type($file['tmp_name']);
$allowed = ['image/png','image/svg+xml','image/jpeg','image/webp'];
if (!in_array($mime, $allowed)) {
    echo json_encode(['ok'=>false,'error'=>'Tipo de archivo no permitido']); exit;
}

$ext  = match($mime) { 'image/png'=>'png','image/svg+xml'=>'svg','image/jpeg'=>'jpg','image/webp'=>'webp', default=>'png' };
$dir  = ROOT_PATH . '/public/uploads/logos/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$filename = 'logo_' . $eid . '_' . time() . '.' . $ext;
$dest     = $dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['ok'=>false,'error'=>'Error al guardar el archivo']); exit;
}

// Borrar logo anterior si existe
$emp = DB::row("SELECT logo_url FROM empresas WHERE id=?", [$eid]);
if ($emp['logo_url']) {
    $old = ROOT_PATH . '/public' . parse_url($emp['logo_url'], PHP_URL_PATH);
    if (file_exists($old)) @unlink($old);
}

$url = '/uploads/logos/' . $filename;
DB::execute("UPDATE empresas SET logo_url=? WHERE id=?", [$url, $eid]);
echo json_encode(['ok'=>true, 'url'=>$url]);
