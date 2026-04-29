<?php
// ============================================================
//  CotizaApp — core/ActividadScore.php  v5.1
//  APC: Algoritmo de Productividad Comercial (Auto-ajustable)
//
//  5 DIMENSIONES:
//    Activación    (8%)  — ¿las cotizaciones llegan al cliente?
//    Engagement   (17%)  — penalizaciones: sin pago, descuentos, enfriamiento, bajo benchmark
//    Seguimiento  (25%)  — feedback del Radar: tarea (dar) + examen (calidad)
//    Radar Health (15%)  — balance de temperatura del pipeline (up vs down)
//    Conversión   (35%)  — close_rate + calidad + velocidad + consistencia
//
//  AUTO-AJUSTE:
//    - Benchmarks por empresa: close_rate, TTC, radar_weekly, apertura
//    - 1/close_rate amplifica penalizaciones fuertes
//    - close_rate atenúa penalizaciones suaves
//    - sqrt(1/CR) para penalizaciones moderadas
//    - Sin valores fijos: todo escala con datos de la empresa
//    - Pesos finales auto-ajustables: proporcional + momentum + percentil
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
        'lectura_comprometida' => 'caliente',
        'multi_persona'        => 'caliente',
        'alto_importe'         => 'caliente',
        'decision_activa'      => 'caliente',
        'prediccion_alta'      => 'caliente',
        'validando_precio'     => 'caliente',
        'inminente'            => 'caliente',
        'onfire'               => 'caliente',
        'probable_cierre'      => 'caliente',
    ];

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

        // Velocidad de cierre — tiempo promedio vs benchmark empresa
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

        // Transiciones consolidadas (1 query para Engagement + Radar Health)
        $transiciones_up = 0;
        $transiciones_down = 0;
        $health_up_pts = 0.0;
        $health_down_pts = 0.0;
        $health_null_a_caliente = 0;
        $health_caidas_caliente = 0;
        try {
            $all_trans = DB::query(
                "SELECT bt.bucket_anterior, bt.bucket_nuevo, c.estado
                 FROM bucket_transitions bt
                 JOIN cotizaciones c ON c.id = bt.cotizacion_id
                 WHERE bt.vendedor_id=? AND bt.empresa_id=?
                 AND bt.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 AND c.suspendida = 0",
                [$usuario_id, $empresa_id, $periodo]
            );
            $temp_order = ['ninguno' => -1, 'frio' => 0, 'tibio' => 1, 'caliente' => 2];
            foreach ($all_trans as $t) {
                $temp_ant = self::BUCKET_TEMP[$t['bucket_anterior']] ?? ($t['bucket_anterior'] === null ? 'ninguno' : null);
                $temp_new = self::BUCKET_TEMP[$t['bucket_nuevo']] ?? ($t['bucket_nuevo'] === null ? 'ninguno' : null);
                if ($temp_ant === null || $temp_new === null) continue;
                $ord_ant = $temp_order[$temp_ant];
                $ord_new = $temp_order[$temp_new];
                if ($ord_ant === $ord_new) continue;

                $es_aceptada = in_array($t['estado'], ['aceptada', 'convertida', 'aceptada_cliente']);
                if ($es_aceptada) continue;

                $is_up = $ord_new > $ord_ant;
                $is_down = $ord_new < $ord_ant;

                // Engagement: solo transiciones entre buckets reales (sin NULL)
                if ($t['bucket_anterior'] !== null && $t['bucket_nuevo'] !== null) {
                    if ($is_up) $transiciones_up++;
                    elseif ($is_down) $transiciones_down++;
                }

                // Radar Health: con pesos por tipo de movimiento
                if ($is_up) {
                    if ($temp_ant === 'ninguno' && $temp_new === 'caliente') {
                        $health_null_a_caliente++;
                    } elseif ($temp_ant === 'ninguno' && $temp_new === 'tibio') {
                        $health_up_pts += 0.5;
                    } elseif ($temp_ant === 'ninguno' && $temp_new === 'frio') {
                        $health_down_pts += 0.5;
                    } else {
                        $health_up_pts += 1.0;
                    }
                } elseif ($is_down) {
                    if ($temp_new === 'ninguno' && $temp_ant === 'frio') {
                        $health_down_pts += 0.5;
                    } elseif ($temp_new === 'ninguno' && $temp_ant === 'caliente') {
                        $health_caidas_caliente++;
                        $health_down_pts += 1.0;
                    } else {
                        if ($temp_ant === 'caliente') $health_caidas_caliente++;
                        $health_down_pts += 1.0;
                    }
                }
            }
        } catch (\Throwable $e) { /* tabla aún no migrada */ }

        // Multiplicador auto-ajustable para entradas calientes
        $health_mult = $health_null_a_caliente > 0
            ? max(1.0, $health_caidas_caliente / $health_null_a_caliente)
            : 1.0;
        $health_up_pts += $health_null_a_caliente * $health_mult;
        $health_up = (int)round($health_up_pts);
        $health_down = (int)round($health_down_pts);

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
            // v5: descuento ya se penaliza en Engagement, no duplicar aquí
            $puntos_cierre += $base_cierre * $mult_bucket;
        }

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 1: ACTIVACIÓN (8%)
        //  "¿Envías y llegan?"
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
        // Pesos relativos a TTC: dormida 7d con TTC=5d es grave, con TTC=30d es normal
        $pen_dormidas = 0.0;
        $dormidas_solo_7  = $dormidas_7d - $dormidas_14d;
        $dormidas_solo_14 = $dormidas_14d - $dormidas_21d;
        $dormidas_solo_21 = $dormidas_21d;
        if ($asignadas_validas > 0) {
            $ttc = $bench['time_to_close'];
            $w_d7  = 7.0  / $ttc;  // TTC=7: 1.0, TTC=14: 0.5, TTC=30: 0.23
            $w_d14 = 14.0 / $ttc;  // TTC=7: 2.0, TTC=14: 1.0, TTC=30: 0.47
            $w_d21 = 21.0 / $ttc;  // TTC=7: 3.0, TTC=14: 1.5, TTC=30: 0.70
            $pen_dormidas = (
                $dormidas_solo_7  * $w_d7 +
                $dormidas_solo_14 * $w_d14 +
                $dormidas_solo_21 * $w_d21
            ) / $asignadas_validas;
        }
        $pen_dormidas = min($pen_dormidas, 1.0);

        // v5: ratio directo, sin sigmoid, sin piso fijo
        // Dormidas ponderadas por (1 - apertura): si todo se abre, dormidas no pesan.
        // Si apertura es baja (0.50), dormidas pesan 0.50 — compounding real.
        $s_activacion = $tasa_apertura - $pen_no_abiertas - $pen_dormidas * (1.0 - $tasa_apertura);
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
        //  DIMENSIÓN 2: ENGAGEMENT (17%)
        //  Penalizaciones: sin pago, descuentos, enfriamiento, bajo benchmark
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

        // pen_bajo_benchmark: ventas del vendedor vs promedio empresa período anterior
        // Si vendes menos de lo que vendía la empresa por vendedor, penaliza
        $eng_pen_bajo_benchmark = 0.0;
        $ventas_emp_prev = (int)DB::val(
            "SELECT COUNT(*) FROM ventas WHERE empresa_id=? AND estado != 'cancelada'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo * 2, $periodo]
        );
        $sellers_prev = (int)DB::val(
            "SELECT COUNT(DISTINCT COALESCE(vendedor_id, usuario_id)) FROM ventas
             WHERE empresa_id=? AND estado != 'cancelada'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo * 2, $periodo]
        );
        $bench_ventas = $sellers_prev > 0 ? $ventas_emp_prev / $sellers_prev : 0;
        if ($bench_ventas > 0 && $ventas_totales < $bench_ventas) {
            // Deficit escalado por close_rate: más fácil vender → castiga más no vender
            $eng_pen_bajo_benchmark = (1.0 - $ventas_totales / $bench_ventas) * $close_rate_safe;
        }

        $s_engagement = 1.0 - $eng_pen_sin_pago - $eng_pen_descuento - $eng_pen_enfriamiento - $eng_pen_bajo_benchmark;
        $s_engagement = max(0.0, min(1.0, $s_engagement));

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 3: SEGUIMIENTO (25%)
        //  Feedback del Radar: tarea (dar) + examen (calidad)
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

        // Penalización por buckets estancados — ratio puro, sin valor fijo
        // Cada estancado importa más cuando tienes pocas asignadas
        $pen_buckets = $buckets_estancados / $asignadas_validas;

        // Seguimiento: tarea/examen con pesos auto-ajustables
        // Poca data → tarea (dar feedback) importa más (esfuerzo)
        // Mucha data → examen (calidad) importa más (resultado medible)
        $w_tarea  = 1.0;
        $w_examen = max($fb_total, 1);  // 1fb→50/50, 5fb→17/83, 10fb→9/91
        $w_seg_total = $w_tarea + $w_examen;

        $s_seguimiento = ($tasa_completado * $w_tarea + $calidad_fb * $w_examen) / $w_seg_total
                       - $pen_buckets;
        $s_seguimiento = max(0.0, min(1.0, $s_seguimiento));

        // Guardar para debug
        $benchmark_radar = $cots_calientes; // para compatibilidad con debug display

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 4: CONVERSIÓN (35%)
        //  Close rate + calidad + velocidad + tendencia volumen
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
                // v5: descuento penalizado en Engagement, no aquí
                $puntos_con_radar += $base_cierre * (self::CIERRE_MULT[$cc['radar_bucket']] ?? 1.0);
            }
        }
        if ($cierres_con_radar > 0) {
            $avg_mult = $puntos_con_radar / $cierres_con_radar / $base_cierre;
            $cierre_quality = min($avg_mult / 2.0, 1.0);
        } else {
            // Sin datos de radar → neutro
            $cierre_quality = 0.50;
        }

        // Penalizaciones ratio-based — escaladas con sqrt(1/close_rate)
        $sqrt_inv_cr = sqrt(1.0 / $close_rate_safe);
        $pen_zona_muerta = ($zona_muerta / $asignadas_validas) * $sqrt_inv_cr;

        // Volumen sin resultado: déficit vs benchmark de la empresa
        $pen_volumen_sin_cierre = 0.0;
        if ($tasa_cierre < $bench['close_rate'] && $cot_vistas >= 3) {
            $deficit = 1.0 - ($tasa_cierre / max($bench['close_rate'], 0.01));
            $pen_volumen_sin_cierre = $deficit * ($cot_vistas / $asignadas_validas);
        }

        $pen_conversion = min($pen_zona_muerta + $pen_volumen_sin_cierre, 1.0);

        // Tendencia de volumen: ¿vendes más o menos que antes?
        // 1.0 = igual o mejor, 0.0 = cero ventas con benchmark alto
        $vol_trend = $bench_ventas > 0
            ? min($ventas_totales / $bench_ventas, 1.0)
            : 0.50; // sin historial → neutro

        // Sub-pesos auto-ajustables con sqrt para comprimir rango:
        // Peso de cada componente crece con la confiabilidad de sus datos
        $w_cr_conv   = sqrt(max($cot_vistas, 1));            // close_rate: más vistas → más confiable
        $w_qual_conv = sqrt(max($cierres_total, 0) + 1) - 1; // quality: necesita cierres
        $w_ttc_conv  = sqrt(max($cierres_total, 0) + 1) - 1; // velocidad: necesita cierres
        $w_vol_conv  = sqrt(max($bench_ventas, 0));           // tendencia: más historial → más peso
        $w_conv_total = max($w_cr_conv + $w_qual_conv + $w_ttc_conv + $w_vol_conv, 1);

        // Componentes de conversión (close_rate + calidad + velocidad + tendencia)
        $componentes_conv = (
            self::sigmoid($tasa_cierre, $bench['close_rate'], 2.0 / max($bench['close_rate'], 0.01))
                * ($w_cr_conv / $w_conv_total)
            + $cierre_quality * ($w_qual_conv / $w_conv_total)
            + $ttc_score * ($w_ttc_conv / $w_conv_total)
            + $vol_trend * ($w_vol_conv / $w_conv_total)
        );

        // Piso auto-calibrado: proporcional a tu desempeño vs benchmark de la empresa
        // 0 ventas → piso 0 (puede llegar a 0)
        // ventas/bench = 0.5 → piso = 50% de componentes
        // ventas ≥ bench → piso = componentes completos (penalización no aplica)
        // Sin magic numbers — close_rate del vendedor y close_rate empresa son las únicas variables
        $perf_ratio = min($tasa_cierre / max($bench['close_rate'], 0.01), 1.0);
        $conv_floor = $componentes_conv * $perf_ratio;
        $s_conversion = max($conv_floor, $componentes_conv - $pen_conversion);
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

        // Ajustar conversión por consistencia
        // Impacto limitado por sqrt(close_rate): CR bajo → poca reducción max
        // CR=0.10 → max reducción 31%, CR=0.50 → max 71%
        // También necesita suficientes cierres para ser medible
        if ($cierres_total >= 2) {
            $expected_per_week = $cot_asignadas * $close_rate_safe / max($total_semanas, 1);
            $consistency_impact = min($expected_per_week, 1.0);
            $max_reduction = sqrt($close_rate_safe);
            $reduction = min((1.0 - $consistencia) * $consistency_impact, 1.0) * $max_reduction;
            $s_conversion = $s_conversion * (1.0 - $reduction);
            $s_conversion = max(0.0, min(1.0, $s_conversion));
        }

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 5: RADAR HEALTH (15%)
        //  Balance de temperatura del pipeline (up vs down)
        // ═══════════════════════════════════════════════════

        // health_up_pts y health_down_pts ya calculados con pesos en el bloque de transiciones
        $health_total = $health_up_pts + $health_down_pts;
        $s_radar_health = $health_total > 0
            ? $health_up_pts / $health_total
            : 0.50;

        // ═══════════════════════════════════════════════════
        //  SCORE PROPORCIONAL — v5
        //  Pesos: Act 8%, Eng 17%, Seg 25%, Health 15%, Conv 35%
        // ═══════════════════════════════════════════════════

        $w_act  = 0.08;
        $w_eng  = 0.17;
        $w_seg  = 0.25;
        $w_hlt  = 0.15;
        $w_conv = 0.35;

        $proporcional = $s_activacion * $w_act
                      + $s_engagement * $w_eng
                      + $s_seguimiento * $w_seg
                      + $s_radar_health * $w_hlt
                      + $s_conversion * $w_conv;

        // Piso auto-ajustable: vendedores malos se diferencian entre sí
        // Empresas con alto CR tienen piso más alto (promedio más alto)
        $proporcional = max($close_rate_safe * 0.5, $proporcional);

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

            $ema_composite = (float)($prev['ema_gestion'] ?? $proporcional);
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
        //  SCORE FINAL — pesos auto-ajustables
        // ═══════════════════════════════════════════════════
        // Proporcional = siempre la base dominante
        // Percentil = solo útil con equipos grandes, escala con log(team_size)
        //   Con 2 personas el percentil es binario (0/100), no aporta — peso ~7%
        //   Con 5 personas ya diferencia — peso ~16%
        //   Con 10+ — peso ~23%
        // Momentum = complemento basado en close_rate (más cierres → más relevante)

        // Percentil solo con 3+ vendedores (con 2 es binario, no aporta)
        // (n-2)/(n+18): 3→0.05, 5→0.13, 10→0.29→cap 0.25, 20→0.47→cap 0.25
        $w_percentil = $team_size >= 3
            ? min(($team_size - 2) / ($team_size + 18), 0.25)
            : 0.0;
        $w_momentum  = (1.0 - $w_percentil) * $close_rate_safe;
        $w_proporcional = 1.0 - $w_percentil - $w_momentum;

        $final = $proporcional * $w_proporcional
               + $momentum_score * $w_momentum
               + $percentil * $w_percentil;

        $score = (int)round($final * 100);
        $score = max(0, min(100, $score));

        // Nivel
        if ($score >= 86) $nivel = 'top';
        elseif ($score >= 61) $nivel = 'activo';
        elseif ($score >= 31) $nivel = 'regular';
        else $nivel = 'bajo';

        // Total penalizaciones y bonuses (para display)
        $total_pen = $pen_no_abiertas + $pen_dormidas * (1.0 - $tasa_apertura) + $eng_pen_sin_pago + $eng_pen_descuento + $eng_pen_enfriamiento + $eng_pen_bajo_benchmark + $pen_conversion;
        $total_bonus = ($cierre_quality > 0 ? $cierre_quality * $close_rate_safe : 0);

        // ═══════════════════════════════════════════════════
        //  GUARDAR
        // ═══════════════════════════════════════════════════

        DB::execute(
            "INSERT INTO usuario_score
             (usuario_id, empresa_id, score, nivel, dias_activos, acciones, conversiones,
              carga_activa, cot_asignadas, cot_vistas, cot_dormidas,
              cierres_bucket, cierres_sin_dto, transiciones_up, senales_ignoradas,
              radar_views, radar_benchmark, tasa_cierre, ventas_sin_pago, ventas_periodo, bench_ventas,
              s_activacion, s_engagement, eng_pen_sin_pago, eng_pen_descuento, eng_pen_enfriamiento, eng_pen_bajo_benchmark,
              s_seguimiento, s_radar_health, s_conversion, penalizaciones, bonuses,
              tasa_gestion,
              ema_gestion, ema_presencia, ema_conversion, ema_activacion, ema_seguimiento,
              momentum, percentil)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
              score=VALUES(score), nivel=VALUES(nivel),
              dias_activos=VALUES(dias_activos), acciones=VALUES(acciones),
              conversiones=VALUES(conversiones), carga_activa=VALUES(carga_activa),
              cot_asignadas=VALUES(cot_asignadas), cot_vistas=VALUES(cot_vistas),
              cot_dormidas=VALUES(cot_dormidas),
              cierres_bucket=VALUES(cierres_bucket), cierres_sin_dto=VALUES(cierres_sin_dto),
              transiciones_up=VALUES(transiciones_up), senales_ignoradas=VALUES(senales_ignoradas),
              radar_views=VALUES(radar_views), radar_benchmark=VALUES(radar_benchmark),
              tasa_cierre=VALUES(tasa_cierre), ventas_sin_pago=VALUES(ventas_sin_pago), ventas_periodo=VALUES(ventas_periodo), bench_ventas=VALUES(bench_ventas),
              s_activacion=VALUES(s_activacion), s_engagement=VALUES(s_engagement),
              eng_pen_sin_pago=VALUES(eng_pen_sin_pago), eng_pen_descuento=VALUES(eng_pen_descuento), eng_pen_enfriamiento=VALUES(eng_pen_enfriamiento), eng_pen_bajo_benchmark=VALUES(eng_pen_bajo_benchmark),
              s_seguimiento=VALUES(s_seguimiento),
              s_radar_health=VALUES(s_radar_health),
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
                $cierres_bucket, $cierres_sin_dto, $health_up, $health_down,
                $fb_total, round($cots_calientes, 1), round($tasa_cierre, 3), $ventas_sin_pago, $ventas_totales, round($bench_ventas, 1),
                round($s_activacion, 3), round($s_engagement, 3),
                round($eng_pen_sin_pago, 3), round($eng_pen_descuento, 3), round($eng_pen_enfriamiento, 3), round($eng_pen_bajo_benchmark, 3),
                round($s_seguimiento, 3), round($s_radar_health, 3), round($s_conversion, 3),
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
            'eng_pen_bajo_benchmark' => round($eng_pen_bajo_benchmark, 3),
            'bench_ventas'          => round($bench_ventas, 1),
            'ventas_totales'        => $ventas_totales,
            'ventas_con_descuento' => $ventas_con_descuento,
            's_seguimiento'     => round($s_seguimiento, 3),
            's_radar_health'    => round($s_radar_health, 3),
            'health_up'         => $health_up,
            'health_down'       => $health_down,
            's_conversion'      => round($s_conversion, 3),
            'penalizaciones'    => round($total_pen, 3),
            'bonuses'           => round($total_bonus, 3),
            'tasa_gestion'      => round($proporcional, 3),
            'momentum'          => round($momentum, 2),
            'percentil'         => round($percentil, 2),
            'team_size'         => $team_size,
            'w_proporcional'    => round($w_proporcional, 3),
            'w_momentum'        => round($w_momentum, 3),
            'w_percentil'       => round($w_percentil, 3),
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
    public static function diagnostico_ctx(int $empresa_id, int $team_size): array
    {
        $periodo = self::PERIODO;
        $total = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0 AND suspendida=0 AND estado != 'borrador' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [$empresa_id, $periodo]);
        $cerr = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND estado IN ('aceptada','convertida') AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND total > 0", [$empresa_id, $periodo]);

        $mes_actual = (int)date('n');
        $anio_actual = (int)date('Y');

        // Promedio 12 meses y mismo mes año anterior
        $hist = DB::query(
            "SELECT anio, mes, ventas_cantidad, ventas_monto, tasa_cierre
             FROM historial_mensual WHERE empresa_id=?
             ORDER BY anio DESC, mes DESC LIMIT 24",
            [$empresa_id]
        );
        $avg_ventas = 0; $avg_monto = 0; $avg_cierre = 0; $meses_count = 0;
        $mismo_mes_ventas = null; $mismo_mes_monto = null; $mismo_mes_cierre = null;
        $mes_anterior_ventas = null; $mes_anterior_monto = null;
        foreach ($hist as $h) {
            $a = (int)$h['anio']; $m = (int)$h['mes'];
            if ($meses_count < 12 && !($a === $anio_actual && $m === $mes_actual)) {
                $avg_ventas += (int)$h['ventas_cantidad'];
                $avg_monto += (float)$h['ventas_monto'];
                $avg_cierre += (float)$h['tasa_cierre'];
                $meses_count++;
            }
            if ($a === $anio_actual - 1 && $m === $mes_actual && $mismo_mes_ventas === null) {
                $mismo_mes_ventas = (int)$h['ventas_cantidad'];
                $mismo_mes_monto = (float)$h['ventas_monto'];
                $mismo_mes_cierre = (float)$h['tasa_cierre'];
            }
            $mes_ant = $mes_actual - 1 ?: 12;
            $anio_ant = $mes_actual === 1 ? $anio_actual - 1 : $anio_actual;
            if ($a === $anio_ant && $m === $mes_ant && $mes_anterior_ventas === null) {
                $mes_anterior_ventas = (int)$h['ventas_cantidad'];
                $mes_anterior_monto = (float)$h['ventas_monto'];
            }
        }
        if ($meses_count > 0) { $avg_ventas = round($avg_ventas / $meses_count, 1); $avg_monto = round($avg_monto / $meses_count); $avg_cierre = round($avg_cierre / $meses_count, 1); }

        // Monto del mes actual de toda la empresa
        $monto_mes_actual = (float)DB::val(
            "SELECT COALESCE(SUM(total), 0) FROM ventas WHERE empresa_id=? AND estado != 'cancelada' AND YEAR(created_at)=? AND MONTH(created_at)=?",
            [$empresa_id, $anio_actual, $mes_actual]
        );
        $dia_mes = (int)date('j');
        $dias_mes = (int)date('t');

        return [
            'close_rate'         => $total > 0 ? $cerr / $total : 0.10,
            'team_size'          => $team_size,
            'avg_ventas_mes'     => $avg_ventas,
            'avg_monto_mes'      => $avg_monto,
            'avg_cierre'         => $avg_cierre,
            'mismo_mes_ventas'   => $mismo_mes_ventas,
            'mismo_mes_monto'    => $mismo_mes_monto,
            'mismo_mes_cierre'   => $mismo_mes_cierre,
            'mes_anterior_ventas'=> $mes_anterior_ventas,
            'mes_anterior_monto' => $mes_anterior_monto,
            'mes_nombre'         => ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][$mes_actual],
            'monto_mes_actual'   => $monto_mes_actual,
            'dia_mes'            => $dia_mes,
            'dias_mes'           => $dias_mes,
        ];
    }

    public static function diagnostico(array $s, ?array $ctx = null): string
    {
        $act  = (float)($s['s_activacion'] ?? 0);
        $eng  = (float)($s['s_engagement'] ?? 1);
        $seg  = (float)($s['s_seguimiento'] ?? 0);
        $hlt  = (float)($s['s_radar_health'] ?? 0.5);
        $conv = (float)($s['s_conversion'] ?? 0);
        $asig = (int)($s['cot_asignadas'] ?? 0);
        $vist = (int)($s['cot_vistas'] ?? 0);
        $cierres = (int)($s['conversiones'] ?? 0);
        $dorm = (int)($s['cot_dormidas'] ?? 0);
        $calientes_diag = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $fb_diag = (int)($s['fb_total'] ?? $s['radar_views'] ?? 0);
        $ign = max(0, $calientes_diag - $fb_diag);
        $cbkt = (int)($s['cierres_bucket'] ?? 0);
        $sdto = (int)($s['cierres_sin_dto'] ?? 0);
        $mom  = (float)($s['momentum'] ?? 1);
        $score = (int)($s['score'] ?? 0);
        $vsp = (int)($s['ventas_sin_pago'] ?? 0);
        $h_up = (int)($s['health_up'] ?? $s['transiciones_up'] ?? 0);
        $h_down = (int)($s['health_down'] ?? $s['senales_ignoradas'] ?? 0);
        $nab = (int)($s['no_abiertas_5d'] ?? 0);
        $vt_diag = (int)($s['ventas_periodo'] ?? $s['ventas_totales'] ?? 0);
        $bv_diag = (float)($s['bench_ventas'] ?? 0);
        $bench_cr = (float)($ctx['close_rate'] ?? 0);
        $team_size = (int)($ctx['team_size'] ?? 1);
        $avg_ventas_mes = (float)($ctx['avg_ventas_mes'] ?? 0);
        $avg_monto_mes = (float)($ctx['avg_monto_mes'] ?? 0);
        $avg_cierre_hist = (float)($ctx['avg_cierre'] ?? 0);
        $mismo_mes_v = $ctx['mismo_mes_ventas'] ?? null;
        $mismo_mes_m = $ctx['mismo_mes_monto'] ?? null;
        $mes_ant_v = $ctx['mes_anterior_ventas'] ?? null;
        $mes_ant_m = $ctx['mes_anterior_monto'] ?? null;
        $mes_nombre = $ctx['mes_nombre'] ?? '';
        $rot = (((int)($s['usuario_id'] ?? 0)) + (int)date('j')) % 3;

        if (($s['nivel'] ?? '') === 'nuevo') return 'Recopilando información — score en proceso de activación.';
        if ($asig === 0) return 'Sin cotizaciones en el período.';

        $frases = [];
        $tasa_cierre_real = $vist > 0 ? $cierres / $vist : 0;
        $sin_cerrar = max(0, $vist - $cierres);
        $tasa_cierre_pct = round($tasa_cierre_real * 100);
        $bench_cr_pct = round($bench_cr * 100);
        $sin_abrir = $asig - $vist;
        $var_neg = $bench_cr > 0 ? round((1 - $tasa_cierre_real / $bench_cr) * 100) : 0;
        $fb_calientes = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $fb_dados = (int)($s['fb_total'] ?? $s['radar_views'] ?? 0);
        if ($fb_calientes > 0 && $fb_dados > $fb_calientes) $fb_dados = $fb_calientes;
        $cierre_arriba = ($bench_cr > 0 && $tasa_cierre_real > $bench_cr * 1.15 && $team_size >= 2);
        $cierre_abajo = ($bench_cr > 0 && $tasa_cierre_real < $bench_cr * 0.85 && $team_size >= 2);
        $cierre_hist_abajo = ($avg_cierre_hist > 0 && $tasa_cierre_pct < $avg_cierre_hist * 0.80);
        $volumen_bajo = ($avg_ventas_mes > 0 && $team_size > 0 && $vt_diag < ($avg_ventas_mes / $team_size) * 0.7);
        $pocos_cierres = ($cierres > 0 && $cierres <= 2 && $vist >= 10);
        $max_frases = match(true) { $score >= 85 => 4, $score >= 75 => 5, $score >= 70 => 6, $score >= 45 => 7, default => 5 };

        // ═══ 1. TASA DE CIERRE ═══
        if ($cierres === 0 && $vist >= 3) {
            if ($score >= 75) {
                $frases[] = "$vist cotizaciones abiertas, sin cierres en el período.";
            } elseif ($score >= 70) {
                $frases[] = "$vist clientes abrieron tu cotización y ninguno compró. Cierre en 0%.";
            } elseif ($score >= 45) {
                $frases[] = "0 ventas de $vist cotizaciones abiertas. La empresa promedia {$bench_cr_pct}% de cierre. Los números necesitan atención urgente.";
            } else {
                $frases[] = "0 ventas de $vist cotizaciones abiertas. La empresa promedia {$bench_cr_pct}% de cierre.";
            }
        } elseif ($cierres === 0) {
            $frases[] = "Sin cierres en el período.";
        } else {
            $tc = "Tasa de cierre del {$tasa_cierre_pct}%";
            if ($cierre_arriba && $cierres >= 3) $tc .= ", por encima del {$bench_cr_pct}% de la empresa";
            elseif ($cierre_abajo && $score < 70) $tc .= " — la empresa promedia {$bench_cr_pct}%, estás {$var_neg}% abajo";
            elseif ($cierre_abajo) $tc .= ", por debajo del {$bench_cr_pct}% de la empresa";
            $frases[] = "$tc. $cierres de $vist.";
        }

        // ═══ 2. VOLUMEN / RADAR ═══
        if ($pocos_cierres && $score < 80) {
            $v = ["Solo $cierres venta" . ($cierres > 1 ? 's' : '') . " de $vist cotizaciones abiertas — el volumen de cierre necesita subir.", "$sin_cerrar clientes vieron tu propuesta y no compraron."];
            $frases[] = $v[$rot % count($v)];
        } elseif ($sin_cerrar > 5 && $score < 70) {
            $frases[] = "De $vist clientes que abrieron tu cotización, $sin_cerrar no compraron.";
        }
        if ($cbkt > 0) {
            $v = ["$cbkt venta" . ($cbkt > 1 ? 's cerradas' : ' cerrada') . " con apoyo del Radar.", "$cbkt cierre" . ($cbkt > 1 ? 's' : '') . " asistido" . ($cbkt > 1 ? 's' : '') . " por el Radar."];
            $frases[] = $v[$rot % count($v)];
        }

        // ═══ 3. CLIENTES ACTIVOS EN RADAR ═══
        if ($ign > 0) {
            if ($score >= 75) {
                $v = ["El Radar tiene $ign cliente" . ($ign > 1 ? 's' : '') . " con actividad reciente pendiente" . ($ign > 1 ? 's' : '') . " de tu atención.", "$ign cliente" . ($ign > 1 ? 's' : '') . " con actividad en el Radar. Revísalos."];
            } elseif ($score >= 70) {
                $v = ["El Radar detectó $ign cliente" . ($ign > 1 ? 's' : '') . " con actividad reciente que no se " . ($ign > 1 ? 'han' : 'ha') . " atendido — ahí están las oportunidades más inmediatas.", "$ign cliente" . ($ign > 1 ? 's activos' : ' activo') . " en el Radar esperando seguimiento. Cada día que pasa baja la probabilidad de cierre."];
            } else {
                $v = ["El Radar tiene $ign cliente" . ($ign > 1 ? 's activos' : ' activo') . " sin atender. Es lo más cercano a una venta que tienes ahora.", "$ign cliente" . ($ign > 1 ? 's' : '') . " con actividad en el Radar sin atender."];
            }
            $frases[] = $v[$rot % count($v)];
        }

        // ═══ 4. PIPELINE ═══
        if ($h_down > $h_up && $h_down > 2) {
            if ($score >= 75) {
                $frases[] = "$h_down clientes bajando de actividad, $h_up regresaron. Revisa el Radar.";
            } elseif ($score >= 70) {
                $frases[] = "$h_down clientes bajando de actividad contra $h_up que regresaron. Revisa el Radar para identificar a los que aún tienen movimiento.";
            } else {
                $frases[] = "$h_down clientes bajando de actividad, $h_up regresaron. Revisa el Radar.";
            }
        } elseif ($h_up > $h_down && $h_up >= 3) {
            $frases[] = "$h_up clientes con actividad en aumento en el Radar.";
        }

        // ═══ 5. SIN ABRIR / DORMIDAS ═══
        if ($sin_abrir > 0) $frases[] = "$sin_abrir cotización" . ($sin_abrir > 1 ? 'es' : '') . " sin abrir.";
        if ($dorm > 0) $frases[] = "$dorm cotización" . ($dorm > 1 ? 'es' : '') . " lleva" . ($dorm > 1 ? 'n' : '') . " más de 7 días sin abrirse.";

        // ═══ 6. HISTÓRICO ═══
        if ($cierre_hist_abajo && $score < 70) $frases[] = "Tu cierre está por debajo del promedio anual de la empresa.";

        // ═══ 7. COBROS ═══
        if ($vsp > 0) $frases[] = "$vsp venta" . ($vsp > 1 ? 's' : '') . " sin cobrar.";
        if ($cierres > 0 && $sdto < $cierres) { $pct_dto = round(($cierres - $sdto) / $cierres * 100); if ($pct_dto >= 50 && $score < 80) $frases[] = "$pct_dto% de ventas con descuento."; }

        // ═══ 8. TENDENCIA ═══
        if ($mom <= 0.80 && $score < 80) $frases[] = "Tendencia a la baja.";

        // Limitar
        if (count($frases) > $max_frases) $frases = array_slice($frases, 0, $max_frases);

        // ═══ ACCIÓN FINAL ═══
        if ($score >= 85) {
            $v = ["Sigue cotizando y revisa el Radar.", "Cotiza más para mantener el flujo.", "Mantén el ritmo cotizando y revisando el Radar."];
        } elseif ($score >= 75) {
            $v = ["Cotiza más y apóyate en el Radar para priorizar.", "Sigue cotizando. El Radar te ayuda a identificar a quién darle prioridad.", "Cotiza más para generar oportunidades y usa el Radar para dar seguimiento."];
        } elseif ($score >= 70) {
            $v = [" 💡 Entra al Radar, identifica dónde hay actividad y enfócate en cerrar. Cotiza más para generar volumen.", " 💡 El Radar te muestra dónde están las oportunidades. Enfócate en lo que tiene movimiento y sigue cotizando.", " 💡 Cotiza más y revisa el Radar para saber a quién darle seguimiento. El volumen de cotizaciones es lo que genera oportunidades."];
        } elseif ($score >= 45) {
            $v = [" 💡 Los números necesitan atención urgente. Entra al Radar, enfócate en lo que tiene actividad y cotiza más. Cada cotización sin seguimiento es una venta que se pierde.", " 💡 Revisa el Radar, enfócate en cerrar lo que tiene movimiento y sigue cotizando. Los números están por debajo de lo que la empresa necesita.", " 💡 El Radar te muestra dónde hay oportunidad real. Enfócate ahí, cotiza más y trabaja en mejorar el cierre. Los números tienen que subir ya."];
        } else {
            $v = [" 💡 Los números no están dando. Entra al Radar, enfócate y cotiza. Se necesita acción inmediata.", " 💡 La situación es urgente. Revisa el Radar, enfócate en cerrar y cotiza más.", " 💡 Se necesita un cambio. El Radar te muestra dónde hay actividad. Enfócate y cotiza."];
        }
        $frases[] = $v[$rot];

        // ═══ CIERRE: COMPARACIÓN HISTÓRICA EMPRESA ═══
        $monto_actual = (float)($ctx['monto_mes_actual'] ?? 0);
        if ($avg_monto_mes > 0) {
            $ratio_mes = $monto_actual / $avg_monto_mes;
            if ($ratio_mes < 0.5) {
                $v = [
                    "Falta bastante para alcanzar el promedio mensual de ventas de la empresa.",
                    "Las ventas del mes están lejos del promedio mensual de la empresa.",
                    "El mes va por debajo del promedio mensual de la empresa.",
                ];
                $frases[] = $v[$rot];
            } elseif ($ratio_mes < 0.85) {
                $v = [
                    "Las ventas del mes van acercándose al promedio mensual de la empresa.",
                    "Falta para alcanzar el promedio mensual de la empresa.",
                ];
                $frases[] = $v[$rot % count($v)];
            } elseif ($ratio_mes >= 1.0) {
                $v = [
                    "El mes ya alcanzó el promedio mensual de ventas de la empresa.",
                    "Las ventas del mes ya están al nivel del promedio de la empresa.",
                ];
                $frases[] = $v[$rot % count($v)];
            }
        }

        return implode(' ', $frases);
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
