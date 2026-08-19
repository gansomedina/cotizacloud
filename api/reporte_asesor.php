<?php
// POST /api/reporte-asesor — genera el reporte del Director para un asesor.
// Business only. Admin: 1 por asesor cada 7 días (dentro de la semana devuelve
// el snapshot guardado). Superadmin: ilimitado y siempre fresco.
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::logueado()) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'sesion']); exit; }
csrf_check();

$es_super = Auth::es_superadmin();
// El SUPERVISOR multi-sucursal consulta reportes de las empresas que tiene
// asignadas. Se le trata como al superadmin en TODO lo que sigue: es un
// observador, no la empresa. Si contara como admin, cada reporte que abriera
// le consumiría al dueño su reporte de la semana y le quemaría al asesor una
// técnica de la rotación que nunca vería.
$es_supervisor = function_exists('es_supervisor') && es_supervisor();
$observador    = $es_super || $es_supervisor;
if (!$observador && !Auth::es_admin()) { echo json_encode(['ok'=>false,'error'=>'permiso']); exit; }

$b = json_decode(file_get_contents('php://input'), true) ?? [];
$asesor_id = (int)($b['asesor_id'] ?? 0);
if (!$asesor_id) { echo json_encode(['ok'=>false,'error'=>'asesor']); exit; }

// Empresa objetivo. El admin solo puede la suya; el supervisor, cualquiera de
// SU lista (validada contra supervisor_empresas, nunca contra lo que mande el
// request). EMPRESA_ID no sirve de referencia para él: su usuario vive en
// '_system' y no en la sucursal que está consultando.
$emp_objetivo = EMPRESA_ID;
if ($es_supervisor) {
    $emp_pedida = (int)($b['empresa_id'] ?? 0);
    if (!supervisor_ve_empresa($emp_pedida)) { echo json_encode(['ok'=>false,'error'=>'empresa']); exit; }
    $emp_objetivo = $emp_pedida;
}

// Business + Mesa activa (mismo gate que la tarjeta de Ritmo)
if (empty(trial_info($emp_objetivo)['es_business'])) { echo json_encode(['ok'=>false,'error'=>'plan']); exit; }
try { $mesa_on = (int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$emp_objetivo]) >= 1; }
catch (Throwable $e) { $mesa_on = false; }
if (!$mesa_on) { echo json_encode(['ok'=>false,'error'=>'mesa']); exit; }

// El asesor debe ser de la empresa objetivo
$existe = DB::val("SELECT 1 FROM usuarios WHERE id=? AND empresa_id=?", [$asesor_id, $emp_objetivo]);
if (!$existe) { echo json_encode(['ok'=>false,'error'=>'no_encontrado']); exit; }

// El rango NO lo elige el cliente: la ventana es la del ciclo real de la empresa
// (2×p75), la MISMA de la tarjeta de Ritmo. Se deriva del reporte ya generado y
// se guarda tal cual, para que el snapshot no diga un rango que no midió.

// Rate-limit semanal (solo admin). Dentro de la semana → devuelve el snapshot.
if (!$observador) {
    try {
        // Solo cuentan los reportes generados por la PROPIA empresa. Las vistas
        // del superadmin son invisibles para este reloj: si no, revisar a un
        // asesor un lunes le consumía al admin su reporte de la semana y se lo
        // dejaba fechado en MI horario, no el suyo.
        $last = DB::row(
            "SELECT r.contenido, r.created_at, r.rango_desde, r.rango_hasta
               FROM ritmo_reportes r
               JOIN usuarios u ON u.id = r.generado_por
              WHERE r.empresa_id=? AND r.asesor_id=? AND COALESCE(u.rol,'') <> 'superadmin'
              ORDER BY r.created_at DESC LIMIT 1",
            [$emp_objetivo, $asesor_id]
        );
    } catch (Throwable $e) { $last = null; }
    if ($last && strtotime($last['created_at']) > time() - 7 * 86400) {
        json_ok([
            'html'  => $last['contenido'],
            'cache' => true,
            'desde' => $last['rango_desde'],
            'hasta' => $last['rango_hasta'],
            'fecha' => $last['created_at'],
        ], 'Reporte de esta semana (1 por asesor cada 7 días).');
    }
}

$rep = RitmoReporte::generar($emp_objetivo, $asesor_id);

// Rango REAL medido = la ventana del reporte (2×p75), no un default inventado.
$win   = max(1, (int)($rep['win'] ?? 15));
$hasta = date('Y-m-d');
$desde = date('Y-m-d', strtotime("-{$win} days"));

try {
    DB::execute(
        "INSERT INTO ritmo_reportes (empresa_id, asesor_id, generado_por, rango_desde, rango_hasta, contenido)
         VALUES (?,?,?,?,?,?)",
        [$emp_objetivo, $asesor_id, Auth::id(), $desde, $hasta, $rep['html']]
    );
} catch (Throwable $e) { /* si falla el guardado, igual devolvemos el reporte */ }

// Registrar la técnica mostrada (rotación del tip: no repetir hasta agotar).
// SOLO si el reporte lo generó la empresa. El superadmin tiene generación
// ilimitada: sin este gate, cada vistazo suyo quemaba una técnica que el
// asesor NUNCA vio — a las pocas revisiones les saltaba media rotación.
if (!$observador && !empty($rep['tip']['handle'])) {
    try {
        DB::execute("INSERT INTO ritmo_tips (empresa_id, asesor_id, handle) VALUES (?,?,?)",
            [$emp_objetivo, $asesor_id, $rep['tip']['handle']]);
    } catch (Throwable $e) {}
}

json_ok(['html'=>$rep['html'], 'cache'=>false, 'desde'=>$desde, 'hasta'=>$hasta]);
