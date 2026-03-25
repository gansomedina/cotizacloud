<?php
// ============================================================
//  CotizaApp — core/PushNotification.php
//  Servicio de Push Notifications vía APNs (HTTP/2)
// ============================================================

defined('COTIZAAPP') or die;

class PushNotification
{
    // ─── Registrar / actualizar token de dispositivo ─────────
    public static function registrar_token(
        int $empresa_id,
        int $usuario_id,
        string $token,
        string $plataforma = 'ios'
    ): void {
        if (!$token) return;

        // Upsert: si el token ya existe, actualizar usuario/empresa
        DB::execute(
            "INSERT INTO dispositivos_push (empresa_id, usuario_id, token, plataforma, activo)
             VALUES (?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE
                empresa_id = VALUES(empresa_id),
                usuario_id = VALUES(usuario_id),
                plataforma = VALUES(plataforma),
                activo = 1,
                updated_at = NOW()",
            [$empresa_id, $usuario_id, $token, $plataforma]
        );
    }

    // ─── Desactivar token (cuando se hace logout o falla) ────
    public static function desactivar_token(string $token): void
    {
        DB::execute(
            "UPDATE dispositivos_push SET activo = 0 WHERE token = ?",
            [$token]
        );
    }

    // ─── Obtener tokens activos de una empresa ───────────────
    public static function tokens_empresa(int $empresa_id): array
    {
        return DB::query(
            "SELECT id, usuario_id, token, plataforma
             FROM dispositivos_push
             WHERE empresa_id = ? AND activo = 1",
            [$empresa_id]
        );
    }

    // ─── Enviar push a todos los dispositivos de una empresa ─
    public static function enviar_a_empresa(
        int $empresa_id,
        string $tipo,
        string $titulo,
        string $cuerpo,
        array $datos = []
    ): int {
        $dispositivos = self::tokens_empresa($empresa_id);
        $enviadas = 0;

        foreach ($dispositivos as $disp) {
            $ok = false;
            $error = null;

            try {
                if ($disp['plataforma'] === 'ios') {
                    $ok = self::enviar_apns($disp['token'], $titulo, $cuerpo, $datos);
                } elseif ($disp['plataforma'] === 'web') {
                    $ok = WebPush::enviar($disp['token'], $titulo, $cuerpo, $datos);
                }
                // Android (FCM) se agregará después
            } catch (\Exception $e) {
                $error = $e->getMessage();
                // Si el token es inválido o subscription expirada, desactivarlo
                if (self::es_token_invalido($error) || WebPush::es_subscription_expirada($error ?? '')) {
                    self::desactivar_token($disp['token']);
                }
            }

            // Log
            DB::insert(
                "INSERT INTO notificaciones_push
                 (empresa_id, usuario_id, dispositivo_id, tipo, titulo, cuerpo, datos, enviada, error)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $empresa_id,
                    $disp['usuario_id'],
                    $disp['id'],
                    $tipo,
                    $titulo,
                    $cuerpo,
                    $datos ? json_encode($datos) : null,
                    $ok ? 1 : 0,
                    $error,
                ]
            );

            if ($ok) $enviadas++;
        }

        return $enviadas;
    }

    // ─── Enviar vía APNs (HTTP/2 con curl) ───────────────────
    private static function enviar_apns(
        string $token,
        string $titulo,
        string $cuerpo,
        array $datos = []
    ): bool {
        // Configuración APNs — se lee de constantes o env
        $key_path  = defined('APNS_KEY_PATH')  ? APNS_KEY_PATH  : (getenv('APNS_KEY_PATH')  ?: '');
        $key_id    = defined('APNS_KEY_ID')    ? APNS_KEY_ID    : (getenv('APNS_KEY_ID')    ?: '');
        $team_id   = defined('APNS_TEAM_ID')   ? APNS_TEAM_ID   : (getenv('APNS_TEAM_ID')   ?: '');
        $bundle_id = defined('APNS_BUNDLE_ID') ? APNS_BUNDLE_ID : (getenv('APNS_BUNDLE_ID') ?: 'com.cotizacloud.app');
        $env       = defined('APNS_ENV')       ? APNS_ENV       : (getenv('APNS_ENV')       ?: 'production');

        if (!$key_path || !$key_id || !$team_id) {
            throw new \Exception('APNs no configurado: faltan APNS_KEY_PATH, APNS_KEY_ID o APNS_TEAM_ID');
        }

        // Generar JWT para APNs
        $jwt = self::generar_jwt_apns($key_path, $key_id, $team_id);

        // URL APNs
        $url = ($env === 'production')
            ? "https://api.push.apple.com/3/device/{$token}"
            : "https://api.sandbox.push.apple.com/3/device/{$token}";

        // Payload
        $payload = [
            'aps' => [
                'alert' => [
                    'title' => $titulo,
                    'body'  => $cuerpo,
                ],
                'sound' => 'default',
                'badge' => 1,
            ],
        ];

        if ($datos) {
            $payload['data'] = $datos;
        }

        $json = json_encode($payload);

        // Enviar con curl HTTP/2
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
            CURLOPT_HTTPHEADER     => [
                "Authorization: bearer {$jwt}",
                "apns-topic: {$bundle_id}",
                "apns-push-type: alert",
                "apns-priority: 10",
                "Content-Type: application/json",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception("curl error: {$curlError}");
        }

        if ($httpCode === 200) {
            return true;
        }

        $body = json_decode($response, true);
        $reason = $body['reason'] ?? "HTTP {$httpCode}";
        throw new \Exception("APNs error: {$reason}");
    }

