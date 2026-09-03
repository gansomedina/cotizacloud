<?php
// ============================================================
//  CotizaApp — core/ScoreLectura.php
//
//  Traduce el score y sus 5 dimensiones a palabras.
//
//  Por qué existe: el reporte del asesor imprimía "Activación 24%" y ya. Un
//  porcentaje pelón no se puede accionar, y en dos de las cinco dimensiones
//  además ENGAÑA:
//
//   · Engagement sube cuando NO hay ventas que castigar. Un 89% con cero
//     ventas se lee como "va muy bien" y significa lo contrario.
//   · Seguimiento da 1.0 cuando la mesa está VACÍA (no hubo examen). El mismo
//     100% puede ser "atendió todo" o "no le pidieron nada".
//   · Activación son DOS mitades independientes: que sus cotizaciones lleguen,
//     y que él lea su diagnóstico. El mismo 24% puede ser "no abren sus
//     cotizaciones" o "no abre su diagnóstico" — consejos opuestos.
//
//  Todo son funciones puras sobre la fila de usuario_score: sin BD, sin estado.
//  Así se puede probar entera (tools/test_score_lectura.php).
// ============================================================

defined('COTIZAAPP') or die;

class ScoreLectura
{
    // ── Bandas de desempeño (decisión CEO, 3 sep 2026) ──────────
    // OJO: no son las mismas que el ENUM `nivel` del código
    // (ActividadScore:1221 → 86 top / 61 activo / 31 regular). Solo coincide el
    // techo. El reporte usa ESTAS y no muestra el ENUM, para no darle al mismo
    // asesor dos veredictos distintos en dos pantallas. Alinear el ENUM es una
    // decisión aparte: cambiaría el significado de las filas ya guardadas en
    // score_historial.
    public const EXCELENCIA = 85;
    public const ESTANDAR   = 70;
    public const PISO       = 60;

    // Pesos — espejo de ActividadScore:1004-1008. Si allá cambian, aquí también.
    public const PESOS = [
        'activacion'   => 0.13,
        'engagement'   => 0.17,
        'seguimiento'  => 0.25,
        'radar_health' => 0.10,
        'conversion'   => 0.35,
    ];

    // Cuánto del score final aporta el proporcional. El resto (~15%) lo ponen
    // momentum y percentil, que valen 0.50 cuando el asesor está estable y a
    // media tabla — o sea ~7 puntos casi fijos. Es una APROXIMACIÓN: el peso
    // real se calcula con el tamaño del equipo y la tasa de cierre de la
    // empresa (ActividadScore:1133-1137). Se usa solo para estimar cuántos
    // puntos vale cada dimensión; el ORDEN de las oportunidades no depende de
    // esta constante, porque es un factor común a todas.
    private const K_PROPORCIONAL = 0.85;

    // ─── Banda de desempeño ──────────────────────────────────
    public static function banda(int $score): array
    {
        if ($score >= self::EXCELENCIA) return ['clave'=>'excelencia', 'txt'=>'Excelencia',           'color'=>'#1f9d57'];
        if ($score >= self::ESTANDAR)   return ['clave'=>'estandar',   'txt'=>'En el estándar',       'color'=>'#1f9d57'];
        if ($score >= self::PISO)       return ['clave'=>'debajo',     'txt'=>'Debajo del estándar',  'color'=>'#c07d16'];
        return                                 ['clave'=>'critico',    'txt'=>'Crítico',              'color'=>'#dc2626'];
    }

    /**
     * Las 5 dimensiones traducidas.
     * $s = fila de usuario_score (array asociativo).
     * Devuelve una lista con: label, pct, frase, crudo (el dato detrás) y
     * alerta (cuando el número engaña).
     */
    public static function dimensiones(array $s): array
    {
        $n   = fn(string $k, $d = 0) => $s[$k] ?? $d;
        $pct = fn(string $k) => (int)round(((float)($s[$k] ?? 0)) * 100);

        return [
            self::_activacion($s, $n, $pct),
            self::_engagement($s, $n, $pct),
            self::_seguimiento($s, $n, $pct),
            self::_radar_health($s, $n, $pct),
            self::_conversion($s, $n, $pct),
        ];
    }

