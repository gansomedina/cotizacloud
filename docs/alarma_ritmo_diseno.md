# Rendimiento de asesores — método de la Mesa

Como gerente de ventas: calificar el rendimiento de cada asesor con la info de
la Mesa, en un vistazo. TODO auto-ajustable a la empresa / al propio asesor —
**cero valores fijos** (regla del CEO, igual que el termómetro v5).

## Guarda
- Solo empresas con **Mesa activa** (`mesa_activa >= 1`). Sin Mesa no hay datos.
- **SIN gracia para nuevos.** La ventana es corta (7 días para el ritmo) → un
  asesor nuevo se llena rápido; si se suelta, que se vea.

## Los 4 pilares

### VEREDICTO — cómo va (el semáforo + nota /100)
1. **Cierra** — sus ventas vs el **histórico de cierre de SU empresa**
   (`_close_hist`: ventas/cotizaciones de la empresa ANTES de la ventana). Así un
   asesor solo no se compara consigo mismo. `cierra = (his_close/bench)×50`.
2. **Limpio** — descartes vs ventas (gaming), vs la **norma de descarte de la
   empresa**. `gaming` = descarta por encima de la norma **Y** cierra por debajo
   del promedio **Y** ≥3 descartes. El que descarta mucho pero cierra bien (Kevin)
   NO es gaming. `score` final = `0.6·Cierra + 0.4·Limpio`; gaming lo aplasta a ≤30.

### RITMO — quién se está soltando (leading, presiona YA)
3. **⏰ Vencidas subiendo** — límite de seguimiento de la Mesa. Normalmente bajo
   (la Mesa presiona y responden). La alarma es cuando **sube vs su propio nivel**
   (`mesa_vencidos`: últimos 7d vs promedio de las 3 semanas previas). No es un
   número de "qué tan malo" — es el radar de quién dejó de mantenerse al día.
4. **📅 Citas bajando** — ritmo de agendar (Cita→Venta). Cae vs **su propio
   promedio semanal** (`mesa_estados` nos_citamos: 7d vs 28d/4). Solo alarma si
   tenía ritmo de citas (base ≥ 1).

## Semáforo (relativo, no cortes absolutos)
- 🔴 **rojo**: gaming, o score muy por debajo del promedio (`< SCORE_ROJO`).
- 🟡 **amarillo**: cierra por debajo del promedio de la empresa, o una alerta de
  ritmo prendida (⏰/📅), o score bajo el promedio-alto.
- 🟢 **verde**: cierra al/arriba del promedio, sin alertas, sin gaming.

Los umbrales (`CIERRA_BAJO=50`, `SCORE_ROJO`, `SCORE_VERDE`) están **relativos al
promedio** (50 = promedio de su empresa, por construcción de los ratios). Son
constantes calibrables — NO benchmarks fijos.

## Datos reales que fundamentaron el diseño (30 días, 3 sucursales OnTime)
- **Abigail** (Hmo): 157 activas, 12 ventas, 12 descartes → cierra bien, limpio.
- **Manuel Estrada** (Hmo): NUEVO (2 sem), 1 venta, 15 descartes → sin gracia,
  cae en gaming/bajo — es lo que el CEO quiere ver.
- **Manuel Limón** (Obr): **Mesa apagada** → fuera de la alarma.
- **Kevin** (Nog): 105 activas, 9 ventas, 27 descartes → descarta mucho PERO
  cierra → NO es gaming. El método debe distinguirlo (por eso gaming exige
  también cierre bajo).
- Aprendizaje clave: la **vencidas casi no prende** porque la Mesa YA funciona
  como disuasor; su valor es detectar cuándo SUBE.

## Archivos
| Archivo | Rol |
|---|---|
| `core/RitmoAsesor.php` | Método: 4 pilares por asesor, solo lectura de la Mesa |
| `modules/dashboard/_ritmo.php` | Tarjeta admin |
| `modules/superadmin/executive.php` | Sección superadmin (todas las empresas) |

## No valida en el contenedor de build
Sin MariaDB local: solo `php -l`. `RitmoAsesor` NO modifica `Mesa::armar/reporte`
(solo las lee) → las sims de la Mesa siguen vigentes. La calibración de umbrales
(SCORE_ROJO/VERDE, el ratio de gaming, los pisos del ritmo) se hace con datos
reales en el deploy.
