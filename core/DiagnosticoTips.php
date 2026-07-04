<?php
// ============================================================
//  core/DiagnosticoTips.php
//  Tips del termómetro en voz de gerente comercial — TÁCTICOS.
//
//  MODELO: RED de las 5 dimensiones. La conversión NO es una dimensión
//  suelta, es el RESULTADO del proceso. Las otras 4 son el proceso:
//
//     Activación  →  Seguimiento  →  Radar Health  →  Engagement  →  CIERRE
//     (llenas el    (trabajas lo    (no dejas       (cierras       (resultado)
//      tubo)         que entró)      enfriar)        limpio)
//
//  Diagnosticar = LOCALIZAR el primer eslabón roto de la cadena, y leer
//  el cierre a la luz de lo que hay arriba. El mismo "no vende" significa
//  cosas OPUESTAS según las otras barras:
//    · activación baja + no vende  = piloto automático (cotiza y reza) →
//      el % de cierre es ruido; el problema es volumen/velocidad, no técnica.
//    · todo arriba bien + no vende = la CONVERSACIÓN falla → discovery,
//      objeción no dicha, pedir la decisión. Trabajar más no lo arregla.
//
//  Principio rector (respaldado por evidencia de ventas):
//    proceso > talento ·  diagnóstico > persistencia.
//    Se coachea el CUELLO DE BOTELLA, nunca la fortaleza.
//    UNA jugada concreta con script, no una lista de definiciones.
//
//  El TIER (score) solo modula el TONO y la presión final, no el diagnóstico.
//  PURO: no toca BD. Recibe $s (score) y $ctx. Testeable en aislamiento.
// ============================================================

defined('COTIZAAPP') or die;

final class DiagnosticoTips
{
    // Umbrales de las barras (0-1) — las mismas que se pintan en el termómetro.
    private const BAJO = 0.45;
    private const ALTO = 0.66;

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

        $tier = self::_tier($m['score']);
        $seed = ((int)($s['usuario_id'] ?? 0)) + (int)date('z');

