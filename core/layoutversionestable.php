<?php
// ============================================================
//  CotizaApp — core/layout.php  v2.0
//  Shell HTML base: sidebar desktop + bottom nav mobile
//  Breakpoint: 768px
// ============================================================

defined('COTIZAAPP') or die;

$usuario = Auth::usuario();
$empresa = Auth::empresa();
$flash   = flash_get();
$path    = Router::path();

$menu = [
    ['href' => '/',             'icon' => 'home',         'label' => 'Inicio'],
    ['href' => '/cotizaciones', 'icon' => 'file-text',    'label' => 'Cotizaciones'],
    ['href' => '/clientes',     'icon' => 'users',        'label' => 'Clientes'],
    ['href' => '/ventas',       'icon' => 'shopping-bag', 'label' => 'Ventas'],
    ['href' => '/costos',       'icon' => 'trending-down','label' => 'Costos'],
    ['href' => '/radar',        'icon' => 'activity',     'label' => 'Radar'],
    ['href' => '/reportes',     'icon' => 'bar-chart-2',  'label' => 'Reportes'],
    ['href' => '/config',       'icon' => 'settings',     'label' => 'Configuración'],
];

$bottom_nav = [
    ['href' => '/',             'icon' => 'home',         'label' => 'Inicio'],
    ['href' => '/cotizaciones', 'icon' => 'file-text',    'label' => 'Cotizaciones'],
    ['href' => '/ventas',       'icon' => 'shopping-bag', 'label' => 'Ventas'],
    ['href' => '/radar',        'icon' => 'activity',     'label' => 'Radar'],
];

if (!function_exists('menu_activo')) {
    function menu_activo(string $href, string $path): bool {
        if ($href === '/') return $path === '/' || $path === '/dashboard';
        return str_starts_with($path, $href);
    }
}

