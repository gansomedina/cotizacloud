<?php
// ============================================================
//  SuperAdmin — Detalle de empresa
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

$empresa_id = (int)($id ?? 0);
$emp = DB::row("SELECT * FROM empresas WHERE id = ?", [$empresa_id]);
if (!$emp) { http_response_code(404); die('Empresa no encontrada'); }

// ── Usuarios ──────────────────────────────────────────────
$usuarios = DB::query(
    "SELECT id, nombre, email, usuario, rol, activo, ultimo_login, created_at
     FROM usuarios WHERE empresa_id = ? ORDER BY rol ASC, nombre ASC",
    [$empresa_id]
);

// ── Métricas ──────────────────────────────────────────────
$num_cots = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id = ?", [$empresa_id]);
$num_ventas = (int)DB::val("SELECT COUNT(*) FROM ventas WHERE empresa_id = ?", [$empresa_id]);
$num_clientes = (int)DB::val("SELECT COUNT(*) FROM clientes WHERE empresa_id = ?", [$empresa_id]);
$num_articulos = (int)DB::val("SELECT COUNT(*) FROM articulos WHERE empresa_id = ?", [$empresa_id]);

// Cotizaciones por estado
$cots_estado = DB::query(
    "SELECT estado, COUNT(*) AS n FROM cotizaciones WHERE empresa_id = ? GROUP BY estado",
    [$empresa_id]
);
$estados = [];
foreach ($cots_estado as $ce) $estados[$ce['estado']] = (int)$ce['n'];

// Últimas 10 cotizaciones
$ultimas_cots = DB::query(
    "SELECT c.id, c.slug, c.numero, c.estado, c.total, c.created_at,
            cl.nombre AS cliente_nombre
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.empresa_id = ?
     ORDER BY c.created_at DESC LIMIT 10",
    [$empresa_id]
);

// Radar config
$radar_config = json_decode($emp['radar_config'] ?? '{}', true) ?: [];
$radar_modo = $radar_config['modo'] ?? 'medio';

function sa_hace(int $ts): string {
    $d = time() - $ts;
    if ($d <= 0) return 'ahora';
    if ($d < 60) return $d . 's';
    if ($d < 3600) return floor($d / 60) . 'm';
    if ($d < 86400) return floor($d / 3600) . 'h';
    if ($d < 86400 * 30) return floor($d / 86400) . 'd';
    return date('d/m/Y', $ts);
}

function sa_money(float $n): string {
    if ($n >= 1000000) return '$' . number_format($n / 1000000, 1) . 'M';
    if ($n >= 1000) return '$' . number_format($n / 1000, 0) . 'K';
    return '$' . number_format($n, 0);
}

$badge_estado = [
    'borrador' => 'badge-slate',
    'enviada'  => 'badge-blue',
    'vista'    => 'badge-amber',
    'aceptada' => 'badge-green',
    'rechazada'=> 'badge-red',
    'expirada' => 'badge-red',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($emp['nombre']) ?> — Super Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
<style>
:root {
    --bg:#f4f4f0; --white:#fff; --border:#e2e2dc; --border2:#c8c8c0;
    --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
    --g:#1a5c38; --g-bg:#eef7f2; --g-border:#b8ddc8;
    --amb:#92400e; --amb-bg:#fef3c7;
    --blue:#1d4ed8; --blue-bg:#dbeafe;
    --danger:#c53030; --danger-bg:#fff5f5;
    --purple:#6d28d9; --purple-bg:#ede9fe;
    --slate:#475569; --slate-bg:#f1f5f9;
    --r:12px; --r-sm:9px;
    --sh:0 1px 3px rgba(0,0,0,.06);
    --body:'Plus Jakarta Sans',sans-serif;
    --num:'DM Sans',sans-serif;
}
*,*::before,*::after{box-sizing:border-box}
body{font-family:var(--body);background:var(--bg);color:var(--text);margin:0;font-size:14px;line-height:1.5}

.sa-wrap{max-width:1100px;margin:0 auto;padding:20px 24px 60px}

.back{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--t3);text-decoration:none;margin-bottom:16px;transition:color .12s}
.back:hover{color:var(--g)}

