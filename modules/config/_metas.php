<?php
// ============================================================
//  cotiza.cloud — modules/config/_metas.php
//  Pestaña Configuración › Metas (Business, solo admin).
//  UNA meta general de la empresa, igual para todos los meses.
//  Espera: $empresa (fila de empresas), $empresa_id, $tab_activo.
//  Guarda en POST /config/metas (guardar_metas.php).
// ============================================================
defined('COTIZAAPP') or die;
if (!class_exists('MetasEmpresa')) require_once __DIR__ . '/../../core/MetasEmpresa.php';

$mt_meta  = null;
$mt_error = false;
try {
    $mt_meta = MetasEmpresa::meta((int)$empresa_id);
} catch (\Throwable $ex) {
    $mt_error = true;
    error_log('[Metas] pestaña empresa ' . $empresa_id . ': ' . $ex->getMessage());
}
$mt_moneda = strtoupper((string)($empresa['moneda'] ?? 'MXN'));
$mt_tasa   = $empresa['tasa_conv_meta'] ?? null;
// Con separador de miles: 1,800,000 y 180,000 no se confunden. El servidor
// acepta comas (MetasEmpresa::parse_monto).
$mt_fmt = fn($x) => number_format((float)$x, fmod((float)$x, 1.0) == 0.0 ? 0 : 2, '.', ',');
$mt_val = fn(string $k) => $mt_meta ? e($mt_fmt($mt_meta[$k])) : '';
?>
<style>
.mt-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.mt-grid .num-in,.mt-tasa .num-in{width:100%;box-sizing:border-box}
.mt-acc{display:flex;gap:8px;align-items:center;margin-top:14px;flex-wrap:wrap}
.mt-btn{padding:9px 16px;border-radius:var(--r-sm);border:1.5px solid var(--g);background:var(--g);color:#fff;font:700 13px var(--body);cursor:pointer;line-height:1.2}
.mt-acc .mt-btn,.mt-tasa .mt-btn{margin:0}
.mt-btn.sec{background:transparent;color:var(--t3);border-color:var(--border)}
.mt-err{font:600 12px var(--body);color:#b91c1c;display:none;margin-top:8px}
.mt-ok{font:600 12px var(--body);color:var(--g);display:none}
.mt-aviso{padding:12px 16px;border-radius:var(--r-sm);background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font:500 13px var(--body);margin-bottom:14px;line-height:1.5}
@media (max-width:640px){ .mt-grid{grid-template-columns:1fr} }
</style>

<div class="tab-panel <?= $tab_activo === 'metas' ? 'on' : '' ?>" id="panel-metas">

  <p style="font:400 13px var(--body);color:var(--t3);margin-bottom:16px;line-height:1.6">
    La meta mensual es el dato que CotizaCloud AI usa para leer cómo va la empresa. Aplica a todos los meses
    y se compara contra las <b>ventas con anticipo</b>, en la fecha en que el cliente aceptó,
    <b>sin Descuento Inteligente</b>. Tus asesores nunca verán las cifras: solo leerán cómo va la empresa
    (por ejemplo, <i>"La empresa va baja en este mes"</i>).
  </p>

  <?php if ($mt_error): ?>
  <div class="mt-aviso">Las metas todavía no están disponibles en tu cuenta. Vuelve a intentar más tarde.</div>
  <?php else: ?>

  <?php if ($mt_meta && $mt_meta['moneda'] !== $mt_moneda): ?>
  <div class="mt-aviso">Tu meta está capturada en <?= e($mt_meta['moneda']) ?> y la empresa ahora opera en <?= e($mt_moneda) ?>: recaptúrala.</div>
  <?php endif; ?>

  <div class="sec-lbl">Meta mensual de la empresa (<?= e($mt_moneda) ?>)</div>
  <div class="card" style="padding:18px 20px;margin-bottom:22px" id="mt_meta">
    <div class="mt-grid">
      <div>
        <label class="field-lbl" for="mt_equilibrio">Punto de equilibrio</label>
        <input class="num-in" id="mt_equilibrio" type="text" inputmode="decimal" autocomplete="off" onblur="mtFormato(this)" value="<?= $mt_val('equilibrio') ?>">
        <div class="field-sub">Lo mínimo que la empresa necesita vender al mes para no perder.</div>
      </div>
      <div>
        <label class="field-lbl" for="mt_pesimista">Meta pesimista</label>
        <input class="num-in" id="mt_pesimista" type="text" inputmode="decimal" autocomplete="off" onblur="mtFormato(this)" value="<?= $mt_val('pesimista') ?>">
        <div class="field-sub">La meta del mes en un escenario conservador.</div>
      </div>
      <div>
        <label class="field-lbl" for="mt_optimista">Meta optimista</label>
        <input class="num-in" id="mt_optimista" type="text" inputmode="decimal" autocomplete="off" onblur="mtFormato(this)" value="<?= $mt_val('optimista') ?>">
        <div class="field-sub">La meta del mes si todo sale bien.</div>
      </div>
    </div>
    <div class="field-sub" style="margin-top:12px">El punto de equilibrio no puede quedar arriba de la meta pesimista, ni la pesimista arriba de la optimista.</div>
    <div class="mt-err" id="mt_meta_err"></div>
    <div class="mt-acc">
      <button class="mt-btn" type="button" onclick="mtGuardarMeta(this)">Guardar meta</button>
      <?php if ($mt_meta): ?>
      <button class="mt-btn sec" type="button" onclick="mtQuitarMeta()">Quitar meta</button>
      <?php endif; ?>
      <span class="mt-ok" id="mt_meta_ok">✓ Guardado</span>
    </div>
  </div>

  <div class="sec-lbl">Tasa de conversión deseada</div>
  <div class="card mt-tasa" style="padding:16px 20px;margin-bottom:22px">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
      <div>
        <label class="field-lbl" for="mt_tasa">Tasa deseada (%)</label>
        <input class="num-in" id="mt_tasa" type="text" inputmode="decimal" autocomplete="off"
               value="<?= $mt_tasa !== null ? e(rtrim(rtrim(number_format((float)$mt_tasa, 2, '.', ''), '0'), '.')) : '' ?>" placeholder="—" style="width:110px">
      </div>
      <button class="mt-btn" type="button" onclick="mtGuardarTasa()">Guardar</button>
      <span class="mt-ok" id="mt_tasa_ok">✓ Guardado</span>
    </div>
    <div class="field-sub" style="margin-top:8px">De cada 100 cotizaciones que envías, cuántas quieres vender.</div>
    <div class="mt-err" id="mt_tasa_err"></div>
  </div>
  <?php endif; ?>
</div>

<script>
async function mtPost(body) {
    const r = await fetch('/config/metas', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN, 'Accept': 'application/json'},
        body: JSON.stringify(body)
    });
    let d = null;
    try { d = await r.json(); } catch (e) {}
    // Sin JSON: sesión vencida (redirige al login) o permiso retirado.
    if (!d && (r.redirected || r.status === 401 || r.status === 403)) {
        return {ok: false, error: 'Tu sesión expiró o ya no tienes permiso. Recarga la página.'};
    }
    return d || {ok: false, error: 'No se pudo guardar. Revisa tu conexión.'};
}
function mtErr(id, msg) { const el = document.getElementById(id); el.textContent = msg || ''; el.style.display = msg ? 'block' : 'none'; }
function mtOk(id) { const el = document.getElementById(id); el.style.display = 'inline'; setTimeout(() => el.style.display = 'none', 1800); }

