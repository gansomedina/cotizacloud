<?php
// ============================================================
//  CotizaApp — core/layout.php
//  *** SUBIR A: public_html/core/layout.php ***
// ============================================================

defined('COTIZAAPP') or die;

$usuario = Auth::usuario();
$empresa = Auth::empresa();
$flash   = flash_get();
$path    = Router::path();

// Menú principal (sidebar desktop)
$menu = [
    ['href' => '/',             'icon' => 'home',          'label' => 'Inicio'],
    ['href' => '/clientes',     'icon' => 'users',         'label' => 'Clientes'],
    ['href' => '/cotizaciones', 'icon' => 'file-text',     'label' => 'Cotizaciones'],
    ['href' => '/ventas',       'icon' => 'shopping-bag',  'label' => 'Ventas'],
    ['href' => '/costos',       'icon' => 'trending-down', 'label' => 'Costos'],
    ['href' => '/radar',        'icon' => 'activity',      'label' => 'Radar'],
    ['href' => '/reportes',     'icon' => 'bar-chart-2',   'label' => 'Reportes'],
    ['href' => '/config',       'icon' => 'settings',      'label' => 'Configuración'],
    ['href' => '/ayuda',        'icon' => 'help-circle',   'label' => 'Ayuda'],
];

if (Auth::es_superadmin()) {
    $menu[] = ['href' => '/superadmin', 'icon' => 'shield', 'label' => 'Super Admin'];
}

if (!function_exists('menu_activo')) {
    function menu_activo(string $href, string $path): bool {
        if ($href === '/') return $path === '/' || $path === '/dashboard';
        return str_starts_with($path, $href);
    }
}

// ── SVGs inline para bottom nav ────────────────────────────
// NO usamos feather.replace() para el nav porque feather no puede
// renderizar elementos que están con display:none en el momento
// que se ejecuta el JS. Con SVGs inline se garantiza que siempre
// se ven los iconos en mobile.
$S = [
    'home'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'cot'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    'ven'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
    'rad'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
    'mas'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
    'cli'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'cos'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>',
    'rep'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'cfg'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l-.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    'sal'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
    'ham'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
    'ayu'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
];

