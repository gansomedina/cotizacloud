# Mesa de Trabajo — Diseño final v1 (8 julio 2026)

Complemento operativo al score/termómetro: la cola de trabajo diaria del asesor,
armada sola por los 3 motores existentes (Radar = calor, ciclo_venta = ventana,
monto = tamaño), donde el asesor solo declara EL DESENLACE de cada contacto con
un tap — y cada declaración se AUDITA contra el comportamiento real del cliente.
"Un CRM pero mejor hecho": el pipeline se llena solo, los estados no pueden
mentir, y el reporte al dueño distingue siempre lo declarado de lo observado.

Basado en: spec técnica anclada al código (agente 1) + investigación de CRMs
competencia con fuentes (agente 2: HubSpot, Pipedrive, Close, noCRM, Zoho,
Kommo, Leadsales/Sirena, Gong, Proposify, DocSend). Validado con datos reales
de producción (vendedor Manuel uid 20: 105 activas → mesa de ~16 filas; su
empresa cierra p25=1d/mediana=2d/p75=7d y jamás cerró >30d; el 👎 del asesor
cierra 0.6% → 163/164 aciertos).

## 1. TAXONOMÍA FINAL DE DESENLACES (un tap + segundo tap solo al descartar)

Principio (noCRM + el anti-ejemplo de Zoho): pocos estados, inequívocos entre
sí, y cada uno dispara comportamiento DISTINTO del sistema. La etapa del
pipeline NO se captura — la infiere el Radar (elimina la causa #1 de datos
podridos: HubSpot exige stage+outcome+status y se desincronizan).

| # | Estado (tap) | Qué hace el sistema | Cómo lo audita el Radar |
|---|---|---|---|
| 1 | 📵 No contesta | Reagenda reintento (cadencia 1/3/7d tipo Close); a los 3 intentos sugiere cambiar de canal | Cliente abre el slug después → "te evita pero le interesa, cambia de canal" |
| 2 | 💬 Está decidiendo | Ventana de espera = ciclo real de la empresa; al vencer vuelve a la mesa | No reabre ni una vez en la espera → "decidiendo pero frío"; reabre precio → "llama HOY" |
| 3 | 📅 En cita | Pide fecha (hoy/mañana/elegir); recordatorio; post-fecha exige desenlace nuevo | LA ESTRELLA: "cita declarada y el cliente no ha vuelto a abrir en 9 días" = cita en riesgo → sugerir confirmación por WhatsApp la víspera |
| 4 | 💰 Objeción precio | Playbook (parcialidades, descuento con vencimiento, versión ajustada); ventana corta 48-72h | Cruza con bucket validando_precio: reabre solo precios = confirma; deja de abrir = era despedida educada |
| 5 | ✏️ Pidió cambios | Tarea "enviar versión nueva" con SLA <24h (Better Proposals: +23% conversión) | Detecta si la versión nueva fue abierta; si no en 48h → reintento. (Conecta con hallazgo validado: cotización que se mueve cierra 3.2x) |
| 6 | 🏆 Ganada | NO declarable — la pone el sistema al aceptar slug o registrar anticipo (anti-sandbagging, Gong) | Viene del dinero, no del asesor |
| 7 | 🗑 Descartada → 2º tap razón | Sale de la mesa, entra al archivo con razón reportable | NO muere: si el cliente reabre → RESUCITA en la mesa con contexto ("descartada por precio el 12/jun; hoy la abrió 2 veces") |

Razones de descarte (dropdown corto obligatorio, jamás texto libre):
1. Muy caro / sin presupuesto · 2. Se fue con otro (+"¿quién?" opcional) ·
3. Lo dejó para después (re-contacto auto en 90d) · 4. Dejó de responder ·
5. No era comprador real (no ensucia tasa de cierre) · 6. Otro (texto corto)

Estado implícito NO declarable: 👻 FANTASMA — X días sin respuesta Y sin
aperturas → la mesa lo marca sola y sugiere el mensaje de cierre honroso
(~24% de deals "van dark"; nadie lo confiesa — detectarlo pasivo quita la
vergüenza). Razón verificada: si marcó "muy caro" pero el cliente nunca llegó
a la sección de precios, la razón se marca sospechosa (razones con evidencia).

