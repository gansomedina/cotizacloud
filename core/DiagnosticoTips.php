<?php
// ============================================================
//  core/DiagnosticoTips.php
//  Tips del termómetro en voz de gerente comercial — TÁCTICOS.
//
//  Filosofía: NO motivar ni repetir el diagnóstico. Dar la JUGADA real
//  y universal (sirve a cualquier giro SaaS): el "no hagas X, haz Y",
//  el script, y a quién priorizar. Segmentado por TIER (tono + dureza).
//
//  Estructura del mensaje:
//    [prefijo tier] · diagnóstico breve · LA JUGADA (táctica) · [nag] · [consecuencia si bajo]
//
//  Consistencia: piezas vetadas + helper _pl(). Variedad multiplicativa:
//  fuga × pools de jugadas × tier × datos vivos × rotación (usuario+día).
//
//  PURO: no toca BD. Recibe $s (score) y $ctx. Testeable en aislamiento.
// ============================================================

defined('COTIZAAPP') or die;

final class DiagnosticoTips
{
    private static function _pl(int $n, string $sing, string $plur): string
    {
        return $n . ' ' . ($n === 1 ? $sing : $plur);
    }
    private static function _pick(array $pool, int $seed): string
    {
        return empty($pool) ? '' : $pool[$seed % count($pool)];
    }

    public static function build(array $s, ?array $ctx = null): string
    {
        $m = self::_metricas($s, $ctx);

        if (($s['nivel'] ?? '') === 'nuevo') {
            return 'Recopilando información — tu score se está activando. Sigue cotizando y dando seguimiento; en unos días el termómetro empieza a leerte.';
        }
        if ($m['asig'] === 0) {
            return 'No cotizaste en el período. Sin propuestas no hay de dónde salir ventas. Manda cotizaciones hoy.';
        }

        $tier = self::_tier($m['score'], $m);
        $fuga = self::_detectar_fuga($m, $tier);
        $seed = ((int)($s['usuario_id'] ?? 0)) + (int)date('z');

        return self::_componer($m, $tier, $fuga, $seed);
    }

    private static function _metricas(array $s, ?array $ctx): array
    {
        $asig    = (int)($s['cot_asignadas'] ?? 0);
        $vist    = (int)($s['cot_vistas'] ?? 0);
        $cierres = (int)($s['conversiones'] ?? 0);
        $bench   = (float)($ctx['close_rate'] ?? 0.10);
        $tasa    = $vist > 0 ? $cierres / $vist : 0.0;
        $cal     = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $fb      = (int)($s['fb_total'] ?? $s['radar_views'] ?? 0);
        if ($cal > 0 && $fb > $cal) $fb = $cal;
        $sdto    = (int)($s['cierres_sin_dto'] ?? $cierres);
        return [
            'score'      => (int)($s['score'] ?? 0),
            'asig'       => $asig,
            'vist'       => $vist,
            'cierres'    => $cierres,
            'sin_cerrar' => max(0, $vist - $cierres),
            'sin_abrir'  => max(0, $asig - $vist),
            'aper'       => $asig > 0 ? $vist / $asig : 0.0,
            'dorm'       => (int)($s['cot_dormidas'] ?? 0),
            'nab'        => (int)($s['no_abiertas_5d'] ?? 0),
            'cal'        => $cal,
            'fb'         => $fb,
            'ign'        => max(0, $cal - $fb),
            'exp'        => (int)($s['calientes_exploradas'] ?? 0),
            'vsp'        => (int)($s['ventas_sin_pago'] ?? 0),
            'mom'        => (float)($s['momentum'] ?? 1),
            'bench'      => $bench,
            'bench_pct'  => (int)round($bench * 100),
            'tasa'       => $tasa,
            'tasa_pct'   => (int)round($tasa * 100),
            'con_dto'    => max(0, $cierres - $sdto),
            'h_up'       => (int)($s['health_up'] ?? $s['transiciones_up'] ?? 0),
            'h_down'     => (int)($s['health_down'] ?? $s['senales_ignoradas'] ?? 0),
            'tips_s'     => (float)($s['tips_score'] ?? 1),
            'dias_act'   => (int)($s['dias_activos'] ?? 0),
            'bcierre'    => (int)($s['bonus_cierre'] ?? 0),
        ];
    }

