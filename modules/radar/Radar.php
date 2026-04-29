<?php
// ============================================================
//  CotizaApp — modules/radar/Radar.php  v3.0
//  Motor de scoring e intención — 17 buckets × 3 modos
//  v3.0: FIT scoring v3 — power-scaled Naive Bayes + isotonic PAV + decididos.
//  v2.4: Momentum — indicador visual de frescura por bucket.
//        Vigencia estable = mitad de la ventana _recent_hours del bucket.
//        Si última actividad excede vigencia → momentum='cooling' (↓ en UI).
//        No modifica buckets, score ni FIT. Capa puramente informativa.
//  v2.1: Ajustes alto ticket — prioridades, FIT, multi-persona, no_abierta
//  v2.2: Universalidad — alto_importe dinámico (P80), vigencia real, multi-persona balance
//  v2.3: Robustez estadística + ventas consultivas:
//        - FIT: Laplace smoothing (α=5) + cap multiplicadores [0.3, 3.0] + cache
//        - Calibración gap: últimas 2 sesiones (no span total)
//        - Auto-calibración proporcional (20% delta) + decay 90d
//        - Probable cierre: requiere señal de calidad + piso FIT ≥5% o sess ≥3
//        - Predicción alta: ventana alive proporcional a predict_recent_days
//        - Re-enganche caliente: nuevo bucket (regresó + interacción precio)
//        - Sobre-análisis: umbrales diferenciados por modo
//        - Comparando: gate de engagement JS (anti-bot)
//        - Enfriándose: requiere engagement previo (no clasifica "perdidos")
//        - P80 alto_importe cacheado + multip_boost parametrizado
//        - Bandas calibración: prepared statements completos
//        - SQL injection fix en lista_activas
//  Portado fielmente de radar_3_.php (On Time / WordPress)
//  Adaptado a PDO sin WordPress, multitenant por empresa_id
// ============================================================

defined('COTIZAAPP') or die;

class Radar
{
    // ─── Cache en memoria por request ───────────────────────
    private static array $config_cache = [];
    private static array $p80_cache = [];
    private static array $fit_cal_cache = [];
    private static array $ciclo_cache = [];
    private static array $engage_avg_cache = [];

    // ============================================================
    //  UMBRALES × 3 MODOS
    //  Cada constante tiene [agresivo, medio, ligero]
    //  "medio" = valores exactos del radar original de On Time
    //  "agresivo" = umbrales más permisivos (más cotizaciones clasificadas)
    //  "ligero"   = umbrales más exigentes  (solo señales sólidas)
    // ============================================================
    const U = [

        // ── Deduplicación de vistas (ventana de sesión) ──────
        'dedupe_seconds'             => [1200,  1800,  3600 ],

        // ── Bucket 2: Inminente ──────────────────────────────
        'imminent_recent_hours'      => [48,    36,    24   ],
        'imminent_min_fit_pct'       => [4.0,   5.0,   7.0  ],
        'imminent_min_age_hours'     => [2.0,   3.0,   6.0  ],
        'imminent_min_guest'         => [1,     1,     2    ],
        'imminent_ip_window_min'     => [240,   180,   120  ],
        'imminent_signal_views24'    => [1,     2,     3    ],
        'imminent_signal_ips_120m'   => [1,     2,     3    ],
        'imminent_signal_closes'     => [1,     1,     2    ],
        'imminent_signal_scroll_pct' => [70,    90,    90   ],
        'imminent_signal_vis_max'    => [8000,  15000, 20000],
        'imminent_signal_vis_sum'    => [10000, 18000, 25000],
        'imminent_signal_views48'    => [2,     3,     4    ],
        'imminent_signal_span_h'     => [3,     6,     8    ],
        'imminent_signal_coupon'     => [1,     1,     1    ],
        'imminent_min_signals'       => [1,     1,     2    ],
        'imminent_min_strong'        => [1,     1,     2    ],
        'imminent_min_span48_h'      => [0.5,   1.0,   2.0  ],  // regreso real: distancia mínima entre sesiones en 48h

        // ── Bucket 3: On Fire ────────────────────────────────
        'onfire_recent_hours'        => [96,    72,    48   ],
        'onfire_min_sessions'        => [2,     2,     3    ],
        'onfire_min_scroll_pct'      => [70,    90,    90   ],
        'onfire_min_vis_sum'         => [20000, 30000, 40000],
        'onfire_min_vis_max'         => [15000, 22000, 30000],
        'onfire_min_gap_days'        => [1,     1,     1    ],
        'onfire_min_views48'         => [2,     3,     4    ],
        'onfire_min_span_h'          => [4,     6,     8    ],

        // ── Bucket 4: Validando precio ───────────────────────
        'priceval_recent_hours'      => [96,    72,    48   ],
        'priceval_vis_soft'          => [2000,  4000,  6000 ],
        'priceval_vis_hard'          => [5000,  8000,  12000],
        'priceval_vis_sum'           => [10000, 14000, 20000],
        'priceval_scroll_soft'       => [40,    50,    70   ],

        // ── Bucket 5: Predicción alta ────────────────────────
        'predict_min_fit_pct'        => [10.0,  14.0,  18.0 ],

        // ── Bucket 6: Decisión activa ────────────────────────
        'decision_window_h'          => [72,    48,    48   ],
        'decision_min_views48'       => [2,     4,     5    ],
        'decision_min_span_h'        => [3,     6,     8    ],

        // ── Bucket 7: Re-enganche ────────────────────────────
        'reeng_gap_days'             => [2,     4,     6    ],
        'reeng_recent_hours'         => [240,   168,   120  ],
        'reeng_min_guest_24h'        => [1,     1,     1    ],
        'reeng_min_views24'          => [1,     1,     2    ],

        // ── Bucket 8: Multi-persona ──────────────────────────
        'multip_recent_hours'        => [96,    72,    48   ],
        'multip_ip_window_min'       => [720,   480,   360  ],
        'multip_min_ips_post_guest'  => [2,     3,     4    ],
        'multip_min_guest_total'     => [1,     2,     3    ],

        // ── Bucket 9: Revisión profunda ──────────────────────
        'deep_recent_hours'          => [96,    72,    48   ],
        'deep_min_views48'           => [2,     3,     4    ],
        'deep_min_span_h'            => [2,     3,     4    ],
        'deep_min_guest_48h'         => [1,     1,     2    ],
        'deep_min_vis_max'           => [8000,  10000, 15000],
        'deep_min_vis_sum'           => [14000, 18000, 25000],

        // ── Bucket 10: Hesitación ────────────────────────────
        'hes_min_guest_7d'           => [1,     2,     3    ],
        'hes_last_min_hours'         => [24,    36,    48   ],
        'hes_last_max_days'          => [14,    10,    7    ],
        'hes_max_ips_total'          => [3,     2,     2    ],
        'hes_max_span_h'             => [8,     6,     4    ],

        // ── Bucket 11: Sobre-análisis ────────────────────────
        'over_min_sessions'          => [12,    20,    28   ],
        'over_min_guest'             => [5,     8,     12   ],
        'over_min_age_days'          => [5,     7,     10   ],
        'over_recent_days'           => [30,    21,    14   ],
        'over_max_fit_pct'           => [18.0,  14.0,  10.0 ],
        'over_max_ips_post_guest'    => [5,     4,     3    ],

        // ── Bucket 12: Regreso ───────────────────────────────
        'return_gap_days'            => [2,     4,     6    ],
        'return_recent_hours'        => [72,    48,    36   ],

        // ── Bucket 13: Revivió ───────────────────────────────
        'revive_gap_days'            => [15,    30,    45   ],
        'revive_recent_hours'        => [72,    48,    36   ],

        // ── Bucket 14: Comparando ────────────────────────────
        'compare_min_ips'            => [2,     2,     3    ],
        'compare_window_h'           => [36,    24,    24   ],

        // ── Bucket 15: Enfriándose ───────────────────────────
        'cooling_min_sessions'       => [3,     4,     5    ],
        'cooling_days'               => [10,    7,     5    ],
        'cooling_min_silence_h'      => [60,    48,    36   ],

        // ── Bucket alto importe ──────────────────────────────
        'high_amount_threshold'      => [80000, 120000, 160000],
        'high_amount_recent_hours'   => [72,    48,    36   ],

        // ── Bucket lectura comprometida ──────────────────────
        'engage_recent_hours'        => [168,   144,   120  ],
        'engage_max_sessions'        => [4,     3,     3    ],
        'engage_min_vis_ms'          => [15000, 15000, 20000],

        // ── Bucket vistas múltiples ──────────────────────────
        'multi_min_ips'              => [2,     2,     3    ],
        'multi_min_views24'          => [2,     3,     4    ],
        'multi_recent_hours'         => [36,    24,    24   ],
    ];

    // ============================================================
    //  MODELO FIT — Tasas base neutras (fallback antes de calibrar)
    //  Se reemplazan con datos reales de cada empresa en cuanto hay 3 ventas.
    //  FIT_GLOBAL: tasa de cierre promedio industria servicios ~10%
    //  Las tasas por bucket son relativas entre sí — más sesiones = más interés.
    //  Se expresan como multiplicadores del global, no como números absolutos.
    // ============================================================
    const FIT_GLOBAL  = 0.10;   // 10% — promedio neutro servicios B2B/B2C
    const FIT_MIN     = 0.005;  // 0.5% mínimo absoluto
    const FIT_MAX     = 0.250;  // 25% máximo absoluto (v3: ajustado por power-scaled NB)

    // Tasas relativas neutras: más sesiones/IPs = mayor probabilidad de cierre
    // Se calibran con datos reales de la empresa en cuanto hay suficientes ventas
    // v2.1: curva más plana en rangos altos — en alto ticket más sesiones = due diligence, no sobre-análisis
    const FIT_RATE_SESS = [
        '1'   => 0.06,   // 1 sesión — vio y se fue
        '2'   => 0.10,   // 2 sesiones — volvió a revisar
        '3-4' => 0.13,   // 3-4 sesiones — considerando seriamente
        '5-7' => 0.16,   // 5-7 sesiones — muy interesado (pico)
        '8-12'=> 0.155,  // 8-12 sesiones — comprador meticuloso, caída suave
        '13+' => 0.13,   // 13+ sesiones — aún buena señal en alto ticket
    ];
    // v2.1: 4+ IPs = comité de compra activo en alto ticket (esposo+esposa+arquitecto+familiar)
    const FIT_RATE_IPS = [
        '1'  => 0.07,    // 1 IP — solo el contacto principal
        '2'  => 0.14,    // 2 IPs — lo compartió con alguien (pareja, socio)
        '3'  => 0.11,    // 3 IPs — varios involucrados
        '4+' => 0.12,    // 4+ IPs — comité de compra, proceso de aprobación
    ];
    const FIT_RATE_GAP = [
        'sin'  => 0.08,  // Sin gap — vio una sola vez
        '1-3d' => 0.12,  // Revisó en los primeros días
        '4+d'  => 0.09,  // Gap largo — puede estar comparando o dudando
    ];

    // ─── UA y prefijos de bots (copiados del mu-plugin) ─────
    const BOT_UA = [
        'bot','spider','crawler','scan',
        'curl','wget','python-requests','httpclient','go-http-client','java/',
        'headless','lighthouse','pagespeed',
        'googlebot','bingbot','yandex','duckduckbot','baiduspider',
        'facebookexternalhit','meta-externalagent',
        'whatsapp','slackbot','telegrambot',
        'prerender','embedly','opengraph','iframely',
        'pingdom','uptimerobot','newrelic','datadog',
        'selenium','puppeteer','playwright','phantomjs',
    ];
    const BOT_IP = [
        '66.249.',                                          // Google
        '40.77.','52.167.','157.55.','207.46.',             // Microsoft/Bing
        '31.13.','66.220.','173.252.','69.171.','57.141.', // Facebook/Meta
        '104.28.',                                          // Cloudflare
        '154.12.','185.191.','85.208.',                     // Yandex
        '54.39.','15.235.','167.114.',                      // OVH
        '51.161.','51.222.','142.44.','148.113.',           // Hetzner
    ];

    // ============================================================
    //  HELPERS INTERNOS
    // ============================================================

    /** Devuelve el umbral para el modo dado. */
    private static function u(string $key, string $modo): int|float
    {
        $i = match ($modo) { 'agresivo' => 0, 'ligero' => 2, default => 1 };
        return self::U[$key][$i];
    }

    private static function bot_ua(string $ua): bool
    {
        $ua = strtolower(trim($ua));
        if (strlen($ua) < 10) return true;
        foreach (self::BOT_UA as $b) {
            if (str_contains($ua, $b)) return true;
        }
        return false;
    }

    private static function bot_ip(string $ip): bool
    {
        foreach (self::BOT_IP as $p) {
            if (str_starts_with($ip, $p)) return true;
        }
        return false;
    }

    private static function bk_sess(int $n): string
    {
        if ($n <= 1) return '1';
        if ($n === 2) return '2';
        if ($n <= 4) return '3-4';
        if ($n <= 7) return '5-7';
        if ($n <= 12) return '8-12';
        return '13+';
    }

    private static function bk_ips(int $n): string
    {
        if ($n <= 1) return '1';
        if ($n === 2) return '2';
        if ($n === 3) return '3';
        return '4+';
    }

    private static function bk_gap(?int $g): string
    {
        if ($g === null || $g <= 0) return 'sin';
        if ($g <= 3) return '1-3d';
        return '4+d';
    }

