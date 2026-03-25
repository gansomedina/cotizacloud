<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/lista.php
// ============================================================
defined('COTIZAAPP') or die;

$usuario    = Auth::usuario();
$empresa    = Auth::empresa();
$empresa_id = EMPRESA_ID;

$estado   = $_GET['estado']  ?? 'todas';
$busqueda = trim($_GET['q']  ?? '');
$orden    = $_GET['orden']   ?? 'reciente';
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = 20;

$estados_validos = ['todas','borrador','enviada','vista','aceptada','rechazada','vencida','convertida','suspendida'];
if (!in_array($estado, $estados_validos)) $estado = 'todas';
$ordenes_validos = ['reciente','antigua','monto_asc','monto_desc','cliente'];
if (!in_array($orden, $ordenes_validos)) $orden = 'reciente';

$where  = ["c.empresa_id = ?"];
$params = [$empresa_id];
if ($estado !== 'todas') {
    if ($estado === 'vencida') {
        // Vencida no es un valor del ENUM — es un estado calculado
        $where[] = "c.estado IN ('enviada','vista') AND c.valida_hasta IS NOT NULL AND c.valida_hasta < NOW()";
    } elseif ($estado === 'suspendida') {
        $where[] = "c.suspendida = 1";
    } else {
        $where[] = "c.estado = ?";
        $params[] = $estado;
    }
}
if (!Auth::puede('ver_todas_cots')) { $where[] = "(c.usuario_id = ? OR c.vendedor_id = ?)"; $params[] = $usuario['id']; $params[] = $usuario['id']; }
if ($busqueda !== '') {
    $where[] = "(c.titulo LIKE ? OR c.numero LIKE ? OR cl.nombre LIKE ? OR cl.telefono LIKE ?)";
    $like = '%'.$busqueda.'%';
    $params = array_merge($params, [$like,$like,$like,$like]);
}
$where_sql = implode(' AND ', $where);
$order_sql = match($orden) {
    'antigua'    => 'c.created_at ASC',
    'monto_asc'  => 'c.total ASC',
    'monto_desc' => 'c.total DESC',
    'cliente'    => 'cl.nombre ASC',
    default      => 'c.created_at DESC',
};

// Conteos por estado
$cw = ["c.empresa_id = ?"]; $cp = [$empresa_id];
if (!Auth::puede('ver_todas_cots')) { $cw[] = "(c.usuario_id = ? OR c.vendedor_id = ?)"; $cp[] = $usuario['id']; $cp[] = $usuario['id']; }
$raw = DB::query("SELECT estado, COUNT(*) AS n FROM cotizaciones c WHERE ".implode(' AND ',$cw)." GROUP BY estado", $cp);
$conteos = ['todas' => 0, 'vencida' => 0];
foreach ($raw as $r) { $conteos[$r['estado']] = (int)$r['n']; $conteos['todas'] += (int)$r['n']; }
// Contar vencidas (calculado — enviada/vista con valida_hasta pasada)
$conteos['vencida'] = (int)DB::val(
    "SELECT COUNT(*) FROM cotizaciones c WHERE ".implode(' AND ',$cw)
    ." AND c.estado IN ('enviada','vista') AND c.valida_hasta IS NOT NULL AND c.valida_hasta < NOW()",
    $cp
);
// Contar suspendidas
$conteos['suspendida'] = (int)DB::val(
    "SELECT COUNT(*) FROM cotizaciones c WHERE ".implode(' AND ',$cw)." AND c.suspendida = 1",
    $cp
);

$total_rows = (int)DB::val("SELECT COUNT(*) FROM cotizaciones c LEFT JOIN clientes cl ON cl.id=c.cliente_id WHERE $where_sql", $params);
$pag = paginar($total_rows, $pagina, $por_pag);

