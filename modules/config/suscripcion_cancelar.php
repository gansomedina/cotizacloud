<?php
// ============================================================
//  CotizaCloud — modules/config/suscripcion_cancelar.php
//  POST /config/suscripcion/cancelar
//  Cancela la suscripción en MercadoPago (activa hasta fin de ciclo)
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();
csrf_check();

$sub = DB::row("SELECT * FROM suscripciones WHERE empresa_id=? AND estado='active'", [EMPRESA_ID]);

if (!$sub) {
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'No hay suscripción activa.'];
    redirect('/config?tab=suscripcion');
}

if ($sub['mp_preapproval_id']) {
    $result = MercadoPago::cancelarPreapproval($sub['mp_preapproval_id']);
    if (isset($result['error'])) {
        error_log('[MP Cancelar] Error: ' . json_encode($result));
    }
}

DB::execute(
    "UPDATE suscripciones SET cancel_al_vencer=1, cancelled_at=NOW(), updated_at=NOW() WHERE id=?",
    [$sub['id']]
);

$_SESSION['flash'] = ['tipo' => 'ok', 'msg' => 'Suscripción cancelada. Tu plan seguirá activo hasta el fin del ciclo actual.'];
redirect('/config?tab=suscripcion');