    // ============================================================
    //  FIT — Probabilidad de cierre basada en comportamiento
    //  Portado fielmente de compute_fit_prob() del radar original
    // ============================================================
    public static function fit_prob(int $sessions, int $uniq_ips, ?int $gap_days, int $empresa_id): float
    {
        // Cache por request: evita N+1 queries en recalcular_empresa()
        if (!isset(self::$fit_cal_cache[$empresa_id])) {
            self::$fit_cal_cache[$empresa_id] = DB::row(
                "SELECT global_rate, rate_sess_json, rate_ips_json, rate_gap_json
                 FROM radar_fit_calibracion WHERE empresa_id=? AND activa=1
                 ORDER BY created_at DESC LIMIT 1",
                [$empresa_id]
            );
        }
        $cal = self::$fit_cal_cache[$empresa_id];

        $global = $cal ? (float)($cal['global_rate'] ?? self::FIT_GLOBAL) : self::FIT_GLOBAL;
        $rs_map = ($cal && $cal['rate_sess_json']) ? (json_decode($cal['rate_sess_json'], true) ?? self::FIT_RATE_SESS) : self::FIT_RATE_SESS;
        $ri_map = ($cal && $cal['rate_ips_json'])  ? (json_decode($cal['rate_ips_json'],  true) ?? self::FIT_RATE_IPS)  : self::FIT_RATE_IPS;
        $rg_map = ($cal && $cal['rate_gap_json'])  ? (json_decode($cal['rate_gap_json'],  true) ?? self::FIT_RATE_GAP)  : self::FIT_RATE_GAP;

        $rs = $rs_map[self::bk_sess($sessions)]   ?? $global;
        $ri = $ri_map[self::bk_ips($uniq_ips)]    ?? $global;
        $rg = $rg_map[self::bk_gap($gap_days)]    ?? $global;

        // v3: Power-scaled Naive Bayes — corrige correlación sesiones/IPs
        // Exponentes suman 1.0 (no 3.0) → dampea inflación multiplicativa
        $gr = max(0.001, $global);
        $lift_s = max(0.1, $rs / $gr);
        $lift_i = max(0.1, $ri / $gr);
        $lift_g = max(0.1, $rg / $gr);

        $fit = $gr * pow($lift_s, 0.50) * pow($lift_i, 0.30) * pow($lift_g, 0.20);
        return max(self::FIT_MIN, min(self::FIT_MAX, $fit));
    }

    // ============================================================
    //  SCORE + CLASIFICACIÓN EN 15 BUCKETS
    //  Portado línea a línea del loop principal de radar_3_.php
    // ============================================================

