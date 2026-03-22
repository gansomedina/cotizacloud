<?php
// ============================================================
//  CotizaApp — core/ActividadScore.php  v2.1
//  APC: Algoritmo de Productividad Comercial (Auto-ajustable)
//
//  4 ÁNGULOS:
//    1. Asesor vs su histórico     (EMA + momentum por dimensión)
//    2. Asesor vs el equipo        (percentil por dimensión)
//    3. Uso vs Resultados          (ratio efectividad)
//    4. Capas de profundidad       (peso progresivo + penalizaciones)
//
//  3 DIMENSIONES:
//    Activación   (20%) — ¿las cotizaciones asignadas llegan al cliente? (mínimo esperado)
//    Seguimiento  (35%) — ¿usa el radar para dar seguimiento?
//    Conversión   (45%) — ¿el software le ayuda a cerrar ventas? (lo que importa)
//
//  PENALIZACIONES:
//    - Cotizaciones dormidas (sin abrir >7d, escala con tiempo)
//    - Señales calientes ignoradas (cliente abrió 3+ veces, vendedor no revisó)
//    - Buckets estancados >14 días
//    - Cotizaciones vencidas sin acción
//    - Zona muerta del pipeline (>21d sin movimiento)
//
//  BONUSES:
//    - Cierre desde bucket frío → máximo (vendedor rescató la venta)
//    - Cierre desde bucket caliente → menor (venta venía sola)
//    - Transiciones frío→caliente (vendedor movió la aguja)
//    - Reacción rápida a señal caliente
//    - Descuento reduce puntaje del cierre (empresa cedió margen)
//
//  AUTO-AJUSTE:
//    - EMA α=0.3 con decay exponencial
//    - Percentil en equipo (umbrales relativos, no fijos)
//    - Normalización por volumen (sigmoid)
// ============================================================

class ActividadScore
{
    private const EMA_ALPHA = 0.3;
    private const PERIODO   = 30; // días rolling

    // ─── Pesos por bucket para cierres (invertido: frío = más mérito) ──
    private const CIERRE_MULT = [
        // Bucket frío → máximo mérito (vendedor hizo la diferencia)
        'enfriandose'     => 2.0,
        'no_abierta'      => 2.0,
        'hesitacion'      => 1.8,
        'sobre_analisis'  => 1.6,
        'comparando'      => 1.5,
        'regreso'         => 1.4,
        // Bucket tibio → buen mérito
        'revivio'              => 1.3,
        're_enganche'          => 1.3,
        'revision_profunda'    => 1.2,
        'vistas_multiples'     => 1.2,
        're_enganche_caliente' => 1.1,
        'multi_persona'        => 1.1,
        // Bucket caliente → menor mérito (venta venía encaminada)
        'alto_importe'     => 1.0,
        'decision_activa'  => 1.0,
        'prediccion_alta'  => 1.0,
        'validando_precio' => 0.9,
        'inminente'        => 0.9,
        'onfire'           => 0.8,
        'probable_cierre'  => 0.8,
    ];

    // ─── Clasificación de buckets por temperatura ──
    private const BUCKET_TEMP = [
        'enfriandose'     => 'frio',
        'no_abierta'      => 'frio',
        'hesitacion'      => 'frio',
        'sobre_analisis'  => 'tibio',
        'comparando'      => 'tibio',
        'regreso'         => 'tibio',
        'revivio'              => 'tibio',
        're_enganche'          => 'tibio',
        'revision_profunda'    => 'tibio',
        'vistas_multiples'     => 'tibio',
        're_enganche_caliente' => 'caliente',
        'multi_persona'        => 'caliente',
        'alto_importe'         => 'caliente',
        'decision_activa'      => 'caliente',
        'prediccion_alta'      => 'caliente',
        'validando_precio'     => 'caliente',
        'inminente'            => 'caliente',
        'onfire'               => 'caliente',
        'probable_cierre'      => 'caliente',
    ];

