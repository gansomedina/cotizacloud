<?php
/**
 * Import cotizaciones from WordPress CSV export into CotizaCloud.
 *
 * Reads  : /tmp/wppl_posts.csv
 * Writes : /home/user/cotizacloud/import_all_cotizaciones.sql
 *
 * Estado logic:
 *   - invoice_id != NULL  → 'aceptada'
 *   - post_status = 'draft' → 'borrador'
 *   - otherwise            → 'enviada'
 */

$csvPath = '/tmp/wppl_posts.csv';
$sqlPath = __DIR__ . '/import_all_cotizaciones.sql';

// ---------------------------------------------------------------------------
// 1. Read CSV
// ---------------------------------------------------------------------------
$fh = fopen($csvPath, 'r');
if (!$fh) {
    die("ERROR: cannot open $csvPath\n");
}
$headers = fgetcsv($fh);
$rows = [];
while (($line = fgetcsv($fh)) !== false) {
    if (count($line) !== count($headers)) {
        // Try to handle rows that might have mismatched columns
        fprintf(STDERR, "WARN: skipping row with %d cols (expected %d): %s\n",
            count($line), count($headers), implode(',', array_slice($line, 0, 3)));
        continue;
    }
    $rows[] = array_combine($headers, $line);
}
fclose($fh);

fprintf(STDERR, "CSV rows read: %d\n", count($rows));

// ---------------------------------------------------------------------------
// 2. Build INSERT statements
// ---------------------------------------------------------------------------
$inserts   = [];
$skipped   = 0;
$imported  = 0;
$accepted  = 0;
$drafts    = 0;

foreach ($rows as $r) {
    $wp_id      = trim($r['wp_id']);
    $titulo     = $r['post_title'];
    $post_date  = $r['post_date'];      // "2026-03-14 12:36:05"
    $status     = $r['post_status'];
    $numero_raw = trim($r['numero']);    // "QUO-950" or "" or "147"
    $total_fmt  = $r['total_fmt'];       // "$20,600.00"
    $tax_pct    = floatval($r['tax_pct']);
    $descuento  = $r['descuento'];
    $invoice_id = $r['invoice_id'];
    $last_view  = $r['last_view_ts'];
    $first_view = $r['first_view_ts'];

    // --- Parse total ---
    $total = floatval(str_replace([',', '$'], '', $total_fmt));

    // --- Compute subtotal (reverse tax if any) ---
    if ($tax_pct > 0) {
        $subtotal = round($total / (1 + $tax_pct / 100), 2);
        $impuesto_amt = round($total - $subtotal, 2);
        $impuesto_modo = 'incluido';
    } else {
        $subtotal = $total;
        $impuesto_amt = 0;
        $impuesto_modo = 'ninguno';
    }

    // --- Estado ---
    $has_invoice = ($invoice_id !== 'NULL' && $invoice_id !== '' && $invoice_id !== null);
    if ($has_invoice) {
        $estado = 'aceptada';
        $accepted++;
    } elseif ($status === 'draft') {
        $estado = 'borrador';
        $drafts++;
    } else {
        $estado = 'enviada';
    }

    // --- Numero ---
    // Keep as-is from CSV. If empty, generate from wp_id.
    if ($numero_raw === '' || $numero_raw === null) {
        $numero = 'QUO-WP-' . $wp_id;
    } elseif (!preg_match('/^QUO-/', $numero_raw)) {
        // e.g. just "147"
        $numero = 'QUO-' . $numero_raw;
    } else {
        $numero = $numero_raw;
    }

    // --- Slug ---
    // Extract numeric part for slug: QUO-950 → 950
    $numero_num = preg_replace('/^QUO-0*/', '', $numero);
    if ($numero_num === '') $numero_num = $wp_id;
    $slug = 'imp-quo-' . $numero_num . '-' . $wp_id;
    // Sanitise slug (lowercase, only alnum and hyphens)
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $slug));
    $slug = preg_replace('/-+/', '-', trim($slug, '-'));

    // --- Token (deterministic from wp_id so re-runs produce same output) ---
    $token = hash('sha256', 'cotizacloud-import-wp-' . $wp_id);

    // --- Timestamps ---
    $created_at = $post_date;
    $enviada_at = ($estado !== 'borrador') ? $post_date : null;
    $aceptada_at = ($estado === 'aceptada') ? $post_date : null;

    // last_view_ts / first_view_ts are Unix timestamps or "NULL"
    $ultima_vista = null;
    if ($last_view !== 'NULL' && $last_view !== '' && $last_view !== null) {
        $ultima_vista = date('Y-m-d H:i:s', intval($last_view));
    }
    $vista_at = null;
    if ($first_view !== 'NULL' && $first_view !== '' && $first_view !== null) {
        $vista_at = date('Y-m-d H:i:s', intval($first_view));
    }

    // --- Build INSERT ---
    $inserts[] = buildInsert([
        'numero'         => $numero,
        'empresa_id'     => 2,
        'cliente_id'     => 2,
        'usuario_id'     => 2,
        'cupon_id'       => null,
        'titulo'         => $titulo,
        'slug'           => $slug,
        'token'          => $token,
        'descripcion'    => null,
        'notas_internas' => null,
        'notas_cliente'  => null,
        'subtotal'       => $subtotal,
        'cupon_pct'      => 0,
        'cupon_codigo'   => null,
        'cupon_amt'      => 0,
        'impuesto_pct'   => $tax_pct,
        'impuesto_modo'  => $impuesto_modo,
        'impuesto_amt'   => $impuesto_amt,
        'total'          => $total,
        'estado'         => $estado,
        'motivo_rechazo' => null,
        'enviada_at'     => $enviada_at,
        'vista_at'       => $vista_at,
        'accion_at'      => $aceptada_at,
        'aceptada_at'    => $aceptada_at,
        'rechazada_at'   => null,
        'rechazada_motivo' => null,
        'valida_hasta'   => null,
        'ultima_vista_at' => $ultima_vista,
        'radar_bucket'   => null,
        'radar_score'    => null,
        'radar_senales'  => null,
        'radar_updated_at' => null,
        'created_at'     => $created_at,
        'updated_at'     => $created_at,
        'visitas'        => 0,
        'descuento_auto_activo' => 0,
        'descuento_auto_pct'    => 0,
        'descuento_auto_dias'   => 3,
        'descuento_auto_expira' => null,
        'descuento_auto_amt'    => 0,
        'cupon_monto'    => 0,
    ]);

    $imported++;
}

