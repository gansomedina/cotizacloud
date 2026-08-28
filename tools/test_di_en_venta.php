<?php
// ============================================================
// PRUEBA — EL DESCUENTO INTELIGENTE SE VE EN LA VENTA.
//
// El bug que arregla, con un caso real: COT-2026-0317 se cerró con un DI del
// 8% (−$1,352.28). El cobro fue correcto —la venta quedó en $37,051.22 en vez
// de $38,403.50— pero la URL pública que ve el CLIENTE no mostraba una sola
// línea que lo explicara. Veía un total que no cuadraba con la lista y nada le
// decía por qué.
//
// La causa: el DI vive en su propia tabla y NO escribe en
// ventas.descuento_auto_amt (decisión de diseño: "feature independiente"). Cada
// vista que muestre totales tiene que consultarla. ventas/ver.php ya lo hacía;
// public/venta.php no. Nadie lo notó porque el dinero sí estaba bien.
//
// Por eso la comprobación 1 recorre TODAS las vistas de venta: si mañana se
// agrega otra pantalla de totales y se olvida el DI, se cae aquí.
//
// Correr: php tools/test_di_en_venta.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
$raiz = dirname(__DIR__);

// Vistas que pintan los totales de una venta. Si se agrega otra, va en esta
// lista — ese es justamente el punto de la prueba.
$VISTAS = [
    'public/venta.php'        => 'la que ve el cliente',
    'modules/ventas/ver.php'  => 'la interna del asesor',
];

$ok = 0; $fail = 0;
function chk(string $t, $got, $want): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got) . " want=" . json_encode($want) . "\n"; }
}
$src = [];
foreach ($VISTAS as $f => $_) $src[$f] = (string)file_get_contents($raiz . '/' . $f);

echo "\n1) TODA VISTA DE TOTALES CONSULTA EL DI\n";
foreach ($VISTAS as $f => $quien) {
    chk("$f ($quien) consulta desc_int_activaciones",
        str_contains($src[$f], 'desc_int_activaciones'), true);
    // Solo el DI que de verdad se usó: 'activo' es uno ofrecido y no aceptado,
    // y pintarlo sería prometerle al cliente un descuento que no tiene.
    chk("$f solo cuenta el DI 'utilizado'",
        str_contains($src[$f], "estado='utilizado'"), true);
    // Aislamiento entre clientes del SaaS: la consulta va por empresa.
    chk("$f filtra por empresa",
        (bool)preg_match('/desc_int_activaciones[\s\S]{0,200}?empresa_id\s*=\s*\?/u', $src[$f]), true);
}

echo "\n2) LA LÍNEA SE PINTA EN LAS DOS VISTAS DE LA URL PÚBLICA\n";
$pub = $src['public/venta.php'];
// Pantalla e impresión son bloques distintos: el cliente se guarda el PDF, así
// que el descuento tiene que constar en los dos.
chk('en pantalla y en impresión',
    substr_count($pub, 'Descuento (<?= (float)$desc_int_act'), 2);
chk('con el porcentaje y el monto', str_contains($pub, "monto_desc"), true);
// La FECHA en que el cliente aceptó, para que el asesor sepa de cuándo viene
// ese precio. La activación no la guarda: se toma de aceptada_at, con el
// created_at de la venta como respaldo (nacen en la misma transacción).
chk('y la fecha en que el cliente lo aceptó',
    substr_count($pub, 'aceptado \' . e($di_fecha)'), 2);
chk('sale de aceptada_at, no de la activación',
    str_contains($pub, "c.aceptada_at AS cot_aceptada_at")
    && str_contains($pub, "cot_aceptada_at'] ?: \$venta['created_at']"), true);
// Antes del total, no después: un descuento listado debajo del total no explica
// nada.
chk('antes de la línea del Total',
    (bool)preg_match('/desc_int_act\[.pct.\][\s\S]{0,600}?tot-row final/u', $pub), true);

echo "\n3) LA VISTA PÚBLICA NO HABLA EN INTERNO\n";
// "Descuento Inteligente" es el nombre del motor, no algo que el cliente tenga
// por qué leer en su recibo (decisión CEO). Para él es un descuento y ya. En el
// panel del asesor sí se llama por su nombre — ahí importa distinguirlo del
// automático y del cupón.
chk('el cliente lee "Descuento", sin jerga', str_contains($pub, 'Descuento inteligente'), false);
chk('el asesor sí lo ve por su nombre',
    str_contains($src['modules/ventas/ver.php'], 'Descuento inteligente'), true);
chk('sin el enlace "quitar" (eso es del panel interno)',
    str_contains($pub, 'quitarDescInt'), false);

echo "\n4) EL DESCUENTO NO SE PUEDE RESTAR DOS VECES\n";
// guardar.php recalcula la venta partiendo del nuevo_total CONGELADO del DI —
// que ya trae el descuento aplicado. Si además alguien copiara el monto a
// ventas.descuento_auto_amt, cada edición de la venta le bajaría el total otra
// vez. Este candado lo deja escrito.
$g = (string)file_get_contents($raiz . '/modules/ventas/guardar.php');
chk('guardar.php parte del nuevo_total congelado',
    str_contains($g, 'nuevo_total') && str_contains($g, 'desc_int_activaciones'), true);
$qa = (string)file_get_contents($raiz . '/api/quote_action.php');
chk('al aceptar, el DI no se copia a descuento_auto_amt',
    (bool)preg_match('/\$desc_auto_srv\s*=\s*[^;]*di_vig/u', $qa), false);

echo "\n" . ($fail === 0
    ? "✓ DI EN VENTA OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
