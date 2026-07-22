<?php
// ============================================================
//  CotizaApp — modules/cotizaciones/suspender.php
//  POST /cotizaciones/:id/suspender
//  Toggle suspender / reactivar cotización
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');
csrf_check();

$empresa_id = EMPRESA_ID;
$cot_id     = (int)($id ?? 0);
if (!$cot_id) json_error('ID inválido', 400);

$cot = DB::row(
    "SELECT id, estado, suspendida, titulo, numero, COALESCE(vendedor_id, usuario_id) AS vend
     FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, $empresa_id]
);
if (!$cot) json_error('No encontrada', 404);

// Ownership: solo el dueño (vendedor/creador) o un admin puede suspender/reactivar.
// Antes solo pedía login+CSRF+empresa → cualquier asesor podía matar el slug y el
// Radar de una venta viva de OTRO asesor por POST directo. Mismo guard que usan
// api/mesa_estado.php y api/mesa_agendar.php.
if (!Auth::es_admin() && (int)$cot['vend'] !== Auth::id()) {
    json_error('Sin acceso a esta cotización', 403);
}

// No suspender cotizaciones ya cerradas
if (in_array($cot['estado'], ['aceptada', 'convertida', 'aceptada_cliente'])) {
    json_error('No se puede suspender una cotización ' . $cot['estado'], 422);
}

// Acción EXPLÍCITA opcional (body JSON) para evitar el toggle ciego: si la mesa
// manda accion='suspender' y otra vía (auto_suspender u otra pestaña) ya la
// suspendió entre render y clic, el toggle la REACTIVABA — lo contrario de lo
// pedido. Con acción explícita, un estado ya-en-destino es no-op idempotente.
// Sin accion (ver.php/lista.php) conserva el toggle histórico.
$body_susp  = json_decode(file_get_contents('php://input'), true) ?: [];
$accion_req = $body_susp['accion'] ?? null;
if ($accion_req === 'suspender')      $nueva = 1;
elseif ($accion_req === 'reactivar')  $nueva = 0;
else                                   $nueva = $cot['suspendida'] ? 0 : 1;
$accion = $nueva ? 'suspendida' : 'reactivada';

DB::execute(
    "UPDATE cotizaciones SET suspendida = ?, suspendida_at = ? WHERE id = ?",
    [$nueva, $nueva ? date('Y-m-d H:i:s') : null, $cot_id]
);

// Limpiar radar si se suspende
if ($nueva) {
    DB::execute(
        "UPDATE cotizaciones SET radar_bucket = NULL, radar_score = NULL, radar_senales = NULL WHERE id = ?",
        [$cot_id]
    );
}

// Log
DB::insert(
    "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, evento, detalle, created_at) VALUES (?,?,?,?,NOW())",
    [$cot_id, Auth::id(), $accion, 'Cotización ' . $accion . ' por ' . Auth::usuario()['nombre']]
);

json_ok(['id' => $cot_id, 'suspendida' => $nueva, 'accion' => $accion]);
