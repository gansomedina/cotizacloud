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

    // La venta y su Descuento Inteligente se liberan JUNTOS: sin esto la
    // activación queda 'utilizado' huérfana (quitar-desc-int rechaza ventas
    // canceladas) y el cliente queda bloqueado de DI para siempre por
    // uk_cliente_vivo. OJO: esto NO regresa la cotización a la mesa — sigue
    // con estado 'aceptada'/'convertida' y accion_at seteado (fuera del
    // universo de Mesa::armar); revertir el estado de la cotización al
    // cancelar la venta es una decisión de producto pendiente.
    DB::beginTransaction();
    try {
        DB::execute(
            "UPDATE ventas SET estado='cancelada', notas_internas=CONCAT(COALESCE(notas_internas,''), '\n[Cancelada: ', ?, ']'), updated_at=NOW() WHERE id=?",
            [$motivo, $venta_id]
        );
        if (!empty($venta['cotizacion_id'])) {
            try {
                DB::execute(
                    "UPDATE desc_int_activaciones SET estado='cancelado'
                     WHERE cotizacion_id=? AND estado='utilizado'",
                    [(int)$venta['cotizacion_id']]
                );
            } catch (Throwable $e) {
                // tabla sin migrar = no hay activaciones que liberar; cualquier
                // otro error se loguea pero no bloquea la cancelación de la venta
                error_log('[Venta cancelar][DI] ' . $e->getMessage());
            }
        }
        DB::commit();
    } catch (Throwable $e) {
        try { DB::rollback(); } catch (Throwable $e2) {}
        error_log('[Venta cancelar] ' . $e->getMessage());
        json_error('No se pudo cancelar la venta', 500);
    }

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

    // DI 'utilizado': agregar un artículo REGULAR cambia la base del contrato
    // congelado → se bloquea (como el descuento manual). Para editar el precio
    // de una venta con DI, se quita el DI primero (los extras add-on sí se permiten).
    try {
        if (DB::val("SELECT 1 FROM desc_int_activaciones WHERE cotizacion_id=? AND estado='utilizado' LIMIT 1", [$cot_id])) {
            json_error('Esta venta tiene Descuento Inteligente. Para agregar artículos, primero quita el DI.', 422);
        }
    } catch (\Throwable $e) {} // tabla sin migrar → sin bloqueo

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

        // Recomputar el total (antes hacía total=total+subtotal SIN IVA → subcobro
        // en modo suma). Modelo: base sin extras descontada + extras gravados, IVA
        // según config. Sin DI (bloqueado arriba). Igual que ventas/guardar.
        $base_ne = (float)DB::val("SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=? AND es_extra=0", [$cot_id]);
        $extras  = (float)DB::val("SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=? AND es_extra=1", [$cot_id]);
        $cotd = DB::row("SELECT impuesto_pct, impuesto_modo, cupon_monto, descuento_auto_amt FROM cotizaciones WHERE id=?", [$cot_id]);
        $imp_pct   = (float)($cotd['impuesto_pct'] ?? 0);
        $imp_modo  = $cotd['impuesto_modo'] ?? 'ninguno';
        $taxable   = max(0, $base_ne - (float)($cotd['cupon_monto'] ?? 0) - (float)($cotd['descuento_auto_amt'] ?? 0)) + $extras;
        $nuevo_total = ($imp_modo === 'suma')
            ? round($taxable + round($taxable * $imp_pct / 100, 2), 2)
            : round($taxable, 2);
        $nuevo_saldo = max(0, round($nuevo_total - (float)$venta['pagado'], 2));
        DB::execute("UPDATE ventas SET total=?, saldo=?, updated_at=NOW() WHERE id=?",
            [$nuevo_total, $nuevo_saldo, $venta_id]);
        DB::execute("UPDATE cotizaciones SET subtotal=(SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=?), updated_at=NOW() WHERE id=?",
            [$cot_id, $cot_id]);

        DB::commit();
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        json_error('Error al agregar artículo', 500);
    }

    json_ok(['subtotal' => $subtotal, 'total' => $nuevo_total, 'saldo' => $nuevo_saldo]);
}

