<?php
// ============================================================
//  CotizaApp — modules/ventas/lista.php
//  GET /ventas
// ============================================================

defined('COTIZAAPP') or die;

$empresa_id = EMPRESA_ID;
$empresa    = Auth::empresa();

$estado   = $_GET['estado'] ?? 'todas';
$busqueda = trim($_GET['q'] ?? '');
$orden    = $_GET['orden']  ?? 'reciente';

$estados_validos = ['todas','pendiente','parcial','pagada','entregada','cancelada'];
if (!in_array($estado, $estados_validos)) $estado = 'todas';

$solo_propias = !Auth::puede('ver_todas_ventas');

// ─── Conteos ─────────────────────────────────────────────
$cnt_w = ["v.empresa_id = ?"];
$cnt_p = [$empresa_id];
if ($solo_propias) { $cnt_w[] = "(v.usuario_id = ? OR v.vendedor_id = ?)"; $cnt_p[] = Auth::id(); $cnt_p[] = Auth::id(); }

$conteos = ['todas' => 0];
foreach (DB::query("SELECT estado, COUNT(*) n FROM ventas v WHERE " . implode(' AND ', $cnt_w) . " GROUP BY estado", $cnt_p) as $r) {
    $conteos[$r['estado']] = (int)$r['n'];
    $conteos['todas'] += (int)$r['n'];
}

// ─── Query principal ─────────────────────────────────────
$where  = ["v.empresa_id = ?"];
$params = [$empresa_id];
if ($estado !== 'todas') { $where[] = "v.estado = ?"; $params[] = $estado; }
if ($solo_propias)       { $where[] = "(v.usuario_id = ? OR v.vendedor_id = ?)"; $params[] = Auth::id(); $params[] = Auth::id(); }
if ($busqueda !== '') {
    $where[] = "(v.titulo LIKE ? OR v.numero LIKE ? OR cl.nombre LIKE ? OR cl.telefono LIKE ?)";
    $like    = '%' . $busqueda . '%';
    $params  = array_merge($params, [$like, $like, $like, $like]);
}
$where_sql = implode(' AND ', $where);
$order_sql = match($orden) {
    'antigua'    => 'v.created_at ASC',
    'monto_desc' => 'v.total DESC',
    'monto_asc'  => 'v.total ASC',
    default      => 'v.created_at DESC',
};

$ventas = DB::query(
    "SELECT v.id, v.numero, v.titulo, v.slug, v.estado,
            v.total, v.pagado, v.saldo, v.created_at,
            cl.nombre AS cnombre, cl.telefono AS ctel,
            COALESCE(uv.nombre, u.nombre) AS vendedor,
            (SELECT COUNT(*) FROM recibos r WHERE r.venta_id=v.id AND r.cancelado=0) AS num_pagos
     FROM ventas v
     LEFT JOIN clientes cl ON cl.id = v.cliente_id
     LEFT JOIN usuarios u  ON u.id = v.usuario_id
     LEFT JOIN usuarios uv ON uv.id = v.vendedor_id
     WHERE $where_sql ORDER BY $order_sql LIMIT 100",
    $params
);

// ─── Helper badge ─────────────────────────────────────────
function vst_badge(string $estado): string {
    $map = [
        'pendiente' => ['s-pendiente','Pendiente'],
        'parcial'   => ['s-parcial',  'Parcial'],
        'pagada'    => ['s-pagada',   'Pagada'],
        'entregada' => ['s-entregada','Entregada'],
        'cancelada' => ['s-cancelada','Cancelada'],
    ];
    [$cls, $lbl] = $map[$estado] ?? ['s-pendiente', ucfirst($estado)];
    return "<span class=\"status $cls\"><span class=\"status-dot\"></span>$lbl</span>";
}