    /**
     * @return array{
     *   score: int, fit_pct: float, priority_pct: float,
     *   bucket: string|null, buckets: string[], senales: array, debug: array
     * }
     */
    public static function score(int $cotizacion_id, int $empresa_id): array
    {
        $cfg  = self::config($empresa_id);
        $modo = $cfg['sensibilidad'] ?? 'medio';
        $now  = time();

        // ── A. Cargar eventos JS ─────────────────────────────
        $lookback = $now - 150 * 86400;
        try {
            $ev_rows = DB::query(
                "SELECT tipo, ts_unix, max_scroll, visible_ms, open_ms,
                        visitor_id, device_sig, session_id, page_id, ip, ua
                 FROM quote_events
                 WHERE cotizacion_id=? AND ts_unix >= ?
                 ORDER BY id ASC",
                [$cotizacion_id, $lookback]
            );
        } catch (Throwable $e) {
            $ev_rows = DB::query(
                "SELECT tipo, ts_unix, max_scroll, visible_ms, open_ms,
                        visitor_id, NULL AS device_sig, session_id, page_id, ip, ua
                 FROM quote_events
                 WHERE cotizacion_id=? AND ts_unix >= ?
                 ORDER BY id ASC",
                [$cotizacion_id, $lookback]
            );
        }

        // ── B. Internos ──────────────────────────────────────
        $intern_v = [];
        $intern_ip = [];
        $intern_dsig = [];
        if ($cfg['excluir_internos'] ?? true) {
            foreach (DB::query("SELECT visitor_id FROM radar_visitors_internos WHERE empresa_id=?", [$empresa_id]) as $r) {
                $intern_v[$r['visitor_id']] = true;
            }
            foreach (DB::query("SELECT ip FROM radar_ips_internas WHERE empresa_id=? AND aprendida_ts >= ?", [$empresa_id, time() - 7 * 86400]) as $r) {
                $intern_ip[$r['ip']] = true;
            }
            try {
                foreach (DB::query(
                    "SELECT DISTINCT us.device_sig FROM user_sessions us
                     JOIN usuarios u ON u.id = us.usuario_id
                     WHERE (u.empresa_id = ? OR u.rol = 'superadmin')
                       AND us.device_sig IS NOT NULL AND us.device_sig != ''
                       AND us.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
                    [$empresa_id]
                ) as $r) {
                    $intern_dsig[$r['device_sig']] = true;
                }
            } catch (Throwable $e) {}
        }

        // ── C. Agregar eventos JS (misma lógica que event_stats_by_quote) ──
        $es = self::_agregar_eventos($ev_rows, $intern_v, $intern_ip, $intern_dsig, $cfg);

        // ── D. Cargar sesiones históricas (mismo lookback que eventos: 150d) ──
        try {
            $sess_rows = DB::query(
                "SELECT ip, user_agent AS ua, visitor_id, device_sig, created_at, scroll_max, visible_ms
                 FROM quote_sessions WHERE cotizacion_id=? AND created_at >= FROM_UNIXTIME(?) ORDER BY created_at ASC",
                [$cotizacion_id, $lookback]
            );
        } catch (Throwable $e) {
            $sess_rows = DB::query(
                "SELECT ip, user_agent AS ua, visitor_id, NULL AS device_sig, created_at, scroll_max, visible_ms
                 FROM quote_sessions WHERE cotizacion_id=? AND created_at >= FROM_UNIXTIME(?) ORDER BY created_at ASC",
                [$cotizacion_id, $lookback]
            );
        }

        // ── E. Procesar sesiones (deduplicar, filtrar, contar) ─
        $dedupe = (int) self::u('dedupe_seconds', $modo);
        $multip_win = (int) self::u('multip_ip_window_min', $modo) * 60;
        $compare_win = (int) self::u('compare_window_h', $modo) * 3600;
        $ip_win = (int) self::u('imminent_ip_window_min', $modo) * 60;

        $last_by_ip    = [];
        $last_by_vid   = [];
        $session_ts    = [];
        $vid_dsig      = [];  // vid → dsig (para descarte)
        $ip_dsig       = [];  // ip → [dsig => true, ...] (para descarte, array por IP)
        $sessions = $views24 = $views7d = $views48 = 0;
        $guest_sessions = $guest_24h = $guest_48h = $guest_7d = 0;
        $first_guest_ts = 0;
        $last_ts = 0;
        $compare_ips = $ips_120m = [];

        // Para multip: IPs en ventana post primer guest
        $ips_post_guest = [];

        foreach ($sess_rows as $s) {
            $ip   = trim((string)($s['ip'] ?? ''));
            $ua   = (string)($s['ua'] ?? '');
            $ts   = strtotime($s['created_at']);
            $vid  = trim((string)($s['visitor_id'] ?? ''));
            $dsig = trim((string)($s['device_sig'] ?? ''));

            if ($ip === '') continue;
            if (($cfg['filtrar_bots'] ?? true) && (self::bot_ip($ip) || self::bot_ua($ua))) continue;
            if (($cfg['excluir_internos'] ?? true) && (
                isset($intern_ip[$ip]) ||
                ($vid !== '' && isset($intern_v[$vid])) ||
                ($dsig !== '' && isset($intern_dsig[$dsig]))
            )) continue;

            // ── Filtro behavioral: sesión fantasma de bot de preview ──
            // Si scroll=0, visible=0, sesión tiene >2 min de vida,
            // y no hay eventos JS desde esa IP → es un bot que hizo fetch
            // pero no ejecutó JavaScript (WhatsApp, Teams, iMessage, etc.)
            $scroll = (int)($s['scroll_max'] ?? 0);
            $vis    = (int)($s['visible_ms'] ?? 0);
            if (($cfg['filtrar_bots'] ?? true) && $scroll === 0 && $vis === 0 && ($now - $ts) > 120) {
                // Verificar si hay al menos 1 evento JS desde esta IP
                $ip_has_events = false;
                foreach ($ev_rows as $ev) {
                    if (($ev['ip'] ?? '') === $ip) { $ip_has_events = true; break; }
                }
                if (!$ip_has_events) continue;
            }

            // Construir mapas vid→dsig e ip→dsig (después de filtros, antes de dedup)
            if ($dsig !== '') {
                if ($vid !== '') $vid_dsig[$vid] = $dsig;
                $ip_dsig[$ip][$dsig] = true;
            }

            // Deduplicar: visitor_id primero (misma persona), IP como fallback
            if ($vid !== '' && isset($last_by_vid[$vid]) && ($ts - $last_by_vid[$vid]) < $dedupe) {
                $last_by_vid[$vid] = $ts;
                continue;
            }
            if (isset($last_by_ip[$ip]) && ($ts - $last_by_ip[$ip]) < $dedupe) {
                if ($vid !== '') $last_by_vid[$vid] = $ts;
                continue;
            }

            if ($vid !== '') $last_by_vid[$vid] = $ts;
            $last_by_ip[$ip] = $ts;
            $sessions++;
            $session_ts[] = $ts;
            if ($ts > $last_ts) $last_ts = $ts;

            // Guest = ni interno ni identificado como asesor
            $is_guest = ($vid === '' || !isset($intern_v[$vid]));
            if ($is_guest) {
                $guest_sessions++;
                if ($first_guest_ts <= 0) $first_guest_ts = $ts;
                if ($ts >= $now - 24 * 3600) $guest_24h++;
                if ($ts >= $now - 48 * 3600) $guest_48h++;
                if ($ts >= $now - 7 * 86400) $guest_7d++;
            }

            if ($ts >= $now - 24 * 3600) $views24++;
            if ($ts >= $now - 7 * 86400) $views7d++;
            if ($ts >= $now - 48 * 3600) $views48++;

            if ($ts >= $now - $compare_win) $compare_ips[$ip] = true;
            if ($ts >= $now - $ip_win)      $ips_120m[$ip]    = true;
        }

        if ($sessions <= 0) {
            // ── Detectar "no abierta" ──────────────────────────
            $cot_meta_early = DB::row("SELECT created_at, estado, valida_hasta FROM cotizaciones WHERE id=?", [$cotizacion_id]);
            $created_early  = $cot_meta_early ? strtotime($cot_meta_early['created_at']) : $now;
            $age_h_early    = ($now - $created_early) / 3600.0;
            $accepted_early = ($cot_meta_early['estado'] ?? '') === 'aceptada';
            $has_js = ($es['opens'] > 0 || $es['closes'] > 0 || $es['tot_views'] > 0 ||
                       $es['tot_rev'] > 0 || $es['loops'] > 0 || $es['coupons'] > 0 ||
                       $es['scroll_any'] > 0 || $es['vis_max'] > 0 || $es['uniq_v'] > 0);
            // v2.2: usar vigencia real de la cotización (valida_hasta) o fallback a 30d
            $vigencia_ts = ($cot_meta_early && $cot_meta_early['valida_hasta'])
                ? strtotime($cot_meta_early['valida_hasta'])
                : $created_early + 30 * 86400;
            $no_abierta_age_ok = ($age_h_early >= 24 && $now <= $vigencia_ts);
            if (!$accepted_early && $no_abierta_age_ok && !$has_js) {
                return [
                    'score'=>0,'fit_pct'=>0.0,'priority_pct'=>0.0,
                    'bucket'=>'no_abierta','buckets'=>['no_abierta'],
                    'senales'=>[],'debug'=>['no_abierta'=>true],
                    'icons' => ['not_opened'=>true],
                ];
            }
            return ['score'=>0,'fit_pct'=>0.0,'priority_pct'=>0.0,'bucket'=>null,'buckets'=>[],'senales'=>[],'debug'=>[],'icons'=>[]];
        }

        // IPs y visitor_ids post primer guest (para multi-persona)
        // IMPORTANTE: aplicar los mismos filtros que el loop principal de sesiones
        $vids_post_guest = [];
        if ($first_guest_ts > 0) {
            foreach ($sess_rows as $s) {
                $ts2 = strtotime($s['created_at']);
                $ip2 = trim((string)($s['ip'] ?? ''));
                $ua2 = (string)($s['ua'] ?? '');
                $vid2 = trim((string)($s['visitor_id'] ?? ''));
                if ($ip2 === '' || $ts2 < $first_guest_ts) continue;
                if ($ts2 > $first_guest_ts + $multip_win) break;
                $dsig2 = trim((string)($s['device_sig'] ?? ''));
                if (($cfg['filtrar_bots'] ?? true) && (self::bot_ip($ip2) || self::bot_ua($ua2))) continue;
                if (($cfg['excluir_internos'] ?? true) && (
                    isset($intern_ip[$ip2]) ||
                    ($vid2 !== '' && isset($intern_v[$vid2])) ||
                    ($dsig2 !== '' && isset($intern_dsig[$dsig2]))
                )) continue;
                // Filtro ghost (paridad con loop principal)
                $sc2 = (int)($s['scroll_max'] ?? 0);
                $vi2 = (int)($s['visible_ms'] ?? 0);
                if (($cfg['filtrar_bots'] ?? true) && $sc2 === 0 && $vi2 === 0 && ($now - $ts2) > 120) {
                    $ip2_has_ev = false;
                    foreach ($ev_rows as $ev) { if (($ev['ip'] ?? '') === $ip2) { $ip2_has_ev = true; break; } }
                    if (!$ip2_has_ev) continue;
                }
                $ips_post_guest[$ip2] = true;
                if ($vid2 !== '') $vids_post_guest[$vid2] = true;
            }
        }

        // ── F. Métricas derivadas ─────────────────────────────
        $uniq_ips_total       = count($last_by_ip);
        $compare_ips_count    = count($compare_ips);
        $ips_120m_count       = count($ips_120m);
        $ips_post_guest_count = count($ips_post_guest);
        $vids_post_guest_count = count($vids_post_guest);

        // Guardar raw para FIT (calibrado con IPs sin descarte)
        $fit_uniq_ips = $uniq_ips_total;

        // ── F2. Descarte por device_sig ──────────────────────
        // Agrupa visitor_ids y IPs por device_sig para contar personas reales.
        // Solo reduce o mantiene conteos — nunca rompe la lógica existente.
        // Si no hay datos de device_sig, no cambia nada.
        if (!empty($vid_dsig) || !empty($ip_dsig)) {

            // Validar vids: agrupar por dsig (mismo device = misma persona)
            $g = [];
            foreach ($vids_post_guest as $vid => $_) {
                $g[$vid_dsig[$vid] ?? $vid] = true;
            }
            $vids_post_guest_count = count($g);

            // Validar IPs: expandir por dsigs, excluyendo empleados
            $validate_ips = function(array $ips) use ($ip_dsig, $intern_dsig): int {
                $g = [];
                foreach ($ips as $ip => $_) {
                    if (isset($ip_dsig[$ip])) {
                        foreach ($ip_dsig[$ip] as $dsig => $_2) {
                            if (!isset($intern_dsig[$dsig])) $g[$dsig] = true;
                        }
                    } else {
                        $g[$ip] = true;
                    }
                }
                return count($g);
            };

            $ips_post_guest_count = $validate_ips($ips_post_guest);
            $compare_ips_count    = $validate_ips($compare_ips);
            $ips_120m_count       = $validate_ips($ips_120m);
            $uniq_ips_total       = $validate_ips($last_by_ip);

            // multi_ips_24h (calculado aquí en vez de en la sección de buckets)
            $ips_24h_raw = [];
            foreach ($last_by_ip as $ip_addr => $ip_ts) {
                if ($ip_ts >= $now - 24 * 3600) $ips_24h_raw[$ip_addr] = true;
            }
            $multi_ips_24h = $validate_ips($ips_24h_raw);
        } else {
            // Sin datos de device_sig — calcular multi_ips_24h raw
            $multi_ips_24h = 0;
            foreach ($last_by_ip as $ip_addr => $ip_ts) {
                if ($ip_ts >= $now - 24 * 3600) $multi_ips_24h++;
            }
        }

        // Dispositivos únicos de guests (para debug)
        $devices = [];
        foreach ($sess_rows as $s) {
            $s_ua = (string)($s['ua'] ?? '');
            $s_vid = trim((string)($s['visitor_id'] ?? ''));
            $s_ip = trim((string)($s['ip'] ?? ''));
            if ($s_ip === '') continue;
            if (($cfg['excluir_internos'] ?? true) && (isset($intern_ip[$s_ip]) || ($s_vid !== '' && isset($intern_v[$s_vid])))) continue;
            $dev = self::parse_device($s_ua);
            if (!in_array($dev, $devices, true)) $devices[] = $dev;
        }

        // Span en ventana 48h
        $ts48 = array_values(array_filter($session_ts, fn($t) => $t >= $now - 48 * 3600));
        $span48 = count($ts48) >= 2 ? max($ts48) - min($ts48) : 0;

        // Gap penúltima → última sesión
        $gap_days = null;
        if (count($session_ts) >= 2) {
            $prev = $session_ts[count($session_ts) - 2];
            $gap_days = max(0, (int)floor(($last_ts - $prev) / 86400));
        }

        // ── G. Extraer métricas de eventos JS ─────────────────
        $e_opens      = (int)($es['opens']     ?? 0);
        $e_closes     = (int)($es['closes']    ?? 0);
        $e_coupons    = (int)($es['coupons']   ?? 0);
        $e_tot_views  = (int)($es['tot_views'] ?? 0);
        $e_tot_rev    = (int)($es['tot_rev']   ?? 0);
        $e_loops      = (int)($es['loops']     ?? 0);
        $e_promo      = (int)($es['promo']     ?? 0);
        $e_scroll_any = (int)($es['scroll_any']?? 0);
        $e_scroll_cls = (int)($es['scroll_cls']?? 0);
        $e_vis_max    = (int)($es['vis_max']   ?? 0);
        $e_vis_sum    = (int)($es['vis_sum']   ?? 0);
        $e_uniq_v     = (int)($es['uniq_v']    ?? 0);
        $e_sv_price   = (bool)($es['sv_price'] ?? false); // same-visitor price focus
        $e_mv_price   = (bool)($es['mv_price'] ?? false); // multi-visitor price
        $e_sv_sess    = (bool)($es['sv_sess']  ?? false); // same-visitor multi-session
        $e_sv_page    = (bool)($es['sv_page']  ?? false); // same-visitor multi-page
        $e_main_ev    = (int)($es['main_ev']   ?? 0);     // eventos del visitor principal
        $e_main_pev   = (int)($es['main_pev']  ?? 0);     // price events del visitor principal

        $has_tot_view = $e_tot_views > 0;
        $has_tot_rev  = $e_tot_rev   > 0;
        $has_loop     = $e_loops     > 0;
        $has_promo    = $e_promo     > 0;

        // price_signal_score — igual que en el radar original
        $pss = 0.0;
        if ($has_tot_view) $pss += 1.0;
        if ($has_tot_rev)  $pss += 2.0;
        if ($has_loop)     $pss += 3.0;
        if ($e_coupons > 0) $pss += 0.75;
        if ($has_promo)     $pss += 0.25;
        if ($e_sv_price)    $pss += 1.50;
        if ($e_mv_price)    $pss += 1.25;
        if ($e_sv_sess)     $pss += 0.50;
        if ($e_sv_page)     $pss += 0.50;

        // ── H. FIT + Priority ───
        // v3: Compradores decididos (≤1 sesión) → FIT neutral (sin patrón medible)
        $is_decided_buyer = ($sessions <= 1);
        if ($is_decided_buyer) {
            $cal_row = self::$fit_cal_cache[$empresa_id] ?? null;
            $fit_prob = $cal_row ? (float)($cal_row['global_rate'] ?? self::FIT_GLOBAL) : self::FIT_GLOBAL;
        } else {
            $fit_prob = self::fit_prob($sessions, $fit_uniq_ips, $gap_days, $empresa_id);
        }
        $fit_pct     = min(100.0, $fit_prob * 100.0);

        // recency_bonus — v2.1: escalón 72h para alto ticket (vigencia 30d)
        $recency = 0.0;
        if ($last_ts >= $now - 30 * 60)       $recency = 12.0;
        elseif ($last_ts >= $now - 4  * 3600) $recency = 8.0;
        elseif ($last_ts >= $now - 24 * 3600) $recency = 4.0;
        elseif ($last_ts >= $now - 48 * 3600) $recency = 2.0;
        elseif ($last_ts >= $now - 72 * 3600) $recency = 1.0;

        $priority = $fit_pct + $recency;
        $priority += min(4.0, $pss * 0.55);
        if ($e_sv_price) $priority += 0.75;
        if ($e_mv_price) $priority += 0.50;
        // v3.1: booster multi-persona por visitor_ids únicos
        // v3.1: booster multi-vid — usar solo vids de sesiones (más confiable que eventos JS)
        // e_uniq_v puede estar inflado por vids fantasma de antes del fix de PHP
        $multi_vid_count = $vids_post_guest_count;
        if ($multi_vid_count >= 3) $priority += 2.0;       // 3+ personas — señal muy fuerte
        elseif ($multi_vid_count >= 2) $priority += 1.0;   // 2 personas — señal débil
        $priority = min(100.0, $priority);

        // Datos de cotización (edad)
        $cot_meta = DB::row("SELECT created_at, estado, total FROM cotizaciones WHERE id=?", [$cotizacion_id]);
        $created_ts = $cot_meta ? strtotime($cot_meta['created_at']) : $now;
        $age_days   = max(0, (int)floor(($now - $created_ts) / 86400));
        $age_hours  = ($now - $created_ts) / 3600.0;
        $accepted   = ($cot_meta['estado'] ?? '') === 'aceptada';

        // ============================================================
        //  CLASIFICACIÓN — 15 BUCKETS
        //  Portados línea a línea del loop principal del radar original
        // ============================================================
        $buckets = [];

        // ── 1. Probable cierre → se evalúa AL FINAL (necesita saber los otros buckets)

        // ── 2. Inminente ────────────────────────────────────
        // Señales fuertes
        $sig_scroll = (
            $e_scroll_cls >= (int)self::u('imminent_signal_scroll_pct', $modo) ||
            $e_scroll_any >= (int)self::u('imminent_signal_scroll_pct', $modo)
        );
        $sig_visible = (
            ($e_vis_max >= (int)self::u('imminent_signal_vis_max', $modo) ||
             $e_vis_sum >= (int)self::u('imminent_signal_vis_sum', $modo)) &&
            ($has_tot_rev || $has_loop || $e_tot_views >= 2)
        );
        $sig_review48 = (
            $views48 >= (int)self::u('imminent_signal_views48', $modo) &&
            $span48  >= (int)self::u('imminent_signal_span_h', $modo) * 3600
        );
        $sig_price_strong = ($has_loop || $has_tot_rev);
        $sig_same_v = (
            $e_sv_price && ($e_sv_sess || $e_sv_page || $e_main_ev >= 4)
        );
        // Señales débiles
        $sig_views   = ($views24 >= (int)self::u('imminent_signal_views24', $modo));
        $sig_ips_120 = ($ips_120m_count >= (int)self::u('imminent_signal_ips_120m', $modo));
        $sig_closes  = ($e_closes >= (int)self::u('imminent_signal_closes', $modo));
        $sig_coupon  = ($e_coupons >= (int)self::u('imminent_signal_coupon', $modo));
        $sig_mv_price= $e_mv_price;

        $strong = 0; $total_sig = 0;
        if ($sig_scroll)       { $strong++; $total_sig++; }
        if ($sig_visible)      { $strong++; $total_sig++; }
        if ($sig_review48)     { $strong++; $total_sig++; }
        if ($sig_price_strong) { $strong++; $total_sig++; }
        if ($sig_same_v)       { $strong++; $total_sig++; }
        if ($sig_views)    $total_sig++;
        if ($sig_ips_120)  $total_sig++;
        if ($sig_closes)   $total_sig++;
        // v2.1: cupón = señal fuerte en alto ticket (decisión tomada, negociando precio)
        if ($sig_coupon)   { $strong++; $total_sig++; }
        if ($sig_mv_price) $total_sig++;
        // bonus señal media
        if (!$sig_price_strong && ($e_tot_views >= 2 || $pss >= 2.0) && $sig_scroll) $total_sig++;

        $promo_boost = (
            $has_promo && $strong >= 1 &&
            $total_sig >= max(1, (int)self::u('imminent_min_signals', $modo) - 1)
        );

        if (
            !$accepted &&
            $last_ts   >= $now - (int)self::u('imminent_recent_hours', $modo) * 3600 &&
            $fit_pct   >= (float)self::u('imminent_min_fit_pct', $modo) &&
            $age_hours >= (float)self::u('imminent_min_age_hours', $modo) &&
            $guest_sessions >= (int)self::u('imminent_min_guest', $modo) &&
            $span48    >= (float)self::u('imminent_min_span48_h', $modo) * 3600 &&  // regreso real, no cambio de red
            ($total_sig >= (int)self::u('imminent_min_signals', $modo) || $promo_boost) &&
            $strong >= (int)self::u('imminent_min_strong', $modo)
        ) {
            $buckets[] = 'inminente';
        }

        // ── 3. On Fire ──────────────────────────────────────
        if (
            !$accepted &&
            $last_ts  >= $now - (int)self::u('onfire_recent_hours', $modo) * 3600 &&
            $sessions >= (int)self::u('onfire_min_sessions', $modo) &&
            ($e_scroll_cls >= (int)self::u('onfire_min_scroll_pct', $modo) || $e_scroll_any >= (int)self::u('onfire_min_scroll_pct', $modo)) &&
            ($e_vis_sum >= (int)self::u('onfire_min_vis_sum', $modo) || $e_vis_max >= (int)self::u('onfire_min_vis_max', $modo)) &&
            ($has_loop || $has_tot_rev || $pss >= 4.0) &&
            (
                ($gap_days !== null && $gap_days >= (int)self::u('onfire_min_gap_days', $modo)) ||
                ($views48 >= (int)self::u('onfire_min_views48', $modo) && $span48 >= (int)self::u('onfire_min_span_h', $modo) * 3600)
            ) &&
            ($e_sv_price || $e_mv_price || $e_main_ev >= 4 || $e_sv_sess || $e_sv_page)
        ) {
            $buckets[] = 'onfire';
        }

        // ── 4. Validando precio ─────────────────────────────
        $pv_read = (
            $e_vis_max >= (int)self::u('priceval_vis_hard', $modo) ||
            $e_vis_sum >= (int)self::u('priceval_vis_sum',  $modo) ||
            ($e_vis_max >= (int)self::u('priceval_vis_soft', $modo) && $e_scroll_any >= (int)self::u('priceval_scroll_soft', $modo))
        );
        if (
            !$accepted &&
            $last_ts >= $now - (int)self::u('priceval_recent_hours', $modo) * 3600 &&
            $guest_sessions >= 2 &&
            ($pv_read || $e_sv_price || $e_mv_price || $e_main_pev >= 2) &&
            ($has_loop || $has_tot_rev || $e_tot_views >= 2 || $e_sv_price || $e_mv_price)
        ) {
            $buckets[] = 'validando_precio';
        }

        // ── 5. Predicción alta ──────────────────────────────
        // v2.3: ventanas adaptadas al ciclo de venta real de la empresa.
        // predict_window = ciclo × multiplicador por modo (agresivo=1.5, medio=1.0, ligero=0.7)
        // predict_alive  = 1/3 de la ventana (mín 3d, máx 30d)
        // Freelancer (ciclo 5d): ventana=5d, alive=3d
        // Constructora (ciclo 60d): ventana=60d, alive=20d
        $ciclo = self::ciclo_venta($empresa_id);
        $ciclo_mult = match($modo) { 'agresivo' => 1.5, 'ligero' => 0.7, default => 1.0 };
        $predict_window = max(7, min(120, (int)round($ciclo['dias'] * $ciclo_mult)));
        $predict_alive_days = max(3, min(30, (int)ceil($predict_window / 3)));
        $predict_alive = ($last_ts >= $now - $predict_alive_days * 86400);
        if (
            !$accepted &&
            $fit_pct  >= (float)self::u('predict_min_fit_pct', $modo) &&
            $age_days <= $predict_window &&
            $predict_alive
        ) {
            $buckets[] = 'prediccion_alta';
        }

        // ── 5b. Lectura comprometida ────────────────────────
        // Primera impresión fuerte: pocas sesiones, engagement por encima
        // del promedio de compradores, interacción con precio.
        // Thresholds adaptativos: scroll y visible del promedio de ventas.
        $ea = self::engage_avg($empresa_id);
        $engage_has_plus = ($has_tot_rev || $has_loop || $e_coupons > 0);
        $lc_max_sess = (int)self::u('engage_max_sessions', $modo);
        $lc_recent_ts = $now - (int)self::u('engage_recent_hours', $modo) * 3600;
        $lc_min_vis = (int)self::u('engage_min_vis_ms', $modo);
        if (
            !$accepted &&
            $sessions <= $lc_max_sess &&
            $guest_sessions >= 1 &&
            $last_ts >= $lc_recent_ts &&
            $e_scroll_any >= $ea['scroll'] &&
            $e_vis_max >= $ea['vis_ms'] &&
            $e_vis_max >= $lc_min_vis &&
            $has_tot_view &&
            $engage_has_plus
        ) {
            $buckets[] = 'lectura_comprometida';
        }

        // ── 6. Decisión activa ──────────────────────────────
        if (
            !$accepted &&
            $views48 >= (int)self::u('decision_min_views48', $modo) &&
            $span48  >= (int)self::u('decision_min_span_h', $modo) * 3600 &&
            $last_ts >= $now - (int)self::u('decision_window_h', $modo) * 3600
        ) {
            $buckets[] = 'decision_activa';
        }

        // ── 7. Re-enganche ──────────────────────────────────
        // v2.3: diferenciar calidad — re-enganche con señal de precio
        // tiene prioridad más alta (re_enganche_caliente vs re_enganche)
        $reeng_interest = (
            $guest_24h >= (int)self::u('reeng_min_guest_24h', $modo) ||
            $views24   >= (int)self::u('reeng_min_views24', $modo) ||
            $has_tot_view || $has_tot_rev || $has_loop || $pss >= 2.0 || $e_sv_price
        );
        $reeng_hot = ($has_tot_rev || $has_loop || $e_sv_price || $e_mv_price || $e_coupons > 0);
        if (
            !$accepted &&
            $gap_days !== null && $gap_days >= (int)self::u('reeng_gap_days', $modo) &&
            $last_ts >= $now - (int)self::u('reeng_recent_hours', $modo) * 3600 &&
            $reeng_interest
        ) {
            $buckets[] = $reeng_hot ? 're_enganche_caliente' : 're_enganche';
        }

        // ── 8. Multi-persona ────────────────────────────────
        // Basado en visitor_ids (validados por descarte) + IPs como respaldo
        if (
            !$accepted &&
            $last_ts >= $now - (int)self::u('multip_recent_hours', $modo) * 3600 &&
            $guest_sessions >= (int)self::u('multip_min_guest_total', $modo) &&
            (
                $vids_post_guest_count >= 2 ||
                $e_uniq_v >= 2 ||
                $ips_post_guest_count >= (int)self::u('multip_min_ips_post_guest', $modo)
            )
        ) {
            $buckets[] = 'multi_persona';
        }

        // ── 9. Revisión profunda ─────────────────────────────
        if (
            !$accepted &&
            $views48   >= (int)self::u('deep_min_views48', $modo) &&
            $span48    >= (int)self::u('deep_min_span_h', $modo) * 3600 &&
            $last_ts   >= $now - (int)self::u('deep_recent_hours', $modo) * 3600 &&
            $guest_48h >= (int)self::u('deep_min_guest_48h', $modo) &&
            ($e_vis_max >= (int)self::u('deep_min_vis_max', $modo) || $e_vis_sum >= (int)self::u('deep_min_vis_sum', $modo)) &&
            ($has_tot_view || $has_tot_rev || $has_loop || $pss >= 2.5 || $e_sv_price)
        ) {
            $buckets[] = 'revision_profunda';
        }

        // ── 10. Hesitación ───────────────────────────────────
        $hes_between = (
            $last_ts < $now - (int)self::u('hes_last_min_hours', $modo) * 3600 &&
            $last_ts >= $now - (int)self::u('hes_last_max_days', $modo) * 86400
        );
        if (
            !$accepted &&
            $guest_7d >= (int)self::u('hes_min_guest_7d', $modo) &&
            $hes_between &&
            $uniq_ips_total <= (int)self::u('hes_max_ips_total', $modo) &&
            $span48 < (int)self::u('hes_max_span_h', $modo) * 3600 &&
            ($has_tot_view || $has_tot_rev || $has_loop || $e_coupons > 0 || $pss >= 2.0 || $e_sv_price)
        ) {
            $buckets[] = 'hesitacion';
        }

        // ── 11. Sobre-análisis ───────────────────────────────
        $over_fit_ok = (
            $fit_pct < (float)self::u('over_max_fit_pct', $modo) ||
            ($e_sv_price && !($e_uniq_v >= 2) && $e_main_ev >= 6)
        );
        if (
            !$accepted &&
            $sessions       >= (int)self::u('over_min_sessions', $modo) &&
            $guest_sessions >= (int)self::u('over_min_guest', $modo) &&
            $age_days       >= (int)self::u('over_min_age_days', $modo) &&
            $last_ts        >= $now - (int)self::u('over_recent_days', $modo) * 86400 &&
            $ips_post_guest_count <= (int)self::u('over_max_ips_post_guest', $modo) &&
            $over_fit_ok
        ) {
            $buckets[] = 'sobre_analisis';
        }

        // ── 12. Alto importe ────────────────────────────────
        // v2.2: umbral dinámico — P80 de cotizaciones de la empresa (universal)
        // Para freelancer $5k: P80 ≈ $12k → alto importe = $12k+
        // Para constructora $200k: P80 ≈ $350k → alto importe = $350k+
        // Fallback al umbral estático si <5 cotizaciones
        $cot_total = (float)($cot_meta['total'] ?? 0.0);
        $hi_threshold = self::_p80_alto_importe($empresa_id, $modo);
        if (
            !$accepted &&
            $cot_total >= $hi_threshold &&
            $last_ts >= $now - (int)self::u('high_amount_recent_hours', $modo) * 3600
        ) {
            $buckets[] = 'alto_importe';
        }

        // ── 13. Vistas múltiples ─────────────────────────────
        // $multi_ips_24h ya calculado en F2 (con descarte por device_sig)
        if (
            !$accepted &&
            $last_ts >= $now - (int)self::u('multi_recent_hours', $modo) * 3600 &&
            (
                $multi_ips_24h >= (int)self::u('multi_min_ips', $modo) ||
                $views24 >= (int)self::u('multi_min_views24', $modo)
            )
        ) {
            $buckets[] = 'vistas_multiples';
        }

        // ── 14-17. Buckets exclusivos (uno solo) ─────────────
        // Mismo orden de precedencia que el radar original
        $is_revive  = (!$accepted && $gap_days !== null && $gap_days >= (int)self::u('revive_gap_days', $modo) && $last_ts >= $now - (int)self::u('revive_recent_hours', $modo) * 3600);
        $is_return4 = (!$accepted && $gap_days !== null && $gap_days >= (int)self::u('return_gap_days', $modo) && $last_ts >= $now - (int)self::u('return_recent_hours', $modo) * 3600);
        // v2.3: comparando requiere al menos 1 evento JS (scroll, visible_ms, open)
        // para evitar falsos positivos de bots que pasaron el filtro de UA/IP
        $has_any_engagement = ($e_opens > 0 || $e_scroll_any > 0 || $e_vis_max > 0);
        $is_compare = (!$accepted && $compare_ips_count >= (int)self::u('compare_min_ips', $modo) && $last_ts >= $now - (int)self::u('compare_window_h', $modo) * 3600 && $has_any_engagement);
        // v2.3: enfriándose requiere engagement previo mínimo.
        // Sin engagement = nunca enganchó → no se está "enfriando", está perdido.
        // Con precio tocado → "enfriandose" (accionable para el asesor).
        $is_cooling = (
            !$accepted &&
            $sessions >= (int)self::u('cooling_min_sessions', $modo) &&
            $last_ts  <  $now - (int)self::u('cooling_min_silence_h', $modo) * 3600 &&
            $last_ts  >= $now - (int)self::u('cooling_days', $modo) * 86400 &&
            ($e_opens > 0 || $e_scroll_any > 0 || $e_vis_max > 0)
        );

        if ($is_revive)       $buckets[] = 'revivio';
        elseif ($is_return4)  $buckets[] = 'regreso';
        elseif ($is_compare)  $buckets[] = 'comparando';
        elseif ($is_cooling)  $buckets[] = 'enfriandose';

        // ── 1. Probable cierre (CROSS-BUCKET) ──────────────────
        // v5: Meta-bucket con umbrales calibrados por arquetipo de comprador.
        // Arquetipos: decidido (1-2 sess, lee bien), comité (multi-IP),
        //   comparador (foco precio), analítico (muchas sesiones).
        // Requiere:
        //   - Estar en al menos 1 bucket de alta intención
        //   - Actividad reciente (72h)
        //   - sessions >= 2
        //   - 2+ categorías (agresivo/medio) o 3+ (ligero)
        //   - Al menos 1 categoría fuerte (precio O engagement)
        // Engagement: scroll ≥ 50-90% + visibilidad ≥ 5-20s (lectura real)
        // Precio: pss, sv/mv_price, loops, cupones (no se endurece — respeta al decidido)
        // Persistencia: sessions ≥ 2 o gap ≥ 1d
        // Social: multi-IP, multi-visitor (no se endurece — respeta al comprador solo)

        // Variables de PC — inicializar antes del bloque condicional (PHP 8.1+ compat)
        $cat_engagement = $cat_precio = $cat_persistencia = $cat_social = false;
        $cat_count = 0; $has_strong_cat = false;

        // Buckets que califican como alta intención
        $pc_qualifying = ['onfire','inminente','validando_precio','decision_activa',
                          're_enganche_caliente','prediccion_alta','lectura_comprometida','multi_persona','alto_importe'];
        $pc_source = null;
        foreach ($pc_qualifying as $qb) {
            if (in_array($qb, $buckets, true)) { $pc_source = $qb; break; }
        }

        $pc_min_sessions = ($pc_source === 'lectura_comprometida') ? 1 : 2;
        $pc_window = ($pc_source === 'lectura_comprometida')
            ? (int)self::u('engage_recent_hours', $modo) * 3600
            : 72 * 3600;

        if ($pc_source !== null && !$accepted && $last_ts >= $now - $pc_window && $sessions >= $pc_min_sessions) {
            // Contar categorías de señal presentes
            // Engagement: visibilidad real (no 8ms) + scroll significativo
            $pc_scroll_min = match($modo) { 'agresivo' => 50, 'ligero' => 90, default => 70 };
            $pc_vis_min    = match($modo) { 'agresivo' => 5000, 'ligero' => 20000, default => 15000 };
            $cat_engagement  = ($e_scroll_cls >= $pc_scroll_min || $e_scroll_any >= $pc_scroll_min ||
                                $e_vis_max >= $pc_vis_min || $e_vis_sum >= ($pc_vis_min * 2) ||
                                ($has_tot_view && $sessions >= 2));
            $cat_precio      = ($has_tot_rev || $has_loop || $e_coupons > 0 ||
                                $e_sv_price || $e_mv_price || $pss >= 2.0);
            $cat_persistencia = ($sessions >= 2 || ($gap_days !== null && $gap_days >= 1));
            // Con dsig: confiar en vids validados. Sin dsig: IPs solo si vids también confirman.
            $cat_social = (!empty($vid_dsig) || !empty($ip_dsig))
                ? ($vids_post_guest_count >= 2 || $e_uniq_v >= 2 || $e_mv_price)
                : ($vids_post_guest_count >= 2 || $e_uniq_v >= 2 || ($ips_post_guest_count >= 2 && $vids_post_guest_count >= 2) || $e_mv_price);

            $cat_count = (int)$cat_engagement + (int)$cat_precio +
                         (int)$cat_persistencia + (int)$cat_social;

            // v4: Exigir al menos 1 categoría fuerte (precio o engagement)
            // Solo persistencia + social = fase de consideration, no decision.
            $has_strong_cat = ($cat_precio || $cat_engagement);

            $pc_min_cats = ($modo === 'ligero') ? 3 : 2;
            if ($cat_count >= $pc_min_cats && $has_strong_cat) {
                $buckets[] = 'probable_cierre';
            }

        }

        // ── Iconos para la UI (mismos que el radar original) ─
        // Nota: no_abierta se maneja en el early return (línea ~380) cuando sessions=0.
        // $is_not_opened siempre sería false aquí porque sessions > 0 en este punto.
        $icons = [];
        if ($e_coupons > 0) $icons['coupon'] = true;
        if ($es['promo'] > 0) $icons['promo'] = true;
        if ($pss >= 3.0) $icons['price'] = true;
        if ($e_sv_price) $icons['sv_price'] = true;
        if ($e_mv_price) $icons['mv_price'] = true;
        // not_opened solo aplica vía early return cuando sessions=0

        // v3.1: multi_persona movido a zona caliente (después de decision_activa)
        static $PRIORIDAD = [
            'probable_cierre',
            'onfire','inminente','validando_precio',
            'prediccion_alta','lectura_comprometida','multi_persona','alto_importe','decision_activa','revivio',
            'no_abierta','re_enganche_caliente','re_enganche',
            'revision_profunda','vistas_multiples','hesitacion','sobre_analisis',
            'regreso','comparando','enfriandose',
        ];
        $bucket_main = null;
        foreach ($PRIORIDAD as $b) {
            if (in_array($b, $buckets, true)) { $bucket_main = $b; break; }
        }

        // cooling_price_touched — señal para la UI (igual que el radar original)
        $cooling_price_touched = (
            $has_tot_view || $has_tot_rev || $has_loop ||
            $e_coupons > 0 || $pss >= 2.0 ||
            $e_sv_price || $e_mv_price
        );
        $cooling_reason = $cooling_price_touched ? 'con precio' : 'sin precio';

        // Momentum: indicador visual de frescura (no modifica bucket ni score)
        $momentum = self::momentum($bucket_main, $last_ts, $now, $modo);

        $calentura_horas = max(12, $ciclo['dias'] * 24 / 5);
        $first_view_ts = !empty($session_ts) ? $session_ts[0] : $now;
        $horas_desde_primera_vista = ($now - $first_view_ts) / 3600.0;
        $en_calentura = ($horas_desde_primera_vista < ($calentura_horas + 48)) && in_array('probable_cierre', $buckets, true);

        return [
            'score'        => (int) round($priority),
            'fit_pct'      => round($fit_pct, 2),
            'priority_pct' => round($priority, 2),
            'bucket'       => $bucket_main,
            'buckets'      => $buckets,
            'momentum'     => $momentum,
            'pc_source'    => $pc_source,
            'calentura'    => $en_calentura,
            'cat_precio'   => $cat_precio ?? false,
            'first_view_ts'=> $first_view_ts,
            'calentura_hasta' => $en_calentura ? ($first_view_ts + ($calentura_horas + 48) * 3600) : null,
            'cooling_price_touched' => $cooling_price_touched,
            'cooling_reason'        => $cooling_reason,
            'icons'        => $icons,
            'senales'      => self::_senales(
                $sessions, $uniq_ips_total, $views24, $views48,
                $gap_days, $fit_pct, $pss, $has_loop, $has_tot_rev,
                $has_tot_view, $e_coupons, $e_sv_price, $e_mv_price,
                $last_ts, $now, $accepted
            ),
            'debug' => [
                'sessions'=>$sessions,'uniq_ips'=>$uniq_ips_total,'uniq_ips_raw'=>$fit_uniq_ips,
                'gap_days'=>$gap_days,'guest'=>$guest_sessions,
                'views24'=>$views24,'views48'=>$views48,
                'span48h'=>round($span48/3600,1).'h','pss'=>round($pss,2),
                'ev_uniq_v'=>$e_uniq_v,'vids_post'=>$vids_post_guest_count,'multi_vid'=>$multi_vid_count,
                'devices'=>$devices,'modo'=>$modo,'momentum'=>$momentum,
                'scroll_cls'=>$e_scroll_cls,'scroll_any'=>$e_scroll_any,
                'vis_max'=>$e_vis_max,'vis_sum'=>$e_vis_sum,
                'ips_post_guest'=>$ips_post_guest_count,
                'dsig_maps'=>count($vid_dsig).'/'.count($ip_dsig),
                'engage_avg'=>$ea['scroll'].'%/'.$ea['vis_ms'].'ms('.($ea['auto']?'auto':'default').')',
                'pc_cats'=>($pc_source !== null) ? [
                    'engagement'=>(bool)($cat_engagement ?? false),
                    'precio'=>(bool)($cat_precio ?? false),
                    'persistencia'=>(bool)($cat_persistencia ?? false),
                    'social'=>(bool)($cat_social ?? false),
                    'total'=>$cat_count ?? 0,
                    'has_strong'=>(bool)($has_strong_cat ?? false),
                    'min_sess_ok'=>$sessions >= 2,
                ] : null,
            ],
        ];
    }

