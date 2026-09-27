<?php
// ============================================================
// MetasEmpresa — las metas que DECLARA la empresa, como dato fijo de
// CotizaCloud AI. Diseño y decisiones del CEO: docs/metas_cotizacloud_ai.md
//
// ÚNICA puerta de entrada. Tres funciones, tres audiencias:
//   estado($e)  → TODO con cifras (metas, vendido, faltante, tasas). SOLO ADMIN.
//   nivel($e)   → solo etiquetas y calendario. Lo puede leer un asesor.
//   frases($n)  → texto ya redactado, en tercera persona, sin cifras de meta.
//
// La regla "al asesor nunca cifras" se garantiza AQUÍ, no en cada plantilla:
// nivel() y frases() jamás llevan montos, porcentajes ni tasas. Días y
// fechas sí (son calendario, no revelan la meta).
//
// NO se usa en ActividadScore (decisión 6: el score queda abierto).
// ============================================================
defined('COTIZAAPP') or die;

class MetasEmpresa
{
    /** Días desde la primera venta con pago para que se lea algo (CEO, 2ª ronda). */
    public const HISTORIA_DIAS = 30;
    /**
     * Muestra histórica mínima para opinar de la conversión: la MISMA que usa
     * la tarjeta de Ritmo (RitmoAsesor::HIST_MIN = 8) con la misma tasa.
     */
    public const CONV_MIN = 8;
    /** ±10% alrededor de la tasa deseada = "en lo que busca". */
    public const CONV_BANDA = 0.10;
    /**
     * Histéresis para BAJAR de nivel: se sostiene el nivel previo mientras el
     * vendido no caiga más de este % debajo del umbral de ese nivel.
     * Con escalones de 10 puntos, 0.05 da una banda de 3 a 5 puntos.
     * (El diseño proponía 0.10 cuando había 4 niveles anchos; con 10% sería
     * casi un escalón entero — pendiente de visto bueno del CEO.)
     */
    public const HISTERESIS = 0.05;
    /** Horas que la alerta de cambio de nivel sigue visible para el admin. */
    public const ALERTA_HORAS = 48;
    /** Planes que tienen metas. Pro sigue abierto (§11 del diseño). */
    public const PLANES = ['business'];

    /** Orden ascendente. El índice ES el orden. */
    public const NIVELES = [
        'sin_equilibrio', // vendido < equilibrio
        'muy_baja',       // < 60% de la pesimista
        'baja',           // 60–69%
        'debajo',         // 70–79%
        'cerca',          // 80–89%
        'casi',           // 90–99%
        'llego',          // pesimista alcanzada, < 90% de la optimista
        'casi_optima',    // 90–99% de la optimista
        'sobrepasada',    // ≥ optimista
    ];

    /** Reloj inyectable para pruebas (timestamp). null = time(). */
    public static ?int $ahora = null;

    private static array $memo = [];
    private static bool $logged = false;

    private const MESES = [1=>'enero','febrero','marzo','abril','mayo','junio','julio',
                           'agosto','septiembre','octubre','noviembre','diciembre'];

    /** Limpia la memoria del request (pruebas). */
    public static function reset(): void
    {
        self::$memo = [];
    }

