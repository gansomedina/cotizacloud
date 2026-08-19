<?php
// ============================================================
// PRUEBA del alcance del SUPERVISOR multi-sucursal.
//
// Lo que garantiza, y por qué cada una importa en un SaaS:
//   1. Sin archivo de configuración, NADIE es supervisor.
//   2. Un usuario que no está en el archivo no ve nada.
//   3. El que sí está, ve EXACTAMENTE sus sucursales y ninguna más.
//   4. El superadmin no entra por esta puerta (tiene la suya).
//   5. Basura en el archivo (IDs 0, negativos, texto) se descarta.
//   6. Un correo con mayúsculas en el archivo no deja fuera a nadie.
//
// El punto 3 es EL punto: si el alcance se resolviera mal, un supervisor
// vería datos de empresas de otros clientes.
//
// Correr: php tools/test_supervisor.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);

// Auth de mentiras: solo lo que consultan los helpers del supervisor.
class Auth {
    public static $u = null;
    public static function logueado(): bool { return self::$u !== null; }
    public static function usuario(): ?array { return self::$u; }
    public static function es_superadmin(): bool { return (self::$u['rol'] ?? '') === 'superadmin'; }
    public static function requerir_login(string $r = '/login'): void {}
}

$dir = dirname(__DIR__) . '/data';
$f   = $dir . '/supervisores.json';
$backup = is_file($f) ? file_get_contents($f) : null;

// supervisores_config() memoiza en una static, así que el archivo se escribe
// ANTES del primer require y no se toca después: lo que varía es el usuario.
if (!is_dir($dir)) mkdir($dir, 0755, true);
file_put_contents($f, json_encode([
    'Supervisor@OnTime.com' => ['nombre' => 'Supervisor', 'usuario_id' => 509, 'empresas' => [12, 13, 14, 2]],
    'basura@ejemplo.com'    => ['nombre' => 'Basura',     'usuario_id' => 77,  'empresas' => [0, -5, 'x', 7, 7]],
    'vacio@ejemplo.com'     => ['nombre' => 'Vacío',      'usuario_id' => 88,  'empresas' => []],
    'sinid@ejemplo.com'     => ['nombre' => 'Sin id',                          'empresas' => [12, 13]],
], JSON_PRETTY_PRINT));

require __DIR__ . '/../core/Helpers.php';

$ok = 0; $fail = 0;
function chk(string $t, bool $c): void {
    global $ok, $fail;
    if ($c) { $ok++; echo "  ✓ $t\n"; } else { $fail++; echo "  ✗ $t\n"; }
}
// El id importa tanto como el correo — ver bloque 10.
function como(?array $u): void { Auth::$u = $u; }

echo "\n1) SIN SESIÓN\n";
como(null);
chk('no es supervisor',           !es_supervisor());
chk('alcance vacío',              supervisor_empresas() === []);

echo "\n2) USUARIO QUE NO ESTÁ EN EL ARCHIVO\n";
como(['id' => 900, 'email' => 'cualquiera@ejemplo.com', 'rol' => 'admin']);
chk('no es supervisor',           !es_supervisor());
chk('alcance vacío',              supervisor_empresas() === []);
chk('no ve la empresa 12',        !supervisor_ve_empresa(12));

echo "\n3) SUPERVISOR CON SUS SUCURSALES\n";
como(['id' => 509, 'email' => 'supervisor@ontime.com', 'rol' => 'admin']);
chk('es supervisor',              es_supervisor());
chk('ve exactamente sus 4',       supervisor_empresas() === [12, 13, 14, 2]);
chk('ve la 12',                   supervisor_ve_empresa(12));
chk('ve la 2',                    supervisor_ve_empresa(2));
chk('NO ve Granito (7)',          !supervisor_ve_empresa(7));
chk('NO ve una que no existe',    !supervisor_ve_empresa(999));
chk('NO ve la 0',                 !supervisor_ve_empresa(0));

echo "\n4) EL SUPERADMIN NO ENTRA POR AQUÍ\n";
como(['id' => 509, 'email' => 'supervisor@ontime.com', 'rol' => 'superadmin']);
chk('mismo correo, pero superadmin → alcance vacío', supervisor_empresas() === []);
chk('no es supervisor',           !es_supervisor());