Mapeo de compatibilidad → radar_feedback (NO se rompe ActividadScore):
- en_cita / decidiendo / objecion_precio / pidio_cambios → con_interes
- descartada (cualquier razón) → sin_interes
- no_contesta / fantasma → NO mapean (no son información sobre el interés)
Los botones 👍/👎 del Radar SE QUEDAN; ambas superficies escriben al mismo dato.

## 2. SELECCIÓN Y ORDEN DE FILAS (spec técnica, validada con Manuel)

- Universo: estado IN (enviada,vista), suspendida=0, total>0, COALESCE(vendedor_id,usuario_id)=asesor.
- Tiers por ciclo real (Radar::ciclo_venta, Radar.php:2100): T1 edad≤p75 (en
  ventana), T2 p75<edad≤2·p75 (cerrándose), T3 MILAGROS edad>2·p75 pero bucket
  HOT con radar_bucket_at reciente (ahora-o-nunca, pisan todo). Cap mesa: 25.
- UNA fila por cotización (dedup; "dormida" es badge, no fila).
- Descartadas fuera + auto-resurrección por bucket_transitions post-descarte.
- Limpieza en lote: banner si ≥10 fuera de ventana sin calor, con evidencia
  real ("tu empresa ha cerrado N ventas y ninguna tardó más de M días") —
  M = GREATEST(max_histórico, 2·p75). Suspende con el flujo existente.
- Orden: sin-estado primero → tier (T3, T1, T2) → calor ($PRIO del Radar) →
  monto DESC (el monto ordena DENTRO del calor, nunca encima).
- Empresa con <3 ventas (ciclo['auto']=false): p75 efectivo 30d, sin T3 ni
  limpieza — sin evidencia, el sistema calla.

## 3. MODELO DE DATOS

- Tabla NUEVA `mesa_estados` INSERT-ONLY (sin UNIQUE — la historia completa se
  conserva; lección del ON DUPLICATE de radar_feedback que la borra):
  id, cotizacion_id, usuario_id, empresa_id, estado VARCHAR(30), razon
  VARCHAR(30) NULL (descarte), bucket_snapshot VARCHAR(40), created_at.
  Índices: (cotizacion_id,created_at), (usuario_id,created_at),
  (empresa_id,created_at). estado en VARCHAR, no ENUM (taxonomía ampliable).
- Estado actual: helper último-por-grupo (MAX(id) por cotización), no VIEW.
- Escritura doble: mesa_estados (historia) + upsert radar_feedback (proyección
  compatible) reusando guards de api/radar_feedback.php:30-53.
- Acciones que NO requieren declaración (se detectan solas): editada/enviada
  vía cotizacion_log (usuario_id IS NOT NULL), abono, suspender. El contador
  de frescura SOLO se resetea con contacto real o comportamiento del cliente —
  nunca con ediciones internas triviales (falla documentada de Pipedrive).

## 4. MOTOR DE SUGERENCIA POR FILA

`core/MesaSugerencias.php` (pura, patrón DiagnosticoTips). Precedencia:
estado declarado > bucket > ventana. El ARQUETIPO del asesor (DiagnosticoTips,
método público nuevo `arquetipo()`) modula CÓMO, nunca QUÉ.

Matriz comprimida: 4 grupos de calor × estados × 6 familias de técnica
(cierre_mecanico=rematador_ausente+meseta; permiso_de_no=cultivador+teatro;
ritmo_hoy=sin_ritmo+desconectado+presente_pasivo; rescate_tibios=cerrador_
desperdiciado+pipeline_frio+sordo+una_pierna; volumen=francotirador+cerrador_
solitario+sembrador+bajo_caudal; cobro_limpio=cierre_falso+regalador+
engagement_flojo+motor_completo) + ~20 overrides redactados a mano.
Reglas de voz heredadas: directa, cero horóscopo, máximo un número, guion
mecánico solo a quien le falta técnica. El mensaje copiable del playbook por
bucket aparece al expandir la sugerencia.

Ejemplos aprobados en sesión: rematador_ausente × decidiendo → "No le
preguntes qué decidió — asume: 'le tengo lugar esta semana, ¿qué día le
acomoda?'"; regalador × objeción_precio → "cambia la estructura de pago, no
el número"; milagro → "Esta la dabas por muerta y la está viendo AHORA. Es
ahora o se va." (la urgencia pisa la modulación).