    // ─────────────────────────────────────────────────────────
    //  estado() — con cifras. SOLO pantallas del admin.
    // ─────────────────────────────────────────────────────────
    public static function estado(int $e): array
    {
        if (isset(self::$memo[$e])) return self::$memo[$e];

        $t      = self::$ahora ?? time();
        $hoy    = date('Y-m-d', $t);
        $mes    = (int)date('n', $t);
        $base = [
            'estado'     => 'sin_metas',
            'hoy'        => $hoy,
            'dia'        => (int)date('j', $t),
            'dias_mes'   => (int)date('t', $t),
            'mes_nombre' => self::MESES[$mes],
            'moneda'     => null,
            'ventanas'   => ['mes' => self::_ventana_vacia('sin_metas'), 'd30' => self::_ventana_vacia('sin_metas')],
            'conv'       => self::_conv_vacia(),
            'ticket'     => null,
            'ticket_origen' => null,
            'faltan_cot' => ['real' => null, 'deseada' => null],
        ];

        try {
            if (!self::plan_ok($e)) return self::$memo[$e] = $base;

            $emp = DB::row("SELECT moneda, tasa_conv_meta, meta_equilibrio, meta_pesimista, meta_optimista, meta_moneda
                              FROM empresas WHERE id = ?", [$e]);
            if (!$emp) return self::$memo[$e] = $base;
            $moneda  = strtoupper((string)($emp['moneda'] ?: 'MXN'));
            $deseada = $emp['tasa_conv_meta'] !== null ? (float)$emp['tasa_conv_meta'] / 100 : null;
            $base['moneda'] = $moneda;

            // UNA meta general de la empresa, igual para todos los meses (CEO,
            // 27 sep: "no vamos a entrar a qué mes es cuál").
            // Sin meta no se enciende nada: se sale ANTES de las consultas
            // pesadas (esto corre en cada carga del dashboard de un Business).
            // Sin escrituras en este camino: corre en CADA carga del dashboard.
            // La memoria de histéresis se borra al "Quitar meta" (endpoint);
            // un cambio de meta o de moneda lo detecta la firma.
            if ($emp['meta_equilibrio'] === null || $emp['meta_pesimista'] === null || $emp['meta_optimista'] === null) {
                return self::$memo[$e] = $base;
            }
            $E = (float)$emp['meta_equilibrio'];
            $P = (float)$emp['meta_pesimista'];
            $O = (float)$emp['meta_optimista'];
            // Capturada en otra moneda: no se compara pesos contra dólares.
            if (strtoupper((string)$emp['meta_moneda']) !== $moneda) {
                foreach (['mes', 'd30'] as $w) $base['ventanas'][$w] = self::_ventana_vacia('sin_metas') + ['motivo' => 'moneda'];
                return self::$memo[$e] = $base;
            }
            $firma = self::_firma($E, $P, $O, $moneda);

            // ── Límites (en PHP; NUNCA NOW() en SQL: el reloj es uno solo) ──
            $ini_mes = date('Y-m-01 00:00:00', $t);
            $ini_30  = date('Y-m-d 00:00:00', strtotime('-29 days', strtotime($hoy)));  // 30 fechas contando hoy
            $manana  = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($hoy)));
            $lo      = min($ini_mes, $ini_30);

            // ── Vendido: UNA consulta, dos ventanas, ? posicionales ──
            // DB.php fija ATTR_EMULATE_PREPARES=false: un marcador con nombre
            // repetido lanza excepción, el catch la tragaría y la empresa
            // quedaría en 'sin_metas' para siempre. Por eso ? posicionales.
            //
            // El Descuento Inteligente NO cuenta. OJO: la vara de empresa del
            // motor (ActividadScore) SÍ lo cuenta; aquí se excluye POR DECISIÓN
            // DEL CEO ("el DI no le cuenta al asesor, entonces no cuenta").
            // No "corregir" para alinearlo con el motor.
            $v = DB::row(
                "SELECT
                   COALESCE(SUM(CASE WHEN v.created_at >= ? THEN v.total END),0) AS mes,
                   COALESCE(SUM(v.created_at >= ?),0)                           AS n_mes,
                   COALESCE(SUM(CASE WHEN v.created_at >= ? THEN v.total END),0) AS d30,
                   COALESCE(SUM(v.created_at >= ?),0)                           AS n_30
                 FROM ventas v
                 WHERE v.empresa_id = ? AND v.estado <> 'cancelada'
                   AND v.pagado > 0 AND v.total > 0
                   AND v.created_at >= ? AND v.created_at < ?
                   AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di
                                    WHERE di.cotizacion_id = v.cotizacion_id AND di.estado = 'utilizado')",
                [$ini_mes, $ini_mes, $ini_30, $ini_30, $e, $lo, $manana]
            ) ?? [];
            $vend = ['mes' => (float)($v['mes'] ?? 0), 'd30' => (float)($v['d30'] ?? 0)];
            $n    = ['mes' => (int)($v['n_mes'] ?? 0), 'd30' => (int)($v['n_30'] ?? 0)];

            // ── Conversión: la tasa AUTOAJUSTABLE de la empresa contra la deseada ──
            // Es la misma tasa que ya ven el asesor ("la empresa 18%") y los tips
            // (ActividadScore::close_rate_historico). Decisión del CEO (27 sep):
            // se AGREGA la comparación contra la deseada, SIN alterar esa tasa ni
            // dónde se usa. Así hay un solo número de cierre de la empresa: la
            // deseada nunca se compara contra una cuenta distinta (p. ej. ventas
            // del mes ÷ enviadas del mes, que brinca con el arrastre de meses
            // anteriores).
            $base['conv'] = self::_conv(self::_tasa_empresa($e), $deseada);

