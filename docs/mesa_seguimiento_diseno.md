# Mesa de Trabajo — Ciclo de Seguimiento Obligatorio (diseño v1)

> Estado: DISEÑO APROBADO EN LO GENERAL, pendiente de afinar decisiones marcadas 🔶.
> Regla: NO construir hasta que la mesa esté estable y el CEO dé luz verde por fase.
> Principios heredados: cero push/notificaciones (todo vive EN la mesa) · cero valores
> fijos (ancla = mediana del ciclo de venta) · la exigencia no se resetea con un tap.

## 1. El problema (palabras del CEO)

"La mesa muy bien, pero ya que la llené, ¿y ahora qué? ¿Cómo obligamos el seguimiento?"
El examen de cumplidos califica DESPUÉS (retrospectivo, para el dueño); al asesor nadie
le dispara el siguiente toque. La inteligencia existe (tips); falta el RELOJ y la EXIGENCIA.

## 2. Lo que dice la investigación (por qué la obligatoriedad es correcta)

- **El 80% de las ventas requiere 5+ toques de seguimiento; el 44% de los vendedores
  abandona tras el primero y el 92% tras el cuarto.** El asesor promedio abandona por
  diseño humano — el sistema debe forzar la persistencia estructuralmente, no confiar
  en la disciplina. (IRC Sales Solutions, Peak Sales Recruiting, MTD Sales Training)
- **Solo el 2% cierra al primer contacto; el grueso cierra entre el 5º y el 12º.**
  La mesa que "se llena y ahí queda" pierde exactamente donde está el dinero.
- **Cadencia óptima: toques cada 2-3 días al inicio, espaciando después; multicanal
  duplica el engagement.** (Salesloft, Kaspr, Martal) → nuestra cadencia de no-contesta
  (2 días) y el "cambia de canal" que ya dicen los tips son consistentes con la industria.
- **Adrián Bravo (formador ES de ventas/persuasión):** seguimiento sin perseguir ni
  presionar — cada toque aporta valor, preguntas cerradas fáciles de responder. Los tips
  de la mesa ya hablan ese idioma ("una pregunta que se conteste con sí o no"); el
  recordatorio es para el ASESOR, nunca mensajes automáticos al cliente.

## 3. El ancla: la MEDIANA del ciclo de venta (p50)

- Ya existe: `Radar::ciclo_venta()` → `mediana` (días creación→venta, clamp 1-180).
- Se consume **cerrada hacia arriba**: `cadencia_base = max(1, (int)ceil(mediana))`.
- Empresa sin mediana (<3 ventas): fallback = 7 días ✅ (aprobado CEO 15-jul;
  en cuanto hay 3 ventas se vuelve auto).

## 4. La regla de oro anti-reset: el toque vive en CONTACTO

**Un seguimiento se demuestra con una fila nueva del área Contacto** (`hablamos` o
`no_contesta`) posterior al vencimiento:

- Declarar un desenlace nuevo (compromiso/cita) YA inserta contacto implícito → cuenta.
- **Re-tapear la misma pill NO escribe contacto → NO resetea** (la racha del examen ya
  ignora re-taps; el reloj de seguimiento usa la misma filosofía).
- La manita (👍👎📵) es juicio, no toque → tampoco resetea.
- El reloj corre desde el ÚLTIMO toque real (o desde la entrada a la mesa si no hay).

## 5. Cadencias por estado vigente (decisiones del CEO 15-jul)

| Estado vigente | El siguiente toque vence en | Al vencer |
|---|---|---|
| Sin trabajar | HOY (ya existe: "Por trabajar") | fila en Por trabajar (ya) |
| **No contestó** (con o sin 📵) | **cada 2 días** desde el último intento, SIN tregua hasta que conteste (`hablamos`) o se descarte | 🔴 vencida |
| **Nos citamos** | **ceil(mediana)** — FIRME | 🔴 fila roja + **exige actualización de estado**: solo baja cambiando el desenlace (Quedamos / No quiso / Nada) o descartando — un toque simple NO la baja |
| Quedamos en algo | ceil(mediana) | 🔴 vencida (toque la pone al corriente) |
| Decidiendo / Objeción precio / Pidió cambios / En el aire | ceil(mediana) | 🔴 vencida |

RESUELTO (recomendación adoptada por default, CEO delegó): **relojes separados** —
el examen de cumplidos (5 días) mide AL CLIENTE (¿se movió?); el vencimiento de toque
(mediana) mide AL ASESOR (¿lo trabajaste?). No se acoplan. Con el castigo directo del
score (§7) esta tensión desaparece en la práctica: el examen alimenta el reporte del
dueño, el vencimiento alimenta el castigo — canales distintos. Revisar en Fase D.

## 5b. Aclaraciones selladas (CEO 15-jul, 2ª ronda)

1. **Apertura del cliente NO pone al corriente al asesor** — alimenta el otro reloj
   (examen/Radar/categorías) y SUBE la urgencia (vencida + leyendo ahora = tope de la
   mesa). En no-contesta, que el cliente lea sin contestar no detiene la cadencia.
2. **Nunca-trabajadas NO acumulan castigo directo** — ya las castiga la cobertura
   (Por trabajar = falla). El castigo directo es por ABANDONAR lo empezado. Sin doble golpe.
