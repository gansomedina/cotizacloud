# Motor de Sugerencias de la Mesa de Trabajo — Especificación v2
(Diseñado 9 julio 2026. Estado: PENDIENTE DE APROBACIÓN del CEO antes de implementar.)

Ver conversación de diseño. Especificación entregada por agente Fable 5 tras leer
Mesa.php, DiagnosticoTips.php (20 arquetipos reales), ActividadScore.php,
Radar.php (20 buckets), playbook-data.php, api/mesa_estado.php y
docs/mesa_trabajo_diseno.md.

## Regla maestra de recencia (resuelve el bug de mezclas incoherentes)
- T_c = timestamp de la declaración de contacto más reciente.
- Declaración de compromiso/postura es VIGENTE si su ts >= T_c; si no, es HISTÓRICA
  (contexto, jamás se narra en presente).
- Choque entre vigentes: manda la más reciente; empate → jerarquía contacto > compromiso > postura.
- Guard duro: las plantillas de acción viven en un registro indexado por clase.
  La clase DOM-C (no contesta vigente) solo tiene plantillas de canal/mensaje/reintento —
  los verbos de conversación NO existen en su registro. Incoherencia imposible por construcción.

## Capa 1 — Clases de equivalencia de la mezcla (60 mezclas vivas + overlays)
Rama C1 (no_contesta vigente): EVASIVA, EVASIVA con lectura, EVASIVA asumida,
PLANTÓN (compromiso histórico roto), RECHAZO+EVASIÓN, CONTRADICTORIA-a (se degrada a histórica),
EVASIVA con K3 redundante (caso del CEO: sin_compromiso junto a no_contesta se ABSORBE).
Rama C2 (hablamos): PACTO (→ PACTO_CUMPLIENDO / PACTO_HUMO según reabrió),
PACTO CONDICIONADO A PRECIO / A CAMBIOS (con SLA del asesor), CONTRADICTORIA-b,
RECHAZO DIAGNOSTICADO (precio/cambios), RECHAZO TENSO, RECHAZO SIN PORQUÉ,
NADA CONCRETO, DECIDIENDO DECLARADO, FRENO NOMBRADO SIN PACTO, EN EL AIRE HONESTO,
TOQUE INCOMPLETO.
Rama C0 (sin contacto): VIRGEN, JUICIO SIN TOQUE, COMPROMISO IMPLÍCITO (se imputa C2).
Overlays (orden): REVIVIDA > MILAGRO > FANTASMA (2+ intentos nc + 7d+ sin abrir)
> OREJAS FELICES (postura optimista + dormida + no reabrió tras declarar).

## Capa 2 — Realidad del Radar
Estados: R_LEYENDO (vistas_24h), R_REABRIO (vs ult_decl_at), R_CALIENTE, R_VOLVIO,
R_DORMIDA, R_ENFRIANDO (momentum down), R_NUNCA. Cada uno con fragmento de evidencia
que CITA el dato ("la abrió 3 veces hoy", "lleva 6d sin volver").
Matiz por bucket (los 20): onfire=cerrar hoy sin reexplicar; validando_precio=estructura
de pago nunca defender número; multi_persona=reunión con todos + garantía escrita;
sobre_analisis=invitar la objeción jamás más info; alto_importe=paciencia+garantía;
re_enganche_caliente=aparecer YA; vistas_multiples=mensaje ultra corto; etc.
(tabla completa en el resultado del agente / transcript).

## Capa 3 — Contexto de negocio
1. ratio_ticket = total / AVG(ventas 180d, fallback all-time, null si <5 ventas).
   >=2 → "vale por N de tus ventas típicas" + prioridad+2; 1.2-2 → prio+1 sin número.
2. Ventana: mediana<edad<=p75 "a media ventana"; edad>p75 "saliendo de tu ventana ~Nd".
3. Cadencia no_contesta (intentos_nc desde último hablamos): 1→mañana mismo canal;
   2→3d; >=3→cambiar canal obligatorio. es_hot comprime a hoy.
4. visitas>=6 sin cierre → empuja diagnóstico.
5. SLA de cambios: sin edición/reenvío 24h tras pidio_cambios → acción AL ASESOR.