    // ── 1. Activación (13%) — dos mitades independientes ─────
    private static function _activacion(array $s, callable $n, callable $pct): array
    {
        $op    = (float)$n('s_activacion_op');
        $tips  = (float)$n('tips_score');
        $nab   = (int)$n('no_abiertas_5d');
        $dorm  = (int)$n('cot_dormidas');
        $vis   = (int)$n('cot_vistas');
        $asig  = (int)$n('cot_asignadas');
        $dlec  = (int)$n('dias_lectura');
        $dact  = (int)$n('dias_activos_feature');

        // La mitad de lectura: días que abrió su diagnóstico / días que usó el
        // sistema desde que la función existe para él (ActividadScore:497).
        if ($dact > 0) {
            $ftips = $tips >= 1.0
                ? "Abres tu diagnóstico casi siempre ({$dlec} de {$dact} días activos)."
                : ($tips >= 0.5
                    ? "Abres tu diagnóstico a medias ({$dlec} de {$dact} días activos). Llegando al 70% esta mitad vale doble."
                    : "Casi no abres tu diagnóstico ({$dlec} de {$dact} días activos). Es la mitad de esta nota y son unos segundos al día.");
        } else {
            $ftips = "Nunca has abierto tu diagnóstico. Esa es la mitad de esta nota, y hoy vale cero.";
        }

        // La mitad operativa: una sola cotización sin abrir a los 5 días la mata.
        if ($nab > 0) {
            $fop = $nab === 1
                ? "Tienes 1 cotización que el cliente nunca abrió en 5+ días. Mientras siga así, esta mitad vale cero — da igual cuántas mandes."
                : "Tienes {$nab} cotizaciones que el cliente nunca abrió en 5+ días. Mientras sigan así, esta mitad vale cero — da igual cuántas mandes.";
        } elseif ($dorm > 0 && $vis > 0) {
            $fop = "Tus cotizaciones llegan y se abren ({$vis} de {$asig}), pero {$dorm} clientes la vieron y no volvieron en 7+ días.";
        } elseif ($asig > 0) {
            $fop = "Tus cotizaciones llegan y se abren ({$vis} de {$asig}). Esta mitad está sana.";
        } else {
            $fop = "Sin cotizaciones asignadas en la ventana.";
        }

        return [
            'clave' => 'activacion',
            'label' => 'Activación',
            'peso'  => self::PESOS['activacion'],
            'pct'   => $pct('s_activacion'),
            'frase' => $fop . ' ' . $ftips,
            'crudo' => "operativa " . (int)round($op * 100) . "% · lectura " . (int)round($tips * 100) . "%",
            'alerta'=> null,
            // Para el reporte: esta dimensión NO se puede leer como un solo número.
            'partes'=> [
                ['label' => 'llegan y se abren', 'pct' => (int)round(max($op, 0) * 100), 'frase' => $fop],
                ['label' => 'lees tu diagnóstico', 'pct' => (int)round($tips * 100),      'frase' => $ftips],
            ],
        ];
    }

