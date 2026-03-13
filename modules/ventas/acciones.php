<?php
// ============================================================
//  CotizaApp — modules/ventas/acciones.php
//  POST /ventas/:id/estado
//  POST /ventas/:id/cancelar
//  POST /ventas/:id/agregar-item
//  POST /ventas/:id/notas
// ============================================================

defined('COTIZAAPP') or die;
ob_start(); // Captura output accidental (warnings, notices) antes del JSON

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

    VentaLog::registrar($venta_id, $empresa_id, 'estado_cambiado', 'Estado → ' . ucfirst($nuevo), Auth::id());
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
        "SELECT COUNT(*) FROM recibos WHERE venta_id=? AND cancelado=0",
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

    // Si viene articulo_id, tomar datos del catálogo
    $art_id = (int)($body['articulo_id'] ?? 0);
    if ($art_id) {
        $art = DB::row("SELECT * FROM articulos WHERE id=? AND empresa_id=?", [$art_id, EMPRESA_ID]);
        if (!$art) json_error('Artículo no encontrado');
        $titulo   = $art['titulo'];
        $sku      = $art['sku'] ?? '';
        $desc     = $art['descripcion'] ?? '';
        $precio   = (float)$art['precio'];
        $cantidad = max(0.01, (float)($body['cantidad'] ?? 1));
    } else {
        $titulo    = trim($body['titulo'] ?? '');
        $sku       = trim($body['sku']    ?? '');
        $desc      = trim($body['descripcion'] ?? '');
        $cantidad  = max(0, (float)($body['cantidad']   ?? 1));
        $precio    = max(0, (float)($body['precio_unit'] ?? 0));
        if (empty($titulo)) json_error('El nombre es requerido');
    }
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

// ════════════════════════════════════════════════════════════
//  DESCUENTO MANUAL
// ════════════════════════════════════════════════════════════
elseif ($accion === 'descuento') {
    if (!Auth::es_admin()) json_error('Sin permiso', 403);
    if ($venta['estado'] === 'cancelada') json_error('Venta cancelada', 422);

    $desc_amt = max(0, (float)($body['descuento_manual_amt'] ?? 0));

    // Calcular nuevo total: recalcular desde subtotal de líneas
    $subtotal_lineas = (float)DB::val(
        "SELECT COALESCE(SUM(cl.subtotal),0) FROM cotizacion_lineas cl WHERE cl.cotizacion_id=?",
        [$venta['cotizacion_id']]
    );

    // Traer descuentos de cotización origen (cupon_monto es el nombre real de la columna)
    $cot = DB::row("SELECT cupon_monto, impuesto_pct, impuesto_modo, impuesto_amt FROM cotizaciones WHERE id=?",
        [$venta['cotizacion_id']]);

    $nuevo_subtotal = $subtotal_lineas - (float)($cot['cupon_monto']??0) - $desc_amt;
    if ($cot['impuesto_modo'] === 'suma') {
        $nuevo_total = $nuevo_subtotal * (1 + (float)$cot['impuesto_pct']/100);
    } else {
        $nuevo_total = max(0, $nuevo_subtotal);
    }
    $nuevo_total = round($nuevo_total, 2);
    $nuevo_saldo = round($nuevo_total - (float)$venta['pagado'], 2);

    // Intentar UPDATE con descuento_manual_amt si existe, si no solo total/saldo
    try {
        DB::execute(
            "UPDATE ventas SET descuento_manual_amt=?, total=?, saldo=?, updated_at=NOW() WHERE id=?",
            [$desc_amt, $nuevo_total, $nuevo_saldo, $venta_id]
        );
    } catch (\PDOException $e) {
        // descuento_manual_amt no existe en BD — solo actualizar total/saldo
        DB::execute(
            "UPDATE ventas SET total=?, saldo=?, updated_at=NOW() WHERE id=?",
            [$nuevo_total, $nuevo_saldo, $venta_id]
        );
    }

    json_ok(['total'=>$nuevo_total,'saldo'=>$nuevo_saldo,'descuento'=>$desc_amt]);
}

