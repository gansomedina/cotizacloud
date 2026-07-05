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
        // PÁRRAFO 2 — el perfil psicológico + los tips exactos (SIN números).
        return self::_parrafo_perfil($arq, $m, $boot);
    }

    // ════════════════════════════════════════════════════════
    //  PÁRRAFO 1 — Los NÚMEROS reales (clavado al dato, sin prosa)
    //  Público: el dashboard lo muestra como el primer tip.
    // ════════════════════════════════════════════════════════
    public static function numeros(array $s, ?array $ctx = null): string
    {
        $m = self::_metricas($s, $ctx);
        if (($s['nivel'] ?? '') === 'nuevo' || $m['asig'] === 0) return '';
        return self::_parrafo_numeros($m);
    }

    private static function _parrafo_numeros(array $m): string
    {
        $f = [];
        // Embudo
        $vist = $m['vist'] === $m['asig'] ? 'todas' : (string)$m['vist'];
        $abr  = $m['vist'] === 1 ? 'abrió' : 'abrieron';
        $cerr = $m['cierres'] === 1 ? 'cerraste 1' : "cerraste {$m['cierres']}";
        $f[]  = "De " . self::_pl($m['asig'], 'propuesta', 'propuestas') . ", {$vist} {$abr} y {$cerr}.";
        // Radar
        if ($m['cal'] > 0) {
            $r = self::_pl($m['cal'], 'cliente se puso caliente', 'clientes se pusieron calientes');
            if ($m['h_down'] > 0) $r .= "; " . self::_pl($m['h_down'], 'se enfrió sin cerrar', 'se enfriaron sin cerrar');
            $f[] = ucfirst($r) . ".";
        }
        if ($m['dorm'] > 0) $f[] = ucfirst(self::_pl($m['dorm'], 'cliente abrió y no volvió', 'clientes abrieron y no volvieron')) . ".";
        if ($m['nab'] > 0)  $f[] = ucfirst(self::_pl($m['nab'], 'propuesta lleva', 'propuestas llevan')) . " 5+ días sin abrir.";
        // Uso de la guía
        if ($m['dias_act'] > 0) $f[] = "Leíste la guía {$m['dias_lec']} de {$m['dias_act']} días activos.";
        // Boosters (mérito por dinero)
        if ($m['bticket'] >= 5) $f[] = ($m['bticket_v'] === 1 ? 'Cerraste una venta grande' : "Cerraste {$m['bticket_v']} ventas grandes") . " (arriba de tu ticket promedio).";
        if ($m['bcierre'] >= 4) $f[] = "Tu tasa de cierre viene por arriba de tu histórico.";
        // Cierre sucio (solo con ventas reales)
        if ($m['cierres'] >= 1 || $m['vper'] >= 1) {
            if ($m['vsp'] > 0) $f[] = ucfirst(self::_pl($m['vsp'], 'venta cerrada sin cobrar', 'ventas cerradas sin cobrar')) . ".";
            if ($m['epd'] > 0.03) $f[] = "Cerraste con descuento.";
            if ($m['bventas'] > 0 && $m['vper'] < $m['bventas']) $f[] = "Vendiste " . self::_pl($m['vper'], 'venta', 'ventas') . " vs " . round($m['bventas'], 1) . " del promedio del equipo.";
        }
        return implode(' ', $f);
    }

    // ════════════════════════════════════════════════════════
    //  PÁRRAFO 2 — El PERFIL psicológico + tips exactos (SIN números)
    //  Determinado por el vector de 5 segmentos y sus valores internos.
    // ════════════════════════════════════════════════════════
    private static function _parrafo_perfil(string $arq, array $m, array $boot): string
    {
        $capaz = $boot['capaz'];
        switch ($arq) {

            case 'desconectado':
                return "Tu perfil es el del desconectado: no estás en el juego. Ni entras al sistema ni cierras, y ni la guía que te dice qué hacer abres — y eso no se arregla con técnica, se arregla decidiendo entrarle. El tip para ti: esta semana solo lo mínimo, lee el análisis a diario y retoma un cliente que se enfrió. Una victoria chica para volver a arrancar el motor.";

            case 'presente_pasivo':
                return "Tu perfil es el del pasivo: administras lo que te llega en vez de salir a generar y a cerrar. El tip: fuerza las dos puntas del embudo. Arriba, una hora fija de prospección cada día. Abajo, a cada cliente que ya abrió, pídele la decisión de frente en lugar de esperar a que conteste.";

            case 'teatro':
                $s = "Tu perfil es el que confunde actividad con venta: haces todo lo cómodo —marcar señales, revisar el Radar— y esquivas lo único que cierra, pedir la venta.";
                $s .= $capaz
                    ? " No es que no sepas —cuando te lo propones, cierras—, es que evitas el momento de rematar."
                    : " Es renuencia a rematar, no falta de técnica.";
                $s .= " El tip para ti: deja de mandar seguimientos y empieza a pedir cierres. En cada caliente saca la objeción —«¿qué lo detiene: precio, tiempo o alcance?»— y en cuanto la resuelva, pide la fecha directo: «¿lo cerramos esta semana o la próxima?».";
                return $s;

            case 'sembrador':
                return "Tu perfil es el del sembrador que no cosecha: se te da generar interés y evitas rematarlo — mandas y esperas. El tip: por cada propuesta nueva que armes, remata una que ya abrieron. Llámala en caliente —«si arrancáramos hoy, ¿qué es lo único que lo detiene?»— y pídele la decisión.";

            case 'rematador_ausente':
                return "Tu perfil es el del rematador ausente: haces bien todo el proceso —generas, sigues, atiendes las señales— y te falta solo el último paso, pedir la venta. Esto sí es técnica de cierre, no falta de ganas. El tip: tras resolver la duda —«¿es precio, tiempo o alcance?»— no vuelvas a preguntar si le interesa; dalo por hecho y pon la fecha: «¿arrancamos esta o la próxima semana?».";

            case 'cultivador':
                return "Tu perfil es el del cultivador que no cosecha: cuidas tanto la relación que nunca la cierras — tu pipeline está lleno y vivo, pero no produce. El tip: ponle fecha de decisión a cada caliente. «La sostengo hasta [fecha], ¿la cerramos antes o de plano no es momento?». El «¿o no es momento?» obliga a decidir al que está cómodo en el limbo.";

            case 'francotirador':
                return "Tu perfil es el del francotirador: cierras casi todo lo que trabajas, pero trabajas poco — tienes puntería y te falta munición. El tip: no toques cómo cierras, dale volumen. Meta diaria de cotizaciones nuevas y reusa las que ya te funcionaron para salir a más manos.";

            case 'cerrador_solitario':
                return "Tu perfil es el del cerrador solitario: vives del cierre pero sin pipeline atrás, así que dependes de rachas. El tip: construye flujo — meta diaria de propuestas y un toque a cada caliente — para que tu buen remate no dependa de la suerte del mes.";

            case 'cerrador_desperdiciado':
                return "Tu perfil es el del cerrador que deja morir: cierras bien lo que atiendes pero abandonas el resto del pipeline. El tip: no toques tu cierre; rescata al tibio antes de que se muera con un ángulo nuevo —«salió algo que le acomoda a su proyecto, ¿lo retomamos?»— en lugar de dejarlo enfriar.";

            case 'una_pierna':
                return "Tu perfil es el del cerrador de una pierna: cierras al que decide rápido y sueltas al que necesita dos o tres toques. El tip: al que no decide de una, agéndale el siguiente contacto con fecha —«le doy hasta [fecha] para verlo con calma; ese día lo busco con la propuesta lista para cerrar»— y trátalo como un cierre en dos tiempos, no como perdido.";

            case 'motor_completo':
                return "Tu perfil es el del motor completo: traes, sigues, cierras y cobras, y cada pieza empuja a la siguiente. El tip: para crecer, ve por clientes más grandes con el mismo método, y enséñale al equipo lo que haces — vuelve tu forma de vender algo repetible, no solo tu buen mes.";

            case 'meseta':
            default:
                return "Tu perfil es el del estable sin filo: no tienes fugas graves, pero tampoco una fortaleza que jale. El tip: elige el cierre y afílalo — en cada propuesta que abran, en vez de esperar respuesta, pregunta «¿qué falta para decidir?» y pon una fecha. Un solo hábito sube el cierre sin trabajar más horas.";
        }
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
