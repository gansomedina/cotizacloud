<?php
// ============================================================
//  CotizaApp — core/WebPush.php
//  Envío de Web Push Notifications (VAPID + RFC 8291)
//  Sin dependencias externas — usa openssl y curl nativo de PHP
// ============================================================

defined('COTIZAAPP') or die;

class WebPush
{
    /**
     * Enviar notificación Web Push a una subscription
     *
     * @param string $subscriptionJson JSON con {endpoint, keys: {p256dh, auth}}
     * @param string $titulo
     * @param string $cuerpo
     * @param array  $datos  Datos extra (url, etc.)
     * @return bool
     */
    public static function enviar(
        string $subscriptionJson,
        string $titulo,
        string $cuerpo,
        array $datos = []
    ): bool {
        $sub = json_decode($subscriptionJson, true);
        if (!$sub || empty($sub['endpoint']) || empty($sub['keys']['p256dh']) || empty($sub['keys']['auth'])) {
            throw new \Exception('Subscription inválida');
        }

        $endpoint = $sub['endpoint'];
        $p256dh   = $sub['keys']['p256dh'];
        $auth     = $sub['keys']['auth'];

        // Payload JSON
        $payload = json_encode([
            'title' => $titulo,
            'body'  => $cuerpo,
            'url'   => $datos['url'] ?? '/',
            'tag'   => $datos['tag'] ?? 'cotizacloud',
        ]);

        // Encriptar payload (RFC 8291 — aes128gcm)
        $encrypted = self::encrypt($payload, $p256dh, $auth);
        if (!$encrypted) {
            throw new \Exception('Error al encriptar payload');
        }

        // Generar VAPID Authorization header
        $vapidHeaders = self::vapid_headers($endpoint);

        // Enviar via curl
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $encrypted['body'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/octet-stream',
                'Content-Encoding: aes128gcm',
                'TTL: 86400',
                'Urgency: high',
                'Authorization: vapid t=' . $vapidHeaders['token'] . ', k=' . $vapidHeaders['key'],
                'Content-Length: ' . strlen($encrypted['body']),
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception("WebPush curl error: {$curlError}");
        }

        // 201 Created = success. 410 Gone = subscription expired.
        if ($httpCode === 201 || $httpCode === 200) {
            return true;
        }

        if ($httpCode === 410 || $httpCode === 404) {
            throw new \Exception("WebPush subscription expired (HTTP {$httpCode})");
        }

        throw new \Exception("WebPush error: HTTP {$httpCode} — {$response}");
    }

    // ─── VAPID JWT + headers ────────────────────────────────
    private static function vapid_headers(string $endpoint): array
    {
        static $cache = ['token' => null, 'exp' => 0, 'aud' => ''];

        $parsed = parse_url($endpoint);
        $aud = $parsed['scheme'] . '://' . $parsed['host'];

        if ($cache['token'] && time() < $cache['exp'] && $cache['aud'] === $aud) {
            return ['token' => $cache['token'], 'key' => VAPID_PUBLIC_KEY];
        }

        $pemPath = defined('VAPID_PRIVATE_PEM') ? VAPID_PRIVATE_PEM : '';
        $subject = defined('VAPID_SUBJECT') ? VAPID_SUBJECT : 'mailto:noreply@cotiza.cloud';

        $pem = file_get_contents($pemPath);
        if (!$pem) throw new \Exception('No se pudo leer VAPID private key');

        $header = self::b64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims = self::b64url(json_encode([
            'aud' => $aud,
            'exp' => time() + 43200, // 12h
            'sub' => $subject,
        ]));

        $pkey = openssl_pkey_get_private($pem);
        if (!$pkey) throw new \Exception('VAPID key inválida');

        $sig = '';
        openssl_sign("{$header}.{$claims}", $sig, $pkey, OPENSSL_ALGO_SHA256);
        $sig = self::der_to_raw($sig);

        $token = "{$header}.{$claims}." . self::b64url($sig);

        $cache = ['token' => $token, 'exp' => time() + 3000, 'aud' => $aud];

        return ['token' => $token, 'key' => VAPID_PUBLIC_KEY];
    }

