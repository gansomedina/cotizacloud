<?php
/**
 * CotizaCloud — Import v2 (from WordPress CSV exports)
 *
 * Sources:
 *   - quote-export-2026-03-16.csv      (cotizaciones: number, title, status, date, total)
 *   - invoice-export-2026-03-16.csv    (ventas: number, title, status, date, total)
 *   - wppl_postmeta.csv                (líneas: wp_id, serialized items)
 *   - /tmp/wppl_posts.csv              (wp_id → numero mapping for líneas)
 *
 * Outputs:
 *   - import_v2_cotizaciones.sql
 *   - import_v2_lineas.sql
 *   - import_v2_ventas.sql
 */

$EMPRESA_ID  = 2;
$CLIENTE_ID  = 4;
$USUARIO_ID  = 2;

$quotesCsv   = __DIR__ . '/quote-export-2026-03-16.csv';
$invoicesCsv = __DIR__ . '/invoice-export-2026-03-16.csv';
$metaCsv     = __DIR__ . '/wppl_postmeta.csv';
$postsCsv    = '/tmp/wppl_posts.csv';

// =========================================================================
// Helpers
// =========================================================================
function parseMoney(string $s): float {
    return floatval(str_replace([',', '$', ' '], '', $s));
}

function parseDate(string $s): string {
    // "March 13, 2026" → "2026-03-13 12:00:00"
    $ts = strtotime(trim($s));
    return $ts ? date('Y-m-d 12:00:00', $ts) : date('Y-m-d 12:00:00');
}

function esc(string $s): string {
    return addslashes(trim($s));
}

function sqlVal($v): string {
    if ($v === null) return 'NULL';
    if (is_int($v) || is_float($v)) return (string)$v;
    return "'" . esc((string)$v) . "'";
}

// =========================================================================
// 1. Read quote CSV → cotizaciones
// =========================================================================
$fh = fopen($quotesCsv, 'r');
// Skip BOM if present
$header = fgetcsv($fh);
$header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);

$quotes = [];
while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < 11) continue;
    $r = array_combine($header, $row);
    $numero = trim($r['Number']);
    if (!$numero) continue;

    $status = strtolower(trim($r['Status']));
    $estado = match($status) {
        'accepted' => 'aceptada',
        'declined' => 'rechazada',
        'draft'    => 'enviada',  // WordPress "Draft" = published quote, not a real draft
        default    => 'enviada',
    };

    $total = parseMoney($r['Total']);
    $subtotal = parseMoney($r['Sub Total']);
    $tax = parseMoney($r['Tax']);
    $date = parseDate($r['Created']);
    $titulo = trim($r['Title']);

    // Slug from numero: QUO-950 → imp-v2-quo-950
    $slug = 'imp-v2-' . strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $numero));
    $slug = preg_replace('/-+/', '-', trim($slug, '-'));
    $token = hash('sha256', 'cotizacloud-v2-' . $numero);

    $quotes[$numero] = [
        'numero'   => $numero,
        'titulo'   => $titulo,
        'slug'     => $slug,
        'token'    => $token,
        'total'    => $total,
        'subtotal' => $subtotal,
        'tax'      => $tax,
        'estado'   => $estado,
        'date'     => $date,
    ];
}
fclose($fh);
fprintf(STDERR, "Quotes read: %d\n", count($quotes));

// =========================================================================
// 2. Read invoice CSV → ventas
// =========================================================================
$fh = fopen($invoicesCsv, 'r');
$header = fgetcsv($fh);
$header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);

$invoices = [];
while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < 11) continue;
    $r = array_combine($header, $row);
    $numero = trim($r['Number']);
    if (!$numero) continue;

    $total = parseMoney($r['Total']);
    $status = strtolower(trim($r['Status']));
    $estado = ($status === 'paid') ? 'pagada' : 'pendiente';
    $date = parseDate($r['Created']);
    $titulo = trim($r['Title']);

    $invoices[] = [
        'numero'  => $numero,
        'titulo'  => $titulo,
        'total'   => $total,
        'estado'  => $estado,
        'date'    => $date,
    ];
}
fclose($fh);
fprintf(STDERR, "Invoices read: %d\n", count($invoices));

