# Mejorías tangibles — Radar + Termómetro (6 julio 2026)

Investigación de MEJORÍAS (no bugs) al corazón del negocio: el Radar (detección de
intención de compra) y el Termómetro (score que impulsa el uso del Radar). Dos agentes
Fable 5 leyeron el código línea por línea. Cada mejoría trae su punto de inserción real.

El hilo conductor: hoy ambos sistemas **describen y castigan**; estas mejorías los
convierten en sistemas que **dirigen y celebran**, con el Radar como destino de casi
toda acción sugerida — que es el negocio.

---

# PARTE A — MOTOR DEL RADAR (detección de intención)

## A1. Explotar `accept_open` abandonado — "estuvo a punto de aceptar" (la #1)
El JS ya manda `accept_open`/`reject_open` cuando el cliente abre el diálogo de aceptar/rechazar
(`cotizacion.php:1409`), `track.php` los acepta (`track.php:37-38`) y se guardan en `quote_events`.
Pero `Radar::_agregar_eventos()` (`Radar.php:1260-1266`) cuenta solo 7 tipos y los ignora.
**Un `accept_open` sin `accept_confirm` = el cliente abrió Aceptar, leyó la confirmación y se
arrepintió: la señal de intención más fuerte del sistema, hoy ignorada al 100%.**
- Contar `accept_opens/accept_confirms/reject_opens` en `Radar.php:1266`.
- `Radar.php:748-757`: `if ($accept_aband) $pss += 3.5` (más que el loop de precio 3.0 — es intención declarada).
- Señal en `_senales` (`Radar.php:1413`): `casi_acepta` + frase en `explicar_bucket` "Abrió Aceptar y no confirmó — llámalo".
- Opcional: push inmediato en `track.php:221` case `accept_open`.
- **Falsos positivos: casi nulos** (requiere click humano). **Esfuerzo: BAJO.** Ningún competidor PyME tiene "casi aceptó".

## A2. "Está viéndola AHORA" — badge en vivo + push de primera apertura
El push solo se dispara cuando el bucket CAMBIA (`Radar.php:1518`). El momento de mayor probabilidad
de contacto —el cliente la tiene abierta en pantalla ahora— no genera señal. `quote_sessions.updated_at`
ya se refresca en cada heartbeat (`cotizacion.php:325`).
- Push primera apertura en `cotizacion.php:306` (tras `estado='vista'`), gateado por `notif_config`.
- Badge "En línea ahora" (verde pulsante) en `radar/index.php:334` con `live_at > NOW()-3min`.
- **Esfuerzo: BAJO-MEDIO.** Convierte el Radar de reporte histórico a herramienta de venta en vivo.

## A3. Dwell time real en la sección de precio (`totals_ms`)
Los eventos de precio son binarios; `validando_precio` (`Radar.php:881`) usa visibilidad de TODA la
página como proxy. Con el `inView()` que ya corre (`cotizacion.php:1700`) es capturable barato.
- `setInterval` 1s que suma cuando el total está en viewport y visible → `totals_ms` en el payload.
- Columna `totals_ms` en `quote_events`; en `Radar.php:883` sustituir el proxy: `>= 8000ms mirando el total = validar precio`.
- **Esfuerzo: MEDIO.** "Cuánto tiempo miró tu precio" separa curioso de comprador y reduce falsos positivos.

## A4. Señales con ventana temporal + botón WhatsApp en el Radar
`_senales()` (`Radar.php:1406`) recibe booleans sin tiempo: "revisó precio" puede ser de hace 5 días.
`quote_events` tienen `ts_unix`. Contar por ventana → **"Abrió la sección de precio 3 veces en la
última hora — llámalo ya"**. Y `radar/index.php:82` ya carga `cl.telefono` **y nunca lo renderiza**:
agregar botón `wa.me/{tel}?text=` con mensaje según bucket junto a "Editar" (`index.php:419`).
- **Esfuerzo: BAJO.** Convierte cada fila del Radar en "detectar → actuar" en un tap. El cierre PyME es por WhatsApp.

## A5. Guardar el referer y clasificar el canal de re-apertura
`cotizacion.php:153` captura `HTTP_REFERER` **y nunca lo guarda**. Se pierde una dimensión:
referer vacío en sesión N≥2 desde desktop = el cliente guardó/escribió el link = **regreso deliberado**
(señal más fuerte que un re-click en el chat); `l.facebook.com` = llegó por retargeting (cierra el loop con Marketing).
- Columna `referer_host` + clasificador de 10 líneas en el INSERT (`cotizacion.php:292`).
- **Esfuerzo: BAJO.** El regreso deliberado detecta al comprador serio temprano; hace tangible el ROI del módulo Marketing.

