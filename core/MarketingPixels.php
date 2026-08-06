<?php
// ============================================================
//  CotizaApp — core/MarketingPixels.php
//  Inyección de pixels de marketing en vistas públicas
//  Solo IDs validados → templates fijos (prevención XSS)
// ============================================================

defined('COTIZAAPP') or die;

class MarketingPixels
{
    private static ?array $config = null;

    /**
     * Cargar config de marketing para la empresa actual
     */
    public static function cargar(int $empresa_id): array
    {
        if (self::$config !== null) return self::$config;

        self::$config = DB::row(
            "SELECT pixel_meta, capi_token, pixel_ga4, pixel_gads_id, pixel_gads_label, pixel_tiktok
             FROM marketing_config WHERE empresa_id = ?",
            [$empresa_id]
        ) ?: [];

        return self::$config;
    }

    /**
     * Generar scripts base de pixels (para inyectar en <head> o inicio de <body>)
     * Incluye PageView automático
     */
    public static function scripts_base(int $empresa_id, ?array $am = null): string
    {
        $cfg = self::cargar($empresa_id);
        if (empty($cfg)) return '';

        $html = "\n<!-- Marketing Pixels -->\n";

        // Meta Pixel
        $meta = $cfg['pixel_meta'] ?? '';
        if ($meta && preg_match('/^\d{15,16}$/', $meta)) {
            // Advanced Matching (opt-in): datos del cliente EN CLARO normalizado;
            // la librería fbevents.js los hashea sola. NO hashear aquí (si hasheas
            // tú y la librería re-hashea, el match falla). El hash manual es solo
            // para el CAPI. json_encode → seguro para el contexto JS.
            $am_js = ($am && is_array($am) && $am) ? ',' . json_encode($am, JSON_UNESCAPED_UNICODE) : '';
            $html .= "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$meta}'{$am_js});fbq('track','PageView');</script>\n";
            $html .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.net/tr?id={$meta}&ev=PageView&noscript=1\"/></noscript>\n";
        }

        // GA4
        $ga4 = $cfg['pixel_ga4'] ?? '';
        if ($ga4 && preg_match('/^G-[A-Za-z0-9]{10,12}$/', $ga4)) {
            $html .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$ga4}\"></script>\n";
            $html .= "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$ga4}');</script>\n";
        }

