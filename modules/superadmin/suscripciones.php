<?php
// ============================================================
//  SuperAdmin — Panel de suscripciones
//  GET /superadmin/suscripciones
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

$subs = DB::query(
    "SELECT s.*, e.nombre AS empresa_nombre, e.slug, e.plan AS plan_actual, e.plan_vence, e.grace_hasta,
            (SELECT COUNT(*) FROM pagos_suscripcion p WHERE p.suscripcion_id=s.id AND p.estado='approved') AS total_pagos,
            (SELECT SUM(p.monto_mxn) FROM pagos_suscripcion p WHERE p.suscripcion_id=s.id AND p.estado='approved') AS total_cobrado,
            (SELECT MAX(p.fecha_pago) FROM pagos_suscripcion p WHERE p.suscripcion_id=s.id AND p.estado='approved') AS ultimo_pago
     FROM suscripciones s
     JOIN empresas e ON e.id = s.empresa_id
     ORDER BY s.updated_at DESC"
);

$stats = [
    'activas' => 0,
    'canceladas' => 0,
    'mrr' => 0.0,
    'total_cobrado' => 0.0,
];
foreach ($subs as $s) {
    if ($s['estado'] === 'active' && !$s['cancel_al_vencer']) {
        $stats['activas']++;
        $stats['mrr'] += $s['ciclo'] === 'anual' ? $s['monto_mxn'] / 12 : $s['monto_mxn'];
    } else {
        $stats['canceladas']++;
    }
    $stats['total_cobrado'] += (float)($s['total_cobrado'] ?? 0);
}

$page_title = 'Suscripciones';
ob_start();
?>
<style>
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px}
.stat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 20px;box-shadow:var(--sh)}
.stat-val{font:700 24px var(--num);color:var(--text)}
.stat-lbl{font:500 11px var(--body);text-transform:uppercase;letter-spacing:.06em;color:var(--t3);margin-top:2px}
.sub-tbl{width:100%;border-collapse:collapse;font:400 13px var(--body)}
.sub-tbl th{padding:10px 14px;text-align:left;font:700 11px var(--body);text-transform:uppercase;letter-spacing:.06em;color:var(--t3);background:var(--bg);border-bottom:1px solid var(--border)}
.sub-tbl td{padding:10px 14px;border-bottom:1px solid var(--border)}
.sub-tbl tr:hover{background:var(--bg)}
</style>

<a href="/superadmin" style="font:500 13px var(--body);color:var(--g);text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:16px">
    <i data-feather="arrow-left" style="width:14px;height:14px"></i> Volver
</a>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-val"><?= $stats['activas'] ?></div>
        <div class="stat-lbl">Activas</div>
    </div>
    <div class="stat-card">
        <div class="stat-val">$<?= number_format($stats['mrr'], 0) ?></div>
        <div class="stat-lbl">MRR (MXN)</div>
    </div>
    <div class="stat-card">
        <div class="stat-val">$<?= number_format($stats['total_cobrado'], 0) ?></div>
        <div class="stat-lbl">Total cobrado</div>
    </div>
    <div class="stat-card">
        <div class="stat-val"><?= $stats['canceladas'] ?></div>
        <div class="stat-lbl">Canceladas / Pendientes</div>
    </div>
</div>

<?php if ($subs): ?>
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow-x:auto;box-shadow:var(--sh)">
<table class="sub-tbl">
<thead>
<tr>
    <th>Empresa</th>
    <th>Plan</th>
    <th>Ciclo</th>
    <th>Monto</th>
    <th>Estado</th>
    <th>Vence</th>
    <th>Pagos</th>
    <th>Último pago</th>
</tr>
</thead>
<tbody>
<?php foreach ($subs as $s):
    $estado_color = match($s['estado']) {
        'active' => $s['cancel_al_vencer'] ? 'var(--amb)' : 'var(--g)',
        'paused' => 'var(--amb)',
        'cancelled' => 'var(--danger)',
        default => 'var(--t3)',
    };
    $estado_text = match($s['estado']) {
        'active' => $s['cancel_al_vencer'] ? 'Cancelará' : 'Activa',
        'paused' => 'Pausada',
        'cancelled' => 'Cancelada',
        default => $s['estado'],
    };
?>
<tr>
    <td>
        <a href="/superadmin/empresa/<?= $s['empresa_id'] ?>" style="color:var(--g);font-weight:600;text-decoration:none"><?= e($s['empresa_nombre']) ?></a>
        <div style="font:400 11px var(--num);color:var(--t3)"><?= e($s['slug']) ?></div>
    </td>
    <td><span style="font-weight:600;color:<?= $s['plan'] === 'business' ? 'var(--blue)' : 'var(--g)' ?>"><?= ucfirst($s['plan']) ?></span></td>
    <td><?= ucfirst($s['ciclo']) ?></td>
    <td style="font:600 13px var(--num)">$<?= number_format($s['monto_mxn'], 2) ?></td>
    <td><span style="color:<?= $estado_color ?>;font-weight:600"><?= $estado_text ?></span>
        <?php if ($s['grace_hasta'] && $s['grace_hasta'] >= date('Y-m-d')): ?>
        <div style="font:500 10px var(--body);color:var(--danger)">Grace: <?= date('d/m', strtotime($s['grace_hasta'])) ?></div>
        <?php endif; ?>
    </td>
    <td style="font:500 13px var(--num)"><?= $s['plan_vence'] ? date('d/m/Y', strtotime($s['plan_vence'])) : '—' ?></td>
    <td style="font:500 13px var(--num)"><?= (int)$s['total_pagos'] ?> ($<?= number_format((float)($s['total_cobrado'] ?? 0), 0) ?>)</td>
    <td style="font:400 12px var(--num);color:var(--t3)"><?= $s['ultimo_pago'] ? date('d/m/Y', strtotime($s['ultimo_pago'])) : '—' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div style="text-align:center;padding:40px;color:var(--t3);font:500 14px var(--body)">
    No hay suscripciones registradas aún.
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
