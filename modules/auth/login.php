<?php
// ============================================================
//  CotizaApp — modules/auth/login.php
//  GET /login — Login centralizado con campo de empresa
// ============================================================

defined('COTIZAAPP') or die;

// Si ya está logueado, redirigir al dashboard
if (Auth::logueado()) {
    redirect('/dashboard');
}

$error   = $_GET['error'] ?? null;
$errores = [
    'credenciales' => 'Usuario o contraseña incorrectos.',
    'empresa'      => 'Empresa no encontrada. Verifica tu subdominio.',
    'inactivo'     => 'Tu cuenta está desactivada. Contacta al administrador.',
    'sesion'       => 'Tu sesión expiró. Ingresa de nuevo.',
    'rate'         => 'Demasiados intentos. Espera unos minutos e intenta de nuevo.',
];

$msg_error    = $error && isset($errores[$error]) ? $errores[$error] : null;
$cuenta_nueva = isset($_GET['nuevo']) && $_GET['nuevo'] === '1';
$usuario_nuevo = e($_GET['u'] ?? '');
$empresa_pre   = e($_GET['empresa'] ?? $_POST['empresa_slug'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Ingresar — Cotiza.cloud</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#f4f4f0; --white:#fff; --border:#e2e2dc; --border2:#c8c8c0;
            --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
            --g:#1a5c38; --g-bg:#eef7f2; --g-border:#b8ddc8; --g-light:#e6f4ed;
            --danger:#c53030; --danger-bg:#fff5f5;
            --r:12px; --r-sm:9px;
            --sh-md:0 4px 16px rgba(0,0,0,.08);
            --body:'Plus Jakarta Sans',sans-serif;
            --num:'DM Sans',sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--body);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        .wrap { width: 100%; max-width: 420px; }

        .logo {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 32px; justify-content: center;
        }
        .logo-mark {
            width: 44px; height: 44px; border-radius: 12px;
            background: var(--g);
            display: flex; align-items: center; justify-content: center;
        }
        .logo-mark svg { width: 36px; height: 28px; }
        .logo-name { font: 800 22px var(--body); letter-spacing: -.02em; }
        .logo-name span { color: var(--g); }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px 24px;
            box-shadow: var(--sh-md);
        }
        .card-title { font: 800 20px var(--body); letter-spacing: -.01em; margin-bottom: 4px; }
        .card-sub   { font: 400 14px var(--body); color: var(--t3); margin-bottom: 24px; line-height: 1.5; }

        .error-box {
            display: flex; align-items: flex-start; gap: 9px;
            background: var(--danger-bg);
            border: 1px solid #fca5a5;
            border-radius: var(--r-sm);
            padding: 10px 12px;
            margin-bottom: 16px;
        }
        .error-box svg { flex-shrink: 0; margin-top: 1px; }
        .error-box p   { font: 500 13px var(--body); color: var(--danger); line-height: 1.4; }

        .field        { margin-bottom: 16px; }
        .lbl          { display: block; font: 700 10px var(--body); letter-spacing: .08em; text-transform: uppercase; color: var(--t3); margin-bottom: 6px; }
        .inp {
            width: 100%; background: var(--bg); border: 1.5px solid var(--border);
            border-radius: var(--r-sm); padding: 11px 13px;
            font: 400 15px var(--body); color: var(--text);
            outline: none; transition: border-color .15s;
        }
        .inp:focus      { border-color: var(--g); }
        .inp::placeholder { color: var(--t3); }
        .inp.error      { border-color: var(--danger); }

        /* Campo empresa con sufijo fijo */
        .empresa-field {
            display: flex; align-items: stretch;
            background: var(--bg); border: 1.5px solid var(--border);
            border-radius: var(--r-sm); overflow: hidden;
            transition: border-color .15s;
        }
        .empresa-field:focus-within { border-color: var(--g); }
        .empresa-field.error { border-color: var(--danger); }
        .empresa-field input {
            flex: 1; border: none; background: transparent;
            padding: 11px 2px 11px 13px;
            font: 400 15px var(--body); color: var(--text);
            outline: none; min-width: 0;
        }
        .empresa-field input::placeholder { color: var(--t3); }
        .empresa-field .suffix {
            display: flex; align-items: center;
            padding: 0 13px 0 2px;
            font: 500 14px var(--num); color: var(--t3);
            white-space: nowrap; user-select: none;
        }

        /* Recordarme */
        .remember-row {
            display: flex; align-items: center; gap: 8px;
            margin-top: 4px;
        }
        .remember-row input[type="checkbox"] {
            width: 16px; height: 16px; accent-color: var(--g);
            cursor: pointer;
        }
        .remember-row label {
            font: 400 13px var(--body); color: var(--t2); cursor: pointer;
        }

        .btn-submit {
            width: 100%; padding: 13px; margin-top: 20px;
            border-radius: var(--r-sm); border: none;
            background: var(--g);
            font: 700 15px var(--body); color: #fff;
            cursor: pointer; transition: opacity .15s;
        }
        .btn-submit:hover    { opacity: .88; }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; }

        .auth-link {
            font: 400 13px var(--body); color: var(--t3);
            margin-top: 16px; text-align: center; line-height: 1.5;
        }
        .auth-link a { color: var(--g); font-weight: 600; text-decoration: none; }
        .auth-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="logo">
        <div class="logo-mark">
            <svg viewBox="0 0 60 48" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M48.5 38H14c-5.5 0-10-4.5-10-10 0-4.8 3.4-8.8 8-9.8C12.2 12.5 17.5 8 24 8c5.2 0 9.7 3 12 7.3C37.3 14.5 39 14 41 14c5.5 0 10 4.5 10 10 0 .7-.1 1.3-.2 2C54.3 27.5 57 31 57 35c0 1-.2 2-.5 3" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="33" cy="26" r="12" stroke="rgba(255,255,255,.5)" stroke-width="1.5"/>
              <circle cx="33" cy="26" r="8" stroke="rgba(255,255,255,.65)" stroke-width="1.5"/>
              <circle cx="33" cy="26" r="4" stroke="rgba(255,255,255,.8)" stroke-width="1.5"/>
              <line x1="33" y1="14" x2="33" y2="38" stroke="rgba(255,255,255,.3)" stroke-width="1"/>
              <line x1="21" y1="26" x2="45" y2="26" stroke="rgba(255,255,255,.3)" stroke-width="1"/>
              <line x1="33" y1="26" x2="42" y2="18" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
              <circle cx="33" cy="26" r="1.8" fill="#4ade80"/>
            </svg>
        </div>
        <div class="logo-name">Cotiza<span>.cloud</span></div>
    </div>

    <?php if ($cuenta_nueva): ?>
    <div style="text-align:center;margin-bottom:24px">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--g-light);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--g)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <div style="font:800 20px var(--body);letter-spacing:-.01em;margin-bottom:6px">¡Cuenta creada!</div>
        <div style="font:400 14px var(--body);color:var(--t3);line-height:1.5">Ahora ingresa con tu usuario y contraseña.</div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title">Iniciar sesión</div>
        <div class="card-sub">Ingresa tus datos para acceder</div>

        <?php if ($msg_error): ?>
        <div class="error-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p><?= e($msg_error) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/login" id="login-form">
            <?= csrf_field() ?>
            <input type="hidden" id="vid_field" name="visitor_id" value="">
            <input type="hidden" id="dsig_field" name="device_sig" value="">

            <div class="field">
                <label class="lbl" for="empresa_slug">Tu empresa</label>
                <div class="empresa-field <?= $error === 'empresa' ? 'error' : '' ?>">
                    <input
                        type="text"
                        id="empresa_slug"
                        name="empresa_slug"
                        value="<?= $empresa_pre ?>"
                        placeholder="miempresa"
                        autocomplete="organization"
                        autocapitalize="none"
                        spellcheck="false"
                        required
                    >
                    <div class="suffix">.cotiza.cloud</div>
                </div>
            </div>

            <div class="field">
                <label class="lbl" for="email">Email</label>
                <input
                    class="inp <?= $error === 'credenciales' ? 'error' : '' ?>"
                    type="email"
                    id="email"
                    name="usuario"
                    value="<?= e($_POST['usuario'] ?? ($cuenta_nueva ? $usuario_nuevo : '')) ?>"
                    placeholder="tu@email.com"
                    autocomplete="username"
                    autocapitalize="none"
                    required
                >
            </div>

            <div class="field">
                <label class="lbl" for="password">Contraseña</label>
                <input
                    class="inp <?= $error === 'credenciales' ? 'error' : '' ?>"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>

            <div style="display:flex;align-items:center;justify-content:flex-end;margin-top:4px">
                <a href="/recuperar" style="font:500 12px var(--body);color:var(--g);text-decoration:none">¿Olvidaste tu contraseña?</a>
            </div>

            <button class="btn-submit" type="submit" id="btn-submit">Entrar</button>
        </form>
    </div>

    <div class="auth-link" id="registro-link">
        ¿Tu empresa no tiene cuenta? <a href="/registro">Crear cuenta nueva</a>
    </div>
    <script>
    // Ocultar registro en app iOS (Apple Guideline 3.1.1)
    if (window.Capacitor || navigator.userAgent.includes('CotizaCloud')) {
        var el = document.getElementById('registro-link');
        if (el) el.style.display = 'none';
    }
    </script>

