<?php
// ============================================================
// PRUEBA — DESCARTAR SIEMPRE PIDE MOTIVO.
//
// Por qué existe: descartar se puede hacer de dos formas —la pastilla
// "Descartar" del cajón de la Mesa y el 👎 del renglón o del Radar— y las dos
// escriben la misma marca, sacan la cotización de la mesa y pesan igual en el
// pilar de Descartadas. Durante mucho tiempo solo la pastilla pedía motivo. El
// resultado real, medido en producción: de 18 descartes de un asesor, 15 salieron
// por 👎 y quedaron SIN registro de por qué se perdió el cliente.
//
// Lo que garantiza:
//   1. Los dos gestos cuentan como descarte (y ningún otro tap lo hace).
//   2. La lista de motivos es UNA sola y está sana.
//   3. Los dos endpoints resuelven la regla con la MISMA función, no con
//      condiciones repetidas que se pueden desincronizar.
//
// Correr: php tools/test_descarte_motivo.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);
define('MODULES_PATH', '/dev/null');

// Mesa.php solo necesita estos nombres para poder cargarse.
class DB { public static function query($s,$p=[]){ return []; } }
class Radar {}

require __DIR__ . '/../core/Mesa.php';

$ok = 0; $fail = 0;
function chk(string $t, $got, $want): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got) . " want=" . json_encode($want) . "\n"; }
}

echo "\n1) QUÉ CUENTA COMO DESCARTE\n";
chk('la pastilla del cajón descarta',        Mesa::es_descarte('postura',  'descartada'),      true);
chk('el 👎 descarta (renglón y Radar)',      Mesa::es_descarte('feedback', 'sin_interes'),     true);

echo "\n2) QUÉ NO\n";
chk('👍 no descarta',                        Mesa::es_descarte('feedback', 'con_interes'),     false);
chk('📵 no descarta — es "no responde"',     Mesa::es_descarte('feedback', 'sin_info'),        false);
chk('"Nada concreto" no descarta',           Mesa::es_descarte('compromiso','sin_compromiso'), false);
chk('"Propuse, no quiso" no descarta',       Mesa::es_descarte('compromiso','propuse_no_quiso'), false);
chk('"En el aire" no descarta',              Mesa::es_descarte('postura',  'en_el_aire'),      false);
chk('"No contestó" no descarta',             Mesa::es_descarte('contacto', 'no_contesta'),     false);
// El nombre del área no basta por sí solo en ninguna dirección.
chk('sin_interes fuera de feedback no cuenta', Mesa::es_descarte('postura', 'sin_interes'),    false);

echo "\n3) LA LISTA DE MOTIVOS\n";
$rz = Mesa::RAZONES;
chk('son 6',                                 count($rz), 6);
chk('las claves esperadas',                  array_keys($rz),
    ['precio','competencia','despues','no_responde','no_comprador','otro']);
chk('ninguna etiqueta vacía',                count(array_filter($rz, fn($v) => trim((string)$v) === '')), 0);
chk('sin etiquetas repetidas',               count(array_unique($rz)), count($rz));
// La columna es VARCHAR(30): una clave más larga se truncaría al guardar y el
// reporte del Director ya no la reconocería.
chk('ninguna clave pasa de 30 chars',
    count(array_filter(array_keys($rz), fn($k) => strlen((string)$k) > 30)), 0);

echo "\n4) \"OTRO\" EXIGE EL MOTIVO ESCRITO\n";
// "Otro" no explica nada por si solo. Si el texto fuera opcional nadie lo
// llenaria y volveriamos a tener descartes sin explicacion — el mismo problema
// que se acaba de arreglar exigiendo motivo al 👎.
chk('con otro, el texto se conserva',      Mesa::razon_texto('otro', 'se mudo de ciudad'), 'se mudo de ciudad');
chk('vacio = null (el endpoint lo rechaza)', Mesa::razon_texto('otro', '   '),  null);
chk('null tambien',                        Mesa::razon_texto('otro', null),     null);
// Los demas motivos ya se explican con su etiqueta: si llega texto, se ignora.
chk('precio no guarda texto',              Mesa::razon_texto('precio', 'algo'), null);
chk('competencia tampoco',                 Mesa::razon_texto('competencia', 'x'), null);
chk('sin razon tampoco',                   Mesa::razon_texto(null, 'x'),        null);
// Espacios y saltos de linea colapsados: el historial de la mesa es una linea.
chk('normaliza espacios',                  Mesa::razon_texto('otro', "  se   mudo \n de ciudad "), 'se mudo de ciudad');
// La columna es VARCHAR(200): mas largo se truncaria en la BD sin avisar.
chk('corta a 200 caracteres',              strlen(Mesa::razon_texto('otro', str_repeat('a', 500))), 200);
chk('el tope coincide con la columna',     Mesa::RAZON_TEXTO_MAX, 200);

