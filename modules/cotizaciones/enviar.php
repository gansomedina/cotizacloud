<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/enviar.php
//  POST /cotizaciones/:id/enviar
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

csrf_check();

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

$cot = DB::row(
    "SELECT * FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('No encontrada', 404);

if (!in_array($cot['estado'], ['borrador','enviada','vista'])) {
    json_error('No se puede enviar en estado: ' . $cot['estado'], 422);
}

$empresa = Auth::empresa();

DB::beginTransaction();
try {
    DB::execute(
        "UPDATE cotizaciones SET estado='enviada', enviada_at=NOW() WHERE id=?",
        [$cot_id]
    );
    DB::execute(
        "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, ip) VALUES (?,?,'enviada',?)",
        [$cot_id, Auth::id(), ip_real()]
    );

    // Notificación por correo (si configurada)
    // TODO: Mailer::enviar_cotizacion($cot, $empresa);

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    json_error('Error al enviar', 500);
}

$url = 'https://' . EMPRESA_SLUG . '.' . BASE_DOMAIN . '/c/' . $cot['slug'];
json_ok(['url' => $url]);
