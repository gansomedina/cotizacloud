<?php
// ============================================================
//  Crea la tabla plan_log usando la conexión de config.php
//
//  Existe para no tener que buscar usuario y contraseña de MySQL: reusa la
//  misma conexión que ya usa la aplicación.
//
//  Idempotente: el CREATE lleva IF NOT EXISTS, así que correrlo dos veces no
//  hace nada la segunda. NO borra ni altera datos.
//
//    php tools/migrar_plan_log.php config.php
// ============================================================
define('COTIZAAPP', 1);
$cfg = $argv[1] ?? '/var/www/cotizacloud/config.php';
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

$sql_file = __DIR__ . '/../migrations/add_plan_log.sql';
if (!is_file($sql_file)) { fwrite(STDERR, "No encuentro $sql_file\n"); exit(1); }

$ya = false;
try { DB::val("SELECT 1 FROM plan_log LIMIT 1"); $ya = true; } catch (Throwable $e) {}
if ($ya) {
    echo "La tabla plan_log ya existe. Nada que hacer.\n";
    exit(0);
}

// Quitar los comentarios de línea y el punto y coma final: es un solo CREATE.
$ddl = preg_replace('/^\s*--.*$/m', '', file_get_contents($sql_file));
$ddl = rtrim(trim($ddl), ';');

try {
    DB::execute($ddl);
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR al crear la tabla: " . $e->getMessage() . "\n");
    exit(1);
}

// Verificar de verdad, no asumir que el CREATE funcionó.
try {
    DB::val("SELECT 1 FROM plan_log LIMIT 1");
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: el CREATE no falló pero la tabla no responde: " . $e->getMessage() . "\n");
    exit(1);
}

$cols = 0;
try {
    $cols = (int)DB::val(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_log'");
} catch (Throwable $e) {}

echo "Tabla plan_log creada ($cols columnas).\n";
echo "\nSiguiente paso — ver qué historial se puede reconstruir (no escribe):\n";
echo "  php tools/backfill_plan_log.php " . $cfg . "\n";
