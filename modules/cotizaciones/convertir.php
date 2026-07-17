<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/convertir.php
//  POST /cotizaciones/:id/convertir
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

$cot = DB::row(
    "SELECT c.*, cl.nombre AS cliente_nombre FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.id = ? AND c.empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('No encontrada', 404);

// ── Permisos (auditoría 17-jul): convertir a venta es tan sensible como editar
//    la cotización. Antes solo pedía csrf_check → cualquier asesor logueado
//    convertía CUALQUIER cotización (incl. borrador) a venta. ──
if (!Auth::es_admin() && !Auth::puede('editar_cotizaciones')) {
    json_error('Sin permiso para convertir a venta', 403);
}
if (!Auth::puede('ver_todas_cots')
    && (int)$cot['usuario_id'] !== (int)Auth::id()
    && (int)($cot['vendedor_id'] ?? 0) !== (int)Auth::id()) {
    json_error('Sin acceso a esta cotización', 403);
}

// ── Nunca crear una SEGUNDA venta (auditoría 17-jul): si ya existe una
//    (aceptada por el cliente en el slug → el accept ya creó su venta; ya
//    convertida; o doble-submit), devolver ESA. Antes solo se checaba en estado
//    'convertida' → convertir una 'aceptada' creaba venta duplicada = ingreso
//    doble. Una venta CANCELADA bloquea la reconversión automática (evita
//    re-crear al precio ya descontado del DI — el cot sigue 'aceptada'). ──
$venta_prev = DB::row("SELECT id, estado FROM ventas WHERE cotizacion_id = ? ORDER BY id DESC LIMIT 1", [$cot_id]);
if ($venta_prev) {
    if ($venta_prev['estado'] !== 'cancelada') json_ok(['venta_id' => (int)$venta_prev['id']]);
    json_error('Esta cotización ya tuvo una venta cancelada; no se reconvierte automáticamente.', 422);
}

if (!in_array($cot['estado'], ['aceptada','enviada','vista','borrador'])) {
    json_error('No se puede convertir en estado: ' . $cot['estado'], 422);
}

$empresa = Auth::empresa();

try {
    DB::beginTransaction();

    // ── Lock + re-check bajo el lock: dos POST concurrentes sobre una 'enviada'
    //    creaban dos ventas (solo la 1ª consumía el DI). El FOR UPDATE serializa
    //    y el re-check devuelve la venta si otra request la creó primero. ──
    DB::row("SELECT id FROM cotizaciones WHERE id = ? FOR UPDATE", [$cot_id]);
    $dup = DB::val("SELECT id FROM ventas WHERE cotizacion_id = ? AND estado <> 'cancelada' LIMIT 1", [$cot_id]);
    if ($dup) { DB::rollback(); json_ok(['venta_id' => (int)$dup]); }

    // ── Descuento Inteligente VIGENTE: aplica igual que el accept (decisión
    //    CEO 16-jul). Total = nuevo_total congelado del contrato (sin extras,
    //    con IVA si el modo es suma) + extras actuales; el contrato pasa a
    //    'utilizado'. Un DI vencido/inexistente NO descuenta → precio completo.
    //    Antes convertir era CIEGO al DI: cobraba precio completo y dejaba el
    //    contrato 'activo' colgado (la caja del editor prometía un cobro falso). ──
    $total_vta = (float)$cot['total'];
    try {
        $di_vig = DescuentoInteligente::vigente($cot_id);
        if ($di_vig && $di_vig['estado'] === 'activo') {
            $extras_di = (float)DB::val(
                "SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas
                 WHERE cotizacion_id = ? AND es_extra = 1", [$cot_id]);
            $total_vta = round((float)$di_vig['nuevo_total'] + $extras_di, 2);
            // WHERE estado='activo' evita doble-uso y carrera con un accept simultáneo
            DB::execute("UPDATE desc_int_activaciones SET estado='utilizado' WHERE id=? AND estado='activo'",
                [(int)$di_vig['id']]);
            // La cotización refleja el total cobrado (igual que hace el accept)
            DB::execute("UPDATE cotizaciones SET total=? WHERE id=?", [$total_vta, $cot_id]);
        }
    } catch (\Throwable $e) {} // tabla DI sin migrar → sin descuento

    // Folio de venta
    $numero_vta = DB::siguiente_folio($empresa_id, 'VTA', $empresa['vta_prefijo'] ?? 'VTA');
    $slug_vta   = slug_unico($cot['titulo'], 'ventas', 'slug', $empresa_id);
    $token_vta  = generar_token(32);

    // Crear venta
    $venta_vendedor = (int)($cot['vendedor_id'] ?? $cot['usuario_id'] ?? Auth::id());
    $venta_id = DB::insert(
        "INSERT INTO ventas
         (empresa_id, cotizacion_id, cliente_id, usuario_id, vendedor_id,
          numero, titulo, slug, token,
          total, pagado, saldo, estado)
         VALUES (?,?,?,?,?,?,?,?,?,?,0,?,?)",
        [
            $empresa_id,
            $cot_id,
            $cot['cliente_id'],
            Auth::id(),
            $venta_vendedor,
            $numero_vta,
            $cot['titulo'],
            $slug_vta,
            $token_vta,
            $total_vta,
            $total_vta,   // saldo inicial = total (con DI aplicado si estaba vigente)
            'pendiente',
        ]
    );

    // Actualizar estado cotización
    DB::execute(
        "UPDATE cotizaciones SET estado='convertida', updated_at=NOW() WHERE id=?",
        [$cot_id]
    );

    // Log cotización
    DB::execute(
        "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, detalle, ip)
         VALUES (?,?,'convertida',?,?)",
        [$cot_id, Auth::id(), 'Venta: ' . $numero_vta, ip_real()]
    );

    // Congelar el original antes de que la venta pueda modificar las líneas.
    snapshot_cotizacion($cot_id);

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    json_error('Error al convertir', 500);
}

json_ok(['venta_id' => $venta_id, 'numero' => $numero_vta]);
