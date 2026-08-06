<?php
// ============================================================
//  Dashboard partial — Ritmo del equipo (Alarma de Ritmo Semanal)
//  SOLO ADMIN. Solo lectura (RitmoAsesor). Indicador ADELANTADO:
//  "¿quién está bajando el ritmo de seguimiento?" — antes de que
//  caiga la venta (ciclo ~28 días).
//  3 ejes, todos de la Mesa: vencidas subiendo · por vencer sin
//  tocar · descartes sin trabajar.
//  Diseño: docs/alarma_ritmo_diseno.md
//  NOTA: los includes comparten scope con index.php — TODAS las
//  variables locales van con prefijo rt_ para no pisar nada.
// ============================================================
defined('COTIZAAPP') or die;

if (!Auth::es_admin()) return;

// La señal (reloj + descartes) vive en la Mesa; sin Mesa activa no hay datos.
$rt_on = false;
try { $rt_on = (int) DB::val("SELECT mesa_activa FROM empresas WHERE id = ?", [EMPRESA_ID]) >= 1; }
catch (Throwable $rt_e) { $rt_on = false; }
if (!$rt_on) return;

$rt_filas = RitmoAsesor::empresa(EMPRESA_ID);
$rt_utiles = array_filter($rt_filas, fn($x) => $x['semaforo'] !== 'sin_datos');
if (!$rt_utiles) return; // nadie con cartera → no mostrar

$rt_colmap = ['rojo' => '#dc2626', 'amarillo' => '#d97706', 'verde' => '#16a34a', 'sin_datos' => '#9ca3af'];
$rt_n_alerta = count(array_filter($rt_utiles, fn($x) => $x['semaforo'] === 'rojo' || $x['semaforo'] === 'amarillo'));
?>
<div class="slabel">Ritmo de seguimiento · esta semana
  <?php if ($rt_n_alerta > 0): ?><span style="font:700 11px var(--body);color:#dc2626"><?= $rt_n_alerta ?> por atender</span><?php endif; ?>
</div>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:16px">
  <?php foreach ($rt_filas as $rt_f):
      if ($rt_f['semaforo'] === 'sin_datos') continue;
      $rt_c = $rt_colmap[$rt_f['semaforo']];
      // Flecha de tendencia de vencidas
      $rt_tr = $rt_f['venc_prev'] . ' → ' . $rt_f['venc_now'] . ($rt_f['venc_sube'] ? ' ▲' : '');
  ?>
  <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border)">
    <span style="width:12px;height:12px;border-radius:50%;background:<?= $rt_c ?>;flex-shrink:0;margin-top:3px"></span>
    <div style="flex:1;min-width:0">
      <div style="font:700 14px var(--body);color:var(--text)"><?= e($rt_f['nombre']) ?></div>
      <div style="font:500 12px var(--num);color:var(--t3);margin-top:2px">
        <?php if ($rt_f['vencidas'] > 0): ?><span style="color:var(--t3)">⏳ <b><?= $rt_f['vencidas'] ?></b> vencidas ahora</span> · <?php endif; ?>
        vencidas semana: <b style="color:<?= $rt_f['venc_sube'] ? '#dc2626' : 'var(--text)' ?>"><?= $rt_tr ?></b>
        <?php if ($rt_f['por_vencer'] > 0): ?> · <span style="color:#d97706">por vencer: <b><?= $rt_f['por_vencer'] ?></b></span><?php endif; ?>
        <?php if ($rt_f['descartes'] > 0): ?> · 🗑 descartó: <b style="color:var(--text)"><?= $rt_f['descartes'] ?></b><?php if ($rt_f['sin_trabajo'] > 0): ?> <span style="color:#b91c1c">(<?= $rt_f['sin_trabajo'] ?> sin trabajar)</span><?php endif; ?><?php endif; ?>
      </div>
      <div style="font:500 12px var(--body);color:<?= $rt_f['semaforo'] === 'verde' ? 'var(--t3)' : $rt_c ?>;margin-top:3px"><?= e($rt_f['motivo']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>Ritmo de seguimiento</strong> = lo que la Mesa ya mide (indicador adelantado): <b>vencidas subiendo</b> (dejó pudrir el seguimiento), <b>por vencer sin tocar</b> (vence hoy/mañana), y <b>descartes sin trabajar</b> (limpió la mesa en vez de dar seguimiento). En un ciclo de ~28 días, si baja el ritmo hoy la venta cae en un mes — actúa antes, no después.
  </div>
</div>