$mas_rutas  = ['/clientes', '/costos', '/reportes', '/config'];
$mas_activo = false;
foreach ($mas_rutas as $r) {
    if (str_starts_with($path, $r)) { $mas_activo = true; break; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($page_title ?? 'CotizaApp') ?> — <?= e($empresa['nombre'] ?? 'CotizaApp') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { corePlugins: { preflight: false } }</script>
    <script src="/assets/js/feather.min.js"></script>
    <style>

    :root {
        --bg:#f4f4f0; --white:#fff; --border:#e2e2dc; --border2:#c8c8c0;
        --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
        --g:#1a5c38; --g-bg:#eef7f2; --g-border:#b8ddc8; --g-light:#e6f4ed; --g-dark:#144a2c;
        --amb:#92400e; --amb-bg:#fef3c7;
        --blue:#1d4ed8; --blue-bg:#dbeafe;
        --danger:#c53030; --danger-bg:#fff5f5;
        --purple:#6d28d9; --purple-bg:#ede9fe;
        --slate:#475569; --slate-bg:#f1f5f9;
        --r:12px; --r-sm:9px; --r-xs:6px;
        --sh:0 1px 3px rgba(0,0,0,.06); --sh-md:0 4px 16px rgba(0,0,0,.10); --sh-lg:0 8px 32px rgba(0,0,0,.13);
        --body:'Plus Jakarta Sans',sans-serif; --num:'DM Sans',sans-serif;
        --sidebar-w:220px; --topbar-h:56px; --bottomnav-h:64px;
    }
    *,*::before,*::after{box-sizing:border-box}
    html{-webkit-text-size-adjust:100%}
    body{font-family:var(--body);background:var(--bg);color:var(--text);margin:0;font-size:14px;line-height:1.5;overflow-x:hidden}

    /* SIDEBAR */
    #sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);background:var(--white);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:200;transition:transform .22s cubic-bezier(.4,0,.2,1)}
    .sidebar-logo{padding:18px 20px 14px;border-bottom:1px solid var(--border);flex-shrink:0}
    .sidebar-logo img{height:34px;object-fit:contain;display:block}
    .sidebar-logo-text{font-size:19px;font-weight:700;color:var(--g);letter-spacing:-.4px}
    .empresa-nombre{font-size:11px;color:var(--t3);margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .sidebar-nav{flex:1;padding:10px 8px;overflow-y:auto}
    .nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--r-sm);color:var(--t2);text-decoration:none;font-size:13.5px;font-weight:500;transition:background .12s,color .12s;margin-bottom:2px;min-height:40px}
    .nav-item:hover{background:var(--bg);color:var(--text)}
    .nav-item.active{background:var(--g-bg);color:var(--g);font-weight:600}
    .nav-item svg{width:16px;height:16px;flex-shrink:0}
    .sidebar-footer{padding:10px 8px;border-top:1px solid var(--border);flex-shrink:0}

    /* OVERLAY */
    #sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:190;opacity:0;transition:opacity .22s}
    #sidebar-overlay.on{display:block;opacity:1}

    /* MAIN */
    #main{margin-left:var(--sidebar-w);min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden;max-width:100%}

    /* TOPBAR */
    #topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 24px;height:var(--topbar-h);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;flex-shrink:0}
    .topbar-left{display:flex;align-items:center;gap:10px;min-width:0;flex:1}
    #topbar-hamburger{display:none;align-items:center;justify-content:center;width:40px;height:40px;border:none;background:none;cursor:pointer;border-radius:var(--r-xs);color:var(--t2);flex-shrink:0;padding:0}
    #topbar-hamburger:hover{background:var(--bg)}
    #topbar-hamburger svg{width:22px;height:22px}
    .topbar-title{font-size:16px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .topbar-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
    .topbar-avatar{width:32px;height:32px;background:var(--g-bg);color:var(--g);border:2px solid var(--g-border);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0}
    .topbar-username{font-size:13px;color:var(--t2);font-weight:500}

    /* CONTENT */
    #content{padding:20px 24px 48px;flex:1;min-width:0;max-width:100%;overflow-x:hidden}

    /* BOTTOM NAV */
    #bottom-nav{display:none}

    /* FLASH */
    .flash{padding:12px 16px;border-radius:var(--r-sm);font-size:13.5px;font-weight:500;margin-bottom:20px;display:flex;align-items:center;gap:10px}
    .flash svg{width:17px;height:17px;flex-shrink:0}
    .flash-ok{background:var(--g-bg);color:var(--g);border:1px solid var(--g-border)}
    .flash-error{background:var(--danger-bg);color:var(--danger);border:1px solid #fca5a5}
    .flash-warning{background:var(--amb-bg);color:var(--amb);border:1px solid #fcd34d}
    .flash-info{background:var(--blue-bg);color:var(--blue);border:1px solid #93c5fd}

    /* CARDS */
    .card{background:var(--white);border-radius:var(--r);border:1px solid var(--border);box-shadow:var(--sh)}
    .card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .card-title{font-size:14px;font-weight:700;color:var(--text)}
    .card-body{padding:20px}

    /* BOTONES */
    .btn{display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:var(--r-sm);font-family:var(--body);font-size:13.5px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:opacity .12s,transform .08s,background .12s;line-height:1;min-height:40px;white-space:nowrap;-webkit-tap-highlight-color:transparent}
    .btn:active{transform:scale(.97)}
    .btn svg{width:15px;height:15px;flex-shrink:0}
    .btn:hover{opacity:.88}
    .btn:disabled{opacity:.4;cursor:not-allowed;transform:none}
    .btn-primary{background:var(--g);color:#fff}
    .btn-secondary{background:var(--bg);color:var(--t2);border:1px solid var(--border2)}
    .btn-danger{background:var(--danger);color:#fff}
    .btn-ghost{background:transparent;color:var(--t2)}
    .btn-blue{background:var(--blue);color:#fff}
    .btn-sm{padding:6px 13px;font-size:12.5px;min-height:34px}
    .btn-lg{padding:12px 24px;font-size:15px;min-height:48px}
    .btn-icon{padding:10px;width:40px;height:40px;justify-content:center}

    /* FORMULARIOS */
    .field{margin-bottom:18px}
    .field:last-child{margin-bottom:0}
    .label{display:block;font-size:12.5px;font-weight:600;color:var(--t2);margin-bottom:6px;letter-spacing:.2px}
    .input,.select,.textarea{width:100%;padding:10px 13px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font-family:var(--body);font-size:14px;color:var(--text);background:var(--white);transition:border-color .12s,box-shadow .12s;outline:none;min-height:44px;-webkit-appearance:none}
    .input:focus,.select:focus,.textarea:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(26,92,56,.10)}
    .input::placeholder{color:var(--t3)}
    .input-num{font-family:var(--num)}
    .textarea{min-height:120px;resize:vertical;line-height:1.6}
    .textarea-lg{min-height:200px}
    .fields-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 20px}
    .fields-grid .field-full{grid-column:1/-1}

    /* TABLAS */
    .table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:var(--r);border:1px solid var(--border);box-shadow:var(--sh)}
    .table{width:100%;border-collapse:collapse;background:var(--white);min-width:480px}
    .table th{text-align:left;font-size:11.5px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.5px;padding:10px 14px;border-bottom:2px solid var(--border);white-space:nowrap;background:var(--bg)}
    .table td{padding:12px 14px;font-size:13.5px;color:var(--text);border-bottom:1px solid var(--border);vertical-align:middle}
    .table tr:last-child td{border-bottom:none}
    .table tr:hover td{background:#fafaf8}

    /* MOB CARDS (patrón listas mobile) */
    .mob-card{background:var(--white);border-bottom:1px solid var(--border);padding:16px}
    .mob-card:last-child{border-bottom:none}

    /* BADGES */
    .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:600;white-space:nowrap}
    .badge-green{background:var(--g-bg);color:var(--g)}
    .badge-amber{background:var(--amb-bg);color:var(--amb)}
    .badge-blue{background:var(--blue-bg);color:var(--blue)}
    .badge-red{background:var(--danger-bg);color:var(--danger)}
    .badge-slate{background:var(--slate-bg);color:var(--slate)}
    .badge-purple{background:var(--purple-bg);color:var(--purple)}

    /* UTILIDADES */
    .num{font-family:var(--num)}
    .text-muted{color:var(--t3)}
    .text-sm{font-size:12.5px}
    .text-xs{font-size:11.5px}
    .fw-600{font-weight:600}
    .fw-700{font-weight:700}
    .divider{border:none;border-top:1px solid var(--border);margin:20px 0}
    ::-webkit-scrollbar{width:6px;height:6px}
    ::-webkit-scrollbar-track{background:transparent}
    ::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}
    ::-webkit-scrollbar-thumb:hover{background:var(--t3)}

    /* PAGE HEADER */
    .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap}
    .page-header-title{font-size:20px;font-weight:700;color:var(--text);margin:0;line-height:1.2}
    .page-header-sub{font-size:13px;color:var(--t3);margin-top:3px}

    /* DRAWER MAS */
    #more-drawer{display:none;position:fixed;bottom:var(--bottomnav-h);left:0;right:0;background:var(--white);border-top:1px solid var(--border);border-radius:var(--r) var(--r) 0 0;z-index:160;box-shadow:0 -4px 24px rgba(0,0,0,.13);transform:translateY(100%);transition:transform .22s cubic-bezier(.4,0,.2,1);padding:8px 8px calc(8px + env(safe-area-inset-bottom))}
    #more-drawer.open{display:block;transform:translateY(0)}
    #more-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:155}
    #more-overlay.on{display:block}
    .more-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;padding:8px 0}
    .more-item{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;border-radius:var(--r-sm);text-decoration:none;color:var(--t2);font-size:12px;font-weight:500;transition:background .12s,color .12s;-webkit-tap-highlight-color:transparent}
    .more-item:hover,.more-item.active{background:var(--g-bg);color:var(--g)}
    .more-item svg{width:24px;height:24px}
    .more-item-logout{color:var(--danger)}
    .more-item-logout:hover{background:var(--danger-bg);color:var(--danger)}
    .drawer-handle{width:40px;height:4px;background:var(--border2);border-radius:2px;margin:0 auto 4px}

    /* MOBILE */
    @media(max-width:768px){
        body{font-size:15px}
        #sidebar{transform:translateX(-100%);width:80vw;max-width:300px;box-shadow:var(--sh-lg)}
        #sidebar.open{transform:translateX(0)}
        #main{margin-left:0}
        #topbar{padding:0 16px;height:52px}
        #topbar-hamburger{display:flex}
        .topbar-username{display:none}
        #content{padding:14px 14px calc(var(--bottomnav-h) + 16px);overflow-x:hidden}
        #bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;height:var(--bottomnav-h);background:var(--white);border-top:1px solid var(--border);z-index:150;box-shadow:0 -2px 12px rgba(0,0,0,.08);padding-bottom:env(safe-area-inset-bottom)}
        .bn-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;text-decoration:none;color:var(--t3);font-size:10.5px;font-weight:500;padding:6px 4px;transition:color .12s,background .12s;border:none;background:none;cursor:pointer;-webkit-tap-highlight-color:transparent;position:relative}
        .bn-item svg{width:22px;height:22px}
        .bn-item.active{color:var(--g)}
        .bn-item.active svg{stroke:var(--g)}
        .bn-item.active::before{content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);width:32px;height:3px;background:var(--g);border-radius:0 0 3px 3px}
        .fields-grid{grid-template-columns:1fr}
        .field-full{grid-column:1}
        .input,.select,.textarea{font-size:15px;min-height:48px;padding:11px 14px}
        .textarea{min-height:140px}
        .textarea-lg{min-height:220px}
        .btn{min-height:46px;font-size:14px;padding:11px 18px}
        .btn-sm{min-height:40px;font-size:13px;padding:8px 14px}
        .flash{font-size:14px}
        .card-header{padding:14px 16px}
        .card-body{padding:14px 16px}
        .table-mobile-hidden{display:none}
        .desk-only{display:none!important}
        .mob-only{display:block!important}
        .page-header-title{font-size:18px}
    }
    @media(min-width:769px){
        .mob-only{display:none!important}
        .desk-only{display:block!important}
        #bottom-nav{display:none!important}
        #sidebar-overlay{display:none!important}
        #topbar-hamburger{display:none!important}
    }
    </style>
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body>

