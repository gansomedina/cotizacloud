<?php
// ============================================================
//  Dashboard partial — Metas de la empresa (CotizaCloud AI)
//  SOLO ADMIN, con cifras. El asesor NUNCA ve esta tarjeta: lo suyo son
//  frases sin cifras (MetasEmpresa::frases) en su tip y en su reporte.
//  Solo lectura. Comparte scope con index.php — variables con prefijo mt_.
// ============================================================
defined('COTIZAAPP') or die;

if (!Auth::es_admin()) return;
if (!class_exists('MetasEmpresa')) require_once __DIR__ . '/../../core/MetasEmpresa.php';

$mt_s = MetasEmpresa::estado(EMPRESA_ID);
$mt_moneda_mal = ($mt_s['ventanas']['mes']['motivo'] ?? null) === 'moneda';
if ($mt_s['estado'] === 'sin_metas' && !$mt_moneda_mal) return;   // sin meta capturada (o sin plan): no hay tarjeta

$mt_mon = $mt_s['moneda'] ?? 'MXN';
$mt_m   = fn($mt_x) => format_money((float)$mt_x, $mt_mon);
$mt_n   = MetasEmpresa::nivel(EMPRESA_ID);
$mt_f   = MetasEmpresa::frases($mt_n);
$mt_col = [
    'sin_equilibrio' => '#dc2626', 'muy_baja' => '#dc2626', 'baja' => '#ea580c',
    'debajo' => '#d97706', 'cerca' => '#d97706', 'casi' => '#65a30d',
    'llego' => '#16a34a', 'casi_optima' => '#16a34a', 'sobrepasada' => '#2563eb',
];
$mt_ventanas = ['mes' => 'Este mes (' . $mt_s['mes_nombre'] . ')', 'd30' => 'Últimos 30 días'];
?>
<style>
.mt-card{padding:16px 18px;margin-bottom:16px}
.mt-hd{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px}
.mt-tl{display:flex;align-items:baseline;gap:6px;flex-wrap:wrap;min-width:0}
.mt-tt{font:800 15px var(--body);color:var(--text)}
.mt-cv{font:500 12px var(--body);color:var(--t2)}
.mt-cv b{font:800 13px var(--num);color:var(--text)}
.mt-ed{font:600 12px var(--body);color:var(--g);text-decoration:none;white-space:nowrap}
.mt-w{padding:10px 0;border-top:1px solid var(--border)}
.mt-hd + .mt-w{border-top:none}
.mt-wl{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;font:700 13px var(--body);color:var(--text)}
.mt-v{font:800 15px var(--num)}
.mt-bar{position:relative;height:10px;border-radius:5px;background:var(--bg);margin:8px 0 18px}
.mt-fill{position:absolute;left:0;top:0;bottom:0;border-radius:5px}
.mt-mk{position:absolute;top:-3px;bottom:-3px;width:2px;background:var(--t2)}
.mt-mk span{position:absolute;top:15px;left:50%;transform:translateX(-50%);font:600 10px var(--body);color:var(--t3);white-space:nowrap}
.mt-mk .c{display:none}
@media (max-width:560px){ .mt-mk .l{display:none} .mt-mk .c{display:inline} }
.mt-fr{font:500 13px var(--body);line-height:1.5}
.mt-sub{font:400 12px var(--body);color:var(--t3);line-height:1.5;margin-top:4px}
.mt-al{margin:6px 0 2px;padding:6px 10px;border-radius:var(--r-sm);background:#fff7ed;color:#9a3412;font:600 12px var(--body)}
</style>
<div class="card mt-card">
  <?php $mt_cv = $mt_s['conv']; ?>
  <div class="mt-hd">
    <div class="mt-tl">
      <span class="mt-tt">Metas de la empresa</span>
      <?php if ($mt_cv['deseada'] !== null):   // el cierre va junto al título (CEO) ?>
      <span class="mt-cv">
        · Cierre <b><?= ($mt_cv['tasa'] !== null && $mt_cv['nivel'] !== 'gris') ? round($mt_cv['tasa'] * 100) . '%' : '—' ?></b>
        · buscas <?= rtrim(rtrim(number_format($mt_cv['deseada'] * 100, 2, '.', ''), '0'), '.') ?>%
        <?php if (!empty($mt_f['conv']) && $mt_s['estado'] === 'ok'): ?>· <?= e($mt_f['conv']) ?>
        <?php elseif ($mt_cv['nivel'] === 'gris'): ?>· todavía no hay suficientes cotizaciones para comparar<?php endif; ?>
      </span>
      <?php endif; ?>
    </div>
    <a class="mt-ed" href="/config?tab=metas">Editar metas</a>
  </div>

  <?php if ($mt_moneda_mal): ?>
  <div class="mt-sub">Tu meta está capturada en otra moneda y la empresa ahora opera en <?= e($mt_mon) ?>: <a class="mt-ed" href="/config?tab=metas">recaptúrala</a>.</div>
  <?php endif; ?>

  <?php if ($mt_s['estado'] === 'sin_historia'): ?>
  <div class="mt-sub">Todavía no hay suficiente historia para leer cómo va la empresa: hacen falta 30 días desde la primera venta con anticipo.</div>
  <?php endif; ?>

  <?php foreach ($mt_ventanas as $mt_w => $mt_lbl):
      $mt_v = $mt_s['ventanas'][$mt_w];
      if (!in_array($mt_v['estado'], ['ok', 'sin_historia'], true)) continue;
      $mt_top  = max((float)$mt_v['optimista'], (float)$mt_v['vendido']) * 1.08;
      $mt_pct  = fn($mt_x) => $mt_top > 0 ? min(100, round((float)$mt_x / $mt_top * 100, 2)) : 0;
      $mt_nv   = $mt_v['nivel'] ?? $mt_v['nivel_crudo'];
      $mt_c    = $mt_col[$mt_nv] ?? '#9aa1aa';
  ?>
  <div class="mt-w">
    <div class="mt-wl"><span><?= e($mt_lbl) ?></span><span class="mt-v" style="color:<?= $mt_c ?>"><?= e($mt_m($mt_v['vendido'])) ?></span></div>
    <div class="mt-bar">
      <div class="mt-fill" style="width:<?= $mt_pct($mt_v['vendido']) ?>%;background:<?= $mt_c ?>"></div>
      <?php foreach (['equilibrio' => ['Equilibrio', 'Eq.'], 'pesimista' => ['Pesimista', 'Pes.'], 'optimista' => ['Optimista', 'Opt.']] as $mt_k => [$mt_l, $mt_lc]): ?>
      <div class="mt-mk" style="left:<?= $mt_pct($mt_v[$mt_k]) ?>%" title="<?= e($mt_l . ' ' . $mt_m($mt_v[$mt_k])) ?>"><span class="l"><?= e($mt_l) ?></span><span class="c"><?= e($mt_lc) ?></span></div>
      <?php endforeach; ?>
    </div>
    <?php if ($mt_v['estado'] === 'ok' && !empty($mt_f[$mt_w])): ?>
    <div class="mt-fr" style="color:<?= $mt_c ?>"><?= e($mt_f[$mt_w]) ?></div>
    <?php endif; ?>
    <div class="mt-sub">
      <?= (int)$mt_v['n'] ?> venta<?= (int)$mt_v['n'] === 1 ? '' : 's' ?> ·
      equilibrio <?= e($mt_m($mt_v['equilibrio'])) ?> · pesimista <?= e($mt_m($mt_v['pesimista'])) ?> · optimista <?= e($mt_m($mt_v['optimista'])) ?>
      <?php if (!empty($mt_v['faltante_hacia'])): ?>
      · faltan <b><?= e($mt_m($mt_v['faltante'])) ?></b> para la <?= $mt_v['faltante_hacia'] === 'equilibrio' ? 'meta del punto de equilibrio' : 'meta ' . e($mt_v['faltante_hacia']) ?>
      <?php endif; ?>
    </div>
    <?php if (!empty($mt_v['alerta']) && !empty($mt_v['nivel_anterior'])): ?>
    <div class="mt-al">Cambió desde la última lectura (<?= e(date('d/m H:i', strtotime($mt_v['cambiado_at']))) ?>).</div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <?php $mt_fc = $mt_s['faltan_cot']; $mt_vm = $mt_s['ventanas']['mes'];
  if (($mt_fc['real'] || $mt_fc['deseada']) && !empty($mt_vm['faltante_hacia'])):
      $mt_obj = $mt_vm['faltante_hacia'] === 'equilibrio' ? 'el punto de equilibrio' : 'la meta ' . $mt_vm['faltante_hacia'];
      $mt_partes = [];
      if ($mt_fc['real'])    $mt_partes[] = 'a como cierra hoy la empresa, hacen falta unas <b>' . (int)$mt_fc['real'] . '</b> cotizaciones más';
      if ($mt_fc['deseada']) $mt_partes[] = 'si cerrara a lo que buscas, harían falta <b>' . (int)$mt_fc['deseada'] . '</b>';
  ?>
  <div class="mt-sub" style="margin-top:8px">Para llegar a <?= e($mt_obj) ?> este mes, <?= implode('; ', $mt_partes) ?>.</div>
  <?php endif; ?>

</div>
