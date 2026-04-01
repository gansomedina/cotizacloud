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
  <a href="#inicio" class="active" onclick="ayTab('inicio',this)"><span class="ay-ico">🏠</span> Bienvenida</a>
  <a href="#primeros-pasos" onclick="ayTab('primeros-pasos',this)"><span class="ay-ico">🚀</span> Primeros pasos</a>
  <a href="#soporte" onclick="ayTab('soporte',this)"><span class="ay-ico"><?= ico('mail',14) ?></span> Enviar ticket</a>

  <div class="ay-nav-section">Módulos</div>
  <a href="#dashboard" onclick="ayTab('dashboard',this)"><span class="ay-ico"><?= ico('chart',14) ?></span> Dashboard</a>
  <a href="#clientes" onclick="ayTab('clientes',this)"><span class="ay-ico"><?= ico('eye',14) ?></span> Clientes</a>
  <a href="#cotizaciones" onclick="ayTab('cotizaciones',this)"><span class="ay-ico"><?= ico('file',14) ?></span> Cotizaciones</a>
  <a href="#ventas" onclick="ayTab('ventas',this)"><span class="ay-ico"><?= ico('money',14) ?></span> Ventas</a>
  <a href="#radar" onclick="ayTab('radar',this)"><span class="ay-ico"><?= ico('target',14) ?></span> Radar</a>
  <a href="#costos" onclick="ayTab('costos',this)"><span class="ay-ico"><?= ico('chart',14) ?></span> Costos</a>
  <a href="#reportes" onclick="ayTab('reportes',this)"><span class="ay-ico"><?= ico('file',14) ?></span> Reportes</a>

  <div class="ay-nav-section">Admin</div>
  <a href="#configuracion" onclick="ayTab('configuracion',this)"><span class="ay-ico"><?= ico('target',14) ?></span> Configuración</a>
  <a href="#faq" onclick="ayTab('faq',this)"><span class="ay-ico"><?= ico('bulb',14) ?></span> Preguntas frecuentes</a>
</nav>

