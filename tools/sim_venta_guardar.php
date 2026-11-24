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
//
//  Y quitar descuentos en la venta (24-nov-2026):
//   · cupón, descuento especial (cronómetro / "Agregar descuento") e
//     inteligente se quitan sin permiso de descuentos, por quien trabaja la
//     venta (admin, ver_todas_ventas o su asesor); otro asesor no;
//   · el total, saldo y estado quedan como los dejaría guardar.php, y el
//     historial anota quién lo quitó, de cuánto era y el total antes y después;
//   · al quitar el cupón, la copia congelada de la cotización lo conserva;
//   · guardar.php pide el permiso de descuentos solo para SUBIRLO, y en ventas
//     con DI no escribe un descuento viejo en la cotización;
//   · convertir.php copia a la venta el descuento y el cupón que trae el total
//     (antes la venta nacía sin ellos y el primer guardado los borraba).
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

$tablas = ['venta_log', 'desc_int_activaciones', 'cotizacion_lineas', 'ventas', 'cotizaciones', 'clientes', 'cotizacion_log'];
function limpiar(): void { global $pdo, $tablas; foreach ($tablas as $t) $pdo->exec("DROP TABLE IF EXISTS $t"); }
limpiar();
$pdo->exec("CREATE TABLE cotizaciones (
    id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NULL, usuario_id INT UNSIGNED NULL, vendedor_id INT UNSIGNED NULL,
    titulo VARCHAR(255) NOT NULL DEFAULT 'Cotización', estado VARCHAR(20) NOT NULL DEFAULT 'aceptada',
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0, total DECIMAL(14,2) NOT NULL DEFAULT 0,
    impuesto_modo VARCHAR(20) NOT NULL DEFAULT 'ninguno', impuesto_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    cupon_codigo VARCHAR(30) NULL, cupon_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    cupon_monto DECIMAL(14,2) NOT NULL DEFAULT 0,
    descuento_auto_activo TINYINT NOT NULL DEFAULT 0, descuento_auto_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    descuento_auto_expira DATETIME NULL,
    descuento_auto_amt DECIMAL(14,2) NOT NULL DEFAULT 0,
    lineas_snapshot LONGTEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE ventas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL, cotizacion_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NULL, usuario_id INT UNSIGNED NULL, vendedor_id INT UNSIGNED NULL,
    numero VARCHAR(30) NULL, titulo VARCHAR(255) NULL, slug VARCHAR(160) NULL, token VARCHAR(64) NULL,
    total DECIMAL(14,2) NOT NULL DEFAULT 0, pagado DECIMAL(14,2) NOT NULL DEFAULT 0,
    saldo DECIMAL(14,2) NOT NULL DEFAULT 0, estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    descuento_auto_amt DECIMAL(14,2) NOT NULL DEFAULT 0, cupon_monto DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL) DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE clientes (id INT UNSIGNED PRIMARY KEY, nombre VARCHAR(120)) DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE cotizacion_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED, usuario_id INT UNSIGNED NULL,
    accion VARCHAR(40), detalle TEXT NULL, ip VARCHAR(60) NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP) DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE cotizacion_lineas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL,
    orden INT NOT NULL DEFAULT 0, sku VARCHAR(100) NULL, titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL, cantidad DECIMAL(12,3) NOT NULL DEFAULT 1,
    precio_unit DECIMAL(14,2) NOT NULL DEFAULT 0, subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    es_extra TINYINT NOT NULL DEFAULT 0) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE desc_int_activaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL, empresa_id INT UNSIGNED NOT NULL DEFAULT 1,
    estado VARCHAR(20) NOT NULL, pct DECIMAL(5,2) NOT NULL DEFAULT 0, monto_desc DECIMAL(14,2) NOT NULL DEFAULT 0,
    nuevo_total DECIMAL(14,2) NOT NULL, expira_at DATETIME NULL)");
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
$accion = $argv[5] ?? "";   // ventas/acciones.php la recibe del Router
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
// Rol por variables de entorno: CZ_ROL=admin|asesor, CZ_UID, CZ_PERM=permiso,permiso
class Auth {
    public static function es_admin(){ return (getenv("CZ_ROL") ?: "admin") === "admin"; }
    public static function puede($p){ return self::es_admin() || in_array($p, array_filter(explode(",", (string)getenv("CZ_PERM"))), true); }
    public static function id(){ return (int)(getenv("CZ_UID") ?: 7); }
}
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
// Runner completo para cotizaciones/convertir.php: Helpers y DescuentoInteligente
// REALES (slug_unico, generar_token, snapshot_cotizacion, csrf real con token).
$runner_full = tempnam(sys_get_temp_dir(), 'vgf') . '.php';
file_put_contents($runner_full, '<?php
define("COTIZAAPP", 1);
define("DEBUG", false);
define("CSRF_TOKEN_NAME", "csrf_token");
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
    public static function insert($s,$p=[]){ self::execute($s,$p); return (int)self::pdo()->lastInsertId(); }
    public static function siguiente_folio($e,$t,$pre){ return $pre . "-SIM-" . substr(bin2hex(random_bytes(3)),0,6); }
    public static function beginTransaction(){ self::pdo()->beginTransaction(); }
    public static function commit(){ self::pdo()->commit(); }
    public static function rollback(){ if (self::pdo()->inTransaction()) self::pdo()->rollBack(); }
}
class Auth {
    public static function es_admin(){ return (getenv("CZ_ROL") ?: "admin") === "admin"; }
    public static function puede($p){ return self::es_admin() || in_array($p, array_filter(explode(",", (string)getenv("CZ_PERM"))), true); }
    public static function id(){ return (int)(getenv("CZ_UID") ?: 7); }
    public static function empresa(){ return ["id" => EMPRESA_ID, "vta_prefijo" => "VTA"]; }
}
$_SESSION = [CSRF_TOKEN_NAME => "tok-sim"];
$_SERVER["HTTP_X_CSRF_TOKEN"] = "tok-sim";
$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
require "' . __DIR__ . '/../core/Helpers.php";
require "' . __DIR__ . '/../core/DescuentoInteligente.php";
require $archivo;
');
// $como: rol (admin|asesor), uid, perm (lista), accion (para acciones.php), full (convertir)
function post(string $endpoint, int $id, array $body, int $emp = 1, array $como = []): array {
    global $runner, $runner_full;
    $archivo = __DIR__ . '/../modules/' . (str_contains($endpoint, '/') ? $endpoint : 'ventas/' . $endpoint) . '.php';
    $env = 'CZ_ROL=' . escapeshellarg($como['rol'] ?? 'admin') . ' CZ_UID=' . (int)($como['uid'] ?? 7)
         . ' CZ_PERM=' . escapeshellarg($como['perm'] ?? '');
    $out = shell_exec($env . ' php ' . escapeshellarg(!empty($como['full']) ? $runner_full : $runner) . ' '
        . escapeshellarg($archivo) . ' ' . $emp . ' ' . $id . ' ' . escapeshellarg(json_encode($body)) . ' '
        . escapeshellarg($como['accion'] ?? '') . ' 2>&1');
    return json_decode((string)$out, true) ?? ['raw' => $out];
}
// Quitar un descuento como lo hace la pantalla
function quitar(int $venta_id, string $tipo, array $como): array {
    return $tipo === 'inteligente'
        ? post('acciones', $venta_id, [], 1, $como + ['accion' => 'quitar-desc-int'])
        : post('acciones', $venta_id, ['tipo' => $tipo], 1, $como + ['accion' => 'quitar-descuento']);
}
$ASESOR_SIN_PERMISOS = ['rol' => 'asesor', 'uid' => 8, 'perm' => ''];
$OTRO_ASESOR         = ['rol' => 'asesor', 'uid' => 9, 'perm' => 'editar_cotizaciones,aplicar_descuentos'];
$log_ultimo = fn(int $vid) => row("SELECT evento, usuario_id, detalle FROM venta_log WHERE venta_id = ? ORDER BY id DESC LIMIT 1", [$vid]);
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