// ════════════════════════════════════════════════════════════
//  EDITAR / ELIMINAR LÍNEA DE COTIZACIÓN
// ════════════════════════════════════════════════════════════
elseif ($accion === 'editar-linea') {
    if (!Auth::es_admin()) json_error('Solo administradores', 403);
    if ($venta['estado'] === 'cancelada') json_error('Venta cancelada', 422);

    $linea_id = (int)($body['linea_id'] ?? 0);
    if (!$linea_id) json_error('linea_id requerido');

    // Verificar que la línea pertenece a la cotización de esta venta
    $linea = DB::row(
        "SELECT cl.* FROM cotizacion_lineas cl
         JOIN cotizaciones c ON c.id = cl.cotizacion_id
         JOIN ventas v ON v.cotizacion_id = c.id
         WHERE cl.id=? AND v.id=? AND v.empresa_id=?",
        [$linea_id, $venta_id, $empresa_id]
    );
    if (!$linea) json_error('Línea no encontrada o sin permiso', 404);

    DB::beginTransaction();
    try {
        if (!empty($body['eliminar'])) {
            // Eliminar línea
            $subtotal_viejo = (float)$linea['subtotal'];
            DB::execute("DELETE FROM cotizacion_lineas WHERE id=?", [$linea_id]);
            DB::execute(
                "UPDATE ventas SET total=GREATEST(0,total-?), saldo=GREATEST(0,saldo-?), updated_at=NOW() WHERE id=?",
                [$subtotal_viejo, $subtotal_viejo, $venta_id]
            );
        } else {
            // Editar línea
            $titulo   = trim($body['titulo'] ?? '');
            if (empty($titulo)) json_error('El nombre es requerido');
            $sku      = trim($body['sku'] ?? '');
            $desc     = trim($body['descripcion'] ?? '');
            $cantidad = max(0.001, (float)($body['cantidad'] ?? 1));
            $precio   = max(0, (float)($body['precio_unit'] ?? 0));
            $subtotal_nuevo = round($cantidad * $precio, 2);
            $subtotal_viejo = (float)$linea['subtotal'];
            $diff = $subtotal_nuevo - $subtotal_viejo;

            DB::execute(
                "UPDATE cotizacion_lineas SET titulo=?, sku=?, descripcion=?, cantidad=?, precio_unit=?, subtotal=? WHERE id=?",
                [$titulo, $sku, $desc, $cantidad, $precio, $subtotal_nuevo, $linea_id]
            );
            DB::execute(
                "UPDATE ventas SET total=GREATEST(0,total+?), saldo=GREATEST(0,saldo+?), updated_at=NOW() WHERE id=?",
                [$diff, $diff, $venta_id]
            );
        }
        DB::commit();
    } catch (Exception $e) {
        DB::rollback();
        json_error('Error al procesar', 500);
    }

    json_ok();
}

// ════════════════════════════════════════════════════════════
//  CAMBIAR CLIENTE
// ════════════════════════════════════════════════════════════
elseif ($accion === 'cliente') {
    if (!Auth::es_admin()) json_error('Solo administradores', 403);

    $nuevo_cliente_id = (int)($body['cliente_id'] ?? 0);
    if (!$nuevo_cliente_id) json_error('cliente_id requerido');

    // Verificar que el cliente pertenece a esta empresa
    $cli = DB::row("SELECT id, nombre FROM clientes WHERE id=? AND empresa_id=?", [$nuevo_cliente_id, $empresa_id]);
    if (!$cli) json_error('Cliente no encontrado');

    DB::execute(
        "UPDATE ventas SET cliente_id=?, updated_at=NOW() WHERE id=?",
        [$nuevo_cliente_id, $venta_id]
    );

    VentaLog::registrar($venta_id, $empresa_id, 'cliente_cambiado', 'Cliente → ' . $cli['nombre'], Auth::id());
    json_ok(['nombre' => $cli['nombre']]);
}


else {
    json_error('Acción no reconocida: ' . $accion, 404);
}
