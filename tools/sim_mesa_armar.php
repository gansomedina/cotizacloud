<?php
// ============================================================
// SIMULACIÓN REAL — Mesa::armar() (la COLA de trabajo) contra
// MariaDB de verdad. Categorías, filtros, caps, orden y resumen
// con expectativas calculadas a mano. p75=20 → ventana 20d,
// mesa hasta 40d, línea de limpieza = max(hist, 40).
//
// REQUISITOS (entorno de desarrollo, NUNCA producción):
//   - MariaDB/MySQL local con BD 'simtest' y usuario sim/sim
//   - DESTRUYE y recrea las tablas de simtest en cada corrida
// Correr: php tools/sim_mesa_armar.php   → debe terminar en OK
// Obligatorio tras CUALQUIER cambio a Mesa::armar().
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
require __DIR__ . '/../core/DiagnosticoTips.php';
require __DIR__ . '/../core/MesaSugerencias.php';
require __DIR__ . '/../core/Mesa.php';

// ── Esquema (incluye clientes para el LEFT JOIN; SIN usuario_score:
//    el try/catch de armar debe tragarse ese error) ──
$ddl = <<<SQL
DROP TABLE IF EXISTS cotizaciones, ventas, mesa_estados, radar_feedback,
                     bucket_transitions, quote_sessions, usuarios, cotizacion_log, clientes;
CREATE TABLE cotizaciones (
  id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL, vendedor_id INT UNSIGNED NULL, cliente_id INT UNSIGNED NULL,
  numero VARCHAR(30), titulo VARCHAR(100), total DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado VARCHAR(20) NOT NULL DEFAULT 'vista', visitas INT NOT NULL DEFAULT 0,
  suspendida TINYINT NOT NULL DEFAULT 0, accion_at DATETIME NULL,
  radar_bucket VARCHAR(40) NULL, radar_bucket_at DATETIME NULL,
  ultima_vista_at DATETIME NULL, radar_senales TEXT NULL, created_at DATETIME NOT NULL
);
CREATE TABLE clientes (id INT UNSIGNED PRIMARY KEY, nombre VARCHAR(100), telefono VARCHAR(30));
CREATE TABLE ventas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED,
  empresa_id INT UNSIGNED NOT NULL, total DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado VARCHAR(20) NOT NULL DEFAULT 'activa', created_at DATETIME NOT NULL
);
CREATE TABLE mesa_estados (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL, empresa_id INT UNSIGNED NOT NULL,
  area VARCHAR(12) NOT NULL, estado VARCHAR(30) NOT NULL, razon VARCHAR(30) NULL,
  bucket_snapshot VARCHAR(40) NULL, created_at DATETIME NOT NULL
);
CREATE TABLE radar_feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL, empresa_id INT UNSIGNED NOT NULL,
  tipo VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
  UNIQUE KEY uk_cot_user (cotizacion_id, usuario_id)
);
CREATE TABLE bucket_transitions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  bucket_anterior VARCHAR(40) NULL, bucket_nuevo VARCHAR(40) NOT NULL, created_at DATETIME NOT NULL
);
CREATE TABLE quote_sessions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  es_interno TINYINT NOT NULL DEFAULT 0, visible_ms INT NULL, scroll_max INT NULL,
  ip VARCHAR(45) NULL, created_at DATETIME NOT NULL
);
CREATE TABLE usuarios (id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL, activo TINYINT NOT NULL DEFAULT 1);
CREATE TABLE cotizacion_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NULL, accion VARCHAR(30) NULL, evento VARCHAR(30) NULL, created_at DATETIME NOT NULL
);
SQL;
foreach (array_filter(array_map('trim', explode(';', $ddl))) as $stmt) DB::pdo()->exec($stmt);

