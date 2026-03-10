<?php
// ============================================================
//  CotizaApp — modules/ventas/cancelar_recibo.php
//  POST /ventas/recibos/:id/cancelar
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();
$recibo_id  = (int)($id ?? 0);
if (!$recibo_id) json_error('ID inválido', 400);

$body = json_decode(file_get_contents('php://input'), true);
$motivo = trim($body['motivo'] ?? '');
if (empty($motivo)) json_error('El motivo es requerido');

// ─── Cargar recibo ───────────────────────────────────────
$recibo = DB::row(
    "SELECT r.*, v.estado AS venta_estado, v.pagado, v.saldo, v.total
     FROM recibos r JOIN ventas v ON v.id = r.venta_id
     WHERE r.id = ? AND r.empresa_id = ?",
    [$recibo_id, $empresa_id]
);
if (!$recibo) json_error('Recibo no encontrado', 404);
if ($recibo['cancelado']) json_error('Este recibo ya está cancelado', 422);
if ($recibo['tipo'] !== 'abono') json_error('Solo se pueden cancelar recibos de abono', 422);

// Solo admin o asesor con permiso
if (!Auth::es_admin() && !Auth::puede('cancelar_recibos')) {
    json_error('Sin permiso', 403);
}

try {
    DB::beginTransaction();

    // Folio de cancelación
    $numero_canc = DB::siguiente_folio($empresa_id, 'REC', $empresa['rec_prefijo'] ?? 'REC');
    $token_canc  = generar_token(32);
    $monto       = (float)$recibo['monto'];

    // Nuevo pagado y saldo
    $nuevo_pagado = round((float)$recibo['pagado'] - $monto, 2);
    if ($nuevo_pagado < 0) $nuevo_pagado = 0;
    $nuevo_saldo  = round((float)$recibo['total'] - $nuevo_pagado, 2);

    // Estado automático
    $nuevo_estado = 'pendiente';
    if ($nuevo_pagado > 0 && $nuevo_saldo > 0) $nuevo_estado = 'parcial';
    elseif ($nuevo_saldo <= 0) $nuevo_estado = 'pagada';

    // Marcar recibo original como cancelado
    DB::execute(
        "UPDATE recibos SET cancelado=1, cancelado_motivo=?, cancelado_at=NOW() WHERE id=?",
        [$motivo, $recibo_id]
    );

    // Crear recibo de cancelación (monto negativo)
    $canc_id = DB::insert(
        "INSERT INTO recibos
         (empresa_id, venta_id, usuario_id, numero, token, tipo,
          forma_pago, monto, concepto, referencia,
          cancelado_por_id, pagado_antes, saldo_despues)
         VALUES (?,?,?,?,?,'cancelacion',?,?,?,?,?,?,?)",
        [
            $empresa_id,
            $recibo['venta_id'],
            Auth::id(),
            $numero_canc,
            $token_canc,
            $recibo['forma_pago'],
            -$monto,
            'Cancelación de ' . $recibo['numero'],
            $motivo,
            $recibo_id,
            (float)$recibo['pagado'],
            $nuevo_saldo,
        ]
    );

    // Actualizar venta
    DB::execute(
        "UPDATE ventas SET pagado=?, saldo=?, estado=?, updated_at=NOW() WHERE id=?",
        [$nuevo_pagado, $nuevo_saldo, $nuevo_estado, $recibo['venta_id']]
    );

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    json_error('Error al cancelar recibo', 500);
}

json_ok([
    'cancelacion_id' => $canc_id,
    'numero'         => $numero_canc,
    'nuevo_pagado'   => $nuevo_pagado,
    'nuevo_saldo'    => $nuevo_saldo,
    'nuevo_estado'   => $nuevo_estado,
]);
