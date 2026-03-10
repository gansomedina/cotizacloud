<?php
// ============================================================
//  CotizaApp — modules/costos/nuevo_gasto.php
//  POST /costos/gasto        → crear
//  POST /costos/gasto/:id    → editar
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json');

$empresa_id = EMPRESA_ID;
$gasto_id   = isset($id) ? (int)$id : 0;  // :id del router si es edición

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { echo json_encode(['ok'=>false,'error'=>'Payload inválido']); exit; }

$venta_id    = (int)($body['venta_id']    ?? 0);
$categoria_id= (int)($body['categoria_id']?? 0);
$concepto    = trim($body['concepto']     ?? '');
$importe     = (float)($body['importe']   ?? 0);
$fecha       = $body['fecha'] ?? date('Y-m-d');
$nota        = mb_substr(trim($body['nota'] ?? ''), 0, 500);

// Validaciones
if (!$venta_id || !$categoria_id || $concepto === '' || $importe <= 0) {
    echo json_encode(['ok'=>false,'error'=>'Faltan campos obligatorios']); exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

// Verificar que la venta pertenece a la empresa (y al usuario si no es admin)
$venta = DB::row("SELECT id, usuario_id FROM ventas WHERE id=? AND empresa_id=? AND estado != 'cancelada'", [$venta_id, $empresa_id]);
if (!$venta) { echo json_encode(['ok'=>false,'error'=>'Venta no encontrada']); exit; }
if (!Auth::es_admin() && !Auth::puede('ver_todas_ventas') && (int)$venta['usuario_id'] !== Auth::id()) {
    echo json_encode(['ok'=>false,'error'=>'Sin permiso']); exit;
}

// Verificar categoría
$cat = DB::row("SELECT id FROM categorias_costos WHERE id=? AND empresa_id=?", [$categoria_id, $empresa_id]);
if (!$cat) { echo json_encode(['ok'=>false,'error'=>'Categoría inválida']); exit; }

if ($gasto_id > 0) {
    // Editar — verificar propiedad
    $g = DB::row("SELECT id FROM gastos_venta WHERE id=? AND empresa_id=?", [$gasto_id, $empresa_id]);
    if (!$g) { echo json_encode(['ok'=>false,'error'=>'Gasto no encontrado']); exit; }

    DB::execute(
        "UPDATE gastos_venta SET venta_id=?, categoria_id=?, concepto=?, importe=?, fecha=?, nota=? WHERE id=?",
        [$venta_id, $categoria_id, $concepto, $importe, $fecha, $nota, $gasto_id]
    );
    echo json_encode(['ok'=>true, 'id'=>$gasto_id]);
} else {
    // Crear
    $nuevo_id = DB::insert(
        "INSERT INTO gastos_venta (empresa_id, venta_id, categoria_id, concepto, importe, fecha, nota) VALUES (?,?,?,?,?,?,?)",
        [$empresa_id, $venta_id, $categoria_id, $concepto, $importe, $fecha, $nota]
    );
    echo json_encode(['ok'=>true, 'id'=>$nuevo_id]);
}
