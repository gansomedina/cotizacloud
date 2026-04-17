<?php
// ============================================================
//  CotizaCloud — api/mp_return.php
//  GET /api/mp/return
//  Return URL después del checkout de MercadoPago
//  Redirige al usuario a la configuración con mensaje flash
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = (int)($_GET['empresa_id'] ?? 0);
$status = $_GET['status'] ?? '';

if ($status === 'authorized' || $status === 'approved') {
    $_SESSION['flash'] = ['tipo' => 'ok', 'msg' => '¡Suscripción activada! Tu plan se actualizará en unos momentos.'];
} elseif ($status === 'pending') {
    $_SESSION['flash'] = ['tipo' => 'info', 'msg' => 'Tu pago está pendiente de aprobación. Te notificaremos cuando se confirme.'];
} else {
    $_SESSION['flash'] = ['tipo' => 'warning', 'msg' => 'El proceso de pago no se completó. Puedes intentarlo de nuevo.'];
}

header('Location: ' . BASE_URL . '/config?tab=suscripcion');
exit;
