<?php
// ============================================================
//  CotizaApp — modules/auth/landing.php
//  GET / — Landing page informativa de CotizaCloud
// ============================================================

defined('COTIZAAPP') or die;

// ─── Tipo de cambio MXN→USD (cache 12h, fallback 17.5) ──────
$usd_rate = (function() {
    $cache = dirname(__DIR__, 2) . '/data/exchange_rate.json';
    @mkdir(dirname($cache), 0755, true);
    $data = file_exists($cache) ? json_decode(file_get_contents($cache), true) : null;
    $now = time();
    if (is_array($data) && !empty($data['ts']) && ($now - (int)$data['ts']) < 43200 && !empty($data['usd'])) {
        return (float)$data['usd'];
    }
    // Fetch fresh — open.er-api.com es free, sin api key, fallback a Frankfurter
    $rate = null;
    foreach (['https://open.er-api.com/v6/latest/MXN', 'https://api.frankfurter.app/latest?from=MXN&to=USD'] as $url) {
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $body = @file_get_contents($url, false, $ctx);
        if (!$body) continue;
        $j = json_decode($body, true);
        if (isset($j['rates']['USD'])) { $rate = (float)$j['rates']['USD']; break; }
    }
    if ($rate && $rate > 0) {
        @file_put_contents($cache, json_encode(['ts' => $now, 'usd' => $rate]));
        return $rate;
    }
    // Fallback hardcoded si todo falla (ajustar manualmente si cambia mucho)
    return is_array($data) && !empty($data['usd']) ? (float)$data['usd'] : 0.0571;
})();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<script>
// App Capacitor: redirigir al login sin mostrar landing
// window.Capacitor se inyecta antes de que cualquier JS corra
if(window.Capacitor&&window.Capacitor.isNativePlatform&&window.Capacitor.isNativePlatform()){window.location.replace('/login');}
</script>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cotiza.cloud — ¿Sabes quién va a comprar antes de que te llame?</title>
<meta name="description" content="Manda cotizaciones y entérate cuáles se van a cerrar. El Radar analiza el comportamiento de tu cliente en tiempo real y te avisa cuando está listo para comprar.">
<meta property="og:title" content="Cotiza.cloud — ¿Sabes quién va a comprar antes de que te llame?">
<meta property="og:description" content="Manda cotizaciones y entérate cuáles se van a cerrar. Radar de inteligencia de ventas en tiempo real.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://cotiza.cloud">
<meta name="theme-color" content="#0e110f">
<link rel="icon" href="/assets/logo.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --body:'Plus Jakarta Sans','Apple Color Emoji','Segoe UI Emoji',sans-serif;
  --num:'DM Sans','Apple Color Emoji','Segoe UI Emoji',sans-serif;

  --g:#4fae7c;
  --g-hi:#6cc596;
  --g-soft:#14251c;
  --g-line:#254432;

  --ground:#0e110f;
  --panel:#191e1b;
  --panel-2:#1e241f;
  --line:#252b26;
  --ink:#e9ebe7;
  --muted:#9aa39c;
  --faint:#79817a;

  --fire:#ff6f66;
  --warm:#f0b03c;
  --cool:#5f9bea;
  --ok:#4ade80;
  --mid:#f7b955;
  --bad:#ff6f66;

  --r:14px;
  --r-lg:22px;
  --shadow:0 1px 2px rgba(0,0,0,.4), 0 18px 44px -22px rgba(0,0,0,.7);
  --maxw:1080px;
}

*{box-sizing:border-box}
body{
  margin:0;background:var(--ground);color:var(--ink);
  font-family:var(--body);font-size:17px;line-height:1.6;font-weight:500;
  -webkit-font-smoothing:antialiased;
}
img{max-width:100%}
b,strong{font-weight:700}
em{font-style:normal}

.wrap{max-width:var(--maxw);margin:0 auto;padding:0 20px}

/* ── marca ─────────────────────────────────────────────── */
.brandbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 0}
.brand{display:flex;align-items:center;gap:10px;font-weight:700;letter-spacing:-.01em}
.brand .ic{width:30px;height:30px;border-radius:8px;flex-shrink:0;
  background:url(/assets/logo.svg) center/cover no-repeat}
.brand span{font-size:17px}
.navr{display:flex;align-items:center;gap:clamp(8px,1.6vw,18px)}
.navlink{font:700 14px var(--body);color:var(--muted);text-decoration:none;padding:8px 4px;white-space:nowrap}
.navlink:hover{color:var(--ink)}
.navlink:focus-visible{outline:2px solid var(--g);outline-offset:2px;border-radius:6px}
.navcta{font:700 14px var(--body);color:#fff;text-decoration:none;white-space:nowrap;
  border:1.5px solid var(--g);border-radius:999px;padding:9px 18px;background:var(--g)}
.navcta:hover{background:var(--g-hi);border-color:var(--g-hi)}
.v-sm{display:none}
@media(max-width:620px){
  .v-lg{display:none} .v-sm{display:inline}
  .navcta{padding:9px 15px;font-size:13.5px} .navlink{font-size:13.5px}
  .brand span:last-child{font-size:15px}
  .brandbar{padding:16px 0}
}
@media(max-width:480px){ .brand span:last-child{display:none} }
.navcta:focus-visible{outline:2px solid var(--g);outline-offset:2px}

