# Catálogo de Tips de Ventas — CotizaCloud AI

**Qué es:** el banco de conocimiento del que el motor saca los *tips* del termómetro
(y enriquece el Consejo del reporte). Cada tip **enseña una técnica de ventas real**
—no resume los números del asesor—, personalizada con su dato del día y con el tono
según su score.

**Fuente:** metodología probada (LAER, escalera de compromiso, value-before-price,
closing, qualifying, resurrección, HEAR — repo `louisblythe/Sales-Skills`), adaptada al
vendedor mexicano de SMB (cocina/mueble/servicio, WhatsApp y cita presencial, ciclo corto).

> **Estado: BORRADOR v2 para curar.** José revisa: ¿suena a lo que un buen gerente le
> diría a su vendedor? Quita, corrige con TUS palabras, agrega.

## Cómo rota el motor (para que NO se repita) — LEER

Cada debilidad tiene **3-5 técnicas ordenadas como currículo** (de básica a fina). Dos capas de variación:

1. **Rotación de técnica.** El motor guarda qué técnicas ya le mostró a cada asesor
   (`asesor_id, tecnica_id, fecha`). Muestra la #1; la próxima, la #2; luego la #3…
   **No repite una técnica hasta agotar las de esa debilidad.**
2. **Gancho fresco.** Aunque toque la misma técnica, el **dato del día cambia** (otro
   folio, cliente, vistas) → mismo mensaje, ejemplo distinto.
3. **Al agotar:** si la debilidad mejoró → pasa a la siguiente debilidad #1; si no →
   re-cicla desde la #1 con dato nuevo (ya pasaron semanas).
4. **Tono por score** (los "diferenciales"): bajo → firme y directo · medio → coach que
   corrige · alto → refina y reconoce.
5. **Fact-lint:** el gancho (números) sale de la BD; la técnica es fija. Nunca afirmar del
   asesor algo que el dato no diga.

**Anatomía:** `[gancho con su dato] + [técnica con nombre] + [cómo, con palabras exactas]`.
Cada técnica lleva un `handle` (id) para que el motor lleve el control de rotación.

---

## 1. Descarta sin CALIFICAR (tira leads a ciegas)
*Detección: descartes sin cita / muy rápido.*
> Reencuadre clave: descartar no es malo; descartar **sin calificar** sí. El buen vendedor
> suelta rápido al que no es comprador — pero después de preguntar, no antes.

1. `d1_calificar` — **Califica antes de tirar.** Antes de descartar, 2-3 preguntas: *"¿para
   cuándo lo necesita?"*, *"¿ya vio otras opciones?"*, *"¿es usted quien decide?"*. Si de
   verdad no es comprador, suéltala sin culpa. Descartar sin preguntar es tirar ventas.
2. `d1_escalera` — **Escalera de pequeños sí.** Descarta porque salta directo al cierre y el
   cliente aún no está listo. Sube por escalones: *"¿te resolvió la duda del material?"* →
   *"te mando foto del acabado"* → *"¿te marco 10 min?"*. Cada sí facilita el siguiente.
3. `d1_intento` — **La regla del intento de cita.** Nunca descartes sin haber propuesto al
   menos una cita. Si no llegó a cita, no está muerta: está sin trabajar.
4. `d1_diagnostica` — **Diagnostica antes de tirar.** Una pregunta abierta primero: *"¿qué es
   lo que más te hace dudar?"*. El silencio del cliente no es un "no".

## 2. Tira leads calientes (descarta con señal de compra)
*Detección: descartó cotizaciones que el Radar tenía calientes.*

1. `d2_lee` — **Lee la señal.** Que el cliente entre varias veces a tu cotización ES señal de
   compra, no de molestia. Al que vuelve a abrir, no lo descartes: háblale mientras mira.
2. `d2_momento` — **El momento caliente no espera.** El interés se enfría en horas. Cuando
   veas que acaba de abrir, marca hoy, no mañana.
3. `d2_rescate` — **Rescate antes de tirar.** Si lo ibas a descartar y estaba caliente,
   mándale un gancho: *"vi que revisaste la cotización, ¿te ayudo con alguna duda para
   cerrarlo?"*.

## 3. Pierde por precio ("está caro")
*Detección: razón de descarte/no-cierre = precio.*

1. `d3_explora` — **Entiende antes de defender.** "Está caro" casi nunca es el precio real:
   no ve por qué vale eso. No defiendas el número, explora: *"¿caro comparado con qué?"* /
   *"¿qué esperabas invertir?"*. Ahí sale el freno verdadero.
2. `d3_sandwich` — **Sándwich de valor.** No repitas el precio: recuérdale el resultado, mete
   el precio en medio, cierra con otro beneficio. *"Le queda instalado en 3 días con garantía
   de 2 años, son $X, y le incluimos el primer mantenimiento."*
