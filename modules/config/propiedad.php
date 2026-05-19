<?php
// ============================================================
//  CotizaCloud — modules/config/propiedad.php
//  POST /config/propiedad         → crear
//  POST /config/propiedad/:id     → editar
//  POST /config/propiedad/:id/eliminar
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
csrf_check();
header('Content-Type: application/json');

$eid      = EMPRESA_ID;
$art_id   = isset($id) ? (int)$id : 0;
$accion   = $accion ?? 'guardar';

// ── Eliminar (soft delete en articulos) ──
if ($accion === 'eliminar' && $art_id) {
    $a = DB::row("SELECT id FROM articulos WHERE id=? AND empresa_id=?", [$art_id, $eid]);
    if (!$a) json_error('Propiedad no encontrada', 404);
    DB::execute("UPDATE articulos SET activo=0 WHERE id=?", [$art_id]);
    json_ok(['id' => $art_id]);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Datos inválidos', 400);

$titulo          = trim($body['titulo'] ?? '');
$descripcion     = trim($body['descripcion'] ?? '');
$precio          = max(0, (float)($body['precio'] ?? 0));
$tipo_operacion  = in_array($body['tipo_operacion'] ?? '', ['venta','renta','renta_temporal']) ? $body['tipo_operacion'] : 'venta';
$tipo_propiedad  = in_array($body['tipo_propiedad'] ?? '', ['casa','departamento','terreno','local_comercial','oficina','bodega']) ? $body['tipo_propiedad'] : 'casa';
$m2_terreno      = !empty($body['m2_terreno']) ? max(0, (float)$body['m2_terreno']) : null;
$m2_construccion = !empty($body['m2_construccion']) ? max(0, (float)$body['m2_construccion']) : null;
$recamaras       = !empty($body['recamaras']) ? max(0, (int)$body['recamaras']) : null;
$banos           = !empty($body['banos']) ? max(0, (float)$body['banos']) : null;

if (!$titulo) json_error('El nombre es requerido');

// ── Editar ──
if ($art_id) {
    $a = DB::row("SELECT id FROM articulos WHERE id=? AND empresa_id=?", [$art_id, $eid]);
    if (!$a) json_error('Propiedad no encontrada', 404);

    DB::beginTransaction();
    try {
        DB::execute(
            "UPDATE articulos SET titulo=?, descripcion=?, precio=? WHERE id=?",
            [$titulo, $descripcion, $precio, $art_id]
        );
        DB::execute(
            "INSERT INTO propiedades (articulo_id, tipo_operacion, tipo_propiedad, m2_terreno, m2_construccion, recamaras, banos)
             VALUES (?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE tipo_operacion=VALUES(tipo_operacion), tipo_propiedad=VALUES(tipo_propiedad),
                m2_terreno=VALUES(m2_terreno), m2_construccion=VALUES(m2_construccion),
                recamaras=VALUES(recamaras), banos=VALUES(banos)",
            [$art_id, $tipo_operacion, $tipo_propiedad, $m2_terreno, $m2_construccion, $recamaras, $banos]
        );
        DB::commit();
    } catch (Exception $e) {
        DB::rollback();
        json_error('Error al guardar: ' . $e->getMessage(), 500);
    }
    json_ok(['id' => $art_id]);
}

// ── Crear ──
DB::beginTransaction();
try {
    $new_id = DB::insert(
        "INSERT INTO articulos (empresa_id, titulo, descripcion, precio, activo) VALUES (?,?,?,?,1)",
        [$eid, $titulo, $descripcion, $precio]
    );
    DB::execute(
        "INSERT INTO propiedades (articulo_id, tipo_operacion, tipo_propiedad, m2_terreno, m2_construccion, recamaras, banos)
         VALUES (?,?,?,?,?,?,?)",
        [$new_id, $tipo_operacion, $tipo_propiedad, $m2_terreno, $m2_construccion, $recamaras, $banos]
    );
    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    json_error('Error al crear: ' . $e->getMessage(), 500);
}

json_ok(['id' => $new_id]);
