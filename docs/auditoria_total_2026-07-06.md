# Auditoría total CotizaCloud — 6 julio 2026

Auditoría de todo el sistema con Fable 5: 8 auditores de dominio + refutación adversarial por hallazgo. **89 hallazgos confirmados** (sobrevivieron a un verificador que intentó tumbarlos leyendo el código).

> Los hallazgos marcados _(verificado inline)_ los confirmó el CEO-agente leyendo el código directo porque el límite de sesión cortó su verificador en el workflow.

## Seguridad

- **🔴 CRÍTICO** `api/quote_action.php:27` — quote_action.php: IDOR sin token — acepta/rechaza y crea ventas de CUALQUIER cotización por id secuencial
  - **Fix:** Exigir el `token` (32 hex) de la cotización en el body y validarlo: `WHERE id=? AND empresa_id=? AND token=?`. El token ya existe (cotizaciones.token) y es no-adivinable; el slug público ya lo tiene disponible para pasarlo. Sumar rate limit por IP.
- **🔴 CRÍTICO** `respaldoconfig.php + vapid_private.pem` _(verificado inline)_ — SECRETOS EN GIT: DB_PASS real (`Jalfonso234`) y APP_SECRET como fallback hardcodeado + llave privada VAPID versionada
  - **Fix:** Rotar credenciales YA, git rm --cached los archivos, agregarlos a .gitignore, mover secretos 100% a getenv
- **🔴 CRÍTICO** `api/mp_return.php:12` _(verificado inline)_ — SIN AUTENTICACIÓN + REPLAY: empresa_id viene del GET, no valida que el pago sea de esa empresa, y re-llamar el mismo payment_id aprobado extiende plan_vence +30/365 días cada vez (el UNIQUE solo protege el log, no la activación)
  - **Fix:** Exigir Auth::id(); validar que external_reference del pago == empresa_id de sesión; hacer idempotente la activación por mp_payment_id, no solo el log
- **🔴 CRÍTICO** `migrations/add_suscripciones.sql:10` _(verificado inline)_ — suscripciones.plan ENUM('pro','business') NO incluye 'lite' — si se vende Lite por MP, el pago se cobra pero el INSERT falla/trunca y no activa (latente hasta lanzar Lite)
  - **Fix:** ALTER suscripciones MODIFY plan ENUM('lite','pro','business')
- **🟡 MEDIO** `cron/procesar_suscripciones.php:191` _(verificado inline)_ — El cron degrada a Free las licencias MANUALES (gift/transferencia, sin fila en suscripciones) al vencer y pone activa=1 aunque el superadmin la haya suspendido
  - **Fix:** Excluir del degradado las empresas sin mp_customer_id que tengan licencia manual vigente; no tocar activa
- **🟡 MEDIO** `core/Helpers.php:131` _(verificado inline)_ — e_html usa strip_tags con <a>,<span>,<div> permitidos: strip_tags NO quita atributos → XSS almacenado vía href="javascript:" u on* en datos del cliente renderizados en el slug
  - **Fix:** Sanitizar atributos (whitelist con HTMLPurifier o regex que elimine on*/javascript:), o quitar <a> de la lista
- **🟡 MEDIO** `modules/config/ip_interna.php:8` _(verificado inline)_ — POST admin sin csrf_check (solo requerir_admin) — igual en calibrar_radar.php y logo.php
  - **Fix:** Agregar csrf_check() a los POST de config
- **🟡 MEDIO** `modules/ventas/agregar_extra.php:32` — Endpoints de extras acotados sólo por empresa: cualquier asesor con permiso edita ventas de otros asesores
  - **Fix:** Añadir, cuando no es admin, el filtro de propiedad: rechazar si `venta.usuario_id != Auth::id() && venta.vendedor_id != Auth::id()` salvo permiso ver_todas_ventas (como ya hace eliminar_gasto.php).

## Cotizaciones

- **🔴 CRÍTICO** `public/cotizacion.php:1817` — El slug público sobrescribe el Total al cargar: excluye extras y borra el cupón aplicado por el asesor
  - **Fix:** En calc(): partir de SUB, aplicar descuentos, sumar extras al final (const EXTRAS = <?= (float)$subtotal_extras ?>) y restar CUPON_GUARDADO = <?= (float)$cupon_monto_guardado ?>; no ocultar #tCR cuando hay cupón guardado.
- **🔴 CRÍTICO** `modules/cotizaciones/guardar.php:128` — guardar.php y quote_action.php aplican descuentos e impuesto TAMBIÉN sobre los extras; el editor y el slug los tratan aparte
  - **Fix:** En guardar.php y quote_action.php separar subRegular/subExtras (WHERE es_extra=0 en el SUM), aplicar cupón+descuento+impuesto solo a subRegular y sumar extras al final, replicando exactamente calcularTotales() del editor.