    // ============================================================
    //  AGREGACIÓN DE EVENTOS JS
    //  Portado del bloque event_stats_by_quote del radar original
    // ============================================================
    private static function _agregar_eventos(array $rows, array $intern_v, array $intern_ip, array $intern_dsig, array $cfg): array
    {
        $s = [
            'opens'=>0,'closes'=>0,'coupons'=>0,'tot_views'=>0,'tot_rev'=>0,
            'loops'=>0,'promo'=>0,'scroll_any'=>0,'scroll_cls'=>0,
            'vis_max'=>0,'vis_by_page'=>[],'uniq_v'=>[],'uniq_sess'=>[],
            'v_ev'=>[],'v_price_ev'=>[],'v_sess'=>[],'v_page'=>[],'price_v'=>[],
            'ev_vid_dsig'=>[],
        ];

        foreach ($rows as $r) {
            $vid = trim((string)($r['visitor_id'] ?? ''));
            if ($vid !== '' && isset($intern_v[$vid])) continue;

            $ev_ip = trim((string)($r['ip'] ?? ''));
            if ($ev_ip !== '' && ($cfg['excluir_internos'] ?? true) && isset($intern_ip[$ev_ip])) continue;

            $ev_dsig = trim((string)($r['device_sig'] ?? ''));
            if ($ev_dsig !== '' && ($cfg['excluir_internos'] ?? true) && isset($intern_dsig[$ev_dsig])) continue;

            $ua = (string)($r['ua'] ?? '');
            if (($cfg['filtrar_bots'] ?? true) && self::bot_ua($ua)) continue;

            $tipo   = (string)($r['tipo'] ?? '');
            $scroll = max(0, (int)($r['max_scroll'] ?? 0));
            $vms    = max(0, (int)($r['visible_ms']  ?? 0));
            $sid    = trim((string)($r['session_id'] ?? ''));
            $pid    = trim((string)($r['page_id']    ?? ''));

            if ($tipo === 'quote_open')              $s['opens']++;
            if ($tipo === 'quote_close')             $s['closes']++;
            if ($tipo === 'coupon_validate_click')   $s['coupons']++;
            if ($tipo === 'section_view_totals')     $s['tot_views']++;
            if ($tipo === 'section_revisit_totals')  $s['tot_rev']++;
            if ($tipo === 'quote_price_review_loop') $s['loops']++;
            if ($tipo === 'promo_timer_present')     $s['promo']++;

            if ($scroll > $s['scroll_any']) $s['scroll_any'] = $scroll;
            if ($tipo === 'quote_close' && $scroll > $s['scroll_cls']) $s['scroll_cls'] = $scroll;
            if ($vms > $s['vis_max']) $s['vis_max'] = $vms;

            if ($pid === '') $pid = 'p_' . md5(($sid ?: 'x') . '|' . ($r['ts_unix'] ?? 0) . '|' . $tipo);
            if (!isset($s['vis_by_page'][$pid]) || $vms > $s['vis_by_page'][$pid]) $s['vis_by_page'][$pid] = $vms;

            if ($vid !== '') {
                $s['uniq_v'][$vid]  = true;
                $s['v_ev'][$vid]    = ($s['v_ev'][$vid]    ?? 0) + 1;
                if ($sid) $s['v_sess'][$vid][$sid] = true;
                if ($pid) $s['v_page'][$vid][$pid] = true;
            }
            if ($sid) $s['uniq_sess'][$sid] = true;

            $is_price = in_array($tipo, ['section_view_totals','section_revisit_totals','quote_price_review_loop','coupon_validate_click'], true);
            if ($is_price && $vid !== '') {
                $s['v_price_ev'][$vid] = ($s['v_price_ev'][$vid] ?? 0) + 1;
                $s['price_v'][$vid]    = true;
            }

            // Mapa vid→dsig desde eventos filtrados
            if ($vid !== '' && $ev_dsig !== '') $s['ev_vid_dsig'][$vid] = $ev_dsig;
        }

        // Totales derivados
        $vis_sum = array_sum($s['vis_by_page']);

        // Descarte: agrupar uniq_v y price_v por device_sig
        $ev_vd = $s['ev_vid_dsig'] ?? [];
        if (!empty($ev_vd)) {
            $g = [];
            foreach ($s['uniq_v'] as $vid => $_) $g[$ev_vd[$vid] ?? $vid] = true;
            $uniq_v_count = count($g);

            $gp = [];
            foreach ($s['price_v'] as $vid => $_) $gp[$ev_vd[$vid] ?? $vid] = true;
            $mv_price = count($gp) >= 2;
        } else {
            $uniq_v_count = count($s['uniq_v']);
            $mv_price = count($s['price_v']) >= 2;
        }

        // Visitor principal (merge por persona si hay dsig)
        if (!empty($ev_vd)) {
            $p_ev = [];
            foreach ($s['v_ev'] as $vid => $cnt) {
                $key = $ev_vd[$vid] ?? $vid;
                $p_ev[$key] = ($p_ev[$key] ?? 0) + $cnt;
            }
            $max_ev = 0; $main_v = '';
            foreach ($p_ev as $key => $cnt) { if ($cnt > $max_ev) { $max_ev = $cnt; $main_v = $key; } }

            $p_pev = [];
            foreach ($s['v_price_ev'] as $vid => $cnt) {
                $key = $ev_vd[$vid] ?? $vid;
                $p_pev[$key] = ($p_pev[$key] ?? 0) + $cnt;
            }
        } else {
            $max_ev = 0; $main_v = '';
            foreach ($s['v_ev'] as $vid => $cnt) { if ($cnt > $max_ev) { $max_ev = $cnt; $main_v = $vid; } }
            $p_pev = $s['v_price_ev'];
        }

        // Flags de comportamiento (sv = same-visitor, NO se tocan por descarte)
        $sv_sess = false;
        foreach ($s['v_sess'] as $sids) { if (count($sids) >= 2) { $sv_sess = true; break; } }
        $sv_page = false;
        foreach ($s['v_page'] as $pids) { if (count($pids) >= 2) { $sv_page = true; break; } }
        $sv_price = false;
        foreach ($s['v_price_ev'] as $cnt) { if ($cnt >= 2) { $sv_price = true; break; } }

        return [
            'opens'    => $s['opens'],      'closes'   => $s['closes'],
            'coupons'  => $s['coupons'],     'tot_views'=> $s['tot_views'],
            'tot_rev'  => $s['tot_rev'],     'loops'    => $s['loops'],
            'promo'    => $s['promo'],       'scroll_any'=>$s['scroll_any'],
            'scroll_cls'=>$s['scroll_cls'],  'vis_max'  => $s['vis_max'],
            'vis_sum'  => $vis_sum,          'uniq_v'   => $uniq_v_count,
            'sv_price' => $sv_price,         'mv_price' => $mv_price,
            'sv_sess'  => $sv_sess,          'sv_page'  => $sv_page,
            'main_ev'  => $max_ev,           'main_v'   => $main_v,
            'main_pev' => (function($vpc) { $mx=0; foreach($vpc as $c){if($c>$mx)$mx=$c;} return $mx; })($p_pev),
            'uniq_sess'=> $s['uniq_sess'],
        ];
    }

