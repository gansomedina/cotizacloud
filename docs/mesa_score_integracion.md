# Mesa → Termómetro: integración al Seguimiento (DISEÑO APROBADO, sin implementar)

**Decisión del CEO (11 jul 2026):** la mesa vale el **25% del Seguimiento**, binario
y drástico: cobertura ≥80% = 25% completo; abajo = CERO. Con margen mínimo de 1
falla (equidad para carteras chicas). Se implementa cuando la mesa salga de beta
a asesores — NO antes.

## Fórmula aprobada

```
s_mesa = 1.0  si  fallas <= max(1, floor(0.20 × pedidas))     // "80% con margen de 1"
       = 0.0  en caso contrario
       = 1.0  si  pedidas = 0                                  // sin examen no hay reprobado

s_seguimiento = 0.75 × (tarea×examen − pen_buckets)
              + 0.25 × s_mesa
```
**El multiplicador ❓ (radar_why) YA NO EXISTE** — decisión CEO 11 jul, aplicada
en código ese mismo día: la mesa es el mecanismo de "atiende tus señales".
Cada tap de mesa registra `radar_why_click` a nombre del asesor dueño
(mesa_estado.php, con throttle 5 min) — eso mantiene vivos los consumidores
de `calientes_exploradas` en DiagnosticoTips (diligente, arquetipo teatro,
voluntad, gate "las ignoras") sin insultar al que migró del Radar a la mesa.
```

- **pedidas** = señales 🔥 del período con ventana de reacción cerrada (3+ días),
  por EPISODIO (rebotes suprimidos) — la MISMA métrica "señales desatendidas"
  de `Mesa::reporte()` query 0b, ya simulada y validada.
- **atendidas** = con captura de mesa, calificación 👍👎 del dueño, venta o
  respuesta del cliente dentro de los **3 días** siguientes a la señal
  (decisión CEO 11 jul: 3d en vez de 2d cubre la señal de viernes/sábado
  para equipos de lunes a viernes SIN lógica de días hábiles ni config).

### Días laborales — decisión tomada (11 jul 2026)
NO se implementa configuración de días laborales: la mayoría del termómetro
mide al CLIENTE (que abre en domingo), el costo tocaría cada INTERVAL del
motor, y va contra la filosofía sin-config. La ventana de 3 días + margen
de 1 falla cubren el caso real. CRITERIO DE REAPERTURA: al mes del rollout
del 25%, correr `SELECT DAYOFWEEK(senal)` sobre las desatendidas — si >30%
nacieron viernes/sábado, implementar SOLO "la ventana no corre en fin de
semana" dentro de `Mesa::cobertura_senales()` (sin config de usuario).
- **Gate de empresa**: si `empresas.mesa_activa = 0` → Seguimiento = fórmula
  actual al 100% (el 25% no existe). Flag explícito que el superadmin enciende
  por empresa al soltar la mesa a sus asesores.

Impacto: 25% × 25 pts = 6.25 pts del PROPORCIONAL; el score final los
multiplica por w_proporcional → **~4.7 a 5.6 pts reales** según equipo/CR.

### Divergencia de sets — escenario X/Y documentado (auditoría 11 jul)
La mesa pide el set de 8 buckets; la tarea del 75% mide el set de 5. Casos
reales: (X) asesor cubre 83% de la mesa pero pocas de sus atenciones caen en
el set de 5 → mesa ✓ y tarea baja; (Y) feedback perfecto al set de 5 pero
ignora los 3 buckets extra → tarea 1.0 y mesa en cero (−4.7/−5.6 pts).
Intencional (la mesa redirige trabajo al set completo), PERO obliga a:
- El widget de cobertura del asesor sale de `Mesa::cobertura_senales()`
  (set de 8, episodios, 3d) — el número que ve = el número que lo examina.
- La frase del reprobado nombra "la mesa" y "3 días", nunca "el Radar" ni
  "tu seguimiento" a secas.
- Aviso del rollout: "las señales de la mesa son MÁS que las del Radar".

### tips_score — decisión
Leer los tips de la mesa NO cuenta para dias_lectura (mediría 3 veces el
mismo acto). Criterio de reapertura: al mes del rollout, si dias_lectura
promedio de la piloto cae >40%, evaluar contar "día con cajón abierto".

## Estado actual del motor (verificado en código, no de memoria)

| Elemento | Valor real | Archivo:línea |
|---|---|---|
| Pesos | Act 13 / Eng 17 / **Seg 25** / Health 10 / Conv 35 | ActividadScore.php:796-800 |
| Período | **15 días** rolling, AUTO-EXTENDIBLE a 45-60 si time_to_close > 20d | :25, :159-170 |
| Gracia | `GRACIA_DIAS = 7` (temporal testing — volver a 15) con early return TOTAL | :105, :136-157 |
| Seguimiento hoy | tarea (fb/calientes) × examen (aciertos/fallos ×1/CR), pesos auto `w_examen=max(fb,1)`, − pen_buckets, × multiplicador ❓ (0.70/0.85/1.0) | :509-639 |
| Hot set del Seguimiento | **5 buckets** (probable_cierre, onfire, inminente, validando_precio, prediccion_alta) | :516 |
| Hot set de la Mesa | **8 buckets** (`Mesa::HOT`: + lectura_comprometida, multi_persona, alto_importe) | Mesa.php:16-19 |
| Hot set de Radar Health | **10 buckets** | :762-764 |
| EMA | por dimensión, alpha 0.03–0.25; PERO `$proporcional` usa los s_* CRUDOS — la EMA solo alimenta momentum | :802, :820-846 |
| Persistencia | INSERT usuario_score ~:1001; snapshot mensual :1584 |
| Tips | DiagnosticoTips lee `s_seg` (:124) para frases/arquetipos |

## Las 6 trampas donde nos equivocaríamos (verificadas)

1. **Período equivocado.** La mesa reporta a 30d por default; el score usa
   `$periodo` = 15d **auto-extendible a 45-60** en empresas de ciclo largo.
   La cobertura DEBE calcularse con el `$periodo` del score, no con 30 fijo.
   Si no: en OnTime (ciclo largo) juzgaríamos señales de una ventana distinta
   a la del resto del score.

2. **Duplicar el SQL de cobertura.** Si el score reescribe su propia query de
   señales, el día que ajustemos la del reporte divergen y el asesor ve "0 de 5
   desatendidas" en el reporte pero reprueba el 25% (o al revés) — la clase de
   incoherencia que ya nos costó 3 rondas de auditoría. **Obligatorio**: extraer
   la query 0b a un helper único `Mesa::cobertura_senales($empresa_id,
   $vendedor_id, $dias): ['pedidas','atendidas','fallas']` que consumen AMBOS
   (reporte por empresa, score por vendedor). Una sola fuente de verdad,
   cubierta por la simulación existente.

3. **Gate por auto-detección en vez de flag.** Si el gate fuera "hay taps en la
   empresa", los taps del ADMIN (única UI hoy) encenderían el examen para
   asesores que NI VEN la mesa — reprobación garantizada de todo el equipo.
   Debe ser columna explícita `empresas.mesa_activa` (default 0) que se
   enciende POR EMPRESA al hacer el rollout. Migración:
   `ALTER TABLE empresas ADD COLUMN mesa_activa TINYINT(1) NOT NULL DEFAULT 0;`

4. **Sets de buckets distintos.** "Pedidas" usa `Mesa::HOT` (8) porque es lo
   que la mesa efectivamente le pide al asesor — juzgar con un set distinto al
   de la cola sería reprobar por señales que la mesa nunca mostró. La tarea del
   75% sigue con su set de 5 (sin cambio de comportamiento). Documentado como
   convivencia intencional; NO "unificar" a la ligera: cambiar el set de tarea
   movería scores existentes.

5. **El salto de ±6.25 pts es REAL en el score final.** `$proporcional` usa los
   s_* crudos (la EMA solo suaviza momentum), así que cruzar el corte del 80%
   mueve el score de golpe. Decisión consciente del CEO ("drástico"); el margen
   de 1 falla elimina el caso más injusto (perderlo todo por UNA cotización en
   cartera chica). Además el momentum amplifica el brinco la primera quincena
   (ratio vs ema_composite) — esperado, documentado, no bug.

6. **La gracia de asesores nuevos ya lo cubre — pero OJO con GRACIA_DIAS=7.**
   El early return (:136) sale ANTES de toda dimensión → el binario no toca a
   nuevos. Pendiente previo independiente: regresar `GRACIA_DIAS` a 15 (está
   en 7 "temporal para testing" desde hace semanas).

## Decisiones ya tomadas que este diseño respeta

- Taps del admin cuentan al asesor ("va al asesor") — al rollout, el asesor
  tapea él mismo; el admin puede seguir asistiendo. Columna `declarado_por`
  queda como opción futura, NO bloquea esto.
- Doble efecto de un tap (mejora el 75% vía proyección Y cuenta al 25% de uso)
  = aceptado: el mismo acto es disciplina + juicio; el examen del 75% castiga
  el juicio malo, así que tapear basura no regala score.
- "Sin señales = 25% gratis": aceptado (no existió el examen). Si algún día
  molesta, v2: usar cobertura de cartera (sin_calificar/se_fueron) como examen
  alterno para períodos sin señales.
- "Propuse, no quiso" y "En el aire" NO proyectan 👎 (estados de momento, no
  juicio terminal) — confirmado por el CEO, ya implementado así.

## Cambios exactos al implementar (estimado ~60 líneas + migración)

1. **Migración** `add_mesa_score.sql` — flag de 3 valores (auditoría rollout:
   permite la quincena de UI sin score con una sola columna):
   ```sql
   -- 0 = off · 1 = UI asesores (sin score) · 2 = UI + score 25%
   ALTER TABLE empresas ADD COLUMN mesa_activa TINYINT(1) NOT NULL DEFAULT 0;
   ALTER TABLE usuario_score
     ADD COLUMN mesa_pedidas   INT UNSIGNED NOT NULL DEFAULT 0,
     ADD COLUMN mesa_atendidas INT UNSIGNED NOT NULL DEFAULT 0,
     ADD COLUMN s_mesa         DECIMAL(3,2) NULL;   -- NULL = gate apagado
   ```
2. **core/Mesa.php**: extraer query 0b a `Mesa::cobertura_senales()` público;
   `reporte()` la consume (cero cambio de números — verificar con sim 34/34).
3. **core/ActividadScore.php** (tras :639, antes de Conversión):
   ```php
   $s_mesa = null;
   if (empresa mesa_activa) {
       $cob = Mesa::cobertura_senales($empresa_id, $usuario_id, $periodo);
       $margen = max(1, (int)floor(0.20 * $cob['pedidas']));
       $s_mesa = ($cob['pedidas'] === 0 || $cob['fallas'] <= $margen) ? 1.0 : 0.0;
       $s_seguimiento = 0.75 * $s_seguimiento + 0.25 * $s_mesa;
   }
   ```
   + persistir mesa_pedidas/atendidas/s_mesa en el INSERT (:1001) y snapshot.
4. **DiagnosticoTips**: 3 variantes cuando `s_mesa === 0.0` (rotadas por seed,
   generadas DESPUÉS del corte de max_frases — lección bonus_ticket), gates:
   `mesa_activa && s_mesa===0.0 && pedidas>0`; la v3 exige fallas ≥ 2:
   1. (reto) "Atendiste {X} de {Y} señales 🔥 de tu mesa — {N} se quedaron más
      de 3 días esperando. Ese cuarto de tu Seguimiento está en cero. La mesa
      te las forma sola cada mañana; tu parte es un toque el mismo día: con 8
      de cada 10 lo recuperas completo."
   2. (coach) "La mesa te pidió {Y} señales y llegaste a {X}. Una señal
      caliente dura horas, no semanas: empieza el día por lo 🔥 de tu mesa —
      un tap dentro de los 3 días cuenta, aunque el cliente no conteste.
      Cubriendo el 80% ese punto vuelve solo."
   3. (calle) "{Y} clientes te levantaron la mano y a {N} los dejaste colgados
      más de 3 días. Nadie te pide cerrarlos, te pide aparecer: entra a tu
      mesa, dales un toque y declara qué pasó. Ocho de cada diez y el cuarto
      de tu Seguimiento regresa."
   Ajustes de wording con mesa_activa en $ctx: sordo_a_senales v1 y
   muestra_chica dicen "en tu mesa" en vez de "en el Radar".
5. **Debug panel / executive**: línea "Mesa: X/Y → ✓/✗" en el desglose.
   ⚠️ OBLIGATORIO agregar las columnas nuevas al SELECT de
   `modules/superadmin/executive.php` — ya nos pasó con bonus_ticket:
   columna que no está en el SELECT = warning/valor perdido en el panel.
6. **Simulación**: extender `tools/sim_mesa_reporte.php` con checks del helper
   (pedidas/atendidas por vendedor) + escenarios del margen (3 señales/1 falla
   pasa; 10/3 falla; 0 señales neutral). La mezcla 75/25 es aritmética
   trivial — se cubre con un assert del blend, no requiere simular todo
   ActividadScore.
7. **Toggle superadmin**: switch "Mesa activa" en la ficha de empresa
   (`modules/superadmin/empresa.php`, mismo patrón que toggle_plan) para
   encender `mesa_activa` por empresa sin tocar BD a mano.

## Hallazgos de la auditoría de rollout (11 jul, 3 agentes) — INTEGRAR al implementar

**Bloqueantes técnicos:** (1) partir $MESA_SHARED en $MESA_ASSETS (CSS/JS/toast,
para todos) + shared admin-only — si no, el primer tap del asesor truena con
'mesaTap is not defined'; (2) el gate del reporte debe ser
`$es_admin_dash && isset($_GET['mesa_dias'])` — si no, ?mesa_dias tecleado
destapa el reporte del equipo al asesor; (3) MÓVIL: el CSS ≤640px oculta los
👍👎 y el nombre del cliente se trunca a nada — el asesor vive en el teléfono;
layout de 2 líneas obligatorio antes del rollout. Estimación real total:
~165-185 líneas + migración (no las ~40 originales).

**Producto (psicología de ventas):**
- Contador de cobertura CON DESGLOSE en el strip del asesor: "🔥 Señales: 3 de
  5 — 2 por vencer (F-1043 vence mañana)"; el tap abre la lista itemizada
  (qué cuenta, qué vence, qué venció, la regla en una línea). La percepción
  de justicia depende del desglose, no del margen.
- DISUASIÓN ANUNCIADA (párrafo nuevo del playbook asesor): "La mesa no
  califica lo que declaras — califica que trabajes tus señales. Lo que
  declares se coteja solo con lo que el cliente hace… Declarar lo que de
  verdad pasó siempre te da mejor consejo — y mejor score — que declarar lo
  que se ve bien." + toast del descarte agrega "— y un descarte que el
  cliente desmiente cuenta en tu contra".
- Explicar que las fallas CADUCAN (rolling 15d): "cada quincena empieza
  limpia" — sin esto, el reprobado deja de intentar (efecto acantilado).
- 💰 Recuperado PERSONAL para el asesor ("tú recuperaste $X de cotizaciones
  que dabas por muertas") — el gancho de adopción número uno.
- Espejo: el asesor ve SU fila del reporte (solo la suya), con "Se te
  fueron" reenmarcado a "Sin atención +Nd: 3 · $145,000 — aún vivas,
  tócalas hoy". Lo que genera paranoia no es ver números malos: es saber
  que el jefe ve un reporte que tú no puedes ver.
- Su mesa ARRIBA del dashboard, abierta por default (no enterrada en un
  details).
- Push de revividas (⚡) — sin él, "si revive, vuelve sola" depende de que
  el asesor entre a diario. Expectativa del rollout, no bloqueante.
- Textos admin a ocultar/reescribir en su rama: badge "solo admin", "la mesa
  de cada asesor está en su tarjeta", "Datos de toda la empresa", referencia
  al 📊 Reporte en el strip, párrafo Recuperado del playbook.

## Sub-proyecto previo al rollout: abrir la mesa a ASESORES (falta especificar UI)

El paso 3 del checklist NO es un switch — es un cambio de UI con reglas:
- El asesor ve SOLO SU mesa: `$mesa_uid = Auth::id()` forzado, SIN chips de
  otros vendedores.
- El asesor NO ve: el 📊 Reporte del equipo (admin only), el aviso de
  limpieza en lote, ni datos de otros asesores.
- El asesor SÍ ve: sus filas, tips, taps, playbook, y su propia cobertura
  ("vas 4 de 5 señales esta quincena") — para que el 80% no sea sorpresa.
- Gate actual `$es_admin_dash` en `_mesa.php:13` pasa a:
  admin → vista completa; asesor (si `empresas.mesa_activa`) → vista propia.
- Los endpoints ya están listos para asesores (permiso vendedor-dueño,
  rate-limit, atribución) — solo cambia el render.
- Estimado: ~40 líneas en _mesa.php + gate en dashboard/index.php.

## Checklist del día del rollout (en orden)

1. Regresar `GRACIA_DIAS` de 7 a 15 (pendiente independiente, hacerlo antes).
2. Correr migración `add_mesa_score.sql` en producción.
3. Deploy del código: helper + blend + UI asesor (gate apagado en todas las
   empresas: CERO cambio de scores — verificable en el leaderboard ese día).
4. Abrir la mesa a asesores de la empresa piloto (sub-proyecto UI de arriba).
5. 1 quincena de uso SIN score (datos se acumulan; el reporte ya los muestra;
   el asesor ya ve su cobertura para aprender la regla sin castigo).
6. Encender `mesa_activa=1` a la piloto (toggle superadmin); avisar la regla
   del 80% al equipo ANTES del primer corte.
7. Verificar leaderboard/debug esa semana (el brinco de momentum de la
   primera quincena es esperado); luego rollout por empresa.
