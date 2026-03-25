<?php
// ============================================================
//  CotizaApp — modules/ventas/abono.php
//  POST /ventas/:id/abono
// ============================================================

defined('COTIZAAPP') or die;
ob_start(); // Captura output accidental (warnings, notices) antes del JSON

header('Content-Type: application/json; charset=utf-8');

csrf_check();

if (!Auth::es_admin() && !Auth::puede('capturar_pagos')) json_error('Sin permiso', 403);

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();
$venta_id   = (int)($id ?? 0);
if (!$venta_id) json_error('ID inválido', 400);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

// ─── Cargar venta ────────────────────────────────────────
$venta = DB::row(
    "SELECT * FROM ventas WHERE id = ? AND empresa_id = ?",
    [$venta_id, $empresa_id]
);
if (!$venta) json_error('Venta no encontrada', 404);
if (in_array($venta['estado'], ['cancelada', 'entregada'])) {
    json_error('No se puede abonar a una venta ' . $venta['estado'], 422);
}

// ─── Validar ─────────────────────────────────────────────
$formas_validas = ['efectivo', 'transferencia', 'tarjeta'];
$forma_pago = $body['forma_pago'] ?? 'efectivo';
if (!in_array($forma_pago, $formas_validas)) json_error('Forma de pago inválida');

$monto = (float)($body['monto'] ?? 0);
if ($monto <= 0) json_error('El monto debe ser mayor a 0');

$concepto   = substr(trim($body['concepto']   ?? ''), 0, 255);
$referencia = substr(trim($body['referencia'] ?? ''), 0, 255);

// ─── Generar folio recibo ────────────────────────────────
try {
    DB::beginTransaction();

    $cnt_rec    = (int)DB::val("SELECT COUNT(*) FROM recibos WHERE empresa_id=?", [$empresa_id]);
    $numero_rec = 'REC-' . date('Y') . '-' . str_pad($cnt_rec + 1, 4, '0', STR_PAD_LEFT);
    $token_rec  = generar_token(32);

    // Calcular nuevo pagado y saldo
    $nuevo_pagado = round((float)$venta['pagado'] + $monto, 2);
    $nuevo_saldo  = round((float)$venta['total']  - $nuevo_pagado, 2);

    // Estado automático por saldo
    $nuevo_estado = $venta['estado'];
    if ($nuevo_saldo <= 0) {
        $nuevo_estado   = 'pagada';
        $nuevo_saldo    = 0;
        // pagado conserva el monto real (puede ser > total si hubo sobrepago)
    } elseif ($nuevo_pagado > 0) {
        $nuevo_estado = 'parcial';
    }

    // Crear recibo (columnas reales del schema)
    $notas_rec = trim(($forma_pago !== 'efectivo' ? ucfirst($forma_pago) . ($referencia ? ' · '.$referencia : '') : ($referencia ?: '')));
    $recibo_id = DB::insert(
        "INSERT INTO recibos
         (empresa_id, venta_id, numero, token,
          monto, concepto, notas, fecha, created_at,
          forma_pago, usuario_id, pagado_antes, saldo_despues)
         VALUES (?,?,?,?,?,?,?,CURDATE(),NOW(),?,?,?,?)",
        [
            $empresa_id, $venta_id,
            $numero_rec, $token_rec,
            $monto, $concepto ?: ucfirst($forma_pago),
            $notas_rec ?: null,
            $forma_pago, Auth::id(),
            (float)$venta['pagado'], $nuevo_saldo,
        ]
    );

    // Actualizar venta
    DB::execute(
        "UPDATE ventas SET pagado=?, saldo=?, estado=?, updated_at=NOW() WHERE id=?",
        [$nuevo_pagado, $nuevo_saldo, $nuevo_estado, $venta_id]
    );

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    ob_end_clean();
    json_error('Error al registrar abono', 500);
}

VentaLog::registrar(
    $venta_id, $empresa_id,
    'abono_registrado',
    '$' . number_format($monto, 2) . ' · ' . ($concepto ?: ucfirst($forma_pago)) . ($notas_rec ? ' · ' . $notas_rec : ''),
    Auth::id()
);

// ─── Notificaciones de abono ─────────────────────────────────
$ncfg_abono = notif_config($empresa_id);
if ($ncfg_abono['abono_registrado']) {

// Push
try {
    $venta_num = $venta['numero'] ?? 'VTA-' . $venta_id;
    $push_titulo = 'Abono registrado: $' . number_format($monto, 2);
    $push_cuerpo = $venta_num . ($concepto ? ' — ' . $concepto : '');
    PushNotification::enviar_a_empresa(
        $empresa_id,
        'abono_registrado',
        $push_titulo,
        $push_cuerpo,
        ['venta_id' => $venta_id, 'url' => '/ventas/' . $venta_id]
    );
} catch (\Exception $e) {
    if (defined('DEBUG') && DEBUG) error_log('Push abono error: ' . $e->getMessage());
}

// Email
try {
    $notif_email = $empresa['notif_email'] ?? '';
    if ($notif_email) {
        $cliente_info = DB::row(
            "SELECT cl.nombre FROM clientes cl
             JOIN ventas v ON v.cliente_id = cl.id
             WHERE v.id = ? AND cl.empresa_id = ?",
            [$venta_id, $empresa_id]
        );
        $moneda = $empresa['moneda'] ?? 'MXN';
        $emp_slug = $empresa['slug'] ?? '';
        $url_recibo = 'https://' . $emp_slug . '.' . BASE_DOMAIN . '/r/' . $token_rec;
        Mailer::enviar_abono(
            $notif_email,
            $cliente_info['nombre'] ?? 'Cliente',
            $empresa['nombre'] ?? 'CotizaCloud',
            $numero_rec,
            $monto,
            $moneda,
            $nuevo_saldo,
            $forma_pago,
            $url_recibo,
            $concepto
        );
    }
} catch (\Exception $e) {
    if (defined('DEBUG') && DEBUG) error_log('Email abono error: ' . $e->getMessage());
}

} // cierre if notif abono_registrado

ob_end_clean();
json_ok([
    'recibo_id' => $recibo_id,
    'numero'    => $numero_rec,
    'pagado'    => $nuevo_pagado,
    'saldo'     => $nuevo_saldo,
    'estado'    => $nuevo_estado,
]);
