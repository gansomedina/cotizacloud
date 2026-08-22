<?php
// ============================================================
// PRUEBA — EL EDITOR DE COTIZACIÓN NUEVA SIRVE EN EL TELÉFONO.
//
// El bug que arregla: el selector de asesor vivía SOLO en el panel de
// escritorio, y ese panel se oculta entero en ≤820px. En el teléfono no había
// forma de elegir asesor — y con 2+ asesores el guardado EXIGE la elección, así
// que crear una cotización desde el celular quedaba bloqueado. La única salida
// era girar el teléfono a horizontal.
//
// La comprobación que de verdad importa es la 3: cada campo que el guardado lee
// con getVal(desk, mob) tiene que existir DE LOS DOS LADOS. Si mañana alguien
// agrega un campo nuevo solo al panel de escritorio, esto lo caza — no hace
// falta que se acuerde de este caso.
//
// Correr: php tools/test_nueva_movil.php   → debe terminar en OK
// No necesita base de datos ni servidor: lee el archivo.
// ============================================================
$src = (string)file_get_contents(__DIR__ . '/../modules/cotizaciones/nueva.php');

$ok = 0; $fail = 0;
function chk(string $t, $got, $want): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got) . " want=" . json_encode($want) . "\n"; }
}

echo "\n1) LA PREMISA: el panel de escritorio SÍ se oculta en el teléfono\n";
// Si esto dejara de ser cierto, el resto de la prueba deja de tener sentido —
// mejor enterarse que seguir comprobando algo que ya no aplica.
chk('hay un @media que esconde .col-panel',
    (bool)preg_match('/@media\s*\(\s*max-width\s*:\s*\d+px\s*\)\s*\{[^{}]*\.col-panel\s*\{[^}]*display\s*:\s*none/s', $src), true);

echo "\n2) EL ASESOR SE PUEDE ELEGIR DESDE EL TELÉFONO\n";
chk('existe el selector móvil',              str_contains($src, 'id="cot-vendedor-mob"'), true);
// Y tiene que estar FUERA del panel que se oculta, si no da igual que exista.
$ini_panel = strpos($src, '<div class="col-panel">');
$pos_mob   = strpos($src, 'id="cot-vendedor-mob"');
chk('y vive ANTES del panel de escritorio (o sea, fuera de él)',
    $pos_mob !== false && $ini_panel !== false && $pos_mob < $ini_panel, true);
chk('está dentro del panel móvil',
    $pos_mob > strpos($src, 'class="mobile-panel"'), true);
chk('abierto por default (es obligatorio, no un extra)',
    (bool)preg_match('/mob-section open[\s\S]{0,400}cot-vendedor-mob/u', $src), true);

echo "\n3) NINGÚN CAMPO DEL GUARDADO EXISTE DE UN SOLO LADO\n";
// Se recorren TODAS las parejas que lee guardarCotizacion() y se exige que los
// dos ids estén en el marcado. Aquí es donde se caza el próximo campo huérfano.
preg_match_all("/get(?:Val|Checked)\(\s*'([^']+)'\s*,\s*'([^']+)'\s*\)/", $src, $m, PREG_SET_ORDER);
chk('hay parejas que revisar', count($m) > 0, true);
foreach ($m as $par) {
    [$_, $desk, $mob] = $par;
    chk("  $desk / $mob — los dos existen en el marcado",
        str_contains($src, 'id="' . $desk . '"') && str_contains($src, 'id="' . $mob . '"'), true);
}

echo "\n4) EL GUARDADO NO SE QUEDA CIEGO NI SE TRABA\n";
chk('lee el asesor de los dos lados',
    str_contains($src, "getVal('cot-vendedor', 'cot-vendedor-mob')"), true);
chk('manda al servidor ese valor, no el del panel oculto',
    str_contains($src, 'vendedor_id:           vendedorVal'), true);
chk('el aviso enfoca el selector VISIBLE',
    str_contains($src, 'vendedorVisible()') && str_contains($src, 'offsetParent'), true);

echo "\n5) LOS DOS SELECTORES NO SE DESINCRONIZAN\n";
chk('los dos avisan al cambiar',
    substr_count($src, 'onchange="syncVendedor('), 2);
chk('existe syncVendedor',                   str_contains($src, 'function syncVendedor'), true);
// Las <option> se arman una vez: duplicarlas a mano es cómo un selector se
// queda sin un asesor nuevo y nadie se entera.
chk('las opciones salen de una sola fuente', substr_count($src, '<?= $vend_opts'), 2);

echo "\n" . ($fail === 0
    ? "✓ NUEVA MÓVIL OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
