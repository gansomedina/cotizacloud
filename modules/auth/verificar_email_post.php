<?php
// ============================================================
//  CotizaApp — modules/auth/verificar_email_post.php
//  POST /verificar-email — Verifica código y crea la cuenta
// ============================================================

defined('COTIZAAPP') or die;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/registro');

csrf_check();

$pendiente = $_SESSION['registro_pendiente'] ?? null;
if (!$pendiente) redirect('/registro');

$email = $pendiente['email'];

// ─── Reenviar código ─────────────────────────────────────
if (!empty($_POST['reenviar'])) {
    $rate = rate_check('verificar_reenviar', 3, 15);
    if (!$rate['ok']) {
        $_SESSION['verificar_error'] = $rate['error'];
        redirect('/verificar-email');
    }
    rate_hit('verificar_reenviar');

    // Invalidar códigos previos
    DB::execute("UPDATE email_verificacion SET verificado = 1 WHERE email = ? AND verificado = 0", [$email]);

    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    DB::execute(
        "INSERT INTO email_verificacion (email, codigo, expires_at)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))",
        [$email, $codigo]
    );

    Mailer::enviar_verificacion($email, $pendiente['nombre'], $codigo);

    $_SESSION['verificar_error'] = null;
    redirect('/verificar-email');
}

// ─── Verificar código ─────────────────────────────────────
$codigo = trim($_POST['codigo'] ?? '');

if (strlen($codigo) !== 6 || !ctype_digit($codigo)) {
    $_SESSION['verificar_error'] = 'Ingresa el código de 6 dígitos.';
    redirect('/verificar-email');
}

// Buscar código válido
$verificacion = DB::row(
    "SELECT id, intentos FROM email_verificacion
     WHERE email = ? AND codigo = ? AND verificado = 0 AND expires_at > NOW()
     ORDER BY id DESC LIMIT 1",
    [$email, $codigo]
);

if (!$verificacion) {
    // Incrementar intentos del último código
    DB::execute(
        "UPDATE email_verificacion SET intentos = intentos + 1
         WHERE email = ? AND verificado = 0 ORDER BY id DESC LIMIT 1",
        [$email]
    );

    // Verificar si se excedieron los intentos
    $ultimo = DB::row(
        "SELECT intentos FROM email_verificacion
         WHERE email = ? AND verificado = 0 ORDER BY id DESC LIMIT 1",
        [$email]
    );
    if ($ultimo && $ultimo['intentos'] >= 5) {
        $_SESSION['verificar_error'] = 'Demasiados intentos fallidos. Solicita un nuevo código.';
    } else {
        $_SESSION['verificar_error'] = 'Código incorrecto. Intenta de nuevo.';
    }
    redirect('/verificar-email');
}

// Marcar como verificado
DB::execute("UPDATE email_verificacion SET verificado = 1 WHERE id = ?", [$verificacion['id']]);

// ─── Crear empresa y usuario admin ───────────────────────
$slug_raw       = $pendiente['slug'];
$nombre_empresa = $pendiente['nombre_empresa'];
$nombre         = $pendiente['nombre'];
$password       = $pendiente['password'];
$moneda         = $pendiente['moneda'];
$impuesto_modo  = $pendiente['impuesto_modo'];
$impuesto_pct   = $pendiente['impuesto_pct'];

// Re-validar slug (pudo ser tomado mientras verificaban)
$existe = DB::val("SELECT id FROM empresas WHERE slug = ?", [$slug_raw]);
if ($existe) {
    $_SESSION['registro_errores'] = ['slug' => "El subdominio «{$slug_raw}» ya fue tomado. Elige otro."];
    $_SESSION['registro_valores'] = $pendiente;
    unset($_SESSION['registro_pendiente']);
    redirect('/registro');
}