<!-- ── Contenido ── -->
<div class="ay-body">

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
      <p>Una vez enviada, ve a <b>Radar</b>. Ahí verás cuándo el cliente abre la cotización, qué secciones revisa y cuánto tiempo dedica. Esto te dice cuándo es el mejor momento para dar seguimiento.</p>
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
    <p class="ay-subtitle">Activa tu plan Pro o Business para crear cotizaciones ilimitadas. Selecciona el plan y la duracion deseada y te contactaremos con la liga de cobro.</p>
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
          <option value="Pro">Pro — $299/mes</option>
          <option value="Business">Business — $799/mes</option>
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
  <h2 class="ay-h2"><?= ico('chart',18,'#2563eb') ?> Dashboard</h2>
  <p class="ay-subtitle">Tu vista ejecutiva. De un vistazo sabes cómo va tu negocio.</p>

  <div class="ay-card">
    <h3>¿Qué encuentras aquí?</h3>
    <ul>
      <li><b>KPIs principales</b> — Total de cotizaciones, ventas cerradas, tasa de cierre y monto total vendido</li>
      <li><b>Gráficas de tendencia</b> — Visualiza cómo evolucionan tus ventas en el tiempo</li>
      <li><b>Filtro por período</b> — Mes actual, mes anterior, últimos 30 días, 90 días o año completo</li>
    </ul>
  </div>

  <div class="ay-tip">Revisa tu dashboard al inicio de cada día para tener contexto antes de empezar a vender.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  CLIENTES                                              -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-clientes">
  <h2 class="ay-h2">👥 Clientes</h2>
  <p class="ay-subtitle">Tu base de datos de clientes. Centraliza toda la información de contacto.</p>

  <div class="ay-card">
    <h3>Funciones principales</h3>
    <ul>
      <li><b>Crear cliente</b> — Nombre, teléfono, email, dirección y notas</li>
      <li><b>Buscar</b> — Encuentra clientes por nombre, teléfono o email</li>
      <li><b>Historial</b> — Ve todas las cotizaciones y ventas asociadas a cada cliente</li>
      <li><b>Editar</b> — Actualiza datos de contacto en cualquier momento</li>
    </ul>
  </div>

  <div class="ay-steps">
    <div class="ay-step">
      <h4>Crea un nuevo cliente</h4>
      <p>Haz clic en <b>"Nuevo cliente"</b>, llena al menos el nombre y un dato de contacto (teléfono o email), y guarda.</p>
    </div>
    <div class="ay-step">
      <h4>Asocia cotizaciones</h4>
      <p>Al crear una cotización, seleccionas el cliente. Todo queda vinculado automáticamente.</p>
    </div>
  </div>

  <div class="ay-tip">Siempre registra el teléfono del cliente — es el canal más efectivo para dar seguimiento en ventas de alto valor.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  COTIZACIONES                                          -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-cotizaciones">
  <h2 class="ay-h2">📄 Cotizaciones</h2>
  <p class="ay-subtitle">El corazón del sistema. Crea propuestas profesionales que tus clientes pueden ver, aceptar o rechazar desde un link.</p>

  <div class="ay-card">
    <h3>Estados de una cotización</h3>
    <ul>
      <li><span class="ay-shortcut">Borrador</span> Aún no se envía al cliente. Puedes editarla libremente.</li>
      <li><span class="ay-shortcut">Enviada</span> El cliente recibió el link pero no la ha abierto.</li>
      <li><span class="ay-shortcut">Vista</span> El cliente abrió la cotización. El Radar empieza a rastrear.</li>
      <li><span class="ay-shortcut">Aceptada</span> El cliente aceptó. Se convierte en venta automáticamente.</li>
      <li><span class="ay-shortcut">Rechazada</span> El cliente rechazó la propuesta.</li>
    </ul>
  </div>

  <div class="ay-card">
    <h3>Crear una cotización</h3>
    <div class="ay-steps">
      <div class="ay-step">
        <h4>Selecciona o crea el cliente</h4>
        <p>Busca un cliente existente o crea uno nuevo directamente desde el formulario de cotización.</p>
      </div>
      <div class="ay-step">
        <h4>Agrega productos o servicios</h4>
        <p>Selecciona del catálogo o agrega líneas personalizadas con descripción, cantidad y precio.</p>
      </div>
      <div class="ay-step">
        <h4>Revisa y envía</h4>
        <p>Verifica totales, agrega notas si es necesario, y presiona <b>Enviar</b>. El cliente recibe un link que puede ver desde cualquier dispositivo.</p>
      </div>
    </div>
  </div>

  <div class="ay-card">
    <h3>¿Qué ve el cliente?</h3>
    <p>El cliente recibe un link (ej: <code>tuempresa.cotiza.cloud/c/abc123</code>) que abre una página profesional con tu logo, productos, precios y totales. Puede aceptar o rechazar directamente desde ahí.</p>
  </div>

  <div class="ay-tip">Envía la cotización por WhatsApp para mejor tasa de apertura. El link funciona en cualquier dispositivo sin necesidad de descargar nada.</div>

  <div class="ay-warn">Una vez enviada, editar la cotización actualiza lo que el cliente ve en tiempo real. Ten cuidado si el cliente ya la está revisando.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  VENTAS                                                -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-ventas">
  <h2 class="ay-h2">🛒 Ventas</h2>
  <p class="ay-subtitle">Gestiona tus ventas cerradas. Registra pagos, emite recibos y controla entregas.</p>

  <div class="ay-card">
    <h3>Estados de una venta</h3>
    <ul>
      <li><span class="ay-shortcut">Pendiente</span> Venta creada, sin pagos registrados.</li>
      <li><span class="ay-shortcut">Parcial</span> El cliente ha hecho abonos pero falta saldo.</li>
      <li><span class="ay-shortcut">Pagada</span> El monto total fue cubierto.</li>
      <li><span class="ay-shortcut">Entregada</span> Producto/servicio entregado al cliente.</li>
      <li><span class="ay-shortcut">Cancelada</span> Venta cancelada.</li>
    </ul>
  </div>

  <div class="ay-card">
    <h3>Registrar pagos</h3>
    <div class="ay-steps">
      <div class="ay-step">
        <h4>Abre la venta</h4>
        <p>Haz clic en la venta desde el listado para ver el detalle.</p>
      </div>
      <div class="ay-step">
        <h4>Registra un abono</h4>
        <p>Ingresa el monto del pago y el método (efectivo, transferencia, tarjeta, etc.). El sistema calcula el saldo restante automáticamente.</p>
      </div>
      <div class="ay-step">
        <h4>Emite recibo</h4>
        <p>Cada pago genera un recibo con folio que puedes compartir con el cliente como comprobante.</p>
      </div>
    </div>
  </div>

  <div class="ay-tip">El cliente también puede ver el estado de su venta y saldo pendiente desde un link público — ideal para que sepa cuánto debe sin tener que preguntar.</div>
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

  <div class="ay-card">
    <h3>Buckets principales</h3>
    <ul>
      <li><b><?= ico('target',12,'#92400e') ?> Probable cierre</b> — Tu lista de trabajo #1. Cotizaciones con intención confirmada desde múltiples señales. Contacta HOY.</li>
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
<!--  COSTOS                                                -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-costos">
  <h2 class="ay-h2">📉 Costos</h2>
  <p class="ay-subtitle">Controla los gastos asociados a tus ventas para conocer tu margen real.</p>

  <div class="ay-card">
    <h3>Funciones principales</h3>
    <ul>
      <li><b>Registrar gastos</b> — Cada gasto con monto, categoría, descripción y fecha</li>
      <li><b>Categorías</b> — Organiza por tipo: materiales, mano de obra, transporte, etc.</li>
      <li><b>Asociar a ventas</b> — Vincula gastos a ventas específicas para calcular margen</li>
      <li><b>Filtrar</b> — Busca por categoría, fecha o descripción</li>
    </ul>
  </div>

  <div class="ay-tip">Registra los costos al momento de incurrir en ellos, no al final del mes. Así siempre tendrás visibilidad real de tu rentabilidad.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  REPORTES                                              -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-reportes">
  <h2 class="ay-h2"><?= ico('file',18,'#7c3aed') ?> Reportes</h2>
  <p class="ay-subtitle">Análisis de rendimiento de tu negocio con datos reales, filtrados por período.</p>

  <div class="ay-card">
    <h3>Tipos de reporte</h3>
    <ul>
      <li><b>Financiero</b> — Ingresos, gastos, margen bruto por período</li>
      <li><b>Asesores</b> — Rendimiento individual de cada vendedor del equipo</li>
      <li><b>Cotizaciones</b> — Tasa de cierre, tiempo promedio de cierre, cotizaciones más vistas</li>
      <li><b>Costos</b> — Desglose de gastos por categoría</li>
    </ul>
  </div>

  <div class="ay-card">
    <h3>Filtro por período</h3>
    <p>Todos los reportes permiten filtrar por:</p>
    <ul>
      <li>Mes actual</li>
      <li>Mes anterior</li>
      <li>Últimos 30 días</li>
      <li>Últimos 90 días</li>
      <li>Año completo</li>
    </ul>
  </div>

  <div class="ay-tip">Revisa el reporte de asesores semanalmente si tienes equipo de ventas. Te ayuda a identificar quién necesita apoyo y quién está sobresaliendo.</div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  CONFIGURACIÓN                                         -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="ay-section" id="sec-configuracion">
  <h2 class="ay-h2">⚙️ Configuración</h2>
  <p class="ay-subtitle">Personaliza tu cuenta, equipo y catálogo. Solo visible para administradores.</p>

  <div class="ay-card">
    <h3>Secciones de configuración</h3>
    <ul>
      <li><b>Empresa</b> — Nombre, logo, datos fiscales, moneda, condiciones comerciales. Todo esto aparece en tus cotizaciones.</li>
      <li><b>Catálogo</b> — Tus productos/servicios con SKU, nombre, descripción y precio. Puedes agregar, editar o desactivar artículos.</li>
      <li><b>Clientes</b> — Gestión masiva de tu base de clientes.</li>
      <li><b>Cupones</b> — Crea códigos de descuento que tus clientes pueden aplicar a cotizaciones.</li>
      <li><b>Usuarios</b> — Agrega asesores y administradores. Cada usuario tiene su login y puedes asignar permisos.</li>
      <li><b>Radar</b> — Ajusta la sensibilidad del radar (agresivo, medio, ligero) y configura IPs internas para excluir vistas propias.</li>
    </ul>
  </div>

  <div class="ay-card">
    <h3>Roles de usuario</h3>
    <ul>
      <li><b>Admin</b> — Acceso total: configuración, todos los clientes, todas las cotizaciones, reportes completos</li>
      <li><b>Asesor</b> — Ve solo sus propias cotizaciones y clientes (salvo que tenga permiso especial)</li>
    </ul>
  </div>

  <div class="ay-warn">Solo los administradores pueden acceder a Configuración. Si no ves esta opción en el menú, contacta a tu administrador.</div>
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
    <div class="ay-faq-body">Sí. En <b>Configuración → Usuarios</b> puedes agregar tantos asesores como necesites. Cada uno tiene su login y ve solo sus cotizaciones. El administrador ve todo.</div>
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