    // ============================================================
    //  MOMENTUM — indicador de frescura del bucket
    //  No modifica el bucket ni el score. Agrega una capa visual.
    //  Compara tiempo desde última actividad vs mitad de la ventana
    //  _recent_hours del bucket. Si excede → 'cooling'.
    //  Buckets estadísticos/atributo no aplican (siempre 'stable').
    // ============================================================
    private static function momentum(?string $bucket, int $last_ts, int $now, string $modo): string
    {
        if ($bucket === null) return 'none';

        // Buckets que NO decaen: son patrones/atributos, no estados emocionales
        static $NO_DECAY = [
            'prediccion_alta', 'alto_importe', 'sobre_analisis',
            'hesitacion', 'no_abierta', 'enfriandose',
        ];
        if (in_array($bucket, $NO_DECAY, true)) return 'stable';

        // Mapeo bucket → clave de umbral _recent_hours
        static $WINDOW_KEY = [
            'onfire'                => 'onfire_recent_hours',
            'inminente'             => 'imminent_recent_hours',
            'probable_cierre'       => 'onfire_recent_hours',       // usa ventana de onfire (72h medio)
            'validando_precio'      => 'priceval_recent_hours',
            'decision_activa'       => 'decision_window_h',
            're_enganche_caliente'  => 'reeng_recent_hours',
            're_enganche'           => 'reeng_recent_hours',
            'lectura_comprometida'  => 'engage_recent_hours',
            'multi_persona'         => 'multip_recent_hours',
            'revision_profunda'     => 'deep_recent_hours',
            'vistas_multiples'      => 'multi_recent_hours',
            'revivio'               => 'revive_recent_hours',
            'regreso'               => 'return_recent_hours',
            'comparando'            => 'compare_window_h',
        ];

        $key = $WINDOW_KEY[$bucket] ?? null;
        if ($key === null) return 'stable';

        $window_hours = (float) self::u($key, $modo);
        // Vigencia estable = mitad de la ventana del bucket
        $stable_hours = $window_hours / 2.0;
        $elapsed_hours = ($now - $last_ts) / 3600.0;

        if ($elapsed_hours <= $stable_hours) return 'stable';
        return 'cooling';
    }

