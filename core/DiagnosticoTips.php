<?php
// ============================================================
//  core/DiagnosticoTips.php
//  Motor de "tips" del termómetro en voz de gerente comercial.
//
//  Diagnostica la FUGA #1 del embudo, dice a QUIÉN atender con datos
//  reales, da UNA acción, cierra con consecuencia. TODO segmentado por
//  TIER de score (tono + largo).
//
//  Consistencia: se compone de PIEZAS vetadas (opener/arma/acción/cierre)
//  + helper _pl() de plurales. Nunca strings sueltos por combinación.
//
//  Variedad multiplicativa: esqueleto × fuga × pools × tier × datos vivos
//  × rotación (usuario_id + día). Un lector diario tarda meses en cruzarlo.
//
//  PURO: no toca BD. Recibe $s (score) y $ctx (contexto). Testeable solo.
// ============================================================

defined('COTIZAAPP') or die;

final class DiagnosticoTips
{
    // ── Helpers ─────────────────────────────────────────────

    private static function _pl(int $n, string $sing, string $plur): string
    {
        return $n . ' ' . ($n === 1 ? $sing : $plur);
    }
    private static function _pick(array $pool, int $seed): string
    {
        return empty($pool) ? '' : $pool[$seed % count($pool)];
    }
    private static function _cap(string $s): string
    {
        return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1);
    }

    // ── Entrada ─────────────────────────────────────────────

    public static function build(array $s, ?array $ctx = null): string
    {
        $m = self::_metricas($s, $ctx);

        if (($s['nivel'] ?? '') === 'nuevo') {
            return 'Recopilando información — tu score se está activando. Sigue cotizando y dando seguimiento; en unos días el termómetro empieza a leerte.';
        }
        if ($m['asig'] === 0) {
            return 'No cotizaste en el período. Sin cotizaciones el sistema no tiene nada que medir — y tú no tienes de dónde sacar ventas. Manda propuestas hoy.';
        }

        $tier = self::_tier($m['score'], $m);
        $fuga = self::_detectar_fuga($m, $tier);
        $seed = ((int)($s['usuario_id'] ?? 0)) + (int)date('z');

        return self::_componer($m, $tier, $fuga, $seed);
    }

    // ── Métricas ────────────────────────────────────────────

    private static function _metricas(array $s, ?array $ctx): array
    {
        $asig    = (int)($s['cot_asignadas'] ?? 0);
        $vist    = (int)($s['cot_vistas'] ?? 0);
        $cierres = (int)($s['conversiones'] ?? 0);
        $bench   = (float)($ctx['close_rate'] ?? 0.10);
        $tasa    = $vist > 0 ? $cierres / $vist : 0.0;
        $cal     = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $fb      = (int)($s['fb_total'] ?? $s['radar_views'] ?? 0);
        $sdto    = (int)($s['cierres_sin_dto'] ?? $cierres);
        return [
            'score'      => (int)($s['score'] ?? 0),
            'asig'       => $asig,
            'vist'       => $vist,
            'cierres'    => $cierres,
            'sin_cerrar' => max(0, $vist - $cierres),
            'sin_abrir'  => max(0, $asig - $vist),
            'aper'       => $asig > 0 ? $vist / $asig : 0.0,   // tasa de apertura
            'dorm'       => (int)($s['cot_dormidas'] ?? 0),
            'nab'        => (int)($s['no_abiertas_5d'] ?? 0),
            'ign'        => max(0, $cal - $fb),                // calientes sin atender
            'vsp'        => (int)($s['ventas_sin_pago'] ?? 0),
            'mom'        => (float)($s['momentum'] ?? 1),
            'bench'      => $bench,
            'bench_pct'  => (int)round($bench * 100),
            'tasa'       => $tasa,
            'tasa_pct'   => (int)round($tasa * 100),
            'bticket'    => (int)($s['bonus_ticket'] ?? 0),
            'bcierre'    => (int)($s['bonus_cierre'] ?? 0),
            'con_dto'    => max(0, $cierres - $sdto),          // ventas con descuento
        ];
    }

    // ── Tier de tono/largo ──────────────────────────────────
    // 85+ top · 70-84 activo · 45-69 regular · <45 bajo
    private static function _tier(int $score, array $m): string
    {
        if ($score >= 85 || $m['bcierre'] >= 8) return 'top';
        if ($score >= 70) return 'activo';
        if ($score >= 45) return 'regular';
        return 'bajo';
    }

    // ── Detector de fuga #1 (triage de gerente) ─────────────
    private static function _detectar_fuga(array $m, string $tier): string
    {
        if ($tier === 'top') return 'top';

        // 1) No las abren: si la mayoría ni se abre, ese es el problema raíz.
        if ($m['asig'] >= 4 && $m['aper'] < 0.5) return 'no_abren';
        // 2) Vende bien pero no cobra: dinero en la calle.
        if ($m['cierres'] >= 2 && $m['vsp'] >= 2 && $m['vsp'] >= $m['cierres'] * 0.5) return 'cobro';
        // 3) Cierra bien pero poco volumen: sube tiros.
        if ($m['bench'] > 0 && $m['tasa'] >= $m['bench'] && $m['vist'] < 8 && $m['cierres'] >= 1) return 'volumen';
        // 4) Cotiza y le abren, pero no cierra (el clásico).
        if ($m['vist'] >= 8 && $m['bench'] > 0 && $m['tasa'] < $m['bench'] * 0.85) return 'cierre_bajo';
        // 5) Regala margen.
        if ($m['cierres'] >= 3 && $m['con_dto'] >= $m['cierres'] * 0.5) return 'margen';
        // 6) Venía mejor.
        if ($m['mom'] <= 0.75) return 'momentum';
        return 'cierre_bajo';
    }

    // ════════════════════════════════════════════════════════
    //  COMPOSICIÓN por tier
    // ════════════════════════════════════════════════════════

    private static function _componer(array $m, string $tier, string $fuga, int $seed): array|string
    {
        if ($fuga === 'top') return self::_render_top($m, $seed);

        $partes = [];

        // Prefijo de tono según tier (oración aparte; marca la dureza)
        $pre = self::_prefijo_tier($tier, $seed);
        $op  = self::_opener($fuga, $m, $seed);
        $partes[] = $pre !== '' ? ($pre . ' ' . $op) : $op;

        // Arma (positivo como palanca) — solo regular (el que más coaching necesita)
        if ($tier === 'regular' && $m['bticket'] >= 5) {
            $partes[] = self::_arma($m, $seed);
        }

        // Acciones: bajo/activo = 1, regular = hasta 2
        $acc = self::_acciones($fuga, $m, $seed);
        if ($tier !== 'regular') $acc = array_slice($acc, 0, 1);
        else                     $acc = array_slice($acc, 0, 2);
        if (!empty($acc)) $partes[] = implode(' ', $acc);

        // Cierre por tier (consecuencia)
        $partes[] = self::_cierre_tier($tier, $seed);

        return implode(' ', $partes);
    }

    /** minúscula inicial (para pegar tras un prefijo). */
    private static function _lc(string $s): string
    {
        return mb_strtolower(mb_substr($s, 0, 1)) . mb_substr($s, 1);
    }

    // Prefijo de tono por tier
    private static function _prefijo_tier(string $tier, int $seed): string
    {
        return match ($tier) {
            'bajo' => self::_pick([
                'Sin rodeos.', 'Te lo digo directo.', 'Esto es serio.', 'Aterriza esto.',
            ], $seed + 1),
            'activo' => self::_pick([
                'Vas bien — y por eso te exijo más.', 'Buen nivel; te falta un paso.', 'Casi en el top.',
            ], $seed + 1),
            default => '', // regular: sin prefijo, el opener ya carga el tono
        };
    }

    // ════════════════════════════════════════════════════════
    //  OPENERS por fuga (registro firme; el tier añade el matiz)
    // ════════════════════════════════════════════════════════

    private static function _opener(string $fuga, array $m, int $seed): string
    {
        $v = $m['vist']; $c = $m['cierres']; $bp = $m['bench_pct']; $tp = $m['tasa_pct'];
        $sc = $m['sin_cerrar']; $sa = $m['sin_abrir']; $asig = $m['asig'];

        $pool = match ($fuga) {
            'cierre_bajo' => [
                "{$c} de {$v}. Esa es tu tasa de cierre — {$tp}% contra el {$bp}% de la empresa. No te faltan clientes; te falta rematar.",
                "Cotizas de sobra y no cierras — ahí está tu problema. {$v} clientes vieron tu propuesta y solo " . ($c === 1 ? 'uno compró' : "{$c} compraron") . ". El trabajo es cerrar lo que ya mandaste, no mandar más.",
                "¿{$v} propuestas y " . ($c === 1 ? 'una sola venta' : "solo {$c} ventas") . "? No es suerte ni precio: es seguimiento. La empresa cierra {$bp}% y tú {$tp}%.",
                "{$sc} clientes abrieron tu cotización y se fueron sin comprar. Con tanto interés perdido, casi siempre es una de dos: no diste seguimiento, o no manejaste la objeción.",
                "Estás llenando el embudo pero se vacía antes de cerrar. " . self::_pl($c, 'venta', 'ventas') . " de {$v} vistas. El cuello está en el remate.",
            ],
            'no_abren' => [
                "Estás mandando cotizaciones al vacío: {$sa} de {$asig} ni se abrieron. El problema no es tu precio — es que no llegan o no las ven.",
                "{$sa} propuestas sin abrir. Una cotización que el cliente no ve es una hora de trabajo a la basura. Antes de cotizar más, asegúrate de que lleguen.",
                "Tu apertura está en el piso: la mayoría de tus cotizaciones no se abren. Todo lo demás da igual si el cliente ni las mira.",
            ],
            'volumen' => [
                "Cierras bien — tu tasa va pareja o arriba de la empresa. Tu problema es otro: hay muy pocos tiros. Con {$v} " . self::_pl($v, 'cotización vista', 'cotizaciones vistas') . " no alcanza.",
                "Sabes cerrar; lo que falta es volumen. Cotizaste poco este período y con pocas propuestas no hay de dónde sacar más ventas.",
                "Tu remate funciona. El límite es cuántas oportunidades generas: pocas cotizaciones = techo bajo, por bueno que seas cerrando.",
            ],
            'cobro' => [
                self::_pl($m['vsp'], 'venta cerrada sin un solo peso cobrado', 'ventas cerradas sin un solo peso cobrado') . ". Una venta sin cobrar no es venta, es un pendiente que te va a explotar.",
                "Vendes y no cobras: tienes " . self::_pl($m['vsp'], 'venta', 'ventas') . " sin pago. Cerrar sin cobrar es hacer la mitad del trabajo.",
                "Cierras, pero el dinero se queda en la calle: " . self::_pl($m['vsp'], 'venta', 'ventas') . " sin abono. Eso pone en riesgo el flujo de toda la empresa.",
            ],
            'margen' => [
                "Cierras, sí, pero comprando el sí con descuento — buena parte de tus ventas rebajadas. Eso no es vender, es ceder margen.",
                "Tu cierre depende demasiado del descuento. Cada punto que regalas sale de la empresa; el cliente paga por certeza, no por barato.",
                "Vendes bajando el precio. Funciona a corto plazo, pero te acostumbra a ti y al cliente a lo más fácil, no a lo más rentable.",
            ],
            'momentum' => [
                "Venías mejor. Estás cayendo respecto a ti mismo — el período pasado rendías más. Algo cambió en tu ritmo.",
                "Tu tendencia va a la baja. No es catástrofe, pero es una señal: lo que te funcionaba se está aflojando.",
                "Estás por debajo de tu propio nivel reciente. El mejor termómetro de un vendedor es contra sí mismo, y ese marcador bajó.",
            ],
            default => [
                "Tus números están por debajo de lo que la empresa necesita. Hay que moverlos.",
            ],
        };
        return self::_pick($pool, $seed);
    }

    // ── Arma (positivo como palanca) ────────────────────────
    private static function _arma(array $m, int $seed): string
    {
        return self::_pick([
            "Y ojo: cuando cierras, cierras grande — tu venta fue muy por encima del ticket promedio. O sea, sí sabes vender; lo que falta es seguimiento.",
            "No es que no sepas vender: tu último cierre fue de los grandes. El problema no es tu pitch, es que sueltas a los demás.",
            "Tienes con qué — cerraste una venta de ticket alto. Si le dieras seguimiento al resto como a esa, otra sería la historia.",
        ], $seed + 3);
    }

    // ── Acciones (data-driven, a QUIÉN atender) ─────────────
    private static function _acciones(string $fuga, array $m, int $seed): array
    {
        $acc = [];

        // Caliente ignorado — la más valiosa, aplica a cualquier fuga si hay
        if ($m['ign'] > 0) {
            $n = $m['ign'];
            $acc[] = self::_pick([
                $n === 1
                    ? "Arranca por lo caliente: 1 cliente está activo en el Radar ahora mismo — volvió y revisó el precio. No lo has tocado. Esa es tu venta de hoy: llámalo antes de comer."
                    : "Arranca por lo caliente: {$n} clientes están activos en el Radar ahora mismo — volvieron y revisaron el precio. No los has tocado. Esa es tu venta de hoy: llámalos antes de comer.",
                "El Radar te grita: " . self::_pl($n, 'cliente en modo compra', 'clientes en modo compra') . " sin atender. " . ($n === 1 ? 'Márcalo' : 'Márcalos') . " ya — es lo más cercano a una venta que tienes.",
                "Lo primero, sin excusas: " . self::_pl($n, 'oportunidad caliente', 'oportunidades calientes') . " esperándote en el Radar. Cada hora baja la probabilidad. Hoy, no mañana.",
            ], $seed + 5);
        }
        // Dormidas
        if ($m['dorm'] > 0) {
            $d = $m['dorm'];
            $acc[] = self::_pick([
                $d === 1
                    ? "Luego ve por el cliente que miró y desapareció: un mensaje hoy lo revive antes de que se enfríe."
                    : "Luego ve por los {$d} que miraron y desaparecieron: un mensaje hoy los revive antes de que se enfríen.",
                $d === 1
                    ? "Después, el cliente dormido: abrió y dejaste de existir para él. Reactívalo."
                    : "Después, los {$d} dormidos: abrieron y dejaste de existir para ellos. Reactívalos uno por uno.",
                $d === 1
                    ? "Y no dejes morir a ese cliente que vio tu cotización y lleva una semana sin saber de ti. Es dinero enfriándose."
                    : "Y no dejes morir a esos {$d} clientes que vieron tu cotización y llevan una semana sin saber de ti. Es dinero enfriándose sobre la mesa.",
            ], $seed + 7);
        }

        // Acciones específicas de la fuga cuando no hay calientes/dormidas que empujar
        if (empty($acc)) {
            $pool = match ($fuga) {
                'no_abren' => [
                    "Manda el link por WhatsApp, no por correo que se va a spam, y confirma que llegó. Si no la abren, no hay venta que perseguir.",
                    "Reenvía las que no se abrieron por otro canal y con un mensaje corto: 'te mandé tu cotización, ¿la viste?'. Primero que la abran.",
                ],
                'volumen' => [
                    "Sube el número de cotizaciones esta semana: con tu tasa de cierre, más tiros = más ventas, casi lineal. Prospecta y cotiza.",
                    "Tu palanca es volumen: agenda tiempo diario para prospectar y mandar propuestas. Cierras bien, solo necesitas más oportunidades.",
                ],
                'cobro' => [
                    "Antes de cerrar otra venta, ve por ese dinero: llama, agenda el pago, y no marques la venta como ganada hasta que entre el primer abono.",
                    "Prioriza el cobro hoy: una venta sin pago es un problema, no un logro. Cierra el ciclo con cada cliente que ya te dijo que sí.",
                ],
                'margen' => [
                    "Practica sostener el precio: pregunta qué frena al cliente antes de mover el número. El descuento es el último recurso, no el primero.",
                    "En tu próxima cotización, defiende el valor antes que el precio. Si tienes que dar algo, que sea plazo o extras, no margen.",
                ],
                'momentum' => [
                    "Retoma tu rutina esta semana: revisa el Radar a diario y dale seguimiento a lo que tiene movimiento. Recupera el ritmo antes de que se vuelva costumbre.",
                    "Vuelve a lo básico que te funcionaba: cotiza, sigue, cierra. Un par de días enfocado y el marcador se endereza.",
                ],
                default => [
                    "Entra al Radar, enfócate en lo que tiene actividad y ciérralo. Ahí están tus ventas.",
                ],
            };
            $acc[] = self::_pick($pool, $seed + 5);
        }

        return $acc;
    }

    // ── Cierre por tier (consecuencia) ──────────────────────
    private static function _cierre_tier(string $tier, int $seed): string
    {
        $pool = match ($tier) {
            'bajo' => [
                "Así como vas, esto no aguanta. Se necesita cambio esta semana, no el mes que entra.",
                "El marcador está en rojo y arrastra a todo el equipo. Muévelo ya.",
                "No hay margen para más semanas así. Enfócate y ejecuta hoy.",
            ],
            'activo' => [
                "Cierra esa brecha y el mes es tuyo — estás a un paso del top.",
                "Ajusta ese detalle y saltas de nivel. Ya casi.",
                "Con eso resuelto, pasas de bueno a intocable. Ve por ello.",
            ],
            default => [ // regular
                "Es 100% arreglable: es seguimiento y disciplina, no talento. Empieza hoy.",
                "Si cambias eso, en dos semanas se nota en el número. Manos a la obra.",
                "El material lo tienes; falta la constancia. Ordénate y sube.",
            ],
        };
        return self::_pick($pool, $seed + 11);
    }

    // ── Render TOP ──────────────────────────────────────────
    private static function _render_top(array $m, int $seed): string
    {
        $partes = [];
        $partes[] = self::_pick([
            "Vas volando. Cierras muy por encima de lo que esperaría la empresa de ti — eres de los que mueven el número.",
            "Mes de los buenos: tu cierre está claramente arriba del promedio. Así se ve quien domina su embudo.",
            "Nivel top. No solo vendes: vendes bien y consistente. Eso separa al profesional del que tiene suerte.",
            "Estás en tu mejor forma y jalas al equipo hacia arriba. Bien hecho.",
        ], $seed);

        if ($m['ign'] > 0) {
            $partes[] = self::_pick([
                "No te relajes: aún tienes " . self::_pl($m['ign'], 'cliente caliente', 'clientes calientes') . " en el Radar sin atender. Ciérralos y el mes es histórico.",
                "Un detalle para ser perfecto: " . self::_pl($m['ign'], 'oportunidad', 'oportunidades') . " activa" . ($m['ign'] === 1 ? '' : 's') . " esperándote. No dejes ese dinero en la mesa.",
            ], $seed + 4);
        } else {
            $partes[] = self::_pick([
                "El siguiente nivel: sostén el ritmo y comparte con el equipo qué haces distinto. Un líder multiplica, no solo produce.",
                "Vuelve repetible lo que te trajo aquí: documenta tu seguimiento para que sea tu estándar, no tu buen mes.",
                "Mantén la disciplina y ayuda a subir a los que van atrás — eso te hace referente.",
            ], $seed + 4);
        }
        return implode(' ', $partes);
    }
}
