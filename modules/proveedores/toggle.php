<?php
// ============================================================
//  CotizaApp — modules/proveedores/toggle.php
//  POST /proveedores/:id/toggle  → activar/desactivar
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

$empresa_id   = EMPRESA_ID;
$proveedor_id = (int)($id ?? 0);

$plan = trial_info($empresa_id);
if (!$plan['es_business']) json_error('Función exclusiva del plan Business', 403);

if (!$proveedor_id) json_error('ID inválido');

$prov = DB::row(
    "SELECT id, activo FROM proveedores WHERE id = ? AND empresa_id = ?",
    [$proveedor_id, $empresa_id]
);
if (!$prov) json_error('Proveedor no encontrado', 404);

$nuevo = $prov['activo'] ? 0 : 1;

DB::exec(
    "UPDATE proveedores SET activo = ? WHERE id = ? AND empresa_id = ?",
    [$nuevo, $proveedor_id, $empresa_id]
);

json_ok(['activo' => $nuevo]);
