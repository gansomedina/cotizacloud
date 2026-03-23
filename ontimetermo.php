<?php
// ============================================================
//  OnTime — ontimetermo.php  v1.0
//  APC: Algoritmo de Productividad Comercial (adaptado de CotizaCloud)
//
//  3 DIMENSIONES:
//    Activación   (20%) — ¿las cotizaciones llegan al cliente?
//    Seguimiento  (35%) — ¿usa el radar? ¿reacciona a señales?
//    Conversión   (45%) — close rate + calidad de cierres + velocidad
//
//  Contexto: 2 usuarios (admin + vendedor), WordPress + Sliced Invoices
// ============================================================
require_once __DIR__ . '/wp-load.php';

$current_user = wp_get_current_user();
$login = strtolower($current_user->user_login ?? '');

if (!is_user_logged_in() || !(current_user_can('manage_options') || $login === 'ontime')) {
    status_header(403);
    exit('No autorizado');
}

date_default_timezone_set(wp_timezone_string());
$now = time();
global $wpdb;

// ============================================================
//  CONFIG
// ============================================================
$PERIODO = 30; // días rolling
$GRACIA_DIAS = 7;

// Usuario a evaluar: admin ve al vendedor, vendedor se ve a sí mismo
$is_admin = current_user_can('manage_options');

// Vendedor = usuario 815 (ontime). Hardcoded porque solo hay 1 vendedor.
$vendedor_user = get_user_by('id', 815);
if (!$vendedor_user) {
    $vendedor_user = get_user_by('login', 'ontime');
}
if (!$vendedor_user) {
    $vendedor_user = $current_user; // fallback último recurso
}

$target_user_id = $is_admin ? (int)$vendedor_user->ID : (int)$current_user->ID;
$target_login = $is_admin ? $vendedor_user->user_login : $current_user->user_login;

// Tablas
$usage_table = $wpdb->prefix . 'radar_usage_events';
$events_table = $wpdb->prefix . 'sliced_quote_events';
$transitions_table = $wpdb->prefix . 'radar_bucket_transitions';

$table_exists = function($t) use ($wpdb) {
    return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $t)) === $t;
};

$has_usage = $table_exists($usage_table);
$has_events = $table_exists($events_table);
$has_transitions = $table_exists($transitions_table);

// ============================================================
//  PERÍODO DE GRACIA
// ============================================================
$primer_uso = $has_usage ? $wpdb->get_var($wpdb->prepare(
    "SELECT MIN(created_ts) FROM {$usage_table} WHERE user_id=%d", $target_user_id
)) : null;

$dias_en_plataforma = $primer_uso
    ? (int)ceil(($now - (int)$primer_uso) / 86400)
    : 0;

$en_gracia = ($dias_en_plataforma < $GRACIA_DIAS);

// ============================================================
//  BENCHMARKS DE LA EMPRESA (auto-calculados)
// ============================================================
$periodo_start = date('Y-m-d', $now - $PERIODO * 86400);

// Total cotizaciones (no borrador)
$total_cots = (int)$wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts}
     WHERE post_type='sliced_quote'
     AND post_status IN ('publish','draft','private')"
);

// Cotizaciones del período
$cots_periodo = (int)$wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts}
     WHERE post_type='sliced_quote'
     AND post_status IN ('publish','draft','private')
     AND post_date >= %s",
    $periodo_start
));

// Ventas (accepted) del período
$accepted_term = get_term_by('slug', 'accepted', 'quote_status');
$ventas_periodo = 0;
$ventas_total = 0;
if ($accepted_term) {
    $ventas_periodo = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT tr.object_id)
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tr.term_taxonomy_id = %d
         AND p.post_type = 'sliced_quote'
         AND p.post_date >= %s",
        $accepted_term->term_taxonomy_id,
        $periodo_start
    ));
    $ventas_total = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT tr.object_id)
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tr.term_taxonomy_id = %d
         AND p.post_type = 'sliced_quote'",
        $accepted_term->term_taxonomy_id
    ));
}

$bench_close_rate = $total_cots > 0 ? $ventas_total / $total_cots : 0.10;
$bench_apertura = 0.60; // % cotizaciones que el cliente abrió (default)

// Cotizaciones con al menos 1 vista
$cots_con_vista = (int)$wpdb->get_var(
    "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sliced_log'
     WHERE p.post_type = 'sliced_quote'
     AND p.post_status IN ('publish','draft','private')"
);
if ($total_cots > 0) {
    $bench_apertura = max(0.10, $cots_con_vista / $total_cots);
}

// Radar sessions por semana (benchmark)
$bench_radar_weekly = 3.0; // default: 3 sesiones/semana
if ($has_usage) {
    $total_radar = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$usage_table}
         WHERE event_type IN ('radar_open','radar_refresh')
         AND created_ts >= %d",
        $now - $PERIODO * 86400
    ));
    $semanas = max($PERIODO / 7, 1);
    $bench_radar_weekly = max(1.0, $total_radar / $semanas);
}

// ============================================================
//  SEÑALES CRUDAS DEL VENDEDOR
// ============================================================

// En OnTime con 1 vendedor, todas las cotizaciones son suyas
$cot_asignadas = $cots_periodo;

// Cotizaciones vistas por el cliente
// _sliced_log existe en casi todas (Sliced lo crea al guardar), así que no sirve como proxy.
// Usamos: quote_events (JS tracking) + accepted (si fue aceptada, fue vista).
// Para quotes sin events ni aceptación, parseamos _sliced_log buscando 'quote_viewed' real.
$cot_vistas_events = 0;
if ($has_events) {
    $cot_vistas_events = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT e.quote_id) FROM {$events_table} e
         INNER JOIN {$wpdb->posts} p ON p.ID = e.quote_id
         WHERE p.post_type = 'sliced_quote' AND p.post_date >= %s",
        $periodo_start
    ));
}

// Aceptadas del período = vistas por definición
$cot_vistas_accepted = $ventas_periodo;

