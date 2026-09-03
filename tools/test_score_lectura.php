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
// Los fixtures llevan s_mesa: es el gate de que la mesa manda la dimensión
// (queda NULL con mesa_activa < 2). Sin él caen en la rama de feedback.
// Mesa vacía → 1.0 neutro (ActividadScore:842). Es el mismo número que "atendió
// todo" y significa lo contrario: que no le pidieron nada.
$s0 = dim(['s_seguimiento'=>1.0,'s_mesa'=>1.0,'mesa_pedidas'=>0,'mesa_atendidas'=>0], 'seguimiento');
chk('mesa vacía lleva alerta',        $s0['alerta'] !== null);
chk('dice que es neutro, no mérito',  str_contains((string)$s0['alerta'], 'no es mérito'));
// Mesa con trabajo y atendida → mérito real, sin alerta.
$s1 = dim(['s_seguimiento'=>1.0,'s_mesa'=>1.0,'mesa_pedidas'=>12,'mesa_atendidas'=>11], 'seguimiento');
chk('con señales atendidas no hay alerta', $s1['alerta'], null);
chk('y da el n de m',                 str_contains($s1['frase'], '11 de 12'));
// Escalón intermedio: 50% cuenta la mitad, y hay que decir cómo llegar al tope.
$s2 = dim(['s_seguimiento'=>0.5,'s_mesa'=>0.5,'mesa_pedidas'=>10,'mesa_atendidas'=>6], 'seguimiento');
chk('el escalón medio se explica',    str_contains($s2['frase'], 'cuenta la mitad'));
// El castigo directo al score (hasta -8) no estaba documentado en ningún lado.
$s3 = dim(['s_seguimiento'=>0.0,'s_mesa'=>0.0,'mesa_pedidas'=>10,'mesa_atendidas'=>2,
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

echo "\n12) LOS SEIS HALLAZGOS DE LA AUDITORÍA\n";

// (1) mesa_pedidas=0 NO significa "mesa vacía": también es 0 con la mesa
// apagada, y ahí el Seguimiento mide otra cosa (feedback en calientes). El
// gate real es s_mesa, que queda NULL cuando mesa_activa < 2.
$sin_mesa = dim(['s_seguimiento'=>0.42,'mesa_pedidas'=>0,'mesa_atendidas'=>0], 'seguimiento');
chk('sin mesa NO habla de señales',   str_contains($sin_mesa['frase'], 'mesa'), false);
chk('sin mesa no inventa un 100%',    $sin_mesa['alerta'], null);
chk('sin mesa habla del feedback',    str_contains($sin_mesa['frase'], 'interés'));
// Con la mesa mandando (s_mesa presente) sí aplica la lectura de la mesa.
$con_mesa = dim(['s_seguimiento'=>1.0,'s_mesa'=>1.0,'mesa_pedidas'=>0,'mesa_atendidas'=>0], 'seguimiento');
chk('con mesa vacía sí avisa que es neutro', str_contains((string)$con_mesa['alerta'], 'no es mérito'));

// (2) Período de gracia: score 0 y dimensiones en cero por early return del
// motor, no porque el asesor esté mal.
chk('detecta al nuevo',               ScoreLectura::es_nuevo(['nivel'=>'nuevo']));
chk('y no confunde al regular',       ScoreLectura::es_nuevo(['nivel'=>'regular']), false);
chk('el reporte lo trata aparte',
    str_contains((string)file_get_contents(__DIR__ . '/../core/RitmoReporte.php'), 'ScoreLectura::es_nuevo'));

// (3) Sin ventas, Engagement aterriza en 1 − close_rate (~71-80%). Con umbral
// de 80 el aviso se escapaba y el texto acusaba de dar descuentos a quien no
// vendió nada.
$e75 = dim(['s_engagement'=>0.75,'ventas_periodo'=>0,'ventas_sin_pago'=>0], 'engagement');
chk('75% sin ventas también avisa',   $e75['alerta'] !== null);
chk('y no lo acusa de descuentos',    str_contains($e75['frase'], 'descuento'), false);

// (4) veredicto() no tenía fila para 'sin_datos' y caía en 'estable',
// contradiciendo al renglón que acababa de decir que no hay historial.
$vsd = ScoreLectura::veredicto(51, 'sin_datos');
chk('sin_datos no dice "estable"',    str_contains($vsd, 'estable'), false);
chk('sin_datos reconoce que falta historial', str_contains($vsd, 'historial'));
foreach ([90,73,65,45] as $sc)
    chk("sin_datos tiene texto propio para {$sc}", trim(ScoreLectura::veredicto($sc, 'sin_datos')) !== '');

// (5) Los snapshots deben poder guardar aunque falte la migración: si no,
// desplegar antes de correrla congela el historial de TODAS las empresas.
$as = (string)file_get_contents(__DIR__ . '/../core/ActividadScore.php');
chk('score_diario tiene respaldo sin las columnas nuevas',
    substr_count($as, 'INSERT INTO score_diario') === 2);
chk('score_historial también',
    substr_count($as, 'INSERT INTO score_historial') === 2);

// (6) La comparación por dimensión: era código muerto (se calculaba y nadie
// la consumía). Es la razón de haber agregado las dos columnas al historial.
$prom = ['activacion'=>0.30,'engagement'=>0.85,'seguimiento'=>1.0,'radar_health'=>0.58,'conversion'=>0.13];
$hoy  = ['s_activacion'=>0.24,'s_engagement'=>0.89,'s_seguimiento'=>1.0,'s_radar_health'=>0.36,'s_conversion'=>0.11];
$mv = ScoreLectura::movimiento($hoy, $prom);
chk('detecta la caída de Radar Health', $mv[0]['clave'], 'radar_health');
chk('y la redacta',                     str_contains($mv[0]['txt'], 'cayó de 58% a 36%'));
chk('ignora los movimientos chicos',    count($mv), 1);
chk('sin historial de dimensiones, no inventa', ScoreLectura::movimiento($hoy, null), []);
// El más grande primero: es el que explica el cambio de score.
$mv2 = ScoreLectura::movimiento(['s_conversion'=>0.11,'s_radar_health'=>0.36],
                                ['conversion'=>0.60,'radar_health'=>0.58]);
chk('ordena por tamaño del movimiento', $mv2[0]['clave'], 'conversion');
chk('el reporte sí la consume',
    str_contains((string)file_get_contents(__DIR__ . '/../core/RitmoReporte.php'), 'ScoreLectura::movimiento'));

echo "\n13) EL ENUM DEL MOTOR YA USA EL ESTÁNDAR\n";
// Antes 86/61/31: el dashboard decía "Activo" a un 62 que está DEBAJO del
// estándar, y "Regular" a un 35 que es crítico. Dos veredictos para el mismo
// asesor en dos pantallas.
chk('el motor usa las constantes, no números sueltos',
    str_contains($src, 'ScoreLectura::EXCELENCIA) $nivel = \'top\''));
chk('y ya no quedan los umbrales viejos',
    (bool)preg_match('/\$score >= 86|\$score >= 61|\$score >= 31/', $src), false);
// El backfill tiene que existir y preservar el período de gracia.
$bf = (string)file_get_contents(__DIR__ . '/../migrations/backfill_niveles_estandar_70.sql');
chk('hay backfill de las filas viejas',   $bf !== '');
chk('cubre las tres tablas',              substr_count($bf, 'UPDATE `'), 3);
chk('preserva el período de gracia',      substr_count($bf, "WHEN `nivel` = 'nuevo' THEN 'nuevo'"), 3);
chk('usa los cortes nuevos',              substr_count($bf, '>= 85') === 3 && substr_count($bf, '>= 70') === 3);

echo "\n14) LO QUE SALIÓ MAL EN EL REPORTE REAL DE UN ASESOR\n";
// (a) "30 días vencidos" en una ventana de 15 días es imposible de creer, y le
// quita credibilidad a todo el reporte. mesa_vencidos tiene PK (cotizacion,
// fecha): son días-COTIZACIÓN acumulados.
$venc = dim(['s_seguimiento'=>1.0,'s_mesa'=>1.0,'mesa_pedidas'=>11,'mesa_atendidas'=>11,
             'mesa_dias_vencidos'=>30,'castigo_seguimiento'=>8], 'seguimiento');
chk('no dice "30 días vencidos" a secas', str_contains((string)$venc['alerta'], '30 días vencidos'), false);
chk('dice que son acumulados',            str_contains((string)$venc['alerta'], 'acumulados'));
chk('y explica cómo se cuentan',          str_contains((string)$venc['alerta'], 'cada cotización por cada día'));

// (b) "Completo." al lado de -8 puntos se lee como contradicción.
chk('con castigo no dice solo "Completo"', str_contains($venc['frase'], 'el problema es el retraso'));
chk('sin castigo sí dice "Completo"',
    str_contains(dim(['s_seguimiento'=>1.0,'s_mesa'=>1.0,'mesa_pedidas'=>11,'mesa_atendidas'=>11], 'seguimiento')['frase'], 'Completo.'));

// (c) Felicitar por Engagement a quien vendió 1 contra un ritmo de 3.5
// contradice el aviso que la propia Conversión imprime dos renglones abajo.
$eb = dim(['s_engagement'=>0.89,'ventas_periodo'=>1,'ventas_sin_pago'=>0,'bench_ventas'=>3.5], 'engagement');
chk('no felicita a secas al que vende poco', str_contains($eb['frase'], 'Cobras lo que vendes y no'), false);
chk('y explica de dónde viene el número',    str_contains($eb['frase'], 'no hay más que castigar'));
// Vendiendo al ritmo de la empresa, la felicitación sí es legítima.
$eok = dim(['s_engagement'=>0.89,'ventas_periodo'=>4,'ventas_sin_pago'=>0,'bench_ventas'=>3.5], 'engagement');
chk('al que sí vende, se le reconoce',       str_contains($eok['frase'], 'Cobras lo que vendes'));

echo "\n15) EL REPORTE IMPRESO CABE EN UNA HOJA\n";
// El reporte creció con las tres secciones nuevas y salía en 3 hojas.
$rr  = (string)file_get_contents(__DIR__ . '/../core/RitmoReporte.php');
$rit = (string)file_get_contents(__DIR__ . '/../modules/dashboard/_ritmo.php');
$sup = (string)file_get_contents(__DIR__ . '/../modules/supervisor/index.php');

chk('existe el CSS de impresión',      str_contains($rr, 'function css_impresion('));
// Dos columnas es la palanca grande: el reporte son viñetas cortas y a una
// columna se desperdicia media hoja de margen derecho.
chk('usa dos columnas',                str_contains($rr, 'columns:2'));
chk('el encabezado cruza las dos',     str_contains($rr, 'column-span:all'));
// Media lista arriba a la derecha y la otra media abajo a la izquierda es peor
// que gastar una hoja.
chk('ninguna sección se parte',        str_contains($rr, 'break-inside:avoid'));

// GANAR ESPACIO BORRANDO CONTENIDO NO ES GANAR. Un intento anterior escondía
// al imprimir la nota metodológica ("los pilares y el veredicto son los mismos
// de la tarjeta de Ritmo…") para recuperar tres renglones. El papel tiene que
// decir EXACTAMENTE lo mismo que la pantalla: se achica, no se esconde.
require __DIR__ . '/../core/RitmoReporte.php';
$css = RitmoReporte::css_impresion();
chk('el CSS impreso no esconde nada',  str_contains(str_replace(' ', '', $css), 'display:none'), false);

/** Resuelve el font-size final de un selector, como lo haría el navegador. */
function css_fs(string $css, string $sel): ?float {
    $out = null;
    foreach (explode('}', $css) as $blk) {
        $p = explode('{', $blk, 2);
        if (count($p) < 2) continue;
        $hit = false;
        foreach (explode(',', $p[0]) as $s) {
            if (preg_match('/(^|\s)' . preg_quote($sel, '/') . '$/', trim($s))) { $hit = true; break; }
        }
        if ($hit && preg_match('/font-size:\s*([\d.]+)px/', $p[1], $m)) $out = (float)$m[1];
    }
    return $out;
}

// EL PREFIJO NO ES COSMÉTICO. La pantalla escribe `#rt-modal .rr-x` — con ID.
// Una regla de clase pelona no le gana NUNCA, y así estuvo: la compactación
// quedó inerte salvo las columnas, y `.rr-list li` (la única sin regla con ID)
// se fue sola a 9px mientras el resto seguía en 13px. Ese desnivel era el
// "títulos grandes, letra chiquita" que se veía en el PDF.
$pelonas = [];
foreach (explode('}', $css) as $blk) {
    $p = explode('{', $blk, 2);
    if (count($p) < 2) continue;
    foreach (explode(',', $p[0]) as $s) {
        $s = trim(preg_replace('#/\*.*?\*/#s', '', $s));
        if (str_starts_with($s, '.rr-')) $pelonas[] = $s;
    }
}
chk('ninguna regla sin #rt-modal (perdería)', $pelonas, []);

// GANAR ESPACIO BORRANDO CONTENIDO NO ES GANAR: la nota se achica, no se va.
chk('conserva la nota metodológica',   css_fs($css, '.rr-note') !== null);
// Jerarquía pareja: el cuerpo por ENCIMA de los encabezados de sección, y todo
// lo que se lee al mismo tamaño. Antes convivían un 9px y un 13px en la hoja.
$cuerpo = ['.rr-list li', '.rr-pil', '.rr-tip-b', '.rr-note'];
foreach ($cuerpo as $sel) chk("$sel se lee (>= 9px)", (float)css_fs($css, $sel) >= 9.0);
chk('las viñetas superan al título de sección',
    (float)css_fs($css, '.rr-list li') > (float)css_fs($css, '.rr-st'));
chk('el nombre ya no grita (<= 14px)', (float)css_fs($css, '.rr-name') <= 14.0);
chk('viñetas, píldoras y tip miden igual',
    count(array_unique(array_map(fn($s) => css_fs($css, $s), ['.rr-list li', '.rr-pil', '.rr-tip-b']))), 1);

// EL PIE NO CRUZA LAS COLUMNAS. Cuando lo hacía se llevaba una hoja entera él
// solo: un elemento que atraviesa ambas columnas no arranca a media página, y
// si las columnas ya llegaron abajo se va completo a la siguiente. La segunda
// hoja del reporte de Manuel tenía UNA línea: este pie.
preg_match('/\.rr-foot\{([^}]*)\}/', $css, $mf);
chk('el pie no cruza las columnas', str_contains($mf[1] ?? '', 'column-span:all'), false);
// El encabezado sí debe cruzarlas: va arriba, donde siempre hay hoja.
chk('el encabezado sí las cruza',   (bool)preg_match('/\.rr-hd[^{]*\{[^}]*column-span:all/', $css));

// RECUADROS, NO FONDOS: en papel el degradado a toda página es tinta gastada y
// le baja el contraste a la letra. El borde señala lo mismo.
foreach (['.rr-tip', '.rr-consejo'] as $caja) {
    preg_match('/' . preg_quote($caja, '/') . '[^{]*\{([^}]*)\}/', $css, $mb);
    $todo = '';
    foreach (explode('}', $css) as $blk) {
        $p = explode('{', $blk, 2);
        if (count($p) === 2 && preg_match('/(^|\s)' . preg_quote($caja, '/') . '(,|$)/', $p[0] . ',')) $todo .= $p[1] . ';';
    }
    chk("$caja imprime sin fondo",  str_contains(str_replace(' ', '', $todo), 'background:none'));
    chk("$caja queda como recuadro", (bool)preg_match('/border:\s*1px/', $todo));
}

// El dashboard y el panel del supervisor arman la MISMA ventana de impresión
// por separado. Con el CSS duplicado, mejorar el papel en uno dejaba al otro
// atrás — que es justo como estaban antes.
chk('el dashboard usa el CSS compartido',  str_contains($rit, 'RitmoReporte::css_impresion()'));
chk('el supervisor también',               str_contains($sup, 'RitmoReporte::css_impresion()'));
chk('y ninguno conserva su @page viejo',
    str_contains($rit, '@page{margin:14mm}') || str_contains($sup, '@page{margin:14mm}'), false);
// Tiene que ir DESPUÉS del css del reporte o no le gana en especificidad.
foreach (['dashboard'=>$rit, 'supervisor'=>$sup] as $quien => $src2) {
    chk("en $quien el compacto va después del css base",
        strpos($src2, 'RT_CSS_PRINT +') > strpos($src2, "      css +\n"));
}

echo "\n" . ($fail === 0
    ? "✓ SCORE EN PALABRAS OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
