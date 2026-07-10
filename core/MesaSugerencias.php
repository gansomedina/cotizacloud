<?php
// ============================================================
//  CotizaApp — core/MesaSugerencias.php
//  Motor de sugerencias de la Mesa de Trabajo (por capas).
//  Puro: no toca BD — recibe $ctx armado por Mesa::armar() o
//  api/mesa_estado.php. Diseño: docs/mesa_sugerencias_motor.md
//
//  Capas: 1) coherencia de la mezcla declarada (el contacto manda)
//         2) realidad del Radar (evidencia concreta)
//         3) contexto de negocio (ticket, ventana, cadencia)
//         4) modulación por arquetipo del termómetro (CÓMO, nunca QUÉ)
// ============================================================

defined('COTIZAAPP') or die;

class MesaSugerencias
{
    public static function sugerir(array $c): string
    {
        // ── Normalizar declaraciones con la regla de recencia ──
        // T_c = ts del contacto más reciente. Compromiso/postura anteriores
        // a T_c son HISTORIA: contexto, jamás estado presente.
        $con = $c['contacto'] ?? null;        // ['estado'=>..,'at'=>..]|null
        $com = $c['compromiso'] ?? null;
        $pos = $c['postura_decl'] ?? null;

        $tc      = $con ? strtotime($con['at']) : 0;
        $con_e   = $con['estado'] ?? null;
        $com_e   = $com['estado'] ?? null;
        $pos_e   = $pos['estado'] ?? null;
        $com_vig = $com && strtotime($com['at']) >= $tc;
        $pos_vig = $pos && strtotime($pos['at']) >= $tc;

        // ── Señales del Radar ──
        $bucket   = $c['bucket'] ?? null;
        $hot      = !empty($c['es_hot']);
        $visitas  = (int)($c['visitas'] ?? 0);
        $dsv      = (int)($c['dias_sin_vista'] ?? 0);
        $v24      = (int)($c['vistas_24h'] ?? 0);
        $ips7     = (int)($c['ips_7d'] ?? 0);
        $leyendo  = $v24 >= 1;
        $dormida  = $visitas > 0 && $dsv >= 7;
        // Abrió hoy/ayer/antier: cliente ACTIVO aunque no esté leyendo ahorita.
        // Regla de hechos: jamás afirmar "no abre" si abrió hace <3 días.
        $reciente = $visitas > 0 && $dsv <= 2;
        // hot "fresco": bucket caliente Y apertura real <=2d (radar_bucket_at
        // puede tener hasta 7 días — el flag solo NO prueba lectura actual)
        $hot_fresco = $hot && $reciente;
        $viva     = $leyendo || $reciente || $hot_fresco;
        // Evidencia de apertura para citar en la frase (siempre factual)
        $ev_abrio = $leyendo ? ($v24 > 1 ? "{$v24} veces hoy" : 'hoy')
                  : ($dsv === 1 ? 'ayer' : ($dsv === 2 ? 'hace 2 días' : "hace {$dsv}d"));

        // ¿El cliente reabrió DESPUÉS de la última declaración?
        $ult_decl = 0;
        foreach ([$con, $com, $pos] as $d) {
            if ($d && strtotime($d['at']) > $ult_decl) $ult_decl = strtotime($d['at']);
        }
        $uv      = !empty($c['ultima_vista_at']) ? strtotime($c['ultima_vista_at']) : 0;
        $reabrio = $ult_decl > 0 && $uv > $ult_decl;

        // Días desde una declaración
        $dias = fn(?array $d) => $d ? max(0, (int)floor((time() - strtotime($d['at'])) / 86400)) : 0;

        // ── Contexto de negocio ──
        $edad    = (int)($c['edad'] ?? 0);
        $p75     = max(1, (int)($c['p75'] ?? 30));
        $mediana = max(1, (int)($c['mediana'] ?? $p75));
        $fuera   = $edad > $p75;
        $intentos_nc = (int)($c['intentos_nc'] ?? 0);
        $ratio = null;
        if (!empty($c['ticket_empresa']) && (float)$c['ticket_empresa'] > 0) {
            $ratio = (float)$c['total'] / (float)$c['ticket_empresa'];
        }
        $alto = $ratio !== null && $ratio >= 2;

        // Rotación de variantes: determinística por cotización y por día
        // (la misma fila mantiene su frase durante el día; cambia mañana)
        $seed = ((int)($c['cot_id'] ?? 0)) * 7 + (int)date('z');
        $pk = fn(array $pool) => $pool[$seed % count($pool)];

        // Señales finas del Radar
        $momentum = $c['momentum'] ?? null;
        $fit      = (int)($c['fit_pct'] ?? 0);

        // slots que la frase compuesta acepta para la modulación
        $slots = [];
        $f = null;

        // ══ OVERLAYS (pisan todo; saltan la Capa 4) ══
        if (($c['cat'] ?? '') === 'descartada_hoy') {
            $rz = match ($c['razon_descarte'] ?? '') {
                'precio' => ' por precio', 'competencia' => ' porque se fue con otro',
                'despues' => ' porque lo dejó para después', 'no_responde' => ' porque dejó de responder',
                'no_comprador' => ' porque no era comprador', default => '',
            };
            return $pk([
                "Descartada hoy{$rz} — mañana sale de tu lista. El Radar la sigue vigilando: si el cliente la reabre, vuelve sola con ⚡.",
                "Quedó descartada hoy{$rz} y mañana sale de tu lista. El Radar sigue pendiente: si el cliente vuelve a abrirla, regresa sola con ⚡.",
                "Descartada hoy{$rz}; mañana ya no aparece en tu lista. Si el cliente la vuelve a abrir, el Radar te la regresa con ⚡.",
            ]);
        }
        if (!empty($c['revivida'])) {
            // El cliente revivió tras el descarte PERO puede haberse vuelto a callar.
            // Si ya lleva 3+ días sin abrir, no digas "mándale antes de que se enfríe"
            // (ya se enfrió): último empujón o descarte en firme.
            if ($dsv >= 3) {
                return $pk([
                    "El cliente reabrió esta cotización tras tu descarte pero ya lleva {$dsv}d sin volver — un último mensaje directo hoy, o descártala en firme.",
                    "Volvió sola después del descarte y otra vez se calló ({$dsv}d sin abrir) — hoy decides: un empujón directo o el descarte final.",
                    "Reapareció tras el descarte y lleva {$dsv}d sin abrirla de nuevo — mándale hoy un mensaje directo; si no responde, descártala en firme.",
                ]);
            }
            return match ($c['razon_descarte'] ?? '') {
                'precio' => $pk([
                    'La descartaste por precio y el cliente la volvió a abrir solo — algo cambió de su lado: mándale un mensaje hoy.',
                    'La descartaste por precio y el cliente regresó solo a la cotización — su situación cambió: escríbele hoy mismo.',
                    'Descartaste esta cotización por precio y el cliente la reabrió sin que lo buscaras — aprovecha: mándale un mensaje hoy.',
                ]),
                'no_responde' => $pk([
                    'La descartaste porque no respondía y el cliente volvió solo a la cotización — escríbele hoy; una segunda oportunidad así no se repite.',
                    'El cliente que no respondía reabrió la cotización por su cuenta — mándale un mensaje hoy antes de que se vuelva a enfriar.',
                    'La descartaste por silencio y el cliente regresó solo — escríbele hoy mismo; esta señal no va a repetirse.',
                ]),
                default => $pk([
                    'La descartaste y el cliente volvió a abrir la cotización esta semana — escríbele hoy; una señal así no se repite.',
                    'Descartaste esta cotización y el cliente regresó solo — mándale un mensaje directo hoy antes de que se enfríe otra vez.',
                    'El cliente reabrió una cotización que ya habías descartado — aprovecha hoy: mensaje directo preguntando si la retoman.',
                ]),
            };
        }
        if (!empty($c['milagro'])) {
            // "AHORA" solo si abrió HOY (v24>=1). Si el bucket se calentó hace
            // días pero el cliente no ha vuelto a abrir, NO está "viéndola ahora".
            if (!$leyendo) {
                $ev = $dsv <= 1 ? 'ayer' : "hace {$dsv}d";
                return $pk([
                    "Una cotización que ya dabas por vieja se volvió a calentar ({$ev} la última apertura) — fuera de tu ciclo pero con señal: contáctalo hoy antes de que se enfríe.",
                    "El cliente reactivó una cotización vieja (última apertura {$ev}) — está fuera de tu ciclo normal: mándale hoy un mensaje directo citando la cotización.",
                    "Cotización vieja que revivió, abierta {$ev} — no la dejes pasar: contáctalo hoy y pregúntale si la retoman.",
                ]);
            }
            if ($con_e === 'no_contesta') {
                return $pk([
                    'El cliente que dabas por perdido está viendo la cotización AHORA — mándale un mensaje en este momento citando la cotización.',
                    'No contestaba y justo ahora está leyendo la cotización — escríbele en este instante mencionando la cotización directo.',
                    'El cliente que no respondía tiene la cotización abierta AHORA — mensaje inmediato: pregúntale si le queda alguna duda.',
                ]);
            }
            return $pk([
                'La cotización ya está fuera de tu ciclo y el cliente la está viendo AHORA — mándale un mensaje en cuanto sueltes esta pantalla.',
                'El cliente está leyendo AHORA una cotización que ya dabas por vieja — escríbele en este momento; en una hora ya se enfrió.',
                'La cotización es vieja pero el cliente la tiene abierta AHORA — es el momento: mensaje inmediato preguntando si la retoman.',
            ]);
        }
        // FANTASMA: insiste sin respuesta y el cliente tampoco la abre
        if ($con_e === 'no_contesta' && $intentos_nc >= 2 && $visitas > 0 && $dsv >= 7 && !$viva) {
            return $pk([
                "Varios intentos sin respuesta y el cliente lleva {$dsv}d sin abrir la cotización — mándale un último mensaje amable y descártala con razón.",
                "El cliente no contesta y lleva {$dsv}d sin abrir la cotización — un último mensaje hoy y descártala con razón: no le dediques más tiempo.",
                "Ni respuesta ni aperturas en {$dsv}d — despídete con un último mensaje amable y descártala con razón; tu tiempo rinde más en otras cotizaciones.",
            ]);
        }

        // ══ RAMA C1 — no_contesta vigente: SOLO acciones de canal ══
        if ($con_e === 'no_contesta') {
            // PLANTÓN: compromiso histórico roto
            if ($com_e === 'compromiso') {
                if ($leyendo || $reciente) {
                    $f = $pk([
                        'Quedaron en un acuerdo, el cliente dejó de contestar pero volvió a abrir la cotización — mándale un mensaje retomando exactamente lo acordado.',
                        'El cliente no contesta pero sigue abriendo la cotización — el acuerdo sigue vivo: escríbele hoy citando lo que quedaron.',
                        'El cliente dejó de contestar y aun así volvió a la cotización — el acuerdo le sigue interesando: mensaje hoy recordando punto por punto lo acordado.',
                    ]);
                } else {
                    $f = $pk([
                        'Quedaron en un acuerdo y el cliente dejó de contestar — mándale hoy un mensaje directo: "¿sigue en pie lo que quedamos?", sin reclamar.',
                        'El cliente rompió el acuerdo con silencio — escríbele hoy sin reclamo: pregúntale si lo que quedaron sigue en pie.',
                        'Después del acuerdo el cliente ya no contestó — mensaje corto hoy: "¿seguimos con lo que quedamos?", sin presión ni reclamo.',
                    ]);
                }
                $slots['senal_viva'] = $leyendo;
            }
            // RECHAZO + EVASIÓN
            elseif ($com_e === 'propuse_no_quiso') {
                $f = $viva
                    ? $pk([
                        "El cliente te dijo que no y no contesta, pero abrió la cotización {$ev_abrio} — algo lo detiene y no te lo ha dicho: pregúntaselo directo por mensaje.",
                        "El cliente te dijo que no y te evita, pero abrió la cotización {$ev_abrio} — hay una duda que no te contó: mándale un mensaje preguntando qué lo detiene.",
                        "El cliente dijo que no, dejó de contestar y aun así abrió la cotización {$ev_abrio} — el interés sigue: escríbele hoy preguntando qué le falta para decidirse.",
                    ])
                    : $pk([
                        "El cliente te dijo que no y lleva {$dsv}d sin abrir la cotización — mándale un último mensaje sin presión y, si no responde, descártala con razón.",
                        "El cliente te dijo que no, no contesta y lleva {$dsv}d sin abrir la cotización — un último mensaje amable hoy; si sigue el silencio, descártala con razón.",
                        "El cliente dijo que no y lleva {$dsv}d sin contestar ni abrir la cotización — mándale un último mensaje hoy y descártala con razón si no responde.",
                    ]);
            }
            // EVASIVA (sin_compromiso junto a no_contesta es redundante: se absorbe)
            else {
                if ($alto && $ips7 >= 2) {
                    $f = $pk([
                        'El cliente no te contesta pero la cotización vale ' . self::x($ratio) . ' de tu venta típica y más personas la están viendo — mensaje hoy a tu contacto: ofrece resolver las dudas de todos.',
                        'Tu contacto no responde pero la cotización vale ' . self::x($ratio) . ' de tu venta típica y varias personas la revisan — escríbele hoy y ofrece una llamada para resolver dudas de todos.',
                        'Sin respuesta, pero la cotización vale ' . self::x($ratio) . ' de tu venta típica y más gente la está viendo — no lo satures de mensajes: ofrécele hoy resolver las dudas del grupo.',
                    ]);
                } elseif ($leyendo || $reciente) {
                    $vv = $ev_abrio;
                    $f = $pk([
                        "El cliente no te contesta pero abrió la cotización {$vv} — te lee aunque no responda: mándale una pregunta que se conteste con sí o no.",
                        "El cliente te evita pero volvió a abrir la cotización {$vv} — no pidas llamada: mándale UNA pregunta cerrada, de las que se contestan con sí o no.",
                        "El cliente no responde y aun así abrió la cotización {$vv} — sigue interesado: mensaje corto hoy con una sola pregunta de sí o no.",
                    ]);
                    $slots['senal_viva'] = true;
                } elseif ($reabrio) {
                    $f = $pk([
                        'El cliente no te responde pero reabrió la cotización después de tu último intento — mándale por escrito una pregunta que se conteste con sí o no.',
                        'El cliente no contesta, pero después de que lo buscaste volvió a abrir la cotización — escríbele una pregunta cerrada que responda con sí o no.',
                        'Tu mensaje no tuvo respuesta pero el cliente reabrió la cotización — sigue ahí: mándale hoy una pregunta directa de sí o no.',
                    ]);
                } elseif ($visitas === 0 && $intentos_nc >= 2 && $edad >= 7) {
                    $f = $pk([
                        "El cliente nunca ha abierto la cotización y van {$intentos_nc} intentos sin respuesta — último intento hoy por otro canal y descártala con razón.",
                        "Ni una apertura de la cotización y ya son {$intentos_nc} intentos sin que conteste — un último mensaje hoy; si sigue el silencio, descártala con razón.",
                        "La cotización sigue sin abrirse y el cliente no responde tras {$intentos_nc} intentos — haz un último intento hoy y después descártala con razón.",
                    ]);
                } elseif ($intentos_nc >= 3) {
                    $f = $pk([
                        "Van {$intentos_nc} intentos sin respuesta del cliente — cambia de canal hoy: si le llamas, escríbele; si le escribes, llámale; o busca a otra persona del mismo cliente.",
                        "Ya son {$intentos_nc} intentos y el cliente no responde — cambia de canal: otro medio, otra hora, o busca a otra persona de su empresa.",
                        "El cliente lleva {$intentos_nc} intentos sin contestarte — no repitas el mismo canal: cámbialo hoy o consigue el teléfono de otra persona del mismo cliente.",
                    ]);
                } elseif ($dormida) {
                    $f = $pk([
                        "El cliente no contesta y lleva {$dsv}d sin abrir la cotización — reinténtalo mañana por OTRO canal; si tampoco responde, prepárate para descartarla.",
                        "El cliente ni contesta ni ha abierto la cotización en {$dsv}d — mañana dale un toque por un canal distinto; si sigue el silencio, va rumbo a descarte.",
                        "Sin respuesta y {$dsv}d sin una sola apertura de la cotización — cambia de canal mañana; si tampoco funciona, descártala con razón.",
                    ]);
                } else {
                    $f = $pk([
                        'El cliente no te contestó — cambia de canal: mándale un mensaje corto con una pregunta que se conteste con sí o no; si ya le escribiste, márcale.',
                        'Sin respuesta del cliente — no repitas el mismo canal a la misma hora: otro medio, otra hora, y una pregunta de sí o no.',
                        'El cliente no respondió a tu último toque — intenta hoy por otro canal con una sola pregunta que pueda contestar con sí o no.',
                    ]);
                }
            }
        }

        // ══ PACTO — compromiso vigente ══
        elseif ($com_vig && $com_e === 'compromiso') {
            $dcom = $dias($com);
            // Reapertura anclada AL ACUERDO (no al max de declaraciones: una
            // postura redeclarada hoy no puede borrar una reapertura real)
            $reabrio_com = $uv > strtotime($com['at']);
            if ($pos_vig && $pos_e === 'en_el_aire') {
                $f = $pk([
                    'Declaraste acuerdo y "en el aire" a la vez — no pueden ser las dos: si el acuerdo es real, ponle fecha hoy; si no, corrige la postura.',
                    'Marcaste acuerdo y también "en el aire" — decide cuál es verdad: si hay acuerdo, ponle fecha hoy; si no lo hay, corrige tu postura.',
                    'Tienes un acuerdo declarado y a la vez "en el aire" — aclara hoy: un acuerdo real lleva fecha; si no la tiene, corrige la postura.',
                ]);
            } elseif ($pos_vig && $pos_e === 'objecion_precio') {
                $f = $pk([
                    'El acuerdo se puede caer por el precio — antes de ver al cliente prepara 2 formas de pagar el mismo total (anticipo y resto, o parcialidades) y dáselas a escoger.',
                    'El precio amenaza el acuerdo — llega con 2 formas de pago del mismo total y deja que el cliente escoja; no muevas el número.',
                    'El cliente aceptó pero el precio le pesa — prepara hoy 2 esquemas de pago del mismo total y preséntaselos para que elija uno.',
                ]);
                $slots['precio'] = true;
            } elseif ($pos_vig && $pos_e === 'pidio_cambios') {
                $reabrio_pos = $uv > strtotime($pos['at']);
                if (empty($c['accion_post_cambios'])) {
                    $f = $pk([
                        'El cliente aceptó pero pidió cambios — la versión nueva de la cotización sale HOY: no llegues a la cita sin ella.',
                        'Quedaron en un acuerdo y el cliente pidió cambios — manda hoy mismo la cotización actualizada; no llegues al compromiso sin ella.',
                        'Hay acuerdo pero el cliente pidió ajustes — prepara y envía HOY la versión nueva de la cotización; el acuerdo se sostiene con ella.',
                    ]);
                } elseif (!$reabrio_pos) {
                    $f = $pk([
                        'Le mandaste la cotización actualizada y el cliente no la ha abierto — avísale por mensaje que ya está lista; no asumas que la vio.',
                        'El cliente no ha abierto la versión nueva de la cotización — mándale un mensaje hoy avisando que ya se la enviaste.',
                        'La cotización nueva sigue sin abrirse — escríbele al cliente: "ya te mandé la versión con tus cambios, échale un ojo".',
                    ]);
                } else { // reabrio_pos: SÍ abrió la versión nueva
                    $f = $pk([
                        'El cliente pidió cambios y ya abrió la cotización nueva — no esperes su opinión: márcale hoy y pregunta "¿así ya cerramos?".',
                        'El cliente ya vio la versión nueva de la cotización — adelántate: llámale hoy y pregúntale directo si con esos cambios ya cerramos.',
                        'El cliente ya revisó la cotización con sus cambios — márcale hoy mismo: "¿quedó como querías? ¿cerramos?".',
                    ]);
                    $slots['cierre'] = true;
                }
            } elseif ($reabrio_com) {
                $f = $pk([
                    'Quedaron en un acuerdo y el cliente volvió a revisar la cotización — está cumpliendo su parte: confírmale el siguiente paso hoy.',
                    'Después del acuerdo el cliente reabrió la cotización — va en serio: mándale hoy el siguiente paso, no esperes a que te busque.',
                    'El cliente está revisando la cotización después del acuerdo — aprovecha: llámale hoy y amarra la fecha del siguiente paso.',
                ]);
                $slots['cierre'] = true;
            } elseif ($leyendo) {
                $f = $pk([
                    'Hay acuerdo en curso y el cliente abrió la cotización hoy — confírmale el siguiente paso hoy mismo.',
                    'El cliente abrió la cotización hoy con el acuerdo fresco — mándale el siguiente paso, no esperes a que te busque.',
                    'Acuerdo en curso y apertura de hoy — llámale y amarra la fecha del siguiente paso.',
                ]);
                $slots['cierre'] = true;
            } elseif ($dcom >= 2 && !$reciente) {
                $f = $pk([
                    "Quedaron en un acuerdo hace {$dcom}d y el cliente no ha vuelto a abrir la cotización — mándale un mensaje hoy para confirmar que sigue en pie.",
                    "El acuerdo ya tiene {$dcom}d y el cliente ni se ha asomado a la cotización — recuérdaselo hoy citando exactamente lo que quedaron.",
                    "Pasaron {$dcom}d del acuerdo sin que el cliente abra la cotización — escríbele hoy: confirma si lo acordado sigue en pie.",
                ]);
                $slots['confronta'] = true;
            } elseif ($fuera) {
                $f = $pk([
                    "El acuerdo va en serio pero la cotización ya pasó los {$p75} días en que normalmente cierras — en el siguiente contacto ponle fecha de decisión, no un \"¿cómo vamos?\".",
                    "Hay acuerdo pero la cotización ya pasó los {$p75} días en que sueles cerrar — el próximo toque es para poner fecha de cierre, no para saludar.",
                    "El cliente va en serio pero ya pasaron los {$p75} días en que sueles cerrar — llámale hoy y amarra una fecha de decisión concreta.",
                ]);
                $slots['decision'] = true;
            } else {
                $f = $pk([
                    'Hay acuerdo con el cliente — prepara hoy tu parte y ponle fecha; si en 2 días él no abre la cotización, mándale un mensaje para confirmarlo.',
                    'Amarra el acuerdo por escrito hoy: mándale al cliente qué quedó y para cuándo — lo que no queda por escrito se olvida en una semana.',
                    'El acuerdo está fresco — mándale hoy al cliente un resumen escrito de lo que quedaron y la fecha; un acuerdo sin fecha se enfría.',
                ]);
                $slots['cierre'] = true;
            }
        }

        // ══ RECHAZO — propuso y no quiso ══
        elseif ($com_vig && $com_e === 'propuse_no_quiso') {
            if ($pos_vig && $pos_e === 'decidiendo') {
                $f = $pk([
                    'Propusiste cerrar, el cliente no quiso y dice que lo piensa — pregúntale qué tendría que pasar para que diga sí, y ponle fecha a esa respuesta.',
                    'El cliente rechazó tu propuesta y "lo está pensando" — pregúntale directo qué le falta para decir sí y acuerden una fecha para su respuesta.',
                    'El cliente no aceptó y quedó en pensarlo — llámale y pregúntale qué necesita para decidirse; cierra la llamada con fecha para su respuesta.',
                ]);
                $slots['decision'] = true;
            } elseif ($pos_vig && $pos_e === 'objecion_precio') {
                if ($bucket === 'validando_precio' && $viva) {
                    $f = $pk([
                        'El cliente no quiso comprometerse y está revisando el precio dentro de la cotización — la objeción es real: llégale hoy con 2 formas de pago.',
                        'El cliente rechazó el compromiso pero está comparando el precio en la cotización — le interesa: prepara opciones de pago y márcale hoy.',
                        'El cliente no quiso cerrar pero sigue metido en los precios de la cotización — el precio le importa: ofrécele hoy formas de pago, no bajes el número.',
                    ]);
                } elseif ($viva) {
                    $f = $pk([
                        'El cliente dijo que no por precio pero relee la cotización completa — no bajes el precio: reenvíasela hoy resaltando todo lo que incluye por ese precio.',
                        'El cliente no quiso por precio y aun así sigue leyendo toda la cotización — no bajes el número: mejora cómo presentas el valor y reenvíasela hoy.',
                        'El cliente rechazó por precio pero estudia la cotización de arriba a abajo — le interesa: reenvíasela hoy resaltando lo que incluye, sin tocar el precio.',
                    ]);
                } else {
                    $f = $pk([
                        'El cliente dijo que no por precio y no ha vuelto a abrir la cotización — el problema no es el precio, es el interés: pregúntale qué le falta a la propuesta.',
                        'El cliente dijo caro y dejó de abrir la cotización — el precio es pretexto: llámale hoy y pregunta qué necesitaría la propuesta para interesarle.',
                        'El cliente se quejó del precio pero ni abre la cotización — el interés se apagó: pregúntale directo qué le falta, no le ofrezcas rebaja.',
                    ]);
                    // sin slot precio: aquí la tesis es "el precio es pretexto"
                }
                if ($viva) $slots['precio'] = true; // objeción real solo con señal viva
            } elseif ($pos_vig && $pos_e === 'pidio_cambios') {
                $f = empty($c['accion_post_cambios'])
                    ? $pk([
                        'El cliente no la quiso así como está y pidió cambios — la cotización nueva es tu jugada: mándala HOY.',
                        'El cliente rechazó la versión actual y pidió cambios — prepara y envía HOY la cotización actualizada; ahí está la venta.',
                        'El cliente no aceptó la cotización como está y pidió ajustes — hazlos HOY y mándale la versión nueva; cada día de espera lo enfría.',
                    ])
                    : $pk([
                        'El cliente no quiso la versión anterior y ya le mandaste la nueva — pregúntale directo si con estos cambios ya le entra.',
                        'El cliente ya tiene la cotización actualizada después de rechazar la anterior — márcale hoy: "¿con estos cambios sí cerramos?".',
                        'La cotización nueva ya está en manos del cliente — no esperes: llámale hoy y pregunta si así ya se decide.',
                    ]);
            } elseif ($hot_fresco && in_array($bucket, ['onfire', 'inminente', 'probable_cierre'], true)) {
                $f = $pk([
                    'El cliente te dijo que no pero el Radar trae la cotización ' . self::bnom($bucket) . ' — interés sí hay: pregúntale de frente qué le falta para cerrar.',
                    'El cliente rechazó el compromiso y aun así el Radar marca la cotización ' . self::bnom($bucket) . ' — el freno no es interés: llámale hoy y pregunta directo qué lo detiene.',
                    'El cliente dijo que no pero el Radar tiene la cotización ' . self::bnom($bucket) . ' — algo no te ha contado: márcale hoy y pregúntale qué tendría que cambiar.',
                ]);
                $slots['confronta'] = true;
            } elseif ($viva) {
                $f = $pk([
                    'Después de decirte que no, el cliente sigue metido en la cotización — algo lo detiene y no te lo dijo: pregúntale qué tendría que cambiar para que diga sí.',
                    'El cliente rechazó pero sigue leyendo la cotización — el interés está vivo: márcale hoy y pregúntale directo qué lo frena.',
                    'El cliente dijo que no y aun así vuelve a la cotización — hay una duda escondida: escríbele hoy preguntando qué le falta para animarse.',
                ]);
                $slots['confronta'] = true;
            } else {
                $f = $pk([
                    'El cliente no quiso y no sabes por qué — tu siguiente llamada es para averiguar la razón, no para intentar cerrar.',
                    'Propusiste, el cliente dijo que no y no te dio el motivo — llámale hoy solo para entender qué lo detuvo; el cierre viene después.',
                    'Hubo un no sin explicación — antes de volver a proponer, pregúntale al cliente qué fue lo que no le convenció.',
                ]);
            }
        }

        // ══ NADA CONCRETO — hablaron sin pacto ══
        elseif ($com_vig && $com_e === 'sin_compromiso') {
            if ($pos_vig && $pos_e === 'en_el_aire') {
                if ($bucket === 'enfriandose' || $dormida) {
                    $f = $pk([
                        'Hablaron sin quedar en nada y el cliente abre la cotización cada vez menos — pregúntale de frente: ¿le entramos o lo dejamos? Un no también te sirve.',
                        'La plática no aterrizó y el cliente se está enfriando — mensaje directo hoy: "¿seguimos con esto o lo dejamos?"; cualquier respuesta te sirve.',
                        'No quedaron en nada y el interés del cliente va de bajada — pregúntale hoy sin rodeos si sigue interesado; un no claro vale más que el silencio.',
                    ]);
                    $slots['confronta'] = true;
                } else {
                    $f = $pk([
                        'Hablaron sin quedar en nada y tú mismo la ves dudosa — deja de sondear: la siguiente llamada lleva UNA propuesta cerrada o una fecha límite.',
                        'La plática quedó abierta y tú no la ves firme — no llames a "ver cómo va": llámale al cliente con una propuesta concreta o ponle fecha límite.',
                        'No hubo acuerdo y tú dudas del cliente — prepara UNA propuesta cerrada hoy y preséntasela; sin propuesta no hay decisión.',
                    ]);
                    $slots['decision'] = true;
                }
            } elseif ($pos_vig && $pos_e === 'decidiendo') {
                if ($dormida && !$reabrio) {
                    $f = $pk([
                        "Dices que el cliente está decidiendo pero lleva {$dsv}d sin abrir la cotización — eso no es decidir, es enfriarse: pídele una definición esta semana.",
                        "Marcaste \"decidiendo\" pero el cliente tiene {$dsv}d sin ver la cotización — se está enfriando: llámale y ponle fecha a la decisión.",
                        "El cliente \"decide\" desde hace {$dsv}d sin abrir la cotización — nadie decide sin releer: contáctalo hoy y pide una respuesta con fecha.",
                    ]);
                    $slots['confronta'] = true;
                } elseif ($reabrio || $leyendo) {
                    $f = $pk([
                        'Hablaron sin acuerdo pero el cliente está decidiendo y relee la cotización — agenda hoy la llamada de decisión, no esperes su veredicto.',
                        'El cliente lo está pensando y sigue abriendo la cotización — proponle hoy una llamada para decidir juntos; no esperes a que él te busque.',
                        'El cliente está decidiendo con la cotización en la mano — llámale hoy y ofrécele resolver la última duda; la decisión se cierra contigo enfrente.',
                    ]);
                    $slots['decision'] = true;
                } else {
                    $f = $pk([
                        'Hablaron sin acuerdo y el cliente "lo está pensando" — mándale hoy algo nuevo que lo ayude a decidir: un caso, una foto, una fecha límite.',
                        'El cliente quedó en pensarlo y no hay más señales — no esperes otra semana: mándale hoy un dato nuevo o una fecha límite para decidir.',
                        'Un "lo estoy pensando" sin movimiento es un no lento — dale hoy al cliente una razón nueva para decidir: beneficio, plazo o fecha que expira.',
                    ]);
                }
            } elseif ($pos_vig && $pos_e === 'objecion_precio') {
                $f = ($bucket === 'validando_precio' && $viva)
                    ? $pk([
                        'Hablaron, el precio quedó como duda y el cliente lo está revisando en la cotización — la objeción es real: llégale con formas de pago.',
                        'El cliente quedó dudando del precio y ahora lo valida dentro de la cotización — prepárale hoy opciones de pago; no defiendas el número.',
                        'La duda de precio va en serio: el cliente está comparando los totales de la cotización — márcale hoy con esquemas de pago listos.',
                    ])
                    : $pk([
                        'Hablaron y el precio quedó de pretexto — no des un número nuevo por teléfono: mándale al cliente una propuesta cerrada por escrito.',
                        'El precio quedó como pretexto tras la plática — no negocies por teléfono: mándale hoy una propuesta cerrada por escrito.',
                        'Del precio solo hay quejas, no números — ponlo por escrito: mándale hoy al cliente una propuesta cerrada y después llámale por su respuesta.',
                    ]);
                $slots['precio'] = true;
            } elseif ($pos_vig && $pos_e === 'pidio_cambios') {
                $f = empty($c['accion_post_cambios'])
                    ? $pk([
                        'Hablaron y el cliente pidió cambios — la cotización nueva sale HOY; el interés se muere esperando.',
                        'El cliente pidió ajustes en la plática — manda HOY la cotización actualizada; cada día sin versión nueva enfría la venta.',
                        'El cliente pidió cambios y la cotización sigue igual — hazlos hoy y mándasela; el cliente decide con la versión nueva en la mano.',
                    ])
                    : $pk([
                        'Le mandaste la cotización nueva después de la plática — confirma que el cliente la abrió y pregúntale si así ya cierran.',
                        'La versión nueva de la cotización ya está enviada — mándale hoy un mensaje al cliente: "¿ya la viste? ¿así ya cerramos?".',
                        'El cliente ya tiene la cotización con sus cambios — no esperes su opinión: márcale hoy y pregunta si con eso se decide.',
                    ]);
            } elseif ($bucket === 'multi_persona' && $ips7 >= 2) {
                $f = $pk([
                    "Hablaron sin quedar en nada y hay {$ips7} dispositivos viendo la cotización — tu contacto no decide solo: proponle una reunión con todos los que deciden.",
                    "La cotización se está viendo desde {$ips7} dispositivos y tu contacto no se comprometió — hay más gente decidiendo: pide hoy una reunión con todos.",
                    "Tu contacto no cerró y la cotización la revisan {$ips7} dispositivos — el que decide es otro: ofrece hoy presentarla a todo el grupo, con garantía por escrito.",
                ]);
            } elseif ($viva) {
                $f = $pk([
                    'Después de la plática el cliente siguió leyendo la cotización — le interesa pero algo no te dijo: márcale y ofrécele dos fechas de arranque para que escoja.',
                    'La plática no aterrizó pero el cliente siguió estudiando la cotización — hay interés atorado: pregúntale directo qué lo detiene y dale dos opciones de arranque.',
                    'Hablaron sin acuerdo y aun así el cliente relee la cotización — no lo dejes enfriar: llámale hoy y proponle dos fechas para arrancar.',
                ]);
                $slots['decision'] = true;
            } else {
                $f = $pk([
                    'No repitas la plática: mándale al cliente una propuesta por escrito con fecha y anticipo definidos, y que tu siguiente llamada solo pida el sí o el no.',
                    'La siguiente llamada necesita algo nuevo — mándale antes al cliente una propuesta cerrada con fecha y anticipo, y llama solo a confirmarla.',
                    'Otra plática igual no cierra nada — mándale hoy al cliente una propuesta escrita con fecha y anticipo; la siguiente llamada es solo para el sí o el no.',
                ]);
                $slots['decision'] = true;
            }
        }

        // ══ TOQUE INCOMPLETO — hablamos sin desenlace ══
        elseif ($con_e === 'hablamos' && !$com_vig && !$pos_vig) {
            $f = $viva
                ? $pk([
                    'Hablaste con el cliente, no registraste en qué quedaron, y él sigue entrando a la cotización — captura el desenlace y dale el siguiente toque hoy.',
                    'El cliente sigue revisando la cotización pero no declaraste cómo terminó la plática — registra en qué quedaron y contáctalo hoy.',
                    'El cliente sigue moviéndose en la cotización y tú no capturaste el resultado de la plática — decláralo ahora y dale el siguiente toque hoy mismo.',
                ])
                : $pk([
                    'Hablaron — ¿y quedaron en algo? Registra el desenlace; si no quedaron en nada, esa es tu siguiente llamada.',
                    'Hubo plática pero no declaraste el resultado — captúralo hoy; si no quedaron en nada, llama al cliente para aterrizar un acuerdo.',
                    'La plática quedó sin registro — declara en qué terminó; y si terminó en nada, tu siguiente llamada es para quedar en algo concreto.',
                ]);
        }

        // ══ JUICIO SIN TOQUE — postura vigente (sola o con hablamos sin compromiso) ══
        elseif ($pos_vig) {
            $f = self::juicio_sin_toque($pos_e, $c, $viva, $dormida, $reabrio, $dsv, $bucket, $slots, $pk);
        }

        // ══ VIRGEN — nada declarado: fallback por Radar/categoría ══
        if ($f === null) {
            $f = self::virgen($c, $bucket, $hot_fresco, $viva, $dormida, $dsv, $edad, $p75, $mediana, $ips7, $slots, $pk, $momentum, $fit);
        }

        // ══ CAPA 3 extra: alto importe — SOLO cuando nada está declarado
        // (las filas ya declaradas traen guía específica; no meter ruido)
        if ($alto && !$con && !$com && !$pos && !str_contains($f, 'venta típica') && mb_strlen($f) < 130) {
            $f .= ' Vale ' . self::x($ratio) . ' de tu venta típica.';
        }

        // ══ CAPA 4: modulación por arquetipo (CÓMO, nunca QUÉ) ══
        return self::modular($f, (string)($c['arquetipo'] ?? ''), $slots, $viva, $dormida, $pk);
    }

