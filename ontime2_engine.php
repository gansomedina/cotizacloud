<?php
// ============================================================
//  ontime2_engine.php — Motor de scoring CotizaCloud v2.4
//  Calibración FIT en vivo + 17 buckets + momentum
// ============================================================

/**
 * Calibrar FIT con Laplace smoothing desde datos reales.
 * @param array $cal_data Array de ['sessions'=>int,'ips'=>int,'gap'=>?int,'accepted'=>bool]
 * @return array{global:float, rate_sess:array, rate_ips:array, rate_gap:array}
 */
function ot2_calibrate(array $cal_data): array {
    $total = count($cal_data);
    $cerrados = 0;
    foreach ($cal_data as $d) { if ($d['accepted']) $cerrados++; }

    if ($total < 5 || $cerrados < 3) {
        return [
            'global'    => FIT_GLOBAL,
            'rate_sess' => FIT_RATE_SESS,
            'rate_ips'  => FIT_RATE_IPS,
            'rate_gap'  => FIT_RATE_GAP,
            'calibrated'=> false,
            'total'     => $total,
            'cerrados'  => $cerrados,
        ];
    }

    $base = $cerrados / $total;
    $alpha = 5; // Laplace smoothing

    // Agrupar por bucket
    $bk_sess = []; $bk_ips = []; $bk_gap = [];
    foreach ($cal_data as $d) {
        $bs = ot2_bk_sess($d['sessions']);
        $bi = ot2_bk_ips($d['ips']);
        $bg = ot2_bk_gap($d['gap']);
        $bk_sess[$bs] = $bk_sess[$bs] ?? [0,0]; $bk_sess[$bs][0]++; if ($d['accepted']) $bk_sess[$bs][1]++;
        $bk_ips[$bi]  = $bk_ips[$bi]  ?? [0,0]; $bk_ips[$bi][0]++;  if ($d['accepted']) $bk_ips[$bi][1]++;
        $bk_gap[$bg]  = $bk_gap[$bg]  ?? [0,0]; $bk_gap[$bg][0]++;  if ($d['accepted']) $bk_gap[$bg][1]++;
    }

    $smooth = function($bk, $fallback_map) use ($base, $alpha) {
        $result = $fallback_map; // Start with fallback keys
        foreach ($bk as $k => $v) {
            $result[$k] = $v[0] > 0
                ? round(($v[1] + $alpha * $base) / ($v[0] + $alpha), 4)
                : $base;
        }
        return $result;
    };

    return [
        'global'    => round($base, 4),
        'rate_sess' => $smooth($bk_sess, FIT_RATE_SESS),
        'rate_ips'  => $smooth($bk_ips, FIT_RATE_IPS),
        'rate_gap'  => $smooth($bk_gap, FIT_RATE_GAP),
        'calibrated'=> true,
        'total'     => $total,
        'cerrados'  => $cerrados,
    ];
}

/**
 * Calcular P80 dinámico para alto importe.
 */
function ot2_p80(array $amounts, string $modo): float {
    $amounts = array_filter($amounts, fn($a) => $a > 0);
    sort($amounts);
    $n = count($amounts);
    if ($n >= 5) {
        $idx = max(0, (int)floor($n * 0.80) - 1);
        return (float)$amounts[$idx];
    }
    return (float) ot2_u('high_amount_threshold', $modo);
}

/**
 * Agregar eventos JS — idéntico a CotizaCloud Radar::_agregar_eventos
 */
