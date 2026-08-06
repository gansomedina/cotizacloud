# Rendimiento de asesores — método de la Mesa

Como gerente de ventas: calificar el rendimiento de cada asesor con la info de
la Mesa, en un vistazo. **Se mide contra las REGLAS DE LA MESA y la ventana de
cierre real de la empresa** — NO contra el promedio de la empresa (lo diluye el
asesor dominante) ni contra la historia del propio asesor. Auto-ajustable a una
SaaS de 1, 2 o N asesores — **cero valores fijos** (regla del CEO).

Muestra **HECHOS, nunca acusa de "trampa"**: el tool muestra el comportamiento;
el gerente diagnostica el porqué.

## Guarda
- Solo empresas con **Mesa activa** (`mesa_activa >= 1`). Sin Mesa no hay datos.
- **SIN gracia para nuevos.** La ventana es corta → un asesor nuevo se llena
  rápido; si se suelta, que se vea.

## La ventana (auto-ajustable, de `Radar::ciclo_venta`)
- `p75` del ciclo de cierre de la empresa → `win = 2·p75` (ventana de medición).
- `mediana` → `rapido_dias = floor(mediana/2)` (descartar antes de esto = muy
  rápido para el ciclo real de ESA empresa).
- Reales (jul 2026): emp12 med5/p75=10, emp13 med2/p75=7, emp14 med3/p75=7.

## Los 5 pilares (cada uno con su mini-semáforo; TODOS sus parámetros, nada resumido a 1)

1. **Conversión** — cerró (ventas con pago) vs cotizaciones trabajadas en la
   Mesa. Valor directo, sin comparación. Alarma solo si trabajó mucho (`CERO_MIN`)
   y cerró 0.
2. **Descartadas** — 3 parámetros: volumen vs cierres (descarta más de lo que
   cierra), **sin cita** (descartó sin llegar a agendar), **muy rápido** (antes
   de `rapido_dias` del ciclo). Rojo si volumen alto **y** (sin-cita o rápido
   dominan ≥60%).
3. **Citas** — su ritmo de agendar (`nos_citamos`): 7 días vs su propio promedio
   semanal (28d/4). Alarma solo si tenía ritmo (base ≥ 2) y cae a la mitad.
4. **Seguimiento** — el **reloj de la Mesa** (el mismo que ve el asesor, vía
   `Mesa::armar` en modo solo-lectura). Una vencida es una vencida, **sin
   histórico ni tendencia**: tiene vencidas → 🔴 ; solo "vence hoy" (esperó al
   último) → 🟠 ; ninguna acercándose → 🟢. Muestra los dos datos: # vencidas y
   # que vencen hoy.
5. **Contacto** — leads que lo buscaron y **no logra contactar** (nunca llegó a
   `hablamos`). Rojo ≥60%, amarillo ≥35%.

## Semáforo del asesor
El **peor** de sus 5 pilares. `flag = "no sigue el proceso"` si algún pilar está
en rojo. `motivo` = veredicto factual del peor pilar (nunca "trampa").

## Archivos
| Archivo | Rol |
|---|---|
| `core/RitmoAsesor.php` | Método: 5 pilares por asesor, solo lectura de la Mesa |
| `modules/dashboard/_ritmo.php` | Tarjeta admin |
| `modules/superadmin/executive.php` | Sección superadmin (todas las empresas) |

## No valida en el contenedor de build
Sin MariaDB local: solo `php -l`. `RitmoAsesor` NO modifica `Mesa::armar/reporte`
(solo las lee) → las sims de la Mesa siguen vigentes. La calibración de umbrales
se hace con datos reales en el deploy.
