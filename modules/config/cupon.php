<?php
// ============================================================
//  cotiza.cloud — modules/config/cupon.php
//  POST /config/cupon         → crear
//  POST /config/cupon/:id     → editar
//  POST /config/cupon/:id/eliminar
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
header('Content-Type: application/json');

$eid     = EMPRESA_ID;
$cup_id  = isset($id) ? (int)$id : 0;
$accion  = $accion ?? 'guardar';

// ── ELIMINAR ─────────────────────────────────────────────────
if ($accion === 'eliminar') {
    if (!$cup_id) { echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }
    $c = DB::row("SELECT id FROM cupones WHERE id=? AND empresa_id=?", [$cup_id, $eid]);
    if (!$c) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }
    DB::execute("DELETE FROM cupones WHERE id=?", [$cup_id]);
    echo json_encode(['ok' => true]);
    exit;
}

// ── CREAR / EDITAR ────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
$codigo   = mb_substr(strtoupper(trim($body['codigo'] ?? '')), 0, 60);
$pct      = (float)($body['porcentaje'] ?? 0);
$desc     = mb_substr(trim($body['descripcion'] ?? ''), 0, 200);
$activo   = (int)($body['activo'] ?? 1);

if ($codigo === '')  { echo json_encode(['ok'=>false,'error'=>'El código es obligatorio']); exit; }
if ($pct <= 0 || $pct > 99) { echo json_encode(['ok'=>false,'error'=>'El descuento debe ser entre 0.01 y 99%']); exit; }
if (!preg_match('/^[A-Z0-9_\-]{1,60}$/', $codigo)) {
    echo json_encode(['ok'=>false,'error'=>'Código inválido — solo letras, números, guión y guión bajo']); exit;
}

if ($cup_id > 0) {
    $c = DB::row("SELECT id FROM cupones WHERE id=? AND empresa_id=?", [$cup_id, $eid]);
    if (!$c) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }
    // Verificar duplicado de código (excepto el mismo)
    $dup = DB::val("SELECT id FROM cupones WHERE empresa_id=? AND codigo=? AND id!=?", [$eid, $codigo, $cup_id]);
    if ($dup) { echo json_encode(['ok'=>false,'error'=>'Ese código ya está en uso']); exit; }
    DB::execute(
        "UPDATE cupones SET codigo=?, porcentaje=?, descripcion=?, activo=? WHERE id=?",
        [$codigo, $pct, $desc ?: null, $activo, $cup_id]
    );
    echo json_encode(['ok'=>true, 'id'=>$cup_id]);
} else {
    $dup = DB::val("SELECT id FROM cupones WHERE empresa_id=? AND codigo=?", [$eid, $codigo]);
    if ($dup) { echo json_encode(['ok'=>false,'error'=>'Ese código ya existe']); exit; }
    $nuevo = DB::insert(
        "INSERT INTO cupones (empresa_id, codigo, porcentaje, descripcion, activo) VALUES (?,?,?,?,?)",
        [$eid, $codigo, $pct, $desc ?: null, $activo]
    );
    echo json_encode(['ok'=>true, 'id'=>$nuevo]);
}
