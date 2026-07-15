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
- Empresa sin mediana (<3 ventas): 🔶 fallback propuesto = 7 días (documentado como
  arranque; en cuanto hay 3 ventas se vuelve auto).

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

🔶 Tensión a decidir: el examen de cumplidos madura a los 5 días; si la mediana > 5, el
toque "preventivo" del acuerdo vencería DESPUÉS de que el examen ya reprobó. Opciones:
(a) dejarlo así (el examen mide al cliente, el toque mide al asesor — relojes distintos);
(b) para compromiso/cita topar a `min(5, ceil(mediana))`. Recomendación: (a) en v1, medir.

## 6. Exigencia GRÁFICA (sin push — todo en la mesa)

1. **Chip por fila**: `🔴 vencido hace Nd` / `🟠 vence hoy` / nada (al corriente).
2. **Orden**: dentro de cada grupo (milagros / tier1 / tier2), las vencidas PRIMERO.
3. **Strip**: se agrega `⏰ N vencidas` junto a "por trabajar / en seguimiento".
4. **Cita vencida**: fila roja; el cajón abre directo en "¿Qué pasó con la cita?" con
   los pills del desenlace. No hay botón para posponerla sin declarar.
5. **La marca deja huella**: al ponerse al corriente el chip cambia, pero la fila
   muestra `⏰ estuvo vencida Nd` el resto del período (los días acumulados no se
   borran — ver score). Nada de "toco y quedó limpio".

## 7. Exigencia en el SCORE (no reseteable)

- **Métrica**: `dias_vencidos` acumulados por fila en el período rolling (15d del
  termómetro). Tocar DETIENE la acumulación; NO la borra. El período la purga solo.
- **Penalización auto-ajustable** (cero valores fijos):
  `pen_seguimiento = min(dias_vencidos_total / (pedidas × cadencia_base), tope)`
  — "qué fracción del tiempo exigido estuviste vencido".
- 🔶 Dónde pega (recomendación): dentro del bloque s_mesa del Seguimiento:
  `s_mesa_final = s_mesa_cobertura − pen_seguimiento` (piso 0). La regla
  manita+postura queda INTACTA; la puntualidad es una resta encima.
- 🔶 Tope propuesto: 0.5 (la impuntualidad puede costarte hasta la mitad del s_mesa,
  nunca todo — la cobertura sigue siendo lo principal).
- Persistir en usuario_score (`mesa_dias_vencidos`, `pen_seguimiento`) para el debug
  panel del superadmin.

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