echo "\n5) LOS DOS CAMINOS DE DESCARTE LO PIDEN IGUAL\n";
// El 👎 del Radar y el de la Mesa son el MISMO descarte; si solo uno exigiera
// el texto, los asesores usarian el otro.
foreach (['api/mesa_estado.php' => 'la Mesa', 'api/radar_feedback.php' => 'el Radar'] as $f => $quien) {
    $src = (string)file_get_contents(__DIR__ . '/../' . $f);
    chk("$quien usa Mesa::razon_texto()",   str_contains($src, 'Mesa::razon_texto('), true);
    chk("$quien rechaza 'otro' sin texto",  (bool)preg_match("/razon === 'otro' && \\\$razon_texto === null/u", $src), true);
    chk("$quien lo guarda en la BD",        str_contains($src, 'razon_texto, bucket_snapshot'), true);
}

echo "\n6) Y SE VE — si no, es un dato que nadie lee\n";
$mesa_src = (string)file_get_contents(__DIR__ . '/../modules/dashboard/_mesa.php');
chk('el historial del cajon lo trae',      str_contains($mesa_src, 'estado, razon, razon_texto, created_at'), true);
chk('y lo pinta',                          str_contains($mesa_src, "razon_texto']"), true);
$rep_src = (string)file_get_contents(__DIR__ . '/../core/RitmoReporte.php');
chk('el reporte del Director lo trae',     str_contains($rep_src, 'mp3.razon_texto'), true);
chk('y le gana a la etiqueta generica',    (bool)preg_match("/!empty\\(\\\$c\\['razon_texto'\\]\\)[\\s\\S]{0,60}\\\$why = \\\$c\\['razon_texto'\\]/u", $rep_src), true);
// Y donde de verdad se lee despues: la COTIZACION. El cajon de la Mesa solo
// existe el dia del descarte —la fila sale al siguiente— asi que sin esto el
// motivo escrito era ilegible a las 24 horas de escribirlo.
$cot_src = (string)file_get_contents(__DIR__ . '/../modules/cotizaciones/ver.php');
chk('la cotizacion trae los toques de la Mesa',
    str_contains($cot_src, 'FROM mesa_estados m'), true);
chk('con el motivo escrito',                 str_contains($cot_src, 'm.razon_texto'), true);
chk('el texto le gana a la etiqueta',
    (bool)preg_match("/!empty\\(\\\$t\\['razon_texto'\\]\\)[\\s\\S]{0,60}\\\$mot = \\\$t\\['razon_texto'\\]/u", $cot_src), true);
chk('el contacto implicito no ensucia',      str_contains($cot_src, "razon <> 'auto'"), true);
// Base sin migrar: la pagina se pinta igual, no revienta.
chk('tolera que mesa_estados no exista',
    (bool)preg_match('/FROM mesa_estados[\\s\\S]{0,400}?catch \\(\\\\Throwable/u', $cot_src), true);
// El CEO pidio poder revertirlo rapido: las marcas tienen que estar.
// Son DOS bloques (la consulta arriba, el render abajo) y cada uno tiene que
// decir como quitarse — si no, revertirlo obliga a leerse el archivo entero.
chk('los dos bloques dicen como quitarse',   substr_count($cot_src, 'PARA QUITARLO'), 2);
chk('y estan delimitados de inicio a fin',
    str_contains($cot_src, 'SEGUIMIENTO DE LA MESA — FIN')
    && str_contains($cot_src, '══ MESA — INICIO')
    && str_contains($cot_src, '══ MESA — FIN'), true);

$modal = (string)file_get_contents(__DIR__ . '/../modules/dashboard/_razon_modal.php');
chk('el selector pide el texto en "otro"', str_contains($modal, "clave === 'otro'") && str_contains($modal, 'czRzOtro'), true);
chk('sin texto no descarta',               str_contains($modal, 'if (!v) { txt.focus(); return; }'), true);

echo "\n7) UNA SOLA REGLA, NO COPIAS\n";
// Si alguien vuelve a escribir la condición a mano en un endpoint, se
// desincroniza en silencio la primera vez que cambie.
foreach (['api/mesa_estado.php', 'api/radar_feedback.php'] as $f) {
    $src = (string)file_get_contents(__DIR__ . '/../' . $f);
    chk("$f usa Mesa::es_descarte()",        str_contains($src, 'Mesa::es_descarte('), true);
    chk("$f no trae la lista a mano",        str_contains($src, "'no_comprador'"),     false);
}

echo "\n" . ($fail === 0
    ? "✓ DESCARTE/MOTIVO OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
