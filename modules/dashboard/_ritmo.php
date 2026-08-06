<?php
// ============================================================
//  Dashboard partial — Rendimiento del equipo (desde la Mesa)
//  SOLO ADMIN. Solo lectura (RitmoAsesor).
//  Veredicto (Cierra · Limpio) + Ritmo (vencidas · citas), todo
//  auto-ajustable a la empresa / al propio asesor. Sin valores fijos.
//  NOTA: comparte scope con index.php — variables con prefijo rt_.
// ============================================================
defined('COTIZAAPP') or die;

if (!Auth::es_admin()) return;

$rt_on = false;
try { $rt_on = (int) DB::val("SELECT mesa_activa FROM empresas WHERE id = ?", [EMPRESA_ID]) >= 1; }
catch (Throwable $rt_e) { $rt_on = false; }
if (!$rt_on) return;

$rt_filas = RitmoAsesor::empresa(EMPRESA_ID);
if (!$rt_filas) return;

$rt_colmap  = ['rojo' => '#dc2626', 'amarillo' => '#d97706', 'verde' => '#16a34a'];
$rt_n_alerta = count(array_filter($rt_filas, fn($x) => $x['semaforo'] !== 'verde'));
?>
<div class="slabel">Rendimiento del equipo
  <?php if ($rt_n_alerta > 0): ?><span style="font:700 11px var(--body);color:#dc2626"><?= $rt_n_alerta ?> por atender</span><?php endif; ?>
</div>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:16px">
  <?php foreach ($rt_filas as $rt_f):
      $rt_c = $rt_colmap[$rt_f['semaforo']] ?? '#9ca3af';
  ?>
  <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border)">
    <span style="width:12px;height:12px;border-radius:50%;background:<?= $rt_c ?>;flex-shrink:0;margin-top:5px"></span>
    <div style="flex:1;min-width:0">
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font:700 14px var(--body);color:var(--text)"><?= e($rt_f['nombre']) ?></span>
        <span style="font:750 15px var(--num);color:<?= $rt_c ?>"><?= (int)$rt_f['score'] ?><span style="font-size:10px;color:var(--t3)">/100</span></span>
        <?php if (!empty($rt_f['gaming'])): ?><span style="font:700 10px var(--body);color:#b91c1c;background:#fdeaea;padding:2px 7px;border-radius:999px">🚩 gaming</span><?php endif; ?>
      </div>
      <div style="font:500 12px var(--num);color:var(--t3);margin-top:3px">
        Cierra <b style="color:var(--text)"><?= (int)$rt_f['cierra'] ?></b> ·
        Limpio <b style="color:var(--text)"><?= (int)$rt_f['limpio'] ?></b>
        <span style="color:var(--t3)">· <?= (int)$rt_f['ventas'] ?> ventas / <?= (int)$rt_f['descartes'] ?> descartes</span>
        <?php if (!empty($rt_f['venc_sube'])): ?> · <span style="color:#d97706;font-weight:700">⏰ vencidas ▲</span><?php endif; ?>
        <?php if (!empty($rt_f['citas_baja'])): ?> · <span style="color:#d97706;font-weight:700">📅 citas ↓</span><?php endif; ?>
      </div>
      <div style="font:500 12px var(--body);color:<?= $rt_f['semaforo'] === 'verde' ? 'var(--t3)' : $rt_c ?>;margin-top:3px"><?= e($rt_f['motivo']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>Rendimiento</strong> = <b>Cierra</b> (vs el histórico de tu empresa) + <b>Limpio</b> (descartes vs ventas). Las alertas <b>⏰ vencidas</b> y <b>📅 citas</b> avisan cuándo un asesor empieza a soltarse — antes de que caiga la venta. Todo relativo a su empresa / su propio ritmo.
  </div>
</div>
