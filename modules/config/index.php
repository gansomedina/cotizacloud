<?php
// ============================================================
//  cotiza.cloud — modules/config/index.php
//  GET /config[?tab=empresa|catalogo|clientes|cupones|usuarios|radar|costos]
//  Solo admin puede acceder
// ============================================================

defined('COTIZAAPP') or die;
Auth::requerir_admin();

$empresa_id = EMPRESA_ID;
$tab_activo = in_array($_GET['tab'] ?? '', ['empresa','catalogo','clientes','cupones','usuarios','radar','costos','marketing','historial'])
    ? $_GET['tab'] : 'empresa';

// Usuarios solo disponible en plan Business
if ($tab_activo === 'usuarios') {
    $plan_check = trial_info(EMPRESA_ID);
    if (!$plan_check['es_business']) $tab_activo = 'empresa';
}

// Costos solo disponible en plan Pro o Business
if ($tab_activo === 'costos') {
    $plan_check = $plan_check ?? trial_info(EMPRESA_ID);
    if (!$plan_check['es_pagado']) $tab_activo = 'empresa';
}

// Marketing solo disponible en plan Business
if ($tab_activo === 'marketing') {
    $plan_check = $plan_check ?? trial_info(EMPRESA_ID);
    if (!$plan_check['es_business']) $tab_activo = 'empresa';
}

// ─── Cargar empresa ─────────────────────────────────────────
$empresa = DB::row("SELECT * FROM empresas WHERE id=?", [$empresa_id]);

// ─── Marketing config ──────────────────────────────────────
$mkt = DB::row("SELECT * FROM marketing_config WHERE empresa_id=?", [$empresa_id]) ?: [];

// ─── Catálogo ────────────────────────────────────────────────
$q_cat = trim($_GET['q_cat'] ?? '');
$articulos = DB::query(
    "SELECT * FROM articulos WHERE empresa_id=?
     " . ($q_cat ? "AND (titulo LIKE ? OR sku LIKE ?)" : "") . "
     ORDER BY orden ASC, titulo ASC",
    $q_cat ? [$empresa_id, "%$q_cat%", "%$q_cat%"] : [$empresa_id]
);

// ─── Clientes ────────────────────────────────────────────────
$q_cli = trim($_GET['q_cli'] ?? '');
$clientes = DB::query(
    "SELECT cl.*,
            (SELECT COUNT(*) FROM cotizaciones c WHERE c.cliente_id=cl.id) AS num_cots
     FROM clientes cl WHERE cl.empresa_id=?
     " . ($q_cli ? "AND (cl.nombre LIKE ? OR cl.telefono LIKE ?)" : "") . "
     ORDER BY cl.nombre ASC",
    $q_cli ? [$empresa_id, "%$q_cli%", "%$q_cli%"] : [$empresa_id]
);

// ─── Cupones ─────────────────────────────────────────────────
$cupones = DB::query(
    "SELECT * FROM cupones WHERE empresa_id=? ORDER BY created_at DESC",
    [$empresa_id]
);

// ─── Usuarios ────────────────────────────────────────────────
$usuarios = DB::query(
    "SELECT u.*, (SELECT MAX(us.created_at) FROM user_sessions us WHERE us.usuario_id=u.id) AS ultimo_login
     FROM usuarios u WHERE u.empresa_id=? ORDER BY u.rol ASC, u.nombre ASC",
    [$empresa_id]
);

// ─── Radar config ────────────────────────────────────────────
$radar_raw = $empresa['radar_config'] ?? null;
$radar_cfg = $radar_raw ? json_decode($radar_raw, true) : [];
$radar_modo       = $radar_cfg['modo']            ?? 'medio';
$radar_auto_cal   = (bool)($radar_cfg['auto_calibrar'] ?? true);
$radar_excl_int   = (bool)($radar_cfg['excluir_internos'] ?? true);
$radar_filtrar_bot= (bool)($radar_cfg['filtrar_bots']     ?? true);
$radar_dedup      = (bool)($radar_cfg['deduplicar_30min'] ?? true);
$radar_buckets    = $radar_cfg['buckets_activos'] ?? ['cierre','validando_precio','decision_activa','multi_persona','revivio','enfriandose','hesitacion'];

// Calibración FIT activa
$fit = DB::row(
    "SELECT * FROM radar_fit_calibracion WHERE empresa_id=? AND activa=1 ORDER BY created_at DESC LIMIT 1",
    [$empresa_id]
);
$fit_bandas = $fit ? json_decode($fit['bandas_json'] ?? '[]', true) : [];

// ─── Historial mensual ──────────────────────────────────────
$historial = DB::query(
    "SELECT * FROM historial_mensual WHERE empresa_id = ? ORDER BY anio DESC, mes DESC",
    [$empresa_id]
) ?: [];

// ─── Helpers ─────────────────────────────────────────────────
function ini_cfg(string $n): string {
    $p = array_filter(explode(' ', $n));
    $i = ''; foreach (array_slice($p,0,2) as $w) $i .= strtoupper($w[0]);
    return $i ?: '?';
}
function fmt_cfg(float $n): string { return '$' . number_format($n, 0, '.', ','); }

$page_title = 'Configuración';
ob_start();
?>
<style>
/* ─── Tabs ───────────────────────────────────────────────── */
.cfg-tabs-wrap{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:56px;z-index:90;overflow-x:auto;scrollbar-width:none;margin:-20px 0 24px;-webkit-overflow-scrolling:touch}
@media(max-width:768px){.cfg-tabs-wrap{top:52px;margin:0 -14px 20px;z-index:105;padding:0 14px}}
.cfg-tabs-wrap::-webkit-scrollbar{display:none}
.cfg-tabs{display:flex;max-width:var(--max);margin:0 auto}
.cfg-tab{padding:13px 16px;font:600 13px var(--body);color:var(--t3);text-decoration:none;border-bottom:2.5px solid transparent;white-space:nowrap;transition:all .15s;display:flex;align-items:center;gap:6px;-webkit-tap-highlight-color:rgba(0,0,0,.05)}
.cfg-tab:hover{color:var(--t2)}
.cfg-tab.on{color:var(--g);border-bottom-color:var(--g)}
.tab-panel{display:none}.tab-panel.on{display:block}

/* ─── Secciones ─────────────────────────────────────────── */
.sec{margin-bottom:22px}
.sec-lbl{font:700 11px var(--body);letter-spacing:.07em;text-transform:uppercase;color:var(--t2);margin-bottom:10px;display:flex;align-items:center;gap:10px}
.sec-lbl::after{content:'';flex:1;height:1.5px;background:var(--border)}

/* ─── Card + fields ─────────────────────────────────────── */
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:0}
.field-row{padding:12px 16px;border-bottom:1px solid var(--border);display:flex;flex-direction:column;gap:4px}
.field-row:last-child{border-bottom:none}
.field-row.h{flex-direction:row;align-items:center;justify-content:space-between;gap:16px}
.field-lbl{font:700 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3)}
.field-sub{font:400 11px var(--body);color:var(--t3);line-height:1.5;margin-top:2px}
.field-in{background:transparent;border:none;outline:none;font:500 15px var(--body);color:var(--text);width:100%}
.field-in::placeholder{color:var(--t3);font-weight:400}
.field-box{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:10px 13px;font:400 15px var(--body);color:var(--text);outline:none;transition:border-color .15s}
.field-box:focus{border-color:var(--g)}
.field-box::placeholder{color:var(--t3)}
textarea.field-in{resize:none;overflow:hidden;line-height:1.6;min-height:80px}
.num-in{width:80px;text-align:right;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:8px 10px;font:600 14px var(--num);outline:none}
.num-in:focus{border-color:var(--g)}
.in-row{display:flex;align-items:center;gap:8px}

/* ─── Toggle ────────────────────────────────────────────── */
.toggle{position:relative;width:40px;height:22px;cursor:pointer;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;border-radius:11px;background:var(--border2);transition:background .15s}
.toggle-thumb{position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:8px;background:#fff;transition:transform .15s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle input:checked~.toggle-track{background:var(--g)}
.toggle input:checked~.toggle-thumb{transform:translateX(18px)}

/* ─── Theme picker ───────────────────────────────────────── */
.theme-btn{display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:var(--r-sm);border:2px solid var(--border);background:var(--white);cursor:pointer;transition:all .15s}
.theme-btn:hover{border-color:var(--tc)}
.theme-btn.on{border-color:var(--tc);background:color-mix(in srgb, var(--tc) 8%, white)}
.theme-dot{width:20px;height:20px;border-radius:50%;background:var(--tc);flex-shrink:0}
.theme-lbl{font:600 13px var(--body);color:var(--t2)}
.theme-btn.on .theme-lbl{color:var(--tc);font-weight:700}

/* ─── Logo ───────────────────────────────────────────────── */
.logo-wrap{padding:16px;display:flex;align-items:center;gap:16px;border-bottom:1px solid var(--border)}
.logo-preview{width:80px;height:80px;border-radius:var(--r);border:1.5px solid var(--border2);background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:30px;overflow:hidden;flex-shrink:0}
.logo-preview img{width:100%;height:100%;object-fit:contain;padding:4px}
.logo-btn{padding:7px 13px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:600 12px var(--body);color:var(--t2);cursor:pointer;transition:all .12s}
.logo-btn:hover{border-color:var(--g);color:var(--g)}
.logo-btn.danger:hover{border-color:var(--danger);color:var(--danger)}
.logo-hint{font:400 11px var(--body);color:var(--t3);margin-top:6px}

/* ─── Impuesto radio ─────────────────────────────────────── */
.tax-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:14px 16px;border-bottom:1px solid var(--border)}
.tax-opt{padding:12px 14px;border-radius:var(--r-sm);border:1.5px solid var(--border);cursor:pointer;transition:all .15s}
.tax-opt.on{border-color:var(--g);background:var(--g-bg)}
.tax-opt-tit{font:700 13px var(--body);margin-bottom:3px}
.tax-opt-sub{font:400 11px var(--body);color:var(--t3)}
.tax-opt.on .tax-opt-sub{color:var(--g)}

/* ─── Vars de mensaje ────────────────────────────────────── */
.msg-vars{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.msg-var{padding:4px 9px;border-radius:20px;border:1px solid var(--g-border);background:var(--g-bg);font:600 11px var(--body);color:var(--g);cursor:pointer;transition:all .12s}
.msg-var:hover{background:var(--g);color:#fff}

/* ─── Búsqueda ───────────────────────────────────────────── */
.search-bar{position:relative;margin-bottom:12px}
.search-bar input{width:100%;background:var(--white);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:10px 14px 10px 38px;font:400 14px var(--body);color:var(--text);outline:none;transition:border-color .15s;box-shadow:var(--sh)}
.search-bar input:focus{border-color:var(--g)}
.search-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--t3);font-size:14px}

