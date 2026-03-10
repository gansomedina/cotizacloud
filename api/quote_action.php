<?php
// ============================================================
//  CotizaApp — api/quote_action.php
//  POST /api/quote-action  (sin login)
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Payload inválido']); exit; }

$cot_id = (int)($body['cotizacion_id'] ?? 0);
$accion = trim($body['accion'] ?? '');

if (!$cot_id || !$accion) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Datos requeridos']); exit;
}

$acciones_validas = ['aceptar','rechazar'];
if (!in_array($accion, $acciones_validas)) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Acción inválida']); exit;
}

// ─── Cargar cotización ───────────────────────────────────
$cot = DB::row(
    "SELECT id, empresa_id, estado, cliente_id, titulo FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, EMPRESA_ID]
);
if (!$cot) {
    http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Cotización no encontrada']); exit;
}

$estado_actual = $cot['estado'];
$estados_activos = ['enviada','vista','aceptada'];

// ─── Aceptar ─────────────────────────────────────────────
if ($accion === 'aceptar') {
    if (!in_array($estado_actual, $estados_activos)) {
        echo json_encode(['ok'=>false,'error'=>'Esta cotización ya no está activa']); exit;
    }

    $nombre        = trim($body['nombre']          ?? '');
    $total_final   = (float)($body['total_final']   ?? 0);
    $desc_auto_amt = (float)($body['descuento_auto_amt'] ?? 0);
    $cupon_codigo  = trim($body['cupon_codigo']    ?? '');
    $cupon_pct     = (float)($body['cupon_pct']    ?? 0);

    if (!$nombre) {
        echo json_encode(['ok'=>false,'error'=>'El nombre es requerido']); exit;
    }

    try {
        DB::beginTransaction();

        // Actualizar estado
        DB::execute(
            "UPDATE cotizaciones SET
                estado          = 'aceptada',
                aceptada_at     = NOW(),
                aceptada_nombre = ?,
                total           = CASE WHEN ? > 0 THEN ? ELSE total END
             WHERE id = ?",
            [$nombre, $total_final, $total_final, $cot_id]
        );

        // Guardar cupón aplicado si hay
        if ($cupon_codigo && $cupon_pct > 0) {
            DB::execute(
                "UPDATE cotizaciones SET cupon_codigo=?, cupon_pct=? WHERE id=?",
                [$cupon_codigo, $cupon_pct, $cot_id]
            );
            // Incrementar usos del cupón
            DB::execute(
                "UPDATE cupones SET usos=usos+1 WHERE empresa_id=? AND codigo=?",
                [EMPRESA_ID, $cupon_codigo]
            );
        }

        // Log
        DB::execute(
            "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, detalle)
             VALUES (?,NULL,'aceptada_cliente',?)",
            [
                $cot_id,
                'Aceptada por: '.$nombre.($cupon_codigo ? ' | Cupón: '.$cupon_codigo : '').($total_final ? ' | Total final: $'.number_format($total_final,2) : '')
            ]
        );

        DB::commit();
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error al procesar']); exit;
    }

    echo json_encode(['ok'=>true, 'estado'=>'aceptada']); exit;
}

// ─── Rechazar ────────────────────────────────────────────
if ($accion === 'rechazar') {
    if (!in_array($estado_actual, $estados_activos)) {
        echo json_encode(['ok'=>false,'error'=>'Esta cotización ya no está activa']); exit;
    }

    $motivo = trim($body['motivo'] ?? '');

    try {
        DB::beginTransaction();

        DB::execute(
            "UPDATE cotizaciones SET
                estado         = 'rechazada',
                rechazada_at   = NOW(),
                rechazada_motivo = ?
             WHERE id = ?",
            [$motivo ?: null, $cot_id]
        );

        DB::execute(
            "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, detalle)
             VALUES (?,NULL,'rechazada_cliente',?)",
            [$cot_id, 'Rechazada desde vista pública'.($motivo ? ': '.$motivo : '')]
        );

        DB::commit();
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error al procesar']); exit;
    }

    echo json_encode(['ok'=>true,'estado'=>'rechazada']); exit;
}