// =========================================================================
// 3. Build wp_id → QUO-numero mapping (for líneas)
// =========================================================================
$wpToQuote = [];
if (file_exists($postsCsv)) {
    $fh = fopen($postsCsv, 'r');
    $header = fgetcsv($fh);
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) !== count($header)) continue;
        $r = array_combine($header, $row);
        $wp_id = trim($r['wp_id']);
        $num = trim($r['numero']);
        if ($num && $wp_id) {
            // Normalize numero
            if (!preg_match('/^QUO-/', $num)) {
                $num = 'QUO-' . $num;
            }
            $wpToQuote[$wp_id] = $num;
        }
    }
    fclose($fh);
}
fprintf(STDERR, "WP→QUO mappings: %d\n", count($wpToQuote));

// =========================================================================
// 4. Read postmeta CSV → líneas (grouped by QUO numero)
// =========================================================================
$lineas = []; // QUO-numero => [items]
if (file_exists($metaCsv)) {
    $fh = fopen($metaCsv, 'r');
    $header = fgetcsv($fh);
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) < 2) continue;
        $wp_id = trim($row[0]);
        $raw = trim($row[1]);
        if (!$wp_id || !$raw) continue;
        if (!isset($wpToQuote[$wp_id])) continue;

        $items = @unserialize($raw);
        if ($items === false) $items = @unserialize(stripslashes($raw));
        if (!is_array($items) || empty($items)) continue;

        $quo = $wpToQuote[$wp_id];
        if (!isset($quotes[$quo])) continue;

        $lineas[$quo] = [];
        foreach ($items as $orden => $item) {
            $qty = (float)($item['qty'] ?? 1);
            $title = $item['title'] ?? 'Sin título';
            $amount = (float)($item['amount'] ?? 0);
            $desc = strip_tags($item['description'] ?? '');
            $desc = preg_replace('/\s+/', ' ', trim($desc));
            $subtotal = round($qty * $amount, 2);

            $lineas[$quo][] = [
                'orden'    => $orden,
                'titulo'   => $title,
                'desc'     => $desc,
                'qty'      => $qty,
                'amount'   => $amount,
                'subtotal' => $subtotal,
            ];
        }
    }
    fclose($fh);
}
fprintf(STDERR, "Quotes with líneas: %d\n", count($lineas));

// =========================================================================
// 5. Trust CSV totals — DO NOT recalculate from líneas
//    The CSV export has the correct totals. Lines may be incomplete.
//    Just log mismatches for reference.
// =========================================================================
$mismatches = 0;
foreach ($lineas as $quo => $items) {
    if (!isset($quotes[$quo])) continue;
    $sum = 0;
    foreach ($items as $item) $sum += $item['subtotal'];
    if (abs($quotes[$quo]['total'] - $sum) > 0.01 && $sum > 0) {
        fprintf(STDERR, "  Mismatch %s: CSV=\$%.2f vs Lines=\$%.2f (keeping CSV)\n", $quo, $quotes[$quo]['total'], $sum);
        $mismatches++;
    }
}
fprintf(STDERR, "Total mismatches (kept CSV value): %d\n", $mismatches);

// =========================================================================
// 6. Generate SQL: cotizaciones
// =========================================================================
$sqlCot = "-- ============================================================\n";
$sqlCot .= "-- CotizaCloud v2: import cotizaciones from quote-export CSV\n";
$sqlCot .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sqlCot .= "-- Total: " . count($quotes) . " cotizaciones\n";
$sqlCot .= "-- ============================================================\n\n";
$sqlCot .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($quotes as $q) {
    $enviada_at = ($q['estado'] !== 'borrador') ? sqlVal($q['date']) : 'NULL';
    $aceptada_at = ($q['estado'] === 'aceptada') ? sqlVal($q['date']) : 'NULL';
    $rechazada_at = ($q['estado'] === 'rechazada') ? sqlVal($q['date']) : 'NULL';

    $sqlCot .= "INSERT IGNORE INTO cotizaciones "
        . "(numero, empresa_id, cliente_id, usuario_id, cupon_id, titulo, slug, token, "
        . "descripcion, notas_internas, notas_cliente, subtotal, cupon_pct, cupon_codigo, cupon_amt, "
        . "impuesto_pct, impuesto_modo, impuesto_amt, total, estado, motivo_rechazo, "
        . "enviada_at, vista_at, accion_at, aceptada_at, rechazada_at, rechazada_motivo, "
        . "valida_hasta, ultima_vista_at, created_at, updated_at, visitas, "
        . "descuento_auto_activo, descuento_auto_pct, descuento_auto_dias, descuento_auto_expira, descuento_auto_amt, cupon_monto) "
        . "VALUES ("
        . sqlVal($q['numero']) . ", $EMPRESA_ID, $CLIENTE_ID, $USUARIO_ID, NULL, "
        . sqlVal($q['titulo']) . ", " . sqlVal($q['slug']) . ", " . sqlVal($q['token']) . ", "
        . "NULL, NULL, NULL, {$q['subtotal']}, 0, NULL, 0, "
        . "0, 'ninguno', 0, {$q['total']}, " . sqlVal($q['estado']) . ", NULL, "
        . "$enviada_at, NULL, $aceptada_at, $aceptada_at, $rechazada_at, NULL, "
        . "NULL, NULL, " . sqlVal($q['date']) . ", " . sqlVal($q['date']) . ", 0, "
        . "0, 0, 3, NULL, 0, 0);\n";
}

