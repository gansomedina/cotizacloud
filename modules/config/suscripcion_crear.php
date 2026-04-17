<?php
// ============================================================
//  CotizaCloud — modules/config/suscripcion_crear.php
//  POST /config/suscripcion/crear
//  Crea preapproval en MercadoPago y redirige al checkout
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();
csrf_check();

$plan  = $_POST['plan']  ?? '';
$ciclo = $_POST['ciclo'] ?? '';

if (!in_array($plan, ['pro', 'business']) || !in_array($ciclo, ['mensual', 'anual'])) {
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Plan o ciclo inválido.'];
    redirect('/config?tab=suscripcion');
}

$empresa = DB::row("SELECT email, nombre FROM empresas WHERE id=?", [EMPRESA_ID]);
if (!$empresa) {
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Empresa no encontrada.'];
    redirect('/config?tab=suscripcion');
}

$result = MercadoPago::crearPreapproval([
    'plan'       => $plan,
    'ciclo'      => $ciclo,
    'email'      => $empresa['email'],
    'empresa_id' => EMPRESA_ID,
]);

if (isset($result['error'])) {
    error_log('[MP Crear] Error: ' . json_encode($result));
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Error al conectar con MercadoPago. Intenta de nuevo.'];
    redirect('/config?tab=suscripcion');
}

$init_point = $result['init_point'] ?? '';
if (!$init_point) {
    error_log('[MP Crear] Sin init_point: ' . json_encode($result));
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'No se pudo iniciar el proceso de pago.'];
    redirect('/config?tab=suscripcion');
}

header('Location: ' . $init_point);
exit;
