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
    .topbar { position:sticky; top:0; z-index:100; background:var(--white); border-bottom:1px solid var(--border); height:54px; display:flex; align-items:center; padding:0 16px; }
    .topbar-inner { width:100%; max-width:1200px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .topbar-l { display:flex; align-items:center; gap:8px; flex:1; min-width:0; overflow:hidden; }
    .topbar-l > div { min-width:0; overflow:hidden; }
    .back-btn { width:34px; height:34px; border-radius:8px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--t2); text-decoration:none; transition:all .12s; flex-shrink:0; }
    .back-btn:hover { border-color:var(--g); color:var(--g); }
    .topbar-title { font:700 14px var(--body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; }
    .topbar-num   { font:500 11px var(--num); color:var(--t3); display:block; }
    .topbar-actions { display:flex; gap:6px; align-items:center; flex-shrink:0; }
    @media(max-width:640px) {
        .topbar-secondary { display:none !important; }
        .topbar-title { font-size:13px; }
    }

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
    @media(max-width:768px){
        /* Subir sobre la bottom nav (64px) */
        .sticky-bottom { bottom:64px; bottom:calc(64px + env(safe-area-inset-bottom)); z-index:145; }
    }
    .sticky-total-lbl { font:400 11px var(--body); color:var(--t3); }
    .sticky-total-val { font:700 20px var(--num); color:var(--text); }
    .btn-gen { width:100%; padding:13px; border-radius:var(--r-sm); border:none; background:var(--g); font:700 15px var(--body); color:#fff; cursor:pointer; margin-top:10px; }

    @media(max-width:820px) {
        .col-panel     { display:none; }
        .mobile-panel  { display:block; }
        .sticky-bottom { display:block !important; }
        /* 64px bottom nav + ~80px sticky-bottom propio */
        .page-layout   { padding:16px 0 80px; }
        .page-wrap     { padding:0 14px; }
        .item-field input, .item-field textarea { font-size:16px; }
        .item-arrow { width:34px; height:34px; font-size:14px; }
    }
    @media(min-width:821px) {
        .sticky-bottom { display:none !important; }
    }
    </style>
