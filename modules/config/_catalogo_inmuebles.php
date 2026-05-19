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
                            "fotos"=>$fotos,
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
<div class="bottom-sheet" id="shProp" style="max-height:95vh">
  <div class="sh-handle"></div>
  <div class="sh-header">
    <div class="sh-title" id="shPropTit">Propiedad</div>
    <button class="sh-close" onclick="closeSheet('shProp')">✕</button>
  </div>
  <div class="sh-body">
    <input type="hidden" id="shPropId" value="">

    <!-- ── SECCIÓN 1: DATOS ── -->
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

    <div class="sh-field">
      <div class="sh-lbl">Descripción / Dirección</div>
      <textarea class="sh-input" id="shPropDesc" style="min-height:80px;resize:none" oninput="autoResize(this)" placeholder="Dirección completa, características, amenidades…"></textarea>
    </div>

    <div style="padding:4px 0 8px;text-align:right">
      <button id="btnGuardarDatos" onclick="guardarDatos()" style="padding:7px 20px;background:none;border:1.5px solid var(--g);border-radius:var(--r-sm);font:600 12px var(--body);color:var(--g);cursor:pointer">Guardar datos</button>
      <span id="shPropDatosOk" style="display:none;font:600 12px var(--body);color:var(--g);margin-left:8px">✓ Guardado</span>
    </div>

    <div class="sh-field" style="border-bottom:none">
      <div class="sh-lbl">Fotos <span style="color:var(--t3);font-weight:400">(máx 10)</span></div>
      <div id="shPropFotosList" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px"></div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <label style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;background:var(--bg);border:1.5px dashed var(--border);border-radius:var(--r-sm);cursor:pointer;font:500 12px var(--body);color:var(--t2)">
          + Agregar fotos
          <input type="file" id="shPropFotoInput" accept="image/jpeg,image/png,image/webp,image/gif" multiple style="display:none" onchange="agregarFotosProp(this)">
        </label>
        <button id="btnGuardarFotos" onclick="guardarFotos()" disabled style="padding:7px 16px;background:none;border:1.5px solid var(--border);border-radius:var(--r-sm);font:600 12px var(--body);color:var(--t3);cursor:not-allowed">Subir fotos</button>
        <span id="shPropFotoStatus" style="display:none;font:500 11px var(--body);color:var(--t3)"></span>
      </div>
      <div class="sh-note" style="margin-top:6px">JPG, PNG, WebP — máx <?= MAX_UPLOAD_MB ?>MB</div>
    </div>
  </div>
  <div class="sh-footer">
    <button class="sh-btn-cancel" onclick="cerrarProp()">Cancelar</button>
    <button class="sh-btn-save" id="btnGuardarProp" onclick="guardarPropiedad()">Guardar propiedad</button>
  </div>
</div>

<script>
var _propFotos = [];
var _propFotosPendientes = [];
var _propDatosGuardados = false;

function _actualizarBtnFotos() {
    var btn = document.getElementById('btnGuardarFotos');
    var tiene_id = !!document.getElementById('shPropId').value;
    var tiene_pendientes = _propFotosPendientes.length > 0;
    var habilitar = tiene_id && tiene_pendientes;
    btn.disabled = !habilitar;
    btn.style.cursor = habilitar ? 'pointer' : 'not-allowed';
    btn.style.borderColor = habilitar ? 'var(--g)' : 'var(--border)';
    btn.style.color = habilitar ? 'var(--g)' : 'var(--t3)';
    if (!tiene_id && tiene_pendientes) {
        btn.textContent = 'Guarda datos primero';
    } else {
        btn.textContent = tiene_pendientes ? 'Subir ' + _propFotosPendientes.length + ' foto' + (_propFotosPendientes.length>1?'s':'') : 'Subir fotos';
    }
}

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
    _propFotos = [];
    _propFotosPendientes = [];
    _propDatosGuardados = false;
    document.getElementById('shPropDatosOk').style.display = 'none';
    document.getElementById('btnGuardarDatos').textContent = 'Guardar datos';
    renderFotos();
    _actualizarBtnFotos();
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
    _propFotos = (data.fotos || []).slice();
    _propFotosPendientes = [];
    _propDatosGuardados = true;
    document.getElementById('shPropDatosOk').style.display = '';
    document.getElementById('btnGuardarDatos').textContent = 'Actualizar datos';
    renderFotos();
    _actualizarBtnFotos();
    openSheet('shProp');
}

