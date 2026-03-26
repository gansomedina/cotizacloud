<?php
// ============================================================
//  CotizaApp — modules/auth/reset_password.php
//  GET /reset-password?token=... — Formulario para nueva contraseña
// ============================================================

defined('COTIZAAPP') or die;

if (Auth::logueado()) redirect('/dashboard');

$token = trim($_GET['token'] ?? '');

// Validar token
$reset = null;
if ($token) {
    try {
        $reset = DB::row(
            "SELECT pr.*, u.nombre, u.email, e.slug AS empresa_slug
             FROM password_resets pr
             JOIN usuarios u ON u.id = pr.usuario_id
             JOIN empresas e ON e.id = pr.empresa_id
             WHERE pr.token = ? AND pr.usado = 0 AND pr.expires_at > NOW()",
            [$token]
        );
    } catch (\PDOException $e) {
        // Tabla no existe
    }
}

$invalido = !$reset;
$exito = isset($_GET['exito']);
$err = $_SESSION['reset_error'] ?? null;
unset($_SESSION['reset_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Nueva contraseña — Cotiza.cloud</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#f4f4f0; --white:#fff; --border:#e2e2dc;
            --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
            --g:#1a5c38; --g-light:#e6f4ed;
            --danger:#c53030; --danger-bg:#fff5f5;
            --r-sm:9px;
            --sh-md:0 4px 16px rgba(0,0,0,.08);
            --body:'Plus Jakarta Sans',sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--body); background: var(--bg); color: var(--text);
            min-height: 100vh; display: flex; flex-direction: column;
            align-items: center; justify-content: center; padding: 20px;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { width: 100%; max-width: 420px; }
        .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 32px; justify-content: center; }
        .logo-mark {
            width: 44px; height: 44px; border-radius: 12px; background: var(--g);
            display: flex; align-items: center; justify-content: center;
        }
        .logo-mark svg { width: 36px; height: 28px; }
        .logo-name { font: 800 22px var(--body); letter-spacing: -.02em; }
        .logo-name span { color: var(--g); }
        .card {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 16px; padding: 28px 24px; box-shadow: var(--sh-md);
        }
        .card-title { font: 800 20px var(--body); letter-spacing: -.01em; margin-bottom: 4px; }
        .card-sub   { font: 400 14px var(--body); color: var(--t3); margin-bottom: 24px; line-height: 1.5; }
        .field       { margin-bottom: 16px; }
        .lbl         { display: block; font: 700 10px var(--body); letter-spacing: .08em; text-transform: uppercase; color: var(--t3); margin-bottom: 6px; }
        .inp {
            width: 100%; background: var(--bg); border: 1.5px solid var(--border);
            border-radius: var(--r-sm); padding: 11px 13px;
            font: 400 15px var(--body); color: var(--text); outline: none; transition: border-color .15s;
        }
        .inp:focus { border-color: var(--g); }
        .btn-submit {
            width: 100%; padding: 13px; margin-top: 20px; border-radius: var(--r-sm); border: none;
            background: var(--g); font: 700 15px var(--body); color: #fff; cursor: pointer; transition: opacity .15s;
        }
        .btn-submit:hover { opacity: .88; }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; }
        .auth-link { font: 400 13px var(--body); color: var(--t3); margin-top: 16px; text-align: center; }
        .auth-link a { color: var(--g); font-weight: 600; text-decoration: none; }
        .error-box {
            display: flex; align-items: flex-start; gap: 9px;
            background: var(--danger-bg); border: 1px solid #fca5a5;
            border-radius: var(--r-sm); padding: 10px 12px; margin-bottom: 16px;
        }
        .error-box p { font: 500 13px var(--body); color: var(--danger); line-height: 1.4; }
        .success-box {
            display: flex; align-items: flex-start; gap: 9px;
            background: var(--g-light); border: 1px solid #b8ddc8;
            border-radius: var(--r-sm); padding: 12px 14px; margin-bottom: 16px;
        }
        .success-box p { font: 500 13px var(--body); color: var(--g); line-height: 1.4; }
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

    <div class="card">
        <?php if ($exito): ?>
            <div class="card-title">Contraseña actualizada</div>
            <div class="success-box" style="margin-top:16px">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--g)" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                <p>Tu contraseña ha sido cambiada exitosamente. Ya puedes iniciar sesión.</p>
            </div>
            <a href="/login" class="btn-submit" style="display:block;text-align:center;text-decoration:none;margin-top:12px">Ir al login</a>

        <?php elseif ($invalido): ?>
            <div class="card-title">Enlace inválido</div>
            <div class="error-box" style="margin-top:16px">
                <p>Este enlace ha expirado o ya fue utilizado. Solicita uno nuevo.</p>
            </div>
            <a href="/recuperar" class="btn-submit" style="display:block;text-align:center;text-decoration:none;margin-top:12px">Solicitar nuevo enlace</a>

        <?php else: ?>
            <div class="card-title">Nueva contraseña</div>
            <div class="card-sub">Crea una nueva contraseña para <strong><?= e($reset['email']) ?></strong></div>

            <?php if ($err): ?>
            <div class="error-box">
                <p><?= e($err) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" action="/reset-password" id="reset-form">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <div class="field">
                    <label class="lbl" for="password">Nueva contraseña</label>
                    <input class="inp" type="password" id="password" name="password"
                           placeholder="Mínimo 6 caracteres" autocomplete="new-password" required minlength="6">
                </div>

                <div class="field">
                    <label class="lbl" for="password2">Confirmar contraseña</label>
                    <input class="inp" type="password" id="password2" name="password2"
                           placeholder="Repite la contraseña" autocomplete="new-password" required minlength="6">
                </div>

                <button class="btn-submit" type="submit" id="btn-submit">Cambiar contraseña</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="auth-link">
        <a href="/login">Volver al login</a>
    </div>

</div>

<script>
document.getElementById('reset-form')?.addEventListener('submit', function(e) {
    var p1 = document.getElementById('password').value;
    var p2 = document.getElementById('password2').value;
    if (p1 !== p2) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return;
    }
    var btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
});
</script>
</body>
</html>