    private static function _tier(int $score, array $m): string
    {
        if ($score >= 85 || $m['bcierre'] >= 8) return 'top';
        if ($score >= 70) return 'activo';
        if ($score >= 45) return 'regular';
        return 'bajo';
    }

    private static function _detectar_fuga(array $m, string $tier): string
    {
        if ($tier === 'top') return 'top';
        if ($m['asig'] < 8 && $m['cierres'] === 0 && $m['nab'] === 0) return 'muestra_chica';
        if ($m['asig'] >= 4 && $m['aper'] < 0.5) return 'no_abren';
        if ($m['ign'] >= 3) return 'caliente';
        if ($m['h_up'] >= 3 && $m['h_down'] >= $m['h_up'] * 0.5) return 'radar_health';
        if ($m['cierres'] >= 2 && $m['vsp'] >= 2 && $m['vsp'] >= $m['cierres'] * 0.5) return 'cobro';
        if ($m['dorm'] >= 3 && $m['bench'] > 0 && $m['tasa'] >= $m['bench'] * 0.85) return 'dormidas';
        if ($m['vist'] >= 8 && $m['bench'] > 0 && $m['tasa'] < $m['bench'] * 0.85) return 'cierre_bajo';
        if ($m['bench'] > 0 && $m['tasa'] >= $m['bench'] && $m['vist'] < 8 && $m['cierres'] >= 1) return 'volumen';
        if ($m['cierres'] >= 3 && $m['con_dto'] >= $m['cierres'] * 0.5) return 'margen';
        if ($m['mom'] <= 0.75) return 'momentum';
        return 'neutro';
    }

    // ════════════════════════════════════════════════════════
    //  COMPOSICIÓN — diagnóstico breve + LA JUGADA
    // ════════════════════════════════════════════════════════

    private static function _componer(array $m, string $tier, string $fuga, int $seed): string
    {
        if ($fuga === 'top')           return self::_render_top($m, $seed);
        if ($fuga === 'muestra_chica') return self::_render_muestra_chica($m, $seed);

        $partes = [];

        // Prefijo de tono (breve, factual). El opener es el diagnóstico corto.
        $pre = self::_prefijo_tier($tier, $seed);
        $diag = self::_diagnostico($fuga, $m, $seed);
        $partes[] = $pre !== '' ? ($pre . ' ' . $diag) : $diag;

        // LA JUGADA — el núcleo. Táctica universal + a quién priorizar.
        $partes[] = self::_jugada($fuga, $m, $seed);

        // Nag de herramientas (crisp, y tácticamente útil): solo regular/bajo.
        if ($tier === 'regular' || $tier === 'bajo') {
            $nag = self::_tips_nag($m, $seed);
            if ($nag !== '') $partes[] = $nag;
        }

        // Consecuencia FACTUAL solo en bajo (sin aliento).
        if ($tier === 'bajo') {
            $partes[] = self::_consecuencia_bajo($seed);
        }

        return implode(' ', $partes);
    }

    private static function _prefijo_tier(string $tier, int $seed): string
    {
        return match ($tier) {
            'bajo'   => self::_pick(['Sin rodeos.', 'Directo:', 'Esto es serio.'], $seed + 1),
            'activo' => self::_pick(['Vas bien; te falta un ajuste.', 'Buen nivel — afina esto.', 'Cerca del top:'], $seed + 1),
            default  => '',
        };
    }

