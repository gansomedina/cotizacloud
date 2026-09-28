<?php
// ============================================================
// SIMULACIÓN REAL — MetasEmpresa contra MariaDB de verdad.
//
// Lo que puede fallar aquí son las QUERIES y los LÍMITES: que la receta de
// vendido muerda (pagado > 0, total > 0, no cancelada, sin DI), que el
// primer segundo del mes entre y el último del anterior no, que "30 días"
// sean 30 fechas, que la meta de 30 días sume bien cruzando febrero, que la
// histéresis no parpadee y que el día 1 no dispare alertas falsas.
//
// Corre la MIGRACIÓN REAL (migrations/add_empresa_metas.sql) sobre un
// esquema mínimo: si la migración tiene un error, esta prueba lo dice.
//
// La conexión usa ATTR_EMULATE_PREPARES=false, IGUAL que core/DB.php: con
// preparados emulados un marcador repetido pasa en la prueba y truena en
// producción.
//
// REQUISITOS (desarrollo, NUNCA producción):
//   - MariaDB/MySQL local con BD 'simtest' y usuario sim/sim
//   - DESTRUYE y recrea sus tablas en cada corrida — incluidas empresas,
//     cotizaciones y ventas de la BD compartida 'simtest'. Correr las
//     simulaciones UNA POR UNA y nunca entre sim_mesa_armar y
//     sim_mesa_render (render reutiliza los fixtures de armar).
// El reloj se inyecta (MetasEmpresa::$ahora): no depende de la hora real.
// Correr: php tools/sim_metas.php   → debe terminar en OK
// Obligatorio tras CUALQUIER cambio a MetasEmpresa.
// ============================================================
define('COTIZAAPP', 1);
date_default_timezone_set('America/Hermosillo');

class DB {
    private static ?PDO $pdo = null;
    public static function pdo(): PDO {
        if (!self::$pdo) {
            self::$pdo = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4',
                'sim', 'sim', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                               PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                               PDO::ATTR_EMULATE_PREPARES => false]);
        }
        return self::$pdo;
    }
    public static function query($sql, $params = []): array {
        $st = self::pdo()->prepare($sql); $st->execute($params); return $st->fetchAll();
    }
    public static function row($sql, $params = []): ?array {
        $st = self::pdo()->prepare($sql); $st->execute($params);
        $r = $st->fetch(); return $r === false ? null : $r;
    }
    public static function val($sql, $params = []) {
        $st = self::pdo()->prepare($sql); $st->execute($params); return $st->fetchColumn();
    }
    public static function execute($sql, $params = []): int {
        $st = self::pdo()->prepare($sql); $st->execute($params); return $st->rowCount();
    }
}

/** Stub de Helpers::trial_info: solo el plan, leído de la tabla. */
function trial_info(int $e): array {
    $p = (string)DB::val("SELECT plan FROM empresas WHERE id = ?", [$e]);
    // 'business_vencido' simula una licencia Business vencida (no trial):
    // trial_info conserva plan='business' y marca vencido=true.
    if ($p === 'business_vencido') return ['plan' => 'business', 'es_business' => true, 'vencido' => true];
    return ['plan' => $p, 'es_business' => $p === 'business', 'vencido' => false];
}

require __DIR__ . '/../core/RitmoCot.php';
require __DIR__ . '/../core/MetasEmpresa.php';
// La tasa AUTOAJUSTABLE la calcula el motor (ActividadScore, con sus propias
// pruebas). Aquí se inyecta por empresa; default: sin muestra.
$TASAS = [];
MetasEmpresa::$tasa_fn = function (int $e) { global $TASAS; return $TASAS[$e] ?? ['rate' => 0.0, 'muestra' => 0]; };
function tasa(int $e, float $rate, int $muestra): void { global $TASAS; $TASAS[$e] = ['rate' => $rate, 'muestra' => $muestra]; }

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . " want=" . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n"; }
}
function near(string $t, $got, float $want, float $eps = 0.01): void {
    chk($t . " (≈$want)", is_numeric($got) && abs((float)$got - $want) < $eps);
}
function reloj(string $dt): void { MetasEmpresa::$ahora = strtotime($dt); MetasEmpresa::reset(); }
function EST(int $e): array { MetasEmpresa::reset(); return MetasEmpresa::estado($e); }

