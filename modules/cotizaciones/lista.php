<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/lista.php
//  GET /cotizaciones
// ============================================================

defined('COTIZAAPP') or die;

$usuario    = Auth::usuario();
$empresa    = Auth::empresa();
$empresa_id = EMPRESA_ID;

// ─── Filtros y búsqueda ──────────────────────────────────────
$estado   = $_GET['estado']  ?? 'todas';
$busqueda = trim($_GET['q']  ?? '');
$orden    = $_GET['orden']   ?? 'reciente';
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = 20;

$estados_validos = ['todas','borrador','enviada','vista','aceptada','rechazada','vencida','convertida'];
if (!in_array($estado, $estados_validos)) $estado = 'todas';

$ordenes_validos = ['reciente','antigua','monto_asc','monto_desc','cliente'];
if (!in_array($orden, $ordenes_validos)) $orden = 'reciente';

// ─── Cláusulas SQL dinámicas ─────────────────────────────────
$where  = ["c.empresa_id = ?"];
$params = [$empresa_id];

// Filtro de estado
if ($estado !== 'todas') {
    $where[]  = "c.estado = ?";
    $params[] = $estado;
}

// Filtro de asesor (si no puede ver todas)
if (!Auth::puede('ver_todas_cots')) {
    $where[]  = "c.usuario_id = ?";
    $params[] = $usuario['id'];
}

// Búsqueda
if ($busqueda !== '') {
    $where[]  = "(c.titulo LIKE ? OR c.numero LIKE ? OR cl.nombre LIKE ? OR cl.telefono LIKE ?)";
    $like     = '%' . $busqueda . '%';
    $params   = array_merge($params, [$like, $like, $like, $like]);
}

$where_sql = implode(' AND ', $where);

// Orden
$order_sql = match($orden) {
    'antigua'    => 'c.created_at ASC',
    'monto_asc'  => 'c.total ASC',
    'monto_desc' => 'c.total DESC',
    'cliente'    => 'cl.nombre ASC',
    default      => 'c.created_at DESC',
};

// ─── Conteos por estado ──────────────────────────────────────
$conteo_params = [$empresa_id];
$conteo_where  = ["c.empresa_id = ?"];
if (!Auth::puede('ver_todas_cots')) {
    $conteo_where[]  = "c.usuario_id = ?";
    $conteo_params[] = $usuario['id'];
}
$conteo_where_sql = implode(' AND ', $conteo_where);

$conteos_raw = DB::query(
    "SELECT estado, COUNT(*) AS n
     FROM cotizaciones c
     WHERE $conteo_where_sql
     GROUP BY estado",
    $conteo_params
);

$conteos = ['todas' => 0];
foreach ($conteos_raw as $r) {
    $conteos[$r['estado']] = (int)$r['n'];
    $conteos['todas'] += (int)$r['n'];
}

// ─── Total para paginación ───────────────────────────────────
$total_rows = (int) DB::val(
    "SELECT COUNT(*) FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE $where_sql",
    $params
);

$pag = paginar($total_rows, $pagina, $por_pag);

// ─── Query principal ─────────────────────────────────────────
$cotizaciones = DB::query(
    "SELECT
        c.id, c.numero, c.titulo, c.slug, c.estado,
        c.total, c.subtotal, c.created_at, c.enviada_at,
        c.vista_at, c.valida_hasta, c.radar_bucket,
        c.cupon_pct, c.descuento_auto_pct, c.descuento_auto_activo,
        cl.nombre  AS cliente_nombre,
        cl.telefono AS cliente_telefono,
        u.nombre   AS asesor_nombre
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     LEFT JOIN usuarios u  ON u.id  = c.usuario_id
     WHERE $where_sql
     ORDER BY $order_sql
     LIMIT ? OFFSET ?",
    array_merge($params, [$por_pag, $pag['offset']])
);

// ─── Labels y colores de estados ────────────────────────────
function estado_badge(string $estado): string {
    return match($estado) {
        'borrador'   => '<span class="badge badge-slate">Borrador</span>',
        'enviada'    => '<span class="badge badge-blue">Enviada</span>',
        'vista'      => '<span class="badge badge-amber">Vista</span>',
        'aceptada'   => '<span class="badge badge-green">Aceptada</span>',
        'rechazada'  => '<span class="badge badge-red">Rechazada</span>',
        'vencida'    => '<span class="badge badge-red">Vencida</span>',
        'convertida' => '<span class="badge badge-green">Convertida</span>',
        default      => '<span class="badge badge-slate">' . e($estado) . '</span>',
    };
}

$page_title = 'Cotizaciones';
ob_start();
?>

