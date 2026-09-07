<?php
// ============================================================
// PRUEBA — EL PROYECTO ANDROID TIENE SUS RECURSOS.
//
// El proyecto Android, como estaba commiteado, NO compilaba: styles.xml
// referenciaba colorPrimary, colorPrimaryDark y colorAccent, y el único
// archivo de color era ic_launcher_background.xml. Con AppTheme aplicado en el
// AndroidManifest, aapt falla al no encontrarlos.
//
// Nadie lo notó porque nadie había compilado Android — la carpeta llevaba
// meses ahí sin tocarse. Esta prueba lo detecta sin necesidad del SDK: cada
// @color/x y @string/x citado en res/ tiene que estar definido.
//
// Correr: php tools/test_android_build.php   → debe terminar en OK
// No necesita base de datos ni Android SDK.
// ============================================================
$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . "\n"; }
}
$res = __DIR__ . '/../android/app/src/main/res';
$man = (string)file_get_contents(__DIR__ . '/../android/app/src/main/AndroidManifest.xml');

/** Junta todos los xml de res/ */
$xml = '';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($res));
foreach ($it as $f) if ($f->isFile() && $f->getExtension() === 'xml') $xml .= file_get_contents($f->getPathname());
$todo = $xml . $man;

echo "\n1) NINGÚN RECURSO CITADO SIN DEFINIR\n";
foreach ([['color', 'color'], ['string', 'string']] as [$tipo, $tag]) {
    preg_match_all('/@' . $tipo . '\/([A-Za-z0-9_]+)/', $todo, $r);
    preg_match_all('/<' . $tag . ' name="([A-Za-z0-9_]+)"/', $xml, $d);
    $falta = array_values(array_unique(array_diff($r[1], $d[1])));
    chk("todos los @$tipo/ existen" . ($falta ? ' — faltan: ' . implode(', ', $falta) : ''), $falta, []);
}

echo "\n2) EL TEMA DE LA APP\n";
// El verde de marca es el mismo del splash y la barra de estado
// (capacitor.config.ts) para que no cambie de color al arrancar.
$cfg = (string)file_get_contents(__DIR__ . '/../capacitor.config.ts');
preg_match('/<color name="colorPrimary">([^<]+)</', $xml, $m);
chk('colorPrimary es el verde de la marca',
    strtolower(trim($m[1] ?? '')), strtolower(str_contains($cfg, '#1a5c38') ? '#1a5c38' : 'sin-marca'));

echo "\n3) LO QUE EL MANIFEST NECESITA\n";
chk('la app declara INTERNET',      str_contains($man, 'android.permission.INTERNET'));
chk('MainActivity está declarada',  str_contains($man, '.MainActivity'));
chk('y el archivo existe',          is_file(__DIR__ . '/../android/app/src/main/java/com/cotizacloud/app/MainActivity.java'));

echo "\n" . ($fail === 0
    ? "✓ ANDROID RECURSOS OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