// ── Esquema mínimo (solo lo que MetasEmpresa toca) + la migración real ──
DB::pdo()->exec("SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS empresa_metas_estado, empresa_metas_mes, ventas, cotizaciones,
                     desc_int_activaciones, historial_mensual, empresas;
SET FOREIGN_KEY_CHECKS=1;
CREATE TABLE empresas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, moneda VARCHAR(3) NOT NULL DEFAULT 'MXN',
  plan VARCHAR(20) NOT NULL DEFAULT 'business'
) ENGINE=InnoDB;
CREATE TABLE cotizaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NULL, vendedor_id INT UNSIGNED NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'enviada', suspendida TINYINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB;
CREATE TABLE ventas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  cotizacion_id INT UNSIGNED NULL, usuario_id INT UNSIGNED NULL, vendedor_id INT UNSIGNED NULL,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  pagado DECIMAL(14,2) NOT NULL DEFAULT 0, estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  created_at DATETIME NOT NULL
) ENGINE=InnoDB;
CREATE TABLE desc_int_activaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  estado ENUM('activo','vencido','utilizado','cancelado') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;
CREATE TABLE historial_mensual (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  anio SMALLINT UNSIGNED NOT NULL, mes TINYINT UNSIGNED NOT NULL,
  cotizaciones_cantidad INT UNSIGNED NOT NULL DEFAULT 0,
  ventas_cantidad INT UNSIGNED NOT NULL DEFAULT 0, ventas_monto DECIMAL(14,2) NOT NULL DEFAULT 0,
  tasa_cierre DECIMAL(5,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;
");
// Producción ya tiene la tabla por mes de la primera versión: se crea aquí
// para que la migración demuestre que la quita.
DB::pdo()->exec("CREATE TABLE empresa_metas_mes (empresa_id INT UNSIGNED NOT NULL PRIMARY KEY) ENGINE=InnoDB");
// La migración real, sentencia por sentencia (sin comentarios).
$mig = file_get_contents(__DIR__ . '/../migrations/add_empresa_metas.sql');
$mig = preg_replace('/--[^\n]*/', '', $mig);
foreach (array_filter(array_map('trim', explode(';', $mig))) as $sql) DB::pdo()->exec($sql);
// Re-ejecutable: correrla dos veces no truena.
$reej = true;
try { foreach (array_filter(array_map('trim', explode(';', $mig))) as $sql) DB::pdo()->exec($sql); } catch (\Throwable $x) { $reej = false; }

echo "\n── Migración ──\n";
chk('la tabla por mes ya NO existe (la meta es general)', (int)DB::val("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='simtest' AND table_name='empresa_metas_mes'"), 0);
chk('empresas trae las 6 columnas de la meta', (int)DB::val("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='simtest' AND table_name='empresas' AND column_name IN ('meta_equilibrio','meta_pesimista','meta_optimista','meta_moneda','meta_capturada_at','meta_capturada_por')"), 6);
chk('empresa_metas_estado existe', (int)DB::val("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='simtest' AND table_name='empresa_metas_estado'"), 1);
chk('empresas.tasa_conv_meta',     (int)DB::val("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='simtest' AND table_name='empresas' AND column_name IN ('tasa_conv_meta','tasa_conv_meta_desde')"), 2);
chk('la migración se puede volver a correr', $reej);
chk('nivel cabe el más largo (sin_equilibrio)', (int)DB::val("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_schema='simtest' AND table_name='empresa_metas_estado' AND column_name='nivel'") >= max(array_map('strlen', MetasEmpresa::NIVELES)));

// ── Fixtures ──
function empresa(string $plan = 'business', string $moneda = 'MXN', ?float $tasa = null): int {
    DB::execute("INSERT INTO empresas (plan, moneda, tasa_conv_meta) VALUES (?,?,?)", [$plan, $moneda, $tasa]);
    return (int)DB::pdo()->lastInsertId();
}
/** La meta GENERAL de la empresa (una sola, igual para todos los meses). */
function metas(int $e, float $E, float $P, float $O, string $mon = 'MXN'): void {
    DB::execute("UPDATE empresas SET meta_equilibrio=?, meta_pesimista=?, meta_optimista=?, meta_moneda=? WHERE id=?",
        [$E, $P, $O, $mon, $e]);
}
function sin_meta(int $e): void {
    DB::execute("UPDATE empresas SET meta_equilibrio=NULL, meta_pesimista=NULL, meta_optimista=NULL, meta_moneda=NULL WHERE id=?", [$e]);
}
/** Simula producción sin migrar: la columna de la meta no existe. */
function sin_columnas(bool $quitar): void {
    DB::pdo()->exec($quitar ? "ALTER TABLE empresas RENAME COLUMN meta_equilibrio TO meta_equilibrio_x"
                            : "ALTER TABLE empresas RENAME COLUMN meta_equilibrio_x TO meta_equilibrio");
}
function cot(int $e, string $fecha, string $estado = 'enviada', int $susp = 0): int {
    DB::execute("INSERT INTO cotizaciones (empresa_id, estado, suspendida, created_at) VALUES (?,?,?,?)", [$e, $estado, $susp, $fecha]);
    return (int)DB::pdo()->lastInsertId();
}
/** Venta con su cotización. $di: estado de una activación de DI, o null. */
function venta(int $e, string $fecha, float $total, float $pagado = 1, string $estado = 'pendiente', ?string $di = null): int {
    $c = cot($e, $fecha, 'aceptada');
    DB::execute("INSERT INTO ventas (empresa_id, cotizacion_id, total, pagado, estado, created_at) VALUES (?,?,?,?,?,?)",
        [$e, $c, $total, $pagado, $estado, $fecha]);
    $id = (int)DB::pdo()->lastInsertId();
    if ($di) DB::execute("INSERT INTO desc_int_activaciones (cotizacion_id, estado) VALUES (?,?)", [$c, $di]);
    return $id;
}
/** Historia: una venta pagada vieja para pasar el candado de 30 días. */
function historia(int $e, string $fecha = '2026-01-15 12:00:00'): void { venta($e, $fecha, 1000); }

// ═════════════════════════════════════════════════════════════
echo "\n── Receta de vendido y límites (hoy = 25/Sep/2026 10:00) ──\n";
reloj('2026-09-25 10:00:00');
$e = empresa();
historia($e);
metas($e, 50000, 100000, 150000);
metas($e, 50000, 100000, 150000);
venta($e, '2026-09-01 00:00:00', 1000);                 // primer segundo del mes: entra
venta($e, '2026-08-31 23:59:59', 2000);                 // último del anterior: fuera de mes, dentro de 30d? no (ini_30 = 27/Ago) → sí
venta($e, '2026-08-27 00:00:00', 4000);                 // ini_30 exacto (30 fechas contando hoy): entra en d30
venta($e, '2026-08-26 23:59:59', 8000);                 // un segundo antes: fuera
venta($e, '2026-09-25 23:59:59', 16000);                // hoy, último segundo: entra
venta($e, '2026-09-26 00:00:00', 32000);                // mañana: fuera
venta($e, '2026-09-10 12:00:00', 64000, 0);             // sin pago: fuera
venta($e, '2026-09-10 12:00:00', 128000, 5, 'cancelada');// cancelada: fuera
venta($e, '2026-09-10 12:00:00', 0, 5);                 // total 0: fuera
venta($e, '2026-09-10 12:00:00', 256000, 5, 'pendiente', 'utilizado'); // DI utilizado: fuera
venta($e, '2026-09-11 12:00:00', 512, 5, 'pendiente', 'cancelado');    // DI cancelado: CUENTA
$s = EST($e);
near('mes = 1000 + 16000 + 512', $s['ventanas']['mes']['vendido'], 17512);
chk('n_mes = 3', $s['ventanas']['mes']['n'], 3);
near('d30 = mes + 2000 + 4000', $s['ventanas']['d30']['vendido'], 23512);
chk('n_30 = 5', $s['ventanas']['d30']['n'], 5);
chk('n es entero', is_int($s['ventanas']['mes']['n']));

echo "\n── Cero ventas en la ventana ──\n";
$e0 = empresa(); historia($e0); metas($e0, 1, 2, 3); metas($e0, 1, 2, 3);
$s = EST($e0);
chk('vendido 0.0 (no null)', $s['ventanas']['mes']['vendido'], 0.0);
chk('n 0 (no null)', $s['ventanas']['mes']['n'], 0);
chk('nivel sin_equilibrio', $s['ventanas']['mes']['nivel'], 'sin_equilibrio');

echo "\n── Escala de 10% (niveles crudos, fronteras exactas) ──\n";
$rf = new ReflectionMethod('MetasEmpresa', '_nivel_crudo');
$casos = [
    [49999.99, 'sin_equilibrio'], [50000, 'muy_baja'], [59999.99, 'muy_baja'],
    [60000, 'baja'], [69999.99, 'baja'], [70000, 'debajo'], [79999.99, 'debajo'],
    [80000, 'cerca'], [89999.99, 'cerca'], [90000, 'casi'], [99999.99, 'casi'],
    // Pesimista → optimista en tercios del tramo (100k → 150k): 116,666.67 y 133,333.33.
    [100000, 'llego'], [116666.66, 'llego'], [116666.67, 'medio_optima'],
    [133333.32, 'medio_optima'], [133333.33, 'casi_optima'], [135000, 'casi_optima'],
    [149999.99, 'casi_optima'], [150000, 'sobrepasada'], [900000, 'sobrepasada'],
];
foreach ($casos as [$v, $want]) chk("V=$v → $want", $rf->invoke(null, (float)$v, 50000.0, 100000.0, 150000.0), $want);
// Equilibrio arriba del 60/70%: esos escalones quedan vacíos y la lectura sigue coherente.
chk('E=75k: 74,999 → sin_equilibrio', $rf->invoke(null, 74999.0, 75000.0, 100000.0, 150000.0), 'sin_equilibrio');
chk('E=75k: 75,000 → debajo (75% de la pesimista)', $rf->invoke(null, 75000.0, 75000.0, 100000.0, 150000.0), 'debajo');
// Optimista pegada a la pesimista: los tercios son chicos, pero siguen en orden.
chk('O=1.05P: P → llego', $rf->invoke(null, 100000.0, 50000.0, 100000.0, 105000.0), 'llego');
chk('O=1.05P: 102,000 → medio_optima', $rf->invoke(null, 102000.0, 50000.0, 100000.0, 105000.0), 'medio_optima');
chk('O=1.05P: 104,000 → casi_optima', $rf->invoke(null, 104000.0, 50000.0, 100000.0, 105000.0), 'casi_optima');
chk('E=P=O: todo o nada', $rf->invoke(null, 100000.0, 100000.0, 100000.0, 100000.0), 'sobrepasada');

echo "\n── Mes calendario SIN prorrateo (CEO, 3ª ronda) ──\n";
reloj('2026-09-25 10:00:00');
$ep = empresa(); historia($ep); metas($ep, 50000, 100000, 150000); metas($ep, 50000, 100000, 150000);
venta($ep, '2026-09-05 10:00:00', 85000);
$s = EST($ep);
chk('meta del mes = completa', $s['ventanas']['mes']['pesimista'], 100000.0);
chk('85k de 100k → cerca', $s['ventanas']['mes']['nivel'], 'cerca');
reloj('2026-09-01 08:00:00');
$e1 = empresa(); historia($e1); metas($e1, 50000, 100000, 150000); metas($e1, 50000, 100000, 150000);
venta($e1, '2026-09-01 07:00:00', 20000);
$s = EST($e1);
chk('día 1 a las 8 am: muy baja contra la meta completa (aceptado)', $s['ventanas']['mes']['nivel'], 'sin_equilibrio');

echo "\n── Últimos 30 días contra la MISMA meta completa ──\n";
reloj('2026-03-01 10:00:00');          // ventana 31/Ene … 1/Mar: cruza febrero y no importa
$ef = empresa(); historia($ef, '2025-10-01 12:00:00'); metas($ef, 3100, 6200, 9300);
venta($ef, '2026-02-10 10:00:00', 5000);
$s = EST($ef);
chk('d30: la meta es la del mes, entera (sin repartir por días)', [$s['ventanas']['d30']['equilibrio'], $s['ventanas']['d30']['pesimista'], $s['ventanas']['d30']['optimista']], [3100.0, 6200.0, 9300.0]);
chk('mes y d30 comparan contra la misma meta', $s['ventanas']['mes']['pesimista'], $s['ventanas']['d30']['pesimista']);
chk('d30: 5,000 de 6,200 → cerca (80.6%)', $s['ventanas']['d30']['nivel'], 'cerca');
chk('mes (marzo, 1 día): sin ventas → sin_equilibrio', $s['ventanas']['mes']['nivel'], 'sin_equilibrio');
reloj('2026-02-28 10:00:00');          // febrero: 28 días. Repartir por días daría 30/28 de la meta
$eF = empresa(); historia($eF, '2025-10-01 12:00:00'); metas($eF, 3000, 6200, 9300);
venta($eF, '2026-02-10 10:00:00', 6200);
chk('d30 en febrero: 6,200 contra la meta ENTERA de 6,200 → llegó', EST($eF)['ventanas']['d30']['nivel'], 'llego');
reloj('2026-03-01 10:00:00');
chk('sin claves del modelo por mes', array_key_exists('provisional', $s['ventanas']['mes']) || array_key_exists('origen', $s['ventanas']['mes']), false);

echo "\n── Sin meta → nada se enciende ──\n";
reloj('2026-09-25 10:00:00');
$ex0 = empresa(); historia($ex0);
chk('sin capturar: sin_metas', EST($ex0)['estado'], 'sin_metas');
DB::execute("UPDATE empresas SET meta_equilibrio=1, meta_pesimista=2, meta_moneda='MXN' WHERE id=?", [$ex0]);   // incompleta (a mano), moneda correcta
chk('meta incompleta: sin_metas', EST($ex0)['estado'], 'sin_metas');

echo "\n── Moneda distinta → sin_metas ──\n";
$em = empresa('business', 'USD'); historia($em);
metas($em, 1, 2, 3, 'MXN'); metas($em, 1, 2, 3, 'MXN');
$s = EST($em);
chk('mes sin_metas por moneda', [$s['ventanas']['mes']['estado'], $s['ventanas']['mes']['motivo']], ['sin_metas', 'moneda']);
chk('d30 sin_metas por moneda', $s['ventanas']['d30']['motivo'], 'moneda');

echo "\n── Plan ──\n";
$epr = empresa('pro'); historia($epr); metas($epr, 1, 2, 3);
chk('Pro → sin_metas', EST($epr)['estado'], 'sin_metas');
$ebn = empresa(); metas($ebn, 1, 2, 3);
DB::execute("UPDATE empresas SET plan='pro' WHERE id=?", [$ebn]);
chk('baja de plan: no se borra la meta', DB::val("SELECT meta_pesimista FROM empresas WHERE id=?", [$ebn]), '2.00');
chk('baja de plan: estado sin_metas', EST($ebn)['estado'], 'sin_metas');

echo "\n── Historia mínima: 30 días desde la primera venta con pago ──\n";
$es = empresa(); metas($es, 1, 2, 3); metas($es, 1, 2, 3);
venta($es, '2026-08-27 09:00:00', 500);            // hace 29 días
$s = EST($es);
chk('29 días → sin_historia', $s['estado'], 'sin_historia');
chk('ventana sin nivel', $s['ventanas']['mes']['nivel'], null);
chk('nivel() dice sin_historia', MetasEmpresa::nivel($es)['mes'], 'sin_historia');
$f = MetasEmpresa::frases(MetasEmpresa::nivel($es));
chk('frase de historia, una sola', [$f['mes'], $f['d30']], ['Todavía no hay suficiente historia para leer cómo va la empresa.', null]);
chk('sin_historia no escribe histéresis', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_estado WHERE empresa_id=?", [$es]), 0);
$es2 = empresa(); metas($es2, 1, 2, 3);
venta($es2, '2026-08-26 09:00:00', 500);           // hace 30 días
chk('30 días → ya se lee', EST($es2)['estado'], 'ok');
$es3 = empresa(); metas($es3, 1, 2, 3);
venta($es3, '2026-07-01 09:00:00', 500, 0);        // vieja pero SIN pago
venta($es3, '2026-07-02 09:00:00', 500, 5, 'pendiente', 'utilizado'); // vieja pero DI
chk('venta vieja sin pago o con DI no cuenta como historia', EST($es3)['estado'], 'sin_historia');

echo "\n── Venta viva: primer pago después, DI quitado, extra, recibo cancelado ──\n";
reloj('2026-09-25 10:00:00');
$ev = empresa(); historia($ev); metas($ev, 1, 2, 3); metas($ev, 50000, 100000, 150000);
$vid = venta($ev, '2026-09-20 10:00:00', 10000, 0);
chk('sin anticipo: fuera', EST($ev)['ventanas']['mes']['vendido'], 0.0);
DB::execute("UPDATE ventas SET pagado = 3000 WHERE id = ?", [$vid]);
chk('anticipo llega: entra en su mes de aceptación', EST($ev)['ventanas']['mes']['vendido'], 10000.0);
reloj('2026-10-03 10:00:00');
$s = EST($ev);
chk('en octubre no cuenta para octubre', $s['ventanas']['mes']['vendido'], 0.0);
chk('en octubre sí en los últimos 30 días', $s['ventanas']['d30']['vendido'], 10000.0);
reloj('2026-09-25 10:00:00');
DB::execute("UPDATE ventas SET total = 12500 WHERE id = ?", [$vid]);
chk('extra agregado después: sube', EST($ev)['ventanas']['mes']['vendido'], 12500.0);
DB::execute("UPDATE ventas SET pagado = 0 WHERE id = ?", [$vid]);
chk('recibo cancelado (pagado a 0): sale', EST($ev)['ventanas']['mes']['vendido'], 0.0);
$vdi = venta($ev, '2026-09-21 10:00:00', 9000, 1000, 'pendiente', 'utilizado');
chk('con DI utilizado: fuera', EST($ev)['ventanas']['mes']['vendido'], 0.0);
DB::execute("UPDATE desc_int_activaciones SET estado='cancelado' WHERE cotizacion_id = (SELECT cotizacion_id FROM ventas WHERE id=?)", [$vdi]);
DB::execute("UPDATE ventas SET total = 10000 WHERE id = ?", [$vdi]);   // quitar el DI sube el total
chk('DI quitado: entra con el total nuevo', EST($ev)['ventanas']['mes']['vendido'], 10000.0);

echo "\n── Histéresis ──\n";
reloj('2026-09-25 10:00:00');
$eh2 = empresa(); historia($eh2); metas($eh2, 50000, 100000, 150000); metas($eh2, 50000, 100000, 150000);
$vh = venta($eh2, '2026-09-10 10:00:00', 85000);
$s = EST($eh2);
chk('primera evaluación: cerca', $s['ventanas']['mes']['nivel'], 'cerca');
chk('primera evaluación NO es cambio', $s['ventanas']['mes']['cambio'], false);
DB::execute("UPDATE ventas SET total = 77000 WHERE id = ?", [$vh]);    // 77k ≥ 80k×0.95 = 76k
$s = EST($eh2);
chk('77k: se sostiene cerca (banda)', $s['ventanas']['mes']['nivel'], 'cerca');
chk('77k: crudo sí es debajo', $s['ventanas']['mes']['nivel_crudo'], 'debajo');
chk('sostener no es cambio', $s['ventanas']['mes']['cambio'], false);
DB::execute("UPDATE ventas SET total = 75000 WHERE id = ?", [$vh]);    // < 76k
$s = EST($eh2);
chk('75k: baja a debajo', $s['ventanas']['mes']['nivel'], 'debajo');
chk('75k: es cambio', $s['ventanas']['mes']['cambio'], true);
chk('75k: nivel anterior cerca', $s['ventanas']['mes']['nivel_anterior'], 'cerca');
$s = EST($eh2);
chk('releer sin cambio: cambio=false', $s['ventanas']['mes']['cambio'], false);
chk('releer conserva nivel_anterior', $s['ventanas']['mes']['nivel_anterior'], 'cerca');
DB::execute("UPDATE ventas SET total = 80000 WHERE id = ?", [$vh]);
$s = EST($eh2);
chk('subir es inmediato al cruzar', [$s['ventanas']['mes']['nivel'], $s['ventanas']['mes']['cambio']], ['cerca', true]);

echo "\n── Cambio de mes: no arrastra nivel ni dispara alerta ──\n";
reloj('2026-10-01 09:00:00');
metas($eh2, 50000, 100000, 150000);
$s = EST($eh2);
chk('1/Oct: mes nuevo, nivel propio', $s['ventanas']['mes']['nivel'], 'sin_equilibrio');
chk('1/Oct: no es cambio', $s['ventanas']['mes']['cambio'], false);
chk('periodo guardado = 2026-10', DB::val("SELECT periodo FROM empresa_metas_estado WHERE empresa_id=? AND ventana='mes'", [$eh2]), '2026-10');

echo "\n── d30: una venta que sale de la ventana no hace parpadear ──\n";
reloj('2026-09-25 10:00:00');
$ed = empresa(); historia($ed); metas($ed, 30000, 60000, 90000); metas($ed, 30000, 60000, 90000);
// P30 ≈ 5 días de ago (60000/31) + 25 de sep (60000/30) ≈ 59,677
venta($ed, '2026-08-28 10:00:00', 3000);
venta($ed, '2026-09-15 10:00:00', 46000);
$s0 = EST($ed)['ventanas']['d30'];
chk('d30 inicial: cerca', $s0['nivel'], 'cerca');
reloj('2026-09-28 10:00:00');       // la de 3,000 sale; P30≈59,871 → 46k = 76.8% (≥ 80%×0.95 = 76%)
$s1 = EST($ed)['ventanas']['d30'];
chk('crudo bajó a debajo', $s1['nivel_crudo'], 'debajo');
chk('pero se sostiene cerca', [$s1['nivel'], $s1['cambio']], ['cerca', false]);

echo "\n── Conversión: la tasa AUTOAJUSTABLE contra la deseada ──\n";
reloj('2026-09-25 10:00:00');
$ek = empresa('business', 'MXN', 15.00); historia($ek); metas($ek, 1, 2, 3);
tasa($ek, 0.18, 40);                                          // "la empresa 18%" del reporte
for ($i = 0; $i < 30; $i++) cot($ek, '2026-09-10 10:00:00');  // muchas enviadas del mes: NO cuentan
venta($ek, '2026-09-12 10:00:00', 1000);
$c = EST($ek)['conv'];
chk('usa la tasa autoajustable tal cual (18%)', [$c['tasa'], $c['muestra']], [0.18, 40]);
chk('18% contra 15% → arriba (16.5% es la banda)', $c['nivel'], 'arriba');
chk('ya no existen cuentas por ventana (mes/d30) ni enviadas', array_key_exists('mes', $c) || array_key_exists('enviadas', $c), false);
$ek2 = empresa('business', 'MXN', 15.00); historia($ek2); metas($ek2, 1, 2, 3);
tasa($ek2, 0.18, 7);
chk('muestra 7 (< 8, como la tarjeta de Ritmo) → gris', EST($ek2)['conv']['nivel'], 'gris');
$ek3 = empresa('business', 'MXN', 15.00); historia($ek3); metas($ek3, 1, 2, 3);
tasa($ek3, 0.0, 50);
chk('tasa 0 → gris (no se opina, igual que la tarjeta)', EST($ek3)['conv']['nivel'], 'gris');
$ek4 = empresa('business', 'MXN', 30.00); historia($ek4); metas($ek4, 1, 2, 3);
tasa($ek4, 0.30, 20);
chk('30% contra 30% → en', EST($ek4)['conv']['nivel'], 'en');
$ek5 = empresa('business', 'MXN', 30.00); historia($ek5); metas($ek5, 1, 2, 3);
tasa($ek5, 0.20, 20);
chk('20% contra 30% → debajo', EST($ek5)['conv']['nivel'], 'debajo');
$ek6 = empresa(); historia($ek6); metas($ek6, 1, 2, 3); tasa($ek6, 0.2, 20);
chk('sin tasa deseada → sin_meta', EST($ek6)['conv']['nivel'], 'sin_meta');
chk('nivel() del asesor: conv = arriba', MetasEmpresa::nivel($ek)['conv'], 'arriba');
chk('frase de conversión, sin ventana (la tasa es histórica)', MetasEmpresa::frases(MetasEmpresa::nivel($ek))['conv'], 'La empresa cierra por encima de lo que busca.');
chk('en producción lee ActividadScore::close_rate_historico', str_contains(file_get_contents(__DIR__ . '/../core/MetasEmpresa.php'), 'ActividadScore::close_rate_historico($e)'));

echo "\n── Ticket y cotizaciones que faltan ──\n";
reloj('2026-09-25 10:00:00');
$et = empresa('business', 'MXN', 25.00); historia($et, '2026-06-01 10:00:00');
metas($et, 20000, 40000, 60000); metas($et, 20000, 40000, 60000);
for ($i = 0; $i < 4; $i++) venta($et, '2026-09-05 10:00:00', 5000);    // 20k en el mes
tasa($et, 1/3, 12);                                                     // tasa autoajustable 33%
$s = EST($et);
chk('ticket de ventas (5 en 180 días)', [$s['ticket'], $s['ticket_origen']], [4200.0, 'ventas']);
chk('faltante hacia la pesimista', [$s['ventanas']['mes']['faltante'], $s['ventanas']['mes']['faltante_hacia']], [20000.0, 'pesimista']);
// 20000 / 4200 = 4.76 ventas → /0.3333 = 14.28 → 15 ; /0.25 = 19.05 → 20
chk('N con la tasa real', $s['faltan_cot']['real'], 15);
chk('M con la deseada', $s['faltan_cot']['deseada'], 20);
$eth = empresa(); historia($eth); metas($eth, 1, 2, 3);
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2026,3,10,50000),(?,2026,2,0,0),(?,2025,1,1,999999)", [$eth, $eth, $eth]);
$s = EST($eth);
// Solo el último año (CEO, 27 sep): la fila de ene-2025 NO entra. (50,000 / 10)
chk('respaldo historial_mensual (último año)', [$s['ticket'], $s['ticket_origen']], [5000.0, 'historial']);
$ey = empresa(); historia($ey); metas($ey, 1, 2, 3);
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2025,10,4,40000),(?,2025,9,1,999999)", [$ey, $ey]);
chk('historial: oct-2025 entra (12 meses contando sep-2026)', EST($ey)['ticket'], 10000.0);
$ey2 = empresa(); historia($ey2); metas($ey2, 1, 2, 3);
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2024,3,10,50000)", [$ey2]);
chk('historial solo de hace años → sin ticket', [EST($ey2)['ticket'], EST($ey2)['faltan_cot']], [null, ['real' => null, 'deseada' => null]]);
$etn = empresa(); metas($etn, 1, 2, 3);
chk('empresa nueva sin nada → sin ticket', EST($etn)['ticket'], null);

