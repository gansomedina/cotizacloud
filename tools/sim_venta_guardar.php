<?php
// ============================================================
//  Simulación — guardar la venta y agregar un extra, contra MariaDB real
//  Uso: php tools/sim_venta_guardar.php
//  Requiere: MariaDB local, BD simtest, usuario sim/sim (igual que las otras sims)
//
//  Corre los endpoints REALES (modules/ventas/guardar.php y agregar_extra.php)
//  con un runner de prueba (DB/Auth/csrf/json y php://input simulados), mismo
//  patrón que tools/sim_metas.php. Obligatoria tras tocar el guardado de la venta.
//
//  Cubre (18-nov-2026):
//   · la descripción ya no se corta a 1000 BYTES: llega completa hasta 5,000
//     letras y, pasando de ahí, se corta por letra (sin partir acentos);
//   · guardar la venta conserva qué líneas son extras;
//   · con Descuento Inteligente, el total sigue cobrando los extras;
//   · agregar un extra guarda su descripción completa;
//   · la pantalla de la venta manda es_extra, trae la descripción del catálogo
//     y no ofrece mover a extra / principal en ventas con DI.
// ============================================================

$pdo = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4', 'sim', 'sim', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$pdo->exec("SET time_zone = '" . (new DateTime('now', new DateTimeZone('America/Hermosillo')))->format('P') . "'");
function q(string $s, array $p = []) { global $pdo; $st = $pdo->prepare($s); $st->execute($p); return $st; }
function val(string $s, array $p = []) { return q($s, $p)->fetchColumn(); }
function row(string $s, array $p = []) { $r = q($s, $p)->fetch(); return $r === false ? null : $r; }

$ok = 0; $fail = 0;
function chk(string $msg, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $msg\n"; return; }
    $fail++;
    echo "  ✗ $msg\n    obtenido: " . var_export($got, true) . "\n    esperado: " . var_export($want, true) . "\n";
}

$tablas = ['venta_log', 'desc_int_activaciones', 'cotizacion_lineas', 'ventas', 'cotizaciones'];
function limpiar(): void { global $pdo, $tablas; foreach ($tablas as $t) $pdo->exec("DROP TABLE IF EXISTS $t"); }
limpiar();
$pdo->exec("CREATE TABLE cotizaciones (
    id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0, total DECIMAL(14,2) NOT NULL DEFAULT 0,
    impuesto_modo VARCHAR(20) NOT NULL DEFAULT 'ninguno', impuesto_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    cupon_monto DECIMAL(14,2) NOT NULL DEFAULT 0,
    descuento_auto_activo TINYINT NOT NULL DEFAULT 0, descuento_auto_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    descuento_auto_amt DECIMAL(14,2) NOT NULL DEFAULT 0,
    updated_at DATETIME NULL) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE ventas (
    id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL, cotizacion_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NULL, total DECIMAL(14,2) NOT NULL DEFAULT 0, pagado DECIMAL(14,2) NOT NULL DEFAULT 0,
    saldo DECIMAL(14,2) NOT NULL DEFAULT 0, estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    descuento_auto_amt DECIMAL(14,2) NOT NULL DEFAULT 0, updated_at DATETIME NULL) DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE cotizacion_lineas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
    orden INT NOT NULL DEFAULT 0, sku VARCHAR(100) NULL, titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL, cantidad DECIMAL(12,3) NOT NULL DEFAULT 1,
    precio_unit DECIMAL(14,2) NOT NULL DEFAULT 0, subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    es_extra TINYINT NOT NULL DEFAULT 0) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE desc_int_activaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
    estado VARCHAR(20) NOT NULL, nuevo_total DECIMAL(14,2) NOT NULL)");
$pdo->exec("CREATE TABLE venta_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, venta_id INT UNSIGNED, empresa_id INT UNSIGNED,
    usuario_id INT UNSIGNED NULL, evento VARCHAR(40), detalle TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP) DEFAULT CHARSET=utf8mb4");

