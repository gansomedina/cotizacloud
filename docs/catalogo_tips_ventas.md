# Catálogo de Tips de Ventas — CotizaCloud AI

**Qué es:** el banco de conocimiento del que el motor (`core/RitmoTip.php`) saca los
*tips* del termómetro y el "💡 Tip de la semana" del reporte. Cada tip **enseña una
técnica de ventas real** —no resume los números del asesor—, personalizada con su dato
y con el tono según su score.

**Fuente:** metodología probada (`louisblythe/Sales-Skills`) adaptada al vendedor
mexicano de SMB (cocina/mueble/servicio, WhatsApp y cita presencial, ciclo corto).

> **Este doc = espejo del motor.** Cúralo con tus palabras (José): reescribe lo que no
> suene a ti, quita lo que no aplique, agrega técnicas. Lo que edites aquí se pasa al
> motor.

## Anatomía de un tip

```
[MARCO con fuerza + dato]  +  [TÉCNICA real de la skill]
```

- El **marco** carga las stakes y teje el dato (vive en `_debilidad()`): *"Cada cotización
  que trabajas cuesta esfuerzo… el cliente estaba listo y lo mandaste a la basura. Esas
  eran tuyas."*
- La **técnica** enseña el método con nombre y palabras exactas (vive en `catalogo()`),
  y **rota**: cada día/reporte la siguiente de esa debilidad.

## Reglas del motor

