<?php
// ============================================================
// PRUEBA — FORMA DE PAGO EN LOS RECIBOS (recibo_forma en Helpers.php).
//
// Por qué existe: la forma de pago vive en recibos.forma_pago, pero las
// pantallas imprimían las NOTAS del pago como si fueran la forma. Como
// ventas/abono.php:70 solo pone la forma dentro de las notas fuera de efectivo,
// el recibo impreso salía así:
//   tarjeta              → "Forma: Tarjeta"            (bien, por casualidad)
//   efectivo             → sin renglón de forma
//   efectivo con nota    → "Forma: Caja 2"             (la nota en lugar de la forma)
//   transferencia + ref  → "Forma: Transferencia · SPEI 88213"
//
// Regla del CEO (25-nov-2026): un pago en efectivo dice "Efectivo", sin la nota.
// Fuera de efectivo, la referencia va aparte y sin repetir la forma.
//
// Correr: php tools/test_recibo_forma.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);

// Helpers.php solo necesita estos nombres para poder cargarse.
class DB {
    public static function val($s, $p = []) { return null; }
    public static function row($s, $p = []) { return null; }
    public static function query($s, $p = []) { return []; }
    public static function execute($s, $p = []) { return 0; }
}
class Auth { public static function id() { return null; } }
class Radar {}

require __DIR__ . '/../core/Helpers.php';

$ok = 0; $fail = 0;
function chk(string $t, $got, $want): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . " want=" . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n"; }
}

// Copia literal de cómo arma las notas ventas/abono.php:70. La sección 5
// verifica que abono.php siga igual: si cambia, esta prueba tiene que cambiar.
function notas_abono(string $forma_pago, string $referencia): ?string {
    $notas_rec = trim(($forma_pago !== 'efectivo' ? ucfirst($forma_pago) . ($referencia ? ' · '.$referencia : '') : ($referencia ?: '')));
    return $notas_rec ?: null;
}
function rec(string $forma, string $ref): array {
    return ['forma_pago' => $forma, 'notas' => notas_abono($forma, $ref)];
}

echo "\n1) LO QUE GUARDA abono.php\n";
chk('efectivo',                          recibo_forma(rec('efectivo', '')),            ['efectivo', 'Efectivo', '']);
chk('efectivo con nota → solo Efectivo', recibo_forma(rec('efectivo', 'Caja 2')),      ['efectivo', 'Efectivo', '']);
chk('tarjeta',                           recibo_forma(rec('tarjeta', '')),             ['tarjeta', 'Tarjeta', '']);
chk('tarjeta con referencia',            recibo_forma(rec('tarjeta', '1234')),         ['tarjeta', 'Tarjeta', '1234']);
chk('transferencia',                     recibo_forma(rec('transferencia', '')),       ['transferencia', 'Transferencia bancaria', '']);
chk('transferencia con referencia',      recibo_forma(rec('transferencia', 'SPEI 88213')), ['transferencia', 'Transferencia bancaria', 'SPEI 88213']);
// La referencia puede traer su propio "·": solo se quita la forma de adelante.
chk('referencia con · adentro',          recibo_forma(rec('tarjeta', 'Visa · 1234')),  ['tarjeta', 'Tarjeta', 'Visa · 1234']);
// Que la referencia empiece con la misma palabra no la mutila.
chk('referencia que empieza igual',      recibo_forma(rec('tarjeta', 'Tarjetas Banorte')), ['tarjeta', 'Tarjeta', 'Tarjetas Banorte']);

echo "\n2) EN EFECTIVO NUNCA SALE LA NOTA\n";
foreach (['Caja 2', 'Pagó su esposa', 'Transferencia · 123', 'Tarjeta', '  recibió Manuel  ', "<b>x</b>"] as $nota) {
    chk("efectivo + '" . trim($nota) . "' → Efectivo, sin referencia",
        recibo_forma(['forma_pago' => 'efectivo', 'notas' => $nota]), ['efectivo', 'Efectivo', '']);
}

echo "\n3) RECIBOS QUE NO PASARON POR abono.php\n";
// Sin forma guardada: se deduce de las notas solo si empiezan con una forma.
chk('sin forma, notas "Tarjeta · 99"',    recibo_forma(['forma_pago' => null, 'notas' => 'Tarjeta · 99']), ['tarjeta', 'Tarjeta', '99']);
chk('sin forma, notas "Transferencia"',   recibo_forma(['forma_pago' => '',   'notas' => 'Transferencia']), ['transferencia', 'Transferencia bancaria', '']);
chk('sin forma, notas "Efectivo"',        recibo_forma(['forma_pago' => null, 'notas' => 'Efectivo']), ['efectivo', 'Efectivo', '']);
// Si no se puede deducir, NO se inventa la forma: la nota queda como referencia.
chk('sin forma, notas "Caja 2"',          recibo_forma(['forma_pago' => null, 'notas' => 'Caja 2']), ['', '', 'Caja 2']);
chk('sin forma y sin notas',              recibo_forma(['forma_pago' => null, 'notas' => null]), ['', '', '']);
chk('sin columnas',                       recibo_forma([]), ['', '', '']);
chk('forma con mayúscula',                recibo_forma(['forma_pago' => 'Tarjeta', 'notas' => 'Tarjeta']), ['tarjeta', 'Tarjeta', '']);
chk('forma con espacios',                 recibo_forma(['forma_pago' => ' efectivo ', 'notas' => 'Caja 2']), ['efectivo', 'Efectivo', '']);
chk('notas en minúsculas',                recibo_forma(['forma_pago' => 'tarjeta', 'notas' => 'tarjeta · 55']), ['tarjeta', 'Tarjeta', '55']);
// Forma que no es de las tres: se muestra tal cual está guardada.
chk('forma desconocida se respeta',       recibo_forma(['forma_pago' => 'cheque', 'notas' => 'Banamex 0042']), ['cheque', 'Cheque', 'Banamex 0042']);
// abono.php corta la referencia con substr() en bytes: puede quedar UTF-8 roto
// al final. preg_replace con /u devuelve null ahí; la nota no debe desaparecer.
$roto = recibo_forma(['forma_pago' => 'transferencia', 'notas' => "Transferencia · Depósito Bancomer \xC3"]);
chk('UTF-8 roto: la forma sale bien',     [$roto[0], $roto[1]], ['transferencia', 'Transferencia bancaria']);
chk('UTF-8 roto: la nota no se pierde',   $roto[2] !== '', true);

