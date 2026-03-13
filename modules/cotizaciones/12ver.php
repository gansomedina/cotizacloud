<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/ver.php
//  GET /cotizaciones/:id
// ============================================================

defined('COTIZAAPP') or die;

$empresa    = Auth::empresa();
$usuario    = Auth::usuario();
$empresa_id = EMPRESA_ID;

$cot_id = (int)($id ?? 0);
if (!$cot_id) redirect('/cotizaciones');

// ─── Cargar cotización ───────────────────────────────────
$cot = DB::row(
    "SELECT c.*, cl.nombre AS cliente_nombre, cl.telefono AS cliente_telefono, cl.email AS cliente_email
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.id = ? AND c.empresa_id = ?",
    [$cot_id, $empresa_id]
);

if (!$cot) {
    flash('error', 'Cotización no encontrada');
    redirect('/cotizaciones');
}

// Verificar permiso de acceso
if (!Auth::puede('ver_todas_cots') && (int)$cot['usuario_id'] !== (int)Auth::id()) {
    flash('error', 'No tienes acceso a esta cotización');
    redirect('/cotizaciones');
}

// ─── Líneas ──────────────────────────────────────────────
$lineas = DB::query(
    "SELECT * FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden ASC",
    [$cot_id]
);

// ─── Log / historial ─────────────────────────────────────
$log = DB::query(
    "SELECT l.*, u.nombre AS usuario_nombre
     FROM cotizacion_log l
     LEFT JOIN usuarios u ON u.id = l.usuario_id
     WHERE l.cotizacion_id = ?
     ORDER BY l.created_at DESC
     LIMIT 20",
    [$cot_id]
);

// ─── Visitas del cliente (sesiones radar) ────────────────
$visitas = DB::query(
    "SELECT s.created_at, s.ip, s.user_agent, s.es_interno,
            COALESCE(s.visible_ms, 0) AS visible_ms,
            COALESCE(s.scroll_max, 0) AS scroll_max
     FROM quote_sessions s
     WHERE s.cotizacion_id = ? AND s.es_interno = 0
     ORDER BY s.created_at DESC
     LIMIT 10",
    [$cot_id]
);

// ─── Catálogo, clientes, cupones ─────────────────────────
$articulos = DB::query(
    "SELECT id, sku, titulo, descripcion, precio FROM articulos
     WHERE empresa_id = ? AND activo = 1 ORDER BY orden ASC, titulo ASC",
    [$empresa_id]
);

$clientes = DB::query(
    "SELECT id, nombre, telefono, email FROM clientes
     WHERE empresa_id = ? ORDER BY nombre ASC",
    [$empresa_id]
);

$cupones = DB::query(
    "SELECT id, codigo, descripcion, porcentaje FROM cupones
     WHERE empresa_id = ? AND activo = 1 ORDER BY codigo ASC",
    [$empresa_id]
);

$puede_editar_precios = Auth::puede('editar_precios');
$puede_descuentos     = Auth::puede('aplicar_descuentos');

$es_editable = in_array($cot['estado'], ['borrador', 'enviada', 'vista']);

// JSON para JS
$articulos_js = json_encode(array_map(fn($a) => [
    'id' => (int)$a['id'], 'sku' => $a['sku'] ?? '',
    'titulo' => $a['titulo'], 'descripcion' => $a['descripcion'] ?? '',
    'precio' => (float)$a['precio'],
], $articulos));

$clientes_js = json_encode(array_map(fn($c) => [
    'id' => (int)$c['id'], 'nombre' => $c['nombre'],
    'telefono' => $c['telefono'], 'email' => $c['email'] ?? '',
], $clientes));

$lineas_js = json_encode(array_map(fn($l) => [
    'articulo_id'  => $l['articulo_id'] ? (int)$l['articulo_id'] : null,
    'sku'          => $l['sku'] ?? '',
    'titulo'       => $l['titulo'],
    'descripcion'  => $l['descripcion'] ?? '',
    'cantidad'     => (float)$l['cantidad'],
    'precio_unit'  => (float)$l['precio_unit'],
], $lineas));

$empresa_js = json_encode([
    'moneda'         => $empresa['moneda'],
    'impuesto_modo'  => $empresa['impuesto_modo'],
    'impuesto_pct'   => (float)$empresa['impuesto_pct'],
    'impuesto_label' => $empresa['impuesto_label'] ?? 'IVA',
    'descuento_auto_pct'  => (float)($empresa['descuento_auto_pct'] ?? 0),
    'descuento_auto_dias' => (int)($empresa['descuento_auto_dias'] ?? 3),
]);

$cot_js = json_encode([
    'id'                   => (int)$cot['id'],
    'cliente_id'           => $cot['cliente_id'] ? (int)$cot['cliente_id'] : null,
    'cliente_nombre'       => $cot['cliente_nombre'] ?? '',
    'cliente_telefono'     => $cot['cliente_telefono'] ?? '',
    'cupon_id'             => $cot['cupon_id'] ? (int)$cot['cupon_id'] : null,
    'cupon_codigo'         => $cot['cupon_codigo'] ?? '',
    'cupon_pct'            => (float)$cot['cupon_pct'],
    'descuento_auto_activo'=> (int)$cot['descuento_auto_activo'],
    'descuento_auto_pct'   => (float)$cot['descuento_auto_pct'],
    'descuento_auto_expira'=> $cot['descuento_auto_expira'] ?? null,
    'notas_cliente'        => $cot['notas_cliente'] ?? '',
    'notas_internas'       => $cot['notas_internas'] ?? '',
    'estado'               => $cot['estado'],
    'slug'                 => $cot['slug'],
]);