$d = fn(float $dias) => date('Y-m-d H:i:s', time() - (int)round($dias * 86400));
function cot(int $id, int $uid, float $total, float $edad, array $x = []): void {
    global $d;
    DB::execute("INSERT INTO cotizaciones (id, empresa_id, usuario_id, cliente_id, numero, titulo, total, estado, visitas,
                 suspendida, radar_bucket, radar_bucket_at, ultima_vista_at, created_at)
                 VALUES (?,5,?,?,?,?,?,?,?,?,?,?,?,?)",
        [$id, $uid, $x['cliente_id'] ?? null, "COT-$id", $x['titulo'] ?? "Cot $id", $total,
         $x['estado'] ?? 'vista', $x['visitas'] ?? 1, $x['suspendida'] ?? 0,
         $x['bucket'] ?? null, isset($x['bucket_at_d']) ? $GLOBALS['d']($x['bucket_at_d']) : null,
         isset($x['vista_d']) ? $GLOBALS['d']($x['vista_d']) : null, $d($edad)]);
}
function tap(int $cot, string $area, string $estado, float $hace_d): void {
    global $d;
    DB::execute("INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, created_at)
                 VALUES (?,900,5,?,?,?)", [$cot, $area, $estado, $d($hace_d)]);
}
function fb(int $cot, int $uid, string $tipo, float $upd_d): void {
    global $d;
    DB::execute("INSERT INTO radar_feedback (cotizacion_id, usuario_id, empresa_id, tipo, created_at, updated_at)
                 VALUES (?,?,5,?,?,?)", [$cot, $uid, $tipo, $d($upd_d), $d($upd_d)]);
}
function hotbt(int $cot, float $hace_d): void {
    global $d;
    DB::execute("INSERT INTO bucket_transitions (cotizacion_id, bucket_nuevo, created_at) VALUES (?,'probable_cierre',?)", [$cot, $d($hace_d)]);
}

DB::execute("INSERT INTO usuarios VALUES (500,5,'Vendedor',1),(501,5,'Cargado',1)");
DB::execute("INSERT INTO clientes VALUES (1,'Cliente Uno','555')");

// ══ VENDEDOR 500 — una cotización por categoría/filtro ══════
// M1 (1): SIN_POSTURA — hot fresco (bucket_at -1d), visitas 3, nada capturado
cot(1, 500, 10000, 5, ['bucket' => 'probable_cierre', 'bucket_at_d' => 1, 'visitas' => 3, 'vista_d' => 1, 'cliente_id' => 1]);
// M2 (2): DESCARTADA_HOY — 👎 del dueño hoy (hace 2 horas)
cot(2, 500, 11000, 8, ['visitas' => 2, 'vista_d' => 2]);
fb(2, 500, 'sin_interes', 0.08);
// M3 (3): descartada hace 3d SIN revivir → NO debe aparecer (ni limpieza: edad 10 < 40)
cot(3, 500, 12000, 10, ['visitas' => 1, 'vista_d' => 5]);
fb(3, 500, 'sin_interes', 3);
// M4 (4): REVIVIDA — 👎 -10d, bt hot -3d, bucket vigente probable_cierre
cot(4, 500, 13000, 15, ['bucket' => 'probable_cierre', 'bucket_at_d' => 3, 'visitas' => 4, 'vista_d' => 3]);
fb(4, 500, 'sin_interes', 10);
hotbt(4, 3);
// M5 (5): MILAGRO — edad 45 (>40), bucket hot con vista -2d (v7>0)
cot(5, 500, 14000, 45, ['bucket' => 'onfire', 'bucket_at_d' => 2, 'visitas' => 6, 'vista_d' => 2]);
DB::execute("INSERT INTO quote_sessions (cotizacion_id, es_interno, visible_ms, scroll_max, created_at) VALUES (5,0,5000,80,?)", [$d(2)]);
// M6 (6): fuera >40 sin calor, sin revivir → FUERA de la mesa + LIMPIEZA (45 > 40)
cot(6, 500, 15000, 45, ['visitas' => 1, 'vista_d' => 30]);
// M7 (7): INTERES_MURIENDO — 👍 del dueño, dormida (vista -9d), sin postura en mesa
cot(7, 500, 16000, 12, ['visitas' => 2, 'vista_d' => 9]);
fb(7, 500, 'con_interes', 8);
// M8 (8): ULTIMO_TRAMO — 👍, edad 25 (>p75), vista -3d (no dormida)
cot(8, 500, 17000, 25, ['visitas' => 3, 'vista_d' => 3]);
fb(8, 500, 'con_interes', 5);
// M9 (9): TRABAJO — captura ayer (hablamos tras 2 no_contesta)
cot(9, 500, 18000, 6, ['visitas' => 2, 'vista_d' => 2]);
tap(9, 'contacto', 'no_contesta', 3);
tap(9, 'contacto', 'no_contesta', 2);
tap(9, 'contacto', 'hablamos', 1);
// M10 (10): ATENDIDA_HOY — captura hoy (hace 1 hora)
cot(10, 500, 19000, 7, ['visitas' => 1, 'vista_d' => 1]);
tap(10, 'contacto', 'hablamos', 0.04);
// M11 (11): visitas=0 sin bucket → FUERA (la cubre "Sin abrir" del dashboard)
cot(11, 500, 20000, 5, ['visitas' => 0]);
// M12 (12): suspendida → FUERA del universo
cot(12, 500, 21000, 5, ['suspendida' => 1, 'visitas' => 2]);
// M13 (13): aceptada → FUERA del universo
cot(13, 500, 22000, 5, ['estado' => 'aceptada', 'visitas' => 2]);
// M14 (14): con venta → FUERA del universo
cot(14, 500, 23000, 5, ['visitas' => 2]);
DB::execute("INSERT INTO ventas (cotizacion_id, empresa_id, total, estado, created_at) VALUES (14,5,23000,'activa',?)", [$d(1)]);

// M15 (15): CALOR SOSTENIDO revive — 👎 -5d, bucket hot desde -10d (SIN
//   transición nueva), cliente ABRIÓ -2d → debe volver como revivida
cot(15, 500, 24000, 30, ['bucket' => 'probable_cierre', 'bucket_at_d' => 10, 'visitas' => 5, 'vista_d' => 2]);
fb(15, 500, 'sin_interes', 5);
hotbt(15, 10);
// M16 (16): LEGACY/reasignada — postura 'descartada' en mesa SIN rf del dueño
//   → debe tratarse como descartada (fuera de la mesa, no 'trabajo' eterno)
cot(16, 500, 25000, 12, ['visitas' => 2, 'vista_d' => 8]);
tap(16, 'postura', 'descartada', 4);
// M17 (17): fila 'feedback' de dueño ANTERIOR (uid 999) sin nada más →
//   sigue siendo sin_postura (una marca ajena no es captura del asesor)
cot(17, 500, 26000, 6, ['bucket' => 'probable_cierre', 'bucket_at_d' => 1, 'visitas' => 2, 'vista_d' => 1]);
DB::execute("INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, created_at) VALUES (17,999,5,'feedback','con_interes',?)", [$d(3)]);
// M18 (18): 👎 -10d + transición hot -2d PERO SIN VISTA REAL posterior
//   (visitas=0) → regla nueva 12-jul: la transición sola es flapping del
//   Radar, NO revive. Regression test del caso COT-2026-0246.
cot(18, 500, 27000, 15, ['visitas' => 0]);
fb(18, 500, 'sin_interes', 10);
hotbt(18, 2);

// ══ VENDEDOR 501 — CAPS: 8 milagros + 20 hot tier1 = 28 filas pre-cap ══
for ($i = 1; $i <= 8; $i++) { // milagros: edad 50, hot fresco
    cot(100 + $i, 501, 5000 + $i, 50, ['bucket' => 'probable_cierre', 'bucket_at_d' => 1, 'visitas' => 2, 'vista_d' => 1]);
}
for ($i = 1; $i <= 20; $i++) { // tier 1: edad 5, hot fresco
    cot(200 + $i, 501, 3000 + $i, 5, ['bucket' => 'probable_cierre', 'bucket_at_d' => 1, 'visitas' => 2, 'vista_d' => 1]);
}

// ── EXPECTATIVAS ───────────────────────────────────────────
$fail = 0;
function chk(string $name, $got, $want): void {
    global $fail;
    $ok = ($got == $want);
    if (!$ok) $fail++;
    echo ($ok ? "  ✓ " : "  ✗ ") . $name . ($ok ? '' : '  got=' . json_encode($got) . ' want=' . json_encode($want)) . "\n";
}

$mesa = Mesa::armar(5, 500);
$rows = $mesa['rows'];
$by = [];
foreach ($rows as $r) $by[(int)$r['id']] = $r;

echo "═ VISIBILIDAD (quién entra y quién no) ═\n";
chk('10 filas visibles (M1,M2,M4,M5,M7,M8,M9,M10,M15,M17)', count($rows), 10);
chk('ids exactos', (function () use ($by) { $k = array_keys($by); sort($k); return $k; })(), [1, 2, 4, 5, 7, 8, 9, 10, 15, 17]);
chk('M3 (descartada 3d sin revivir) NO aparece', isset($by[3]), false);
chk('M6 (fuera >2×p75 sin calor) NO aparece', isset($by[6]), false);
chk('M11 (visitas=0 sin bucket) NO aparece', isset($by[11]), false);
chk('M12/M13/M14 (suspendida/aceptada/vendida) NO aparecen', isset($by[12]) || isset($by[13]) || isset($by[14]), false);

echo "═ CATEGORÍAS ═\n";
chk('M1 = sin_postura', $by[1]['cat'] ?? '', 'sin_postura');
chk('M2 = descartada_hoy', $by[2]['cat'] ?? '', 'descartada_hoy');
chk('M4 = revivida', $by[4]['cat'] ?? '', 'revivida');
chk('M5 = milagro', $by[5]['cat'] ?? '', 'milagro');
chk('M7 = interes_muriendo (👍 + dormida 9d)', $by[7]['cat'] ?? '', 'interes_muriendo');
chk('M8 = ultimo_tramo (👍 + fuera de ventana)', $by[8]['cat'] ?? '', 'ultimo_tramo');
chk('M9 = trabajo (capturada)', $by[9]['cat'] ?? '', 'trabajo');
chk('M10 = trabajo + atendida_hoy', [$by[10]['cat'] ?? '', $by[10]['atendida_hoy'] ?? null], ['trabajo', true]);
chk('M15 = revivida por CALOR SOSTENIDO (sin transición nueva)', $by[15]['cat'] ?? '', 'revivida');
chk('M16 (postura descartada sin rf) NO aparece — descarte de doble fuente', isset($by[16]), false);
chk('M17 = sin_postura (la marca del dueño anterior no es captura)', $by[17]['cat'] ?? '', 'sin_postura');
chk('M18 (transición hot SIN vista real) NO aparece — el flapping no revive', isset($by[18]), false);

echo "═ FLAGS Y DATOS DE FILA ═\n";
chk('M7 dormida = true', $by[7]['dormida'] ?? null, true);
chk('M4 revivida flag / M5 milagro flag', [$by[4]['revivida'] ?? null, $by[5]['milagro'] ?? null], [true, true]);
chk('M9 ult_decl_dias = 1, atendida_hoy = false', [$by[9]['ult_decl_dias'] ?? -1, $by[9]['atendida_hoy'] ?? null], [1, false]);
chk('M1 cliente del LEFT JOIN', $by[1]['cliente'] ?? '', 'Cliente Uno');
chk('M9 decl contacto vigente = hablamos', $by[9]['decl']['contacto']['estado'] ?? '', 'hablamos');
$sug_vacias = 0;
foreach ($rows as $r) if (trim((string)$r['sugerencia']) === '') $sug_vacias++;
chk('todas las filas tienen sugerencia no vacía', $sug_vacias, 0);

echo "═ ORDEN ═\n";
$ids_orden = array_map(fn($r) => (int)$r['id'], $rows);
chk('grupo 0 = revividas/milagros (M4, M15 pc; M5 onfire) y luego grupo 1', array_slice($ids_orden, 0, 4), [15, 4, 5, 17]);
$tier2_pos = array_search(8, $ids_orden); $tier1_max = max(array_search(1, $ids_orden), array_search(9, $ids_orden), array_search(10, $ids_orden));
chk('tier 1 antes que tier 2 (M8 después de M1/M9/M10)', $tier2_pos > $tier1_max, true);

echo "═ RESUMEN Y LIMPIEZA ═\n";
$mr = $mesa['resumen'];
chk('universo = 10', $mr['universo'] ?? -1, 10);
chk('descartadas hoy = 1 (M2), atendidas = 1 (M10)', [$mr['descartadas'], $mr['atendidas']], [1, 1]);
chk('n pendientes = 8', $mr['n'], 8);
chk('monto pendientes = 138,000 (+M15 24k, M17 26k; M18 fuera)', $mr['monto'], 138000.0);
chk('sin_postura = 2 (M1, M17), mas_viejo = 6', [$mr['sin_postura'], $mr['mas_viejo_dias']], [2, 6]);
chk('limpieza: 1 cotización $15,000 (M6), línea 40', [$mesa['limpieza']['n'], $mesa['limpieza']['monto'], $mesa['limpieza']['linea_dias']], [1, 15000.0, 40]);

echo "═ CAPS (vendedor 501: 8 milagros + 20 tier1 = 28) ═\n";
// Sin tope de mesa: se muestran TODAS (6 milagros por CAP_MILAGROS + 20 tier1 = 26).
// Solo milagros/revividas siguen topados a 6 para no inundar la cabecera.
$m2 = Mesa::armar(5, 501);
$cats = array_count_values(array_column($m2['rows'], 'cat'));
chk('lista completa: 6 milagros + 20 tier1 = 26 (sin tope de mesa)', count($m2['rows']), 26);
chk('universo = 28', $m2['resumen']['universo'] ?? -1, 28);
chk('milagros capeados a 6', $cats['milagro'] ?? 0, 6);
chk('tier1 completo = 20', $cats['sin_postura'] ?? 0, 20);

echo "\n" . ($fail ? "✗ $fail FALLAS — HAY ERRORES EN ARMAR()" : "✓ SIMULACIÓN ARMAR OK") . "\n";
exit($fail ? 1 : 0);
