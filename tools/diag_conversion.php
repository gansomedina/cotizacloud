<?php
// ============================================================
//  DIAGNÓSTICO del pilar CONVERSIÓN de la tarjeta de Ritmo
//
//  Existe porque la tarjeta imprime "cerró 2 de 12 abiertas (0%)" — tres
//  números que salen de TRES cuentas distintas, y no hay forma de ver cuál
//  es cuál sin abrir el código.
//
//  Este script NO decide nada. Pone los candidatos lado a lado, con datos
//  reales, para que la decisión se tome viendo los números:
//
//    HOY      — lo que la tarjeta dice literalmente (RitmoAsesor.php:152)
//    CASA     — la definición que usan los otros 5 módulos: cierres del
//               período (por accion_at, con pago) ÷ cotizaciones del período
//    CASA+MAD — igual, pero excluyendo del denominador las cotizaciones que
//               todavía no cumplen la mediana del ciclo
//    COHORTE  — lo que hoy calcula el porcentaje (nacidas Y cerradas dentro)
//
//  Y mide las dos cosas que cambian el veredicto sin importar el texto:
//    (a) ACEPTADAS SIN ANTICIPO — la cotización ya vendió pero la venta trae
//        pagado=0. Entra al denominador y falla el numerador: cuenta como
//        fracaso una venta hecha.
//    (b) ASIMETRÍA DE MADUREZ — la edad de las cotizaciones del asesor vs la
//        edad de las cotizaciones con las que se le compara.
//
//  SOLO LEE. No escribe en ninguna tabla.
//    cd /var/www/cotizacloud && php tools/diag_conversion.php config.php
// ============================================================
define('COTIZAAPP', 1);
$cfg = $argv[1] ?? '/var/www/cotizacloud/config.php';
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

if (!defined('ROOT_PATH'))    define('ROOT_PATH', dirname(__DIR__));
if (!defined('CORE_PATH'))    define('CORE_PATH', ROOT_PATH . '/core');
if (!defined('MODULES_PATH')) define('MODULES_PATH', ROOT_PATH . '/modules');
foreach (['DB', 'Helpers', 'ActividadScore'] as $c) {
    if (!class_exists($c) && is_file(CORE_PATH . "/$c.php")) require_once CORE_PATH . "/$c.php";
}

// ── Filtros, copiados de su origen (si el motor cambia, esto queda viejo) ──
// RitmoAsesor::_cohorte() :337-355
$ABIERTA = "c.total > 0 AND c.suspendida = 0 AND c.estado != 'borrador'
            AND (c.visitas > 0 OR c.estado IN ('aceptada','convertida','aceptada_cliente'))";
// ActividadScore :304-308  (cot_vistas — el denominador de la casa)
$VISTA   = "c.total > 0 AND c.suspendida = 0
            AND (c.estado IN ('vista','aceptada','convertida','aceptada_cliente') OR c.visitas > 0)";
$PAGADA  = "EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = c.id
                    AND v.pagado > 0 AND v.estado <> 'cancelada')";
$SIN_DI  = "NOT EXISTS (SELECT 1 FROM desc_int_activaciones di
                        WHERE di.cotizacion_id = c.id AND di.estado = 'utilizado')";

$pct = fn($n, $d) => $d > 0 ? round($n / $d * 100) : 0;

$emps = DB::query("SELECT id, nombre FROM empresas WHERE mesa_activa >= 1 AND slug <> '_system' ORDER BY nombre");
if (!$emps) { echo "Ninguna empresa con mesa activa.\n"; exit(0); }

echo "DIAGNÓSTICO — pilar CONVERSIÓN de la tarjeta de Ritmo\n";
echo "Fecha: " . date('Y-m-d H:i') . "   ·   SOLO LECTURA\n";
echo str_repeat("=", 78) . "\n";

