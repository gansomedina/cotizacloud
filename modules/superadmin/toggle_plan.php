<?php
// ============================================================
//  SuperAdmin — Toggle plan trial/pro (con fecha de vencimiento)
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

if ($accion === 'activar_pro') {
    // Activar PRO con duración
    $duracion = $_POST['duracion'] ?? '1_mes';
    $dias = match ($duracion) {
        '1_mes'  => 30,
        '3_meses' => 90,
        '6_meses' => 180,
        '1_anio' => 365,
        default  => 30,
    };
    $vence = date('Y-m-d', strtotime("+{$dias} days"));
    DB::execute("UPDATE empresas SET plan = 'pro', plan_vence = ?, activa = 1 WHERE id = ?", [$vence, $empresa_id]);

} elseif ($accion === 'renovar') {
    // Renovar PRO: extender desde hoy o desde fecha actual de vencimiento (lo que sea mayor)
    $duracion = $_POST['duracion'] ?? '1_mes';
    $dias = match ($duracion) {
        '1_mes'  => 30,
        '3_meses' => 90,
        '6_meses' => 180,
        '1_anio' => 365,
        default  => 30,
    };
    $vence_actual = $emp['plan_vence'] ?? null;
    $base = ($vence_actual && $vence_actual >= date('Y-m-d')) ? $vence_actual : date('Y-m-d');
    $nuevo_vence = date('Y-m-d', strtotime($base . " +{$dias} days"));
    DB::execute("UPDATE empresas SET plan = 'pro', plan_vence = ?, activa = 1 WHERE id = ?", [$nuevo_vence, $empresa_id]);

} elseif ($accion === 'regresar_trial') {
    // Regresar a trial
    DB::execute("UPDATE empresas SET plan = 'trial', plan_vence = NULL WHERE id = ?", [$empresa_id]);

} else {
    // Toggle simple (legacy)
    $nuevo_plan = ($emp['plan'] ?? 'trial') === 'trial' ? 'pro' : 'trial';
    DB::execute("UPDATE empresas SET plan = ? WHERE id = ?", [$nuevo_plan, $empresa_id]);
}

redirect('/superadmin/empresa/' . $empresa_id);