        // Google Ads (usa gtag si GA4 ya lo cargó)
        $gads = $cfg['pixel_gads_id'] ?? '';
        if ($gads && preg_match('/^AW-\d{9,11}$/', $gads)) {
            if (!$ga4) {
                // Si no hay GA4, cargar gtag.js con Google Ads
                $html .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$gads}\"></script>\n";
                $html .= "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());</script>\n";
            }
            $html .= "<script>gtag('config','{$gads}');</script>\n";
        }

        // TikTok Pixel
        $ttk = $cfg['pixel_tiktok'] ?? '';
        if ($ttk && preg_match('/^C[A-Za-z0-9]{10,25}$/', $ttk)) {
            $html .= "<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie','holdConsent','revokeConsent','grantConsent'],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r='https://analytics.tiktok.com/i18n/pixel/events.js',o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var a=document.createElement('script');a.type='text/javascript',a.async=!0,a.src=r+'?sdkid='+e+'&lib='+t;var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(a,s)};ttq.load('{$ttk}');ttq.page()}(window,document,'ttq');</script>\n";
        }

        return $html;
    }

    /**
     * Generar JS para disparar evento ViewContent
     */
    public static function evento_view(int $empresa_id, string $numero, float $total, string $moneda = 'MXN', ?string $event_id = null): string
    {
        $cfg = self::cargar($empresa_id);
        if (empty($cfg)) return '';

        $numero_js = htmlspecialchars($numero, ENT_QUOTES, 'UTF-8');
        // event_id de dedup (mismo que el CAPI). Solo hex → seguro en JS.
        $eid_js = ($event_id && preg_match('/^[a-f0-9]{16,64}$/', $event_id)) ? ",{eventID:'{$event_id}'}" : '';
        $js = '';

        if (!empty($cfg['pixel_meta'])) {
            $js .= "if(typeof fbq!=='undefined')fbq('track','ViewContent',{content_name:'Cotización {$numero_js}',value:{$total},currency:'{$moneda}'}{$eid_js});\n";
        }
        if (!empty($cfg['pixel_ga4'])) {
            $js .= "if(typeof gtag!=='undefined')gtag('event','view_item',{currency:'{$moneda}',value:{$total},items:[{item_name:'Cotización {$numero_js}'}]});\n";
        }
        if (!empty($cfg['pixel_tiktok'])) {
            $js .= "if(typeof ttq!=='undefined')ttq.track('ViewContent',{content_name:'Cotización {$numero_js}',value:{$total},currency:'{$moneda}'});\n";
        }

        return $js ? "<script>{$js}</script>\n" : '';
    }

    /**
     * Generar JS para disparar evento de aceptación (Lead/Conversion)
     * Se usa inline en el callback de éxito del JS
     */
    public static function evento_aceptar_js(int $empresa_id): string
    {
        $cfg = self::cargar($empresa_id);
        if (empty($cfg)) return '';

        $js = '';

        if (!empty($cfg['pixel_meta'])) {
            $js .= "if(typeof fbq!=='undefined')fbq('track','Lead',{value:totalFinal,currency:MONEDA});";
        }
        if (!empty($cfg['pixel_ga4'])) {
            $js .= "if(typeof gtag!=='undefined')gtag('event','generate_lead',{value:totalFinal,currency:MONEDA});";
        }
        $gads = $cfg['pixel_gads_id'] ?? '';
        $label = $cfg['pixel_gads_label'] ?? '';
        if ($gads && $label) {
            $js .= "if(typeof gtag!=='undefined')gtag('event','conversion',{send_to:'{$gads}/{$label}',value:totalFinal,currency:MONEDA});";
        }
        if (!empty($cfg['pixel_tiktok'])) {
            $js .= "if(typeof ttq!=='undefined')ttq.track('SubmitForm',{value:totalFinal,currency:MONEDA});";
        }

        return $js;
    }

    /**
     * Generar JS para disparar evento de rechazo (custom)
     */
    public static function evento_rechazar_js(int $empresa_id): string
    {
        $cfg = self::cargar($empresa_id);
        if (empty($cfg)) return '';

        $js = '';

        if (!empty($cfg['pixel_meta'])) {
            $js .= "if(typeof fbq!=='undefined')fbq('trackCustom','QuoteRejected');";
        }
        if (!empty($cfg['pixel_ga4'])) {
            $js .= "if(typeof gtag!=='undefined')gtag('event','quote_rejected');";
        }

        return $js;
    }

    // ─── Conversions API (Server-Side) ──────────────────────

    /**
     * Enviar evento server-side a Meta Conversions API
     * Se ejecuta en background (no bloquea el response)
     */
    public static function capi_enviar(int $empresa_id, string $event_name, array $event_data = [], ?string $event_id = null, array $user_data_extra = []): void
    {
        $cfg = self::cargar($empresa_id);
        $pixel = $cfg['pixel_meta'] ?? '';
        $token = $cfg['capi_token'] ?? '';

        if (!$pixel || !$token) return;

        // Validar pixel y token antes de usarlos
        if (!preg_match('/^\d{15,16}$/', $pixel)) return;
        if (!preg_match('/^[A-Za-z0-9_-]{50,300}$/', $token)) return;

        $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua   = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $url  = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
        $fbp  = $_COOKIE['_fbp'] ?? null;
        $fbc  = $_COOKIE['_fbc'] ?? null;

        $user_data = [
            'client_ip_address'  => $ip,
            'client_user_agent'  => $ua,
        ];
        if ($fbp) $user_data['fbp'] = $fbp;
        if ($fbc) $user_data['fbc'] = $fbc;
        // PII de Advanced Matching, YA hasheada (SHA-256) — solo si opt-in ON.
        if ($user_data_extra) $user_data = array_merge($user_data, $user_data_extra);

        $event = [
            'event_name'  => $event_name,
            'event_time'  => time(),
            'event_source_url' => $url,
            'action_source'    => 'website',
            'user_data'        => $user_data,
        ];

        // Dedup con el navegador: mismo event_id en ambos lados → Meta los cuenta
        // como UN evento cubierto (no doble). Sube la "cobertura de eventos".
        if ($event_id) $event['event_id'] = $event_id;

        if (!empty($event_data)) {
            $event['custom_data'] = $event_data;
        }

        $payload = json_encode(['data' => [$event]]);

        $api_url = "https://graph.facebook.com/v21.0/{$pixel}/events?access_token=" . urlencode($token);

        // Enviar con cURL async (timeout corto para no bloquear)
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $api_url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log de errores (solo en debug)
        if ($http_code !== 200 && defined('DEBUG') && DEBUG) {
            error_log("CAPI Error [{$http_code}] empresa={$empresa_id} event={$event_name}: {$response}");
        }
    }

    /**
     * Enviar evento ViewContent via CAPI
     */
    public static function capi_view(int $empresa_id, string $numero, float $total, string $moneda = 'MXN', ?string $event_id = null, array $am_hashed = []): void
    {
        self::capi_enviar($empresa_id, 'ViewContent', [
            'content_name' => "Cotización {$numero}",
            'value'        => $total,
            'currency'     => $moneda,
        ], $event_id, $am_hashed);
    }

    // ─── Advanced Matching (Fase 2 — opt-in por empresa) ────────────────────

    /** ¿La empresa activó el envío de datos del cliente a Meta? Guardado:
     *  columna sin migrar → false (nunca revienta el pixel). */
    public static function advanced_matching_on(int $empresa_id): bool
    {
        try {
            return (int) DB::val(
                "SELECT advanced_matching_optin FROM marketing_config WHERE empresa_id = ?",
                [$empresa_id]
            ) === 1;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Normalización de texto (nombre/apellido): minúsculas, sin acentos, solo
     *  letras. Igual en navegador y CAPI; el hash solo se aplica en CAPI. */
    private static function norm_text(string $s): string
    {
        $s = strtolower(trim($s));
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false) $s = $t;
        return preg_replace('/[^a-z]/', '', $s);
    }

    /** Normalización de teléfono a E.164 sin '+': dígitos con código de país.
     *  MX-céntrico (la base es MX): 10 díg → 52 + díg; 521… → 52…; EU/frontera
     *  (1+10) se deja. */
    private static function norm_phone(string $tel): string
    {
        $d = preg_replace('/\D/', '', $tel);
        if (strlen($d) === 10) return '52' . $d;
        if (strlen($d) === 13 && substr($d, 0, 3) === '521') return '52' . substr($d, 3);
        if (strlen($d) === 12 && substr($d, 0, 2) === '52')  return $d;
        if (strlen($d) === 11 && substr($d, 0, 1) === '1')   return $d;
        return $d;
    }

    /** Advanced Matching para el NAVEGADOR (valores EN CLARO normalizados; la
     *  librería fbevents los hashea). Sin email (casi nunca se llena) ni ciudad
     *  (la de la empresa no es la del cliente → dato incorrecto). */
    public static function am_browser(string $nombre, string $tel, int $cliente_id): array
    {
        $partes = preg_split('/\s+/', trim($nombre), 2);
        return array_filter([
            'ph'          => self::norm_phone($tel),
            'fn'          => self::norm_text($partes[0] ?? ''),
            'ln'          => self::norm_text($partes[1] ?? ''),
            'external_id' => $cliente_id > 0 ? (string)$cliente_id : '',
        ]);
    }

    /** Advanced Matching para el CAPI: los MISMOS campos, ya hasheados SHA-256
     *  (Meta los quiere hasheados server-side; en el navegador van en claro). */
    public static function am_capi_hashed(string $nombre, string $tel, int $cliente_id): array
    {
        $out = [];
        foreach (self::am_browser($nombre, $tel, $cliente_id) as $k => $v) {
            if ($v !== '') $out[$k] = hash('sha256', $v);
        }
        return $out;
    }

    /**
     * Enviar evento Lead (aceptación) via CAPI
     */
    public static function capi_lead(int $empresa_id, float $total, string $moneda = 'MXN'): void
    {
        self::capi_enviar($empresa_id, 'Lead', [
            'value'    => $total,
            'currency' => $moneda,
        ]);
    }

    /**
     * Enviar evento custom QuoteRejected via CAPI
     */
    public static function capi_rechazar(int $empresa_id): void
    {
        self::capi_enviar($empresa_id, 'QuoteRejected');
    }
}
