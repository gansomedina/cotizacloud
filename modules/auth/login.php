<?php
// ============================================================
//  CotizaApp — modules/auth/login.php
//  GET /login — Formulario de acceso
// ============================================================

defined('COTIZAAPP') or die;

// Si ya está logueado, redirigir al dashboard
if (Auth::logueado()) {
    redirect('/');
}

// Si no hay empresa activa en este subdominio, no debería llegar aquí
// (Auth::init() ya maneja ese caso), pero por si acaso:
if (EMPRESA_ID === 0) {
    redirect(BASE_URL . '/registro');
}

$empresa = Auth::empresa();
$error   = $_GET['error'] ?? null;

// Mensajes de error desde query string
$errores = [
    'credenciales' => 'Usuario o contraseña incorrectos.',
    'inactivo'     => 'Tu cuenta está desactivada. Contacta al administrador.',
    'sesion'       => 'Tu sesión expiró. Ingresa de nuevo.',
];

$msg_error  = $error && isset($errores[$error]) ? $errores[$error] : null;
$cuenta_nueva = isset($_GET['nuevo']) && $_GET['nuevo'] === '1';
$usuario_nuevo = e($_GET['u'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Ingresar — <?= e($empresa['nombre']) ?></title>
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
            width: 42px; height: 42px; border-radius: 12px;
            background: var(--g);
            display: flex; align-items: center; justify-content: center;
        }
        .logo-mark svg { width: 22px; height: 22px; fill: none; stroke: #fff; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
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

        .subdomain-badge {
            display: flex; align-items: center; gap: 8px;
            background: var(--g-bg);
            border: 1px solid var(--g-border);
            border-radius: var(--r-sm);
            padding: 9px 13px;
            margin-bottom: 20px;
        }
        .subdomain-badge .dot { width: 6px; height: 6px; border-radius: 3px; background: var(--g); flex-shrink: 0; }
        .subdomain-badge .empresa-n { font: 700 14px var(--body); color: var(--g); }
        .subdomain-badge .dominio   { font: 400 13px var(--num); color: var(--t3); }

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
            <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
        </div>
        <div class="logo-name">Cotiza<span>App</span></div>
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
        <div class="card-title">Bienvenido de vuelta</div>
        <div class="card-sub">Ingresa a tu cuenta</div>

        <div class="subdomain-badge">
            <div class="dot"></div>
            <div>
                <div class="empresa-n"><?= e($empresa['nombre']) ?></div>
                <div class="dominio"><?= e(EMPRESA_SLUG) ?>.cotiza.cloud</div>
            </div>
        </div>

        <?php if ($msg_error): ?>
        <div class="error-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p><?= e($msg_error) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/login" id="login-form">
            <?= csrf_field() ?>
            <!-- visitor_id capturado desde localStorage — se registra como interno al hacer login exitoso -->
            <input type="hidden" id="vid_field" name="visitor_id" value="">

            <div class="field">
                <label class="lbl" for="email">Email</label>
                <input
                    class="inp <?= $msg_error ? 'error' : '' ?>"
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
                    class="inp <?= $msg_error ? 'error' : '' ?>"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button class="btn-submit" type="submit" id="btn-submit">Entrar</button>
        </form>
    </div>

    <div class="auth-link">
        ¿Tu empresa no tiene cuenta? <a href="<?= BASE_URL ?>/registro">Crear cuenta nueva</a>
    </div>

</div>

<script>
// ─── Capturar visitor_id del navegador antes de enviar ───────────
// Mismo mecanismo que en el portal de cotizaciones:
// localStorage PRIMARY → cookie FALLBACK → generar nuevo UUID.
// Al llegar al servidor se registra en radar_visitors_internos.
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
        document.cookie = n + '=' + encodeURIComponent(v) + '; path=/; max-age=' + sec + '; SameSite=Lax';
    }
    function lsGet(k) { try { return localStorage.getItem(k) || ''; } catch(e) { return ''; } }
    function lsSet(k, v) { try { localStorage.setItem(k, v); } catch(e) {} }

    // Obtener o crear visitor_id — mismo key que el portal público
    var lk = 'cz_visitor_id', ck = 'cz_vid';
    var vid = lsGet(lk) || getCookie(ck) || uuidv4();
    lsSet(lk, vid);
    setCookie(ck, vid, 60 * 60 * 24 * 730); // 2 años

    // Inyectar en el campo hidden
    var f = document.getElementById('vid_field');
    if (f) f.value = vid;
})();

document.getElementById('login-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.textContent = 'Ingresando...';
});
</script>
</body>
</html>
