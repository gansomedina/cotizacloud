<?php
// ============================================================
//  CotizaApp — modules/ventas/guardar.php
//  POST /ventas/:id/guardar
//  Batch save: líneas + descuento + cliente
// ============================================================
defined('COTIZAAPP') or die;
ob_start(); // Captura output accidental (warnings, notices) antes del JSON
header('Content-Type: application/json; charset=utf-8');
csrf_check();

if (!Auth::es_admin() && !Auth::puede('eliminar_items_venta') && !Auth::puede('editar_cotizaciones')) json_error('Sin permisos para editar', 403);

$empresa_id = EMPRESA_ID;
$venta_id   = (int)($id ?? 0);
if (!$venta_id) json_error('ID inválido', 400);

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$venta = DB::row("SELECT * FROM ventas WHERE id=? AND empresa_id=?", [$venta_id, $empresa_id]);
if (!$venta) json_error('Venta no encontrada', 404);
if ($venta['estado'] === 'cancelada') json_error('Venta cancelada', 422);

$lineas_new       = $body['lineas'] ?? [];
$desc_auto_amt    = round(max(0, (float)($body['descuento_auto_amt'] ?? 0)), 2);
$desc_auto_pct    = round(max(0, (float)($body['descuento_auto_pct'] ?? 0)), 2);
if (($desc_auto_amt > 0 || $desc_auto_pct > 0) && !Auth::es_admin() && !Auth::puede('aplicar_descuentos')) {
    json_error('Sin permiso para aplicar descuentos', 403);
}
$nuevo_cliente_id = isset($body['cliente_id']) && $body['cliente_id'] ? (int)$body['cliente_id'] : null;

if (empty($lineas_new)) json_error('Debe haber al menos un artículo');

$cot_id = (int)$venta['cotizacion_id'];
if (!$cot_id) json_error('Esta venta no tiene cotización asociada');

// ── Leer estado ANTES del save (para comparar en log) ──
$cot_antes = DB::row(
    "SELECT descuento_auto_amt, subtotal FROM cotizaciones WHERE id=?",
    [$cot_id]
);
$lineas_count_antes = (int)DB::val(
    "SELECT COUNT(*) FROM cotizacion_lineas WHERE cotizacion_id=?",
    [$cot_id]
);
$subtotal_anterior = (float)($cot_antes['subtotal'] ?? 0);

DB::beginTransaction();
try {
    // ── Reemplazar líneas de la cotización ──
    DB::execute("DELETE FROM cotizacion_lineas WHERE cotizacion_id=?", [$cot_id]);

    $subtotal_lineas = 0;
    $extras_lineas   = 0.0; // extras (raw, sin IVA extra) — para el total con DI
    foreach ($lineas_new as $orden => $l) {
        $titulo   = substr(trim($l['titulo'] ?? ''), 0, 255);
        $sku      = substr(trim($l['sku'] ?? ''), 0, 100);
        $desc     = substr(trim($l['descripcion'] ?? ''), 0, 1000);
        $cantidad = max(0.001, (float)($l['cantidad'] ?? 1));
        $precio   = max(0, (float)($l['precio_unit'] ?? 0));
        $subtotal = round($cantidad * $precio, 2);
        $subtotal_lineas += $subtotal;

        $es_extra = (int)($l['es_extra'] ?? 0);
        if ($es_extra) $extras_lineas += $subtotal;
        DB::execute(
            "INSERT INTO cotizacion_lineas
             (cotizacion_id, orden, sku, titulo, descripcion, cantidad, precio_unit, subtotal, es_extra)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$cot_id, $orden + 1, $sku, $titulo, $desc, $cantidad, $precio, $subtotal, $es_extra]
        );
    }

    // ── Actualizar descuento en cotización ──
    DB::execute(
        "UPDATE cotizaciones SET
            subtotal            = ?,
            descuento_auto_amt  = ?,
            descuento_auto_pct  = ?,
            descuento_auto_activo = ?,
            updated_at          = NOW()
         WHERE id=?",
        [$subtotal_lineas, $desc_auto_amt, $desc_auto_pct,
         $desc_auto_amt > 0 ? 1 : 0, $cot_id]
    );

    // ── Recalcular total de venta ──
    // Traer impuesto de la cotización
    $cot = DB::row("SELECT impuesto_pct, impuesto_modo, cupon_monto FROM cotizaciones WHERE id=?", [$cot_id]);
    $cupon_amt = (float)($cot['cupon_monto'] ?? 0);
    $imp_pct   = (float)($cot['impuesto_pct'] ?? 0);
    $imp_modo  = $cot['impuesto_modo'] ?? 'ninguno';

    // ── DI 'utilizado': el TOTAL lo manda el CONTRATO CONGELADO, no se re-deriva ──
    // total = nuevo_total (frozen, SIN extras, ya CON IVA si el modo es suma)
    //       + extras actuales (raw, sin IVA extra)
    // Es la fórmula EXACTA del accept (quote_action) y de convertir. Antes esta
    // rama re-derivaba `base = subtotal_lineas − monto_desc` y le aplicaba IVA
    // encima → doble IVA sobre el descuento (monto_desc ya es post-IVA) y además
    // gravaba los extras que el contrato deja crudos: un simple "Guardar" sin
    // editar nada movía el total ($1,044 → $1,025.44 con IVA suma). Editar el
    // precio base de una venta con DII no debe romper el precio que aceptó el
    // cliente; para cambiarlo, se quita el DI de la venta (acciones.php).
    $di_nuevo_total = null;
    try {
        $v = DB::val(
            "SELECT nuevo_total FROM desc_int_activaciones
             WHERE cotizacion_id = ? AND estado = 'utilizado'
             ORDER BY id DESC LIMIT 1", [$cot_id]);
        // DB::val() (PDO fetchColumn) devuelve FALSE —no null— cuando NO hay fila.
        // `nuevo_total` es NOT NULL en el schema, así que una fila real siempre
        // trae número. Sin este `!== false`, una venta SIN DI entraba a la rama DI
        // con $di_nuevo_total=0.0 y colapsaba su total a $0 (regresión SEV-1).
        if ($v !== false && $v !== null) $di_nuevo_total = (float)$v;
    } catch (\Throwable $e) {}
    if ($di_nuevo_total !== null) {
        $cupon_amt = 0.0;
        $desc_auto_amt = 0.0;
        $nuevo_total = round($di_nuevo_total + $extras_lineas, 2);
    } else {
        $base = $subtotal_lineas - $cupon_amt - $desc_auto_amt;
        if ($imp_modo === 'suma') {
            $nuevo_total = round($base * (1 + $imp_pct / 100), 2);
        } else {
            $nuevo_total = round(max(0, $base), 2);
        }
    }
    $nuevo_saldo = max(0, round($nuevo_total - (float)$venta['pagado'], 2));

    // ── Estado automático según saldo ──
    $nuevo_estado = null;
    if ($nuevo_saldo <= 0 && (float)$venta['pagado'] > 0) {
        $nuevo_estado = 'pagada';
    } elseif ($nuevo_saldo > 0 && (float)$venta['pagado'] > 0 && $nuevo_saldo < $nuevo_total) {
        $nuevo_estado = 'parcial';
    }

    // ── Actualizar venta ──
    $update_sql = "UPDATE ventas SET total=?, saldo=?, descuento_auto_amt=?, updated_at=NOW()";
    $params = [$nuevo_total, $nuevo_saldo, $desc_auto_amt];

    if ($nuevo_estado && $venta['estado'] !== 'cancelada') {
        $update_sql .= ", estado=?";
        $params[] = $nuevo_estado;
    }

    if ($nuevo_cliente_id) {
        $cli = DB::row("SELECT id FROM clientes WHERE id=? AND empresa_id=?", [$nuevo_cliente_id, $empresa_id]);
        if ($cli) {
            $update_sql .= ", cliente_id=?";
            $params[] = $nuevo_cliente_id;
        }
    }
    $update_sql .= " WHERE id=?";
    $params[] = $venta_id;
    DB::execute($update_sql, $params);

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (defined('DEBUG') && DEBUG) throw $e;
    ob_end_clean();
    json_error('Error al guardar: ' . $e->getMessage(), 500);
}