// ════════════════════════════════════════════════════════════
//  QUITAR DESCUENTOS EN LA VENTA (24-nov-2026)
//  Mismos montos en todos: base 10,000 + IVA 16% sumado, descuento 10%.
//  El asesor 8 es el de la venta; el 9 no lo es ni ve todas las ventas.
// ════════════════════════════════════════════════════════════
echo "\n── Quitar el cupón: lo hace el asesor de la venta sin permisos ──\n";
$snap_viejo = json_encode(['lineas' => [['titulo' => 'Cocina']], 'descuento_auto_amt' => 0], JSON_UNESCAPED_UNICODE);
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, impuesto_modo, impuesto_pct, cupon_codigo, cupon_pct, cupon_monto, lineas_snapshot)
   VALUES (1101,1,11000,11600,'suma',16,'PROMO10',10,1000,?)", [$snap_viejo]);
linea(1101, 1, 'Cocina', 'Base.', 10000);
linea(1101, 2, 'Flete', 'Extra.', 1000, 1);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, usuario_id, total, pagado, saldo, estado, cupon_monto)
   VALUES (601,1,1101,8,11600,2000,9600,'parcial',1000)");
$r = quitar(601, 'cupon', $OTRO_ASESOR);
chk('otro asesor (ni suya ni ve todas las ventas) no puede, aunque tenga permiso de descuentos', [$r['ok'] ?? null, $r['error'] ?? null], [false, 'Sin acceso a esta venta']);
chk('y no cambió nada', (string)val("SELECT total FROM ventas WHERE id=601"), '11600.00');
$r = quitar(601, 'cupon', $ASESOR_SIN_PERMISOS);
chk('quitar cupón: ok', $r['ok'] ?? $r, true);
// (10,000 + extra 1,000) × 1.16 = 12,760 — la fórmula de guardar.php sin el cupón
chk('total, saldo y estado recalculados', row("SELECT total, saldo, estado FROM ventas WHERE id=601"), ['total' => '12760.00', 'saldo' => '10760.00', 'estado' => 'parcial']);
chk('el cupón sale de la cotización y de la venta', [row("SELECT cupon_codigo, cupon_pct, cupon_monto FROM cotizaciones WHERE id=1101"), (string)val("SELECT cupon_monto FROM ventas WHERE id=601")],
    [['cupon_codigo' => null, 'cupon_pct' => '0.00', 'cupon_monto' => '0.00'], '0.00']);