    // ── DIAGNÓSTICO breve (1 frase con el dato, sin repetir) ──
    private static function _diagnostico(string $fuga, array $m, int $seed): string
    {
        $v = $m['vist']; $c = $m['cierres']; $bp = $m['bench_pct']; $tp = $m['tasa_pct'];
        $sa = $m['sin_abrir']; $asig = $m['asig']; $ign = $m['ign']; $dorm = $m['dorm'];
        $hu = $m['h_up']; $hd = $m['h_down'];

        $pool = match ($fuga) {
            'cierre_bajo' => [
                "{$v} clientes abrieron tu propuesta y cerraste " . ($c === 1 ? 'solo 1' : "solo {$c}") . " — {$tp}% cuando la empresa cierra {$bp}%.",
                "Tu problema no es el volumen, es el remate: " . self::_pl($c, 'venta', 'ventas') . " de {$v} vistas.",
                "Abren tu cotización y no compran: {$tp}% de cierre contra {$bp}% de la empresa.",
            ],
            'caliente' => [
                "Tienes " . self::_pl($ign, 'cliente caliente sin atender', 'clientes calientes sin atender') . " en el Radar — la venta más cercana que tienes hoy.",
                "El Radar te marcó " . self::_pl($ign, 'oportunidad lista', 'oportunidades listas') . " y " . ($ign === 1 ? 'sigue' : 'siguen') . " sin seguimiento.",
            ],
            'radar_health' => [
                "De " . self::_pl($hu, 'cliente que se calentó', 'clientes que se calentaron') . ", " . ($hd === 1 ? 'uno se enfrió' : "{$hd} se enfriaron") . " sin cierre.",
                "Estás dejando envejecer lo caliente: " . self::_pl($hd, 'cliente con interés real se enfrió', 'clientes con interés real se enfriaron') . ".",
            ],
            'dormidas' => [
                "Cierras bien, pero " . self::_pl($dorm, 'cliente abrió y no volviste a tocarlo', 'clientes abrieron y no volviste a tocarlos') . ".",
                "Tu fuga es la constancia: " . self::_pl($dorm, 'cliente interesado', 'clientes interesados') . " sin seguimiento.",
            ],
            'no_abren' => [
                "{$sa} de {$asig} cotizaciones ni se abrieron — la venta se te cae en el primer escalón.",
                "Solo {$v} de {$asig} propuestas se abrieron. El resto no llegó a los ojos del cliente.",
            ],
            'volumen' => [
                "Cierras a buen ritmo, pero con " . self::_pl($v, 'sola propuesta vista', 'propuestas vistas') . " el techo es bajo.",
                "Tu tasa de cierre está bien; lo que falta son tiros: cotizaste poco.",
            ],
            'cobro' => [
                self::_pl($m['vsp'], 'venta cerrada sin un peso cobrado', 'ventas cerradas sin un peso cobrado') . ".",
                "Cierras pero no cobras: " . self::_pl($m['vsp'], 'venta', 'ventas') . " sin abono.",
            ],
            'margen' => [
                "Buena parte de tus cierres van con descuento — el número se ve bien, el margen no.",
                "Estás cerrando a base de rebaja.",
            ],
            'momentum' => [
                "Vienes cayendo respecto a tu propio nivel reciente.",
                "Tu tendencia bajó: rendías más el período pasado.",
            ],
            'neutro' => [
                "Sin fuga grave ni mes de récord — estás en zona pareja.",
                "Tus números están en su lugar, sin alarmas ni brillo.",
            ],
            default => ["Tus números están por debajo de lo que la empresa necesita."],
        };
        return self::_pick($pool, $seed);
    }

    // ════════════════════════════════════════════════════════
    //  LA JUGADA — táctica universal + priorización (el núcleo)
    // ════════════════════════════════════════════════════════

