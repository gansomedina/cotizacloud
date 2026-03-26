<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/guardar.php
//  POST /cotizaciones/:id
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Método no permitido', 405);

csrf_check();

if (!Auth::es_admin() && !Auth::puede('editar_cotizaciones')) {
    json_error('Sin permiso para editar cotizaciones', 403);
}

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

// Verificar que existe y pertenece a la empresa
$cot = DB::row(
    "SELECT * FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('Cotización no encontrada', 404);

// Verificar acceso
if (!Auth::puede('ver_todas_cots') && (int)$cot['usuario_id'] !== (int)Auth::id() && (int)($cot['vendedor_id'] ?? 0) !== (int)Auth::id()) {
    json_error('Sin acceso', 403);
}

// Solo editable si está en estado editable
$editables = ['borrador', 'enviada', 'vista'];
if (!in_array($cot['estado'], $editables)) {
    json_error('No se puede editar en estado: ' . $cot['estado'], 422);
}

$empresa = Auth::empresa();

// ─── Validar campos ──────────────────────────────────────
$titulo = trim($body['titulo'] ?? '');
if (empty($titulo)) json_error('El título es requerido');

$cliente_id = isset($body['cliente_id']) ? (int)$body['cliente_id'] : null;
if (!$cliente_id) json_error('El cliente es requerido');
$ok = DB::val("SELECT id FROM clientes WHERE id = ? AND empresa_id = ?", [$cliente_id, $empresa_id]);
if (!$ok) json_error('Cliente no válido');

// Vendedor asignado (solo si tiene permiso)
$vendedor_id = (int)($cot['vendedor_id'] ?? $cot['usuario_id']);
if (isset($body['vendedor_id']) && Auth::puede('asignar_cotizaciones')) {
    $vid = (int)$body['vendedor_id'];
    if ($vid > 0) {
        $existe_vendedor = DB::val(
            "SELECT id FROM usuarios WHERE id = ? AND empresa_id = ? AND activo = 1",
            [$vid, $empresa_id]
        );
        if ($existe_vendedor) $vendedor_id = $vid;
    }
}

$valida_hasta = trim($body['valida_hasta'] ?? '');
// Validar formato, rango razonable y que no sea fecha cero
if ($valida_hasta && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valida_hasta) && $valida_hasta > '2000-01-01') {
    // válido
} else {
    $valida_hasta = null;
}

// Cupón
$cupon_id = null; $cupon_codigo = null; $cupon_pct = 0;
if (!empty($body['cupon_id']) && Auth::puede('aplicar_descuentos')) {
    $cupon = DB::row(
        "SELECT id, codigo, porcentaje FROM cupones WHERE id = ? AND empresa_id = ? AND activo = 1",
        [(int)$body['cupon_id'], $empresa_id]
    );
    if ($cupon) {
        $cupon_id     = (int)$cupon['id'];
        $cupon_codigo = $cupon['codigo'];
        $cupon_pct    = (float)$cupon['porcentaje'];
    }
}

// Descuento auto
$desc_auto_activo = 0; $desc_auto_pct = 0.0; $desc_auto_expira = null; $desc_auto_amt = 0.0;
if (!empty($body['descuento_auto_activo']) && Auth::puede('aplicar_descuentos')) {
    $desc_auto_activo = 1;
    $desc_auto_pct    = max(0, min(100, (float)($body['descuento_auto_pct'] ?? 0)));
    $dias = max(1, (int)($body['descuento_auto_dias'] ?? 3));
    $desc_auto_expira = date('Y-m-d H:i:s', strtotime("+{$dias} days"));
}

// ─── Recalcular totales ──────────────────────────────────
$items    = $body['items'] ?? [];
$subtotal = 0.0;
$lineas   = [];

