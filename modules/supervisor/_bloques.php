<?php
// ============================================================
//  Supervisor — bloques que se suman al Ejecutivo
//
//  Todo vive DENTRO de la misma página del ejecutivo, no en pantallas
//  aparte: el supervisor abre una URL y ahí está todo.
//
//  Dos bloques:
//    1. Rendimiento del equipo por sucursal — los MISMOS 5 pilares con sus
//       textos y su conclusión que ve el dueño en su dashboard, y el botón del
//       reporte del Director.
//    2. Score mensual — promedio real por mes.
//
//  POR QUÉ SE REPINTA Y NO SE INCLUYE modules/dashboard/_ritmo.php:
//  ese partial está escrito para el layout de la app, que es claro. El
//  ejecutivo es OSCURO y además su --r es un color rojo, no un radio de borde
//  (ver el :root de executive.php). Incluirlo tal cual saldría roto.
//  Los DATOS y los TEXTOS salen de RitmoAsesor::empresa(), la misma fuente que
//  alimenta al dueño — lo único que cambia aquí es la pintura.
//
//  El reporte del Director sí se reusa entero: el modal, su CSS y su JS son
//  los mismos, con el tema claro forzado dentro del modal para que se lea
//  igual que el del superadmin.
// ============================================================
defined('COTIZAAPP') or die;
if (empty($modo_supervisor)) return;

// ── Datos: 5 pilares por sucursal ───────────────────────────
$sv_suc = [];
foreach ($empresas_cfg as $sv_eid => $sv_cfg) {
    $sv_on = false;
    try { $sv_on = (int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$sv_eid]) >= 1; }
    catch (Throwable $e) { $sv_on = false; }
    $sv_filas = [];
    if ($sv_on) {
        try { $sv_filas = RitmoAsesor::empresa((int)$sv_eid); } catch (Throwable $e) { $sv_filas = []; }
    }
    $sv_suc[$sv_eid] = ['cfg' => $sv_cfg, 'mesa' => $sv_on, 'filas' => $sv_filas];
}

// ── Datos: score mensual ────────────────────────────────────
$sv_sc = []; $sv_meses = [];
foreach (array_keys($empresas_cfg) as $sv_eid) {
    foreach (ActividadScore::promedio_mensual((int)$sv_eid, 6) as $sv_r) {
        $sv_meses[$sv_r['mes']] = true;
        $sv_sc[$sv_eid][(int)$sv_r['usuario_id']]['nombre'] = $sv_r['asesor'];
        $sv_sc[$sv_eid][(int)$sv_r['usuario_id']]['m'][$sv_r['mes']] = $sv_r;
    }
}
krsort($sv_meses);
$sv_cols = array_slice(array_keys($sv_meses), 0, 6);
$SV_MES = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];

$sv_col = ['rojo' => '#ef4444', 'amarillo' => '#f59e0b', 'verde' => '#22c55e'];
// Un pilar: punto de SU color + etiqueta + texto. Mismo criterio que el
// dashboard del dueño: si el texto empieza con ✓ va apagado, no en color.
$sv_pil = function (string $estado, string $label, string $txt) use ($sv_col): string {
    $c  = $sv_col[$estado] ?? '#52525b';
    $ok = str_starts_with($txt, '✓');
    return '<div style="display:flex;align-items:baseline;gap:8px;margin-top:4px;font:500 12.5px \'Inter\',sans-serif">'
         . '<span style="width:8px;height:8px;border-radius:50%;background:' . $c . ';flex:none;align-self:center"></span>'
         . '<span style="color:var(--t2);font-weight:700;min-width:96px">' . $label . '</span>'
         . '<span style="color:' . ($ok ? 'var(--t3)' : $c) . '">' . e($txt) . '</span></div>';
};
$sv_scol = function (?float $v): string {
    if ($v === null) return 'var(--t3)';
    if ($v >= 86) return '#16a34a';
    if ($v >= 61) return '#22c55e';
    if ($v >= 31) return '#f59e0b';
    return '#ef4444';
};
$sv_hay_ritmo = (bool)array_filter($sv_suc, fn($s) => !empty($s['filas']));
?>