function renderFotos() {
    var c = document.getElementById('shPropFotosList');
    c.innerHTML = '';
    _propFotos.forEach(function(f, i) {
        var url = '<?= UPLOADS_URL ?>/' + f;
        var d = document.createElement('div');
        d.style.cssText = 'position:relative;width:72px;height:54px;border-radius:6px;overflow:hidden;border:1px solid var(--border)';
        d.innerHTML = '<img src="' + url + '" style="width:100%;height:100%;object-fit:cover">'
            + '<button onclick="eliminarFotoProp(' + i + ')" style="position:absolute;top:2px;right:2px;width:18px;height:18px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;border:none;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1">✕</button>';
        c.appendChild(d);
    });
    _propFotosPendientes.forEach(function(f, i) {
        var d = document.createElement('div');
        d.style.cssText = 'position:relative;width:72px;height:54px;border-radius:6px;overflow:hidden;border:2px dashed var(--g-border)';
        d.innerHTML = '<img src="' + URL.createObjectURL(f) + '" style="width:100%;height:100%;object-fit:cover;opacity:.7">'
            + '<button onclick="quitarPendiente(' + i + ')" style="position:absolute;top:2px;right:2px;width:18px;height:18px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;border:none;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1">✕</button>';
        c.appendChild(d);
    });
}

function agregarFotosProp(input) {
    var files = Array.from(input.files);
    input.value = '';
    files.forEach(function(f) {
        if ((_propFotos.length + _propFotosPendientes.length) < 10) {
            _propFotosPendientes.push(f);
        }
    });
    renderFotos();
    _actualizarBtnFotos();
}

function quitarPendiente(idx) {
    _propFotosPendientes.splice(idx, 1);
    renderFotos();
    _actualizarBtnFotos();
}

async function eliminarFotoProp(idx) {
    var id = document.getElementById('shPropId').value;
    if (!id) return;
    try {
        var r = await fetch('/config/propiedad/' + id + '/foto/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ index: idx })
        });
        var d = await r.json();
        if (d.ok) { _propFotos = (d.data&&d.data.fotos)||d.fotos||[]; renderFotos(); _actualizarBtnFotos(); }
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}

function fotoStatus(msg) {
    var el = document.getElementById('shPropFotoStatus');
    if (msg) { el.textContent = msg; el.style.display = ''; }
    else { el.style.display = 'none'; }
}

async function guardarDatos() {
    var btn = document.getElementById('btnGuardarDatos');
    var id = document.getElementById('shPropId').value;
    var titulo = document.getElementById('shPropTitulo').value.trim();
    if (!titulo) { alert('El nombre es obligatorio.'); return; }
    btn.disabled = true; btn.textContent = 'Guardando...';
    var payload = {
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
    var url = id ? '/config/propiedad/' + id : '/config/propiedad';
    try {
        var r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN}, body:JSON.stringify(payload) });
        var d = await r.json();
        if (d.ok) {
            var newId = (d.data && d.data.id) || d.id;
            if (newId) document.getElementById('shPropId').value = newId;
            _propDatosGuardados = true;
            document.getElementById('shPropDatosOk').style.display = '';
            btn.textContent = 'Actualizar datos';
            btn.disabled = false;
            _actualizarBtnFotos();
            if (_propFotosPendientes.length === 0) {
                closeSheet('shProp'); location.reload();
            }
        } else { alert(d.error || 'Error.'); btn.textContent = id ? 'Actualizar datos' : 'Guardar datos'; btn.disabled = false; }
    } catch(e) { alert('Error de conexión.'); btn.textContent = id ? 'Actualizar datos' : 'Guardar datos'; btn.disabled = false; }
}

