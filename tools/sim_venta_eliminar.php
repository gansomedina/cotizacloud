<?php
// ============================================================
//  Simulación — eliminar una venta cancelada y regresar su cotización
//  Uso: php tools/sim_venta_eliminar.php
//  Requiere: MariaDB local, BD simtest, usuario sim/sim (igual que las otras sims)
//
//  Decisión CEO (26-nov-2026): una venta CANCELADA que NUNCA tuvo pagos se puede
//  eliminar, y su cotización regresa a su estado original —«Vista» si el cliente
//  la abrió, «Enviada» si nunca—, con las partidas de la copia congelada, sus
//  visitas y su historial, para poder aceptarla otra vez. Solo el admin.
//
//  Corre los endpoints REALES: api/quote_action.php (el cliente acepta),
//  ventas/acciones.php (cancelar y eliminar), ventas/guardar.php (editar la
//  venta) y cotizaciones/convertir.php, con Helpers.php, VentaLog.php y
//  DescuentoInteligente.php reales. Las tablas llevan las MISMAS llaves
//  foráneas que producción (consulta del 26-nov-2026): recibos, gastos_venta y
//  venta_log borran en cascada; cotizacion_lineas.venta_id queda en NULL;
//  ventas → cotizaciones restringe.
//
//  Obligatoria tras tocar eliminar la venta o regresar la cotización.
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

$tablas = ['mesa_estados', 'quote_sessions', 'cupones', 'desc_int_activaciones', 'cotizacion_log', 'venta_log',
           'gastos_venta', 'recibos', 'cotizacion_lineas', 'ventas', 'cotizaciones', 'clientes', 'empresas',
           'cotizacion_log_x'];
