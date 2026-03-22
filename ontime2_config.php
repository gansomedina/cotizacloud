<?php
// ============================================================
//  ontime2_config.php — CotizaCloud Radar v2.4 para On Time
//  Umbrales × 3 modos, modelo FIT, constantes, helpers
// ============================================================

// ─── UMBRALES × 3 MODOS [agresivo, medio, ligero] ──────────
const U = [
    'dedupe_seconds'             => [1200,  1800,  3600 ],

    // Inminente
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

    // On Fire
    'onfire_recent_hours'        => [96,    72,    48   ],
    'onfire_min_sessions'        => [2,     2,     3    ],
    'onfire_min_scroll_pct'      => [70,    90,    90   ],
    'onfire_min_vis_sum'         => [20000, 30000, 40000],
    'onfire_min_vis_max'         => [15000, 22000, 30000],
    'onfire_min_gap_days'        => [1,     1,     1    ],
    'onfire_min_views48'         => [2,     3,     4    ],
    'onfire_min_span_h'          => [4,     6,     8    ],

    // Validando precio
    'priceval_recent_hours'      => [96,    72,    48   ],
    'priceval_vis_soft'          => [2000,  4000,  6000 ],
    'priceval_vis_hard'          => [5000,  8000,  12000],
    'priceval_vis_sum'           => [10000, 14000, 20000],
    'priceval_scroll_soft'       => [40,    50,    70   ],
    'priceval_scroll_hard'       => [70,    90,    90   ],

    // Predicción alta
    'predict_min_fit_pct'        => [10.0,  14.0,  18.0 ],
    'predict_recent_days'        => [45,    30,    21   ],

    // Decisión activa
    'decision_window_h'          => [72,    48,    48   ],
    'decision_min_views48'       => [2,     4,     5    ],
    'decision_min_span_h'        => [3,     6,     8    ],

    // Re-enganche
    'reeng_gap_days'             => [2,     4,     6    ],
    'reeng_recent_hours'         => [240,   168,   120  ],
    'reeng_min_guest_24h'        => [1,     1,     1    ],
    'reeng_min_views24'          => [1,     1,     2    ],

    // Multi-persona
    'multip_recent_hours'        => [96,    72,    48   ],
    'multip_ip_window_min'       => [720,   480,   360  ],
    'multip_min_ips_post_guest'  => [2,     3,     4    ],
    'multip_min_guest_total'     => [1,     2,     3    ],
    'multip_boost_vis_max'       => [8000,  12000, 16000],

    // Revisión profunda
    'deep_recent_hours'          => [96,    72,    48   ],
    'deep_min_views48'           => [2,     3,     4    ],
    'deep_min_span_h'            => [2,     3,     4    ],
    'deep_min_guest_48h'         => [1,     1,     2    ],
    'deep_min_vis_max'           => [8000,  10000, 15000],
    'deep_min_vis_sum'           => [14000, 18000, 25000],

    // Hesitación
    'hes_min_guest_7d'           => [1,     2,     3    ],
    'hes_last_min_hours'         => [24,    36,    48   ],
    'hes_last_max_days'          => [14,    10,    7    ],
    'hes_max_ips_total'          => [3,     2,     2    ],
    'hes_max_span_h'             => [8,     6,     4    ],

    // Sobre-análisis
    'over_min_sessions'          => [12,    20,    28   ],
    'over_min_guest'             => [5,     8,     12   ],
    'over_min_age_days'          => [5,     7,     10   ],
    'over_recent_days'           => [30,    21,    14   ],
    'over_max_fit_pct'           => [18.0,  14.0,  10.0 ],
    'over_max_ips_post_guest'    => [5,     4,     3    ],

    // Regreso
    'return_gap_days'            => [2,     4,     6    ],
    'return_recent_hours'        => [72,    48,    36   ],

    // Revivió
    'revive_gap_days'            => [15,    30,    45   ],
    'revive_recent_hours'        => [72,    48,    36   ],

    // Comparando
    'compare_min_ips'            => [2,     2,     3    ],
    'compare_window_h'           => [36,    24,    24   ],

    // Enfriándose
    'cooling_min_sessions'       => [3,     4,     5    ],
    'cooling_days'               => [10,    7,     5    ],
    'cooling_min_silence_h'      => [60,    48,    36   ],

    // Alto importe
    'high_amount_threshold'      => [80000, 120000, 160000],
    'high_amount_recent_hours'   => [72,    48,    36   ],

    // Vistas múltiples
    'multi_min_ips'              => [2,     2,     3    ],
    'multi_min_views24'          => [2,     3,     4    ],
    'multi_recent_hours'         => [36,    24,    24   ],
];

