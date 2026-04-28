<?php
defined('COTIZAAPP') or die;
Auth::requerir_admin();
header('Content-Type: application/json; charset=utf-8');
csrf_check();

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$visible = !empty($body['termometro_visible']) ? 1 : 0;

DB::execute("UPDATE empresas SET termometro_visible=? WHERE id=?", [$visible, EMPRESA_ID]);

echo json_encode(['ok' => true]);
