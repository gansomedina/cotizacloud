<?php
// ============================================================
// PRUEBA — EL SCORE TRADUCIDO A PALABRAS.
//
// Por qué existe: el reporte del asesor imprimía "Activación 24%" y ya. Un
// porcentaje pelón no se acciona, y en tres de las cinco dimensiones además
// ENGAÑA. Esta prueba blinda justo esos casos, porque son los que hacen que un
// reporte felicite a quien no está vendiendo:
//
//   · Engagement sube cuando NO hay ventas que castigar.
//   · Seguimiento da 100% cuando la mesa está VACÍA (no hubo examen).
//   · Radar Health da 50% cuando no hubo ningún cliente caliente.
//   · Activación son dos mitades independientes: el MISMO 24% puede significar
//     "no abren tus cotizaciones" o "no lees tu diagnóstico" — consejos opuestos.
//
// Correr: php tools/test_score_lectura.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================
define('COTIZAAPP', 1);
require __DIR__ . '/../core/ScoreLectura.php';

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . " want=" . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n"; }
}
/** Busca una dimensión por clave en el resultado de dimensiones(). */
function dim(array $row, string $clave): array {
    foreach (ScoreLectura::dimensiones($row) as $d) if ($d['clave'] === $clave) return $d;
    throw new RuntimeException("dimensión $clave no encontrada");
}

echo "\n1) LAS BANDAS DEL ESTÁNDAR\n";
// Decisión CEO: 70 es el estándar, abajo de 60 es crítico, arriba de 85 excelencia.
chk('100 es excelencia',        ScoreLectura::banda(100)['clave'], 'excelencia');
chk('85 justo es excelencia',   ScoreLectura::banda(85)['clave'],  'excelencia');
chk('84 ya no lo es',           ScoreLectura::banda(84)['clave'],  'estandar');
chk('70 justo es el estándar',  ScoreLectura::banda(70)['clave'],  'estandar');
chk('69 cae abajo',             ScoreLectura::banda(69)['clave'],  'debajo');
chk('60 sigue siendo "debajo"', ScoreLectura::banda(60)['clave'],  'debajo');
chk('59 es crítico',            ScoreLectura::banda(59)['clave'],  'critico');
chk('0 es crítico',             ScoreLectura::banda(0)['clave'],   'critico');
// Las constantes son la fuente: si alguien mueve el estándar, se mueve todo.
chk('el estándar es 70',        ScoreLectura::ESTANDAR,  70);
chk('el piso es 60',            ScoreLectura::PISO,      60);
chk('la excelencia es 85',      ScoreLectura::EXCELENCIA, 85);

echo "\n2) LOS PESOS SON ESPEJO DE ActividadScore\n";
// Si allá cambian y aquí no, la brecha señalaría la dimensión equivocada.
$src = (string)file_get_contents(__DIR__ . '/../core/ActividadScore.php');
foreach ([['w_act',0.13],['w_eng',0.17],['w_seg',0.25],['w_hlt',0.10],['w_conv',0.35]] as [$v,$peso]) {
    // Se compara el VALOR, no el texto: el motor escribe 0.10 y aquí vive 0.1.
    preg_match('/\$' . $v . '\s*=\s*([0-9.]+);/', $src, $mm);
    chk("$v = $peso en el motor", isset($mm[1]) ? (float)$mm[1] : null, $peso);
}
chk('suman 1.0', round(array_sum(ScoreLectura::PESOS), 4), 1.0);

echo "\n3) ACTIVACIÓN — el mismo 24% son tres realidades distintas\n";
// s_activacion = operativa×0.5 + tips×0.5 (ActividadScore:572). Un solo número
// no basta: hay que partirlo o el consejo sale al revés.
$base = ['score'=>51,'s_activacion'=>0.24,'cot_asignadas'=>10,'cot_vistas'=>5];

// (a) Sus cotizaciones llegan bien; lo que falla es que NO lee su diagnóstico.
$a = dim($base + ['s_activacion_op'=>0.48,'tips_score'=>0.0,'no_abiertas_5d'=>0,
                  'dias_lectura'=>0,'dias_activos_feature'=>6], 'activacion');
chk('(a) no culpa a las cotizaciones', str_contains($a['frase'], 'llegan y se abren'));
chk('(a) señala la lectura',           str_contains($a['frase'], 'diagnóstico'));
chk('(a) muestra 0 de 6 días',         str_contains($a['frase'], '0 de 6'));

