<?php
// ============================================================
//  RitmoTip — Tip de coaching por asesor (CotizaCloud AI)
//  Enseña una TÉCNICA de ventas real (no resume números), elegida
//  por la debilidad #1 del asesor, ROTANDO para no repetir, con
//  gancho de su dato real y tono según score.
//
//  Catálogo: docs/catalogo_tips_ventas.md (fuente curable).
//  Rotación: tabla ritmo_tips (asesor_id, handle) — no repite una
//  técnica hasta agotar las de la debilidad.
//  Solo lectura (la escritura del handle mostrado la hace el endpoint).
// ============================================================
defined('COTIZAAPP') or die;

class RitmoTip
{
    /** Catálogo: debilidad => [ [handle, texto], ... ] en orden de currículo. */
    private static function catalogo(): array
    {
        return [
            'califica' => [
                ['d1_calificar',   "Técnica de calificación: no descartes a ciegas, califica en 2 minutos con 3 preguntas. Situación: “¿para cuándo lo necesita?”; problema: “¿qué es lo que quiere resolver?”; decisión: “¿es usted quien decide o hay alguien más?”. Si es comprador real, trabájalo duro; si de verdad no, suéltalo sin culpa. Descalificar bien es vender; descartar sin preguntar es tirar ventas."],
                ['d1_diagnostica', "Diagnostica antes de proponer (método SPIN): vende el que primero entiende. Antes de mandar precio o descartar, pregunta de situación y de problema: “¿qué está usando hoy?”, “¿qué es lo que más le molesta de eso?”. Cuando el cliente dice su dolor en voz alta, la venta se arma sola."],
                ['d1_escalera',    "Escalera de compromiso (micro-sí): descartas porque brincas al cierre y el salto es muy grande. Pide un “sí” chiquito primero: “¿le mando una foto del acabado?” → “¿le sirvió?” → “¿lo vemos 10 minutos?”. Cada sí pequeño hace fácil el siguiente."],
                ['d1_intento',     "Regla del intento de cita: nunca descartes sin haber propuesto al menos una. Sin cita, la cotización no está muerta — está sin trabajar."],
            ],
            'calientes' => [
                ['d2_senal',   "Lee la señal de compra: que un cliente vuelva a entrar a su cotización es intención, no molestia. El que regresa te levantó la mano — háblale el mismo día, no lo tires."],
                ['d2_yesif',   "Micro-compromiso “sí, si…”: en vez de “¿nos vemos?” (fácil decir no), amárralo al valor: “si le enseño en 10 minutos cómo le queda y cuánto se ahorra, ¿vale la pena?”. Si dice sí, se comprometió; si dice no, te dio su objeción real."],
                ['d2_urgencia',"Urgencia real (no inventada): si de verdad hay una razón, úsala para que decida ya: “de esto me queda una y hay otro cliente viéndola”. Solo si es cierto — la urgencia falsa quema."],
                ['d2_rescate', "Rescate antes de tirar: antes de descartar una caliente, un gancho neutral: “¿pudo revisar la cotización? ¿le resuelvo alguna duda para cerrarlo?”."],
            ],
            'precio' => [
                ['d3_laer',         "Marco LAER, la clave es Explorar antes de Responder: ante “está caro” no defiendas el número. Escucha, valida (“es válido cuidar la inversión”) y explora: “¿caro comparado con qué?”, “¿qué esperaba invertir?”, “¿es el precio o lo que ve por ese precio?”. Recién con el freno real, responde ESO."],
                ['d3_sandwich',     "Sándwich de valor: no repitas el precio pelón. Resultado, precio en medio, otro beneficio: “Le queda instalado en 3 días con garantía de 2 años, son 18 mil, y le incluimos el primer mantenimiento.” El número se diluye entre dos valores."],
                ['d3_no_contra_ti', "Nunca negocies contra ti mismo: no bajes ni ofrezcas descuento antes de que te lo pidan. Si nadie objetó el precio, no hay nada que bajar."],
                ['d3_intercambia',  "Intercambia, no regales: si mueves el precio, pide algo a cambio: “le ajusto si cerramos hoy” o “si se lleva también X”. Bajar por bajar le enseña a pedir más."],
                ['d3_comparacion',  "Si te comparan con uno más barato, no bajes: pregunta “¿qué está comparando exactamente?”. Casi nunca es lo mismo — resalta lo que tú incluyes y ellos no, y cuéntale de alguien que se fue por precio y se arrepintió."],
            ],
            'objeciones' => [
                ['d4_laer',        "LAER ante “déjame pensarlo”: no es un no, es una duda sin destapar. Valida (“claro, es para pensarse”) y explora: “¿qué es lo que quiere terminar de ver?”. Ahí sale lo que de verdad lo frena."],
                ['d4_aisla',       "Aísla la objeción real: “Aparte de eso, ¿hay algo más que lo detenga?”. Si dice que no, ya sabes qué resolver. Si dice que sí, salió la objeción de verdad."],
                ['d4_sus_razones', "Compra por SUS razones, no las tuyas: no lo convenzas con lo que a ti te parece importante. Pregúntale qué es lo que a ÉL le importa (tiempo, garantía, quedar bien) y véndele eso."],
                ['d4_amarra',      "Amarra la definición: lo que “quedó en el aire” se enfría. Ponle fecha: “¿le parece si el jueves, ya con esto resuelto, lo cerramos?”."],
            ],
            'citas' => [
                ['d5_alternativa',     "Doble opción, ningún “no”: no preguntes “¿quiere que nos veamos?”. Da a elegir entre dos síes: “¿le queda hoy o mañana?”, “¿por la mañana o en la tarde?”."],
                ['d5_cierra_contacto', "Cierra cada contacto con la siguiente cita: nunca cuelgues sin fecha. Antes de terminar: “perfecto, ¿lo vemos el martes a las 11?”."],
                ['d5_revive',          "Revive cartera para llenar el embudo: si bajaron tus citas, no esperes leads nuevos. Reactiva a los que quedaron en el aire hace semanas — ahí hay citas sin gastar en publicidad."],
            ],
            'contacto' => [
                ['d6_horario',   "Cambia el horario, no el mensaje: si marcaste a las 10 y no contestó, no vuelvas a las 10:15. Prueba otra franja (mediodía, después de las 6). El “no contesta” casi siempre es mal momento, no falta de interés."],
                ['d6_canal',     "Cambia de canal: si no contesta la llamada, no insistas por lo mismo. WhatsApp, y si no, un audio corto. Cada cliente tiene su medio."],
                ['d6_velocidad', "Regla de la primera hora: un lead nuevo se contacta en minutos, no en horas. La velocidad del primer intento es lo que más mueve que te contesten."],
            ],
            'cierre' => [
                ['d7_senal',       "Reconoce la señal de cierre: cuando pregunta por tiempos, garantía o formas de pago, ya está listo. Deja de vender y cierra."],
                ['d7_asuntivo',    "Cierre asuntivo: si la señal es fuerte, avanza como si ya dijo sí. No preguntes “¿lo quiere?”, di “perfecto, ¿lo apartamos hoy?” o “¿le empiezo el pedido?”."],
                ['d7_alternativa', "Cierre por alternativa: si necesita un empujón, dos caminos que ambos cierran: “¿de contado o a plazos?”, “¿esta semana o la próxima?”."],
                ['d7_resumen',     "Cierre de resumen (para lo más caro): recapitula el valor y pide el paso: “quedamos en que le resuelve X, Y y Z, cabe en su presupuesto y lo tiene listo el [fecha]. ¿Qué duda le queda antes de arrancar?”."],
                ['d7_prueba',      "Cierre de prueba: si hay una objeción pendiente, pruébalo: “si le resuelvo lo del plazo, ¿lo cerramos hoy?”. Te dice si esa era la única traba."],
            ],
            'seguimiento' => [
                ['d8_sistema',   "Sistema, no memoria: los seguimientos se agendan, no se recuerdan. Lo que vence hoy, hoy se toca. Confiar en la memoria es como se pierden las ventas."],
                ['d8_servicio',  "Aporta valor en cada toque: persistir no es fastidiar si traes algo — un dato, una foto, una respuesta. Nunca solo “¿ya decidió?”."],
                ['d8_velocidad', "La velocidad manda: al que acaba de pedir cotización o dejó una duda, contéstale en minutos. El seguimiento tardío es una venta que se enfría."],
            ],
            'radar' => [
                ['d9_prioridad', "Empieza el día por tus calientes: el Radar te dice quién quiere comprarte hoy. Un caliente sin atender es la venta más fácil que estás dejando pasar."],
                ['d9_marca',     "Marca lo que ves (👍/👎): no es burocracia, te ordena a quién seguir y afina el sistema. Un caliente sin marcar es un cliente sin dueño."],
            ],
            'enfriamiento' => [
                ['d10_gancho', "Resurrección con gancho, no con pregunta vacía: al que se enfrió, no le mandes “¿sigue interesado?” (invita al no). Revívelo con algo nuevo y concreto: “me llegaron los tiempos de entrega, ¿se los paso?” o un cambio que le mueva."],
                ['d10_timing', "Es timing, no falta de interés: la mayoría no compra al primer intento por CUÁNDO, no por SI. Un frío hoy puede estar listo en dos semanas — guárdalo y reactívalo, no lo tires."],
            ],
            'descuento' => [
                ['d11_valor',       "Sube el valor, no bajes el precio: cada descuento sin pelear se come tu margen y te vuelve vendedor de precio. Antes de ceder, recuérdale todo lo que se lleva; muchas veces el descuento sobra."],
                ['d11_intercambia', "Si descuentas, intercambia: el descuento se cambia por algo — cierre hoy, contado, una compra más grande. Nunca por nada."],
                ['d11_urgencia',    "Urgencia real en vez de descuento: en lugar de bajar el precio, dale una razón verdadera para decidir ya (última pieza, sube el mes que entra). Solo si es cierto."],
            ],
            'ticket' => [
                ['d14_necesidad',  "Descubre el proyecto completo: el ticket sube cuando encuentras lo que no te pidió pero necesita. Pregunta por todo el proyecto, no solo por lo que vino a cotizar."],
                ['d14_complemento',"Suma en el momento del sí: cuando ya dijo que sí es cuando más compra. “¿Le agregamos X que combina con esto?”. El cliente comprado compra más."],
            ],
            'bien' => [
                ['ok_sube',   "Vas bien. Para el siguiente nivel, sube el ticket: en cada venta pregunta por el proyecto completo, no solo por lo que vinieron a cotizar. Ahí está el crecimiento sin más clientes."],
                ['ok_ritmo',  "Vas bien. No sueltes el ritmo: agenda la siguiente cita antes de cerrar la de hoy, así el embudo nunca se seca."],
                ['ok_radar',  "Vas bien. Exprime el Radar: el que vuelve a abrir su cotización es tu venta más fácil de la semana — atiéndelo primero."],
            ],
        ];
    }

