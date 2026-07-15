<?php
// ============================================================
//  CotizaApp — core/ActividadScore.php  v5.1
//  APC: Algoritmo de Productividad Comercial (Auto-ajustable)
//
//  5 DIMENSIONES:
//    Activación   (13%)  — ¿las cotizaciones llegan al cliente?
//    Engagement   (17%)  — penalizaciones: sin pago, descuentos, bajo benchmark
//    Seguimiento  (25%)  — feedback del Radar: tarea (dar) + examen (calidad)
//    Radar Health (10%)  — clientes con interés que se dejan morir
//    Conversión   (35%)  — close_rate + calidad + tendencia + consistencia
//
//  AUTO-AJUSTE:
//    - Benchmarks por empresa: close_rate, close_rate_hist, TTC, ticket_promedio
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

    private const GRACIA_DIAS = 15;

    // ─── Calcular score completo de un usuario ───────────
    /**
     * Período efectivo del score (15d, auto-extendible a 45-60 con ciclo
     * largo) — expuesto para que el widget de cobertura del asesor use la
     * MISMA ventana que lo examina (trampa #1 del doc de integración).
     */
    public static function periodo_efectivo(int $empresa_id): int
    {
        $bench = self::_benchmarks($empresa_id, self::PERIODO);
        if ($bench['time_to_close'] > 20) {
            return (int)min(max(self::PERIODO, $bench['time_to_close'] * 1.5), 60);
        }
        return self::PERIODO;
    }

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
            // Recalcular benchmarks con el período extendido (la caché ahora
            // es por empresa+período — la entrada de 15d queda intacta para
            // periodo_efectivo y los demás vendedores)
            $bench = self::_benchmarks($empresa_id, $periodo);
        }

        // ═══════════════════════════════════════════════════
        //  SEÑALES CRUDAS (últimos 30 días)
        // ═══════════════════════════════════════════════════

        // ── Detectar días de importación masiva (>20 cotizaciones en 1 día) ──
        // Estas cotizaciones no son trabajo real del vendedor. Ventana amplia
        // (365d, no $periodo): no_abiertas_5d no tiene cota inferior de tiempo,
        // así que un import viejo con cotizaciones sin abrir mataría Activación
        // si su día no está en la lista de exclusión. Los consumidores acotados
        // al período (asignadas/vistas/dormidas) ignoran las fechas viejas del
        // NOT IN (ninguna fila vieja matchea su propio filtro de período).
        $import_dates = DB::query(
            "SELECT DATE(created_at) AS d, COUNT(*) AS n
             FROM cotizaciones
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)
             GROUP BY DATE(created_at) HAVING n > 20",
            [$usuario_id, $empresa_id]
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
        // Dormidas: vistas por el cliente pero sin actividad reciente
        // Misma ventana que el período del score
        $dormidas_7d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
             AND estado IN ('enviada','vista') AND visitas > 0
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND COALESCE(ultima_vista_at, created_at) < DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $dormidas_14d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
             AND estado IN ('enviada','vista') AND visitas > 0
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND COALESCE(ultima_vista_at, created_at) < DATE_SUB(NOW(), INTERVAL 14 DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $dormidas_21d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
             AND estado IN ('enviada','vista') AND visitas > 0
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND COALESCE(ultima_vista_at, created_at) < DATE_SUB(NOW(), INTERVAL 21 DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $pago_ok = "AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = cotizaciones.id AND v.pagado > 0 AND v.estado != 'cancelada')";
        $pago_ok_c = "AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = c.id AND v.pagado > 0 AND v.estado != 'cancelada')";
        // Descuento Inteligente = "otro vendedor" (DI). Sus ventas son de la empresa,
        // NO del asesor humano → se excluyen SOLO de las métricas personales del asesor.
        // La vara/benchmark de empresa y las pantallas globales SÍ las cuentan (DI es un
        // vendedor más). Filtro por estado='utilizado': si el admin quita el DI en ventas
        // (→'cancelado'), la venta regresa automática a la matemática del asesor.
        $no_di   = "AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di WHERE di.cotizacion_id = cotizaciones.id AND di.estado='utilizado')";
        $no_di_c = "AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di WHERE di.cotizacion_id = c.id AND di.estado='utilizado')";
        $no_di_v = "AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di WHERE di.cotizacion_id = ventas.cotizacion_id AND di.estado='utilizado')";

        $cierres_total = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_import
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             $pago_ok $no_di",
            [$usuario_id, $empresa_id, $periodo]
        );
        // Cierres con apoyo del Radar: cotización tuvo algún bucket real en su historial.
        // Checa bucket_transitions porque radar_bucket se borra al aceptar.
        // Excluye 'no_abierta' — cierre en vivo sin que el cliente usara el slug.
        $cierres_bucket = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones c WHERE $cw $no_import
             AND c.estado IN ('aceptada','convertida','aceptada_cliente')
             AND c.accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             $pago_ok_c $no_di_c
             AND EXISTS (
                SELECT 1 FROM bucket_transitions bt
                WHERE bt.cotizacion_id = c.id
                  AND bt.bucket_nuevo IS NOT NULL
                  AND bt.bucket_nuevo != 'no_abierta'
             )",
            [$usuario_id, $empresa_id, $periodo]
        );
        $cierres_sin_dto = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_import
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND cupon_pct = 0
             AND descuento_auto_pct = 0
             $pago_ok $no_di",
            [$usuario_id, $empresa_id, $periodo]
        );
        $carga_activa = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp
             AND estado IN ('borrador','enviada','vista')",
            [$usuario_id, $empresa_id]
        );
        // Misma ventana y exclusión de imports que asignadas — pen_buckets divide
        // contra asignadas del período, el numerador debe ser del mismo universo
        $buckets_estancados = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
             AND radar_bucket IS NOT NULL AND radar_bucket != 'no_abierta'
             AND estado IN ('enviada','vista')
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND radar_updated_at < DATE_SUB(NOW(), INTERVAL 14 DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $zona_muerta = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
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

        // ── Lectura de tips del diagnóstico (3 "ver más") ──
        // Cuenta días donde el asesor expandió los 3 "ver más" del diagnóstico
        $dias_lectura = (int)DB::val(
            "SELECT COUNT(*) FROM (
                SELECT DATE(created_at) AS dia
                FROM actividad_log
                WHERE usuario_id=? AND tipo IN ('tip_expand_1','tip_expand_2','tip_expand_3')
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY dia
                HAVING COUNT(DISTINCT tipo) = 3
            ) AS t",
            [$usuario_id, $periodo]
        );
        // Tips reading score. Grace de usuario nuevo ya cubre por early return
        // arriba (línea 140) cuando dias_en_plataforma < GRACIA_DIAS.
        $first_tip_expand = DB::val(
            "SELECT MIN(created_at) FROM actividad_log
             WHERE usuario_id=? AND tipo IN ('tip_expand_1','tip_expand_2','tip_expand_3')",
            [$usuario_id]
        );
        if (!$first_tip_expand) {
            $tips_score = 0.0;
            $dias_activos_feature = 0;
        } else {
            // Auto-calibrado: contar solo días activos DESDE que vio la feature.
            $dias_activos_feature = (int)DB::val(
                "SELECT COUNT(DISTINCT DATE(created_at)) FROM actividad_log
                 WHERE usuario_id=? AND tipo IN ('radar_view','quote_view','client_view')
                 AND created_at >= ?
                 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$usuario_id, $first_tip_expand, $periodo]
            );
            $dias_activos_feature = max($dias_activos_feature, 1);
            $pct_lectura = $dias_lectura / $dias_activos_feature;
            if ($pct_lectura >= 0.70) $tips_score = 1.0;
            elseif ($pct_lectura >= 0.30) $tips_score = 0.50;
            else $tips_score = 0.0;
        }

        // Transiciones up/down: variables legacy. El loop de balance se eliminó —
        // Radar Health ahora mide muerte de calientes (ver Dimensión 5).
        // Se dejan en 0: eng_pen_enfriamiento quedó fuera del score.
        $transiciones_up = 0;
        $transiciones_down = 0;

        // Puntos de cierre ponderados por bucket y descuento
        // Usa último bucket real de bucket_transitions (radar_bucket se borra al aceptar)
        $cierres_con_bucket = DB::query(
            "SELECT COALESCE(
                        (SELECT bt.bucket_nuevo FROM bucket_transitions bt
                         WHERE bt.cotizacion_id = c.id AND bt.bucket_nuevo IS NOT NULL AND bt.bucket_nuevo != 'no_abierta'
                         ORDER BY bt.created_at DESC LIMIT 1),
                        c.radar_bucket
                    ) AS radar_bucket,
                    COALESCE(c.cupon_pct, 0) AS cupon_pct,
                    COALESCE(c.descuento_auto_pct, 0) AS dto_auto_pct
             FROM cotizaciones c
             WHERE COALESCE(c.vendedor_id, c.usuario_id)=? AND c.empresa_id=?
             AND c.estado IN ('aceptada','convertida','aceptada_cliente')
             AND c.accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             $pago_ok_c
             $no_import $no_di_c",
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
        //  DIMENSIÓN 1: ACTIVACIÓN (13%)
        //  "¿Envías y llegan?"
        // ═══════════════════════════════════════════════════

        $asignadas_validas = max($cot_asignadas, 1);
        $tasa_apertura = $cot_vistas / $asignadas_validas;

        // No abiertas en 5+ días — sin ventana, penaliza mientras siga sin abrir
        // (excluye días de importación masiva, igual que asignadas/vistas)
        $no_abiertas_5d = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE $cw $no_susp $no_import
             AND estado='enviada' AND visitas=0
             AND created_at < DATE_SUB(NOW(), INTERVAL 5 DAY)",
            [$usuario_id, $empresa_id]
        );

        // Penalización por no abiertas: 1+ sin abrir en 5d → operativa muere
        $close_rate_safe = max($bench['close_rate'], 0.01);
        $pen_no_abiertas = $no_abiertas_5d > 0 ? 1.0 : 0.0;

        // Dormidas: ratio directo contra vistas, resta de operativa (puede ir negativo)
        $cot_vistas_safe_d = max($cot_vistas, 1);
        $pen_dormidas = $dormidas_7d / $cot_vistas_safe_d;

        // Activación = operativa (50%) + tips (50%)
        // Operativa: 50 puntos base, no_abiertas la mata, dormidas restan encima
        if ($no_abiertas_5d > 0) {
            $s_activacion_op = 0.0 - $pen_dormidas; // negativo posible
        } else {
            $s_activacion_op = $tasa_apertura - $pen_dormidas;
        }
        // NO clamp a 0 — permite negativo para arrastrar score
        $s_activacion_op = min(1.0, $s_activacion_op);
        $s_activacion = ($s_activacion_op * 0.5) + ($tips_score * 0.5);

        // Tasa de cierre (se calcula antes de engagement/seguimiento).
        // Clamp a 1.0: cierres se cuentan por accion_at y vistas por created_at
        // — cerrar backlog viejo puede dar ratio >1 (ventanas distintas)
        $cot_vistas_safe = max($cot_vistas, 1);
        $tasa_cierre = min($cierres_total / $cot_vistas_safe, 1.0);

        // Ventas sin pago inicial (>5 días) — se usa en Engagement.
        // Acotada al período: una venta vieja sin cobrar no debe anular
        // Engagement para siempre (el denominador también es del período)
        $ventas_sin_pago = (int)DB::val(
            "SELECT COUNT(*) FROM ventas
             WHERE COALESCE(vendedor_id, usuario_id) = ? AND empresa_id = ?
             AND pagado = 0 AND estado NOT IN ('cancelada','entregada')
             AND total > 0
             AND created_at < DATE_SUB(NOW(), INTERVAL 5 DAY)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             $no_di_v",
            [$usuario_id, $empresa_id, $periodo]
        );

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 2: ENGAGEMENT (17%)
        //  Penalizaciones: sin pago, descuentos, bajo benchmark
        // ═══════════════════════════════════════════════════

        // Ventas totales del vendedor en el período (no canceladas)
        $ventas_totales = (int)DB::val(
            "SELECT COUNT(*) FROM ventas
             WHERE COALESCE(vendedor_id, usuario_id)=? AND empresa_id=?
             AND estado != 'cancelada' AND pagado > 0
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             $no_di_v",
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
             AND estado != 'cancelada' AND pagado > 0
             AND (descuento_auto_amt > 0 OR cupon_monto > 0)
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $eng_pen_descuento = ($ventas_con_descuento / $ventas_totales_safe) * $close_rate_safe;

        // pen_enfriamiento: legacy — fuera del score. El enfriamiento del pipeline
        // ya no se penaliza (se rehízo en Radar Health). Se deja en 0.
        $eng_pen_enfriamiento = 0.0;

        // pen_bajo_benchmark: ventas del vendedor vs promedio empresa período anterior
        // Si vendes menos de lo que vendía la empresa por vendedor, penaliza
        $eng_pen_bajo_benchmark = 0.0;
        // Superadmin excluido del benchmark (principio v5: no infla promedios)
        $ventas_emp_prev = (int)DB::val(
            "SELECT COUNT(*) FROM ventas v
             LEFT JOIN usuarios u ON u.id = COALESCE(v.vendedor_id, v.usuario_id)
             WHERE v.empresa_id=? AND v.estado != 'cancelada' AND v.pagado > 0
             AND COALESCE(u.rol,'') != 'superadmin'
             AND v.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND v.created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo * 2, $periodo]
        );
        $sellers_prev = (int)DB::val(
            "SELECT COUNT(DISTINCT COALESCE(v.vendedor_id, v.usuario_id)) FROM ventas v
             LEFT JOIN usuarios u ON u.id = COALESCE(v.vendedor_id, v.usuario_id)
             WHERE v.empresa_id=? AND v.estado != 'cancelada' AND v.pagado > 0
             AND COALESCE(u.rol,'') != 'superadmin'
             AND v.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND v.created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo * 2, $periodo]
        );
        $bench_ventas = $sellers_prev > 0 ? $ventas_emp_prev / $sellers_prev : 0;
        if ($bench_ventas > 0 && $ventas_totales < $bench_ventas) {
            // Deficit escalado por close_rate: más fácil vender → castiga más no vender
            $eng_pen_bajo_benchmark = (1.0 - $ventas_totales / $bench_ventas) * $close_rate_safe;
        }

        // pen_enfriamiento NO resta aquí — el enfriamiento del pipeline ya
        // se mide en Radar Health; restarlo también sería doble castigo.
        $s_engagement = 1.0 - $eng_pen_sin_pago - $eng_pen_descuento - $eng_pen_bajo_benchmark;
        $s_engagement = max(0.0, min(1.0, $s_engagement));

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 3: SEGUIMIENTO (25%)
        //  Feedback del Radar: tarea (dar) + examen (calidad)
        // ═══════════════════════════════════════════════════

        // Cotizaciones que FUERON calientes en el período (bucket_transitions)
        // Incluye las que se enfriaron o aceptaron — el asesor debía dar feedback
        $hot_buckets_sql = "('probable_cierre','onfire','inminente','validando_precio','prediccion_alta')";
        $cots_calientes = (int)DB::val(
            "SELECT COUNT(DISTINCT bt.cotizacion_id)
             FROM bucket_transitions bt
             JOIN cotizaciones c ON c.id = bt.cotizacion_id
             WHERE COALESCE(c.vendedor_id, c.usuario_id) = ? AND c.empresa_id = ?
               AND bt.bucket_nuevo IN $hot_buckets_sql
               AND bt.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
               AND c.suspendida = 0",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Feedback dado — incluye cotizaciones que ya perdieron bucket o fueron aceptadas
        $fb_data = DB::query(
            "SELECT rf.cotizacion_id, rf.tipo, c.estado,
                    c.ultima_vista_at, rf.created_at AS fb_at, c.aceptada_at
             FROM radar_feedback rf
             JOIN cotizaciones c ON c.id = rf.cotizacion_id
             WHERE rf.usuario_id=? AND rf.empresa_id=?
             AND c.suspendida = 0
             AND EXISTS (
                SELECT 1 FROM bucket_transitions bt
                WHERE bt.cotizacion_id = c.id AND bt.bucket_nuevo IN $hot_buckets_sql
                  AND bt.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             )",
            [$usuario_id, $empresa_id, $periodo]
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
            } elseif ($fb['tipo'] === 'sin_interes') {
                if ($es_aceptada) {
                    $fallos += $inv_cr;   // sin_interes + acepta = fallo grave
                } elseif ($dias_desde_fb >= 5 && !$cliente_regreso) {
                    $aciertos += 1.0;     // sin_interes + 5d sin regresar = acierto
                }
                // sin_interes + <5 días = no evaluable (ya filtrado arriba)
            }
            // 'sin_info' (📵, cliente no responde) = NEUTRAL: sí cuenta como
            // señal atendida (fb_total → tasa_completado) pero no hay juicio
            // que validar — ni acierto ni fallo.
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

        // ── Exploración ❓ en Radar: multiplicador sobre Seguimiento ──
        // Cuenta cuántas calientes el asesor ha explorado (click ❓) alguna vez.
        // 1 click por cotización es suficiente — no se requiere diario.
        $calientes_exploradas = 0;
        $radar_why_score = 1.0;
        if ($cots_calientes > 0) {
            $calientes_ids = DB::query(
                "SELECT DISTINCT bt.cotizacion_id AS id
                 FROM bucket_transitions bt
                 JOIN cotizaciones c ON c.id = bt.cotizacion_id
                 WHERE COALESCE(c.vendedor_id, c.usuario_id) = ? AND c.empresa_id = ?
                   AND bt.bucket_nuevo IN $hot_buckets_sql
                   AND bt.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                   AND c.suspendida = 0",
                [$usuario_id, $empresa_id, $periodo]
            );
            if (!empty($calientes_ids)) {
                $ids_list = implode(',', array_map(fn($r) => (int)$r['id'], $calientes_ids));
                $calientes_exploradas = (int)DB::val(
                    "SELECT COUNT(DISTINCT ref_id) FROM actividad_log
                     WHERE usuario_id=? AND tipo='radar_why_click'
                     AND ref_id IN ($ids_list)",
                    [$usuario_id]
                );
            }
            // DECISIÓN CEO (11 jul 2026): el ❓ YA NO PENALIZA — la Mesa de
            // Trabajo es el mecanismo de "atiende tus señales" y valdrá el 25%
            // del Seguimiento. El conteo se conserva SOLO como dato informativo
            // (debug panel) y para los consumidores de DiagnosticoTips, que se
            // alimentan también de los taps de la mesa (mesa_estado registra
            // radar_why_click). radar_why_score queda en 1.0 permanente.
        }
        $radar_why_score = 1.0;

        // ═══ SEGUIMIENTO = LA MESA (decisión CEO) ═══
        // Con mesa_activa >= 2, el Seguimiento ES la mesa (100%), no el feedback
        // en calientes (esa medida se calcula arriba pero ya no define Seguimiento;
        // sigue viva solo para empresas SIN mesa). Gate: empresas.mesa_activa
        // (0=off, 1=UI asesores sin score, 2=UI+score). FUENTE ÚNICA de cobertura:
        // Mesa::cobertura_senales — el mismo número que ve el asesor.
        $s_mesa = null; $mesa_pedidas = 0; $mesa_atendidas = 0;
        static $mesa_flag_cache = [];
        if (!array_key_exists($empresa_id, $mesa_flag_cache)) {
            try {
                $mesa_flag_cache[$empresa_id] = (int)DB::val(
                    "SELECT mesa_activa FROM empresas WHERE id = ?", [$empresa_id]);
            } catch (\Throwable $e) { $mesa_flag_cache[$empresa_id] = 0; } // columna sin migrar
        }
        // SEGUIMIENTO = LA MESA (decisión CEO). Si la empresa usa la mesa
        // (mesa_activa >= 2), el Seguimiento ES la cobertura de la mesa (lista
        // COMPLETA, sin tope — opción A), no la medida vieja de feedback-en-
        // calientes (esa se sigue calculando arriba porque otras cosas del panel
        // la usan, pero ya NO define el Seguimiento).
        // PROPORCIONAL 3 escalones: cobertura = atendidas/pedidas (atendida =
        // feedback 👍👎 + postura).
        //   · <50%        → 0.0   (incluye "atendió 0")
        //   · 50% a 80%   → 0.50
        //   · ≥80%        → 1.0
        //   · Mesa vacía (pedidas=0) → 1.0 neutro (no hay examen).
        if ($mesa_flag_cache[$empresa_id] >= 2) {
            $cob = Mesa::cobertura_senales($empresa_id, $usuario_id, $periodo);
            if (empty($cob['error'])) { // fail-neutral: error SQL ≠ Seguimiento regalado
                $mesa_pedidas   = $cob['pedidas'];
                $mesa_atendidas = $cob['atendidas'];
                $cov = $cob['pedidas'] > 0 ? $cob['atendidas'] / $cob['pedidas'] : 1.0;
                $s_mesa = $cob['pedidas'] === 0 ? 1.0
                        : ($cov >= 0.80 ? 1.0 : ($cov >= 0.50 ? 0.50 : 0.0));
                $s_seguimiento = $s_mesa;
            }
        }

        // Guardar para debug
        $benchmark_radar = $cots_calientes; // para compatibilidad con debug display

        // ═══════════════════════════════════════════════════
        //  DIMENSIÓN 4: CONVERSIÓN (35%)
        //  Close rate + calidad + tendencia volumen
        // ═══════════════════════════════════════════════════

        // Tasa de cierre ya calculada arriba (antes de seguimiento)

        // Calidad de cierre: multiplicador promedio normalizado.
        // Sin bucket (cierre directo) = mult 1.0 → quality 0.50 base.
        // Rescata venta fría (2.0) → quality 1.0. Solo fáciles (0.8) → quality 0.40.
        // Cierres sin bucket no castigan: se normaliza sobre cierres CON bucket si los hay.
        $cierres_con_radar = 0;
        $puntos_con_radar = 0.0;
        foreach ($cierres_con_bucket as $cc) {
            // Excluir 'no_abierta': cierre en vivo del asesor, no apoyo del Radar
            if ($cc['radar_bucket'] !== null && $cc['radar_bucket'] !== 'no_abierta') {
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
        if ($tasa_cierre < $bench['close_rate_hist'] && $cot_vistas >= 3) {
            $deficit = 1.0 - ($tasa_cierre / max($bench['close_rate_hist'], 0.01));
            $pen_volumen_sin_cierre = $deficit * ($cot_vistas / $asignadas_validas);
        }

        $pen_conversion = min($pen_zona_muerta + $pen_volumen_sin_cierre, 1.0);

        // Tendencia de volumen: ¿vendes más o menos que antes?
        // 1.0 = igual o mejor, 0.0 = cero ventas con benchmark alto
        $vol_trend = $bench_ventas > 0
            ? min($ventas_totales / $bench_ventas, 1.0)
            : 0.50; // sin historial → neutro

        // Sub-pesos auto-ajustables con sqrt para comprimir rango:
        // Peso de cada componente crece con la confiabilidad de sus datos.
        // close_rate absorbe el peso que tenía velocidad — cerrar rápido ya no
        // se mide aparte: lo importante es cerrar, no la velocidad.
        $w_cr_conv   = sqrt(max($cot_vistas, 1)) + (sqrt(max($cierres_total, 0) + 1) - 1); // close_rate: vistas + peso heredado de velocidad
        $w_qual_conv = sqrt(max($cierres_total, 0) + 1) - 1; // quality: necesita cierres
        $w_vol_conv  = sqrt(max($bench_ventas, 0));           // tendencia: más historial → más peso
        $w_conv_total = max($w_cr_conv + $w_qual_conv + $w_vol_conv, 1);

        // Componentes de conversión (close_rate + calidad + tendencia)
        $componentes_conv = (
            self::sigmoid($tasa_cierre, $bench['close_rate_hist'], 2.0 / max($bench['close_rate_hist'], 0.01))
                * ($w_cr_conv / $w_conv_total)
            + $cierre_quality * ($w_qual_conv / $w_conv_total)
            + $vol_trend * ($w_vol_conv / $w_conv_total)
        );

        // Piso auto-calibrado: proporcional a tu desempeño vs benchmark de la empresa
        // 0 ventas → piso 0 (puede llegar a 0)
        // ventas/bench = 0.5 → piso = 50% de componentes
        // ventas ≥ bench → piso = componentes completos (penalización no aplica)
        // Sin magic numbers — close_rate del vendedor y close_rate empresa son las únicas variables
        $perf_ratio = min($tasa_cierre / max($bench['close_rate_hist'], 0.01), 1.0);
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
             AND total > 0 $no_import
             $pago_ok $no_di",
            [$usuario_id, $empresa_id, $periodo]
        );
        $total_semanas = max(round($periodo / 7), 1);
        // Ratio: 4/4 semanas con cierre = 1.0, 1/4 = 0.25.
        // min(1.0): una ventana de 15 días abarca 3 semanas ISO pero
        // total_semanas asume 2 — sin el tope, consistencia pasa de 1.0,
        // reduction se vuelve negativa e infla s_conversion en vez de penalizar.
        $consistencia = $cierres_total > 0 ? min($semanas_con_cierre / $total_semanas, 1.0) : 0;

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
        //  DIMENSIÓN 5: RADAR HEALTH (10%)
        //  Salud del radar: ¿cuántos clientes con interés dejas morir?
        //  Caliente = el cliente ve la cotización con interés.
        //  Murió = perdió el bucket por completo, sin cerrar → la soltaste.
        //  s = 1 − muertas/calientes. Mide acción del vendedor, no temporada.
        // ═══════════════════════════════════════════════════

        $cal_buckets = "'re_enganche_caliente','lectura_comprometida','multi_persona',"
                     . "'alto_importe','decision_activa','prediccion_alta',"
                     . "'validando_precio','inminente','onfire','probable_cierre'";

        $rh = DB::row(
            "SELECT
                COUNT(DISTINCT bt.cotizacion_id) AS calientes,
                COUNT(DISTINCT CASE WHEN c.estado NOT IN ('aceptada','convertida','aceptada_cliente')
                                     AND c.radar_bucket IS NULL
                                    THEN bt.cotizacion_id END) AS muertas
             FROM bucket_transitions bt
             JOIN cotizaciones c ON c.id = bt.cotizacion_id
             WHERE COALESCE(c.vendedor_id, c.usuario_id) = ? AND c.empresa_id = ?
               AND c.suspendida = 0
               AND bt.bucket_nuevo IN ($cal_buckets)
               AND bt.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );
        $rh_calientes = (int)($rh['calientes'] ?? 0);
        $rh_muertas   = (int)($rh['muertas'] ?? 0);
        // Sin calientes en el período → neutro (nada que evaluar)
        $s_radar_health = $rh_calientes > 0
            ? 1.0 - ($rh_muertas / $rh_calientes)
            : 0.50;
        $s_radar_health = max(0.0, min(1.0, $s_radar_health));
        // Persistencia/debug (reusa columnas legacy transiciones_up/senales_ignoradas)
        $health_up   = $rh_calientes;
        $health_down = $rh_muertas;

        // ═══════════════════════════════════════════════════
        //  SCORE PROPORCIONAL — v5
        //  Pesos: Act 13%, Eng 17%, Seg 25%, Health 10%, Conv 35%
        // ═══════════════════════════════════════════════════

        $w_act  = 0.13;
        $w_eng  = 0.17;
        $w_seg  = 0.25;
        $w_hlt  = 0.10;
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
            "SELECT ema_activacion, ema_seguimiento, ema_conversion, ema_engagement, ema_radar_health, ema_gestion, ema_presencia, updated_at
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
            $ema_eng  = $alpha * $s_engagement   + (1 - $alpha) * (float)($prev['ema_engagement'] ?? $s_engagement);
            $ema_hlt  = $alpha * $s_radar_health + (1 - $alpha) * (float)($prev['ema_radar_health'] ?? $s_radar_health);

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
            $ema_eng  = $s_engagement;
            $ema_hlt  = $s_radar_health;
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
                    us.tasa_gestion AS tg
             FROM usuarios u
             LEFT JOIN usuario_score us ON us.usuario_id = u.id
             WHERE u.empresa_id = ? AND u.activo = 1
             AND u.rol != 'superadmin'
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

        // Comparar manzanas con manzanas: el usuario actual entra con su
        // proporcional recién calculado y los demás con su tasa_gestion
        // persistida (= su proporcional del último recálculo). El score/100
        // no es comparable: ya trae momentum, percentil y bonuses.
        if ($team_size >= 2) {
            $scores_equipo = [];
            foreach ($team as $t) {
                if ((int)$t['id'] === $usuario_id) {
                    $scores_equipo[] = ['s' => $proporcional, 'me' => true];
                } else {
                    $s_other = $t['tg'] !== null ? (float)$t['tg'] : (float)($t['sc'] ?? 0) / 100.0;
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

        // ═══════════════════════════════════════════════════
        //  BONUS TICKET ALTO
        //  Premia ventas por encima del ticket promedio histórico
        //  1.5x → +2, 2x → +5, 3x → +8, tope 15
        // ═══════════════════════════════════════════════════
        $bonus_ticket = 0;
        $bonus_ticket_ventas = 0;
        $ticket_prom = $bench['ticket_promedio'];
        if ($ticket_prom > 0) {
            $ventas_periodo_montos = DB::query(
                "SELECT total FROM ventas
                 WHERE COALESCE(vendedor_id, usuario_id) = ? AND empresa_id = ?
                   AND estado != 'cancelada' AND total > 0 AND pagado > 0
                   AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                   $no_di_v",
                [$usuario_id, $empresa_id, $periodo]
            );
            foreach ($ventas_periodo_montos as $vpm) {
                $mult = (float)$vpm['total'] / $ticket_prom;
                $pts = 0;
                if ($mult >= 3.0) $pts = 8;
                elseif ($mult >= 2.0) $pts = 5;
                elseif ($mult >= 1.5) $pts = 2;
                if ($pts > 0) {
                    $bonus_ticket += $pts;
                    $bonus_ticket_ventas++;
                }
            }
            $bonus_ticket = min($bonus_ticket, 15);
        }
        $score = min($score + $bonus_ticket, 100);

        // ═══════════════════════════════════════════════════
        //  BONUS POR CIERRE SOBRE HISTÓRICO
        //  Premia al vendedor que sobresale: tasa de cierre del
        //  periodo muy por encima de su histórico. Un solo tier
        //  (el más alto alcanzado), nunca acumulable. Requiere
        //  volumen real de cierres para no premiar un mes flaco.
        // ═══════════════════════════════════════════════════
        $bonus_cierre = 0;
        if ($cierres_total >= 4 && $bench['close_rate_hist'] > 0) {
            $ratio_cierre = $tasa_cierre / $bench['close_rate_hist'];
            if ($ratio_cierre >= 4.0)     $bonus_cierre = 8;
            elseif ($ratio_cierre >= 2.5) $bonus_cierre = 4;
        }
        $score = min($score + $bonus_cierre, 100);

        // Nivel
        if ($score >= 86) $nivel = 'top';
        elseif ($score >= 61) $nivel = 'activo';
        elseif ($score >= 31) $nivel = 'regular';
        else $nivel = 'bajo';

        // Total penalizaciones ponderadas (impacto real en score) y bonuses
        $total_pen = ($pen_no_abiertas + $pen_dormidas) * $w_act
                   + ($eng_pen_sin_pago + $eng_pen_descuento + $eng_pen_bajo_benchmark) * $w_eng
                   + $pen_conversion * $w_conv;
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
              no_abiertas_5d, pen_no_abiertas, pen_dormidas, dias_activos_feature,
              s_activacion, s_activacion_op, tips_score, dias_lectura, radar_why_score, calientes_exploradas, s_engagement, eng_pen_sin_pago, eng_pen_descuento, eng_pen_enfriamiento, eng_pen_bajo_benchmark,
              s_seguimiento, s_radar_health, s_conversion, penalizaciones, bonuses,
              tasa_gestion,
              ema_gestion, ema_presencia, ema_conversion, ema_activacion, ema_seguimiento, ema_engagement, ema_radar_health,
              momentum, percentil, bonus_ticket, bonus_ticket_ventas, ticket_promedio, bonus_cierre,
              mesa_pedidas, mesa_atendidas, s_mesa)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
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
              no_abiertas_5d=VALUES(no_abiertas_5d), pen_no_abiertas=VALUES(pen_no_abiertas), pen_dormidas=VALUES(pen_dormidas), dias_activos_feature=VALUES(dias_activos_feature),
              s_activacion=VALUES(s_activacion), s_activacion_op=VALUES(s_activacion_op), tips_score=VALUES(tips_score), dias_lectura=VALUES(dias_lectura),
              radar_why_score=VALUES(radar_why_score), calientes_exploradas=VALUES(calientes_exploradas),
              s_engagement=VALUES(s_engagement),
              eng_pen_sin_pago=VALUES(eng_pen_sin_pago), eng_pen_descuento=VALUES(eng_pen_descuento), eng_pen_enfriamiento=VALUES(eng_pen_enfriamiento), eng_pen_bajo_benchmark=VALUES(eng_pen_bajo_benchmark),
              s_seguimiento=VALUES(s_seguimiento),
              s_radar_health=VALUES(s_radar_health),
              s_conversion=VALUES(s_conversion),
              penalizaciones=VALUES(penalizaciones), bonuses=VALUES(bonuses),
              tasa_gestion=VALUES(tasa_gestion),
              ema_gestion=VALUES(ema_gestion), ema_presencia=VALUES(ema_presencia),
              ema_conversion=VALUES(ema_conversion),
              ema_activacion=VALUES(ema_activacion), ema_seguimiento=VALUES(ema_seguimiento),
              ema_engagement=VALUES(ema_engagement), ema_radar_health=VALUES(ema_radar_health),
              momentum=VALUES(momentum), percentil=VALUES(percentil),
              bonus_ticket=VALUES(bonus_ticket), bonus_ticket_ventas=VALUES(bonus_ticket_ventas), ticket_promedio=VALUES(ticket_promedio), bonus_cierre=VALUES(bonus_cierre),
              mesa_pedidas=VALUES(mesa_pedidas), mesa_atendidas=VALUES(mesa_atendidas), s_mesa=VALUES(s_mesa),
              updated_at=NOW()",
            [
                $usuario_id, $empresa_id, $score, $nivel,
                $dias_activos, $consultas + $radar_sessions, $cierres_total,
                $carga_activa, $cot_asignadas, $cot_vistas, $dormidas_7d,
                $cierres_bucket, $cierres_sin_dto, $health_up, $health_down,
                $fb_total, round($cots_calientes, 1), round($tasa_cierre, 3), $ventas_sin_pago, $ventas_totales, round($bench_ventas, 1),
                $no_abiertas_5d, round($pen_no_abiertas, 3), round($pen_dormidas, 3), $dias_activos_feature,
                round($s_activacion, 3), round($s_activacion_op, 3), round($tips_score, 2), $dias_lectura,
                round($radar_why_score, 2), $calientes_exploradas,
                round($s_engagement, 3),
                round($eng_pen_sin_pago, 3), round($eng_pen_descuento, 3), round($eng_pen_enfriamiento, 3), round($eng_pen_bajo_benchmark, 3),
                round($s_seguimiento, 3), round($s_radar_health, 3), round($s_conversion, 3),
                round($total_pen, 3), round($total_bonus, 3),
                round($proporcional, 3),
                // Fix P16: ema_gestion = EMA del proporcional, ema_presencia = EMA de activación
                round($alpha * $proporcional + (1 - $alpha) * (float)($prev['ema_gestion'] ?? $proporcional), 3),
                round($ema_act, 3), round($ema_conv, 3),
                round($ema_act, 3), round($ema_seg, 3),
                round($ema_eng, 3), round($ema_hlt, 3),
                round($momentum, 2), round($percentil, 2),
                $bonus_ticket, $bonus_ticket_ventas, round($ticket_prom, 2), $bonus_cierre,
                $mesa_pedidas, $mesa_atendidas, ($s_mesa === null ? null : round($s_mesa, 2)),
            ]
        );

        return [
            'usuario_id'        => $usuario_id,
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
            'radar_why_score'   => round($radar_why_score, 2),
            'calientes_exploradas' => $calientes_exploradas,
            's_activacion'      => round($s_activacion, 3),
            's_activacion_op'   => round($s_activacion_op, 3),
            'tips_score'        => round($tips_score, 2),
            'dias_lectura'      => $dias_lectura,
            's_engagement'      => round($s_engagement, 3),
            'eng_pen_sin_pago'  => round($eng_pen_sin_pago, 3),
            'eng_pen_descuento' => round($eng_pen_descuento, 3),
            'eng_pen_enfriamiento' => round($eng_pen_enfriamiento, 3),
            'eng_pen_bajo_benchmark' => round($eng_pen_bajo_benchmark, 3),
            'bench_ventas'          => round($bench_ventas, 1),
            'ventas_totales'        => $ventas_totales,
            'ventas_con_descuento' => $ventas_con_descuento,
            's_seguimiento'     => round($s_seguimiento, 3),
            's_mesa'            => ($s_mesa === null ? null : round($s_mesa, 2)),
            'mesa_pedidas'      => $mesa_pedidas,
            'mesa_atendidas'    => $mesa_atendidas,
            'ventas_periodo'    => $ventas_totales,
            's_radar_health'    => round($s_radar_health, 3),
            'health_up'         => $health_up,
            'health_down'       => $health_down,
            's_conversion'      => round($s_conversion, 3),
            'penalizaciones'    => round($total_pen, 3),
            'bonuses'           => round($total_bonus, 3),
            'bonus_ticket'      => $bonus_ticket,
            'bonus_ticket_ventas' => $bonus_ticket_ventas,
            'bonus_cierre'      => $bonus_cierre,
            'ticket_promedio'   => round($ticket_prom, 2),
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
        // Punto diario (1 por asesor por día) → base del promedio mensual real.
        // Sin cron: se registra cada vez que un admin abre el dashboard/radar.
        self::snapshot_diario($empresa_id);
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
        $cerr = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND estado IN ('aceptada','convertida','aceptada_cliente') AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND total > 0 AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = cotizaciones.id AND v.pagado > 0 AND v.estado != 'cancelada')", [$empresa_id, $periodo]);

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
            "SELECT COALESCE(SUM(total), 0) FROM ventas WHERE empresa_id=? AND estado != 'cancelada' AND pagado > 0 AND YEAR(created_at)=? AND MONTH(created_at)=?",
            [$empresa_id, $anio_actual, $mes_actual]
        );
        $dia_mes = (int)date('j');
        $dias_mes = (int)date('t');

        return [
            'close_rate'         => $total > 0 ? $cerr / $total : 0.10,
            'mesa_activa'        => (function () use ($empresa_id) {
                try { return (int)DB::val("SELECT mesa_activa FROM empresas WHERE id = ?", [$empresa_id]); }
                catch (\Throwable $e) { return 0; } // columna aún no migrada
            })(),
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
        // Motor nuevo (voz de gerente comercial — core/DiagnosticoTips.php).
        // Ante cualquier edge case que lance, cae al legacy para no romper el
        // dashboard. Para revertir del todo: comentar este bloque try.
        try {
            $txt = DiagnosticoTips::build($s, $ctx);
            if (is_string($txt) && trim($txt) !== '') return $txt;
        } catch (\Throwable $e) {
            if (defined('DEBUG') && DEBUG) throw $e;
        }
        return self::_diagnostico_legacy($s, $ctx);
    }

    // Texto 1 (números) — el análisis del checkpoint original.
    public static function diagnostico_numeros(array $s, ?array $ctx = null): string
    {
        if (($s['nivel'] ?? '') === 'nuevo') return '';
        return self::_diagnostico_legacy($s, $ctx);
    }

    private static function _diagnostico_legacy(array $s, ?array $ctx = null): string
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

        // ═══ 0. USO DE HERRAMIENTAS (tips + ❓) ═══
        $tips_s = (float)($s['tips_score'] ?? 1);
        $dias_act_d = (int)($s['dias_activos'] ?? 0);
        // Para ❓: usar datos REALES (calientes/exploradas) no el score (que tiene grace period)
        // Esto permite que la frase SIEMPRE avise incluso si el score no penaliza.
        $cal_diag = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $exp_diag = (int)($s['calientes_exploradas'] ?? 0);
        if ($dias_act_d > 0) {
            // Estado de tips: 'ok' (1.0), 'medio' (0.5), 'no' (0.0)
            $tip_state = $tips_s >= 1.0 ? 'ok' : ($tips_s <= 0.0 ? 'no' : 'medio');
            // Estado de ❓: basado en datos reales, no en score (que tiene grace period)
            if ($cal_diag === 0) {
                $why_state = 'ok'; // sin calientes, nada que explorar
            } else {
                $pct_exp = $exp_diag / $cal_diag;
                if ($pct_exp >= 0.70) $why_state = 'ok';
                elseif ($pct_exp >= 0.30) $why_state = 'medio';
                else $why_state = 'no';
            }

            $partes_neg = [];
            if ($tip_state === 'no') $partes_neg[] = 'no lees los tips';
            elseif ($tip_state === 'medio') $partes_neg[] = 'lees los tips a medias';
            $mesa_on_leg = (int)($ctx['mesa_activa'] ?? 0) >= 1;
            $donde_sen = $mesa_on_leg ? 'de tu mesa' : 'del Radar';
            if ($why_state === 'no') $partes_neg[] = "no exploras las señales {$donde_sen}";
            elseif ($why_state === 'medio') $partes_neg[] = "exploras a medias las señales {$donde_sen}";

            if (!empty($partes_neg)) {
                $first = mb_strtoupper(mb_substr($partes_neg[0], 0, 1)) . mb_substr($partes_neg[0], 1);
                if (count($partes_neg) === 1) {
                    // Cierre con call to action si es solo ❓
                    if ($why_state !== 'ok') {
                        $frases[] = $first . ($mesa_on_leg
                            ? ' — abre tu mesa y dales un toque a las calientes.'
                            : ' — usa el ❓ en tus cotizaciones calientes.');
                    } else {
                        $frases[] = $first . ' — revisa el análisis completo.';
                    }
                } else {
                    $frases[] = $first . ' y ' . $partes_neg[1] . '.';
                }
            }
        }

        // ═══ 1. TASA DE CIERRE ═══
        if ($cierres === 0 && $vist >= 3) {
            if ($score >= 75) {
                $frases[] = "$vist cotizaciones abiertas, sin cierres en el período.";
            } elseif ($score >= 70) {
                $frases[] = "$vist clientes abrieron tu cotización y ninguno compró. Cierre en 0%.";
            } elseif ($score >= 45) {
                // Solo citar el promedio si el contexto lo trae de verdad (>0)
                $ref = $bench_cr > 0 ? " La empresa promedia {$bench_cr_pct}% de cierre." : "";
                $frases[] = "0 ventas de $vist cotizaciones abiertas.$ref Los números necesitan atención urgente.";
            } else {
                $ref = $bench_cr > 0 ? " La empresa promedia {$bench_cr_pct}% de cierre." : "";
                $frases[] = "0 ventas de $vist cotizaciones abiertas.$ref";
            }
        } elseif ($cierres === 0) {
            $frases[] = "Sin cierres en el período.";
        } else {
            $tc = "Tasa de cierre del {$tasa_cierre_pct}%";
            if ($cierre_arriba && $cierres >= 3) $tc .= ", por encima del {$bench_cr_pct}% de la empresa";
            elseif ($cierre_abajo && $score < 70) $tc .= " — la empresa promedia {$bench_cr_pct}%, estás {$var_neg}% abajo";
            elseif ($cierre_abajo) $tc .= ", por debajo del {$bench_cr_pct}% de la empresa";
            $frases[] = "$tc. Cerraste $cierres de $vist cotizaciones vistas.";
        }

        // ═══ 2. SIN ABRIR / DORMIDAS (prioridad alta, no deben cortarse) ═══
        if ($sin_abrir > 0) $frases[] = "$sin_abrir " . ($sin_abrir > 1 ? 'cotizaciones' : 'cotización') . " sin abrir.";
        if ($nab > 0) $frases[] = "⚠️ $nab " . ($nab > 1 ? 'cotizaciones' : 'cotización') . " sin abrir en más de 5 días — penaliza tu score.";
        if ($dorm > 0) $frases[] = "$dorm " . ($dorm > 1 ? 'cotizaciones' : 'cotización') . " donde el cliente no regresa en 7+ días.";

        // ═══ 3. RADAR (apoyo en cierres) ═══
        // (Se quitó el restatement "N no compraron" — la tasa de cierre de arriba ya lo dice.)
        if ($cierres > 0 && $cbkt === $cierres) {
            // Todas las ventas con apoyo del Radar — fortaleza
            $v = $cierres === 1
                ? ["Tu venta se cerró con apoyo del Radar.", "La venta vino asistida por el Radar."]
                : ["Tus $cierres ventas se cerraron con apoyo del Radar.", "Las $cierres ventas vinieron asistidas por el Radar."];
            $frases[] = $v[$rot % count($v)];
        } elseif ($cbkt > 0 && $cbkt < $cierres) {
            // Mezcla: parte con radar, parte fuera
            $frases[] = "$cbkt de $cierres ventas con apoyo del Radar.";
        } elseif ($cierres > 0 && $cbkt === 0) {
            // Vendió sin apoyo del Radar — pueden ser referidos o cotizaciones que el cliente no usó
            $frases[] = "$cierres cierre" . ($cierres > 1 ? 's' : '') . " en frío sin pasar por el Radar — (referidos o cotización no usada).";
        }

        // ═══ 4. CLIENTES ACTIVOS EN RADAR ═══
        if ($ign > 0) {
            if ($score >= 75) {
                $v = ["El Radar tiene $ign cliente" . ($ign > 1 ? 's' : '') . " con actividad reciente pendiente" . ($ign > 1 ? 's' : '') . " de tu atención.", "$ign cliente" . ($ign > 1 ? 's' : '') . " con actividad en el Radar. Revísalos."];
            } elseif ($score >= 70) {
                $v = ["El Radar detectó $ign cliente" . ($ign > 1 ? 's' : '') . " con actividad reciente que no se " . ($ign > 1 ? 'han' : 'ha') . " atendido — ahí están las oportunidades más inmediatas.", "$ign cliente" . ($ign > 1 ? 's activos' : ' activo') . " en el Radar esperando seguimiento. Cada día que pasa baja la probabilidad de cierre."];
            } else {
                $v = ["El Radar tiene $ign cliente" . ($ign > 1 ? 's activos' : ' activo') . " sin marcar con 👍/👎. Es lo más cercano a una venta que tienes ahora.", "$ign cliente" . ($ign > 1 ? 's' : '') . " con actividad en el Radar sin marcar (👍/👎)."];
            }
            $frases[] = $v[$rot % count($v)];
        }

        // ═══ 4. RADAR HEALTH — clientes con interés que se dejaron morir ═══
        if ($h_up > 0) {
            $tasa_muerte = $h_down / $h_up;
            // Observable (sin asumir abandono): calientes que perdieron el bucket sin cerrar.
            if ($tasa_muerte >= 0.40 && $h_down >= 3) {
                $frases[] = "$h_down de $h_up clientes que estaban calientes se enfriaron sin cerrar.";
            } elseif ($tasa_muerte <= 0.30 && $h_up >= 5) {
                $frases[] = "Cuidas bien tu Radar: solo $h_down de $h_up calientes se te enfriaron.";
            }
        }

        // ═══ 6. HISTÓRICO ═══
        if ($cierre_hist_abajo && $score < 70) $frases[] = "Tu cierre está por debajo del promedio anual de la empresa.";

        // ═══ 7. COBROS ═══
        if ($vsp > 0) $frases[] = "$vsp venta" . ($vsp > 1 ? 's' : '') . " sin cobrar.";
        if ($cierres >= 2 && $sdto < $cierres) { $pct_dto = round(($cierres - $sdto) / $cierres * 100); if ($pct_dto >= 50 && $score < 80) $frases[] = "$pct_dto% de ventas con descuento."; }

        // ═══ 8. TENDENCIA ═══
        // Arbitraje con los bonus (que se emiten DESPUÉS del corte): "Tendencia
        // a la baja." junto a "gran mes" en el mismo párrafo es contradicción.
        $b_cierre_pre = (int)($s['bonus_cierre'] ?? 0);
        if ($mom <= 0.80 && $score < 80) {
            $frases[] = $b_cierre_pre >= 4
                ? "Tu actividad viene cayendo aunque el cierre va arriba."
                : "Tendencia a la baja.";
        }

        // Limitar
        if (count($frases) > $max_frases) $frases = array_slice($frases, 0, $max_frases);

        // ═══ BONUS (premios) — después del corte para que siempre se vean ═══
        $b_ticket = (int)($s['bonus_ticket'] ?? 0);
        if ($b_ticket >= 5) {
            $b_ventas = (int)($s['bonus_ticket_ventas'] ?? 0);
            $tier_txt = $b_ticket >= 8 ? 'al triple' : 'al doble';
            if ($b_ventas === 1) {
                $frases[] = "1 venta $tier_txt del ticket promedio — buen cierre.";
            } else {
                $frases[] = "$b_ventas ventas por encima del ticket promedio.";
            }
        }
        $b_cierre = (int)($s['bonus_cierre'] ?? 0);
        // La coletilla GLOBAL ("gran mes/excepcional") solo con momentum sano —
        // el hecho del cierre es verdadero siempre; el juicio del mes, no.
        if ($b_cierre >= 8) {
            $frases[] = $mom > 0.95
                ? "Tu tasa de cierre va muy por encima de tu histórico — mes excepcional."
                : "Tu tasa de cierre va muy por encima de tu histórico.";
        } elseif ($b_cierre >= 4) {
            $frases[] = $mom > 0.95
                ? "Cerraste claramente por arriba de tu histórico — gran mes."
                : "Cerraste claramente por arriba de tu histórico.";
        }

        // (Se quitaron la "acción final" genérica y la comparación mensual: eran filler
        //  que contradecía otros segmentos. El párrafo de números da SOLO datos concretos;
        //  el qué-hacer va en el tip de perfil, no aquí.)

        return implode(' ', $frases);
    }

    // ─── Benchmarks auto-ajustables por empresa ─────────
    private static array $_bench = [];

    private static function _benchmarks(int $empresa_id, int $periodo): array
    {
        // Caché por empresa Y período — la misma empresa puede evaluarse con
        // ventana 15 y extendida (45-60) en el mismo request; mezclar ventanas
        // contamina benchmarks entre vendedores y entre score y widget
        if (isset(self::$_bench[$empresa_id][$periodo])) return self::$_bench[$empresa_id][$periodo];

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
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = cotizaciones.id AND v.pagado > 0 AND v.estado != 'cancelada')",
            [$empresa_id, $periodo]
        );
        // Techo 0.90: cierres van por accion_at y vistas por created_at —
        // cerrar backlog puede dar ratio >1 y un close_rate >1 invierte los
        // pesos del score final (w_proporcional negativo)
        $close_rate = $emp_vistas >= 5 ? min($emp_cierres / $emp_vistas, 0.90) : 0.15;

        // Tasa de cierre HISTÓRICA — todo lo anterior a la ventana actual.
        // Referencia estable para Conversión: el desempeño actual no contamina
        // su propio benchmark. Crítico en empresas de 1 vendedor, donde el
        // benchmark de la ventana actual ES el propio vendedor.
        $emp_vistas_hist = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0
             AND suspendida = 0 AND estado != 'borrador'
             AND (visitas > 0 OR estado IN ('aceptada','convertida','aceptada_cliente'))
             AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $periodo]
        );
        $emp_cierres_hist = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND total > 0
             AND suspendida = 0
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at < DATE_SUB(NOW(), INTERVAL ? DAY)
             AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = cotizaciones.id AND v.pagado > 0 AND v.estado != 'cancelada')",
            [$empresa_id, $periodo]
        );
        // Con historial suficiente → usa el histórico. Sin historial (empresa
        // nueva) → cae al close_rate de la ventana actual (comportamiento previo).
        $close_rate_hist = $emp_vistas_hist >= 5
            ? min($emp_cierres_hist / $emp_vistas_hist, 0.90)
            : $close_rate;

        // Tiempo promedio de cierre (días)
        $avg_ttc = DB::val(
            "SELECT AVG(DATEDIFF(accion_at, created_at)) FROM cotizaciones
             WHERE empresa_id=? AND total > 0
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND accion_at IS NOT NULL
             AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = cotizaciones.id AND v.pagado > 0 AND v.estado != 'cancelada')",
            [$empresa_id, $periodo]
        );

        // Ticket promedio histórico de la empresa (todas las ventas, incluyendo actuales)
        $ticket_promedio = (float)(DB::val(
            "SELECT AVG(total) FROM ventas WHERE empresa_id=? AND estado != 'cancelada' AND total > 0 AND pagado > 0",
            [$empresa_id]
        ) ?? 0);

        self::$_bench[$empresa_id][$periodo] = [
            'close_rate'      => max((float)$close_rate, 0.03),
            'close_rate_hist' => max((float)$close_rate_hist, 0.03),
            'time_to_close'   => max((float)($avg_ttc ?? 14), 3),
            'ticket_promedio' => $ticket_promedio,
        ];
        return self::$_bench[$empresa_id][$periodo];
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
                      s_activacion=VALUES(s_activacion),
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
            } catch (\Throwable $e) {
                // El fallo silencioso ya congeló el histórico una vez — dejar rastro
                error_log('[ActividadScore snapshot] ' . $e->getMessage());
            }
        }
    }

    // ─── Snapshot diario para promedio mensual ─────────────
    // 1 fila por asesor por día (UNIQUE usuario_id+fecha). Upsert: si el admin
    // abre el dashboard varias veces al día, solo se actualiza el punto de HOY
    // (gana el último). El promedio mensual = AVG(score) de estos puntos diarios.
    // Sin cron: depende de que se abra el dashboard/radar (recalcular_empresa).
    public static function snapshot_diario(int $empresa_id): void
    {
        $hoy = date('Y-m-d');
        foreach (self::equipo($empresa_id) as $s) {
            try {
                // Foto del día = la MÁS ALTA (decisión CEO): solo se actualiza si
                // el score nuevo supera al ya registrado hoy. Se guarda la FILA
                // COMPLETA de ese mejor momento (no se mezclan dimensiones de
                // fotos distintas). El promedio mensual = AVG de estas fotos.
                DB::execute(
                    "INSERT INTO score_diario
                        (usuario_id, empresa_id, fecha, score, nivel,
                         s_activacion, s_seguimiento, s_conversion)
                     VALUES (?,?,?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE
                        nivel        = IF(VALUES(score) > score, VALUES(nivel), nivel),
                        s_activacion = IF(VALUES(score) > score, VALUES(s_activacion), s_activacion),
                        s_seguimiento= IF(VALUES(score) > score, VALUES(s_seguimiento), s_seguimiento),
                        s_conversion = IF(VALUES(score) > score, VALUES(s_conversion), s_conversion),
                        score        = IF(VALUES(score) > score, VALUES(score), score)",
                    [
                        (int)$s['usuario_id'], $empresa_id, $hoy,
                        (int)$s['score'], $s['nivel'],
                        (float)($s['s_activacion'] ?? 0),
                        (float)($s['s_seguimiento'] ?? 0),
                        (float)($s['s_conversion'] ?? 0),
                    ]
                );
            } catch (\Throwable $e) {
                error_log('[ActividadScore snapshot_diario] ' . $e->getMessage());
            }
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
