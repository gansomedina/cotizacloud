<?php
// ============================================================
//  CotizaApp — api/quote_action.php
//  POST /api/quote-action  (sin login)
// ============================================================

defined('COTIZAAPP') or die;

header('Content-Type: application/json; charset=utf-8');

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Payload inválido']); exit; }

$cot_id = (int)($body['cotizacion_id'] ?? 0);
$accion = trim($body['accion'] ?? '');

if (!$cot_id || !$accion) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Datos requeridos']); exit;
}

$acciones_validas = ['aceptar','rechazar'];
if (!in_array($accion, $acciones_validas)) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Acción inválida']); exit;
}

// ─── Cargar cotización ───────────────────────────────────
$cot = DB::row(
    "SELECT id, empresa_id, estado, suspendida, cliente_id, titulo FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, EMPRESA_ID]
);
if (!$cot) {
    http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Cotización no encontrada']); exit;
}
if (!empty($cot['suspendida'])) {
    echo json_encode(['ok'=>false,'error'=>'Esta cotización está suspendida']); exit;
}

$estado_actual = $cot['estado'];
$estados_activos = ['enviada','vista','aceptada'];

// ─── Aceptar ─────────────────────────────────────────────
if ($accion === 'aceptar') {
    if (!in_array($estado_actual, $estados_activos)) {
        echo json_encode(['ok'=>false,'error'=>'Esta cotización ya no está activa']); exit;
    }

    $nombre        = trim($body['nombre']          ?? '');
    $total_final   = (float)($body['total_final']   ?? 0);
    $desc_auto_amt = (float)($body['descuento_auto_amt'] ?? 0);
    $cupon_codigo  = trim($body['cupon_codigo']    ?? '');
    $cupon_pct     = (float)($body['cupon_pct']    ?? 0);

    if (!$nombre) {
        echo json_encode(['ok'=>false,'error'=>'El nombre es requerido']); exit;
    }

    try {
        DB::beginTransaction();

        // Lock para prevenir aceptación duplicada concurrente
        $cot_lock = DB::row("SELECT estado FROM cotizaciones WHERE id=? FOR UPDATE", [$cot_id]);
        if (!$cot_lock || !in_array($cot_lock['estado'], $estados_activos)) {
            DB::rollback();
            echo json_encode(['ok'=>false,'error'=>'Esta cotización ya no está activa']); exit;
        }

        // Total final (con descuentos aplicados)
        $total_guardar = $total_final > 0 ? $total_final : (float)DB::val("SELECT total FROM cotizaciones WHERE id=?", [$cot_id]);

        // 1. Actualizar estado cotización
        DB::execute(
            "UPDATE cotizaciones SET
                estado      = 'aceptada',
                aceptada_at = NOW(),
                accion_at   = NOW(),
                total       = ?
             WHERE id = ?",
            [$total_guardar, $cot_id]
        );

        // 2. Guardar cupón aplicado si hay — validar % contra BD, no confiar en el cliente
        if ($cupon_codigo) {
            $cupon_real = DB::row(
                "SELECT id, porcentaje FROM cupones WHERE empresa_id=? AND codigo=? AND activo=1",
                [EMPRESA_ID, $cupon_codigo]
            );
            if ($cupon_real) {
                $cupon_pct = (float)$cupon_real['porcentaje'];
                DB::execute(
                    "UPDATE cotizaciones SET cupon_codigo=?, cupon_pct=? WHERE id=?",
                    [$cupon_codigo, $cupon_pct, $cot_id]
                );
                DB::execute(
                    "UPDATE cupones SET usos=usos+1 WHERE id=?",
                    [$cupon_real['id']]
                );
            }
        }

        // 3. Crear venta automáticamente — mismo momento que la aceptación
        // Si ya existe una venta para esta cotización no duplicar
        $venta_existente = DB::val("SELECT id FROM ventas WHERE cotizacion_id=? LIMIT 1", [$cot_id]);
        if (!$venta_existente) {
            $slug_vta    = slug_unico($cot['titulo'], 'ventas', 'slug', EMPRESA_ID);
            $token_vta   = generar_token(32);
            // Generar folio VTA-YYYY-NNNN
            $anio_vta    = date('Y');
            $cnt_vta     = (int)DB::val("SELECT COUNT(*) FROM ventas WHERE empresa_id=?", [EMPRESA_ID]);
            $numero_vta  = 'VTA-' . $anio_vta . '-' . str_pad($cnt_vta + 1, 4, '0', STR_PAD_LEFT);

            DB::execute(
                "INSERT INTO ventas
                 (empresa_id, cotizacion_id, cliente_id, usuario_id,
                  numero, titulo, slug, token,
                  total, pagado, saldo, estado, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,0,?,'pendiente',NOW())",
                [
                    EMPRESA_ID,
                    $cot_id,
                    $cot['cliente_id'],
                    null,
                    $numero_vta,
                    $cot['titulo'],
                    $slug_vta,
                    $token_vta,
                    $total_guardar,
                    $total_guardar,
                ]
            );
        }

        // 4. Log
        DB::execute(
            "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, evento, detalle)
             VALUES (?,NULL,'aceptada',?)",
            [
                $cot_id,
                'Aceptada por: '.$nombre
                    .($cupon_codigo ? ' | Cupón: '.$cupon_codigo : '')
                    .(' | Total: $'.number_format($total_guardar, 2))
            ]
        );

        DB::commit();

        // Push notification a los usuarios de la empresa
        try {
            PushNotification::enviar_a_empresa(
                EMPRESA_ID,
                'cotizacion_aceptada',
                'Cotización aceptada',
                $nombre . ' aceptó la cotización: ' . $cot['titulo'],
                ['cotizacion_id' => $cot_id, 'url' => '/cotizaciones/' . $cot_id]
            );
        } catch (\Exception $e) {
            // No romper el flujo principal si falla el push
            if (DEBUG) error_log('Push error: ' . $e->getMessage());
        }

        // Email a admins de la empresa
        try {
            $empresa_mail = DB::row("SELECT nombre, moneda FROM empresas WHERE id=?", [EMPRESA_ID]);
            $admins = DB::query(
                "SELECT email, nombre FROM usuarios WHERE empresa_id=? AND activo=1 AND rol='admin'",
                [EMPRESA_ID]
            );
            foreach ($admins as $admin) {
                Mailer::enviar_cotizacion_aceptada(
                    $admin['email'],
                    $admin['nombre'],
                    $cot['titulo'],
                    $nombre,
                    $total_guardar,
                    $empresa_mail['moneda'] ?? 'MXN'
                );
            }
        } catch (\Exception $e) {
            if (DEBUG) error_log('Email aceptada error: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error al procesar']); exit;
    }

    echo json_encode(['ok'=>true, 'estado'=>'aceptada']); exit;
}

