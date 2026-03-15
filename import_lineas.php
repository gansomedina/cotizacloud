<?php
/**
 * Genera SQL para importar cotizacion_lineas desde wppl_postmeta.csv
 * Los items están en PHP serialized format de Sliced Invoices.
 *
 * USO: php import_lineas.php > import_lineas.sql
 */

$csv_file = __DIR__ . '/wppl_postmeta.csv';
$sql_file = __DIR__ . '/import_all_cotizaciones.sql';
$out_file = __DIR__ . '/import_lineas.sql';

if (!file_exists($csv_file)) die("No se encuentra $csv_file\n");
if (!file_exists($sql_file)) die("No se encuentra $sql_file\n");

// Build wp_id -> exact slug map from import SQL
$slug_map = [];
preg_match_all('/imp-quo-(\d+)-(\d+)/', file_get_contents($sql_file), $m, PREG_SET_ORDER);
foreach ($m as $match) {
    $wp = $match[2];
    $slug = $match[0];
    if (!isset($slug_map[$wp])) $slug_map[$wp] = $slug;
}
echo "Loaded " . count($slug_map) . " slug mappings\n";

$fh = fopen($csv_file, 'r');
$header = fgetcsv($fh); // wp_id, items_data

$all_inserts = [];
$total_items = 0;
$total_quotes = 0;
$errors = [];

while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < 2) continue;
    $wp_id = trim($row[0]);
    $raw = trim($row[1]);
    if (!$wp_id || !$raw) continue;

    // The CSV uses double-double-quotes for escaped quotes
    // PHP serialized format: a:N:{...}
    // Fix: CSV escapes " as "" — the fgetcsv already handles this

    $items = @unserialize($raw);
    if ($items === false && $raw !== 'b:0;') {
        // Try fixing common serialization issues
        $items = @unserialize(stripslashes($raw));
    }

    if (!is_array($items) || empty($items)) {
        $errors[] = "WP ID $wp_id: no se pudo parsear items";
        continue;
    }

    $total_quotes++;

    foreach ($items as $orden => $item) {
        $qty = (float)($item['qty'] ?? 1);
        $title = $item['title'] ?? 'Sin título';
        $amount = (float)($item['amount'] ?? 0);
        $desc = $item['description'] ?? '';

        // Strip HTML tags from description, keep text
        $desc = strip_tags($desc);
        $desc = preg_replace('/\s+/', ' ', trim($desc));

        // Calculate subtotal: qty * amount
        $subtotal = round($qty * $amount, 2);

        $all_inserts[] = [
            'wp_id' => $wp_id,
            'orden' => $orden,
            'title' => $title,
            'desc' => $desc,
            'qty' => $qty,
            'amount' => $amount,
            'subtotal' => $subtotal,
        ];
        $total_items++;
    }
}
fclose($fh);

// Generate SQL
$sql = "-- ============================================================\n";
$sql .= "--  CotizaCloud — Import cotizacion_lineas from WordPress\n";
$sql .= "--  Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "--  Total: $total_items items from $total_quotes cotizaciones\n";
$sql .= "-- ============================================================\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

// We need to match wp_id to cotizacion.id via slug pattern imp-quo-%-{wp_id}
// Use INSERT ... SELECT to join with cotizaciones table

$sql .= "-- Delete existing imported lines to avoid duplicates\n";
$sql .= "DELETE cl FROM cotizacion_lineas cl\n";
$sql .= "INNER JOIN cotizaciones c ON c.id = cl.cotizacion_id\n";
$sql .= "WHERE c.empresa_id = 2 AND c.slug LIKE 'imp-quo-%';\n\n";

// Group inserts by wp_id
$by_wp = [];
foreach ($all_inserts as $ins) {
    $by_wp[$ins['wp_id']][] = $ins;
}

$skipped = [];
foreach ($by_wp as $wp_id => $items) {
    if (!isset($slug_map[$wp_id])) {
        $skipped[] = $wp_id;
        continue;
    }
    $slug = $slug_map[$wp_id];
    $sql .= "-- WP ID: $wp_id / slug: $slug (" . count($items) . " items)\n";

    foreach ($items as $item) {
        $title_esc = addslashes($item['title']);
        $desc_esc = addslashes($item['desc']);
        if (strlen($desc_esc) > 5000) $desc_esc = substr($desc_esc, 0, 5000);

        $sql .= "INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)\n";
        $sql .= "SELECT c.id, NULL, NULL, {$item['orden']}, '{$title_esc}', '{$desc_esc}', NULL, {$item['qty']}, {$item['amount']}, {$item['subtotal']}\n";
        $sql .= "FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = '{$slug}';\n";
    }
    $sql .= "\n";
}

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

$sql .= "-- Summary: $total_items items from $total_quotes cotizaciones\n";

if (!empty($errors)) {
    $sql .= "-- PARSE ERRORS:\n";
    foreach ($errors as $e) $sql .= "-- $e\n";
}
if (!empty($skipped)) {
    $sql .= "-- SKIPPED (no slug match): WP IDs " . implode(', ', $skipped) . "\n";
}

file_put_contents($out_file, $sql);
echo "Generated $out_file\n";
echo "Total: $total_items items from $total_quotes quotes\n";
if (!empty($errors)) {
    echo "\nParse errors (" . count($errors) . "):\n";
    foreach ($errors as $e) echo "  - $e\n";
}
if (!empty($skipped)) {
    echo "\nSkipped WP IDs (no slug match): " . implode(', ', $skipped) . "\n";
}