    /**
     * Elige el tip para el asesor a partir del expediente del reporte ($d) y
     * los handles ya mostrados. Devuelve ['handle','texto','debilidad'] o null.
     */
    public static function elegir(array $d, array $vistos = []): ?array
    {
        [$deb, $marcos] = self::_debilidad($d);
        $cat = self::catalogo();
        $tecnicas = $cat[$deb] ?? $cat['bien'];
        $gancho = self::_marco($marcos, (int)($d['card']['usuario_id'] ?? 0));

        // ROUND-ROBIN real: gana la que lleva MÁS tiempo sin mostrarse. $vistos
        // viene ordenado del más reciente al más viejo, así que la posición ES la
        // antigüedad; la que nunca se mostró gana siempre (y entre esas, el
        // currículo). Antes se tomaba "la primera no vista, si no la [0]" — al
        // agotar la debilidad se quedaba clavada en la #1 para siempre.
        $elegida = null; $mejor = -1;
        foreach ($tecnicas as $t) {
            $pos  = array_search($t[0], $vistos, true);
            $edad = ($pos === false) ? PHP_INT_MAX : (int)$pos;
            if ($edad > $mejor) { $mejor = $edad; $elegida = $t; }
        }
        if ($elegida === null) $elegida = $tecnicas[0];

        $score = (int)($d['score']['score'] ?? 50);
        $texto = self::_tono($score, $gancho) . $elegida[1];
        return ['handle' => $elegida[0], 'texto' => $texto, 'debilidad' => $deb];
    }

