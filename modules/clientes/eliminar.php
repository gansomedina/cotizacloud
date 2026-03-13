<?php
// ============================================================
//  CotizaApp — modules/clientes/eliminar.php
//  POST /clientes/:id/eliminar
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

if (!Auth::es_admin()) json_error('Solo administradores pueden eliminar clientes', 403);

$empresa_id = EMPRESA_ID;
$cliente_id = (int)($id ?? 0);
if (!$cliente_id) json_error('ID inválido', 400);

// ─── Verificar existencia ────────────────────────────────
$cliente = DB::row(
    "SELECT id, nombre FROM clientes WHERE id = ? AND empresa_id = ?",
    [$cliente_id, $empresa_id]
);
if (!$cliente) json_error('Cliente no encontrado', 404);

// ─── Verificar que no tenga actividad ────────────────────
$num_cots = (int) DB::val(
    "SELECT COUNT(*) FROM cotizaciones WHERE cliente_id = ? AND empresa_id = ?",
    [$cliente_id, $empresa_id]
);
if ($num_cots > 0) json_error("No se puede eliminar: tiene $num_cots cotización(es) asociada(s)");

$num_ventas = (int) DB::val(
    "SELECT COUNT(*) FROM ventas WHERE cliente_id = ? AND empresa_id = ?",
    [$cliente_id, $empresa_id]
);
if ($num_ventas > 0) json_error("No se puede eliminar: tiene $num_ventas venta(s) asociada(s)");

// ─── Eliminar ────────────────────────────────────────────
DB::execute("DELETE FROM clientes WHERE id = ?", [$cliente_id]);

json_ok(['eliminado' => $cliente_id]);
