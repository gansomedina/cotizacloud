<?php
// ============================================================
//  CotizaCloud — modules/config/_catalogo_inmuebles.php
//  Partial: tab Catálogo (Propiedades) para giro=inmuebles
//  Included from config/index.php when empresa.giro === 'inmuebles'
// ============================================================
defined('COTIZAAPP') or die;

$q_cat = trim($_GET['q_cat'] ?? '');
$propiedades = DB::query(
    "SELECT a.id, a.titulo, a.descripcion, a.precio, a.activo,
            p.tipo_operacion, p.tipo_propiedad, p.m2_terreno, p.m2_construccion,
            p.recamaras, p.banos, p.fotos
     FROM articulos a
     LEFT JOIN propiedades p ON p.articulo_id = a.id
     WHERE a.empresa_id = ? AND a.activo = 1
     " . ($q_cat ? "AND (a.titulo LIKE ? OR a.descripcion LIKE ?)" : "") . "
     ORDER BY a.id DESC",
    $q_cat ? [$empresa_id, "%$q_cat%", "%$q_cat%"] : [$empresa_id]
);

$tipo_op_labels = ['venta'=>'Venta','renta'=>'Renta','renta_temporal'=>'Renta temporal'];
$tipo_prop_labels = ['casa'=>'Casa','departamento'=>'Depto','terreno'=>'Terreno','local_comercial'=>'Local','oficina'=>'Oficina','bodega'=>'Bodega'];
?>

<div class="tab-panel <?= $tab_activo==='catalogo'?'on':'' ?>" id="panel-catalogo">

  <div class="search-bar">
    <span class="search-ico"><?= ico('search', 16, '#6a6a64') ?></span>
    <input type="text" placeholder="Buscar propiedad…" id="qCat" value="<?= e($q_cat) ?>"
           oninput="debounce(()=>buscarTab('q_cat',this.value),280)">
  </div>

  <div class="card">
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Propiedad</th>
            <th>Tipo</th>
            <th>Precio</th>
            <th style="text-align:right">Acciones</th>
          </tr>
        </thead>
        <tbody id="tblCat">
          <?php foreach ($propiedades as $prop):
              $fotos = $prop['fotos'] ? json_decode($prop['fotos'], true) : [];
              $op = $tipo_op_labels[$prop['tipo_operacion'] ?? 'venta'] ?? 'Venta';
              $tp = $tipo_prop_labels[$prop['tipo_propiedad'] ?? 'casa'] ?? 'Casa';
              $detalles = [];
              if ($prop['m2_terreno'])      $detalles[] = number_format($prop['m2_terreno'],0) . 'm² ter';
              if ($prop['m2_construccion']) $detalles[] = number_format($prop['m2_construccion'],0) . 'm² con';
              if ($prop['recamaras'])       $detalles[] = $prop['recamaras'] . ' rec';
              if ($prop['banos'])           $detalles[] = number_format($prop['banos'],1) . ' baños';
          ?>
          <tr data-art-id="<?= (int)$prop['id'] ?>">
            <td>
              <div class="tbl-name"><?= e($prop['titulo']) ?></div>
              <?php if ($prop['descripcion']): ?>
              <div class="tbl-desc"><?= e(mb_substr(strip_tags($prop['descripcion']), 0, 80)) ?></div>
              <?php endif; ?>
              <?php if ($detalles): ?>
              <div class="tbl-desc" style="color:var(--g);font-size:11px;margin-top:2px"><?= e(implode(' · ', $detalles)) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <span class="tbl-sku"><?= e($tp) ?></span>
              <div style="font-size:11px;color:var(--t3)"><?= e($op) ?></div>
            </td>
            <td><span class="tbl-price"><?= fmt_cfg((float)$prop['precio']) ?></span></td>
            <td>
              <div class="tbl-actions">
                <button class="tbl-btn"
                        onclick='editarPropiedad(<?= (int)$prop["id"] ?>, <?= htmlspecialchars(json_encode([
                            "titulo"=>$prop["titulo"],
                            "descripcion"=>$prop["descripcion"]??'',
                            "precio"=>$prop["precio"],
                            "tipo_operacion"=>$prop["tipo_operacion"]??'venta',
                            "tipo_propiedad"=>$prop["tipo_propiedad"]??'casa',
                            "m2_terreno"=>$prop["m2_terreno"],
                            "m2_construccion"=>$prop["m2_construccion"],
                            "recamaras"=>$prop["recamaras"],
                            "banos"=>$prop["banos"],
                        ]), ENT_QUOTES) ?>)'
                        title="Editar">✎</button>
                <button class="tbl-btn del" onclick="eliminarPropiedad(<?= (int)$prop['id'] ?>, this)" title="Eliminar">✕</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($propiedades)): ?>
          <tr><td colspan="4" style="text-align:center;padding:28px;color:var(--t3);font-size:13px">Sin propiedades en el catálogo</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <button class="add-row-btn" onclick="nuevaPropiedad()">+ Nueva propiedad</button>

</div><!-- /panel-catalogo -->

