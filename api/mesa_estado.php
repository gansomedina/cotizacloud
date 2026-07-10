<?php
// POST /api/mesa/estado — declara el desenlace de un toque (Mesa de Trabajo)
// Responde {"ok":true,"estado":...,"sugerencia":...} — la sugerencia se
// recalcula con la MEZCLA completa (MesaSugerencias) para swap instantáneo.
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');
if (!Auth::logueado()) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'sesion']); exit; }
csrf_check();

$b = json_decode(file_get_contents('php://input'), true) ?? [];
$cot_id = (int)($b['cotizacion_id'] ?? 0);
$area   = trim((string)($b['area'] ?? ''));
$estado = trim((string)($b['estado'] ?? ''));
$razon  = trim((string)($b['razon'] ?? '')) ?: null;

$AREAS = [
    'contacto'   => ['no_contesta','hablamos'],
    'compromiso' => ['compromiso','propuse_no_quiso','sin_compromiso'],
    'postura'    => ['decidiendo','objecion_precio','pidio_cambios','en_el_aire','descartada'],
    'feedback'   => ['con_interes','sin_interes'],   // 👍/👎 homologado con el Radar
];
$VALIDOS = $AREAS[$area] ?? [];
$RAZONES = ['precio','competencia','despues','no_responde','no_comprador','otro'];
if (!$cot_id || !in_array($estado, $VALIDOS, true)) { echo json_encode(['ok'=>false,'error'=>'datos']); exit; }
if ($estado === 'descartada' && !in_array($razon, $RAZONES, true)) { echo json_encode(['ok'=>false,'error'=>'razon']); exit; }
if ($estado !== 'descartada') $razon = null;

