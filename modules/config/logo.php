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
        $uploads_base = realpath(ROOT_PATH . '/uploads');
        $path = realpath(ROOT_PATH . parse_url($emp['logo_url'], PHP_URL_PATH));
        if ($path && $uploads_base && str_starts_with($path, $uploads_base)) @unlink($path);
        // Fallback: buscar en public/ por logos antiguos
        $pub_base = realpath(ROOT_PATH . '/public/uploads');
        $path2 = realpath(ROOT_PATH . '/public' . parse_url($emp['logo_url'], PHP_URL_PATH));
        if ($path2 && $pub_base && str_starts_with($path2, $pub_base)) @unlink($path2);
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
$dir  = ROOT_PATH . '/uploads/logos/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$filename = 'logo_' . $eid . '_' . time() . '.' . $ext;
$dest     = $dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['ok'=>false,'error'=>'Error al guardar el archivo']); exit;
}

// Borrar logo anterior si existe
$emp = DB::row("SELECT logo_url FROM empresas WHERE id=?", [$eid]);
if ($emp['logo_url']) {
    $uploads_base = realpath(ROOT_PATH . '/uploads');
    $old = realpath(ROOT_PATH . parse_url($emp['logo_url'], PHP_URL_PATH));
    if ($old && $uploads_base && str_starts_with($old, $uploads_base)) @unlink($old);
    $pub_base = realpath(ROOT_PATH . '/public/uploads');
    $old2 = realpath(ROOT_PATH . '/public' . parse_url($emp['logo_url'], PHP_URL_PATH));
    if ($old2 && $pub_base && str_starts_with($old2, $pub_base)) @unlink($old2);
}

$url = '/uploads/logos/' . $filename;
DB::execute("UPDATE empresas SET logo_url=? WHERE id=?", [$url, $eid]);
echo json_encode(['ok'=>true, 'url'=>$url]);
