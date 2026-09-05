<?php
// ============================================================
// PRUEBA — RENOMBRAR UNA VENTA.
//
// El título de la venta nace copiado de la cotización al convertirla y hasta
// hoy no había forma de corregirlo: una venta que nacía con el nombre
// equivocado se quedaba así, y había que entrar a la base de datos.
//
// Lo que esta prueba cuida no es que el campo exista, sino las dos decisiones
// que lo hacen seguro:
//
//   · SOLO ADMIN. El cliente ve este título —sale en la liga pública, en el
//     estado de cuenta y en los recibos—, así que no es algo que cada asesor
//     deba reescribir sobre ventas de otros.
//   · EL SLUG NO CAMBIA SOLO. También salió del título, pero es la llave con
//     la que el cliente abre su venta: cambiarlo rompe la liga que ya tenga.
//     `ventas` no registra visitas, así que el sistema NO PUEDE saber si esa
//     liga ya se mandó. Se le pregunta al admin, con la consecuencia escrita.
//
// Correr: php tools/test_titulo_venta.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . " want=" . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n"; }
}
$acc = (string)file_get_contents(__DIR__ . '/../modules/ventas/acciones.php');
$ver = (string)file_get_contents(__DIR__ . '/../modules/ventas/ver.php');
$rou = (string)file_get_contents(__DIR__ . '/../core/Router.php');

echo "\n1) LA RUTA Y LA ACCIÓN EXISTEN Y CASAN\n";
// Una sin la otra da 404 silencioso o una acción inalcanzable — que es
// exactamente como quedaron los botones de envío antes de que se detectara.
chk('la ruta está registrada',   str_contains($rou, "'/ventas/:id/titulo'"));
chk('y apunta a la acción',      str_contains($rou, "'accion'=>'titulo'"));
chk('la acción existe',          str_contains($acc, "elseif (\$accion === 'titulo')"));
chk('el front llama esa ruta',   str_contains($ver, "'/ventas/'+VENTA_ID+'/titulo'"));

echo "\n2) SOLO ADMIN\n";
preg_match("/elseif \(\\\$accion === 'titulo'\)\s*\{(.*?)\n\}/s", $acc, $m);
$bloque = $m[1] ?? '';
chk('el backend exige admin',    str_contains($bloque, 'Auth::es_admin()'));
chk('y responde 403',            str_contains($bloque, '403'));
// El gate del backend es el que manda; el del front es para no enseñar un
// botón que va a rebotar.
chk('el lápiz solo se pinta a admin',
    (bool)preg_match("/Auth::es_admin\(\)[^?]*\?>.{0,400}openSheet\('shTitulo'\)/s", $ver));
chk('y la hoja tampoco se imprime a los demás',
    (bool)preg_match("/if \(Auth::es_admin\(\).{0,120}\)\s*:\s*\?>\s*.{0,600}id=\"shTitulo\"/s", $ver));

echo "\n3) EL SLUG NO SE TOCA SOLO\n";
// Ese es el punto entero: la liga que el cliente ya tiene en la mano no se
// rompe salvo que alguien lo pida a propósito.
chk('el cambio de slug es opt-in',   str_contains($bloque, "!empty(\$body['regenerar_slug'])"));
chk('hay un UPDATE que NO toca slug',(bool)preg_match('/UPDATE ventas SET titulo=\?, updated_at=NOW\(\)/', $bloque));
chk('y otro que sí, aparte',         (bool)preg_match('/UPDATE ventas SET titulo=\?, slug=\?/', $bloque));
// Sin excluir la propia venta, slug_unico chocaría consigo misma y le colgaría
// un "-2" al slug que ya tenía.
chk('slug_unico excluye la venta misma',
    (bool)preg_match("/slug_unico\(\\\$titulo, 'ventas', 'slug', \\\$empresa_id, \\\$venta_id\)/", $bloque));
// Un título de puros símbolos deja el slug vacío: mejor no tocarlo que dejar
// la venta con una liga en blanco, imposible de abrir.
chk('un slug vacío no se guarda',    str_contains($bloque, "\$slug_nuevo = null"));

echo "\n4) EL USUARIO SABE LO QUE VA A PASAR\n";
// La consecuencia se dice ANTES, no se descubre cuando el cliente reclama que
// su liga ya no abre.
chk('la hoja avisa que la liga anterior deja de funcionar',
    str_contains($ver, 'la liga anterior deja de funcionar'));
chk('y qué hacer si ya se mandó',
    str_contains($ver, 'deja esto sin marcar'));
chk('muestra la liga actual',        str_contains($ver, 'Liga actual:'));
chk('y confirma antes de romperla',  str_contains($ver, 'confirm('));
chk('avisa que el cliente ve el título',
    str_contains($ver, 'El cliente ve este nombre'));

echo "\n5) VALIDACIÓN\n";
chk('no acepta título vacío',        str_contains($bloque, "El título es requerido"));
chk('ni más largo que la columna',   str_contains($bloque, 'mb_strlen($titulo) > 255'));
// Sobre una venta cancelada no se renombra: su historia ya está cerrada.
chk('no se ofrece en ventas canceladas',
    substr_count($ver, "Auth::es_admin() && \$venta['estado'] !== 'cancelada'"), 2);

echo "\n" . ($fail === 0
    ? "✓ RENOMBRAR VENTA OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
