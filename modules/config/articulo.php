<?php
// ============================================================
//  cotiza.cloud — modules/config/articulo.php
//  POST /config/articulo         → crear
//  POST /config/articulo/:id     → editar
//  POST /config/articulo/:id/eliminar
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
header('Content-Type: application/json');

$eid      = EMPRESA_ID;
$art_id   = isset($id) ? (int)$id : 0;
$accion   = $accion ?? 'guardar';

// ── ELIMINAR ─────────────────────────────────────────────────
if ($accion === 'eliminar') {
    if (!$art_id) { echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }
    $a = DB::row("SELECT id FROM articulos WHERE id=? AND empresa_id=?", [$art_id, $eid]);
    if (!$a) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }
    DB::execute("UPDATE articulos SET activo=0 WHERE id=?", [$art_id]);
    echo json_encode(['ok' => true]);
    exit;
}

// ── CREAR / EDITAR ────────────────────────────────────────────
$body  = json_decode(file_get_contents('php://input'), true);
$titulo = mb_substr(trim($body['titulo'] ?? ''), 0, 255);
if ($titulo === '') { echo json_encode(['ok'=>false,'error'=>'El nombre es obligatorio']); exit; }

$sku    = mb_substr(trim($body['sku']         ?? ''), 0, 60)  ?: null;
$desc   = mb_substr(trim($body['descripcion'] ?? ''), 0, 5000) ?: null;
$precio = max(0, (float)($body['precio'] ?? 0));

if ($art_id > 0) {
    $a = DB::row("SELECT id FROM articulos WHERE id=? AND empresa_id=?", [$art_id, $eid]);
    if (!$a) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }
    DB::execute(
        "UPDATE articulos SET titulo=?, sku=?, descripcion=?, precio=? WHERE id=?",
        [$titulo, $sku, $desc, $precio, $art_id]
    );
    echo json_encode(['ok'=>true, 'id'=>$art_id]);
} else {
    $nuevo = DB::insert(
        "INSERT INTO articulos (empresa_id, titulo, sku, descripcion, precio, activo) VALUES (?,?,?,?,?,1)",
        [$eid, $titulo, $sku, $desc, $precio]
    );
    echo json_encode(['ok'=>true, 'id'=>$nuevo]);
}
