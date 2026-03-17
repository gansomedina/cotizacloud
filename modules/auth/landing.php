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
<title>Cotiza.cloud — El secreto mejor guardado del profesional</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f4f4f0;--white:#fff;--border:#e2e2dc;--border2:#c8c8c0;
  --text:#1a1a18;--t2:#4a4a46;--t3:#6a6a64;
  --g:#1a5c38;--g2:#164f30;--g-bg:#eef7f2;--g-border:#b8ddc8;
  --accent:#7c3aed;--accent-bg:#ede9fe;
  --warm:#dc2626;--warm-bg:#fef2f2;
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
.hero{max-width:900px;margin:0 auto;padding:72px 24px 0;text-align:center}
.hero-badge{display:inline-flex;align-items:center;gap:8px;padding:7px 18px;border-radius:20px;background:var(--g-bg);border:1px solid var(--g-border);font:700 11px var(--body);color:var(--g);margin-bottom:28px;letter-spacing:.08em;text-transform:uppercase}
.hero-badge-dot{width:7px;height:7px;border-radius:50%;background:var(--g);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
.hero h1{font:800 clamp(36px,6vw,60px)/1.05 var(--body);letter-spacing:-.04em;margin-bottom:24px}
.hero h1 em{font-style:normal;color:var(--g)}
.hero-sub{font:500 clamp(17px,2.2vw,21px)/1.55 var(--body);color:var(--t2);max-width:620px;margin:0 auto 40px}
.hero-sub strong{color:var(--text);font-weight:700}
.hero-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:16px}
.btn-hero{padding:16px 36px;border-radius:var(--r-sm);font:700 15px var(--body);text-decoration:none;transition:all .18s;border:none;cursor:pointer}
.btn-hero-primary{background:var(--g);color:#fff;box-shadow:0 4px 16px rgba(26,92,56,.3)}
.btn-hero-primary:hover{background:var(--g2);box-shadow:0 6px 24px rgba(26,92,56,.4);transform:translateY(-1px)}
.btn-hero-secondary{background:var(--white);color:var(--t2);border:1.5px solid var(--border2)}
.btn-hero-secondary:hover{border-color:var(--g);color:var(--g)}
.hero-note{font:400 13px var(--body);color:var(--t3)}

/* PROOF BAR */
.proof{max-width:800px;margin:0 auto;padding:48px 24px 0}
.proof-inner{display:flex;justify-content:center;gap:40px;flex-wrap:wrap}
.proof-item{text-align:center}
.proof-num{font:800 28px var(--num);color:var(--g);letter-spacing:-.02em}
.proof-label{font:500 12px var(--body);color:var(--t3);margin-top:2px}

/* MANIFESTO */
.manifesto{max-width:780px;margin:0 auto;padding:64px 24px 0}
.manifesto-card{background:var(--white);border:1.5px solid var(--border);border-radius:20px;padding:44px 48px;box-shadow:var(--sh-md);position:relative;overflow:hidden}
.manifesto-card::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--g),var(--accent))}
.manifesto-text{font:600 clamp(18px,2.4vw,23px)/1.6 var(--body);color:var(--text);text-align:center}
.manifesto-text strong{color:var(--g);font-weight:800}
.manifesto-text em{font-style:normal;color:var(--accent);font-weight:800}

/* DIVIDER */
.divider{max-width:1100px;margin:0 auto;padding:72px 24px 0}
.divider-line{height:1px;background:var(--border)}