// Fallback: contar quotes con _sliced_log que contengan 'quote_viewed'
// (serialized log, buscamos el string literal como proxy rápido)
$cot_vistas_log = (int)$wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sliced_log'
     WHERE p.post_type = 'sliced_quote'
     AND p.post_status IN ('publish','draft','private')
     AND p.post_date >= %s
     AND pm.meta_value LIKE %s",
    $periodo_start,
    '%quote_viewed%'
));

// Total vistas = max de las fuentes (hay overlap, no sumar)
$cot_vistas = max($cot_vistas_events, $cot_vistas_log, $cot_vistas_accepted);

// IDs de cotizaciones aceptadas (para queries posteriores)
$accepted_ids = [];
if ($accepted_term) {
    $accepted_posts = get_posts([
        'post_type'      => 'sliced_quote',
        'post_status'    => ['publish','draft','private'],
        'posts_per_page' => 8000,
        'fields'         => 'ids',
        'tax_query'      => [[
            'taxonomy' => 'quote_status',
            'field'    => 'slug',
            'terms'    => ['accepted'],
        ]],
    ]);
    foreach ($accepted_posts as $aqid) {
        $accepted_ids[(int)$aqid] = true;
    }
}

// Cotizaciones dormidas escalonadas: enviadas sin vista, >7d / >14d / >21d
// (como CotizaCloud: penalización creciente por antigüedad)
$dormidas_7d = 0;
$dormidas_14d = 0;
$dormidas_21d = 0;

$sin_vista = $wpdb->get_results($wpdb->prepare(
    "SELECT p.ID, p.post_date FROM {$wpdb->posts} p
     WHERE p.post_type = 'sliced_quote'
     AND p.post_status IN ('publish','draft','private')
     AND p.post_date >= %s
     AND NOT EXISTS (
         SELECT 1 FROM {$wpdb->postmeta} pm
         WHERE pm.post_id = p.ID AND pm.meta_key = '_sliced_log'
         AND pm.meta_value IS NOT NULL AND pm.meta_value != ''
     )
     " . ($has_events ? "AND NOT EXISTS (SELECT 1 FROM {$events_table} e WHERE e.quote_id = p.ID)" : "") . "
     AND p.ID NOT IN (
         SELECT tr.object_id FROM {$wpdb->term_relationships} tr
         WHERE tr.term_taxonomy_id IN (
             SELECT tt.term_taxonomy_id FROM {$wpdb->term_taxonomy} tt
             INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
             WHERE tt.taxonomy = 'quote_status' AND t.slug IN ('accepted','declined','cancelled')
         )
     )",
    $periodo_start
), ARRAY_A);

foreach ($sin_vista as $sv) {
    $age = ($now - strtotime($sv['post_date'])) / 86400;
    if ($age >= 21) $dormidas_21d++;
    elseif ($age >= 14) $dormidas_14d++;
    elseif ($age >= 7) $dormidas_7d++;
}

// Cierres del período
$cierres_total = $ventas_periodo;

// Carga activa (cotizaciones no cerradas, no rechazadas)
$carga_activa = (int)$wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts} p
     WHERE p.post_type = 'sliced_quote'
     AND p.post_status IN ('publish','draft','private')
     AND p.post_date >= %s
     AND p.ID NOT IN (
         SELECT tr.object_id FROM {$wpdb->term_relationships} tr
         WHERE tr.term_taxonomy_id IN (
             SELECT tt.term_taxonomy_id FROM {$wpdb->term_taxonomy} tt
             INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
             WHERE tt.taxonomy = 'quote_status' AND t.slug IN ('accepted','declined','cancelled')
         )
     )",
    $periodo_start
));

// Vencidas sin acción — Sliced Invoices no tiene fecha de vencimiento estándar
// OnTime no usa esta métrica (0 = sin penalización)
$vencidas_sin_accion = 0;

// Sesiones de radar del vendedor
$radar_sessions = 0;
$dias_activos = 0;
if ($has_usage) {
    $radar_sessions = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$usage_table}
         WHERE user_id=%d AND event_type IN ('radar_open','radar_refresh')
         AND created_ts >= %d",
        $target_user_id, $now - $PERIODO * 86400
    ));
    $dias_activos = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT DATE(FROM_UNIXTIME(created_ts))) FROM {$usage_table}
         WHERE user_id=%d AND event_type IN ('radar_open','radar_refresh','radar_ping','radar_scroll')
         AND created_ts >= %d",
        $target_user_id, $now - $PERIODO * 86400
    ));
}

// Señales calientes ignoradas
$senales_ignoradas = 0;
if ($has_events) {
    // Cotizaciones con 3+ sesiones en últimos 7 días = cliente interesado
    $cot_calientes = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT quote_id) FROM {$events_table}
         WHERE ts_unix >= %d
         GROUP BY quote_id HAVING COUNT(DISTINCT session_id) >= 3",
        $now - 7 * 86400
    ));

    if ($cot_calientes > 0 && $has_usage) {
        $ultimo_radar_ts = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT MAX(created_ts) FROM {$usage_table}
             WHERE user_id=%d AND event_type IN ('radar_open','radar_refresh')",
            $target_user_id
        ));
        $horas_desde_radar = $ultimo_radar_ts > 0 ? ($now - $ultimo_radar_ts) / 3600.0 : 999;

        if ($horas_desde_radar <= 24) $senales_ignoradas = 0;
        elseif ($horas_desde_radar <= 48) $senales_ignoradas = (int)ceil($cot_calientes * 0.50);
        elseif ($horas_desde_radar <= 72) $senales_ignoradas = (int)ceil($cot_calientes * 0.75);
        else $senales_ignoradas = $cot_calientes;
    }
}

