<?php
// POST /api/reporte-asesor — genera el reporte del Director para un asesor.
// Business only. Admin: 1 por asesor cada 7 días (dentro de la semana devuelve
// el snapshot guardado). Superadmin: ilimitado y siempre fresco.
defined('COTIZAAPP') or die;
header('Content-Type: application/json; charset=utf-8');

if (!Auth::logueado()) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'sesion']); exit; }
csrf_check();

$es_super = Auth::es_superadmin();
if (!$es_super && !Auth::es_admin()) { echo json_encode(['ok'=>false,'error'=>'permiso']); exit; }

// Business + Mesa activa (mismo gate que la tarjeta de Ritmo)
if (empty(trial_info(EMPRESA_ID)['es_business'])) { echo json_encode(['ok'=>false,'error'=>'plan']); exit; }
try { $mesa_on = (int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [EMPRESA_ID]) >= 1; }
catch (Throwable $e) { $mesa_on = false; }
if (!$mesa_on) { echo json_encode(['ok'=>false,'error'=>'mesa']); exit; }

$b = json_decode(file_get_contents('php://input'), true) ?? [];
$asesor_id = (int)($b['asesor_id'] ?? 0);
if (!$asesor_id) { echo json_encode(['ok'=>false,'error'=>'asesor']); exit; }

// El asesor debe ser de ESTA empresa
$existe = DB::val("SELECT 1 FROM usuarios WHERE id=? AND empresa_id=?", [$asesor_id, EMPRESA_ID]);
if (!$existe) { echo json_encode(['ok'=>false,'error'=>'no_encontrado']); exit; }

// Rango: default últimos 15 días (hoy − 14 → hoy). Validado y acotado a 90d.
$hoy   = date('Y-m-d');
$hasta = $hoy;
$desde = date('Y-m-d', strtotime('-14 days'));
$rx = '/^\d{4}-\d{2}-\d{2}$/';
if (preg_match($rx, (string)($b['desde'] ?? '')) && preg_match($rx, (string)($b['hasta'] ?? ''))) {
    $bd = $b['desde']; $bh = $b['hasta'];
    if ($bd <= $bh && $bh <= $hoy) {
        // acota a máximo 90 días
        if ((strtotime($bh) - strtotime($bd)) / 86400 <= 90) { $desde = $bd; $hasta = $bh; }
    }
}

// Rate-limit semanal (solo admin). Dentro de la semana → devuelve el snapshot.
if (!$es_super) {
    try {
        $last = DB::row(
            "SELECT contenido, created_at, rango_desde, rango_hasta FROM ritmo_reportes
              WHERE empresa_id=? AND asesor_id=? ORDER BY created_at DESC LIMIT 1",
            [EMPRESA_ID, $asesor_id]
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

$rep = RitmoReporte::generar(EMPRESA_ID, $asesor_id, $desde, $hasta);

try {
    DB::execute(
        "INSERT INTO ritmo_reportes (empresa_id, asesor_id, generado_por, rango_desde, rango_hasta, contenido)
         VALUES (?,?,?,?,?,?)",
        [EMPRESA_ID, $asesor_id, Auth::id(), $desde, $hasta, $rep['html']]
    );
} catch (Throwable $e) { /* si falla el guardado, igual devolvemos el reporte */ }

json_ok(['html'=>$rep['html'], 'cache'=>false, 'desde'=>$desde, 'hasta'=>$hasta]);