$sqlCot .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
file_put_contents(__DIR__ . '/import_v2_cotizaciones.sql', $sqlCot);
fprintf(STDERR, "Written: import_v2_cotizaciones.sql\n");

// =========================================================================
// 7. Generate SQL: líneas
// =========================================================================
$sqlLin = "-- ============================================================\n";
$sqlLin .= "-- CotizaCloud v2: import cotizacion_lineas\n";
$sqlLin .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$totalItems = array_sum(array_map('count', $lineas));
$sqlLin .= "-- Total: $totalItems items from " . count($lineas) . " cotizaciones\n";
$sqlLin .= "-- ============================================================\n\n";
$sqlLin .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($lineas as $quo => $items) {
    if (!isset($quotes[$quo])) continue;
    $slug = $quotes[$quo]['slug'];
    $sqlLin .= "-- $quo / slug: $slug (" . count($items) . " items)\n";
    foreach ($items as $item) {
        $titleEsc = esc($item['titulo']);
        $descEsc = esc(substr($item['desc'], 0, 5000));
        $sqlLin .= "INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)\n";
        $sqlLin .= "SELECT c.id, NULL, NULL, {$item['orden']}, '$titleEsc', '$descEsc', NULL, {$item['qty']}, {$item['amount']}, {$item['subtotal']}\n";
        $sqlLin .= "FROM cotizaciones c WHERE c.empresa_id = $EMPRESA_ID AND c.slug = '$slug';\n";
    }
    $sqlLin .= "\n";
}

$sqlLin .= "SET FOREIGN_KEY_CHECKS = 1;\n";
file_put_contents(__DIR__ . '/import_v2_lineas.sql', $sqlLin);
fprintf(STDERR, "Written: import_v2_lineas.sql\n");

// =========================================================================
// 8. Generate SQL: ventas (from invoice CSV with real dates & totals)
// =========================================================================
$sqlVta = "-- ============================================================\n";
$sqlVta .= "-- CotizaCloud v2: import ventas from invoice-export CSV\n";
$sqlVta .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sqlVta .= "-- Total: " . count($invoices) . " ventas\n";
$sqlVta .= "-- Dates and totals come directly from WordPress invoice export\n";
$sqlVta .= "-- ============================================================\n\n";
$sqlVta .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

// Match invoices to quotes by title to link cotizacion_id
// Build title → slug lookup from quotes
$titleToSlug = [];
foreach ($quotes as $q) {
    $key = mb_strtolower(trim($q['titulo']));
    $titleToSlug[$key] = $q['slug'];
}

$matched = 0;
$unmatched = [];

foreach ($invoices as $inv) {
    $total = $inv['total'];
    if ($total <= 0) continue; // skip empty invoices

    $slug = 'imp-v2-vta-' . strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $inv['numero']));
    $slug = preg_replace('/-+/', '-', trim($slug, '-'));
    $token = hash('sha256', 'cotizacloud-v2-vta-' . $inv['numero']);

    // Try to match to a cotización by title
    $titleKey = mb_strtolower(trim($inv['titulo']));
    $cotSlug = $titleToSlug[$titleKey] ?? null;

    $pagado = ($inv['estado'] === 'pagada') ? $total : 0;
    $saldo = $total - $pagado;

    if ($cotSlug) {
        // Insert with cotizacion_id from slug match
        $sqlVta .= "-- {$inv['numero']}: {$inv['titulo']} → matched $cotSlug\n";
        $sqlVta .= "INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, "
            . "numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) "
            . "SELECT $EMPRESA_ID, c.id, $CLIENTE_ID, $USUARIO_ID, "
            . sqlVal($inv['numero']) . ", " . sqlVal($inv['titulo']) . ", "
            . sqlVal($slug) . ", " . sqlVal($token) . ", "
            . "$total, $pagado, $saldo, " . sqlVal($inv['estado']) . ", "
            . sqlVal($inv['date']) . ", " . sqlVal($inv['date'])
            . " FROM cotizaciones c WHERE c.empresa_id = $EMPRESA_ID AND c.slug = " . sqlVal($cotSlug) . ";\n\n";
        $matched++;
    } else {
        // Insert without cotizacion_id
        $sqlVta .= "-- {$inv['numero']}: {$inv['titulo']} → NO MATCH (venta sin cotización)\n";
        $sqlVta .= "INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, "
            . "numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) "
            . "VALUES ($EMPRESA_ID, NULL, $CLIENTE_ID, $USUARIO_ID, "
            . sqlVal($inv['numero']) . ", " . sqlVal($inv['titulo']) . ", "
            . sqlVal($slug) . ", " . sqlVal($token) . ", "
            . "$total, $pagado, $saldo, " . sqlVal($inv['estado']) . ", "
            . sqlVal($inv['date']) . ", " . sqlVal($inv['date']) . ");\n\n";
        $unmatched[] = $inv['numero'] . ' - ' . $inv['titulo'];
    }
}

