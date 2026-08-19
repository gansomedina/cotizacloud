<?php
// ============================================================
//  Supervisor — Panel multi-sucursal
//
//  Una pestaña por sucursal. Dentro de cada una, LO MISMO que ve el dueño de
//  esa sucursal en su dashboard: la Mesa de Trabajo, el Ritmo del equipo (5
//  pilares) y el Ranking con sus barras, tips y reporte del Director.
//
//  NO se repinta nada: se incluyen los ARCHIVOS REALES
//  (dashboard/_mesa.php, _ritmo.php, _leaderboard.php). Repintarlos fue el
//  primer intento y el CEO lo rechazó con razón — al copiar 1,800 líneas
//  siempre falta algo, y cada cambio al dashboard habría que replicarlo a mano.
//  Esos partials no leen la constante EMPRESA_ID: trabajan con $empresa_id y
//  compañía, así que basta con prepararles el scope de la sucursal elegida.
//
//  El Ejecutivo consolidado vive aparte (/supervisor/ejecutivo): es una página
//  oscura completa y no se puede meter dentro de este layout.
// ============================================================
defined('COTIZAAPP') or die;
supervisor_requerir();

$sv_ids = supervisor_empresas();
$ph = implode(',', array_fill(0, count($sv_ids), '?'));
$sv_sucursales = DB::query("SELECT * FROM empresas WHERE id IN ($ph) ORDER BY nombre", $sv_ids);

// Sucursal activa: validada contra SU lista, así que ?emp=99 cae al default en
// vez de mostrar datos que no le tocan.
$sv_sel = (int)($_GET['emp'] ?? 0);
if (!in_array($sv_sel, $sv_ids, true)) $sv_sel = (int)($sv_sucursales[0]['id'] ?? 0);

// ── Scope que esperan los partials del dashboard ────────────
// Son los mismos nombres de variable que usa modules/dashboard/index.php.
$empresa_id     = $sv_sel;
$empresa        = null;
foreach ($sv_sucursales as $sv_s) if ((int)$sv_s['id'] === $sv_sel) $empresa = $sv_s;
$es_admin_dash  = true;   // ve la vista de admin de esa sucursal (solo lectura)
$mesa_solo_lectura = true; // NO escribir mesa_vencidos: mirar no castiga
$rt_eid         = $sv_sel;
$trial          = trial_info($sv_sel);
$es_business_dash = !empty($trial['es_business']);

$equipo_scores = [];
$diag_ctx      = null;
if ($es_business_dash) {
    try {
        // Igual que el dashboard: si el score más viejo pasa de 10 min, recalcula.
        $sv_old = DB::val("SELECT MIN(updated_at) FROM usuario_score WHERE empresa_id=?", [$sv_sel]);
        if (!$sv_old || (time() - strtotime($sv_old)) > 600) ActividadScore::recalcular_empresa($sv_sel);
        $equipo_scores = ActividadScore::equipo($sv_sel);
        $diag_ctx = ActividadScore::diagnostico_ctx($sv_sel, count($equipo_scores));
    } catch (Throwable $e) { $equipo_scores = []; }
}

$MESA_SHARED = ''; $MESA_BLOQUES = []; $MESA_ASSETS = ''; $MESA_EMITIDO = false; $MESA_ASESOR = '';

ob_start();
?>
<style>
.svt{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px;border-bottom:1px solid var(--border);padding-bottom:10px}
.svt a{padding:9px 15px;border-radius:9px;font:700 13px var(--body);text-decoration:none;color:var(--t2);
       border:1px solid transparent;white-space:nowrap}
.svt a:hover{background:rgba(0,0,0,.04)}
.svt a.on{background:var(--white);border-color:var(--border);color:var(--text);box-shadow:var(--sh)}
.svt a.exe{margin-left:auto;color:var(--g);border-color:var(--border)}
.sv-vacio{background:var(--white);border:1px solid var(--border);border-radius:var(--r);
          padding:26px 18px;text-align:center;color:var(--t3);font:400 13px var(--body)}
</style>

<div class="svt">
    <?php foreach ($sv_sucursales as $sv_s): ?>
    <a href="/supervisor?emp=<?= (int)$sv_s['id'] ?>" class="<?= (int)$sv_s['id'] === $sv_sel ? 'on' : '' ?>"><?= e($sv_s['nombre']) ?></a>
    <?php endforeach; ?>
    <a href="/supervisor/ejecutivo" class="exe">Ejecutivo consolidado →</a>
</div>

<?php if (!$empresa): ?>
<div class="sv-vacio">No tienes sucursales asignadas.</div>
<?php else: ?>

    <?php if (!$es_business_dash): ?>
    <div class="sv-vacio">
        <strong><?= e($empresa['nombre']) ?></strong> no está en plan Business, y la Mesa,
        el Ritmo y el Ranking son de ese plan.
    </div>
    <?php else: ?>

    <?php
    // Los partials REALES, en el mismo orden que el dashboard del dueño.
    // _mesa.php no emite: llena $MESA_SHARED y $MESA_BLOQUES, que consume
    // _leaderboard.php para incrustar la mesa de cada asesor bajo su tip.
    include MODULES_PATH . '/dashboard/_mesa.php';
    include MODULES_PATH . '/dashboard/_ritmo.php';
    include MODULES_PATH . '/dashboard/_leaderboard.php';

    // Si el ranking no se renderizó (sin scores todavía), la mesa no puede
    // desaparecer: se emite sola. Mismo fallback que el dashboard.
    if (empty($MESA_EMITIDO) && $MESA_SHARED) {
        echo '<div class="card" style="margin-bottom:16px;padding:4px 0 8px">'
           . $MESA_SHARED . $MESA_ASSETS;
        foreach ($MESA_BLOQUES as $mb) echo $mb;
        echo '</div>';
        $MESA_EMITIDO = true;
    }
    if (!$equipo_scores && !$MESA_EMITIDO): ?>
    <div class="sv-vacio">
        <strong><?= e($empresa['nombre']) ?></strong> todavía no tiene datos de equipo:
        ni asesores con cartera activa ni scores calculados.
    </div>
    <?php endif; ?>

    <?php endif; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
$titulo  = 'Supervisión';
require ROOT_PATH . '/core/layout.php';