/* ─── Tabla catálogo ─────────────────────────────────────── */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th{font:700 11px var(--body);letter-spacing:.06em;text-transform:uppercase;color:var(--t3);padding:9px 14px;border-bottom:2px solid var(--border);background:var(--bg);white-space:nowrap}
.tbl td{padding:11px 14px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:#fafaf8}
.tbl-name{font:600 13px var(--body)}
.tbl-desc{font:400 11px var(--body);color:var(--t3);margin-top:2px}
.tbl-sku{font:600 12px var(--num);color:var(--t2);background:var(--bg);padding:2px 7px;border-radius:5px;border:1px solid var(--border)}
.tbl-price{font:700 13px var(--num);color:var(--text)}
.tbl-actions{display:flex;gap:4px;justify-content:flex-end}
.tbl-btn{width:30px;height:30px;border-radius:7px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;color:var(--t2);transition:all .12s}
.tbl-btn:hover{border-color:var(--g);color:var(--g)}
.tbl-btn.del:hover{border-color:var(--danger);color:var(--danger)}
.tbl-badge{padding:3px 9px;border-radius:20px;font:700 11px var(--body)}
@media(max-width:640px){
  .tbl th.hide-mob,.tbl td.hide-mob{display:none}
}
.badge-on{background:var(--g-bg);color:var(--g)}
.badge-off{background:var(--slate-bg);color:var(--slate)}

/* ─── Lista clientes ─────────────────────────────────────── */
.cli-row{display:flex;align-items:center;gap:12px;padding:11px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s}
.cli-row:last-child{border-bottom:none}
.cli-row:hover{background:#fafaf8}
.cli-av{width:36px;height:36px;border-radius:10px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 13px var(--body);color:#fff;flex-shrink:0}
.cli-info{flex:1;min-width:0}
.cli-name{font:600 14px var(--body)}
.cli-tel{font:400 12px var(--num);color:var(--t3);margin-top:2px}
.cli-cots{font:500 12px var(--body);color:var(--t3);flex-shrink:0}

/* ─── Lista usuarios ─────────────────────────────────────── */
.usr-row{display:flex;align-items:center;gap:12px;padding:12px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s}
.usr-row:last-child{border-bottom:none}
.usr-row:hover{background:#fafaf8}
.usr-av{width:36px;height:36px;border-radius:10px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 13px var(--body);color:#fff;flex-shrink:0}
.usr-av.asesor{background:var(--slate-bg);color:var(--slate)}
.usr-av.inactivo{background:var(--border2);color:var(--t3)}
.usr-info{flex:1;min-width:0}
.usr-name{font:600 14px var(--body)}
.usr-email{font:400 11px var(--body);color:var(--t3);margin-top:2px}
.usr-badges{display:flex;gap:5px;flex-wrap:wrap;margin-top:5px}
.ubadge{padding:2px 8px;border-radius:20px;font:700 10px var(--body);letter-spacing:.04em}
.ubadge-admin{background:#ede9fe;color:#6d28d9}
.ubadge-asesor{background:var(--slate-bg);color:var(--slate)}
.ubadge-perm{background:var(--g-bg);color:var(--g)}
.ubadge-off{background:var(--danger-bg);color:var(--danger)}
.ubadge-off{background:#fff7ed;color:#9a3412}
.usr-meta{flex-shrink:0;text-align:right}

/* ─── Radar modos ────────────────────────────────────────── */
.radar-modos{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px}
.radar-modo{padding:14px 16px;border-radius:var(--r);border:1.5px solid var(--border);cursor:pointer;transition:all .15s;background:var(--white)}
.radar-modo.on{border-color:var(--g);background:var(--g-bg)}
.radar-modo-tit{font:700 13px var(--body);margin-bottom:4px}
.radar-modo-sub{font:400 12px var(--body);color:var(--t3);line-height:1.5}
.radar-modo.on .radar-modo-sub{color:#2d7a50}

/* ─── Fit bandas ─────────────────────────────────────────── */
.fit-bandas{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;padding:14px 16px}
.fit-banda{background:var(--bg);border-radius:var(--r-sm);padding:10px;text-align:center}
.fit-banda-lbl{font:700 10px var(--body);color:var(--t3);margin-bottom:4px}
.fit-banda-val{font:700 16px var(--num)}

/* ─── Add row btn ────────────────────────────────────────── */
.add-row-btn{width:100%;padding:12px;border-radius:var(--r);border:1.5px dashed var(--border2);background:transparent;display:flex;align-items:center;justify-content:center;gap:8px;font:600 13px var(--body);color:var(--t2);cursor:pointer;transition:all .15s;margin-top:8px}
.add-row-btn:hover{border-color:var(--g);color:var(--g);background:var(--g-bg)}

/* ─── Save btn ───────────────────────────────────────────── */
.save-btn{padding:10px 24px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 14px var(--body);color:#fff;cursor:pointer;transition:opacity .15s}
.save-btn:hover{opacity:.88}

/* ─── Sheets ─────────────────────────────────────────────── */
.sh-overlay{position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .25s}
.sh-overlay.open{opacity:1;pointer-events:auto}
.bottom-sheet{display:none;position:fixed;bottom:0;left:0;right:0;z-index:201;background:var(--white);border-radius:20px 20px 0 0;max-height:92vh;flex-direction:column;box-shadow:0 -8px 32px rgba(0,0,0,.1);max-width:640px;margin:0 auto;pointer-events:none}
.bottom-sheet.open{display:flex;pointer-events:auto;animation:sheetUp .3s cubic-bezier(.32,0,.15,1)}
@keyframes sheetUp{from{transform:translateY(100%)}to{transform:translateY(0)}}
.sh-handle{width:34px;height:4px;border-radius:2px;background:var(--border2);margin:12px auto 0;flex-shrink:0}
.sh-header{padding:14px 18px 12px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;border-bottom:1px solid var(--border)}
.sh-title{font:800 17px var(--body)}
.sh-close{width:30px;height:30px;border-radius:999px;border:none;background:var(--bg);font-size:15px;cursor:pointer;color:var(--t2)}
.sh-body{overflow-y:auto;flex:1;padding:0 0 16px}
.sh-field{padding:13px 18px;border-bottom:1px solid var(--border)}
.sh-field:last-child{border-bottom:none}
.sh-lbl{font:700 10px var(--body);letter-spacing:.08em;text-transform:uppercase;color:var(--t3);margin-bottom:6px}
.sh-note{font:400 11px var(--body);color:var(--t3);margin-top:5px;line-height:1.5}
.sh-input{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:11px 13px;font:400 15px var(--body);color:var(--text);outline:none;transition:border-color .15s}
.sh-input:focus{border-color:var(--g)}
.sh-input.mono{font-family:'DM Sans',monospace;letter-spacing:.04em}
.sh-select{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r-sm);padding:11px 13px;font:400 15px var(--body);color:var(--text);outline:none;appearance:none;cursor:pointer}
.sh-select:focus{border-color:var(--g)}
.sh-row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sh-footer{padding:14px 18px;border-top:1px solid var(--border);flex-shrink:0;display:flex;gap:10px}
.sh-btn-save{flex:1;padding:13px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 14px var(--body);color:#fff;cursor:pointer}
.sh-btn-cancel{padding:13px 18px;border-radius:var(--r-sm);border:1px solid var(--border);background:transparent;font:600 14px var(--body);color:var(--t2);cursor:pointer}
.sh-btn-danger{flex:0 0 auto;padding:13px 16px;border-radius:var(--r-sm);border:1.5px solid var(--danger);background:transparent;font:600 13px var(--body);color:var(--danger);cursor:pointer}
.sh-btn-danger:hover{background:var(--danger-bg)}

/* ─── Permisos checkbox list ─────────────────────────────── */
.perm-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)}
.perm-row:last-child{border-bottom:none}
.perm-lbl{font:500 13px var(--body);color:var(--text)}
.perm-sub{font:400 11px var(--body);color:var(--t3);margin-top:2px}

/* ─── Aviso ──────────────────────────────────────────────── */
.aviso{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:#fef3c7;border:1px solid #fcd34d;border-radius:var(--r);margin-bottom:16px;font:400 13px var(--body);color:#92400e;line-height:1.5}

/* ─── Responsive ─────────────────────────────────────────── */
@media(max-width:640px){
  .grid2{grid-template-columns:1fr}
  .grid2 .field-row{border-right:none}
  .radar-modos{grid-template-columns:1fr}
  .sh-row2{grid-template-columns:1fr}
  .tax-grid{grid-template-columns:1fr}
  .costos-modo-grid{grid-template-columns:1fr}
}
@media(min-width:641px){
  .grid2 .field-row{border-right:1px solid var(--border)}
  .grid2 .field-row:nth-child(2n){border-right:none}
}

/* ── Costos modo selector ── */
.costos-modo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
.costos-modo-opt{position:relative;display:flex;flex-direction:column;align-items:center;text-align:center;padding:20px 16px;border:2px solid var(--border);border-radius:12px;cursor:pointer;transition:all .2s;background:var(--bg)}
.costos-modo-opt:hover{border-color:var(--g);background:#f7faf8}
.costos-modo-opt.sel{border-color:var(--g);background:#eef7f2;box-shadow:0 0 0 1px var(--g)}
.cm-icon{margin-bottom:10px;color:var(--t3)}.costos-modo-opt.sel .cm-icon{color:var(--g)}
.cm-name{font:700 15px var(--body);color:var(--text);margin-bottom:6px}
.cm-desc{font:400 12px var(--body);color:var(--t3);line-height:1.4}
.cm-check{display:none;position:absolute;top:8px;right:8px;width:24px;height:24px;border-radius:50%;background:var(--g);color:#fff;align-items:center;justify-content:center}
.costos-modo-opt.sel .cm-check{display:flex}
.cm-badge{display:inline-block;margin-top:8px;padding:2px 8px;border-radius:4px;background:#3b82f6;color:#fff;font:600 10px var(--body);letter-spacing:.04em;text-transform:uppercase}
.costos-modo-note{font-size:12px;color:var(--t3);margin:12px 0 0;display:flex;align-items:flex-start;gap:6px;line-height:1.4}
</style>

<!-- Header con botón guardar empresa -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;flex-wrap:wrap;gap:10px">
  <h1 style="font:800 22px var(--body);letter-spacing:-.02em">Configuración</h1>
  <button class="save-btn" id="btnGuardarEmpresa" onclick="guardarEmpresa()" style="display:<?= $tab_activo==='empresa'?'block':'none' ?>">Guardar cambios</button>
</div>

<!-- Tabs -->
<div class="cfg-tabs-wrap">
  <div class="cfg-tabs">
    <a class="cfg-tab <?= $tab_activo==='empresa'   ?'on':'' ?>" href="/config?tab=empresa">Empresa</a>
    <a class="cfg-tab <?= $tab_activo==='catalogo'  ?'on':'' ?>" href="/config?tab=catalogo">Catálogo</a>
    <a class="cfg-tab <?= $tab_activo==='clientes'  ?'on':'' ?>" href="/config?tab=clientes">Clientes</a>
    <a class="cfg-tab <?= $tab_activo==='cupones'   ?'on':'' ?>" href="/config?tab=cupones">Cupones</a>
    <?php $plan_info = trial_info(EMPRESA_ID); if ($plan_info['es_business']): ?>
    <a class="cfg-tab <?= $tab_activo==='usuarios'  ?'on':'' ?>" href="/config?tab=usuarios">Usuarios</a>
    <?php endif; ?>
    <a class="cfg-tab <?= $tab_activo==='radar'     ?'on':'' ?>" href="/config?tab=radar">Radar</a>
    <?php if ($plan_info['es_pagado']): ?>
    <a class="cfg-tab <?= $tab_activo==='costos'    ?'on':'' ?>" href="/config?tab=costos">Costos</a>
    <?php endif; ?>
    <?php if ($plan_info['es_business']): ?>
    <a class="cfg-tab <?= $tab_activo==='marketing' ?'on':'' ?>" href="/config?tab=marketing">Marketing</a>
    <a class="cfg-tab <?= $tab_activo==='historial' ?'on':'' ?>" href="/config?tab=historial">Historial</a>
    <?php endif; ?>
  </div>
</div>


<!-- ══ TAB: EMPRESA ══════════════════════════════════════════ -->
<div class="tab-panel <?= $tab_activo==='empresa'?'on':'' ?>" id="panel-empresa">

  <!-- Datos generales -->
  <div class="sec">
    <div class="sec-lbl">Datos generales</div>
    <div class="card">
      <div class="logo-wrap">
        <div class="logo-preview" id="logoPreview">
          <?php if ($empresa['logo_url']): ?>
          <img src="<?= e($empresa['logo_url']) ?>" alt="Logo">
          <?php else: ?>
          🏠
          <?php endif; ?>
        </div>
        <div>
          <div id="logoBtns" style="display:flex;gap:8px;flex-wrap:wrap">
            <label class="logo-btn" style="cursor:pointer">
              Subir logo
              <input type="file" accept="image/png,image/svg+xml,image/jpeg,image/webp" style="display:none" onchange="subirLogo(this)">
            </label>
            <?php if ($empresa['logo_url']): ?>
            <button class="logo-btn danger" id="btnQuitarLogo" onclick="quitarLogo()">Quitar logo</button>
            <?php endif; ?>
          </div>
          <div class="logo-hint">PNG, SVG o WEBP · máx. 2 MB · fondo transparente recomendado</div>
        </div>
      </div>
      <div class="grid2">
        <div class="field-row">
          <div class="field-lbl">Nombre de empresa</div>
          <input class="field-in" id="e_nombre" type="text" value="<?= e($empresa['nombre']) ?>" placeholder="Nombre de tu empresa">
        </div>
        <div class="field-row">
          <div class="field-lbl">Ciudad / Sucursal</div>
          <input class="field-in" id="e_ciudad" type="text" value="<?= e($empresa['ciudad'] ?? '') ?>" placeholder="Ciudad, Estado">
        </div>
        <div class="field-row">
          <div class="field-lbl">Teléfono</div>
          <input class="field-in" id="e_telefono" type="tel" value="<?= e($empresa['telefono'] ?? '') ?>" placeholder="10 dígitos">
        </div>
        <div class="field-row">
          <div class="field-lbl">Email</div>
          <input class="field-in" id="e_email" type="email" value="<?= e($empresa['email'] ?? '') ?>" placeholder="correo@empresa.com">
        </div>
        <div class="field-row" style="grid-column:1/-1">
          <div class="field-lbl">Dirección</div>
          <input class="field-in" id="e_direccion" type="text" value="<?= e($empresa['direccion'] ?? '') ?>" placeholder="Calle, número, colonia…">
        </div>
        <div class="field-row">
          <div class="field-lbl">RFC</div>
          <input class="field-in" id="e_rfc" type="text" value="<?= e($empresa['rfc'] ?? '') ?>" placeholder="XAXX010101000">
        </div>
        <div class="field-row">
          <div class="field-lbl">Sitio web</div>
          <input class="field-in" id="e_website" type="url" value="<?= e($empresa['website'] ?? '') ?>" placeholder="https://tuempresa.com">
        </div>
      </div>
    </div>
  </div>

  <!-- Impuestos -->
  <div class="sec">
    <div class="sec-lbl">Impuestos</div>
    <div class="card">
      <div class="tax-grid">
        <div class="tax-opt <?= $empresa['impuesto_modo']==='ninguno'?'on':'' ?>" onclick="selTax('ninguno',this)" data-modo="ninguno">
          <div class="tax-opt-tit">Ninguno</div>
          <div class="tax-opt-sub">Sin impuesto en los totales</div>
        </div>
        <div class="tax-opt <?= $empresa['impuesto_modo']==='suma'?'on':'' ?>" onclick="selTax('suma',this)" data-modo="suma">
          <div class="tax-opt-tit">Suma</div>
          <div class="tax-opt-sub">IVA se agrega al subtotal</div>
        </div>
        <div class="tax-opt <?= $empresa['impuesto_modo']==='incluido'?'on':'' ?>" onclick="selTax('incluido',this)" data-modo="incluido">
          <div class="tax-opt-tit">Incluido</div>
          <div class="tax-opt-sub">Precios ya incluyen IVA</div>
        </div>
      </div>
      <input type="hidden" id="e_impuesto_modo" value="<?= e($empresa['impuesto_modo']) ?>">
      <div class="field-row h">
        <div>
          <div class="field-lbl">Porcentaje IVA</div>
          <div class="field-sub">Aplica cuando el modo es "Suma"</div>
        </div>
        <div class="in-row">
          <input class="num-in" id="e_impuesto_pct" type="number" min="0" max="99" step="0.01" value="<?= (float)$empresa['impuesto_pct'] ?>">
          <span style="font:500 14px var(--body);color:var(--t2)">%</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Notificaciones -->
  <?php $ncfg = notif_config($empresa_id); ?>
  <div class="sec">
    <div class="sec-lbl">Notificaciones</div>
    <div class="card">
      <div class="field-row">
        <div class="field-lbl">Email de notificaciones</div>
        <div class="field-sub">Recibe avisos de tu negocio. Puede ser distinto al email principal.</div>
        <input class="field-box" id="e_notif_email" type="email" placeholder="alertas@tuempresa.com" style="margin-top:10px" value="<?= e($empresa['notif_email'] ?? '') ?>">
      </div>
      <div class="field-row h">
        <div>
          <div class="field-lbl">Cotización aceptada</div>
          <div class="field-sub">Aviso cuando un cliente acepta una cotización</div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="e_notif_acepta" <?= ($ncfg['cotizacion_aceptada'] ?? true)?'checked':'' ?>>
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>
      <div class="field-row h">
        <div>
          <div class="field-lbl">Cotización rechazada</div>
          <div class="field-sub">Aviso cuando un cliente rechaza una cotización</div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="e_notif_rechaza" <?= ($ncfg['cotizacion_rechazada'] ?? true)?'checked':'' ?>>
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>
      <div class="field-row h">
        <div>
          <div class="field-lbl">Abono registrado</div>
          <div class="field-sub">Aviso cuando se registra un pago en una venta</div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="e_notif_abono" <?= ($ncfg['abono_registrado'] ?? true)?'checked':'' ?>>
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>
      <div class="field-row h">
        <div>
          <div class="field-lbl">Alertas del Radar</div>
          <div class="field-sub">Aviso cuando una cotización tiene actividad importante</div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="e_notif_radar" <?= ($ncfg['radar_alerta'] ?? true)?'checked':'' ?>>
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>
    </div>
  </div>

  <!-- Theme cotización pública -->
  <div class="sec">
    <div class="sec-lbl">Apariencia — Cotización pública</div>
    <div class="card">
      <div class="field-row">
        <div class="field-lbl">Color de la cotización</div>
        <div class="field-sub">Este color se aplica en el enlace público que ve tu cliente</div>
        <div style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap" id="theme-picker">
          <?php
          $theme_actual = $empresa['cot_theme'] ?? 'verde';
          $theme_opts = [
              'verde'   => ['#1a6b3c','Verde'],
              'azul'    => ['#1d4ed8','Azul'],
              'rojo'    => ['#b91c1c','Rojo'],
              'naranja' => ['#d97706','Naranja'],
              'dorado'  => ['#92400e','Dorado'],
              'morado'  => ['#6d28d9','Morado'],
              'oscuro'  => ['#1e293b','Oscuro'],
          ];
          foreach ($theme_opts as $key => [$color, $label]):
          ?>
          <button type="button" class="theme-btn <?= $key === $theme_actual ? 'on' : '' ?>"
                  data-theme="<?= $key ?>" onclick="selTheme(this)"
                  style="--tc:<?= $color ?>">
            <span class="theme-dot"></span>
            <span class="theme-lbl"><?= $label ?></span>
          </button>
          <?php endforeach ?>
        </div>
        <input type="hidden" id="e_cot_theme" value="<?= e($theme_actual) ?>">
      </div>
    </div>
  </div>

  <!-- Defaults cotizaciones -->
  <div class="sec">
    <div class="sec-lbl">Defaults — Cotizaciones</div>
    <div class="card">
      <div class="field-row h">
        <div>
          <div class="field-lbl">Vigencia por defecto</div>
          <div class="field-sub">Días de validez al crear una cotización nueva</div>
        </div>
        <div class="in-row">
          <input class="num-in" id="e_cot_vigencia_dias" type="number" min="1" max="365" value="<?= (int)$empresa['cot_vigencia_dias'] ?>">
          <span style="font:500 14px var(--body);color:var(--t2)">días</span>
        </div>
      </div>
      <div class="field-row h">
        <div>
          <div class="field-lbl">Permitir a asesores editar precios</div>
          <div class="field-sub">Si está apagado, nadie puede cambiar precios unitarios en cotizaciones</div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="e_allow_precio_edit" <?= $empresa['allow_precio_edit']?'checked':'' ?>>
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>
      <div class="field-row h">
        <div>
          <div class="field-lbl">Ocultar cantidad y precio unitario al cliente</div>
          <div class="field-sub">En las vistas públicas (links de cotización y venta) solo se muestra descripción y total por línea</div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="e_ocultar_cant_pu" <?= !empty($empresa['ocultar_cant_pu'])?'checked':'' ?>>
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>
      <div class="field-row h">
        <div>
          <div class="field-lbl">Auto-suspender cotizaciones</div>
          <div class="field-sub">Suspender automáticamente cotizaciones sin actividad después de X días. El cliente no podrá ver la cotización y se excluye del Radar.</div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="e_auto_suspender_activo" <?= !empty($empresa['auto_suspender_activo'])?'checked':'' ?>>
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>
      <div class="field-row h" id="auto_susp_dias_row" style="<?= empty($empresa['auto_suspender_activo'])?'opacity:.4;pointer-events:none':'' ?>">
        <div>
          <div class="field-lbl">Días sin actividad para suspender</div>
          <div class="field-sub">Se cuentan desde la última vista del cliente</div>
        </div>
        <div class="in-row">
          <input class="num-in" id="e_auto_suspender_dias" type="number" min="7" max="365" value="<?= (int)($empresa['auto_suspender_dias'] ?? 30) ?>">
          <span style="font:500 14px var(--body);color:var(--t2)">días</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Mensajes cotizaciones -->
  <div class="sec">
    <div class="sec-lbl">Mensajes — Cotizaciones</div>
    <div class="card">
      <div class="field-row">
        <div class="field-lbl">Encabezado / Saludo</div>
        <div class="field-sub">Texto que aparece antes de los artículos en la cotización pública. Ej: "Gracias por su interés, es un placer atenderle..."</div>
        <textarea class="field-box" id="e_cot_encabezado" style="margin-top:10px;min-height:70px;resize:none" oninput="autoResize(this)"><?= e($empresa['cot_encabezado'] ?? '') ?></textarea>
        <div class="msg-vars">
          <div class="msg-var" onclick="insertVar('e_cot_encabezado','{{cliente}}')">{{cliente}}</div>
          <div class="msg-var" onclick="insertVar('e_cot_encabezado','{{empresa}}')">{{empresa}}</div>
          <div class="msg-var" onclick="insertVar('e_cot_encabezado','{{asesor}}')">{{asesor}}</div>
        </div>
      </div>
      <div class="field-row">
        <div class="field-lbl">Mensaje al aceptar</div>
        <div class="field-sub">El cliente lo ve en pantalla al dar clic en "Aceptar"</div>
        <textarea class="field-box" id="e_cot_msg_acepta" style="margin-top:10px;min-height:80px;resize:none" oninput="autoResize(this)"><?= e($empresa['cot_msg_acepta'] ?? '') ?></textarea>
        <div class="msg-vars">
          <div class="msg-var" onclick="insertVar('e_cot_msg_acepta','{{cliente}}')">{{cliente}}</div>
          <div class="msg-var" onclick="insertVar('e_cot_msg_acepta','{{cotizacion}}')">{{cotizacion}}</div>
          <div class="msg-var" onclick="insertVar('e_cot_msg_acepta','{{empresa}}')">{{empresa}}</div>
        </div>
      </div>
      <div class="field-row">
        <div class="field-lbl">Mensaje al rechazar</div>
        <div class="field-sub">El cliente lo ve en pantalla al dar clic en "Rechazar"</div>
        <textarea class="field-box" id="e_cot_msg_rechaza" style="margin-top:10px;min-height:70px;resize:none" oninput="autoResize(this)"><?= e($empresa['cot_msg_rechaza'] ?? '') ?></textarea>
        <div class="msg-vars">
          <div class="msg-var" onclick="insertVar('e_cot_msg_rechaza','{{cliente}}')">{{cliente}}</div>
          <div class="msg-var" onclick="insertVar('e_cot_msg_rechaza','{{empresa}}')">{{empresa}}</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Términos cotizaciones -->
  <div class="sec">
    <div class="sec-lbl">Términos y condiciones — Cotizaciones</div>
    <div class="card">
      <div class="field-row">
        <div class="field-lbl">Términos</div>
        <div class="field-sub">Aparecen al pie de la cotización antes del botón Aceptar</div>
        <textarea class="field-box" id="e_cot_terminos" style="margin-top:10px;min-height:100px;resize:none" oninput="autoResize(this)"><?= e($empresa['cot_terminos'] ?? '') ?></textarea>
      </div>
      <div class="field-row">
        <div class="field-lbl">Footer de cotización</div>
        <div class="field-sub">Texto pequeño al pie</div>
        <textarea class="field-box" id="e_cot_footer" style="margin-top:10px;min-height:50px;resize:none" oninput="autoResize(this)"><?= e($empresa['cot_footer'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Términos ventas -->
  <div class="sec">
    <div class="sec-lbl">Términos y condiciones — Ventas</div>
    <div class="card">
      <div class="field-row">
        <div class="field-lbl">Términos</div>
        <div class="field-sub">Aparecen en la vista de venta y en los recibos</div>
        <textarea class="field-box" id="e_vta_terminos" style="margin-top:10px;min-height:90px;resize:none" oninput="autoResize(this)"><?= e($empresa['vta_terminos'] ?? '') ?></textarea>
      </div>
      <div class="field-row">
        <div class="field-lbl">Footer de venta / recibo</div>
        <textarea class="field-box" id="e_vta_footer" style="margin-top:10px;min-height:50px;resize:none" oninput="autoResize(this)"><?= e($empresa['vta_footer'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <div style="display:flex;justify-content:flex-end;padding-bottom:10px">
    <button class="save-btn" onclick="guardarEmpresa()">Guardar cambios</button>
  </div>

</div><!-- /panel-empresa -->


<!-- ══ TAB: CATÁLOGO ═════════════════════════════════════════ -->
<div class="tab-panel <?= $tab_activo==='catalogo'?'on':'' ?>" id="panel-catalogo">

  <div class="search-bar">
    <span class="search-ico"><?= ico('search', 16, '#6a6a64') ?></span>
    <input type="text" placeholder="Buscar artículo…" id="qCat" value="<?= e($q_cat) ?>"
           oninput="debounce(()=>buscarTab('q_cat',this.value),280)">
  </div>

  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Artículo</th>
            <th>SKU</th>
            <th>Precio</th>
            <th style="text-align:right">Acciones</th>
          </tr>
        </thead>
        <tbody id="tblCat">
          <?php foreach ($articulos as $art): ?>
          <tr data-art-id="<?= (int)$art['id'] ?>">
            <td>
              <div class="tbl-name"><?= e($art['titulo']) ?></div>
              <?php if ($art['descripcion']): ?>
              <div class="tbl-desc"><?= e(mb_substr(strip_tags($art['descripcion']), 0, 70)) ?></div>
              <?php endif; ?>
            </td>
            <td><?php if ($art['sku']): ?><span class="tbl-sku"><?= e($art['sku']) ?></span><?php endif; ?></td>
            <td><span class="tbl-price"><?= fmt_cfg((float)$art['precio']) ?></span></td>
            <td>
              <div class="tbl-actions">
                <button class="tbl-btn"
                        onclick='editarArticulo(<?= (int)$art["id"] ?>, <?= htmlspecialchars(json_encode(["titulo"=>$art["titulo"],"sku"=>$art["sku"],"descripcion"=>strip_tags($art["descripcion"]??''),"precio"=>$art["precio"]]), ENT_QUOTES) ?>)'
                        title="Editar">✎</button>
                <button class="tbl-btn del" onclick="eliminarArticulo(<?= (int)$art['id'] ?>, this)" title="Eliminar">✕</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($articulos)): ?>
          <tr><td colspan="4" style="text-align:center;padding:28px;color:var(--t3);font-size:13px">Sin artículos en el catálogo</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <button class="add-row-btn" onclick="nuevoArticulo()">+ Nuevo artículo</button>

</div><!-- /panel-catalogo -->


<!-- ══ TAB: CLIENTES ═════════════════════════════════════════ -->
<div class="tab-panel <?= $tab_activo==='clientes'?'on':'' ?>" id="panel-clientes">

  <div class="search-bar">
    <span class="search-ico"><?= ico('search', 16, '#6a6a64') ?></span>
    <input type="text" placeholder="Buscar cliente…" id="qCli" value="<?= e($q_cli) ?>"
           oninput="debounce(()=>buscarTab('q_cli',this.value),280)">
  </div>

  <div class="card">
    <?php foreach ($clientes as $cl):
      $ini = ini_cfg($cl['nombre']);
    ?>
    <div class="cli-row" onclick='editarCliente(<?= (int)$cl["id"] ?>, <?= htmlspecialchars(json_encode(["nombre"=>$cl["nombre"],"telefono"=>$cl["telefono"],"email"=>$cl["email"],"notas"=>$cl["notas"]]), ENT_QUOTES) ?>)'>
      <div class="cli-av"><?= e($ini) ?></div>
      <div class="cli-info">
        <div class="cli-name"><?= e($cl['nombre']) ?></div>
        <div class="cli-tel"><?= e($cl['telefono']) ?><?= $cl['email'] ? ' · ' . e($cl['email']) : '' ?></div>
      </div>
      <div class="cli-cots"><?= (int)$cl['num_cots'] ?> cot<?= $cl['num_cots']!=1?'s':'' ?></div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($clientes)): ?>
    <div style="text-align:center;padding:28px;color:var(--t3);font-size:13px">Sin clientes aún</div>
    <?php endif; ?>
  </div>
  <button class="add-row-btn" onclick="nuevoCliente()">+ Nuevo cliente</button>

</div><!-- /panel-clientes -->


<!-- ══ TAB: CUPONES ══════════════════════════════════════════ -->
<div class="tab-panel <?= $tab_activo==='cupones'?'on':'' ?>" id="panel-cupones">

  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Código</th>
            <th>Descuento</th>
            <th class="hide-mob">Usos</th>
            <th class="hide-mob">Estado</th>
            <th style="text-align:right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cupones as $cup): ?>
          <tr data-cup-id="<?= (int)$cup['id'] ?>">
            <td>
              <div style="font:700 13px var(--num)"><?= e($cup['codigo']) ?></div>
              <?php if ($cup['descripcion']): ?>
              <div style="font:400 11px var(--body);color:var(--t3);margin-top:2px"><?= e($cup['descripcion']) ?></div>
              <?php endif; ?>
            </td>
            <td><span class="tbl-price">-<?= (float)$cup['porcentaje'] ?>%</span></td>
            <td class="hide-mob" style="font:500 13px var(--num);color:var(--t3)"><?= (int)$cup['usos'] ?></td>
            <td class="hide-mob"><span class="tbl-badge <?= $cup['activo']?'badge-on':'badge-off' ?>"><?= $cup['activo']?'Activo':'Inactivo' ?></span></td>
            <td style="text-align:right">
              <div class="tbl-actions">
                <button class="tbl-btn"
                        onclick='editarCupon(<?= (int)$cup["id"] ?>, <?= htmlspecialchars(json_encode(["codigo"=>$cup["codigo"],"descripcion"=>$cup["descripcion"],"porcentaje"=>$cup["porcentaje"],"activo"=>$cup["activo"],"vencimiento_tipo"=>$cup["vencimiento_tipo"]??'nunca',"vencimiento_dias"=>$cup["vencimiento_dias"]??null,"vencimiento_fecha"=>$cup["vencimiento_fecha"]??null]), ENT_QUOTES) ?>)'
                        title="Editar">✎</button>
                <button class="tbl-btn del" onclick="eliminarCupon(<?= (int)$cup['id'] ?>, this)" title="Eliminar">✕</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($cupones)): ?>
          <tr><td colspan="5" style="text-align:center;padding:28px;color:var(--t3);font-size:13px">Sin cupones creados</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <button class="add-row-btn" onclick="nuevoCupon()">+ Nuevo cupón</button>

  <!-- Descuento automático defaults -->
  <div class="sec" style="margin-top:24px">
    <div class="sec-title">Descuento automático (cronómetro)</div>
    <div class="card" style="padding:16px 20px">
      <div style="font-size:13px;color:var(--t3);margin-bottom:12px">Valores por defecto al activar el descuento con cronómetro en una cotización.</div>
      <div class="field-row h">
        <div class="field-group" style="flex:1">
          <label class="field-lbl">Porcentaje</label>
          <div style="display:flex;align-items:center;gap:6px">
            <input class="num-in" id="e_desc_auto_pct" type="number" min="0" max="100" step="0.5" value="<?= (float)($empresa['descuento_auto_pct_default'] ?? 0) ?>" style="width:80px">
            <span style="font-size:13px;color:var(--t3)">%</span>
          </div>
        </div>
        <div class="field-group" style="flex:1">
          <label class="field-lbl">Días de vigencia</label>
          <div style="display:flex;align-items:center;gap:6px">
            <input class="num-in" id="e_desc_auto_dias" type="number" min="1" max="30" value="<?= (int)($empresa['descuento_auto_dias_default'] ?? 3) ?>" style="width:80px">
            <span style="font-size:13px;color:var(--t3)">días</span>
          </div>
        </div>
      </div>
      <button class="save-btn" style="margin-top:12px" onclick="guardarDescDefaults()">Guardar defaults</button>
    </div>
  </div>

</div><!-- /panel-cupones -->


<!-- ══ TAB: USUARIOS ═════════════════════════════════════════ -->
<div class="tab-panel <?= $tab_activo==='usuarios'?'on':'' ?>" id="panel-usuarios">

  <div class="aviso">
    <span style="font-size:16px;flex-shrink:0">⚙️</span>
    <div>El permiso <strong>"puede editar precios"</strong> solo aplica si está activado globalmente en <strong>Empresa → Defaults</strong>.</div>
  </div>

  <?php
  // Cargar scores del equipo
  $scores_equipo = [];
  foreach (ActividadScore::equipo($empresa_id) as $se) {
      $scores_equipo[(int)$se['usuario_id']] = $se;
  }
  ?>
  <div class="card">
    <?php foreach ($usuarios as $usr):
      $ini = ini_cfg($usr['nombre']);
      $es_admin = $usr['rol'] === 'admin';
      $usr_score = $scores_equipo[(int)$usr['id']] ?? null;
    ?>
    <div class="usr-row" onclick='editarUsuario(<?= (int)$usr["id"] ?>, <?= htmlspecialchars(json_encode(["nombre"=>$usr["nombre"],"usuario"=>$usr["usuario"],"email"=>$usr["email"]??'',"rol"=>$usr["rol"],"activo"=>$usr["activo"],"puede_editar_precios"=>$usr["puede_editar_precios"],"puede_aplicar_descuentos"=>$usr["puede_aplicar_descuentos"],"puede_ver_todas_cots"=>$usr["puede_ver_todas_cots"],"puede_ver_todas_ventas"=>$usr["puede_ver_todas_ventas"],"puede_eliminar_items_venta"=>$usr["puede_eliminar_items_venta"],"puede_cancelar_recibos"=>$usr["puede_cancelar_recibos"],"puede_capturar_pagos"=>$usr["puede_capturar_pagos"]??0,"puede_asignar_cotizaciones"=>$usr["puede_asignar_cotizaciones"]??0,"puede_ver_costos"=>$usr["puede_ver_costos"]??1,"puede_ver_proveedores"=>$usr["puede_ver_proveedores"]??1]), ENT_QUOTES) ?>)'>
      <div class="usr-av <?= $es_admin?'':'asesor' ?> <?= !$usr['activo']?'inactivo':'' ?>">
        <?= e($ini) ?>
      </div>
      <div class="usr-info">
        <div class="usr-name"><?= e($usr['nombre']) ?></div>
        <div class="usr-email"><?= e($usr['usuario']) ?><?= $usr['email'] ? ' · ' . e($usr['email']) : '' ?></div>
        <div class="usr-badges">
          <?php if ($es_admin): ?>
          <span class="ubadge ubadge-admin">Admin</span>
          <?php else: ?>
          <span class="ubadge ubadge-asesor">Asesor</span>
          <?php if ($usr['puede_editar_precios']): ?><span class="ubadge ubadge-perm">Edita precios</span><?php endif; ?>
          <?php if ($usr['puede_aplicar_descuentos']): ?><span class="ubadge ubadge-perm">Aplica descuentos</span><?php endif; ?>
          <?php if ($usr['puede_ver_todas_cots']): ?><span class="ubadge ubadge-perm">Ve todas las cots</span><?php endif; ?>
          <?php if (!empty($usr['puede_capturar_pagos'])): ?><span class="ubadge ubadge-perm">Captura pagos</span><?php endif; ?>
          <?php if (!empty($usr['puede_asignar_cotizaciones'])): ?><span class="ubadge ubadge-perm">Asigna cots</span><?php endif; ?>
          <?php if (empty($usr['puede_ver_costos'] ?? 1)): ?><span class="ubadge ubadge-off">Sin costos</span><?php endif; ?>
          <?php if (empty($usr['puede_ver_proveedores'] ?? 1)): ?><span class="ubadge ubadge-off">Sin proveedores</span><?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="usr-meta">
        <?php if ($usr_score && $usr['activo']):
          $usc = (int)$usr_score['score'];
          $usc_color = match($usr_score['nivel']) {
              'top' => '#2563eb', 'activo' => '#16a34a', 'regular' => '#d97706', default => '#dc2626'
          };
          $usc_lbl = match($usr_score['nivel']) {
              'top' => 'Top', 'activo' => 'Activo', 'regular' => 'Regular', default => 'Bajo'
          };
        ?>
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px" title="Score: <?= $usc ?>/100 · <?= $usr_score['dias_activos'] ?> días activo · <?= $usr_score['acciones'] ?> acciones">
          <svg width="28" height="28" viewBox="0 0 28 28" style="flex-shrink:0"><circle cx="14" cy="14" r="11" fill="none" stroke="#e5e7eb" stroke-width="3"/><circle cx="14" cy="14" r="11" fill="none" stroke="<?= $usc_color ?>" stroke-width="3" stroke-dasharray="<?= round(2*M_PI*11*$usc/100,1) ?> <?= round(2*M_PI*11,1) ?>" stroke-linecap="round" transform="rotate(-90 14 14)"/></svg>
          <span style="font:700 12px var(--num);color:<?= $usc_color ?>"><?= $usc ?></span>
          <span style="font:500 10px var(--body);color:<?= $usc_color ?>"><?= $usc_lbl ?></span>
        </div>
        <?php endif; ?>
        <span class="tbl-badge <?= $usr['activo']?'badge-on':'badge-off' ?>"><?= $usr['activo']?'Activo':'Inactivo' ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <button class="add-row-btn" onclick="nuevoUsuario()">+ Nuevo usuario</button>

</div><!-- /panel-usuarios -->


<!-- ══ TAB: RADAR ════════════════════════════════════════════ -->
<div class="tab-panel <?= $tab_activo==='radar'?'on':'' ?>" id="panel-radar">

  <!-- Sensibilidad -->
  <div class="sec-lbl">Sensibilidad del radar</div>
  <p style="font:400 13px var(--body);color:var(--t3);margin-bottom:14px;line-height:1.6">Controla qué tan exigente es el radar para asignar cada bucket. <strong style="color:var(--text)">Agresivo</strong> muestra más cotizaciones con menos señales. <strong style="color:var(--text)">Ligero</strong> solo muestra las más sólidas.</p>

  <div class="radar-modos">
    <?php
    $modos = [
        'agresivo' => ['ico'=>ico('fire',16,'#dc2626'),'tit'=>'Agresivo','sub'=>'Más cotizaciones visibles. Ideal cuando el volumen es bajo y no quieres perder ninguna señal.'],
        'medio'    => ['ico'=>ico('target',16,'#2563eb'),'tit'=>'Medio',    'sub'=>'Balance entre precisión y cobertura. Recomendado para la mayoría de los casos.'],
        'ligero'   => ['ico'=>ico('target',16,'#16a34a'),'tit'=>'Ligero',   'sub'=>'Solo muestra señales muy sólidas. Reduce ruido cuando tienes muchas cotizaciones activas.'],
    ];
    foreach ($modos as $mk => $mv):
    ?>
    <div class="radar-modo <?= $radar_modo===$mk?'on':'' ?>"
         onclick="selModo('<?= $mk ?>',this)" data-modo="<?= $mk ?>">
      <div class="radar-modo-tit"><?= $mv['ico'] ?> <?= $mv['tit'] ?></div>
      <div class="radar-modo-sub"><?= $mv['sub'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <input type="hidden" id="r_modo" value="<?= e($radar_modo) ?>">

  <!-- FIT Calibración -->
  <div class="sec-lbl">Calibración FIT</div>
  <div class="card" style="margin-bottom:20px">
    <?php if (!empty($fit_bandas)): ?>
    <div class="fit-bandas">
      <?php foreach ($fit_bandas as $b):
        $tc = (float)($b['tasa_cierre'] ?? 0) * 100;
        $col = $tc >= 20 ? 'var(--g)' : ($tc >= 10 ? '#b45309' : 'var(--t2)');
      ?>
      <div class="fit-banda">
        <div class="fit-banda-lbl"><?= e($b['label'] ?? '') ?></div>
        <div class="fit-banda-val" style="color:<?= $col ?>"><?= round($tc,1) ?>%</div>
        <div style="font:400 10px var(--num);color:var(--t3);margin-top:2px"><?= (int)($b['total']??0) ?> cots</div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="padding:20px;text-align:center;color:var(--t3);font:400 13px var(--body)">Sin calibración aún — recalibra con tus datos actuales</div>
    <?php endif; ?>
    <div style="padding:12px 16px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div>
        <div style="font:600 13px var(--body);margin-bottom:2px">Recalibrar con datos actuales</div>
        <div style="font:400 12px var(--body);color:var(--t3)">Recomendado cada vez que cierres 5+ ventas nuevas</div>
      </div>
      <button onclick="recalibrarFit()" id="btnRecalibrar" style="padding:9px 18px;border-radius:var(--r-sm);border:none;background:var(--g);font:700 13px var(--body);color:#fff;cursor:pointer">Recalibrar ahora</button>
    </div>
  </div>

  <!-- Calibración automática -->
  <div class="card" style="margin-bottom:20px">
    <div class="field-row h">
      <div>
        <div class="field-lbl">Calibración automática</div>
        <div class="field-sub">El sistema recalibra solo cada vez que acumulas 10 nuevas ventas cerradas</div>
      </div>
      <label class="toggle">
        <input type="checkbox" id="r_auto_calibrar" <?= $radar_auto_cal?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
  </div>

  <!-- Buckets activos -->
  <div class="sec-lbl">Buckets activos</div>
  <p style="font:400 13px var(--body);color:var(--t3);margin-bottom:14px;line-height:1.6">Los desactivados no se calculan ni aparecen en el Radar.</p>
  <div class="card" style="margin-bottom:20px">
    <?php
    $buckets_def = [
        'cierre'           => ['ico'=>ico('fire',14,'#dc2626'),'tit'=>'Probable cierre / Cierre inminente',   'sub'=>'Actividad reciente + señales fuertes de intención'],
        'validando_precio' => ['ico'=>ico('money',14,'#d97706'),'tit'=>'Validando precio',                      'sub'=>'Foco en totales, loops de precio y revisitas'],
        'decision_activa'  => ['ico'=>ico('target',14,'#2563eb'),'tit'=>'Decisión activa',                       'sub'=>'Múltiples vistas con span de horas entre ellas'],
        'multi_persona'    => ['ico'=>ico('eye',14,'#2563eb'),'tit'=>'Revisión multi-persona',                'sub'=>'Varias IPs distintas revisando la misma cotización'],
        'revivio'          => ['ico'=>ico('purple',10),'tit'=>'Revivió / Regreso',                     'sub'=>'Volvió tras días o semanas sin actividad'],
        'enfriandose'      => ['ico'=>ico('blue',10),'tit'=>'Enfriándose',                           'sub'=>'Tuvo actividad pero no ha regresado en 48h+'],
        'hesitacion'       => ['ico'=>ico('orange',10),'tit'=>'Sobre-análisis / Hesitación',           'sub'=>'Muchas visitas, pausa prolongada, posible fricción de precio'],
    ];
    $last_b = array_key_last($buckets_def);
    foreach ($buckets_def as $bk => $bv):
      $checked = in_array($bk, $radar_buckets);
    ?>
    <div class="field-row h" style="<?= $bk===$last_b?'border-bottom:none':'' ?>">
      <div>
        <div style="font:600 13px var(--body)"><?= $bv['ico'] ?> <?= $bv['tit'] ?></div>
        <div class="field-sub"><?= $bv['sub'] ?></div>
      </div>
      <label class="toggle">
        <input type="checkbox" class="bucket-chk" data-bucket="<?= $bk ?>" <?= $checked?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Filtros de ruido -->
  <div class="sec-lbl">Filtros de ruido <span style="font:400 11px var(--body);color:var(--t3);text-transform:none;letter-spacing:0">(se sugiere no mover)</span></div>
  <div class="card" style="margin-bottom:20px">
    <div class="field-row h">
      <div>
        <div style="font:600 13px var(--body)">Excluir visitas del equipo interno</div>
        <div class="field-sub">Filtra las IPs marcadas como internas en el tab Actividad inusual del Radar</div>
      </div>
      <label class="toggle">
        <input type="checkbox" id="r_excluir_internos" <?= $radar_excl_int?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
    <div class="field-row h">
      <div>
        <div style="font:600 13px var(--body)">Filtrar bots conocidos</div>
        <div class="field-sub">Google, Bing, Meta, crawlers y scrapers comunes</div>
      </div>
      <label class="toggle">
        <input type="checkbox" id="r_filtrar_bots" <?= $radar_filtrar_bot?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
    <div class="field-row h" style="border-bottom:none">
      <div>
        <div style="font:600 13px var(--body)">Deduplicar vistas (ventana 30 min)</div>
        <div class="field-sub">Vistas de la misma IP en menos de 30 min cuentan como una sola sesión</div>
      </div>
      <label class="toggle">
        <input type="checkbox" id="r_deduplicar" <?= $radar_dedup?'checked':'' ?>>
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
  </div>

  <div style="display:flex;justify-content:flex-end;padding-bottom:10px">
    <button class="save-btn" onclick="guardarRadar()">Guardar configuración de Radar</button>
  </div>

</div><!-- /panel-radar -->


<!-- ══ TAB: COSTOS ══════════════════════════════════════════ -->
<?php if ($plan_info['es_pagado']):
  $costos_modo = $empresa['costos_modo'] ?? 'venta';
?>
<div class="tab-panel <?= $tab_activo==='costos'?'on':'' ?>" id="panel-costos">
  <div class="sec">
    <div class="sec-lbl">Modo de costos</div>
    <p style="font-size:13px;color:var(--t3);margin:0 0 16px">Elige cómo registrar los costos de tu negocio. Esto afecta el formulario de nuevo gasto y las vistas del módulo de Costos.</p>
    <div class="card">
      <div class="costos-modo-grid">

        <!-- Opción: Por venta -->
        <label class="costos-modo-opt <?= $costos_modo==='venta'?'sel':'' ?>" onclick="selCostosModo('venta')">
          <input type="radio" name="costos_modo" value="venta" <?= $costos_modo==='venta'?'checked':'' ?> style="display:none">
          <div class="cm-icon"><?= ico('file', 28) ?></div>
          <div class="cm-name">Por venta</div>
          <div class="cm-desc">Cada costo se asigna a una venta específica. Ideal para calcular margen y rentabilidad por proyecto.</div>
          <div class="cm-check"><?= ico('check', 16) ?></div>
        </label>

        <!-- Opción: Por empresa -->
        <label class="costos-modo-opt <?= $costos_modo==='empresa'?'sel':'' ?>" onclick="selCostosModo('empresa')">
          <input type="radio" name="costos_modo" value="empresa" <?= $costos_modo==='empresa'?'checked':'' ?> style="display:none">
          <div class="cm-icon"><?= ico('chart', 28) ?></div>
          <div class="cm-name">Por empresa</div>
          <div class="cm-desc">Los costos se registran como gastos generales del negocio. Ideal para ver rentabilidad global mensual.</div>
          <div class="cm-check"><?= ico('check', 16) ?></div>
        </label>

        <?php if ($plan_info['es_business']): ?>
        <!-- Opción: Ambos (solo Business) -->
        <label class="costos-modo-opt <?= $costos_modo==='ambos'?'sel':'' ?>" onclick="selCostosModo('ambos')">
          <input type="radio" name="costos_modo" value="ambos" <?= $costos_modo==='ambos'?'checked':'' ?> style="display:none">
          <div class="cm-icon"><?= ico('shield', 28) ?></div>
          <div class="cm-name">Ambos</div>
          <div class="cm-desc">Costos avanzados: registra gastos por venta y gastos generales de empresa. Máximo control y visibilidad.</div>
          <div class="cm-badge">Business</div>
          <div class="cm-check"><?= ico('check', 16) ?></div>
        </label>
        <?php endif; ?>

      </div>

      <p class="costos-modo-note"><?= ico('alert', 14) ?> Los costos ya registrados no se eliminan al cambiar de modo. Solo afecta cómo se crean nuevos costos y qué vistas se muestran.</p>

      <button class="save-btn" style="margin-top:12px" onclick="guardarCostosModo()">Guardar modo de costos</button>
    </div>
  </div>
</div><!-- /panel-costos -->
<?php endif; ?>

<?php if ($plan_info['es_business']): ?>
<!-- ══ TAB: MARKETING ══════════════════════════════════════ -->
<div class="tab-panel <?= $tab_activo==='marketing'?'on':'' ?>" id="panel-marketing">
  <div class="sec">
    <div class="sec-lbl">Pixels de seguimiento</div>
    <p style="font-size:13px;color:var(--t3);margin:0 0 16px">Activa los pixels que uses y agrega tu ID. Los scripts se inyectan automaticamente en las vistas publicas de cotizaciones y ventas.</p>
    <div class="card">

      <!-- Meta Pixel -->
      <div class="field-row h">
        <div style="flex:1">
          <div class="field-lbl">Meta Pixel (Facebook / Instagram)</div>
          <div class="field-sub">Pixel ID de 15-16 digitos. Meta Events Manager > Data Sources > Pixel</div>
          <div id="mkt_meta_wrap" style="margin-top:8px;display:<?= empty($mkt['pixel_meta'])?'none':'block' ?>">
            <input class="field-box" id="mkt_meta" type="text" placeholder="548297463810254" maxlength="20" value="<?= e($mkt['pixel_meta'] ?? '') ?>">
            <div style="margin-top:8px">
              <div class="field-lbl">Conversions API Token <span style="font-weight:400;color:var(--t3)">(opcional)</span></div>
              <div class="field-sub">Mejora la precisión de eventos. Meta Events Manager > Settings > Conversions API > Generate Access Token</div>
              <input class="field-box" id="mkt_capi_token" type="password" placeholder="EAAxxxxxxx..." maxlength="255" value="<?= e($mkt['capi_token'] ?? '') ?>" style="margin-top:4px;font-family:var(--num)">
              <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                <label style="font-size:11px;color:var(--t3);cursor:pointer;user-select:none">
                  <input type="checkbox" onchange="document.getElementById('mkt_capi_token').type=this.checked?'text':'password'" style="vertical-align:middle"> Mostrar token
                </label>
              </div>
            </div>
          </div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="mkt_meta_on" <?= !empty($mkt['pixel_meta'])?'checked':'' ?> onchange="document.getElementById('mkt_meta_wrap').style.display=this.checked?'block':'none';if(!this.checked){document.getElementById('mkt_meta').value='';document.getElementById('mkt_capi_token').value=''}">
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>

      <!-- GA4 -->
      <div class="field-row h">
        <div style="flex:1">
          <div class="field-lbl">Google Analytics 4</div>
          <div class="field-sub">Measurement ID formato G-XXXXXXXXXX. GA4 Admin > Data Streams</div>
          <div id="mkt_ga4_wrap" style="margin-top:8px;display:<?= empty($mkt['pixel_ga4'])?'none':'block' ?>">
            <input class="field-box" id="mkt_ga4" type="text" placeholder="G-ABC1234DEF" maxlength="20" value="<?= e($mkt['pixel_ga4'] ?? '') ?>">
          </div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="mkt_ga4_on" <?= !empty($mkt['pixel_ga4'])?'checked':'' ?> onchange="document.getElementById('mkt_ga4_wrap').style.display=this.checked?'block':'none';if(!this.checked)document.getElementById('mkt_ga4').value=''">
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>

      <!-- Google Ads -->
      <div class="field-row h">
        <div style="flex:1">
          <div class="field-lbl">Google Ads — Conversion</div>
          <div class="field-sub">Conversion ID y Label. Google Ads > Herramientas > Conversiones</div>
          <div id="mkt_gads_wrap" style="margin-top:8px;display:<?= empty($mkt['pixel_gads_id'])?'none':'grid' ?>;grid-template-columns:1fr 1fr;gap:10px">
            <input class="field-box" id="mkt_gads_id" type="text" placeholder="AW-12345678901" maxlength="20" value="<?= e($mkt['pixel_gads_id'] ?? '') ?>">
            <input class="field-box" id="mkt_gads_label" type="text" placeholder="Label: AbCdEf_12345" maxlength="30" value="<?= e($mkt['pixel_gads_label'] ?? '') ?>">
          </div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="mkt_gads_on" <?= !empty($mkt['pixel_gads_id'])?'checked':'' ?> onchange="document.getElementById('mkt_gads_wrap').style.display=this.checked?'grid':'none';if(!this.checked){document.getElementById('mkt_gads_id').value='';document.getElementById('mkt_gads_label').value=''}">
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>

      <!-- TikTok -->
      <div class="field-row h">
        <div style="flex:1">
          <div class="field-lbl">TikTok Pixel</div>
          <div class="field-sub">Pixel ID. TikTok Ads Manager > Assets > Events > Web Events</div>
          <div id="mkt_tiktok_wrap" style="margin-top:8px;display:<?= empty($mkt['pixel_tiktok'])?'none':'block' ?>">
            <input class="field-box" id="mkt_tiktok" type="text" placeholder="CBGD5ABC123DEF456GH" maxlength="30" value="<?= e($mkt['pixel_tiktok'] ?? '') ?>">
          </div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="mkt_tiktok_on" <?= !empty($mkt['pixel_tiktok'])?'checked':'' ?> onchange="document.getElementById('mkt_tiktok_wrap').style.display=this.checked?'block':'none';if(!this.checked)document.getElementById('mkt_tiktok').value=''">
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>

    </div>
    <p style="font-size:12px;color:var(--t3);margin:12px 0 0;line-height:1.5">
      <?= ico('alert', 12) ?> La configuracion de cada pixel es responsabilidad del usuario. Para obtener tu ID consulta la documentacion oficial de cada plataforma. CotizaCloud no brinda soporte sobre la configuracion de pixels de terceros.
    </p>
  </div>

  <div class="sec">
    <div class="sec-lbl">Eventos que se disparan</div>
    <div class="card" style="padding:16px">
      <p style="font:400 13px var(--body);color:var(--t3);line-height:1.6;margin:0">
        Los siguientes eventos se disparan automaticamente en las vistas publicas de tus cotizaciones y ventas:
      </p>
      <table style="width:100%;margin-top:12px;font-size:12px;border-collapse:collapse">
        <tr style="border-bottom:1px solid var(--border)">
          <td style="padding:8px 0;font-weight:600">Cliente abre cotizacion</td>
          <td style="padding:8px 0;color:var(--t3)">ViewContent (Meta/TikTok) · page_view (GA4)</td>
        </tr>
        <tr style="border-bottom:1px solid var(--border)">
          <td style="padding:8px 0;font-weight:600">Cliente acepta cotizacion</td>
          <td style="padding:8px 0;color:var(--t3)">Lead (Meta) · generate_lead (GA4) · Conversion (Google Ads) · SubmitForm (TikTok)</td>
        </tr>
        <tr>
          <td style="padding:8px 0;font-weight:600">Cliente rechaza cotizacion</td>
          <td style="padding:8px 0;color:var(--t3)">QuoteRejected (Meta/GA4 custom event)</td>
        </tr>
      </table>
    </div>
  </div>

  <div style="display:flex;justify-content:flex-end;padding-bottom:10px">
    <button class="save-btn" onclick="guardarMarketing()">Guardar configuracion de Marketing</button>
  </div>

</div><!-- /panel-marketing -->

<!-- ══ TAB: HISTORIAL ═══════════════════════════════════════ -->
<div class="tab-panel <?= $tab_activo==='historial'?'on':'' ?>" id="panel-historial">

  <div style="margin-bottom:16px;font-size:13px;color:var(--t3)">
    Datos históricos importados. Se usan como base para reportes y tasa de cierre del Radar.
  </div>

  <!-- Formulario agregar mes -->
  <div class="card" style="padding:16px 20px;margin-bottom:16px">
    <div style="font:700 13px var(--body);margin-bottom:10px">Agregar mes</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end">
      <div>
        <label class="field-lbl">Año</label>
        <input class="num-in" id="h_anio" type="number" min="2018" max="2030" value="<?= date('Y') ?>" style="width:80px">
      </div>
      <div>
        <label class="field-lbl">Mes</label>
        <select id="h_mes" class="num-in" style="width:110px;padding:6px 8px">
          <option value="1">Enero</option><option value="2">Febrero</option><option value="3">Marzo</option>
          <option value="4">Abril</option><option value="5">Mayo</option><option value="6">Junio</option>
          <option value="7">Julio</option><option value="8">Agosto</option><option value="9">Septiembre</option>
          <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
        </select>
      </div>
      <div>
        <label class="field-lbl">Cotizaciones</label>
        <input class="num-in" id="h_cots" type="number" min="0" value="0" style="width:80px">
      </div>
      <div>
        <label class="field-lbl">Monto cots</label>
        <input class="num-in" id="h_cots_monto" type="number" min="0" step="0.01" value="0" style="width:110px">
      </div>
      <div>
        <label class="field-lbl">Ventas</label>
        <input class="num-in" id="h_ventas" type="number" min="0" value="0" style="width:80px">
      </div>
      <div>
        <label class="field-lbl">Monto ventas</label>
        <input class="num-in" id="h_ventas_monto" type="number" min="0" step="0.01" value="0" style="width:110px">
      </div>
      <button class="save-btn" onclick="guardarHistorial()">Agregar</button>
    </div>
  </div>

  <!-- Tabla historial -->
  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl" id="tbl-historial">
        <thead>
          <tr>
            <th>Periodo</th>
            <th style="text-align:right">Cotizaciones</th>
            <th style="text-align:right">Monto Cots</th>
            <th style="text-align:right">Ventas</th>
            <th style="text-align:right">Monto Ventas</th>
            <th style="text-align:right">Tasa Cierre</th>
            <th style="text-align:right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $meses_nombre = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
          foreach ($historial as $h):
          ?>
          <tr data-hist-id="<?= (int)$h['id'] ?>">
            <td style="font:600 13px var(--body)"><?= $meses_nombre[(int)$h['mes']] ?> <?= $h['anio'] ?></td>
            <td style="text-align:right;font:500 13px var(--num)"><?= number_format($h['cotizaciones_cantidad']) ?></td>
            <td style="text-align:right;font:500 13px var(--num)">$<?= number_format($h['cotizaciones_monto'], 0) ?></td>
            <td style="text-align:right;font:600 13px var(--num)"><?= number_format($h['ventas_cantidad']) ?></td>
            <td style="text-align:right;font:600 13px var(--num);color:var(--g)">$<?= number_format($h['ventas_monto'], 0) ?></td>
            <td style="text-align:right;font:700 13px var(--num);color:<?= $h['tasa_cierre'] >= 15 ? 'var(--g)' : ($h['tasa_cierre'] >= 8 ? 'var(--amb)' : '#c53030') ?>"><?= number_format($h['tasa_cierre'], 1) ?>%</td>
            <td style="text-align:right">
              <button class="tbl-btn del" onclick="eliminarHistorial(<?= (int)$h['id'] ?>, this)" title="Eliminar">✕</button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($historial)): ?>
          <tr><td colspan="7" style="text-align:center;padding:28px;color:var(--t3);font-size:13px">Sin datos históricos</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (!empty($historial)):
    $total_cots = array_sum(array_column($historial, 'cotizaciones_cantidad'));
    $total_ventas = array_sum(array_column($historial, 'ventas_cantidad'));
    $total_ventas_monto = array_sum(array_column($historial, 'ventas_monto'));
    $tasa_global = $total_cots > 0 ? round($total_ventas / $total_cots * 100, 1) : 0;
  ?>
  <div style="margin-top:12px;display:flex;gap:12px;flex-wrap:wrap">
    <div class="card" style="padding:12px 16px;flex:1;min-width:120px">
      <div style="font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.04em">Total cotizaciones</div>
      <div style="font:800 18px var(--num)"><?= number_format($total_cots) ?></div>
    </div>
    <div class="card" style="padding:12px 16px;flex:1;min-width:120px">
      <div style="font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.04em">Total ventas</div>
      <div style="font:800 18px var(--num)"><?= number_format($total_ventas) ?></div>
    </div>
    <div class="card" style="padding:12px 16px;flex:1;min-width:120px">
      <div style="font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.04em">Monto vendido</div>
      <div style="font:800 18px var(--num);color:var(--g)">$<?= number_format($total_ventas_monto, 0) ?></div>
    </div>
    <div class="card" style="padding:12px 16px;flex:1;min-width:120px">
      <div style="font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.04em">Tasa cierre promedio</div>
      <div style="font:800 18px var(--num)"><?= $tasa_global ?>%</div>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /panel-historial -->
<?php endif; ?>


<!-- ══ SHEET: ARTÍCULO ══════════════════════════════════════ -->
<div class="sh-overlay" id="ov-shArt" onclick="closeSheet('shArt')"></div>
<div class="bottom-sheet" id="shArt">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shArtTit">Artículo</div>
    <button class="sh-close" onclick="closeSheet('shArt')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shArtId" value="">
    <div class="sh-field">
      <div class="sh-lbl">Nombre <span style="color:var(--danger)">*</span></div>
      <input class="sh-input" type="text" id="shArtTitulo" placeholder="Nombre del artículo" maxlength="255">
    </div>
    <div class="sh-field">
      <div class="sh-lbl">SKU (opcional)</div>
      <input class="sh-input mono" type="text" id="shArtSku" placeholder="SKU-01" maxlength="60">
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Descripción (opcional)</div>
      <textarea class="sh-input" id="shArtDesc" style="min-height:70px;resize:none" oninput="autoResize(this)" placeholder="Descripción del artículo…"></textarea>
    </div>
    <div class="sh-field" style="border-bottom:none">
      <div class="sh-lbl">Precio unitario</div>
      <input class="sh-input" type="number" id="shArtPrecio" placeholder="0.00" min="0" step="0.01">
      <div class="sh-note">Precio default al agregar el artículo — editable en la cotización</div>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shArt')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarArticulo()">Guardar artículo</button>
  </div>
</div>

<!-- ══ SHEET: CLIENTE ═══════════════════════════════════════ -->
<div class="sh-overlay" id="ov-shCli" onclick="closeSheet('shCli')"></div>
<div class="bottom-sheet" id="shCli">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shCliTit">Cliente</div>
    <button class="sh-close" onclick="closeSheet('shCli')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shCliId" value="">
    <div class="sh-field sh-row2">
      <div>
        <div class="sh-lbl">Nombre <span style="color:var(--danger)">*</span></div>
        <input class="sh-input" type="text" id="shCliNombre" placeholder="Nombre completo">
      </div>
      <div>
        <div class="sh-lbl">Teléfono <span style="color:var(--danger)">*</span></div>
        <input class="sh-input mono" type="tel" id="shCliTel" placeholder="10 dígitos">
        <div class="sh-note">Sin espacios ni +52</div>
      </div>
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Email (opcional)</div>
      <input class="sh-input" type="email" id="shCliEmail" placeholder="correo@ejemplo.com">
    </div>
    <div class="sh-field" style="border-bottom:none">
      <div class="sh-lbl">Notas internas (opcional)</div>
      <textarea class="sh-input" id="shCliNotas" style="min-height:60px;resize:none" placeholder="Solo visible para el asesor…"></textarea>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shCli')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarCliente()">Guardar cliente</button>
  </div>
</div>

<!-- ══ SHEET: CUPÓN ═════════════════════════════════════════ -->
<div class="sh-overlay" id="ov-shCup" onclick="closeSheet('shCup')"></div>
<div class="bottom-sheet" id="shCup">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shCupTit">Cupón</div>
    <button class="sh-close" onclick="closeSheet('shCup')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shCupId" value="">
    <div class="sh-field sh-row2">
      <div>
        <div class="sh-lbl">Código <span style="color:var(--danger)">*</span></div>
        <input class="sh-input mono" type="text" id="shCupCodigo" placeholder="CÓDIGO"
               oninput="this.value=this.value.toUpperCase()" maxlength="60">
        <div class="sh-note">El cliente lo ingresa en la cotización</div>
      </div>
      <div>
        <div class="sh-lbl">Descuento <span style="color:var(--danger)">*</span></div>
        <div style="display:flex;align-items:center;gap:6px">
          <input class="sh-input" type="number" id="shCupPct" placeholder="0" min="0.01" max="99" step="0.01" style="text-align:right">
          <span style="font:500 15px var(--body);color:var(--t2);flex-shrink:0">%</span>
        </div>
      </div>
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Descripción (opcional)</div>
      <input class="sh-input" type="text" id="shCupDesc" placeholder="Para qué es este cupón…" maxlength="200">
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Vencimiento</div>
      <select class="sh-input" id="shCupVencTipo" onchange="toggleVencCupon(this.value)">
        <option value="nunca">No vence</option>
        <option value="dias_cotizacion">Vence N días después de creada la cotización</option>
        <option value="fecha_fija">Vence en fecha fija</option>
      </select>
    </div>
    <div class="sh-field" id="shCupVencDiasWrap" style="display:none">
      <div class="sh-lbl">Días de vigencia <span style="color:var(--danger)">*</span></div>
      <div style="display:flex;align-items:center;gap:8px">
        <input class="sh-input" type="number" id="shCupVencDias" min="1" max="365" placeholder="30" style="text-align:right;max-width:100px">
        <span style="font:500 13px var(--body);color:var(--t2)">días desde la cotización</span>
      </div>
    </div>
    <div class="sh-field" id="shCupVencFechaWrap" style="display:none">
      <div class="sh-lbl">Fecha de vencimiento <span style="color:var(--danger)">*</span></div>
      <input class="sh-input" type="date" id="shCupVencFecha">
    </div>
    <div class="sh-field" style="border-bottom:none">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <span style="font:600 13px var(--body)">Cupón activo</span>
        <label class="toggle">
          <input type="checkbox" id="shCupActivo" checked>
          <div class="toggle-track"></div><div class="toggle-thumb"></div>
        </label>
      </div>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shCup')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarCupon()">Guardar cupón</button>
  </div>
</div>

<!-- ══ SHEET: USUARIO ═══════════════════════════════════════ -->
<div class="sh-overlay" id="ov-shUsr" onclick="closeSheet('shUsr')"></div>
<div class="bottom-sheet" id="shUsr">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shUsrTit">Usuario</div>
    <button class="sh-close" onclick="closeSheet('shUsr')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shUsrId" value="">
    <div class="sh-field sh-row2">
      <div>
        <div class="sh-lbl">Nombre completo <span style="color:var(--danger)">*</span></div>
        <input class="sh-input" type="text" id="shUsrNombre" placeholder="Nombre completo">
      </div>
      <div>
        <div class="sh-lbl">Usuario <span style="color:var(--danger)">*</span></div>
        <input class="sh-input mono" type="text" id="shUsrUsuario" placeholder="usuario" maxlength="60"
               oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9._-]/g,'')">
        <div class="sh-note">Solo letras, números y . _ -</div>
      </div>
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Email (opcional)</div>
      <input class="sh-input" type="email" id="shUsrEmail" placeholder="correo@ejemplo.com">
    </div>
    <div class="sh-field">
      <div class="sh-lbl">Contraseña</div>
      <input class="sh-input" type="password" id="shUsrPass" placeholder="Mín. 8 caracteres">
      <div class="sh-note" id="shUsrPassNote">Deja en blanco para no cambiarla</div>
    </div>
    <div class="sh-field sh-row2">
      <div>
        <div class="sh-lbl">Rol</div>
        <select class="sh-select" id="shUsrRol" onchange="togglePerms(this.value)">
          <option value="asesor">Asesor</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div>
        <div class="sh-lbl">Estado</div>
        <div style="display:flex;align-items:center;gap:10px;padding-top:4px">
          <label class="toggle">
            <input type="checkbox" id="shUsrActivo" checked>
            <div class="toggle-track"></div><div class="toggle-thumb"></div>
          </label>
          <span style="font:500 13px var(--body);color:var(--t2)">Activo</span>
        </div>
      </div>
    </div>
    <div id="shPermsWrap">
      <div class="sh-field">
        <div class="sh-lbl" style="margin-bottom:10px">Permisos del asesor</div>
        <div class="perm-row">
          <div><div class="perm-lbl">Crear cotizaciones</div><div class="perm-sub">Puede crear nuevas cotizaciones</div></div>
          <label class="toggle"><input type="checkbox" id="perm_crear_cots" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Editar cotizaciones</div><div class="perm-sub">Puede editar cotizaciones existentes</div></div>
          <label class="toggle"><input type="checkbox" id="perm_editar_cots" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Ver cantidad y precio unitario</div><div class="perm-sub">Si está apagado, solo ve descripción y total</div></div>
          <label class="toggle"><input type="checkbox" id="perm_ver_cantidades" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Editar precios unitarios</div><div class="perm-sub">Sujeto al permiso global de empresa</div></div>
          <label class="toggle"><input type="checkbox" id="perm_precio" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Aplicar descuentos / cupones</div></div>
          <label class="toggle"><input type="checkbox" id="perm_descuento" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Ver todas las cotizaciones</div><div class="perm-sub">Por defecto solo ve las suyas</div></div>
          <label class="toggle"><input type="checkbox" id="perm_ver_cots"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Ver todas las ventas</div></div>
          <label class="toggle"><input type="checkbox" id="perm_ver_ventas"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Eliminar ítems de ventas</div></div>
          <label class="toggle"><input type="checkbox" id="perm_eliminar_items"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Agregar extras en ventas</div><div class="perm-sub">Agregar artículos extra a una venta</div></div>
          <label class="toggle"><input type="checkbox" id="perm_agregar_extras"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Cancelar recibos</div></div>
          <label class="toggle"><input type="checkbox" id="perm_cancelar_recibos"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Capturar pagos / abonos</div><div class="perm-sub">Registrar abonos a ventas</div></div>
          <label class="toggle"><input type="checkbox" id="perm_capturar_pagos"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Asignar cotizaciones</div><div class="perm-sub">Asignar cotizaciones a otros vendedores</div></div>
          <label class="toggle"><input type="checkbox" id="perm_asignar_cotizaciones"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
      </div>
      <div class="sh-field">
        <div class="sh-lbl" style="margin-bottom:10px">Acceso a módulos</div>
        <div class="perm-row">
          <div><div class="perm-lbl">Costos</div><div class="perm-sub">Ver costos, registrar gastos, análisis</div></div>
          <label class="toggle"><input type="checkbox" id="perm_ver_costos" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row">
          <div><div class="perm-lbl">Proveedores</div><div class="perm-sub">Ver y gestionar proveedores</div></div>
          <label class="toggle"><input type="checkbox" id="perm_ver_proveedores" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
        <div class="perm-row" style="border-bottom:none">
          <div><div class="perm-lbl">Reportes</div><div class="perm-sub">Ver reportes financieros y de cotizaciones</div></div>
          <label class="toggle"><input type="checkbox" id="perm_ver_reportes" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
        </div>
      </div>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shUsr')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarUsuario()">Guardar usuario</button>
  </div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
// ── Sheets ──────────────────────────────────────────────────
function openSheet(id) {
    const ov = document.getElementById('ov-' + id);
    const sh = document.getElementById(id);
    if(ov) ov.classList.add('open');
    if(sh) sh.classList.add('open');
    document.body.classList.add('sheet-open');
}
function closeSheet(id) {
    const ov = document.getElementById('ov-' + id);
    const sh = document.getElementById(id);
    if(ov) ov.classList.remove('open');
    if(sh) sh.classList.remove('open');
    document.body.classList.remove('sheet-open');
}

// ── Auto-resize textarea ─────────────────────────────────────
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
}
document.querySelectorAll('textarea').forEach(t => { if(t.value) autoResize(t); });

// ── Insertar variable en textarea ────────────────────────────
function insertVar(id, txt) {
    const el = document.getElementById(id);
    if (!el) return;
    const s = el.selectionStart, e = el.selectionEnd;
    el.value = el.value.slice(0,s) + txt + el.value.slice(e);
    el.selectionStart = el.selectionEnd = s + txt.length;
    el.focus(); autoResize(el);
}

// ── Impuesto ─────────────────────────────────────────────────
function selTax(modo, el) {
    document.querySelectorAll('.tax-opt').forEach(o => o.classList.remove('on'));
    el.classList.add('on');
    document.getElementById('e_impuesto_modo').value = modo;
}

// ── Radar modo ───────────────────────────────────────────────
function selModo(modo, el) {
    document.querySelectorAll('.radar-modo').forEach(m => m.classList.remove('on'));
    el.classList.add('on');
    document.getElementById('r_modo').value = modo;
}

// ── Búsqueda con debounce ────────────────────────────────────
let dbTimer;
function debounce(fn, ms) { clearTimeout(dbTimer); dbTimer = setTimeout(fn, ms); }
function buscarTab(param, val) {
    const url = new URL(window.location.href);
    url.searchParams.set(param, val);
    const tab = url.searchParams.get('tab') || 'empresa';
    url.searchParams.set('tab', param === 'q_cat' ? 'catalogo' : 'clientes');
    window.location.href = url.toString();
}

// ── Logo ─────────────────────────────────────────────────────
async function subirLogo(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { alert('El archivo no debe superar 2 MB.'); return; }
    const fd = new FormData(); fd.append('logo', file);
    try {
        const r = await fetch('/config/logo', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
        const d = await r.json();
        if (d.ok) {
            const prev = document.getElementById('logoPreview');
            prev.innerHTML = `<img src="${d.url}" style="width:100%;height:100%;object-fit:contain">`;
            // Mostrar botón quitar si no existe
            const btns = document.getElementById('logoBtns');
            if(btns && !document.getElementById('btnQuitarLogo')) {
                const b = document.createElement('button');
                b.id = 'btnQuitarLogo';
                b.className = 'logo-btn danger';
                b.textContent = 'Quitar logo';
                b.onclick = quitarLogo;
                btns.appendChild(b);
            }
            flashOk('Logo subido correctamente');
        } else alert(d.error || 'Error al subir el logo.');
    } catch(e) { alert('Error de conexión.'); }
}
async function quitarLogo() {
    if (!confirm('¿Quitar el logo?')) return;
    try {
        const r = await fetch('/config/logo/quitar', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN } });
        const d = await r.json();
        if (d.ok) { document.getElementById('logoPreview').innerHTML = '🏠'; flashOk('Logo eliminado'); }
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}

// ── Guardar defaults descuento automático ────────────────────
async function guardarDescDefaults() {
    const payload = {
        descuento_auto_pct_default: parseFloat(document.getElementById('e_desc_auto_pct').value) || 0,
        descuento_auto_dias_default: parseInt(document.getElementById('e_desc_auto_dias').value) || 3,
    };
    try {
        const r = await fetch('/config/empresa', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ ...payload, _solo_desc_defaults: true })
        });
        const d = await r.json();
        if (d.ok) flashOk('Defaults guardados');
        else alert(d.error || 'Error al guardar.');
    } catch(e) { alert('Error de conexión.'); }
}

// ── Guardar empresa ──────────────────────────────────────────
async function guardarEmpresa() {
    const payload = {
        nombre:             document.getElementById('e_nombre').value.trim(),
        ciudad:             document.getElementById('e_ciudad').value.trim(),
        telefono:           document.getElementById('e_telefono').value.trim(),
        email:              document.getElementById('e_email').value.trim(),
        direccion:          document.getElementById('e_direccion').value.trim(),
        rfc:                document.getElementById('e_rfc').value.trim(),
        website:            document.getElementById('e_website').value.trim(),
        impuesto_modo:      document.getElementById('e_impuesto_modo').value,
        impuesto_pct:       parseFloat(document.getElementById('e_impuesto_pct').value) || 0,
        notif_email:        document.getElementById('e_notif_email').value.trim(),
        notif_email_acepta: document.getElementById('e_notif_acepta').checked ? 1 : 0,
        notif_email_rechaza:document.getElementById('e_notif_rechaza').checked ? 1 : 0,
        notif_config: {
            cotizacion_aceptada:  document.getElementById('e_notif_acepta').checked,
            cotizacion_rechazada: document.getElementById('e_notif_rechaza').checked,
            abono_registrado:     document.getElementById('e_notif_abono').checked,
            radar_alerta:         document.getElementById('e_notif_radar').checked,
        },
        cot_vigencia_dias:  parseInt(document.getElementById('e_cot_vigencia_dias').value) || 30,
        allow_precio_edit:  document.getElementById('e_allow_precio_edit').checked ? 1 : 0,
        ocultar_cant_pu:    document.getElementById('e_ocultar_cant_pu').checked ? 1 : 0,
        auto_suspender_activo: document.getElementById('e_auto_suspender_activo').checked ? 1 : 0,
        auto_suspender_dias: parseInt(document.getElementById('e_auto_suspender_dias').value) || 30,
        cot_theme:          document.getElementById('e_cot_theme').value,
        cot_encabezado:     document.getElementById('e_cot_encabezado').value,
        cot_msg_acepta:     document.getElementById('e_cot_msg_acepta').value,
        cot_msg_rechaza:    document.getElementById('e_cot_msg_rechaza').value,
        cot_terminos:       document.getElementById('e_cot_terminos').value,
        cot_footer:         document.getElementById('e_cot_footer').value,
        vta_terminos:       document.getElementById('e_vta_terminos').value,
        vta_footer:         document.getElementById('e_vta_footer').value,
    };
    if (!payload.nombre) { alert('El nombre de empresa es obligatorio.'); return; }
    try {
        const r = await fetch('/config/empresa', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify(payload)
        });
        const d = await r.json();
        if (d.ok) flashOk('Cambios guardados');
        else alert(d.error || 'Error al guardar.');
    } catch(e) { alert('Error de conexión.'); }
}

// ── Theme picker ─────────────────────────────────────────────
function selTheme(btn) {
    document.querySelectorAll('.theme-btn').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    document.getElementById('e_cot_theme').value = btn.dataset.theme;
}

// ── Toggle auto-suspender días row ───────────────────────────
document.getElementById('e_auto_suspender_activo').addEventListener('change', function(){
    const row = document.getElementById('auto_susp_dias_row');
    row.style.opacity = this.checked ? '1' : '.4';
    row.style.pointerEvents = this.checked ? 'auto' : 'none';
});

// ── Guardar radar ────────────────────────────────────────────
async function guardarRadar() {
    const buckets = [];
    document.querySelectorAll('.bucket-chk:checked').forEach(c => buckets.push(c.dataset.bucket));
    const payload = {
        modo:             document.getElementById('r_modo').value,
        auto_calibrar:    document.getElementById('r_auto_calibrar').checked,
        excluir_internos: document.getElementById('r_excluir_internos').checked,
        filtrar_bots:     document.getElementById('r_filtrar_bots').checked,
        deduplicar_30min: document.getElementById('r_deduplicar').checked,
        buckets_activos:  buckets,
    };
    try {
        const r = await fetch('/config/radar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify(payload)
        });
        const d = await r.json();
        if (d.ok) flashOk('Radar guardado');
        else alert(d.error || 'Error al guardar.');
    } catch(e) { alert('Error de conexión.'); }
}

// ── Recalibrar FIT ───────────────────────────────────────────
async function recalibrarFit() {
    const btn = document.getElementById('btnRecalibrar');
    btn.disabled = true; btn.textContent = 'Calibrando…';
    try {
        const r = await fetch('/config/radar/calibrar', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN } });
        const d = await r.json();
        if (d.ok) { flashOk('Calibración completada'); setTimeout(()=>location.reload(), 800); }
        else { alert(d.error || 'Error al calibrar.'); btn.disabled=false; btn.textContent='Recalibrar ahora'; }
    } catch(e) { alert('Error de conexión.'); btn.disabled=false; btn.textContent='Recalibrar ahora'; }
}

// ── Flash mensaje ────────────────────────────────────────────
function flashOk(msg) {
    const el = document.createElement('div');
    el.textContent = '✓ ' + msg;
    Object.assign(el.style, {position:'fixed',bottom:'24px',left:'50%',transform:'translateX(-50%)',background:'var(--g)',color:'#fff',padding:'10px 24px',borderRadius:'999px',font:'700 13px var(--body)',boxShadow:'0 4px 16px rgba(0,0,0,.15)',zIndex:'999',transition:'opacity .3s'});
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),300); }, 2200);
}