    // ============================================================
    //  SEÑALES LEGIBLES PARA LA UI
    // ============================================================
    private static function _senales(
        int $sessions, int $uniq_ips, int $v24, int $v48,
        ?int $gap, float $fit, float $pss,
        bool $loop, bool $tot_rev, bool $tot_view, int $coupons,
        bool $sv_price, bool $mv_price, int $last_ts, int $now, bool $accepted
    ): array {
        $s = [];
        if ($sessions >= 2) $s['sesiones']   = ['pts'=>min(5,$sessions)*8, 'desc'=>"$sessions visitas únicas"];
        if ($uniq_ips >= 2) $s['multi_ip']   = ['pts'=>12, 'desc'=>"$uniq_ips personas distintas"];
        if ($loop)          $s['price_loop'] = ['pts'=>10, 'desc'=>'Revisó precio varias veces'];
        if ($tot_rev)       $s['tot_rev']    = ['pts'=>8,  'desc'=>'Volvió a revisar totales'];
        if ($tot_view && !$tot_rev) $s['tot_view'] = ['pts'=>4, 'desc'=>'Revisó sección de totales'];
        if ($coupons > 0)   $s['cupon']      = ['pts'=>10, 'desc'=>'Intentó aplicar cupón'];
        if ($sv_price)      $s['sv_price']   = ['pts'=>8,  'desc'=>'Misma persona enfocada en precio'];
        if ($mv_price)      $s['mv_price']   = ['pts'=>8,  'desc'=>'Varias personas revisaron precio'];
        if ($v24 >= 2)      $s['vistas_hoy'] = ['pts'=>6,  'desc'=>"$v24 vistas en 24h"];
        if ($gap !== null && $gap >= 30) $s['revivio'] = ['pts'=>12,'desc'=>"Regresó tras $gap días"];
        elseif ($gap !== null && $gap >= 4) $s['regreso'] = ['pts'=>10,'desc'=>"Regresó tras $gap días"];
        $hace = $now - $last_ts;
        if ($hace < 3600)       $s['reciente'] = ['pts'=>12,'desc'=>'Visitó hace menos de 1h'];
        elseif ($hace < 86400)  $s['reciente'] = ['pts'=>4, 'desc'=>'Visitó hoy'];
        if ($fit >= 10) $s['fit'] = ['pts'=>0, 'desc'=>'FIT '.round($fit,1).'% — patrón de cierre alto'];
        if ($accepted)  $s['aceptada'] = ['pts'=>0,'desc'=>'Cotización aceptada'];
        return $s;
    }

    // ============================================================
    //  PERSISTIR SCORE
    // ============================================================
    // Buckets que disparan push notification a la empresa
    const PUSH_BUCKETS = [
        'probable_cierre'       => 'Probable cierre',
        'onfire'                => 'On Fire',
        'inminente'             => 'Cierre inminente',
        'validando_precio'      => 'Validando precio',
        'prediccion_alta'       => 'Predicción alta',
        'lectura_comprometida'  => 'Lectura comprometida',
        'multi_persona'         => 'Multi-persona',
        'alto_importe'          => 'Alto importe',
    ];

    // Buckets de alta prioridad — NO aplica sticky (ya tienen ventanas amplias)
    const HIGH_PRIORITY_BUCKETS = [
        'probable_cierre', 'inminente', 'onfire', 'validando_precio', 'prediccion_alta', 'lectura_comprometida',
        'multi_persona', 'alto_importe',
    ];

    public static function recalcular(int $cotizacion_id, int $empresa_id): void
    {
        $cot = DB::row("SELECT estado, suspendida, radar_bucket, radar_bucket_at, radar_score, vendedor_id, titulo, numero FROM cotizaciones WHERE id=? AND empresa_id=?", [$cotizacion_id, $empresa_id]);
        if (!$cot || !in_array($cot['estado'], ['enviada','vista','aceptada']) || !empty($cot['suspendida'])) return;

        // Si está aceptada, preservar el bucket que tenía — no recalcular
        if ($cot['estado'] === 'aceptada') return;

        $old_bucket = $cot['radar_bucket'];
        $old_rscore = $cot['radar_score'];
        $r = self::score($cotizacion_id, $empresa_id);

        $new_bucket = $r['bucket'];

        // ── Sticky: mantener buckets secundarios por ciclo_venta/5 ──
        // Si el bucket BAJARÍA a NULL y el anterior es secundario reciente → mantener
        if ($new_bucket === null && $old_bucket !== null && $old_bucket !== 'no_abierta' && !in_array($old_bucket, self::HIGH_PRIORITY_BUCKETS, true)) {
            $bucket_at = $cot['radar_bucket_at'] ? strtotime($cot['radar_bucket_at']) : 0;
            if ($bucket_at > 0) {
                $ciclo = self::ciclo_venta($empresa_id);
                $hold_seconds = max(1, (int)round($ciclo['dias'] / 5)) * 86400;
                $hold_seconds = max(86400, min($hold_seconds, 7 * 86400)); // min 1d, max 7d
                if ((time() - $bucket_at) < $hold_seconds) {
                    $new_bucket = $old_bucket; // mantener
                }
            }
        }

        // Registrar transición de bucket si cambió
        if ($new_bucket !== $old_bucket) {
            try {
                DB::execute(
                    "INSERT INTO bucket_transitions (cotizacion_id, empresa_id, vendedor_id, bucket_anterior, bucket_nuevo, radar_score_ant, radar_score_new)
                     VALUES (?,?,?,?,?,?,?)",
                    [$cotizacion_id, $empresa_id, $cot['vendedor_id'], $old_bucket, $new_bucket, $old_rscore, $r['score']]
                );
            } catch (\Throwable $e) {}
        }

        // radar_bucket_at: actualizar solo cuando el bucket CAMBIA
        $bucket_at_sql = ($new_bucket !== $old_bucket) ? ', radar_bucket_at=NOW()' : '';

        try {
            DB::execute(
                "UPDATE cotizaciones SET radar_score=?, radar_bucket=?, radar_senales=?, radar_updated_at=NOW() {$bucket_at_sql} WHERE id=?",
                [
                    $r['score'],
                    $new_bucket,
                    json_encode(['senales'=>$r['senales'],'buckets'=>$r['buckets'],'debug'=>$r['debug'],'icons'=>$r['icons'] ?? [],'pc_source'=>$r['pc_source'] ?? null,'momentum'=>$r['momentum'] ?? 'stable','calentura'=>$r['calentura'] ?? false,'cat_precio'=>$r['cat_precio'] ?? false,'calentura_hasta'=>$r['calentura_hasta'] ?? null,'fit_pct'=>$r['fit_pct'] ?? 0,'sticky'=>($new_bucket !== $r['bucket'])]),
                    $cotizacion_id,
                ]
            );
        } catch (\Throwable $e) {
            // Fallback sin radar_bucket_at si columna no existe
            DB::execute(
                "UPDATE cotizaciones SET radar_score=?, radar_bucket=?, radar_senales=?, radar_updated_at=NOW() WHERE id=?",
                [
                    $r['score'],
                    $new_bucket,
                    json_encode(['senales'=>$r['senales'],'buckets'=>$r['buckets'],'debug'=>$r['debug'],'icons'=>$r['icons'] ?? [],'pc_source'=>$r['pc_source'] ?? null,'momentum'=>$r['momentum'] ?? 'stable','calentura'=>$r['calentura'] ?? false,'cat_precio'=>$r['cat_precio'] ?? false,'calentura_hasta'=>$r['calentura_hasta'] ?? null,'fit_pct'=>$r['fit_pct'] ?? 0]),
                    $cotizacion_id,
                ]
            );
        }

        // Push notification cuando entra en un bucket importante
        // Condiciones: bucket cambió + no se envió push para esta cotización en 24h
        if (
            $r['bucket'] !== null &&
            $r['bucket'] !== $old_bucket &&
            isset(self::PUSH_BUCKETS[$r['bucket']])
        ) {
            $ya_enviado = (int)DB::val(
                "SELECT COUNT(*) FROM notificaciones_push
                 WHERE empresa_id = ? AND tipo LIKE 'radar_%'
                   AND datos LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                [$empresa_id, '%"cotizacion_id":' . $cotizacion_id . '%']
            );
            $ncfg_radar = notif_config($empresa_id);
            if ($ya_enviado === 0 && ($ncfg_radar['radar_alerta'] ?? true)) {
                try {
                    $label = self::PUSH_BUCKETS[$r['bucket']];
                    $ref = $cot['numero'] ?: $cot['titulo'] ?: "#{$cotizacion_id}";
                    PushNotification::enviar_a_empresa(
                        $empresa_id,
                        'radar_' . $r['bucket'],
                        "Radar: {$label}",
                        "{$ref} — {$label}",
                        ['cotizacion_id' => $cotizacion_id, 'url' => '/radar']
                    );
                } catch (\Exception $e) {
                    // No bloquear el recálculo si falla el push
                }
            }
        }
    }

    public static function recalcular_empresa(int $empresa_id): int
    {
        self::auto_suspender($empresa_id);

        $cots = DB::query("SELECT id FROM cotizaciones WHERE empresa_id=? AND estado IN ('enviada','vista','aceptada') AND suspendida = 0 ORDER BY ultima_vista_at DESC, id DESC", [$empresa_id]);
        $count = 0;
        $start = time();
        foreach ($cots as $c) {
            self::recalcular((int)$c['id'], $empresa_id);
            $count++;
            if (time() - $start > 120) break;
        }
        return $count;
    }

    /**
     * Auto-suspender cotizaciones sin actividad después de X días.
     * Se ejecuta antes de recalcular para excluirlas del radar.
     */
    public static function auto_suspender(int $empresa_id): int
    {
        $emp = DB::row("SELECT auto_suspender_activo, auto_suspender_dias FROM empresas WHERE id=?", [$empresa_id]);
        if (!$emp || empty($emp['auto_suspender_activo'])) return 0;

        $dias = max(7, (int)$emp['auto_suspender_dias']);

        // Suspender cotizaciones enviadas/vista sin actividad en X días
        // Usa ultima_vista_at si existe, sino created_at
        $affected = DB::execute(
            "UPDATE cotizaciones
             SET suspendida = 1, suspendida_at = NOW(),
                 radar_bucket = NULL, radar_score = NULL, radar_senales = NULL
             WHERE empresa_id = ?
               AND estado IN ('enviada','vista')
               AND suspendida = 0
               AND COALESCE(ultima_vista_at, created_at) < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$empresa_id, $dias]
        );

