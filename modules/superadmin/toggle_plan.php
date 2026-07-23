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

// Asientos (paquetes 23-jul): tope de usuarios ACTIVOS por empresa. Vacío/0 =
// default del plan (Free/Lite=1, Pro/Business=ilimitado). Perilla para Business
// por asiento pactado en demo, o para capar un plan puntual.
if ($accion === 'asientos') {
    $val = trim((string)($_POST['asientos'] ?? ''));
    $asientos = ($val === '' || (int)$val <= 0) ? null : min(250, (int)$val);
    try {
        DB::execute("UPDATE empresas SET asientos = ? WHERE id = ?", [$asientos, $empresa_id]);
    } catch (Throwable $e) {
        error_log('[Asientos] columna sin migrar — correr migrations/add_asientos.sql');
    }
    redirect('/superadmin/empresa/' . $empresa_id);
}

// Mesa de Trabajo: rollout por empresa (0=off, 1=UI asesores, 2=UI+score 25%)
if ($accion === 'mesa_activa') {
    $val = (int)($_POST['valor'] ?? 0);
    if (!in_array($val, [0, 1, 2], true)) $val = 0;
    try {
        DB::execute("UPDATE empresas SET mesa_activa = ? WHERE id = ?", [$val, $empresa_id]);
    } catch (Throwable $e) {
        error_log('[Mesa toggle] columna mesa_activa sin migrar — correr migrations/add_mesa_score.sql');
    }
    redirect('/superadmin/empresa/' . $empresa_id);
}

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

if ($accion === 'activar_lite') {
    $vence = date('Y-m-d', strtotime("+{$duracion_dias()} days"));
    DB::execute("UPDATE empresas SET plan = 'lite', plan_vence = ?, activa = 1 WHERE id = ?", [$vence, $empresa_id]);

} elseif ($accion === 'activar_pro') {
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
    // Mantener el plan actual (lite, pro o business)
    $plan_actual = in_array($emp['plan'], ['lite', 'pro', 'business']) ? $emp['plan'] : 'pro';
    DB::execute("UPDATE empresas SET plan = ?, plan_vence = ?, activa = 1 WHERE id = ?", [$plan_actual, $nuevo_vence, $empresa_id]);

} elseif ($accion === 'cambiar_plan') {
    // Cambiar entre lite, pro y business manteniendo la fecha de vencimiento
    $nuevo_plan = $_POST['nuevo_plan'] ?? 'pro';
    if (!in_array($nuevo_plan, ['lite', 'pro', 'business'])) $nuevo_plan = 'pro';
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
