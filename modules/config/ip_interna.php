<?php
// ============================================================
//  CotizaApp — modules/config/ip_interna.php
//  POST /config/ip-interna           → agregar
//  POST /config/ip-interna/:id/eliminar → borrar
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();

header('Content-Type: application/json');

$accion = $app['accion'] ?? 'crear';  // inyectado por Router

// ── ELIMINAR ─────────────────────────────────────────────
if ($accion === 'eliminar') {
    $id = (int)($app['id'] ?? 0);
    if (!$id) { echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }

    $row = DB::row("SELECT id FROM radar_ips_internas WHERE id=? AND empresa_id=?", [$id, EMPRESA_ID]);
    if (!$row) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }

    DB::execute("DELETE FROM radar_ips_internas WHERE id=?", [$id]);
    echo json_encode(['ok'=>true]);
    exit;
}

// ── CREAR ─────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
$ip   = trim($body['ip'] ?? '');
$desc = mb_substr(trim($body['descripcion'] ?? ''), 0, 100);

// Validar IP v4 o v6
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    echo json_encode(['ok'=>false,'error'=>'IP inválida']); exit;
}

// Duplicado
$existe = DB::val(
    "SELECT id FROM radar_ips_internas WHERE empresa_id=? AND ip=?",
    [EMPRESA_ID, $ip]
);
if ($existe) {
    echo json_encode(['ok'=>false,'error'=>'Esta IP ya está registrada']); exit;
}

DB::insert(
    "INSERT INTO radar_ips_internas (empresa_id, ip, descripcion) VALUES (?,?,?)",
    [EMPRESA_ID, $ip, $desc]
);

echo json_encode(['ok'=>true]);