function ot2_agregar_eventos(array $rows, array $intern_v): array {
    $s = [
        'opens'=>0,'closes'=>0,'coupons'=>0,'tot_views'=>0,'tot_rev'=>0,
        'loops'=>0,'promo'=>0,'scroll_any'=>0,'scroll_cls'=>0,
        'vis_max'=>0,'vis_by_page'=>[],'uniq_v'=>[],'uniq_sess'=>[],
        'v_ev'=>[],'v_price_ev'=>[],'v_sess'=>[],'v_page'=>[],'price_v'=>[],
    ];

    foreach ($rows as $r) {
        $vid = trim((string)($r['visitor_id'] ?? ''));
        if ($vid !== '' && isset($intern_v[$vid])) continue;

        $ua = (string)($r['ua'] ?? '');
        if (ot2_bot_ua($ua)) continue;

        $tipo   = (string)($r['tipo'] ?? $r['event_type'] ?? '');
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
            $s['uniq_v'][$vid] = true;
            $s['v_ev'][$vid] = ($s['v_ev'][$vid] ?? 0) + 1;
            if ($sid) $s['v_sess'][$vid][$sid] = true;
            if ($pid) $s['v_page'][$vid][$pid] = true;
        }
        if ($sid) $s['uniq_sess'][$sid] = true;

        $is_price = in_array($tipo, ['section_view_totals','section_revisit_totals','quote_price_review_loop','coupon_validate_click'], true);
        if ($is_price && $vid !== '') {
            $s['v_price_ev'][$vid] = ($s['v_price_ev'][$vid] ?? 0) + 1;
            $s['price_v'][$vid] = true;
        }
    }

    $vis_sum = array_sum($s['vis_by_page']);
    $uniq_v_count = count($s['uniq_v']);

    $max_ev = 0; $main_v = '';
    foreach ($s['v_ev'] as $vid => $cnt) { if ($cnt > $max_ev) { $max_ev = $cnt; $main_v = $vid; } }

    $sv_sess = false;
    foreach ($s['v_sess'] as $sids) { if (count($sids) >= 2) { $sv_sess = true; break; } }
    $sv_page = false;
    foreach ($s['v_page'] as $pids) { if (count($pids) >= 2) { $sv_page = true; break; } }
    $sv_price = false;
    foreach ($s['v_price_ev'] as $cnt) { if ($cnt >= 2) { $sv_price = true; break; } }

    $main_pev = 0;
    foreach ($s['v_price_ev'] as $c) { if ($c > $main_pev) $main_pev = $c; }

    return [
        'opens'    => $s['opens'],      'closes'   => $s['closes'],
        'coupons'  => $s['coupons'],    'tot_views'=> $s['tot_views'],
        'tot_rev'  => $s['tot_rev'],    'loops'    => $s['loops'],
        'promo'    => $s['promo'],      'scroll_any'=>$s['scroll_any'],
        'scroll_cls'=>$s['scroll_cls'], 'vis_max'  => $s['vis_max'],
        'vis_sum'  => $vis_sum,         'uniq_v'   => $uniq_v_count,
        'sv_price' => $sv_price,        'mv_price' => count($s['price_v']) >= 2,
        'sv_sess'  => $sv_sess,         'sv_page'  => $sv_page,
        'main_ev'  => $max_ev,          'main_v'   => $main_v,
        'main_pev' => $main_pev,
    ];
}

/**
 * Clasificar en buckets — lógica CotizaCloud v2.4 completa
 * @return array{bucket:?string, buckets:array, score:int, fit_pct:float, priority_pct:float, momentum:string, ...}
 */