/* ── héroe: pantalla de bloqueo ────────────────────────── */
.hero{
  position:relative;overflow:hidden;
  background:radial-gradient(120% 90% at 20% 0%, #14402c 0%, #0d2a20 42%, #0b1a24 100%);
  color:#eef2ee;
  border-radius:var(--r-lg);
  margin:8px 0 0;
  padding:clamp(34px,6vw,64px) clamp(20px,4vw,52px) clamp(40px,6vw,64px);
}
.hero-grid{display:grid;grid-template-columns:1fr;gap:clamp(30px,5vw,56px);align-items:center}
@media(min-width:900px){ .hero-grid{grid-template-columns:1.05fr .95fr} }

.kick{font:700 12px var(--body);letter-spacing:.22em;text-transform:uppercase;color:#7fd2a4;margin:0 0 14px}
.hero h1{
  font:800 clamp(2.15rem,5.4vw,3.6rem)/1.03 var(--body);letter-spacing:-.035em;
  margin:0;text-wrap:balance;color:#fff;
}
.hero h1 em{color:#7fd2a4}
.hero .lede{margin:18px 0 0;max-width:44em;color:#b9c7bd;font-size:clamp(1rem,1.6vw,1.14rem)}
.hero-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}
.btn{
  display:inline-block;font:700 15px var(--body);text-decoration:none;
  border-radius:999px;padding:13px 26px;border:1.5px solid transparent;
}
.btn-primary{background:#fff;color:#0d2a20}
.btn-primary:hover{background:#e6f2ea}
.btn-ghost{border-color:rgba(255,255,255,.28);color:#dfe8e1}
.btn-ghost:hover{background:rgba(255,255,255,.08)}
.btn:focus-visible{outline:2px solid #7fd2a4;outline-offset:3px}
.hero .fine{margin:16px 0 0;font-size:13.5px;color:#8fa295}

/* teléfono */
.phone{
  justify-self:center;width:min(330px,86vw);
  border-radius:40px;padding:14px;
  background:linear-gradient(170deg,#2a3a31,#141c18);
  box-shadow:0 40px 90px -30px rgba(0,0,0,.75), 0 0 0 1px rgba(255,255,255,.07);
}
.screen{
  border-radius:29px;overflow:hidden;padding:26px 12px 20px;
  background:linear-gradient(175deg,#1d4a3c 0%,#173a3f 45%,#141f38 100%);
  min-height:520px;display:flex;flex-direction:column;
}
.clock{text-align:center;color:#fff;font:200 62px/1 var(--num);letter-spacing:-.02em;margin:6px 0 26px}
.stack{display:flex;flex-direction:column;gap:9px}
.nrow{
  background:rgba(238,242,238,.82);
  -webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px);
  border-radius:17px;padding:11px 12px;display:flex;gap:10px;align-items:flex-start;
}
.nrow .ni{width:34px;height:34px;border-radius:9px;flex-shrink:0;
  background:url(/assets/logo.svg) center/cover no-repeat}
.nrow .nx{flex:1;min-width:0}
.nrow .n1{display:flex;justify-content:space-between;gap:8px;align-items:baseline}
.nrow .nt{font:700 13.5px var(--body);color:#141613;letter-spacing:-.01em}
.nrow .nc{font:500 12px var(--body);color:#5d635d;flex-shrink:0}
.nrow .nb{font:500 13px/1.35 var(--body);color:#3a403a;margin-top:2px}
.nrow .nb b{color:#141613;font-weight:700}

/* ── secciones ─────────────────────────────────────────── */
section.blk{padding:clamp(52px,8vw,92px) 0}
.head{max-width:58rem}
.head .kick{color:var(--g)}
.head h2{
  font:800 clamp(1.65rem,3.6vw,2.5rem)/1.1 var(--body);letter-spacing:-.03em;
  margin:0;color:var(--ink);text-wrap:balance;
}
.head h2 em{color:var(--g)}
.head p{margin:14px 0 0;color:var(--muted);max-width:52em}

.card{
  background:var(--panel);border:1px solid var(--line);border-radius:var(--r-lg);
  box-shadow:var(--shadow);margin-top:clamp(24px,3.5vw,38px);overflow:hidden;
}
.card-pad{padding:clamp(18px,2.6vw,26px)}

/* buckets */
.bk{border-bottom:1px solid var(--line);padding:18px clamp(16px,2.4vw,24px)}
.bk:last-of-type{border-bottom:0}
.bk-h{display:flex;align-items:center;justify-content:space-between;gap:12px}
.bk-n{display:flex;align-items:center;gap:10px;font:700 16px var(--body);letter-spacing:-.01em}
.pip{width:11px;height:11px;border-radius:50%;flex-shrink:0}
.bk-s{font:500 14px var(--num);color:var(--muted);font-variant-numeric:tabular-nums;white-space:nowrap}
.bk-r{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-top:10px}
.bk-c{min-width:0}
.bk-c b{display:block;font:700 16px var(--body);letter-spacing:-.01em}
.bk-c span{display:block;font-size:14px;color:var(--muted);margin-top:1px}
.bk-m{text-align:right;flex-shrink:0}
.bk-m b{font:700 17px var(--num);font-variant-numeric:tabular-nums;letter-spacing:-.01em}
.dots{display:flex;gap:5px;justify-content:flex-end;margin-top:7px}
.dots i{width:9px;height:9px;border-radius:50%;background:var(--line);
  transform:scale(.55);opacity:.5;transition:transform .34s cubic-bezier(.2,.9,.3,1.3),opacity .34s}
.dots i.on{transform:scale(1);opacity:1}

.aitip{
  margin:0;background:var(--g-soft);border-top:1px solid var(--g-line);
  padding:16px clamp(16px,2.4vw,24px);display:flex;gap:12px;align-items:flex-start;
}
.tg{flex-shrink:0;background:var(--g);color:#fff;font:800 11px var(--body);
  padding:5px 11px;border-radius:999px;letter-spacing:.04em;white-space:nowrap}
.tg-inline{display:inline-block;vertical-align:1px;margin-left:7px}
.tx{font:600 15px/1.4 var(--body);color:var(--ink)}
.tx b{color:var(--g)}

/* comportamiento */
.beh-h{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;
  padding-bottom:16px;border-bottom:1px solid var(--line)}
.beh-h b{font:700 18px var(--body);letter-spacing:-.015em}
.beh-h span{display:block;color:var(--muted);font-size:14px;margin-top:2px}
.beh-h .amt{font:700 19px var(--num);font-variant-numeric:tabular-nums;white-space:nowrap}
.lbl{font:700 11px var(--body);letter-spacing:.14em;text-transform:uppercase;color:var(--faint);margin:18px 0 4px}
.ev{display:flex;align-items:center;justify-content:space-between;gap:14px;
  padding:14px 0;border-bottom:1px solid var(--line)}
.ev:last-of-type{border-bottom:0}
.ev-l{display:flex;gap:11px;align-items:flex-start;min-width:0}
.ev-l .pip{margin-top:7px;background:var(--g)}
.ev-l b{font:700 15px var(--body);display:block}
.ev-l span{font-size:13.5px;color:var(--muted)}
.ev-r{font:700 14.5px var(--body);white-space:nowrap;text-align:right}
.verdict{margin-top:16px;border:1.5px solid rgba(224,59,52,.35);background:rgba(224,59,52,.07);
  border-radius:var(--r);padding:13px 16px;font:700 16px var(--body);color:var(--fire)}

/* mesa */
.mesa-top{background:var(--g-soft);border-bottom:1px solid var(--g-line);
  padding:16px clamp(16px,2.4vw,24px)}
.mesa-top b{font:800 16px var(--body);color:var(--g)}
.mesa-top .meta{font:500 14px var(--num);color:var(--muted);font-variant-numeric:tabular-nums}
.mesa-top p{margin:5px 0 0;font-size:14.5px;color:var(--ink)}
.task{display:flex;gap:14px;padding:18px clamp(16px,2.4vw,24px);border-bottom:1px solid var(--line)}
.task:last-child{border-bottom:0}
.task .no{font:800 15px var(--num);color:var(--faint);width:16px;flex-shrink:0;padding-top:2px}
.task .body{flex:1;min-width:0}
.task .tt{display:flex;align-items:flex-start;gap:9px}
.task .tt b{font:700 16px/1.3 var(--body);letter-spacing:-.01em}
.task .who{font-size:14px;color:var(--muted);margin-top:1px}
.task .amt{font:700 16px var(--num);font-variant-numeric:tabular-nums;white-space:nowrap;flex-shrink:0}
.task .act{margin-top:9px;font-size:14.5px;display:flex;gap:9px;align-items:flex-start;flex-wrap:wrap}

/* móvil */
.mob{display:grid;grid-template-columns:1fr;gap:clamp(26px,4vw,44px);align-items:center}
@media(min-width:820px){ .mob{grid-template-columns:.9fr 1.1fr} }
.mob .head{max-width:none}
.phone-lite{
  justify-self:center;width:min(300px,84vw);border-radius:34px;padding:11px;
  background:linear-gradient(170deg,#2a312c,#171c18);
  box-shadow:0 30px 70px -28px rgba(0,0,0,.6);
}
.phone-lite .inner{background:var(--panel);border-radius:24px;overflow:hidden}
.mob-top{display:flex;align-items:baseline;justify-content:space-between;gap:10px;
  padding:15px 16px;border-bottom:1px solid var(--line)}
.mob-top .l{font:700 10.5px var(--body);letter-spacing:.13em;text-transform:uppercase;color:var(--faint)}
.mob-top .v{font:800 17px var(--num);color:var(--g);font-variant-numeric:tabular-nums}

/* precios */
.tiers{display:grid;gap:16px;grid-template-columns:1fr;margin-top:clamp(24px,3.5vw,38px)}
@media(min-width:760px){ .tiers{grid-template-columns:repeat(3,1fr)} }
.tier{background:var(--panel);border:1px solid var(--line);border-radius:var(--r-lg);
  padding:26px 22px;display:flex;flex-direction:column;box-shadow:var(--shadow)}
.tier.feat{border-color:var(--g);border-width:1.5px}
.tier .tn{font:800 15px var(--body);letter-spacing:.02em}
.tier .badge{display:inline-block;margin-left:8px;font:800 10.5px var(--body);letter-spacing:.06em;
  text-transform:uppercase;color:#fff;background:var(--g);border-radius:999px;padding:3px 9px;vertical-align:2px}
.tier .pr{display:flex;align-items:baseline;gap:6px;margin:14px 0 2px}
.tier .pr b{font:800 clamp(2rem,3.4vw,2.5rem) var(--num);letter-spacing:-.03em;font-variant-numeric:tabular-nums}
.tier .pr span{color:var(--muted);font-size:14.5px}
.tier .yr{font-size:13.5px;color:var(--faint)}
.tier ul{list-style:none;margin:20px 0 0;padding:0;display:flex;flex-direction:column;gap:9px;flex:1}
.tier li{position:relative;padding-left:22px;font-size:15px;color:var(--ink)}
.tier li::before{content:"";position:absolute;left:2px;top:8px;width:9px;height:9px;border-radius:50%;
  border:2px solid var(--g)}
.tier .cta{margin-top:22px;display:block;text-align:center;text-decoration:none;
  font:700 15px var(--body);border-radius:999px;padding:12px 20px;
  border:1.5px solid var(--g-line);color:var(--g);background:var(--panel)}
.tier .cta:hover{background:var(--g-soft)}
.tier.feat .cta{background:var(--g);color:#fff;border-color:var(--g)}
.tier.feat .cta:hover{background:var(--g-hi)}
.tier .cta:focus-visible{outline:2px solid var(--g);outline-offset:2px}

/* cierre */
.close{margin:clamp(48px,7vw,84px) 0 0;border-radius:var(--r-lg);overflow:hidden;color:#fff;
  background:radial-gradient(120% 95% at 76% 18%, #14402c 0%, #0d2a20 46%, #0b1a24 100%);
  padding:clamp(36px,5.6vw,62px) clamp(22px,4vw,52px);
  display:grid;grid-template-columns:1fr;gap:clamp(30px,4vw,50px);align-items:center}
@media(min-width:820px){ .close{grid-template-columns:1.12fr .88fr} }
.close h2{font:800 clamp(1.75rem,3.9vw,2.75rem)/1.05 var(--body);letter-spacing:-.035em;
  margin:0;color:#fff;text-wrap:balance}
.close h2 em{color:#7fd2a4}
.close p{margin:16px 0 0;max-width:30em;color:#b9c7bd;font-size:clamp(1rem,1.5vw,1.1rem)}
.close .btn-primary{margin-top:24px}
.warr{display:flex;flex-wrap:wrap;gap:9px;margin-top:20px}
.warr span{font:700 13px var(--body);color:#cfe4d8;border:1px solid rgba(255,255,255,.22);
  border-radius:999px;padding:7px 14px;background:rgba(255,255,255,.05)}

/* radar del cierre */
.rviz{position:relative;width:min(276px,70vw);aspect-ratio:1;justify-self:center}
.rviz .ring{position:absolute;border:1px solid rgba(127,210,164,.26);border-radius:50%}
.rviz .ring.r1{inset:0}.rviz .ring.r2{inset:17%}.rviz .ring.r3{inset:34%}.rviz .ring.r4{inset:51%}
.rviz .sweep{position:absolute;inset:0;border-radius:50%;
  background:conic-gradient(from 0deg, rgba(127,210,164,.40), rgba(127,210,164,.06) 34%, rgba(127,210,164,0) 62%);
  animation:sweep 3.6s linear infinite}
@keyframes sweep{to{transform:rotate(360deg)}}
.rviz .blip{position:absolute;width:11px;height:11px;border-radius:50%;background:#7fd2a4;
  animation:blip 3.6s ease-out infinite}
.rviz .b1{top:21%;left:61%}
.rviz .b2{top:57%;left:27%;animation-delay:1.2s}
.rviz .b3{top:69%;left:64%;animation-delay:2.4s}
@keyframes blip{0%{box-shadow:0 0 0 0 rgba(127,210,164,.55)}
  70%{box-shadow:0 0 0 19px rgba(127,210,164,0)}100%{box-shadow:0 0 0 0 rgba(127,210,164,0)}}
.rviz .core{position:absolute;inset:0;display:grid;place-items:center;
  font:800 12px var(--body);letter-spacing:.24em;color:#7fd2a4;opacity:.85}
@media (prefers-reduced-motion: reduce){ .rviz .sweep,.rviz .blip{animation:none} }

footer{padding:34px 0 60px;color:var(--faint);font-size:13.5px;text-align:center}
footer a{color:var(--muted);text-decoration:none;border-bottom:1px solid transparent}
footer a:hover{color:var(--ink);border-bottom-color:var(--line)}
footer a:focus-visible{outline:2px solid var(--g);outline-offset:3px;border-radius:4px}

/* interruptores de precio */
.sw-bar{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;
  gap:clamp(15px,2.6vw,30px);margin-top:clamp(22px,3vw,32px)}
.sw-group{display:flex;align-items:center;gap:11px}
.sw-div{width:1px;height:26px;background:var(--line);flex-shrink:0}
@media(max-width:560px){ .sw-div{display:none} }
.sw-lb{font:600 15px var(--body);color:var(--muted);cursor:pointer;user-select:none}
.sw-lb.on{color:var(--ink);font-weight:700}
.sw{position:relative;display:inline-block;width:50px;height:27px;flex-shrink:0}
.sw input{position:absolute;opacity:0;width:100%;height:100%;margin:0;cursor:pointer;z-index:2}
.sw .track{position:absolute;inset:0;background:var(--line);border-radius:999px;transition:background .25s}
.sw .track::before{content:"";position:absolute;height:21px;width:21px;left:3px;top:3px;background:#fff;
  border-radius:50%;transition:transform .25s;box-shadow:0 1px 4px rgba(0,0,0,.22)}
.sw input:checked+.track{background:var(--g)}
.sw input:checked+.track::before{transform:translateX(23px)}
.sw input:focus-visible+.track{outline:2px solid var(--g);outline-offset:2px}
.sw-save{font:800 12px var(--body);color:#fff;background:var(--g);padding:4px 12px;border-radius:999px}
@media (prefers-reduced-motion: reduce){ .sw .track,.sw .track::before{transition:none} }

/* proceso en 5 pasos */
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(148px,1fr));
  gap:clamp(16px,2.4vw,26px);margin-top:clamp(28px,3.6vw,42px)}
.step{text-align:center}
.step .num{width:46px;height:46px;border-radius:50%;background:var(--g);color:#fff;
  font:800 19px var(--num);display:grid;place-items:center;margin:0 auto 13px}
.step.end .num{background:var(--fire);box-shadow:0 0 0 7px rgba(224,59,52,.14)}
.step b{display:block;font:700 16px var(--body);letter-spacing:-.01em}
.step span{display:block;font-size:14px;color:var(--muted);margin-top:6px;line-height:1.45}

/* lo que no pasó */
.nono{display:grid;grid-template-columns:1fr;gap:12px;margin-top:14px}
@media(min-width:640px){ .nono{grid-template-columns:1fr 1fr} }
.nono div{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);
  padding:16px 18px;box-shadow:var(--shadow)}
.nono b{display:flex;align-items:center;gap:9px;font:700 15.5px var(--body);letter-spacing:-.01em}
.nono .mk{display:inline-grid;place-items:center;width:26px;height:26px;border-radius:50%;
  background:rgba(220,38,38,.12);border:1.5px solid rgba(220,38,38,.4);color:var(--bad);
  font:800 13px var(--body);flex-shrink:0;line-height:1}
.nono span{display:block;font-size:14px;color:var(--muted);margin-top:4px}

/* distintivo de plan */
.plan{display:inline-block;margin-left:9px;font:800 10.5px var(--body);letter-spacing:.09em;
  text-transform:uppercase;color:var(--g);border:1.5px solid var(--g-line);background:var(--g-soft);
  border-radius:999px;padding:4px 10px;vertical-align:2px;white-space:nowrap}
.aitip .plan{margin-left:auto;align-self:center}

/* lista de cotizaciones */
.tblwrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
table.qt{width:100%;border-collapse:collapse;min-width:640px}
.qt th{font:700 11px var(--body);letter-spacing:.12em;text-transform:uppercase;color:var(--faint);
  text-align:left;padding:14px clamp(14px,2vw,22px);border-bottom:1px solid var(--line);white-space:nowrap}
.qt th.n,.qt td.n{text-align:right}
.qt td{padding:15px clamp(14px,2vw,22px);border-bottom:1px solid var(--line);vertical-align:top}
.qt tbody tr:last-child td{border-bottom:0}
.qt .folio{font:600 11.5px var(--num);color:var(--faint);letter-spacing:.05em}
.qt .proj{font:700 15.5px var(--body);letter-spacing:-.01em;margin-top:2px}
.qt .cli{font:600 15px var(--body)}
.qt .amt2{font:700 16px var(--num);font-variant-numeric:tabular-nums;white-space:nowrap}
.st{display:inline-block;font:700 12px var(--body);border-radius:999px;padding:4px 11px;white-space:nowrap}
.mot{display:inline-flex;align-items:center;gap:6px;font:700 12px var(--body);
  border-radius:999px;padding:4px 11px;margin-top:8px;white-space:nowrap}
.mot .eye{font:600 11.5px var(--num);opacity:.8}

/* dos columnas: caso + ventas */
.two{display:grid;grid-template-columns:1fr;gap:clamp(16px,2.2vw,22px);
  margin-top:clamp(24px,3.5vw,38px);align-items:stretch}
@media(min-width:900px){ .two{grid-template-columns:1fr 1fr} }
.two>.card{margin-top:0;height:100%;display:flex;flex-direction:column}

/* tarjeta compacta */
.sm .beh-h b{font-size:16px}.sm .beh-h .amt{font-size:17px}
.sm .ev{padding:11px 0}.sm .ev-l b{font-size:14.5px}.sm .ev-l span{font-size:12.5px}
.sm .ev-r{font-size:13.5px}.sm .verdict{font-size:14.5px;padding:11px 14px}
.sm .lbl{font-size:10.5px;margin:14px 0 2px}

/* ventas cerradas */
.sl{font:700 11px var(--body);letter-spacing:.13em;text-transform:uppercase;color:var(--faint)}
.srow{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:8px}
.srow b{font:800 clamp(1.7rem,3.6vw,2.3rem) var(--num);letter-spacing:-.03em;
  font-variant-numeric:tabular-nums;color:var(--ink)}
.schip{font:800 12.5px var(--body);color:var(--g);background:var(--g-soft);
  border:1px solid var(--g-line);border-radius:999px;padding:5px 11px;white-space:nowrap}
.gbars{display:flex;align-items:flex-end;gap:clamp(6px,1.2vw,11px);height:168px;margin-top:auto;padding-top:22px}
.gcol{flex:1;display:flex;flex-direction:column;justify-content:flex-end;height:100%;min-width:0}
.gcol i{display:block;width:100%;height:0;border-radius:8px 8px 4px 4px;background:var(--g);
  transition:height .9s cubic-bezier(.2,.8,.3,1)}
.gcol.off i{background:var(--line)}
.gcol.g1 i{background:#a8ddc0}.gcol.g2 i{background:#5fb98a}.gcol.g3 i{background:var(--g)}
.gcol span{display:block;text-align:center;font:600 12.5px var(--body);color:var(--muted);margin-top:8px}
.gcol.g3 span{font-weight:800;color:var(--ink)}
.gleg{display:flex;margin-top:6px}
.gleg span{flex:1;text-align:center;font:600 12.5px var(--body);color:var(--faint)}
.gleg span.on{font-weight:800;color:var(--g)}
@media (prefers-reduced-motion: reduce){ .gcol i{transition:none} }

/* escalera de intentos (Mesa) *//* escalera de intentos (Mesa) */
.chip{display:inline-flex;align-items:center;gap:6px;font:800 12px var(--body);
  color:var(--mid);background:rgba(217,119,6,.12);border:1px solid rgba(217,119,6,.32);
  border-radius:999px;padding:5px 11px}
.ladder{margin-top:14px;border:1px solid var(--line);border-radius:var(--r);
  background:var(--panel-2);padding:14px 16px}
.ladder-h{display:flex;align-items:center;gap:10px;flex-wrap:wrap;
  font:700 13px var(--body);letter-spacing:.05em;text-transform:uppercase;color:var(--muted)}
.ladder-h .n{font:800 14px var(--num);color:var(--ink);letter-spacing:0;text-transform:none}
.ladder-dots{display:flex;gap:5px}
.ladder-dots i{width:10px;height:10px;border-radius:50%}
.ladder p{margin:9px 0 0;font-size:15px;line-height:1.45}

/* termómetro del equipo */
.trow{display:flex;align-items:center;gap:14px;padding:16px clamp(16px,2.4vw,24px);
  border-bottom:1px solid var(--line)}
.trow:last-child{border-bottom:0}
.trow.bad{background:rgba(220,38,38,.06);border-top:1px solid rgba(220,38,38,.3);align-items:flex-start}
.av{width:38px;height:38px;border-radius:50%;color:#fff;display:grid;place-items:center;
  font:700 14px var(--body);flex-shrink:0}
.tinfo{flex:1;min-width:0}
.tname{font:700 16px var(--body);letter-spacing:-.01em}
.tbar{height:9px;border-radius:999px;background:var(--panel-2);margin-top:8px;
  overflow:hidden;border:1px solid var(--line)}
.tfill{height:100%;border-radius:999px;width:0;transition:width .95s cubic-bezier(.2,.8,.3,1)}
.tnote{font:700 13.5px var(--body);color:var(--bad);margin-top:9px}
.tscore{font:800 25px var(--num);font-variant-numeric:tabular-nums;width:50px;
  text-align:right;flex-shrink:0;letter-spacing:-.02em}
.tarrow{font:700 15px var(--body);flex-shrink:0;width:14px;text-align:center}
@media (prefers-reduced-motion: reduce){ .tfill{transition:none} }

/* motion */
.rev{opacity:0;transform:translateY(16px);transition:opacity .6s ease,transform .6s cubic-bezier(.2,.7,.3,1)}
.rev.in{opacity:1;transform:none}
@keyframes notiIn{from{opacity:0;transform:translateY(14px) scale(.985)}to{opacity:1;transform:none}}
.nrow{opacity:0;animation:notiIn .55s cubic-bezier(.2,.8,.3,1.05) forwards}
.nrow:nth-child(1){animation-delay:.32s}
.nrow:nth-child(2){animation-delay:.49s}
.nrow:nth-child(3){animation-delay:.66s}
.nrow:nth-child(4){animation-delay:.83s}
@media (prefers-reduced-motion: reduce){
  .rev,.nrow{opacity:1!important;transform:none!important;transition:none!important;animation:none!important}
  .dots i{transform:scale(1);opacity:1;transition:none}
}
</style>
</head>
<body>
<div class="wrap">

  <div class="brandbar">
    <div class="brand"><span class="ic"></span><span>cotiza.cloud</span></div>
    <div class="navr">
      <a class="navlink" href="/login">Iniciar sesión</a>
      <a class="navcta" href="/registro"><span class="v-lg">Crear cuenta gratis</span><span class="v-sm">Crear cuenta</span></a>
    </div>
  </div>

  <!-- ── HÉROE ─────────────────────────────────────────── -->
  <div class="hero">
    <div class="hero-grid">
      <div>
        <p class="kick">Inteligencia de ventas</p>
        <h1>¿Sabes quién va a comprar<br><em>antes de que te llame?</em></h1>
        <p class="lede">¿Mandas cotizaciones y esperas? Nosotros te decimos <b style="color:#fff">cuáles se van a cerrar</b>. El Radar analiza el comportamiento de tu cliente en tiempo real y te avisa cuando está listo.</p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="/registro">Probar 30 días gratis</a>
        </div>
        <p class="fine">Sin tarjeta. Cotizas desde el primer minuto.</p>
      </div>

      <div class="phone" aria-label="Pantalla de bloqueo con notificaciones de CotizaCloud">
        <div class="screen">
          <div class="clock">11:47</div>
          <div class="stack" id="stack">
            <div class="nrow"><span class="ni"></span><div class="nx">
              <div class="n1"><span class="nt">Cotización aceptada</span><span class="nc">ahora</span></div>
              <div class="nb"><b>Mariana Gutiérrez</b> aceptó la cotización: Proyecto arquitectónico — Casa Lomas</div>
            </div></div>
            <div class="nrow"><span class="ni"></span><div class="nx">
              <div class="n1"><span class="nt">Cotización aceptada</span><span class="nc">hace 9 min</span></div>
              <div class="nb"><b>Arq. Fernando Ruiz</b> aceptó la cotización: Diseño de interiores — Depto. Andes 204</div>
            </div></div>
            <div class="nrow"><span class="ni"></span><div class="nx">
              <div class="n1"><span class="nt">Radar: Probable cierre</span><span class="nc">hace 23 min</span></div>
              <div class="nb">COT-2026-0271 — Cocina en L</div>
            </div></div>
            <div class="nrow"><span class="ni"></span><div class="nx">
              <div class="n1"><span class="nt">Radar: Predicción alta</span><span class="nc">hace 1 h</span></div>
              <div class="nb">COT-2026-0258 — Contenido web</div>
            </div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div><!-- /wrap del héroe -->

  <!-- ── EL SISTEMA ────────────────────────────────────── -->
  <section class="blk" id="sistema">
  <div class="wrap">
    <div class="head rev">
      <p class="kick">El sistema</p>
      <h2>Cotizaciones profesionales, <em>en minutos</em>.</h2>
      <p>Tu catálogo, tus precios, tus extras. El cliente la abre con la marca de tu empresa — <b>y el Radar ya viene adentro.</b></p>
    </div>

    <div class="card rev">
      <div class="tblwrap">
        <table class="qt">
          <thead>
            <tr><th>Proyecto</th><th>Cliente</th><th>Estatus</th><th class="n">Importe</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><div class="folio">COT-2026-0304</div><div class="proj">Oporto 5, Cerrada del Prado</div></td>
              <td><div class="cli">Daniela Ortiz</div></td>
              <td><span class="st" style="color:var(--ok);background:rgba(22,163,74,.12)">Aceptada</span></td>
              <td class="n"><span class="amt2">$93,705.00</span></td>
            </tr>
            <tr>
              <td><div class="folio">COT-2026-0299</div><div class="proj">Belcanto Residencial</div></td>
              <td><div class="cli">Mariana Gutiérrez</div></td>
              <td>
                <span class="st" style="color:var(--cool);background:rgba(61,127,214,.12)">Vista</span>
                <div><span class="mot" style="color:var(--fire);background:rgba(224,59,52,.11)">
                  <span class="pip" style="width:8px;height:8px;background:var(--fire)"></span>On Fire <span class="eye">👁 4</span></span></div>
              </td>
              <td class="n"><span class="amt2">$99,952.00</span></td>
            </tr>
            <tr>
              <td><div class="folio">COT-2026-0294</div><div class="proj">Calle Oropel #6, Áurea Residencial</div></td>
              <td><div class="cli">Paola Berrelleza</div></td>
              <td>
                <span class="st" style="color:var(--cool);background:rgba(61,127,214,.12)">Vista</span>
                <div><span class="mot" style="color:var(--mid);background:rgba(217,119,6,.12)">
                  <span class="pip" style="width:8px;height:8px;background:var(--warm)"></span>Validando precio <span class="eye">👁 2</span></span></div>
              </td>
              <td class="n"><span class="amt2">$69,880.00</span></td>
            </tr>
            <tr>
              <td><div class="folio">COT-2026-0292</div><div class="proj">Archanda 11, Bilbao Residencial</div></td>
              <td><div class="cli">Familia Cervantes</div></td>
              <td>
                <span class="st" style="color:var(--cool);background:rgba(61,127,214,.12)">Vista</span>
                <div><span class="mot" style="color:var(--g);background:var(--g-soft)">
                  <span class="pip" style="width:8px;height:8px;background:var(--g)"></span>Lectura comprometida <span class="eye">👁 4</span></span></div>
              </td>
              <td class="n"><span class="amt2">$67,381.60</span></td>
            </tr>
            <tr>
              <td><div class="folio">COT-2026-0293</div><div class="proj">Armería 4, Villa Satélite</div></td>
              <td><div class="cli">Grupo Miravalle</div></td>
              <td>
                <span class="st" style="color:var(--muted);background:rgba(120,130,120,.12)">Enviada</span>
                <div><span class="mot" style="color:var(--bad);background:rgba(220,38,38,.1)">✕ No abierta</span></div>
              </td>
              <td class="n"><span class="amt2">$196,378.80</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </section>

  <!-- ── PROCESO ───────────────────────────────────────── -->
  <section class="blk" id="proceso">
  <div class="wrap">
    <div class="head rev" style="max-width:46rem">
      <p class="kick">Tu proceso, potenciado</p>
      <h2>Tu expertise cierra ventas. Nosotros te decimos <em>a quién llamar primero</em>.</h2>
    </div>
    <div class="steps rev">
      <div class="step"><div class="num">1</div><b>Genera la cotización</b>
        <span>Con tu marca, tus productos y tus condiciones.</span></div>
      <div class="step"><div class="num">2</div><b>Compártela por link</b>
        <span>WhatsApp o correo. Se abre al instante, sin descargar nada.</span></div>
      <div class="step"><div class="num">3</div><b>El cliente la abre</b>
        <span>Y tú te enteras en el momento.</span></div>
      <div class="step"><div class="num">4</div><b>El Radar la clasifica</b>
        <span>Por intención de compra, no por número de visitas.</span></div>
      <div class="step end"><div class="num">5</div><b>Tú cierras</b>
        <span>Con el momento exacto para levantar el teléfono.</span></div>
    </div>
  </div>
  </section>

  <!-- ── RADAR ─────────────────────────────────────────── -->
  <section class="blk" id="radar">
  <div class="wrap">
    <div class="head rev">
      <p class="kick">Radar de ventas</p>
      <h2>El Radar detecta el <em>interés real</em> de tu cliente.</h2>
      <p>Nuestro algoritmo y CotizaCloud AI analizan su comportamiento y clasifican su intención de compra con los datos de tu propio negocio. <b>No solo registramos visitas.</b></p>
    </div>

    <div class="card rev">
      <div class="bk">
        <div class="bk-h">
          <div class="bk-n"><span class="pip" style="background:var(--fire)"></span>On Fire</div>
          <div class="bk-s">$443,900 · 5 cotizaciones</div>
        </div>
        <div class="bk-r">
          <div class="bk-c"><b>Daniela Ortiz</b><span>Organización de boda</span></div>
          <div class="bk-m"><b>$132,000</b>
            <div class="dots" data-on="5" data-color="var(--fire)"><i></i><i></i><i></i><i></i><i></i></div>
          </div>
        </div>
      </div>

      <div class="bk">
        <div class="bk-h">
          <div class="bk-n"><span class="pip" style="background:var(--warm)"></span>Probable cierre</div>
          <div class="bk-s">$723,400 · 8 cotizaciones</div>
        </div>
        <div class="bk-r">
          <div class="bk-c"><b>Mariana Gutiérrez</b><span>Proyecto arquitectónico</span></div>
          <div class="bk-m"><b>$148,500</b>
            <div class="dots" data-on="4" data-color="var(--warm)"><i></i><i></i><i></i><i></i><i></i></div>
          </div>
        </div>
      </div>

      <div class="bk">
        <div class="bk-h">
          <div class="bk-n"><span class="pip" style="background:var(--cool)"></span>Validando precio</div>
          <div class="bk-s">$83,400 · 3 cotizaciones</div>
        </div>
        <div class="bk-r">
          <div class="bk-c"><b>Sra. Camacho</b><span>Cancelería de aluminio</span></div>
          <div class="bk-m"><b>$37,900</b>
            <div class="dots" data-on="2" data-color="var(--cool)"><i></i><i></i><i></i><i></i><i></i></div>
          </div>
        </div>
      </div>

      <div class="aitip">
        <span class="tg">✨ CotizaCloud AI</span>
        <span class="tx">Empieza por <b>Daniela</b>: su intención de compra subió hoy. Llámala y cierra fecha.</span>
        <span class="plan">Business</span>
      </div>
    </div>
      <div class="nono rev">
      <div><b><span class="mk">✕</span> Nadie la abrió</b><span>Días enviada y sin abrirse. Te enteras antes de que se enfríe.</span></div>
      <div><b><span class="mk">✕</span> Ni siquiera se mandó</b><span>Tú lo ves aunque el asesor no lo diga.</span></div>
    </div>
  </div>
  </section>

  <!-- ── LA PRUEBA + VENTAS ────────────────────────────── -->
  <section class="blk">
  <div class="wrap">
    <div class="head rev">
      <p class="kick">La frase que ya conoces</p>
      <h2>«Lo voy a pensar…»<br><em>Cotiza igual. Cierra más.</em></h2>
      <p>Todos la dicen. La diferencia está en lo que hacen después.</p>
    </div>

    <div class="two">
      <div class="card card-pad sm rev">
        <div class="beh-h">
          <div><b>Cancelería de aluminio</b><span>Sra. Camacho</span></div>
          <div class="amt">$37,900</div>
        </div>

        <p class="lbl">Intención de compra</p>
        <div class="ev">
          <div class="ev-l"><span class="pip"></span><div><b>Hoy, 9:41 pm</b><span>iPhone · Guadalajara, MX</span></div></div>
          <div class="ev-r" style="color:var(--fire)">🔥 Interés al alza</div>
        </div>
        <div class="ev">
          <div class="ev-l"><span class="pip"></span><div><b>Hoy, 2:31 pm</b><span>iPhone · Guadalajara, MX</span></div></div>
          <div class="ev-r" style="color:var(--g)">↩ Volvió a leerla</div>
        </div>

        <div class="verdict">Clasificación: 🔥 On Fire</div>
      </div>

      <div class="card card-pad rev">
        <div class="sl">Ventas cerradas</div>
        <div class="srow"><b>$247,900</b><span class="schip">▲ +31% vs abril</span></div>

        <div class="gbars">
          <div class="gcol off"><i data-h="38"></i><span>feb</span></div>
          <div class="gcol off"><i data-h="44"></i><span>mar</span></div>
          <div class="gcol off"><i data-h="36"></i><span>abr</span></div>
          <div class="gcol g1"><i data-h="54"></i><span>may</span></div>
          <div class="gcol g2"><i data-h="68"></i><span>jun</span></div>
          <div class="gcol g3"><i data-h="100"></i><span>jul</span></div>
        </div>
        <div class="gleg"><span>sin Radar</span><span class="on">con Radar</span></div>
      </div>
    </div>
  </div>
  </section>

  <!-- ── MESA ──────────────────────────────────────────── -->
  <section class="blk">
  <div class="wrap">
    <div class="head rev">
      <p class="kick">La Mesa de Trabajo <span class="plan">Business</span></p>
      <h2>Tu día ya viene <em>priorizado</em>.</h2>
      <p>El Radar te dice quién está caliente. La Mesa te dice qué hacer hoy, y en qué orden.</p>
    </div>

    <div class="card rev">
      <div class="mesa-top">
        <b>Mesa de hoy</b> <span class="meta">6 pendientes · $1,478,982 en juego</span>
        <p>Empieza por la <b>#1</b> — es la de mayor intención de compra.</p>
      </div>

      <div class="task">
        <div class="no">1</div>
        <div class="body">
          <div class="tt">
            <span class="pip" style="background:var(--fire);margin-top:7px"></span>
            <div style="flex:1;min-width:0">
              <b>Organización de boda — Hacienda La Cantera</b>
              <div class="who">Daniela Ortiz</div>
            </div>
            <div class="amt">$132,000</div>
          </div>
          <div class="act"><span class="tg">✨ CotizaCloud AI</span>
            <span>Su intención de compra <b>subió hoy</b> — llámale y cierra fecha.</span></div>
        </div>
      </div>

      <div class="task">
        <div class="no">2</div>
        <div class="body">
          <div class="tt">
            <span class="pip" style="background:var(--warm);margin-top:7px"></span>
            <div style="flex:1;min-width:0">
              <b>Seguro empresarial — flotilla 12 unidades</b>
              <div class="who">Grupo Miravalle</div>
            </div>
            <div class="amt">$181,400</div>
          </div>
          <div class="act"><span><b>⏰ Vence hoy</b> — reenvíala con un recordatorio.</span></div>
        </div>
      </div>

      <div class="task">
        <div class="no">3</div>
        <div class="body">
          <div class="tt">
            <span class="pip" style="background:var(--mid);margin-top:7px"></span>
            <div style="flex:1;min-width:0">
              <b>Bonaterra Residencial — Arboretum 11</b>
              <div class="who">Alexa Gastelum · la vio hoy</div>
            </div>
            <div class="amt">$37,800</div>
          </div>
          <div class="act"><span class="chip">⚡ Revivió tras descarte</span>
            <span>La habías dado por perdida y el cliente volvió.</span></div>

          <div class="ladder">
            <div class="ladder-h">
              <span>Intentos sin respuesta</span>
              <span class="n">4 de 4</span>
              <span class="ladder-dots">
                <i style="background:var(--mid)"></i><i style="background:var(--mid)"></i>
                <i style="background:var(--bad)"></i><i style="background:var(--bad)"></i>
              </span>
            </div>
            <p>Llevas <b>4 «no contestó» seguidos</b>. La Mesa propone suspenderla. Si contesta una vez, la cuenta vuelve a cero.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  </section>

  <!-- ── TERMÓMETRO / RANKING ──────────────────────────── -->
  <section class="blk">
  <div class="wrap">
    <div class="head rev">
      <p class="kick">Termómetro del equipo <span class="plan">Business</span></p>
      <h2>¿Quién de tu equipo <em>sí está vendiendo</em>?</h2>
      <p>Mide el seguimiento real de cada asesor — sin perseguirlos. No cuántas mandó: si atendió a tiempo y si cerró. <span class="tg tg-inline">✨ CotizaCloud AI</span></p>
    </div>

    <div class="card rev">
      <div class="trow">
        <span class="av" style="background:var(--ok)">RS</span>
        <div class="tinfo">
          <div class="tname">Renata Salgado</div>
          <div class="tbar"><div class="tfill" data-w="82" style="background:var(--ok)"></div></div>
        </div>
        <span class="tscore" style="color:var(--ok)">82</span>
        <span class="tarrow" style="color:var(--ok)">▲</span>
      </div>

      <div class="trow">
        <span class="av" style="background:var(--mid)">LO</span>
        <div class="tinfo">
          <div class="tname">Luis Ortega</div>
          <div class="tbar"><div class="tfill" data-w="67" style="background:var(--warm)"></div></div>
        </div>
        <span class="tscore" style="color:var(--mid)">67</span>
        <span class="tarrow" style="color:var(--ok)">▲</span>
      </div>

      <div class="trow bad">
        <span class="av" style="background:var(--bad)">KN</span>
        <div class="tinfo">
          <div class="tname">Karla Núñez</div>
          <div class="tbar"><div class="tfill" data-w="41" style="background:var(--bad)"></div></div>
          <div class="tnote">⚠ Reprobada — no da seguimiento</div>
        </div>
        <span class="tscore" style="color:var(--bad)">41</span>
        <span class="tarrow" style="color:var(--bad)">▼</span>
      </div>

    </div>
  </div>
  </section>

  <!-- ── MÓVIL ─────────────────────────────────────────── -->
  <section class="blk">
  <div class="wrap">
    <div class="mob">
      <div class="head rev">
        <p class="kick">Dónde se usa</p>
        <h2>Cotiza en la oficina o <em>frente al cliente</em>.</h2>
        <p>En la computadora trabajas el día completo, con todo a la vista. En el teléfono armas y editas la cotización ahí mismo, en la obra o en su sala — y te avisa en cuanto alguien está listo.</p>
      </div>

      <div class="phone-lite rev">
        <div class="inner">
          <div class="mob-top">
            <span class="l">Oportunidades activas</span>
            <span class="v" data-count="1250000">$1.25M en juego</span>
          </div>
          <div class="bk">
            <div class="bk-h">
              <div class="bk-n" style="font-size:15px"><span class="pip" style="background:var(--fire)"></span>On Fire</div>
              <div class="bk-s" style="font-size:13px">$443,900 · 5</div>
            </div>
            <div class="bk-r">
              <div class="bk-c"><b style="font-size:15px">Daniela Ortiz</b><span style="font-size:13px">Organización de boda</span></div>
              <div class="bk-m"><b style="font-size:15px">$132,000</b>
                <div class="dots" data-on="5" data-color="var(--fire)"><i></i><i></i><i></i><i></i><i></i></div>
              </div>
            </div>
          </div>
          <div class="bk">
            <div class="bk-h">
              <div class="bk-n" style="font-size:15px"><span class="pip" style="background:var(--warm)"></span>Probable cierre</div>
              <div class="bk-s" style="font-size:13px">$723,400 · 8</div>
            </div>
            <div class="bk-r">
              <div class="bk-c"><b style="font-size:15px">Mariana Gutiérrez</b><span style="font-size:13px">Proyecto arquitectónico</span></div>
              <div class="bk-m"><b style="font-size:15px">$148,500</b>
                <div class="dots" data-on="4" data-color="var(--warm)"><i></i><i></i><i></i><i></i><i></i></div>
              </div>
            </div>
          </div>
          <div class="aitip" style="padding:14px 16px">
            <span class="tg">✨ AI</span>
            <span class="tx" style="font-size:14px">Empieza por <b>Daniela</b> — su interés subió hoy.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  </section>

  <!-- ── PRECIOS ───────────────────────────────────────── -->
  <section class="blk" id="precios">
  <div class="wrap">
    <div class="head rev">
      <p class="kick">Precios</p>
      <h2>Empieza gratis <em>30 días</em>.</h2>
      <p>Cotizas desde el primer minuto. Si al mes no te sirvió, no pagas nada.</p>
    </div>

    <div class="sw-bar rev">
      <div class="sw-group">
        <span class="sw-lb on" id="lbMensual">Mensual</span>
        <label class="sw"><input type="checkbox" id="swCiclo" aria-label="Cambiar a pago anual"><span class="track"></span></label>
        <span class="sw-lb" id="lbAnual">Anual</span>
        <span class="sw-save">Ahorra 20%</span>
      </div>
      <span class="sw-div"></span>
      <div class="sw-group">
        <span class="sw-lb on" id="lbMXN">MXN</span>
        <label class="sw"><input type="checkbox" id="swMoneda" aria-label="Cambiar a dólares"><span class="track"></span></label>
        <span class="sw-lb" id="lbUSD">USD</span>
      </div>
    </div>

    <div class="tiers rev">
      <div class="tier">
        <div class="tn">Lite</div>
        <div class="pr"><b data-m="199" data-a="159">$199</b><span>/ mes</span></div>
        <div class="yr"><span data-m="2388" data-a="1910">$2,388</span> al año</div>
        <ul>
          <li>Cotizaciones y ventas ilimitadas</li>
          <li>Clientes y catálogo</li>
          <li>Cupones y descuentos</li>
          <li>Feedback de tus clientes</li>
          <li>Señal de interés en cada cotización</li>
          <li>1 usuario</li>
        </ul>
        <a class="cta" href="/registro?plan=lite">Probar 30 días</a>
      </div>

      <div class="tier feat">
        <div class="tn">Pro <span class="badge">El más usado</span></div>
        <div class="pr"><b data-m="499" data-a="399">$499</b><span>/ mes</span></div>
        <div class="yr"><span data-m="5988" data-a="4790">$5,988</span> al año</div>
        <ul>
          <li>Todo lo de Lite</li>
          <li><b>Radar completo</b> con buckets y alertas</li>
          <li><b>Asesores ilimitados</b> — todo tu equipo de ventas, sin costo por usuario</li>
          <li>Costos, márgenes y reportes</li>
          <li>Descuentos inteligentes</li>
        </ul>
        <a class="cta" href="/registro?plan=pro">Probar 30 días</a>
      </div>

      <div class="tier">
        <div class="tn">Business</div>
        <div class="pr"><b data-m="2999" data-a="2399">$2,999</b><span>/ mes</span></div>
        <div class="yr"><span data-m="35988" data-a="28790">$35,988</span> al año</div>
        <ul>
          <li>Todo lo de Pro</li>
          <li><b>Mesa de Trabajo</b> y CotizaCloud AI</li>
          <li>Termómetro y ranking del equipo</li>
          <li>Costos avanzados, proveedores y marketing</li>
          <li>Permisos por asesor</li>
          <li>Demo y 4 horas de capacitación</li>
        </ul>
        <a class="cta" href="#" onclick="var f=document.getElementById('czl-fab');if(f){f.click();}return false;">Agenda una demo</a>
      </div>
    </div>
  </div>
  </section>

<div class="wrap">

  <div class="close rev">
    <div>
      <h2>Deja de adivinar <em>a quién llamarle</em>.</h2>
      <p>Manda tu primera cotización hoy y mira quién está más cerca de decir que sí.</p>
      <div class="warr">
        <span>30 días gratis</span><span>Sin tarjeta</span><span>Cancelas cuando quieras</span>
      </div>
      <a class="btn btn-primary" href="/registro">Probar 30 días gratis</a>
    </div>
    <div class="rviz" aria-hidden="true">
      <span class="ring r1"></span><span class="ring r2"></span>
      <span class="ring r3"></span><span class="ring r4"></span>
      <span class="sweep"></span>
      <span class="blip b1"></span><span class="blip b2"></span><span class="blip b3"></span>
      <span class="core">RADAR</span>
    </div>
  </div>

  <footer>
    Cotiza.cloud &copy; <?= date('Y') ?> &middot; <a href="/login">Iniciar sesión</a> &middot; <a href="/registro">Crear cuenta</a> &middot; <a href="/terminos">Términos</a> &middot; <a href="/privacidad">Privacidad</a>
  </footer>
</div>

<script>
(function(){
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // pintar los puntos de intención con su color
  document.querySelectorAll('.dots').forEach(function(d){
    var n = parseInt(d.dataset.on,10) || 0, c = d.dataset.color;
    d.querySelectorAll('i').forEach(function(el,i){ if(i<n) el.style.background = c; });
    if (reduce) d.querySelectorAll('i').forEach(function(el,i){ if(i<n) el.classList.add('on'); });
  });

  if (reduce) {
    document.querySelectorAll('.rev').forEach(function(el){ el.classList.add('in'); });
    document.querySelectorAll('.tfill').forEach(function(f){ f.style.width = f.dataset.w + '%'; });
    document.querySelectorAll('.gcol i').forEach(function(g){ g.style.height = g.dataset.h + '%'; });
    return;
  }


  // ── precios: ciclo (mensual/anual) y moneda (MXN/USD)
  var swCiclo=document.getElementById('swCiclo'), swMon=document.getElementById('swMoneda');
  if (swCiclo && swMon) {
    var TC = <?= json_encode($usd_rate) ?>;   // MXN→USD, del servidor (cache 12h)
    var pesos = function(v){ return '$' + v.toLocaleString('es-MX'); };
    var dolares = function(v){ return 'US$' + Math.round(v*TC).toLocaleString('en-US'); };
    var pintar = function(){
      var anual = swCiclo.checked, usd = swMon.checked;
      document.querySelectorAll('[data-m]').forEach(function(el){
        var v = parseInt(anual ? el.dataset.a : el.dataset.m, 10);
        el.textContent = usd ? dolares(v) : pesos(v);
      });
      document.getElementById('lbMensual').classList.toggle('on', !anual);
      document.getElementById('lbAnual').classList.toggle('on', anual);
      document.getElementById('lbMXN').classList.toggle('on', !usd);
      document.getElementById('lbUSD').classList.toggle('on', usd);
    };
    swCiclo.addEventListener('change', pintar);
    swMon.addEventListener('change', pintar);
    pintar();
  }

  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if (!e.isIntersecting) return;
      e.target.classList.add('in');
      e.target.querySelectorAll('.tfill').forEach(function(f,i){
        setTimeout(function(){ f.style.width = f.dataset.w + '%'; }, 120 + i*90);
      });
      e.target.querySelectorAll('.gcol i').forEach(function(g,i){
        setTimeout(function(){ g.style.height = g.dataset.h + '%'; }, 120 + i*80);
      });
      e.target.querySelectorAll('.dots').forEach(function(d){
        var n = parseInt(d.dataset.on,10) || 0;
        d.querySelectorAll('i').forEach(function(el,i){
          if (i<n) setTimeout(function(){ el.classList.add('on'); }, 140 + i*90);
        });
      });
      io.unobserve(e.target);
    });
  }, { threshold:.18, rootMargin:'0px 0px -8% 0px' });

  document.querySelectorAll('.rev').forEach(function(el){ io.observe(el); });
})();
</script>

<!-- ── Chat de soporte / captura de lead (landing, anónimo) ── -->
<style>
#czl-fab{position:fixed;right:20px;bottom:22px;z-index:9000;display:flex;flex-direction:column-reverse;align-items:flex-end;gap:8px;cursor:pointer}
#czl-bubble{position:relative;width:56px;height:56px;border-radius:50%;background:#1a5c38;color:#fff;border:none;box-shadow:0 6px 20px rgba(26,92,56,.4);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:24px;transition:transform .15s}
#czl-fab:hover #czl-bubble{transform:scale(1.06)}
#czl-label{background:#fff;color:#1a1a18;font:600 13px -apple-system,sans-serif;padding:8px 13px;border-radius:16px;box-shadow:0 4px 14px rgba(0,0,0,.14);border:1px solid rgba(0,0,0,.05);white-space:nowrap}
#czl-win{position:fixed;right:20px;bottom:22px;width:360px;max-width:calc(100vw - 24px);height:500px;max-height:calc(100vh - 44px);background:#fff;border-radius:16px;box-shadow:0 14px 44px rgba(0,0,0,.28);z-index:9001;display:none;flex-direction:column;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
#czl-win.open{display:flex}
.czl-hdr{background:#1a5c38;color:#fff;padding:15px 16px;display:flex;align-items:center;justify-content:space-between}
.czl-hdr .t{font-weight:700;font-size:15px}.czl-hdr .s{font-size:11px;opacity:.85;margin-top:2px}
.czl-hdr button{background:rgba(255,255,255,.18);border:none;color:#fff;width:28px;height:28px;border-radius:8px;cursor:pointer;font-size:15px}
.czl-body{flex:1;overflow-y:auto;padding:14px;background:#f7f6f3;display:flex;flex-direction:column;gap:8px}
.czl-m{max-width:82%;padding:9px 12px;border-radius:13px;font-size:13.5px;line-height:1.45;white-space:pre-wrap;word-break:break-word}
.czl-m.usuario{align-self:flex-end;background:#1a5c38;color:#fff;border-bottom-right-radius:4px}
.czl-m.agente{align-self:flex-start;background:#fff;border:1px solid #e2e2dc;border-bottom-left-radius:4px}
.czl-sys{align-self:center;background:#eee;color:#666;font-size:11.5px;padding:5px 11px;border-radius:99px;text-align:center}
.czl-form{padding:14px;display:flex;flex-direction:column;gap:9px}
.czl-form .intro{font-size:13px;color:#4a4a46;line-height:1.5;margin-bottom:2px}
.czl-form input,.czl-foot textarea{border:1.5px solid #c8c8c0;border-radius:10px;padding:10px 12px;font:400 14px inherit;outline:none;width:100%}
.czl-form input:focus,.czl-foot textarea:focus{border-color:#1a5c38}
.czl-form textarea{resize:none;min-height:60px}
.czl-form .err{color:#c53030;font-size:12px;display:none}
.czl-form button{background:#1a5c38;color:#fff;border:none;border-radius:10px;padding:11px;font:700 14px inherit;cursor:pointer}
.czl-foot{padding:10px;border-top:1px solid #e2e2dc;display:flex;gap:8px;align-items:flex-end}
.czl-foot button{background:#1a5c38;color:#fff;border:none;border-radius:10px;width:42px;height:40px;cursor:pointer;font-size:17px}
@media(max-width:768px){#czl-win{right:0;left:0;bottom:0;width:100vw;max-width:100vw;height:100vh;height:100dvh;max-height:100vh;max-height:100dvh;border-radius:0}}
@media print{#czl-fab,#czl-win{display:none!important}}
</style>

<div id="czl-fab" role="button" tabindex="0" aria-label="Abrir chat">
  <button id="czl-bubble" type="button">💬</button>
  <span id="czl-label">¿Dudas? Escríbenos</span>
</div>
<div id="czl-win" role="dialog" aria-label="Chat">
  <div class="czl-hdr">
    <div><div class="t">¿Te ayudamos?</div><div class="s" id="czl-status">Cargando…</div></div>
    <button type="button" id="czl-min" title="Cerrar">▽</button>
  </div>
  <!-- Pre-chat: captura de lead -->
  <div class="czl-form" id="czl-form">
    <div class="intro" id="czl-intro">Déjanos tus datos y tu pregunta. Te respondemos por aquí.</div>
    <input type="text" id="czl-nombre" placeholder="Tu nombre" autocomplete="name">
    <input type="email" id="czl-email" placeholder="Tu correo" autocomplete="email">
    <textarea id="czl-msg" placeholder="¿En qué te ayudamos?"></textarea>
    <div class="err" id="czl-err">Completa nombre, un correo válido y tu mensaje.</div>
    <button type="button" id="czl-start">Enviar</button>
  </div>
  <!-- Chat -->
  <div class="czl-body" id="czl-cbody" style="display:none"></div>
  <div class="czl-foot" id="czl-cfoot" style="display:none">
    <textarea id="czl-input" rows="1" placeholder="Escribe tu mensaje…"></textarea>
    <button type="button" id="czl-send">➤</button>
  </div>
</div>

<script>
(function(){
  var fab=document.getElementById('czl-fab'), win=document.getElementById('czl-win'),
      form=document.getElementById('czl-form'), cbody=document.getElementById('czl-cbody'),
      cfoot=document.getElementById('czl-cfoot'), statusEl=document.getElementById('czl-status'),
      input=document.getElementById('czl-input'), sendBtn=document.getElementById('czl-send'),
      startBtn=document.getElementById('czl-start'), errEl=document.getElementById('czl-err');
  var token='', lastId=0, pollTimer=null, saludoShown=false, started=false;
  try{ token=localStorage.getItem('cz_sop_token')||''; }catch(e){}

  function scrollB(){ cbody.scrollTop=cbody.scrollHeight; }
  function addMsg(a,c){ var d=document.createElement('div'); d.className='czl-m '+a; d.textContent=c; cbody.appendChild(d); scrollB(); }
  function addSys(t){ var d=document.createElement('div'); d.className='czl-sys'; d.textContent=t; cbody.appendChild(d); scrollB(); }
  function showChat(){ form.style.display='none'; cbody.style.display='flex'; cfoot.style.display='flex'; started=true; }

  async function poll(){
    var url='/api/soporte/poll'+(token?('?token='+encodeURIComponent(token)+'&since='+lastId):'');
    try{
      var r=await fetch(url); var d=await r.json(); if(!d.ok)return;
      if(d.horario){ statusEl.textContent=d.horario.online?('🟢 '+d.horario.msg):d.horario.msg; }
      if(token && d.conversacion_id){
        if(!started) showChat();
        if(!saludoShown && (d.mensajes||[]).length===0 && d.horario && d.horario.saludo){ addSys(d.horario.saludo); saludoShown=true; }
        (d.mensajes||[]).forEach(function(m){ addMsg(m.autor,m.cuerpo); lastId=Math.max(lastId,m.id); saludoShown=true; });
      }
    }catch(e){}
  }
  function startPoll(){ if(!pollTimer) pollTimer=setInterval(function(){ if(!document.hidden) poll(); },5000); }

  async function enviarPrimero(){
    var nombre=document.getElementById('czl-nombre').value.trim();
    var email=document.getElementById('czl-email').value.trim();
    var msg=document.getElementById('czl-msg').value.trim();
    if(!nombre || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email) || !msg){ errEl.style.display='block'; return; }
    errEl.style.display='none'; startBtn.disabled=true;
    try{
      var r=await fetch('/api/soporte',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({accion:'enviar',nombre:nombre,email:email,cuerpo:msg,token:token})});
      var d=await r.json();
      if(d.ok){ token=d.token||token; try{localStorage.setItem('cz_sop_token',token);}catch(e){} showChat(); addMsg('usuario',msg); if(d.mensaje_id)lastId=Math.max(lastId,d.mensaje_id); saludoShown=true; startPoll(); }
      else { errEl.textContent=d.error==='datos'?'Completa nombre, un correo válido y tu mensaje.':'No se pudo enviar, intenta de nuevo.'; errEl.style.display='block'; }
    }catch(e){}
    startBtn.disabled=false;
  }
  async function enviar(){
    var t=input.value.trim(); if(!t)return; sendBtn.disabled=true;
    try{
      var r=await fetch('/api/soporte',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({accion:'enviar',cuerpo:t,token:token})});
      var d=await r.json();
      if(d.ok){ addMsg('usuario',t); if(d.mensaje_id)lastId=Math.max(lastId,d.mensaje_id); input.value=''; input.style.height='auto'; }
    }catch(e){}
    sendBtn.disabled=false; input.focus();
  }

  function open(){ win.classList.add('open'); fab.style.display='none'; poll(); startPoll(); if(started)input.focus(); }
  function close(){ win.classList.remove('open'); fab.style.display='flex'; if(pollTimer){clearInterval(pollTimer);pollTimer=null;} }
  fab.addEventListener('click', open);
  document.getElementById('czl-min').addEventListener('click', close);
  startBtn.addEventListener('click', enviarPrimero);
  sendBtn.addEventListener('click', enviar);
  input.addEventListener('keydown', function(e){ if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();enviar();} });
  input.addEventListener('input', function(){ input.style.height='auto'; input.style.height=Math.min(90,input.scrollHeight)+'px'; });

  // Si ya había conversación previa (token), precargar al abrir; si no, mostrar form.
  if(token){ poll(); }
})();
</script>

</body>
</html>
