<?php
// ============================================================
//  CotizaApp — modules/radar/descartar_comp.php
//  POST /radar/descartar-comp
//  Marca una IP o visitor_id como interno para eliminar alertas
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();

header('Content-Type: application/json; charset=utf-8');
csrf_check();

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) json_error('Payload inválido', 400);

$tipo       = $body['tipo'] ?? '';
$valor      = trim($body['valor'] ?? '');
$empresa_id = (int)($body['empresa_id'] ?? EMPRESA_ID);

if ($valor === '') json_error('Valor requerido');

require_once MODULES_PATH . '/radar/Radar.php';

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
             VALUES (?, ?, 'manual_dismiss', ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
             ON DUPLICATE KEY UPDATE last_seen=UNIX_TIMESTAMP()",
            [$empresa_id, $valor, 'Descartado manualmente desde Radar']
        );
        break;

    case 'device':
        // No hay tabla de device_sigs internos — se filtran via user_sessions
        // Pero podemos marcar todos los visitor_ids asociados a este device_sig como internos
        $vids = DB::query(
            "SELECT DISTINCT visitor_id FROM quote_sessions
             WHERE device_sig = ? AND empresa_id IN (
                SELECT c.empresa_id FROM cotizaciones c
                JOIN quote_sessions qs2 ON qs2.cotizacion_id = c.id
                WHERE qs2.device_sig = ?
             ) AND visitor_id IS NOT NULL AND visitor_id != ''",
            [$valor, $valor]
        );
        foreach ($vids ?: [] as $v) {
            DB::execute(
                "INSERT IGNORE INTO radar_visitors_internos (empresa_id, visitor_id, source, label, first_seen, last_seen)
                 VALUES (?, ?, 'manual_dismiss_device', ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                 ON DUPLICATE KEY UPDATE last_seen=UNIX_TIMESTAMP()",
                [$empresa_id, $v['visitor_id'], 'Device ' . $valor . ' descartado']
            );
        }
        // También agregar las IPs asociadas
        $ips = DB::query(
            "SELECT DISTINCT qs.ip FROM quote_sessions qs
             WHERE qs.device_sig = ? AND qs.ip IS NOT NULL AND qs.ip != ''",
            [$valor]
        );
        foreach ($ips ?: [] as $r) {
            DB::execute(
                "INSERT IGNORE INTO radar_ips_internas (empresa_id, ip) VALUES (?, ?)",
                [$empresa_id, $r['ip']]
            );
        }
        break;

    default:
        json_error('Tipo no válido');
}

json_ok(['descartado' => true]);