// ── Artículo ─────────────────────────────────────────────────
function nuevoArticulo() {
    document.getElementById('shArtId').value    = '';
    document.getElementById('shArtTit').textContent = 'Nuevo artículo';
    document.getElementById('shArtTitulo').value= '';
    document.getElementById('shArtSku').value   = '';
    document.getElementById('shArtDesc').value  = '';
    document.getElementById('shArtPrecio').value= '';
    openSheet('shArt');
}
function editarArticulo(id, data) {
    document.getElementById('shArtId').value    = id;
    document.getElementById('shArtTit').textContent = 'Editar artículo';
    document.getElementById('shArtTitulo').value= data.titulo;
    document.getElementById('shArtSku').value   = data.sku || '';
    document.getElementById('shArtDesc').value  = data.descripcion || '';
    document.getElementById('shArtPrecio').value= data.precio;
    openSheet('shArt');
}
async function guardarArticulo() {
    const id     = document.getElementById('shArtId').value;
    const titulo = document.getElementById('shArtTitulo').value.trim();
    const sku    = document.getElementById('shArtSku').value.trim();
    const desc   = document.getElementById('shArtDesc').value.trim();
    const precio = parseFloat(document.getElementById('shArtPrecio').value) || 0;
    if (!titulo) { alert('El nombre es obligatorio.'); return; }
    const url = id ? '/config/articulo/' + id : '/config/articulo';
    try {
        const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN}, body:JSON.stringify({titulo,sku,descripcion:desc,precio}) });
        const d = await r.json();
        if (d.ok) { closeSheet('shArt'); location.reload(); }
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}
async function eliminarArticulo(id, btn) {
    if (!confirm('¿Eliminar este artículo del catálogo?')) return;
    try {
        const r = await fetch('/config/articulo/' + id + '/eliminar', { method:'POST', headers:{'X-CSRF-Token':CSRF_TOKEN} });
        const d = await r.json();
        if (d.ok) btn.closest('tr')?.remove();
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}

// ── Cliente ──────────────────────────────────────────────────
function nuevoCliente() {
    document.getElementById('shCliId').value    = '';
    document.getElementById('shCliTit').textContent = 'Nuevo cliente';
    ['shCliNombre','shCliTel','shCliEmail','shCliNotas'].forEach(i => document.getElementById(i).value = '');
    openSheet('shCli');
}
function editarCliente(id, data) {
    document.getElementById('shCliId').value       = id;
    document.getElementById('shCliTit').textContent = 'Editar cliente';
    document.getElementById('shCliNombre').value   = data.nombre;
    document.getElementById('shCliTel').value      = data.telefono;
    document.getElementById('shCliEmail').value    = data.email || '';
    document.getElementById('shCliNotas').value    = data.notas || '';
    openSheet('shCli');
}
async function guardarCliente() {
    const id     = document.getElementById('shCliId').value;
    const nombre = document.getElementById('shCliNombre').value.trim();
    const tel    = document.getElementById('shCliTel').value.trim();
    const email  = document.getElementById('shCliEmail').value.trim();
    const notas  = document.getElementById('shCliNotas').value.trim();
    if (!nombre || !tel) { alert('Nombre y teléfono son obligatorios.'); return; }
    const url = id ? '/clientes/' + id + '/guardar' : '/clientes/crear';
    try {
        const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN}, body:JSON.stringify({nombre,telefono:tel,email,notas}) });
        const d = await r.json();
        if (d.ok) { closeSheet('shCli'); location.reload(); }
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}

