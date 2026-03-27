<?php
// ============================================================
//  CotizaApp — modules/ventas/eliminar_extra.php
//  POST /ventas/:id/eliminar-extra
//  Solo elimina líneas con es_extra=1
// ============================================================
defined('COTIZAAPP') or die;
ob_start();
header('Content-Type: application/json; charset=utf-8');
csrf_check();

if (!Auth::es_admin() && !Auth::puede('agregar_extras')) {
    json_error('Sin permiso', 403);
}

$empresa_id = EMPRESA_ID;
$venta_id   = (int)($id ?? 0);
if (!$venta_id) json_error('ID inválido', 400);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$linea_id = (int)($body['linea_id'] ?? 0);
if (!$linea_id) json_error('ID de línea inválido', 400);

// Cargar venta
$venta = DB::row("SELECT * FROM ventas WHERE id=? AND empresa_id=?", [$venta_id, $empresa_id]);
if (!$venta) json_error('Venta no encontrada', 404);
if ($venta['estado'] === 'cancelada') json_error('Venta cancelada', 422);

$cot_id = (int)$venta['cotizacion_id'];
if (!$cot_id) json_error('Sin cotización asociada');

// Verificar que la línea es un extra y pertenece a esta cotización
$linea = DB::row(
    "SELECT id, titulo, subtotal, es_extra FROM cotizacion_lineas WHERE id=? AND cotizacion_id=?",
    [$linea_id, $cot_id]
);
if (!$linea) json_error('Línea no encontrada', 404);
if (!(int)$linea['es_extra']) json_error('Solo se pueden eliminar extras', 422);

DB::beginTransaction();
try {
    // Eliminar la línea
    DB::execute("DELETE FROM cotizacion_lineas WHERE id=?", [$linea_id]);

    // Recalcular subtotal
    $nuevo_subtotal = (float)DB::val(
        "SELECT COALESCE(SUM(subtotal), 0) FROM cotizacion_lineas WHERE cotizacion_id=?",
        [$cot_id]
    );

    // Leer datos de impuesto y descuentos
    $cot = DB::row("SELECT impuesto_pct, impuesto_modo, cupon_monto, descuento_auto_amt FROM cotizaciones WHERE id=?", [$cot_id]);
    $cupon_amt     = (float)($cot['cupon_monto'] ?? 0);
    $desc_auto_amt = (float)($cot['descuento_auto_amt'] ?? 0);
    $imp_pct       = (float)($cot['impuesto_pct'] ?? 0);
    $imp_modo      = $cot['impuesto_modo'] ?? 'ninguno';

    $base = $nuevo_subtotal - $cupon_amt - $desc_auto_amt;
    if ($imp_modo === 'suma') {
        $nuevo_total = round($base * (1 + $imp_pct / 100), 2);
    } else {
        $nuevo_total = round(max(0, $base), 2);
    }

    // Actualizar cotización
    DB::execute("UPDATE cotizaciones SET subtotal=?, total=?, updated_at=NOW() WHERE id=?",
        [$nuevo_subtotal, $nuevo_total, $cot_id]);

    // Actualizar venta
    $nuevo_saldo = max(0, round($nuevo_total - (float)$venta['pagado'], 2));
    $nuevo_estado = $venta['estado'];
    if ($nuevo_saldo <= 0 && (float)$venta['pagado'] > 0) {
        $nuevo_estado = 'pagada';
    } elseif ($nuevo_saldo > 0 && (float)$venta['pagado'] > 0 && $nuevo_saldo < $nuevo_total) {
        $nuevo_estado = 'parcial';
    }

    DB::execute("UPDATE ventas SET total=?, saldo=?, estado=?, updated_at=NOW() WHERE id=?",
        [$nuevo_total, $nuevo_saldo, $nuevo_estado, $venta_id]);

    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    ob_end_clean();
    json_error('Error: ' . $e->getMessage(), 500);
}

VentaLog::registrar($venta_id, $empresa_id, 'item_eliminado',
    'Extra eliminado: ' . $linea['titulo'] . ' · $' . number_format((float)$linea['subtotal'], 2), Auth::id());

ob_end_clean();
json_ok(['total' => $nuevo_total, 'saldo' => $nuevo_saldo, 'estado' => $nuevo_estado]);