3. **"Nada concreto" y "Propuse, no quiso" también llevan cadencia** (mediana) — el
   no de hoy no es el de mañana; solo descartar/agendar/vender apaga la exigencia.
4. **La cita roja también baja con "Hablamos" nuevo + re-citar** (la pospusieron de
   verdad — exige toque real declarado, visible al dueño como toque).
5. **Recuperación del castigo = decaimiento natural**: cada día vencido es un evento
   con fecha; el castigo suma los de los últimos 15 días. Trabajar detiene la
   acumulación; el tiempo drena los viejos (~2 semanas para limpiar un −5). Sin
   resets manuales.

## 6. Exigencia GRÁFICA (sin push — todo en la mesa)

1. **Chip por fila**: `🔴 vencido hace Nd` / `🟠 vence hoy` / nada (al corriente).
2. **Orden**: dentro de cada grupo (milagros / tier1 / tier2), las vencidas PRIMERO.
3. **Strip**: se agrega `⏰ N vencidas` junto a "por trabajar / en seguimiento".
4. **Cita vencida**: fila roja; el cajón abre directo en "¿Qué pasó con la cita?" con
   los pills del desenlace. No hay botón para posponerla sin declarar.
5. **La marca deja huella**: al ponerse al corriente el chip cambia, pero la fila
   muestra `⏰ estuvo vencida Nd` el resto del período (los días acumulados no se
   borran — ver score). Nada de "toco y quedó limpio".

## 7. Exigencia en el SCORE — castigo DIRECTO estilo boosters (decisión CEO 15-jul)

Espejo de los bonus existentes (bonus_ticket +2/+5/+8 · bonus_cierre +4/+8):
**puntos directos que se RESTAN del score final**, sin fórmula intermedia.
La cobertura (manita+postura, 3 niveles) y todo el Seguimiento quedan INTACTOS —
el castigo vive donde viven los bonus: en el score final.

- **Métrica (no reseteable)**: `dias_vencidos` acumulados por el asesor en el
  período rolling de 15d (suma de todas sus filas vencidas). Tocar DETIENE la
  acumulación; NO la borra — el período la purga solo.
- **Castigo por niveles** (un solo nivel vigente, el mayor — como bonus_cierre):
  | Días vencidos acumulados | Castigo | Visibilidad |
  |---|---|---|
  | 3+  | −2 | silencioso (solo debug panel) |
  | 7+  | −5 | frase en el diagnóstico |
  | 14+ | −8 (tope) | frase fuerte |
- Piso del score: 0. 🔶 Umbrales/valores por afinar con datos reales (Fase D).
- Frase del diagnóstico (fact-lint aplica): "⏰ Traes N días de seguimiento
  vencido — te está costando X puntos del termómetro".
- Persistir en usuario_score: `mesa_dias_vencidos` + `castigo_seguimiento`
  (debug panel del superadmin, como los bonus).

## 8. Hasta cuándo (límites del ciclo — ya existen)

- La exigencia vive del día 1 a 2×p75 (la ventana de la mesa). Trabajadas → Frías a
  p75 (sin exigencia); ignoradas siguen exigiendo. Agendadas: sin exigencia mientras
  parqueadas (la agenda ES el seguimiento pactado). Descartadas/DI: fuera.
- La cadencia de no-contesta muere al contestar, descartar, o salir de ventana.

## 9. Fases de construcción (cuando la mesa esté estable)

| Fase | Qué | Tamaño |
|---|---|---|
| A | Reloj + chips + orden + strip ⏰ (derivado de mesa_estados, sin BD nueva) | ~1 sesión |
| B | Cita vencida exige desenlace + huella "estuvo vencida" + persistencia días | ~1 sesión |
| C | pen_seguimiento al score + debug panel + sims/fact-lint | ~media sesión |
| D | Calibración con 2-4 semanas de datos reales (¿cadencias correctas? ¿tope?) | análisis |

## 10. Validaciones obligatorias al construir

sims mesa (armar/reporte/render) con casos: vencida por cadencia · re-tap no resetea ·
toque real sí · cita vencida exige desenlace · acumulado no se borra · fact-lint 0.

## Fuentes de la investigación

- IRC Sales Solutions — Sales Follow-Up Statistics (80%/5 toques, 44%/92% abandono)
- Peak Sales Recruiting / MTD Sales Training — estadísticas de seguimiento
- Salesloft Big Book of Sales Cadences / Kaspr / Martal — espaciado 2-3 días, multicanal
- Adrián Bravo (adrianbravo.ventas) — seguimiento sin presión, aporte de valor por toque

## 11. Validación del examen de 5 días (15-jul-2026, datos reales)

SQL de reacción post-compromiso/cita corrido en producción: **4 de 4 reacciones
dentro de los 5 días, promedio 1.0 día** — el cliente que quedó en algo se asoma
al día siguiente. El 5 fijo queda VALIDADO direccionalmente (si acaso, generoso).
Muestra chica (n=4): re-correr el mismo SQL en Fase D cuando total ≥ 30; si el
promedio se sostiene en ~1-2 días, evaluar apretar a 3-4. NO anclar a la mediana
(el examen mide reacción humana al compromiso, no ciclo de venta — con mediana
grande perdería sentido). El mismo 5 vive en el Termómetro v5 (validación de
feedback): cualquier cambio va a los dos lugares a la vez.
