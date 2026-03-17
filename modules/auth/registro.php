<?php
// ============================================================
//  CotizaApp — modules/auth/registro.php
//  GET /registro (en dominio raíz) — Crear nueva empresa
// ============================================================

defined('COTIZAAPP') or die;

if (EMPRESA_ID > 0) {
    redirect('/');
}

$errores = $_SESSION['registro_errores'] ?? [];
$valores = $_SESSION['registro_valores'] ?? [];
unset($_SESSION['registro_errores'], $_SESSION['registro_valores']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Crear cuenta — Cotiza.cloud</title>
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
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { width: 100%; max-width: 440px; }

        .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; justify-content: center; }
        .logo-mark { width: 42px; height: 42px; border-radius: 12px; background: var(--g); display: flex; align-items: center; justify-content: center; }
        .logo-mark svg { width: 22px; height: 22px; fill: none; stroke: #fff; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
        .logo-name { font: 800 22px var(--body); letter-spacing: -.02em; }
        .logo-name span { color: var(--g); }

        .card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; padding: 28px 24px; box-shadow: var(--sh-md); }
        .card-title { font: 800 22px var(--body); letter-spacing: -.01em; margin-bottom: 4px; }
        .card-sub   { font: 400 14px var(--body); color: var(--t3); margin-bottom: 24px; line-height: 1.5; }

        .sec-title { font: 700 10px var(--body); letter-spacing: .08em; text-transform: uppercase; color: var(--t3); margin: 20px 0 14px; padding-top: 16px; border-top: 1px solid var(--border); }

        .field { margin-bottom: 14px; }
        .lbl   { display: block; font: 700 10px var(--body); letter-spacing: .08em; text-transform: uppercase; color: var(--t3); margin-bottom: 6px; }
        .req   { color: var(--danger); }

        .inp {
            width: 100%; background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: var(--r-sm); padding: 11px 13px;
            font: 400 15px var(--body); color: var(--text);
            outline: none; transition: border-color .15s;
        }
        .inp:focus        { border-color: var(--g); }
        .inp::placeholder { color: var(--t3); }
        .inp.error        { border-color: var(--danger); }

        .inp-group { display: flex; align-items: center; border: 1.5px solid var(--border); border-radius: var(--r-sm); background: var(--bg); overflow: hidden; transition: border-color .15s; }
        .inp-group:focus-within { border-color: var(--g); }
        .inp-group.error { border-color: var(--danger); }
        .inp-group input { flex: 1; background: transparent; border: none; padding: 11px 4px 11px 13px; font: 400 15px var(--num); color: var(--text); outline: none; min-width: 0; }
        .inp-group input::placeholder { color: var(--t3); font-family: var(--body); }
        .inp-suffix { padding: 11px 13px 11px 0; font: 400 13px var(--num); color: var(--t3); white-space: nowrap; }
        .slug-hint { font: 400 12px var(--num); color: var(--t3); margin-top: 5px; }
        .slug-hint strong { color: var(--g); font-weight: 600; }

        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .error-box { display: flex; align-items: flex-start; gap: 9px; background: var(--danger-bg); border: 1px solid #fca5a5; border-radius: var(--r-sm); padding: 10px 12px; margin-bottom: 16px; }
        .error-box p { font: 500 13px var(--body); color: var(--danger); line-height: 1.4; }
        .field-error { font: 500 12px var(--body); color: var(--danger); margin-top: 5px; }

        .btn-submit { width: 100%; padding: 14px; margin-top: 22px; border-radius: var(--r-sm); border: none; background: var(--g); font: 700 15px var(--body); color: #fff; cursor: pointer; transition: opacity .15s; }
        .btn-submit:hover { opacity: .88; }

        .auth-link { font: 400 13px var(--body); color: var(--t3); margin-top: 16px; text-align: center; }
        .auth-link strong { color: var(--text); font-weight: 600; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="logo">
        <div class="logo-mark">
            <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
        </div>
        <div class="logo-name">Cotiza<span>.cloud</span></div>
    </div>

    <div class="card">
        <div class="card-title">Crear cuenta</div>
        <div class="card-sub">Configura tu empresa en cotiza.cloud. Puedes ajustar todo después en Configuración.</div>

        <?php if (!empty($errores['general'])): ?>
        <div class="error-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p><?= e($errores['general']) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/registro">
            <?= csrf_field() ?>

            <!-- ── Empresa ── -->
            <div class="field">
                <label class="lbl">Nombre de la empresa <span class="req">*</span></label>
                <input class="inp <?= !empty($errores['nombre_empresa']) ? 'error' : '' ?>"
                       type="text" name="nombre_empresa"
                       placeholder="Ej. Cocinas Rodríguez"
                       value="<?= e($valores['nombre_empresa'] ?? '') ?>"
                       oninput="autoSlug(this.value)" autofocus>
                <?php if (!empty($errores['nombre_empresa'])): ?>
                    <div class="field-error"><?= e($errores['nombre_empresa']) ?></div>
                <?php endif; ?>
            </div>

            <div class="field">
                <label class="lbl">Subdominio <span class="req">*</span></label>
                <div class="inp-group <?= !empty($errores['slug']) ? 'error' : '' ?>">
                    <input type="text" name="slug" id="slug_input"
                           placeholder="miempresa"
                           value="<?= e($valores['slug'] ?? '') ?>"
                           oninput="previewSlug(this.value)"
                           autocapitalize="none" autocorrect="off" spellcheck="false">
                    <span class="inp-suffix">.cotiza.cloud</span>
                </div>
                <div class="slug-hint" id="slug-hint">Solo letras minúsculas, números y guiones. Mínimo 3 caracteres.</div>
                <?php if (!empty($errores['slug'])): ?>
                    <div class="field-error"><?= e($errores['slug']) ?></div>
                <?php endif; ?>
            </div>

            <!-- ── Cuenta admin ── -->
            <div class="sec-title">Tu cuenta de acceso</div>

            <div class="field">
                <label class="lbl">Tu nombre <span class="req">*</span></label>
                <input class="inp <?= !empty($errores['nombre']) ? 'error' : '' ?>"
                       type="text" name="nombre"
                       placeholder="Nombre completo"
                       value="<?= e($valores['nombre'] ?? '') ?>">
                <?php if (!empty($errores['nombre'])): ?>
                    <div class="field-error"><?= e($errores['nombre']) ?></div>
                <?php endif; ?>
            </div>

            <div class="row2">
                <div class="field">
                    <label class="lbl">Email <span class="req">*</span></label>
                    <input class="inp <?= !empty($errores['email']) ? 'error' : '' ?>"
                           type="email" name="email"
                           placeholder="admin@tuempresa.com"
                           value="<?= e($valores['email'] ?? '') ?>"
                           autocapitalize="none" autocorrect="off" spellcheck="false">
                    <?php if (!empty($errores['email'])): ?>
                        <div class="field-error"><?= e($errores['email']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label class="lbl">Contraseña <span class="req">*</span></label>
                    <input class="inp <?= !empty($errores['password']) ? 'error' : '' ?>"
                           type="password" name="password"
                           placeholder="Mín. 6 caracteres">
                    <?php if (!empty($errores['password'])): ?>
                        <div class="field-error"><?= e($errores['password']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn-submit">Crear cuenta</button>
        </form>
    </div>

    <div class="auth-link">
        ¿Ya tienes cuenta? Ingresa en <strong>tuempresa.cotiza.cloud</strong>
    </div>

</div>
<script>
function autoSlug(val) {
    const input = document.getElementById('slug_input');
    if (input._manual) return;
    const slug = val.toLowerCase()
        .replace(/[áàäâ]/g,'a').replace(/[éèëê]/g,'e')
        .replace(/[íìïî]/g,'i').replace(/[óòöô]/g,'o')
        .replace(/[úùüû]/g,'u').replace(/ñ/g,'n')
        .replace(/[^a-z0-9\s-]/g,'').trim()
        .replace(/\s+/g,'').replace(/-+/g,'').substring(0, 40);
    input.value = slug;
    updateHint(slug);
}
function previewSlug(val) {
    // Marcar como editado manualmente solo si el usuario tocó el campo
    input = document.getElementById('slug_input');
    input._manual = true;
    updateHint(val);
}
function updateHint(val) {
    const hint = document.getElementById('slug-hint');
    if (val && /^[a-z0-9]{3,60}$/.test(val)) {
        hint.innerHTML = 'Tu URL: <strong>' + val + '.cotiza.cloud</strong>';
    } else if (val.length >= 1) {
        hint.textContent = 'Solo letras minúsculas y números. Mínimo 3 caracteres.';
    } else {
        hint.textContent = 'Se genera automático con el nombre de tu empresa.';
    }
}
// Si el usuario borra el slug manualmente, volver a auto
document.addEventListener('DOMContentLoaded', function() {
    const slugInput = document.getElementById('slug_input');
    slugInput.addEventListener('input', function() {
        if (this.value === '') this._manual = false;
        updateHint(this.value);
    });
});
<?php if (!empty($valores['slug'])): ?>
updateHint('<?= e($valores['slug']) ?>');
<?php endif; ?>
</script>
</body>
</html>
