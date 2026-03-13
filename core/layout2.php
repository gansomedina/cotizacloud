<?php
// ============================================================
//  CotizaApp — core/layout.php
//  Shell HTML base del app: sidebar + topbar + contenido
//  Uso: require ROOT_PATH . '/core/layout.php';
//       La variable $page_title y $active_menu se usan aquí.
// ============================================================

defined('COTIZAAPP') or die;

$usuario = Auth::usuario();
$empresa = Auth::empresa();
$flash   = flash_get();
$path    = Router::path();

// Menú principal
$menu = [
    ['href' => '/',             'icon' => 'home',          'label' => 'Inicio'],
    ['href' => '/cotizaciones', 'icon' => 'file-text',     'label' => 'Cotizaciones'],
    ['href' => '/clientes',     'icon' => 'users',         'label' => 'Clientes'],
    ['href' => '/ventas',       'icon' => 'shopping-bag',  'label' => 'Ventas'],
    ['href' => '/costos',       'icon' => 'trending-down',  'label' => 'Costos'],
    ['href' => '/radar',        'icon' => 'activity',      'label' => 'Radar'],
    ['href' => '/reportes',     'icon' => 'bar-chart-2',   'label' => 'Reportes'],
    ['href' => '/config',       'icon' => 'settings',      'label' => 'Configuración'],
];

function menu_activo(string $href, string $path): bool {
    if ($href === '/') return $path === '/' || $path === '/dashboard';
    return str_starts_with($path, $href);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'CotizaApp') ?> — <?= e($empresa['nombre'] ?? 'CotizaApp') ?></title>

    <!-- Design System fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>

    <style>
        :root {
            --bg:#f4f4f0; --white:#fff; --border:#e2e2dc; --border2:#c8c8c0;
            --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
            --g:#1a5c38; --g-bg:#eef7f2; --g-border:#b8ddc8; --g-light:#e6f4ed;
            --amb:#92400e; --amb-bg:#fef3c7;
            --blue:#1d4ed8; --blue-bg:#dbeafe;
            --danger:#c53030; --danger-bg:#fff5f5;
            --purple:#6d28d9; --purple-bg:#ede9fe;
            --slate:#475569; --slate-bg:#f1f5f9;
            --r:12px; --r-sm:9px;
            --sh:0 1px 3px rgba(0,0,0,.06); --sh-md:0 4px 16px rgba(0,0,0,.08);
            --body:'Plus Jakarta Sans',sans-serif;
            --num:'DM Sans',sans-serif;
            --sidebar-w: 220px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: var(--body);
            background: var(--bg);
            color: var(--text);
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--white);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo img { height: 32px; object-fit: contain; }
        .sidebar-logo-text {
            font-size: 18px;
            font-weight: 700;
            color: var(--g);
            letter-spacing: -.3px;
        }
        .empresa-nombre {
            font-size: 11px;
            color: var(--t3);
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-nav { flex: 1; padding: 12px 10px; overflow-y: auto; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: var(--r-sm);
            color: var(--t2);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: background .12s, color .12s;
            margin-bottom: 2px;
        }
        .nav-item:hover  { background: var(--bg); color: var(--text); }
        .nav-item.active { background: var(--g-bg); color: var(--g); font-weight: 600; }
        .nav-item svg    { width: 16px; height: 16px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 12px 10px;
            border-top: 1px solid var(--border);
        }

        /* Main content */
        #main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        #topbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--t2);
        }
        .topbar-avatar {
            width: 30px;
            height: 30px;
            background: var(--g-bg);
            color: var(--g);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        /* Page content */
        #content {
            padding: 28px;
            flex: 1;
        }

        /* Flash message */
        .flash {
            padding: 12px 16px;
            border-radius: var(--r-sm);
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .flash-ok      { background: var(--g-bg);      color: var(--g);      border: 1px solid var(--g-border); }
        .flash-error   { background: var(--danger-bg);  color: var(--danger); border: 1px solid #fca5a5; }
        .flash-warning { background: var(--amb-bg);     color: var(--amb);    border: 1px solid #fcd34d; }
        .flash-info    { background: var(--blue-bg);    color: var(--blue);   border: 1px solid #93c5fd; }

        /* Cards */
        .card {
            background: var(--white);
            border-radius: var(--r);
            border: 1px solid var(--border);
            box-shadow: var(--sh);
        }
        .card-header {
            padding: 18px 20px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }
        .card-body { padding: 20px; }

        /* Botones */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: var(--r-sm);
            font-family: var(--body);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: opacity .12s, transform .08s;
            line-height: 1;
        }
        .btn:active { transform: scale(.98); }
        .btn svg    { width: 15px; height: 15px; }

        .btn-primary   { background: var(--g);     color: #fff; }
        .btn-secondary { background: var(--bg);    color: var(--t2); border: 1px solid var(--border2); }
        .btn-danger    { background: var(--danger); color: #fff; }
        .btn-ghost     { background: transparent;  color: var(--t2); }
        .btn:hover     { opacity: .88; }
        .btn:disabled  { opacity: .45; cursor: not-allowed; }
        .btn-sm        { padding: 6px 12px; font-size: 12.5px; }

        /* Inputs */
        .field { margin-bottom: 16px; }
        .label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--t2);
            margin-bottom: 5px;
        }
        .input, .select, .textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1.5px solid var(--border2);
            border-radius: var(--r-sm);
            font-family: var(--body);
            font-size: 13.5px;
            color: var(--text);
            background: var(--white);
            transition: border-color .12s;
            outline: none;
        }
        .input:focus, .select:focus, .textarea:focus {
            border-color: var(--g);
        }
        .input-num { font-family: var(--num); }

        /* Tabla */
        .table { width: 100%; border-collapse: collapse; }
        .table th {
            text-align: left;
            font-size: 11.5px;
            font-weight: 700;
            color: var(--t3);
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 10px 14px;
            border-bottom: 2px solid var(--border);
        }
        .table td {
            padding: 12px 14px;
            font-size: 13.5px;
            color: var(--text);
            border-bottom: 1px solid var(--border);
        }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td      { background: var(--bg); }

        /* Badges de estado */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 600;
        }
        .badge-green    { background: var(--g-bg);    color: var(--g); }
        .badge-amber    { background: var(--amb-bg);  color: var(--amb); }
        .badge-blue     { background: var(--blue-bg); color: var(--blue); }
        .badge-red      { background: var(--danger-bg); color: var(--danger); }
        .badge-slate    { background: var(--slate-bg);  color: var(--slate); }
        .badge-purple   { background: var(--purple-bg); color: var(--purple); }

        /* Nums con fuente DM Sans */
        .num { font-family: var(--num); }

        /* Mobile overlay sidebar */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                transition: transform .2s;
            }
            #sidebar.open { transform: translateX(0); }
            #main { margin-left: 0; }
            #content { padding: 16px; }
            #topbar { padding: 0 16px; }
        }

        /* Scrollbar sutil */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }
    </style>

    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body>

