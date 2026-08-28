<?php
// ============================================================
// PRUEBA — UN DESCUENTO INTELIGENTE POR CLIENTE, EN SU VIDA.
//
// Regla de negocio (decisión CEO, 28-ago-2026): el cupo se gasta al RECIBIR el
// descuento, no al usarlo. Si el cliente dejó pasar las 24 horas sin comprar,
// NO se le devuelve — ya tuvo su oportunidad. Lo único que libera el cupo es
// que el asesor lo quite a mano ('cancelado'), que es una corrección explícita.
//
// Antes el candado soltaba también en 'vencido'. Como el vencimiento es lazy
// (solo ocurre si alguien reabre la cotización), el resultado dependía de si
// alguien la reabrió o no: unos clientes quedaban bloqueados y otros no, por
// puro accidente. Se corrigió en migrations/add_di_cupo_permanente.sql.
//
// Esto NO se puede comprobar leyendo código: la regla vive en una columna
// generada de MySQL. Así que la prueba la ejercita de verdad contra la BD.
//
// Correr: php tools/test_di_cupo.php     → debe terminar en OK
// Requiere MariaDB local con la BD simtest (usuario sim/sim), igual que las sims.
// ============================================================
$REGLA = "CASE WHEN estado <> 'cancelado' THEN cliente_id ELSE NULL END";

$pdo = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4',
               'sim', 'sim', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$ok = 0; $fail = 0;
function chk(string $t, $got, $want): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got) . " want=" . json_encode($want) . "\n"; }
}

// Tabla con la MISMA regla que la migración. El bloque 3 comprueba que el
// archivo de migración diga exactamente esto — si alguien lo cambia allá y no
// acá (o al revés), la prueba lo caza.
$pdo->exec("DROP TABLE IF EXISTS t_di_cupo");
$pdo->exec("CREATE TABLE t_di_cupo (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id    INT UNSIGNED NOT NULL,
    cotizacion_id INT UNSIGNED NOT NULL,
    cliente_id    INT UNSIGNED NOT NULL,
    cliente_lock  INT UNSIGNED GENERATED ALWAYS AS ($REGLA) STORED,
    estado        ENUM('activo','vencido','utilizado','cancelado') NOT NULL DEFAULT 'activo',
    UNIQUE KEY uk_cotizacion   (cotizacion_id),
    UNIQUE KEY uk_cliente_vivo (cliente_lock)
) ENGINE=InnoDB");

/** ¿La BD deja darle otro descuento a este cliente? */
function admite(PDO $pdo, int $cliente, int $cot): bool {
    try {
        $pdo->prepare("INSERT INTO t_di_cupo (empresa_id,cotizacion_id,cliente_id,estado)
                       VALUES (12,?,?,'activo')")->execute([$cot, $cliente]);
        return true;
    } catch (PDOException $e) {
        // 23000 es el mismo código que DescuentoInteligente::activar() ya atrapa
        // y trata como "este cliente no califica" — no hay ruta de error nueva.
        if ($e->getCode() === '23000') return false;
        throw $e;
    }
}

$pdo->prepare("INSERT INTO t_di_cupo (empresa_id,cotizacion_id,cliente_id,estado) VALUES
    (12,100,1,'utilizado'),   -- compró con su descuento
    (12,101,2,'activo'),      -- lo tiene vivo ahora
    (12,102,3,'vencido'),     -- lo dejó pasar sin comprar
    (12,103,4,'cancelado')    -- el asesor se lo quitó
")->execute();

echo "\n1) QUIÉN NO PUEDE RECIBIR OTRO\n";
chk('el que YA COMPRÓ con su descuento',          admite($pdo, 1, 200), false);
chk('el que lo tiene vivo ahorita',               admite($pdo, 2, 201), false);
chk('el que lo DEJÓ VENCER sin comprar',          admite($pdo, 3, 202), false);

echo "\n2) QUIÉN SÍ\n";
chk('aquel a quien el asesor se lo QUITÓ',        admite($pdo, 4, 203), true);
chk('el que nunca ha tenido uno',                 admite($pdo, 9, 204), true);
// Y una vez que recibe el suyo, se le acaba el cupo igual que a todos.
chk('…pero solo una vez',                         admite($pdo, 9, 205), false);

echo "\n3) LA MIGRACIÓN DICE LO MISMO\n";
// La regla vive en el DDL: si el archivo y esta prueba se separan, lo de
// producción y lo que aquí se comprueba dejan de ser lo mismo.
// Se miran solo las SENTENCIAS, no los comentarios: el archivo de corrección
// CITA la regla vieja a propósito, para explicar qué se cambió y por qué.
$ddl = function (string $ruta): string {
    $sin_comentarios = preg_replace('/^\s*--.*$/m', '', (string)file_get_contents($ruta));
    return strtolower(preg_replace('/\s+/', ' ', $sin_comentarios));
};
$norm = fn(string $s): string => strtolower(preg_replace('/\s+/', ' ', $s));
foreach (['migrations/add_descuentos_inteligentes.sql' => 'la base (servidor nuevo)',
          'migrations/add_di_cupo_permanente.sql'      => 'la corrección (servidor existente)'] as $f => $quien) {
    $sql = $ddl(dirname(__DIR__) . '/' . $f);
    chk("$quien trae la regla nueva", str_contains($sql, $norm($REGLA)), true);
    chk("$quien ya no trae la vieja",
        str_contains($sql, $norm("estado IN ('activo','utilizado') THEN cliente_id")), false);
}

$pdo->exec("DROP TABLE IF EXISTS t_di_cupo");

echo "\n" . ($fail === 0
    ? "✓ DI CUPO OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
