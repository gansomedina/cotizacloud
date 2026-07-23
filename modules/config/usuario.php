<?php
// ============================================================
//  cotiza.cloud — modules/config/usuario.php
//  POST /config/usuario         → crear
//  POST /config/usuario/:id     → editar
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_admin();
csrf_check();
header('Content-Type: application/json');

$eid    = EMPRESA_ID;
$usr_id = isset($id) ? (int)$id : 0;

// Paquetes 23-jul: Pro y Business pueden crear usuarios (Pro = "tu equipo").
// Tope por ASIENTOS (asientos_max de trial_info): columna empresas.asientos si
// está fijada; si no, default del plan (Free/Lite=1, Pro/Business=ilimitado).
// El mismo check aplica al REACTIVAR (rama editar) — sin él, una empresa
// degradada revivía usuarios por edición sin gate (hueco de la auditoría).
$plan = trial_info($eid);
$chk_asientos = function () use ($plan, $eid) {
    if ($plan['asientos_max'] === null) return null; // sin tope
    $activos = (int)DB::val("SELECT COUNT(*) FROM usuarios WHERE empresa_id=? AND activo=1", [$eid]);
    if ($activos >= $plan['asientos_max']) {
        return "Tu plan incluye {$plan['asientos_max']} usuario" . ($plan['asientos_max'] > 1 ? 's' : '')
             . " activo" . ($plan['asientos_max'] > 1 ? 's' : '') . " — para agregar más, mejora tu plan.";
    }
    return null;
};
if ($usr_id === 0) {
    if (!$plan['es_pro_o_superior']) {
        echo json_encode(['ok'=>false,'error'=>'Crear usuarios está disponible en los planes Pro y Business']); exit;
    }
    if (($err_as = $chk_asientos()) !== null) {
        echo json_encode(['ok'=>false,'error'=>$err_as]); exit;
    }
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { echo json_encode(['ok'=>false,'error'=>'Payload inválido']); exit; }

$nombre  = mb_substr(trim($body['nombre']  ?? ''), 0, 120);
$email   = mb_substr(strtolower(trim($body['email'] ?? '')), 0, 120);
if ($email === '') { echo json_encode(['ok'=>false,'error'=>'El email es obligatorio']); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['ok'=>false,'error'=>'Email inválido']); exit; }
// Autogenerar usuario del email (parte antes del @)
$usuario = mb_substr(preg_replace('/[^a-z0-9._-]/', '', strtolower(explode('@', $email)[0])), 0, 60);
if ($usuario === '') $usuario = mb_substr(str_replace(['@','.'], '_', $email), 0, 60);
$rol     = in_array($body['rol']??'', ['admin','asesor']) ? $body['rol'] : 'asesor';
// Clamp a 0/1: sin él, activo=2 burlaba el check de reactivación en 2 pasos
$activo  = ((int)($body['activo'] ?? 1)) ? 1 : 0;
$pass    = $body['password'] ?? '';

// Validaciones
if ($nombre === '')  { echo json_encode(['ok'=>false,'error'=>'El nombre es obligatorio']); exit; }

// Permisos granulares (solo aplican a asesores)
$perms = [
    'puede_crear_cotizaciones'   => (int)($body['puede_crear_cotizaciones']   ?? 1),
    'puede_editar_cotizaciones'  => (int)($body['puede_editar_cotizaciones']  ?? 1),
    'puede_ver_cantidades'       => (int)($body['puede_ver_cantidades']       ?? 1),
    'puede_editar_precios'       => (int)($body['puede_editar_precios']       ?? 1),
    'puede_aplicar_descuentos'   => (int)($body['puede_aplicar_descuentos']   ?? 1),
    'puede_ver_todas_cots'       => (int)($body['puede_ver_todas_cots']       ?? 0),
    'puede_ver_todas_ventas'     => (int)($body['puede_ver_todas_ventas']     ?? 0),
    'puede_eliminar_items_venta' => (int)($body['puede_eliminar_items_venta'] ?? 0),
    'puede_agregar_extras'       => (int)($body['puede_agregar_extras']       ?? 0),
    'puede_cancelar_recibos'     => (int)($body['puede_cancelar_recibos']     ?? 0),
    'puede_capturar_pagos'       => (int)($body['puede_capturar_pagos']       ?? 0),
    'puede_asignar_cotizaciones' => (int)($body['puede_asignar_cotizaciones'] ?? 0),
    'puede_ver_costos'           => (int)($body['puede_ver_costos']           ?? 1),
    'puede_ver_proveedores'      => (int)($body['puede_ver_proveedores']      ?? 1),
    'puede_ver_reportes'         => (int)($body['puede_ver_reportes']         ?? 1),
    'puede_adjuntar'             => (int)($body['puede_adjuntar']             ?? 1),
    'puede_editar_clientes'      => (int)($body['puede_editar_clientes']      ?? 1),
];
if ($rol === 'admin') {
    // Admin tiene todos los permisos implícitos
    $perms = array_map(fn() => 1, $perms);
}