// ════════════════════════════════════════════════════════════
//  GUARDAR NOTAS INTERNAS
// ════════════════════════════════════════════════════════════
elseif ($accion === 'notas') {
    if (!Auth::puede('ver_todas_ventas') && (int)$venta['usuario_id'] !== (int)Auth::id() && (int)($venta['vendedor_id'] ?? 0) !== (int)Auth::id()) {
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
    if (!Auth::es_admin() && !Auth::puede('aplicar_descuentos')) json_error('Sin permiso', 403);
    if ($venta['estado'] === 'cancelada') json_error('Venta cancelada', 422);

    // DI 'utilizado': el precio lo manda el contrato congelado; no se apila un
    // descuento manual encima (regla CEO). Antes esta acción recalculaba
    // total = lineas − cupon − manual, BORRANDO el DI (revive COT-2026-0185 por
    // otra ruta). Para cambiar el precio de una venta con DI, se quita el DI
    // primero (acción quitar-desc-int, que restaura el total).
    try {
        if (DB::val("SELECT 1 FROM desc_int_activaciones WHERE cotizacion_id=? AND estado='utilizado' LIMIT 1",
                    [$venta['cotizacion_id']])) {
            json_error('Esta venta tiene Descuento Inteligente. Para cambiar el precio, primero quita el DI.', 422);
        }
    } catch (\Throwable $e) {} // tabla sin migrar → sin bloqueo

    $desc_amt = max(0, (float)($body['descuento_manual_amt'] ?? 0));

    // Base sin extras (descontable) y extras (add-ons gravables, no descontables)
    $base_ne_lineas = (float)DB::val(
        "SELECT COALESCE(SUM(cl.subtotal),0) FROM cotizacion_lineas cl WHERE cl.cotizacion_id=? AND cl.es_extra=0",
        [$venta['cotizacion_id']]);
    $extras_lineas = (float)DB::val(
        "SELECT COALESCE(SUM(cl.subtotal),0) FROM cotizacion_lineas cl WHERE cl.cotizacion_id=? AND cl.es_extra=1",
        [$venta['cotizacion_id']]);

    // Traer descuentos de cotización origen (cupon_monto es el nombre real de la columna)
    $cot = DB::row("SELECT cupon_monto, impuesto_pct, impuesto_modo, impuesto_amt FROM cotizaciones WHERE id=?",
        [$venta['cotizacion_id']]);

    // Descuentos SOLO a la base sin extras; los extras entran a la base gravable.
    $base_ne  = $base_ne_lineas - (float)($cot['cupon_monto'] ?? 0) - $desc_amt;
    $taxable  = max(0, $base_ne) + $extras_lineas;
    if ($cot['impuesto_modo'] === 'suma') {
        $nuevo_total = round($taxable + round($taxable * (float)$cot['impuesto_pct'] / 100, 2), 2);
    } else {
        $nuevo_total = round($taxable, 2);
    }
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
    if ($venta['estado'] === 'cancelada') json_error('Venta cancelada', 422);

    $linea_id = (int)($body['linea_id'] ?? 0);
    if (!$linea_id) json_error('linea_id requerido');

    // Verificar permisos: eliminar requiere puede_eliminar_items_venta, editar requiere admin o editar_precios
    $es_eliminar = !empty($body['eliminar']);
    if ($es_eliminar) {
        if (!Auth::es_admin() && !Auth::puede('eliminar_items_venta')) json_error('Sin permiso para eliminar', 403);
    } else {
        if (!Auth::es_admin() && !Auth::puede('editar_precios')) json_error('Sin permiso para editar', 403);
    }

    // Verificar que la línea pertenece a la cotización de esta venta
    $linea = DB::row(
        "SELECT cl.* FROM cotizacion_lineas cl
         JOIN cotizaciones c ON c.id = cl.cotizacion_id
         JOIN ventas v ON v.cotizacion_id = c.id
         WHERE cl.id=? AND v.id=? AND v.empresa_id=?",
        [$linea_id, $venta_id, $empresa_id]
    );
    if (!$linea) json_error('Línea no encontrada o sin permiso', 404);
    $cot_id = (int)$linea['cotizacion_id'];

    // DI 'utilizado': editar/eliminar una línea REGULAR cambia la base del
    // contrato congelado → se bloquea (los extras add-on sí se permiten vía
    // agregar/eliminar_extra). Para cambiar el precio base, se quita el DI.
    if (!(int)$linea['es_extra']) {
        try {
            if (DB::val("SELECT 1 FROM desc_int_activaciones WHERE cotizacion_id=? AND estado='utilizado' LIMIT 1", [$cot_id])) {
                json_error('Esta venta tiene Descuento Inteligente. Para cambiar artículos, primero quita el DI.', 422);
            }
        } catch (\Throwable $e) {} // tabla sin migrar → sin bloqueo
    }

    DB::beginTransaction();
    try {
        if ($es_eliminar) {
            DB::execute("DELETE FROM cotizacion_lineas WHERE id=?", [$linea_id]);
        } else {
            $titulo   = trim($body['titulo'] ?? '');
            if (empty($titulo)) json_error('El nombre es requerido');
            $sku      = trim($body['sku'] ?? '');
            $desc     = trim($body['descripcion'] ?? '');
            $cantidad = max(0.001, (float)($body['cantidad'] ?? 1));
            $precio   = max(0, (float)($body['precio_unit'] ?? 0));
            $subtotal_nuevo = round($cantidad * $precio, 2);
            DB::execute(
                "UPDATE cotizacion_lineas SET titulo=?, sku=?, descripcion=?, cantidad=?, precio_unit=?, subtotal=? WHERE id=?",
                [$titulo, $sku, $desc, $cantidad, $precio, $subtotal_nuevo, $linea_id]
            );
        }

        // Recompute completo (antes sumaba/restaba el subtotal crudo SIN IVA →
        // subcobro en modo suma). Modelo: base sin extras descontada + extras
        // gravados; DI-aware (nuevo_total congelado + extras gravados).
        $base_ne = (float)DB::val("SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=? AND es_extra=0", [$cot_id]);
        $extras  = (float)DB::val("SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=? AND es_extra=1", [$cot_id]);
        $cotd = DB::row("SELECT impuesto_pct, impuesto_modo, cupon_monto, descuento_auto_amt FROM cotizaciones WHERE id=?", [$cot_id]);
        $imp_pct  = (float)($cotd['impuesto_pct'] ?? 0);
        $imp_modo = $cotd['impuesto_modo'] ?? 'ninguno';
        $extras_final = ($imp_modo === 'suma') ? round($extras + round($extras * $imp_pct / 100, 2), 2) : $extras;
        $di_nt = null;
        try {
            $v = DB::val("SELECT nuevo_total FROM desc_int_activaciones WHERE cotizacion_id=? AND estado='utilizado' ORDER BY id DESC LIMIT 1", [$cot_id]);
            if ($v !== false && $v !== null) $di_nt = (float)$v;
        } catch (\Throwable $e) {}
        if ($di_nt !== null) {
            $nuevo_total = round($di_nt + $extras_final, 2);
        } else {
            $taxable = max(0, $base_ne - (float)($cotd['cupon_monto'] ?? 0) - (float)($cotd['descuento_auto_amt'] ?? 0)) + $extras;
            $nuevo_total = ($imp_modo === 'suma') ? round($taxable + round($taxable * $imp_pct / 100, 2), 2) : round($taxable, 2);
        }
        $nuevo_saldo = max(0, round($nuevo_total - (float)$venta['pagado'], 2));
        DB::execute("UPDATE ventas SET total=?, saldo=?, updated_at=NOW() WHERE id=?", [$nuevo_total, $nuevo_saldo, $venta_id]);
        DB::execute("UPDATE cotizaciones SET subtotal=(SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=?), updated_at=NOW() WHERE id=?", [$cot_id, $cot_id]);
        DB::commit();
    } catch (Exception $e) {
        DB::rollback();
        json_error('Error al procesar', 500);
    }

    json_ok(['total' => $nuevo_total, 'saldo' => $nuevo_saldo]);
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

elseif ($accion === 'vendedor') {
    if (!Auth::es_admin()) json_error('Solo administradores', 403);

    $nuevo_vendedor_id = (int)($body['vendedor_id'] ?? 0);
    if (!$nuevo_vendedor_id) json_error('vendedor_id requerido');

    $usr = DB::row("SELECT id, nombre FROM usuarios WHERE id=? AND empresa_id=? AND activo=1", [$nuevo_vendedor_id, $empresa_id]);
    if (!$usr) json_error('Usuario no encontrado');

    $anterior = DB::val("SELECT COALESCE(u.nombre, '—') FROM ventas v LEFT JOIN usuarios u ON u.id = v.vendedor_id WHERE v.id=?", [$venta_id]);

    DB::execute("UPDATE ventas SET vendedor_id=?, updated_at=NOW() WHERE id=?", [$nuevo_vendedor_id, $venta_id]);

    if ($venta['cotizacion_id']) {
        DB::execute("UPDATE cotizaciones SET vendedor_id=?, updated_at=NOW() WHERE id=?", [$nuevo_vendedor_id, $venta['cotizacion_id']]);
    }

    VentaLog::registrar($venta_id, $empresa_id, 'vendedor_cambiado', ($anterior ?: '—') . ' → ' . $usr['nombre'], Auth::id());
    json_ok(['nombre' => $usr['nombre']]);
}


// ════════════════════════════════════════════════════════════
//  QUITAR DESCUENTO INTELIGENTE (restaura el precio lleno)
// ════════════════════════════════════════════════════════════
elseif ($accion === 'quitar-desc-int') {
    if (!Auth::es_admin() && !Auth::puede('aplicar_descuentos')) json_error('Sin permiso', 403);
    if ($venta['estado'] === 'cancelada') json_error('Venta cancelada', 422);

    // La activación 'utilizado' de esta cotización tiene el monto exacto que
    // se descontó (con IVA). Restaurar = total + monto_desc revierte justo lo
    // que aplicó quote_action al aceptar (validado).
    //
    // Transaccional + FOR UPDATE: sin esto, dos POST concurrentes (o un abono
    // que entre en medio) leían $venta stale y sumaban monto_desc dos veces /
    // pisaban el saldo. El lock de la venta + re-lectura fresca + marcar la
    // activación con WHERE estado='utilizado' garantizan una sola reversa.
    DB::beginTransaction();
    try {
        $v = DB::row("SELECT total, pagado FROM ventas WHERE id=? AND empresa_id=? FOR UPDATE",
            [$venta_id, $empresa_id]);
        if (!$v) { DB::rollback(); json_error('Venta no encontrada', 404); }

        $act = DB::row(
            "SELECT id, monto_desc FROM desc_int_activaciones
             WHERE cotizacion_id=? AND empresa_id=? AND estado='utilizado'
             ORDER BY id DESC LIMIT 1 FOR UPDATE",
            [(int)$venta['cotizacion_id'], $empresa_id]
        );
        if (!$act) { DB::rollback(); json_error('Esta venta no tiene descuento inteligente aplicado', 422); }

        // Marcar cancelado SOLO si sigue 'utilizado' — el que gana la carrera revierte.
        $marcada = DB::execute("UPDATE desc_int_activaciones SET estado='cancelado' WHERE id=? AND estado='utilizado'",
            [(int)$act['id']]);
        if ($marcada < 1) { DB::rollback(); json_error('El descuento ya fue quitado', 422); }

        $nuevo_total = round((float)$v['total'] + (float)$act['monto_desc'], 2);
        $nuevo_saldo = round($nuevo_total - (float)$v['pagado'], 2);
        DB::execute("UPDATE ventas SET total=?, saldo=?, updated_at=NOW() WHERE id=?",
            [$nuevo_total, $nuevo_saldo, $venta_id]);

        VentaLog::registrar($venta_id, $empresa_id, 'descuento_quitado',
            'Descuento inteligente quitado (+' . number_format((float)$act['monto_desc'], 2) . ')', Auth::id());
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollback();
        json_error('No se pudo quitar el descuento', 500);
    }
    json_ok(['total'=>$nuevo_total, 'saldo'=>$nuevo_saldo]);
}

else {
    json_error('Acción no reconocida: ' . $accion, 404);
}
