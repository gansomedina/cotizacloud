<?php
// ============================================================
//  CotizaApp — core/ActividadScore.php  v3.2
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
    private const PERIODO   = 15; // días rolling

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

    private const GRACIA_DIAS = 7; // temporal para testing — volver a 15

    // ─── Calcular score completo de un usuario ───────────
    public static function calcular(int $usuario_id, int $empresa_id): array
    {
        // ═══════════════════════════════════════════════════
        //  PERÍODO DE GRACIA — 15 días (= PERIODO)
        //  No evaluar vendedores nuevos. Se necesita un período
        //  completo de datos para un score justo.
        // ═══════════════════════════════════════════════════
        $primer_cot = DB::val(
            "SELECT MIN(created_at) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=? AND total > 0",
            [$usuario_id, $empresa_id]
        );
        $primer_log = DB::val(
            "SELECT MIN(created_at) FROM actividad_log WHERE usuario_id=?",
            [$usuario_id]
        );
        // Tomar la fecha más antigua entre primera cotización y primer log
        $fecha_inicio = null;
        if ($primer_cot && $primer_log) {
            $fecha_inicio = min($primer_cot, $primer_log);
        } else {
            $fecha_inicio = $primer_cot ?: $primer_log;
        }

        $dias_en_plataforma = $fecha_inicio
            ? (int)ceil((time() - strtotime($fecha_inicio)) / 86400)
            : 0;

        if ($dias_en_plataforma < self::GRACIA_DIAS) {
            $dias_restantes = self::GRACIA_DIAS - $dias_en_plataforma;
            $resultado_gracia = [
                'score' => 0, 'nivel' => 'nuevo',
                'dias_activos' => 0, 'acciones' => 0, 'conversiones' => 0,
                'carga_activa' => 0, 'cot_asignadas' => 0, 'cot_vistas' => 0,
                'cot_dormidas' => 0, 'cierres_bucket' => 0, 'cierres_sin_dto' => 0,
                'transiciones_up' => 0, 'senales_ignoradas' => 0,
                's_activacion' => 0, 's_seguimiento' => 0, 's_conversion' => 0,
                'penalizaciones' => 0, 'bonuses' => 0, 'tasa_gestion' => 0,
                'momentum' => 1.0, 'percentil' => 0.50, 'team_size' => 0,
                'en_gracia' => true, 'dias_restantes' => $dias_restantes,
            ];
            // Guardar en BD para que obtener() también lo refleje
            DB::execute(
                "INSERT INTO usuario_score (usuario_id, empresa_id, score, nivel, momentum, percentil)
                 VALUES (?,?,0,'nuevo',1.00,0.50)
                 ON DUPLICATE KEY UPDATE score=0, nivel='nuevo', updated_at=NOW()",
                [$usuario_id, $empresa_id]
            );
            return $resultado_gracia;
        }

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
        // Suspendidas no cuentan para el score (como si no existieran)
        $no_susp = "AND suspendida = 0";

        $cot_asignadas = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw AND total > 0
             AND (estado != 'borrador' OR visitas > 0) $no_susp $no_import
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $cot_vistas = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw AND total > 0 $no_susp $no_import
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND (estado IN ('vista','aceptada','convertida','aceptada_cliente') OR visitas > 0)",
            [$usuario_id, $empresa_id, $periodo]
        );
        // Dormidas: solo las NO suspendidas. Si se suspendió antes de 14d/21d,
        // ya no escala la penalización. El penalty de 7d se limpia solo al salir
        // de la ventana de 30 días.
        $dormidas_7d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
             AND estado='enviada' AND visitas=0
             AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $dormidas_14d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
             AND estado='enviada' AND visitas=0
             AND created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $dormidas_21d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
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
             AND cupon_pct = 0
             AND descuento_auto_pct = 0",
            [$usuario_id, $empresa_id, $periodo]
        );
        $carga_activa = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp
             AND estado IN ('borrador','enviada','vista')",
            [$usuario_id, $empresa_id]
        );
        $buckets_estancados = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp
             AND radar_bucket IS NOT NULL AND radar_bucket != 'no_abierta'
             AND estado IN ('enviada','vista')
             AND radar_updated_at < DATE_SUB(NOW(), INTERVAL 14 DAY)",
            [$usuario_id, $empresa_id]
        );
        $vencidas_sin_accion = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp
             AND valida_hasta IS NOT NULL AND valida_hasta < CURDATE()
             AND estado IN ('enviada','vista') AND accion_at IS NULL",
            [$usuario_id, $empresa_id]
        );
        $zona_muerta = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp
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
             AND estado IN ('enviada','vista') AND suspendida = 0
             AND visitas >= 3
             AND ultima_vista_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$usuario_id, $empresa_id]
        );

        // Señales ignoradas = cotizaciones calientes SIN reacción del vendedor en 48h
        // Verifica por cada cotización, no globalmente
        $senales_ignoradas = 0;
        if ($cot_calientes > 0) {
            $cot_calientes_atendidas = (int)DB::val(
                "SELECT COUNT(*) FROM cotizaciones c
                 WHERE COALESCE(c.vendedor_id, c.usuario_id)=? AND c.empresa_id=?
                 AND c.estado IN ('enviada','vista') AND c.suspendida = 0
                 AND c.visitas >= 3
                 AND c.ultima_vista_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 AND EXISTS (
                    SELECT 1 FROM actividad_log al
                    WHERE al.usuario_id = ?
                    AND al.tipo IN ('radar_view','quote_view')
                    AND al.created_at BETWEEN c.ultima_vista_at AND DATE_ADD(c.ultima_vista_at, INTERVAL 48 HOUR)
                    LIMIT 1
                 )",
                [$usuario_id, $empresa_id, $usuario_id]
            );
            $senales_ignoradas = $cot_calientes - $cot_calientes_atendidas;
        }

        // Fix 11: Tasa de reacción — de cotizaciones con actividad del cliente en 7d,
        // ¿en cuántas el vendedor revisó radar/cotización dentro de 48h?
        $cot_con_actividad_cliente = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND ultima_vista_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             AND estado IN ('enviada','vista') AND suspendida = 0 AND total > 0",
            [$usuario_id, $empresa_id]
        );

        $cot_con_reaccion_vendor = 0;
        if ($cot_con_actividad_cliente > 0) {
            // EXISTS evita producto cartesiano — solo pregunta "¿hay al menos 1 log?"
            $cot_con_reaccion_vendor = (int)DB::val(
                "SELECT COUNT(*) FROM cotizaciones c
                 WHERE COALESCE(c.vendedor_id, c.usuario_id)=? AND c.empresa_id=?
                 AND c.ultima_vista_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 AND c.estado IN ('enviada','vista') AND c.suspendida = 0 AND c.total > 0
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
        // Fix P7: si no hay actividad del cliente, evaluar según carga activa.
        // Sin cotizaciones activas = 0.0 (no hay mérito). Con cotizaciones pero sin
        // actividad del cliente = 0.3 (neutro-bajo, no castiga si es nuevo).
        if ($cot_con_actividad_cliente > 0) {
            $tasa_reaccion = $cot_con_reaccion_vendor / $cot_con_actividad_cliente;
        } elseif ($cot_asignadas === 0) {
            $tasa_reaccion = 0.0;
        } else {
            $tasa_reaccion = 0.3;
        }

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
        //  DIMENSIÓN 1: ACTIVACIÓN (10%) — v5
        //  "¿Envías y llegan?"
        //  Ratio directo + penalización fuerte por no abiertas
        // ═══════════════════════════════════════════════════

        $asignadas_validas = max($cot_asignadas, 1);
        $tasa_apertura = $cot_vistas / $asignadas_validas;

        // No abiertas en 5+ días (excluir suspendidas, ya filtrado por $no_susp)
        $no_abiertas_5d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
             AND estado='enviada' AND visitas=0
             AND created_at < DATE_SUB(NOW(), INTERVAL 5 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Penalización por no abiertas: usa 1/close_rate (fuerte, auto-ajustable)
        $close_rate_safe = max($bench['close_rate'], 0.01);
        $pen_no_abiertas = min(($no_abiertas_5d / $asignadas_validas) * (1.0 / $close_rate_safe), 1.0);

        // Penalización escalonada por dormidas (7d, 14d, 21d)
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

        // v5: ratio directo, sin sigmoid, sin piso fijo
        $s_activacion = $tasa_apertura - $pen_no_abiertas - ($pen_dormidas * 0.4);
        $s_activacion = max(0.0, min(1.0, $s_activacion));

        // Tasa de cierre (se calcula antes de engagement/seguimiento)
        $cot_vistas_safe = max($cot_vistas, 1);
        $tasa_cierre = $cierres_total / $cot_vistas_safe;

        // Ventas sin pago inicial (>5 días) — se usa en Engagement
        $ventas_sin_pago = (int)DB::val(
            "SELECT COUNT(*) FROM ventas
             WHERE COALESCE(vendedor_id, usuario_id) = ? AND empresa_id = ?
             AND pagado = 0 AND estado NOT IN ('cancelada','entregada')
             AND total > 0
             AND created_at < DATE_SUB(NOW(), INTERVAL 5 DAY)",
            [$usuario_id, $empresa_id]
        );

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 2: ENGAGEMENT (20%) — v5
        //  Capa de penalizaciones post-envío
        //  Arranca en 1.0, se restan penalizaciones
        // ═══════════════════════════════════════════════════

        // Ventas totales del vendedor en el período (no canceladas)
        $ventas_totales = (int)DB::val(
            "SELECT COUNT(*) FROM ventas
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado != 'cancelada'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $ventas_totales_safe = max($ventas_totales, 1);

        // pen_sin_pago: ventas con pagado=0 después de 5 días
        // Fórmula: (sin_pago/totales) × (1/close_rate) — fuerte
        $eng_pen_sin_pago = 0.0;
        if ($ventas_sin_pago > 0) {
            $eng_pen_sin_pago = min(
                ($ventas_sin_pago / $ventas_totales_safe) * (1.0 / $close_rate_safe),
                1.0
            );
        }

        // pen_descuento: ventas con descuento — mérito de empresa, no vendedor
        // Fórmula: (con_descuento/totales) × close_rate — suave
        $ventas_con_descuento = (int)DB::val(
            "SELECT COUNT(*) FROM ventas
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado != 'cancelada'
             AND (descuento_auto_amt > 0 OR cupon_monto > 0)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $eng_pen_descuento = ($ventas_con_descuento / $ventas_totales_safe) * $close_rate_safe;

        // pen_enfriamiento: transiciones down / total × close_rate (suave)
        // Enfriamiento natural no es culpa directa del vendedor
        $trans_total = $transiciones_up + $transiciones_down;
        $eng_pen_enfriamiento = $trans_total > 0
            ? ($transiciones_down / $trans_total) * $close_rate_safe
            : 0.0;

        $s_engagement = 1.0 - $eng_pen_sin_pago - $eng_pen_descuento - $eng_pen_enfriamiento;
        $s_engagement = max(0.0, min(1.0, $s_engagement));

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 3: SEGUIMIENTO (30%) — v5
        //  "¿Actúas sobre las señales del Radar?"
        //  Basado en feedback: tasa_completado × calidad
        // ═══════════════════════════════════════════════════

        // Cotizaciones en buckets calientes del vendedor
        $hot_buckets_sql = "('probable_cierre','onfire','inminente','validando_precio','prediccion_alta')";
        $cots_calientes = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp
             AND radar_bucket IN $hot_buckets_sql
             AND estado IN ('enviada','vista')",
            [$usuario_id, $empresa_id]
        );

        // Feedback dado por el vendedor — solo cuenta si:
        // 1. La cotización sigue activa (enviada/vista), o
        // 2. El feedback se dio ANTES de que se aceptara
        $fb_data = DB::query(
            "SELECT rf.cotizacion_id, rf.tipo, c.estado,
                    c.ultima_vista_at, rf.created_at AS fb_at, c.aceptada_at
             FROM radar_feedback rf
             JOIN cotizaciones c ON c.id = rf.cotizacion_id
             WHERE rf.usuario_id=? AND rf.empresa_id=?
             AND c.radar_bucket IN $hot_buckets_sql
             AND (c.estado IN ('enviada','vista')
                  OR (c.aceptada_at IS NOT NULL AND rf.created_at < c.aceptada_at))",
            [$usuario_id, $empresa_id]
        );

        $fb_total = count($fb_data);
        $aciertos = 0.0;
        $fallos = 0.0;
        $inv_cr = 1.0 / $close_rate_safe; // 1/close_rate para escalar

        foreach ($fb_data as $fb) {
            $es_aceptada = in_array($fb['estado'], ['aceptada', 'convertida', 'aceptada_cliente']);
            $dias_desde_fb = $fb['fb_at'] ? (time() - strtotime($fb['fb_at'])) / 86400 : 0;
            $cliente_regreso = false;
            if ($fb['ultima_vista_at'] && $fb['fb_at']) {
                $cliente_regreso = strtotime($fb['ultima_vista_at']) > strtotime($fb['fb_at']);
            }

            // Si pasaron menos de 5 días, no evaluar calidad aún (neutro)
            $evaluable = ($dias_desde_fb >= 5) || $es_aceptada || $cliente_regreso;

            if (!$evaluable) {
                // Feedback reciente sin resultado aún — no cuenta ni como acierto ni fallo
                continue;
            }

            if ($fb['tipo'] === 'con_interes') {
                if ($es_aceptada) {
                    $aciertos += $inv_cr; // con_interes + contrata = máximo acierto
                } elseif ($cliente_regreso) {
                    $aciertos += 1.0;     // con_interes + regresa = buen seguimiento
                } else {
                    $fallos += 1.0;       // con_interes + no regresa (5d+) = fallo
                }
            } else { // sin_interes
                if ($es_aceptada) {
                    $fallos += $inv_cr;   // sin_interes + acepta = fallo grave
                } elseif ($dias_desde_fb >= 5 && !$cliente_regreso) {
                    $aciertos += 1.0;     // sin_interes + 5d sin regresar = acierto
                }
                // sin_interes + <5 días = no evaluable (ya filtrado arriba)
            }
        }

        // Tasa de completado: feedback dado / cotizaciones calientes
        $tasa_completado = $cots_calientes > 0 ? min($fb_total / $cots_calientes, 1.0) : 0.50;

        // Calidad del feedback (solo evaluable después de 5 días)
        $calidad_fb = ($aciertos + $fallos) > 0 ? $aciertos / ($aciertos + $fallos) : 0.50;

        // Penalización por buckets estancados (se mantiene de v4)
        $pen_buckets = min($buckets_estancados * 0.06, 0.3);

        // Seguimiento = tarea (40%) + examen (60%) - estancados
        // Tarea: dar feedback (esfuerzo)
        // Examen: calidad del feedback (resultado)
        $s_seguimiento = ($tasa_completado * 0.40) + ($calidad_fb * 0.60) - $pen_buckets;
        $s_seguimiento = max(0.0, min(1.0, $s_seguimiento));

        // Guardar para debug
        $benchmark_radar = $cots_calientes; // para compatibilidad con debug display

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 3: CONVERSIÓN (45%)
        //  "¿Cierra ventas? Esto es lo que importa."
        //  Volumen alto + cierres bajos = penalización fuerte.
        // ═══════════════════════════════════════════════════

        // Tasa de cierre ya calculada arriba (antes de seguimiento)

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
        $pen_volumen_sin_cierre = 0.0;
        $half_bench = $bench['close_rate'] / 2;
        if ($cierres_total === 0 && $cot_vistas >= 3) {
            $pen_volumen_sin_cierre = min(($cot_vistas - 2) * 0.08, 0.5);
        } elseif ($cot_vistas >= 5 && $tasa_cierre < $half_bench) {
            $pen_volumen_sin_cierre = min((1.0 - $tasa_cierre / $half_bench) * 0.3, 0.3);
        }

        // pen_sin_pago ya calculada en Engagement (eng_pen_sin_pago)

        // close rate + quality + velocidad de cierre
        $pen_conversion = min($pen_vencidas + $pen_zona_muerta + $pen_volumen_sin_cierre, 0.70);
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
        //  SCORE PROPORCIONAL — v5
        //  Pesos: Activación 10%, Engagement 20%, Seguimiento 30%, Conversión 40%
        // ═══════════════════════════════════════════════════

        $w_act  = 0.10;
        $w_eng  = 0.20;
        $w_seg  = 0.30;
        $w_conv = 0.40;

        $proporcional = $s_activacion * $w_act
                      + $s_engagement * $w_eng
                      + $s_seguimiento * $w_seg
                      + $s_conversion * $w_conv;

        // Fix P15: Cap global — las penalizaciones acumuladas no deben dejar
        // el proporcional por debajo de 0.05, para que vendedores malos en
        // distintas dimensiones aún se diferencien entre sí.
        $proporcional = max(0.05, $proporcional);

        // ═══════════════════════════════════════════════════
        //  ÁNGULO 1: MOMENTUM (vs su propio histórico)
        // ═══════════════════════════════════════════════════

        $prev = DB::row(
            "SELECT ema_activacion, ema_seguimiento, ema_conversion, ema_gestion, ema_presencia, updated_at
             FROM usuario_score WHERE usuario_id=?",
            [$usuario_id]
        );

        // Fix 9 + P13: EMA time-weighted con piso y techo equitativo.
        // Alpha escala por horas, pero con min=0.03 (siempre evoluciona algo,
        // incluso en recálculos rápidos) y max=0.25 (no salta demasiado aunque
        // pasen días sin entrar). Esto iguala vendedores frecuentes vs intermitentes.
        $base_alpha = self::EMA_ALPHA;
        $hours_since = ($prev && $prev['updated_at'])
            ? (time() - strtotime($prev['updated_at'])) / 3600.0
            : 24.0;
        $alpha = $base_alpha * min($hours_since / 24.0, 1.0);
        $alpha = max(0.03, min($alpha, 0.25));

        // Fix P17: verificar por updated_at (= ya fue calculado antes), no por
        // valores numéricos. Un vendedor inactivo tiene EMA=0 pero SÍ tiene historial.
        if ($prev && $prev['updated_at']) {
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

        // Fix P14: usar score final guardado (no dimensiones sueltas) para comparar
        // con el equipo. Esto es más justo porque el score final ya incluye momentum
        // y penalizaciones. Para el usuario actual, usar su proporcional recién calculado.
        if ($team_size >= 2) {
            $scores_equipo = [];
            foreach ($team as $t) {
                if ((int)$t['id'] === $usuario_id) {
                    $scores_equipo[] = ['s' => $proporcional, 'me' => true];
                } else {
                    // Usar tasa_gestion (proporcional guardado) en vez de reconstruir
                    // desde dimensiones individuales que pueden estar desfasadas
                    $s_other = (float)($t['sc'] ?? 0) / 100.0;
                    $scores_equipo[] = ['s' => $s_other, 'me' => false];
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
            // Vendedor solo: proporcional domina (80%). Momentum complementa (20%).
            // Permite llegar a Top (90+) con rendimiento consistente.
            // Antes era 65/35 → máximo 82 con momentum estable.
            $final = $proporcional * 0.80 + $momentum_score * 0.20;
        }

        $score = (int)round($final * 100);
        $score = max(0, min(100, $score));

        // Nivel
        if ($score >= 86) $nivel = 'top';
        elseif ($score >= 61) $nivel = 'activo';
        elseif ($score >= 31) $nivel = 'regular';
        else $nivel = 'bajo';

        // Total penalizaciones y bonuses (para display)
        $total_pen = $pen_no_abiertas + ($pen_dormidas * 0.4) + $eng_pen_sin_pago + $eng_pen_descuento + $eng_pen_enfriamiento + $pen_conversion;
        $total_bonus = ($cierre_quality > 0 ? $cierre_quality * 0.2 : 0);

        // ═══════════════════════════════════════════════════
        //  GUARDAR
        // ═══════════════════════════════════════════════════

        DB::execute(
            "INSERT INTO usuario_score
             (usuario_id, empresa_id, score, nivel, dias_activos, acciones, conversiones,
              carga_activa, cot_asignadas, cot_vistas, cot_dormidas,
              cierres_bucket, cierres_sin_dto, transiciones_up, senales_ignoradas,
              radar_views, radar_benchmark, tasa_cierre, ventas_sin_pago,
              s_activacion, s_engagement, eng_pen_sin_pago, eng_pen_descuento, eng_pen_enfriamiento,
              s_seguimiento, s_conversion, penalizaciones, bonuses,
              tasa_gestion,
              ema_gestion, ema_presencia, ema_conversion, ema_activacion, ema_seguimiento,
              momentum, percentil)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
              score=VALUES(score), nivel=VALUES(nivel),
              dias_activos=VALUES(dias_activos), acciones=VALUES(acciones),
              conversiones=VALUES(conversiones), carga_activa=VALUES(carga_activa),
              cot_asignadas=VALUES(cot_asignadas), cot_vistas=VALUES(cot_vistas),
              cot_dormidas=VALUES(cot_dormidas),
              cierres_bucket=VALUES(cierres_bucket), cierres_sin_dto=VALUES(cierres_sin_dto),
              transiciones_up=VALUES(transiciones_up), senales_ignoradas=VALUES(senales_ignoradas),
              radar_views=VALUES(radar_views), radar_benchmark=VALUES(radar_benchmark),
              tasa_cierre=VALUES(tasa_cierre), ventas_sin_pago=VALUES(ventas_sin_pago),
              s_activacion=VALUES(s_activacion), s_engagement=VALUES(s_engagement),
              eng_pen_sin_pago=VALUES(eng_pen_sin_pago), eng_pen_descuento=VALUES(eng_pen_descuento), eng_pen_enfriamiento=VALUES(eng_pen_enfriamiento),
              s_seguimiento=VALUES(s_seguimiento),
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
                $cierres_bucket, $cierres_sin_dto, $transiciones_up, $transiciones_down,
                $fb_total, round($cots_calientes, 1), round($tasa_cierre, 3), $ventas_sin_pago,
                round($s_activacion, 3), round($s_engagement, 3),
                round($eng_pen_sin_pago, 3), round($eng_pen_descuento, 3), round($eng_pen_enfriamiento, 3),
                round($s_seguimiento, 3), round($s_conversion, 3),
                round($total_pen, 3), round($total_bonus, 3),
                round($proporcional, 3),
                // Fix P16: ema_gestion = EMA del proporcional, ema_presencia = EMA de activación
                round($alpha * $proporcional + (1 - $alpha) * (float)($prev['ema_gestion'] ?? $proporcional), 3),
                round($ema_act, 3), round($ema_conv, 3),
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
            'no_abiertas_5d'    => $no_abiertas_5d,
            'pen_no_abiertas'   => round($pen_no_abiertas, 3),
            'cierres_bucket'    => $cierres_bucket,
            'cierres_sin_dto'   => $cierres_sin_dto,
            'transiciones_up'   => $transiciones_up,
            'transiciones_down' => $transiciones_down,
            'senales_ignoradas' => $transiciones_down, // reutilizado para debug display
            'cots_calientes'    => $cots_calientes,
            'fb_total'          => $fb_total,
            'fb_calidad'        => round($calidad_fb, 3),
            's_activacion'      => round($s_activacion, 3),
            's_engagement'      => round($s_engagement, 3),
            'eng_pen_sin_pago'  => round($eng_pen_sin_pago, 3),
            'eng_pen_descuento' => round($eng_pen_descuento, 3),
            'eng_pen_enfriamiento' => round($eng_pen_enfriamiento, 3),
            'ventas_con_descuento' => $ventas_con_descuento,
            's_seguimiento'     => round($s_seguimiento, 3),
            's_conversion'      => round($s_conversion, 3),
            'penalizaciones'    => round($total_pen, 3),
            'bonuses'           => round($total_bonus, 3),
            'tasa_gestion'      => round($proporcional, 3),
            'momentum'          => round($momentum, 2),
            'percentil'         => round($percentil, 2),
            'team_size'         => $team_size,
            // Debug: penalties breakdown
            'pen_dormidas'      => round($pen_dormidas ?? 0, 3),
            'pen_seguimiento'   => round($pen_seguimiento ?? 0, 3),
            'pen_conversion'    => round($pen_conversion ?? 0, 3),
            'pen_sin_pago'      => round($eng_pen_sin_pago ?? 0, 3),
            'ventas_sin_pago'   => $ventas_sin_pago ?? 0,
            'tasa_cierre'       => round($tasa_cierre ?? 0, 3),
            'radar_views'       => $radar_sessions ?? 0,
            'radar_benchmark'   => round($benchmark_radar ?? 0, 1),
            'bench'             => $bench ?? [],
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
        // Actualizar snapshot mensual para reportes históricos
        self::snapshot_mensual($empresa_id);
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
        // v5: calientes sin feedback. Datos vienen como cots_calientes/fb_total (return) o radar_benchmark/radar_views (BD)
        $calientes_diag = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $fb_diag = (int)($s['fb_total'] ?? $s['radar_views'] ?? 0);
        $ign = max(0, $calientes_diag - $fb_diag);
        $cbkt = (int)($s['cierres_bucket'] ?? 0);
        $sdto = (int)($s['cierres_sin_dto'] ?? 0);
        $tup  = (int)($s['transiciones_up'] ?? 0);
        $mom  = (float)($s['momentum'] ?? 1);
        $score = (int)($s['score'] ?? 0);
        $pen  = (float)($s['penalizaciones'] ?? 0);

        // Período de gracia
        if (($s['nivel'] ?? '') === 'nuevo') return 'Recopilando información — score en proceso de activación.';
        // Sin datos suficientes
        if ($asig === 0) return 'Sin cotizaciones en el período.';

        $frases = [];

        // ── ACTIVACIÓN ──
        $tasa_ap = $asig > 0 ? $vist / $asig : 0;
        $nab = (int)($s['no_abiertas_5d'] ?? 0);
        if ($nab > 0) {
            $frases[] = $nab === 1
                ? "1 cotización sin abrir en más de 5 días — dar seguimiento urgente"
                : "$nab cotizaciones sin abrir en más de 5 días — revisar seguimiento";
        } elseif ($tasa_ap >= 0.90) {
            $frases[] = "Casi todo lo que envía llega al cliente";
        } elseif ($tasa_ap >= 0.60) {
            $sin_abrir = $asig - $vist;
            $frases[] = "Buena tasa de entrega, $sin_abrir " . ($sin_abrir === 1 ? "cotización sin abrir" : "cotizaciones sin abrir");
        } elseif ($tasa_ap >= 0.30) {
            $frases[] = "Muchas cotizaciones no se abren — revisar canal de envío";
        } else {
            $frases[] = "La mayoría de sus cotizaciones no llegan al cliente";
        }

        // ── SEGUIMIENTO ──
        // Distinguir: ¿tiene acciones (quote_view+radar) pero 0 cierres desde radar?
        // Si acciones > 0 pero cierres_bucket = 0 y score bajo, es que revisa cotizaciones
        // pero no usa radar específicamente.
        // ── SEGUIMIENTO (feedback) ──
        $fb_calientes = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $fb_dados = (int)($s['fb_total'] ?? $s['radar_views'] ?? 0);
        // Sanitize: fb_dados no puede ser mayor que fb_calientes
        if ($fb_calientes > 0 && $fb_dados > $fb_calientes) $fb_dados = $fb_calientes;
        if ($seg >= 0.70) {
            $frases[] = "buen seguimiento — responde a las señales del radar";
        } elseif ($seg >= 0.35) {
            if ($fb_calientes > 0 && $fb_dados > 0 && $fb_dados < $fb_calientes) {
                $sin_fb = $fb_calientes - $fb_dados;
                $frases[] = "da seguimiento pero falta feedback en $sin_fb señal" . ($sin_fb > 1 ? 'es' : '') . " caliente" . ($sin_fb > 1 ? 's' : '');
            } elseif ($fb_calientes > 0 && $fb_dados >= $fb_calientes) {
                $frases[] = "dio feedback a todas las señales calientes — pendiente evaluación";
            } else {
                $frases[] = "seguimiento moderado — revisar señales calientes del radar";
            }
        } elseif ($seg > 0.05) {
            $frases[] = $fb_calientes > 0
                ? "poco seguimiento — {$fb_calientes} señales calientes sin atender"
                : "poco seguimiento";
        } else {
            $frases[] = "no da seguimiento a las señales del radar";
        }

        // ── CONVERSIÓN ──
        if ($cierres === 0 && $vist >= 3) {
            $frases[] = $vist === 1
                ? "cotiza pero no cierra — 1 abierta sin resultado"
                : "cotiza pero no cierra — $vist abiertas sin resultado";
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
            $frases[] = $dorm === 1
                ? "1 cotización enviada que nadie abrió en 7+ días"
                : "$dorm cotizaciones enviadas que nadie abrió en 7+ días";
        }
        if ($ign > 0) {
            $frases[] = $ign === 1
                ? "1 señal caliente sin feedback — dar seguimiento desde el radar"
                : "$ign señales calientes sin feedback — dar seguimiento desde el radar";
        }
        // Fix P3: Mencionar descuentos si cierra mayormente con descuento
        if ($cierres > 0 && $sdto < $cierres) {
            $con_dto = $cierres - $sdto;
            $pct_dto = round($con_dto / $cierres * 100);
            if ($pct_dto >= 70) {
                $frases[] = "$pct_dto% de cierres con descuento — afecta puntaje de conversión";
            } elseif ($pct_dto >= 40) {
                $frases[] = "$con_dto de $cierres cierres con descuento";
            }
        }

        // ── ENGAGEMENT ──
        $vsp = (int)($s['ventas_sin_pago'] ?? 0);
        if ($vsp > 0) {
            $frases[] = $vsp === 1
                ? "1 venta sin cobrar en más de 5 días — afecta engagement"
                : "$vsp ventas sin cobrar en más de 5 días — afecta engagement";
        }
        $vcd = (int)($s['ventas_con_descuento'] ?? 0);
        if ($vcd > 0 && $cierres > 0) {
            $pct_vcd = round($vcd / $cierres * 100);
            if ($pct_vcd >= 50) {
                $frases[] = "$pct_vcd% de ventas con descuento — mérito de empresa no del vendedor";
            }
        }
        $pen_enf = (float)($s['eng_pen_enfriamiento'] ?? 0);
        if ($pen_enf > 0.3) {
            $frases[] = "pipeline enfriándose — más cotizaciones bajan de bucket que suben";
        }

        // ── MOMENTUM ──
        if ($mom >= 1.20) {
            $frases[] = "tendencia en mejora";
        } elseif ($mom <= 0.80) {
            $frases[] = "tendencia a la baja vs su historial";
        }

        // Fix P5: Meta concreta para subir de nivel
        $nivel_actual = $s['nivel'] ?? 'bajo';
        $score_actual = (int)($s['score'] ?? 0);
        if ($nivel_actual === 'bajo' && $score_actual < 31) {
            $faltan = 31 - $score_actual;
            $frases[] = "necesita $faltan puntos para subir a Regular";
        } elseif ($nivel_actual === 'regular' && $score_actual < 61) {
            $faltan = 61 - $score_actual;
            $frases[] = "a $faltan puntos de nivel Activo";
        } elseif ($nivel_actual === 'activo' && $score_actual < 86) {
            $faltan = 86 - $score_actual;
            $frases[] = "a $faltan puntos de nivel Top";
        }

        // Construir frase final — capitalizar primera letra
        $txt = implode('. ', array_map(fn($f) => ucfirst($f), $frases)) . '.';

        // Recomendación final según el punto más débil
        $dims = ['act' => $act, 'seg' => $seg, 'conv' => $conv];
        $peor = array_keys($dims, min($dims))[0];
        $reco = match($peor) {
            'act'  => $asig - $vist <= 2
                ? ''
                : ' Tip: confirmar WhatsApp/email del cliente, reenviar por otro canal o llamar para verificar recepción.',
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

        // Tasa de cierre de la empresa (vistas → cierres) — excluir suspendidas y borradores
        $emp_vistas = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0
             AND suspendida = 0 AND estado != 'borrador'
             AND (visitas > 0 OR estado IN ('aceptada','convertida','aceptada_cliente'))
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo]
        );
        $emp_cierres = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0
             AND suspendida = 0
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

        // Radar semanal promedio — excluir superadmin (contamina benchmarks de otras empresas)
        $weeks = max($periodo / 7, 1);
        $radar_users = (int)DB::val(
            "SELECT COUNT(DISTINCT al.usuario_id) FROM actividad_log al
             JOIN usuarios u ON u.id = al.usuario_id
             WHERE al.empresa_id=? AND al.tipo='radar_view'
             AND u.rol != 'superadmin'
             AND al.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo]
        );
        $avg_radar = $radar_users >= 2 ? DB::val(
            "SELECT AVG(cnt) FROM (
                SELECT COUNT(*)/{$weeks} AS cnt FROM actividad_log al
                JOIN usuarios u ON u.id = al.usuario_id
                WHERE al.empresa_id=? AND al.tipo='radar_view'
                AND u.rol != 'superadmin'
                AND al.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY al.usuario_id
             ) AS sub",
            [$empresa_id, $periodo]
        ) : null;

        // Para empresas con 1 solo vendedor: pendiente análisis de mejor enfoque
        // Por ahora usa el promedio propio o el default de 2.0

        // Tasa de apertura de la empresa — excluir suspendidas y borradores
        $emp_asig = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0
             AND suspendida = 0 AND estado != 'borrador'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo]
        );
        $apertura = $emp_asig >= 5 ? $emp_vistas / max($emp_asig, 1) : 0.70;

        self::$_bench[$empresa_id] = [
            'close_rate'    => max((float)$close_rate, 0.03),
            'time_to_close' => max((float)($avg_ttc ?? 14), 3),
            'radar_weekly'  => max((float)($avg_radar ?? 2.0), 0.5),
            'apertura'      => max((float)$apertura, 0.30),
        ];
        return self::$_bench[$empresa_id];
    }

    // ─── Snapshot mensual para reportes ────────────────────
    // Guarda/actualiza el snapshot del mes actual para cada vendedor.
    // Se llama al final de recalcular_empresa().
    public static function snapshot_mensual(int $empresa_id): void
    {
        $periodo_actual = date('Y-m');
        $scores = self::equipo($empresa_id);
        $rank = 0;
        $team_size = count($scores);

        foreach ($scores as $s) {
            $rank++;
            try {
                DB::execute(
                    "INSERT INTO score_historial
                     (usuario_id, empresa_id, periodo, score, nivel,
                      s_activacion, s_seguimiento, s_conversion,
                      cot_asignadas, cot_vistas, cot_dormidas,
                      conversiones, cierres_bucket, cierres_sin_dto,
                      transiciones_up, senales_ignoradas,
                      penalizaciones, bonuses, momentum, percentil,
                      ranking, team_size)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE
                      score=VALUES(score), nivel=VALUES(nivel),
                      s_activacion=VALUES(s_activacion), s_engagement=VALUES(s_engagement),
              eng_pen_sin_pago=VALUES(eng_pen_sin_pago), eng_pen_descuento=VALUES(eng_pen_descuento), eng_pen_enfriamiento=VALUES(eng_pen_enfriamiento),
              s_seguimiento=VALUES(s_seguimiento),
                      s_conversion=VALUES(s_conversion),
                      cot_asignadas=VALUES(cot_asignadas), cot_vistas=VALUES(cot_vistas),
                      cot_dormidas=VALUES(cot_dormidas), conversiones=VALUES(conversiones),
                      cierres_bucket=VALUES(cierres_bucket), cierres_sin_dto=VALUES(cierres_sin_dto),
                      transiciones_up=VALUES(transiciones_up), senales_ignoradas=VALUES(senales_ignoradas),
                      penalizaciones=VALUES(penalizaciones), bonuses=VALUES(bonuses),
                      momentum=VALUES(momentum), percentil=VALUES(percentil),
                      ranking=VALUES(ranking), team_size=VALUES(team_size)",
                    [
                        (int)$s['usuario_id'], $empresa_id, $periodo_actual,
                        (int)$s['score'], $s['nivel'],
                        (float)$s['s_activacion'], (float)$s['s_seguimiento'], (float)$s['s_conversion'],
                        (int)($s['cot_asignadas'] ?? 0), (int)($s['cot_vistas'] ?? 0), (int)($s['cot_dormidas'] ?? 0),
                        (int)($s['conversiones'] ?? 0), (int)($s['cierres_bucket'] ?? 0), (int)($s['cierres_sin_dto'] ?? 0),
                        (int)($s['transiciones_up'] ?? 0), (int)($s['senales_ignoradas'] ?? 0),
                        (float)($s['penalizaciones'] ?? 0), (float)($s['bonuses'] ?? 0),
                        (float)($s['momentum'] ?? 1), (float)($s['percentil'] ?? 0.5),
                        $rank, $team_size,
                    ]
                );
            } catch (\Throwable $e) { /* tabla aún no migrada */ }
        }
    }

    // ─── Sigmoid helper ───────────────────────────────────
    // Fix P12: steepness se clampea a [1.5, 8.0] para evitar step-functions
    // con midpoints muy bajos (ej: close_rate=0.03 → steepness=66 → binario)
    // y sigmoid planas con midpoints muy altos.
    private static function sigmoid(float $x, float $midpoint, float $steepness): float
    {
        $steepness = max(1.5, min($steepness, 8.0));
        return 1.0 / (1.0 + exp(-$steepness * ($x - $midpoint)));
    }
}
