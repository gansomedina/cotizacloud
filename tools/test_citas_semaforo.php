<?php
// ============================================================
//  Tabla de verdad del pilar CITAS (RitmoAsesor::_citas_semaforo)
//
//  Regla CEO: CERO ES ROJO, SIEMPRE. No agendar ni una cita en los últimos
//  7 días es el embudo parado — da igual si antes agendaba mucho, poco o
//  nunca. El ámbar queda solo para la caída parcial (agendó algo, pero
//  menos de la mitad de su propio ritmo). En este pilar NO hay gris.
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

echo "═ CERO ES ROJO — SIEMPRE, sin importar la historia ═\n";
chk('0 citas · venía en 4/sem  (el caso de Abigail)', $sem(0, 4.0),  'rojo');
chk('0 citas · venía en 2/sem',                       $sem(0, 2.0),  'rojo');
chk('0 citas · venía en 8/sem  (ritmo alto)',         $sem(0, 8.0),  'rojo');
chk('0 citas · venía en 1.75/sem (poco ritmo)',       $sem(0, 1.75), 'rojo');
chk('0 citas · venía en 1/sem  (de vez en cuando)',   $sem(0, 1.0),  'rojo');
chk('0 citas · base 0.5 (casi nunca agenda)',         $sem(0, 0.5),  'rojo');
chk('0 citas · NUNCA ha agendado (base 0)',           $sem(0, 0.0),  'rojo');

echo "\n═ ÁMBAR: agendó algo, pero cayó bajo la mitad de lo suyo ═\n";
chk('1 cita · venía en 4/sem',                        $sem(1, 4.0),  'amarillo');
chk('3 citas · venía en 8/sem',                       $sem(3, 8.0),  'amarillo');

echo "\n═ VERDE: sostiene su ritmo ═\n";
chk('2 citas · venía en 4/sem (justo la mitad)',      $sem(2, 4.0),  'verde');
chk('5 citas · venía en 4/sem (arriba de lo suyo)',   $sem(5, 4.0),  'verde');
chk('1 cita · venía en 1/sem (su ritmo normal)',      $sem(1, 1.0),  'verde');
chk('1 cita · nunca agendaba (base 0) — algo es algo', $sem(1, 0.0), 'verde');

echo "\n═ EN ESTE PILAR NO HAY GRIS ═\n";
$grises = [];
foreach ([0,1,2,3,5,9] as $c7)
    foreach ([0.0,0.25,0.5,1.0,1.75,2.0,4.0,8.0] as $b)
        if ($sem($c7, $b) === 'gris') $grises[] = "c7=$c7 base=$b";
chk('ningún caso devuelve gris', $grises ? implode(' · ', $grises) : 'ok', 'ok');

echo "\n═ QUÉ CAMBIÓ (esta regla SÍ estrena alertas — a propósito) ═\n";
// Regla anterior: gris si (base<1 && c7<1); ámbar si (base>=2 && c7<base/2).
// El cero sin ritmo probado antes salía gris o verde; ahora sale rojo. Se
// enumera para que el cambio quede explícito y no sea una sorpresa en producción.
$nuevos = [];
foreach ([0,1,2,3,5,9] as $c7) {
    foreach ([0.0,0.5,1.0,1.75,2.0,4.0,8.0] as $b) {
        $hoy   = $sem($c7, $b);
        $antes = ($b < 1.0 && $c7 < 1) ? 'gris'
               : ((($b >= 2.0) && ($c7 < $b * 0.5)) ? 'amarillo' : 'verde');
        if ($hoy !== $antes) $nuevos[] = "c7=$c7 base=$b: {$antes}→{$hoy}";
    }
}
foreach ($nuevos as $n) echo "    · $n\n";
// Todo lo que cambió tiene que ser un cero volviéndose rojo — nada más.
$raros = array_filter($nuevos, fn($n) => !str_starts_with($n, 'c7=0 ') || !str_ends_with($n, '→rojo'));
chk('lo único que cambió es el cero → rojo', $raros ? implode(' · ', $raros) : 'ok', 'ok');

echo "\n" . ($FAIL === 0 ? "✓ CITAS OK" : "✗ $FAIL FALLAS") . "\n";
exit($FAIL === 0 ? 0 : 1);
