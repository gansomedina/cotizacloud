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
    "SELECT id, empresa_id, estado, suspendida, cliente_id, titulo, usuario_id, vendedor_id FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, EMPRESA_ID]
);
if (!$cot) {
    http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Cotización no encontrada']); exit;
}
if (!empty($cot['suspendida'])) {
    echo json_encode(['ok'=>false,'error'=>'Esta cotización está suspendida']); exit;
}

$estado_actual = $cot['estado'];
// SIN 'aceptada' (seguridad, auditoría 17-jul): incluirla permitía RE-ACEPTAR
// una cotización ya cerrada — recalculaba y reescribía el total desde líneas
// vivas (que la venta ya pudo editar), dejaba inyectar un cupón retroactivo
// para bajar el precio, reseteaba aceptada_at (corrompía tasa de cierre/TTC del
// termómetro) y duplicaba push/email. Y en la rama de RECHAZAR permitía rechazar
// una ya aceptada, dejando la venta huérfana. La aceptación crea la venta en la
// MISMA transacción, así que no existe un estado legítimo 'aceptada sin venta'
// que necesite re-entrar. El doble-clic queda cubierto por el guard
// venta_existente + el FOR UPDATE.
$estados_activos = ['enviada','vista'];

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

        // Total final — recalcular del lado del servidor, NO confiar en el cliente
        $cot_data = DB::row(
            "SELECT total, subtotal, impuesto_modo, impuesto_pct, created_at,
                    descuento_auto_activo, descuento_auto_pct, descuento_auto_expira
             FROM cotizaciones WHERE id=?", [$cot_id]
        );
        $lineas_sub = (float)DB::val(
            "SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=?", [$cot_id]
        );
        $subtotal_srv = $lineas_sub > 0 ? $lineas_sub : (float)$cot_data['subtotal'];

        $imp_modo = $cot_data['impuesto_modo'] ?? 'ninguno';
        $imp_pct  = (float)($cot_data['impuesto_pct'] ?? 0);
        $cupon_amt_srv = 0; $desc_auto_srv = 0;

        // ── Descuento Inteligente: si hay uno VIGENTE, MANDA. Aplica sobre el
        //    precio SIN extras (mismo criterio que el banner) e ignora
        //    cupón/manual (que por precedencia no existen si el inteligente
        //    disparó). % congelado en la activación (server-authoritative). ──
        $di_vig = null;
        try { $di_vig = DescuentoInteligente::vigente($cot_id); } catch (\Throwable $e) {}

        if ($di_vig) {
            // ── Contrato firme: se cobra el precio CONGELADO que vio y aceptó el
            //    cliente, NO se recomputa de líneas vivas. `nuevo_total` = base
            //    descontada CON IVA, sin extras (frozen en la activación). Si el
            //    asesor editó la cotización tras activar el DI, manda lo aceptado.
            //    Los extras SÍ son actuales (add-ons aparte, mostrados por separado
            //    en el banner). Usar el frozen también hace la reversa en ventas
            //    exacta (nuevo_total + monto_desc = precio_original, sin drift). ──
            $nuevo_base_congelado = round((float)$di_vig['nuevo_total'], 2);
            $extras_raw = (float)DB::val(
                "SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas
                 WHERE cotizacion_id=? AND es_extra=1", [$cot_id]);
            $total_guardar = round($nuevo_base_congelado + $extras_raw, 2);
            $cupon_codigo  = null; // el inteligente no se apila
            // WHERE estado='activo' evita doble-uso y carrera con vigente()→vencido.
            DB::execute("UPDATE desc_int_activaciones SET estado='utilizado' WHERE id=? AND estado='activo'", [(int)$di_vig['id']]);
        } else {
            // ── Cupón bloqueado si la cotización TIENE O TUVO un DI (decisión CEO
            //    16-jul): sin DI vigente (p.ej. venció sin usarse) se cobra precio
            //    COMPLETO — el cupón tampoco aplica. Espeja el gate del slug que
            //    oculta la sección de cupón cuando existe cualquier registro DI. ──
            if ($cupon_codigo) {
                try {
                    if (DB::val("SELECT 1 FROM desc_int_activaciones WHERE cotizacion_id = ? LIMIT 1", [$cot_id])) {
                        $cupon_codigo = null;
                    }
                } catch (\Throwable $e) {} // tabla sin migrar → sin bloqueo
            }
            // Cupón — re-validar server-side (se aplica primero, igual que guardar.php)
            if ($cupon_codigo) {
                $cupon_real = DB::row(
                    "SELECT id, porcentaje, monto_fijo, vencimiento_tipo, vencimiento_dias, vencimiento_fecha
                     FROM cupones WHERE empresa_id=? AND codigo=? AND activo=1",
                    [EMPRESA_ID, $cupon_codigo]
                );
                if ($cupon_real) {
                    // Validar VENCIMIENTO server-side (auditoría 17-jul): el JS ya lo
                    // hacía, pero un POST directo con un código vencido por fecha_fija
                    // o dias_cotizacion (que sigue activo=1) se cobraba con descuento.
                    // Misma fórmula que el slug (cotizacion.php).
                    $exp_cup = null;
                    if ($cupon_real['vencimiento_tipo'] === 'fecha_fija' && !empty($cupon_real['vencimiento_fecha'])) {
                        $exp_cup = $cupon_real['vencimiento_fecha'];
                    } elseif ($cupon_real['vencimiento_tipo'] === 'dias_cotizacion' && !empty($cupon_real['vencimiento_dias'])) {
                        $exp_cup = date('Y-m-d', strtotime($cot_data['created_at']) + ((int)$cupon_real['vencimiento_dias'] * 86400));
                    }
                    $cup_vencido = $exp_cup !== null && $exp_cup < date('Y-m-d');
                    if ($cup_vencido) {
                        $cupon_codigo = null; // vencido → no se aplica ni se guarda
                    } elseif ($cupon_real['monto_fijo'] !== null) {
                        $cupon_amt_srv = round(min((float)$cupon_real['monto_fijo'], $subtotal_srv), 2);
                    } else {
                        $cupon_pct = (float)$cupon_real['porcentaje'];
                        $cupon_amt_srv = round($subtotal_srv * $cupon_pct / 100, 2);
                    }
                }
            }
            // Descuento automático sobre el subtotal DESPUÉS del cupón
            $base_after_cupon = $subtotal_srv - $cupon_amt_srv;
            if (!empty($cot_data['descuento_auto_activo'])) {
                $exp = $cot_data['descuento_auto_expira'] ? strtotime($cot_data['descuento_auto_expira']) : 0;
                if (!$exp || $exp > time()) {
                    $desc_auto_srv = round($base_after_cupon * (float)$cot_data['descuento_auto_pct'] / 100, 2);
                }
            }
            $base_srv = $base_after_cupon - $desc_auto_srv;
            if ($imp_modo === 'suma') {
                $total_guardar = round($base_srv * (1 + $imp_pct / 100), 2);
            } else {
                $total_guardar = round(max(0, $base_srv), 2);
            }
        }

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

        // 2. Guardar cupón y descuento aplicados
        if ($cupon_codigo && $cupon_amt_srv > 0) {
            $cupon_db = DB::row("SELECT id, porcentaje FROM cupones WHERE empresa_id=? AND codigo=? AND activo=1", [EMPRESA_ID, $cupon_codigo]);
            if ($cupon_db) {
                DB::execute("UPDATE cotizaciones SET cupon_codigo=?, cupon_pct=?, cupon_monto=? WHERE id=?",
                    [$cupon_codigo, (float)$cupon_db['porcentaje'], $cupon_amt_srv, $cot_id]);
                DB::execute("UPDATE cupones SET usos=usos+1 WHERE id=?", [$cupon_db['id']]);
            }
        }
        DB::execute("UPDATE cotizaciones SET descuento_auto_amt=? WHERE id=?", [$desc_auto_srv, $cot_id]);

        // Congelar el original (líneas + descuento) antes de que la venta
        // pueda modificar cotizacion_lineas.
        snapshot_cotizacion($cot_id);

        // 3. Crear venta automáticamente — mismo momento que la aceptación
        // Si ya existe una venta para esta cotización no duplicar
        $venta_existente = DB::val("SELECT id FROM ventas WHERE cotizacion_id=? LIMIT 1", [$cot_id]);
        if (!$venta_existente) {
            $slug_vta    = slug_unico($cot['titulo'], 'ventas', 'slug', EMPRESA_ID);
            $token_vta   = generar_token(32);
            // Generar folio VTA-YYYY-NNNN
            $vta_prefijo = DB::val("SELECT vta_prefijo FROM empresas WHERE id=?", [EMPRESA_ID]) ?: 'VTA';
            $numero_vta  = DB::siguiente_folio(EMPRESA_ID, 'VTA', $vta_prefijo);

            // Asesor: heredar de la cotización
            $cot_usuario_id   = $cot['usuario_id'] ?: null;
            $cot_vendedor_id  = $cot['vendedor_id'] ?: null;

            DB::execute(
                "INSERT INTO ventas
                 (empresa_id, cotizacion_id, cliente_id, usuario_id, vendedor_id,
                  numero, titulo, slug, token,
                  total, pagado, saldo, descuento_auto_amt, cupon_monto, estado, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,0,?,?,?,'pendiente',NOW())",
                [
                    EMPRESA_ID,
                    $cot_id,
                    $cot['cliente_id'],
                    $cot_usuario_id,
                    $cot_vendedor_id,
                    $numero_vta,
                    $cot['titulo'],
                    $slug_vta,
                    $token_vta,
                    $total_guardar,
                    $total_guardar,
                    $desc_auto_srv,
                    $cupon_amt_srv,
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

        // Verificar si las notificaciones están activas
        $ncfg = notif_config(EMPRESA_ID);

        // Push notification a los usuarios de la empresa
        if ($ncfg['cotizacion_aceptada']) {
            try {
                PushNotification::enviar_a_empresa(
                    EMPRESA_ID,
                    'cotizacion_aceptada',
                    'Cotización aceptada',
                    $nombre . ' aceptó la cotización: ' . $cot['titulo'],
                    ['cotizacion_id' => $cot_id, 'url' => '/cotizaciones/' . $cot_id]
                );
            } catch (\Exception $e) {
                if (DEBUG) error_log('Push error: ' . $e->getMessage());
            }
        }

        // Email al correo de notificaciones de la empresa
        if ($ncfg['cotizacion_aceptada']) {
            try {
                $empresa_mail = DB::row("SELECT nombre, moneda, notif_email FROM empresas WHERE id=?", [EMPRESA_ID]);
                $notif_email = $empresa_mail['notif_email'] ?? '';
                if ($notif_email) {
                Mailer::enviar_cotizacion_aceptada(
                    $notif_email,
                    $empresa_mail['nombre'] ?? '',
                    $cot['titulo'],
                    $nombre,
                    $total_guardar,
                    $empresa_mail['moneda'] ?? 'MXN'
                );
            }
        } catch (\Exception $e) {
            if (DEBUG) error_log('Email aceptada error: ' . $e->getMessage());
        }
        }
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error al procesar']); exit;
    }

    // CAPI: enviar Lead server-side (auditoría 17-jul: usaba $empresa_id/$empresa/
    // $cot['total'] INDEFINIDOS → TypeError tragado por el catch → NUNCA se enviaba.
    // Ahora con EMPRESA_ID, el total realmente cobrado y la moneda de la empresa).
    try {
        MarketingPixels::capi_lead(EMPRESA_ID, (float)$total_guardar,
            DB::val("SELECT moneda FROM empresas WHERE id=?", [EMPRESA_ID]) ?: 'MXN');
    } catch (\Throwable $e) {}

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
        $ncfg_r = notif_config(EMPRESA_ID);
        if ($ncfg_r['cotizacion_rechazada']) {
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

        // Email al correo de notificaciones de la empresa
        try {
            $empresa_mail = DB::row("SELECT nombre, notif_email FROM empresas WHERE id=?", [EMPRESA_ID]);
            $notif_email = $empresa_mail['notif_email'] ?? '';
            if ($notif_email) {
                Mailer::enviar_cotizacion_rechazada(
                    $notif_email,
                    $empresa_mail['nombre'] ?? '',
                    $cot['titulo'],
                    $motivo
                );
            }
        } catch (\Exception $e) {
            if (DEBUG) error_log('Email rechazada error: ' . $e->getMessage());
        }
        } // cierre if ncfg cotizacion_rechazada
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error al procesar']); exit;
    }

    // CAPI: enviar QuoteRejected server-side ($empresa_id era indefinido → nunca corría)
    try { MarketingPixels::capi_rechazar(EMPRESA_ID); } catch (\Throwable $e) {}

    echo json_encode(['ok'=>true,'estado'=>'rechazada']); exit;
}