if ($usr_id > 0) {
    // ── EDITAR ───────────────────────────────────────────────
    $u = DB::row("SELECT id, usuario, activo FROM usuarios WHERE id=? AND empresa_id=?", [$usr_id, $eid]);
    if (!$u) { echo json_encode(['ok'=>false,'error'=>'Usuario no encontrado']); exit; }

    // REACTIVAR (inactivo → activo) cuenta contra el tope de asientos igual que
    // crear — antes esta rama no validaba plan y era la puerta trasera.
    if ($activo === 1 && (int)$u['activo'] !== 1 && ($err_as = $chk_asientos()) !== null) {
        echo json_encode(['ok'=>false,'error'=>$err_as]); exit;
    }

    // Verificar email único (excepto el mismo)
    $dup = DB::val("SELECT id FROM usuarios WHERE empresa_id=? AND email=? AND id!=?", [$eid, $email, $usr_id]);
    if ($dup) { echo json_encode(['ok'=>false,'error'=>'Ese email ya está en uso']); exit; }

    // No permitir desactivar al único admin
    if ($activo === 0) {
        $admin_count = DB::val("SELECT COUNT(*) FROM usuarios WHERE empresa_id=? AND rol='admin' AND activo=1 AND id!=?", [$eid, $usr_id]);
        $esta_admin  = DB::val("SELECT rol FROM usuarios WHERE id=?", [$usr_id]) === 'admin';
        if ($esta_admin && (int)$admin_count === 0) {
            echo json_encode(['ok'=>false,'error'=>'No puedes desactivar al único admin']); exit;
        }
    }

    $set = "nombre=?, usuario=?, email=?, rol=?, activo=?,
            puede_crear_cotizaciones=?, puede_editar_cotizaciones=?, puede_ver_cantidades=?,
            puede_editar_precios=?, puede_aplicar_descuentos=?,
            puede_ver_todas_cots=?, puede_ver_todas_ventas=?,
            puede_eliminar_items_venta=?, puede_agregar_extras=?, puede_cancelar_recibos=?,
            puede_capturar_pagos=?, puede_asignar_cotizaciones=?,
            puede_ver_costos=?, puede_ver_proveedores=?, puede_ver_reportes=?, puede_adjuntar=?,
            puede_editar_clientes=?";
    $vals = [
        $nombre, $usuario, $email, $rol, $activo,
        $perms['puede_crear_cotizaciones'], $perms['puede_editar_cotizaciones'], $perms['puede_ver_cantidades'],
        $perms['puede_editar_precios'], $perms['puede_aplicar_descuentos'],
        $perms['puede_ver_todas_cots'], $perms['puede_ver_todas_ventas'],
        $perms['puede_eliminar_items_venta'], $perms['puede_agregar_extras'], $perms['puede_cancelar_recibos'],
        $perms['puede_capturar_pagos'], $perms['puede_asignar_cotizaciones'],
        $perms['puede_ver_costos'], $perms['puede_ver_proveedores'], $perms['puede_ver_reportes'],
        $perms['puede_adjuntar'], $perms['puede_editar_clientes'],
    ];

    if ($pass !== '') {
        if (strlen($pass) < 8) { echo json_encode(['ok'=>false,'error'=>'La contraseña debe tener al menos 8 caracteres']); exit; }
        $set .= ', password_hash=?';
        $vals[] = password_hash($pass, PASSWORD_DEFAULT);
    }

    $vals[] = $usr_id;
    DB::execute("UPDATE usuarios SET $set WHERE id=?", $vals);
    echo json_encode(['ok'=>true, 'id'=>$usr_id]);

} else {
    // ── CREAR ────────────────────────────────────────────────
    if (strlen($pass) < 8) { echo json_encode(['ok'=>false,'error'=>'La contraseña debe tener al menos 8 caracteres']); exit; }

    $dup = DB::val("SELECT id FROM usuarios WHERE empresa_id=? AND email=?", [$eid, $email]);
    if ($dup) { echo json_encode(['ok'=>false,'error'=>'Ese email ya está en uso']); exit; }

    $nuevo = DB::insert(
        "INSERT INTO usuarios
         (empresa_id, nombre, usuario, email, password_hash, rol, activo,
          puede_crear_cotizaciones, puede_editar_cotizaciones, puede_ver_cantidades,
          puede_editar_precios, puede_aplicar_descuentos,
          puede_ver_todas_cots, puede_ver_todas_ventas,
          puede_eliminar_items_venta, puede_agregar_extras, puede_cancelar_recibos,
          puede_capturar_pagos, puede_asignar_cotizaciones,
          puede_ver_costos, puede_ver_proveedores, puede_ver_reportes, puede_adjuntar,
          puede_editar_clientes)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            $eid, $nombre, $usuario, $email,
            password_hash($pass, PASSWORD_DEFAULT),
            $rol, $activo,
            $perms['puede_crear_cotizaciones'], $perms['puede_editar_cotizaciones'], $perms['puede_ver_cantidades'],
            $perms['puede_editar_precios'], $perms['puede_aplicar_descuentos'],
            $perms['puede_ver_todas_cots'], $perms['puede_ver_todas_ventas'],
            $perms['puede_eliminar_items_venta'], $perms['puede_agregar_extras'], $perms['puede_cancelar_recibos'],
            $perms['puede_capturar_pagos'], $perms['puede_asignar_cotizaciones'],
            $perms['puede_ver_costos'], $perms['puede_ver_proveedores'], $perms['puede_ver_reportes'],
            $perms['puede_adjuntar'], $perms['puede_editar_clientes'],
        ]
    );
    echo json_encode(['ok'=>true, 'id'=>$nuevo]);
}