## 5. UI

- FRANJA comprimida junto al score (dashboard/index.php, insertar ~:854,
  FUERA del gate termometro_visible): "▸ Mesa: 16 en juego · $1.9M · 5 sin
  estado" (rojo si >0, con 'hace Xd' del más viejo). Colapso en localStorage.
- TABLA expandida: Cliente/título · Monto · Calor (badge $BM/rbadge del Radar
  — mismo lenguaje visual) · Ciclo ("día 9 de ~7", rojo si excede) · Última
  señal (cliente) / última acción (asesor) · Estado (chips tap-eables,
  corregible = INSERT nuevo) · Sugerencia (1 línea, expande detalle+mensaje) ·
  Acciones (👍/👎 del Radar si aplican gates, Ver, Suspender).
- POST /api/mesa/estado con X-CSRF-Token (patrón radarFb). Cada tap DEVUELVE
  algo al asesor al instante: próxima ventana ya agendada + playbook (regla
  anti-"data entry para el jefe": 71% de reps lo resienten; Salesforce: solo
  28% de la semana se vende).
- Mobile: cards apiladas; SIN item nuevo en bottom nav (vive en Inicio).
- Flujo tips→mesa: el 4º bloque del diagnóstico termina en "→ Tu mesa tiene N
  pendientes"; la mesa NUNCA gateada por leer tips.

## 6. ADMIN / DUEÑO

- Línea por vendedor en el leaderboard: "Mesa: 16 · $1.9M · 5 sin estado (el
  más viejo hace 4d)" + tap expande filas ofensoras.
- Jerarquía anti-fatiga (investigada): (1) push inmediato SOLO dinero/crítico
  — máx 1-2/día, dedupe 24h; (2) DIGEST diario un solo push de 3 líneas
  ("4 calientes sin trabajar, 2 citas donde el cliente no volvió a abrir,
  1 descartada revivió"); (3) dashboard = tendencias. v1: dispara al abrir el
  dashboard del admin (throttle 24h, sin cron nuevo); cuando exista el cron
  de resumen 8am, se muda ahí.
- Umbral X de "sin estado": auto-derivado del ciclo — X = clamp(ceil(mediana/2), 1, 7)
  días; hot sin desenlace vence a las 48h de entrar a caliente. Solo T1/T3
  despiertan al dueño. Toggle en notif_config ('mesa_alerta').
- Regla de reporte: SIEMPRE doble columna "lo que el asesor declaró" vs "lo
  que el cliente hizo" — esa distinción ES el producto (76% de usuarios de
  CRM admite que <50% de sus datos son precisos — Validity 2025).

## 7. CAPACIDADES SOLO-COTIZACLOUD (el pitch, con evidencia)

Nadie ha unido los dos mundos: los de inspección (Gong/Clari) auditan contra
actividad del VENDEDOR (enterprise, inglés, $$$); los proposal-trackers
(DocSend/Proposify/PandaDoc) ven al COMPRADOR pero mueren en "te avisamos que
lo abrió". CotizaCloud tiene ambas mitades en la misma BD:
1. Auditoría desenlace vs conducta del cliente final (el CRM que no acepta
   mentiras piadosas). 2. Resurrección automática de descartadas. 3. Prioridad
   por ventana de ciclo REAL por empresa (Proposify publica que la mediana de
   cierre es 51.4h — como benchmark; nadie lo opera por cliente). 4. Pipeline
   de captura cero real (Spiro se llena del email del vendedor; Leadsales
   arrastra chats a mano). 5. Cita en riesgo (declarativo × conductual).
   6. Coaching por sección+desenlace. 7. Score de calibración del asesor (% de
   declaraciones que el comportamiento confirmó — FUTURO, va con el ajuste de
   valores del score). 8. Razones de pérdida con evidencia adjunta.

## 8. ERRORES DE LA COMPETENCIA QUE ESTE DISEÑO EVITA (resumen)