// Clasificación de buckets por temperatura
$bucket_temp = [
    'enfriandose' => 'frio', 'no_abierta' => 'frio', 'hesitacion' => 'frio',
    'sobre_analisis' => 'tibio', 'comparando' => 'tibio', 'regreso' => 'tibio',
    'revivio' => 'tibio', 're_enganche' => 'tibio', 'revision_profunda' => 'tibio',
    'vistas_multiples' => 'tibio', 're_enganche_caliente' => 'caliente',
    'multi_persona' => 'caliente', 'alto_importe' => 'caliente',
    'decision_activa' => 'caliente', 'prediccion_alta' => 'caliente',
    'validando_precio' => 'caliente', 'inminente' => 'caliente',
    'onfire' => 'caliente', 'probable_cierre' => 'caliente',
    'probable_cierre_base' => 'caliente', 'activo48' => 'tibio',
];
$temp_order = ['frio' => 0, 'tibio' => 1, 'caliente' => 2];

// Transiciones de bucket — con detección de reacción del vendedor
$transiciones_up = 0;
$transiciones_down = 0;
$transiciones_con_reaccion = 0;
if ($has_transitions) {
    // Traer transiciones con timestamp para cruzar con uso del radar
    $trans = $wpdb->get_results($wpdb->prepare(
        "SELECT bucket_anterior, bucket_nuevo, created_ts FROM {$transitions_table}
         WHERE created_ts >= %d",
        $now - $PERIODO * 86400
    ), ARRAY_A);

    foreach ($trans as $t) {
        $ta = $bucket_temp[$t['bucket_anterior']] ?? null;
        $tn = $bucket_temp[$t['bucket_nuevo']] ?? null;
        if (!$ta || !$tn) continue;

        if (($temp_order[$tn] ?? 0) > ($temp_order[$ta] ?? 0)) {
            $transiciones_up++;
            // ¿El vendedor revisó el radar en las 48h PREVIAS a la transición?
            if ($has_usage) {
                $reacciono = (int)$wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$usage_table}
                     WHERE user_id=%d AND event_type IN ('radar_open','radar_refresh')
                     AND created_ts BETWEEN %d AND %d LIMIT 1",
                    $target_user_id,
                    (int)$t['created_ts'] - 48 * 3600,
                    (int)$t['created_ts']
                ));
                if ($reacciono > 0) $transiciones_con_reaccion++;
            }
        } elseif (($temp_order[$tn] ?? 0) < ($temp_order[$ta] ?? 0)) {
            $transiciones_down++;
        }
    }
}

// Buckets estancados: cotizaciones con bucket tibio/caliente pero sin movimiento >14 días
$buckets_estancados = 0;
if ($has_transitions) {
    // Último bucket de cada cotización activa
    $last_buckets = $wpdb->get_results($wpdb->prepare(
        "SELECT bt.quote_id, bt.bucket_nuevo, bt.created_ts
         FROM {$transitions_table} bt
         INNER JOIN (
             SELECT quote_id, MAX(created_ts) AS max_ts
             FROM {$transitions_table}
             GROUP BY quote_id
         ) latest ON bt.quote_id = latest.quote_id AND bt.created_ts = latest.max_ts
         WHERE bt.created_ts < %d",
        $now - 14 * 86400
    ), ARRAY_A);

    foreach ($last_buckets as $lb) {
        $temp = $bucket_temp[$lb['bucket_nuevo']] ?? null;
        if (!$temp || $temp === 'frio') continue;
        // Solo contar si la cotización sigue activa (no aceptada/rechazada)
        if (!isset($accepted_ids[(int)$lb['quote_id']])) {
            $buckets_estancados++;
        }
    }
}

// Tasa de reacción: de cotizaciones con actividad del cliente en 7 días,
// ¿en cuántas el vendedor revisó el radar dentro de 48h?
$cot_con_actividad_cliente = 0;
$cot_con_reaccion_vendor = 0;
$tasa_reaccion = 0.3; // neutro por defecto

if ($has_events && $has_usage) {
    // Cotizaciones con actividad del cliente en últimos 7 días
    $cots_activas_cliente = $wpdb->get_results($wpdb->prepare(
        "SELECT quote_id, MAX(ts_unix) AS last_event_ts
         FROM {$events_table}
         WHERE ts_unix >= %d
         GROUP BY quote_id",
        $now - 7 * 86400
    ), ARRAY_A);

    $cot_con_actividad_cliente = count($cots_activas_cliente);

    if ($cot_con_actividad_cliente > 0) {
        foreach ($cots_activas_cliente as $ca) {
            // ¿El vendedor revisó radar/cotización dentro de 48h después de la actividad?
            $reacciono = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$usage_table}
                 WHERE user_id=%d AND event_type IN ('radar_open','radar_refresh')
                 AND created_ts BETWEEN %d AND %d LIMIT 1",
                $target_user_id,
                (int)$ca['last_event_ts'],
                (int)$ca['last_event_ts'] + 48 * 3600
            ));
            if ($reacciono > 0) $cot_con_reaccion_vendor++;
        }
        $tasa_reaccion = $cot_con_reaccion_vendor / $cot_con_actividad_cliente;
    }
} elseif ($carga_activa === 0) {
    $tasa_reaccion = 0.0;
}

// Consultas (visitas a cotizaciones individuales desde el radar)
$consultas = 0;
if ($has_usage) {
    $consultas = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$usage_table}
         WHERE user_id=%d AND event_type IN ('radar_scroll','radar_ping')
         AND created_ts >= %d",
        $target_user_id, $now - $PERIODO * 86400
    ));
}

// ============================================================
//  SIGMOID HELPER
// ============================================================
function apc_sigmoid(float $x, float $midpoint, float $steepness): float {
    return 1.0 / (1.0 + exp(-$steepness * ($x - $midpoint)));
}

// ============================================================
//  DIMENSIÓN 1: ACTIVACIÓN (20%)
//  "¿Las cotizaciones llegan al cliente?"
//  Tasa de apertura + penalización escalonada por dormidas.
//  Sigmoid con midpoint = benchmark empresa (auto-ajustable).
// ============================================================
$asignadas_safe = max($cot_asignadas, 1);
$tasa_apertura = $cot_vistas / $asignadas_safe;

// Penalización escalonada por dormidas (más viejas = peor)
// dormidas_solo_7  = las que tienen entre 7-14 días sin abrir
// dormidas_solo_14 = las que tienen entre 14-21 días
// dormidas_solo_21 = las que tienen 21+ días
$dormidas_solo_7  = $dormidas_7d;   // ya son solo 7-14
$dormidas_solo_14 = $dormidas_14d;  // ya son solo 14-21
$dormidas_solo_21 = $dormidas_21d;  // ya son 21+

