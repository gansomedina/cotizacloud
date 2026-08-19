<?php
// ============================================================
//  Supervisor — Score mensual dentro del Ejecutivo
//
//  Promedio REAL por mes de cada asesor, de score_diario. El score del
//  termómetro es una ventana móvil de 15 días que se sobrescribe: mirarlo hoy
//  no dice cómo estuvo el mes.
//
//  Va aquí, en el consolidado, porque cruza sucursales. Lo de CADA sucursal
//  (mesa, ritmo, ranking, tips) vive en /supervisor, con los archivos reales
//  del dashboard incluidos tal cual.
//
//  Fuente: ActividadScore::promedio_mensual, la MISMA que usa el reporte de la
//  empresa — no una copia del query.
// ============================================================
defined('COTIZAAPP') or die;
if (empty($modo_supervisor)) return;

// ── Datos: score mensual ────────────────────────────────────
$sv_sc = []; $sv_meses = [];
foreach (array_keys($empresas_cfg) as $sv_eid) {
    foreach (ActividadScore::promedio_mensual((int)$sv_eid, 6) as $sv_r) {
        $sv_meses[$sv_r['mes']] = true;
        $sv_sc[$sv_eid][(int)$sv_r['usuario_id']]['nombre'] = $sv_r['asesor'];
        $sv_sc[$sv_eid][(int)$sv_r['usuario_id']]['m'][$sv_r['mes']] = $sv_r;
    }
}
krsort($sv_meses);
$sv_cols = array_slice(array_keys($sv_meses), 0, 6);
$SV_MES = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];

$sv_scol = function (?float $v): string {
    if ($v === null) return 'var(--t3)';
    if ($v >= 86) return '#16a34a';
    if ($v >= 61) return '#22c55e';
    if ($v >= 31) return '#f59e0b';
    return '#ef4444';
};
?>

<!-- ══ SUPERVISIÓN: SCORE MENSUAL ════════════════════════════ -->
<div class="sec">
    <div class="sec-hdr">
        <div class="sec-title">Score mensual por asesor</div>
        <div class="sec-count">promedio real del mes</div>
    </div>

    <?php if (!$sv_cols): ?>
    <div class="tbl-card" style="padding:20px;text-align:center;color:var(--t3);font:400 13px 'Inter',sans-serif">
        Todavía no hay historial diario de scores. Se llena solo conforme se usa el sistema.
    </div>
    <?php else: ?>
    <?php foreach ($empresas_cfg as $sv_eid => $sv_cfg):
        $sv_ases = $sv_sc[$sv_eid] ?? [];
        if (!$sv_ases) continue;
        uasort($sv_ases, function ($a, $b) {
            $pa = array_sum(array_column($a['m'], 'score_prom')) / max(count($a['m']), 1);
            $pb = array_sum(array_column($b['m'], 'score_prom')) / max(count($b['m']), 1);
            return $pb <=> $pa;
        });
    ?>
    <div class="tbl-card" style="margin-bottom:12px">
    <table>
    <thead><tr>
        <th><span class="tag" style="background:<?= $sv_cfg['color'] ?>"><?= e($sv_cfg['short']) ?></span> <?= e($sv_cfg['nombre']) ?></th>
        <?php foreach ($sv_cols as $sv_m): [$sv_a, $sv_mm] = explode('-', $sv_m); ?>
        <th class="r"><?= $SV_MES[(int)$sv_mm] ?> <?= substr($sv_a, 2) ?></th>
        <?php endforeach; ?>
        <th class="r">Promedio</th>
    </tr></thead>
    <tbody>
    <?php foreach ($sv_ases as $sv_a2):
        $sv_v = array_map(fn($f) => (float)$f['score_prom'], $sv_a2['m']);
        $sv_p = $sv_v ? array_sum($sv_v) / count($sv_v) : null;
    ?>
    <tr>
        <td style="font-weight:600"><?= e($sv_a2['nombre']) ?></td>
        <?php foreach ($sv_cols as $sv_m):
            $sv_f2 = $sv_a2['m'][$sv_m] ?? null;
            $sv_val = $sv_f2 ? (float)$sv_f2['score_prom'] : null;
        ?>
        <td class="r mono">
            <?php if ($sv_val === null): ?><span style="color:var(--t3)">—</span>
            <?php else: ?>
                <span style="font-weight:800;color:<?= $sv_scol($sv_val) ?>"><?= number_format($sv_val, 1) ?></span>
                <div style="font-size:10px;color:var(--t3)"><?= (int)$sv_f2['score_min'] ?>–<?= (int)$sv_f2['score_max'] ?> · <?= (int)$sv_f2['dias'] ?>d</div>
            <?php endif; ?>
        </td>
        <?php endforeach; ?>
        <td class="r mono" style="font-weight:800;color:<?= $sv_scol($sv_p) ?>"><?= $sv_p === null ? '—' : number_format($sv_p, 1) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php endforeach; ?>
    <div style="font:400 11.5px 'Inter',sans-serif;color:var(--t3);line-height:1.55">
        El score del termómetro es una ventana móvil de 15 días que se sobrescribe; esto es el
        promedio de los puntos diarios, que sí es el mes. Bajo cada número, el rango
        (mínimo–máximo) y los días con registro: pocos días = promedio menos confiable.
    </div>
    <?php endif; ?>
</div>