- **🟠 ALTO** `api/quote_action.php:80` — Al aceptar, quote_action ignora el cupón guardado en la cotización: el descuento otorgado por el asesor se evapora
  - **Fix:** En quote_action, si $body['cupon_codigo'] viene vacío, usar cotizaciones.cupon_codigo/cupon_monto guardados (ya cargados en $cot_data) como fallback antes de recalcular.
- **🟠 ALTO** `api/quote_action.php:238` — CAPI Lead y QuoteRejected nunca se envían: $empresa_id no existe en quote_action.php (TypeError silenciado)
  - **Fix:** Usar EMPRESA_ID en ambas llamadas y $total_guardar como valor: `MarketingPixels::capi_lead(EMPRESA_ID, $total_guardar, $mon)` leyendo la moneda de empresas.
- **🟠 ALTO** `api/quote_action.php:82` — quote_action acepta cupones VENCIDOS: solo valida activo=1, no vencimiento
  - **Fix:** Replicar en el WHERE/PHP del quote_action la lógica de expiración de cotizacion.php:129-135 (fecha_fija y dias_cotizacion contra created_at de la cot) antes de aplicar el cupón.
- **🟡 MEDIO** `modules/cotizaciones/guardar.php:151` — Editar una cotización con descuento automático VENCIDO sigue restándolo del total guardado
  - **Fix:** En guardar.php, antes de la línea 151: si $desc_auto_expira ya pasó (strtotime < time()), poner $desc_auto_activo=0 y $desc_auto_amt=0 (o renovar la fecha explícitamente si el asesor reactiva).
- **🟡 MEDIO** `public/cotizacion.php:1374` — Descuento fijo + descuento automático: el slug muestra un total y el servidor cobra otro (orden de aplicación invertido)
  - **Fix:** Unificar el orden: aplicar en el JS del slug el cupón primero y el descuento auto sobre el remanente, igual que guardar.php y quote_action.
- **🟡 MEDIO** `modules/cotizaciones/clonar.php:82` — Clonar copia el total CON descuento de cupón pero no copia el cupón: el clon nace con total inconsistente con sus propias líneas
  - **Fix:** Copiar también cupon_id, cupon_codigo, cupon_pct y cupon_monto en el INSERT del clon (y anular descuento_auto_* si descuento_auto_expira ya pasó).
- **🟡 MEDIO** `modules/cotizaciones/enviar.php:32` — Re-enviar una cotización ya VISTA regresa su estado a 'enviada' (parece que el cliente nunca la abrió)
  - **Fix:** Cambiar a `SET estado = IF(estado='vista','vista','enviada'), enviada_at=COALESCE(enviada_at,NOW())` (o solo tocar estado cuando venga de 'borrador') y agregar check de permiso/ownership.
- **🟡 MEDIO** `modules/cotizaciones/crear.php:26` — Límite de 25 del plan Free con race condition y crear.php acepta cotizaciones sin líneas
  - **Fix:** Mover el check de límite dentro de la transacción con `SELECT COUNT(*) ... FOR UPDATE` (o UNIQUE folio-based); y replicar en crear.php las validaciones de líneas de guardar.php:143-146.
- **⚪ BAJO** `modules/cotizaciones/ver.php:944` — El editor muestra $0 de descuento para cupones de monto fijo (solo calcula por porcentaje)
  - **Fix:** En calcularTotales(): `cuponAmt = cuponSeleccionado.monto_fijo ? Math.min(cuponSeleccionado.monto_fijo, subRegular) : subRegular*(cuponSeleccionado.pct/100);` (exponer monto_fijo en el objeto del cupón).
- **🟡 MEDIO** `api/quote_action.php:117` — Al aceptar con cupón/descuento, impuesto_amt queda obsoleto en la fila (solo se actualiza total)
  - **Fix:** En el mismo UPDATE de aceptación, persistir también impuesto_amt recalculado: `round($base_srv * $imp_pct/100, 2)` para modo 'suma' y la fórmula de 'incluido' para ese modo.

## Ventas / Dinero

- **🔴 CRÍTICO** `api/quote_action.php:73` — El total que acepta el cliente en el slug NO es el total con que se crea la venta cuando hay extras
  - **Fix:** Unificar la fórmula: decidir si los extras entran o no a descuentos/impuestos y aplicarla igual en quote_action.php (filtrar es_extra o no), en el render PHP del slug y en el JS (pasar SUB_EXTRAS al calc() y al modal de aceptar).
