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
    'compromiso' => ['compromiso','nos_citamos','propuse_no_quiso','sin_compromiso'],
    'postura'    => ['decidiendo','objecion_precio','pidio_cambios','en_el_aire','descartada'],
    'feedback'   => ['con_interes','sin_interes','sin_info'],   // 👍/👎/📵 homologado con el Radar
];
$VALIDOS = $AREAS[$area] ?? [];
$RAZONES = ['precio','competencia','despues','no_responde','no_comprador','otro'];
if (!$cot_id || !in_array($estado, $VALIDOS, true)) { echo json_encode(['ok'=>false,'error'=>'datos']); exit; }
if ($estado === 'descartada' && !in_array($razon, $RAZONES, true)) { echo json_encode(['ok'=>false,'error'=>'razon']); exit; }
if ($estado !== 'descartada') $razon = null;

$cot = DB::row(
    "SELECT id, estado, suspendida, total, visitas, radar_bucket, radar_bucket_at, radar_senales, ultima_vista_at, created_at,
            DATEDIFF(NOW(), created_at) AS edad,
            DATEDIFF(NOW(), COALESCE(ultima_vista_at, created_at)) AS dias_sin_vista,
            COALESCE(vendedor_id, usuario_id) AS vend
     FROM cotizaciones WHERE id=? AND empresa_id=?", [$cot_id, EMPRESA_ID]);
if (!$cot) { echo json_encode(['ok'=>false,'error'=>'no_encontrada']); exit; }
if ((int)$cot['vend'] !== Auth::id() && !Auth::es_admin()) { echo json_encode(['ok'=>false,'error'=>'permiso']); exit; }
// El flag mesa_activa gobierna también el WRITE path: un asesor de empresa
// con la mesa apagada no debe poder sembrar declaraciones por POST directo
if (!Auth::es_admin()) {
    try {
        $mesa_flag = (int)DB::val("SELECT mesa_activa FROM empresas WHERE id = ?", [EMPRESA_ID]);
    } catch (Throwable $e) { $mesa_flag = 0; } // columna sin migrar = apagada
    if ($mesa_flag < 1) { echo json_encode(['ok' => false, 'error' => 'mesa_off']); exit; }
}
// Solo cotizaciones vivas: declarar/feedbackear una aceptada/rechazada/suspendida
// alteraría el examen de Seguimiento del termómetro con marcas post-desenlace
// (suspendida es COLUMNA, no estado — el guard de estado no la cubre)
if (!in_array($cot['estado'], ['enviada', 'vista'], true) || (int)$cot['suspendida'] === 1) {
    echo json_encode(['ok' => false, 'error' => 'cerrada']); exit;
}

// 📵 Sin info SOLO con contacto vigente 'no_contesta': si hablaste con el
// cliente, tienes con qué juzgar (👍👎). Sin el candado, 📵 sería la vía
// floja para farmear cobertura sin comprometerse nunca a un juicio.
if ($estado === 'sin_info') {
    $ult_con = null;
    try {
        $ult_con = DB::val(
            "SELECT estado FROM mesa_estados
             WHERE cotizacion_id = ? AND empresa_id = ? AND area = 'contacto'
             ORDER BY id DESC LIMIT 1", [$cot_id, EMPRESA_ID]);
    } catch (Throwable $e) {}
    if ($ult_con !== 'no_contesta') {
        echo json_encode(['ok' => false, 'error' => 'sin_info_gate']); exit;
    }
}

// Rate-limit por usuario: cada tap es una fila insert-only que cuenta como
// "toque" en el reporte del equipo — sin tope, un script inflaría las métricas
// con las que el dueño evalúa (mismo patrón que api/soporte.php)
try {
    $taps_min = (int)DB::val(
        "SELECT COUNT(*) FROM mesa_estados
         WHERE usuario_id = ? AND created_at >= NOW() - INTERVAL 1 MINUTE", [Auth::id()]
    );
    if ($taps_min >= 30) { echo json_encode(['ok' => false, 'error' => 'rate']); exit; }
} catch (Throwable $e) {} // tabla aún no migrada

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
             WHERE cotizacion_id = ? AND empresa_id = ? AND area = 'contacto'
             ORDER BY id DESC LIMIT 1", [$cot_id, EMPRESA_ID]
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

    // La manita (radar_feedback) es el JUICIO INDEPENDIENTE del asesor
    // (decisión CEO 15-jul): NINGÚN tap de compromiso/postura la escribe ni
    // la corrige por él — solo los pulgares 👍/👎/📵 (área 'feedback'). El
    // pill Descartar sigue sacando la fila de la mesa por la POSTURA misma
    // (doble fuente en armar); para que la cobertura la califique hace falta
    // TAMBIÉN la manita. Se escribe a nombre del ASESOR dueño: la llave es
    // (cotizacion, usuario) — una sola marca aunque quien tapee sea el admin.
    if ($area === 'feedback') {
        DB::execute(
            "INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo)
             VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE tipo=VALUES(tipo), updated_at=NOW()",
            [$cot_id, (int)$cot['vend'], EMPRESA_ID, $estado]
        );
    }

    DB::commit();
} catch (Throwable $e) {
    try { DB::rollback(); } catch (Throwable $e2) {}
    error_log('[Mesa tap] ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'guardar']); exit;
}

// Tapear ES explorar la señal (el cajón muestra bucket + sugerencia — información
// equivalente o superior al ❓ del Radar). Se registra a nombre del ASESOR dueño
// (misma identidad que el feedback): alimenta calientes_exploradas, que usan la
// validación de diligencia, el arquetipo 'teatro' y la frase "las ignoras" de
// DiagnosticoTips. Sin esto, migrar la rutina del Radar a la mesa insultaría
// al asesor por usar la herramienta nueva. El consumidor cuenta DISTINCT ref_id
// — duplicados inofensivos. Fuera de la transacción: si falla, el tap ya quedó.
try {
    ActividadScore::registrar((int)$cot['vend'], EMPRESA_ID, 'radar_why_click', $cot_id);
} catch (Throwable $e) {}

// ── Recalcular la sugerencia con la mezcla completa ──
$sugerencia = null;
$fb_hint = false;
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

    // MISMA regla que Mesa::armar (bucket_at solo cambia con el bucket:
    // calor sostenido con visitas recientes también es hot reciente)
    $es_hot_bucket = $cot['radar_bucket'] !== null && in_array($cot['radar_bucket'], Mesa::HOT, true);
    $es_hot = $es_hot_bucket && (
        ($cot['radar_bucket_at'] && strtotime($cot['radar_bucket_at']) >= time() - 7 * 86400)
        || (int)($act['v7'] ?? 0) > 0
    );

    $accion_post_cambios = false; $accion_post_cambios_at = null;
    if (($decl['postura']['estado'] ?? '') === 'pidio_cambios') {
        if ($ult_accion && strtotime($ult_accion) > strtotime($decl['postura']['at'])) {
            $accion_post_cambios = true;
            $accion_post_cambios_at = $ult_accion;
        }
    }

    // Overlays y CATEGORÍA con las MISMAS reglas que Mesa::armar — si el tip
    // del tap usa reglas distintas, contradice al de la recarga en las filas
    // más urgentes (revividas, milagros, interés muriendo, último tramo).
    $fb_row = DB::row(
        "SELECT tipo, updated_at FROM radar_feedback WHERE cotizacion_id = ? AND usuario_id = ?",
        [$cot_id, (int)$cot['vend']]
    );
    // Recordatorio (NO auto-corrección): tap positivo con 👎 vigente — desde
    // que los taps ya no proyectan la manita, nadie la cambia por el asesor;
    // se le avisa para que decida él si su juicio cambió.
    $fb_hint = in_array($estado, ['compromiso','nos_citamos','decidiendo','objecion_precio','pidio_cambios'], true)
        && (($fb_row['tipo'] ?? '') === 'sin_interes');
    $revivida_now = false; $milagro_now = false;
    $descartada_ahora = ($estado === 'descartada' || $estado === 'sin_interes');
    // "Hoy" con el reloj de la BD (igual que armar): si los timezone de PHP
    // y MySQL difieren, strtotime('today') clasifica distinto que la recarga
    $hoy_db = (string)DB::val("SELECT CURDATE()");
    // descartada_hoy también si el 👎 vigente es de HOY (fila ya en esa sección
    // recibiendo otro tap): igual que armar (fb updated_at >= hoy)
    $descartada_hoy_fb = ($fb_row['tipo'] ?? '') === 'sin_interes'
        && !empty($fb_row['updated_at'])
        && substr($fb_row['updated_at'], 0, 10) === $hoy_db;
    // Postura vigente 'descartada' (2ª fuente, igual que armar) — anulada por
    // un juicio positivo POSTERIOR (feedback con_interes de historia o rf)
    $post_desc_now = false; $post_desc_at = 0;
    if (($decl['postura']['estado'] ?? '') === 'descartada') {
        $post_desc_now = true;
        $post_desc_at  = strtotime($decl['postura']['at']);
        if (($decl['feedback']['estado'] ?? '') === 'con_interes'
            && strtotime($decl['feedback']['at']) > $post_desc_at) $post_desc_now = false;
        if (($fb_row['tipo'] ?? '') === 'con_interes'
            && strtotime($fb_row['updated_at'] ?? '') > $post_desc_at) $post_desc_now = false;
    }
    $descartada_hoy_pos = $post_desc_now && substr($decl['postura']['at'], 0, 10) === $hoy_db;
    $descartada_vig = ($fb_row['tipo'] ?? '') === 'sin_interes' || $post_desc_now;
    if (!$descartada_ahora && $descartada_vig) {
        // Revivida = VISTA REAL posterior al ÚLTIMO juicio (misma regla que
        // armar) — las transiciones de bucket solas son flapping del Radar
        $desc_at_now = max(
            ($fb_row['tipo'] ?? '') === 'sin_interes' ? strtotime($fb_row['updated_at'] ?? '') : 0,
            $post_desc_now ? $post_desc_at : 0
        );
        if ($desc_at_now && $cot['ultima_vista_at']
            && strtotime($cot['ultima_vista_at']) > $desc_at_now
            && strtotime($cot['ultima_vista_at']) >= time() - 7 * 86400) {
            $revivida_now = true;
        }
    }
    if (!$descartada_ahora) {
        $milagro_now = !$revivida_now && !$descartada_vig && $es_hot && (int)$cot['edad'] > 2 * $p75;
    }

    // Cadena de categorías de Mesa::armar, replicada
    $postura_vacia = empty($decl['postura']);
    $dormida = (int)$cot['visitas'] > 0 && (int)$cot['dias_sin_vista'] >= 7;
    if ($descartada_ahora || (!$revivida_now && ($descartada_hoy_fb || $descartada_hoy_pos))) {
        $cat_now = 'descartada_hoy';
    } elseif ($revivida_now) {
        $cat_now = 'revivida';
    } elseif ($milagro_now) {
        $cat_now = 'milagro';
    } elseif (($fb_row['tipo'] ?? '') === 'con_interes' && $postura_vacia
              && ($dormida || $cot['radar_bucket'] === 'enfriandose')) {
        $cat_now = 'interes_muriendo';
    } elseif (($fb_row['tipo'] ?? '') === 'con_interes' && $postura_vacia && (int)$cot['edad'] > $p75) {
        $cat_now = 'ultimo_tramo';
    } else {
        $cat_now = 'trabajo'; // tras un tap siempre hay declaración — sin_postura no aplica
    }
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
        'accion_post_cambios_at' => $accion_post_cambios_at,
        'p75' => $p75, 'mediana' => (int)($ciclo['mediana'] ?? $p75),
        'ticket_empresa' => $ticket,
        'arquetipo' => $arquetipo,
    ]);
} catch (Throwable $e) {
    // El tap ya se guardó — no romper la respuesta por la sugerencia
    error_log('[Mesa sugerencia] ' . $e->getMessage());
}

echo json_encode(['ok' => true, 'estado' => $estado, 'sugerencia' => $sugerencia, 'auto_contacto' => $auto_contacto, 'fb_hint' => $fb_hint]);