.emp-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.emp-header h1{font-size:22px;font-weight:800;margin:0}
.emp-header .slug{font-size:13px;color:var(--t3);font-family:var(--num)}

.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:600}
.badge-green{background:var(--g-bg);color:var(--g)}
.badge-red{background:var(--danger-bg);color:var(--danger)}
.badge-amber{background:var(--amb-bg);color:var(--amb)}
.badge-blue{background:var(--blue-bg);color:var(--blue)}
.badge-slate{background:var(--slate-bg);color:var(--slate)}

.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;box-shadow:var(--sh)}
.card-label{font-size:11.5px;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.3px}
.card-val{font:800 24px var(--num);color:var(--text);margin-top:4px}

.section{margin-bottom:28px}
.section h2{font-size:15px;font-weight:700;margin:0 0 12px;color:var(--text)}

.tbl-wrap{background:var(--white);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{text-align:left;font-size:11px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px;padding:10px 14px;border-bottom:2px solid var(--border);white-space:nowrap}
td{padding:10px 14px;font-size:13px;color:var(--text);border-bottom:1px solid var(--border)}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafaf8}
.num{font-family:var(--num)}
.ago{color:var(--t3);font-size:12px}

.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:12px;margin-bottom:24px}
.info-item{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;box-shadow:var(--sh)}
.info-item label{font-size:11px;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.3px;display:block;margin-bottom:4px}
.info-item span{font-size:13.5px;font-weight:500;color:var(--text)}

.btn-enter{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:var(--r-sm);font:600 13px var(--body);cursor:pointer;border:1.5px solid var(--g-border);background:var(--g-bg);color:var(--g);text-decoration:none;transition:all .12s}
.btn-enter:hover{background:var(--g);color:#fff}

@media(max-width:768px){
    .sa-wrap{padding:14px}
    .cards{grid-template-columns:repeat(2,1fr)}
}
</style>
</head>
<body>

<div class="sa-wrap">

<a href="/superadmin" class="back"><i data-feather="arrow-left" style="width:14px;height:14px"></i> Todas las empresas</a>

<div class="emp-header">
    <div>
        <h1><?= e($emp['nombre']) ?></h1>
        <div class="slug"><?= e($emp['slug']) ?>.cotiza.cloud</div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <span class="badge <?= $emp['activa'] ? 'badge-green' : 'badge-red' ?>">
            <?= $emp['activa'] ? 'Activa' : 'Suspendida' ?>
        </span>
        <form method="post" action="/superadmin/impersonar" style="margin:0">
            <input type="hidden" name="empresa_id" value="<?= $emp['id'] ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn-enter"><i data-feather="log-in" style="width:14px;height:14px"></i> Entrar como admin</button>
        </form>
        <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/toggle" style="margin:0" onsubmit="return confirm('<?= $emp['activa'] ? '¿Suspender esta empresa? Los usuarios no podrán acceder.' : '¿Reactivar esta empresa?' ?>')">
            <?= csrf_field() ?>
            <?php if ($emp['activa']): ?>
                <button type="submit" class="btn-enter" style="border-color:#fca5a5;background:#fff5f5;color:#c53030"><i data-feather="pause-circle" style="width:14px;height:14px"></i> Suspender</button>
            <?php else: ?>
                <button type="submit" class="btn-enter"><i data-feather="play-circle" style="width:14px;height:14px"></i> Activar</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Info de empresa -->
<div class="info-grid">
    <div class="info-item">
        <label>Moneda</label>
        <span><?= e($emp['moneda'] ?? 'MXN') ?></span>
    </div>
    <div class="info-item">
        <label>Impuesto</label>
        <span><?= e($emp['impuesto_modo'] ?? 'ninguno') ?> <?= $emp['impuesto_pct'] ? '(' . $emp['impuesto_pct'] . '% ' . e($emp['impuesto_nombre'] ?? 'IVA') . ')' : '' ?></span>
    </div>
    <div class="info-item">
        <label>Registrada</label>
        <span><?= $emp['created_at'] ? date('d/m/Y H:i', strtotime($emp['created_at'])) : '—' ?></span>
    </div>
    <div class="info-item">
        <label>Radar modo</label>
        <span class="badge badge-<?= match($radar_modo) { 'agresivo'=>'amber', 'ligero'=>'blue', default=>'green' } ?>"><?= e($radar_modo) ?></span>
    </div>
    <?php if (!empty($emp['email'])): ?>
    <div class="info-item">
        <label>Email</label>
        <span><?= e($emp['email']) ?></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($emp['telefono'])): ?>
    <div class="info-item">
        <label>Teléfono</label>
        <span><?= e($emp['telefono']) ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="cards">
    <div class="card">
        <div class="card-label">Usuarios</div>
        <div class="card-val"><?= count($usuarios) ?></div>
    </div>
    <div class="card">
        <div class="card-label">Cotizaciones</div>
        <div class="card-val num"><?= $num_cots ?></div>
    </div>
    <div class="card">
        <div class="card-label">Ventas</div>
        <div class="card-val num"><?= $num_ventas ?></div>
    </div>
    <div class="card">
        <div class="card-label">Clientes</div>
        <div class="card-val num"><?= $num_clientes ?></div>
    </div>
    <div class="card">
        <div class="card-label">Artículos</div>
        <div class="card-val num"><?= $num_articulos ?></div>
    </div>