- **🟠 ALTO** `public/recibo.php:48` — Recibo público muestra 'Total pagado' incorrecto: mezcla el total VIVO de la venta con el saldo snapshot del recibo
  - **Fix:** Calcular con puros snapshots: `$pagado_despues = $pagado_antes + ($es_cancelacion ? 0 : $monto);` y no derivar nada del total vivo.
- **🟠 ALTO** `modules/ventas/abono.php:51` — Folios de recibo generados con MAX(id) global: saltan según la actividad de OTRAS empresas y pueden duplicarse en concurrencia
  - **Fix:** Usar el mecanismo atómico que ya existe: `DB::siguiente_folio($empresa_id, 'REC', 'REC')` (core/DB.php:111) como se hace con VTA, y agregar UNIQUE (empresa_id, numero) en recibos.
- **🟠 ALTO** `modules/ventas/acciones.php:174` — Descuento manual y descuento automático se borran mutuamente al recalcular el total de la venta
  - **Fix:** Ambos recálculos deben restar los DOS descuentos: en acciones.php incluir descuento_auto_amt en el SELECT y en la resta; en guardar.php restar también descuento_manual_amt; acotar saldo con max(0) y base con max(0) antes del IVA.
- **🟠 ALTO** `modules/ventas/acciones.php:124` — agregar-item y editar-linea suman/restan el subtotal directo al total sin aplicar IVA ni descuentos (y sin round)
  - **Fix:** Reemplazar los deltas por el mismo recálculo completo de guardar.php (subtotal de líneas − cupón − descuentos, luego IVA, round 2) tras cada INSERT/UPDATE/DELETE de línea.
- **🟡 MEDIO** `api/quote_action.php:39` — Re-aceptación de cotización ya aceptada sin autenticación: sobreescribe totales, infla usos de cupón y duplica notificaciones
  - **Fix:** En la rama aceptar, validar contra `['enviada','vista']` (dejar 'aceptada' solo para rechazar si es intencional) o retornar idempotente 'ya aceptada' sin re-ejecutar UPDATEs ni notificaciones.
- **🟡 MEDIO** `modules/ventas/ver.php:15` — Detalle de venta y registro de abonos sin verificación de ownership: asesor ve y abona ventas ajenas
  - **Fix:** En ver.php, abono.php y las acciones de acciones.php replicar el gate de 'notas': si no tiene ver_todas_ventas y no es usuario_id/vendedor_id de la venta → 403.
- **🟡 MEDIO** `modules/ventas/guardar.php:20` — Recálculo de saldo con 'pagado' leído sin lock: guardar.php y cancelar_recibo.php pueden pisar un abono concurrente
  - **Fix:** Mover el SELECT de la venta dentro de la transacción con `FOR UPDATE` en guardar.php y cancelar_recibo.php (mismo patrón que abono.php:40-43).
- **🟡 MEDIO** `public/venta.php:460` — Vista pública de venta nunca muestra la línea de IVA: el desglose visible no suma al total
  - **Fix:** Agregar la fila de impuesto (label y monto, como ya lo hace el slug de cotización en cotizacion.php:904-906) en el resumen y en la vista imprimible cuando impuesto_modo != 'ninguno'.
- **🟡 MEDIO** `modules/ventas/guardar.php:73` — Estado de cuenta imprimible usa impuesto_amt viejo: ventas/guardar.php nunca recalcula impuesto_amt ni total de la cotización
  - **Fix:** En ventas/guardar.php incluir `impuesto_amt=?` y `total=?` en el UPDATE de cotizaciones con los valores recalculados; mismo ajuste en agregar_extra/eliminar_extra.
- **🟡 MEDIO** `modules/ventas/guardar.php:93` — Total de venta puede quedar NEGATIVO con IVA suma: la rama 'suma' no clampa la base y el descuento no se topa contra el subtotal
  - **Fix:** Aplicar `$base = max(0, $base);` antes de calcular el impuesto en los 4 archivos, y rechazar descuentos mayores al subtotal (`$desc_auto_amt = min($desc_auto_amt, $subtotal_lineas - $cupon_amt)`).
- **⚪ BAJO** `modules/ventas/guardar.php:63` — guardar.php acepta es_extra sin gate de plan Business ni permiso agregar_extras: bypass del endpoint dedicado
  - **Fix:** En ventas/guardar.php y cotizaciones/guardar.php: si `!trial_info(EMPRESA_ID)['es_business']` o el usuario no tiene agregar_extras, forzar `$es_extra = 0` (o preservar el valor previo de la línea).

## Motor Radar

- **🟠 ALTO** `modules/radar/Radar.php:1886` — calibrar() entrena el FIT con sesiones contaminadas (internos/bots/sin dedupe) y con gap distinto al que usa score()
  - **Fix:** En la query de calibrar(): agregar `AND qs.es_interno = 0`, deduplicar sesiones con la misma ventana que score() (subquery o agregación por ventana de 30 min), y calcular el gap penúltima→última sesión en vez de DATEDIFF(MAX,MIN).
