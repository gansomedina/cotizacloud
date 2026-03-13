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

if (!Auth::es_admin()) json_error('Solo administradores', 403);

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
    foreach ($lineas_new as $orden => $l) {
        $titulo   = substr(trim($l['titulo'] ?? ''), 0, 255);
        $sku      = substr(trim($l['sku'] ?? ''), 0, 100);
        $desc     = substr(trim($l['descripcion'] ?? ''), 0, 1000);
        $cantidad = max(0.001, (float)($l['cantidad'] ?? 1));
        $precio   = max(0, (float)($l['precio_unit'] ?? 0));
        $subtotal = round($cantidad * $precio, 2);
        $subtotal_lineas += $subtotal;

        DB::execute(
            "INSERT INTO cotizacion_lineas
             (cotizacion_id, orden, sku, titulo, descripcion, cantidad, precio_unit, subtotal)
             VALUES (?,?,?,?,?,?,?,?)",
            [$cot_id, $orden + 1, $sku, $titulo, $desc, $cantidad, $precio, $subtotal]
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

    $base = $subtotal_lineas - $cupon_amt - $desc_auto_amt;
    if ($imp_modo === 'suma') {
        $nuevo_total = round($base * (1 + $imp_pct / 100), 2);
    } else {
        $nuevo_total = round(max(0, $base), 2);
    }
    $nuevo_saldo = round($nuevo_total - (float)$venta['pagado'], 2);

    // ── Actualizar venta ──
    $update_sql = "UPDATE ventas SET total=?, saldo=?, updated_at=NOW()";
    $params = [$nuevo_total, $nuevo_saldo];

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
json_ok(['total' => $nuevo_total, 'saldo' => $nuevo_saldo]);
