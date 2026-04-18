<?php
// ============================================================
//  CotizaCloud — core/MercadoPago.php
//  Wrapper para MercadoPago Preapproval (suscripciones recurrentes)
//  API: https://api.mercadopago.com/preapproval
// ============================================================

defined('COTIZAAPP') or die;

class MercadoPago
{
    private const API_BASE = 'https://api.mercadopago.com';

    private static function accessToken(): string
    {
        return defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : '';
    }

    // ─── Precios ────────────────────────────────────────────────
    public static function precios(): array
    {
        return [
            'pro' => [
                'mensual' => 299.00,
                'anual'   => 2868.00,
            ],
            'business' => [
                'mensual' => 799.00,
                'anual'   => 7668.00,
            ],
        ];
    }

    public static function precio(string $plan, string $ciclo): float
    {
        return self::precios()[$plan][$ciclo] ?? 0.0;
    }

    // ─── Crear Preference (Checkout Pro) ────────────────────────
    // Flujo nuevo: pago único con tokenización de tarjeta para
    // cobros recurrentes via /v1/payments. Sustituye Preapproval.
    public static function crearPreference(array $data): array
    {
        $plan  = $data['plan'];
        $ciclo = $data['ciclo'];
        $email = $data['email'];
        $empresa_id = $data['empresa_id'];
        $nombre  = $data['nombre'] ?? '';
        $telefono = $data['telefono'] ?? '';
        $rfc = $data['rfc'] ?? '';

        $monto = self::precio($plan, $ciclo);
        if ($monto <= 0) {
            return ['error' => 'Plan o ciclo inválido'];
        }

        $plan_label = $plan === 'business' ? 'Business' : 'Pro';
        $ciclo_label = $ciclo === 'anual' ? 'Anual' : 'Mensual';

        $payer = ['email' => $email];
        if ($nombre) {
            $parts = explode(' ', trim($nombre), 2);
            $payer['name'] = $parts[0];
            if (isset($parts[1])) $payer['surname'] = $parts[1];
        }
        if ($telefono) {
            $payer['phone'] = [
                'area_code' => '',
                'number' => preg_replace('/[^0-9]/', '', $telefono),
            ];
        }
        if ($rfc) {
            $payer['identification'] = [
                'type' => 'RFC',
                'number' => $rfc,
            ];
        }

        $body = [
            'items' => [[
                'id'          => "cz_{$plan}_{$ciclo}",
                'title'       => "CotizaCloud {$plan_label} — {$ciclo_label}",
                'description' => "Suscripción {$plan_label} {$ciclo_label} a CotizaCloud",
                'quantity'    => 1,
                'currency_id' => 'MXN',
                'unit_price'  => (float)$monto,
                'category_id' => 'services',
            ]],
            'payer' => $payer,
            'external_reference' => "cz_{$empresa_id}_{$plan}_{$ciclo}",
            'back_urls' => [
                'success' => BASE_URL . '/api/mp/return?empresa_id=' . $empresa_id,
                'pending' => BASE_URL . '/api/mp/return?empresa_id=' . $empresa_id,
                'failure' => BASE_URL . '/api/mp/return?empresa_id=' . $empresa_id,
            ],
            'auto_return' => 'approved',
            'statement_descriptor' => 'CotizaCloud',
            'metadata' => [
                'empresa_id' => $empresa_id,
                'plan'       => $plan,
                'ciclo'      => $ciclo,
            ],
        ];

        return self::post('/checkout/preferences', $body);
    }

    // ─── Obtener pago (consulta /v1/payments/{id}) ──────────────
    public static function obtenerPago(string $paymentId): array
    {
        return self::get('/v1/payments/' . $paymentId);
    }

