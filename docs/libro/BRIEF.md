# Brief — Libro de Ventas (proyecto CotizaCloud)

> Documento puente. Se escribió al final de una sesión de auditoría para que una
> **sesión nueva y dedicada** pueda arrancar el libro con el 100% del contexto,
> sin arrastrar el historial de auditoría/código. Todo lo que el libro necesita
> (el método, los datos, las reglas) vive en el repo + `CLAUDE.md` + este brief.

---

## 1. Qué es este libro (y qué NO es)

- **ES** un libro **comercial de ventas**, con la voz del CEO (José Alfonso Medina), enfocado en el vendedor que **cotiza y manda una liga** — cocinas, granito, closets, inmuebles, seguros, servicios. Ese vendedor no cierra en el mostrador: el trato se decide en el **silencio de los días siguientes**.
- **NO ES** el manual de CotizaCloud. El software **no se enseña**. Idealmente ni aparece salvo en la bio/epílogo.
- El nicho está **vacío**: nadie ha escrito el libro de **ventas por cotización con liga**. Ahí está la oportunidad.

### El ángulo que nadie más puede tomar
El autor tiene lo que ningún gurú de ventas tiene: **datos reales de comportamiento** de miles de cotizaciones (cuándo abren, cuántas veces, scroll, buckets fríos/calientes, dormidas a los 7 días, tasa real de apertura, relación reaperturas→cierre). El libro usa esos datos como **evidencia** para afirmar verdades de ventas que nadie puede afirmar porque nadie más los tiene.

**Estructura de cada capítulo:** un dolor real de venta → la creencia común (lo que todos hacen) → **lo que el dato dice** → la regla accionable → una historia de campo (real, del autor).

---

## 2. Reglas NO negociables (heredadas de `CLAUDE.md`)

1. **CERO datos inventados.** Si un número no sale de la BD real (o de una fuente verificable), **no se afirma como dato**. Se dice "en mi experiencia" o se deja un hueco.
2. **CERO anécdotas falsas.** Las historias son reales (anonimizadas) o van como hueco `[tu historia aquí]` que se llena con el autor.
3. **La voz se calibra, no se inventa** (ver §4). Un libro genérico con su nombre sería fraude de valor.
4. **Honestidad de alcance:** si el contenido denso da 160 páginas buenas y no 200 infladas, se dice "son 160, no relleno".

---

## 3. Decisiones ya tomadas (con el CEO)

- Meta de extensión: **~200 páginas** (≈50-60 mil palabras). Realista solo si es contenido denso; nada de paja.
- Cadencia: **por etapas** — esqueleto → capítulo por capítulo → pasada de voz. No es una sesión.
- Software **invisible**: CotizaCloud aparece, cuando mucho, en bio/epílogo de una página ("estos datos salen del sistema que construí"). Vender el método sin el software da **más** credibilidad; no huele a folleto.
- Doble función implícita: el libro te vuelve **la autoridad del nicho** → alimenta las demos y el plan de adquisición (FB Ads). Pero el libro no lo dice; lo logra siendo bueno.

---

## 4. Lo que falta DECIDIR / PEDIR al autor (primer paso de la sesión nueva)

1. **Lector objetivo:** ¿dueño de negocio o vendedor de piso? Cambia el tono. (Recomendación: se puede escribir para el vendedor y que el dueño lo compre para su equipo.)
2. **Calibración de voz:** pedir 3-5 muestras reales de cómo habla/escribe el autor:
   - Audios de demo transcritos, WhatsApps largos a clientes, cómo explica el Radar en persona.
   - Con eso se fija la voz y se revisa el **Capítulo 1** hasta que diga "así hablo yo". De ahí el resto sale consistente.
3. **Historias reales disponibles** (candidatas, verificar/anonimizar con el autor):
   - El vendedor que pasó de 8.6% a 47% de cierre.
   - La cotización marcada "sin abrir" que el cliente sí había leído (caso Calvario/Rodano).
   - La competencia detectada espiando precios.
   - Los rechazos bancarios de los primeros cobros recurrentes (México).

