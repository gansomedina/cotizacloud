<?php
// ============================================================
//  CotizaApp — modules/ayuda/index.php
//  GET /ayuda
//  Centro de ayuda y manual de usuario
// ============================================================

defined('COTIZAAPP') or die;

$page_title = 'Centro de Ayuda';
$empresa = Auth::empresa();
ob_start();
?>
<style>
/* ── Layout ── */
.ay-wrap{display:flex;gap:0;min-height:calc(100vh - 80px)}
.ay-nav{width:240px;flex-shrink:0;background:var(--white);border-right:1px solid var(--border);position:sticky;top:0;height:100vh;overflow-y:auto;padding:20px 0}
.ay-body{flex:1;padding:32px 40px;max-width:800px}

/* ── Nav ── */
.ay-nav-title{font:800 14px var(--body);color:var(--t1);padding:0 20px 16px;letter-spacing:-.02em}
.ay-nav-section{font:700 10px var(--body);text-transform:uppercase;letter-spacing:.1em;color:var(--t3);padding:16px 20px 6px}
.ay-nav a{display:flex;align-items:center;gap:8px;padding:8px 20px;font:500 13px var(--body);color:var(--t2);text-decoration:none;transition:all .12s;border-left:3px solid transparent}
.ay-nav a:hover{background:var(--bg);color:var(--t1)}
.ay-nav a.active{background:var(--g-bg);color:var(--g);border-left-color:var(--g);font-weight:700}
.ay-nav a .ay-ico{width:18px;text-align:center;font-size:14px}

/* ── Content ── */
.ay-hero{margin-bottom:32px}
.ay-hero h1{font:800 28px var(--body);letter-spacing:-.03em;margin:0 0 8px}
.ay-hero p{font:400 15px var(--body);color:var(--t3);line-height:1.6;margin:0}
.ay-section{display:none;animation:ayFade .2s ease}
.ay-section.active{display:block}
@keyframes ayFade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

.ay-h2{font:800 22px var(--body);letter-spacing:-.02em;margin:0 0 6px;display:flex;align-items:center;gap:10px}
.ay-subtitle{font:400 14px var(--body);color:var(--t3);margin:0 0 24px;line-height:1.5}

.ay-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:20px 24px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.ay-card h3{font:700 15px var(--body);margin:0 0 8px;display:flex;align-items:center;gap:8px}
.ay-card p,.ay-card li{font:400 14px var(--body);color:var(--t2);line-height:1.7;margin:0}
.ay-card ul{margin:8px 0 0;padding-left:20px}
.ay-card li{margin-bottom:6px}