    // ─── Cobrar tarjeta guardada (cobro recurrente) ─────────────
    // Usa card_id + customer_id para cobrar sin intervención del
    // usuario. Solo funciona si la tarjeta fue guardada previamente
    // desde un pago exitoso (first-payment Checkout Pro).
    public static function cobrarTarjetaGuardada(array $data): array
    {
        $empresa_id = $data['empresa_id'];
        $customer_id = $data['customer_id'];
        $card_id = $data['card_id'];
        $monto = (float)$data['monto'];
        $descripcion = $data['descripcion'] ?? 'CotizaCloud — Renovación';
        $plan = $data['plan'];
        $ciclo = $data['ciclo'];
        $email = $data['email'];

        // Generar card token a partir de la tarjeta guardada
        $tokenResp = self::post('/v1/card_tokens', [
            'card_id'     => $card_id,
            'customer_id' => $customer_id,
        ]);

        if (isset($tokenResp['error']) || empty($tokenResp['id'])) {
            return [
                'error'   => 'token_error',
                'message' => 'No se pudo generar token de la tarjeta guardada',
                'detail'  => $tokenResp,
            ];
        }

        $idempotencyKey = 'cz_renew_' . $empresa_id . '_' . date('Ymd') . '_' . bin2hex(random_bytes(4));

        $body = [
            'transaction_amount'   => $monto,
            'token'                => $tokenResp['id'],
            'description'          => $descripcion,
            'installments'         => 1,
            'payer'                => [
                'type'  => 'customer',
                'id'    => $customer_id,
                'email' => $email,
            ],
            'binary_mode'          => true,
            'statement_descriptor' => 'CotizaCloud',
            'external_reference'   => "cz_{$empresa_id}_{$plan}_{$ciclo}_renew",
            'metadata'             => [
                'empresa_id' => $empresa_id,
                'plan'       => $plan,
                'ciclo'      => $ciclo,
                'tipo'       => 'renovacion',
            ],
        ];

        if (!empty($data['payment_method_id'])) {
            $body['payment_method_id'] = $data['payment_method_id'];
        }

        return self::requestWithIdempotency('POST', '/v1/payments', $body, $idempotencyKey);
    }

    // ─── Extraer datos relevantes de un pago ────────────────────
    public static function extraerDatosPago(array $pago): array
    {
        return [
            'status'            => $pago['status'] ?? '',
            'status_detail'     => $pago['status_detail'] ?? '',
            'payment_id'        => (string)($pago['id'] ?? ''),
            'transaction_amount'=> (float)($pago['transaction_amount'] ?? 0),
            'customer_id'       => $pago['payer']['id'] ?? null,
            'card_id'           => $pago['card']['id'] ?? null,
            'card_last4'        => $pago['card']['last_four_digits'] ?? null,
            'card_brand'        => $pago['payment_method_id'] ?? null,
            'card_exp_month'    => $pago['card']['expiration_month'] ?? null,
            'card_exp_year'     => $pago['card']['expiration_year'] ?? null,
            'payment_method_id' => $pago['payment_method_id'] ?? null,
            'payment_type_id'   => $pago['payment_type_id'] ?? null,
            'external_reference'=> $pago['external_reference'] ?? '',
            'date_approved'     => $pago['date_approved'] ?? null,
        ];
    }

    // ─── LEGACY: Crear suscripción (preapproval) ────────────────
    // Mantener por compatibilidad — no se usa en flujo nuevo.
    public static function crearPreapproval(array $data): array
    {
        $plan  = $data['plan'];
        $ciclo = $data['ciclo'];
        $email = $data['email'];
        $empresa_id = $data['empresa_id'];

        $monto = self::precio($plan, $ciclo);
        if ($monto <= 0) {
            return ['error' => 'Plan o ciclo inválido'];
        }

        $plan_label = $plan === 'business' ? 'Business' : 'Pro';
        $ciclo_label = $ciclo === 'anual' ? 'Anual' : 'Mensual';
        $frequency = $ciclo === 'anual' ? ['frequency' => 12, 'frequency_type' => 'months']
                                         : ['frequency' => 1,  'frequency_type' => 'months'];

        $body = [
            'reason'             => "CotizaCloud {$plan_label} — {$ciclo_label}",
            'external_reference' => "cz_{$empresa_id}_{$plan}_{$ciclo}",
            'payer_email'        => $email,
            'auto_recurring'     => [
                'frequency'      => $frequency['frequency'],
                'frequency_type' => $frequency['frequency_type'],
                'transaction_amount' => $ciclo === 'anual' ? $monto : $monto,
                'currency_id'    => 'MXN',
            ],
            'back_url'  => BASE_URL . '/api/mp/return?empresa_id=' . $empresa_id,
            'status'    => 'pending',
        ];

        return self::post('/preapproval', $body);
    }