    // ── Postura declarada sin (o con) contacto — auditada contra el Radar ──
    private static function juicio_sin_toque(?string $pos_e, array $c, bool $viva, bool $dormida, bool $reabrio, int $dsv, ?string $bucket, array &$slots, callable $pk): string
    {
        switch ($pos_e) {
            case 'decidiendo':
                if ($dormida && !$reabrio) {
                    $slots['confronta'] = true;
                    return $pk([
                        "Dices que el cliente está decidiendo pero lleva {$dsv}d sin abrir la cotización — eso no es decidir, es enfriarse: pídele una definición esta semana.",
                        "Marcaste \"decidiendo\" pero el cliente tiene {$dsv}d sin ver la cotización — se está enfriando: llámale y ponle fecha a la decisión.",
                        "El cliente \"decide\" desde hace {$dsv}d sin abrir la cotización — nadie decide sin releer: contáctalo hoy y pide una respuesta con fecha.",
                    ]);
                }
                if ($bucket === 'validando_precio' && $viva) { $slots['precio'] = true; return $pk([
                    'El cliente está decidiendo y clavado en los precios de la cotización — te está comparando AHORA: mándale hoy tu razón #1 para elegirte.',
                    'El cliente está decidiendo y revisa una y otra vez los precios — te compara con otros: mándale hoy lo que solo tú le ofreces.',
                    'El cliente compara los precios de la cotización mientras decide — adelántate: mensaje hoy con tu mejor argumento para que te elija.',
                ]); }
                if ($bucket === 'multi_persona' && $viva)    { return $pk([
                    'El cliente está decidiendo y varias personas revisan la cotización — arma a tu contacto para defenderte por dentro: mándale garantía y proceso por escrito.',
                    'Varias personas están viendo la cotización mientras el cliente decide — tu contacto te defiende allá adentro: dale hoy garantía y pasos por escrito.',
                    'La decisión no es de uno solo: varias personas ven la cotización — mándale hoy a tu contacto un resumen con garantía para que lo presente.',
                ]); }
                if ($viva) { $slots['decision'] = true; return $pk([
                    'El cliente está decidiendo y relee la cotización — que decida contigo enfrente: agenda hoy la llamada de decisión, no esperes el veredicto.',
                    'El cliente está decidiendo con la cotización abierta — llámale hoy y proponle resolver la última duda juntos; no esperes su respuesta a solas.',
                    'El cliente relee la cotización mientras decide — adelántate: agenda hoy una llamada corta para cerrar la decisión.',
                ]); }
                return $pk([
                    'Marcaste "decidiendo" pero el cliente no ha vuelto a abrir la cotización — nadie decide sin releer: mándale hoy algo nuevo que lo haga decidir.',
                    'Dices que el cliente decide pero la cotización está fría — sin lecturas no hay decisión: dale hoy un motivo nuevo (dato, foto o fecha límite).',
                    'El cliente "está decidiendo" sin abrir la cotización — eso es un no lento: mándale hoy una razón nueva para retomarla.',
                ]);
            case 'objecion_precio':
                if ((int)($c['visitas'] ?? 0) === 0) return $pk([
                    'El cliente te dijo caro sin haber abierto la cotización ni una vez — está regateando de oídas: primero logra que la abra, luego hablan de precio.',
                    'El cliente dijo que está cara y jamás ha abierto la cotización — no negocies a ciegas: mándale hoy un motivo para que la lea primero.',
                    'El cliente se quejó del precio sin ver la cotización — el reclamo es de oídas: pídele hoy que la revise antes de hablar de números.',
                ]);
                if ($bucket === 'validando_precio' && $viva) { $slots['precio'] = true; return $pk([
                    'El cliente te dijo caro y está validando el precio dentro de la cotización — la objeción es real: llégale con formas de pago, no defiendas el número.',
                    'El cliente dijo caro y se la pasa revisando los totales de la cotización — le interesa: prepara hoy 2 esquemas de pago del mismo total.',
                    'La queja de precio va en serio: el cliente compara los números de la cotización — márcale hoy con opciones de pago listas.',
                ]); }
                if ($viva) { $slots['precio'] = true; return $pk([
                    'El cliente dijo caro pero relee la cotización completa — no bajes el precio: reenvíasela hoy resaltando todo lo que incluye por ese precio.',
                    'El cliente dijo que está cara y aun así estudia toda la cotización — no bajes el precio: mejora cómo presentas el valor y reenvíasela hoy.',
                    'El cliente se quejó del precio pero lee la cotización de punta a punta — el interés está: reenvíasela hoy resaltando lo que incluye, sin tocar el precio.',
                ]); }
                return $pk([
                    'El cliente te dijo caro y no ha vuelto a abrir la cotización — el problema no es el precio, es el interés: pregúntale qué le falta a la propuesta.',
                    'El cliente dijo caro y dejó de ver la cotización — el precio es pretexto: llámale hoy y pregunta qué necesitaría para interesarle de verdad.',
                    'La queja fue el precio pero el cliente ni abre la cotización — no ofrezcas rebaja: pregúntale hoy qué le falta a la propuesta para convencerlo.',
                ]);
            case 'pidio_cambios':
                if (empty($c['accion_post_cambios'])) return $pk([
                    'El cliente pidió cambios y la cotización sigue igual — la versión nueva sale HOY: una cotización que se mueve, cierra.',
                    'El cliente pidió ajustes y no has actualizado la cotización — hazlos hoy y mándasela; el cliente decide sobre la versión nueva.',
                    'Los cambios que pidió el cliente siguen pendientes — sácalos HOY; cada día sin versión nueva enfría la venta.',
                ]);
                if ($reabrio) { $slots['cierre'] = true; return $pk([
                    'El cliente ya abrió la cotización con sus cambios — no esperes su opinión: márcale hoy y pregunta "¿así ya cerramos?".',
                    'El cliente ya vio la versión nueva de la cotización — adelántate: llámale hoy y pregúntale si con esos cambios ya se decide.',
                    'El cliente ya revisó la cotización actualizada — mensaje o llamada hoy: "¿quedó como querías? ¿cerramos?".',
                ]); }
                return $pk([
                    'Le mandaste la cotización actualizada y el cliente no la ha abierto — avísale por mensaje que ya está lista; no asumas que la vio.',
                    'La versión nueva de la cotización sigue sin abrirse — mándale hoy un mensaje al cliente avisando que ya se la enviaste.',
                    'El cliente no ha visto la cotización con sus cambios — escríbele hoy: "ya te mandé la versión nueva, échale un ojo".',
                ]);
            case 'en_el_aire':
                if ($viva) { $slots['confronta'] = true; return $pk([
                    'Tú ves la venta dudosa pero el cliente está releyendo la cotización — el que duda eres tú: márcale hoy y sal de la duda.',
                    'Marcaste la venta como dudosa y el cliente sigue abriendo la cotización — el interés existe: llámale hoy y pregúntale directo en qué va.',
                    'Tú la das por dudosa pero el cliente vuelve a la cotización — no adivines: contacta hoy al cliente y pregunta si siguen.',
                ]); }
                if ($dormida) return $pk([
                    "El cliente lleva {$dsv}d sin abrir la cotización y tú mismo dudas de la venta — ponle fecha límite hoy o descártala; otra semana igual no la revive.",
                    "Van {$dsv}d sin una sola apertura y tú ya la ves dudosa — mándale hoy un mensaje con fecha límite; si el cliente no responde, descártala con razón.",
                    "La cotización tiene {$dsv}d sin abrirse y tu propia postura es de duda — decide hoy: fecha límite al cliente o descarte con razón.",
                ]);
                return $pk([
                    'Tienes la venta en duda — una pregunta directa al cliente hoy vale más que otra semana de espera: pregúntale qué falta para decidir.',
                    'La venta sigue sin rumbo — mándale hoy al cliente una pregunta concreta: qué necesita para tomar la decisión.',
                    'No sabes en qué va el cliente — sal de la duda hoy: mensaje directo preguntando qué le falta para decidirse.',
                ]);
        }
        return $pk([
            'Actualiza el estado de esta cotización — captura hoy el resultado de tu último toque.',
            'Dale un toque hoy a esta cotización y registra en qué quedó el cliente.',
            'Esta cotización necesita un toque hoy — contáctalo y actualiza su estado al terminar.',
        ]);
    }

