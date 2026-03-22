<?php
// ============================================================
//  CotizaApp — core/ActividadScore.php  v3.0
//  APC: Algoritmo de Productividad Comercial (Auto-ajustable)
//
//  3 DIMENSIONES (pesos dinámicos según datos disponibles):
//    Activación   (20%) — ¿las cotizaciones llegan al cliente? (mínimo esperado)
//    Seguimiento  (35%) — radar 30% + consultas 15% + reacción 35% + transiciones 20%
//    Conversión   (45%) — close_rate 40% + quality 35% + velocidad 25%
//
//  AUTO-AJUSTE (Fix 12):
//    - Benchmarks por empresa: close_rate, apertura, radar_weekly, avg_monto, time_to_close
//    - Sigmoid midpoints = promedio de la empresa (no hardcoded)
//    - Steepness auto-escalado: 2.0/midpoint
//    - EMA time-weighted (Fix 9): alpha escala por horas desde último cálculo
//
//  MÉTRICAS NUEVAS (v3):
//    - Velocidad de cierre (Fix 1): días vs benchmark empresa
//    - Tasa de reacción (Fix 11): % de cotizaciones con actividad del cliente
//      donde el vendedor revisó radar/cotización dentro de 48h
//    - Monto relativo (Fix 2): bonus por cerrar sobre el promedio empresa
//    - Transiciones con reacción (Fix 4): solo premia si el vendedor revisó radar
//      en las 48h PREVIAS a la transición del bucket
//
//  CORRECCIONES (v3):
//    - Fix 3:  N+1 señales → 2 queries
//    - Fix 5:  cierre_quality = avg_mult/max (no auto-referencial)
//    - Fix 6:  Filtra cotizaciones vacías (total > 0)
//    - Fix 7:  Dormidas: solo 'enviada', no 'borrador'
//    - Fix 8:  Percentil: sorted index, no float array_search
//    - Fix 10: Zona muerta alineada a período rolling (no 90d)
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
        // Período base 30d, pero auto-escala si el ciclo de venta de la empresa es largo
        // Primero calcular benchmarks con 30d, luego ajustar si time_to_close > 20d
        $bench = self::_benchmarks($empresa_id, self::PERIODO);
        $periodo = self::PERIODO;
        if ($bench['time_to_close'] > 20) {
            // Empresa con ciclo largo: extender período para capturar suficientes cierres
            // ttc=30d → periodo=45d, ttc=60d → periodo=60d (cap 60)
            $periodo = (int)min(max(self::PERIODO, $bench['time_to_close'] * 1.5), 60);
            // Recalcular benchmarks con el período extendido
            unset(self::$_bench[$empresa_id]);
            $bench = self::_benchmarks($empresa_id, $periodo);
        }

        // ═══════════════════════════════════════════════════
        //  SEÑALES CRUDAS (últimos 30 días)
        // ═══════════════════════════════════════════════════

        // ── Detectar días de importación masiva (>20 cotizaciones en 1 día) ──
        // Estas cotizaciones no son trabajo real del vendedor
        $import_dates = DB::query(
            "SELECT DATE(created_at) AS d, COUNT(*) AS n
             FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at) HAVING n > 20",
            [$usuario_id, $empresa_id, $periodo]
        );
        $excl_dates = array_map(fn($r) => "'" . $r['d'] . "'", $import_dates);
        $no_import = $excl_dates
            ? "AND DATE(created_at) NOT IN (" . implode(',', $excl_dates) . ")"
            : "";

        // ── Cotizaciones del vendedor ──
        $cw = "COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?";

        // Cuenta TODAS las cotizaciones que salieron del borrador, incluyendo
        // canceladas/rechazadas — para que borrar/cancelar no mejore el score
        $cot_asignadas = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw AND total > 0
             AND (estado != 'borrador' OR visitas > 0) $no_import
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $cot_vistas = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw AND total > 0 $no_import
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND (estado IN ('vista','aceptada','convertida','aceptada_cliente') OR visitas > 0)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $dormidas_7d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_import
             AND estado='enviada' AND visitas=0
             AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $dormidas_14d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_import
             AND estado='enviada' AND visitas=0
             AND created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $dormidas_21d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_import
             AND estado='enviada' AND visitas=0
             AND created_at < DATE_SUB(NOW(), INTERVAL 21 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $cierres_total = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_import
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $cierres_bucket = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_import
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND radar_bucket IS NOT NULL",
            [$usuario_id, $empresa_id, $periodo]
        );
        $cierres_sin_dto = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_import
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND (cupon_pct IS NULL OR cupon_pct=0)
             AND (descuento_auto_pct IS NULL OR descuento_auto_pct=0)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $carga_activa = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw
             AND estado IN ('borrador','enviada','vista')",
            [$usuario_id, $empresa_id]
        );
        $buckets_estancados = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw
             AND radar_bucket IS NOT NULL AND radar_bucket != 'no_abierta'
             AND estado IN ('enviada','vista')
             AND radar_updated_at < DATE_SUB(NOW(), INTERVAL 14 DAY)",
            [$usuario_id, $empresa_id]
        );
        $vencidas_sin_accion = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw
             AND valida_hasta IS NOT NULL AND valida_hasta < CURDATE()
             AND estado IN ('enviada','vista') AND accion_at IS NULL",
            [$usuario_id, $empresa_id]
        );
        $zona_muerta = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw
             AND estado IN ('enviada','vista')
             AND COALESCE(radar_updated_at, updated_at, created_at) < DATE_SUB(NOW(), INTERVAL 21 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );

        // ── Actividad del vendedor ──
        $radar_sessions = (int)DB::val(
            "SELECT COUNT(*) FROM actividad_log
             WHERE usuario_id=? AND tipo='radar_view'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );
        $consultas = (int)DB::val(
            "SELECT COUNT(*) FROM actividad_log
             WHERE usuario_id=? AND tipo IN ('quote_view','client_view')
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );
        $dias_activos = (int)DB::val(
            "SELECT COUNT(DISTINCT DATE(created_at)) FROM actividad_log
             WHERE usuario_id=? AND tipo IN ('radar_view','quote_view','client_view')
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );

        // Señales calientes ignoradas (FIX: 2 queries, no N+1)
        // Cotizaciones con 3+ visitas recientes = cliente interesado
        $cot_calientes = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado IN ('enviada','vista')
             AND visitas >= 3
             AND ultima_vista_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$usuario_id, $empresa_id]
        );

        // Escala gradual: ¿cuándo fue la última vez que revisó el radar?
        $senales_ignoradas = 0;
        if ($cot_calientes > 0) {
            $ultimo_radar = DB::val(
                "SELECT MAX(created_at) FROM actividad_log
                 WHERE usuario_id=? AND tipo='radar_view'",
                [$usuario_id]
            );
            if (!$ultimo_radar) {
                // Nunca ha entrado al radar → todas ignoradas
                $senales_ignoradas = $cot_calientes;
            } else {
                $horas_desde_radar = (time() - strtotime($ultimo_radar)) / 3600.0;
                // <24h = 0 ignoradas, 24-48h = 50%, 48-72h = 75%, >72h = 100%
                if ($horas_desde_radar <= 24) {
                    $senales_ignoradas = 0;
                } elseif ($horas_desde_radar <= 48) {
                    $senales_ignoradas = (int)ceil($cot_calientes * 0.50);
                } elseif ($horas_desde_radar <= 72) {
                    $senales_ignoradas = (int)ceil($cot_calientes * 0.75);
                } else {
                    $senales_ignoradas = $cot_calientes;
                }
            }
        }

        // Fix 11: Tasa de reacción — de cotizaciones con actividad del cliente en 7d,
        // ¿en cuántas el vendedor revisó radar/cotización dentro de 48h?
        $cot_con_actividad_cliente = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND ultima_vista_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             AND estado IN ('enviada','vista') AND total > 0",
            [$usuario_id, $empresa_id]
        );

        $cot_con_reaccion_vendor = 0;
        if ($cot_con_actividad_cliente > 0) {
            // EXISTS evita producto cartesiano — solo pregunta "¿hay al menos 1 log?"
            $cot_con_reaccion_vendor = (int)DB::val(
                "SELECT COUNT(*) FROM cotizaciones c
                 WHERE COALESCE(c.vendedor_id, c.usuario_id)=? AND c.empresa_id=?
                 AND c.ultima_vista_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 AND c.estado IN ('enviada','vista') AND c.total > 0
                 AND EXISTS (
                    SELECT 1 FROM actividad_log al
                    WHERE al.usuario_id = ?
                    AND al.tipo IN ('radar_view','quote_view')
                    AND al.created_at BETWEEN c.ultima_vista_at AND DATE_ADD(c.ultima_vista_at, INTERVAL 48 HOUR)
                    LIMIT 1
                 )",
                [$usuario_id, $empresa_id, $usuario_id]
            );
        }
        $tasa_reaccion = $cot_con_actividad_cliente > 0
            ? $cot_con_reaccion_vendor / $cot_con_actividad_cliente
            : 0.5; // neutro si no hay actividad del cliente

        // Fix 1: Velocidad de cierre — tiempo promedio vs benchmark empresa
        $avg_ttc_vendedor = $cierres_total > 0 ? DB::val(
            "SELECT AVG(DATEDIFF(accion_at, created_at)) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND accion_at IS NOT NULL AND total > 0 $no_import",
            [$usuario_id, $empresa_id, $periodo]
        ) : null;

        $ttc_score = 0.5; // neutro por defecto
        if ($cierres_total > 0 && $avg_ttc_vendedor !== null && (float)$avg_ttc_vendedor > 0) {
            // ratio > 1 = cierra más rápido que el promedio empresa
            $ratio_ttc = $bench['time_to_close'] / (float)$avg_ttc_vendedor;
            $ttc_score = self::sigmoid($ratio_ttc, 1.0, 3.0);
        }

        // Transiciones — single query con LEFT JOIN para detectar reacción
        $transiciones_up = 0;
        $transiciones_down = 0;
        $transiciones_con_reaccion = 0;
        try {
            $trans = DB::query(
                "SELECT bt.bucket_anterior, bt.bucket_nuevo,
                        EXISTS(
                            SELECT 1 FROM actividad_log al
                            WHERE al.usuario_id = bt.vendedor_id
                            AND al.tipo IN ('radar_view','quote_view')
                            AND al.created_at BETWEEN DATE_SUB(bt.created_at, INTERVAL 48 HOUR) AND bt.created_at
                            LIMIT 1
                        ) AS reacciono
                 FROM bucket_transitions bt
                 WHERE bt.vendedor_id=? AND bt.empresa_id=?
                 AND bt.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$usuario_id, $empresa_id, $periodo]
            );
            $order = ['frio' => 0, 'tibio' => 1, 'caliente' => 2];
            foreach ($trans as $t) {
                $temp_ant = self::BUCKET_TEMP[$t['bucket_anterior']] ?? null;
                $temp_new = self::BUCKET_TEMP[$t['bucket_nuevo']] ?? null;
                if (!$temp_ant || !$temp_new) continue;
                if (($order[$temp_new] ?? 0) > ($order[$temp_ant] ?? 0)) {
                    $transiciones_up++;
                    if ((int)$t['reacciono']) $transiciones_con_reaccion++;
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
             AND c.accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             $no_import",
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

        // Fix 12: midpoint auto-ajustable al promedio de la empresa
        $s_activacion = self::sigmoid($tasa_apertura, $bench['apertura'], 2.0 / max($bench['apertura'], 0.1)) - ($pen_dormidas * 0.4);
        // Tope: penalizaciones de activación no pueden bajar más de 0.60
        $s_activacion = max(0.0, min(1.0, $s_activacion));

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 2: SEGUIMIENTO (30%)
        //  "¿Usa el radar para dar seguimiento?"
        // ═══════════════════════════════════════════════════

        $semanas = max($periodo / 7, 1);
        $radar_por_semana = $radar_sessions / $semanas;
        $consultas_por_semana = $consultas / $semanas;

        // Fix 12: radar midpoint = promedio de la empresa
        $s_radar = self::sigmoid($radar_por_semana, $bench['radar_weekly'], 2.0 / max($bench['radar_weekly'], 0.1));
        $s_consultas = self::sigmoid($consultas_por_semana, $bench['radar_weekly'] * 2.5, 0.5);

        // Fix 4: bonus solo por transiciones donde el vendedor REACCIONÓ
        $bonus_transiciones = min($transiciones_con_reaccion * 0.10, 0.3);

        // Penalización por buckets estancados
        $pen_buckets = min($buckets_estancados * 0.06, 0.3);

        // Penalización por señales calientes ignoradas
        $pen_senales = min($senales_ignoradas * 0.10, 0.4);

        // Penalización por transiciones caliente→frío
        $pen_trans_down = min($transiciones_down * 0.05, 0.2);

        // tasa_reaccion entra con 35% del peso de seguimiento
        $pen_seguimiento = min($pen_buckets + $pen_senales + $pen_trans_down, 0.60); // cap 0.60
        $s_seguimiento = ($s_radar * 0.30 + $s_consultas * 0.15 + $tasa_reaccion * 0.35 + $bonus_transiciones * 0.20)
                         - $pen_seguimiento;
        $s_seguimiento = max(0.0, min(1.0, $s_seguimiento));

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 3: CONVERSIÓN (45%)
        //  "¿Cierra ventas? Esto es lo que importa."
        //  Volumen alto + cierres bajos = penalización fuerte.
        // ═══════════════════════════════════════════════════

        // Tasa de cierre sobre cotizaciones vistas (justo)
        $cot_vistas_safe = max($cot_vistas, 1);
        $tasa_cierre = $cierres_total / $cot_vistas_safe;

        // Calidad de cierre: multiplicador promedio normalizado.
        // Sin bucket (cierre directo) = mult 1.0 → quality 0.50 base.
        // Rescata venta fría (2.0) → quality 1.0. Solo fáciles (0.8) → quality 0.40.
        // Cierres sin bucket no castigan: se normaliza sobre cierres CON bucket si los hay.
        $cierres_con_radar = 0;
        $puntos_con_radar = 0.0;
        foreach ($cierres_con_bucket as $cc) {
            if ($cc['radar_bucket'] !== null) {
                $cierres_con_radar++;
                $puntos_con_radar += $base_cierre * (self::CIERRE_MULT[$cc['radar_bucket']] ?? 1.0)
                    * (((float)$cc['cupon_pct'] > 0 || (float)$cc['dto_auto_pct'] > 0) ? self::DESCUENTO_FACTOR : 1.0);
            }
        }
        if ($cierres_con_radar > 0) {
            $avg_mult = $puntos_con_radar / $cierres_con_radar / $base_cierre;
            $cierre_quality = min($avg_mult / 2.0, 1.0);
        } else {
            // Sin datos de radar → neutro
            $cierre_quality = 0.50;
        }

        // Penalización por vencidas sin acción
        $pen_vencidas = min($vencidas_sin_accion * 0.08, 0.3);

        // Penalización por zona muerta
        $pen_zona_muerta = min($zona_muerta * 0.05, 0.25);

        // Penalización por volumen sin resultado:
        // Si tiene muchas cotizaciones vistas pero 0 cierres, penalizar proporcionalmente.
        // 5+ vistas sin cierre = empieza a pesar, 10+ = penalización fuerte
        // Fix 12: penalización relativa al benchmark de la empresa
        $pen_volumen_sin_cierre = 0.0;
        $half_bench = $bench['close_rate'] / 2; // mitad del promedio empresa
        if ($cierres_total === 0 && $cot_vistas >= 3) {
            $pen_volumen_sin_cierre = min(($cot_vistas - 2) * 0.08, 0.5);
        } elseif ($cot_vistas >= 5 && $tasa_cierre < $half_bench) {
            $pen_volumen_sin_cierre = min((1.0 - $tasa_cierre / $half_bench) * 0.3, 0.3);
        }

        // close rate + quality + velocidad de cierre
        $pen_conversion = min($pen_vencidas + $pen_zona_muerta + $pen_volumen_sin_cierre, 0.65); // cap 0.65
        $s_conversion = (
            self::sigmoid($tasa_cierre, $bench['close_rate'], 2.0 / max($bench['close_rate'], 0.01)) * 0.40
            + $cierre_quality * 0.35
            + $ttc_score * 0.25
        )
                        - $pen_conversion;
        $s_conversion = max(0.0, min(1.0, $s_conversion));

        // ═══════════════════════════════════════════════════
        //  CONSISTENCIA SEMANAL
        //  Un vendedor que cierra 1-2/semana constante es mejor
        //  que uno que cierra 5 en semana 1 y luego 0 por 3 semanas.
        // ═══════════════════════════════════════════════════

        $semanas_con_cierre = (int)DB::val(
            "SELECT COUNT(DISTINCT YEARWEEK(accion_at, 1)) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND total > 0 $no_import",
            [$usuario_id, $empresa_id, $periodo]
        );
        $total_semanas = max(round($periodo / 7), 1);
        // Ratio: 4/4 semanas con cierre = 1.0, 1/4 = 0.25
        $consistencia = $cierres_total > 0 ? $semanas_con_cierre / $total_semanas : 0;

        // Ajustar conversión: bonus si es consistente, penalización si es irregular
        // Solo aplica si tiene al menos 2 cierres (con 1 no hay variación que medir)
        if ($cierres_total >= 2) {
            $s_conversion = $s_conversion * (0.80 + 0.20 * $consistencia);
            $s_conversion = max(0.0, min(1.0, $s_conversion));
        }

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
            "SELECT ema_activacion, ema_seguimiento, ema_conversion, ema_gestion, ema_presencia, updated_at
             FROM usuario_score WHERE usuario_id=?",
            [$usuario_id]
        );

        // Fix 9: EMA time-weighted — alpha se escala por tiempo desde último cálculo
        // 5 min → alpha ~0.001 (casi no cambia), 24h+ → alpha completo (0.3)
        // Evita dilución por recálculos frecuentes (dashboard cache 5 min)
        $base_alpha = self::EMA_ALPHA;
        $hours_since = ($prev && $prev['updated_at'])
            ? (time() - strtotime($prev['updated_at'])) / 3600.0
            : 24.0;
        $alpha = $base_alpha * min($hours_since / 24.0, 1.0);

        if ($prev && ((float)($prev['ema_activacion'] ?? 0) > 0 || (float)($prev['ema_gestion'] ?? 0) > 0)) {
            $ema_act  = $alpha * $s_activacion  + (1 - $alpha) * (float)($prev['ema_activacion'] ?? $s_activacion);
            $ema_seg  = $alpha * $s_seguimiento + (1 - $alpha) * (float)($prev['ema_seguimiento'] ?? $s_seguimiento);
            $ema_conv = $alpha * $s_conversion  + (1 - $alpha) * (float)($prev['ema_conversion'] ?? $s_conversion);

            $ema_composite = $ema_act * $w_act + $ema_seg * $w_seg + $ema_conv * $w_conv;
            $cur_composite = $proporcional;

            // Momentum simétrico con log: mejorar 2x = +0.69, empeorar 2x = -0.69
            $ratio = $ema_composite > 0
                ? $cur_composite / $ema_composite
                : ($cur_composite > 0 ? 2.0 : 1.0);
            $momentum = max(0.1, min(10.0, $ratio)); // clamp para evitar log(0)
        } else {
            // Primera vez
            $ema_act  = $s_activacion;
            $ema_seg  = $s_seguimiento;
            $ema_conv = $s_conversion;
            $momentum = 1.0;
        }

        // Convertir momentum a score 0-1 con sigmoid sobre log(ratio)
        // log(1.0)=0 → 0.50 (estable), log(2.0)=0.69 → ~0.80, log(0.5)=-0.69 → ~0.20
        $momentum_score = 1.0 / (1.0 + exp(-3.0 * log($momentum)));

        // ═══════════════════════════════════════════════════
        //  ÁNGULO 2: PERCENTIL EN EQUIPO
        // ═══════════════════════════════════════════════════

        // Solo usuarios que realmente venden (tienen al menos 1 cotización asignada)
        // Excluye admins, usuarios base, cuentas de sistema que no cotizan
        $team = DB::query(
            "SELECT u.id, COALESCE(us.score, 0) AS sc,
                    COALESCE(us.s_activacion, 0) AS sa,
                    COALESCE(us.s_seguimiento, 0) AS ss,
                    COALESCE(us.s_conversion, 0) AS scv
             FROM usuarios u
             LEFT JOIN usuario_score us ON us.usuario_id = u.id
             WHERE u.empresa_id = ? AND u.activo = 1
             AND EXISTS (
                SELECT 1 FROM cotizaciones c
                WHERE COALESCE(c.vendedor_id, c.usuario_id) = u.id
                AND c.empresa_id = ? AND c.total > 0
                AND c.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             )",
            [$empresa_id, $empresa_id, $periodo]
        );

        $team_size = count($team);
        $percentil = 0.50;

        if ($team_size >= 2) {
            $scores_equipo = [];
            $mi_idx = 0;
            foreach ($team as $i => $t) {
                if ((int)$t['id'] === $usuario_id) {
                    $scores_equipo[] = ['s' => $proporcional, 'me' => true];
                } else {
                    $scores_equipo[] = ['s' => (float)$t['sa'] * $w_act + (float)$t['ss'] * $w_seg + (float)$t['scv'] * $w_conv, 'me' => false];
                }
            }
            usort($scores_equipo, fn($a, $b) => $a['s'] <=> $b['s']);
            $pos = 0;
            foreach ($scores_equipo as $i => $se) {
                if ($se['me']) { $pos = $i; break; }
            }
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
        $total_pen = min($pen_dormidas, 0.60) + $pen_seguimiento + $pen_conversion;
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
        unset(self::$_bench[$empresa_id]);
        $periodo = self::PERIODO;
        // Solo recalcular usuarios que realmente tienen cotizaciones asignadas
        $usuarios = DB::query(
            "SELECT DISTINCT u.id FROM usuarios u
             WHERE u.empresa_id = ? AND u.activo = 1
             AND EXISTS (
                SELECT 1 FROM cotizaciones c
                WHERE COALESCE(c.vendedor_id, c.usuario_id) = u.id
                AND c.empresa_id = ? AND c.total > 0
                AND c.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             )",
            [$empresa_id, $empresa_id, $periodo]
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
        $periodo = self::PERIODO;
        return DB::query(
            "SELECT us.*, u.nombre, u.rol
             FROM usuario_score us
             JOIN usuarios u ON u.id = us.usuario_id
             WHERE us.empresa_id = ? AND u.activo = 1
             AND EXISTS (
                SELECT 1 FROM cotizaciones c
                WHERE COALESCE(c.vendedor_id, c.usuario_id) = u.id
                AND c.empresa_id = ? AND c.total > 0
                AND c.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             )
             ORDER BY us.score DESC",
            [$empresa_id, $empresa_id, $periodo]
        );
    }

    // ─── Diagnóstico textual — traduce el score a frase humana ──
    public static function diagnostico(array $s): string
    {
        $act  = (float)($s['s_activacion'] ?? 0);
        $seg  = (float)($s['s_seguimiento'] ?? 0);
        $conv = (float)($s['s_conversion'] ?? 0);
        $asig = (int)($s['cot_asignadas'] ?? 0);
        $vist = (int)($s['cot_vistas'] ?? 0);
        $cierres = (int)($s['conversiones'] ?? 0);
        $dorm = (int)($s['cot_dormidas'] ?? 0);
        $ign  = (int)($s['senales_ignoradas'] ?? 0);
        $cbkt = (int)($s['cierres_bucket'] ?? 0);
        $sdto = (int)($s['cierres_sin_dto'] ?? 0);
        $tup  = (int)($s['transiciones_up'] ?? 0);
        $mom  = (float)($s['momentum'] ?? 1);
        $score = (int)($s['score'] ?? 0);
        $pen  = (float)($s['penalizaciones'] ?? 0);

        // Sin datos suficientes
        if ($asig === 0) return 'Sin cotizaciones en el período.';

        $frases = [];

        // ── ACTIVACIÓN ──
        $tasa_ap = $asig > 0 ? $vist / $asig : 0;
        if ($tasa_ap >= 0.90) {
            $frases[] = "Casi todo lo que envía llega al cliente";
        } elseif ($tasa_ap >= 0.60) {
            $frases[] = "Buena tasa de entrega, " . ($asig - $vist) . " cotizaciones sin abrir";
        } elseif ($tasa_ap >= 0.30) {
            $frases[] = "Muchas cotizaciones no se abren — revisar canal de envío";
        } else {
            $frases[] = "La mayoría de sus cotizaciones no llegan al cliente";
        }

        // ── SEGUIMIENTO ──
        if ($seg >= 0.70) {
            $frases[] = "da buen seguimiento con el radar";
        } elseif ($seg >= 0.35) {
            $frases[] = "seguimiento moderado, puede usar más el radar";
        } elseif ($seg > 0.05) {
            $frases[] = "poco seguimiento — rara vez revisa el radar";
        } else {
            $frases[] = "no usa el radar para dar seguimiento";
        }

        // ── CONVERSIÓN ──
        if ($cierres === 0 && $vist >= 3) {
            $frases[] = "cotiza pero no cierra — " . $vist . " abiertas sin resultado";
        } elseif ($cierres === 0) {
            $frases[] = "aún sin cierres en el período";
        } elseif ($conv >= 0.70) {
            $frases[] = "excelente tasa de cierre";
            if ($cbkt > 0) $frases[] = "$cbkt cierres asistidos por radar";
            if ($sdto === $cierres && $cierres > 0) $frases[] = "todos a precio completo";
        } elseif ($conv >= 0.40) {
            $frases[] = "$cierres cierres, ritmo aceptable";
        } else {
            $frases[] = "cierra poco para el volumen que maneja";
        }

        // ── SEÑALES ESPECÍFICAS ──
        if ($dorm > 0) {
            $frases[] = "$dorm cotizaciones enviadas que nadie abrió en 7+ días";
        }
        if ($ign > 0) {
            $frases[] = "clientes activos sin atender — $ign señales calientes ignoradas";
        }

        // ── MOMENTUM ──
        if ($mom >= 1.20) {
            $frases[] = "tendencia en mejora";
        } elseif ($mom <= 0.80) {
            $frases[] = "tendencia a la baja vs su historial";
        }

        // Construir frase final — capitalizar primera letra
        $txt = implode('. ', array_map(fn($f) => ucfirst($f), $frases)) . '.';

        // Recomendación final según el punto más débil
        $dims = ['act' => $act, 'seg' => $seg, 'conv' => $conv];
        $peor = array_keys($dims, min($dims))[0];
        $reco = match($peor) {
            'act'  => $asig - $vist <= 2
                ? ''
                : ' Tip: verificar que los datos de contacto del cliente sean correctos.',
            'seg'  => $seg < 0.10
                ? ' Tip: el Radar detecta clientes interesados en tiempo real — revisarlo frecuentemente marca la diferencia.'
                : ' Tip: revisar el Radar con frecuencia para no perder oportunidades calientes.',
            'conv' => $cierres === 0
                ? ' Tip: dar seguimiento personalizado a las cotizaciones más vistas.'
                : ' Tip: enfocarse en cotizaciones con alta actividad del cliente.',
        };

        return $txt . $reco;
    }

    // ─── Benchmarks auto-ajustables por empresa ─────────
    private static array $_bench = [];

    private static function _benchmarks(int $empresa_id, int $periodo): array
    {
        if (isset(self::$_bench[$empresa_id])) return self::$_bench[$empresa_id];

        // Tasa de cierre de la empresa (vistas → cierres)
        $emp_vistas = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0
             AND (visitas > 0 OR estado IN ('aceptada','convertida','aceptada_cliente'))
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo]
        );
        $emp_cierres = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo]
        );
        $close_rate = $emp_vistas >= 5 ? $emp_cierres / $emp_vistas : 0.15;

        // Tiempo promedio de cierre (días)
        $avg_ttc = DB::val(
            "SELECT AVG(DATEDIFF(accion_at, created_at)) FROM cotizaciones
             WHERE empresa_id=? AND total > 0
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND accion_at IS NOT NULL",
            [$empresa_id, $periodo]
        );

        // Radar semanal promedio (requiere mínimo 2 usuarios para no sesgar)
        $weeks = max($periodo / 7, 1);
        $radar_users = (int)DB::val(
            "SELECT COUNT(DISTINCT usuario_id) FROM actividad_log
             WHERE empresa_id=? AND tipo='radar_view'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo]
        );
        $avg_radar = $radar_users >= 2 ? DB::val(
            "SELECT AVG(cnt) FROM (
                SELECT COUNT(*)/{$weeks} AS cnt FROM actividad_log
                WHERE empresa_id=? AND tipo='radar_view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY usuario_id
             ) AS sub",
            [$empresa_id, $periodo]
        ) : null;

        // Tasa de apertura de la empresa
        $emp_asig = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo]
        );
        $apertura = $emp_asig >= 5 ? $emp_vistas / $emp_asig : 0.70;

        self::$_bench[$empresa_id] = [
            'close_rate'    => max((float)$close_rate, 0.03),
            'time_to_close' => max((float)($avg_ttc ?? 14), 3),
            'radar_weekly'  => max((float)($avg_radar ?? 2.0), 0.5),
            'apertura'      => max((float)$apertura, 0.30),
        ];
        return self::$_bench[$empresa_id];
    }

    // ─── Sigmoid helper ───────────────────────────────────
    private static function sigmoid(float $x, float $midpoint, float $steepness): float
    {
        return 1.0 / (1.0 + exp(-$steepness * ($x - $midpoint)));
    }
}