        return $affected;
    }

    // ============================================================
    //  FILTRO DE INTERNOS — aprender visitor_id
    // ============================================================
    public static function marcar_visitor_interno(int $empresa_id, string $visitor_id, string $source = 'internal_user', ?int $usuario_id = null, string $ip = '', string $ua = ''): void
    {
        if (trim($visitor_id) === '') return;
        $now = time();
        // Construir label legible igual que ontime-quote-events.php
        $label_parts = [];
        if ($usuario_id) {
            $urow = DB::row("SELECT email FROM usuarios WHERE id=? LIMIT 1", [$usuario_id]);
            if ($urow) $label_parts[] = $urow['email'];
        }
        if ($ip !== '') $label_parts[] = $ip;
        if ($ua !== '') $label_parts[] = substr($ua, 0, 120);
        $label = substr(implode(' | ', $label_parts), 0, 255);

        DB::execute(
            "INSERT INTO radar_visitors_internos (empresa_id, visitor_id, source, usuario_id, ip, label, first_seen, last_seen)
             VALUES (?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
               last_seen  = VALUES(last_seen),
               ip         = VALUES(ip),
               source     = CASE WHEN source='internal_ip' THEN VALUES(source) ELSE source END,
               usuario_id = CASE WHEN usuario_id IS NULL AND VALUES(usuario_id) IS NOT NULL THEN VALUES(usuario_id) ELSE usuario_id END,
               label      = CASE WHEN VALUES(label) != '' THEN VALUES(label) ELSE label END",
            [$empresa_id, substr($visitor_id,0,64), $source, $usuario_id, $ip, $label, $now, $now]
        );

    }

    public static function es_visitor_interno(int $empresa_id, string $visitor_id): bool
    {
        if (trim($visitor_id) === '') return false;
        return (bool)DB::val(
            "SELECT 1 FROM radar_visitors_internos WHERE empresa_id=? AND visitor_id=? AND last_seen > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 365 DAY)) LIMIT 1",
            [$empresa_id, $visitor_id]
        );
    }

    // ============================================================
    //  EXPLICAR BUCKET — lenguaje natural para el vendedor
    // ============================================================
    public static function explicar_bucket(array $senales, ?string $feedback_tipo = null, bool $accepted = false): string
    {
        // Mapa por prefijo SO-Tipo (ignora navegador, que puede venir como ?)
        $DEV_PREFIX = [
            'iOS-M' => 'iPhone',
            'iPd-T' => 'iPad',
            'And-M' => 'Android',
            'And-T' => 'tablet Android',
            'Mac-D' => 'Mac',
            'Win-D' => 'PC',
            'Lin-D' => 'PC',
        ];
        $traducir_dev = function(string $code) use ($DEV_PREFIX): string {
            $prefix = substr($code, 0, 5);
            return $DEV_PREFIX[$prefix] ?? 'dispositivo';
        };

        if ($accepted) return "Esta cotización ya fue aceptada por el cliente.";

        $dbg = $senales['debug'] ?? [];
        $sn  = $senales['senales'] ?? [];
        $bks = $senales['buckets'] ?? [];

        $sess = (int)($dbg['sessions'] ?? 0);
        $vids = (int)($dbg['vids_post'] ?? 0);
        $ips  = (int)($dbg['ips_post_guest'] ?? 0);
        $vis_max = (int)($dbg['vis_max'] ?? 0);
        $scroll = (int)($dbg['scroll_any'] ?? 0);
        $pss = (float)($dbg['pss'] ?? 0);
        $raw_devs = $dbg['devices'] ?? [];
        $gap = $dbg['gap_days'] ?? null;

        $devices = array_values(array_unique(array_map($traducir_dev, $raw_devs)));

        $has_loop = isset($sn['price_loop']);
        $has_rev = isset($sn['tot_rev']);
        $has_sv = isset($sn['sv_price']);
        $has_mv = isset($sn['mv_price']);
        $has_reciente = isset($sn['reciente']);
        $has_regreso = isset($sn['regreso']) || isset($sn['revivio']);
        $is_lectura = in_array('lectura_comprometida', $bks, true);
        $is_alto = in_array('alto_importe', $bks, true);

        $lead = '';
        $f = [];

        // Frase de red/ubicación cuando hay varios visitors (vids ya está dedup por device_sig)
        $red_frase = '';
        if ($vids >= 2 && $ips >= 1) {
            if ($ips === 1) {
                $red_frase = " Todas conectadas a la misma red.";
            } elseif ($vids > $ips) {
                $red_frase = " Algunas comparten la misma red.";
            } elseif ($vids < $ips) {
                $red_frase = " Una se conectó desde varias redes (probablemente móvil).";
            } else {
                $red_frase = " Cada una desde una red distinta.";
            }
        }

        // Lead phrase — the most distinguishing feature, unique per cotización
        if ($vids >= 2 && count($devices) >= 2) {
            $dev_list = count($devices) <= 2 ? implode(' y ', $devices) : implode(', ', array_slice($devices, 0, -1)) . ' y ' . end($devices);
            $lead = "Varias personas la están evaluando desde {$dev_list}." . $red_frase;
        } elseif ($has_regreso || ($gap !== null && $gap >= 3)) {
            $dias = ($gap !== null && $gap >= 2) ? " después de {$gap} días" : '';
            $lead = "El cliente regresó{$dias} a revisar esta cotización.";
        } elseif ($is_lectura && $vis_max >= 60000) {
            $min = round($vis_max / 60000, 1);
            $lead = "El cliente dedicó {$min} minutos a leer esta propuesta.";
        } elseif ($is_alto) {
            $lead = "Cotización de alto valor con actividad del cliente.";
        } elseif ($sess >= 1 && $vis_max <= 500 && $scroll < 10) {
            $lead = "Cotización recién abierta. El cliente aún no ha revisado el contenido.";
        } elseif ($vids >= 2) {
            $lead = "Varias personas están revisando esta cotización." . $red_frase;
        } elseif (count($devices) >= 2) {
            $dev_list2 = count($devices) <= 2 ? implode(' y ', $devices) : implode(', ', array_slice($devices, 0, -1)) . ' y ' . end($devices);
            $lead = "El cliente la revisó desde {$dev_list2}.";
        }

        // Lectura
        if ($vis_max >= 120000 && !$is_lectura) {
            $min = round($vis_max / 60000, 1);
            $f[] = $scroll >= 80
                ? "Leyó toda la propuesta ({$min} min)."
                : "Le dedicó {$min} minutos de lectura.";
        } elseif ($scroll >= 80) {
            $f[] = "Leyó la propuesta completa.";
        } elseif ($scroll >= 50 && $scroll < 80) {
            $f[] = "Revisó más de la mitad de la propuesta.";
        }

        // Precio
        if ($has_loop && $has_rev) {
            $f[] = "Revisó el precio varias veces y regresó a comparar.";
        } elseif ($has_loop) {
            $f[] = "Revisó el precio varias veces.";
        } elseif ($has_rev) {
            $f[] = "Regresó a revisar el precio.";
        } elseif ($pss >= 4) {
            $f[] = "Mostró interés en el precio.";
        }

        // Multi-visitor precio
        if ($has_mv) {
            $f[] = "Varias personas revisaron el precio.";
        } elseif ($has_sv && !$has_mv) {
            $f[] = "La misma persona regresó al precio.";
        }

        // Lectura comprometida
        if ($is_lectura && $sess <= 2 && !str_contains($lead, 'leer')) {
            $f[] = "Está evaluando seriamente desde la primera visita.";
        }

        // Calentura
        $cal_hasta = $senales['calentura_hasta'] ?? null;
        if ($cal_hasta && time() < $cal_hasta && !empty($senales['cat_precio'])) {
            $f[] = "Se enganchó rápido con la propuesta.";
        }

        // Actividad reciente (solo si no fue lead)
        if ($has_reciente && !str_contains($lead, 'regresó')) {
            $f[] = "La actividad es reciente.";
        }

        // Feedback del vendedor vs señales del Radar
        if ($feedback_tipo === 'con_interes') {
            $radar_respalda = $pss >= 4 || $has_loop || $has_rev;
            $f[] = $radar_respalda
                ? "👍 Confirmaste interés y el Radar lo respalda."
                : "👍 Confirmaste interés.";
        } elseif ($feedback_tipo === 'sin_interes') {
            $radar_activo = $pss >= 4 || $has_loop || $has_rev || $has_reciente;
            $f[] = $radar_activo
                ? "👎 Marcaste sin interés, pero el Radar sigue detectando actividad."
                : "👎 Marcaste sin interés.";
        }

        if (!$lead && empty($f)) return "Cotización en evaluación.";

        return trim($lead . ' ' . implode(' ', $f));
    }

    // ============================================================
    //  PARSEAR USER-AGENT → etiqueta compacta (SO-Tipo-Nav)
    // ============================================================
    public static function parse_device(string $ua): string
    {
        $ua = strtolower($ua);
        // SO
        if (str_contains($ua, 'iphone'))         $so = 'iOS';
        elseif (str_contains($ua, 'ipad'))        $so = 'iPd';
        elseif (str_contains($ua, 'android'))     $so = 'And';
        elseif (str_contains($ua, 'windows'))     $so = 'Win';
        elseif (str_contains($ua, 'macintosh'))   $so = 'Mac';
        elseif (str_contains($ua, 'linux'))       $so = 'Lin';
        else $so = '?';
        // Tipo
        if (str_contains($ua, 'iphone') || (str_contains($ua, 'android') && str_contains($ua, 'mobile')))
            $tipo = 'M';
        elseif (str_contains($ua, 'ipad') || (str_contains($ua, 'android') && !str_contains($ua, 'mobile')))
            $tipo = 'T';
        else
            $tipo = 'D';
        // Navegador
        if (str_contains($ua, 'edg/'))            $nav = 'Edge';
        elseif (str_contains($ua, 'firefox'))     $nav = 'FF';
        elseif (str_contains($ua, 'crios'))       $nav = 'Chr';
        elseif (str_contains($ua, 'chrome') && !str_contains($ua, 'edg/')) $nav = 'Chr';
        elseif (str_contains($ua, 'safari') && !str_contains($ua, 'chrome')) $nav = 'Saf';
        else $nav = '?';
        return "{$so}-{$tipo}-{$nav}";
    }

    // ============================================================
    //  AUTO-APRENDER IP DEL USUARIO QUE ABRE EL RADAR
    //  Portado del radar original: al abrir el radar se registra la IP
    //  del asesor en radar_ips_internas para excluir sus vistas futuras.
    //  Llamar desde modules/radar/index.php al inicio de cada request.
    // ============================================================
    public static function aprender_ip_radar(int $empresa_id, string $ip, ?int $usuario_id = null): void
    {
        if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) return;
        $uid = $usuario_id ?: (Auth::id() ?: null);
        try {
            DB::execute(
                "INSERT INTO radar_ips_internas (empresa_id, ip, aprendida_ts, fuente, usuario_id)
                 VALUES (?, ?, ?, 'radar_open', ?)
                 ON DUPLICATE KEY UPDATE aprendida_ts = VALUES(aprendida_ts), fuente = VALUES(fuente), usuario_id = VALUES(usuario_id)",
                [$empresa_id, $ip, time(), $uid]
            );
        } catch (\Throwable $e) {
            // Fallback sin usuario_id si la columna no existe aún
            try {
                DB::execute(
                    "INSERT INTO radar_ips_internas (empresa_id, ip, aprendida_ts, fuente)
                     VALUES (?, ?, ?, 'radar_open')
                     ON DUPLICATE KEY UPDATE aprendida_ts = VALUES(aprendida_ts), fuente = VALUES(fuente)",
                    [$empresa_id, $ip, time()]
                );
            } catch (\Throwable $e2) {}
        }
    }

    // ============================================================
    //  CALIBRACIÓN DEL MODELO FIT
    //  Calibra por sesiones × IPs × gap (más predictivo que solo monto)
    // ============================================================
    public static function calibrar(int $empresa_id): array
    {
        // Fuente de cierres reales: ventas creadas (cotizaciones convertidas)
        // + cotizaciones aceptadas que aún no se convirtieron.
        // Una cotización 'convertida' ya tiene su registro en ventas.
        // Una 'aceptada' es un cierre confirmado aunque no se haya generado la venta aún.
        // Fuente de cierres: ventas reales (excluye canceladas)
        // Una cotización aceptada siempre genera una venta automáticamente.
        $cerr = (int)DB::val("SELECT COUNT(*) FROM ventas WHERE empresa_id=? AND estado != 'cancelada'", [$empresa_id]);
        if ($cerr < 3) return ['ok'=>false,'msg'=>'Se necesitan al menos 3 ventas para calibrar.'];

        $total = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND estado NOT IN ('borrador') AND suspendida = 0", [$empresa_id]);
        $base  = $total > 0 ? $cerr / $total : self::FIT_GLOBAL;

        $cots = DB::query(
            "SELECT c.id, c.estado, c.total,
                    COUNT(qs.id) AS num_sess,
                    COUNT(DISTINCT qs.ip) AS num_ips,
                    DATEDIFF(MAX(qs.created_at), MIN(qs.created_at)) AS span_d
             FROM cotizaciones c
             LEFT JOIN quote_sessions qs ON qs.cotizacion_id = c.id
             WHERE c.empresa_id=? AND c.estado NOT IN ('borrador') AND c.suspendida = 0
             GROUP BY c.id, c.estado, c.total",
            [$empresa_id]
        );

        $bk_sess = []; $bk_ips = []; $bk_gap = [];
        foreach ($cots as $c) {
            $sess = (int)$c['num_sess'];
            $closed = in_array($c['estado'], ['aceptada','convertida'], true);

            // v3: Excluir "compradores decididos" (≤1 sesión) de calibración.
            // Son clientes recomendados, repetidores, o que cerraron por otro canal.
            // No tienen patrón de engagement medible → contaminan los buckets.
            if ($sess <= 1) continue;

            $bs = self::bk_sess($sess);
            $bi = self::bk_ips((int)$c['num_ips']);
            $bg = self::bk_gap($c['span_d'] !== null ? (int)$c['span_d'] : null);
            $bk_sess[$bs] = ($bk_sess[$bs] ?? [0,0]); $bk_sess[$bs][0]++; if ($closed) $bk_sess[$bs][1]++;
            $bk_ips[$bi]  = ($bk_ips[$bi]  ?? [0,0]); $bk_ips[$bi][0]++;  if ($closed) $bk_ips[$bi][1]++;
            $bk_gap[$bg]  = ($bk_gap[$bg]  ?? [0,0]); $bk_gap[$bg][0]++;  if ($closed) $bk_gap[$bg][1]++;
        }

        // Laplace smoothing: (cierres + α·prior) / (total + α)
        // α = 5 pseudo-observaciones → con pocos datos converge al prior global,
        // con muchos datos domina la evidencia real de la empresa.
        $alpha = 5;
        $to_rate = fn($bk, $fallback) => array_map(
            fn($v) => $v[0] > 0
                ? round(($v[1] + $alpha * $fallback) / ($v[0] + $alpha), 4)
                : $fallback,
            $bk
        );
        $raw_sess = $to_rate($bk_sess, $base);
        $raw_ips  = $to_rate($bk_ips,  $base);
        $raw_gap  = $to_rate($bk_gap,  $base);

        // v3: Isotonic regression (Pool Adjacent Violators)
        // Fuerza monotonicidad: más engagement = rate >= bucket anterior
        $isotonic = function(array $rates, array $ordered_keys): array {
            $vals = [];
            foreach ($ordered_keys as $k) $vals[] = $rates[$k] ?? 0;
            $n = count($vals);
            $blocks = [];
            for ($i = 0; $i < $n; $i++) {
                $blocks[] = ['sum' => $vals[$i], 'cnt' => 1];
                while (count($blocks) >= 2) {
                    $last = $blocks[count($blocks) - 1];
                    $prev = $blocks[count($blocks) - 2];
                    if (($last['sum'] / $last['cnt']) >= ($prev['sum'] / $prev['cnt'])) break;
                    array_pop($blocks);
                    $blocks[count($blocks) - 1] = [
                        'sum' => $prev['sum'] + $last['sum'],
                        'cnt' => $prev['cnt'] + $last['cnt'],
                    ];
                }
            }
            $result = []; $idx = 0;
            foreach ($blocks as $b) {
                $avg = round($b['sum'] / max(1, $b['cnt']), 4);
                for ($j = 0; $j < $b['cnt'] && $idx < $n; $j++) {
                    $result[$ordered_keys[$idx]] = $avg;
                    $idx++;
                }
            }
            return $result;
        };

        $rate_sess = $isotonic($raw_sess, ['1','2','3-4','5-7','8-12','13+']);
        $rate_ips  = $isotonic($raw_ips,  ['1','2','3','4+']);
        $rate_gap  = $isotonic($raw_gap,  ['4+d','1-3d','sin']);

        // Bandas por monto — auto-calculadas por percentiles de cada empresa
        $bandas = [];
        $totales = array_map(fn($c) => (float)$c['total'], $cots);
        sort($totales);
        $n = count($totales);
        if ($n >= 5) {
            $pcts = [0, 0.2, 0.4, 0.6, 0.8, 1.0];
            $cortes = [];
            foreach ($pcts as $p) {
                $idx = min((int)floor($p * ($n - 1)), $n - 1);
                $cortes[] = $totales[$idx];
            }
            // Eliminar duplicados y redondear a miles
            $cortes = array_values(array_unique(array_map(fn($v) => round($v / 1000) * 1000, $cortes)));
            // Construir bandas entre cada corte
            for ($i = 0; $i < count($cortes) - 1; $i++) {
                $mn = $cortes[$i];
                $mx = $cortes[$i + 1];
                if ($mn === $mx) continue;
                $lbl = '$' . number_format($mn / 1000, 0) . 'K–$' . number_format($mx / 1000, 0) . 'K';
                $bandas[] = ['min' => $mn, 'max' => $mx, 'label' => $lbl];
            }
            // Última banda abierta
            $last = end($cortes);
            $bandas[] = ['min' => $last, 'max' => null, 'label' => '$' . number_format($last / 1000, 0) . 'K+'];
        } else {
            // Menos de 5 cotizaciones: una sola banda
            $bandas[] = ['min' => 0, 'max' => null, 'label' => 'Todas'];
        }
        // Calcular tasa de cierre por banda (prepared statements)
        foreach ($bandas as &$b) {
            $params_t = [$empresa_id, $b['min']];
            $params_c = [$empresa_id, $b['min']];
            $mc = '';
            if ($b['max'] !== null) {
                $mc = 'AND c.total < ?';
                $params_t[] = $b['max'];
                $params_c[] = $b['max'];
            }
            $bt = (int)DB::val("SELECT COUNT(*) FROM cotizaciones c WHERE c.empresa_id=? AND c.total>=? $mc AND estado NOT IN ('borrador')", $params_t);
            $bc = (int)DB::val("SELECT COUNT(*) FROM cotizaciones c WHERE c.empresa_id=? AND c.total>=? $mc AND estado IN ('aceptada','convertida')", $params_c);
            $b['total'] = $bt;
            $b['cerradas'] = $bc;
            $b['tasa_cierre'] = $bt > 0 ? round($bc / $bt, 4) : 0;
        }
        unset($b);
        $bandas = array_values(array_filter($bandas, fn($b) => $b['total'] > 0));

        DB::execute("UPDATE radar_fit_calibracion SET activa=0 WHERE empresa_id=?", [$empresa_id]);
        DB::execute(
            "INSERT INTO radar_fit_calibracion (empresa_id, global_rate, bandas_json, rate_sess_json, rate_ips_json, rate_gap_json, cotizaciones, ventas_cerradas, activa)
             VALUES (?,?,?,?,?,?,?,?,1)",
            [$empresa_id, round($base,4), json_encode($bandas), json_encode($rate_sess), json_encode($rate_ips), json_encode($rate_gap), $total, $cerr]
        );
        // Invalidar cache de FIT para que recalcular_empresa() use datos frescos
        unset(self::$fit_cal_cache[$empresa_id]);

        return ['ok'=>true,'global_rate'=>round($base*100,2),'rate_sess'=>$rate_sess,'rate_ips'=>$rate_ips,'rate_gap'=>$rate_gap,'bandas'=>$bandas,'total'=>$total,'cierres'=>$cerr];
    }

    public static function check_auto_calibrar(int $empresa_id): void
    {
        // v2.3: auto-calibración proporcional + decaimiento temporal
        // - Trigger proporcional: cada 20% de nuevos cierres (mín 5, máx 50)
        // - Trigger temporal: recalibrar si la calibración tiene > 90 días
        // Así funciona igual para empresa con 10 ventas que con 1000.
        if (!($this_cfg = self::config($empresa_id))['calibracion_auto']) return;

        $cal_row = DB::row("SELECT ventas_cerradas, created_at FROM radar_fit_calibracion WHERE empresa_id=? AND activa=1 ORDER BY created_at DESC LIMIT 1", [$empresa_id]);
        $ultima   = (int)($cal_row['ventas_cerradas'] ?? 0);
        $cal_age  = $cal_row ? (time() - strtotime($cal_row['created_at'])) / 86400 : 999;
        $actuales = (int)DB::val("SELECT COUNT(*) FROM ventas WHERE empresa_id=? AND estado != 'cancelada'", [$empresa_id]);

        // Trigger proporcional: 20% del total anterior, clamped [5, 50]
        $delta_trigger = max(5, min(50, (int)ceil($ultima * 0.20)));
        $needs_recal = ($actuales - $ultima) >= $delta_trigger;

        // Trigger temporal: > 90 días desde última calibración
        if (!$needs_recal && $cal_age > 90 && $actuales >= 3) $needs_recal = true;

        if ($needs_recal) self::calibrar($empresa_id);
    }

    // ============================================================
    //  CONFIG
    // ============================================================
    public static function config(int $empresa_id): array
    {
        if (isset(self::$config_cache[$empresa_id])) return self::$config_cache[$empresa_id];
        $raw = DB::val("SELECT radar_config FROM empresas WHERE id=?", [$empresa_id]);
        self::$config_cache[$empresa_id] = array_merge([
            'sensibilidad'     => 'medio',
            'calibracion_auto' => true,
            'excluir_internos' => true,
            'filtrar_bots'     => true,
            'deduplicar_30min' => true,
        ], $raw ? (json_decode($raw, true) ?? []) : []);
        return self::$config_cache[$empresa_id];
    }

    public static function guardar_config(int $empresa_id, array $cfg): void
    {
        if (!in_array($cfg['sensibilidad'] ?? '', ['agresivo','medio','ligero'])) $cfg['sensibilidad'] = 'medio';
        DB::execute("UPDATE empresas SET radar_config=? WHERE id=?", [json_encode($cfg), $empresa_id]);
        unset(self::$config_cache[$empresa_id]);
    }

    // ============================================================
    //  P80 ALTO IMPORTE — cacheado por request (evita N+1)
    // ============================================================
    private static function _p80_alto_importe(int $empresa_id, string $modo): float
    {
        if (isset(self::$p80_cache[$empresa_id])) return self::$p80_cache[$empresa_id];

        $fallback = (float)self::u('high_amount_threshold', $modo);
        $hi_count = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND estado NOT IN ('borrador') AND total > 0",
            [$empresa_id]
        );
        if ($hi_count >= 5) {
            $hi_offset = max(0, (int)floor($hi_count * 0.80) - 1);
            $p80 = DB::val(
                "SELECT total FROM cotizaciones WHERE empresa_id=? AND estado NOT IN ('borrador') AND total > 0
                 ORDER BY total ASC LIMIT 1 OFFSET ?",
                [$empresa_id, $hi_offset]
            );
            if ($p80 !== null && $p80 !== false) {
                $fallback = (float)$p80;
            }
        }
        self::$p80_cache[$empresa_id] = $fallback;
        return $fallback;
    }

    // ============================================================
    //  CICLO DE VENTA — mediana de días envío → cierre por empresa
    //  Se auto-calcula con datos reales. Fallback: 30 días.
    //  Cacheado por request. Usado para adaptar ventanas temporales.
    // ============================================================
    public static function ciclo_venta(int $empresa_id): array
    {
        if (isset(self::$ciclo_cache[$empresa_id])) return self::$ciclo_cache[$empresa_id];

        // Días entre envío de cotización y creación de la venta (cierre real)
        $rows = DB::query(
            "SELECT DATEDIFF(v.created_at, c.enviada_at) AS dias
             FROM ventas v
             JOIN cotizaciones c ON c.id = v.cotizacion_id
             WHERE v.empresa_id = ? AND v.estado != 'cancelada'
               AND c.enviada_at IS NOT NULL
             ORDER BY dias ASC",
            [$empresa_id]
        );

        $dias = array_filter(array_map(fn($r) => max(0, (int)$r['dias']), $rows), fn($d) => $d >= 0);
        sort($dias);
        $n = count($dias);

        if ($n < 3) {
            // Menos de 3 ventas: no hay suficiente data, fallback 30d
            $result = ['dias' => 30, 'mediana' => null, 'p25' => null, 'p75' => null, 'n' => $n, 'auto' => false];
        } else {
            $med = $n % 2 === 0
                ? ($dias[$n/2 - 1] + $dias[$n/2]) / 2
                : $dias[(int)floor($n/2)];
            $p25 = $dias[(int)floor($n * 0.25)];
            $p75 = $dias[(int)floor($n * 0.75)];
            // Clamp mediana a [1, 180] para evitar extremos
            $med = max(1, min(180, (int)round($med)));
            $result = ['dias' => $med, 'mediana' => $med, 'p25' => $p25, 'p75' => $p75, 'n' => $n, 'auto' => true];
        }

        self::$ciclo_cache[$empresa_id] = $result;
        return $result;
    }

    // ============================================================
    //  LISTA PARA UI
    // ============================================================
    public static function lista_activas(int $empresa_id, ?int $usuario_id = null): array
    {
        $uw = $usuario_id ? "AND c.usuario_id=?" : '';
        $params = [$empresa_id];
        if ($usuario_id) $params[] = $usuario_id;
        return DB::query(
            "SELECT c.id, c.titulo, c.numero, c.total, c.estado,
                    c.radar_score, c.radar_bucket, c.radar_senales, c.radar_updated_at,
                    c.visitas, c.ultima_vista_at, c.enviada_at,
                    cl.nombre AS cliente_nombre, cl.telefono AS cli_tel,
                    u.nombre  AS asesor_nombre,
                    IFNULL(qs_agg.num_sesiones, 0) AS num_sesiones,
                    IFNULL(qs_agg.num_ips, 0) AS num_ips
             FROM cotizaciones c
             LEFT JOIN clientes cl ON cl.id=c.cliente_id
             LEFT JOIN usuarios  u  ON u.id=c.usuario_id
             LEFT JOIN (
                 SELECT cotizacion_id, COUNT(*) AS num_sesiones, COUNT(DISTINCT ip) AS num_ips
                 FROM quote_sessions GROUP BY cotizacion_id
             ) qs_agg ON qs_agg.cotizacion_id = c.id
             WHERE c.empresa_id=? AND c.estado IN ('enviada','vista','aceptada') $uw
             ORDER BY c.radar_score IS NULL ASC, c.radar_score DESC, c.ultima_vista_at DESC",
            $params
        );
    }

    // ============================================================
    //  PROMEDIOS DE ENGAGEMENT DE VENTAS CERRADAS
    //  Para bucket "lectura_comprometida" — auto-ajustable
    // ============================================================
    public static function engage_avg(int $empresa_id): array
    {
        if (isset(self::$engage_avg_cache[$empresa_id])) return self::$engage_avg_cache[$empresa_id];

        $row = DB::row(
            "SELECT ROUND(AVG(qs.scroll_max)) AS scroll_avg,
                    ROUND(AVG(qs.visible_ms)) AS vis_avg,
                    COUNT(DISTINCT v.id) AS ventas
             FROM quote_sessions qs
             JOIN cotizaciones c ON c.id = qs.cotizacion_id
             JOIN ventas v ON v.cotizacion_id = c.id
             WHERE v.empresa_id = ? AND v.estado != 'cancelada'
               AND qs.scroll_max > 0 AND qs.visible_ms > 0",
            [$empresa_id]
        );

        $row = $row ?? ['ventas' => 0, 'scroll_avg' => null, 'vis_avg' => null];
        $ventas = (int)($row['ventas'] ?? 0);
        if ($ventas < 5) {
            $cfg = self::config($empresa_id);
            $modo = $cfg['sensibilidad'] ?? 'medio';
            $def_scroll = match($modo) { 'agresivo' => 60, 'ligero' => 80, default => 70 };
            $def_vis    = match($modo) { 'agresivo' => 20000, 'ligero' => 35000, default => 25000 };
            $result = ['scroll' => $def_scroll, 'vis_ms' => $def_vis, 'ventas' => $ventas, 'auto' => false];
        } else {
            $result = [
                'scroll' => max(50, min(95, (int)$row['scroll_avg'])),
                'vis_ms' => max(10000, min(180000, (int)$row['vis_avg'])),
                'ventas' => $ventas,
                'auto'   => true,
            ];
        }

        self::$engage_avg_cache[$empresa_id] = $result;
        return $result;
    }
}