    // ── 2. Engagement (17%) — sube por ausencia de castigos ──
    private static function _engagement(array $s, callable $n, callable $pct): array
    {
        $v      = $pct('s_engagement');
        $ventas = (int)$n('ventas_periodo');
        $sinpag = (int)$n('ventas_sin_pago');

        $alerta = null;
        if ($ventas === 0 && $v >= 80) {
            // El caso que más engaña de las cinco.
            $alerta = "Este número está alto porque NO hay ventas que castigar: sin ventas no hay cobros pendientes ni descuentos. No lo leas como algo bueno.";
            $frase  = "Sin ventas en la ventana, así que no hay nada que evaluar aquí.";
        } elseif ($v >= 85) {
            $frase = "Cobras lo que vendes y no estás comprando las ventas con descuento.";
        } elseif ($v >= 60) {
            $frase = $sinpag > 0
                ? "Tienes {$sinpag} " . ($sinpag === 1 ? "venta sin cobrar" : "ventas sin cobrar") . " — eso es lo que te está restando."
                : "Estás dando descuentos o vendiendo por debajo del promedio del equipo.";
        } else {
            $frase = $sinpag > 0
                ? "{$sinpag} " . ($sinpag === 1 ? "venta sin un solo pago" : "ventas sin un solo pago") . ". Cerrar sin cobrar no es cerrar."
                : "Estás cerrando a puro descuento o muy por debajo del promedio del equipo.";
        }

        return ['clave'=>'engagement','label'=>'Engagement','peso'=>self::PESOS['engagement'],
                'pct'=>$v,'frase'=>$frase,
                'crudo'=>"{$ventas} " . ($ventas === 1 ? 'venta' : 'ventas') . " · {$sinpag} sin cobrar",
                'alerta'=>$alerta,'partes'=>null];
    }

    // ── 3. Seguimiento (25%) — la mesa ───────────────────────
    private static function _seguimiento(array $s, callable $n, callable $pct): array
    {
        $v    = $pct('s_seguimiento');
        $ped  = (int)$n('mesa_pedidas');
        $at   = (int)$n('mesa_atendidas');
        $venc = (int)$n('mesa_dias_vencidos');
        $cast = (int)$n('castigo_seguimiento');

        $alerta = null;
        if ($ped === 0) {
            // 1.0 neutro por mesa vacía — NO es mérito (ActividadScore:842).
            $alerta = "Tu mesa no te pidió nada en la ventana. Este 100% es neutro, no es mérito: no hubo nada que atender.";
            $frase  = "Sin señales en tu mesa durante la ventana.";
        } else {
            $cov = (int)round($at / max($ped, 1) * 100);
            if ($v >= 100)     $frase = "Atendiste {$at} de {$ped} señales de tu mesa ({$cov}%). Completo.";
            elseif ($v >= 50)  $frase = "Atendiste {$at} de {$ped} ({$cov}%). A medias: cuenta la mitad. Llegando al 80% cuenta completo.";
            else               $frase = "Atendiste {$at} de {$ped} ({$cov}%). Debajo de la mitad no cuenta nada — y esta dimensión es la segunda que más pesa.";
        }

        if ($cast > 0) {
            // Puntos DIRECTOS al score, aparte de la dimensión (ActividadScore:1191).
            $alerta = trim(($alerta ? $alerta . ' ' : '')
                   . "Además te está restando {$cast} " . ($cast === 1 ? 'punto' : 'puntos')
                   . " directos del score por {$venc} " . ($venc === 1 ? 'día vencido' : 'días vencidos')
                   . " en la mesa. Tocarlas detiene el reloj; los días viejos salen solos en ~2 semanas.");
        }

        return ['clave'=>'seguimiento','label'=>'Seguimiento','peso'=>self::PESOS['seguimiento'],
                'pct'=>$v,'frase'=>$frase,
                // Con la mesa vacía el crudo sería "0 de 0" — ruido al lado de
                // la alerta, que ya explica que el 100% es neutro.
                'crudo'=>$ped > 0 ? "{$at} de {$ped} señales" : null,
                'alerta'=>$alerta,'partes'=>null];
    }