            // ── Ticket (para "cotizaciones que faltan", admin) ──
            [$base['ticket'], $base['ticket_origen']] = self::_ticket($e, $t);

            // ── Historia mínima: 30 días desde la primera venta con pago ──
            $primera = DB::val(
                "SELECT v.created_at FROM ventas v
                  WHERE v.empresa_id = ? AND v.estado <> 'cancelada' AND v.pagado > 0 AND v.total > 0
                    AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di
                                     WHERE di.cotizacion_id = v.cotizacion_id AND di.estado = 'utilizado')
                  ORDER BY v.created_at LIMIT 1",
                [$e]);
            $con_historia = $primera
                && strtotime(date('Y-m-d', strtotime((string)$primera))) <= strtotime('-' . self::HISTORIA_DIAS . ' days', strtotime($hoy));

            // ── Las dos ventanas contra la MISMA meta completa ──
            // Mes calendario: sin prorrateo (CEO, 3ª ronda). Últimos 30 días:
            // la ventana siempre abarca un mes completo, así que la meta
            // entera es justa. Sin repartir por días.
            foreach (['mes', 'd30'] as $w) {
                $vw = $vend[$w];
                $crudo = self::_nivel_crudo($vw, $E, $P, $O);
                $vent = [
                    'estado'      => $con_historia ? 'ok' : 'sin_historia',
                    'equilibrio'  => round($E, 2),
                    'pesimista'   => round($P, 2),
                    'optimista'   => round($O, 2),
                    'vendido'     => round($vw, 2),
                    'n'           => $n[$w],
                    'nivel_crudo' => $crudo,
                    'nivel'       => null,
                    'cambio'      => false,
                    'alerta'      => false,
                    'nivel_anterior' => null,
                    'cambiado_at' => null,
                ] + self::_faltante($vw, $E, $P, $O);

                if ($con_historia) {
                    $periodo = $w === 'mes' ? date('Y-m', $t) : 'rolling';
                    $h = self::_histeresis($e, $w, $periodo, $firma, $crudo, $vw, $E, $P, $O, $t);
                    $vent['nivel']          = $h['nivel'];
                    $vent['cambio']         = $h['cambio'];
                    $vent['nivel_anterior'] = $h['nivel_anterior'];
                    $vent['cambiado_at']    = $h['cambiado_at'];
                    // La ALERTA del admin sale de lo guardado, no de 'cambio':
                    // 'cambio' solo es true para el request que escribió la
                    // transición, y ése casi siempre es un asesor.
                    $vent['alerta'] = $h['nivel_anterior'] !== null && $h['cambiado_at'] !== null
                        && strtotime($h['cambiado_at']) >= $t - self::ALERTA_HORAS * 3600;
                }
                $base['ventanas'][$w] = $vent;
            }
            $base['estado'] = $con_historia ? 'ok' : 'sin_historia';

            // ── Cotizaciones que faltan (mes calendario, admin) ──
            $vm = $base['ventanas']['mes'];
            if ($vm['estado'] === 'ok' && $vm['faltante'] > 0 && $base['ticket'] > 0) {
                $necesarias = $vm['faltante'] / $base['ticket'];
                $cm = $base['conv'];
                // La tasa real vale aunque no haya deseada declarada; lo que
                // decide si se usa es la muestra, no el nivel.
                if ($cm['muestra'] >= self::CONV_MIN && $cm['tasa'] > 0) {
                    $base['faltan_cot']['real'] = (int)ceil($necesarias / $cm['tasa']);
                }
                if ($deseada) {
                    $base['faltan_cot']['deseada'] = (int)ceil($necesarias / $deseada);
                }
            }
            return self::$memo[$e] = $base;

        } catch (\Throwable $ex) {
            if (!self::$logged) {
                error_log('[Metas] empresa ' . $e . ': ' . $ex->getMessage());
                self::$logged = true;
            }
            return self::$memo[$e] = $base;
        }
    }

    // ─────────────────────────────────────────────────────────
    //  nivel() — SIN cifras. Lo que puede leer un asesor.
    // ─────────────────────────────────────────────────────────
    public static function nivel(int $e): array
    {
        $s = self::estado($e);
        $out = [
            'mes'        => self::_etiqueta($s['ventanas']['mes']),
            'd30'        => self::_etiqueta($s['ventanas']['d30']),
            // La conversión deseada solo se enciende junto con las metas: sin
            // metas o sin historia NO sale ningún texto de metas (§1).
            'conv'       => $s['estado'] === 'ok' ? $s['conv']['nivel'] : 'sin_meta',
            'dias'       => $s['dia'],
            'mes_nombre' => $s['mes_nombre'],
            'corte'      => $s['hoy'],
        ];
        return $out;
    }

    // ─────────────────────────────────────────────────────────
    //  frases() — texto final. Tercera persona, "la empresa" de sujeto.
    //  Nunca "vas", "te faltan", "tu meta". Nunca cifras de meta.
    //  $fechado=true para lo que se guarda/imprime (reporte del Director,
    //  cacheado 7 días): "en septiembre" en vez de "en este mes".
    // ─────────────────────────────────────────────────────────
    public static function frases(array $nivel, bool $fechado = false): array
    {
        $out = ['mes' => null, 'd30' => null, 'conv' => null];

        $ventana = [
            'mes' => $fechado ? 'en ' . ($nivel['mes_nombre'] ?? 'este mes') : 'en este mes',
            'd30' => $fechado && !empty($nivel['corte'])
                        ? 'en los 30 días al ' . self::_fecha_corta($nivel['corte'])
                        : 'en los últimos 30 días',
        ];
        $txt = [
            'sin_equilibrio' => 'La empresa ni siquiera llega al punto de equilibrio %s.',
            'muy_baja'       => 'La empresa va muy baja %s.',
            'baja'           => 'La empresa va baja %s.',
            'debajo'         => 'La empresa va por debajo de su meta %s.',
            'cerca'          => 'La empresa va cerca de su meta %s.',
            'casi'           => 'La empresa casi llega a su meta %s.',
            'llego'          => 'La empresa ya llegó a su meta %s.',
            'casi_optima'    => 'La empresa casi llega a su meta optimista %s.',
            'sobrepasada'    => 'La empresa ya sobrepasó su meta optimista %s.',
        ];

        // Sin historia: una sola frase, no dos iguales.
        if (($nivel['mes'] ?? null) === 'sin_historia' || ($nivel['d30'] ?? null) === 'sin_historia') {
            $out['mes'] = 'Todavía no hay suficiente historia para leer cómo va la empresa.';
        }
        foreach (['mes', 'd30'] as $w) {
            $k = $nivel[$w] ?? null;
            if (is_string($k) && isset($txt[$k])) $out[$w] = sprintf($txt[$k], $ventana[$w]);
        }

        // Sin ventana: la tasa autoajustable es histórica, no del mes.
        $conv = [
            'debajo' => 'La empresa cierra por debajo de lo que busca.',
            'en'     => 'La empresa cierra en lo que busca.',
            'arriba' => 'La empresa cierra por encima de lo que busca.',
        ];
        $k = $nivel['conv'] ?? null;
        if (is_string($k) && isset($conv[$k])) $out['conv'] = $conv[$k];
        return $out;
    }

    /**
     * Los renglones del TIP del termómetro (asesor): DOS frases, mes calendario
     * y últimos 30 días (CEO, 27 sep), cada una solo si hay un nivel real.
     * [] = no se muestra nada. Sin cifras.
     */
    public static function lineas_tip(int $e): array
    {
        $n = self::nivel($e);
        $f = self::frases($n);
        $out = [];
        foreach (['mes', 'd30'] as $w) {
            if (in_array($n[$w], self::NIVELES, true) && !empty($f[$w])) $out[] = $f[$w];
        }
        return $out;
    }

    /** La acción que corresponde a cada debilidad del tip (RitmoTip::_debilidad). */
    private const ACCION_TIP = [
        'seguimiento'  => 'ponerte al día con tus seguimientos',
        'citas'        => 'agendar citas',
        'cierre'       => 'cerrar lo que ya abriste',
        'contacto'     => 'lograr que te contesten',
        'calientes'    => 'atender a los que ya mostraron interés',
        'enfriamiento' => 'retomar a los que se enfriaron',
        'radar'        => 'responder a las señales del Radar',
        'precio'       => 'defender el precio en vez de soltar al cliente',
        'objeciones'   => 'resolver las objeciones que quedaron en el aire',
        'califica'     => 'calificar antes de descartar',
        'descuento'    => 'cerrar sin descuento',
        'ticket'       => 'subir tu ticket',
    ];

    /** Banda de la empresa para el puente del tip. */
    private static function _banda(string $nivel): string
    {
        return match ($nivel) {
            'sin_equilibrio', 'muy_baja', 'baja' => 'abajo',
            'debajo', 'cerca', 'casi'            => 'camino',
            default                              => 'arriba',   // llego, casi_optima, sobrepasada
        };
    }

    /**
     * El texto que se ANEXA al tip del asesor (CEO, 27 sep: dentro del tip,
     * no como bloque aparte, y CONECTADO con lo que dice el tip).
     *   1) Cómo va la empresa: mes y 30 días; si van en el mismo nivel, una
     *      sola frase ("…en este mes y en los últimos 30 días.").
     *   2) Un puente según la banda de la empresa en el mes y la debilidad que
     *      ya eligió el tip. Sin debilidad conocida (diagnóstico legacy), sin
     *      puente. Nunca cifras.
     * '' = nada que anexar.
     */
    public static function texto_tip(int $e, ?string $debilidad = null): string
    {
        $l = self::lineas_tip($e);
        if (!$l) return '';
        $n = self::nivel($e);
        $base = (count($l) === 2 && $n['mes'] === $n['d30'])
            ? mb_substr($l[0], 0, -1) . ' y en los últimos 30 días.'
            : implode(' ', $l);

        // La banda la manda el mes (lo accionable); si el mes no se lee, 30 días.
        $ref = in_array($n['mes'], self::NIVELES, true) ? $n['mes'] : $n['d30'];
        $banda = self::_banda($ref);

        $puente = '';
        if ($debilidad === 'bien') {
            $puente = [
                'abajo'  => 'Tu trabajo está empujando; sigue así.',
                'camino' => 'Tu ritmo ayuda a que llegue.',
                'arriba' => 'Tu trabajo es parte de ese resultado.',
            ][$banda];
        } elseif ($debilidad !== null && isset(self::ACCION_TIP[$debilidad])) {
            $acc = self::ACCION_TIP[$debilidad];
            $puente = [
                'abajo'  => "Cada venta cuenta: {$acc} es lo que más ayuda ahora.",
                'camino' => "Está cerca: {$acc} puede ser lo que falte.",
                'arriba' => "Para sumarte a ese resultado, empieza por {$acc}.",
            ][$banda];
        }
        return trim($base . ' ' . $puente);
    }

    /**
     * Los renglones de la sección del REPORTE del asesor: frases FECHADAS
     * (el reporte se guarda 7 días y se imprime), sin cifras, sin vacíos.
     */
    public static function lineas_reporte(array $nivel): array
    {
        return array_values(array_filter(self::frases($nivel, true), fn($x) => is_string($x) && $x !== ''));
    }

    // ═════════════════════════════════════════════════════════
    //  Captura (Configuración › Metas). La validación vive AQUÍ para que
    //  la pestaña, el endpoint y la simulación usen la misma regla.
    // ═════════════════════════════════════════════════════════

    /** Tope de DECIMAL(14,2). */
    public const MONTO_MAX = 999999999999.99;
    /** Rango real del motor: piso 3% (ActividadScore max(...,0.03)), techo 90%. */
    public const TASA_MIN = 3;
    public const TASA_MAX = 90;

    /**
     * "$120,000.50" / "120000" → 120000.5. Vacío, basura, hex, binario,
     * notación científica o arreglos → null. Solo dígitos, comas de miles y
     * un punto decimal: lo que escribe una persona, nada que PHP o JS
     * "interpreten" distinto (0x10, 1e3, INF).
     */
    public static function parse_monto(mixed $x): ?float
    {
        if (is_int($x)) return (float)$x;
        if (is_float($x)) return is_finite($x) ? $x : null;
        if (!is_string($x)) return null;
        $x = trim(str_replace([',', '$', ' '], '', $x));
        if (!preg_match('/^\d+(\.\d+)?$/', $x)) return null;
        return (float)$x;
    }

    /** ¿Más de 2 decimales? "180.000" es casi seguro 180 mil con punto de miles. */
    public static function decimales_de_mas(mixed $x): bool
    {
        return is_string($x) && (bool)preg_match('/\.\d{3,}\s*$/', trim($x));
    }

    /**
     * Los tres montos juntos o ninguno; todos > 0; equilibrio ≤ pesimista ≤
     * optimista (CEO, 2ª ronda: si no se cumple, se rechaza). null = válido.
     */
    public static function validar_metas(?float $E, ?float $P, ?float $O): ?string
    {
        if ($E === null || $P === null || $O === null) return 'Captura los tres montos: punto de equilibrio, meta pesimista y meta optimista.';
        // Contra el valor YA redondeado: 0.004 pasaba "> 0" y se guardaba 0.00.
        if (round($E, 2) <= 0 || round($P, 2) <= 0 || round($O, 2) <= 0) return 'Los tres montos deben ser mayores a cero.';
        if (max($E, $P, $O) > self::MONTO_MAX)         return 'El monto es demasiado grande.';
        if (round($E, 2) > round($P, 2))               return 'La meta pesimista no puede quedar debajo del punto de equilibrio.';
        if (round($P, 2) > round($O, 2))               return 'La meta optimista no puede quedar debajo de la meta pesimista.';
        return null;
    }

    /** La meta vigente para la pantalla de captura (admin). null = sin capturar. */
    public static function meta(int $e): ?array
    {
        $r = DB::row("SELECT meta_equilibrio, meta_pesimista, meta_optimista, meta_moneda, meta_capturada_at
                        FROM empresas WHERE id = ?", [$e]);
        if (!$r || $r['meta_equilibrio'] === null) return null;
        return ['equilibrio' => (float)$r['meta_equilibrio'], 'pesimista' => (float)$r['meta_pesimista'],
                'optimista' => (float)$r['meta_optimista'], 'moneda' => strtoupper((string)$r['meta_moneda']),
                'capturada_at' => $r['meta_capturada_at']];
    }

    /** Tasa deseada en %, entre 3 y 90. Vacío = no declarada (válido). */
    public static function validar_tasa(?float $tasa): ?string
    {
        if ($tasa === null) return null;
        if ($tasa < self::TASA_MIN || $tasa > self::TASA_MAX) {
            return 'La tasa deseada va entre ' . self::TASA_MIN . '% y ' . self::TASA_MAX . '%.';
        }
        return null;
    }

    // ═════════════════════════════════════════════════════════
    //  Internos
    // ═════════════════════════════════════════════════════════

    public static function plan_ok(int $e): bool
    {
        if (!function_exists('trial_info')) {
            if (!self::$logged) { error_log('[Metas] trial_info() no está cargada'); self::$logged = true; }
            return false;
        }
        $ti = trial_info($e);
        // Una licencia Business vencida (no trial) conserva plan='business'
        // con vencido=true: esa NO tiene metas.
        return in_array($ti['plan'] ?? '', self::PLANES, true) && empty($ti['vencido']);
    }

    private static function _ventana_vacia(string $estado): array
    {
        return ['estado' => $estado, 'nivel' => null];
    }

    private static function _conv_vacia(): array
    {
        return ['tasa' => null, 'muestra' => 0, 'deseada' => null, 'nivel' => 'sin_meta'];
    }

    /** Etiqueta pública de una ventana: el nivel, o su estado si no hay nivel. */
    private static function _etiqueta(array $v): string
    {
        if (($v['estado'] ?? '') === 'ok' && !empty($v['nivel'])) return $v['nivel'];
        return $v['estado'] ?? 'sin_metas';
    }

    /**
     * Umbral (monto) a partir del cual rige cada nivel. Primero el equilibrio;
     * alcanzado, el avance contra la pesimista; pasada, contra la optimista.
     * Todo max(E, …): si el equilibrio queda arriba de un escalón, ese
     * escalón queda vacío y la lectura sigue coherente.
     */
    private static function _umbrales(float $E, float $P, float $O): array
    {
        return [
            0.0,
            $E,
            max($E, 0.6 * $P),
            max($E, 0.7 * $P),
            max($E, 0.8 * $P),
            max($E, 0.9 * $P),
            max($E, $P),
            max($E, $P, 0.9 * $O),
            max($E, $P, $O),
        ];
    }

    private static function _idx(float $V, array $u): int
    {
        $i = 0;
        foreach ($u as $k => $lim) {
            if ($k > 0 && round($V, 2) >= round($lim, 2)) $i = $k;
        }
        return $i;
    }

    private static function _nivel_crudo(float $V, float $E, float $P, float $O): string
    {
        return self::NIVELES[self::_idx($V, self::_umbrales($E, $P, $O))];
    }

    /** Lo que falta para el siguiente objetivo no alcanzado (equilibrio → pesimista → optimista). */
    private static function _faltante(float $V, float $E, float $P, float $O): array
    {
        foreach (['equilibrio' => $E, 'pesimista' => $P, 'optimista' => $O] as $k => $x) {
            if (round($V, 2) < round($x, 2)) return ['faltante' => round($x - $V, 2), 'faltante_hacia' => $k];
        }
        return ['faltante' => 0.0, 'faltante_hacia' => null];
    }

    /**
     * Subir de nivel: al cruzar el umbral. Bajar: solo si el vendido cae
     * debajo de umbral_del_nivel_previo × (1 − HISTERESIS). Así una venta que
     * sale de la ventana de 30 días no hace parpadear la frase.
     * La primera evaluación de un periodo NO es "cambio" (sin alerta el día 1).
     * Escribe durante una lectura, como Mesa::armar → mesa_vencidos. Si la
     * tabla no está, devuelve el nivel crudo sin histéresis.
     */
    private static function _histeresis(int $e, string $w, string $periodo, string $firma, string $crudo,
                                        float $V, float $E, float $P, float $O, int $t): array
    {
        $r = ['nivel' => $crudo, 'cambio' => false, 'nivel_anterior' => null, 'cambiado_at' => null];
        try {
            $prev = DB::row("SELECT periodo, firma, nivel, nivel_anterior, cambiado_at FROM empresa_metas_estado
                              WHERE empresa_id = ? AND ventana = ?", [$e, $w]);
            $ahora = date('Y-m-d H:i:s', $t);
            $idx_c = array_search($crudo, self::NIVELES, true);

            // Otro periodo u otras metas (el admin las editó) = primera lectura:
            // el nivel nuevo no se compara contra uno calculado con otra vara.
            if (!$prev || $prev['periodo'] !== $periodo || $prev['firma'] !== $firma
                || ($idx_p = array_search($prev['nivel'], self::NIVELES, true)) === false) {
                DB::execute(
                    "INSERT INTO empresa_metas_estado (empresa_id, ventana, periodo, firma, nivel, nivel_anterior, cambiado_at)
                     VALUES (?,?,?,?,?,NULL,?)
                     ON DUPLICATE KEY UPDATE periodo = VALUES(periodo), firma = VALUES(firma), nivel = VALUES(nivel),
                                             nivel_anterior = NULL, cambiado_at = VALUES(cambiado_at)",
                    [$e, $w, $periodo, $firma, $crudo, $ahora]);
                $r['cambiado_at'] = $ahora;
                return $r;
            }

            $nivel = $crudo;
            if ($idx_c < $idx_p) {
                $u = self::_umbrales($E, $P, $O);
                if (round($V, 2) >= round($u[$idx_p] * (1 - self::HISTERESIS), 2)) $nivel = $prev['nivel'];
            }

            if ($nivel !== $prev['nivel']) {
                DB::execute(
                    "UPDATE empresa_metas_estado SET nivel = ?, nivel_anterior = ?, cambiado_at = ?
                      WHERE empresa_id = ? AND ventana = ?",
                    [$nivel, $prev['nivel'], $ahora, $e, $w]);
                return ['nivel' => $nivel, 'cambio' => true, 'nivel_anterior' => $prev['nivel'], 'cambiado_at' => $ahora];
            }
            return ['nivel' => $nivel, 'cambio' => false,
                    'nivel_anterior' => $prev['nivel_anterior'], 'cambiado_at' => $prev['cambiado_at']];
        } catch (\Throwable $ex) {
            if (!self::$logged) {
                error_log('[Metas] histéresis empresa ' . $e . ': ' . $ex->getMessage());
                self::$logged = true;
            }
            return $r;
        }
    }

    /** Huella de la meta con que se calculó un nivel: si el admin la edita, cambia. */
    private static function _firma(float $E, float $P, float $O, string $moneda): string
    {
        return md5(implode('|', [round($E, 2), round($P, 2), round($O, 2), $moneda]));
    }

    /** Borra la memoria de histéresis (al quitar la meta). */
    public static function olvidar(int $e, array $ventanas = ['mes', 'd30']): void
    {
        try {
            foreach ($ventanas as $w) {
                DB::execute("DELETE FROM empresa_metas_estado WHERE empresa_id = ? AND ventana = ?", [$e, $w]);
            }
        } catch (\Throwable $ex) {
            // Tabla no migrada: no hay memoria que borrar.
        }
    }

    /** Pruebas: sustituye la lectura de la tasa autoajustable. fn(int $e): ['rate'=>, 'muestra'=>] */
    public static $tasa_fn = null;

    /** La tasa autoajustable de la empresa, tal cual la calcula el motor. Solo lectura. */
    private static function _tasa_empresa(int $e): array
    {
        if (is_callable(self::$tasa_fn)) return (self::$tasa_fn)($e);
        if (!class_exists('ActividadScore')) require_once __DIR__ . '/ActividadScore.php';
        return ActividadScore::close_rate_historico($e);
    }

    /** Tasa autoajustable contra la deseada. */
    private static function _conv(array $cr, ?float $deseada): array
    {
        $tasa    = (float)($cr['rate'] ?? 0);
        $muestra = (int)($cr['muestra'] ?? 0);
        $c = ['tasa' => $tasa > 0 ? round($tasa, 4) : null, 'muestra' => $muestra,
              'deseada' => $deseada, 'nivel' => 'sin_meta'];
        if ($deseada === null || $deseada <= 0) return $c;
        // Mismo candado que la tarjeta de Ritmo: sin muestra o sin tasa, no se opina.
        if ($muestra < self::CONV_MIN || $tasa <= 0) { $c['nivel'] = 'gris'; return $c; }
        // Redondeo antes de comparar: sin él, 27/100 contra 30% daba 'en' y
        // 18/100 contra 20% daba 'debajo' (misma frontera, distinto binario).
        $t4 = round($tasa, 6);
        if ($t4 < round($deseada * (1 - self::CONV_BANDA), 6))     $c['nivel'] = 'debajo';
        elseif ($t4 > round($deseada * (1 + self::CONV_BANDA), 6)) $c['nivel'] = 'arriba';
        else                                               $c['nivel'] = 'en';
        return $c;
    }

    /**
     * Ticket PROPIO de Metas (misma receta de vendido, 180 días, n ≥ 5).
     * Respaldo: historial_mensual, las 6 filas más recientes DEL ÚLTIMO AÑO.
     * La Mesa conserva el suyo (Mesa.php): no se comparten, porque cambiaría
     * el umbral de monto de la Mesa y con él su orden.
     */
    private static function _ticket(int $e, int $t): array
    {
        $hoy = date('Y-m-d', $t);
        $r = DB::row(
            "SELECT COUNT(*) AS n, COALESCE(SUM(v.total),0) AS s FROM ventas v
              WHERE v.empresa_id = ? AND v.estado <> 'cancelada' AND v.pagado > 0 AND v.total > 0
                AND v.created_at >= ? AND v.created_at < ?
                AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di
                                 WHERE di.cotizacion_id = v.cotizacion_id AND di.estado = 'utilizado')",
            [$e,
             date('Y-m-d 00:00:00', strtotime('-179 days', strtotime($hoy))),
             date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($hoy)))]
        );
        if ($r && (int)$r['n'] >= 5) return [round((float)$r['s'] / (int)$r['n'], 2), 'ventas'];

        try {
            $h = DB::row(
                "SELECT COALESCE(SUM(ventas_monto),0) AS s, COALESCE(SUM(ventas_cantidad),0) AS n
                   FROM (SELECT ventas_monto, ventas_cantidad FROM historial_mensual
                          WHERE empresa_id = ? AND ventas_cantidad > 0
                            AND (anio * 100 + mes) BETWEEN ? AND ?
                          ORDER BY anio DESC, mes DESC LIMIT 6) x",
                // Solo el último año (CEO, 27 sep): un historial de hace años
                // daría una venta promedio con precios viejos. Sin nada
                // reciente, no hay ticket y la tarjeta calla ese renglón.
                [$e, (int)date('Y', strtotime('-11 months', strtotime(date('Y-m-01', $t)))) * 100
                        + (int)date('n', strtotime('-11 months', strtotime(date('Y-m-01', $t)))),
                     (int)date('Y', $t) * 100 + (int)date('n', $t)]);
            if ($h && (int)$h['n'] > 0 && (float)$h['s'] > 0) {
                return [round((float)$h['s'] / (int)$h['n'], 2), 'historial'];
            }
        } catch (\Throwable $ex) {
            // historial_mensual es opcional (import Business). Sin ella, sin ticket.
        }
        return [null, null];
    }

    private static function _fecha_corta(string $ymd): string
    {
        if (!class_exists('RitmoCot')) require_once __DIR__ . '/RitmoCot.php';
        return RitmoCot::fecha_corta($ymd);
    }
}