// ── Cupón ────────────────────────────────────────────────────
function toggleVencCupon(tipo) {
    document.getElementById('shCupVencDiasWrap').style.display   = tipo === 'dias_cotizacion' ? '' : 'none';
    document.getElementById('shCupVencFechaWrap').style.display  = tipo === 'fecha_fija'      ? '' : 'none';
}
function nuevoCupon() {
    document.getElementById('shCupId').value    = '';
    document.getElementById('shCupTit').textContent = 'Nuevo cupón';
    ['shCupCodigo','shCupPct','shCupDesc','shCupVencDias','shCupVencFecha'].forEach(i => document.getElementById(i).value = '');
    document.getElementById('shCupActivo').checked = true;
    document.getElementById('shCupVencTipo').value = 'nunca';
    toggleVencCupon('nunca');
    openSheet('shCup');
}
function editarCupon(id, data) {
    document.getElementById('shCupId').value       = id;
    document.getElementById('shCupTit').textContent = 'Editar cupón';
    document.getElementById('shCupCodigo').value   = data.codigo;
    document.getElementById('shCupPct').value      = data.porcentaje;
    document.getElementById('shCupDesc').value     = data.descripcion || '';
    document.getElementById('shCupActivo').checked = !!parseInt(data.activo);
    const tipo = data.vencimiento_tipo || 'nunca';
    document.getElementById('shCupVencTipo').value  = tipo;
    document.getElementById('shCupVencDias').value  = data.vencimiento_dias || '';
    document.getElementById('shCupVencFecha').value = data.vencimiento_fecha || '';
    toggleVencCupon(tipo);
    openSheet('shCup');
}
async function guardarCupon() {
    const id     = document.getElementById('shCupId').value;
    const codigo = document.getElementById('shCupCodigo').value.trim().toUpperCase();
    const pct    = parseFloat(document.getElementById('shCupPct').value) || 0;
    const desc   = document.getElementById('shCupDesc').value.trim();
    const activo = document.getElementById('shCupActivo').checked ? 1 : 0;
    const vencimiento_tipo  = document.getElementById('shCupVencTipo').value;
    const vencimiento_dias  = parseInt(document.getElementById('shCupVencDias').value) || null;
    const vencimiento_fecha = document.getElementById('shCupVencFecha').value || null;
    if (!codigo || pct <= 0) { alert('Código y descuento son obligatorios.'); return; }
    if (vencimiento_tipo === 'dias_cotizacion' && !vencimiento_dias) { alert('Indica los días de vigencia.'); return; }
    if (vencimiento_tipo === 'fecha_fija' && !vencimiento_fecha) { alert('Indica la fecha de vencimiento.'); return; }
    const url = id ? '/config/cupon/' + id : '/config/cupon';
    try {
        const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
            body:JSON.stringify({codigo, porcentaje:pct, descripcion:desc, activo,
                vencimiento_tipo, vencimiento_dias, vencimiento_fecha}) });
        const d = await r.json();
        if (d.ok) { closeSheet('shCup'); location.reload(); }
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}
async function eliminarCupon(id, btn) {
    if (!confirm('¿Eliminar este cupón?')) return;
    try {
        const r = await fetch('/config/cupon/' + id + '/eliminar', { method:'POST', headers:{'X-CSRF-Token':CSRF_TOKEN} });
        const d = await r.json();
        if (d.ok) btn.closest('tr')?.remove();
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}

// ── Usuario ──────────────────────────────────────────────────
function nuevoUsuario() {
    document.getElementById('shUsrId').value    = '';
    document.getElementById('shUsrTit').textContent = 'Nuevo usuario';
    ['shUsrNombre','shUsrUsuario','shUsrEmail','shUsrPass'].forEach(i => document.getElementById(i).value = '');
    document.getElementById('shUsrRol').value   = 'asesor';
    document.getElementById('shUsrActivo').checked = true;
    document.getElementById('shUsrPassNote').textContent = 'Mín. 8 caracteres';
    togglePerms('asesor');
    // Defaults permisos
    document.getElementById('perm_crear_cots').checked      = true;
    document.getElementById('perm_editar_cots').checked     = true;
    document.getElementById('perm_ver_cantidades').checked  = true;
    document.getElementById('perm_precio').checked         = true;
    document.getElementById('perm_descuento').checked      = true;
    document.getElementById('perm_ver_cots').checked       = false;
    document.getElementById('perm_ver_ventas').checked     = false;
    document.getElementById('perm_eliminar_items').checked = false;
    document.getElementById('perm_agregar_extras').checked = false;
    document.getElementById('perm_cancelar_recibos').checked= false;
    document.getElementById('perm_capturar_pagos').checked = false;
    document.getElementById('perm_asignar_cotizaciones').checked = false;
    document.getElementById('perm_ver_costos').checked = true;
    document.getElementById('perm_ver_proveedores').checked = true;
    document.getElementById('perm_ver_reportes').checked = true;
    openSheet('shUsr');
}
function editarUsuario(id, data) {
    document.getElementById('shUsrId').value       = id;
    document.getElementById('shUsrTit').textContent = 'Editar usuario';
    document.getElementById('shUsrNombre').value   = data.nombre;
    document.getElementById('shUsrUsuario').value  = data.usuario;
    document.getElementById('shUsrEmail').value    = data.email;
    document.getElementById('shUsrPass').value     = '';
    document.getElementById('shUsrPassNote').textContent = 'Deja en blanco para no cambiarla';
    document.getElementById('shUsrRol').value      = data.rol;
    document.getElementById('shUsrActivo').checked = !!parseInt(data.activo);
    document.getElementById('perm_crear_cots').checked       = !!parseInt(data.puede_crear_cotizaciones ?? 1);
    document.getElementById('perm_editar_cots').checked      = !!parseInt(data.puede_editar_cotizaciones ?? 1);
    document.getElementById('perm_ver_cantidades').checked   = !!parseInt(data.puede_ver_cantidades ?? 1);
    document.getElementById('perm_precio').checked          = !!parseInt(data.puede_editar_precios);
    document.getElementById('perm_descuento').checked       = !!parseInt(data.puede_aplicar_descuentos);
    document.getElementById('perm_ver_cots').checked        = !!parseInt(data.puede_ver_todas_cots);
    document.getElementById('perm_ver_ventas').checked      = !!parseInt(data.puede_ver_todas_ventas);
    document.getElementById('perm_eliminar_items').checked  = !!parseInt(data.puede_eliminar_items_venta);
    document.getElementById('perm_agregar_extras').checked  = !!parseInt(data.puede_agregar_extras ?? 0);
    document.getElementById('perm_cancelar_recibos').checked= !!parseInt(data.puede_cancelar_recibos);
    document.getElementById('perm_capturar_pagos').checked = !!parseInt(data.puede_capturar_pagos);
    document.getElementById('perm_asignar_cotizaciones').checked = !!parseInt(data.puede_asignar_cotizaciones);
    document.getElementById('perm_ver_costos').checked = !!parseInt(data.puede_ver_costos ?? 1);
    document.getElementById('perm_ver_proveedores').checked = !!parseInt(data.puede_ver_proveedores ?? 1);
    document.getElementById('perm_ver_reportes').checked = !!parseInt(data.puede_ver_reportes ?? 1);
    togglePerms(data.rol);
    openSheet('shUsr');
}
function togglePerms(rol) {
    document.getElementById('shPermsWrap').style.display = rol === 'asesor' ? 'block' : 'none';
}
async function guardarUsuario() {
    const id      = document.getElementById('shUsrId').value;
    const nombre  = document.getElementById('shUsrNombre').value.trim();
    const usuario = document.getElementById('shUsrUsuario').value.trim();
    const email   = document.getElementById('shUsrEmail').value.trim();
    const pass    = document.getElementById('shUsrPass').value;
    const rol     = document.getElementById('shUsrRol').value;
    const activo  = document.getElementById('shUsrActivo').checked ? 1 : 0;
    if (!nombre || !usuario) { alert('Nombre y usuario son obligatorios.'); return; }
    if (!id && pass.length < 8) { alert('La contraseña debe tener al menos 8 caracteres.'); return; }
    if (id && pass && pass.length < 8) { alert('La nueva contraseña debe tener al menos 8 caracteres.'); return; }
    const payload = {
        nombre, usuario, email, rol, activo,
        puede_crear_cotizaciones:    document.getElementById('perm_crear_cots').checked ? 1 : 0,
        puede_editar_cotizaciones:   document.getElementById('perm_editar_cots').checked ? 1 : 0,
        puede_ver_cantidades:        document.getElementById('perm_ver_cantidades').checked ? 1 : 0,
        puede_editar_precios:        document.getElementById('perm_precio').checked ? 1 : 0,
        puede_aplicar_descuentos:    document.getElementById('perm_descuento').checked ? 1 : 0,
        puede_ver_todas_cots:        document.getElementById('perm_ver_cots').checked ? 1 : 0,
        puede_ver_todas_ventas:      document.getElementById('perm_ver_ventas').checked ? 1 : 0,
        puede_eliminar_items_venta:  document.getElementById('perm_eliminar_items').checked ? 1 : 0,
        puede_agregar_extras:        document.getElementById('perm_agregar_extras').checked ? 1 : 0,
        puede_cancelar_recibos:      document.getElementById('perm_cancelar_recibos').checked ? 1 : 0,
        puede_capturar_pagos:        document.getElementById('perm_capturar_pagos').checked ? 1 : 0,
        puede_asignar_cotizaciones:  document.getElementById('perm_asignar_cotizaciones').checked ? 1 : 0,
        puede_ver_costos:            document.getElementById('perm_ver_costos').checked ? 1 : 0,
        puede_ver_proveedores:       document.getElementById('perm_ver_proveedores').checked ? 1 : 0,
        puede_ver_reportes:          document.getElementById('perm_ver_reportes').checked ? 1 : 0,
    };
    if (pass) payload.password = pass;
    const url = id ? '/config/usuario/' + id : '/config/usuario';
    try {
        const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN}, body:JSON.stringify(payload) });
        const d = await r.json();
        if (d.ok) { closeSheet('shUsr'); location.reload(); }
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}

