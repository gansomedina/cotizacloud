# Alarma de Ritmo Semanal por Asesor — IMPLEMENTADO (24 jul 2026)

> Estado: **IMPLEMENTADO.** Es SOLO LECTURA — NO toca ActividadScore ni la
> lógica de la Mesa. Solo lee `mesa_estados` + `cotizaciones`. Riesgo bajo.
>
> **Archivos:**
> - `core/RitmoAsesor.php` — helper: `empresa($id)` y `todas()`.
> - `modules/dashboard/_ritmo.php` — tarjeta admin (gate: es_admin + mesa_activa>=1).
> - `modules/dashboard/index.php` — include tras la mesa.
> - `modules/superadmin/executive.php` — sección "Ritmo de asesores · 7 días".
>
> **Pendiente de validar con datos reales:** calibrar los umbrales del semáforo
> con Abigail/Kevin las primeras semanas (ver más abajo).
>
> **Nota de rendimiento:** `RitmoAsesor::todas()` corre ~4 COUNT por asesor por
> empresa. A escala grande (cientos de empresas) el ejecutivo del superadmin
> haría muchas queries — optimizar con queries agregadas (GROUP BY) cuando la
> base crezca. Hoy (pocas empresas) va bien.

## Problema que resuelve
Abigail y Kevin (Hermosillo) bajan el ritmo; al presionarlos, suben las ventas.
Pero el ciclo es ~28 días: si bajan HOY, la venta cae dentro de un mes. Medir
ventas cerradas es indicador ATRASADO (ves la caída cuando ya perdiste el mes).
Se necesita un indicador ADELANTADO: la **actividad de esta semana** que predice
la venta de dentro de 28 días. El termómetro ya mide esto, pero es complejo de
leer. Esta vista responde UNA pregunta de un vistazo: **"¿quién bajó el ritmo
esta semana?"** — para presionar a tiempo, cuando la presión aún sirve.

## Por qué se puede (dato clave verificado)
`mesa_estados` es **INSERT-ONLY con historia completa** (`usuario_id`,
`created_at`, `area=contacto|compromiso|postura`, `estado`). Los "toques" quedan
con fecha por asesor → el delta **semana-vs-semana es real, no estimado**.
(Nota: el "toque" en CotizaCloud = seguimiento capturado en la mesa, NO mensajes
crudos — no integramos mensajería, por decisión. Correcto así.)

## Decisiones tomadas
- **Ventana:** rolling 7 días (últimos 7 vs los 7 anteriores). Se ve la caída el
  mismo día, sin esperar al lunes. La ventana de 7d cubre fines de semana/días no
  hábiles (no alarma por un sábado sin toques).
- **Alcance:** LAS DOS. (a) admin de cada empresa ve el ritmo de SUS asesores en
  su dashboard; (b) superadmin ve asesores de TODAS las empresas en el ejecutivo.

## Indicadores (todos de datos reales con historia)
1. **Toques de la semana (señal principal).**
   `COUNT(mesa_estados WHERE usuario_id=? AND empresa_id=? AND area='contacto'
   AND created_at >= NOW()-INTERVAL 7 DAY)`. Semana anterior = created_at en
   [NOW()-14d, NOW()-7d). **El delta (esta vs anterior) es la alarma.**
2. **Toques ÷ leads activos (contexto).** Leads activos = cotizaciones "vivas"
   del asesor (estado enviada/vista — NO aceptada/rechazada/suspendida/borrador;
   confirmar enum exacto al construir), por `usuario_id` o `vendedor_id`. Da
   contexto: 0.6 toques/lead se lee distinto con 10 leads que con 60.
3. **Huérfanos.** Leads vivos del asesor cuyo último `mesa_estados area='contacto'`
   es > 7 días (o nunca). Debe estar cerca de 0. Si de 3 salta a 15, se está
   soltando el pipeline.
4. **Cotizaciones nuevas de la semana** (contexto de flujo de entrada).
   `COUNT(cotizaciones WHERE (usuario_id=? OR vendedor_id=?) AND created_at >=
   NOW()-7d)`.

## Semáforo (semana vs semana anterior) — umbrales iniciales, afinar con datos
| Luz | Regla (empezar simple) | Acción |
|---|---|---|
| 🟢 Mantiene | toques ≥ semana pasada (o dentro de -10%) y huérfanos no suben | — |
| 🟡 Bajó | toques caen 10-30% · o huérfanos suben notablemente | vigilar |
| 🔴 Bajó fuerte | toques caen >30% · o huérfanos se disparan | **presiona esta semana** |

Los umbrales exactos (%) se calibran con los datos reales de Abigail/Kevin en las
primeras semanas. Empezar simple; ajustar.

## La vista (glanceable)
Una tarjeta por asesor, **ordenada por quien más bajó primero** (el que necesita
presión, arriba). Ejemplo:
> 🔴 **Abigail** — Toques/lead: **0.6** (↓ de 1.2) · Huérfanos: **15** (↑ de 3) · Nuevas: 4
> "Bajó el ritmo. Habla con ella esta semana — antes de que caiga la venta en 28 días."

## Guardrails (mismas reglas que ya usa el termómetro)
- **Excluir superadmin** de la lista de asesores (no tiene cartera propia).
- **Asesor nuevo** (poca historia) → no alarmar (gracia, como el termómetro).
- **Empresas con `mesa_activa=0`** → sin toques no hay señal; marcar "sin datos",
  no 🔴 falso.
- Solo `area='contacto'` para los toques (compromiso/postura son consecuencia de
  un contacto — no doblar el conteo).
- **Solo lectura + gate por rol.** El admin ve solo su empresa; el superadmin ve
  todas.

## Archivos a tocar (0 migraciones — solo lee tablas existentes)
| Archivo | Cambio |
|---|---|
| `core/RitmoAsesor.php` | NUEVO — helper solo-lectura: dado empresa_id (o todas), regresa por asesor los 4 indicadores + delta + semáforo, ordenado por caída. |
| `modules/dashboard/index.php` | Tarjeta "Ritmo del equipo" para admin (gate: es_admin/es_business + tiene asesores). |
| `modules/superadmin/executive.php` | Sección "Ritmo de asesores (todas las empresas)". |

## Trampas verificadas / a cuidar al construir
- Confirmar el enum exacto de estados "vivos" de `cotizaciones` (enviada/vista) —
  no incluir suspendida/borrador/aceptada/rechazada en "leads activos".
- Un asesor puede estar como `usuario_id` o `vendedor_id` — contar por ambos (el
  quote_action hereda ambos; ver CLAUDE.md).
- Huérfanos es un snapshot actual; el "↑ de 3" compara contra el snapshot de hace
  7 días (recalcular con la fecha de corte anterior), o mostrar solo el actual +
  su ratio vs leads activos si el delta histórico sale caro. Decidir al construir.
- No recalcular nada del score — esta vista NO escribe, solo lee.

## Posible upside comercial (no ahora)
Es un gancho fuerte de la demo Business ("supervisa el ritmo de tu equipo, no solo
sus ventas") — alinea con el Ad 9 (termómetro/equipo). Primero resolver el
problema operativo (leer fácil); el pitch sale solo.