<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<aside id="sidebar">
    <div class="sidebar-logo">
        <?php if (!empty($empresa['logo_url'])): ?>
            <img src="<?= e($empresa['logo_url']) ?>" alt="<?= e($empresa['nombre'] ?? '') ?>">
        <?php else: ?>
            <div class="sidebar-logo-text">CotizaApp</div>
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

<div id="main">
    <header id="topbar">
        <div class="topbar-left">
            <button id="topbar-hamburger" onclick="openSidebar()" aria-label="Menú">
                <i data-feather="menu"></i>
            </button>
            <span class="topbar-title"><?= e($page_title ?? '') ?></span>
        </div>
        <div class="topbar-right">
            <?php if (!empty($topbar_action)) echo $topbar_action; ?>
            <div style="display:flex;align-items:center;gap:8px">
                <div class="topbar-avatar">
                    <?= strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) ?>
                </div>
                <span class="topbar-username"><?= e($usuario['nombre'] ?? '') ?></span>
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
        <?php if (isset($content)) echo $content; ?>
    </main>
</div>

<nav id="bottom-nav">
    <?php foreach ($bottom_nav as $bn): ?>
        <a href="<?= e($bn['href']) ?>"
           class="bn-item <?= menu_activo($bn['href'], $path) ? 'active' : '' ?>">
            <i data-feather="<?= e($bn['icon']) ?>"></i>
            <?= e($bn['label']) ?>
        </a>
    <?php endforeach; ?>
    <button class="bn-item <?= $mas_activo ? 'active' : '' ?>"
            id="btn-more" onclick="toggleMoreDrawer()" aria-label="Más opciones">
        <i data-feather="grid"></i>
        Más
    </button>
