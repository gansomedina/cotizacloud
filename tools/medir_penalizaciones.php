<?php
// ============================================================
//  ¿Cuánto bajarían los scores al revivir las penalizaciones?
//
//  Las penalizaciones de "zona muerta" y "buckets estancados" tenían
//  umbrales FIJOS (21 y 14 días) dentro de una ventana de 15: pedían algo
//  imposible y siempre daban 0. Al volverlos proporcionales al período,
//  vuelven a cobrar. Este script dice a QUIÉN y CUÁNTO, antes de aplicar.
//
//  SOLO LEE. No escribe nada.
//  Correr en el servidor:
//    php tools/medir_penalizaciones.php /var/www/cotizacloud/config.php
// ============================================================
define('COTIZAAPP', 1);
$cfg = $argv[1] ?? '/var/www/cotizacloud/config.php';
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

$PERIODO   = 15;
$d_estanc  = max(5, (int)round($PERIODO * 0.5));
$d_muerta  = max(7, (int)round($PERIODO * 0.6));

printf("Ventana del score: %d días · nuevos umbrales: estancados %dd · zona muerta %dd\n",
       $PERIODO, $d_estanc, $d_muerta);
printf("(antes: 14d y 21d fijos — ambos imposibles dentro de %d días)\n\n", $PERIODO);

$emps = DB::query("SELECT id, nombre FROM empresas WHERE slug <> '_system' ORDER BY nombre");
$tot_est = 0; $tot_mue = 0;

foreach ($emps as $e) {
    $eid = (int)$e['id'];
    $filas = DB::query(
        "SELECT u.id, u.nombre,
                (SELECT COUNT(*) FROM cotizaciones c
                  WHERE c.empresa_id = ? AND COALESCE(c.vendedor_id,c.usuario_id) = u.id
                    AND c.suspendida = 0 AND c.estado IN ('enviada','vista')
                    AND c.created_at >= DATE_SUB(NOW(), INTERVAL $PERIODO DAY)) AS asignadas,
                (SELECT COUNT(*) FROM cotizaciones c
                  WHERE c.empresa_id = ? AND COALESCE(c.vendedor_id,c.usuario_id) = u.id
                    AND c.suspendida = 0 AND c.estado IN ('enviada','vista')
                    AND c.radar_bucket IS NOT NULL AND c.radar_bucket <> 'no_abierta'
                    AND c.created_at >= DATE_SUB(NOW(), INTERVAL $PERIODO DAY)
                    AND c.radar_updated_at < DATE_SUB(NOW(), INTERVAL $d_estanc DAY)) AS estancados,
                (SELECT COUNT(*) FROM cotizaciones c
                  WHERE c.empresa_id = ? AND COALESCE(c.vendedor_id,c.usuario_id) = u.id
                    AND c.suspendida = 0 AND c.estado IN ('enviada','vista')
                    AND COALESCE(c.radar_updated_at, c.updated_at, c.created_at) < DATE_SUB(NOW(), INTERVAL $d_muerta DAY)
                    AND c.created_at >= DATE_SUB(NOW(), INTERVAL $PERIODO DAY)) AS zona_muerta
           FROM usuarios u
          WHERE u.empresa_id = ? AND u.activo = 1 AND COALESCE(u.rol,'') <> 'superadmin'
          ORDER BY u.nombre",
        [$eid, $eid, $eid, $eid]
    );
    $filas = array_filter($filas, fn($f) => (int)$f['asignadas'] > 0);
    if (!$filas) continue;

    echo "── {$e['nombre']}\n";
    foreach ($filas as $f) {
        $as = (int)$f['asignadas']; $es = (int)$f['estancados']; $zm = (int)$f['zona_muerta'];
        $tot_est += $es; $tot_mue += $zm;
        // pen_buckets resta directo de Seguimiento (solo rama SIN mesa);
        // pen_zona_muerta entra a Conversión (peso 35% del score final)
        $pb = $as > 0 ? $es / $as : 0;
        $pz = $as > 0 ? $zm / $as : 0;
        printf("   %-22s asignadas %2d · estancados %2d (-%.0f%% Seguim.) · zona muerta %2d (-%.0f%% Conv.)\n",
               $f['nombre'], $as, $es, $pb * 100, $zm, $pz * 100);
    }
    echo "\n";
}
printf("TOTAL: %d cotizaciones estancadas · %d en zona muerta\n", $tot_est, $tot_mue);
echo "\nSi ambos totales son 0, revivir las penalizaciones NO mueve ningún score.\n";
echo "Los porcentajes son el golpe a la DIMENSIÓN, no al score final:\n";
echo "  Seguimiento pesa 25% y Conversión 35% — el impacto en el 0-100 es menor.\n";
