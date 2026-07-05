<?php
// ============================================================
//  core/DiagnosticoTips.php
//  Tip del termómetro por PERFIL PSICOLÓGICO del vendedor.
//  Implementa docs/termometro_motor_perfiles.md.
//
//  Pipeline:
//    _metricas  → normaliza TODA la fila (segmentos, activadores,
//                 boosters, EMAs, momentum)
//    _estados   → cada segmento a bajo/medio/alto
//    _validar   → Goodhart: marca cada ALTO como real o HUECO
//    _boosters  → mérito validado por dinero + prueba de capacidad
//    _voluntad  → índice 0–100 (se infiere, no se declara)
//    _cuadrante → A×C + índice → TONO (sacudida/reenganche/metodo/autonomia)
//    _arquetipo → perfil por el vector completo de 5
//    _componer  → reconocimiento + diagnóstico + jugada(tono) + activador
//
//  Reglas duras: (1) el resultado valida la actividad — no elogiar lo hueco;
//  (2) marcar es un clic, el número real deja rastro en el cliente;
//  (3) voluntad ≠ habilidad — script inútil para voluntad, sacudida para ella.
//  Nunca asumir contacto externo (modo receta futura). Cero mención de barras.
//  PURO: no toca BD. Recibe $s (row usuario_score) y $ctx.
// ============================================================

defined('COTIZAAPP') or die;

