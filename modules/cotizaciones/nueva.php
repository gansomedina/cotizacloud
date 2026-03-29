<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/nueva.php
//  GET /cotizaciones/nueva
// ============================================================

defined('COTIZAAPP') or die;

$empresa    = Auth::empresa();
$usuario    = Auth::usuario();
$empresa_id = EMPRESA_ID;

// ── Verificar permiso para crear cotizaciones ───────────
if (!Auth::es_admin() && !Auth::puede('crear_cotizaciones')) {
    redirect('/cotizaciones');
}

// ── Verificar límite plan Free ──────────────────────────
$trial = trial_info($empresa_id);
if ($trial['agotado']) {
    $page_title = 'Límite alcanzado';
    ob_start();
    ?>
    <div style="max-width:520px;margin:60px auto;text-align:center">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--amb-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--amb)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h2 style="font-size:20px;font-weight:800;margin:0 0 8px">Límite del plan Free alcanzado</h2>
        <p style="color:var(--t2);margin:0 0 16px;line-height:1.6">
            Has utilizado las <strong><?= TRIAL_LIMIT ?> cotizaciones</strong> incluidas en tu plan Free.
            Para seguir creando cotizaciones, activa tu plan Pro.
        </p>
        <div style="background:var(--amb-bg);border:1px solid #fcd34d;border-radius:var(--r);padding:16px;margin-bottom:24px;text-align:left">
            <div style="font-size:13px;color:var(--amb)">
                <strong>Cotizaciones usadas:</strong> <?= $trial['usadas'] ?> / <?= TRIAL_LIMIT ?>
            </div>
            <div style="background:#fde68a;border-radius:6px;height:8px;margin-top:8px;overflow:hidden">
                <div style="background:var(--amb);height:100%;width:100%;border-radius:6px"></div>
            </div>
        </div>
        <a href="/licencia" class="btn btn-primary" style="padding:12px 28px;font-size:14px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            Activar licencia
        </a>
        <a href="/cotizaciones" style="display:block;margin-top:12px;font-size:13px;color:var(--t3);text-decoration:none">Volver a cotizaciones</a>
    </div>
    <?php
    $content = ob_get_clean();
    require ROOT_PATH . '/core/layout.php';
    exit;
}

// Cargar catálogo de artículos activos
$articulos = DB::query(
    "SELECT id, sku, titulo, descripcion, precio
     FROM articulos
     WHERE empresa_id = ? AND activo = 1
     ORDER BY orden ASC, titulo ASC",
    [$empresa_id]
);

// Cargar clientes
$clientes = DB::query(
    "SELECT id, nombre, telefono, email FROM clientes
     WHERE empresa_id = ? ORDER BY nombre ASC",
    [$empresa_id]
);

// Cargar cupones activos
$cupones = DB::query(
    "SELECT id, codigo, descripcion, porcentaje
     FROM cupones WHERE empresa_id = ? AND activo = 1
     ORDER BY codigo ASC",
    [$empresa_id]
);

// Defaults de empresa para la cotización nueva
$vigencia_default = (int)($empresa['cot_vigencia_dias'] ?? 30);
$fecha_hoy        = date('Y-m-d');
$fecha_vence      = date('Y-m-d', strtotime("+{$vigencia_default} days"));

$puede_editar_precios    = Auth::puede('editar_precios');
$puede_descuentos        = Auth::puede('aplicar_descuentos');
$puede_asignar           = Auth::puede('asignar_cotizaciones');

// Lista de vendedores activos (para asignación)
$vendedores = [];
if ($puede_asignar) {
    $vendedores = DB::query(
        "SELECT id, nombre FROM usuarios WHERE empresa_id = ? AND activo = 1 ORDER BY nombre",
        [$empresa_id]
    );
}

// JSON para JS
$articulos_js = json_encode(array_map(fn($a) => [
    'id'          => (int)$a['id'],
    'sku'         => $a['sku'] ?? '',
    'titulo'      => $a['titulo'],
    'descripcion' => $a['descripcion'] ?? '',
    'precio'      => (float)$a['precio'],
], $articulos), JSON_HEX_TAG | JSON_HEX_APOS);

$clientes_js = json_encode(array_map(fn($c) => [
    'id'       => (int)$c['id'],
    'nombre'   => $c['nombre'],
    'telefono' => $c['telefono'],
    'email'    => $c['email'] ?? '',
], $clientes), JSON_HEX_TAG | JSON_HEX_APOS);

$empresa_js = json_encode([
    'moneda'        => $empresa['moneda'],
    'impuesto_modo' => $empresa['impuesto_modo'],
    'impuesto_pct'  => (float)$empresa['impuesto_pct'],
    'impuesto_label'=> $empresa['impuesto_label'] ?? 'IVA',
    'descuento_auto_pct'  => (float)($empresa['descuento_auto_pct'] ?? 0),
    'descuento_auto_dias' => (int)($empresa['descuento_auto_dias'] ?? 3),
], JSON_HEX_TAG);

$page_title = 'Nueva cotización';

