<?php
// ============================================================
// PRUEBA — EL TOKEN CSRF SOBREVIVE A LA LIMPIEZA DE SESIONES DE PHP.
//
// El bug que arregla, medido en producción:
//   session.gc_maxlifetime = 1440 (24 min) en /etc/php/8.3/fpm/php.ini, y
//   phpsessionclean.timer borrando /var/lib/php/sessions cada 30 min. El token
//   vivía SOLO en $_SESSION, o sea en uno de esos archivos. La sesión real dura
//   14 días, pero dejar una pantalla abierta media hora y darle a un botón
//   fallaba con "Sesión expirada" — sin estar deslogueado.
//
// La prueba que importa es la 1: vacía $_SESSION a mitad de camino (justo lo
// que hace el limpiador) y exige que el token siga siendo el mismo.
//
// Correr: php tools/test_csrf.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);
define('SESSION_NAME',     'cza_session');
define('CSRF_TOKEN_NAME',  '_token');
define('APP_SECRET',       'secreto-de-prueba-32-caracteres!');

require __DIR__ . '/../core/Helpers.php';

$ok = 0; $fail = 0;
function chk(string $t, $got, $want): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got) . " want=" . json_encode($want) . "\n"; }
}
/** Lo que hace phpsessionclean: el archivo desaparece y $_SESSION nace vacío. */
function limpiador_de_php(): void { $_SESSION = []; }

$_SESSION = [];
$_POST    = [];
$_COOKIE  = [];

echo "\n1) CON SESIÓN — el token aguanta la limpieza (EL BUG)\n";
$_COOKIE[SESSION_NAME] = 'token-de-sesion-abc123';
$t1 = csrf_token();
limpiador_de_php();
$t2 = csrf_token();
chk('mismo token después de que PHP borró la sesión', $t2, $t1);
chk('y no se guardó nada en $_SESSION',               isset($_SESSION[CSRF_TOKEN_NAME]), false);

echo "\n2) ESTÁ FIRMADO DE VERDAD\n";
chk('es el HMAC del token de sesión con APP_SECRET',
    $t1, hash_hmac('sha256', 'csrf|token-de-sesion-abc123', APP_SECRET));
chk('sha256 en hex (64 chars)',                       strlen($t1), 64);
chk('NO revela el token de sesión',
    str_contains($t1, 'token-de-sesion-abc123'),      false);

echo "\n3) CADA SESIÓN, SU TOKEN\n";
$_COOKIE[SESSION_NAME] = 'otra-sesion-xyz789';
$t3 = csrf_token();
chk('otra sesión → otro token',                       $t3 === $t1, false);
// Si el ancla desaparece (logout) el token deja de ser válido: es lo correcto.
$_COOKIE = [];
limpiador_de_php();
chk('sin sesión ya no vale el token de la sesión',    csrf_token() === $t1, false);

echo "\n4) VERIFICACIÓN\n";
$_SESSION = []; $_POST = []; $_SERVER = [];
$_COOKIE[SESSION_NAME] = 'token-de-sesion-abc123';
$_POST[CSRF_TOKEN_NAME] = csrf_token();
chk('acepta el token por POST',                       csrf_verify(), true);
$_POST = [];
$_SERVER['HTTP_X_CSRF_TOKEN'] = csrf_token();
chk('acepta el token por header (los fetch de JSON)', csrf_verify(), true);
$_SERVER['HTTP_X_CSRF_TOKEN'] = str_repeat('a', 64);
chk('rechaza un token ajeno del mismo largo',         csrf_verify(), false);
$_SERVER = []; $_POST = [];
chk('rechaza si no viene ninguno',                    csrf_verify(), false);

echo "\n5) SIN SESIÓN (login, registro) — se conserva el comportamiento viejo\n";
$_SESSION = []; $_COOKIE = []; $_POST = []; $_SERVER = [];
$a = csrf_token();
chk('se guarda en $_SESSION',                         isset($_SESSION[CSRF_TOKEN_NAME]), true);
chk('estable mientras $_SESSION viva',                csrf_token(), $a);
$_POST[CSRF_TOKEN_NAME] = $a;
chk('y verifica igual',                               csrf_verify(), true);
// Aquí sí se pierde con la limpieza — no hay a qué anclarse. Es una pantalla
// de un solo paso, así que se acepta a propósito.
limpiador_de_php();
chk('tras la limpieza cambia (limitación conocida y aceptada)', csrf_token() === $a, false);

echo "\n" . ($fail === 0
    ? "✓ CSRF OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