    // ─── Consultar suscripción ──────────────────────────────────
    public static function obtenerPreapproval(string $preapprovalId): array
    {
        return self::get('/preapproval/' . $preapprovalId);
    }

    // ─── Cancelar suscripción ───────────────────────────────────
    public static function cancelarPreapproval(string $preapprovalId): array
    {
        return self::put('/preapproval/' . $preapprovalId, [
            'status' => 'cancelled',
        ]);
    }

    // ─── Pausar suscripción ─────────────────────────────────────
    public static function pausarPreapproval(string $preapprovalId): array
    {
        return self::put('/preapproval/' . $preapprovalId, [
            'status' => 'paused',
        ]);
    }

    // ─── Sincronizar estado con MP (reemplaza webhook) ──────────
    // Consulta MP por el estado del preapproval y los pagos recientes
    // Actualiza plan/plan_vence/estado local con la verdad de MP
    public static function sincronizar(int $empresa_id): array
    {
        $sub = DB::row(
            "SELECT id, mp_preapproval_id, ciclo FROM suscripciones
             WHERE empresa_id = ? AND mp_preapproval_id IS NOT NULL",
            [$empresa_id]
        );

        // Marca sync timestamp siempre (aunque no haya suscripción, evita queries repetidas)
        DB::execute("UPDATE empresas SET ultima_sync_mp = NOW() WHERE id = ?", [$empresa_id]);

        if (!$sub) {
            return ['synced' => false, 'reason' => 'no_subscription'];
        }

        $remote = self::obtenerPreapproval($sub['mp_preapproval_id']);
        if (isset($remote['error'])) {
            error_log('[MP sync] Error obteniendo preapproval ' . $sub['mp_preapproval_id'] . ': ' . json_encode($remote));
            return ['synced' => false, 'reason' => 'mp_api_error', 'detail' => $remote];
        }

        $status   = $remote['status'] ?? '';
        $extRef   = $remote['external_reference'] ?? '';
        $nextDate = $remote['next_payment_date'] ?? null;

        preg_match('/^cz_(\d+)_(pro|business)_(mensual|anual)$/', $extRef, $m);
        if (!$m) {
            return ['synced' => false, 'reason' => 'invalid_external_reference'];
        }
        $plan  = $m[2];
        $ciclo = $m[3];

        if ($status === 'authorized') {
            if ($nextDate) {
                $vence = date('Y-m-d', strtotime($nextDate));
            } else {
                $dias  = $ciclo === 'anual' ? 365 : 30;
                $vence = date('Y-m-d', strtotime("+{$dias} days"));
            }

            DB::execute(
                "UPDATE empresas SET plan=?, plan_vence=?, grace_hasta=NULL, activa=1 WHERE id=?",
                [$plan, $vence, $empresa_id]
            );
            DB::execute(
                "UPDATE suscripciones SET plan=?, estado='active', updated_at=NOW() WHERE empresa_id=?",
                [$plan, $empresa_id]
            );
            return ['synced' => true, 'status' => 'authorized', 'plan_vence' => $vence];
        }

        if ($status === 'cancelled') {
            DB::execute(
                "UPDATE suscripciones SET estado='cancelled', cancel_al_vencer=1, cancelled_at=COALESCE(cancelled_at, NOW()) WHERE empresa_id=?",
                [$empresa_id]
            );
            return ['synced' => true, 'status' => 'cancelled'];
        }

        if ($status === 'paused') {
            DB::execute(
                "UPDATE suscripciones SET estado='paused', updated_at=NOW() WHERE empresa_id=?",
                [$empresa_id]
            );
            return ['synced' => true, 'status' => 'paused'];
        }

        return ['synced' => true, 'status' => $status];
    }