.ay-steps{counter-reset:step}
.ay-step{position:relative;padding:16px 20px 16px 60px;background:var(--white);border:1px solid var(--border);border-radius:var(--r);margin-bottom:10px}
.ay-step::before{counter-increment:step;content:counter(step);position:absolute;left:18px;top:16px;width:28px;height:28px;border-radius:50%;background:var(--g);color:#fff;font:700 14px var(--num);display:flex;align-items:center;justify-content:center}
.ay-step h4{font:700 14px var(--body);margin:0 0 4px}
.ay-step p{font:400 13px var(--body);color:var(--t3);margin:0;line-height:1.6}

/* ── Form ── */
.ay-field{margin-bottom:16px}
.ay-field label{display:block;font:600 13px var(--body);color:var(--t1);margin-bottom:6px}
.ay-field input[type="text"],.ay-field textarea{width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:var(--r-sm);font:400 14px var(--body);color:var(--t1);background:var(--bg);transition:border-color .15s;box-sizing:border-box;resize:vertical}
.ay-field input[type="text"]:focus,.ay-field textarea:focus{outline:none;border-color:var(--g);box-shadow:0 0 0 3px rgba(16,185,129,.1)}
.ay-upload-area{border:2px dashed var(--border);border-radius:var(--r);padding:0;cursor:pointer;transition:border-color .15s;overflow:hidden;position:relative}
.ay-upload-area:hover,.ay-upload-area.dragover{border-color:var(--g);background:var(--g-bg)}
.ay-upload-placeholder{display:flex;flex-direction:column;align-items:center;gap:6px;padding:28px 20px;color:var(--t3);font:500 13px var(--body)}
.ay-upload-preview{position:relative;text-align:center;padding:12px}
.ay-upload-preview img{max-width:100%;max-height:220px;border-radius:var(--r-sm);object-fit:contain}
.ay-upload-remove{position:absolute;top:8px;right:8px;width:28px;height:28px;border-radius:50%;border:none;background:rgba(0,0,0,.6);color:#fff;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1}
.ay-btn-submit{display:inline-flex;align-items:center;gap:8px;padding:10px 28px;background:var(--g);color:#fff;border:none;border-radius:var(--r-sm);font:600 14px var(--body);cursor:pointer;transition:opacity .15s}
.ay-btn-submit:hover{opacity:.9}
.ay-btn-submit:disabled{opacity:.5;cursor:not-allowed}

.ay-tip{background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--r);padding:14px 18px;margin:16px 0;font:400 13px var(--body);color:#1e40af;line-height:1.6}
.ay-tip::before{content:'';display:inline-block;width:14px;height:14px;margin-right:6px;vertical-align:middle;background:currentColor;-webkit-mask:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z'/%3E%3C/svg%3E") center/contain no-repeat;mask:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z'/%3E%3C/svg%3E") center/contain no-repeat}

.ay-warn{background:#fffbeb;border:1px solid #fde68a;border-radius:var(--r);padding:14px 18px;margin:16px 0;font:400 13px var(--body);color:#92400e;line-height:1.6}
.ay-warn::before{content:'';display:inline-block;width:14px;height:14px;margin-right:6px;vertical-align:middle;background:currentColor;-webkit-mask:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z'/%3E%3Cline x1='12' y1='9' x2='12' y2='13'/%3E%3Cline x1='12' y1='17' x2='12.01' y2='17'/%3E%3C/svg%3E") center/contain no-repeat;mask:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z'/%3E%3Cline x1='12' y1='9' x2='12' y2='13'/%3E%3Cline x1='12' y1='17' x2='12.01' y2='17'/%3E%3C/svg%3E") center/contain no-repeat}

.ay-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin:16px 0}
.ay-feature{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px;text-align:center}
.ay-feature .ay-feat-ico{font-size:28px;margin-bottom:8px}
.ay-feature h4{font:700 13px var(--body);margin:0 0 4px}
.ay-feature p{font:400 12px var(--body);color:var(--t3);margin:0;line-height:1.5}

.ay-shortcut{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;font:600 12px var(--num);color:var(--t2);margin:2px}

.ay-faq{border:1px solid var(--border);border-radius:var(--r);margin-bottom:8px;overflow:hidden}
.ay-faq summary{padding:14px 18px;font:600 14px var(--body);cursor:pointer;background:var(--white);list-style:none;display:flex;align-items:center;gap:8px}
.ay-faq summary::before{content:'▸';font-size:12px;transition:transform .2s}
.ay-faq[open] summary::before{transform:rotate(90deg)}
.ay-faq summary:hover{background:var(--bg)}
.ay-faq .ay-faq-body{padding:0 18px 16px;font:400 14px var(--body);color:var(--t2);line-height:1.7}

/* ── Mobile ── */
@media(max-width:768px){
  .ay-wrap{flex-direction:column}
  .ay-nav{width:100%;height:auto;position:relative;border-right:none;border-bottom:1px solid var(--border);padding:12px 0;overflow-x:auto;white-space:nowrap;display:flex;gap:0;flex-wrap:nowrap}
  .ay-nav-title,.ay-nav-section{display:none}
  .ay-nav a{padding:8px 14px;border-left:none;border-bottom:2px solid transparent;white-space:nowrap;font-size:12px}
  .ay-nav a.active{border-left:none;border-bottom-color:var(--g)}
  .ay-body{padding:20px 16px}
  .ay-hero h1{font-size:22px}
  .ay-step{padding-left:50px}
  .ay-step::before{left:12px;width:24px;height:24px;font-size:12px}
}
</style>

<div class="ay-wrap">

<!-- ── Navegación lateral ── -->
<nav class="ay-nav" id="ayNav">
  <div class="ay-nav-title">Centro de Ayuda</div>

  <div class="ay-nav-section">Inicio</div>
  <a href="#calibra" onclick="ayTab('calibra',this)" style="font-weight:800;color:var(--g)"><span class="ay-ico">🎯</span> Calibra tus armas</a>
  <a href="#escudo" onclick="ayTab('escudo',this)" style="font-weight:800;color:var(--g)"><span class="ay-ico">🛡️</span> Escudo</a>
  <a href="#inicio" class="active" onclick="ayTab('inicio',this)"><span class="ay-ico">🏠</span> Bienvenida</a>
  <a href="#primeros-pasos" onclick="ayTab('primeros-pasos',this)"><span class="ay-ico">🚀</span> Primeros pasos</a>
  <a href="#soporte" onclick="ayTab('soporte',this)"><span class="ay-ico"><?= ico('mail',14) ?></span> Enviar ticket</a>

  <div class="ay-nav-section">Módulos</div>
  <a href="#dashboard" onclick="ayTab('dashboard',this)"><span class="ay-ico"><?= ico('chart',14) ?></span> Dashboard</a>
  <a href="#clientes" onclick="ayTab('clientes',this)"><span class="ay-ico"><?= ico('eye',14) ?></span> Clientes</a>
  <a href="#cotizaciones" onclick="ayTab('cotizaciones',this)"><span class="ay-ico"><?= ico('file',14) ?></span> Cotizaciones</a>
  <a href="#ventas" onclick="ayTab('ventas',this)"><span class="ay-ico"><?= ico('money',14) ?></span> Ventas</a>
  <a href="#radar" onclick="ayTab('radar',this)"><span class="ay-ico"><?= ico('target',14) ?></span> Radar</a>
  <a href="#termometro" onclick="ayTab('termometro',this)"><span class="ay-ico">🌡️</span> Termómetro</a>
  <a href="#costos" onclick="ayTab('costos',this)"><span class="ay-ico"><?= ico('chart',14) ?></span> Costos</a>
  <a href="#reportes" onclick="ayTab('reportes',this)"><span class="ay-ico"><?= ico('file',14) ?></span> Reportes</a>

  <div class="ay-nav-section">Admin</div>
  <a href="#configuracion" onclick="ayTab('configuracion',this)"><span class="ay-ico"><?= ico('target',14) ?></span> Configuración</a>
  <a href="#faq" onclick="ayTab('faq',this)"><span class="ay-ico"><?= ico('bulb',14) ?></span> Preguntas frecuentes</a>
</nav>

<!-- ── Contenido ── -->
<div class="ay-body">

<!-- ═══════════════════════════════════════════════════════ -->
<!--  CALIBRA TUS ARMAS (lo más importante)                 -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-calibra">
  <h2 class="ay-h2">🎯 Calibra tus armas de venta</h2>
  <p class="ay-subtitle">Esto es lo más importante de todo el sistema. Tienes 4 armas. Las <b>3 primeras las usas tú</b> (configurar, proteger, trabajar). La <b>4ª (el Termómetro) es el resultado</b>: el sistema mide solo qué tan bien usaste las otras 3 y te pone una calificación. Si cuidas las 3, la 4ª sube sola.</p>

  <div class="ay-tip">Léelo completo aunque no sepas nada de computadoras. Cada arma tiene su pestaña con el detalle; aquí está lo esencial.</div>

  <div class="ay-card" style="border-left:4px solid var(--g)">
    <h3>1️⃣ Configura bien tu empresa <span style="color:var(--t3);font-weight:500">— la base de todo</span></h3>
    <p>Es lo PRIMERO. Sube tu <b>logo</b>, tus <b>datos fiscales</b>, y arma tu <b>catálogo</b> de productos o servicios con sus precios. Una empresa bien configurada hace que tus cotizaciones se vean profesionales y las generes en segundos. Si esto está mal, todo lo demás sale mal.</p>
    <p style="margin-top:8px"><a href="#configuracion" onclick="event.preventDefault();document.querySelector('.ay-nav a[href=\'#configuracion\']').click()" style="color:var(--g);font-weight:600">→ Ver cómo configurar (pestaña Configuración)</a></p>
  </div>

  <div class="ay-card" style="border-left:4px solid var(--g)">
    <h3>2️⃣ Cuida el Escudo <span style="color:var(--t3);font-weight:500">— para que el Radar no te mienta</span></h3>
    <p>Abre las cotizaciones de tus clientes <b>SOLO desde tu dispositivo con la sesión iniciada</b>. Si las abres desde varios celulares o navegadores donde no entraste, el sistema creerá que el cliente está interesado… cuando en realidad eras tú. Eso ensucia todo lo demás.</p>
    <p style="margin-top:8px"><a href="#escudo" onclick="event.preventDefault();document.querySelector('.ay-nav a[href=\'#escudo\']').click()" style="color:var(--g);font-weight:600">→ Ver la regla de oro del Escudo</a></p>
  </div>

  <div class="ay-card" style="border-left:4px solid var(--g)">
    <h3>3️⃣ Trabaja con el Radar todos los días <span class="ay-shortcut">Pro y Business</span> <span style="color:var(--t3);font-weight:500">— aquí es donde vendes</span></h3>
    <p>El Radar es tu herramienta de trabajo diaria. Cada mañana:</p>
    <ul>
      <li>Entra al <b>Radar</b> y empieza por el <b>Resumen "Probable cierre"</b>: ahí están tus clientes más calientes, los que debes contactar HOY.</li>
      <li>Lee el botón <b>"?"</b> de cada cotización para saber qué está pasando con ese cliente y qué hacer.</li>
      <li>Después de hablar con un cliente, márcalo con la <b>manita 👍 (con interés)</b> o <b>👎 (sin interés)</b>, según cómo te fue de verdad. Esto le enseña al sistema a leer a tus clientes.</li>
      <li>Cierra las ventas y, muy importante, <b>anota el pago</b>.</li>
    </ul>
    <p style="margin-top:8px"><a href="#radar" onclick="event.preventDefault();document.querySelector('.ay-nav a[href=\'#radar\']').click()" style="color:var(--g);font-weight:600">→ Ver cómo se usa el Radar y las manitas 👍/👎</a></p>
  </div>

  <div class="ay-card" style="border-left:4px solid var(--g)">
    <h3>4️⃣ El Termómetro <span class="ay-shortcut">Business</span> <span style="color:var(--t3);font-weight:500">— tu calificación, calculada por el sistema</span></h3>
    <p>El Termómetro <b>no es algo que "haces"</b>: es un <b>análisis automático de tu trabajo como vendedor</b>. El sistema revisa TODO — cuántas cotizaciones envías, si las abren, si das seguimiento, si marcas las manitas en el Radar, cada cuánto cierras, si cobras, si das muchos descuentos, cómo va tu cartera de clientes — y con todo eso <b>calcula un número del 0 al 100</b> que es tu calificación.</p>
    <p style="margin-top:8px">Es tu <b>espejo</b>: te dice de forma objetiva qué tan bien estás vendiendo y en qué estás fallando, sin adivinar. <b>No sube con clics sueltos: sube cuando haces bien las 3 armas de arriba.</b> Si configuras bien, cuidas el Escudo y trabajas el Radar a diario, el Termómetro sube solo.</p>
    <p style="margin-top:8px"><a href="#termometro" onclick="event.preventDefault();document.querySelector('.ay-nav a[href=\'#termometro\']').click()" style="color:var(--g);font-weight:600">→ Ver cómo se calcula y cómo subirlo (pestaña Termómetro)</a></p>
  </div>

  <div class="ay-warn"><b>En resumen:</b> configura bien tu empresa → cuida el Escudo → trabaja el Radar todos los días (contacta, marca 👍/👎, cierra y cobra). Haz eso y el Termómetro —tu calificación— sube solo. El sistema hace el trabajo pesado: tú solo sigues las señales y cierras.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  ESCUDO (lo más importante)                            -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-escudo">
  <h2 class="ay-h2">🛡️ El Escudo — lo más importante</h2>
  <p class="ay-subtitle">Léelo con calma. Si entiendes esto, todo el sistema trabaja a tu favor. Si lo ignoras, el Radar te va a engañar y vas a perder tiempo y puntos.</p>

  <div class="ay-card" style="border:2px solid var(--g)">
    <h3>¿Qué es el Escudo?</h3>
    <p>El Radar funciona porque sabe quién abre cada cotización: <b>el cliente o tú</b>. El <b>Escudo</b> es lo que le dice al sistema "esta vez fui yo, el vendedor, no el cliente", para que <b>tus propias vistas no se cuenten como si fuera el cliente.</b></p>
    <p style="margin-top:10px">Si no cuidas el Escudo, el Radar te <b>miente</b>: te dirá que un cliente está súper interesado cuando en realidad eras tú revisando tu propia cotización. Perderías tiempo llamándole a quien no toca, y se llena de señales falsas.</p>
  </div>

  <div class="ay-card">
    <h3>📍 ¿Dónde se activa y cómo sé que está funcionando?</h3>
    <p>Lo bueno: <b>el Escudo se activa casi solo, con que inicies sesión</b> en CotizaCloud en ese dispositivo. No tienes que prender nada complicado. Aun así, sigue estos pasos para asegurarte:</p>
    <div class="ay-steps">
      <div class="ay-step">
        <h4>Inicia sesión en el dispositivo y navegador que usas</h4>
        <p>Entra a CotizaCloud con tu usuario y contraseña en la computadora o celular donde trabajas. Con eso ese dispositivo ya queda reconocido como tuyo.</p>
      </div>
      <div class="ay-step">
        <h4>Dale click al aviso verde “Activar Escudo”</h4>
        <p>La primera vez en un navegador nuevo, en tu pantalla de <b>Inicio</b> aparece un aviso verde que dice <b>“Activa tu Escudo Radar”</b> con un botón <b>“Activar Escudo”</b>. Dale click. Eso confirma que ese navegador es tu equipo de trabajo.</p>
      </div>
      <div class="ay-step">
        <h4>Confirma en la tarjeta “Escudo Radar — Activo”</h4>
        <p>En tu <b>Inicio</b>, busca la tarjeta que dice <b>“Escudo Radar — Activo”</b>. Ahí aparece la lista de tus dispositivos protegidos, cada uno con una palomita ✓. Si el dispositivo que estás usando aparece ahí, ¡listo, estás protegido!</p>
      </div>
    </div>
    <p style="margin-top:10px"><b>¿Vas a usar otro equipo o navegador?</b> Repite lo mismo: inicia sesión ahí y dale “Activar Escudo”. Si revisas una cotización desde un dispositivo que NO está en tu lista, inicia sesión de inmediato desde él.</p>
  </div>

  <div class="ay-card">
    <h3>✅ La regla de oro (muy fácil)</h3>
    <ul>
      <li>Abre las cotizaciones de tus clientes <b>SOLO desde el dispositivo y el navegador donde tienes tu sesión iniciada</b> en CotizaCloud (donde entras a trabajar normalmente).</li>
      <li><b>Mantente con la sesión abierta.</b> Así el sistema te reconoce y no confunde tus vistas con las del cliente.</li>
    </ul>
  </div>

  <div class="ay-card">
    <h3>🚫 Lo que NO debes hacer</h3>
    <ul>
      <li>Abrir la misma cotización desde tu <b>celular</b>, luego desde <b>otra computadora</b>, luego desde <b>otro navegador</b> donde NO tienes tu sesión.</li>
      <li>Mandarte el link a ti mismo y abrirlo por curiosidad desde varios lados.</li>
    </ul>
    <p style="margin-top:8px">Cada vez que abres desde un lugar distinto donde el sistema no te reconoce, <b>cuenta como si fuera otra persona (el cliente) viendo la cotización</b>. Resultado: el Radar se prende en falso, te muestra clientes "calientes" que en realidad eras tú, y tu termómetro también se ensucia.</p>
  </div>

  <div class="ay-warn">¿Necesitas enseñarle la cotización a un cliente en persona desde tu celular? Está bien, pero hazlo <b>con tu sesión iniciada</b> en ese celular. Si abres desde un equipo "prestado" o sin sesión, el sistema lo contará como una vista del cliente.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  BIENVENIDA                                            -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section active" id="sec-inicio">
  <div class="ay-hero">
    <h1>Bienvenido a cotiza.cloud</h1>
    <p>La plataforma todo-en-uno para crear cotizaciones profesionales, cerrar más ventas y entender el comportamiento de tus clientes en tiempo real.</p>
  </div>

  <div class="ay-grid">
    <div class="ay-feature">
      <div class="ay-feat-ico"><?= ico('file',28,'#2563eb') ?></div>
      <h4>Cotizaciones</h4>
      <p>Crea y envía cotizaciones profesionales en minutos</p>
    </div>
    <div class="ay-feature">
      <div class="ay-feat-ico"><?= ico('target',28,'#16a34a') ?></div>
      <h4>Radar</h4>
      <p>Sabe cuándo tu cliente revisa la cotización y qué le interesa</p>
    </div>
    <div class="ay-feature">
      <div class="ay-feat-ico"><?= ico('money',28,'#d97706') ?></div>
      <h4>Ventas</h4>
      <p>Convierte cotizaciones en ventas y controla pagos</p>
    </div>
    <div class="ay-feature">
      <div class="ay-feat-ico"><?= ico('chart',28,'#7c3aed') ?></div>
      <h4>Reportes</h4>
      <p>Visualiza el rendimiento de tu negocio con datos reales</p>
    </div>
  </div>

  <div class="ay-card">
    <h3>¿Cómo está organizado el sistema?</h3>
    <p>cotiza.cloud se divide en módulos. Cada uno tiene una función específica en tu flujo de ventas:</p>
    <ul>
      <li><b>Dashboard</b> — Vista ejecutiva de tu negocio</li>
      <li><b>Clientes</b> — Base de datos de tus clientes</li>
      <li><b>Cotizaciones</b> — Crear, enviar y dar seguimiento a propuestas</li>
      <li><b>Ventas</b> — Gestionar ventas cerradas, pagos y recibos</li>
      <li><b>Radar</b> — Inteligencia comercial: detecta quién va a comprar</li>
      <li><b>Costos</b> — Controla gastos asociados a cada venta</li>
      <li><b>Reportes</b> — Análisis de rendimiento por período</li>
      <li><b>Configuración</b> — Ajusta tu empresa, catálogo, usuarios y más</li>
    </ul>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  PRIMEROS PASOS                                        -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-primeros-pasos">
  <h2 class="ay-h2">🚀 Primeros pasos</h2>
  <p class="ay-subtitle">Sigue estos pasos para configurar tu cuenta y enviar tu primera cotización.</p>

  <div class="ay-steps">
    <div class="ay-step">
      <h4>Configura tu empresa</h4>
      <p>Ve a <b>Configuración → Empresa</b> y llena el nombre, logo, datos fiscales y condiciones comerciales. Esto aparece en todas tus cotizaciones.</p>
    </div>
    <div class="ay-step">
      <h4>Crea tu catálogo de productos</h4>
      <p>En <b>Configuración → Catálogo</b>, agrega tus productos o servicios con nombre, descripción y precio base. Podrás ajustar precios al momento de cotizar.</p>
    </div>
    <div class="ay-step">
      <h4>Agrega tu primer cliente</h4>
      <p>Ve a <b>Clientes → Nuevo cliente</b>. Solo necesitas nombre y teléfono o email para empezar.</p>
    </div>
    <div class="ay-step">
      <h4>Crea y envía tu primera cotización</h4>
      <p>En <b>Cotizaciones → Nueva cotización</b>, selecciona el cliente, agrega productos del catálogo o líneas personalizadas, y presiona <b>Enviar</b>. El cliente recibe un link profesional.</p>
    </div>
    <div class="ay-step">
      <h4>Revisa el Radar</h4>
      <p>Una vez enviada, ve a <b>Radar</b>. Ahí verás cuándo el cliente abre la cotización, qué secciones revisa y cuánto tiempo dedica. Esto te dice cuándo es el mejor momento para dar seguimiento. <i>(En el plan Lite, las visitas y el interés se ven dentro de Cotizaciones.)</i></p>
    </div>
  </div>

  <div class="ay-tip">Una vez que el cliente acepta, la cotización se convierte en venta automáticamente. Desde ahí puedes registrar pagos y emitir recibos.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  SOLICITAR LICENCIA (oculto en app nativa — Apple 3.1.1) -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php $trial = trial_info(EMPRESA_ID);
$is_native_app_ayuda = str_contains($_SERVER['HTTP_USER_AGENT'] ?? '', 'CotizaCloud');
if (!$is_native_app_ayuda): ?>
<div class="ay-section" id="sec-licencia">
  <h2 class="ay-h2">Activar licencia</h2>
  <?php if ($trial['es_free']): ?>
    <p class="ay-subtitle">Activa tu plan Lite, Pro o Business para crear cotizaciones ilimitadas. Selecciona el plan y la duracion deseada y te contactaremos con la liga de cobro.</p>
  <?php elseif (!empty($trial['trial_activo']) && $trial['por_vencer']): ?>
    <p class="ay-subtitle">Tu prueba de <?= $trial['plan_label'] ?> termina en <strong><?= $trial['dias_restantes'] ?> dias</strong> (<?= date('d/m/Y', strtotime($trial['plan_vence'])) ?>). Activa tu plan en Configuración → Suscripción para no perderlo.</p>
<?php elseif ($trial['por_vencer']): ?>
    <p class="ay-subtitle">Tu licencia <?= $trial['plan_label'] ?> vence en <strong><?= $trial['dias_restantes'] ?> dias</strong> (<?= date('d/m/Y', strtotime($trial['plan_vence'])) ?>). Renuevala aqui para no perder acceso.</p>
  <?php else: ?>
    <p class="ay-subtitle">Tu plan <?= $trial['plan_label'] ?> esta activo<?= $trial['plan_vence'] ? ' hasta el ' . date('d/m/Y', strtotime($trial['plan_vence'])) : '' ?>. Si necesitas renovar o extender, usa el formulario.</p>
  <?php endif; ?>

  <div class="ay-card">
    <form action="/ayuda/ticket" method="POST" id="licenciaForm">
      <?= csrf_field() ?>
      <input type="hidden" name="titulo" value="Solicitud de licencia">

      <div class="ay-field">
        <label for="lic-plan">Plan deseado</label>
        <select id="lic-plan" name="plan_lic" style="width:100%;padding:10px 12px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font:400 14px var(--body);background:var(--white);color:var(--text)">
          <?php $ay_precios = MercadoPago::precios(); // fuente única — no divergir de nuevo ?>
          <option value="Lite">Lite — $<?= number_format($ay_precios['lite']['mensual'], 0) ?>/mes</option>
          <option value="Pro">Pro — $<?= number_format($ay_precios['pro']['mensual'], 0) ?>/mes</option>
          <option value="Business">Business — $<?= number_format($ay_precios['business']['mensual'], 0) ?>/mes (con demo y capacitación)</option>
        </select>
      </div>

      <div class="ay-field">
        <label for="lic-duracion">Duracion deseada</label>
        <select id="lic-duracion" name="duracion_lic" style="width:100%;padding:10px 12px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font:400 14px var(--body);background:var(--white);color:var(--text)">
          <option value="1 mes">1 mes</option>
          <option value="3 meses">3 meses</option>
          <option value="6 meses">6 meses</option>
          <option value="1 año">1 año (20% descuento)</option>
        </select>
      </div>

      <div class="ay-field">
        <label for="lic-msg">Mensaje (opcional)</label>
        <textarea id="lic-msg" name="descripcion" rows="3" placeholder="Informacion adicional..." style="width:100%;padding:10px 12px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font:400 14px var(--body);color:var(--text);resize:vertical"></textarea>
      </div>

      <button type="submit" class="ay-btn-submit" onclick="
        var plan = document.getElementById('lic-plan').value;
        var dur = document.getElementById('lic-duracion').value;
        var desc = document.getElementById('lic-msg');
        var tit = this.form.querySelector('[name=titulo]');
        tit.value = 'Solicitud de licencia ' + plan + ' — ' + dur;
        if (!desc.value.trim()) desc.value = 'Solicitud de activacion de licencia ' + plan + '.\nDuracion solicitada: ' + dur;
        else desc.value = 'Solicitud de activacion de licencia ' + plan + '.\nDuracion solicitada: ' + dur + '\n\nMensaje:\n' + desc.value;
      ">Solicitar activacion</button>

      <p style="font-size:12px;color:var(--t3);margin-top:12px;text-align:center">Seras contactado a la brevedad con la liga de cobro para activar tu licencia.</p>
    </form>
  </div>
</div>
<?php endif; // !$is_native_app_ayuda ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  ENVIAR TICKET DE SOPORTE                              -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-soporte">
  <h2 class="ay-h2">🎫 Enviar ticket de soporte</h2>
  <p class="ay-subtitle">¿Tienes un problema o necesitas ayuda? Descríbelo y nuestro equipo te contactará.</p>

  <div class="ay-card">
    <form action="/ayuda/ticket" method="POST" enctype="multipart/form-data" id="ticketForm">
      <?= csrf_field() ?>

      <div class="ay-field">
        <label for="tk-titulo">Título del problema</label>
        <input type="text" id="tk-titulo" name="titulo" required maxlength="255" placeholder="Ej: No puedo enviar cotización al cliente">
      </div>

      <div class="ay-field">
        <label for="tk-desc">Descripción</label>
        <textarea id="tk-desc" name="descripcion" required rows="5" placeholder="Describe el problema con el mayor detalle posible: qué intentabas hacer, qué pasó y qué esperabas que pasara."></textarea>
      </div>

      <div class="ay-field">
        <label for="tk-img">Captura de pantalla <span style="color:var(--t3);font-weight:400">(opcional)</span></label>
        <div class="ay-upload-area" id="uploadArea">
          <input type="file" id="tk-img" name="imagen" accept="image/*" style="display:none">
          <div class="ay-upload-placeholder" id="uploadPlaceholder">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span>Haz clic o arrastra una imagen aquí</span>
            <span style="font-size:11px;color:var(--t3)">JPG, PNG o GIF — Máx <?= MAX_UPLOAD_MB ?>MB</span>
          </div>
          <div class="ay-upload-preview" id="uploadPreview" style="display:none">
            <img id="previewImg" src="" alt="Preview">
            <button type="button" class="ay-upload-remove" onclick="removeImage()">&times;</button>
          </div>
        </div>
      </div>

      <button type="submit" class="ay-btn-submit" id="btnSubmit">Enviar ticket</button>
    </form>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  DASHBOARD                                             -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-dashboard">
  <h2 class="ay-h2"><?= ico('chart',18,'#2563eb') ?> Inicio (Dashboard)</h2>
  <p class="ay-subtitle">Es la pantalla de inicio: la foto de cómo va tu negocio <b>hoy</b>. Esta guía recorre <b>cada bloque de arriba abajo</b> para que sepas qué significa cada número. Si eres asesor, ves solo lo tuyo; si eres admin, ves todo el equipo.</p>

  <div class="ay-card">
    <h3>Filtro de período (arriba a la derecha)</h3>
    <p>Casi todo el dashboard se mide por un <b>período</b> que tú eliges: <b>Este mes</b>, <b>Mes anterior</b>, <b>Últimos 30 días</b>, <b>Últimos 90 días</b> o <b>Este año</b>. Cámbialo y todos los números se recalculan. Varios bloques comparan contra el período anterior para mostrarte si vas <b>↑ subiendo</b> o <b>↓ bajando</b>.</p>
  </div>

  <!-- ── ESCUDO ── -->
  <div class="ay-card">
    <h3>Tarjeta del Escudo Radar</h3>
    <p>Arriba puede aparecer la tarjeta <b>"Escudo Radar — Activo"</b> con la lista de tus dispositivos protegidos (tu compu, tu celular). Sus visitas a las cotizaciones <b>no se cuentan como cliente</b>. <b>Regla de oro:</b> si revisas una cotización desde un dispositivo que no está en la lista, inicia sesión ahí de inmediato para protegerlo. Lee el apartado <b>🛡️ Escudo</b> del menú Inicio para entenderlo a fondo.</p>
  </div>

  <!-- ── TERMÓMETRO + LEADERBOARD ── -->
  <div class="ay-card">
    <h3>Tu Termómetro y el ranking del equipo</h3>
    <p>Si tu empresa lo tiene activado, verás tu <b>score del 0 al 100</b> con tu nivel (Excepcional, Buen ritmo, Puede mejorar, Necesita atención) y una flecha de tendencia (↑ mejorando, → estable, ↓ bajando). Debajo hay 5 barritas — <b>Activación, Engagement, Seguimiento, Radar Health y Conversión</b> — y un diagnóstico que te dice qué mejorar (toca "ver más").</p>
    <ul>
      <li>Si eres <b>nuevo</b>, primero verás "Analizando tu actividad": el score se activa tras unos días de uso.</li>
      <li>Si eres <b>admin</b>, además aparece el <b>Ranking del equipo</b> con el score de cada vendedor, sus abiertas, cierres, dormidas, y un botón <b>"?"</b> que explica a fondo cómo se calcula.</li>
    </ul>
    <div class="ay-tip">Hay un apartado completo del <b>🌡️ Termómetro</b> en este manual. Aquí solo lo ves resumido en tu inicio.</div>
  </div>

  <!-- ── KPIs ── -->
  <div class="ay-card">
    <h3>Resumen financiero (las 4 tarjetas de colores)</h3>
    <ul>
      <li><b>Ventas del período</b> — Cuánto vendiste y en cuántas ventas, con la comparación contra el período anterior.</li>
      <li><b>Cobrado</b> — El dinero que realmente <b>entró</b> en el período (suma de los pagos/recibos), y cuántos pagos fueron.</li>
      <li><b>Por cobrar</b> — Lo que te <b>deben</b> en total (todas las ventas con saldo, sin importar la fecha) y cuántas ventas tienen saldo.</li>
      <li><b>Cotizaciones creadas</b> — Cuántas hiciste en el período, el monto total cotizado y si subiste o bajaste vs antes.</li>
    </ul>
  </div>

  <!-- ── VENTAS SIN PAGOS ── -->
  <div class="ay-card">
    <h3>Ventas sin pagos</h3>
    <p>Lista de ventas que <b>no tienen ningún abono</b> todavía, ordenadas de la más vieja a la más nueva. Te dice cuántos días llevan sin pago (en rojo si pasan de 14). Es tu lista de cobranza: tócalas para ir directo a registrar el pago.</p>
  </div>

  <!-- ── CONVERSIÓN ── -->
  <div class="ay-card">
    <h3>Métricas de conversión</h3>
    <ul>
      <li><b>Embudo del período</b> — Cuántas cotizaciones se <b>enviaron</b>, cuántas el cliente <b>abrió</b>, cuántas <b>aceptó</b> y cuántas <b>rechazó</b>, con su barra y porcentaje.</li>
      <li><b>Indicadores clave</b> — <b>Tasa de cierre</b> (% de enviadas que terminan en venta pagada), <b>ticket promedio</b>, <b>tiempo promedio de cierre</b> (días que tardan en aceptar), cotizaciones <b>rechazadas</b>, cotizaciones <b>sin abrir</b> y cuántas ventas llevaron descuento.</li>
    </ul>
  </div>

  <!-- ── ALERTAS ── -->
  <div class="ay-card">
    <h3>Alertas — lo que necesita tu atención</h3>
    <p>Cuatro listas de lo más reciente. Toca cualquier renglón para ir a la cotización o venta:</p>
    <ul>
      <li><b>✓ Aceptadas recientemente</b> — Las que el cliente aceptó en los últimos 14 días.</li>
      <li><b>✕ Rechazadas</b> — Las rechazadas en los últimos 14 días.</li>
      <li><b>⏰ Próximas a vencer</b> — Enviadas o vistas que vencen en 7 días o menos.</li>
      <li><b>✉️ Sin abrir</b> — Enviadas que el cliente aún no abre (marca <b>VENCIDA</b> si ya pasó su fecha). <b>Atiende estas rápido:</b> a los 5 días sin abrir golpean fuerte tu Termómetro.</li>
    </ul>
  </div>

  <!-- ── RADAR ── -->
  <div class="ay-card">
    <h3>Radar — oportunidades activas</h3>
    <p>Un resumen del Radar con los clientes más calientes, en 4 grupos: <b>Probable cierre</b>, <b>Cierre inminente</b>, <b>On Fire</b> y <b>Validando precio</b>. Cada renglón muestra el cliente, el monto y unos <b>puntos de calor</b> (entre más rojos, más interés). Tócalo para ir a la cotización y darle seguimiento. El Radar completo está en su propio menú.</p>
  </div>

  <!-- ── RECIBOS DEL DÍA ── -->
  <div class="ay-card">
    <h3>Recibos del día</h3>
    <p>Todos los pagos registrados <b>hoy</b> (y las cancelaciones del día), con cliente, folio, forma de pago y monto. Ideal para tu corte diario.</p>
  </div>

  <!-- ── ACTIVIDAD ── -->
  <div class="ay-card">
    <h3>Actividad del período</h3>
    <p>Dos tarjetas con el detalle fino:</p>
    <ul>
      <li><b>Cotizaciones</b> — Cuántas generaste, total cotizado, ticket promedio (del período y del año), cuántas cerraste, rechazadas y pendientes de respuesta.</li>
      <li><b>Ventas</b> — Cuántas, total vendido, ticket promedio, completamente pagadas, con saldo y total cobrado.</li>
    </ul>
  </div>

  <div class="ay-tip">Revisa tu inicio al arrancar el día: primero las <b>Ventas sin pagos</b> (cobranza), luego <b>Sin abrir</b> y <b>Próximas a vencer</b> (seguimiento), y por último el <b>Radar</b> para saber a quién llamar HOY.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  CLIENTES                                              -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-clientes">
  <h2 class="ay-h2">👥 Clientes</h2>
  <p class="ay-subtitle">Tu directorio. Cada cliente guarda sus datos de contacto y todo su historial de cotizaciones y ventas. Esta guía explica el <b>listado</b> y la <b>ficha</b> de cada cliente, opción por opción.</p>

  <!-- ── LISTADO ── -->
  <div class="ay-card">
    <h3>El listado de clientes</h3>
    <ul>
      <li><b>+ Nuevo cliente</b> — Abre un formulario con: nombre (obligatorio), teléfono (obligatorio), dirección (opcional) y una nota interna (opcional). Es la forma rápida de dar de alta a alguien sin salir de la pantalla.</li>
      <li><b>Buscar</b> — Escribe nombre, teléfono o email y la lista se filtra al instante.</li>
      <li><b>Ordenar</b> — Más reciente, Nombre A–Z, Mayor compra o Más cotizaciones.</li>
      <li>Cada renglón muestra el cliente, su teléfono, cuántas <b>cotizaciones</b> tiene, cuánto te ha <b>comprado</b> en total y su <b>saldo pendiente</b>. Tócalo para abrir su ficha.</li>
    </ul>
  </div>

  <!-- ── FICHA ── -->
  <div class="ay-card">
    <h3>La ficha del cliente</h3>
    <p>Al abrir un cliente ves todo lo suyo en una sola pantalla:</p>
    <ul>
      <li><b>Datos de contacto</b> — Nombre, teléfono (toca para llamar) y email (toca para escribir), más la fecha de registro, el asesor que lo dio de alta y la última actividad.</li>
      <li><b>Cotizaciones</b> — La lista completa de sus cotizaciones con folio, título, monto y estado. Tócalas para abrirlas.</li>
      <li><b>Ventas</b> — Sus ventas con monto, saldo pendiente y estado.</li>
      <li><b>Resumen (lado derecho)</b> — Número de cotizaciones, ventas, <b>tasa de conversión</b> (qué % de sus cotizaciones se volvieron venta), total comprado y saldo pendiente.</li>
    </ul>
  </div>

  <!-- ── ACCIONES ── -->
  <div class="ay-card">
    <h3>Acciones en la ficha</h3>
    <ul>
      <li><b>Editar cliente</b> — Actualiza nombre, teléfono, dirección y nota. <i>(Necesitas ser admin o tener el permiso de editar clientes.)</i></li>
      <li><b>+ Nueva cotización</b> — Crea una cotización ya ligada a este cliente, sin tener que volver a buscarlo.</li>
      <li><b>Eliminar cliente</b> — Solo aparece (para admin) si el cliente <b>no tiene ninguna cotización ni venta</b>. Así no se borra información ligada a tu historial.</li>
    </ul>
  </div>

  <div class="ay-tip">Registra siempre el <b>teléfono</b>: es el canal más efectivo para dar seguimiento. Y revisa la <b>tasa de conversión</b> de tus mejores clientes para detectar a quién vale la pena consentir.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  COTIZACIONES                                          -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-cotizaciones">
  <h2 class="ay-h2">📄 Cotizaciones</h2>
  <p class="ay-subtitle">El corazón del sistema. Aquí creas la propuesta, la mandas por un link y le das seguimiento. Esta guía explica <b>botón por botón, campo por campo</b> todo lo que puedes hacer al crear y al editar una cotización. No te saltes nada: cada opción está aquí por una razón.</p>

  <!-- ── ESTADOS ── -->
  <div class="ay-card">
    <h3>Los estados (en qué punto va cada cotización)</h3>
    <p>Cada cotización tiene una "etiqueta de color" que te dice en qué va. La verás en el listado y arriba del editor:</p>
    <ul>
      <li><span class="ay-shortcut">Enviada</span> Recién creada. Ya tiene su link listo para mandar, pero el cliente <b>todavía no la abre</b>. <i>(Toda cotización nueva nace en este estado.)</i></li>
      <li><span class="ay-shortcut">Vista</span> El cliente <b>ya la abrió</b>. Aquí arranca el Radar a rastrear su interés.</li>
      <li><span class="ay-shortcut">Aceptada</span> El cliente le dio "Aceptar" desde el link. ¡Felicidades!</li>
      <li><span class="ay-shortcut">Rechazada</span> El cliente le dio "Rechazar".</li>
      <li><span class="ay-shortcut">Vencida</span> Pasó la fecha de "Vence" sin que el cliente decidiera.</li>
      <li><span class="ay-shortcut">Convertida</span> Ya la pasaste a venta. Queda "congelada" (de solo lectura) para que la venta no le borre nada.</li>
      <li><span class="ay-shortcut">Borrador</span> Estado interno para cotizaciones que aún no se mandan.</li>
      <li><span class="ay-shortcut">Suspendida</span> Tú la pausaste a propósito. El cliente <b>ya no puede abrir el link</b> y deja de contar para el Termómetro.</li>
    </ul>
    <p style="margin-top:8px"><b>¿Cuándo puedo editar?</b> Solo mientras esté <b>Enviada</b>, <b>Vista</b> o <b>Borrador</b> (y no esté suspendida). Una vez <b>Aceptada</b>, <b>Rechazada</b> o <b>Convertida</b> queda de <b>solo lectura</b> para proteger lo que el cliente ya vio.</p>
  </div>

  <!-- ── CREAR: PASO A PASO ── -->
  <div class="ay-card">
    <h3>Crear una cotización nueva</h3>
    <p>Entra a <b>Cotizaciones → Nueva cotización</b>. Verás una pantalla dividida: a la izquierda armas la cotización, a la derecha (en computadora) están las opciones y el total. En celular, esas opciones aparecen abajo en secciones que se despliegan.</p>
    <div class="ay-steps">
      <div class="ay-step">
        <h4>Elige el cliente (obligatorio)</h4>
        <p>Toca <b>"Seleccionar cliente"</b>. Se abre una ventana con dos pestañas: <b>Clientes</b> (busca por nombre o teléfono) y <b>Nuevo cliente</b> (lo das de alta ahí mismo con nombre, teléfono y dirección, sin salir de la cotización). No puedes guardar sin cliente.</p>
      </div>
      <div class="ay-step">
        <h4>Pon el título y las fechas</h4>
        <p><b>Título</b> es obligatorio (ej. "Cocina integral para Sra. López"). La <b>Fecha</b> sale con el día de hoy y <b>Vence</b> se calcula solo según la vigencia que configuraste (por defecto 30 días). Puedes cambiarlas.</p>
      </div>
      <div class="ay-step">
        <h4>Agrega los artículos</h4>
        <p>Toca <b>"Agregar artículo"</b>: se abre tu catálogo, busca y toca el que quieras. ¿No está en el catálogo? Usa <b>"Ítem libre"</b> para escribirlo a mano. Cada artículo trae nombre, SKU, descripción, cantidad y precio.</p>
      </div>
      <div class="ay-step">
        <h4>Revisa el total y genera</h4>
        <p>El <b>Total</b> se calcula solo a la derecha conforme agregas cosas, cupones o descuentos. Cuando esté listo, toca <b>"Generar cotización"</b>. Te aparece una ventana para <b>copiar el link</b>, ver la cotización o editarla.</p>
      </div>
    </div>
    <div class="ay-warn">Plan Free: tienes un máximo de <b>25 cotizaciones en total</b>. Al llegar al límite (o al terminar tu prueba) el sistema te pide activar un plan. Lite, Pro y Business son ilimitadas.</div>
  </div>

  <!-- ── CADA ARTÍCULO POR DENTRO ── -->
  <div class="ay-card">
    <h3>Cada artículo, botón por botón</h3>
    <p>Cada artículo es una tarjeta. Dentro tiene:</p>
    <ul>
      <li><b>Flechas ▲ ▼</b> — Suben o bajan el artículo para cambiar el orden en que el cliente los ve.</li>
      <li><b>Nombre</b> — El nombre del producto o servicio (lo que ve el cliente).</li>
      <li><b>SKU</b> (opcional) — Tu clave o código interno. Si no lo usas, déjalo vacío.</li>
      <li><b>Descripción</b> (opcional) — Detalles, medidas, condiciones. Se ve en el link del cliente.</li>
      <li><b>Cantidad</b> y <b>Precio unit.</b> — El <b>Total</b> de la línea se multiplica solo.</li>
      <li><b>Botón ↗ "Mover a extra / Mover a principal"</b> — Convierte el artículo en un "extra" (o lo regresa a principal). Ver abajo qué son los extras.</li>
      <li><b>✕ Eliminar</b> — Quita el artículo de la cotización.</li>
    </ul>
    <div class="ay-tip">Si tu cuenta no tiene permiso para <b>editar precios</b>, el campo de precio se bloquea y toma el precio que tiene el artículo en el catálogo. Así nadie cambia precios sin autorización.</div>
  </div>

  <!-- ── EXTRAS (BUSINESS) ── -->
  <div class="ay-card">
    <h3>Artículos "Extra" <span class="ay-shortcut">Todos</span></h3>
    <p>Los <b>extras</b> son artículos opcionales que se muestran en una sección aparte (con borde ámbar) y con su <b>subtotal separado</b>. Sirven para ofrecer complementos sin inflar el precio principal ("agrégale esto si quieres"). Usa el botón <b>"Agregar extra"</b> o el botón ↗ de cualquier artículo.</p>
    <ul>
      <li>Los <b>cupones y descuentos NO aplican</b> sobre los extras: solo sobre los artículos principales.</li>
      <li>Disponible en todos los planes, para giros que no sean inmuebles.</li>
    </ul>
  </div>

  <!-- ── PANEL DE OPCIONES ── -->
  <div class="ay-card">
    <h3>El panel de opciones (lado derecho)</h3>
    <p>Aquí está todo lo que afina la cotización. En celular lo encuentras abajo, en secciones que se abren al tocarlas.</p>
    <ul>
      <li><b>Cupones</b> — Si creaste cupones en Configuración, aquí los activas con un toque. Aplican un % de descuento. <i>(Necesita permiso de descuentos.)</i></li>
      <li><b>Descuento automático</b> — Un descuento con <b>cronómetro</b>: el cliente ve "este precio vence en X días" sin necesidad de código. Prendes el interruptor, pones el <b>porcentaje</b> y los <b>días</b> que dura. Es ideal para crear urgencia. <i>(Necesita permiso de descuentos.)</i></li>
      <li><b>Totales</b> — Te muestra desglosado: Subtotal, lo que baja el Cupón, lo que baja el Descuento, el impuesto (IVA u otro, si tu empresa lo usa) y el <b>Total</b> final.</li>
      <li><b>Notas para el cliente</b> — Texto que <b>SÍ ve el cliente</b> en su link (condiciones, formas de pago, agradecimientos).</li>
      <li><b>Notas internas</b> — Texto <b>privado, solo para ti y tu equipo</b>. El cliente nunca lo ve.</li>
      <li><b>Vendedor asignado</b> — Si eres admin (o tienes permiso), puedes asignar la cotización a otro vendedor de tu equipo. Por defecto queda a tu nombre.</li>
      <li><b>Historial de visitas</b> — Vacío al crear. Se llena cuando el cliente abre el link (ver más abajo).</li>
    </ul>
  </div>

  <!-- ── EDITAR ── -->
  <div class="ay-card">
    <h3>Editar una cotización (la barra de arriba)</h3>
    <p>Al abrir una cotización ya creada (desde el listado o el Radar) entras al editor. Es la misma pantalla de crear, pero con los datos cargados y con una <b>barra de acciones arriba</b>:</p>
    <ul>
      <li><b>Guardar</b> — Guarda tus cambios. El sistema <b>recalcula los totales por seguridad</b> (no confía en lo que muestra la pantalla) y reemplaza las líneas. <i>(Solo aparece si la cotización es editable.)</i></li>
      <li><b>Copiar</b> — Copia el link del cliente al portapapeles, listo para pegar en WhatsApp o correo.</li>
      <li><b>Ver</b> — Abre el link <b>tal como lo ve el cliente</b>, en otra pestaña. <span class="ay-shortcut">🛡️</span> Recuerda: ábrelo solo desde tu equipo con tu sesión iniciada, para que el Escudo no lo cuente como visita de cliente.</li>
      <li><b>Suspender / Reactivar</b> — Pausa la cotización (el cliente deja de poder abrirla) o la vuelve a activar. Útil para clientes que no responden.</li>
      <li><b>Eliminar</b> — Borra la cotización para siempre. Solo lo ve el <b>administrador</b> y no se puede eliminar una ya convertida en venta.</li>
    </ul>
    <div class="ay-warn">Si editas una cotización que ya está <b>Vista</b>, los cambios se reflejan <b>al instante</b> en el link del cliente. Ten cuidado si sabes que la está revisando en ese momento.</div>
  </div>

  <!-- ── COMPARTIR ── -->
  <div class="ay-card">
    <h3>Mandarle la cotización al cliente</h3>
    <p>Cuando generas o abres una cotización tienes botones para compartir el link:</p>
    <ul>
      <li><b>Copiar link</b> — El enlace se ve como <code>tuempresa.cotiza.cloud/c/abc123</code>. Funciona en cualquier celular o compu, sin instalar nada.</li>
      <li><b>WhatsApp</b> — Abre WhatsApp con el link listo para enviar. <b>Es la forma con mejor tasa de apertura.</b></li>
      <li><b>Correo</b> — Abre tu correo con el link en el cuerpo, al correo del cliente si lo tienes registrado.</li>
      <li><b>Marcar como enviada</b> — Deja el estado en "Enviada" para empezar el seguimiento.</li>
    </ul>
    <div class="ay-warn">Manda <b>siempre el LINK</b>, nunca una foto o PDF. El link es lo que activa el Radar y el seguimiento: una captura de pantalla no te dice nada de lo que hace el cliente.</div>
  </div>

  <!-- ── ADJUNTOS ── -->
  <div class="ay-card">
    <h3>Archivos adjuntos</h3>
    <p>Después de guardar la cotización puedes <b>adjuntar archivos</b> (planos, fichas técnicas, fotos, contratos) que el cliente verá junto a la propuesta.</p>
    <ul>
      <li>Hasta <b>3 archivos</b>, <b>1 MB cada uno</b>.</li>
      <li>Formatos: imágenes (JPG, PNG, GIF), PDF, Word (DOC/DOCX) y Excel (XLS/XLSX).</li>
      <li>Para subir o borrar adjuntos necesitas ser admin o tener el permiso correspondiente.</li>
    </ul>
  </div>

  <!-- ── HISTORIAL ── -->
  <div class="ay-card">
    <h3>Historial de visitas e historial de cambios</h3>
    <p>En el panel derecho, al editar, tienes dos historiales muy útiles:</p>
    <ul>
      <li><b>Historial de visitas</b> — Cada vez que el cliente abre el link aparece una línea: hace cuánto fue, desde qué dispositivo (iPhone, Android, computadora), su zona y cuánto tiempo lo tuvo abierto ("12s" o "breve").</li>
      <li><b>Historial de cambios</b> — Registro de qué se hizo con la cotización (creada, editada, enviada, suspendida) y quién lo hizo.</li>
    </ul>
    <div class="ay-tip">Una visita solo cuenta si el cliente <b>realmente vio la cotización al menos 2 segundos</b>. Las aperturas instantáneas (vistas previas de WhatsApp, toques accidentales) se filtran solas para que tu contador no mienta.</div>
  </div>

  <!-- ── CLONAR / CONVERTIR ── -->
  <div class="ay-card">
    <h3>Clonar y convertir a venta</h3>
    <ul>
      <li><b>Clonar</b> (desde el listado de Cotizaciones) — Crea una copia idéntica con nuevo folio: mismos artículos, cliente, notas, extras y descuentos. Ideal para clientes que piden algo casi igual o para volver a cotizar lo mismo a otro cliente.</li>
      <li><b>Convertir a venta</b> — Pasa la cotización a venta para empezar a cobrar. La cotización original queda <b>congelada</b> (no se modifica aunque luego edites la venta). Cuando el cliente <b>acepta</b> desde el link, esto puede pasar automáticamente.</li>
    </ul>
  </div>

  <!-- ── PERMISOS ── -->
  <div class="ay-card">
    <h3>¿Por qué a mí no me aparecen algunas opciones?</h3>
    <p>El administrador puede limitar qué hace cada vendedor. Si no ves cierto botón, es por permisos. Estos son los que afectan cotizaciones:</p>
    <ul>
      <li><b>Crear / Editar cotizaciones</b> — Sin esto, no ves el botón de nueva ni puedes guardar cambios.</li>
      <li><b>Editar precios</b> — Sin esto, los precios quedan bloqueados al precio del catálogo.</li>
      <li><b>Aplicar descuentos</b> — Controla si ves cupones y el descuento automático.</li>
      <li><b>Asignar cotizaciones</b> — Permite cambiar el vendedor asignado.</li>
      <li><b>Ver todas las cotizaciones</b> — Sin esto, solo ves las tuyas (las que creaste o te asignaron).</li>
      <li><b>Ver cantidades / Adjuntar</b> — Controlan si ves cantidades y precios, y si puedes subir archivos.</li>
    </ul>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  VENTAS                                                -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-ventas">
  <h2 class="ay-h2">🛒 Ventas</h2>
  <p class="ay-subtitle">Cuando una cotización se cierra, se vuelve una <b>venta</b>. Aquí cobras (abonos), emites recibos, llevas el saldo y controlas la entrega. Esta guía explica <b>cada botón y cada opción</b> del detalle de la venta y de cómo editarla.</p>

  <!-- ── ESTADOS ── -->
  <div class="ay-card">
    <h3>Los estados de una venta</h3>
    <p>Una venta cambia de estado <b>sola</b> según lo que cobres. Lo verás como etiqueta de color en el listado y arriba del detalle:</p>
    <ul>
      <li><span class="ay-shortcut">Pendiente</span> Venta creada, <b>sin ningún pago</b> registrado.</li>
      <li><span class="ay-shortcut">Parcial</span> El cliente ya <b>abonó algo</b>, pero todavía debe saldo.</li>
      <li><span class="ay-shortcut">Pagada</span> Se cubrió el total. El saldo llega a cero. <i>(El sistema lo pone solo cuando los pagos igualan o superan el total.)</i></li>
      <li><span class="ay-shortcut">Entregada</span> Marcaste que ya entregaste el producto o servicio.</li>
      <li><span class="ay-shortcut">Cancelada</span> La venta se anuló. Solo se puede cancelar si <b>no tiene pagos activos</b>.</li>
    </ul>
  </div>

  <!-- ── DE DÓNDE SALE UNA VENTA ── -->
  <div class="ay-card">
    <h3>¿Cómo nace una venta?</h3>
    <p>Una venta sale de una cotización: cuando el cliente <b>acepta</b> desde el link, o cuando tú la <b>conviertes a venta</b> desde el editor de la cotización. En ese momento la cotización original queda <b>congelada</b> (no se modifica) y la venta arranca su propia vida con su folio (ej. <code>VTA-2026-0007</code>) y su saldo igual al total.</p>
  </div>

  <!-- ── ENCABEZADO ── -->
  <div class="ay-card">
    <h3>El detalle de la venta (lo de arriba)</h3>
    <p>Al abrir una venta verás un encabezado con la información clave. Si eres <b>administrador</b>, varios datos traen un lápiz ✏️ para editarlos en el momento:</p>
    <ul>
      <li><b>Cliente</b> — A quién se le vende. El admin puede <b>cambiarlo</b> con "✏️ cambiar".</li>
      <li><b>Fecha</b> — Cuándo se creó la venta.</li>
      <li><b>Asesor</b> — El vendedor responsable. El admin puede <b>reasignarlo</b> con el lápiz (también actualiza la cotización origen).</li>
      <li><b>Cotización</b> — Un enlace a la cotización de la que salió, por si necesitas revisarla.</li>
    </ul>
  </div>

  <!-- ── ARTÍCULOS ── -->
  <div class="ay-card">
    <h3>Los artículos de la venta</h3>
    <p>Abajo aparecen los conceptos contratados con su cantidad, precio y total. El <b>administrador</b> puede modificarlos:</p>
    <ul>
      <li><b>+ Agregar artículo</b> — Abre una ventana con dos pestañas: <b>Del catálogo</b> (busca y elige, pones la cantidad) o <b>Manual</b> (escribes nombre, SKU, descripción, cantidad y precio a mano).</li>
      <li><b>✏️ Editar</b> — Cambia nombre, SKU, descripción, cantidad o precio de una línea.</li>
      <li><b>🗑 Eliminar</b> — Quita el artículo. <i>(Necesita ser admin o tener el permiso de eliminar artículos de venta.)</i></li>
      <li><b>Botón ↓ / ↑ "Mover a extra / principal"</b> — Convierte un artículo en extra (se muestra aparte con su subtotal) o lo regresa a principal.</li>
    </ul>
    <div class="ay-warn">Los cambios a los artículos <b>no se guardan solos</b>: al modificar algo aparece el botón verde <b>"Guardar cambios"</b> en el lado derecho. Tócalo para que se apliquen. El total y el saldo se recalculan automáticamente.</div>
  </div>

  <!-- ── COBRAR / ABONOS ── -->
  <div class="ay-card">
    <h3>Cobrar: registrar abonos</h3>
    <p>Aquí registras cada pago que te hace el cliente. Una venta puede recibir varios abonos hasta quedar pagada.</p>
    <div class="ay-steps">
      <div class="ay-step">
        <h4>Toca "Registrar abono"</h4>
        <p>Está en el historial de pagos y en los botones del lado derecho.</p>
      </div>
      <div class="ay-step">
        <h4>Elige la forma de pago y el monto</h4>
        <p>Efectivo 💵, transferencia 🏦 o tarjeta 💳. Pon el <b>monto</b> (obligatorio) y, si quieres, un <b>concepto</b> ("Anticipo 50%", "Pago final") y una <b>referencia/nota</b> ("BBVA ref. 8823"). La fecha y hora se ponen solas.</p>
      </div>
      <div class="ay-step">
        <h4>Guarda</h4>
        <p>El sistema genera un <b>recibo con folio</b> (ej. <code>REC-2026-0012</code>), actualiza el saldo y cambia el estado solo (a Parcial o Pagada). Si activaste avisos, te llega notificación push y correo del abono.</p>
      </div>
    </div>
  </div>

  <!-- ── RECIBOS ── -->
  <div class="ay-card">
    <h3>Recibos de pago</h3>
    <ul>
      <li>Cada abono genera su <b>recibo</b>. En el historial de pagos puedes tocar <b>"🖨 PDF"</b> para imprimir el comprobante (sale en dos copias: empresa y cliente).</li>
      <li><b>Cancelar un recibo</b> (botón ✕): si te equivocaste en un pago, lo cancelas con un motivo. El saldo de la venta <b>se reajusta</b> automáticamente. <i>(Necesita permiso de cancelar recibos.)</i></li>
      <li>El cliente también tiene su propio link de recibo como comprobante.</li>
    </ul>
  </div>

  <!-- ── RESUMEN FINANCIERO ── -->
  <div class="ay-card">
    <h3>El resumen financiero (lado derecho)</h3>
    <p>De un vistazo controlas el dinero de la venta:</p>
    <ul>
      <li><b>Total</b> — Lo que cuesta la venta completa.</li>
      <li><b>Pagado</b> — Lo que el cliente ya cubrió.</li>
      <li><b>Saldo pendiente</b> — Lo que falta por cobrar (o "Pagado completo ✓").</li>
      <li><b>Barra de progreso</b> — El % cobrado, visual.</li>
    </ul>
  </div>

  <!-- ── BOTONES DE ACCIÓN ── -->
  <div class="ay-card">
    <h3>Botones de acción (lado derecho)</h3>
    <ul>
      <li><b>Registrar abono</b> — Cobra un pago (ver arriba).</li>
      <li><b>Copiar URL del cliente</b> — Copia el link público de la venta para que el cliente vea su saldo y pagos.</li>
      <li><b>Agregar descuento</b> — Aplica un descuento en pesos directo al total. <b>Se acumula</b> sobre el descuento que ya hubiera. Pon 0 para quitarlo. <i>(Necesita permiso de descuentos.)</i></li>
      <li><b>Agregar extra</b> — Suma un concepto extra (instalación, flete, accesorio) con su nombre y total. Aparece en sección aparte.</li>
      <li><b>Guardar cambios</b> — Aparece en verde solo cuando hay cambios pendientes en artículos o descuentos.</li>
      <li><b>🖨️ Imprimir / PDF</b> — Genera el comprobante completo de la venta (no es factura fiscal).</li>
      <li><b>📋 Estado de cuenta</b> — Imprime un desglose claro: conceptos, extras, ajustes, pagos y saldo. Ideal para mandárselo al cliente.</li>
      <li><b>✕ Cancelar venta</b> — Anula la venta (admin). Solo se permite si <b>no tiene pagos</b>; si los tiene, primero cancela los recibos.</li>
    </ul>
  </div>

  <!-- ── NOTAS / HISTORIAL ── -->
  <div class="ay-card">
    <h3>Notas internas e historial</h3>
    <ul>
      <li><b>Notas internas</b> — Un espacio para apuntar lo de producción, entrega u observaciones. Se guarda solo conforme escribes. El cliente <b>no lo ve</b>.</li>
      <li><b>Historial</b> — Registra todo lo que pasa con la venta: abonos, cambios de artículos, descuentos, cambio de cliente o asesor, cancelaciones, y quién lo hizo.</li>
    </ul>
  </div>

  <!-- ── LISTADO ── -->
  <div class="ay-card">
    <h3>El listado de ventas</h3>
    <p>En <b>Ventas</b> ves todas tus ventas. Arriba hay <b>filtros rápidos</b> (Todas, Pendiente, Parcial, Pagada, Entregada, Cancelada) con su contador, y un <b>buscador</b> por folio, cliente o título. Cada renglón muestra el estado y cuánto falta por cobrar; las ventas <b>sin ningún pago</b> se resaltan para que las persigas.</p>
  </div>

  <div class="ay-tip">El cliente puede ver su venta, su saldo y sus pagos desde un link público — perfecto para que sepa cuánto debe sin tener que preguntarte.</div>
  <div class="ay-warn">Los documentos que genera CotizaCloud (recibos, estado de cuenta, comprobante de venta) son <b>internos</b>, no son facturas fiscales (CFDI).</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  RADAR                                                 -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-radar">
  <h2 class="ay-h2"><?= ico('target',18,'#16a34a') ?> Radar</h2>
  <p class="ay-subtitle">Tu arma secreta de ventas. Detecta en tiempo real quién está a punto de comprar y quién necesita seguimiento.</p>

  <div class="ay-card">
    <h3>¿Cómo funciona?</h3>
    <p>Cada vez que un cliente abre su cotización, el Radar registra:</p>
    <ul>
      <li><b>Cuándo la abrió</b> — Fecha y hora exacta</li>
      <li><b>Cuánto tiempo estuvo</b> — Segundos de lectura real</li>
      <li><b>Qué tanto scrolleó</b> — Si solo vio el inicio o leyó toda la cotización</li>
      <li><b>Si revisó el precio</b> — Si se detuvo en los totales</li>
      <li><b>Cuántas veces volvió</b> — Si regresó a revisar de nuevo</li>
      <li><b>Desde cuántos dispositivos</b> — Si la compartió con alguien</li>
    </ul>
    <p style="margin-top:12px">Con estas señales, el sistema clasifica cada cotización en <b>buckets</b> (categorías de intención) y te dice exactamente a quién llamar primero.</p>
  </div>

  <div class="ay-tip">🛡️ Para que el Radar no te mienta, cuida el <b>Escudo</b>: abre las cotizaciones solo desde tu dispositivo con la sesión iniciada. Lee el apartado <b>🛡️ Escudo</b> en el menú de Inicio — es de lo más importante.</div>

  <div class="ay-card">
    <h3>🎯 Resumen — Probable cierre (tu lista #1)</h3>
    <p><b>Esto NO es un grupo más: es el concentrado de todos tus clientes calientes.</b> El sistema junta en una sola lista a las cotizaciones que aparecen en cualquiera de los grupos de abajo y que de verdad muestran intención de compra (varias señales juntas + lectura real + que se fijaron en el precio).</p>
    <p style="margin-top:8px">Si solo vas a ver una cosa en el Radar, ve esta. <b>Son los clientes a los que tienes que contactar HOY.</b></p>
  </div>

  <div class="ay-card">
    <h3>Los grupos (buckets) que alimentan el resumen</h3>
    <ul>
      <li><b><?= ico('fire',12,'#991b1b') ?> On Fire</b> — Máxima actividad. El cliente está revisando intensamente ahora mismo.</li>
      <li><b><?= ico('fire',12,'#c2410c') ?> Cierre inminente</b> — Varias señales fuertes convergiendo. Está a punto de decidir.</li>
      <li><b>💸 Validando precio</b> — Está enfocado en los números. No bajes el precio — transfiere certeza.</li>
      <li><b>🔮 Predicción alta</b> — El modelo estadístico predice alta probabilidad de cierre.</li>
      <li><b><?= ico('fire',12,'#6d28d9') ?> Re-enganche caliente</b> — Volvió después de días con señales fuertes.</li>
      <li><b>👥 Multi-persona</b> — Múltiples personas revisando. Decisión compartida (pareja, socio).</li>
    </ul>
  </div>

  <div class="ay-card">
    <h3>Playbook</h3>
    <p>Cada bucket tiene un <b>Playbook</b> — una guía con:</p>
    <ul>
      <li>Qué está pensando el cliente en esa etapa</li>
      <li>Qué hacer y qué NO hacer</li>
      <li>Mensajes de WhatsApp listos para copiar y enviar</li>
      <li>El mejor canal para contactar (WhatsApp vs llamada)</li>
    </ul>
    <p style="margin-top:8px">Haz clic en el botón <b>📖 Playbook</b> de cualquier bucket para verlo.</p>
  </div>

  <div class="ay-card">
    <h3>Métricas clave</h3>
    <ul>
      <li><b>Score%</b> — Qué tan probable es que cierre (basado en comportamiento real)</li>
      <li><b>Prior%</b> — Prioridad para dar seguimiento</li>
      <li><b>Ciclo venta</b> — Mediana de días que tarda una venta en cerrarse en tu empresa</li>
      <li><b>Tasa cierre</b> — Porcentaje de cotizaciones que se convierten en venta</li>
    </ul>
  </div>

  <div class="ay-tip">El Radar se recalibra automáticamente con cada nueva venta. Entre más datos tenga, más preciso se vuelve. Confía en las señales pero siempre usa tu criterio de vendedor.</div>

  <div class="ay-warn">El Radar solo funciona cuando el cliente abre la cotización desde el link. Si le mandas un PDF o screenshot, no hay tracking.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  TERMÓMETRO                                            -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-termometro">
  <h2 class="ay-h2">🌡️ Termómetro de Productividad <span class="ay-shortcut">Business</span></h2>
  <p class="ay-subtitle">Es como el velocímetro de tu trabajo: un número del 0 al 100 que te dice qué tan bien estás vendiendo. Sube cuando haces bien tu trabajo y baja cuando lo dejas de hacer. No necesitas saber de computadoras — solo seguir 4 pasos todos los días. Aquí te explicamos cada uno, paso a paso.</p>

  <div class="ay-card">
    <h3>📍 ¿Dónde lo veo?</h3>
    <p>Entra a <b>Inicio</b> (la primera pantalla cuando abres el sistema). Ahí, hacia abajo, está tu termómetro con tu número y tu nivel:</p>
    <ul>
      <li>🟢 <b>Top</b> (86 a 100) — Vas excelente</li>
      <li>🟢 <b>Activo</b> (61 a 85) — Vas bien</li>
      <li>🟡 <b>Regular</b> (31 a 60) — Te falta seguimiento</li>
      <li>🔴 <b>Bajo</b> (0 a 30) — Hay que ponerse las pilas</li>
      <li>⚪ <b>Nuevo</b> — Cuando apenas empiezas, no se te califica todavía (unos días de gracia)</li>
    </ul>
  </div>

  <div class="ay-tip">El número se calcula solo, con lo que haces de verdad. No se puede "hacer trampa": solo sube si trabajas como un buen vendedor. Mide los <b>últimos 15 días</b>, así que es una foto de cómo estás trabajando AHORA.</div>

  <h3 style="font:800 18px var(--body);margin:30px 0 4px">📋 Lo que tienes que hacer TODOS LOS DÍAS</h3>
  <p class="ay-subtitle">Haz estos 4 pasos cada día y tu termómetro va a subir solo. Está explicado para que cualquiera lo pueda hacer.</p>

  <div class="ay-steps">
    <div class="ay-step">
      <h4>Manda tus cotizaciones por el LINK (no por foto)</h4>
      <p>Cuando hagas una cotización, mándala con el <b>enlace (link)</b> que te da el sistema — por WhatsApp o correo. <b>NO mandes una foto, captura de pantalla ni PDF.</b> ¿Por qué? Porque solo con el link el sistema sabe si el cliente la abrió. Si mandas una foto, es como si no hubieras mandado nada: el termómetro no lo ve.</p>
    </div>

    <div class="ay-step">
      <h4>Entra al RADAR todos los días</h4>
      <p>El <b>Radar</b> está en el menú. Es la pantalla donde aparecen tus clientes ordenados por quién está más interesado en comprar. Con solo entrar a revisarlo todos los días ya sumas puntos. Un vendedor que no revisa su Radar pierde puntos. Conviértelo en tu primer hábito de la mañana.</p>
    </div>

    <div class="ay-step">
      <h4>Lee el botón con el signo de pregunta <b>“?”</b> de cada cotización</h4>
      <p>Dentro del Radar, cada cotización tiene un botón pequeño con un signo de pregunta <b>(?)</b>. <b>Dale click.</b> Te explica en palabras sencillas: cuántas veces abrió el cliente la cotización, qué tan interesado está, y qué te conviene hacer con ese cliente. Es como un consejo personalizado para cada uno. Revísalo a diario — te dice a quién llamarle primero.</p>
    </div>

    <div class="ay-step">
      <h4>Marca la manita 👍 o 👎 según cómo te fue con el cliente</h4>
      <p>Aquí está <b>lo más importante del termómetro.</b> En el Radar, junto a cada cotización vas a ver dos manitas: una <b>arriba 👍</b> y una <b>abajo 👎</b>.</p>
      <p style="margin-top:8px">Cuando hablas con el cliente, lo visitas o tienen una cita, <b>tú eres quien sabe</b> si quedó interesado o no. Entonces regresas al Radar, buscas su cotización, y le das click a la manita que corresponde:</p>
      <ul>
        <li><b>👍 manita arriba</b> = el cliente SÍ mostró interés (te dijo que le interesa, agendaron, te pidió más información, va en serio).</li>
        <li><b>👎 manita abajo</b> = el cliente NO mostró interés (te dijo que no, no contesta, ya no quiere, era solo curiosidad).</li>
      </ul>
      <p style="margin-top:8px">El sistema después <b>compara tu manita con lo que el cliente hace de verdad</b> (si regresa a ver la cotización, si termina comprando). Si tú marcaste bien lo que iba a pasar, ganas más puntos. Por eso debes ser <b>honesto</b>: marca lo que realmente sentiste en la plática, no lo que te gustaría que pasara. El termómetro premia al vendedor que de verdad conoce a sus clientes.</p>
    </div>

    <div class="ay-step">
      <h4>Cierra la venta y REGISTRA EL PAGO</h4>
      <p>Cuando un cliente te compra, registra la venta y, muy importante, <b>anota el pago (abono) en la sección de Ventas.</b> Una venta sin ningún pago anotado <b>no cuenta</b> para tu termómetro — el sistema no sabe que cobraste hasta que lo registras. Cerrar ventas y cobrarlas es lo que más puntos te da.</p>
    </div>
  </div>

  <div class="ay-warn"><b>⏰ Ojo con las cotizaciones que el cliente NO abre.</b> Si una cotización cumple <b>5 días sin que el cliente la abra</b>, te castiga muy fuerte tu puntaje (tumba casi toda la parte de "envío" a cero), y con una sola que tengas así ya te pega. <b>Qué hacer:</b> si a los 2 o 3 días el cliente todavía no la ha abierto, háblale, reenvíale el link y asegúrate de que entre. No dejes que una cotización llegue a los 5 días sin abrir.<br><br><b>💡 Truco:</b> si de plano el cliente no responde, en el menú de <b>Cotizaciones</b> puedes <b>suspender</b> esa cotización. Una cotización suspendida ya no te penaliza (es como si no existiera para el termómetro), y de paso obligas al cliente a pedírtela de nuevo si le interesa.</div>

  <div class="ay-card">
    <h3>🌡️ Los consejos (tips) que te salen cada día — LÉELOS COMPLETOS</h3>
    <p>Cada día, tu termómetro te muestra unos <b>consejos cortos</b> sobre cómo vas trabajando: qué estás haciendo bien y qué te está faltando. <b>Léelos completos, hasta el final.</b> Por dos razones:</p>
    <ul>
      <li>Te dicen <b>exactamente qué hacer</b> para vender más y subir tu puntaje.</li>
      <li>El sistema nota cuando los lees — leerlos también te suma puntos.</li>
    </ul>
    <p style="margin-top:8px">No los ignores. Son la guía que el sistema arma especialmente para ti según cómo estuviste trabajando.</p>
  </div>

  <div class="ay-card">
    <h3>🏆 Premios para los que venden bien</h3>
    <p>El termómetro no solo te cuida de errores: también te <b>premia con puntos extra</b> cuando vendes en grande. Un buen vendedor se nota:</p>
    <ul>
      <li><b>Ventas grandes (ticket alto)</b> — Si cierras una venta por arriba del <b>doble</b> de tu venta promedio, ganas puntos extra. Si es el <b>triple</b> o más, ganas todavía más.</li>
      <li><b>Buena racha de cierre</b> — Si estás cerrando <b>mucho más seguido</b> que lo normal de la empresa, ganas un bono por tu racha.</li>
      <li><b>Vender más que el promedio</b> — Si vendes más que el promedio del equipo del periodo pasado, tu puntaje sube. Si vendes menos, baja. Siempre busca superar tu propio ritmo.</li>
    </ul>
    <p style="margin-top:8px"><b>En pocas palabras: cierra, cierra grande, y cierra seguido.</b></p>
  </div>

  <div class="ay-card">
    <h3>⚠️ Lo que te BAJA el puntaje</h3>
    <ul>
      <li><b>Dejar cotizaciones 5+ días sin que el cliente las abra (esta castiga MUCHO).</b></li>
      <li>No dar seguimiento: dejar que tus clientes interesados se "enfríen" sin llamarles.</li>
      <li>No marcar las manitas 👍 / 👎 en el Radar.</li>
      <li>Vender pero no anotar el pago.</li>
      <li>Dar demasiados descuentos.</li>
      <li>No entrar al sistema ni revisar el Radar por varios días.</li>
    </ul>
  </div>

  <div class="ay-tip">¿Vendes pero tu número no sube? Casi siempre es por una de dos cosas: <b>(1)</b> no anotaste el pago de tus ventas, o <b>(2)</b> no le diste 👍/👎 a tus clientes en el Radar. Revisa esas dos primero.<br><br><b>Nota:</b> las ventas con <b>descuento</b> dan menos puntos que las que vendes a precio completo. Usa los descuentos con medida.</div>

  <div class="ay-warn">El termómetro mira los últimos 15 días. Si esta semana dejaste de dar seguimiento, baja — aunque el mes pasado hayas vendido mucho. Lo importante es mantener el ritmo todos los días.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  COSTOS                                                -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-costos">
  <h2 class="ay-h2">📉 Costos <span class="ay-shortcut">Pro y Business</span></h2>
  <p class="ay-subtitle">Registra lo que te cuesta cada venta o tu operación, para conocer tu <b>margen real</b> (no solo lo que vendes, sino lo que te queda). Lo que ves aquí depende del <b>modo de costos</b> que elegiste en Configuración → Costos.</p>

  <div class="ay-card">
    <h3>Primero: el modo de costos</h3>
    <p>En <b>Configuración → Costos</b> eliges cómo trabajas, y eso define las pestañas que aparecen:</p>
    <ul>
      <li><b>Por venta</b> — Asignas cada gasto a una venta concreta. Verás la pestaña <b>"Costos por venta"</b>.</li>
      <li><b>Por empresa</b> — Registras gastos generales del negocio. Verás <b>"Gastos generales"</b>.</li>
      <li><b>Ambos</b> <span class="ay-shortcut">Business</span> — Las dos pestañas, para control total.</li>
    </ul>
  </div>

  <!-- ── COSTOS POR VENTA ── -->
  <div class="ay-card">
    <h3>Pestaña "Costos por venta"</h3>
    <p>Arriba ves 3 indicadores del mes: <b>Ventas</b>, <b>Costos</b> y <b>Margen promedio</b> (comparado contra el mes anterior). Debajo, la lista de tus ventas con su margen, filtrable por categoría y con buscador.</p>
    <ul>
      <li>Toca <b>"+ Costo"</b> en una venta (o entra a su detalle) para registrar un gasto: <b>categoría</b>, <b>proveedor</b> (opcional), <b>concepto</b>, <b>importe</b>, <b>fecha</b> y nota.</li>
      <li>En el detalle de cada venta ves todos sus costos sumados, la <b>utilidad</b> (venta − costos) y el <b>% de margen</b> con una barra de color (verde = sano, ámbar = ajustado, rojo = bajo).</li>
    </ul>
  </div>

  <!-- ── GASTOS GENERALES ── -->
  <div class="ay-card">
    <h3>Pestaña "Gastos generales"</h3>
    <p>Para gastos que <b>no son de una venta</b>: renta, nómina, servicios, seguros, etc. Cada uno con concepto, categoría, proveedor, fecha e importe. Arriba ves el <b>total</b> de gastos generales y qué <b>% representan de tus ventas</b>.</p>
  </div>

  <!-- ── CATEGORÍAS ── -->
  <div class="ay-card">
    <h3>Pestaña "Categorías"</h3>
    <p>Organiza tus costos por tipo (materiales, mano de obra, transporte…). Cada categoría tiene un <b>color</b> y un interruptor para <b>activarla o desactivarla</b> (las activas son las que aparecen al registrar un gasto). El <b>admin</b> puede crear y editar categorías.</p>
  </div>

  <!-- ── ANÁLISIS ── -->
  <div class="ay-card">
    <h3>Pestaña "Análisis" <span class="ay-shortcut">Business</span></h3>
    <p>Gráficas que reparten tus costos <b>por categoría</b> (cuánto pesa cada tipo de gasto) para que veas dónde se te va el dinero.</p>
  </div>

  <!-- ── PROVEEDORES ── -->
  <div class="ay-card">
    <h3>Pestaña "Proveedores"</h3>
    <p>Tu lista de proveedores. Puedes ligar gastos a un proveedor para luego ver cuánto le pagas a cada uno (el detalle vive en Reportes → Proveedores).</p>
  </div>

  <div class="ay-tip">Registra los costos <b>cuando ocurren</b>, no a fin de mes. Así tu margen siempre refleja la realidad y sabes qué ventas de verdad te dejan dinero.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  REPORTES                                              -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-reportes">
  <h2 class="ay-h2"><?= ico('file',18,'#7c3aed') ?> Reportes</h2>
  <p class="ay-subtitle">La radiografía a fondo de tu negocio, con datos reales. Está organizado en <b>pestañas</b> y todo se filtra por el <b>período</b> que elijas. Esta guía explica cada pestaña.</p>

  <div class="ay-card">
    <h3>Período y exportar</h3>
    <p>Arriba eliges el período: <b>este mes, mes anterior, últimos 30/90 días, últimos 12 meses, este año, año anterior, todo el historial</b> o un <b>rango de fechas</b> a tu medida. El botón <b>"↓ Exportar CSV"</b> baja la tabla que estés viendo para abrirla en Excel.</p>
  </div>

  <div class="ay-card">
    <h3>Pestaña Financiero</h3>
    <p>El panorama del dinero. Indicadores grandes: <b>Ingresos</b>, <b>Cobrado</b> (y lo pendiente), <b>Utilidad bruta</b> (ingresos − costos) y <b>Margen bruto %</b>. Una <b>gráfica</b> de ingresos vs. costos mes a mes, el resumen de cotizaciones del período (generadas, aceptadas, rechazadas, activas, tasa de conversión) y, si lo capturaste, tu <b>historial importado</b>.</p>
  </div>

  <div class="ay-card">
    <h3>Pestaña Por asesor <span class="ay-shortcut">Admin</span></h3>
    <p>Compara a tu equipo: por cada vendedor ves ventas, ingresos, costos, utilidad, margen, cotizaciones y conversión. Ideal para saber quién sobresale y quién necesita apoyo.</p>
  </div>

  <div class="ay-card">
    <h3>Pestaña Cotizaciones</h3>
    <p>Un conteo rápido por estado (sin abrir, suspendidas, abiertas, aceptadas, rechazadas, vencidas) y la <b>tabla detallada</b> de cada cotización del período con su monto, estado, visitas y fecha. Tócalas para abrirlas.</p>
  </div>

  <div class="ay-card">
    <h3>Pestaña Recibos</h3>
    <p>Todos los pagos del período: total de recibos, monto cobrado y cancelaciones, con el detalle de cada recibo (cliente, venta, monto, forma de pago y estado).</p>
  </div>

  <div class="ay-card">
    <h3>Pestaña Costos y márgenes</h3>
    <p>Profundiza en la rentabilidad: utilidad y margen, <b>costos por categoría</b>, evolución mensual, el <b>margen venta por venta</b>, el <b>punto de equilibrio</b> (cuánto necesitas vender para no perder) y un <b>ranking de rentabilidad</b> de tus ventas.</p>
  </div>

  <div class="ay-card">
    <h3>Pestaña Proveedores <span class="ay-shortcut">Business</span></h3>
    <p>A quién le pagas y cuánto: top proveedores, pagos mensuales y el detalle de cada pago.</p>
  </div>

  <div class="ay-card">
    <h3>Pestaña Feedback</h3>
    <p>Aparece si activaste la calificación del cliente (Configuración → Feedback). Muestra la <b>distribución de estrellas</b>, el promedio <b>por asesor</b> y los comentarios recientes de tus clientes.</p>
  </div>

  <div class="ay-tip">Revisa <b>Por asesor</b> cada semana si tienes equipo, y <b>Costos y márgenes</b> al cierre de mes para confirmar que de verdad estás ganando, no solo vendiendo.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  CONFIGURACIÓN                                         -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-configuracion">
  <h2 class="ay-h2">⚙️ Configuración</h2>
  <p class="ay-subtitle">El centro de control de tu cuenta. Aquí está organizado en <b>pestañas (tabs)</b>. Esta guía explica <b>cada pestaña y cada opción</b> que vas a encontrar. <b>Solo el administrador</b> entra a Configuración. Algunas pestañas aparecen según tu plan (Free, Lite, Pro o Business).</p>

  <div class="ay-card">
    <h3>Las pestañas que verás</h3>
    <ul>
      <li><b>Empresa</b> — Tus datos, impuestos, avisos, apariencia y textos. <span class="ay-shortcut">Todos</span></li>
      <li><b>Catálogo</b> (o <b>Propiedades</b> si vendes inmuebles) — Tus productos o servicios. <span class="ay-shortcut">Todos</span></li>
      <li><b>Clientes</b> — Tu base de clientes. <span class="ay-shortcut">Todos</span></li>
      <li><b>Cupones</b> — Códigos de descuento + el descuento con cronómetro. <span class="ay-shortcut">Todos</span></li>
      <li><b>Usuarios</b> — Tu equipo y sus permisos. <span class="ay-shortcut">Pro y Business</span></li>
      <li><b>Radar</b> — Qué tan fino detecta el interés de tus clientes. <span class="ay-shortcut">Pro y Business</span></li>
      <li><b>Costos</b> — Cómo registras tus gastos. <span class="ay-shortcut">Pro y Business</span></li>
      <li><b>Marketing</b> — Pixels de Meta, Google, TikTok. <span class="ay-shortcut">Business</span></li>
      <li><b>Historial</b> — Tus números viejos para alimentar reportes. <span class="ay-shortcut">Business</span></li>
      <li><b>Termómetro</b> — Mostrar u ocultar el score a los asesores. <span class="ay-shortcut">Business</span></li>
      <li><b>Feedback</b> — Pedir calificación al cliente. <span class="ay-shortcut">Todos</span></li>
      <li><b>Suscripción</b> — Tu plan y pagos. <span class="ay-shortcut">Todos</span></li>
    </ul>
    <div class="ay-tip">Recuerda tocar el botón <b>"Guardar cambios"</b> de cada pestaña. Los cambios no se aplican hasta que guardas.</div>
  </div>

  <!-- ── EMPRESA ── -->
  <div class="ay-card">
    <h3>Pestaña Empresa — empieza aquí</h3>
    <p>Es lo primero que debes llenar. Todo esto aparece en las cotizaciones y ventas que ve tu cliente.</p>
    <ul>
      <li><b>Datos generales</b> — Sube tu <b>logo</b> (PNG, SVG o WEBP, máx. 2 MB), y pon nombre, ciudad/sucursal, teléfono, email, dirección, RFC y sitio web.</li>
      <li><b>Impuestos</b> — Elige cómo manejas el IVA: <b>Ninguno</b> (sin impuesto), <b>Suma</b> (el IVA se agrega al subtotal) o <b>Incluido</b> (tus precios ya traen IVA). Y el <b>porcentaje</b>.</li>
      <li><b>Notificaciones</b> — Pon un <b>email de avisos</b> y prende/apaga qué te notifican: cotización aceptada, rechazada, abono registrado, alertas del Radar y feedback del cliente.</li>
      <li><b>Apariencia</b> — Elige el <b>color</b> con que se ve la cotización pública del cliente (verde, azul, rojo, naranja, dorado, morado u oscuro).</li>
      <li><b>Defaults de cotizaciones</b> — La <b>vigencia</b> por defecto (días para que venza), si los asesores <b>pueden editar precios</b>, si <b>ocultas cantidad y precio unitario</b> al cliente (solo ve descripción y total), y el <b>auto-suspender</b> (suspende solas las cotizaciones sin actividad tras X días).</li>
      <li><b>Mensajes</b> — Personaliza el <b>saludo/encabezado</b>, el mensaje al <b>aceptar</b> y al <b>rechazar</b>. Puedes insertar variables como <code>{{cliente}}</code>, <code>{{empresa}}</code> o <code>{{asesor}}</code> que se rellenan solas.</li>
      <li><b>Términos y condiciones</b> — Textos al pie de las cotizaciones y de las ventas/recibos, más un footer chico para cada uno.</li>
    </ul>
  </div>

  <!-- ── CATÁLOGO ── -->
  <div class="ay-card">
    <h3>Pestaña Catálogo (o Propiedades)</h3>
    <p>Tu lista de productos o servicios con su precio. Es lo que eliges al armar una cotización para no escribir todo a mano.</p>
    <ul>
      <li><b>+ Nuevo artículo</b> — Pon nombre, SKU (clave interna, opcional), descripción (opcional) y precio.</li>
      <li><b>✎ Editar / ✕ Eliminar</b> — Modifica o quita cualquier artículo. Hay un buscador arriba.</li>
      <li>Si tu giro es <b>inmuebles</b>, esta pestaña se llama <b>Propiedades</b> y captura datos de la propiedad (m², recámaras, fotos, etc.).</li>
    </ul>
  </div>

  <!-- ── CLIENTES ── -->
  <div class="ay-card">
    <h3>Pestaña Clientes</h3>
    <p>Tu directorio. Cada cliente muestra su teléfono, email y cuántas cotizaciones tiene.</p>
    <ul>
      <li><b>+ Nuevo cliente</b> — Nombre, teléfono, email (opcional) y notas internas (solo las ves tú).</li>
      <li>Toca cualquier cliente para editarlo. Hay buscador arriba.</li>
    </ul>
  </div>

  <!-- ── CUPONES ── -->
  <div class="ay-card">
    <h3>Pestaña Cupones</h3>
    <ul>
      <li><b>+ Nuevo cupón</b> — Define un <b>código</b>, el <b>tipo de descuento</b> (porcentaje % o monto fijo $), una descripción, el <b>vencimiento</b> (nunca / a los X días / en una fecha) y si está activo.</li>
      <li>La tabla muestra cuántas veces se ha usado cada cupón y si está activo.</li>
      <li><b>Descuento automático (cronómetro)</b> — Abajo defines los valores por defecto (porcentaje y días) del descuento con cuenta regresiva que activas en cada cotización.</li>
    </ul>
  </div>

  <!-- ── USUARIOS ── -->
  <div class="ay-card">
    <h3>Pestaña Usuarios <span class="ay-shortcut">Pro y Business</span></h3>
    <p>Tu equipo. Cada vendedor tiene su propio login (en Business, además, su score del Termómetro aparece aquí). Al crear o editar un usuario defines:</p>
    <ul>
      <li><b>Datos</b> — Nombre, email, contraseña (mín. 8 caracteres; déjala vacía para no cambiarla), <b>rol</b> (Asesor o Admin) y si está activo.</li>
      <li><b>Permisos del asesor</b> (cada uno es un interruptor): crear cotizaciones, editar cotizaciones, ver cantidad y precio unitario, editar precios, aplicar descuentos/cupones, ver todas las cotizaciones, ver todas las ventas, eliminar ítems de ventas, agregar extras, cancelar recibos, capturar pagos/abonos y asignar cotizaciones.</li>
      <li><b>Acceso a módulos</b>: Costos, Proveedores, Reportes, Adjuntar archivos y Editar clientes.</li>
    </ul>
    <div class="ay-tip">El <b>Admin</b> tiene acceso total. El <b>Asesor</b> por defecto solo ve lo suyo; tú decides qué más puede hacer con estos interruptores.</div>
  </div>

  <!-- ── RADAR ── -->
  <div class="ay-card">
    <h3>Pestaña Radar</h3>
    <p>Ajusta qué tan fino detecta el Radar el interés de tus clientes.</p>
    <ul>
      <li><b>Sensibilidad</b> — <b>Agresivo</b> (muestra más cotizaciones con menos señales, bueno si tienes poco volumen), <b>Medio</b> (equilibrado, recomendado) o <b>Ligero</b> (solo las señales más sólidas).</li>
      <li><b>Calibración FIT</b> — El Radar aprende de tus cierres reales. Puedes <b>recalibrar</b> manualmente (recomendado cada 5+ ventas nuevas) o dejar la <b>calibración automática</b> (recalibra solo cada 10 ventas cerradas).</li>
      <li><b>Buckets activos</b> — Prende o apaga los grupos del Radar (probable cierre, validando precio, decisión activa, revisión multi-persona, revivió, enfriándose, hesitación). Los apagados no se calculan ni aparecen.</li>
      <li><b>Filtros de ruido</b> (se sugiere no mover) — Excluir visitas del equipo interno, filtrar bots conocidos y deduplicar vistas de la misma IP dentro de 30 min.</li>
    </ul>
  </div>

  <!-- ── COSTOS ── -->
  <div class="ay-card">
    <h3>Pestaña Costos <span class="ay-shortcut">Pro y Business</span></h3>
    <p>Elige cómo registras tus gastos:</p>
    <ul>
      <li><b>Por venta</b> — Cada costo se asigna a una venta. Ideal para ver margen y rentabilidad por proyecto.</li>
      <li><b>Por empresa</b> — Los costos son gastos generales del negocio. Ideal para rentabilidad global mensual.</li>
      <li><b>Ambos</b> <span class="ay-shortcut">Business</span> — Lo combina: gastos por venta y generales. Máximo control.</li>
    </ul>
    <p style="margin-top:6px">Cambiar de modo <b>no borra</b> los costos ya registrados; solo cambia cómo capturas los nuevos.</p>
  </div>

  <!-- ── MARKETING ── -->
  <div class="ay-card">
    <h3>Pestaña Marketing <span class="ay-shortcut">Business</span></h3>
    <p>Conecta tus <b>pixels de seguimiento</b> para medir y hacer remarketing. Prende el que uses y pega tu ID:</p>
    <ul>
      <li><b>Meta Pixel</b> (Facebook/Instagram) + token de Conversions API opcional.</li>
      <li><b>Google Analytics 4</b> (ID G-XXXXXXXXXX).</li>
      <li><b>Google Ads</b> (Conversion ID + Label).</li>
      <li><b>TikTok Pixel</b>.</li>
    </ul>
    <p style="margin-top:6px">Los scripts se inyectan solos en las cotizaciones y ventas públicas. Se disparan eventos cuando el cliente <b>abre</b>, <b>acepta</b> o <b>rechaza</b> una cotización. La configuración de cada pixel es responsabilidad tuya (consulta la doc oficial de cada plataforma).</p>
  </div>

  <!-- ── HISTORIAL ── -->
  <div class="ay-card">
    <h3>Pestaña Historial <span class="ay-shortcut">Business</span></h3>
    <p>Captura tus números de <b>antes de usar CotizaCloud</b> (mes a mes: cotizaciones, monto, ventas, monto). El sistema los usa como base para tus reportes y para calcular mejor la tasa de cierre del Radar. Verás totales y tasa de cierre promedio.</p>
  </div>

  <!-- ── TERMÓMETRO ── -->
  <div class="ay-card">
    <h3>Pestaña Termómetro <span class="ay-shortcut">Business</span></h3>
    <p>Un solo interruptor: <b>mostrar el termómetro a los asesores</b>. Si lo prendes, cada asesor ve su score, su diagnóstico y el leaderboard en su panel. Si lo apagas, solo tú lo ves en el panel ejecutivo.</p>
  </div>

  <!-- ── FEEDBACK ── -->
  <div class="ay-card">
    <h3>Pestaña Feedback <span class="ay-shortcut">Todos</span></h3>
    <p>Pide a tus clientes que <b>califiquen la atención</b> directamente al final de la cotización pública (5 estrellas + comentario). Es una calificación por cotización y solo te llega a ti.</p>
    <ul>
      <li>Préndelo con el interruptor y <b>personaliza los textos</b>: pregunta principal, texto secundario, placeholder del comentario y mensaje de agradecimiento.</li>
      <li>Tienes una <b>vista previa</b> en vivo de cómo lo verá el cliente.</li>
    </ul>
  </div>

  <!-- ── SUSCRIPCIÓN ── -->
  <div class="ay-card">
    <h3>Pestaña Suscripción</h3>
    <p>Tu plan actual, próximo cobro e historial de pagos. Desde aquí puedes cambiar o cancelar tu plan.</p>
    <div class="ay-warn">En la <b>app de iPhone</b> esta gestión no aparece (regla de Apple). Para cambiar tu plan, abre <b>cotiza.cloud</b> en Safari o Chrome.</div>
  </div>

  <div class="ay-warn">Solo los administradores ven Configuración. Si no la encuentras en el menú, pídele a tu administrador que te dé acceso.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  FAQ                                                   -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-faq">
  <h2 class="ay-h2">❓ Preguntas frecuentes</h2>
  <p class="ay-subtitle">Respuestas rápidas a las dudas más comunes.</p>

  <details class="ay-faq">
    <summary>¿El cliente necesita crear cuenta para ver la cotización?</summary>
    <div class="ay-faq-body">No. El cliente solo necesita el link. Puede ver la cotización, aceptarla o rechazarla sin registrarse ni descargar nada.</div>
  </details>

  <details class="ay-faq">
    <summary>¿Cómo sé si el cliente ya vio mi cotización?</summary>
    <div class="ay-faq-body">Ve al <b>Radar</b>. Ahí aparecen todas las cotizaciones con actividad. También puedes ver el estado de cada cotización en el listado — cambia de "Enviada" a "Vista" cuando el cliente la abre.</div>
  </details>

  <details class="ay-faq">
    <summary>¿Puedo editar una cotización después de enviarla?</summary>
    <div class="ay-faq-body">Sí. Los cambios se reflejan en tiempo real en el link del cliente. Ten en cuenta que si el cliente la está viendo en ese momento, verá los cambios al refrescar.</div>
  </details>

  <details class="ay-faq">
    <summary>¿Cómo funciona el Score% del Radar?</summary>
    <div class="ay-faq-body">El Score% es una probabilidad calculada automáticamente basada en el comportamiento real del cliente: cuántas veces abrió la cotización, si revisó el precio, si volvió después de días, si la compartió con alguien, etc. Se calibra con tu historial de ventas real — entre más ventas cierres, más preciso se vuelve.</div>
  </details>

  <details class="ay-faq">
    <summary>¿Puedo usar cotiza.cloud desde el celular?</summary>
    <div class="ay-faq-body">Sí. El sistema es 100% responsive. Puedes crear cotizaciones, ver el Radar y gestionar ventas desde tu celular. Solo abre tu navegador y entra a tu subdominio (tuempresa.cotiza.cloud).</div>
  </details>

  <details class="ay-faq">
    <summary>¿Qué pasa cuando el cliente acepta la cotización?</summary>
    <div class="ay-faq-body">Se crea una venta automáticamente. La cotización cambia a estado "Aceptada" y aparece en el módulo de Ventas donde puedes registrar pagos y emitir recibos.</div>
  </details>

  <details class="ay-faq">
    <summary>¿Mis datos están seguros?</summary>
    <div class="ay-faq-body">Sí. Cada empresa tiene sus datos completamente aislados. Los asesores solo ven sus propias cotizaciones (salvo que el admin les dé acceso ampliado). Todas las conexiones son cifradas con HTTPS.</div>
  </details>

  <details class="ay-faq">
    <summary>¿Puedo tener varios asesores en mi cuenta?</summary>
    <div class="ay-faq-body">En los planes <b>Pro y Business</b> sí: en <b>Configuración → Usuarios</b> agregas usuarios ilimitados (sujeto a uso justo). Cada uno tiene su login y ve solo sus cotizaciones; el administrador ve todo. El plan Lite incluye 1 usuario.</div>
  </details>

  <details class="ay-faq">
    <summary>¿El Radar funciona si envío la cotización como PDF?</summary>
    <div class="ay-faq-body">No. El Radar solo funciona cuando el cliente abre la cotización desde el link de cotiza.cloud. Si le mandas un PDF, screenshot o la imprimes, no hay forma de rastrear si la vio.</div>
  </details>

  <details class="ay-faq">
    <summary>¿Puedo crear cupones de descuento?</summary>
    <div class="ay-faq-body">Sí. En <b>Configuración → Cupones</b> puedes crear códigos de descuento con porcentaje o monto fijo. El cliente puede aplicarlo directamente en la cotización.</div>
  </details>

  <div class="ay-card" style="margin-top:24px;text-align:center">
    <h3>¿No encontraste tu respuesta?</h3>
    <p>Escríbenos por WhatsApp y te ayudamos en minutos.</p>
  </div>
</div>

</div><!-- .ay-body -->
</div><!-- .ay-wrap -->

<script>
// ── Upload area: click, drag & drop, preview ──
(function(){
  var area = document.getElementById('uploadArea');
  var input = document.getElementById('tk-img');
  var ph = document.getElementById('uploadPlaceholder');
  var pv = document.getElementById('uploadPreview');
  var img = document.getElementById('previewImg');
  if (!area) return;

  area.addEventListener('click', function(){ input.click(); });
  input.addEventListener('change', function(){ if(input.files[0]) showPreview(input.files[0]); });

  area.addEventListener('dragover', function(e){ e.preventDefault(); area.classList.add('dragover'); });
  area.addEventListener('dragleave', function(){ area.classList.remove('dragover'); });
  area.addEventListener('drop', function(e){
    e.preventDefault(); area.classList.remove('dragover');
    if(e.dataTransfer.files[0]){
      input.files = e.dataTransfer.files;
      showPreview(e.dataTransfer.files[0]);
    }
  });

  window.showPreview = function(file){
    if(!file.type.startsWith('image/')){ return; }
    var reader = new FileReader();
    reader.onload = function(e){ img.src = e.target.result; ph.style.display='none'; pv.style.display='block'; };
    reader.readAsDataURL(file);
  };
  window.removeImage = function(){
    input.value = ''; img.src = ''; pv.style.display='none'; ph.style.display='flex';
  };
})();

// ── Auto-open section from hash (for redirect after ticket submit) ──
(function(){
  var h = window.location.hash.replace('#','');
  if (h) {
    var link = document.querySelector('.ay-nav a[href="#'+h+'"]');
    if (link) ayTab(h, link);
  }
})();

// ── Prevent double submit ──
(function(){
  var form = document.getElementById('ticketForm');
  if(form) form.addEventListener('submit', function(){
    document.getElementById('btnSubmit').disabled = true;
    document.getElementById('btnSubmit').textContent = 'Enviando...';
  });
})();

function ayTab(id, el) {
  document.querySelectorAll('.ay-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.ay-nav a').forEach(a => a.classList.remove('active'));
  var sec = document.getElementById('sec-' + id);
  if (sec) sec.classList.add('active');
  if (el) el.classList.add('active');
  // Scroll top on mobile
  if (window.innerWidth <= 768) {
    document.querySelector('.ay-body').scrollTop = 0;
    window.scrollTo(0, document.querySelector('.ay-body').offsetTop - 60);
  }
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
