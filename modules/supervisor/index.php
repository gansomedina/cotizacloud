<?php
// ============================================================
//  Supervisor — Selector de sucursal
//
//  NO renderiza mesa, ni ritmo, ni ranking: cambia la sucursal de su sesión y
//  lo manda al dashboard REAL de esa empresa. Ahí ve exactamente lo mismo que
//  el dueño —la Mesa completa con su cajón, los 5 pilares, el Ranking con sus
//  barras, los tips y el reporte del Director— porque ES el dashboard, no una
//  copia.
//
//  Este archivo y cambiar.php son TODO lo que existe del supervisor en el
//  sistema. Ningún archivo del dashboard fue tocado: replicar la Mesa (1,436
//  líneas) y el Ranking (177) siempre dejaba algo fuera y obligaba a modificar
//  código que usan todos los clientes.
// ============================================================
defined('COTIZAAPP') or die;
supervisor_requerir();

$sv_ids = supervisor_empresas();
$ph  = implode(',', array_fill(0, count($sv_ids), '?'));
$suc = DB::query(
    "SELECT id, nombre, slug, plan FROM empresas WHERE id IN ($ph) ORDER BY nombre",
    $sv_ids
);

// Resumen de cada sucursal, para saber a cuál entrar sin tener que entrar.
// Solo lectura: NO llama a Mesa::armar (escribiría mesa_vencidos y le movería
// el score a los asesores). Cuenta directo lo que importa.
$sv_info = [];
foreach ($suc as $s) {
    $eid = (int)$s['id'];
    $d = ['asesores' => 0, 'vivas' => 0, 'alerta' => 0, 'mesa' => false];
    try {
        $d['mesa'] = (int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$eid]) >= 1;
    } catch (Throwable $e) {}
    try {
        $d['vivas'] = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones WHERE empresa_id=? AND suspendida=0
              AND estado IN ('enviada','vista')", [$eid]);
        $d['asesores'] = (int)DB::val(
            "SELECT COUNT(DISTINCT COALESCE(c.vendedor_id, c.usuario_id))
               FROM cotizaciones c WHERE c.empresa_id=? AND c.suspendida=0
                AND c.estado IN ('enviada','vista')", [$eid]);
    } catch (Throwable $e) {}
    // Cuántos asesores traen algún pilar en rojo/ámbar — el mismo dato que el
    // dueño ve como "N por atender", desde la misma fuente (RitmoAsesor).
    if ($d['mesa']) {
        try {
            $f = RitmoAsesor::empresa($eid);
            $d['alerta'] = count(array_filter($f, fn($x) => ($x['semaforo'] ?? '') !== 'verde'));
        } catch (Throwable $e) {}
    }
    $sv_info[$eid] = $d;
}

$sv_actual = defined('EMPRESA_ID') ? (int)EMPRESA_ID : 0;

ob_start();
?>
<style>
.svc{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px;margin-bottom:20px}
.svb{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;
     box-shadow:var(--sh);display:flex;flex-direction:column;gap:9px}
.svb.on{border-color:var(--g);box-shadow:0 0 0 2px rgba(26,92,56,.15)}
.svb-n{font:800 15px var(--body);color:var(--text)}
.svb-m{font:400 12.5px var(--body);color:var(--t3);line-height:1.5}
.svb-a{font:700 12px var(--body);color:var(--danger)}
.svb-btn{margin-top:auto;width:100%;padding:9px;border:0;border-radius:9px;cursor:pointer;
         font:800 13px var(--body);color:#fff;background:var(--g)}
.svb-btn[disabled]{background:var(--border);color:var(--t3);cursor:default}
.sv-exe{display:inline-block;padding:10px 18px;border-radius:9px;border:1px solid var(--border);
        background:var(--white);color:var(--text);text-decoration:none;font:700 13px var(--body)}
</style>

<div class="slabel">Sucursales que supervisas</div>
<p style="font:400 13px var(--body);color:var(--t3);margin:0 0 14px;line-height:1.55">
    Entra a una y verás su panel completo —Mesa de Trabajo, Ritmo del equipo, Ranking con
    los tips y el reporte del Director— igual que lo ve su dueño. Puedes volver aquí cuando
    quieras desde el menú.
</p>

<div class="svc">
<?php foreach ($suc as $s):
    $eid = (int)$s['id']; $i = $sv_info[$eid]; $act = $eid === $sv_actual;
?>
    <div class="svb <?= $act ? 'on' : '' ?>">
        <div class="svb-n"><?= e($s['nombre']) ?><?= $act ? ' <span style="font:700 11px var(--body);color:var(--g)">· aquí estás</span>' : '' ?></div>
        <div class="svb-m">
            <?= (int)$i['vivas'] ?> cotizaciones vivas · <?= (int)$i['asesores'] ?> asesor<?= $i['asesores'] == 1 ? '' : 'es' ?>
            <?php if (!$i['mesa']): ?><br><span style="color:var(--t3)">Mesa de Trabajo apagada</span><?php endif; ?>
        </div>
        <?php if ($i['alerta'] > 0): ?>
        <div class="svb-a"><?= (int)$i['alerta'] ?> asesor<?= $i['alerta'] == 1 ? '' : 'es' ?> por atender</div>
        <?php endif; ?>
        <?php if ($act): ?>
        <a class="svb-btn" href="/dashboard" style="text-align:center;text-decoration:none;display:block">Ver su panel</a>
        <?php else: ?>
        <form method="post" action="/supervisor/cambiar" style="margin-top:auto">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="empresa_id" value="<?= $eid ?>">
            <button class="svb-btn" type="submit">Entrar</button>
        </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<a class="sv-exe" href="/supervisor/ejecutivo">Ejecutivo consolidado — todas las sucursales juntas →</a>
<?php
$content = ob_get_clean();
$titulo  = 'Supervisión';
require ROOT_PATH . '/core/layout.php';