3. `d3_no_contra_ti` — **Nunca negocies contra ti mismo.** No bajes el precio antes de que te
   lo pidan, ni ofrezcas descuento "por si acaso". Si nadie objetó, no hay nada que bajar.
4. `d3_intercambia` — **Intercambia, no regales.** Si mueves el precio, pide algo a cambio:
   *"te ajusto si cerramos hoy"* o *"si te llevas también X"*. Bajar por bajar enseña a pedir más.
5. `d3_valor_primero` — **Valor antes que precio.** Si llega directo al "¿cuánto es lo menos?",
   regresa al valor: qué le resuelves y cuánto le cuesta NO resolverlo, luego la inversión.

## 4. No maneja otras objeciones ("déjame pensarlo", "en el aire")
*Detección: posturas "pidió cambios", "quedó en el aire", "decidiendo".*

1. `d4_laer` — **LAER: escucha, valida, explora, responde.** Ante "déjame pensarlo", valida
   (*"claro, es para pensarse"*) y explora (*"¿qué es lo que quieres terminar de ver?"*). El
   "lo pienso" esconde una duda concreta.
2. `d4_aisla` — **Aísla la objeción real.** *"Aparte de eso, ¿hay algo más que te detenga?"*.
   Si dice que no, ya sabes qué resolver. Si sí, salió la objeción de verdad.
3. `d4_sus_razones` — **Compra por SUS razones, no las tuyas.** No lo convenzas con lo que a
   ti te parece importante; pregúntale qué es lo que a ÉL le importa y véndele eso.
4. `d4_amarra` — **La duda no se resuelve sola.** "En el aire" se enfría. Amárralo: *"¿te
   parece si el jueves, ya con esto resuelto, lo cerramos?"*.

## 5. No amarra la cita
*Detección: ritmo de citas bajo vs su normal.*

1. `d5_cierra_contacto` — **Cierra cada contacto con la siguiente cita.** Nunca cuelgues sin
   fecha: *"¿lo vemos el martes a las 11 o mejor por la tarde?"*.
2. `d5_doble` — **Doble opción, ningún "no".** No preguntes *"¿quieres que nos veamos?"* (invita
   al no). Da a elegir entre dos síes: *"¿te queda hoy o mañana?"*.
3. `d5_temprano` — **La cita se agenda temprano.** Entre más pronto en la conversación la
   amarras, más fácil. Si esperas al final, ya se enfrió.

## 6. No logra contacto (el cliente no le contesta)
*Detección: % alto de "no le contestaron".*

1. `d6_canal` — **Cambia de medio.** Si no contesta la llamada, no insistas igual: WhatsApp;
   si no, un audio corto. Cada cliente tiene su canal.
2. `d6_horario` — **Cambia el horario, no el mensaje.** Si marcaste a las 10, no vuelvas a las
   10:15. Prueba otra franja (mediodía, después de las 6). Muchas veces es mal horario, no
   falta de interés.
3. `d6_velocidad` — **Velocidad al primer contacto.** Un lead nuevo se contacta en minutos.
   Entre más tardas en el primer intento, menos te contesta.

## 7. Llega a cita pero no cierra
*Detección: citas > 0 y cierres = 0.*

1. `d7_diagnostica` — **Diagnostica antes de proponer.** Si tienes citas y no cierras, quizá
   presentas antes de entender. Primero pregunta y escucha; propón cuando ya sabes qué le importa.
2. `d7_senal` — **Reconoce la señal de cierre.** Cuando pregunta por tiempos, garantía o formas
   de pago, ya está listo: no sigas vendiendo, **cierra**.
3. `d7_pide` — **Pide la venta con confianza.** El cliente espera que TÚ des el paso. No te
   quedes en "cualquier cosa me avisa": *"¿lo dejamos apartado?"*.
4. `d7_alternativa` — **Cierre por alternativa.** No preguntes *"¿lo quieres?"*; asume el sí:
   *"¿lo quieres para esta semana o la próxima?"*, *"¿de contado o a plazos?"*.
5. `d7_avanza` — **Toda conversación avanza.** Si no cerraste, no la dejes en el aire: sal con
   el siguiente paso agendado. Una plática sin avance es una venta que se enfría.

## 8. Deja vencer seguimientos
*Detección: vencidas (cronómetro de la Mesa).*

1. `d8_sistema` — **Sistema, no memoria.** Los seguimientos se agendan, no se recuerdan. Lo que
   vence hoy, hoy se toca. La memoria es como se pierden ventas.
2. `d8_servicio` — **Persistencia no es fastidio.** Dar seguimiento es un servicio, siempre que
   aportes algo (un dato, una foto, una respuesta), no solo *"¿ya decidió?"*.