// (b) Lee a medias Y tiene cotizaciones sin abrir.
$b = dim($base + ['s_activacion_op'=>0.0,'tips_score'=>0.5,'no_abiertas_5d'=>3,
                  'dias_lectura'=>3,'dias_activos_feature'=>8], 'activacion');
chk('(b) nombra las 3 sin abrir',      str_contains($b['frase'], '3 cotizaciones'));
chk('(b) dice que esa mitad vale cero',str_contains($b['frase'], 'vale cero'));

// (c) Lee todo, pero la operativa está en el suelo.
$c = dim($base + ['s_activacion_op'=>-0.52,'tips_score'=>1.0,'no_abiertas_5d'=>4,
                  'dias_lectura'=>7,'dias_activos_feature'=>8], 'activacion');
chk('(c) reconoce que sí lee',         str_contains($c['frase'], 'casi siempre'));

// Las tres frases tienen que ser DISTINTAS: es el punto de partir la dimensión.
chk('las tres lecturas difieren', count(array_unique([$a['frase'],$b['frase'],$c['frase']])), 3);
// Y siempre se expone la partición, para que el reporte pueda imprimirla.
chk('expone las dos mitades',          count($a['partes']), 2);
chk('la mitad operativa no va negativa en pantalla', $c['partes'][0]['pct'] >= 0);
// Singular/plural: "1 cotizaciones" delata que el texto es de máquina.
$uno = dim($base + ['s_activacion_op'=>0.0,'tips_score'=>0.5,'no_abiertas_5d'=>1,
                    'dias_lectura'=>1,'dias_activos_feature'=>4], 'activacion');
chk('1 sin abrir va en singular',      str_contains($uno['frase'], '1 cotización que'));
// Nunca ha abierto un tip → el denominador es 0 y no puede dividir.
$nunca = dim($base + ['s_activacion_op'=>0.48,'tips_score'=>0.0,'no_abiertas_5d'=>0,
                      'dias_lectura'=>0,'dias_activos_feature'=>0], 'activacion');
chk('sin haber abierto nunca, lo dice', str_contains($nunca['frase'], 'Nunca has abierto'));

echo "\n4) ENGAGEMENT — el falso positivo que felicita al que no vende\n";
// 89% con cero ventas NO es mérito: no hay cobros pendientes ni descuentos
// porque no hay ventas. Sin este aviso el reporte lo felicita.
$e0 = dim(['s_engagement'=>0.89,'ventas_periodo'=>0,'ventas_sin_pago'=>0], 'engagement');
chk('avisa que es por ausencia de ventas', $e0['alerta'] !== null);
chk('lo dice explícito',              str_contains((string)$e0['alerta'], 'No lo leas como algo bueno'));
// Con ventas de verdad, el mismo 89% sí es mérito y NO debe llevar alerta.
$e1 = dim(['s_engagement'=>0.89,'ventas_periodo'=>4,'ventas_sin_pago'=>0], 'engagement');
chk('con ventas no hay alerta',       $e1['alerta'], null);
chk('y ahí sí felicita',              str_contains($e1['frase'], 'Cobras lo que vendes'));
// Bajo por ventas sin cobrar: hay que nombrar la causa, no el porcentaje.
$e2 = dim(['s_engagement'=>0.40,'ventas_periodo'=>3,'ventas_sin_pago'=>2], 'engagement');
chk('nombra las ventas sin cobrar',   str_contains($e2['frase'], '2 ventas sin un solo pago'));

echo "\n5) SEGUIMIENTO — 100% puede ser mérito o mesa vacía\n";
// Mesa vacía → 1.0 neutro (ActividadScore:842). Es el mismo número que "atendió
// todo" y significa lo contrario: que no le pidieron nada.
$s0 = dim(['s_seguimiento'=>1.0,'mesa_pedidas'=>0,'mesa_atendidas'=>0], 'seguimiento');
chk('mesa vacía lleva alerta',        $s0['alerta'] !== null);
chk('dice que es neutro, no mérito',  str_contains((string)$s0['alerta'], 'no es mérito'));
// Mesa con trabajo y atendida → mérito real, sin alerta.
$s1 = dim(['s_seguimiento'=>1.0,'mesa_pedidas'=>12,'mesa_atendidas'=>11], 'seguimiento');
chk('con señales atendidas no hay alerta', $s1['alerta'], null);
chk('y da el n de m',                 str_contains($s1['frase'], '11 de 12'));
// Escalón intermedio: 50% cuenta la mitad, y hay que decir cómo llegar al tope.
$s2 = dim(['s_seguimiento'=>0.5,'mesa_pedidas'=>10,'mesa_atendidas'=>6], 'seguimiento');
chk('el escalón medio se explica',    str_contains($s2['frase'], 'cuenta la mitad'));
// El castigo directo al score (hasta -8) no estaba documentado en ningún lado.
$s3 = dim(['s_seguimiento'=>0.0,'mesa_pedidas'=>10,'mesa_atendidas'=>2,
           'mesa_dias_vencidos'=>9,'castigo_seguimiento'=>5], 'seguimiento');