echo "\n── Auditoría: alerta, conversión apagada, huecos, edición, frontera ──\n";
reloj('2026-09-25 10:00:00');
// A — la alerta NO la consume quien lee primero.
$ea = empresa(); historia($ea); metas($ea, 50000, 100000, 150000); metas($ea, 50000, 100000, 150000);
$va = venta($ea, '2026-09-10 10:00:00', 85000);
EST($ea);                                                     // primera lectura: cerca
DB::execute("UPDATE ventas SET total = 60000 WHERE id = ?", [$va]);
MetasEmpresa::reset(); MetasEmpresa::nivel($ea);           // el ASESOR lee primero y escribe la transición
$s = EST($ea);                                                // luego el admin
chk('A: el admin ve la alerta aunque el asesor leyó antes', [$s['ventanas']['mes']['alerta'], $s['ventanas']['mes']['nivel_anterior']], [true, 'cerca']);
chk('A: cambio=false para el admin (no se usa para alertar)', $s['ventanas']['mes']['cambio'], false);
reloj('2026-09-27 11:00:00');                               // pasadas 48 h
chk('A: la alerta caduca a las ' . MetasEmpresa::ALERTA_HORAS . ' h', EST($ea)['ventanas']['mes']['alerta'], false);
reloj('2026-09-25 10:00:00');
chk('A: primera lectura nunca es alerta', EST($ep)['ventanas']['mes']['alerta'], false);

// B — sin metas o sin historia NO sale texto de conversión para el asesor.
$eb = empresa('business', 'MXN', 30.00); tasa($eb, 0.25, 20);
for ($i = 0; $i < 10; $i++) cot($eb, '2026-09-10 10:00:00');
$nb = MetasEmpresa::nivel($eb);
chk('B: tasa declarada sin metas → sin texto', array_filter(MetasEmpresa::frases($nb)), []);
$eb2 = empresa('business', 'MXN', 30.00); metas($eb2, 1, 2, 3); tasa($eb2, 0.25, 20);
for ($i = 0; $i < 10; $i++) cot($eb2, '2026-09-10 10:00:00');
venta($eb2, '2026-09-01 10:00:00', 100);                  // historia de 24 días
$fb = MetasEmpresa::frases(MetasEmpresa::nivel($eb2));
chk('B: sin historia → solo la frase de historia, sin conversión', array_values(array_filter($fb)), ['Todavía no hay suficiente historia para leer cómo va la empresa.']);
tasa($eb2, 0.25, 20);
chk('B: el admin sí conserva el dato de conversión', EST($eb2)['conv']['tasa'], 0.25);