$snap = json_decode((string)val("SELECT lineas_snapshot FROM cotizaciones WHERE id=1101"), true);
// json_encode escribe 1000.0 como 1000: se compara como número
chk('la copia congelada conserva el cupón con el que se aceptó', [(float)($snap['cupon_monto'] ?? -1), $snap['cupon_codigo'] ?? null, (float)($snap['cupon_pct'] ?? -1), $snap['lineas'][0]['titulo'] ?? null], [1000.0, 'PROMO10', 10.0, 'Cocina']);
chk('historial: quién, qué, de cuánto y el total antes y después', $log_ultimo(601),
    ['evento' => 'descuento_quitado', 'usuario_id' => 8, 'detalle' => 'Cupón PROMO10 (10%) · $1,000.00 · total $11,600.00 → $12,760.00']);
$r = quitar(601, 'cupon', $ASESOR_SIN_PERMISOS);
chk('quitarlo otra vez: "no tiene cupón" y no se anota de nuevo', [$r['error'] ?? null, (int)val("SELECT COUNT(*) FROM venta_log WHERE venta_id=601")], ['Esta venta no tiene cupón', 1]);
$r = post('guardar', 601, ['lineas' => payload_lineas(1101), 'descuento_auto_amt' => 0, 'descuento_auto_pct' => 0]);
chk('guardar después da el mismo total (misma fórmula)', (string)val("SELECT total FROM ventas WHERE id=601"), '12760.00');

