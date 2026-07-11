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

s_seguimiento = 0.75 × (fórmula actual completa: tarea×examen − pen_buckets, × radar_why)
              + 0.25 × s_mesa
```

- **pedidas** = señales 🔥 del período con ventana de reacción cerrada (2+ días),
  por EPISODIO (rebotes suprimidos) — la MISMA métrica "señales desatendidas"
  de `Mesa::reporte()` query 0b, ya simulada y validada.
- **atendidas** = con captura de mesa, calificación 👍👎 del dueño, venta o
  respuesta del cliente dentro de los 2 días siguientes a la señal.
- **Gate de empresa**: si `empresas.mesa_activa = 0` → Seguimiento = fórmula
  actual al 100% (el 25% no existe). Flag explícito que el superadmin enciende
  por empresa al soltar la mesa a sus asesores.

Impacto: 25% × 25 pts de Seguimiento = **6.25 pts del score total** en juego.

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

1. **Migración** `add_mesa_score.sql`:
   ```sql
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
4. **DiagnosticoTips**: 1 frase nueva cuando `s_mesa === 0.0`
   ("Atendiste X de Y señales en la mesa — el 25% de tu Seguimiento está en cero;
   se recupera cubriendo el 80%").
5. **Debug panel / executive**: línea "Mesa: X/Y → ✓/✗" en el desglose.
6. **Simulación**: extender `tools/sim_mesa_reporte.php` con checks del helper
   (pedidas/atendidas por vendedor) + escenarios del margen (3 señales/1 falla
   pasa; 10/3 falla; 0 señales neutral).

## Checklist del día del rollout (en orden)

1. Correr migración `add_mesa_score.sql` en producción.
2. Deploy del código (gate apagado en todas: cero cambio de scores).
3. Abrir la mesa a asesores de la empresa piloto (UI — quitar gate solo-admin).
4. 1 quincena de uso SIN score (los datos se acumulan; el reporte ya los muestra).
5. Encender `mesa_activa=1` a la piloto; avisar al equipo la regla del 80%.
6. Verificar leaderboard/debug esa semana; luego rollout por empresa.
