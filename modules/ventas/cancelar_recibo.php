<?php
// ============================================================
//  CotizaApp — modules/ventas/cancelar_recibo.php
//  POST /ventas/recibos/:id/cancelar
// ============================================================

defined('COTIZAAPP') or die;
ob_start(); // Captura output accidental (warnings, notices) antes del JSON

header('Content-Type: application/json; charset=utf-8');

csrf_check();

if (!Auth::es_admin() && !Auth::puede('cancelar_recibos')) json_error('Sin permiso', 403);

$empresa_id = EMPRESA_ID;
$recibo_id  = (int)($id ?? 0);
if (!$recibo_id) json_error('ID inválido', 400);

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$motivo = trim($body['motivo'] ?? '');
if (empty($motivo)) json_error('El motivo es requerido');

// ─── Cargar recibo ───────────────────────────────────────
$recibo = DB::row(
    "SELECT r.*, v.pagado AS venta_pagado, v.saldo AS venta_saldo, v.total AS venta_total
     FROM recibos r
     JOIN ventas v ON v.id = r.venta_id
     WHERE r.id = ? AND r.empresa_id = ?",
    [$recibo_id, $empresa_id]
);
if (!$recibo) json_error('Recibo no encontrado', 404);
if ($recibo['cancelado']) json_error('Este recibo ya está cancelado', 422);

$monto = (float)$recibo['monto'];

try {
    DB::beginTransaction();

    // Marcar recibo como cancelado (notas guarda el motivo)
    DB::execute(
        "UPDATE recibos SET cancelado=1, cancelado_at=NOW(),
         notas=CONCAT(COALESCE(notas,''), ' [Cancelado: ', ?, ']')
         WHERE id=?",
        [$motivo, $recibo_id]
    );

    // Recalcular pagado y saldo
    $nuevo_pagado = round((float)$recibo['venta_pagado'] - $monto, 2);
    if ($nuevo_pagado < 0) $nuevo_pagado = 0;
    $nuevo_saldo  = round((float)$recibo['venta_total'] - $nuevo_pagado, 2);

    // Estado automático
    if ($nuevo_saldo <= 0) {
        $nuevo_estado = 'pagada';
        $nuevo_saldo  = 0;
    } elseif ($nuevo_pagado > 0) {
        $nuevo_estado = 'parcial';
    } else {
        $nuevo_estado = 'pendiente';
    }

    // Actualizar venta
    DB::execute(
        "UPDATE ventas SET pagado=?, saldo=?, estado=?, updated_at=NOW() WHERE id=?",
        [$nuevo_pagado, $nuevo_saldo, $nuevo_estado, $recibo['venta_id']]
    );

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    ob_end_clean();
    json_error('Error al cancelar recibo', 500);
}

VentaLog::registrar(
    (int)$recibo['venta_id'], $empresa_id,
    'abono_cancelado',
    'Recibo ' . ($recibo['numero'] ?? '#') . ' · -$' . number_format((float)$recibo['monto'], 2) . ($motivo ? ' · ' . $motivo : ''),
    Auth::id()
);
ob_end_clean();
json_ok([
    'nuevo_pagado' => $nuevo_pagado,
    'nuevo_saldo'  => $nuevo_saldo,
    'nuevo_estado' => $nuevo_estado,
]);