async function guardarFotos() {
    var id = document.getElementById('shPropId').value;
    if (!id || !_propFotosPendientes.length) return;
    var btn = document.getElementById('btnGuardarFotos');
    btn.disabled = true;
    var total = _propFotosPendientes.length;
    for (var i = 0; i < total; i++) {
        fotoStatus('Subiendo foto ' + (i+1) + ' de ' + total + '...');
        btn.textContent = 'Subiendo ' + (i+1) + '/' + total + '...';
        var fd = new FormData();
        fd.append('foto', _propFotosPendientes[i]);
        try {
            var r = await fetch('/config/propiedad/' + id + '/foto', {
                method: 'POST',
                headers: { 'X-CSRF-Token': CSRF_TOKEN },
                body: fd
            });
            var d = await r.json();
            if (d.ok) { _propFotos = (d.data&&d.data.fotos)||d.fotos||[]; }
            else { fotoStatus('Error: ' + (d.error || 'fallo')); btn.disabled = false; _actualizarBtnFotos(); return; }
        } catch(e) { fotoStatus('Error de conexión'); btn.disabled = false; _actualizarBtnFotos(); return; }
    }
    _propFotosPendientes = [];
    fotoStatus('');
    renderFotos();
    _actualizarBtnFotos();
    btn.textContent = 'Fotos subidas';
    btn.style.borderColor = 'var(--g)';
    btn.style.color = 'var(--g)';
}

function cerrarProp() { closeSheet('shProp'); if (_propDatosGuardados) location.reload(); }

async function guardarPropiedad() {
    var btn = document.getElementById('btnGuardarProp');
    btn.disabled = true; btn.textContent = 'Guardando...';
    if (!_propDatosGuardados) {
        var titulo = document.getElementById('shPropTitulo').value.trim();
        if (!titulo) { alert('El nombre es obligatorio.'); btn.disabled=false; btn.textContent='Guardar propiedad'; return; }
        var id = document.getElementById('shPropId').value;
        var payload = {
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
        try {
            var r = await fetch(id ? '/config/propiedad/'+id : '/config/propiedad', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN}, body:JSON.stringify(payload) });
            var d = await r.json();
            if (!d.ok) { alert(d.error||'Error.'); btn.disabled=false; btn.textContent='Guardar propiedad'; return; }
            var nid = (d.data && d.data.id) || d.id;
            if (nid) document.getElementById('shPropId').value = nid;
            _propDatosGuardados = true;
        } catch(e) { alert('Error de conexión.'); btn.disabled=false; btn.textContent='Guardar propiedad'; return; }
    }
    if (_propFotosPendientes.length > 0) {
        var pid = document.getElementById('shPropId').value;
        for (var i = 0; i < _propFotosPendientes.length; i++) {
            btn.textContent = 'Subiendo foto ' + (i+1) + '/' + _propFotosPendientes.length + '...';
            var fd = new FormData();
            fd.append('foto', _propFotosPendientes[i]);
            try {
                var r = await fetch('/config/propiedad/' + pid + '/foto', { method:'POST', headers:{'X-CSRF-Token':CSRF_TOKEN}, body:fd });
                var d = await r.json();
                if (!d.ok) { alert(d.error||'Error al subir foto.'); btn.disabled=false; btn.textContent='Guardar propiedad'; return; }
                _propFotos = (d.data&&d.data.fotos)||d.fotos||[];
            } catch(e) { alert('Error de conexión.'); btn.disabled=false; btn.textContent='Guardar propiedad'; return; }
        }
        _propFotosPendientes = [];
    }
    closeSheet('shProp'); location.reload();
}

async function eliminarPropiedad(id, btn) {
    if (!confirm('¿Eliminar esta propiedad del catálogo?')) return;
    try {
        var r = await fetch('/config/propiedad/' + id + '/eliminar', {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        var d = await r.json();
        if (d.ok) btn.closest('tr')?.remove();
        else alert(d.error || 'Error.');
    } catch(e) { alert('Error de conexión.'); }
}
</script>