$page_title = 'Ventas';
ob_start();
?>
<style>
/* STATUS */
.status{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:6px;font:700 13px var(--body);letter-spacing:.02em}
.status-dot{width:6px;height:6px;border-radius:3px;flex-shrink:0}
.s-pendiente{background:#f1f5f9;color:#475569}.s-pendiente .status-dot{background:#94a3b8}
.s-parcial{background:var(--amb-bg);color:var(--amb)}.s-parcial .status-dot{background:#f59e0b}
.s-pagada{background:var(--g-bg);color:var(--g)}.s-pagada .status-dot{background:var(--g)}
.s-entregada{background:var(--blue-bg);color:var(--blue)}.s-entregada .status-dot{background:var(--blue)}
.s-cancelada{background:var(--danger-bg);color:var(--danger)}.s-cancelada .status-dot{background:var(--danger)}

/* FILTROS */
.filter-bar{display:flex;gap:8px;margin-bottom:16px;overflow-x:auto;padding-bottom:2px;scrollbar-width:none}
.filter-bar::-webkit-scrollbar{display:none}
.filter-chip{padding:8px 16px;border-radius:20px;border:1px solid var(--border);background:var(--white);font:600 13px var(--body);color:var(--t2);cursor:pointer;white-space:nowrap;transition:all .12s;flex-shrink:0;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.filter-chip:hover{border-color:var(--g);color:var(--g)}
.filter-chip.active{background:var(--g);border-color:var(--g);color:#fff}
.fc-n{font:700 10px var(--body);background:rgba(0,0,0,.1);padding:1px 5px;border-radius:10px}
.filter-chip.active .fc-n{background:rgba(255,255,255,.25)}

/* TABLA WRAP */
.vt-wrap{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}

/* SEARCH */
.search-wrap{flex:1;min-width:200px;display:flex;align-items:center;gap:8px;position:relative;margin-bottom:12px}
.search-wrap input{width:100%;background:var(--white);border:1px solid var(--border);border-radius:var(--r-sm);padding:10px 14px 10px 38px;font:400 14px var(--body);color:var(--text);outline:none;transition:border-color .15s;box-shadow:var(--sh)}
.search-wrap input:focus{border-color:var(--g)}
.search-wrap input::placeholder{color:var(--t3)}
.search-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;color:var(--t3);pointer-events:none}

/* TABLA DESKTOP */
.vtbl-header{display:none}

/* FILA — mobile por defecto */
.venta-row{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;color:inherit}
.venta-row:last-child{border-bottom:none}
.venta-row:hover{background:#fafaf8}

/* Mobile: avatar + info + derecha */
.venta-av{width:42px;height:42px;border-radius:10px;background:var(--g);display:flex;align-items:center;justify-content:center;font:700 15px var(--body);color:#fff;flex-shrink:0}
.venta-info{flex:1;min-width:0}
.venta-num{font:600 13px var(--num);color:var(--t3)}
.venta-title{font:600 16px var(--body);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.venta-client{font:400 14px var(--body);color:var(--t3);margin-top:2px}
.progress-wrap{margin-top:5px}
.progress-bar{height:3px;border-radius:2px;background:var(--border);overflow:hidden}
.progress-fill{height:100%;border-radius:2px;background:var(--g)}
.venta-r{text-align:right;flex-shrink:0}
.venta-total{font:700 16px var(--num);color:var(--text)}
.venta-saldo{font-size:13px;margin-top:3px}
.saldo-ok{color:var(--g)}
.saldo-pend{color:var(--amb)}
/* Botones de acción: cuadrados chicos inline */
.act-btn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:7px;border:1px solid var(--border);background:var(--white);font-size:14px;cursor:pointer;text-decoration:none;color:var(--t2);transition:all .12s;flex-shrink:0}
.act-btn:hover{border-color:var(--g);background:var(--g-bg)}
.act-danger:hover{border-color:var(--danger)!important;background:var(--danger-bg)!important;color:var(--danger)!important}

/* DESKTOP grid — 5 columnas */
@media(min-width:761px){
  .vtbl-header{
    display:grid;
    grid-template-columns:minmax(0,2fr) 140px minmax(90px,1fr) 100px minmax(110px,1fr) 100px;
    align-items:center;padding:9px 20px;
    border-bottom:2px solid var(--border);background:var(--bg)
  }
  .vtbl-header span{font:700 12px var(--body);letter-spacing:.06em;text-transform:uppercase;color:var(--t3)}

  .venta-row{
    display:grid;
    grid-template-columns:minmax(0,2fr) 140px minmax(90px,1fr) 100px minmax(110px,1fr) 100px;
    align-items:center;gap:0;padding:14px 20px
  }


  /* Col 1: folio+título — sin avatar */
  .venta-av{display:none}
  .venta-info{min-width:0;padding-right:12px}
  .venta-num{font-size:12px}
  .venta-title{font-size:15px;margin-top:2px}
  .venta-client{display:none}
  .progress-wrap{display:none}

  /* Col 2: cliente */
  .venta-col-cliente{min-width:0;padding-right:10px}
  .venta-col-cliente .vc-nombre{font:500 14px var(--body);color:var(--t2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .venta-col-cliente .vc-fecha{font:400 12px var(--num);color:var(--t3);margin-top:3px}

  /* Col 3: asesor */
  .venta-col-asesor{min-width:0;padding-right:10px}
  .venta-col-asesor .vc-nombre{font:500 13px var(--body);color:var(--t3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

  /* Col 4: estatus */
  .venta-col-status{}

  /* Col 4: monto+saldo+barra */
  .venta-col-monto{padding-right:8px}

  /* Col 5: acciones — en la misma línea, alineadas a la derecha */
  .venta-col-accion{display:flex!important;justify-content:flex-end;align-items:center;gap:3px}

  /* Ocultar mobile */
  .venta-r{display:none!important}

  /* Cols desktop: mostrar */
  .venta-col-cliente,.venta-col-asesor,.venta-col-status,.venta-col-monto{display:block}
}

@media(max-width:760px){
  .venta-col-cliente,.venta-col-asesor,.venta-col-status,
  .venta-col-monto,.venta-col-accion{display:none}
  .venta-title{white-space:normal;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
}
</style>

<!-- ENCABEZADO -->
<div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:20px">
  <div>
    <h1 style="font:800 28px var(--body);letter-spacing:-.03em;margin:0;color:var(--text)">Ventas</h1>
    <p style="font:400 14px var(--body);color:var(--t3);margin:4px 0 0"><?= number_format($conteos['todas']) ?> <?= $conteos['todas'] === 1 ? 'venta' : 'ventas' ?> en total</p>
  </div>
</div>

<!-- FILTROS -->
<div class="filter-bar">
<?php
$elabels = ['todas'=>'Todas','pendiente'=>'Pendiente','parcial'=>'Parcial','pagada'=>'Pagada','entregada'=>'Entregada','cancelada'=>'Cancelada'];
foreach ($elabels as $k => $lbl):
    $n = $conteos[$k] ?? 0;
    if ($k !== 'todas' && $n === 0) continue;
    $qs = http_build_query(['estado'=>$k,'q'=>$busqueda,'orden'=>$orden]);
?>
<a href="/ventas?<?= $qs ?>" class="filter-chip <?= $estado===$k?'active':'' ?>"><?= $lbl ?> <span class="fc-n"><?= $n ?></span></a>
<?php endforeach; ?>
</div>

<!-- SEARCH -->
<div class="search-wrap">
  <span class="search-ico"><?= ico('search', 16, '#6a6a64') ?></span>
  <input type="text" id="srch" value="<?= e($busqueda) ?>" placeholder="Buscar venta, cliente, folio…" onkeydown="if(event.key==='Enter')fil('q',this.value)">
  <button onclick="fil('q',document.getElementById('srch').value)" style="padding:6px 14px;border-radius:var(--r-sm);border:1px solid var(--g);background:var(--g);color:#fff;font:600 13px var(--body);cursor:pointer;flex-shrink:0">Buscar</button>
</div>

<!-- TABLA -->
<div class="vt-wrap">

<?php if (empty($ventas)): ?>
<div style="text-align:center;padding:60px 20px;color:var(--t3)">
  <div style="font:700 18px var(--body);color:var(--t2);margin-bottom:6px"><?= $busqueda ? 'Sin resultados' : 'No hay ventas' ?></div>
  <div style="font:400 15px var(--body)"><?= $busqueda ? 'Prueba otro término' : 'Las ventas se crean automáticamente al aceptar una cotización.' ?></div>
</div>
<?php else: ?>

<div class="vtbl-header">
  <span>Proyecto / Folio</span>
  <span>Cliente</span>
  <span>Asesor</span>
  <span>Estatus</span>
  <span>Total / Saldo</span>
  <span style="text-align:right">Acciones</span>
</div>

<?php foreach ($ventas as $v):
  $pct      = $v['total'] > 0 ? min(100, round($v['pagado'] / $v['total'] * 100)) : 0;
  $total_f  = format_money($v['total'], $empresa['moneda']);
  $saldo_f  = format_money($v['saldo'], $empresa['moneda']);
  $url_vta  = url_publica('v/' . $v['slug']);
  $ini      = strtoupper(substr($v['cnombre'] ?? $v['titulo'], 0, 2));
  $fecha_f  = fecha_humana($v['created_at']);
?>
<div class="venta-row" onclick="window.location='/ventas/<?= (int)$v['id'] ?>'">

  <!-- mobile: avatar -->
  <div class="venta-av"><?= $ini ?></div>

  <!-- Col 1: folio + título -->
  <div class="venta-info">
    <div class="venta-num"><?= e($v['numero'] ?? 'VTA-'.$v['id']) ?></div>
    <div class="venta-title"><?= e($v['titulo']) ?></div>
    <div class="venta-client"><?= e($v['cnombre'] ?? '—') ?> · <?= $fecha_f ?></div>
    <div class="progress-wrap">
      <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
    </div>
  </div>

  <!-- Col 2: cliente (desktop) -->
  <div class="venta-col-cliente">
    <div class="vc-nombre"><?= e($v['cnombre'] ?? '—') ?></div>
    <div class="vc-fecha"><?= $fecha_f ?></div>
  </div>

  <!-- Col 3: asesor (desktop) -->
  <div class="venta-col-asesor">
    <div class="vc-nombre"><?= e($v['vendedor'] ?? '—') ?></div>
  </div>

  <!-- Col 4: estatus (desktop) -->
  <div class="venta-col-status"><?= vst_badge($v['estado']) ?></div>

  <!-- Col 4: monto + saldo (desktop) -->
  <div class="venta-col-monto">
    <div style="font:600 16px var(--num)"><?= $total_f ?></div>
    <div style="font:400 13px var(--body);margin-top:3px">
      <?php if ($v['saldo'] <= 0): ?>
        <span class="saldo-ok">✓ Pagado</span>
      <?php else: ?>
        <span class="saldo-pend"><?= $saldo_f ?> pendiente</span>
      <?php endif ?>
    </div>
    <div class="progress-bar" style="margin-top:4px"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
  </div>

  <!-- Col 5: acciones -->
  <div class="venta-col-accion" onclick="event.stopPropagation()">
    <a href="/ventas/<?= (int)$v['id'] ?>" class="act-btn" title="Editar"><?= ico('edit',12) ?></a>
    <a href="<?= e($url_vta) ?>" target="_blank" class="act-btn" title="Ver liga pública"><?= ico('link',12) ?></a>
    <?php if ($v['estado'] !== 'cancelada' && (float)$v['pagado'] <= 0): ?>
    <button class="act-btn act-danger" title="Cancelar venta"
      onclick="cancelarVenta(<?= (int)$v['id'] ?>)">✕</button>
    <?php endif ?>
  </div>

  <!-- mobile derecha -->
  <div class="venta-r">
    <div class="venta-total"><?= $total_f ?></div>
    <div class="venta-saldo <?= $v['saldo'] <= 0 ? 'saldo-ok' : 'saldo-pend' ?>">
      <?= $v['saldo'] <= 0 ? 'Pagado ✓' : $saldo_f . ' pendiente' ?>
    </div>
    <div style="margin-top:5px"><?= vst_badge($v['estado']) ?></div>
    <a href="<?= e($url_vta) ?>" target="_blank" onclick="event.stopPropagation()" class="liga-btn" style="margin-top:5px"><?= ico('link',12) ?> Liga</a>
  </div>

</div>
<?php endforeach ?>
<?php endif ?>
</div>

<script>
const CSRF_TOKEN='<?= csrf_token() ?>';
async function cancelarVenta(id){
  const motivo = prompt('Motivo de cancelación:');
  if (!motivo) return;
  const r = await fetch('/ventas/'+id+'/cancelar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({motivo})});
  const d = await r.json();
  if(d.ok) location.reload();
  else alert(d.error||'Error al cancelar');
}
function fil(k,v){const p=new URLSearchParams(window.location.search);if(v)p.set(k,v);else p.delete(k);if(k!=='p')p.delete('p');window.location='/ventas?'+p.toString()}
</script>
<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