echo "\n── Quitar el descuento especial (cronómetro / Agregar descuento) ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, impuesto_modo, impuesto_pct, descuento_auto_activo, descuento_auto_pct, descuento_auto_amt)
   VALUES (1102,1,10000,10440,'suma',16,1,10,1000)");
linea(1102, 1, 'Cocina', 'Base.', 10000);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, usuario_id, total, pagado, saldo, estado, descuento_auto_amt)
   VALUES (602,1,1102,8,10440,10440,0,'pagada',1000)");
$r = quitar(602, 'especial', $ASESOR_SIN_PERMISOS);
chk('quitar descuento especial: ok', $r['ok'] ?? $r, true);
chk('total 11,600; la venta pagada pasa a parcial con saldo', row("SELECT total, saldo, estado, descuento_auto_amt FROM ventas WHERE id=602"),
    ['total' => '11600.00', 'saldo' => '1160.00', 'estado' => 'parcial', 'descuento_auto_amt' => '0.00']);
chk('y sale de la cotización (igual que la ✕ de antes)', row("SELECT descuento_auto_amt, descuento_auto_pct, descuento_auto_activo FROM cotizaciones WHERE id=1102"),
    ['descuento_auto_amt' => '0.00', 'descuento_auto_pct' => '0.00', 'descuento_auto_activo' => 0]);
chk('historial', $log_ultimo(602), ['evento' => 'descuento_quitado', 'usuario_id' => 8, 'detalle' => 'Descuento especial (10%) · $1,000.00 · total $10,440.00 → $11,600.00']);

echo "\n── Quitar el Descuento Inteligente (antes pedía permiso y el botón no se veía) ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, impuesto_modo, impuesto_pct) VALUES (1103,1,10000,10440,'suma',16)");
linea(1103, 1, 'Cocina', 'Base.', 10000);
q("INSERT INTO desc_int_activaciones (cotizacion_id, empresa_id, estado, pct, monto_desc, nuevo_total) VALUES (1103,1,'utilizado',10,1160,10440)");
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, usuario_id, total, pagado, saldo, estado) VALUES (603,1,1103,8,10440,0,10440,'pendiente')");
$r = quitar(603, 'cupon', $ASESOR_SIN_PERMISOS);
chk('en una venta con DI, cupón/especial no se quitan: se quita el inteligente', $r['error'] ?? null, 'Esta venta tiene Descuento Inteligente: ese es el descuento que se quita.');
$r = quitar(603, 'inteligente', $OTRO_ASESOR);
chk('otro asesor no puede quitar el inteligente', [$r['ok'] ?? null, (string)val("SELECT estado FROM desc_int_activaciones WHERE cotizacion_id=1103")], [false, 'utilizado']);
$r = quitar(603, 'inteligente', $ASESOR_SIN_PERMISOS);
chk('el asesor de la venta, sin permisos, sí', $r['ok'] ?? $r, true);
chk('regresa a precio lleno y el DI queda cancelado', [row("SELECT total, saldo, estado FROM ventas WHERE id=603"), (string)val("SELECT estado FROM desc_int_activaciones WHERE cotizacion_id=1103")],
    [['total' => '11600.00', 'saldo' => '11600.00', 'estado' => 'pendiente'], 'cancelado']);
chk('historial', $log_ultimo(603), ['evento' => 'descuento_quitado', 'usuario_id' => 8, 'detalle' => 'Descuento inteligente (10%) · $1,160.00 · total $10,440.00 → $11,600.00']);