    // ─── Factor descuento para cierres ──
    private const DESCUENTO_FACTOR = 0.6;

    // ─── Registrar actividad ──────────────────────────────
    public static function registrar(int $usuario_id, int $empresa_id, string $tipo, ?int $ref_id = null): void
    {
        // Throttle: no registrar la misma acción del mismo ref en los últimos 5 min
        if ($ref_id) {
            $reciente = DB::val(
                "SELECT id FROM actividad_log
                 WHERE usuario_id=? AND tipo=? AND ref_id=? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                 LIMIT 1",
                [$usuario_id, $tipo, $ref_id]
            );
            if ($reciente) return;
        } else {
            $reciente = DB::val(
                "SELECT id FROM actividad_log
                 WHERE usuario_id=? AND tipo=? AND ref_id IS NULL AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                 LIMIT 1",
                [$usuario_id, $tipo]
            );
            if ($reciente) return;
        }

        DB::execute(
            "INSERT INTO actividad_log (usuario_id, empresa_id, tipo, ref_id) VALUES (?,?,?,?)",
            [$usuario_id, $empresa_id, $tipo, $ref_id]
        );
    }

    // ─── Calcular score completo de un usuario ───────────
    public static function calcular(int $usuario_id, int $empresa_id): array
    {
        $periodo = self::PERIODO;

        // ═══════════════════════════════════════════════════
        //  SEÑALES CRUDAS (últimos 30 días)
        // ═══════════════════════════════════════════════════

        // Cotizaciones asignadas al vendedor en el período
        $cot_asignadas = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Cotizaciones asignadas que fueron vistas por el cliente (estado='vista' o visitas>0)
        $cot_vistas = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND (estado IN ('vista','aceptada','convertida','aceptada_cliente') OR visitas > 0)",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Cotizaciones dormidas: asignadas >7 días sin ninguna apertura
        $dormidas_7d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND visitas = 0
             AND estado IN ('borrador','enviada')",
            [$usuario_id, $empresa_id, $periodo]
        );

