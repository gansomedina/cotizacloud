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

echo "\n4) UNA SOLA REGLA, NO COPIAS\n";
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