// Query principal — incluye visitas y radar
$rows = DB::query(
    "SELECT c.id, c.numero, c.titulo, c.slug,
            -- Estado calculado: si valida_hasta venció y sigue enviada/vista → mostrar como vencida
            CASE
                WHEN c.estado IN ('enviada','vista') AND c.valida_hasta IS NOT NULL AND c.valida_hasta < NOW()
                THEN 'vencida'
                ELSE c.estado
            END AS estado,
            c.estado AS estado_real,
            c.total, c.created_at, c.valida_hasta, c.visitas,
            c.radar_bucket, c.radar_score, c.suspendida,
            cl.nombre AS cnombre, cl.telefono AS ctel,
            u.nombre AS asesor, c.vendedor_id,
            COALESCE(uv.nombre, u.nombre) AS vendedor
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id=c.cliente_id
     LEFT JOIN usuarios u  ON u.id=c.usuario_id
     LEFT JOIN usuarios uv ON uv.id=c.vendedor_id
     WHERE $where_sql ORDER BY $order_sql LIMIT ? OFFSET ?",
    array_merge($params, [$por_pag, $pag['offset']])
);

function radar_badge(?string $bucket, ?int $score, int $vistas = 0): string {
    if (!$bucket) return '';
    $map = [
        'onfire'           => [ico('red',10),'#991b1b','#fff1f2'],
        'inminente'        => [ico('orange',10),'#c2410c','#fff7ed'],
        'probable_cierre'  => [ico('yellow',10),'#92400e','#fffbeb'],
        'decision_activa'  => [ico('yellow',10),'#92400e','#fffbeb'],
        'validando_precio' => [ico('yellow',10),'#92400e','#fffbeb'],
        'prediccion_alta'  => [ico('green',10),'#166534','#f0fdf4'],
        'revision_profunda'=> [ico('blue',10),'#1d4ed8','#dbeafe'],
        'multi_persona'    => [ico('blue',10),'#1d4ed8','#dbeafe'],
        're_enganche_caliente' => [ico('fire',12,'#6d28d9'),'#6d28d9','#ede9fe'],
        're_enganche'      => [ico('purple',10),'#6d28d9','#ede9fe'],
        'regreso'          => [ico('purple',10),'#6d28d9','#ede9fe'],
        'revivio'          => [ico('purple',10),'#6d28d9','#ede9fe'],
        'hesitacion'       => [ico('gray',10),'#64748b','#f1f5f9'],
        'sobre_analisis'   => [ico('gray',10),'#64748b','#f1f5f9'],
        'enfriandose'      => [ico('gray',10),'#94a3b8','#f1f5f9'],
        'comparando'       => [ico('gray',10),'#94a3b8','#f1f5f9'],
        'no_abierta'       => [ico('x',10,'#dc2626'),'#dc2626','#fef2f2'],
    ];
    [$ico,$color,$bg] = $map[$bucket] ?? [ico('gray',10),'#64748b','#f1f5f9'];
    $lbl = ucwords(str_replace('_',' ',$bucket));
    $eye = $vistas > 0 ? ' '.ico('eye',10,$color).' '.$vistas : '';
    return "<span style=\"display:inline-flex;align-items:center;gap:4px;padding:2px 7px;border-radius:12px;font:700 10px var(--body);background:{$bg};color:{$color};white-space:nowrap\">{$ico} {$lbl}{$eye}</span>";
}

function st_badge(string $e, bool $suspendida = false): string {
    if ($suspendida) {
        return "<span class='st st-suspendida'><span class='st-dot'></span>Suspendida</span>";
    }
    $m = ['borrador'=>'st-borrador','enviada'=>'st-enviada','vista'=>'st-vista',
          'aceptada'=>'st-aceptada','rechazada'=>'st-rechazada','vencida'=>'st-vencida','convertida'=>'st-aceptada'];
    $c = $m[$e] ?? 'st-borrador';
    return "<span class='st {$c}'><span class='st-dot'></span>".ucfirst($e)."</span>";
}

function vence_html(?string $f): string {
    if (!$f) return '';
    $ts = strtotime($f); $d = ($ts - strtotime('today'))/86400; $txt = date('d M Y',$ts);
    if ($d < 0)  return "<span class='cot-vence hoy'>Venció: {$txt}</span>";
    if ($d == 0) return "<span class='cot-vence hoy'>Vence: hoy</span>";
    if ($d <= 3) return "<span class='cot-vence pronto'>Vence: {$txt}</span>";
    return "<span class='cot-vence'>Vence: {$txt}</span>";
}

