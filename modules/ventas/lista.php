<?php
// ============================================================
//  CotizaApp — modules/ventas/lista.php
//  GET /ventas
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();
$usuario    = Auth::usuario();

// ─── Parámetros ──────────────────────────────────────────
$tab      = in_array($_GET['tab'] ?? '', ['ventas','recibos']) ? $_GET['tab'] : 'ventas';
$estado   = $_GET['estado'] ?? 'todas';
$busqueda = trim($_GET['q'] ?? '');
$orden    = $_GET['orden']  ?? 'reciente';
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = 25;

$estados_validos = ['todas','pendiente','parcial','pagada','entregada','cancelada'];
if (!in_array($estado, $estados_validos)) $estado = 'todas';

// ─── Restricción de asesor ───────────────────────────────
$solo_propias = !Auth::puede('ver_todas_ventas');

// ─── Conteos por estado ──────────────────────────────────
$cnt_params = [$empresa_id];
$cnt_where  = ["v.empresa_id = ?"];
if ($solo_propias) { $cnt_where[] = "v.usuario_id = ?"; $cnt_params[] = Auth::id(); }

$conteos_raw = DB::query(
    "SELECT estado, COUNT(*) n FROM ventas v WHERE " . implode(' AND ', $cnt_where) . " GROUP BY estado",
    $cnt_params
);
$conteos = ['todas' => 0];
foreach ($conteos_raw as $r) {
    $conteos[$r['estado']] = (int)$r['n'];
    $conteos['todas'] += (int)$r['n'];
}

// ─── Query ventas ────────────────────────────────────────
$where  = ["v.empresa_id = ?"];
$params = [$empresa_id];

if ($estado !== 'todas') { $where[] = "v.estado = ?"; $params[] = $estado; }
if ($solo_propias)       { $where[] = "v.usuario_id = ?"; $params[] = Auth::id(); }
if ($busqueda !== '') {
    $where[]  = "(v.titulo LIKE ? OR v.numero LIKE ? OR cl.nombre LIKE ?)";
    $like     = '%' . $busqueda . '%';
    $params   = array_merge($params, [$like, $like, $like]);
}

$where_sql = implode(' AND ', $where);
$order_sql = match($orden) {
    'antigua'    => 'v.created_at ASC',
    'monto_desc' => 'v.total DESC',
    'monto_asc'  => 'v.total ASC',
    'cliente'    => 'cl.nombre ASC',
    default      => 'v.created_at DESC',
};

$total_rows = (int) DB::val(
    "SELECT COUNT(*) FROM ventas v LEFT JOIN clientes cl ON cl.id = v.cliente_id WHERE $where_sql",
    $params
);
$pag = paginar($total_rows, $pagina, $por_pag);

$ventas = DB::query(
    "SELECT v.id, v.numero, v.titulo, v.slug, v.estado,
            v.total, v.pagado, v.saldo, v.created_at,
            cl.nombre AS cliente_nombre, cl.telefono AS cliente_telefono,
            u.nombre  AS asesor_nombre,
            c.numero  AS cot_numero, c.id AS cot_id
     FROM ventas v
     LEFT JOIN clientes cl  ON cl.id = v.cliente_id
     LEFT JOIN usuarios u   ON u.id  = v.usuario_id
     LEFT JOIN cotizaciones c ON c.id = v.cotizacion_id
     WHERE $where_sql ORDER BY $order_sql LIMIT ? OFFSET ?",
    array_merge($params, [$por_pag, $pag['offset']])
);

// ─── Query recibos (tab recibos) ─────────────────────────
$recibos = [];
if ($tab === 'recibos') {
    $rw = ["r.empresa_id = ?"];
    $rp = [$empresa_id];
    if ($solo_propias) { $rw[] = "v.usuario_id = ?"; $rp[] = Auth::id(); }
    if ($busqueda !== '') {
        $rw[]  = "(r.numero LIKE ? OR cl.nombre LIKE ? OR v.numero LIKE ?)";
        $like  = '%' . $busqueda . '%';
        $rp    = array_merge($rp, [$like, $like, $like]);
    }
    $recibos = DB::query(
        "SELECT r.id, r.numero, r.tipo, r.forma_pago, r.monto, r.concepto,
                r.cancelado, r.cancelado_por_id, r.created_at,
                v.numero AS venta_numero, v.id AS venta_id, v.titulo AS venta_titulo,
                cl.nombre AS cliente_nombre
         FROM recibos r
         JOIN ventas v ON v.id = r.venta_id
         LEFT JOIN clientes cl ON cl.id = v.cliente_id
         WHERE " . implode(' AND ', $rw) . "
         ORDER BY r.created_at DESC LIMIT 50",
        $rp
    );
}

