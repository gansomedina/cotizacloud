<?php
// ============================================================
//  CotizaCloud — api/mp_return.php
//  GET /api/mp/return
//  Return URL después del Checkout Pro.
//  Procesa el payment_id devuelto por MP, guarda el token de
//  la tarjeta y activa la suscripción.
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = (int)($_GET['empresa_id'] ?? 0);
$payment_id = $_GET['payment_id'] ?? $_GET['collection_id'] ?? '';
$status_qs  = $_GET['status'] ?? $_GET['collection_status'] ?? '';

if ($empresa_id <= 0) {
    $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'Faltan parámetros para confirmar la suscripción.'];
    header('Location: ' . BASE_URL . '/config?tab=suscripcion');
    exit;
}

$intento = $_SESSION['mp_intento'] ?? null;
$plan  = $intento['plan']  ?? '';
$ciclo = $intento['ciclo'] ?? '';

if (!$payment_id) {
    $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'No recibimos confirmación del pago. Si ya pagaste, refresca en unos minutos.'];
    header('Location: ' . BASE_URL . '/config?tab=suscripcion');
    exit;
}

$pago = MercadoPago::obtenerPago((string)$payment_id);
if (isset($pago['error'])) {
    error_log('[MP Return] Error obteniendo pago ' . $payment_id . ': ' . json_encode($pago));
    $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'No pudimos validar el pago. Si ya pagaste, contacta a soporte.'];
    header('Location: ' . BASE_URL . '/config?tab=suscripcion');
    exit;
}

$datos = MercadoPago::extraerDatosPago($pago);
$status = $datos['status'];

if ((!$plan || !$ciclo) && $datos['external_reference']) {
    if (preg_match('/^cz_\d+_(pro|business)_(mensual|anual)/', $datos['external_reference'], $m)) {
        $plan  = $m[1];
        $ciclo = $m[2];
    }
}

if ($status !== 'approved') {
    $msg = match($status) {
        'pending'    => 'Tu pago está pendiente de aprobación. Te notificaremos cuando se confirme.',
        'in_process' => 'Tu pago está siendo revisado. Puede tardar unos minutos.',
        'rejected'   => 'El pago fue rechazado. ' . (($datos['status_detail'] ?? '') === 'cc_rejected_call_for_authorize'
                        ? 'Llama a tu banco para autorizar el cargo e intenta de nuevo.'
                        : 'Intenta con otro medio de pago.'),
        default      => 'El pago no se completó. Intenta de nuevo.',
    };
    $tipo = $status === 'rejected' ? 'error' : 'info';
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
    header('Location: ' . BASE_URL . '/config?tab=suscripcion');
    exit;
}

// ── Pago aprobado ──────────────────────────────────────────────
if (!$plan || !$ciclo) {
    error_log('[MP Return] Pago aprobado sin plan/ciclo identificable: ' . json_encode($datos));
    $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'Pago recibido pero no pudimos identificar el plan. Contacta a soporte.'];
    header('Location: ' . BASE_URL . '/config?tab=suscripcion');
    exit;
}

$monto = $datos['transaction_amount'];
$dias  = $ciclo === 'anual' ? 365 : 30;

DB::beginTransaction();
try {
    $ya_registrado = DB::row("SELECT id FROM pagos_suscripcion WHERE mp_payment_id=?", [$datos['payment_id']]);
    $sub = DB::row("SELECT id FROM suscripciones WHERE empresa_id=?", [$empresa_id]);

    $vence_actual = DB::val("SELECT plan_vence FROM empresas WHERE id=?", [$empresa_id]);
    $base  = ($vence_actual && $vence_actual >= date('Y-m-d')) ? $vence_actual : date('Y-m-d');
    $vence = date('Y-m-d', strtotime($base . " +{$dias} days"));
    $prox_cobro = $vence;

    if ($sub) {
        DB::execute(
            "UPDATE suscripciones SET
                plan=?, ciclo=?, estado='active',
                mp_customer_id=?, mp_card_id=?, mp_last_payment_id=?,
                card_last4=?, card_brand=?, card_exp_month=?, card_exp_year=?,
                monto_mxn=?, proximo_cobro=?, intentos_cobro=0, ultimo_error=NULL,
                cancel_al_vencer=0, cancelled_at=NULL, updated_at=NOW()
             WHERE empresa_id=?",
            [
                $plan, $ciclo,
                $datos['customer_id'], $datos['card_id'], $datos['payment_id'],
                $datos['card_last4'], $datos['card_brand'], $datos['card_exp_month'], $datos['card_exp_year'],
                $monto, $prox_cobro,
                $empresa_id,
            ]
        );
        $sub_id = (int)$sub['id'];
    } else {
        $sub_id = DB::insert(
            "INSERT INTO suscripciones
                (empresa_id, plan, ciclo, estado, mp_customer_id, mp_card_id, mp_last_payment_id,
                 card_last4, card_brand, card_exp_month, card_exp_year, monto_mxn, proximo_cobro)
             VALUES (?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $empresa_id, $plan, $ciclo,
                $datos['customer_id'], $datos['card_id'], $datos['payment_id'],
                $datos['card_last4'], $datos['card_brand'], $datos['card_exp_month'], $datos['card_exp_year'],
                $monto, $prox_cobro,
            ]
        );
    }

    if (!$ya_registrado) {
        DB::insert(
            "INSERT INTO pagos_suscripcion
                (suscripcion_id, empresa_id, mp_payment_id, monto_mxn, estado, fecha_pago, detalle)
             VALUES (?, ?, ?, ?, 'approved', ?, ?)",
            [
                $sub_id, $empresa_id, $datos['payment_id'], $monto,
                $datos['date_approved'] ?: date('Y-m-d H:i:s'),
                json_encode(['status_detail' => $datos['status_detail'], 'first_payment' => !$sub]),
            ]
        );
    }

    DB::execute(
        "UPDATE empresas SET plan=?, plan_vence=?, grace_hasta=NULL, activa=1, ultima_sync_mp=NOW() WHERE id=?",
        [$plan, $vence, $empresa_id]
    );

    DB::commit();

    unset($_SESSION['mp_intento']);

    $guardada = $datos['card_last4']
        ? ' Tu tarjeta ****' . $datos['card_last4'] . ' se guardó para renovar automáticamente.'
        : '';
    $_SESSION['flash'] = [
        'tipo' => 'ok',
        'msg'  => '¡Pago recibido! Tu plan ' . ucfirst($plan) . ' está activo hasta el ' . date('d/m/Y', strtotime($vence)) . '.' . $guardada,
    ];
} catch (Throwable $e) {
    DB::rollBack();
    error_log('[MP Return] Error guardando pago: ' . $e->getMessage() . ' — payment_id=' . $datos['payment_id']);
    $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'Pago recibido. Hubo un detalle al activarlo, contacta a soporte.'];
}

header('Location: ' . BASE_URL . '/config?tab=suscripcion');
exit;
