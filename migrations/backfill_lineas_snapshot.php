<?php
// ============================================================
//  Backfill — snapshot de cotizaciones ya cerradas
//  Toma el snapshot de las cotizaciones aceptada/convertida que aún
//  no lo tienen. Correr UNA vez, DESPUÉS de add_lineas_snapshot.sql.
//  Ejecutar: php migrations/backfill_lineas_snapshot.php
// ============================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/DB.php';

$cerradas = DB::query(
    "SELECT id, descuento_auto_amt FROM cotizaciones
     WHERE estado IN ('aceptada','convertida')
       AND (lineas_snapshot IS NULL OR lineas_snapshot = '')"
);
echo 'Cotizaciones cerradas sin snapshot: ' . count($cerradas) . "\n";

$n = 0;
foreach ($cerradas as $c) {
    $lineas = DB::query(
        "SELECT * FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden ASC",
        [(int)$c['id']]
    );
    $snap = [
        'lineas'             => $lineas,
        'descuento_auto_amt' => (float)($c['descuento_auto_amt'] ?? 0),
    ];
    DB::execute(
        "UPDATE cotizaciones SET lineas_snapshot = ? WHERE id = ?",
        [json_encode($snap, JSON_UNESCAPED_UNICODE), (int)$c['id']]
    );
    $n++;
}
echo "Snapshot tomado a {$n} cotizaciones.\n";
