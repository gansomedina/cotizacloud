<?php
// ============================================================
//  ¿Cuánto bajarían los scores al arreglar las penalizaciones?
//
//  Dos problemas encadenados:
//   (a) los umbrales eran FIJOS (21 y 14 días) dentro de una ventana de 15:
//       pedían algo imposible y siempre daban 0. Ya se hicieron proporcionales.
//   (b) la COLUMNA que miran tampoco sirve: radar_updated_at se refresca en
//       CADA recálculo del Radar (Radar.php:1495) y el recálculo de toda la
//       empresa se dispara al abrir el dashboard (dashboard/index.php:401).
//       Nunca envejece → el conteo sigue en 0.
//
//  Este script mide, empresa por empresa y asesor por asesor:
//    [1] por qué el conteo actual da 0
//    [2] qué contaría la columna correcta y cuánto costaría en el score
//
//  CADA EMPRESA USA SU PROPIO PERÍODO. El score alarga la ventana cuando el
//  ciclo de venta es largo (ActividadScore::periodo_efectivo). Medir con 15
//  días fijos mientras el motor usa 45 da razones falsas: numerador de una
//  ventana y denominador de otra.
//
//  SOLO LEE. No escribe nada.
//  Correr en el servidor:
//    cd /var/www/cotizacloud && php tools/medir_penalizaciones.php config.php
// ============================================================
define('COTIZAAPP', 1);
$cfg = $argv[1] ?? '/var/www/cotizacloud/config.php';
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

$PERIODO_BASE = 15;
$HAY_SCORE = class_exists('ActividadScore');

// printf pad por BYTES; '→' y los acentos ocupan 2-3 bytes y descuadran la
// tabla. Este pad cuenta CARACTERES.
function pad(string $s, int $n, bool $izq = true): string {
    $f = max(0, $n - mb_strlen($s));
    return $izq ? $s . str_repeat(' ', $f) : str_repeat(' ', $f) . $s;
}

function tiene_col(string $tabla, string $col): bool {
    try {
        return (int)DB::val(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$tabla, $col]
        ) > 0;
    } catch (Throwable $e) { return false; }
}

// El MISMO período que usa el motor para esta empresa. Si no se puede leer,
// se cae al base y se avisa en pantalla — no se finge precisión.
function periodo_de(int $eid, int $base): array {
    if (class_exists('ActividadScore') && method_exists('ActividadScore', 'periodo_efectivo')) {
        try { return [max(1, (int)ActividadScore::periodo_efectivo($eid)), true]; }
        catch (Throwable $e) {}
    }
    return [$base, false];
}

$hay_bucket_at = tiene_col('cotizaciones', 'radar_bucket_at');
$hay_vista_at  = tiene_col('cotizaciones', 'ultima_vista_at');

// Columna alterna para cada penalización. Si la columna correcta no existe en
// este servidor, se degrada a la actual y se avisa — nunca se inventa el dato.
$col_estanc_real = $hay_bucket_at
    ? "COALESCE(c.radar_bucket_at, c.created_at)"
    : "c.radar_updated_at";
$col_muerta_real = $hay_vista_at
    ? "COALESCE(c.ultima_vista_at, c.updated_at, c.created_at)"
    : "COALESCE(c.updated_at, c.created_at)";

echo "Cada empresa usa SU período (el score lo alarga si el ciclo de venta es largo).\n";
echo "Umbrales proporcionales: estancados = 50% del período · zona muerta = 60%.\n";
if (!$hay_bucket_at) echo "AVISO: no existe cotizaciones.radar_bucket_at — la columna 'movió' se degrada.\n";
if (!$hay_vista_at)  echo "AVISO: no existe cotizaciones.ultima_vista_at — la columna 'tocó' se degrada.\n";
if (!$HAY_SCORE)     echo "AVISO: ActividadScore no cargado — período base y sin proyección de score.\n";
echo "\n";

$emps = DB::query("SELECT id, nombre FROM empresas WHERE slug <> '_system' ORDER BY nombre");

// ─────────────────────────────────────────────────────────────
// [1] ¿Por qué el conteo actual da 0?
//
// Si el último recálculo del Radar fue hace minutos y ninguna cotización
// tiene radar_updated_at más viejo que el umbral, queda probado que la
// columna no envejece mientras la empresa se use.
// ─────────────────────────────────────────────────────────────
echo "[1] POR QUÉ EL CONTEO ACTUAL DA 0\n";
echo "    radar_updated_at se refresca en cada recálculo del Radar, no cuando la\n";
echo "    cotización se mueve. Si alguien entra al dashboard, se refresca TODA la empresa.\n\n";