$pen_dormidas = 0.0;
if ($asignadas_safe > 0) {
    $pen_dormidas = (
        ($dormidas_solo_7  * 6) +
        ($dormidas_solo_14 * 10) +
        ($dormidas_solo_21 * 15)
    ) / ($asignadas_safe * 15);
}
$pen_dormidas = min($pen_dormidas, 1.0);

$s_activacion = apc_sigmoid($tasa_apertura, $bench_apertura, 2.0 / max($bench_apertura, 0.1))
                - ($pen_dormidas * 0.4);
$s_activacion = max(0.0, min(1.0, $s_activacion));

// ============================================================
//  DIMENSIÓN 2: SEGUIMIENTO (35%)
//  "¿Usa el radar para dar seguimiento?"
//  Pesos internos: radar 30% + consultas 15% + reacción 35% + transiciones 20%
//  Penalizaciones: buckets estancados, señales ignoradas, transiciones down
// ============================================================
$semanas = max($PERIODO / 7, 1);
$radar_por_semana = $radar_sessions / $semanas;
$consultas_por_semana = $consultas / $semanas;

$s_radar = apc_sigmoid($radar_por_semana, $bench_radar_weekly, 2.0 / max($bench_radar_weekly, 0.1));
$s_consultas = apc_sigmoid($consultas_por_semana, $bench_radar_weekly * 2.5, 0.5);

// Bonus solo por transiciones donde el vendedor REACCIONÓ (revisó radar antes)
$bonus_transiciones = min($transiciones_con_reaccion * 0.10, 0.3);

// Penalizaciones
$pen_buckets = min($buckets_estancados * 0.06, 0.3);
$pen_senales = min($senales_ignoradas * 0.10, 0.4);
$pen_trans_down = min($transiciones_down * 0.05, 0.2);

// tasa_reaccion entra con 35% del peso de seguimiento
$pen_seguimiento = min($pen_buckets + $pen_senales + $pen_trans_down, 0.60); // cap 0.60
$s_seguimiento = ($s_radar * 0.30 + $s_consultas * 0.15 + $tasa_reaccion * 0.35 + $bonus_transiciones * 0.20)
                 - $pen_seguimiento;
$s_seguimiento = max(0.0, min(1.0, $s_seguimiento));

// ============================================================
//  DIMENSIÓN 3: CONVERSIÓN (45%)
//  "¿Cierra ventas? Esto es lo que importa."
//  Pesos internos: close_rate 40% + quality 35% + velocidad 25%
//  Penalizaciones: vencidas, zona muerta, volumen sin resultado
//  Consistencia semanal ajusta el resultado final.
// ============================================================

// ── Multiplicadores por bucket para cierres (invertido: frío = más mérito) ──
$cierre_mult = [
    'enfriandose'     => 2.0, 'no_abierta'      => 2.0, 'hesitacion'      => 1.8,
    'sobre_analisis'  => 1.6, 'comparando'      => 1.5, 'regreso'         => 1.4,
    'revivio'         => 1.3, 're_enganche'     => 1.3, 'revision_profunda' => 1.2,
    'vistas_multiples' => 1.2, 're_enganche_caliente' => 1.1, 'multi_persona' => 1.1,
    'alto_importe'    => 1.0, 'decision_activa' => 1.0, 'prediccion_alta' => 1.0,
    'validando_precio' => 0.9, 'inminente'      => 0.9, 'onfire'          => 0.8,
    'probable_cierre'  => 0.8, 'probable_cierre_base' => 0.8, 'activo48' => 1.0,
];
$descuento_factor = 0.6; // cierre con descuento vale 60%

// Tasa de cierre sobre cotizaciones vistas (justo)
$cot_vistas_safe = max($cot_vistas, 1);
$tasa_cierre = $cierres_total / $cot_vistas_safe;

// ── Calidad de cierre: puntos ponderados por bucket ──
// Obtener último bucket de cada cotización aceptada en el período
$cierres_con_bucket = [];
$cierres_bucket_count = 0;
$cierres_sin_dto = 0;

if ($accepted_term && $has_transitions) {
    // IDs de cotizaciones aceptadas en el período
    $accepted_periodo = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT tr.object_id AS qid
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tr.term_taxonomy_id = %d
         AND p.post_type = 'sliced_quote'
         AND p.post_date >= %s",
        $accepted_term->term_taxonomy_id,
        $periodo_start
    ), ARRAY_A);

    foreach ($accepted_periodo as $ap) {
        $qid = (int)$ap['qid'];
        // Último bucket antes del cierre
        $last_bucket = $wpdb->get_var($wpdb->prepare(
            "SELECT bucket_nuevo FROM {$transitions_table}
             WHERE quote_id = %d ORDER BY created_ts DESC LIMIT 1",
            $qid
        ));

        // Verificar si tuvo descuento (meta de Sliced Invoices)
        $tiene_dto = false; // OnTime no tiene cupones automáticos por ahora

        $cierres_con_bucket[] = [
            'bucket' => $last_bucket,
            'tiene_dto' => $tiene_dto,
        ];
        if ($last_bucket !== null) $cierres_bucket_count++;
        if (!$tiene_dto) $cierres_sin_dto++;
    }
}

// Calcular calidad: multiplicador promedio normalizado
$base_cierre = 10.0;
$puntos_cierre = 0.0;
$cierres_con_radar = 0;
$puntos_con_radar = 0.0;

foreach ($cierres_con_bucket as $cc) {
    $mult_bucket = $cierre_mult[$cc['bucket']] ?? 1.0;
    $mult_dto = $cc['tiene_dto'] ? $descuento_factor : 1.0;
    $puntos_cierre += $base_cierre * $mult_bucket * $mult_dto;

    if ($cc['bucket'] !== null) {
        $cierres_con_radar++;
        $puntos_con_radar += $base_cierre * $mult_bucket * $mult_dto;
    }
}