echo "\n5) BASURA EN EL ARCHIVO SE DESCARTA\n";
como(['id' => 77, 'email' => 'basura@ejemplo.com', 'rol' => 'admin']);
chk('IDs 0, negativos y texto fuera; queda solo el 7', supervisor_empresas() === [7]);
chk('el 7 duplicado no se repite',                     count(supervisor_empresas()) === 1);

echo "\n6) LISTA VACÍA = SIN ACCESO\n";
como(['id' => 88, 'email' => 'vacio@ejemplo.com', 'rol' => 'admin']);
chk('no es supervisor',           !es_supervisor());
chk('alcance vacío',              supervisor_empresas() === []);

echo "\n7) CORREO SIN NORMALIZAR NO DEJA FUERA A NADIE\n";
// En el archivo está como 'Supervisor@OnTime.com'; el usuario lo tiene en
// minúsculas y con espacios de sobra.
como(['id' => 509, 'email' => '  SUPERVISOR@ontime.COM  ', 'rol' => 'admin']);
chk('matchea igual',              supervisor_empresas() === [12, 13, 14, 2]);

echo "\n8) SIN CORREO\n";
como(['id' => 509, 'email' => '', 'rol' => 'admin']);
chk('alcance vacío',              supervisor_empresas() === []);

echo "\n9) COBERTURA DEL ESCUDO — en qué empresas queda marcado como interno\n";
// Replica el cálculo de modules/auth/login_post.php:103-106. El Escudo trabaja
// POR EMPRESA (Radar::es_visitor_interno filtra por empresa_id), así que un
// supervisor que solo estuviera marcado en SU empresa contaminaría el Radar de
// las sucursales: cada slug que abriera contaría como visita de cliente.
$marcar = function (int $emp_propia): array {
    $m = [$emp_propia => $emp_propia];
    foreach (supervisor_empresas() as $se) $m[(int)$se] = (int)$se;
    sort($m);
    return array_values($m);
};

como(['id' => 900, 'email' => 'cualquiera@ejemplo.com', 'rol' => 'asesor']);
chk('asesor normal: SOLO su empresa',            $marcar(9) === [9]);

como(['id' => 509, 'email' => 'supervisor@ontime.com', 'rol' => 'asesor']);
chk('supervisor: su empresa + las 4 que supervisa', $marcar(4) === [2, 4, 12, 13, 14]);
chk('si su casa YA es una sucursal, no se duplica', $marcar(12) === [2, 12, 13, 14]);

como(['id' => 509, 'email' => 'supervisor@ontime.com', 'rol' => 'superadmin']);
chk('superadmin no pasa por esta rama (la suya marca todas)', $marcar(4) === [4]);

echo "\n10) EL CORREO NO ES IDENTIDAD — suplantación desde otro cliente del SaaS\n";
// usuarios.email es único POR EMPRESA (modules/config/usuario.php:94 y :139).
// El admin de cualquier otra empresa puede crear en la SUYA un usuario con el
// correo del supervisor y ponerle la contraseña que quiera. Si el alcance se
// resolviera solo por correo, entraría a ver estas 4 sucursales. Por eso el
// archivo declara usuario_id y tiene que coincidir.
como(['id' => 4321, 'email' => 'supervisor@ontime.com', 'rol' => 'admin']);
chk('mismo correo, OTRO usuario → alcance vacío', supervisor_empresas() === []);
chk('y no es supervisor',                         !es_supervisor());
chk('ni ve una sola sucursal',                    !supervisor_ve_empresa(12));
chk('ni queda marcado en el Escudo de ellas',     $marcar(31) === [31]);

echo "\n11) ENTRADA SIN usuario_id = SIN ACCESO (el default es negar)\n";
como(['id' => 55, 'email' => 'sinid@ejemplo.com', 'rol' => 'admin']);
chk('alcance vacío aunque el correo esté en el archivo', supervisor_empresas() === []);

echo "\n12) SESIÓN SIN id\n";
como(['email' => 'supervisor@ontime.com', 'rol' => 'admin']);
chk('alcance vacío',              supervisor_empresas() === []);

// Dejar el entorno como estaba.
if ($backup !== null) file_put_contents($f, $backup); else @unlink($f);

echo "\n" . ($fail === 0
    ? "✓ SUPERVISOR OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
