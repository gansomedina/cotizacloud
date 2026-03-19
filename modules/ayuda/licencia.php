<?php
// ============================================================
//  CotizaApp — modules/ayuda/licencia.php
//  GET /licencia
//  Página dedicada para solicitar activación de licencia
// ============================================================

defined('COTIZAAPP') or die;

$empresa = Auth::empresa();
$trial = trial_info(EMPRESA_ID);

$page_title = 'Activar licencia';
ob_start();
?>

<div style="max-width:520px;margin:40px auto;padding:0 16px">

    <?php if ($trial['es_trial']): ?>
    <!-- Trial info -->
    <div style="text-align:center;margin-bottom:24px">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--amb-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--amb)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <h2 style="font-size:22px;font-weight:800;margin:0 0 8px">Activa tu licencia PRO</h2>
        <p style="color:var(--t2);margin:0;line-height:1.6">
            <?php if ($trial['agotado']): ?>
                Has usado <strong><?= $trial['usadas'] ?>/<?= TRIAL_LIMIT ?></strong> cotizaciones de prueba. Activa PRO para crear cotizaciones ilimitadas.
            <?php else: ?>
                Te quedan <strong><?= $trial['restantes'] ?></strong> cotizaciones de prueba. Activa PRO para no tener límite.
            <?php endif; ?>
        </p>
    </div>

    <?php if ($trial['agotado']): ?>
    <div style="background:var(--amb-bg);border:1px solid #fcd34d;border-radius:var(--r);padding:14px 16px;margin-bottom:20px">
        <div style="font-size:13px;color:var(--amb)">
            <strong>Cotizaciones usadas:</strong> <?= $trial['usadas'] ?> / <?= TRIAL_LIMIT ?>
        </div>
        <div style="background:#fde68a;border-radius:6px;height:6px;margin-top:8px;overflow:hidden">
            <div style="background:var(--danger);height:100%;width:100%;border-radius:6px"></div>
        </div>
    </div>
    <?php endif; ?>

    <?php elseif ($trial['por_vencer']): ?>
    <div style="text-align:center;margin-bottom:24px">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--amb-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--amb)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h2 style="font-size:22px;font-weight:800;margin:0 0 8px">Renueva tu licencia</h2>
        <p style="color:var(--t2);margin:0;line-height:1.6">
            Tu licencia PRO vence el <strong><?= date('d/m/Y', strtotime($trial['plan_vence'])) ?></strong> (<?= $trial['dias_restantes'] ?> días restantes).
        </p>
    </div>

    <?php else: ?>
    <div style="text-align:center;margin-bottom:24px">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--g-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--g)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h2 style="font-size:22px;font-weight:800;margin:0 0 8px">Tu licencia PRO</h2>
        <p style="color:var(--t2);margin:0;line-height:1.6">
            Licencia activa<?= $trial['plan_vence'] ? ' hasta el <strong>' . date('d/m/Y', strtotime($trial['plan_vence'])) . '</strong>' : '' ?>.
            Si necesitas renovar o extender, usa el formulario.
        </p>
    </div>
    <?php endif; ?>

    <!-- Formulario -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
        <form action="/ayuda/ticket" method="POST" id="licenciaForm">
            <?= csrf_field() ?>
            <input type="hidden" name="titulo" value="Solicitud de licencia PRO">

            <div style="margin-bottom:16px">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--t1);margin-bottom:6px">Duración deseada</label>
                <select id="lic-duracion" name="duracion_lic" style="width:100%;padding:10px 12px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font:400 14px var(--body);background:var(--white);color:var(--text)">
                    <option value="1 mes">1 mes</option>
                    <option value="3 meses">3 meses</option>
                    <option value="6 meses">6 meses</option>
                    <option value="1 año">1 año</option>
                </select>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--t1);margin-bottom:6px">Mensaje (opcional)</label>
                <textarea id="lic-msg" name="descripcion" rows="3" placeholder="Información adicional..." style="width:100%;padding:10px 12px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font:400 14px var(--body);color:var(--text);resize:vertical"></textarea>
            </div>

            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:var(--r-sm);font:600 14px var(--body);background:var(--g);color:#fff;cursor:pointer;transition:opacity .12s" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'" onclick="
                var dur = document.getElementById('lic-duracion').value;
                var desc = document.getElementById('lic-msg');
                this.form.querySelector('[name=titulo]').value = 'Solicitud de licencia PRO — ' + dur;
                if (!desc.value.trim()) desc.value = 'Solicitud de activación de licencia PRO.\nDuración solicitada: ' + dur;
                else desc.value = 'Solicitud de activación de licencia PRO.\nDuración solicitada: ' + dur + '\n\nMensaje:\n' + desc.value;
            ">Solicitar activación</button>
        </form>

        <p style="font-size:12px;color:var(--t3);margin:14px 0 0;text-align:center;line-height:1.5">
            Serás contactado a la brevedad con la liga de cobro para activar tu licencia.
        </p>
    </div>

    <a href="/cotizaciones" style="display:block;text-align:center;margin-top:16px;font-size:13px;color:var(--t3);text-decoration:none">Volver a cotizaciones</a>
</div>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
