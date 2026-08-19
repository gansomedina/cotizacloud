<?php
// ============================================================
//  Supervisor — Mesas de trabajo por sucursal
//
//  Una pestaña por sucursal; dentro, los asesores uno bajo otro.
//
//  TODO EN SOLO LECTURA, y no por prudencia sino por corrección:
//  Mesa::armar() sin ese flag ESCRIBE en mesa_vencidos (Mesa.php:687), que es
//  lo que alimenta el castigo de Seguimiento del asesor. Si el supervisor
//  abriera la mesa en modo normal, el simple hecho de mirarla le movería el
//  score a la persona que está supervisando.
//
//  Por eso tampoco se usa Mesa::resumen(): es un atajo que llama a armar()
//  SIN solo_lectura (Mesa.php:709).
// ============================================================
defined('COTIZAAPP') or die;
supervisor_requerir();

$mis_empresas = supervisor_empresas();

// Nombre y datos de cada sucursal. El WHERE IN se arma con los IDs ya
// validados por supervisor_empresas(), nunca con nada que venga del request.
$ph = implode(',', array_fill(0, count($mis_empresas), '?'));
$sucursales = DB::query(
    "SELECT id, nombre, slug FROM empresas WHERE id IN ($ph) ORDER BY nombre",
    $mis_empresas
);

// Sucursal activa: se valida contra la lista, así que ?emp=7 de una empresa
// ajena cae al default en vez de mostrar datos que no le tocan.
$emp_sel = (int)($_GET['emp'] ?? 0);
if (!in_array($emp_sel, $mis_empresas, true)) {
    $emp_sel = (int)($sucursales[0]['id'] ?? 0);
}

// Asesores de la sucursal activa. Mismo criterio que el leaderboard
// (ActividadScore::equipo): solo quien tiene cotizaciones asignadas en el
// período — así no aparecen admins ni cuentas de sistema que no venden.
$asesores = [];
if ($emp_sel) {
    $asesores = DB::query(
        "SELECT u.id, u.nombre
           FROM usuarios u
          WHERE u.empresa_id = ? AND u.activo = 1
            AND COALESCE(u.rol,'') <> 'superadmin'
            AND EXISTS (
                SELECT 1 FROM cotizaciones c
                 WHERE COALESCE(c.vendedor_id, c.usuario_id) = u.id
                   AND c.empresa_id = ? AND c.total > 0
                   AND c.created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY))
          ORDER BY u.nombre",
        [$emp_sel, $emp_sel]
    );
}

// Las categorías REALES de Mesa::armar (Mesa.php:417-434), con la etiqueta que
// explica cada una. El default de abajo cubre cualquier categoría nueva sin
// romper la pantalla.
$CAT = [
    'revivida'         => ['Revivida',         '#dc2626'], // la habían descartado y el cliente volvió
    'milagro'          => ['Milagro',          '#ea580c'], // fuera de ciclo pero la está viendo AHORA
    'interes_muriendo' => ['Interés muriendo', '#ef4444'], // dijo que le interesa y el cliente se apaga
    'sin_postura'      => ['Sin trabajar',     '#b91c1c'], // nada capturado todavía
    'ultimo_tramo'     => ['Último tramo',     '#f59e0b'], // con interés, pero saliendo de ventana y fría
    'agendada'         => ['Cita agendada',    '#22c55e'], // el cliente pidió seguimiento para ~ahora
    'trabajo'          => ['En seguimiento',   '#3b82f6'], // capturada, a trabajarla
    'descartada_hoy'   => ['Descartada hoy',   '#64748b'], // visible solo hoy; mañana sale de la mesa
];

ob_start();
?>
<style>
.sv-wrap{max-width:1100px;margin:0 auto;padding:20px 16px 60px}
.sv-h1{font:800 22px 'DM Sans',sans-serif;color:var(--text);margin:0 0 4px}
.sv-sub{font:400 13px 'DM Sans',sans-serif;color:var(--t3);margin:0 0 18px}
.sv-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:10px}
.sv-tab{padding:9px 16px;border-radius:9px;font:700 13px 'DM Sans',sans-serif;text-decoration:none;
        color:var(--t2);background:transparent;border:1px solid transparent;white-space:nowrap}
.sv-tab:hover{background:rgba(0,0,0,.04)}
.sv-tab.on{background:var(--white);border-color:var(--border);color:var(--text);box-shadow:var(--sh)}
.sv-ases{background:var(--white);border:1px solid var(--border);border-radius:12px;margin-bottom:18px;overflow:hidden}
.sv-ases-h{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;
           padding:13px 16px;border-bottom:1px solid var(--border);background:rgba(0,0,0,.015)}
