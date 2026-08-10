<?php
// ============================================================
//  GUARDIA DE BUCKETS CALIENTES — que no se vuelva a olvidar uno
//
//  Mesa::HOT es la FUENTE ÚNICA. Radar.php conserva sus propias copias
//  (PUSH_BUCKETS / HIGH_PRIORITY_BUCKETS) a propósito: debe poder cargarse
//  sin el autoloader de core (tools/test_radar_fix.php lo hace). Este test
//  falla si alguna copia se desincroniza, y si aparece en el código una
//  lista de buckets escrita a mano en vez de leer Mesa::HOT.
//
//  Correr: php tools/test_buckets_calientes.php   → debe terminar en OK
//  Obligatorio al AGREGAR o QUITAR un bucket caliente.
//
//  NO toca la BD.
// ============================================================
define('COTIZAAPP', 1);
define('MODULES_PATH', __DIR__ . '/../modules');

require_once __DIR__ . '/../core/Mesa.php';
require_once __DIR__ . '/../modules/radar/Radar.php';

$FAIL = 0;
function ok(string $t, bool $c, string $extra = '') {
    global $FAIL; if (!$c) $FAIL++;
    echo ($c ? "  ✓ " : "  ✗ ") . $t . ($extra ? "\n      $extra" : "") . "\n";
}
$dif = fn(array $a, array $b) => implode(', ', array_merge(array_diff($a, $b), array_diff($b, $a))) ?: '—';

$HOT = Mesa::HOT;
echo "═ FUENTE ÚNICA: Mesa::HOT (" . count($HOT) . " buckets) ═\n  " . implode(' · ', $HOT) . "\n\n";

echo "═ COPIAS DE Radar.php (viven aparte por el autoloader) ═\n";
ok('Radar::HIGH_PRIORITY_BUCKETS == Mesa::HOT',
   !array_diff($HOT, Radar::HIGH_PRIORITY_BUCKETS) && !array_diff(Radar::HIGH_PRIORITY_BUCKETS, $HOT),
   'difieren: ' . $dif($HOT, Radar::HIGH_PRIORITY_BUCKETS));
ok('claves de Radar::PUSH_BUCKETS == Mesa::HOT',
   !array_diff($HOT, array_keys(Radar::PUSH_BUCKETS)) && !array_diff(array_keys(Radar::PUSH_BUCKETS), $HOT),
   'difieren: ' . $dif($HOT, array_keys(Radar::PUSH_BUCKETS)));

echo "\n═ hot_sql() ═\n";
ok('arma un IN (...) válido', Mesa::hot_sql() === "('" . implode("','", $HOT) . "')", Mesa::hot_sql());
ok('acepta extras (Radar Health)', str_contains(Mesa::hot_sql(['decision_activa']), "'decision_activa')"));

echo "\n═ EL RADAR RENDERIZA TODOS LOS CALIENTES ═\n";
// $PRIO es el ORDEN DE DESPLIEGUE de los ~20 buckets (no la lista de calientes),
// pero si un caliente nuevo no entra ahí, no se pinta en ninguna sección.
$idx = file_get_contents($raiz_idx = dirname(__DIR__) . '/modules/radar/index.php');
$prio = [];
if (preg_match('/\$PRIO\s*=\s*\[(.*?)\];/s', $idx, $m)) {
    preg_match_all("/'([a-z_]+)'/", $m[1], $mm);
    $prio = $mm[1];
}
ok('$PRIO incluye los ' . count($HOT) . ' calientes',
   $prio && !array_diff($HOT, $prio),
   $prio ? 'faltan en $PRIO: ' . (implode(', ', array_diff($HOT, $prio)) ?: '—') : 'no se pudo leer $PRIO');

echo "\n═ NADIE MÁS ESCRIBE LA LISTA A MANO ═\n";
// Cualquier archivo que enumere 3+ buckets calientes juntos y NO lea Mesa::HOT
// es una copia nueva a punto de desincronizarse.
$raiz  = dirname(__DIR__);
$libre = [
    '/core/Mesa.php',                    // la fuente
    '/modules/radar/Radar.php',          // copias a propósito (ver arriba); ya se validan
    '/tools/test_buckets_calientes.php', // este archivo
    '/tools/factlint_tips_v2.php',       // fixture de pruebas, no lógica de producto
];
// MesaSugerencias ya NO está exento: se abrió a los 8 (decisión CEO 10-ago) y
// lee Mesa::HOT. Si alguien vuelve a escribir ahí una lista a mano, esto falla.
$rx    = '/(?:\'|")(?:probable_cierre|onfire|inminente|validando_precio|prediccion_alta|lectura_comprometida|multi_persona|alto_importe)(?:\'|")\s*,\s*(?:\'|")(?:probable_cierre|onfire|inminente|validando_precio|prediccion_alta|lectura_comprometida|multi_persona|alto_importe)(?:\'|")\s*,\s*(?:\'|")(?:probable_cierre|onfire|inminente|validando_precio|prediccion_alta|lectura_comprometida|multi_persona|alto_importe)(?:\'|")/';
$copias = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if ($f->getExtension() !== 'php') continue;
    $rel = str_replace($raiz, '', $f->getPathname());
    if (str_starts_with($rel, '/vendor/') || in_array($rel, $libre, true)) continue;
    $txt = file_get_contents($f->getPathname());
    // $PRIO no es "la lista de calientes" (es el orden de los ~20 buckets) y ya
    // tiene su propia verificación arriba — se saca del escaneo.
    $txt = preg_replace('/\$PRIO\s*=\s*\[.*?\];/s', '', $txt);
    if (preg_match($rx, $txt)) $copias[] = $rel;
}
ok('sin listas de buckets escritas a mano', !$copias, "copias sueltas: " . implode(', ', $copias));

echo "\n" . ($FAIL === 0 ? "✓ BUCKETS OK — una sola fuente" : "✗ $FAIL FALLAS") . "\n";
exit($FAIL === 0 ? 0 : 1);
