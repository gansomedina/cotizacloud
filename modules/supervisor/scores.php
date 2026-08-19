<?php
// ============================================================
//  Supervisor — Promedio mensual del score por asesor
//
//  El score que se ve en el termómetro es una ventana móvil de 15 días que se
//  sobrescribe: mirarlo hoy NO dice cómo estuvo el mes. Esta pantalla lee
//  score_diario (un punto por asesor por día) y promedia — eso sí es el mes.
//
//  Usa ActividadScore::promedio_mensual, la MISMA función que alimenta el
//  reporte de la empresa. Copiar el query aquí habría garantizado que tarde o
//  temprano el supervisor y el dueño vieran números distintos del mismo asesor.
// ============================================================
defined('COTIZAAPP') or die;
supervisor_requerir();

$mis_empresas = supervisor_empresas();
$MESES = 6;

$ph = implode(',', array_fill(0, count($mis_empresas), '?'));
$sucursales = DB::query(
    "SELECT id, nombre FROM empresas WHERE id IN ($ph) ORDER BY nombre",
    $mis_empresas
);

// Se arma por sucursal: cada una tiene su propio equipo y su propio ranking.
$data = [];   // eid => [asesor_id => ['nombre'=>, 'meses'=>[mes=>fila]]]
$meses_vistos = [];
foreach ($sucursales as $s) {
    $eid = (int)$s['id'];
    foreach (ActividadScore::promedio_mensual($eid, $MESES) as $r) {
        $uid = (int)$r['usuario_id'];
        $mes = (string)$r['mes'];
        $meses_vistos[$mes] = true;
        $data[$eid][$uid]['nombre'] = $r['asesor'];
        $data[$eid][$uid]['meses'][$mes] = $r;
    }
}
// Columnas de mes: las que REALMENTE tienen datos, más recientes primero.
krsort($meses_vistos);
$cols = array_slice(array_keys($meses_vistos), 0, $MESES);

$MES_ES = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',
           7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
$mes_lbl = function (string $ym) use ($MES_ES): string {
    [$a, $m] = array_pad(explode('-', $ym), 2, '');
    return ($MES_ES[(int)$m] ?? $m) . ' ' . substr($a, 2);
};
// Mismos cortes que el nivel del termómetro (ActividadScore: 86 / 61 / 31).
$color = function (?float $v): string {
    if ($v === null)  return 'var(--t3)';
    if ($v >= 86)     return '#15803d';
    if ($v >= 61)     return '#22c55e';
    if ($v >= 31)     return '#b45309';
    return '#dc2626';
};

ob_start();
?>
<style>
.sc-wrap{max-width:1100px;margin:0 auto;padding:20px 16px 60px}
.sc-h1{font:800 22px 'DM Sans',sans-serif;color:var(--text);margin:0 0 4px}
.sc-sub{font:400 13px 'DM Sans',sans-serif;color:var(--t3);margin:0 0 20px;line-height:1.55}
.sc-suc{background:var(--white);border:1px solid var(--border);border-radius:12px;margin-bottom:18px;overflow:hidden}
.sc-suc-h{padding:12px 16px;border-bottom:1px solid var(--border);background:rgba(0,0,0,.015);
          font:800 15px 'DM Sans',sans-serif;color:var(--text)}
.sc-scroll{overflow-x:auto}
.sc-tbl{width:100%;border-collapse:collapse;min-width:520px}
.sc-tbl th{font:700 10.5px 'Inter',sans-serif;text-transform:uppercase;letter-spacing:.04em;color:var(--t3);
           padding:9px 14px;border-bottom:1px solid var(--border);white-space:nowrap;text-align:center}
.sc-tbl th.izq{text-align:left}
.sc-tbl td{font:400 13px 'DM Sans',sans-serif;padding:10px 14px;border-bottom:1px solid var(--border);
           text-align:center;white-space:nowrap}
