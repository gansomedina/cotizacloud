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
  estado VARCHAR(20) NOT NULL DEFAULT 'enviada', suspendida TINYINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB;
CREATE TABLE ventas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  cotizacion_id INT UNSIGNED NULL, total DECIMAL(14,2) NOT NULL DEFAULT 0,
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
  ventas_cantidad INT UNSIGNED NOT NULL DEFAULT 0, ventas_monto DECIMAL(14,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;
");
// La migración real, sentencia por sentencia (sin comentarios).
$mig = file_get_contents(__DIR__ . '/../migrations/add_empresa_metas.sql');
$mig = preg_replace('/--[^\n]*/', '', $mig);
foreach (array_filter(array_map('trim', explode(';', $mig))) as $sql) DB::pdo()->exec($sql);

echo "\n── Migración ──\n";
chk('empresa_metas_mes existe',    (int)DB::val("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='simtest' AND table_name='empresa_metas_mes'"), 1);
chk('empresa_metas_estado existe', (int)DB::val("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='simtest' AND table_name='empresa_metas_estado'"), 1);
chk('empresas.tasa_conv_meta',     (int)DB::val("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='simtest' AND table_name='empresas' AND column_name IN ('tasa_conv_meta','tasa_conv_meta_desde')"), 2);
chk('nivel cabe el más largo (sin_equilibrio)', (int)DB::val("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_schema='simtest' AND table_name='empresa_metas_estado' AND column_name='nivel'") >= max(array_map('strlen', MetasEmpresa::NIVELES)));

// ── Fixtures ──
function empresa(string $plan = 'business', string $moneda = 'MXN', ?float $tasa = null): int {
    DB::execute("INSERT INTO empresas (plan, moneda, tasa_conv_meta) VALUES (?,?,?)", [$plan, $moneda, $tasa]);
    return (int)DB::pdo()->lastInsertId();
}
function metas(int $e, int $a, int $m, float $E, float $P, float $O, string $mon = 'MXN'): void {
    DB::execute("REPLACE INTO empresa_metas_mes (empresa_id, anio, mes, equilibrio, meta_pesimista, meta_optimista, moneda)
                 VALUES (?,?,?,?,?,?,?)", [$e, $a, $m, $E, $P, $O, $mon]);
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
metas($e, 2026, 8, 50000, 100000, 150000);
metas($e, 2026, 9, 50000, 100000, 150000);
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
$e0 = empresa(); historia($e0); metas($e0, 2026, 8, 1, 2, 3); metas($e0, 2026, 9, 1, 2, 3);
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
    [100000, 'llego'], [134999.99, 'llego'], [135000, 'casi_optima'],
    [149999.99, 'casi_optima'], [150000, 'sobrepasada'], [900000, 'sobrepasada'],
];
foreach ($casos as [$v, $want]) chk("V=$v → $want", $rf->invoke(null, (float)$v, 50000.0, 100000.0, 150000.0), $want);
// Equilibrio arriba del 60/70%: esos escalones quedan vacíos y la lectura sigue coherente.
chk('E=75k: 74,999 → sin_equilibrio', $rf->invoke(null, 74999.0, 75000.0, 100000.0, 150000.0), 'sin_equilibrio');
chk('E=75k: 75,000 → debajo (75% de la pesimista)', $rf->invoke(null, 75000.0, 75000.0, 100000.0, 150000.0), 'debajo');
// Optimista pegada a la pesimista: "llegó" queda vacío.
chk('O=1.05P: P → casi_optima', $rf->invoke(null, 100000.0, 50000.0, 100000.0, 105000.0), 'casi_optima');
chk('E=P=O: todo o nada', $rf->invoke(null, 100000.0, 100000.0, 100000.0, 100000.0), 'sobrepasada');

echo "\n── Mes calendario SIN prorrateo (CEO, 3ª ronda) ──\n";
reloj('2026-09-25 10:00:00');
$ep = empresa(); historia($ep); metas($ep, 2026, 8, 50000, 100000, 150000); metas($ep, 2026, 9, 50000, 100000, 150000);
venta($ep, '2026-09-05 10:00:00', 85000);
$s = EST($ep);
chk('meta del mes = completa', $s['ventanas']['mes']['pesimista'], 100000.0);
chk('85k de 100k → cerca', $s['ventanas']['mes']['nivel'], 'cerca');
reloj('2026-09-01 08:00:00');
$e1 = empresa(); historia($e1); metas($e1, 2026, 8, 50000, 100000, 150000); metas($e1, 2026, 9, 50000, 100000, 150000);
venta($e1, '2026-09-01 07:00:00', 20000);
$s = EST($e1);
chk('día 1 a las 8 am: muy baja contra la meta completa (aceptado)', $s['ventanas']['mes']['nivel'], 'sin_equilibrio');

echo "\n── Últimos 30 días: suma día por día, cruzando febrero ──\n";
reloj('2026-03-01 10:00:00');          // ventana = 31/Ene … 1/Mar: TRES meses
$ef = empresa(); historia($ef, '2025-10-01 12:00:00');
metas($ef, 2026, 1, 3100, 6200, 9300);   // 100/200/300 por día
metas($ef, 2026, 2, 2800, 5600, 8400);   // 100/200/300 por día
metas($ef, 2026, 3, 6200, 12400, 18600); // 200/400/600 por día
$s = EST($ef);
near('E30 = 1·100 + 28·100 + 1·200', $s['ventanas']['d30']['equilibrio'], 3100);
near('P30 = 1·200 + 28·200 + 1·400', $s['ventanas']['d30']['pesimista'], 6200);
chk('d30 no provisional', $s['ventanas']['d30']['provisional'], false);
chk('origen enumera los 3 meses', $s['ventanas']['d30']['origen'], '2026-01,2026-02,2026-03');

echo "\n── d30 sin cobertura → sin_metas ──\n";
reloj('2026-09-25 10:00:00');
$ec = empresa(); historia($ec); metas($ec, 2026, 9, 1, 2, 3);   // agosto sin meta
$s = EST($ec);
chk('d30 sin_metas', $s['ventanas']['d30']['estado'], 'sin_metas');
chk('motivo cobertura', $s['ventanas']['d30']['motivo'], 'cobertura');
chk('mes sí se lee', $s['ventanas']['mes']['estado'], 'ok');

echo "\n── Herencia (solo hacia atrás) y provisional ──\n";
$eh = empresa(); historia($eh);
metas($eh, 2026, 7, 10, 20, 30);
metas($eh, 2026, 11, 999, 9999, 99999);                         // futura: no aplica a septiembre
$s = EST($eh);
chk('septiembre hereda de julio', $s['ventanas']['mes']['origen'], '2026-07');
chk('provisional', $s['ventanas']['mes']['provisional'], true);
chk('la fila futura no se usa', $s['ventanas']['mes']['pesimista'], 20.0);
chk('d30 heredado también es provisional', $s['ventanas']['d30']['provisional'], true);
chk('d30 heredado: origen julio', $s['ventanas']['d30']['origen'], '2026-07');
$en = empresa(); historia($en); metas($en, 2026, 10, 1, 2, 3);  // solo una meta futura
chk('solo meta futura → sin_metas', EST($en)['estado'], 'sin_metas');

echo "\n── Moneda distinta → sin_metas ──\n";
$em = empresa('business', 'USD'); historia($em);
metas($em, 2026, 8, 1, 2, 3, 'MXN'); metas($em, 2026, 9, 1, 2, 3, 'MXN');
$s = EST($em);
chk('mes sin_metas por moneda', [$s['ventanas']['mes']['estado'], $s['ventanas']['mes']['motivo']], ['sin_metas', 'moneda']);
chk('d30 sin_metas por moneda', $s['ventanas']['d30']['motivo'], 'moneda');

echo "\n── Plan ──\n";
$epr = empresa('pro'); historia($epr); metas($epr, 2026, 9, 1, 2, 3);
chk('Pro → sin_metas', EST($epr)['estado'], 'sin_metas');
$ebn = empresa(); metas($ebn, 2026, 9, 1, 2, 3);
DB::execute("UPDATE empresas SET plan='pro' WHERE id=?", [$ebn]);
chk('baja de plan: no se borra la fila', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_mes WHERE empresa_id=?", [$ebn]), 1);
chk('baja de plan: estado sin_metas', EST($ebn)['estado'], 'sin_metas');

echo "\n── Historia mínima: 30 días desde la primera venta con pago ──\n";
$es = empresa(); metas($es, 2026, 8, 1, 2, 3); metas($es, 2026, 9, 1, 2, 3);
venta($es, '2026-08-27 09:00:00', 500);            // hace 29 días
$s = EST($es);
chk('29 días → sin_historia', $s['estado'], 'sin_historia');
chk('ventana sin nivel', $s['ventanas']['mes']['nivel'], null);
chk('nivel() dice sin_historia', MetasEmpresa::nivel($es)['mes'], 'sin_historia');
$f = MetasEmpresa::frases(MetasEmpresa::nivel($es));
chk('frase de historia, una sola', [$f['mes'], $f['d30']], ['Todavía no hay suficiente historia para leer cómo va la empresa.', null]);
chk('sin_historia no escribe histéresis', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_estado WHERE empresa_id=?", [$es]), 0);
$es2 = empresa(); metas($es2, 2026, 9, 1, 2, 3);
venta($es2, '2026-08-26 09:00:00', 500);           // hace 30 días
chk('30 días → ya se lee', EST($es2)['estado'], 'ok');
$es3 = empresa(); metas($es3, 2026, 9, 1, 2, 3);
venta($es3, '2026-07-01 09:00:00', 500, 0);        // vieja pero SIN pago
venta($es3, '2026-07-02 09:00:00', 500, 5, 'pendiente', 'utilizado'); // vieja pero DI
chk('venta vieja sin pago o con DI no cuenta como historia', EST($es3)['estado'], 'sin_historia');

echo "\n── Venta viva: primer pago después, DI quitado, extra, recibo cancelado ──\n";
reloj('2026-09-25 10:00:00');
$ev = empresa(); historia($ev); metas($ev, 2026, 8, 1, 2, 3); metas($ev, 2026, 9, 50000, 100000, 150000);
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
$eh2 = empresa(); historia($eh2); metas($eh2, 2026, 8, 50000, 100000, 150000); metas($eh2, 2026, 9, 50000, 100000, 150000);
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
metas($eh2, 2026, 10, 50000, 100000, 150000);
$s = EST($eh2);
chk('1/Oct: mes nuevo, nivel propio', $s['ventanas']['mes']['nivel'], 'sin_equilibrio');
chk('1/Oct: no es cambio', $s['ventanas']['mes']['cambio'], false);
chk('periodo guardado = 2026-10', DB::val("SELECT periodo FROM empresa_metas_estado WHERE empresa_id=? AND ventana='mes'", [$eh2]), '2026-10');

echo "\n── d30: una venta que sale de la ventana no hace parpadear ──\n";
reloj('2026-09-25 10:00:00');
$ed = empresa(); historia($ed); metas($ed, 2026, 8, 30000, 60000, 90000); metas($ed, 2026, 9, 30000, 60000, 90000);
// P30 ≈ 5 días de ago (60000/31) + 25 de sep (60000/30) ≈ 59,677
venta($ed, '2026-08-28 10:00:00', 3000);
venta($ed, '2026-09-15 10:00:00', 46000);
$s0 = EST($ed)['ventanas']['d30'];
chk('d30 inicial: cerca', $s0['nivel'], 'cerca');
reloj('2026-09-28 10:00:00');       // la de 3,000 sale; P30≈59,871 → 46k = 76.8% (≥ 80%×0.95 = 76%)
$s1 = EST($ed)['ventanas']['d30'];
chk('crudo bajó a debajo', $s1['nivel_crudo'], 'debajo');
chk('pero se sostiene cerca', [$s1['nivel'], $s1['cambio']], ['cerca', false]);

echo "\n── Conversión ──\n";
reloj('2026-09-25 10:00:00');
$ek = empresa('business', 'MXN', 30.00); metas($ek, 2026, 9, 1, 2, 3);
for ($i = 0; $i < 7; $i++) cot($ek, '2026-09-10 10:00:00');
cot($ek, '2026-09-10 10:00:00', 'borrador');
cot($ek, '2026-09-10 10:00:00', 'enviada', 1);
venta($ek, '2026-09-12 10:00:00', 1000);                     // su cotización cuenta como enviada (aceptada)
$c = EST($ek)['conv']['mes'];
chk('borrador y suspendida no son enviadas (7 + 1 aceptada)', $c['enviadas'], 8);
chk('8 enviadas ya se lee (CONV_MIN)', $c['nivel'] !== 'gris');
near('tasa 1/8', $c['tasa'], 0.125, 0.0001);
chk('12.5% contra 30% → debajo', $c['nivel'], 'debajo');
$ek2 = empresa('business', 'MXN', 30.00); metas($ek2, 2026, 9, 1, 2, 3);
for ($i = 0; $i < 6; $i++) cot($ek2, '2026-09-10 10:00:00');
chk('7 enviadas → gris', EST($ek2)['conv']['mes']['nivel'], 'gris');
$ek3 = empresa('business', 'MXN', 30.00); metas($ek3, 2026, 9, 1, 2, 3);
for ($i = 0; $i < 8; $i++) venta($ek3, '2026-09-10 10:00:00', 1000);
for ($i = 0; $i < 4; $i++) venta($ek3, '2026-08-10 10:00:00', 1000);   // ventas de agosto... cotizaciones también de agosto
$c = EST($ek3)['conv']['mes'];
chk('8/8 → topada en 90%', $c['tasa'], 0.9);
chk('arriba', $c['nivel'], 'arriba');
$ek4 = empresa('business', 'MXN', 30.00); metas($ek4, 2026, 9, 1, 2, 3);
for ($i = 0; $i < 7; $i++) cot($ek4, '2026-09-10 10:00:00');
for ($i = 0; $i < 3; $i++) venta($ek4, '2026-09-10 10:00:00', 1000);   // 3/10 = 30%
chk('30% contra 30% → en', EST($ek4)['conv']['mes']['nivel'], 'en');
chk('sin tasa deseada → sin_meta', EST($ek)['conv']['mes']['deseada'] !== null && EST($e)['conv']['mes']['nivel'] === 'sin_meta');

echo "\n── Ticket y cotizaciones que faltan ──\n";
reloj('2026-09-25 10:00:00');
$et = empresa('business', 'MXN', 25.00); historia($et, '2026-06-01 10:00:00');
metas($et, 2026, 8, 20000, 40000, 60000); metas($et, 2026, 9, 20000, 40000, 60000);
for ($i = 0; $i < 4; $i++) venta($et, '2026-09-05 10:00:00', 5000);    // 20k en el mes
for ($i = 0; $i < 8; $i++) cot($et, '2026-09-06 10:00:00');            // 12 enviadas, 4 ventas → 33%
$s = EST($et);
chk('ticket de ventas (5 en 180 días)', [$s['ticket'], $s['ticket_origen']], [4200.0, 'ventas']);
chk('faltante hacia la pesimista', [$s['ventanas']['mes']['faltante'], $s['ventanas']['mes']['faltante_hacia']], [20000.0, 'pesimista']);
// 20000 / 4200 = 4.76 ventas → /0.3333 = 14.28 → 15 ; /0.25 = 19.05 → 20
chk('N con la tasa real', $s['faltan_cot']['real'], 15);
chk('M con la deseada', $s['faltan_cot']['deseada'], 20);
$eth = empresa(); historia($eth); metas($eth, 2026, 9, 1, 2, 3);
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2026,3,10,50000),(?,2026,2,0,0),(?,2025,1,1,999999)", [$eth, $eth, $eth]);
$s = EST($eth);
// Solo el último año (CEO, 27 sep): la fila de ene-2025 NO entra. (50,000 / 10)
chk('respaldo historial_mensual (último año)', [$s['ticket'], $s['ticket_origen']], [5000.0, 'historial']);
$ey = empresa(); historia($ey); metas($ey, 2026, 9, 1, 2, 3);
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2025,10,4,40000),(?,2025,9,1,999999)", [$ey, $ey]);
chk('historial: oct-2025 entra (12 meses contando sep-2026)', EST($ey)['ticket'], 10000.0);
$ey2 = empresa(); historia($ey2); metas($ey2, 2026, 9, 1, 2, 3);
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2024,3,10,50000)", [$ey2]);
chk('historial solo de hace años → sin ticket', [EST($ey2)['ticket'], EST($ey2)['faltan_cot']], [null, ['real' => null, 'deseada' => null]]);
$etn = empresa(); metas($etn, 2026, 9, 1, 2, 3);
chk('empresa nueva sin nada → sin ticket', EST($etn)['ticket'], null);

echo "\n── Auditoría: alerta, conversión apagada, huecos, edición, frontera ──\n";
reloj('2026-09-25 10:00:00');
// A — la alerta NO la consume quien lee primero.
$ea = empresa(); historia($ea); metas($ea, 2026, 8, 50000, 100000, 150000); metas($ea, 2026, 9, 50000, 100000, 150000);
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
$eb = empresa('business', 'MXN', 30.00);
for ($i = 0; $i < 10; $i++) cot($eb, '2026-09-10 10:00:00');
$nb = MetasEmpresa::nivel($eb);
chk('B: tasa declarada sin metas → sin texto', array_filter(MetasEmpresa::frases($nb)), []);
$eb2 = empresa('business', 'MXN', 30.00); metas($eb2, 2026, 9, 1, 2, 3);
for ($i = 0; $i < 10; $i++) cot($eb2, '2026-09-10 10:00:00');
venta($eb2, '2026-09-01 10:00:00', 100);                  // historia de 24 días
$fb = MetasEmpresa::frases(MetasEmpresa::nivel($eb2));
chk('B: sin historia → solo la frase de historia, sin conversión', array_values(array_filter($fb)), ['Todavía no hay suficiente historia para leer cómo va la empresa.']);
chk('B: el admin sí conserva el dato de conversión', EST($eb2)['conv']['mes']['enviadas'], 11);

// C — la ventana de 30 días no compara contra un nivel viejo después de un hueco.
$eg = empresa(); historia($eg); metas($eg, 2026, 8, 1, 2, 3); metas($eg, 2026, 9, 1, 2, 3);
venta($eg, '2026-09-10 10:00:00', 50);
chk('C: arranca en sobrepasada', EST($eg)['ventanas']['d30']['nivel'], 'sobrepasada');
DB::execute("DELETE FROM empresa_metas_mes WHERE empresa_id = ?", [$eg]);
EST($eg);                                                     // hueco: sin metas
chk('C: el hueco borra la memoria', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_estado WHERE empresa_id=?", [$eg]), 0);
metas($eg, 2026, 8, 1000, 2000, 3000); metas($eg, 2026, 9, 1000, 2000, 3000);
$s = EST($eg)['ventanas']['d30'];
chk('C: al volver es primera lectura (sin alerta vieja)', [$s['nivel'], $s['alerta'], $s['nivel_anterior']], ['sin_equilibrio', false, null]);

// Mes heredado: misma fila (misma firma) pero OTRO periodo → primera lectura.
$eo = empresa(); historia($eo); metas($eo, 2026, 8, 1, 2, 3); metas($eo, 2026, 9, 100, 200, 300);
venta($eo, '2026-09-10 10:00:00', 500);
chk('heredado: septiembre sobrepasada', EST($eo)['ventanas']['mes']['nivel'], 'sobrepasada');
reloj('2026-10-02 10:00:00');                          // octubre hereda la fila de septiembre
$s = EST($eo)['ventanas']['mes'];
chk('heredado: octubre es primera lectura, sin arrastre ni alerta', [$s['nivel'], $s['alerta'], $s['nivel_anterior'], $s['provisional']], ['sin_equilibrio', false, null, true]);
reloj('2026-09-25 10:00:00');

// C bis — hueco solo en la ventana de 30 días (el mes sigue leyéndose).
$eg2 = empresa(); historia($eg2); metas($eg2, 2026, 8, 1, 2, 3); metas($eg2, 2026, 9, 1, 2, 3);
venta($eg2, '2026-09-10 10:00:00', 50);
EST($eg2);
DB::execute("DELETE FROM empresa_metas_mes WHERE empresa_id = ? AND mes = 8", [$eg2]);
$s = EST($eg2);
chk('C bis: d30 sin cobertura, mes sí', [$s['ventanas']['d30']['estado'], $s['ventanas']['mes']['estado']], ['sin_metas', 'ok']);
chk('C bis: se olvida SOLO la memoria de d30', DB::query("SELECT ventana FROM empresa_metas_estado WHERE empresa_id=? ORDER BY ventana", [$eg2]), [['ventana' => 'mes']]);

// D — editar las metas no dispara alerta.
$ed2 = empresa(); historia($ed2); metas($ed2, 2026, 8, 50000, 100000, 150000); metas($ed2, 2026, 9, 50000, 100000, 150000);
venta($ed2, '2026-09-10 10:00:00', 60000);
chk('D: antes de editar: baja', EST($ed2)['ventanas']['mes']['nivel'], 'baja');
metas($ed2, 2026, 9, 20000, 40000, 60000);
$s = EST($ed2)['ventanas']['mes'];
chk('D: tras editar: sobrepasada sin alerta', [$s['nivel'], $s['alerta'], $s['nivel_anterior']], ['sobrepasada', false, null]);

// E — la frontera ±10% no depende del binario.
$rc = new ReflectionMethod('MetasEmpresa', '_conv');
chk('E: 27/100 vs 30% → en',  $rc->invoke(null, 100, 27, 0.30)['nivel'], 'en');
chk('E: 18/100 vs 20% → en',  $rc->invoke(null, 100, 18, 0.20)['nivel'], 'en');
chk('E: 36/100 vs 40% → en',  $rc->invoke(null, 100, 36, 0.40)['nivel'], 'en');
chk('E: 44/100 vs 40% → en (frontera de arriba)', $rc->invoke(null, 100, 44, 0.40)['nivel'], 'en');
chk('E: 26/100 vs 30% → debajo', $rc->invoke(null, 100, 26, 0.30)['nivel'], 'debajo');
chk('E: 34/100 vs 30% → arriba', $rc->invoke(null, 100, 34, 0.30)['nivel'], 'arriba');

// Datos inconsistentes (la captura de la fase 2 los rechazará, pero hoy nada lo impide).
chk('E>P: alcanzar el equilibrio ya es llegó', $rf->invoke(null, 120000.0, 120000.0, 100000.0, 150000.0), 'llego');
chk('E>P: debajo del equilibrio sigue sin_equilibrio', $rf->invoke(null, 110000.0, 120000.0, 100000.0, 150000.0), 'sin_equilibrio');
chk('O<P: la pesimista ya es sobrepasada', $rf->invoke(null, 100000.0, 50000.0, 100000.0, 80000.0), 'sobrepasada');

// Cada nivel de conversión lleva SU frase (mutación M26: frases cruzadas).
$fc = fn($k) => MetasEmpresa::frases(['conv_mes' => $k])['conv_mes'];
chk('conv debajo → "por debajo"', $fc('debajo'), 'La empresa cierra por debajo de lo que busca en este mes.');
chk('conv en → "en lo que busca"', $fc('en'), 'La empresa cierra en lo que busca en este mes.');
chk('conv arriba → "por encima"', $fc('arriba'), 'La empresa cierra por encima de lo que busca en este mes.');
$fl = fn($k) => MetasEmpresa::frases(['mes' => $k])['mes'];
$esperadas = [
    'sin_equilibrio' => 'La empresa ni siquiera llega al punto de equilibrio en este mes.',
    'muy_baja' => 'La empresa va muy baja en este mes.',
    'baja' => 'La empresa va baja en este mes.',
    'debajo' => 'La empresa va por debajo de su meta en este mes.',
    'cerca' => 'La empresa va cerca de su meta en este mes.',
    'casi' => 'La empresa casi llega a su meta en este mes.',
    'llego' => 'La empresa ya llegó a su meta en este mes.',
    'casi_optima' => 'La empresa casi llega a su meta optimista en este mes.',
    'sobrepasada' => 'La empresa ya sobrepasó su meta optimista en este mes.',
];
foreach ($esperadas as $k => $txt) chk("frase $k", $fl($k), $txt);
chk('frases() no truena si le pasan estado() por error', is_array(MetasEmpresa::frases(EST($ep))));

// Ticket: la receta muerde (DI, sin pago) y el corte de 5 es exacto.
$et2 = empresa(); historia($et2, '2026-09-01 10:00:00'); metas($et2, 2026, 9, 1, 2, 3);
for ($i = 0; $i < 3; $i++) venta($et2, '2026-09-05 10:00:00', 1000);
venta($et2, '2026-09-05 10:00:00', 90000, 0);                         // sin pago
venta($et2, '2026-09-05 10:00:00', 90000, 5, 'pendiente', 'utilizado'); // DI
DB::execute("INSERT INTO historial_mensual (empresa_id, anio, mes, ventas_cantidad, ventas_monto) VALUES (?,2026,3,2,7000)", [$et2]);
chk('ticket: 4 ventas válidas (DI y sin pago fuera) → respaldo historial', [EST($et2)['ticket'], EST($et2)['ticket_origen']], [3500.0, 'historial']);

// Cotizaciones que faltan: con < CONV_MIN enviadas no se usa la tasa real.
$ef2 = empresa('business', 'MXN', 25.00); historia($ef2, '2026-06-01 10:00:00');
metas($ef2, 2026, 8, 20000, 40000, 60000); metas($ef2, 2026, 9, 20000, 40000, 60000);
for ($i = 0; $i < 4; $i++) venta($ef2, '2026-09-05 10:00:00', 5000);   // 4 enviadas
$s = EST($ef2);
chk('faltan: tasa real ignorada con 4 enviadas', $s['faltan_cot']['real'], null);
chk('faltan: la deseada sí', $s['faltan_cot']['deseada'] !== null);

// Licencia Business vencida (no trial): sin metas.
$evx = empresa('business_vencido'); historia($evx); metas($evx, 2026, 9, 1, 2, 3);
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
chk('parse "1e3" (notación) → número', MetasEmpresa::parse_monto('1e3'), 1000.0);
chk('tasa vacía = válida (no declarada)', MetasEmpresa::validar_tasa(null), null);
chk('tasa 3 y 90 válidas', [MetasEmpresa::validar_tasa(3.0), MetasEmpresa::validar_tasa(90.0)], [null, null]);
chk('tasa 2.9 y 90.1 rechazadas', is_string(MetasEmpresa::validar_tasa(2.9)) && is_string(MetasEmpresa::validar_tasa(90.1)));
$mc = MetasEmpresa::meses_captura(strtotime('2026-09-25 10:00:00'));
chk('rejilla de 12 meses', count($mc), 12);
chk('empieza 5 atrás (abr-2026)', [$mc[0]['anio'], $mc[0]['mes']], [2026, 4]);
chk('termina 6 adelante (mar-2027, cruza año)', [$mc[11]['anio'], $mc[11]['mes']], [2027, 3]);
chk('marca el actual', array_values(array_filter(array_map(fn($m) => $m['actual'] ? $m['mes'] : null, $mc))), [9]);
$mc31 = MetasEmpresa::meses_captura(strtotime('2026-08-31 10:00:00'));   // día 31: '+1 month' desde el 31 brincaría
chk('día 31 no brinca meses (sep..feb consecutivos)', array_map(fn($m) => $m['mes'], $mc31), [3,4,5,6,7,8,9,10,11,12,1,2]);
chk('mes fuera de rejilla no editable', MetasEmpresa::mes_editable(2026, 3, strtotime('2026-09-25')), false);
chk('mes en rejilla editable', MetasEmpresa::mes_editable(2027, 3, strtotime('2026-09-25')), true);

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
$hoy = date('Y-m'); [$ya, $ym] = [(int)date('Y'), (int)date('n')];   // el endpoint usa el reloj real
$ec2 = empresa();
$r = post($ec2, ['accion' => 'mes', 'anio' => $ya, 'mes' => $ym, 'equilibrio' => '50,000', 'pesimista' => '$100,000', 'optimista' => 150000]);
chk('guardar mes: ok', $r['ok'] ?? $r, true);
$f = DB::row("SELECT equilibrio, meta_pesimista, meta_optimista, moneda, capturado_por FROM empresa_metas_mes WHERE empresa_id=? AND anio=? AND mes=?", [$ec2, $ya, $ym]);
chk('guardado con moneda y capturado_por', $f, ['equilibrio' => '50000.00', 'meta_pesimista' => '100000.00', 'meta_optimista' => '150000.00', 'moneda' => 'MXN', 'capturado_por' => 77]);
$r = post($ec2, ['accion' => 'mes', 'anio' => $ya, 'mes' => $ym, 'equilibrio' => 60000, 'pesimista' => 100000, 'optimista' => 150000]);
chk('re-guardar actualiza (no duplica)', [(int)DB::val("SELECT COUNT(*) FROM empresa_metas_mes WHERE empresa_id=?", [$ec2]), DB::val("SELECT equilibrio FROM empresa_metas_mes WHERE empresa_id=?", [$ec2])], [1, '60000.00']);
$r = post($ec2, ['accion' => 'mes', 'anio' => $ya, 'mes' => $ym, 'equilibrio' => 200000, 'pesimista' => 100000, 'optimista' => 150000]);
chk('E > P rechazado con su mensaje', [$r['ok'], $r['error']], [false, 'La meta pesimista no puede quedar debajo del punto de equilibrio.']);
chk('el rechazo no tocó la fila', DB::val("SELECT equilibrio FROM empresa_metas_mes WHERE empresa_id=?", [$ec2]), '60000.00');
$r = post($ec2, ['accion' => 'mes', 'anio' => $ya, 'mes' => $ym, 'equilibrio' => 1, 'pesimista' => '', 'optimista' => 3]);
chk('monto vacío rechazado', $r['ok'], false);
$r = post($ec2, ['accion' => 'mes', 'anio' => $ya - 2, 'mes' => $ym, 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
chk('mes fuera de rejilla rechazado', $r['ok'], false);
$r = post($ec2, ['accion' => 'borrar', 'anio' => $ya, 'mes' => $ym]);
chk('borrar: la fila sale', [$r['ok'], (int)DB::val("SELECT COUNT(*) FROM empresa_metas_mes WHERE empresa_id=?", [$ec2])], [true, 0]);
$ecx = empresa();
metas($ecx, $ya, $ym, 1, 2, 3);
post($ec2, ['accion' => 'borrar', 'anio' => $ya, 'mes' => $ym]);
chk('borrar solo toca SU empresa', (int)DB::val("SELECT COUNT(*) FROM empresa_metas_mes WHERE empresa_id=?", [$ecx]), 1);
$r = post($ec2, ['accion' => 'otra']);
chk('acción inválida', $r['ok'], false);
$epro = empresa('pro');
$r = post($epro, ['accion' => 'mes', 'anio' => $ya, 'mes' => $ym, 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
chk('Pro rechazado (403) y sin escribir', [$r['ok'], $r['code'], (int)DB::val("SELECT COUNT(*) FROM empresa_metas_mes WHERE empresa_id=?", [$epro])], [false, 403, 0]);
$eusd = empresa('business', 'USD');
post($eusd, ['accion' => 'mes', 'anio' => $ya, 'mes' => $ym, 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
chk('moneda copiada de la empresa', DB::val("SELECT moneda FROM empresa_metas_mes WHERE empresa_id=?", [$eusd]), 'USD');

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
$r = post($ec2, ['accion' => 'tasa', 'tasa' => '95']);
chk('tasa 95 rechazada, la anterior queda', [$r['ok'], DB::val("SELECT tasa_conv_meta FROM empresas WHERE id=?", [$ec2])], [false, '30.00']);
$r = post($ec2, ['accion' => 'tasa', 'tasa' => 'mucho']);
chk('tasa no numérica rechazada', $r['ok'], false);
post($ec2, ['accion' => 'tasa', 'tasa' => '']);
chk('tasa vacía = no declarada (NULL, sin fecha)', DB::row("SELECT tasa_conv_meta, tasa_conv_meta_desde FROM empresas WHERE id=?", [$ec2]), ['tasa_conv_meta' => null, 'tasa_conv_meta_desde' => null]);
DB::pdo()->exec("RENAME TABLE empresa_metas_mes TO empresa_metas_mes_x");
$r = post($ec2, ['accion' => 'mes', 'anio' => $ya, 'mes' => $ym, 'equilibrio' => 1, 'pesimista' => 2, 'optimista' => 3]);
chk('sin migrar: error limpio, no 500 crudo', [$r['ok'] ?? null, $r['code'] ?? null], [false, 500]);
DB::pdo()->exec("RENAME TABLE empresa_metas_mes_x TO empresa_metas_mes");
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
metas($er, 2026, 7, 10000, 20000, 30000);
metas($er, 2026, 9, 50000, 100000, 150000);
$html = render_metas($er);
chk('12 renglones', substr_count($html, 'class="mt-row'), 12);
chk('marca este mes', substr_count($html, '<b>Este mes</b>'), 1);
chk('septiembre capturado con sus valores (con comas)', str_contains($html, 'value="100,000"'));
chk('mes con mayúscula inicial', str_contains($html, '>Septiembre 2026</div>'));
chk('agosto usa las de julio (placeholder, no value)', str_contains($html, 'Usa las de julio 2026') && str_contains($html, 'placeholder="20,000"'));
chk('abril y mayo sin metas', substr_count($html, 'Sin metas'), 3);   // abr, may, jun
chk('octubre en adelante heredan de septiembre', substr_count($html, 'Usa las de septiembre 2026'), 6);
chk('tasa precargada', str_contains($html, 'value="27.5"'));
chk('texto literal de la tasa', str_contains($html, 'De cada 100 cotizaciones que envías, cuántas quieres vender.'));
chk('"Quitar" solo en meses capturados', substr_count($html, 'mtBorrarMes(this)" title'), 2);
chk('sin aviso de moneda', str_contains($html, 'recaptúralas'), false);
DB::execute("UPDATE empresas SET moneda='USD' WHERE id=?", [$er]);
chk('aviso de moneda distinta', str_contains(render_metas($er), 'Tus metas están capturadas en MXN y la empresa ahora opera en USD: recaptúralas.'));
DB::pdo()->exec("RENAME TABLE empresa_metas_mes TO empresa_metas_mes_x");
$h2 = render_metas($er);
chk('sin migrar: aviso, sin rejilla, sin error', str_contains($h2, 'todavía no están disponibles') && !str_contains($h2, 'mt-row"'));
DB::pdo()->exec("RENAME TABLE empresa_metas_mes_x TO empresa_metas_mes");

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

echo "\n── Tabla ausente → sin_metas, sin excepción ──\n";
DB::pdo()->exec("RENAME TABLE empresa_metas_mes TO empresa_metas_mes_x");
$r = null; $exc = false;
try { $r = EST($e); } catch (\Throwable $x) { $exc = true; }
chk('no lanza', $exc, false);
chk('sin_metas', $r['estado'] ?? null, 'sin_metas');
DB::pdo()->exec("RENAME TABLE empresa_metas_mes_x TO empresa_metas_mes");
DB::pdo()->exec("RENAME TABLE empresa_metas_estado TO empresa_metas_estado_x");
$r = EST($ep);
chk('sin tabla de histéresis: nivel crudo igual', $r['ventanas']['mes']['nivel'], $r['ventanas']['mes']['nivel_crudo']);
DB::pdo()->exec("RENAME TABLE empresa_metas_estado_x TO empresa_metas_estado");

echo "\n── Frases y fugas (lo que puede leer un asesor) ──\n";
reloj('2026-09-25 10:00:00');
$n = MetasEmpresa::nivel($ep);
$claves_ok = ['mes', 'd30', 'conv_mes', 'conv_d30', 'dias', 'mes_nombre', 'corte'];
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
                ['mes' => $nv, 'd30' => $nv, 'conv_mes' => $cv, 'conv_d30' => $cv,
                 'dias' => 25, 'mes_nombre' => 'septiembre', 'corte' => '2026-09-25'], $fe))));
        }
    }
}
$todas = array_unique($todas);
chk('hay frase para los 9 niveles × 2 ventanas + conv', count($todas) >= 9 * 2 + 3);
$mal = array_filter($todas, fn($x) => preg_match('/[$%]|\d{2,}[.,]\d|\bvas\b|te faltan|tu meta/iu', $x));
chk('ninguna con $, %, montos, "vas", "te faltan", "tu meta"', array_values($mal), []);
$dig = array_filter($todas, fn($x) => preg_match('/\d/', preg_replace('/(30 días|\d{1,2}\/[A-Z][a-z]{2})/u', '', $x)));
chk('los únicos dígitos son calendario (30 días, 25/Sep)', array_values($dig), []);
$sin3 = array_filter($todas, fn($x) => !str_starts_with($x, 'La empresa') && !str_starts_with($x, 'Todavía'));
chk('todas en tercera persona, la empresa de sujeto', array_values($sin3), []);
chk('sin_metas no produce texto', array_filter(MetasEmpresa::frases(['mes' => 'sin_metas', 'd30' => 'sin_metas', 'conv_mes' => 'sin_meta', 'conv_d30' => 'sin_meta'])), []);
chk('d30 fechado', MetasEmpresa::frases(['d30' => 'baja', 'corte' => '2026-09-25'], true)['d30'], 'La empresa va baja en los 30 días al 25/Sep.');

echo "\n── Memo ──\n";
$a = MetasEmpresa::estado($ep);
DB::execute("UPDATE empresa_metas_mes SET meta_pesimista = 1 WHERE empresa_id = ?", [$ep]);
chk('memo: mismo request, mismo resultado', MetasEmpresa::estado($ep) === $a);
MetasEmpresa::reset();
chk('reset: relee', MetasEmpresa::estado($ep)['ventanas']['mes']['pesimista'], 1.0);

echo "\n── Contrato de fuente ──\n";
$src = file_get_contents(__DIR__ . '/../core/MetasEmpresa.php');
chk('sin marcadores con nombre en SQL (EMULATE_PREPARES=false)', preg_match('/[\s(,=]:[a-z_]+\b/', $src), 0);
chk('sin NOW() en SQL (el reloj es uno solo)', stripos(preg_replace('#//[^\n]*#', '', $src), 'NOW()'), false);
chk('ActividadScore no menciona MetasEmpresa (decisión 6)', strpos(file_get_contents(__DIR__ . '/../core/ActividadScore.php'), 'MetasEmpresa'), false);
chk('la exclusión del DI va comentada', str_contains($src, 'POR DECISIÓN') && str_contains($src, 'DEL CEO'));

echo "\n" . ($fail === 0 ? "✓ SIMULACIÓN METAS OK — $ok comprobaciones contra MariaDB real\n" : "✗ $fail FALLAS de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