// ─── FIT — Tasas fallback (se reemplazan con calibración en vivo) ───
const FIT_GLOBAL  = 0.10;
const FIT_MIN     = 0.005;
const FIT_MAX     = 0.350;
const FIT_RATE_SESS = [
    '1'   => 0.06, '2'   => 0.10, '3-4' => 0.13,
    '5-7' => 0.16, '8-12'=> 0.155,'13+' => 0.13,
];
const FIT_RATE_IPS = [
    '1' => 0.07, '2' => 0.14, '3' => 0.11, '4+' => 0.12,
];
const FIT_RATE_GAP = [
    'sin' => 0.08, '1-3d' => 0.12, '4+d' => 0.09,
];

// ─── MOMENTUM — vigencia por bucket ───
const MOMENTUM_WINDOW_KEY = [
    'onfire'                => 'onfire_recent_hours',
    'inminente'             => 'imminent_recent_hours',
    'probable_cierre'       => 'onfire_recent_hours',
    'validando_precio'      => 'priceval_recent_hours',
    'decision_activa'       => 'decision_window_h',
    're_enganche_caliente'  => 'reeng_recent_hours',
    're_enganche'           => 'reeng_recent_hours',
    'multi_persona'         => 'multip_recent_hours',
    'revision_profunda'     => 'deep_recent_hours',
    'vistas_multiples'      => 'multi_recent_hours',
    'revivio'               => 'revive_recent_hours',
    'regreso'               => 'return_recent_hours',
    'comparando'            => 'compare_window_h',
];
const MOMENTUM_NO_DECAY = [
    'prediccion_alta', 'alto_importe', 'sobre_analisis',
    'hesitacion', 'no_abierta', 'enfriandose',
];

// ─── Prioridad de buckets (primer match gana) ───
const BUCKET_PRIORITY = [
    'probable_cierre',
    'onfire','inminente','validando_precio',
    'prediccion_alta','alto_importe','decision_activa','revivio',
    'no_abierta','re_enganche_caliente','re_enganche','multi_persona',
    'revision_profunda','vistas_multiples','hesitacion','sobre_analisis',
    'regreso','comparando','enfriandose',
];

// ─── Bot lists ───
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

// ─── Helpers ───
function ot2_u(string $key, string $modo): int|float {
    $i = match ($modo) { 'agresivo' => 0, 'ligero' => 2, default => 1 };
    return U[$key][$i];
}

function ot2_bot_ua(string $ua): bool {
    $ua = strtolower(trim($ua));
    if (strlen($ua) < 10) return true;
    foreach (BOT_UA as $b) { if (str_contains($ua, $b)) return true; }
    return false;
}

function ot2_bot_ip(string $ip): bool {
    foreach (BOT_IP as $p) { if (str_starts_with($ip, $p)) return true; }
    return false;
}

function ot2_bk_sess(int $n): string {
    if ($n <= 1) return '1';
    if ($n === 2) return '2';
    if ($n <= 4) return '3-4';
    if ($n <= 7) return '5-7';
    if ($n <= 12) return '8-12';
    return '13+';
}

function ot2_bk_ips(int $n): string {
    if ($n <= 1) return '1';
    if ($n === 2) return '2';
    if ($n === 3) return '3';
    return '4+';
}

