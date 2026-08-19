<?php
// ============================================================
// PRUEBA de mp_vence_tras_pago() — la regla que decide hasta cuándo queda
// vigente un plan después de un pago de MercadoPago.
//
// POR QUÉ EXISTE: api/mp_return.php es la back_url del checkout. Extendía
// plan_vence sin mirar si el pago ya se había procesado, así que recargar la
// página con F5 regalaba otros 30 días. Cada recarga, un mes gratis.
//
// Correr: php tools/test_mp_vence.php   → debe terminar en OK
// Obligatorio tras cualquier cambio a mp_vence_tras_pago() o a mp_return.php.
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);
require __DIR__ . '/../core/Helpers.php';

$ok = 0; $fail = 0;
function chk(string $t, string $esperado, string $obtenido): void {
    global $ok, $fail;
    if ($esperado === $obtenido) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t\n      esperaba $esperado, obtuvo $obtenido\n"; }
}

$hoy    = date('Y-m-d');
$en30   = date('Y-m-d', strtotime('+30 days'));
$en60   = date('Y-m-d', strtotime('+60 days'));
$en365  = date('Y-m-d', strtotime('+365 days'));
$ayer20 = date('Y-m-d', strtotime('-20 days'));

echo "\n1) PRIMER PAGO\n";
chk('sin vigencia previa → hoy + 30',
    $en30, mp_vence_tras_pago(null, 30, false));
chk('anual sin vigencia previa → hoy + 365',
    $en365, mp_vence_tras_pago(null, 365, false));
chk('con vigencia VENCIDA → arranca desde hoy, no desde la fecha muerta',
    $en30, mp_vence_tras_pago($ayer20, 30, false));

echo "\n2) RENOVACIÓN LEGÍTIMA — los días se SUMAN a lo que le queda\n";
chk('le quedaban 30 días → 60',
    $en60, mp_vence_tras_pago($en30, 30, false));

echo "\n3) EL BUG: PAGO YA PROCESADO (F5 en la página de confirmación)\n";
chk('NO vuelve a extender: se queda igual',
    $en30, mp_vence_tras_pago($en30, 30, true));
chk('ni siquiera con ciclo anual',
    $en30, mp_vence_tras_pago($en30, 365, true));
// Diez recargas seguidas no deben mover la fecha ni un día.
$v = $en30;
for ($i = 0; $i < 10; $i++) $v = mp_vence_tras_pago($v, 30, true);
chk('diez recargas seguidas siguen sin moverla', $en30, $v);

echo "\n4) NO DEJAR AL CLIENTE PAGADO Y SIN VIGENCIA\n";
chk('pago ya registrado pero plan_vence NULL → recalcula igual',
    $en30, mp_vence_tras_pago(null, 30, true));
chk('pago ya registrado con plan_vence vacío → recalcula igual',
    $en30, mp_vence_tras_pago('', 30, true));
// Ya procesado y ya vencido: se respeta la fecha muerta. Recargar una página
// vieja no debe resucitar un plan que expiró.
chk('pago ya registrado y vigencia vencida → respeta la fecha vencida',
    $ayer20, mp_vence_tras_pago($ayer20, 30, true));

echo "\n" . ($fail === 0
    ? "✓ MP VENCE OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