// Mark matched cotizaciones as convertida
$sqlVta .= "\n-- Mark matched cotizaciones as 'convertida'\n";
$sqlVta .= "UPDATE cotizaciones c\n";
$sqlVta .= "INNER JOIN ventas v ON v.cotizacion_id = c.id\n";
$sqlVta .= "SET c.estado = 'convertida', c.updated_at = NOW()\n";
$sqlVta .= "WHERE c.empresa_id = $EMPRESA_ID AND c.slug LIKE 'imp-v2-%' AND v.slug LIKE 'imp-v2-vta-%';\n\n";

// Link cotizacion_lineas to ventas
$sqlVta .= "-- Link cotizacion_lineas to ventas\n";
$sqlVta .= "UPDATE cotizacion_lineas cl\n";
$sqlVta .= "INNER JOIN ventas v ON v.cotizacion_id = cl.cotizacion_id\n";
$sqlVta .= "SET cl.venta_id = v.id\n";
$sqlVta .= "WHERE v.empresa_id = $EMPRESA_ID AND v.slug LIKE 'imp-v2-vta-%' AND cl.venta_id IS NULL;\n\n";

// Update folio counters
$sqlVta .= "-- Update folio counters\n";
$sqlVta .= "INSERT INTO folios (empresa_id, tipo, anio, ultimo)\n";
$sqlVta .= "SELECT $EMPRESA_ID, 'VTA', YEAR(NOW()),\n";
$sqlVta .= "       (SELECT COUNT(*) FROM ventas WHERE empresa_id = $EMPRESA_ID)\n";
$sqlVta .= "ON DUPLICATE KEY UPDATE ultimo = GREATEST(ultimo,\n";
$sqlVta .= "       (SELECT COUNT(*) FROM ventas WHERE empresa_id = $EMPRESA_ID));\n\n";

// Verification
$sqlVta .= "-- ════════════════════════════════════════════════════════════\n";
$sqlVta .= "-- VERIFICATION\n";
$sqlVta .= "-- ════════════════════════════════════════════════════════════\n";
$sqlVta .= "SELECT 'cotizaciones' AS tabla, COUNT(*) AS total FROM cotizaciones WHERE empresa_id = $EMPRESA_ID\n";
$sqlVta .= "UNION ALL SELECT 'ventas', COUNT(*) FROM ventas WHERE empresa_id = $EMPRESA_ID\n";
$sqlVta .= "UNION ALL SELECT 'lineas', COUNT(*) FROM cotizacion_lineas cl INNER JOIN cotizaciones c ON c.id = cl.cotizacion_id WHERE c.empresa_id = $EMPRESA_ID;\n\n";

$sqlVta .= "-- Ventas de marzo 2026\n";
$sqlVta .= "SELECT v.numero, v.titulo, v.total, v.estado, v.created_at\n";
$sqlVta .= "FROM ventas v WHERE v.empresa_id = $EMPRESA_ID\n";
$sqlVta .= "  AND v.created_at BETWEEN '2026-03-01' AND '2026-03-31 23:59:59'\n";
$sqlVta .= "ORDER BY v.created_at DESC;\n";

$sqlVta .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents(__DIR__ . '/import_v2_ventas.sql', $sqlVta);
fprintf(STDERR, "Written: import_v2_ventas.sql\n");
fprintf(STDERR, "  Matched to quotes: $matched\n");
if (!empty($unmatched)) {
    fprintf(STDERR, "  Unmatched (" . count($unmatched) . "):\n");
    foreach ($unmatched as $u) fprintf(STDERR, "    - $u\n");
}
