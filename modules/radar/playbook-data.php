<?php
// ============================================================
//  CotizaApp — modules/radar/playbook-data.php
//  Playbook de seguimiento por bucket — genérico para cualquier negocio
//  Cada bucket del radar tiene su guía: psicología, qué hacer,
//  qué no hacer, mensajes sugeridos y objetivo.
//  Basado en psicología de compra de alto valor + ventas consultivas.
// ============================================================

defined('COTIZAAPP') or die;

// Forzar scope global — este archivo se carga dentro de Router::app()
// que ejecuta require dentro de un método (scope local).
$GLOBALS['PLAYBOOK'] = [

    // ─── ALTA PRIORIDAD ──────────────────────────────────────────

    'onfire' => [
        'priority'   => 'crítica',
        'tone'       => 'hot',
        'summary'    => 'Señal premium. Leyó todo, revisó precios, scrolleó al fondo, volvió más de una vez.',
        'psychology' => 'No es curiosidad — es alguien tomando una decisión real. El nivel de atención invertido es muy alto. Lo que necesita ahora no es más información sino una razón para decirse a sí mismo que es la decisión correcta.',
        'meaning'    => [
            'Hay intensidad real y atención invertida.',
            'Puede cerrar pronto si se maneja bien.',
            'El precio no suele ser el freno aquí — el freno es el miedo a equivocarse.',
        ],
        'do' => [
            'Tratarlo como el lead más importante del día.',
            'Reforzar su elección — que ya decidió bien.',
            'Hacer el siguiente paso concreto y sin fricción.',
        ],
        'dont' => [
            'No contestar como si fuera seguimiento frío.',
            'No dejarlo enfriar más de 24 horas.',
            'No abrir con precio ni con lista de beneficios.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿tiene alguna duda sobre la cotización o qué le falta para que arranquemos?',
                'nota'  => 'Directa y abierta. Invita al cliente a nombrar lo que falta sin presionarlo — y si ya está listo, lo dice.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], esta semana tenemos disponibilidad limitada. Si quiere que arranquemos este mes, este es buen momento para apartar su lugar.',
                'nota'  => 'Urgencia real de capacidad. Para alguien con señal fuerte, este mensaje es suficiente para mover la decisión.',
            ],
            [
                'canal' => 'Llamada — solo si no respondió WhatsApp en 48h',
                'texto' => '[Nombre], le llamo rápido porque quería consultarle un detalle de su cotización antes de agendar.',
                'nota'  => 'Pretexto concreto que justifica la llamada. En la llamada se mencionan tiempos de entrega y se cierra agenda.',
            ],
        ],
        'best_channel' => 'WhatsApp inmediato. Llamada solo como segundo recurso con pretexto real.',
        'goal'         => 'Convertir intención fuerte en avance operativo.',
    ],

    'inminente' => [
        'priority'   => 'crítica',
        'tone'       => 'danger',
        'summary'    => 'Señales fuertes de decisión en las últimas 36h. FIT alto + señal fuerte de engagement — actúa hoy.',
        'psychology' => 'Este cliente no solo leyó tu cotización — se fue, lo pensó, y regresó. Ese patrón de "abrir → cerrar → volver después" es el indicador más fuerte de que está tomando una decisión real. Lo que necesita ahora no es más información sino sentir que el siguiente paso es fácil y sin riesgo.',
        'meaning'    => [
            'Regresó después de al menos 1 hora — estaba pensándolo.',
            'No es interés casual — hay intención de compra real.',
            'La oportunidad de cierre es cercana — cada hora cuenta.',
        ],
        'do' => [
            'Actuar hoy mismo.',
            'Hacer el siguiente paso obvio y fácil.',
            'Pregunta directa: ¿qué falta para arrancar?',
        ],
        'dont' => [
            'No mandar seguimiento tibio o genérico.',
            'No explicar la propuesta de nuevo — ya la leyó.',
            'No abrir con precio ni con descuentos.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], esta semana tenemos disponibilidad limitada. Si quiere que arranquemos este mes, este es buen momento para apartar su lugar.',
                'nota'  => 'Urgencia real de capacidad. El cliente siente que está aprovechando un espacio disponible, no que lo están presionando.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿tiene alguna duda sobre su cotización o qué le falta para iniciar?',
                'nota'  => 'Pregunta directa que invita a nombrar el freno. Si ya está listo, lo dice. Si tiene duda, también.',
            ],
        ],
        'best_channel' => 'WhatsApp primero. Si no responde en 2h, intentar llamada con pretexto concreto.',
        'goal'         => 'Cerrar o identificar la objeción final.',
    ],

    'probable_cierre' => [
        'priority'   => 'crítica',
        'tone'       => 'close',
        'summary'    => 'Tu lista de trabajo #1. Cotizaciones con lectura real (15s+), foco en precio y señales cruzadas de intención.',
        'psychology' => 'Esta persona cruzó la línea de curiosidad. No solo vio tu cotización — la leyó a fondo, interactuó con el precio, volvió, o la compartió con alguien. Eso es comportamiento de comprador, no de curioso. Cada hora que pasa sin contacto es una hora donde un competidor puede cerrar.',
        'meaning'    => [
            'Lectura real confirmada: 15+ segundos de atención + foco en precio.',
            'El badge "Motivo" indica QUÉ bucket la activó — úsalo para elegir tu enfoque.',
            'Estas son tus mejores oportunidades AHORA. No mañana, hoy.',
        ],
        'do' => [
            'Revisar el badge de motivo para adaptar tu mensaje.',
            'Contactar HOY — estas cotizaciones tienen ventana de cierre activa.',
            'Si el motivo es precio: no bajes precio, transfiere certeza.',
            'Si el motivo es re-enganche: reconoce que volvió y facilita el siguiente paso.',
        ],
        'dont' => [
            'No tratar todas igual — el motivo te dice cómo abordar cada una.',
            'No esperar a mañana. Si están aquí, la ventana está abierta AHORA.',
            'No mandar mensajes genéricos. Personaliza según la señal.',
            'No asumir que el precio es el problema — casi nunca lo es.',
        ],
        'messages' => [
            [
                'canal' => '📱 WhatsApp — motivo Inminente/On Fire',
                'texto' => '[Nombre], vi que ha estado revisando su cotización. ¿Le gustaría agendar para resolver cualquier duda y arrancar esta semana?',
                'nota'  => 'Directo al cierre. Esta persona ya decidió, solo necesita el empujón final.',
            ],
            [
                'canal' => '📱 WhatsApp — motivo Validando precio',
                'texto' => '[Nombre], ¿qué le parece la propuesta? Si gusta le explico las opciones de pago que manejamos.',
                'nota'  => 'No bajes el precio. Ofrece estructura de pago que haga digerible el monto.',
            ],
            [
                'canal' => 'Llamada — motivo Re-enganche',
                'texto' => 'Hola [Nombre], noté que revisó de nuevo su cotización. ¿Cambió algo en su proyecto o le ajustamos algo?',
                'nota'  => 'Volvió después de días. Llamada > WhatsApp aquí. Algo cambió en su situación.',
            ],
        ],
        'best_channel' => 'Depende del motivo. Inminente/On Fire: WhatsApp directo. Re-enganche: llamada. Precio: WhatsApp con opciones de pago.',
        'goal'         => 'Cerrar o agendar compromiso concreto. Estas no son para "dar seguimiento" — son para cerrar.',
    ],

    'validando_precio' => [
        'priority'   => 'alta',
        'tone'       => 'money',
        'summary'    => 'Revisó los totales varias veces. Está atascado en el número — pero el problema real casi nunca es el precio.',
        'psychology' => 'No tiene problema con el precio — tiene miedo de pagar eso y que salga mal. O está buscando un argumento para justificarlo internamente. Atacar el precio directamente casi siempre es un error aquí.',
        'meaning'    => [
            'Hay foco en totales, revisitas o loops de precio.',
            'Puede estar comparando con otras opciones.',
            'El trabajo es transferir certeza, no bajar el número.',
        ],
        'do' => [
            'Preguntar qué lo está frenando antes de ofrecer cualquier cosa.',
            'Si él saca el precio, entonces sí entra la facilidad de pago.',
            'Hablar de lo que evita (riesgos, pérdida), no solo de lo que incluye.',
        ],
        'dont' => [
            'No abrir con precio ni con descuentos.',
            'No ofrecer descuento sin diagnóstico previo.',
            'No sonar inseguro con el valor de la propuesta.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿hay algo de la propuesta que no quedó del todo claro? A veces por este medio no se lee igual — si gusta podemos hacer una llamada rápida.',
                'nota'  => 'Abre la puerta sin asumir que el problema es precio. Si es precio, él lo dice. Si es otra cosa, también.',
            ],
            [
                'canal' => '📱 WhatsApp — SOLO si él ya mencionó precio',
                'texto' => '[Nombre], algo importante: podemos arrancar con un anticipo parcial y el resto conforme avanzamos. Así no compromete todo de entrada.',
                'nota'  => 'SOLO si el cliente ya abrió el tema de precio. Reencuadra como flujo de caja, no como costo total.',
            ],
        ],
        'best_channel' => 'WhatsApp. La llamada aplica si ya respondió y quiere hablar.',
        'goal'         => 'Diagnosticar el freno real antes de defender precio.',
    ],

    'prediccion_alta' => [
        'priority'   => 'alta',
        'tone'       => 'predict',
        'summary'    => 'FIT alto + edad dentro del ciclo de venta. El modelo predice alta probabilidad de cierre.',
        'psychology' => 'Este prospecto tiene el perfil estadístico de alguien que cierra. No es señal de comportamiento puntual — es que los números se parecen a los de quienes sí compraron. Hay que tratarlo como oportunidad real sin forzar.',
        'meaning'    => [
            'El perfil de engagement coincide con patrones de cierre.',
            'Está dentro de la ventana temporal donde ocurren la mayoría de cierres.',
            'No es certeza — es probabilidad alta. Hay que confirmar con contacto real.',
        ],
        'do' => [
            'Iniciar contacto proactivo si no lo has hecho.',
            'Ofrecer resolver cualquier duda pendiente.',
            'Hacer seguimiento consistente — este lead merece atención prioritaria.',
        ],
        'dont' => [
            'No asumir que ya está listo — el modelo predice, no confirma.',
            'No saturar con múltiples mensajes antes de que responda.',
            'No ignorarlo solo porque no ha dado señal explícita.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿cómo va con la propuesta? Si tiene alguna duda o quiere ajustar algo, con gusto lo vemos.',
                'nota'  => 'Neutro y disponible. Le das razón para responder sin asumir nada sobre su proceso.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], solo para que lo tenga presente: tenemos disponibilidad esta semana si quiere que avancemos.',
                'nota'  => 'Timing suave. No presiona, pero le recuerda que hay un espacio para él.',
            ],
        ],
        'best_channel' => 'WhatsApp.',
        'goal'         => 'Validar la predicción con contacto real — convertir probabilidad en conversación.',
    ],

    // ─── TODOS LOS BUCKETS ───────────────────────────────────────

    'alto_importe' => [
        'priority'   => 'alta',
        'tone'       => 'value',
        'summary'    => 'Cotización por encima del P80 de tu empresa. Ticket alto = proceso de decisión distinto.',
        'psychology' => 'Las decisiones de alto importe toman más tiempo y suelen involucrar a más personas. El cliente no es más difícil — es más cuidadoso. Necesita sentir que el riesgo está controlado y que hay respaldo real detrás de la propuesta.',
        'meaning'    => [
            'El importe está por encima de lo habitual para tu negocio.',
            'Probablemente hay más de una persona evaluando.',
            'El proceso de cierre será más largo — y eso es normal.',
        ],
        'do' => [
            'Ofrecer referencias, casos similares, o evidencia de trabajos anteriores.',
            'Facilitar una conversación con todos los decisores si es posible.',
            'Ser paciente con el timing — presión fuerte aquí espanta.',
        ],
        'dont' => [
            'No tratar igual que una cotización estándar.',
            'No empujar cierre rápido — el ticket alto requiere confianza.',
            'No dar descuento como primer recurso.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿cómo va la evaluación? Si necesita material adicional — fotos, referencias, o detalles técnicos — con gusto se lo preparo.',
                'nota'  => 'Te posicionas como recurso, no como vendedor. En ticket alto, el que da más confianza gana.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], entiendo que una decisión de este nivel se piensa bien. Si quiere podemos agendar una llamada para resolver cualquier duda que tenga.',
                'nota'  => 'Reconoces que es una decisión importante. Eso genera confianza — sabe que no lo vas a presionar.',
            ],
        ],
        'best_channel' => 'WhatsApp. Llamada o videollamada para proyectos grandes.',
        'goal'         => 'Construir confianza suficiente para que el ticket alto no sea un freno.',
    ],

    'decision_activa' => [
        'priority'   => 'alta',
        'tone'       => 'cool',
        'summary'    => '4+ visitas en 48h con horas entre ellas. Está evaluando en serio, va y viene.',
        'psychology' => 'No está desconectado — le está dando vueltas, probablemente consultando internamente o con alguien más. El proceso está avanzando aunque no lo veas. Necesita que le sea fácil preguntar lo que tiene pendiente.',
        'meaning'    => [
            'Sigue activo en su proceso de decisión.',
            'Puede haber más de una persona involucrada.',
            'No confundir silencio con rechazo.',
        ],
        'do' => [
            'Posicionarte como recurso disponible, no como vendedor esperando el sí.',
            'Preguntar si hay alguien más en la decisión.',
            'Hacer fácil preguntar lo que tiene pendiente.',
        ],
        'dont' => [
            'No meter presión — el proceso ya está avanzando.',
            'No mandar info nueva que complique más.',
            'No interpretar el silencio como abandono.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], si tiene alguna pregunta mientras revisa la propuesta, aquí estoy. A veces es más fácil si me pregunta directo.',
                'nota'  => 'No presionas — te ofreces como recurso. El cliente siente apoyo disponible cuando lo necesite.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿tiene alguna duda sobre su cotización o qué le falta para iniciar?',
                'nota'  => 'Segunda opción más directa. Invita a nombrar el freno sin asumir nada.',
            ],
        ],
        'best_channel' => 'WhatsApp. Audio si ya hay confianza.',
        'goal'         => 'Reducir fricción y facilitar la pregunta pendiente.',
    ],

    're_enganche_caliente' => [
        'priority'   => 'alta',
        'tone'       => 'fire',
        'summary'    => 'Regresó tras un gap Y revisó precios. Señal de compra fuerte.',
        'psychology' => 'Este es uno de los buckets con mejor tasa de cierre. El cliente se fue, comparó, pensó — y volvió a revisar tu precio. Eso significa que estás en la mesa de decisión final. El asesor que aparece en ese momento con confianza y claridad tiene ventaja real.',
        'meaning'    => [
            'No es seguimiento desde cero — es una segunda oportunidad con intención.',
            'Probablemente ya tiene otras cotizaciones para comparar.',
            'El que responde primero y con más claridad en este punto suele cerrar.',
        ],
        'do' => [
            'Aparecer rápido — la ventana de comparación activa es corta.',
            'Tono seguro y directo, no ansioso.',
            'Dar un motivo concreto para avanzar ya.',
        ],
        'dont' => [
            'No reiniciar la venta desde cero.',
            'No asumir que está listo sin preguntar.',
            'No sonar a que llevas la cuenta de cuándo desapareció.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿cómo va el proyecto? ¿Sigue en pie? Si gusta le explico cualquier detalle de la cotización.',
                'nota'  => 'Retoma sin drama. Ofrecer explicar le da razón para responder — y abre la puerta a resolver lo que la competencia no le resolvió.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], tenemos disponibilidad limitada esta semana. Si quiere arrancar este mes, es buen momento para apartar su lugar.',
                'nota'  => 'Urgencia real de capacidad. Para quien ya está comparando, un espacio disponible es un argumento concreto.',
            ],
        ],
        'best_channel' => 'WhatsApp.',
        'goal'         => 'Reactivar conversación sin fricción y aprovechar la ventana de decisión.',
    ],

    're_enganche' => [
        'priority'   => 'alta',
        'tone'       => 'purple',
        'summary'    => 'Regresó tras un gap con señal de interés, pero sin foco directo en precio.',
        'psychology' => 'Estuvo ausente y volvió — eso ya es señal positiva. Pero a diferencia del re-enganche caliente, aquí no revisó precios directamente. Puede estar retomando el proyecto, comparando sin urgencia, o simplemente revisitando. El enfoque correcto es retomar con naturalidad.',
        'meaning'    => [
            'El proyecto volvió a tomar relevancia para él.',
            'No hay urgencia de precio — pero hay interés renovado.',
            'Es momento de abrir conversación, no de cerrar.',
        ],
        'do' => [
            'Retomar como continuidad, no como inicio.',
            'Preguntar si algo cambió desde la última vez.',
            'Tono natural — sin drama por el tiempo que pasó.',
        ],
        'dont' => [
            'No tratar como lead nuevo.',
            'No mencionar cuánto tiempo estuvo sin responder.',
            'No abrir con beneficios o descuentos.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿cómo va todo? ¿El proyecto sigue en pie?',
                'nota'  => 'Simple y natural. Si algo cambió, lo dice. Si solo está revisando, retoma conversación.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿gusta que le actualice la cotización o ajustemos algo?',
                'nota'  => 'Ofreces valor concreto sin asumir nada. Le das razón para responder.',
            ],
        ],
        'best_channel' => 'WhatsApp.',
        'goal'         => 'Retomar contacto natural y entender si el interés es real.',
    ],

    'multi_persona' => [
        'priority'   => 'crítica',
        'tone'       => 'hot',
        'summary'    => 'Múltiples personas están evaluando la cotización. Señal fuerte de decisión compartida en curso.',
        'psychology' => 'En ventas de alto valor, cuando 2 o más personas revisan la misma cotización desde diferentes dispositivos, significa que la decisión ya está en mesa. Tu contacto la compartió con quien necesita convencer: pareja, socio, familiar. Esto es una de las señales más fuertes de intención de compra — el cliente ya pasó de "explorar" a "consultar para decidir".',
        'meaning'    => [
            '2+ dispositivos diferentes vieron la cotización — no es la misma persona.',
            'Tu contacto ya la compartió con alguien más para tomar la decisión juntos.',
            'La cotización está siendo evaluada activamente por múltiples decisores.',
            'Si hay 3+ dispositivos, es muy probable que sea familia o comité completo.',
        ],
        'do' => [
            'Llamar a tu contacto y preguntar si hay algo que puedas aclarar para él o para las otras personas involucradas.',
            'Ofrecerte para una reunión breve con todos los que están evaluando.',
            'Preparar un resumen ejecutivo que sea fácil de compartir y explicar.',
            'Destacar garantías, tiempos de entrega y proceso — es lo que más se comparte entre decisores.',
            'Si es pareja: hablar de cómo el proyecto beneficia a ambos, no solo a uno.',
        ],
        'dont' => [
            'No presionar cierre individual — la decisión es compartida.',
            'No asumir que tu contacto decide solo — puede ser el promotor, no el decisor.',
            'No mandar mensajes técnicos difíciles de explicar a otros.',
            'No ignorar esta señal — es de las más valiosas del Radar.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿cómo va la decisión del proyecto? Si necesita que le prepare algo para revisarlo con su [pareja/socio/familia], con gusto lo hago. Quiero que todos estén tranquilos con la inversión.',
                'nota'  => 'Reconoce indirectamente que hay más personas involucradas sin ser invasivo. "Todos estén tranquilos" habla a la confianza grupal.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], le recuerdo que contamos con garantía por escrito en tiempos y calidad. Si alguien más necesita verla, se la mando con gusto.',
                'nota'  => 'La garantía es el argumento #1 que se comparte entre decisores. Ofrecerla proactivamente acelera el consenso.',
            ],
            [
                'canal' => 'Llamada',
                'texto' => 'Llamar y ofrecer: "¿Le gustaría que agendemos 15 minutos para resolver dudas juntos? Puedo explicarle el proceso a todos los involucrados."',
                'nota'  => 'Una reunión grupal cierra más rápido que ir uno por uno. El cliente se siente atendido a nivel premium.',
            ],
        ],
        'best_channel' => 'Llamada directa o WhatsApp. Proponer reunión breve con todos los involucrados es la jugada más fuerte.',
        'goal'         => 'Facilitar el consenso entre todos los decisores. El que cierra primero gana.',
    ],

    'revision_profunda' => [
        'priority'   => 'media',
        'tone'       => 'indigo',
        'summary'    => 'Tiempo de lectura muy alto. Leyó todo con detalle. Perfil analítico.',
        'psychology' => 'Este cliente decide despacio y eso está bien — es su proceso. No es indecisión, es metodología. Lo que necesita es sentir que tiene toda la información correcta. Cualquier intento de apurarlo genera resistencia.',
        'meaning'    => [
            'Hay lectura real y tiempo invertido.',
            'Casi siempre tiene una pregunta técnica o de detalle pendiente.',
            'No es lead superficial — está evaluando en serio.',
        ],
        'do' => [
            'Abrirle la puerta a preguntas técnicas o de detalle.',
            'Ofrecer evidencia visual: fotos, demos, o ejemplos de proyectos similares.',
            'Respetar su ritmo — no apurar.',
        ],
        'dont' => [
            'No vender como si no hubiera entendido nada.',
            'No repetir lo que ya leyó.',
            'No meter urgencia artificial.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿alguna pregunta sobre los detalles de la propuesta? A veces hay cosas que por este medio no se leen igual.',
                'nota'  => 'Abre la puerta a la pregunta técnica que tiene guardada. Cuando la hace, avanza.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], si quiere le mando ejemplos de proyectos similares al suyo para que vea el resultado final.',
                'nota'  => 'Para el perfil analítico, ver resultados reales es más convincente que cualquier argumento escrito.',
            ],
        ],
        'best_channel' => 'WhatsApp. Llamada solo si ya hay confianza establecida.',
        'goal'         => 'Que haga la pregunta que tiene pendiente — ahí está la clave.',
    ],

    'vistas_multiples' => [
        'priority'   => 'media',
        'tone'       => 'green',
        'summary'    => 'Varias visitas sin profundidad. Interés real pero sin señal de decisión todavía.',
        'psychology' => 'Está asomándose pero no termina de entrar. Puede ser curiosidad, proceso temprano, o que espera hablar con alguien. El objetivo aquí no es cerrar — es lograr que responda algo.',
        'meaning'    => [
            'Hay movimiento, no es abandono.',
            'No hay señal de precio ni de decisión todavía.',
            'Un engagement mínimo es el primer paso para todo lo demás.',
        ],
        'do' => [
            'Mensaje muy corto — una pregunta.',
            'Buscar respuesta, no cierre.',
            'No saturar con información que aún no pidió.',
        ],
        'dont' => [
            'No asumir cierre por volumen de visitas.',
            'No mandar beneficios sin que haya preguntado.',
            'No meter presión — es etapa temprana.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿ya pudo ver bien la propuesta?',
                'nota'  => 'El mensaje más corto del playbook. Cuando no ha respondido nada, un mensaje corto tiene más oportunidad que uno largo.',
            ],
            [
                'canal' => '📱 WhatsApp — si no responde',
                'texto' => '[Nombre], ¿tiene alguna duda sobre la cotización o qué le falta para iniciar?',
                'nota'  => 'Segunda oportunidad. Invita a la duda sin presionar.',
            ],
        ],
        'best_channel' => 'WhatsApp.',
        'goal'         => 'Lograr que responda algo — convertir actividad en conversación.',
    ],

    'hesitacion' => [
        'priority'   => 'media',
        'tone'       => 'amber',
        'summary'    => 'Actividad entre 24h y 7 días atrás. Algo lo frenó — pero no se fue.',
        'psychology' => 'No es abandono — hay algo específico que lo está frenando: una duda no dicha, comparación activa, timing no resuelto, o simplemente está ocupado. El primer objetivo es diagnosticar, no cerrar.',
        'meaning'    => [
            'No está listo para presión fuerte.',
            'Casi siempre hay una duda concreta detrás.',
            'Presionar antes de diagnosticar empeora el proceso.',
        ],
        'do' => [
            'Preguntar qué lo está frenando — directamente.',
            'Normalizar que revisar bien es parte del proceso.',
            'Quitar presión de timing si aplica.',
        ],
        'dont' => [
            'No cerrar agresivamente.',
            'No mandar beneficios sin saber qué lo frena.',
            'No asumir que el silencio es rechazo.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿cómo va todo? ¿El proyecto sigue en pie?',
                'nota'  => 'Sin presión, sin juicio. Si algo cambió, lo dice. Si solo está ocupado, retoma natural.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], solo para que lo tenga claro: arrancamos cuando usted lo autorice. No hay prisa de nuestra parte. Si quiere que avancemos en detalles mientras tanto, dígame.',
                'nota'  => 'Quitar la presión percibida paradójicamente lo mueve más rápido.',
            ],
        ],
        'best_channel' => 'WhatsApp.',
        'goal'         => 'Identificar el freno real antes de cualquier otro movimiento.',
    ],

    'sobre_analisis' => [
        'priority'   => 'media',
        'tone'       => 'earth',
        'summary'    => '20+ sesiones, semanas sin decidir. Tiene toda la información pero sigue sin avanzar.',
        'psychology' => 'Más información ya no ayuda — ya la tiene toda. El bloqueo es otro: miedo a equivocarse, proceso interno que no avanza, alguien más que tiene que aprobar, o simplemente espera que alguien lo empuje con claridad.',
        'meaning'    => [
            'No es un lead muerto — es un lead atascado.',
            'La táctica correcta depende del motivo del atasco.',
            'Más presión sin diagnóstico empeora el proceso.',
        ],
        'do' => [
            'Diagnosticar antes de empujar — preguntar qué falta.',
            'Crear urgencia real con capacidad o agenda, no presión artificial.',
            'Invitar la objeción directamente — darle permiso de decir lo que lo frena.',
        ],
        'dont' => [
            'No asumir que solo falta un descuento.',
            'No insistir igual que a un lead caliente.',
            'No mandar mensajes largos con más información.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], llevo un tiempo dándole seguimiento a su proyecto. Dígame si hay algo que no le haya convencido o si todavía está en proceso.',
                'nota'  => 'Invitas la objeción directamente. El que tiene algo pendiente lo agradece.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], tenemos disponibilidad limitada esta semana. Si quiere que arranquemos este mes, es buen momento.',
                'nota'  => 'Urgencia real de capacidad. Para alguien que lleva semanas pensándolo, una razón concreta de timing a veces es lo único que mueve.',
            ],
            [
                'canal' => '📱 WhatsApp — si hay sospecha de objeción no dicha',
                'texto' => '[Nombre], dígame si hay algo que no le convenció. Prefiero saberlo para poder ayudarle mejor.',
                'nota'  => 'Corto y honesto. Solo necesita que alguien le dé permiso de decirlo.',
            ],
        ],
        'best_channel' => 'WhatsApp directo. Llamada si ya hay relación suficiente.',
        'goal'         => 'Cortar el ciclo de análisis — con una pregunta, no con más información.',
    ],

    'revivio' => [
        'priority'   => 'alta',
        'tone'       => 'violet',
        'summary'    => 'Inactivo 30+ días. Volvió a revisar inesperadamente.',
        'psychology' => 'Algo cambió en su situación — se cayó otro proveedor, mejoró su liquidez, retomó el proyecto. Esa ventana vale mucho y se cierra rápido. El error es tratarlo como lead nuevo o reclamar el tiempo que pasó.',
        'meaning'    => [
            'No es tráfico casual — algo lo trajo de vuelta.',
            'El proyecto volvió a tomar relevancia.',
            'Ventana corta: hay que aparecer pronto.',
        ],
        'do' => [
            'Retomar como continuidad, no como inicio.',
            'Preguntar si sigue siendo el mismo proyecto o cambió algo.',
            'Tono natural — sin drama por el tiempo que pasó.',
        ],
        'dont' => [
            'No escribir como si fuera lead nuevo.',
            'No mencionar cuánto tiempo estuvo sin responder.',
            'No mandar la misma propuesta vieja sin preguntar si cambió algo.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¡hola! Dándole seguimiento a su cotización — ¿gusta que le hagamos algún ajuste?',
                'nota'  => 'Retoma como continuidad natural. Ofrecer ajustes es razón concreta para volver a platicar.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿sigue siendo el mismo proyecto o cambió algo desde que lo platicamos?',
                'nota'  => 'Alternativa más abierta. Evita mandar la misma propuesta vieja sin contexto.',
            ],
        ],
        'best_channel' => 'WhatsApp.',
        'goal'         => 'Reabrir la oportunidad con contexto actualizado.',
    ],

    'regreso' => [
        'priority'   => 'media',
        'tone'       => 'purple',
        'summary'    => 'Ausente 4+ días. Volvió a revisar en las últimas 48h. Bucket con buen volumen de cierre.',
        'psychology' => 'Este es un bucket de alto potencial. En ventas de alto valor, el cliente necesita tiempo para pensar y la competencia tarda en responder. Cuando alguien regresa después de varios días, casi siempre ya tiene con qué comparar. El asesor que aparece en ese momento con confianza tiene ventaja real.',
        'meaning'    => [
            'Ya tuvo tiempo de evaluar otras opciones.',
            'Probablemente ya recibió cotizaciones de competidores.',
            'Regresó por algo — precio, confianza, o una duda que no le resolvieron.',
        ],
        'do' => [
            'Aparecer rápido — la ventana es corta.',
            'Tono seguro, no ansioso.',
            'Dar una razón concreta para avanzar.',
        ],
        'dont' => [
            'No reiniciar la venta desde cero.',
            'No preguntar con quién lo platicó.',
            'No asumir que ya está listo sin preguntar.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿cómo va el proyecto? ¿Sigue en pie? ¿Gusta que le explique algo de la cotización?',
                'nota'  => 'Retoma sin drama. Ofrecer explicar le da razón para responder.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], tenemos disponibilidad esta semana. Si quiere que arranquemos, es buen momento para apartar su lugar.',
                'nota'  => 'Para quien ya está comparando, disponibilidad inmediata es un argumento concreto.',
            ],
        ],
        'best_channel' => 'WhatsApp.',
        'goal'         => 'Retomar contacto de forma natural.',
    ],

    'comparando' => [
        'priority'   => 'media',
        'tone'       => 'orange',
        'summary'    => '2+ IPs distintas en 24h. La cotización se está comparando o se compartió.',
        'psychology' => 'Hay más de una persona o más de un dispositivo evaluando esto. Puede ser competencia activa o decisión en equipo. El error más común aquí es bajar precio de entrada — antes hay que ayudar a comparar bien.',
        'meaning'    => [
            'La propuesta salió del círculo inicial.',
            'Puede haber otras opciones sobre la mesa.',
            'El que ayuda a comparar mejor gana — no el más barato.',
        ],
        'do' => [
            'Ayudar a comparar criterios correctos: garantía, tiempos, proceso, soporte.',
            'Hacer las preguntas que el cliente no le hizo a los otros proveedores.',
            'Ser claro y directo — no defensivo.',
        ],
        'dont' => [
            'No bajar precio de entrada sin diagnóstico.',
            'No atacar a la competencia.',
            'No sonar a "somos los mejores" sin sustento.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], entiendo que tiene que evaluar opciones. Si tiene alguna duda de cómo trabajamos o qué incluye exactamente, con gusto se lo explico.',
                'nota'  => 'Reconoces que puede estar comparando sin juzgarlo. Eso posiciona al asesor como el más transparente.',
            ],
            [
                'canal' => 'Llamada',
                'texto' => '[Nombre], le llamo porque quiero asegurarme de que tiene toda la información para evaluar bien. ¿Tiene dos minutos?',
                'nota'  => '"Para evaluar bien" pone al asesor del lado del cliente.',
            ],
            [
                'canal' => '📱 WhatsApp — SOLO si él ya mencionó comparación',
                'texto' => '[Nombre], algo que casi nadie menciona pero que siempre marca diferencia: nosotros ofrecemos garantía por escrito. Está en el contrato.',
                'nota'  => 'SOLO si el cliente ya abrió el tema. El argumento se sostiene solo.',
            ],
        ],
        'best_channel' => 'WhatsApp. Llamada si ya hay conversación activa.',
        'goal'         => 'Ser el que ayuda a comparar mejor — eso cierra más que bajar precio.',
    ],

    'enfriandose' => [
        'priority'   => 'media',
        'tone'       => 'ice',
        'summary'    => 'Tuvo historial activo pero lleva 48h+ sin revisarla. Se está alejando.',
        'psychology' => 'No todo enfriamiento es rechazo. A veces es saturación, agenda ocupada, o simplemente perdió el hilo. La clave es reaparecer con algo útil y concreto — no con urgencia ni con presión. El tercer contacto sin respuesta es el que más cierres genera paradójicamente, porque le da salida honrosa.',
        'meaning'    => [
            'Hubo interés real — no es un lead frío.',
            'Algo interrumpió el proceso, no necesariamente el interés.',
            'Tres contactos máximo sin respuesta — después esperar 30 días.',
        ],
        'do' => [
            'Primer contacto: ofrecer algo concreto y útil.',
            'Segundo contacto: pregunta directa y simple.',
            'Tercer contacto: quitar toda presión — muchos responden a ese.',
        ],
        'dont' => [
            'No crear urgencia falsa.',
            'No mandar beneficios sin diagnóstico.',
            'No contactar más de 3 veces sin respuesta.',
        ],
        'messages' => [
            [
                'canal' => '📱 WhatsApp — primer contacto',
                'texto' => '[Nombre], terminamos un proyecto muy similar al suyo. Si quiere le mando fotos o detalles para que vea cómo quedó.',
                'nota'  => 'Ofreces algo concreto sin pedir nada. La razón más natural para retomar contacto.',
            ],
            [
                'canal' => '📱 WhatsApp — segundo contacto',
                'texto' => '[Nombre], ¿cómo va su proyecto? ¿Sigue en pie?',
                'nota'  => 'Simple y directo. Sin drama, sin asumir nada.',
            ],
            [
                'canal' => '📱 WhatsApp — tercer contacto',
                'texto' => '[Nombre], le mando este mensaje para no perder el contacto. Cuando guste retomar, aquí estamos con gusto.',
                'nota'  => 'Quitas toda presión. El cliente se siente libre — y muchos responden después de este mensaje.',
            ],
        ],
        'best_channel' => 'WhatsApp. Máximo 3 contactos sin respuesta.',
        'goal'         => 'Reactivar sin fricción — o dar salida honrosa y esperar 30 días.',
    ],

    'no_abierta' => [
        'priority'   => 'media',
        'tone'       => 'red',
        'summary'    => 'Cotización enviada hace 24h+ sin evidencia de apertura por el cliente.',
        'psychology' => 'No la vio — o no le llegó, o se le pasó, o no le llamó la atención. No es rechazo. Es que ni siquiera empezó el proceso. El objetivo es lograr que la abra, no que compre.',
        'meaning'    => [
            'No hubo apertura — no hay evaluación todavía.',
            'Puede ser problema de canal (spam, número equivocado, link roto).',
            'No es rechazo — es desconocimiento.',
        ],
        'do' => [
            'Verificar que el link funcione y que lo recibió.',
            'Reenviar por un canal diferente si es posible.',
            'Mensaje ultra corto — solo lograr que abra el link.',
        ],
        'dont' => [
            'No asumir desinterés.',
            'No mandar mensaje largo explicando la propuesta.',
            'No dar seguimiento de cierre a alguien que ni siquiera vio la cotización.',
        ],
        'messages' => [
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], le envié su cotización hace unos días. ¿Le llegó bien? Se la puedo reenviar si gusta.',
                'nota'  => 'No asumes nada. Si no le llegó, la reenvías. Si la ignoró, la abre ahora.',
            ],
            [
                'canal' => 'WhatsApp',
                'texto' => '[Nombre], ¿pudo ver la propuesta que le envié? Dígame si tiene alguna duda.',
                'nota'  => 'Alternativa más directa. Para clientes con los que ya hay conversación previa.',
            ],
        ],
        'best_channel' => 'WhatsApp. Si no responde, probar llamada breve.',
        'goal'         => 'Lograr que abra la cotización. Sin apertura no hay proceso.',
    ],
];
