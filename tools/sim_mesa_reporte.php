<?php
// ============================================================
// SIMULACIÓN REAL — Mesa::reporte() y Mesa::recuperado() contra
// MariaDB de verdad (las queries SQL se EJECUTAN, no se stubean).
// Escenarios con resultados esperados calculados a mano.
// p75 = 20 → ventana 20d, k (abandono) = 10d, limpieza 40d.
//
// REQUISITOS (entorno de desarrollo, NUNCA producción):
//   - MariaDB/MySQL local con BD 'simtest' y usuario sim/sim
//   - DESTRUYE y recrea las tablas de simtest en cada corrida
// Correr: php tools/sim_mesa_reporte.php   → debe terminar en OK
// Obligatorio tras CUALQUIER cambio a Mesa::reporte()/recuperado().
// ============================================================
define('COTIZAAPP', 1);
define('MODULES_PATH', '/dev/null');

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
        $st = self::pdo()->prepare($sql); $st->execute($params);
        $r = $st->fetch(PDO::FETCH_ASSOC); return $r === false ? null : $r;
    }
    public static function val($sql, $params = []) {
        $st = self::pdo()->prepare($sql); $st->execute($params);
        return $st->fetchColumn();
    }
    public static function execute($sql, $params = []): void {
        $st = self::pdo()->prepare($sql); $st->execute($params);
    }
}

class Radar {
    public static function ciclo_venta($e) { return ['auto' => true, 'p75' => 20, 'mediana' => 10]; }
}

require __DIR__ . '/../core/Mesa.php';

// ── Esquema mínimo con las columnas que las queries usan ──
$ddl = <<<SQL
DROP TABLE IF EXISTS cotizaciones, ventas, mesa_estados, radar_feedback,
                     bucket_transitions, quote_sessions, usuarios, cotizacion_log;
CREATE TABLE cotizaciones (
  id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL, vendedor_id INT UNSIGNED NULL,
  numero VARCHAR(30), titulo VARCHAR(100), total DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado VARCHAR(20) NOT NULL DEFAULT 'enviada', visitas INT NOT NULL DEFAULT 0,
  suspendida TINYINT NOT NULL DEFAULT 0, accion_at DATETIME NULL,
  radar_bucket VARCHAR(40) NULL, radar_bucket_at DATETIME NULL,
  ultima_vista_at DATETIME NULL, radar_senales TEXT NULL,
  created_at DATETIME NOT NULL
);
CREATE TABLE ventas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED,
  empresa_id INT UNSIGNED NOT NULL, total DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado VARCHAR(20) NOT NULL DEFAULT 'activa', created_at DATETIME NOT NULL
);
CREATE TABLE mesa_estados (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL, empresa_id INT UNSIGNED NOT NULL,
  area VARCHAR(12) NOT NULL, estado VARCHAR(30) NOT NULL, razon VARCHAR(30) NULL,
  bucket_snapshot VARCHAR(40) NULL, created_at DATETIME NOT NULL,
  KEY idx_cot_time (cotizacion_id, created_at)
);
CREATE TABLE radar_feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL, empresa_id INT UNSIGNED NOT NULL,
  tipo VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
  UNIQUE KEY uk_cot_user (cotizacion_id, usuario_id)
);
CREATE TABLE bucket_transitions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  bucket_anterior VARCHAR(40) NULL, bucket_nuevo VARCHAR(40) NOT NULL, created_at DATETIME NOT NULL,
  KEY idx_cotizacion (cotizacion_id)
);
CREATE TABLE quote_sessions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  es_interno TINYINT NOT NULL DEFAULT 0, visible_ms INT NULL, scroll_max INT NULL,
  ip VARCHAR(45) NULL, created_at DATETIME NOT NULL
);
CREATE TABLE usuarios (
  id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL, activo TINYINT NOT NULL DEFAULT 1
);
CREATE TABLE cotizacion_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NULL, accion VARCHAR(30) NULL, evento VARCHAR(30) NULL,
  created_at DATETIME NOT NULL
);
SQL;
foreach (array_filter(array_map('trim', explode(';', $ddl))) as $stmt) DB::pdo()->exec($stmt);