echo "\n── Quién más puede, y casos que no ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, impuesto_modo, impuesto_pct, descuento_auto_pct, descuento_auto_amt) VALUES (1104,1,10000,9000,'ninguno',0,10,1000)");
linea(1104, 1, 'Closet', 'Base.', 10000);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, usuario_id, vendedor_id, total, pagado, saldo, estado, descuento_auto_amt) VALUES (604,1,1104,5,9,9000,0,9000,'pendiente',1000)");
$r = quitar(604, 'especial', $OTRO_ASESOR);
chk('el asesor asignado (vendedor_id) también puede', [$r['ok'] ?? null, (string)val("SELECT total FROM ventas WHERE id=604")], [true, '10000.00']);
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, cupon_codigo, cupon_monto) VALUES (1105,1,10000,9000,'X',1000)");
linea(1105, 1, 'Closet', 'Base.', 10000);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, usuario_id, total, pagado, saldo, estado, cupon_monto) VALUES (605,1,1105,5,9000,0,9000,'pendiente',1000)");
$r = quitar(605, 'cupon', ['rol' => 'asesor', 'uid' => 9, 'perm' => 'ver_todas_ventas']);
chk('quien ve todas las ventas también', [$r['ok'] ?? null, (string)val("SELECT total FROM ventas WHERE id=605")], [true, '10000.00']);
$r = post('acciones', 605, ['tipo' => 'otro'], 1, ['accion' => 'quitar-descuento']);
chk('tipo desconocido → error', $r['error'] ?? null, 'Tipo de descuento no válido');
q("UPDATE ventas SET estado='cancelada' WHERE id=605");
$r = quitar(605, 'cupon', []);
chk('venta cancelada → no', $r['error'] ?? null, 'Venta cancelada');

echo "\n── guardar.php: el permiso de descuentos solo para SUBIRLO ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, impuesto_modo, impuesto_pct, descuento_auto_activo, descuento_auto_pct, descuento_auto_amt)
   VALUES (1106,1,10000,10440,'suma',16,1,10,1000)");
linea(1106, 1, 'Cocina', 'Base.', 10000);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, usuario_id, total, pagado, saldo, estado, descuento_auto_amt) VALUES (606,1,1106,8,10440,0,10440,'pendiente',1000)");
$editor = ['rol' => 'asesor', 'uid' => 8, 'perm' => 'editar_cotizaciones'];
$r = post('guardar', 606, ['lineas' => payload_lineas(1106), 'descuento_auto_amt' => 1000, 'descuento_auto_pct' => 10], 1, $editor);
chk('sin permiso de descuentos, guarda la venta con su descuento intacto (antes: "Sin permiso")', [$r['ok'] ?? $r, (string)val("SELECT total FROM ventas WHERE id=606")], [true, '10440.00']);
$r = post('guardar', 606, ['lineas' => payload_lineas(1106), 'descuento_auto_amt' => 1500, 'descuento_auto_pct' => 0], 1, $editor);
chk('pero subirlo sí pide el permiso', [$r['error'] ?? null, (string)val("SELECT descuento_auto_amt FROM ventas WHERE id=606")], ['Sin permiso para aplicar descuentos', '1000.00']);
$r = post('guardar', 606, ['lineas' => payload_lineas(1106), 'descuento_auto_amt' => 1500, 'descuento_auto_pct' => 0], 1, ['rol' => 'asesor', 'uid' => 8, 'perm' => 'editar_cotizaciones,aplicar_descuentos']);
chk('con el permiso, sube', (string)val("SELECT descuento_auto_amt FROM ventas WHERE id=606"), '1500.00');