// ── Runner: corre el endpoint real con DB/Auth/csrf/json de prueba ──
$runner = tempnam(sys_get_temp_dir(), 'vg') . '.php';
file_put_contents($runner, '<?php
define("COTIZAAPP", 1);
define("DEBUG", false);
date_default_timezone_set("America/Hermosillo");
[$_, $archivo, $emp, $id, $body] = $argv;
define("EMPRESA_ID", (int)$emp);
class DB {
    private static $pdo = null;
    public static function pdo(): PDO { return self::$pdo ??= (function () {
        $p = new PDO("mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4","sim","sim",
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
        $p->exec("SET time_zone = \'" . date("P") . "\'");
        return $p; })(); }
    public static function query($s,$p=[]){ $st=self::pdo()->prepare($s); $st->execute($p); return $st->fetchAll(); }
    public static function row($s,$p=[]){ $st=self::pdo()->prepare($s); $st->execute($p); $r=$st->fetch(); return $r===false?null:$r; }
    public static function val($s,$p=[]){ $st=self::pdo()->prepare($s); $st->execute($p); return $st->fetchColumn(); }
    public static function execute($s,$p=[]){ $st=self::pdo()->prepare($s); $st->execute($p); return $st->rowCount(); }
    public static function beginTransaction(){ self::pdo()->beginTransaction(); }
    public static function commit(){ self::pdo()->commit(); }
    public static function rollback(){ if (self::pdo()->inTransaction()) self::pdo()->rollBack(); }
}
class Auth { public static function es_admin(){ return true; } public static function puede($p){ return true; } public static function id(){ return 7; } }
function csrf_check(){}
function json_ok($d=[],$m=""){ echo json_encode(["ok"=>true,"data"=>$d]); exit; }
function json_error($m,$c=400,$d=[]){ echo json_encode(["ok"=>false,"error"=>$m,"code"=>$c]); exit; }
class InWrap { public $context; private $d; private $p=0;
  function stream_open($path,$mode,$o,&$op){ global $body; $this->d = $path==="php://input" ? $body : ""; return true; }
  function stream_read($n){ $r=substr($this->d,$this->p,$n); $this->p+=strlen($r); return $r; }
  function stream_eof(){ return $this->p>=strlen($this->d); } function stream_stat(){ return []; } }
stream_wrapper_unregister("php"); stream_wrapper_register("php", "InWrap");
require "' . __DIR__ . '/../core/VentaLog.php";
require $archivo;
');
function post(string $endpoint, int $venta_id, array $body, int $emp = 1): array {
    global $runner;
    $archivo = __DIR__ . '/../modules/ventas/' . $endpoint . '.php';
    $out = shell_exec('php ' . escapeshellarg($runner) . ' ' . escapeshellarg($archivo) . ' ' . $emp . ' '
        . $venta_id . ' ' . escapeshellarg(json_encode($body)) . ' 2>&1');
    return json_decode((string)$out, true) ?? ['raw' => $out];
}
// Las líneas tal como las manda la pantalla de la venta (guardarCambios)
function payload_lineas(int $cot_id, bool $con_extra = true): array {
    $ls = q("SELECT id, titulo, sku, descripcion, cantidad, precio_unit, subtotal, es_extra
               FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden", [$cot_id])->fetchAll();
    return array_map(function ($l) use ($con_extra) {
        $p = ['id' => (int)$l['id'], 'titulo' => $l['titulo'], 'sku' => $l['sku'] ?? '',
              'descripcion' => $l['descripcion'] ?? '', 'cantidad' => (float)$l['cantidad'],
              'precio_unit' => (float)$l['precio_unit'], 'subtotal' => (float)$l['subtotal']];
        if ($con_extra) $p['es_extra'] = (int)$l['es_extra'] ? 1 : 0;
        return $p;
    }, $ls);
}
function linea(int $cot, int $orden, string $titulo, string $desc, float $precio, int $extra = 0): void {
    q("INSERT INTO cotizacion_lineas (cotizacion_id, orden, titulo, descripcion, cantidad, precio_unit, subtotal, es_extra)
       VALUES (?,?,?,?,1,?,?,?)", [$cot, $orden, $titulo, $desc, $precio, $precio, $extra]);
}

// Textos de prueba: con acentos, ñ, formato y saltos de línea
$larga = str_repeat("Cocina integral en melamina blanca, cubierta de cuarzo, jaladeras <b>negras</b> y bisagras de cierre suave; instalación incluida.\n", 13);
$enorme = str_repeat('Diseño señorial ', 375);   // 6,000 letras; más bytes por la ñ

echo "\n── Textos de prueba ──\n";
chk('la descripción larga pasa de 1000 bytes', strlen($larga) > 1000);
chk('y trae acentos, ñ y una etiqueta de formato', str_contains($larga, 'instalación') && str_contains($larga, '<b>'));
chk('la enorme tiene 6,000 letras y más bytes', [mb_strlen($enorme), strlen($enorme) > 6000], [6000, true]);

echo "\n── Guardar la venta: la descripción llega completa ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, impuesto_modo, impuesto_pct, cupon_monto) VALUES (1001,1,11500,11020,'suma',16,2000)");
linea(1001, 1, 'Cocina integral', $larga, 10000);
linea(1001, 2, 'Instalación eléctrica', 'Extra: contactos y apagadores.', 1500, 1);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, total, pagado, saldo, estado) VALUES (501,1,1001,11020,0,11020,'pendiente')");
$r = post('guardar', 501, ['lineas' => payload_lineas(1001), 'descuento_auto_amt' => 0, 'descuento_auto_pct' => 0]);
chk('guardar: ok', $r['ok'] ?? $r, true);
$guardada = val("SELECT descripcion FROM cotizacion_lineas WHERE cotizacion_id = 1001 AND titulo = 'Cocina integral'");
chk('la descripción quedó idéntica a la original (no a 1000 bytes)', $guardada === trim($larga));
chk('su largo en bytes pasa de 1000', strlen((string)$guardada) > 1000);

echo "\n── Guardar la venta: los extras siguen siendo extras ──\n";
chk('la línea extra sigue con es_extra = 1', (int)val("SELECT es_extra FROM cotizacion_lineas WHERE cotizacion_id = 1001 AND titulo = 'Instalación eléctrica'"), 1);
chk('la principal sigue con es_extra = 0', (int)val("SELECT es_extra FROM cotizacion_lineas WHERE cotizacion_id = 1001 AND titulo = 'Cocina integral'"), 0);
// base 10,000 − cupón 2,000 = 8,000; + extra 1,500 = 9,500 gravable; IVA 16% → 11,020
chk('total = base con cupón + extra, con IVA', [(string)val("SELECT total FROM ventas WHERE id = 501"), (float)($r['data']['total'] ?? 0)], ['11020.00', 11020.0]);
$r = post('guardar', 501, ['lineas' => payload_lineas(1001), 'descuento_auto_amt' => 0, 'descuento_auto_pct' => 0]);
chk('un segundo guardado no cambia nada', [(int)val("SELECT es_extra FROM cotizacion_lineas WHERE cotizacion_id = 1001 AND titulo = 'Instalación eléctrica'"), val("SELECT descripcion FROM cotizacion_lineas WHERE cotizacion_id = 1001 AND titulo = 'Cocina integral'") === trim($larga), (string)val("SELECT total FROM ventas WHERE id = 501")], [1, true, '11020.00']);
$r = post('guardar', 501, ['lineas' => payload_lineas(1001, false), 'descuento_auto_amt' => 0, 'descuento_auto_pct' => 0]);
chk('una línea que llega sin es_extra se guarda como principal (como antes)', (int)val("SELECT SUM(es_extra) FROM cotizacion_lineas WHERE cotizacion_id = 1001"), 0);

echo "\n── Guardar la venta: tope de 5,000 letras ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total) VALUES (1002,1,5000,5000)");
linea(1002, 1, 'Closet', $enorme, 5000);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, total, pagado, saldo, estado) VALUES (502,1,1002,5000,0,5000,'pendiente')");
$r = post('guardar', 502, ['lineas' => payload_lineas(1002), 'descuento_auto_amt' => 0, 'descuento_auto_pct' => 0]);
$g = (string)val("SELECT descripcion FROM cotizacion_lineas WHERE cotizacion_id = 1002");
chk('guardar: ok', $r['ok'] ?? $r, true);
chk('se corta en 5,000 letras exactas', mb_strlen($g), 5000);
chk('el corte es por letra: queda UTF-8 válido', mb_check_encoding($g, 'UTF-8'));
chk('es el principio exacto del texto', $g === mb_substr(trim($enorme), 0, 5000));

