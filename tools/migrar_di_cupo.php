<?php
// ============================================================
//  Aplica migrations/add_di_cupo_permanente.sql con red de seguridad.
//
//  Por qué no correr el .sql a mano: el cambio toca un índice UNIQUE sobre
//  datos vivos, y MySQL hace DDL con auto-commit. La secuencia es DROP INDEX →
//  MODIFY → ADD INDEX; si el ADD fallara por un cliente con dos activaciones,
//  el DROP ya quedó aplicado y la tabla se queda SIN candado — o sea, cualquier
//  cliente podría acumular descuentos. Este script comprueba ANTES y aborta sin
//  tocar nada si hay conflictos.
//
//  Idempotente: si el candado ya es el nuevo, no hace nada y lo dice.
//
//    php tools/migrar_di_cupo.php               (usa /var/www/cotizacloud/config.php)
//    php tools/migrar_di_cupo.php ruta/config.php
// ============================================================
define('COTIZAAPP', 1);
$cfg = $argv[1] ?? '/var/www/cotizacloud/config.php';
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

$NUEVA = "case when `estado` <> 'cancelado' then `cliente_id` else NULL end";

function pdo(): PDO {
    static $p = null;
    if ($p === null) {
        $p = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                     DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    return $p;
}

echo "\n═══ 1. ESTADO ACTUAL DEL CANDADO ═══\n";
$col = pdo()->query(
    "SELECT GENERATION_EXPRESSION FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'desc_int_activaciones'
        AND COLUMN_NAME = 'cliente_lock'")->fetchColumn();
if ($col === false) { fwrite(STDERR, "  ✗ No existe la columna cliente_lock — ¿corriste add_di_cliente_lock.sql?\n"); exit(1); }
echo "  $col\n";
if (stripos(str_replace(' ', '', (string)$col), str_replace(' ', '', $NUEVA)) !== false) {
    echo "\n  ✓ Ya está aplicada. No hay nada que hacer.\n\n"; exit(0);
}

echo "\n═══ 2. ¿HAY CLIENTES CON DOS ACTIVACIONES VIVAS? ═══\n";
// Con la regla nueva, dos filas no-canceladas del mismo cliente chocarían.
$conf = pdo()->query(
    "SELECT empresa_id, cliente_id, COUNT(*) AS n
       FROM desc_int_activaciones WHERE estado <> 'cancelado'
      GROUP BY empresa_id, cliente_id HAVING n > 1")->fetchAll(PDO::FETCH_ASSOC);
if ($conf) {
    echo "  ✗ ABORTADO — hay " . count($conf) . " cliente(s) en conflicto:\n";
    foreach ($conf as $r) echo "      empresa {$r['empresa_id']} · cliente {$r['cliente_id']} · {$r['n']} activaciones\n";
    echo "\n  Resuélvelos primero (dejar una viva, cancelar las demás) y vuelve a correr.\n";
    echo "  NO se tocó nada.\n\n";
    exit(1);
}
echo "  ✓ Ninguno. Se puede aplicar.\n";

echo "\n═══ 3. APLICANDO ═══\n";
$pasos = [
    'soltar el índice viejo' => "ALTER TABLE desc_int_activaciones DROP INDEX uk_cliente_vivo",
    'cambiar la regla'       => "ALTER TABLE desc_int_activaciones
        MODIFY COLUMN cliente_lock INT UNSIGNED GENERATED ALWAYS AS
        (CASE WHEN estado <> 'cancelado' THEN cliente_id ELSE NULL END) STORED",
    'volver a poner el índice' => "ALTER TABLE desc_int_activaciones ADD UNIQUE KEY uk_cliente_vivo (cliente_lock)",
];
foreach ($pasos as $que => $sql) {
    try { pdo()->exec($sql); echo "  ✓ $que\n"; }
    catch (Throwable $e) {
        echo "  ✗ falló al $que: " . $e->getMessage() . "\n";
        echo "\n  ATENCIÓN: si ya se soltó el índice, la tabla está sin candado.\n";
        echo "  Aplícalo a mano antes de seguir operando:\n";
        echo "    ALTER TABLE desc_int_activaciones ADD UNIQUE KEY uk_cliente_vivo (cliente_lock);\n\n";
        exit(1);
    }
}

echo "\n═══ 4. COMPROBACIÓN FINAL ═══\n";
$idx = pdo()->query(
    "SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'desc_int_activaciones'
        AND INDEX_NAME = 'uk_cliente_vivo'")->fetchColumn();
$col2 = pdo()->query(
    "SELECT GENERATION_EXPRESSION FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'desc_int_activaciones'
        AND COLUMN_NAME = 'cliente_lock'")->fetchColumn();
echo "  índice uk_cliente_vivo presente: " . ((int)$idx > 0 ? 'sí ✓' : 'NO ✗') . "\n";
echo "  regla: $col2\n";
$libres = pdo()->query(
    "SELECT COUNT(*) FROM desc_int_activaciones WHERE cliente_lock IS NULL")->fetchColumn();
echo "  cupos libres (solo deberían ser los 'cancelado'): $libres\n\n";
exit((int)$idx > 0 ? 0 : 1);
