# Catálogo de Tips de Ventas — CotizaCloud AI

**Qué es:** el banco de conocimiento del que el motor saca los *tips* del termómetro
(y enriquece el Consejo del reporte). Cada tip **enseña una técnica de ventas real**
—no resume los números del asesor—, personalizada con su dato del día y con el tono
según su score.

**Fuente:** técnicas adaptadas de metodología probada (LAER, escalera de compromiso,
value-before-price, resurrección, HEAR, etc. — repo `louisblythe/Sales-Skills`),
bajadas al contexto de un vendedor mexicano de SMB (cocina/mueble/servicio, WhatsApp
y cita presencial, ciclo corto).

> **Estado: BORRADOR v1 para curar.** José (CEO/vendedor) revisa: ¿suena a lo que un
> buen gerente le diría a su vendedor, o todavía a manual gringo? Quita, corrige, agrega.

## Cómo lo usa el motor (reglas)

1. **Detecta la debilidad #1** del asesor (ya lo hace RitmoAsesor/score).
2. **Elige la técnica que le toca** de esa debilidad, **rotando** (recuerda cuáles ya
   le mostró para no repetir). Cuando la debilidad mejora, pasa a la siguiente.
3. **Personaliza con su dato real** como gancho (folio, cliente, monto, vistas).
4. **Ajusta el tono por score** (los "diferenciales"):
   - Score bajo (reprobado) → firme y directo. *"Esto te está costando ventas."*
   - Score medio → coach que corrige. *"Cuida esto, mira cómo."*
   - Score alto → refina y reconoce. *"Vas bien; para el siguiente nivel…"*
5. **Fact-lint:** el gancho (números) sale de la BD; la técnica es conocimiento fijo.
   Nunca afirmar algo del asesor que el dato no diga.

**Anatomía de un tip:** `[gancho con su dato] + [técnica con nombre] + [cómo, con palabras exactas]`

---

## 1. Descarta sin trabajar (tira leads sin darles proceso)
*Detección: descartes sin cita / muy rápido.*

- **Escalera de pequeños sí.** Descarta porque salta directo al cierre y el cliente aún
  no está listo: es un salto muy grande. No pidas la venta de golpe; sube por escalones:
  *"¿te resolvió la duda del material?"* → *"te mando foto del acabado"* → *"¿te marco 10
  min para verlo?"*. Cada sí pequeño hace fácil el siguiente.
- **Diagnostica antes de tirar.** Antes de descartar, una pregunta abierta: *"¿qué es lo
  que más te está haciendo dudar?"*. La respuesta casi siempre es trabajable; el silencio
  del cliente no es un "no".
- **La regla del intento de cita.** Nunca descartes una cotización sin haber propuesto al
  menos una cita. Si no llegó a cita, no está muerta: está sin trabajar.

## 2. Tira leads calientes (descarta con señal de compra)
*Detección: descartó cotizaciones que el Radar tenía calientes.*

- **Lee la señal.** Que el cliente entre varias veces a tu cotización ES una señal de
  compra, no de molestia. Al que vuelve a abrir, no lo descartes: háblale hoy, mientras
  está mirando.
- **El momento caliente no espera.** El interés se enfría en horas, no en días. Cuando veas
  que un cliente acaba de abrir, ese es el momento de marcarle, no mañana.
- **Rescate del caliente.** Si ya lo ibas a descartar y estaba caliente, mándale un gancho
  concreto antes de tirarlo: *"vi que revisaste la cotización, ¿te ayudo con alguna duda
  para cerrarlo?"*.

## 3. Pierde por precio ("está caro")
*Detección: razón de descarte/no-cierre = precio.*

- **Entiende antes de defender.** "Está caro" casi nunca es el precio real; es que no ve por
  qué vale eso. No defiendas el número, explora: *"¿caro comparado con qué?"* / *"¿qué
  esperabas invertir?"*. Ahí sale el freno verdadero.
- **Sándwich de valor.** Cuando te objeten precio, no repitas el número: recuérdale el
  resultado, mete el precio en medio, cierra con otro beneficio. *"Le queda instalado en 3
  días con garantía de 2 años, son $X, y le incluimos el primer mantenimiento."*
