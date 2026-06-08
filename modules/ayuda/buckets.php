<?php
// ============================================================
//  CotizaApp — modules/ayuda/buckets.php
//  GET /ayuda/buckets
//  Página estática: explica los badges (buckets) del Radar que
//  aparecen en la lista de cotizaciones. Pensada para el plan
//  Lite (que no tiene el módulo Radar completo) pero visible a todos.
//
//  Los colores de cada punto coinciden con el badge real que se
//  pinta en la lista de cotizaciones (ver modules/cotizaciones/lista.php
//  → función radar_badge). Si cambian ahí, actualizar aquí.
// ============================================================

defined('COTIZAAPP') or die;

$page_title = 'Interpretación del radar';
ob_start();
?>
<style>
.ib-wrap{max-width:760px;margin:0 auto}
.ib-h1{font:800 22px var(--body);letter-spacing:-.02em;margin:0 0 6px;display:flex;align-items:center;gap:10px}
.ib-sub{font:400 14px var(--body);color:var(--t3);margin:0 0 22px;line-height:1.6}
.ib-sec{font:700 12px var(--body);text-transform:uppercase;letter-spacing:.05em;color:var(--t3);margin:26px 0 12px;padding-bottom:7px;border-bottom:1px solid var(--border)}
.ib-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.04);display:flex;gap:14px;align-items:flex-start}
.ib-dot{width:14px;height:14px;border-radius:50%;flex-shrink:0;margin-top:3px}
.ib-name{font:700 15px var(--body);margin:0 0 3px}
.ib-desc{font:400 13.5px var(--body);color:var(--t2);line-height:1.55;margin:0}
.ib-do{font:600 12.5px var(--body);color:var(--g);margin-top:5px}
.ib-tip{background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--r);padding:14px 18px;margin:18px 0;font:400 13px var(--body);color:#1e40af;line-height:1.6}
</style>

