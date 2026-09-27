<?php
// ============================================================
//  cotiza.cloud — modules/config/guardar_metas.php
//  POST /config/metas   (JSON)
//    {accion:'meta',   equilibrio, pesimista, optimista}   → la meta general
//    {accion:'quitar'}                                     → sin meta
//    {accion:'tasa',   tasa}                               → '' = no declarada
//
//  La meta es UNA, general de la empresa, igual para todos los meses.
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
if (!is_array($body)) $body = [];
$accion = is_string($body['accion'] ?? null) ? $body['accion'] : '';

try {
    if ($accion === 'tasa') {
        $raw  = is_scalar($body['tasa'] ?? null) ? trim((string)$body['tasa']) : 'x';
        // En la tasa la coma es DECIMAL ("12,5" = 12.5): nadie escribe miles en un porcentaje.
        $tasa = $raw === '' ? null : MetasEmpresa::parse_monto(str_replace(['%', ','], ['', '.'], $raw));
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

    if ($accion === 'quitar') {
        DB::execute("UPDATE empresas SET meta_equilibrio = NULL, meta_pesimista = NULL, meta_optimista = NULL,
                            meta_moneda = NULL, meta_capturada_at = NULL, meta_capturada_por = NULL
                      WHERE id = ?", [$empresa_id]);
        json_ok(['quitada' => true]);
    }

    if ($accion !== 'meta') json_error('Acción inválida');

    foreach (['equilibrio', 'pesimista', 'optimista'] as $k) {
        if (MetasEmpresa::decimales_de_mas($body[$k] ?? null)) {
            json_error('Los montos llevan máximo 2 decimales. Para miles usa coma: 180,000.');
        }
    }
    $E = MetasEmpresa::parse_monto($body['equilibrio'] ?? null);
    $P = MetasEmpresa::parse_monto($body['pesimista'] ?? null);
    $O = MetasEmpresa::parse_monto($body['optimista'] ?? null);
    if ($err = MetasEmpresa::validar_metas($E, $P, $O)) json_error($err);

    // La moneda se copia de la empresa al guardar: si la empresa cambia de
    // moneda, la meta queda en sin_metas en vez de compararse mal.
    $moneda = strtoupper((string)(DB::val("SELECT moneda FROM empresas WHERE id = ?", [$empresa_id]) ?: 'MXN'));

    DB::execute(
        "UPDATE empresas SET meta_equilibrio = ?, meta_pesimista = ?, meta_optimista = ?,
                             meta_moneda = ?, meta_capturada_at = NOW(), meta_capturada_por = ?
          WHERE id = ?",
        [round($E, 2), round($P, 2), round($O, 2), $moneda, Auth::id(), $empresa_id]);
    json_ok(['guardado' => true]);

} catch (\PDOException $ex) {
    error_log('[Metas] guardar empresa ' . $empresa_id . ': ' . $ex->getMessage());
    json_error('No se pudo guardar. Si es la primera vez, falta correr la migración de metas.', 500);
}
