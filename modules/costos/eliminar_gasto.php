<?php
// ============================================================
//  CotizaApp — modules/costos/eliminar_gasto.php
//  POST /costos/gasto/:id/eliminar
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json');

$empresa_id = EMPRESA_ID;
$gasto_id   = (int)($id ?? 0);

if (!$gasto_id) { echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }

$g = DB::row(
    "SELECT gv.id, v.usuario_id, v.vendedor_id
     FROM gastos_venta gv
     JOIN ventas v ON v.id = gv.venta_id
     WHERE gv.id=? AND gv.empresa_id=?",
    [$gasto_id, $empresa_id]
);
if (!$g) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }

if (!Auth::es_admin() && !Auth::puede('ver_todas_ventas') && (int)$g['usuario_id'] !== Auth::id() && (int)($g['vendedor_id'] ?? 0) !== Auth::id()) {
    echo json_encode(['ok'=>false,'error'=>'Sin permiso']); exit;
}

DB::execute("DELETE FROM gastos_venta WHERE id=?", [$gasto_id]);
echo json_encode(['ok'=>true]);