// Quality: avg_mult/max. Sin bucket = neutro (0.50). Frío = alto. Caliente = bajo.
if ($cierres_con_radar > 0) {
    $avg_mult = $puntos_con_radar / $cierres_con_radar / $base_cierre;
    $cierre_quality = min($avg_mult / 2.0, 1.0);
} else {
    $cierre_quality = 0.50; // neutro, sin datos de radar
}

// ── Velocidad de cierre (TTC) vs benchmark empresa ──
// Tiempo promedio del vendedor vs promedio empresa
// Usar ciclo de venta del radar (ya calculado en wp_options por ontime.php)
$ciclo_venta_opt = get_option('radar_ciclo_venta', null);
if ($ciclo_venta_opt && is_array($ciclo_venta_opt) && isset($ciclo_venta_opt['dias'])) {
    $bench_ttc = max(3.0, (float)$ciclo_venta_opt['dias']);
} else {
    // Fallback: calcular desde _sliced_log con sanity check
    $bench_ttc = 14.0;
    if ($accepted_term) {
        $all_accepted_ids = array_keys($accepted_ids);
        if (count($all_accepted_ids) >= 3) {
            $ttc_diffs = [];
            foreach ($all_accepted_ids as $aqid) {
                $post = get_post($aqid);
                if (!$post) continue;
                $created_ts = strtotime($post->post_date);
                if ($created_ts <= 0) continue;
                $log_val = get_post_meta($aqid, '_sliced_log', true);
                $log = is_array($log_val) ? $log_val : @unserialize($log_val ?: '');
                if (!is_array($log)) $log = [];
                $last_ts = 0;
                foreach ($log as $ts => $entry) {
                    $ts_int = (int)$ts;
                    if ($ts_int > $last_ts) $last_ts = $ts_int;
                }
                if ($last_ts > $created_ts) {
                    $diff_days = ($last_ts - $created_ts) / 86400;
                    // Sanity: ignorar si >365 días (dato corrupto)
                    if ($diff_days > 0 && $diff_days <= 365) {
                        $ttc_diffs[] = $diff_days;
                    }
                }
            }
            if (count($ttc_diffs) >= 3) {
                sort($ttc_diffs);
                $bench_ttc = max(3.0, array_sum($ttc_diffs) / count($ttc_diffs));
            }
        }
    }
}

// TTC del vendedor en el período
$ttc_score = 0.5; // neutro
if ($cierres_total > 0) {
    $vendor_ttc_diffs = [];
    foreach ($cierres_con_bucket as $cc_idx => $cc) {
        $qid = (int)($accepted_periodo[$cc_idx]['qid'] ?? 0);
        if (!$qid) continue;
        $post = get_post($qid);
        if (!$post) continue;
        $created_ts = strtotime($post->post_date);
        $log_val = get_post_meta($qid, '_sliced_log', true);
        $log = is_array($log_val) ? $log_val : @unserialize($log_val ?: '');
        if (!is_array($log)) $log = [];
        $last_ts = 0;
        foreach ($log as $ts => $entry) {
            $ts_int = (int)$ts;
            if ($ts_int > $last_ts) $last_ts = $ts_int;
        }
        if ($last_ts > $created_ts) {
            $diff_days = ($last_ts - $created_ts) / 86400;
            if ($diff_days > 0 && $diff_days <= 365) {
                $vendor_ttc_diffs[] = $diff_days;
            }
        }
    }
    if (count($vendor_ttc_diffs) > 0) {
        $avg_ttc_vendor = array_sum($vendor_ttc_diffs) / count($vendor_ttc_diffs);
        if ($avg_ttc_vendor > 0) {
            $ratio_ttc = $bench_ttc / $avg_ttc_vendor;
            $ttc_score = apc_sigmoid($ratio_ttc, 1.0, 3.0);
        }
    }
}

// ── Penalizaciones de conversión ──
$pen_vencidas = min($vencidas_sin_accion * 0.08, 0.3);

// Zona muerta: cotizaciones activas sin movimiento >21 días en el período
$zona_muerta = 0;
if ($has_events) {
    // Cotizaciones del período sin eventos en 21+ días
    $zona_results = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         WHERE p.post_type = 'sliced_quote'
         AND p.post_status IN ('publish','draft','private')
         AND p.post_date >= %s
         AND p.ID NOT IN (
             SELECT tr.object_id FROM {$wpdb->term_relationships} tr
             WHERE tr.term_taxonomy_id IN (
                 SELECT tt.term_taxonomy_id FROM {$wpdb->term_taxonomy} tt
                 INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
                 WHERE tt.taxonomy = 'quote_status' AND t.slug IN ('accepted','declined','cancelled')
             )
         )
         AND NOT EXISTS (
             SELECT 1 FROM {$events_table} e
             WHERE e.quote_id = p.ID AND e.ts_unix >= %d
         )",
        $periodo_start,
        $now - 21 * 86400
    ), ARRAY_A);
    $zona_muerta = count($zona_results);
}
$pen_zona_muerta = min($zona_muerta * 0.05, 0.25);

// Volumen sin resultado: muchas vistas pero 0 cierres
$pen_volumen_sin_cierre = 0.0;
$half_bench = $bench_close_rate / 2;
if ($cierres_total === 0 && $cot_vistas >= 3) {
    $pen_volumen_sin_cierre = min(($cot_vistas - 2) * 0.08, 0.5);
} elseif ($cot_vistas >= 5 && $tasa_cierre < $half_bench) {
    $pen_volumen_sin_cierre = min((1.0 - $tasa_cierre / max($half_bench, 0.01)) * 0.3, 0.3);
}

// ── Score de conversión: close_rate 40% + quality 35% + velocidad 25% ──
$pen_conversion = min($pen_vencidas + $pen_zona_muerta + $pen_volumen_sin_cierre, 0.65);
$s_conversion = (
    apc_sigmoid($tasa_cierre, $bench_close_rate, 2.0 / max($bench_close_rate, 0.01)) * 0.40
    + $cierre_quality * 0.35
    + $ttc_score * 0.25
) - $pen_conversion;
$s_conversion = max(0.0, min(1.0, $s_conversion));

