<?php
// POST /api/mesa/estado — declara el desenlace de un toque (Mesa de Trabajo)
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');
if (!Auth::logueado()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
csrf_check();

$b = json_decode(file_get_contents('php://input'), true) ?? [];
$cot_id = (int)($b['cotizacion_id'] ?? 0);
$area   = trim((string)($b['area'] ?? ''));
$estado = trim((string)($b['estado'] ?? ''));
$razon  = trim((string)($b['razon'] ?? '')) ?: null;

$AREAS = [
    'contacto'   => ['no_contesta','hablamos'],
    'compromiso' => ['en_cita','propuse_no_quiso','sin_compromiso'],
    'postura'    => ['decidiendo','objecion_precio','pidio_cambios','en_el_aire','descartada'],
];
$VALIDOS = $AREAS[$area] ?? [];
$RAZONES = ['precio','competencia','despues','no_responde','no_comprador','otro'];
if (!$cot_id || !in_array($estado, $VALIDOS, true)) { echo json_encode(['ok'=>false,'error'=>'datos']); exit; }
if ($estado === 'descartada' && !in_array($razon, $RAZONES, true)) { echo json_encode(['ok'=>false,'error'=>'razon']); exit; }
if ($estado !== 'descartada') $razon = null;

$cot = DB::row("SELECT id, radar_bucket, COALESCE(vendedor_id, usuario_id) AS vend
                FROM cotizaciones WHERE id=? AND empresa_id=?", [$cot_id, EMPRESA_ID]);
if (!$cot) { echo json_encode(['ok'=>false,'error'=>'no_encontrada']); exit; }
if ((int)$cot['vend'] !== Auth::id() && !Auth::es_admin()) { echo json_encode(['ok'=>false,'error'=>'permiso']); exit; }

// Historia insert-only
DB::execute(
    "INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, razon, bucket_snapshot)
     VALUES (?,?,?,?,?,?,?)",
    [$cot_id, Auth::id(), EMPRESA_ID, $area, $estado, $razon, $cot['radar_bucket']]
);

// Proyección compatible → radar_feedback (el examen del score no se toca)
$map = ['en_cita'=>'con_interes','decidiendo'=>'con_interes','objecion_precio'=>'con_interes',
        'pidio_cambios'=>'con_interes','descartada'=>'sin_interes'];
if (isset($map[$estado])) {
    DB::execute(
        "INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo)
         VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE tipo=VALUES(tipo), updated_at=NOW()",
        [$cot_id, Auth::id(), EMPRESA_ID, $map[$estado]]
    );
}
echo json_encode(['ok'=>true, 'estado'=>$estado]);