// ── Costos modo ──
function selCostosModo(modo){
  document.querySelectorAll('.costos-modo-opt').forEach(el => {
    el.classList.toggle('sel', el.querySelector('input').value === modo);
    el.querySelector('input').checked = (el.querySelector('input').value === modo);
  });
}
async function guardarCostosModo(){
  const sel = document.querySelector('input[name="costos_modo"]:checked');
  if (!sel) return;
  try {
    const r = await fetch('/config/costos-modo', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
      body: JSON.stringify({costos_modo: sel.value})
    });
    const d = await r.json();
    if (d.ok) { location.reload(); }
    else alert(d.error || 'Error al guardar.');
  } catch(e) { alert('Error de conexión.'); }
}

// ── Marketing ──
async function guardarMarketing(){
  try {
    const r = await fetch('/config/marketing', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
      body: JSON.stringify({
        pixel_meta:       document.getElementById('mkt_meta')?.value.trim() || '',
        capi_token:       document.getElementById('mkt_capi_token')?.value.trim() || '',
        pixel_ga4:        document.getElementById('mkt_ga4')?.value.trim() || '',
        pixel_gads_id:    document.getElementById('mkt_gads_id')?.value.trim() || '',
        pixel_gads_label: document.getElementById('mkt_gads_label')?.value.trim() || '',
        pixel_tiktok:     document.getElementById('mkt_tiktok')?.value.trim() || '',
      })
    });
    const d = await r.json();
    if (d.ok) { alert('Configuracion de Marketing guardada'); }
    else alert(d.error || 'Error al guardar.');
  } catch(e) { alert('Error de conexión.'); }
}

