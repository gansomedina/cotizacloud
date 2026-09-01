<?php
// ============================================================
//  cotiza.cloud — modules/dashboard/_razon_modal.php
//  Selector de MOTIVO DE DESCARTE, compartido.
//
//  Lo usan el 👎 del Radar y el 👎 del renglón de la Mesa. Los dos escriben la
//  misma marca y los dos sacan la cotización de la mesa, así que los dos piden
//  el mismo motivo — el de la pastilla "Descartar" del cajón, que siempre lo
//  exigió. Antes el 👎 era un tap sin explicación y por ahí salía la mayoría de
//  los descartes: el dato de POR QUÉ perdemos se perdía.
//
//  Expone una función global:
//      czPedirRazon(function (clave, texto) { ... })
//  El callback SOLO corre si el asesor elige un motivo. Si cancela (botón,
//  Escape o clic afuera) no se llama y no se descarta nada.
//
//  "Otro" abre un campo de texto y EXIGE que se escriba algo: si fuera
//  opcional nadie lo llenaría y volveríamos a tener descartes sin explicación
//  —justo lo que se acaba de arreglar exigiendo motivo al 👎—. Además "Otro"
//  es la salida fácil; sin texto obligatorio no cuesta nada elegirla.
//  En los demás motivos `texto` llega null.
//
//  Se incluye desde dos páginas distintas; el guard evita emitirlo dos veces
//  si algún día conviven en la misma.
// ============================================================
defined('COTIZAAPP') or die;

if (!empty($GLOBALS['CZ_RAZON_MODAL_EMITIDO'])) return;
$GLOBALS['CZ_RAZON_MODAL_EMITIDO'] = true;
?>
<div id="czrz-back" class="czrz-back" onclick="czRzCerrar(event)">
  <div class="czrz-card" role="dialog" aria-modal="true" aria-labelledby="czrz-t" onclick="event.stopPropagation()">
    <div class="czrz-t" id="czrz-t">¿Por qué lo descartas?</div>
    <div class="czrz-s">Sale de tu mesa. Si el cliente revive, vuelve solo.</div>
    <div class="czrz-opts">
      <?php foreach (Mesa::RAZONES as $rk => $rl): ?>
      <button type="button" class="czrz-b" data-k="<?= e($rk) ?>" onclick="czRzElegir('<?= e($rk) ?>')"><?= e($rl) ?></button>
      <?php endforeach; ?>
    </div>
    <?php // "Otro" no descarta de inmediato: abre esto y pide el motivo escrito. ?>
    <div class="czrz-otro" id="czrz-otro">
      <input type="text" id="czrz-txt" maxlength="200" autocomplete="off"
             placeholder="¿Por qué? Ej: se mudó de ciudad">
      <button type="button" class="czrz-ok" onclick="czRzOtro()">Descartar</button>
    </div>
    <button type="button" class="czrz-x" onclick="czRzCerrar()">Cancelar</button>
  </div>
</div>
<style>
.czrz-back{display:none;position:fixed;inset:0;z-index:9000;background:rgba(20,20,18,.44);
  align-items:center;justify-content:center;padding:20px}
.czrz-back.on{display:flex}
.czrz-card{background:#fff;border-radius:16px;padding:20px;max-width:340px;width:100%;
  box-shadow:0 18px 48px rgba(0,0,0,.22);font-family:'Inter',system-ui,sans-serif}
.czrz-t{font:700 16px 'Inter',system-ui,sans-serif;color:#1a1a18;margin-bottom:4px}
.czrz-s{font:400 12.5px 'Inter',system-ui,sans-serif;color:#8a8a82;margin-bottom:14px;line-height:1.45}
.czrz-opts{display:flex;flex-direction:column;gap:6px}
.czrz-b{text-align:left;padding:11px 13px;border:1px solid #e5e5e0;background:#fafaf8;border-radius:10px;
  font:600 13.5px 'Inter',system-ui,sans-serif;color:#3a3a34;cursor:pointer}
.czrz-b:hover{background:#f2f2ec;border-color:#d6d6ce}
.czrz-x{margin-top:12px;width:100%;padding:9px;border:0;background:none;color:#9a9a92;
  font:600 12.5px 'Inter',system-ui,sans-serif;cursor:pointer}
.czrz-x:hover{color:#57574f}
/* Campo de "Otro" — oculto hasta que se elige esa opción. */
.czrz-otro{display:none;gap:6px;margin-top:10px}
.czrz-otro.on{display:flex}
.czrz-otro input{flex:1;min-width:0;padding:11px 12px;border:1px solid #d6d6ce;border-radius:10px;
  font:500 13.5px 'Inter',system-ui,sans-serif;color:#1a1a18;outline:none}
.czrz-otro input:focus{border-color:#1a5c38}
.czrz-ok{flex:none;padding:0 16px;border:0;border-radius:10px;background:#1a5c38;color:#fff;
  font:700 13px 'Inter',system-ui,sans-serif;cursor:pointer}
.czrz-ok:disabled{opacity:.45;cursor:not-allowed}
</style>
<script>
(function(){
  var cb = null;
  var back  = document.getElementById('czrz-back');
  var caja  = document.getElementById('czrz-otro');
  var txt   = document.getElementById('czrz-txt');
  var okBtn = caja.querySelector('.czrz-ok');

  function cerrarCaja(){ caja.classList.remove('on'); txt.value = ''; okBtn.disabled = true; }

  // Cancelar NO descarta: se suelta el callback sin llamarlo. Un descarte
  // silencioso por cerrar el diálogo sería justo lo que este cambio evita.
  window.czRzCerrar = function(ev){
    if (ev && ev.target !== back) return;   // clic dentro de la tarjeta
    back.classList.remove('on'); cerrarCaja(); cb = null;
  };
  function entregar(clave, texto){
    var f = cb; back.classList.remove('on'); cerrarCaja(); cb = null;
    if (f) f(clave, texto || null);
  }
  window.czRzElegir = function(clave){
    // "Otro" no cierra: abre el campo y espera el texto. Los demás motivos ya
    // se explican solos y descartan de inmediato.
    if (clave === 'otro') { caja.classList.add('on'); txt.focus(); return; }
    entregar(clave, null);
  };
  window.czRzOtro = function(){
    var v = txt.value.trim();
    if (!v) { txt.focus(); return; }   // obligatorio: sin texto no descarta
    entregar('otro', v);
  };
  // El botón se habilita solo con algo escrito, para que se vea que hace falta.
  txt.addEventListener('input', function(){ okBtn.disabled = txt.value.trim() === ''; });
  txt.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); window.czRzOtro(); } });

  window.czPedirRazon = function(fn){ cb = fn; cerrarCaja(); back.classList.add('on'); };
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && back.classList.contains('on')) window.czRzCerrar();
  });
})();
</script>
