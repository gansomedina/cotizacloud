<?php
// ============================================================
//  CotizaApp — modules/config/cupon.php
//  POST /config/cupon         → crear
//  POST /config/cupon/:id     → editar
//  POST /config/cupon/:id/eliminar
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
header('Content-Type: application/json');

$eid    = EMPRESA_ID;
$cup_id = isset($id) ? (int)$id : 0;
$accion = $accion ?? 'guardar';

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

$codigo  = mb_substr(strtoupper(trim($body['codigo'] ?? '')), 0, 60);
$pct     = (float)($body['porcentaje'] ?? 0);
$desc    = mb_substr(trim($body['descripcion'] ?? ''), 0, 200);
$activo  = (int)($body['activo'] ?? 1);

// Vencimiento
$venc_tipo  = $body['vencimiento_tipo'] ?? 'nunca';
if (!in_array($venc_tipo, ['nunca','fecha_fija','dias_cotizacion'])) $venc_tipo = 'nunca';
$venc_dias  = ($venc_tipo === 'dias_cotizacion') ? max(1, (int)($body['vencimiento_dias'] ?? 30)) : null;
$venc_fecha = null;
if ($venc_tipo === 'fecha_fija') {
    $f = trim($body['vencimiento_fecha'] ?? '');
    $venc_fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) ? $f : null;
    if (!$venc_fecha) { echo json_encode(['ok'=>false,'error'=>'Fecha de vencimiento inválida']); exit; }
}

if ($codigo === '') { echo json_encode(['ok'=>false,'error'=>'El código es obligatorio']); exit; }
if ($pct <= 0 || $pct > 99) { echo json_encode(['ok'=>false,'error'=>'El descuento debe ser entre 0.01 y 99%']); exit; }
if (!preg_match('/^[A-Z0-9_\-]{1,60}$/', $codigo)) {
    echo json_encode(['ok'=>false,'error'=>'Código inválido — solo letras, números, guión y guión bajo']); exit;
}

if ($cup_id > 0) {
    $c = DB::row("SELECT id FROM cupones WHERE id=? AND empresa_id=?", [$cup_id, $eid]);
    if (!$c) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }
    $dup = DB::val("SELECT id FROM cupones WHERE empresa_id=? AND codigo=? AND id!=?", [$eid, $codigo, $cup_id]);
    if ($dup) { echo json_encode(['ok'=>false,'error'=>'Ese código ya está en uso']); exit; }
    DB::execute(
        "UPDATE cupones SET codigo=?, porcentaje=?, descripcion=?, activo=?,
         vencimiento_tipo=?, vencimiento_dias=?, vencimiento_fecha=? WHERE id=?",
        [$codigo, $pct, $desc ?: null, $activo, $venc_tipo, $venc_dias, $venc_fecha, $cup_id]
    );
    echo json_encode(['ok'=>true, 'id'=>$cup_id]);
} else {
    $dup = DB::val("SELECT id FROM cupones WHERE empresa_id=? AND codigo=?", [$eid, $codigo]);
    if ($dup) { echo json_encode(['ok'=>false,'error'=>'Ese código ya existe']); exit; }
    $nuevo = DB::insert(
        "INSERT INTO cupones (empresa_id, codigo, porcentaje, descripcion, activo,
         vencimiento_tipo, vencimiento_dias, vencimiento_fecha)
         VALUES (?,?,?,?,?,?,?,?)",
        [$eid, $codigo, $pct, $desc ?: null, $activo, $venc_tipo, $venc_dias, $venc_fecha]
    );
    echo json_encode(['ok'=>true, 'id'=>$nuevo]);
}