<!-- ══ SUPERVISIÓN: RENDIMIENTO DEL EQUIPO POR SUCURSAL ══════ -->
<div class="sec">
    <div class="sec-hdr">
        <div class="sec-title">Rendimiento del equipo</div>
        <div class="sec-count">5 pilares · ventana del ciclo de cada sucursal</div>
    </div>

    <?php if (!$sv_hay_ritmo): ?>
    <div class="tbl-card" style="padding:20px;text-align:center;color:var(--t3);font:400 13px 'Inter',sans-serif">
        Ninguna sucursal tiene la Mesa de Trabajo activa, que es de donde salen estos indicadores.
    </div>
    <?php endif; ?>

    <?php foreach ($sv_suc as $sv_eid => $sv_s):
        if (!$sv_s['filas']) continue;
        $sv_alerta = count(array_filter($sv_s['filas'], fn($x) => $x['semaforo'] !== 'verde'));
    ?>
    <div class="tbl-card" style="margin-bottom:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:9px;padding:10px 14px;border-bottom:1px solid var(--border)">
            <span class="tag" style="background:<?= $sv_s['cfg']['color'] ?>"><?= e($sv_s['cfg']['short']) ?></span>
            <strong style="font:700 14px 'Inter',sans-serif;color:var(--text)"><?= e($sv_s['cfg']['nombre']) ?></strong>
            <?php if ($sv_alerta > 0): ?>
            <span style="font:700 11px 'Inter',sans-serif;color:#ef4444"><?= $sv_alerta ?> por atender</span>
            <?php endif; ?>
        </div>

        <?php foreach ($sv_s['filas'] as $sv_f):
            $sv_c = $sv_col[$sv_f['semaforo']] ?? '#52525b';
        ?>
        <div style="display:flex;align-items:flex-start;gap:12px;padding:13px 14px;border-bottom:1px solid var(--border)">
            <span style="width:12px;height:12px;border-radius:50%;background:<?= $sv_c ?>;flex:none;margin-top:4px"></span>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px">
                    <span style="font:700 14px 'Inter',sans-serif;color:var(--text)"><?= e($sv_f['nombre']) ?></span>
                    <?php if (!empty($sv_f['flag'])): ?>
                    <span style="font:700 10px 'Inter',sans-serif;color:#fca5a5;background:rgba(239,68,68,.15);padding:2px 8px;border-radius:999px">no sigue el proceso<?= !empty($sv_f['flag_pilares']) ? ': ' . e($sv_f['flag_pilares']) : '' ?></span>
                    <?php endif; ?>
                    <button type="button" class="rt-rep-btn"
                            data-uid="<?= (int)$sv_f['usuario_id'] ?>"
                            data-eid="<?= (int)$sv_eid ?>"
                            data-name="<?= e($sv_f['nombre']) ?>"
                            style="margin-left:auto;flex:none;font:800 11px 'Inter',sans-serif;color:#fff;background:linear-gradient(135deg,#1a5c38,#2ea043);border:0;border-radius:999px;padding:6px 13px;cursor:pointer">✨ Generar reporte</button>
                </div>
                <?= $sv_pil($sv_f['conv_estado'],  'Conversión',  $sv_f['conv_txt']) ?>
                <?= $sv_pil($sv_f['desc_estado'],  'Descartadas', $sv_f['desc_txt']) ?>
                <?= $sv_pil($sv_f['citas_estado'], 'Citas',       $sv_f['citas_txt']) ?>
                <?= $sv_pil($sv_f['venc_estado'],  'Seguimiento', $sv_f['venc_txt']) ?>
                <?= $sv_pil($sv_f['cont_estado'],  'Contacto',    $sv_f['cont_txt']) ?>
                <div style="font:600 12.5px 'Inter',sans-serif;color:<?= $sv_f['semaforo'] === 'verde' ? 'var(--t3)' : $sv_c ?>;margin-top:7px">→ <?= e($sv_f['motivo']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php if ($sv_hay_ritmo): ?>
    <div style="font:400 11.5px 'Inter',sans-serif;color:var(--t3);line-height:1.55;padding:2px 2px 0">
        <strong style="color:var(--t2)">5 pilares</strong>, cada uno con su semáforo:
        <b style="color:var(--t2)">Conversión</b> (cerró vs cotizaciones) ·
        <b style="color:var(--t2)">Descartadas</b> (vs cierres, sin cita, muy rápido) ·
        <b style="color:var(--t2)">Citas</b> (su ritmo de agendar) ·
        <b style="color:var(--t2)">Seguimiento</b> (el cronómetro de vencidas) ·
        <b style="color:var(--t2)">Contacto</b> (a cuántos no le contestan).
        Medido contra las reglas de la Mesa — hechos, no acusaciones.
    </div>
    <?php endif; ?>
</div>

<!-- ══ SUPERVISIÓN: SCORE MENSUAL ════════════════════════════ -->
<div class="sec">
    <div class="sec-hdr">
        <div class="sec-title">Score mensual por asesor</div>
        <div class="sec-count">promedio real del mes</div>
    </div>

    <?php if (!$sv_cols): ?>
    <div class="tbl-card" style="padding:20px;text-align:center;color:var(--t3);font:400 13px 'Inter',sans-serif">
        Todavía no hay historial diario de scores. Se llena solo conforme se usa el sistema.
    </div>
    <?php else: ?>
    <?php foreach ($empresas_cfg as $sv_eid => $sv_cfg):
        $sv_ases = $sv_sc[$sv_eid] ?? [];
        if (!$sv_ases) continue;
        uasort($sv_ases, function ($a, $b) {
            $pa = array_sum(array_column($a['m'], 'score_prom')) / max(count($a['m']), 1);
            $pb = array_sum(array_column($b['m'], 'score_prom')) / max(count($b['m']), 1);
            return $pb <=> $pa;
        });
    ?>
    <div class="tbl-card" style="margin-bottom:12px">
    <table>
    <thead><tr>
        <th><span class="tag" style="background:<?= $sv_cfg['color'] ?>"><?= e($sv_cfg['short']) ?></span> <?= e($sv_cfg['nombre']) ?></th>
        <?php foreach ($sv_cols as $sv_m): [$sv_a, $sv_mm] = explode('-', $sv_m); ?>
        <th class="r"><?= $SV_MES[(int)$sv_mm] ?> <?= substr($sv_a, 2) ?></th>
        <?php endforeach; ?>
        <th class="r">Promedio</th>
    </tr></thead>
    <tbody>
    <?php foreach ($sv_ases as $sv_a2):
        $sv_v = array_map(fn($f) => (float)$f['score_prom'], $sv_a2['m']);
        $sv_p = $sv_v ? array_sum($sv_v) / count($sv_v) : null;
    ?>
    <tr>
        <td style="font-weight:600"><?= e($sv_a2['nombre']) ?></td>
        <?php foreach ($sv_cols as $sv_m):
            $sv_f2 = $sv_a2['m'][$sv_m] ?? null;
            $sv_val = $sv_f2 ? (float)$sv_f2['score_prom'] : null;
        ?>
        <td class="r mono">
            <?php if ($sv_val === null): ?><span style="color:var(--t3)">—</span>
            <?php else: ?>
                <span style="font-weight:800;color:<?= $sv_scol($sv_val) ?>"><?= number_format($sv_val, 1) ?></span>
                <div style="font-size:10px;color:var(--t3)"><?= (int)$sv_f2['score_min'] ?>–<?= (int)$sv_f2['score_max'] ?> · <?= (int)$sv_f2['dias'] ?>d</div>
            <?php endif; ?>
        </td>
        <?php endforeach; ?>
        <td class="r mono" style="font-weight:800;color:<?= $sv_scol($sv_p) ?>"><?= $sv_p === null ? '—' : number_format($sv_p, 1) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php endforeach; ?>
    <div style="font:400 11.5px 'Inter',sans-serif;color:var(--t3);line-height:1.55">
        El score del termómetro es una ventana móvil de 15 días que se sobrescribe; esto es el
        promedio de los puntos diarios, que sí es el mes. Bajo cada número, el rango
        (mínimo–máximo) y los días con registro: pocos días = promedio menos confiable.
    </div>
    <?php endif; ?>
</div>

<!-- ── Reporte del Director: el MISMO modal del dashboard ──── -->
<div id="rt-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.66);padding:20px;overflow:auto">
  <div style="max-width:640px;margin:24px auto;background:var(--white);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.5);overflow:hidden">
    <div style="display:flex;align-items:center;gap:10px;padding:13px 16px;border-bottom:1px solid var(--border)">
      <strong id="rt-modal-title" style="font:800 15px var(--body);color:var(--text)">Reporte</strong>
      <span class="rt-ai-badge">✨ CotizaCloud AI</span>
      <span style="flex:1"></span>
      <button type="button" onclick="rtPrint()" style="font:700 11px var(--body);color:var(--t2);background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:5px 10px;cursor:pointer">Imprimir</button>
      <button type="button" onclick="rtClose()" style="font:700 16px var(--body);color:var(--t3);background:none;border:0;cursor:pointer;line-height:1">✕</button>
    </div>
    <div id="rt-body" style="padding:16px;min-height:120px"></div>
  </div>
</div>

<style id="rt-css">
  /* El reporte se lee en CLARO aunque el ejecutivo sea oscuro: es un documento
     para leer e imprimir, y así queda idéntico al que ve el dueño. Las
     variables se redefinen SOLO dentro del modal. */
  #rt-modal{--bg:#f4f4f0;--white:#fff;--border:#e2e2dc;--text:#1a1a18;--t2:#4a4a46;--t3:#6a6a64;
            --body:'Inter',system-ui,-apple-system,'Segoe UI',sans-serif}
  #rt-modal .rr{font:400 13px var(--body);color:var(--text);line-height:1.5}
  #rt-modal .rr-hd{display:flex;align-items:center;gap:12px;margin-bottom:12px}
  #rt-modal .rr-k{font:700 10px var(--body);letter-spacing:.05em;text-transform:uppercase;color:#1a5c38}
  #rt-modal .rr-name{font:800 18px var(--body);color:var(--text);letter-spacing:-.01em}
  #rt-modal .rr-score{margin-left:auto;text-align:center;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:5px 12px}
  #rt-modal .rr-sn{font:800 22px var(--body);color:var(--text);line-height:1}
  #rt-modal .rr-sl{font:700 9px var(--body);text-transform:uppercase;color:var(--t3);letter-spacing:.05em}
  #rt-modal .rr-dims{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px}
  #rt-modal .rr-dim{font:600 11px var(--body);color:var(--t2);background:var(--bg);border:1px solid var(--border);border-radius:999px;padding:3px 9px}
  #rt-modal .rr-dim b{color:var(--text)}
  #rt-modal .rr-note{font:400 10.5px var(--body);color:var(--t3);margin-bottom:14px;line-height:1.4}
  #rt-modal .rr-sec{margin-bottom:14px}
  #rt-modal .rr-st{font:800 11px var(--body);text-transform:uppercase;letter-spacing:.05em;color:var(--t2);margin-bottom:6px;border-bottom:1px solid var(--border);padding-bottom:4px}
  #rt-modal .rr-list{margin:0;padding-left:18px} #rt-modal .rr-list li{margin:2px 0}
  #rt-modal .rr-foco{margin-bottom:8px;padding:8px 11px;background:#fbe9e9;border-radius:8px}
  #rt-modal .rr-ft{font:750 12.5px var(--body);color:#b91c1c}
  #rt-modal .rr-casos{margin:5px 0 0;padding-left:16px;font-variant-numeric:tabular-nums}
  #rt-modal .rr-casos li{margin:1px 0;color:var(--t2);font-size:12px}
  .rt-ai-badge{background:linear-gradient(135deg,#1a5c38,#2ea043);color:#fff;font:800 10px 'Inter',sans-serif;padding:4px 10px;border-radius:999px;letter-spacing:.04em;white-space:nowrap}
  #rt-modal .rr-sub{list-style:none;color:var(--t3);font-size:11.5px;margin-left:-6px}
  #rt-modal .rr-consejo{background:linear-gradient(135deg,rgba(26,92,56,.09),rgba(46,160,67,.05));border:1px solid rgba(26,92,56,.25);border-radius:10px;padding:11px 13px;margin-bottom:14px}
  #rt-modal .rr-consejo .rr-st{color:#1a5c38;border-bottom:0;padding-bottom:0;margin-bottom:5px}
  #rt-modal .rr-consejo .rr-list{padding-left:0;list-style:none}
  #rt-modal .rr-consejo .rr-list li{font:600 13px var(--body);color:var(--text);margin:0}
  #rt-modal .rr-pil{display:flex;align-items:baseline;gap:8px;font:500 13px var(--body);margin:4px 0}
  #rt-modal .rr-pil b{color:var(--t2);min-width:92px;font-weight:750}
  #rt-modal .rr-dot{width:9px;height:9px;border-radius:50%;flex:none;align-self:center}
  #rt-modal .rr-tip{background:linear-gradient(135deg,rgba(217,119,6,.10),rgba(245,158,11,.05));border:1px solid rgba(217,119,6,.28);border-radius:10px;padding:11px 13px;margin-bottom:14px}
  #rt-modal .rr-tip-h{font:800 11px var(--body);text-transform:uppercase;letter-spacing:.05em;color:#b45309;margin-bottom:4px}
  #rt-modal .rr-tip-b{font:500 13px var(--body);color:var(--text);line-height:1.5}
  #rt-modal .rr-foot{font:400 10.5px var(--body);color:var(--t3);border-top:1px solid var(--border);padding-top:8px;margin-top:6px}
</style>

<script>
(function(){
  const CSRF = <?= json_encode(csrf_token()) ?>;
  let uid = 0, eid = 0;
  const $ = id => document.getElementById(id);

  document.querySelectorAll('.rt-rep-btn').forEach(b => b.addEventListener('click', () => {
    uid = parseInt(b.dataset.uid, 10);
    eid = parseInt(b.dataset.eid, 10);   // el supervisor cruza sucursales: la empresa viaja con el botón
    $('rt-modal-title').textContent = 'Reporte — ' + b.dataset.name;
    $('rt-body').innerHTML = '';
    $('rt-modal').style.display = 'block';
    rtGen();
  }));

  window.rtClose = () => { $('rt-modal').style.display = 'none'; };

  // Imprimir desde una ventana LIMPIA con solo el reporte: con
  // 'body *{visibility:hidden}' el ejecutivo entero seguía paginando en blanco
  // y el reporte salía recortado varias páginas abajo.
  window.rtPrint = function () {
    var cont = $('rt-body');
    if (!cont || !cont.innerHTML.trim()) return;
    var css = (document.getElementById('rt-css') || {}).textContent || '';
    var w = window.open('', '_blank');
    if (!w) { window.print(); return; }
    var titulo = ($('rt-modal-title') || {}).textContent || 'Reporte';
    w.document.open();
    w.document.write(
      '<!doctype html><html lang="es"><head><meta charset="utf-8">' +
      '<title>' + titulo.replace(/[<>&]/g, '') + '</title><style>' +
      'html,body{margin:0;padding:0;background:#fff}' +
      '@page{margin:14mm}' +
      '#rt-modal{padding:0;position:static}#rt-modal>div{max-width:100%;margin:0;box-shadow:none}' +
      '#rt-body{padding:0}' +
      '.rr-sec,.rr-tip,.rr-pil{break-inside:avoid;page-break-inside:avoid}' +
      '*{-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
      css +
      '</style></head><body><div id="rt-modal"><div><div id="rt-body">' +
      cont.innerHTML +
      '</div></div></div></body></html>'
    );
    w.document.close();
    var go = function () { try { w.focus(); w.print(); } catch (e) {} };
    if (w.document.readyState === 'complete') setTimeout(go, 120);
    else w.onload = function () { setTimeout(go, 120); };
  };

  $('rt-modal').addEventListener('click', e => { if (e.target === $('rt-modal')) rtClose(); });

  window.rtGen = async function(){
    if (!uid) return;
    $('rt-body').innerHTML = '<div style="padding:24px;text-align:center;color:#6a6a64;font:500 13px Inter,sans-serif">Cruzando la data del asesor…</div>';
    try {
      const r = await fetch('/api/reporte-asesor', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF,'Accept':'application/json'},
        body: JSON.stringify({ asesor_id: uid, empresa_id: eid })
      });
      const d = await r.json();
      if (!d.ok) { $('rt-body').innerHTML = '<div style="padding:20px;color:#b91c1c;font:600 13px Inter,sans-serif">No se pudo generar ('+(d.error||'error')+').</div>'; }
      else {
        let banner = '';
        if (d.data.cache) {
          var f = d.data.fecha ? (' · generado el ' + String(d.data.fecha).slice(0,16).replace('T',' ')) : '';
          banner = '<div style="margin-bottom:10px;padding:7px 10px;background:#f4f4f0;border-radius:7px;font:500 11px Inter,sans-serif;color:#6a6a64">'+(d.msg||'Reporte de esta semana.')+f+'</div>';
        }
        $('rt-body').innerHTML = banner + d.data.html;
      }
    } catch(e){ $('rt-body').innerHTML = '<div style="padding:20px;color:#b91c1c">Error de red.</div>'; }
  };
})();
</script>
