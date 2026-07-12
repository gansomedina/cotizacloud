<?php
// Harness de fact-gates de DiagnosticoTips: las frases no contradicen el header
// (flecha de momentum, chips de dormidas/lectura). Correr tras CUALQUIER cambio
// a DiagnosticoTips: php tools/test_tips_gates.php -> debe terminar en OK. Sin BD.
define('COTIZAAPP', 1);
require __DIR__ . '/../core/DiagnosticoTips.php';

$fail = 0;
function chk($n, $ok) { global $fail; if (!$ok) $fail++; echo ($ok ? "  ✓ " : "  ✗ ") . $n . "\n"; }
function build_all(array $s): array { // todas las variantes (varía el seed vía usuario_id)
    $out = [];
    for ($u = 1; $u <= 6; $u++) { $s['usuario_id'] = $u; $out[] = DiagnosticoTips::build($s, ['close_rate' => 0.10]); }
    return $out;
}
$base = ['nivel' => 'activo', 'score' => 62, 'cot_asignadas' => 35, 'cot_vistas' => 35, 'conversiones' => 3,
    's_activacion' => 0.30, 's_engagement' => 0.55, 's_seguimiento' => 0.55, 's_radar_health' => 0.55, 's_conversion' => 0.50,
    'cot_dormidas' => 14, 'no_abiertas_5d' => 0, 'tips_score' => 0.0, 'dias_lectura' => 0, 'dias_activos' => 14,
    'momentum' => 1.20, 'cots_calientes' => 10, 'calientes_exploradas' => 8, 'radar_why_score' => 1.0,
    'eng_pen_sin_pago' => 0, 'eng_pen_descuento' => 0, 'eng_pen_bajo_benchmark' => 0, 'ventas_sin_pago' => 0,
    'ventas_periodo' => 3, 'bench_ventas' => 0, 'bonus_ticket' => 0, 'bonus_ticket_ventas' => 0, 'bonus_cierre' => 0,
    'ticket_promedio' => 0, 'health_up' => 10, 'health_down' => 2];

echo "═ CASO ABIGAIL: sin_ritmo con momentum ↑ (header dice Buen ritmo ↑) ═\n";
$outs = build_all($base);
$todo = implode(' | ', $outs);
chk('NINGUNA variante dice "tendencia a la baja"', stripos($todo, 'tendencia a la baja') === false);
chk('NINGUNA dice "bajaste el ritmo" / "se te cayó el ritmo"', stripos($todo, 'bajaste el ritmo') === false && stripos($todo, 'cayó el ritmo') === false);
chk('NINGUNA dice "marcador viene a la baja/cayendo"', stripos($todo, 'marcador viene') === false);
chk('el fallback SÍ dice los hechos (leer análisis + 14 clientes)', stripos($todo, 'análisis') !== false && strpos($todo, '14 clientes') !== false);

echo "═ sin_ritmo con momentum ↓ real (0.85): las variantes originales SÍ salen ═\n";
$s2 = $base; $s2['momentum'] = 0.85;
$outs2 = implode(' | ', build_all($s2));
chk('con ↓ sí puede decir "ritmo"/tendencia', stripos($outs2, 'ritmo') !== false);

echo "═ sin_ritmo con tips PERFECTOS (lee a diario) y mom ↓ ═\n";
$s3 = $base; $s3['momentum'] = 0.85; $s3['tips_score'] = 1.0; $s3['dias_lectura'] = 14;
$outs3 = implode(' | ', build_all($s3));
chk('NO acusa "dejaste de leer" a quien lee a diario', stripos($outs3, 'dejaste de leer') === false && stripos($outs3, 'ni revisas el análisis') === false);
chk('sí menciona las dormidas (hecho real)', strpos($outs3, '14 clientes') !== false || stripos($outs3, 'retom') !== false);

echo "═ sordo_a_senales con cobertura 100% (responde a todas) ═\n";
$s4 = $base; $s4['s_activacion'] = 0.55; $s4['s_seguimiento'] = 0.30; $s4['cots_calientes'] = 10; $s4['calientes_exploradas'] = 10;
$outs4 = implode(' | ', build_all($s4));
chk('NO dice "las ignoras" a quien respondió al 100%', stripos($outs4, 'las ignoras') === false && stripos($outs4, 'no lo trabajas') === false);
chk('reformula a calidad de lectura', stripos($outs4, 'lectura del cliente') !== false);

echo "═ motor_completo con momentum ↓ ═\n";
$s5 = $base; $s5['s_activacion'] = 0.7; $s5['s_engagement'] = 0.7; $s5['s_seguimiento'] = 0.7; $s5['s_radar_health'] = 0.7;
$s5['s_conversion'] = 0.7; $s5['momentum'] = 0.85; $s5['cot_dormidas'] = 0; $s5['tips_score'] = 1.0; $s5['cots_calientes'] = 8; $s5['calientes_exploradas'] = 8;
$outs5 = implode(' | ', build_all($s5));
chk('NO dice "van ganando/mes ganado" con flecha ↓', stripos($outs5, 'van ganando') === false && stripos($outs5, 'ya esté ganado') === false);

echo "═ desconectado repuntando (mom 1.5) ═\n";
$s6 = $base; foreach (['s_activacion','s_engagement','s_seguimiento','s_radar_health'] as $k) $s6[$k] = 0.2;
$s6['s_conversion'] = 0.3; $s6['momentum'] = 1.50; $s6['cot_dormidas'] = 3;
$outs6 = implode(' | ', build_all($s6));
chk('NO dice "te apagaste" (pretérito) a quien repunta', stripos($outs6, 'te apagaste') === false);

echo "\n" . ($fail ? "✗ $fail FALLAS" : "✓ FACT-GATES OK") . "\n";
exit($fail ? 1 : 0);
