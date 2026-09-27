<?php
// ============================================================
//  cotiza.cloud — modules/config/guardar_metas.php
//  POST /config/metas   (JSON)
//    {accion:'mes',    anio, mes, equilibrio, pesimista, optimista}
//    {accion:'borrar', anio, mes}      → el mes vuelve a heredar
//    {accion:'tasa',   tasa}           → '' = no declarada
//
//  Solo admin, solo Business vigente. La regla de validación vive en
//  MetasEmpresa (la misma que prueba tools/sim_metas.php).
// ============================================================
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

Auth::requerir_admin();
csrf_check();

if (!class_exists('MetasEmpresa')) require_once __DIR__ . '/../../core/MetasEmpresa.php';

$empresa_id = EMPRESA_ID;
if (!MetasEmpresa::plan_ok($empresa_id)) json_error('Las metas son exclusivas del plan Business', 403);

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = (string)($body['accion'] ?? '');

try {
    if ($accion === 'tasa') {
        $raw  = trim((string)($body['tasa'] ?? ''));
        $tasa = $raw === '' ? null : MetasEmpresa::parse_monto(str_replace('%', '', $raw));
        if ($raw !== '' && $tasa === null) json_error('Escribe la tasa como número, por ejemplo 25.');
        if ($tasa !== null) $tasa = round($tasa, 2);
        if ($err = MetasEmpresa::validar_tasa($tasa)) json_error($err);

        // _desde solo se mueve si el valor cambió: marca desde cuándo rige.
        DB::execute(
            "UPDATE empresas
                SET tasa_conv_meta_desde = CASE WHEN tasa_conv_meta <=> ? THEN tasa_conv_meta_desde
                                                WHEN ? IS NULL THEN NULL ELSE NOW() END,
                    tasa_conv_meta = ?
              WHERE id = ?",
            [$tasa, $tasa, $tasa, $empresa_id]);
        json_ok(['tasa' => $tasa]);
    }

    $anio = (int)($body['anio'] ?? 0);
    $mes  = (int)($body['mes'] ?? 0);
    if (!MetasEmpresa::mes_editable($anio, $mes)) {
        json_error('Ese mes ya no se puede editar (se capturan del mes actual −5 al +6).');
    }

    if ($accion === 'borrar') {
        DB::execute("DELETE FROM empresa_metas_mes WHERE empresa_id = ? AND anio = ? AND mes = ?",
            [$empresa_id, $anio, $mes]);
        json_ok(['borrado' => true]);
    }

    if ($accion !== 'mes') json_error('Acción inválida');

    $E = MetasEmpresa::parse_monto($body['equilibrio'] ?? null);
    $P = MetasEmpresa::parse_monto($body['pesimista'] ?? null);
    $O = MetasEmpresa::parse_monto($body['optimista'] ?? null);
    if ($err = MetasEmpresa::validar_metas($E, $P, $O)) json_error($err);

    // La moneda se copia de la empresa al guardar: si la empresa cambia de
    // moneda, las metas viejas quedan en sin_metas en vez de compararse mal.
    $moneda = strtoupper((string)(DB::val("SELECT moneda FROM empresas WHERE id = ?", [$empresa_id]) ?: 'MXN'));

    DB::execute(
        "INSERT INTO empresa_metas_mes
            (empresa_id, anio, mes, equilibrio, meta_pesimista, meta_optimista, moneda, capturado_por)
         VALUES (?,?,?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE equilibrio = VALUES(equilibrio), meta_pesimista = VALUES(meta_pesimista),
                                 meta_optimista = VALUES(meta_optimista), moneda = VALUES(moneda),
                                 capturado_por = VALUES(capturado_por)",
        [$empresa_id, $anio, $mes, round($E, 2), round($P, 2), round($O, 2), $moneda, Auth::id()]);
    json_ok(['guardado' => true]);

} catch (\PDOException $ex) {
    error_log('[Metas] guardar empresa ' . $empresa_id . ': ' . $ex->getMessage());
    json_error('No se pudo guardar. Si es la primera vez, falta correr la migración de metas.', 500);
}
