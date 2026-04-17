<?php
// ============================================================
//  CotizaCloud — cron/procesar_suscripciones.php
//  Cron diario (3am): grace period, degradación, emails aviso
//
//  Ejecutar: php /path/to/cron/procesar_suscripciones.php
//  Crontab:  0 3 * * * php /home/cotizacl/cotizacloud/cron/procesar_suscripciones.php
// ============================================================

define('COTIZAAPP', true);
require_once dirname(__DIR__) . '/config.php';

$log = function(string $msg) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    error_log('[Cron Suscripciones] ' . $msg);
};

$log('Iniciando procesamiento de suscripciones...');

// ─── 1. Empresas en grace period que ya expiraron ───────────
$expiradas_grace = DB::query(
    "SELECT e.id, e.nombre, e.email, e.plan, e.grace_hasta, s.mp_preapproval_id
     FROM empresas e
     LEFT JOIN suscripciones s ON s.empresa_id = e.id
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
            '<p>Tu período de gracia ha finalizado y no pudimos procesar tu pago.</p>' .
            '<p>Tu cuenta ha sido cambiada al plan <strong>Free</strong> (máximo 25 cotizaciones).</p>' .
            '<p>Para reactivar tu plan, visita <a href="' . BASE_URL . '/config?tab=suscripcion">Configuración > Suscripción</a>.</p>'
        );
    }
    $log("Degradada a Free: empresa #{$emp['id']} ({$emp['nombre']})");
}

// ─── 2. Empresas con plan pagado que venció sin grace ────────
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
        "SELECT COUNT(*) FROM suscripciones WHERE empresa_id=? AND estado='active' AND cancel_al_vencer=0",
        [$emp['id']]
    );

    if ($tiene_sub_activa) {
        $grace = date('Y-m-d', strtotime('+7 days'));
        DB::execute("UPDATE empresas SET grace_hasta=? WHERE id=?", [$grace, $emp['id']]);
        $log("Grace period activado: empresa #{$emp['id']} hasta {$grace}");

        if ($emp['email']) {
            Mailer::enviar(
                $emp['email'],
                $emp['nombre'] ?: 'Usuario',
                'Problema con tu pago — CotizaCloud',
                '<p>Hola ' . htmlspecialchars($emp['nombre'] ?: 'Usuario') . ',</p>' .
                '<p>No pudimos procesar tu último pago para el plan <strong>' . ucfirst($emp['plan']) . '</strong>.</p>' .
                '<p>Tienes <strong>7 días</strong> para regularizar tu pago antes de que tu cuenta sea degradada al plan Free.</p>' .
                '<p>Si crees que es un error, revisa tu método de pago en MercadoPago.</p>'
            );
        }
    } else {
        DB::execute(
            "UPDATE empresas SET plan='free', plan_vence=NULL, activa=1 WHERE id=?",
            [$emp['id']]
        );
        $log("Degradada a Free (sin suscripción activa): empresa #{$emp['id']}");
    }
}

// ─── 3. Aviso de vencimiento próximo (7 días) ───────────────
$por_vencer = DB::query(
    "SELECT e.id, e.nombre, e.email, e.plan, e.plan_vence, s.cancel_al_vencer
     FROM empresas e
     LEFT JOIN suscripciones s ON s.empresa_id = e.id
     WHERE e.plan IN ('pro','business')
       AND e.plan_vence IS NOT NULL
       AND e.plan_vence = DATE_ADD(CURDATE(), INTERVAL 7 DAY)
       AND (s.cancel_al_vencer = 1 OR s.id IS NULL)"
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

// ─── 4. Aviso 3 días antes de grace expire ──────────────────
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