// ── Consistencia semanal ──
// Un vendedor que cierra 1-2/semana constante > uno que cierra 5 en semana 1 y 0 las demás
$semanas_con_cierre = 0;
if ($accepted_term && $cierres_total > 0) {
    // Contar semanas distintas en las que hubo cierre
    $accepted_periodo_ids = array_map(fn($a) => (int)$a['qid'], $accepted_periodo ?? []);
    $weeks_seen = [];
    foreach ($accepted_periodo_ids as $aqid) {
        $post = get_post($aqid);
        if (!$post) continue;
        $log_val = get_post_meta($aqid, '_sliced_log', true);
        $log = is_array($log_val) ? $log_val : @unserialize($log_val ?: '');
        if (!is_array($log)) $log = [];
        $last_ts = 0;
        foreach ($log as $ts => $entry) {
            $ts_int = (int)$ts;
            if ($ts_int > $last_ts) $last_ts = $ts_int;
        }
        if ($last_ts > 0) {
            $weeks_seen[date('Y-W', $last_ts)] = true;
        }
    }
    $semanas_con_cierre = count($weeks_seen);
}

$total_semanas = max(round($PERIODO / 7), 1);
$consistencia = $cierres_total > 0 ? $semanas_con_cierre / $total_semanas : 0;

// Ajustar conversión: bonus si es consistente, penalización si es irregular
if ($cierres_total >= 2) {
    $s_conversion = $s_conversion * (0.80 + 0.20 * $consistencia);
    $s_conversion = max(0.0, min(1.0, $s_conversion));
}

// ============================================================
//  SCORE PROPORCIONAL — PESOS DINÁMICOS
//  Distinguir:
//  a) Sin datos de radar → seguimiento no medible → redistribuir
//  b) Con datos → pesos adaptativos si cierra >2x benchmark
// ============================================================
$w_act = 0.20;
$w_seg = 0.35;
$w_conv = 0.45;

if (!$has_usage || $radar_sessions === 0) {
    // Sin datos de radar → redistribuir
    $w_act  = 0.20;
    $w_seg  = 0.00;
    $w_conv = 0.80;
} else {
    // Pesos adaptativos: si cierra >2x benchmark, seguimiento pierde peso
    // (los resultados hablan por sí solos)
    if ($bench_close_rate > 0 && $tasa_cierre > $bench_close_rate * 2) {
        $ratio_sobre_bench = $tasa_cierre / $bench_close_rate;
        $reduccion_seg = min(($ratio_sobre_bench - 2) * 0.05, 0.20);
        $w_seg  -= $reduccion_seg;
        $w_conv += $reduccion_seg;
    }
}

$proporcional = $s_activacion * $w_act + $s_seguimiento * $w_seg + $s_conversion * $w_conv;
$proporcional = max(0.05, $proporcional); // piso global

// ============================================================
//  MOMENTUM (EMA vs su propio histórico)
//  Compara score actual vs promedio móvil exponencial.
//  Mejorar = momentum positivo. Empeorar = momentum negativo.
//  Persistido en wp_options para sobrevivir entre pageloads.
// ============================================================
$EMA_ALPHA = 0.3;
$ema_option_key = 'apc_ema_' . $target_user_id;
$prev_ema = get_option($ema_option_key, null);

if ($prev_ema && is_array($prev_ema) && isset($prev_ema['updated_at'])) {
    $hours_since = (time() - (int)$prev_ema['updated_at']) / 3600.0;
    $alpha = $EMA_ALPHA * min($hours_since / 24.0, 1.0);
    $alpha = max(0.03, min($alpha, 0.25));

    $ema_act  = $alpha * $s_activacion  + (1 - $alpha) * (float)($prev_ema['ema_act']  ?? $s_activacion);
    $ema_seg  = $alpha * $s_seguimiento + (1 - $alpha) * (float)($prev_ema['ema_seg']  ?? $s_seguimiento);
    $ema_conv = $alpha * $s_conversion  + (1 - $alpha) * (float)($prev_ema['ema_conv'] ?? $s_conversion);

    $ema_composite = $ema_act * $w_act + $ema_seg * $w_seg + $ema_conv * $w_conv;
    $cur_composite = $proporcional;

    $ratio = $ema_composite > 0
        ? $cur_composite / $ema_composite
        : ($cur_composite > 0 ? 2.0 : 1.0);
    $momentum = max(0.1, min(10.0, $ratio));
} else {
    // Primera vez
    $ema_act  = $s_activacion;
    $ema_seg  = $s_seguimiento;
    $ema_conv = $s_conversion;
    $momentum = 1.0;
}

// Guardar EMA en wp_options
update_option($ema_option_key, [
    'ema_act'    => round($ema_act, 4),
    'ema_seg'    => round($ema_seg, 4),
    'ema_conv'   => round($ema_conv, 4),
    'updated_at' => time(),
], false);

// Convertir momentum a score 0-1 con sigmoid sobre log(ratio)
// log(1.0)=0 → 0.50 (estable), log(2.0)=0.69 → ~0.80, log(0.5)=-0.69 → ~0.20
$momentum_score = 1.0 / (1.0 + exp(-3.0 * log($momentum)));

// ============================================================
//  SCORE FINAL
//  Vendedor solo (OnTime = 2 usuarios): proporcional 80% + momentum 20%
// ============================================================
$final = $proporcional * 0.80 + $momentum_score * 0.20;
$score = (int)round($final * 100);
$score = max(0, min(100, $score));

// Nivel y color (mismos umbrales que CotizaCloud)
$nivel = 'bajo';
$nivel_label = 'Hay oportunidad';
$nivel_color = '#3b82f6';
$nivel_bg = '#eff6ff';
$nivel_emoji = '🧊';