// ---------------------------------------------------------------------------
// 3. Write SQL file
// ---------------------------------------------------------------------------
$out = fopen($sqlPath, 'w');

fwrite($out, "-- ============================================================\n");
fwrite($out, "-- CotizaCloud: import cotizaciones from WordPress\n");
fwrite($out, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
fwrite($out, "-- Rows: $imported  (accepted: $accepted, drafts: $drafts, enviada: " . ($imported - $accepted - $drafts) . ")\n");
fwrite($out, "-- ============================================================\n\n");

fwrite($out, "SET NAMES utf8mb4;\n");
fwrite($out, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

// Guard: skip rows whose slug already exists
fwrite($out, "-- Each INSERT uses INSERT IGNORE with a unique slug to prevent duplicates.\n");
fwrite($out, "-- The `slug` column must have a UNIQUE index for this to work.\n");
fwrite($out, "-- If it doesn't, run:  ALTER TABLE cotizaciones ADD UNIQUE INDEX uq_slug (slug);\n\n");

foreach ($inserts as $sql) {
    fwrite($out, $sql . "\n");
}

fwrite($out, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
fwrite($out, "\n-- Summary: $imported rows imported.\n");
fwrite($out, "-- NOTE: 113 rows marked 'aceptada' (had invoice_id in WP).\n");
fwrite($out, "-- The remaining ~711 published quotes are marked 'enviada'.\n");
fwrite($out, "-- If you have the list of WP IDs that were truly 'Accepted' or 'Declined'\n");
fwrite($out, "-- from wp_term_relationships, update them with:\n");
fwrite($out, "--   UPDATE cotizaciones SET estado='aceptada', aceptada_at=created_at\n");
fwrite($out, "--     WHERE slug IN ('imp-quo-XXX-YYY', ...);\n");
fwrite($out, "--   UPDATE cotizaciones SET estado='rechazada', rechazada_at=created_at\n");
fwrite($out, "--     WHERE slug IN ('imp-quo-XXX-YYY', ...);\n");

fclose($out);

fprintf(STDERR, "Done. SQL written to: $sqlPath\n");
fprintf(STDERR, "  Total inserts : $imported\n");
fprintf(STDERR, "  Aceptada      : $accepted\n");
fprintf(STDERR, "  Borrador      : $drafts\n");
fprintf(STDERR, "  Enviada       : %d\n", $imported - $accepted - $drafts);
fprintf(STDERR, "  Skipped       : $skipped\n");

// ===========================================================================
// Helper
// ===========================================================================
function buildInsert(array $data): string
{
    $cols = [];
    $vals = [];
    foreach ($data as $col => $val) {
        $cols[] = '`' . $col . '`';
        if ($val === null) {
            $vals[] = 'NULL';
        } elseif (is_int($val) || is_float($val)) {
            $vals[] = $val;
        } else {
            $vals[] = "'" . addslashes((string)$val) . "'";
        }
    }
    return 'INSERT IGNORE INTO `cotizaciones` (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ');';
}
