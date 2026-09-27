<?php
// ============================================================
//  cotiza.cloud — modules/config/_metas.php
//  Pestaña Configuración › Metas (Business, solo admin).
//  Espera: $empresa (fila de empresas), $empresa_id, $tab_activo.
//  Guarda en POST /config/metas (guardar_metas.php).
// ============================================================
defined('COTIZAAPP') or die;
if (!class_exists('MetasEmpresa')) require_once __DIR__ . '/../../core/MetasEmpresa.php';

$mt_filas = [];
$mt_error = false;
try {
    $mt_filas = MetasEmpresa::filas((int)$empresa_id);
} catch (\Throwable $ex) {
    $mt_error = true;
    error_log('[Metas] pestaña empresa ' . $empresa_id . ': ' . $ex->getMessage());
}
$mt_moneda = strtoupper((string)($empresa['moneda'] ?? 'MXN'));
$mt_tasa   = $empresa['tasa_conv_meta'] ?? null;
$mt_meses  = MetasEmpresa::meses_captura();
// Solo las filas que RIGEN algún mes de la rejilla: una fila vieja en otra
// moneda que ya nada usa no debe dejar un aviso que el admin no puede quitar.
$mt_otra_moneda = [];
foreach ($mt_meses as $m) {
    $v = MetasEmpresa::fila_vigente($mt_filas, $m['anio'], $m['mes']);
    if ($v && strtoupper($v['moneda']) !== $mt_moneda) $mt_otra_moneda[strtoupper($v['moneda'])] = true;
}
$mt_otra_moneda = array_keys($mt_otra_moneda);
// Con separador de miles: 1,800,000 y 180,000 no se confunden. El servidor
// acepta comas (MetasEmpresa::parse_monto).
$mt_fmt = fn($x) => number_format((float)$x, fmod((float)$x, 1.0) == 0.0 ? 0 : 2, '.', ',');
?>
<style>
.mt-row{display:grid;grid-template-columns:150px repeat(3,minmax(0,1fr)) 150px;gap:10px;align-items:end;padding:12px 16px;border-top:1px solid var(--border)}
.mt-row:first-child{border-top:none}
.mt-row.actual{background:#f7faf8}
.mt-mes{font:700 13px var(--body);color:var(--text)}
.mt-origen{font:400 11px var(--body);color:var(--t3);margin-top:2px;text-transform:none}
.mt-row .num-in{width:100%;box-sizing:border-box}
.mt-acc{display:flex;gap:6px;align-items:stretch}
.mt-acc .mt-btn{margin:0;line-height:1.2}
.mt-btn{padding:8px 12px;border-radius:var(--r-sm);border:1.5px solid var(--g);background:var(--g);color:#fff;font:700 12px var(--body);cursor:pointer}
.mt-btn.sec{background:transparent;color:var(--t3);border-color:var(--border)}
.mt-err{grid-column:1/-1;font:600 12px var(--body);color:#b91c1c;display:none}
.mt-ok{font:600 12px var(--body);color:var(--g);display:none}
.mt-aviso{padding:12px 16px;border-radius:var(--r-sm);background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font:500 13px var(--body);margin-bottom:14px;line-height:1.5}
@media (max-width:900px){
  .mt-row{grid-template-columns:repeat(3,minmax(0,1fr))}
  .mt-row .mt-mes-c{grid-column:1/-1}
  .mt-row .mt-acc{grid-column:1/-1}
}
</style>

<div class="tab-panel <?= $tab_activo === 'metas' ? 'on' : '' ?>" id="panel-metas">

  <p style="font:400 13px var(--body);color:var(--t3);margin-bottom:16px;line-height:1.6">
    Las metas son el dato que CotizaCloud AI usa para leer cómo va la empresa. Se comparan contra las
    <b>ventas con anticipo</b>, en la fecha en que el cliente aceptó, <b>sin Descuento Inteligente</b>.
    Tus asesores nunca verán las cifras: solo leerán cómo va la empresa (por ejemplo, <i>"La empresa va baja en este mes"</i>).
  </p>

  <?php if ($mt_error): ?>
  <div class="mt-aviso">Las metas todavía no están disponibles en tu cuenta. Vuelve a intentar más tarde.</div>
  <?php else: ?>

  <?php if ($mt_otra_moneda): ?>
  <div class="mt-aviso">Tus metas están capturadas en <?= e(implode(', ', $mt_otra_moneda)) ?> y la empresa ahora opera en <?= e($mt_moneda) ?>: recaptúralas.</div>
  <?php endif; ?>

  <div class="sec-lbl">Tasa de conversión deseada</div>
  <div class="card" style="padding:16px 20px;margin-bottom:22px">
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
    <div class="mt-err" id="mt_tasa_err" style="margin-top:6px"></div>
  </div>

  <div class="sec-lbl">Metas por mes (<?= e($mt_moneda) ?>)</div>
  <p style="font:400 12px var(--body);color:var(--t3);margin:-4px 0 10px;line-height:1.5">
    El punto de equilibrio no puede quedar arriba de la meta pesimista, ni la pesimista arriba de la optimista.
    Un mes sin capturar usa las metas del último mes capturado antes que él.
  </p>
  <div class="card" style="margin-bottom:20px">
    <?php foreach ($mt_meses as $m):
        $k    = $m['anio'] . '-' . $m['mes'];
        $vig  = MetasEmpresa::fila_vigente($mt_filas, $m['anio'], $m['mes']);
        $prop = $vig && (int)$vig['anio'] === $m['anio'] && (int)$vig['mes'] === $m['mes'];
        $ori  = $vig && !$prop ? MetasEmpresa::nombre_mes((int)$vig['anio'], (int)$vig['mes']) : null;
    ?>
    <div class="mt-row<?= $m['actual'] ? ' actual' : '' ?>" data-anio="<?= $m['anio'] ?>" data-mes="<?= $m['mes'] ?>">
      <div class="mt-mes-c">
        <div class="mt-mes"><?= e(mb_convert_case(mb_substr($m['nombre'], 0, 1), MB_CASE_UPPER) . mb_substr($m['nombre'], 1)) ?></div>
        <div class="mt-origen"><?= $m['actual'] ? '<b>Este mes</b> · ' : '' ?>
          <?php if ($prop): ?>Capturado
          <?php elseif ($ori): ?>Usa las de <?= e($ori) ?>
          <?php else: ?>Sin metas<?php endif; ?>
        </div>
      </div>
      <?php foreach (['equilibrio' => ['Punto de equilibrio', 'equilibrio'],
                      'pesimista'  => ['Meta pesimista', 'meta_pesimista'],
                      'optimista'  => ['Meta optimista', 'meta_optimista']] as $campo => [$lbl, $col]): ?>
      <div>
        <label class="field-lbl"><?= $lbl ?></label>
        <input class="num-in mt-<?= $campo ?>" type="text" inputmode="decimal" autocomplete="off" onblur="mtFormato(this)"
               value="<?= $prop ? e($mt_fmt($vig[$col])) : '' ?>" data-orig="<?= $prop ? e($mt_fmt($vig[$col])) : '' ?>"
               placeholder="<?= $vig && !$prop ? e($mt_fmt($vig[$col])) : '' ?>">
      </div>
      <?php endforeach; ?>
      <div class="mt-acc">
        <button class="mt-btn" type="button" onclick="mtGuardarMes(this)">Guardar</button>
        <?php if ($prop): ?>
        <button class="mt-btn sec" type="button" onclick="mtBorrarMes(this)" title="Quitar las metas de este mes para que use las del mes anterior">Quitar</button>
        <?php endif; ?>
      </div>
      <div class="mt-err"></div>
    </div>
    <?php endforeach; ?>
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
// 180000 → 180,000 al salir del campo. Si no es número, se deja como está
// y el servidor responde con el error.
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
// ¿Algún OTRO mes tiene cambios sin guardar?
function mtOtrosSucios(row) {
    return [...document.querySelectorAll('#panel-metas .mt-row')].some(r => r !== row &&
        [...r.querySelectorAll('input[data-orig]')].some(i => i.value.trim() !== i.dataset.orig));
}
function mtRecargar() { location.href = '/config?tab=metas'; }
function mtErr(el, msg) { el.textContent = msg || ''; el.style.display = msg ? 'block' : 'none'; }

async function mtGuardarTasa() {
    const err = document.getElementById('mt_tasa_err');
    mtErr(err, '');
    const d = await mtPost({accion: 'tasa', tasa: document.getElementById('mt_tasa').value});
    if (!d.ok) { mtErr(err, d.error); return; }
    const ok = document.getElementById('mt_tasa_ok');
    ok.style.display = 'inline'; setTimeout(() => ok.style.display = 'none', 1800);
}

async function mtGuardarMes(btn) {
    const row = btn.closest('.mt-row'), err = row.querySelector('.mt-err');
    mtErr(err, '');
    const v = c => row.querySelector('.mt-' + c).value;
    // Un campo vacío con valor heredado visible (placeholder) NO se envía como
    // ese valor: el admin debe escribir los tres para capturar el mes.
    btn.disabled = true;
    const d = await mtPost({accion: 'mes', anio: +row.dataset.anio, mes: +row.dataset.mes,
                            equilibrio: v('equilibrio'), pesimista: v('pesimista'), optimista: v('optimista')});
    btn.disabled = false;
    if (!d.ok) { mtErr(err, d.error); return; }
    // Recargar borraría lo que el admin lleva escrito en otros meses.
    if (mtOtrosSucios(row)) {
        row.querySelectorAll('input[data-orig]').forEach(i => i.dataset.orig = i.value.trim());
        row.querySelector('.mt-origen').textContent = '✓ Guardado — recarga al terminar para ver cómo heredan los demás meses';
        return;
    }
    mtRecargar();
}

async function mtBorrarMes(btn) {
    if (!confirm('¿Quitar las metas de este mes? Usará las del último mes capturado antes que él.')) return;
    const row = btn.closest('.mt-row'), err = row.querySelector('.mt-err');
    if (mtOtrosSucios(row) && !confirm('Tienes cambios sin guardar en otros meses y se van a perder. ¿Continuar?')) return;
    const d = await mtPost({accion: 'borrar', anio: +row.dataset.anio, mes: +row.dataset.mes});
    if (!d.ok) { mtErr(err, d.error); return; }
    mtRecargar();
}
</script>
