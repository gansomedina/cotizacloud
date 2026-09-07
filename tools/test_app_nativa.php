<?php
// ============================================================
// PRUEBA — LA APP ES PARA USAR; EL DINERO VIVE EN LA WEB.
//
// Regla del CEO, estilo Netflix: dentro de la app el asesor SÍ puede ver que
// existen planes superiores, pero SIN precios, SIN botones de compra y
// SIEMPRE mencionando que se contratan en cotiza.cloud desde el navegador.
//
// POR QUÉ EXISTE ESTA PRUEBA. La regla se implementó el 1 de abril
// (commit 69bedb0) con `str_contains($_SERVER['HTTP_USER_AGENT'], 'CotizaCloud')`
// repetido en seis archivos. Esa comprobación NUNCA fue verdadera: el WKWebView
// de iOS no pone el nombre de la app en el User-Agent y `appendUserAgent` jamás
// se configuró. Verificado contra producción: cero sesiones con esa palabra en
// 30 días.
//
// Y como el efecto era OCULTAR, el fallo fue invisible durante meses: la app
// publicada siguió mostrando precios sin error, sin log y sin que nadie lo
// notara. Peor: los tres puntos escritos DESPUÉS (dashboard con otro nombre de
// variable, bienvenida, ticket) ni siquiera intentaron comprobarlo.
//
// Por eso esta prueba NO comprueba que "funcione": comprueba que exista UNA
// SOLA PUERTA y que nadie abra un décimo punto sin pasar por ella.
//
// Correr: php tools/test_app_nativa.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . " want=" . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n"; }
}
function src(string $f): string { return (string)file_get_contents(__DIR__ . '/../' . $f); }

$help  = src('core/Helpers.php');
$auth  = src('core/Auth.php');
$lay   = src('core/layout.php');
$dash  = src('modules/dashboard/index.php');
$ayuda = src('modules/ayuda/index.php');
$lic   = src('modules/ayuda/licencia.php');
$tick  = src('modules/ayuda/ticket.php');
$bien  = src('modules/auth/bienvenida.php');
$nueva = src('modules/cotizaciones/nueva.php');

echo "\n1) UNA SOLA PUERTA\n";
chk('existe es_app_nativa()',      str_contains($help, 'function es_app_nativa()'));
// Tres fuentes: la cookie del login (sin parpadeo), la del JS (sesiones viejas)
// y el User-Agent (para cuando appendUserAgent entre en un build futuro).
chk('lee la cookie del login',     str_contains($help, "\$_COOKIE['cz_app']"));
chk('y también el User-Agent',     str_contains($help, "'CotizaCloud'"));
// NADIE puede volver a comprobarlo por su cuenta: así se llegó a seis copias,
// una de ellas con otro nombre de variable que se escapó de la primera revisión.
foreach (['core/layout.php','modules/dashboard/index.php','modules/ayuda/index.php',
          'modules/ayuda/licencia.php','modules/auth/registro.php',
          'modules/cotizaciones/nueva.php'] as $f) {
    chk("$f no comprueba el UA por su cuenta",
        (bool)preg_match("/HTTP_USER_AGENT.{0,40}CotizaCloud/", src($f)), false);
}

echo "\n2) LA MARCA SE PONE DONDE SÍ SE SABE\n";
// El formulario de login manda is_app=1 (login.php:353). Ese es el ÚNICO
// momento en que el servidor lo sabe con certeza y ANTES de pintar nada.
chk('el login pone la cookie',       str_contains($auth, "setcookie('cz_app'"));
chk('solo cuando es la app',         (bool)preg_match('/if \(\$is_app\) \{\s*setcookie\(\x27cz_app\x27/', $auth));
chk('y vale en ese mismo request',   str_contains($auth, "\$_COOKIE['cz_app'] = '1'"));
// El JS es respaldo para quien YA tenía sesión abierta antes del arreglo.
chk('el JS la repone como respaldo', str_contains($lay, "cz_app=1"));
chk('y recarga una sola vez',        str_contains($lay, 'location.reload()'));

