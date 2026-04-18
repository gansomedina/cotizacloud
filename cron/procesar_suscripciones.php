<?php
// ============================================================
//  CotizaCloud — cron/procesar_suscripciones.php
//  Cron diario (3am): cobro automático, grace period,
//  degradación, emails aviso.
//
//  Ejecutar: php /path/to/cron/procesar_suscripciones.php
//  Crontab:  0 3 * * * php /home/cotizacl/public_html/cron/procesar_suscripciones.php
// ============================================================

define('COTIZAAPP', true);
require_once dirname(__DIR__) . '/config.php';

$log = function(string $msg) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    error_log('[Cron Suscripciones] ' . $msg);
};

$log('Iniciando procesamiento de suscripciones...');

// ─── 1. Cobro automático: vencen hoy o en los próximos 3 días ─
$por_cobrar = DB::query(
    "SELECT s.*, e.email, e.nombre
     FROM suscripciones s
     INNER JOIN empresas e ON e.id = s.empresa_id
     WHERE s.estado = 'active'
       AND s.cancel_al_vencer = 0
       AND s.mp_customer_id IS NOT NULL
       AND s.mp_card_id IS NOT NULL
       AND s.proximo_cobro IS NOT NULL
       AND s.proximo_cobro <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
       AND s.intentos_cobro < 3"
);

$cobros_ok = 0; $cobros_err = 0;
foreach ($por_cobrar as $sub) {
    $empresa_id = (int)$sub['empresa_id'];

    // Anti doble-cobro: si el último intento fue hace menos de 24h, saltar
    if ($sub['ultimo_intento'] && strtotime($sub['ultimo_intento']) > time() - 86400) {
        continue;
    }

    $resp = MercadoPago::cobrarTarjetaGuardada([
        'empresa_id'        => $empresa_id,
        'customer_id'       => $sub['mp_customer_id'],
        'card_id'           => $sub['mp_card_id'],
        'monto'             => (float)$sub['monto_mxn'],
        'email'             => $sub['email'],
        'plan'              => $sub['plan'],
        'ciclo'             => $sub['ciclo'],
        'payment_method_id' => $sub['card_brand'],
        'descripcion'       => 'CotizaCloud ' . ucfirst($sub['plan']) . ' — Renovación ' . ucfirst($sub['ciclo']),
    ]);

    DB::execute(
        "UPDATE suscripciones SET ultimo_intento=NOW(), intentos_cobro=intentos_cobro+1 WHERE id=?",
        [$sub['id']]
    );

    $status = $resp['status'] ?? '';
    $status_detail = $resp['status_detail'] ?? ($resp['message'] ?? '');

    if ($status === 'approved') {
        $dias  = $sub['ciclo'] === 'anual' ? 365 : 30;
        $base  = ($sub['proximo_cobro'] && $sub['proximo_cobro'] >= date('Y-m-d'))
                  ? $sub['proximo_cobro'] : date('Y-m-d');
        $vence = date('Y-m-d', strtotime($base . " +{$dias} days"));

        DB::execute(
            "UPDATE suscripciones SET
                proximo_cobro=?, intentos_cobro=0, ultimo_error=NULL,
                mp_last_payment_id=?, updated_at=NOW()
             WHERE id=?",
            [$vence, (string)$resp['id'], $sub['id']]
        );
        DB::execute(
            "UPDATE empresas SET plan=?, plan_vence=?, grace_hasta=NULL, activa=1 WHERE id=?",
            [$sub['plan'], $vence, $empresa_id]
        );
        DB::insert(
            "INSERT INTO pagos_suscripcion
                (suscripcion_id, empresa_id, mp_payment_id, monto_mxn, estado, fecha_pago, detalle)
             VALUES (?, ?, ?, ?, 'approved', NOW(), ?)",
            [
                $sub['id'], $empresa_id, (string)$resp['id'], (float)$sub['monto_mxn'],
                json_encode(['tipo' => 'renovacion', 'status_detail' => $status_detail]),
            ]
        );

        $cobros_ok++;
        $log("Cobro OK: empresa #{$empresa_id} — nuevo vence {$vence}");
        continue;
    }

    $err_msg = substr($status_detail ?: ($resp['error'] ?? 'unknown'), 0, 255);
    DB::execute("UPDATE suscripciones SET ultimo_error=? WHERE id=?", [$err_msg, $sub['id']]);

    $intento = (int)$sub['intentos_cobro'] + 1;
    $cobros_err++;
    $log("Cobro FALLÓ (intento {$intento}): empresa #{$empresa_id} — {$err_msg}");

    if ($sub['email'] && ($intento === 1 || $intento === 3)) {
        $subject = $intento === 1
            ? 'No pudimos cobrar tu renovación — actualiza tu tarjeta'
            : 'ÚLTIMO AVISO: tu tarjeta sigue fallando';
        $url_cambiar = BASE_URL . '/config?tab=suscripcion';
        Mailer::enviar(
            $sub['email'],
            $sub['nombre'] ?: 'Usuario',
            $subject,
            '<p>Hola ' . htmlspecialchars($sub['nombre'] ?: 'Usuario') . ',</p>' .
            '<p>Intentamos cobrar tu plan <strong>' . ucfirst($sub['plan']) . ' ' . ucfirst($sub['ciclo']) . '</strong> ' .
            'por <strong>$' . number_format((float)$sub['monto_mxn'], 2) . ' MXN</strong> pero tu tarjeta fue rechazada.</p>' .
            '<p>Razón: <em>' . htmlspecialchars($err_msg) . '</em></p>' .
            '<p><a href="' . $url_cambiar . '">Actualiza tu método de pago</a> para mantener tu plan activo. ' .
            ($intento === 1 ? 'Volveremos a intentar en 24 horas.' : 'Si no actualizas en 7 días, tu plan bajará a Free.') .
            '</p>'
        );
    }
}
$log("Cobros: {$cobros_ok} ok, {$cobros_err} err (total intentados: " . count($por_cobrar) . ")");

