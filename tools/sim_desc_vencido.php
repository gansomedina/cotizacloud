<?php
// ============================================================
// SIMULACIÓN REAL — descuento automático VENCIDO contra MariaDB de verdad.
//
// El caso (9-nov-2026, COT-2026-0081 de Obregón): el descuento automático del
// 9% venció el 16-may, pero el total GUARDADO de la cotización seguía con él.
// El encabezado del enlace leía ese total ($104,340.60) mientras el Resumen y
// el aceptar cobraban el completo ($114,660). Hermosillo traía 11 abiertas
// igual, y la lista del asesor también mostraba el precio con descuento.
//
// Lo que se prueba aquí:
//   1. La cuenta de cot_corregir_desc_vencido(): el total corregido es el que
//      guardar.php guardaría con el descuento vencido (cupón guardado sobre la
//      base sin extras, extras gravables, IVA vigente de la empresa), en los
//      tres modos de IVA.
//   2. Cuándo NO corrige: vigente, sin fecha, cerrada (aceptada/convertida),
//      rechazada, descuento apagado, ya corregida (idempotente), sin partidas,
//      y la carrera con el aceptar.
//   3. Lo que NO toca: cotizacion_log (abrir no cuenta como edición en la
//      Mesa), updated_at, y el indicador de que lo tuvo (activo/pct/expira).
//   4. Contratos de los archivos: encabezado = Resumen, editor que muestra el
//      vencido pero no lo resta, lista con el ⏱ gris.
//
// La conexión usa ATTR_EMULATE_PREPARES=false y SET time_zone con el offset de
// PHP, IGUAL que core/DB.php (la función compara strtotime() contra NOW()).
// REQUISITOS (desarrollo, NUNCA producción): MariaDB local, BD simtest,
// usuario sim/sim. Crea y BORRA sus tablas (empresas, cotizaciones,
// cotizacion_lineas, cotizacion_log) — correr las simulaciones una por una.
// Correr: php tools/sim_desc_vencido.php   → debe terminar en OK
// Obligatorio tras cualquier cambio al descuento automático o a esa función.
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
            self::$pdo->prepare("SET time_zone = ?")->execute([date('P')]);
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

require __DIR__ . '/../core/Helpers.php';

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . " want=" . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n"; }
}

