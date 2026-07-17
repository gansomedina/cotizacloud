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

    // Expone el arquetipo del asesor para otros motores (Mesa de Trabajo).
    // Reusa el pipeline existente sin tocarlo.
    public static function arquetipo(array $s, ?array $ctx = null): string
    {
        $m = self::_metricas($s, $ctx);
        if (($s['nivel'] ?? '') === 'nuevo' || $m['asig'] === 0) return 'muestra_chica';
        $e    = self::_estados($m);
        $real = self::_validar($m, $e);
        return self::_arquetipo($m, $e, $real);
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

        $txt = self::_componer($arq, $tono, $m, $e, $real, $boot, $seed);
        // Reprobado del 25% de la mesa — SIEMPRE anexado al final (nunca antes
        // de un corte: lección bonus_ticket). Gates: s_mesa calculado (flag=2),
        // reprobado, y hubo señales (con pedidas=0 s_mesa=1, imposible reprobar).
        if ($m['s_mesa'] !== null && $m['s_mesa'] === 0.0 && $m['mesa_ped'] > 0) {
            $txt .= ' ' . self::_frase_mesa($m, $seed);
        }
        return $txt;
    }

    // ── Frase del Seguimiento reprobado (= la mesa): hechos + jugada ──
    private static function _frase_mesa(array $m, int $seed): string
    {
        // mesa_ped = cotizaciones de la mesa visible; mesa_att = las que tienen
        // feedback 👍👎 + postura. Tu Seguimiento ES esto (100%), no un cuarto.
        $x = $m['mesa_att']; $y = $m['mesa_ped']; $n = max(0, $y - $x);
        $pool = [
            "Trabajaste {$x} de " . self::_pl($y, 'cotización de tu mesa', 'cotizaciones de tu mesa')
            . " — " . ($n === 1 ? 'una se quedó' : "{$n} se quedaron")
            . " sin tocar. Tu Seguimiento ES tu mesa, y hoy está en cero. Cada una necesita feedback 👍👎 + postura; cubre el 80% y vuelve completo.",
            "Tu mesa tiene " . self::_pl($y, 'cotización', 'cotizaciones') . " y trabajaste {$x}. El Seguimiento es tu mesa: entra, dale a cada una feedback 👍👎 + postura (aunque sea para descartarla) y con el 80% tu Seguimiento vuelve completo.",
        ];
        if ($n >= 2) {
            $pool[] = "Tienes " . self::_pl($n, 'cotización', 'cotizaciones') . " de tu mesa sin tocar. Nadie te pide cerrarlas, te pide trabajarlas: entra, dales feedback 👍👎 + postura y declara qué pasó. Ocho de cada diez y tu Seguimiento regresa.";
        }
        return self::_pick($pool, $seed);
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
            'castigo_seg'=> (int)($s['castigo_seguimiento'] ?? 0),
            'dias_venc'  => (int)($s['mesa_dias_vencidos'] ?? 0),
            'ticket'     => (float)($s['ticket_promedio'] ?? 0),
            // Mesa de Trabajo (25% del Seguimiento cuando mesa_activa=2)
            'mesa_on'    => (int)($ctx['mesa_activa'] ?? 0) >= 1,
            's_mesa'     => array_key_exists('s_mesa', $s) && $s['s_mesa'] !== null ? (float)$s['s_mesa'] : null,
            'mesa_ped'   => (int)($s['mesa_pedidas'] ?? 0),
            'mesa_att'   => (int)($s['mesa_atendidas'] ?? 0),
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
        // La diligencia se mide por FOLLOW-UP observable: cubrió sus calientes
        // (exploró/dio feedback) y NO las dejó dormir. Leer la guía (tips_s) NO
        // es follow-up — es voluntad/coachabilidad, va aparte (_voluntad).
        //   Diligente → seguimiento real, es falta de técnica de cierre (rematador).
        //   No diligente (clickea pero deja enfriar) → hueco, teatro/renuencia (voluntad).
        $cobertura = $m['cal'] > 0 ? $m['exp'] / $m['cal'] : 0.0;
        $diligente = $cobertura >= 0.7 && ($m['dorm'] / max($m['vist'], 1)) < 0.20;
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
        $vist = $m['vist'] === $m['asig'] ? ($m['asig'] === 1 ? 'la abrió' : 'todas abrieron')
              : (string)$m['vist'] . ($m['vist'] === 1 ? ' abrió' : ' abrieron');
        $cerr = $m['cierres'] === 1 ? 'cerraste 1' : "cerraste {$m['cierres']}";
        $f[]  = "De " . self::_pl($m['asig'], 'propuesta', 'propuestas') . ", {$vist} y {$cerr}.";
        // Radar — cal (5 buckets calientes) y h_down (10 buckets muertos) NO son
        // subconjunto uno del otro: frases separadas, sin implicar "N de M"
        if ($m['cal'] > 0) {
            $f[] = ucfirst(self::_pl($m['cal'], 'cliente se puso caliente', 'clientes se pusieron calientes')) . ".";
        }
        if ($m['h_down'] > 0) {
            $f[] = ucfirst(self::_pl($m['h_down'], 'cliente perdió su señal sin cerrar', 'clientes perdieron su señal sin cerrar')) . ".";
        }
        if ($m['dorm'] > 0) $f[] = ucfirst(self::_pl($m['dorm'], 'cliente abrió y no volvió', 'clientes abrieron y no volvieron')) . ".";
        if ($m['nab'] > 0)  $f[] = ucfirst(self::_pl($m['nab'], 'propuesta lleva', 'propuestas llevan')) . " 5+ días sin abrir.";
        // Uso de la guía
        if ($m['dias_act'] > 0) $f[] = "Leíste la guía {$m['dias_lec']} de {$m['dias_act']} días activos.";
        // Boosters (mérito por dinero)
        if ($m['bticket'] >= 5) $f[] = ($m['bticket_v'] === 1 ? 'Cerraste una venta grande' : "Cerraste {$m['bticket_v']} ventas grandes") . " (arriba de tu ticket promedio).";
        if ($m['bcierre'] >= 4) $f[] = "Tu tasa de cierre viene por arriba de tu histórico.";
        // Castigo por seguimiento vencido (ciclo Fase C) — el -2 es silencioso
        if ($m['castigo_seg'] >= 8) {
            $f[] = "⏰ Acumulas {$m['dias_venc']} días-cotización de seguimiento vencido en tu mesa — te está costando {$m['castigo_seg']} puntos: las vencidas van primero, un contacto declarado las pone al corriente.";
        } elseif ($m['castigo_seg'] >= 5) {
            $f[] = "⏰ Acumulas {$m['dias_venc']} días-cotización de seguimiento vencido en tu mesa — te está costando {$m['castigo_seg']} puntos.";
        }
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
            'sin_ritmo' => [ // A↓: disciplina diaria caída (no lee + deja enfriar + cae). Su fuga
                             // literal SÍ incluye el no leer. No afirmar que vende bien.
                "Se te cayó el ritmo: dejaste de leer el análisis que te marca a quién seguir, tienes clientes que abrieron y se enfriaron, y el marcador viene a la baja. El pipeline se alimenta a diario o se seca. Vuelve a la rutina: abre el análisis en la mañana y dale un toque a cada cliente que ya te vio antes de que se enfríe.",
                "Andas en automático: ni revisas el análisis ni retomas a los que ya abrieron, y el marcador viene cayendo. Un vendedor a media máquina rinde menos que uno a full. Retoma la disciplina diaria —el análisis cada día, un toque a cada tibio— antes de que el mes se caiga más.",
                "Bajaste el ritmo: dejaste de leer y varios tibios sin retomar, con la tendencia a la baja. Lo primero es volver a aparecer todos los días con tus clientes: revisa el análisis a diario y no dejes dormir a nadie que ya te vio. Sobre esa base, lo demás se acomoda.",
            ],
            'teatro' => [ // bloqueo = miedo al «no». Técnica: pregunta que da permiso de decir no (script), distinta al cierre asumido del rematador
                "Tu freno es el miedo al «no», por eso te escondes en marcar y seguir. Pero un «no» te libera para el que sí. Facilítatelo con una pregunta que se conteste sin incomodar: a tu más caliente, «¿ya lo descartó o sigue en la mesa?». El que iba a decir no te lo dice hoy y sueltas ese tiempo; el que sigue, avanza.",
                "Marcar señales es lo cómodo; pedir la venta es lo que evitas porque expone a un rechazo. Quítale el peso pidiendo permiso para el no: «le hablo para cerrar el tema, no para presionar — ¿le entramos o de plano no es momento?». Te da una respuesta clara sin que sientas que estás forzando.",
                "Gastas energía en seguir a clientes que no te han dicho nada, y el miedo es al «no». Pero el que no pide, no pierde la venta: nunca la tuvo. Hoy, a tu más caliente, de frente: «para no dejarlo en el aire, ¿lo cerramos esta semana o lo dejo por ahora?». Un sí o un no, los dos te sirven.",
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
            'cerrador_desperdiciado' => [ // cierra fuerte (C alto); único margen = tibios que se enfrían (H bajo). Tono: felicita + upside, NO regaño
                "Eres de los que cierran lo que tocan — esa es tu fuerza y ya la tienes. El único margen que te queda es fácil: algunos que ya habían mostrado interés se están enfriando antes de que los retomes. No es un problema, es dinero esperándote. Regrésales con un pretexto nuevo: «salió algo que le queda justo a lo que buscaba, ¿lo retomamos?». Ya hiciste lo difícil.",
                "Tu cierre está sólido, no se toca. Lo único que te falta para redondear está arriba del embudo: hay tibios que se enfrían solos porque no alcanzas a retomarlos a tiempo. Aparta un rato hoy para reactivarlos con novedad (no con «sigo pendiente») — son las ventas más baratas que vas a hacer esta semana.",
                "El que ya abrió y se enfrió no está perdido, está dormido — y tú cierras lo que trabajas. Despiértalo con un motivo nuevo para volver: traer uno de vuelta te cuesta menos que conseguir uno de cero, y con tu cierre casi siempre paga. Es la palanca que te falta, y es la más sencilla.",
            ],
            'sordo_a_senales' => [ // cierra bien pero NO capitaliza las señales calientes
                "Cierras a los que ya vienen convencidos, pero ignoras las señales: cuando un cliente revisa tu propuesta con interés, el sistema te lo marca y no lo trabajas. Ahí se te escapan los cierres más fáciles. Cada mañana revisa quién se puso caliente y contáctalo el mismo día: «vi que estuvo revisando la propuesta, ¿qué duda le resuelvo para que decida?». El que ya te levantó la mano está a un paso.",
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
                "No traes fugas ni una fortaleza que jale — estás parejo, y lo parejo no destaca. Elige tu palanca más rentable, casi siempre el cierre: esta semana, a cada propuesta que ya abrieron, ponle una fecha de decisión en vez de dejarla abierta. Un solo hábito afilado mueve más que cinco a medias.",
                "Estás en tierra de nadie, ni mal ni bien. No repartas esfuerzo en todo: escoge el cierre y aprieta ahí una semana — a los que ya abrieron, pídeles un sí o un no claro, no un «lo pienso». Lo que se afila destaca; lo parejo, no.",
                "La meseta se rompe con foco, no con esfuerzo repartido. Toma la etapa donde una mejora chica rinde más —normalmente pedir la decisión a tiempo— y vuélvela tu costumbre esta semana: a cada propuesta abierta, un siguiente paso con fecha. Una cosa bien hecha vale más que cinco a medias.",
            ],
        ];
        // ── FACT-GATES (auditoría 11 jul 2026) ──────────────────
        // Ninguna frase afirma una dirección o un hecho que la MISMA tarjeta
        // contradice (la flecha ↑↓ del header sale de momentum; los chips de
        // dormidas/feedback salen de los mismos campos). Mismo espíritu que
        // el fact-lint de la Mesa: si el hecho no está, la frase no sale.
        // Estrictos en la frontera: el header pinta ↓ con mom<=0.95 y ↑ con
        // mom>=1.05 — en el valor exacto la frase no debe contradecir la flecha
        $g_baja   = $m['mom'] <= 0.95;   // puede afirmar "tendencia/marcador a la baja"
        $g_alza   = $m['mom'] > 0.95;    // puede afirmar "van ganando / mes ganado"
        $g_tips   = $m['tips_s'] < 0.5;  // puede afirmar "dejaste de leer / ni revisas"
        $g_dorm   = $m['dorm'] >= 1;     // puede afirmar "tibios / se enfriaron / juntando polvo"
        $g_dorm2  = $m['dorm'] >= 2;     // "varios tibios"
        $g_cayo   = $m['mom'] < 1.05;    // "te apagaste" (pretérito de caída, no de recuperación)
        $cob      = $m['cal'] > 0 ? $m['exp'] / $m['cal'] : 1.0;
        $g_ignora = $cob < 0.7;          // "las ignoras / no lo trabajas"

        // sin_ritmo: cada variante afirma tendencia-baja + no-lee + dormidas
        $sr = [];
        if ($g_baja && $g_tips && $g_dorm)  { $sr[] = $V['sin_ritmo'][0]; $sr[] = $V['sin_ritmo'][1]; }
        if ($g_baja && $g_tips && $g_dorm2) { $sr[] = $V['sin_ritmo'][2]; }
        if (!$sr) {
            // Fallback neutro: SOLO hechos verificados individualmente, sin
            // afirmar dirección global (el header puede estar diciendo ↑)
            $fx = [];
            if ($m['tips_s'] < 1.0) $fx[] = 'no estás leyendo el análisis a diario';
            // Observable: el CLIENTE no regresó en 7+ días (no si tú lo retomaste)
            if ($m['dorm'] >= 1)    $fx[] = ($m['dorm'] === 1
                ? 'traes un cliente que abrió y no ha regresado en 7+ días'
                : "traes {$m['dorm']} clientes que abrieron y no han regresado en 7+ días");
            if (!$fx && $m['nab'] >= 1) $fx[] = self::_pl($m['nab'], 'propuesta lleva', 'propuestas llevan') . ' 5+ días sin abrirse';
            $sr[] = 'Tu base diaria trae fugas' . ($fx ? ': ' . implode(' y ', $fx) : '')
                  . '. La rutina lo arregla: el análisis en la mañana y un toque a cada cliente que ya te vio. Sobre esa base, lo demás se acomoda.';
        }
        $V['sin_ritmo'] = $sr;

        // desconectado v2 dice "te apagaste" (caída reciente) — con ↑ es recuperación
        if (!$g_cayo) $V['desconectado'] = [$V['desconectado'][0], $V['desconectado'][2]];

        // sembrador v1/v3 afirman abandono ("dejas tirado / juntando polvo") — exige dormidas
        if (!$g_dorm) $V['sembrador'] = [$V['sembrador'][1]];
        // Con CERO aperturas, "los que ya vieron tu propuesta" es falso — el
        // problema es la entrega, no la cosecha
        if ($m['vist'] === 0) $V['sembrador'] = [
            'Cotizas pero nadie ha abierto tu propuesta todavía. El problema no es tu cartera, es la entrega: reenvía por WhatsApp con un mensaje personal («te mandé la propuesta, ¿la pudiste ver?») — una cotización que no se abre no existe.',
        ];

        // sordo_a_senales: "las ignoras" exige cobertura baja; si responde a todas,
        // la fuga es de LECTURA (calidad del juicio), no de atención
        if (!$g_ignora) $V['sordo_a_senales'] = [
            'Respondes a las señales del Radar, pero tu lectura del cliente está fallando: marcas interés donde no lo hay o descartas al que sí iba. Antes de calificar, valida con una pregunta directa al cliente — la marca vale por lo que aciertas, no por darla.',
        ];

        // motor_completo v3 presume racha ("van ganando / mes ganado") — exige momentum sano
        if (!$g_alza) $V['motor_completo'] = [$V['motor_completo'][0], $V['motor_completo'][1]];

        // Con la mesa activa, la jugada de señales es "abre tu mesa" (la mesa
        // ya forma las señales solas — mandar al Radar sería la jugada vieja)
        if (!empty($m['mesa_on'])) {
            $V['sordo_a_senales'] = array_map(
                fn($f) => str_replace(
                    ['Cada mañana revisa quién se puso caliente y contáctalo el mismo día',
                     'Empieza el día por los calientes de hoy'],
                    ['Cada mañana abre tu mesa y dale un toque al que se puso caliente el mismo día',
                     'Empieza el día por lo 🔥 de tu mesa'],
                    $f),
                $V['sordo_a_senales']
            );
        }

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
            $donde = !empty($m['mesa_on']) ? 'en tu mesa' : 'en el Radar';
            $op .= ' ' . ($sin_expl === 1
                ? "Y si ya tienes 1 caliente {$donde}, atiéndela hoy — no dejes ir lo poco activo que tienes."
                : "Y si ya tienes {$sin_expl} calientes {$donde}, atiéndelas hoy — no dejes ir lo poco activo que tienes.");
        }
        return $op;
    }
}