// Solo se formatea lo que es claramente un monto (dígitos, comas, hasta 2
// decimales). "180.000", "0x10" o "1e3" se dejan tal cual: el servidor los
// rechaza con su mensaje en vez de que el formato los cambie a otro número.
function mtFormato(el) {
    const t = String(el.value).trim().replace(/^\$/, '');
    if (!/^[\d,]+(\.\d{1,2})?$/.test(t)) return;
    const n = Number(t.replace(/,/g, ''));
    if (!isFinite(n)) return;
    el.value = n.toLocaleString('en-US', {maximumFractionDigits: 2});
}

async function mtGuardarMeta(btn) {
    mtErr('mt_meta_err', '');
    const v = id => document.getElementById(id).value;
    btn.disabled = true;
    const d = await mtPost({accion: 'meta', equilibrio: v('mt_equilibrio'), pesimista: v('mt_pesimista'), optimista: v('mt_optimista')});
    btn.disabled = false;
    if (!d.ok) { mtErr('mt_meta_err', d.error); return; }
    mtOk('mt_meta_ok');
}

async function mtQuitarMeta() {
    if (!confirm('¿Quitar la meta mensual? CotizaCloud AI dejará de leer cómo va la empresa hasta que captures una nueva.')) return;
    mtErr('mt_meta_err', '');
    const d = await mtPost({accion: 'quitar'});
    if (!d.ok) { mtErr('mt_meta_err', d.error); return; }
    location.href = '/config?tab=metas';
}

async function mtGuardarTasa() {
    mtErr('mt_tasa_err', '');
    const d = await mtPost({accion: 'tasa', tasa: document.getElementById('mt_tasa').value});
    if (!d.ok) { mtErr('mt_tasa_err', d.error); return; }
    mtOk('mt_tasa_ok');
}
</script>
