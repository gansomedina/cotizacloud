<?php
// ============================================================
//  core/DiagnosticoTips.php
//  Tips del termómetro en voz de gerente comercial — TÁCTICOS.
//
//  MODELO: RED de las 5 dimensiones + SUB-DRIVER real de cada una.
//  El diagnóstico se mapea a lo que REALMENTE compone cada barra
//  (leído de ActividadScore::calcular), no a una narrativa inventada:
//
//    ACTIVACIÓN (13%) = operativa·50% + tips_score·50%
//        operativa = tasa_apertura − pen_dormidas
//        (si hay no_abiertas_5d>0 → operativa colapsa a −pen_dormidas)
//        · sub: no_lee (mitad de la barra), no_llega (nunca la abren),
//               dormidas (abren y se enfrían), apertura (no la ven)
//    ENGAGEMENT (17%) = 1 − pen_sin_cobrar − pen_descuento − pen_bajo_benchmark
//        · sub: sin_cobrar, descuento, bajo_vol (vendes menos que el equipo)
//    SEGUIMIENTO (25%) = feedback del Radar (botones con/sin interés) ·
//        calidad · exploración del ❓
//        · sub: no_explora (no abre el ❓), feedback (no marca en calientes)
//    RADAR HEALTH (10%) = 1 − muertas/calientes (calientes que soltaste)
//    CONVERSIÓN (35%) = close_rate vs empresa + calidad + tendencia
//
//  Se camina el embudo (Act → Seg → Health → Eng → Cierre) y se para en
//  el primer eslabón roto. El cierre se lee EN CONTEXTO: activación baja +
//  no vende = el % es ruido; todo arriba bien + no vende = la conversación
//  falla (discovery, objeción no dicha, pedir la decisión).
//
//  Principio: proceso > talento · diagnóstico > persistencia. UNA jugada
//  concreta con script. Se coachea el cuello, no la fortaleza. El TIER
//  (score) solo modula el tono y la presión final, no el diagnóstico.
//
//  TENDENCIA (sube/baja/estable) por EMA de cada dimensión — cambia el tono.
//  PURO: no toca BD. Recibe $s (row de usuario_score) y $ctx.
// ============================================================

defined('COTIZAAPP') or die;

