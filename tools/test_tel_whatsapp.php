<?php
// ============================================================
// PRUEBA — NORMALIZACIÓN DE TELÉFONO PARA WHATSAPP.
//
// Por qué existe: el botón "Enviar WhatsApp" arma un enlace wa.me con el
// número del cliente. Si la normalización se equivoca, el asesor le abre una
// conversación a un desconocido con la cotización de otra persona adentro.
// Ese es el peor error posible de esta función, y por eso la regla es:
// ante la duda, devolver '' (WhatsApp abre sin destinatario y el asesor elige
// el contacto). Un tap extra siempre es mejor que un mensaje mal enviado.
//
// Los teléfonos se GUARDAN como la gente los escribe. Esta función solo
// normaliza al momento de armar el enlace.
//
// Correr: php tools/test_tel_whatsapp.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);

// Helpers.php solo necesita estos nombres para poder cargarse.
class DB {
    public static function val($s, $p = []) { return null; }
    public static function row($s, $p = []) { return null; }
    public static function query($s, $p = []) { return []; }
    public static function execute($s, $p = []) { return 0; }
}
class Auth { public static function id() { return null; } }
class Radar {}

require __DIR__ . '/../core/Helpers.php';

$ok = 0; $fail = 0;
function chk(string $t, $got, $want): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got) . " want=" . json_encode($want) . "\n"; }
}

echo "\n1) EL CASO NORMAL EN MÉXICO\n";
// La gente escribe 10 dígitos. WhatsApp necesita 52 + esos 10.
chk('10 dígitos con espacios',      tel_whatsapp('662 142 1858'),      '526621421858');
chk('10 dígitos pegados',           tel_whatsapp('6621421858'),        '526621421858');
chk('con guiones',                  tel_whatsapp('662-142-1858'),      '526621421858');
chk('con paréntesis de lada',       tel_whatsapp('(662) 142 18 58'),   '526621421858');
chk('con punto',                    tel_whatsapp('662.142.1858'),      '526621421858');
chk('con espacios de sobra',        tel_whatsapp('  662 142 1858  '),  '526621421858');

echo "\n2) YA TRAE LA LADA — NO SE DUPLICA\n";
chk('52 + 10 dígitos',              tel_whatsapp('52 662 142 1858'),   '526621421858');
chk('ya normalizado',               tel_whatsapp('526621421858'),      '526621421858');
// Formato viejo de WhatsApp (52 + 1 + 10). El '1' ya no se usa: si se manda,
// el número no existe y el mensaje no llega.
chk('formato viejo 521 se corrige', tel_whatsapp('5216621421858'),     '526621421858');

echo "\n3) EL '+' ES LA SALIDA PARA EL EXTRANJERO\n";
// El asesor ya escribió la lada completa: se respeta, NO se le aplica la de la empresa.
chk('+1 (EE.UU.)',                  tel_whatsapp('+1 619 555 0134'),   '16195550134');
chk('+34 (España)',                 tel_whatsapp('+34 612 345 678'),   '34612345678');
chk('00 a la europea',              tel_whatsapp('001 619 555 0134'),  '16195550134');
// Sin '+', un número de 10 dígitos de otro país se trata como nacional. Es lo
// correcto: el 99% de los clientes son locales y el '+' está para el resto.
chk('sin + se asume nacional',      tel_whatsapp('6195550134'),        '526195550134');

echo "\n4) ANTE LA DUDA, VACÍO (abre WhatsApp sin destinatario)\n";
chk('vacío',                        tel_whatsapp(''),                  '');
chk('null',                         tel_whatsapp(null),                '');
chk('solo espacios',                tel_whatsapp('   '),               '');
chk('sin dígitos',                  tel_whatsapp('no tiene'),          '');
chk('incompleto (8 dígitos)',       tel_whatsapp('66214218'),          '');
chk('9 dígitos',                    tel_whatsapp('662142185'),         '');
chk('11 dígitos sueltos',           tel_whatsapp('66214218588'),       '');
chk('12 que no empiezan con lada',  tel_whatsapp('996621421858'),      '');
chk('extensión pegada',             tel_whatsapp('662 142 1858 ext 5'), '');
chk('+ pero muy corto',             tel_whatsapp('+12345'),            '');
chk('+ pero absurdo de largo',      tel_whatsapp('+1234567890123456'), '');

echo "\n5) OTRAS LADAS DE PAÍS\n";
// La empresa configura su lada. Un cliente en Colombia (57) escribe 10 dígitos igual.
chk('lada 57, 10 dígitos',          tel_whatsapp('310 555 1234', '57'), '573105551234');
chk('lada 57 ya puesta',            tel_whatsapp('57 310 555 1234', '57'), '573105551234');
chk('lada 1, 10 dígitos',           tel_whatsapp('619 555 0134', '1'),  '16195550134');
// El arreglo del '521' es específico de México: no debe dispararse con otra lada.
chk('521 con lada 57 no se toca',   tel_whatsapp('5216621421858', '57'), '');
chk('lada basura cae a 52',         tel_whatsapp('6621421858', 'abc'),  '526621421858');

echo "\n6) NUNCA DEVUELVE ALGO QUE NO SEA DÍGITOS\n";
// wa.me/<numero> con cualquier otro carácter genera un enlace roto.
foreach (['662 142 1858', '+1 619 555 0134', '(662) 142-1858', 'no tiene', ''] as $in) {
    $out = tel_whatsapp($in);
    chk("'" . $in . "' → solo dígitos o vacío", $out === '' || ctype_digit($out), true);
}

echo "\n" . ($fail === 0
    ? "✓ TEL WHATSAPP OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
