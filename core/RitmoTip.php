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
                ['d1_calificar',   "Antes de tirar una cotización, califícala con 2-3 preguntas: “¿para cuándo lo necesita?”, “¿ya vio otras opciones?”, “¿es usted quien decide?”. Si de verdad no es comprador, suéltala sin culpa. Descartar sin preguntar es tirar ventas."],
                ['d1_escalera',    "Sueltas las cotizaciones porque brincas directo al cierre y el cliente aún no está listo. Sube por escalones: “¿te resolvió la duda del material?” → “te mando foto del acabado” → “¿te marco 10 min?”. Cada sí pequeño hace fácil el siguiente."],
                ['d1_intento',     "Nunca descartes sin haber propuesto al menos una cita. Si no llegó a cita, no está muerta: está sin trabajar."],
                ['d1_diagnostica', "Antes de descartar, una pregunta abierta: “¿qué es lo que más te hace dudar?”. El silencio del cliente no es un “no”, es una duda que no preguntaste."],
            ],
            'calientes' => [
                ['d2_lee',     "Que el cliente entre varias veces a tu cotización ES señal de compra, no de molestia. Al que vuelve a abrir, no lo descartes: háblale mientras la está viendo."],
                ['d2_momento', "El interés se enfría en horas, no en días. Cuando veas que un cliente acaba de abrir tu cotización, ese es el momento de marcarle, no mañana."],
                ['d2_rescate', "Si ya la ibas a descartar y estaba caliente, mándale un gancho antes de tirarla: “vi que revisaste la cotización, ¿te ayudo con alguna duda para cerrarlo?”."],
            ],
            'precio' => [
                ['d3_explora',      "“Está caro” casi nunca es el precio real: es que aún no ve por qué vale eso. No defiendas el número, explora: “¿caro comparado con qué?”. Ahí sale el freno verdadero."],
                ['d3_sandwich',     "Cuando te objeten el precio, no repitas el número: recuérdale el resultado, mete el precio en medio y cierra con otro beneficio. “Le queda instalado en 3 días con garantía de 2 años, son 18 mil, y le incluimos el primer mantenimiento.”"],
                ['d3_no_contra_ti', "Nunca negocies contra ti mismo: no bajes el precio antes de que te lo pidan, ni ofrezcas descuento “por si acaso”. Si nadie objetó, no hay nada que bajar."],
                ['d3_intercambia',  "Si mueves el precio, pide algo a cambio: “te ajusto si cerramos hoy” o “si te llevas también X”. Bajar por bajar le enseña al cliente a pedir más."],
                ['d3_valor',        "Si llega directo al “¿cuánto es lo menos?”, regresa al valor primero: qué le resuelves y cuánto le cuesta NO resolverlo. Luego hablas de la inversión."],
            ],
            'objeciones' => [
                ['d4_laer',        "Ante un “déjame pensarlo”, no lo dejes ir: valida (“claro, es para pensarse”) y explora (“¿qué es lo que quieres terminar de ver?”). El “lo pienso” esconde una duda concreta."],
                ['d4_aisla',       "Aísla la objeción real: “Aparte de eso, ¿hay algo más que te detenga?”. Si dice que no, ya sabes qué resolver. Si dice que sí, salió la objeción de verdad."],
                ['d4_amarra',      "Lo que “quedó en el aire” se enfría solo. Amárralo con fecha: “¿te parece si el jueves, ya con esto resuelto, lo cerramos?”."],
            ],
            'citas' => [
                ['d5_cierra_contacto', "Nunca cuelgues sin la siguiente cita. Antes de terminar: “perfecto, ¿lo vemos el martes a las 11 o mejor por la tarde?”."],
                ['d5_doble',           "No preguntes “¿quieres que nos veamos?” (invita al no). Da a elegir entre dos síes: “¿te queda hoy o mañana?”."],
                ['d5_revive',          "Si bajaron tus citas, no esperes leads nuevos: revive tu cartera. Clientes que quedaron en el aire hace semanas son citas sin gastar en publicidad."],
            ],
            'contacto' => [
                ['d6_canal',     "Si no te contesta la llamada, no insistas por lo mismo: manda WhatsApp; si no, un audio corto. Cada cliente tiene su canal."],
                ['d6_horario',   "Si marcaste a las 10 y no contestó, no vuelvas a las 10:15. Prueba otra franja (mediodía, después de las 6). Muchas veces es mal horario, no falta de interés."],
                ['d6_velocidad', "Un lead nuevo se contacta en minutos, no en horas. Entre más tardas en el primer intento, menos te contesta."],
            ],
            'cierre' => [
                ['d7_senal',       "Cuando el cliente pregunta por tiempos, garantía o formas de pago, ya está listo: deja de vender y CIERRA."],
                ['d7_pide',        "El cliente espera que TÚ des el paso. No te quedes en “cualquier cosa me avisa”: pide la venta con confianza: “¿lo dejamos apartado?”."],
                ['d7_alternativa', "No preguntes “¿lo quieres?”; asume el sí y ofrece opciones: “¿lo quieres para esta semana o la próxima?”, “¿de contado o a plazos?”."],
                ['d7_diagnostica', "Si tienes citas pero no cierras, quizá presentas antes de entender. En la cita, primero pregunta y escucha; propón solo cuando ya sabes qué le importa."],
            ],
            'seguimiento' => [
                ['d8_sistema',  "Los seguimientos no se recuerdan, se agendan. Lo que vence hoy, hoy se toca. Confiar en la memoria es como se pierden las ventas."],
                ['d8_servicio', "Dar seguimiento es un servicio, no molestar, siempre que aportes algo (un dato, una foto, una respuesta), no solo “¿ya decidió?”."],
                ['d8_enfria',   "El cliente que te dijo “la próxima semana” y no le hablaste, ya se enfrió o compró con otro. El seguimiento a tiempo ES la venta."],
            ],
            'radar' => [
                ['d9_prioridad', "El Radar te dice a quién hablarle HOY. Un caliente sin atender es una venta fácil que dejas pasar: revísalos antes que nada en el día."],
                ['d9_marca',     "El 👍/👎 no es burocracia: te ordena a quién seguir. Un caliente sin marcar es un cliente sin dueño."],
            ],
            'enfriamiento' => [
                ['d10_gancho', "Muerto no es muerto para siempre. Al que dejó de abrir, no le mandes “¿sigue interesado?” (invita al no): revívelo con algo nuevo — “me llegaron los tiempos de entrega de lo que viste, ¿te los paso?”."],
                ['d10_timing', "La mayoría no compra al primer intento por CUÁNDO, no por SI. Un frío hoy puede estar listo en dos semanas: reactívalo, no lo tires."],
            ],
            'descuento' => [
                ['d11_valor',       "Cada descuento que das sin pelear se come tu margen. Antes de ceder, recuérdale todo lo que se lleva; muchas veces el descuento sobra."],
                ['d11_intercambia', "Si descuentas, intercambia: cierre hoy, pago de contado, una compra más grande. Nunca por nada."],
                ['d11_urgencia',    "En lugar de bajar el precio, dale una razón real para decidir ya (última pieza, sube el mes que entra) — solo si es cierto."],
            ],
            'ticket' => [
                ['d14_necesidad',  "El ticket sube cuando descubres lo que el cliente NO te pidió pero necesita. Pregunta por el proyecto completo, no solo por lo que vino a cotizar."],
                ['d14_complemento',"Cuando ya dijo que sí es el mejor momento para sumar: “¿le agregamos X que combina con esto?”. El cliente comprado compra más."],
            ],
            'bien' => [
                ['ok_sube',   "Vas bien. Para el siguiente nivel: sube el ticket preguntando por el proyecto completo, no solo por lo que vinieron a cotizar."],
                ['ok_ritmo',  "Vas bien. No bajes el ritmo: agenda la siguiente cita antes de cerrar la de hoy, así el embudo nunca se seca."],
                ['ok_radar',  "Vas bien. Aprovecha el Radar al máximo: el que vuelve a abrir tu cotización es tu venta más fácil de la semana."],
            ],
        ];
    }

    /**
     * Elige el tip para el asesor a partir del expediente del reporte ($d) y
     * los handles ya mostrados. Devuelve ['handle','texto','debilidad'] o null.
     */
    public static function elegir(array $d, array $vistos = []): ?array
    {
        [$deb, $gancho] = self::_debilidad($d);
        $cat = self::catalogo();
        $tecnicas = $cat[$deb] ?? $cat['bien'];

        // Primera no mostrada (currículo). Si todas mostradas, re-cicla a la #1.
        $elegida = null;
        foreach ($tecnicas as $t) { if (!in_array($t[0], $vistos, true)) { $elegida = $t; break; } }
        if ($elegida === null) $elegida = $tecnicas[0];

        $score = (int)($d['score']['score'] ?? 50);
        $texto = self::_tono($score, $gancho) . $elegida[1];
        return ['handle' => $elegida[0], 'texto' => $texto, 'debilidad' => $deb];
    }

    /**
     * Variante para el TERMÓMETRO: detecta la debilidad desde el row de
     * usuario_score (sin queries por asesor) y rota POR DÍA (estable en el día,
     * avanza cada día). Para el dashboard y el leaderboard.
     */
    public static function desdeScore(array $s): ?array
    {
        if (($s['nivel'] ?? '') === 'nuevo') return null;      // "recopilando info" → deja el legacy
        if ((int)($s['cot_asignadas'] ?? 0) === 0) return null;

        [$deb, $gancho] = self::_debilidadScore($s);
        $cat = self::catalogo();
        $tecs = $cat[$deb] ?? $cat['bien'];
        if (!$tecs) return null;

        // Rotación diaria determinista: misma técnica todo el día, siguiente mañana.
        $uid = (int)($s['usuario_id'] ?? 0);
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

        if ($venc > 0)                         return ['seguimiento', "Se te están acumulando seguimientos vencidos. "];
        if ($cierres === 0 && $vist >= 8)      return ['cierre', "Trabajaste {$vist} cotizaciones y aún no cierras ninguna. "];
        if ($ign >= 2)                         return ['radar', "Tienes {$ign} calientes del Radar sin marcar. "];
        if ($cierres > 0 && $sdto < $cierres)  return ['descuento', "Cerraste con descuento en " . ($cierres - $sdto) . " de {$cierres}. "];
        if ($dorm > 0)                         return ['enfriamiento', "Tienes {$dorm} clientes que dejaron de volver. "];
        if ($nab > 0)                          return ['contacto', "Tienes {$nab} cotizaciones que el cliente no ha abierto. "];
        return ['bien', ""];
    }

    /** Debilidad #1 + gancho con su dato real, en orden de prioridad. */
    private static function _debilidad(array $d): array
    {
        $card = $d['card'] ?? null; $de = $d['desc'] ?? []; $ve = $d['vent'] ?? [];
        $m = $d['mesa'] ?? []; $rd = $d['radar'] ?? [];
        $noc = ($card && ($card['cont_estado'] ?? '') !== 'verde' && ($card['cont_estado'] ?? '') !== 'gris');

        // Seguimiento vencido (lo más urgente)
        if (($m['vencidas'] ?? 0) > 0)
            return ['seguimiento', "Traes {$m['vencidas']} seguimientos vencidos. "];

        // Descartes: precio > calientes > sin calificar
        $descRojo = $card && in_array($card['desc_estado'] ?? '', ['rojo','amarillo'], true);
        if ($descRojo && ($de['precio'] ?? 0) > 0 && ($de['precio'] ?? 0) >= (int)ceil(($de['n'] ?? 1) * 0.4))
            return ['precio', "{$de['precio']} de tus descartes se cayeron por precio. "];
        if ($descRojo && ($de['hot_noprecio'] ?? 0) >= 2)
            return ['calientes', "Descartaste {$de['hot_noprecio']} cotizaciones que estaban calientes. "];
        if ($descRojo)
            return ['califica', "Descartaste {$de['n']}, {$de['sincita']} sin llegar a cita. "];

        // No cierra pese a tener citas
        if ($card && ($card['conv_estado'] ?? '') === 'rojo')
            return ['cierre', "Trabajaste varias y aún no cierras ninguna. "];

        // No contesta
        if ($noc)
            return ['contacto', "" . $card['cont_txt'] . ". "];

        // Regala descuento
        if (($ve['cierres'] ?? 0) > 0 && ($ve['con_dto'] ?? 0) > ($ve['sin_dto'] ?? 0))
            return ['descuento', "{$ve['con_dto']} de tus {$ve['cierres']} ventas fueron con descuento. "];

        // Citas bajando
        if ($card && ($card['citas_estado'] ?? '') === 'amarillo')
            return ['citas', "Bajó tu ritmo de citas esta semana. "];

        // Calientes sin marcar
        if (($rd['sin_feedback'] ?? 0) >= 2)
            return ['radar', "Tienes {$rd['sin_feedback']} calientes del Radar sin revisar. "];

        // Va bien
        return ['bien', ""];
    }

    /** Tono por score (los "diferenciales"). Prefijo corto antes del gancho. */
    private static function _tono(int $score, string $gancho): string
    {
        if ($gancho === '') return '';           // caso "bien": la técnica ya trae el tono
        if ($score < 40)  return "Ojo: " . $gancho;   // firme
        return $gancho;                                // coach/refina: el gancho tal cual
    }
}