function limpiar(): void {
    global $pdo, $tablas;
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($tablas as $t) $pdo->exec("DROP TABLE IF EXISTS $t");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}
limpiar();
$pdo->exec("CREATE TABLE empresas (
    id INT UNSIGNED PRIMARY KEY, nombre VARCHAR(120) NOT NULL DEFAULT 'Empresa',
    impuesto_modo VARCHAR(20) NOT NULL DEFAULT 'suma', impuesto_pct DECIMAL(5,2) NOT NULL DEFAULT 16,
    vta_prefijo VARCHAR(10) NOT NULL DEFAULT 'VTA', moneda VARCHAR(5) NOT NULL DEFAULT 'MXN',
    notif_config TEXT NULL, notif_email_acepta TINYINT NOT NULL DEFAULT 0, notif_email_rechaza TINYINT NOT NULL DEFAULT 0,
    notif_email VARCHAR(120) NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE clientes (id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL, nombre VARCHAR(120))
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE cotizaciones (
    id INT UNSIGNED PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL, cliente_id INT UNSIGNED NULL,
    usuario_id INT UNSIGNED NULL, vendedor_id INT UNSIGNED NULL,
    numero VARCHAR(30) NULL, titulo VARCHAR(255) NOT NULL DEFAULT 'Cocina', slug VARCHAR(160) NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'vista', suspendida TINYINT NOT NULL DEFAULT 0,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0, total DECIMAL(14,2) NOT NULL DEFAULT 0,
    impuesto_modo VARCHAR(20) NOT NULL DEFAULT 'suma', impuesto_pct DECIMAL(5,2) NOT NULL DEFAULT 16,
    impuesto_amt DECIMAL(14,2) NOT NULL DEFAULT 0,
    cupon_id INT UNSIGNED NULL, cupon_codigo VARCHAR(30) NULL, cupon_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    cupon_monto DECIMAL(14,2) NOT NULL DEFAULT 0,
    descuento_auto_activo TINYINT NOT NULL DEFAULT 0, descuento_auto_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    descuento_auto_expira DATETIME NULL, descuento_auto_amt DECIMAL(14,2) NOT NULL DEFAULT 0,
    lineas_snapshot LONGTEXT NULL, visitas INT UNSIGNED NOT NULL DEFAULT 0,
    vista_at DATETIME NULL, ultima_vista_at DATETIME NULL, aceptada_at DATETIME NULL, accion_at DATETIME NULL,
    radar_bucket VARCHAR(40) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE ventas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
    cotizacion_id INT UNSIGNED NULL, cliente_id INT UNSIGNED NULL, usuario_id INT UNSIGNED NULL, vendedor_id INT UNSIGNED NULL,
    numero VARCHAR(30) NULL, titulo VARCHAR(255) NULL, slug VARCHAR(160) NULL, token VARCHAR(64) NULL,
    total DECIMAL(14,2) NOT NULL DEFAULT 0, pagado DECIMAL(14,2) NOT NULL DEFAULT 0, saldo DECIMAL(14,2) NOT NULL DEFAULT 0,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    descuento_auto_amt DECIMAL(14,2) NOT NULL DEFAULT 0, cupon_monto DECIMAL(14,2) NOT NULL DEFAULT 0,
    notas_internas TEXT NULL, cancelado_at DATETIME NULL, cancelado_motivo VARCHAR(255) NULL, cancelado_por_id INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE cotizacion_lineas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL, venta_id INT UNSIGNED NULL,
    articulo_id INT UNSIGNED NULL, orden INT NOT NULL DEFAULT 0, titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL, sku VARCHAR(100) NULL, cantidad DECIMAL(12,3) NOT NULL DEFAULT 1,
    precio_unit DECIMAL(14,2) NOT NULL DEFAULT 0, subtotal DECIMAL(14,2) NOT NULL DEFAULT 0, es_extra TINYINT NOT NULL DEFAULT 0,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE recibos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL, venta_id INT UNSIGNED NOT NULL,
    numero VARCHAR(30) NULL, monto DECIMAL(14,2) NOT NULL DEFAULT 0, cancelado TINYINT NOT NULL DEFAULT 0,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE gastos_venta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL, venta_id INT UNSIGNED NULL,
    concepto VARCHAR(200) NOT NULL, importe DECIMAL(14,2) NOT NULL DEFAULT 0, fecha DATE NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE venta_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, venta_id INT UNSIGNED NOT NULL, empresa_id INT UNSIGNED,
    usuario_id INT UNSIGNED NULL, evento VARCHAR(40), detalle TEXT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE cotizacion_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL, usuario_id INT UNSIGNED NULL,
    evento VARCHAR(30) NULL, detalle TEXT NULL, ip VARCHAR(60) NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    accion VARCHAR(30) NULL,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE desc_int_activaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
    cotizacion_id INT UNSIGNED NOT NULL, cliente_id INT UNSIGNED NULL, pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    precio_original DECIMAL(14,2) NOT NULL DEFAULT 0, monto_desc DECIMAL(14,2) NOT NULL DEFAULT 0,
    nuevo_total DECIMAL(14,2) NOT NULL DEFAULT 0, estado VARCHAR(12) NOT NULL DEFAULT 'activo', expira_at DATETIME NULL,
    UNIQUE KEY uk_cotizacion (cotizacion_id),
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE cupones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL, codigo VARCHAR(30) NOT NULL,
    porcentaje DECIMAL(5,2) NULL, monto_fijo DECIMAL(14,2) NULL, activo TINYINT NOT NULL DEFAULT 1,
    usos INT NOT NULL DEFAULT 0, vencimiento_tipo VARCHAR(20) NULL, vencimiento_dias INT NULL, vencimiento_fecha DATE NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE quote_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL, es_interno TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE mesa_estados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED NOT NULL, estado VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");

// IVA 16% sumado. Los avisos de "cotización aceptada" apagados (push/correo).
q("INSERT INTO empresas (id, notif_config) VALUES (1, ?), (2, ?)",
  ['{"cotizacion_aceptada":false}', '{"cotizacion_aceptada":false}']);
q("INSERT INTO clientes (id, empresa_id, nombre) VALUES (1, 1, 'Cliente')");

// ── Runner: corre el archivo real con Auth/CSRF/php://input de prueba ──
$errlog = tempnam(sys_get_temp_dir(), 'vel');   // lo que el código manda a error_log()
$runner = tempnam(sys_get_temp_dir(), 've') . '.php';
file_put_contents($runner, str_replace('__RAIZ__', dirname(__DIR__), <<<'PHP'
<?php
define("COTIZAAPP", 1);
define("DEBUG", false);
define("CSRF_TOKEN_NAME", "csrf_token");
date_default_timezone_set("America/Hermosillo");
[$_, $archivo, $emp, $id, $body] = $argv;
$accion = $argv[5] ?? "";   // ventas/acciones.php la recibe del Router
define("EMPRESA_ID", (int)$emp);
class DB {
    private static $pdo = null;
    public static function pdo(): PDO { return self::$pdo ??= (function () {
        $p = new PDO("mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4", "sim", "sim",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
        $p->exec("SET time_zone = '" . date("P") . "'");
        return $p; })(); }
    public static function query($s, $p = []) { $st = self::pdo()->prepare($s); $st->execute($p); return $st->fetchAll(); }
    public static function row($s, $p = []) { $st = self::pdo()->prepare($s); $st->execute($p); $r = $st->fetch(); return $r ?: null; }
    public static function val($s, $p = []) { $st = self::pdo()->prepare($s); $st->execute($p); return $st->fetchColumn(); }
    public static function execute($s, $p = []) { $st = self::pdo()->prepare($s); $st->execute($p); return $st->rowCount(); }
    public static function insert($s, $p = []) { self::execute($s, $p); return (int)self::pdo()->lastInsertId(); }
    public static function siguiente_folio($e, $t, $pre) { return $pre . "-SIM-" . bin2hex(random_bytes(3)); }
    public static function beginTransaction() { self::pdo()->beginTransaction(); }
    public static function commit() { self::pdo()->commit(); }
    public static function rollback() { if (self::pdo()->inTransaction()) self::pdo()->rollBack(); }
}
// Rol por variables de entorno: CZ_ROL=admin|asesor, CZ_UID, CZ_PERM=permiso,permiso
class Auth {
    public static function es_admin() { return (getenv("CZ_ROL") ?: "admin") === "admin"; }
    public static function puede($p) { return self::es_admin() || in_array($p, array_filter(explode(",", (string)getenv("CZ_PERM"))), true); }
    public static function id() { return (int)(getenv("CZ_UID") ?: 7); }
    public static function empresa() { return ["id" => EMPRESA_ID, "vta_prefijo" => "VTA"]; }
}
class PushNotification { public static function __callStatic($n, $a) {} }
class Mailer { public static function __callStatic($n, $a) {} }
class MarketingPixels { public static function __callStatic($n, $a) { return ""; } }
$_SESSION = [CSRF_TOKEN_NAME => "tok-sim"];
$_SERVER["HTTP_X_CSRF_TOKEN"] = "tok-sim";
$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
// error_log() a un archivo: en la terminal se mezclaría con el JSON. Los avisos
// de PHP (display_errors) sí salen junto al JSON y rompen la prueba a propósito.
ini_set("error_log", getenv("CZ_ERRLOG") ?: "/dev/null");
class InWrap { public $context; private $d; private $p = 0;
    function stream_open($path, $mode, $o, &$op) { global $body; $this->d = $path === "php://input" ? $body : ""; return true; }
    function stream_read($n) { $r = substr($this->d, $this->p, $n); $this->p += strlen($r); return $r; }
    function stream_eof() { return $this->p >= strlen($this->d); }
    function stream_stat() { return []; } }
stream_wrapper_unregister("php"); stream_wrapper_register("php", "InWrap");
require "__RAIZ__/core/Helpers.php";
require "__RAIZ__/core/VentaLog.php";
require "__RAIZ__/core/DescuentoInteligente.php";
require $archivo;
PHP));

// $como: rol (admin|asesor), uid, perm (lista), accion (para acciones.php)
function post(string $archivo, int $id, array $body, array $como = [], int $emp = 1): array {
    global $runner, $errlog;
    $env = 'CZ_ROL=' . escapeshellarg($como['rol'] ?? 'admin') . ' CZ_UID=' . (int)($como['uid'] ?? 7)
         . ' CZ_PERM=' . escapeshellarg($como['perm'] ?? '') . ' CZ_ERRLOG=' . escapeshellarg($errlog);
    $out = shell_exec($env . ' php ' . escapeshellarg($runner) . ' ' . escapeshellarg(dirname(__DIR__) . '/' . $archivo)
        . ' ' . $emp . ' ' . $id . ' ' . escapeshellarg(json_encode($body)) . ' ' . escapeshellarg($como['accion'] ?? '') . ' 2>&1');
    // Cualquier aviso de PHP antes del JSON lo rompe: la prueba lo atrapa.
    $j = json_decode((string)$out, true);
    return is_array($j) ? $j : ['raw' => $out];
}
function eliminar(int $vid, array $como = [], int $emp = 1): array { return post('modules/ventas/acciones.php', $vid, [], $como + ['accion' => 'eliminar'], $emp); }
function cancelar(int $vid, string $motivo): array { return post('modules/ventas/acciones.php', $vid, ['motivo' => $motivo], ['accion' => 'cancelar']); }
function aceptar(int $cid, array $extra = []): array {
    return post('api/quote_action.php', 0, ['cotizacion_id' => $cid, 'accion' => 'aceptar', 'slug' => 'slug-' . $cid, 'nombre' => 'Cliente Sim'] + $extra);
}
function convertir(int $cid): array { return post('modules/cotizaciones/convertir.php', $cid, []); }
function venta_de(int $cid): ?array { return row("SELECT * FROM ventas WHERE cotizacion_id = ? ORDER BY id DESC LIMIT 1", [$cid]); }
function lineas_de(int $cid): array {
    return q("SELECT titulo, articulo_id, precio_unit, subtotal, es_extra, venta_id FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden, id", [$cid])->fetchAll();
}
function cot(int $cid): array { return row("SELECT * FROM cotizaciones WHERE id = ?", [$cid]); }
function log_regreso(int $cid): ?array {
    return row("SELECT evento, usuario_id, detalle FROM cotizacion_log WHERE cotizacion_id = ? AND evento IN ('regresada','venta_eliminada') ORDER BY id DESC LIMIT 1", [$cid]);
}
// Cotización enviada/vista con sus partidas: Cocina 10,000 (del catálogo, artículo 55) + Flete 500 (extra).
// Total con IVA 16%: (10,000 + 500) × 1.16 = 12,180.
function nueva_cot(int $id, array $c = []): void {
    q("INSERT INTO cotizaciones (id, empresa_id, cliente_id, usuario_id, vendedor_id, numero, titulo, slug, estado,
         subtotal, total, impuesto_amt, visitas, vista_at, ultima_vista_at,
         cupon_id, cupon_codigo, cupon_pct, cupon_monto,
         descuento_auto_activo, descuento_auto_pct, descuento_auto_expira, descuento_auto_amt)
       VALUES (?,1,1,8,8,?,?,?,?, 10500,?,?, ?,?,?, ?,?,?,?, ?,?,?,?)",
      [$id, 'COT-' . $id, 'Cocina ' . $id, 'slug-' . $id, $c['estado'] ?? 'vista',
       $c['total'] ?? 12180, $c['impuesto_amt'] ?? 1680,
       $c['visitas'] ?? 3, array_key_exists('vista_at', $c) ? $c['vista_at'] : '2026-11-01 10:00:00', $c['ultima_vista_at'] ?? '2026-11-02 18:30:00',
       $c['cupon_id'] ?? null, $c['cupon_codigo'] ?? null, $c['cupon_pct'] ?? 0, $c['cupon_monto'] ?? 0,
       $c['d_activo'] ?? 0, $c['d_pct'] ?? 0, $c['d_expira'] ?? null, $c['d_amt'] ?? 0]);
    q("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, orden, sku, titulo, descripcion, cantidad, precio_unit, subtotal, es_extra)
       VALUES (?,55,1,'COC-1','Cocina','Melamina blanca con cubierta de cuarzo.',1,10000,10000,0),
              (?,NULL,2,'','Flete','Entrega en obra.',1,500,500,1)", [$id, $id]);
}
// Venta cancelada armada a mano (como las que ya existen en producción)
function venta_cancelada(int $vid, int $cid, array $v = []): void {
    q("INSERT INTO ventas (id, empresa_id, cotizacion_id, numero, total, saldo, estado, notas_internas, cancelado_motivo)
       VALUES (?,?,?,?,12180,12180,'cancelada',?,?)",
      [$vid, $v['empresa'] ?? 1, $cid, $v['numero'] ?? 'VTA-2026-' . $vid, $v['notas'] ?? null, $v['motivo'] ?? null]);
}
$SNAP_LINEAS = function (int $cid): array {   // la copia que congeló el aceptar, como la deja SELECT *
    return q("SELECT * FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden", [$cid])->fetchAll();
};
$futuro = date('Y-m-d H:i:s', time() + 3 * 86400);
$pasado = date('Y-m-d H:i:s', time() - 5 * 86400);
$ASESOR = ['rol' => 'asesor', 'uid' => 8, 'perm' => 'editar_cotizaciones,ver_todas_ventas,ver_todas_cots,aplicar_descuentos'];

// ════════════════════════════════════════════════════════════
echo "\n── El cliente acepta, se cancela la venta y se elimina ──\n";
nueva_cot(3001);
q("INSERT INTO quote_sessions (cotizacion_id) VALUES (3001), (3001)");
q("INSERT INTO mesa_estados (cotizacion_id, estado) VALUES (3001, 'hablamos')");
$r = aceptar(3001);
chk('aceptar (api/quote_action.php real): ok', $r['ok'] ?? $r, true);
$v = venta_de(3001);
chk('nació la venta y la cotización quedó aceptada con copia congelada',
    [$v !== null, cot(3001)['estado'], cot(3001)['lineas_snapshot'] !== null], [true, 'aceptada', true]);
$snap = json_decode((string)cot(3001)['lineas_snapshot'], true);
chk('la copia congelada ya guarda el cronómetro (descuento_auto_activo/pct/expira)',
    [array_key_exists('descuento_auto_activo', $snap), array_key_exists('descuento_auto_pct', $snap), array_key_exists('descuento_auto_expira', $snap)],
    [true, true, true]);
$r = eliminar((int)$v['id']);
chk('una venta que NO está cancelada no se elimina', [$r['ok'] ?? null, $r['error'] ?? null], [false, 'Primero cancela la venta.']);
$r = cancelar((int)$v['id'], 'El cliente se fue con otro proveedor');
chk('cancelar: ok', $r['ok'] ?? $r, true);
$vc = row("SELECT estado, cancelado_motivo, cancelado_por_id, cancelado_at IS NOT NULL AS con_fecha, notas_internas FROM ventas WHERE id = ?", [$v['id']]);
chk('cancelar ahora llena cancelado_motivo, cancelado_por_id y cancelado_at',
    [$vc['estado'], $vc['cancelado_motivo'], (int)$vc['cancelado_por_id'], (int)$vc['con_fecha']],
    ['cancelada', 'El cliente se fue con otro proveedor', 7, 1]);
chk('y sigue anotándolo en las notas internas, como siempre', str_contains((string)$vc['notas_internas'], '[Cancelada: El cliente se fue con otro proveedor]'));
chk('la cotización sigue aceptada (el problema que se resuelve)', cot(3001)['estado'], 'aceptada');
q("INSERT INTO venta_log (venta_id, empresa_id, evento) VALUES (?, 1, 'cotizacion_guardada')", [$v['id']]);
$antes = cot(3001);
$log_antes = (int)val("SELECT COUNT(*) FROM cotizacion_log WHERE cotizacion_id = 3001");

$r = eliminar((int)$v['id'], $ASESOR);
chk('un asesor no puede (aunque tenga permisos): solo el admin', [$r['ok'] ?? null, $r['error'] ?? null], [false, 'Solo el administrador puede eliminar una venta']);
chk('y no cambió nada', [venta_de(3001)['id'] ?? null, cot(3001)['estado']], [$v['id'], 'aceptada']);
$r = eliminar((int)$v['id'], [], 2);
chk('desde otra empresa: "Venta no encontrada"', [$r['ok'] ?? null, $r['error'] ?? null], [false, 'Venta no encontrada']);

$r = eliminar((int)$v['id']);
chk('el admin la elimina: ok', $r['ok'] ?? $r, true);
chk('responde a qué cotización y estado regresó', [$r['data']['cotizacion_id'] ?? null, $r['data']['estado'] ?? null], [3001, 'vista']);
chk('la venta ya no existe', venta_de(3001), null);
chk('su historial de venta se fue con ella (cascada)', (int)val("SELECT COUNT(*) FROM venta_log WHERE venta_id = ?", [$v['id']]), 0);
$c = cot(3001);
chk('la cotización regresa a «Vista»: el cliente la había abierto', $c['estado'], 'vista');
chk('sin fecha de aceptación ni de acción, sin copia congelada',
    [$c['aceptada_at'], $c['accion_at'], $c['lineas_snapshot']], [null, null, null]);
chk('con las mismas visitas', [(int)$c['visitas'], $c['vista_at'], $c['ultima_vista_at']], [3, $antes['vista_at'], $antes['ultima_vista_at']]);
chk('con su historial de visitas y su seguimiento de la Mesa',
    [(int)val("SELECT COUNT(*) FROM quote_sessions WHERE cotizacion_id = 3001"), (int)val("SELECT COUNT(*) FROM mesa_estados WHERE cotizacion_id = 3001")], [2, 1]);
chk('con su historial de cambios completo (la aceptación sigue ahí) y una línea más',
    [(int)val("SELECT COUNT(*) FROM cotizacion_log WHERE cotizacion_id = 3001"), (int)val("SELECT COUNT(*) FROM cotizacion_log WHERE cotizacion_id = 3001 AND evento = 'aceptada'")],
    [$log_antes + 1, 1]);
$lr = log_regreso(3001);
chk('la línea nueva dice quién, qué venta y a qué estado regresó',
    [$lr['evento'] ?? null, (int)($lr['usuario_id'] ?? 0),
     str_contains((string)($lr['detalle'] ?? ''), 'Se eliminó la venta ' . $v['numero'] . ' (cancelada: El cliente se fue con otro proveedor)'),
     str_contains((string)($lr['detalle'] ?? ''), 'Regresó a «Vista» con las partidas que el cliente aceptó')],
    ['regresada', 7, true, true]);
chk('total recalculado: 12,180 (sin descuentos)', [$c['total'], $c['subtotal'], $c['impuesto_amt']], ['12180.00', '10500.00', '1680.00']);
chk('las partidas son las que aceptó el cliente', array_map(fn($l) => [$l['titulo'], $l['articulo_id'] === null ? null : (int)$l['articulo_id'], $l['subtotal'], (int)$l['es_extra']], lineas_de(3001)),
    [['Cocina', 55, '10000.00', 0], ['Flete', null, '500.00', 1]]);

$r = aceptar(3001);
chk('el cliente la puede volver a aceptar desde su liga', $r['ok'] ?? $r, true);
$v2 = venta_de(3001);
chk('nace una venta NUEVA, con otro folio, a 12,180',
    [$v2 !== null && (int)$v2['id'] !== (int)$v['id'], ($v2['numero'] ?? '') !== $v['numero'], $v2['total'] ?? null], [true, true, '12180.00']);
chk('la cotización vuelve a aceptada, con su copia congelada nueva', [cot(3001)['estado'], cot(3001)['lineas_snapshot'] !== null], ['aceptada', true]);

// ════════════════════════════════════════════════════════════
echo "\n── Convertida sin que el cliente la abriera: regresa a «Enviada» ──\n";
nueva_cot(3002, ['estado' => 'enviada', 'visitas' => 0, 'vista_at' => null, 'ultima_vista_at' => null]);
$r = convertir(3002);
chk('convertir (cotizaciones/convertir.php real): ok', $r['ok'] ?? $r, true);
$v = venta_de(3002);
chk('cancelar: ok', cancelar((int)$v['id'], 'Era una prueba')['ok'] ?? null, true);
$r = convertir(3002);
chk('con la venta cancelada, convertir se niega (como hoy)', $r['ok'] ?? null, false);
$r = eliminar((int)$v['id']);
chk('eliminar: ok', [$r['ok'] ?? $r, $r['data']['estado'] ?? null], [true, 'enviada']);
chk('regresa a «Enviada»: el cliente nunca la abrió', [cot(3002)['estado'], cot(3002)['vista_at'], (int)cot(3002)['visitas']], ['enviada', null, 0]);
chk('el historial lo dice', str_contains((string)(log_regreso(3002)['detalle'] ?? ''), 'Regresó a «Enviada»'));
$r = convertir(3002);
chk('y se puede volver a convertir', [$r['ok'] ?? $r, venta_de(3002) !== null, cot(3002)['estado']], [true, true, 'convertida']);

// ════════════════════════════════════════════════════════════
echo "\n── La venta se editó: regresan las partidas que aceptó el cliente ──\n";
nueva_cot(3003);
aceptar(3003);
$v = venta_de(3003);
// La pantalla de la venta guarda así: precio cambiado y una partida nueva.
// ventas/guardar.php reescribe las partidas SIN articulo_id.
$r = post('modules/ventas/guardar.php', (int)$v['id'], ['lineas' => [
        ['titulo' => 'Cocina', 'sku' => 'COC-1', 'descripcion' => 'Cambió en la venta.', 'cantidad' => 1, 'precio_unit' => 9000, 'es_extra' => 0],
        ['titulo' => 'Flete', 'sku' => '', 'descripcion' => 'Entrega en obra.', 'cantidad' => 1, 'precio_unit' => 500, 'es_extra' => 1],
        ['titulo' => 'Zoclo', 'sku' => '', 'descripcion' => 'Agregado en la venta.', 'cantidad' => 1, 'precio_unit' => 800, 'es_extra' => 0],
    ], 'descuento_auto_amt' => 0, 'descuento_auto_pct' => 0]);
chk('guardar la venta (ventas/guardar.php real): ok', $r['ok'] ?? $r, true);
chk('la cotización quedó con las partidas de la venta y sin el artículo del catálogo',
    [count(lineas_de(3003)), lineas_de(3003)[0]['articulo_id']], [3, null]);
q("INSERT INTO gastos_venta (empresa_id, venta_id, concepto, importe, fecha) VALUES (1, ?, 'Material', 2500, CURDATE()), (1, ?, 'Flete proveedor', 400, CURDATE())",
  [$v['id'], $v['id']]);
cancelar((int)$v['id'], 'Cambió de opinión');
$r = eliminar((int)$v['id']);
chk('eliminar: ok', $r['ok'] ?? $r, true);
chk('regresan las dos partidas que aceptó, con su artículo del catálogo; la de la venta se va',
    array_map(fn($l) => [$l['titulo'], $l['articulo_id'] === null ? null : (int)$l['articulo_id'], $l['precio_unit'], (int)$l['es_extra'], $l['venta_id']], lineas_de(3003)),
    [['Cocina', 55, '10000.00', 0, null], ['Flete', null, '500.00', 1, null]]);
chk('y el total vuelve a 12,180', cot(3003)['total'], '12180.00');
chk('los gastos NO se borran: quedan como gastos generales (sin venta)',
    [(int)val("SELECT COUNT(*) FROM gastos_venta WHERE concepto IN ('Material','Flete proveedor')"),
     (int)val("SELECT COUNT(*) FROM gastos_venta WHERE concepto IN ('Material','Flete proveedor') AND venta_id IS NULL"),
     (string)val("SELECT SUM(importe) FROM gastos_venta WHERE concepto IN ('Material','Flete proveedor')")],
    [2, 2, '2900.00']);
chk('el historial anota los gastos', str_contains((string)(log_regreso(3003)['detalle'] ?? ''), '2 gastos pasaron a gastos generales'));

// ════════════════════════════════════════════════════════════
echo "\n── Con Descuento Inteligente: no regresa, se vuelve a aceptar a precio lleno ──\n";
nueva_cot(3004);
// Contrato DI vigente: 10% sobre la base sin extras con IVA (11,600) → 10,440
q("INSERT INTO desc_int_activaciones (empresa_id, cotizacion_id, cliente_id, pct, precio_original, monto_desc, nuevo_total, estado, expira_at)
   VALUES (1, 3004, 1, 10, 11600, 1160, 10440, 'activo', ?)", [$futuro]);
aceptar(3004);
$v = venta_de(3004);
chk('se aceptó con el DI: 10,440 + flete 580 = 11,020; contrato utilizado',
    [$v['total'], cot(3004)['total'], (string)val("SELECT estado FROM desc_int_activaciones WHERE cotizacion_id = 3004")],
    ['11020.00', '11020.00', 'utilizado']);
cancelar((int)$v['id'], 'No pagó');
chk('cancelar libera el DI (como hoy)', (string)val("SELECT estado FROM desc_int_activaciones WHERE cotizacion_id = 3004"), 'cancelado');
$r = eliminar((int)$v['id']);
chk('eliminar: ok', $r['ok'] ?? $r, true);
chk('el total de la cotización regresa a precio lleno (12,180), no al del DI',
    [cot(3004)['total'], (string)val("SELECT estado FROM desc_int_activaciones WHERE cotizacion_id = 3004")], ['12180.00', 'cancelado']);
chk('el historial lo dice', str_contains((string)(log_regreso(3004)['detalle'] ?? ''), 'Tuvo Descuento Inteligente: no regresa'));
$r = aceptar(3004, ['cupon_codigo' => 'PROMO10']);
chk('al volver a aceptar: precio lleno; tampoco entra un cupón (regla del 16-jul)',
    [$r['ok'] ?? $r, venta_de(3004)['total'] ?? null, cot(3004)['cupon_codigo']], [true, '12180.00', null]);

echo "\n── DI que se quedó 'utilizado' (ventas canceladas antes de que cancelar lo liberara) ──\n";
nueva_cot(3005);
q("INSERT INTO desc_int_activaciones (empresa_id, cotizacion_id, cliente_id, pct, precio_original, monto_desc, nuevo_total, estado, expira_at)
   VALUES (1, 3005, 1, 10, 11600, 1160, 10440, 'activo', ?)", [$futuro]);
aceptar(3005);
$v = venta_de(3005);
cancelar((int)$v['id'], 'No pagó');
q("UPDATE desc_int_activaciones SET estado = 'utilizado' WHERE cotizacion_id = 3005");   // como quedaban antes
$r = eliminar((int)$v['id']);
chk('eliminar: ok, y el DI queda cancelado', [$r['ok'] ?? $r, (string)val("SELECT estado FROM desc_int_activaciones WHERE cotizacion_id = 3005")], [true, 'cancelado']);
aceptar(3005);
chk('sin esto, el siguiente aceptar cobraría otra vez el precio del DI; ahora cobra 12,180', venta_de(3005)['total'] ?? null, '12180.00');

// ════════════════════════════════════════════════════════════
echo "\n── Cupones ──\n";
q("INSERT INTO cupones (id, empresa_id, codigo, porcentaje, activo, usos) VALUES (91, 1, 'PROMO10', 10, 1, 5), (92, 1, 'ASESOR5', 5, 1, 0)");
nueva_cot(3006);
aceptar(3006, ['cupon_codigo' => 'PROMO10']);
chk('el cliente tecleó PROMO10 al aceptar: se guardó y sumó un uso',
    [cot(3006)['cupon_codigo'], cot(3006)['cupon_monto'], (int)val("SELECT usos FROM cupones WHERE id = 91"), venta_de(3006)['total']],
    ['PROMO10', '1000.00', 6, '11020.00']);
$v = venta_de(3006);
cancelar((int)$v['id'], 'Se arrepintió');
eliminar((int)$v['id']);
chk('al regresar, el cupón del cliente se quita y se le devuelve el uso',
    [cot(3006)['cupon_codigo'], cot(3006)['cupon_monto'], cot(3006)['cupon_id'], (int)val("SELECT usos FROM cupones WHERE id = 91")],
    [null, '0.00', null, 5]);
chk('total sin el cupón: 12,180', cot(3006)['total'], '12180.00');
chk('el historial lo dice', str_contains((string)(log_regreso(3006)['detalle'] ?? ''), 'Se quitó el cupón PROMO10 que el cliente tecleó al aceptar'));
// El del asesor: lo guardó el editor (cupon_id). (10,000 − 500 + 500) × 1.16 = 11,600
nueva_cot(3007, ['cupon_id' => 92, 'cupon_codigo' => 'ASESOR5', 'cupon_pct' => 5, 'cupon_monto' => 500, 'total' => 11600, 'impuesto_amt' => 1600]);
aceptar(3007);
$v = venta_de(3007);
cancelar((int)$v['id'], 'Se arrepintió');
eliminar((int)$v['id']);
chk('el cupón que puso el asesor se queda, con su monto y el total que haría guardar.php',
    [(int)cot(3007)['cupon_id'], cot(3007)['cupon_codigo'], cot(3007)['cupon_monto'], cot(3007)['total'], (int)val("SELECT usos FROM cupones WHERE id = 92")],
    [92, 'ASESOR5', '500.00', '11600.00', 0]);

// ════════════════════════════════════════════════════════════
echo "\n── Cronómetro (descuento automático) ──\n";
// Vigente al aceptar: 10% → (10,000 − 1,000 + 500) × 1.16 = 11,020
nueva_cot(3008, ['d_activo' => 1, 'd_pct' => 10, 'd_expira' => $futuro, 'd_amt' => 1000, 'total' => 11020, 'impuesto_amt' => 1520]);
aceptar(3008);
$v = venta_de(3008);
chk('se aceptó con el cronómetro: 11,020', $v['total'], '11020.00');
// En la venta le ponen "Agregar descuento" ($300 más): guardar.php deja pct 0
post('modules/ventas/guardar.php', (int)$v['id'], ['lineas' => [
        ['titulo' => 'Cocina', 'sku' => 'COC-1', 'descripcion' => 'x', 'cantidad' => 1, 'precio_unit' => 10000, 'es_extra' => 0],
        ['titulo' => 'Flete', 'sku' => '', 'descripcion' => 'x', 'cantidad' => 1, 'precio_unit' => 500, 'es_extra' => 1],
    ], 'descuento_auto_amt' => 1300, 'descuento_auto_pct' => 0]);
chk('la venta le cambió el cronómetro a la cotización (pct 0, $1,300)', [cot(3008)['descuento_auto_pct'], cot(3008)['descuento_auto_amt']], ['0.00', '1300.00']);
cancelar((int)$v['id'], 'No pagó');
eliminar((int)$v['id']);
$c = cot(3008);
chk('regresa con el cronómetro con que se aceptó: 10%, misma fecha, $1,000, total 11,020',
    [(int)$c['descuento_auto_activo'], $c['descuento_auto_pct'], $c['descuento_auto_expira'], $c['descuento_auto_amt'], $c['total']],
    [1, '10.00', $futuro, '1000.00', '11020.00']);

// Copias congeladas de antes del 26-nov (sin la configuración): se queda la actual
nueva_cot(3009, ['estado' => 'aceptada', 'd_activo' => 1, 'd_pct' => 10, 'd_expira' => $pasado, 'd_amt' => 0]);
q("UPDATE cotizaciones SET aceptada_at = NOW(), accion_at = NOW(), lineas_snapshot = ? WHERE id = 3009",
  [json_encode(['lineas' => $SNAP_LINEAS(3009), 'descuento_auto_amt' => 0], JSON_UNESCAPED_UNICODE)]);
venta_cancelada(709, 3009, ['notas' => "Nota.\n[Cancelada: Cliente desistió]"]);
$r = eliminar(709);
$c = cot(3009);
chk('copia vieja + cronómetro vencido: sigue marcado (indicador), sin restar; total 12,180',
    [$r['ok'] ?? $r, (int)$c['descuento_auto_activo'], $c['descuento_auto_pct'], $c['descuento_auto_amt'], $c['total']],
    [true, 1, '10.00', '0.00', '12180.00']);
chk('el motivo de una venta cancelada antes de hoy sale de las notas internas',
    str_contains((string)(log_regreso(3009)['detalle'] ?? ''), '(cancelada: Cliente desistió)'));
nueva_cot(3010, ['estado' => 'aceptada', 'd_activo' => 1, 'd_pct' => 0, 'd_amt' => 300]);
q("UPDATE cotizaciones SET lineas_snapshot = ? WHERE id = 3010",
  [json_encode(['lineas' => $SNAP_LINEAS(3010), 'descuento_auto_amt' => 0], JSON_UNESCAPED_UNICODE)]);
venta_cancelada(710, 3010);
eliminar(710);
$c = cot(3010);
chk('copia vieja + "Agregar descuento" de la venta (0 %): no pasa a la cotización',
    [(int)$c['descuento_auto_activo'], $c['descuento_auto_pct'], $c['descuento_auto_amt'], $c['descuento_auto_expira'], $c['total']],
    [0, '0.00', '0.00', null, '12180.00']);

// ════════════════════════════════════════════════════════════
echo "\n── Lo que no se puede ──\n";
nueva_cot(3011, ['estado' => 'aceptada']);
venta_cancelada(711, 3011);
q("INSERT INTO recibos (empresa_id, venta_id, numero, monto, cancelado) VALUES (1, 711, 'REC-2026-0900', 3000, 1)");
$r = eliminar(711);
chk('tuvo un pago (aunque esté cancelado): no se elimina',
    [$r['ok'] ?? null, str_starts_with((string)($r['error'] ?? ''), 'Esta venta tuvo pagos (aunque estén cancelados)')], [false, true]);
chk('y no cambió nada: la venta, su recibo y la cotización siguen',
    [(int)val("SELECT COUNT(*) FROM ventas WHERE id = 711"), (int)val("SELECT COUNT(*) FROM recibos WHERE venta_id = 711"), cot(3011)['estado']],
    [1, 1, 'aceptada']);
nueva_cot(3012, ['estado' => 'aceptada']);
venta_cancelada(712, 3012);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, numero, total, saldo, estado) VALUES (812, 1, 3012, 'VTA-2026-0812', 12180, 12180, 'pendiente')");
$r = eliminar(712);
chk('la cotización tiene otra venta viva: no', [$r['ok'] ?? null, $r['error'] ?? null], [false, 'La cotización tiene otra venta (VTA-2026-0812) que no se puede eliminar.']);
chk('y no cambió nada', [(int)val("SELECT COUNT(*) FROM ventas WHERE cotizacion_id = 3012"), cot(3012)['estado']], [2, 'aceptada']);

echo "\n── Todo o nada: si algo falla a la mitad, no se toca nada ──\n";
nueva_cot(3013, ['estado' => 'aceptada']);
q("UPDATE cotizaciones SET lineas_snapshot = ? WHERE id = 3013",
  [json_encode(['lineas' => $SNAP_LINEAS(3013), 'descuento_auto_amt' => 0], JSON_UNESCAPED_UNICODE)]);
venta_cancelada(713, 3013);
q("INSERT INTO gastos_venta (empresa_id, venta_id, concepto, importe) VALUES (1, 713, 'Gasto 713', 100)");
file_put_contents($errlog, '');
$pdo->exec("RENAME TABLE cotizacion_log TO cotizacion_log_x");   // el último paso va a fallar
$r = eliminar(713);
$pdo->exec("RENAME TABLE cotizacion_log_x TO cotizacion_log");
chk('falla el último paso → error 500, sin detalles internos', [$r['ok'] ?? null, $r['error'] ?? null], [false, 'No se pudo eliminar la venta']);
chk('y el error real queda en el log del servidor', str_contains((string)file_get_contents($errlog), '[Venta eliminar]'));
chk('la venta, su gasto y la cotización quedaron como estaban',
    [(int)val("SELECT COUNT(*) FROM ventas WHERE id = 713"), (int)val("SELECT venta_id FROM gastos_venta WHERE concepto = 'Gasto 713'"),
     cot(3013)['estado'], cot(3013)['lineas_snapshot'] !== null],
    [1, 713, 'aceptada', true]);

echo "\n── Casos de datos viejos ──\n";
nueva_cot(3014, ['estado' => 'aceptada']);          // sin copia congelada
venta_cancelada(714, 3014);
q("UPDATE cotizacion_lineas SET venta_id = 714 WHERE cotizacion_id = 3014 AND titulo = 'Flete'");
$r = eliminar(714);
chk('sin copia congelada: regresa igual, con las partidas que tenga',
    [$r['ok'] ?? $r, cot(3014)['estado'], count(lineas_de(3014)), lineas_de(3014)[1]['venta_id']], [true, 'vista', 2, null]);
chk('y el historial no promete «las partidas que el cliente aceptó»',
    str_contains((string)(log_regreso(3014)['detalle'] ?? ''), 'con las partidas'), false);
q("INSERT INTO ventas (id, empresa_id, cotizacion_id, numero, total, estado) VALUES (715, 1, NULL, 'VTA-2026-0715', 5000, 'cancelada')");
$r = eliminar(715);
chk('una venta sin cotización: se elimina y ya',
    [$r['ok'] ?? $r, array_key_exists('cotizacion_id', $r['data'] ?? []) ? $r['data']['cotizacion_id'] : 'falta', (int)val("SELECT COUNT(*) FROM ventas WHERE id = 715")],
    [true, null, 0]);

// ════════════════════════════════════════════════════════════
echo "\n── Contratos en el código ──\n";
$h  = file_get_contents(__DIR__ . '/../core/Helpers.php');
$ac = file_get_contents(__DIR__ . '/../modules/ventas/acciones.php');
$rt = file_get_contents(__DIR__ . '/../core/Router.php');
$vv = file_get_contents(__DIR__ . '/../modules/ventas/ver.php');
$cv = file_get_contents(__DIR__ . '/../modules/cotizaciones/ver.php');
$ay = file_get_contents(__DIR__ . '/../modules/ayuda/index.php');
chk('ruta POST /ventas/:id/eliminar → acciones.php, acción eliminar',
    str_contains($rt, "self::post('/ventas/:id/eliminar',            fn(\$p) => self::app_extra('ventas', 'acciones', \$p, ['accion'=>'eliminar']));"));
chk('la acción es solo del admin', str_contains($ac, "elseif (\$accion === 'eliminar') {\n    if (!Auth::es_admin()) json_error('Solo el administrador puede eliminar una venta', 403);"));
chk('cancelar llena cancelado_motivo / cancelado_por_id / cancelado_at', str_contains($ac, "cancelado_at=NOW(), cancelado_motivo=?, cancelado_por_id=?"));
chk('la regla de pagos cuenta TODOS los recibos, también los cancelados', str_contains($h, '"SELECT COUNT(*) FROM recibos WHERE venta_id = ?", [(int)$venta[\'id\']]'));
chk('los gastos se sueltan ANTES de borrar la venta (su llave borra en cascada)',
    strpos($h, 'UPDATE gastos_venta SET venta_id = NULL WHERE venta_id = ?') < strpos($h, 'DELETE FROM ventas WHERE id = ? AND empresa_id = ?'));
chk('el estado sale de vista_at/visitas', str_contains($h, "\$estado = (!empty(\$cot['vista_at']) || (int)(\$cot['visitas'] ?? 0) > 0) ? 'vista' : 'enviada';"));
chk('la venta: botón solo para el admin con la venta cancelada y sin bloqueo',
    str_contains($vv, "<?php if (\$puede_admin && \$venta['estado'] === 'cancelada'): ?>\n  <?php if (\$bloqueo_eliminar === null): ?>")
    && str_contains($vv, "onclick=\"openSheet('shEliminarVta')\">✕ Eliminar venta y regresar la cotización</button>"));
chk('la venta: si no se puede, dice por qué', str_contains($vv, '<?= e($bloqueo_eliminar) ?>'));
chk('la venta: la hoja manda a /ventas/:id/eliminar y luego a la cotización',
    str_contains($vv, "fetch('/ventas/'+VENTA_ID+'/eliminar'") && str_contains($vv, "location.href=cid?'/cotizaciones/'+cid:'/ventas'"));
chk('la venta: el aviso rojo muestra el motivo', str_contains($vv, "Venta cancelada<?= \$motivo_cancel !== '' ? ' — '.e(\$motivo_cancel) : '' ?>")
    && !str_contains($vv, "\$venta['cancelado_motivo'] ? ' — '"));
chk('la cotización: aviso de venta cancelada con el botón para el admin',
    str_contains($cv, '<?php if ($venta_cancelada): $vc_motivo = venta_motivo_cancelacion($venta_cancelada); ?>')
    && str_contains($cv, 'onclick="regresarCotizacion(<?= (int)$venta_cancelada[\'id\'] ?>)">Eliminar la venta y regresar la cotización</button>')
    && str_contains($cv, "\$puede_regresar = \$venta_cancelada && Auth::es_admin() && \$bloqueo_eliminar_vta === null;"));
chk('la cotización: el historial muestra el evento con su detalle',
    str_contains($cv, "\$ev_vta = in_array(\$ev_log, ['regresada', 'venta_eliminada'], true);") && str_contains($cv, "<?= e(\$entry['detalle']) ?>"));
chk('la Ayuda lo explica', str_contains($ay, '<b>✕ Eliminar venta y regresar la cotización</b>'));

@unlink($runner);
@unlink($errlog);
limpiar();
echo "\n" . ($fail === 0 ? "✓ SIMULACIÓN ELIMINAR VENTA OK — $ok comprobaciones contra MariaDB real\n" : "✗ $fail FALLAS ($ok bien)\n");
exit($fail === 0 ? 0 : 1);