chk('avisa del castigo directo',      str_contains((string)$s3['alerta'], '5 puntos'));
chk('y explica cómo se drena',        str_contains((string)$s3['alerta'], 'detiene el reloj'));

echo "\n6) RADAR HEALTH — el número ES la respuesta\n";
// s = 1 − muertas/calientes. Las columnas crudas son legacy reusadas.
$r0 = dim(['s_radar_health'=>0.36,'health_up'=>11,'health_down'=>7], 'radar_health');
chk('traduce a "de cada 10"',         str_contains($r0['frase'], 'De cada 10'));
chk('da los crudos',                  str_contains($r0['frase'], '7 de 11'));
chk('no lo achaca a la temporada',    str_contains($r0['frase'], 'es seguimiento'));
// Lee también las columnas legacy si no vienen los alias.
$r1 = dim(['s_radar_health'=>0.36,'transiciones_up'=>11,'senales_ignoradas'=>7], 'radar_health');
chk('acepta los nombres legacy',      $r1['frase'], $r0['frase']);
// Sin calientes → 0.50 es el neutro del sistema, no una calificación.
$r2 = dim(['s_radar_health'=>0.50,'health_up'=>0,'health_down'=>0], 'radar_health');
chk('sin calientes lleva alerta',     $r2['alerta'] !== null);
chk('aclara que 50% es neutro',       str_contains((string)$r2['alerta'], 'neutro del sistema'));

echo "\n7) CONVERSIÓN — la que pesa más que ninguna\n";
$c0 = dim(['s_conversion'=>0.11,'ventas_periodo'=>0,'tasa_cierre'=>0.0,'bench_ventas'=>0], 'conversion');
chk('sin cierres lo dice sin rodeos', str_contains($c0['frase'], 'No cerraste ninguna venta'));
chk('recuerda que es el 35%',         str_contains($c0['frase'], '35%'));
$c1 = dim(['s_conversion'=>0.70,'ventas_periodo'=>6,'tasa_cierre'=>0.28,'bench_ventas'=>4], 'conversion');
chk('cerrando bien, felicita',        str_contains($c1['frase'], 'por encima'));
chk('y ahí no hay alerta',            $c1['alerta'], null);
// Muy por debajo del ritmo de la empresa: se conecta con Engagement, que es
// donde ese mismo déficit también resta.
$c2 = dim(['s_conversion'=>0.20,'ventas_periodo'=>1,'tasa_cierre'=>0.05,'bench_ventas'=>5], 'conversion');
chk('avisa del déficit vs la empresa', str_contains((string)$c2['alerta'], 'ritmo de la empresa'));

echo "\n8) LA BRECHA AL ESTÁNDAR\n";
// Perfil real que dio el CEO: Act 24 · Eng 89 · Seg 100 · Health 36 · Conv 11.
$perfil = ['score'=>51,'s_activacion'=>0.24,'s_engagement'=>0.89,
           's_seguimiento'=>1.00,'s_radar_health'=>0.36,'s_conversion'=>0.11];
$br = ScoreLectura::brecha($perfil);
chk('faltan 19 puntos para 70',       $br['faltan'], 19);
chk('la meta es el estándar',         $br['meta'], 70);
// Conversión tiene el mayor margen × el mayor peso: es la #1 por aritmética.
chk('la primera oportunidad es Conversión', $br['oportunidades'][0]['clave'], 'conversion');
// Seguimiento está en 100%: no puede ofrecer puntos.
$seg = array_values(array_filter($br['oportunidades'], fn($o) => $o['clave'] === 'seguimiento'))[0];
chk('lo que ya está al 100% no ofrece nada', $seg['puntos'], 0.0);
// El orden debe ser descendente SIEMPRE — es lo único exacto del cálculo.
$pts = array_column($br['oportunidades'], 'puntos');
$ord = $pts; rsort($ord);
chk('las oportunidades van de mayor a menor', $pts, $ord);
// Ya en el estándar → no hay brecha que reportar.
chk('en 70 la brecha es cero',        ScoreLectura::brecha(['score'=>70] + $perfil)['faltan'], 0);
chk('arriba del estándar tampoco',    ScoreLectura::brecha(['score'=>88] + $perfil)['faltan'], 0);