    private static function _jugada(string $fuga, array $m, int $seed): string
    {
        $ign = $m['ign']; $dorm = $m['dorm'];

        $pool = match ($fuga) {
            'cierre_bajo' => [
                "Cuando alguien lee tu propuesta y no compra, casi siempre hay una objeción que no te dijo. No la resuelves mandando otra cotización: llámale — llamada, no mensaje — y pregunta directo \"¿qué le falta para decidir?\". El que contesta te da el guion para cerrar.",
                "Prioriza a los que abrieron tu cotización más de una vez: repetir la lectura es interés real con una duda atorada. Una llamada corta la destraba mejor que diez mensajes.",
                "Deja de perseguir clientes nuevos y trabaja a los que ya te dieron su atención. Llámales, escucha qué los frena y resuélvelo en la llamada, no por escrito. Ahí está el cierre que te falta.",
                "Si revisó el precio y no volvió, no bajes el número: no quiere descuento, quiere justificar el gasto. Dale una razón — qué incluye que otros no — o propón arrancar con un anticipo parcial y el resto conforme avanza.",
                "Re-enviar la propuesta \"por si acaso\" no mueve nada. Lo que mueve es una pregunta directa por teléfono: \"¿qué necesita ver para avanzar?\". Empieza por los que más veces la abrieron.",
            ],
            'caliente' => [
                ($ign === 1 ? "Llámalo mientras sigue viendo la propuesta" : "Llámalos mientras siguen viendo la propuesta") . ". Abre con un motivo, no con venta: \"vi que estaba revisando su cotización, ¿le resuelvo alguna duda?\". El que está activo ahora cierra tres veces más que uno frío.",
                "No abras con precio ni con lista de beneficios — ya los vieron. Abre preguntando qué les falta para decidir. Están a un paso; solo necesitan que se lo pongas fácil.",
                "La velocidad es todo: no lo dejes para la tarde. " . ($ign === 1 ? 'Ese cliente está en modo compra hoy' : 'Esos clientes están en modo compra hoy') . ". Un \"noté su interés, ¿avanzamos?\" a tiempo basta.",
            ],
            'radar_health' => [
                "Reactívalos con un motivo concreto, no con un \"seguimos en contacto\": una opción de pago, una duda resuelta, un dato nuevo. La ventana se cierra rápido — hoy todavía te recuerdan, en una semana no.",
                "Tu regla: atiende lo caliente el mismo día que sube, no cuando tengas tiempo. Revisa el Radar a diario y no dejes que ningún cliente con interés envejezca sin una llamada.",
            ],
            'dormidas' => [
                "El error es escribir \"¿sigue en pie?\": suena a cobro y los aleja. Reabre con una pregunta útil: \"¿le quedó clara la propuesta o hay algo que ajustar?\". Empieza por los más recientes, que aún te recuerdan.",
                "Dales una razón para volver, no un recordatorio: un dato nuevo, una opción de pago, o resuelve la duda con la que se quedaron. El seguimiento que cierra da, no pide.",
                "Reactívalos uno por uno con un mensaje personal, no en cadena. \"¿Pudo revisar la propuesta? Quedo al pendiente de cualquier duda\" abre más que un genérico de recordatorio.",
            ],
            'no_abren' => [
                "El correo se pierde en spam. Manda el link por WhatsApp con una línea personal, o llama: \"le acabo de enviar su propuesta, ¿le llegó bien?\". Primero que la vean; sin eso no hay venta que perseguir.",
                "Confirma la recepción, no la asumas. Un \"¿le llegó la cotización?\" por el canal que sí usa el cliente destraba más que reenviarla diez veces por el mismo medio.",
            ],
            'volumen' => [
                "Tu techo es cuántas propuestas mandas. Ponte una meta diaria de cotizaciones nuevas: con tu tasa de cierre, cada una extra es casi dinero seguro.",
                "Cierras bien; falta llenar el embudo. Bloquea una hora al día solo para prospectar y cotizar. El volumen es tu única palanca pendiente.",
            ],
            'cobro' => [
                "El cobro se pacta al cerrar, no después. Ponle condición: \"para apartar necesito un anticipo\". Un sí sin anticipo se enfría; con anticipo, el cliente se compromete.",
                "Trabaja tus ventas sin pago como cierres nuevos: llama, pon una fecha de pago concreta y no la sueltes. Hasta que no entra el dinero, no es venta.",
            ],
            'margen' => [
                "Cuando pidan descuento, no lo des de inmediato: pregunta primero qué los frena — muchas veces no es el precio. Si tienes que ceder, cede en plazo o extras, no en el número.",
                "El descuento fácil enseña al cliente a pedir más. Ancla el valor: qué incluye que otros no, y por qué a la larga le sale más barato contigo. Cierra igual, sin regalar margen.",
            ],
            'momentum' => [
                "Elige un día para reordenarte: revisa qué dejaste caer del Radar y retómalo. El ritmo se recupera actuando sobre lo pendiente, no empezando de cero.",
                "Vuelve a tu rutina que sí funcionaba: revisa el Radar a diario y atiende lo que tiene movimiento el mismo día. Dos días enfocado y el marcador se endereza.",
            ],
            'neutro' => [
                "Aprieta una sola tuerca esta semana: no dejes ni un cliente caliente sin atender el mismo día que aparece en el Radar. De ahí sale tu siguiente escalón.",
                "Elige tu palanca: o subes volumen de propuestas, o aprietas el seguimiento a lo caliente. Una sola, bien hecha, te mueve de estable a bueno.",
            ],
            default => ["Entra al Radar, atiende lo que tiene movimiento el mismo día, y prioriza a quien ya mostró interés."],
        };
        $jug = self::_pick($pool, $seed);

        // Priorización con dato real cuando aplica (a QUIÉN empezar).
        if ($fuga !== 'caliente' && $fuga !== 'dormidas') {
            if ($ign > 0) {
                $jug .= ' ' . ($ign === 1
                    ? "Y hay 1 cliente activo en el Radar ahora mismo: ese es el primero de la lista."
                    : "Y hay {$ign} clientes activos en el Radar ahora mismo: esos son los primeros de la lista.");
            } elseif ($dorm > 0 && $fuga !== 'no_abren') {
                $jug .= ' ' . ($dorm === 1
                    ? "Arranca con el cliente que abrió y no volvió."
                    : "Arranca con los {$dorm} que abrieron y no volvieron.");
            }
        }
        return $jug;
    }

