<?php
// ============================================================
//  CotizaCloud — modules/config/suscripcion.php
//  Tab "Suscripción" en Configuración
//  Muestra plan actual, tarjeta guardada, botones de upgrade,
//  historial de pagos. Oculto en app nativa iOS.
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;

$trial = trial_info($empresa_id);
$empresa = DB::row("SELECT email, nombre FROM empresas WHERE id=?", [$empresa_id]);
$sub = DB::row("SELECT * FROM suscripciones WHERE empresa_id=?", [$empresa_id]);
$pagos = DB::query(
    "SELECT * FROM pagos_suscripcion WHERE empresa_id=? ORDER BY fecha_pago DESC LIMIT 20",
    [$empresa_id]
);
$precios = MercadoPago::precios();
$csrf = $_SESSION[CSRF_TOKEN_NAME] ?? '';

$tiene_auto_renew = $sub && $sub['estado'] === 'active' && !$sub['cancel_al_vencer'] && !empty($sub['mp_customer_id']);
$cobro_fallido = $sub && (int)($sub['intentos_cobro'] ?? 0) > 0 && !empty($sub['ultimo_error']);

// Trial de 30 días en curso — flag EXPLÍCITO de trial_info (es_trial del
// registro): un cliente de pago manual/transferencia jamás ve este banner
$trial_en_curso = !empty($trial['trial_activo']) && $trial['dias_restantes'] !== null;
?>
<?php if ($trial_en_curso): ?>
<div style="margin-bottom:16px;padding:14px 18px;background:var(--amb-bg,#fef6e7);border:1px solid #fcd34d;border-radius:10px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
  <span style="font-size:22px">🎁</span>
  <div style="flex:1;min-width:220px">
    <div style="font-weight:800;font-size:14px">Tu prueba de <?= e($trial['plan_label']) ?> termina <?= (int)$trial['dias_restantes'] === 0 ? 'HOY' : 'en ' . (int)$trial['dias_restantes'] . ' día' . ((int)$trial['dias_restantes'] === 1 ? '' : 's') ?></div>
    <div style="font-size:12.5px;color:#92400e">Activa tu plan abajo para no perderlo — tus datos y cotizaciones se conservan igual.</div>
  </div>
</div>
<?php endif; ?>

