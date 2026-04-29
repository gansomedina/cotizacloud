<?php
defined('COTIZAAPP') or die;
Auth::requerir_admin();
header('Content-Type: application/json; charset=utf-8');
csrf_check();

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$activo       = !empty($body['feedback_activo']) ? 1 : 0;
$pregunta     = trim((string)($body['feedback_pregunta'] ?? ''));
$subtitulo    = trim((string)($body['feedback_subtitulo'] ?? ''));
$label_com    = trim((string)($body['feedback_label_comentario'] ?? ''));
$agradec      = trim((string)($body['feedback_agradecimiento'] ?? ''));

// Defaults si vienen vacíos (subtitulo es opcional, puede quedar vacío)
if ($pregunta === '')  $pregunta  = '¿Qué tan satisfecho estás con la atención recibida?';
if ($label_com === '') $label_com = 'Cuéntanos brevemente qué podemos mejorar en tu atención';
if ($agradec === '')   $agradec   = 'Tu opinión nos ayuda a mejorar como te atendemos';

$pregunta  = mb_substr($pregunta, 0, 255);
$subtitulo = mb_substr($subtitulo, 0, 255);
$label_com = mb_substr($label_com, 0, 255);
$agradec   = mb_substr($agradec, 0, 255);

DB::execute(
    "UPDATE empresas SET
        feedback_activo = ?,
        feedback_pregunta = ?,
        feedback_subtitulo = ?,
        feedback_label_comentario = ?,
        feedback_agradecimiento = ?
     WHERE id = ?",
    [$activo, $pregunta, $subtitulo, $label_com, $agradec, EMPRESA_ID]
);

echo json_encode(['ok' => true]);
