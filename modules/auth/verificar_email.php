<?php
// ============================================================
//  CotizaApp — modules/auth/verificar_email.php
//  GET /verificar-email — Ingresar código de verificación
// ============================================================

defined('COTIZAAPP') or die;

$pendiente = $_SESSION['registro_pendiente'] ?? null;
if (!$pendiente) redirect('/registro');

$email = $pendiente['email'] ?? '';
$err   = $_SESSION['verificar_error'] ?? null;
unset($_SESSION['verificar_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Verificar email — Cotiza.cloud</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#f4f4f0; --white:#fff; --border:#e2e2dc;
            --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
            --g:#1a5c38; --g-bg:#eef7f2; --g-light:#e6f4ed; --g-border:#b8ddc8;
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
        .error-box {
            display: flex; align-items: flex-start; gap: 9px;
            background: var(--danger-bg); border: 1px solid #fca5a5;
            border-radius: var(--r-sm); padding: 10px 12px; margin-bottom: 16px;
        }
        .error-box p { font: 500 13px var(--body); color: var(--danger); line-height: 1.4; }
        .code-inputs {
            display: flex; gap: 8px; justify-content: center; margin: 24px 0;
        }
        .code-inputs input {
            width: 48px; height: 56px; text-align: center;
            font: 800 24px var(--body); color: var(--text);
            background: var(--bg); border: 2px solid var(--border);
            border-radius: var(--r-sm); outline: none; transition: border-color .15s;
        }
        .code-inputs input:focus { border-color: var(--g); }
        .btn-submit {
            width: 100%; padding: 13px; margin-top: 8px; border-radius: var(--r-sm); border: none;
            background: var(--g); font: 700 15px var(--body); color: #fff; cursor: pointer; transition: opacity .15s;
        }
        .btn-submit:hover { opacity: .88; }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; }
        .auth-link { font: 400 13px var(--body); color: var(--t3); margin-top: 16px; text-align: center; line-height: 1.6; }
        .auth-link a { color: var(--g); font-weight: 600; text-decoration: none; }
        .auth-link a:hover { text-decoration: underline; }
        .resend-row { text-align: center; margin-top: 16px; }
        .resend-btn {
            background: none; border: none; font: 600 13px var(--body);
            color: var(--g); cursor: pointer; text-decoration: underline;
        }
        .resend-btn:disabled { color: var(--t3); cursor: not-allowed; text-decoration: none; }
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

    <!-- Icono de email -->
    <div style="text-align:center;margin-bottom:24px">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--g-light);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--g)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div style="font:800 20px var(--body);letter-spacing:-.01em;margin-bottom:6px">Verifica tu email</div>
        <div style="font:400 14px var(--body);color:var(--t3);line-height:1.5">Enviamos un código de 6 dígitos a<br><strong style="color:var(--text)"><?= e($email) ?></strong></div>
    </div>

    <div class="card">

        <?php if ($err): ?>
        <div class="error-box">
            <p><?= e($err) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/verificar-email" id="verify-form">
            <?= csrf_field() ?>
            <input type="hidden" name="codigo" id="codigo-hidden">

            <div class="code-inputs">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-idx="0" autofocus>
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-idx="1">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-idx="2">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-idx="3">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-idx="4">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-idx="5">
            </div>

            <button class="btn-submit" type="submit" id="btn-submit">Verificar</button>
        </form>

        <div class="resend-row">
            <button class="resend-btn" id="resend-btn" disabled>Reenviar código (<span id="countdown">60</span>s)</button>
        </div>
    </div>

    <div class="auth-link">
        <a href="/registro">Volver al registro</a>
    </div>

</div>

<script>
// ─── Code input UX ─────────────────────────────────────────
(function() {
    var digits = document.querySelectorAll('.code-digit');
    var hidden = document.getElementById('codigo-hidden');

    function updateHidden() {
        var code = '';
        digits.forEach(function(d) { code += d.value; });
        hidden.value = code;
    }

    digits.forEach(function(inp, i) {
        inp.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && i < 5) digits[i + 1].focus();
            updateHidden();
            // Auto-submit when all 6 digits entered
            if (hidden.value.length === 6) {
                document.getElementById('btn-submit').click();
            }
        });
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && i > 0) {
                digits[i - 1].focus();
                digits[i - 1].value = '';
                updateHidden();
            }
        });
        // Handle paste
        inp.addEventListener('paste', function(e) {
            e.preventDefault();
            var data = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            for (var j = 0; j < 6 && j < data.length; j++) {
                digits[j].value = data[j];
            }
            if (data.length >= 6) digits[5].focus();
            else if (data.length > 0) digits[Math.min(data.length, 5)].focus();
            updateHidden();
            if (hidden.value.length === 6) {
                document.getElementById('btn-submit').click();
            }
        });
    });

    // Countdown for resend
    var countdown = 60;
    var countEl = document.getElementById('countdown');
    var resendBtn = document.getElementById('resend-btn');
    var timer = setInterval(function() {
        countdown--;
        countEl.textContent = countdown;
        if (countdown <= 0) {
            clearInterval(timer);
            resendBtn.disabled = false;
            resendBtn.textContent = 'Reenviar código';
        }
    }, 1000);

    resendBtn.addEventListener('click', function() {
        if (this.disabled) return;
        // Create a form and POST to /verificar-email with resend=1
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/verificar-email';
        var csrf = document.querySelector('input[name="<?= CSRF_TOKEN_NAME ?>"]');
        var csrfClone = csrf.cloneNode();
        form.appendChild(csrfClone);
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'reenviar'; inp.value = '1';
        form.appendChild(inp);
        document.body.appendChild(form);
        form.submit();
    });

    // Form submit
    document.getElementById('verify-form').addEventListener('submit', function() {
        var btn = document.getElementById('btn-submit');
        btn.disabled = true;
        btn.textContent = 'Verificando...';
    });
})();
</script>
</body>
</html>