<div class="sec">
  <div class="sec-lbl">Tu plan actual</div>
  <div class="card">
    <div style="padding:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
      <div style="flex:1;min-width:200px">
        <div style="font:700 22px var(--body);color:var(--text);display:flex;align-items:center;gap:10px">
          <?php
          $plan_color = match($trial['plan']) {
              'business' => 'var(--blue)',
              'pro' => 'var(--g)',
              'lite' => '#92400e',
              default => 'var(--amb)',
          };
          ?>
          <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?= $plan_color ?>"></span>
          <?= $trial['plan_label'] ?>
        </div>
        <?php if ($trial['es_free']): ?>
          <div style="font:400 13px var(--body);color:var(--t3);margin-top:4px">
            <?= $trial['usadas'] ?>/<?= TRIAL_LIMIT ?> cotizaciones usadas
          </div>
        <?php elseif ($trial['es_pagado']): ?>
          <div style="font:400 13px var(--body);color:var(--t3);margin-top:4px">
            <?php if ($trial['plan_vence']): ?>
              Vence: <?= date('d/m/Y', strtotime($trial['plan_vence'])) ?>
              <?php if ($trial['dias_restantes'] !== null): ?>
                (<?= $trial['dias_restantes'] ?> día<?= $trial['dias_restantes']!=1?'s':'' ?>)
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <?php if ($tiene_auto_renew): ?>
            <div style="font:500 12px var(--body);color:var(--g);margin-top:4px">
              Renovación automática activa (<?= $sub['ciclo'] ?>)
            </div>
          <?php elseif ($sub && $sub['cancel_al_vencer']): ?>
            <div style="font:500 12px var(--body);color:var(--amb);margin-top:4px">
              Se cancelará al vencer — no se renovará
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($tiene_auto_renew && !empty($sub['card_last4'])): ?>
    <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <svg width="28" height="20" viewBox="0 0 28 20" fill="none" style="flex-shrink:0"><rect width="28" height="20" rx="3" fill="var(--bg)" stroke="var(--border)"/><rect x="3" y="6" width="6" height="4" rx="1" fill="var(--amb)"/><rect x="3" y="13" width="18" height="1.5" fill="var(--t3)"/></svg>
        <div>
          <div style="font:500 13px var(--body);color:var(--text)">
            <?= strtoupper(htmlspecialchars($sub['card_brand'] ?: 'Tarjeta')) ?> ****<?= htmlspecialchars($sub['card_last4']) ?>
          </div>
          <?php if ($sub['card_exp_month'] && $sub['card_exp_year']): ?>
            <div style="font:400 11px var(--body);color:var(--t3)">
              Vence <?= str_pad($sub['card_exp_month'], 2, '0', STR_PAD_LEFT) ?>/<?= $sub['card_exp_year'] ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($sub['proximo_cobro']): ?>
      <div style="font:400 12px var(--body);color:var(--t3)">
        Próximo cobro: <span style="color:var(--text);font-weight:500"><?= date('d/m/Y', strtotime($sub['proximo_cobro'])) ?></span>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($cobro_fallido): ?>
    <div style="padding:12px 20px;background:color-mix(in srgb, var(--danger) 8%, white);border-top:1px solid var(--danger);display:flex;align-items:flex-start;gap:10px">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div style="font:400 12px var(--body);color:var(--danger);line-height:1.5;flex:1">
        <strong>No pudimos cobrar tu tarjeta</strong><?= $sub['intentos_cobro'] > 1 ? ' (intento ' . $sub['intentos_cobro'] . ' de 3)' : '' ?>.
        Actualiza tu método de pago para no perder tu plan.
      </div>
    </div>
    <?php endif; ?>

    <?php
    $grace = DB::val("SELECT grace_hasta FROM empresas WHERE id=?", [$empresa_id]);
    if ($grace && $grace >= date('Y-m-d')): ?>
    <div style="padding:12px 20px;background:var(--amb-bg);border-top:1px solid var(--amb);display:flex;align-items:center;gap:8px">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--amb)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span style="font:500 13px var(--body);color:var(--amb-dark)">
        Período de gracia activo — tu pago no se pudo procesar. Tienes hasta el <?= date('d/m/Y', strtotime($grace)) ?> para regularizar.
      </span>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php
$mostrar_planes = $trial['es_free']
    || ($trial['es_pagado'] && (!$sub || $sub['cancel_al_vencer'] || empty($sub['mp_customer_id'])))
    || $cobro_fallido;
$titulo_seccion = $cobro_fallido ? 'Actualizar método de pago' : 'Actualizar plan';
?>

