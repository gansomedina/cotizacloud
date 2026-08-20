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
//      czPedirRazon(function (clave) { ... })
//  El callback SOLO corre si el asesor elige un motivo. Si cancela (botón,
//  Escape o clic afuera) no se llama y no se descarta nada.
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
      <button type="button" class="czrz-b" onclick="czRzElegir('<?= e($rk) ?>')"><?= e($rl) ?></button>
      <?php endforeach; ?>
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
</style>
<script>
(function(){
  var cb = null;
  var back = document.getElementById('czrz-back');
  // Cancelar NO descarta: se suelta el callback sin llamarlo. Un descarte
  // silencioso por cerrar el diálogo sería justo lo que este cambio evita.
  window.czRzCerrar = function(ev){
    if (ev && ev.target !== back) return;   // clic dentro de la tarjeta
    back.classList.remove('on'); cb = null;
  };
  window.czRzElegir = function(clave){
    var f = cb; back.classList.remove('on'); cb = null;
    if (f) f(clave);
  };
  window.czPedirRazon = function(fn){ cb = fn; back.classList.add('on'); };
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && back.classList.contains('on')) window.czRzCerrar();
  });
})();
</script>