final class DiagnosticoTips
{
    private const BAJO = 0.40;   // < bajo
    private const ALTO = 0.63;   // ≥ alto  (entre ambos = medio)

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
            return 'No cotizaste en el período. Sin propuestas no hay de dónde salir ventas. Manda cotizaciones hoy — el cliente evalúa cuando pide precio, no la semana que entra.';
        }

        $seed = ((int)($s['usuario_id'] ?? 0)) + (int)date('z');
        $e    = self::_estados($m);
        $real = self::_validar($m, $e);
        $boot = self::_boosters($m);
        $vol  = self::_voluntad($m, $e, $real, $boot);
        $tono = self::_cuadrante($m, $e, $real, $vol);
        $arq  = self::_arquetipo($m, $e, $real);

        return self::_componer($arq, $tono, $m, $e, $real, $boot, $seed);
    }

    // ── Normaliza TODA la fila ───────────────────────────────
    private static function _metricas(array $s, ?array $ctx): array
    {
        $asig    = (int)($s['cot_asignadas'] ?? 0);
        $vist    = (int)($s['cot_vistas'] ?? 0);
        $cierres = (int)($s['conversiones'] ?? 0);
        $bench   = (float)($ctx['close_rate'] ?? 0.10);
        $tasa    = $vist > 0 ? $cierres / $vist : 0.0;
        $cal     = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        return [
            'score'      => (int)($s['score'] ?? 0),
            'nivel'      => (string)($s['nivel'] ?? ''),
            'asig'       => $asig,
            'vist'       => $vist,
            'cierres'    => $cierres,
            'aper'       => $asig > 0 ? $vist / $asig : 0.0,
            'aper_pct'   => (int)round(($asig > 0 ? $vist / $asig : 0.0) * 100),
            'dorm'       => (int)($s['cot_dormidas'] ?? 0),
            'nab'        => (int)($s['no_abiertas_5d'] ?? 0),
            'cal'        => $cal,
            'exp'        => (int)($s['calientes_exploradas'] ?? 0),
            'why'        => (float)($s['radar_why_score'] ?? 1.0),
            // Radar Health: transiciones_up=calientes, senales_ignoradas=muertas
            'h_up'       => (int)($s['health_up'] ?? $s['transiciones_up'] ?? 0),
            'h_down'     => (int)($s['health_down'] ?? $s['senales_ignoradas'] ?? 0),
            // Engagement (penalizaciones directas + volumen)
            'eps'        => (float)($s['eng_pen_sin_pago'] ?? 0),
            'epd'        => (float)($s['eng_pen_descuento'] ?? 0),
            'epb'        => (float)($s['eng_pen_bajo_benchmark'] ?? 0),
            'vsp'        => (int)($s['ventas_sin_pago'] ?? 0),
            'vper'       => (int)($s['ventas_periodo'] ?? 0),
            'bventas'    => (float)($s['bench_ventas'] ?? 0),
            // Activación tools
            'tips_s'     => (float)($s['tips_score'] ?? 1),
            'dias_lec'   => (int)($s['dias_lectura'] ?? 0),
            'dias_act'   => (int)($s['dias_activos'] ?? 0),
            // Boosters (mérito validado por dinero)
            'bticket'    => (int)($s['bonus_ticket'] ?? 0),
            'bticket_v'  => (int)($s['bonus_ticket_ventas'] ?? 0),
            'bcierre'    => (int)($s['bonus_cierre'] ?? 0),
            'ticket'     => (float)($s['ticket_promedio'] ?? 0),
            // Tendencia
            'mom'        => (float)($s['momentum'] ?? 1),
            'bench'      => $bench,
            'bench_pct'  => (int)round($bench * 100),
            'tasa'       => $tasa,
            'tasa_pct'   => (int)round($tasa * 100),
            // 5 segmentos
            's_act'      => (float)($s['s_activacion']  ?? 0.5),
            's_eng'      => (float)($s['s_engagement']  ?? 0.5),
            's_seg'      => (float)($s['s_seguimiento'] ?? 0.5),
            's_hlt'      => (float)($s['s_radar_health']?? 0.5),
            's_conv'     => (float)($s['s_conversion']  ?? 0.5),
        ];
    }

    // ── Cada segmento a bajo / medio / alto ──────────────────
    private static function _estados(array $m): array
    {
        $f = function (float $v): string {
            if ($v < self::BAJO) return 'bajo';
            if ($v >= self::ALTO) return 'alto';
            return 'medio';
        };
        return [
            'act'  => $f($m['s_act']),  'eng' => $f($m['s_eng']),
            'seg'  => $f($m['s_seg']),  'hlt' => $f($m['s_hlt']),
            'conv' => $f($m['s_conv']),
        ];
    }

    // ── Goodhart: ¿qué ALTO es real y qué es hueco? ──────────
    private static function _validar(array $m, array $e): array
    {
        $real = ['act' => true, 'eng' => true, 'seg' => true, 'hlt' => true, 'conv' => true];
        // Seguimiento alto + no cierra: ¿es trabajo REAL o teatro de clics?
        // Diligente (lee la guía + no deja enfriar) → real, es falta de técnica (rematador).
        // No diligente → hueco, es teatro/renuencia al cierre (voluntad).
        $diligente = $m['tips_s'] >= 0.7 && ($m['dorm'] / max($m['vist'], 1)) < 0.20;
        if ($e['seg'] === 'alto' && $m['s_conv'] < self::BAJO && !$diligente) $real['seg'] = false;
        // Apertura alta pero se le duermen = vanidad
        if ($e['act'] === 'alto' && ($m['dorm'] / max($m['vist'], 1)) >= 0.25) $real['act'] = false;
        // Engagement "alto" pero con ventas sucias / bajo volumen
        if ($e['eng'] === 'alto' && ($m['vsp'] > 0 || $m['epd'] > 0.03 || ($m['bventas'] > 0 && $m['vper'] < $m['bventas']))) $real['eng'] = false;
        // Radar Health alto con pocas calientes = espejismo
        if ($e['hlt'] === 'alto' && $m['cal'] < 5) $real['hlt'] = false;
        return $real;
    }

    // ── Boosters: mérito validado por dinero + capacidad ─────
    private static function _boosters(array $m): array
    {
        $capaz  = ($m['bcierre'] >= 4) || ($m['bticket'] >= 5);
        $merito = '';
        if ($m['bcierre'] >= 8) {
            $merito = 'tu tasa de cierre viene muy por encima de tu propio histórico — sabes hacerlo';
        } elseif ($m['bcierre'] >= 4) {
            $merito = 'vienes cerrando por arriba de tu histórico reciente';
        } elseif ($m['bticket'] >= 5) {
            $merito = ($m['bticket_v'] === 1 ? 'cerraste una venta grande' : "cerraste {$m['bticket_v']} ventas grandes")
                    . ' — cuando te lo propones cierras en grande';
        }
        return ['capaz' => $capaz, 'merito' => $merito];
    }

    // ── Índice de voluntad (0–100), inferido ─────────────────
    private static function _voluntad(array $m, array $e, array $real, array $boot): int
    {
        $v = 50.0;
        $v += ($m['tips_s'] - 0.5) * 40.0;                    // leer la guía = voluntad
        $v -= ($m['dorm'] / max($m['vist'], 1)) * 30.0;       // dejar enfriar = evitación
        if ($real['seg'] === false) $v -= 20.0;               // teatro (S hueco) = will solo para lo cómodo
        $v += ($m['mom'] - 1.0) * 30.0;                       // tendencia
        if ($boot['capaz']) $v += 15.0;                       // demostró que puede
        return (int)max(0, min(100, round($v)));
    }

    // ── A×C fija el tono; el índice lo gradúa ────────────────
    private static function _cuadrante(array $m, array $e, array $real, int $vol): string
    {
        // Teatro de actividad: seguimiento hueco + no cierra → sacudida al cierre
        if ($real['seg'] === false && $m['s_conv'] < self::BAJO) return 'sacudida_cierre';

        $aBaja = $e['act'] === 'bajo';
        $cAlta = $e['conv'] === 'alto';
        $cBaja = $e['conv'] === 'bajo';

        if (!$aBaja && $cBaja) return 'metodo';
        if ($aBaja && $cBaja)  return $vol < 35 ? 'sacudida' : 'metodo';
        if ($aBaja && $cAlta)  return 'reenganche';
        if (!$aBaja && $cAlta) return 'autonomia';
        return 'afinar';
    }

    // ── Perfil por el vector COMPLETO de 5 ───────────────────
    private static function _arquetipo(array $m, array $e, array $real): string
    {
        if ($m['asig'] < 6 && $m['cierres'] === 0) return 'muestra_chica';

        $bajos = 0;
        foreach ($e as $st) if ($st === 'bajo') $bajos++;
        if ($bajos >= 4) return 'desconectado';

        // Teatro: seguimiento se veía alto pero es hueco y no cierra
        if ($real['seg'] === false && $m['s_conv'] < self::BAJO && $e['seg'] === 'alto') return 'teatro';

        $aBaja = $e['act'] === 'bajo';
        $cAlta = $e['conv'] === 'alto';
        $cBaja = $e['conv'] === 'bajo';
        $segAlto = $e['seg'] === 'alto' && $real['seg'];
        $hltAlto = $e['hlt'] === 'alto' && $real['hlt'];
        $hltBajo = $e['hlt'] === 'bajo';

        // A↓ · C↓
        if ($aBaja && $cBaja) return 'presente_pasivo';

        // A↑/~ · C↓  (falta técnica de cierre — método)
        if ($cBaja) {
            if ($segAlto && $hltAlto) return 'cultivador';       // pipeline lleno no cosecha
            if ($segAlto)             return 'rematador_ausente'; // hace todo, no remata
            return 'sembrador';                                  // genera, no capitaliza
        }

        // A↓ · C↑  (re-enganche)
        if ($aBaja && $cAlta) {
            if ($e['seg'] === 'bajo' && $hltBajo) return 'cerrador_solitario';
            return 'francotirador';
        }

        // A↑ · C↑  (autonomía)
        if ($cAlta) {
            if ($hltBajo && $e['seg'] === 'bajo') return 'una_pierna';
            if ($hltBajo)                          return 'cerrador_desperdiciado';
            return 'motor_completo';
        }

        return 'meseta';
    }

    // ════════════════════════════════════════════════════════
    //  COMPOSICIÓN
    // ════════════════════════════════════════════════════════
    private static function _componer(string $arq, string $tono, array $m, array $e, array $real, array $boot, int $seed): string
    {
        if ($arq === 'muestra_chica') return self::_render_muestra_chica($m, $seed);

        $partes = [];

        // 1) Reconocimiento SOLO si el resultado lo valida (booster o alto real)
        $rec = self::_reconocimiento($m, $e, $real, $boot);
        if ($rec !== '') $partes[] = $rec;

        // 2) Diagnóstico del perfil + 3) jugada con script (en el tono)
        $partes[] = self::_perfil($arq, $m, $seed);

        // 4) Capa E si el cierre sale sucio
        $eng = self::_capa_e($m, $e, $real, $seed);
        if ($eng !== '') $partes[] = $eng;

        // 5) A quién empezar (dato vivo fiable) — solo donde la jugada no ya lo cubre
        $t = self::_target($m);
        if ($t !== '' && in_array($arq, ['sembrador','rematador_ausente','cultivador','presente_pasivo'], true)) {
            $partes[] = $t;
        }

        // 6) Consecuencia solo en sacudida / score bajo
        if ($tono === 'sacudida' || $tono === 'sacudida_cierre' || $m['nivel'] === 'bajo') {
            $partes[] = self::_consecuencia($seed);
        }

        return implode(' ', $partes);
    }

    // ── Reconocimiento validado (booster o alto real) ────────
    private static function _reconocimiento(array $m, array $e, array $real, array $boot): string
    {
        if ($boot['merito'] !== '') {
            return 'Un dato real a tu favor: ' . $boot['merito'] . '.';
        }
        // Alto REAL (no hueco) → reconocer la fortaleza de forma factual
        if ($e['conv'] === 'alto') return 'Cierras por encima del promedio — el remate lo tienes.';
        if ($e['seg']  === 'alto' && $real['seg']) return 'Trabajas tus señales calientes y eso se nota.';
        if ($e['act']  === 'alto' && $real['act']) return 'Lo que mandas se abre y no lo dejas enfriar — el arranque lo tienes.';
        return '';
    }

    // ── El perfil: diagnóstico + jugada + script ─────────────
    private static function _perfil(string $arq, array $m, int $seed): string
    {
        switch ($arq) {

            case 'desconectado':
                return self::_pick([
                    "Seamos claros: no está entrando flujo ni se cierra lo que entra, y ni siquiera estás leyendo lo que el sistema te deja masticado cada día. Esto no es de que no sepas — es de que no le estás entrando. Esta semana, lo mínimo: lee el análisis a diario y toca una sola cotización que ya se enfrió. Un paso, pero arranca hoy.",
                    "Ahorita el motor está apagado: poco entra, nada cierra, y la guía que te dice qué hacer ni la abres. Nadie va a vender por ti. Lo mínimo esta semana — leer y retomar una dormida — y de ahí construimos. El que no hace ni lo fácil no va a hacer lo difícil.",
                ], $seed + 1);

            case 'presente_pasivo':
                return self::_pick([
                    "Estás pasivo en las dos puntas que deciden la venta: entra poca propuesta nueva y de las que abren, pocas rematan. El medio se te sostiene, pero eso te mantiene a flote, no te hace crecer. Dos frentes: sube el flujo con un bloque diario para prospectar, y al que ya mostró interés llévalo a la decisión — «¿lo dejamos en la opción A o la B para arrancar esta semana?».",
                    "Trabajas de a ratos y no rematas: administras lo que llega en vez de generar y cerrar. Aparecer a medias no construye un mes. Métele las dos puntas — prospección diaria arriba, y abajo pedir la decisión de frente en vez de dejar la propuesta «enviada».",
                ], $seed + 1);

            case 'teatro':
                return self::_pick([
                    "Estás haciendo todo lo cómodo —marcar, revisar, seguir— y esquivando lo único que mueve el marcador: pedir la venta. " . self::_pl($m['cal'], 'señal trabajada', 'señales trabajadas') . " y " . ($m['cierres'] === 1 ? 'un cierre' : "{$m['cierres']} cierres") . ". Eso no es estar productivo, es estar ocupado. Marcar que alguien se interesó no lo acerca un paso; pedirle la decisión sí. En tu próxima caliente no la marques y la dejes: remata — «¿qué lo detiene: precio, tiempo o alcance?» y en cuanto lo resuelva, «¿arrancamos con la A o la B esta semana?».",
                    "Marcas cada señal y revisas cada cliente, pero de eso no sale ni una venta: es teatro de actividad. El trabajo no es tener el tablero al día, es pedir la venta —y ahí te frenas. En la que ya está picando, sácale la objeción de una vez —«¿es precio, tiempo o algo que no le cuadra?»— y pide la decisión de frente. Mientras el trabajo termine en nota y no en un sí o un no, el marcador no se mueve.",
                ], $seed + 1);

            case 'sembrador':
                return self::_pick([
                    "Generas interés de sobra y no lo capitalizas ni lo rematas: mandas y esperas que el cliente cierre solo. El interés sin un siguiente paso se enfría. Deja de armar la siguiente hasta trabajar la que ya abrieron: llámala en caliente y sácale la objeción —«si arrancáramos hoy, ¿qué es lo único que lo detiene?»— y pídele la decisión.",
                    "Traes muchos al agua y no los haces beber: buena parte de tu trabajo muere después del envío. Lo que cierra no es otra cotización, es una conversación de cierre — en la que ya mostró interés, dimensiona lo que le cuesta no resolverlo y pide la venta de frente.",
                ], $seed + 1);

            case 'rematador_ausente':
                return self::_pick([
                    "Haces casi todo bien —traes, trabajas las señales— y te caes en el último metro: pedir la decisión. Cerrar no es acompañar más, es sacar la objeción que no te dijeron y pedir la venta. En tu próxima caliente: «para ayudarle mejor, ¿es precio, tiempo o algo del alcance?», y en cuanto lo resuelva, da por hecho el sí — «¿arrancamos con la A o la B esta semana?».",
                    "Lees bien a tus clientes y los llevas cargados hasta la línea de gol para dejar el balón ahí. Tu seguimiento es fino; el remate falta. La próxima conversación con un caliente no puede terminar sin una pregunta de decisión sobre la mesa.",
                ], $seed + 1);

            case 'cultivador':
                return self::_pick([
                    "Tienes el pipeline lleno y bien cuidado, y no lo cosechas: cuidas tanto la relación que nunca la vuelves transacción. Ponle fecha de decisión a cada caliente: «la propuesta la sostengo hasta [fecha]; después cambian las condiciones. ¿La cerramos dentro de esa fecha o de plano no es momento?». El «¿o no es momento?» destraba al que estaba cómodo en el limbo.",
                    "Coleccionas interesados en vez de cerrarlos: un pipeline sano que no produce. Un cliente caliente eterno es un cliente perdido en cámara lenta. Fuerza el desenlace con una fecha de corte real — o compra dentro de ella, o te libera.",
                ], $seed + 1);

            case 'francotirador':
                return self::_pick([
                    "Lo que cotizas, lo cierras — el remate lo tienes. Lo que falta es cuánto sales a vender: juegas muy pocas manos. Con tu nivel de cierre, cada propuesta extra es casi una venta extra. Ponte una meta diaria de cotizaciones nuevas y reusa las que ya te funcionaron para sacar el triple en el mismo tiempo. No toques la técnica; dale volumen.",
                    "Cierras casi todo lo poco que trabajas: francotirador con poca munición. Tu techo no es cómo cierras, es cuánto expones. Bloquea una hora diaria solo para prospectar y cotizar — el volumen es tu única palanca pendiente.",
                ], $seed + 1);

            case 'cerrador_solitario':
                return self::_pick([
                    "Vives del cierre y no tienes pipeline detrás: dependes de rachas. El mes que no cae un cliente fácil, te quedas en ceros. Construye flujo y seguimiento para no depender de la suerte: meta diaria de propuestas y un toque a cada caliente, para que tu buen remate tenga sobre qué trabajar.",
                ], $seed + 1);

            case 'cerrador_desperdiciado':
                return self::_pick([
                    "Cierras bien lo que atiendes y dejas morir el resto del pipeline: conviertes lo fácil y desperdicias lo trabajable. No toques tu cierre; amplía cuánto del pipeline trabajas. Rescata al tibio antes de que muera con un ángulo nuevo —«me acordé de su proyecto, salió [algo], ¿lo retomamos?»— en vez de dejarlo enfriar.",
                ], $seed + 1);

            case 'una_pierna':
                return self::_pick([
                    "Cierras y cobras como pocos, pero solo al que compra a la primera: al de dos o tres toques lo dejas enfriar. Vendes con una pierna. Ponle al sostenimiento la misma disciplina que al cierre: al que no decide de una, agéndale un segundo toque con fecha — «le doy hasta [fecha] para aterrizarlo, ese día lo busco con la propuesta lista».",
                ], $seed + 1);

            case 'motor_completo':
                return self::_pick([
                    "Vas completo: traes, trabajas las señales, cierras y cobras. El trabajo ya no es vender más, es vender más grande y volver tu método enseñable. Cuando alguien ya te compró, súbele un complemento en el mismo cierre — «la mayoría suma esto porque les evita [problema] después». En el instante del sí, la resistencia a agregar es casi cero.",
                    "Rendimiento de primera: cada pieza empuja a la siguiente. Tu siguiente nivel es ticket más alto y clientes más grandes con el mismo método — y documentar lo que haces para que sea tu estándar, no tu buen mes.",
                ], $seed + 1);

            case 'meseta':
            default:
                return self::_pick([
                    "No tienes fugas graves, te falta filo: competente y estancado. La medianía se rompe por un punto, no por todos. Mete un solo hábito esta semana — en cada trato, una pregunta de implicación: «¿qué le cuesta seguir sin resolver esto?». Es lo que más mueve el cierre sin trabajar más horas.",
                    "Todo se sostiene y nada brilla. El salto sale de profundizar, no de corregir: elige un frente —normalmente el cierre— y llévalo de «bien» a «excelente» este mes pidiendo un compromiso más firme del que sueles pedir.",
                ], $seed + 1);
        }
    }

    // ── Capa E (cierre sucio) — se suma si aplica ────────────
    private static function _capa_e(array $m, array $e, array $real, int $seed): string
    {
        // Necesita un PATRÓN de ventas (2+); con 1 venta no hay "así cierras siempre".
        // Y si casi no cierra, la fuga es el cierre, no el cobro.
        if ($m['cierres'] < 2 && $m['vper'] < 2) return '';
        // Dominante entre las 3 penalizaciones
        $g = ['cobro' => $m['eps'], 'descuento' => $m['epd'], 'volumen' => $m['epb']];
        arsort($g);
        $k = array_key_first($g);
        if ($g[$k] < 0.03) return '';
        switch ($k) {
            case 'cobro':
                return "Y ojo con cerrar en falso: " . self::_pl($m['vsp'], 'una venta lleva días', 'ventas llevan días') . " sin un peso — pide el anticipo desde el sí: «para apartar y arrancar hoy, dejamos el anticipo, ¿transferencia o tarjeta?». Quien pone dinero ya decidió.";
            case 'descuento':
                return "Y estás comprando el sí con descuento: sostén el precio y cede en plazo o alcance, no en el número — «lo puedo ajustar, pero cerrando hoy con el anticipo completo».";
            case 'volumen':
            default:
                return "Y vendes por debajo del equipo: no es cómo cierras, es cuánto — meta de volumen y un complemento en cada cierre.";
        }
    }

    // ── A quién empezar (datos fiables) ──────────────────────
    private static function _target(array $m): string
    {
        $sin_expl = max(0, $m['cal'] - $m['exp']);
        if ($sin_expl > 0) {
            return $sin_expl === 1
                ? "Empieza por la caliente que aún no abres en el ❓ del Radar."
                : "Empieza por las {$sin_expl} calientes que aún no abres en el ❓ del Radar.";
        }
        if ($m['dorm'] > 0) {
            return $m['dorm'] === 1
                ? "Arranca con el cliente que abrió y no volvió."
                : "Arranca con los {$m['dorm']} que abrieron y no volvieron.";
        }
        return '';
    }

    private static function _consecuencia(int $seed): string
    {
        return self::_pick([
            "Con estos números el mes no cierra, y eso pega a todo el equipo. Tiene que cambiar esta semana.",
            "Así no se sostiene. La empresa necesita que estos números reaccionen ya, no el mes que entra.",
            "Cada semana así cuesta dinero real. Esto es prioridad hoy.",
        ], $seed + 11);
    }

    private static function _render_muestra_chica(array $m, int $seed): string
    {
        $a = $m['asig'];
        $op = self::_pick([
            "Apenas " . self::_pl($a, 'cotización en el período', 'cotizaciones en el período') . " — muy poco para juzgar tu cierre. Tu prioridad ahora es volumen: manda más propuestas y el termómetro empieza a leerte de verdad.",
            "Con " . self::_pl($a, 'sola cotización', 'cotizaciones') . " el termómetro casi no tiene qué leer. Sube el número de propuestas y los números empiezan a hablar.",
        ], $seed);
        $sin_expl = max(0, $m['cal'] - $m['exp']);
        if ($sin_expl > 0) {
            $op .= ' ' . ($sin_expl === 1
                ? "Y si ya tienes 1 caliente en el Radar, atiéndela hoy — no dejes ir lo poco activo que tienes."
                : "Y si ya tienes {$sin_expl} calientes en el Radar, atiéndelas hoy — no dejes ir lo poco activo que tienes.");
        }
        return $op;
    }
}