    /**
     * Variante para el TERMÓMETRO (coherente con el reporte): usa el expediente
     * granular ($d de RitmoReporte::expediente) para detectar la MISMA debilidad
     * #1 que el reporte, y rota POR DÍA (estable en el día, avanza mañana).
     */
    public static function paraTermometro(array $d): ?array
    {
        if (empty($d['card'])) return null;                    // sin tarjeta → deja el legacy
        [$deb, $marcos] = self::_debilidad($d);
        $cat = self::catalogo();
        $tecs = $cat[$deb] ?? $cat['bien'];
        if (!$tecs) return null;

        $uid = (int)($d['card']['usuario_id'] ?? 0);
        $gancho = self::_marco($marcos, $uid);
        $idx = ((int)date('z') + $uid) % count($tecs);        // rotación diaria por asesor
        $t = $tecs[$idx];
        $score = (int)($d['score']['score'] ?? 50);
        return ['handle' => $t[0], 'texto' => self::_tono($score, $gancho) . $t[1], 'debilidad' => $deb];
    }

    /**
     * Variante ligera (solo score row) — fallback si no hay expediente. Rota por día.
     */
    public static function desdeScore(array $s): ?array
    {
        if (($s['nivel'] ?? '') === 'nuevo') return null;      // "recopilando info" → deja el legacy
        if ((int)($s['cot_asignadas'] ?? 0) === 0) return null;

        [$deb, $marcos] = self::_debilidadScore($s);
        $cat = self::catalogo();
        $tecs = $cat[$deb] ?? $cat['bien'];
        if (!$tecs) return null;

        // Rotación diaria determinista: misma técnica todo el día, siguiente mañana.
        $uid = (int)($s['usuario_id'] ?? 0);
        $gancho = self::_marco($marcos, $uid);
        $idx = ((int)date('z') + $uid) % count($tecs);
        $t = $tecs[$idx];

        $score = (int)($s['score'] ?? 50);
        return ['handle' => $t[0], 'texto' => self::_tono($score, $gancho) . $t[1], 'debilidad' => $deb];
    }

