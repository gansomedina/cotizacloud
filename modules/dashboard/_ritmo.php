<?php
// ============================================================
//  Dashboard partial — Ritmo del equipo (Alarma de Ritmo Semanal)
//  SOLO ADMIN. Solo lectura (RitmoAsesor). Indicador ADELANTADO:
//  "¿quién bajó el ritmo esta semana?" — antes de que caiga la venta.
//  Diseño: docs/alarma_ritmo_diseno.md
// ============================================================
defined('COTIZAAPP') or die;

if (!Auth::es_admin()) return;

// La señal (toques) viene de la Mesa; sin Mesa activa no hay datos.
$ritmo_on = false;
try { $ritmo_on = (int) DB::val("SELECT mesa_activa FROM empresas WHERE id = ?", [EMPRESA_ID]) >= 1; }
catch (Throwable $e) { $ritmo_on = false; }
if (!$ritmo_on) return;

$ritmo_filas = RitmoAsesor::empresa(EMPRESA_ID);
$ritmo_utiles = array_filter($ritmo_filas, fn($f) => $f['semaforo'] !== 'sin_datos');
if (!$ritmo_utiles) return; // nadie con cartera → no mostrar

$ritmo_col = ['rojo' => '#dc2626', 'amarillo' => '#d97706', 'verde' => '#16a34a', 'sin_datos' => '#9ca3af'];
$ritmo_n_alerta = count(array_filter($ritmo_utiles, fn($f) => $f['semaforo'] === 'rojo' || $f['semaforo'] === 'amarillo'));
?>
<div class="slabel">Ritmo del equipo · últimos 7 días
  <?php if ($ritmo_n_alerta > 0): ?><span style="font:700 11px var(--body);color:#dc2626"><?= $ritmo_n_alerta ?> por atender</span><?php endif; ?>
</div>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:16px">
  <?php foreach ($ritmo_filas as $f):
      if ($f['semaforo'] === 'sin_datos') continue;
      $col = $ritmo_col[$f['semaforo']];
      $trend = $f['toques_prev'] > 0 || $f['toques_now'] > 0 ? $f['toques_prev'] . ' → ' . $f['toques_now'] : '—';
  ?>
  <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border)">
    <span style="width:12px;height:12px;border-radius:50%;background:<?= $col ?>;flex-shrink:0;margin-top:3px"></span>
    <div style="flex:1;min-width:0">
      <div style="font:700 14px var(--body);color:var(--text)"><?= e($f['nombre']) ?></div>
      <div style="font:500 12px var(--num);color:var(--t3);margin-top:2px">
        Toques/lead: <b style="color:var(--text)"><?= $f['ratio'] ?></b>
        · toques semana: <b style="color:<?= $col ?>"><?= $trend ?></b>
        <?php if ($f['huerfanos'] > 0): ?> · <span style="color:#dc2626">huérfanos: <b><?= $f['huerfanos'] ?></b></span><?php endif; ?>
        · nuevas: <b style="color:var(--text)"><?= $f['nuevas'] ?></b>
      </div>
      <div style="font:500 12px var(--body);color:<?= $f['semaforo'] === 'verde' ? 'var(--t3)' : $col ?>;margin-top:3px"><?= e($f['motivo']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>Ritmo</strong> = actividad de seguimiento de ESTA semana (indicador adelantado). En un ciclo de ~28 días, si un asesor baja el ritmo hoy, la venta cae en un mes — actúa antes, no después.
  </div>
</div>
