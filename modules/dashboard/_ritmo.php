<?php
// ============================================================
//  Dashboard partial — Rendimiento del equipo (desde la Mesa)
//  SOLO ADMIN. Solo lectura (RitmoAsesor). Tarjeta por PILARES:
//  Conversión · Proceso · Ritmo — los 3 siempre visibles y etiquetados.
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
// Un pilar "bien" (empieza con ✓) va en gris; uno con problema, en el color del asesor.
$rt_pill = function (string $label, string $txt, string $col): string {
    $ok = str_starts_with($txt, '✓');
    $c  = $ok ? 'var(--t3)' : $col;
    return '<div style="font:500 12px var(--body);color:var(--t3);margin-top:2px">'
         . '<b style="color:var(--t2);font-weight:700">' . $label . '</b> — '
         . '<span style="color:' . $c . '">' . e($txt) . '</span></div>';
};
?>
<div class="slabel">Rendimiento del equipo · esta semana
  <?php if ($rt_n_alerta > 0): ?><span style="font:700 11px var(--body);color:#dc2626"><?= $rt_n_alerta ?> por atender</span><?php endif; ?>
</div>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);margin-bottom:16px">
  <?php foreach ($rt_filas as $rt_f):
      $rt_c = $rt_colmap[$rt_f['semaforo']] ?? '#9ca3af';
  ?>
  <div style="display:flex;align-items:flex-start;gap:12px;padding:13px 16px;border-bottom:1px solid var(--border)">
    <span style="width:12px;height:12px;border-radius:50%;background:<?= $rt_c ?>;flex-shrink:0;margin-top:4px"></span>
    <div style="flex:1;min-width:0">
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font:700 14px var(--body);color:var(--text)"><?= e($rt_f['nombre']) ?></span>
        <?php if (!empty($rt_f['flag'])): ?><span style="font:700 10px var(--body);color:#b91c1c;background:#fdeaea;padding:2px 7px;border-radius:999px">no sigue el proceso</span><?php endif; ?>
      </div>
      <?= $rt_pill('Conversión', $rt_f['conv_txt'], $rt_c) ?>
      <?= $rt_pill('Proceso',    $rt_f['proc_txt'], $rt_c) ?>
      <?= $rt_pill('Ritmo',      $rt_f['ritmo_txt'], $rt_c) ?>
      <div style="font:600 12.5px var(--body);color:<?= $rt_f['semaforo'] === 'verde' ? 'var(--t3)' : $rt_c ?>;margin-top:6px">→ <?= e($rt_f['motivo']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>3 pilares:</strong> <b>Conversión</b> (cerró vs trabajó) · <b>Proceso</b> (no descarta muy rápido —antes del ciclo—, no descarta sin cita, logra contactar) · <b>Ritmo</b> (no se le vencen seguimientos, no bajan sus citas). Muestra HECHOS medidos contra las reglas de la Mesa — el porqué lo revisas tú.
  </div>
</div>