1. Detecta la **debilidad #1** del asesor (misma en termómetro y reporte).
2. Elige la técnica que le toca, **rotando** (termómetro: por día · reporte: round-robin
   por memoria en `ritmo_tips` — siempre sale la que lleva más tiempo sin mostrarse, así
   que al agotar la debilidad el ciclo vuelve a empezar en vez de clavarse en la #1).
3. **Personaliza** el marco con su dato real, tomado de los crudos del pilar
   (`n_trabajo`, `n_desc`, `n_sincita`…) para que el tip **nunca** contradiga a la tarjeta.
4. **Tono por score** (los "diferenciales"): <35 → *"Esto es lo que más te está costando
   ahora mismo."* · 35-70 → directo, sin prefijo · >70 → *"Traes buen score, y aun así por
   aquí se te va dinero."* El "va bien" no lleva prefijo.
5. **Fact-lint:** el dato del marco sale de la BD; la técnica es fija. Nunca afirmar del
   asesor algo que el dato no diga, ni evidenciar que trackeamos al cliente.

---

## 1. Descarta sin CALIFICAR  (`califica`)
**Marco:** *"Conseguir cada cotización te costó trabajo. Estás soltando N, y M sin siquiera intentar una cita: ese esfuerzo lo tiras sin pelear la venta."*

1. `d1_calificar` — **Calificación en 3 preguntas.** No descartes a ciegas: situación (*"¿para cuándo lo necesita?"*), problema (*"¿qué quiere resolver?"*), decisión (*"¿es usted quien decide?"*). Comprador real → trabájalo; no → suéltalo sin culpa. Descalificar bien es vender; descartar sin preguntar es tirar ventas.
2. `d1_diagnostica` — **Diagnostica antes de proponer (SPIN).** Antes de mandar precio o descartar: *"¿qué está usando hoy?"*, *"¿qué es lo que más le molesta de eso?"*. Cuando el cliente dice su dolor en voz alta, la venta se arma sola.
3. `d1_escalera` — **Escalera de compromiso (micro-sí).** Pide un "sí" chiquito primero: *"¿le mando foto del acabado?"* → *"¿le sirvió?"* → *"¿lo vemos 10 min?"*.
4. `d1_intento` — **Regla del intento de cita.** Nunca descartes sin proponer al menos una cita.

## 2. Tira leads CALIENTES  (`calientes`)
**Marco:** *"Cada cotización que trabajas cuesta esfuerzo… Descartaste N que ADEMÁS estaban calientes — el cliente estaba listo y lo mandaste a la basura. Esas eran tuyas."*

1. `d2_senal` — **Lee la señal de compra.** Que un cliente vuelva a entrar a su cotización es intención, no molestia. El que regresa te levantó la mano: háblale el mismo día.
2. `d2_yesif` — **Micro-compromiso "sí, si…".** En vez de *"¿nos vemos?"*: *"si le enseño en 10 min cómo le queda y cuánto se ahorra, ¿vale la pena?"*. Sí → se comprometió; no → te dio su objeción real.
3. `d2_urgencia` — **Urgencia real (no inventada).** *"De esto me queda una y hay otro cliente viéndola."* Solo si es cierto — la urgencia falsa quema.
4. `d2_rescate` — **Rescate antes de tirar.** Gancho neutral: *"¿pudo revisar la cotización? ¿le resuelvo alguna duda para cerrarlo?"*.

## 3. Pierde por PRECIO  (`precio`)
**Marco:** *"Bajar el precio a la primera es la salida fácil y la que menos vende. N se te cayeron por precio, varias estando calientes: no era el precio, era que no le mostraste por qué vale."*

1. `d3_laer` — **Marco LAER (Explorar antes de Responder).** Ante *"está caro"* no defiendas el número. Escucha, valida, y explora: *"¿caro comparado con qué?"*, *"¿qué esperaba invertir?"*, *"¿es el precio o lo que ve por ese precio?"*. Con el freno real, responde ESO.
2. `d3_sandwich` — **Sándwich de valor.** Resultado, precio en medio, otro beneficio: *"Le queda instalado en 3 días con garantía de 2 años, son 18 mil, y le incluimos el primer mantenimiento."*
3. `d3_no_contra_ti` — **Nunca negocies contra ti mismo.** No bajes antes de que te lo pidan.
4. `d3_intercambia` — **Intercambia, no regales.** *"Le ajusto si cerramos hoy"* / *"si se lleva también X"*.
5. `d3_comparacion` — **"Competidor más barato": costo total.** *"¿Qué está comparando exactamente?"* — resalta lo que tú incluyes y ellos no.

## 4. No maneja OTRAS OBJECIONES  (`objeciones`)
**Marco:** *"Un “déjame pensarlo” no es un no: es una duda que no destapaste. N de tus descartes quedaron en el aire y ahí se enfriaron, con una objeción que nadie resolvió."*
*(Dispara con ≥2 descartes cuya razón fue “para después” o cuya última postura fue “en el aire”/“decidiendo”.)*

1. `d4_laer` — **LAER ante "déjame pensarlo".** No es un no. Valida y explora: *"¿qué es lo que quiere terminar de ver?"*.
2. `d4_aisla` — **Aísla la objeción real.** *"Aparte de eso, ¿hay algo más que lo detenga?"*.
3. `d4_sus_razones` — **Compra por SUS razones.** Pregúntale qué le importa a ÉL y véndele eso.
4. `d4_amarra` — **Amarra la definición.** *"¿Le parece si el jueves, ya con esto resuelto, lo cerramos?"*.

## 5. No amarra la CITA  (`citas`)
**Marco:** *"Sin cita no hay venta, y tu embudo se está secando: bajó tu ritmo de citas esta semana."*

1. `d5_alternativa` — **Doble opción, ningún "no".** *"¿Le queda hoy o mañana?"*.
2. `d5_cierra_contacto` — **Cierra cada contacto con la siguiente cita.** Nunca cuelgues sin fecha.
3. `d5_revive` — **Revive cartera para llenar el embudo.** Reactiva a los que quedaron en el aire.

## 6. No logra CONTACTO  (`contacto`)
**Marco:** *"Un cliente que no contesta no siempre es un no: muchas veces es que no lo buscaste bien. [dato] — ahí hay ventas esperando a que insistas distinto."*

1. `d6_horario` — **Cambia el horario, no el mensaje.** Otra franja (mediodía, después de las 6). Casi siempre es mal momento, no falta de interés.
2. `d6_canal` — **Cambia de canal.** Llamada → WhatsApp → audio corto.
3. `d6_velocidad` — **Regla de la primera hora.** El lead nuevo se contacta en minutos.

## 7. Llega a cita pero NO CIERRA  (`cierre`)
**Marco:** *"Abrir no es cerrar, y ahí es donde se gana o se pierde. Trabajaste N+ y no cerraste ninguna: el cliente llega hasta la puerta y no lo estás haciendo pasar."*

1. `d7_senal` — **Reconoce la señal de cierre.** Pregunta por tiempos/garantía/pago = listo. Deja de vender y cierra.
2. `d7_asuntivo` — **Cierre asuntivo.** *"Perfecto, ¿lo apartamos hoy?"* / *"¿le empiezo el pedido?"*.
3. `d7_alternativa` — **Cierre por alternativa.** *"¿De contado o a plazos?"*.
4. `d7_resumen` — **Cierre de resumen (lo más caro).** Recapitula el valor y pide el paso.
5. `d7_prueba` — **Cierre de prueba.** *"Si le resuelvo lo del plazo, ¿lo cerramos hoy?"*.

## 8. Deja vencer SEGUIMIENTOS  (`seguimiento`)
**Marco:** *"Cada cliente que dejas vencer se enfría o se va con otro. Traes N seguimientos caídos: eran ventas en tu mano y las estás soltando por no marcar a tiempo."*

1. `d8_sistema` — **Sistema, no memoria.** Lo que vence hoy, hoy se toca.
2. `d8_servicio` — **Aporta valor en cada toque.** Un dato, una foto, una respuesta — nunca solo *"¿ya decidió?"*.
3. `d8_velocidad` — **La velocidad manda.** Al que dejó una duda, contéstale en minutos.

## 9. Ignora el RADAR  (`radar`)
**Marco:** *"El Radar te está diciendo quién quiere comprarte hoy. Tienes N clientes calientes que ni tocaste: son las ventas más fáciles de la semana y las estás dejando pasar."*

1. `d9_prioridad` — **Empieza el día por tus calientes.**
2. `d9_marca` — **Marca lo que ves (👍/👎).** Un caliente sin marcar es un cliente sin dueño.

## 10. No reacciona al ENFRIAMIENTO  (`enfriamiento`)
1. `d10_gancho` — **Resurrección con gancho, no pregunta vacía.** No *"¿sigue interesado?"*; revive con algo nuevo: *"me llegaron los tiempos de entrega, ¿se los paso?"*.
2. `d10_timing` — **Es timing, no falta de interés.** Un frío hoy puede estar listo en dos semanas.

## 11. Cierra regalando DESCUENTO  (`descuento`)
**Marco:** *"Regalar descuento se siente como cerrar, pero te come el margen y te acostumbra a vender por precio. Cerraste N de M con descuento: estás comprando la venta en vez de ganártela."*

1. `d11_valor` — **Sube el valor, no bajes el precio.**
2. `d11_intercambia` — **Si descuentas, intercambia.** Cierre hoy, contado, compra más grande. Nunca por nada.
3. `d11_urgencia` — **Urgencia real en vez de descuento.** Solo si es cierto.

## 12. TICKET bajo  (`ticket`)
**Marco:** *"Estás cerrando, pero chico: tu ticket promedio es $X y el del equipo $Y. Cada venta te cuesta el mismo trabajo — lo que cambia tu quincena es el tamaño."*
*(Dispara si cerró en la ventana y su ticket va por debajo del 70% del promedio del equipo. Con un solo asesor no dispara — se compararía contra sí mismo.)*

1. `d14_necesidad` — **Descubre el proyecto completo.** Pregunta por todo, no solo por lo que vino a cotizar.
2. `d14_complemento` — **Suma en el momento del sí.** *"¿Le agregamos X que combina con esto?"*.

## 13. Va bien  (`bien`)
1. `ok_sube` — **Sube el ticket** preguntando por el proyecto completo.
2. `ok_ritmo` — **No sueltes el ritmo:** agenda la siguiente cita antes de cerrar la de hoy.
3. `ok_radar` — **Exprime el Radar:** el que vuelve a abrir es tu venta más fácil.

---

## Ideas de SISTEMA (no son tips)
De `pipeline-management` y `time-management`: son de gerente/dashboard, no coaching
individual — ya los cubre la **Mesa** (prioriza por urgencia/monto) y el **reporte**.
Futuro posible: cobertura de pipeline / forecast. **No van al catálogo de tips.**

## Pendiente al curar (José)
- Reescribir cada técnica y cada marco con TUS palabras.
- Confirmar el **orden/currículo** de cada debilidad (cuál se enseña primero).
- Agregar debilidades o técnicas que falten.
- Aprobar el mapeo tono ↔ score.

> Al editar aquí, avísame y lo paso al motor (`core/RitmoTip.php`) con su fact-lint.
