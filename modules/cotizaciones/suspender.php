<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/suspender.php
//  POST /cotizaciones/:id/suspender
//  Toggle suspender / reactivar cotización
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');
csrf_check();

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

$cot = DB::row(
    "SELECT id, estado, suspendida, titulo, numero FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('No encontrada', 404);

// No suspender cotizaciones ya cerradas
if (in_array($cot['estado'], ['aceptada', 'convertida', 'aceptada_cliente'])) {
    json_error('No se puede suspender una cotización ' . $cot['estado'], 422);
}

$nueva = $cot['suspendida'] ? 0 : 1;
$accion = $nueva ? 'suspendida' : 'reactivada';

DB::execute(
    "UPDATE cotizaciones SET suspendida = ?, suspendida_at = ? WHERE id = ?",
    [$nueva, $nueva ? date('Y-m-d H:i:s') : null, $cot_id]
);

// Limpiar radar si se suspende
if ($nueva) {
    DB::execute(
        "UPDATE cotizaciones SET radar_bucket = NULL, radar_score = NULL, radar_senales = NULL WHERE id = ?",
        [$cot_id]
    );
}

// Log
DB::insert(
    "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, evento, detalle, created_at) VALUES (?,?,?,?,NOW())",
    [$cot_id, Auth::id(), $accion, 'Cotización ' . $accion . ' por ' . Auth::usuario()['nombre']]
);

json_ok(['id' => $cot_id, 'suspendida' => $nueva, 'accion' => $accion]);