- **🟡 MEDIO** `modules/radar/index.php:1260` — Config Radar promete 'IPs internas (excluidas del radar)' pero nada las excluye, y los botones Agregar/Quitar pegan a rutas inexistentes
  - **Fix:** Decidir: (a) reactivar exclusión por IP en score()/cotizacion.php con TTL corto, o (b) eliminar la sección y el toggle engañoso de la UI. En cualquier caso, registrar las rutas /radar/ip (POST) y /radar/ip/:id (DELETE) o apuntar el JS a /config/ip-interna.
- **🟡 MEDIO** `modules/radar/Radar.php:588` — El loop post-guest usa filtros viejos distintos al loop principal: ghost-restore infla multi_persona y probable_cierre
  - **Fix:** Replicar exactamente los filtros del loop principal en el loop post-guest: min-visita `($s['visible_ms'] !== null && $vi2 < 200 && $sc2 < 35)` y ghost check por vid con engagement (scroll>0 || vis>=2000).
- **🟡 MEDIO** `modules/radar/Radar.php:976` — multi_persona y cat_social se disparan con e_uniq_v, métrica que el propio código declara inflada por vids fantasma
  - **Fix:** Quitar `$e_uniq_v >= 2` de multi_persona (línea 976) y de cat_social (líneas 1126-1127), dejando $vids_post_guest_count (validado por union-find) e IPs validadas como únicas señales, igual que ya se hizo con el booster de priority.
- **🟡 MEDIO** `modules/radar/Radar.php:1560` — recalcular_empresa corta a 120s con orden fijo: la cola de cotizaciones nunca se recalcula y sus buckets calientes quedan pegados
  - **Fix:** Recorrer la cola con rotación: `ORDER BY radar_updated_at ASC` (las más viejas primero) o persistir un cursor; alternativamente mover el recálculo masivo a cron y dejar el on-page solo para la cabeza.
- **🟡 MEDIO** `modules/radar/index.php:64` — Para el asesor filtrado, 'Cierre global' divide ventas de TODA la empresa entre sus cotizaciones propias
  - **Fix:** Aplicar el mismo filtro de vendedor a $stat_aceptadas (JOIN ventas→cotizaciones filtrando COALESCE(c.vendedor_id,c.usuario_id)=uid) o etiquetar las tarjetas como métricas de empresa cuando hay filtro.
- **🟡 MEDIO** `modules/radar/Radar.php:2181` — engage_avg() calcula el umbral de 'lectura_comprometida' incluyendo sesiones internas
  - **Fix:** Agregar `AND qs.es_interno = 0 AND qs.visitor_id NOT IN (SELECT visitor_id FROM radar_visitors_internos WHERE empresa_id = ?)` a la query de engage_avg().
- **🟡 MEDIO** `api/cot_feedback.php:68` — cot_feedback: el bloqueo de internos solo aplica si el cliente envía visitor_id — un asesor en incógnito puede autocalificarse 5 estrellas
  - **Fix:** Verificar también la sesión del request: si Auth::logueado() y el usuario pertenece a la empresa (no solo superadmin), rechazar con 'interno'. Mantener el check por vid como capa adicional, no única.
- **⚪ BAJO** `modules/radar/Radar.php:1529` — Dedupe de push por LIKE sin delimitador: la cotización 12 suprime su alerta si la 123 tuvo push en 24h
  - **Fix:** Cambiar el patrón a `'%"cotizacion_id":' . $cotizacion_id . ',%'` (o mejor: `JSON_EXTRACT(datos, '$.cotizacion_id') = ?`), o agregar columna cotizacion_id indexada a notificaciones_push.
- **⚪ BAJO** `modules/radar/index.php:752` — El detalle de la alerta de competencia no filtra es_interno=0: muestra sesiones del asesor como vistas del 'competidor'
  - **Fix:** Agregar `AND qs.es_interno = 0` al WHERE de la query en render_comp_row (línea ~753).
- **⚪ BAJO** `modules/radar/Radar.php:547` — no_abierta ignora la vigencia: $vigencia_ts se calcula y nunca se usa, aunque la UI promete 'dentro de su vigencia'
  - **Fix:** Usar la variable ya calculada: agregar `&& $now <= $vigencia_ts` a la condición de no_abierta (o quitar 'dentro de su vigencia' del hint si se decide mostrarlas con etiqueta VENCIDA).
- **⚪ BAJO** `modules/radar/index.php:233` — Score% fabricado cuando falta fit_pct: la UI inventa score×0.65 y lo presenta con 1 decimal
  - **Fix:** Mostrar '—' cuando fit_pct no existe en radar_senales en lugar de fabricar el valor: `'fit_pct' => isset($senales['fit_pct']) ? (float)$senales['fit_pct'] : null` y renderizar null como guion.