.sv-nom{font:800 15px 'DM Sans',sans-serif;color:var(--text)}
.sv-chips{display:flex;gap:6px;flex-wrap:wrap}
.sv-chip{font:700 11px 'Inter',sans-serif;padding:3px 9px;border-radius:20px;background:rgba(0,0,0,.05);color:var(--t2)}
.sv-chip.rojo{background:rgba(239,68,68,.12);color:#dc2626}
.sv-chip.amb{background:rgba(245,158,11,.14);color:#b45309}
.sv-chip.verde{background:rgba(34,197,94,.14);color:#15803d}
.sv-tbl{width:100%;border-collapse:collapse}
.sv-tbl th{font:700 10.5px 'Inter',sans-serif;text-transform:uppercase;letter-spacing:.04em;color:var(--t3);
           text-align:left;padding:8px 16px;border-bottom:1px solid var(--border);white-space:nowrap}
.sv-tbl td{font:400 13px 'DM Sans',sans-serif;color:var(--text);padding:9px 16px;border-bottom:1px solid var(--border);vertical-align:top}
.sv-tbl tr:last-child td{border-bottom:none}
.sv-cat{font:700 11px 'Inter',sans-serif;padding:2px 8px;border-radius:5px;color:#fff;white-space:nowrap}
.sv-vac{padding:22px 16px;text-align:center;font:400 13px 'DM Sans',sans-serif;color:var(--t3)}
.sv-nota{font:400 12px 'DM Sans',sans-serif;color:var(--t3);line-height:1.55;margin-top:14px}
@media(max-width:640px){.sv-tbl .op{display:none}}
</style>

<div class="sv-wrap">
    <h1 class="sv-h1">Mesas de trabajo</h1>
    <p class="sv-sub">Vista de supervisión, solo lectura. Abrir estas mesas no altera el score de los asesores.</p>

    <div class="sv-tabs">
        <?php foreach ($sucursales as $s): ?>
        <a class="sv-tab <?= (int)$s['id'] === $emp_sel ? 'on' : '' ?>"
           href="/supervisor/mesas?emp=<?= (int)$s['id'] ?>"><?= e($s['nombre']) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$asesores): ?>
    <div class="sv-ases"><div class="sv-vac">Esta sucursal no tiene asesores con cotizaciones recientes.</div></div>
    <?php endif; ?>

    <?php foreach ($asesores as $a):
        // solo_lectura = true. Ver Mesa.php:687 — sin esto se escribiría
        // mesa_vencidos y mirar castigaría al asesor.
        try { $m = Mesa::armar($emp_sel, (int)$a['id'], true); }
        catch (\Throwable $ex) { $m = ['rows' => [], 'resumen' => []]; }
        $r    = $m['resumen'] ?? [];
        $rows = $m['rows'] ?? [];
        $pend = (int)($r['n'] ?? 0);
        $venc = (int)($r['vencidas'] ?? 0);
        $hoy  = (int)($r['vence_hoy'] ?? 0);
        $sinp = (int)($r['sin_postura'] ?? 0);
    ?>
    <div class="sv-ases">
        <div class="sv-ases-h">
            <div class="sv-nom"><?= e($a['nombre']) ?></div>
            <div class="sv-chips">
                <span class="sv-chip"><?= $pend ?> pendientes</span>
                <?php if ($venc > 0): ?><span class="sv-chip rojo"><?= $venc ?> vencidas</span><?php endif; ?>
                <?php if ($hoy > 0):  ?><span class="sv-chip amb"><?= $hoy ?> vencen hoy</span><?php endif; ?>
                <?php if ($sinp > 0): ?><span class="sv-chip rojo"><?= $sinp ?> sin trabajar</span><?php endif; ?>
                <?php if ((int)($r['atendidas'] ?? 0) > 0): ?>
                <span class="sv-chip verde"><?= (int)$r['atendidas'] ?> atendidas hoy</span><?php endif; ?>
            </div>
        </div>

        <?php if (!$rows): ?>
        <div class="sv-vac">Mesa vacía.</div>
        <?php else: ?>
        <table class="sv-tbl">
        <thead><tr>
            <th>Cotización</th><th>Cliente</th><th>Estado</th>
            <th class="op">Seguimiento</th><th class="op">Días</th>
        </tr></thead>
        <tbody>
        <?php foreach ($rows as $row):
            [$cat_txt, $cat_col] = $CAT[$row['cat'] ?? ''] ?? [ucfirst(str_replace('_',' ', (string)($row['cat'] ?? '—'))), '#94a3b8'];
            $seg = $row['seguimiento'] ?? [];
        ?>
        <tr>
            <td>
                <strong>#<?= e((string)($row['numero'] ?? '')) ?></strong>
                <div style="font-size:12px;color:var(--t3)"><?= e(mb_substr((string)($row['titulo'] ?? ''), 0, 46)) ?></div>
            </td>
            <td><?= e((string)($row['cliente'] ?? '—')) ?></td>
            <td>
                <span class="sv-cat" style="background:<?= $cat_col ?>"><?= e($cat_txt) ?></span>
                <?php if (!empty($row['es_hot'])): ?>
                <div style="font-size:11px;color:#dc2626;margin-top:3px">🔥 señal caliente</div>
                <?php endif; ?>
                <?php if ((int)($row['intentos_nc'] ?? 0) > 0): ?>
                <div style="font-size:11px;color:var(--t3);margin-top:3px">Intento <?= (int)$row['intentos_nc'] ?> de 4</div>
                <?php endif; ?>
            </td>
            <td class="op" style="font-size:12px">
                <?php if (!empty($seg['vence'])): ?>
                    <?= e(date('d/m/Y', strtotime((string)$seg['vence']))) ?>
                    <?php $est = (string)($seg['estado'] ?? ''); ?>
                    <?php if ($est === 'vencida'): ?>
                    <span style="color:#dc2626;font-weight:700"> · vencido</span>
                    <?php elseif ($est === 'hoy'): ?>
                    <span style="color:#b45309;font-weight:700"> · hoy</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:var(--t3)">sin capturar</span>
                <?php endif; ?>
            </td>
            <td class="op" style="font-size:12px;color:var(--t3)"><?= (int)($row['edad'] ?? 0) ?>d</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="sv-nota">
        Es la misma cola que ve cada asesor en su panel. «Sin trabajar» son cotizaciones que
        todavía no tienen postura declarada; «vencidas», compromisos cuya fecha ya pasó.
    </div>
</div>
<?php
$content = ob_get_clean();
$titulo  = 'Mesas de trabajo';
require ROOT_PATH . '/core/layout.php';
