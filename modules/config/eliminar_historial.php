<?php
// ============================================================
//  CotizaApp — modules/config/eliminar_historial.php
//  POST /config/historial/:id/eliminar
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

Auth::requerir_admin();
csrf_check();

$empresa_id = EMPRESA_ID;
$hist_id    = (int)($id ?? 0);

if (!$hist_id) json_error('ID inválido');

// Verificar que pertenece a esta empresa
$existe = DB::val(
    "SELECT id FROM historial_mensual WHERE id = ? AND empresa_id = ?",
    [$hist_id, $empresa_id]
);

if (!$existe) json_error('Registro no encontrado', 404);

DB::execute("DELETE FROM historial_mensual WHERE id = ? AND empresa_id = ?", [$hist_id, $empresa_id]);

json_ok(['deleted' => true]);