        return self::_componer($m, $tier, $seed);
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
            // Las 5 dimensiones (0-1) — las barras del termómetro.
            's_act'      => (float)($s['s_activacion']  ?? 0.5),
            's_eng'      => (float)($s['s_engagement']  ?? 0.5),
            's_seg'      => (float)($s['s_seguimiento'] ?? 0.5),
            's_hlt'      => (float)($s['s_radar_health']?? 0.5),
            's_conv'     => (float)($s['s_conversion']  ?? 0.5),
            // Promedios móviles (EMA) de períodos anteriores — para leer
            // TENDENCIA (sube/baja/estable) comparando la barra actual vs su EMA.
            // Solo act/seg/conv tienen EMA viva; -1 = sin dato (usa momentum).
            'ema_act'    => isset($s['ema_activacion'])   ? (float)$s['ema_activacion']   : -1.0,
            'ema_seg'    => isset($s['ema_seguimiento'])  ? (float)$s['ema_seguimiento']  : -1.0,
            'ema_conv'   => isset($s['ema_conversion'])   ? (float)$s['ema_conversion']   : -1.0,
            'ema_eng'    => isset($s['ema_engagement'])   ? (float)$s['ema_engagement']   : -1.0,
            'ema_hlt'    => isset($s['ema_radar_health']) ? (float)$s['ema_radar_health'] : -1.0,
        ];
    }

    private static function _tier(int $score): string
    {
        if ($score >= 85) return 'top';
        if ($score >= 70) return 'activo';
        if ($score >= 45) return 'regular';
        return 'bajo';
    }

    // ════════════════════════════════════════════════════════
    //  LA RED — localiza el arquetipo por la MEZCLA de las 5 barras.
    //  Camina el embudo de arriba hacia abajo y se detiene en el
    //  primer eslabón roto. El cierre se lee a la luz de lo de arriba.
    // ════════════════════════════════════════════════════════

    private static function _arquetipo(array $m): string
    {
        $B = self::BAJO; $A = self::ALTO;
        $act = $m['s_act']; $eng = $m['s_eng']; $seg = $m['s_seg'];
        $hlt = $m['s_hlt']; $conv = $m['s_conv'];

        // Muestra chica: con muy pocas propuestas el % de cierre es ruido.
        if ($m['asig'] < 6 && $m['cierres'] === 0) return 'muestra_chica';

        // Todo abajo: el proceso no arranca. No es técnica, son fundamentos.
        $bajos = (int)($act < $B) + (int)($seg < $B) + (int)($hlt < $B)
               + (int)($eng < $B) + (int)($conv < $B);
        if ($bajos >= 4) return 'desenganchado';

        // 1) ARRANQUE — activación es el cuello (tope del embudo).
        //    Incluye no leer los tips ni abrir el ❓ (son parte de activación).
        if ($act < $B) {
            // A baja + C alto = francotirador: cierra lo poco que trabaja.
            if ($conv >= $A && $m['cierres'] >= 1) return 'francotirador';
            return 'arranque';
        }

        // 2) SEGUIMIENTO — llena el tubo pero no trabaja lo que entró.
        if ($seg < $B) return 'emisor_ciego';

        // 3) RADAR HEALTH — reacciona a lo urgente pero deja enfriar el resto.
        if ($hlt < $B) return 'enfriador';

        // 4) CIERRE — trabaja bien arriba y aun así no vende: la
        //    conversación de venta es la fuga (discovery / objeción / pedir).
        if ($conv < $B) return 'no_cierra';

        // 5) ENGAGEMENT — cierra pero regalando margen o sin cobrar.
        if ($eng < $B) return 'regalador';

        // Sin fuga marcada: zona pareja, sin filo.
        return 'estancado';
    }

    // ════════════════════════════════════════════════════════
    //  COMPOSICIÓN
    // ════════════════════════════════════════════════════════

    private static function _componer(array $m, string $tier, int $seed): string
    {
        if ($tier === 'top') return self::_render_top($m, $seed);

        $arq = self::_arquetipo($m);

        if ($arq === 'muestra_chica') return self::_render_muestra_chica($m, $seed);

        $partes = [];
        $partes[] = self::_diag($arq, $m, $seed);   // qué significa la mezcla

        // TENDENCIA: ¿el cuello sube, baja o está estancado? (dato vivo, cambia
        // el tono: refuerzo si mejora, urgencia si cae, empujón si estancado).
        $tr = self::_trend_clause($arq, $m, $seed);
        if ($tr !== '') $partes[] = $tr;

        $partes[] = self::_jugada($arq, $m, $seed); // LA jugada concreta

        $merito = self::_merito($arq, $m);           // mérito factual, si aplica
        if ($merito !== '') $partes[] = $merito;

        if ($tier === 'bajo') {
            $partes[] = self::_consecuencia_bajo($seed);
        }

        return implode(' ', $partes);
    }

    // ── DIAGNÓSTICO: lo que la MEZCLA significa (no una definición) ──
    private static function _diag(string $arq, array $m, int $seed): string
    {
        switch ($arq) {

            case 'desenganchado':
                return self::_pick([
                    "Los cinco frentes están abajo a la vez: esto no es un detalle de técnica, es que el proceso todavía no arranca. Vamos por partes, no todo junto.",
                    "Ahorita todo está en rojo al mismo tiempo. No hay una sola cosa que arreglar, hay que construir la rutina desde el primer escalón.",
                ], $seed + 1);

            case 'arranque': {
                $stat = self::_stat_arranque($m);
                $base = self::_pick([
                    "Tu cuello está en el arranque{$stat}: lo que hagas después no importa si la propuesta no llega a los ojos del cliente a tiempo.",
                    "La fuga es el primer escalón{$stat}: el cliente evalúa cuando pide el precio, y si la cotización llega tarde o no la ve, no hay nada que perseguir.",
                ], $seed + 1);
                // Si además no cierra, aclarar que el % es ruido (mezcla A bajo + C bajo).
                if ($m['s_conv'] < self::BAJO) {
                    $base .= " Y como cotizas poco, tu porcentaje de cierre todavía no significa nada — el problema hoy es volumen y velocidad, no cómo rematas.";
                }
                return $base;
            }

            case 'francotirador':
                return self::_pick([
                    "Cierras casi todo lo que trabajas — el talento para rematar ya lo tienes. Lo que te frena es que trabajas poco: con más tiros, cierras más. Es aritmética, no técnica.",
                    "Tu remate es bueno; tu volumen no. Estás desperdiciando una máquina de cerrar que funciona por darle pocas propuestas.",
                ], $seed + 1);

            case 'emisor_ciego': {
                $stat = self::_stat_seg($m);
                return self::_pick([
                    "Llenas el tubo pero no trabajas lo que entra{$stat}: disparas cotizaciones y te olvidas. Casi ninguna venta se cierra al primer toque — viven en el seguimiento, justo donde la mayoría suelta.",
                    "Mandas bien, pero abandonas después de enviar{$stat}. El cliente rara vez compra de una; el que da seguimiento ordenado se lleva la venta que el que envía y olvida deja tirada.",
                ], $seed + 1);
            }

            case 'enfriador': {
                $stat = self::_stat_hlt($m);
                return self::_pick([
                    "Atiendes lo urgente pero dejas enfriar el resto del pipeline{$stat}. Trabajas por impulso —lo que grita— y abandonas al tibio, que casi nunca es mal cliente, solo desatendido.",
                    "Reaccionas a lo caliente y sueltas lo demás{$stat}: el interesado que no te apura se te enfría en silencio. Eso es dinero que se evapora sin ruido.",
                ], $seed + 1);
            }

            case 'no_cierra': {
                $stat = self::_stat_cierre($m);
                return self::_pick([
                    "Aquí está la clave: trabajas bien —mandas, sigues, atiendes el Radar— y aun así no cierras{$stat}. Cuando el esfuerzo está arriba y el resultado abajo, el problema ya no es trabajar más, es la conversación de venta.",
                    "Todo lo de arriba lo haces bien y la venta no baja{$stat}. Eso descarta el esfuerzo: la fuga está dentro de la plática con el cliente, no en cuántas propuestas mandas.",
                ], $seed + 1);
            }

            case 'regalador': {
                $stat = self::_stat_eng($m);
                return self::_pick([
                    "Cierras, pero regalando{$stat}: el número se ve bien y el margen no. Un descuento chico se come una tajada de la utilidad mucho más grande de lo que parece.",
                    "Sí vendes, pero el cierre te sale caro{$stat}. Bajar el precio para cerrar te entrena a regalar, y el cliente aprende a pedir más la próxima.",
                ], $seed + 1);
            }

            case 'estancado':
            default:
                return self::_pick([
                    "Sin fugas graves pero sin filo: estás en zona pareja en los cinco frentes. Para subir no necesitas arreglar nada, necesitas afilar una cosa.",
                    "Nada roto, nada que brille. Estás estable; el salto sale de profundizar, no de corregir.",
                ], $seed + 1);
        }
    }

    // ── LA JUGADA: táctica concreta con script (misma fuente que el diag) ──
    private static function _jugada(string $arq, array $m, int $seed): string
    {
        switch ($arq) {

            case 'desenganchado':
                return self::_pick([
                    "Un paso a la vez, en orden. Primero lo básico: manda tus cotizaciones el mismo día que te las piden y confírmale al cliente que le llegaron —«le acabo de enviar su cotización, ¿le llegó bien?»—. Cuando eso sea rutina diaria, seguimos con el seguimiento. No intentes los cinco frentes juntos.",
                    "Arranca por el cimiento: cotización el mismo día, siempre, y un mensaje personal para confirmar que la vio. Domina eso una semana antes de pensar en cierre. El sistema te marca a quién seguir cada día en estos tips y en el ❓ del Radar — úsalos, ahí está tu lista de trabajo.",
                ], $seed + 2);

            case 'arranque': {
                $j = self::_pick([
                    "La cotización vale cuando el cliente aún tiene la intención caliente: mándala el mismo día y avísale por el canal que sí usa —WhatsApp, una llamada— con una línea personal: «le acabo de mandar su cotización de [lo suyo], ¿le llegó bien?». Una enviada a tiempo y confirmada vale por diez perdidas en la bandeja.",
                    "Deja de mandar en silencio. Cada propuesta sale con un aviso directo para forzar la apertura, y las que nadie abrió se rescatan a las 24-48h: «quería asegurarme de que le llegó, a veces se va a spam, ¿la pudo ver?». Primero que la vean; sin eso no hay venta que perseguir.",
                ], $seed + 2);
                if ($m['tips_s'] <= 0.0 && $m['dias_act'] > 0) {
                    $j .= " Y hoy no estás usando lo que ya tienes: el sistema te dice en estos tips y en el ❓ del Radar a quién llamar y por qué. Es tu lista diaria, gratis — no un adorno.";
                }
                return $j;
            }

            case 'francotirador':
                return self::_pick([
                    "No cambies nada de cómo cierras — funciona. Solo dale volumen: ponte una meta diaria de cotizaciones nuevas y bloquea una hora fija para prospectar. Cada propuesta extra, con tu tasa, es casi dinero seguro.",
                    "Tu única palanca es llenar el embudo. Fija un número de propuestas por día y respétalo como cita. La máquina de cerrar ya la tienes; solo le falta con qué trabajar.",
                ], $seed + 2);

            case 'emisor_ciego': {
                $j = self::_pick([
                    "El Radar te marca quién abrió y volvió a abrir — esa es tu venta más cercana del día. Reacciona el mismo día, y no preguntes «¿ya la vio?» (ya sabes que sí): ve directo a resolver — «vi que estuvo revisando la propuesta, ¿qué duda le aclaro para que decida?».",
                    "Dale a cada cotización una fecha de siguiente contacto y no la sueltes tras enviarla: día 1 confirmas, día 3 aportas algo útil, día 7 otro ángulo. El que insiste con orden se lleva la venta que el que manda-y-olvida deja tirada.",
                ], $seed + 2);
                $t = self::_target($m);
                if ($t !== '') $j .= ' ' . $t;
                return $j;
            }

            case 'enfriador':
                return self::_pick([
                    "Prioriza por temperatura, no por antigüedad, y no dejes ningún cliente vivo sin próximo paso agendado. Al que iba subiendo reactívalo con un motivo nuevo —una opción de pago, un dato, una duda resuelta—, nunca con un «seguimos en contacto» que no dice nada.",
                    "Tu regla diaria: revisa el Radar y rescata al tibio antes de que se pase. Un ángulo fresco reabre —«me acordé de usted, ya tenemos [algo nuevo], ¿sigue en pie lo suyo?»—. Hoy todavía te recuerdan; en una semana, no.",
                ], $seed + 2);

            case 'no_cierra': {
                // La ciencia del cierre: discovery, objeción no dicha, calificar,
                // y pedir la decisión. NO "insiste más".
                $j = self::_pick([
                    "No vendes el producto, haces visible lo que le cuesta NO resolver su problema: «si sigue con esto otros meses, ¿qué le representa?». El cliente que dimensiona su propio dolor se vende solo la urgencia — eso mueve el cierre, no otra cotización.",
                    "El que abre y no compra casi siempre tiene una objeción que no te dijo. Sácala tú antes de que mate el trato: «antes de cerrar, ¿qué le impediría avanzar hoy?». No se resuelve mandando otro PDF, se resuelve en una llamada.",
                    "Puede que estés persiguiendo a quien nunca iba a comprar. Califica temprano para descartar rápido: «¿esto lo quiere resolver este mes o de momento está viendo precios?». Soltar al que no compra te libera para el que sí.",
                    "Muchas ventas se caen porque nadie pidió la orden. Cuando ya hay interés, deja de preguntar «¿le interesa?» —ya lo sabes— y avanza al siguiente paso: «¿se lo agendamos para esta semana o la próxima?».",
                ], $seed + 2);
                $t = self::_target($m);
                if ($t !== '') $j .= ' ' . $t;
                return $j;
            }

            case 'regalador':
                return self::_pick([
                    "Cuando pidan descuento no bajes el número de inmediato: pregunta «¿con qué lo está comparando?». Muchas veces no es precio, es que no ve el valor. Si tienes que ceder, cede en plazo o alcance —«¿ajustamos el alcance o lo dejamos completo?»—, no en el precio.",
                    "Amarra el cierre con un anticipo: «para apartar y arrancar hoy dejamos un anticipo y el resto contra entrega». Quien pone dinero ya decidió; un sí sin anticipo se enfría. Y una venta sin cobrar no es venta hasta que entra el dinero — ponle fecha de pago concreta y no la sueltes.",
                ], $seed + 2);

            case 'estancado':
            default:
                return self::_pick([
                    "Aprieta una sola tuerca esta semana: en cada trato, una pregunta de implicación —«¿qué le cuesta seguir sin resolver esto?»—. Es el hábito que más mueve el cierre sin trabajar más horas.",
                    "Elige una palanca y clávala: reacciona el mismo día a todo lo que el Radar marque caliente. Una cosa bien hecha te saca de estable y te sube de nivel.",
                ], $seed + 2);
        }
    }

    // ── Mérito FACTUAL (sin pep-talk). Solo cuando es cierto y relevante. ──
    private static function _merito(string $arq, array $m): string
    {
        // Para el que trabaja bien arriba: dejar claro que NO es su fuga.
        if ($arq === 'no_cierra' || $arq === 'regalador') {
            return "El arranque y el seguimiento no son tu problema — enfócate solo en esto.";
        }
        if ($m['bcierre'] >= 8) {
            return "A tu favor, y es real: tu cierre va muy por encima de tu propio histórico.";
        }
        if ($m['bcierre'] >= 4) {
            return "Un dato a tu favor: estás cerrando por encima de tu histórico reciente.";
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

    // ── TENDENCIA (sube/baja/estable) ───────────────────────
    // Dirección de UNA dimensión: barra actual vs su EMA (promedio reciente).
    private static function _dir(float $cur, float $ema): string
    {
        if ($ema < 0) return 'na';           // sin EMA guardada
        $d = $cur - $ema;
        if ($d >= 0.05) return 'sube';
        if ($d <= -0.05) return 'baja';
        return 'estable';
    }
    // Dirección global por momentum (para dims sin EMA: engagement, radar health).
    private static function _dir_global(array $m): string
    {
        if ($m['mom'] >= 1.10) return 'sube';
        if ($m['mom'] <= 0.90) return 'baja';
        return 'estable';
    }

    /** Cláusula de tendencia del CUELLO del arquetipo. Cambia el tono. */
    private static function _trend_clause(string $arq, array $m, int $seed): string
    {
        // ¿Qué dimensión es el cuello y tengo EMA para ella?
        $cual = ''; $dir = 'na';
        switch ($arq) {
            case 'arranque':
            case 'desenganchado':
                $dir = self::_dir($m['s_act'], $m['ema_act']); $cual = 'tu actividad'; break;
            case 'emisor_ciego':
                $dir = self::_dir($m['s_seg'], $m['ema_seg']); $cual = 'tu seguimiento'; break;
            case 'no_cierra':
            case 'francotirador':
                $dir = self::_dir($m['s_conv'], $m['ema_conv']); $cual = 'tu cierre'; break;
            case 'regalador':
                $dir = self::_dir($m['s_eng'], $m['ema_eng']); $cual = 'tu disciplina de cierre'; break;
            case 'enfriador':
                $dir = self::_dir($m['s_hlt'], $m['ema_hlt']); $cual = 'la salud de tu pipeline'; break;
        }
        if ($dir === 'na') { $dir = self::_dir_global($m); $cual = 'tu ritmo general'; }

        switch ($dir) {
            case 'sube':
                return self::_pick([
                    "Y hay buena señal: {$cual} ya viene subiendo respecto a tus semanas recientes — vas corrigiendo, no aflojes justo ahora.",
                    "A favor: {$cual} trae tendencia a la alza contra tu propio historial. Estás en la dirección correcta; sostenlo.",
                ], $seed + 7);
            case 'baja':
                return self::_pick([
                    "Y ojo: {$cual} viene cayendo respecto a tus semanas recientes — esto no se arregla solo, hay que frenarlo ya.",
                    "Alerta de tendencia: {$cual} va a la baja contra tu propio historial. Cada semana sin actuar lo hace más hondo.",
                ], $seed + 7);
            case 'estable':
            default:
                return self::_pick([
                    "Y lleva ahí estancado: {$cual} no se mueve solo — sin un cambio de tu parte, la próxima semana lees lo mismo.",
                    "{$cual} está plano hace semanas: no empeora, pero tampoco sube por inercia. El movimiento tiene que venir de ti.",
                ], $seed + 7);
        }
    }

    // ── A QUIÉN empezar (dato vivo del Radar) ───────────────
    private static function _target(array $m): string
    {
        if ($m['ign'] > 0) {
            return $m['ign'] === 1
                ? "Empieza por el cliente que está activo en el Radar ahora mismo: ese es el primero de la lista."
                : "Empieza por los {$m['ign']} clientes activos en el Radar ahora mismo: esos son los primeros de la lista.";
        }
        if ($m['dorm'] > 0) {
            return $m['dorm'] === 1
                ? "Arranca con el cliente que abrió y no volviste a tocar."
                : "Arranca con los {$m['dorm']} que abrieron y no volviste a tocar.";
        }
        return '';
    }

    // ── STATs por arquetipo (números reales, entre paréntesis) ──
    private static function _stat_arranque(array $m): string
    {
        $p = [];
        if ($m['sin_abrir'] > 0) $p[] = self::_pl($m['sin_abrir'], 'sin abrir', 'sin abrir');
        if ($m['dorm'] > 0)      $p[] = self::_pl($m['dorm'], 'dormida', 'dormidas');
        return empty($p) ? '' : ' (' . implode(', ', $p) . ')';
    }
    private static function _stat_seg(array $m): string
    {
        if ($m['ign'] > 0) return ' (' . self::_pl($m['ign'], 'caliente sin atender', 'calientes sin atender') . ')';
        if ($m['dorm'] > 0) return ' (' . self::_pl($m['dorm'], 'cliente sin seguimiento', 'clientes sin seguimiento') . ')';
        return '';
    }
    private static function _stat_hlt(array $m): string
    {
        return $m['h_down'] > 0 ? ' (' . self::_pl($m['h_down'], 'cliente se enfrió', 'clientes se enfriaron') . ')' : '';
    }
    private static function _stat_cierre(array $m): string
    {
        return $m['vist'] > 0
            ? " ({$m['cierres']} de {$m['vist']} — {$m['tasa_pct']}% contra {$m['bench_pct']}% de la empresa)"
            : '';
    }
    private static function _stat_eng(array $m): string
    {
        $p = [];
        if ($m['con_dto'] > 0) $p[] = self::_pl($m['con_dto'], 'con descuento', 'con descuento');
        if ($m['vsp'] > 0)     $p[] = self::_pl($m['vsp'], 'sin cobrar', 'sin cobrar');
        return empty($p) ? '' : ' (' . implode(', ', $p) . ')';
    }

    // ── TOP ─────────────────────────────────────────────────
    private static function _render_top(array $m, int $seed): string
    {
        $partes = [];
        $partes[] = self::_pick([
            "Rendimiento de primera este período — vas en el grupo de arriba.",
            "Mes fuerte: tus números están claramente sobre la media.",
        ], $seed);

        if ($m['ign'] > 0) {
            $partes[] = ($m['ign'] === 1)
                ? "Para redondearlo tienes 1 cliente caliente en el Radar sin atender. Llámalo mientras esté activo y el mes es histórico."
                : "Para redondearlo, {$m['ign']} clientes calientes en el Radar sin atender. Atiéndelos hoy, mientras están activos, y el mes es histórico.";
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
            "Apenas " . self::_pl($a, 'cotización en el período', 'cotizaciones en el período') . " — muy poco para juzgar tu cierre. Tu prioridad ahora es volumen: manda más propuestas y el termómetro empieza a leerte de verdad.",
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