---

## 5. Paso CERO del proyecto: minería de datos (antes de escribir)

La columna vertebral del libro son **números reales**. Correr contra la BD de producción (agregado, anonimizado) y guardar los resultados como base de las afirmaciones. Ejemplos de lo que hay que medir (afinar las queries en la sesión nueva):

- **Tasa real de apertura** de cotizaciones (cuántas se abren vs cuántas mueren sin abrirse).
- **Reaperturas → cierre**: ¿cuántas veces reabre una cotización que termina cerrando vs una que no?
- **Velocidad de cierre** vs días de silencio (¿a los cuántos días de no abrir se muere una cotización?).
- **Efecto del descuento con ventana** (DI): tasa de cierre con vs sin descuento inteligente, y el efecto del timing/ventana corta.
- **Dormidas**: a los 7 días sin volver a abrir, ¿qué probabilidad de cierre queda?
- **Ticket y márgenes** por giro (cocinas vs granito vs closets…).

> Regla: cada afirmación numérica del libro debe apuntar a una de estas consultas. Si no hay query que lo respalde, no es "dato", es opinión — y se etiqueta como tal.

---

## 6. Esqueleto tentativo (12-14 capítulos, títulos-dolor)

Reescribir con el autor; los títulos son **dolores de venta**, no módulos del software.

**Parte I — La cotización que muere en silencio**
1. El silencio después de enviar (por qué la mayoría de las cotizaciones mueren sin que sepas si las vieron)
2. Dejaste de vender en el mostrador y no te diste cuenta

**Parte II — Leer al cliente sin verlo**
3. Cómo saber si el cliente abrió tu cotización (y por qué "creo que lo está pensando" es mentira)
4. El cliente que reabre 3 veces en 48 horas no está indeciso: está comparando
5. Cómo leer a un cliente que no te contesta

**Parte III — El seguimiento como sistema, no como insistencia**
6. El seguimiento no es insistir: es cadencia
7. Si no te contestó, el reintento va a los 2 días (no a la semana)
8. El arte de cerrar sin perseguir

**Parte IV — El cierre y el precio**
9. El timing del descuento (ventana corta, una sola vez — el descuento eterno educa a esperar)
10. Cómo negociar sin regalar margen

**Parte V — Medirte (y medir a tu equipo)**
11. Vender sin cobrar no es venta: es inventario emocional
12. Cómo saber si un vendedor es bueno de verdad (más allá de "cae bien")

*(2 capítulos de reserva para lo que surja de la minería de datos.)*

---

## 7. Cómo arrancar la sesión nueva (instrucciones para el próximo agente)

1. Abrir sesión **en esta misma carpeta** (repo CotizaCloud) → carga `CLAUDE.md` completo automáticamente (todo el método: Radar, buckets, mesa, termómetro, DI, datos reales).
2. Leer este archivo (`docs/libro/BRIEF.md`).
3. **Primero:** resolver §4 (lector objetivo + pedir muestras de voz). No escribir capítulos hasta fijar la voz.
4. **Segundo:** §5 (minería de datos) — proponer y correr las queries, guardar los números.
5. **Tercero:** cerrar el esqueleto (§6) con el autor.
6. **Luego:** capítulo por capítulo, revisando voz en el Cap. 1 antes de seguir.
7. Mantener siempre las reglas de §2 (cero inventado). Es lo que hace al libro creíble y vendible.

---

## 8. Modelo Fable 5 — nota operativa

El autor prefiere **Fable 5**. Las salvaguardas de Fable pueden marcar contenido y cambiar a otro modelo cuando el texto toca temas de "ciberseguridad/biología". El libro de ventas **no** dispara eso — es contenido comercial/narrativo, así que Fable 5 se mantiene sin problema. (Solo la auditoría de código lo disparaba.)
