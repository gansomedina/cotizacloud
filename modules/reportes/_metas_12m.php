<?php
// ============================================================
//  cotiza.cloud — modules/reportes/_metas_12m.php
//  Reportes › Financiero: metas de los últimos 12 meses (SOLO ADMIN).
//  Solo meses del sistema; los importados no entran (CEO, 28 sep 2026).
//  Espera: $empresa_id, $es_admin.
// ============================================================
defined('COTIZAAPP') or die;
if (empty($es_admin)) return;
if (!class_exists('MetasEmpresa')) require_once __DIR__ . '/../../core/MetasEmpresa.php';

$m12 = MetasEmpresa::historial_meses((int)$empresa_id, 12);
if (!$m12) return;

$m12_rp  = fn($x) => '$' . number_format((float)$x, 0, '.', ',');
$m12_ico = fn($ok) => $ok === true
    ? '<span class="m12-ok" title="Logrado">✓</span>'
    : ($ok === false ? '<span class="m12-no" title="No se logró">✗</span>' : '<span class="m12-cur">en curso</span>');
$m12_cerr = array_values(array_filter($m12['meses'], fn($x) => !$x['en_curso']));
$m12_cnt  = fn($k) => count(array_filter($m12_cerr, fn($x) => $x[$k] === true));
?>
<style>
.m12-ok{display:inline-block;width:22px;height:22px;border-radius:50%;background:#dcfce7;color:#15803d;font:800 13px/22px var(--body);text-align:center}
.m12-no{display:inline-block;width:22px;height:22px;border-radius:50%;background:#fee2e2;color:#b91c1c;font:800 13px/22px var(--body);text-align:center}
.m12-cur{font:600 11px var(--body);color:var(--t3)}
.m12 td,.m12 th{text-align:center}
.m12 td:first-child,.m12 th:first-child{text-align:left}
.m12 th small{display:block;font:500 10px var(--num);color:var(--t3);letter-spacing:0;text-transform:none}
</style>
<div class="sec-lbl" style="margin:28px 0 12px">Metas — últimos 12 meses</div>
<div class="stat-card">
  <div class="tbl-wrap">
    <table class="tbl m12" style="font-size:12px">
      <thead>
        <tr>
          <th>Mes</th>
          <th style="text-align:right">Vendido</th>
          <th>Punto de equilibrio<small><?= $m12_rp($m12['meta']['equilibrio']) ?></small></th>
          <th>Meta pesimista<small><?= $m12_rp($m12['meta']['pesimista']) ?></small></th>
          <th>Meta optimista<small><?= $m12_rp($m12['meta']['optimista']) ?></small></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($m12['meses'] as $m12_r): ?>
        <tr>
          <td style="font:600 12px var(--body)"><?= e(mb_strtoupper(mb_substr($m12_r['nombre'], 0, 1)) . mb_substr($m12_r['nombre'], 1)) ?><?= $m12_r['en_curso'] ? ' <span class="m12-cur">(en curso)</span>' : '' ?></td>
          <td style="text-align:right;font:600 12px var(--num)"><?= $m12_rp($m12_r['vendido']) ?></td>
          <td><?= $m12_ico($m12_r['equilibrio']) ?></td>
          <td><?= $m12_ico($m12_r['pesimista']) ?></td>
          <td><?= $m12_ico($m12_r['optimista']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($m12_cerr): $m12_n = count($m12_cerr); ?>
  <div style="font:600 12px var(--body);color:var(--t2);margin-top:10px">
    De <?= $m12_n ?> mes<?= $m12_n === 1 ? '' : 'es' ?> cerrado<?= $m12_n === 1 ? '' : 's' ?>:
    equilibrio <?= $m12_cnt('equilibrio') ?>/<?= $m12_n ?> · pesimista <?= $m12_cnt('pesimista') ?>/<?= $m12_n ?> · optimista <?= $m12_cnt('optimista') ?>/<?= $m12_n ?>
  </div>
  <?php endif; ?>
  <div style="font:400 11px var(--body);color:var(--t3);margin-top:6px">Solo ventas con anticipo y sin Descuento Inteligente, en la fecha en que el cliente aceptó. Todos los meses se comparan contra la meta actual.</div>
</div>