    // ─── Validar firma webhook ──────────────────────────────────
    public static function validarWebhook(): bool
    {
        $secret = defined('MP_WEBHOOK_SECRET') ? MP_WEBHOOK_SECRET : '';
        if (!$secret) {
            error_log('[MP Webhook] MP_WEBHOOK_SECRET no configurado — validación HMAC deshabilitada (modo testing)');
            return true;
        }

        $xSignature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
        $xRequestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? '';

        if (!$xSignature || !$xRequestId) return false;

        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) $parts[trim($kv[0])] = trim($kv[1]);
        }

        $ts   = $parts['ts'] ?? '';
        $hash = $parts['v1'] ?? '';
        if (!$ts || !$hash) return false;

        $dataId = $_GET['data.id'] ?? $_GET['id'] ?? '';

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }

    // ─── Procesar evento webhook ────────────────────────────────
    public static function procesarWebhook(array $body): array
    {
        $type   = $body['type'] ?? '';
        $action = $body['action'] ?? '';
        $dataId = $body['data']['id'] ?? '';

        if (!$dataId) return ['processed' => false, 'reason' => 'no data.id'];

        if ($type === 'subscription_preapproval') {
            return self::procesarPreapproval($dataId, $action);
        }

        if ($type === 'payment') {
            return self::procesarPago($dataId, $action);
        }

        return ['processed' => false, 'reason' => "type not handled: {$type}"];
    }

    private static function procesarPreapproval(string $preapprovalId, string $action): array
    {
        $remote = self::obtenerPreapproval($preapprovalId);
        if (isset($remote['error'])) return $remote;

        $status = $remote['status'] ?? '';
        $extRef = $remote['external_reference'] ?? '';

        preg_match('/^cz_(\d+)_(pro|business)_(mensual|anual)$/', $extRef, $m);
        if (!$m) return ['processed' => false, 'reason' => 'invalid external_reference'];

        $empresa_id = (int)$m[1];
        $plan  = $m[2];
        $ciclo = $m[3];
        $monto = (float)($remote['auto_recurring']['transaction_amount'] ?? 0);

        if ($status === 'authorized') {
            $dias = $ciclo === 'anual' ? 365 : 30;
            $vence = date('Y-m-d', strtotime("+{$dias} days"));

            $exists = DB::row("SELECT id FROM suscripciones WHERE empresa_id = ?", [$empresa_id]);
            if ($exists) {
                DB::execute(
                    "UPDATE suscripciones SET plan=?, ciclo=?, mp_preapproval_id=?, estado='active',
                     monto_mxn=?, cancel_al_vencer=0, cancelled_at=NULL, updated_at=NOW()
                     WHERE empresa_id=?",
                    [$plan, $ciclo, $preapprovalId, $monto, $empresa_id]
                );
            } else {
                DB::insert(
                    "INSERT INTO suscripciones (empresa_id, plan, ciclo, mp_preapproval_id, estado, monto_mxn)
                     VALUES (?, ?, ?, ?, 'active', ?)",
                    [$empresa_id, $plan, $ciclo, $preapprovalId, $monto]
                );
            }

            DB::execute(
                "UPDATE empresas SET plan=?, plan_vence=?, grace_hasta=NULL, activa=1 WHERE id=?",
                [$plan, $vence, $empresa_id]
            );

            return ['processed' => true, 'action' => 'activated', 'empresa_id' => $empresa_id];
        }

        if ($status === 'cancelled') {
            DB::execute(
                "UPDATE suscripciones SET estado='cancelled', cancel_al_vencer=1, cancelled_at=NOW() WHERE empresa_id=?",
                [$empresa_id]
            );
            return ['processed' => true, 'action' => 'cancelled', 'empresa_id' => $empresa_id];
        }

        if ($status === 'paused') {
            DB::execute(
                "UPDATE suscripciones SET estado='paused', updated_at=NOW() WHERE empresa_id=?",
                [$empresa_id]
            );
            return ['processed' => true, 'action' => 'paused', 'empresa_id' => $empresa_id];
        }

        return ['processed' => false, 'reason' => "preapproval status: {$status}"];
    }

    private static function procesarPago(string $paymentId, string $action): array
    {
        $idempotent = DB::row("SELECT id FROM pagos_suscripcion WHERE mp_payment_id = ?", [$paymentId]);
        if ($idempotent) return ['processed' => true, 'action' => 'already_processed'];

        $remote = self::get('/v1/payments/' . $paymentId);
        if (isset($remote['error'])) return $remote;

        $status     = $remote['status'] ?? '';
        $preapId    = $remote['metadata']['preapproval_id'] ?? '';
        $monto      = (float)($remote['transaction_amount'] ?? 0);
        $fechaPago  = $remote['date_approved'] ?? $remote['date_created'] ?? date('Y-m-d H:i:s');

        $sub = null;
        if ($preapId) {
            $sub = DB::row("SELECT * FROM suscripciones WHERE mp_preapproval_id = ?", [$preapId]);
        }
        if (!$sub) {
            $extRef = $remote['external_reference'] ?? '';
            preg_match('/^cz_(\d+)/', $extRef, $m);
            if ($m) {
                $sub = DB::row("SELECT * FROM suscripciones WHERE empresa_id = ?", [(int)$m[1]]);
            }
        }
        if (!$sub) return ['processed' => false, 'reason' => 'subscription not found for payment'];

        DB::insert(
            "INSERT INTO pagos_suscripcion (suscripcion_id, empresa_id, mp_payment_id, monto_mxn, estado, fecha_pago, detalle)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $sub['id'], $sub['empresa_id'], $paymentId, $monto, $status,
                $fechaPago, json_encode(['status_detail' => $remote['status_detail'] ?? ''])
            ]
        );

        if ($status === 'approved') {
            $ciclo = $sub['ciclo'];
            $dias  = $ciclo === 'anual' ? 365 : 30;
            $vence_actual = DB::val("SELECT plan_vence FROM empresas WHERE id=?", [$sub['empresa_id']]);
            $base = ($vence_actual && $vence_actual >= date('Y-m-d')) ? $vence_actual : date('Y-m-d');
            $nuevo_vence = date('Y-m-d', strtotime($base . " +{$dias} days"));

            DB::execute(
                "UPDATE empresas SET plan_vence=?, grace_hasta=NULL, activa=1 WHERE id=?",
                [$nuevo_vence, $sub['empresa_id']]
            );

            return ['processed' => true, 'action' => 'payment_approved', 'empresa_id' => $sub['empresa_id']];
        }

        if ($status === 'rejected') {
            $grace = date('Y-m-d', strtotime('+7 days'));
            DB::execute(
                "UPDATE empresas SET grace_hasta=? WHERE id=? AND grace_hasta IS NULL",
                [$grace, $sub['empresa_id']]
            );
            return ['processed' => true, 'action' => 'payment_rejected_grace', 'empresa_id' => $sub['empresa_id']];
        }

        return ['processed' => true, 'action' => "payment_{$status}"];
    }

    // ─── HTTP helpers ───────────────────────────────────────────
    private static function get(string $path): array
    {
        return self::request('GET', $path);
    }

    private static function post(string $path, array $body): array
    {
        return self::request('POST', $path, $body);
    }

    private static function put(string $path, array $body): array
    {
        return self::request('PUT', $path, $body);
    }

    // POST con X-Idempotency-Key (requerido por MP en /v1/payments)
    private static function requestWithIdempotency(string $method, string $path, array $body, string $key): array
    {
        return self::request($method, $path, $body, ['X-Idempotency-Key: ' . $key]);
    }

    private static function request(string $method, string $path, ?array $body = null, array $extraHeaders = []): array
    {
        $url = self::API_BASE . $path;
        $ch  = curl_init($url);

        $headers = array_merge([
            'Authorization: Bearer ' . self::accessToken(),
            'Content-Type: application/json',
        ], $extraHeaders);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => 'curl_error', 'message' => $error];
        }

        $data = json_decode($response, true) ?: [];

        if ($httpCode >= 400) {
            $data['error'] = $data['error'] ?? 'http_error';
            $data['http_code'] = $httpCode;
        }

        return $data;
    }
}