echo "\n9) HOY vs HISTORIAL\n";
// ±3 es ruido: la ventana es de 15 días y la foto diaria guarda el máximo.
chk('+2 es estable',   ScoreLectura::tendencia(53, 51.0, 20)['clave'], 'estable');
chk('-2 es estable',   ScoreLectura::tendencia(49, 51.0, 20)['clave'], 'estable');
chk('+5 sube',         ScoreLectura::tendencia(56, 51.0, 20)['clave'], 'sube');
chk('-5 baja',         ScoreLectura::tendencia(46, 51.0, 20)['clave'], 'baja');
// Sin historial no se inventa una tendencia.
chk('sin fotos no hay tendencia', ScoreLectura::tendencia(53, null, 0)['clave'], 'sin_datos');
chk('y lo explica',    str_contains(ScoreLectura::tendencia(53, null, 0)['txt'], 'Sin historial'));
// Pocas fotos: se avisa que es anécdota. No hay cron — un hueco es "nadie entró".
$pocas = ScoreLectura::tendencia(53, 51.0, 4);
chk('con 4 fotos avisa',           $pocas['confiable'], false);
chk('y lo dice en el texto',       str_contains($pocas['txt'], 'anécdota'));
chk('con 10 ya es confiable',      ScoreLectura::tendencia(53, 51.0, 10)['confiable'], true);
// Rango ancho: "hoy" es casi aleatorio y hay que decirlo.
$vol = ScoreLectura::tendencia(33, 54.7, 18, 33, 82);
chk('rango de 49 dispara el aviso', str_contains($vol['txt'], 'dice poco'));
chk('rango estrecho no lo dispara', str_contains(ScoreLectura::tendencia(42, 53.2, 31, 42, 63)['txt'], 'dice poco'), false);

echo "\n10) EL VEREDICTO DE UNA LÍNEA\n";
// Crítico y estancado es MÁS grave que crítico cayendo: el que cae se mueve.
chk('crítico estancado se marca como el peor',
    str_contains(ScoreLectura::veredicto(45, 'estable'), 'más grave'));
chk('crítico subiendo se reconoce',
    str_contains(ScoreLectura::veredicto(45, 'sube'), 'única señal buena'));
chk('en el estándar y estable = no tocar',
    str_contains(ScoreLectura::veredicto(73, 'estable'), 'no necesita intervención'));
chk('excelencia cayendo pide mirar ya',
    str_contains(ScoreLectura::veredicto(90, 'baja'), 'no cuando ya cayó'));
chk('debajo y estancado marca el riesgo de que nadie lo vea',
    str_contains(ScoreLectura::veredicto(65, 'estable'), 'nadie lo ve como urgencia'));
// Las 12 combinaciones tienen que existir y ser distintas.
$vs = [];
foreach (['excelencia'=>90,'estandar'=>73,'debajo'=>65,'critico'=>45] as $s)
    foreach (['sube','estable','baja'] as $t) $vs[] = ScoreLectura::veredicto($s, $t);
chk('las 12 combinaciones son distintas', count(array_unique($vs)), 12);
chk('ninguna sale vacía', count(array_filter($vs, fn($v) => trim($v) === '')), 0);

echo "\n11) NUNCA REVIENTA CON DATOS INCOMPLETOS\n";
// usuario_score puede venir sin columnas si la migración no corrió.
foreach ([[], ['score'=>0], ['s_activacion'=>0.5]] as $i => $row) {
    $d = ScoreLectura::dimensiones($row);
    chk("fila incompleta #$i devuelve las 5", count($d), 5);
    chk("fila incompleta #$i sin frases vacías",
        count(array_filter($d, fn($x) => trim($x['frase']) === '')), 0);
}
chk('brecha con fila vacía no truena', ScoreLectura::brecha([])['faltan'], 70);

echo "\n" . ($fail === 0
    ? "✓ SCORE EN PALABRAS OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
