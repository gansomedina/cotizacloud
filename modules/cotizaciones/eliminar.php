<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/eliminar.php
//  POST /cotizaciones/:id/eliminar
// ============================================================

defined('COTIZAAPP') or die;

ob_start();
header('Content-Type: application/json; charset=utf-8');

csrf_check();

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

$cot = DB::row(
    "SELECT id, estado, usuario_id FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('No encontrada', 404);

// Solo admins pueden eliminar cotizaciones
if (!Auth::es_admin()) {
    json_error('Solo administradores pueden eliminar cotizaciones', 403);
}

// Si tiene venta asociada activa (no cancelada), no permitir eliminar
$venta_activa = DB::row(
    "SELECT id, estado FROM ventas WHERE cotizacion_id = ? AND estado NOT IN ('cancelada')",
    [$cot_id]
);
if ($venta_activa) {
    json_error('Esta cotización tiene una venta activa (' . ($venta_activa['estado']) . '). Cancela la venta primero.', 422);
}

try {
    DB::beginTransaction();

    // Eliminar ventas canceladas y sus dependencias
    $ventas_canceladas = DB::query("SELECT id FROM ventas WHERE cotizacion_id = ? AND estado = 'cancelada'", [$cot_id]);
    foreach ($ventas_canceladas as $vc) {
        DB::execute("DELETE FROM recibos WHERE venta_id = ?", [$vc['id']]);
        DB::execute("DELETE FROM gastos_venta WHERE venta_id = ?", [$vc['id']]);
        DB::execute("UPDATE cotizacion_lineas SET venta_id = NULL WHERE venta_id = ?", [$vc['id']]);
        DB::execute("DELETE FROM ventas WHERE id = ?", [$vc['id']]);
    }

    // Eliminar bucket_transitions ligadas
    DB::execute("DELETE FROM bucket_transitions WHERE cotizacion_id = ?", [$cot_id]);

    // CASCADE elimina: cotizacion_lineas, cotizacion_archivos, cotizacion_log, quote_events, quote_sessions
    DB::execute("DELETE FROM cotizaciones WHERE id = ?", [$cot_id]);

    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    ob_end_clean();
    json_error('Error al eliminar: ' . $e->getMessage(), 500);
}

ob_end_clean();
json_ok(['id' => $cot_id]);
