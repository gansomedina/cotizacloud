<?php
// ============================================================
//  ¿Cuánto bajarían los scores al revivir las penalizaciones?
//
//  Las penalizaciones de "zona muerta" y "buckets estancados" tenían
//  umbrales FIJOS (21 y 14 días) dentro de una ventana de 15: pedían algo
//  imposible y siempre daban 0. Al volverlos proporcionales al período,
//  vuelven a cobrar... salvo que la COLUMNA que miran tampoco sirva.
//
//  Este script mide las dos cosas:
//    [1] por qué el conteo da 0 (diagnóstico de radar_updated_at)
//    [2] qué contarían las columnas correctas, asesor por asesor
//
//  SOLO LEE. No escribe nada.
//  Correr en el servidor:
//    cd /var/www/cotizacloud && php tools/medir_penalizaciones.php config.php
// ============================================================
define('COTIZAAPP', 1);
$cfg = $argv[1] ?? '/var/www/cotizacloud/config.php';
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

$PERIODO   = 15;
$d_estanc  = max(5, (int)round($PERIODO * 0.5));
$d_muerta  = max(7, (int)round($PERIODO * 0.6));

function tiene_col(string $tabla, string $col): bool {
    try {
        return (int)DB::val(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$tabla, $col]
        ) > 0;
    } catch (Throwable $e) { return false; }
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

printf("Ventana del score: %d días · umbrales proporcionales: estancados %dd · zona muerta %dd\n",
       $PERIODO, $d_estanc, $d_muerta);
printf("(antes: 14d y 21d fijos — ambos imposibles dentro de %d días)\n", $PERIODO);
if (!$hay_bucket_at) echo "AVISO: no existe cotizaciones.radar_bucket_at — la columna 'movió' se degrada.\n";
if (!$hay_vista_at)  echo "AVISO: no existe cotizaciones.ultima_vista_at — la columna 'tocó' se degrada.\n";
echo "\n";

// ─────────────────────────────────────────────────────────────
// [1] DIAGNÓSTICO: ¿por qué el conteo actual da 0?
//
// radar_updated_at se reescribe con NOW() en CADA recálculo del Radar, cambie
// o no el bucket (modules/radar/Radar.php:1495). Y el recálculo de TODA la
// empresa se dispara al abrir el dashboard o el Radar si el dato tiene más de
// 5 minutos (modules/dashboard/index.php:401, modules/radar/index.php:54).
// Traducción: mientras alguien de la empresa entre, la columna nunca envejece.
// ─────────────────────────────────────────────────────────────
echo "[1] POR QUÉ EL CONTEO ACTUAL DA 0\n";
echo "    radar_updated_at se refresca en cada recálculo del Radar, no cuando la\n";
echo "    cotización se mueve. Si alguien entra al dashboard, se refresca TODA la empresa.\n\n";

$emps = DB::query("SELECT id, nombre FROM empresas WHERE slug <> '_system' ORDER BY nombre");
foreach ($emps as $e) {
    $eid = (int)$e['id'];
    $d = DB::row(
        "SELECT COUNT(*) AS vivas,
                MAX(radar_updated_at) AS ult,
                SUM(CASE WHEN radar_updated_at < DATE_SUB(NOW(), INTERVAL $d_estanc DAY) THEN 1 ELSE 0 END) AS viejas
           FROM cotizaciones
          WHERE empresa_id = ? AND suspendida = 0 AND estado IN ('enviada','vista')
            AND created_at >= DATE_SUB(NOW(), INTERVAL $PERIODO DAY)",
        [$eid]
    );
    if (!$d || (int)$d['vivas'] === 0) continue;
    $edad = $d['ult'] ? round((time() - strtotime($d['ult'])) / 60) : null;
    printf("   %-32s %2d vivas · último recálculo: %s · con radar_updated_at > %dd: %d\n",
        mb_substr($e['nombre'], 0, 32), (int)$d['vivas'],
        $edad === null ? 'nunca' : ($edad < 90 ? "hace {$edad} min" : 'hace ' . round($edad / 60) . ' h'),
        $d_estanc, (int)$d['viejas']);
}