    // ── 4. Radar Health (10%) — clientes calientes que mueren ─
    private static function _radar_health(array $s, callable $n, callable $pct): array
    {
        $v    = $pct('s_radar_health');
        // Columnas legacy reusadas: transiciones_up = calientes, senales_ignoradas = muertas
        $cal  = (int)($s['health_up']   ?? $s['transiciones_up']   ?? 0);
        $mue  = (int)($s['health_down'] ?? $s['senales_ignoradas'] ?? 0);

        $alerta = null;
        if ($cal === 0) {
            $alerta = "Ningún cliente se puso caliente en la ventana. El 50% es el neutro del sistema, no una calificación.";
            $frase  = "Sin clientes calientes que evaluar.";
        } else {
            $pctm = (int)round($mue / max($cal, 1) * 100);
            if ($v >= 70)      $frase = "De {$cal} clientes que mostraron interés real, solo perdiste {$mue}. Cuando se calientan, los trabajas.";
            elseif ($v >= 40)  $frase = "De {$cal} clientes con interés real, se te murieron {$mue} ({$pctm}%). Ya habían levantado la mano.";
            else               $frase = "De cada 10 clientes que se interesaron de verdad, " . (int)round($pctm / 10) . " se te murieron sin cerrar ({$mue} de {$cal}). No es temporada: es seguimiento.";
        }

        return ['clave'=>'radar_health','label'=>'Radar Health','peso'=>self::PESOS['radar_health'],
                'pct'=>$v,'frase'=>$frase,
                'crudo'=>$cal > 0 ? "{$mue} muertos de {$cal} calientes" : null,
                'alerta'=>$alerta,'partes'=>null];
    }

    // ── 5. Conversión (35%) — la que más pesa ────────────────
    private static function _conversion(array $s, callable $n, callable $pct): array
    {
        $v    = $pct('s_conversion');
        $tc   = (float)$n('tasa_cierre');
        $vent = (int)$n('ventas_periodo');
        $bench= (float)$n('bench_ventas');

        if ($v >= 60)      $frase = "Cierras por encima de lo que cierra tu empresa (" . round($tc * 100) . "% de cierre).";
        elseif ($v >= 30)  $frase = "Cierras, pero debajo del promedio de la empresa (" . round($tc * 100) . "%).";
        elseif ($vent === 0) $frase = "No cerraste ninguna venta en la ventana. Es el 35% del score: ninguna otra dimensión compensa esto.";
        else               $frase = "Casi no estás cerrando (" . round($tc * 100) . "%). Es el 35% del score — pesa más que las otras cuatro juntas menos una.";

        $alerta = null;
        if ($bench > 0 && $vent < $bench * 0.5) {
            $alerta = "Vas en {$vent} " . ($vent === 1 ? 'venta' : 'ventas') . " contra " . round($bench, 1) . " que es el ritmo de la empresa. Eso también te resta en Engagement.";
        }

        return ['clave'=>'conversion','label'=>'Conversión','peso'=>self::PESOS['conversion'],
                'pct'=>$v,'frase'=>$frase,
                'crudo'=>"{$vent} " . ($vent === 1 ? 'venta' : 'ventas') . " · " . round($tc * 100) . "% de cierre",
                'alerta'=>$alerta,'partes'=>null];
    }

    /**
     * Brecha al estándar: cuántos puntos faltan y de dónde sacarlos.
     *
     * El orden de las oportunidades es EXACTO (el factor K es común a todas);
     * los puntos absolutos son una estimación — ver K_PROPORCIONAL.
     */
    public static function brecha(array $s, int $meta = self::ESTANDAR): array
    {
        $score  = (int)($s['score'] ?? 0);
        $faltan = max(0, $meta - $score);

        $ops = [];
        foreach (self::PESOS as $clave => $peso) {
            $col = $clave === 'radar_health' ? 's_radar_health' : "s_{$clave}";
            $val = (float)($s[$col] ?? 0);
            $margen = max(0.0, 1.0 - $val);
            $ops[] = [
                'clave'  => $clave,
                'actual' => (int)round($val * 100),
                'puntos' => round($margen * $peso * 100 * self::K_PROPORCIONAL, 1),
            ];
        }
        // De más a menos puntos disponibles.
        usort($ops, fn($a, $b) => $b['puntos'] <=> $a['puntos']);

        return ['score'=>$score, 'meta'=>$meta, 'faltan'=>$faltan, 'oportunidades'=>$ops];
    }

