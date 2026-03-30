<?php
// ============================================================
//  Herramienta: Importar historial mensual desde CSVs
//  Uso: php tools/importar_historial.php <empresa_id> <quotes.csv> <invoices.csv>
//  Solo se ejecuta desde CLI (línea de comandos)
// ============================================================

if (php_sapi_name() !== 'cli') { die('Solo ejecución por CLI'); }

if ($argc < 4) {
    echo "Uso: php importar_historial.php <empresa_id> <quotes.csv> <invoices.csv>\n";
    echo "Ejemplo: php importar_historial.php 5 migrations/hmo-quote-export.csv migrations/hmo-invoice-export.csv\n";
    exit(1);
}

$empresa_id  = (int)$argv[1];
$quotes_file = $argv[2];
$invoices_file = $argv[3];

if (!$empresa_id) { die("Error: empresa_id inválido\n"); }
if (!file_exists($quotes_file)) { die("Error: archivo de cotizaciones no encontrado: {$quotes_file}\n"); }
if (!file_exists($invoices_file)) { die("Error: archivo de ventas no encontrado: {$invoices_file}\n"); }

// Bootstrap mínimo
define('COTIZAAPP', true);
require_once dirname(__DIR__) . '/config.php';

// Verificar empresa existe
$emp = DB::row("SELECT id, nombre FROM empresas WHERE id = ?", [$empresa_id]);
if (!$emp) { die("Error: empresa {$empresa_id} no encontrada\n"); }

echo "Importando historial para: {$emp['nombre']} (ID: {$empresa_id})\n\n";

// Mapa de meses en español
$meses_es = [
    'enero'=>1,'febrero'=>2,'marzo'=>3,'abril'=>4,'mayo'=>5,'junio'=>6,
    'julio'=>7,'agosto'=>8,'septiembre'=>9,'octubre'=>10,'noviembre'=>11,'diciembre'=>12
];

/**
 * Parsear fecha del CSV: "marzo 26, 2026" → [2026, 3]
 */
function parsearFechaMes(string $fecha, array $meses): ?array
{
    $fecha = trim($fecha);
    if (preg_match('/^(\w+)\s+\d+,\s+(\d{4})$/u', $fecha, $m)) {
        $mes = $meses[mb_strtolower($m[1])] ?? null;
        if ($mes) return [(int)$m[2], $mes];
    }
    return null;
}

/**
 * Parsear monto: "$85,000.00" → 85000.00
 */
function parsearMonto(string $val): float
{
    return (float)str_replace(['$', ','], '', trim($val));
}

// ─── Procesar cotizaciones ──────────────────────────────────
echo "Leyendo cotizaciones: {$quotes_file}\n";
$fq = fopen($quotes_file, 'r');
if (!$fq) { die("Error: no se pudo abrir {$quotes_file}\n"); }

$header_q = fgetcsv($fq);
$datos = []; // clave: "YYYY-MM"
$cots_total = 0;
$cots_skip = 0;

while ($row = fgetcsv($fq)) {
    $status = trim($row[6] ?? '');
    if ($status !== 'Draft' && $status !== 'Accepted') { $cots_skip++; continue; }

    $fecha = parsearFechaMes($row[7] ?? '', $meses_es);
    if (!$fecha) { $cots_skip++; continue; }

    $total = parsearMonto($row[10] ?? '0');
    $key = sprintf('%04d-%02d', $fecha[0], $fecha[1]);

    if (!isset($datos[$key])) {
        $datos[$key] = ['anio' => $fecha[0], 'mes' => $fecha[1], 'cots' => 0, 'cots_monto' => 0, 'ventas' => 0, 'ventas_monto' => 0];
    }
    $datos[$key]['cots']++;
    $datos[$key]['cots_monto'] += $total;
    $cots_total++;
}
fclose($fq);
echo "  Cotizaciones procesadas: {$cots_total} (saltadas: {$cots_skip})\n";

// ─── Procesar ventas ────────────────────────────────────────
echo "Leyendo ventas: {$invoices_file}\n";
$fi = fopen($invoices_file, 'r');
if (!$fi) { die("Error: no se pudo abrir {$invoices_file}\n"); }

$header_i = fgetcsv($fi);
$ventas_total = 0;
$ventas_skip = 0;

while ($row = fgetcsv($fi)) {
    $status = trim($row[6] ?? '');
    if ($status !== 'Draft' && $status !== 'Paid') { $ventas_skip++; continue; }

    $fecha = parsearFechaMes($row[7] ?? '', $meses_es);
    if (!$fecha) { $ventas_skip++; continue; }

    $total = parsearMonto($row[10] ?? '0');
    $key = sprintf('%04d-%02d', $fecha[0], $fecha[1]);

    if (!isset($datos[$key])) {
        $datos[$key] = ['anio' => $fecha[0], 'mes' => $fecha[1], 'cots' => 0, 'cots_monto' => 0, 'ventas' => 0, 'ventas_monto' => 0];
    }
    $datos[$key]['ventas']++;
    $datos[$key]['ventas_monto'] += $total;
    $ventas_total++;
}
fclose($fi);
echo "  Ventas procesadas: {$ventas_total} (saltadas: {$ventas_skip})\n";

// ─── Insertar en BD ─────────────────────────────────────────
ksort($datos);
echo "\nInsertando " . count($datos) . " registros mensuales...\n\n";

$insertados = 0;
$actualizados = 0;

foreach ($datos as $key => $d) {
    $tasa = $d['cots'] > 0 ? round(($d['ventas'] / $d['cots']) * 100, 2) : 0;

    $existe = DB::val(
        "SELECT id FROM historial_mensual WHERE empresa_id = ? AND anio = ? AND mes = ?",
        [$empresa_id, $d['anio'], $d['mes']]
    );

    if ($existe) {
        DB::execute(
            "UPDATE historial_mensual SET
                cotizaciones_cantidad = ?, cotizaciones_monto = ?,
                ventas_cantidad = ?, ventas_monto = ?,
                tasa_cierre = ?
             WHERE id = ?",
            [$d['cots'], $d['cots_monto'], $d['ventas'], $d['ventas_monto'], $tasa, $existe]
        );
        $actualizados++;
    } else {
        DB::execute(
            "INSERT INTO historial_mensual (empresa_id, anio, mes, cotizaciones_cantidad, cotizaciones_monto, ventas_cantidad, ventas_monto, tasa_cierre)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$empresa_id, $d['anio'], $d['mes'], $d['cots'], $d['cots_monto'], $d['ventas'], $d['ventas_monto'], $tasa]
        );
        $insertados++;
    }

    printf("  %s | Cots: %3d ($%s) | Ventas: %2d ($%s) | Tasa: %.1f%%\n",
        $key, $d['cots'], number_format($d['cots_monto'], 0),
        $d['ventas'], number_format($d['ventas_monto'], 0), $tasa
    );
}

echo "\nResultado: {$insertados} insertados, {$actualizados} actualizados\n";
echo "¡Importación completada!\n";
