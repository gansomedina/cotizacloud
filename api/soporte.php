<?php
// ============================================================
//  CotizaApp — api/soporte.php
//  Chat de soporte casero.
//   · Admin de empresa (logueado): app
//   · Visitante anónimo del landing: captura nombre/correo (lead)
//   · Superadmin: responder
//  POST /api/soporte
// ============================================================

defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

$body   = json_decode(file_get_contents('php://input'), true) ?: [];
$accion = trim($body['accion'] ?? '');
$rol    = Auth::logueado() ? Auth::rol() : '';

// ── Helper: conversación activa por usuario (app) ──
function soporte_conv_usuario(int $uid, int $eid, bool $crear): ?int {
    $conv = DB::row("SELECT id FROM soporte_conversaciones WHERE usuario_id=? AND estado='abierta' ORDER BY id DESC LIMIT 1", [$uid]);
    if ($conv) return (int)$conv['id'];
    $rec = DB::row("SELECT id FROM soporte_conversaciones WHERE usuario_id=? AND estado='cerrada' AND COALESCE(ultimo_mensaje_at,created_at) > DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY id DESC LIMIT 1", [$uid]);
    if ($rec) { DB::execute("UPDATE soporte_conversaciones SET estado='abierta' WHERE id=?", [(int)$rec['id']]); return (int)$rec['id']; }
    if (!$crear) return null;
    return DB::insert("INSERT INTO soporte_conversaciones (empresa_id, usuario_id, origen, estado, ultimo_mensaje_at) VALUES (?,?, 'app','abierta', NOW())", [$eid, $uid]);
}

// ── Helper: conversación activa por token (landing anónimo) ──
function soporte_conv_token(string $token): ?array {
    return DB::row("SELECT id, estado FROM soporte_conversaciones WHERE visitor_token=? AND origen='landing' ORDER BY id DESC LIMIT 1", [$token]);
}

