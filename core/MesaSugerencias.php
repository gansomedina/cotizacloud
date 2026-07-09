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
        $viva     = $leyendo || $hot;

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
            return "Descartada hoy{$rz} — mañana sale de la mesa. El Radar la sigue vigilando: si el cliente revive, vuelve sola con ⚡.";
        }
        if (!empty($c['revivida'])) {
            return match ($c['razon_descarte'] ?? '') {
                'precio' => 'La descartaste por precio y el cliente la reabrió solo — algo cambió de su lado: mensaje directo hoy.',
                'no_responde' => 'La descartaste porque no respondía y volvió solo — mensaje directo hoy; los milagros no se repiten.',
                default => 'La descartaste y el cliente volvió a calentarse esta semana — los milagros no se repiten: mensaje directo hoy.',
            };
        }
        if (!empty($c['milagro'])) {
            if ($con_e === 'no_contesta') {
                return 'La dabas por incontactable, está fuera de tu ciclo y la está viendo AHORA — mensaje en este instante citando la cotización.';
            }
            return 'Fuera de tu ciclo normal y la está viendo AHORA — es ahora o se va: mensaje en cuanto sueltes esta pantalla.';
        }
        // FANTASMA: insiste sin respuesta y el cliente tampoco la abre
        if ($con_e === 'no_contesta' && $intentos_nc >= 2 && $visitas > 0 && $dsv >= 7 && !$viva) {
            return "Varios intentos sin respuesta y ni una apertura en {$dsv}d — mándale el cierre honroso y descártala con razón: deja de cargarla.";
        }

        // ══ RAMA C1 — no_contesta vigente: SOLO acciones de canal ══
        if ($con_e === 'no_contesta') {
            // PLANTÓN: compromiso histórico roto
            if ($com_e === 'compromiso') {
                if ($bucket === 'validando_precio' || $leyendo) {
                    $f = 'Quedaron en algo, dejó de contestar y volvió a la cotización — el pacto sigue vivo en su cabeza: mensaje retomando exactamente lo acordado.';
                } else {
                    $f = 'Quedaron en algo y luego dejó de contestar — mensaje hoy: "¿sigue en pie lo que quedamos?", sin reproche.';
                }
                $slots['senal_viva'] = $leyendo;
            }
            // RECHAZO + EVASIÓN
            elseif ($com_e === 'propuse_no_quiso') {
                $f = $viva
                    ? 'Te dijo que no, te evita, pero sigue abriendo la cotización — algo le estorba que no te dijo: mensaje escrito preguntándolo directo.'
                    : 'Te dijo que no y lleva días sin contestar ni abrirla — última carta sin presión y si no responde, descártala con razón.';
            }
            // EVASIVA (sin_compromiso junto a no_contesta es redundante: se absorbe)
            else {
                if ($alto && $ips7 >= 2) {
                    $f = 'No te contesta pero vale ' . self::x($ratio) . ' de tu venta típica y más gente la está viendo — no la quemes a insistencia: mensaje a tu contacto ofreciendo resolver las dudas de todos.';
                } elseif ($leyendo) {
                    $f = 'No te contesta pero abrió la cotización ' . ($v24 > 1 ? "{$v24} veces hoy" : 'hoy') . ' — te lee aunque te evite: mensaje corto con una pregunta que se conteste con sí o no; si ya le escribiste, márcale.';
                } elseif ($reabrio) {
                    $f = 'No te responde pero reabrió la cotización después de tu intento — búscalo por escrito con una pregunta cerrada, que pueda contestar sin conversación.';
                } elseif ($intentos_nc >= 3) {
                    $f = 'Van ' . $intentos_nc . ' intentos sin respuesta — cambia de canal obligado: si llamas, escribe; si escribes, llama, o busca otro contacto de la cuenta.';
                } elseif ($dormida) {
                    $f = "No contesta y lleva {$dsv}d sin abrirla — reintento mañana por OTRO canal; si tampoco, va camino a fantasma.";
                } else {
                    $f = 'No te contestó — cambia de canal: mensaje corto con una pregunta que se conteste con sí o no; si ya le escribiste, márcale.';
                }
            }
        }

        // ══ PACTO — compromiso vigente ══
        elseif ($com_vig && $com_e === 'compromiso') {
            $dcom = $dias($com);
            if ($pos_vig && $pos_e === 'en_el_aire') {
                $f = 'Declaraste pacto y "en el aire" a la vez — no pueden ser las dos: si el pacto es real ponle fecha, si no, corrige la postura.';
            } elseif ($pos_vig && $pos_e === 'objecion_precio') {
                $f = 'El pacto se puede caer por el precio — antes de verlo arma 2 formas de pagar el mismo número (anticipo + resto, o parcialidades) y dáselas a escoger.';
                $slots['precio'] = true;
            } elseif ($pos_vig && $pos_e === 'pidio_cambios') {
                if (empty($c['accion_post_cambios'])) {
                    $f = 'Quedaron en algo y pidió cambios — la versión nueva sale HOY: llega al compromiso con ella en mano.';
                } elseif (!$reabrio && $dcom >= 2) {
                    $f = 'Le mandaste la versión nueva y no la ha abierto — avísale que la versión nueva ya está lista; no asumas que la vio.';
                } else {
                    $f = 'Pidió cambios, ya abrió tu versión nueva — no esperes su opinión: márcale y pregunta "¿así ya cerramos?".';
                    $slots['cierre'] = true;
                }
            } elseif ($reabrio || $leyendo) {
                $f = 'Quedaron en algo y volvió a revisarla — está cumpliendo su parte: confírmale antes de que se enfríe.';
                $slots['cierre'] = true;
            } elseif ($dcom >= 2) {
                $f = "Quedaron en algo hace {$dcom}d y no ha vuelto a abrirla ni una vez — confírmalo hoy con un mensaje o era humo.";
                $slots['confronta'] = true;
            } elseif ($fuera) {
                $f = "Va en serio pero ya se salió de tu ventana de ~{$p75}d — el siguiente toque pone fecha de decisión, no un \"cómo vamos\".";
                $slots['decision'] = true;
            } else {
                $f = 'Prepara hoy tu parte del acuerdo y ponle fecha — si en 2 días él no ha vuelto a abrir la cotización, confírmalo: un pacto sin movimiento es humo.';
                $slots['cierre'] = true;
            }
        }

        // ══ RECHAZO — propuso y no quiso ══
        elseif ($com_vig && $com_e === 'propuse_no_quiso') {
            if ($pos_vig && $pos_e === 'decidiendo') {
                $f = 'Propusiste, no quiso y dice que lo piensa — pregunta qué tendría que pasar para que sí, y ponle fecha a esa respuesta.';
                $slots['decision'] = true;
            } elseif ($pos_vig && $pos_e === 'objecion_precio') {
                if ($bucket === 'validando_precio') {
                    $f = 'No quiso comprometerse y está validando el número en la cotización — es real: llega con estructura de pago.';
                } elseif ($viva) {
                    $f = 'No quiso por precio pero la relee completa — no es el número, es el valor: reacomoda la propuesta.';
                } else {
                    $f = 'No quiso por precio y no la ha vuelto a abrir — el problema no es el precio, es el interés: pregunta qué falta, no cuánto sobra.';
                }
                $slots['precio'] = true;
            } elseif ($pos_vig && $pos_e === 'pidio_cambios') {
                $f = empty($c['accion_post_cambios'])
                    ? 'No quiso ASÍ y pidió cambios — la versión nueva es la jugada y sale HOY.'
                    : 'No quiso la versión anterior y ya le mandaste la nueva — pregúntale directo si con esto ya le entra.';
            } elseif ($hot && in_array($bucket, ['onfire', 'inminente', 'probable_cierre'], true)) {
                $f = 'Te dijo que no al compromiso pero el Radar la trae ' . self::bnom($bucket) . ' — el freno no es interés: pregúntale de frente qué le falta.';
                $slots['confronta'] = true;
            } elseif ($viva) {
                $f = 'Después de decirte que no, sigue metido en la cotización — hay un freno que no te dijo: pregúntale qué tendría que cambiar para que sí.';
                $slots['confronta'] = true;
            } else {
                $f = 'Propusiste y no quiso, y no diagnosticaste el freno — la siguiente llamada es para saber el porqué, no para cerrar.';
            }
        }

        // ══ NADA CONCRETO — hablaron sin pacto ══
        elseif ($com_vig && $com_e === 'sin_compromiso') {
            if ($pos_vig && $pos_e === 'en_el_aire') {
                if ($bucket === 'enfriandose' || $dormida) {
                    $f = 'Hablaron, nada concreto, y cada vez la abre menos — dile derecho: ¿le entramos o lo soltamos? Un no también te sirve.';
                    $slots['confronta'] = true;
                } else {
                    $f = 'Hablaron, nada concreto y tú mismo la ves en el aire — deja de sondear: siguiente llamada con UNA propuesta cerrada o fecha límite.';
                    $slots['decision'] = true;
                }
            } elseif ($pos_vig && $pos_e === 'decidiendo') {
                if ($dormida && !$reabrio) {
                    $f = "Dices que está decidiendo pero lleva {$dsv}d sin abrirla — eso no es decidir, es enfriarse: fuérzale una definición esta semana.";
                    $slots['confronta'] = true;
                } elseif ($viva) {
                    $f = 'Hablaron sin acuerdo pero está decidiendo y la relee — agenda la llamada de decisión, no esperes el veredicto.';
                    $slots['decision'] = true;
                } else {
                    $f = 'Hablaron sin acuerdo y "lo está pensando" — dale algo nuevo que decidir, no otra semana de silencio.';
                }
            } elseif ($pos_vig && $pos_e === 'objecion_precio') {
                $f = $bucket === 'validando_precio'
                    ? 'Hablaron, quedó el precio en el aire y lo está validando en la cotización — llega con estructura de pago.'
                    : 'Hablaron y quedó el precio de pretexto — no sueltes número nuevo por teléfono: propuesta cerrada por escrito.';
                $slots['precio'] = true;
            } elseif ($pos_vig && $pos_e === 'pidio_cambios') {
                $f = empty($c['accion_post_cambios'])
                    ? 'Hablaron y pidió cambios — la versión nueva sale HOY; el interés se muere esperándote.'
                    : 'Le mandaste la versión nueva tras la plática — confirma que la abrió y pregunta si así ya cerramos.';
            } elseif ($bucket === 'multi_persona' && $ips7 >= 2) {
                $f = "Hablaron sin quedar en nada y hay {$ips7} dispositivos viéndola — tu contacto no decide solo: proponle la reunión con todos y garantía por escrito.";
            } elseif ($viva) {
                $f = 'Después de la plática siguió leyendo la cotización — le interesa pero algo no te dijo: márcale y ofrécele dos fechas de arranque para que escoja una.';
                $slots['decision'] = true;
            } else {
                $f = 'No repitas la plática: mándale por escrito una propuesta con fecha y anticipo definidos, y que tu siguiente llamada solo pida el sí o el no.';
                $slots['decision'] = true;
            }
        }

        // ══ TOQUE INCOMPLETO — hablamos sin desenlace ══
        elseif ($con_e === 'hablamos' && !$com_vig && !$pos_vig) {
            $f = $viva
                ? 'Hablaste con él, no declaraste en qué quedaron, y el Radar la trae caliente — captura el desenlace y dale el siguiente toque hoy.'
                : 'Hablaron — ¿y quedaron en algo? Declara el desenlace; si no quedaron en nada, esa es tu siguiente llamada.';
        }

        // ══ JUICIO SIN TOQUE — postura sola ══
        elseif ($pos_vig && !$con) {
            $f = self::juicio_sin_toque($pos_e, $c, $viva, $dormida, $reabrio, $dsv, $bucket, $slots);
        }
        elseif ($pos_vig) {
            // hablamos + postura (sin compromiso declarado)
            $f = self::juicio_sin_toque($pos_e, $c, $viva, $dormida, $reabrio, $dsv, $bucket, $slots);
        }

        // ══ VIRGEN — nada declarado: fallback por Radar/categoría ══
        if ($f === null) {
            $f = self::virgen($c, $bucket, $hot, $viva, $dormida, $dsv, $edad, $p75, $mediana, $ips7, $slots);
        }

        // ══ CAPA 3 extra: alto importe — SOLO cuando nada está declarado
        // (las filas ya declaradas traen guía específica; no meter ruido)
        if ($alto && !$con && !$com && !$pos && !str_contains($f, 'venta típica')) {
            $f .= ' Vale ' . self::x($ratio) . ' de tu venta típica — trátala como prioridad.';
        }

        // ══ CAPA 4: modulación por arquetipo (CÓMO, nunca QUÉ) ══
        return self::modular($f, (string)($c['arquetipo'] ?? ''), $slots, $viva, $dormida);
    }

    // ── Postura declarada sin (o con) contacto — auditada contra el Radar ──
    private static function juicio_sin_toque(?string $pos_e, array $c, bool $viva, bool $dormida, bool $reabrio, int $dsv, ?string $bucket, array &$slots): string
    {
        switch ($pos_e) {
            case 'decidiendo':
                if ($dormida && !$reabrio) {
                    $slots['confronta'] = true;
                    return "Dices que está decidiendo pero lleva {$dsv}d sin abrirla — eso no es decidir, es enfriarse: fuérzale una definición esta semana.";
                }
                if ($bucket === 'validando_precio') { $slots['precio'] = true; return 'Está decidiendo y clavado en los precios — está comparando AHORA: mándale hoy tu razón #1 para elegirte.'; }
                if ($bucket === 'multi_persona')    { return 'Está decidiendo y varias personas la revisan — arma al que te defiende por dentro: garantía y proceso por escrito.'; }
                if ($viva) { $slots['decision'] = true; return 'Está decidiendo y la relee — que decida contigo enfrente: agenda la llamada de decisión, no esperes el veredicto.'; }
                return 'Dijiste "decidiendo" y está frío — decidiendo sin releerla no es decidir: dale algo nuevo que decidir.';
            case 'objecion_precio':
                $slots['precio'] = true;
                if ((int)($c['visitas'] ?? 0) === 0) return 'Te dijo caro sin haberla abierto ni una vez — está regateando de oídas: primero que la abra, luego hablan de precio.';
                if ($bucket === 'validando_precio') return 'Te dijo caro y está validando el precio en la cotización — es real: llega con estructura de pago, no defiendas el número.';
                if ($viva) return 'Dijo caro pero la relee completa — no es el número, es el valor: reacomoda la propuesta antes de mover el precio.';
                return 'Te dijo caro y no la ha vuelto a abrir — el problema no es el precio, es el interés: pregunta qué falta, no cuánto sobra.';
            case 'pidio_cambios':
                if (empty($c['accion_post_cambios'])) return 'Pidió cambios y la cotización sigue igual — la versión nueva sale HOY: cotización que se mueve, cierra.';
                if ($reabrio) { $slots['cierre'] = true; return 'Ya abrió tu versión nueva — no esperes su opinión: márcale y pregunta "¿así ya cerramos?".'; }
                return 'Le mandaste la versión nueva y no la ha abierto — avísale que la versión nueva ya está lista; no asumas que la vio.';
            case 'en_el_aire':
                if ($viva) { $slots['confronta'] = true; return 'Tú la ves en el aire y el cliente la está releyendo — el que está en el aire eres tú: márcale hoy y sal de la duda.'; }
                if ($dormida) return "Lleva {$dsv}d sin abrirla y tú mismo la ves en el aire — ponle fecha límite hoy o descártala — cargarla otra semana no la revive.";
                return 'La traes en el aire — una pregunta directa hoy vale más que otra semana de limbo: define qué falta para aterrizarla.';
        }
        return 'Captura el status: un tap hoy vale más que la memoria de la semana.';
    }

    // ── Sin declaración: la voz del Radar por bucket/categoría ──
    private static function virgen(array $c, ?string $bucket, bool $hot, bool $viva, bool $dormida, int $dsv, int $edad, int $p75, int $mediana, int $ips7, array &$slots): string
    {
        $cat = $c['cat'] ?? 'trabajo';
        $fuera = $edad > $p75;
        if ($cat === 'interes_muriendo') {
            $slots['dormida'] = true;
            // Fuera de ventana Y dormida: rescatarla ya no es el consejo honesto
            if ($fuera && $dormida) {
                return "Marcaste 👍 pero ya se salió de tu ventana y lleva {$dsv}d sin abrirla — última jugada: motivo nuevo hoy con fecha límite, y si no responde, descártala.";
            }
            return 'Marcaste 👍 y el cliente se está apagando — rescátala hoy con un motivo concreto, o corrige tu marca.';
        }
        if ($cat === 'ultimo_tramo') {
            $slots['decision'] = true;
            return "Marcaste 👍 pero ya está en día {$edad}, saliendo de tu ventana (~{$p75}d) — último tramo útil: el siguiente toque pide definición.";
        }
        // Evidencia propia de ESTA fila (cada cotización cita sus números)
        $v24 = (int)($c['vistas_24h'] ?? 0);
        $v7  = (int)($c['vistas_7d'] ?? 0);
        $ev  = null;
        if ($v24 >= 2)      $ev = "La abrió {$v24} veces hoy";
        elseif ($v24 === 1) $ev = 'La abrió hoy';
        elseif ($dsv === 1) $ev = 'La abrió ayer';
        elseif ($v7 >= 3)   $ev = "La abrió {$v7} veces esta semana";
        elseif ($dsv >= 2 && $dsv < 7 && (int)($c['visitas'] ?? 0) > 0) $ev = "Lleva {$dsv}d sin volver a verla";

        // Por bucket (matiz del playbook) — solo si el calor es real.
        // probable_cierre agrupa varios motivos: pc_source dice CUÁL fue.
        if ($hot) {
            $slots['senal_viva'] = true;
            $b = $bucket;
            if ($b === 'probable_cierre' && !empty($c['pc_source'])) $b = $c['pc_source'];
            [$intro, $accion, $con_num] = match ($b) {
                'onfire'               => ['la leyó completa y volvió más de una vez', 'no reexpliques nada: contacto de cierre HOY.', false],
                'inminente'            => ['se fue, lo pensó y regresó — está decidiendo YA', 'pregunta directa hoy: qué falta para arrancar.', false],
                'validando_precio'     => ['está clavado en los totales', 'llega con la estructura de pago, no defiendas el número.', false],
                'decision_activa'      => ['va y viene con ella, la está consultando', 'ponte disponible y pregunta si alguien más decide.', false],
                'prediccion_alta'      => ['su patrón se parece al de los que sí compran', 'contacto proactivo suave: confirma, no asumas.', false],
                'lectura_comprometida' => ['la leyó a fondo a la primera y tocó el precio', 'esta señal dura horas: contáctalo HOY.', false],
                'multi_persona'        => [($ips7 >= 2 ? "hay {$ips7} personas viéndola" : 'varias personas la están viendo'), 'tu contacto no decide solo: reunión con todos y garantía por escrito.', true],
                'alto_importe'         => ['está arriba de lo que normalmente vendes', 'paciencia y garantía, jamás cierre exprés.', false],
                're_enganche_caliente' => ['regresó directo a los precios tras días fuera', 'está comparando en la mesa final: aparece YA con seguridad.', false],
                default                => ['el Radar la trae caliente y no la has calificado', 'dale el toque hoy y declara cómo lo ves.', false],
            };
            if (in_array($b, ['validando_precio', 're_enganche_caliente'], true)) $slots['precio'] = true;
            // Componer: evidencia de la fila + motivo + acción (1 número máx)
            if ($ev && !$con_num) return $ev . ' y ' . $intro . ' — ' . $accion;
            return ucfirst($intro) . ' — ' . $accion;
        }
        return match ($bucket) {
            're_enganche', 'regreso' => 'Volvió a asomarse tras días de silencio — retómala natural: pregunta si algo cambió de su lado.',
            'revivio'            => 'Resucitó sola después de semanas — continuidad, no presión: pregunta si el proyecto cambió.',
            'vistas_multiples'   => 'Se asoma pero no entra — mensaje ultra corto que busque respuesta, no cierre.',
            'hesitacion'         => 'Duda pero no se va — diagnostica el freno y quítale presión.',
            'sobre_analisis'     => 'Lleva días dándole vueltas con toda la información — invita la objeción; jamás mandes más información.',
            'comparando'         => 'La están abriendo desde lados distintos — ayúdale a comparar con garantía y tiempos, no bajando precio.',
            'enfriandose'        => 'Se está apagando — cadencia de 3 toques con salida honrosa, empezando hoy.',
            default              => match (true) {
                $fuera && $dormida => "Lleva {$dsv}d sin abrirla y ya se salió de tu ventana — última jugada: motivo nuevo hoy con fecha límite, y si no responde, descártala.",
                $fuera             => "Ya pasó tu ventana de ~{$p75}d — el siguiente toque define: fecha de decisión o descarte, no otro \"¿cómo vamos?\".",
                $dormida           => "Lleva {$dsv}d sin volver a abrirla — dale un motivo nuevo para reabrirla hoy (algo nuevo, no un \"¿ya lo viste?\").",
                $edad <= $mediana  => 'Está en tu mejor ventana de cierre — un toque hoy que termine en algo concreto.',
                default            => "Va a media ventana (día {$edad} de ~{$p75}) — el siguiente toque busca compromiso, no plática.",
            },
        };
    }

    // ── Capa 4: el arquetipo modula el CÓMO ──
    private static function modular(string $f, string $arq, array $slots, bool $viva, bool $dormida): string
    {
        if ($arq === '' || $arq === 'muestra_chica' || $arq === 'motor_completo') return $f;

        switch ($arq) {
            case 'regalador':
                if (!empty($slots['precio']) && !str_contains($f, 'descuento')) $f .= ' Cero descuento — cambia la forma de pago, no el número.';
                break;
            case 'cierre_falso':
            case 'engagement_flojo':
                if (!empty($slots['cierre'])) $f .= ' Y si te dice sí, el anticipo se pide el mismo día.';
                break;
            case 'rematador_ausente':
            case 'meseta':
                if (!empty($slots['decision'])) $f .= ' No preguntes si quiere: dos opciones donde ambas son sí.';
                break;
            case 'cultivador':
            case 'teatro':
                if (!empty($slots['confronta'])) $f .= ' Un "no" también te sirve — pídelo de frente.';
                break;
            case 'sin_ritmo':
            case 'desconectado':
            case 'presente_pasivo':
                $f = 'Tu toque del día es este: ' . lcfirst($f);
                break;
            case 'sordo_a_senales':
                if ($viva || !empty($slots['senal_viva'])) $f .= ' La señal dura horas — hoy antes de comer, no cuando tengas hueco.';
                break;
            case 'una_pierna':
                if (!$viva && !$dormida) $f .= ' Agéndalo con fecha exacta — el lento se pierde por olvidado.';
                break;
            case 'cerrador_desperdiciado':
            case 'pipeline_frio':
                if ($dormida || !empty($slots['dormida'])) $f .= ' Con pretexto nuevo — no con "sigo pendiente".';
                break;
            case 'sembrador':
                if ($viva) $f .= ' Remata ESTA antes de mandar una cotización nueva.';
                break;
            case 'francotirador':
            case 'cerrador_solitario':
            case 'bajo_caudal':
                if ($viva) $f .= ' De estas no se te van — hoy mismo.';
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
            'onfire' => 'On Fire desde ayer',
            'inminente' => 'en cierre inminente',
            default => 'en probable cierre',
        };
    }
}
