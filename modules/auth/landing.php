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
<title>Cotiza.cloud — Sabes quien va a comprar antes de que te llame</title>
<meta name="description" content="Manda cotizaciones y sabe cuales se van a cerrar. El Radar analiza el comportamiento de tu cliente en tiempo real y te avisa cuando esta listo para comprar.">
<meta property="og:title" content="Cotiza.cloud — Sabes quien va a comprar antes de que te llame">
<meta property="og:description" content="Manda cotizaciones y sabe cuales se van a cerrar. Radar de inteligencia de ventas en tiempo real.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://cotiza.cloud">
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
.logo-mark{width:40px;height:40px;border-radius:11px;background:var(--g);display:flex;align-items:center;justify-content:center}
.logo-mark svg{width:32px;height:26px}
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
.hero-note{font:400 14px var(--body);color:var(--t3)}

/* PROOF BAR */
.proof{max-width:800px;margin:0 auto;padding:48px 24px 0}
.proof-inner{display:flex;justify-content:center;gap:40px;flex-wrap:wrap}
.proof-item{text-align:center}
.proof-num{font:800 30px var(--num);color:var(--g);letter-spacing:-.02em}
.proof-label{font:500 13px var(--body);color:var(--t3);margin-top:2px}

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
.section-label{display:block;text-align:center;font:700 12px var(--body);letter-spacing:.1em;text-transform:uppercase;color:var(--g);margin-bottom:12px}
.section-title{text-align:center;font:800 clamp(26px,3.8vw,38px)/1.12 var(--body);letter-spacing:-.03em;margin-bottom:12px}
.section-sub{text-align:center;font:400 17px var(--body);color:var(--t3);margin-bottom:56px;max-width:560px;margin-left:auto;margin-right:auto;line-height:1.6}
.flow-steps{display:flex;gap:0;justify-content:center;flex-wrap:wrap;position:relative}
.flow-step{flex:1;min-width:150px;max-width:188px;text-align:center;position:relative;padding:0 6px}
.flow-step-num{width:52px;height:52px;border-radius:50%;background:var(--g);color:#fff;font:800 20px var(--num);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(26,92,56,.25);position:relative;z-index:2}
.flow-step-num.hot{background:var(--warm);box-shadow:0 4px 16px rgba(220,38,38,.25)}
.flow-step-title{font:700 16px var(--body);margin-bottom:5px;letter-spacing:-.01em}
.flow-step-desc{font:400 14px var(--body);color:var(--t3);line-height:1.55}
.flow-arrow{display:flex;align-items:flex-start;padding-top:22px;color:var(--g-border)}
.flow-arrow svg{width:20px;height:20px;stroke:var(--border2);stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round}

/* AUDIENCE / PARA QUIEN */
.audience{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.audience-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.aud-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:28px 22px;text-align:center;transition:all .25s;position:relative;overflow:hidden}
.aud-card:hover{border-color:var(--g-border);box-shadow:var(--sh-lg);transform:translateY(-3px)}
.aud-card-ico{font-size:36px;margin-bottom:14px;line-height:1}
.aud-card-title{font:700 17px var(--body);color:var(--text);margin-bottom:8px;letter-spacing:-.01em}
.aud-card-desc{font:400 14.5px var(--body);color:var(--t3);line-height:1.6}
.aud-card-example{font:500 13px var(--body);color:var(--g);margin-top:12px}
@media(max-width:768px){.audience-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.audience-grid{grid-template-columns:1fr}}

/* ACCELERATORS */
.accel{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.accel-inner{background:linear-gradient(135deg,#f0fdf4 0%,#ecfdf5 40%,#f0fdfa 100%);border:1px solid rgba(26,92,56,.12);border-radius:20px;padding:48px 40px;position:relative;overflow:hidden}
.accel-inner::before{content:"";position:absolute;top:-40px;right:-40px;width:160px;height:160px;background:radial-gradient(circle,rgba(26,92,56,.08) 0%,transparent 70%);border-radius:50%}
.accel-sparkle{position:absolute;top:16px;right:20px;font:700 10px var(--body);color:#fff;background:var(--g);padding:4px 12px;border-radius:20px;letter-spacing:.06em;text-transform:uppercase}
.accel-label{font:700 12px var(--body);letter-spacing:.1em;text-transform:uppercase;color:var(--g);margin-bottom:12px}
.accel-title{font:800 clamp(24px,3.2vw,32px)/1.15 var(--body);letter-spacing:-.02em;color:var(--text);margin-bottom:12px}
.accel-sub{font:400 16px var(--body);color:var(--t3);line-height:1.6;margin-bottom:32px;max-width:580px}
.accel-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.accel-item{background:rgba(255,255,255,.8);border:1px solid rgba(26,92,56,.1);border-radius:14px;padding:22px 18px;text-align:center;transition:all .2s}
.accel-item:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(26,92,56,.1);border-color:var(--g-border)}
.accel-item-ico{font-size:30px;margin-bottom:10px;line-height:1}
.accel-item-name{font:700 15px var(--body);color:var(--text);margin-bottom:6px}
.accel-item-desc{font:400 14px var(--body);color:var(--t3);line-height:1.55}
@media(max-width:768px){.accel-grid{grid-template-columns:repeat(2,1fr)}.accel-inner{padding:32px 24px}}
@media(max-width:480px){.accel-grid{grid-template-columns:1fr}}

/* FEATURES */
.features{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.feat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:28px 24px;box-shadow:var(--sh);transition:all .2s}
.feat-card:hover{box-shadow:var(--sh-lg);transform:translateY(-2px)}
.feat-card.featured{border-color:var(--g-border);border-width:2px;position:relative}
.feat-card.featured::after{content:"NUEVO";position:absolute;top:16px;right:16px;font:800 9px var(--body);letter-spacing:.08em;background:var(--warm);color:#fff;padding:3px 8px;border-radius:4px}
.feat-ico{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px}
.feat-ico svg{width:22px;height:22px;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;fill:none}
.feat-tag{display:inline-block;font:700 11px var(--body);letter-spacing:.06em;text-transform:uppercase;padding:4px 10px;border-radius:4px;margin-bottom:10px}
.feat-title{font:700 18px var(--body);margin-bottom:6px;letter-spacing:-.01em}
.feat-desc{font:400 14.5px var(--body);color:var(--t3);line-height:1.65}

/* PREDICTIVE SECTION — 2-col: copy left, pipeline right */
.predictive{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.pred-card{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);border-radius:24px;padding:52px;color:#fff;display:grid;grid-template-columns:1.15fr 1fr;gap:56px;align-items:center;box-shadow:0 12px 48px rgba(0,0,0,.2);position:relative;overflow:hidden}
.pred-card::before{content:"";position:absolute;top:-60%;right:-20%;width:400px;height:400px;background:radial-gradient(circle,rgba(124,58,237,.12) 0%,transparent 70%);pointer-events:none}
.pred-card::after{content:"";position:absolute;bottom:-40%;left:-10%;width:300px;height:300px;background:radial-gradient(circle,rgba(26,92,56,.12) 0%,transparent 70%);pointer-events:none}
.pred-left{position:relative;z-index:1}
.pred-label{font:700 11px var(--body);letter-spacing:.1em;text-transform:uppercase;color:#a78bfa;margin-bottom:14px}
.pred-title{font:800 clamp(24px,3.2vw,32px)/1.15 var(--body);letter-spacing:-.02em;margin-bottom:18px}
.pred-desc{font:400 15px var(--body);opacity:.55;line-height:1.7;margin-bottom:32px}
.pred-states{display:flex;flex-direction:column;gap:18px}
.pred-state{display:flex;gap:12px;align-items:flex-start}
.pred-state-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:5px}
.pred-state-text{font:400 14px var(--body);color:rgba(255,255,255,.6);line-height:1.55}
.pred-state-text strong{display:block;font:600 15px var(--body);color:#fff;margin-bottom:2px}
.pred-right{position:relative;z-index:1;display:flex;align-items:center}

/* PIPELINE VISUAL — right column */
.pipeline{display:flex;flex-direction:column;gap:14px;width:100%}
.pipe-row{display:flex;align-items:center}
.pipe-bar{height:50px;border-radius:12px;display:flex;align-items:center;padding:0 18px;font:600 14px var(--body);white-space:nowrap;gap:6px}
.pipe-num{font:800 20px var(--num);letter-spacing:-.02em;margin-right:2px}
.pipe-tag{margin-left:auto;font:600 10px var(--body);letter-spacing:.06em;text-transform:uppercase;opacity:.7}
.pipe-cold{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);width:100%;color:rgba(255,255,255,.45)}
.pipe-warm{background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.18);width:88%;color:#fbbf24}
.pipe-hot{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.18);width:72%;color:#ef4444}
.pipe-close{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);width:52%;color:#4ade80}

/* TOOLS SECTION */
.tools{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.tools-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.tool-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:32px 28px;box-shadow:var(--sh);display:flex;gap:20px;align-items:flex-start;transition:all .2s}
.tool-card:hover{box-shadow:var(--sh-md)}
.tool-ico{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tool-ico svg{width:22px;height:22px;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;fill:none}
.tool-title{font:700 17px var(--body);margin-bottom:5px;letter-spacing:-.01em}
.tool-desc{font:400 14.5px var(--body);color:var(--t3);line-height:1.6}

/* PRICING */
.pricing{max-width:1100px;margin:0 auto;padding:80px 24px 0}
.pricing-toggle{display:flex;align-items:center;justify-content:center;gap:14px;margin-bottom:48px}
.toggle-label{font:600 15px var(--body);color:var(--t3);cursor:pointer;transition:color .2s}
.toggle-label.active{color:var(--text);font-weight:700}
.toggle-switch{position:relative;display:inline-block;width:52px;height:28px;cursor:pointer}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;top:0;left:0;right:0;bottom:0;background:var(--border2);border-radius:28px;transition:all .3s}
.toggle-slider::before{content:"";position:absolute;height:22px;width:22px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:all .3s;box-shadow:0 1px 4px rgba(0,0,0,.15)}
.toggle-switch input:checked+.toggle-slider{background:var(--g)}
.toggle-switch input:checked+.toggle-slider::before{transform:translateX(24px)}
.toggle-save{font:700 12px var(--body);color:#fff;background:var(--g);padding:4px 12px;border-radius:20px;letter-spacing:.02em;animation:pulse-save 2s infinite}
@keyframes pulse-save{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.85;transform:scale(1.05)}}

.pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:start}
.price-card{background:var(--white);border:1.5px solid var(--border);border-radius:20px;padding:36px 28px;position:relative;transition:all .3s}
.price-card:hover{box-shadow:var(--sh-lg);transform:translateY(-4px)}
.price-card-featured{border:2.5px solid var(--g);box-shadow:var(--sh-lg);transform:scale(1.04);z-index:2}
.price-card-featured:hover{transform:scale(1.04) translateY(-4px)}
.price-card-business{border:2px solid #1d4ed8;background:linear-gradient(180deg,#fff 0%,#eff6ff 100%)}
.price-card-business .price-value{color:#1d4ed8}
.price-btn-business{display:block;text-align:center;padding:14px;border-radius:12px;font:700 15px var(--body);background:#1d4ed8;color:#fff;text-decoration:none;transition:all .2s}
.price-btn-business:hover{background:#1e40af;transform:translateY(-1px)}

.price-badge-popular{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:var(--g);color:#fff;font:800 11px var(--body);padding:5px 18px;border-radius:20px;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}
.price-badge-launch{position:absolute;top:16px;right:16px;background:var(--warm-bg);color:var(--warm);font:700 10px var(--body);padding:4px 10px;border-radius:6px;letter-spacing:.04em;text-transform:uppercase;animation:pulse-badge 2.5s infinite}
@keyframes pulse-badge{0%,100%{opacity:1}50%{opacity:.6}}

.price-header{margin-bottom:24px}
.price-plan-name{font:800 24px var(--body);letter-spacing:-.02em;margin-bottom:4px}
.price-plan-desc{font:400 14px var(--body);color:var(--t3)}

.price-amount{display:flex;align-items:baseline;gap:2px;margin-bottom:4px}
.price-currency{font:700 22px var(--num);color:var(--text)}
.price-value{font:800 52px var(--num);color:var(--text);letter-spacing:-.04em;line-height:1}
.price-mo{font:500 16px var(--body);color:var(--t3);margin-left:2px}
.price-original{font:600 18px var(--num);color:var(--t3);text-decoration:line-through;margin-right:8px;align-self:center}
.price-card-featured .price-value{color:var(--g)}

.price-period{font:500 13px var(--body);color:var(--t3);margin-bottom:24px;min-height:20px}
.price-period strong{color:var(--g);font-weight:700}

.price-btn{display:block;width:100%;padding:14px;border-radius:var(--r-sm);font:700 15px var(--body);text-align:center;text-decoration:none;transition:all .2s;border:none;cursor:pointer}
.price-btn-solid{background:var(--g);color:#fff;box-shadow:0 4px 16px rgba(26,92,56,.25)}
.price-btn-solid:hover{background:var(--g2);box-shadow:0 6px 24px rgba(26,92,56,.35);transform:translateY(-1px)}
.price-btn-outline{background:var(--white);color:var(--g);border:2px solid var(--g)}
.price-btn-outline:hover{background:var(--g-bg)}

.price-trial-note{font:500 12px var(--body);color:var(--t3);text-align:center;margin-top:8px}
.price-features{margin-top:24px;padding-top:24px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:12px}
.price-feat-header{font:700 13px var(--body);color:var(--t2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
.price-feat{display:flex;align-items:center;gap:10px;font:400 14.5px var(--body);color:var(--t2)}
.feat-check{color:var(--g);font-weight:800;font-size:15px;flex-shrink:0}

.pricing-note{text-align:center;font:400 13px var(--body);color:var(--t3);margin-top:32px}

@media(max-width:900px){.pricing-grid{grid-template-columns:1fr;max-width:400px;margin:0 auto}.price-card-featured{transform:scale(1)}.price-card-featured:hover{transform:translateY(-4px)}}

/* CTA */
.cta{max-width:720px;margin:0 auto;padding:80px 24px;text-align:center}
.cta-card{background:var(--g);border-radius:24px;padding:56px 40px;position:relative;overflow:hidden}
.cta-card::before{content:"";position:absolute;top:-50%;right:-30%;width:400px;height:400px;background:radial-gradient(circle,rgba(255,255,255,.06) 0%,transparent 70%);pointer-events:none}
.cta h2{font:800 clamp(26px,3.8vw,36px)/1.1 var(--body);letter-spacing:-.03em;margin-bottom:12px;color:#fff}
.cta-sub{font:400 17px var(--body);color:rgba(255,255,255,.85);margin-bottom:32px;line-height:1.6;max-width:460px;margin-left:auto;margin-right:auto}
.btn-cta{display:inline-block;padding:18px 44px;border-radius:var(--r-sm);background:#fff;color:var(--g);font:800 17px var(--body);text-decoration:none;transition:all .18s;box-shadow:0 4px 16px rgba(0,0,0,.15)}
.btn-cta:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(0,0,0,.2)}
.cta-note{font:500 14px var(--body);color:rgba(255,255,255,.65);margin-top:16px}

/* LIVE NOTIFICATIONS */
.notif-stack{position:fixed;top:76px;right:20px;z-index:90;display:flex;flex-direction:column;gap:12px;pointer-events:none}
.notif{background:rgba(255,255,255,.97);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(0,0,0,.08);border-radius:16px;padding:18px 20px;box-shadow:0 8px 32px rgba(0,0,0,.12),0 2px 6px rgba(0,0,0,.06);display:flex;align-items:flex-start;gap:14px;max-width:380px;transform:translateX(120%);opacity:0;transition:all .5s cubic-bezier(.16,1,.3,1);pointer-events:auto;position:relative}
.notif.show{transform:translateX(0);opacity:1}
.notif-ico{font-size:28px;flex-shrink:0;line-height:1;margin-top:2px}
.notif-body{flex:1;min-width:0}
.notif-title{font:700 15px var(--body);color:var(--text);margin-bottom:4px;letter-spacing:-.01em}
.notif-desc{font:400 14px var(--body);color:var(--t2);line-height:1.5}
.notif-time{font:500 12px var(--body);color:var(--t3);flex-shrink:0;margin-top:3px}
.notif-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;font:700 11px var(--body);letter-spacing:.02em;margin-top:6px}
.notif-close{position:absolute;top:8px;right:10px;width:28px;height:28px;border-radius:50%;border:none;background:rgba(0,0,0,.06);color:var(--t3);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;line-height:1;padding:0}
.notif-close:hover{background:rgba(0,0,0,.12);color:var(--text)}

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
  .pred-card{grid-template-columns:1fr;padding:36px 24px 32px}
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
        <svg viewBox="0 0 60 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Cloud -->
          <path d="M48.5 38H14c-5.5 0-10-4.5-10-10 0-4.8 3.4-8.8 8-9.8C12.2 12.5 17.5 8 24 8c5.2 0 9.7 3 12 7.3C37.3 14.5 39 14 41 14c5.5 0 10 4.5 10 10 0 .7-.1 1.3-.2 2C54.3 27.5 57 31 57 35c0 1-.2 2-.5 3" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
          <!-- Radar circles -->
          <circle cx="33" cy="26" r="12" stroke="rgba(255,255,255,.5)" stroke-width="1.5" fill="none"/>
          <circle cx="33" cy="26" r="8" stroke="rgba(255,255,255,.65)" stroke-width="1.5" fill="none"/>
          <circle cx="33" cy="26" r="4" stroke="rgba(255,255,255,.8)" stroke-width="1.5" fill="none"/>
          <!-- Radar cross -->
          <line x1="33" y1="14" x2="33" y2="38" stroke="rgba(255,255,255,.3)" stroke-width="1"/>
          <line x1="21" y1="26" x2="45" y2="26" stroke="rgba(255,255,255,.3)" stroke-width="1"/>
          <!-- Radar sweep needle -->
          <line x1="33" y1="26" x2="42" y2="18" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
          <!-- Center dot -->
          <circle cx="33" cy="26" r="1.8" fill="#4ade80"/>
        </svg>
      </div>
      <div class="logo-name">Cotiza<span>.cloud</span></div>
    </a>
    <div class="nav-links">
      <a href="#precios" class="nav-link nav-link-ghost">Precios</a>
      <a href="/login" class="nav-link nav-link-ghost">Iniciar sesion</a>
      <a href="/registro" class="nav-link nav-link-primary">Crear cuenta gratis</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><div class="hero-badge-dot"></div>Radar de inteligencia de ventas</div>
  <h1>Sabes quien va a comprar<br><em>antes de que te llame</em></h1>
  <p class="hero-sub">Mandas cotizaciones y esperas? Nosotros te decimos <strong>cuales se van a cerrar</strong>. El radar analiza el comportamiento de tu cliente en tiempo real y te avisa cuando esta listo.</p>
  <div class="hero-btns">
    <a href="/registro" class="btn-hero btn-hero-primary">Probar el Radar gratis</a>
    <a href="#como-funciona" class="btn-hero btn-hero-secondary">Ver como funciona</a>
  </div>
  <p class="hero-note">Sin tarjeta. Sin contratos. Radar completo desde el dia 1.</p>
</section>

<!-- PROOF -->
<section class="proof">
  <div class="proof-inner">
    <div class="proof-item">
      <div class="proof-num">17</div>
      <div class="proof-label">Senales de interes que detecta</div>
    </div>
    <div class="proof-item">
      <div class="proof-num">24/7</div>
      <div class="proof-label">Monitoreo automatico</div>
    </div>
    <div class="proof-item">
      <div class="proof-num">0</div>
      <div class="proof-label">Llamadas en frio</div>
    </div>
  </div>
</section>

<!-- PREDICTIVE — 2-col: copy + pipeline -->
<section class="predictive" id="como-funciona">
  <div class="pred-card">
    <div class="pred-left">
      <div class="pred-label">Asi funciona el Radar</div>
      <div class="pred-title">Tu pipeline de ventas en tiempo real — sin preguntar nada</div>
      <div class="pred-desc">Cada vez que tu cliente abre una cotizacion, el radar registra su comportamiento: cuantas veces entro, si reviso el precio, si alguien mas la vio, cuanto tiempo le dedico. Con eso calcula la probabilidad real de cierre.</div>
      <div class="pred-states">
        <div class="pred-state">
          <span class="pred-state-dot" style="background:rgba(255,255,255,.3)"></span>
          <div class="pred-state-text"><strong>No abierta</strong>Enviaste la cotizacion y nadie la ha visto. Buen momento para reenviar.</div>
        </div>
        <div class="pred-state">
          <span class="pred-state-dot" style="background:#fbbf24"></span>
          <div class="pred-state-text"><strong>Comparando</strong>La abrieron desde otro dispositivo. Alguien mas la esta evaluando.</div>
        </div>
        <div class="pred-state">
          <span class="pred-state-dot" style="background:#ef4444"></span>
          <div class="pred-state-text"><strong>Validando precio</strong>Tu cliente regreso varias veces a revisar los totales. Le interesa.</div>
        </div>
        <div class="pred-state">
          <span class="pred-state-dot" style="background:#4ade80"></span>
          <div class="pred-state-text"><strong>Cierre inminente</strong>Multiples senales fuertes. Es momento de llamar.</div>
        </div>
      </div>
    </div>
    <div class="pred-right">
      <div class="pipeline">
        <div class="pipe-row"><div class="pipe-bar pipe-cold"><span class="pipe-num">12</span> cotizaciones<span class="pipe-tag">Sin abrir</span></div></div>
        <div class="pipe-row"><div class="pipe-bar pipe-warm"><span class="pipe-num">8</span> cotizaciones<span class="pipe-tag">Comparando</span></div></div>
        <div class="pipe-row"><div class="pipe-bar pipe-hot"><span class="pipe-num">5</span> cotizaciones<span class="pipe-tag">Alto interes</span></div></div>
        <div class="pipe-row"><div class="pipe-bar pipe-close"><span class="pipe-num">3</span> cotizaciones<span class="pipe-tag">Llamar ahora</span></div></div>
      </div>
    </div>
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


<!-- COSTOS — Seccion dedicada, segundo gancho -->
<section class="predictive">
  <div class="pred-card" style="background:linear-gradient(135deg,#1a1a18 0%,#2d1f0e 50%,#3d2b10 100%)">
    <div>
      <div class="pred-label" style="color:#fbbf24">Rentabilidad real</div>
      <div class="pred-title">Sabes exactamente cuanto ganas en cada proyecto</div>
      <div class="pred-desc">No es Excel. Es un sistema que registra tus costos por operacion y te muestra el margen real — por venta, por cliente, por periodo. Sin sorpresas al final del mes.</div>
      <ul class="pred-list">
        <li><span class="pred-dot" style="background:#fbbf24"></span><strong>Costo por venta</strong> — Registra materiales, mano de obra, gastos. Todo desglosado.</li>
        <li><span class="pred-dot" style="background:#4ade80"></span><strong>Margen real</strong> — Ve tu utilidad neta por cada proyecto cerrado.</li>
        <li><span class="pred-dot" style="background:#60a5fa"></span><strong>Reportes</strong> — Dashboards de ingresos, gastos y utilidad por periodo.</li>
      </ul>
    </div>
    <div class="pred-right">
      <div class="pipeline">
        <div class="pipe-row">
          <span class="pipe-label">Venta</span>
          <div class="pipe-bar" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);width:100%;color:rgba(255,255,255,.7)">$34,500 MXN<span class="pipe-tag">Ingreso</span></div>
        </div>
        <div class="pipe-row">
          <span class="pipe-label">Costos</span>
          <div class="pipe-bar" style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);width:62%;color:#ef4444">$21,400 MXN<span class="pipe-tag">62%</span></div>
        </div>
        <div class="pipe-row">
          <span class="pipe-label">Utilidad</span>
          <div class="pipe-bar" style="background:rgba(74,222,128,.15);border:1px solid rgba(74,222,128,.3);width:38%;color:#4ade80">$13,100 MXN<span class="pipe-tag">38%</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PLATAFORMA — Features compactos, no roban protagonismo -->
<section class="features">
  <span class="section-label">Plataforma completa</span>
  <div class="section-title">Cotiza, vende, cobra — todo en un lugar</div>
  <p class="section-sub">El Radar es el cerebro. Esto es todo lo que lo rodea.</p>

  <div class="features-grid">
    <div class="feat-card">
      <div class="feat-ico" style="background:var(--g-bg)">
        <svg viewBox="0 0 24 24" stroke="var(--g)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      </div>
      <div class="feat-title">Cotizaciones profesionales</div>
      <div class="feat-desc">Con tu marca, articulos, descuentos y cupones. Compartelas por WhatsApp o correo con un link unico.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><rect x="1" y="3" width="22" height="18" rx="2"/><line x1="1" y1="9" x2="23" y2="9"/><line x1="8" y1="15" x2="16" y2="15"/></svg>
      </div>
      <div class="feat-title">Ventas y recibos</div>
      <div class="feat-desc">Genera recibos, registra abonos parciales o totales. Tu cliente ve su saldo en tiempo real.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#fef3c7">
        <svg viewBox="0 0 24 24" stroke="#92400e"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <div class="feat-title">Catalogo de articulos</div>
      <div class="feat-desc">Precios, descripciones y categorias siempre actualizados. Cotiza rapido y sin errores.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:var(--g-bg)">
        <svg viewBox="0 0 24 24" stroke="var(--g)"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      </div>
      <div class="feat-title">Directorio de clientes</div>
      <div class="feat-desc">Historial completo por cliente: cotizaciones, ventas, pagos y nivel de actividad.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#fce7f3">
        <svg viewBox="0 0 24 24" stroke="#be185d"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div class="feat-title">Reportes de ventas</div>
      <div class="feat-desc">Dashboards claros con ingresos, gastos y utilidad. Filtra por periodo, vendedor o cliente.</div>
    </div>
    <div class="feat-card">
      <div class="feat-ico" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" stroke="#1d4ed8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="feat-title">Multi-usuario y permisos</div>
      <div class="feat-desc">Agrega vendedores con permisos granulares. Controla quien cotiza y quien ve los numeros.</div>
    </div>
  </div>
</section>

<!-- TERMÓMETRO — sección compacta, visual -->
<section class="features" style="padding-top:60px;padding-bottom:20px">
  <div class="section-title">Sabe quien vende bien — y quien necesita ayuda</div>
  <p class="section-sub">Cada vendedor recibe un score en tiempo real con diagnostico automatico. Tu solo lees el resultado.</p>

  <div style="max-width:520px;margin:32px auto 0;background:#fff;border:2px solid #bfdbfe;border-radius:16px;padding:28px 24px;box-shadow:var(--sh-md)">
    <div style="display:flex;flex-direction:column;gap:16px">

      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:32px;height:32px;border-radius:9px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 13px var(--body);color:#fff;flex-shrink:0">M</div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;justify-content:space-between"><span style="font:600 14px var(--body)">Maria Lopez</span><span style="font:700 18px var(--num);color:#16a34a">87 <span style="font:400 11px var(--body);color:#16a34a">Top</span></span></div>
          <div style="font:400 12px var(--body);color:var(--t3);margin-top:2px">Excelente cierre. Usa el Radar. Todos a precio completo.</div>
        </div>
      </div>

      <div style="height:1px;background:var(--border)"></div>

      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:32px;height:32px;border-radius:9px;background:#64748b;display:flex;align-items:center;justify-content:center;font:700 13px var(--body);color:#fff;flex-shrink:0">C</div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;justify-content:space-between"><span style="font:600 14px var(--body)">Carlos Ruiz</span><span style="font:700 18px var(--num);color:#d97706">48 <span style="font:400 11px var(--body);color:#d97706">Regular</span></span></div>
          <div style="font:400 12px var(--body);color:var(--t3);margin-top:2px">Cotiza bien pero no cierra. No revisa el Radar.</div>
        </div>
      </div>

      <div style="height:1px;background:var(--border)"></div>

      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:32px;height:32px;border-radius:9px;background:#64748b;display:flex;align-items:center;justify-content:center;font:700 13px var(--body);color:#fff;flex-shrink:0">R</div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;justify-content:space-between"><span style="font:600 14px var(--body)">Roberto Diaz</span><span style="font:700 18px var(--num);color:#dc2626">19 <span style="font:400 11px var(--body);color:#dc2626">Bajo</span></span></div>
          <div style="font:400 12px var(--body);color:var(--t3);margin-top:2px">Sus cotizaciones no se abren. Ignora senales de clientes interesados.</div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- AUDIENCE -->
<section class="audience">
  <span class="section-label">Para quien es</span>
  <div class="section-title">Si cotizas para vender, esto es para ti</div>
  <p class="section-sub">Profesionales que mandan cotizaciones y quieren saber que pasa despues.</p>

  <div class="audience-grid">
    <div class="aud-card">
      <div class="aud-card-ico">&#128208;</div>
      <div class="aud-card-title">Arquitectos y Despachos</div>
      <div class="aud-card-desc">Cotizaciones de alto valor donde el seguimiento define si cierras o no.</div>
    </div>
    <div class="aud-card">
      <div class="aud-card-ico">&#127959;</div>
      <div class="aud-card-title">Constructoras</div>
      <div class="aud-card-desc">Multiples cotizaciones activas. Sabe cuales van en serio sin llamar a todos.</div>
    </div>
    <div class="aud-card">
      <div class="aud-card-ico">&#128736;</div>
      <div class="aud-card-title">Fabricantes y Talleres</div>
      <div class="aud-card-desc">Muebles, herreria, carpinteria. Productos a la medida con imagen profesional.</div>
    </div>
    <div class="aud-card">
      <div class="aud-card-ico">&#128187;</div>
      <div class="aud-card-title">Freelancers y Consultores</div>
      <div class="aud-card-desc">Diseno, marketing, TI. Deja de perseguir clientes — llama cuando estan listos.</div>
    </div>
  </div>
</section>


<!-- ACCELERATORS - Sales psychology tools -->
<section class="accel">
  <div class="accel-inner">
    <div class="accel-sparkle">Incluido</div>
    <div class="accel-label">Acelera el cierre</div>
    <div class="accel-title">Gatillos de venta que convierten interes en decision</div>
    <div class="accel-sub">Las mismas tecnicas que usan Amazon, Booking y Mercado Libre para que la gente compre ahora y no "despues". Urgencia, escasez y precio especial — integradas en cada cotizacion.</div>
    <div class="accel-grid">
      <div class="accel-item">
        <div class="accel-item-ico">&#9200;</div>
        <div class="accel-item-name">Cuenta regresiva</div>
        <div class="accel-item-desc">Un cronometro visible en tu cotizacion. Tu cliente sabe que el precio tiene fecha limite.</div>
      </div>
      <div class="accel-item">
        <div class="accel-item-ico">&#127915;</div>
        <div class="accel-item-name">Cupones de descuento</div>
        <div class="accel-item-desc">Codigos exclusivos que tu cliente aplica al aceptar. Le das el poder de "ganar" su descuento.</div>
      </div>
      <div class="accel-item">
        <div class="accel-item-ico">&#128184;</div>
        <div class="accel-item-name">Precio tachado</div>
        <div class="accel-item-desc">Muestra el precio original junto al precio especial. El ancla visual que acelera la decision de compra.</div>
      </div>
      <div class="accel-item">
        <div class="accel-item-ico">&#128165;</div>
        <div class="accel-item-name">Oferta por tiempo limitado</div>
        <div class="accel-item-desc">Descuento + cronometro. La oferta desaparece y tu cliente lo sabe desde que abre la cotizacion.</div>
      </div>
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="pricing" id="precios">
  <span class="section-label">Planes y precios</span>
  <div class="section-title">Elige el plan que mueve tu negocio</div>
  <p class="section-sub">Todos incluyen Radar. La diferencia esta en cuanto quieres crecer.</p>

  <div class="pricing-toggle">
    <span class="toggle-label" id="toggleMensual">Mensual</span>
    <label class="toggle-switch">
      <input type="checkbox" id="billingToggle">
      <span class="toggle-slider"></span>
    </label>
    <span class="toggle-label" id="toggleAnual">Anual</span>
    <span class="toggle-save">Ahorra 20%</span>
  </div>

  <div class="pricing-grid">

    <!-- FREE -->
    <div class="price-card">
      <div class="price-header">
        <div class="price-plan-name">Free</div>
        <div class="price-plan-desc">Conoce el sistema. Sin limite de tiempo.</div>
      </div>
      <div class="price-amount">
        <span class="price-currency">$</span>
        <span class="price-value">0</span>
      </div>
      <div class="price-period">Para siempre</div>
      <a href="/registro" class="price-btn price-btn-outline">Crear cuenta gratis</a>
      <div class="price-features">
        <div class="price-feat"><span class="feat-check">&#10003;</span>25 cotizaciones</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Radar de inteligencia</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Ventas y recibos</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Costos basicos</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Catalogo de articulos</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Cupones y descuentos</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>1 usuario</div>
      </div>
    </div>

    <!-- PRO -->
    <div class="price-card price-card-featured">
      <div class="price-badge-popular">Mas popular</div>
      <div class="price-badge-launch">Precio de lanzamiento</div>
      <div class="price-header">
        <div class="price-plan-name">Pro</div>
        <div class="price-plan-desc">Cotiza sin limites. Vende con inteligencia.</div>
      </div>
      <div class="price-amount">
        <span class="price-original monthly-price">$499</span>
        <span class="price-original annual-price" style="display:none">$399</span>
        <span class="price-currency">$</span>
        <span class="price-value monthly-price">299</span>
        <span class="price-value annual-price" style="display:none">239</span>
        <span class="price-mo">/mes</span>
      </div>
      <div class="price-period monthly-price">$3,588 MXN/año</div>
      <div class="price-period annual-price" style="display:none">$2,868 MXN/año · <strong>Ahorras $720</strong></div>
      <a href="/registro" class="price-btn price-btn-solid">Empezar 14 dias gratis</a>
      <div class="price-trial-note">Sin tarjeta. Cancela cuando quieras.</div>
      <div class="price-features">
        <div class="price-feat-header">Todo de Free, sin limites:</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span><strong>Cotizaciones ilimitadas</strong></div>
        <div class="price-feat"><span class="feat-check">&#10003;</span><strong>Radar de inteligencia completo</strong></div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Articulos ilimitados</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Ventas, recibos y abonos</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Costos y margenes por venta</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Portal publico del cliente</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>App movil + notificaciones push</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>1 usuario</div>
      </div>
    </div>

    <!-- BUSINESS -->
    <div class="price-card price-card-business">
      <div class="price-header">
        <div class="price-plan-name">Business</div>
        <div class="price-plan-desc">Tu equipo completo. Control total.</div>
      </div>
      <div class="price-amount">
        <span class="price-original monthly-price">$1,299</span>
        <span class="price-original annual-price" style="display:none">$999</span>
        <span class="price-currency">$</span>
        <span class="price-value monthly-price">799</span>
        <span class="price-value annual-price" style="display:none">639</span>
        <span class="price-mo">/mes</span>
      </div>
      <div class="price-period monthly-price">$9,588 MXN/año</div>
      <div class="price-period annual-price" style="display:none">$7,668 MXN/año · <strong>Ahorras $1,920</strong></div>
      <a href="/registro" class="price-btn price-btn-business">Empezar 14 dias gratis</a>
      <div class="price-trial-note">Sin tarjeta. Cancela cuando quieras.</div>
      <div class="price-features">
        <div class="price-feat-header">Todo de Pro, mas:</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span><strong>Usuarios ilimitados</strong></div>
        <div class="price-feat"><span class="feat-check">&#10003;</span><strong>Permisos por vendedor</strong></div>
        <div class="price-feat"><span class="feat-check">&#10003;</span><strong>Termometro de productividad</strong></div>
        <div class="price-feat"><span class="feat-check">&#10003;</span><strong>Ranking y diagnostico por vendedor</strong></div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Costos avanzados por categoria</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Modulo de proveedores</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Reportes avanzados de equipo</div>
        <div class="price-feat"><span class="feat-check">&#10003;</span>Marketing y retargeting</div>
      </div>
    </div>

  </div>

  <p class="pricing-note">Precios en MXN. IVA no incluido. Pago con tarjeta, transferencia o SPEI.</p>
</section>

<!-- CTA -->
<section class="cta">
  <div class="cta-card">
    <h2>Tu proximo cliente ya esta revisando tu cotizacion. Lo sabes?</h2>
    <p class="cta-sub">Con el Radar de Cotiza.cloud, sabras quien esta listo para cerrar — antes de hacer una sola llamada.</p>
    <a href="/registro" class="btn-cta">Probar el Radar gratis</a>
    <p class="cta-note">Sin tarjeta. Sin contratos. Radar completo desde el dia 1.</p>
  </div>
</section>

<!-- LIVE NOTIFICATIONS (demo) -->
<div class="notif-stack" id="notifStack">

  <div class="notif" id="notif1">
    <button class="notif-close" onclick="this.parentElement.classList.remove('show')" aria-label="Cerrar">&times;</button>
    <div class="notif-ico">🔥</div>
    <div class="notif-body">
      <div class="notif-title">3 cotizaciones en Cierre Inminente</div>
      <div class="notif-desc">Arq. Rodriguez, Ing. Vega y Despacho Luna las revisaron varias veces hoy</div>
      <div class="notif-badge" style="background:#fff1f2;color:#991b1b">🔥 Cierre Inminente</div>
    </div>
    <div class="notif-time">ahora</div>
  </div>

  <div class="notif" id="notif2">
    <button class="notif-close" onclick="this.parentElement.classList.remove('show')" aria-label="Cerrar">&times;</button>
    <div class="notif-ico">❌</div>
    <div class="notif-body">
      <div class="notif-title">4 cotizaciones no han sido vistas</div>
      <div class="notif-desc">Enviadas hace mas de 48h sin ninguna apertura</div>
      <div class="notif-badge" style="background:#fef2f2;color:#dc2626">❌ No abierta</div>
    </div>
    <div class="notif-time">hace 1m</div>
  </div>

  <div class="notif" id="notif3">
    <button class="notif-close" onclick="this.parentElement.classList.remove('show')" aria-label="Cerrar">&times;</button>
    <div class="notif-ico">💸</div>
    <div class="notif-body">
      <div class="notif-title">5 cotizaciones Validando Precio</div>
      <div class="notif-desc">Tus clientes estan comparando. Buen momento para llamar.</div>
      <div class="notif-badge" style="background:#fffbeb;color:#92400e">💸 Validando precio</div>
    </div>
    <div class="notif-time">hace 3m</div>
  </div>

  <div class="notif" id="notif4">
    <button class="notif-close" onclick="this.parentElement.classList.remove('show')" aria-label="Cerrar">&times;</button>
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
  function showAndAutoDismiss(id, showDelay, dismissDelay) {
    setTimeout(function(){
      var el = document.getElementById(id);
      if(el) el.classList.add('show');
    }, showDelay);
    setTimeout(function(){
      var el = document.getElementById(id);
      if(el) el.classList.remove('show');
    }, dismissDelay);
  }

  /* DISPARO 1: entre Pipeline y Flow → las primeras 2 notificaciones */
  var fired1=false;
  var target1=document.querySelector('.flow');
  if(target1){
    var io1=new IntersectionObserver(function(entries){
      if(entries[0].isIntersecting && !fired1){
        fired1=true;
        showAndAutoDismiss('notif1', 400, 18000);
        showAndAutoDismiss('notif2', 1300, 20000);
      }
    },{threshold:0.2});
    io1.observe(target1);
  }

  /* DISPARO 2: al ver Pricing → las segundas 2 notificaciones */
  var fired2=false;
  var target2=document.querySelector('.pricing');
  if(target2){
    var io2=new IntersectionObserver(function(entries){
      if(entries[0].isIntersecting && !fired2){
        fired2=true;
        showAndAutoDismiss('notif3', 400, 18000);
        showAndAutoDismiss('notif4', 1300, 20000);
      }
    },{threshold:0.2});
    io2.observe(target2);
  }
})();
</script>

<script>
(function(){
  var toggle = document.getElementById('billingToggle');
  var lblMensual = document.getElementById('toggleMensual');
  var lblAnual = document.getElementById('toggleAnual');
  if(!toggle) return;

  function updatePricing(){
    var isAnual = toggle.checked;
    var monthly = document.querySelectorAll('.monthly-price');
    var annual = document.querySelectorAll('.annual-price');

    for(var i=0;i<monthly.length;i++){
      monthly[i].style.display = isAnual ? 'none' : '';
    }
    for(var i=0;i<annual.length;i++){
      annual[i].style.display = isAnual ? '' : 'none';
    }

    lblMensual.classList.toggle('active', !isAnual);
    lblAnual.classList.toggle('active', isAnual);
  }

  toggle.addEventListener('change', updatePricing);
  lblMensual.addEventListener('click', function(){ toggle.checked=false; updatePricing(); });
  lblAnual.addEventListener('click', function(){ toggle.checked=true; updatePricing(); });

  // Init
  lblMensual.classList.add('active');
})();
</script>

<!-- FOOTER -->
<footer class="footer">
  Cotiza.cloud &copy; <?= date('Y') ?> &middot; <a href="/login">Iniciar sesion</a> &middot; <a href="/registro">Crear cuenta</a>
</footer>

</body>
</html>
