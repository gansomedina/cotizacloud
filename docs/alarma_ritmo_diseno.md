# Alarma de Ritmo Semanal por Asesor — diseño

## Qué resuelve
Detectar **cuándo un asesor baja el ritmo de seguimiento**, como indicador
ADELANTADO — antes de que caiga la venta. El ciclo de venta es ~28 días: si el
asesor suelta el seguimiento hoy, la venta se pierde en un mes. Medir ventas
cerradas es mirar el resultado atrasado; hay que medir la actividad de HOY que
predice la venta.

Caso real: distinguir a **Abigail** (bajó y empezó a NO dar seguimiento — se le
acumulan las vencidas) de **Kevin** (bajó actividad cruda pero está al corriente,
mesa bien hecha). El error viejo era contar **toques crudos**: penalizaba a Kevin
(trabaja lo que la Mesa le exige con menos toques) — falso positivo.

## Principio
**Cero contador paralelo.** Todo sale de la propia Mesa de Trabajo, que ya mide
el seguimiento por cotización. `RitmoAsesor` solo LEE (`Mesa::armar`,
`mesa_vencidos`, `mesa_estados`, `radar_feedback`) — no escribe, no toca
`ActividadScore`.

## Los 3 ejes (todos de la Mesa)

### 1. 🔴 Vencidas subiendo — "dejó pudrir el seguimiento"
El reloj de la Mesa marca **vencida** una cotización cuando pasa su cadencia de
seguimiento sin retomarla (`Mesa.php` `seguimiento.estado`; rojo = +1 día
vencido). Dos lecturas:
- **Foto de hoy**: `resumen['vencidas']` — cuántas tiene vencidas AHORA.
- **Tendencia**: `mesa_vencidos` (registro diario por asesor) — cotizaciones que
  se le vencieron esta semana vs la anterior. Si sube = **empezó a soltar**.

### 2. 🟡 Por vencer sin tocar — alarma temprana
El rojo (vencida) por diseño ya es tarde. Esta banda abre ANTES: cotizaciones
que **vencen hoy o mañana y no las ha movido** (reloj `estado='hoy'`, o `'ok'`
con `vence == mañana`, y sin `atendida_hoy`). Cacha al que se espera al límite
mientras todavía hay 1-2 días de margen.

### 3. 🗑 Candado de descartes — "solo descarté, no di seguimiento"
La puerta trasera: limpiar la mesa **descartando** en vez de trabajar (descartar
saca la cotización de vencidas/por-vencer). Se mide de la propia Mesa:
- **Descartes de la semana** (postura 'Descartar' o 👎, atribuidos al dueño).
- **Sin seguimiento previo**: de esos, cuántos **nunca tuvieron un "Hablamos"**
  y **no agotaron la escalera** (4 "no contestó"). Ese es el descarte-basura —
  se detecta al instante, sin esperar a que el cliente vuelva.

Respaldo (tardío pero en pesos): **revividos/recuperado** de `Mesa::reporte` —
descartes que el cliente recalentó o que terminaron en venta.

## Semáforo (umbrales PROVISIONALES — calibrar con datos reales)
- 🔴 **rojo**: `sin_trabajo ≥ 3` · o `vencidas nuevas ≥ 3 subiendo` · o `vencidas ≥ 6`
- 🟡 **amarillo**: `por_vencer ≥ 3` · o vencidas subiendo (leve) · o `sin_trabajo ≥ 1` · o `vencidas ≥ 3`
- 🟢 **verde**: al corriente

Constantes en `RitmoAsesor` (`ROJO_VENC_FOTO`, `AMBAR_PORVENCER`, `ROJO_DUMP`…) —
se ajustan sin tocar la lógica.

## Marcador del mes (en la barra de la Mesa, `_mesa.php`)
Segmento `📅 Mes: N cierres · N descartadas` (mes calendario, por asesor). El
scoreboard honesto: si descartadas ≫ cierres, no hay vuelta de hoja. Los cierres
no se inflan (venta con `pagado > 0`). Visible para el admin (por asesor) y para
el asesor (su propia barra).

## Archivos
| Archivo | Rol |
|---|---|
| `core/RitmoAsesor.php` | Lógica: 3 ejes por asesor, solo lectura de la Mesa |
| `modules/dashboard/_ritmo.php` | Tarjeta admin en el dashboard |
| `modules/superadmin/executive.php` | Sección ritmo del superadmin (todas las empresas) |
| `modules/dashboard/_mesa.php` | Marcador del mes en la barra de la Mesa |

## No valida en el contenedor de build
Las sims de la Mesa piden MariaDB local (BD `simtest`), que NO existe en el
contenedor. `RitmoAsesor` NO modifica `Mesa::armar/reporte/tips` (solo las lee),
así que las sims de la Mesa siguen vigentes. La validación con datos reales
(calibrar umbrales, confirmar Abigail 🔴 / Kevin 🟢) se hace en el deploy.
