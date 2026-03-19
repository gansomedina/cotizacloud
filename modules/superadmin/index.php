<?php
// ============================================================
//  SuperAdmin — Panel principal
//  Lista de empresas con métricas clave
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

// ── Métricas globales ──────────────────────────────────────
$total_empresas = (int)DB::val("SELECT COUNT(*) FROM empresas WHERE slug != '_system'");
$total_usuarios = (int)DB::val("SELECT COUNT(*) FROM usuarios WHERE rol != 'superadmin'");
$total_cots     = (int)DB::val("SELECT COUNT(*) FROM cotizaciones");
$total_ventas   = (int)DB::val("SELECT COUNT(*) FROM ventas");

$nuevas_hoy = (int)DB::val("SELECT COUNT(*) FROM empresas WHERE slug != '_system' AND DATE(created_at) = CURDATE()");
$nuevas_7d  = (int)DB::val("SELECT COUNT(*) FROM empresas WHERE slug != '_system' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");

// ── Empresas nuevas (últimos 30 días) ──────────────────────
$empresas_nuevas = DB::query("
    SELECT e.id, e.nombre, e.slug, e.activa, e.created_at,
        (SELECT COUNT(*) FROM usuarios u WHERE u.empresa_id = e.id) AS num_usuarios,
        (SELECT COUNT(*) FROM cotizaciones c WHERE c.empresa_id = e.id) AS num_cots
    FROM empresas e
    WHERE e.slug != '_system' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY e.created_at DESC
");

// ── Tickets de soporte ─────────────────────────────────────
DB::execute("CREATE TABLE IF NOT EXISTS tickets_soporte (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id    INT UNSIGNED NOT NULL,
    usuario_id    INT UNSIGNED NOT NULL,
    titulo        VARCHAR(255) NOT NULL,
    descripcion   TEXT NOT NULL,
    imagen_url    VARCHAR(500) DEFAULT NULL,
    estado        ENUM('abierto','en_proceso','cerrado') NOT NULL DEFAULT 'abierto',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_empresa (empresa_id),
    INDEX idx_estado  (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$tickets = DB::query("
    SELECT t.*, e.nombre AS empresa_nombre, e.slug AS empresa_slug,
           u.nombre AS usuario_nombre, u.email AS usuario_email
    FROM tickets_soporte t
    JOIN empresas e ON e.id = t.empresa_id
    JOIN usuarios u ON u.id = t.usuario_id
    ORDER BY FIELD(t.estado, 'abierto', 'en_proceso', 'cerrado'), t.created_at DESC
    LIMIT 50
");
$tickets_abiertos = (int)DB::val("SELECT COUNT(*) FROM tickets_soporte WHERE estado != 'cerrado'");

// ── Lista de empresas con stats ────────────────────────────
$empresas = DB::query("
    SELECT e.*,
        (SELECT COUNT(*) FROM usuarios u WHERE u.empresa_id = e.id) AS num_usuarios,
        (SELECT COUNT(*) FROM cotizaciones c WHERE c.empresa_id = e.id) AS num_cots,
        (SELECT COUNT(*) FROM cotizaciones c WHERE c.empresa_id = e.id AND c.estado = 'vista') AS num_vistas,
        (SELECT COUNT(*) FROM ventas v WHERE v.empresa_id = e.id) AS num_ventas,
        (SELECT MAX(u.ultimo_login) FROM usuarios u WHERE u.empresa_id = e.id) AS ultimo_login
    FROM empresas e
    WHERE e.slug != '_system'
    ORDER BY e.created_at DESC
");

$page_title = 'Super Admin';

// ── Layout propio (sin sidebar de empresa) ────────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin — CotizaCloud</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<script src="/assets/js/feather.min.js"></script>
<style>
:root {
    --bg:#f4f4f0; --white:#fff; --border:#e2e2dc; --border2:#c8c8c0;
    --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
    --g:#1a5c38; --g-bg:#eef7f2; --g-border:#b8ddc8;
    --amb:#92400e; --amb-bg:#fef3c7;
    --blue:#1d4ed8; --blue-bg:#dbeafe;
    --danger:#c53030; --danger-bg:#fff5f5;
    --purple:#6d28d9; --purple-bg:#ede9fe;
    --r:12px; --r-sm:9px;
    --sh:0 1px 3px rgba(0,0,0,.06);
    --body:'Plus Jakarta Sans',sans-serif;
    --num:'DM Sans',sans-serif;
}
*,*::before,*::after{box-sizing:border-box}
body{font-family:var(--body);background:var(--bg);color:var(--text);margin:0;font-size:14px;line-height:1.5}

.sa-wrap{max-width:1200px;margin:0 auto;padding:20px 24px 60px}

/* Header */
.sa-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px}
.sa-logo{display:flex;align-items:center;gap:10px}
.sa-logo-icon{width:40px;height:40px;border-radius:10px;background:var(--g);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px}
.sa-logo h1{font-size:20px;font-weight:800;color:var(--text);margin:0}
.sa-logo small{font-size:12px;color:var(--t3);font-weight:500}
.sa-actions{display:flex;gap:8px;align-items:center}
.sa-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--r-sm);font:600 13px var(--body);cursor:pointer;border:none;text-decoration:none;transition:opacity .12s}
.sa-btn:hover{opacity:.85}
.sa-btn-ghost{background:var(--bg);color:var(--t2);border:1.5px solid var(--border2)}
.sa-btn-danger{background:var(--danger);color:#fff}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:28px}
.stat{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;box-shadow:var(--sh)}
.stat-label{font-size:11.5px;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.3px}
.stat-val{font:800 26px var(--num);color:var(--text);margin-top:4px}
.stat-sub{font-size:11.5px;color:var(--t3);margin-top:2px}
.stat-highlight .stat-val{color:var(--g)}

/* Search */
.search-bar{margin-bottom:20px}
.search-bar input{width:100%;max-width:400px;padding:9px 14px 9px 36px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font:400 13.5px var(--body);color:var(--text);background:var(--white) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%236a6a64' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='7' cy='7' r='4.5'/%3E%3Cline x1='10.5' y1='10.5' x2='15' y2='15'/%3E%3C/svg%3E") 12px center no-repeat;outline:none;transition:border-color .12s}
.search-bar input:focus{border-color:var(--g)}

/* Table */
.sa-table-wrap{background:var(--white);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);overflow-x:auto}
.sa-table{width:100%;border-collapse:collapse;min-width:800px}
.sa-table th{text-align:left;font-size:11px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px;padding:12px 14px;border-bottom:2px solid var(--border);white-space:nowrap}
.sa-table td{padding:12px 14px;font-size:13.5px;color:var(--text);border-bottom:1px solid var(--border);vertical-align:middle}
.sa-table tr:last-child td{border-bottom:none}
.sa-table tr:hover td{background:#fafaf8}
.sa-table tr.new-today td{background:#f0fdf4}

.emp-name{font-weight:700;color:var(--text)}
.emp-slug{font-size:12px;color:var(--t3);font-family:var(--num)}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:600}
.badge-green{background:var(--g-bg);color:var(--g)}
.badge-red{background:var(--danger-bg);color:var(--danger)}
.badge-amber{background:var(--amb-bg);color:var(--amb)}
.badge-blue{background:var(--blue-bg);color:var(--blue)}
.num{font-family:var(--num)}

.btn-enter{display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:var(--r-sm);font:600 12px var(--body);cursor:pointer;border:1.5px solid var(--g-border);background:var(--g-bg);color:var(--g);text-decoration:none;transition:all .12s;white-space:nowrap}
.btn-enter:hover{background:var(--g);color:#fff}
.btn-detail{display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:var(--r-sm);font:600 12px var(--body);cursor:pointer;border:1.5px solid var(--border2);background:var(--white);color:var(--t2);text-decoration:none;transition:all .12s;white-space:nowrap}
.btn-detail:hover{border-color:var(--blue);color:var(--blue)}
.btn-suspend{display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:var(--r-sm);font:600 12px var(--body);cursor:pointer;border:1.5px solid #fca5a5;background:var(--danger-bg);color:var(--danger);text-decoration:none;transition:all .12s;white-space:nowrap}
.btn-suspend:hover{background:var(--danger);color:#fff}
.btn-activate{display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:var(--r-sm);font:600 12px var(--body);cursor:pointer;border:1.5px solid var(--g-border);background:var(--g-bg);color:var(--g);text-decoration:none;transition:all .12s;white-space:nowrap}
.btn-activate:hover{background:var(--g);color:#fff}

.actions-cell{display:flex;gap:6px;align-items:center}

/* Sections */
.sa-section{margin-bottom:28px}
.sa-section h2{font-size:15px;font-weight:700;margin:0 0 12px;display:flex;align-items:center;gap:8px}
.sa-section h2 .count{font:600 12px var(--num);background:var(--danger);color:#fff;border-radius:99px;padding:2px 8px}

/* Ticket badges */
.ticket-estado{display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:600}
.ticket-abierto{background:#fef3c7;color:#92400e}
.ticket-en_proceso{background:#dbeafe;color:#1d4ed8}
.ticket-cerrado{background:#f1f5f9;color:#475569}
.ticket-desc{font-size:12px;color:var(--t3);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

.ago{color:var(--t3);font-size:12px}

@media(max-width:768px){
    .sa-wrap{padding:14px}
    .stats{grid-template-columns:repeat(2,1fr)}
    .sa-header{flex-direction:column;align-items:flex-start}
}
</style>
</head>
<body>

<div class="sa-wrap">

<!-- Header -->
<div class="sa-header">
    <div class="sa-logo">
        <div class="sa-logo-icon"><i data-feather="shield" style="width:22px;height:22px"></i></div>
        <div>
            <h1>CotizaCloud Admin</h1>
            <small>Panel de administración del sistema</small>
        </div>
    </div>
    <div class="sa-actions">
        <a href="/logout" class="sa-btn sa-btn-ghost" style="color:var(--danger);border-color:#fca5a5"><i data-feather="log-out" style="width:14px;height:14px"></i> Salir</a>
    </div>
</div>

<!-- Stats -->
<div class="stats">
    <div class="stat <?= $nuevas_hoy > 0 ? 'stat-highlight' : '' ?>">
        <div class="stat-label">Nuevas hoy</div>
        <div class="stat-val"><?= $nuevas_hoy ?></div>
    </div>
    <div class="stat">
        <div class="stat-label">Nuevas 7d</div>
        <div class="stat-val"><?= $nuevas_7d ?></div>
    </div>
    <div class="stat">
        <div class="stat-label">Empresas</div>
        <div class="stat-val"><?= $total_empresas ?></div>
    </div>
    <div class="stat">
        <div class="stat-label">Usuarios</div>
        <div class="stat-val"><?= $total_usuarios ?></div>
    </div>
    <div class="stat">
        <div class="stat-label">Cotizaciones</div>
        <div class="stat-val num"><?= number_format($total_cots) ?></div>
    </div>
    <div class="stat">
        <div class="stat-label">Ventas</div>
        <div class="stat-val num"><?= number_format($total_ventas) ?></div>
    </div>
    <div class="stat <?= $tickets_abiertos > 0 ? 'stat-highlight' : '' ?>">
        <div class="stat-label">Tickets abiertos</div>
        <div class="stat-val"><?= $tickets_abiertos ?></div>
    </div>
</div>

<!-- Empresas nuevas (últimos 30 días) -->
<?php if ($empresas_nuevas): ?>
<div class="sa-section">
    <h2><i data-feather="star" style="width:16px;height:16px;color:var(--g)"></i> Empresas nuevas (30d)</h2>
    <div class="sa-table-wrap">
    <table class="sa-table" style="min-width:500px">
    <thead>
    <tr><th>Empresa</th><th>Estado</th><th>Usuarios</th><th>Cots</th><th>Registrada</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($empresas_nuevas as $en): ?>
    <tr>
        <td>
            <div class="emp-name"><?= e($en['nombre']) ?></div>
            <div class="emp-slug"><?= e($en['slug']) ?>.cotiza.cloud</div>
        </td>
        <td><span class="badge <?= $en['activa'] ? 'badge-green' : 'badge-red' ?>"><?= $en['activa'] ? 'Activa' : 'Suspendida' ?></span></td>
        <td class="num"><?= $en['num_usuarios'] ?></td>
        <td class="num"><?= $en['num_cots'] ?></td>
        <td><span class="ago"><?= $en['created_at'] ? date('d/m/Y', strtotime($en['created_at'])) : '—' ?></span></td>
        <td><a href="/superadmin/empresa/<?= $en['id'] ?>" class="btn-detail"><i data-feather="eye" style="width:12px;height:12px"></i> Ver</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<!-- Tickets de soporte -->
<div class="sa-section">
    <h2>
        <i data-feather="message-circle" style="width:16px;height:16px"></i> Tickets de soporte
        <?php if ($tickets_abiertos > 0): ?><span class="count"><?= $tickets_abiertos ?></span><?php endif; ?>
    </h2>
    <?php if ($tickets): ?>
    <div class="sa-table-wrap">
    <table class="sa-table" style="min-width:700px">
    <thead>
    <tr><th>Ticket</th><th>Empresa</th><th>Usuario / Email</th><th>Estado</th><th>Fecha</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($tickets as $tk): ?>
    <tr>
        <td>
            <div style="font-weight:600;font-size:13px"><?= e($tk['titulo']) ?></div>
            <div class="ticket-desc"><?= e(mb_substr($tk['descripcion'], 0, 80)) ?><?= mb_strlen($tk['descripcion']) > 80 ? '...' : '' ?></div>
        </td>
        <td>
            <div class="emp-name" style="font-size:12.5px"><?= e($tk['empresa_nombre']) ?></div>
            <div class="emp-slug"><?= e($tk['empresa_slug']) ?></div>
        </td>
        <td>
            <div style="font-size:12.5px;font-weight:500"><?= e($tk['usuario_nombre']) ?></div>
            <div style="font-size:12px;color:var(--blue)"><a href="mailto:<?= e($tk['usuario_email']) ?>" style="color:var(--blue);text-decoration:none"><?= e($tk['usuario_email']) ?></a></div>
        </td>
        <td><span class="ticket-estado ticket-<?= e($tk['estado']) ?>"><?= e(str_replace('_', ' ', ucfirst($tk['estado']))) ?></span></td>
        <td><span class="ago"><?= $tk['created_at'] ? date('d/m/Y H:i', strtotime($tk['created_at'])) : '—' ?></span></td>
        <td>
            <?php if ($tk['estado'] !== 'cerrado'): ?>
            <form method="post" action="/superadmin/ticket/<?= $tk['id'] ?>/estado" style="margin:0;display:flex;gap:4px">
                <?= csrf_field() ?>
                <?php if ($tk['estado'] === 'abierto'): ?>
                    <input type="hidden" name="estado" value="en_proceso">
                    <button type="submit" class="btn-detail" style="font-size:11px;padding:4px 8px">En proceso</button>
                <?php endif; ?>
                <?php if ($tk['estado'] === 'abierto'): ?>
                    <input type="hidden" name="estado" value="cerrado">
                <?php endif; ?>
                <button type="submit" class="btn-suspend" style="font-size:11px;padding:4px 8px" onclick="this.form.querySelector('[name=estado]').value='cerrado'">Cerrar</button>
            </form>
            <?php else: ?>
                <span class="ago">Cerrado</span>
            <?php endif; ?>
            <?php if ($tk['imagen_url']): ?>
                <a href="<?= e($tk['imagen_url']) ?>" target="_blank" style="font-size:11px;color:var(--blue);margin-left:4px">Ver imagen</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:24px;text-align:center;color:var(--t3);font-size:13px">
        Sin tickets de soporte pendientes.
    </div>
    <?php endif; ?>
</div>

<!-- Search -->
<div class="search-bar">
    <input type="text" id="search" placeholder="Buscar empresa por nombre o slug..." oninput="filtrar(this.value)">
</div>

<!-- Table -->
<div class="sa-table-wrap">
<table class="sa-table" id="empresas-table">
<thead>
<tr>
    <th>Empresa</th>
    <th>Plan</th>
    <th>Vence</th>
    <th>Estado</th>
    <th>Usuarios</th>
    <th>Cots</th>
    <th>Vistas</th>
    <th>Ventas</th>
    <th>Último login</th>
    <th>Registrada</th>
    <th></th>
</tr>
</thead>
<tbody>
<?php foreach ($empresas as $e):
    $is_today = (substr($e['created_at'] ?? '', 0, 10) === date('Y-m-d'));
    $login_ago = $e['ultimo_login'] ? hace_tiempo(strtotime($e['ultimo_login'])) : 'nunca';
    $created_ago = $e['created_at'] ? hace_tiempo(strtotime($e['created_at'])) : '—';
?>
<tr class="<?= $is_today ? 'new-today' : '' ?>" data-search="<?= e(strtolower($e['nombre'] . ' ' . $e['slug'])) ?>">
    <td>
        <div class="emp-name"><?= e($e['nombre']) ?></div>
        <div class="emp-slug"><?= e($e['slug']) ?>.cotiza.cloud</div>
    </td>
    <td>
        <?php $plan = $e['plan'] ?? 'trial'; ?>
        <?php if ($plan === 'trial' && (int)$e['num_cots'] >= TRIAL_LIMIT): ?>
            <span class="badge badge-red">TRIAL AGOTADO</span>
        <?php else: ?>
            <span class="badge <?= $plan === 'pro' ? 'badge-green' : 'badge-amber' ?>"><?= strtoupper($plan) ?></span>
        <?php endif; ?>
    </td>
    <td>
        <?php
        $pv = $e['plan_vence'] ?? null;
        if ($plan === 'pro' && $pv):
            $dias_r = (int)((strtotime($pv) - strtotime(date('Y-m-d'))) / 86400);
            if ($dias_r < 0): ?>
                <span class="badge badge-red"><?= date('d/m', strtotime($pv)) ?></span>
            <?php elseif ($dias_r <= 7): ?>
                <span class="badge badge-amber"><?= $dias_r ?>d</span>
            <?php else: ?>
                <span class="ago"><?= date('d/m/Y', strtotime($pv)) ?></span>
            <?php endif;
        elseif ($plan === 'pro'): ?>
            <span class="ago">—</span>
        <?php else: ?>
            <span class="ago">—</span>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($e['activa']): ?>
            <span class="badge badge-green">Activa</span>
        <?php else: ?>
            <span class="badge badge-red">Suspendida</span>
        <?php endif; ?>
    </td>
    <td class="num"><?= $e['num_usuarios'] ?></td>
    <td class="num"><?= $e['num_cots'] ?></td>
    <td class="num"><?= $e['num_vistas'] ?></td>
    <td class="num"><?= $e['num_ventas'] ?></td>
    <td><span class="ago"><?= e($login_ago) ?></span></td>
    <td>
        <span class="ago"><?= e($created_ago) ?></span>
        <?php if ($is_today): ?>
            <span class="badge badge-blue" style="margin-left:4px">Nueva</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="actions-cell">
            <a href="/superadmin/empresa/<?= $e['id'] ?>" class="btn-detail"><i data-feather="eye" style="width:12px;height:12px"></i> Ver</a>
            <form method="post" action="/superadmin/impersonar" style="margin:0">
                <input type="hidden" name="empresa_id" value="<?= $e['id'] ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn-enter"><i data-feather="log-in" style="width:12px;height:12px"></i> Entrar</button>
            </form>
            <form method="post" action="/superadmin/empresa/<?= $e['id'] ?>/toggle" style="margin:0" onsubmit="return confirm('<?= $e['activa'] ? '¿Suspender esta empresa? Los usuarios no podrán acceder.' : '¿Reactivar esta empresa?' ?>')">
                <?= csrf_field() ?>
                <?php if ($e['activa']): ?>
                    <button type="submit" class="btn-suspend"><i data-feather="pause-circle" style="width:12px;height:12px"></i> Suspender</button>
                <?php else: ?>
                    <button type="submit" class="btn-activate"><i data-feather="play-circle" style="width:12px;height:12px"></i> Activar</button>
                <?php endif; ?>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</div>

<script>
feather.replace();

function filtrar(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('#empresas-table tbody tr').forEach(function(tr) {
        var match = !q || tr.dataset.search.indexOf(q) >= 0;
        tr.style.display = match ? '' : 'none';
    });
}
</script>

<?php
// Helper para tiempo relativo
function hace_tiempo(int $ts): string {
    $d = time() - $ts;
    if ($d <= 0) return 'ahora';
    if ($d < 60) return $d . 's';
    if ($d < 3600) return floor($d / 60) . 'm';
    if ($d < 86400) return floor($d / 3600) . 'h';
    if ($d < 86400 * 30) return floor($d / 86400) . 'd';
    return date('d/m/Y', $ts);
}
?>

</body>
</html>