<div class="ib-wrap">

  <h1 class="ib-h1">🎯 Interpretación del radar</h1>
  <p class="ib-sub">En tu lista de cotizaciones, cada una muestra una etiqueta de color (un "bucket") con el 👁 número de visitas. El radar lee cómo el cliente interactúa con tu cotización y la clasifica para decirte <b>qué tan interesado está</b> y <b>qué hacer</b>. Aquí está qué significa cada etiqueta.</p>

  <!-- ─── CALIENTES ─────────────────────────────────────────── -->
  <div class="ib-sec">🔥 Calientes — actúa hoy</div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#991b1b"></div>
    <div>
      <div class="ib-name">🔥 On Fire</div>
      <p class="ib-desc">Máxima actividad. El cliente está revisando tu cotización <b>intensamente y ahora mismo</b>: leyó todo, revisó precios y volvió más de una vez. Es el momento más caliente.</p>
      <div class="ib-do">→ Háblale YA, mientras la tiene abierta.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#c2410c"></div>
    <div>
      <div class="ib-name">⏰ Cierre inminente</div>
      <p class="ib-desc">Señales fuertes de decisión en las últimas horas: se fue, lo pensó y <b>regresó</b>. Ese patrón es de los indicadores más fuertes de que está por decidir.</p>
      <div class="ib-do">→ Dale el empujón final: resuelve su última duda y cierra.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#92400e"></div>
    <div>
      <div class="ib-name">📈 Probable cierre</div>
      <p class="ib-desc">Tu lista #1. Agrupa las cotizaciones más calientes (varias señales juntas + lectura real). El badge "Motivo" te dice qué señal la activó.</p>
      <div class="ib-do">→ Contáctalos HOY, son tus mejores oportunidades.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#1d4ed8"></div>
    <div>
      <div class="ib-name">👥 Multi-persona</div>
      <p class="ib-desc">Varias personas (o dispositivos distintos) están viendo la misma cotización. Tu contacto ya la compartió para <b>decidir entre varios</b>: pareja, socio, familia. Señal muy fuerte de compra.</p>
      <div class="ib-do">→ Facilita material fácil de compartir (garantía, tiempos) y ofrece una llamada con todos.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#6d28d9"></div>
    <div>
      <div class="ib-name">🔥 Re-enganche caliente</div>
      <p class="ib-desc">Regresó tras varios días <b>y revisó los precios</b>. Casi siempre ya tiene otras cotizaciones para comparar — estás en la mesa de decisión final.</p>
      <div class="ib-do">→ Aparece rápido, con seguridad. El primero que responde con claridad suele cerrar.</div>
    </div>
  </div>

  <!-- ─── EN EVALUACIÓN ─────────────────────────────────────── -->
  <div class="ib-sec">🔍 En evaluación — señal media</div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#92400e"></div>
    <div>
      <div class="ib-name">💸 Validando precio</div>
      <p class="ib-desc">Está enfocado en los números: revisa el total, vuelve al precio, compara. Pero el freno real casi nunca es el precio — es el miedo a equivocarse.</p>
      <div class="ib-do">→ No bajes el precio de inmediato. Diagnostica qué lo frena y transmite certeza.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#92400e"></div>
    <div>
      <div class="ib-name">💎 Alto importe</div>
      <p class="ib-desc">Cotización por encima de lo habitual en tu negocio. Ticket alto = decisión más cuidadosa, más lenta y con más personas involucradas. No es un cliente difícil, es uno prudente.</p>
      <div class="ib-do">→ Da confianza: referencias, casos similares, garantías. No presiones cierre rápido.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#92400e"></div>
    <div>
      <div class="ib-name">📖 Lectura comprometida</div>
      <p class="ib-desc">Leyó tu cotización con <b>atención real</b>: tiempo de lectura alto y enfoque en el contenido. No es un vistazo rápido — está evaluando en serio.</p>
      <div class="ib-do">→ Ábrele la puerta a su duda técnica o de detalle, ahí está la clave.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#166534"></div>
    <div>
      <div class="ib-name">📊 Predicción alta</div>
      <p class="ib-desc">El perfil de esta cotización <b>se parece al de las que sí cierran</b> (buen ajuste + dentro de la ventana de tiempo donde ocurren la mayoría de los cierres). Es probabilidad, no certeza.</p>
      <div class="ib-do">→ Inicia contacto proactivo y dale seguimiento prioritario para confirmarlo.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#92400e"></div>
    <div>
      <div class="ib-name">🔍 Decisión activa</div>
      <p class="ib-desc">Varias visitas en pocos días, con horas entre ellas: <b>va y viene</b>, evaluando en serio, probablemente consultando internamente. El proceso avanza aunque no lo veas.</p>
      <div class="ib-do">→ Ponte como recurso disponible, sin presión. Pregunta si hay alguien más decidiendo.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#1d4ed8"></div>
    <div>
      <div class="ib-name">🔬 Revisión profunda</div>
      <p class="ib-desc">Tiempo de lectura muy alto: leyó todo con detalle. Perfil analítico que decide despacio — no es indecisión, es su método.</p>
      <div class="ib-do">→ Respeta su ritmo. Ofrécele evidencia visual: fotos, ejemplos, proyectos similares.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#64748b"></div>
    <div>
      <div class="ib-name">👀 Vistas múltiples</div>
      <p class="ib-desc">Varias visitas pero sin profundidad. Hay interés real, pero todavía sin señal clara de decisión. Etapa temprana.</p>
      <div class="ib-do">→ Mensaje corto, una sola pregunta. Busca que responda algo, no cerrar aún.</div>
    </div>
  </div>

  <!-- ─── REGRESÓ ───────────────────────────────────────────── -->
  <div class="ib-sec">🔄 Regresó después de días</div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#6d28d9"></div>
    <div>
      <div class="ib-name">🔄 Re-enganche</div>
      <p class="ib-desc">Volvió tras un tiempo ausente con señal de interés, pero <b>sin enfocarse en el precio</b>. El proyecto volvió a tomar relevancia para él.</p>
      <div class="ib-do">→ Retoma con naturalidad. Pregunta si algo cambió desde la última vez.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#6d28d9"></div>
    <div>
      <div class="ib-name">↩️ Regreso</div>
      <p class="ib-desc">Estuvo ausente 4+ días y volvió a revisar en las últimas horas. Bucket con buen volumen de cierre — ya tuvo tiempo de comparar con otros.</p>
      <div class="ib-do">→ Aparece rápido, con seguridad. Dale una razón concreta para avanzar.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#6d28d9"></div>
    <div>
      <div class="ib-name">💫 Revivió</div>
      <p class="ib-desc">Inactivo 30+ días y volvió a revisar inesperadamente. Algo cambió en su situación (se le cayó otro proveedor, mejoró su liquidez). Esa ventana vale mucho y se cierra rápido.</p>
      <div class="ib-do">→ Retómalo como continuidad, sin reclamar el tiempo que pasó. Pregunta si cambió algo.</div>
    </div>
  </div>

  <!-- ─── ENFRIÁNDOSE ───────────────────────────────────────── -->
  <div class="ib-sec">❄️ Enfriándose / dudando</div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#64748b"></div>
    <div>
      <div class="ib-name">🤔 Hesitación</div>
      <p class="ib-desc">Tuvo actividad días atrás y algo lo frenó — pero no se fue. Casi siempre hay una duda concreta detrás, no un rechazo.</p>
      <div class="ib-do">→ Pregunta directo qué lo frena. Quita presión de tiempo antes de empujar.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#64748b"></div>
    <div>
      <div class="ib-name">♻️ Sobre-análisis</div>
      <p class="ib-desc">Muchas visitas durante semanas sin decidir. Ya tiene toda la información — el bloqueo es otro: miedo a equivocarse o alguien más que debe aprobar. Más información no ayuda.</p>
      <div class="ib-do">→ Invita la objeción directamente. Crea urgencia real con agenda o capacidad, no artificial.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#94a3b8"></div>
    <div>
      <div class="ib-name">⚖️ Comparando</div>
      <p class="ib-desc">Dos o más dispositivos/redes distintas en poco tiempo: la cotización se está comparando o se compartió. Puede haber competencia activa sobre la mesa.</p>
      <div class="ib-do">→ Ayuda a comparar bien (garantía, tiempos, proceso). El que aclara mejor gana, no el más barato.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#94a3b8"></div>
    <div>
      <div class="ib-name">❄️ Enfriándose</div>
      <p class="ib-desc">Tuvo historial activo pero lleva días sin revisarla. Se está alejando — no siempre es rechazo, a veces es agenda ocupada o perdió el hilo.</p>
      <div class="ib-do">→ Reaparece con algo útil y concreto, sin urgencia falsa. Máximo 3 contactos sin respuesta.</div>
    </div>
  </div>

  <!-- ─── SIN ABRIR ─────────────────────────────────────────── -->
  <div class="ib-sec">✉️ Sin abrir</div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#dc2626"></div>
    <div>
      <div class="ib-name">✉️ Sin abrir</div>
      <p class="ib-desc">El cliente todavía no abre tu cotización. No hay señal de interés aún — puede ser problema de canal (no le llegó, spam, link roto). No es rechazo, es desconocimiento.</p>
      <div class="ib-do">→ Asegúrate de que le llegó: reenvíale el link por WhatsApp con un mensaje corto.</div>
    </div>
  </div>

  <div class="ib-tip">💡 El número junto al 👁 son las <b>veces que el cliente abrió</b> la cotización. Más visitas = más interés. Combínalo con la etiqueta para saber a quién llamar primero.</div>

</div>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
