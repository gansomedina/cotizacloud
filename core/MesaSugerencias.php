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
        // Evidencia de apertura para citar en la frase (siempre factual).
        // v24 es ventana RODANTE de 24h: una visita de anoche cae en v24 pero su
        // día calendario es AYER (dsv=1) — decir "hoy" ahí era mentira (bug real:
        // visita hace 21h → "abrió hoy"). El DÍA citado sale SIEMPRE de $dsv
        // (calendario); v24 solo aporta el conteo, citado como "últimas 24h".
        $ev_dia   = $dsv === 0 ? 'hoy' : ($dsv === 1 ? 'ayer' : ($dsv === 2 ? 'hace 2 días' : "hace {$dsv}d"));
        $ev_abrio = $leyendo
            ? ($v24 > 1 ? "{$v24} veces en las últimas 24h" : 'hace menos de 24h')
            : $ev_dia;

        // ¿El cliente reabrió DESPUÉS de la última declaración?
        $ult_decl = 0;
        foreach ([$con, $com, $pos] as $d) {
            if ($d && strtotime($d['at']) > $ult_decl) $ult_decl = strtotime($d['at']);
        }
        $uv      = !empty($c['ultima_vista_at']) ? strtotime($c['ultima_vista_at']) : 0;
        $reabrio = $ult_decl > 0 && $uv > $ult_decl;
        // "Ya vio la versión nueva" exige vista POSTERIOR al EDIT de la
        // cotización — una vista entre la postura y el edit era la versión vieja
        $apc_at    = !empty($c['accion_post_cambios_at']) ? strtotime($c['accion_post_cambios_at']) : 0;
        $vio_nueva = $apc_at > 0 && $uv > $apc_at;

        // Días de CALENDARIO desde una declaración (no horas/24): una captura de
        // ayer por la noche es "hace 1d", no "hoy/fresco" solo porque no han
        // pasado 24h. Consistente con la columna Actividad y con dias_sin_vista.
        $dias = fn(?array $d) => $d ? max(0, (int)round((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($d['at'])))) / 86400)) : 0;

        // ── Contexto de negocio ──
        $edad    = (int)($c['edad'] ?? 0);
        $p75     = max(1, (int)($c['p75'] ?? 30));
        $mediana = max(1, (int)($c['mediana'] ?? $p75));
        $fuera   = $edad > $p75;
        $intentos_nc = (int)($c['intentos_nc'] ?? 0);
        // Nota de la ESCALERA de intentos (suspender asistido): alinea los tips
        // de rendición con el contador "N de 4" del cajón. Closure porque se usa
        // en DOS lugares: la rama FANTASMA (que hace return temprano y se salta
        // las notas del final) y el bloque sistémico del cierre.
        // REGLA DEL CEO (23-jul): «no contestó» es un HECHO — la escalera cuenta
        // y el 4.º habilita suspender SIN importar manita/calor/categoría. El
        // asesor decide (asistido).
        // &$pk por referencia: $pk se define MÁS ABAJO (línea ~115) — capturarlo
        // por valor aquí lo congela en null y truena al llamar la nota
        $nota_escalera = function () use ($intentos_nc, &$pk) {
            if ($intentos_nc < 1) return '';
            return ' ' . ($intentos_nc >= 4
                ? $pk([
                    'Ya van 4 «no contestó» sin respuesta: puedes suspenderla desde aquí — o descártala con razón si tu lectura es que no era comprador.',
                    'Con 4 «no contestó» la escalera está completa — suspéndela desde aquí, o descártala con razón si ya lo juzgaste.',
                  ])
                : $pk([
                    "Si tampoco responde y no quieres descartarla aún: decláralo como «No contestó» — vas {$intentos_nc} de 4; al 4.º podrás suspenderla.",
                    "Y si no responde pero prefieres no descartarla todavía, decláralo como «No contestó»: vas {$intentos_nc} de 4 — al 4.º se habilita suspenderla.",
                  ]));
        };
        // Reloj de seguimiento (Fase A): las ramas de ESPERA solo aplican con
        // el reloj al corriente — si el límite es hoy o ya pasó, aconsejar
        // "dale espacio" contradice el chip 🔴 y el castigo que corre a diario.
        $seg_ok = (($c['seguimiento']['estado'] ?? 'ok') === 'ok');
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
                    "El cliente reabrió esta cotización tras tu descarte pero ya lleva {$dsv}d sin volver — un último mensaje directo cuanto antes, o descártala en firme.",
                    "La reabrió después del descarte y otra vez se calló ({$dsv}d sin abrir) — hoy decides: un empujón directo o el descarte final.",
                    "Reapareció tras el descarte y lleva {$dsv}d sin abrirla de nuevo — mándale sin tardar un mensaje directo; si no responde, descártala en firme.",
                ]);
            }
            return match ($c['razon_descarte'] ?? '') {
                'precio' => $pk([
                    'La descartaste por precio y el cliente la volvió a abrir — pregúntale de inmediato si algo cambió de su lado.',
                    'La descartaste por precio y el cliente regresó a la cotización — escríbele ya mismo y averigua si su situación cambió.',
                    'Descartaste esta cotización por precio y el cliente la reabrió — aprovecha la señal: mándale un mensaje cuanto antes.',
                ]),
                'no_responde' => $pk([
                    'La descartaste porque no respondía y el cliente volvió a la cotización — escríbele sin tardar; una segunda oportunidad así no se repite.',
                    'El cliente que no respondía reabrió la cotización — mándale un mensaje de inmediato antes de que se vuelva a enfriar.',
                    'La descartaste por silencio y el cliente regresó a verla — escríbele ya mismo; esta señal no va a repetirse.',
                ]),
                default => $pk([
                    'La descartaste y el cliente volvió a abrir la cotización esta semana — escríbele cuanto antes; una señal así no se repite.',
                    'Descartaste esta cotización y el cliente regresó a abrirla — mándale un mensaje directo sin tardar antes de que se enfríe otra vez.',
                    'El cliente reabrió una cotización que ya habías descartado — aprovecha de inmediato: mensaje directo preguntando si la retoman.',
                ]),
            };
        }
        if (!empty($c['milagro'])) {
            // "AHORA" solo si abrió HOY (v24>=1). Si el bucket se calentó hace
            // días pero el cliente no ha vuelto a abrir, NO está "viéndola ahora".
            if (!$leyendo) {
                $ev = $dsv <= 1 ? 'ayer' : "hace {$dsv}d";
                return $pk([
                    "Una cotización que ya dabas por vieja se volvió a calentar ({$ev} la última apertura) — fuera de tu ciclo pero con señal: contáctalo ya mismo antes de que se enfríe.",
                    "El cliente reactivó una cotización vieja (última apertura {$ev}) — está fuera de tu ciclo normal: mándale cuanto antes un mensaje directo citando la cotización.",
                    "Cotización vieja que revivió, abierta {$ev} — no la dejes pasar: contáctalo sin tardar y pregúntale si la retoman.",
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
        // FANTASMA: insiste sin respuesta y el cliente tampoco la abre.
        // Return temprano → la nota de la escalera se pega AQUÍ (el bloque
        // sistémico del final no la alcanza).
        if ($con_e === 'no_contesta' && $intentos_nc >= 2 && $visitas > 0 && $dsv >= 7 && !$viva) {
            return $pk([
                "Varios intentos sin respuesta y el cliente lleva {$dsv}d sin abrir la cotización — mándale un último mensaje amable y descártala con razón.",
                "El cliente no contesta y lleva {$dsv}d sin abrir la cotización — un último mensaje de inmediato y descártala con razón: no le dediques más tiempo.",
                "Ni respuesta ni aperturas en {$dsv}d — despídete con un último mensaje amable y descártala con razón; tu tiempo rinde más en otras cotizaciones.",
            ]) . $nota_escalera();
        }

        // ══ RAMA C1 — no_contesta vigente: SOLO acciones de canal ══
        if ($con_e === 'no_contesta') {
            // PLANTÓN: compromiso histórico roto
            if ($com_e === 'compromiso') {
                if ($leyendo || $reciente) {
                    $f = $pk([
                        'Quedaron en un acuerdo, el cliente dejó de contestar pero volvió a abrir la cotización — mándale un mensaje retomando exactamente lo acordado.',
                        'El cliente no contesta pero sigue abriendo la cotización — el acuerdo sigue vivo: escríbele ya mismo citando lo que quedaron.',
                        'El cliente dejó de contestar y aun así volvió a la cotización — el acuerdo le sigue interesando: mensaje cuanto antes recordando punto por punto lo acordado.',
                    ]);
                } else {
                    $f = $pk([
                        'Quedaron en un acuerdo y el cliente dejó de contestar — mándale sin tardar un mensaje directo: "¿sigue en pie lo que quedamos?", sin reclamar.',
                        'El cliente rompió el acuerdo con silencio — escríbele de inmediato sin reclamo: pregúntale si lo que quedaron sigue en pie.',
                        'Después del acuerdo el cliente ya no contestó — mensaje corto ya mismo: "¿seguimos con lo que quedamos?", sin presión ni reclamo.',
                    ]);
                }
                $slots['senal_viva'] = $leyendo;
            }
            // PLANTÓN DE CITA: fijaron cita y el cliente dejó de contestar
            elseif ($com_e === 'nos_citamos') {
                $reabrio_tras_cita = $uv > strtotime($com['at']);
                if ($leyendo || $reciente) {
                    $f = $pk([
                        "Tenían cita y el cliente dejó de contestar, pero abrió la cotización {$ev_abrio} — la cita le sigue interesando: mándale un mensaje reconfirmándola con día y hora.",
                        "El cliente no contesta pero abrió la cotización {$ev_abrio}" . ($reabrio_tras_cita ? " después de fijar la cita" : "") . " — reconfírmala cuanto antes por mensaje con día y hora.",
                        "Fijaron cita, el cliente calló, y aun así volvió a la cotización — sigue en juego: mensaje sin tardar reconfirmando la cita.",
                    ]);
                    $slots['senal_viva'] = true;
                } else {
                    $f = $pk([
                        'Tenían cita y el cliente dejó de contestar — mándale de inmediato un mensaje directo: "¿sigue en pie nuestra cita?", sin reclamar.',
                        'La cita quedó fijada y el cliente ya no respondió — confirma ya mismo por mensaje si la cita sigue en pie; sin reclamo.',
                        'Después de fijar la cita el cliente guardó silencio — mensaje corto cuanto antes: "¿confirmamos la cita?", y decide con su respuesta.',
                    ]);
                }
            }
            // RECHAZO + EVASIÓN
            elseif ($com_e === 'propuse_no_quiso') {
                $f = $viva
                    ? $pk([
                        "El cliente te dijo que no y no contesta, pero abrió la cotización {$ev_abrio} — algo lo detiene y no te lo ha dicho: pregúntaselo directo por mensaje.",
                        "El cliente te dijo que no y te evita, pero abrió la cotización {$ev_abrio} — hay una duda que no te contó: mándale un mensaje preguntando qué lo detiene.",
                        "El cliente dijo que no, dejó de contestar y aun así abrió la cotización {$ev_abrio} — el interés sigue: escríbele sin tardar preguntando qué le falta para decidirse.",
                    ])
                    : $pk([
                        "El cliente te dijo que no y lleva {$dsv}d sin abrir la cotización — mándale un último mensaje sin presión y, si no responde, descártala con razón.",
                        "El cliente te dijo que no, no contesta y lleva {$dsv}d sin abrir la cotización — un último mensaje amable de inmediato; si sigue el silencio, descártala con razón.",
                        "El cliente dijo que no y lleva {$dsv}d sin contestar ni abrir la cotización — mándale un último mensaje ya mismo y descártala con razón si no responde.",
                    ]);
            }
            // EVASIVA (sin_compromiso junto a no_contesta es redundante: se absorbe)
            else {
                if ($alto && $ips7 >= 2) {
                    $f = $pk([
                        'El cliente no te contesta pero la cotización vale ' . self::x($ratio) . ' de tu venta típica y más personas la están viendo — mensaje cuanto antes a tu contacto: ofrece resolver las dudas de todos.',
                        'Tu contacto no responde pero la cotización vale ' . self::x($ratio) . ' de tu venta típica y varias personas la revisan — escríbele sin tardar y ofrece una llamada para resolver dudas de todos.',
                        'Sin respuesta, pero la cotización vale ' . self::x($ratio) . ' de tu venta típica y más gente la está viendo — no lo satures de mensajes: ofrécele de inmediato resolver las dudas del grupo.',
                    ]);
                } elseif ($leyendo || $reciente) {
                    $vv = $ev_abrio;
                    $f = $pk([
                        "El cliente no te contesta pero abrió la cotización {$vv} — te lee aunque no responda: mándale una pregunta que se conteste con sí o no.",
                        "El cliente te evita pero volvió a abrir la cotización {$vv} — no pidas llamada: mándale UNA pregunta cerrada, de las que se contestan con sí o no.",
                        "El cliente no responde y aun así abrió la cotización {$vv} — sigue interesado: mensaje corto ya mismo con una sola pregunta de sí o no.",
                    ]);
                    $slots['senal_viva'] = true;
                } elseif ($reabrio) {
                    $f = $pk([
                        'El cliente no te responde pero reabrió la cotización después de tu último intento — mándale por escrito una pregunta que se conteste con sí o no.',
                        'El cliente no contesta, pero después de que lo buscaste volvió a abrir la cotización — escríbele una pregunta cerrada que responda con sí o no.',
                        'Tu mensaje no tuvo respuesta pero el cliente reabrió la cotización — sigue ahí: mándale cuanto antes una pregunta directa de sí o no.',
                    ]);
                } elseif ($visitas === 0 && $intentos_nc >= 2 && $edad >= 7) {
                    $f = $pk([
                        "El cliente nunca ha abierto la cotización y van {$intentos_nc} intentos sin respuesta — último intento sin tardar por otro canal y descártala con razón.",
                        "Ni una apertura de la cotización y ya son {$intentos_nc} intentos sin que conteste — un último mensaje de inmediato; si sigue el silencio, descártala con razón.",
                        "La cotización sigue sin abrirse y el cliente no responde tras {$intentos_nc} intentos — haz un último intento ya mismo y después descártala con razón.",
                    ]);
                } elseif ($intentos_nc >= 3) {
                    $f = $pk([
                        "Van {$intentos_nc} intentos sin respuesta del cliente — cambia de canal sin tardar: si le llamas, escríbele; si le escribes, llámale; o busca a otra persona del mismo cliente.",
                        "Ya son {$intentos_nc} intentos y el cliente no responde — cambia de canal: otro medio, otra hora, o busca a otra persona de su empresa.",
                        "El cliente lleva {$intentos_nc} intentos sin contestarte — no repitas el mismo canal: cámbialo cuanto antes o consigue el teléfono de otra persona del mismo cliente.",
                        "Van {$intentos_nc} intentos sin respuesta — mientras insistes por otro canal, márcala 📵 Sin comunicación con tu lectura en \"¿Cómo lo ves?\": tu trabajo cuenta aunque el cliente no conteste.",
                    ]);
                } elseif ($dormida) {
                    $f = $pk([
                        "El cliente no contesta y lleva {$dsv}d sin abrir la cotización — reinténtalo hoy o mañana a más tardar por OTRO canal; si tampoco responde, prepárate para descartarla.",
                        "El cliente ni contesta ni ha abierto la cotización en {$dsv}d — dale un toque hoy o mañana por un canal distinto; si sigue el silencio, va rumbo a descarte.",
                        "Sin respuesta y {$dsv}d sin una sola apertura de la cotización — cambia de canal de inmediato; si tampoco funciona, descártala con razón.",
                    ]);
                } else {
                    $f = $pk([
                        'El cliente no te contestó — cambia de canal: mándale un mensaje corto con una pregunta que se conteste con sí o no; si ya le escribiste, márcale.',
                        'Sin respuesta del cliente — no repitas el mismo canal a la misma hora: otro medio, otra hora, y una pregunta de sí o no.',
                        'El cliente no respondió a tu último toque — intenta ya mismo por otro canal con una sola pregunta que pueda contestar con sí o no.',
                        'Sin respuesta todavía — prueba cuanto antes a otra hora y por otro medio; una pregunta corta, fácil de contestar.',
                        'El cliente no ha contestado — el siguiente intento va por otro canal y con una pregunta que se responda en segundos.',
                        'El cliente no contesta — reintenta por otro canal y márcala 📵 Sin comunicación mientras: cuenta como evaluación; cuando logres contacto la cambias a 👍/👎.',
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
                    'Hay acuerdo pero tú mismo la ves en el aire — la prueba es la fecha: si el cliente acepta ponerla hoy, el acuerdo es real; si la esquiva, tu instinto tenía razón.',
                    'Marcaste acuerdo y también "en el aire" — decide cuál es verdad: si hay acuerdo, ponle fecha sin tardar; si no lo hay, corrige tu postura.',
                    'Tienes un acuerdo declarado y a la vez "en el aire" — aclara de inmediato: un acuerdo real lleva fecha; si no la tiene, corrige la postura.',
                ]);
            } elseif ($pos_vig && $pos_e === 'objecion_precio') {
                $f = $pk([
                    'El acuerdo se puede caer por el precio — antes de ver al cliente prepara 2 formas de pagar el mismo total (anticipo y resto, o parcialidades) y dáselas a escoger.',
                    'El precio amenaza el acuerdo — llega con 2 formas de pago del mismo total y deja que el cliente escoja; no muevas el número.',
                    'El cliente aceptó pero el precio le pesa — prepara ya mismo 2 esquemas de pago del mismo total y preséntaselos para que elija uno.',
                ]);
                $slots['precio'] = true;
            } elseif ($pos_vig && $pos_e === 'pidio_cambios') {
                $reabrio_pos = $vio_nueva; // vista posterior al EDIT, no a la postura
                if (empty($c['accion_post_cambios'])) {
                    $f = $pk([
                        'El cliente aceptó pero pidió cambios — la versión nueva de la cotización sale HOY: no llegues a la cita sin ella.',
                        'Quedaron en un acuerdo y el cliente pidió cambios — manda cuanto antes la cotización actualizada; no llegues al compromiso sin ella.',
                        'Hay acuerdo pero el cliente pidió ajustes — prepara y envía HOY la versión nueva de la cotización; el acuerdo se sostiene con ella.',
                    ]);
                } elseif (!$reabrio_pos) {
                    $f = $pk([
                        'Le mandaste la cotización actualizada y el cliente no la ha abierto — avísale por mensaje que ya está lista; no asumas que la vio.',
                        'El cliente no ha abierto la versión nueva de la cotización — mándale un mensaje sin tardar avisando que ya se la enviaste.',
                        'La cotización nueva sigue sin abrirse — escríbele al cliente: "ya te mandé la versión con tus cambios, échale un ojo".',
                    ]);
                } else { // reabrio_pos: SÍ abrió la versión nueva
                    $f = $pk([
                        'El cliente pidió cambios y ya abrió la cotización nueva — no esperes su opinión: márcale sin tardar y pregunta "¿así ya cerramos?".',
                        'El cliente ya vio la versión nueva de la cotización — adelántate: llámale de inmediato y pregúntale directo si con esos cambios ya cerramos.',
                        'El cliente ya revisó la cotización con sus cambios — márcale de inmediato: "¿quedó como querías? ¿cerramos?".',
                    ]);
                    $slots['cierre'] = true;
                }
            } elseif ($reabrio_com) {
                $f = $pk([
                    'Quedaron en un acuerdo y el cliente volvió a revisar la cotización — está cumpliendo su parte: confírmale el siguiente paso ya mismo.',
                    'Después del acuerdo el cliente reabrió la cotización — va en serio: mándale ya mismo el siguiente paso, no esperes a que te busque.',
                    'El cliente está revisando la cotización después del acuerdo — aprovecha: llámale cuanto antes y amarra la fecha del siguiente paso.',
                ]);
                $slots['cierre'] = true;
            } elseif ($leyendo) {
                $f = $pk([
                    "Hay acuerdo en curso y el cliente abrió la cotización {$ev_abrio} — confírmale el siguiente paso cuanto antes.",
                    "El cliente abrió la cotización {$ev_abrio} con el acuerdo fresco — mándale el siguiente paso, no esperes a que te busque.",
                    "Acuerdo en curso y el cliente abrió la cotización {$ev_abrio} — llámale y amarra la fecha del siguiente paso.",
                ]);
                $slots['cierre'] = true;
            } elseif ($dcom >= 2 && !$reciente) {
                $f = $pk([
                    "Quedaron en un acuerdo hace {$dcom}d y el cliente no ha vuelto a abrir la cotización — mándale un mensaje sin tardar para confirmar que sigue en pie.",
                    "El acuerdo ya tiene {$dcom}d y el cliente ni se ha asomado a la cotización — recuérdaselo de inmediato citando exactamente lo que quedaron.",
                    "Pasaron {$dcom}d del acuerdo sin que el cliente abra la cotización — escríbele ya mismo: confirma si lo acordado sigue en pie.",
                ]);
                $slots['confronta'] = true;
            } elseif ($fuera) {
                $f = $pk([
                    "El acuerdo va en serio pero la cotización ya pasó los {$p75} días en que normalmente cierras — en el siguiente contacto ponle fecha de decisión, no un \"¿cómo vamos?\".",
                    "Hay acuerdo pero la cotización ya pasó los {$p75} días en que sueles cerrar — el próximo toque es para poner fecha de cierre, no para saludar.",
                    "El cliente va en serio pero ya pasaron los {$p75} días en que sueles cerrar — llámale cuanto antes y amarra una fecha de decisión concreta.",
                ]);
                $slots['decision'] = true;
            } elseif ($dcom >= 1 && $seg_ok) {
                // Quedaron AYER (o antier) y el cliente no se ha movido: ya hiciste
                // tu parte. SOLO con el reloj al corriente — vencido, el consejo
                // es el toque, no el espacio.
                $slots['espera'] = true;
                $f = $pk([
                    'Quedaron hace ' . $dcom . 'd y el cliente no se ha movido — ya hiciste tu parte. Hoy dale espacio y observa si reabre o responde; lo único pendiente es que el acuerdo tenga fecha por escrito.',
                    'El acuerdo es de hace ' . $dcom . 'd — un par de días es normal. No lo satures: si ya quedó la fecha, espera su reacción; si no, mándale solo el resumen con la fecha y ahí lo dejas.',
                    'Acordaron hace ' . $dcom . 'd — no lo persigas todavía. Observa hoy si el cliente reabre la cotización; el único pendiente tuyo es dejar la fecha por escrito.',
                ]);
            } elseif ($dcom >= 1) {
                // Acuerdo con el reloj vencido/al límite: el espacio se acabó —
                // el toque declarado es lo que lo pone al corriente
                $f = $pk([
                    "El acuerdo tiene {$dcom}d y tu seguimiento ya llegó a su límite — un mensaje corto HOY (\u00bfsigue en pie lo que quedamos?) te pone al corriente.",
                    "Quedaron hace {$dcom}d y la cotización ya pide toque — mensaje breve cuanto antes confirmando lo acordado; con eso queda al corriente.",
                    "Pasaron {$dcom}d del acuerdo y el reloj de seguimiento venció — escríbele sin tardar citando lo que quedaron.",
                ]);
            } else {
                // dcom == 0: quedaron HOY — formalízalo mientras está fresco
                $f = $pk([
                    'Quedaron HOY — formalízalo mientras está fresco: mándale al cliente un resumen escrito de lo acordado y la fecha. Un acuerdo sin fecha se enfría.',
                    'Acuerdo de hoy — amárralo por escrito ahora: qué quedó y para cuándo. Lo que no queda por escrito se olvida en una semana.',
                    'El acuerdo está fresco (hoy) — mándale el resumen con la fecha y luego dale espacio para que responda; un acuerdo sin fecha se enfría.',
                ]);
                $slots['cierre'] = true;
            }
        }

        // ══ CITA — nos citamos vigente ══
        // Sin fecha capturada (la pill no la pide): los tiempos se miden desde
        // la DECLARACIÓN. Nunca afirmar que la cita ya ocurrió — no lo sabemos.
        elseif ($com_vig && $com_e === 'nos_citamos') {
            $dcita = $dias($com);
            $reabrio_cita = $uv > strtotime($com['at']);
            // El "registra el desenlace" sigue a la CADENCIA real de la cita
            // (ceil(mediana), topada a 5): con ciclo corto el chip se pone rojo
            // al dia 3 y el tip debe pedir el desenlace ahi, no al 5 fijo
            $lim_cita = max(2, min(5, (int)ceil((float)($c['mediana'] ?? 7))));
            if ($dcita >= $lim_cita) {
                $f = $pk([
                    "Declaraste la cita hace {$dcita}d — si ya se dieron, registra sin tardar el desenlace (¿quedaron en algo, no quiso, nada?); si sigue pendiente, reconfírmala por mensaje.",
                    "La cita se fijó hace {$dcita}d — actualízala: si ya ocurrió, captura el resultado; si el cliente la movió, decláralo de nuevo — una cita eterna no dice nada.",
                    "Han pasado {$dcita}d desde que fijaron la cita — o ya pasó (registra el desenlace de inmediato) o sigue en el aire (reconfírmala por mensaje).",
                ]);
            } elseif ($reabrio_cita || $leyendo) {
                $f = $pk([
                    'Tienen cita fijada y el cliente volvió a abrir la cotización — va en serio: confírmale la cita ya mismo y prepara la conversación sobre lo que él está revisando.',
                    ($reabrio_cita ? 'El cliente abrió la cotización después de fijar la cita' : 'El cliente está revisando la cotización con la cita fijada') . ' — llega preparado: confírmala cuanto antes y lleva respuestas para lo que estuvo mirando.',
                    'Cita en pie y el cliente revisando la cotización — confírmala sin tardar por mensaje; a la cita se llega con la cotización lista.',
                ]);
                $slots['cierre'] = true;
            } elseif ($dcita >= 1) {
                $f = $pk([
                    "Fijaron cita hace {$dcita}d — confírmala con un mensaje corto un día antes y llega con la cotización lista para cerrar ahí mismo.",
                    "La cita ya está fijada (hace {$dcita}d) — mándale una confirmación breve; a la cita se llega con la cotización abierta o impresa.",
                    "Cita agendada hace {$dcita}d — confírmasela por mensaje y prepara 2 formas de pago del mismo total por si la conversación llega al precio.",
                ]);
            } else {
                // dcita == 0: la fijaron HOY — amarrarla por escrito
                $f = $pk([
                    'Fijaron cita HOY — mándale la confirmación por escrito ahora (día, hora y lugar o medio): una cita sin confirmación por escrito se cae.',
                    'La cita quedó hoy — amárrala por escrito de inmediato: día, hora y dónde (o por dónde); lo confirmado por escrito se respeta más.',
                    'Cita fijada hoy — confírmala por mensaje ahora mismo con día y hora; llegado el día, llega con la cotización lista.',
                ]);
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
                        'El cliente no quiso comprometerse y está revisando el precio dentro de la cotización — la objeción es real: llégale de inmediato con 2 formas de pago.',
                        'El cliente rechazó el compromiso pero está comparando el precio en la cotización — le interesa: prepara opciones de pago y márcale ya mismo.',
                        'El cliente no quiso cerrar pero sigue metido en los precios de la cotización — el precio le importa: ofrécele de inmediato formas de pago, no bajes el número.',
                    ]);
                } elseif ($viva) {
                    $f = $pk([
                        'El cliente dijo que no por precio pero relee la cotización completa — no bajes el precio: reenvíasela cuanto antes resaltando todo lo que incluye por ese precio.',
                        'El cliente no quiso por precio y aun así sigue leyendo toda la cotización — no bajes el número: mejora cómo presentas el valor y reenvíasela sin tardar.',
                        'El cliente rechazó por precio pero estudia la cotización de arriba a abajo — le interesa: reenvíasela de inmediato resaltando lo que incluye, sin tocar el precio.',
                    ]);
                } elseif ($reabrio) {
                    // Volvió a abrirla DESPUÉS del no (aunque ya se enfrió) —
                    // "dejó de abrir" sería falso
                    $f = $pk([
                        "El cliente dijo que no por precio pero volvió a abrir la cotización después (última vez hace {$dsv}d) — el interés no murió: retómalo de inmediato con valor, sin rebaja.",
                        "Tras el no por precio el cliente reabrió la cotización, aunque lleva {$dsv}d callado — llámale ya mismo con 2 formas de pago del mismo total.",
                        "El cliente rechazó por precio y aun así volvió a la cotización hace {$dsv}d — no está cerrado: reenvíale cuanto antes la propuesta resaltando lo que incluye.",
                    ]);
                } else {
                    $f = $pk([
                        'El cliente dijo que no por precio y no ha vuelto a abrir la cotización — el problema no es el precio, es el interés: pregúntale qué le falta a la propuesta.',
                        'El cliente dijo caro y dejó de abrir la cotización — el precio es pretexto: llámale cuanto antes y pregunta qué necesitaría la propuesta para interesarle.',
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
                        'El cliente ya tiene la cotización actualizada después de rechazar la anterior — márcale ya mismo: "¿con estos cambios sí cerramos?".',
                        'La cotización nueva ya está en manos del cliente — no esperes: llámale sin tardar y pregunta si así ya se decide.',
                    ]);
            } elseif ($hot_fresco && in_array($bucket, ['onfire', 'inminente', 'probable_cierre'], true)) {
                $f = $pk([
                    'El cliente te dijo que no pero el Radar trae la cotización ' . self::bnom($bucket) . ' — interés sí hay: pregúntale de frente qué le falta para cerrar.',
                    'El cliente rechazó el compromiso y aun así el Radar marca la cotización ' . self::bnom($bucket) . ' — el freno no es interés: llámale de inmediato y pregunta directo qué lo detiene.',
                    'El cliente dijo que no pero el Radar tiene la cotización ' . self::bnom($bucket) . ' — algo no te ha contado: márcale cuanto antes y pregúntale qué tendría que cambiar.',
                ]);
                $slots['confronta'] = true;
            } elseif ($viva) {
                $f = $pk([
                    'Después de decirte que no, el cliente sigue metido en la cotización — algo lo detiene y no te lo dijo: pregúntale qué tendría que cambiar para que diga sí.',
                    'El cliente rechazó pero sigue leyendo la cotización — el interés está vivo: márcale sin tardar y pregúntale directo qué lo frena.',
                    'El cliente dijo que no y aun así vuelve a la cotización — hay una duda escondida: escríbele ya mismo preguntando qué le falta para animarse.',
                ]);
                $slots['confronta'] = true;
            } elseif ($dias($com) >= 1) {
                // Propusiste hace días, el cliente dijo que no y sigue callado: ya
                // preguntaste. No repitas "llámale cuanto antes" — dale aire; el cliente
                // pelotea. Solo si NUNCA le sacaste el motivo, un intento distinto.
                $dpnq = $dias($com);
                if (!$seg_ok) {
                    // Reloj vencido: el aire se acabó — un toque corto lo pone
                    // al corriente y de paso cierra el capítulo
                    $f = $pk([
                        "El no fue hace {$dpnq}d y tu seguimiento llegó a su límite — un último mensaje corto sin tardar; si calla, descártala con razón y tu 👎.",
                        "Propusiste hace {$dpnq}d, dijo que no y el reloj de seguimiento ya venció — toque breve sin tardar por otro ángulo; sin respuesta, es descarte con razón y 👎.",
                    ]);
                } else {
                $slots['espera'] = true;
                $f = $pk([
                    "El no fue hace {$dpnq}d y el cliente sigue sin moverse — ya jugaste tu carta. Dale aire hoy; si aún no sabes el motivo real, mándale UN mensaje distinto (no el mismo), y si calla, es un no.",
                    "Propusiste hace {$dpnq}d, dijo que no y no ha vuelto — el balón está de su lado. No lo persigas hoy: si nunca te dio la razón, un último intento por otro ángulo; si ya, déjalo enfriar y evalúa descartarla.",
                    "Hace {$dpnq}d que dijo que no y no se asoma — insistir hoy con lo mismo lo aleja. Si te falta entender qué lo frenó, pregúntalo distinto; si ya lo sabes y no cambió, esta va para descarte.",
                ]);
                }
            } else {
                // El no fue HOY — averigua el motivo mientras está fresco
                $f = $pk([
                    'El cliente no quiso y no sabes por qué — tu siguiente llamada es para averiguar la razón, no para intentar cerrar.',
                    'Propusiste hoy, el cliente dijo que no y no te dio el motivo — pregúntale de inmediato qué lo detuvo; el cierre viene después.',
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
                        'La plática no aterrizó y el cliente se está enfriando — mensaje directo ya mismo: "¿seguimos con esto o lo dejamos?"; cualquier respuesta te sirve.',
                        'No quedaron en nada y el interés del cliente va de bajada — pregúntale cuanto antes sin rodeos si sigue interesado; un no claro vale más que el silencio.',
                    ]);
                    $slots['confronta'] = true;
                } elseif (!$seg_ok) {
                    // Reloj vencido: la propuesta/fecha cerrada ES el toque de hoy
                    // (igual que la rama 'decidiendo'). Antes esta variante
                    // (en_el_aire sin pacto, no dormida) NO mencionaba el
                    // seguimiento vencido → el tip contradecía el 🔴 de la fila.
                    $f = $pk([
                        'Hablaron sin aterrizar y tu seguimiento ya venció — deja de sondear: la siguiente llamada lleva UNA propuesta cerrada o una fecha límite, y con eso quedas al corriente.',
                        'La plática quedó abierta y el reloj de seguimiento venció — no llames a "ver cómo va": hoy va una propuesta concreta o una fecha límite; con eso te pones al corriente.',
                    ]);
                    $slots['decision'] = true;
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
                        "Van {$dsv}d sin que el cliente abra la cotización desde que quedó en decidiendo — nadie decide sin releer: ponle fecha a la decisión esta semana.",
                        "Marcaste \"decidiendo\" pero el cliente tiene {$dsv}d sin ver la cotización — se está enfriando: llámale y ponle fecha a la decisión.",
                        "El cliente decide desde hace {$dsv}d sin abrir la cotización — nadie decide sin releer: contáctalo sin tardar y pide una respuesta con fecha.",
                    ]);
                    $slots['confronta'] = true;
                } elseif (($reabrio && $reciente) || $leyendo) {
                    // "relee/sigue abriendo" en PRESENTE exige lectura reciente
                    // (<=2d) — un reabrió de hace 10 días no es "está releyendo"
                    $f = $pk([
                        'Hablaron sin acuerdo pero el cliente está decidiendo y relee la cotización — agenda hoy la llamada de decisión, no esperes su veredicto.',
                        'El cliente lo está pensando y sigue abriendo la cotización — proponle hoy una llamada para decidir juntos; no esperes a que él te busque.',
                        'El cliente está decidiendo con la cotización en la mano — llámale de inmediato y ofrécele resolver la última duda; la decisión se cierra contigo enfrente.',
                    ]);
                    $slots['decision'] = true;
                } elseif ($dias($pos) >= 1 && !$seg_ok) {
                    // Reloj vencido: el espacio se acabó — el toque de hoy es
                    // por una FECHA de decisión (y te pone al corriente)
                    $dp = $dias($pos);
                    $f = $pk([
                        "Lleva {$dp}d decidiendo y tu seguimiento llegó a su límite — mensaje corto ya mismo pidiendo fecha de respuesta; con eso quedas al corriente.",
                        "El \"decidiendo\" ya tiene {$dp}d y el reloj de seguimiento venció — el toque de hoy no es más info: es pedirle fecha para su respuesta.",
                    ]);
                    $slots['decision'] = true;
                } elseif ($dias($pos) >= 1) {
                    // Ya marcaste "decidiendo" hace días y el cliente no se movió:
                    // no repitas "mándale algo nuevo" — a un decisor no lo mueve más
                    // info. Dale espacio; si se estanca, el toque es por una fecha.
                    $dp = $dias($pos);
                    $f = $pk([
                        "Marcaste \"decidiendo\" hace {$dp}d y el cliente no se ha movido — no le mandes más info, eso no mueve a un decisor. Dale espacio hoy; si sigue quieto, el siguiente toque es por una fecha, no por otro dato.",
                        "El cliente quedó en pensarlo hace {$dp}d — ya le diste con qué. Observa si reabre; no repitas el envío. Si se estanca, pídele una respuesta con fecha.",
                        "Van {$dp}d de \"lo está pensando\" sin señales — insistir con más material lo satura. Aire hoy; si no se mueve, una fecha límite y a lo que sigue.",
                    ]);
                    $slots['espera'] = true;
                } else {
                    $f = $pk([
                        'Hablaron sin acuerdo y el cliente "lo está pensando" — mándale cuanto antes algo nuevo que lo ayude a decidir: un caso, una foto, una fecha límite.',
                        'El cliente quedó en pensarlo y no hay más señales — no esperes otra semana: mándale sin tardar un dato nuevo o una fecha límite para decidir.',
                        'Un "lo estoy pensando" sin movimiento es un no lento — dale ya mismo al cliente una razón nueva para decidir: beneficio, plazo o fecha que expira.',
                    ]);
                }
            } elseif ($pos_vig && $pos_e === 'objecion_precio') {
                $f = ($bucket === 'validando_precio' && $viva)
                    ? $pk([
                        'Hablaron, el precio quedó como duda y el cliente lo está revisando en la cotización — la objeción es real: llégale con formas de pago.',
                        'El cliente quedó dudando del precio y ahora lo valida dentro de la cotización — prepárale hoy opciones de pago; no defiendas el número.',
                        'La duda de precio va en serio: el cliente está comparando los totales de la cotización — márcale de inmediato con esquemas de pago listos.',
                    ])
                    : $pk([
                        'Hablaron y el precio quedó de pretexto — no des un número nuevo por teléfono: mándale al cliente una propuesta cerrada por escrito.',
                        'El precio quedó como pretexto tras la plática — no negocies por teléfono: mándale de inmediato una propuesta cerrada por escrito.',
                        'Del precio solo hay quejas, no números — ponlo por escrito: mándale ya mismo al cliente una propuesta cerrada y después llámale por su respuesta.',
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
                        'La versión nueva de la cotización ya está enviada — mándale cuanto antes un mensaje al cliente: "¿ya la viste? ¿así ya cerramos?".',
                        'El cliente ya tiene la cotización con sus cambios — no esperes su opinión: márcale ya mismo y pregunta si con eso se decide.',
                    ]);
            } elseif ($bucket === 'multi_persona' && $ips7 >= 2) {
                $f = $pk([
                    "Hablaron sin quedar en nada y hay {$ips7} dispositivos viendo la cotización — tu contacto no decide solo: proponle una reunión con todos los que deciden.",
                    "La cotización se está viendo desde {$ips7} dispositivos y tu contacto no se comprometió — hay más gente decidiendo: pide de inmediato una reunión con todos.",
                    "Tu contacto no cerró y la cotización la revisan {$ips7} dispositivos — el que decide es otro: ofrece cuanto antes presentarla a todo el grupo, con garantía por escrito.",
                ]);
            } elseif ($viva) {
                $f = $pk([
                    'Después de la plática el cliente siguió leyendo la cotización — le interesa pero algo no te dijo: márcale y ofrécele dos fechas de arranque para que escoja.',
                    'La plática no aterrizó pero el cliente siguió estudiando la cotización — hay interés atorado: pregúntale directo qué lo detiene y dale dos opciones de arranque.',
                    'Hablaron sin acuerdo y aun así el cliente relee la cotización — no lo dejes enfriar: llámale sin tardar y proponle dos fechas para arrancar.',
                ]);
                $slots['decision'] = true;
            } elseif ($dias($com) >= 1) {
                // Hablaron hace días sin cerrar: el pendiente es UNA propuesta por
                // escrito, no otra plática. Si ya la mandaste, observa; si no, esa
                // es la única acción de hoy. No repitas "vuelve a proponer".
                $dp = $dias($com);
                if (!$seg_ok) {
                    // Reloj vencido: la propuesta por escrito ES el toque de hoy
                    $f = $pk([
                        "Hablaron hace {$dp}d sin aterrizar y tu seguimiento llegó a su límite — mensaje corto de inmediato con la propuesta cerrada (fecha y anticipo); con eso quedas al corriente.",
                        "Van {$dp}d sin nada concreto y el reloj de seguimiento venció — el toque de hoy es la propuesta por escrito que pida sí o no.",
                    ]);
                    $slots['decision'] = true;
                } else {
                $slots['espera'] = true;
                $f = $pk([
                    "Hablaron hace {$dp}d y no aterrizó — si ya le mandaste la propuesta por escrito (fecha y anticipo), dale espacio hoy: la siguiente llamada solo pide sí o no. Si no se la mandaste, esa es tu única acción de hoy.",
                    "La plática fue hace {$dp}d sin cerrar nada — no repitas la charla. ¿Ya está la propuesta por escrito? Si sí, espera su respuesta; si no, mándala hoy y ahí lo dejas.",
                    "Van {$dp}d desde que hablaron sin quedar en nada — el pendiente es UNA propuesta cerrada por escrito, no otra plática. Si ya la enviaste, hoy solo observas.",
                ]);
                }
            } else {
                $f = $pk([
                    'No repitas la plática: mándale al cliente una propuesta por escrito con fecha y anticipo definidos, y que tu siguiente llamada solo pida el sí o el no.',
                    'La siguiente llamada necesita algo nuevo — mándale antes al cliente una propuesta cerrada con fecha y anticipo, y llama solo a confirmarla.',
                    'Ya hablaron y no aterrizó nada — pon la propuesta por escrito hoy (fecha y anticipo) y que el cliente solo diga sí o no.',
                ]);
                $slots['decision'] = true;
            }
        }

        // ══ TOQUE INCOMPLETO — hablamos sin desenlace ══
        elseif ($con_e === 'hablamos' && !$com_vig && !$pos_vig) {
            $f = $viva
                ? $pk([
                    'Hablaste con el cliente, no registraste en qué quedaron, y él sigue entrando a la cotización — registra en qué quedaron y dale el siguiente toque hoy.',
                    'El cliente sigue revisando la cotización pero no declaraste cómo terminó la plática — registra en qué quedaron y contáctalo ya mismo.',
                    'El cliente sigue moviéndose en la cotización y tú no capturaste el resultado de la plática — decláralo ahora y dale el siguiente toque hoy mismo.',
                ])
                : $pk([
                    'Hablaron — ¿y quedaron en algo? Registra el desenlace; si no quedaron en nada, esa es tu siguiente llamada.',
                    'Hubo plática pero no declaraste el resultado — captúralo hoy; si no quedaron en nada, llama al cliente para aterrizar un acuerdo.',
                    'La plática quedó sin registro — declara en qué terminó; y si terminó en nada, tu siguiente llamada es para quedar en algo concreto.',
                    'Hablaste con el cliente pero no capturaste el resultado — regístralo ahora; sin eso la mesa no puede ayudarte a darle seguimiento.',
                    'Falta registrar en qué quedó tu plática — decláralo y, si no quedaron en nada, ese es justo tu pendiente de hoy.',
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

        // ══ RELOJ VENCIDO (🔴): reconocerlo si la rama no lo hizo ══
        // Escalera de intentos (suspender asistido, 22-jul): los tips de
        // rendición ("último mensaje y descártala") son ANTERIORES a la
        // escalera y contradecían al contador "N de 4" del cajón (reporte CEO:
        // el tip dice "último" y el contador "2 de 4"). Esta nota los alinea
        // presentando las DOS salidas como alternativa: descartar = tu juicio
        // (👎 con motivo); suspender = completar la escalera sin juzgar.
        // Gates: SOLO fila GHOST (manita 👍/👎 bloquea la suspensión — 📵 o
        // sin manita sí califica), con intentos declarados, NUNCA en tips de
        // espera ni revivida/milagro (señal viva: el cajón ahí dice "insiste")
        // ni descartada_hoy, y solo si el tip ES de rendición (regex).
        // Gates mínimos: nunca en tips de ESPERA (contradice "dale aire") ni en
        // descartada_hoy (fila ya resuelta), y solo si el tip ES de rendición.
        // (revivida/milagro hacen return temprano — jamás llegan aquí.)
        if (empty($slots['espera'])
            && ($c['cat'] ?? '') !== 'descartada_hoy'
            && preg_match('/descart|despídete|último (?:mensaje|intento)|última jugada|no le dediques|mensaje final|cierra el caso/iu', $f)) {
            $f .= $nota_escalera(); // '' sin intentos declarados
        }

        // La mayoría de las ~50 ramas de tips no revisan el reloj de seguimiento
        // y daban buen consejo pero sin reconocer el atraso — el asesor ve 🔴
        // "sin seguimiento" en la fila y el tip lo ignoraba. Si el consejo aún
        // no lo menciona, se ata a ponerse al corriente. SOLO estado 'vencida'
        // (no 'hoy', que aún no vence → afirmar "venció" sería falso) y NUNCA en
        // tips de ESPERA (contradice "dale espacio"). Las 6 ramas que ya lo dicen
        // traen "venció/al corriente/a su límite" en $f → el guard las salta.
        // Guard específico del RELOJ (antes /venci/ suelto matcheaba de más:
        // "conVENCIó", "cotización VENCIda contra tu historial" → suprimía la nota
        // falsamente). Ahora exige que "venció/límite" esté ligado a seguimiento/
        // reloj, o el cierre "al corriente" que solo usan las ramas del reloj.
        if (($c['seguimiento']['estado'] ?? '') === 'vencida' && empty($slots['espera'])
            && !preg_match('/al corriente|(seguimiento|reloj)[^.]{0,25}(venci|límite)/iu', $f)) {
            $f .= ' ' . $pk([
                'Y ojo: tu seguimiento ya venció — hazlo hoy y quedas al corriente.',
                'Además tu seguimiento está vencido — este toque de hoy te pone al corriente.',
            ]);
        }

        // ══ CAPA 4: modulación por arquetipo (CÓMO, nunca QUÉ) ══
        return self::modular($f, (string)($c['arquetipo'] ?? ''), $slots, $viva, $dormida, $pk);
    }

    // ── Postura declarada sin (o con) contacto — auditada contra el Radar ──
    private static function juicio_sin_toque(?string $pos_e, array $c, bool $viva, bool $dormida, bool $reabrio, int $dsv, ?string $bucket, array &$slots, callable $pk): string
    {
        // "Ya vio la versión nueva" exige vista POSTERIOR al EDIT — se recomputa
        // aquí desde $c (este método no recibe el $vio_nueva de sugerir()).
        $apc_at    = !empty($c['accion_post_cambios_at']) ? strtotime($c['accion_post_cambios_at']) : 0;
        $uv        = !empty($c['ultima_vista_at']) ? strtotime($c['ultima_vista_at']) : 0;
        $vio_nueva = $apc_at > 0 && $uv > $apc_at;
        // Días de calendario desde que marcaste esta postura (recencia de TU acción)
        $dpos = self::dcal(!empty($c['postura_decl']['at']) ? strtotime($c['postura_decl']['at']) : 0);
        switch ($pos_e) {
            case 'decidiendo':
                if ($dormida && !$reabrio) {
                    $slots['confronta'] = true;
                    return $pk([
                        "Van {$dsv}d sin que el cliente abra la cotización desde que quedó en decidiendo — nadie decide sin releer: ponle fecha a la decisión esta semana.",
                        "Marcaste \"decidiendo\" pero el cliente tiene {$dsv}d sin ver la cotización — se está enfriando: llámale y ponle fecha a la decisión.",
                        "El cliente decide desde hace {$dsv}d sin abrir la cotización — nadie decide sin releer: contáctalo cuanto antes y pide una respuesta con fecha.",
                    ]);
                }
                if ($bucket === 'validando_precio' && $viva) { $slots['precio'] = true; return $pk([
                    'El cliente está decidiendo y clavado en los precios de la cotización — te está comparando AHORA: mándale sin tardar tu razón #1 para elegirte.',
                    'El cliente está decidiendo y revisa una y otra vez los precios — te compara con otros: mándale de inmediato lo que solo tú le ofreces.',
                    'El cliente compara los precios de la cotización mientras decide — adelántate: mensaje ya mismo con tu mejor argumento para que te elija.',
                ]); }
                if ($bucket === 'multi_persona' && $viva)    { return $pk([
                    'El cliente está decidiendo y varias personas revisan la cotización — arma a tu contacto para defenderte por dentro: mándale garantía y proceso por escrito.',
                    'Varias personas están viendo la cotización mientras el cliente decide — tu contacto te defiende allá adentro: dale sin tardar garantía y pasos por escrito.',
                    'La decisión no es de uno solo: varias personas ven la cotización — mándale cuanto antes a tu contacto un resumen con garantía para que lo presente.',
                ]); }
                if ($viva) { $slots['decision'] = true; return $pk([
                    'El cliente está decidiendo y relee la cotización — que decida contigo enfrente: agenda hoy la llamada de decisión, no esperes el veredicto.',
                    'El cliente está decidiendo con la cotización abierta — llámale sin tardar y proponle resolver la última duda juntos; no esperes su respuesta a solas.',
                    'El cliente relee la cotización mientras decide — adelántate: agenda hoy una llamada corta para cerrar la decisión.',
                ]); }
                // Reabrió DESPUÉS de la marca pero ya se enfrió (dsv>=3 aquí,
                // $viva atrapa lo reciente) — no afirmar "no ha vuelto a abrir"
                if ($reabrio) { $slots['decision'] = true; return $pk([
                    "El cliente sí reabrió la cotización después de tu marca, pero lleva {$dsv}d sin regresar — la decisión se enfría: llámale de inmediato y ponle fecha.",
                    "Marcaste \"decidiendo\" y el cliente la reabrió, aunque van {$dsv}d desde esa lectura — retómalo ya mismo: pide una respuesta con fecha.",
                    "El cliente volvió a la cotización tras tu marca y luego se calló ({$dsv}d) — no lo dejes decidir solo: contáctalo ya mismo con fecha límite.",
                ]); }
                if ($dpos >= 1) { $slots['confronta'] = true; return $pk([
                    "Marcaste \"decidiendo\" hace {$dpos}d y el cliente no ha reabierto la cotización — un \"lo pienso\" que no se mueve es un no lento. Ya le diste con qué: hoy no más info, pídele una respuesta con fecha.",
                    "Van {$dpos}d de \"decidiendo\" sin que el cliente reabra — no repitas el envío, eso ya no lo mueve. El toque de hoy es por una definición con fecha, sí o no.",
                    "El cliente \"decide\" desde hace {$dpos}d sin abrir la cotización — deja de alimentar la espera: ponle fecha a la decisión o acéptalo como no.",
                ]); }
                return $pk([
                    'Marcaste "decidiendo" hoy pero el cliente no ha vuelto a abrir la cotización — nadie decide sin releer: mándale algo nuevo que lo haga decidir.',
                    'Dices que el cliente decide pero la cotización está fría — sin lecturas no hay decisión: dale de inmediato un motivo nuevo (dato, foto o fecha límite).',
                    'El cliente "está decidiendo" sin abrir la cotización — eso es un no lento: mándale cuanto antes una razón nueva para retomarla.',
                ]);
            case 'objecion_precio':
                if ((int)($c['visitas'] ?? 0) === 0) return $pk([
                    'El cliente te dijo caro sin haber abierto la cotización ni una vez — está regateando de oídas: primero logra que la abra, luego hablan de precio.',
                    'El cliente dijo que está cara y jamás ha abierto la cotización — no negocies a ciegas: mándale sin tardar un motivo para que la lea primero.',
                    'El cliente se quejó del precio sin ver la cotización — el reclamo es de oídas: pídele hoy que la revise antes de hablar de números.',
                ]);
                if ($bucket === 'validando_precio' && $viva) { $slots['precio'] = true; return $pk([
                    'El cliente te dijo caro y está validando el precio dentro de la cotización — la objeción es real: llégale con formas de pago, no defiendas el número.',
                    'El cliente dijo caro y se la pasa revisando los totales de la cotización — le interesa: prepara cuanto antes 2 esquemas de pago del mismo total.',
                    'La queja de precio va en serio: el cliente compara los números de la cotización — márcale sin tardar con opciones de pago listas.',
                ]); }
                if ($viva) { $slots['precio'] = true; return $pk([
                    'El cliente dijo caro pero relee la cotización completa — no bajes el precio: reenvíasela de inmediato resaltando todo lo que incluye por ese precio.',
                    'El cliente dijo que está cara y aun así estudia toda la cotización — no bajes el precio: mejora cómo presentas el valor y reenvíasela ya mismo.',
                    'El cliente se quejó del precio pero lee la cotización de punta a punta — el interés está: reenvíasela cuanto antes resaltando lo que incluye, sin tocar el precio.',
                ]); }
                // Reabrió después del "caro" pero ya se enfrió — el hecho es
                // "volvió y se calló", no "ni abre"
                if ($reabrio) { $slots['precio'] = true; return $pk([
                    "El cliente dijo caro y aun así volvió a abrir la cotización (última vez hace {$dsv}d) — el interés sigue: retómalo cuanto antes con valor, no con rebaja.",
                    "Tras el \"está caro\" el cliente reabrió la cotización, pero van {$dsv}d sin más lecturas — llámale de inmediato con formas de pago, sin bajar el número.",
                    "El cliente se quejó del precio pero volvió a ver la cotización hace {$dsv}d — el precio no lo espantó del todo: márcale sin tardar con opciones de pago.",
                ]); }
                return $pk([
                    'El cliente te dijo caro y no ha vuelto a abrir la cotización — el problema no es el precio, es el interés: pregúntale qué le falta a la propuesta.',
                    'El cliente dijo caro y dejó de ver la cotización — el precio es pretexto: llámale ya mismo y pregunta qué necesitaría para interesarle de verdad.',
                    'La queja fue el precio pero el cliente ni abre la cotización — no ofrezcas rebaja: pregúntale cuanto antes qué le falta a la propuesta para convencerlo.',
                ]);
            case 'pidio_cambios':
                if (empty($c['accion_post_cambios'])) return $pk([
                    'El cliente pidió cambios y la cotización sigue igual — la versión nueva sale HOY: una cotización que se mueve, cierra.',
                    'El cliente pidió ajustes y no has actualizado la cotización — hazlos hoy y mándasela; el cliente decide sobre la versión nueva.',
                    'Los cambios que pidió el cliente siguen pendientes — sácalos HOY; cada día sin versión nueva enfría la venta.',
                ]);
                if ($vio_nueva) { $slots['cierre'] = true; return $pk([
                    'El cliente ya abrió la cotización con sus cambios — no esperes su opinión: márcale de inmediato y pregunta "¿así ya cerramos?".',
                    'El cliente ya vio la versión nueva de la cotización — adelántate: llámale sin tardar y pregúntale si con esos cambios ya se decide.',
                    'El cliente ya revisó la cotización actualizada — mensaje o llamada hoy: "¿quedó como querías? ¿cerramos?".',
                ]); }
                return $pk([
                    'Le mandaste la cotización actualizada y el cliente no la ha abierto — avísale por mensaje que ya está lista; no asumas que la vio.',
                    'La versión nueva de la cotización sigue sin abrirse — mándale de inmediato un mensaje al cliente avisando que ya se la enviaste.',
                    'El cliente no ha visto la cotización con sus cambios — escríbele ya mismo: "ya te mandé la versión nueva, échale un ojo".',
                ]);
            case 'en_el_aire':
                if ($viva) { $slots['confronta'] = true; return $pk([
                    'Tú la ves dudosa pero el cliente la sigue releyendo — los datos están de tu lado: márcale ya mismo y sal de la duda.',
                    'Marcaste la venta como dudosa y el cliente sigue abriendo la cotización — el interés existe: llámale cuanto antes y pregúntale directo en qué va.',
                    'Tú la das por dudosa pero el cliente vuelve a la cotización — no adivines: contacta hoy al cliente y pregunta si siguen.',
                ]); }
                if ($dormida) return $pk([
                    "El cliente lleva {$dsv}d sin abrir la cotización y tú mismo dudas de la venta — ponle fecha límite hoy o descártala; otra semana igual no la revive.",
                    "Van {$dsv}d sin una sola apertura y tú ya la ves dudosa — mándale sin tardar un mensaje con fecha límite; si el cliente no responde, descártala con razón.",
                    "La cotización tiene {$dsv}d sin abrirse y tu propia postura es de duda — decide hoy: fecha límite al cliente o descarte con razón.",
                ]);
                if ($dpos >= 1) { $slots['confronta'] = true; return $pk([
                    "Llevas {$dpos}d con esta venta \"en el aire\" y el cliente no se mueve — sentarte en la duda no la resuelve: hoy UNA pregunta directa (qué falta para decidir) o ponle fecha límite.",
                    "Van {$dpos}d de duda sin definir — si ya le preguntaste y no contestó, no insistas igual: fecha límite y, si calla, a descarte. Si nunca preguntaste, esa es la de hoy.",
                    "La marcaste \"en el aire\" hace {$dpos}d — una duda que no se resuelve se pudre. Hoy sales de ella: pregunta directa o fecha límite, no otra semana de espera.",
                ]); }
                return $pk([
                    'Tienes la venta en duda — una pregunta directa al cliente hoy vale más que otra semana de espera: pregúntale qué falta para decidir.',
                    'La venta sigue sin rumbo — mándale de inmediato al cliente una pregunta concreta: qué necesita para tomar la decisión.',
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
                    "Marcaste 👍 pero el cliente lleva {$dsv}d sin abrir la cotización y ya salió de tu ventana — último intento ya mismo: motivo nuevo con fecha límite, o descártala.",
                    "Le pusiste 👍 pero van {$dsv}d sin aperturas y la cotización ya está fuera de tu ventana — mándale cuanto antes un motivo nuevo con fecha límite; si calla, descártala.",
                    "El 👍 ya no aguanta: {$dsv}d sin que el cliente abra la cotización y fuera de tu ventana — hoy un último mensaje con fecha límite o descarte con razón.",
                ]);
            }
            return $pk([
                'Marcaste 👍 pero el cliente se está apagando — mándale sin tardar un motivo concreto para retomar la cotización, o corrige tu marca.',
                'Tu 👍 ya no coincide con el cliente: cada vez abre menos la cotización — dale sin tardar una razón nueva para volver, o acepta que se enfrió y descártala; un 👍 viejo no la mantiene viva.',
                'El interés del cliente va de bajada aunque marcaste 👍 — escríbele de inmediato con algo nuevo (foto, ajuste, fecha) o corrige tu marca.',
            ]);
        }
        if ($cat === 'ultimo_tramo') {
            $slots['decision'] = true;
            return $pk([
                "Marcaste 👍 pero la cotización ya va en día {$edad} y pasó tu ventana de cierre — el siguiente toque pide definición al cliente, no otra plática.",
                "El 👍 sigue pero la cotización va en día {$edad}, ya fuera de tu ventana — llámale ya mismo al cliente y pide una definición concreta.",
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
                'onfire'               => ['leyó la cotización completa y volvió más de una vez', $pk(['no reexpliques nada: contacto de cierre HOY.', 'ya no vendas: pregúntale con cuál fecha arrancan.', 'deja la labor de venta: llámale cuanto antes a cerrar.']), false],
                'inminente'            => ['se fue, lo pensó y regresó a la cotización: está decidiendo YA', $pk(['pregúntale directo hoy qué le falta para arrancar.', 'llámale sin tardar: "¿qué falta para que arranquemos?".', 'contáctalo de inmediato con una sola pregunta: ¿arrancamos o no?']), false],
                'validando_precio'     => ['está clavado en los totales de la cotización', $pk(['llega con la estructura de pago lista, no defiendas el número.', 'arma dos formas de pagar el mismo total y dáselas a escoger.', 'ofrécele ya mismo esquemas de pago; el número no se toca.']), false],
                'decision_activa'      => ['va y viene con la cotización, la está consultando', $pk(['ponte disponible hoy y pregúntale si alguien más participa en la decisión.', 'mensaje cuanto antes: ofrécete a resolver dudas y pregunta quién más decide.', 'llámale sin tardar, queda a la mano y confirma si decide solo o con alguien.']), false],
                'prediccion_alta'      => ['su patrón de lectura se parece al de los que sí compran', $pk(['contacto suave hoy: confirma el interés, no lo asumas.', 'un toque ligero hoy — pregúntale qué le pareció, sin presionar.', 'mensaje corto de inmediato preguntando cómo ve la propuesta; sin presión.']), false],
                'lectura_comprometida' => ['leyó la cotización a fondo a la primera y se detuvo en el precio', $pk(['esta señal dura horas: contáctalo HOY.', 'no dejes pasar el día: llámale o escríbele HOY.', 'esa atención se enfría en horas — búscalo ya mismo.']), false],
                'multi_persona'        => [($ips7 >= 2 ? "hay {$ips7} personas viendo la cotización" : 'varias personas están viendo la cotización'), $pk(['tu contacto no decide solo: propón reunión con todos y garantía por escrito.', 'pide ya mismo una reunión con todos los que deciden y manda garantía por escrito.', 'ofrécete a presentarla a todo el grupo hoy, con garantía por escrito.']), true],
                'alto_importe'         => ['la cotización está arriba de lo que normalmente vendes', $pk(['venta grande: manda garantía por escrito y tiempos de entrega, sin presionar el cierre.', 'mándale sin tardar por escrito la garantía y los tiempos de entrega, sin presionar el cierre.', 'mándale de inmediato la garantía por escrito y explícale paso a paso cómo trabajas, sin prisas.']), false],
                're_enganche_caliente' => ['regresó directo a los precios tras días fuera', $pk(['está comparando opciones para decidir: contáctalo HOY con seguridad.', 'anda en la comparación final: márcale cuanto antes y dale certeza con garantía y tiempos.', 'está por decidir entre opciones: aparece hoy con tu mejor argumento.']), false],
                default                => ['el Radar trae la cotización caliente y no la has calificado', $pk(['dale el toque hoy y declara cómo lo ves.', 'contáctalo ya mismo y registra en qué quedó.', 'búscalo cuanto antes y captura el resultado del toque.']), false],
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
                'El cliente regresó a la cotización después del silencio — aprovecha sin tardar: pregúntale si la retoman, sin presionar.',
            ]),
            'revivio'            => $pk([
                'La cotización revivió: el cliente la volvió a abrir después de semanas — dale continuidad sin presión: pregúntale si el proyecto cambió.',
                'Tras semanas muerta, el cliente reabrió la cotización — retómala con calma: mensaje de inmediato preguntando cómo va su proyecto.',
                'El cliente resucitó la cotización después de semanas — no lo presiones: pregúntale ya mismo si el plan sigue o algo cambió.',
            ]),
            'vistas_multiples'   => $pk([
                'El cliente abre la cotización varias veces pero no la lee a fondo — mándale un mensaje muy corto que busque respuesta, no cierre.',
                'Puras aperturas rápidas de la cotización, sin lectura completa — mensaje breve sin tardar con una pregunta fácil de contestar.',
                'El cliente se asoma a la cotización pero no la estudia — escríbele algo corto hoy: una pregunta simple, sin intentar cerrar.',
            ]),
            'hesitacion'         => $pk([
                'El cliente duda pero no suelta la cotización — pregúntale cuanto antes qué lo detiene y quítale presión.',
                'Hay duda: el cliente sigue abriendo la cotización sin decidirse — mensaje sin tardar preguntando qué le preocupa, sin presionar.',
                'El cliente no se decide pero tampoco abandona la cotización — llámale de inmediato, pregunta qué lo frena y dale confianza.',
            ]),
            'sobre_analisis'     => $pk([
                'El cliente lleva días dándole vueltas a la cotización con toda la información en mano — pregúntale directo qué duda tiene; no le mandes más información.',
                'El cliente ya tiene todo y sigue analizando la cotización — no le mandes nada nuevo: pregúntale ya mismo cuál es la duda que lo detiene.',
                'Días de análisis sobre la misma cotización — más información no ayuda: llámale cuanto antes al cliente y pregúntale directo qué objeción trae.',
            ]),
            'comparando'         => $pk([
                'La cotización se está abriendo desde varios lugares: el cliente está comparando — ayúdale con garantía y tiempos claros, no bajando el precio.',
                'Hay señales de comparación: la cotización se ve desde distintos lados — dale de inmediato certeza al cliente (garantía, tiempos de entrega) en vez de tocar el precio.',
                'El cliente te está comparando con otros — mándale sin tardar garantía y tiempos por escrito; el precio no se mueve.',
            ]),
            'enfriandose'        => $pk([
                'El cliente se está enfriando: cada vez abre menos la cotización — empieza hoy una serie de 3 toques con motivo nuevo en cada uno.',
                'El interés del cliente va de bajada — plan de 3 toques desde hoy: cada mensaje con algo nuevo, no con "¿cómo vamos?".',
                'La cotización se apaga poco a poco — dale cuanto antes el primer toque de una serie de 3, cada uno con una razón nueva para contestar.',
            ]),
            default              => match (true) {
                $fuera && $dormida => $pk([
                    "El cliente lleva {$dsv}d sin abrir la cotización y ya salió de tu ventana — última jugada hoy: motivo nuevo con fecha límite; si no responde, descártala.",
                    "Van {$dsv}d sin aperturas y la cotización ya rebasó tu ventana — un último mensaje de inmediato con fecha límite; si sigue el silencio, descártala con razón.",
                    "El cliente tiene {$dsv}d sin abrir la cotización y el tiempo ya se pasó — hoy decides: mensaje final con fecha límite o descarte con razón.",
                    "Cliente callado {$dsv}d y cotización vencida contra tu historial — un último mensaje con fecha límite y a lo que sigue.",
                    "Ni aperturas en {$dsv}d ni tiempo a favor — cierra el caso hoy: última oferta con fecha o descarte con razón.",
                ]),
                $fuera             => $pk([
                    "La cotización ya pasó los {$p75} días en que normalmente cierras — el siguiente toque define: fecha de decisión o descarte, no otro \"¿cómo vamos?\".",
                    "Ya pasaron los {$p75} días en que sueles cerrar — llámale ya mismo al cliente para poner fecha de decisión; si no hay fecha, va para descarte.",
                    "Esta cotización ya tardó más de tus {$p75} días normales — el próximo contacto le pide al cliente una fecha, o se descarta.",
                    "El tiempo de esta cotización ya venció contra tu propio historial — hoy le pones fecha límite al cliente o la descartas con razón.",
                    "A esta edad tus cotizaciones ya no cierran solas — llámale cuanto antes: fecha de decisión o la das de baja.",
                ]),
                $momentum === 'down' && !$dormida => $pk([
                    'El cliente abre la cotización cada vez menos — recupéralo con algo nuevo (una foto, un ajuste, un beneficio), no con un "¿ya lo pensó?".',
                    'El interés del cliente baja lectura tras lectura — tu siguiente mensaje necesita una razón nueva para que reabra la cotización.',
                    'Cada apertura de la cotización es más corta y más espaciada — mándale sin tardar algo distinto: un cambio, una foto o una fecha que expira.',
                    'El cliente va soltando la cotización poco a poco — recupéralo hoy con una novedad real, no con un recordatorio.',
                    'Las lecturas del cliente van en picada — cambia el juego hoy: ajuste, beneficio nuevo o fecha límite.',
                ]),
                $dormida           => $pk([
                    "El cliente lleva {$dsv}d sin volver a abrir la cotización — dale sin tardar un motivo nuevo para reabrirla; no un \"¿ya lo viste?\".",
                    "El cliente lleva {$dsv}d sin asomarse a la cotización — mándale algo que lo obligue a reabrirla: un cambio, una foto, una fecha que expira.",
                    "Van {$dsv}d sin una sola apertura de la cotización — escríbele de inmediato con una razón nueva para verla; el recordatorio solo ya no funciona.",
                    "El cliente se durmió: {$dsv}d sin tocar la cotización — despiértalo con algo distinto: una foto, un ajuste o una fecha que vence.",
                    "Silencio de {$dsv}d en la cotización — tu mensaje de hoy necesita traer algo nuevo que el anterior no tenía.",
                ]),
                $fit >= 60         => $pk([
                    'El patrón de lectura del cliente se parece al de los que sí compran (FIT alto) — no dejes enfriar la cotización: toque suave hoy.',
                    'El FIT está alto: el cliente lee la cotización como leen los que compran — mándale ya mismo un mensaje ligero para mantenerla viva.',
                    'El cliente trae FIT alto: su forma de leer la cotización apunta a compra — dale un toque suave hoy, sin presión.',
                ]),
                $edad <= $mediana  => $pk([
                    'La cotización está en tu mejor ventana de cierre — dale ya mismo un toque que termine en algo concreto: fecha, visita o anticipo.',
                    "La mayoría de tus ventas se cierran antes del día {$mediana} — el toque de hoy busca un acuerdo con el cliente, no un \"ahí la lleva\".",
                    'Estos son los días donde tus ventas se cierran — contacta hoy al cliente y sal con un compromiso chico: fecha, visita o anticipo.',
                    'La cotización está fresca y es cuando más cierras — búscalo cuanto antes y amarra el siguiente paso con fecha.',
                    'Ahorita es cuando: tus cierres ocurren en estos primeros días — un toque hoy con propuesta de fecha o visita.',
                ]),
                default            => $pk([
                    "La cotización va en el día {$edad}, a medio camino de tu tiempo normal de cierre — el siguiente toque busca compromiso del cliente, no plática.",
                    'A esta altura, el que no amarra fecha pierde la venta — pídele hoy al cliente una definición chica: visita, anticipo o fecha.',
                    "La cotización ya va en el día {$edad} de tu ciclo — el toque de hoy pide algo concreto al cliente, no otro sondeo.",
                    "El reloj corre: día {$edad} del ciclo — hoy toca pedirle al cliente una definición chica, no saludar.",
                    'Va a media carrera — el siguiente contacto propone fecha o anticipo; un saludo suelto ya no suma.',
                ]),
            },
        };
    }

    // ── Capa 4: el arquetipo modula el CÓMO ──
    private static function modular(string $f, string $arq, array $slots, bool $viva, bool $dormida, callable $pk): string
    {
        if ($arq === '' || $arq === 'muestra_chica' || $arq === 'motor_completo') return $f;
        // Una frase de espera con remate de acción se contradice a sí misma
        if (!empty($slots['espera'])) return $f;
        // El remate del arquetipo en TODAS las filas se vuelve muletilla.
        // Aplicar solo en la mitad (determinístico por cotización+día).
        if (($pk([0, 1])) === 1) return $f;

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
                $f .= ' ' . $pk(['Hazlo en tu primera hora — lo que no se hace temprano, no se hace.',
                                 'Este toque va hoy antes de mediodía, no "al rato".',
                                 'Agéndalo ahora mismo con hora — lo que se queda para al rato es lo primero que se pierde.']);
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
                if ($viva) $f .= ' ' . $pk(['Esta cotización está viva — contáctalo sin tardar.',
                                            'El cliente está activo: esta venta no se te puede ir — hoy.',
                                            'Cotización con señal viva: dale prioridad hoy mismo.']);
                break;
        }
        return $f;
    }

    // Días de CALENDARIO desde un timestamp (0 = hoy/nunca). Igual criterio que
    // el cierre $dias de sugerir() y que la columna Actividad.
    private static function dcal(int $ts): int
    {
        return $ts > 0 ? max(0, (int)round((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', $ts))) / 86400)) : 0;
    }

    private static function x(float $ratio): string
    {
        // floor, no round: una cotización de 2.5× NO "vale por 3" — nunca
        // afirmar más de lo que el número sostiene
        $r = (int)floor($ratio);
        return $r >= 3 ? "por {$r}" : ($r === 2 ? 'el doble' : 'más');
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