3. `d8_enfria` — **Cada día vencido enfría.** El que te dijo "la próxima semana" y no le
   hablaste, ya se enfrió o compró con otro. El seguimiento a tiempo es la venta.

## 9. Ignora las señales del Radar (calientes sin atender)
*Detección: calientes sin marcar/sin atención.*

1. `d9_prioridad` — **El Radar te dice a quién hablarle HOY.** Un caliente sin atender es una
   venta fácil que dejas pasar. Antes que nada en el día, revisa tus calientes.
2. `d9_marca` — **Marca lo que ves.** El 👍/👎 no es burocracia: te ordena a quién seguir. Un
   caliente sin marcar es un cliente sin dueño.

## 10. No reacciona cuando el cliente se enfría
*Detección: cotización baja de bucket sin acción.*

1. `d10_gancho` — **Resurrección con gancho.** Muerto no es muerto para siempre. Al que dejó de
   abrir, no le mandes *"¿sigue interesado?"* (invita al no): revívelo con algo nuevo — *"me
   llegaron los tiempos de entrega de lo que viste, ¿te los paso?"*.
2. `d10_timing` — **El timing, no el interés.** La mayoría no compra al primer intento por
   *cuándo*, no por *si*. Un frío hoy puede estar listo en dos semanas: reactívalo, no lo tires.

## 11. Cierra regalando descuento
*Detección: ventas con descuento vs sin.*

1. `d11_valor` — **Sube el valor, no bajes el precio.** Cada descuento sin pelear se come el
   margen. Antes de ceder, recuérdale todo lo que se lleva; muchas veces el descuento sobra.
2. `d11_intercambia` — **Si descuentas, intercambia.** El descuento se cambia por algo: cierre
   hoy, contado, compra más grande. Nunca por nada.
3. `d11_urgencia` — **Urgencia real en vez de descuento.** En lugar de bajar precio, da una
   razón verdadera para decidir ya (última pieza, sube el mes que entra) — solo si es cierto.

## 12. Se le seca el embudo (ritmo de citas cayendo)
*Detección: citas bajando.*

1. `d12_revive` — **No vivas solo de leads nuevos.** Si bajaron tus citas, revive tu cartera:
   clientes que quedaron en el aire. Ahí hay citas sin gastar en leads nuevos.
2. `d12_siembra` — **Llena antes de vaciar.** Agenda la siguiente cita antes de cerrar la de
   hoy. El embudo se seca cuando dejas de sembrar.

## 13. Cierra pero no cobra (ventas sin pago)
*Detección: ventas con pagado = 0.*

1. `d13_anticipo` — **El cierre incluye el primer pago.** "Sí, lo quiero" no es venta hasta que
   hay anticipo. Amárralo: *"¿te tomo el anticipo para apartarlo?"*.
2. `d13_ahora` — **No dejes el cobro "para después".** Entre más pasa entre el sí y el pago, más
   se cae. El mejor momento de cobrar es cuando dijo que sí.

## 14. Ticket bajo (no sube el valor de la venta)
*Detección: ticket promedio por debajo.*

1. `d14_necesidad` — **Entiende toda la necesidad.** El ticket sube cuando descubres lo que el
   cliente NO te pidió pero necesita. Pregunta por el proyecto completo, no solo lo que vino a cotizar.
2. `d14_complemento` — **Ofrece el complemento en el momento justo.** Cuando ya dijo que sí:
   *"¿le agregamos X que combina con esto?"*. El cliente comprado compra más.

## 15. Abandona leads nuevos (nunca los trabaja)
*Detección: cotizaciones nunca trabajadas.*

1. `d15_caliente` — **El primer contacto es en caliente.** Un lead recién llegado tiene el
   interés arriba; a las horas ya bajó. Contáctalo el mismo día, aunque sea un mensaje corto.
2. `d15_no_espera` — **Una cotización enviada no es trabajada.** Mandar el PDF y esperar no es
   vender. Detrás de cada cotización nueva va un contacto que abra la conversación.

---

## Ideas de SISTEMA (no son tips — futuras features)
De `pipeline-management` y `time-management`: son de gerente/dashboard, no coaching individual.
Ya los cubre la **Mesa** (prioriza por urgencia/monto) y el **reporte**. Posibles a futuro:
cobertura de pipeline / forecast en el reporte; "tu hora pico de venta" en la Mesa. **No van al catálogo de tips.**

## Pendiente al curar (José)
- Marcar qué técnicas NO aplican a tu giro y reescribir con TUS palabras.
- Confirmar el **orden/currículo** de cada debilidad (cuál se enseña primero → última).
- Agregar debilidades o técnicas que falten.
- Aprobar el mapeo tono ↔ score.

Con el catálogo curado se construye el motor: selección por debilidad #1 + rotación por
`handle` (memoria de mostradas) + personalización con el dato + tono por score + fact-lint.
