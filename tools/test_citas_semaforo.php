<?php
// ============================================================
//  Tabla de verdad del pilar CITAS (RitmoAsesor::_citas_semaforo)
//
//  Regla CEO: CERO ES ROJO. Con ritmo probado (>= 2 citas/sem de promedio
//  en 28 días), no agendar NI UNA en los últimos 7 días no es "una caída a
//  la mitad" — es el embudo parado. El ámbar queda para la caída parcial.
//
//  Correr: php tools/test_citas_semaforo.php   → debe terminar en OK
//  NO toca la BD.
// ============================================================
define('COTIZAAPP', 1);
require_once __DIR__ . '/../core/RitmoAsesor.php';

$m = new ReflectionMethod('RitmoAsesor', '_citas_semaforo');
$m->setAccessible(true);
// $baja se calcula en _citas() con la MISMA fórmula; se replica aquí para
// alimentar la tabla con entradas coherentes.
$sem = function (int $c7, float $base) use ($m): string {
    $baja = ($base >= 2.0) && ($c7 < $base * 0.5);
    return $m->invoke(null, $c7, $base, $baja);
};

$FAIL = 0;
function chk(string $caso, string $got, string $esp) {
    global $FAIL; $ok = ($got === $esp); if (!$ok) $FAIL++;
    printf("  %s %-58s → %-9s%s\n", $ok ? '✓' : '✗', $caso, $got, $ok ? '' : "  (esperado: $esp)");
}

echo "═ CERO ES ROJO (solo con ritmo probado) ═\n";
chk('0 citas · venía en 4/sem  (el caso de Abigail)', $sem(0, 4.0),  'rojo');
chk('0 citas · venía en 2/sem  (justo en el gate)',   $sem(0, 2.0),  'rojo');
chk('0 citas · venía en 8/sem  (ritmo alto)',         $sem(0, 8.0),  'rojo');

echo "\n═ CERO SIN RITMO PROBADO: no se inventa alarma ═\n";
chk('0 citas · venía en 1.75/sem (bajo el gate)',     $sem(0, 1.75), 'verde');
chk('0 citas · venía en 1/sem  (agenda de vez en cuando)', $sem(0, 1.0), 'verde');
chk('0 citas · nunca agenda (base 0)',                $sem(0, 0.0),  'gris');
chk('0 citas · base 0.5 (casi nunca)',                $sem(0, 0.5),  'gris');

echo "\n═ ÁMBAR: cayó a menos de la mitad, pero agendó algo ═\n";
chk('1 cita · venía en 4/sem',                        $sem(1, 4.0),  'amarillo');
chk('3 citas · venía en 8/sem',                       $sem(3, 8.0),  'amarillo');

echo "\n═ VERDE: sostiene su ritmo ═\n";
chk('2 citas · venía en 4/sem (justo la mitad)',      $sem(2, 4.0),  'verde');
chk('5 citas · venía en 4/sem (arriba de lo suyo)',   $sem(5, 4.0),  'verde');
chk('1 cita · venía en 1/sem (su ritmo normal)',      $sem(1, 1.0),  'verde');

echo "\n═ EL ROJO ES SUBCONJUNTO DEL ÁMBAR DE ANTES (cero alertas nuevas) ═\n";
// Antes: amarillo si (base>=2 && c7 < base/2). Todo lo que hoy es rojo tenía
// que ser amarillo ayer — si no, estaríamos estrenando alarmas.
$viol = [];
foreach ([0,1,2,3,5,9] as $c7) {
    foreach ([0.0,0.5,1.0,1.75,2.0,4.0,8.0] as $b) {
        $hoy   = $sem($c7, $b);
        $antes = ($b < 1.0 && $c7 < 1) ? 'gris'
               : ((($b >= 2.0) && ($c7 < $b * 0.5)) ? 'amarillo' : 'verde');
        if ($hoy === 'rojo' && $antes !== 'amarillo') $viol[] = "c7=$c7 base=$b (antes $antes)";
        if ($hoy !== 'rojo' && $hoy !== $antes)       $viol[] = "c7=$c7 base=$b cambió $antes→$hoy";
    }
}
chk('ningún estado nuevo fuera del ámbar previo', $viol ? implode(' · ', $viol) : 'ok', 'ok');

echo "\n" . ($FAIL === 0 ? "✓ CITAS OK" : "✗ $FAIL FALLAS") . "\n";
exit($FAIL === 0 ? 0 : 1);
