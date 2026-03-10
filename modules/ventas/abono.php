<?php
// ============================================================
//  CotizaApp — modules/ventas/abono.php
//  POST /ventas/:id/abono
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

if (!Auth::es_admin()) json_error('Sin permiso', 403);

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();
$venta_id   = (int)($id ?? 0);
if (!$venta_id) json_error('ID inválido', 400);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

// ─── Cargar venta ────────────────────────────────────────
$venta = DB::row(
    "SELECT * FROM ventas WHERE id = ? AND empresa_id = ?",
    [$venta_id, $empresa_id]
);
if (!$venta) json_error('Venta no encontrada', 404);
if (in_array($venta['estado'], ['cancelada', 'entregada'])) {
    json_error('No se puede abonar a una venta ' . $venta['estado'], 422);
}

// ─── Validar ─────────────────────────────────────────────
$formas_validas = ['efectivo', 'transferencia', 'tarjeta'];
$forma_pago = $body['forma_pago'] ?? 'efectivo';
if (!in_array($forma_pago, $formas_validas)) json_error('Forma de pago inválida');

$monto = (float)($body['monto'] ?? 0);
if ($monto <= 0) json_error('El monto debe ser mayor a 0');

$concepto   = substr(trim($body['concepto']   ?? ''), 0, 255);
$referencia = substr(trim($body['referencia'] ?? ''), 0, 255);

// ─── Generar folio recibo ────────────────────────────────
try {
    DB::beginTransaction();

    $numero_rec = DB::siguiente_folio($empresa_id, 'REC', $empresa['rec_prefijo'] ?? 'REC');
    $token_rec  = generar_token(32);

    // Calcular nuevo pagado y saldo
    $nuevo_pagado = round((float)$venta['pagado'] + $monto, 2);
    $nuevo_saldo  = round((float)$venta['total']  - $nuevo_pagado, 2);

    // Estado automático por saldo
    $nuevo_estado = $venta['estado'];
    if ($nuevo_saldo <= 0) {
        $nuevo_estado   = 'pagada';
        $nuevo_saldo    = 0;
        $nuevo_pagado   = (float)$venta['total'];
    } elseif ($nuevo_pagado > 0) {
        $nuevo_estado = 'parcial';
    }

    // Crear recibo
    $recibo_id = DB::insert(
        "INSERT INTO recibos
         (empresa_id, venta_id, usuario_id, numero, token, tipo,
          forma_pago, monto, concepto, referencia,
          pagado_antes, saldo_despues)
         VALUES (?,?,?,?,?,'abono',?,?,?,?,?,?)",
        [
            $empresa_id, $venta_id, Auth::id(),
            $numero_rec, $token_rec,
            $forma_pago, $monto, $concepto, $referencia,
            (float)$venta['pagado'],
            $nuevo_saldo,
        ]
    );

    // Actualizar venta
    DB::execute(
        "UPDATE ventas SET pagado=?, saldo=?, estado=?, updated_at=NOW() WHERE id=?",
        [$nuevo_pagado, $nuevo_saldo, $nuevo_estado, $venta_id]
    );

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    json_error('Error al registrar abono', 500);
}

json_ok([
    'recibo_id' => $recibo_id,
    'numero'    => $numero_rec,
    'pagado'    => $nuevo_pagado,
    'saldo'     => $nuevo_saldo,
    'estado'    => $nuevo_estado,
]);