<!-- ══ SHEET: PROPIEDAD ══════════════════════════════════════ -->
<div class="sh-overlay" id="ov-shProp" onclick="closeSheet('shProp')"></div>
<div class="bottom-sheet" id="shProp">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shPropTit">Propiedad</div>
    <button class="sh-close" onclick="closeSheet('shProp')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shPropId" value="">

    <div class="sh-field">
      <div class="sh-lbl">Nombre de la propiedad <span style="color:var(--danger)">*</span></div>
      <input class="sh-input" type="text" id="shPropTitulo" placeholder="Casa 3 rec, Col. Fuente de Piedra" maxlength="255">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0" class="sh-field">
      <div style="padding:12px 16px;border-right:1px solid var(--bd2)">
        <div class="sh-lbl">Tipo de operación</div>
        <select class="sh-input" id="shPropOp" style="padding:10px 12px">
          <option value="venta">Venta</option>
          <option value="renta">Renta</option>
          <option value="renta_temporal">Renta temporal</option>
        </select>
      </div>
      <div style="padding:12px 16px">
        <div class="sh-lbl">Tipo de propiedad</div>
        <select class="sh-input" id="shPropTipo" style="padding:10px 12px">
          <option value="casa">Casa</option>
          <option value="departamento">Departamento</option>
          <option value="terreno">Terreno</option>
          <option value="local_comercial">Local comercial</option>
          <option value="oficina">Oficina</option>
          <option value="bodega">Bodega</option>
        </select>
      </div>
    </div>

    <div class="sh-field">
      <div class="sh-lbl">Precio</div>
      <input class="sh-input" type="number" id="shPropPrecio" placeholder="0.00" min="0" step="0.01">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0" class="sh-field">
      <div style="padding:12px 16px;border-right:1px solid var(--bd2)">
        <div class="sh-lbl">m² terreno</div>
        <input class="sh-input" type="number" id="shPropM2T" placeholder="—" min="0" step="0.01">
      </div>
      <div style="padding:12px 16px">
        <div class="sh-lbl">m² construcción</div>
        <input class="sh-input" type="number" id="shPropM2C" placeholder="—" min="0" step="0.01">
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0" class="sh-field">
      <div style="padding:12px 16px;border-right:1px solid var(--bd2)">
        <div class="sh-lbl">Recámaras</div>
        <input class="sh-input" type="number" id="shPropRec" placeholder="—" min="0" step="1">
      </div>
      <div style="padding:12px 16px">
        <div class="sh-lbl">Baños</div>
        <input class="sh-input" type="number" id="shPropBanos" placeholder="—" min="0" step="0.5">
      </div>
    </div>

    <div class="sh-field" style="border-bottom:none">
      <div class="sh-lbl">Descripción / Dirección</div>
      <textarea class="sh-input" id="shPropDesc" style="min-height:80px;resize:none" oninput="autoResize(this)" placeholder="Dirección completa, características, amenidades…"></textarea>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="closeSheet('shProp')">Cancelar</button>
    <button class="sh-btn-save" onclick="guardarPropiedad()">Guardar propiedad</button>
  </div>
</div>

<script>
function nuevaPropiedad() {
    document.getElementById('shPropId').value = '';
    document.getElementById('shPropTit').textContent = 'Nueva propiedad';
    document.getElementById('shPropTitulo').value = '';
    document.getElementById('shPropOp').value = 'venta';
    document.getElementById('shPropTipo').value = 'casa';
    document.getElementById('shPropPrecio').value = '';
    document.getElementById('shPropM2T').value = '';
    document.getElementById('shPropM2C').value = '';
    document.getElementById('shPropRec').value = '';
    document.getElementById('shPropBanos').value = '';
    document.getElementById('shPropDesc').value = '';
    openSheet('shProp');
}
function editarPropiedad(id, data) {
    document.getElementById('shPropId').value = id;
    document.getElementById('shPropTit').textContent = 'Editar propiedad';
    document.getElementById('shPropTitulo').value = data.titulo;
    document.getElementById('shPropOp').value = data.tipo_operacion || 'venta';
    document.getElementById('shPropTipo').value = data.tipo_propiedad || 'casa';
    document.getElementById('shPropPrecio').value = data.precio;
    document.getElementById('shPropM2T').value = data.m2_terreno || '';
    document.getElementById('shPropM2C').value = data.m2_construccion || '';
    document.getElementById('shPropRec').value = data.recamaras || '';
    document.getElementById('shPropBanos').value = data.banos || '';
    document.getElementById('shPropDesc').value = data.descripcion || '';
    openSheet('shProp');
}
async function guardarPropiedad() {
    const id     = document.getElementById('shPropId').value;
    const titulo = document.getElementById('shPropTitulo').value.trim();
    if (!titulo) { alert('El nombre es obligatorio.'); return; }

    const payload = {
        titulo:          titulo,
        descripcion:     document.getElementById('shPropDesc').value.trim(),
        precio:          parseFloat(document.getElementById('shPropPrecio').value) || 0,
        tipo_operacion:  document.getElementById('shPropOp').value,
        tipo_propiedad:  document.getElementById('shPropTipo').value,
        m2_terreno:      parseFloat(document.getElementById('shPropM2T').value) || null,
        m2_construccion: parseFloat(document.getElementById('shPropM2C').value) || null,
        recamaras:       parseInt(document.getElementById('shPropRec').value) || null,
        banos:           parseFloat(document.getElementById('shPropBanos').value) || null,
    };

    const url = id ? '/config/propiedad/' + id : '/config/propiedad';
    try {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify(payload)
        });
        const d = await r.json();
        if (d.ok) { closeSheet('shProp'); location.reload(); }
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}
async function eliminarPropiedad(id, btn) {
    if (!confirm('¿Eliminar esta propiedad del catálogo?')) return;
    try {
        const r = await fetch('/config/propiedad/' + id + '/eliminar', {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        const d = await r.json();
        if (d.ok) btn.closest('tr')?.remove();
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}
</script>
