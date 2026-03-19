<?php
// ============================================================
//  Crea cuenta de prueba para Apple App Review
//  Ejecutar: php migrations/create_review_user.php
// ============================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/DB.php';

$email    = 'review@cotiza.cloud';
$password = 'Review2026!';
$nombre   = 'Apple Review';
$slug     = 'apple-review';
$empresa  = 'Apple Review Demo';

// Verificar si ya existe
$existe = DB::query("SELECT id FROM empresas WHERE slug = ?", [$slug]);
if ($existe) {
    echo "La empresa '{$slug}' ya existe (id={$existe[0]['id']}). Abortando.\n";
    exit(1);
}

try {
    DB::beginTransaction();

    // Crear empresa demo
    $empresa_id = DB::insert(
        "INSERT INTO empresas
         (slug, nombre, moneda, impuesto_modo, impuesto_pct, activa)
         VALUES (?, ?, 'MXN', 'suma', 16.00, 1)",
        [$slug, $empresa]
    );

    // Crear categorías de costos por defecto
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

    // Crear usuario admin
    $user_id = DB::insert(
        "INSERT INTO usuarios
         (empresa_id, nombre, email, password_hash, rol, activo)
         VALUES (?, ?, ?, ?, 'admin', 1)",
        [
            $empresa_id,
            $nombre,
            $email,
            password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        ]
    );

    // Crear un cliente de ejemplo
    $cliente_id = DB::insert(
        "INSERT INTO clientes (empresa_id, nombre, email, telefono)
         VALUES (?, 'Cliente Ejemplo', 'cliente@ejemplo.com', '555-0100')",
        [$empresa_id]
    );

    // Crear una cotización de ejemplo
    DB::insert(
        "INSERT INTO cotizaciones (empresa_id, usuario_id, cliente_id, folio, subtotal, impuesto, total, estado)
         VALUES (?, ?, ?, 'COT-001', 10000.00, 1600.00, 11600.00, 'enviada')",
        [$empresa_id, $user_id, $cliente_id]
    );

    DB::commit();

    echo "Cuenta de review creada exitosamente:\n";
    echo "  Empresa: {$empresa} (slug: {$slug}, id: {$empresa_id})\n";
    echo "  Usuario: {$email}\n";
    echo "  Password: {$password}\n";
    echo "  URL: https://{$slug}.cotiza.cloud/login\n";
    echo "\nUsa estas credenciales en App Store Connect > App Review Information.\n";

} catch (Exception $e) {
    DB::rollback();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