// Rutas del drawer "Más"
$mas_rutas = ['/clientes', '/costos', '/reportes', '/config', '/ayuda'];
$mas_activo = false;
foreach ($mas_rutas as $r) {
    if (str_starts_with($path, $r)) { $mas_activo = true; break; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <title><?= e($page_title ?? 'Cotiza.cloud') ?> — <?= e($empresa['nombre'] ?? 'Cotiza.cloud') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { corePlugins: { preflight: false } }</script>

    <!-- Feather: solo para sidebar y flash (están visibles desde el inicio) -->
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
            --sidebar-w:220px;
            --nav-h:60px;
        }

        *,*::before,*::after{box-sizing:border-box}
        body{font-family:var(--body);background:var(--bg);color:var(--text);margin:0;font-size:14px;line-height:1.5}

        /* ── SIDEBAR ── */
        #sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);background:var(--white);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:200;transition:transform .22s cubic-bezier(.4,0,.2,1)}
        .sidebar-logo{padding:20px 20px 16px;border-bottom:1px solid var(--border)}
        .sidebar-logo img{height:32px;object-fit:contain}
        .sidebar-logo-text{font-size:18px;font-weight:700;color:var(--g);letter-spacing:-.3px}
        .empresa-nombre{font-size:11px;color:var(--t3);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .sidebar-nav{flex:1;padding:12px 10px;overflow-y:auto}
        .nav-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:var(--r-sm);color:var(--t2);text-decoration:none;font-size:13.5px;font-weight:500;transition:background .12s,color .12s;margin-bottom:2px}
        .nav-item:hover{background:var(--bg);color:var(--text)}
        .nav-item.active{background:var(--g-bg);color:var(--g);font-weight:600}
        .nav-item svg{width:16px;height:16px;flex-shrink:0}
        .sidebar-footer{padding:12px 10px;border-top:1px solid var(--border)}

        /* Overlay sidebar mobile */
        #sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:190}
        #sidebar-overlay.on{display:block}

        /* ── MAIN ── */
        #main{margin-left:var(--sidebar-w);min-height:100dvh;display:flex;flex-direction:column}

        /* ── TOPBAR ── */
        #topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 28px;height:56px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
        .topbar-title{font-size:16px;font-weight:700;color:var(--text)}
        .topbar-right{display:flex;align-items:center;gap:12px}
        .topbar-user{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--t2)}
        .topbar-avatar{width:30px;height:30px;background:var(--g-bg);color:var(--g);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px}
        #topbar-hamburger{display:none;background:none;border:none;cursor:pointer;padding:8px;border-radius:var(--r-sm);color:var(--t2);-webkit-tap-highlight-color:transparent}
        #topbar-hamburger svg{width:22px;height:22px;display:block;stroke:currentColor}

        /* ── CONTENT ── */
        #content{padding:16px 24px 40px;flex:1}

        /* ── FLASH ── */
        .flash{padding:12px 16px;border-radius:var(--r-sm);font-size:13.5px;font-weight:500;margin-bottom:20px;display:flex;align-items:center;gap:10px}
        .flash-ok     {background:var(--g-bg);     color:var(--g);     border:1px solid var(--g-border)}
        .flash-error  {background:var(--danger-bg);color:var(--danger);border:1px solid #fca5a5}
        .flash-warning{background:var(--amb-bg);   color:var(--amb);   border:1px solid #fcd34d}
        .flash-info   {background:var(--blue-bg);  color:var(--blue);  border:1px solid #93c5fd}

        /* ── CARDS ── */
        .card{background:var(--white);border-radius:var(--r);border:1px solid var(--border);box-shadow:var(--sh)}
        .card-header{padding:18px 20px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
        .card-title{font-size:14px;font-weight:700;color:var(--text)}
        .card-body{padding:20px}

        /* ── BOTONES ── */
        .btn{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:var(--r-sm);font-family:var(--body);font-size:13.5px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:opacity .12s,transform .08s;line-height:1;-webkit-tap-highlight-color:transparent}
        .btn:active{transform:scale(.98)}
        .btn svg{width:15px;height:15px}
        .btn-primary  {background:var(--g);      color:#fff}
        .btn-secondary{background:var(--bg);     color:var(--t2);border:1px solid var(--border2)}
        .btn-danger   {background:var(--danger); color:#fff}
        .btn-ghost    {background:transparent;   color:var(--t2)}
        .btn:hover    {opacity:.88}
        .btn:disabled {opacity:.45;cursor:not-allowed}
        .btn-sm       {padding:6px 12px;font-size:12.5px}

        /* ── INPUTS ── */
        .field{margin-bottom:16px}
        .label{display:block;font-size:12.5px;font-weight:600;color:var(--t2);margin-bottom:5px}
        .input,.select,.textarea{width:100%;padding:8px 12px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font-family:var(--body);font-size:13.5px;color:var(--text);background:var(--white);transition:border-color .12s;outline:none}
        .input:focus,.select:focus,.textarea:focus{border-color:var(--g)}
        .input-num{font-family:var(--num)}

        /* ── TABLA ── */
        .table{width:100%;border-collapse:collapse}
        .table th{text-align:left;font-size:11.5px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px;padding:10px 14px;border-bottom:2px solid var(--border)}
        .table td{padding:12px 14px;font-size:13.5px;color:var(--text);border-bottom:1px solid var(--border)}
        .table tr:last-child td{border-bottom:none}
        .table tr:hover td{background:var(--bg)}

        /* ── BADGES ── */
        .badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:11.5px;font-weight:600}
        .badge-green {background:var(--g-bg);      color:var(--g)}
        .badge-amber {background:var(--amb-bg);    color:var(--amb)}
        .badge-blue  {background:var(--blue-bg);   color:var(--blue)}
        .badge-red   {background:var(--danger-bg); color:var(--danger)}
        .badge-slate {background:var(--slate-bg);  color:var(--slate)}
        .badge-purple{background:var(--purple-bg); color:var(--purple)}

        .num{font-family:var(--num)}

        /* ── BOTTOM NAV — OCULTO por defecto ── */
        #bottom-nav{display:none}

        /* ── MORE DRAWER ── */
        #more-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:var(--nav-h);background:rgba(0,0,0,.4);z-index:580}
        #more-overlay.on{display:block}
        #more-drawer{display:none;position:fixed;bottom:var(--nav-h);left:0;right:0;background:var(--white);border-top:1px solid var(--border);border-radius:14px 14px 0 0;z-index:590;box-shadow:0 -4px 24px rgba(0,0,0,.12);padding:10px 8px}
        #more-drawer.open{display:block;animation:drawerUp .22s cubic-bezier(.4,0,.2,1) both}
        @keyframes drawerUp{from{transform:translateY(100%)}to{transform:translateY(0)}}
        .more-handle{width:40px;height:4px;background:var(--border2);border-radius:2px;margin:0 auto 10px}
        .more-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px}
        .more-item{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;border-radius:var(--r-sm);text-decoration:none;color:var(--t2);font-size:12px;font-weight:500;-webkit-tap-highlight-color:transparent;transition:background .12s,color .12s}
        .more-item:active,.more-item.active{background:var(--g-bg);color:var(--g)}
        .more-item svg{width:24px;height:24px}
        .more-item-logout{color:var(--danger)}
        .more-item-logout:active{background:var(--danger-bg)}

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar{width:6px;height:6px}
        ::-webkit-scrollbar-track{background:transparent}
        ::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}

        /* ── MOBILE ── */
        @media(max-width:768px){
            html{height:100%;overflow:hidden}
            body{height:100%;overflow:hidden;font-size:15px}
            #sidebar{transform:translateX(-100%);width:80vw;max-width:300px;box-shadow:0 8px 32px rgba(0,0,0,.13)}
            #sidebar.open{transform:translateX(0)}
            #main{margin-left:0;height:100%;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior-y:none}
            #topbar{padding:0 14px;height:52px}
            #topbar-hamburger{display:flex;align-items:center;justify-content:center}
            .topbar-user span{display:none}
            /* padding-bottom = altura nav + safe area + margen */
            #content{padding:14px 14px calc(var(--nav-h) + env(safe-area-inset-bottom,0px) + 20px)}

            /* Bottom nav visible */
            #bottom-nav{
                display:flex;
                position:fixed;bottom:0;left:0;right:0;
                height:calc(var(--nav-h) + env(safe-area-inset-bottom,0px));
                padding-bottom:env(safe-area-inset-bottom,0px);
                background:var(--white);
                border-top:1px solid var(--border);
                z-index:600;
                box-shadow:0 -2px 12px rgba(0,0,0,.08);
            }
            .bn-item{
                flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
                gap:3px;text-decoration:none;color:var(--t3);font-size:10.5px;font-weight:500;
                padding:6px 4px;border:none;background:none;cursor:pointer;
                -webkit-tap-highlight-color:transparent;position:relative;
            }
            .bn-item svg{width:22px;height:22px;display:block;flex-shrink:0;stroke:currentColor;fill:none}
            .bn-item.active{color:var(--g)}
            .bn-item.active svg{stroke:var(--g)}
            .bn-item.active::before{
                content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);
                width:32px;height:3px;background:var(--g);border-radius:0 0 3px 3px
            }
        }
        @media(min-width:769px){
            #bottom-nav,#more-drawer,#more-overlay{display:none!important}
        }
    </style>

    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body>

<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside id="sidebar">
    <div class="sidebar-logo">
        <?php if (!empty($empresa['logo_url'])): ?>
            <img src="<?= e($empresa['logo_url']) ?>" alt="Logo">
        <?php else: ?>
            <div style="display:flex;align-items:center;gap:8px">
              <div style="width:34px;height:34px;border-radius:9px;background:var(--g);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg viewBox="0 0 60 48" fill="none" style="width:28px;height:22px"><path d="M48.5 38H14c-5.5 0-10-4.5-10-10 0-4.8 3.4-8.8 8-9.8C12.2 12.5 17.5 8 24 8c5.2 0 9.7 3 12 7.3C37.3 14.5 39 14 41 14c5.5 0 10 4.5 10 10 0 .7-.1 1.3-.2 2C54.3 27.5 57 31 57 35c0 1-.2 2-.5 3" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="33" cy="26" r="12" stroke="rgba(255,255,255,.5)" stroke-width="1.5"/><circle cx="33" cy="26" r="8" stroke="rgba(255,255,255,.65)" stroke-width="1.5"/><circle cx="33" cy="26" r="4" stroke="rgba(255,255,255,.8)" stroke-width="1.5"/><line x1="33" y1="26" x2="42" y2="18" stroke="#fff" stroke-width="2" stroke-linecap="round"/><circle cx="33" cy="26" r="1.8" fill="#4ade80"/></svg>
              </div>
              <div class="sidebar-logo-text">Cotiza.cloud</div>
            </div>
        <?php endif; ?>
        <div class="empresa-nombre"><?= e($empresa['nombre'] ?? '') ?></div>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($menu as $item): ?>
            <a href="<?= e($item['href']) ?>"
               class="nav-item <?= menu_activo($item['href'], $path) ? 'active' : '' ?>"
               onclick="closeSidebar()">
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
    <header id="topbar">
        <div style="display:flex;align-items:center;gap:10px">
            <button id="topbar-hamburger" onclick="openSidebar()" aria-label="Menú">
                <?= $S['ham'] ?>
            </button>
            <span class="topbar-title"><?= e($page_title ?? '') ?></span>
        </div>
        <div class="topbar-right">
            <?php if (!empty($topbar_action)) echo $topbar_action; ?>
            <div class="topbar-user">
                <div class="topbar-avatar">
                    <?= strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) ?>
                </div>
                <span><?= e($usuario['nombre'] ?? '') ?></span>
            </div>
        </div>
    </header>

    <main id="content">
        <?php if ($flash):
            $ficons = ['ok'=>'check-circle','error'=>'alert-circle','warning'=>'alert-triangle','info'=>'info'];
            $ficon  = $ficons[$flash['tipo']] ?? 'info';
        ?>
        <div class="flash flash-<?= e($flash['tipo']) ?>">
            <i data-feather="<?= $ficon ?>"></i>
            <?= e($flash['msg']) ?>
        </div>
        <?php endif; ?>
        <?php
        // Trial banner
        if (EMPRESA_ID > 0 && !Auth::es_superadmin()) {
            $trial = trial_info(EMPRESA_ID);
            if ($trial['agotado']): ?>
            <div class="flash flash-error" style="margin-bottom:16px">
                <i data-feather="alert-circle"></i>
                <div>
                    <strong>Límite de prueba alcanzado.</strong> Has usado <?= $trial['usadas'] ?>/<?= TRIAL_LIMIT ?> cotizaciones.
                    <a href="mailto:soporte@cotiza.cloud" style="color:inherit;font-weight:700;text-decoration:underline">Activa tu licencia</a>
                </div>
            </div>
            <?php elseif ($trial['cerca']): ?>
            <div class="flash flash-warning" style="margin-bottom:16px">
                <i data-feather="alert-triangle"></i>
                <div>
                    <strong>Prueba:</strong> <?= $trial['restantes'] ?> cotizaciones restantes de <?= TRIAL_LIMIT ?>.
                    <a href="mailto:soporte@cotiza.cloud" style="color:inherit;font-weight:700;text-decoration:underline">Activa tu licencia</a>
                </div>
            </div>
            <?php elseif ($trial['por_vencer']): ?>
            <div class="flash flash-warning" style="margin-bottom:16px">
                <i data-feather="alert-triangle"></i>
                <div>
                    <strong>Tu licencia vence en <?= $trial['dias_restantes'] ?> día<?= $trial['dias_restantes'] !== 1 ? 's' : '' ?>.</strong>
                    Contacta a <a href="mailto:soporte@cotiza.cloud" style="color:inherit;font-weight:700;text-decoration:underline">soporte</a> para renovar.
                </div>
            </div>
            <?php endif;
        }
        ?>
        <?php if (isset($content)) echo $content; ?>
    </main>
