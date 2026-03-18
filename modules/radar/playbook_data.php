<?php
// ============================================================
//  CotizaCloud — modules/radar/playbook_data.php
//  Playbook genérico de ventas para cada bucket del Radar v2.3
//  Contenido universal: aplica a cualquier negocio/industria.
//  Basado en psicología de compras, ventas consultivas de alto
//  valor, marketing estratégico y behavioral analytics.
// ============================================================

defined('COTIZAAPP') or die;

/**
 * Retorna el playbook completo: array de buckets + objeciones genéricas.
 */
function radar_playbook(): array
{
    $buckets = [

        // ─── ZONA DE CIERRE ───────────────────────────────────────

        [
            'bucket'   => 'onfire',
            'icon'     => '🔴',
            'priority' => 'critica',
            'type'     => 'cierre',
            'tone'     => 'urgente',
            'summary'  => 'Este prospecto está en su punto máximo de interés. Ha revisado la cotización intensamente, se enfocó en precio y ha vuelto varias veces. Si no actúas ahora, podrías perder la venta.',
            'psychology' => 'El comprador está en estado de "resolución inminente": ya evaluó opciones, validó internamente y ahora busca confirmación final. Cada minuto que pasa sin contacto, la ansiedad post-decisión crece y puede buscar alternativas. La urgencia es real: su cerebro ya dijo "sí" pero necesita el empujón social.',
            'meaning' => [
                'Múltiples sesiones intensas en las últimas horas',
                'Foco repetido en precios, totales y condiciones',
                'Scroll profundo: leyó todo el contenido a detalle',
                'Patrón de "última revisión antes de decidir"',
            ],
            'do' => [
                'Contacta en los próximos 5-15 minutos máximo',
                'Usa un tono cálido y directo: "Vi que revisaste todo, ¿te queda alguna duda?"',
                'Ofrece facilitar el siguiente paso (contrato, enlace de pago, agenda)',
                'Si no contesta, manda un mensaje breve y concreto por WhatsApp',
                'Ten lista una oferta de cierre: descuento por decisión hoy, bonus, garantía extra',
            ],
            'dont' => [
                'NO esperes "a que ellos llamen" — el momento es AHORA',
                'NO mandes un correo largo con más información — ya la tiene',
                'NO presiones con escasez falsa — detectará la manipulación',
                'NO hagas preguntas abiertas que reabran la negociación',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => '¡Hola [nombre]! Vi que estuviste revisando la cotización a detalle. ¿Hay algo que te gustaría ajustar o estamos listos para avanzar? Estoy disponible ahora mismo.',
                    'nota'  => 'Directo, cálido, sin presión. Demuestra que estás atento.',
                ],
                [
                    'canal' => '📞 Llamada',
                    'texto' => '"Hola [nombre], te llamo porque noté tu interés en la cotización y quería asegurarme de resolver cualquier duda personalmente. ¿Tienes un par de minutos?"',
                    'nota'  => 'Si no contesta WhatsApp, llama. La velocidad es tu ventaja.',
                ],
            ],
            'best_channel' => '📞 Llamada directa o 📱 WhatsApp — lo que permita contacto más rápido',
            'goal' => 'Cerrar la venta o agendar el paso final en esta misma interacción.',
        ],

        [
            'bucket'   => 'inminente',
            'icon'     => '🟠',
            'priority' => 'critica',
            'type'     => 'cierre',
            'tone'     => 'calido',
            'summary'  => 'Señales fuertes de compra: actividad reciente, revisión de precio, múltiples visitas. Este prospecto está a punto de decidir — a tu favor o en tu contra.',
            'psychology' => 'El comprador está en la fase de "evaluación final". Su cerebro ha reducido las opciones a 2-3 y busca la señal definitiva para inclinar la balanza. En este momento, la confianza personal en el vendedor pesa más que el precio. Quien responda más rápido y con más seguridad, gana.',
            'meaning' => [
                'Actividad confirmada en las últimas 24 horas',
                'Score de probabilidad alto (FIT elevado)',
                'Ha traído a otras personas a revisar (guests/visitors)',
                'Múltiples señales de compra activas simultáneamente',
            ],
            'do' => [
                'Contacta hoy mismo, idealmente en la próxima hora',
                'Llama primero, si no contesta, WhatsApp',
                'Sé consultor: "¿Qué necesitas para tomar la decisión?"',
                'Ofrece una reunión breve (15 min) para resolver dudas finales',
                'Prepara respuestas a objeciones comunes de tu industria',
            ],
            'dont' => [
                'NO dejes pasar más de 24 horas sin contacto',
                'NO envíes más información genérica — pregunta qué necesita',
                'NO negocies precio de entrada — primero entiende el bloqueo',
                'NO hables mal de la competencia si sabes que está comparando',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], quería darte seguimiento sobre la cotización. Noté que la has estado revisando y me gustaría resolver cualquier pregunta. ¿Te parece si agendamos una llamada rápida de 10 minutos?',
                    'nota'  => 'Proponer algo concreto (10 min) baja la barrera de entrada.',
                ],
                [
                    'canal' => '📧 Email',
                    'texto' => 'Hola [nombre],\n\nQuiero asegurarme de que la cotización cubra exactamente lo que necesitas. Si hay algo que ajustar o alguna duda, estoy a una llamada de distancia.\n\n¿Te funciona mañana a las [hora] para una llamada rápida?\n\nSaludos',
                    'nota'  => 'Email como respaldo si no contesta WhatsApp.',
                ],
            ],
            'best_channel' => '📞 Llamada > 📱 WhatsApp > 📧 Email',
            'goal' => 'Identificar y resolver la última objeción. Cerrar o agendar cierre.',
        ],

        [
            'bucket'   => 'probable_cierre',
            'icon'     => '🟡',
            'priority' => 'alta',
            'type'     => 'cierre',
            'tone'     => 'calido',
            'summary'  => 'Hay señales reales de intención de compra: interacción con precio, buen engagement y actividad reciente. No es un curioso — es un comprador evaluando.',
            'psychology' => 'Este comprador ha superado la fase de "solo estoy viendo". Su comportamiento indica comparación activa y validación de presupuesto. Está en el punto donde un buen seguimiento lo convierte en cliente, pero si lo ignoras, otro proveedor lo capturará.',
            'meaning' => [
                'Vistas recientes con señal de calidad (precio, scroll profundo, cupón)',
                'Score FIT por encima del umbral o múltiples sesiones',
                'No es solo curiosidad: hay acciones concretas de evaluación',
                'Ha pasado tiempo suficiente para que haya reflexionado',
            ],
            'do' => [
                'Haz seguimiento proactivo dentro de las próximas 24 horas',
                'Enfoca la conversación en valor, no en precio',
                'Comparte un caso de éxito o testimonio relevante',
                'Pregunta: "¿Ya tuviste oportunidad de revisarla con tu equipo?"',
                'Ofrece clarificar cualquier ítem de la cotización',
            ],
            'dont' => [
                'NO asumas que va a comprar solo — necesita seguimiento',
                'NO ofrezcas descuento sin que lo pida — devalúa tu propuesta',
                'NO seas agresivo con el cierre — aún está evaluando',
                'NO mandes la misma cotización de nuevo — aporta algo nuevo',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], ¿cómo vas con la cotización? Si tienes alguna pregunta sobre los detalles o quieres que ajustemos algo, con gusto te ayudo.',
                    'nota'  => 'Casual, servicial. No presión.',
                ],
                [
                    'canal' => '📧 Email',
                    'texto' => 'Hola [nombre],\n\nTe comparto un breve resumen de por qué nuestros clientes nos eligen:\n\n• [Beneficio 1 - ej: garantía, soporte, calidad]\n• [Beneficio 2]\n• [Beneficio 3]\n\n¿Te gustaría que revisemos la cotización juntos?\n\nSaludos',
                    'nota'  => 'Refuerza valor sin hablar de precio.',
                ],
            ],
            'best_channel' => '📱 WhatsApp para seguimiento inicial > 📞 Llamada si hay interés',
            'goal' => 'Mover al prospecto de "evaluando" a "listo para decidir".',
        ],

        [
            'bucket'   => 'validando_precio',
            'icon'     => '💸',
            'priority' => 'alta',
            'type'     => 'cierre',
            'tone'     => 'estrategico',
            'summary'  => 'Este prospecto está enfocado específicamente en el precio. Ha revisado totales, hecho zoom en costos o comparado con otra persona. Está decidiendo si el precio es justo.',
            'psychology' => 'La validación de precio es una señal POSITIVA: significa que ya le interesa tu producto/servicio y ahora está justificando la inversión. No es un "no", es un "convénceme de que vale lo que cuesta". Su cerebro busca razones lógicas para justificar una decisión que emocionalmente ya tomó.',
            'meaning' => [
                'Foco repetido en la sección de precios y totales',
                'Posible validación con otra persona (visitor/guest)',
                'Está comparando contra su presupuesto o contra competencia',
                'Busca confirmar que la relación costo-beneficio es favorable',
            ],
            'do' => [
                'Refuerza el VALOR antes de hablar de precio',
                'Presenta el costo como inversión, no como gasto',
                'Desglosa: "Esto incluye X, Y, Z que normalmente se cobran aparte"',
                'Ofrece opciones de pago o planes si aplica',
                'Usa la fórmula: "Si comparas con [alternativa], nuestro precio incluye..."',
            ],
            'dont' => [
                'NO bajes el precio de inmediato — devalúa todo',
                'NO ignores la señal de precio — sí les importa',
                'NO te pongas defensivo sobre los costos',
                'NO agregues más items — simplifica lo que ya tiene',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], vi que estuviste revisando los detalles de la cotización. Te comento que el precio incluye [valor diferencial]. Si quieres, puedo explicarte el desglose para que veas exactamente qué estás recibiendo.',
                    'nota'  => 'Justifica el precio sin que lo pida. Anticipate.',
                ],
                [
                    'canal' => '📞 Llamada',
                    'texto' => '"[nombre], quiero asegurarme de que entiendas todo lo que incluye la cotización. A veces los números no cuentan toda la historia. ¿Puedo explicarte los beneficios detallados?"',
                    'nota'  => 'Reencuadra de "precio" a "valor incluido".',
                ],
            ],
            'best_channel' => '📞 Llamada — el precio se defiende mejor en voz que por texto',
            'goal' => 'Justificar el precio con valor. Que el prospecto sienta que es una inversión inteligente.',
        ],

        [
            'bucket'   => 'prediccion_alta',
            'icon'     => '🔮',
            'priority' => 'alta',
            'type'     => 'nurture',
            'tone'     => 'consultivo',
            'summary'  => 'El modelo predictivo indica alta probabilidad de conversión basándose en el comportamiento histórico de tu empresa. Está dentro de tu ciclo de venta normal.',
            'psychology' => 'Este prospecto encaja en el patrón de los clientes que sí compran. Su perfil de comportamiento (FIT) es similar al de quienes cerraron. Es un "cliente ideal en formación" — solo necesita el proceso de nurturing correcto y el timing adecuado.',
            'meaning' => [
                'Score FIT alto: su comportamiento coincide con el de compradores reales',
                'Está dentro de la ventana temporal normal de tu ciclo de venta',
                'Tiene actividad reciente — no ha abandonado',
                'El modelo estadístico lo marca como alta probabilidad',
            ],
            'do' => [
                'Mantén contacto periódico sin ser invasivo',
                'Envía contenido de valor: casos de éxito, testimonios, artículos útiles',
                'Agenda una revisión de la cotización si no has hablado',
                'Pregunta por su timeline: "¿Para cuándo necesitas tener esto resuelto?"',
                'Posiciónate como asesor, no como vendedor',
            ],
            'dont' => [
                'NO lo presiones — su timing es natural para tu negocio',
                'NO asumas que porque el modelo dice "alta" ya es venta segura',
                'NO dejes de dar seguimiento solo porque "ya lo tiene caliente"',
                'NO cambies drásticamente la cotización sin razón',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], ¿cómo va tu evaluación? Si necesitas más información o quieres agendar una reunión para revisar detalles, estoy para ayudarte. Sin presión, a tu ritmo.',
                    'nota'  => 'Demuestra paciencia profesional. El "sin presión" genera confianza.',
                ],
                [
                    'canal' => '📧 Email',
                    'texto' => 'Hola [nombre],\n\nTe comparto un caso reciente de un cliente con necesidades similares a las tuyas: [breve descripción del resultado].\n\nSi te interesa platicar sobre cómo podríamos lograr algo similar para ti, quedo a tus órdenes.\n\nSaludos',
                    'nota'  => 'Proof social + relevancia. No habla de precio.',
                ],
            ],
            'best_channel' => '📱 WhatsApp para mantener cercanía > 📧 Email para contenido de valor',
            'goal' => 'Nutrir la relación y estar presente cuando tome la decisión.',
        ],

        // ─── ZONA DE OPORTUNIDAD ──────────────────────────────────

        [
            'bucket'   => 'alto_importe',
            'icon'     => '💰',
            'priority' => 'alta',
            'type'     => 'oportunidad',
            'tone'     => 'premium',
            'summary'  => 'Cotización con importe significativamente mayor al promedio. Estas ventas grandes requieren un proceso más cuidadoso y personalizado.',
            'psychology' => 'Las decisiones de alto valor activan el "sistema 2" del cerebro: análisis lento, racional, con múltiples evaluadores. El comprador necesita más justificación, más confianza y más tiempo. Pero la recompensa es proporcionalmente mayor. Trata esta cotización como una cuenta VIP.',
            'meaning' => [
                'Importe por encima del percentil 80 de tu empresa',
                'Probable proceso de decisión más largo y con más involucrados',
                'Mayor riesgo percibido por el comprador = más análisis',
                'Una venta de estas puede equivaler a varias ventas normales',
            ],
            'do' => [
                'Personaliza cada interacción — nada genérico',
                'Ofrece una presentación o reunión dedicada',
                'Anticipa preguntas sobre garantías, soporte post-venta, SLAs',
                'Involucra a tu equipo senior si es necesario',
                'Documenta todo: propuesta formal, timeline, condiciones claras',
            ],
            'dont' => [
                'NO trates como una venta más — merece atención especial',
                'NO negocies por WhatsApp — merece una reunión formal',
                'NO des descuentos sin justificación — devalúas tu servicio premium',
                'NO apresures — las ventas grandes tienen ciclo largo',
            ],
            'messages' => [
                [
                    'canal' => '📧 Email',
                    'texto' => 'Hola [nombre],\n\nGracias por tu interés en nuestro servicio. Dado el alcance de este proyecto, me gustaría agendar una reunión para revisar juntos cada detalle y asegurarnos de que la cotización refleje exactamente lo que necesitas.\n\n¿Te funciona [día] a las [hora]? Puede ser presencial o por videollamada.\n\nSaludos',
                    'nota'  => 'Profesional, personalizado. Reunión formal = seriedad.',
                ],
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], para tu proyecto me gustaría tomar unos minutos para revisar la cotización contigo y asegurarme de que todo esté perfecto. ¿Cuándo podríamos hablar?',
                    'nota'  => 'WhatsApp para agendar, no para cerrar.',
                ],
            ],
            'best_channel' => '📧 Email formal + 📞 Reunión presencial o videollamada',
            'goal' => 'Elevar el nivel de atención. Construir confianza para una decisión grande.',
        ],

        [
            'bucket'   => 'decision_activa',
            'icon'     => '🧠',
            'priority' => 'alta',
            'type'     => 'oportunidad',
            'tone'     => 'consultivo',
            'summary'  => 'Este prospecto está activamente tomando una decisión. Múltiples visitas en poco tiempo con regresos reales. Su mente está trabajando en esto.',
            'psychology' => 'El patrón de "regresos frecuentes" indica un bucle de decisión: el comprador revisa, se va, piensa, vuelve a revisar. Está comparando mentalmente contra alternativas o contra su presupuesto. Cada regreso es una señal de que NO ha descartado tu propuesta.',
            'meaning' => [
                'Múltiples vistas en las últimas 48 horas',
                'Regresos reales (no solo pestañas abiertas) con horas de diferencia',
                'El prospecto está deliberando activamente',
                'No ha descartado tu cotización — sigue en su "shortlist"',
            ],
            'do' => [
                'Ofrece claridad: "¿Hay algo específico que estés evaluando?"',
                'Comparte un comparativo de beneficios si tu oferta es competitiva',
                'Facilita la decisión: checklists, resúmenes, FAQs',
                'Demuestra disponibilidad: "Puedo responder cualquier duda ahora"',
            ],
            'dont' => [
                'NO interpretes los regresos como duda — es proceso normal',
                'NO mandes múltiples mensajes seguidos — uno bien pensado basta',
                'NO cambies la cotización sin que lo pida',
                'NO muestres ansiedad por cerrar',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], sé que estás evaluando opciones y quiero facilitar tu decisión. Si hay algún detalle que necesites aclarar o si quieres que revisemos algo juntos, aquí estoy.',
                    'nota'  => 'Reconoce el proceso sin presionar.',
                ],
            ],
            'best_channel' => '📱 WhatsApp — accesible pero no invasivo',
            'goal' => 'Facilitar la decisión. Eliminar dudas sin generar nuevas.',
        ],

        [
            'bucket'   => 'revivio',
            'icon'     => '💜',
            'priority' => 'alta',
            'type'     => 'oportunidad',
            'tone'     => 'calido',
            'summary'  => 'Un prospecto que no veía la cotización desde hace más de 30 días acaba de regresar. Algo cambió en su situación — puede ser presupuesto, urgencia o simplemente que descartó a tu competencia.',
            'psychology' => 'Cuando alguien regresa después de tanto tiempo, hay un "evento disparador" detrás: aprobaron presupuesto, el otro proveedor falló, cambió una necesidad, o simplemente ahora tiene la urgencia que antes no tenía. Este es un momento de altísimo valor porque el prospecto ELIGIÓ volver a ti.',
            'meaning' => [
                'Más de 30 días sin actividad y acaba de volver',
                'Hubo un cambio en su situación (presupuesto, urgencia, competencia)',
                'Regresó voluntariamente — señal muy positiva',
                'La cotización sigue en su mente después de mucho tiempo',
            ],
            'do' => [
                'Contacta HOY — este impulso es temporal',
                'Muestra genuino interés: "¡Qué gusto verte de nuevo!"',
                'Pregunta qué cambió: "¿Hay algo nuevo en tu proyecto?"',
                'Ofrece actualizar la cotización si los precios/condiciones cambiaron',
                'Sé flexible: la situación del cliente pudo cambiar',
            ],
            'dont' => [
                'NO preguntes "¿por qué desapareciste?" — genera culpa',
                'NO asumas que todo sigue igual — valida la situación',
                'NO envíes la misma cotización sin preguntar primero',
                'NO ignores esta señal — es de las más valiosas',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], ¡gusto en saludarte! Vi que retomaste el tema de la cotización. Me encantaría ponerme al día contigo. ¿Sigues interesado? Puedo actualizar la propuesta si algo cambió.',
                    'nota'  => 'Cálido, sin reproches. Oferta de actualizar = flexibilidad.',
                ],
            ],
            'best_channel' => '📱 WhatsApp directo — velocidad es clave',
            'goal' => 'Reactivar la relación y entender la nueva situación del prospecto.',
        ],

        [
            'bucket'   => 'no_abierta',
            'icon'     => '❌',
            'priority' => 'media',
            'type'     => 'rescate',
            'tone'     => 'cuidadoso',
            'summary'  => 'La cotización fue enviada pero el cliente nunca la abrió. No hay evidencia de lectura. Puede ser un problema técnico, falta de interés o simplemente no la vio.',
            'psychology' => 'Una cotización no abierta no es necesariamente un "no". La bandeja de entrada está saturada, los links se pierden, las prioridades cambian. El comprador puede estar esperando el momento adecuado o simplemente no vio tu mensaje entre 100 más.',
            'meaning' => [
                'Más de 24 horas sin ninguna apertura ni vista',
                'No hay eventos JS ni actividad alguna del cliente',
                'Cotización dentro de su período de vigencia',
                'Puede ser problema técnico o simple omisión',
            ],
            'do' => [
                'Reenvía con un asunto diferente y más atractivo',
                'Manda un WhatsApp: "¿Pudiste ver la cotización que te envié?"',
                'Verifica que el email/número sean correctos',
                'Ofrece enviarla por otro medio (WhatsApp, link directo)',
                'Si no responde en 48h más, llama directamente',
            ],
            'dont' => [
                'NO asumas que no le interesa — quizá no la recibió',
                'NO reenvíes el mismo email idéntico — cambia el enfoque',
                'NO esperes más de 3 días sin hacer algo al respecto',
                'NO te rindas con un solo intento de contacto',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], te envié una cotización hace unos días y quería confirmar que la hayas recibido correctamente. Si prefieres, te la puedo enviar por aquí directamente. 👇',
                    'nota'  => 'Asume un problema técnico, no desinterés. Ofrece alternativa.',
                ],
                [
                    'canal' => '📧 Email',
                    'texto' => 'Hola [nombre],\n\nTe reenvío la cotización que preparamos para ti. Aquí tienes el enlace directo: [link]\n\nSi tienes alguna duda, no dudes en contactarme.\n\nSaludos',
                    'nota'  => 'Reenvío con asunto nuevo: "Tu cotización — enlace directo".',
                ],
            ],
            'best_channel' => '📱 WhatsApp — más visible que email, más personal',
            'goal' => 'Lograr que el prospecto abra y revise la cotización.',
        ],

        // ─── ZONA DE RE-ENGANCHE ──────────────────────────────────

        [
            'bucket'   => 're_enganche_caliente',
            'icon'     => '🔥',
            'priority' => 'alta',
            'type'     => 'reactivacion',
            'tone'     => 'oportuno',
            'summary'  => 'Regresó después de un período de inactividad Y se enfocó en precio (revisó totales, hizo loop de precios, usó cupón). Esta es una señal de compra MUY fuerte.',
            'psychology' => 'Este patrón es oro: el prospecto se fue, reflexionó, y volvió directo a ver el precio. Esto significa que ya decidió emocionalmente y ahora está haciendo la validación final del costo. Su regreso al precio indica que tiene el presupuesto y está buscando la justificación lógica.',
            'meaning' => [
                'Regresó tras un gap de inactividad significativo',
                'Se enfocó directamente en precio/totales (señal fortísima)',
                'Hizo alguna de: revisión de totales, loop de precio, cupón, vista de precio',
                'Combinación de "volvió" + "revisó precio" = máxima intención',
            ],
            'do' => [
                'Contacta de INMEDIATO — está en modo compra activa',
                'Ofrece un incentivo de cierre: "Si confirmas hoy/esta semana..."',
                'Ten preparada una oferta especial de reactivación',
                'Facilita al máximo: link de pago, contrato listo, siguiente paso claro',
                'Si aplica, menciona que los precios podrían cambiar pronto',
            ],
            'dont' => [
                'NO dejes pasar más de unas horas sin contacto',
                'NO reabras toda la negociación — ve directo al cierre',
                'NO preguntes por qué se fue — enfócate en el ahora',
                'NO mandes más información — ya tiene todo lo que necesita',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], ¡qué gusto! Vi que retomaste la cotización. Quiero que sepas que mantenemos las condiciones originales y si decides esta semana, puedo [beneficio adicional]. ¿Platicamos?',
                    'nota'  => 'Urgencia suave + incentivo. No reproches por la pausa.',
                ],
                [
                    'canal' => '📞 Llamada',
                    'texto' => '"Hola [nombre], te llamo porque vi que estás retomando el proyecto y no quiero que se te pase la oportunidad. ¿Hay algo que necesites para avanzar?"',
                    'nota'  => 'Directo a la acción. Esta llamada puede cerrar la venta.',
                ],
            ],
            'best_channel' => '📞 Llamada directa — este prospecto está listo',
            'goal' => 'Cerrar la venta aprovechando el impulso de reactivación.',
        ],

        [
            'bucket'   => 're_enganche',
            'icon'     => '🟣',
            'priority' => 'media',
            'type'     => 'reactivacion',
            'tone'     => 'calido',
            'summary'  => 'Regresó tras un período de inactividad con señales de interés, pero sin foco directo en precio. Está retomando el tema — aún no está en modo compra.',
            'psychology' => 'El regreso sin foco en precio sugiere que el prospecto está "revaluando", no "comprando". Algo le recordó tu propuesta (un trigger externo, una necesidad nueva) pero aún no está en la fase final. Necesita nurturing, no presión de cierre.',
            'meaning' => [
                'Regresó tras un gap de inactividad',
                'Muestra interés general pero sin enfoque en precio',
                'Está en fase de re-evaluación, no de cierre',
                'Oportunidad de reconstruir la relación comercial',
            ],
            'do' => [
                'Contacta dentro de las próximas 24 horas',
                'Pregunta genuinamente cómo va su proyecto/necesidad',
                'Ofrece actualizar la cotización si algo cambió',
                'Comparte novedades: "Desde que platicamos, tenemos [nuevo beneficio]"',
                'Sé paciente — está calentando de nuevo',
            ],
            'dont' => [
                'NO presiones con cierre inmediato — aún no está ahí',
                'NO asumas que todo sigue igual que cuando se fue',
                'NO te muestres desesperado por la venta',
                'NO ignores este regreso — es una segunda oportunidad',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], ¿cómo estás? Vi que retomaste la cotización y me alegra. ¿Hay algo nuevo en tu proyecto? Si necesitas que ajustemos algo, estoy para ayudarte.',
                    'nota'  => 'Cálido, sin presión. Oferta de ajustar = flexibilidad.',
                ],
            ],
            'best_channel' => '📱 WhatsApp — cercano pero sin presión',
            'goal' => 'Reactivar el diálogo y entender la nueva situación.',
        ],

        // ─── ZONA DE ANÁLISIS ─────────────────────────────────────

        [
            'bucket'   => 'multi_persona',
            'icon'     => '👥',
            'priority' => 'media',
            'type'     => 'oportunidad',
            'tone'     => 'estrategico',
            'summary'  => 'La cotización está siendo vista por múltiples personas o desde múltiples dispositivos. Hay un "comité de compra" evaluando.',
            'psychology' => 'Cuando un prospecto comparte la cotización, es BUENA señal: significa que la propuesta pasó su filtro personal y ahora necesita validación de otros. En ventas consultivas, el "comité de compra" (jefe, socio, pareja, equipo) tiene voz. Tu trabajo es equipar a tu "campeón interno" con argumentos para defender tu propuesta.',
            'meaning' => [
                'Múltiples visitantes o IPs distintas en la cotización',
                'El prospecto compartió el enlace con otras personas',
                'Hay un proceso de decisión grupal en marcha',
                'Tu "campeón" necesita herramientas para convencer a otros',
            ],
            'do' => [
                'Pregunta: "¿Alguien más necesita ver la cotización?"',
                'Ofrece crear versiones personalizadas para distintos evaluadores',
                'Prepara un resumen ejecutivo para quien toma la decisión final',
                'Identifica al "tomador de decisión" y al "influenciador"',
                'Ofrece una presentación grupal si hay varios involucrados',
            ],
            'dont' => [
                'NO te comuniques solo con un contacto si hay comité',
                'NO asumas que tu contacto tiene poder de decisión solo',
                'NO te frustres si el proceso es más largo — es normal en grupo',
                'NO envíes información que solo entienda un técnico si hay no-técnicos evaluando',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], noté que otras personas también están revisando la cotización. ¿Necesitas que prepare algún material adicional o quieres que agendemos una reunión con tu equipo?',
                    'nota'  => 'Demuestra profesionalismo. Ofrece ayudar con el comité.',
                ],
            ],
            'best_channel' => '📱 WhatsApp + 📧 Email con material para compartir',
            'goal' => 'Equipar al prospecto para que "venda" tu propuesta internamente.',
        ],

        [
            'bucket'   => 'revision_profunda',
            'icon'     => '🧾',
            'priority' => 'media',
            'type'     => 'oportunidad',
            'tone'     => 'tecnico',
            'summary'  => 'El prospecto está haciendo un análisis detallado y serio de la cotización. Lectura real, foco en totales y precios. No es scroll rápido — es estudio.',
            'psychology' => 'Un comprador que lee a profundidad es un comprador serio. Este patrón indica que está construyendo su caso interno (para sí mismo o para su jefe). Necesita que la cotización "hable por sí sola" porque probablemente la está usando como documento de referencia.',
            'meaning' => [
                'Tiempo de lectura real (no solo scroll)',
                'Foco específico en secciones de precio y totales',
                'Análisis serio y metódico del contenido',
                'Probablemente está construyendo un caso de compra',
            ],
            'do' => [
                'Ofrece documentación adicional si la necesita',
                'Pregunta si necesita una versión con más detalle técnico',
                'Envía un resumen de beneficios clave por separado',
                'Muestra disposición: "Si necesitas datos adicionales para tu evaluación..."',
            ],
            'dont' => [
                'NO interrumpas con llamadas innecesarias — está analizando',
                'NO simplifiques demasiado — este comprador quiere detalle',
                'NO cambies la cotización mientras la está estudiando',
                'NO asumas que más detalle = más dudas. Es proceso normal.',
            ],
            'messages' => [
                [
                    'canal' => '📧 Email',
                    'texto' => 'Hola [nombre],\n\nNoté que estás revisando la cotización a detalle (¡excelente!). Si necesitas información adicional como fichas técnicas, referencias de otros proyectos, o un desglose más detallado, no dudes en pedirlo.\n\nEstoy para apoyarte en tu evaluación.\n\nSaludos',
                    'nota'  => 'Valida su proceso analítico. Ofrece más datos, no presión.',
                ],
            ],
            'best_channel' => '📧 Email — respeta su proceso analítico',
            'goal' => 'Proveer toda la información necesaria para que complete su análisis favorablemente.',
        ],

        [
            'bucket'   => 'vistas_multiples',
            'icon'     => '🟩',
            'priority' => 'media',
            'type'     => 'oportunidad',
            'tone'     => 'amigable',
            'summary'  => 'Varias vistas o IPs en las últimas 24 horas. Hay actividad real y reciente. El prospecto tiene tu cotización "en la mesa".',
            'psychology' => 'Múltiples vistas en poco tiempo indican que la cotización está en la "memoria de trabajo" del comprador. No la archivó ni la olvidó — está activamente pensando en ella. Es el momento perfecto para estar disponible sin ser invasivo.',
            'meaning' => [
                'Múltiples vistas o IPs distintas en las últimas 24 horas',
                'La cotización está activamente en evaluación',
                'El prospecto no ha descartado tu propuesta',
                'Hay momentum — aprovéchalo',
            ],
            'do' => [
                'Mantente disponible y responde rápido cualquier mensaje',
                'Un seguimiento ligero: "¿Todo bien con la cotización?"',
                'Ofrece una llamada rápida si tiene dudas',
                'Comparte un dato de valor: testimonio, caso similar',
            ],
            'dont' => [
                'NO bombardees con mensajes — uno es suficiente',
                'NO interpretes cada vista como "está listo para comprar"',
                'NO seas invisible — el silencio completo también es malo',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], espero que estés bien. Solo quería confirmar si tienes alguna pregunta sobre la cotización. Estoy disponible si necesitas algo.',
                    'nota'  => 'Ligero, servicial. No invasivo.',
                ],
            ],
            'best_channel' => '📱 WhatsApp — breve y accesible',
            'goal' => 'Estar presente y disponible mientras evalúa.',
        ],

        [
            'bucket'   => 'hesitacion',
            'icon'     => '🟠',
            'priority' => 'media',
            'type'     => 'friccion',
            'tone'     => 'empatico',
            'summary'  => 'El prospecto pausó entre 24 horas y 7 días con señales de fricción en precio. Algo lo detuvo — precio, timing, o una duda no resuelta.',
            'psychology' => 'La hesitación es la manifestación de un conflicto interno: "Lo quiero pero algo me detiene". Los bloqueos más comunes son: precio vs. presupuesto, timing (no es el momento), miedo a equivocarse, o falta de aprobación de un tercero. Tu trabajo es identificar cuál es el bloqueo REAL y resolverlo.',
            'meaning' => [
                'Pausa de 1-7 días después de mostrar interés',
                'Señales de fricción en precios o totales',
                'El prospecto no ha descartado — pero tampoco avanza',
                'Hay un bloqueo específico que necesita resolverse',
            ],
            'do' => [
                'Pregunta directamente: "¿Hay algo que te genera duda?"',
                'Escucha activamente — el bloqueo puede no ser el precio',
                'Ofrece alternativas: "Podemos ajustar el alcance para tu presupuesto"',
                'Valida su hesitación: "Es normal tomarse tiempo en una decisión así"',
                'Si el bloqueo es precio, ofrece opciones de pago o versiones reducidas',
            ],
            'dont' => [
                'NO presiones — empeorarás la hesitación',
                'NO asumas que el problema es el precio (a veces es timing)',
                'NO ofrezcas descuento de primera — primero entiende el bloqueo',
                'NO desaparezcas — tu silencio confirma sus dudas',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], entiendo que una decisión así necesita su tiempo. ¿Hay algo específico que te genera duda? Me encantaría ayudarte a resolverlo.',
                    'nota'  => 'Empático, no presión. Abre la puerta al diálogo.',
                ],
            ],
            'best_channel' => '📱 WhatsApp — personal y empático',
            'goal' => 'Identificar y resolver el bloqueo específico.',
        ],

        [
            'bucket'   => 'sobre_analisis',
            'icon'     => '🟤',
            'priority' => 'media',
            'type'     => 'friccion',
            'tone'     => 'directo',
            'summary'  => 'Muchas sesiones, muchos guests, edad alta y FIT bajo. El prospecto está atrapado en un ciclo de análisis sin avanzar. Parálisis de decisión.',
            'psychology' => 'La "parálisis por análisis" ocurre cuando el comprador tiene demasiada información y demasiadas opciones. Su cerebro entra en un loop de evaluación infinita donde cada nueva revisión genera más dudas en lugar de claridad. La solución no es más información — es simplificación y guía.',
            'meaning' => [
                'Muchas sesiones y visitas pero sin avanzar a cierre',
                'Múltiples personas revisando (muchos guests)',
                'Cotización con mucho tiempo de vida',
                'Score de probabilidad bajo a pesar de la actividad',
            ],
            'do' => [
                'Simplifica: "De todo lo que incluye, ¿qué es lo más importante para ti?"',
                'Reduce opciones: "Te recomiendo esta configuración porque..."',
                'Pon un deadline suave: "Los precios son válidos hasta [fecha]"',
                'Ofrece una opción "base" más pequeña para facilitar la decisión',
                'Agenda una llamada para "definir juntos los próximos pasos"',
            ],
            'dont' => [
                'NO mandes más información — ya tiene demasiada',
                'NO agregues más opciones a la cotización',
                'NO seas pasivo esperando que se decida solo',
                'NO te frustres — ayúdalo a salir del loop',
            ],
            'messages' => [
                [
                    'canal' => '📞 Llamada',
                    'texto' => '"Hola [nombre], sé que has estado evaluando a fondo y quiero ayudarte a simplificar. ¿Qué tal si revisamos juntos cuáles son tus prioridades y te hago una recomendación concreta?"',
                    'nota'  => 'La llamada rompe el loop de análisis digital.',
                ],
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], para facilitarte la decisión, te resumo las 3 razones clave por las que otros clientes nos eligieron:\n\n1. [Beneficio principal]\n2. [Diferenciador]\n3. [Garantía/Seguridad]\n\n¿Agendamos 10 min para definir tu proyecto?',
                    'nota'  => 'Simplifica en 3 puntos. Propón acción concreta.',
                ],
            ],
            'best_channel' => '📞 Llamada — rompe la parálisis digital',
            'goal' => 'Sacar al prospecto del loop de análisis y guiarlo a una decisión.',
        ],

        [
            'bucket'   => 'regreso',
            'icon'     => '🟣',
            'priority' => 'media',
            'type'     => 'reactivacion',
            'tone'     => 'calido',
            'summary'  => 'El prospecto volvió a ver la cotización después de más de 4 días sin actividad. Algo reactivó su interés.',
            'psychology' => 'Un regreso después de días de silencio suele estar vinculado a un "trigger externo": le recordaron la necesidad, venció un plazo, otro proveedor falló, o simplemente ahora tiene más claridad. Es un momento de alta receptividad — está buscando retomar.',
            'meaning' => [
                'Más de 4 días sin actividad y acaba de regresar',
                'Última vista en las últimas 48 horas',
                'Algo cambió en su contexto',
                'Señal de que tu propuesta sigue vigente en su mente',
            ],
            'do' => [
                'Contacta hoy mismo con un tono cercano',
                'Pregunta cómo va su proyecto — muestra interés genuino',
                'Ofrece actualizar la propuesta si es necesario',
                'Sé breve y directo — "Vi que retomaste la cotización, ¿puedo ayudarte?"',
            ],
            'dont' => [
                'NO preguntes por qué tardó en volver',
                'NO reenvíes la cotización sin preguntar primero',
                'NO asumas que la situación es la misma de antes',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], ¿cómo estás? Vi que revisaste la cotización nuevamente. ¿Hay algo en lo que pueda ayudarte o algún ajuste que necesites?',
                    'nota'  => 'Simple, cálido. Muestra disponibilidad.',
                ],
            ],
            'best_channel' => '📱 WhatsApp — cercano y rápido',
            'goal' => 'Reestablecer contacto y entender la nueva situación.',
        ],

        [
            'bucket'   => 'comparando',
            'icon'     => '🔘',
            'priority' => 'media',
            'type'     => 'competencia',
            'tone'     => 'estrategico',
            'summary'  => 'Múltiples IPs con engagement real (no bots). Indica que la cotización está siendo compartida para comparar o validar con un comité.',
            'psychology' => 'Cuando múltiples personas con engagement real revisan tu cotización, hay un proceso de comparación activo. Puede ser un comité de compra (positivo — estás en la shortlist) o compartieron con un competidor/asesor externo para validar precios. Tu estrategia debe ser diferenciarte por valor, no solo por precio.',
            'meaning' => [
                'Dos o más IPs distintas con interacción real (JS events)',
                'No son bots: hay scroll, clics, tiempo de lectura',
                'Probablemente un comité de compra o comparación de proveedores',
                'Tu cotización está siendo evaluada activamente por múltiples personas',
            ],
            'do' => [
                'Diferénciate: destaca lo que te hace único vs. competencia',
                'Pregunta si necesitan una propuesta ajustada para otro tomador de decisión',
                'Comparte testimonios o casos de éxito similares',
                'Ofrece una tabla comparativa de beneficios (sin mencionar competidores por nombre)',
                'Muestra confianza: "Estamos seguros de nuestra propuesta"',
            ],
            'dont' => [
                'NO hables mal de competidores — te hace ver inseguro',
                'NO bajes precios preventivamente',
                'NO te muestres nervioso por la comparación — es proceso normal',
                'NO ignores esta señal — tu competencia puede estar actuando',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], sé que evaluar opciones es parte del proceso y me parece muy bien. Si hay algo en lo que quieras que profundicemos o si necesitas un comparativo de beneficios, con gusto te lo preparo.',
                    'nota'  => 'Confianza, no defensividad. Ofrece herramientas de decisión.',
                ],
            ],
            'best_channel' => '📧 Email con material comparativo + 📱 WhatsApp de seguimiento',
            'goal' => 'Posicionarte como la mejor opción en la comparación.',
        ],

        [
            'bucket'   => 'enfriandose',
            'icon'     => '🔵',
            'priority' => 'baja',
            'type'     => 'rescate',
            'tone'     => 'estrategico',
            'summary'  => 'Tuvo interés real (scroll, vistas, aperturas) pero dejó de ver la cotización. Se está enfriando — aún hay tiempo de recuperar.',
            'psychology' => 'Un prospecto que se enfría no es un prospecto perdido. Algo más capturó su atención, sus prioridades cambiaron, o simplemente está procrastinando. La clave es reactivar sin parecer desesperado. Un toque ligero puede ser suficiente para volver a ponerlo en marcha.',
            'meaning' => [
                'Tuvo engagement previo confirmado (scroll, visible, opens)',
                'Ha dejado de interactuar con la cotización',
                'No es "perdido" — es "pausado"',
                'Hay base de interés sobre la cual reconstruir',
            ],
            'do' => [
                'Un mensaje ligero de seguimiento — no presión',
                'Comparte algo nuevo: caso de éxito, actualización, novedad',
                'Pregunta abierta: "¿Cómo va tu proyecto?"',
                'Si tenía foco en precio, ofrece una promoción o condición especial',
                'Programa un recordatorio si no responde en 5-7 días',
            ],
            'dont' => [
                'NO mandes un mensaje de reproche — "¿Ya no te interesa?"',
                'NO lo borres de tu pipeline — aún tiene potencial',
                'NO mandes 3 mensajes seguidos sin respuesta',
                'NO ofrezcas descuento de entrada — primero intenta reactivar sin él',
            ],
            'messages' => [
                [
                    'canal' => '📱 WhatsApp',
                    'texto' => 'Hola [nombre], espero que estés bien. Te comparto [dato de valor: noticia del sector, caso de éxito, nueva funcionalidad]. La cotización que preparamos sigue vigente. ¿Hay algo en lo que pueda ayudarte?',
                    'nota'  => 'Aporta valor primero, luego recuerda la cotización.',
                ],
                [
                    'canal' => '📧 Email',
                    'texto' => 'Hola [nombre],\n\nQuería darte un breve update: [novedad relevante de tu empresa o industria].\n\nTu cotización sigue disponible y podemos ajustarla si tus necesidades han cambiado.\n\n¿Te gustaría agendar una llamada rápida?\n\nSaludos',
                    'nota'  => 'Email de reactivación con valor. No es un simple "¿sigues interesado?"',
                ],
            ],
            'best_channel' => '📱 WhatsApp con contenido de valor > 📧 Email de reactivación',
            'goal' => 'Reactivar el interés y volver a poner la cotización en su radar.',
        ],

    ];

    // ─── OBJECIONES GENÉRICAS ─────────────────────────────────

    $objeciones = [
        [
            'trigger'  => '💰 "Está muy caro" / "No tengo presupuesto"',
            'response' => 'Entiendo tu preocupación. Permíteme mostrarte qué incluye cada punto y por qué es una inversión, no un gasto. También puedo ofrecerte opciones de pago o ajustar el alcance para que se adapte a tu presupuesto sin sacrificar calidad.',
            'nota'     => 'Nunca bajes precio de inmediato. Primero explora si es un "no puedo" real o un "convénceme". Pregunta: "¿Cuál sería un rango cómodo para ti?"',
        ],
        [
            'trigger'  => '⏳ "Necesito pensarlo" / "Déjame consultarlo"',
            'response' => 'Por supuesto, es una decisión importante. Para ayudarte a evaluar mejor, ¿hay algún punto específico que te gustaría que aclare? Puedo prepararte un resumen con los puntos clave para que lo compartas con quien necesites.',
            'nota'     => 'No es un "no" — es un "necesito más elementos". Ofrece herramientas para su proceso de decisión. Pregunta: "¿Con quién lo vas a consultar? ¿Necesitas que prepare algo para esa persona?"',
        ],
        [
            'trigger'  => '🏪 "Estoy viendo otras opciones"',
            'response' => 'Me parece perfecto comparar, es lo más inteligente. Lo que puedo asegurarte es [tu diferenciador principal]. ¿Hay algo específico que estés comparando? Te puedo explicar cómo nos diferenciamos en ese punto.',
            'nota'     => 'Muestra seguridad, no inseguridad. Pregunta qué criterios está usando para comparar y posiciónate en esos criterios.',
        ],
        [
            'trigger'  => '📅 "No es buen momento" / "Más adelante"',
            'response' => 'Entiendo que el timing es importante. ¿Para cuándo estarías planeando? Puedo reservar las condiciones actuales y contactarte cuando sea el momento ideal. ¿Te parece si agendo un seguimiento para [fecha]?',
            'nota'     => 'Fija una fecha concreta. "Más adelante" sin fecha = nunca. Obtén un compromiso de timing.',
        ],
        [
            'trigger'  => '🤔 "No estoy seguro de que sea lo que necesito"',
            'response' => 'Esa es la duda más importante de resolver. Cuéntame exactamente qué necesitas lograr y te digo honestamente si nuestra solución es la adecuada. Si no lo es, te lo diré — prefiero recomendarte bien que venderte algo que no te funcione.',
            'nota'     => 'La honestidad radical genera confianza. Si tu producto no es el ideal, decirlo te posiciona como asesor de confianza para futuras oportunidades.',
        ],
        [
            'trigger'  => '👤 "Necesito la aprobación de alguien más"',
            'response' => '¡Entendido! ¿Te gustaría que prepare un resumen ejecutivo para esa persona? También puedo agendar una presentación breve con ambos. ¿Qué le importa más a quien da la aprobación: el costo, los beneficios o los plazos?',
            'nota'     => 'Identifica al tomador de decisión. Prepara material específico para esa persona. Ofrece involucrar a tu equipo senior si el deal lo amerita.',
        ],
        [
            'trigger'  => '📉 "Vi algo más barato"',
            'response' => 'Es posible que encuentres precios más bajos. La pregunta clave es: ¿qué incluye ese precio? Te invito a comparar punto por punto. Nuestro precio incluye [diferenciadores: garantía, soporte, calidad, experiencia]. A veces lo barato sale caro.',
            'nota'     => 'No compitas solo en precio — compite en valor total. Pregunta: "¿Puedo ver qué te están ofreciendo para hacerte una comparación justa?"',
        ],
    ];

    return [
        'buckets'    => $buckets,
        'objeciones' => $objeciones,
    ];
}
