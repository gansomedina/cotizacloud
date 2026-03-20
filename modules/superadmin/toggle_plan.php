<?php
// ============================================================
//  SuperAdmin — Gestión de planes (free/pro/business)
//  POST /superadmin/empresa/:id/plan
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();
csrf_check();

$empresa_id = (int)($id ?? 0);
$emp = DB::row("SELECT id, plan, plan_vence, slug FROM empresas WHERE id = ?", [$empresa_id]);
if (!$emp || $emp['slug'] === '_system') {
    http_response_code(404);
    die('Empresa no encontrada');
}

$accion = $_POST['accion'] ?? 'toggle';

$duracion_dias = function() {
    $duracion = $_POST['duracion'] ?? '1_mes';
    return match ($duracion) {
        '1_mes'  => 30,
        '3_meses' => 90,
        '6_meses' => 180,
        '1_anio' => 365,
        default  => 30,
    };
};

if ($accion === 'activar_pro') {
    $vence = date('Y-m-d', strtotime("+{$duracion_dias()} days"));
    DB::execute("UPDATE empresas SET plan = 'pro', plan_vence = ?, activa = 1 WHERE id = ?", [$vence, $empresa_id]);

} elseif ($accion === 'activar_business') {
    $vence = date('Y-m-d', strtotime("+{$duracion_dias()} days"));
    DB::execute("UPDATE empresas SET plan = 'business', plan_vence = ?, activa = 1 WHERE id = ?", [$vence, $empresa_id]);

} elseif ($accion === 'renovar') {
    // Renovar: extender desde hoy o desde fecha actual de vencimiento (lo que sea mayor)
    $dias = $duracion_dias();
    $vence_actual = $emp['plan_vence'] ?? null;
    $base = ($vence_actual && $vence_actual >= date('Y-m-d')) ? $vence_actual : date('Y-m-d');
    $nuevo_vence = date('Y-m-d', strtotime($base . " +{$dias} days"));
    // Mantener el plan actual (pro o business)
    $plan_actual = in_array($emp['plan'], ['pro', 'business']) ? $emp['plan'] : 'pro';
    DB::execute("UPDATE empresas SET plan = ?, plan_vence = ?, activa = 1 WHERE id = ?", [$plan_actual, $nuevo_vence, $empresa_id]);

} elseif ($accion === 'cambiar_plan') {
    // Cambiar entre pro y business manteniendo la fecha de vencimiento
    $nuevo_plan = $_POST['nuevo_plan'] ?? 'pro';
    if (!in_array($nuevo_plan, ['pro', 'business'])) $nuevo_plan = 'pro';
    DB::execute("UPDATE empresas SET plan = ? WHERE id = ?", [$nuevo_plan, $empresa_id]);

} elseif ($accion === 'regresar_free') {
    DB::execute("UPDATE empresas SET plan = 'free', plan_vence = NULL WHERE id = ?", [$empresa_id]);

} elseif ($accion === 'regresar_trial') {
    // Compatibilidad legacy → ahora va a free
    DB::execute("UPDATE empresas SET plan = 'free', plan_vence = NULL WHERE id = ?", [$empresa_id]);

} else {
    // Toggle simple (legacy)
    $nuevo_plan = ($emp['plan'] ?? 'free') === 'free' ? 'pro' : 'free';
    DB::execute("UPDATE empresas SET plan = ? WHERE id = ?", [$nuevo_plan, $empresa_id]);
}

redirect('/superadmin/empresa/' . $empresa_id);