function ot2_score(array $p, string $modo): array {
    $now = $p['now'];

    // Extraer parámetros
    $sessions       = $p['sessions'];
    $guest_sessions = $p['guest_sessions'];
    $guest_24h      = $p['guest_24h'];
    $guest_48h      = $p['guest_48h'];
    $guest_7d       = $p['guest_7d'];
    $views24        = $p['views24'];
    $views48        = $p['views48'];
    $span48         = $p['span48'];
    $last_ts        = $p['last_ts'];
    $gap_days       = $p['gap_days'];
    $age_days       = $p['age_days'];
    $age_hours      = $p['age_hours'];
    $accepted       = $p['accepted'];
    $uniq_ips_total = $p['uniq_ips_total'];
    $ips_120m_count = $p['ips_120m_count'];
    $ips_post_guest_count = $p['ips_post_guest_count'];
    $compare_ips_count    = $p['compare_ips_count'];
    $cot_total      = $p['cot_total'];
    $p80_threshold  = $p['p80_threshold'];
    $fit_pct        = $p['fit_pct'];
    $first_guest_ts = $p['first_guest_ts'];

    // Eventos JS
    $es = $p['es'];
    $e_opens      = (int)($es['opens'] ?? 0);
    $e_closes     = (int)($es['closes'] ?? 0);
    $e_coupons    = (int)($es['coupons'] ?? 0);
    $e_tot_views  = (int)($es['tot_views'] ?? 0);
    $e_tot_rev    = (int)($es['tot_rev'] ?? 0);
    $e_loops      = (int)($es['loops'] ?? 0);
    $e_promo      = (int)($es['promo'] ?? 0);
    $e_scroll_any = (int)($es['scroll_any'] ?? 0);
    $e_scroll_cls = (int)($es['scroll_cls'] ?? 0);
    $e_vis_max    = (int)($es['vis_max'] ?? 0);
    $e_vis_sum    = (int)($es['vis_sum'] ?? 0);
    $e_uniq_v     = (int)($es['uniq_v'] ?? 0);
    $e_sv_price   = (bool)($es['sv_price'] ?? false);
    $e_mv_price   = (bool)($es['mv_price'] ?? false);
    $e_sv_sess    = (bool)($es['sv_sess'] ?? false);
    $e_sv_page    = (bool)($es['sv_page'] ?? false);
    $e_main_ev    = (int)($es['main_ev'] ?? 0);
    $e_main_pev   = (int)($es['main_pev'] ?? 0);

    $has_tot_view = $e_tot_views > 0;
    $has_tot_rev  = $e_tot_rev > 0;
    $has_loop     = $e_loops > 0;
    $has_promo    = $e_promo > 0;

    // Price signal score
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

    // Priority
    $recency = ot2_recency_bonus($last_ts, $now);
    $priority = $fit_pct + $recency;
    $priority += min(4.0, $pss * 0.55);
    if ($e_sv_price) $priority += 0.75;
    if ($e_mv_price) $priority += 0.50;
    $priority = min(100.0, $priority);

    // ── BUCKETS ──
    $buckets = [];

    // 1. Inminente
    $sig_scroll = ($e_scroll_cls >= (int)ot2_u('imminent_signal_scroll_pct', $modo) || $e_scroll_any >= (int)ot2_u('imminent_signal_scroll_pct', $modo));
    $sig_visible = (($e_vis_max >= (int)ot2_u('imminent_signal_vis_max', $modo) || $e_vis_sum >= (int)ot2_u('imminent_signal_vis_sum', $modo)) && ($has_tot_rev || $has_loop || $e_tot_views >= 2));
    $sig_review48 = ($views48 >= (int)ot2_u('imminent_signal_views48', $modo) && $span48 >= (int)ot2_u('imminent_signal_span_h', $modo) * 3600);
    $sig_price_strong = ($has_loop || $has_tot_rev);
    $sig_same_v = ($e_sv_price && ($e_sv_sess || $e_sv_page || $e_main_ev >= 4));
    $sig_views   = ($views24 >= (int)ot2_u('imminent_signal_views24', $modo));
    $sig_ips_120 = ($ips_120m_count >= (int)ot2_u('imminent_signal_ips_120m', $modo));
    $sig_closes  = ($e_closes >= (int)ot2_u('imminent_signal_closes', $modo));
    $sig_coupon  = ($e_coupons >= (int)ot2_u('imminent_signal_coupon', $modo));
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
    if ($sig_coupon)   { $strong++; $total_sig++; }
    if ($sig_mv_price) $total_sig++;
    if (!$sig_price_strong && ($e_tot_views >= 2 || $pss >= 2.0) && $sig_scroll) $total_sig++;

    $promo_boost = ($has_promo && $strong >= 1 && $total_sig >= max(1, (int)ot2_u('imminent_min_signals', $modo) - 1));

    if (!$accepted && $last_ts >= $now - (int)ot2_u('imminent_recent_hours', $modo) * 3600 &&
        $fit_pct >= (float)ot2_u('imminent_min_fit_pct', $modo) &&
        $age_hours >= (float)ot2_u('imminent_min_age_hours', $modo) &&
        $guest_sessions >= (int)ot2_u('imminent_min_guest', $modo) &&
        ($total_sig >= (int)ot2_u('imminent_min_signals', $modo) || $promo_boost) &&
        $strong >= (int)ot2_u('imminent_min_strong', $modo)) {
        $buckets[] = 'inminente';
    }

    // 2. On Fire
    if (!$accepted && $last_ts >= $now - (int)ot2_u('onfire_recent_hours', $modo) * 3600 &&
        $sessions >= (int)ot2_u('onfire_min_sessions', $modo) &&
        ($e_scroll_cls >= (int)ot2_u('onfire_min_scroll_pct', $modo) || $e_scroll_any >= (int)ot2_u('onfire_min_scroll_pct', $modo)) &&
        ($e_vis_sum >= (int)ot2_u('onfire_min_vis_sum', $modo) || $e_vis_max >= (int)ot2_u('onfire_min_vis_max', $modo)) &&
        ($has_loop || $has_tot_rev || $pss >= 4.0) &&
        (($gap_days !== null && $gap_days >= (int)ot2_u('onfire_min_gap_days', $modo)) ||
         ($views48 >= (int)ot2_u('onfire_min_views48', $modo) && $span48 >= (int)ot2_u('onfire_min_span_h', $modo) * 3600)) &&
        ($e_sv_price || $e_mv_price || $e_main_ev >= 4 || $e_sv_sess || $e_sv_page)) {
        $buckets[] = 'onfire';
    }

    // 3. Validando precio
    $pv_read = ($e_vis_max >= (int)ot2_u('priceval_vis_hard', $modo) || $e_vis_sum >= (int)ot2_u('priceval_vis_sum', $modo) ||
        ($e_vis_max >= (int)ot2_u('priceval_vis_soft', $modo) && $e_scroll_any >= (int)ot2_u('priceval_scroll_soft', $modo)));
    if (!$accepted && $last_ts >= $now - (int)ot2_u('priceval_recent_hours', $modo) * 3600 &&
        $guest_sessions >= 1 &&
        ($guest_sessions >= 2 || $e_sv_price || $e_mv_price) &&
        ($pv_read || $e_sv_price || $e_mv_price || $e_main_pev >= 2) &&
        ($has_loop || $has_tot_rev || $e_tot_views >= 2 || $e_sv_price || $e_mv_price)) {
        $buckets[] = 'validando_precio';
    }

    // 4. Predicción alta (ventana fija 30d, sin ciclo_venta en WP)
    $predict_window = (int)ot2_u('predict_recent_days', $modo);
    $predict_alive_days = max(3, min(30, (int)ceil($predict_window / 3)));
    if (!$accepted && $fit_pct >= (float)ot2_u('predict_min_fit_pct', $modo) &&
        $age_days <= $predict_window &&
        $last_ts >= $now - $predict_alive_days * 86400) {
        $buckets[] = 'prediccion_alta';
    }

    // 5. Decisión activa
    if (!$accepted && $views48 >= (int)ot2_u('decision_min_views48', $modo) &&
        $span48 >= (int)ot2_u('decision_min_span_h', $modo) * 3600 &&
        $last_ts >= $now - (int)ot2_u('decision_window_h', $modo) * 3600) {
        $buckets[] = 'decision_activa';
    }

    // 6. Re-enganche (caliente vs normal)
    $reeng_interest = ($guest_24h >= (int)ot2_u('reeng_min_guest_24h', $modo) ||
        $views24 >= (int)ot2_u('reeng_min_views24', $modo) ||
        $has_tot_view || $has_tot_rev || $has_loop || $pss >= 2.0 || $e_sv_price);
    $reeng_hot = ($has_tot_rev || $has_loop || $e_sv_price || $e_mv_price || $e_coupons > 0);
    if (!$accepted && $gap_days !== null && $gap_days >= (int)ot2_u('reeng_gap_days', $modo) &&
        $last_ts >= $now - (int)ot2_u('reeng_recent_hours', $modo) * 3600 && $reeng_interest) {
        $buckets[] = $reeng_hot ? 're_enganche_caliente' : 're_enganche';
    }

    // 7. Multi-persona
    $multip_boost = ($has_tot_rev || $has_loop || $pss >= 3.0 || $e_opens >= 2 ||
        $e_closes >= 1 || $e_vis_max >= (int)ot2_u('multip_boost_vis_max', $modo) || $e_mv_price || $e_uniq_v >= 2);
    if (!$accepted && $last_ts >= $now - (int)ot2_u('multip_recent_hours', $modo) * 3600 &&
        $guest_sessions >= (int)ot2_u('multip_min_guest_total', $modo) &&
        (($e_uniq_v >= 2 && ($e_mv_price || $e_sv_sess)) ||
         $ips_post_guest_count >= (int)ot2_u('multip_min_ips_post_guest', $modo) ||
         ($ips_post_guest_count >= max(2, (int)ot2_u('multip_min_ips_post_guest', $modo) - 1) && $multip_boost))) {
        $buckets[] = 'multi_persona';
    }

    // 8. Revisión profunda
    if (!$accepted && $views48 >= (int)ot2_u('deep_min_views48', $modo) &&
        $span48 >= (int)ot2_u('deep_min_span_h', $modo) * 3600 &&
        $last_ts >= $now - (int)ot2_u('deep_recent_hours', $modo) * 3600 &&
        $guest_48h >= (int)ot2_u('deep_min_guest_48h', $modo) &&
        ($e_vis_max >= (int)ot2_u('deep_min_vis_max', $modo) || $e_vis_sum >= (int)ot2_u('deep_min_vis_sum', $modo)) &&
        ($has_tot_view || $has_tot_rev || $has_loop || $pss >= 2.5 || $e_sv_price)) {
        $buckets[] = 'revision_profunda';
    }

    // 9. Hesitación
    $hes_between = ($last_ts < $now - (int)ot2_u('hes_last_min_hours', $modo) * 3600 &&
        $last_ts >= $now - (int)ot2_u('hes_last_max_days', $modo) * 86400);
    if (!$accepted && $guest_7d >= (int)ot2_u('hes_min_guest_7d', $modo) && $hes_between &&
        $uniq_ips_total <= (int)ot2_u('hes_max_ips_total', $modo) &&
        $span48 < (int)ot2_u('hes_max_span_h', $modo) * 3600 &&
        ($has_tot_view || $has_tot_rev || $has_loop || $e_coupons > 0 || $pss >= 2.0 || $e_sv_price)) {
        $buckets[] = 'hesitacion';
    }

    // 10. Sobre-análisis
    $over_fit_ok = ($fit_pct < (float)ot2_u('over_max_fit_pct', $modo) ||
        ($e_sv_price && !($e_uniq_v >= 2) && $e_main_ev >= 6));
    if (!$accepted && $sessions >= (int)ot2_u('over_min_sessions', $modo) &&
        $guest_sessions >= (int)ot2_u('over_min_guest', $modo) &&
        $age_days >= (int)ot2_u('over_min_age_days', $modo) &&
        $last_ts >= $now - (int)ot2_u('over_recent_days', $modo) * 86400 &&
        $ips_post_guest_count <= (int)ot2_u('over_max_ips_post_guest', $modo) && $over_fit_ok) {
        $buckets[] = 'sobre_analisis';
    }

    // 11. Alto importe (P80 dinámico)
    if (!$accepted && $cot_total >= $p80_threshold &&
        $last_ts >= $now - (int)ot2_u('high_amount_recent_hours', $modo) * 3600) {
        $buckets[] = 'alto_importe';
    }

    // 12. Vistas múltiples
    $multi_ips_24h = $p['multi_ips_24h'] ?? 0;
    if (!$accepted && $last_ts >= $now - (int)ot2_u('multi_recent_hours', $modo) * 3600 &&
        ($multi_ips_24h >= (int)ot2_u('multi_min_ips', $modo) || $views24 >= (int)ot2_u('multi_min_views24', $modo))) {
        $buckets[] = 'vistas_multiples';
    }

    // 13-16. Exclusivos
    $is_revive  = (!$accepted && $gap_days !== null && $gap_days >= (int)ot2_u('revive_gap_days', $modo) && $last_ts >= $now - (int)ot2_u('revive_recent_hours', $modo) * 3600);
    $is_return4 = (!$accepted && $gap_days !== null && $gap_days >= (int)ot2_u('return_gap_days', $modo) && $last_ts >= $now - (int)ot2_u('return_recent_hours', $modo) * 3600);
    $has_any_engagement = ($e_opens > 0 || $e_scroll_any > 0 || $e_vis_max > 0);
    $is_compare = (!$accepted && $compare_ips_count >= (int)ot2_u('compare_min_ips', $modo) && $last_ts >= $now - (int)ot2_u('compare_window_h', $modo) * 3600 && $has_any_engagement);
    $is_cooling = (!$accepted && $sessions >= (int)ot2_u('cooling_min_sessions', $modo) &&
        $last_ts < $now - (int)ot2_u('cooling_min_silence_h', $modo) * 3600 &&
        $last_ts >= $now - (int)ot2_u('cooling_days', $modo) * 86400 &&
        ($e_opens > 0 || $e_scroll_any > 0 || $e_vis_max > 0));

    if ($is_revive)       $buckets[] = 'revivio';
    elseif ($is_return4)  $buckets[] = 'regreso';
    elseif ($is_compare)  $buckets[] = 'comparando';
    elseif ($is_cooling)  $buckets[] = 'enfriandose';

    // 17. Probable cierre (CROSS-BUCKET)
    $pc_qualifying = ['onfire','inminente','validando_precio','decision_activa',
                      're_enganche_caliente','prediccion_alta','multi_persona','alto_importe'];
    $pc_source = null;
    foreach ($pc_qualifying as $qb) {
        if (in_array($qb, $buckets, true)) { $pc_source = $qb; break; }
    }

    if ($pc_source !== null && !$accepted && $last_ts >= $now - 72 * 3600 && $sessions >= 2) {
        $cat_engagement  = ($e_scroll_cls >= 50 || $e_scroll_any >= 50 || $e_vis_max >= 8 || $e_vis_sum >= 15 || ($has_tot_view && $sessions >= 2));
        $cat_precio      = ($has_tot_rev || $has_loop || $e_coupons > 0 || $e_sv_price || $e_mv_price || $pss >= 2.0);
        $cat_persistencia = ($sessions >= 2 || ($gap_days !== null && $gap_days >= 1));
        $cat_social       = ($e_uniq_v >= 2 || $ips_post_guest_count >= 2 || $e_mv_price);

        $cat_count = (int)$cat_engagement + (int)$cat_precio + (int)$cat_persistencia + (int)$cat_social;
        $has_strong_cat = ($cat_precio || $cat_engagement);

        if ($cat_count >= 2 && $has_strong_cat) {
            $buckets[] = 'probable_cierre';
        }
    }

    // ── Bucket principal ──
    $bucket_main = null;
    foreach (BUCKET_PRIORITY as $b) {
        if (in_array($b, $buckets, true)) { $bucket_main = $b; break; }
    }

    // ── Icons ──
    $icons = [];
    if ($e_coupons > 0) $icons['coupon'] = true;
    if ($es['promo'] > 0) $icons['promo'] = true;
    if ($pss >= 3.0) $icons['price'] = true;
    if ($e_sv_price) $icons['sv_price'] = true;
    if ($e_mv_price) $icons['mv_price'] = true;

    // ── Momentum ──
    $momentum = ot2_momentum($bucket_main, $last_ts, $now, $modo);

    // ── Cooling reason ──
    $cooling_price_touched = ($has_tot_view || $has_tot_rev || $has_loop || $e_coupons > 0 || $pss >= 2.0 || $e_sv_price || $e_mv_price);

    return [
        'score'        => (int) round($priority),
        'fit_pct'      => round($fit_pct, 2),
        'priority_pct' => round($priority, 2),
        'bucket'       => $bucket_main,
        'buckets'      => $buckets,
        'momentum'     => $momentum,
        'pc_source'    => $pc_source,
        'icons'        => $icons,
        'pss'          => round($pss, 2),
        'cooling_price_touched' => $cooling_price_touched,
        'cooling_reason' => $cooling_price_touched ? '💸 con precio' : '🧊 sin precio',
        'debug' => [
            'sessions'=>$sessions,'uniq_ips'=>$uniq_ips_total,
            'gap_days'=>$gap_days,'guest'=>$guest_sessions,
            'views24'=>$views24,'views48'=>$views48,
            'span48h'=>round($span48/3600,1).'h','pss'=>round($pss,2),
            'ev_uniq_v'=>$e_uniq_v,'modo'=>$modo,'momentum'=>$momentum,
            'scroll_cls'=>$e_scroll_cls,'scroll_any'=>$e_scroll_any,
            'vis_max'=>$e_vis_max,'vis_sum'=>$e_vis_sum,
            'ips_post_guest'=>$ips_post_guest_count,
        ],
    ];
}
