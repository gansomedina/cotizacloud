<?php
// ============================================================
//  cotiza.cloud — modules/config/guardar_empresa.php
//  POST /config/empresa
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
header('Content-Type: application/json');

$eid  = EMPRESA_ID;
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { echo json_encode(['ok'=>false,'error'=>'Payload inválido']); exit; }

$nombre  = mb_substr(trim($body['nombre'] ?? ''), 0, 120);
if ($nombre === '') { echo json_encode(['ok'=>false,'error'=>'El nombre es obligatorio']); exit; }

$impuesto_modo = in_array($body['impuesto_modo']??'', ['ninguno','suma','incluido'])
    ? $body['impuesto_modo'] : 'ninguno';
$impuesto_pct  = max(0, min(99, (float)($body['impuesto_pct'] ?? 16)));
$vigencia_dias = max(1, min(365, (int)($body['cot_vigencia_dias'] ?? 30)));

$auto_susp_dias = max(7, min(365, (int)($body['auto_suspender_dias'] ?? 30)));
$themes_validos = ['verde','azul','rojo','naranja','dorado','morado','oscuro'];
$cot_theme = in_array($body['cot_theme'] ?? '', $themes_validos) ? $body['cot_theme'] : 'verde';

DB::execute(
    "UPDATE empresas SET
        nombre             = ?,
        ciudad             = ?,
        telefono           = ?,
        email              = ?,
        direccion          = ?,
        rfc                = ?,
        website            = ?,
        impuesto_modo      = ?,
        impuesto_pct       = ?,
        notif_email        = ?,
        notif_email_acepta = ?,
        notif_email_rechaza= ?,
        notif_config       = ?,
        cot_vigencia_dias  = ?,
        allow_precio_edit  = ?,
        auto_suspender_activo = ?,
        auto_suspender_dias   = ?,
        cot_theme          = ?,
        cot_encabezado     = ?,
        cot_msg_acepta     = ?,
        cot_msg_rechaza    = ?,
        cot_terminos       = ?,
        cot_footer         = ?,
        vta_terminos       = ?,
        vta_footer         = ?
     WHERE id = ?",
    [
        $nombre,
        mb_substr(trim($body['ciudad']      ?? ''), 0, 80),
        mb_substr(trim($body['telefono']    ?? ''), 0, 30),
        mb_substr(trim($body['email']       ?? ''), 0, 120),
        mb_substr(trim($body['direccion']   ?? ''), 0, 500),
        mb_substr(strtoupper(trim($body['rfc'] ?? '')), 0, 20),
        mb_substr(trim($body['website']     ?? ''), 0, 120),
        $impuesto_modo,
        $impuesto_pct,
        mb_substr(trim($body['notif_email'] ?? ''), 0, 255),
        (int)($body['notif_email_acepta']  ?? 0),
        (int)($body['notif_email_rechaza'] ?? 0),
        isset($body['notif_config']) ? json_encode($body['notif_config']) : null,
        $vigencia_dias,
        (int)($body['allow_precio_edit']   ?? 1),
        (int)($body['auto_suspender_activo'] ?? 0),
        $auto_susp_dias,
        $cot_theme,
        mb_substr($body['cot_encabezado'] ?? '', 0, 2000),
        mb_substr($body['cot_msg_acepta']  ?? '', 0, 2000),
        mb_substr($body['cot_msg_rechaza'] ?? '', 0, 2000),
        $body['cot_terminos'] ?? '',
        mb_substr($body['cot_footer']      ?? '', 0, 500),
        $body['vta_terminos'] ?? '',
        mb_substr($body['vta_footer']      ?? '', 0, 500),
        $eid,
    ]
);
echo json_encode(['ok' => true]);