foreach ($emps as $e) {
    $eid = (int)$e['id'];
    [$p, ] = periodo_de($eid, $PERIODO_BASE);
    $d_est = max(5, (int)round($p * 0.5));
    $d = DB::row(
        "SELECT COUNT(*) AS vivas, MAX(radar_updated_at) AS ult,
                SUM(CASE WHEN radar_updated_at < DATE_SUB(NOW(), INTERVAL $d_est DAY) THEN 1 ELSE 0 END) AS viejas
           FROM cotizaciones
          WHERE empresa_id = ? AND suspendida = 0 AND estado IN ('enviada','vista')
            AND created_at >= DATE_SUB(NOW(), INTERVAL $p DAY)",
        [$eid]
    );
    if (!$d || (int)$d['vivas'] === 0) continue;
    $edad = $d['ult'] ? round((time() - strtotime($d['ult'])) / 60) : null;
    printf("   %-30s %2d vivas · último recálculo: %-14s · con radar_updated_at > %dd: %d\n",
        mb_substr($e['nombre'], 0, 30), (int)$d['vivas'],
        $edad === null ? 'nunca' : ($edad < 90 ? "hace {$edad} min" : 'hace ' . round($edad / 60) . ' h'),
        $d_est, (int)$d['viejas']);
}

// ─────────────────────────────────────────────────────────────
// [2] Qué contaría la columna correcta, y qué costaría
//
// El conteo crudo NO es la penalización. core/ActividadScore.php:790:
//     pen_zona_muerta = (zona_muerta / asignadas) * sqrt(1 / close_rate)
// El sqrt AMPLIFICA cuando cerrar es raro en esa empresa. Después:
//     pen_conversion = min(pen_zona + pen_volumen, 1.0)          (:799)
//     s_conversion   = max(conv_floor, componentes - pen_conv)   (:831)
// conv_floor (:830) puede absorber parte del golpe para quien cierra por
// encima del histórico de su empresa → lo de abajo es el TECHO del daño.
//
// asignadas y s_conv salen de usuario_score (lo que el motor ya calculó),
// y el conteo usa el MISMO período de esa empresa. Mismo universo en el
// numerador y en el denominador.
// ─────────────────────────────────────────────────────────────
echo "\n[2] QUÉ CONTARÍA LA COLUMNA CORRECTA Y QUÉ COSTARÍA\n";
echo "    'hoy → real' = lo que cuenta el código actual → lo que contaría bien.\n";
echo "    pen = (zona muerta / asignadas) × sqrt(1 / close_rate). s_conv y score son los guardados.\n\n";

$tot = ['est_hoy'=>0, 'est_real'=>0, 'mue_hoy'=>0, 'mue_real'=>0];