// C — la ventana de 30 días no compara contra un nivel viejo después de un hueco.
$eg = empresa(); historia($eg); metas($eg, 1, 2, 3); metas($eg, 1, 2, 3);
venta($eg, '2026-09-10 10:00:00', 50);
chk('C: arranca en sobrepasada', EST($eg)['ventanas']['d30']['nivel'], 'sobrepasada');
$antes = (int)DB::val("SELECT COUNT(*) FROM empresa_metas_estado WHERE empresa_id=?", [$eg]);
sin_meta($eg);
EST($eg);                                                     // leer sin meta
chk('C: LEER sin meta no escribe (corre en cada carga del dashboard)', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_estado WHERE empresa_id=?", [$eg]), $antes);
MetasEmpresa::olvidar($eg);                                   // lo que hace "Quitar meta"
chk('C: quitar la meta borra la memoria', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_estado WHERE empresa_id=?", [$eg]), 0);
metas($eg, 1000, 2000, 3000); metas($eg, 1000, 2000, 3000);
$s = EST($eg)['ventanas']['d30'];
chk('C: al volver es primera lectura (sin alerta vieja)', [$s['nivel'], $s['alerta'], $s['nivel_anterior']], ['sin_equilibrio', false, null]);

// Mes nuevo con la MISMA meta (misma firma), OTRO periodo → primera lectura.
$eo = empresa(); historia($eo); metas($eo, 1, 2, 3); metas($eo, 100, 200, 300);
venta($eo, '2026-09-10 10:00:00', 500);
chk('septiembre sobrepasada', EST($eo)['ventanas']['mes']['nivel'], 'sobrepasada');
reloj('2026-10-02 10:00:00');                          // octubre: la misma meta general
$s = EST($eo)['ventanas']['mes'];
chk('octubre es primera lectura, sin arrastre ni alerta', [$s['nivel'], $s['alerta'], $s['nivel_anterior']], ['sin_equilibrio', false, null]);
reloj('2026-09-25 10:00:00');

// D — editar las metas no dispara alerta.
$ed2 = empresa(); historia($ed2); metas($ed2, 50000, 100000, 150000); metas($ed2, 50000, 100000, 150000);
venta($ed2, '2026-09-10 10:00:00', 60000);
chk('D: antes de editar: baja', EST($ed2)['ventanas']['mes']['nivel'], 'baja');
metas($ed2, 20000, 40000, 60000);
$s = EST($ed2)['ventanas']['mes'];
chk('D: tras editar: sobrepasada sin alerta', [$s['nivel'], $s['alerta'], $s['nivel_anterior']], ['sobrepasada', false, null]);

// E — la frontera ±10% no depende del binario.
$rc = new ReflectionMethod('MetasEmpresa', '_conv');
$cv = fn($num, $den, $d) => $rc->invoke(null, ['rate' => $num / $den, 'muestra' => $den], $d)['nivel'];
chk('E: 27/100 vs 30% → en',  $cv(27, 100, 0.30), 'en');
chk('E: 18/100 vs 20% → en',  $cv(18, 100, 0.20), 'en');
chk('E: 36/100 vs 40% → en',  $cv(36, 100, 0.40), 'en');
chk('E: 44/100 vs 40% → en (frontera de arriba)', $cv(44, 100, 0.40), 'en');
chk('E: 26/100 vs 30% → debajo', $cv(26, 100, 0.30), 'debajo');
chk('E: 34/100 vs 30% → arriba', $cv(34, 100, 0.30), 'arriba');

// Datos inconsistentes (la captura de la fase 2 los rechazará, pero hoy nada lo impide).
chk('E>P: alcanzar el equilibrio (40% del tramo) ya es medio camino', $rf->invoke(null, 120000.0, 120000.0, 100000.0, 150000.0), 'medio_optima');
chk('E>P: debajo del equilibrio sigue sin_equilibrio', $rf->invoke(null, 110000.0, 120000.0, 100000.0, 150000.0), 'sin_equilibrio');
chk('O<P: la pesimista ya es sobrepasada', $rf->invoke(null, 100000.0, 50000.0, 100000.0, 80000.0), 'sobrepasada');

// Cada nivel de conversión lleva SU frase (mutación M26: frases cruzadas).
$fc = fn($k) => MetasEmpresa::frases(['conv' => $k])['conv'];
chk('conv debajo → "por debajo"', $fc('debajo'), 'La empresa cierra por debajo de lo que busca.');
chk('conv en → "en lo que busca"', $fc('en'), 'La empresa cierra en lo que busca.');
chk('conv arriba → "por encima"', $fc('arriba'), 'La empresa cierra por encima de lo que busca.');
$fl = fn($k) => MetasEmpresa::frases(['mes' => $k])['mes'];
$esperadas = [
    'sin_equilibrio' => 'La empresa ni siquiera llega al punto de equilibrio en este mes.',
    'muy_baja' => 'La empresa va muy baja en este mes.',
    'baja' => 'La empresa va baja en este mes.',
    'debajo' => 'La empresa va por debajo de su meta en este mes.',
    'cerca' => 'La empresa va cerca de su meta en este mes.',
    'casi' => 'La empresa casi llega a su meta en este mes.',
    'llego' => 'La empresa ya llegó a su meta pesimista en este mes; todavía le falta mucho para la optimista.',
    'medio_optima' => 'La empresa ya pasó su meta pesimista en este mes y va a medio camino de la optimista.',
    'casi_optima' => 'La empresa casi llega a su meta optimista en este mes.',
    'sobrepasada' => 'La empresa ya sobrepasó su meta optimista en este mes.',
];
foreach ($esperadas as $k => $txt) chk("frase $k", $fl($k), $txt);
chk('frases() no truena si le pasan estado() por error', is_array(MetasEmpresa::frases(EST($ep))));

// Ticket: la receta muerde (DI, sin pago) y el corte de 5 es exacto.
$et2 = empresa(); historia($et2, '2026-09-01 10:00:00'); metas($et2, 1, 2, 3);
for ($i = 0; $i < 3; $i++) venta($et2, '2026-09-05 10:00:00', 1000);
venta($et2, '2026-09-05 10:00:00', 90000, 0);                         // sin pago
venta($et2, '2026-09-05 10:00:00', 90000, 5, 'pendiente', 'utilizado'); // DI
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2026,3,2,7000)", [$et2]);
chk('ticket: 4 ventas válidas (DI y sin pago fuera) → respaldo historial', [EST($et2)['ticket'], EST($et2)['ticket_origen']], [3500.0, 'historial']);

// Cotizaciones que faltan: con muestra < CONV_MIN no se usa la tasa real.
$ef2 = empresa('business', 'MXN', 25.00); historia($ef2, '2026-06-01 10:00:00');
metas($ef2, 20000, 40000, 60000); metas($ef2, 20000, 40000, 60000);
for ($i = 0; $i < 4; $i++) venta($ef2, '2026-09-05 10:00:00', 5000);
tasa($ef2, 0.3, 4);
$s = EST($ef2);
chk('faltan: tasa real ignorada con muestra de 4', $s['faltan_cot']['real'], null);
chk('faltan: la deseada sí', $s['faltan_cot']['deseada'] !== null);

// Licencia Business vencida (no trial): sin metas.
$evx = empresa('business_vencido'); historia($evx); metas($evx, 1, 2, 3);
chk('Business vencida → sin_metas', EST($evx)['estado'], 'sin_metas');

// ═════════════════════════════════════════════════════════════
//  FASE 2 — Captura (Configuración › Metas)
// ═════════════════════════════════════════════════════════════
echo "\n── Captura: validación ──\n";
chk('válido E<P<O', MetasEmpresa::validar_metas(1.0, 2.0, 3.0), null);
chk('válido E=P=O', MetasEmpresa::validar_metas(5.0, 5.0, 5.0), null);
chk('falta uno', is_string(MetasEmpresa::validar_metas(1.0, null, 3.0)));
chk('cero', is_string(MetasEmpresa::validar_metas(0.0, 2.0, 3.0)));
chk('negativo', is_string(MetasEmpresa::validar_metas(-1.0, 2.0, 3.0)));
chk('pesimista < equilibrio → rechazo', MetasEmpresa::validar_metas(2.0, 1.0, 3.0), 'La meta pesimista no puede quedar debajo del punto de equilibrio.');
chk('optimista < pesimista → rechazo', MetasEmpresa::validar_metas(1.0, 3.0, 2.0), 'La meta optimista no puede quedar debajo de la meta pesimista.');
chk('pasa del DECIMAL(14,2)', is_string(MetasEmpresa::validar_metas(1.0, 2.0, 1e13)));
chk('parse "$120,000.50"', MetasEmpresa::parse_monto('$120,000.50'), 120000.5);
chk('parse vacío → null', MetasEmpresa::parse_monto(''), null);
chk('parse basura → null', MetasEmpresa::parse_monto('abc'), null);
chk('parse "1e3", "0x10", "INF" → null (solo lo que escribe una persona)', [MetasEmpresa::parse_monto('1e3'), MetasEmpresa::parse_monto('0x10'), MetasEmpresa::parse_monto(INF), MetasEmpresa::parse_monto(['1'])], [null, null, null, null]);
chk('0.004 redondea a cero → rechazado', is_string(MetasEmpresa::validar_metas(0.001, 0.001, 0.004)));
chk('"180.000" tiene decimales de más', [MetasEmpresa::decimales_de_mas('180.000'), MetasEmpresa::decimales_de_mas('180,000.50'), MetasEmpresa::decimales_de_mas('180000')], [true, false, false]);
chk('tasa vacía = válida (no declarada)', MetasEmpresa::validar_tasa(null), null);
chk('tasa 3 y 90 válidas', [MetasEmpresa::validar_tasa(3.0), MetasEmpresa::validar_tasa(90.0)], [null, null]);
chk('tasa 2.9 y 90.1 rechazadas', is_string(MetasEmpresa::validar_tasa(2.9)) && is_string(MetasEmpresa::validar_tasa(90.1)));
echo "\n── Captura: el endpoint real contra MariaDB ──\n";
$runner = tempnam(sys_get_temp_dir(), 'gm') . '.php';
file_put_contents($runner, '<?php
define("COTIZAAPP", 1);
date_default_timezone_set("America/Hermosillo");
[$_, $emp, $uid, $body] = $argv;
define("EMPRESA_ID", (int)$emp);
class DB {
    private static $pdo = null;
    public static function pdo(): PDO { return self::$pdo ??= new PDO("mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4","sim","sim",
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]); }
    public static function query($s,$p=[]){ $st=self::pdo()->prepare($s); $st->execute($p); return $st->fetchAll(); }
    public static function row($s,$p=[]){ $st=self::pdo()->prepare($s); $st->execute($p); $r=$st->fetch(); return $r===false?null:$r; }
    public static function val($s,$p=[]){ $st=self::pdo()->prepare($s); $st->execute($p); return $st->fetchColumn(); }
    public static function execute($s,$p=[]){ $st=self::pdo()->prepare($s); $st->execute($p); return $st->rowCount(); }
}
class Auth { public static function requerir_admin(){} public static function id(){ global $uid; return (int)$uid; } }
function csrf_check(){}
function trial_info($e){ $p=(string)DB::val("SELECT plan FROM empresas WHERE id=?",[$e]); return ["plan"=>$p,"es_business"=>$p==="business","vencido"=>false]; }
function json_ok($d=[],$m=""){ echo json_encode(["ok"=>true,"data"=>$d]); exit; }
function json_error($m,$c=400,$d=[]){ echo json_encode(["ok"=>false,"error"=>$m,"code"=>$c]); exit; }
class InWrap { public $context; private $d; private $p=0;
  function stream_open($path,$mode,$o,&$op){ global $body; $this->d = $path==="php://input" ? $body : ""; return true; }
  function stream_read($n){ $r=substr($this->d,$this->p,$n); $this->p+=strlen($r); return $r; }
  function stream_eof(){ return $this->p>=strlen($this->d); } function stream_stat(){ return []; } }
stream_wrapper_unregister("php"); stream_wrapper_register("php", "InWrap");
require "' . __DIR__ . '/../core/MetasEmpresa.php";
require "' . __DIR__ . '/../modules/config/guardar_metas.php";
');
function post(int $e, array $body, int $uid = 77): array {
    global $runner;
    $out = shell_exec('php ' . escapeshellarg($runner) . ' ' . $e . ' ' . $uid . ' ' . escapeshellarg(json_encode($body)) . ' 2>/dev/null');   // error_log va a stderr
    return json_decode((string)$out, true) ?? ['raw' => $out];
}
$M = fn(int $e) => DB::row("SELECT meta_equilibrio, meta_pesimista, meta_optimista, meta_moneda, meta_capturada_por, meta_capturada_at IS NOT NULL AS con_fecha FROM empresas WHERE id=?", [$e]);
$ec2 = empresa();
$r = post($ec2, ['accion' => 'meta', 'equilibrio' => '50,000', 'pesimista' => '$100,000', 'optimista' => 150000]);
chk('guardar meta: ok', $r['ok'] ?? $r, true);
chk('guardada con moneda, quién y cuándo', $M($ec2), ['meta_equilibrio' => '50000.00', 'meta_pesimista' => '100000.00', 'meta_optimista' => '150000.00', 'meta_moneda' => 'MXN', 'meta_capturada_por' => 77, 'con_fecha' => 1]);
$r = post($ec2, ['accion' => 'meta', 'equilibrio' => 60000, 'pesimista' => 100000, 'optimista' => 150000]);
chk('re-guardar reemplaza', DB::val("SELECT meta_equilibrio FROM empresas WHERE id=?", [$ec2]), '60000.00');
$r = post($ec2, ['accion' => 'meta', 'equilibrio' => 200000, 'pesimista' => 100000, 'optimista' => 150000]);
chk('E > P rechazado con su mensaje', [$r['ok'], $r['error']], [false, 'La meta pesimista no puede quedar debajo del punto de equilibrio.']);
chk('el rechazo no tocó la meta', DB::val("SELECT meta_equilibrio FROM empresas WHERE id=?", [$ec2]), '60000.00');
$r = post($ec2, ['accion' => 'meta', 'equilibrio' => 1, 'pesimista' => '', 'optimista' => 3]);
chk('monto vacío rechazado', $r['ok'], false);
$r = post($ec2, ['accion' => 'meta', 'equilibrio' => '180.000', 'pesimista' => '250.000', 'optimista' => '320.000']);
chk('"180.000" rechazado con mensaje de miles', [$r['ok'], $r['error']], [false, 'Los montos llevan máximo 2 decimales. Para miles usa coma: 180,000.']);
$r = post($ec2, ['accion' => 'meta', 'equilibrio' => 0.001, 'pesimista' => 0.001, 'optimista' => 0.004]);
chk('montos que redondean a 0 rechazados', [$r['ok'], DB::val("SELECT meta_equilibrio FROM empresas WHERE id=?", [$ec2])], [false, '60000.00']);
$ecx = empresa(); metas($ecx, 1, 2, 3);
$r = post($ec2, ['accion' => 'quitar']);
chk('quitar: la meta queda en NULL', [$r['ok'], $M($ec2)['meta_equilibrio'], $M($ec2)['meta_moneda'], $M($ec2)['con_fecha']], [true, null, null, 0]);
chk('quitar solo toca SU empresa', DB::val("SELECT meta_equilibrio FROM empresas WHERE id=?", [$ecx]), '1.00');
$eqh = empresa(); historia($eqh);
post($eqh, ['accion' => 'meta', 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
EST($eqh);                                                    // deja memoria de histéresis
chk('hay memoria antes de quitar', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_estado WHERE empresa_id=?", [$eqh]), 2);
post($eqh, ['accion' => 'quitar']);
chk('el endpoint "quitar" borra la memoria de histéresis', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_estado WHERE empresa_id=?", [$eqh]), 0);
$r = post($ec2, ['accion' => ['x'], 'tasa' => ['25']]);
chk('arreglos en el JSON → 400 limpio', [$r['ok'] ?? null, $r['code'] ?? null], [false, 400]);
$r = post($ec2, ['accion' => 'mes']);
chk('acción del modelo viejo ("mes") ya no existe', $r['ok'], false);
$epro = empresa('pro');
$r = post($epro, ['accion' => 'meta', 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
chk('Pro rechazado (403) y sin escribir', [$r['ok'], $r['code'], DB::val("SELECT meta_equilibrio FROM empresas WHERE id=?", [$epro])], [false, 403, null]);
$eusd = empresa('business', 'USD');
post($eusd, ['accion' => 'meta', 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
chk('moneda copiada de la empresa', DB::val("SELECT meta_moneda FROM empresas WHERE id=?", [$eusd]), 'USD');
$ex1 = empresa(); historia($ex1);
post($ex1, ['accion' => 'meta', 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
chk('lo que guarda el endpoint lo lee estado()', EST($ex1)['ventanas']['mes']['pesimista'], 2.0);

// Tasa: _desde se mueve SOLO cuando el valor cambia.
$r = post($ec2, ['accion' => 'tasa', 'tasa' => '25']);
$t1 = DB::row("SELECT tasa_conv_meta, tasa_conv_meta_desde FROM empresas WHERE id=?", [$ec2]);
chk('tasa guardada con fecha', [$r['ok'], $t1['tasa_conv_meta'], $t1['tasa_conv_meta_desde'] !== null], [true, '25.00', true]);
DB::execute("UPDATE empresas SET tasa_conv_meta_desde = '2026-01-01 00:00:00' WHERE id=?", [$ec2]);
post($ec2, ['accion' => 'tasa', 'tasa' => '25.00']);
chk('misma tasa: _desde NO se mueve', DB::val("SELECT tasa_conv_meta_desde FROM empresas WHERE id=?", [$ec2]), '2026-01-01 00:00:00');
post($ec2, ['accion' => 'tasa', 'tasa' => '30%']);
$t2 = DB::row("SELECT tasa_conv_meta, tasa_conv_meta_desde FROM empresas WHERE id=?", [$ec2]);
chk('tasa distinta: _desde se mueve', [$t2['tasa_conv_meta'], $t2['tasa_conv_meta_desde'] !== '2026-01-01 00:00:00'], ['30.00', true]);
$ev2 = empresa('business', 'MXN', 40.0);
post($ec2, ['accion' => 'tasa', 'tasa' => '12,5']);
chk('tasa "12,5" = 12.5 (coma decimal)', DB::val("SELECT tasa_conv_meta FROM empresas WHERE id=?", [$ec2]), '12.50');
chk('la tasa de OTRA empresa no se toca', DB::val("SELECT tasa_conv_meta FROM empresas WHERE id=?", [$ev2]), '40.00');
DB::execute("UPDATE empresas SET tasa_conv_meta_desde = '2026-01-01 00:00:00' WHERE id=?", [$ec2]);
post($ec2, ['accion' => 'tasa', 'tasa' => '12.504']);
chk('tasa redondeada: 12.504 = 12.50, _desde no se mueve', DB::val("SELECT tasa_conv_meta_desde FROM empresas WHERE id=?", [$ec2]), '2026-01-01 00:00:00');
$r = post($ec2, ['accion' => 'tasa', 'tasa' => '95']);
chk('tasa 95 rechazada, la anterior queda', [$r['ok'], DB::val("SELECT tasa_conv_meta FROM empresas WHERE id=?", [$ec2])], [false, '12.50']);
$r = post($ec2, ['accion' => 'tasa', 'tasa' => 'mucho']);
chk('tasa no numérica rechazada', $r['ok'], false);
post($ec2, ['accion' => 'tasa', 'tasa' => '']);
$r = post($ec2, ['accion' => 'tasa', 'tasa' => '30']);
$r = post($ec2, ['accion' => 'tasa', 'tasa' => '25%']);
chk('tasa "25%" se entiende (no borra)', [$r['ok'], DB::val("SELECT tasa_conv_meta FROM empresas WHERE id=?", [$ec2])], [true, '25.00']);
post($ec2, ['accion' => 'tasa', 'tasa' => '']);
chk('tasa vacía = no declarada (NULL, sin fecha)', DB::row("SELECT tasa_conv_meta, tasa_conv_meta_desde FROM empresas WHERE id=?", [$ec2]), ['tasa_conv_meta' => null, 'tasa_conv_meta_desde' => null]);
sin_columnas(true);
$r = post($ec2, ['accion' => 'meta', 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
chk('sin migrar: error limpio, no 500 crudo', [$r['ok'] ?? null, $r['code'] ?? null], [false, 500]);
sin_columnas(false);
@unlink($runner);

echo "\n── Captura: render de la pestaña ──\n";
if (!function_exists('e')) { function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
function render_metas(int $empresa_id): string {
    $empresa = DB::row("SELECT * FROM empresas WHERE id=?", [$empresa_id]);
    $tab_activo = 'metas';
    ob_start(); include __DIR__ . '/../modules/config/_metas.php'; return ob_get_clean();
}
reloj('2026-09-25 10:00:00');
$er = empresa('business', 'MXN', 27.5);
metas($er, 180000, 250000, 320000);
$html = render_metas($er);
chk('UN solo bloque de meta, sin meses', substr_count($html, 'id="mt_meta"') === 1 && !preg_match('/septiembre|octubre|enero 20|mt-row/i', $html));
chk('los tres campos con la meta (con comas)', str_contains($html, 'value="180,000"') && str_contains($html, 'value="250,000"') && str_contains($html, 'value="320,000"'));
chk('"Quitar meta" cuando hay meta', str_contains($html, 'onclick="mtQuitarMeta()"'));
chk('tasa precargada', str_contains($html, 'value="27.5"'));
chk('texto literal de la tasa', str_contains($html, 'De cada 100 cotizaciones que envías, cuántas quieres vender.'));
chk('dice que aplica a todos los meses', str_contains($html, 'Aplica a todos los meses'));
chk('sin aviso de moneda', str_contains($html, 'recaptúrala'), false);
chk('tasa como texto (no type=number: el navegador mandaría "" y borraría)', (bool)preg_match('/id="mt_tasa" type="text"/', $html));
$ev0 = empresa();
$h0 = render_metas($ev0);
chk('sin meta: campos vacíos y sin "Quitar meta"', !str_contains($h0, 'onclick="mtQuitarMeta()"') && substr_count($h0, 'onblur="mtFormato(this)" value=""') === 3);
$exs = empresa(); DB::execute("UPDATE empresas SET moneda='<x>' WHERE id=?", [$exs]); metas($exs, 1, 2, 3, 'MXN');
$hx = render_metas($exs);
chk('XSS: la moneda se escapa en la pestaña', str_contains($hx, '&lt;X&gt;') && !str_contains($hx, '<X>'));
$pj = file_get_contents(__DIR__ . '/../modules/config/_metas.php');
chk('el formato no toca "180.000" ni "0x10"', str_contains($pj, "/^[\\d,]+(\\.\\d{1,2})?$/"));
DB::execute("UPDATE empresas SET moneda='USD' WHERE id=?", [$er]);
chk('aviso de moneda distinta', str_contains(render_metas($er), 'Tu meta está capturada en MXN y la empresa ahora opera en USD: recaptúrala.'));
sin_columnas(true);
$h2 = render_metas($er);
chk('sin migrar: aviso, sin campos, sin error', str_contains($h2, 'todavía no están disponibles') && !str_contains($h2, 'id="mt_meta"'));
sin_columnas(false);

echo "\n── Captura: cableado ──\n";
$cfg = file_get_contents(__DIR__ . '/../modules/config/index.php');
$rt  = file_get_contents(__DIR__ . '/../core/Router.php');
$gm  = file_get_contents(__DIR__ . '/../modules/config/guardar_metas.php');
chk('metas en la lista blanca de pestañas', (bool)preg_match("/'termometro','metas'/", $cfg));
chk('gate Business por URL directa', (bool)preg_match("/\['termometro', 'historial', 'metas'\]/", $cfg));
chk('enlace dentro del bloque es_business', (bool)preg_match("/es_business'\]\): \?>(?:(?!endif).)*tab=metas/s", $cfg));
chk('include dentro de es_business', str_contains($cfg, "if (\$plan_info['es_business']) include __DIR__ . '/_metas.php';"));
chk('ruta POST /config/metas', str_contains($rt, "self::post('/config/metas'"));
chk('endpoint: admin + csrf + plan ANTES de leer el cuerpo',
    ($a = strpos($gm, 'Auth::requerir_admin()')) !== false && ($b = strpos($gm, 'csrf_check()')) > $a
    && ($c = strpos($gm, 'plan_ok(')) > $b && strpos($gm, "php://input") > $c);

echo "\n── Sin migrar → sin_metas, sin excepción ──\n";
sin_columnas(true);
$r = null; $exc = false;
try { $r = EST($e); } catch (\Throwable $x) { $exc = true; }
chk('no lanza', $exc, false);
chk('sin_metas', $r['estado'] ?? null, 'sin_metas');
sin_columnas(false);
DB::pdo()->exec("RENAME TABLE empresa_metas_estado TO empresa_metas_estado_x");
$r = EST($ep);
chk('sin tabla de histéresis: nivel crudo igual', $r['ventanas']['mes']['nivel'], $r['ventanas']['mes']['nivel_crudo']);
DB::pdo()->exec("RENAME TABLE empresa_metas_estado_x TO empresa_metas_estado");

echo "\n── Frases y fugas (lo que puede leer un asesor) ──\n";
reloj('2026-09-25 10:00:00');
$n = MetasEmpresa::nivel($ep);
$claves_ok = ['mes', 'd30', 'conv', 'dias', 'mes_nombre', 'corte'];
chk('nivel() solo trae etiquetas y calendario', array_keys($n), $claves_ok);
$vals = json_encode($n);
chk('nivel() sin los montos del fixture', !preg_match('/85000|100000|50000|150000/', $vals));
$f = MetasEmpresa::frases($n);
chk('frase mes', $f['mes'], 'La empresa va cerca de su meta en este mes.');
$ff = MetasEmpresa::frases($n, true);
chk('fechada: mes con nombre', $ff['mes'], 'La empresa va cerca de su meta en septiembre.');
// Todas las combinaciones posibles, en las dos formas.
$todas = [];
foreach (array_merge(MetasEmpresa::NIVELES, ['sin_historia', 'sin_metas', 'gris']) as $nv) {
    foreach (['debajo', 'en', 'arriba', 'gris', 'sin_meta'] as $cv) {
        foreach ([false, true] as $fe) {
            $todas = array_merge($todas, array_values(array_filter(MetasEmpresa::frases(
                ['mes' => $nv, 'd30' => $nv, 'conv' => $cv,
                 'dias' => 25, 'mes_nombre' => 'septiembre', 'corte' => '2026-09-25'], $fe))));
        }
    }
}
$todas = array_unique($todas);
chk('hay frase para los 10 niveles × 2 ventanas + conv', count($todas) >= 10 * 2 + 3);
$mal = array_filter($todas, fn($x) => preg_match('/[$%]|\d{2,}[.,]\d|\bvas\b|te faltan|tu meta/iu', $x));
chk('ninguna con $, %, montos, "vas", "te faltan", "tu meta"', array_values($mal), []);
$dig = array_filter($todas, fn($x) => preg_match('/\d/', preg_replace('/(30 días|\d{1,2}\/[A-Z][a-z]{2})/u', '', $x)));
chk('los únicos dígitos son calendario (30 días, 25/Sep)', array_values($dig), []);
$sin3 = array_filter($todas, fn($x) => !str_starts_with($x, 'La empresa') && !str_starts_with($x, 'Todavía'));
chk('todas en tercera persona, la empresa de sujeto', array_values($sin3), []);
chk('sin_metas no produce texto', array_filter(MetasEmpresa::frases(['mes' => 'sin_metas', 'd30' => 'sin_metas', 'conv' => 'sin_meta'])), []);
chk('d30 fechado', MetasEmpresa::frases(['d30' => 'baja', 'corte' => '2026-09-25'], true)['d30'], 'La empresa va baja en los 30 días al 25/Sep.');

echo "\n── Memo ──\n";
$a = MetasEmpresa::estado($ep);
DB::execute("UPDATE empresas SET meta_pesimista = 1 WHERE id = ?", [$ep]);
chk('memo: mismo request, mismo resultado', MetasEmpresa::estado($ep) === $a);
MetasEmpresa::reset();
chk('reset: relee', MetasEmpresa::estado($ep)['ventanas']['mes']['pesimista'], 1.0);

echo "\n── Contrato de fuente ──\n";
$src = file_get_contents(__DIR__ . '/../core/MetasEmpresa.php');
chk('sin marcadores con nombre en SQL (EMULATE_PREPARES=false)', preg_match('/[\s(,=]:[a-z_]+\b/', $src), 0);
chk('sin NOW() en SQL (el reloj es uno solo)', stripos(preg_replace('#//[^\n]*#', '', $src), 'NOW()'), false);
chk('ActividadScore no menciona MetasEmpresa (decisión 6)', strpos(file_get_contents(__DIR__ . '/../core/ActividadScore.php'), 'MetasEmpresa'), false);
chk('la exclusión del DI va comentada', str_contains($src, 'POR DECISIÓN') && str_contains($src, 'DEL CEO'));

// ═════════════════════════════════════════════════════════════
//  FASE 3/4 — Dónde se ve: reporte del asesor, tarjeta del admin, tip
// ═════════════════════════════════════════════════════════════
echo "\n── Reporte del asesor: sección de la empresa ──\n";
reloj('2026-09-27 10:00:00');
$eR = empresa('business', 'MXN', 15.00); historia($eR, '2026-04-13 08:48:16');
metas($eR, 420000, 590000, 690000); tasa($eR, 0.18, 40);
venta($eR, '2026-09-10 10:00:00', 806336.30);
if (!function_exists('e')) { function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
require_once __DIR__ . '/../core/RitmoReporte.php';
$nR = MetasEmpresa::nivel($eR);
$dR = ['nombre' => 'Asesor', 'win' => 20, 'score' => null, 'tip' => null, 'metas' => $nR,
       'secciones' => ['empresa' => MetasEmpresa::lineas_reporte($nR),
                       'comovas' => ['COMOVAS'], 'resumen' => ['RESUMEN'], 'embudo' => [], 'ritmo' => [], 'cinco' => [],
                       'brecha' => [], 'casos' => [], 'precio' => [], 'calidad' => [], 'radar' => [], 'consejo' => [], 'meta' => []]];
$hR = RitmoReporte::render($dR);
chk('título fechado, sin "este mes" ni "Meta"', str_contains($hR, 'La empresa en septiembre (al 27/Sep)'));
chk('frase del mes fechada', str_contains($hR, 'La empresa ya sobrepasó su meta optimista en septiembre.'));
chk('frase de 30 días fechada', str_contains($hR, 'La empresa ya sobrepasó su meta optimista en los 30 días al 27/Sep.'));
chk('frase de conversión', str_contains($hR, 'La empresa cierra por encima de lo que busca.'));
chk('va entre "Cómo vas" y "Resumen"', strpos($hR, 'COMOVAS') < strpos($hR, 'La empresa en septiembre') && strpos($hR, 'La empresa en septiembre') < strpos($hR, 'RESUMEN'));
$secR = substr($hR, strpos($hR, 'La empresa en septiembre'), strpos($hR, 'RESUMEN') - strpos($hR, 'La empresa en septiembre'));
chk('la sección no trae cifras de la meta ni de lo vendido', !preg_match('/\$|%|806|590|690|420|18|15/', preg_replace('/27\/Sep|30 días/', '', $secR)));
chk('en este mes NUNCA en el reporte (se guarda 7 días)', str_contains($hR, 'en este mes'), false);
$dR['metas'] = null; $dR['secciones']['empresa'] = [];
chk('sin metas: sin sección', str_contains(RitmoReporte::render($dR), 'La empresa en'), false);
$rrs = file_get_contents(__DIR__ . '/../core/RitmoReporte.php');
chk('generar() llena metas con nivel() (sin cifras) y _componer usa lineas_reporte()',
    str_contains($rrs, "\$d['metas'] = MetasEmpresa::nivel(\$empresa_id)") && str_contains($rrs, "\$empresa = MetasEmpresa::lineas_reporte(\$d['metas']);"));
$lr = MetasEmpresa::lineas_reporte($nR);
chk('lineas_reporte: 3 renglones, fechados, sin vacíos', [count($lr), str_contains(implode(' ', $lr), 'este mes'), in_array('', $lr, true)], [3, false, false]);
$lh = MetasEmpresa::lineas_reporte(['mes' => 'sin_historia', 'd30' => 'sin_historia', 'conv' => 'sin_meta', 'mes_nombre' => 'septiembre', 'corte' => '2026-09-27']);
chk('lineas_reporte sin historia: un solo renglón', $lh, ['Todavía no hay suficiente historia para leer cómo va la empresa.']);
chk('expediente() NO cambia (lo usa el tip del dashboard)', !preg_match('/function expediente.*?MetasEmpresa.*?function generar/s', $rrs));

echo "\n── Tarjeta del admin en el dashboard ──\n";
if (!class_exists('Auth')) { class Auth { public static bool $admin = true; public static function es_admin(): bool { return self::$admin; } } }
if (!function_exists('format_money')) { function format_money($m, $mon = 'MXN') { return '$' . number_format((float)$m, 2); } }
define('EMPRESA_ID', $eR);
$card = function () { MetasEmpresa::reset(); ob_start(); include __DIR__ . '/../modules/dashboard/_metas.php'; return ob_get_clean(); };
Auth::$admin = true;
$hC = $card();
chk('admin ve la tarjeta', str_contains($hC, 'Metas de la empresa'));
chk('con lo vendido y las tres metas', str_contains($hC, '$806,336.30') && str_contains($hC, '$420,000.00') && str_contains($hC, '$590,000.00') && str_contains($hC, '$690,000.00'));
chk('con su frase de nivel', str_contains($hC, 'La empresa ya sobrepasó su meta optimista en este mes.'));
chk('cierre real contra el buscado', str_contains($hC, 'Cierre <b>18%</b>') && str_contains($hC, 'buscas 15%'));
chk('el cierre va junto al título, antes del primer bloque', strpos($hC, 'Cierre <b>18%</b>') > strpos($hC, 'Metas de la empresa') && strpos($hC, 'Cierre <b>18%</b>') < strpos($hC, 'Este mes ('));
chk('con su frase, en el encabezado', strpos($hC, 'La empresa cierra por encima de lo que busca.') < strpos($hC, 'Este mes ('));
chk('tarjeta compacta: sin las notas de abajo', !str_contains($hC, 'misma tasa de cierre que ven tus asesores') && !str_contains($hC, 'Solo cuentan ventas con anticipo'));
chk('liga a editar', str_contains($hC, 'href="/config?tab=metas"'));
Auth::$admin = false;
chk('el ASESOR no ve nada de la tarjeta', $card(), '');
Auth::$admin = true;
sin_meta($eR);
chk('sin meta capturada: no hay tarjeta', $card(), '');
metas($eR, 420000, 590000, 690000, 'USD');
chk('moneda distinta: la tarjeta avisa en vez de desaparecer', str_contains($card(), 'la empresa ahora opera en MXN'));
metas($eR, 900000, 1200000, 1500000);                           // aún falta
tasa($eR, 0.18, 40);
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2026,8,10,800000)", [$eR]);   // ticket 80,000
$hF = $card();
chk('faltante hacia el equilibrio', str_contains($hF, 'faltan <b>$93,663.70</b> para la meta del punto de equilibrio'));
chk('frase de cotizaciones que faltan, sin contradicción', (bool)preg_match('/Para llegar a el punto de equilibrio este mes, a como cierra hoy la empresa, hacen falta unas <b>\d+<\/b> cotizaciones más; si cerrara a lo que buscas, harían falta <b>\d+<\/b>\./', $hF));
$frF = preg_replace('/\s+/', ' ', substr($hF, (int)strpos($hF, 'Para llegar'), 260));
chk('sin "bastan" ni espacio antes del punto', !str_contains($frF, 'bastan') && !str_contains($frF, ' .'));
// 93,663.70 / 80,000 = 1.17 ventas → /0.18 = 6.5 → 7 ; /0.15 = 7.8 → 8
chk('N=7 a como cierra hoy, M=8 con lo que buscas', str_contains($frF, 'unas <b>7</b> cotizaciones más') && str_contains($frF, 'harían falta <b>8</b>'));
tasa($eR, 0.18, 3);
$hG = $card();
chk('en gris no se muestra la tasa', str_contains($hG, '<b>—</b>') && str_contains($hG, 'todavía no hay suficientes cotizaciones para comparar'));
tasa($eR, 0.18, 40);
$eC = empresa('business', 'MXN', 15.0); metas($eC, 1, 2, 3); venta($eC, '2026-09-20 10:00:00', 5);
DB::execute("UPDATE empresas SET id=id WHERE id=?", [$eC]);
metas($eR, 420000, 590000, 690000);

echo "\n── Tip del termómetro ──\n";
$dsh = file_get_contents(__DIR__ . '/../modules/dashboard/index.php');
MetasEmpresa::reset();
chk('tip: DOS frases, mes y 30 días', MetasEmpresa::lineas_tip($eR), ['La empresa ya sobrepasó su meta optimista en este mes.', 'La empresa ya sobrepasó su meta optimista en los últimos 30 días.']);
$eT = empresa(); historia($eT); metas($eT, 420000, 590000, 690000); venta($eT, '2026-09-10 10:00:00', 450000);
MetasEmpresa::reset();
$lt = MetasEmpresa::lineas_tip($eT);
chk('tip SIN cifras (ni meta ni vendido); el único número es "30 días"', count($lt) === 2 && !preg_match('/\d/', str_replace('30 días', '', implode(' ', $lt))));
$eT2 = empresa(); metas($eT2, 1, 2, 3); venta($eT2, '2026-09-20 10:00:00', 5);   // sin historia
MetasEmpresa::reset();
chk('sin historia: el tip no muestra nada', MetasEmpresa::lineas_tip($eT2), []);
$eT3 = empresa(); historia($eT3);
MetasEmpresa::reset();
chk('sin meta: el tip no muestra nada', MetasEmpresa::lineas_tip($eT3), []);
$eT4 = empresa('business', 'USD'); historia($eT4); metas($eT4, 1, 2, 3, 'MXN');
MetasEmpresa::reset();
chk('moneda distinta: el tip no muestra nada', MetasEmpresa::lineas_tip($eT4), []);
MetasEmpresa::reset();
chk('tip, mismo nivel: UNA frase con las dos ventanas', MetasEmpresa::texto_tip($eR), 'La empresa ya sobrepasó su meta optimista en este mes y en los últimos 30 días.');
$eT5 = empresa(); historia($eT5); metas($eT5, 420000, 590000, 690000);
venta($eT5, '2026-08-30 10:00:00', 300000); venta($eT5, '2026-09-10 10:00:00', 450000);   // mes 450k (cerca) · 30 días 750k (sobrepasada)
MetasEmpresa::reset();
chk('tip, niveles distintos: dos frases seguidas', MetasEmpresa::texto_tip($eT5), 'La empresa va por debajo de su meta en este mes. La empresa ya sobrepasó su meta optimista en los últimos 30 días.');
// "llegó" con apellido (CEO, 28 sep): pasó la pesimista, no la optimista.
// La frase sigue DESPUÉS de la ventana, así que la unión de las dos ventanas
// va junto a "en este mes", no al final.
$eT6 = empresa(); historia($eT6); metas($eT6, 420000, 590000, 690000); venta($eT6, '2026-09-10 10:00:00', 600000);
MetasEmpresa::reset();
chk('llegó, mismo nivel: la unión va junto a la ventana', MetasEmpresa::texto_tip($eT6),
    'La empresa ya llegó a su meta pesimista en este mes y en los últimos 30 días; todavía le falta mucho para la optimista.');
chk('llegó en el reporte: fechado, dice pesimista y que va por la optimista',
    MetasEmpresa::lineas_reporte(MetasEmpresa::nivel($eT6))[0] ?? '',
    'La empresa ya llegó a su meta pesimista en septiembre; todavía le falta mucho para la optimista.');
// Consejo del Director: una línea de la empresa, fechada, sin cifras.
$lc = fn($k) => MetasEmpresa::linea_consejo(['mes' => $k, 'mes_nombre' => 'septiembre']);
chk('consejo abajo', $lc('baja'), 'La empresa va baja en septiembre: cada cierre de esta semana pesa.');
chk('consejo camino', $lc('cerca'), 'La empresa está cerca de su meta de septiembre: las ventas de esta semana pueden completarla.');
chk('consejo arriba (llegó, medio, casi)', [$lc('llego'), $lc('medio_optima'), $lc('casi_optima')],
    array_fill(0, 3, 'La empresa ya pasó su meta pesimista de septiembre y va por la optimista: cada cierre ahora es la diferencia.'));
chk('consejo sobrepasada', $lc('sobrepasada'), 'La empresa ya superó su meta optimista de septiembre: toca sostener el ritmo.');
chk('consejo sin nivel real: nada', [$lc('sin_historia'), $lc('sin_metas'), MetasEmpresa::linea_consejo([])], ['', '', '']);
$todas_lc = array_map($lc, MetasEmpresa::NIVELES);
chk('consejo SIN cifras en ningún nivel', !preg_match('/\d/', implode(' ', $todas_lc)));
$rr = file_get_contents(__DIR__ . '/../core/RitmoReporte.php');
chk('consejo: la línea de la empresa va DESPUÉS de "Va sólido" (al final)',
    strpos($rr, 'linea_consejo') > strpos($rr, 'Va sólido. Para subir'));
MetasEmpresa::reset();
chk('tip sin historia: nada', MetasEmpresa::texto_tip($eT2), '');
chk('tip sin historia: nada aunque traiga debilidad', MetasEmpresa::texto_tip($eT2, 'seguimiento'), '');

echo "\n── Tip: puente según la empresa y la debilidad del tip ──\n";
MetasEmpresa::reset();
chk('arriba + seguimiento (Abigail hoy)', MetasEmpresa::texto_tip($eR, 'seguimiento'),
    'La empresa ya sobrepasó su meta optimista en este mes y en los últimos 30 días. Para sumarte a ese resultado, empieza por ponerte al día con tus seguimientos.');
chk('arriba + bien', MetasEmpresa::texto_tip($eR, 'bien'),
    'La empresa ya sobrepasó su meta optimista en este mes y en los últimos 30 días. Tu trabajo es parte de ese resultado.');
chk('debilidad desconocida (legacy): sin puente', MetasEmpresa::texto_tip($eR, null), 'La empresa ya sobrepasó su meta optimista en este mes y en los últimos 30 días.');
chk('debilidad que no existe: sin puente', MetasEmpresa::texto_tip($eR, 'xyz'), 'La empresa ya sobrepasó su meta optimista en este mes y en los últimos 30 días.');
$eB = empresa(); historia($eB); metas($eB, 420000, 590000, 690000); venta($eB, '2026-09-10 10:00:00', 100000);
MetasEmpresa::reset();
chk('abajo + citas', MetasEmpresa::texto_tip($eB, 'citas'),
    'La empresa ni siquiera llega al punto de equilibrio en este mes y en los últimos 30 días. Cada venta cuenta: agendar citas es lo que más ayuda ahora.');
chk('abajo + bien', str_ends_with(MetasEmpresa::texto_tip($eB, 'bien'), 'Tu trabajo está empujando; sigue así.'));
$eCm = empresa(); historia($eCm); metas($eCm, 420000, 590000, 690000); venta($eCm, '2026-09-10 10:00:00', 540000);
MetasEmpresa::reset();
chk('camino + cierre', str_ends_with(MetasEmpresa::texto_tip($eCm, 'cierre'), 'Está cerca: cerrar lo que ya abriste puede ser lo que falte.'));
chk('camino + bien', str_ends_with(MetasEmpresa::texto_tip($eCm, 'bien'), 'Tu ritmo ayuda a que llegue.'));
MetasEmpresa::reset();
chk('la banda la manda el MES (mes camino, 30 días arriba)', str_ends_with(MetasEmpresa::texto_tip($eT5, 'ticket'), 'Está cerca: subir tu ticket puede ser lo que falte.'));
// Las 12 debilidades con acción producen puente, y ninguno lleva cifras ni "vas".
$rt_src = file_get_contents(__DIR__ . '/../core/RitmoTip.php');
preg_match_all("/return \\['([a-z_]+)'/", $rt_src, $mm);
$debs = array_values(array_diff(array_unique($mm[1]), ['handle']));   // 'handle' es la clave del arreglo de salida, no una debilidad
$sin = [];
foreach ($debs as $dk) {
    MetasEmpresa::reset();
    $tx = MetasEmpresa::texto_tip($eB, $dk);
    if ($tx === 'La empresa ni siquiera llega al punto de equilibrio en este mes y en los últimos 30 días.') $sin[] = $dk;
    if (preg_match('/\d|\bvas\b|te faltan|tu meta/iu', str_replace('30 días', '', $tx))) $sin[] = "CIFRA:$dk";
}
chk('toda debilidad que devuelve RitmoTip tiene puente (' . count($debs) . ' debilidades)', $sin, []);
chk('el dashboard pasa la debilidad del tip SOLO si el tip mostrado es el del motor nuevo',
    str_contains($dsh, "(\$ts_rt && trim(\$ts_rt['texto']) !== '') ? (\$ts_rt['debilidad'] ?? null) : null"));
chk('se anexa AL TIP, en la parte de "ver más" ($diag_b2), no como bloque aparte',
    str_contains($dsh, "if (\$ts_meta_txt !== '') \$diag_b2 = trim(\$diag_b2 . ' ' . \$ts_meta_txt);") && !str_contains($dsh, 'thermo-metas'));
chk('la primera parte del tip ($diag_b1) se corta ANTES de anexar: queda idéntica',
    strpos($dsh, "\$diag_b1 = trim(mb_substr(\$perfil, 0, \$pcut));") < strpos($dsh, 'MetasEmpresa::texto_tip(EMPRESA_ID,')
    && !preg_match('/\$perfil\s*=.*meta/i', $dsh));
chk('el texto del tip NO se toca ($ts_diag no menciona metas)', !preg_match('/\$ts_diag\s*=.*Metas/', $dsh) && !preg_match('/\$perfil\s*=.*meta/i', $dsh));
chk('ranking del equipo: cada fila lleva el remate de la empresa con SU debilidad (CEO, 28 sep)',
    str_contains($dsh, "(\$es_rt && trim(\$es_rt['texto']) !== '') ? (\$es_rt['debilidad'] ?? null) : null")
    && str_contains($dsh, "if (\$es_meta_txt !== '') \$es_diag = trim(\$es_diag . ' ' . \$es_meta_txt);")
    && strpos($dsh, '$es_meta_txt') < strpos($dsh, '<div class="lb-diag"><?= e($es_diag) ?></div>'));
chk('la tarjeta del admin se incluye antes de Ritmo', strpos($dsh, "include __DIR__ . '/_metas.php'") < strpos($dsh, "include __DIR__ . '/_ritmo.php'"));

// ═════════════════════════════════════════════════════════════
//  Reportes › Financiero: historial mensual + metas de 12 meses
// ═════════════════════════════════════════════════════════════
echo "\n── Metas: últimos 12 meses (solo sistema) ──\n";
reloj('2026-09-27 10:00:00');
$eH = empresa(); metas($eH, 420000, 590000, 690000);
venta($eH, '2026-04-13 08:48:16', 381246.92);               // abril: sobre equilibrio? no (381k < 420k)
venta($eH, '2026-05-10 10:00:00', 700000);                   // mayo: las tres
venta($eH, '2026-06-10 10:00:00', 600000);                   // junio: equilibrio y pesimista
venta($eH, '2026-06-11 10:00:00', 500000, 0);                // sin anticipo: no cuenta
venta($eH, '2026-06-12 10:00:00', 500000, 5, 'pendiente', 'utilizado');   // DI: no cuenta
// julio: nada
venta($eH, '2026-08-10 10:00:00', 450000);                   // agosto: solo equilibrio
venta($eH, '2026-09-10 10:00:00', 500000);                   // septiembre (en curso): equilibrio sí, lo demás en curso
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2026,3,8,999999)", [$eH]);
$hm = MetasEmpresa::historial_meses($eH);
$ym = array_map(fn($r) => sprintf('%04d-%02d', $r['anio'], $r['mes']), $hm['meses']);
chk('meses del sistema, del más reciente al primero con venta (los importados NO entran)', $ym, ['2026-09','2026-08','2026-07','2026-06','2026-05','2026-04']);
$porym = array_combine($ym, $hm['meses']);
$tri = fn($r) => [$r['equilibrio'], $r['pesimista'], $r['optimista']];
chk('mayo: las tres ✓', $tri($porym['2026-05']), [true, true, true]);
chk('junio: sin anticipo ni DI → 600k: ✓ ✓ ✗', [$porym['2026-06']['vendido'], $tri($porym['2026-06'])], [600000.0, [true, true, false]]);
chk('julio sin ventas: aparece con ✗ ✗ ✗', [$porym['2026-07']['vendido'], $tri($porym['2026-07'])], [0.0, [false, false, false]]);
chk('abril: ✗ ✗ ✗', $tri($porym['2026-04']), [false, false, false]);
chk('septiembre en curso: ✓ lo logrado, "en curso" (null) lo que falta', [$porym['2026-09']['en_curso'], $tri($porym['2026-09'])], [true, [true, null, null]]);
chk('solo el mes actual va en curso', count(array_filter($hm['meses'], fn($r) => $r['en_curso'])), 1);
reloj('2027-09-27 10:00:00');
$hm2 = MetasEmpresa::historial_meses($eH);
chk('nunca más de 12 meses', [count($hm2['meses']), $hm2['meses'][11]['nombre']], [12, 'octubre 2026']);
reloj('2026-09-27 10:00:00');
$eHn = empresa();
chk('sin meta: nada', MetasEmpresa::historial_meses($eHn), []);
$eHp = empresa('pro'); metas($eHp, 1, 2, 3); venta($eHp, '2026-09-10 10:00:00', 5);
chk('Pro: nada', MetasEmpresa::historial_meses($eHp), []);
$eHu = empresa('business', 'USD'); metas($eHu, 1, 2, 3, 'MXN'); venta($eHu, '2026-09-10 10:00:00', 5);
chk('meta en otra moneda: nada', MetasEmpresa::historial_meses($eHu), []);
$eHv = empresa(); metas($eHv, 1, 2, 3);
chk('sin ventas con anticipo: nada', MetasEmpresa::historial_meses($eHv), []);

echo "\n── Metas 12 meses: render (solo admin) ──\n";
$m12 = function (int $eid, bool $admin) { $empresa_id = $eid; $es_admin = $admin; ob_start(); include __DIR__ . '/../modules/reportes/_metas_12m.php'; return ob_get_clean(); };
$h12 = $m12($eH, true);
chk('admin ve la tabla con sus 6 meses', substr_count($h12, '<tr>') - 1, 6);
chk('encabezados con la meta', str_contains($h12, 'Punto de equilibrio<small>$420,000</small>') && str_contains($h12, 'Meta optimista<small>$690,000</small>'));
// sep ✓·· · ago ✓✗✗ · jul ✗✗✗ · jun ✓✓✗ · may ✓✓✓ · abr ✗✗✗
chk('palomitas y tachas (7 ✓, 9 ✗, 2 en curso)', [substr_count($h12, 'class="m12-ok"'), substr_count($h12, 'class="m12-no"'), substr_count($h12, '<span class="m12-cur">en curso</span>')], [7, 9, 2]);
chk('resumen de meses cerrados', str_contains($h12, 'De 5 meses cerrados:') && str_contains($h12, 'equilibrio 3/5 · pesimista 2/5 · optimista 1/5'));
chk('el asesor NO ve nada', $m12($eH, false), '');
chk('sin meta: no hay tabla', $m12($eHn, true), '');

echo "\n── Historial mensual: sistema primero, importados abajo ──\n";
if (!function_exists('rep_historial_mensual')) require __DIR__ . '/../modules/reportes/_historial_mensual.php';
$eF = empresa();
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, cotizaciones_cantidad, ventas_cantidad, ventas_monto, tasa_cierre) VALUES
  (?,2026,3,40,8,579010.24,20.0),(?,2026,2,30,8,506749.33,26.7),(?,2025,12,20,9,373484.5,45.0)", [$eF, $eF, $eF]);
foreach ([['2026-04-10',3],['2026-05-10',5],['2026-09-01',2]] as [$d, $k]) for ($i = 0; $i < $k; $i++) cot($eF, "$d 10:00:00");
cot($eF, '2026-05-10 10:00:00', 'borrador'); cot($eF, '2026-05-10 10:00:00', 'enviada', 1);
cot($eF, '2021-01-01 10:00:00');                              // cotización vieja (import): antes del sistema → no inventa meses
venta($eF, '2026-04-20 10:00:00', 1000); venta($eF, '2026-05-20 10:00:00', 2000, 0);   // sin pago TAMBIÉN cuenta (misma cuenta que la gráfica)
venta($eF, '2026-05-21 10:00:00', 3000, 5, 'cancelada');
$hf = rep_historial_mensual($eF, '', '', 24, '2026-09-27');
$yf = array_map(fn($r) => sprintf('%04d-%02d', $r['anio'], $r['mes']), $hf);
chk('orden: sistema (sep→abr) y luego importados (mar→dic 2025), sin encimarse', $yf, ['2026-09','2026-08','2026-07','2026-06','2026-05','2026-04','2026-03','2026-02','2025-12']);
$pf = array_combine($yf, $hf);
chk('mayo del sistema: cotizaciones sin borrador ni suspendida (5 + las 2 de las ventas; la cancelada también se cotizó)', $pf['2026-05']['cotizaciones'], 7);
chk('mayo: ventas no canceladas, con o sin pago (= gráfica)', [$pf['2026-05']['ventas'], $pf['2026-05']['monto']], [1, 2000.0]);
chk('abril: 3 + 1 cotizaciones, 1 venta, tasa 25%', [$pf['2026-04']['cotizaciones'], $pf['2026-04']['ventas'], $pf['2026-04']['tasa']], [4, 1, 25.0]);
chk('mes del sistema sin movimiento aparece en cero', [$pf['2026-07']['cotizaciones'], $pf['2026-07']['ventas']], [0, 0]);
chk('importado conserva sus números', [$pf['2026-03']['ventas'], $pf['2026-03']['monto'], $pf['2026-03']['tasa']], [8, 579010.24, 20.0]);
chk('tope de filas', count(rep_historial_mensual($eF, '', '', 7, '2026-09-27')), 7);
DB::execute("UPDATE ventas SET vendedor_id = 55 WHERE empresa_id = ? AND total = 1000", [$eF]);
$ha = rep_historial_mensual($eF, 'AND (v.usuario_id = 55 OR v.vendedor_id = 55)', 'AND (c.usuario_id = 55 OR c.vendedor_id = 55)', 24, '2026-09-27');
$pa = array_combine(array_map(fn($r) => sprintf('%04d-%02d', $r['anio'], $r['mes']), $ha), $ha);
chk('asesor: los meses del sistema se filtran a lo suyo', [$pa['2026-04']['ventas'], $pa['2026-05']['ventas']], [1, 0]);
$eF2 = empresa();                                             // sin importados: desde su primera cotización o venta
cot($eF2, '2026-07-15 10:00:00'); venta($eF2, '2026-08-02 10:00:00', 500);
$yf2 = array_map(fn($r) => sprintf('%04d-%02d', $r['anio'], $r['mes']), rep_historial_mensual($eF2, '', '', 24, '2026-09-27'));
chk('sin importados: desde el primer movimiento', $yf2, ['2026-09','2026-08','2026-07']);
chk('empresa sin nada: tabla vacía', rep_historial_mensual(empresa(), '', '', 24, '2026-09-27'), []);
$rix = file_get_contents(__DIR__ . '/../modules/reportes/index.php');
chk('Financiero usa la tabla nueva e incluye metas de 12 meses después', ($p1 = strpos($rix, 'rep_historial_mensual((int)$empresa_id')) !== false && strpos($rix, "include __DIR__ . '/_metas_12m.php'") > $p1);
chk('la tabla ya no dice "importado" ni tiene columna de origen', !str_contains($rix, 'Historial importado') && !str_contains($rix, '>Origen<'));

// Limpieza: otras pruebas crean sus tablas con CREATE TABLE IF NOT EXISTS
// (p. ej. test_plan_log con `empresas`). Si esta simulación dejara su esquema
// mínimo, esas pruebas tronarían por columnas faltantes.
DB::pdo()->exec("SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS empresa_metas_estado, empresa_metas_mes, ventas, cotizaciones,
                     desc_int_activaciones, historial_mensual, empresas;
SET FOREIGN_KEY_CHECKS=1;");

echo "\n" . ($fail === 0 ? "✓ SIMULACIÓN METAS OK — $ok comprobaciones contra MariaDB real\n" : "✗ $fail FALLAS de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