    // ── Nag de herramientas (tácticamente útil) ─────────────
    private static function _tips_nag(array $m, int $seed): string
    {
        if ($m['dias_act'] <= 0) return '';
        $no_tips = $m['tips_s'] <= 0.0;
        $no_explora = $m['cal'] > 0 && ($m['exp'] / max(1, $m['cal'])) < 0.30;

        if ($no_explora) {
            return self::_pick([
                "Antes de llamar, abre el ❓ de cada cotización caliente: te dice qué señal la disparó (precio, re-visita, varios dispositivos) y con eso llegas sabiendo qué objetar.",
                "Usa el ❓ del Radar en tus calientes: ahí ves por qué se marcó cada una. Llegar con esa info a la llamada es media venta hecha.",
            ], $seed + 9);
        }
        if ($no_tips) {
            return self::_pick([
                "Y abre el análisis completo del termómetro: ahí tienes el desglose de qué dimensión te está frenando.",
                "Revisa tu desglose por dimensión en el termómetro — te dice con número dónde está la fuga exacta.",
            ], $seed + 9);
        }
        return '';
    }

    // ── Consecuencia factual (solo bajo, sin aliento) ───────
    private static function _consecuencia_bajo(int $seed): string
    {
        return self::_pick([
            "Con estos números el mes no cierra, y eso pega a todo el equipo. Tiene que cambiar esta semana.",
            "Así no se sostiene. La empresa necesita que estos números reaccionen ya, no el mes que entra.",
            "Cada semana así cuesta dinero real. Esto es prioridad hoy.",
        ], $seed + 11);
    }

    // ── TOP ─────────────────────────────────────────────────
    private static function _render_top(array $m, int $seed): string
    {
        $partes = [];
        $partes[] = self::_pick([
            "Vas arriba: cierras por encima del promedio de la empresa.",
            "Mes fuerte — tu cierre está claramente sobre la media.",
            "Rendimiento de primera este período.",
        ], $seed);

        if ($m['ign'] > 0) {
            $partes[] = ($m['ign'] === 1
                ? "Para redondearlo: tienes 1 cliente caliente en el Radar sin atender. Llámalo mientras esté activo y el mes es histórico."
                : "Para redondearlo: {$m['ign']} clientes calientes en el Radar sin atender. Atiéndelos hoy, mientras están activos, y el mes es histórico.");
        } else {
            $partes[] = self::_pick([
                "Vuelve repetible lo que haces: documenta tu proceso de seguimiento para que sea tu estándar, no tu buen mes.",
                "Siguiente nivel: comparte tu método con el equipo. Lo que a ti te sale natural, a otros les falta.",
            ], $seed + 4);
        }
        return implode(' ', $partes);
    }

    // ── MUESTRA CHICA ───────────────────────────────────────
    private static function _render_muestra_chica(array $m, int $seed): string
    {
        $a = $m['asig'];
        $op = self::_pick([
            "Apenas " . self::_pl($a, 'cotización en el período', 'cotizaciones en el período') . " — muy poco para juzgar tu cierre. Tu prioridad ahora es volumen: manda más propuestas.",
            "Con " . self::_pl($a, 'sola cotización', 'cotizaciones') . " el termómetro casi no tiene qué leer. Sube el número de propuestas y los números empiezan a hablar.",
        ], $seed);
        if ($m['ign'] > 0) {
            $op .= ' ' . ($m['ign'] === 1
                ? "Y si ya tienes 1 cliente caliente en el Radar, atiéndelo hoy — no dejes ir lo poco activo que tienes."
                : "Y si ya tienes {$m['ign']} clientes calientes en el Radar, atiéndelos hoy — no dejes ir lo poco activo que tienes.");
        }
        return $op;
    }
}