// Este módulo tiene su propio layout (pantalla completa, sin sidebar)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Nueva cotización — <?= e($empresa['nombre']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --bg:#f4f4f0; --white:#fff; --border:#e2e2dc; --border2:#c8c8c0;
        --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
        --g:#1a5c38; --g-bg:#eef7f2; --g-border:#b8ddc8; --g-light:#e6f4ed;
        --amb:#92400e; --amb-bg:#fef3c7;
        --blue:#1d4ed8; --blue-bg:#dbeafe;
        --danger:#c53030; --danger-bg:#fff5f5;
        --purple:#6d28d9; --purple-bg:#ede9fe;
        --r:12px; --r-sm:9px;
        --sh:0 1px 3px rgba(0,0,0,.06);
        --sh-md:0 4px 16px rgba(0,0,0,.08);
        --body:'Plus Jakarta Sans',sans-serif;
        --num:'DM Sans',sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--body); background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; }

    /* TOPBAR */
    .topbar { position:sticky; top:0; z-index:100; background:var(--white); border-bottom:1px solid var(--border); height:54px; display:flex; align-items:center; padding:0 20px; }
    .topbar-inner { width:100%; max-width:1200px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; }
    .topbar-l { display:flex; align-items:center; gap:10px; }
    .back-btn { width:34px; height:34px; border-radius:8px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--t2); text-decoration:none; transition:all .12s; }
    .back-btn:hover { border-color:var(--g); color:var(--g); }
    .topbar-title { font:700 15px var(--body); }
    .topbar-num   { font:500 12px var(--num); color:var(--t3); margin-left:6px; }

    /* LAYOUT */
    .page-wrap   { max-width:1200px; margin:0 auto; padding:0 20px; }
    .page-layout { display:flex; gap:24px; padding:24px 0 80px; align-items:flex-start; }
    .col-main    { flex:1; min-width:0; }
    .col-panel   { width:300px; flex-shrink:0; background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; position:sticky; top:70px; box-shadow:var(--sh); }

    /* SECTION LABEL */
    .slabel { font:700 11px var(--body); letter-spacing:.06em; text-transform:uppercase; color:var(--t2); margin:24px 0 10px; display:flex; align-items:center; gap:10px; }
    .slabel::after { content:''; flex:1; height:1.5px; background:var(--border); }
    .slabel:first-child { margin-top:0; }

    /* CARD */
    .card { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }
    .field { padding:12px 15px; border-bottom:1px solid var(--border); display:flex; flex-direction:column; gap:4px; }
    .field:last-child { border-bottom:none; }
    .field-lbl { font:700 10px var(--body); letter-spacing:.08em; text-transform:uppercase; color:var(--t3); }
    .field input, .field textarea {
        background:transparent; border:none; outline:none;
        font:500 15px var(--body); color:var(--text); width:100%;
        resize:none; line-height:1.5; padding:0;
    }
    .field input::placeholder, .field textarea::placeholder { color:var(--t3); font-weight:400; }

    /* CLIENTE */
    .client-btn { width:100%; padding:14px 16px; background:transparent; border:none; display:flex; align-items:center; gap:12px; cursor:pointer; text-align:left; transition:background .1s; }
    .client-btn:hover { background:var(--bg); }
    .client-avatar { width:40px; height:40px; border-radius:10px; background:var(--g); display:flex; align-items:center; justify-content:center; font:700 15px var(--body); color:#fff; flex-shrink:0; }
    .client-avatar.empty { background:var(--bg); border:1.5px dashed var(--border2); color:var(--t3); font-size:20px; }
    .client-name  { font:600 14px var(--body); color:var(--text); }
    .client-phone { font:400 12px var(--body); color:var(--t3); margin-top:2px; }
    .client-chevron { color:var(--t3); margin-left:auto; }

    /* ITEMS */
    .items-list { display:flex; flex-direction:column; gap:8px; }
    .item-card  { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); }
    .item-header { padding:9px 12px; background:var(--bg); border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px; }
    .item-num-wrap { display:flex; align-items:center; gap:5px; flex-shrink:0; }
    .item-arrow { width:26px; height:26px; border:1px solid var(--border2); background:var(--white); border-radius:6px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--t2); font-size:11px; transition:all .1s; flex-shrink:0; }
    .item-arrow:hover { background:var(--g-bg); border-color:var(--g); color:var(--g); }
    .item-num   { min-width:22px; height:22px; border-radius:5px; background:var(--border); display:flex; align-items:center; justify-content:center; font:600 11px var(--num); color:var(--t2); padding:0 4px; }
    .item-title-prev { flex:1; font:600 13px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .item-amt-prev   { font:700 13px var(--num); color:var(--g); flex-shrink:0; }
    .item-del { width:26px; height:26px; border-radius:6px; border:none; background:transparent; display:flex; align-items:center; justify-content:center; color:var(--t3); cursor:pointer; font-size:14px; flex-shrink:0; transition:all .1s; }
    .item-del:hover { background:var(--danger-bg); color:var(--danger); }
    .item-field { padding:10px 13px; border-bottom:1px solid var(--border); display:flex; flex-direction:column; gap:3px; }
    .item-field:last-child { border-bottom:none; }
    .item-field-lbl { font:700 10px var(--body); letter-spacing:.08em; text-transform:uppercase; color:var(--t3); }
    .item-field input, .item-field textarea { background:transparent; border:none; outline:none; font:500 14px var(--body); color:var(--text); width:100%; resize:none; line-height:1.5; }
    .item-field input::placeholder, .item-field textarea::placeholder { color:var(--t3); font-weight:400; }
    .item-nums { display:grid; grid-template-columns:1fr 1fr 1fr; border-bottom:1px solid var(--border); }
    .item-nums .item-field { border-bottom:none; border-right:1px solid var(--border); }
    .item-nums .item-field:last-child { border-right:none; }
    .item-total input { color:var(--g) !important; font-weight:700 !important; font-family:var(--num) !important; }
    .item-field input[type=number] { font-family:var(--num); }
    <?php if (!$puede_editar_precios): ?>
    .item-field input[data-campo=precio] { pointer-events:none; color:var(--t3); }
    <?php endif; ?>

    .add-item-btn { width:100%; margin-top:8px; padding:14px; border-radius:var(--r); border:1.5px dashed var(--border2); background:transparent; display:flex; align-items:center; justify-content:center; gap:8px; font:600 14px var(--body); color:var(--t2); cursor:pointer; transition:all .15s; }
    .add-item-btn:hover { border-color:var(--g); color:var(--g); background:var(--g-bg); }

    /* PANEL DERECHO */
    .panel-section { padding:14px 16px; border-bottom:1px solid var(--border); }
    .panel-section:last-child { border-bottom:none; }
    .panel-lbl { font:700 10px var(--body); letter-spacing:.08em; text-transform:uppercase; color:var(--t3); margin-bottom:10px; }
    .panel-lbl-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
    .panel-lbl-row .panel-lbl { margin-bottom:0; }

    /* Toggle */
    .toggle-sm { position:relative; width:36px; height:20px; cursor:pointer; flex-shrink:0; display:inline-block; }
    .toggle-sm input { opacity:0; width:0; height:0; position:absolute; }
    .toggle-track { position:absolute; inset:0; border-radius:10px; background:var(--border2); transition:background .15s; }
    .toggle-thumb { position:absolute; top:3px; left:3px; width:14px; height:14px; border-radius:7px; background:#fff; transition:transform .15s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
    .toggle-sm input:checked ~ .toggle-track { background:var(--g); }
    .toggle-sm input:checked ~ .toggle-thumb { transform:translateX(16px); }

    /* Cupón */
    .panel-coupon { display:flex; align-items:center; gap:8px; padding:9px 10px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--bg); cursor:pointer; transition:all .15s; margin-bottom:6px; }
    .panel-coupon:last-child { margin-bottom:0; }
    .panel-coupon.checked { background:var(--g-bg); border-color:var(--g); }
    .panel-check { width:18px; height:18px; border-radius:5px; border:1.5px solid var(--border2); background:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:transparent; transition:all .15s; font-size:11px; }
    .panel-coupon.checked .panel-check { background:var(--g); border-color:var(--g); color:#fff; }
    .panel-coupon-code { font:600 12px var(--num); color:var(--text); }
    .panel-coupon-desc { font:400 11px var(--body); color:var(--t3); margin-top:1px; }
    .panel-coupon-pct  { font:700 13px var(--num); color:var(--g); flex-shrink:0; margin-left:auto; }

    /* Descuento auto */
    .panel-auto-fields { display:flex; flex-direction:column; gap:8px; margin-top:10px; }
    .panel-auto-row    { display:flex; align-items:center; gap:8px; }
    .panel-auto-lbl    { font:400 13px var(--body); color:var(--t2); flex:1; }
    .panel-auto-input  { width:64px; padding:7px 10px; background:var(--bg); border:1px solid var(--border2); border-radius:var(--r-sm); font:500 13px var(--num); color:var(--text); outline:none; text-align:right; }
    .panel-auto-input:focus { border-color:var(--g); }
    .panel-auto-sub    { font:400 11px var(--body); color:var(--t3); line-height:1.5; }

    /* Totales */
    .panel-t-row  { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid var(--border); }
    .panel-t-row:last-child { border-bottom:none; }
    .panel-t-lbl  { font:400 13px var(--body); color:var(--t2); }
    .panel-t-val  { font:500 14px var(--num); color:var(--text); }
    .panel-t-row.disc .panel-t-lbl, .panel-t-row.disc .panel-t-val { color:var(--amb); }
    .panel-t-row.final .panel-t-lbl { font:700 15px var(--body); color:var(--text); }
    .panel-t-row.final .panel-t-val { font:700 18px var(--num); color:var(--g); }

    /* Historial */
    .visit-row   { display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid var(--border); }
    .visit-row:last-child { border-bottom:none; }
    .visit-dot   { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
    .visit-time  { font:600 12px var(--body); color:var(--text); }
    .visit-detail{ font:400 11px var(--body); color:var(--t3); margin-top:1px; }
    .visit-dur   { font:500 11px var(--num); color:var(--t3); flex-shrink:0; }
    .visit-empty { text-align:center; padding:12px 0; color:var(--t3); font:400 12px var(--body); line-height:1.6; }

    /* Botón guardar */
    .btn-guardar { width:100%; padding:14px; border-radius:var(--r-sm); border:none; background:var(--g); font:700 15px var(--body); color:#fff; cursor:pointer; transition:opacity .15s; }
    .btn-guardar:hover    { opacity:.9; }
    .btn-guardar:disabled { opacity:.5; cursor:not-allowed; }

    /* Archivos */
    .panel-drop { border:1.5px dashed var(--border2); border-radius:var(--r-sm); padding:13px; text-align:center; cursor:pointer; transition:all .15s; }
    .panel-drop:hover { border-color:var(--g); background:var(--g-bg); }
    .panel-file-item { display:flex; align-items:center; gap:8px; padding:7px 10px; background:var(--bg); border:1px solid var(--border); border-radius:var(--r-sm); margin-top:6px; }
    .panel-file-name { flex:1; font:500 12px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .panel-file-size { font:400 10px var(--num); color:var(--t3); flex-shrink:0; }
    .panel-file-rm   { background:none; border:none; color:var(--t3); cursor:pointer; font-size:14px; flex-shrink:0; padding:0 2px; }
    .panel-file-rm:hover { color:var(--danger); }

    /* Notes */
    .panel-notes textarea { width:100%; background:var(--bg); border:1px solid var(--border); border-radius:var(--r-sm); padding:10px 12px; font:400 13px var(--body); color:var(--text); resize:none; outline:none; min-height:60px; line-height:1.5; }
    .panel-notes textarea:focus { border-color:var(--g); }
    .panel-notes textarea::placeholder { color:var(--t3); }

    /* SHEETS */
    .sh-overlay  { position:fixed; top:0; left:0; right:0; bottom:0; z-index:490; background:rgba(0,0,0,.5); opacity:0; pointer-events:none; transition:opacity .25s; display:none; }
    .sh-overlay.open { opacity:1; pointer-events:all; display:block; }
    .bottom-sheet { display:none; position:fixed; bottom:0; left:0; right:0; z-index:500; background:var(--white); border-radius:20px 20px 0 0; max-height:90vh; flex-direction:column; box-shadow:0 -8px 32px rgba(0,0,0,.1); max-width:720px; margin:0 auto; }
    .bottom-sheet.open { display:flex; animation:sheetUp .28s cubic-bezier(.32,0,.15,1); }
    @keyframes sheetUp { from{transform:translateY(100%)} to{transform:translateY(0)} }
    @media(max-width:768px){
        .sh-overlay   { bottom:64px; }
        .bottom-sheet { bottom:64px; border-radius:16px 16px 0 0; max-height:80vh; }
    }
    .sh-handle   { width:34px; height:4px; border-radius:2px; background:var(--border2); margin:12px auto 0; flex-shrink:0; }
    .sh-header   { padding:14px 18px 10px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; }
    .sh-title    { font:800 17px var(--body); }
    .sh-close    { width:30px; height:30px; border-radius:999px; border:none; background:var(--bg); cursor:pointer; color:var(--t2); display:flex; align-items:center; justify-content:center; font-size:15px; }
    .sh-search   { padding:0 16px 10px; flex-shrink:0; }
    .sh-search-wrap { display:flex; align-items:center; gap:8px; background:var(--bg); border:1px solid var(--border); border-radius:var(--r-sm); padding:10px 13px; }
    .sh-search-wrap input { flex:1; background:transparent; border:none; outline:none; font:400 15px var(--body); color:var(--text); }
    .sh-list     { overflow-y:auto; flex:1; padding:0 16px 32px; }
    .sh-item     { display:flex; align-items:center; gap:12px; padding:12px; border-radius:var(--r-sm); cursor:pointer; border:1px solid transparent; margin-bottom:6px; transition:all .12s; }
    .sh-item:hover { background:var(--g-bg); border-color:var(--g-border); }
    .sh-item-body { flex:1; }
    .sh-item-title { font:600 14px var(--body); margin-bottom:2px; }
    .sh-item-sku   { font:400 11px var(--num); color:var(--t3); }
    .sh-item-desc  { font:400 12px var(--body); color:var(--t3); margin-top:2px; }
    .sh-item-price { font:700 15px var(--num); color:var(--g); flex-shrink:0; }
    .sh-client-item { display:flex; align-items:center; gap:12px; padding:12px; border-radius:var(--r-sm); cursor:pointer; margin-bottom:6px; transition:background .12s; }
    .sh-client-item:hover { background:var(--g-bg); }
    .sh-client-avatar { width:40px; height:40px; border-radius:10px; background:var(--g); display:flex; align-items:center; justify-content:center; font:700 15px var(--body); color:#fff; flex-shrink:0; }
    .sh-tabs { display:flex; background:var(--bg); border:1px solid var(--border); border-radius:var(--r-sm); padding:3px; margin:0 16px 12px; flex-shrink:0; }
    .sh-tab  { flex:1; padding:8px; text-align:center; border-radius:7px; font:600 13px var(--body); color:var(--t2); cursor:pointer; border:none; background:transparent; transition:all .15s; }
    .sh-tab.active { background:var(--white); color:var(--g); box-shadow:0 1px 3px rgba(0,0,0,.08); }
    .nc-field { padding:0 16px 12px; flex-shrink:0; }
    .nc-lbl   { font:700 10px var(--body); letter-spacing:.08em; text-transform:uppercase; color:var(--t3); margin-bottom:6px; display:block; }
    .nc-input { width:100%; background:var(--bg); border:1.5px solid var(--border); border-radius:var(--r-sm); padding:10px 13px; font:400 15px var(--body); color:var(--text); outline:none; transition:border-color .15s; }
    .nc-input:focus { border-color:var(--g); }
    .nc-btn   { width:calc(100% - 32px); margin:0 16px 16px; padding:14px; background:var(--g); border:none; border-radius:var(--r-sm); font:700 15px var(--body); color:#fff; cursor:pointer; }

    /* PANEL MÓVIL */
    .mobile-panel { display:none; margin-top:8px; }
    .mob-section  { background:var(--white); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--sh); margin-bottom:8px; }
    .mob-sec-hdr  { padding:14px 16px; display:flex; align-items:center; justify-content:space-between; cursor:pointer; user-select:none; }
    .mob-sec-title { font:700 14px var(--body); color:var(--text); }
    .mob-sec-arrow { color:var(--t3); font-size:16px; transition:transform .2s; }
    .mob-sec-body  { display:none; border-top:1px solid var(--border); }
    .mob-section.open .mob-sec-arrow { transform:rotate(90deg); }
    .mob-section.open .mob-sec-body  { display:block; }
    .mob-sec-inner { padding:14px 16px; }

    /* Sticky bottom móvil */
    .sticky-bottom { position:fixed; bottom:0; left:0; right:0; z-index:50; background:var(--white); border-top:1px solid var(--border); padding:12px 20px 24px; display:none; box-shadow:0 -4px 16px rgba(0,0,0,.06); }
    .sticky-total-lbl { font:400 11px var(--body); color:var(--t3); }
    .sticky-total-val { font:700 20px var(--num); color:var(--text); }
    .btn-gen { width:100%; padding:13px; border-radius:var(--r-sm); border:none; background:var(--g); font:700 15px var(--body); color:#fff; cursor:pointer; margin-top:10px; }

    @media(max-width:820px) {
        .col-panel     { display:none; }
        .mobile-panel  { display:block; }
        .sticky-bottom { display:block !important; }
        .page-layout   { padding:16px 0 120px; }
        .page-wrap     { padding:0 14px; }
        .item-field input, .item-field textarea { font-size:16px; }
        .item-arrow { width:34px; height:34px; font-size:14px; }
    }
    @media(min-width:821px) {
        .sticky-bottom { display:none !important; }
    }
    </style>
</head>
<body>

<div id="app-main">
<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-l">
            <a href="/cotizaciones" class="back-btn" title="Volver">
                <i data-feather="arrow-left" style="width:16px;height:16px;"></i>
            </a>
            <div>
                <div class="topbar-title">Nueva cotización</div>
                <span class="topbar-num" id="cot-numero">—</span>
            </div>
        </div>
    </div>
</div>

<div class="page-wrap">
<div class="page-layout">

    <!-- COLUMNA PRINCIPAL -->
    <div class="col-main">

        <div class="slabel">Cliente</div>
        <div class="card">
            <button class="client-btn" onclick="openSheet('clientSheet','clientOverlay')" id="client-btn">
                <div class="client-avatar empty" id="client-avatar">+</div>
                <div style="flex:1;">
                    <div class="client-name" id="client-name" style="color:var(--t3)">Seleccionar cliente</div>
                    <div class="client-phone" id="client-phone"></div>
                </div>
                <i data-feather="chevron-right" style="width:16px;height:16px;" class="client-chevron"></i>
            </button>
        </div>

        <div class="slabel">Proyecto</div>
        <div class="card">
            <div class="field">
                <div class="field-lbl">Título <span style="color:var(--danger)">*</span></div>
                <input type="text" id="cot-titulo" placeholder="Titulo del Proyecto"
                       oninput="actualizarEstado()">
            </div>
            <div style="display:flex">
                <div class="field" style="flex:1;border-bottom:none;border-right:1px solid var(--border)">
                    <div class="field-lbl">Fecha</div>
                    <input type="date" id="cot-fecha" value="<?= $fecha_hoy ?>">
                </div>
                <div class="field" style="flex:1;border-bottom:none">
                    <div class="field-lbl">Vence</div>
                    <input type="date" id="cot-vence" value="<?= $fecha_vence ?>">
                </div>
            </div>
        </div>

        <div class="slabel">Artículos</div>
        <div class="items-list" id="items-list"></div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
        <button class="add-item-btn" style="flex:2;margin-top:0" onclick="abrirCatalogo(false)">
            <span style="font-size:18px">+</span> Agregar artículo
        </button>
        <?php if ($trial['es_business']): ?>
        <button class="add-item-btn" style="flex:1;margin-top:0;border-color:#d97706;color:#d97706" onclick="abrirCatalogo(true)">
            <span style="font-size:18px">+</span> Agregar extra
        </button>
        <?php endif; ?>
        </div>

        <!-- ADJUNTOS (disponible después de guardar) -->
        <div class="slabel">Archivos adjuntos</div>
        <div style="padding:20px;background:var(--bg);border:1.5px dashed var(--border2);border-radius:var(--r);text-align:center">
          <div style="font:500 13px var(--body);color:var(--t3)">Guarda la cotización primero para adjuntar archivos</div>
          <div style="font:400 12px var(--body);color:var(--t3);margin-top:4px">Máximo 3 archivos, 1 MB cada uno</div>
        </div>

        <!-- PANEL MÓVIL -->
        <div class="mobile-panel">
            <div class="slabel" style="margin-top:24px">Opciones</div>

            <?php if (!empty($cupones) && $puede_descuentos): ?>
            <div class="mob-section">
                <div class="mob-sec-hdr" onclick="toggleMob(this)">
                    <span class="mob-sec-title">Cupones</span>
                    <span class="mob-sec-arrow">›</span>
                </div>
                <div class="mob-sec-body">
                    <div class="mob-sec-inner" id="cupones-mob"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($puede_descuentos): ?>
            <div class="mob-section">
                <div class="mob-sec-hdr" onclick="toggleMob(this)">
                    <span class="mob-sec-title">Descuento automático</span>
                    <span class="mob-sec-arrow">›</span>
                </div>
                <div class="mob-sec-body">
                    <div class="mob-sec-inner" id="desc-auto-mob">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                            <span style="font:400 14px var(--body);color:var(--t2)">Activar descuento</span>
                            <label class="toggle-sm"><input type="checkbox" id="desc-auto-toggle-mob" onchange="syncToggle('mob')"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                        </div>
                        <div class="panel-auto-fields" id="desc-auto-fields-mob" style="display:none">
                            <div class="panel-auto-row"><span class="panel-auto-lbl">Porcentaje</span><input type="number" class="panel-auto-input" id="desc-pct-mob" value="0" min="0" max="100" oninput="syncDescAuto('mob')"><span style="font:400 12px var(--body);color:var(--t3)">%</span></div>
                            <div class="panel-auto-row"><span class="panel-auto-lbl">Expira en</span><input type="number" class="panel-auto-input" id="desc-dias-mob" value="3" min="1" oninput="syncDescAuto('mob')"><span style="font:400 12px var(--body);color:var(--t3)">días</span></div>
                            <div class="panel-auto-sub">El cliente ve el descuento con cronómetro.</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="mob-section">
                <div class="mob-sec-hdr" onclick="toggleMob(this)">
                    <span class="mob-sec-title">Notas para el cliente</span>
                    <span class="mob-sec-arrow">›</span>
                </div>
                <div class="mob-sec-body">
                    <div class="mob-sec-inner">
                        <div class="panel-notes"><textarea id="notas-cliente-mob" placeholder="Visible para el cliente..." oninput="autoResize(this);syncNotas('mob')"></textarea></div>
                    </div>
                </div>
            </div>

            <div class="mob-section">
                <div class="mob-sec-hdr" onclick="toggleMob(this)">
                    <span class="mob-sec-title">Notas internas</span>
                    <span class="mob-sec-arrow">›</span>
                </div>
                <div class="mob-sec-body">
                    <div class="mob-sec-inner">
                        <div class="panel-notes"><textarea id="notas-internas-mob" placeholder="Solo visible para el asesor..." oninput="autoResize(this);syncNotas('mob')"></textarea></div>
                    </div>
                </div>
            </div>

            <div class="mob-section">
                <div class="mob-sec-hdr" onclick="toggleMob(this)">
                    <span class="mob-sec-title">Historial de visitas</span>
                    <span class="mob-sec-arrow">›</span>
                </div>
                <div class="mob-sec-body">
                    <div class="mob-sec-inner">
                        <div class="visit-empty">Sin visitas aún<br>Se registran cuando el cliente abre la cotización</div>
                    </div>
                </div>
            </div>
        </div><!-- /mobile-panel -->

    </div><!-- /col-main -->

    <!-- PANEL DERECHO (desktop) -->
    <div class="col-panel">

        <?php if (!empty($cupones) && $puede_descuentos): ?>
        <div class="panel-section">
            <div class="panel-lbl">Cupones</div>
            <div id="cupones-desktop">
                <?php foreach ($cupones as $cup): ?>
                <div class="panel-coupon" data-cupon-id="<?= (int)$cup['id'] ?>"
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
        </div>
        <?php endif; ?>

        <?php if ($puede_descuentos): ?>
        <div class="panel-section">
            <div class="panel-lbl-row">
                <span class="panel-lbl">Descuento automático</span>
                <label class="toggle-sm">
                    <input type="checkbox" id="desc-auto-toggle-desk" onchange="syncToggle('desk')">
                    <div class="toggle-track"></div>
                    <div class="toggle-thumb"></div>
                </label>
            </div>
            <div class="panel-auto-fields" id="desc-auto-fields-desk" style="display:none">
                <div class="panel-auto-row">
                    <span class="panel-auto-lbl">Porcentaje</span>
                    <input type="number" class="panel-auto-input" id="desc-pct-desk" value="0" min="0" max="100" oninput="syncDescAuto('desk')">
                    <span style="font:400 12px var(--body);color:var(--t3)">%</span>
                </div>
                <div class="panel-auto-row">
                    <span class="panel-auto-lbl">Expira en</span>
                    <input type="number" class="panel-auto-input" id="desc-dias-desk" value="3" min="1" oninput="syncDescAuto('desk')">
                    <span style="font:400 12px var(--body);color:var(--t3)">días</span>
                </div>
                <div class="panel-auto-sub">El cliente ve el descuento con cronómetro, sin código.</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="panel-section">
            <div class="panel-lbl">Totales</div>
            <div class="panel-t-row">
                <span class="panel-t-lbl">Subtotal</span>
                <span class="panel-t-val" id="total-subtotal">$0.00</span>
            </div>
            <div class="panel-t-row disc" id="row-cupon" style="display:none">
                <span class="panel-t-lbl" id="lbl-cupon">Cupón</span>
                <span class="panel-t-val" id="total-cupon">-$0.00</span>
            </div>
            <div class="panel-t-row disc" id="row-desc-auto" style="display:none">
                <span class="panel-t-lbl">Descuento</span>
                <span class="panel-t-val" id="total-desc-auto">-$0.00</span>
            </div>
            <?php if ($empresa['impuesto_modo'] !== 'ninguno'): ?>
            <div class="panel-t-row" id="row-impuesto">
                <span class="panel-t-lbl"><?= e($empresa['impuesto_label'] ?? 'IVA') ?></span>
                <span class="panel-t-val" id="total-impuesto">$0.00</span>
            </div>
            <?php endif; ?>
            <div class="panel-t-row final">
                <span class="panel-t-lbl">Total</span>
                <span class="panel-t-val" id="total-final">$0.00</span>
            </div>
        </div>

        <div class="panel-section">
            <div class="panel-lbl">Notas para el cliente</div>
            <div class="panel-notes">
                <textarea id="notas-cliente-desk" rows="3"
                          placeholder="Visible para el cliente..."
                          oninput="autoResize(this);syncNotas('desk')"></textarea>
            </div>
        </div>

        <div class="panel-section">
            <div class="panel-lbl">Notas internas</div>
            <div class="panel-notes">
                <textarea id="notas-internas-desk" rows="2"
                          placeholder="Solo visible para el asesor..."
                          oninput="autoResize(this);syncNotas('desk')"></textarea>
            </div>
        </div>

        <?php if ($puede_asignar && count($vendedores) > 1): ?>
        <div class="panel-section">
            <div class="panel-lbl">Vendedor asignado</div>
            <select id="cot-vendedor" style="width:100%;border:none;background:transparent;font:400 14px var(--body);color:var(--text);padding:8px 0;outline:none;cursor:pointer">
                <?php foreach ($vendedores as $v): ?>
                <option value="<?= (int)$v['id'] ?>" <?= (int)$v['id'] === (int)Auth::id() ? 'selected' : '' ?>><?= e($v['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="panel-section">
            <div class="panel-lbl" style="margin-bottom:8px">Historial de visitas</div>
            <div class="visit-empty">Sin visitas aún.<br>Se registran cuando el cliente abre la cotización.</div>
        </div>

        <div class="panel-section">
            <button class="btn-guardar" onclick="guardarCotizacion()" id="btn-guardar">
                Generar cotización
            </button>
        </div>
    </div><!-- /col-panel -->

</div><!-- /page-layout -->
</div><!-- /page-wrap -->

<!-- STICKY BOTTOM MÓVIL -->
<div class="sticky-bottom">
    <div class="sticky-total-lbl">Total estimado</div>
    <div class="sticky-total-val" id="total-mob">$0.00</div>
    <button class="btn-gen" onclick="guardarCotizacion()">Generar cotización</button>
</div>

<!-- ══ SHEET: Catálogo ══ -->
<div class="sh-overlay" id="catalogOverlay" onclick="closeSheet('catalogSheet','catalogOverlay')"></div>
<div class="bottom-sheet" id="catalogSheet">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <span class="sh-title">Agregar artículo</span>
        <button class="sh-close" onclick="closeSheet('catalogSheet','catalogOverlay')">✕</button>
    </div>
    <div class="sh-search">
        <div class="sh-search-wrap">
            <i data-feather="search" style="width:15px;height:15px;color:var(--t3);flex-shrink:0"></i>
            <input type="text" placeholder="Buscar en catálogo..." id="catalog-search"
                   oninput="filtrarCatalogo(this.value)">
        </div>
    </div>
    <button style="margin:0 16px 10px;width:calc(100% - 32px);padding:12px 14px;border-radius:var(--r-sm);border:1.5px dashed var(--border2);background:transparent;display:flex;align-items:center;gap:8px;font:600 14px var(--body);color:var(--t2);cursor:pointer;flex-shrink:0;transition:all .15s;"
            onmouseover="this.style.borderColor='var(--g)';this.style.color='var(--g)'"
            onmouseout="this.style.borderColor='var(--border2)';this.style.color='var(--t2)'"
            onclick="agregarItemVacio()">
        <span style="font-size:18px">+</span> Ítem libre (sin catálogo)
    </button>
    <div class="sh-list" id="catalog-list"></div>
</div>

<!-- ══ SHEET: Cliente ══ -->
<div class="sh-overlay" id="clientOverlay" onclick="closeSheet('clientSheet','clientOverlay')"></div>
<div class="bottom-sheet" id="clientSheet">
    <div class="sh-handle"></div>
    <div class="sh-header">
        <span class="sh-title">Seleccionar cliente</span>
        <button class="sh-close" onclick="closeSheet('clientSheet','clientOverlay')">✕</button>
    </div>
    <div class="sh-tabs">
        <button class="sh-tab active" id="tab-existing" onclick="switchClientTab('existing')">Clientes</button>
        <button class="sh-tab" id="tab-new" onclick="switchClientTab('new')">Nuevo cliente</button>
    </div>
    <!-- Existente -->
    <div id="tab-existing-panel">
        <div class="sh-search">
            <div class="sh-search-wrap">
                <i data-feather="search" style="width:15px;height:15px;color:var(--t3);flex-shrink:0"></i>
                <input type="text" placeholder="Buscar cliente..." id="client-search"
                       oninput="filtrarClientes(this.value)">
            </div>
        </div>
        <div class="sh-list" id="client-list"></div>
    </div>
    <!-- Nuevo -->
    <div id="tab-new-panel" style="display:none;overflow-y:auto;flex:1;padding-bottom:20px;">
        <div class="nc-field">
            <label class="nc-lbl">Nombre <span style="color:var(--danger)">*</span></label>
            <input type="text" class="nc-input" id="nc-nombre" placeholder="Nombre completo">
        </div>
        <div class="nc-field">
            <label class="nc-lbl">Teléfono <span style="color:var(--danger)">*</span></label>
            <input type="tel" class="nc-input" id="nc-telefono" placeholder="662 123 4567">
        </div>
        <div class="nc-field">
            <label class="nc-lbl">Dirección (opcional)</label>
            <input type="text" class="nc-input" id="nc-direccion" placeholder="Calle, colonia, ciudad…">
        </div>
        <button class="nc-btn" onclick="crearClienteNuevo()">Agregar cliente</button>
    </div>
</div>

<script src="/assets/js/feather.min.js"></script>
<script>
// ─── Datos del servidor ─────────────────────────────────
const ARTICULOS   = <?= $articulos_js ?>;
const CLIENTES    = <?= $clientes_js ?>;
const EMPRESA_CFG = <?= $empresa_js ?>;
const CSRF_TOKEN  = '<?= csrf_token() ?>';
const URL_PUB_BASE = '<?= e(Router::url_publica('/c/')) ?>';
const PUEDE_PRECIOS    = <?= $puede_editar_precios ? 'true' : 'false' ?>;
const PUEDE_DESCUENTOS = <?= $puede_descuentos ? 'true' : 'false' ?>;
const CUPONES_DATA = <?= json_encode(array_map(fn($c) => [
    'id'          => (int)$c['id'],
    'codigo'      => $c['codigo'],
    'descripcion' => $c['descripcion'] ?? '',
    'pct'         => (float)$c['porcentaje'],
], $cupones)) ?>;

// ─── Estado global ──────────────────────────────────────
let clienteSeleccionado = null;
let cuponSeleccionado   = null; // {id, codigo, pct}
let descAutoActivo      = false;
let descAutoPct         = EMPRESA_CFG.descuento_auto_pct || 0;
let descAutoDias        = EMPRESA_CFG.descuento_auto_dias || 3;

// Renderizar cupones en mobile
(function() {
    const mob = document.getElementById('cupones-mob');
    if (!mob || !CUPONES_DATA.length) return;
    mob.innerHTML = CUPONES_DATA.map(c => `
        <div class="panel-coupon" data-cupon-id="${c.id}"
             data-cupon-codigo="${c.codigo}" data-cupon-pct="${c.pct}"
             onclick="toggleCupon(this)" style="margin-bottom:6px">
            <div style="font:700 13px var(--num)">${c.codigo}</div>
            ${c.descripcion ? `<div style="font:400 11px var(--body);color:var(--t3)">${c.descripcion}</div>` : ''}
            <div style="font:600 12px var(--num);color:var(--g);margin-top:2px">-${c.pct}%</div>
        </div>
    `).join('');
})();

// ─── Init ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    feather.replace();
    renderCatalogList('');
    renderClientList('');
    calcularTotales();

    // Defaults de descuento auto desde empresa
    if (descAutoPct > 0) {
        setVal('desc-pct-desk', descAutoPct);
        setVal('desc-pct-mob',  descAutoPct);
    }
    setVal('desc-dias-desk', descAutoDias);
    setVal('desc-dias-mob',  descAutoDias);
});

// ─── Sheets ─────────────────────────────────────────────
function openSheet(s, o) {
    document.getElementById(o).classList.add('open');
    document.getElementById(s).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeSheet(s, o) {
    document.getElementById(o).classList.remove('open');
    document.getElementById(s).classList.remove('open');
    document.body.style.overflow = '';
}

// ─── Catálogo ───────────────────────────────────────────
function renderCatalogList(filtro) {
    const q    = filtro.toLowerCase();
    const list = ARTICULOS.filter(a =>
        !q || a.titulo.toLowerCase().includes(q) ||
              (a.sku && a.sku.toLowerCase().includes(q))
    );
    const el = document.getElementById('catalog-list');
    if (!list.length) {
        el.innerHTML = '<div style="text-align:center;padding:24px;color:var(--t3);font-size:13px">Sin resultados</div>';
        return;
    }
    el.innerHTML = list.map(a => `
        <div class="sh-item" onclick="agregarDesde(${a.id})">
            <div style="flex:1;">
                <div class="sh-item-title">${esc(a.titulo)}</div>
                ${a.sku ? `<div class="sh-item-sku">${esc(a.sku)}</div>` : ''}
                ${a.descripcion ? `<div class="sh-item-desc">${esc(strip(a.descripcion))}</div>` : ''}
            </div>
            <div class="sh-item-price">${fmt(a.precio)}</div>
        </div>
    `).join('');
}

function filtrarCatalogo(val) { renderCatalogList(val); }

let _agregandoExtra = false;
function abrirCatalogo(esExtra){ _agregandoExtra = esExtra; openSheet('catalogSheet','catalogOverlay'); }

function agregarDesde(id) {
    const a = ARTICULOS.find(x => x.id === id);
    if (!a) return;
    const pre = _agregandoExtra ? 'EXTRA: ' : '';
    agregarItem(pre + a.titulo, a.sku || '', a.descripcion || '', a.precio, id, _agregandoExtra);
    closeSheet('catalogSheet', 'catalogOverlay');
    _agregandoExtra = false;
}

function agregarItemVacio() {
    const pre = _agregandoExtra ? 'EXTRA: ' : '';
    agregarItem(pre, '', '', 0, null, _agregandoExtra);
    closeSheet('catalogSheet', 'catalogOverlay');
    _agregandoExtra = false;
    setTimeout(() => {
        const items = document.querySelectorAll('#items-list .item-card');
        const last  = items[items.length - 1];
        if (last) last.querySelector('input[data-campo=titulo]').focus();
    }, 100);
}

// ─── Items ──────────────────────────────────────────────
let itemCounter = 0;

function agregarItem(titulo, sku, desc, precio, articulo_id, esExtra=false) {
    itemCounter++;
    const id  = 'item-' + itemCounter;
    const amt = titulo ? fmt(precio) : '$0.00';
    const html = `
    <div class="item-card" data-articulo-id="${articulo_id || ''}" data-es-extra="${esExtra?1:0}" id="${id}">
        <div class="item-header">
            <div class="item-num-wrap">
                <button class="item-arrow" onclick="moverItem(this,-1)" title="Subir">▲</button>
                <div class="item-num">?</div>
                <button class="item-arrow" onclick="moverItem(this,1)" title="Bajar">▼</button>
            </div>
            <div class="item-title-prev">${esc(titulo) || 'Sin nombre'}</div>
            <div class="item-amt-prev">${amt}</div>
            <button class="item-del" onclick="eliminarItem(this)" title="Eliminar">✕</button>
        </div>
        <div class="item-body">
            <div class="item-field">
                <div class="item-field-lbl">Nombre</div>
                <input type="text" data-campo="titulo" value="${esc(titulo)}"
                       placeholder="Nombre del artículo"
                       oninput="updateItemPreview(this)">
            </div>
            <div class="item-field">
                <div class="item-field-lbl">SKU (opcional)</div>
                <input type="text" data-campo="sku" value="${esc(sku)}" placeholder="Ej. EST-01">
            </div>
            <div class="item-field">
                <div class="item-field-lbl">Descripción (opcional)</div>
                <textarea data-campo="descripcion" oninput="autoResize(this)">${esc(desc)}</textarea>
            </div>
            <div class="item-nums">
                <div class="item-field">
                    <div class="item-field-lbl">Cantidad</div>
                    <input type="number" data-campo="cantidad" value="1" min="0" step="any" oninput="calcItemTotal(this)">
                </div>
                <div class="item-field">
                    <div class="item-field-lbl">Precio unit.</div>
                    <input type="number" data-campo="precio" value="${precio}" min="0" step="any"
                           oninput="calcItemTotal(this)"
                           ${!PUEDE_PRECIOS ? 'readonly style="color:var(--t3)"' : ''}>
                </div>
                <div class="item-field item-total">
                    <div class="item-field-lbl">Total</div>
                    <input type="text" data-campo="total" value="${amt}" readonly>
                </div>
            </div>
        </div>
    </div>`;

    const list = document.getElementById('items-list');
    list.insertAdjacentHTML('beforeend', html);
    list.lastElementChild.querySelectorAll('textarea').forEach(t => autoResize(t));
    renumerarItems();
    calcularTotales();
    list.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function eliminarItem(btn) {
    btn.closest('.item-card').remove();
    renumerarItems();
    calcularTotales();
}

function moverItem(btn, dir) {
    const card  = btn.closest('.item-card');
    const list  = document.getElementById('items-list');
    const items = [...list.children];
    const idx   = items.indexOf(card);
    const target = items[idx + dir];
    if (!target) return;
    dir === -1 ? list.insertBefore(card, target) : list.insertBefore(target, card);
    renumerarItems();
}

function renumerarItems() {
    document.querySelectorAll('#items-list .item-card').forEach((c, i) => {
        c.querySelector('.item-num').textContent = i + 1;
    });
}

function updateItemPreview(input) {
    const card = input.closest('.item-card');
    card.querySelector('.item-title-prev').textContent = input.value || 'Sin nombre';
}

function calcItemTotal(input) {
    const card  = input.closest('.item-card');
    const cant  = parseFloat(card.querySelector('[data-campo=cantidad]').value) || 0;
    const precio = parseFloat(card.querySelector('[data-campo=precio]').value)   || 0;
    const total = cant * precio;
    card.querySelector('[data-campo=total]').value   = fmt(total);
    card.querySelector('.item-amt-prev').textContent = fmt(total);
    calcularTotales();
}

// ─── Cálculo de totales ─────────────────────────────────
function calcularTotales() {
    // Subtotal de items
    let subtotal = 0;
    document.querySelectorAll('#items-list .item-card').forEach(card => {
        const cant  = parseFloat(card.querySelector('[data-campo=cantidad]')?.value) || 0;
        const precio = parseFloat(card.querySelector('[data-campo=precio]')?.value)  || 0;
        subtotal += cant * precio;
    });

    // Descuentos
    let baseImpuesto = subtotal;

    // Cupón
    let cuponAmt = 0;
    if (cuponSeleccionado) {
        cuponAmt    = subtotal * (cuponSeleccionado.pct / 100);
        baseImpuesto -= cuponAmt;
        document.getElementById('row-cupon').style.display = '';
        document.getElementById('lbl-cupon').textContent   = 'Cupón ' + cuponSeleccionado.codigo;
        document.getElementById('total-cupon').textContent = '-' + fmt(cuponAmt);
    } else {
        document.getElementById('row-cupon').style.display = 'none';
    }

    // Descuento auto
    let descAutoAmt = 0;
    const rowDescAuto = document.getElementById('row-desc-auto');
    if (descAutoActivo && descAutoPct > 0) {
        descAutoAmt  = baseImpuesto * (descAutoPct / 100);
        baseImpuesto -= descAutoAmt;
        rowDescAuto.style.display = '';
        document.getElementById('total-desc-auto').textContent = '-' + fmt(descAutoAmt);
    } else {
        rowDescAuto.style.display = 'none';
    }

    // Impuesto
    let impuestoAmt = 0;
    const modo = EMPRESA_CFG.impuesto_modo;
    const pct  = EMPRESA_CFG.impuesto_pct / 100;
    if (modo === 'suma') {
        impuestoAmt = baseImpuesto * pct;
    } else if (modo === 'incluido') {
        impuestoAmt = baseImpuesto - (baseImpuesto / (1 + pct));
    }

    const total = modo === 'suma'
        ? baseImpuesto + impuestoAmt
        : baseImpuesto; // incluido o ninguno

    // Actualizar DOM
    setText('total-subtotal', fmt(subtotal));
    if (document.getElementById('total-impuesto')) {
        setText('total-impuesto', (modo === 'incluido' ? '' : '+') + fmt(impuestoAmt));
    }
    setText('total-final', fmt(total));
    setText('total-mob',   fmt(total));
}

// ─── Cliente ────────────────────────────────────────────
function renderClientList(filtro) {
    const q  = filtro.toLowerCase();
    const el = document.getElementById('client-list');
    const lista = CLIENTES.filter(c =>
        !q || c.nombre.toLowerCase().includes(q) ||
              c.telefono.includes(q)
    );
    if (!lista.length) {
        el.innerHTML = '<div style="text-align:center;padding:24px;color:var(--t3);font-size:13px">Sin clientes</div>';
        return;
    }
    el.innerHTML = lista.map(c => `
        <div class="sh-client-item" onclick="seleccionarCliente(${c.id})">
            <div class="sh-client-avatar">${esc(c.nombre.charAt(0).toUpperCase())}</div>
            <div>
                <div style="font:600 14px var(--body)">${esc(c.nombre)}</div>
                <div style="font:400 12px var(--body);color:var(--t3)">${esc(c.telefono)}</div>
            </div>
        </div>
    `).join('');
}

function filtrarClientes(val) { renderClientList(val); }

function seleccionarCliente(id) {
    const c = CLIENTES.find(x => x.id === id);
    if (!c) return;
    clienteSeleccionado = c;

    const iniciales = c.nombre.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
    const avatar    = document.getElementById('client-avatar');
    avatar.className    = 'client-avatar';
    avatar.textContent  = iniciales;

    document.getElementById('client-name').style.color = '';
    document.getElementById('client-name').textContent  = c.nombre;
    document.getElementById('client-phone').textContent = c.telefono;

    closeSheet('clientSheet', 'clientOverlay');
}

function switchClientTab(tab) {
    document.getElementById('tab-existing-panel').style.display = tab === 'existing' ? '' : 'none';
    document.getElementById('tab-new-panel').style.display      = tab === 'new'      ? '' : 'none';
    document.getElementById('tab-existing').classList.toggle('active', tab === 'existing');
    document.getElementById('tab-new').classList.toggle('active', tab === 'new');
}

async function crearClienteNuevo() {
    const nombre    = document.getElementById('nc-nombre').value.trim();
    const telefono  = document.getElementById('nc-telefono').value.trim();
    const direccion = document.getElementById('nc-direccion').value.trim();

    if (!nombre)   { alert('El nombre es requerido'); return; }
    if (!telefono) { alert('El teléfono es requerido'); return; }

    try {
        const r = await fetch('/clientes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ nombre, telefono, direccion })
        });
        const data = await r.json();
        if (!data.ok) { alert(data.error || 'Error al crear cliente'); return; }

        const c = { id: data.data.id, nombre, telefono, direccion };
        CLIENTES.unshift(c);
        seleccionarCliente(c.id);
        document.getElementById('nc-nombre').value    = '';
        document.getElementById('nc-telefono').value  = '';
        document.getElementById('nc-direccion').value = '';
    } catch (e) {
        alert('Error de conexión');
    }
}

// ─── Cupones ────────────────────────────────────────────
function toggleCupon(el) {
    const id     = parseInt(el.dataset.cuponId);
    const codigo = el.dataset.cuponCodigo;
    const pct    = parseFloat(el.dataset.cuponPct);

    if (el.classList.contains('checked')) {
        // Deseleccionar
        el.classList.remove('checked');
        cuponSeleccionado = null;
    } else {
        // Deseleccionar todos
        document.querySelectorAll('.panel-coupon.checked').forEach(x => x.classList.remove('checked'));
        el.classList.add('checked');
        cuponSeleccionado = { id, codigo, pct };
    }
    calcularTotales();
}

// ─── Descuento automático ───────────────────────────────
function syncToggle(from) {
    const srcId  = 'desc-auto-toggle-' + from;
    const dstId  = 'desc-auto-toggle-' + (from === 'desk' ? 'mob' : 'desk');
    const val    = document.getElementById(srcId)?.checked;

    const dst = document.getElementById(dstId);
    if (dst) dst.checked = val;

    descAutoActivo = val;
    ['desk','mob'].forEach(s => {
        const f = document.getElementById('desc-auto-fields-' + s);
        if (f) f.style.display = val ? 'flex' : 'none';
    });
    calcularTotales();
}

function syncDescAuto(from) {
    const pct  = parseFloat(document.getElementById('desc-pct-'  + from)?.value) || 0;
    const dias = parseInt(document.getElementById('desc-dias-' + from)?.value)    || 1;
    const other = from === 'desk' ? 'mob' : 'desk';

    setVal('desc-pct-'  + other, pct);
    setVal('desc-dias-' + other, dias);
    descAutoPct  = pct;
    descAutoDias = dias;
    calcularTotales();
}

// ─── Notas sync ─────────────────────────────────────────
function syncNotas(from) {
    const other = from === 'desk' ? 'mob' : 'desk';
    ['notas-cliente','notas-internas'].forEach(key => {
        const src = document.getElementById(key + '-' + from);
        const dst = document.getElementById(key + '-' + other);
        if (src && dst) dst.value = src.value;
    });
}

// ─── Recolectar items del DOM ───────────────────────────
function recolectarItems() {
    const items = [];
    document.querySelectorAll('#items-list .item-card').forEach((card, i) => {
        items.push({
            orden:        i + 1,
            articulo_id:  card.dataset.articuloId || null,
            titulo:       card.querySelector('[data-campo=titulo]')?.value       || '',
            sku:          card.querySelector('[data-campo=sku]')?.value           || '',
            descripcion:  card.querySelector('[data-campo=descripcion]')?.value  || '',
            cantidad:     parseFloat(card.querySelector('[data-campo=cantidad]')?.value) || 1,
            precio_unit:  parseFloat(card.querySelector('[data-campo=precio]')?.value)  || 0,
            es_extra:     parseInt(card.dataset.esExtra) || 0,
        });
    });
    return items;
}

// ─── Guardar cotización ─────────────────────────────────
async function guardarCotizacion() {
    const items = recolectarItems();
    if (items.length === 0) { alert('Agrega al menos un artículo.'); return; }

    const titulo = document.getElementById('cot-titulo')?.value.trim() || '';
    if (!titulo) { alert('El título es requerido.'); return; }

    // Leer valores — usar mob si desk no tiene valor (estamos en mobile)
    const getVal = (deskId, mobId) => {
        const d = document.getElementById(deskId);
        const m = document.getElementById(mobId);
        return (d?.value || m?.value || '');
    };
    const getChecked = (deskId, mobId) => {
        return document.getElementById(deskId)?.checked || document.getElementById(mobId)?.checked || false;
    };

    const vendedorSel = document.getElementById('cot-vendedor');
    const payload = {
        titulo,
        cliente_id:            clienteSeleccionado?.id || null,
        vendedor_id:           vendedorSel ? parseInt(vendedorSel.value) : null,
        valida_hasta:          document.getElementById('cot-vence')?.value || null,
        notas_cliente:         getVal('notas-cliente-desk', 'notas-cliente-mob'),
        notas_internas:        getVal('notas-internas-desk', 'notas-internas-mob'),
        descuento_auto_activo: getChecked('desc-auto-toggle-desk', 'desc-auto-toggle-mob') ? 1 : 0,
        descuento_auto_pct:    parseFloat(getVal('desc-pct-desk', 'desc-pct-mob')) || 0,
        descuento_auto_dias:   parseInt(getVal('desc-dias-desk', 'desc-dias-mob')) || 3,
        cupon_id:              cuponSeleccionado?.id || null,
        items,
        preview: false,
    };

    const btn = document.getElementById('btn-guardar');
    const btnMob = document.querySelector('.btn-gen');
    if (btn) { btn.disabled = true; btn.textContent = 'Generando...'; }
    if (btnMob) { btnMob.disabled = true; btnMob.textContent = 'Generando...'; }

    try {
        const r = await fetch('/cotizaciones/nueva', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify(payload)
        });
        const data = await r.json();
        if (!data.ok) {
            alert(data.error || 'Error al guardar');
            if (btn) { btn.disabled = false; btn.textContent = 'Generar cotización'; }
            if (btnMob) { btnMob.disabled = false; btnMob.textContent = 'Generar cotización'; }
            return;
        }
        // Mostrar popup de éxito
        const cotId   = data.data.id;
        const cotSlug = data.data.slug;
        const urlPublica = URL_PUB_BASE + cotSlug;
        document.getElementById('popup-ver-url').href    = urlPublica;
        document.getElementById('popup-ver-url').target  = '_blank';
        document.getElementById('popup-link-url').value  = urlPublica;
        document.getElementById('popup-overlay').style.display = 'flex';
    } catch (e) {
        alert('Error de conexión: ' + e.message);
        if (btn) { btn.disabled = false; btn.textContent = 'Generar cotización'; }
        if (btnMob) { btnMob.disabled = false; btnMob.textContent = 'Generar cotización'; }
    }
}
function actualizarEstado() { /* hook futuro */ }

// ─── Helpers ────────────────────────────────────────────
function fmt(n) {
    const sym = EMPRESA_CFG.moneda === 'USD' ? 'USD ' : '$';
    return sym + parseFloat(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function strip(html) {
    const d = document.createElement('div'); d.innerHTML = html; return d.textContent || '';
}
function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }
function setVal(id, val)  { const el = document.getElementById(id); if (el) el.value = val; }
function autoResize(el)   { el.style.height = 'auto'; el.style.height = el.scrollHeight + 'px'; }
function toggleMob(hdr)   { hdr.closest('.mob-section').classList.toggle('open'); }
</script>

<!-- ══ POPUP: COTIZACIÓN GENERADA ══ -->
<div id="popup-overlay" style="display:none;position:fixed;inset:0;z-index:400;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);align-items:flex-end;justify-content:center;">
    <div style="background:var(--white);border-radius:20px 20px 0 0;padding:24px 20px 40px;width:100%;max-width:560px;">
        <div style="width:34px;height:4px;border-radius:2px;background:var(--border2);margin:0 auto 20px"></div>
        <div style="font-size:28px;text-align:center;margin-bottom:8px">🎉</div>
        <div style="font:800 19px var(--body);text-align:center;margin-bottom:4px">¡Cotización generada!</div>
        <div style="font:400 13px var(--body);color:var(--t3);text-align:center;margin-bottom:20px">Elige qué hacer ahora</div>

        <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-sm);padding:12px 14px;display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <input id="popup-link-url" type="text" readonly
                   style="flex:1;font:500 12px var(--num);color:var(--g);border:none;background:transparent;outline:none;word-break:break-all;min-width:0">
            <button onclick="const u=document.getElementById('popup-link-url').value;navigator.clipboard.writeText(u);this.textContent='¡Copiado!';setTimeout(()=>this.textContent='Copiar link',2000)"
                    style="padding:8px 13px;border-radius:7px;border:none;background:var(--g);font:700 12px var(--body);color:#fff;cursor:pointer;flex-shrink:0">Copiar link</button>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;">
            <a id="popup-ver-url" href="#"
               style="display:block;padding:14px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 14px var(--body);color:#fff;cursor:pointer;text-align:center;text-decoration:none;">
                Ver cotización
            </a>
            <a href="/cotizaciones"
               style="display:block;padding:14px;border-radius:var(--r-sm);border:1px solid var(--border);background:transparent;font:600 14px var(--body);color:var(--t2);cursor:pointer;text-align:center;text-decoration:none;">
                Volver al listado
            </a>
        </div>
</div><!-- /app-main -->

<!-- ══ BOTTOM NAV MOBILE ══════════════════════════════════ -->
<style>
#app-bottom-nav{display:none}
#app-more-drawer{display:none}
#app-more-overlay{display:none}
@media(max-width:768px){
  html,body{height:100%;overflow:hidden}
  #app-main{height:100%;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior-y:none}
  body.sheet-open #app-bottom-nav{display:none!important}
  #app-bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;height:calc(60px + env(safe-area-inset-bottom,0px));padding-bottom:env(safe-area-inset-bottom,0px);background:#fff;border-top:1px solid #e2e2dc;z-index:600;box-shadow:0 -2px 12px rgba(0,0,0,.08)}
  .app-bn-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;text-decoration:none;color:#6a6a64;font-size:10.5px;font-weight:500;padding:6px 4px;border:none;background:none;cursor:pointer;-webkit-tap-highlight-color:transparent;position:relative}
  .app-bn-item svg{width:22px;height:22px}
  .app-bn-item.active{color:#1a5c38}
  .app-bn-item.active svg{stroke:#1a5c38}
  .app-bn-item.active::before{content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);width:32px;height:3px;background:#1a5c38;border-radius:0 0 3px 3px}
  #app-more-drawer{display:none;position:fixed;bottom:64px;left:0;right:0;background:#fff;border-top:1px solid #e2e2dc;border-radius:12px 12px 0 0;z-index:85;box-shadow:0 -4px 24px rgba(0,0,0,.13);transform:translateY(100%);transition:transform .22s cubic-bezier(.4,0,.2,1);padding:8px 8px calc(8px + env(safe-area-inset-bottom))}
  #app-more-drawer.open{display:block;transform:translateY(0)}
  #app-more-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:82}
  #app-more-overlay.on{display:block}
  .app-more-handle{width:40px;height:4px;background:#c8c8c0;border-radius:2px;margin:0 auto 4px}
  .app-more-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;padding:8px 0}
  .app-more-item{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;border-radius:9px;text-decoration:none;color:#4a4a46;font-size:12px;font-weight:500;transition:background .12s,color .12s;-webkit-tap-highlight-color:transparent}
  .app-more-item:hover,.app-more-item.active{background:#eef7f2;color:#1a5c38}
  .app-more-item svg{width:24px;height:24px}
  .app-more-item-logout{color:#c53030}
  .app-more-item-logout:hover{background:#fff5f5;color:#c53030}
}
</style>
<nav id="app-bottom-nav">
  <a href="/"             class="app-bn-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Inicio</a>
  <a href="/cotizaciones" class="app-bn-item active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>Cotizaciones</a>
  <a href="/ventas"       class="app-bn-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>Ventas</a>
  <a href="/radar"        class="app-bn-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Radar</a>
  <button class="app-bn-item" id="app-btn-more" onclick="appToggleMore()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Más</button>
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
function appOpenMore(){var d=document.getElementById('app-more-drawer'),o=document.getElementById('app-more-overlay');d.style.display='block';o.classList.add('on');d.offsetHeight;d.classList.add('open');try{feather.replace({'stroke-width':1.8});}catch(e){}}
function appCloseMore(){var d=document.getElementById('app-more-drawer'),o=document.getElementById('app-more-overlay');d.classList.remove('open');o.classList.remove('on');setTimeout(function(){if(!d.classList.contains('open'))d.style.display='';},240);}
function appToggleMore(){var d=document.getElementById('app-more-drawer');if(d.classList.contains('open'))appCloseMore();else appOpenMore();}
(function(){var s=0,dr=document.getElementById('app-more-drawer');dr.addEventListener('touchstart',function(e){s=e.touches[0].clientY},{passive:true});dr.addEventListener('touchend',function(e){if(e.changedTouches[0].clientY-s>60)appCloseMore()},{passive:true});})();
</script>
</body>
</html>