echo "\n── Con Descuento Inteligente: el total sigue cobrando el extra ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, impuesto_modo, impuesto_pct) VALUES (1003,1,10500,9580,'suma',16)");
linea(1003, 1, 'Cocina', 'Base del contrato.', 10000);
linea(1003, 2, 'Flete', 'Extra.', 500, 1);
q("INSERT INTO desc_int_activaciones (cotizacion_id, estado, nuevo_total) VALUES (1003,'utilizado',9000)");
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, total, pagado, saldo, estado) VALUES (503,1,1003,9580,0,9580,'pendiente')");
$r = post('guardar', 503, ['lineas' => payload_lineas(1003), 'descuento_auto_amt' => 0, 'descuento_auto_pct' => 0]);
// contrato congelado 9,000 + extra 500 con IVA (580) = 9,580; sin es_extra el extra se perdía → 9,000
chk('total = contrato congelado + extra con IVA (antes se perdía el extra)', [(string)val("SELECT total FROM ventas WHERE id = 503"), (int)val("SELECT es_extra FROM cotizacion_lineas WHERE cotizacion_id = 1003 AND titulo = 'Flete'")], ['9580.00', 1]);

echo "\n── Agregar un extra: la descripción llega completa ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total) VALUES (1004,1,1000,1000)");
linea(1004, 1, 'Mueble', 'Corto.', 1000);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, total, pagado, saldo, estado) VALUES (504,1,1004,1000,0,1000,'pendiente')");
$r = post('agregar_extra', 504, ['titulo' => 'Iluminación LED', 'descripcion' => $larga, 'total' => 800]);
chk('agregar extra: ok', $r['ok'] ?? $r, true);
chk('su descripción quedó completa', val("SELECT descripcion FROM cotizacion_lineas WHERE cotizacion_id = 1004 AND titulo = 'Iluminación LED'") === trim($larga));
$r = post('agregar_extra', 504, ['titulo' => 'Zoclo', 'descripcion' => $enorme, 'total' => 300]);
$g = (string)val("SELECT descripcion FROM cotizacion_lineas WHERE cotizacion_id = 1004 AND titulo = 'Zoclo'");
chk('y la enorme se corta en 5,000 letras, UTF-8 válido', [mb_strlen($g), mb_check_encoding($g, 'UTF-8')], [5000, true]);