foreach ($emps as $e) {
    $eid = (int)$e['id'];

    // Ventana de la tarjeta: 2 × p75 del ciclo de venta (RitmoAsesor::empresa)
    $p75 = 10; $mediana = 5;
    try {
        if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
        $c = Radar::ciclo_venta($eid);
        if (!empty($c['auto'])) {
            if (!empty($c['p75']))     $p75     = max(3, (int)$c['p75']);
            if (!empty($c['mediana'])) $mediana = max(1, (int)$c['mediana']);
        }
    } catch (Throwable $ex) {}
    $win = 2 * $p75;

    // La vara que YA existe y nadie llama (ActividadScore:120)
    $cr_hist = ['rate' => 0.0, 'muestra' => 0];
    try { $cr_hist = ActividadScore::close_rate_historico($eid); } catch (Throwable $ex) {}
    $periodo_score = 15;
    try { $periodo_score = (int)ActividadScore::periodo_efectivo($eid); } catch (Throwable $ex) {}

    // Vara que usa la tarjeta HOY: cohorte de toda la empresa, historia anterior
    $hr = DB::row(
        "SELECT COUNT(*) AS ab, COALESCE(SUM($PAGADA),0) AS ce
           FROM cotizaciones c
          WHERE c.empresa_id = ? AND $ABIERTA
            AND c.created_at >= NOW() - INTERVAL " . ($win * 8) . " DAY
            AND c.created_at <= NOW() - INTERVAL $win DAY
            AND c.created_at <= NOW() - INTERVAL $mediana DAY", [$eid]);
    $h_ab = (int)($hr['ab'] ?? 0); $h_ce = (int)($hr['ce'] ?? 0);

    echo "\n" . str_repeat("─", 78) . "\n";
    printf("%s  (id %d)\n", $e['nombre'], $eid);
    printf("  ciclo: p75=%dd  mediana=%dd   →  ventana de la tarjeta = %dd\n", $p75, $mediana, $win);
    printf("  VARA de la tarjeta HOY (cohorte histórica): %d%%  (%d de %d, nacidas hace %d-%dd)\n",
        $pct($h_ce, $h_ab), $h_ce, $h_ab, $win, $win * 8);
    printf("  VARA de la CASA, ya escrita y sin usar (close_rate_historico): %d%%  (muestra %d, período del score %dd)\n",
        round($cr_hist['rate'] * 100), $cr_hist['muestra'], $periodo_score);
    echo str_repeat("─", 78) . "\n";

    $asesores = DB::query(
        "SELECT DISTINCT u.id, u.nombre
           FROM usuarios u
           JOIN cotizaciones c ON COALESCE(c.vendedor_id, c.usuario_id) = u.id
          WHERE u.empresa_id = ? AND u.activo = 1 AND u.rol <> 'superadmin'
            AND c.empresa_id = ? AND c.created_at >= NOW() - INTERVAL 120 DAY
          ORDER BY u.nombre", [$eid, $eid]);

    foreach ($asesores as $a) {
        $uid = (int)$a['id'];
        $P   = [$eid, $uid];
        $MIO = "c.empresa_id = ? AND COALESCE(c.vendedor_id, c.usuario_id) = ?";

        // ── 1. Lo que la tarjeta IMPRIME hoy ──
        // numerador: ventas del período, por ventas.created_at (RitmoAsesor::_cierres)
        $n_ventas = (int)DB::val(
            "SELECT COUNT(*) FROM ventas v
               LEFT JOIN cotizaciones c ON c.id = v.cotizacion_id
              WHERE v.empresa_id = ? AND v.estado <> 'cancelada' AND v.pagado > 0 AND v.total > 0
                AND v.created_at >= NOW() - INTERVAL $win DAY
                AND COALESCE(v.vendedor_id, v.usuario_id, c.vendedor_id, c.usuario_id) = ?", $P);

        // denominador + porcentaje: cohorte (nacidas en la ventana, ya maduras)
        $cr = DB::row(
            "SELECT COUNT(*) AS ab, COALESCE(SUM($PAGADA),0) AS ce
               FROM cotizaciones c
              WHERE $MIO AND $ABIERTA
                AND c.created_at >= NOW() - INTERVAL $win DAY
                AND c.created_at <= NOW() - INTERVAL $mediana DAY", $P);
        $coh_ab = (int)($cr['ab'] ?? 0); $coh_ce = (int)($cr['ce'] ?? 0);

        // ── 2. La definición de la CASA ──
        // numerador: cotizaciones aceptadas EN el período (accion_at) con pago
        $n_casa = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones c
              WHERE $MIO AND c.estado IN ('aceptada','convertida','aceptada_cliente')
                AND c.accion_at >= NOW() - INTERVAL $win DAY
                AND $PAGADA AND $SIN_DI", $P);
        // denominador: cotizaciones del período (sin filtro de madurez)
        $d_casa = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones c
              WHERE $MIO AND $VISTA
                AND c.created_at >= NOW() - INTERVAL $win DAY", $P);
        // denominador con madurez
        $d_mad = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones c
              WHERE $MIO AND $VISTA
                AND c.created_at >= NOW() - INTERVAL $win DAY
                AND c.created_at <= NOW() - INTERVAL $mediana DAY", $P);

        // ── 3. El bug del anticipo ──
        // aceptada, con venta creada, pero la venta trae pagado = 0
        $sin_anticipo = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones c
              WHERE $MIO AND $ABIERTA
                AND c.created_at >= NOW() - INTERVAL $win DAY
                AND c.estado IN ('aceptada','convertida','aceptada_cliente')
                AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = c.id AND v.estado <> 'cancelada')
                AND NOT $PAGADA", $P);

        // ── 4. Asimetría de madurez ──
        $edad = DB::row(
            "SELECT MIN(DATEDIFF(NOW(), c.created_at)) AS mn,
                    ROUND(AVG(DATEDIFF(NOW(), c.created_at))) AS av
               FROM cotizaciones c
              WHERE $MIO AND $ABIERTA
                AND c.created_at >= NOW() - INTERVAL $win DAY
                AND c.created_at <= NOW() - INTERVAL $mediana DAY", $P);

        if ($coh_ab === 0 && $d_casa === 0 && $n_ventas === 0 && $n_casa === 0) continue;

        // ── Frases ──
        $r_coh  = $pct($coh_ce, $coh_ab);
        $r_casa = $d_casa > 0 ? min(round($n_casa / $d_casa * 100), 100) : 0;
        $r_mad  = $d_mad  > 0 ? min(round($n_casa / $d_mad  * 100), 100) : 0;

        printf("\n  %s  (uid %d)\n", $a['nombre'], $uid);
        printf("    HOY  la tarjeta dice ....... \"cerró %d de %d abiertas (%d%%)\"\n", $n_ventas, $coh_ab, $r_coh);
        printf("         · el %d sale de ventas del período (ventas.created_at)\n", $n_ventas);
        printf("         · el %d sale de cotizaciones nacidas en la ventana\n", $coh_ab);
        printf("         · el %d%% sale de %d nacidas-Y-cerradas ÷ %d   ← tercera cuenta\n", $r_coh, $coh_ce, $coh_ab);
        printf("    CASA     cierres %d ÷ cotizaciones %d = %d%%   (accion_at, con pago, sin DI)\n", $n_casa, $d_casa, $r_casa);
        printf("    CASA+MAD cierres %d ÷ cotizaciones %d = %d%%   (excluye las de menos de %dd)\n", $n_casa, $d_mad, $r_mad, $mediana);
        if ($n_casa > $d_casa) {
            printf("         ⚠ el numerador PASA al denominador (%d > %d): sin tope diría %d%%\n",
                $n_casa, $d_casa, $pct($n_casa, $d_casa));
        }
        if ($sin_anticipo > 0) {
            printf("    ⚠ ANTICIPO  %d cotización(es) ACEPTADA(S) con venta creada pero pagado=0\n", $sin_anticipo);
            printf("                entran al denominador y fallan el numerador: venta hecha contada como fracaso\n");
        }
        printf("    MADUREZ  sus cotizaciones tienen %d-%dd (promedio %dd) · la vara las tiene de %dd en adelante\n",
            (int)($edad['mn'] ?? 0), $win, (int)($edad['av'] ?? 0), $win);
    }
}

echo "\n" . str_repeat("=", 78) . "\n";
echo "Cómo leerlo:\n";
echo "  · Si HOY el primer número y el porcentaje no concuerdan, es el renglón 152.\n";
echo "  · CASA vs CASA+MAD decide si una cotización de anteayer cuenta como fracaso.\n";
echo "  · Cada ⚠ ANTICIPO es una venta REAL que hoy se está contando como fracaso.\n";
echo "  · Las dos VARAS de arriba deben parecerse; si no, es que se comparan recetas distintas.\n";