foreach ($items as $i => $item) {
    $cant  = max(0, (float)($item['cantidad']   ?? 1));
    $precio = max(0, (float)($item['precio_unit'] ?? 0));

    if (!Auth::puede('editar_precios') && !empty($item['articulo_id'])) {
        $art_precio = DB::val(
            "SELECT precio FROM articulos WHERE id = ? AND empresa_id = ?",
            [(int)$item['articulo_id'], $empresa_id]
        );
        if ($art_precio !== null) $precio = (float)$art_precio;
    }

    $sub_linea = $cant * $precio;
    $subtotal += $sub_linea;

    $lineas[] = [
        'orden'       => $i + 1,
        'articulo_id' => !empty($item['articulo_id']) ? (int)$item['articulo_id'] : null,
        'sku'         => substr(trim($item['sku'] ?? ''), 0, 60),
        'titulo'      => substr(trim($item['titulo'] ?? 'Sin nombre'), 0, 255),
        'descripcion' => $item['descripcion'] ?? '',
        'cantidad'    => $cant,
        'precio_unit' => $precio,
        'subtotal'    => $sub_linea,
    ];
}

if (empty($lineas)) json_error('Se requiere al menos un artículo');
$tiene_precio = false;
foreach ($lineas as $l) { if ($l['precio_unit'] > 0) { $tiene_precio = true; break; } }
if (!$tiene_precio) json_error('Al menos un artículo debe tener precio');

$base = $subtotal;
$cupon_monto = 0.0;
if ($cupon_id) { $cupon_monto = $subtotal * ($cupon_pct / 100); $base -= $cupon_monto; }
if ($desc_auto_activo) { $desc_auto_amt = $base * ($desc_auto_pct / 100); $base -= $desc_auto_amt; }
$base = max(0, $base); // Nunca permitir total negativo

$impuesto_modo = $empresa['impuesto_modo'];
$impuesto_pct  = (float)$empresa['impuesto_pct'];
$impuesto_amt  = 0.0;
if ($impuesto_modo === 'suma')     { $impuesto_amt = $base * ($impuesto_pct / 100); $total = $base + $impuesto_amt; }
elseif ($impuesto_modo === 'incluido') { $impuesto_amt = $base - ($base / (1 + $impuesto_pct / 100)); $total = $base; }
else { $total = $base; }

// ─── Actualizar en DB ────────────────────────────────────
try {
    DB::beginTransaction();

    DB::execute(
        "UPDATE cotizaciones SET
            titulo=?, cliente_id=?, vendedor_id=?, cupon_id=?,
            subtotal=?, cupon_codigo=?, cupon_pct=?, cupon_monto=?,
            descuento_auto_activo=?, descuento_auto_pct=?, descuento_auto_expira=?, descuento_auto_amt=?,
            impuesto_modo=?, impuesto_pct=?, impuesto_amt=?,
            total=?, valida_hasta=?, notas_cliente=?, notas_internas=?,
            updated_at=NOW()
         WHERE id=?",
        [
            $titulo, $cliente_id, $vendedor_id, $cupon_id,
            $subtotal, $cupon_codigo, $cupon_pct, $cupon_monto,
            $desc_auto_activo, $desc_auto_pct, $desc_auto_expira, $desc_auto_amt,
            $impuesto_modo, $impuesto_pct, $impuesto_amt,
            $total, $valida_hasta,
            substr($body['notas_cliente']  ?? '', 0, 5000),
            substr($body['notas_internas'] ?? '', 0, 5000),
            $cot_id,
        ]
    );

    // Reemplazar líneas
    DB::execute("DELETE FROM cotizacion_lineas WHERE cotizacion_id = ?", [$cot_id]);
    foreach ($lineas as $linea) {
        DB::execute(
            "INSERT INTO cotizacion_lineas
             (cotizacion_id, articulo_id, orden, sku, titulo, descripcion, cantidad, precio_unit, subtotal)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$cot_id, $linea['articulo_id'], $linea['orden'], $linea['sku'], $linea['titulo'],
             $linea['descripcion'], $linea['cantidad'], $linea['precio_unit'], $linea['subtotal']]
        );
    }

    // Log
    DB::execute(
        "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, ip) VALUES (?,?,'editada',?)",
        [$cot_id, Auth::id(), ip_real()]
    );

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    json_error('Error al guardar', 500);
}

json_ok(['id' => $cot_id]);
