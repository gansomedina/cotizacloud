<?php
// ============================================================
//  CotizaCloud — modules/config/suscripcion.php
//  Tab "Suscripción" en Configuración
//  Muestra plan actual, botones de upgrade, historial de pagos
//  Oculto en app nativa iOS (Apple Guideline 3.1.1)
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;

// Sincronizar con MP si tiene suscripción y último sync hace +10 min
$ultima_sync = DB::val("SELECT ultima_sync_mp FROM empresas WHERE id=?", [$empresa_id]);
$tiene_sub   = DB::val("SELECT COUNT(*) FROM suscripciones WHERE empresa_id=? AND mp_preapproval_id IS NOT NULL", [$empresa_id]);
if ($tiene_sub && (!$ultima_sync || strtotime($ultima_sync) < time() - 600)) {
    MercadoPago::sincronizar($empresa_id);
}

$trial = trial_info($empresa_id);
$empresa = DB::row("SELECT email, nombre FROM empresas WHERE id=?", [$empresa_id]);
$sub = DB::row("SELECT * FROM suscripciones WHERE empresa_id=?", [$empresa_id]);
$pagos = DB::query(
    "SELECT * FROM pagos_suscripcion WHERE empresa_id=? ORDER BY fecha_pago DESC LIMIT 20",
    [$empresa_id]
);
$precios = MercadoPago::precios();
$csrf = $_SESSION[CSRF_TOKEN_NAME] ?? '';
?>

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
          <?php if ($sub && $sub['estado'] === 'active' && !$sub['cancel_al_vencer']): ?>
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

<?php if ($trial['es_free'] || ($trial['es_pagado'] && (!$sub || $sub['cancel_al_vencer']))): ?>
<div class="sec">
  <div class="sec-lbl">Actualizar plan</div>

  <div style="padding:12px 14px;background:var(--amb-bg);border:1px solid var(--amb);border-radius:var(--r-sm);margin-bottom:16px;display:flex;align-items:flex-start;gap:10px">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--amb-dark)" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div style="font:400 12px var(--body);color:var(--amb-dark);line-height:1.5">
      <strong>Si tu banco rechaza el cargo</strong>, llama al número al reverso de tu tarjeta y pide <em>autorizar cargos recurrentes de MercadoPago</em>. Muchos bancos bloquean el primer cobro por defecto como protección.
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
    <?php foreach (['pro' => 'Pro', 'business' => 'Business'] as $plan_key => $plan_name):
      $is_current = $trial['plan'] === $plan_key && $trial['es_pagado'] && $sub && !$sub['cancel_al_vencer'];
      $color = $plan_key === 'business' ? 'var(--blue)' : 'var(--g)';
    ?>
    <div class="card" style="border-color:<?= $is_current ? $color : 'var(--border)' ?>">
      <div style="padding:20px;border-bottom:1px solid var(--border)">
        <div style="font:700 18px var(--body);color:<?= $color ?>"><?= $plan_name ?></div>
        <div style="font:400 12px var(--body);color:var(--t3);margin-top:4px">
          <?= $plan_key === 'pro' ? 'Para profesionales independientes' : 'Para equipos de trabajo' ?>
        </div>
      </div>

      <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px">
        <!-- Mensual -->
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div>
            <div style="font:700 16px var(--num);color:var(--text)">$<?= number_format($precios[$plan_key]['mensual'], 0) ?> <span style="font:400 12px var(--body);color:var(--t3)">MXN/mes</span></div>
          </div>
          <?php if (!$is_current): ?>
          <form method="POST" action="/config/suscripcion/crear" style="margin:0">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
            <input type="hidden" name="plan" value="<?= $plan_key ?>">
            <input type="hidden" name="ciclo" value="mensual">
            <button type="submit" class="btn-main" style="padding:8px 18px;font-size:12px;background:<?= $color ?>;border-color:<?= $color ?>">Mensual</button>
          </form>
          <?php endif; ?>
        </div>

        <!-- Anual -->
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div>
            <div style="font:700 16px var(--num);color:var(--text)">$<?= number_format($precios[$plan_key]['anual'] / 12, 0) ?> <span style="font:400 12px var(--body);color:var(--t3)">MXN/mes</span></div>
            <div style="font:400 11px var(--body);color:var(--t3)">$<?= number_format($precios[$plan_key]['anual'], 0) ?>/año — <span style="color:var(--g);font-weight:600">20% descuento</span></div>
          </div>
          <?php if (!$is_current): ?>
          <form method="POST" action="/config/suscripcion/crear" style="margin:0">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
            <input type="hidden" name="plan" value="<?= $plan_key ?>">
            <input type="hidden" name="ciclo" value="anual">
            <button type="submit" class="btn-main" style="padding:8px 18px;font-size:12px;background:<?= $color ?>;border-color:<?= $color ?>">Anual</button>
          </form>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($plan_key === 'pro'): ?>
      <div style="padding:12px 20px;border-top:1px solid var(--border);font:400 12px var(--body);color:var(--t3);line-height:1.6">
        Cotizaciones ilimitadas, todos los módulos, app móvil
      </div>
      <?php else: ?>
      <div style="padding:12px 20px;border-top:1px solid var(--border);font:400 12px var(--body);color:var(--t3);line-height:1.6">
        Usuarios ilimitados, marketing pixels, extras, reportes avanzados
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if ($sub && $sub['estado'] === 'active' && !$sub['cancel_al_vencer']): ?>
<div class="sec">
  <div class="sec-lbl">Gestionar suscripción</div>
  <div class="card">
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