    // ─── Generar JWT para APNs ───────────────────────────────
    private static function generar_jwt_apns(
        string $key_path,
        string $key_id,
        string $team_id
    ): string {
        // Cache JWT por 50 minutos (APNs acepta hasta 60 min)
        static $cache = ['jwt' => null, 'exp' => 0];
        if ($cache['jwt'] && time() < $cache['exp']) {
            return $cache['jwt'];
        }

        $key = file_get_contents($key_path);
        if (!$key) {
            throw new \Exception("No se pudo leer la key APNs: {$key_path}");
        }

        $header = self::base64url(json_encode([
            'alg' => 'ES256',
            'kid' => $key_id,
        ]));

        $claims = self::base64url(json_encode([
            'iss' => $team_id,
            'iat' => time(),
        ]));

        $pkey = openssl_pkey_get_private($key);
        if (!$pkey) {
            throw new \Exception('Key APNs inválida');
        }

        $signature = '';
        openssl_sign("{$header}.{$claims}", $signature, $pkey, OPENSSL_ALGO_SHA256);

        // Convertir DER signature a raw R||S (64 bytes)
        $signature = self::der_to_raw($signature);

        $jwt = "{$header}.{$claims}." . self::base64url($signature);

        $cache['jwt'] = $jwt;
        $cache['exp'] = time() + 3000; // 50 min

        return $jwt;
    }

    // ─── Helpers ─────────────────────────────────────────────
    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Convierte firma DER de openssl a formato raw R||S de 64 bytes
    private static function der_to_raw(string $der): string
    {
        $hex = unpack('H*', $der)[1];
        // DER: 30 <len> 02 <rlen> <R> 02 <slen> <S>
        $pos = 4; // skip 30 <len>
        $pos += 2; // skip 02
        $rLen = hexdec(substr($hex, $pos, 2));
        $pos += 2;
        $r = substr($hex, $pos, $rLen * 2);
        $pos += $rLen * 2;
        $pos += 2; // skip 02
        $sLen = hexdec(substr($hex, $pos, 2));
        $pos += 2;
        $s = substr($hex, $pos, $sLen * 2);

        // Pad/trim to 32 bytes each
        $r = str_pad(substr($r, -64), 64, '0', STR_PAD_LEFT);
        $s = str_pad(substr($s, -64), 64, '0', STR_PAD_LEFT);

        return pack('H*', $r . $s);
    }

    // ─── Obtener tokens activos del superadmin ──────────────
    public static function tokens_superadmin(): array
    {
        return DB::query(
            "SELECT d.id, d.usuario_id, d.token, d.plataforma
             FROM dispositivos_push d
             JOIN usuarios u ON u.id = d.usuario_id
             WHERE u.rol = 'superadmin' AND d.activo = 1"
        );
    }

    // ─── Enviar push al superadmin ────────────────────────────
    public static function enviar_a_superadmin(
        string $tipo,
        string $titulo,
        string $cuerpo,
        array $datos = []
    ): int {
        $dispositivos = self::tokens_superadmin();
        $enviadas = 0;

        foreach ($dispositivos as $disp) {
            $ok = false;
            $error = null;

            try {
                if ($disp['plataforma'] === 'ios') {
                    $ok = self::enviar_apns($disp['token'], $titulo, $cuerpo, $datos);
                } elseif ($disp['plataforma'] === 'web') {
                    $ok = WebPush::enviar($disp['token'], $titulo, $cuerpo, $datos);
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
                if (self::es_token_invalido($error) || WebPush::es_subscription_expirada($error ?? '')) {
                    self::desactivar_token($disp['token']);
                }
            }

            DB::insert(
                "INSERT INTO notificaciones_push
                 (empresa_id, usuario_id, dispositivo_id, tipo, titulo, cuerpo, datos, enviada, error)
                 VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $disp['usuario_id'],
                    $disp['id'],
                    $tipo,
                    $titulo,
                    $cuerpo,
                    $datos ? json_encode($datos) : null,
                    $ok ? 1 : 0,
                    $error,
                ]
            );

            if ($ok) $enviadas++;
        }

        return $enviadas;
    }

    private static function es_token_invalido(string $error): bool
    {
        $invalidos = ['BadDeviceToken', 'Unregistered', 'DeviceTokenNotForTopic'];
        foreach ($invalidos as $razon) {
            if (str_contains($error, $razon)) return true;
        }
        return false;
    }
}
