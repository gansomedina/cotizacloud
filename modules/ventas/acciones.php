<?php
// ============================================================
//  CotizaApp — modules/ventas/acciones.php
//  POST /ventas/:id/estado
//  POST /ventas/:id/cancelar
//  POST /ventas/:id/agregar-item
//  POST /ventas/:id/notas
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

$empresa_id = EMPRESA_ID;
$venta_id   = (int)($id ?? 0);
$accion     = $accion ?? ''; // inyectada por Router
if (!$venta_id) json_error('ID inválido', 400);

$venta = DB::row(
    "SELECT * FROM ventas WHERE id = ? AND empresa_id = ?",
    [$venta_id, $empresa_id]
);
if (!$venta) json_error('Venta no encontrada', 404);

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ════════════════════════════════════════════════════════════
//  CAMBIAR ESTADO
// ════════════════════════════════════════════════════════════
if ($accion === 'estado') {
    if (!Auth::es_admin()) json_error('Sin permiso', 403);
    if ($venta['estado'] === 'cancelada') json_error('La venta está cancelada', 422);

    $nuevo = $body['estado'] ?? '';
    $validos = ['pendiente','parcial','pagada','entregada'];
    if (!in_array($nuevo, $validos)) json_error('Estado inválido');

    DB::execute(
        "UPDATE ventas SET estado=?, updated_at=NOW() WHERE id=?",
        [$nuevo, $venta_id]
    );

    json_ok(['estado' => $nuevo]);
}

// ════════════════════════════════════════════════════════════
//  CANCELAR VENTA
// ════════════════════════════════════════════════════════════
elseif ($accion === 'cancelar') {
    if (!Auth::es_admin()) json_error('Sin permiso', 403);
    if ($venta['estado'] === 'cancelada') json_error('Ya está cancelada', 422);

    $motivo = trim($body['motivo'] ?? '');
    if (empty($motivo)) json_error('El motivo es requerido');

    // Si tiene abonos no cancelados, no permitir
    $abonos_activos = (int)DB::val(
        "SELECT COUNT(*) FROM recibos WHERE venta_id=? AND tipo='abono' AND cancelado=0",
        [$venta_id]
    );
    if ($abonos_activos > 0) {
        json_error('Debes cancelar todos los abonos antes de cancelar la venta. (' . $abonos_activos . ' abonos activos)');
    }

    DB::execute(
        "UPDATE ventas SET estado='cancelada', notas_internas=CONCAT(COALESCE(notas_internas,''), '\n[Cancelada: ', ?, ']'), updated_at=NOW() WHERE id=?",
        [$motivo, $venta_id]
    );

    json_ok(['estado' => 'cancelada']);
}

// ════════════════════════════════════════════════════════════
//  AGREGAR ITEM (admin only)
// ════════════════════════════════════════════════════════════
elseif ($accion === 'agregar-item') {
    if (!Auth::es_admin()) json_error('Solo administradores', 403);
    if ($venta['estado'] === 'cancelada') json_error('Venta cancelada', 422);

    $titulo    = trim($body['titulo'] ?? '');
    $sku       = trim($body['sku']    ?? '');
    $desc      = trim($body['descripcion'] ?? '');
    $cantidad  = max(0, (float)($body['cantidad']   ?? 1));
    $precio    = max(0, (float)($body['precio_unit'] ?? 0));
    if (empty($titulo)) json_error('El nombre es requerido');

    $subtotal = $cantidad * $precio;

    // Buscar la cotización origen para agregar la línea
    $cot_id = $venta['cotizacion_id'];
    if (!$cot_id) json_error('Esta venta no tiene cotización asociada para agregar artículos');

    $max_orden = (int)DB::val(
        "SELECT MAX(orden) FROM cotizacion_lineas WHERE cotizacion_id=?",
        [$cot_id]
    );

    DB::beginTransaction();
    try {
        DB::execute(
            "INSERT INTO cotizacion_lineas
             (cotizacion_id, orden, sku, titulo, descripcion, cantidad, precio_unit, subtotal)
             VALUES (?,?,?,?,?,?,?,?)",
            [$cot_id, $max_orden + 1, $sku, $titulo, $desc, $cantidad, $precio, $subtotal]
        );

        // Actualizar total de venta
        DB::execute(
            "UPDATE ventas SET total=total+?, saldo=saldo+?, updated_at=NOW() WHERE id=?",
            [$subtotal, $subtotal, $venta_id]
        );

        DB::commit();
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        json_error('Error al agregar artículo', 500);
    }

    json_ok(['subtotal' => $subtotal]);
}

// ════════════════════════════════════════════════════════════
//  GUARDAR NOTAS INTERNAS
// ════════════════════════════════════════════════════════════
elseif ($accion === 'notas') {
    if (!Auth::puede('ver_todas_ventas') && (int)$venta['usuario_id'] !== (int)Auth::id()) {
        json_error('Sin permiso', 403);
    }

    $notas = substr($body['notas_internas'] ?? '', 0, 5000);
    DB::execute(
        "UPDATE ventas SET notas_internas=?, updated_at=NOW() WHERE id=?",
        [$notas, $venta_id]
    );

    json_ok();
}

else {
    json_error('Acción no reconocida: ' . $accion, 404);
}
