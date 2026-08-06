<?php
// ============================================================
//  Dashboard partial — Rendimiento del equipo (desde la Mesa)
//  SOLO ADMIN. Solo lectura (RitmoAsesor). Semanal, ciclo real,
//  auto-ajustable. Conversión + Limpio (gaming) + ritmo.
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

$rt_colmap   = ['rojo' => '#dc2626', 'amarillo' => '#d97706', 'verde' => '#16a34a'];
$rt_n_alerta = count(array_filter($rt_filas, fn($x) => $x['semaforo'] !== 'verde'));
?>
<div class="slabel">Rendimiento del equipo · esta semana
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
        <?php if (!empty($rt_f['flag'])): ?><span style="font:700 10px var(--body);color:#b91c1c;background:#fdeaea;padding:2px 7px;border-radius:999px">🚩 revisar</span><?php endif; ?>
      </div>
      <div style="font:500 12px var(--num);color:var(--t3);margin-top:3px">
        Cerró <b style="color:var(--text)"><?= (int)$rt_f['cierres7'] ?></b> de <b style="color:var(--text)"><?= (int)$rt_f['trabajo7'] ?></b> que trabajó
        <?php if ($rt_f['desc7'] > 0): ?> · 🗑 <b style="color:var(--text)"><?= (int)$rt_f['desc7'] ?></b><?php $rt_det = []; if ($rt_f['sincita7'] > 0) $rt_det[] = (int)$rt_f['sincita7'].' sin cita'; if ($rt_f['rapido7'] > 0) $rt_det[] = (int)$rt_f['rapido7'].' rápido'; if ($rt_det): ?> <span style="color:#b91c1c">(<?= implode(', ', $rt_det) ?>)</span><?php endif; ?><?php endif; ?>
        <?php if ($rt_f['nocontesta7'] > 0): ?> · <span style="color:#b91c1c">no contactó <b><?= (int)$rt_f['nocontesta7'] ?></b></span><?php endif; ?>
        <?php if (!empty($rt_f['venc_sube'])): ?> · <span style="color:#d97706;font-weight:700">⏰ vencidas ▲</span><?php endif; ?>
        <?php if (!empty($rt_f['citas_baja'])): ?> · <span style="color:#d97706;font-weight:700">📅 citas ↓</span><?php endif; ?>
      </div>
      <div style="font:500 12px var(--body);color:<?= $rt_f['semaforo'] === 'verde' ? 'var(--t3)' : $rt_c ?>;margin-top:3px"><?= e($rt_f['motivo']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>Rendimiento</strong> = <b>cerró vs trabajó</b> + <b>sigue el proceso</b> (no descarta sin cita, no descarta muy rápido, logra contactar). <b>⏰ vencidas</b> y <b>📅 citas</b> avisan cuándo se suelta. Muestra HECHOS, no acusa — el porqué lo revisas tú. Todo sobre el ciclo real de tu empresa.
  </div>
</div>
