<?php
// ============================================================
//  CotizaCloud — modules/config/suscripcion_cancelar.php
//  POST /config/suscripcion/cancelar
//  Cancela la renovación automática. El plan sigue activo hasta
//  el fin del ciclo ya pagado. El cron no la cobrará de nuevo.
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();
csrf_check();

$sub = DB::row("SELECT * FROM suscripciones WHERE empresa_id=? AND estado='active'", [EMPRESA_ID]);

if (!$sub) {
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'No hay suscripción activa.'];
    redirect('/config?tab=suscripcion');
}

// Legacy: si tiene preapproval viejo, también lo cancelamos en MP
if (!empty($sub['mp_preapproval_id'])) {
    $result = MercadoPago::cancelarPreapproval($sub['mp_preapproval_id']);
    if (isset($result['error'])) {
        error_log('[MP Cancelar Legacy] ' . json_encode($result));
    }
}

DB::execute(
    "UPDATE suscripciones SET cancel_al_vencer=1, cancelled_at=NOW(), updated_at=NOW() WHERE id=?",
    [$sub['id']]
);

$_SESSION['flash'] = ['tipo' => 'ok', 'msg' => 'Renovación automática cancelada. Tu plan seguirá activo hasta el fin del ciclo actual.'];
redirect('/config?tab=suscripcion');