    /** Debilidad #1 desde el score row (lo detectable sin queries extra). */
    private static function _debilidadScore(array $s): array
    {
        $vist    = (int)($s['cot_vistas'] ?? 0);
        $cierres = (int)($s['conversiones'] ?? 0);
        $dorm    = (int)($s['cot_dormidas'] ?? 0);
        $nab     = (int)($s['no_abiertas_5d'] ?? 0);
        $cal     = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $fb      = (int)($s['fb_total'] ?? $s['radar_views'] ?? 0);
        $ign     = max(0, $cal - $fb);
        $venc    = (int)($s['mesa_dias_vencidos'] ?? 0);
        $sdto    = (int)($s['cierres_sin_dto'] ?? 0);
        $cdto    = $cierres - $sdto;

        if ($venc > 0) return ['seguimiento', [
            "Se te están acumulando seguimientos vencidos. ",
            "Traes seguimientos que ya se te pasaron de fecha. ",
        ]];
        if ($cierres === 0 && $vist >= 8) return ['cierre', [
            "Trabajaste {$vist} cotizaciones y aún no cierras ninguna. ",
            "Llevas {$vist} trabajadas sin una sola venta. ",
        ]];
        if ($ign >= 2) return ['radar', [
            "Tienes {$ign} calientes del Radar sin marcar. ",
            "Traes {$ign} en caliente sin atender. ",
        ]];
        if ($cierres > 0 && $sdto < $cierres) return ['descuento', [
            "Cerraste con descuento en {$cdto} de {$cierres}. ",
            "De tus {$cierres} ventas, {$cdto} llevaron descuento. ",
        ]];
        if ($dorm > 0) return ['enfriamiento', [
            "Tienes {$dorm} clientes que dejaron de volver. ",
            "Traes {$dorm} que se enfriaron y no han regresado. ",
        ]];
        if ($nab > 0) return ['contacto', [
            "Tienes {$nab} cotizaciones que el cliente no ha abierto. ",
            "Traes {$nab} que el cliente ni siquiera ha abierto. ",
        ]];
        return ['bien', []];
    }

