<?php
// POST /api/mesa/agendar — parquea/despausa una cotización con fecha probable.
//   { cotizacion_id, fecha:'YYYY-MM-DD' }  → agenda
//   { cotizacion_id, cancelar:1 }          → desagenda (la regresa a la mesa)
// Reglas: futura y ≤ 6 meses · cooldown de 15 días entre agendas (anti-pausa
// a propósito) · solo el asesor dueño (o admin) · solo cotizaciones vivas.
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');
if (!Auth::logueado()) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'sesion']); exit; }
csrf_check();

$b        = json_decode(file_get_contents('php://input'), true) ?? [];
$cot_id   = (int)($b['cotizacion_id'] ?? 0);
$cancelar = !empty($b['cancelar']);
$fecha    = trim((string)($b['fecha'] ?? ''));

if (!$cot_id) { echo json_encode(['ok'=>false,'error'=>'datos']); exit; }

$cot = DB::row(
    "SELECT id, estado, suspendida, agenda_fecha, agenda_at,
            COALESCE(vendedor_id, usuario_id) AS vend
     FROM cotizaciones WHERE id=? AND empresa_id=?", [$cot_id, EMPRESA_ID]);
if (!$cot) { echo json_encode(['ok'=>false,'error'=>'no_encontrada']); exit; }
if ((int)$cot['vend'] !== Auth::id() && !Auth::es_admin()) { echo json_encode(['ok'=>false,'error'=>'permiso']); exit; }

// Gate de la mesa (igual que mesa_estado): asesor con mesa apagada no escribe
if (!Auth::es_admin()) {
    try { $mesa_flag = (int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [EMPRESA_ID]); }
    catch (Throwable $e) { $mesa_flag = 0; }
    if ($mesa_flag < 1) { echo json_encode(['ok'=>false,'error'=>'mesa_off']); exit; }
}
// Solo cotizaciones vivas
if (!in_array($cot['estado'], ['enviada','vista'], true) || (int)$cot['suspendida'] === 1) {
    echo json_encode(['ok'=>false,'error'=>'cerrada']); exit;
}

// ── Desagendar: regresa a la mesa. Conserva agenda_at (el cooldown sigue). ──
if ($cancelar) {
    try {
        DB::execute("UPDATE cotizaciones SET agenda_fecha=NULL WHERE id=?", [$cot_id]);
    } catch (Throwable $e) { echo json_encode(['ok'=>false,'error'=>'guardar']); exit; }
    echo json_encode(['ok'=>true, 'agenda_fecha'=>null]); exit;
}

// ── Agendar: validar fecha ──
$ts = strtotime($fecha . ' 00:00:00');
if (!$ts || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)
    // strtotime hace rollover (2026-02-31 → marzo): validar calendario real
    || !checkdate((int)substr($fecha, 5, 2), (int)substr($fecha, 8, 2), (int)substr($fecha, 0, 4))) {
    echo json_encode(['ok'=>false,'error'=>'fecha_invalida']); exit;
}
$hoy   = strtotime(date('Y-m-d') . ' 00:00:00');
$piso  = $hoy + 15 * 86400;  // mínimo 15 días — la agenda es para compra a futuro,
                             // no para posponer el seguimiento unos días (reaparece
                             // 7 días antes; con menos de 15 no alcanza a parquearse)
$tope  = $hoy + 183 * 86400; // ~6 meses
if ($ts < $piso)  { echo json_encode(['ok'=>false,'error'=>'fecha_cerca','msg'=>'La fecha debe ser de al menos 15 días. La agenda es para clientes que compran más adelante, no para posponer el seguimiento.']); exit; }
if ($ts > $tope)  { echo json_encode(['ok'=>false,'error'=>'fecha_lejana','msg'=>'Máximo 6 meses.']); exit; }

// Cooldown 15 días: no re-agendar si la última agenda fue hace < 15 días
if (!empty($cot['agenda_at']) && strtotime($cot['agenda_at']) >= time() - 15 * 86400) {
    $faltan = 15 - (int)floor((time() - strtotime($cot['agenda_at'])) / 86400);
    echo json_encode(['ok'=>false,'error'=>'cooldown','msg'=>"Ya la agendaste hace poco. Espera {$faltan} día(s) para reagendar."]); exit;
}

try {
    DB::execute("UPDATE cotizaciones SET agenda_fecha=?, agenda_at=NOW() WHERE id=?", [$fecha, $cot_id]);
} catch (Throwable $e) { echo json_encode(['ok'=>false,'error'=>'guardar']); exit; }

echo json_encode(['ok'=>true, 'agenda_fecha'=>$fecha]);