## A6. Momentum "heating" — detectar aceleración, no solo enfriamiento
`momentum()` (`Radar.php:1362`) es binario stable/cooling. Los intervalos entre visitas que se ACORTAN
(vio lunes, jueves, hoy dos veces) = patrón clásico de compra, no detectado. `$session_ts[]` ya existe.
- Con ≥3 sesiones, si el gap se redujo a la mitad → `momentum='heating'` + priority +2. Flecha ↑ verde.
- **Esfuerzo: BAJO.** Detección más temprana que los umbrales absolutos de `onfire`.

## A7. Afinar el FIT (corazón del modelo, 2 toscos con fundamento)
**a)** `bk_gap` (`Radar.php:717`) hace `floor(Δ/86400)` → el que regresó a las 5h da gap=0 → bucket `'sin'`
= rate 0.08 "vio una sola vez". El regresador intradía (lo ve en el trabajo, lo reabre en casa — típico
en México) recibe el multiplicador MÁS BAJO. Fix: bucket `'mismo_dia'` cuando sessions≥2 y gap=0.
**b)** `calibrar()` (`Radar.php:1882`) entrena con `DATEDIFF(MAX,MIN)` = span total y `COUNT(qs.id)` crudo
**sin filtrar es_interno ni deduplicar**, mientras `score()` predice con gap penúltima→última, dedup y sin internos.
El modelo aprende de una distribución y predice sobre otra. Fix: alinear la query de calibración con la de inferencia.
- **Esfuerzo: MEDIO.** El FIT calibrado por empresa es el diferenciador técnico; entrenarlo con las features que predice hace que "FIT 14%" sea creíble.

## A8. Matar el ghost de scroll-restore de raíz (ya investigado 28 mayo, sigue pendiente en el JS)
El filtro `scroll<35 && vis<200` (`Radar.php:482`) es parche al síntoma. La solución de causa raíz no está en el JS:
- `cotizacion.php:1806`: `if ('scrollRestoration' in history) history.scrollRestoration = 'manual';`
- `cotizacion.php:1766`: gatear el scroll con `navigator.userActivation?.hasBeenActive`.
- **Esfuerzo: BAJO.** Menos sesiones fantasma → buckets más limpios → menos pushes falsos → más confianza.

## A9. Micro-señales con líneas de JS: print, copy, zoom
- `print`: ya whitelisted (`track.php:38`) pero **nunca se manda** — los botones hacen `window.print()` sin track.
  `beforeprint` → `czTrack('print')`. Imprimir = "lo lleva al jefe/esposa" = precursor de multi_persona.
- `copy` del total → "comparando/consultando con alguien". `zoom` móvil sostenido → lectura de detalle.
- **Esfuerzo: BAJO cada una.** Amplían la base de señales del pss sin tocar arquitectura.

## A10. Re-notificar re-actividad dentro del mismo bucket caliente
El push exige `bucket !== old_bucket` (`Radar.php:1522`). Una cotización 4 días en `probable_cierre`
donde el cliente hoy regresó con sesión intensa NO notifica. Segunda condición de push: bucket caliente +
última sesión hoy + la previa fue hace >24h. El dedupe de 24h ya evita spam.
- **Esfuerzo: BAJO.** El regreso tras silencio es el momento de re-contacto.

## A11. Multi-persona vs multi-dispositivo (proteger la credibilidad de la señal estrella)
El union-find (`Radar.php:614`) solo fusiona vids con mismo device_sig + IP. La persona con iPhone + laptop
(dsigs distintos) cuenta como 2 → `multi_persona` dice "Varias personas la evalúan" → el vendedor llama,
descubre que era el mismo señor, y quema la confianza. Fix: si 2 vids, 1 sola IP, familias de device distintas
(móvil vs desktop) y sesiones no traslapadas → degradar la frase a "La revisó desde iPhone y PC — probablemente la misma persona".
- **Esfuerzo: MEDIO.** Proteger multi_persona de la sobre-afirmación protege la credibilidad del producto.

---

# PARTE B — TERMÓMETRO (score que impulsa el uso del Radar)

