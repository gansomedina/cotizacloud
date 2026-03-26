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
            "SELECT pixel_meta, pixel_ga4, pixel_gads_id, pixel_gads_label, pixel_tiktok
             FROM marketing_config WHERE empresa_id = ?",
            [$empresa_id]
        ) ?: [];

        return self::$config;
    }

    /**
     * Generar scripts base de pixels (para inyectar en <head> o inicio de <body>)
     * Incluye PageView automático
     */
    public static function scripts_base(int $empresa_id): string
    {
        $cfg = self::cargar($empresa_id);
        if (empty($cfg)) return '';

        $html = "\n<!-- Marketing Pixels -->\n";

        // Meta Pixel
        $meta = $cfg['pixel_meta'] ?? '';
        if ($meta && preg_match('/^\d{15,16}$/', $meta)) {
            $html .= "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$meta}');fbq('track','PageView');</script>\n";
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
    public static function evento_view(int $empresa_id, string $numero, float $total, string $moneda = 'MXN'): string
    {
        $cfg = self::cargar($empresa_id);
        if (empty($cfg)) return '';

        $numero_js = htmlspecialchars($numero, ENT_QUOTES, 'UTF-8');
        $js = '';

        if (!empty($cfg['pixel_meta'])) {
            $js .= "if(typeof fbq!=='undefined')fbq('track','ViewContent',{content_name:'Cotización {$numero_js}',value:{$total},currency:'{$moneda}'});\n";
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
}
