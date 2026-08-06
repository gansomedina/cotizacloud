<?php
// ============================================================
//  Dashboard partial — Ritmo del equipo (Alarma de Ritmo Semanal)
//  SOLO ADMIN. Solo lectura (RitmoAsesor). Indicador ADELANTADO:
//  "¿quién bajó el ritmo esta semana?" — antes de que caiga la venta.
//  Diseño: docs/alarma_ritmo_diseno.md
//  NOTA: los includes comparten scope con index.php — TODAS las
//  variables locales van con prefijo rt_ para no pisar nada.
// ============================================================
defined('COTIZAAPP') or die;

if (!Auth::es_admin()) return;

// La señal (toques) viene de la Mesa; sin Mesa activa no hay datos.
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
<div class="slabel">Ritmo del equipo · últimos 7 días
  <?php if ($rt_n_alerta > 0): ?><span style="font:700 11px var(--body);color:#dc2626"><?= $rt_n_alerta ?> por atender</span><?php endif; ?>
</div>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:16px">
  <?php foreach ($rt_filas as $rt_f):
      if ($rt_f['semaforo'] === 'sin_datos') continue;
      $rt_c = $rt_colmap[$rt_f['semaforo']];
      $rt_tr = $rt_f['toques_prev'] > 0 || $rt_f['toques_now'] > 0 ? $rt_f['toques_prev'] . ' → ' . $rt_f['toques_now'] : '—';
  ?>
  <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border)">
    <span style="width:12px;height:12px;border-radius:50%;background:<?= $rt_c ?>;flex-shrink:0;margin-top:3px"></span>
    <div style="flex:1;min-width:0">
      <div style="font:700 14px var(--body);color:var(--text)"><?= e($rt_f['nombre']) ?></div>
      <div style="font:500 12px var(--num);color:var(--t3);margin-top:2px">
        Toques/lead: <b style="color:var(--text)"><?= $rt_f['ratio'] ?></b>
        · toques semana: <b style="color:<?= $rt_c ?>"><?= $rt_tr ?></b>
        <?php if ($rt_f['huerfanos'] > 0): ?> · <span style="color:#dc2626">huérfanos: <b><?= $rt_f['huerfanos'] ?></b></span><?php endif; ?>
        · nuevas: <b style="color:var(--text)"><?= $rt_f['nuevas'] ?></b>
      </div>
      <div style="font:500 12px var(--body);color:<?= $rt_f['semaforo'] === 'verde' ? 'var(--t3)' : $rt_c ?>;margin-top:3px"><?= e($rt_f['motivo']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>Ritmo</strong> = actividad de seguimiento de ESTA semana (indicador adelantado). En un ciclo de ~28 días, si un asesor baja el ritmo hoy, la venta cae en un mes — actúa antes, no después.
  </div>
</div>