// ─── Helpers ─────────────────────────────────────────────
function status_badge_venta(string $estado): string {
    $map = [
        'pendiente' => ['s-pendiente', 'Pendiente'],
        'parcial'   => ['s-parcial',   'Parcial'],
        'pagada'    => ['s-pagada',    'Pagada'],
        'entregada' => ['s-entregada', 'Entregada'],
        'cancelada' => ['s-cancelada', 'Cancelada'],
    ];
    [$cls, $lbl] = $map[$estado] ?? ['s-pendiente', ucfirst($estado)];
    return "<span class=\"status $cls\"><span class=\"status-dot\"></span>$lbl</span>";
}

function icono_forma(string $forma): string {
    return match($forma) {
        'efectivo'      => '💵',
        'transferencia' => '🏦',
        'tarjeta'       => '💳',
        default         => '💰',
    };
}

$page_title = 'Ventas';
ob_start();
?>
<style>
/* NAV TABS */
.vtabs-wrap { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); margin-bottom:16px; }
.vtabs      { display:flex; border-bottom:1px solid var(--border); }
.vtab       { flex:1; padding:13px 16px; font:600 13px var(--body); color:var(--t3); cursor:pointer; border-bottom:2.5px solid transparent; text-align:center; text-decoration:none; transition:all .15s; }
.vtab:hover { color:var(--t2); }
.vtab.active{ color:var(--g); border-bottom-color:var(--g); }

