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
    /** Enviadas mínimas en la ventana para opinar de la conversión. */
    public const CONV_MIN = 8;
    /** ±10% alrededor de la tasa deseada = "en lo que busca". */
    public const CONV_BANDA = 0.10;
    /** Tope de la tasa real, igual que el motor (ActividadScore: min(..., 0.90)). */
    public const CONV_TOPE = 0.90;
    /**
     * Histéresis para BAJAR de nivel: se sostiene el nivel previo mientras el
     * vendido no caiga más de este % debajo del umbral de ese nivel.
     * Con escalones de 10 puntos, 0.05 da una banda de 3 a 5 puntos.
     * (El diseño proponía 0.10 cuando había 4 niveles anchos; con 10% sería
     * casi un escalón entero — pendiente de visto bueno del CEO.)
     */
    public const HISTERESIS = 0.05;
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
        $anio   = (int)date('Y', $t);
        $mes    = (int)date('n', $t);
        $base = [
            'estado'     => 'sin_metas',
            'hoy'        => $hoy,
            'dia'        => (int)date('j', $t),
            'dias_mes'   => (int)date('t', $t),
            'mes_nombre' => self::MESES[$mes],
            'moneda'     => null,
            'ventanas'   => ['mes' => self::_ventana_vacia('sin_metas'), 'd30' => self::_ventana_vacia('sin_metas')],
            'conv'       => ['mes' => self::_conv_vacia(), 'd30' => self::_conv_vacia()],
            'ticket'     => null,
            'ticket_origen' => null,
            'faltan_cot' => ['real' => null, 'deseada' => null],
        ];

        try {
            if (!self::_plan_ok($e)) return self::$memo[$e] = $base;

            $emp = DB::row("SELECT moneda, tasa_conv_meta FROM empresas WHERE id = ?", [$e]);
            if (!$emp) return self::$memo[$e] = $base;
            $moneda  = strtoupper((string)($emp['moneda'] ?: 'MXN'));
            $deseada = $emp['tasa_conv_meta'] !== null ? (float)$emp['tasa_conv_meta'] / 100 : null;
            $base['moneda'] = $moneda;

            $filas = DB::query(
                "SELECT anio, mes, equilibrio, meta_pesimista, meta_optimista, moneda
                   FROM empresa_metas_mes WHERE empresa_id = ? ORDER BY anio, mes", [$e]);

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

            // ── Conversión real: ventas (misma receta) ÷ enviadas (embudo del dashboard) ──
            $env = DB::row(
                "SELECT COALESCE(SUM(c.created_at >= ?),0) AS mes,
                        COALESCE(SUM(c.created_at >= ?),0) AS d30
                   FROM cotizaciones c
                  WHERE c.empresa_id = ? AND c.estado <> 'borrador' AND c.suspendida = 0
                    AND c.created_at >= ? AND c.created_at < ?",
                [$ini_mes, $ini_30, $e, $lo, $manana]
            ) ?? [];
            foreach (['mes', 'd30'] as $w) {
                $base['conv'][$w] = self::_conv((int)($env[$w] ?? 0), $n[$w], $deseada);
            }

            // ── Ticket (para "cotizaciones que faltan", admin) ──
            [$base['ticket'], $base['ticket_origen']] = self::_ticket($e, $t);

            if (!$filas) return self::$memo[$e] = $base;   // sin metas: nada se enciende

            // ── Historia mínima: 30 días desde la primera venta con pago ──
            $primera = DB::val(
                "SELECT MIN(v.created_at) FROM ventas v
                  WHERE v.empresa_id = ? AND v.estado <> 'cancelada' AND v.pagado > 0 AND v.total > 0
                    AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di
                                     WHERE di.cotizacion_id = v.cotizacion_id AND di.estado = 'utilizado')",
                [$e]);
            $con_historia = $primera
                && strtotime(date('Y-m-d', strtotime((string)$primera))) <= strtotime('-' . self::HISTORIA_DIAS . ' days', strtotime($hoy));

            // ── Metas de cada ventana ──
            $metas = [
                'mes' => self::_metas_mes($filas, $anio, $mes, $moneda),
                'd30' => self::_metas_d30($filas, $ini_30, $hoy, $moneda),
            ];

            $alguna = false;
            foreach (['mes', 'd30'] as $w) {
                $m = $metas[$w];
                if ($m['estado'] !== 'ok') {
                    $base['ventanas'][$w] = self::_ventana_vacia($m['estado']) + ['motivo' => $m['motivo'] ?? null];
                    continue;
                }
                $alguna = true;
                $vw = $vend[$w];
                $crudo = self::_nivel_crudo($vw, $m['E'], $m['P'], $m['O']);
                $vent = [
                    'estado'      => $con_historia ? 'ok' : 'sin_historia',
                    'equilibrio'  => round($m['E'], 2),
                    'pesimista'   => round($m['P'], 2),
                    'optimista'   => round($m['O'], 2),
                    'vendido'     => round($vw, 2),
                    'n'           => $n[$w],
                    'provisional' => $m['provisional'],
                    'origen'      => $m['origen'],
                    'nivel_crudo' => $crudo,
                    'nivel'       => null,
                    'cambio'      => false,
                    'nivel_anterior' => null,
                    'cambiado_at' => null,
                ] + self::_faltante($vw, $m['E'], $m['P'], $m['O']);

                if ($con_historia) {
                    $periodo = $w === 'mes' ? date('Y-m', $t) : 'rolling';
                    $h = self::_histeresis($e, $w, $periodo, $crudo, $vw, $m['E'], $m['P'], $m['O'], $t);
                    $vent['nivel']          = $h['nivel'];
                    $vent['cambio']         = $h['cambio'];
                    $vent['nivel_anterior'] = $h['nivel_anterior'];
                    $vent['cambiado_at']    = $h['cambiado_at'];
                }
                $base['ventanas'][$w] = $vent;
            }
            if (!$alguna) return self::$memo[$e] = $base;
            $base['estado'] = $con_historia ? 'ok' : 'sin_historia';

            // ── Cotizaciones que faltan (mes calendario, admin) ──
            $vm = $base['ventanas']['mes'];
            if ($vm['estado'] === 'ok' && $vm['faltante'] > 0 && $base['ticket'] > 0) {
                $necesarias = $vm['faltante'] / $base['ticket'];
                $cm = $base['conv']['mes'];
                // La tasa real vale aunque no haya deseada declarada; lo que
                // decide si se usa es la muestra de enviadas, no el nivel.
                if ($cm['enviadas'] >= self::CONV_MIN && $cm['tasa'] > 0) {
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
            'conv_mes'   => $s['conv']['mes']['nivel'],
            'conv_d30'   => $s['conv']['d30']['nivel'],
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
        $out = ['mes' => null, 'd30' => null, 'conv_mes' => null, 'conv_d30' => null];

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
            if (isset($txt[$k])) $out[$w] = sprintf($txt[$k], $ventana[$w]);
        }

        $conv = [
            'debajo' => 'La empresa cierra por debajo de lo que busca %s.',
            'en'     => 'La empresa cierra en lo que busca %s.',
            'arriba' => 'La empresa cierra por encima de lo que busca %s.',
        ];
        foreach (['mes', 'd30'] as $w) {
            $k = $nivel['conv_' . $w] ?? null;
            if (isset($conv[$k])) $out['conv_' . $w] = sprintf($conv[$k], $ventana[$w]);
        }
        return $out;
    }

    // ═════════════════════════════════════════════════════════
    //  Internos
    // ═════════════════════════════════════════════════════════

    private static function _plan_ok(int $e): bool
    {
        if (!function_exists('trial_info')) return false;
        return in_array(trial_info($e)['plan'] ?? '', self::PLANES, true);
    }

    private static function _ventana_vacia(string $estado): array
    {
        return ['estado' => $estado, 'nivel' => null];
    }

    private static function _conv_vacia(): array
    {
        return ['enviadas' => 0, 'ventas' => 0, 'tasa' => null, 'deseada' => null, 'nivel' => 'sin_meta'];
    }

    /** Etiqueta pública de una ventana: el nivel, o su estado si no hay nivel. */
    private static function _etiqueta(array $v): string
    {
        if (($v['estado'] ?? '') === 'ok' && !empty($v['nivel'])) return $v['nivel'];
        return $v['estado'] ?? 'sin_metas';
    }

    /** La fila que rige (anio, mes): la propia o la última capturada antes. */
    private static function _fila(array $filas, int $anio, int $mes): ?array
    {
        $k = $anio * 100 + $mes;
        $hit = null;
        foreach ($filas as $f) {                 // vienen ordenadas ascendente
            if ((int)$f['anio'] * 100 + (int)$f['mes'] <= $k) $hit = $f; else break;
        }
        return $hit;
    }

    /** Mes calendario: meta COMPLETA del mes, sin prorrateo (CEO, 3ª ronda). */
    private static function _metas_mes(array $filas, int $anio, int $mes, string $moneda): array
    {
        $f = self::_fila($filas, $anio, $mes);
        if (!$f) return ['estado' => 'sin_metas'];
        if (strtoupper($f['moneda']) !== $moneda) return ['estado' => 'sin_metas', 'motivo' => 'moneda'];
        return [
            'estado' => 'ok',
            'E' => (float)$f['equilibrio'], 'P' => (float)$f['meta_pesimista'], 'O' => (float)$f['meta_optimista'],
            'provisional' => !((int)$f['anio'] === $anio && (int)$f['mes'] === $mes),
            'origen' => sprintf('%04d-%02d', $f['anio'], $f['mes']),
        ];
    }

    /**
     * Últimos 30 días: suma día por día de meta_del_mes(d) / días_de_ese_mes(d).
     * Si UN solo día no tiene meta (ni propia ni heredada), la ventana entera
     * queda sin_metas: prorratear a medias la haría artificialmente fácil.
     */
    private static function _metas_d30(array $filas, string $ini, string $hoy, string $moneda): array
    {
        $E = $P = $O = 0.0; $prov = false; $origenes = [];
        $d = strtotime(substr($ini, 0, 10)); $fin = strtotime($hoy);
        for (; $d <= $fin; $d = strtotime('+1 day', $d)) {
            $a = (int)date('Y', $d); $m = (int)date('n', $d); $dm = (int)date('t', $d);
            $f = self::_fila($filas, $a, $m);
            if (!$f) return ['estado' => 'sin_metas', 'motivo' => 'cobertura'];
            if (strtoupper($f['moneda']) !== $moneda) return ['estado' => 'sin_metas', 'motivo' => 'moneda'];
            $E += (float)$f['equilibrio'] / $dm;
            $P += (float)$f['meta_pesimista'] / $dm;
            $O += (float)$f['meta_optimista'] / $dm;
            if (!((int)$f['anio'] === $a && (int)$f['mes'] === $m)) $prov = true;
            $origenes[sprintf('%04d-%02d', $f['anio'], $f['mes'])] = true;
        }
        return ['estado' => 'ok', 'E' => $E, 'P' => $P, 'O' => $O,
                'provisional' => $prov, 'origen' => implode(',', array_keys($origenes))];
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
    private static function _histeresis(int $e, string $w, string $periodo, string $crudo,
                                        float $V, float $E, float $P, float $O, int $t): array
    {
        $r = ['nivel' => $crudo, 'cambio' => false, 'nivel_anterior' => null, 'cambiado_at' => null];
        try {
            $prev = DB::row("SELECT periodo, nivel, nivel_anterior, cambiado_at FROM empresa_metas_estado
                              WHERE empresa_id = ? AND ventana = ?", [$e, $w]);
            $ahora = date('Y-m-d H:i:s', $t);
            $idx_c = array_search($crudo, self::NIVELES, true);

            if (!$prev || $prev['periodo'] !== $periodo
                || ($idx_p = array_search($prev['nivel'], self::NIVELES, true)) === false) {
                DB::execute(
                    "INSERT INTO empresa_metas_estado (empresa_id, ventana, periodo, nivel, nivel_anterior, cambiado_at)
                     VALUES (?,?,?,?,NULL,?)
                     ON DUPLICATE KEY UPDATE periodo = VALUES(periodo), nivel = VALUES(nivel),
                                             nivel_anterior = NULL, cambiado_at = VALUES(cambiado_at)",
                    [$e, $w, $periodo, $crudo, $ahora]);
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

    /** Tasa real (topada) contra la deseada. */
    private static function _conv(int $enviadas, int $ventas, ?float $deseada): array
    {
        $tasa = $enviadas > 0 ? min($ventas / $enviadas, self::CONV_TOPE) : null;
        $c = ['enviadas' => $enviadas, 'ventas' => $ventas,
              'tasa' => $tasa !== null ? round($tasa, 4) : null,
              'deseada' => $deseada, 'nivel' => 'sin_meta'];
        if ($deseada === null || $deseada <= 0) return $c;
        if ($enviadas < self::CONV_MIN)            { $c['nivel'] = 'gris'; return $c; }
        if ($tasa < $deseada * (1 - self::CONV_BANDA))     $c['nivel'] = 'debajo';
        elseif ($tasa > $deseada * (1 + self::CONV_BANDA)) $c['nivel'] = 'arriba';
        else                                               $c['nivel'] = 'en';
        return $c;
    }

    /**
     * Ticket PROPIO de Metas (misma receta de vendido, 180 días, n ≥ 5).
     * Respaldo: historial_mensual de los últimos 6 meses capturados.
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
                            AND (anio * 100 + mes) <= ?
                          ORDER BY anio DESC, mes DESC LIMIT 6) x",
                [$e, (int)date('Y', $t) * 100 + (int)date('n', $t)]);
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
