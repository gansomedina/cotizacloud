<?php
/**
 * Script para resetear/crear el usuario superadmin.
 * Ejecutar UNA VEZ desde CLI o navegador, luego ELIMINAR.
 *
 * php migrations/setup_superadmin.php
 */

require __DIR__ . '/../config.php';

$password = 'CotizaAdmin2026!';
$email    = 'admin@cotiza.cloud';
$hash     = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "Hash generado: $hash\n";
echo "Verificación: " . (password_verify($password, $hash) ? 'OK' : 'FALLO') . "\n\n";

// Verificar que la empresa _system existe
$system = DB::row("SELECT id FROM empresas WHERE slug = '_system'");
if (!$system) {
    echo "ERROR: Empresa _system no existe. Ejecuta primero el SQL de migración.\n";
    exit(1);
}

$system_id = (int)$system['id'];
echo "Empresa _system encontrada, id=$system_id\n";

// Verificar si ya existe el superadmin
$existing = DB::row("SELECT id, email, password_hash FROM usuarios WHERE rol = 'superadmin'");

if ($existing) {
    // Actualizar el hash
    DB::execute(
        "UPDATE usuarios SET password_hash = ?, email = ?, activo = 1 WHERE id = ?",
        [$hash, $email, (int)$existing['id']]
    );
    echo "Superadmin actualizado (id={$existing['id']}). Password reseteado.\n";
} else {
    // Crear nuevo
    $id = DB::insert(
        "INSERT INTO usuarios (empresa_id, nombre, usuario, email, password_hash, rol, activo,
            puede_editar_precios, puede_aplicar_descuentos, puede_ver_todas_cots,
            puede_ver_todas_ventas, puede_eliminar_items_venta, puede_cancelar_recibos, puede_capturar_pagos)
         VALUES (?, 'Super Admin', 'superadmin', ?, ?, 'superadmin', 1, 1, 1, 1, 1, 1, 1, 1)",
        [$system_id, $email, $hash]
    );
    echo "Superadmin creado (id=$id)\n";
}

// Verificar
$check = DB::row("SELECT id, email, password_hash, rol, activo, empresa_id FROM usuarios WHERE rol = 'superadmin'");
echo "\nVerificación final:\n";
echo "  ID: {$check['id']}\n";
echo "  Email: {$check['email']}\n";
echo "  Rol: {$check['rol']}\n";
echo "  Activo: {$check['activo']}\n";
echo "  Empresa ID: {$check['empresa_id']}\n";
echo "  Password verify: " . (password_verify($password, $check['password_hash']) ? 'OK' : 'FALLO') . "\n";

echo "\n¡Listo! Ahora puedes hacer login con:\n";
echo "  Slug: _admin\n";
echo "  Email: $email\n";
echo "  Password: $password\n";
echo "\nIMPORTANTE: Elimina este archivo después de usarlo.\n";
