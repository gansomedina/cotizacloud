<?php
// ============================================================
//  CotizaApp — api/soporte_poll.php
//  GET /api/soporte/poll?since=ID   (requiere login, rol admin)
//  Devuelve mensajes nuevos + estado de horario + no leídos.
// ============================================================

defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::logueado() || Auth::rol() !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$usuario = Auth::usuario();
$uid     = (int)$usuario['id'];
$since   = max(0, (int)($_GET['since'] ?? 0));

// ── Estado de horario (desde data/soporte_config.json) ──────
function soporte_estado_horario(): array {
    $f = dirname(__DIR__) . '/data/soporte_config.json';
    $cfg = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
    if (empty($cfg['activo'])) {
        return ['online' => false, 'msg' => $cfg['msg_fuera'] ?? 'Déjanos tu mensaje y te respondemos pronto.', 'saludo' => $cfg['saludo'] ?? ''];
    }
    $tz = $cfg['tz'] ?? 'America/Hermosillo';
    try { $now = new DateTimeImmutable('now', new DateTimeZone($tz)); }
    catch (\Throwable $e) { $now = new DateTimeImmutable('now'); }
    $dow = (int)$now->format('N'); // 1=lun ... 7=dom
    $h   = (int)$now->format('G');
    $rango = $cfg['horario'][(string)$dow] ?? null;
    $online = is_array($rango) && count($rango) === 2 && $h >= (int)$rango[0] && $h < (int)$rango[1];
    return [
        'online' => $online,
        'msg'    => $online ? ($cfg['msg_online'] ?? 'En línea') : ($cfg['msg_fuera'] ?? 'Fuera de horario'),
        'saludo' => $cfg['saludo'] ?? '',
    ];
}

// Conversación activa (abierta o la última)
$conv = DB::row(
    "SELECT id, estado, no_leidos_usuario FROM soporte_conversaciones
     WHERE usuario_id = ? ORDER BY id DESC LIMIT 1",
    [$uid]
);

$mensajes = [];
$no_leidos = 0;
$conv_id = 0;
if ($conv) {
    $conv_id   = (int)$conv['id'];
    $no_leidos = (int)$conv['no_leidos_usuario'];
    $rows = DB::query(
        "SELECT id, autor, cuerpo, created_at
         FROM soporte_mensajes
         WHERE conversacion_id = ? AND id > ?
         ORDER BY id ASC LIMIT 50",
        [$conv_id, $since]
    );
    foreach ($rows as $r) {
        $mensajes[] = [
            'id'    => (int)$r['id'],
            'autor' => $r['autor'],
            'cuerpo'=> $r['cuerpo'],
            'hora'  => date('H:i', strtotime($r['created_at'])),
        ];
    }
}

echo json_encode([
    'ok'             => true,
    'conversacion_id'=> $conv_id,
    'estado'         => $conv['estado'] ?? 'cerrada',
    'no_leidos'      => $no_leidos,
    'horario'        => soporte_estado_horario(),
    'mensajes'       => $mensajes,
]);