// ─── 2. Grace period: 3 intentos fallidos → iniciar grace ────
$fallidas = DB::query(
    "SELECT empresa_id FROM suscripciones
     WHERE estado='active' AND cancel_al_vencer=0
       AND intentos_cobro >= 3
       AND mp_customer_id IS NOT NULL"
);
foreach ($fallidas as $row) {
    $empresa_id = (int)$row['empresa_id'];
    $grace_existente = DB::val("SELECT grace_hasta FROM empresas WHERE id=?", [$empresa_id]);
    if (!$grace_existente) {
        $grace = date('Y-m-d', strtotime('+7 days'));
        DB::execute("UPDATE empresas SET grace_hasta=? WHERE id=?", [$grace, $empresa_id]);
        $log("Grace iniciado: empresa #{$empresa_id} hasta {$grace}");
    }
}

// ─── 3. Grace expirado → degradar a Free ─────────────────────
$expiradas_grace = DB::query(
    "SELECT e.id, e.nombre, e.email, e.plan, e.grace_hasta
     FROM empresas e
     WHERE e.grace_hasta IS NOT NULL
       AND e.grace_hasta < CURDATE()
       AND e.plan IN ('pro','business')"
);

foreach ($expiradas_grace as $emp) {
    DB::execute(
        "UPDATE empresas SET plan='free', plan_vence=NULL, grace_hasta=NULL, activa=1 WHERE id=?",
        [$emp['id']]
    );
    DB::execute(
        "UPDATE suscripciones SET estado='cancelled', cancel_al_vencer=1, cancelled_at=NOW() WHERE empresa_id=?",
        [$emp['id']]
    );

    if ($emp['email']) {
        Mailer::enviar(
            $emp['email'],
            $emp['nombre'] ?: 'Usuario',
            'Tu plan CotizaCloud ha sido degradado',
            '<p>Hola ' . htmlspecialchars($emp['nombre'] ?: 'Usuario') . ',</p>' .
            '<p>Tu período de gracia terminó y no pudimos procesar tu pago.</p>' .
            '<p>Tu cuenta se cambió al plan <strong>Free</strong> (máximo 25 cotizaciones).</p>' .
            '<p>Para reactivar tu plan, visita <a href="' . BASE_URL . '/config?tab=suscripcion">Configuración > Suscripción</a>.</p>'
        );
    }
    $log("Degradada a Free: empresa #{$emp['id']} ({$emp['nombre']})");
}