</nav>

<div id="more-overlay" onclick="closeMoreDrawer()"></div>
<div id="more-drawer">
    <div class="drawer-handle"></div>
    <div class="more-grid">
        <?php
        $more_items = [
            ['href'=>'/clientes', 'icon'=>'users',        'label'=>'Clientes'],
            ['href'=>'/costos',   'icon'=>'trending-down', 'label'=>'Costos'],
            ['href'=>'/reportes', 'icon'=>'bar-chart-2',  'label'=>'Reportes'],
            ['href'=>'/config',   'icon'=>'settings',     'label'=>'Configuración'],
        ];
        foreach ($more_items as $mi): ?>
        <a href="<?= e($mi['href']) ?>"
           class="more-item <?= menu_activo($mi['href'], $path) ? 'active' : '' ?>"
           onclick="closeMoreDrawer()">
            <i data-feather="<?= e($mi['icon']) ?>"></i>
            <?= e($mi['label']) ?>
        </a>
        <?php endforeach; ?>
        <a href="/logout" class="more-item more-item-logout">
            <i data-feather="log-out"></i>
            Salir
        </a>
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
    var d=document.getElementById('more-drawer');
    var o=document.getElementById('more-overlay');
    d.style.display='block';
    o.classList.add('on');
    d.offsetHeight; // reflow
    d.classList.add('open');
    feather.replace({'stroke-width':1.8});
}
function closeMoreDrawer(){
    var d=document.getElementById('more-drawer');
    var o=document.getElementById('more-overlay');
    d.classList.remove('open');
    o.classList.remove('on');
    setTimeout(function(){ if(!d.classList.contains('open')) d.style.display=''; },240);
}
function toggleMoreDrawer(){
    var d=document.getElementById('more-drawer');
    if(d.classList.contains('open')) closeMoreDrawer();
    else openMoreDrawer();
}

// Swipe hacia abajo cierra el drawer
(function(){
    var startY=0;
    var drawer=document.getElementById('more-drawer');
    drawer.addEventListener('touchstart',function(e){startY=e.touches[0].clientY},{passive:true});
    drawer.addEventListener('touchend',function(e){if(e.changedTouches[0].clientY-startY>60)closeMoreDrawer()},{passive:true});
})();
</script>

<?php if (isset($extra_scripts)) echo $extra_scripts; ?>
</body>
</html>