    // ── Sin declaración: la voz del Radar por bucket/categoría ──
    private static function virgen(array $c, ?string $bucket, bool $hot, bool $viva, bool $dormida, int $dsv, int $edad, int $p75, int $mediana, int $ips7, array &$slots, callable $pk, ?string $momentum, int $fit): string
    {
        $cat = $c['cat'] ?? 'trabajo';
        $fuera = $edad > $p75;
        if ($cat === 'interes_muriendo') {
            $slots['dormida'] = true;
            // Fuera de ventana Y dormida: rescatarla ya no es el consejo honesto
            if ($fuera && $dormida) {
                return $pk([
                    "Marcaste 👍 pero el cliente lleva {$dsv}d sin abrir la cotización y ya salió de tu ventana — último intento hoy: motivo nuevo con fecha límite, o descártala.",
                    "Le pusiste 👍 pero van {$dsv}d sin aperturas y la cotización ya está fuera de tu ventana — mándale hoy un motivo nuevo con fecha límite; si calla, descártala.",
                    "El 👍 ya no aguanta: {$dsv}d sin que el cliente abra la cotización y fuera de tu ventana — hoy un último mensaje con fecha límite o descarte con razón.",
                ]);
            }
            return $pk([
                'Marcaste 👍 pero el cliente se está apagando — mándale hoy un motivo concreto para retomar la cotización, o corrige tu marca.',
                'Tu 👍 ya no coincide con el cliente: cada vez abre menos la cotización — dale hoy una razón nueva para volver, o quita el 👍.',
                'El interés del cliente va de bajada aunque marcaste 👍 — escríbele hoy con algo nuevo (foto, ajuste, fecha) o corrige tu marca.',
            ]);
        }
        if ($cat === 'ultimo_tramo') {
            $slots['decision'] = true;
            return $pk([
                "Marcaste 👍 pero la cotización ya va en día {$edad} y pasó tu ventana de cierre — el siguiente toque pide definición al cliente, no otra plática.",
                "El 👍 sigue pero la cotización va en día {$edad}, ya fuera de tu ventana — llámale hoy al cliente y pide una definición concreta.",
                "La cotización llegó al día {$edad}, ya fuera de tu ventana — este toque es para que el cliente defina: fecha, anticipo o descarte.",
            ]);
        }
        // Evidencia propia de ESTA fila (cada cotización cita sus números)
        $v24 = (int)($c['vistas_24h'] ?? 0);
        $v7  = (int)($c['vistas_7d'] ?? 0);
        $ev  = null;
        if ($v24 >= 2)      $ev = "El cliente la abrió {$v24} veces hoy";
        elseif ($v24 === 1) $ev = 'El cliente la abrió hoy';
        elseif ($dsv === 1) $ev = 'El cliente la abrió ayer';
        elseif ($v7 >= 3)   $ev = "El cliente la abrió {$v7} veces esta semana";
        elseif ($dsv >= 2 && $dsv < 7 && (int)($c['visitas'] ?? 0) > 0) $ev = "El cliente lleva {$dsv}d sin volver a abrirla";

        // Por bucket (matiz del playbook) — solo si el calor es real.
        // probable_cierre agrupa varios motivos: pc_source dice CUÁL fue.
        if ($hot) {
            $slots['senal_viva'] = true;
            $b = $bucket;
            if ($b === 'probable_cierre' && !empty($c['pc_source'])) $b = $c['pc_source'];
            [$intro, $accion, $con_num] = match ($b) {
                'onfire'               => ['leyó la cotización completa y volvió más de una vez', $pk(['no reexpliques nada: contacto de cierre HOY.', 'ya no vendas: pregúntale con cuál fecha arrancan.', 'deja la labor de venta: llámale hoy a cerrar.']), false],
                'inminente'            => ['se fue, lo pensó y regresó a la cotización: está decidiendo YA', $pk(['pregúntale directo hoy qué le falta para arrancar.', 'llámale hoy: "¿qué falta para que arranquemos?".', 'contáctalo hoy con una sola pregunta: ¿arrancamos o no?']), false],
                'validando_precio'     => ['está clavado en los totales de la cotización', $pk(['llega con la estructura de pago lista, no defiendas el número.', 'arma dos formas de pagar el mismo total y dáselas a escoger.', 'ofrécele hoy esquemas de pago; el número no se toca.']), false],
                'decision_activa'      => ['va y viene con la cotización, la está consultando', $pk(['ponte disponible hoy y pregúntale si alguien más participa en la decisión.', 'mensaje hoy: ofrécete a resolver dudas y pregunta quién más decide.', 'llámale hoy, queda a la mano y confirma si decide solo o con alguien.']), false],
                'prediccion_alta'      => ['su patrón de lectura se parece al de los que sí compran', $pk(['contacto suave hoy: confirma el interés, no lo asumas.', 'un toque ligero hoy — pregúntale qué le pareció, sin presionar.', 'mensaje corto hoy preguntando cómo ve la propuesta; sin presión.']), false],
                'lectura_comprometida' => ['leyó la cotización a fondo a la primera y se detuvo en el precio', $pk(['esta señal dura horas: contáctalo HOY.', 'no dejes pasar el día: llámale o escríbele HOY.', 'esa atención se enfría en horas — búscalo hoy mismo.']), false],
                'multi_persona'        => [($ips7 >= 2 ? "hay {$ips7} personas viendo la cotización" : 'varias personas están viendo la cotización'), $pk(['tu contacto no decide solo: propón reunión con todos y garantía por escrito.', 'pide hoy una reunión con todos los que deciden y manda garantía por escrito.', 'ofrécete a presentarla a todo el grupo hoy, con garantía por escrito.']), true],
                'alto_importe'         => ['la cotización está arriba de lo que normalmente vendes', $pk(['venta grande: mándale hoy la garantía por escrito y los tiempos de entrega, sin presionar el cierre.', 'mándale hoy por escrito la garantía y los tiempos de entrega, sin presionar el cierre.', 'mándale hoy la garantía por escrito y explícale paso a paso cómo trabajas, sin prisas.']), false],
                're_enganche_caliente' => ['regresó directo a los precios tras días fuera', $pk(['está comparando opciones para decidir: contáctalo HOY con seguridad.', 'anda en la comparación final: márcale hoy y dale certeza con garantía y tiempos.', 'está por decidir entre opciones: aparece hoy con tu mejor argumento.']), false],
                default                => ['el Radar trae la cotización caliente y no la has calificado', $pk(['dale el toque hoy y declara cómo lo ves.', 'contáctalo hoy y registra en qué quedó.', 'búscalo hoy y captura el resultado del toque.']), false],
            };
            if (in_array($b, ['validando_precio', 're_enganche_caliente'], true)) $slots['precio'] = true;
            // Componer: evidencia POSITIVA + motivo + acción (1 número máx).
            // Nunca pegar "lleva Nd sin volver" a un intro caliente en presente.
            if ($ev && !$con_num && !str_contains($ev, 'sin volver')) return $ev . ' y ' . $intro . ' — ' . $accion;
            return ucfirst($intro) . ' — ' . $accion;
        }
        return match ($bucket) {
            're_enganche', 'regreso' => $pk([
                'El cliente volvió a abrir la cotización tras días de silencio — retómalo sin presión: pregúntale si algo cambió de su lado.',
                'Después de días sin señales, el cliente se asomó a la cotización — mensaje natural hoy: "¿cómo va el proyecto? ¿algo cambió?".',
                'El cliente regresó a la cotización después del silencio — aprovecha hoy: pregúntale si la retoman, sin presionar.',
            ]),
            'revivio'            => $pk([
                'La cotización revivió sola: el cliente la volvió a abrir después de semanas — dale continuidad sin presión: pregúntale si el proyecto cambió.',
                'Tras semanas muerta, el cliente reabrió la cotización — retómala con calma: mensaje hoy preguntando cómo va su proyecto.',
                'El cliente resucitó la cotización después de semanas — no lo presiones: pregúntale hoy si el plan sigue o algo cambió.',
            ]),
            'vistas_multiples'   => $pk([
                'El cliente abre la cotización varias veces pero no la lee a fondo — mándale un mensaje muy corto que busque respuesta, no cierre.',
                'Puras aperturas rápidas de la cotización, sin lectura completa — mensaje breve hoy con una pregunta fácil de contestar.',
                'El cliente se asoma a la cotización pero no la estudia — escríbele algo corto hoy: una pregunta simple, sin intentar cerrar.',
            ]),
            'hesitacion'         => $pk([
                'El cliente duda pero no suelta la cotización — pregúntale hoy qué lo detiene y quítale presión.',
                'Hay duda: el cliente sigue abriendo la cotización sin decidirse — mensaje hoy preguntando qué le preocupa, sin presionar.',
                'El cliente no se decide pero tampoco abandona la cotización — llámale hoy, pregunta qué lo frena y dale confianza.',
            ]),
            'sobre_analisis'     => $pk([
                'El cliente lleva días dándole vueltas a la cotización con toda la información en mano — pregúntale directo qué duda tiene; no le mandes más información.',
                'El cliente ya tiene todo y sigue analizando la cotización — no le mandes nada nuevo: pregúntale hoy cuál es la duda que lo detiene.',
                'Días de análisis sobre la misma cotización — más información no ayuda: llámale hoy al cliente y pregúntale directo qué objeción trae.',
            ]),
            'comparando'         => $pk([
                'La cotización se está abriendo desde varios lugares: el cliente está comparando — ayúdale con garantía y tiempos claros, no bajando el precio.',
                'Hay señales de comparación: la cotización se ve desde distintos lados — dale hoy certeza al cliente (garantía, tiempos de entrega) en vez de tocar el precio.',
                'El cliente te está comparando con otros — mándale hoy garantía y tiempos por escrito; el precio no se mueve.',
            ]),
            'enfriandose'        => $pk([
                'El cliente se está enfriando: cada vez abre menos la cotización — empieza hoy una serie de 3 toques con motivo nuevo en cada uno.',
                'El interés del cliente va de bajada — plan de 3 toques desde hoy: cada mensaje con algo nuevo, no con "¿cómo vamos?".',
                'La cotización se apaga poco a poco — dale hoy el primer toque de una serie de 3, cada uno con una razón nueva para contestar.',
            ]),
            default              => match (true) {
                $fuera && $dormida => $pk([
                    "El cliente lleva {$dsv}d sin abrir la cotización y ya salió de tu ventana — última jugada hoy: motivo nuevo con fecha límite; si no responde, descártala.",
                    "Van {$dsv}d sin aperturas y la cotización ya rebasó tu ventana — un último mensaje hoy con fecha límite; si sigue el silencio, descártala con razón.",
                    "El cliente tiene {$dsv}d sin abrir la cotización y el tiempo ya se pasó — hoy decides: mensaje final con fecha límite o descarte con razón.",
                ]),
                $fuera             => $pk([
                    "La cotización ya pasó los {$p75} días en que normalmente cierras — el siguiente toque define: fecha de decisión o descarte, no otro \"¿cómo vamos?\".",
                    "Ya pasaron los {$p75} días en que sueles cerrar — llámale hoy al cliente para poner fecha de decisión; si no hay fecha, va para descarte.",
                    "Esta cotización ya tardó más de tus {$p75} días normales — el próximo contacto le pide al cliente una fecha, o se descarta.",
                ]),
                $momentum === 'down' && !$dormida => $pk([
                    'El cliente abre la cotización cada vez menos — recupéralo con algo nuevo (una foto, un ajuste, un beneficio), no con un "¿ya lo pensó?".',
                    'El interés del cliente baja lectura tras lectura — tu siguiente mensaje necesita una razón nueva para que reabra la cotización.',
                    'Cada apertura de la cotización es más corta y más espaciada — mándale hoy algo distinto: un cambio, una foto o una fecha que expira.',
                ]),
                $dormida           => $pk([
                    "El cliente lleva {$dsv}d sin volver a abrir la cotización — dale hoy un motivo nuevo para reabrirla; no un \"¿ya lo viste?\".",
                    "El cliente lleva {$dsv}d sin asomarse a la cotización — mándale algo que lo obligue a reabrirla: un cambio, una foto, una fecha que expira.",
                    "Van {$dsv}d sin una sola apertura de la cotización — escríbele hoy con una razón nueva para verla; el recordatorio solo ya no funciona.",
                ]),
                $fit >= 60         => $pk([
                    'El patrón de lectura del cliente se parece al de los que sí compran (FIT alto) — no dejes enfriar la cotización: toque suave hoy.',
                    'El FIT está alto: el cliente lee la cotización como leen los que compran — mándale hoy un mensaje ligero para mantenerla viva.',
                    'El cliente trae FIT alto: su forma de leer la cotización apunta a compra — dale un toque suave hoy, sin presión.',
                ]),
                $edad <= $mediana  => $pk([
                    'La cotización está en tu mejor ventana de cierre — dale hoy un toque que termine en algo concreto: fecha, visita o anticipo.',
                    "La mayoría de tus ventas se cierran antes del día {$mediana} — el toque de hoy busca un acuerdo con el cliente, no un \"ahí la lleva\".",
                    'Estos son los días donde tus ventas se cierran — contacta hoy al cliente y sal con un compromiso chico: fecha, visita o anticipo.',
                ]),
                default            => $pk([
                    "La cotización va en el día {$edad}, a medio camino de tu tiempo normal de cierre — el siguiente toque busca compromiso del cliente, no plática.",
                    'A esta altura, el que no amarra fecha pierde la venta — pídele hoy al cliente una definición chica: visita, anticipo o fecha.',
                    "La cotización ya va en el día {$edad} de tu ciclo — el toque de hoy pide algo concreto al cliente, no otro sondeo.",
                ]),
            },
        };
    }

