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
                     bucket_transitions, quote_sessions, usuarios, cotizacion_log, clientes,
                     desc_int_activaciones, mesa_vencidos;
CREATE TABLE cotizaciones (
  id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL, vendedor_id INT UNSIGNED NULL, cliente_id INT UNSIGNED NULL,
  numero VARCHAR(30), titulo VARCHAR(100), total DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado VARCHAR(20) NOT NULL DEFAULT 'vista', visitas INT NOT NULL DEFAULT 0,
  suspendida TINYINT NOT NULL DEFAULT 0, accion_at DATETIME NULL,
  radar_bucket VARCHAR(40) NULL, radar_bucket_at DATETIME NULL,
  ultima_vista_at DATETIME NULL, radar_senales TEXT NULL, agenda_fecha DATE NULL, agenda_at DATETIME NULL, created_at DATETIME NOT NULL
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
CREATE TABLE desc_int_activaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  cotizacion_id INT UNSIGNED NOT NULL, estado VARCHAR(12) NOT NULL DEFAULT 'activo',
  expira_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE mesa_vencidos (
  cotizacion_id INT UNSIGNED NOT NULL, usuario_id INT UNSIGNED NOT NULL,
  empresa_id INT UNSIGNED NOT NULL, fecha DATE NOT NULL,
  PRIMARY KEY (cotizacion_id, fecha)
);
SQL;
foreach (array_filter(array_map('trim', explode(';', $ddl))) as $stmt) DB::pdo()->exec($stmt);

$d = function (float $dias): string {
    $now = time();
    $mid = strtotime(date('Y-m-d')); // medianoche de HOY (hora local del sim)
    // Marcadores de "HOY" (offset < 0.5d): interpolar SIEMPRE dentro de la ventana
    // [floor, now], con floor = max(medianoche, posición natural del marcador 0.5d),
    // de modo que:
    //   (a) nunca crucen a AYER — atendida_hoy/descartada_hoy comparan el DÍA, y un
    //       marcador de "hoy" que caiga en ayer causaba el falso-fallo ~2h post-medianoche;
    //   (b) el orden global quede estrictamente descendente en TODA hora, incluido el
    //       borde 0.49↔0.5 — el clamp parcial anterior mezclaba rama plana y clamp e
    //       INVERTÍA el orden entre ~00:14 y ~03:00 (d=0.2 quedaba más reciente que d=0.1).
    // Offsets >= 0.5d quedan en su posición natural (ventanas de 24h+ intactas).
    // Nota: a <~60s de la medianoche la ventana es tan chica que dos offsets in-band
    // pueden empatar el segundo (no invertir); el desempate por id resuelve el orden.
    if ($dias >= 0 && $dias < 0.5) {
        $floor = max($mid, $now - 43200);
        return date('Y-m-d H:i:s', $now - (int) round(($dias / 0.5) * ($now - $floor)));
    }
    return date('Y-m-d H:i:s', $now - (int) round($dias * 86400));
};
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
function tap(int $cot, string $area, string $estado, float $hace_d, ?string $razon = null): void {
    global $d;
    DB::execute("INSERT INTO mesa_estados (cotizacion_id, usuario_id, empresa_id, area, estado, razon, created_at)
                 VALUES (?,900,5,?,?,?,?)", [$cot, $area, $estado, $razon, $d($hace_d)]);
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
// M10 (10): ATENDIDA_HOY — captura hoy + CALIFICADA (manita + postura):
//   desde el fix 15-jul, tocarla sin calificar ya NO marca atendida
cot(10, 500, 19000, 7, ['visitas' => 1, 'vista_d' => 1]);
tap(10, 'contacto', 'hablamos', 0.04);
tap(10, 'postura', 'decidiendo', 0.04);
fb(10, 500, 'con_interes', 0.04);
// M21 (21): tocada HOY pero SIN manita ni postura → NO atendida (sigue pendiente)
cot(21, 500, 1000, 5, ['visitas' => 2, 'vista_d' => 2]);
tap(21, 'contacto', 'hablamos', 0.04);
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
// M19 (19): MILAGRO pero con DI ACTIVO → Opción B: el sistema la tomó, sale
//   de la mesa (y del score) aunque el cliente la esté viendo caliente.
cot(19, 500, 28000, 45, ['bucket' => 'onfire', 'bucket_at_d' => 2, 'visitas' => 6, 'vista_d' => 2]);
DB::execute("INSERT INTO quote_sessions (cotizacion_id, es_interno, visible_ms, scroll_max, created_at) VALUES (19,0,5000,80,?)", [$d(2)]);
DB::execute("INSERT INTO desc_int_activaciones (empresa_id, cotizacion_id, estado, expira_at, created_at) VALUES (5,19,'activo',?,?)", [$d(-1), $d(0.5)]);
// M20 (20): MILAGRO con DI 'cancelado' (venta revertida) → la oportunidad
//   revivió, SÍ vuelve a la mesa (excepción de la Opción B).
cot(20, 500, 29000, 45, ['bucket' => 'onfire', 'bucket_at_d' => 2, 'visitas' => 6, 'vista_d' => 2]);
DB::execute("INSERT INTO quote_sessions (cotizacion_id, es_interno, visible_ms, scroll_max, created_at) VALUES (20,0,5000,80,?)", [$d(2)]);
DB::execute("INSERT INTO desc_int_activaciones (empresa_id, cotizacion_id, estado, expira_at, created_at) VALUES (5,20,'cancelado',?,?)", [$d(1), $d(2)]);

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
chk('12 filas visibles (M1,M2,M4,M5,M7,M8,M9,M10,M15,M17,M20,M21)', count($rows), 12);
chk('ids exactos', (function () use ($by) { $k = array_keys($by); sort($k); return $k; })(), [1, 2, 4, 5, 7, 8, 9, 10, 15, 17, 20, 21]);
chk('M3 (descartada 3d sin revivir) NO aparece', isset($by[3]), false);
chk('M6 (fuera >2×p75 sin calor) NO aparece', isset($by[6]), false);
chk('M11 (visitas=0 sin bucket) NO aparece', isset($by[11]), false);
chk('M12/M13/M14 (suspendida/aceptada/vendida) NO aparecen', isset($by[12]) || isset($by[13]) || isset($by[14]), false);
chk('M19 (milagro con DI ACTIVO) NO aparece — Opción B', isset($by[19]), false);
chk('M20 (milagro con DI CANCELADO) SÍ aparece — excepción B', $by[20]['cat'] ?? '', 'milagro');

echo "═ CATEGORÍAS ═\n";
chk('M1 = sin_postura', $by[1]['cat'] ?? '', 'sin_postura');
chk('M2 = descartada_hoy', $by[2]['cat'] ?? '', 'descartada_hoy');
chk('M4 = revivida', $by[4]['cat'] ?? '', 'revivida');
chk('M5 = milagro', $by[5]['cat'] ?? '', 'milagro');
chk('M7 = interes_muriendo (👍 + dormida 9d)', $by[7]['cat'] ?? '', 'interes_muriendo');
chk('M8 = ultimo_tramo (👍 + fuera de ventana)', $by[8]['cat'] ?? '', 'ultimo_tramo');
chk('M9 = trabajo (capturada)', $by[9]['cat'] ?? '', 'trabajo');
chk('M10 = trabajo + atendida_hoy (calificada + tocada hoy)', [$by[10]['cat'] ?? '', $by[10]['atendida_hoy'] ?? null], ['trabajo', true]);
chk('M21 tocada HOY sin manita/postura → NO atendida (regla 2 elementos)', [$by[21]['cat'] ?? '', $by[21]['atendida_hoy'] ?? null], ['trabajo', false]);
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
chk('grupo 0 = revividas/milagros (M15,M4 revividas; M20,M5 milagros)', array_slice($ids_orden, 0, 4), [15, 4, 20, 5]);
$tier2_pos = array_search(8, $ids_orden); $tier1_max = max(array_search(1, $ids_orden), array_search(9, $ids_orden), array_search(10, $ids_orden));
chk('tier 1 antes que tier 2 (M8 después de M1/M9/M10)', $tier2_pos > $tier1_max, true);

echo "═ RESUMEN Y LIMPIEZA ═\n";
$mr = $mesa['resumen'];
chk('universo = 12 (+M20, +M21; M19 con DI fuera)', $mr['universo'] ?? -1, 12);
chk('descartadas hoy = 1 (M2), atendidas = 1 (M10)', [$mr['descartadas'], $mr['atendidas']], [1, 1]);
chk('n pendientes = 10 (+M20 milagro, +M21 tocada sin calificar; M19 con DI fuera)', $mr['n'], 10);
chk('monto pendientes = 168,000 (138k + M20 29k + M21 1k)', $mr['monto'], 168000.0);
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

echo "═ FRÍAS (vendedor 502) ═\n";
// F1: vieja (35>20), fría, TRABAJADA (fb+postura) → Fría (sale de principal y del score)
cot(8501, 502, 10000, 35, ['visitas' => 2, 'vista_d' => 30]);
fb(8501, 502, 'con_interes', 30); tap(8501, 'postura', 'decidiendo', 30);
// F2: vieja (35>20), fría, SIN trabajar (solo contacto) → principal (falla, castiga)
cot(8502, 502, 20000, 35, ['visitas' => 2, 'vista_d' => 30]);
tap(8502, 'contacto', 'hablamos', 30);
// F3: fresca (10<20), trabajada → principal (atendida)
cot(8503, 502, 30000, 10, ['visitas' => 2, 'vista_d' => 8]);
fb(8503, 502, 'con_interes', 7); tap(8503, 'postura', 'decidiendo', 7);
$mf   = Mesa::armar(5, 502);
$mfby = [];
foreach ($mf['rows'] as $r) $mfby[$r['numero']] = $r;
chk('F1 vieja+trabajada → es_fria', $mfby['COT-8501']['es_fria'] ?? null, true);
chk('F2 vieja+sin trabajar → principal (no fría)', $mfby['COT-8502']['es_fria'] ?? null, false);
chk('F3 fresca+trabajada → principal (no fría)', $mfby['COT-8503']['es_fria'] ?? null, false);
chk('resumen.frias = 1', $mf['resumen']['frias'] ?? -1, 1);
$cf = Mesa::cobertura_senales(5, 502);
chk('cobertura excluye Frías: pedidas=2 (F2,F3), atendidas=1 (F3), fallas=1 (F2)',
    [$cf['pedidas'], $cf['atendidas'], $cf['fallas']], [2, 1, 1]);

echo "═ SIN INFO 📵 (vendedor 503) ═\n";
// S1: fb sin_info + postura declarada → manita+postura = ATENDIDA, NO descartada
cot(9501, 503, 15000, 8, ['visitas' => 2, 'vista_d' => 3]);
fb(9501, 503, 'sin_info', 2); tap(9501, 'postura', 'en_el_aire', 2);
// S2: fb sin_info SOLO (sin postura) → pedida NO atendida, NO descartada
cot(9502, 503, 25000, 8, ['visitas' => 2, 'vista_d' => 3]);
fb(9502, 503, 'sin_info', 2);
$ms   = Mesa::armar(5, 503);
$msby = [];
foreach ($ms['rows'] as $r) $msby[$r['numero']] = $r;
chk('S1 y S2 visibles (sin_info NO descarta)', [isset($msby['COT-9501']), isset($msby['COT-9502'])], [true, true]);
chk('S1/S2 NO son descartada_hoy', [($msby['COT-9501']['cat'] ?? '') === 'descartada_hoy', ($msby['COT-9502']['cat'] ?? '') === 'descartada_hoy'], [false, false]);
$cs = Mesa::cobertura_senales(5, 503);
chk('cobertura sin_info: pedidas=2, atendidas=1 (S1 manita+postura), fallas=1 (S2 sin postura)',
    [$cs['pedidas'], $cs['atendidas'], $cs['fallas']], [2, 1, 1]);

echo "═ RELOJ DE SEGUIMIENTO ⏰ (vendedor 504 — Fase A) ═\n";
// R1 (9601): no_contesta hace 4d → cadencia 2 → venció hace 2d → VENCIDA
cot(9601, 504, 10000, 8, ['visitas' => 2, 'vista_d' => 3]);
tap(9601, 'contacto', 'no_contesta', 4);
// R2 (9602): compromiso hace 2d (ancla fallback) → cadencia ceil(mediana=10) → vence en 8d → OK
cot(9602, 504, 20000, 8, ['visitas' => 2, 'vista_d' => 3]);
tap(9602, 'compromiso', 'compromiso', 2);
// R3 (9603): virgen (sin declaraciones) → SIN reloj (la exige "Por trabajar")
cot(9603, 504, 30000, 8, ['visitas' => 2, 'vista_d' => 3]);
$mv   = Mesa::armar(5, 504);
$mvby = [];
foreach ($mv['rows'] as $r) $mvby[$r['numero']] = $r;
chk('R1 no_contesta 4d → vencida 2d', [$mvby['COT-9601']['seguimiento']['estado'] ?? '', $mvby['COT-9601']['seguimiento']['dias'] ?? -1], ['vencida', 2]);
chk('R2 compromiso 2d → al corriente (cadencia mediana 10)', $mvby['COT-9602']['seguimiento']['estado'] ?? '', 'ok');
chk('R3 virgen → sin reloj', isset($mvby['COT-9603']) && ($mvby['COT-9603']['seguimiento'] ?? 'x') !== 'x' ? 'con reloj' : (isset($mvby['COT-9603']) ? 'sin reloj' : 'no visible'), 'sin reloj');
chk('resumen.vencidas = 1', $mv['resumen']['vencidas'] ?? -1, 1);
$mv_ids = array_map(fn($r) => (int)$r['id'], $mv['rows']);
chk('la vencida (R1) va PRIMERO en el orden', $mv_ids[0], 9601);
chk('R1 registró SOLO HOY en mesa_vencidos (sin backfill retroactivo — fix 2ª auditoría)',
    (int)DB::val("SELECT COUNT(*) FROM mesa_vencidos WHERE cotizacion_id = 9601"), 1);

echo "═ CITA FIRME + HUELLA (vendedores 505/506 — Fase B) ═\n";
// C1 (9701): cita hace 12d (cad mediana=10 → venció hace 2d) + RE-TAP pelón
//   hace 1d → NO re-ancla (anti-gaming): sigue CITA VENCIDA 2d
cot(9701, 506, 15000, 14, ['visitas' => 2, 'vista_d' => 5]);
tap(9701, 'compromiso', 'nos_citamos', 12);
tap(9701, 'compromiso', 'nos_citamos', 1);
// C2 (9702): cita hace 12d + Hablamos hace 1d + re-cita hace 0.5d (pospuesta
//   DE VERDAD: hablaron y re-fijaron) → re-anclada al Hablamos → al corriente
cot(9702, 506, 25000, 14, ['visitas' => 2, 'vista_d' => 5]);
tap(9702, 'compromiso', 'nos_citamos', 12);
tap(9702, 'contacto', 'hablamos', 1);
tap(9702, 'compromiso', 'nos_citamos', 0.5);
// C3 (9703): REGRESIÓN del implícito 24h (verificación 16-jul) — cita hace 12d
//   + Hablamos REAL hace 3d + re-cita HOY con su implícito razon='auto' (el
//   endpoint lo inserta porque el Hablamos real tiene >24h). El auto TAPA al
//   real como contacto vigente; el re-anclaje debe mirar el último Hablamos
//   NO-auto ($con_real) → re-anclada al -3d → al corriente. Sin el fix: vencida.
cot(9703, 506, 18000, 14, ['visitas' => 2, 'vista_d' => 5]);
tap(9703, 'compromiso', 'nos_citamos', 12);
tap(9703, 'contacto', 'hablamos', 3);
tap(9703, 'contacto', 'hablamos', 0.02, 'auto'); // implícito del endpoint, HOY
tap(9703, 'compromiso', 'nos_citamos', 0.01);
$mc   = Mesa::armar(5, 506);
$mcby = [];
foreach ($mc['rows'] as $r) $mcby[$r['numero']] = $r;
chk('C1 re-tap pelón NO re-ancla → cita vencida 2d + flag cita_vencida',
    [$mcby['COT-9701']['seguimiento']['estado'] ?? '', $mcby['COT-9701']['seguimiento']['dias'] ?? -1, $mcby['COT-9701']['cita_vencida'] ?? null],
    ['vencida', 2, true]);
chk('C2 Hablamos + re-cita (pospuesta real) → re-anclada, al corriente',
    [$mcby['COT-9702']['seguimiento']['estado'] ?? '', $mcby['COT-9702']['cita_vencida'] ?? null], ['ok', false]);
chk('C3 pospuesta real + implícito auto encima → el auto NO tapa al Hablamos real: re-anclada, al corriente',
    [$mcby['COT-9703']['seguimiento']['estado'] ?? '', $mcby['COT-9703']['cita_vencida'] ?? null], ['ok', false]);
// H1 (9801): huella — estuvo vencida 2d (preseed) pero HOY está al corriente
DB::execute("INSERT INTO mesa_vencidos VALUES (9801,505,5,?),(9801,505,5,?)", [date('Y-m-d', time() - 4 * 86400), date('Y-m-d', time() - 3 * 86400)]);
cot(9801, 505, 12000, 8, ['visitas' => 2, 'vista_d' => 3]);
tap(9801, 'contacto', 'hablamos', 1);
$mh = Mesa::armar(5, 505);
$mhby = [];
foreach ($mh['rows'] as $r) $mhby[$r['numero']] = $r;
chk('H1 al corriente PERO con huella ⏰2d (no se borra al tocar)',
    [$mhby['COT-9801']['seguimiento']['estado'] ?? '', $mhby['COT-9801']['venc_huella'] ?? -1], ['ok', 2]);

echo "\n" . ($fail ? "✗ $fail FALLAS — HAY ERRORES EN ARMAR()" : "✓ SIMULACIÓN ARMAR OK") . "\n";
exit($fail ? 1 : 0);
