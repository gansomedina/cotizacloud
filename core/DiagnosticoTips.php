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
    // Reestructurado tras auditoría: S, H y E se revisan SIEMPRE (no solo en los
    // extremos de C). motor_completo solo si S/H/E están OK. desconectado no atropella
    // a un cerrador. La franja C=medio ya no es ciega a las fugas de S/H/E.
    private static function _arquetipo(array $m, array $e, array $real): string
    {
        if ($m['asig'] < 6 && $m['cierres'] === 0) return 'muestra_chica';

        $aBaja   = $e['act'] === 'bajo';
        $cAlta   = $e['conv'] === 'alto';
        $cBaja   = $e['conv'] === 'bajo';
        $segBajo = $e['seg'] === 'bajo';
        $segAlto = $e['seg'] === 'alto' && $real['seg'];
        $hltAlto = $e['hlt'] === 'alto' && $real['hlt'];
        $hltBajo = $e['hlt'] === 'bajo';
        $engBajo = $e['eng'] === 'bajo';

        // Desconectado: casi todo apagado Y no cierra. Si cierra alto, NO está apagado.
        $bajos = 0;
        foreach ($e as $st) if ($st === 'bajo') $bajos++;
        if ($bajos >= 4 && !$cAlta) return 'desconectado';

        // Teatro: S alto hueco + no cierra + activación OK (si A en piso, la fuga es A).
        if (!$aBaja && $real['seg'] === false && $m['s_conv'] < self::BAJO && $e['seg'] === 'alto') return 'teatro';

        // ACTIVACIÓN en el piso → se maneja aparte
        if ($aBaja) {
            if ($cAlta) {
                if ($segBajo && $hltBajo) return 'cerrador_solitario';
                return 'francotirador';
            }
            if ($e['seg'] === 'alto' || $e['eng'] === 'alto' || !$cBaja) return 'sin_ritmo';
            return 'presente_pasivo';
        }

        // A no baja · C↓  (falta técnica de cierre)
        if ($cBaja) {
            if ($segAlto && $hltAlto) return 'cultivador';
            if ($segAlto)             return 'rematador_ausente';
            return 'sembrador';
        }

        // A no baja · C medio/alto — revisar las fugas del medio ANTES de motor/meseta.
        // Prioridad: señales (S) → pipeline (H) → cierre limpio (E).
        if ($segBajo && $hltBajo) return 'una_pierna';                    // solo caza al que decide rápido
        if ($segBajo)             return 'sordo_a_senales';               // ignora las señales calientes
        if ($hltBajo)             return $cAlta ? 'cerrador_desperdiciado' : 'pipeline_frio';
        if ($engBajo) {
            $gp = ['cierre_falso' => $m['eps'], 'regalador' => $m['epd'], 'bajo_caudal' => $m['epb']];
            arsort($gp);
            $kp = array_key_first($gp);
            return $gp[$kp] > 0.05 ? $kp : 'engagement_flojo';            // fallback: E bajo sin pen dominante
        }

        if ($cAlta) return 'motor_completo';   // S, H y E ya verificados OK
        return 'meseta';                       // C medio, todo lo demás OK
    }

    // ════════════════════════════════════════════════════════
    //  COMPOSICIÓN
    // ════════════════════════════════════════════════════════
    private static function _componer(string $arq, string $tono, array $m, array $e, array $real, array $boot, int $seed): string
    {
        if ($arq === 'muestra_chica') return self::_render_muestra_chica($m, $seed);
        // PÁRRAFO 2 — el perfil psicológico + los tips exactos (SIN números).
        // 3 variantes por perfil, rotadas por (usuario+día) → el lector diario no repite.
        return self::_parrafo_perfil($arq, $m, $boot, $seed);
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
    private static function _parrafo_perfil(string $arq, array $m, array $boot, int $seed): string
    {
        // 3 variantes por perfil, cada una con distinto ÁNGULO (reto / coach / calle).
        // El guion mecánico de cierre («precio, tiempo o alcance» / «pon fecha») vive
        // SOLO en rematador_ausente — su bloqueo es falta de técnica. En los demás el
        // cierre se ataca por su bloqueo emocional, con lenguaje distinto (un pecado,
        // un mensaje).
        $V = [
            'desconectado' => [
                "Ahorita no se trata de vender, se trata de volver a agarrar el ritmo. Escoge UN cliente, el que se enfrió pero te caía bien, y retómalo hoy sin agenda: «me acordé de usted, ¿en qué quedó su proyecto?». Uno hoy, otro mañana. El músculo se recupera moviéndolo, no pensándolo.",
                "No es cómo vendes, es que te apagaste. La salida no es esforzarte más, es volver al juego con algo chico: una sola cotización vieja, puesta al día hoy. No busques cerrar, busca moverte — el movimiento trae las ganas, no al revés.",
                "Lo pesado es arrancar, no seguir. Regla de hoy: abre el sistema y trabaja UNA cotización, aunque sean dos minutos. Casi siempre que empiezas, sigues. Una victoria chica prende el motor.",
            ],
            'presente_pasivo' => [
                "Estás viviendo de lo que cae del árbol, y el árbol se seca. Bloquéate una hora en la mañana, la misma todos los días, solo para buscar gente nueva — ni correos ni cotizaciones viejas, pura caza. Esa hora es sagrada.",
                "Traes el motor en neutral: ni metes prospectos nuevos ni empujas los que ya tienes. Empieza por una punta, la de arriba: una cuota mínima de cotizaciones nuevas hoy, pase lo que pase. Romper el modo espera es el primer paso.",
                "El que solo atiende lo que llega, se apaga. Vuelve a ser tú quien mueve las fichas: una hora fija de prospección al día, aunque no te nazca. Lo que entra solo nunca llena el mes.",
            ],
            'sin_ritmo' => [ // A↓: se le cayó la disciplina diaria (no lee, deja enfriar, cae).
                             // NO afirmar que vende bien — su conversión puede estar baja también.
                "Se te cayó el ritmo: dejaste de leer el análisis, tienes clientes que abrieron y se enfriaron, y vienes a la baja. El pipeline se alimenta a diario o se seca. Empieza por reengancharte: abre la guía en la mañana y dale un toque a cada cliente que ya te vio antes de que se enfríe.",
                "Andas en automático: ni revisas el análisis ni retomas a los que ya abrieron, y el marcador viene cayendo. Un vendedor a media máquina rinde menos que uno a full. Vuelve a la disciplina diaria —la guía cada día, un toque a cada tibio— antes de que el mes se caiga más.",
                "Bajaste el ritmo: cero lectura y varios tibios sin retomar. Lo primero es volver a aparecer todos los días con tus clientes: abre la guía a diario y no dejes dormir a nadie que ya te vio. Sobre esa base recuperada, lo demás se acomoda.",
            ],
            'teatro' => [
                "Traes mucho movimiento y poca venta, y el fondo es un miedito: pedir la decisión expone a un «no». Pero el «no» no te mata, la duda sí — te come el tiempo cuidando a alguien que nunca iba a comprar. Al próximo caliente, en vez de otro seguimiento, pídele una respuesta clara: sí o no.",
                "Revisar y marcar se siente productivo, pero es el disfraz de la parte que evitas. Sé honesto: ¿estás trabajando o esquivando el momento de pedir la venta? Deja de contar cuántos seguimientos mandas y cuenta cuántas veces pediste el cierre esta semana.",
                "Un «no» rápido es un regalo: te libera para el que sí. Estás gastando energía en mantener vivas cotizaciones que no te han dicho nada — hoy elige la más caliente y pídele que se defina. El que no pide, no pierde la venta: nunca la tuvo.",
            ],
            'sembrador' => [
                "Eres bueno para prender el interés; lo que dejas tirado es la cosecha. Regla simple: por cada cotización nueva que mandes, primero remata una que ya abrieron. Ganas el gusto de crear solo después de cobrar lo sembrado.",
                "El subidón lo tienes en mandar, pero el dinero está en volver. Tus mejores prospectos no son los nuevos, son los que ya vieron tu propuesta y no te han dicho que no. Empieza el día por ahí, no por lo nuevo.",
                "Siembras mucho y cosechas poco: propuestas abiertas juntando polvo mientras haces otras nuevas. Antes de crear una más, ve a una que ya abrió y remátala hoy. Un sembrador que no cosecha se queda sin bodega, no sin semilla.",
            ],
            'rematador_ausente' => [ // ← único con el guion mecánico de cierre
                "Haces todo bien hasta el segundo antes de cobrar la pieza. El error está en la pregunta: cuando dices «¿le interesa?», le das chance de «déjeme lo pienso». No preguntes si quiere, asume que ya: «le tengo lugar esta semana o la próxima, ¿cuál le acomoda?».",
                "Tú ya ganaste, nomás no recoges el premio. Cambia el sí/no por dos opciones donde ambas son un sí: «¿lo dejamos con el alcance completo o arrancamos con la primera etapa?». El cliente elige cómo comprar, no si comprar.",
                "Antes de pedir la venta, destapa lo que estorba con una pregunta directa: «¿qué necesitarías para decidir hoy?». Te dice el obstáculo real; lo resuelves, y ahí mismo pones la fecha — sin volver a preguntar si quiere.",
            ],
            'cultivador' => [
                "Eres tan buena onda que ya eres su amigo, y a los amigos no se les cobra — por eso no cierras. Usa la confianza, no la escondas: «con la confianza que ya hay le hablo derecho, ¿le entramos o lo dejamos para más adelante?». La franqueza respeta la relación, no la rompe.",
                "Tu miedo no es al «no», es a vaciar el pipeline si cierras. Pero un prospecto en el limbo no es un activo, es peso muerto. Date permiso de soltarlo: «la sostengo hasta [fecha]; si no es el momento, la cerramos y liberamos a los dos».",
                "Estás cómodo en el «ahí la llevamos» y el cliente también — por eso nunca avanza. Rómpelo forzando la definición: «¿la cerramos antes de [fecha] o de plano no es momento?». Un no claro vale más que un tal vez eterno.",
            ],
            'francotirador' => [ // ← nunca guion de cierre; su cierre ya es bueno
                "Lo que agarras, lo tumbas — puntería pura. El pecado no es cómo cierras, es que disparas poquito. No te toco la técnica: métete una meta de cotizaciones nuevas al día. Con tu cierre, el doble de volumen es el doble de venta.",
                "Tu cierre es de lujo, no se toca. Lo que falta es cantidad: más propuestas afuera, punto. Reusa las que ya te funcionaron para llegarle a más gente hoy.",
                "Eres francotirador, no ametralladora — y a veces se necesita ametralladora. Sube el número de tiros: bloquea una hora diaria solo para cotizar. Tu porcentaje ya es tu superpoder; multiplícalo.",
            ],
            'cerrador_solitario' => [
                "Cierras bien pero vives al día: dependes de que caiga algo, y cuando no cae, te quedas seco. Arma colchón: mientras cierras lo de hoy, ten cinco cosas prendiéndose para la semana que entra. El cierre lo tienes; la fila no.",
                "Traes buena mano pero cero banca. Cambia el orden: dedica la primera parte del día a generar, no a cerrar. Así nunca amaneces sin con qué trabajar.",
                "Vives de rachas porque no siembras mientras cosechas. Un toque nuevo diario y dejas de rezarle al mes. Eres bueno cerrando, malo llenando — llena.",
            ],
            'cerrador_desperdiciado' => [
                "Lo que atiendes lo cierras, pero dejas morir gente que ya había mostrado interés — eso es dinero a la basura. Regrésales con un pretexto nuevo: «salió algo que le queda justo a lo que buscaba, ¿lo retomamos?». No los perdiste, los dejaste.",
                "Tienes cierre pero eres desperdiciado: dejas morir clientes vivos. Aparta un rato hoy solo para rescatar tibios — reactívalos con novedad, no con «sigo pendiente».",
                "El que ya abrió y dejaste ir no está muerto, está dormido. Despiértalo con un motivo nuevo para volver. Traer uno de vuelta cuesta menos que conseguir uno de cero.",
            ],
            'sordo_a_senales' => [ // cierra bien pero NO capitaliza las señales calientes
                "Cierras a los que ya vienen convencidos, pero ignoras las señales: cuando un cliente revisa tu propuesta con interés, el sistema te lo marca y no lo trabajas. Ahí se te escapan los cierres más fáciles. Cada mañana revisa quién se puso caliente y contáctalo el mismo día — el que ya te levantó la mano está a un paso.",
                "Tu problema no es rematar, es que no capitalizas lo que ya está caliente: dejas pasar a los que te mostraron interés. Ese interés dura horas, no días. Reacciona a la señal el mismo día que aparece, no cuando tengas hueco.",
                "Cierras bien lo que trabajas, pero trabajas solo lo que te cae de frente; las señales de compra que el sistema te marca, las ignoras — y ahí está tu venta más cercana. Empieza el día por los calientes de hoy.",
            ],
            'pipeline_frio' => [ // deja morir calientes (H bajo) sin ser cerrador top
                "Se te están enfriando clientes que ya habían mostrado interés — los dejas madurar hasta que se pierden. Un caliente que se apaga casi nunca es mal cliente, es desatención. Rescátalo antes de que muera con un motivo nuevo: «salió algo que le acomoda, ¿lo retomamos?».",
                "Tu pipeline pierde temperatura: varios que estaban calientes se murieron sin cerrar. Aparta un rato al día para tocar a los tibios antes de que se enfríen del todo — ahí hay media venta ya hecha.",
                "Dejas morir el interés que ya generaste, que es lo más caro porque ya hiciste lo difícil. Un toque con ángulo nuevo reabre lo que el silencio cerró; no lo dejes para después.",
            ],
            'engagement_flojo' => [ // E bajo sin penalización dominante (cierre sucio genérico)
                "Cierras, pero el cierre te sale flojo: entre cobros que se atrasan, algún descuento y ventas por debajo del equipo, ganas menos de lo que deberías. Aprieta el remate — pide el anticipo desde el sí y defiende el precio con valor, no con rebaja.",
                "Vendes, pero no aseguras: el «sí» se te queda a medias. Amarra cada cierre con un anticipo el mismo día y sostén tu precio. Cerrar incluye cobrar, y cobrar bien.",
                "Tu número de cierres está, pero la calidad no: cierras sin cobrar del todo o cediendo de más. Sube el estándar del remate — dinero de por medio desde el sí, y el precio se defiende, no se regala.",
            ],
            'una_pierna' => [
                "Cierras rifado al que decide rápido, pero al que necesita dos o tres vueltas lo sueltas — y ahí está la mitad de tu dinero. El de ciclo largo no se cierra hoy, se agenda: «le doy hasta el viernes, ese día lo busco con la propuesta lista». Ponle fecha y no lo sueltes.",
                "Corres los 100 metros pero no la maratón. Al lento no le pierdas la paciencia: agéndalo con fecha exacta y regrésale ese día. El de ciclo largo no se pierde por lento, se pierde por olvidado.",
                "Al de decisión lenta no le pidas el sí grande de golpe — se asusta. Pídele un paso chico: una duda que resolver, un siguiente contacto con fecha. Los ciclos largos se cierran por escalones, no de un salto.",
            ],
            'motor_completo' => [ // ← nunca guion de cierre; ya cierra
                "Traes el paquete completo: generas, sigues, cierras y cobras. Ya no creces haciendo más de lo mismo con clientes chicos — crece hacia arriba: el mismo método en cuentas más grandes, donde una venta vale por cinco.",
                "Estás afinado. Lo que sigue no es más volumen, es más tamaño y más gente: cuentas más gordas, y empieza a soltarle tu forma al equipo. Un maestro vale más que un vendedor.",
                "Estás en la cima, y ahí el enemigo es la comodidad. No aflojes lo que te trajo: mantén tu cuota de propuestas nuevas aunque el mes ya esté ganado. Los mejores no bajan el ritmo cuando van ganando.",
            ],
            'regalador' => [
                "Cierras, pero regalando: bajas el precio para asegurar el sí. Eso no es vender, es rematar tu propio margen. La próxima que te pidan descuento, aguanta el silencio y defiende el valor antes de mover el número — el que pide precio muchas veces sí compra al precio.",
                "Vendes bien pero la empresa gana poco, porque cierras con rebaja. Cambia el descuento por una condición: «te lo ajusto, pero cerrando hoy con el anticipo completo». Un descuento regalado devalúa tu producto; uno a cambio de algo, no.",
                "Estás comprando el sí con precio. Antes de soltar rebaja, recuérdale qué incluye y por qué vale; y si cedes, que sea a cambio de volumen o de decidir hoy, nunca gratis.",
            ],
            'cierre_falso' => [
                "Cierras la venta pero no aseguras el pago: una venta sin anticipo es un favor, no una venta. Pide el anticipo como parte del sí: «para apartar y arrancar, va el anticipo, ¿transferencia o tarjeta?». Quien pone dinero ya decidió; el que no, todavía se puede arrepentir.",
                "Le pones todo el empeño a que firme y ninguno a que pague — y así se te caen las ventas. Cerrar incluye cobrar: el mismo día del sí, deja amarrado el anticipo. Sin dinero de por medio, el cliente sigue siendo prospecto.",
                "Un «sí» sin pago se enfría igual que una cotización. Amarra el compromiso con un anticipo desde el cierre, no después. El que ya puso un peso deja de comparar y se vuelve tu cliente.",
            ],
            'bajo_caudal' => [
                "Cierras limpio, sin regalar — eso está bien — pero tu número total queda por debajo del equipo. No es cómo vendes, es cuánto: súbele al volumen de propuestas nuevas por semana sin bajar tu estándar.",
                "Vendes bien pero poco. Tu cierre ya es sólido; lo que falta es más gente en el embudo. Ponte una meta semanal de ventas y llena arriba para alcanzarla.",
                "Buena calidad, poco caudal. Lo que te limita no es la técnica, es el número de intentos. Más propuestas afuera, mismo cuidado al cerrar.",
            ],
            'meseta' => [
                "No traes fugas ni una fortaleza que jale — estás parejo, y lo parejo no destaca. No arregles todo: escoge UNA cosa que ya te sale medio bien y vuélvete el mejor de la oficina en eso. Al que es EL BUENO en algo, lo buscan.",
                "Estás en tierra de nadie, ni mal ni bien. Elige un filo —prospección, cierre o seguimiento— y afílalo hasta que te distinga. Ser correcto en todo no vende; ser el mejor en una cosa, sí.",
                "La meseta se rompe con foco, no con esfuerzo repartido. Una sola habilidad, a fondo, este mes. Después mueves la siguiente.",
            ],
        ];
        $pool = $V[$arq] ?? $V['meseta'];
        return self::_pick($pool, $seed);
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