// Helpers de seed — todo relativo a NOW()
$d = fn(float $dias) => date('Y-m-d H:i:s', time() - (int)round($dias * 86400));
function cot(int $id, int $emp, int $uid, float $total, float $edad_d, string $estado = 'vista', array $x = []): void {
    global $d;
    DB::execute("INSERT INTO cotizaciones (id, empresa_id, usuario_id, vendedor_id, numero, titulo, total, estado, suspendida, accion_at, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        [$id, $emp, $uid, $x['vendedor_id'] ?? null, "COT-$id", "Cot $id", $total, $estado,
         $x['suspendida'] ?? 0, $x['accion_at'] ?? null, $d($edad_d)]);
}
function tap(int $cot, int $emp, string $area, string $estado, float $hace_d, ?string $razon = null): void {
    global $d;
    DB::execute("INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, razon, created_at)
                 VALUES (?,?,?,?,?,?,?)", [$cot, 900, $emp, $area, $estado, $razon, $d($hace_d)]);
}
function fb(int $cot, int $uid, int $emp, string $tipo, float $upd_d): void {
    global $d;
    DB::execute("INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo, created_at, updated_at)
                 VALUES (?,?,?,?,?,?)", [$cot, $uid, $emp, $tipo, $d($upd_d), $d($upd_d)]);
}
function hotbt(int $cot, float $hace_d): void {
    global $d;
    DB::execute("INSERT INTO bucket_transitions (cotizacion_id, bucket_nuevo, created_at) VALUES (?,?,?)",
        [$cot, 'probable_cierre', $d($hace_d)]);
}
function venta(int $cot, int $emp, float $total, float $hace_d): void {
    global $d;
    DB::execute("INSERT INTO ventas (cotizacion_id, empresa_id, total, estado, created_at) VALUES (?,?,?,'activa',?)",
        [$cot, $emp, $total, $d($hace_d)]);
}
function visita(int $cot, float $hace_d, int $vis = 5000, int $scr = 80): void {
    global $d;
    DB::execute("INSERT INTO quote_sessions (cotizacion_id, es_interno, visible_ms, scroll_max, created_at) VALUES (?,0,?,?,?)",
        [$cot, $vis, $scr, $d($hace_d)]);
}

DB::execute("INSERT INTO usuarios VALUES (101,1,'Ana',1),(102,1,'Beto',1),(103,1,'Dora',1),(201,2,'Carla',1),(999,1,'Superadmin',1)");

// ══ EMPRESA 1 — ANA (uid 101) ══════════════════════════════
// A1 (1001): activa 5d, virgen. rf de TERCERO (999) NO debe calificarla.
cot(1001, 1, 101, 10000, 5);
fb(1001, 999, 1, 'sin_interes', 3);            // tercero — debe ignorarse
hotbt(1001, 1);                                 // señal de ayer — ventana no cerrada, NO se juzga
// A2 (1002): activa 25d (fuera), NUNCA tocada → se le fue.
cot(1002, 1, 101, 20000, 25);
hotbt(1002, 6);                                 // señal desatendida
hotbt(1002, 5);                                 // rebote 1d después — episodio suprimido
// A3 (1003): activa 25d (fuera) pero TOCADA hace 2-4d → NO se le fue.
cot(1003, 1, 101, 12000, 25);
tap(1003, 1, 'contacto', 'no_contesta', 4);
tap(1003, 1, 'contacto', 'hablamos', 2);
tap(1003, 1, 'compromiso', 'sin_compromiso', 2);
hotbt(1003, 5);                                 // señal atendida (no_contesta a 1d)
// A4 (1004): DESCARTADA hace 15d (postura + rf del dueño). Revivió hace 5d.
cot(1004, 1, 101, 18000, 25);
tap(1004, 1, 'contacto', 'hablamos', 16);
tap(1004, 1, 'compromiso', 'compromiso', 16);   // acuerdo previo al descarte — NO debe contar
tap(1004, 1, 'postura', 'descartada', 15, 'precio');
fb(1004, 101, 1, 'sin_interes', 15);
hotbt(1004, 5);                                 // revivió → revividos+1; también señal desatendida
// A5 (1005): activa 3d, 👍 del dueño → calificada, trabajada.
cot(1005, 1, 101, 8000, 3);
fb(1005, 101, 1, 'con_interes', 1);
// V1 (1006): VENDIDA. Acuerdo 9d → venta 4d. Señal 10d atendida (tap 9d + venta).
cot(1006, 1, 101, 50000, 20);
tap(1006, 1, 'contacto', 'hablamos', 9);
tap(1006, 1, 'compromiso', 'compromiso', 9);
hotbt(1006, 10);
venta(1006, 1, 50000, 4);
// V2 (1007): RECUPERADA — descartada (mesa fb + rf) 10d, vendida 2d.
cot(1007, 1, 101, 30000, 18);
tap(1007, 1, 'feedback', 'sin_interes', 10);
fb(1007, 101, 1, 'sin_interes', 10);
venta(1007, 1, 30000, 2);
// V3 (1008): LATCH — 👎 12d corregido a 👍 11d, vendida 6d → NO recuperada.
cot(1008, 1, 101, 15000, 14);
tap(1008, 1, 'feedback', 'sin_interes', 12);
tap(1008, 1, 'feedback', 'con_interes', 11);
fb(1008, 101, 1, 'con_interes', 11);
venta(1008, 1, 15000, 6);
// C1 (1010): acuerdo 7d, cliente ABRIÓ 6d (cumplido vía visita).
cot(1010, 1, 101, 22000, 12);
tap(1010, 1, 'contacto', 'hablamos', 7);
tap(1010, 1, 'compromiso', 'compromiso', 7);
visita(1010, 6);
// C2 (1011): acuerdo 3d → EN CURSO.
cot(1011, 1, 101, 9000, 4);
tap(1011, 1, 'contacto', 'hablamos', 3);
tap(1011, 1, 'compromiso', 'compromiso', 3);

// D6 (3007): 👎 VIEJO (episodio -45, fuera de período) re-tapeado hace 2d
//   (updated_at bumpeado) → NO debe entrar a 'descartes del período'
cot(3007, 1, 103, 19000, 60);
tap(3007, 1, 'feedback', 'sin_interes', 45);
fb(3007, 103, 1, 'sin_interes', 2);

// ══ EMPRESA 1 — BETO (uid 102): el que no hace nada ═══════
cot(1009, 1, 102, 40000, 30);

// ══ EMPRESA 1 — DORA (uid 103): escenarios adversariales de la auditoría ══
// D1 (3001): descarte LEGACY vía postura (sin historia feedback) hace 15d,
//   revivió hace 8d, re-👎 hace 7d → el revivido NO debe borrarse (ancla fallback postura)
cot(3001, 1, 103, 11000, 25);
tap(3001, 1, 'postura', 'descartada', 15, 'precio');
fb(3001, 103, 1, 'sin_interes', 7);
hotbt(3001, 8);
// D2 (3002): ciclo VIEJO — 👎(-60) revivió(-50) corregido 👍(-49) re-👎(-5) sin revivir después
//   → el bt de -50 NO debe contar (ancla del episodio vigente = -5)
cot(3002, 1, 103, 17000, 70);
tap(3002, 1, 'feedback', 'sin_interes', 60);
tap(3002, 1, 'feedback', 'con_interes', 49);
tap(3002, 1, 'feedback', 'sin_interes', 5);
fb(3002, 103, 1, 'sin_interes', 5);
hotbt(3002, 50);
// D3 (3003): acuerdo VIGENTE viejo (-40) + plática nueva (-2) → cuenta A FAVOR (no deflacta)
cot(3003, 1, 103, 13000, 45);
tap(3003, 1, 'compromiso', 'compromiso', 40);
tap(3003, 1, 'contacto', 'hablamos', 2);
// D4 (3004): GAMING — acuerdo -20 sin movimiento del cliente, re-tap misma pill ayer
//   → maduro NO cumplido (el reloj de racha no se reinicia; antes salía "en curso")
cot(3004, 1, 103, 14000, 25);
tap(3004, 1, 'contacto', 'hablamos', 20);
tap(3004, 1, 'compromiso', 'compromiso', 20);
tap(3004, 1, 'compromiso', 'compromiso', 1);
// D5 (3005): postura Descartar (-15) + 👍 posterior (-14) → UNA sola definición:
//   descartada para cartera (no 'se le fue') y para trabajo
cot(3005, 1, 103, 16000, 30);
tap(3005, 1, 'postura', 'descartada', 15, 'otro');
tap(3005, 1, 'feedback', 'con_interes', 14);
fb(3005, 103, 1, 'con_interes', 14);
// V4 (3006): LATCH vía postura — 👎(-12) corregido tapeando postura Decidiendo(-11)
//   (el writer nuevo también escribe la fila feedback con_interes) → venta -6 NO recuperada
cot(3006, 1, 103, 12000, 20);
tap(3006, 1, 'feedback', 'sin_interes', 12);
tap(3006, 1, 'postura', 'decidiendo', 11);
tap(3006, 1, 'feedback', 'con_interes', 11);
fb(3006, 103, 1, 'con_interes', 11);
venta(3006, 1, 12000, 6);

// ══ EMPRESA 2 — CARLA (201): aislamiento ══════════════════
cot(2001, 2, 201, 99000, 10);
tap(2001, 2, 'contacto', 'hablamos', 2);

// ── EXPECTATIVAS calculadas a mano ─────────────────────────
$fail = 0;
function chk(string $name, $got, $want): void {
    global $fail;
    $ok = ($got == $want);
    if (!$ok) $fail++;
    echo ($ok ? "  ✓ " : "  ✗ ") . $name . ($ok ? '' : '  got=' . json_encode($got) . ' want=' . json_encode($want)) . "\n";
}

$rep = Mesa::reporte(1, 30);
$ana = $rep['asesores'][101] ?? [];
$bet = $rep['asesores'][102] ?? [];

echo "═ ANA — Cartera (foto de hoy) ═\n";
chk('activas = 7 (A1-A5, C1, C2; las vendidas fuera)', $ana['activas'] ?? -1, 7);
chk('sin_calificar = 5 (A1 con rf de tercero SÍ cuenta, A2, A3, C1, C2)', $ana['sin_calificar'] ?? -1, 5);
chk('sin_trabajar = 2 (A1, A2) $30,000', [$ana['sin_trabajar'] ?? -1, $ana['monto_sin_trabajar'] ?? -1], [2, 30000.0]);
chk('se_fueron = 1 (solo A2: A3 tocada hace 2d, A4 descartada) $20,000', [$ana['se_fueron'] ?? -1, $ana['monto_se_fueron'] ?? -1], [1, 20000.0]);

echo "═ ANA — Señales 🔥 ═\n";
chk('hot_total = 4 (A2, A3, A4, V1; la de A1 <2d fuera; rebote A2 suprimido)', $ana['hot_total'] ?? -1, 4);
chk('desatendidas = 2 (A2 nunca tocada, A4 revivió sin caso; A3 tap a 1d, V1 venta)', $ana['hot_desatendidas'] ?? -1, 2);

echo "═ ANA — Trabajo declarado ═\n";
chk('toques: 5 hablamos / 1 no_contesta', [$ana['hablamos'] ?? -1, $ana['no_contesta'] ?? -1], [5, 1]);
chk('hablamos_cots = 4 (A3, V1, C1, C2; A4 descartada FUERA)', $ana['hablamos_cots'] ?? -1, 4);
chk('con_compromiso = 3 (V1, C1, C2; el acuerdo de A4 NO cuenta)', $ana['con_compromiso'] ?? -1, 3);
chk('sin_compromiso = 1 (A3), no_quiso = 0', [$ana['sin_compromiso'] ?? -1, $ana['no_quiso'] ?? -1], [1, 0]);
$dondes = array_column($ana['compromiso_cots'] ?? [], 'donde', 'numero');
chk('desglose: V1 vendida, C1 activa, C2 activa', $dondes, ['COT-1006' => 'vendida', 'COT-1010' => 'activa', 'COT-1011' => 'activa']);
chk('cumplidos: 2 de 2 maduros (V1 venta a 5d, C1 visita a 1d) + 1 en curso (C2)',
    [$ana['comp_maduros'] ?? -1, $ana['comp_cumplidos'] ?? -1, $ana['comp_en_curso'] ?? -1], [2, 2, 1]);
chk('postura: descartada=1 (A4)', $ana['postura'] ?? [], ['descartada' => 1]);
chk('revividos: 1 de 1 (A4; el 👎 de tercero en A1 NO cuenta)', [$ana['revividos'] ?? -1, $ana['descartes'] ?? -1], [1, 1]);
chk('recuperado Ana: $30,000 (1) — V2 sí, V3 (corregida a 👍) no', [$ana['rec_n'] ?? -1, $ana['rec_monto'] ?? -1], [1, 30000.0]);

echo "═ BETO — el que no hace nada ═\n";
chk('aparece con activas=1, sin_calificar=1, sin_trabajar=1 ($40k)', [$bet['activas'] ?? -1, $bet['sin_calificar'] ?? -1, $bet['sin_trabajar'] ?? -1, $bet['monto_sin_trabajar'] ?? -1], [1, 1, 1, 40000.0]);
chk('se_fueron=1 ($40k), cero trabajo', [$bet['se_fueron'] ?? -1, $bet['hablamos'] ?? -1, $bet['hablamos_cots'] ?? -1], [1, 0, 0]);

echo "═ DORA — escenarios adversariales de la auditoría ═\n";
$dor = $rep['asesores'][103] ?? [];
chk('D: cartera activas=6 (D1-D5 + D6), sin_calificar=2 (D3,D4), sin_trabajar=0', [$dor['activas'] ?? -1, $dor['sin_calificar'] ?? -1, $dor['sin_trabajar'] ?? -1], [6, 2, 0]);
chk('D5 (Descartar + 👍 después) NO es "se le fue" — definición única de descartada', $dor['se_fueron'] ?? -1, 0);
chk('D: señal de D1 atendida (re-👎 a 1d), la de D2 fuera de período → 0 de 1', [$dor['hot_desatendidas'] ?? -1, $dor['hot_total'] ?? -1], [0, 1]);
chk('D3: acuerdo vigente viejo + plática nueva cuenta A FAVOR → 2 de 2', [$dor['con_compromiso'] ?? -1, $dor['hablamos_cots'] ?? -1], [2, 2]);
chk('D4: re-tap de la misma pill NO borra el reprobado → 1 maduro, 0 cumplidos, 0 en curso',
    [$dor['comp_maduros'] ?? -1, $dor['comp_cumplidos'] ?? -1, $dor['comp_en_curso'] ?? -1], [1, 0, 0]);
chk('D: revividos 1 de 2 (D1 ancla fallback; bt viejo de D2 no arrastra; D6 re-tapeado con episodio -45d NO entra al período)',
    [$dor['revividos'] ?? -1, $dor['descartes'] ?? -1], [1, 2]);
chk('D: postura = descartada 2 (D1, D5) + decidiendo 1 (V4)', $dor['postura'] ?? [], ['decidiendo' => 1, 'descartada' => 2]);
chk('V4 (👎 corregido vía postura) NO recuperada para Dora', $dor['rec_n'] ?? -1, 0);

echo "═ RECUPERADO empresa-wide ═\n";
$rec = Mesa::recuperado(1, 30);
chk('rec: $30,000 (1) — solo V2; V3 y V4 (corregidas) NO', [$rec['rec_n'], $rec['rec_monto']], [1, 30000.0]);
chk('trabajada: $62,000 (2) — V1 y V4', [$rec['trab_n'], $rec['trab_monto']], [2, 62000.0]);

echo "═ HELPER cobertura_senales (fuente única del score/widget) ═\n";
$cob_ana = Mesa::cobertura_senales(1, 101, 30);
chk('Ana por-vendedor = su fila del reporte (4 pedidas, 2 atendidas, 2 fallas)',
    [$cob_ana['pedidas'], $cob_ana['atendidas'], $cob_ana['fallas']], [4, 2, 2]);
chk('vendedor sin señales → ceros', Mesa::cobertura_senales(1, 102, 30), ['pedidas' => 0, 'atendidas' => 0, 'fallas' => 0]);
$det_ana = Mesa::cobertura_detalle(1, 101, 30);
chk('detalle de Ana: 5 episodios (incluye el de ventana abierta)', count($det_ana), 5);

echo "═ AISLAMIENTO entre empresas ═\n";
chk('empresa 1 no incluye a Carla', isset($rep['asesores'][201]), false);
$rep2 = Mesa::reporte(2, 30);
chk('empresa 2 solo Carla, 1 hablamos', [array_keys($rep2['asesores']), $rep2['asesores'][201]['hablamos'] ?? -1], [[201], 1]);

echo "═ PERÍODO 7d (las declaraciones de 9-16d salen) ═\n";
$rep7 = Mesa::reporte(1, 7);
$ana7 = $rep7['asesores'][101] ?? [];
chk('toques 7d: 3 hablamos (A3, C1... C1 fue hace 7d — borde; C2) / 1 no_contesta',
    ($ana7['hablamos'] ?? -1) >= 2 && ($ana7['hablamos'] ?? -1) <= 3, true);
chk('cartera IGUAL con 7d (es foto de hoy): activas=7, se_fueron=1', [$ana7['activas'] ?? -1, $ana7['se_fueron'] ?? -1], [7, 1]);

echo "\n" . ($fail ? "✗ $fail FALLAS — HAY ERRORES REALES EN LAS QUERIES" : "✓ SIMULACIÓN COMPLETA OK — las queries reales producen los números esperados") . "\n";
exit($fail ? 1 : 0);
