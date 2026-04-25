<?php
// ============================================================
//  CotizaApp — modules/radar/descartar_comp.php
//  POST /radar/descartar-comp
//  Dos acciones:
//    review   → "Ya revisé" — limpia alerta, reaparece si hay actividad nueva
//    internal → "Es interno" — marca IP/visitor como del equipo permanentemente
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();

header('Content-Type: application/json; charset=utf-8');
csrf_check();

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

$accion     = $body['accion'] ?? 'review';
$tipo       = $body['tipo'] ?? '';
$valor      = trim($body['valor'] ?? '');
$empresa_id = (int)($body['empresa_id'] ?? EMPRESA_ID);

if ($valor === '') json_error('Valor requerido');
if (!in_array($tipo, ['ip', 'user', 'device'], true)) json_error('Tipo no válido');

require_once MODULES_PATH . '/radar/Radar.php';

if ($accion === 'internal') {
    // Marcar como interno permanentemente
    switch ($tipo) {
        case 'ip':
            DB::execute(
                "INSERT IGNORE INTO radar_ips_internas (empresa_id, ip) VALUES (?, ?)",
                [$empresa_id, $valor]
            );
            break;
        case 'user':
            DB::execute(
                "INSERT IGNORE INTO radar_visitors_internos (empresa_id, visitor_id, source, label, first_seen, last_seen)
                 VALUES (?, ?, 'manual', 'Marcado como interno desde Radar', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                 ON DUPLICATE KEY UPDATE last_seen=UNIX_TIMESTAMP()",
                [$empresa_id, $valor]
            );
            break;
        case 'device':
            $vids = DB::query(
                "SELECT DISTINCT qs.visitor_id FROM quote_sessions qs
                 JOIN cotizaciones c ON c.id = qs.cotizacion_id
                 WHERE qs.device_sig = ? AND c.empresa_id = ?
                   AND qs.visitor_id IS NOT NULL AND qs.visitor_id != ''",
                [$valor, $empresa_id]
            );
            foreach ($vids ?: [] as $v) {
                DB::execute(
                    "INSERT IGNORE INTO radar_visitors_internos (empresa_id, visitor_id, source, label, first_seen, last_seen)
                     VALUES (?, ?, 'manual_device', ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                     ON DUPLICATE KEY UPDATE last_seen=UNIX_TIMESTAMP()",
                    [$empresa_id, $v['visitor_id'], 'Device ' . $valor]
                );
            }
            $ips = DB::query(
                "SELECT DISTINCT qs.ip FROM quote_sessions qs
                 JOIN cotizaciones c ON c.id = qs.cotizacion_id
                 WHERE qs.device_sig = ? AND c.empresa_id = ?
                   AND qs.ip IS NOT NULL AND qs.ip != ''",
                [$valor, $empresa_id]
            );
            foreach ($ips ?: [] as $r) {
                DB::execute(
                    "INSERT IGNORE INTO radar_ips_internas (empresa_id, ip) VALUES (?, ?)",
                    [$empresa_id, $r['ip']]
                );
            }
            break;
    }
    json_ok(['interno' => true]);
} else {
    // "Ya revisé" — guardar fecha, la alerta reaparece si hay actividad nueva
    DB::execute(
        "INSERT INTO radar_comp_reviewed (empresa_id, tipo, valor, reviewed_at)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE reviewed_at = NOW()",
        [$empresa_id, $tipo, $valor]
    );
    json_ok(['revisado' => true]);
}
