<?php
// ============================================================
//  Solicitar licencia — POST /solicitar-licencia
//  Endpoint público para empresas suspendidas/vencidas
//  Crea un ticket de soporte tipo "Solicitud de licencia"
// ============================================================
defined('COTIZAAPP') or die;

$slug     = trim($_POST['slug'] ?? '');
$duracion = trim($_POST['duracion'] ?? '1_mes');
$mensaje  = trim($_POST['mensaje'] ?? '');

if ($slug === '') {
    http_response_code(400);
    die('Datos inválidos');
}

$empresa = DB::row("SELECT id, nombre, slug FROM empresas WHERE slug = ?", [$slug]);
if (!$empresa) {
    http_response_code(404);
    die('Empresa no encontrada');
}

$duracion_txt = match ($duracion) {
    '1_mes'   => '1 mes',
    '3_meses' => '3 meses',
    '6_meses' => '6 meses',
    '1_anio'  => '1 año',
    default   => '1 mes',
};

// Auto-crear tabla si no existe
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

// Buscar el admin de la empresa para usuario_id
$admin_id = (int)DB::val(
    "SELECT id FROM usuarios WHERE empresa_id = ? AND rol = 'admin' ORDER BY id ASC LIMIT 1",
    [$empresa['id']]
);
if (!$admin_id) {
    $admin_id = (int)DB::val(
        "SELECT id FROM usuarios WHERE empresa_id = ? ORDER BY id ASC LIMIT 1",
        [$empresa['id']]
    );
}

$desc = "Solicitud de activación de licencia PRO.\n\nDuración solicitada: {$duracion_txt}";
if ($mensaje !== '') {
    $desc .= "\n\nMensaje del cliente:\n{$mensaje}";
}

DB::insert(
    "INSERT INTO tickets_soporte (empresa_id, usuario_id, titulo, descripcion)
     VALUES (?, ?, ?, ?)",
    [$empresa['id'], $admin_id ?: 0, 'Solicitud de licencia PRO — ' . $duracion_txt, $desc]
);

// Mostrar confirmación
http_response_code(200);
echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">';
echo '<title>Solicitud enviada — CotizaCloud</title>';
echo '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">';
echo '<style>';
echo '*{box-sizing:border-box;margin:0;padding:0}';
echo 'body{font-family:"Plus Jakarta Sans",sans-serif;background:#f4f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}';
echo '.card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:480px;width:100%;padding:48px 40px;text-align:center}';
echo '.icon{width:64px;height:64px;border-radius:50%;background:#eef7f2;display:flex;align-items:center;justify-content:center;margin:0 auto 24px}';
echo '.icon svg{width:32px;height:32px;stroke:#1a5c38;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}';
echo 'h1{font-size:22px;font-weight:800;color:#1a1a18;margin-bottom:8px}';
echo 'p{font-size:14px;color:#4a4a46;line-height:1.6;margin-bottom:20px}';
echo '.back{display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border-radius:9px;font:600 14px "Plus Jakarta Sans",sans-serif;background:#1a5c38;color:#fff;text-decoration:none;transition:opacity .12s}';
echo '.back:hover{opacity:.85}';
echo '</style></head><body>';
echo '<div class="card">';
echo '<div class="icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>';
echo '<h1>Solicitud enviada</h1>';
echo '<p>Hemos recibido tu solicitud de licencia PRO (' . e($duracion_txt) . '). Te contactaremos a la brevedad con la liga de cobro para activar tu licencia.</p>';
echo '<a href="javascript:history.back()" class="back">Volver</a>';
echo '</div></body></html>';
exit;