if ($score >= 86) {
    $nivel = 'top'; $nivel_label = 'Top'; $nivel_color = '#dc2626'; $nivel_bg = '#fef2f2'; $nivel_emoji = '🔥';
} elseif ($score >= 61) {
    $nivel = 'activo'; $nivel_label = 'Activo'; $nivel_color = '#ea580c'; $nivel_bg = '#fff7ed'; $nivel_emoji = '🟠';
} elseif ($score >= 31) {
    $nivel = 'regular'; $nivel_label = 'Regular'; $nivel_color = '#ca8a04'; $nivel_bg = '#fefce8'; $nivel_emoji = '🟡';
}

// ============================================================
//  DIAGNÓSTICO TEXTUAL (tips como CotizaCloud)
// ============================================================
$tips = [];

if ($cot_asignadas === 0) {
    $tips[] = 'Sin cotizaciones en el período.';
} else {
    // Activación
    $tasa_ap = $cot_vistas / max($cot_asignadas, 1);
    if ($tasa_ap >= 0.90) {
        $tips[] = "Casi todo lo que envía llega al cliente";
    } elseif ($tasa_ap >= 0.60) {
        $sin_abrir = $cot_asignadas - $cot_vistas;
        $tips[] = "Buena tasa de entrega, {$sin_abrir} cotizaciones sin abrir";
    } elseif ($tasa_ap >= 0.30) {
        $tips[] = "Muchas cotizaciones no se abren — revisar canal de envío";
    } else {
        $tips[] = "La mayoría de sus cotizaciones no llegan al cliente";
    }

    // Seguimiento
    if ($s_seguimiento >= 0.70) {
        $tips[] = "Buen seguimiento con el radar";
    } elseif ($s_seguimiento >= 0.35) {
        $tips[] = "Seguimiento moderado, puede usar más el radar";
    } elseif ($s_seguimiento > 0.05) {
        $tips[] = "Poco seguimiento — rara vez revisa el radar";
    } elseif ($w_seg > 0) {
        $tips[] = "No usa el radar para dar seguimiento";
    }

    // Conversión
    if ($cierres_total === 0 && $cot_vistas >= 3) {
        $tips[] = "Cotiza pero no cierra — {$cot_vistas} abiertas sin resultado";
    } elseif ($cierres_total === 0) {
        $tips[] = "Aún sin cierres en el período";
    } elseif ($s_conversion >= 0.70) {
        $tips[] = "Excelente tasa de cierre";
        if ($cierres_bucket_count > 0) $tips[] = "{$cierres_bucket_count} cierres asistidos por radar";
    } elseif ($s_conversion >= 0.40) {
        $tips[] = "{$cierres_total} cierres, ritmo aceptable";
    } else {
        $tips[] = "Cierra poco para el volumen que maneja";
    }

    // Señales específicas
    $total_dormidas = $dormidas_7d + $dormidas_14d + $dormidas_21d;
    if ($total_dormidas > 0) {
        $tips[] = "{$total_dormidas} cotizaciones enviadas que nadie abrió en 7+ días";
    }
    if ($senales_ignoradas > 0) {
        $tips[] = "{$senales_ignoradas} señales calientes ignoradas — clientes activos sin atender";
    }

    // Momentum
    if ($momentum >= 1.20) {
        $tips[] = "Tendencia en mejora";
    } elseif ($momentum <= 0.80) {
        $tips[] = "Tendencia a la baja vs su historial";
    }

    // Meta para subir
    if ($nivel === 'bajo' && $score < 31) {
        $tips[] = "Necesita " . (31 - $score) . " puntos para subir a Regular";
    } elseif ($nivel === 'regular' && $score < 61) {
        $tips[] = "A " . (61 - $score) . " puntos de nivel Activo";
    } elseif ($nivel === 'activo' && $score < 86) {
        $tips[] = "A " . (86 - $score) . " puntos de nivel Top";
    }
}

// Datos para debug
$debug = [
    'periodo' => $PERIODO,
    'target_user' => $target_login,
    'cot_asignadas' => $cot_asignadas,
    'cot_vistas' => $cot_vistas,
    'dormidas' => "{$dormidas_7d}/{$dormidas_14d}/{$dormidas_21d} (7/14/21d)",
    'cierres' => $cierres_total,
    'cierres_bucket' => $cierres_bucket_count,
    'cierres_sin_dto' => $cierres_sin_dto,
    'cierre_quality' => round($cierre_quality, 3),
    'ttc_score' => round($ttc_score, 3),
    'bench_ttc' => round($bench_ttc, 1) . ' días',
    'consistencia' => round($consistencia, 2) . " ({$semanas_con_cierre}/{$total_semanas} sem)",
    'zona_muerta' => $zona_muerta,
    'carga_activa' => $carga_activa,
    'vencidas_sin_accion' => $vencidas_sin_accion,
    'radar_sessions' => $radar_sessions,
    'consultas' => $consultas,
    'dias_activos' => $dias_activos,
    'senales_ignoradas' => $senales_ignoradas,
    'buckets_estancados' => $buckets_estancados,
    'tasa_reaccion' => round($tasa_reaccion, 2) . " ({$cot_con_reaccion_vendor}/{$cot_con_actividad_cliente})",
    'trans_up' => "{$transiciones_up} ({$transiciones_con_reaccion} con reacción)",
    'trans_down' => $transiciones_down,
    'bench_close_rate' => round($bench_close_rate * 100, 2) . '%',
    'bench_apertura' => round($bench_apertura * 100, 2) . '%',
    'bench_radar_weekly' => round($bench_radar_weekly, 1),
    's_activacion' => round($s_activacion, 3),
    's_seguimiento' => round($s_seguimiento, 3),
    's_conversion' => round($s_conversion, 3),
    'proporcional' => round($proporcional, 3),
    'momentum' => round($momentum, 3),
    'momentum_score' => round($momentum_score, 3),
    'pesos' => "act={$w_act} seg={$w_seg} conv={$w_conv}",
    'formula' => "prop*0.80 + mom*0.20 = " . round($final, 3),
];

