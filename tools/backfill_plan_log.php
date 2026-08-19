<?php
// ============================================================
//  RECONSTRUCTOR del historial de planes (tabla plan_log)
//
//  La bitácora nace vacía, pero las empresas ya llevan meses cambiando de
//  plan. Este script rearma lo que SE PUEDE PROBAR con la evidencia que
//  quedó, y NO inventa el resto.
//
//  Qué reconstruye y con qué evidencia:
//    R1 alta        ← empresas.created_at            (fecha exacta)
//    R2 pagos MP    ← pagos_suscripcion              (fecha, monto, ciclo)
//    R3 suscripción ← suscripciones.created_at       (solo si no hay pago cerca)
//    R4 cancelación ← suscripciones.cancelled_at
//    R5 fin trial   ← created_at + 30d               (LÍMITE INFERIOR, aprox.)
//
//  Qué NO reconstruye, y por qué:
//    · El paquete que tenía una empresa durante su trial YA DEGRADADO. El plan
//      elegido en la landing nunca se persistió y planes_degradar_free lo
//      sobrescribió con 'free' en el mismo UPDATE. No hay de dónde sacarlo:
//      se registra plan_anterior = NULL, jamás un 'pro' inventado.
//    · Ninguna activación, renovación o cambio manual del superadmin anterior
//      a la instalación. toggle_plan.php no escribía log de ninguna clase.
//
//  SEGURO DE RE-CORRER: solo INSERT, nunca UPDATE ni DELETE, con INSERT IGNORE
//  contra el índice único de hecho_uid. Correrlo diez veces deja lo mismo que
//  correrlo una.
//
//  USO:
//    php tools/backfill_plan_log.php config.php            → simulacro (no escribe)
//    php tools/backfill_plan_log.php config.php --apply    → escribe
// ============================================================
define('COTIZAAPP', 1);
$args  = array_slice($argv, 1);
$apply = in_array('--apply', $args, true);
$cfg   = null;
foreach ($args as $a) { if ($a !== '--apply') { $cfg = $a; break; } }
$cfg = $cfg ?: '/var/www/cotizacloud/config.php';
if (!is_file($cfg)) { fwrite(STDERR, "No encuentro config.php en $cfg\n"); exit(1); }
require $cfg;

echo $apply ? "MODO ESCRITURA\n\n" : "SIMULACRO — no escribe nada. Agrega --apply para aplicar.\n\n";

// Tarifas históricas, para deducir plan y ciclo desde el monto cobrado.
// Un monto que no esté aquí NO se adivina: se guarda con plan NULL y una nota.
$TARIFAS = [
    199 => ['lite','mensual'],    1910 => ['lite','anual'],
    299 => ['pro','mensual'],     2868 => ['pro','anual'],      // tarifa vieja
    499 => ['pro','mensual'],     4788 => ['pro','anual'],
    799 => ['business','mensual'],7668 => ['business','anual'], // tarifa vieja
   2999 => ['business','mensual'],28788=> ['business','anual'],
];

$stats = ['R1'=>0,'R2'=>0,'R3'=>0,'R4'=>0,'R5'=>0,'dup'=>0];

// Inserta un hecho reconstruido. La deduplicación es doble:
//  (1) INSERT IGNORE contra uk_hecho_uid → re-correr no duplica.
//  (2) si ya existe una fila VIVA con el mismo ref, no se reconstruye:
//      el sistema ya lo registró de verdad y esto sería un duplicado feo.
function meter(array $f, bool $apply, array &$stats, string $clave): void
{
    // La comprobación de duplicado va en su PROPIO try: si la tabla todavía no
    // existe, esto lanza, y sin aislarlo se llevaba por delante el conteo del
    // simulacro (los hechos con ref desaparecían sin explicación). Fallo aquí
    // = "no hay fila viva", que es lo correcto cuando no hay ni tabla.
    if (!empty($f['ref'])) {
        try {
            $viva = DB::val(
                "SELECT 1 FROM plan_log WHERE empresa_id=? AND ref=? AND origen<>'backfill' LIMIT 1",
                [$f['empresa_id'], $f['ref']]
            );
            if ($viva) { $stats['dup']++; return; }
        } catch (Throwable $e) { /* sin tabla aún: nada que duplicar */ }
    }
    if (!$apply) { $stats[$clave]++; return; }
    try {
        $n = DB::execute(
            "INSERT IGNORE INTO plan_log
                (empresa_id, evento, origen, motivo, plan_anterior, plan_nuevo,
                 vence_nuevo, dias, ciclo, monto_mxn, ref, confianza, hecho_uid,
                 detalle, ocurrio_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $f['empresa_id'], $f['evento'], 'backfill', $f['motivo'] ?? null,
                $f['plan_anterior'] ?? null, $f['plan_nuevo'] ?? null,
                $f['vence_nuevo'] ?? null, $f['dias'] ?? null, $f['ciclo'] ?? null,
                $f['monto'] ?? null, $f['ref'] ?? null, $f['confianza'],
                $f['hecho_uid'], $f['detalle'] ?? null, $f['ocurrio_at'],
            ]
        );
        if ($n > 0) $stats[$clave]++; else $stats['dup']++;
    } catch (Throwable $e) {
        fwrite(STDERR, "  ! " . $e->getMessage() . "\n");
    }
}