<?php if ($mostrar_planes): ?>
<div class="sec">
  <div class="sec-lbl"><?= $titulo_seccion ?></div>

  <?php if (!$cobro_fallido): ?>
  <div style="padding:12px 14px;background:var(--amb-bg);border:1px solid var(--amb);border-radius:var(--r-sm);margin-bottom:16px;display:flex;align-items:flex-start;gap:10px">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--amb-dark)" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div style="font:400 12px var(--body);color:var(--amb-dark);line-height:1.5">
      <strong>Si tu banco rechaza el cargo</strong>, llama al número al reverso de tu tarjeta y pide <em>autorizar cargos recurrentes de MercadoPago</em>. Muchos bancos bloquean el primer cobro por defecto como protección.
    </div>
  </div>
  <?php endif; ?>

  <?php
  $planes_grid = [
    'lite' => [
      'name'     => 'Lite',
      'color'    => '#92400e',
      'tagline'  => 'Para empezar — lo esencial',
      'features' => 'Cotizaciones ilimitadas, clientes, ventas y seguimiento de visitas',
    ],
    'pro' => [
      'name'     => 'Pro',
      'color'    => 'var(--g)',
      'tagline'  => 'Para tu equipo de ventas',
      'features' => 'Todo lo de Lite + tu equipo (usuarios ilimitados, uso justo), Radar completo, reportes, costos y app móvil',
    ],
    'business' => [
      'name'     => 'Business',
      'color'    => 'var(--blue)',
      'tagline'  => 'Dirige a tu equipo con datos',
      'features' => 'Todo lo de Pro + Termómetro, Mesa de Trabajo, ranking del equipo, CotizaCloud AI, marketing y reportes avanzados',
    ],
  ];

  // Jerarquía de planes. Un cliente que YA paga un plan no puede bajarse solo
  // a uno menor desde aquí — eso pasa por soporte (el superadmin lo ajusta a
  // mano). Evita dejar asesores/datos colgados en un downgrade automático.
  $plan_rango   = ['free' => 0, 'lite' => 1, 'pro' => 2, 'business' => 3];
  $rango_actual = $plan_rango[$trial['plan']] ?? 0;
  ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:16px">
    <?php foreach ($planes_grid as $plan_key => $pd):
      if (empty($precios[$plan_key])) continue;
      $is_current = $trial['plan'] === $plan_key && $trial['es_pagado'] && $tiene_auto_renew && !$cobro_fallido;
      // Downgrade = plan PAGADO real de rango menor. El trial NO cuenta (C2 de
      // la auditoría: el trial de Pro impedía auto-comprar Lite, el plan gancho).
      $is_downgrade = $trial['es_pagado'] && empty($trial['trial_activo']) && ($plan_rango[$plan_key] ?? 0) < $rango_actual;
      $color = $pd['color'];
    ?>
    <div class="card" style="border-color:<?= $is_current ? $color : 'var(--border)' ?>">
      <div style="padding:20px;border-bottom:1px solid var(--border)">
        <div style="font:700 18px var(--body);color:<?= $color ?>"><?= $pd['name'] ?></div>
        <div style="font:400 12px var(--body);color:var(--t3);margin-top:4px">
          <?= $pd['tagline'] ?>
        </div>
      </div>

      <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px">
        <?php if ($is_downgrade): ?>
        <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start">
          <div style="font:700 16px var(--num);color:var(--t3)">$<?= number_format($precios[$plan_key]['mensual'], 0) ?> <span style="font:400 12px var(--body);color:var(--t3)">MXN/mes</span></div>
          <div style="font:400 12px var(--body);color:var(--t3);line-height:1.5">
            Ya tienes un plan superior (<?= htmlspecialchars($trial['plan_label']) ?>). Para cambiar a un plan menor, <a href="/ayuda" style="color:<?= $color ?>;font-weight:600">contacta a soporte</a>.
          </div>
        </div>
        <?php elseif ($plan_key === 'business'): ?>
        <div style="font:700 16px var(--num);color:var(--text)">$<?= number_format($precios['business']['mensual'], 0) ?> <span style="font:400 12px var(--body);color:var(--t3)">MXN/mes</span></div>
        <div style="font:400 12px var(--body);color:var(--t3);margin:6px 0 10px">Business se activa con una demo personalizada y 4 horas de capacitación para tu equipo.</div>
        <?php // Abre el CHAT DE SOPORTE in-app (burbuja czs de layout.php, solo admins
              // — esta pantalla es de admins): la solicitud llega con push + email al
              // superadmin. Un mailto a un buzón inexistente perdía el lead. ?>
        <a href="#" class="btn-main" style="display:inline-block;padding:8px 18px;font-size:12px;background:<?= $color ?>;border-color:<?= $color ?>;text-decoration:none"
           onclick="var f=document.getElementById('czs-fab');if(f){f.click();var t=document.getElementById('czs-input');if(t&&!t.value){t.value='Quiero una demo de Business';}}return false;">Agenda una demo</a>
        <?php else: ?>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div>
            <div style="font:700 16px var(--num);color:var(--text)">$<?= number_format($precios[$plan_key]['mensual'], 0) ?> <span style="font:400 12px var(--body);color:var(--t3)">MXN/mes</span></div>
          </div>
          <form method="POST" action="/config/suscripcion/crear" style="margin:0">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
            <input type="hidden" name="plan" value="<?= $plan_key ?>">
            <input type="hidden" name="ciclo" value="mensual">
            <button type="submit" class="btn-main" style="padding:8px 18px;font-size:12px;background:<?= $color ?>;border-color:<?= $color ?>">Mensual</button>
          </form>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div>
            <div style="font:700 16px var(--num);color:var(--text)">$<?= number_format($precios[$plan_key]['anual'] / 12, 0) ?> <span style="font:400 12px var(--body);color:var(--t3)">MXN/mes</span></div>
            <div style="font:400 11px var(--body);color:var(--t3)">$<?= number_format($precios[$plan_key]['anual'], 0) ?>/año — <span style="color:var(--g);font-weight:600">20% descuento</span></div>
          </div>
          <form method="POST" action="/config/suscripcion/crear" style="margin:0">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
            <input type="hidden" name="plan" value="<?= $plan_key ?>">
            <input type="hidden" name="ciclo" value="anual">
            <button type="submit" class="btn-main" style="padding:8px 18px;font-size:12px;background:<?= $color ?>;border-color:<?= $color ?>">Anual</button>
          </form>
        </div>
        <?php endif; ?>
      </div>

      <div style="padding:12px 20px;border-top:1px solid var(--border);font:400 12px var(--body);color:var(--t3);line-height:1.6">
        <?= $pd['features'] ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if ($tiene_auto_renew): ?>