echo "\n4) LAS PANTALLAS USAN LA FUNCIÓN, NO LAS NOTAS\n";
$ver    = file_get_contents(__DIR__ . '/../modules/ventas/ver.php');
$venta  = file_get_contents(__DIR__ . '/../public/venta.php');
$recibo = file_get_contents(__DIR__ . '/../public/recibo.php');
$abono  = file_get_contents(__DIR__ . '/../modules/ventas/abono.php');

// Editor de la venta
chk('ver: historial usa recibo_forma',          str_contains($ver, 'recibo_forma($ab)'), true);
chk('ver: ícono por forma, no por concepto',    str_contains($ver, 'icono_forma($ab_fp)') && str_contains($ver, 'bg_forma($ab_fp)'), true);
chk('ver: ya no pinta íconos con el concepto',  preg_match('/(icono_forma|bg_forma)\(\$ab\[.concepto.\]/', $ver), 0);
chk('ver: el recibo recibe forma y ref',        str_contains($ver, "'forma'    => \$ab_forma") && str_contains($ver, "'ref'      => \$ab_ref"), true);
chk('ver: el recibo ya no recibe las notas',    str_contains($ver, "'notas'    => \$ab['notas']"), false);
chk('ver: plantilla "Forma de pago"',           str_contains($ver, "<tr><td>Forma de pago</td><td>'+escHtml(d.forma)"), true);
chk('ver: plantilla "Referencia"',              str_contains($ver, "<tr><td>Referencia</td><td>'+escHtml(d.ref)"), true);
chk('ver: plantilla ya no imprime d.notas',     str_contains($ver, 'd.notas'), false);
// Encabezado: el punto solo va entre dos datos (con ciudad vacía salía "· 6442400993")
chk('ver: encabezado sin punto suelto',         str_contains($ver, "[d.ciudad, d.tel].map(s => String(s || '').trim()).filter(Boolean).map(escHtml).join(' · ')"), true);
chk('ver: impresión de la venta usa la función', substr_count($ver, '= recibo_forma($r)'), 2); // fac-pago-sub + estado de cuenta
chk('ver: ningún notas crudo en pagos',         preg_match('/e\(\$(ab|r)\[.notas.\]\)/', $ver), 0);

// Liga de la venta del cliente
chk('venta: usa recibo_forma',                  substr_count($venta, 'recibo_forma($r)'), 2); // historial + impresión
chk('venta: ya no deduce la forma de las notas', str_contains($venta, "explode(' · ', \$notas_r"), false);
chk('venta: historial escapado',                str_contains($venta, '<div class="abo-forma"><?= e($forma_linea) ?></div>'), true);
chk('venta: nada imprime $notas_r sin escapar', str_contains($venta, '<?= $notas_r'), false);
chk('venta: ventana recibe la etiqueta',        str_contains($venta, "'forma'     => \$forma_lbl ?: '—'"), true);
chk('venta: encabezado de la ventana sin punto suelto',
    str_contains($venta, "<div class=\"rec-emp-sub\"><?= e(implode(' · ', array_filter([trim((string)(\$venta['emp_ciudad'] ?? '')), trim((string)(\$venta['emp_tel'] ?? ''))], 'strlen'))) ?></div>"), true);

// Recibo /r/
chk('recibo: usa recibo_forma',                 str_contains($recibo, 'recibo_forma($rec)'), true);
chk('recibo: Referencia sin la forma repetida', str_contains($recibo, '<?php if ($forma_ref !== \'\'): ?>') && str_contains($recibo, '<?= e($forma_ref) ?>'), true);
chk('recibo: ya no imprime las notas crudas',   str_contains($recibo, "e(\$rec['notas'])"), false);
chk('recibo: forma escapada',                   str_contains($recibo, "<?= e(\$forma_lbl ?: '—') ?>"), true);

echo "\n5) abono.php SIGUE ARMANDO LAS NOTAS IGUAL\n";
// recibo_forma depende de este formato. Si abono.php cambia, revisar la función.
chk('abono.php:70 sin cambios', str_contains($abono,
    "\$notas_rec = trim((\$forma_pago !== 'efectivo' ? ucfirst(\$forma_pago) . (\$referencia ? ' · '.\$referencia : '') : (\$referencia ?: '')));"), true);
chk('abono.php solo acepta las tres formas', str_contains($abono, "\$formas_validas = ['efectivo', 'transferencia', 'tarjeta'];"), true);

echo "\n" . ($fail === 0
    ? "✓ RECIBO FORMA OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
