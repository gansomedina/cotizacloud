<?php
// ============================================================
//  CotizaCloud — modules/config/suscripcion_crear.php
//  POST /config/suscripcion/crear
//  Crea Preference en MercadoPago (Checkout Pro) y redirige
//  Al volver, api/mp/return procesa el pago y guarda el token
//  de la tarjeta para cobros recurrentes automáticos.
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();
csrf_check();

$plan  = $_POST['plan']  ?? '';
$ciclo = $_POST['ciclo'] ?? '';

if (!in_array($plan, ['lite', 'pro', 'business']) || !in_array($ciclo, ['mensual', 'anual'])) {
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Plan o ciclo inválido.'];
    redirect('/config?tab=suscripcion');
}

// Candado de downgrade: un cliente que ya paga un plan NO puede bajarse solo a
// uno menor desde aquí. Eso lo gestiona soporte (superadmin) para no dejar
// asesores/datos colgados. La UI ya lo oculta; esto es la defensa de servidor.
$plan_rango = ['free' => 0, 'lite' => 1, 'pro' => 2, 'business' => 3];
$trial      = trial_info(EMPRESA_ID);
if ($trial['es_pagado'] && ($plan_rango[$plan] ?? 0) < ($plan_rango[$trial['plan']] ?? 0)) {
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Para cambiar a un plan menor, contacta a soporte. No es posible bajar de plan automáticamente.'];
    redirect('/config?tab=suscripcion');
}

$empresa = DB::row("SELECT email, nombre, telefono, rfc FROM empresas WHERE id=?", [EMPRESA_ID]);
if (!$empresa) {
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Empresa no encontrada.'];
    redirect('/config?tab=suscripcion');
}

$result = MercadoPago::crearPreference([
    'plan'       => $plan,
    'ciclo'      => $ciclo,
    'email'      => $empresa['email'],
    'nombre'     => $empresa['nombre'] ?? '',
    'telefono'   => $empresa['telefono'] ?? '',
    'rfc'        => $empresa['rfc'] ?? '',
    'empresa_id' => EMPRESA_ID,
]);

if (isset($result['error'])) {
    error_log('[MP Preference] Error: ' . json_encode($result));
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Error al conectar con MercadoPago. Intenta de nuevo.'];
    redirect('/config?tab=suscripcion');
}

$init_point = $result['init_point'] ?? '';
if (!$init_point) {
    error_log('[MP Preference] Sin init_point: ' . json_encode($result));
    $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'No se pudo iniciar el proceso de pago.'];
    redirect('/config?tab=suscripcion');
}

$_SESSION['mp_intento'] = [
    'empresa_id'    => EMPRESA_ID,
    'plan'          => $plan,
    'ciclo'         => $ciclo,
    'preference_id' => $result['id'] ?? '',
    'created_at'    => time(),
];

header('Location: ' . $init_point);
exit;
