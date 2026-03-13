<?php
// ============================================================
//  CotizaApp — modules/costos/categoria.php
//  POST /costos/categoria        → crear
//  POST /costos/categoria/:id    → editar
//  POST /costos/categoria/:id/toggle → activar/desactivar
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();

header('Content-Type: application/json');

$empresa_id = EMPRESA_ID;
$cat_id     = isset($id) ? (int)$id : 0;
$accion     = $accion ?? 'guardar'; // inyectado por Router

// ── TOGGLE activa ────────────────────────────────────────────
if ($accion === 'toggle') {
    if (!$cat_id) { echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }
    $body = json_decode(file_get_contents('php://input'), true);
    $activa = (bool)($body['activa'] ?? false);

    DB::execute(
        "UPDATE categorias_costos SET activa=? WHERE id=? AND empresa_id=?",
        [(int)$activa, $cat_id, $empresa_id]
    );
    echo json_encode(['ok'=>true]);
    exit;
}

// ── CREAR / EDITAR ────────────────────────────────────────────
$body  = json_decode(file_get_contents('php://input'), true);
$nombre = mb_substr(trim($body['nombre'] ?? ''), 0, 80);
$color  = trim($body['color'] ?? '#3b82f6');

if ($nombre === '') { echo json_encode(['ok'=>false,'error'=>'El nombre es obligatorio']); exit; }
if (!preg_match('/^#[0-9a-f]{3,6}$/i', $color)) $color = '#3b82f6';

if ($cat_id > 0) {
    // Editar — verificar existencia
    $c = DB::row("SELECT id FROM categorias_costos WHERE id=? AND empresa_id=?", [$cat_id, $empresa_id]);
    if (!$c) { echo json_encode(['ok'=>false,'error'=>'Categoría no encontrada']); exit; }

    DB::execute(
        "UPDATE categorias_costos SET nombre=?, color=? WHERE id=?",
        [$nombre, $color, $cat_id]
    );
    echo json_encode(['ok'=>true, 'id'=>$cat_id]);
} else {
    // Duplicado
    $existe = DB::val(
        "SELECT id FROM categorias_costos WHERE empresa_id=? AND nombre=?",
        [$empresa_id, $nombre]
    );
    if ($existe) { echo json_encode(['ok'=>false,'error'=>'Ya existe una categoría con ese nombre']); exit; }

    $nuevo = DB::insert(
        "INSERT INTO categorias_costos (empresa_id, nombre, color, activa) VALUES (?,?,?,1)",
        [$empresa_id, $nombre, $color]
    );
    echo json_encode(['ok'=>true, 'id'=>$nuevo]);
}