    /**
     * Hoy contra su propio historial.
     *
     * $prom / $peor / $mejor vienen de score_diario (null si no hay historial).
     * $dias = cuántas fotos hay en la ventana. IMPORTANTE: score_diario guarda
     * el MÁXIMO del día y no hay cron, así que faltan días — un hueco significa
     * "nadie abrió el sistema", no "día malo".
     */
    public static function tendencia(int $hoy, ?float $prom, int $dias, ?int $peor = null, ?int $mejor = null): array
    {
        if ($prom === null || $dias < 1) {
            return ['clave'=>'sin_datos', 'delta'=>null, 'dias'=>$dias,
                    'txt'=>'Sin historial suficiente para comparar. Se necesita que alguien abra el sistema para que se guarde la foto del día.',
                    'confiable'=>false, 'rango'=>null];
        }

        $delta = round($hoy - $prom, 1);
        // ±3 con ventana de 15 días y foto del máximo es ruido, no movimiento.
        $dir = $delta > 3 ? 'sube' : ($delta < -3 ? 'baja' : 'estable');

        // Menos de 10 fotos en 30 días no es tendencia, es anécdota.
        $confiable = $dias >= 10;
        $rango = ($peor !== null && $mejor !== null) ? ($mejor - $peor) : null;

        $t = [
            'sube'    => "Va subiendo: hoy {$hoy} contra {$prom} de promedio (+{$delta}).",
            'baja'    => "Va bajando: hoy {$hoy} contra {$prom} de promedio ({$delta}).",
            'estable' => "Estable: hoy {$hoy} contra {$prom} de promedio. La diferencia es ruido.",
        ][$dir];

        if (!$confiable) {
            $t .= " Ojo: solo hay {$dias} " . ($dias === 1 ? 'foto' : 'fotos') . " del período — es anécdota, no tendencia.";
        }
        // Un rango ancho hace que "hoy" sea casi aleatorio.
        if ($rango !== null && $rango >= 30) {
            $t .= " Su score se movió entre {$peor} y {$mejor} en el período: con ese vaivén, el número de hoy dice poco — lee el promedio.";
        }

        return ['clave'=>$dir, 'delta'=>$delta, 'dias'=>$dias, 'txt'=>$t,
                'confiable'=>$confiable, 'rango'=>$rango];
    }

    /**
     * El veredicto de una línea: cruza banda con tendencia.
     * Es la frase que resume todo el bloque.
     */
    public static function veredicto(int $score, string $tendencia): string
    {
        $b = self::banda($score)['clave'];

        $m = [
            'excelencia' => [
                'sube'    => 'Excelencia y todavía subiendo. Vale la pena documentar qué está haciendo para replicarlo.',
                'estable' => 'Excelencia sostenida. No hay nada que corregir, solo sostener.',
                'baja'    => 'Excelencia pero soltando. Este es el momento de mirar, no cuando ya cayó.',
            ],
            'estandar' => [
                'sube'    => 'En el estándar y con impulso. Va camino a excelencia.',
                'estable' => 'En el estándar y estable. Este es el punto correcto: no necesita intervención.',
                'baja'    => 'En el estándar pero bajando. Atajarlo ahora — hay poco colchón antes del piso.',
            ],
            'debajo' => [
                'sube'    => 'Debajo del estándar pero reaccionando. Seguirlo de cerca estas dos semanas.',
                'estable' => 'Estancado debajo del estándar. Lo cómodo de este caso es lo peligroso: nadie lo ve como urgencia.',
                'baja'    => 'Debajo del estándar y cayendo. Intervención esta semana.',
            ],
            'critico' => [
                'sube'    => 'En crítico pero reaccionando. Reconocérselo: es la única señal buena que tiene hoy.',
                'estable' => 'Crítico y estancado. Es el caso más grave del tablero — más que el que cae, porque el que cae al menos se mueve.',
                'baja'    => 'Crítico y empeorando. Urgente.',
            ],
        ];

        return $m[$b][$tendencia] ?? $m[$b]['estable'];
    }
}