// ════════════════════════════════════════════════════════════
//  AGENTE — responder (superadmin)
// ════════════════════════════════════════════════════════════
if ($accion === 'responder') {
    if ($rol !== 'superadmin') { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Solo superadmin']); exit; }
    if (!csrf_verify()) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Token inválido']); exit; }
    $conv_id = (int)($body['conversacion_id'] ?? 0);
    $cuerpo  = mb_substr(trim((string)($body['cuerpo'] ?? '')), 0, 4000);
    if (!$conv_id || $cuerpo === '') { echo json_encode(['ok'=>false,'error'=>'Datos requeridos']); exit; }
    $conv = DB::row("SELECT id, usuario_id FROM soporte_conversaciones WHERE id=?", [$conv_id]);
    if (!$conv) { echo json_encode(['ok'=>false,'error'=>'Conversación no existe']); exit; }
    $resp_id = DB::insert("INSERT INTO soporte_mensajes (conversacion_id, autor, cuerpo) VALUES (?, 'agente', ?)", [$conv_id, $cuerpo]);
    DB::execute("UPDATE soporte_conversaciones SET ultimo_mensaje_at=NOW(), no_leidos_usuario=no_leidos_usuario+1, no_leidos_agente=0, estado='abierta' WHERE id=?", [$conv_id]);
    // Push solo si la conversación es de un usuario logueado (los anónimos ven por poll)
    if (!empty($conv['usuario_id'])) {
        try { PushNotification::enviar_a_usuario((int)$conv['usuario_id'], 'soporte_respuesta', 'Soporte CotizaCloud', mb_substr($cuerpo,0,120), ['url'=>'/dashboard','soporte'=>1]); } catch (\Throwable $e) {}
    }
    echo json_encode(['ok'=>true, 'mensaje_id'=>$resp_id]);
    exit;
}

// ════════════════════════════════════════════════════════════
//  USUARIO LOGUEADO (admin de empresa) — app
// ════════════════════════════════════════════════════════════
if ($rol === 'admin') {
    if (!csrf_verify()) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Token inválido']); exit; }
    $usuario = Auth::usuario();
    $uid = (int)$usuario['id']; $eid = (int)$usuario['empresa_id'];

    if ($accion === 'enviar') {
        $cuerpo = mb_substr(trim((string)($body['cuerpo'] ?? '')), 0, 4000);
        if ($cuerpo === '') { echo json_encode(['ok'=>false,'error'=>'Mensaje vacío']); exit; }
        $rec = (int)DB::val("SELECT COUNT(*) FROM soporte_mensajes m JOIN soporte_conversaciones c ON c.id=m.conversacion_id WHERE c.usuario_id=? AND m.autor='usuario' AND m.created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)", [$uid]);
        if ($rec >= 12) { echo json_encode(['ok'=>false,'error'=>'Demasiados mensajes, espera un momento']); exit; }
        $lock = 'soporte_conv_' . $uid;
        DB::val("SELECT GET_LOCK(?,5)", [$lock]);
        try {
            $conv_id = soporte_conv_usuario($uid, $eid, true);
            $msg_id = DB::insert("INSERT INTO soporte_mensajes (conversacion_id, autor, cuerpo) VALUES (?, 'usuario', ?)", [$conv_id, $cuerpo]);
            DB::execute("UPDATE soporte_conversaciones SET ultimo_mensaje_at=NOW(), no_leidos_agente=no_leidos_agente+1, estado='abierta' WHERE id=?", [$conv_id]);
        } finally { DB::val("SELECT RELEASE_LOCK(?)", [$lock]); }
        $ref = trim(str_replace(["\r","\n"], ' ', ($usuario['nombre'] ?? 'Usuario') . ' · ' . (Auth::empresa()['nombre'] ?? '')));
        try { PushNotification::enviar_a_superadmin('soporte_mensaje', 'Nuevo mensaje de soporte', mb_substr($ref.': '.$cuerpo,0,150), ['url'=>'/superadmin/soporte','conversacion_id'=>$conv_id]); } catch (\Throwable $e) {}
        if (defined('SUPERADMIN_EMAIL') && SUPERADMIN_EMAIL) {
            try { @mail(SUPERADMIN_EMAIL, 'Soporte — '.$ref, "Mensaje:\n{$cuerpo}\n\nResponder: https://cotiza.cloud/superadmin/soporte", "From: noreply@cotiza.cloud\r\nContent-Type: text/plain; charset=utf-8"); } catch (\Throwable $e) {}
        }
        echo json_encode(['ok'=>true, 'conversacion_id'=>$conv_id, 'mensaje_id'=>$msg_id]);
        exit;
    }
    if ($accion === 'cerrar') { DB::execute("UPDATE soporte_conversaciones SET estado='cerrada' WHERE usuario_id=? AND estado='abierta'", [$uid]); echo json_encode(['ok'=>true]); exit; }
    if ($accion === 'leido')  { DB::execute("UPDATE soporte_conversaciones SET no_leidos_usuario=0 WHERE usuario_id=? AND estado='abierta'", [$uid]); echo json_encode(['ok'=>true]); exit; }
    echo json_encode(['ok'=>false,'error'=>'Acción inválida']); exit;
}

// ════════════════════════════════════════════════════════════
//  VISITANTE ANÓNIMO (landing) — captura de lead
// ════════════════════════════════════════════════════════════
$token = substr(preg_replace('/[^a-zA-Z0-9\-]/', '', (string)($body['token'] ?? '')), 0, 64);

if ($accion === 'enviar') {
    $cuerpo = mb_substr(trim((string)($body['cuerpo'] ?? '')), 0, 4000);
    $nombre = mb_substr(trim((string)($body['nombre'] ?? '')), 0, 120);
    $email  = mb_substr(trim((string)($body['email'] ?? '')), 0, 160);
    if ($cuerpo === '') { echo json_encode(['ok'=>false,'error'=>'Mensaje vacío']); exit; }

    $ip = ip_real();
    // Rate limit por IP: máx 15 mensajes/min
    $rec = (int)DB::val("SELECT COUNT(*) FROM soporte_mensajes m JOIN soporte_conversaciones c ON c.id=m.conversacion_id WHERE c.origen='landing' AND c.ip=? AND m.autor='usuario' AND m.created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)", [$ip]);
    if ($rec >= 15) { echo json_encode(['ok'=>false,'error'=>'Demasiados mensajes, espera un momento']); exit; }

    $conv = $token !== '' ? soporte_conv_token($token) : null;
    if (!$conv) {
        // Primera vez → exigir nombre + correo válido (es el lead)
        if ($nombre === '' || !validar_email($email)) { echo json_encode(['ok'=>false,'error'=>'datos','need'=>['nombre','email']]); exit; }
        if ($token === '') {
            $token = sprintf('%04x%04x-%04x-%04x', random_int(0,0xffff), random_int(0,0xffff), random_int(0,0xffff), random_int(0,0xffff));
        }
        $conv_id = DB::insert(
            "INSERT INTO soporte_conversaciones (origen, estado, visitante_nombre, visitante_email, visitor_token, ip, ultimo_mensaje_at)
             VALUES ('landing','abierta', ?, ?, ?, ?, NOW())",
            [$nombre, $email, $token, $ip]
        );
    } else {
        $conv_id = (int)$conv['id'];
        if ($conv['estado'] === 'cerrada') DB::execute("UPDATE soporte_conversaciones SET estado='abierta' WHERE id=?", [$conv_id]);
    }
    $msg_id = DB::insert("INSERT INTO soporte_mensajes (conversacion_id, autor, cuerpo) VALUES (?, 'usuario', ?)", [$conv_id, $cuerpo]);
    DB::execute("UPDATE soporte_conversaciones SET ultimo_mensaje_at=NOW(), no_leidos_agente=no_leidos_agente+1, estado='abierta' WHERE id=?", [$conv_id]);

    $ref = trim(str_replace(["\r","\n"], ' ', "Landing · {$nombre} <{$email}>"));
    try { PushNotification::enviar_a_superadmin('soporte_landing', 'Lead del landing', mb_substr($ref.': '.$cuerpo,0,150), ['url'=>'/superadmin/soporte','conversacion_id'=>$conv_id]); } catch (\Throwable $e) {}
    if (defined('SUPERADMIN_EMAIL') && SUPERADMIN_EMAIL) {
        try { @mail(SUPERADMIN_EMAIL, 'Lead landing — '.$ref, "Prospecto: {$nombre}\nCorreo: {$email}\n\nMensaje:\n{$cuerpo}\n\nResponder: https://cotiza.cloud/superadmin/soporte", "From: noreply@cotiza.cloud\r\nContent-Type: text/plain; charset=utf-8"); } catch (\Throwable $e) {}
    }
    echo json_encode(['ok'=>true, 'token'=>$token, 'conversacion_id'=>$conv_id, 'mensaje_id'=>$msg_id]);
    exit;
}

echo json_encode(['ok'=>false,'error'=>'No disponible']);
