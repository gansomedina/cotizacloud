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

// Solo admins o el asesor dueño pueden eliminar
if (!Auth::es_admin() && (int)$cot['usuario_id'] !== (int)Auth::id()) {
    json_error('Sin permiso', 403);
}

// Solo borradores
if ($cot['estado'] !== 'borrador') {
    json_error('Solo se pueden eliminar cotizaciones en borrador', 422);
}

// CASCADE elimina líneas, archivos y log
DB::execute("DELETE FROM cotizaciones WHERE id = ?", [$cot_id]);

json_ok(['id' => $cot_id]);