foreach ($emps as $e) {
    $eid = (int)$e['id'];
    [$p, $p_ok] = periodo_de($eid, $PERIODO_BASE);
    $d_est = max(5, (int)round($p * 0.5));
    $d_mue = max(7, (int)round($p * 0.6));

    $cr = 0.0;
    if ($HAY_SCORE && method_exists('ActividadScore', 'bench_publico')) {
        $b = ActividadScore::bench_publico($eid);
        if (isset($b['close_rate'])) $cr = (float)$b['close_rate'];
    }
    $amp = sqrt(1.0 / max($cr, 0.01));

    // Conteos por asesor, todos dentro del período de ESTA empresa
    $base = "c.empresa_id = ? AND COALESCE(c.vendedor_id,c.usuario_id) = u.id
             AND c.suspendida = 0 AND c.estado IN ('enviada','vista')
             AND c.created_at >= DATE_SUB(NOW(), INTERVAL $p DAY)";
    try {
        $filas = DB::query(
            "SELECT u.id AS uid, u.nombre,
                    (SELECT COUNT(*) FROM cotizaciones c WHERE $base) AS asignadas,
                    (SELECT COUNT(*) FROM cotizaciones c WHERE $base
                       AND c.radar_bucket IS NOT NULL AND c.radar_bucket <> 'no_abierta'
                       AND c.radar_updated_at < DATE_SUB(NOW(), INTERVAL $d_est DAY)) AS est_hoy,
                    (SELECT COUNT(*) FROM cotizaciones c WHERE $base
                       AND c.radar_bucket IS NOT NULL AND c.radar_bucket <> 'no_abierta'
                       AND $col_estanc_real < DATE_SUB(NOW(), INTERVAL $d_est DAY)) AS est_real,
                    (SELECT COUNT(*) FROM cotizaciones c WHERE $base
                       AND COALESCE(c.radar_updated_at, c.updated_at, c.created_at) < DATE_SUB(NOW(), INTERVAL $d_mue DAY)) AS mue_hoy,
                    (SELECT COUNT(*) FROM cotizaciones c WHERE $base
                       AND $col_muerta_real < DATE_SUB(NOW(), INTERVAL $d_mue DAY)) AS mue_real
               FROM usuarios u
              WHERE u.empresa_id = ? AND u.activo = 1 AND COALESCE(u.rol,'') <> 'superadmin'
              ORDER BY u.nombre",
            [$eid, $eid, $eid, $eid, $eid, $eid]
        );
    } catch (Throwable $ex) { continue; }

    // Lo que el motor guardó, para proyectar sobre su propio número
    $sc = [];
    try {
        foreach (DB::query(
            "SELECT usuario_id, cot_asignadas, s_conversion, score FROM usuario_score WHERE empresa_id = ?",
            [$eid]) as $r) { $sc[(int)$r['usuario_id']] = $r; }
    } catch (Throwable $ex) {}

    $filas = array_filter($filas, fn($f) => (int)$f['asignadas'] > 0);
    if (!$filas) continue;

    printf("── %s   período %dd%s · zona muerta ≥%dd · close_rate %.0f%% (amplifica ×%.2f)\n",
        $e['nombre'], $p, $p_ok ? '' : ' (base, no leído)', $d_mue, $cr * 100, $amp);
    echo '   ' . pad('ASESOR', 20) . pad('asign', 6, false) . '  '
       . pad('estancados', 12) . pad('zona muerta', 13) . pad('pen', 6, false) . '  '
       . pad('s_conv', 14) . pad('score', 6, false) . "\n";

    foreach ($filas as $f) {
        $uid = (int)$f['uid'];
        $as  = (int)$f['asignadas'];
        $eh = (int)$f['est_hoy'];  $er = (int)$f['est_real'];
        $mh = (int)$f['mue_hoy'];  $mr = (int)$f['mue_real'];
        $tot['est_hoy'] += $eh; $tot['est_real'] += $er;
        $tot['mue_hoy'] += $mh; $tot['mue_real'] += $mr;

        // El denominador de la penalización es el que usó el motor. Si no hay
        // fila guardada, se usa el conteo propio y se marca con *.
        $den = isset($sc[$uid]) ? max(1, (int)$sc[$uid]['cot_asignadas']) : $as;
        $marca = isset($sc[$uid]) ? '' : '*';
        $pen = min(($mr / $den) * $amp, 1.0);
        $sconv = isset($sc[$uid]) && $sc[$uid]['s_conversion'] !== null ? (float)$sc[$uid]['s_conversion'] : null;
        $score = $sc[$uid]['score'] ?? null;

        echo '   ' . pad(mb_substr($f['nombre'], 0, 20), 20) . pad($den . $marca, 6, false) . '  '
           . pad("$eh → $er", 12) . pad("$mh → $mr", 13) . pad(number_format($pen, 2), 6, false) . '  '
           . pad($sconv === null ? '—' : sprintf('%.2f → %.2f', $sconv, max(0.0, $sconv - $pen)), 14)
           . pad((string)($score ?? '—'), 6, false) . "\n";
    }
    echo "\n";
}

printf("TOTAL estancados:  hoy %d → real %d\n", $tot['est_hoy'], $tot['est_real']);
printf("TOTAL zona muerta: hoy %d → real %d\n", $tot['mue_hoy'], $tot['mue_real']);
echo "\n";
echo "Cómo leerlo:\n";
echo "  · 'hoy 0 → real N' con N>0 → la penalización mira la columna equivocada.\n";
echo "  · ambos en 0               → de verdad no hay cotizaciones abandonadas.\n";
echo "  · pen es el TECHO del daño a la Conversión; conv_floor puede absorber parte.\n";
echo "  · Conversión pesa 35% del score: una caída de 0.30 en s_conv son ~10 puntos.\n";
echo "  · 'asign' es el cot_asignadas del motor (ActividadScore.php:253): TODO lo que\n";
echo "    salió de borrador, incluidas aceptadas y rechazadas. Es el denominador real\n";
echo "    de la penalización. '*' = sin fila en usuario_score; se usó el conteo propio.\n";