## B1. "Te faltan X puntos para [nivel]" + las 2 jugadas concretas que los dan
Hoy el vendedor ve un 54 ámbar y 5 barras y nada le dice qué mover mañana. Umbrales de nivel existen
(`ActividadScore.php:985`: 86/61/31) pero nunca se muestran como meta.
- Bajo el gauge: *"A 7 puntos de Buen ritmo"* + las 2 acciones con mayor `headroom = (1-s_dim)×w_dim`,
  mapeadas a la acción diagnosticable con deep-link (`/radar`, `/cotizaciones/:id`).
- Inserción: `dashboard/index.php:933`. Solo presentación, cero cambio al cálculo.
- **Esfuerzo: BAJO.** Mejor ratio de todas: convierte el score de veredicto a plan de acción y empuja al Radar con propósito.

## B2. Premiar la VELOCIDAD de reacción a un caliente
El Radar manda push cuando entra a bucket caliente. Seguimiento (25%) mide SI diste feedback, pero uno
dado en 10 días vale igual que uno en 2 horas — cuando la intención decae en horas.
- Ponderar cada acierto por latencia: `factor = 1/(1 + latencia_dias/(ttc/3))`, escalado contra el TTC de
  la empresa que ya existe en `$bench['time_to_close']`. Auto-ajustable, cero constantes nuevas.
- Inserción: `ActividadScore.php:529` (query) y `:564` (multiplicar aciertos).
- **Esfuerzo: MEDIO.** Cierra el loop push → abrir Radar → feedback inmediato con consecuencia en el score.

## B3. Quitar el acantilado binario de `pen_no_abiertas` (la injusticia más visible)
`ActividadScore.php:410`: **1 sola** cotización sin abrir 5+ días → `operativa=0` aunque tengas 20/21 abiertas.
Peor: la query (`:401`) no tiene ventana — una cotización zombie de hace 3 meses mata Activación para siempre.
Es la queja "hice todo bien y traigo la barra roja" que destruye la confianza.
- Fix: proporcional amplificado `min((no_abiertas/asignadas)×(1/close_rate_safe), 1.0)` — la MISMA fórmula que
  ya usa `eng_pen_sin_pago`. 1 de 21 duele (0.32), 3 de 10 mata (1.0). Frase con remedio + link.
- **Esfuerzo: BAJO.** Reparación de confianza #1.

## B4. Redistribuir peso cuando una dimensión no tiene datos
Sin calientes: `s_seguimiento`→0.50 (`:583`) y `s_radar_health`→0.50 (`:783`). El vendedor cuyos clientes
no generaron calientes tiene **35% del score congelado en la mitad** sin poder hacer nada — techo real ~82.
Los pesos 13/17/25/10/35 son lo único NO auto-ajustable del algoritmo que presume "cero valores fijos".
- Fix: con `cots_calientes==0`, poner ese peso en 0 y renormalizar los restantes. Cuando aparece el primer caliente, el peso regresa.
- Inserción: `ActividadScore.php:796`. **Esfuerzo: BAJO-MEDIO.** Elimina el techo injusto de empresas de bajo tráfico (la mayoría de 1 vendedor).

## B5. Explotar `score_historial` — hoy es WRITE-ONLY
`snapshot_mensual()` (`:1584`) escribe score/nivel/dimensiones cada mes y **ningún archivo lo lee** (grep confirma).
La materia prima de la retención guardándose en el vacío.
- (a) Flecha de momentum (`dashboard:899`) → delta concreto *"+6 vs el mes pasado"*.
- (b) Récord personal *"Tu mejor mes: 71"* (meta auto-ajustable por definición).
- (c) Frase de celebración al superar el récord.
- **Esfuerzo: BAJO.** Para empresas de 1 vendedor es LA comparación legítima. Nada que migrar.

## B6. Racha de días usando el Radar — visual, SIN tocar el score
`dias_activos` se calcula pero no hay concepto de racha consecutiva (el mecanismo de hábito diario más probado).
- Racha = días consecutivos con `radar_view` en `actividad_log`. *"Racha: 6 días revisando tu Radar"*.
- **Deliberadamente sin impacto en el score** (si diera puntos → farmeo Goodhart). Motiva por aversión a la pérdida.
- **Esfuerzo: BAJO.** Ataca directo el KPI "abre el Radar a diario".

