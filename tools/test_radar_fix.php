<?php
// ============================================================
//  Test del fix al filtro de ghosts del Radar
//  Uso: php tools/test_radar_fix.php COT_ID EMPRESA_ID
//
//  Llama score() directamente (bypasea guard de "aceptada" en
//  recalcular()). NO MODIFICA BD — solo imprime resultado.
//
//  Compara el debug con el bucket guardado en cotizaciones para
//  ver cuánto cambia.
// ============================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../modules/radar/Radar.php';

$cot_id     = (int)($argv[1] ?? 3918);
$empresa_id = (int)($argv[2] ?? 7);  // 7 = GD por default

$cot = DB::row("SELECT id, estado, radar_bucket, radar_score FROM cotizaciones WHERE id=? AND empresa_id=?",
               [$cot_id, $empresa_id]);
if (!$cot) { echo "Cot $cot_id no encontrada en empresa $empresa_id\n"; exit(1); }

echo "═══════════════════════════════════════════════\n";
echo "COT $cot_id  (empresa $empresa_id)\n";
echo "Estado actual:  {$cot['estado']}\n";
echo "Bucket guardado: " . ($cot['radar_bucket'] ?? '(null)') . "\n";
echo "Score guardado:  " . ($cot['radar_score'] ?? '(null)') . "\n";
echo "═══════════════════════════════════════════════\n\n";

echo "Llamando Radar::score() en vivo (filtro NUEVO ya aplicado en código)...\n\n";
$r = Radar::score($cot_id, $empresa_id);

echo "── RESULTADO score() ──────────────────────────\n";
echo "Bucket nuevo:   " . ($r['bucket'] ?? '(null)') . "\n";
echo "Buckets array:  " . implode(',', $r['buckets'] ?? []) . "\n";
echo "Score nuevo:    " . ($r['score'] ?? 0) . "\n";
echo "Fit_pct:        " . ($r['fit_pct'] ?? 0) . "\n";
echo "Priority_pct:   " . ($r['priority_pct'] ?? 0) . "\n";
echo "\n";

echo "── DEBUG (sessions counter) ───────────────────\n";
$dbg = $r['debug'] ?? [];
foreach (['sessions','guest_sessions','views24','views48','views7d',
          'uniq_ips','uniq_ips_raw','span48','gap_days','last_ts'] as $k) {
    if (isset($dbg[$k])) echo sprintf("  %-18s %s\n", $k.':', $dbg[$k]);
}
echo "\n";

echo "── SEÑALES ────────────────────────────────────\n";
foreach (($r['senales'] ?? []) as $name => $info) {
    echo sprintf("  %-25s %s\n", $name, is_array($info) ? json_encode($info) : $info);
}
echo "\n";

echo "═══════════════════════════════════════════════\n";
echo "INTERPRETACIÓN:\n";
echo "  • Si 'sessions' bajó de ~12 a ~5 → fix funciona\n";
echo "  • Si 'bucket' cambia → cot afectada por el cambio\n";
echo "  • Si 'bucket' es null/no_abierta → ghosts ya no la inflan\n";
echo "  • Bucket guardado NO se actualiza (cot aceptada) → línea 1418\n";
echo "═══════════════════════════════════════════════\n";