<style>
.toolbar      { display:flex; gap:8px; margin-bottom:12px; flex-wrap:wrap; align-items:center; }
.search-wrap  { flex:1; min-width:200px; position:relative; }
.search-ico   { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--t3); pointer-events:none; }
.search-wrap input {
    width:100%; background:var(--white); border:1.5px solid var(--border);
    border-radius:var(--r-sm); padding:9px 14px 9px 36px;
    font:400 14px var(--body); color:var(--text);
    outline:none; transition:border-color .15s; box-shadow:var(--sh);
}
.search-wrap input:focus { border-color:var(--g); }

.filter-bar { display:flex; gap:6px; margin-bottom:14px; overflow-x:auto; padding-bottom:2px; scrollbar-width:none; }
.filter-bar::-webkit-scrollbar { display:none; }
.chip {
    padding:7px 13px; border-radius:20px; border:1px solid var(--border);
    background:var(--white); font:600 12px var(--body); color:var(--t2);
    cursor:pointer; white-space:nowrap; flex-shrink:0;
    display:inline-flex; align-items:center; gap:5px;
    text-decoration:none; transition:all .12s;
}
.chip:hover               { border-color:var(--g); color:var(--g); }
.chip.active              { background:var(--g); border-color:var(--g); color:#fff; }
.chip-count               { font:700 10px var(--body); background:rgba(0,0,0,.1); padding:1px 5px; border-radius:10px; }
.chip.active .chip-count  { background:rgba(255,255,255,.25); }

.results-bar  { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
.results-count{ font:500 12px var(--body); color:var(--t3); }

/* Lista */
.cot-list     { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }
.cot-row      { display:flex; align-items:center; gap:14px; padding:14px 18px; border-bottom:1px solid var(--border); text-decoration:none; color:inherit; transition:background .1s; }
.cot-row:last-child { border-bottom:none; }
.cot-row:hover{ background:var(--bg); }

.cot-icon     { width:38px; height:38px; border-radius:9px; background:var(--bg); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.cot-main     { flex:1; min-width:0; }
.cot-titulo   { font:600 14px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:3px; }
.cot-meta     { display:flex; align-items:center; gap:8px; font:400 12px var(--body); color:var(--t3); flex-wrap:wrap; }
.cot-meta-sep { color:var(--border2); }
.cot-right    { display:flex; flex-direction:column; align-items:flex-end; gap:5px; flex-shrink:0; }
.cot-total    { font:700 15px var(--num); color:var(--text); }
.cot-bucket   { font:500 11px var(--body); color:var(--purple); background:var(--purple-bg); padding:2px 7px; border-radius:5px; }

/* Paginación */
.pagination   { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:20px; }
.pag-btn      { padding:7px 13px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 13px var(--body); color:var(--t2); cursor:pointer; text-decoration:none; transition:all .12s; }
.pag-btn:hover{ border-color:var(--g); color:var(--g); }
.pag-btn.active { background:var(--g); border-color:var(--g); color:#fff; }
.pag-btn.disabled { opacity:.4; pointer-events:none; }

/* Empty */
.empty-state  { text-align:center; padding:60px 20px; color:var(--t3); }
.empty-state svg { margin-bottom:16px; opacity:.3; }
.empty-title  { font:700 16px var(--body); color:var(--t2); margin-bottom:6px; }
.empty-sub    { font:400 13px var(--body); line-height:1.6; }

@media(max-width:640px) {
    .cot-icon   { display:none; }
    .cot-right  { min-width:80px; }
    .cot-total  { font-size:14px; }
}
</style>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <div>
        <h1 style="font:800 22px var(--body); letter-spacing:-.02em;">Cotizaciones</h1>
        <p style="font:400 13px var(--body); color:var(--t3); margin-top:3px;">
            <?= number_format($conteos['todas']) ?> en total
        </p>
    </div>
    <a href="/cotizaciones/nueva" class="btn btn-primary">
        <i data-feather="plus"></i> Nueva cotización
    </a>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="search-wrap">
        <i data-feather="search" style="width:15px;height:15px;" class="search-ico"></i>
        <input type="text"
               placeholder="Buscar por título, número o cliente..."
               value="<?= e($busqueda) ?>"
               id="search-input"
               oninput="debounceSearch(this.value)">
    </div>
    <select class="select" style="width:auto;padding:9px 12px;"
            onchange="filtrar('orden', this.value)">
        <option value="reciente"   <?= $orden === 'reciente'   ? 'selected' : '' ?>>Más reciente</option>
        <option value="antigua"    <?= $orden === 'antigua'    ? 'selected' : '' ?>>Más antigua</option>
        <option value="monto_desc" <?= $orden === 'monto_desc' ? 'selected' : '' ?>>Mayor monto</option>
        <option value="monto_asc"  <?= $orden === 'monto_asc'  ? 'selected' : '' ?>>Menor monto</option>
        <option value="cliente"    <?= $orden === 'cliente'    ? 'selected' : '' ?>>Cliente A–Z</option>
    </select>
</div>

<!-- Filtros por estado -->
<div class="filter-bar">
    <?php
    $estados_labels = [
        'todas'      => 'Todas',
        'borrador'   => 'Borrador',
        'enviada'    => 'Enviada',
        'vista'      => 'Vista',
        'aceptada'   => 'Aceptada',
        'rechazada'  => 'Rechazada',
        'vencida'    => 'Vencida',
        'convertida' => 'Convertida',
    ];
    foreach ($estados_labels as $key => $label):
        $cnt = $conteos[$key] ?? 0;
        if ($key !== 'todas' && $cnt === 0) continue;
        $qs = http_build_query(['estado' => $key, 'q' => $busqueda, 'orden' => $orden]);
    ?>
        <a href="/cotizaciones?<?= $qs ?>"
           class="chip <?= $estado === $key ? 'active' : '' ?>">
            <?= e($label) ?>
            <span class="chip-count"><?= $cnt ?></span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Resultados -->
<div class="results-bar">
    <span class="results-count">
        <?= number_format($pag['total']) ?> resultado<?= $pag['total'] !== 1 ? 's' : '' ?>
        <?= $busqueda ? ' para "' . e($busqueda) . '"' : '' ?>
    </span>
</div>

<!-- Lista -->
<?php if (empty($cotizaciones)): ?>
    <div class="empty-state">
        <i data-feather="file-text" style="width:48px;height:48px;"></i>
        <div class="empty-title">
            <?= $busqueda ? 'Sin resultados' : 'No hay cotizaciones' ?>
        </div>
        <div class="empty-sub">
            <?= $busqueda
                ? 'Intenta con otro término de búsqueda'
                : 'Crea tu primera cotización con el botón de arriba' ?>
        </div>
    </div>
<?php else: ?>
    <div class="cot-list">
        <?php foreach ($cotizaciones as $cot): ?>
            <a href="/cotizaciones/<?= (int)$cot['id'] ?>" class="cot-row">

                <div class="cot-icon">
                    <i data-feather="file-text" style="width:18px;height:18px;color:var(--t3)"></i>
                </div>

                <div class="cot-main">
                    <div class="cot-titulo"><?= e($cot['titulo']) ?></div>
                    <div class="cot-meta">
                        <span class="num" style="color:var(--t2);font-weight:600;"><?= e($cot['numero']) ?></span>
                        <?php if ($cot['cliente_nombre']): ?>
                            <span class="cot-meta-sep">·</span>
                            <span><?= e($cot['cliente_nombre']) ?></span>
                        <?php endif; ?>
                        <?php if ($cot['asesor_nombre'] && Auth::puede('ver_todas_cots')): ?>
                            <span class="cot-meta-sep">·</span>
                            <span><?= e($cot['asesor_nombre']) ?></span>
                        <?php endif; ?>
                        <span class="cot-meta-sep">·</span>
                        <span><?= fecha_humana($cot['created_at']) ?></span>
                        <?php if ($cot['valida_hasta'] && in_array($cot['estado'], ['enviada','vista'])): ?>
                            <span class="cot-meta-sep">·</span>
                            <span style="color:<?= strtotime($cot['valida_hasta']) < time() ? 'var(--danger)' : 'var(--t3)' ?>">
                                Vence <?= fecha_humana($cot['valida_hasta']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="cot-right">
                    <span class="cot-total num"><?= format_money($cot['total'], $empresa['moneda']) ?></span>
                    <?= estado_badge($cot['estado']) ?>
                    <?php if ($cot['radar_bucket']): ?>
                        <span class="cot-bucket"><?= e($cot['radar_bucket']) ?></span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($pag['total_pags'] > 1): ?>
        <div class="pagination">
            <?php
            $qs_base = http_build_query(['estado' => $estado, 'q' => $busqueda, 'orden' => $orden]);
            ?>
            <a href="/cotizaciones?<?= $qs_base ?>&p=<?= $pag['pagina'] - 1 ?>"
               class="pag-btn <?= !$pag['hay_prev'] ? 'disabled' : '' ?>">
                ← Anterior
            </a>

            <?php for ($i = max(1, $pag['pagina'] - 2); $i <= min($pag['total_pags'], $pag['pagina'] + 2); $i++): ?>
                <a href="/cotizaciones?<?= $qs_base ?>&p=<?= $i ?>"
                   class="pag-btn <?= $i === $pag['pagina'] ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <a href="/cotizaciones?<?= $qs_base ?>&p=<?= $pag['pagina'] + 1 ?>"
               class="pag-btn <?= !$pag['hay_next'] ? 'disabled' : '' ?>">
                Siguiente →
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
let _searchTimer = null;
function debounceSearch(val) {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => filtrar('q', val), 350);
}

function filtrar(key, val) {
    const params = new URLSearchParams(window.location.search);
    params.set(key, val);
    if (key !== 'p') params.delete('p');
    window.location.href = '/cotizaciones?' + params.toString();
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