// ─── Rechazar ────────────────────────────────────────────
if ($accion === 'rechazar') {
    if (!in_array($estado_actual, $estados_activos)) {
        echo json_encode(['ok'=>false,'error'=>'Esta cotización ya no está activa']); exit;
    }

    $motivo = trim($body['motivo'] ?? '');

    try {
        DB::beginTransaction();

        // Lock para prevenir acción duplicada concurrente
        $cot_lock = DB::row("SELECT estado FROM cotizaciones WHERE id=? FOR UPDATE", [$cot_id]);
        if (!$cot_lock || !in_array($cot_lock['estado'], $estados_activos)) {
            DB::rollback();
            echo json_encode(['ok'=>false,'error'=>'Esta cotización ya no está activa']); exit;
        }

        DB::execute(
            "UPDATE cotizaciones SET
                estado         = 'rechazada',
                rechazada_at   = NOW(),
                accion_at      = NOW(),
                motivo_rechazo = ?
             WHERE id = ?",
            [$motivo ?: null, $cot_id]
        );

        DB::execute(
            "INSERT INTO cotizacion_log (cotizacion_id, usuario_id, evento, detalle)
             VALUES (?,NULL,'rechazada',?)",
            [$cot_id, 'Rechazada desde vista pública'.($motivo ? ': '.$motivo : '')]
        );

        DB::commit();

        // Push notification a los usuarios de la empresa
        try {
            PushNotification::enviar_a_empresa(
                EMPRESA_ID,
                'cotizacion_rechazada',
                'Cotización rechazada',
                'La cotización "' . $cot['titulo'] . '" fue rechazada' . ($motivo ? ': ' . $motivo : ''),
                ['cotizacion_id' => $cot_id, 'url' => '/cotizaciones/' . $cot_id]
            );
        } catch (\Exception $e) {
            if (DEBUG) error_log('Push error: ' . $e->getMessage());
        }

        // Email a admins de la empresa
        try {
            $admins = DB::query(
                "SELECT email, nombre FROM usuarios WHERE empresa_id=? AND activo=1 AND rol='admin'",
                [EMPRESA_ID]
            );
            foreach ($admins as $admin) {
                Mailer::enviar_cotizacion_rechazada(
                    $admin['email'],
                    $admin['nombre'],
                    $cot['titulo'],
                    $motivo
                );
            }
        } catch (\Exception $e) {
            if (DEBUG) error_log('Email rechazada error: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error al procesar']); exit;
    }

    echo json_encode(['ok'=>true,'estado'=>'rechazada']); exit;
}
