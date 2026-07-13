<?php
// ============================================================
//  cotiza.cloud — modules/config/desc_int_guardar.php
//  POST /config/desc-int — guarda la config de Descuentos Inteligentes
//  (toggles + %). Solo toca las columnas de settings; las anclas
//  cacheadas (p75/p90/zonas) NO se tocan aquí.
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
csrf_check();
header('Content-Type: application/json');

$eid  = EMPRESA_ID;
$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) { echo json_encode(['ok'=>false,'error'=>'Payload inválido']); exit; }

$r1_activa = !empty($body['r1_activa']) ? 1 : 0;
$r2_activa = !empty($body['r2_activa']) ? 1 : 0;
$r1_pct    = max(0.0, min(100.0, (float)($body['r1_pct'] ?? 0)));
$r2_pct    = max(0.0, min(100.0, (float)($body['r2_pct'] ?? 0)));

// Activar una regla exige un porcentaje > 0 (un descuento de 0% no tiene sentido)
if ($r1_activa && $r1_pct <= 0) { echo json_encode(['ok'=>false,'error'=>'La Regla 1 está activa pero el porcentaje es 0']); exit; }
if ($r2_activa && $r2_pct <= 0) { echo json_encode(['ok'=>false,'error'=>'La Regla 2 está activa pero el porcentaje es 0']); exit; }

try {
    DB::execute(
        "INSERT INTO desc_int_config (empresa_id, r1_activa, r1_pct, r2_activa, r2_pct)
         VALUES (?,?,?,?,?)
         ON DUPLICATE KEY UPDATE
           r1_activa=VALUES(r1_activa), r1_pct=VALUES(r1_pct),
           r2_activa=VALUES(r2_activa), r2_pct=VALUES(r2_pct)",
        [$eid, $r1_activa, $r1_pct, $r2_activa, $r2_pct]
    );
} catch (\Throwable $e) {
    error_log('[DescInt guardar] ' . $e->getMessage());
    echo json_encode(['ok'=>false,'error'=>'No se pudo guardar (¿migración pendiente?)']); exit;
}

echo json_encode(['ok'=>true]);
