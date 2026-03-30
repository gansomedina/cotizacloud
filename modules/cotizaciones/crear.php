<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/crear.php
//  POST /cotizaciones/nueva  (JSON)
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método no permitido', 405);
}

csrf_check();

if (!Auth::es_admin() && !Auth::puede('crear_cotizaciones')) {
    json_error('Sin permiso para crear cotizaciones', 403);
}

$empresa    = Auth::empresa();
$usuario    = Auth::usuario();
$empresa_id = EMPRESA_ID;

// ─── Verificar límite plan Free ──────────────────────────
$trial = trial_info($empresa_id);
if ($trial['agotado']) {
    json_error('Has alcanzado el límite de ' . TRIAL_LIMIT . ' cotizaciones del plan Free. Activa Pro para continuar.', 402);
}

// ─── Leer JSON ───────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

// ─── Validar campos obligatorios ─────────────────────────
$titulo = trim($body['titulo'] ?? '');
if (empty($titulo)) json_error('El título es requerido');

$cliente_id = isset($body['cliente_id']) ? (int)$body['cliente_id'] : null;
if (!$cliente_id) json_error('El cliente es requerido');
$existe_cliente = DB::val(
    "SELECT id FROM clientes WHERE id = ? AND empresa_id = ?",
    [$cliente_id, $empresa_id]
);
if (!$existe_cliente) json_error('Cliente no válido');

// Vendedor asignado (default = usuario actual)
$vendedor_id = Auth::id();
if (!empty($body['vendedor_id']) && Auth::puede('asignar_cotizaciones')) {
    $vid = (int)$body['vendedor_id'];
    $existe_vendedor = DB::val(
        "SELECT id FROM usuarios WHERE id = ? AND empresa_id = ? AND activo = 1",
        [$vid, $empresa_id]
    );
    if ($existe_vendedor) $vendedor_id = $vid;
}

// Fechas
$fecha_hoy  = date('Y-m-d');
$valida_hasta = $body['valida_hasta'] ?? null;
if ($valida_hasta && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valida_hasta)) {
    $valida_hasta = null;
}

// Cupón
$cupon_id     = null;
$cupon_codigo = null;
$cupon_pct    = 0;
if (!empty($body['cupon_id']) && Auth::puede('aplicar_descuentos')) {
    $cupon = DB::row(
        "SELECT id, codigo, porcentaje FROM cupones
         WHERE id = ? AND empresa_id = ? AND activo = 1",
        [(int)$body['cupon_id'], $empresa_id]
    );
    if ($cupon) {
        $cupon_id     = (int)$cupon['id'];
        $cupon_codigo = $cupon['codigo'];
        $cupon_pct    = (float)$cupon['porcentaje'];
    }
}

// Descuento automático
$desc_auto_activo = 0;
$desc_auto_pct    = 0.0;
$desc_auto_expira = null;
$desc_auto_amt    = 0.0;
if (!empty($body['descuento_auto_activo']) && Auth::puede('aplicar_descuentos')) {
    $desc_auto_activo = 1;
    $desc_auto_pct    = max(0, min(100, (float)($body['descuento_auto_pct'] ?? 0)));
    $dias             = max(1, (int)($body['descuento_auto_dias'] ?? 3));
    $desc_auto_expira = date('Y-m-d H:i:s', strtotime("+{$dias} days"));
}

// ─── Items ───────────────────────────────────────────────
$items = $body['items'] ?? [];
if (!is_array($items)) $items = [];

// ─── Calcular totales ────────────────────────────────────
$subtotal = 0.0;
$lineas   = [];

foreach ($items as $i => $item) {
    $cant    = max(0, (float)($item['cantidad']   ?? 1));
    $precio  = max(0, (float)($item['precio_unit'] ?? 0));

    // Si no puede editar precios, verificar precio original del artículo
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
        'es_extra'    => (int)($item['es_extra'] ?? 0),
    ];
}

// Cálculo de descuentos e impuesto
$base = $subtotal;

$cupon_monto = 0.0;
if ($cupon_id) {
    $cupon_monto = $subtotal * ($cupon_pct / 100);
    $base -= $cupon_monto;
}

if ($desc_auto_activo) {
    $desc_auto_amt = $base * ($desc_auto_pct / 100);
    $base -= $desc_auto_amt;
}

$impuesto_modo  = $empresa['impuesto_modo'];
$impuesto_pct   = (float)$empresa['impuesto_pct'];
$impuesto_amt   = 0.0;

if ($impuesto_modo === 'suma') {
    $impuesto_amt = $base * ($impuesto_pct / 100);
    $total = $base + $impuesto_amt;
} elseif ($impuesto_modo === 'incluido') {
    $impuesto_amt = $base - ($base / (1 + $impuesto_pct / 100));
    $total = $base;
} else {
    $total = $base;
}

// ─── Generar folio, slug y token ─────────────────────────
try {
    DB::beginTransaction();

    $numero = DB::siguiente_folio($empresa_id, 'COT', $empresa['cot_prefijo'] ?? 'COT');
    $slug   = slug_unico($titulo, 'cotizaciones', 'slug', $empresa_id);
    $token  = generar_token(32);

    // Insertar cotización
    $cot_id = DB::insert(
        "INSERT INTO cotizaciones
         (empresa_id, cliente_id, usuario_id, vendedor_id, cupon_id,
          numero, titulo, slug, token, estado,
          subtotal,
          cupon_codigo, cupon_pct, cupon_monto,
          descuento_auto_activo, descuento_auto_pct, descuento_auto_expira, descuento_auto_amt,
          impuesto_modo, impuesto_pct, impuesto_amt,
          total, valida_hasta, notas_cliente, notas_internas)
         VALUES (?,?,?,?,?,?,?,?,?,'enviada',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            $empresa_id, $cliente_id, Auth::id(), $vendedor_id, $cupon_id,
            $numero, $titulo, $slug, $token,
            $subtotal,
            $cupon_codigo, $cupon_pct, $cupon_monto,
            $desc_auto_activo, $desc_auto_pct, $desc_auto_expira, $desc_auto_amt,
            $impuesto_modo, $impuesto_pct, $impuesto_amt,
            $total, $valida_hasta,
            substr($body['notas_cliente']   ?? '', 0, 5000),
            substr($body['notas_internas']  ?? '', 0, 5000),
        ]
    );

    // Insertar líneas
    foreach ($lineas as $linea) {
        DB::execute(
            "INSERT INTO cotizacion_lineas
             (cotizacion_id, articulo_id, orden, sku, titulo, descripcion, cantidad, precio_unit, subtotal, es_extra)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [
                $cot_id, $linea['articulo_id'], $linea['orden'],
                $linea['sku'], $linea['titulo'], $linea['descripcion'],
                $linea['cantidad'], $linea['precio_unit'], $linea['subtotal'],
                $linea['es_extra'],
            ]
        );
    }

    // Marcar enviada_at y log
    DB::execute("UPDATE cotizaciones SET enviada_at=NOW() WHERE id=?", [$cot_id]);
    DB::execute(
        "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, ip)
         VALUES (?, ?, 'enviada', ?)",
        [$cot_id, Auth::id(), ip_real()]
    );

    DB::commit();

} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    json_error('Error al crear la cotización', 500);
}

json_ok(['id' => $cot_id, 'numero' => $numero, 'slug' => $slug]);