// ─── 4. Plan vencido sin grace ni suscripción activa → Free ──
$vencidas_sin_grace = DB::query(
    "SELECT e.id, e.nombre, e.email, e.plan, e.plan_vence
     FROM empresas e
     WHERE e.plan IN ('pro','business')
       AND e.plan_vence IS NOT NULL
       AND e.plan_vence < CURDATE()
       AND e.grace_hasta IS NULL"
);

foreach ($vencidas_sin_grace as $emp) {
    $tiene_sub_activa = DB::val(
        "SELECT COUNT(*) FROM suscripciones WHERE empresa_id=? AND estado='active' AND cancel_al_vencer=0 AND mp_customer_id IS NOT NULL",
        [$emp['id']]
    );
    if ($tiene_sub_activa) continue; // el paso 1 la está cobrando

    DB::execute(
        "UPDATE empresas SET plan='free', plan_vence=NULL, activa=1 WHERE id=?",
        [$emp['id']]
    );
    $log("Degradada a Free (sin suscripción activa): empresa #{$emp['id']}");
}

// ─── 5. Aviso 7 días antes de vencer (sin auto-renovar) ──────
$por_vencer = DB::query(
    "SELECT e.id, e.nombre, e.email, e.plan, e.plan_vence, s.cancel_al_vencer, s.mp_customer_id
     FROM empresas e
     LEFT JOIN suscripciones s ON s.empresa_id = e.id
     WHERE e.plan IN ('pro','business')
       AND e.plan_vence IS NOT NULL
       AND e.plan_vence = DATE_ADD(CURDATE(), INTERVAL 7 DAY)
       AND (s.cancel_al_vencer = 1 OR s.id IS NULL OR s.mp_customer_id IS NULL)"
);

foreach ($por_vencer as $emp) {
    if ($emp['email']) {
        Mailer::enviar(
            $emp['email'],
            $emp['nombre'] ?: 'Usuario',
            'Tu plan CotizaCloud vence en 7 días',
            '<p>Hola ' . htmlspecialchars($emp['nombre'] ?: 'Usuario') . ',</p>' .
            '<p>Tu plan <strong>' . ucfirst($emp['plan']) . '</strong> vence el <strong>' .
            date('d/m/Y', strtotime($emp['plan_vence'])) . '</strong>.</p>' .
            '<p>Si deseas renovar, visita <a href="' . BASE_URL . '/config?tab=suscripcion">Configuración > Suscripción</a>.</p>'
        );
        $log("Aviso 7 días: empresa #{$emp['id']}");
    }
}

// ─── 6. Aviso 3 días antes de grace expire ──────────────────
$grace_3d = DB::query(
    "SELECT e.id, e.nombre, e.email, e.grace_hasta
     FROM empresas e
     WHERE e.grace_hasta IS NOT NULL
       AND e.grace_hasta = DATE_ADD(CURDATE(), INTERVAL 3 DAY)"
);

foreach ($grace_3d as $emp) {
    if ($emp['email']) {
        Mailer::enviar(
            $emp['email'],
            $emp['nombre'] ?: 'Usuario',
            'URGENTE: 3 días para regularizar tu pago — CotizaCloud',
            '<p>Hola ' . htmlspecialchars($emp['nombre'] ?: 'Usuario') . ',</p>' .
            '<p><strong>Tu cuenta será degradada al plan Free en 3 días</strong> si no se procesa tu pago.</p>' .
            '<p>Revisa tu método de pago o contáctanos si necesitas ayuda.</p>'
        );
        $log("Aviso urgente grace 3d: empresa #{$emp['id']}");
    }
}

$log('Procesamiento completado.');