// ============================================================
//  VISTA HTML
// ============================================================
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Termómetro APC — <?php echo esc_html($target_login); ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 20px; }

  .container { max-width: 700px; margin: 0 auto; }
  .header { text-align: center; margin-bottom: 24px; }
  .header h1 { font-size: 22px; color: #334155; }
  .header .subtitle { color: #64748b; font-size: 14px; margin-top: 4px; }

  /* Termómetro circular */
  .thermo-wrap { display: flex; justify-content: center; margin: 24px 0; }
  .thermo-circle {
    width: 180px; height: 180px; border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    border: 8px solid <?php echo $nivel_color; ?>;
    background: <?php echo $nivel_bg; ?>;
    transition: all 0.3s;
  }
  .thermo-score { font-size: 48px; font-weight: 800; color: <?php echo $nivel_color; ?>; line-height: 1; }
  .thermo-label { font-size: 14px; font-weight: 600; color: <?php echo $nivel_color; ?>; margin-top: 4px; }
  .thermo-emoji { font-size: 20px; margin-top: 2px; }

  /* Dimensiones */
  .dims { display: flex; gap: 12px; margin: 24px 0; }
  .dim-card {
    flex: 1; background: #fff; border-radius: 12px; padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08); text-align: center;
  }
  .dim-title { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
  .dim-bar-wrap { height: 8px; background: #e2e8f0; border-radius: 4px; margin: 10px 0 6px; overflow: hidden; }
  .dim-bar { height: 100%; border-radius: 4px; transition: width 0.5s; }
  .dim-val { font-size: 20px; font-weight: 700; }
  .dim-peso { font-size: 11px; color: #94a3b8; }

  /* Tips */
  .tips { background: #fff; border-radius: 12px; padding: 16px 20px; margin: 24px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-left: 4px solid <?php echo $nivel_color; ?>; }
  .tips-title { font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
  .tips ul { list-style: none; padding: 0; }
  .tips li { font-size: 13px; color: #475569; padding: 4px 0; padding-left: 16px; position: relative; }
  .tips li::before { content: ''; position: absolute; left: 0; top: 11px; width: 6px; height: 6px; border-radius: 50%; background: <?php echo $nivel_color; ?>; }

  /* KPIs */
  .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 24px 0; }
  .kpi { background: #fff; border-radius: 10px; padding: 14px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
  .kpi-val { font-size: 24px; font-weight: 700; color: #334155; }
  .kpi-label { font-size: 11px; color: #94a3b8; margin-top: 2px; }

  /* Gracia */
  .gracia { text-align: center; padding: 40px; background: #fff; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
  .gracia h2 { color: #64748b; font-size: 18px; }
  .gracia p { color: #94a3b8; margin-top: 8px; }

  /* Debug */
  .debug { margin-top: 24px; background: #fff; border-radius: 10px; padding: 14px; font-size: 12px; color: #64748b; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
  .debug b { color: #334155; }

  /* Back link */
  .back { display: inline-block; margin-bottom: 16px; color: #64748b; text-decoration: none; font-size: 13px; }
  .back:hover { color: #334155; }
</style>
</head>
<body>
<div class="container">

<a href="ontimeradar.php" class="back">← Volver al Radar</a>

<div class="header">
  <h1>Termómetro de Productividad</h1>
  <div class="subtitle"><?php echo esc_html($target_login); ?> — últimos <?php echo $PERIODO; ?> días</div>
</div>

<?php if ($en_gracia): ?>
<div class="gracia">
  <h2>📊 Recopilando información</h2>
  <p>Faltan <?php echo ($GRACIA_DIAS - $dias_en_plataforma); ?> días para tener datos suficientes.<br>
  Sigue usando el radar normalmente.</p>
</div>

<?php else: ?>

<div class="thermo-wrap">
  <div class="thermo-circle">
    <div class="thermo-score"><?php echo $score; ?></div>
    <div class="thermo-label"><?php echo esc_html($nivel_label); ?></div>
    <div class="thermo-emoji"><?php echo $nivel_emoji; ?></div>
  </div>
</div>

<div class="dims">
  <?php
  $dims = [
      ['Activación', $s_activacion, $w_act, '#3b82f6'],
      ['Seguimiento', $s_seguimiento, $w_seg, '#8b5cf6'],
      ['Conversión', $s_conversion, $w_conv, '#10b981'],
  ];
  foreach ($dims as [$name, $val, $peso, $color]):
      $pct = (int)round($val * 100);
      $peso_pct = (int)round($peso * 100);
  ?>
  <div class="dim-card">
    <div class="dim-title"><?php echo $name; ?></div>
    <div class="dim-bar-wrap">
      <div class="dim-bar" style="width:<?php echo $pct; ?>%; background:<?php echo $color; ?>;"></div>
    </div>
    <div class="dim-val" style="color:<?php echo $color; ?>;"><?php echo $pct; ?>%</div>
    <div class="dim-peso">Peso: <?php echo $peso_pct; ?>%</div>
  </div>
  <?php endforeach; ?>
</div>

<?php if (!empty($tips)): ?>
<div class="tips">
  <div class="tips-title">Diagnóstico</div>
  <ul>
    <?php foreach ($tips as $tip): ?>
    <li><?php echo esc_html($tip); ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="kpis">
  <div class="kpi">
    <div class="kpi-val"><?php echo $cot_asignadas; ?></div>
    <div class="kpi-label">Cotizaciones</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo $cot_vistas; ?></div>
    <div class="kpi-label">Vistas por cliente</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo $cierres_total; ?></div>
    <div class="kpi-label">Cierres</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo $radar_sessions; ?></div>
    <div class="kpi-label">Sesiones radar</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo $dias_activos; ?></div>
    <div class="kpi-label">Días activos</div>
  </div>
  <div class="kpi">
    <div class="kpi-val"><?php echo round($bench_close_rate * 100, 1); ?>%</div>
    <div class="kpi-label">Close rate empresa</div>
  </div>
</div>

<?php if ($is_admin): ?>
<div class="debug">
  <b>DEBUG APC</b><br>
  <?php foreach ($debug as $k => $v): ?>
    <?php echo esc_html($k); ?>: <b><?php echo esc_html($v); ?></b> |
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>
