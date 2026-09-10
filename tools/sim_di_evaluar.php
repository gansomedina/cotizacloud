<?php
// ============================================================
// SIMULACIÓN REAL — DescuentoInteligente::evaluar() contra MariaDB
// de verdad. Cubre la PUERTA DE ENTRADA nueva (la mesa decide, no la
// edad) y las exclusiones que se conservaron.
//
// REQUISITOS (entorno de desarrollo, NUNCA producción):
//   - MariaDB/MySQL local con BD 'simtest' y usuario sim/sim
//   - DESTRUYE y recrea sus tablas en cada corrida
// Correr: php tools/sim_di_evaluar.php   → debe terminar en OK
// Obligatorio tras CUALQUIER cambio a DescuentoInteligente::evaluar()
// o a Mesa::fuera_de_ventana().
//
// Anclas fijas: p75=10, p90=25 → mesa 0-20 · R1 21-30 · R2 31-55 ·
// fósil 56+. Bono de edición 10d · bono de toque 5d.
//
// LA REGLA COMPLETA son dos condiciones: salió de la mesa Y el cliente
// lleva más de 2×p75 días sin abrirla. Sin margen ni plazos de gracia.
// ============================================================
define('COTIZAAPP', 1);

class DB {
    private static ?PDO $pdo = null;
    public static function pdo(): PDO {
        if (!self::$pdo) {
            self::$pdo = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4',
                'sim', 'sim', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
        return self::$pdo;
    }
    public static function query($sql, $params = []): array {
        $st = self::pdo()->prepare($sql); $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function row($sql, $params = []) {
        $r = self::query($sql, $params); return $r ? $r[0] : null;
    }
    public static function val($sql, $params = []) {
        $st = self::pdo()->prepare($sql); $st->execute($params);
        return $st->fetchColumn();
    }
    public static function execute($sql, $params = []): int {
        $st = self::pdo()->prepare($sql); $st->execute($params); return $st->rowCount();
    }
}

require __DIR__ . '/../core/Mesa.php';
require __DIR__ . '/../core/DescuentoInteligente.php';

// ── Esquema ────────────────────────────────────────────────
foreach (['radar_feedback','mesa_estados','cotizacion_log','quote_sessions',
          'desc_int_activaciones','desc_int_config','ventas','cotizaciones'] as $t) {
    DB::execute("DROP TABLE IF EXISTS $t");
}
DB::execute("CREATE TABLE cotizaciones (
  id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED, cliente_id INT UNSIGNED NULL,
  estado VARCHAR(20) DEFAULT 'vista', total DECIMAL(14,2) DEFAULT 100000,
  radar_bucket VARCHAR(40) NULL, ultima_vista_at DATETIME NULL, agenda_fecha DATE NULL,
  descuento_auto_activo TINYINT DEFAULT 0, descuento_auto_expira DATETIME NULL,
  cupon_id INT UNSIGNED NULL, impuesto_modo VARCHAR(10) DEFAULT 'ninguno',
  created_at DATETIME)");
DB::execute("CREATE TABLE ventas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT UNSIGNED, empresa_id INT UNSIGNED, cliente_id INT UNSIGNED NULL,
  estado VARCHAR(20) DEFAULT 'activa', created_at DATETIME)");
DB::execute("CREATE TABLE desc_int_config (empresa_id INT UNSIGNED PRIMARY KEY,
  r1_activa TINYINT DEFAULT 1, r1_pct DECIMAL(5,2) DEFAULT 8,
  r2_activa TINYINT DEFAULT 1, r2_pct DECIMAL(5,2) DEFAULT 8,
  n_ventas INT DEFAULT 65, p75 INT NULL, p90 INT NULL,
  dia_fin_vida INT NULL, dia_dead INT NULL, dia_techo INT NULL, anclas_at DATETIME NULL)");
DB::execute("CREATE TABLE desc_int_activaciones (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT UNSIGNED, cliente_id INT UNSIGNED, estado VARCHAR(20))");
DB::execute("CREATE TABLE quote_sessions (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT UNSIGNED, es_interno TINYINT DEFAULT 0,
  visible_ms INT NULL, scroll_max INT NULL, created_at DATETIME)");
DB::execute("CREATE TABLE cotizacion_log (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT UNSIGNED, usuario_id INT UNSIGNED NULL,
  accion VARCHAR(30) NULL, evento VARCHAR(30) NULL, created_at DATETIME)");
DB::execute("CREATE TABLE mesa_estados (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT UNSIGNED, empresa_id INT UNSIGNED, area VARCHAR(20),
  estado VARCHAR(30), razon VARCHAR(30) NULL, created_at DATETIME)");
DB::execute("CREATE TABLE radar_feedback (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT UNSIGNED, usuario_id INT UNSIGNED, empresa_id INT UNSIGNED,
  tipo VARCHAR(20), updated_at DATETIME)");

// Anclas precargadas y frescas: anclas() las toma de cache (TTL 24h) y no
// recalcula. Fija los números de la prueba — p75=10 como Hermosillo.
DB::execute("INSERT INTO desc_int_config
  (empresa_id, n_ventas, p75, p90, dia_fin_vida, dia_dead, dia_techo, anclas_at)
  VALUES (7, 65, 10, 25, 20, 30, 55, NOW())");

$d = fn(float $dias) => date('Y-m-d H:i:s', time() - (int)round($dias * 86400));

$next_cli = 100;
/** Crea una cotización lista para recibir DI y devuelve su fila. */
function cot(int $id, float $edad, float $dorm, array $x = []): array {
    global $d, $next_cli;
    $cli = $x['cliente_id'] ?? ++$next_cli;
    DB::execute("INSERT INTO cotizaciones (id, empresa_id, cliente_id, estado, radar_bucket,
                 ultima_vista_at, agenda_fecha, descuento_auto_activo, cupon_id, created_at)
                 VALUES (?,7,?,?,?,?,?,?,?,?)",
        [$id, $cli, $x['estado'] ?? 'vista', $x['bucket'] ?? null, $d($dorm),
         $x['agenda'] ?? null, $x['desc_manual'] ?? 0, $x['cupon'] ?? null, $d($edad)]);
    // Visita real del cliente el día de la dormancia (sin ella, $vio falla)
    DB::execute("INSERT INTO quote_sessions (cotizacion_id, es_interno, visible_ms, scroll_max, created_at)
                 VALUES (?,0,45000,70,?)", [$id, $d($dorm)]);
    return DB::row("SELECT * FROM cotizaciones WHERE id=?", [$id]);
}
function edito(int $id, float $hace_d): void {
    global $d;
    DB::execute("INSERT INTO cotizacion_log (cotizacion_id, usuario_id, accion, created_at)
                 VALUES (?,900,'editada',?)", [$id, $d($hace_d)]);
}
function toque(int $id, float $hace_d, ?string $razon = null): void {
    global $d;
    DB::execute("INSERT INTO mesa_estados (cotizacion_id, empresa_id, area, estado, razon, created_at)
                 VALUES (?,7,'contacto','hablamos',?,?)", [$id, $razon, $d($hace_d)]);
}
function manita(int $id, float $hace_d): void {
    global $d;
    DB::execute("INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo, updated_at)
                 VALUES (?,900,7,'con_interes',?)", [$id, $d($hace_d)]);
}

$fail = 0;
function chk(string $name, $got, $want): void {
    global $fail;
    $ok = ($got == $want);
    if (!$ok) $fail++;
    echo ($ok ? "  ✓ " : "  ✗ ") . $name . ($ok ? '' : "  got=" . json_encode($got) . " want=" . json_encode($want)) . "\n";
}
/** null si no aplica; el número de regla si aplica. */
function regla(array $cot): ?int {
    $r = DescuentoInteligente::evaluar($cot);
    return $r ? (int)$r['regla'] : null;
}

echo "═ LA MESA ABRE LA PUERTA ═\n";
// Base: 25 días, cliente calló 25, sin ediciones ni toques, bucket vacío.
chk('sin toques ni ediciones y fuera de la mesa → aplica R1', regla(cot(1, 25, 25)), 1);

chk('toque de hace 3d (la mesa la tiene) → NO aplica', regla((function () {
    $c = cot(2, 25, 25); toque(2, 3); return $c; })()), null);

chk('toque de hace 5d (último día que la mesa la tiene) → NO aplica', regla((function () {
    $c = cot(19, 25, 25); toque(19, 5); return $c; })()), null);

// El bono de toque son 5 días y la puerta pide MÁS de 5: al 6º sale, y ahí
// mismo puede entrar el descuento. Sin margen — la mesa suelta, el DI entra.
chk('toque de hace 6d (la mesa la soltó hoy) → aplica R1', regla((function () {
    $c = cot(3, 25, 25); toque(3, 6); return $c; })()), 1);

chk('toque de hace 8d → aplica R1', regla((function () {
    $c = cot(4, 25, 25); toque(4, 8); return $c; })()), 1);

echo "═ EL BONO DE EDICIÓN ES MÁS LARGO QUE LA VENTANA VIEJA ═\n";
// Antes la ventana de edición del descuento era p75/2 = 5 días: una edición de
// hace 7 días ya no protegía. La mesa da 10, y ahora manda la mesa.
chk('edición de hace 7d → NO aplica (la ventana vieja de 5d ya la habría dejado pasar)',
    regla((function () { $c = cot(5, 25, 25); edito(5, 7); return $c; })()), null);
chk('edición de hace 11d (la mesa la soltó) → aplica R1',
    regla((function () { $c = cot(6, 25, 25); edito(6, 11); return $c; })()), 1);

// R2 es donde la rama de mesa_estados NO es redundante: la puerta pide 5 días
// sin toque, pero el window de R2 son 10. Manda la más estricta.
echo "═ EN R2 EL WINDOW DE LA ZONA ES MÁS ESTRICTO QUE LA PUERTA ═\n";
chk('R2 con toque de hace 7d → NO aplica (pasó la puerta, lo frena el window de 10d)',
    regla((function () { $c = cot(20, 35, 35); toque(20, 7); return $c; })()), null);
chk('R2 con toque de hace 11d → aplica R2',
    regla((function () { $c = cot(21, 35, 35); toque(21, 11); return $c; })()), 2);

echo "═ EL IMPLÍCITO NO PROTEGE ═\n";
chk('toque razon=auto de hace 3d → aplica igual (no es trabajo del asesor)',
    regla((function () { $c = cot(7, 25, 25); toque(7, 3, 'auto'); return $c; })()), 1);

echo "═ LO QUE SE CONSERVÓ ═\n";
// La manita del RADAR normalmente escribe LAS DOS tablas en una transacción
// (api/radar_feedback.php), así que casi siempre la ataja mesa_estados. Esta
// rama es la red: si mesa_estados no está migrada, el endpoint tiene un
// fallback que guarda SOLO radar_feedback — y ese 👍 debe proteger igual.
// El fixture reproduce justo ese estado: manita sin fila de mesa.
chk('manita 👍 de hace 2d, sin fila de mesa → NO aplica (la ataja radar_feedback)',
    regla((function () { $c = cot(8, 25, 25); manita(8, 2); return $c; })()), null);
chk('manita 👍 de hace 9d (fuera de la ventana) → aplica R1',
    regla((function () { $c = cot(9, 25, 25); manita(9, 9); return $c; })()), 1);

chk('dormancia de 20d exactos → NO aplica (pide MÁS de 2×p75)', regla(cot(10, 25, 20)), null);
chk('dormancia de 21d → aplica R1', regla(cot(11, 25, 21)), 1);

chk('bucket caliente → NO aplica', regla(cot(12, 25, 25, ['bucket' => 'probable_cierre'])), null);
chk('bucket frío (enfriandose) → aplica R1', regla(cot(13, 25, 25, ['bucket' => 'enfriandose'])), 1);

echo "═ ZONAS: LA EDAD YA SOLO ELIGE REGLA Y TECHO ═\n";
chk('edad 35 → aplica R2 (no R1)', regla(cot(14, 35, 35)), 2);
chk('edad 60 (pasó el techo 55) → NO aplica: fósil', regla(cot(15, 60, 60)), null);

echo "═ CANDADOS DE CLIENTE (sin cambio) ═\n";
$c16 = cot(16, 25, 25);
DB::execute("INSERT INTO ventas (cotizacion_id, empresa_id, cliente_id, estado, created_at)
             VALUES (?,7,?, 'activa', NOW())", [16, (int)$c16['cliente_id']]);
chk('cliente que YA compró → NO aplica', regla($c16), null);
chk('cupón asignado → NO aplica', regla(cot(17, 25, 25, ['cupon' => 5])), null);
chk('descuento manual vivo → NO aplica', regla(cot(18, 25, 25, ['desc_manual' => 1])), null);

echo "\n" . ($fail ? "✗ $fail FALLAS — HAY ERRORES EN evaluar()" : "✓ SIMULACIÓN DI OK") . "\n";
exit($fail ? 1 : 0);