final class DiagnosticoTips
{
    private const BAJO = 0.45;   // umbral de barra "baja"
    private const ALTO = 0.66;   // umbral de barra "alta"

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
        // cots_calientes se persiste como radar_benchmark en la tabla.
        $cal     = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        return [
            'score'      => (int)($s['score'] ?? 0),
            'asig'       => $asig,
            'vist'       => $vist,
            'cierres'    => $cierres,
            'sin_abrir'  => max(0, $asig - $vist),
            'aper'       => $asig > 0 ? $vist / $asig : 0.0,
            'aper_pct'   => (int)round(($asig > 0 ? $vist / $asig : 0.0) * 100),
            'dorm'       => (int)($s['cot_dormidas'] ?? 0),
            'nab'        => (int)($s['no_abiertas_5d'] ?? 0),
            'cal'        => $cal,
            'exp'        => (int)($s['calientes_exploradas'] ?? 0),
            'why'        => (float)($s['radar_why_score'] ?? 1.0),
            'vsp'        => (int)($s['ventas_sin_pago'] ?? 0),
            'vper'       => (int)($s['ventas_periodo'] ?? 0),
            'bventas'    => (float)($s['bench_ventas'] ?? 0),
            'mom'        => (float)($s['momentum'] ?? 1),
            'bench'      => $bench,
            'bench_pct'  => (int)round($bench * 100),
            'tasa'       => $tasa,
            'tasa_pct'   => (int)round($tasa * 100),
            // Radar Health: transiciones_up=calientes, senales_ignoradas=muertas.
            'h_up'       => (int)($s['health_up'] ?? $s['transiciones_up'] ?? 0),
            'h_down'     => (int)($s['health_down'] ?? $s['senales_ignoradas'] ?? 0),
            'tips_s'     => (float)($s['tips_score'] ?? 1),
            'dias_act'   => (int)($s['dias_activos'] ?? 0),
            'bcierre'    => (int)($s['bonus_cierre'] ?? 0),
            // Penalizaciones de Engagement (directas de la tabla).
            'eps'        => (float)($s['eng_pen_sin_pago'] ?? 0),
            'epd'        => (float)($s['eng_pen_descuento'] ?? 0),
            'epb'        => (float)($s['eng_pen_bajo_benchmark'] ?? 0),
            // Las 5 barras del termómetro.
            's_act'      => (float)($s['s_activacion']  ?? 0.5),
            's_eng'      => (float)($s['s_engagement']  ?? 0.5),
            's_seg'      => (float)($s['s_seguimiento'] ?? 0.5),
            's_hlt'      => (float)($s['s_radar_health']?? 0.5),
            's_conv'     => (float)($s['s_conversion']  ?? 0.5),
            // EMAs para TENDENCIA (-1 = sin dato → usa momentum).
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
    //  LA RED — dimensión cuello (camina el embudo, primer eslabón roto).
    // ════════════════════════════════════════════════════════

    private static function _arquetipo(array $m): string
    {
        $B = self::BAJO; $A = self::ALTO;
        $act = $m['s_act']; $eng = $m['s_eng']; $seg = $m['s_seg'];
        $hlt = $m['s_hlt']; $conv = $m['s_conv'];

        if ($m['asig'] < 6 && $m['cierres'] === 0) return 'muestra_chica';

        $bajos = (int)($act < $B) + (int)($seg < $B) + (int)($hlt < $B)
               + (int)($eng < $B) + (int)($conv < $B);
        if ($bajos >= 4) return 'desenganchado';

        if ($act < $B) {
            if ($conv >= $A && $m['cierres'] >= 1) return 'francotirador';
            return 'act';
        }
        if ($seg < $B) return 'seg';
        if ($hlt < $B) return 'hlt';
        if ($conv < $B) return 'conv';
        if ($eng < $B) return 'eng';
        return 'estancado';
    }

    // ── SUB-DRIVER: qué compone realmente la barra baja ─────
    private static function _subdriver(string $arq, array $m): string
    {
        switch ($arq) {
            case 'act': {
                // Ganancia marginal de cada arreglo sobre la barra (operativa=50%, tips=50%).
                $pen_d = min($m['dorm'] / max($m['vist'], 1), 1.0);
                $g = [
                    'no_llega' => $m['nab'] > 0 ? $m['aper'] * 0.5 : 0.0,   // nunca la abren → colapsa operativa
                    'no_lee'   => (1.0 - $m['tips_s']) * 0.5,               // mitad de la barra
                    'dormidas' => $pen_d * 0.5,                             // abren y se enfrían
                    'apertura' => $m['nab'] > 0 ? 0.0 : (1.0 - $m['aper']) * 0.5,
                ];
                arsort($g);
                return array_key_first($g);
            }
            case 'eng': {
                $g = ['sin_cobrar' => $m['eps'], 'descuento' => $m['epd'], 'bajo_vol' => $m['epb']];
                arsort($g);
                return array_key_first($g);
            }
            case 'seg':
                // Señal fiable: exploración del ❓. Si no explora sus calientes → ese.
                if ($m['cal'] > 0 && ($m['exp'] / max($m['cal'], 1)) < 0.5) return 'no_explora';
                return 'feedback';
            default:
                return '';
        }
    }

    // ════════════════════════════════════════════════════════
    //  COMPOSICIÓN
    // ════════════════════════════════════════════════════════

    private static function _componer(array $m, string $tier, int $seed): string
    {
        if ($tier === 'top') return self::_render_top($m, $seed);

        $arq = self::_arquetipo($m);
        if ($arq === 'muestra_chica') return self::_render_muestra_chica($m, $seed);

        $sd = self::_subdriver($arq, $m);

        $partes = [];
        $partes[] = self::_diag($arq, $sd, $m, $seed);

        $tr = self::_trend_clause($arq, $m, $seed);
        if ($tr !== '') $partes[] = $tr;

        $partes[] = self::_jugada($arq, $sd, $m, $seed);

        $merito = self::_merito($arq, $m);
        if ($merito !== '') $partes[] = $merito;

        if ($tier === 'bajo') $partes[] = self::_consecuencia_bajo($seed);

        return implode(' ', $partes);
    }

    // ── DIAGNÓSTICO: lo que la mezcla + sub-driver significan ──
    private static function _diag(string $arq, string $sd, array $m, int $seed): string
    {
        switch ($arq) {

            case 'desenganchado':
                return self::_pick([
                    "Los cinco frentes están abajo a la vez: esto no es un detalle de técnica, es que el proceso todavía no arranca. Vamos por partes, no todo junto.",
                    "Ahorita todo está en rojo al mismo tiempo. No hay una sola cosa que arreglar, hay que construir la rutina desde el primer escalón.",
                ], $seed + 1);

            case 'act':
                switch ($sd) {
                    case 'no_lee':
                        return self::_pick([
                            "Tus cotizaciones llegan y se abren bien ({$m['aper_pct']}%), así que el envío no es tu problema. La mitad de esta barra es leer estos tips y abrir el ❓ del Radar — y hoy no lo haces. Te estás perdiendo, gratis, la lista de a quién llamar y por qué.",
                            "El arranque operativo lo tienes ({$m['aper_pct']}% se abren), pero media barra de activación es usar el sistema: leer el análisis y explorar el ❓. Ignorarlo es pelear a ciegas teniendo el mapa enfrente.",
                        ], $seed + 1);
                    case 'no_llega':
                        return self::_pick([
                            "Tienes " . self::_pl($m['nab'], 'cotización', 'cotizaciones') . " con 5+ días que nadie ha abierto: no llegaron o se perdieron en la bandeja. El cliente evalúa cuando pide el precio; si no la ve, no hay venta que perseguir.",
                            self::_pl($m['nab'], 'propuesta', 'propuestas') . " llevan días sin que el cliente las abra. Eso mata tu activación de raíz — todo lo demás da igual si la propuesta no llega a sus ojos.",
                        ], $seed + 1);
                    case 'dormidas':
                        return self::_pick([
                            "Tus propuestas se abren bien, pero " . self::_pl($m['dorm'], 'se abrió', 'se abrieron') . " y el cliente no volvió: se enfriaron sin que las retomaras. Eso no es envío, es seguimiento — el cliente vio y esperó un empujón que no llegó.",
                            self::_pl($m['dorm'], 'cliente abrió y desapareció', 'clientes abrieron y desaparecieron') . " sin que los volvieras a tocar. Abrir sin dar seguimiento es dejar la venta a medio camino.",
                        ], $seed + 1);
                    case 'apertura':
                    default:
                        return self::_pick([
                            "Solo {$m['aper_pct']}% de tus propuestas se abren: llegan pero no las ven. El problema está en el canal o en el gancho con que las mandas.",
                            "Tu apertura está baja ({$m['aper_pct']}%). Mandas, pero el cliente no llega a abrir — hay que cambiar cómo y por dónde se la haces llegar.",
                        ], $seed + 1);
                }

            case 'francotirador':
                return self::_pick([
                    "Cierras casi todo lo que trabajas — el talento para rematar ya lo tienes. Lo que te frena es que trabajas poco: con más tiros, cierras más. Es aritmética, no técnica.",
                    "Tu remate es bueno; tu volumen no. Estás desperdiciando una máquina de cerrar que funciona por darle pocas propuestas.",
                ], $seed + 1);

            case 'seg':
                if ($sd === 'no_explora') {
                    return self::_pick([
                        "Tuviste " . self::_pl($m['cal'], 'cotización caliente', 'cotizaciones calientes') . " y casi no abres el ❓ del Radar en ellas ({$m['exp']} de {$m['cal']}). Ahí ves QUÉ disparó la señal —revisó el precio, volvió varias veces, la vieron desde varios lados— y llegas a la llamada sabiendo qué objetar.",
                        "El Radar te marcó " . self::_pl($m['cal'], 'oportunidad caliente', 'oportunidades calientes') . " y no exploras el ❓ que te dice por qué. Estás atendiendo a ciegas lo que el sistema ya te explicó.",
                    ], $seed + 1);
                }
                return self::_pick([
                    "El seguimiento aquí se mide por el feedback del Radar en tus " . self::_pl($m['cal'], 'cotización caliente', 'cotizaciones calientes') . ": marcar «con interés / sin interés» y atenderlas. No lo estás cerrando — y una señal caliente sin respuesta se enfría en horas.",
                    "Tus calientes están quedando sin feedback ni atención. El Radar te dice quién está en modo compra; ignorar esa señal es regalar la venta más cercana que tienes.",
                ], $seed + 1);

            case 'hlt':
                return self::_pick([
                    self::_pl($m['h_down'], 'cliente que estaba caliente perdió el interés', 'clientes que estaban calientes perdieron el interés') . " por completo (de {$m['h_up']}): los dejaste morir sin cerrar. Un caliente que muere casi nunca es mal cliente — es desatención.",
                    "De " . self::_pl($m['h_up'], 'cliente caliente', 'clientes calientes') . ", " . ($m['h_down'] === 1 ? 'uno se apagó' : "{$m['h_down']} se apagaron") . " sin que hicieras nada. Eso es pipeline muriéndose en silencio.",
                ], $seed + 1);

            case 'conv': {
                $stat = $m['vist'] > 0 ? " ({$m['cierres']} de {$m['vist']} — {$m['tasa_pct']}% contra {$m['bench_pct']}% de la empresa)" : '';
                return self::_pick([
                    "Aquí está la clave: trabajas bien —mandas, sigues, atiendes el Radar— y aun así no cierras{$stat}. Cuando el esfuerzo está arriba y el resultado abajo, el problema ya no es trabajar más, es la conversación de venta.",
                    "Todo lo de arriba lo haces bien y la venta no baja{$stat}. Eso descarta el esfuerzo: la fuga está dentro de la plática con el cliente, no en cuántas propuestas mandas.",
                ], $seed + 1);
            }

            case 'eng':
                switch ($sd) {
                    case 'sin_cobrar':
                        return self::_pick([
                            "Cierras, pero no cobras: " . self::_pl($m['vsp'], 'venta lleva días sin un peso', 'ventas llevan días sin un peso') . " abonado. Una venta sin cobrar no es venta hasta que entra el dinero — y es lo que más te está pesando aquí.",
                            self::_pl($m['vsp'], 'venta sigue', 'ventas siguen') . " en cero pagado después de varios días. Cerrar sin cobrar es dejar el trabajo a medias.",
                        ], $seed + 1);
                    case 'bajo_vol':
                        return self::_pick([
                            "Vendes por debajo de lo que vendía el equipo el período pasado (" . self::_pl($m['vper'], 'venta', 'ventas') . " vs " . round($m['bventas'], 1) . " de referencia). No es cómo cierras, es cuánto: te falta cerrar más seguido.",
                            "Tu volumen de ventas quedó bajo el promedio del equipo del período anterior. El remate no es el tema — el número de cierres sí.",
                        ], $seed + 1);
                    case 'descuento':
                    default:
                        return self::_pick([
                            "Cierras, pero regalando: buena parte de tus ventas van con descuento. El número se ve bien y el margen no — un descuento chico se come una tajada de utilidad mucho más grande de lo que parece.",
                            "Sí vendes, pero el cierre te sale caro: estás bajando precio para cerrar, y eso entrena al cliente a pedir más la próxima.",
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

    // ── LA JUGADA: script concreto por sub-driver ───────────
    private static function _jugada(string $arq, string $sd, array $m, int $seed): string
    {
        switch ($arq) {

            case 'desenganchado':
                return self::_pick([
                    "Un paso a la vez, en orden. Primero lo básico: manda tus cotizaciones el mismo día que te las piden y confírmale al cliente que le llegaron —«le acabo de enviar su cotización, ¿le llegó bien?»—. Cuando eso sea rutina, seguimos. No intentes los cinco frentes juntos.",
                    "Arranca por el cimiento: cotización el mismo día, y un mensaje personal para confirmar que la vio. Y usa lo que el sistema te da gratis: estos tips y el ❓ del Radar te dicen a quién seguir. Domina eso una semana antes de pensar en cierre.",
                ], $seed + 2);

            case 'act':
                switch ($sd) {
                    case 'no_lee':
                        return self::_pick([
                            "Tu jugada de hoy es de 5 minutos: entra al Radar, abre el ❓ de tus " . self::_pl(max($m['cal'],1), 'cotización caliente', 'cotizaciones calientes') . " y lee por qué se marcaron. Eso te dice a quién llamar y con qué argumento — es tu lista de trabajo, no un adorno.",
                            "Convierte el sistema en tu rutina: cada mañana lee este análisis y abre el ❓ de tus calientes antes de llamar. Llegar sabiendo qué disparó la señal (revisó precio, volvió) es media venta hecha.",
                        ], $seed + 2);
                    case 'no_llega':
                        return self::_pick([
                            "No mandes en silencio. Cada propuesta sale con un aviso directo por el canal que el cliente sí usa —WhatsApp, llamada—: «le acabo de mandar su cotización de [lo suyo], ¿le llegó bien?». Y rescata las viejas: «quería asegurarme de que la vio, a veces se va a spam».",
                            "Persigue la apertura antes que la venta: confirma recepción por WhatsApp o teléfono, no reenvíes por correo diez veces. Primero que la vean; sin eso no hay nada que trabajar.",
                        ], $seed + 2);
                    case 'dormidas':
                        return self::_pick([
                            "Reabre a los que se enfriaron con un ángulo nuevo, no con «¿sigue en pie?» (suena a cobro): «me acordé de usted, ya tenemos [algo nuevo] / ¿le resuelvo alguna duda de la propuesta?». Empieza por los más recientes, que aún te recuerdan.",
                            "Dales una razón para volver, no un recordatorio: una opción de pago, un dato, la duda resuelta. El seguimiento que reactiva da algo, no pide.",
                        ], $seed + 2);
                    case 'apertura':
                    default:
                        return self::_pick([
                            "Cambia el canal y el gancho: manda el link por WhatsApp con una línea personal en vez de un correo genérico, y que el primer mensaje diga qué gana el cliente al abrirla, no solo «su cotización».",
                            "Si no las abren, el problema es el envío: usa el canal que el cliente sí revisa y un asunto concreto —«su cotización de [X], 3 opciones dentro»— en vez de uno genérico.",
                        ], $seed + 2);
                }

            case 'francotirador':
                return self::_pick([
                    "No cambies nada de cómo cierras — funciona. Solo dale volumen: ponte una meta diaria de cotizaciones nuevas y bloquea una hora fija para prospectar. Cada propuesta extra, con tu tasa, es casi dinero seguro.",
                    "Tu única palanca es llenar el embudo. Fija un número de propuestas por día y respétalo como cita. La máquina de cerrar ya la tienes; solo le falta con qué trabajar.",
                ], $seed + 2);

            case 'seg':
                if ($sd === 'no_explora') {
                    $j = self::_pick([
                        "Antes de llamar a cada caliente, abre su ❓ en el Radar: te dice qué disparó la señal. Llegas con el argumento listo —«vi que revisó el precio, déjeme explicarle qué incluye»— en vez de improvisar.",
                        "Haz del ❓ tu paso previo obligatorio: un clic te da por qué está caliente. Con eso la llamada deja de ser a ciegas y se vuelve dirigida a la duda real del cliente.",
                    ], $seed + 2);
                } else {
                    $j = self::_pick([
                        "Marca «con interés / sin interés» en cada caliente del Radar y atiéndela el mismo día: no preguntes «¿ya la vio?» (ya lo sabes), ve directo —«vi que estuvo revisando, ¿qué duda le resuelvo para que decida?».",
                        "Trata la señal caliente como tu prioridad del día: dale feedback en el Radar y una llamada corta. Esa ventana se cierra en horas; el que reacciona primero cierra.",
                    ], $seed + 2);
                }
                $t = self::_target($m); if ($t !== '') $j .= ' ' . $t;
                return $j;

            case 'hlt':
                return self::_pick([
                    "Prioriza por temperatura, no por antigüedad, y no dejes ningún caliente sin próximo paso agendado. Al que se está enfriando reactívalo con un motivo nuevo —una opción de pago, un dato— antes de que muera; hoy te recuerda, en una semana no.",
                    "Revisa el Radar a diario y rescata al que va perdiendo bucket antes de que caiga a cero. Un ángulo fresco lo reabre; abandonarlo lo mata sin ruido.",
                ], $seed + 2);

            case 'conv': {
                $j = self::_pick([
                    "No vendes el producto, haces visible lo que le cuesta NO resolver su problema: «si sigue con esto otros meses, ¿qué le representa?». El cliente que dimensiona su propio dolor se vende solo la urgencia — eso mueve el cierre, no otra cotización.",
                    "El que abre y no compra casi siempre tiene una objeción que no te dijo. Sácala tú antes de que mate el trato: «antes de cerrar, ¿qué le impediría avanzar hoy?». No se resuelve mandando otro PDF, se resuelve en una llamada.",
                    "Puede que estés persiguiendo a quien nunca iba a comprar. Califica temprano para descartar rápido: «¿esto lo quiere resolver este mes o de momento está viendo precios?». Soltar al que no compra te libera para el que sí.",
                    "Muchas ventas se caen porque nadie pidió la orden. Cuando ya hay interés, deja de preguntar «¿le interesa?» —ya lo sabes— y avanza al siguiente paso: «¿se lo agendamos para esta semana o la próxima?».",
                ], $seed + 2);
                $t = self::_target($m); if ($t !== '') $j .= ' ' . $t;
                return $j;
            }

            case 'eng':
                switch ($sd) {
                    case 'sin_cobrar':
                        return self::_pick([
                            "El cobro se pacta al cerrar, no después: pide un anticipo para apartar —«dejamos un anticipo y el resto contra entrega»—. Quien pone dinero ya decidió; un sí sin anticipo se enfría. Y a las que ya cerraste sin cobrar, ponles fecha de pago concreta y no la sueltes.",
                            "Trabaja tus ventas sin pago como cierres nuevos: llama, fija una fecha de abono y dale seguimiento. Hasta que no entra el dinero, no es venta.",
                        ], $seed + 2);
                    case 'bajo_vol':
                        return self::_pick([
                            "Tu remate funciona; falta cantidad. Sube el número de cierres subiendo el de intentos: más propuestas y más seguimiento a lo caliente. El volumen es tu palanca, no la técnica.",
                            "Ponte una meta de ventas por semana y llena el embudo para alcanzarla. Vendes bien, solo necesitas hacerlo más seguido.",
                        ], $seed + 2);
                    case 'descuento':
                    default:
                        return self::_pick([
                            "Cuando pidan descuento no bajes el número de inmediato: pregunta «¿con qué lo está comparando?». Muchas veces no es precio, es que no ve el valor. Si tienes que ceder, cede en plazo o alcance, no en el precio.",
                            "Ancla el valor antes de tocar el precio: qué incluye que otros no, y por qué a la larga le sale más barato contigo. Cierra igual, sin regalar margen.",
                        ], $seed + 2);
                }

            case 'estancado':
            default:
                return self::_pick([
                    "Aprieta una sola tuerca esta semana: en cada trato, una pregunta de implicación —«¿qué le cuesta seguir sin resolver esto?»—. Es el hábito que más mueve el cierre sin trabajar más horas.",
                    "Elige una palanca y clávala: reacciona el mismo día a todo lo que el Radar marque caliente. Una cosa bien hecha te saca de estable y te sube de nivel.",
                ], $seed + 2);
        }
    }

    // ── Mérito FACTUAL (sin pep-talk) ───────────────────────
    private static function _merito(string $arq, array $m): string
    {
        // Reached conv/eng vía embudo ⇒ act/seg/hlt están OK: díselo (es cierto).
        if ($arq === 'conv' || $arq === 'eng') {
            return "El arranque y el seguimiento no son tu problema — enfócate solo en esto.";
        }
        if ($m['bcierre'] >= 8) return "A tu favor, y es real: tu cierre va muy por encima de tu propio histórico.";
        if ($m['bcierre'] >= 4) return "Un dato a tu favor: estás cerrando por encima de tu histórico reciente.";
        return '';
    }

    private static function _consecuencia_bajo(int $seed): string
    {
        return self::_pick([
            "Con estos números el mes no cierra, y eso pega a todo el equipo. Tiene que cambiar esta semana.",
            "Así no se sostiene. La empresa necesita que estos números reaccionen ya, no el mes que entra.",
            "Cada semana así cuesta dinero real. Esto es prioridad hoy.",
        ], $seed + 11);
    }

    // ── TENDENCIA (sube/baja/estable) ───────────────────────
    private static function _dir(float $cur, float $ema): string
    {
        if ($ema < 0) return 'na';
        $d = $cur - $ema;
        if ($d >= 0.05) return 'sube';
        if ($d <= -0.05) return 'baja';
        return 'estable';
    }
    private static function _dir_global(array $m): string
    {
        if ($m['mom'] >= 1.10) return 'sube';
        if ($m['mom'] <= 0.90) return 'baja';
        return 'estable';
    }

    private static function _trend_clause(string $arq, array $m, int $seed): string
    {
        $cual = ''; $dir = 'na';
        switch ($arq) {
            case 'act':
            case 'desenganchado':
                $dir = self::_dir($m['s_act'], $m['ema_act']); $cual = 'tu activación'; break;
            case 'seg':
                $dir = self::_dir($m['s_seg'], $m['ema_seg']); $cual = 'tu seguimiento'; break;
            case 'conv':
            case 'francotirador':
                $dir = self::_dir($m['s_conv'], $m['ema_conv']); $cual = 'tu cierre'; break;
            case 'eng':
                $dir = self::_dir($m['s_eng'], $m['ema_eng']); $cual = 'tu disciplina de cierre'; break;
            case 'hlt':
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
                    "Y lleva ahí estancado: {$cual} no se mueve por su cuenta — sin un cambio de tu parte, la próxima semana lees lo mismo.",
                    "Lleva plano hace semanas: {$cual} no empeora, pero tampoco sube por inercia. El movimiento tiene que venir de ti.",
                ], $seed + 7);
        }
    }

    // ── A QUIÉN empezar (datos fiables: calientes sin explorar / dormidas) ──
    private static function _target(array $m): string
    {
        $sin_explorar = max(0, $m['cal'] - $m['exp']);
        if ($sin_explorar > 0) {
            return $sin_explorar === 1
                ? "Empieza por la caliente que aún no abres en el ❓ del Radar."
                : "Empieza por las {$sin_explorar} calientes que aún no abres en el ❓ del Radar.";
        }
        if ($m['dorm'] > 0) {
            return $m['dorm'] === 1
                ? "Arranca con el cliente que abrió y no volviste a tocar."
                : "Arranca con los {$m['dorm']} que abrieron y no volviste a tocar.";
        }
        return '';
    }

    // ── TOP ─────────────────────────────────────────────────
    private static function _render_top(array $m, int $seed): string
    {
        $partes = [];
        $partes[] = self::_pick([
            "Rendimiento de primera este período — vas en el grupo de arriba.",
            "Mes fuerte: tus números están claramente sobre la media.",
        ], $seed);

        $sin_explorar = max(0, $m['cal'] - $m['exp']);
        if ($sin_explorar > 0) {
            $partes[] = ($sin_explorar === 1)
                ? "Para redondearlo tienes 1 caliente sin abrir en el ❓ del Radar. Atiéndela hoy y el mes es histórico."
                : "Para redondearlo, {$sin_explorar} calientes sin abrir en el ❓ del Radar. Atiéndelas hoy y el mes es histórico.";
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
        $sin_explorar = max(0, $m['cal'] - $m['exp']);
        if ($sin_explorar > 0) {
            $op .= ' ' . ($sin_explorar === 1
                ? "Y si ya tienes 1 caliente en el Radar, atiéndela hoy — no dejes ir lo poco activo que tienes."
                : "Y si ya tienes {$sin_explorar} calientes en el Radar, atiéndelas hoy — no dejes ir lo poco activo que tienes.");
        }
        return $op;
    }
}
