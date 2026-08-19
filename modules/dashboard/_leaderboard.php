<?php
// ============================================================
//  Dashboard partial — Ranking del equipo (leaderboard)
//
//  Extraído de dashboard/index.php SIN cambiarle una línea: el panel del
//  supervisor multi-sucursal necesita exactamente esto —barras por dimensión,
//  tips y la mesa de cada asesor incrustada— y copiarlo habría garantizado que
//  las dos versiones se separaran.
//
//  Depende del scope de quien lo incluye: $es_admin_dash, $equipo_scores,
//  $MESA_SHARED, $MESA_BLOQUES, $MESA_ASSETS, $empresa_id, $trial.
// ============================================================
defined('COTIZAAPP') or die;
?>
<?php if ($es_admin_dash && count($equipo_scores) > 0): ?>
  <div class="lb">
    <div class="lb-head" onclick="var b=document.getElementById('lb-body');b.classList.toggle('lb-collapsed');this.querySelector('.lb-chevron').classList.toggle('lb-chevron-open')" style="cursor:pointer;user-select:none">
      <div style="flex:1">
        <div class="lb-title">Ranking del equipo</div>
        <div class="lb-sub">15 días · auto-ajustable · <?= count($equipo_scores) ?> miembros</div>
      </div>
      <svg class="lb-chevron lb-chevron-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--t3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      <button onclick="event.stopPropagation();document.getElementById('lb-info').classList.toggle('lb-info-open')" style="background:none;border:1px solid var(--border);border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--t3);font:600 12px var(--body);flex-shrink:0;margin-left:6px" title="¿Cómo funciona?">?</button>
    </div>
    <div id="lb-body">
    <div id="lb-info" class="lb-info">
      <div class="lb-info-inner">
        <b>¿Qué mide este ranking?</b>
        <p>Algoritmo APC v5.1 — 15 días rolling, 100% auto-ajustable:</p>
        <ul>
          <li><b>Activación (13%)</b> — ¿Las cotizaciones llegan al cliente? Una o más sin abrir a los 5 días tumba la operativa; las "dormidas" (que el cliente vio pero no volvió a abrir en 7+ días) restan en proporción directa a tus vistas.</li>
          <li><b>Engagement (17%)</b> — Capa de penalizaciones: ventas sin cobrar (×1/tasa cierre, fuerte), descuentos (×tasa cierre, suave), ventas por debajo del promedio de la empresa.</li>
          <li><b>Seguimiento (25%) = tu mesa</b> — Con la Mesa de Trabajo activa, ESTA dimensión ES tu mesa (lista completa): atiende (feedback 👍👎 <b>+</b> postura) el <b>≥80%</b> = completo · <b>50–80%</b> = medio (cuenta la mitad) · <b>menos de 50%</b> = no cuenta. <span style="opacity:.7">(Empresas sin mesa: se mide el feedback en tus cotizaciones calientes.)</span></li>
          <li><b>Radar Health (10%)</b> — ¿Cuidas a los clientes con interés? Cuando una cotización se pone caliente, el cliente la está viendo con interés. Mide cuántas de esas se te mueren — el cliente desaparece del Radar sin que cierres. Entre menos sueltas, más alto.</li>
          <li><b>Conversión (35%)</b> — ¿Cierras ventas? Tasa de cierre vs empresa, calidad (cerrar ventas difíciles vale más), tendencia de volumen (ventas actuales vs período anterior), consistencia semanal.</li>
        </ul>
        <p><b>Auto-ajuste:</b> Todas las penalizaciones escalan con la tasa de cierre de la empresa. Sin valores fijos — cada empresa tiene su propia escala.</p>
        <p><b>Score final:</b> Los pesos del score se ajustan automáticamente: con pocos vendedores domina el proporcional. Con equipo grande, el percentil gana peso. La tendencia (momentum) escala con la tasa de cierre. Flechas: ↑ mejorando, → estable, ↓ decayendo.</p>
        <p><b>Niveles:</b> Top (86-100) · Activo (61-85) · Regular (31-60) · Bajo (0-30) · Nuevo (primeros días).</p>
        <p style="color:var(--t3);font-style:italic;margin-bottom:0">Nota: Índice algorítmico basado en datos de uso de la plataforma. Referencia de productividad comercial, no evaluación personal.</p>
      </div>
    </div>
    <?php if (!empty($MESA_SHARED)) { echo $MESA_SHARED . ($MESA_ASSETS ?? ''); $MESA_EMITIDO = true; } ?>
    <?php
    // Pesos de la mezcla final — son a nivel EMPRESA (dependen del tamaño del
    // equipo y su tasa de cierre, iguales para todos los asesores) y NO se
    // persisten por fila. Se recalculan aquí una sola vez con los totales del
    // leaderboard para no mostrar valores fijos falsos (antes el panel pintaba
    // ×90/10/0 siempre). Es la estimación del panel; el número exacto vive en
    // ActividadScore (_benchmarks + blend). Misma fórmula que el score.
    $team_n = count($equipo_scores);
    $lb_vistas = 0; $lb_cierres = 0;
    foreach ($equipo_scores as $e2) { $lb_vistas += (int)($e2['cot_vistas'] ?? 0); $lb_cierres += (int)($e2['conversiones'] ?? 0); }
    $lb_cr   = $lb_vistas >= 5 ? min($lb_cierres / max($lb_vistas, 1), 0.90) : 0.15;
    $lb_cr   = max($lb_cr, 0.01);
    $w_pct_lb  = $team_n >= 3 ? min(($team_n - 2) / ($team_n + 18), 0.25) : 0.0;
    $w_mom_lb  = (1.0 - $w_pct_lb) * $lb_cr;
    $w_prop_lb = 1.0 - $w_pct_lb - $w_mom_lb;
    // Enteros a mostrar: redondear 2 y derivar el 3ro → SIEMPRE suman 100% (antes
    // los 3 round() independientes daban 99/101). El '≈' avisa que el close_rate
    // es estimado del leaderboard, no el benchmark exacto de ActividadScore.
    $pct_disp  = (int)round($w_pct_lb * 100);
    $mom_disp  = (int)round($w_mom_lb * 100);
    $prop_disp = 100 - $pct_disp - $mom_disp;
    $rank = 0;
    foreach ($equipo_scores as $es):
      $rank++;
      $es_score = (int)$es['score'];
      $es_color = match($es['nivel']) {
          'top' => '#2563eb', 'activo' => '#16a34a', 'regular' => '#d97706', 'nuevo' => '#6b7280', default => '#dc2626'
      };
      $es_bg = match($es['nivel']) {
          'top' => '#eff6ff', 'activo' => '#f0fdf4', 'regular' => '#fffbeb', 'nuevo' => '#f9fafb', default => '#fef2f2'
      };
      $es_lbl = match($es['nivel']) {
          'top' => 'Top', 'activo' => 'Activo', 'regular' => 'Regular', 'nuevo' => 'Nuevo', default => 'Bajo'
      };
      $es_ini = strtoupper(mb_substr($es['nombre'], 0, 1));
      $es_av_bg = $es['rol'] === 'admin' ? 'var(--g)' : '#64748b';
      $es_mom = (float)$es['momentum'];
      $es_arrow = $es_mom >= 1.05 ? '↑' : ($es_mom <= 0.95 ? '↓' : '→');
      $es_mom_c = $es_mom >= 1.05 ? '#16a34a' : ($es_mom <= 0.95 ? '#dc2626' : '#9ca3af');
      $rank_cls = $rank <= 3 ? "lb-rank-{$rank}" : '';
      $es_rt = null;
      try { $es_rt = RitmoTip::paraTermometro(RitmoReporte::expediente(EMPRESA_ID, (int)($es['usuario_id'] ?? 0))); } catch (Throwable $e) {}
      if (!$es_rt) $es_rt = RitmoTip::desdeScore($es);
      $es_diag = ($es_rt && trim($es_rt['texto']) !== '') ? $es_rt['texto'] : ActividadScore::diagnostico($es, $diag_ctx ?? null);
    ?>
    <div class="lb-row">
      <div class="lb-rank <?= $rank_cls ?>"><?= $rank ?></div>
      <div class="lb-av" style="background:<?= $es_av_bg ?>"><?= e($es_ini) ?></div>
      <div class="lb-name">
        <?= e($es['nombre']) ?>
        <div class="lb-diag"><?= e($es_diag) ?></div>
        <?php /* Segundo tip (números) OCULTO — demasiada info. Cálculo intacto: */
              $es_num = trim(ActividadScore::diagnostico_numeros($es, $diag_ctx ?? null)); ?>
        <?php if (Auth::es_superadmin() && $es['nivel'] !== 'nuevo'):
          $lb_act = min(100, round((float)($es['s_activacion'] ?? 0) * 100));
          $lb_eng = min(100, round((float)($es['s_engagement'] ?? 0) * 100));
          $lb_seg = min(100, round((float)($es['s_seguimiento'] ?? 0) * 100));
          $lb_hlt = min(100, round((float)($es['s_radar_health'] ?? 0) * 100));
          $lb_con = min(100, round((float)($es['s_conversion'] ?? 0) * 100));
        ?>
        <div class="thermo-bars" style="margin-top:4px;max-width:220px">
          <?php foreach ([['A',$lb_act],['E',$lb_eng],['S',$lb_seg],['P',$lb_hlt],['C',$lb_con]] as [$l,$v]): ?>
          <div style="flex:1"><div class="thermo-bar"><div class="thermo-bar-fill" style="width:<?= $v ?>%;background:<?= $v >= 60 ? '#16a34a' : ($v >= 30 ? '#d97706' : '#dc2626') ?>"></div></div><div class="thermo-bar-lbl"><?= $l ?></div></div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php if ($es['nivel'] !== 'nuevo'): ?>
      <div class="lb-stats">
        <div class="lb-stat"><span class="lb-stat-val"><?= (int)($es['cot_vistas'] ?? 0) ?>/<?= (int)($es['cot_asignadas'] ?? 0) ?></span><span class="lb-stat-lbl">Abiertas</span></div>
        <div class="lb-stat"><span class="lb-stat-val"><?= (int)$es['conversiones'] ?></span><span class="lb-stat-lbl">Cierres</span></div>
        <div class="lb-stat"><span class="lb-stat-val"><?= (int)($es['cierres_bucket'] ?? 0) ?></span><span class="lb-stat-lbl">Radar</span></div>
        <div class="lb-stat"><span class="lb-stat-val" style="color:<?= (int)($es['cot_dormidas'] ?? 0) > 0 ? 'var(--danger)' : 'inherit' ?>"><?= (int)($es['cot_dormidas'] ?? 0) ?></span><span class="lb-stat-lbl">Dormidas</span></div>
      </div>
      <div class="lb-score">
        <span class="lb-score-num" style="color:<?= $es_color ?>"><?= $es_score ?></span>
        <span style="color:<?= $es_mom_c ?>;font-size:12px"><?= $es_arrow ?></span>
        <span class="lb-nivel" style="color:<?= $es_color ?>;background:<?= $es_bg ?>"><?= $es_lbl ?></span>
      </div>
      <?php else: ?>
      <div class="lb-stats"></div>
      <div class="lb-score">
        <span class="lb-nivel" style="color:<?= $es_color ?>;background:<?= $es_bg ?>"><?= $es_lbl ?></span>
      </div>
      <?php endif; ?>
    </div>
    <?php $mesa_row_uid = (int)($es['usuario_id'] ?? 0);
    if (!empty($MESA_BLOQUES[$mesa_row_uid])) { echo $MESA_BLOQUES[$mesa_row_uid]; unset($MESA_BLOQUES[$mesa_row_uid]); } ?>
    <?php if (Auth::es_superadmin()): ?>
    <!-- Debug expandible por vendedor (solo superadmin) -->
    <div style="border-top:1px dashed var(--border);padding:2px 14px 2px 52px">
      <span onclick="var p=this.nextElementSibling;p.style.display=p.style.display==='none'?'block':'none'" style="font:600 10px var(--body);color:var(--t3);cursor:pointer;letter-spacing:.05em;text-transform:uppercase;opacity:.6">▶ debug</span>
      <div style="display:none;padding:6px 0;font:400 11px var(--num);color:var(--t2);line-height:1.7">
        <div class="dbg-row"><span class="dbg-lbl">Activación (13%)</span><span class="dbg-val"><?= round(($es['s_activacion'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">  operativa</span><span class="dbg-val"><?= round(($es['s_activacion_op'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">  pen dormidas</span><span class="dbg-val dbg-neg"><?= ($es['pen_dormidas'] ?? 0) > 0 ? '-'.round(($es['pen_dormidas'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">  tips <?= (int)($es['dias_lectura'] ?? 0) ?>/<?= (int)($es['dias_activos_feature'] ?? $es['dias_activos'] ?? 0) ?>d</span><span class="dbg-val"><?= round(($es['tips_score'] ?? 1) * 100) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Engagement (17%)</span><span class="dbg-val"><?= round(($es['s_engagement'] ?? 0) * 100, 1) ?>%</span></div>
        <?php
        $dbg_seg_val = round(($es['s_seguimiento'] ?? 0) * 100, 1);
        $dbg_why_s   = (float)($es['radar_why_score'] ?? 1);
        $dbg_why_exp = (int)($es['calientes_exploradas'] ?? 0);
        $dbg_cal_tot = (int)($es['cots_calientes'] ?? $es['radar_benchmark'] ?? 0);
        ?>
        <div class="dbg-row"><span class="dbg-lbl">  pen sin pago</span><span class="dbg-val dbg-neg"><?= ($es['eng_pen_sin_pago'] ?? 0) > 0 ? '-'.round(($es['eng_pen_sin_pago'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">  pen descuento</span><span class="dbg-val dbg-neg"><?= ($es['eng_pen_descuento'] ?? 0) > 0 ? '-'.round(($es['eng_pen_descuento'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">  pen bajo benchmark</span><span class="dbg-val dbg-neg"><?php $epbb = $es['eng_pen_bajo_benchmark'] ?? 0; if ($epbb > 0) { echo '-'.round($epbb * 100, 1).'% ('.($es['ventas_periodo'] ?? '?').' vs '.($es['bench_ventas'] ?? '?').')'; } else echo '—'; ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Seguimiento (25%) = mesa</span><span class="dbg-val"><?= round(($es['s_seguimiento'] ?? 0) * 100, 1) ?>%</span></div>
        <?php if (($es['s_mesa'] ?? null) !== null): ?>
        <?php
          $_mp = (int)($es['mesa_pedidas'] ?? 0); $_ma = (int)($es['mesa_atendidas'] ?? 0);
          $_mcov = $_mp > 0 ? round(100 * $_ma / $_mp) : 100;
          $_sm = (float)$es['s_mesa'];
          $_mtag = $_sm >= 1.0 ? '✓ completo (100%)' : ($_sm >= 0.5 ? '~ medio (50%)' : '✗ bajo (0%)');
        ?>
        <div class="dbg-row"><span class="dbg-lbl">  mesa: <?= $_ma ?>/<?= $_mp ?> atendidas (<?= $_mcov ?>%)</span><span class="dbg-val <?= $_sm > 0 ? '' : 'dbg-neg' ?>"><?= $_mtag ?></span></div>
        <?php endif; ?>
        <div class="dbg-row"><span class="dbg-lbl">Radar Health (10%)</span><span class="dbg-val"><?= round(($es['s_radar_health'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">  calientes / muertas</span><span class="dbg-val"><?= (int)($es['health_up'] ?? $es['transiciones_up'] ?? 0) ?> / <?= (int)($es['health_down'] ?? $es['senales_ignoradas'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">  tasa muerte caliente</span><span class="dbg-val dbg-neg"><?php $rhc=(int)($es['health_up']??$es['transiciones_up']??0);$rhm=(int)($es['health_down']??$es['senales_ignoradas']??0);echo $rhc>0?round($rhm/$rhc*100,1).'%':'—';?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Conversión (35%)</span><span class="dbg-val"><?= round(($es['s_conversion'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Penalizaciones</span><span class="dbg-val dbg-neg"><?= ($es['penalizaciones'] ?? 0) > 0 ? '-'.round(($es['penalizaciones'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Bonuses</span><span class="dbg-val"><?= round(($es['bonuses'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Bonus ticket</span><span class="dbg-val"><?= (int)($es['bonus_ticket'] ?? 0) > 0 ? '+'.($es['bonus_ticket']).' pts ('.(int)($es['bonus_ticket_ventas'] ?? 0).' venta'.((int)($es['bonus_ticket_ventas'] ?? 0) != 1 ? 's' : '').')' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Bonus cierre</span><span class="dbg-val"><?= (int)($es['bonus_cierre'] ?? 0) > 0 ? '+'.($es['bonus_cierre']).' pts' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Castigo seguimiento</span><span class="dbg-val"><?= (int)($es['castigo_seguimiento'] ?? 0) > 0 ? '-'.($es['castigo_seguimiento']).' pts ('.(int)($es['mesa_dias_vencidos'] ?? 0).'d vencidos en el período)' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Proporcional (×≈<?= $prop_disp ?>%)</span><span class="dbg-val"><?= round(($es['tasa_gestion'] ?? 0) * 100, 1) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Momentum (×≈<?= $mom_disp ?>%)</span><span class="dbg-val"><?= number_format($es['momentum'] ?? 1, 2) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Percentil (×≈<?= $pct_disp ?>%)</span><span class="dbg-val"><?= round(($es['percentil'] ?? 0) * 100) ?>%</span></div>
        <div class="dbg-row"><span class="dbg-lbl">Asig / Vistas / Cierres</span><span class="dbg-val"><?= (int)($es['cot_asignadas'] ?? 0) ?> / <?= (int)($es['cot_vistas'] ?? 0) ?> / <?= (int)($es['conversiones'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Dormidas 7d</span><span class="dbg-val"><?= (int)($es['cot_dormidas'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">No abiertas 5d</span><span class="dbg-val dbg-neg"><?= (int)($es['no_abiertas_5d'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Pen no abiertas</span><span class="dbg-val dbg-neg"><?= ($es['pen_no_abiertas'] ?? 0) > 0 ? '-'.round(($es['pen_no_abiertas'] ?? 0) * 100, 1).'%' : '—' ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Cierres radar / Sin dto</span><span class="dbg-val"><?= (int)($es['cierres_bucket'] ?? 0) ?> / <?= (int)($es['cierres_sin_dto'] ?? 0) ?></span></div>
        <div class="dbg-row"><span class="dbg-lbl">Calientes / con feedback</span><span class="dbg-val"><?= (int)($es['radar_benchmark'] ?? 0) ?> / <?= (int)($es['radar_views'] ?? 0) ?></span></div>
      </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
    <?php if (!empty($MESA_BLOQUES)): // asesores con cartera activa pero sin fila de score ?>
      <?php foreach ($MESA_BLOQUES as $mb_uid => $mb): ?>
        <div style="padding:6px 14px 0 52px;font-size:11px;color:#a8a8a2">Asesor sin score en el período — su mesa:</div>
        <?php echo $mb; ?>
      <?php endforeach; $MESA_BLOQUES = []; ?>
    <?php endif; ?>
    </div><!-- /lb-body -->
  </div>
<?php endif; ?>