function ot2_bk_gap(?int $g): string {
    if ($g === null || $g <= 0) return 'sin';
    if ($g <= 3) return '1-3d';
    return '4+d';
}

function ot2_momentum(?string $bucket, int $last_ts, int $now, string $modo): string {
    if ($bucket === null) return 'none';
    if (in_array($bucket, MOMENTUM_NO_DECAY, true)) return 'stable';
    $key = MOMENTUM_WINDOW_KEY[$bucket] ?? null;
    if ($key === null) return 'stable';
    $window_hours = (float) ot2_u($key, $modo);
    $stable_hours = $window_hours / 2.0;
    $elapsed_hours = ($now - $last_ts) / 3600.0;
    return ($elapsed_hours <= $stable_hours) ? 'stable' : 'cooling';
}

function ot2_fit_prob(int $sessions, int $uniq_ips, ?int $gap_days, float $global, array $rs_map, array $ri_map, array $rg_map): float {
    $rs = $rs_map[ot2_bk_sess($sessions)] ?? $global;
    $ri = $ri_map[ot2_bk_ips($uniq_ips)]  ?? $global;
    $rg = $rg_map[ot2_bk_gap($gap_days)]  ?? $global;
    $cap = fn($x) => max(0.3, min(3.0, $x));
    $ls = $global > 0 ? $rs / $global : 1.0;
    $li = $global > 0 ? $ri / $global : 1.0;
    $lg = $global > 0 ? $rg / $global : 1.0;
    $fit = $global * $cap($ls) * $cap($li) * $cap($lg);
    return max(FIT_MIN, min(FIT_MAX, $fit));
}

function ot2_recency_bonus(int $last_ts, int $now): float {
    if ($last_ts >= $now - 30 * 60)       return 12.0;
    if ($last_ts >= $now - 4  * 3600)     return 8.0;
    if ($last_ts >= $now - 24 * 3600)     return 4.0;
    if ($last_ts >= $now - 48 * 3600)     return 2.0;
    if ($last_ts >= $now - 72 * 3600)     return 1.0;
    return 0.0;
}

// ─── UI Metadata ───
const BM = [
    'onfire'                => ['🔴','#991b1b','#fff1f2','On Fire'],
    'inminente'             => ['🟠','#c2410c','#fff7ed','Inminente'],
    'probable_cierre'       => ['🎯','#92400e','#fffbeb','Probable cierre'],
    'decision_activa'       => ['🟡','#92400e','#fffbeb','Decisión activa'],
    'validando_precio'      => ['💸','#92400e','#fffbeb','Validando precio'],
    'prediccion_alta'       => ['🔮','#166534','#f0fdf4','Predicción alta'],
    'alto_importe'          => ['💰','#1d4ed8','#dbeafe','Alto importe'],
    're_enganche_caliente'  => ['🔥','#6d28d9','#ede9fe','Re-enganche caliente'],
    're_enganche'           => ['🟣','#6d28d9','#ede9fe','Re-enganche'],
    'multi_persona'         => ['👥','#1d4ed8','#dbeafe','Multi-persona'],
    'revision_profunda'     => ['🧾','#1d4ed8','#dbeafe','Revisión profunda'],
    'vistas_multiples'      => ['🟩','#166534','#f0fdf4','Vistas múltiples'],
    'hesitacion'            => ['🟠','#c2410c','#fff7ed','Hesitación'],
    'sobre_analisis'        => ['🟤','#64748b','#f1f5f9','Sobre-análisis'],
    'revivio'               => ['💜','#6d28d9','#ede9fe','Revivió'],
    'regreso'               => ['🟣','#6d28d9','#ede9fe','Regreso'],
    'comparando'            => ['🔘','#94a3b8','#f1f5f9','Comparando'],
    'enfriandose'           => ['🔵','#0284c7','#e0f2fe','Enfriándose'],
    'no_abierta'            => ['❌','#dc2626','#fef2f2','No abierta'],
];
