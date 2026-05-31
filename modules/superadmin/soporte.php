<?php
// ============================================================
//  CotizaApp — modules/superadmin/soporte.php
//  Panel de chat de soporte (lado agente / superadmin)
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

$sel = (int)($_GET['c'] ?? 0);

// Lista de conversaciones
$convs = DB::query(
    "SELECT c.id, c.estado, c.no_leidos_agente, c.ultimo_mensaje_at,
            e.nombre AS emp_nombre, e.slug AS emp_slug, e.plan AS emp_plan,
            u.nombre AS usr_nombre, u.email AS usr_email
     FROM soporte_conversaciones c
     JOIN empresas e  ON e.id = c.empresa_id
     JOIN usuarios u  ON u.id = c.usuario_id
     ORDER BY (c.estado='abierta') DESC, c.no_leidos_agente DESC, c.ultimo_mensaje_at DESC
     LIMIT 100"
);

$abiertas = 0;
foreach ($convs as $cv) if ($cv['estado'] === 'abierta') $abiertas++;

// Conversación seleccionada
$conv = null; $mensajes = []; $ctx = null;
if ($sel) {
    $conv = DB::row(
        "SELECT c.*, e.nombre AS emp_nombre, e.slug AS emp_slug, e.plan AS emp_plan,
                e.created_at AS emp_creada, u.nombre AS usr_nombre, u.email AS usr_email
         FROM soporte_conversaciones c
         JOIN empresas e ON e.id = c.empresa_id
         JOIN usuarios u ON u.id = c.usuario_id
         WHERE c.id = ?",
        [$sel]
    );
    if ($conv) {
        DB::execute("UPDATE soporte_conversaciones SET no_leidos_agente = 0 WHERE id = ?", [$sel]);
        $mensajes = DB::query(
            "SELECT autor, cuerpo, created_at FROM soporte_mensajes WHERE conversacion_id = ? ORDER BY id ASC",
            [$sel]
        );
    }
}
$page_title = 'Soporte';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Soporte — CotizaCloud</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--bg:#f4f4f0;--white:#fff;--border:#e2e2dc;--border2:#c8c8c0;--text:#1a1a18;--t2:#4a4a46;--t3:#6a6a64;--g:#1a5c38;--g-bg:#eef7f2;--g-border:#b8ddc8;--amb:#92400e;--amb-bg:#fef3c7;--danger:#c53030;--r:12px;--r-sm:9px;--sh:0 1px 3px rgba(0,0,0,.06);--body:'Plus Jakarta Sans',sans-serif}
*,*::before,*::after{box-sizing:border-box}
body{font-family:var(--body);background:var(--bg);color:var(--text);margin:0;font-size:14px}
.wrap{max-width:1100px;margin:0 auto;padding:18px 20px 60px}
.hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px}
.hdr h1{font-size:20px;font-weight:800;margin:0;display:flex;align-items:center;gap:10px}
.badge{background:var(--danger);color:#fff;font:700 11px var(--body);padding:2px 9px;border-radius:99px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 15px;border-radius:var(--r-sm);font:600 13px var(--body);cursor:pointer;border:1.5px solid var(--border2);background:var(--white);color:var(--t2);text-decoration:none}
.grid{display:grid;grid-template-columns:320px 1fr;gap:16px}
@media(max-width:760px){.grid{grid-template-columns:1fr}}
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden}
.conv{display:block;padding:12px 14px;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text)}
.conv:hover{background:#fafaf8}
.conv.active{background:var(--g-bg)}
.conv .top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.conv .name{font-weight:700;font-size:13.5px}
.conv .emp{font-size:11.5px;color:var(--t3);margin-top:1px}
.conv .prev{font-size:12px;color:var(--t2);margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tag{font:700 10px var(--body);padding:2px 7px;border-radius:5px;background:var(--g);color:#fff}
.dot{width:8px;height:8px;border-radius:50%;background:var(--danger);display:inline-block}
.st-cerrada{opacity:.55}
.chat{display:flex;flex-direction:column;height:70vh;min-height:420px}
.chat-hdr{padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px}
.ctx{font-size:11.5px;color:var(--t3)}
.msgs{flex:1;overflow-y:auto;padding:16px;background:#faf9f6;display:flex;flex-direction:column;gap:8px}
.m{max-width:78%;padding:9px 13px;border-radius:12px;font-size:13.5px;line-height:1.45;white-space:pre-wrap;word-break:break-word}
.m.usuario{align-self:flex-start;background:var(--white);border:1px solid var(--border)}
.m.agente{align-self:flex-end;background:var(--g);color:#fff}
.m .h{font-size:10px;opacity:.6;margin-top:4px;text-align:right}
.compose{padding:12px;border-top:1px solid var(--border);display:flex;gap:8px}
.compose textarea{flex:1;border:1.5px solid var(--border2);border-radius:var(--r-sm);padding:10px 12px;font:400 14px var(--body);resize:none;outline:none}
.compose textarea:focus{border-color:var(--g)}
.compose button{background:var(--g);color:#fff;border:none;border-radius:var(--r-sm);padding:0 18px;font:700 14px var(--body);cursor:pointer}
.empty{padding:60px 20px;text-align:center;color:var(--t3)}
.info{background:var(--amb-bg);color:var(--amb);font-size:12px;padding:8px 14px;border-bottom:1px solid var(--border)}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <h1>💬 Soporte <?php if ($abiertas): ?><span class="badge"><?= $abiertas ?> abiertas</span><?php endif; ?></h1>
    <a href="/superadmin" class="btn">← Volver al panel</a>
  </div>

  <div class="grid">
    <!-- Lista -->
    <div class="card" style="max-height:74vh;overflow-y:auto">
      <?php if (!$convs): ?>
        <div class="empty">Sin conversaciones aún.</div>
      <?php else: foreach ($convs as $cv):
        $act = ($sel === (int)$cv['id']) ? 'active' : '';
        $cerr = $cv['estado'] === 'cerrada' ? 'st-cerrada' : '';
      ?>
        <a class="conv <?= $act ?> <?= $cerr ?>" href="/superadmin/soporte?c=<?= (int)$cv['id'] ?>">
          <div class="top">
            <span class="name"><?= e($cv['usr_nombre'] ?: 'Usuario') ?></span>
            <?php if ((int)$cv['no_leidos_agente'] > 0): ?><span class="dot" title="<?= (int)$cv['no_leidos_agente'] ?> sin leer"></span><?php endif; ?>
          </div>
          <div class="emp"><span class="tag"><?= e($cv['emp_plan'] ?: '—') ?></span> <?= e($cv['emp_nombre']) ?></div>
          <div class="prev"><?= e($cv['ultimo_mensaje_at'] ? date('d/m H:i', strtotime($cv['ultimo_mensaje_at'])) : '') ?> · <?= $cv['estado']==='abierta'?'Abierta':'Cerrada' ?></div>
        </a>
      <?php endforeach; endif; ?>
    </div>

    <!-- Conversación -->
    <div class="card">
      <?php if (!$conv): ?>
        <div class="empty">Selecciona una conversación para responder.</div>
      <?php else: ?>
        <div class="chat" id="chat" data-conv="<?= (int)$conv['id'] ?>">
          <div class="chat-hdr">
            <div>
              <div style="font-weight:700"><?= e($conv['usr_nombre']) ?> · <?= e($conv['emp_nombre']) ?></div>
              <div class="ctx">
                Plan <?= e($conv['emp_plan'] ?: '—') ?> ·
                <?= e($conv['usr_email']) ?> ·
                Empresa desde <?= e(date('d/m/Y', strtotime($conv['emp_creada']))) ?> ·
                <a href="/superadmin/empresa/<?= (int)$conv['empresa_id'] ?>" target="_blank" style="color:var(--g)">ver empresa ↗</a>
              </div>
            </div>
            <span style="font:700 11px var(--body);color:<?= $conv['estado']==='abierta'?'var(--g)':'var(--t3)' ?>"><?= $conv['estado']==='abierta'?'Abierta':'Cerrada' ?></span>
          </div>
          <div class="msgs" id="msgs">
            <?php foreach ($mensajes as $m): ?>
              <div class="m <?= $m['autor'] ?>"><?= e($m['cuerpo']) ?><div class="h"><?= date('H:i', strtotime($m['created_at'])) ?></div></div>
            <?php endforeach; ?>
          </div>
          <div class="compose">
            <textarea id="reply" rows="2" placeholder="Escribe tu respuesta…"></textarea>
            <button id="send" type="button">Enviar</button>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
const CSRF = <?= json_encode(csrf_token()) ?>;
<?php if ($conv): ?>
(function(){
  const chat = document.getElementById('chat');
  const convId = parseInt(chat.dataset.conv, 10);
  const msgs = document.getElementById('msgs');
  const reply = document.getElementById('reply');
  const send = document.getElementById('send');

  function scrollBottom(){ msgs.scrollTop = msgs.scrollHeight; }
  scrollBottom();

  function addMsg(autor, cuerpo, hora){
    const d = document.createElement('div');
    d.className = 'm ' + autor;
    d.textContent = cuerpo;
    const h = document.createElement('div'); h.className='h'; h.textContent = hora || '';
    d.appendChild(h); msgs.appendChild(d); scrollBottom();
  }

  async function doSend(){
    const txt = reply.value.trim();
    if (!txt) return;
    send.disabled = true;
    try {
      const r = await fetch('/api/soporte', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},
        body: JSON.stringify({accion:'responder', conversacion_id:convId, cuerpo:txt})
      });
      const d = await r.json();
      if (d.ok){ addMsg('agente', txt, new Date().toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'})); reply.value=''; }
    } catch(e){}
    send.disabled = false; reply.focus();
  }
  send.addEventListener('click', doSend);
  reply.addEventListener('keydown', e => { if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); doSend(); }});
  // Sin auto-reload: el push avisa de mensajes nuevos. Recarga manual para verlos
  // (no recargamos solos para no borrar lo que estés escribiendo).
})();
<?php endif; ?>
</script>
</body>
</html>
