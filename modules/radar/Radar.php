<?php
// ============================================================
//  CotizaApp — modules/radar/Radar.php  v2.3
//  Motor de scoring e intención — 16 buckets × 3 modos
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

        // ── Bucket 1: Probable cierre ────────────────────────
        'hot_close_last_hours'       => [48,    24,    12   ],
        'hot_close_min_views24'      => [1,     2,     2    ],
        'hot_close_min_views7d'      => [2,     2,     3    ],

        // ── Bucket 2: Inminente ──────────────────────────────
        'imminent_recent_hours'      => [36,    24,    24   ],
        'imminent_min_fit_pct'       => [6.0,   8.5,   11.0 ],
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
        'imminent_min_signals'       => [1,     2,     3    ],
        'imminent_min_strong'        => [1,     1,     2    ],

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
        'priceval_scroll_hard'       => [70,    90,    90   ],

        // ── Bucket 5: Predicción alta ────────────────────────
        'predict_min_fit_pct'        => [10.0,  14.0,  18.0 ],
        'predict_recent_days'        => [45,    30,    21   ],

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
        'multip_boost_vis_max'       => [8000,  12000, 16000],

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
    const FIT_MAX     = 0.350;  // 35% máximo absoluto

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
        '66.249.',
        '40.77.','52.167.','157.55.','207.46.',
        '31.13.','66.220.','173.252.','69.171.','57.141.',
        '104.28.',
        '154.12.','185.191.','85.208.',
        '54.39.','15.235.','167.114.',
        '51.161.','51.222.','142.44.','148.113.',
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

        $ls = $global > 0 ? $rs / $global : 1.0;
        $li = $global > 0 ? $ri / $global : 1.0;
        $lg = $global > 0 ? $rg / $global : 1.0;

        // Cap multiplicadores individuales a [0.3, 3.0] para amortiguar
        // correlación entre sesiones/IPs/gap (no son independientes)
        $cap = fn($x) => max(0.3, min(3.0, $x));
        $fit = $global * $cap($ls) * $cap($li) * $cap($lg);
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
        $ev_rows = DB::query(
            "SELECT tipo, ts_unix, max_scroll, visible_ms, open_ms,
                    visitor_id, session_id, page_id, ip, ua
             FROM quote_events
             WHERE cotizacion_id=? AND ts_unix >= ?
             ORDER BY id ASC",
            [$cotizacion_id, $lookback]
        );

        // ── B. Internos ──────────────────────────────────────
        $intern_v = [];
        $intern_ip = [];
        if ($cfg['excluir_internos'] ?? true) {
            foreach (DB::query("SELECT visitor_id FROM radar_visitors_internos WHERE empresa_id=?", [$empresa_id]) as $r) {
                $intern_v[$r['visitor_id']] = true;
            }
            foreach (DB::query("SELECT ip FROM radar_ips_internas WHERE empresa_id=?", [$empresa_id]) as $r) {
                $intern_ip[$r['ip']] = true;
            }
        }

        // ── C. Agregar eventos JS (misma lógica que event_stats_by_quote) ──
        $es = self::_agregar_eventos($ev_rows, $intern_v, $intern_ip, $cfg);

        // ── D. Cargar sesiones históricas ─────────────────────
        $sess_rows = DB::query(
            "SELECT ip, user_agent AS ua, visitor_id, created_at, scroll_max, visible_ms
             FROM quote_sessions WHERE cotizacion_id=? ORDER BY created_at ASC",
            [$cotizacion_id]
        );

        // ── E. Procesar sesiones (deduplicar, filtrar, contar) ─
        $dedupe = (int) self::u('dedupe_seconds', $modo);
        $multip_win = (int) self::u('multip_ip_window_min', $modo) * 60;
        $compare_win = (int) self::u('compare_window_h', $modo) * 3600;
        $ip_win = (int) self::u('imminent_ip_window_min', $modo) * 60;

        $last_by_ip    = [];
        $session_ts    = [];
        $sessions = $views24 = $views7d = $views48 = 0;
        $guest_sessions = $guest_24h = $guest_48h = $guest_7d = 0;
        $first_guest_ts = 0;
        $last_ts = 0;
        $compare_ips = $ips_120m = [];

        // Para multip: IPs en ventana post primer guest
        $ips_post_guest = [];

        foreach ($sess_rows as $s) {
            $ip  = trim((string)($s['ip'] ?? ''));
            $ua  = (string)($s['ua'] ?? '');
            $ts  = strtotime($s['created_at']);
            $vid = trim((string)($s['visitor_id'] ?? ''));

            if ($ip === '') continue;
            if (($cfg['filtrar_bots'] ?? true) && (self::bot_ip($ip) || self::bot_ua($ua))) continue;
            if (($cfg['excluir_internos'] ?? true) && (isset($intern_ip[$ip]) || ($vid !== '' && isset($intern_v[$vid])))) continue;

            // Deduplicar por IP en ventana
            if (isset($last_by_ip[$ip]) && ($ts - $last_by_ip[$ip]) < $dedupe) continue;

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

        // IPs post primer guest (para multi-persona)
        // IMPORTANTE: aplicar los mismos filtros que el loop principal de sesiones
        if ($first_guest_ts > 0) {
            foreach ($sess_rows as $s) {
                $ts2 = strtotime($s['created_at']);
                $ip2 = trim((string)($s['ip'] ?? ''));
                $ua2 = (string)($s['ua'] ?? '');
                $vid2 = trim((string)($s['visitor_id'] ?? ''));
                if ($ip2 === '' || $ts2 < $first_guest_ts) continue;
                if ($ts2 > $first_guest_ts + $multip_win) break;
                if (($cfg['filtrar_bots'] ?? true) && (self::bot_ip($ip2) || self::bot_ua($ua2))) continue;
                if (($cfg['excluir_internos'] ?? true) && (isset($intern_ip[$ip2]) || ($vid2 !== '' && isset($intern_v[$vid2])))) continue;
                $ips_post_guest[$ip2] = true;
            }
        }

        // ── F. Métricas derivadas ─────────────────────────────
        $uniq_ips_total       = count($last_by_ip);
        $compare_ips_count    = count($compare_ips);
        $ips_120m_count       = count($ips_120m);
        $ips_post_guest_count = count($ips_post_guest);

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

        // ── H. FIT + Priority (igual que en radar original) ───
        $fit_prob    = self::fit_prob($sessions, $uniq_ips_total, $gap_days, $empresa_id);
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

        // ── 1. Probable cierre ───────────────────────────────
        // v2.3: requiere actividad reciente + acumulada + señal de calidad + FIT mínimo
        // Psicología: quien va a comprar interactúa con el precio, no solo mira.
        // Sin un piso de FIT, cualquier curioso con 2 vistas entra aquí y el
        // asesor pierde confianza en el radar. Exigimos FIT ≥ 5% o sesiones ≥ 3.
        $hot_quality = (
            $has_tot_view || $has_tot_rev || $has_loop || $e_coupons > 0 ||
            $e_sv_price || $e_mv_price || $pss >= 2.0 || $e_scroll_cls >= 70
        );
        if (
            !$accepted &&
            $last_ts >= $now - (int)self::u('hot_close_last_hours', $modo) * 3600 &&
            $views24 >= (int)self::u('hot_close_min_views24', $modo) &&
            $views7d >= (int)self::u('hot_close_min_views7d', $modo) &&
            $hot_quality &&
            ($fit_pct >= 5.0 || $sessions >= 3)
        ) {
            $buckets[] = 'probable_cierre';
        }

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
            $guest_sessions >= 1 &&
            ($guest_sessions >= 2 || $e_sv_price || $e_mv_price) &&
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
        $multip_boost = (
            $has_tot_rev || $has_loop || $pss >= 3.0 || $e_opens >= 2 ||
            $e_closes >= 1 || $e_vis_max >= (int)self::u('multip_boost_vis_max', $modo) || $e_mv_price || $e_uniq_v >= 2
        );
        if (
            !$accepted &&
            $last_ts >= $now - (int)self::u('multip_recent_hours', $modo) * 3600 &&
            $guest_sessions >= (int)self::u('multip_min_guest_total', $modo) &&
            (
                ($e_uniq_v >= 2 && ($e_mv_price || $e_sv_sess)) ||
                $ips_post_guest_count >= (int)self::u('multip_min_ips_post_guest', $modo) ||
                ($ips_post_guest_count >= max(2, (int)self::u('multip_min_ips_post_guest', $modo) - 1) && $multip_boost)
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
        // Portado de is_multi del radar original:
        // (2+ IPs distintas en 24h) OR (3+ vistas en 24h) + última vista reciente
        $multi_ips_24h = 0;
        foreach ($last_by_ip as $ip_addr => $ip_ts) {
            if ($ip_ts >= $now - 24 * 3600) $multi_ips_24h++;
        }
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

        static $PRIORIDAD = [
            'onfire','inminente','probable_cierre','validando_precio',
            'prediccion_alta','alto_importe','decision_activa','revivio',
            'no_abierta','re_enganche_caliente','re_enganche','multi_persona',
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

        return [
            'score'        => (int) round($priority),
            'fit_pct'      => round($fit_pct, 2),
            'priority_pct' => round($priority, 2),
            'bucket'       => $bucket_main,
            'buckets'      => $buckets,
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
                'sessions'=>$sessions,'uniq_ips'=>$uniq_ips_total,
                'gap_days'=>$gap_days,'guest'=>$guest_sessions,
                'views24'=>$views24,'views48'=>$views48,
                'span48h'=>round($span48/3600,1).'h','pss'=>round($pss,2),
                'ev_uniq_v'=>$e_uniq_v,'modo'=>$modo,
            ],
        ];
    }

    // ============================================================
    //  AGREGACIÓN DE EVENTOS JS
    //  Portado del bloque event_stats_by_quote del radar original
    // ============================================================
    private static function _agregar_eventos(array $rows, array $intern_v, array $intern_ip, array $cfg): array
    {
        $s = [
            'opens'=>0,'closes'=>0,'coupons'=>0,'tot_views'=>0,'tot_rev'=>0,
            'loops'=>0,'promo'=>0,'scroll_any'=>0,'scroll_cls'=>0,
            'vis_max'=>0,'vis_by_page'=>[],'uniq_v'=>[],'uniq_sess'=>[],
            'v_ev'=>[],'v_price_ev'=>[],'v_sess'=>[],'v_page'=>[],'price_v'=>[],
        ];

        foreach ($rows as $r) {
            $vid = trim((string)($r['visitor_id'] ?? ''));
            if ($vid !== '' && isset($intern_v[$vid])) continue;

            // v2.1: filtrar IPs internas en eventos (paridad con sesiones)
            $ev_ip = trim((string)($r['ip'] ?? ''));
            if ($ev_ip !== '' && ($cfg['excluir_internos'] ?? true) && isset($intern_ip[$ev_ip])) continue;

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
        }

        // Totales derivados
        $vis_sum = array_sum($s['vis_by_page']);
        $uniq_v_count = count($s['uniq_v']);

        // Visitor principal
        $max_ev = 0; $main_v = '';
        foreach ($s['v_ev'] as $vid => $cnt) { if ($cnt > $max_ev) { $max_ev = $cnt; $main_v = $vid; } }

        // Flags de comportamiento
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
            'sv_price' => $sv_price,         'mv_price' => count($s['price_v']) >= 2,
            'sv_sess'  => $sv_sess,          'sv_page'  => $sv_page,
            'main_ev'  => $max_ev,           'main_v'   => $main_v,
            'main_pev' => (function($vpc) { $mx=0; foreach($vpc as $c){if($c>$mx)$mx=$c;} return $mx; })($s['v_price_ev']),
            'uniq_sess'=> $s['uniq_sess'],
        ];
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
    public static function recalcular(int $cotizacion_id, int $empresa_id): void
    {
        $cot = DB::row("SELECT estado FROM cotizaciones WHERE id=? AND empresa_id=?", [$cotizacion_id, $empresa_id]);
        if (!$cot || !in_array($cot['estado'], ['enviada','vista','aceptada'])) return;

        $r = self::score($cotizacion_id, $empresa_id);
        DB::execute(
            "UPDATE cotizaciones SET radar_score=?, radar_bucket=?, radar_senales=?, radar_updated_at=NOW() WHERE id=?",
            [
                $r['score'],
                $r['bucket'],
                json_encode(['senales'=>$r['senales'],'buckets'=>$r['buckets'],'debug'=>$r['debug'],'icons'=>$r['icons'] ?? []]),
                $cotizacion_id,
            ]
        );
    }

    public static function recalcular_empresa(int $empresa_id): int
    {
        $cots = DB::query("SELECT id FROM cotizaciones WHERE empresa_id=? AND estado IN ('enviada','vista','aceptada')", [$empresa_id]);
        foreach ($cots as $c) self::recalcular((int)$c['id'], $empresa_id);
        return count($cots);
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
    //  AUTO-APRENDER IP DEL USUARIO QUE ABRE EL RADAR
    //  Portado del radar original: al abrir el radar se registra la IP
    //  del asesor en radar_ips_internas para excluir sus vistas futuras.
    //  Llamar desde modules/radar/index.php al inicio de cada request.
    // ============================================================
    public static function aprender_ip_radar(int $empresa_id, string $ip): void
    {
        if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) return;
        try {
            DB::execute(
                "INSERT INTO radar_ips_internas (empresa_id, ip, aprendida_ts, fuente)
                 VALUES (?, ?, ?, 'radar_open')
                 ON DUPLICATE KEY UPDATE aprendida_ts = VALUES(aprendida_ts), fuente = VALUES(fuente)",
                [$empresa_id, $ip, time()]
            );
        } catch (\Throwable $e) { /* No bloquear */ }
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

        $total = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND estado NOT IN ('borrador')", [$empresa_id]);
        $base  = $total > 0 ? $cerr / $total : self::FIT_GLOBAL;

        $cots = DB::query(
            "SELECT c.id, c.estado, c.total,
                    (SELECT COUNT(*)        FROM quote_sessions qs WHERE qs.cotizacion_id=c.id) AS num_sess,
                    (SELECT COUNT(DISTINCT qs2.ip) FROM quote_sessions qs2 WHERE qs2.cotizacion_id=c.id) AS num_ips,
                    DATEDIFF(
                        (SELECT qs3.created_at FROM quote_sessions qs3 WHERE qs3.cotizacion_id=c.id ORDER BY qs3.created_at DESC LIMIT 1),
                        (SELECT qs4.created_at FROM quote_sessions qs4 WHERE qs4.cotizacion_id=c.id ORDER BY qs4.created_at DESC LIMIT 1 OFFSET 1)
                    ) AS gap_d
             FROM cotizaciones c
             WHERE c.empresa_id=? AND c.estado NOT IN ('borrador')",
            [$empresa_id]
        );

        $bk_sess = []; $bk_ips = []; $bk_gap = [];
        foreach ($cots as $c) {
            $closed = in_array($c['estado'], ['aceptada','convertida'], true);
            $bs = self::bk_sess((int)$c['num_sess']);
            $bi = self::bk_ips((int)$c['num_ips']);
            $bg = self::bk_gap($c['gap_d'] !== null ? (int)$c['gap_d'] : null);
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
        $rate_sess = $to_rate($bk_sess, $base);
        $rate_ips  = $to_rate($bk_ips,  $base);
        $rate_gap  = $to_rate($bk_gap,  $base);

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
            $b['tasa_cierre'] = $bt > 0 ? round($bc / $bt, 4) : $base;
        }
        unset($b);

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
                    (SELECT COUNT(*)              FROM quote_sessions qs  WHERE qs.cotizacion_id=c.id) AS num_sesiones,
                    (SELECT COUNT(DISTINCT qs2.ip) FROM quote_sessions qs2 WHERE qs2.cotizacion_id=c.id) AS num_ips
             FROM cotizaciones c
             LEFT JOIN clientes cl ON cl.id=c.cliente_id
             LEFT JOIN usuarios  u  ON u.id=c.usuario_id
             WHERE c.empresa_id=? AND c.estado IN ('enviada','vista','aceptada') $uw
             ORDER BY c.radar_score IS NULL ASC, c.radar_score DESC, c.ultima_vista_at DESC",
            $params
        );
    }
}