try {
    DB::beginTransaction();

    // Fase B (23-jul): la empresa nace con el PLAN ELEGIDO en la landing y 30
    // días de prueba. Sin ?plan= (o con business, que es venta asistida) →
    // trial de Pro. Al vencer sin pago, trial_info degrada suave a Free.
    $plan_elegido = in_array($pendiente['plan_intento'] ?? '', ['lite', 'pro'], true)
        ? $pendiente['plan_intento'] : 'pro';
    $trial_vence  = date('Y-m-d', strtotime('+30 days'));

    $empresa_id = DB::insert(
        "INSERT INTO empresas
         (slug, nombre, moneda, impuesto_modo, impuesto_pct, activa, plan, plan_vence)
         VALUES (?, ?, ?, ?, ?, 1, ?, ?)",
        [$slug_raw, $nombre_empresa, $moneda, $impuesto_modo, $impuesto_pct, $plan_elegido, $trial_vence]
    );

    foreach ([
        ['Material extra',      '#3b82f6'],
        ['Mano de obra',        '#10b981'],
        ['Transporte',          '#8b5cf6'],
        ['Instalación',         '#f59e0b'],
        ['Garantía / servicio', '#06b6d4'],
    ] as [$cat_nombre, $cat_color]) {
        DB::execute(
            "INSERT INTO categorias_costos (empresa_id, nombre, color, activa) VALUES (?, ?, ?, 1)",
            [$empresa_id, $cat_nombre, $cat_color]
        );
    }

    $usuario_id = DB::insert(
        "INSERT INTO usuarios
         (empresa_id, nombre, email, password_hash, rol, activo, email_verificado)
         VALUES (?, ?, ?, ?, 'admin', 1, 1)",
        [
            $empresa_id,
            $nombre,
            $email,
            password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        ]
    );

    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    if (DEBUG) throw $e;
    $_SESSION['registro_errores'] = ['general' => 'Error al crear la cuenta. Intenta de nuevo.'];
    $_SESSION['registro_valores'] = $pendiente;
    unset($_SESSION['registro_pendiente']);
    redirect('/registro');
}

// ─── Registrar evidencia de consentimiento (Términos + Privacidad) ──
// El usuario aceptó en el formulario de registro; aquí ya existen
// empresa_id y usuario_id reales para atribuir la aceptación.
if (!empty($pendiente['acepta'])) {
    Legal::registrar_aceptacion($usuario_id, $empresa_id, $email, ['terminos', 'privacidad']);
}

// Limpiar sesión de registro pendiente
unset($_SESSION['registro_pendiente']);

// ─── Notificar al superadmin ─────────────────────
try {
    PushNotification::enviar_a_superadmin(
        'nueva_empresa',
        'Nueva empresa registrada',
        "{$nombre_empresa} ({$slug_raw}) — {$nombre} <{$email}>",
        ['url' => '/superadmin']
    );
    if (defined('SUPERADMIN_EMAIL') && SUPERADMIN_EMAIL) {
        Mailer::enviar_superadmin(
            SUPERADMIN_EMAIL,
            'nueva_empresa',
            $nombre_empresa,
            "{$slug_raw} — {$nombre} &lt;{$email}&gt;"
        );
    }
} catch (Exception $e) {}

// ─── Auto-login (Fase B): sin re-teclear la contraseña en el momento de
//     máxima intención. MISMA cadena que login_post: Auth::login crea la
//     sesión estándar y se registran las 3 señales del Escudo (visitor_id +
//     IP + device_sig — el form de verificación las recolecta con el mismo
//     JS que el login). La cadena cross-domain se omite a propósito: una
//     empresa recién nacida no tiene dominios custom. Si algo falla, cae al
//     flujo original (login manual) — nunca bloquea el registro. ───────────
try {
    $auto = Auth::login($slug_raw, $email, $password, false);
    if (!empty($auto['ok'])) {
        $visitor_id_post = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)($_POST['visitor_id'] ?? '')), 0, 64);
        $device_sig_post = substr(preg_replace('/[^a-zA-Z0-9|\/\-_., ():]/', '', (string)($_POST['device_sig'] ?? '')), 0, 120);
        if ($visitor_id_post === '') {
            $visitor_id_post = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                random_int(0,0xffff), random_int(0,0xffff), random_int(0,0xffff),
                random_int(0,0x0fff)|0x4000, random_int(0,0x3fff)|0x8000,
                random_int(0,0xffff), random_int(0,0xffff), random_int(0,0xffff)
            );
        }
        $cur_token = $auto['token'] ?? '';
        if ($device_sig_post !== '' && $cur_token !== '') {
            try {
                DB::execute(
                    "UPDATE user_sessions SET device_sig = ? WHERE token = ? AND usuario_id = ?",
                    [$device_sig_post, $cur_token, (int)$usuario_id]
                );
            } catch (Throwable $e) {}
        }
        require_once MODULES_PATH . '/radar/Radar.php';
        Radar::marcar_visitor_interno($empresa_id, $visitor_id_post, 'login', (int)$usuario_id, ip_real(), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255));
        Radar::aprender_ip_radar($empresa_id, ip_real());
        redirect('/dashboard'); // el dashboard manda al wizard /bienvenida
    }
} catch (Throwable $e) {
    error_log('[Registro] auto-login falló: ' . $e->getMessage());
}

// ─── Fallback: login manual (flujo original) ─────
$redir = '/login?nuevo=1&empresa=' . urlencode($slug_raw) . '&u=' . urlencode($email);
if (!empty($pendiente['plan_intento'])) {
    $redir .= '&plan=' . urlencode($pendiente['plan_intento']);
}
redirect($redir);
