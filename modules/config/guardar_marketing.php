<?php
// ============================================================
//  CotizaApp — modules/config/guardar_marketing.php
//  POST /config/marketing
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

Auth::requerir_admin();

$empresa_id = EMPRESA_ID;
$plan = trial_info($empresa_id);

if (!$plan['es_business']) json_error('Función solo disponible en plan Business', 403);

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// Validar formato de cada pixel ID
$pixel_meta       = trim($body['pixel_meta'] ?? '');
$pixel_ga4        = trim($body['pixel_ga4'] ?? '');
$pixel_gads_id    = trim($body['pixel_gads_id'] ?? '');
$pixel_gads_label = trim($body['pixel_gads_label'] ?? '');
$pixel_tiktok     = trim($body['pixel_tiktok'] ?? '');

// Validaciones estrictas — solo formatos conocidos
if ($pixel_meta !== '' && !preg_match('/^\d{15,16}$/', $pixel_meta)) {
    json_error('Meta Pixel ID inválido. Debe ser un número de 15-16 dígitos.');
}
if ($pixel_ga4 !== '' && !preg_match('/^G-[A-Za-z0-9]{10,12}$/', $pixel_ga4)) {
    json_error('GA4 Measurement ID inválido. Formato: G-XXXXXXXXXX');
}
if ($pixel_gads_id !== '' && !preg_match('/^AW-\d{9,11}$/', $pixel_gads_id)) {
    json_error('Google Ads Conversion ID inválido. Formato: AW-XXXXXXXXX');
}
if ($pixel_gads_label !== '' && !preg_match('/^[A-Za-z0-9_-]{6,30}$/', $pixel_gads_label)) {
    json_error('Google Ads Conversion Label inválido.');
}
if ($pixel_tiktok !== '' && !preg_match('/^C[A-Za-z0-9]{10,25}$/', $pixel_tiktok)) {
    json_error('TikTok Pixel ID inválido. Debe empezar con C seguido de caracteres alfanuméricos.');
}

// Upsert
DB::execute(
    "INSERT INTO marketing_config (empresa_id, pixel_meta, pixel_ga4, pixel_gads_id, pixel_gads_label, pixel_tiktok)
     VALUES (?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        pixel_meta = VALUES(pixel_meta),
        pixel_ga4 = VALUES(pixel_ga4),
        pixel_gads_id = VALUES(pixel_gads_id),
        pixel_gads_label = VALUES(pixel_gads_label),
        pixel_tiktok = VALUES(pixel_tiktok)",
    [
        $empresa_id,
        $pixel_meta ?: null,
        $pixel_ga4 ?: null,
        $pixel_gads_id ?: null,
        $pixel_gads_label ?: null,
        $pixel_tiktok ?: null,
    ]
);

json_ok(['saved' => true]);