echo "\n── Contratos en el código ──\n";
$gv = file_get_contents(__DIR__ . '/../modules/ventas/guardar.php');
$ae = file_get_contents(__DIR__ . '/../modules/ventas/agregar_extra.php');
$vv = file_get_contents(__DIR__ . '/../modules/ventas/ver.php');
chk('guardar.php corta la descripción por letra en 5,000', str_contains($gv, "mb_substr(trim(\$l['descripcion'] ?? ''), 0, 5000)"));
chk('guardar.php ya no la corta en 1000 bytes', !preg_match("/substr\\(trim\\(\\\$l\\['descripcion'\\][^;]*1000\\)/", $gv));
chk('agregar_extra.php corta por letra en 5,000', str_contains($ae, "mb_substr(trim(\$body['descripcion'] ?? ''), 0, 5000)"));
chk('agregar_extra.php ya no la corta en 1000 bytes', !preg_match("/substr\\(trim\\(\\\$body\\['descripcion'\\][^;]*1000\\)/", $ae));
chk('la pantalla manda es_extra al guardar', str_contains($vv, 'es_extra:    l.es_extra ? 1 : 0,'));
chk('el catálogo de la venta trae la descripción', str_contains($vv, 'SELECT id, titulo, sku, precio, unidad, descripcion FROM articulos'));
chk('agregar del catálogo usa esa descripción', str_contains($vv, "desc     = CATALOGO_DESC[artSelId] || '';"));
chk('la descripción del catálogo va escapada para <script>', str_contains($vv, 'const CATALOGO_DESC = <?= json_encode(') && str_contains($vv, 'JSON_HEX_TAG | JSON_HEX_APOS'));
chk('y si json_encode fallara queda {} (no se cae el JavaScript)', str_contains($vv, "JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;"));
chk('con DI no se ofrece mover a extra / principal', str_contains($vv, '(ES_ADMIN || PUEDE_EXTRAS) && !TIENE_DI') && str_contains($vv, "const TIENE_DI = <?= \$desc_int_act ? 'true' : 'false' ?>;"));

@unlink($runner);
limpiar();
echo "\n" . ($fail === 0 ? "✓ SIMULACIÓN VENTA GUARDAR OK — $ok comprobaciones contra MariaDB real\n" : "✗ $fail FALLAS ($ok bien)\n");
exit($fail === 0 ? 0 : 1);