// ── Esquema mínimo: solo lo que la función lee y escribe ──
// updated_at con ON UPDATE CURRENT_TIMESTAMP: el caso más estricto — si la
// función no lo deja explícito, la base lo movería sola.
DB::pdo()->exec("SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS cotizacion_log, cotizacion_lineas, cotizaciones, empresas;
SET FOREIGN_KEY_CHECKS=1;
CREATE TABLE empresas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  impuesto_modo ENUM('ninguno','suma','incluido') NOT NULL DEFAULT 'ninguno',
  impuesto_pct DECIMAL(5,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE cotizaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT UNSIGNED NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'enviada',
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  cupon_monto DECIMAL(14,2) NOT NULL DEFAULT 0,
  descuento_auto_activo TINYINT(1) NOT NULL DEFAULT 0,
  descuento_auto_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
  descuento_auto_expira DATETIME NULL,
  descuento_auto_amt DECIMAL(14,2) NOT NULL DEFAULT 0,
  impuesto_amt DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE cotizacion_lineas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT UNSIGNED NOT NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  es_extra TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE cotizacion_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT UNSIGNED, usuario_id INT UNSIGNED, accion VARCHAR(40),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;");

function empresa(string $modo = 'ninguno', float $pct = 0): int {
    DB::execute("INSERT INTO empresas (impuesto_modo, impuesto_pct) VALUES (?, ?)", [$modo, $pct]);
    return (int)DB::pdo()->lastInsertId();
}
/** Cotización con su total guardado TAL COMO lo dejó guardar.php con el descuento vigente. */
function cot(int $emp, array $c, array $lineas): int {
    $c += ['estado' => 'vista', 'cupon_monto' => 0, 'activo' => 1, 'pct' => 0, 'expira' => '-1 day',
           'amt' => 0, 'imp' => 0, 'total' => 0, 'updated_at' => '2026-01-15 09:30:00'];
    $exp = $c['expira'] === null ? null : date('Y-m-d H:i:s', strtotime($c['expira']));
    DB::execute(
        "INSERT INTO cotizaciones (empresa_id, estado, subtotal, cupon_monto, descuento_auto_activo,
                                   descuento_auto_pct, descuento_auto_expira, descuento_auto_amt,
                                   impuesto_amt, total, updated_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        [$emp, $c['estado'], array_sum(array_column($lineas, 0)), $c['cupon_monto'], $c['activo'],
         $c['pct'], $exp, $c['amt'], $c['imp'], $c['total'], $c['updated_at']]);
    $id = (int)DB::pdo()->lastInsertId();
    foreach ($lineas as [$sub, $extra]) {
        DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, subtotal, es_extra) VALUES (?,?,?)", [$id, $sub, $extra]);
    }
    return $id;
}
function fila(int $id): array { return DB::row("SELECT * FROM cotizaciones WHERE id = ?", [$id]); }
function corrige(int $id): ?float { return cot_corregir_desc_vencido(fila($id)); }

echo "\n── La cuenta: lo que guardar.php guardaría con el descuento vencido ──\n";
// A) El caso real: OnTime, sin IVA, 9% sobre $114,660 → guardado $104,340.60.
$eN = empresa('ninguno');
$cA = cot($eN, ['pct' => 9, 'expira' => '2026-05-16 10:02:59', 'amt' => 10319.40, 'total' => 104340.60],
          [[114660.00, 0]]);
chk('caso real: devuelve el total completo', corrige($cA), 114660.0);
$fA = fila($cA);
chk('caso real: total guardado $114,660.00', $fA['total'], '114660.00');
chk('caso real: descuento_auto_amt queda en 0', $fA['descuento_auto_amt'], '0.00');
chk('caso real: sin IVA, impuesto 0', $fA['impuesto_amt'], '0.00');
chk('el indicador se queda: activo, 9% y la fecha en que venció',
    [$fA['descuento_auto_activo'], $fA['descuento_auto_pct'], $fA['descuento_auto_expira']],
    [1, '9.00', '2026-05-16 10:02:59']);
chk('updated_at NO se mueve (abrir no es editar)', $fA['updated_at'], '2026-01-15 09:30:00');

// B) IVA que se SUMA, con extras: el extra no se descuenta pero sí paga IVA.
//    Guardado con 10% vigente: base 10,000 −1,000 = 9,000 + extra 1,000 = 10,000 → +16% = 11,600.
$eS = empresa('suma', 16);
$cB = cot($eS, ['pct' => 10, 'amt' => 1000, 'imp' => 1600, 'total' => 11600],
          [[10000.00, 0], [1000.00, 1]]);
chk('IVA suma: 10,000 + extra 1,000 = 11,000 → +16% = 12,760', corrige($cB), 12760.0);
$fB = fila($cB);
chk('IVA suma: impuesto 1,760.00', $fB['impuesto_amt'], '1760.00');
chk('IVA suma: total 12,760.00', $fB['total'], '12760.00');

// C) IVA INCLUIDO con cupón guardado: el cupón se respeta, el vencido no.
//    Guardado con 5%: 5,000 −500 cupón = 4,500 −225 = 4,275.
$eI = empresa('incluido', 16);
$cC = cot($eI, ['cupon_monto' => 500, 'pct' => 5, 'amt' => 225, 'imp' => 589.66, 'total' => 4275],
          [[5000.00, 0]]);
chk('IVA incluido con cupón: 5,000 −500 = 4,500', corrige($cC), 4500.0);
$fC = fila($cC);
chk('IVA incluido: impuesto 620.69 (4,500 − 4,500/1.16)', $fC['impuesto_amt'], '620.69');
chk('IVA incluido: el cupón guardado no se toca', $fC['cupon_monto'], '500.00');

// D) Cupón que se come toda la base: la base no baja de 0 (igual que guardar.php).
$cD = cot($eN, ['cupon_monto' => 300, 'pct' => 5, 'amt' => 0.01, 'total' => 199.99],
          [[300.00, 0], [200.00, 1]]);
chk('base en 0 por el cupón: queda solo el extra', corrige($cD), 200.0);

echo "\n── Cuándo NO corrige ──\n";
$antes = fn(int $id) => [fila($id)['total'], fila($id)['descuento_auto_amt']];
$cV = cot($eN, ['pct' => 9, 'expira' => '+1 day', 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
chk('descuento VIGENTE: no se toca', [corrige($cV), $antes($cV)], [null, ['9100.00', '900.00']]);
$cB2 = cot($eN, ['pct' => 9, 'expira' => '+2 minutes', 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
chk('vence en 2 minutos: todavía no', corrige($cB2), null);
$cB3 = cot($eN, ['pct' => 9, 'expira' => '-2 seconds', 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
chk('venció hace 2 segundos: ya se corrige', corrige($cB3), 10000.0);
$cNul = cot($eN, ['pct' => 9, 'expira' => null, 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
chk('sin fecha de vencimiento: no se toca', [corrige($cNul), $antes($cNul)], [null, ['9100.00', '900.00']]);
foreach (['aceptada', 'convertida', 'rechazada', 'borrador'] as $est) {
    $cE = cot($eN, ['estado' => $est, 'pct' => 9, 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
    chk("estado $est: no se toca", [corrige($cE), $antes($cE)], [null, ['9100.00', '900.00']]);
}
$cEnv = cot($eN, ['estado' => 'enviada', 'pct' => 9, 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
chk('estado enviada SÍ se corrige', corrige($cEnv), 10000.0);
$cOff = cot($eN, ['activo' => 0, 'pct' => 9, 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
chk('descuento apagado: no se toca', corrige($cOff), null);
$cSinL = cot($eN, ['pct' => 9, 'amt' => 900, 'total' => 9100], []);
chk('sin partidas: no escribe un total en 0', [corrige($cSinL), $antes($cSinL)], [null, ['9100.00', '900.00']]);
$cEmp = cot(9999, ['pct' => 9, 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
chk('empresa inexistente: no truena ni escribe', [corrige($cEmp), $antes($cEmp)], [null, ['9100.00', '900.00']]);

echo "\n── Una sola vez, y sin pisar al aceptar ──\n";
chk('segunda apertura del caso real: ya no hay nada que corregir', corrige($cA), null);
chk('…y el total sigue en $114,660.00', fila($cA)['total'], '114660.00');
// Carrera: la página leyó la cotización abierta; antes de escribir, el cliente
// aceptó y el aceptar dejó su total. La corrección no lo pisa.
$cR = cot($eN, ['pct' => 9, 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
$leida = fila($cR);
DB::execute("UPDATE cotizaciones SET estado = 'aceptada', total = 10000, descuento_auto_amt = 0 WHERE id = ?", [$cR]);
chk('carrera con el aceptar: no escribe', cot_corregir_desc_vencido($leida), null);
chk('carrera: queda el total del aceptar', fila($cR)['total'], '10000.00');
// Otra carrera: mientras se abría, el cliente la RECHAZÓ. Rechazar no toca el
// descuento (amt sigue > 0), así que aquí el único candado es el estado del
// WHERE — la de arriba no lo prueba porque el aceptar deja amt en 0.
$cR2 = cot($eN, ['pct' => 9, 'amt' => 900, 'total' => 9100], [[10000.00, 0]]);
$leida2 = fila($cR2);
DB::execute("UPDATE cotizaciones SET estado = 'rechazada' WHERE id = ?", [$cR2]);
chk('carrera con el rechazar: no escribe en una rechazada', cot_corregir_desc_vencido($leida2), null);
chk('carrera: la rechazada conserva su total y su monto', $antes($cR2), ['9100.00', '900.00']);

echo "\n── Lo que NO toca ──\n";
chk('ninguna fila en cotizacion_log: abrir no cuenta como edición en la Mesa',
    (int)DB::val("SELECT COUNT(*) FROM cotizacion_log"), 0);
// Las filas de las carreras sí cambian: las movieron los UPDATE que simulan el
// aceptar y el rechazar, no la función. Todas las demás deben seguir intactas.
chk('updated_at intacto en todas las que pasaron por la función',
    (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE updated_at <> '2026-01-15 09:30:00' AND id NOT IN (?, ?)", [$cR, $cR2]), 0);
chk('…y la base SÍ lo habría movido sola (ON UPDATE): la prueba no es de adorno',
    (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE updated_at <> '2026-01-15 09:30:00' AND id IN (?, ?)", [$cR, $cR2]), 2);

echo "\n── Contratos de los archivos ──\n";
$src = fn(string $p) => (string)file_get_contents(__DIR__ . '/../' . $p);
$slug = $src('public/cotizacion.php');
$inm  = $src('public/cotizacion_inmueble.php');
$ver  = $src('modules/cotizaciones/ver.php');
$lst  = $src('modules/cotizaciones/lista.php');
$hlp  = $src('core/Helpers.php');

chk('enlace: el "Total" del encabezado usa $total_visible',
    str_contains($slug, '<div class="pill-value" id="pTot"><?= fmt_pub($total_visible) ?></div>'));
chk('enlace: ya no lee el total guardado en el encabezado',
    !str_contains($slug, '<div class="pill-value"><?= fmt_pub((float)$cot[\'total\']) ?></div>'));
chk('enlace: $total_visible = Resumen mientras no esté aceptada',
    str_contains($slug, "\$total_visible = in_array(\$cot['estado'], ['aceptada', 'convertida'], true)\n    ? (float)\$cot['total'] : (float)\$total_base;"));
chk('enlace: corrige el total guardado ANTES de calcular lo que muestra',
    ($p1 = strpos($slug, 'cot_corregir_desc_vencido($cot)')) !== false && $p1 < strpos($slug, '$total_visible = in_array('));
chk('enlace: calc() también actualiza el encabezado (cupón tecleado, reloj en cero)',
    str_contains($slug, "if (TOT_VIVO) { const pt = document.getElementById('pTot'); if (pt) pt.textContent = fmt(tot); }"));
chk('enlace: aceptada, el encabezado no se recalcula en el navegador',
    str_contains($slug, "const TOT_VIVO = <?= in_array(\$estado, ['aceptada', 'convertida'], true) ? 'false' : 'true' ?>;"));
chk('enlace: al llegar el reloj a cero se recalcula',
    str_contains($slug, "document.getElementById('adcSec').style.display='none'; calc(); return; }"));
chk('inmuebles: el precio grande también usa $total_visible',
    str_contains($inm, 'fmt_pub($total_visible)') && !str_contains($inm, "fmt_pub((float)\$cot['total'])"));

chk('editor: corrige el total guardado al abrir', str_contains($ver, 'cot_corregir_desc_vencido($cot)'));
chk('editor: el descuento solo se resta si aplica (no vencido)',
    str_contains($ver, 'const daAplica=descAutoAplica();') && !str_contains($ver, 'if(descAutoActivo&&descAutoPct>0){descAutoAmt='));
chk('editor: el vencido se sigue viendo, tachado y con "venció"',
    str_contains($ver, "'% · '+(descAutoVencio()?'venció'") && str_contains($ver, "dv.style.textDecoration='line-through'"));
chk('editor: el vencimiento se calcula con segundos del servidor, no con el reloj de la máquina',
    str_contains($ver, "'descuento_auto_seg'   => !empty(\$cot['descuento_auto_expira']) ? strtotime(\$cot['descuento_auto_expira']) - time() : null,"));

chk('lista: trae el % y la fecha de vencimiento', str_contains($lst, 'c.descuento_auto_pct, c.descuento_auto_expira,'));
chk('lista: el ⏱ morado solo si está vigente o cerró con él',
    str_contains($lst, '$tiene_desc = $da_amt > 0 && ($da_cerrada || !$da_vencio);'));
chk('lista: el ⏱ gris "venció" se pinta en las dos vistas (móvil y escritorio)',
    substr_count($lst, '<?= $da_html ?>') === 2 && str_contains($lst, "% venció</span>'"));

$fn = substr($hlp, (int)strpos($hlp, 'function cot_corregir_desc_vencido'));
$fn = substr($fn, 0, (int)strpos($fn, "\n}\n"));
chk('la función no escribe en cotizacion_log', !str_contains($fn, 'INSERT') && !str_contains($fn, 'cotizacion_log ('));
chk('la función deja updated_at como estaba', str_contains($fn, 'updated_at = updated_at'));

// Limpieza: otras pruebas (test_plan_log) crean `empresas` con IF NOT EXISTS y
// truenan si encuentran este esquema.
DB::pdo()->exec("DROP TABLE IF EXISTS cotizacion_log, cotizacion_lineas, cotizaciones, empresas");

echo "\n" . ($fail === 0 ? "✓ SIMULACIÓN DESCUENTO VENCIDO OK — $ok comprobaciones contra MariaDB real\n"
                         : "✗ $fail FALLAS de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