echo "\n── guardar.php: en ventas con DI no se cuela un descuento en la cotización ──\n";
q("INSERT INTO cotizaciones (id, empresa_id, subtotal, total, impuesto_modo, impuesto_pct) VALUES (1107,1,10500,11020,'suma',16)");
linea(1107, 1, 'Cocina', 'Base.', 10000);
linea(1107, 2, 'Flete', 'Extra.', 500, 1);
q("INSERT INTO desc_int_activaciones (cotizacion_id, empresa_id, estado, pct, monto_desc, nuevo_total) VALUES (1107,1,'utilizado',10,1160,10440)");
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, usuario_id, total, pagado, saldo, estado) VALUES (607,1,1107,8,11020,0,11020,'pendiente')");
$r = post('guardar', 607, ['lineas' => payload_lineas(1107), 'descuento_auto_amt' => 500, 'descuento_auto_pct' => 0]);
chk('total = contrato 10,440 + flete 580; la cotización no se queda con los $500 (antes sí, y en silencio)',
    [(string)val("SELECT total FROM ventas WHERE id=607"), (string)val("SELECT descuento_auto_amt FROM cotizaciones WHERE id=1107"), (int)val("SELECT COUNT(*) FROM venta_log WHERE venta_id=607 AND evento LIKE 'descuento%'")],
    ['11020.00', '0.00', 0]);

echo "\n── convertir.php: la venta nace con el descuento que trae su total ──\n";
q("INSERT INTO clientes (id, nombre) VALUES (1, 'Cliente')");
$futuro = date('Y-m-d H:i:s', time() + 2 * 86400);
$pasado = date('Y-m-d H:i:s', time() - 86400);
q("INSERT INTO cotizaciones (id, empresa_id, cliente_id, usuario_id, titulo, estado, subtotal, total, impuesto_modo, impuesto_pct,
     descuento_auto_activo, descuento_auto_pct, descuento_auto_expira, descuento_auto_amt)
   VALUES (1108,1,1,8,'Cocina reloj','enviada',10000,10440,'suma',16,1,10,?,1000)", [$futuro]);
linea(1108, 1, 'Cocina', 'Base.', 10000);
$r = post('cotizaciones/convertir', 1108, [], 1, ['full' => true]);
$vid = (int)($r['data']['venta_id'] ?? 0);
chk('convertir con cronómetro vigente: ok', [$r['ok'] ?? $r, $vid > 0], [true, true]);
chk('la venta guarda el descuento (antes nacía en 0 y no se veía)', row("SELECT total, descuento_auto_amt, cupon_monto FROM ventas WHERE id=?", [$vid]),
    ['total' => '10440.00', 'descuento_auto_amt' => '1000.00', 'cupon_monto' => '0.00']);
