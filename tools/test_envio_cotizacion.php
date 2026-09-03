<?php
// ============================================================
// PRUEBA — LOS BOTONES DE ENVÍO AL CLIENTE EXISTEN Y ESTÁN CABLEADOS.
//
// Por qué existe: la hoja de envío del editor (openUrlOverlay) llevaba meses
// escrita —con sus accesos a WhatsApp y correo dentro— pero NINGÚN botón la
// abría. Código vivo, inalcanzable, y nadie se dio cuenta. El asesor solo tenía
// "Copiar link". Esta prueba impide que vuelva a pasar.
//
// Lo que garantiza:
//   1. Los dos lugares donde se comparte una cotización (el popup de recién
//      generada y la hoja del editor) tienen WhatsApp y correo.
//   2. La hoja del editor tiene quién la abra.
//   3. El número se normaliza en UN solo lugar (PHP), no con una copia en JS.
//   4. El correo del cliente se captura en los DOS formularios de alta.
//
// Correr: php tools/test_envio_cotizacion.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got) . " want=" . json_encode($want) . "\n"; }
}
function src(string $rel): string {
    return (string)file_get_contents(__DIR__ . '/../' . $rel);
}

$nueva  = src('modules/cotizaciones/nueva.php');
$ver    = src('modules/cotizaciones/ver.php');
$helper = src('core/Helpers.php');
$crear  = src('modules/clientes/crear.php');
$lista  = src('modules/clientes/lista.php');
$cliver = src('modules/clientes/ver.php');

echo "\n1) LA HOJA DEL EDITOR TIENE QUIÉN LA ABRA\n";
// El bug original: la función existía y nadie la llamaba.
chk('openUrlOverlay() está definida',   str_contains($ver, 'function openUrlOverlay('));
chk('y ALGO la llama',                  (bool)preg_match('/onclick="openUrlOverlay\(\)"/', $ver));

echo "\n2) LOS DOS LUGARES OFRECEN WHATSAPP Y CORREO\n";
chk('editor: enlace de WhatsApp',       str_contains($ver, 'https://wa.me/'));
chk('editor: enlace de correo',         str_contains($ver, 'mailto:'));
chk('popup: botón de WhatsApp',         str_contains($nueva, "id=\"popup-wa\""));
chk('popup: botón de correo',           str_contains($nueva, "id=\"popup-mail\""));
chk('popup: les pone href al abrirse',  str_contains($nueva, "getElementById('popup-wa').href"));

echo "\n3) EL NÚMERO SE NORMALIZA EN UN SOLO LUGAR\n";
// Si alguien reescribe la normalización en JavaScript, las dos versiones se
// separan el día que cambie una regla (y el mensaje se va a otro número).
chk('existe tel_whatsapp() en PHP',     str_contains($helper, 'function tel_whatsapp('));
chk('el editor la usa',                 str_contains($ver, 'tel_whatsapp('));
chk('el popup recibe el número ya hecho', str_contains($nueva, "'wa'       => tel_whatsapp("));
chk('el JS solo lo lee, no lo calcula', str_contains($nueva, 'cli.wa'));
// Señal de que alguien volvió a normalizar a mano en JS.
chk('sin regex de dígitos en el JS del popup',
    (bool)preg_match("/replace\(\/\\\\D/", $nueva), false);

echo "\n4) EL TEXTO DEL MENSAJE ES UNO SOLO\n";
chk('existe wa_texto_cotizacion()',     str_contains($helper, 'function wa_texto_cotizacion('));
chk('el editor lo usa',                 str_contains($ver, 'wa_texto_cotizacion('));
chk('el popup recibe la plantilla de PHP', str_contains($nueva, 'const WA_TPL'));
chk('y la variante sin nombre',         str_contains($nueva, 'WA_TPL_SIN'));

echo "\n5) NO REVIENTA SIN CLIENTE\n";
// Se puede generar una cotización sin cliente asignado; el popup igual se abre.
chk('el popup tolera clienteSeleccionado null',
    str_contains($nueva, "typeof clienteSeleccionado !== 'undefined' && clienteSeleccionado"));
chk('y el editor tolera cliente sin correo',
    str_contains($ver, "\$cli_mail = trim((string)(\$cot['cliente_email'] ?? ''))"));

echo "\n6) EL CORREO SE CAPTURA EN LOS DOS FORMULARIOS DE ALTA\n";
// Antes solo se guardaba al EDITAR: un cliente recién creado no tenía a dónde
// recibir su cotización, y el botón de correo nacía inútil.
chk('alta rápida (cotización): campo',  str_contains($nueva, 'id="nc-email"'));
chk('alta rápida: lo manda',            (bool)preg_match('/JSON\.stringify\(\{ nombre, telefono, email, direccion \}\)/', $nueva));
chk('módulo Clientes: campo',           str_contains($lista, 'id="cli-email"'));
chk('módulo Clientes: lo manda',        str_contains($lista, 'telefono_empresa, email, direccion, nota'));
chk('el endpoint lo guarda al CREAR',   str_contains($crear, 'telefono_empresa, email, direccion, nota'));
chk('edición de cliente: teléfono de oficina', str_contains($cliver, 'id="edit-telefono-empresa"'));

echo "\n7) LA BASE SIN MIGRAR NO TUMBA EL ALTA\n";
// Las columnas nuevas son opcionales: si la migración no se corrió, el cliente
// se guarda igual en vez de dar error 500.
chk('crear.php tiene respaldo sin telefono_empresa',
    substr_count($crear, 'INSERT INTO clientes') === 2);
chk('lada_pais() tolera que falte la columna',
    (bool)preg_match('/function lada_pais[\s\S]{0,400}catch \(\\\\Throwable/', $helper));

echo "\n" . ($fail === 0
    ? "✓ ENVÍO AL CLIENTE OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
