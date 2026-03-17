<?php
// ============================================================
//  CotizaApp — modules/auth/landing.php
//  GET / — Landing page informativa de CotizaCloud
// ============================================================

defined('COTIZAAPP') or die;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cotiza.cloud — Sistema de cotizaciones para profesionales</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f4f4f0;--white:#fff;--border:#e2e2dc;--border2:#c8c8c0;
  --text:#1a1a18;--t2:#4a4a46;--t3:#6a6a64;
  --g:#1a5c38;--g2:#164f30;--g-bg:#eef7f2;--g-border:#b8ddc8;--g-light:#e6f4ed;
  --r:14px;--r-sm:10px;
  --sh:0 1px 3px rgba(0,0,0,.06);--sh-md:0 4px 20px rgba(0,0,0,.08);--sh-lg:0 8px 40px rgba(0,0,0,.10);
  --body:'Plus Jakarta Sans',sans-serif;--num:'DM Sans',sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--body);background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased;overflow-x:hidden}

/* NAV */
.nav{background:rgba(255,255,255,.85);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100}
.nav-inner{max-width:1100px;margin:0 auto;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.logo-mark{width:36px;height:36px;border-radius:10px;background:var(--g);display:flex;align-items:center;justify-content:center}
.logo-mark svg{width:19px;height:19px;fill:none;stroke:#fff;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
.logo-name{font:800 19px var(--body);letter-spacing:-.02em;color:var(--text)}
.logo-name span{color:var(--g)}
.nav-links{display:flex;align-items:center;gap:8px}
.nav-link{padding:8px 16px;border-radius:var(--r-sm);font:600 14px var(--body);text-decoration:none;transition:all .12s}
.nav-link-ghost{color:var(--t2);background:transparent}
.nav-link-ghost:hover{background:var(--bg)}
.nav-link-primary{background:var(--g);color:#fff}
.nav-link-primary:hover{opacity:.88}

/* HERO */
.hero{max-width:1100px;margin:0 auto;padding:80px 24px 48px;text-align:center}
.hero-badge{display:inline-flex;align-items:center;gap:8px;padding:7px 16px;border-radius:20px;background:var(--g-bg);border:1px solid var(--g-border);font:700 12px var(--body);color:var(--g);margin-bottom:24px;letter-spacing:.02em;text-transform:uppercase}
.hero-badge-dot{width:7px;height:7px;border-radius:50%;background:var(--g);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font:800 clamp(34px,5.5vw,56px)/1.08 var(--body);letter-spacing:-.035em;margin-bottom:20px}
.hero h1 em{font-style:normal;color:var(--g);position:relative}
.hero-sub{font:400 clamp(16px,2vw,19px)/1.6 var(--body);color:var(--t2);max-width:580px;margin:0 auto 36px}
.hero-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:20px}
.btn-hero{padding:15px 32px;border-radius:var(--r-sm);font:700 15px var(--body);text-decoration:none;transition:all .15s;border:none;cursor:pointer}
.btn-hero-primary{background:var(--g);color:#fff;box-shadow:0 2px 12px rgba(26,92,56,.3)}
.btn-hero-primary:hover{background:var(--g2);box-shadow:0 4px 20px rgba(26,92,56,.4)}
.btn-hero-secondary{background:var(--white);color:var(--t2);border:1.5px solid var(--border2)}
.btn-hero-secondary:hover{border-color:var(--g);color:var(--g)}
.hero-note{font:400 13px var(--body);color:var(--t3)}

/* QUOTE */
.quote-section{max-width:800px;margin:0 auto;padding:20px 24px 60px}
.quote-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:36px 40px;box-shadow:var(--sh-md);position:relative}
.quote-card::before{content:"\201C";font:800 72px var(--body);color:var(--g);opacity:.2;position:absolute;top:8px;left:20px;line-height:1}
.quote-text{font:500 clamp(17px,2.2vw,21px)/1.6 var(--body);color:var(--text);text-align:center;position:relative;z-index:1}
.quote-text strong{color:var(--g);font-weight:700}

/* FLOW */
.flow{max-width:1100px;margin:0 auto;padding:60px 24px 80px}
.section-label{display:block;text-align:center;font:700 11px var(--body);letter-spacing:.1em;text-transform:uppercase;color:var(--g);margin-bottom:10px}
.section-title{text-align:center;font:800 clamp(24px,3.5vw,34px)/1.15 var(--body);letter-spacing:-.02em;margin-bottom:12px}
.section-sub{text-align:center;font:400 15px var(--body);color:var(--t3);margin-bottom:48px;max-width:540px;margin-left:auto;margin-right:auto}
.flow-steps{display:flex;gap:0;justify-content:center;flex-wrap:wrap;position:relative}
.flow-step{flex:1;min-width:160px;max-width:200px;text-align:center;position:relative;padding:0 8px}
.flow-step-num{width:48px;height:48px;border-radius:50%;background:var(--g);color:#fff;font:800 18px var(--num);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 2px 12px rgba(26,92,56,.25);position:relative;z-index:2}
.flow-step-title{font:700 14px var(--body);margin-bottom:4px}
.flow-step-desc{font:400 12.5px var(--body);color:var(--t3);line-height:1.5}
.flow-arrow{display:flex;align-items:flex-start;padding-top:22px;color:var(--g-border)}
.flow-arrow svg{width:24px;height:24px;stroke:var(--g-border);stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round}

/* FEATURES */
.features{max-width:1100px;margin:0 auto;padding:40px 24px 80px}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.feat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:28px 24px;box-shadow:var(--sh);transition:all .2s}
.feat-card:hover{box-shadow:var(--sh-lg);transform:translateY(-2px)}
.feat-ico{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px}
.feat-ico svg{width:22px;height:22px;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;fill:none}
.feat-tag{display:inline-block;font:700 10px var(--body);letter-spacing:.06em;text-transform:uppercase;padding:3px 8px;border-radius:4px;margin-bottom:10px}
.feat-title{font:700 16px var(--body);margin-bottom:6px;letter-spacing:-.01em}
.feat-desc{font:400 13.5px var(--body);color:var(--t3);line-height:1.6}

/* HIGHLIGHT */
.highlight{max-width:1100px;margin:0 auto;padding:0 24px 80px}
.hl-card{background:linear-gradient(135deg,var(--g) 0%,#0f3d24 100%);border-radius:20px;padding:56px 48px;color:#fff;display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;box-shadow:0 8px 40px rgba(26,92,56,.2)}
.hl-title{font:800 clamp(24px,3vw,32px)/1.15 var(--body);letter-spacing:-.02em;margin-bottom:12px}
.hl-desc{font:400 15px var(--body);opacity:.85;line-height:1.7;margin-bottom:24px}
.hl-list{list-style:none;display:flex;flex-direction:column;gap:12px}
.hl-list li{display:flex;align-items:center;gap:10px;font:500 14px var(--body)}
.hl-list li::before{content:"";width:8px;height:8px;border-radius:50%;background:#4ade80;flex-shrink:0}
.hl-stats{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.hl-stat{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:14px;padding:24px;text-align:center;backdrop-filter:blur(4px)}
.hl-stat-ico{font-size:28px;margin-bottom:8px}
.hl-stat-title{font:700 15px var(--body);margin-bottom:2px}
.hl-stat-desc{font:400 12px var(--body);opacity:.7;line-height:1.4}

/* CTA */
.cta{max-width:700px;margin:0 auto;padding:0 24px 80px;text-align:center}
.cta-card{background:var(--white);border:2px solid var(--g-border);border-radius:20px;padding:52px 36px}
.cta h2{font:800 clamp(24px,3.5vw,32px)/1.15 var(--body);letter-spacing:-.02em;margin-bottom:8px}
.cta h2 span{color:var(--g)}
.cta-sub{font:400 15px var(--body);color:var(--t3);margin-bottom:28px;line-height:1.6}
.btn-cta{display:inline-block;padding:16px 36px;border-radius:var(--r-sm);background:var(--g);color:#fff;font:700 16px var(--body);text-decoration:none;transition:all .15s;box-shadow:0 2px 12px rgba(26,92,56,.3)}
.btn-cta:hover{background:var(--g2);box-shadow:0 4px 20px rgba(26,92,56,.4)}
.cta-note{font:400 13px var(--body);color:var(--t3);margin-top:14px}

/* FOOTER */
.footer{border-top:1px solid var(--border);padding:24px;text-align:center;font:400 13px var(--body);color:var(--t3)}
.footer a{color:var(--g);text-decoration:none}

@media(max-width:768px){
  .hero{padding:52px 20px 36px}
  .nav-link-ghost{display:none}
  .features-grid{grid-template-columns:1fr}
  .flow-arrow{display:none}
  .flow-steps{flex-direction:column;align-items:center;gap:20px}
  .flow-step{max-width:280px}
  .hl-card{grid-template-columns:1fr;padding:36px 24px}
  .hl-stats{grid-template-columns:1fr 1fr}
  .quote-card{padding:28px 24px}
}
@media(max-width:480px){
  .features-grid{grid-template-columns:1fr}
  .hl-stats{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <div class="nav-inner">
    <a href="/" class="nav-logo">
      <div class="logo-mark">
        <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
      </div>
      <div class="logo-name">Cotiza<span>.cloud</span></div>
    </a>
    <div class="nav-links">
      <a href="/login" class="nav-link nav-link-ghost">Iniciar sesion</a>
      <a href="/registro" class="nav-link nav-link-primary">Crear cuenta</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><div class="hero-badge-dot"></div>Sistema de cotizaciones para profesionales</div>
  <h1>El secreto mejor guardado<br><em>del profesional</em></h1>
  <p class="hero-sub">No solo mandas cotizaciones. Sabes cuales estan vivas, cuales se estan negociando y cuales estan a punto de cerrar.</p>
  <div class="hero-btns">
    <a href="/registro" class="btn-hero btn-hero-primary">Empieza gratis</a>
    <a href="/login" class="btn-hero btn-hero-secondary">Ya tengo cuenta</a>
  </div>
  <p class="hero-note">Sin tarjeta de credito. Listo en 30 segundos.</p>
</section>

<!-- QUOTE -->
<section class="quote-section">
  <div class="quote-card">
    <p class="quote-text">No solo mandas cotizaciones: sabes cuales estan <strong>vivas</strong>, cuales estan <strong>negociandose</strong> y cuales estan <strong>cerca de cerrar</strong>.</p>
  </div>
</section>

<!-- FLOW: Cómo funciona -->
<section class="flow">
  <span class="section-label">Asi funciona</span>
  <div class="section-title">De la cotizacion al cierre, en automatico</div>
  <p class="section-sub">Cada paso genera inteligencia sobre tu cliente. Sin que hagas nada.</p>

  <div class="flow-steps">
    <div class="flow-step">
      <div class="flow-step-num">1</div>
      <div class="flow-step-title">Genera la cotizacion</div>
      <div class="flow-step-desc">Crea una cotizacion profesional con tu marca y catalogo en segundos.</div>
    </div>
    <div class="flow-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
    <div class="flow-step">
      <div class="flow-step-num">2</div>
      <div class="flow-step-title">Comparte por link</div>
      <div class="flow-step-desc">Envia por WhatsApp, correo o donde quieras. Tu cliente la abre sin instalar nada.</div>
    </div>
    <div class="flow-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
    <div class="flow-step">
      <div class="flow-step-num">3</div>
      <div class="flow-step-title">Mide la interaccion</div>
      <div class="flow-step-desc">Sabes cuando la abrio, cuantas veces la vio y cuanto tiempo le dedico.</div>
    </div>
    <div class="flow-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
    <div class="flow-step">
      <div class="flow-step-num">4</div>
      <div class="flow-step-title">Detecta interes</div>
      <div class="flow-step-desc">El sistema califica automaticamente el nivel de interes de cada cotizacion.</div>
    </div>
    <div class="flow-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
    <div class="flow-step">
      <div class="flow-step-num">5</div>
      <div class="flow-step-title">Alerta lo caliente</div>
      <div class="flow-step-desc">Recibe alertas de las cotizaciones que estan listas para cerrar.</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <span class="section-label">Todo incluido</span>
  <div class="section-title">Herramientas que trabajan por ti</div>
  <p class="section-sub">Todo lo que necesitas para cotizar, vender y controlar tu negocio.</p>

  <div class="features-grid">
    <div class="feat-card">
      <div class="feat-ico" style="background:#ede9fe">
        <svg viewBox="0 0 24 24" stroke="#6d28d9"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <div class="feat-tag" style="background:#ede9fe;color:#6d28d9">Exclusivo</div>
      <div class="feat-title">Radar de interes en tiempo real</div>
      <div class="feat-desc">Monitoreo automatico de cada cotizacion. Detecta cuando tu cliente la abre, cuantas veces regresa y que tan caliente esta. Sin que muevas un dedo.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#fef3c7">
        <svg viewBox="0 0 24 24" stroke="#92400e"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="feat-tag" style="background:#fef3c7;color:#92400e">Rentabilidad</div>
      <div class="feat-title">Modulo de costos y margen</div>
      <div class="feat-desc">Registra tus costos por venta y categoria. Sabe exactamente cuanto ganas y cual es tu margen real en cada operacion.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:var(--g-bg)">
        <svg viewBox="0 0 24 24" stroke="var(--g)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      </div>
      <div class="feat-tag" style="background:var(--g-bg);color:var(--g)">Cotizaciones</div>
      <div class="feat-title">Cotizaciones profesionales</div>
      <div class="feat-desc">Crea cotizaciones con tu marca, articulos, descuentos y cupones. Compartelas por WhatsApp con un link unico.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><rect x="1" y="3" width="22" height="18" rx="2"/><line x1="1" y1="9" x2="23" y2="9"/><line x1="8" y1="15" x2="16" y2="15"/></svg>
      </div>
      <div class="feat-tag" style="background:#dbeafe;color:#1d4ed8">Pagos</div>
      <div class="feat-title">Recibos y control de pagos</div>
      <div class="feat-desc">Genera recibos profesionales, registra abonos parciales o totales y controla el saldo pendiente de cada cliente.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#fce7f3">
        <svg viewBox="0 0 24 24" stroke="#be185d"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div class="feat-tag" style="background:#fce7f3;color:#be185d">Reportes</div>
      <div class="feat-title">Reportes de ventas y costos</div>
      <div class="feat-desc">Visualiza tus ventas, ingresos y gastos con reportes claros. Entiende la rentabilidad real de tu negocio de un vistazo.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="feat-tag" style="background:#dbeafe;color:#1d4ed8">Equipo</div>
      <div class="feat-title">Multi-usuario y permisos</div>
      <div class="feat-desc">Agrega vendedores con permisos granulares. Controla quien puede editar precios, aplicar descuentos o ver todas las ventas.</div>
    </div>
  </div>
</section>

<!-- HIGHLIGHT: Radar -->
<section class="highlight">
  <div class="hl-card">
    <div>
      <div class="hl-title">Monitoreo en tiempo real y automatico</div>
      <div class="hl-desc">Mientras tu trabajas, Cotiza.cloud vigila cada cotizacion que enviaste. Cuando un cliente muestra interes real, tu lo sabes primero.</div>
      <ul class="hl-list">
        <li>Sabe cuando abren tu cotizacion</li>
        <li>Cuenta cuantas veces la revisaron</li>
        <li>Clasifica el nivel de interes automaticamente</li>
        <li>Te alerta cuando una cotizacion esta caliente</li>
      </ul>
    </div>
    <div class="hl-stats">
      <div class="hl-stat">
        <div class="hl-stat-ico">&#128200;</div>
        <div class="hl-stat-title">Radar en vivo</div>
        <div class="hl-stat-desc">Monitorea cada cotizacion sin que hagas nada</div>
      </div>
      <div class="hl-stat">
        <div class="hl-stat-ico">&#128176;</div>
        <div class="hl-stat-title">Margen real</div>
        <div class="hl-stat-desc">Sabe cuanto ganas en cada venta</div>
      </div>
      <div class="hl-stat">
        <div class="hl-stat-ico">&#9889;</div>
        <div class="hl-stat-title">Alertas</div>
        <div class="hl-stat-desc">Recibe avisos de cotizaciones calientes</div>
      </div>
      <div class="hl-stat">
        <div class="hl-stat-ico">&#128203;</div>
        <div class="hl-stat-title">Recibos</div>
        <div class="hl-stat-desc">Genera comprobantes de pago al instante</div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <div class="cta-card">
    <h2>Empieza a cotizar <span>como profesional</span></h2>
    <p class="cta-sub">Crea tu cuenta en 30 segundos. Sin tarjeta de credito, sin contratos, sin complicaciones.</p>
    <a href="/registro" class="btn-cta">Crear cuenta gratis</a>
    <p class="cta-note">Usado por profesionales que saben que el seguimiento cierra ventas.</p>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  Cotiza.cloud &copy; <?= date('Y') ?> &middot; <a href="/login">Iniciar sesion</a> &middot; <a href="/registro">Crear cuenta</a>
</footer>

</body>
</html>
