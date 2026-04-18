<?php
// ============================================================
//  CotizaCloud — api/mp_return.php
//  GET /api/mp/return
//  Return URL después del checkout de MercadoPago
//  Sincroniza con MP y activa la suscripción si corresponde
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = (int)($_GET['empresa_id'] ?? 0);
$status     = $_GET['status'] ?? '';

// Sincronizar con MP para obtener estado real (no confiamos en query params)
if ($empresa_id > 0) {
    $sync = MercadoPago::sincronizar($empresa_id);
    $real_status = $sync['status'] ?? '';

    if ($real_status === 'authorized') {
        $_SESSION['flash'] = ['tipo' => 'ok', 'msg' => '¡Suscripción activada! Tu plan ya está disponible.'];
    } elseif ($real_status === 'pending' || $status === 'pending') {
        $_SESSION['flash'] = ['tipo' => 'info', 'msg' => 'Tu pago está pendiente de aprobación. Te notificaremos cuando se confirme.'];
    } elseif ($real_status === 'cancelled') {
        $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'La suscripción fue cancelada.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'No se pudo confirmar el estado del pago. Si hiciste el pago, espera unos minutos y refresca esta página.'];
    }
} else {
    $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'Faltan parámetros para confirmar la suscripción.'];
}

header('Location: ' . BASE_URL . '/config?tab=suscripcion');
exit;
