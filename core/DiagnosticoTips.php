<?php
// ============================================================
//  core/DiagnosticoTips.php
//  Motor de "tips" del termómetro en voz de gerente comercial.
//
//  Diagnostica la FUGA #1 del embudo, dice a QUIÉN atender con datos
//  reales, da UNA acción, cierra con consecuencia. Segmentado por TIER
//  de score (tono + largo). Cubre las 5 dimensiones del score.
//
//  Consistencia: piezas vetadas (opener/arma/acción/tips/cierre) +
//  helper _pl() de plurales. Variedad multiplicativa: esqueleto × fuga
//  × pools × tier × datos vivos × rotación (usuario + día).
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
        if ($cal > 0 && $fb > $cal) $fb = $cal;
        $sdto    = (int)($s['cierres_sin_dto'] ?? $cierres);
        $exp     = (int)($s['calientes_exploradas'] ?? 0);
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
            'cal'        => $cal,                             // cotizaciones calientes
            'fb'         => $fb,                              // feedback dado a calientes
            'ign'        => max(0, $cal - $fb),               // calientes sin atender/feedback
            'exp'        => $exp,                             // calientes exploradas con ❓
            'vsp'        => (int)($s['ventas_sin_pago'] ?? 0),
            'mom'        => (float)($s['momentum'] ?? 1),
            'bench'      => $bench,
            'bench_pct'  => (int)round($bench * 100),
            'tasa'       => $tasa,
            'tasa_pct'   => (int)round($tasa * 100),
            'bticket'    => (int)($s['bonus_ticket'] ?? 0),
            'bcierre'    => (int)($s['bonus_cierre'] ?? 0),
            'con_dto'    => max(0, $cierres - $sdto),
            'h_up'       => (int)($s['health_up'] ?? $s['transiciones_up'] ?? 0),
            'h_down'     => (int)($s['health_down'] ?? $s['senales_ignoradas'] ?? 0),
            'tips_s'     => (float)($s['tips_score'] ?? 1),
            'dias_act'   => (int)($s['dias_activos'] ?? 0),
        ];
    }

    // ── Tier ────────────────────────────────────────────────
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

        // Muestra chica: pocas cotizaciones = no hay qué juzgar con dureza.
        if ($m['asig'] < 8 && $m['cierres'] === 0 && $m['nab'] === 0) return 'muestra_chica';

        // 1) No las abren: raíz del embudo.
        if ($m['asig'] >= 4 && $m['aper'] < 0.5) return 'no_abren';
        // 2) Sentado sobre calientes: la venta más fácil sin atender (= Seguimiento).
        if ($m['ign'] >= 3) return 'caliente';
        // 3) Deja morir el pipeline: clientes con interés que se enfriaron.
        if ($m['h_up'] >= 3 && $m['h_down'] >= $m['h_up'] * 0.5) return 'radar_health';
        // 4) Vende y no cobra.
        if ($m['cierres'] >= 2 && $m['vsp'] >= 2 && $m['vsp'] >= $m['cierres'] * 0.5) return 'cobro';
        // 5) Abre y abandona: cierra decente pero deja muchos dormidos.
        if ($m['dorm'] >= 3 && $m['bench'] > 0 && $m['tasa'] >= $m['bench'] * 0.85) return 'dormidas';
        // 6) Cotiza y le abren, pero no cierra.
        if ($m['vist'] >= 8 && $m['bench'] > 0 && $m['tasa'] < $m['bench'] * 0.85) return 'cierre_bajo';
        // 7) Cierra bien pero poco volumen.
        if ($m['bench'] > 0 && $m['tasa'] >= $m['bench'] && $m['vist'] < 8 && $m['cierres'] >= 1) return 'volumen';
        // 8) Regala margen.
        if ($m['cierres'] >= 3 && $m['con_dto'] >= $m['cierres'] * 0.5) return 'margen';
        // 9) Venía mejor.
        if ($m['mom'] <= 0.75) return 'momentum';
        // 10) Estable.
        return 'neutro';
    }

    // ════════════════════════════════════════════════════════
    //  COMPOSICIÓN
    // ════════════════════════════════════════════════════════

    private static function _componer(array $m, string $tier, string $fuga, int $seed): string
    {
        if ($fuga === 'top')          return self::_render_top($m, $seed);
        if ($fuga === 'muestra_chica') return self::_render_muestra_chica($m, $seed);

        $partes = [];

        // Prefijo de tono por tier (oración aparte).
        $pre = self::_prefijo_tier($tier, $seed);
        $op  = self::_opener($fuga, $m, $seed);
        $partes[] = $pre !== '' ? ($pre . ' ' . $op) : $op;

        // Arma (positivo como palanca): en regular y activo, si hay mérito.
        if ($tier === 'regular' || $tier === 'activo') {
            $arma = self::_arma($m, $seed);
            if ($arma !== '') $partes[] = $arma;
        }

        // Acciones (a quién atender). bajo/activo = 1, regular = hasta 2.
        $acc = self::_acciones($fuga, $m, $seed);
        $acc = array_slice($acc, 0, $tier === 'regular' ? 2 : 1);
        if (!empty($acc)) $partes[] = implode(' ', $acc);

        // Nag de herramientas (tips/❓): si no las usa, en regular/bajo (los que necesitan empujón).
        if ($tier === 'regular' || $tier === 'bajo') {
            $nag = self::_tips_nag($m, $seed);
            if ($nag !== '') $partes[] = $nag;
        }

        // Cierre por tier.
        $partes[] = self::_cierre_tier($tier, $seed);

        return implode(' ', $partes);
    }

    // ── Prefijo por tier ────────────────────────────────────
    private static function _prefijo_tier(string $tier, int $seed): string
    {
        return match ($tier) {
            'bajo' => self::_pick([
                'Sin rodeos.', 'Te lo digo directo.', 'Esto es serio.', 'Aterriza esto.', 'Escúchame bien.',
            ], $seed + 1),
            'activo' => self::_pick([
                'Vas bien — y por eso te exijo más.', 'Buen nivel; te falta un paso.', 'Casi en el top.',
                'Estás jugando en serio, pero falta un detalle.', 'Vas de subida.',
            ], $seed + 1),
            default => '',
        };
    }

    // ════════════════════════════════════════════════════════
    //  OPENERS por fuga
    // ════════════════════════════════════════════════════════

    private static function _opener(string $fuga, array $m, int $seed): string
    {
        $v = $m['vist']; $c = $m['cierres']; $bp = $m['bench_pct']; $tp = $m['tasa_pct'];
        $sc = $m['sin_cerrar']; $sa = $m['sin_abrir']; $asig = $m['asig'];
        $ign = $m['ign']; $dorm = $m['dorm']; $hd = $m['h_down']; $hu = $m['h_up'];

        $pool = match ($fuga) {
            'cierre_bajo' => [
                "{$c} de {$v}. Esa es tu tasa de cierre — {$tp}% contra el {$bp}% de la empresa. No te faltan clientes; te falta rematar.",
                "Cotizas de sobra y no cierras — ahí está tu problema. {$v} clientes vieron tu propuesta y solo " . ($c === 1 ? 'uno compró' : "{$c} compraron") . ". El trabajo es cerrar lo que ya mandaste, no mandar más.",
                "¿{$v} propuestas y " . ($c === 1 ? 'una sola venta' : "solo {$c} ventas") . "? No es suerte ni precio: es seguimiento. La empresa cierra {$bp}% y tú {$tp}%.",
                "{$sc} clientes abrieron tu cotización y se fueron sin comprar. Con tanto interés perdido, casi siempre es una de dos: no diste seguimiento, o no manejaste la objeción.",
                "Estás llenando el embudo pero se vacía antes de cerrar. " . self::_pl($c, 'venta', 'ventas') . " de {$v} vistas. El cuello está en el remate.",
                "Tienes tráfico de sobra y no lo conviertes: {$tp}% de cierre cuando la empresa hace {$bp}%. El problema no es que te falten clientes, es qué haces con los que ya tienes.",
            ],
            'caliente' => [
                "Estás sentado sobre oro y no lo ves: " . self::_pl($ign, 'cliente caliente sin atender', 'clientes calientes sin atender') . " en el Radar. Es la venta más fácil que tienes y la estás dejando pasar.",
                "El Radar te marcó " . self::_pl($ign, 'oportunidad lista para cerrar', 'oportunidades listas para cerrar') . " y no le diste seguimiento a " . ($ign === 1 ? 'ninguna' : 'ninguna') . ". Eso es dinero tocando la puerta.",
                "Tu mayor fuga hoy no es cotizar ni cerrar — es que " . self::_pl($ign, 'cliente en punto de compra sigue', 'clientes en punto de compra siguen') . " sin que " . ($ign === 1 ? 'lo atiendas' : 'los atiendas') . ". El Radar hizo su trabajo; falta el tuyo.",
                "Tienes " . self::_pl($ign, 'lead caliente', 'leads calientes') . " ardiendo en el Radar y sin tocar. No hay nada más rentable que hacer hoy que atender eso.",
                "El sistema te está entregando " . self::_pl($ign, 'venta casi hecha', 'ventas casi hechas') . " y no la trabajas. Ese es tu punto flaco: el seguimiento a lo caliente.",
            ],
            'radar_health' => [
                "Estás dejando morir tu pipeline: de " . self::_pl($hu, 'cliente que se calentó', 'clientes que se calentaron') . ", " . ($hd === 1 ? 'uno se te enfrió' : "{$hd} se te enfriaron") . ". Tenías el interés y lo dejaste escapar.",
                "Tu Radar se está apagando. " . self::_pl($hd, 'cliente con interés real', 'clientes con interés real') . " se " . ($hd === 1 ? 'enfrió' : 'enfriaron') . " porque no " . ($hd === 1 ? 'llegaste a tiempo' : 'llegaste a tiempo') . ". El interés no espera.",
                "La mitad de tus clientes calientes se enfriaron sin cerrar. No es que no te lleguen oportunidades — es que las dejas envejecer hasta que se pierden.",
                "Tenías " . self::_pl($hu, 'cliente subiendo de temperatura', 'clientes subiendo de temperatura') . " y dejaste caer a " . ($hd === 1 ? 'uno' : "{$hd}") . ". Cada cliente que se enfría es una venta que le regalas al tiempo.",
            ],
            'dormidas' => [
                "Sabes cerrar, pero abres y abandonas: " . self::_pl($dorm, 'cliente miró tu cotización y lleva días sin que lo toques', 'clientes miraron tu cotización y llevan días sin que los toques') . ". Ahí tienes ventas dormidas.",
                "Tu problema no es cerrar — es la constancia. Dejaste " . self::_pl($dorm, 'cliente interesado sin seguimiento', 'clientes interesados sin seguimiento') . ". El que da seguimiento cierra; el que manda y espera, no.",
                "Tienes " . self::_pl($dorm, 'cliente dormido', 'clientes dormidos') . ": abrieron, se interesaron, y desapareciste. Reactivarlos cuesta un mensaje y vale una venta.",
                "Cierras bien cuando das seguimiento — el tema es que a muchos no se los das. " . self::_pl($dorm, 'cliente lleva 7+ días', 'clientes llevan 7+ días') . " enfriándose sin una llamada tuya.",
            ],
            'no_abren' => [
                "Estás mandando cotizaciones al vacío: {$sa} de {$asig} ni se abrieron. El problema no es tu precio — es que no llegan o no las ven.",
                "{$sa} propuestas sin abrir. Una cotización que el cliente no ve es una hora de trabajo a la basura. Antes de cotizar más, asegúrate de que lleguen.",
                "Tu apertura está en el piso: la mayoría de tus cotizaciones no se abren. Todo lo demás da igual si el cliente ni las mira.",
                "De {$asig} cotizaciones, solo {$v} se abrieron. La venta empieza cuando el cliente ve la propuesta — y tú te estás quedando en la puerta.",
                "El embudo se te rompe en el primer escalón: {$sa} sin abrir. No es problema de cierre, es de que tu cotización no está llegando a los ojos del cliente.",
            ],
            'volumen' => [
                "Cierras bien — tu tasa va pareja o arriba de la empresa. Tu problema es otro: hay muy pocos tiros. Con {$v} " . self::_pl($v, 'cotización vista', 'cotizaciones vistas') . " no alcanza.",
                "Sabes cerrar; lo que falta es volumen. Cotizaste poco este período y con pocas propuestas no hay de dónde sacar más ventas.",
                "Tu remate funciona. El límite es cuántas oportunidades generas: pocas cotizaciones = techo bajo, por bueno que seas cerrando.",
                "Tienes buena puntería pero disparas poco. Sube el número de cotizaciones y, con tu tasa, las ventas salen casi solas.",
                "No es un problema de calidad, es de cantidad: cierras lo que trabajas, solo que trabajas pocas. Más propuestas = más ventas.",
            ],
            'cobro' => [
                self::_pl($m['vsp'], 'venta cerrada sin un solo peso cobrado', 'ventas cerradas sin un solo peso cobrado') . ". Una venta sin cobrar no es venta, es un pendiente que te va a explotar.",
                "Vendes y no cobras: tienes " . self::_pl($m['vsp'], 'venta', 'ventas') . " sin pago. Cerrar sin cobrar es hacer la mitad del trabajo.",
                "Cierras, pero el dinero se queda en la calle: " . self::_pl($m['vsp'], 'venta', 'ventas') . " sin abono. Eso pone en riesgo el flujo de toda la empresa.",
                "El cierre lo tienes; el cobro no. " . self::_pl($m['vsp'], 'venta firmada y sin un peso adentro', 'ventas firmadas y sin un peso adentro') . ". Una venta se completa cuando entra el dinero.",
            ],
            'margen' => [
                "Cierras, sí, pero comprando el sí con descuento — buena parte de tus ventas rebajadas. Eso no es vender, es ceder margen.",
                "Tu cierre depende demasiado del descuento. Cada punto que regalas sale de la empresa; el cliente paga por certeza, no por barato.",
                "Vendes bajando el precio. Funciona a corto plazo, pero te acostumbra a ti y al cliente a lo más fácil, no a lo más rentable.",
                "Estás cerrando a base de rebaja. El número de ventas se ve bien, pero el margen que dejas cuenta otra historia.",
            ],
            'momentum' => [
                "Venías mejor. Estás cayendo respecto a ti mismo — el período pasado rendías más. Algo cambió en tu ritmo.",
                "Tu tendencia va a la baja. No es catástrofe, pero es una señal: lo que te funcionaba se está aflojando.",
                "Estás por debajo de tu propio nivel reciente. El mejor termómetro de un vendedor es contra sí mismo, y ese marcador bajó.",
                "Perdiste algo de impulso. No es el fin del mundo, pero si no lo frenas ahora, la inercia juega en tu contra.",
            ],
            'neutro' => [
                "Vas estable — ni fuga grande ni mes de récord. El siguiente nivel no llega solo: hay que ir por él.",
                "Estás en zona pareja. Ni mal ni brillante. Para despegar necesitas apretar una tuerca, no cambiar todo.",
                "Tus números están en su lugar, sin alarmas. Ahora la pregunta es: ¿te conformas con estable o vas por más?",
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
        // Prioridad: cierre sobre histórico > ticket alto > apertura impecable.
        if ($m['bcierre'] >= 4) {
            return self::_pick([
                "Y hay que decirlo: cierras claramente por encima de tu propio histórico. Cuando te enfocas, cierras. Solo falta sostenerlo.",
                "Ojo con lo bueno: tu tasa de cierre va arriba de lo que venías haciendo. Tienes el nivel; el reto es la constancia.",
            ], $seed + 3);
        }
        if ($m['bticket'] >= 5) {
            return self::_pick([
                "Y ojo: cuando cierras, cierras grande — tu venta fue muy por encima del ticket promedio. Sí sabes vender; lo que falta es seguimiento.",
                "No es que no sepas vender: tu último cierre fue de los grandes. El problema no es tu pitch, es que sueltas a los demás.",
                "Tienes con qué — cerraste una venta de ticket alto. Si le dieras seguimiento al resto como a esa, otra sería la historia.",
            ], $seed + 3);
        }
        if ($m['aper'] >= 0.9 && $m['vist'] >= 5) {
            return self::_pick([
                "A tu favor: tu apertura es impecable, prácticamente todas se abren. Lo de mandar bien lo dominas; el juego se decide más adelante en el embudo.",
                "Algo bien claro: llegas — casi todas tus cotizaciones se abren. El problema no es el arranque, es lo que sigue.",
            ], $seed + 3);
        }
        return '';
    }

    // ── Acciones (a quién atender) ──────────────────────────
    private static function _acciones(string $fuga, array $m, int $seed): array
    {
        $acc = [];

        if ($m['ign'] > 0) {
            $n = $m['ign'];
            $acc[] = self::_pick([
                $n === 1
                    ? "Arranca por lo caliente: 1 cliente está activo en el Radar ahora mismo — volvió y revisó el precio. No lo has tocado. Esa es tu venta de hoy: llámalo antes de comer."
                    : "Arranca por lo caliente: {$n} clientes están activos en el Radar ahora mismo — volvieron y revisaron el precio. No los has tocado. Esa es tu venta de hoy: llámalos antes de comer.",
                "El Radar te grita: " . self::_pl($n, 'cliente en modo compra', 'clientes en modo compra') . " sin atender. " . ($n === 1 ? 'Márcalo' : 'Márcalos') . " ya — es lo más cercano a una venta que tienes.",
                "Lo primero, sin excusas: " . self::_pl($n, 'oportunidad caliente', 'oportunidades calientes') . " esperándote en el Radar. Cada hora baja la probabilidad. Hoy, no mañana.",
                "Y no olvides marcar con 👍/👎 en el Radar lo que pase con " . ($n === 1 ? 'ese cliente' : 'esos clientes') . ": ese feedback afina tu puntería y sube tu seguimiento.",
            ], $seed + 5);
        }
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

        if (empty($acc)) {
            $pool = match ($fuga) {
                'no_abren' => [
                    "Manda el link por WhatsApp, no por correo que se va a spam, y confirma que llegó. Si no la abren, no hay venta que perseguir.",
                    "Reenvía las que no se abrieron por otro canal y con un mensaje corto: 'te mandé tu cotización, ¿la viste?'. Primero que la abran.",
                    "Levanta el teléfono con los que no abrieron: a veces un 'te acabo de mandar la propuesta' es todo lo que hace falta para que la vean.",
                ],
                'volumen' => [
                    "Sube el número de cotizaciones esta semana: con tu tasa de cierre, más tiros = más ventas, casi lineal. Prospecta y cotiza.",
                    "Tu palanca es volumen: agenda tiempo diario para prospectar y mandar propuestas. Cierras bien, solo necesitas más oportunidades.",
                    "Ponte una meta diaria de cotizaciones nuevas. Con lo bien que cierras, cada propuesta extra es dinero casi seguro.",
                ],
                'cobro' => [
                    "Antes de cerrar otra venta, ve por ese dinero: llama, agenda el pago, y no marques la venta como ganada hasta que entre el primer abono.",
                    "Prioriza el cobro hoy: una venta sin pago es un problema, no un logro. Cierra el ciclo con cada cliente que ya te dijo que sí.",
                    "Haz una lista de tus ventas sin cobrar y trabájalas como si fueran cierres nuevos — porque hasta que no entre el dinero, no lo son.",
                ],
                'margen' => [
                    "Practica sostener el precio: pregunta qué frena al cliente antes de mover el número. El descuento es el último recurso, no el primero.",
                    "En tu próxima cotización, defiende el valor antes que el precio. Si tienes que dar algo, que sea plazo o extras, no margen.",
                    "Cambia el reflejo de bajar precio por el de agregar valor: garantía, tiempos, servicio. Cierras igual y no regalas margen.",
                ],
                'momentum' => [
                    "Retoma tu rutina esta semana: revisa el Radar a diario y dale seguimiento a lo que tiene movimiento. Recupera el ritmo antes de que se vuelva costumbre.",
                    "Vuelve a lo básico que te funcionaba: cotiza, sigue, cierra. Un par de días enfocado y el marcador se endereza.",
                    "Elige un solo día para reordenarte: revisa qué dejaste caer y retómalo. El impulso se recupera actuando, no esperando.",
                ],
                'radar_health' => [
                    "Entra al Radar y reactiva a los que iban subiendo y se enfriaron: un mensaje a tiempo los vuelve a poner en juego.",
                    "Tu tarea es no dejar envejecer lo caliente: revisa el Radar a diario y atiende lo que sube de temperatura el mismo día.",
                ],
                'neutro' => [
                    "Elige una palanca esta semana: o subes volumen de cotizaciones, o aprietas el seguimiento a lo que tiene movimiento. Una sola, bien hecha.",
                    "Para pasar de estable a bueno: entra al Radar todos los días y no dejes ni un cliente caliente sin atender. Ahí está tu siguiente escalón.",
                ],
                default => [
                    "Entra al Radar, enfócate en lo que tiene actividad y ciérralo. Ahí están tus ventas.",
                ],
            };
            $acc[] = self::_pick($pool, $seed + 5);
        }

        return $acc;
    }

    // ── Nag de herramientas (tips + ❓) ─────────────────────
    private static function _tips_nag(array $m, int $seed): string
    {
        if ($m['dias_act'] <= 0) return '';
        $no_tips = $m['tips_s'] <= 0.0;
        $medio_tips = $m['tips_s'] > 0.0 && $m['tips_s'] < 1.0;
        // ❓: exploró pocas de sus calientes
        $no_explora = $m['cal'] > 0 && ($m['exp'] / max(1, $m['cal'])) < 0.30;

        if ($no_tips && $no_explora) {
            return self::_pick([
                "Y algo básico: no lees los tips ni abres el ❓ de tus cotizaciones calientes. Ahí está la mitad de la información para cerrar — úsala.",
                "Ojo con las herramientas: ni los tips ni el ❓ del Radar estás usando. Te estás peleando a ciegas con datos que ya tienes a la mano.",
            ], $seed + 9);
        }
        if ($no_tips) {
            return self::_pick([
                "Y no estás leyendo los tips — revisa el análisis completo, ahí está el detalle de qué mover.",
                "Empieza por leer esto a fondo: no estás abriendo los tips, y ahí te digo exactamente dónde está la fuga.",
            ], $seed + 9);
        }
        if ($no_explora) {
            return self::_pick([
                "Usa el ❓ en tus cotizaciones calientes: te dice por qué el Radar las marcó y qué necesita ese cliente para decidir.",
                "Abre el ❓ de tus calientes antes de llamar — ahí ves qué señal disparó cada una y llegas con ventaja.",
            ], $seed + 9);
        }
        return '';
    }

    // ── Cierre por tier ─────────────────────────────────────
    private static function _cierre_tier(string $tier, int $seed): string
    {
        $pool = match ($tier) {
            'bajo' => [
                "Así como vas, esto no aguanta. Se necesita cambio esta semana, no el mes que entra.",
                "El marcador está en rojo y arrastra a todo el equipo. Muévelo ya.",
                "No hay margen para más semanas así. Enfócate y ejecuta hoy.",
                "Esto no es para pensarlo, es para actuar hoy. Los números tienen que reaccionar ya.",
                "Cada día así cuesta. Endereza esto ahora, no cuando sea tarde.",
            ],
            'activo' => [
                "Cierra esa brecha y el mes es tuyo — estás a un paso del top.",
                "Ajusta ese detalle y saltas de nivel. Ya casi.",
                "Con eso resuelto, pasas de bueno a intocable. Ve por ello.",
                "Te falta poquito para despegar. Aprieta ahí y lo logras.",
                "Un empujón más en eso y estás arriba. Tú puedes con esto.",
            ],
            default => [ // regular
                "Es 100% arreglable: es seguimiento y disciplina, no talento. Empieza hoy.",
                "Si cambias eso, en dos semanas se nota en el número. Manos a la obra.",
                "El material lo tienes; falta la constancia. Ordénate y sube.",
                "No necesitas reinventarte, solo enfocar. Elige una cosa y hazla bien esta semana.",
                "La diferencia entre este mes y uno bueno es puro seguimiento. Está en tus manos.",
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
            "Rendimiento de primera. Los números hablan por ti este período.",
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

    // ── Render MUESTRA CHICA (pocas cotizaciones) ───────────
    private static function _render_muestra_chica(array $m, int $seed): string
    {
        $a = $m['asig'];
        $op = self::_pick([
            "Apenas " . self::_pl($a, 'cotización en el período', 'cotizaciones en el período') . " — muy poco para juzgar tu cierre. Lo primero es volumen: manda más propuestas.",
            "Con " . self::_pl($a, 'sola cotización', 'cotizaciones') . " el termómetro casi no tiene qué leer. Sube el número de propuestas y los números empiezan a hablar.",
            "Vas arrancando: " . self::_pl($a, 'cotización', 'cotizaciones') . " es muestra chica. Enfócate en generar volumen; el cierre se evalúa cuando haya con qué.",
        ], $seed);
        $acc = '';
        if ($m['ign'] > 0) {
            $acc = ' Y si ya tienes ' . self::_pl($m['ign'], 'cliente caliente', 'clientes calientes') . ' en el Radar, atiéndel' . ($m['ign'] === 1 ? 'o' : 'os') . ' hoy — no dejes ir lo poco que tienes activo.';
        }
        return $op . $acc;
    }
}