    /**
     * Debilidad #1 + marco con su dato real, en orden de prioridad.
     * Devuelve VARIAS redacciones del mismo hecho: el dato es idéntico en todas,
     * cambia cómo se dice. Así dos asesores con el mismo problema no leen el
     * mismo párrafo (queja real: "parece plantilla y dejan de leerlo").
     */
    private static function _debilidad(array $d): array
    {
        $card = $d['card'] ?? null; $de = $d['desc'] ?? []; $ve = $d['vent'] ?? [];
        $m = $d['mesa'] ?? []; $rd = $d['radar'] ?? [];
        $noc = ($card && ($card['cont_estado'] ?? '') !== 'verde' && ($card['cont_estado'] ?? '') !== 'gris');
        $pl  = fn(int $n, string $uno, string $varios): string => $n === 1 ? $uno : $varios;

        // ── CITAS EN CERO: lo PRIMERO (decisión CEO) ──
        // Sin citas no hay ventas. Es la raíz del embudo, y a diferencia de las
        // vencidas no tiene otro mecanismo que se lo grite: la Mesa ya le pone
        // las vencidas hasta arriba de su fila, con reloj. El tip es coaching y
        // el coaching ataca la raíz, no repite lo que la Mesa ya está gritando.
        if ($card && ($card['citas_estado'] ?? '') === 'rojo')
            return ['citas', self::_marcos_citas($card, true)];

        // Seguimiento vencido
        if (($v = (int)($m['vencidas'] ?? 0)) > 0)
            return ['seguimiento', [
                "Cada cliente que dejas vencer se enfría o se va con otro. Traes {$v} " . $pl($v, 'seguimiento caído', 'seguimientos caídos') . ": eran ventas en tu mano y las estás soltando por no marcar a tiempo. ",
                "Traes {$v} " . $pl($v, 'seguimiento vencido', 'seguimientos vencidos') . ". Ese cliente ya te dijo que sí a que le hablaras; el que no llegó a tiempo fuiste tú, y mientras tanto la competencia sí marca. ",
                "{$v} " . $pl($v, 'cliente esperando que le marques', 'clientes esperando que les marques') . " y no lo has hecho. Esas ventas no se pierden por precio ni por producto: se pierden por silencio. ",
            ]];

        // Descartes: precio > calientes > objeción en el aire > sin calificar
        $descRojo = $card && in_array($card['desc_estado'] ?? '', ['rojo','amarillo'], true);
        if ($descRojo && ($de['precio'] ?? 0) > 0 && ($de['precio'] ?? 0) >= (int)ceil(($de['n'] ?? 1) * 0.4)) {
            // "estando calientes" solo si de verdad lo estaban (precio_hot).
            $n   = (int)$de['precio'];
            $ph  = (int)($de['precio_hot'] ?? 0);
            $cal = $ph >= 2 ? ", varias estando calientes" : ($ph === 1 ? ", una de ellas estando caliente" : "");
            return ['precio', [
                "Bajar el precio a la primera es la salida fácil y la que menos vende. {$n} se te cayeron por precio{$cal}: no era el precio, era que no le mostraste por qué vale. ",
                "{$n} cotizaciones muertas por precio{$cal}. Cuando el cliente dice que está caro casi nunca habla del número: habla de que no ve qué recibe por él. ",
                "Se te fueron {$n} por precio{$cal}. El que compite por precio pierde dos veces: pierde el margen y pierde igual al cliente. ",
            ]];
        }
        if ($descRojo && ($n = (int)($de['hot_noprecio'] ?? 0)) >= 2)
            return ['calientes', [
                "Cada cotización que trabajas cuesta esfuerzo: tiempo, escuchar al cliente, el primer contacto. Descartaste {$n} que ADEMÁS estaban calientes — el cliente estaba listo y lo mandaste a la basura. Esas eran tuyas. ",
                "Tiraste {$n} cotizaciones que el Radar traía calientes. El cliente estaba entrando a verlas: eso no es un lead muerto, es uno que te levantó la mano y no volteaste. ",
                "{$n} de tus descartes estaban calientes. De todo lo que traes en la mesa, esas eran las más fáciles de cerrar — y son justo las que soltaste. ",
            ]];
        // Objeción sin destapar: descartes que quedaron "en el aire"/"para después".
        if ($descRojo && ($n = (int)($de['aire'] ?? 0)) >= 2)
            return ['objeciones', [
                "Un “déjame pensarlo” no es un no: es una duda que no destapaste. {$n} de tus descartes quedaron en el aire y ahí se enfriaron, con una objeción que nadie resolvió. ",
                "{$n} cotizaciones se te quedaron “para después”. Nadie deja para después lo que sí quiere: hay un freno que no te dijeron y que tampoco preguntaste. ",
                "Traes {$n} que murieron en el aire. El cliente no te dijo que no — te dijo que ahorita no, que es otra cosa, y ahí es justo donde se trabaja. ",
            ]];

        if ($descRojo) {
            // Números del PILAR (no de otra ventana) — el detalle se adapta a lo
            // que de verdad pasó: sin cita, o descartadas casi sin darles tiempo.
            $nd = (int)($card['n_desc'] ?? $de['n'] ?? 0);
            $ns = (int)($card['n_sincita'] ?? $de['sincita'] ?? 0);
            $nr = (int)($card['n_rapido'] ?? $de['rapido'] ?? 0);
            $nt = (int)($card['n_trabajo'] ?? 0);
            $det = $ns > 0 ? ", {$ns} sin siquiera intentar una cita"
                 : ($nr > 0 ? ", {$nr} casi sin darles tiempo" : "");
            $ref = $nt > 0 ? " de {$nt} que trabajaste" : "";
            return ['califica', [
                "Conseguir cada cotización te costó trabajo. Estás soltando {$nd}{$ref}{$det}: ese esfuerzo lo tiras sin pelear la venta. ",
                "Vas soltando {$nd}{$ref}{$det}. Cada una costó tiempo y una llamada — tirarlas sin pelearlas es tirar el trabajo que ya hiciste. ",
                "{$nd} descartes{$ref}{$det}. Descartar bien es de buen vendedor; descartar sin haber preguntado nada es otra cosa muy distinta. ",
            ]];
        }

        // No cierra pese a tener citas
        if ($card && ($card['conv_estado'] ?? '') === 'rojo') {
            $nt = (int)($card['n_trabajo'] ?? 0);   // TRABAJADAS, no descartes
            return ['cierre', $nt > 0 ? [
                "Abrir no es cerrar, y ahí es donde se gana o se pierde. Trabajaste {$nt} y no cerraste ninguna: el cliente llega hasta la puerta y no lo estás haciendo pasar. ",
                "{$nt} cotizaciones trabajadas y cero cerradas. El problema no está en conseguir clientes — está en el último paso, que es el único que se paga. ",
                "Llevas {$nt} trabajadas sin una sola venta. Todo lo de antes lo estás haciendo bien; lo que falta es pedir la firma. ",
            ] : [
                "Abrir no es cerrar, y ahí es donde se gana o se pierde. Aún no cierras ninguna: el cliente llega hasta la puerta y no lo estás haciendo pasar. ",
                "Aún sin cerrar una sola. El último paso es el único que se paga, y ahí es donde se está quedando la venta. ",
            ]];
        }

        // No contesta
        if ($noc) {
            $t = (string)($card['cont_txt'] ?? '');
            return ['contacto', [
                "Un cliente que no contesta no siempre es un no: muchas veces es que no lo buscaste bien. {$t} — ahí hay ventas esperando a que insistas distinto. ",
                "{$t}. Antes de darlos por perdidos: casi nadie ignora a propósito, la mayoría simplemente estaba ocupado cuando marcaste. ",
                "{$t}. Ese número no dice que no te quieran comprar — dice que tu forma de buscarlos no está funcionando. ",
            ]];
        }

        // Regala descuento
        if (($ve['cierres'] ?? 0) > 0 && ($ve['con_dto'] ?? 0) > ($ve['sin_dto'] ?? 0)) {
            $a = (int)$ve['con_dto']; $b = (int)$ve['cierres'];
            return ['descuento', [
                "Regalar descuento se siente como cerrar, pero te come el margen y te acostumbra a vender por precio. {$a} de tus {$b} ventas fueron con descuento: estás comprando la venta en vez de ganártela. ",
                "{$a} de {$b} ventas con descuento. Cada punto que regalas sale de tu comisión y de la empresa — y le enseña al cliente que tu precio siempre baja. ",
                "Cierras con descuento {$a} de {$b} veces. Eso no es negociar, es ceder: negociar es cambiar algo por algo. ",
            ]];
        }

        // Citas en ÁMBAR (cayó a la mitad de lo suyo). El CERO se atiende arriba.
        if ($card && ($card['citas_estado'] ?? '') === 'amarillo')
            return ['citas', self::_marcos_citas($card, false)];

        // Calientes sin marcar
        if (($n = (int)($rd['sin_feedback'] ?? 0)) >= 2)
            return ['radar', [
                "El Radar te está diciendo quién quiere comprarte hoy. Tienes {$n} clientes calientes que ni tocaste: son las ventas más fáciles de la semana y las estás dejando pasar. ",
                "{$n} clientes calientes sin tocar. El sistema ya hizo el trabajo de decirte quién está listo; falta la parte que solo tú puedes hacer. ",
                "Traes {$n} en caliente sin atender. Mientras tú no marcas, ese cliente sigue comparando — y alguien más sí le va a contestar. ",
            ]];

        // Cierra, pero chico (solo si hay con quién comparar en la empresa)
        $tk = (float)($d['score']['ticket'] ?? 0); $bt = (float)($d['bench_ticket'] ?? 0);
        if (($ve['cierres'] ?? 0) > 0 && $tk > 0 && $bt > 0 && $tk < $bt * 0.7) {
            $a = self::_mx($tk); $b = self::_mx($bt);
            return ['ticket', [
                "Estás cerrando, pero chico: tu ticket promedio es {$a} y el del equipo {$b}. Cada venta te cuesta el mismo trabajo — lo que cambia tu quincena es el tamaño. ",
                "Tu ticket va en {$a} contra {$b} del equipo. Vender más veces cansa; vender más grande no — es la misma llamada con otro número. ",
                "Cierras en {$a} y el equipo en {$b}. No te falta cerrar: te falta preguntar por todo lo que el cliente necesita, no solo por lo que vino a pedir. ",
            ]];
        }

        // Va bien
        return ['bien', []];
    }