<div class="sec">
  <div class="sec-lbl">Gestionar suscripción</div>
  <div class="card">
    <div style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;border-bottom:1px solid var(--border)">
      <div>
        <div style="font:500 14px var(--body);color:var(--text)">Cambiar método de pago</div>
        <div style="font:400 12px var(--body);color:var(--t3);margin-top:2px">
          Haz un pago con una nueva tarjeta — reemplaza la actual para los próximos cobros
        </div>
      </div>
      <form method="POST" action="/config/suscripcion/crear" style="margin:0">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
        <input type="hidden" name="plan" value="<?= htmlspecialchars($sub['plan']) ?>">
        <input type="hidden" name="ciclo" value="<?= htmlspecialchars($sub['ciclo']) ?>">
        <button type="submit" style="padding:8px 18px;border-radius:var(--r-sm);border:1px solid var(--border);background:transparent;font:600 12px var(--body);color:var(--text);cursor:pointer">
          Cambiar tarjeta
        </button>
      </form>
    </div>
    <div style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div>
        <div style="font:500 14px var(--body);color:var(--text)">Cancelar renovación automática</div>
        <div style="font:400 12px var(--body);color:var(--t3);margin-top:2px">
          Tu plan seguirá activo hasta <?= $trial['plan_vence'] ? date('d/m/Y', strtotime($trial['plan_vence'])) : 'el fin del ciclo' ?>
        </div>
      </div>
      <form method="POST" action="/config/suscripcion/cancelar" style="margin:0"
            onsubmit="return confirm('¿Cancelar la renovación automática?\nTu plan seguirá activo hasta el fin del ciclo actual.')">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
        <button type="submit" style="padding:8px 18px;border-radius:var(--r-sm);border:1px solid var(--danger);background:transparent;font:600 12px var(--body);color:var(--danger);cursor:pointer">
          Cancelar suscripción
        </button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($pagos): ?>
<div class="sec">
  <div class="sec-lbl">Historial de pagos</div>
  <div class="card" style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font:400 13px var(--body)">
      <thead>
        <tr style="background:var(--bg);border-bottom:1px solid var(--border)">
          <th style="padding:10px 16px;text-align:left;font-weight:700;font-size:11px;text-transform:uppercase;color:var(--t3)">Fecha</th>
          <th style="padding:10px 16px;text-align:right;font-weight:700;font-size:11px;text-transform:uppercase;color:var(--t3)">Monto</th>
          <th style="padding:10px 16px;text-align:center;font-weight:700;font-size:11px;text-transform:uppercase;color:var(--t3)">Estado</th>
          <th style="padding:10px 16px;text-align:left;font-weight:700;font-size:11px;text-transform:uppercase;color:var(--t3)">Referencia</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pagos as $pago):
          $status_color = match($pago['estado']) {
              'approved' => 'var(--g)',
              'pending' => 'var(--amb)',
              'rejected' => 'var(--danger)',
              'refunded' => 'var(--blue)',
              default => 'var(--t3)',
          };
          $status_label = match($pago['estado']) {
              'approved' => 'Aprobado',
              'pending' => 'Pendiente',
              'rejected' => 'Rechazado',
              'refunded' => 'Reembolsado',
              default => $pago['estado'],
          };
        ?>
        <tr style="border-bottom:1px solid var(--border)">
          <td style="padding:10px 16px;font:500 13px var(--num);color:var(--text)"><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
          <td style="padding:10px 16px;text-align:right;font:600 13px var(--num);color:var(--text)">$<?= number_format($pago['monto_mxn'], 2) ?></td>
          <td style="padding:10px 16px;text-align:center">
            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font:600 11px var(--body);color:<?= $status_color ?>;background:color-mix(in srgb, <?= $status_color ?> 12%, white)"><?= $status_label ?></span>
          </td>
          <td style="padding:10px 16px;font:400 12px var(--num);color:var(--t3)"><?= e($pago['mp_payment_id']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