// ─────────────────────────────────────────────────────────────
// [2] COMPARATIVA: columna actual vs columna que sí mide el hecho
//
//   estancados  hoy → radar_updated_at  (cuándo recalculó el sistema)
//               real → radar_bucket_at  (cuándo se MOVIÓ el bucket; se escribe
//                                        solo si cambió — Radar.php:1489)
//   zona muerta hoy → radar_updated_at manda dentro del COALESCE
//               real → ultima_vista_at / updated_at (actividad HUMANA)
// ─────────────────────────────────────────────────────────────
echo "\n[2] QUÉ CONTARÍA CADA COLUMNA, POR ASESOR\n";
echo "    'hoy' = lo que cuenta el código actual · 'real' = la columna que sí mide el hecho\n\n";

$tot = ['est_hoy'=>0, 'est_real'=>0, 'mue_hoy'=>0, 'mue_real'=>0];

foreach ($emps as $e) {
    $eid = (int)$e['id'];
    $base = "c.empresa_id = ? AND COALESCE(c.vendedor_id,c.usuario_id) = u.id
             AND c.suspendida = 0 AND c.estado IN ('enviada','vista')
             AND c.created_at >= DATE_SUB(NOW(), INTERVAL $PERIODO DAY)";
    $filas = DB::query(
        "SELECT u.id, u.nombre,
                (SELECT COUNT(*) FROM cotizaciones c WHERE $base) AS asignadas,

                (SELECT COUNT(*) FROM cotizaciones c WHERE $base
                   AND c.radar_bucket IS NOT NULL AND c.radar_bucket <> 'no_abierta'
                   AND c.radar_updated_at < DATE_SUB(NOW(), INTERVAL $d_estanc DAY)) AS est_hoy,
                (SELECT COUNT(*) FROM cotizaciones c WHERE $base
                   AND c.radar_bucket IS NOT NULL AND c.radar_bucket <> 'no_abierta'
                   AND $col_estanc_real < DATE_SUB(NOW(), INTERVAL $d_estanc DAY)) AS est_real,

                (SELECT COUNT(*) FROM cotizaciones c WHERE $base
                   AND COALESCE(c.radar_updated_at, c.updated_at, c.created_at) < DATE_SUB(NOW(), INTERVAL $d_muerta DAY)) AS mue_hoy,
                (SELECT COUNT(*) FROM cotizaciones c WHERE $base
                   AND $col_muerta_real < DATE_SUB(NOW(), INTERVAL $d_muerta DAY)) AS mue_real
           FROM usuarios u
          WHERE u.empresa_id = ? AND u.activo = 1 AND COALESCE(u.rol,'') <> 'superadmin'
          ORDER BY u.nombre",
        [$eid, $eid, $eid, $eid, $eid, $eid]
    );
    $filas = array_filter($filas, fn($f) => (int)$f['asignadas'] > 0);
    if (!$filas) continue;

    echo "── {$e['nombre']}\n";
    printf("   %-22s %4s   %-18s   %-18s\n", '', 'asig', 'estancados', 'zona muerta');
    foreach ($filas as $f) {
        $as = (int)$f['asignadas'];
        $eh = (int)$f['est_hoy'];  $er = (int)$f['est_real'];
        $mh = (int)$f['mue_hoy'];  $mr = (int)$f['mue_real'];
        $tot['est_hoy'] += $eh; $tot['est_real'] += $er;
        $tot['mue_hoy'] += $mh; $tot['mue_real'] += $mr;
        printf("   %-22s %4d   hoy %2d → real %2d   hoy %2d → real %2d   (%s)\n",
            mb_substr($f['nombre'], 0, 22), $as, $eh, $er, $mh, $mr,
            $as > 0 ? sprintf('-%.0f%% Seguim. · -%.0f%% Conv.', $er / $as * 100, $mr / $as * 100) : '');
    }
    echo "\n";
}

printf("TOTAL estancados:  hoy %d → real %d\n", $tot['est_hoy'], $tot['est_real']);
printf("TOTAL zona muerta: hoy %d → real %d\n", $tot['mue_hoy'], $tot['mue_real']);
echo "\n";
echo "Cómo leerlo:\n";
echo "  · 'hoy' en 0 con 'real' > 0  → la penalización mira la columna equivocada.\n";
echo "  · ambos en 0                 → de verdad no hay cotizaciones abandonadas.\n";
echo "  · los % son el golpe a la DIMENSIÓN, no al score final:\n";
echo "    Seguimiento pesa 25% y Conversión 35% — el impacto en el 0-100 es menor.\n";
