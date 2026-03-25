<?php
// ============================================================
//  CotizaApp — modules/ayuda/ticket.php
//  POST /ayuda/ticket
//  Crear ticket de soporte
// ============================================================

defined('COTIZAAPP') or die;
csrf_check();

$usuario = Auth::usuario();
$empresa = Auth::empresa();

// ── Auto-crear tabla si no existe ──
DB::execute("CREATE TABLE IF NOT EXISTS tickets_soporte (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id    INT UNSIGNED NOT NULL,
    usuario_id    INT UNSIGNED NOT NULL,
    titulo        VARCHAR(255) NOT NULL,
    descripcion   TEXT NOT NULL,
    imagen_url    VARCHAR(500) DEFAULT NULL,
    estado        ENUM('abierto','en_proceso','cerrado') NOT NULL DEFAULT 'abierto',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_empresa (empresa_id),
    INDEX idx_estado  (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ── Validar campos ──
$titulo      = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($titulo === '' || $descripcion === '') {
    flash('error', 'El título y la descripción son obligatorios.');
    redirect('/ayuda#soporte');
}

if (mb_strlen($titulo) > 255) {
    flash('error', 'El título no puede exceder 255 caracteres.');
    redirect('/ayuda#soporte');
}

// ── Subir imagen (opcional) ──
$imagen_url = null;
if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $upload = upload_archivo($_FILES['imagen'], $empresa['id'], 'tickets');
    if (!$upload['ok']) {
        flash('error', $upload['error']);
        redirect('/ayuda#soporte');
    }
    $imagen_url = $upload['url'];
}

// ── Guardar ticket ──
DB::insert(
    "INSERT INTO tickets_soporte (empresa_id, usuario_id, titulo, descripcion, imagen_url)
     VALUES (?, ?, ?, ?, ?)",
    [$empresa['id'], $usuario['id'], $titulo, $descripcion, $imagen_url]
);

// Si viene de solicitud de licencia, redirigir a /licencia
$es_licencia = str_contains($titulo, 'licencia PRO');

// ── Notificar al superadmin ──
try {
    $empresa_nombre = $empresa['nombre'] ?? 'Empresa';
    if ($es_licencia) {
        PushNotification::enviar_a_superadmin(
            'solicitud_licencia',
            'Solicitud de licencia PRO',
            "{$empresa_nombre} solicita renovación/activación de licencia",
            ['url' => '/superadmin']
        );
        if (defined('SUPERADMIN_EMAIL') && SUPERADMIN_EMAIL) {
            Mailer::enviar_superadmin(SUPERADMIN_EMAIL, 'solicitud_licencia', $empresa_nombre, 'Solicita renovación/activación de licencia');
        }
    } else {
        PushNotification::enviar_a_superadmin(
            'nuevo_ticket',
            'Nuevo ticket de soporte',
            "{$empresa_nombre}: {$titulo}",
            ['url' => '/superadmin']
        );
        if (defined('SUPERADMIN_EMAIL') && SUPERADMIN_EMAIL) {
            Mailer::enviar_superadmin(SUPERADMIN_EMAIL, 'nuevo_ticket', $empresa_nombre, htmlspecialchars($titulo));
        }
    }
} catch (Exception $e) {
    // No bloquear el ticket si falla la notificación
}

if ($es_licencia) {
    flash('success', 'Solicitud enviada. Te contactaremos a la brevedad con la liga de cobro.');
    redirect('/licencia');
} else {
    flash('success', 'Ticket enviado correctamente. Te contactaremos pronto.');
    redirect('/ayuda#soporte');
}