## Tracking / Escudo

- **🟠 ALTO** `public/cotizacion_inmueble.php:604` — Giro inmuebles: el tracker JS envía 'scroll_max' pero track.php lee 'max_scroll' — el scroll llega siempre 0
  - **Fix:** En cotizacion_inmueble.php:604 renombrar la clave a `max_scroll:maxScroll` y agregar `session_id`, `page_id`, `device_sig` y `open_ms` al payload, igual que el sendEvent() de cotizacion.php:1642-1654.
- **🟡 MEDIO** `modules/dashboard/index.php:353` — Ghost cleanup del dashboard borra sesiones con visible_ms NULL (adblocker/JS no cargó) que track.php protege explícitamente
  - **Fix:** Alinear el criterio del dashboard con track.php: cambiar a `qs.visible_ms IS NOT NULL AND qs.visible_ms < 200 AND qs.scroll_max = 0` (o reutilizar la misma lógica en una función compartida).
- **🟡 MEDIO** `core/layout.php:104` — Cleanup retroactivo de layout.php no recalcula vista_at ni revierte estado — la cotización queda 'vista' con la hora del asesor
  - **Fix:** En el mismo UPDATE agregar `c.vista_at = (SELECT MIN(qs.created_at) FROM quote_sessions qs WHERE qs.cotizacion_id=c.id AND qs.es_interno=0)` y, si el resultado es NULL y estado='vista', evaluar revertir a 'enviada' (o al menos marcarla para revisión).
- **🟡 MEDIO** `modules/radar/Radar.php:401` — Radar::score() no filtra quote_sessions.es_interno=1 — sesiones marcadas internas siguen contando en los buckets si su vid no está en la lista
  - **Fix:** Agregar `AND es_interno = 0` al query de sesiones en score() (con fallback si la columna no existe) — coincide con el pendiente documentado 'es_interno=1 + capa_motivo: 5 queries downstream'.
- **🟡 MEDIO** `public/cotizacion.php:1632` — getScrollPct() devuelve 100 cuando la página cabe en el viewport — quote_open reporta scroll=100 sin interacción y los previews evaden el filtro anti-ghost
  - **Fix:** Cuando den<=0 devolver 0 y tratar 'página completa visible' como señal aparte, o solo reportar scroll>0 si hubo interacción real (`navigator.userActivation.hasBeenActive`), que además resuelve el scroll-restore de raíz.
- **🟡 MEDIO** `public/cotizacion.php:166` — Parámetro _sv sin firma permite fijar el cz_vid de cualquier visitante (y ya no lo emite nadie: código huérfano)
  - **Fix:** Eliminar el bloque _sv completo (el bridge HMAC de safari_bridge.php ya cubre el caso de uso) o exigir firma HMAC igual que el bridge.
- **🟡 MEDIO** `public/cotizacion.php:1831` — Los pixels de marketing cliente-side se disparan también para asesores internos y superadmin — solo el CAPI está gateado
  - **Fix:** Envolver scripts_base() y evento_view() en `if (!$es_usuario_interno)` (la variable ya existe en la línea 221) y hacer lo mismo en cotizacion_inmueble.php.
- **🟡 MEDIO** `core/Helpers.php:317` — ip_real() confía en CF-Connecting-IP y X-Forwarded-For sin estar detrás de Cloudflare — la IP del tracking es spoofeable
  - **Fix:** Ignorar CF/XFF salvo que REMOTE_ADDR esté en una lista blanca de proxies conocidos (rangos CF o el LB propio); si no, usar solo REMOTE_ADDR. Rechazar IPs privadas/reservadas con FILTER_FLAG_NO_PRIV_RANGE|NO_RES_RANGE en los headers forwarded.
- **🟡 MEDIO** `modules/dashboard/index.php:366` — Ghost cleanup del dashboard decrementa visitas por resta y sin filtrar es_interno — descuenta dos veces sesiones ya excluidas
  - **Fix:** Agregar `AND qs.es_interno = 0` al SELECT de ghosts y reemplazar la resta por el mismo recalc con subconsultas COUNT/MIN/MAX que usa track.php:297-304.
- **🟡 MEDIO** `api/track.php:157` — track.php sobrescribe el visitor_id de la sesión cuando el fallback por IP fusiona a dos clientes distintos (CGNAT)
  - **Fix:** Invertir el COALESCE a `visitor_id=COALESCE(visitor_id, ?)` (solo rellenar si estaba NULL); si el evento trae un vid distinto al de la sesión matcheada por IP, crear sesión nueva en vez de reusar.
