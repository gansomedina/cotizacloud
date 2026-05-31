<?php
// ============================================================
//  CotizaApp — modules/auth/bienvenida.php
//  GET /bienvenida — Wizard de onboarding (Calibra tus armas)
//  Se muestra una sola vez a la empresa nueva (admin).
// ============================================================
defined('COTIZAAPP') or die;

$usuario = Auth::usuario();
$empresa = Auth::empresa();
$nombre  = $usuario['nombre'] ?? '';
$primer  = trim(explode(' ', $nombre)[0] ?? '');
$emp_nom = $empresa['nombre'] ?? 'tu empresa';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Bienvenido — CotizaCloud</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--g:#1a5c38;--g2:#22784a;--bg:#f4f4f0;--text:#1a1a18;--t2:#4a4a46;--t3:#6a6a64;--body:'Plus Jakarta Sans',sans-serif}
body{font-family:var(--body);background:linear-gradient(160deg,#eef7f2 0%,#f4f4f0 60%);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}

/* ── Portada ── */
.cover{background:#fff;border-radius:22px;box-shadow:0 20px 60px rgba(0,0,0,.14);width:100%;max-width:560px;padding:44px 40px 38px;text-align:center;animation:wzf .4s ease}
.cover-kicker{font:700 11px var(--body);letter-spacing:.16em;text-transform:uppercase;color:var(--g);margin-bottom:16px}
.cover h1{font:800 28px var(--body);letter-spacing:-.03em;line-height:1.15;margin-bottom:14px}
.cover p{font:400 15.5px var(--body);color:var(--t2);line-height:1.65;max-width:430px;margin:0 auto 12px}
.cover p b{color:var(--text)}
.cover-cta{margin-top:24px;background:var(--g);color:#fff;border:none;border-radius:13px;padding:15px 44px;font:800 16px var(--body);cursor:pointer;box-shadow:0 8px 22px rgba(26,92,56,.30);transition:transform .15s,opacity .15s}
.cover-cta:hover{transform:translateY(-1px);opacity:.95}

/* ── Radar animado ── */
.radar{position:relative;width:170px;height:170px;margin:0 auto 26px;border-radius:50%;background:radial-gradient(circle,rgba(26,92,56,.07) 0%,rgba(26,92,56,.02) 72%);border:2px solid rgba(26,92,56,.22);overflow:hidden}
.radar i{position:absolute;border-radius:50%;border:1px solid rgba(26,92,56,.16)}
.radar .r1{inset:28px}.radar .r2{inset:56px}
.radar .cx,.radar .cy{position:absolute;background:rgba(26,92,56,.12)}
.radar .cx{left:50%;top:0;bottom:0;width:1px;transform:translateX(-.5px)}
.radar .cy{top:50%;left:0;right:0;height:1px;transform:translateY(-.5px)}
.radar .sweep{position:absolute;inset:0;border-radius:50%;background:conic-gradient(from 0deg,rgba(26,92,56,.32),rgba(26,92,56,0) 70deg,transparent 360deg);animation:rdSpin 3.5s linear infinite}
@keyframes rdSpin{to{transform:rotate(360deg)}}
.radar .blip{position:absolute;width:9px;height:9px;border-radius:50%;background:var(--g);transform:translate(-50%,-50%);animation:rdBlip 3.5s ease-out infinite}
@keyframes rdBlip{0%,100%{opacity:.2}40%{opacity:1;box-shadow:0 0 0 5px rgba(26,92,56,0)}48%{box-shadow:0 0 0 0 rgba(26,92,56,.45)}}
.radar .tgt{position:absolute;left:50%;top:50%;width:11px;height:11px;border-radius:50%;background:var(--g);transform:translate(-50%,-50%);box-shadow:0 0 0 3px rgba(26,92,56,.18)}
.radar .ring{position:absolute;left:50%;top:50%;width:18px;height:18px;border-radius:50%;border:2px solid var(--g);transform:translate(-50%,-50%);animation:rdPulse 2s ease-out infinite}
@keyframes rdPulse{0%{transform:translate(-50%,-50%) scale(.7);opacity:1}100%{transform:translate(-50%,-50%) scale(2.4);opacity:0}}

.wz{background:#fff;border-radius:22px;box-shadow:0 20px 60px rgba(0,0,0,.12);width:100%;max-width:560px;overflow:hidden;position:relative;display:none}
.wz-top{height:5px;background:#e7e5df}
.wz-bar{height:100%;background:var(--g);width:16%;transition:width .3s ease}
.wz-skip{position:absolute;top:16px;right:18px;font:600 12px var(--body);color:var(--t3);text-decoration:none;z-index:2}
.wz-skip:hover{color:var(--t2)}
.wz-body{padding:46px 40px 32px;text-align:center;min-height:420px;display:flex;flex-direction:column}
.wz-slide{display:none;animation:wzf .35s ease;flex:1;flex-direction:column;justify-content:center}
.wz-slide.on{display:flex}
@keyframes wzf{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.wz-ico{width:84px;height:84px;border-radius:22px;background:var(--g);display:flex;align-items:center;justify-content:center;font-size:42px;margin:0 auto 22px;box-shadow:0 8px 20px rgba(26,92,56,.28)}
.wz-step{font:700 11px var(--body);letter-spacing:.14em;text-transform:uppercase;color:var(--g);margin-bottom:10px}
.wz-h{font:800 25px var(--body);letter-spacing:-.02em;line-height:1.2;margin-bottom:14px}
.wz-p{font:400 15.5px var(--body);color:var(--t2);line-height:1.65;max-width:420px;margin:0 auto}
.wz-p b{color:var(--text)}
.wz-why{margin-top:16px;background:#eef7f2;border:1px solid #cce7d8;border-radius:12px;padding:12px 16px;font:500 13.5px var(--body);color:var(--g);line-height:1.5;max-width:440px;margin-left:auto;margin-right:auto}
.wz-nav{display:flex;align-items:center;justify-content:space-between;padding:18px 40px 30px;gap:12px}
.wz-dots{display:flex;gap:7px}
.wz-dot{width:8px;height:8px;border-radius:50%;background:#d9d6cf;transition:all .2s}
.wz-dot.on{background:var(--g);width:22px;border-radius:5px}
.wz-btn{border:none;border-radius:12px;padding:13px 26px;font:700 15px var(--body);cursor:pointer;transition:opacity .15s}
.wz-btn:hover{opacity:.9}
.wz-next{background:var(--g);color:#fff}
.wz-back{background:transparent;color:var(--t3);font-weight:600}
.wz-back:disabled{opacity:0;pointer-events:none}
@media(max-width:560px){.wz-body{padding:40px 24px 24px}.wz-nav{padding:16px 24px 26px}.wz-h{font-size:22px}}
</style>
</head>
<body>

<!-- ── PORTADA ── -->
<div class="cover" id="wzCover">
  <div class="radar">
    <i class="r1"></i><i class="r2"></i>
    <span class="cx"></span><span class="cy"></span>
    <span class="sweep"></span>
    <span class="blip" style="left:74%;top:32%;animation-delay:.2s"></span>
    <span class="blip" style="left:30%;top:64%;animation-delay:1.1s"></span>
    <span class="blip" style="left:64%;top:72%;animation-delay:1.9s"></span>
    <span class="blip" style="left:36%;top:30%;animation-delay:2.6s"></span>
    <span class="ring"></span>
    <span class="tgt"></span>
  </div>
  <div class="cover-kicker">Bienvenido a bordo</div>
  <h1>Gracias por elegir CotizaCloud 🎯</h1>
  <p>Acabas de activar tu <b>radar de ventas</b>. Tu sistema no solo crea cotizaciones profesionales: <b>detecta qué clientes están listos para comprar, te dice a quién contactar y monitorea a tus asesores en tiempo real.</b></p>
  <p>Pero toda arma poderosa necesita calibrarse. En <b>1 minuto</b> te enseñamos cómo dejarla lista para <b>dar justo en el blanco.</b></p>
  <button class="cover-cta" id="wzComenzar">Comenzar →</button>
</div>

<div class="wz">
  <div class="wz-top"><div class="wz-bar" id="wzBar"></div></div>
  <a href="#" class="wz-skip" id="wzSkip">Saltar</a>
  <div class="wz-body">

    <!-- 1 -->
    <div class="wz-slide on">
      <div class="wz-ico">👋</div>
      <div class="wz-step">Bienvenido</div>
      <h2 class="wz-h">¡Hola<?= $primer ? ', '.htmlspecialchars($primer) : '' ?>! <?= htmlspecialchars($emp_nom) ?> ya está lista</h2>
      <p class="wz-p">En menos de <b>1 minuto</b> te mostramos las <b>4 armas</b> que usarás para vender más con CotizaCloud. Vale la pena — esto es lo que separa a un vendedor que cierra de uno que persigue clientes a ciegas.</p>
    </div>

    <!-- 2 -->
    <div class="wz-slide">
      <div class="wz-ico">🏢</div>
      <div class="wz-step">Arma 1 de 4</div>
      <h2 class="wz-h">Configura tu empresa</h2>
      <p class="wz-p">Es la base de todo. Sube tu <b>logo</b>, tus <b>datos fiscales</b> y arma tu <b>catálogo</b> de productos o servicios con precios.</p>
      <div class="wz-why">💡 Una empresa bien configurada hace que tus cotizaciones se vean profesionales y las generes en segundos.</div>
    </div>

    <!-- 3 -->
    <div class="wz-slide">
      <div class="wz-ico">🛡️</div>
      <div class="wz-step">Arma 2 de 4</div>
      <h2 class="wz-h">El Escudo</h2>
      <p class="wz-p">Abre las cotizaciones de tus clientes <b>solo desde tu dispositivo con la sesión iniciada</b>. Así el sistema sabe que eres tú y no el cliente.</p>
      <div class="wz-why">💡 Si no lo cuidas, el sistema cree que el cliente está interesado cuando en realidad eras tú revisando. Te haría perder tiempo.</div>
    </div>

    <!-- 4 -->
    <div class="wz-slide">
      <div class="wz-ico">🎯</div>
      <div class="wz-step">Arma 3 de 4</div>
      <h2 class="wz-h">El Radar</h2>
      <p class="wz-p">Tu herramienta diaria. Te dice <b>quién está a punto de comprar</b> y a quién llamar primero. Lee el botón <b>"?"</b> de cada cliente y marca <b>👍 / 👎</b> según su interés real.</p>
      <div class="wz-why">💡 Entra al Radar cada mañana: empieza por "Probable cierre", que junta a tus clientes más calientes.</div>
    </div>

    <!-- 5 -->
    <div class="wz-slide">
      <div class="wz-ico">🌡️</div>
      <div class="wz-step">Arma 4 de 4</div>
      <h2 class="wz-h">El Termómetro</h2>
      <p class="wz-p">Es tu <b>calificación como vendedor</b>, del 0 al 100. El sistema <b>analiza todo tu trabajo</b> (envíos, seguimiento, cierres, cobranza) y la calcula solo.</p>
      <div class="wz-why">💡 No sube con clics sueltos: sube cuando haces bien las otras 3 armas. Es tu espejo objetivo.</div>
    </div>

    <!-- 6 -->
    <div class="wz-slide">
      <div class="wz-ico">🚀</div>
      <div class="wz-step">¡Listo!</div>
      <h2 class="wz-h">Estás listo para vender</h2>
      <p class="wz-p">Empieza por <b>configurar tu empresa</b> y manda tu primera cotización. Encontrarás esta guía completa en cualquier momento en el menú de <b>Ayuda</b>.</p>
      <div class="wz-why">¿Tienes dudas del sistema? No dudes en contactarnos por el <b>Chat de soporte</b> (el botón 💬 en tu pantalla), o pídenos ahí mismo una <b>cita de configuración</b>. Estamos para ayudarte.</div>
    </div>

  </div>
  <div class="wz-nav">
    <button class="wz-btn wz-back" id="wzBack" disabled>← Atrás</button>
    <div class="wz-dots" id="wzDots"></div>
    <button class="wz-btn wz-next" id="wzNext">Siguiente →</button>
  </div>
</div>

<script>
(function(){
  var CSRF = <?= json_encode(csrf_token()) ?>;
  // Portada → wizard
  var cover = document.getElementById('wzCover'), wz = document.querySelector('.wz');
  document.getElementById('wzComenzar').addEventListener('click', function(){
    cover.style.display = 'none'; wz.style.display = 'block';
  });
  var slides = document.querySelectorAll('.wz-slide');
  var total = slides.length, i = 0;
  var bar = document.getElementById('wzBar'), dots = document.getElementById('wzDots');
  var back = document.getElementById('wzBack'), next = document.getElementById('wzNext');

  for (var d=0; d<total; d++){ var s=document.createElement('div'); s.className='wz-dot'+(d===0?' on':''); dots.appendChild(s); }
  var dotEls = dots.children;

  function render(){
    for (var k=0;k<total;k++){ slides[k].classList.toggle('on', k===i); dotEls[k].classList.toggle('on', k===i); }
    bar.style.width = Math.round(((i+1)/total)*100) + '%';
    back.disabled = (i===0);
    next.textContent = (i===total-1) ? 'Ir a mi panel' : 'Siguiente →';
  }
  async function finish(){
    next.disabled = true;
    try{ await fetch('/api/onboarding/completar', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF}, body:'{}'}); }catch(e){}
    window.location.href = '/dashboard';
  }
  next.addEventListener('click', function(){ if(i===total-1){ finish(); } else { i++; render(); } });
  back.addEventListener('click', function(){ if(i>0){ i--; render(); } });
  document.getElementById('wzSkip').addEventListener('click', function(e){ e.preventDefault(); finish(); });
  render();
})();
</script>
</body>
</html>