// ── Historial mensual ───────────────────────────────────────
async function guardarHistorial() {
    const anio = parseInt(document.getElementById('h_anio').value) || 0;
    const mes  = parseInt(document.getElementById('h_mes').value) || 0;
    const cotizaciones_cantidad = parseInt(document.getElementById('h_cots').value) || 0;
    const cotizaciones_monto    = parseFloat(document.getElementById('h_cots_monto').value) || 0;
    const ventas_cantidad       = parseInt(document.getElementById('h_ventas').value) || 0;
    const ventas_monto          = parseFloat(document.getElementById('h_ventas_monto').value) || 0;

    if (!anio || !mes) { alert('Año y mes son requeridos'); return; }
    if (cotizaciones_cantidad === 0 && ventas_cantidad === 0) { alert('Ingresa al menos cotizaciones o ventas'); return; }

    try {
        const r = await fetch('/config/historial', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ anio, mes, cotizaciones_cantidad, cotizaciones_monto, ventas_cantidad, ventas_monto })
        });
        const d = await r.json();
        if (d.ok) { location.reload(); }
        else alert(d.error || 'Error al guardar.');
    } catch(e) { alert('Error de conexión.'); }
}

async function eliminarHistorial(id, btn) {
    if (!confirm('¿Eliminar este registro del historial?')) return;
    try {
        const r = await fetch('/config/historial/' + id + '/eliminar', {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        const d = await r.json();
        if (d.ok) { btn.closest('tr').remove(); }
        else alert(d.error || 'Error al eliminar.');
    } catch(e) { alert('Error de conexión.'); }
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
