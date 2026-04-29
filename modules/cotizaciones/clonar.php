<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/clonar.php
//  POST /cotizaciones/:id/clonar — Clonar cotización existente
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
$empresa_id = EMPRESA_ID;

// Verificar límite plan Free
$trial = trial_info($empresa_id);
if ($trial['agotado']) {
    json_error('Has alcanzado el límite de ' . TRIAL_LIMIT . ' cotizaciones del plan Free.', 402);
}

$cot_id = (int)($params['id'] ?? 0);
if (!$cot_id) {
    json_error('ID de cotización requerido', 400);
}

// Obtener cotización original
$cot = DB::row(
    "SELECT * FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, $empresa_id]
);

if (!$cot) {
    json_error('Cotización no encontrada', 404);
}

// Obtener líneas originales
$lineas = DB::query(
    "SELECT articulo_id, orden, sku, titulo, descripcion, cantidad, precio_unit, subtotal, es_extra
     FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden",
    [$cot_id]
);

// Generar nuevo folio, slug y token
try {
    DB::beginTransaction();

    $numero = DB::siguiente_folio($empresa_id, 'COT', $empresa['cot_prefijo'] ?? 'COT');
    $slug   = slug_unico($cot['titulo'], 'cotizaciones', 'slug', $empresa_id);
    $token  = generar_token(32);

    // Insertar cotización clonada — hereda created_at, valida_hasta y descuentos
    // auto de la original (mismo cliente, mismo trato). enviada_at = NOW para
    // disparar el flujo del Radar como nueva cot.
    $new_id = DB::insert(
        "INSERT INTO cotizaciones
         (empresa_id, cliente_id, usuario_id, vendedor_id,
          numero, titulo, slug, token, estado,
          subtotal, impuesto_modo, impuesto_pct, impuesto_amt,
          total, valida_hasta, notas_cliente, notas_internas,
          descuento_auto_activo, descuento_auto_pct, descuento_auto_dias,
          descuento_auto_expira, descuento_auto_amt,
          enviada_at, created_at)
         VALUES (?,?,?,?,?,?,?,?,'enviada',?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?)",
        [
            $empresa_id,
            $cot['cliente_id'],
            Auth::id(),
            Auth::id(),
            $numero,
            $cot['titulo'],
            $slug,
            $token,
            $cot['subtotal'],
            $cot['impuesto_modo'],
            $cot['impuesto_pct'],
            $cot['impuesto_amt'],
            $cot['total'],
            $cot['valida_hasta'],
            $cot['notas_cliente'],
            $cot['notas_internas'],
            $cot['descuento_auto_activo'] ?? 0,
            $cot['descuento_auto_pct'] ?? 0,
            $cot['descuento_auto_dias'] ?? 0,
            $cot['descuento_auto_expira'],
            $cot['descuento_auto_amt'] ?? 0,
            $cot['created_at'],
        ]
    );

    // Copiar líneas
    foreach ($lineas as $linea) {
        DB::execute(
            "INSERT INTO cotizacion_lineas
             (cotizacion_id, articulo_id, orden, sku, titulo, descripcion, cantidad, precio_unit, subtotal, es_extra)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [
                $new_id,
                $linea['articulo_id'],
                $linea['orden'],
                $linea['sku'],
                $linea['titulo'],
                $linea['descripcion'],
                $linea['cantidad'],
                $linea['precio_unit'],
                $linea['subtotal'],
                $linea['es_extra'],
            ]
        );
    }

    // Log
    DB::execute(
        "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, detalle, ip)
         VALUES (?, ?, 'clonada', ?, ?)",
        [$new_id, Auth::id(), 'Clonada de ' . $cot['numero'], ip_real()]
    );

    DB::commit();

} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    json_error('Error al clonar la cotización', 500);
}

json_ok(['id' => $new_id, 'numero' => $numero, 'slug' => $slug]);