</div>

<!-- Cotizaciones por estado -->
<?php if ($estados): ?>
<div class="section">
    <h2>Cotizaciones por estado</h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <?php foreach ($estados as $est => $n): ?>
            <span class="badge <?= $badge_estado[$est] ?? 'badge-slate' ?>" style="font-size:12px;padding:5px 12px">
                <?= e(ucfirst($est)) ?>: <?= $n ?>
            </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Usuarios -->
<div class="section">
    <h2>Usuarios</h2>
    <div class="tbl-wrap">
    <table>
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Rol</th>
        <th>Estado</th>
        <th>Último login</th>
        <th>Creado</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($usuarios as $u): ?>
    <tr>
        <td style="font-weight:600"><?= e($u['nombre']) ?></td>
        <td class="num" style="font-size:12.5px"><?= e($u['email']) ?></td>
        <td><span class="badge <?= $u['rol'] === 'admin' ? 'badge-amber' : 'badge-slate' ?>"><?= e($u['rol']) ?></span></td>
        <td><span class="badge <?= $u['activo'] ? 'badge-green' : 'badge-red' ?>"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
        <td><span class="ago"><?= $u['ultimo_login'] ? sa_hace(strtotime($u['ultimo_login'])) : 'nunca' ?></span></td>
        <td><span class="ago"><?= $u['created_at'] ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>

<!-- Últimas cotizaciones -->
<?php if ($ultimas_cots): ?>
<div class="section">
    <h2>Últimas 10 cotizaciones</h2>
    <div class="tbl-wrap">
    <table>
    <thead>
    <tr>
        <th>Folio</th>
        <th>Cliente</th>
        <th>Estado</th>
        <th>Total</th>
        <th>Creada</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($ultimas_cots as $c): ?>
    <tr>
        <td class="num" style="font-weight:600;font-size:12.5px"><?= e($c['numero'] ?? $c['slug']) ?></td>
        <td><?= e($c['cliente_nombre'] ?? '—') ?></td>
        <td><span class="badge <?= $badge_estado[$c['estado']] ?? 'badge-slate' ?>"><?= e(ucfirst($c['estado'])) ?></span></td>
        <td class="num"><?= sa_money((float)($c['total'] ?? 0)) ?></td>
        <td><span class="ago"><?= $c['created_at'] ? sa_hace(strtotime($c['created_at'])) : '—' ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

</div>

<script>feather.replace();</script>
</body>
</html>