- **Intercambia, no regales.** Si vas a mover el precio, pide algo a cambio: *"te puedo
  ajustar si cerramos hoy"* o *"si te llevas también X"*. Bajar por bajar enseña al cliente
  a pedir más.
- **Valor antes que precio.** Si el cliente llega directo al "¿cuánto es lo menos?", regresa
  al valor primero: confirma qué problema le resuelves y cuánto le cuesta NO resolverlo,
  luego hablas de inversión.

## 4. No maneja otras objeciones ("déjame pensarlo", "en el aire")
*Detección: posturas "pidió cambios", "quedó en el aire", "decidiendo".*

- **LAER: escucha, valida, explora, responde.** Ante un "déjame pensarlo", no lo dejes ir:
  valida (*"claro, es una buena decisión para pensarse"*) y explora (*"¿qué es lo que
  quieres terminar de ver?"*). El "lo pienso" casi siempre esconde una duda concreta.
- **Aísla la objeción real.** *"Aparte de eso, ¿hay algo más que te detenga?"*. Si dice que
  no, ya sabes qué resolver. Si dice que sí, salió la objeción de verdad.
- **La duda no se resuelve sola.** "Quedó en el aire" con el tiempo se enfría. Amárralo:
  *"¿te parece si el jueves ya con esto resuelto lo cerramos?"*.

## 5. No amarra la cita
*Detección: ritmo de citas bajo vs su normal.*

- **Cierra cada contacto con la siguiente cita.** Nunca cuelgues sin fecha. Antes de
  terminar: *"perfecto, ¿lo vemos el martes a las 11 o mejor por la tarde?"*.
- **Doble opción, ningún "no".** No preguntes *"¿quieres que nos veamos?"* (invita al no).
  Da a elegir entre dos síes: *"¿te queda hoy o mañana?"*.
- **La cita se agenda temprano.** Entre más pronto en la conversación amarras la cita, más
  fácil es. Si esperas al final, ya se enfrió.

## 6. No logra contacto (el cliente no le contesta)
*Detección: % alto de "no le contestaron".*

- **Cambia de medio.** Si no contesta la llamada, no insistas por lo mismo: manda WhatsApp;
  si no el WhatsApp, un audio corto. Cada cliente tiene su canal.
- **Cambia el horario, no el mensaje.** Si marcaste a las 10 y no contestó, no vuelvas a las
  10:15. Prueba otra franja (mediodía, después de las 6). El "no contesta" muchas veces es
  mal horario, no falta de interés.
- **Velocidad al primer contacto.** Un lead nuevo se contacta en minutos, no en horas. Entre
  más tardas en el primer intento, menos te contesta.

## 7. Llega a cita pero no cierra
*Detección: citas > 0 y cierres = 0.*

- **Diagnostica antes de proponer.** Si cierras poco pese a tener citas, quizá presentas
  antes de entender. En la cita, primero pregunta y escucha; propón solo cuando ya sabes
  qué le importa.
- **Reconoce la señal de cierre.** Cuando el cliente pregunta por tiempos, garantía o formas
  de pago, ya está listo: no sigas vendiendo, **cierra**. *"¿Lo dejamos apartado?"*.
- **Cierre por alternativa.** No preguntes *"¿lo quieres?"*; asume el sí y ofrece opciones:
  *"¿lo quieres para esta semana o la próxima?"*, *"¿pago de contado o a plazos?"*.

## 8. Deja vencer seguimientos
*Detección: vencidas (cronómetro de la Mesa).*

- **Sistema, no memoria.** Los seguimientos no se recuerdan, se agendan. Usa la Mesa: lo que
  vence hoy, hoy se toca. Confiar en la memoria es cómo se pierden ventas.
- **Persistencia no es fastidio.** Dar seguimiento es un servicio, no molestar — siempre que
  aportes algo (un dato, una foto, una respuesta), no solo *"¿ya decidió?"*.
- **Cada día vencido enfría.** El cliente que te dijo "la próxima semana" y no le hablaste, a
  la próxima semana ya se enfrió o compró con otro. El seguimiento a tiempo es la venta.

## 9. Ignora las señales del Radar (calientes sin atender)
*Detección: calientes sin marcar/sin atención.*

- **El Radar te dice a quién hablarle HOY.** Un caliente sin atender es una venta fácil que
  estás dejando pasar. Antes que nada en el día, revisa tus calientes.
- **Marca lo que ves.** El 👍/👎 no es burocracia: le enseña al sistema y te ordena a quién
  seguir. Un caliente sin marcar es un cliente sin dueño.

## 10. No reacciona cuando el cliente se enfría
*Detección: cotización baja de bucket y no hay acción.*

- **Resurrección con gancho.** Muerto no es muerto para siempre. Al que dejó de abrir, no le
  mandes *"¿sigue interesado?"* (invita al no): revívelo con algo nuevo — *"me llegaron los
  tiempos de entrega de lo que viste, ¿te los paso?"*.
- **El timing, no el interés.** La mayoría no compra al primer intento por *cuándo*, no por
  *si*. Un cliente frío hoy puede estar listo en dos semanas: no lo tires, reactívalo.

## 11. Cierra regalando descuento
*Detección: ventas con descuento vs sin.*

- **Sube el valor, no bajes el precio.** Cada descuento que das sin pelear se te come el
  margen. Antes de ceder, recuérdale todo lo que se lleva; muchas veces el descuento sobra.
- **Si descuentas, intercambia.** El descuento se cambia por algo: cierre hoy, pago de
  contado, una compra más grande. Nunca por nada.
- **Urgencia real en vez de descuento.** En lugar de bajar el precio, usa una razón verdadera
  para decidir ya (última pieza, precio sube el mes que entra) — pero solo si es cierto.

## 12. Se le seca el embudo (ritmo de citas cayendo)
*Detección: citas bajando.*

- **No vivas solo de leads nuevos.** Si esta semana bajaron tus citas, revive tu cartera:
  clientes de hace semanas que quedaron en el aire. Ahí hay citas sin gastar en leads nuevos.
- **Llena antes de vaciar.** Un buen vendedor agenda la siguiente cita antes de cerrar la de
  hoy. El embudo se seca cuando dejas de sembrar.

## 13. Cierra pero no cobra (ventas sin pago)
*Detección: ventas con pagado = 0.*

- **El cierre incluye el primer pago.** "Sí, lo quiero" no es venta hasta que hay anticipo.
  Amárralo en el momento: *"¿te tomo el anticipo para apartarlo?"*.
- **No dejes el cobro "para después".** Entre más pasa entre el sí y el pago, más se cae la
  venta. El mejor momento de cobrar es cuando dijo que sí.

## 14. Ticket bajo (no sube el valor de la venta)
*Detección: ticket promedio por debajo.*

- **Entiende toda la necesidad.** El ticket sube cuando descubres lo que el cliente NO te
  pidió pero necesita. Pregunta por el proyecto completo, no solo por lo que vino a cotizar.
- **Ofrece el complemento en el momento justo.** Cuando ya dijo que sí, es el mejor momento
  para sumar: *"¿le agregamos X que combina con esto?"*. El cliente comprado compra más.

## 15. Abandona leads nuevos (nunca los trabaja)
*Detección: cotizaciones nunca trabajadas.*

- **El primer contacto es en caliente.** Un lead recién llegado tiene el interés arriba; a
  las horas ya bajó. Contáctalo el mismo día, aunque sea un mensaje corto.
- **Una cotización enviada no es una cotización trabajada.** Mandar el PDF y esperar no es
  vender. Detrás de cada cotización nueva va un contacto que abra la conversación.

---

## Pendiente al curar (José)
- Marcar qué técnicas NO aplican a tu giro y cuáles reescribir con TUS palabras.
- Agregar debilidades o técnicas que falten (tu experiencia manda).
- Definir el orden/rotación por debilidad (cuál se enseña primero).
- Aprobar el mapeo tono ↔ score.

Con el catálogo curado, se construye el motor: selección por debilidad #1 + rotación +
personalización con el dato + tono por score + fact-lint. Vive en el termómetro y
enriquece el Consejo del reporte.