    /**
     * Diferencial de intensidad por score. NO agrega hechos nuevos: solo enmarca.
     * Bajo → esto es lo primero; medio → directo (sin prefijo); alto → reconoce
     * el score (dato real: >70 es Activo/Top) y aun así señala la fuga.
     * Si no hay marco (va bien), no se antepone nada.
     */
    private static function _tono(int $score, string $gancho): string
    {
        if (trim($gancho) === '') return $gancho;
        if ($score < 35) return "Esto es lo que más te está costando ahora mismo. " . $gancho;
        if ($score > 70) return "Traes buen score, y aun así por aquí se te va dinero. " . $gancho;
        return $gancho;
    }

    /**
     * Elige UNA redacción del marco. Dos asesores con la MISMA debilidad no
     * deben leer el mismo párrafo palabra por palabra — parece plantilla y
     * dejan de leerlo. El hash mezcla asesor Y día: si hoy a dos les toca la
     * misma, mañana ya no (sumar el día por separado conservaría el choque
     * para siempre).
     */
    private static function _marco(array $variantes, int $uid): string
    {
        if (!$variantes) return '';
        $n = count($variantes);
        return $variantes[$n === 1 ? 0 : abs(crc32($uid . ':' . date('z'))) % $n];
    }

    /**
     * Marcos del pilar Citas (varias redacciones del MISMO hecho). El dato sale
     * de los crudos de la tarjeta y NUNCA afirma un ritmo que no existió: si no
     * agendó nada en el mes, se dice exactamente eso.
     */
    private static function _marcos_citas(array $card, bool $cero): array
    {
        $cb  = (int)($card['n_citas_base'] ?? 0);
        $c28 = (int)($card['n_citas28'] ?? 0);
        $ref = $cb > 0   ? " y venías en ~{$cb} por semana"
             : ($c28 > 0 ? " y en todo el mes llevas {$c28}"
                         : " y tampoco ninguna en el último mes");
        return $cero ? [
            "Sin cita no hay venta, y tu embudo se PARÓ: no agendaste ni una en 7 días{$ref}. Lo que no siembras hoy es la quincena que no cobras. ",
            "Se te secó la agenda: cero citas en 7 días{$ref}. Un vendedor sin citas no está vendiendo, está esperando — y esperar no se cobra. ",
            "Ninguna cita en 7 días{$ref}. Todo lo demás que hagas bien no sirve si nadie se sienta contigo: la venta empieza ahí. ",
        ] : [
            "Sin cita no hay venta, y tu embudo se está secando: bajó tu ritmo de citas en los últimos 7 días{$ref}. Cada semana sin sembrar citas es una quincena floja en camino. ",
            "Le bajaste al ritmo de citas esta racha{$ref}. Lo que no agendas hoy no se nota mañana: se nota en la quincena. ",
            "Vienes agendando menos de lo tuyo{$ref}. El embudo se llena ANTES de quedarte sin nada que trabajar, no después. ",
        ];
    }

    /** Pesos en formato corto para los marcos. */
    private static function _mx(float $n): string { return '$' . number_format($n, 0, '.', ','); }
}