// ── Log granular: comparar estado anterior vs nuevo ──
// (leer valores ANTES del save ya no es posible — los leemos del body vs lo guardado)
// Comparamos con los valores originales de la cotización antes del UPDATE
// Los tenemos: desc_auto_amt es el NUEVO, $cot_antes_desc era el anterior
$desc_anterior = (float)($cot_antes['descuento_auto_amt'] ?? 0);
$lineas_anterior = $lineas_count_antes;

// 1. Descuento
if ($desc_anterior == 0 && $desc_auto_amt > 0) {
    VentaLog::registrar($venta_id, $empresa_id, 'descuento_agregado',
        '$' . number_format($desc_auto_amt, 2), Auth::id());
} elseif ($desc_anterior > 0 && $desc_auto_amt == 0) {
    VentaLog::registrar($venta_id, $empresa_id, 'descuento_eliminado',
        'Era $' . number_format($desc_anterior, 2), Auth::id());
} elseif ($desc_anterior > 0 && $desc_auto_amt > 0 && $desc_anterior != $desc_auto_amt) {
    VentaLog::registrar($venta_id, $empresa_id, 'descuento_agregado',
        '$' . number_format($desc_anterior, 2) . ' → $' . number_format($desc_auto_amt, 2), Auth::id());
}

// 2. Cambio de líneas
$lineas_dif = count($lineas_new) - $lineas_anterior;
if ($lineas_dif > 0) {
    VentaLog::registrar($venta_id, $empresa_id, 'item_agregado',
        '+' . $lineas_dif . ' artículo(s) · total ' . count($lineas_new), Auth::id());
} elseif ($lineas_dif < 0) {
    VentaLog::registrar($venta_id, $empresa_id, 'item_eliminado',
        abs($lineas_dif) . ' artículo(s) eliminado(s) · quedan ' . count($lineas_new), Auth::id());
} elseif ($subtotal_lineas != $subtotal_anterior) {
    // Misma cantidad de líneas pero cambió el monto → se editó alguna
    VentaLog::registrar($venta_id, $empresa_id, 'item_editado',
        'Subtotal $' . number_format($subtotal_anterior, 2) . ' → $' . number_format($subtotal_lineas, 2), Auth::id());
}

// 3. Cliente
if ($nuevo_cliente_id) {
    $cli_nom = DB::val("SELECT nombre FROM clientes WHERE id=?", [$nuevo_cliente_id]);
    if ($cli_nom) VentaLog::registrar($venta_id, $empresa_id, 'cliente_cambiado',
        'Cliente → ' . $cli_nom, Auth::id());
}

ob_end_clean();
json_ok(['total' => $nuevo_total, 'saldo' => $nuevo_saldo, 'estado' => $nuevo_estado ?? $venta['estado']]);
