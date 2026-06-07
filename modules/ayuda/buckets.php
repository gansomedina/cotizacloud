<?php
// ============================================================
//  CotizaApp — modules/ayuda/buckets.php
//  GET /ayuda/buckets
//  Página estática: explica los badges (buckets) del Radar que
//  aparecen en la lista de cotizaciones. Pensada para el plan
//  Lite (que no tiene el módulo Radar completo) pero visible a todos.
// ============================================================

defined('COTIZAAPP') or die;

$page_title = 'Interpretación de buckets';
ob_start();
?>
<style>
.ib-wrap{max-width:760px;margin:0 auto}
.ib-h1{font:800 22px var(--body);letter-spacing:-.02em;margin:0 0 6px;display:flex;align-items:center;gap:10px}
.ib-sub{font:400 14px var(--body);color:var(--t3);margin:0 0 22px;line-height:1.6}
.ib-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.04);display:flex;gap:14px;align-items:flex-start}
.ib-dot{width:14px;height:14px;border-radius:50%;flex-shrink:0;margin-top:3px}
.ib-name{font:700 15px var(--body);margin:0 0 3px}
.ib-desc{font:400 13.5px var(--body);color:var(--t2);line-height:1.55;margin:0}
.ib-do{font:600 12.5px var(--body);color:var(--g);margin-top:5px}
.ib-tip{background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--r);padding:14px 18px;margin:18px 0;font:400 13px var(--body);color:#1e40af;line-height:1.6}
</style>

<div class="ib-wrap">

  <h1 class="ib-h1">🎯 Interpretación de buckets</h1>
  <p class="ib-sub">En tu lista de cotizaciones, cada una muestra una etiqueta de color (un "bucket") con el 👁 número de visitas. El bucket te dice <b>qué tan interesado está el cliente</b> y <b>qué hacer</b>. Aquí está qué significa cada uno.</p>

  <div class="ib-card">
    <div class="ib-dot" style="background:#991b1b"></div>
    <div>
      <div class="ib-name">🔥 On Fire</div>
      <p class="ib-desc">Máxima actividad. El cliente está revisando tu cotización <b>intensamente y ahora mismo</b>. Es el momento más caliente.</p>
      <div class="ib-do">→ Háblale YA, mientras la tiene abierta.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#c2410c"></div>
    <div>
      <div class="ib-name">⏰ Cierre inminente</div>
      <p class="ib-desc">Señales fuertes de que está a punto de decidir: volvió varias veces, leyó a fondo, se detuvo en lo importante.</p>
      <div class="ib-do">→ Dale el empujón final: resuelve su última duda y cierra.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#92400e"></div>
    <div>
      <div class="ib-name">📈 Probable cierre</div>
      <p class="ib-desc">Tu lista #1: clientes calientes con intención real de compra (varias señales juntas + lectura real). Son a los que más conviene dar seguimiento.</p>
      <div class="ib-do">→ Contáctalos HOY, son tus mejores oportunidades.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#1d4ed8"></div>
    <div>
      <div class="ib-name">💸 Validando precio</div>
      <p class="ib-desc">Está enfocado en los números: revisa el total, vuelve al precio, compara. El dinero es su foco.</p>
      <div class="ib-do">→ No bajes el precio de inmediato — transmite certeza y valor.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#1d4ed8"></div>
    <div>
      <div class="ib-name">👥 Decisión compartida</div>
      <p class="ib-desc">Varias personas (o dispositivos) están viendo la misma cotización. Lo están consultando entre varios para decidir.</p>
      <div class="ib-do">→ Facilita material para que convenzan al que decide.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#6d28d9"></div>
    <div>
      <div class="ib-name">🔄 Revivió / Regreso</div>
      <p class="ib-desc">Volvió a abrir la cotización tras días o semanas sin actividad. El interés se reactivó.</p>
      <div class="ib-do">→ Retómalo, pregúntale si tiene nuevas dudas.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#2563eb"></div>
    <div>
      <div class="ib-name">❄️ Enfriándose</div>
      <p class="ib-desc">Tuvo actividad pero no ha vuelto en un par de días. El interés está bajando.</p>
      <div class="ib-do">→ Reactívalo antes de que se enfríe del todo (un mensaje, una promo).</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#d97706"></div>
    <div>
      <div class="ib-name">🤔 Hesitación / Sobre-análisis</div>
      <p class="ib-desc">Muchas visitas y pausas largas: lo está pensando demasiado, posible fricción con el precio o una duda sin resolver.</p>
      <div class="ib-do">→ Detecta qué lo frena y quítale el miedo a decidir.</div>
    </div>
  </div>

  <div class="ib-card">
    <div class="ib-dot" style="background:#dc2626"></div>
    <div>
      <div class="ib-name">✉️ Sin abrir</div>
      <p class="ib-desc">El cliente todavía no abre tu cotización. No hay señal de interés aún.</p>
      <div class="ib-do">→ Asegúrate de que le llegó: reenvíale el link por WhatsApp.</div>
    </div>
  </div>

  <div class="ib-tip">💡 El número junto al 👁 son las <b>veces que el cliente abrió</b> la cotización. Más visitas = más interés. Combínalo con el bucket para saber a quién llamar primero.</div>

</div>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
