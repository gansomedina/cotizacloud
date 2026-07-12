<?php
// ============================================================
//  CotizaApp — api/radar_feedback.php
//  POST /api/radar-feedback
//  Registra feedback del vendedor sobre señales calientes del Radar
// ============================================================

defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::logueado()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}
csrf_check();

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$cot_id = (int)($body['cotizacion_id'] ?? 0);
$tipo   = $body['tipo'] ?? '';

if (!$cot_id || !in_array($tipo, ['con_interes', 'sin_interes'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

$empresa_id = EMPRESA_ID;
$usuario_id = Auth::id();

// Verificar que la cotización existe y pertenece a la empresa
$cot = DB::row(
    "SELECT id, estado, suspendida, radar_bucket, COALESCE(vendedor_id, usuario_id) AS vendedor_id
     FROM cotizaciones WHERE id=? AND empresa_id=?",
    [$cot_id, $empresa_id]
);
if (!$cot) {
    echo json_encode(['ok' => false, 'error' => 'Cotización no encontrada']);
    exit;
}

// Solo el vendedor asignado o admin pueden dar feedback
if ((int)$cot['vendedor_id'] !== $usuario_id && !Auth::es_admin()) {
    echo json_encode(['ok' => false, 'error' => 'Sin permiso']);
    exit;
}

// Solo cotizaciones vivas — feedback post-desenlace inflaría el examen
// de Seguimiento (con_interes sobre aceptada = acierto máximo garantizado).
// Suspendida es COLUMNA, no estado: el guard de estado no la cubre.
if (!in_array($cot['estado'], ['enviada', 'vista'], true) || (int)$cot['suspendida'] === 1) {
    echo json_encode(['ok' => false, 'error' => 'Cotización cerrada']);
    exit;
}

// Rate-limit por usuario (mismo criterio que mesa_estado.php)
try {
    $taps_min = (int)DB::val(
        "SELECT COUNT(*) FROM mesa_estados
         WHERE usuario_id = ? AND created_at >= NOW() - INTERVAL 1 MINUTE", [$usuario_id]
    );
    if ($taps_min >= 30) { echo json_encode(['ok' => false, 'error' => 'Demasiados taps, espera un momento']); exit; }
} catch (Throwable $e) {} // tabla aún no migrada

// Upsert A NOMBRE DEL ASESOR dueño (misma identidad que la Mesa de Trabajo:
// la llave es (cotizacion, usuario) — escribir como el vendedor garantiza UNA
// sola marca por cotización aunque quien tapee sea el admin).
// Los DOS writes van en TRANSACCIÓN: si la historia falla y el upsert queda,
// la última fila 'feedback' seguiría diciendo lo contrario que la marca
// vigente — y Recuperado contaría ventas corregidas (rama mf).
try {
    DB::beginTransaction();
    DB::execute(
        "INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE tipo = VALUES(tipo), updated_at = NOW()",
        [$cot_id, (int)$cot['vendedor_id'], $empresa_id, $tipo]
    );
    // Historia insert-only (misma que los taps de la Mesa): el upsert pierde
    // el CUÁNDO al re-tapear (updated_at se bumpea) — esta fila preserva que
    // la señal se atendió en su momento, para el reporte del equipo.
    DB::execute(
        "INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, razon, bucket_snapshot)
         VALUES (?,?,?,'feedback',?,NULL,?)",
        [$cot_id, $usuario_id, $empresa_id, $tipo, $cot['radar_bucket']]
    );
    DB::commit();
} catch (Throwable $e) {
    try { DB::rollback(); } catch (Throwable $e2) {}
    // Solo tolerar mesa_estados sin migrar: en ese caso guardar el feedback solo
    if (stripos($e->getMessage(), 'mesa_estados') !== false) {
        try {
            DB::execute(
                "INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE tipo = VALUES(tipo), updated_at = NOW()",
                [$cot_id, (int)$cot['vendedor_id'], $empresa_id, $tipo]
            );
        } catch (Throwable $e2) {
            error_log('[Radar feedback retry] ' . $e2->getMessage());
            echo json_encode(['ok' => false, 'error' => 'No se pudo guardar']); exit;
        }
    } else {
        error_log('[Radar feedback] ' . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'No se pudo guardar']); exit;
    }
}

echo json_encode(['ok' => true, 'tipo' => $tipo]);
