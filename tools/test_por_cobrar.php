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

echo "\n5) LAS TARJETAS DE 'ÚLTIMOS' NO IMPRIMEN SUMAS\n";
// "Las últimas 10" ES la definición de esas tarjetas, así que ahí el LIMIT es
// correcto — al revés de "Ventas con saldo". Lo que NO pueden hacer es sumar:
// el total de 10 filas de un universo mayor es la misma cifra que no es de
// nada que tenía la otra sección.
$uv = q($src, 'ultimas_ventas');
$up = q($src, 'ultimos_pagos');
chk('"Últimas ventas" existe',        $uv !== '');
chk('"Últimos pagos" existe',         $up !== '');
chk('y ahí el LIMIT sí es correcto',  (bool)preg_match('/LIMIT 10/', $uv) && (bool)preg_match('/LIMIT 10/', $up));
// El encabezado dice cuántas trae, no cuánto suman.
chk('ninguna suma en el encabezado',
    str_contains($src, '$total_ultimas') || str_contains($src, '$total_pagos_lista'), false);
chk('"Últimas ventas" dice cuántas',  str_contains($src, 'últimas <?= count($ultimas_ventas) ?>'));
chk('"Últimos pagos" dice cuántos',   str_contains($src, 'últimos <?= count($ultimos_pagos) ?>'));
// El asesor es el de la VENTA, el mismo criterio de todo el sistema. En un pago
// recibos.usuario_id es quien lo capturó (a veces administración), no de quién
// es la venta que entró.
foreach (['Últimas ventas'=>$uv, 'Últimos pagos'=>$up] as $n => $sql) {
    chk("$n: el asesor es el de la venta",
        str_contains(preg_replace('/\s+/', ' ', $sql), 'u.id = COALESCE(v.vendedor_id, v.usuario_id)'));
}
chk('los pagos cancelados no cuentan', str_contains($up, 'r.cancelado = 0'));
chk('ni las ventas canceladas',        str_contains($uv, "v.estado <> 'cancelada'"));
// El título se recorta para que la tarjeta no obligue a scrollear, pero con
// mb_strimwidth: pone el "…" SOLO si de verdad recortó. Un corte a media
// palabra ("Cocina integral Monarc") se lee como error, no como recorte.
chk('los títulos se recortan con "…"',
    substr_count($src, "mb_strimwidth"), 2);
chk('y ya no se cortan a secas',
    (bool)preg_match("/mb_substr\(\(string\)\\$uv\['titulo'\]/", $src), false);
// La forma de pago es un dato que se busca de un vistazo ("¿entró por
// transferencia o en efectivo?"), no una nota al pie.
chk('la forma de pago se lee',
    (bool)preg_match("/font:700 12px 'Inter',sans-serif;color:var\(--text\)\"><\?= e\(ucfirst/", $src));

echo "\n" . ($fail === 0
    ? "✓ POR COBRAR OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