$url_publica = 'https://' . EMPRESA_SLUG . '.' . BASE_DOMAIN . '/c/' . $cot['slug'];

$page_title = e($cot['numero']) . ' — ' . e($cot['titulo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($cot['numero']) ?> — <?= e($empresa['nombre']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <?php
    // Estilos compartidos del builder
    $estilos_builder = __DIR__ . '/_builder_styles.php';
    if (file_exists($estilos_builder)) require $estilos_builder;
    ?>
    <style>
    /* Estilos extra para vista existente */
    .estado-bar { display:flex; align-items:center; gap:10px; padding:10px 18px; background:var(--bg); border-bottom:1px solid var(--border); font:400 13px var(--body); flex-wrap:wrap; }
    .estado-bar .badge { font-size:12px; padding:4px 10px; }
    .accion-btn { padding:8px 12px; border-radius:var(--r-sm); border:1px solid var(--border2); background:var(--white); font:600 13px var(--body); color:var(--t2); cursor:pointer; transition:all .12s; white-space:nowrap; text-decoration:none; display:inline-flex; align-items:center; }
    .accion-btn:hover { border-color:var(--g); color:var(--g); }
    .accion-btn.primary, .accion-btn-primary { background:var(--g); border-color:var(--g); color:#fff; }
    .accion-btn.primary:hover, .accion-btn-primary:hover { opacity:.88; }
    .accion-btn.danger  { border-color:var(--danger); color:var(--danger); }
    .log-row { display:flex; gap:10px; padding:8px 0; border-bottom:1px solid var(--border); font:400 12px var(--body); }
    .log-row:last-child { border-bottom:none; }
    .log-evento { font-weight:600; color:var(--text); flex-shrink:0; }
    .log-detalle { color:var(--t3); flex:1; }
    .log-ts { color:var(--t3); font:400 11px var(--num); flex-shrink:0; }

    /* Badge de estado inline */
    .badge { display:inline-flex; align-items:center; padding:3px 9px; border-radius:99px; font:600 12px var(--body); }
    .badge-slate  { background:var(--slate-bg);  color:var(--slate); }
    .badge-blue   { background:var(--blue-bg);   color:var(--blue); }
    .badge-amber  { background:var(--amb-bg);    color:var(--amb); }
    .badge-green  { background:var(--g-bg);      color:var(--g); }
    .badge-red    { background:var(--danger-bg); color:var(--danger); }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-l">
            <a href="/cotizaciones" class="back-btn" title="Regresar">&#8592;</a>
            <div>
                <div class="topbar-title"><?= e($cot['titulo']) ?></div>
                <span class="topbar-num"><?= e($cot['numero']) ?></span>
            </div>
        </div>
        <div class="topbar-actions">
            <?php if ($es_editable): ?>
                <button class="accion-btn accion-btn-primary" onclick="guardarCotizacion()" id="btn-guardar">Guardar</button>
            <?php endif; ?>
            <button class="accion-btn topbar-secondary" onclick="navigator.clipboard.writeText('<?= e($url_publica) ?>');this.textContent='✓';setTimeout(()=>this.textContent='Copiar',2000)">Copiar</button>
            <a href="<?= e($url_publica) ?>" target="_blank" class="accion-btn topbar-secondary">Ver</a>
            <?php if ($cot['estado'] === 'borrador' && (Auth::es_admin() || (int)$cot['usuario_id'] === (int)Auth::id())): ?>
                <button class="accion-btn topbar-secondary" style="color:var(--danger);border-color:var(--danger)" onclick="eliminarCotizacion()">Eliminar</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BARRA DE ESTADO -->
<div class="estado-bar">
    <?php
    $badge_map = [
        'borrador'   => 'badge-slate', 'enviada' => 'badge-blue',
        'vista'      => 'badge-amber', 'aceptada' => 'badge-green',
        'rechazada'  => 'badge-red',   'vencida' => 'badge-red',
        'convertida' => 'badge-green',
    ];
    $badge_class = $badge_map[$cot['estado']] ?? 'badge-slate';
    ?>
    <span class="badge <?= $badge_class ?>"><?= ucfirst(e($cot['estado'])) ?></span>
    <?php if ($cot['cliente_nombre']): ?>
        <span style="color:var(--t3)">·</span>
        <span style="font-weight:600"><?= e($cot['cliente_nombre']) ?></span>
    <?php endif; ?>
    <span style="color:var(--t3)">·</span>
    <span><?= fecha_humana($cot['created_at']) ?></span>
    <?php if ($cot['vista_at']): ?>
        <span style="color:var(--t3)">· Vista <?= tiempo_relativo($cot['vista_at']) ?></span>
    <?php endif; ?>
    <?php if ($cot['radar_bucket']): ?>
        <span style="background:var(--purple-bg);color:var(--purple);padding:2px 8px;border-radius:5px;font:600 11px var(--body);">
            <?= e($cot['radar_bucket']) ?>
        </span>
    <?php endif; ?>
    <?php if (!$es_editable): ?>
        <span style="margin-left:auto;font:400 12px var(--body);color:var(--t3)">Solo lectura</span>
    <?php endif; ?>
</div>

<?php
// A partir de aquí reutilizamos el mismo HTML del builder
// pero precargando los datos de la cotización existente
// En producción esto se extrae a _builder_body.php

// Por ahora mostramos el builder completo igual que nueva.php
// con los datos precargados via JS
?>

<div class="page-wrap">
<div class="page-layout">
    <div class="col-main">

        <div class="slabel">Cliente</div>
        <div class="card">
            <button class="client-btn" id="client-btn"
                    onclick="<?= $es_editable ? "openSheet('clientSheet','clientOverlay')" : 'void(0)' ?>">
                <div class="client-avatar <?= $cot['cliente_nombre'] ? '' : 'empty' ?>" id="client-avatar">
                    <?= $cot['cliente_nombre']
                        ? strtoupper(substr($cot['cliente_nombre'], 0, 1))
                        : '+' ?>
                </div>
                <div style="flex:1">
                    <div class="client-name" id="client-name" <?= !$cot['cliente_nombre'] ? 'style="color:var(--t3)"' : '' ?>>
                        <?= $cot['cliente_nombre'] ? e($cot['cliente_nombre']) : 'Sin cliente asignado' ?>
                    </div>
                    <div class="client-phone" id="client-phone"><?= e($cot['cliente_telefono'] ?? '') ?></div>
                </div>
                <?php if ($es_editable): ?>
                <i data-feather="chevron-right" style="width:16px;height:16px;" class="client-chevron"></i>
                <?php endif; ?>
            </button>
        </div>

        <div class="slabel">Proyecto</div>
        <div class="card">
            <div class="field">
                <div class="field-lbl">Título</div>
                <input type="text" id="cot-titulo" value="<?= e($cot['titulo']) ?>"
                       <?= !$es_editable ? 'readonly' : '' ?>>
            </div>
            <div style="display:flex">
                <div class="field" style="flex:1;border-bottom:none;border-right:1px solid var(--border)">
                    <div class="field-lbl">Fecha</div>
                    <input type="date" id="cot-fecha" value="<?= e(substr($cot['created_at'],0,10)) ?>"
                           <?= !$es_editable ? 'readonly' : '' ?>>
                </div>
                <div class="field" style="flex:1;border-bottom:none">
                    <div class="field-lbl">Vence</div>
<?php
    $vence_val = '';
    if (!empty($cot['valida_hasta']) && $cot['valida_hasta'] > '2000-01-01') {
        $vence_val = $cot['valida_hasta'];
    } elseif ($es_editable) {
        // Sin fecha válida → calcular desde configuración de empresa
        $vigencia_dias = (int)($empresa['cot_vigencia_dias'] ?? 30);
        $vence_val = date('Y-m-d', strtotime("+{$vigencia_dias} days"));
    }
?>
                    <input type="date" id="cot-vence" value="<?= e($vence_val) ?>"
                           <?= !$es_editable ? 'readonly' : '' ?>>
                </div>
            </div>
        </div>

        <div class="slabel">Artículos</div>
        <div class="items-list" id="items-list"></div>

        <?php if ($es_editable): ?>
        <button class="add-item-btn" onclick="openSheet('catalogSheet','catalogOverlay')">
            <span style="font-size:18px">+</span> Agregar artículo
        </button>
        <?php endif; ?>

        <!-- PANEL MÓVIL (igual que nueva.php, omitido por brevedad — se comparte) -->

    </div><!-- /col-main -->

    <!-- PANEL DERECHO -->
    <div class="col-panel">

        <?php if (!empty($cupones) && $puede_descuentos && $es_editable): ?>
        <div class="panel-section">
            <div class="panel-lbl">Cupones</div>
            <?php foreach ($cupones as $cup): ?>
            <div class="panel-coupon <?= ((int)$cup['id'] === (int)$cot['cupon_id']) ? 'checked' : '' ?>"
                 data-cupon-id="<?= (int)$cup['id'] ?>"
                 data-cupon-codigo="<?= e($cup['codigo']) ?>"
                 data-cupon-pct="<?= (float)$cup['porcentaje'] ?>"
                 onclick="toggleCupon(this)">
                <div class="panel-check">✓</div>
                <div style="flex:1;min-width:0">
                    <div class="panel-coupon-code"><?= e($cup['codigo']) ?></div>
                    <?php if ($cup['descripcion']): ?>
                    <div class="panel-coupon-desc"><?= e($cup['descripcion']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="panel-coupon-pct">-<?= number_format($cup['porcentaje'],1) ?>%</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="panel-section">
            <div class="panel-lbl">Totales</div>
            <div class="panel-t-row"><span class="panel-t-lbl">Subtotal</span><span class="panel-t-val" id="total-subtotal"><?= format_money($cot['subtotal'], $empresa['moneda']) ?></span></div>
            <?php if ($cot['cupon_monto'] > 0): ?>
            <div class="panel-t-row disc" id="row-cupon">
                <span class="panel-t-lbl" id="lbl-cupon">Cupón <?= e($cot['cupon_codigo']) ?></span>
                <span class="panel-t-val" id="total-cupon">-<?= format_money($cot['cupon_monto'], $empresa['moneda']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($cot['descuento_auto_activo'] && $cot['descuento_auto_amt'] > 0): ?>
            <div class="panel-t-row disc">
                <span class="panel-t-lbl">Descuento <?= number_format($cot['descuento_auto_pct'],1) ?>%</span>
                <span class="panel-t-val">-<?= format_money($cot['descuento_auto_amt'], $empresa['moneda']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($empresa['impuesto_modo'] !== 'ninguno'): ?>
            <div class="panel-t-row">
                <span class="panel-t-lbl"><?= e($empresa['impuesto_label'] ?? 'IVA') ?></span>
                <span class="panel-t-val" id="total-impuesto"><?= format_money($cot['impuesto_amt'], $empresa['moneda']) ?></span>
            </div>
            <?php endif; ?>
            <div class="panel-t-row final">
                <span class="panel-t-lbl">Total</span>
                <span class="panel-t-val" id="total-final"><?= format_money($cot['total'], $empresa['moneda']) ?></span>
            </div>
        </div>

        <div class="panel-section">
            <div class="panel-lbl">Notas para el cliente</div>
            <div class="panel-notes">
                <textarea id="notas-cliente-desk" <?= !$es_editable ? 'readonly' : '' ?>
                          placeholder="Visible para el cliente..."><?= e($cot['notas_cliente'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="panel-section">
            <div class="panel-lbl">Notas internas</div>
            <div class="panel-notes">
                <textarea id="notas-internas-desk" <?= !$es_editable ? 'readonly' : '' ?>
                          placeholder="Solo visible para el asesor..."><?= e($cot['notas_internas'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Historial de visitas -->
        <div class="panel-section">
            <div class="panel-lbl">Historial de visitas</div>
            <?php if (empty($visitas)): ?>
                <div class="visit-empty">Sin visitas aún.</div>
            <?php else: ?>
                <?php foreach ($visitas as $v):
                    $ua_short = preg_match('/iPhone|iPad/i', $v['user_agent']) ? 'iPhone'
                              : (preg_match('/Android/i', $v['user_agent']) ? 'Android' : 'Desktop');
                    $dur = $v['visible_ms'] > 0 ? round($v['visible_ms'] / 1000) . 's' : '—';
                ?>
                <div class="visit-row">
                    <div class="visit-dot" style="background:var(--g)"></div>
                    <div style="flex:1">
                        <div class="visit-time"><?= tiempo_relativo($v['created_at']) ?></div>
                        <div class="visit-detail"><?= e($ua_short) ?> · <?= e($v['ip']) ?></div>
                    </div>
                    <div class="visit-dur"><?= e($dur) ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Log interno -->
        <div class="panel-section">
            <div class="panel-lbl">Historial de cambios</div>
            <?php foreach ($log as $entry): ?>
            <div class="log-row">
                <span class="log-evento"><?= e(ucfirst($entry['evento'])) ?></span>
                <span class="log-detalle"><?= e($entry['usuario_nombre'] ?? 'Cliente') ?></span>
                <span class="log-ts"><?= tiempo_relativo($entry['created_at']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($es_editable): ?>
        <div class="panel-section">
            <button class="btn-guardar" onclick="guardarCotizacion(false)" id="btn-guardar">
                Guardar cambios
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Overlay URL -->
<div id="url-overlay" style="position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .25s;display:flex;align-items:flex-end;justify-content:center"
     onclick="closeUrlOverlay()">
    <div onclick="event.stopPropagation()" style="background:var(--white);border-radius:20px 20px 0 0;padding:20px 20px 40px;width:100%;max-width:560px;">
        <div style="width:34px;height:4px;border-radius:2px;background:var(--border2);margin:0 auto 18px"></div>
        <div style="font:800 19px var(--body);margin-bottom:4px">URL del cliente</div>
        <div style="font:400 13px var(--body);color:var(--t3);margin-bottom:16px">Comparte este enlace con el cliente</div>
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-sm);padding:12px 14px;display:flex;align-items:center;gap:8px;margin-bottom:14px">
            <span style="flex:1;font:500 12px var(--num);color:var(--g);word-break:break-all"><?= e($url_publica) ?></span>
            <button onclick="navigator.clipboard.writeText('<?= e($url_publica) ?>');this.textContent='¡Copiado!';setTimeout(()=>this.textContent='Copiar',2000)"
                    style="padding:8px 13px;border-radius:7px;border:none;background:var(--g);font:700 12px var(--body);color:#fff;cursor:pointer;flex-shrink:0">Copiar</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
            <a href="https://wa.me/?text=<?= urlencode($url_publica) ?>" target="_blank"
               style="padding:14px;border-radius:var(--r-sm);border:1px solid #a8e6a3;background:#dcf8c6;display:flex;flex-direction:column;align-items:center;gap:5px;text-decoration:none;cursor:pointer">
                <span style="font-size:24px">💬</span>
                <span style="font:700 12px var(--body);color:var(--t2)">WhatsApp</span>
            </a>
            <a href="mailto:<?= e($cot['cliente_email'] ?? '') ?>?subject=Tu+cotización&body=<?= urlencode($url_publica) ?>"
               style="padding:14px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--bg);display:flex;flex-direction:column;align-items:center;gap:5px;text-decoration:none;cursor:pointer">
                <span style="font-size:24px">✉️</span>
                <span style="font:700 12px var(--body);color:var(--t2)">Correo</span>
            </a>
        </div>
        <?php if ($es_editable): ?>
        <button onclick="enviarCotizacion();closeUrlOverlay()"
                style="width:100%;padding:13px;border-radius:var(--r-sm);border:1px solid var(--border);background:transparent;font:600 14px var(--body);color:var(--t2);cursor:pointer;margin-top:8px">
            Marcar como enviada
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Sheets de catálogo y cliente (iguales que nueva.php) -->
<div class="sh-overlay" id="catalogOverlay" onclick="closeSheet('catalogSheet','catalogOverlay')"></div>
<div class="bottom-sheet" id="catalogSheet">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <span class="sh-title">Agregar artículo</span>
        <button class="sh-close" onclick="closeSheet('catalogSheet','catalogOverlay')">✕</button>
    </div>
    <div class="sh-search">
        <div class="sh-search-wrap">
            <input type="text" placeholder="Buscar en catálogo..." id="catalog-search" oninput="filtrarCatalogo(this.value)">
        </div>
    </div>
    <button onclick="agregarItemVacio()" style="margin:0 16px 10px;width:calc(100% - 32px);padding:12px 14px;border-radius:var(--r-sm);border:1.5px dashed var(--border2);background:transparent;display:flex;align-items:center;gap:8px;font:600 14px var(--body);color:var(--t2);cursor:pointer;">
        <span>+</span> Ítem libre
    </button>
    <div class="sh-list" id="catalog-list"></div>
</div>

<div class="sh-overlay" id="clientOverlay" onclick="closeSheet('clientSheet','clientOverlay')"></div>
<div class="bottom-sheet" id="clientSheet">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <span class="sh-title">Cliente</span>
        <button class="sh-close" onclick="closeSheet('clientSheet','clientOverlay')">✕</button>
    </div>
    <div class="sh-list" id="client-list" style="padding-top:8px"></div>
</div>

<script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
<script>
const ARTICULOS   = <?= $articulos_js ?>;
const CLIENTES    = <?= $clientes_js ?>;
const LINEAS_INIT = <?= $lineas_js ?>;
const EMPRESA_CFG = <?= $empresa_js ?>;
const COT         = <?= $cot_js ?>;
const CSRF_TOKEN  = '<?= csrf_token() ?>';
const COT_ID      = <?= $cot_id ?>;
const ES_EDITABLE = <?= $es_editable ? 'true' : 'false' ?>;
const PUEDE_PRECIOS    = <?= $puede_editar_precios ? 'true' : 'false' ?>;
const PUEDE_DESCUENTOS = <?= $puede_descuentos ? 'true' : 'false' ?>;
const URL_PUBLICA = '<?= e($url_publica) ?>';

let clienteSeleccionado = COT.cliente_id
    ? { id: COT.cliente_id, nombre: COT.cliente_nombre, telefono: COT.cliente_telefono }
    : null;

let cuponSeleccionado = COT.cupon_id
    ? { id: COT.cupon_id, codigo: COT.cupon_codigo, pct: COT.cupon_pct }
    : null;

let descAutoActivo = COT.descuento_auto_activo === 1;
let descAutoPct    = COT.descuento_auto_pct;
let descAutoDias   = 3;
let itemCounter    = 0;

document.addEventListener('DOMContentLoaded', () => {
    // feather-icons es CDN externo — si no carga no debe romper el editor
    try { feather.replace(); } catch(e) {}

    // Cargar líneas iniciales
    LINEAS_INIT.forEach(l => {
        agregarItem(l.titulo, l.sku, l.descripcion, l.precio_unit, l.articulo_id, ES_EDITABLE);
    });
    renderCatalogList('');
    renderClientList('');
    calcularTotales();

    // Overlay: asegurar pointer-events:none al inicio para no bloquear clicks
    const overlay = document.getElementById('url-overlay');
    overlay.style.opacity = '0';
    overlay.style.pointerEvents = 'none';
});

// ── Reutilizar las mismas funciones de nueva.php ──
// (En producción se extraen a /public/assets/js/builder.js)

function openSheet(s,o){
    document.getElementById(o).classList.add('open');
    document.getElementById(s).classList.add('open');
    document.body.style.overflow='hidden';
}
function closeSheet(s,o){
    document.getElementById(o).classList.remove('open');
    document.getElementById(s).classList.remove('open');
    document.body.style.overflow='';
}
function autoResize(el){el.style.height='auto';el.style.height=el.scrollHeight+'px';}
function toggleMob(hdr){hdr.closest('.mob-section').classList.toggle('open');}
function fmt(n){const sym=EMPRESA_CFG.moneda==='USD'?'USD ':'$';return sym+parseFloat(n||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2});}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function strip(html){const d=document.createElement('div');d.innerHTML=html;return d.textContent||'';}
function setText(id,val){const el=document.getElementById(id);if(el)el.textContent=val;}

function renderCatalogList(filtro){
    const q=filtro.toLowerCase();
    const el=document.getElementById('catalog-list');
    const lista=ARTICULOS.filter(a=>!q||a.titulo.toLowerCase().includes(q)||(a.sku&&a.sku.toLowerCase().includes(q)));
    if(!lista.length){el.innerHTML='<div style="text-align:center;padding:24px;color:var(--t3);font-size:13px">Sin resultados</div>';return;}
    el.innerHTML=lista.map(a=>`<div class="sh-item" onclick="agregarDesde(${a.id})"><div style="flex:1"><div class="sh-item-title">${esc(a.titulo)}</div>${a.sku?`<div class="sh-item-sku">${esc(a.sku)}</div>`:''}</div><div class="sh-item-price">${fmt(a.precio)}</div></div>`).join('');
}
function filtrarCatalogo(v){renderCatalogList(v);}
function agregarDesde(id){const a=ARTICULOS.find(x=>x.id===id);if(!a)return;agregarItem(a.titulo,a.sku||'',a.descripcion||'',a.precio,id,true);closeSheet('catalogSheet','catalogOverlay');}
function agregarItemVacio(){agregarItem('','','',0,null,true);closeSheet('catalogSheet','catalogOverlay');}

function renderClientList(filtro){
    const q=filtro.toLowerCase();
    const el=document.getElementById('client-list');
    const lista=CLIENTES.filter(c=>!q||c.nombre.toLowerCase().includes(q)||c.telefono.includes(q));
    if(!lista.length){el.innerHTML='<div style="text-align:center;padding:24px;color:var(--t3);font-size:13px">Sin clientes</div>';return;}
    el.innerHTML=lista.map(c=>`<div class="sh-client-item" onclick="seleccionarCliente(${c.id})"><div class="sh-client-avatar">${esc(c.nombre.charAt(0).toUpperCase())}</div><div><div style="font:600 14px var(--body)">${esc(c.nombre)}</div><div style="font:400 12px var(--body);color:var(--t3)">${esc(c.telefono)}</div></div></div>`).join('');
}

function seleccionarCliente(id){
    const c=CLIENTES.find(x=>x.id===id);if(!c)return;
    clienteSeleccionado=c;
    const ini=c.nombre.split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase();
    document.getElementById('client-avatar').className='client-avatar';
    document.getElementById('client-avatar').textContent=ini;
    document.getElementById('client-name').style.color='';
    document.getElementById('client-name').textContent=c.nombre;
    document.getElementById('client-phone').textContent=c.telefono;
    closeSheet('clientSheet','clientOverlay');
}

function toggleCupon(el){
    if(el.classList.contains('checked')){el.classList.remove('checked');cuponSeleccionado=null;}
    else{document.querySelectorAll('.panel-coupon.checked').forEach(x=>x.classList.remove('checked'));el.classList.add('checked');cuponSeleccionado={id:parseInt(el.dataset.cuponId),codigo:el.dataset.cuponCodigo,pct:parseFloat(el.dataset.cuponPct)};}
    calcularTotales();
}

function agregarItem(titulo, sku, desc, precio, articulo_id, editable=true){
    itemCounter++;
    const id='item-'+itemCounter;
    const amt=titulo?fmt(precio):'$0.00';
    const ro=!editable||(!PUEDE_PRECIOS&&articulo_id)?'readonly style="color:var(--t3)"':'';
    const html=`<div class="item-card" data-articulo-id="${articulo_id||''}" id="${id}">
        <div class="item-header">
            <div class="item-num-wrap">
                ${editable?`<button class="item-arrow" onclick="moverItem(this,-1)">▲</button>`:''}
                <div class="item-num">?</div>
                ${editable?`<button class="item-arrow" onclick="moverItem(this,1)">▼</button>`:''}
            </div>
            <div class="item-title-prev">${esc(titulo)||'Sin nombre'}</div>
            <div class="item-amt-prev">${amt}</div>
            ${editable?`<button class="item-del" onclick="eliminarItem(this)">✕</button>`:''}
        </div>
        <div class="item-body">
            <div class="item-field"><div class="item-field-lbl">Nombre</div><input type="text" data-campo="titulo" value="${esc(titulo)}" ${!editable?'readonly':''} oninput="updateItemPreview(this)"></div>
            <div class="item-field"><div class="item-field-lbl">SKU</div><input type="text" data-campo="sku" value="${esc(sku)}" ${!editable?'readonly':''}></div>
            <div class="item-field"><div class="item-field-lbl">Descripción</div><textarea data-campo="descripcion" oninput="autoResize(this)" ${!editable?'readonly':''}>${esc(desc)}</textarea></div>
            <div class="item-nums">
                <div class="item-field"><div class="item-field-lbl">Cantidad</div><input type="number" data-campo="cantidad" value="1" min="0" step="any" ${!editable?'readonly':''} oninput="calcItemTotal(this)"></div>
                <div class="item-field"><div class="item-field-lbl">Precio unit.</div><input type="number" data-campo="precio" value="${precio}" min="0" step="any" ${ro} oninput="calcItemTotal(this)"></div>
                <div class="item-field item-total"><div class="item-field-lbl">Total</div><input type="text" data-campo="total" value="${amt}" readonly></div>
            </div>
        </div>
    </div>`;
    const list=document.getElementById('items-list');
    list.insertAdjacentHTML('beforeend',html);
    // Setear cantidad correcta
    const card=list.lastElementChild;
    const cantInput=card.querySelector('[data-campo=cantidad]');
    if(cantInput&&articulo_id)cantInput.value='1';
    card.querySelectorAll('textarea').forEach(t=>autoResize(t));
    renumerarItems();
    calcularTotales();
}

// Setear cantidad de las líneas cargadas
document.addEventListener('DOMContentLoaded',()=>{
    const cards=[...document.querySelectorAll('#items-list .item-card')];
    LINEAS_INIT.forEach((l,i)=>{
        const card=cards[i];if(!card)return;
        const ci=card.querySelector('[data-campo=cantidad]');
        const pi=card.querySelector('[data-campo=precio]');
        if(ci)ci.value=l.cantidad;
        if(pi)pi.value=l.precio_unit;
        const tot=card.querySelector('[data-campo=total]');
        const prev=card.querySelector('.item-amt-prev');
        const v=fmt(l.cantidad*l.precio_unit);
        if(tot)tot.value=v;
        if(prev)prev.textContent=v;
    });
    calcularTotales();
});

function eliminarItem(btn){btn.closest('.item-card').remove();renumerarItems();calcularTotales();}
function moverItem(btn,dir){const card=btn.closest('.item-card');const list=document.getElementById('items-list');const items=[...list.children];const idx=items.indexOf(card);const target=items[idx+dir];if(!target)return;dir===-1?list.insertBefore(card,target):list.insertBefore(target,card);renumerarItems();}
function renumerarItems(){document.querySelectorAll('#items-list .item-card').forEach((c,i)=>c.querySelector('.item-num').textContent=i+1);}
function updateItemPreview(input){input.closest('.item-card').querySelector('.item-title-prev').textContent=input.value||'Sin nombre';}
function calcItemTotal(input){const card=input.closest('.item-card');const cant=parseFloat(card.querySelector('[data-campo=cantidad]').value)||0;const precio=parseFloat(card.querySelector('[data-campo=precio]').value)||0;const t=cant*precio;card.querySelector('[data-campo=total]').value=fmt(t);card.querySelector('.item-amt-prev').textContent=fmt(t);calcularTotales();}

function calcularTotales(){
    let subtotal=0;
    document.querySelectorAll('#items-list .item-card').forEach(card=>{
        const cant=parseFloat(card.querySelector('[data-campo=cantidad]')?.value)||0;
        const precio=parseFloat(card.querySelector('[data-campo=precio]')?.value)||0;
        subtotal+=cant*precio;
    });
    let base=subtotal,cuponAmt=0,descAutoAmt=0;
    if(cuponSeleccionado){cuponAmt=subtotal*(cuponSeleccionado.pct/100);base-=cuponAmt;}
    if(descAutoActivo&&descAutoPct>0){descAutoAmt=base*(descAutoPct/100);base-=descAutoAmt;}
    let impAmt=0,total=base;
    const modo=EMPRESA_CFG.impuesto_modo,pct=EMPRESA_CFG.impuesto_pct/100;
    if(modo==='suma'){impAmt=base*pct;total=base+impAmt;}
    else if(modo==='incluido'){impAmt=base-(base/(1+pct));}
    setText('total-subtotal',fmt(subtotal));
    setText('total-final',fmt(total));
}

async function guardarCotizacion(preview){
    if(!ES_EDITABLE)return;
    const titulo=document.getElementById('cot-titulo').value.trim();
    if(!titulo){alert('El título es requerido');return;}
    const items=[];
    document.querySelectorAll('#items-list .item-card').forEach((card,i)=>{
        items.push({orden:i+1,articulo_id:card.dataset.articuloId||null,titulo:card.querySelector('[data-campo=titulo]')?.value||'',sku:card.querySelector('[data-campo=sku]')?.value||'',descripcion:card.querySelector('[data-campo=descripcion]')?.value||'',cantidad:parseFloat(card.querySelector('[data-campo=cantidad]')?.value)||1,precio_unit:parseFloat(card.querySelector('[data-campo=precio]')?.value)||0});
    });
    const btn=document.getElementById('btn-guardar');
    if(btn){btn.disabled=true;btn.textContent='Guardando...';}
    try{
        const r=await fetch('/cotizaciones/'+COT_ID,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({titulo,cliente_id:clienteSeleccionado?.id||null,valida_hasta:document.getElementById('cot-vence').value,cupon_id:cuponSeleccionado?.id||null,descuento_auto_activo:descAutoActivo?1:0,descuento_auto_pct:descAutoPct,notas_cliente:document.getElementById('notas-cliente-desk')?.value||'',notas_internas:document.getElementById('notas-internas-desk')?.value||'',items,preview})});
        const data=await r.json();
        if(!data.ok){alert(data.error||'Error al guardar');if(btn){btn.disabled=false;btn.textContent='Guardar cambios';}return;}
        if(btn){btn.textContent='¡Guardado!';setTimeout(()=>{btn.disabled=false;btn.textContent='Guardar cambios';},1800);}
    }catch(e){alert('Error de conexión');if(btn){btn.disabled=false;btn.textContent='Guardar cambios';}}
}

async function enviarCotizacion(){
    if(!confirm('¿Enviar cotización al cliente?'))return;
    const r=await fetch('/cotizaciones/'+COT_ID+'/enviar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({})});
    const data=await r.json();
    if(data.ok)window.location.reload();
    else alert(data.error||'Error al enviar');
}

async function convertirAVenta(){
    if(!confirm('¿Convertir esta cotización a venta?'))return;
    const r=await fetch('/cotizaciones/'+COT_ID+'/convertir',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({})});
    const data=await r.json();
    if(data.ok)window.location.href='/ventas/'+data.data.venta_id;
    else alert(data.error||'Error');
}

async function eliminarCotizacion(){
    if(!confirm('¿Eliminar esta cotización? Esta acción no se puede deshacer.'))return;
    try{
        const r=await fetch('/cotizaciones/'+COT_ID+'/eliminar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({})});
        const data=await r.json();
        if(data.ok)window.location.href='/cotizaciones';
        else alert(data.error||'Error al eliminar');
    }catch(e){alert('Error de conexión');}
}

// URL overlay
// Overlay: abrir/cerrar sin dejar pointer-events activos
(function(){
    const ov = document.getElementById('url-overlay');
    ov.style.cssText = 'position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);transition:opacity .25s;display:flex;align-items:flex-end;justify-content:center;opacity:0;pointer-events:none';
    ov.addEventListener('click', function(e){ if(e.target===this) closeUrlOverlay(); });
})();
function openUrlOverlay(){
    const ov = document.getElementById('url-overlay');
    ov.style.opacity = '1';
    ov.style.pointerEvents = 'all';
}
function closeUrlOverlay(){
    const ov = document.getElementById('url-overlay');
    ov.style.opacity = '0';
    ov.style.pointerEvents = 'none';
}
</script>

<!-- ══ BOTTOM NAV MOBILE ══════════════════════════════════ -->
<style>
#app-bottom-nav{display:none}
@media(max-width:768px){
  body.sheet-open #app-bottom-nav{display:none!important}
  #app-bottom-nav{
    display:flex;position:fixed;bottom:0;left:0;right:0;
    height:64px;background:#fff;border-top:1px solid #e2e2dc;
    z-index:600;box-shadow:0 -2px 12px rgba(0,0,0,.08);
    padding-bottom:env(safe-area-inset-bottom);
    -webkit-transform:translateZ(0);
    transform:translateZ(0);
    will-change:transform;
    -webkit-backface-visibility:hidden;
    backface-visibility:hidden;
  }
  .app-bn-item{
    flex:1;display:flex;flex-direction:column;align-items:center;
    justify-content:center;gap:3px;text-decoration:none;
    color:#6a6a64;font-size:10.5px;font-weight:500;
    padding:6px 4px;border:none;background:none;cursor:pointer;
    -webkit-tap-highlight-color:transparent;position:relative;
  }
  .app-bn-item svg{width:22px;height:22px;display:block;flex-shrink:0;stroke:currentColor;fill:none}
  .app-bn-item.active{color:#1a5c38}
  .app-bn-item.active svg{stroke:#1a5c38}
  .app-bn-item.active::before{
    content:'';position:absolute;top:0;left:50%;
    transform:translateX(-50%);width:32px;height:3px;
    background:#1a5c38;border-radius:0 0 3px 3px;
  }
  /* Espacio para que el contenido no quede tapado por la bottom nav */
  .page-layout{padding-bottom:0}

  /* Drawer "Más" */
  #app-more-drawer{
    display:none;position:fixed;bottom:64px;left:0;right:0;
    background:#fff;border-top:1px solid #e2e2dc;
    border-radius:12px 12px 0 0;z-index:85;
    box-shadow:0 -4px 24px rgba(0,0,0,.13);
    padding:8px 8px calc(8px + env(safe-area-inset-bottom));
  }
  #app-more-drawer.open{display:block;animation:drawerUp .22s cubic-bezier(.4,0,.2,1) both}
  @keyframes drawerUp{from{transform:translateY(100%)}to{transform:translateY(0)}}
  #app-more-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:82}
  #app-more-overlay.on{display:block}
  .app-more-handle{width:40px;height:4px;background:#c8c8c0;border-radius:2px;margin:0 auto 4px}
  .app-more-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;padding:8px 0}
  .app-more-item{
    display:flex;flex-direction:column;align-items:center;gap:6px;
    padding:14px 8px;border-radius:9px;text-decoration:none;
    color:#4a4a46;font-size:12px;font-weight:500;
    transition:background .12s,color .12s;
    -webkit-tap-highlight-color:transparent;
  }
  .app-more-item:hover,.app-more-item.active{background:#eef7f2;color:#1a5c38}
  .app-more-item svg{width:24px;height:24px}
  .app-more-item-logout{color:#c53030}
  .app-more-item-logout:hover{background:#fff5f5;color:#c53030}
}
</style>

<nav id="app-bottom-nav">
  <a href="/"             class="app-bn-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Inicio</a>
  <a href="/cotizaciones" class="app-bn-item active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>Cotizaciones</a>
  <a href="/ventas"       class="app-bn-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>Ventas</a>
  <a href="/radar"        class="app-bn-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Radar</a>
  <button class="app-bn-item" id="app-btn-more" onclick="appToggleMore()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Más
  </button>
</nav>

<div id="app-more-overlay" onclick="appCloseMore()"></div>
<div id="app-more-drawer">
  <div class="app-more-handle"></div>
  <div class="app-more-grid">
    <a href="/clientes"  class="app-more-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Clientes</a>
    <a href="/costos"    class="app-more-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>Costos</a>
    <a href="/reportes"  class="app-more-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Reportes</a>
    <a href="/config"    class="app-more-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>Configuración</a>
    <a href="/logout"    class="app-more-item app-more-item-logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Salir</a>
  </div>
</div>

<script>
function appOpenMore(){
  var d=document.getElementById('app-more-drawer');
  var o=document.getElementById('app-more-overlay');
  d.style.display='block';o.classList.add('on');
  d.offsetHeight;d.classList.add('open');
  try{feather.replace({'stroke-width':1.8});}catch(e){}
}
function appCloseMore(){
  var d=document.getElementById('app-more-drawer');
  var o=document.getElementById('app-more-overlay');
  d.classList.remove('open');o.classList.remove('on');
  setTimeout(function(){if(!d.classList.contains('open'))d.style.display='';},240);
}
function appToggleMore(){
  var d=document.getElementById('app-more-drawer');
  if(d.classList.contains('open'))appCloseMore();else appOpenMore();
}
(function(){
  var s=0,dr=document.getElementById('app-more-drawer');
  dr.addEventListener('touchstart',function(e){s=e.touches[0].clientY},{passive:true});
  dr.addEventListener('touchend',function(e){if(e.changedTouches[0].clientY-s>60)appCloseMore()},{passive:true});
})();
</script>
</body>
</html>