## B7. Push de nivel al subir (nunca al bajar) + resumen semanal
La infra existe (`enviar_a_usuario`, ya usada en soporte). Cruzar de Regular (60) a Activo (61) pasa en silencio.
- Si `nivel_nuevo > nivel_prev` → push *"Subiste a Buen ritmo (65). Revisa tus calientes de hoy"*.
- **Jamás push de bajada** (push negativo = desinstalación). Complemento: push de lunes por la mañana.
- Inserción: `ActividadScore.php:816` (SELECT prev) + `:988`. **Esfuerzo: BAJO-MEDIO.** Canal de re-entrada a la app.

## B8. Rescate de calientes ANTES de que Radar Health los declare muertos
Radar Health (`:762`) castiga cuando la caliente YA murió — penalización forense, enseña resignación.
- Alerta preventiva: calientes con `ultima_vista_at` envejecida >`ttc/3` → *"2 calientes se están enfriando — rescátalas hoy"* con link.
- Premio: contar transiciones a buckets de resurrección (`revivio/re_enganche`, ya existen) como atenuante de muertas.
- **Esfuerzo: MEDIO.** Convierte Radar Health de autopsia a alarma de incendio — que vive en el Radar.

## B9. Gracia con propósito: checklist de arranque
El nuevo ve "tu score se activará en 4 días" — pantalla pasiva en la ventana donde se forman los hábitos.
- Checklist de onboarding (datos que ya existen): "✓ Primera cotización · ✓ Abriste el Radar · ○ Primer feedback a un caliente".
  Cada palomita es el hábito que después el score mide. Llega al día 15 ya entrenado.
- Inserción: `dashboard/index.php:863`. **Esfuerzo: BAJO-MEDIO.** El churn de los primeros 15 días es donde se pierde a la mayoría.

## B10. Tres fixes de coherencia auto-ajustable
1. `bonus_cierre` exige `cierres >= 4` (`:977`) — una empresa de 1 vendedor que cierra 2-3 JAMÁS lo gana
   (el perfil de Kevin, para quien se diseñó). Fix: `>= max(2, ceil($bench_ventas))`.
2. Tips = 50% de Activación (`:425`) — leer el diagnóstico pesa igual que toda la operación de apertura.
   Queja de confianza: "mi score baja porque no clickeo ver más". Fix: multiplicador suave como el `radar_why_score` (×1.0/0.85/0.70).
3. Leaderboard invisible al vendedor (`dashboard:1001`) aunque el percentil sí le afecta el score. Fix: toggle por empresa (patrón `termometro_visible`).
- **Esfuerzo: BAJO los tres.**

---

# Orden de implementación sugerido (por ratio impacto/esfuerzo)

| Prioridad | Mejora | Esfuerzo | Palanca |
|-----------|--------|----------|---------|
| 1 | A1 `accept_open` "casi aceptó" | Bajo | Señal ya en BD, la más fuerte, ignorada |
| 2 | A4 señales con ventana + botón WhatsApp | Bajo | Detectar → actuar en 1 tap |
| 3 | B1 "te faltan X pts" + 2 jugadas con link | Bajo | Score → plan de acción → Radar |
| 4 | B3 pen_no_abiertas proporcional | Bajo | Confianza (mata la queja #1) |
| 5 | A2 "está viéndola ahora" + push primera vista | Bajo-Medio | Venta en vivo, efecto wow en demos |
| 6 | B5 leer score_historial (delta + récord) | Bajo | Retención, empresas de 1 vendedor |
| 7 | A8 scrollRestoration (causa raíz ghost) | Bajo | Menos falsos positivos |
| 8 | B6 racha de días de Radar | Bajo | Hábito diario |
| 9 | A5 referer/canal | Bajo | Regreso deliberado + ROI Marketing |
| 10 | A6 momentum heating | Bajo | Detección temprana |
| 11 | B10 tres fixes de coherencia | Bajo | Justicia + filosofía |
| 12 | A7 afinar FIT (mismo-día + calibración) | Medio | Precisión del modelo (diferenciador) |
| 13 | B2 velocidad de reacción | Medio | El comportamiento que cierra ventas |
| 14 | A3 dwell en precio (totals_ms) | Medio | Curioso vs comprador |
| 15 | A11 multi-persona vs multi-dispositivo | Medio | Credibilidad de la señal estrella |
| 16 | B8 rescate preventivo | Medio | Radar Health accionable |