// La pantalla manda el descuento que tiene la VENTA (DESC_AUTO_AMT_INIT en ver.php)
$r = post('guardar', $vid, ['lineas' => payload_lineas(1108), 'descuento_auto_amt' => (float)val("SELECT descuento_auto_amt FROM ventas WHERE id=?", [$vid]), 'descuento_auto_pct' => 10]);
chk('el primer «Guardar cambios» ya no lo borra (antes subía a 11,600)', (string)val("SELECT total FROM ventas WHERE id=?", [$vid]), '10440.00');
q("INSERT INTO cotizaciones (id, empresa_id, cliente_id, usuario_id, titulo, estado, subtotal, total, impuesto_modo, impuesto_pct, cupon_codigo, cupon_pct, cupon_monto)
   VALUES (1109,1,1,8,'Cocina cupon','vista',10000,10440,'suma',16,'ASESOR',10,1000)");
linea(1109, 1, 'Cocina', 'Base.', 10000);
$r = post('cotizaciones/convertir', 1109, [], 1, ['full' => true]);
$vid2 = (int)($r['data']['venta_id'] ?? 0);
chk('con cupón: la venta lo guarda', row("SELECT total, descuento_auto_amt, cupon_monto FROM ventas WHERE id=?", [$vid2]),
    ['total' => '10440.00', 'descuento_auto_amt' => '0.00', 'cupon_monto' => '1000.00']);
$snap2 = json_decode((string)val("SELECT lineas_snapshot FROM cotizaciones WHERE id=1109"), true);
chk('y la copia congelada trae el cupón desde el inicio', [(float)($snap2['cupon_monto'] ?? -1), $snap2['cupon_codigo'] ?? null, (float)($snap2['cupon_pct'] ?? -1)], [1000.0, 'ASESOR', 10.0]);
q("INSERT INTO cotizaciones (id, empresa_id, cliente_id, usuario_id, titulo, estado, subtotal, total, impuesto_modo, impuesto_pct,
     descuento_auto_activo, descuento_auto_pct, descuento_auto_expira, descuento_auto_amt)
   VALUES (1110,1,1,8,'Cocina vencida','enviada',10000,10440,'suma',16,1,10,?,1000)", [$pasado]);
linea(1110, 1, 'Cocina', 'Base.', 10000);
$r = post('cotizaciones/convertir', 1110, [], 1, ['full' => true]);
$vid3 = (int)($r['data']['venta_id'] ?? 0);
chk('cronómetro vencido: la venta va a precio lleno y sin descuento', row("SELECT total, descuento_auto_amt FROM ventas WHERE id=?", [$vid3]), ['total' => '11600.00', 'descuento_auto_amt' => '0.00']);
chk('y la cotización queda igual que la venta', row("SELECT total, descuento_auto_amt FROM cotizaciones WHERE id=1110"), ['total' => '11600.00', 'descuento_auto_amt' => '0.00']);

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
$ac = file_get_contents(__DIR__ . '/../modules/ventas/acciones.php');
$rt = file_get_contents(__DIR__ . '/../core/Router.php');
$vl = file_get_contents(__DIR__ . '/../core/VentaLog.php');
$ay = file_get_contents(__DIR__ . '/../modules/ayuda/index.php');
chk('los tres descuentos traen «Quitar» en el resumen', substr_count($vv, "\${quitar('") === 3 && str_contains($vv, 'const PUEDE_QUITAR_DESC = <?= $puede_quitar_desc'));
chk('«Quitar» lo ve quien trabaja la venta (mismo acceso que acciones.php)',
    str_contains($vv, "\$puede_quitar_desc = \$venta['estado'] !== 'cancelada' && (Auth::puede('ver_todas_ventas')")
    && str_contains($ac, "\$trabaja_la_venta = Auth::puede('ver_todas_ventas')"));
chk('ni la ✕ de solo-admin ni el enlace muerto del bloque de impresión', !str_contains($vv, 'uiEliminarDescuento') && !str_contains($vv, 'quitarDescInt'));
chk('el resumen de las ventas con DI muestra el descuento y el total real', str_contains($vv, 'DI.nuevo_total + extFinal') && str_contains($vv, "✨ Descuento inteligente (\${DI.pct}%)"));
chk('con DI no se ofrece «Agregar descuento»', str_contains($vv, "\$puede_descuento && \$venta['estado'] !== 'cancelada' && !\$desc_int_act"));
chk('ya no dice «escribe 0 / pon 0 para quitarlo»', !str_contains($vv, 'Escribe 0 para quitar') && !str_contains($ay, 'Pon 0 para quitarlo'));
chk('quitar-desc-int ya no pide permiso de descuentos', !preg_match("/quitar-desc-int'\\) \\{\\s*if \\(!Auth::es_admin\\(\\) && !Auth::puede\\('aplicar_descuentos'\\)\\)/", $ac));
chk('ruta /ventas/:id/quitar-descuento', str_contains($rt, "'/ventas/:id/quitar-descuento'") && str_contains($rt, "['accion'=>'quitar-descuento']"));
chk('el historial lo nombra «Se quitó el descuento»', str_contains($vl, "'descuento_quitado'   => [ico('x',14,'#dc2626'),  'Se quitó el descuento']"));

@unlink($runner);
@unlink($runner_full);
limpiar();
echo "\n" . ($fail === 0 ? "✓ SIMULACIÓN VENTA GUARDAR OK — $ok comprobaciones contra MariaDB real\n" : "✗ $fail FALLAS ($ok bien)\n");
exit($fail === 0 ? 0 : 1);