## Capa 4 — Arquetipo (modulación por slots, nunca sustitución)
Nuevo método público DiagnosticoTips::arquetipo($s,$ctx) que reusa
_metricas/_estados/_validar/_arquetipo (6 líneas, cero lógica nueva).
Slots: ritmo (prefijo), tecnica_cierre, cobro_descuento, confronta_juicio, foco_señal.
Mapeo: regalador→cero descuento; cierre_falso→anticipo el mismo día;
rematador_ausente→doble alternativa; teatro/cultivador→permiso de no;
sin_ritmo/desconectado/presente_pasivo→prefijo "Tu toque del día es este:";
sordo_a_senales→"la señal dura horas"; una_pierna→agendar con fecha exacta;
sembrador→"remata esta antes de mandar una nueva"; motor_completo/muestra_chica→sin modulación.
Si la acción no acepta el slot del arquetipo, la modulación se omite.
MILAGRO y REVIVIDA saltan la Capa 4 (la urgencia pisa la modulación).

## Composición
frase = [evidencia/contraste] + [acción única concreta] + [modulación].
Presupuesto de números: gana UNO (vistas_hoy 90 > días_sin_volver 80 > intentos 70 >
día_ciclo 60 > ratio_ticket 50); los demás se degradan a palabra. <=~180 chars, una línea.

## Cambios de código requeridos
1. NUEVO core/MesaSugerencias.php — sugerir($ctx) puro (patrón DiagnosticoTips, sin BD).
2. core/Mesa.php:
   - SELECT principal: + c.radar_senales; exponer ultima_vista_at y radar_bucket_at en el row.
   - Query $me: + m.razon.
   - Batch 1 quote_sessions (vistas_24h, vistas_7d, ips_7d; es_interno=0 y filtro ghost
     NOT(visible_ms<200 AND scroll_max<35)).
   - Batch 2 historia de contacto (mesa_estados area='contacto' ORDER BY id) → intentos_nc.
   - Batch 3 ticket_empresa (AVG ventas no canceladas 180d, fallback, cache estático).
   - Batch 4 usuario_score → DiagnosticoTips::arquetipo() (fallback 'muestra_chica').
   - Reemplazar sugerencia() por MesaSugerencias::sugerir($ctx) y ABSORBER el closure
     de 'alerta' (PACTO_HUMO y OREJAS FELICES lo generan mejor).
3. core/DiagnosticoTips.php — método público arquetipo() (6 líneas).
4. api/mesa_estado.php — (adición nuestra, no del agente) armar $ctx de UNA cotización
   tras el INSERT (merge en memoria de la declaración recién posteada) y devolver
   {"ok":true,"sugerencia":"..."} para swap instantáneo en el JS.
5. Bug preexistente confirmado: Mesa.php:296 `$es_hot_txt = true` hace disparar la rama
   caliente siempre — desaparece al reemplazar sugerencia().

## 30 frases ejemplo compuestas
(Las 30 están en el transcript del agente; muestra representativa:)
1. no_contesta+sin_compromiso+leyendo → "No te contesta pero abrió la cotización 2 veces
   hoy — te lee aunque te evite: WhatsApp corto con una pregunta que se conteste con sí o no."
7. PACTO_HUMO → "Quedaron en algo hace 3d y no ha vuelto a abrirla ni una vez —
   confírmalo hoy por WhatsApp o era humo."
8. PACTO+validando_precio+regalador → "Hay pacto pero regresó a clavarse en los totales —
   llega con la estructura de pago armada, no muevas el número: cero descuento."
10. cambios sin reenviar → "Te pidió cambios hace 2d y la cotización sigue igual —
   la versión nueva sale HOY; el interés se muere esperándote."
14. rechazo+onfire → "Te dijo que no al compromiso pero el Radar la trae On Fire desde
   ayer — el freno no es interés: pregúntale de frente qué le falta."
15. nada_concreto+multi_persona → "Hablaron sin quedar en nada y hay 3 dispositivos
   viéndola — tu contacto no decide solo: proponle la reunión con todos y garantía por escrito."
17. OREJAS FELICES → "Dices que está decidiendo pero lleva 8d sin abrirla — eso no es
   decidir, es enfriarse: fuérzale una definición esta semana."
25. FANTASMA → "Varios intentos sin respuesta y ni una apertura en 10d — mándale el
   cierre honroso y descártala con razón: deja de cargarla."
26. alto importe → "No te contesta pero vale el doble de tu venta típica y más gente la
   está viendo — no la quemes a llamadas: mensaje a tu contacto ofreciendo resolver dudas de todos."