    // ── Capa 4: el arquetipo modula el CÓMO ──
    private static function modular(string $f, string $arq, array $slots, bool $viva, bool $dormida, callable $pk): string
    {
        if ($arq === '' || $arq === 'muestra_chica' || $arq === 'motor_completo') return $f;

        switch ($arq) {
            case 'regalador':
                if (!empty($slots['precio']) && !str_contains($f, 'descuento')) {
                    $f .= ' ' . $pk(['Cero descuento — cambia la forma de pago, no el número.',
                                     'Ni un peso de descuento: mueve plazos, no el precio.',
                                     'El precio no baja — ofrece forma de pago, no descuento.']);
                } elseif (!empty($slots['cierre'])) {
                    $f .= ' ' . $pk(['Cierra al precio cotizado — sin descuento de último minuto.',
                                     'El cierre va al precio de la cotización, sin rebajar.',
                                     'Nada de descuento para cerrar: el precio es el precio.']);
                }
                break;
            case 'cierre_falso':
            case 'engagement_flojo':
                if (!empty($slots['cierre']) || !empty($slots['decision'])) {
                    $f .= ' ' . $pk(['Y si el cliente dice sí, el anticipo se pide el mismo día.',
                                     'Cierre completo: fecha Y anticipo — un sí sin dinero no es venta.',
                                     'Con el sí del cliente, pide el anticipo de inmediato.']);
                }
                break;
            case 'rematador_ausente':
            case 'meseta':
                if (!empty($slots['decision']) || !empty($slots['cierre'])) {
                    $f .= ' ' . $pk(['No preguntes si quiere: da dos opciones donde ambas son sí.',
                                     'Remata con dos opciones: "¿esta semana o la próxima?".',
                                     'Ofrece dos caminos que cierran: el cliente solo escoge cuál.']);
                }
                break;
            case 'cultivador':
            case 'teatro':
                if (!empty($slots['confronta']) || !empty($slots['decision'])) {
                    $f .= ' ' . $pk(['Un "no" del cliente también te sirve — pídeselo de frente.',
                                     'Pregúntale directo al cliente: ¿sigue interesado o ya no?',
                                     'Pide una respuesta clara: ¿el cliente sigue o ya no?']);
                }
                break;
            case 'sin_ritmo':
            case 'desconectado':
            case 'presente_pasivo':
                $f = $pk(['Tu prioridad de hoy. ', 'Empieza el día aquí. ', 'Arranca el día por aquí. ']) . ucfirst($f);
                break;
            case 'sordo_a_senales':
                if ($viva || !empty($slots['senal_viva'])) {
                    $f .= ' ' . $pk(['La señal dura horas — atiéndela hoy antes de comer.',
                                     'Estas señales caducan el mismo día — atiéndela antes que nada.',
                                     'El interés del cliente se enfría en horas — contáctalo primero.']);
                }
                break;
            case 'una_pierna':
                if (!$viva && !$dormida) $f .= ' ' . $pk(['Agéndale seguimiento con fecha exacta — sin agenda se olvida.',
                                                          'Ponle fecha exacta al siguiente toque o se te va a olvidar.',
                                                          'Agenda el seguimiento hoy con día y hora; no lo dejes de memoria.']);
                break;
            case 'cerrador_desperdiciado':
            case 'pipeline_frio':
                if ($dormida || !empty($slots['dormida'])) {
                    $f .= ' ' . $pk(['Con pretexto nuevo — no con un "sigo pendiente".',
                                     'Que el mensaje traiga algo nuevo — el "¿cómo vamos?" ya no abre puertas.',
                                     'Llega con una razón nueva, no con el mismo recordatorio.']);
                }
                break;
            case 'sembrador':
                if ($viva) $f .= ' ' . $pk(['Cierra esta cotización antes de mandar una nueva.',
                                            'Primero remata esta cotización; luego cotizas la siguiente.',
                                            'No abras otra cotización hasta darle cierre a esta.']);
                break;
            case 'francotirador':
            case 'cerrador_solitario':
            case 'bajo_caudal':
                if ($viva) $f .= ' ' . $pk(['Esta cotización está viva — contáctalo hoy mismo.',
                                            'El cliente está activo: esta venta no se te puede ir — hoy.',
                                            'Cotización con señal viva: dale prioridad hoy mismo.']);
                break;
        }
        return $f;
    }

    private static function x(float $ratio): string
    {
        $r = round($ratio);
        return $r >= 3 ? "por {$r}" : ($r == 2 ? 'el doble' : 'más');
    }

    private static function bnom(?string $b): string
    {
        return match ($b) {
            'onfire' => 'On Fire',
            'inminente' => 'en cierre inminente',
            default => 'en probable cierre',
        };
    }
}