$page_title = 'Cotizaciones';
ob_start();
?>
<style>
/* TOOLBAR */
.toolbar{display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;align-items:center}
.search-wrap{flex:1;min-width:200px;position:relative}
.search-wrap{display:flex;align-items:center;gap:8px}
.search-wrap input{width:100%;background:var(--white);border:1px solid var(--border);border-radius:var(--r-sm);padding:10px 14px 10px 38px;font:400 14px var(--body);color:var(--text);outline:none;transition:border-color .15s;box-shadow:var(--sh)}
.search-wrap input:focus{border-color:var(--g)}
.search-wrap input::placeholder{color:var(--t3)}
.search-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;color:var(--t3);pointer-events:none}
.sort-select{padding:10px 12px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:500 13px var(--body);color:var(--t2);cursor:pointer;outline:none;box-shadow:var(--sh)}

/* CHIPS */
.filter-bar{display:flex;gap:6px;margin-bottom:14px;overflow-x:auto;padding-bottom:2px;scrollbar-width:none}
.filter-bar::-webkit-scrollbar{display:none}
.chip{padding:7px 13px;border-radius:20px;border:1px solid var(--border);background:var(--white);font:600 12px var(--body);color:var(--t2);cursor:pointer;white-space:nowrap;transition:all .12s;flex-shrink:0;display:flex;align-items:center;gap:5px;text-decoration:none}
.chip.active{background:var(--g);border-color:var(--g);color:#fff}
.chip-count{font:700 10px var(--body);padding:1px 5px;border-radius:10px;background:var(--border);color:var(--t2)}
.chip.active .chip-count{background:rgba(255,255,255,.3);color:#fff}

/* RESULTADOS */
.results-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.results-count{font:500 12px var(--body);color:var(--t3)}

/* LISTA */
.cot-list{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}

/* STATUS BADGES */
.st{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;font:700 10px var(--body);white-space:nowrap}
.st-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}
.st-borrador{background:var(--slate-bg);color:var(--slate)}.st-borrador .st-dot{background:#94a3b8}
.st-enviada{background:var(--blue-bg);color:var(--blue)}.st-enviada .st-dot{background:var(--blue)}
.st-vista{background:var(--purple-bg);color:var(--purple)}.st-vista .st-dot{background:var(--purple)}
.st-aceptada{background:var(--g-light);color:var(--g)}.st-aceptada .st-dot{background:var(--g)}
.st-rechazada{background:var(--danger-bg);color:var(--danger)}.st-rechazada .st-dot{background:var(--danger)}
.st-vencida{background:var(--amb-bg);color:var(--amb)}.st-vencida .st-dot{background:#f59e0b}
.st-suspendida{background:#fef3c7;color:#92400e}.st-suspendida .st-dot{background:#d97706}

/* BOTONES */
.act-btn{height:30px;padding:0 12px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:600 13px var(--body);color:var(--t2);cursor:pointer;transition:all .12s;white-space:nowrap;display:inline-flex;align-items:center;gap:5px;text-decoration:none}
.act-btn:hover{border-color:var(--g);color:var(--g);background:var(--g-bg)}
.act-btn.danger:hover{border-color:var(--danger);color:var(--danger);background:var(--danger-bg)}

/* PAGINACION */
.pag-wrap{display:flex;align-items:center;justify-content:space-between;margin-top:14px;flex-wrap:wrap;gap:10px}
.pag-info{font:400 13px var(--num);color:var(--t3)}
.pag-btns{display:flex;align-items:center;gap:4px}
.pag-btn{min-width:34px;height:34px;padding:0 8px;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:600 13px var(--body);color:var(--t2);display:inline-flex;align-items:center;justify-content:center;transition:all .12s;text-decoration:none}
.pag-btn:hover{border-color:var(--g);color:var(--g)}
.pag-btn.active{background:var(--g);border-color:var(--g);color:#fff}
.pag-btn.disabled{opacity:.35;pointer-events:none}
.pag-sep{font:400 13px var(--num);color:var(--t3);padding:0 4px}

/* EMPTY */
.empty{display:flex;flex-direction:column;align-items:center;padding:48px 24px;text-align:center;color:var(--t3);gap:10px}
.empty-ico{font-size:36px;opacity:.4}
.empty-txt{font:500 14px var(--body)}

/* =============================================
   MOBILE — fila compacta, todo en 3 líneas
   ============================================= */
.tbl-header{display:none}

.cot-row{display:block;padding:9px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s}
.cot-row:last-child{border-bottom:none}
.cot-row:hover{background:#fafaf8}

/* Expand mobile */
.mob-expand{display:none;margin-top:8px;padding-top:10px;border-top:1px solid var(--border)}
.mob-expand.open{display:block}
.exp-meta{display:flex;flex-wrap:wrap;gap:4px 10px;margin-bottom:8px;font:400 12px var(--num);color:var(--t3)}
.exp-meta-item{white-space:nowrap}
.exp-meta-item + .exp-meta-item::before{content:'·';margin-right:10px;color:var(--border2)}
.exp-vence-old{color:var(--danger);font-weight:600}
.exp-vence-soon{color:#b45309;font-weight:600}
.exp-btns{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.exp-btn{padding:9px 0;border-radius:var(--r-sm);border:1px solid var(--border);background:var(--white);font:600 13px var(--body);color:var(--t2);cursor:pointer;text-align:center;text-decoration:none;display:block;transition:all .12s}
.exp-btn:hover{border-color:var(--g);color:var(--g);background:var(--g-bg)}
.exp-btn-danger:hover{border-color:var(--danger);color:var(--danger);background:var(--danger-bg)}

/* L1: título (izq) + estado (der) */
.mob-l1{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:2px}
.cot-titulo{font:600 14px var(--body);line-height:1.3;flex:1;min-width:0}

/* L2: cliente + tel */
.mob-l2{font:400 13px var(--body);color:var(--t2);margin-bottom:3px}
.mob-l2 .sep{color:var(--border2);margin:0 5px}
/* L2b: fecha vence en su propia línea */
.mob-vence{margin-bottom:4px}
.cot-vence{font:400 12px var(--num);color:var(--t3)}
.cot-vence.hoy{color:var(--danger);font-weight:600}
.cot-vence.pronto{color:#b45309;font-weight:600}

/* L3: monto (izq) + vistas + botones (der) */
.mob-l3{display:flex;align-items:center;justify-content:space-between}
.cot-monto{font:700 15px var(--num);color:var(--text)}
.mob-r{display:flex;align-items:center;gap:6px}
.cot-vistas{font:500 11px var(--body);color:var(--t3)}
.mob-actions{display:flex;gap:4px}
/* botones icon-only en mobile */
.mob-actions .act-btn{height:30px;width:34px;padding:0;justify-content:center;font-size:15px}

/* ocultar columnas desktop */
.cot-col-cliente,.cot-col-asesor,.cot-col-status,.cot-col-monto,.cot-col-fechas,.cot-col-vistas,.desk-actions{display:none}

/* =============================================
   DESKTOP — 5 columnas: título | cliente | estatus | monto+vistas | acciones
   ============================================= */
@media(min-width:641px){
  .sort-select{display:block}

  .tbl-header{
    display:grid;
    grid-template-columns:minmax(180px,2fr) minmax(120px,1.2fr) minmax(90px,1fr) 90px minmax(110px,1fr) 140px;
    align-items:center;padding:8px 18px;
    border-bottom:2px solid var(--border);background:var(--bg)
  }
  .tbl-header span{font:700 11px var(--body);letter-spacing:.06em;text-transform:uppercase;color:var(--t3)}

  .cot-row{
    display:grid;
    grid-template-columns:minmax(180px,2fr) minmax(120px,1.2fr) minmax(90px,1fr) 90px minmax(110px,1fr) 140px;
    align-items:center;padding:11px 18px;
  }

  /* col 1: número + título solamente */
  .cot-main{min-width:0;padding-right:12px}
  .cot-numero-desk{font:400 11px var(--num);color:var(--t3);display:block;margin-bottom:1px}
  .mob-l1{display:block;margin:0}
  .mob-l1 .st{display:none}
  .cot-titulo{font:600 14px var(--body);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block}
  .mob-l2,.mob-l3,.mob-expand,.mob-vence{display:none}

  /* col 2: cliente en su propia columna */
  .cot-col-cliente{display:block;min-width:0;padding-right:10px}
  .cot-col-cliente .cc-n{font:500 13px var(--body);color:var(--t2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .cot-col-cliente .cc-t{font:400 11px var(--num);color:var(--t3);margin-top:1px}
  .cot-col-asesor{display:block;min-width:0;padding-right:10px}
  .cot-col-asesor .cc-n{font:500 13px var(--body);color:var(--t3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

  /* col 3: estatus */
  .cot-col-status{display:block}

  /* col 4: monto + radar/vistas debajo */
  .cot-col-monto{display:block}
  .cot-monto-val{font:600 14px var(--num);color:var(--text)}
  .cot-monto-sub{font:400 11px var(--body);color:var(--t3);margin-top:3px;display:flex;align-items:center;gap:6px;flex-wrap:wrap}
  .cot-vistas-badge{font:500 11px var(--body);color:var(--t3)}

  /* col 5: acciones */
  .desk-actions{display:flex;gap:5px;justify-content:flex-end;flex-wrap:nowrap}
  .desk-actions .act-btn{height:28px;padding:0 9px;font-size:12px;white-space:nowrap}

  /* ocultar columnas innecesarias */
  .cot-col-fechas,.cot-col-vistas{display:none}
}

@media(max-width:640px){
  .sort-select{display:none}
}
</style>

<!-- Cabecera página -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
  <div>
    <h1 style="font:800 22px var(--body);letter-spacing:-.02em">Cotizaciones</h1>
    <p style="font:400 13px var(--body);color:var(--t3);margin-top:3px"><?= number_format($conteos['todas']) ?> en total</p>
  </div>
  <?php if (Auth::es_admin() || Auth::puede('crear_cotizaciones')): ?>
  <a href="/cotizaciones/nueva" class="btn btn-primary"><i data-feather="plus"></i> Nueva cotización</a>
  <?php endif; ?>
</div>

<!-- Toolbar -->
<div class="toolbar">
  <div class="search-wrap">
    <span class="search-ico"><?= ico('search', 16, '#6a6a64') ?></span>
    <input type="text" id="srchCot" placeholder="Buscar por cliente, teléfono, título, número…"
           value="<?= e($busqueda) ?>" onkeydown="if(event.key==='Enter')filtrar('q',this.value)">
    <button onclick="filtrar('q',document.getElementById('srchCot').value)" style="padding:6px 14px;border-radius:var(--r-sm);border:1px solid var(--g);background:var(--g);color:#fff;font:600 13px var(--body);cursor:pointer;flex-shrink:0">Buscar</button>
  </div>
  <select class="sort-select" onchange="filtrar('orden',this.value)">
    <option value="reciente"   <?= $orden==='reciente'   ?'selected':'' ?>>Más recientes</option>
    <option value="antigua"    <?= $orden==='antigua'    ?'selected':'' ?>>Más antiguas</option>
    <option value="monto_desc" <?= $orden==='monto_desc' ?'selected':'' ?>>Mayor monto</option>
    <option value="monto_asc"  <?= $orden==='monto_asc'  ?'selected':'' ?>>Menor monto</option>
    <option value="cliente"    <?= $orden==='cliente'    ?'selected':'' ?>>Cliente A–Z</option>
  </select>
</div>

<!-- Chips estado -->
<div class="filter-bar">
<?php
$chips = ['todas'=>'Todas','enviada'=>'Enviada','vista'=>'Vista','aceptada'=>'Aceptada',
          'rechazada'=>'Rechazada','vencida'=>'Vencida','suspendida'=>'Suspendida','borrador'=>'Borrador','convertida'=>'Convertida'];
foreach ($chips as $k => $lbl):
    $cnt = $conteos[$k] ?? 0;
    if ($k !== 'todas' && $cnt === 0) continue;
    $qs = http_build_query(['estado'=>$k,'q'=>$busqueda,'orden'=>$orden]);
?>
  <a href="/cotizaciones?<?= $qs ?>" class="chip <?= $estado===$k?'active':'' ?>">
    <?= $lbl ?> <span class="chip-count"><?= $cnt ?></span>
  </a>
<?php endforeach ?>
</div>

<div class="results-bar">
  <span class="results-count">
    <?= number_format($pag['total']) ?> cotizacion<?= $pag['total']!==1?'es':'' ?>
    <?= $busqueda ? ' para "'.e($busqueda).'"' : '' ?>
  </span>
</div>

<?php if (empty($rows)): ?>
<div class="empty">
  <div class="empty-ico"><?= ico('file', 32, '#94a3b8') ?></div>
  <div class="empty-txt"><?= $busqueda ? 'Sin resultados para "'.e($busqueda).'"' : 'No hay cotizaciones aún' ?></div>
</div>
<?php else: ?>

<div class="cot-list">

  <div class="tbl-header">
    <span>Proyecto</span><span>Cliente</span><span>Asesor</span><span>Estatus</span><span>Importe</span><span>Acciones</span>
  </div>

  <?php foreach ($rows as $c):
    $url    = 'https://'.EMPRESA_SLUG.'.'.BASE_DOMAIN.'/c/'.$c['slug'];
    $puedeX = !in_array($c['estado'], ['aceptada','aceptada_cliente','convertida']);
    $esSusp = !empty($c['suspendida']);
    $puedeS = in_array($c['estado_real'] ?? $c['estado'], ['enviada','vista','rechazada','borrador','vencida']) || $esSusp;
    $vistas = (int)($c['visitas'] ?? 0);
    $vis_txt = $vistas > 0 ? ico('eye',12,'#6a6a64').' '.$vistas : '—';
    $radar  = radar_badge($c['radar_bucket'], (int)($c['radar_score'] ?? 0), $vistas);
    $ed_url = '/cotizaciones/'.(int)$c['id'];
  ?>
  <div class="cot-row" id="row-<?= (int)$c['id'] ?>" onclick="toggleCot(<?= (int)$c['id'] ?>,event)">

    <!-- col 1 — mobile: tarjeta compacta con expand; desktop: título+número -->
    <div class="cot-main">
      <!-- col1 desktop: número encima del título; mobile: título+badge en una línea -->
      <span class="cot-numero-desk"><?= e($c['numero']) ?></span>
      <div class="mob-l1">
        <div class="cot-titulo"><?= e($c['titulo']) ?></div>
        <?= st_badge($c['estado'], $esSusp) ?>
      </div>
      <!-- MOBILE: línea 2 — cliente · tel -->
      <div class="mob-l2">
        <?= e($c['cnombre'] ?? '—') ?>
        <?php if ($c['ctel']): ?><span class="sep">·</span><?= e($c['ctel']) ?><?php endif ?>
      </div>
      <!-- MOBILE: línea 3 — monto + radar (que incluye ojo) -->
      <div class="mob-l3">
        <div class="cot-monto"><?= format_money($c['total'], $empresa['moneda']) ?></div>
        <div class="mob-r">
          <?php if ($c['radar_bucket']): ?>
            <?= $radar ?>
          <?php elseif ($vistas > 0): ?>
            <span class="cot-vistas"><?= ico('eye',12,'#6a6a64') ?> <?= $vistas ?></span>
          <?php endif ?>
        </div>
      </div>
      <!-- MOBILE: panel expandido (oculto por defecto) -->
      <div class="mob-expand" id="exp-<?= (int)$c['id'] ?>" onclick="event.stopPropagation()">
        <div class="exp-meta">
          <?php if ($c['vendedor']): ?><span class="exp-meta-item">Asesor: <?= e($c['vendedor']) ?></span><?php endif ?>
          <span class="exp-meta-item">Creada: <?= date('d M Y', strtotime($c['created_at'])) ?></span>
          <?php if ($c['valida_hasta']): ?>
          <?php
            $vts2 = strtotime($c['valida_hasta']); $vd2 = ($vts2-strtotime('today'))/86400;
            $vt2  = date('d M Y', $vts2);
            $vc2  = $vd2 < 0 ? 'exp-vence-old' : ($vd2 <= 3 ? 'exp-vence-soon' : '');
          ?>
          <span class="exp-meta-item <?= $vc2 ?>"><?= $vd2 < 0 ? 'Venció: ' : 'Vence: ' ?><?= $vt2 ?></span>
          <?php endif ?>
        </div>
        <div class="exp-btns">
          <a href="<?= $ed_url ?>" class="exp-btn"><?= ico('edit',12) ?> Editar</a>
          <a href="<?= e($url) ?>" target="_blank" class="exp-btn"><?= ico('link',12) ?> Ver</a>
          <button class="exp-btn" onclick="copyLink('<?= e($url) ?>')"><?= ico('copy',12) ?> Copiar</button>
          <?php if ($puedeS): ?>
          <button class="exp-btn <?= $esSusp ? '' : 'exp-btn-danger' ?>" onclick="suspenderCot(<?= (int)$c['id'] ?>,this)">
            <?= $esSusp ? '▶ Reactivar' : '⏸ Suspender' ?>
          </button>
          <?php endif ?>
          <?php if ($puedeX): ?>
          <button class="exp-btn exp-btn-danger" onclick="eliminarCot(<?= (int)$c['id'] ?>,this)">✕ Borrar</button>
          <?php endif ?>
        </div>
      </div>
    </div>

    <!-- col 2 desktop: cliente -->
    <div class="cot-col-cliente">
      <div class="cc-n"><?= e($c['cnombre'] ?? '—') ?></div>
      <?php if ($c['ctel']): ?><div class="cc-t"><?= e($c['ctel']) ?></div><?php endif ?>
    </div>

    <!-- col 3 desktop: asesor -->
    <div class="cot-col-asesor">
      <div class="cc-n"><?= e($c['vendedor'] ?? '—') ?></div>
    </div>

    <!-- col 4 desktop: estatus -->
    <div class="cot-col-status"><?= st_badge($c['estado'], $esSusp) ?></div>

    <!-- col 4 desktop: monto + radar/vistas debajo -->
    <div class="cot-col-monto">
      <div class="cot-monto-val"><?= format_money($c['total'], $empresa['moneda']) ?></div>
      <div class="cot-monto-sub">
        <?php if ($c['radar_bucket']): ?>
          <?= $radar ?>
        <?php elseif ($vistas > 0): ?>
          <span class="cot-vistas-badge"><?= ico('eye',12,'#6a6a64') ?> <?= $vistas ?></span>
        <?php endif ?>
      </div>
    </div>

    <!-- col 5 desktop: fechas (oculto, info va dentro col-monto) -->
    <div class="cot-col-fechas" style="display:none"></div>

    <!-- col 6 desktop: radar (oculto, va dentro col-monto) -->
    <div class="cot-col-vistas" style="display:none"></div>

    <!-- col 7 desktop: acciones siempre visibles; click fila va a editar -->
    <div class="desk-actions" onclick="event.stopPropagation()">
      <a href="<?= $ed_url ?>" class="act-btn"><?= ico('edit',12) ?> Editar</a>
      <a href="<?= e($url) ?>" target="_blank" class="act-btn"><?= ico('link',12) ?> Ver</a>
      <button class="act-btn" onclick="copyLink('<?= e($url) ?>')"><?= ico('copy',12) ?></button>
      <?php if ($puedeS): ?>
      <button class="act-btn <?= $esSusp ? '' : 'danger' ?>" onclick="suspenderCot(<?= (int)$c['id'] ?>,this)" title="<?= $esSusp ? 'Reactivar' : 'Suspender' ?>">
        <?= $esSusp ? '▶' : '⏸' ?>
      </button>
      <?php endif ?>
      <?php if ($puedeX): ?>
      <button class="act-btn danger" onclick="eliminarCot(<?= (int)$c['id'] ?>,this)">✕</button>
      <?php endif ?>
    </div>

  </div>
  <?php endforeach ?>
</div>

<?php if ($pag['total_pags'] > 1):
  $qb = http_build_query(['estado'=>$estado,'q'=>$busqueda,'orden'=>$orden]);
?>
<div class="pag-wrap">
  <div class="pag-info">Mostrando <?= $pag['offset']+1 ?>–<?= min($pag['offset']+$por_pag,$pag['total']) ?> de <?= number_format($pag['total']) ?></div>
  <div class="pag-btns">
    <a href="/cotizaciones?<?= $qb ?>&p=<?= $pag['pagina']-1 ?>" class="pag-btn <?= !$pag['hay_prev']?'disabled':'' ?>">←</a>
    <?php for ($i=max(1,$pag['pagina']-2); $i<=min($pag['total_pags'],$pag['pagina']+2); $i++): ?>
    <a href="/cotizaciones?<?= $qb ?>&p=<?= $i ?>" class="pag-btn <?= $i===$pag['pagina']?'active':'' ?>"><?= $i ?></a>
    <?php endfor ?>
    <?php if ($pag['pagina']+2 < $pag['total_pags']): ?>
    <span class="pag-sep">…</span>
    <a href="/cotizaciones?<?= $qb ?>&p=<?= $pag['total_pags'] ?>" class="pag-btn"><?= $pag['total_pags'] ?></a>
    <?php endif ?>
    <a href="/cotizaciones?<?= $qb ?>&p=<?= $pag['pagina']+1 ?>" class="pag-btn <?= !$pag['hay_next']?'disabled':'' ?>">→</a>
  </div>
</div>
<?php endif ?>

<?php endif ?>

<script>
function filtrar(k,v){const p=new URLSearchParams(window.location.search);if(v)p.set(k,v);else p.delete(k);if(k!=='p')p.delete('p');window.location='/cotizaciones?'+p.toString()}
const CSRF_TOKEN='<?= csrf_token() ?>';

function toggleCot(id, e) {
  const isDesktop = window.innerWidth >= 641;
  if (isDesktop) {
    // Desktop: ir directo a editar
    window.location = '/cotizaciones/' + id;
    return;
  }
  // Mobile: expandir/colapsar panel
  const exp = document.getElementById('exp-' + id);
  if (!exp) return;
  const isOpen = exp.classList.contains('open');
  // Cerrar todos los demás
  document.querySelectorAll('.mob-expand.open').forEach(el => el.classList.remove('open'));
  if (!isOpen) exp.classList.add('open');
}

function copyLink(url) {
  navigator.clipboard.writeText(url).then(() => {
    const btn = event.target.closest('button');
    const orig = btn.textContent;
    btn.innerHTML = 'Copiado';
    setTimeout(() => btn.innerHTML = orig, 1500);
  }).catch(() => {
    prompt('Copia este enlace:', url);
  });
}

async function eliminarCot(id, btn) {
  if (!confirm('¿Eliminar esta cotización?')) return;
  try {
    const r = await fetch('/cotizaciones/' + id + '/eliminar', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
      body: JSON.stringify({})
    });
    const d = await r.json();
    if (d.ok) btn.closest('.cot-row').remove();
    else alert(d.error || 'Error al eliminar');
  } catch(e) { alert('Error de conexión') }
}

async function suspenderCot(id, btn) {
  const isSusp = btn.textContent.includes('Reactivar');
  const msg = isSusp ? '¿Reactivar esta cotización?' : '¿Suspender esta cotización? El cliente no podrá verla.';
  if (!confirm(msg)) return;
  try {
    const r = await fetch('/cotizaciones/' + id + '/suspender', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
      body: JSON.stringify({})
    });
    const d = await r.json();
    if (d.ok) window.location.reload();
    else alert(d.error || 'Error');
  } catch(e) { alert('Error de conexión') }
}
</script>

<?php
$content = ob_get_clean();
require ROOT_PATH . '/core/layout.php';