</div>

<script>
// ─── Capturar visitor_id del navegador ───────────────────────
(function() {
    function uuidv4() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }
    function getCookie(n) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + n.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : '';
    }
    function setCookie(n, v, sec) {
        // domain=.cotiza.cloud para que la cookie sea visible en empresa.cotiza.cloud
        var d = location.hostname, dom = '';
        if (d === 'cotiza.cloud' || d.endsWith('.cotiza.cloud')) dom = '; domain=.cotiza.cloud';
        document.cookie = n + '=' + encodeURIComponent(v) + '; path=/' + dom + '; max-age=' + sec + '; SameSite=Lax';
    }
    function lsGet(k) { try { return localStorage.getItem(k) || ''; } catch(e) { return ''; } }
    function lsSet(k, v) { try { localStorage.setItem(k, v); } catch(e) {} }

    var lk = 'cz_visitor_id', ck = 'cz_vid';
    var vid = lsGet(lk) || getCookie(ck) || uuidv4();
    lsSet(lk, vid);
    setCookie(ck, vid, 60 * 60 * 24 * 730);

    var f = document.getElementById('vid_field');
    if (f) f.value = vid;

    // Device signature para descarte del Radar
    function getDeviceSig() {
        try {
            var sw = Math.min(screen.width, screen.height);
            var sh = Math.max(screen.width, screen.height);
            var dpr = window.devicePixelRatio || 1;
            var tp = navigator.maxTouchPoints || 0;
            var maxTex = 0;
            try { var c = document.createElement('canvas'), gl = c.getContext('webgl'); if (gl) maxTex = gl.getParameter(gl.MAX_TEXTURE_SIZE) || 0; } catch(e) {}
            var lang = navigator.language || '';
            var tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            var hc = Intl.DateTimeFormat().resolvedOptions().hourCycle || '';
            var motion = window.matchMedia('(prefers-reduced-motion:reduce)').matches ? 1 : 0;
            var contrast = window.matchMedia('(prefers-contrast:more)').matches ? 1 : 0;
            var inverted = window.matchMedia('(inverted-colors:inverted)').matches ? 1 : 0;
            var transp = window.matchMedia('(prefers-reduced-transparency:reduce)').matches ? 1 : 0;
            var iosM = (navigator.userAgent.match(/OS (\d+)/) || [])[1] || '0';
            return [sw,sh,dpr,tp,maxTex,lang,tz.split('/').pop()||tz,hc,motion,contrast,inverted,transp,iosM].join('|');
        } catch(e) { alert('DSig error: ' + e.message); return ''; }
    }
    var df = document.getElementById('dsig_field');
    if (df) {
        var dsigVal = getDeviceSig();
        df.value = dsigVal;
        if (!dsigVal) alert('DSig: vacío');
    }

    // Recordar último slug de empresa usado
    var sk = 'cz_empresa_slug';
    var slugField = document.getElementById('empresa_slug');
    if (slugField && !slugField.value) {
        slugField.value = lsGet(sk) || '';
    }
    document.getElementById('login-form').addEventListener('submit', function() {
        try { var df2 = document.getElementById('dsig_field'); if (df2 && !df2.value) df2.value = getDeviceSig(); } catch(e) {}
        if (slugField.value) lsSet(sk, slugField.value.trim().toLowerCase());
        var btn = document.getElementById('btn-submit');
        btn.disabled = true;
        btn.textContent = 'Ingresando...';
    });

    // Detectar app Capacitor y marcar el formulario
    if (window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform()) {
        var h = document.createElement('input');
        h.type = 'hidden'; h.name = 'is_app'; h.value = '1';
        document.getElementById('login-form').appendChild(h);
    }
})();
</script>
</body>
</html>
