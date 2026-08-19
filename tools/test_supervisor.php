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
    'Supervisor@OnTime.com' => ['nombre' => 'Supervisor', 'empresas' => [12, 13, 14, 2]],
    'basura@ejemplo.com'    => ['nombre' => 'Basura',     'empresas' => [0, -5, 'x', 7, 7]],
    'vacio@ejemplo.com'     => ['nombre' => 'Vacío',      'empresas' => []],
], JSON_PRETTY_PRINT));

require __DIR__ . '/../core/Helpers.php';

$ok = 0; $fail = 0;
function chk(string $t, bool $c): void {
    global $ok, $fail;
    if ($c) { $ok++; echo "  ✓ $t\n"; } else { $fail++; echo "  ✗ $t\n"; }
}
function como(?array $u): void { Auth::$u = $u; }

echo "\n1) SIN SESIÓN\n";
como(null);
chk('no es supervisor',           !es_supervisor());
chk('alcance vacío',              supervisor_empresas() === []);

echo "\n2) USUARIO QUE NO ESTÁ EN EL ARCHIVO\n";
como(['email' => 'cualquiera@ejemplo.com', 'rol' => 'admin']);
chk('no es supervisor',           !es_supervisor());
chk('alcance vacío',              supervisor_empresas() === []);
chk('no ve la empresa 12',        !supervisor_ve_empresa(12));

echo "\n3) SUPERVISOR CON SUS SUCURSALES\n";
como(['email' => 'supervisor@ontime.com', 'rol' => 'admin']);
chk('es supervisor',              es_supervisor());
chk('ve exactamente sus 4',       supervisor_empresas() === [12, 13, 14, 2]);
chk('ve la 12',                   supervisor_ve_empresa(12));
chk('ve la 2',                    supervisor_ve_empresa(2));
chk('NO ve Granito (7)',          !supervisor_ve_empresa(7));
chk('NO ve una que no existe',    !supervisor_ve_empresa(999));
chk('NO ve la 0',                 !supervisor_ve_empresa(0));

echo "\n4) EL SUPERADMIN NO ENTRA POR AQUÍ\n";
como(['email' => 'supervisor@ontime.com', 'rol' => 'superadmin']);
chk('mismo correo, pero superadmin → alcance vacío', supervisor_empresas() === []);
chk('no es supervisor',           !es_supervisor());

echo "\n5) BASURA EN EL ARCHIVO SE DESCARTA\n";
como(['email' => 'basura@ejemplo.com', 'rol' => 'admin']);
chk('IDs 0, negativos y texto fuera; queda solo el 7', supervisor_empresas() === [7]);
chk('el 7 duplicado no se repite',                     count(supervisor_empresas()) === 1);

echo "\n6) LISTA VACÍA = SIN ACCESO\n";
como(['email' => 'vacio@ejemplo.com', 'rol' => 'admin']);
chk('no es supervisor',           !es_supervisor());
chk('alcance vacío',              supervisor_empresas() === []);

echo "\n7) CORREO SIN NORMALIZAR NO DEJA FUERA A NADIE\n";
// En el archivo está como 'Supervisor@OnTime.com'; el usuario lo tiene en
// minúsculas y con espacios de sobra.
como(['email' => '  SUPERVISOR@ontime.COM  ', 'rol' => 'admin']);
chk('matchea igual',              supervisor_empresas() === [12, 13, 14, 2]);

echo "\n8) SIN CORREO\n";
como(['email' => '', 'rol' => 'admin']);
chk('alcance vacío',              supervisor_empresas() === []);

// Dejar el entorno como estaba.
if ($backup !== null) file_put_contents($f, $backup); else @unlink($f);

echo "\n" . ($fail === 0
    ? "✓ SUPERVISOR OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
