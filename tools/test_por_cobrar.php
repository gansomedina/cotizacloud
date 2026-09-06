<?php
// ============================================================
// PRUEBA — "POR COBRAR" DICE LO MISMO EN TODA LA PANTALLA.
//
// El reporte ejecutivo mostraba DOS cifras distintas del mismo dato, una
// debajo de la otra: el KPI "Por cobrar" decía 2.3M y la sección "Ventas con
// saldo" decía 1.7M. Ninguna estaba mal calculada — estaban calculadas
// distinto:
//
//   · El KPI sumaba SUM(saldo) de TODAS las ventas pendientes/parciales.
//   · La sección traía `ORDER BY created_at ASC LIMIT 30` y sumaba en PHP el
//     saldo de esas 30 filas, pero el encabezado lo presentaba como total.
//     Los 600 mil de diferencia eran las ventas que no cupieron en el corte.
//   · Y el KPI no filtraba `saldo > 0`, así que un sobrepago (saldo negativo,
//     que abono.php permite) se le restaba.
//
// Un tope que recorta la lista Y el total la vuelve una cifra que no es de
// nada. Esta prueba impide que vuelvan a separarse.
//
// Correr: php tools/test_por_cobrar.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . " want=" . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n"; }
}
$src = (string)file_get_contents(__DIR__ . '/../modules/superadmin/executive.php');

/** Extrae el cuerpo de una consulta por el nombre de la variable que la recibe. */
function q(string $src, string $var): string {
    preg_match('/\$' . preg_quote($var, '/') . '\s*=\s*(?:\(float\))?\s*DB::(?:val|query|row)\(\s*"(.*?)"\s*\)/s', $src, $m);
    return $m[1] ?? '';
}
$kpi   = q($src, 'por_cobrar');           // la tarjeta de arriba
$lista = q($src, 'sin_pagos');            // "Ventas con saldo"
$tab   = q($src, 'ventas_por_cobrar');    // la pestaña "Por cobrar"

echo "\n1) LAS TRES CONSULTAS EXISTEN\n";
foreach (['KPI'=>$kpi, 'Ventas con saldo'=>$lista, 'pestaña Por cobrar'=>$tab] as $n => $sql) {
    chk("se encontró la consulta de $n", $sql !== '');
}

echo "\n2) NINGUNA LISTA SE RECORTA\n";
// Si una lista tiene tope, su suma deja de ser el total y contradice al KPI —
// que es exactamente lo que pasaba. La tabla ya tiene scroll propio.
chk('"Ventas con saldo" trae todas',  (bool)preg_match('/\bLIMIT\b/i', $lista), false);
chk('la pestaña "Por cobrar" también',(bool)preg_match('/\bLIMIT\b/i', $tab),   false);

echo "\n3) LAS TRES CUENTAN LO MISMO\n";
// Misma definición o los números vuelven a separarse, aunque ya no haya tope.
foreach (['KPI'=>$kpi, 'Ventas con saldo'=>$lista, 'pestaña Por cobrar'=>$tab] as $n => $sql) {
    chk("$n: solo pendiente y parcial", str_contains(preg_replace('/\s+/', ' ', $sql), "estado IN ('pendiente','parcial')"));
    chk("$n: solo saldo positivo",      (bool)preg_match('/saldo\s*>\s*0/', $sql));
}
// Un sobrepago (saldo negativo) no es dinero que te deban: es dinero que TÚ
// debes. Netearlo contra la deuda de otros clientes hace ver por cobrar menos
// de lo que hay.
chk('el KPI explica por qué excluye el sobrepago',
    str_contains($src, 'un saldo') && str_contains($src, 'sobrepago'));

echo "\n4) EL ENCABEZADO NO PUEDE MENTIR SOLO\n";
// Los encabezados suman en PHP lo que trajo la consulta. Mientras la consulta
// no tenga tope, esa suma ES el total; si alguien vuelve a poner LIMIT, el
// bloque 2 falla antes de que el número mienta en pantalla.
chk('"Ventas con saldo" suma lo que trajo',
    str_contains($src, 'foreach ($sin_pagos as $sp) $total_sin_cobrar += (float)$sp[\'saldo\'];'));
chk('la pestaña suma lo que trajo',
    str_contains($src, 'foreach ($ventas_por_cobrar as $vpc) $total_por_cobrar_lista += (float)$vpc[\'saldo\'];'));

echo "\n" . ($fail === 0
    ? "✓ POR COBRAR OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
