<?php
// ============================================================
//  Dashboard partial — Rendimiento del equipo (desde la Mesa)
//  SOLO ADMIN. Solo lectura (RitmoAsesor). 5 PILARES etiquetados,
//  cada uno con su mini-semáforo y TODOS sus parámetros:
//    Conversión · Descartadas · Citas · Seguimiento · Contacto.
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

// Un pilar: mini-semáforo (dot del color de SU estado) + etiqueta + texto.
$rt_pill = function (string $estado, string $label, string $txt) use ($rt_colmap): string {
    $dc = $rt_colmap[$estado] ?? '#9ca3af';
    $ok = str_starts_with($txt, '✓');
    $tc = $ok ? 'var(--t3)' : $dc;
    return '<div style="display:flex;align-items:baseline;gap:7px;margin-top:3px;font:500 12px var(--body)">'
         . '<span style="width:8px;height:8px;border-radius:50%;background:' . $dc . ';flex:none;align-self:center"></span>'
         . '<span style="color:var(--t2);font-weight:700;min-width:88px">' . $label . '</span>'
         . '<span style="color:' . $tc . '">' . e($txt) . '</span></div>';
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
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px">
        <span style="font:700 14px var(--body);color:var(--text)"><?= e($rt_f['nombre']) ?></span>
        <?php if (!empty($rt_f['flag'])): ?><span style="font:700 10px var(--body);color:#b91c1c;background:#fdeaea;padding:2px 7px;border-radius:999px">no sigue el proceso</span><?php endif; ?>
      </div>
      <?= $rt_pill($rt_f['conv_estado'],  'Conversión',  $rt_f['conv_txt']) ?>
      <?= $rt_pill($rt_f['desc_estado'],  'Descartadas', $rt_f['desc_txt']) ?>
      <?= $rt_pill($rt_f['citas_estado'], 'Citas',       $rt_f['citas_txt']) ?>
      <?= $rt_pill($rt_f['venc_estado'],  'Seguimiento', $rt_f['venc_txt']) ?>
      <?= $rt_pill($rt_f['cont_estado'],  'Contacto',    $rt_f['cont_txt']) ?>
      <div style="font:600 12.5px var(--body);color:<?= $rt_f['semaforo'] === 'verde' ? 'var(--t3)' : $rt_c ?>;margin-top:6px">→ <?= e($rt_f['motivo']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <div style="padding:9px 16px;font:400 11px var(--body);color:var(--t3);background:var(--bg);line-height:1.5">
    <strong>5 pilares</strong> (cada uno con su semáforo): <b>Conversión</b> (cerró vs cotizaciones) · <b>Descartadas</b> (vs cierres, sin cita, muy rápido) · <b>Citas</b> (su ritmo de agendar) · <b>Seguimiento</b> (el cronómetro de vencidas) · <b>Contacto</b> (a cuántos no le contestan). Medido contra las reglas de la Mesa — hechos, no acusaciones.
  </div>
</div>