</div>

<!-- BOTTOM NAV — SVGs inline (feather no renderiza display:none) -->
<nav id="bottom-nav">
    <a href="/"             class="bn-item <?= menu_activo('/',             $path) ? 'active' : '' ?>"><?= $S['home'] ?>Inicio</a>
    <a href="/cotizaciones" class="bn-item <?= menu_activo('/cotizaciones', $path) ? 'active' : '' ?>"><?= $S['cot']  ?>Cotizaciones</a>
    <a href="/ventas"       class="bn-item <?= menu_activo('/ventas',       $path) ? 'active' : '' ?>"><?= $S['ven']  ?>Ventas</a>
    <a href="/radar"        class="bn-item <?= menu_activo('/radar',        $path) ? 'active' : '' ?>"><?= $S['rad']  ?>Radar</a>
    <button class="bn-item <?= $mas_activo ? 'active' : '' ?>" onclick="toggleMoreDrawer()"><?= $S['mas'] ?>Más</button>
</nav>

<!-- MORE DRAWER -->
<div id="more-overlay" onclick="closeMoreDrawer()"></div>
<div id="more-drawer">
    <div class="more-handle"></div>
    <div class="more-grid">
        <a href="/clientes" class="more-item <?= menu_activo('/clientes', $path) ? 'active' : '' ?>" onclick="closeMoreDrawer()"><?= $S['cli'] ?>Clientes</a>
        <a href="/costos"   class="more-item <?= menu_activo('/costos',   $path) ? 'active' : '' ?>" onclick="closeMoreDrawer()"><?= $S['cos'] ?>Costos</a>
        <a href="/reportes" class="more-item <?= menu_activo('/reportes', $path) ? 'active' : '' ?>" onclick="closeMoreDrawer()"><?= $S['rep'] ?>Reportes</a>
        <a href="/config"   class="more-item <?= menu_activo('/config',   $path) ? 'active' : '' ?>" onclick="closeMoreDrawer()"><?= $S['cfg'] ?>Configuración</a>
        <a href="/ayuda"    class="more-item <?= menu_activo('/ayuda',    $path) ? 'active' : '' ?>" onclick="closeMoreDrawer()"><?= $S['ayu'] ?>Ayuda</a>
        <a href="/logout"   class="more-item more-item-logout"><?= $S['sal'] ?>Salir</a>
    </div>
</div>

<script>
feather.replace({'stroke-width':1.8});

function openSidebar(){
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebar-overlay').classList.add('on');
    document.body.style.overflow='hidden';
}
function closeSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('on');
    document.body.style.overflow='';
}
function openMoreDrawer(){
    document.getElementById('more-drawer').classList.add('open');
    document.getElementById('more-overlay').classList.add('on');
}
function closeMoreDrawer(){
    document.getElementById('more-drawer').classList.remove('open');
    document.getElementById('more-overlay').classList.remove('on');
}
function toggleMoreDrawer(){
    var d=document.getElementById('more-drawer');
    if(d.classList.contains('open')) closeMoreDrawer(); else openMoreDrawer();
}
// Swipe down cierra drawer
(function(){
    var sy=0,dr=document.getElementById('more-drawer');
    dr.addEventListener('touchstart',function(e){sy=e.touches[0].clientY},{passive:true});
    dr.addEventListener('touchend',function(e){if(e.changedTouches[0].clientY-sy>55)closeMoreDrawer()},{passive:true});
})();
</script>

<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>
