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
<title>Cotiza.cloud — Cotizaciones y ventas para tu negocio</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f4f4f0;--white:#fff;--border:#e2e2dc;--border2:#c8c8c0;
  --text:#1a1a18;--t2:#4a4a46;--t3:#6a6a64;
  --g:#1a5c38;--g-bg:#eef7f2;--g-border:#b8ddc8;--g-light:#e6f4ed;
  --r:12px;--r-sm:9px;
  --sh:0 1px 3px rgba(0,0,0,.06);--sh-md:0 4px 16px rgba(0,0,0,.08);
  --body:'Plus Jakarta Sans',sans-serif;--num:'DM Sans',sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--body);background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}

/* NAV */
.nav{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100}
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
.hero{max-width:1100px;margin:0 auto;padding:80px 24px 60px;text-align:center}
.hero-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:var(--g-bg);border:1px solid var(--g-border);font:600 12px var(--body);color:var(--g);margin-bottom:20px}
.hero-badge-dot{width:6px;height:6px;border-radius:50%;background:var(--g)}
.hero h1{font:800 clamp(32px,5vw,52px)/1.1 var(--body);letter-spacing:-.03em;margin-bottom:16px}
.hero h1 span{color:var(--g)}
.hero-sub{font:400 clamp(16px,2vw,19px)/1.6 var(--body);color:var(--t2);max-width:600px;margin:0 auto 32px}
.hero-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.btn-hero{padding:14px 28px;border-radius:var(--r-sm);font:700 15px var(--body);text-decoration:none;transition:all .15s;border:none;cursor:pointer}
.btn-hero-primary{background:var(--g);color:#fff}
.btn-hero-primary:hover{opacity:.88}
.btn-hero-secondary{background:var(--white);color:var(--t2);border:1.5px solid var(--border2)}
.btn-hero-secondary:hover{border-color:var(--g);color:var(--g)}

/* FEATURES */
.features{max-width:1100px;margin:0 auto;padding:40px 24px 80px}
.features-title{text-align:center;font:800 28px var(--body);letter-spacing:-.02em;margin-bottom:8px}
.features-sub{text-align:center;font:400 15px var(--body);color:var(--t3);margin-bottom:40px}
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}
.feat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:24px;box-shadow:var(--sh);transition:box-shadow .15s}
.feat-card:hover{box-shadow:var(--sh-md)}
.feat-ico{width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.feat-ico svg{width:22px;height:22px;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;fill:none}
.feat-title{font:700 16px var(--body);margin-bottom:6px}
.feat-desc{font:400 14px var(--body);color:var(--t3);line-height:1.6}

/* HOW IT WORKS */
.how{max-width:1100px;margin:0 auto;padding:40px 24px 80px}
.how-title{text-align:center;font:800 28px var(--body);letter-spacing:-.02em;margin-bottom:40px}
.how-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;counter-reset:step}
.how-step{text-align:center;counter-increment:step}
.how-num{width:40px;height:40px;border-radius:50%;background:var(--g);color:#fff;font:800 16px var(--num);display:flex;align-items:center;justify-content:center;margin:0 auto 12px}
.how-num::before{content:counter(step)}
.how-step-title{font:700 15px var(--body);margin-bottom:4px}
.how-step-desc{font:400 13px var(--body);color:var(--t3);line-height:1.6}

/* CTA */
.cta{max-width:700px;margin:0 auto;padding:0 24px 80px;text-align:center}
.cta-card{background:var(--g);border-radius:16px;padding:48px 32px;color:#fff}
.cta h2{font:800 28px var(--body);letter-spacing:-.02em;margin-bottom:8px}
.cta-sub{font:400 15px var(--body);opacity:.85;margin-bottom:28px}
.btn-cta{display:inline-block;padding:14px 32px;border-radius:var(--r-sm);background:#fff;color:var(--g);font:700 15px var(--body);text-decoration:none;transition:opacity .15s}
.btn-cta:hover{opacity:.9}

/* FOOTER */
.footer{border-top:1px solid var(--border);padding:24px;text-align:center;font:400 13px var(--body);color:var(--t3)}
.footer a{color:var(--g);text-decoration:none}

@media(max-width:600px){
  .hero{padding:48px 20px 40px}
  .nav-link-ghost{display:none}
  .features-grid{grid-template-columns:1fr}
  .how-steps{grid-template-columns:1fr}
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
  <div class="hero-badge"><div class="hero-badge-dot"></div>Gratis para empezar</div>
  <h1>Cotizaciones y ventas<br><span>en un solo lugar</span></h1>
  <p class="hero-sub">Crea cotizaciones profesionales, conviertelas en ventas, registra pagos y dale seguimiento a tus clientes. Todo desde tu celular o computadora.</p>
  <div class="hero-btns">
    <a href="/registro" class="btn-hero btn-hero-primary">Crear cuenta gratis</a>
    <a href="/login" class="btn-hero btn-hero-secondary">Ya tengo cuenta</a>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <div class="features-title">Todo lo que necesitas</div>
  <p class="features-sub">Herramientas pensadas para negocios que cotizan y venden</p>
  <div class="features-grid">
    <div class="feat-card">
      <div class="feat-ico" style="background:var(--g-bg)">
        <svg viewBox="0 0 24 24" stroke="var(--g)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      </div>
      <div class="feat-title">Cotizaciones profesionales</div>
      <div class="feat-desc">Crea cotizaciones con tu marca, articulos, descuentos y cupones. Compartelas por WhatsApp o correo con un link unico.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <div class="feat-title">Ventas y pagos</div>
      <div class="feat-desc">Convierte cotizaciones en ventas. Registra abonos, genera recibos y controla el saldo pendiente de cada cliente.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#ede9fe">
        <svg viewBox="0 0 24 24" stroke="#6d28d9"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <div class="feat-title">Radar de interes</div>
      <div class="feat-desc">Detecta automaticamente cuando un cliente abre tu cotizacion, cuantas veces la revisa y que tan interesado esta.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#fef3c7">
        <svg viewBox="0 0 24 24" stroke="#92400e"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div class="feat-title">Reportes y costos</div>
      <div class="feat-desc">Visualiza tus ventas, ingresos y gastos. Registra costos por categoria para entender la rentabilidad real de tu negocio.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:var(--g-bg)">
        <svg viewBox="0 0 24 24" stroke="var(--g)"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="feat-title">Clientes organizados</div>
      <div class="feat-desc">Directorio de clientes con historial completo de cotizaciones y ventas. Encuentra cualquier dato en segundos.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div class="feat-title">Multi-usuario</div>
      <div class="feat-desc">Agrega vendedores con permisos granulares. Controla quien puede editar precios, aplicar descuentos o ver todas las ventas.</div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how">
  <div class="how-title">Asi de facil</div>
  <div class="how-steps">
    <div class="how-step">
      <div class="how-num"></div>
      <div class="how-step-title">Crea tu cuenta</div>
      <div class="how-step-desc">Registra tu empresa en 30 segundos. Sin tarjeta de credito.</div>
    </div>
    <div class="how-step">
      <div class="how-num"></div>
      <div class="how-step-title">Configura tu catalogo</div>
      <div class="how-step-desc">Agrega tus articulos, precios y datos de empresa.</div>
    </div>
    <div class="how-step">
      <div class="how-num"></div>
      <div class="how-step-title">Cotiza y vende</div>
      <div class="how-step-desc">Envia cotizaciones por WhatsApp y convierte en ventas con un click.</div>
    </div>
    <div class="how-step">
      <div class="how-num"></div>
      <div class="how-step-title">Cobra y registra</div>
      <div class="how-step-desc">Registra pagos parciales o totales. Tu cliente ve su saldo en tiempo real.</div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <div class="cta-card">
    <h2>Empieza hoy, es gratis</h2>
    <p class="cta-sub">No necesitas tarjeta de credito. Crea tu cuenta y empieza a cotizar en minutos.</p>
    <a href="/registro" class="btn-cta">Crear cuenta gratis</a>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  Cotiza.cloud &copy; <?= date('Y') ?> &middot; <a href="/login">Iniciar sesion</a> &middot; <a href="/registro">Crear cuenta</a>
</footer>

</body>
</html>
