<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/adjuntos.php
//  POST /cotizaciones/:id/adjuntos       → subir archivo
//  POST /cotizaciones/:id/adjuntos/quitar → eliminar archivo
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');
csrf_check();

if (!Auth::es_admin() && !Auth::puede('adjuntar')) {
    json_error('Sin permiso para adjuntar archivos', 403);
}

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

$cot = DB::row(
    "SELECT id, estado FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('No encontrada', 404);

// ─── ELIMINAR ──────────────────────────────────────────────
if (str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/quitar')) {
    $body = json_decode(file_get_contents('php://input'), true);
    $archivo_id = (int)($body['archivo_id'] ?? 0);
    if (!$archivo_id) json_error('ID archivo inválido', 400);

    $arch = DB::row(
        "SELECT nombre_archivo FROM cotizacion_archivos WHERE id = ? AND cotizacion_id = ?",
        [$archivo_id, $cot_id]
    );
    if (!$arch) json_error('Archivo no encontrado', 404);

    // Eliminar físico
    $ruta = UPLOADS_PATH . '/' . $arch['nombre_archivo'];
    if (file_exists($ruta)) @unlink($ruta);

    DB::execute("DELETE FROM cotizacion_archivos WHERE id = ?", [$archivo_id]);
    json_ok(['id' => $archivo_id]);
}

// ─── SUBIR ─────────────────────────────────────────────────
// Verificar máximo 3 adjuntos
$total = (int)DB::val(
    "SELECT COUNT(*) FROM cotizacion_archivos WHERE cotizacion_id = ?",
    [$cot_id]
);
if ($total >= 3) json_error('Máximo 3 archivos adjuntos por cotización', 422);

if (empty($_FILES['archivo'])) json_error('No se recibió archivo', 400);

// Límite de 1MB para adjuntos de cotización
if ($_FILES['archivo']['size'] > 1 * 1024 * 1024) {
    json_error('El archivo no debe superar 1 MB', 422);
}

$res = upload_archivo($_FILES['archivo'], $empresa_id, 'adjuntos');
if (!$res['ok']) json_error($res['error'], 422);

$new_id = DB::insert(
    "INSERT INTO cotizacion_archivos (cotizacion_id, nombre_original, nombre_archivo, mime_type, tamano_bytes)
     VALUES (?, ?, ?, ?, ?)",
    [$cot_id, $res['nombre_original'], $res['nombre_archivo'], $res['mime_type'], $res['tamano_bytes']]
);

json_ok([
    'id'              => $new_id,
    'nombre_original' => $res['nombre_original'],
    'url'             => $res['url'],
    'mime_type'       => $res['mime_type'],
    'tamano_bytes'    => $res['tamano_bytes'],
]);