.sc-tbl td.izq{text-align:left;font-weight:600;color:var(--text)}
.sc-tbl tr:last-child td{border-bottom:none}
.sc-val{font:800 15px 'Inter',sans-serif}
.sc-rango{font:400 10.5px 'Inter',sans-serif;color:var(--t3);display:block;margin-top:1px}
.sc-prom{background:rgba(0,0,0,.025)}
.sc-vac{padding:22px 16px;text-align:center;font:400 13px 'DM Sans',sans-serif;color:var(--t3)}
.sc-nota{font:400 12px 'DM Sans',sans-serif;color:var(--t3);line-height:1.6;margin-top:14px}
</style>

<div class="sc-wrap">
    <h1 class="sc-h1">Score mensual por asesor</h1>
    <p class="sc-sub">
        Promedio real del mes, no la foto de hoy: el score del termómetro es una ventana
        móvil de 15 días que se sobrescribe. Aquí se promedian los puntos diarios.
    </p>

    <?php if (!$cols): ?>
    <div class="sc-suc"><div class="sc-vac">
        Todavía no hay historial diario de scores. Se va llenando solo conforme se usa el sistema.
    </div></div>
    <?php endif; ?>

    <?php foreach ($sucursales as $s):
        $eid = (int)$s['id'];
        $ases = $data[$eid] ?? [];
        // Orden: mejor promedio general arriba.
        uasort($ases, function ($a, $b) {
            $pa = array_sum(array_column($a['meses'], 'score_prom')) / max(count($a['meses']), 1);
            $pb = array_sum(array_column($b['meses'], 'score_prom')) / max(count($b['meses']), 1);
            return $pb <=> $pa;
        });
    ?>
    <div class="sc-suc">
        <div class="sc-suc-h"><?= e($s['nombre']) ?></div>
        <?php if (!$ases): ?>
        <div class="sc-vac">Sin historial de scores en esta sucursal.</div>
        <?php else: ?>
        <div class="sc-scroll">
        <table class="sc-tbl">
        <thead><tr>
            <th class="izq">Asesor</th>
            <?php foreach ($cols as $m): ?><th><?= e($mes_lbl($m)) ?></th><?php endforeach; ?>
            <th class="sc-prom">Promedio</th>
        </tr></thead>
        <tbody>
        <?php foreach ($ases as $uid => $a):
            $vals = array_map(fn($f) => (float)$f['score_prom'], $a['meses']);
            $prom = $vals ? array_sum($vals) / count($vals) : null;
        ?>
        <tr>
            <td class="izq"><?= e($a['nombre']) ?></td>
            <?php foreach ($cols as $m):
                $f = $a['meses'][$m] ?? null;
                $v = $f ? (float)$f['score_prom'] : null;
            ?>
            <td>
                <?php if ($v === null): ?>
                    <span style="color:var(--t3)">—</span>
                <?php else: ?>
                    <span class="sc-val" style="color:<?= $color($v) ?>"><?= number_format($v, 1) ?></span>
                    <span class="sc-rango"><?= (int)$f['score_min'] ?>–<?= (int)$f['score_max'] ?> · <?= (int)$f['dias'] ?>d</span>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
            <td class="sc-prom">
                <span class="sc-val" style="color:<?= $color($prom) ?>">
                    <?= $prom === null ? '—' : number_format($prom, 1) ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="sc-nota">
        Bajo cada promedio, el rango del mes (mínimo–máximo) y los días con registro.
        Pocos días = promedio menos confiable: el punto diario se captura cuando alguien
        de la sucursal entra al sistema, así que un mes con poca actividad tiene pocos puntos.
        Colores: rojo bajo 31 · ámbar 31-60 · verde 61-85 · verde fuerte 86+, los mismos
        cortes de nivel del termómetro.
    </div>
</div>
<?php
$content = ob_get_clean();
$titulo  = 'Score mensual';
require ROOT_PATH . '/core/layout.php';