echo "\n3) NINGÚN PUNTO DE COMPRA SIN PUERTA\n";
// Los nueve lugares que llevan a comprar. Los tres últimos NUNCA tuvieron nada.
$puntos = [
    'sidebar "Mejorar plan"'        => [$lay,   '$is_native_app'],
    'banners de trial'              => [$lay,   '$is_native_app'],
    'banner del dashboard'          => [$dash,  '$dash_native_app'],
    'tarjeta de plan del dashboard' => [$dash,  '$dash_native_app'],
    'sección de Ayuda'              => [$ayuda, '$is_native_app_ayuda'],
    'página de precios'             => [$lic,   'es_app_nativa()'],
    'límite en cotización nueva'    => [$nueva, 'es_app_nativa()'],
    'fin del alta (bienvenida)'     => [$bien,  'es_app_nativa()'],
    'redirect tras ticket'          => [$tick,  'es_app_nativa()'],
];
foreach ($puntos as $n => [$s, $marca]) chk("$n está gateado", str_contains($s, $marca));

echo "\n4) EN LA APP: SE VEN LOS PLANES, NO LOS PRECIOS\n";
// El asesor debe poder enterarse de qué se está perdiendo — lo que no puede es
// ver tarifas ni comprar. Antes la sección desaparecía entera.
chk('la app tiene su propia sección de planes', str_contains($ayuda, '<h2 class="ay-h2">Planes</h2>'));
chk('nombra los tres planes',
    str_contains($ayuda, "['Lite',") && str_contains($ayuda, "['Pro',") && str_contains($ayuda, "['Business',"));
chk('y marca cuál es el suyo',        str_contains($ayuda, 'tu plan actual'));
// La parte con precios sigue existiendo, pero solo del lado del navegador.
chk('los precios quedan del otro lado',
    strpos($ayuda, 'MercadoPago::precios()') < strpos($ayuda, '<?php else: /* APP NATIVA'));
// Y siempre se dice DÓNDE se contratan.
foreach (['Ayuda'=>$ayuda, 'cotización nueva'=>$nueva] as $n => $s) {
    chk("$n menciona cotiza.cloud", str_contains($s, 'cotiza.cloud'));
}
// La página de precios ya no rebota al dashboard: eso dejaba al asesor sin
// entender qué pasó. Va a la sección que sí le explica.
chk('la página de precios lleva a los planes', str_contains($lic, "/ayuda#sec-licencia"));

echo "\n5) EL ESCUDO NO SE TOCA — Y DEJA DE FALLAR EN SILENCIO\n";
// Es lo más importante de la app. NO depende de es_app_nativa(): lo muestra el
// JS con window.Capacitor, y su bloque vive FUERA del if de los banners de
// trial. Si alguien lo mete dentro, se apaga en la app sin avisar.
chk('el banner no depende de la puerta de pagos',
    (bool)preg_match('/escudo-radar-banner[^\n]*\n(?:(?!is_native_app).)*$/s', $lay) ||
    strpos($lay, 'escudo-radar-banner') > strpos($lay, 'skip_escudo'), true);
chk('lo muestra window.Capacitor',   str_contains($lay, "escudo_radar_active"));
// Sin cz_vid el href salía vacío y "Activar" solo recargaba: el asesor tocaba,
// no pasaba nada, y se quedaba sin Escudo sin saber por qué.
chk('sin cz_vid el banner no se ofrece',
    str_contains($lay, "escudo-radar-banner-sin-vid"));
// El goto tiene que seguir saltando DESPUÉS del contenido: cuando saltaba antes
// del echo, la app quedaba en blanco para la cuenta de revisión de Apple.
chk('el goto no se come el contenido',
    strpos($lay, 'skip_escudo:') < strpos($lay, 'if (isset($content)) echo $content;'));

echo "\n" . ($fail === 0
    ? "✓ APP NATIVA OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