/* FILTROS */
.filter-bar { display:flex; gap:6px; overflow-x:auto; padding-bottom:4px; margin-bottom:12px; scrollbar-width:none; }
.filter-bar::-webkit-scrollbar { display:none; }
.chip       { padding:7px 13px; border-radius:20px; border:1px solid var(--border); background:var(--white); font:600 12px var(--body); color:var(--t2); cursor:pointer; white-space:nowrap; flex-shrink:0; text-decoration:none; transition:all .12s; display:inline-flex; align-items:center; gap:5px; }
.chip:hover  { border-color:var(--g); color:var(--g); }
.chip.active { background:var(--g); border-color:var(--g); color:#fff; }
.chip-count  { font:700 10px var(--body); background:rgba(0,0,0,.1); padding:1px 5px; border-radius:10px; }
.chip.active .chip-count { background:rgba(255,255,255,.25); }

/* TABLA VENTAS */
.vtbl-header { display:grid; grid-template-columns:1fr 160px 120px 100px 100px 90px 80px; gap:0; padding:8px 16px; font:700 11px var(--body); letter-spacing:.05em; text-transform:uppercase; color:var(--t3); border-bottom:1px solid var(--border); background:var(--bg); }
.venta-row   { display:grid; grid-template-columns:1fr 160px 120px 100px 100px 90px 80px; gap:0; align-items:center; padding:13px 16px; border-bottom:1px solid var(--border); cursor:pointer; transition:background .1s; text-decoration:none; color:inherit; }
.venta-row:last-child { border-bottom:none; }
.venta-row:hover      { background:var(--bg); }

.venta-num   { font:600 12px var(--num); color:var(--t3); margin-bottom:2px; }
.venta-title { font:600 14px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.venta-sub   { font:400 12px var(--body); color:var(--t3); margin-top:2px; }
.progress-bar { height:4px; border-radius:2px; background:var(--border); overflow:hidden; margin-top:5px; }
.progress-fill{ height:100%; border-radius:2px; background:var(--g); }

.col-num  { font:500 13px var(--num); }
.col-saldo-ok   { font:600 13px var(--num); color:var(--g); }
.col-saldo-pend { font:600 13px var(--num); color:var(--amb); }

/* Mobile: solo col-info y col-right */
.col-cliente, .col-status, .col-total, .col-saldo, .col-prog, .col-accion { display:flex; align-items:center; }
.venta-r { display:none; }

/* STATUS BADGES */
.status      { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:6px; font:700 12px var(--body); }
.status-dot  { width:6px; height:6px; border-radius:3px; flex-shrink:0; }
.s-pendiente { background:var(--slate-bg); color:var(--slate); } .s-pendiente .status-dot { background:#94a3b8; }
.s-parcial   { background:var(--amb-bg);   color:var(--amb); }   .s-parcial .status-dot   { background:#f59e0b; }
.s-pagada    { background:var(--g-bg);     color:var(--g); }     .s-pagada .status-dot    { background:var(--g); }
.s-entregada { background:var(--blue-bg);  color:var(--blue); }  .s-entregada .status-dot { background:var(--blue); }
.s-cancelada { background:var(--danger-bg);color:var(--danger); } .s-cancelada .status-dot { background:var(--danger); }

.liga-btn { padding:5px 10px; border-radius:6px; border:1px solid var(--border); background:transparent; font:600 12px var(--body); color:var(--t2); cursor:pointer; transition:all .12s; white-space:nowrap; text-decoration:none; display:inline-block; }
.liga-btn:hover { border-color:var(--g); color:var(--g); background:var(--g-bg); }

/* TAB RECIBOS */
.recibo-row  { display:flex; align-items:center; gap:12px; padding:13px 16px; border-bottom:1px solid var(--border); text-decoration:none; color:inherit; transition:background .1s; }
.recibo-row:last-child { border-bottom:none; }
.recibo-row:hover      { background:var(--bg); }
.recibo-ico  { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.recibo-info { flex:1; min-width:0; }
.recibo-num  { font:600 12px var(--num); color:var(--t3); margin-bottom:2px; }
.recibo-title{ font:600 14px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.recibo-sub  { font:400 12px var(--body); color:var(--t3); margin-top:2px; }
.recibo-monto{ font:700 15px var(--num); flex-shrink:0; }
.badge-cancelado   { background:var(--danger-bg); color:var(--danger); padding:2px 7px; border-radius:5px; font:700 11px var(--body); }
.badge-cancelacion { background:var(--purple-bg); color:var(--purple); padding:2px 7px; border-radius:5px; font:700 11px var(--body); }

/* Paginación */
.pagination  { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:20px; }
.pag-btn     { padding:7px 13px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--white); font:600 13px var(--body); color:var(--t2); cursor:pointer; text-decoration:none; transition:all .12s; }
.pag-btn:hover  { border-color:var(--g); color:var(--g); }
.pag-btn.active { background:var(--g); border-color:var(--g); color:#fff; }
.pag-btn.disabled { opacity:.4; pointer-events:none; }

@media(max-width:760px) {
    .vtbl-header { display:none; }
    .venta-row   { grid-template-columns:1fr auto; }
    .col-cliente,.col-status,.col-total,.col-saldo,.col-prog,.col-accion { display:none; }
    .venta-r     { display:block; text-align:right; }
}
</style>

<!-- ENCABEZADO -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <div>
        <h1 style="font:800 22px var(--body); letter-spacing:-.02em;">Ventas</h1>
        <p style="font:400 13px var(--body); color:var(--t3); margin-top:3px;">
            <?= number_format($conteos['todas']) ?> en total
        </p>
    </div>
</div>

<!-- TABS -->
<div class="vtabs-wrap">
    <div class="vtabs">
        <?php
        $qs_base = http_build_query(['q' => $busqueda, 'estado' => $estado, 'orden' => $orden]);
        ?>
        <a href="/ventas?tab=ventas&<?= $qs_base ?>" class="vtab <?= $tab === 'ventas'  ? 'active' : '' ?>">Ventas</a>
        <a href="/ventas?tab=recibos&<?= $qs_base ?>" class="vtab <?= $tab === 'recibos' ? 'active' : '' ?>">Recibos</a>
    </div>

    <!-- TOOLBAR -->
    <div style="padding:12px 16px; display:flex; gap:8px; border-bottom:1px solid var(--border); flex-wrap:wrap;">
        <div style="flex:1; min-width:180px; position:relative;">
            <input type="text" id="search-input"
                   value="<?= e($busqueda) ?>"
                   placeholder="Buscar <?= $tab === 'recibos' ? 'recibo, cliente…' : 'venta, cliente…' ?>"
                   oninput="debounceSearch(this.value)"
                   style="width:100%; padding:9px 14px 9px 36px; border:1.5px solid var(--border); border-radius:var(--r-sm); font:400 14px var(--body); outline:none; background:var(--bg);">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--t3);font-size:14px;">&#128269;</span>
        </div>
        <?php if ($tab === 'ventas'): ?>
        <select class="select" style="padding:9px 12px; border-radius:var(--r-sm); border:1px solid var(--border); font:500 13px var(--body); color:var(--t2); background:var(--white);"
                onchange="filtrar('orden', this.value)">
            <option value="reciente"   <?= $orden==='reciente'  ?'selected':''?>>Más reciente</option>
            <option value="antigua"    <?= $orden==='antigua'   ?'selected':''?>>Más antigua</option>
            <option value="monto_desc" <?= $orden==='monto_desc'?'selected':''?>>Mayor monto</option>
            <option value="monto_asc"  <?= $orden==='monto_asc' ?'selected':''?>>Menor monto</option>
            <option value="cliente"    <?= $orden==='cliente'   ?'selected':''?>>Cliente A–Z</option>
        </select>
        <?php endif; ?>
    </div>

    <!-- FILTROS ESTADO (solo tab ventas) -->
    <?php if ($tab === 'ventas'): ?>
    <div style="padding:10px 16px; border-bottom:1px solid var(--border);">
        <div class="filter-bar">
            <?php
            $estados_labels = [
                'todas'     => 'Todas',
                'pendiente' => 'Pendiente',
                'parcial'   => 'Parcial',
                'pagada'    => 'Pagada',
                'entregada' => 'Entregada',
                'cancelada' => 'Cancelada',
            ];
            foreach ($estados_labels as $key => $label):
                $cnt = $conteos[$key] ?? 0;
                if ($key !== 'todas' && $cnt === 0) continue;
                $qs = http_build_query(['tab' => 'ventas', 'estado' => $key, 'q' => $busqueda, 'orden' => $orden]);
            ?>
            <a href="/ventas?<?= $qs ?>" class="chip <?= $estado === $key ? 'active' : '' ?>">
                <?= e($label) ?> <span class="chip-count"><?= $cnt ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── TAB VENTAS ── -->
    <?php if ($tab === 'ventas'): ?>

        <?php if (empty($ventas)): ?>
        <div style="text-align:center; padding:60px 20px; color:var(--t3);">
            <div style="font:700 16px var(--body); color:var(--t2); margin-bottom:6px;">
                <?= $busqueda ? 'Sin resultados' : 'No hay ventas' ?>
            </div>
            <div style="font:400 13px var(--body);">
                <?= $busqueda ? 'Prueba otro término' : 'Las ventas se crean al convertir una cotización aceptada.' ?>
            </div>
        </div>
        <?php else: ?>

        <!-- Header tabla desktop -->
        <div class="vtbl-header">
            <span>Proyecto / Folio</span>
            <span>Cliente</span>
            <span>Estado</span>
            <span>Total</span>
            <span>Saldo</span>
            <span>Progreso</span>
            <span style="text-align:right">Enlace</span>
        </div>

        <?php foreach ($ventas as $v):
            $pct      = $v['total'] > 0 ? round(($v['pagado'] / $v['total']) * 100) : 0;
            $pagado_c = format_money($v['pagado'], $empresa['moneda']);
            $saldo_c  = format_money($v['saldo'],  $empresa['moneda']);
            $total_c  = format_money($v['total'],  $empresa['moneda']);
            $url_vta  = 'https://' . EMPRESA_SLUG . '.' . BASE_DOMAIN . '/v/' . $v['slug'];
        ?>
        <a href="/ventas/<?= (int)$v['id'] ?>" class="venta-row">
            <div>
                <div class="venta-num"><?= e($v['numero']) ?></div>
                <div class="venta-title"><?= e($v['titulo']) ?></div>
                <div class="venta-sub">
                    <?= e($v['cliente_nombre'] ?? '—') ?> · <?= fecha_humana($v['created_at']) ?>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
                <!-- mobile right -->
                <div class="venta-r">
                    <div style="font:700 14px var(--num);"><?= $total_c ?></div>
                    <?php if ($v['saldo'] <= 0): ?>
                        <div style="font:600 12px var(--body);color:var(--g);">Pagado</div>
                    <?php else: ?>
                        <div style="font:600 12px var(--body);color:var(--amb);"><?= $saldo_c ?> pendiente</div>
                    <?php endif; ?>
                    <div style="margin-top:4px;"><?= status_badge_venta($v['estado']) ?></div>
                </div>
            </div>
            <div class="col-cliente">
                <div>
                    <div style="font:600 13px var(--body);"><?= e($v['cliente_nombre'] ?? '—') ?></div>
                    <div style="font:400 12px var(--body);color:var(--t3);"><?= fecha_humana($v['created_at']) ?></div>
                </div>
            </div>
            <div class="col-status"><?= status_badge_venta($v['estado']) ?></div>
            <div class="col-total col-num"><?= $total_c ?></div>
            <div class="col-saldo">
                <?php if ($v['saldo'] <= 0): ?>
                    <span class="col-saldo-ok">Pagado</span>
                <?php else: ?>
                    <span class="col-saldo-pend"><?= $saldo_c ?></span>
                <?php endif; ?>
            </div>
            <div class="col-prog">
                <div style="font:700 12px var(--num);color:<?= $pct >= 100 ? 'var(--g)' : 'var(--t3)' ?>;margin-bottom:3px;"><?= $pct ?>%</div>
                <div class="progress-bar" style="width:70px;"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
            </div>
            <div class="col-accion" style="justify-content:flex-end;">
                <a href="<?= e($url_vta) ?>" target="_blank"
                   onclick="event.stopPropagation()"
                   class="liga-btn">Ver liga</a>
            </div>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>

    <!-- ── TAB RECIBOS ── -->
    <?php else: ?>

        <?php if (empty($recibos)): ?>
        <div style="text-align:center; padding:60px 20px; color:var(--t3);">
            <div style="font:700 16px var(--body); color:var(--t2); margin-bottom:6px;">Sin recibos</div>
            <div style="font:400 13px var(--body);">Los recibos se generan al registrar abonos.</div>
        </div>
        <?php else: ?>
        <?php foreach ($recibos as $r):
            $es_cancelacion = $r['tipo'] === 'cancelacion';
            $cancelado      = (bool)$r['cancelado'];
            $bg_ico = $cancelado ? 'var(--danger-bg)' : ($es_cancelacion ? 'var(--purple-bg)' : 'var(--g-light)');
        ?>
        <a href="/ventas/recibos/<?= (int)$r['id'] ?>" class="recibo-row">
            <div class="recibo-ico" style="background:<?= $bg_ico ?>;">
                <?= $cancelado ? '🧾' : ($es_cancelacion ? '↩️' : icono_forma($r['forma_pago'])) ?>
            </div>
            <div class="recibo-info">
                <div class="recibo-num"><?= e($r['numero']) ?> · <?= e($r['venta_numero']) ?></div>
                <div class="recibo-title" style="display:flex;align-items:center;gap:6px;">
                    <?= e($r['concepto'] ?: $r['venta_titulo']) ?>
                    <?php if ($cancelado):      ?><span class="badge-cancelado">Cancelado</span><?php endif; ?>
                    <?php if ($es_cancelacion): ?><span class="badge-cancelacion">Cancelación</span><?php endif; ?>
                </div>
                <div class="recibo-sub"><?= e($r['cliente_nombre'] ?? '—') ?> · <?= tiempo_relativo($r['created_at']) ?></div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div class="recibo-monto" style="color:<?= $cancelado ? 'var(--danger)' : ($es_cancelacion ? 'var(--purple)' : 'var(--g)') ?>;<?= $cancelado ? 'text-decoration:line-through;' : '' ?>">
                    <?= $es_cancelacion ? '-' : '' ?><?= format_money(abs($r['monto']), $empresa['moneda']) ?>
                </div>
                <div style="font:400 12px var(--body);color:var(--t3);margin-top:3px;">
                    <?= $es_cancelacion ? 'Auto-generado' : ucfirst(e($r['forma_pago'])) ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>

    <?php endif; ?>
</div><!-- /vtabs-wrap -->

<!-- Paginación (solo ventas) -->
<?php if ($tab === 'ventas' && $pag['total_pags'] > 1): ?>
<div class="pagination">
    <?php $qs_pag = http_build_query(['tab' => 'ventas', 'estado' => $estado, 'q' => $busqueda, 'orden' => $orden]); ?>
    <a href="/ventas?<?= $qs_pag ?>&p=<?= $pag['pagina'] - 1 ?>" class="pag-btn <?= !$pag['hay_prev'] ? 'disabled' : '' ?>">← Anterior</a>
    <?php for ($i = max(1,$pag['pagina']-2); $i <= min($pag['total_pags'],$pag['pagina']+2); $i++): ?>
        <a href="/ventas?<?= $qs_pag ?>&p=<?= $i ?>" class="pag-btn <?= $i === $pag['pagina'] ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="/ventas?<?= $qs_pag ?>&p=<?= $pag['pagina'] + 1 ?>" class="pag-btn <?= !$pag['hay_next'] ? 'disabled' : '' ?>">Siguiente →</a>
</div>
<?php endif; ?>

<script>
let _st = null;
function debounceSearch(v) { clearTimeout(_st); _st = setTimeout(() => filtrar('q', v), 350); }
function filtrar(k, v) {
    const p = new URLSearchParams(window.location.search);
    p.set(k, v); if (k !== 'p') p.delete('p');
    window.location.href = '/ventas?' + p.toString();
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