$cot = DB::row(
    "SELECT id, estado, total, visitas, radar_bucket, radar_bucket_at, radar_senales, ultima_vista_at, created_at,
            DATEDIFF(NOW(), created_at) AS edad,
            DATEDIFF(NOW(), COALESCE(ultima_vista_at, created_at)) AS dias_sin_vista,
            COALESCE(vendedor_id, usuario_id) AS vend
     FROM cotizaciones WHERE id=? AND empresa_id=?", [$cot_id, EMPRESA_ID]);
if (!$cot) { echo json_encode(['ok'=>false,'error'=>'no_encontrada']); exit; }
if ((int)$cot['vend'] !== Auth::id() && !Auth::es_admin()) { echo json_encode(['ok'=>false,'error'=>'permiso']); exit; }
// Solo cotizaciones vivas: declarar/feedbackear una aceptada/rechazada/suspendida
// alteraría el examen de Seguimiento del termómetro con marcas post-desenlace
if (!in_array($cot['estado'], ['enviada', 'vista'], true)) {
    echo json_encode(['ok' => false, 'error' => 'cerrada']); exit;
}

// Los 3 writes van en transacción: un fallo parcial dejaría el tap guardado
// pero sin proyección al Radar (o sin el contacto implícito) — estado divergente.
$auto_contacto = false;
try {
    DB::beginTransaction();

    // Declarar CUALQUIER compromiso implica que hablaron. El contacto implícito
    // se inserta ANTES del compromiso para que su created_at sea <= al del
    // compromiso (si quedara después, la regla de recencia degradaría el
    // compromiso recién declarado a historia).
    if ($area === 'compromiso') {
        $ult_con = DB::val(
            "SELECT estado FROM mesa_estados
             WHERE cotizacion_id = ? AND area = 'contacto' ORDER BY id DESC LIMIT 1", [$cot_id]
        );
        if ($ult_con !== 'hablamos') {
            DB::execute(
                "INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, razon, bucket_snapshot)
                 VALUES (?,?,?,'contacto','hablamos',NULL,?)",
                [$cot_id, Auth::id(), EMPRESA_ID, $cot['radar_bucket']]
            );
            $auto_contacto = true;
        }
    }

    // Historia insert-only
    DB::execute(
        "INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, razon, bucket_snapshot)
         VALUES (?,?,?,?,?,?,?)",
        [$cot_id, Auth::id(), EMPRESA_ID, $area, $estado, $razon, $cot['radar_bucket']]
    );

// Proyección compatible → radar_feedback (el examen del score no se toca)
// Proyección → radar_feedback A NOMBRE DEL ASESOR dueño de la cotización.
// La llave es (cotizacion, usuario): escribir como el vendedor garantiza UNA
// sola marca que siempre se sobreescribe (un descarte voltea el 👍 a 👎),
// sin importar si el tap lo dio el admin desde la mesa del asesor.
    $map = ['compromiso'=>'con_interes','decidiendo'=>'con_interes','objecion_precio'=>'con_interes',
            'pidio_cambios'=>'con_interes','descartada'=>'sin_interes',
            'con_interes'=>'con_interes','sin_interes'=>'sin_interes'];
    if (isset($map[$estado])) {
        // La HISTORIA de la marca también debe reflejar el cambio: si un tap de
        // postura/compromiso corrige el 👎 vigente, sin esta fila la historia
        // 'feedback' se queda con el sin_interes viejo y Recuperado contaría la
        // venta como recuperada aunque el asesor la corrigió (rama mf). Solo se
        // inserta cuando la marca CAMBIA (sin ruido por re-taps).
        if ($area !== 'feedback') {
            $fb_prev = DB::val(
                "SELECT estado FROM mesa_estados
                 WHERE cotizacion_id = ? AND area = 'feedback' ORDER BY id DESC LIMIT 1", [$cot_id]
            );
            if ($fb_prev !== $map[$estado]) {
                DB::execute(
                    "INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, razon, bucket_snapshot)
                     VALUES (?,?,?,'feedback',?,NULL,?)",
                    [$cot_id, Auth::id(), EMPRESA_ID, $map[$estado], $cot['radar_bucket']]
                );
            }
        }
        DB::execute(
            "INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo)
             VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE tipo=VALUES(tipo), updated_at=NOW()",
            [$cot_id, (int)$cot['vend'], EMPRESA_ID, $map[$estado]]
        );
    }

    DB::commit();
} catch (Throwable $e) {
    try { DB::rollback(); } catch (Throwable $e2) {}
    error_log('[Mesa tap] ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'guardar']); exit;
}

// ── Recalcular la sugerencia con la mezcla completa ──
$sugerencia = null;
try {
    // Última declaración por área (incluye la recién insertada)
    $decl = []; $nc = 0;
    foreach (DB::query(
        "SELECT m.area, m.estado, m.razon, m.created_at
         FROM mesa_estados m
         JOIN (SELECT area, MAX(id) AS mid FROM mesa_estados
               WHERE cotizacion_id = ? GROUP BY area) t ON t.mid = m.id",
        [$cot_id]
    ) as $r) {
        $decl[$r['area']] = ['estado' => $r['estado'], 'at' => $r['created_at'], 'razon' => $r['razon']];
    }
    foreach (DB::query(
        "SELECT estado FROM mesa_estados WHERE cotizacion_id = ? AND area = 'contacto'
           AND created_at >= NOW() - INTERVAL 30 DAY ORDER BY id",
        [$cot_id]
    ) as $r) {
        $nc = ($r['estado'] === 'hablamos') ? 0 : $nc + 1;
    }

    $act = DB::row(
        "SELECT SUM(created_at >= NOW() - INTERVAL 1 DAY) AS v24,
                SUM(created_at >= NOW() - INTERVAL 7 DAY) AS v7,
                COUNT(DISTINCT CASE WHEN created_at >= NOW() - INTERVAL 7 DAY THEN ip END) AS ips7
         FROM quote_sessions
         WHERE cotizacion_id = ? AND es_interno = 0
           AND NOT (COALESCE(visible_ms,0) < 200 AND COALESCE(scroll_max,0) < 35)",
        [$cot_id]
    ) ?: [];

    $ult_accion = DB::val(
        "SELECT MAX(created_at) FROM cotizacion_log
         WHERE cotizacion_id = ? AND usuario_id IS NOT NULL
           AND COALESCE(accion, evento) IN ('editada','enviada')", [$cot_id]
    );

    $t = DB::row(
        "SELECT AVG(total) AS avg_t, COUNT(*) AS n FROM ventas
         WHERE empresa_id = ? AND estado != 'cancelada' AND total > 0
           AND created_at >= NOW() - INTERVAL 180 DAY", [EMPRESA_ID]);
    if ((int)($t['n'] ?? 0) < 5) {
        $t = DB::row(
            "SELECT AVG(total) AS avg_t, COUNT(*) AS n FROM ventas
             WHERE empresa_id = ? AND estado != 'cancelada' AND total > 0", [EMPRESA_ID]);
    }
    $ticket = ((int)($t['n'] ?? 0) >= 5) ? (float)$t['avg_t'] : null;

    $arquetipo = 'muestra_chica';
    $us = DB::row(
        "SELECT * FROM usuario_score WHERE usuario_id = ? AND empresa_id = ?",
        [(int)$cot['vend'], EMPRESA_ID]);
    if ($us) $arquetipo = DiagnosticoTips::arquetipo($us);

    if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
    $ciclo = Radar::ciclo_venta(EMPRESA_ID);
    $p75   = (!empty($ciclo['auto']) && !empty($ciclo['p75'])) ? max(1, (int)$ciclo['p75']) : 30;

    $es_hot = $cot['radar_bucket'] !== null
        && in_array($cot['radar_bucket'], Mesa::HOT, true)
        && $cot['radar_bucket_at']
        && strtotime($cot['radar_bucket_at']) >= time() - 7 * 86400;

    $accion_post_cambios = false;
    if (($decl['postura']['estado'] ?? '') === 'pidio_cambios') {
        $accion_post_cambios = $ult_accion && strtotime($ult_accion) > strtotime($decl['postura']['at']);
    }

    // Overlays reales (revivida/milagro) — sin esto el tip del tap contradice
    // al de la recarga en las filas más urgentes de la mesa
    $revivida_now = false; $milagro_now = false;
    $descartada_ahora_pre = ($estado === 'descartada' || $estado === 'sin_interes');
    if (!$descartada_ahora_pre) {
        $fb_row = DB::row(
            "SELECT tipo, updated_at FROM radar_feedback WHERE cotizacion_id = ? AND usuario_id = ?",
            [$cot_id, (int)$cot['vend']]
        );
        if (($fb_row['tipo'] ?? '') === 'sin_interes') {
            $hot_in = "'" . implode("','", Mesa::HOT) . "'";
            $ult_hot = DB::val(
                "SELECT MAX(created_at) FROM bucket_transitions
                 WHERE cotizacion_id = ? AND bucket_nuevo IN ($hot_in)", [$cot_id]
            );
            $revivida_now = $ult_hot
                && strtotime($ult_hot) > strtotime($fb_row['updated_at'])
                && strtotime($ult_hot) >= time() - 7 * 86400;
        }
        $milagro_now = !$revivida_now && $es_hot && (int)$cot['edad'] > 2 * $p75;
    }

    // Si el tap acaba de descartar (botón "Descartar" o 👎 de Feedback Radar),
    // el tip instantáneo debe reflejar el descarte, no un consejo de trabajo.
    $descartada_ahora = $estado === 'descartada' || $estado === 'sin_interes';
    $cat_now = $descartada_ahora ? 'descartada_hoy' : 'trabajo';
    $razon_now = ($decl['postura']['estado'] ?? '') === 'descartada'
        ? ($decl['postura']['razon'] ?? null) : null;

    $sen = !empty($cot['radar_senales']) ? (json_decode($cot['radar_senales'], true) ?: []) : [];
    $sugerencia = MesaSugerencias::sugerir([
        'cot_id' => $cot_id,
        'total' => (float)$cot['total'], 'edad' => (int)$cot['edad'], 'cat' => $cat_now,
        'bucket' => $cot['radar_bucket'], 'es_hot' => $es_hot,
        'pc_source' => $sen['pc_source'] ?? null,
        'momentum'  => $sen['momentum'] ?? null,
        'fit_pct'   => (int)($sen['fit_pct'] ?? 0),
        'visitas' => (int)$cot['visitas'], 'dias_sin_vista' => (int)$cot['dias_sin_vista'],
        'ultima_vista_at' => $cot['ultima_vista_at'],
        'revivida' => $revivida_now, 'milagro' => $milagro_now,
        'contacto' => $decl['contacto'] ?? null,
        'compromiso' => $decl['compromiso'] ?? null,
        'postura_decl' => $decl['postura'] ?? null,
        'razon_descarte' => $razon_now,
        'intentos_nc' => $nc,
        'vistas_24h' => (int)($act['v24'] ?? 0),
        'vistas_7d'  => (int)($act['v7'] ?? 0),
        'ips_7d'     => (int)($act['ips7'] ?? 0),
        'accion_post_cambios' => $accion_post_cambios,
        'p75' => $p75, 'mediana' => (int)($ciclo['mediana'] ?? $p75),
        'ticket_empresa' => $ticket,
        'arquetipo' => $arquetipo,
    ]);
} catch (Throwable $e) {
    // El tap ya se guardó — no romper la respuesta por la sugerencia
    error_log('[Mesa sugerencia] ' . $e->getMessage());
}

echo json_encode(['ok' => true, 'estado' => $estado, 'sugerencia' => $sugerencia, 'auto_contacto' => $auto_contacto]);