/* FLOW */
.flow{max-width:1100px;margin:0 auto;padding:72px 24px 0}
.section-label{display:block;text-align:center;font:700 11px var(--body);letter-spacing:.1em;text-transform:uppercase;color:var(--g);margin-bottom:12px}
.section-title{text-align:center;font:800 clamp(26px,3.8vw,38px)/1.12 var(--body);letter-spacing:-.03em;margin-bottom:12px}
.section-sub{text-align:center;font:400 16px var(--body);color:var(--t3);margin-bottom:56px;max-width:520px;margin-left:auto;margin-right:auto;line-height:1.6}
.flow-steps{display:flex;gap:0;justify-content:center;flex-wrap:wrap;position:relative}
.flow-step{flex:1;min-width:150px;max-width:188px;text-align:center;position:relative;padding:0 6px}
.flow-step-num{width:52px;height:52px;border-radius:50%;background:var(--g);color:#fff;font:800 20px var(--num);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(26,92,56,.25);position:relative;z-index:2}
.flow-step-num.hot{background:var(--warm);box-shadow:0 4px 16px rgba(220,38,38,.25)}
.flow-step-title{font:700 14px var(--body);margin-bottom:5px;letter-spacing:-.01em}
.flow-step-desc{font:400 12.5px var(--body);color:var(--t3);line-height:1.5}
.flow-arrow{display:flex;align-items:flex-start;padding-top:22px;color:var(--g-border)}
.flow-arrow svg{width:20px;height:20px;stroke:var(--border2);stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round}

/* FEATURES */
.features{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.feat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:28px 24px;box-shadow:var(--sh);transition:all .2s}
.feat-card:hover{box-shadow:var(--sh-lg);transform:translateY(-2px)}
.feat-card.featured{border-color:var(--g-border);border-width:2px;position:relative}
.feat-card.featured::after{content:"NUEVO";position:absolute;top:16px;right:16px;font:800 9px var(--body);letter-spacing:.08em;background:var(--warm);color:#fff;padding:3px 8px;border-radius:4px}
.feat-ico{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px}
.feat-ico svg{width:22px;height:22px;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;fill:none}
.feat-tag{display:inline-block;font:700 10px var(--body);letter-spacing:.06em;text-transform:uppercase;padding:3px 8px;border-radius:4px;margin-bottom:10px}
.feat-title{font:700 16px var(--body);margin-bottom:6px;letter-spacing:-.01em}
.feat-desc{font:400 13.5px var(--body);color:var(--t3);line-height:1.65}

/* PREDICTIVE SECTION */
.predictive{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.pred-card{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);border-radius:24px;padding:64px 56px;color:#fff;display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center;box-shadow:0 12px 48px rgba(0,0,0,.2);position:relative;overflow:hidden}
.pred-card::before{content:"";position:absolute;top:-60%;right:-20%;width:400px;height:400px;background:radial-gradient(circle,rgba(124,58,237,.15) 0%,transparent 70%);pointer-events:none}
.pred-card::after{content:"";position:absolute;bottom:-40%;left:-10%;width:300px;height:300px;background:radial-gradient(circle,rgba(26,92,56,.15) 0%,transparent 70%);pointer-events:none}
.pred-label{font:700 11px var(--body);letter-spacing:.1em;text-transform:uppercase;color:#a78bfa;margin-bottom:12px}
.pred-title{font:800 clamp(24px,3.2vw,34px)/1.12 var(--body);letter-spacing:-.02em;margin-bottom:16px}
.pred-desc{font:400 15px var(--body);opacity:.8;line-height:1.7;margin-bottom:28px}
.pred-list{list-style:none;display:flex;flex-direction:column;gap:14px}
.pred-list li{display:flex;align-items:flex-start;gap:12px;font:500 14px var(--body);line-height:1.5}
.pred-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:4px}
.pred-right{position:relative;z-index:1}

/* PIPELINE VISUAL */
.pipeline{display:flex;flex-direction:column;gap:14px}
.pipe-row{display:flex;align-items:center;gap:12px}
.pipe-bar{height:42px;border-radius:10px;display:flex;align-items:center;padding:0 16px;font:700 13px var(--body);transition:all .3s}
.pipe-label{font:500 12px var(--body);opacity:.6;min-width:70px;text-align:right}
.pipe-cold{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);width:100%;color:rgba(255,255,255,.5)}
.pipe-warm{background:rgba(251,191,36,.15);border:1px solid rgba(251,191,36,.25);width:75%;color:#fbbf24}
.pipe-hot{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.25);width:50%;color:#ef4444}
.pipe-close{background:rgba(74,222,128,.15);border:1px solid rgba(74,222,128,.3);width:35%;color:#4ade80}
.pipe-tag{margin-left:auto;font:600 10px var(--body);letter-spacing:.04em;text-transform:uppercase;opacity:.7}

/* TOOLS SECTION */
.tools{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.tools-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.tool-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:32px 28px;box-shadow:var(--sh);display:flex;gap:20px;align-items:flex-start;transition:all .2s}
.tool-card:hover{box-shadow:var(--sh-md)}
.tool-ico{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tool-ico svg{width:22px;height:22px;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;fill:none}
.tool-title{font:700 16px var(--body);margin-bottom:4px;letter-spacing:-.01em}
.tool-desc{font:400 13.5px var(--body);color:var(--t3);line-height:1.6}

/* CTA */
.cta{max-width:720px;margin:0 auto;padding:80px 24px;text-align:center}
.cta-card{background:var(--g);border-radius:24px;padding:56px 40px;position:relative;overflow:hidden}
.cta-card::before{content:"";position:absolute;top:-50%;right:-30%;width:400px;height:400px;background:radial-gradient(circle,rgba(255,255,255,.06) 0%,transparent 70%);pointer-events:none}
.cta h2{font:800 clamp(26px,3.8vw,36px)/1.1 var(--body);letter-spacing:-.03em;margin-bottom:12px;color:#fff}
.cta-sub{font:400 16px var(--body);color:rgba(255,255,255,.8);margin-bottom:32px;line-height:1.6;max-width:440px;margin-left:auto;margin-right:auto}
.btn-cta{display:inline-block;padding:17px 40px;border-radius:var(--r-sm);background:#fff;color:var(--g);font:800 16px var(--body);text-decoration:none;transition:all .18s;box-shadow:0 4px 16px rgba(0,0,0,.15)}
.btn-cta:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(0,0,0,.2)}
.cta-note{font:500 13px var(--body);color:rgba(255,255,255,.6);margin-top:16px}

/* LIVE NOTIFICATIONS */
.notif-stack{position:fixed;top:76px;right:20px;z-index:90;display:flex;flex-direction:column;gap:10px;pointer-events:none}
.notif{background:rgba(255,255,255,.95);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(0,0,0,.06);border-radius:14px;padding:14px 16px;box-shadow:0 6px 24px rgba(0,0,0,.10),0 1px 4px rgba(0,0,0,.05);display:flex;align-items:flex-start;gap:12px;max-width:340px;transform:translateX(120%);opacity:0;transition:all .5s cubic-bezier(.16,1,.3,1);pointer-events:auto}
.notif.show{transform:translateX(0);opacity:1}
.notif-ico{font-size:22px;flex-shrink:0;line-height:1;margin-top:2px}
.notif-body{flex:1;min-width:0}
.notif-title{font:700 13px var(--body);color:var(--text);margin-bottom:3px;letter-spacing:-.01em}
.notif-desc{font:400 12.5px var(--body);color:#64748b;line-height:1.4}
.notif-time{font:500 10px var(--body);color:var(--t3);opacity:.6;flex-shrink:0;margin-top:3px}
.notif-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font:700 10px var(--body);letter-spacing:.02em;margin-top:5px}

/* FOOTER */
.footer{border-top:1px solid var(--border);padding:24px;text-align:center;font:400 13px var(--body);color:var(--t3)}
.footer a{color:var(--g);text-decoration:none}

@media(max-width:768px){
  .notif-stack{right:12px;left:12px}
  .notif{min-width:0;max-width:100%}
  .hero{padding:48px 20px 0}
  .nav-link-ghost{display:none}
  .features-grid{grid-template-columns:1fr}
  .tools-grid{grid-template-columns:1fr}
  .flow-arrow{display:none}
  .flow-steps{flex-direction:column;align-items:center;gap:24px}
  .flow-step{max-width:280px}
  .pred-card{grid-template-columns:1fr;padding:40px 28px}
  .manifesto-card{padding:32px 24px}
  .proof-inner{gap:24px}
}
@media(max-width:480px){
  .features-grid{grid-template-columns:1fr}
  .tools-grid{grid-template-columns:1fr}
  .pipeline{display:none}
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
      <a href="/registro" class="nav-link nav-link-primary">Crear cuenta gratis</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><div class="hero-badge-dot"></div>Sistema de cotizaciones para profesionales</div>
  <h1>El secreto mejor guardado<br><em>del profesional</em></h1>
  <p class="hero-sub">Tus cotizaciones ya son buenas. Lo que te falta es saber <strong>que pasa despues de enviarlas</strong>. Quien las abrio, quien esta comparando y quien esta listo para decir que si.</p>
  <div class="hero-btns">
    <a href="/registro" class="btn-hero btn-hero-primary">Empieza gratis</a>
    <a href="/login" class="btn-hero btn-hero-secondary">Ya tengo cuenta</a>
  </div>
  <p class="hero-note">Sin tarjeta. Sin contratos. Listo en 30 segundos.</p>
</section>

<!-- PROOF -->
<section class="proof">
  <div class="proof-inner">
    <div class="proof-item">
      <div class="proof-num">100%</div>
      <div class="proof-label">Gratis para empezar</div>
    </div>
    <div class="proof-item">
      <div class="proof-num">30s</div>
      <div class="proof-label">Para crear tu cuenta</div>
    </div>
    <div class="proof-item">
      <div class="proof-num">24/7</div>
      <div class="proof-label">Monitoreo activo</div>
    </div>
  </div>
</section>

<!-- MANIFESTO -->
<section class="manifesto">
  <div class="manifesto-card">
    <p class="manifesto-text">No solo mandas cotizaciones: sabes cuales estan <strong>vivas</strong>, cuales se estan <em>negociando</em> y cuales estan <strong>a punto de cerrar</strong>.</p>
  </div>
</section>

<div class="divider"><div class="divider-line"></div></div>

<!-- FLOW -->
<section class="flow">
  <span class="section-label">Tu proceso, potenciado</span>
  <div class="section-title">Tu expertise cierra ventas.<br>Nosotros te decimos a quien llamar primero.</div>
  <p class="section-sub">Cinco pasos que convierten cada cotizacion en inteligencia de ventas para tu negocio.</p>

  <div class="flow-steps">
    <div class="flow-step">
      <div class="flow-step-num">1</div>
      <div class="flow-step-title">Genera la cotizacion</div>
      <div class="flow-step-desc">Con tu marca, tus articulos y tus condiciones. Profesional desde el primer contacto.</div>
    </div>
    <div class="flow-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
    <div class="flow-step">
      <div class="flow-step-num">2</div>
      <div class="flow-step-title">Comparte por link</div>
      <div class="flow-step-desc">WhatsApp, correo, donde sea. Tu cliente la abre al instante, sin descargar nada.</div>
    </div>
    <div class="flow-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
    <div class="flow-step">
      <div class="flow-step-num">3</div>
      <div class="flow-step-title">Mide la interaccion</div>
      <div class="flow-step-desc">Cada apertura, cada visita, cada minuto que tu cliente pasa revisandola queda registrado.</div>
    </div>
    <div class="flow-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
    <div class="flow-step">
      <div class="flow-step-num">4</div>
      <div class="flow-step-title">Detecta el interes</div>
      <div class="flow-step-desc">El sistema identifica patrones de comportamiento y califica el nivel de interes real.</div>
    </div>
    <div class="flow-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
    <div class="flow-step">
      <div class="flow-step-num hot">5</div>
      <div class="flow-step-title">Anticipa el cierre</div>
      <div class="flow-step-desc">Te avisa cuales estan listas. Tu decides el momento exacto para dar seguimiento.</div>
    </div>
  </div>
</section>

<!-- PREDICTIVE -->
<section class="predictive">
  <div class="pred-card">
    <div>
      <div class="pred-label">Inteligencia predictiva</div>
      <div class="pred-title">Sabe que cotizacion se va a cerrar antes de que tu cliente llame</div>
      <div class="pred-desc">El radar analiza el comportamiento de cada cliente en tiempo real y te muestra una radiografia clara de tu pipeline de ventas.</div>
      <ul class="pred-list">
        <li><span class="pred-dot" style="background:rgba(255,255,255,.3)"></span>Cotizaciones enviadas y sin abrir</li>
        <li><span class="pred-dot" style="background:#fbbf24"></span>Clientes que estan comparando opciones</li>
        <li><span class="pred-dot" style="background:#ef4444"></span>Alto interes: multiples visitas recientes</li>
        <li><span class="pred-dot" style="background:#4ade80"></span>Listas para cerrar: el momento es ahora</li>
      </ul>
    </div>
    <div class="pred-right">
      <div class="pipeline">
        <div class="pipe-row">
          <span class="pipe-label">Enviadas</span>
          <div class="pipe-bar pipe-cold">12 cotizaciones<span class="pipe-tag">Sin abrir</span></div>
        </div>
        <div class="pipe-row">
          <span class="pipe-label">Tibias</span>
          <div class="pipe-bar pipe-warm">8 cotizaciones<span class="pipe-tag">Comparando</span></div>
        </div>
        <div class="pipe-row">
          <span class="pipe-label">Calientes</span>
          <div class="pipe-bar pipe-hot">5 cotizaciones<span class="pipe-tag">Alto interes</span></div>
        </div>
        <div class="pipe-row">
          <span class="pipe-label">Por cerrar</span>
          <div class="pipe-bar pipe-close">3 cotizaciones<span class="pipe-tag">Actua ya</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <span class="section-label">Plataforma completa</span>
  <div class="section-title">Cada herramienta que tu negocio necesita</div>
  <p class="section-sub">De la cotizacion al cobro, todo bajo control y en un solo lugar.</p>

  <div class="features-grid">
    <div class="feat-card featured">
      <div class="feat-ico" style="background:var(--accent-bg)">
        <svg viewBox="0 0 24 24" stroke="var(--accent)"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <div class="feat-tag" style="background:var(--accent-bg);color:var(--accent)">Diferenciador</div>
      <div class="feat-title">Radar de interes en tiempo real</div>
      <div class="feat-desc">Monitoreo continuo y automatico de cada cotizacion. Ve quien abrio, quien regreso y quien esta a punto de tomar una decision. Tu ventaja competitiva.</div>
    </div>
    <div class="feat-card featured">
      <div class="feat-ico" style="background:#fef3c7">
        <svg viewBox="0 0 24 24" stroke="#92400e"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="feat-tag" style="background:#fef3c7;color:#92400e">Rentabilidad</div>
      <div class="feat-title">Costos, margen y utilidad por venta</div>
      <div class="feat-desc">Registra el costo de cada operacion. Visualiza tu margen real por venta, por cliente y por periodo. Sabe exactamente cuanto ganas.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:var(--g-bg)">
        <svg viewBox="0 0 24 24" stroke="var(--g)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      </div>
      <div class="feat-tag" style="background:var(--g-bg);color:var(--g)">Cotizaciones</div>
      <div class="feat-title">Cotizaciones con tu marca</div>
      <div class="feat-desc">Articulos, descuentos, cupones, condiciones. Compartelas con un link unico por WhatsApp o correo. Imagen profesional siempre.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><rect x="1" y="3" width="22" height="18" rx="2"/><line x1="1" y1="9" x2="23" y2="9"/><line x1="8" y1="15" x2="16" y2="15"/></svg>
      </div>
      <div class="feat-tag" style="background:#dbeafe;color:#1d4ed8">Cobranza</div>
      <div class="feat-title">Recibos y control de pagos</div>
      <div class="feat-desc">Genera recibos profesionales, registra abonos parciales o totales. Tu cliente ve su saldo en tiempo real. Cero discusiones.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#fce7f3">
        <svg viewBox="0 0 24 24" stroke="#be185d"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div class="feat-tag" style="background:#fce7f3;color:#be185d">Reportes</div>
      <div class="feat-title">Reportes de ventas y costos</div>
      <div class="feat-desc">Dashboards claros con tus ingresos, gastos y utilidad. Filtra por periodo, vendedor o cliente. Decisiones basadas en datos.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="feat-tag" style="background:#dbeafe;color:#1d4ed8">Equipo</div>
      <div class="feat-title">Multi-usuario y permisos</div>
      <div class="feat-desc">Agrega vendedores con permisos granulares. Controla quien cotiza, quien descuenta y quien ve los numeros completos.</div>
    </div>
  </div>
</section>

<!-- EXTRA TOOLS -->
<section class="tools">
  <span class="section-label">Y ademas</span>
  <div class="section-title">Herramientas que completan tu operacion</div>
  <p class="section-sub">Cada detalle pensado para que te enfoques en lo que mejor haces: vender.</p>

  <div class="tools-grid">
    <div class="tool-card">
      <div class="tool-ico" style="background:var(--g-bg)">
        <svg viewBox="0 0 24 24" stroke="var(--g)"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      </div>
      <div>
        <div class="tool-title">Directorio de clientes</div>
        <div class="tool-desc">Historial completo por cliente: cotizaciones, ventas, pagos y nivel de actividad. Todo en un solo lugar.</div>
      </div>
    </div>
    <div class="tool-card">
      <div class="tool-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div>
        <div class="tool-title">Vigencias y seguimiento</div>
        <div class="tool-desc">Cotizaciones con fecha de vigencia. Nunca pierdas de vista una oportunidad por falta de seguimiento.</div>
      </div>
    </div>
    <div class="tool-card">
      <div class="tool-ico" style="background:#fef3c7">
        <svg viewBox="0 0 24 24" stroke="#92400e"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <div>
        <div class="tool-title">Catalogo de articulos</div>
        <div class="tool-desc">Tu catalogo siempre actualizado con precios, descripciones y categorias. Cotiza sin errores.</div>
      </div>
    </div>
    <div class="tool-card">
      <div class="tool-ico" style="background:#fce7f3">
        <svg viewBox="0 0 24 24" stroke="#be185d"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div>
        <div class="tool-title">Notas y comunicacion</div>
        <div class="tool-desc">Agrega notas internas a cada cotizacion. Contexto que tu equipo necesita para dar seguimiento efectivo.</div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <div class="cta-card">
    <h2>Tu proximo cliente ya abrio tu cotizacion. Lo sabias?</h2>
    <p class="cta-sub">Con Cotiza.cloud lo sabras. Crea tu cuenta y transforma la forma en que das seguimiento a tus ventas.</p>
    <a href="/registro" class="btn-cta">Crear cuenta gratis</a>
    <p class="cta-note">Sin tarjeta. Sin contratos. Cancela cuando quieras.</p>
  </div>
</section>

<!-- LIVE NOTIFICATIONS (demo) -->
<div class="notif-stack" id="notifStack">

  <div class="notif" id="notif1">
    <div class="notif-ico">🔥</div>
    <div class="notif-body">
      <div class="notif-title">3 cotizaciones en Cierre Inminente</div>
      <div class="notif-desc">Arq. Rodriguez, Ing. Vega y Despacho Luna las revisaron varias veces hoy</div>
      <div class="notif-badge" style="background:#fff1f2;color:#991b1b">🔥 Cierre Inminente</div>
    </div>
    <div class="notif-time">ahora</div>
  </div>

  <div class="notif" id="notif2">
    <div class="notif-ico">❌</div>
    <div class="notif-body">
      <div class="notif-title">4 cotizaciones no han sido vistas</div>
      <div class="notif-desc">Enviadas hace mas de 48h sin ninguna apertura</div>
      <div class="notif-badge" style="background:#fef2f2;color:#dc2626">❌ No abierta</div>
    </div>
    <div class="notif-time">hace 1m</div>
  </div>

  <div class="notif" id="notif3">
    <div class="notif-ico">💸</div>
    <div class="notif-body">
      <div class="notif-title">5 cotizaciones Validando Precio</div>
      <div class="notif-desc">Tus clientes estan comparando. Buen momento para llamar.</div>
      <div class="notif-badge" style="background:#fffbeb;color:#92400e">💸 Validando precio</div>
    </div>
    <div class="notif-time">hace 3m</div>
  </div>

  <div class="notif" id="notif4">
    <div class="notif-ico">🔮</div>
    <div class="notif-body">
      <div class="notif-title">1 cotizacion con Prediccion Alta</div>
      <div class="notif-desc">Constructora MBL — 94% probabilidad de cierre</div>
      <div class="notif-badge" style="background:#f0fdf4;color:#166534">🔮 Prediccion alta</div>
    </div>
    <div class="notif-time">hace 5m</div>
  </div>

</div>

<script>
(function(){
  var fired=false;
  var target=document.querySelector('.predictive');
  if(!target)return;
  var io=new IntersectionObserver(function(entries){
    if(entries[0].isIntersecting && !fired){
      fired=true;
      var ids=['notif1','notif2','notif3','notif4'];
      ids.forEach(function(id,i){
        setTimeout(function(){document.getElementById(id).classList.add('show')},400+i*900);
      });
      /* se desvanecen uno a uno despues de 20s */
      setTimeout(function(){document.getElementById('notif4').classList.remove('show')},22000);
      setTimeout(function(){document.getElementById('notif3').classList.remove('show')},23500);
      setTimeout(function(){document.getElementById('notif2').classList.remove('show')},25000);
      setTimeout(function(){document.getElementById('notif1').classList.remove('show')},26500);
    }
  },{threshold:0.25});
  io.observe(target);
})();
</script>

<!-- FOOTER -->
<footer class="footer">
  Cotiza.cloud &copy; <?= date('Y') ?> &middot; <a href="/login">Iniciar sesion</a> &middot; <a href="/registro">Crear cuenta</a>
</footer>

</body>
</html>