Un tap nunca formulario · estados inequívocos (anti-Zoho) · pocos estados
(anti-100-lost-reasons) · el estancamiento SUBE la fila y entra al digest, no
solo la pinta (anti-rotting-decorativo de Pipedrive: no notifica, se resetea
con notas internas, ignora citas futuras) · push solo dinero, resto digest ·
razón de pérdida dropdown corto · desenlace ≠ etapa (la etapa la infiere el
Radar) · doble columna declarado/observado · no competir por ser el inbox de
WhatsApp (71% de PyMEs MX venden ahí; el cierre vive en WhatsApp, CotizaCloud
pide el desenlace en un tap y vigila por el slug — división de trabajo que
ningún competidor LATAM tiene).

## 9. IMPLEMENTACIÓN (orden y esfuerzo, ~30-36h)

migración add_mesa_estados.sql (0.5h) → core/Mesa.php armar/estados/resumen
(6-8h; validar: Manuel debe dar ~16 filas) → UI lectura (8-10h) → endpoint
estado + razones (2.5h) → MesaSugerencias (6-8h, mitad redacción) → limpieza
en lote (2h) → admin/push digest (2.5h) → exponer arquetipo() (0.5h) → max en
ciclo_venta (0.5h) → rutas (0.2h).
NO SE TOCA: pesos/queries del score, motor del Radar, botones 👍/👎,
playbook-data, .cpanel.yml. El ajuste de valores del score con los nuevos
estados = fase posterior (decisión CEO).

## 10. DECISIONES PENDIENTES DEL CEO

1. Taxonomía final: aprobar los 7 desenlaces + 5 razones + fantasma implícito
   (sustituyen a los 5 provisionales).
2. ¿Lite ve la franja? (funciona técnicamente; palanca de upsell).
3. Umbral X = ceil(mediana/2) clamp 1-7 — validar contra instinto OnTime.
4. Caps: mesa 25 filas, milagros 3.
5. Citas: ¿pedir fecha exacta en el tap "en cita" (un tap más) o solo
   hoy/mañana/semana? (la auditoría de cita-en-riesgo la necesita).

## ADDENDUM v2 — Decisiones finales del CEO (8 jul 2026)

1. TAXONOMÍA v2 — "CICLO DEL TOQUE" (sustituye §1): la venta es un loop, no
   estados. Cada toque = misma hoja de 3 renglones: (a) ¿Qué pasó? no
   respondió / hablamos (canal 📞💬🤝 — "fui a la cita" ES un toque en
   persona); (b) ¿Quedaron en algo CONCRETO? sí / propuse-y-no-quiso / no
   quedamos en nada — "COMPROMISO" reemplaza a "cita" (universal por giro:
   cita, visita, llamada de decisión, propuesta ajustada); (c) ¿Cómo lo ves?
   chips MULTI: decidiendo / objeción precio / pidió cambios / en el aire /
   ya no le interesa. SIN FECHAS de cita (decisión CEO) — la frescura
   uniforme del ciclo maneja el vencimiento ("¿ya pasó? ¿cómo fue?").
2. INTEGRACIONES: semáforo declarado-vs-observado (4 cuadrantes: avanza /
   OREJAS FELICES / MILAGRO / archivo); briefing pre-toque (Radar arma la
   llamada); hoja precargada por señales del Radar; arquetipos confirmados
   con evidencia conductual (tasa de avance por vendedor: conversaciones →
   compromiso); juicio con línea de tiempo (insert-only).
3. Lite SÍ ve la franja.
4. Umbral alerta admin X = MEDIANA del ciclo de la empresa (piso 1 día).
5. ROLLOUT: v1 SOLO ADMIN/SUPERADMIN — el CEO evalúa y pule antes de
   soltarla a asesores. Hoy no existen asesores-admin: margen seguro.
6. Caps por defecto: mesa 25, milagros 3 (afinables).

### Estado de implementación
- HECHO v1 (solo lectura, gated admin): core/Mesa.php (motor de filas:
  tiers por ciclo real + línea de limpieza con evidencia + resurrección de
  descartadas + orden milagros→sin-postura→tier→calor→monto + caps) +
  modules/dashboard/_mesa.php (franja <details> + selector de vendedor +
  tabla + banner limpieza informativo) + include en dashboard/index.php:856.
  Postura v1 = radar_feedback existente. Sin migración aún.
- SIGUIENTE: migración mesa_estados (toques insert-only) + hoja de toque
  (endpoint POST /api/mesa/toque) + MesaSugerencias con arquetipos +
  semáforo declarado-vs-observado + push digest admin (X=mediana) +
  suspensión en lote.