        $dormidas_14d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND visitas = 0
             AND estado IN ('borrador','enviada')",
            [$usuario_id, $empresa_id, $periodo]
        );

        $dormidas_21d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND created_at < DATE_SUB(NOW(), INTERVAL 21 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND visitas = 0
             AND estado IN ('borrador','enviada')",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Sesiones de radar del vendedor
        $radar_sessions = (int)DB::val(
            "SELECT COUNT(*) FROM actividad_log
             WHERE usuario_id=? AND tipo='radar_view'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );

        // Consultas de cotizaciones/clientes en el sistema
        $consultas = (int)DB::val(
            "SELECT COUNT(*) FROM actividad_log
             WHERE usuario_id=? AND tipo IN ('quote_view','client_view')
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );

        // Días con actividad real (no solo login — consulta, radar o acción)
        $dias_activos = (int)DB::val(
            "SELECT COUNT(DISTINCT DATE(created_at)) FROM actividad_log
             WHERE usuario_id=? AND tipo IN ('radar_view','quote_view','client_view')
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );

        // Cierres (aceptada/convertida) en el período
        $cierres_total = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Cierres desde bucket (el radar detectó interés → se cerró)
        $cierres_bucket = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND radar_bucket IS NOT NULL",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Cierres sin descuento (precio completo)
        $cierres_sin_dto = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND (cupon_pct IS NULL OR cupon_pct = 0)
             AND (descuento_auto_pct IS NULL OR descuento_auto_pct = 0)",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Carga activa (pipeline actual)
        $carga_activa = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado IN ('borrador','enviada','vista')",
            [$usuario_id, $empresa_id]
        );

        // Buckets estancados >14 días sin transición
        $buckets_estancados = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND radar_bucket IS NOT NULL
             AND radar_bucket NOT IN ('no_abierta')
             AND estado IN ('enviada','vista')
             AND radar_updated_at < DATE_SUB(NOW(), INTERVAL 14 DAY)",
            [$usuario_id, $empresa_id]
        );

        // Cotizaciones vencidas sin acción
        $vencidas_sin_accion = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND valida_hasta IS NOT NULL
             AND valida_hasta < CURDATE()
             AND estado IN ('enviada','vista')
             AND accion_at IS NULL",
            [$usuario_id, $empresa_id]
        );

        // Zona muerta: cotizaciones >21 días sin movimiento
        $zona_muerta = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado IN ('enviada','vista')
             AND COALESCE(radar_updated_at, updated_at, created_at) < DATE_SUB(NOW(), INTERVAL 21 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
            [$usuario_id, $empresa_id]
        );

        // Señales calientes ignoradas: cotización con 3+ visitas en 48h
        // pero vendedor no entró al radar en esos 2 días
        $senales_calientes = DB::query(
            "SELECT c.id FROM cotizaciones c
             WHERE COALESCE(c.vendedor_id, c.usuario_id)=? AND c.empresa_id=?
             AND c.estado IN ('enviada','vista')
             AND c.visitas >= 3
             AND c.ultima_vista_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$usuario_id, $empresa_id]
        );

        $senales_ignoradas = 0;
        foreach ($senales_calientes as $sc) {
            // ¿El vendedor revisó el radar después de la señal caliente?
            $reviso = DB::val(
                "SELECT COUNT(*) FROM actividad_log
                 WHERE usuario_id=? AND tipo='radar_view'
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)",
                [$usuario_id]
            );
            if ((int)$reviso === 0) {
                $senales_ignoradas++;
            }
        }

        // Transiciones de bucket frío→caliente (vendedor movió la aguja)
        $transiciones_up = 0;
        $transiciones_down = 0;
        try {
            $trans = DB::query(
                "SELECT bucket_anterior, bucket_nuevo FROM bucket_transitions
                 WHERE vendedor_id=? AND empresa_id=?
                 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$usuario_id, $empresa_id, $periodo]
            );
            foreach ($trans as $t) {
                $temp_ant = self::BUCKET_TEMP[$t['bucket_anterior']] ?? null;
                $temp_new = self::BUCKET_TEMP[$t['bucket_nuevo']] ?? null;
                if (!$temp_ant || !$temp_new) continue;
                $order = ['frio' => 0, 'tibio' => 1, 'caliente' => 2];
                if (($order[$temp_new] ?? 0) > ($order[$temp_ant] ?? 0)) {
                    $transiciones_up++;
                } elseif (($order[$temp_new] ?? 0) < ($order[$temp_ant] ?? 0)) {
                    $transiciones_down++;
                }
            }
        } catch (\Throwable $e) { /* tabla aún no migrada */ }

        // Puntos de cierre ponderados por bucket y descuento
        $cierres_con_bucket = DB::query(
            "SELECT c.radar_bucket,
                    COALESCE(c.cupon_pct, 0) AS cupon_pct,
                    COALESCE(c.descuento_auto_pct, 0) AS dto_auto_pct
             FROM cotizaciones c
             WHERE COALESCE(c.vendedor_id, c.usuario_id)=? AND c.empresa_id=?
             AND c.estado IN ('aceptada','convertida','aceptada_cliente')
             AND c.accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );

        $puntos_cierre = 0.0;
        $base_cierre = 10.0;
        foreach ($cierres_con_bucket as $cc) {
            $bucket = $cc['radar_bucket'];
            $mult_bucket = self::CIERRE_MULT[$bucket] ?? 1.0;
            $tiene_dto = ((float)$cc['cupon_pct'] > 0 || (float)$cc['dto_auto_pct'] > 0);
            $mult_dto = $tiene_dto ? self::DESCUENTO_FACTOR : 1.0;
            $puntos_cierre += $base_cierre * $mult_bucket * $mult_dto;
        }

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 1: ACTIVACIÓN (20%)
        //  "¿Las cotizaciones asignadas llegan al cliente?"
        //  Esto es el MÍNIMO esperado — no un logro.
        //  Techo bajo: sigmoid satura rápido.
        // ═══════════════════════════════════════════════════

        $asignadas_validas = max($cot_asignadas, 1);
        $tasa_apertura = $cot_vistas / $asignadas_validas;

        // Penalización escalonada por dormidas
        $pen_dormidas = 0.0;
        $dormidas_solo_7  = $dormidas_7d - $dormidas_14d;
        $dormidas_solo_14 = $dormidas_14d - $dormidas_21d;
        $dormidas_solo_21 = $dormidas_21d;
        if ($asignadas_validas > 0) {
            $pen_dormidas = (
                ($dormidas_solo_7  * 6) +
                ($dormidas_solo_14 * 10) +
                ($dormidas_solo_21 * 15)
            ) / ($asignadas_validas * 15);
        }
        $pen_dormidas = min($pen_dormidas, 1.0);

        // Techo de activación: satura en 70% de apertura (el resto es suerte del cliente)
        $s_activacion = self::sigmoid($tasa_apertura, 0.70, 6.0) - ($pen_dormidas * 0.4);
        $s_activacion = max(0.0, min(1.0, $s_activacion));

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 2: SEGUIMIENTO (30%)
        //  "¿Usa el radar para dar seguimiento?"
        // ═══════════════════════════════════════════════════

        $semanas = max($periodo / 7, 1);
        $radar_por_semana = $radar_sessions / $semanas;
        $consultas_por_semana = $consultas / $semanas;

        // Score de uso del radar (sigmoid: 2 sesiones/semana = midpoint)
        $s_radar = self::sigmoid($radar_por_semana, 2.0, 2.0);
        // Score de consultas (sigmoid: 5 consultas/semana = midpoint)
        $s_consultas = self::sigmoid($consultas_por_semana, 5.0, 1.0);

        // Bonus por transiciones frío→caliente
        $bonus_transiciones = min($transiciones_up * 0.08, 0.3);

        // Penalización por buckets estancados
        $pen_buckets = min($buckets_estancados * 0.06, 0.3);

        // Penalización por señales calientes ignoradas
        $pen_senales = min($senales_ignoradas * 0.10, 0.4);

        // Penalización por transiciones caliente→frío
        $pen_trans_down = min($transiciones_down * 0.05, 0.2);

        $s_seguimiento = ($s_radar * 0.5 + $s_consultas * 0.3 + $bonus_transiciones * 0.2)
                         - $pen_buckets - $pen_senales - $pen_trans_down;
        $s_seguimiento = max(0.0, min(1.0, $s_seguimiento));

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 3: CONVERSIÓN (45%)
        //  "¿Cierra ventas? Esto es lo que importa."
        //  Volumen alto + cierres bajos = penalización fuerte.
        // ═══════════════════════════════════════════════════

        // Tasa de cierre sobre cotizaciones vistas (justo)
        $cot_vistas_safe = max($cot_vistas, 1);
        $tasa_cierre = $cierres_total / $cot_vistas_safe;

        // Puntos de cierre normalizados (con ponderación bucket + descuento)
        $max_puntos_posibles = max($cierres_total, 1) * $base_cierre * 2.0;
        $cierre_quality = $puntos_cierre / $max_puntos_posibles;

        // Penalización por vencidas sin acción
        $pen_vencidas = min($vencidas_sin_accion * 0.08, 0.3);

        // Penalización por zona muerta
        $pen_zona_muerta = min($zona_muerta * 0.05, 0.25);

        // Penalización por volumen sin resultado:
        // Si tiene muchas cotizaciones vistas pero 0 cierres, penalizar proporcionalmente.
        // 5+ vistas sin cierre = empieza a pesar, 10+ = penalización fuerte
        $pen_volumen_sin_cierre = 0.0;
        if ($cierres_total === 0 && $cot_vistas >= 3) {
            $pen_volumen_sin_cierre = min(($cot_vistas - 2) * 0.08, 0.5);
        } elseif ($cot_vistas >= 5 && $tasa_cierre < 0.10) {
            // Tiene algún cierre pero tasa < 10% con volumen alto
            $pen_volumen_sin_cierre = min((1.0 - $tasa_cierre / 0.10) * 0.3, 0.3);
        }

        $s_conversion = (self::sigmoid($tasa_cierre, 0.15, 10.0) * 0.5 + $cierre_quality * 0.5)
                        - $pen_vencidas - $pen_zona_muerta - $pen_volumen_sin_cierre;
        $s_conversion = max(0.0, min(1.0, $s_conversion));

        // ═══════════════════════════════════════════════════
        //  SCORE PROPORCIONAL — PESOS DINÁMICOS
        //
        //  Distinguir dos casos:
        //  a) Sistema nuevo: actividad_log no tiene datos de NADIE
        //     en la empresa → no es culpa del vendedor → neutro
        //  b) El vendedor no usa el radar: hay logins registrados
        //     (el sistema ya registra) pero 0 radar_view → negativo
        // ═══════════════════════════════════════════════════

        // ¿El sistema ya estaba registrando? Checar si ALGUIEN en la empresa tiene logs
        $empresa_tiene_logs = (int)DB::val(
            "SELECT COUNT(*) FROM actividad_log
             WHERE empresa_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             LIMIT 1",
            [$empresa_id, $periodo]
        );

        // ¿El usuario tiene al menos logins registrados?
        $usuario_tiene_logins = (int)DB::val(
            "SELECT COUNT(*) FROM actividad_log
             WHERE usuario_id=? AND tipo='login'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );

        if ($empresa_tiene_logs === 0) {
            // Sistema nuevo — nadie tiene logs → Seguimiento es neutro, no medible
            // Redistribuir peso: 20% Activación + 80% Conversión
            $w_act  = 0.20;
            $w_seg  = 0.00;
            $w_conv = 0.80;
        } else {
            // El sistema YA registra. Si el vendedor no entra al radar, es SU culpa.
            // Pesos completos: Seguimiento con su score real (que será bajo/0)
            $w_act  = 0.20;
            $w_seg  = 0.35;
            $w_conv = 0.45;
        }

        $proporcional = $s_activacion * $w_act
                      + $s_seguimiento * $w_seg
                      + $s_conversion * $w_conv;

        // ═══════════════════════════════════════════════════
        //  ÁNGULO 1: MOMENTUM (vs su propio histórico)
        // ═══════════════════════════════════════════════════

        $prev = DB::row(
            "SELECT ema_activacion, ema_seguimiento, ema_conversion, ema_gestion, ema_presencia
             FROM usuario_score WHERE usuario_id=?",
            [$usuario_id]
        );

        $alpha = self::EMA_ALPHA;

        if ($prev && ((float)($prev['ema_activacion'] ?? 0) > 0 || (float)($prev['ema_gestion'] ?? 0) > 0)) {
            $ema_act  = $alpha * $s_activacion  + (1 - $alpha) * (float)($prev['ema_activacion'] ?? $s_activacion);
            $ema_seg  = $alpha * $s_seguimiento + (1 - $alpha) * (float)($prev['ema_seguimiento'] ?? $s_seguimiento);
            $ema_conv = $alpha * $s_conversion  + (1 - $alpha) * (float)($prev['ema_conversion'] ?? $s_conversion);

            $ema_composite = $ema_act * $w_act + $ema_seg * $w_seg + $ema_conv * $w_conv;
            $cur_composite = $proporcional;

            $momentum = $ema_composite > 0
                ? $cur_composite / $ema_composite
                : ($cur_composite > 0 ? 1.5 : 1.0);
        } else {
            // Primera vez
            $ema_act  = $s_activacion;
            $ema_seg  = $s_seguimiento;
            $ema_conv = $s_conversion;
            $momentum = 1.0;
        }

        // Convertir momentum a score 0-1 con sigmoid
        $momentum_score = 1.0 / (1.0 + exp(-3.0 * ($momentum - 1.0)));

        // ═══════════════════════════════════════════════════
        //  ÁNGULO 2: PERCENTIL EN EQUIPO
        // ═══════════════════════════════════════════════════

        $team = DB::query(
            "SELECT u.id, COALESCE(us.score, 0) AS sc,
                    COALESCE(us.s_activacion, 0) AS sa,
                    COALESCE(us.s_seguimiento, 0) AS ss,
                    COALESCE(us.s_conversion, 0) AS scv
             FROM usuarios u
             LEFT JOIN usuario_score us ON us.usuario_id = u.id
             WHERE u.empresa_id = ? AND u.activo = 1",
            [$empresa_id]
        );

        $team_size = count($team);
        $percentil = 0.50;

        if ($team_size >= 2) {
            $scores_equipo = [];
            foreach ($team as $t) {
                if ((int)$t['id'] === $usuario_id) {
                    $scores_equipo[] = $proporcional;
                } else {
                    $scores_equipo[] = (float)$t['sa'] * $w_act + (float)$t['ss'] * $w_seg + (float)$t['scv'] * $w_conv;
                }
            }
            sort($scores_equipo);
            $pos = array_search($proporcional, $scores_equipo);
            if ($pos === false) $pos = 0;
            $percentil = $team_size > 1 ? $pos / ($team_size - 1) : 0.50;
        }

        // ═══════════════════════════════════════════════════
        //  SCORE FINAL
        // ═══════════════════════════════════════════════════

        if ($team_size >= 2) {
            $final = $proporcional * 0.50 + $momentum_score * 0.25 + $percentil * 0.25;
        } else {
            $final = $proporcional * 0.65 + $momentum_score * 0.35;
        }

        $score = (int)round($final * 100);
        $score = max(0, min(100, $score));

        // Nivel
        if ($score >= 86) $nivel = 'top';
        elseif ($score >= 61) $nivel = 'activo';
        elseif ($score >= 31) $nivel = 'regular';
        else $nivel = 'bajo';

        // Total penalizaciones y bonuses (para display)
        $total_pen = $pen_dormidas + $pen_buckets + $pen_senales + $pen_vencidas + $pen_zona_muerta + $pen_trans_down + $pen_volumen_sin_cierre;
        $total_bonus = $bonus_transiciones + ($cierre_quality > 0 ? $cierre_quality * 0.2 : 0);

        // ═══════════════════════════════════════════════════
        //  GUARDAR
        // ═══════════════════════════════════════════════════

        DB::execute(
            "INSERT INTO usuario_score
             (usuario_id, empresa_id, score, nivel, dias_activos, acciones, conversiones,
              carga_activa, cot_asignadas, cot_vistas, cot_dormidas,
              cierres_bucket, cierres_sin_dto, transiciones_up, senales_ignoradas,
              s_activacion, s_seguimiento, s_conversion, penalizaciones, bonuses,
              tasa_gestion,
              ema_gestion, ema_presencia, ema_conversion, ema_activacion, ema_seguimiento,
              momentum, percentil)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
              score=VALUES(score), nivel=VALUES(nivel),
              dias_activos=VALUES(dias_activos), acciones=VALUES(acciones),
              conversiones=VALUES(conversiones), carga_activa=VALUES(carga_activa),
              cot_asignadas=VALUES(cot_asignadas), cot_vistas=VALUES(cot_vistas),
              cot_dormidas=VALUES(cot_dormidas),
              cierres_bucket=VALUES(cierres_bucket), cierres_sin_dto=VALUES(cierres_sin_dto),
              transiciones_up=VALUES(transiciones_up), senales_ignoradas=VALUES(senales_ignoradas),
              s_activacion=VALUES(s_activacion), s_seguimiento=VALUES(s_seguimiento),
              s_conversion=VALUES(s_conversion),
              penalizaciones=VALUES(penalizaciones), bonuses=VALUES(bonuses),
              tasa_gestion=VALUES(tasa_gestion),
              ema_gestion=VALUES(ema_gestion), ema_presencia=VALUES(ema_presencia),
              ema_conversion=VALUES(ema_conversion),
              ema_activacion=VALUES(ema_activacion), ema_seguimiento=VALUES(ema_seguimiento),
              momentum=VALUES(momentum), percentil=VALUES(percentil),
              updated_at=NOW()",
            [
                $usuario_id, $empresa_id, $score, $nivel,
                $dias_activos, $consultas + $radar_sessions, $cierres_total,
                $carga_activa, $cot_asignadas, $cot_vistas, $dormidas_7d,
                $cierres_bucket, $cierres_sin_dto, $transiciones_up, $senales_ignoradas,
                round($s_activacion, 3), round($s_seguimiento, 3), round($s_conversion, 3),
                round($total_pen, 3), round($total_bonus, 3),
                round($proporcional, 3),
                round($proporcional, 3), round($s_activacion, 3), round($ema_conv, 3),
                round($ema_act, 3), round($ema_seg, 3),
                round($momentum, 2), round($percentil, 2),
            ]
        );

        return [
            'score'             => $score,
            'nivel'             => $nivel,
            'dias_activos'      => $dias_activos,
            'acciones'          => $consultas + $radar_sessions,
            'conversiones'      => $cierres_total,
            'carga_activa'      => $carga_activa,
            'cot_asignadas'     => $cot_asignadas,
            'cot_vistas'        => $cot_vistas,
            'cot_dormidas'      => $dormidas_7d,
            'cierres_bucket'    => $cierres_bucket,
            'cierres_sin_dto'   => $cierres_sin_dto,
            'transiciones_up'   => $transiciones_up,
            'senales_ignoradas' => $senales_ignoradas,
            's_activacion'      => round($s_activacion, 3),
            's_seguimiento'     => round($s_seguimiento, 3),
            's_conversion'      => round($s_conversion, 3),
            'penalizaciones'    => round($total_pen, 3),
            'bonuses'           => round($total_bonus, 3),
            'tasa_gestion'      => round($proporcional, 3),
            'momentum'          => round($momentum, 2),
            'percentil'         => round($percentil, 2),
            'team_size'         => $team_size,
        ];
    }

    // ─── Recalcular toda la empresa ───────────────────────
    public static function recalcular_empresa(int $empresa_id): void
    {
        $usuarios = DB::query(
            "SELECT id FROM usuarios WHERE empresa_id = ? AND activo = 1",
            [$empresa_id]
        );
        foreach ($usuarios as $u) {
            self::calcular((int)$u['id'], $empresa_id);
        }
    }

    // ─── Obtener score guardado (sin recalcular) ──────────
    public static function obtener(int $usuario_id): ?array
    {
        return DB::row("SELECT * FROM usuario_score WHERE usuario_id = ?", [$usuario_id]);
    }

    // ─── Obtener scores de toda la empresa ────────────────
    public static function equipo(int $empresa_id): array
    {
        return DB::query(
            "SELECT us.*, u.nombre, u.rol
             FROM usuario_score us
             JOIN usuarios u ON u.id = us.usuario_id
             WHERE us.empresa_id = ? AND u.activo = 1
             ORDER BY us.score DESC",
            [$empresa_id]
        );
    }

    // ─── Sigmoid helper ───────────────────────────────────
    private static function sigmoid(float $x, float $midpoint, float $steepness): float
    {
        return 1.0 / (1.0 + exp(-$steepness * ($x - $midpoint)));
    }
}
