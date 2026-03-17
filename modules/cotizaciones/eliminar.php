<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/eliminar.php
//  POST /cotizaciones/:id/eliminar
// ============================================================

defined('COTIZAAPP') or die;

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

// No permitir eliminar cotizaciones convertidas (ya tienen venta asociada)
if ($cot['estado'] === 'convertida') {
    json_error('No se puede eliminar una cotización convertida en venta', 422);
}

// CASCADE elimina líneas, archivos y log
DB::execute("DELETE FROM cotizaciones WHERE id = ?", [$cot_id]);

json_ok(['id' => $cot_id]);