- **⚪ BAJO** `public/cotizacion_inmueble.php:546` — Inmuebles: czTrack('quote_accept'/'quote_reject') no están en tipos_validos de track.php — se descartan en silencio
  - **Fix:** Cambiar a czTrack('accept_confirm') y czTrack('reject_confirm') en cotizacion_inmueble.php (o agregar los alias a $tipos_validos).
- **🟡 MEDIO** `public/cotizacion.php:243` — escudo_log quedó ciego para bots: capa_3_bot es inalcanzable (BOT_IP=[]) y es_bot(UA) sale sin registrar nada
  - **Fix:** Antes del salto por es_bot($ua), llamar `escudo_log_decision('bot_ua', ...)`; y registrar también los estados no publicables si se quiere auditar cobertura completa (pendiente #9 del audit 24 mayo).
- **⚪ BAJO** `api/track.php:284` — Ghost cleanup de track.php borra también sesiones es_interno=1 — destruye el rastro de auditoría del Escudo
  - **Fix:** Agregar `AND es_interno = 0` al SELECT de ghosts en track.php:284-289.

## Matemática del Score

- **🟠 ALTO** `core/ActividadScore.php:1606` — snapshot_mensual falla SIEMPRE: ON DUPLICATE referencia columnas inexistentes en score_historial y el catch lo silencia
  - **Fix:** Quitar las 4 asignaciones de columnas inexistentes del ON DUPLICATE (o correr migración que agregue s_engagement/eng_pen_* a score_historial). Además loggear el Throwable con error_log en vez de tragarlo.
- **🟠 ALTO** `core/ActividadScore.php:432` — pen_sin_pago: numerador sin ventana vs denominador de 15 días — una venta vieja sin cobrar deja Engagement en 0 para siempre
  - **Fix:** Acotar el numerador a la misma ventana del período: agregar `AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)` (con $periodo) a la query de ventas_sin_pago, o al menos un tope (ej. 60 días) coherente con el denominador.
- **🟠 ALTO** `core/ActividadScore.php:928` — w_momentum sin tope: en empresas con close_rate alto, el momentum domina el score y un vendedor perfecto queda topado en ~60-70
  - **Fix:** Capar w_momentum, ej. `$w_momentum = (1.0 - $w_percentil) * min($close_rate_safe, 0.25);` para que proporcional nunca pese menos de ~50%.
- **🟠 ALTO** `core/ActividadScore.php:429` — tasa_cierre mezcla ventanas: cierres por accion_at vs vistas por created_at — puede superar 100% e infla sigmoid, piso y bonus
  - **Fix:** Usar la misma población: contar cierres de cotizaciones con `created_at >= ventana` (cohorte), o denominar sobre cotizaciones vistas en cualquier fecha cuyos cierres caen en la ventana.
- **🟡 MEDIO** `core/ActividadScore.php:1515` — close_rate_hist sesgado a la baja por el corte accion_at vs created_at — infla sistemáticamente bonus_cierre
  - **Fix:** Calcular el histórico por cohorte: cotizaciones con created_at < corte y su resultado final sin importar cuándo cerró (`estado IN ('aceptada',...)` sin filtro de accion_at en el numerador).
- **🟡 MEDIO** `core/ActividadScore.php:105` — GRACIA_DIAS=7 'temporal para testing' quedó en producción — vendedores nuevos evaluados con ventana incompleta
  - **Fix:** Cambiar `GRACIA_DIAS = 7` a `= 15` (o `= self::PERIODO`) como indica el propio comentario.
- **🟡 MEDIO** `core/ActividadScore.php:901` — Percentil compara escalas distintas: mi proporcional crudo (sin bonus/momentum) contra el score final de los compañeros (con bonus hasta +23)
  - **Fix:** Comparar magnitudes iguales: usar `us.tasa_gestion` (proporcional persistido) para los compañeros en vez de `us.score`, o persistir primero y comparar todos por score final.
- **🟡 MEDIO** `core/ActividadScore.php:994` — Columna 'bonuses' persiste un bonus fantasma que nunca se aplica al score — el debug panel muestra un premio inexistente
  - **Fix:** Eliminar $total_bonus y persistir 0 (o persistir bonus_ticket+bonus_cierre reales en 'bonuses'), y ajustar el panel para mostrar solo bonus aplicados.
- **🟡 MEDIO** `core/ActividadScore.php:401` — no_abiertas_5d ignora el filtro de importación masiva ($no_import) — una importación sin visitas mata Activación indefinidamente
  - **Fix:** Agregar `$no_import` a la query de no_abiertas_5d (misma exclusión que las demás métricas de Activación).
- **🟡 MEDIO** `core/ActividadScore.php:1474` — Cache de _benchmarks ignora el parámetro $periodo — vendedores de la misma empresa pueden evaluarse con períodos/benchmarks mezclados
  - **Fix:** Incluir el período en la key del cache: `self::$_bench[$empresa_id.'-'.$periodo]`, y decidir el período extendido UNA vez por empresa en recalcular_empresa().
- **⚪ BAJO** `core/ActividadScore.php:991` — Columna 'penalizaciones' exagera el impacto real: cuenta pen_no_abiertas=1.0 y pen_dormidas a peso completo cuando en el score entran al 50%
  - **Fix:** Calcular total_pen con el impacto efectivo: `(($no_abiertas_5d>0 ? $tasa_apertura : 0) + $pen_dormidas) * 0.5 * $w_act + ...`.
- **⚪ BAJO** `core/ActividadScore.php:1045` — Columna radar_views persiste fb_total pero calcular() retorna radar_sessions bajo la misma llave — el panel ejecutivo muestra un dato distinto al del cálculo en vivo
  - **Fix:** Persistir fb_total en su propia columna (o reusar una legacy con comentario, como se hizo con transiciones_up) y dejar radar_views = $radar_sessions en ambos lados.
- **⚪ BAJO** `core/ActividadScore.php:1119` — pen_seguimiento retornada para debug pero nunca definida — siempre 0 aunque pen_buckets sí penalice Seguimiento
  - **Fix:** Retornar/persistir `'pen_seguimiento' => round($pen_buckets, 3)`.

## Core / Infra

- **🟠 ALTO** `core/DB.php:123` _(verificado inline)_ — siguiente_folio NO atómico en la rama fallback: el re-SELECT tras ON DUPLICATE corre fuera de la operación atómica → 2 concurrentes leen el mismo ultimo → folios duplicados
  - **Fix:** Usar ON DUPLICATE KEY UPDATE ultimo = LAST_INSERT_ID(ultimo+1) y leer solo LAST_INSERT_ID() (per-conexión, atómico)
- **🟠 ALTO** `core/MercadoPago.php:135` _(verificado inline)_ — Idempotency-Key con random_bytes(4): en un timeout el reintento genera key NUEVA → MP lo trata como cargo nuevo → DOBLE CARGO real al cliente
  - **Fix:** Key determinística por periodo: cz_renew_{empresa}_{YYYYMM} sin random
- **🟠 ALTO** `core/Helpers.php:384` _(verificado inline)_ — trial_info ejecuta ALTER TABLE (DDL) sobre empresas en CADA request (aunque las columnas ya existan, intenta y falla en cada carga)
  - **Fix:** Mover los ALTER a migración one-shot; quitarlos del hot path
- **🟡 MEDIO** `core/MercadoPago.php:458` _(verificado inline)_ — Webhook MP inserta estado crudo en pagos_suscripcion.estado ENUM(approved,pending,rejected,refunded): estados reales de MP como in_process/authorized/charged_back revientan/truncan el INSERT y el evento se pierde
  - **Fix:** Mapear estados MP al ENUM o ampliar el ENUM; envolver en validación
- **🟡 MEDIO** `core/Mailer.php:50` _(verificado inline)_ — Mailer captura la excepción y solo loguea si DEBUG=true → en producción (DEBUG=false) los fallos de correo son 100% mudos (reset password, avisos superadmin, etc.)
  - **Fix:** Loguear SIEMPRE el error de correo (no solo en DEBUG)
- **⚪ BAJO** `core/PushNotification.php:118` — badge_count se incrementa aunque el push falle: badge del iPhone se infla permanentemente
  - **Fix:** Incrementar badge_count solo después de envío exitoso, o decrementarlo en el catch cuando $ok sea false.
- **⚪ BAJO** `core/Helpers.php:198` — input_int/input_float nunca devuelven el default por precedencia de operadores
  - **Fix:** `function input_int(string $key, int $default = 0): int { $v = input($key); return $v !== '' ? (int)$v : $default; }` (idem float).

## Producto (mejoras)

- **🟠 ALTO** `modules/radar/index.php:228` — El Radar detecta intención pero no la convierte en acción: teléfono del cliente cargado y nunca usado, mensajes del playbook sin botón WhatsApp ni copiar
  - **Fix:** En cada fila de bucket caliente agregar botón '💬' → https://wa.me/52{ctel limpio}?text={mensaje del playbook del bucket con [Nombre] sustituido por cnombre, urlencoded}. En el modal playbook (línea 1427) botón 'Copiar' y 'Enviar por WhatsApp' por mensaje. Esfuerzo ~3h.
- **🟠 ALTO** `modules/cotizaciones/ver.php:650` — Compartir por WhatsApp manda solo la URL pelona: sin número del cliente ni mensaje profesional pre-armado
  - **Fix:** Cambiar a wa.me/{52+telefono limpio}?text={mensaje con nombre, folio, total, vigencia y url}. Agregar el mismo botón '💬 Enviar' en lista.php desk-actions (línea 521) y exp-btns (línea 465) usando ctel ya cargado en la query (línea 88). ~2h.
- **🟠 ALTO** `core/PushNotification.php:65` — Cero loop de retención: existe infra completa de push (APNs+WebPush) pero ningún resumen diario accionable — el único cron es de suscripciones
  - **Fix:** Crear cron/resumen_diario.php (cron 8am America/Hermosillo): por asesor activo, contar buckets calientes, sin-abrir>3d y ventas sin pago, y mandar enviar_a_usuario() con deep link /radar. Respetar notif_config. ~4h.
- **🟠 ALTO** `modules/dashboard/index.php:327` — Alerta 'Sin abrir' del dashboard no ofrece reenvío 1-clic: ni siquiera trae el teléfono del cliente
  - **Fix:** Agregar cl.telefono a la query y un botón '💬 Reenviar' en la fila con wa.me/{tel}?text={mensaje del playbook no_abierta con nombre + url del slug}. ~1h.
- **🟡 MEDIO** `modules/radar/index.php:379` — No existe 'ya lo contacté': el asesor ve la misma lista de calientes sin saber cuáles ya trabajó
  - **Fix:** Botón '✆ Contactado' junto a 👍/👎 que inserte evento en cotizacion_log (evento='contactado', usuario_id) y muestre badge 'contactado hace 2h' en la fila (leer último log en la query principal). ~3h, sin migración.
- **🟡 MEDIO** `modules/radar/Radar.php:1536` — Push del Radar se manda a TODA la empresa sin cliente ni monto, en vez de al vendedor asignado con contexto vendible
  - **Fix:** Enviar a vendedor_id con enviar_a_usuario() (fallback a admin si no hay asignado), título 'María López está por cerrar 🎯', body con monto (rmoney) y bucket, url '/cotizaciones/{id}'. ~2h.
- **🟡 MEDIO** `modules/dashboard/index.php:302` — El motivo de rechazo que escribe el cliente se consulta en el dashboard pero nunca se muestra
  - **Fix:** En alert-meta de rechazadas (línea 1338) agregar el motivo truncado: '<?php if ($r['motivo_rechazo']): ?> · "'.e(mb_substr($r['motivo_rechazo'],0,60)).'"<?php endif ?>' con title completo. 15 minutos.
- **🟡 MEDIO** `modules/clientes/ver.php:53` — Ficha de cliente (360) ciega al Radar: lista sus cotizaciones sin visitas, bucket ni última vista
  - **Fix:** Agregar visitas, radar_bucket, ultima_vista_at al SELECT (línea 54) y renderizar el mismo radar_badge/👁 de lista.php en cada fila del historial. ~2h reusando radar_badge().
- **🟡 MEDIO** `modules/cotizaciones/ver.php:341` — En el editor el bucket del Radar aparece como etiqueta muda: sin el '¿por qué?' ni playbook que sí existen en el módulo Radar
  - **Fix:** Hacer el badge clickable: popover con Radar::explicar_bucket($senales) + link '📖 Ver playbook' (a /radar o /ayuda/buckets en Lite). radar_senales ya viene en $cot. ~2h.
- **🟡 MEDIO** `core/layout.php:594` — Sin botón '+' de creación en el bottom nav móvil: la acción #1 del vendedor en campo queda a 3 taps
  - **Fix:** FAB central '+' en el bottom nav (patrón Instagram/WhatsApp Business) → /cotizaciones/nueva, visible si Auth::puede('crear_cotizaciones'). ~1-2h de CSS+HTML en layout.php.
- **🟡 MEDIO** `modules/cotizaciones/ver.php:609` — Patrón horario de apertura del cliente: dato ya almacenado en quote_sessions y jamás explotado (0 usos de HOUR() en todo el repo)
  - **Fix:** Bajo el historial de visitas (línea 609): query GROUP BY HOUR(created_at) sobre quote_sessions es_interno=0 del cliente_id (todas sus cots); si hay ≥3 visitas y una franja concentra >40%, mostrar '🕐 Suele revisar entre Xh y Yh'. ~3h.
- **🟡 MEDIO** `modules/cotizaciones/ver.php:690` — El dialog 'Asignar cliente' del editor no permite crear cliente nuevo, a diferencia del builder de nueva cotización
  - **Fix:** Portar el tab 'Nuevo cliente' + crearClienteNuevo() de nueva.php:706/1096 al clientDialog de ver.php (mismo endpoint /clientes/crear). ~1h de copy-adapt.