<!-- SIDEBAR -->
<aside id="sidebar">
    <div class="sidebar-logo">
        <?php if (!empty($empresa['logo_url'])): ?>
            <img src="<?= e($empresa['logo_url']) ?>" alt="Logo">
        <?php else: ?>
            <div class="sidebar-logo-text">CotizaApp</div>
        <?php endif; ?>
        <div class="empresa-nombre"><?= e($empresa['nombre'] ?? '') ?></div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($menu as $item): ?>
            <a href="<?= e($item['href']) ?>"
               class="nav-item <?= menu_activo($item['href'], $path) ? 'active' : '' ?>">
                <i data-feather="<?= e($item['icon']) ?>"></i>
                <?= e($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="/logout" class="nav-item" style="color:var(--danger)">
            <i data-feather="log-out"></i>
            Cerrar sesión
        </a>
    </div>
</aside>

<!-- MAIN -->
<div id="main">

    <!-- TOPBAR -->
    <header id="topbar">
        <div style="display:flex;align-items:center;gap:12px">
            <!-- Hamburger mobile -->
            <button id="sidebar-toggle"
                    style="display:none;background:none;border:none;cursor:pointer;padding:4px"
                    onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i data-feather="menu" style="width:20px;height:20px"></i>
            </button>
            <span class="topbar-title"><?= e($page_title ?? '') ?></span>
        </div>

        <div class="topbar-right">
            <div class="topbar-user">
                <div class="topbar-avatar">
                    <?= strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) ?>
                </div>
                <span style="display:none" class="md-show"><?= e($usuario['nombre'] ?? '') ?></span>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <main id="content">

        <!-- Flash message -->
        <?php if ($flash): ?>
            <div class="flash flash-<?= e($flash['tipo']) ?>">
                <?php
                $icons = ['ok'=>'check-circle','error'=>'alert-circle','warning'=>'alert-triangle','info'=>'info'];
                $icon  = $icons[$flash['tipo']] ?? 'info';
                ?>
                <i data-feather="<?= $icon ?>"></i>
                <?= e($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <?php
        // El contenido de la página se incluye aquí
        // Los módulos hacen: require LAYOUT_PATH; dentro de ob_start/ob_get_clean
        // O bien: definen $content y este layout lo imprime
        if (isset($content)) echo $content;
        ?>

    </main>
</div>

<script>
    // Feather icons
    feather.replace();

    // Mobile: mostrar hamburger
    if (window.innerWidth <= 768) {
        document.getElementById('sidebar-toggle').style.display = 'block';
    }

    // Cerrar sidebar al hacer clic fuera (mobile)
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle  = document.getElementById('sidebar-toggle');
        if (sidebar.classList.contains('open') &&
            !sidebar.contains(e.target) &&
            !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
</script>

<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>