$emps = DB::query("SELECT id, nombre, created_at, plan, plan_vence FROM empresas WHERE slug <> '_system' ORDER BY id");
foreach ($emps as $e) {
    $eid = (int)$e['id'];
    $antes = $stats;

    // Flags de trial: pueden no estar migrados en instalaciones viejas.
    $tr = ['es_trial' => null, 'trial_usado' => null];
    try { $tr = DB::row("SELECT es_trial, trial_usado FROM empresas WHERE id=?", [$eid]) ?: $tr; }
    catch (Throwable $ex) {}
    $es_trial    = $tr['es_trial']    !== null ? (int)$tr['es_trial']    : null;
    $trial_usado = $tr['trial_usado'] !== null ? (int)$tr['trial_usado'] : null;

    // ── R1 · ALTA ─────────────────────────────────────────────
    // es_trial=1 SOLO lo escribe el registro, y lo limpian TODOS los caminos
    // que tocan el plan. Si sigue en 1, nadie lo tocó desde el alta → el plan
    // de hoy ES el plan con el que nació. Eso es deducción dura, no adivinanza.
    $plan_alta = null; $conf = 'inferido'; $nota = 'No consta con qué paquete se dio de alta.';
    if ($es_trial === 1) {
        $plan_alta = $e['plan']; $conf = 'probado';
        $nota = 'Sigue marcada como prueba: nadie tocó el plan desde el alta.';
    } elseif ($trial_usado === 1) {
        $nota = 'Tuvo prueba y ya la agotó. El paquete de esa prueba no quedó registrado en ningún lado.';
        $conf = 'probado'; // el HECHO del alta es exacto; el plan es el que va NULL
    }
    meter([
        'empresa_id' => $eid, 'evento' => 'alta', 'motivo' => 'trial_30d',
        'plan_nuevo' => $plan_alta, 'confianza' => $conf,
        'hecho_uid' => "emp_alta:$eid", 'detalle' => $nota,
        'ocurrio_at' => $e['created_at'],
    ], $apply, $stats, 'R1');

    // ── R2 · PAGOS DE MERCADOPAGO ─────────────────────────────
    // Evidencia dura de fecha y monto. El plan se DEDUCE del monto y por eso
    // va marcado 'inferido': la tabla de pagos no guarda el plan.
    $pagos = [];
    try {
        $pagos = DB::query(
            "SELECT mp_payment_id, fecha_pago, monto_mxn, estado, detalle
               FROM pagos_suscripcion WHERE empresa_id=? ORDER BY fecha_pago ASC", [$eid]);
    } catch (Throwable $ex) {}
    foreach ($pagos as $p) {
        $m = (int)round((float)$p['monto_mxn']);
        [$pl, $ci] = $TARIFAS[$m] ?? [null, null];
        $d = $pl === null
            ? "Monto de $" . number_format((float)$p['monto_mxn'], 2) . " sin tarifa conocida: no se deduce el paquete."
            : "Paquete deducido del monto cobrado.";
        if (($p['estado'] ?? '') !== 'approved') $d .= " Pago en estado '" . $p['estado'] . "'.";
        meter([
            'empresa_id' => $eid, 'evento' => 'pago_mp', 'motivo' => 'cobro',
            'plan_nuevo' => $pl, 'ciclo' => $ci, 'monto' => (float)$p['monto_mxn'],
            'ref' => 'mp_pay:' . $p['mp_payment_id'], 'confianza' => 'inferido',
            'hecho_uid' => 'mp_pay:' . $p['mp_payment_id'], 'detalle' => $d,
            'ocurrio_at' => $p['fecha_pago'],
        ], $apply, $stats, 'R2');
    }

    // ── R3 · ALTA DE SUSCRIPCIÓN ──────────────────────────────
    // Solo si NO hay un pago aprobado dentro de ±48h: si lo hay, ese pago YA es
    // el hecho (R2 lo cubre) y esto sería contarlo dos veces.
    // 'inferido' porque la fila de suscripciones es única por empresa y se
    // actualiza en sitio: el plan que se lee es el de HOY, no el de esa fecha.
    $subs = [];
    try {
        $subs = DB::query(
            "SELECT s.id, s.created_at, s.plan, s.ciclo, s.monto_mxn, s.cancelled_at
               FROM suscripciones s
              WHERE s.empresa_id=?
                AND NOT EXISTS (
                    SELECT 1 FROM pagos_suscripcion p
                     WHERE p.empresa_id = s.empresa_id AND p.estado='approved'
                       AND ABS(TIMESTAMPDIFF(HOUR, p.fecha_pago, s.created_at)) <= 48)", [$eid]);
    } catch (Throwable $ex) {}
    foreach ($subs as $s) {
        meter([
            'empresa_id' => $eid, 'evento' => 'suscripcion_alta', 'motivo' => 'alta_suscripcion',
            'plan_nuevo' => $s['plan'], 'ciclo' => $s['ciclo'], 'monto' => (float)$s['monto_mxn'],
            'ref' => 'sub:' . $s['id'], 'confianza' => 'inferido',
            'hecho_uid' => 'sub_created:' . $s['id'],
            'detalle' => 'El plan sale de la fila de suscripción, que se actualiza en sitio: es el de hoy, no necesariamente el de esa fecha.',
            'ocurrio_at' => $s['created_at'],
        ], $apply, $stats, 'R3');
    }

    // ── R4 · CANCELACIÓN ──────────────────────────────────────
    // Solo sobrevive la ÚLTIMA cancelación: la columna se pisa.
    $cancel = [];
    try {
        $cancel = DB::query(
            "SELECT id, cancelled_at, plan FROM suscripciones WHERE empresa_id=? AND cancelled_at IS NOT NULL", [$eid]);
    } catch (Throwable $ex) {}
    foreach ($cancel as $c) {
        meter([
            'empresa_id' => $eid, 'evento' => 'suscripcion_cancelada', 'motivo' => 'cancelacion',
            'plan_anterior' => $c['plan'], 'confianza' => 'probado',
            'hecho_uid' => 'sub_cancel:' . $c['id'] . ':' . substr((string)$c['cancelled_at'], 0, 10),
            'detalle' => 'Solo queda registrada la última cancelación: la columna se sobrescribe.',
            'ocurrio_at' => $c['cancelled_at'],
        ], $apply, $stats, 'R4');
    }

    // ── R5 · FIN DE PRUEBA ────────────────────────────────────
    // Sabemos QUE pasó (trial_usado=1 y hoy está en free). NO sabemos CUÁNDO:
    // la fecha exacta no se guardó. Se usa alta+30d como LÍMITE INFERIOR y se
    // marca 'acotado' para que la pantalla lo muestre como aproximado.
    // plan_anterior va NULL: poner 'pro' sería inventar.
    if ($trial_usado === 1 && ($e['plan'] ?? '') === 'free') {
        $desde = date('Y-m-d H:i:s', strtotime($e['created_at'] . ' +30 days'));
        meter([
            'empresa_id' => $eid, 'evento' => 'degradacion', 'motivo' => 'trial_vencido',
            'plan_nuevo' => 'free', 'confianza' => 'acotado',
            'hecho_uid' => "trial_fin:$eid",
            'detalle' => 'Fecha aproximada: alta + 30 días. La real no quedó registrada. El paquete que tenía durante la prueba tampoco.',
            'ocurrio_at' => $desde,
        ], $apply, $stats, 'R5');
    }

    $n = array_sum($stats) - array_sum($antes);
    if ($n > 0) {
        printf("  %-34s +%d  (alta %d · pagos %d · susc %d · cancel %d · fin-prueba %d)\n",
            mb_substr($e['nombre'], 0, 34), $n,
            $stats['R1'] - $antes['R1'], $stats['R2'] - $antes['R2'],
            $stats['R3'] - $antes['R3'], $stats['R4'] - $antes['R4'], $stats['R5'] - $antes['R5']);
    }
}

echo "\n";
printf("Altas %d · Pagos MP %d · Suscripciones %d · Cancelaciones %d · Fin de prueba %d\n",
    $stats['R1'], $stats['R2'], $stats['R3'], $stats['R4'], $stats['R5']);
printf("Omitidos por duplicado: %d\n", $stats['dup']);
echo $apply
    ? "\nListo. Re-correrlo no duplica nada.\n"
    : "\nNada escrito. Agrega --apply para aplicar.\n";
echo "\nTodo lo insertado queda marcado como 'reconstruido' en la pantalla, con su\n";
echo "nivel de confianza: probado (evidencia directa), inferido (deducido del\n";
echo "monto o de una fila que se actualiza en sitio) y acotado (la fecha es un\n";
echo "límite inferior, no el momento exacto).\n";