    // ─── Payload encryption (RFC 8291, aes128gcm) ───────────
    private static function encrypt(string $payload, string $p256dhB64, string $authB64): ?array
    {
        // Decode subscription keys
        $clientPub = self::b64decode($p256dhB64);  // 65 bytes (uncompressed point)
        $authSecret = self::b64decode($authB64);    // 16 bytes

        if (strlen($clientPub) !== 65 || strlen($authSecret) !== 16) {
            return null;
        }

        // Generate ephemeral ECDH key pair
        $localKey = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $localDetails = openssl_pkey_get_details($localKey);
        $localPub = chr(4) . $localDetails['ec']['x'] . $localDetails['ec']['y'];

        // ECDH shared secret
        // PHP 8.1+: use openssl_pkey_derive if available
        // For compatibility: use raw EC math via openssl
        $sharedSecret = self::ecdh_agree($localKey, $clientPub);
        if (!$sharedSecret) return null;

        // IKM (Input Keying Material) — HKDF with auth_secret as salt
        $ikm = self::hkdf($authSecret, $sharedSecret, "WebPush: info\x00" . $clientPub . $localPub, 32);

        // Salt (random 16 bytes)
        $salt = random_bytes(16);

        // CEK (Content Encryption Key) — 16 bytes
        $cek = self::hkdf($salt, $ikm, "Content-Encoding: aes128gcm\x00", 16);

        // Nonce — 12 bytes
        $nonce = self::hkdf($salt, $ikm, "Content-Encoding: nonce\x00", 12);

        // Pad payload (minimum 1 byte delimiter + optional padding)
        $paddedPayload = $payload . "\x02"; // delimiter byte

        // Encrypt with AES-128-GCM
        $tag = '';
        $encrypted = openssl_encrypt($paddedPayload, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
        if ($encrypted === false) return null;

        // Build aes128gcm content (RFC 8188)
        // Header: salt(16) + rs(4) + idlen(1) + keyid(65)
        $rs = pack('N', 4096);
        $header = $salt . $rs . chr(65) . $localPub;
        $body = $header . $encrypted . $tag;

        return ['body' => $body];
    }

    // ─── ECDH key agreement ─────────────────────────────────
    private static function ecdh_agree($localPrivKey, string $remotePubRaw): ?string
    {
        // Create a temporary PEM for the remote public key
        // Build EC public key DER
        // P-256 OID: 1.2.840.10045.3.1.7
        $ecOid = hex2bin('06082a8648ce3d030107');  // OID for prime256v1
        $algId = hex2bin('3013') . hex2bin('06072a8648ce3d0201') . $ecOid; // AlgorithmIdentifier
        $bitString = chr(0x00) . $remotePubRaw; // bit string content (0 unused bits)
        $bitStringDer = chr(0x03) . self::asn1_length(strlen($bitString)) . $bitString;
        $spki = chr(0x30) . self::asn1_length(strlen($algId) + strlen($bitStringDer)) . $algId . $bitStringDer;

        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $remotePubKey = openssl_pkey_get_public($pem);
        if (!$remotePubKey) return null;

        // Use openssl_pkey_derive (PHP 7.3+)
        if (function_exists('openssl_pkey_derive')) {
            $shared = openssl_pkey_derive($remotePubKey, $localPrivKey, 256);
            return $shared !== false ? $shared : null;
        }

        return null;
    }

    // ─── ASN.1 length encoding ──────────────────────────────
    private static function asn1_length(int $len): string
    {
        if ($len < 128) return chr($len);
        if ($len < 256) return chr(0x81) . chr($len);
        return chr(0x82) . pack('n', $len);
    }

    // ─── HKDF (RFC 5869) ────────────────────────────────────
    private static function hkdf(string $salt, string $ikm, string $info, int $length): string
    {
        // Extract
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        // Expand
        $t = '';
        $lastBlock = '';
        $counter = 1;
        while (strlen($t) < $length) {
            $lastBlock = hash_hmac('sha256', $lastBlock . $info . chr($counter), $prk, true);
            $t .= $lastBlock;
            $counter++;
        }
        return substr($t, 0, $length);
    }

    // ─── Helpers ────────────────────────────────────────────
    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64decode(string $b64url): string
    {
        return base64_decode(strtr($b64url, '-_', '+/') . str_repeat('=', (4 - strlen($b64url) % 4) % 4));
    }

    private static function der_to_raw(string $der): string
    {
        $hex = unpack('H*', $der)[1];
        $pos = 4;
        $pos += 2;
        $rLen = hexdec(substr($hex, $pos, 2));
        $pos += 2;
        $r = substr($hex, $pos, $rLen * 2);
        $pos += $rLen * 2;
        $pos += 2;
        $sLen = hexdec(substr($hex, $pos, 2));
        $pos += 2;
        $s = substr($hex, $pos, $sLen * 2);
        $r = str_pad(substr($r, -64), 64, '0', STR_PAD_LEFT);
        $s = str_pad(substr($s, -64), 64, '0', STR_PAD_LEFT);
        return pack('H*', $r . $s);
    }

    /**
     * Verificar si un error indica subscription expirada
     */
    public static function es_subscription_expirada(string $error): bool
    {
        return str_contains($error, '410') || str_contains($error, '404') || str_contains($error, 'expired');
    }
}
