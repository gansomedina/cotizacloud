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
$slug_req = trim((string)($body['slug'] ?? ''));

if (!$cot_id || !$accion) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Datos requeridos']); exit;
}

$acciones_validas = ['aceptar','rechazar'];
if (!in_array($accion, $acciones_validas)) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Acción inválida']); exit;
}

// ─── Cargar cotización ───────────────────────────────────
$cot = DB::row(
    "SELECT id, empresa_id, estado, suspendida, cliente_id, titulo, usuario_id, vendedor_id, slug FROM cotizaciones WHERE id = ? AND empresa_id = ?",
    [$cot_id, EMPRESA_ID]
);
if (!$cot) {
    http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Cotización no encontrada']); exit;
}
// El enlace público (slug secreto) ES la llave: ver la cotización ya lo exige,
// pero accionarla se hacía solo con el id numérico (enumerable). Exigir que el
// slug del enlace coincida cierra ese hueco sin exponer nada nuevo — la misma
// llave que ya se usó para abrir la página. hash_equals: comparación en tiempo
// constante (no filtra el slug por timing).
if ($slug_req === '' || !hash_equals((string)$cot['slug'], $slug_req)) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Enlace no válido. Actualiza la página e intenta de nuevo.']); exit;
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
        // Base sin extras (descontable) y extras (add-ons, gravables no descontables)
        $base_ne_srv = (float)DB::val(
            "SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=? AND es_extra=0", [$cot_id]);
        $extras_srv  = (float)DB::val(
            "SELECT COALESCE(SUM(subtotal),0) FROM cotizacion_lineas WHERE cotizacion_id=? AND es_extra=1", [$cot_id]);
        $subtotal_srv = ($base_ne_srv + $extras_srv) > 0 ? $base_ne_srv : (float)$cot_data['subtotal'];

        // IVA de la CONFIGURACIÓN vigente de la empresa (decisión CEO 18-jul: se
        // cobra como esté configurado HOY, igual que lo muestra el enlace público
        // —no el valor congelado al crear—; si la empresa cambia su IVA, las
        // cotizaciones ya enviadas se cobran con el nuevo). Fallback al congelado
        // si el SELECT fallara, para nunca quedar sin IVA.
        $emp_iva  = DB::row("SELECT impuesto_modo, impuesto_pct FROM empresas WHERE id=?", [EMPRESA_ID]);
        $imp_modo = $emp_iva['impuesto_modo'] ?? ($cot_data['impuesto_modo'] ?? 'ninguno');
        $imp_pct  = (float)($emp_iva['impuesto_pct'] ?? $cot_data['impuesto_pct'] ?? 0);
        $cupon_amt_srv = 0; $desc_auto_srv = 0;

        // ── Descuento Inteligente: si hay uno VIGENTE, MANDA. Aplica sobre el
        //    precio SIN extras (mismo criterio que el banner) e ignora
        //    cupón/manual (que por precedencia no existen si el inteligente
        //    disparó). % congelado en la activación (server-authoritative). ──
        $di_vig = null;
        try { $di_vig = DescuentoInteligente::vigente($cot_id); } catch (\Throwable $e) {}

        // ── Carrera del DI (auditoría 17-jul): si el cliente cargó la página con
        //    un descuento VIGENTE y hace clic en aceptar JUSTO DESPUÉS de que
        //    expiró, vigente() lo marcó 'vencido' y devolvió null → antes se
        //    cobraba precio COMPLETO en SILENCIO (el cliente vio "Ahora $X").
        //    Rechazamos con mensaje para que recargue y vea el precio real. El
        //    gate `di_visto` (el slug lo manda solo si renderizó el DI activo)
        //    limita el rechazo a ESA sesión: tras recargar, el slug ya no manda
        //    di_visto → el cliente SÍ puede aceptar a precio completo (sin loop).
        //    di_visto es del cliente pero es inofensivo: solo elige rechazo-vs-
        //    cobro-completo, nunca aplica un descuento.
        if (!$di_vig && !empty($body['di_visto'])) {
            $di_venc = false;
            try {
                $di_venc = (bool)DB::val(
                    "SELECT 1 FROM desc_int_activaciones
                     WHERE cotizacion_id=? AND estado='vencido' LIMIT 1", [$cot_id]);
            } catch (\Throwable $e) {}
            if ($di_venc) {
                DB::rollback();
                echo json_encode(['ok'=>false,'error'=>'El descuento especial venció. Actualiza la página para ver el precio vigente.']); exit;
            }
        }

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
            // Extras gravables (IVA si suma), no descontables — el nuevo_total ya
            // trae el IVA de la base descontada.
            $extras_final = ($imp_modo === 'suma')
                ? round($extras_raw + round($extras_raw * $imp_pct / 100, 2), 2)
                : $extras_raw;
            $total_guardar = round($nuevo_base_congelado + $extras_final, 2);
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
            // Extras (add-ons): NO descontables pero SÍ gravables. La base sin
            // extras se descuenta; luego los extras entran a la base gravable.
            $base_srv = $base_after_cupon - $desc_auto_srv;
            $taxable  = max(0, $base_srv) + $extras_srv;
            if ($imp_modo === 'suma') {
                $total_guardar = round($taxable + round($taxable * $imp_pct / 100, 2), 2);
            } else {
                $total_guardar = round($taxable, 2);
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

        // Push + email DIFERIDOS (mismo patron que public/cotizacion.php:363).
        // Dos razones:
        //  1. El cliente ve su confirmacion al instante; los avisos salen despues,
        //     con la conexion ya cerrada. Antes esperaba el push Y el correo.
        //  2. Quedan FUERA del try de la transaccion: un fallo de correo ya no
        //     dispara el rollback ni devuelve 500 sobre una cotizacion que SI se
        //     acepto y SI se guardo.
        if ($ncfg['cotizacion_aceptada']) {
            $nt_eid    = EMPRESA_ID;
            $nt_cotid  = (int)$cot_id;
            $nt_titulo = (string)($cot['titulo'] ?? '');
            $nt_nombre = (string)$nombre;
            $nt_total  = (float)$total_guardar;
            register_shutdown_function(function () use ($nt_eid, $nt_cotid, $nt_titulo, $nt_nombre, $nt_total) {
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                try {
                    PushNotification::enviar_a_empresa(
                        $nt_eid,
                        'cotizacion_aceptada',
                        'Cotización aceptada',
                        $nt_nombre . ' aceptó la cotización: ' . $nt_titulo,
                        ['cotizacion_id' => $nt_cotid, 'url' => '/cotizaciones/' . $nt_cotid]
                    );
                } catch (\Throwable $e) {
                    error_log('Push aceptada error: ' . $e->getMessage());
                }
                try {
                    $empresa_mail = DB::row("SELECT nombre, moneda, notif_email FROM empresas WHERE id=?", [$nt_eid]);
                    $notif_email  = $empresa_mail['notif_email'] ?? '';
                    if ($notif_email) {
                        Mailer::enviar_cotizacion_aceptada(
                            $notif_email,
                            $empresa_mail['nombre'] ?? '',
                            $nt_titulo,
                            $nt_nombre,
                            $nt_total,
                            $empresa_mail['moneda'] ?? 'MXN'
                        );
                    }
                } catch (\Throwable $e) {
                    error_log('Email aceptada error: ' . $e->getMessage());
                }
            });
        }
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error al procesar']); exit;
    }

    // CAPI: enviar Lead server-side (auditoría 17-jul: usaba $empresa_id/$empresa/
    // $cot['total'] INDEFINIDOS → TypeError tragado por el catch → NUNCA se enviaba.
    // Ahora con EMPRESA_ID, el total realmente cobrado y la moneda de la empresa).
    // CAPI DIFERIDO: es una llamada HTTP a Meta; el cliente no tiene por que esperarla.
    $capi_eid   = EMPRESA_ID;
    $capi_total = (float)$total_guardar;
    register_shutdown_function(function () use ($capi_eid, $capi_total) {
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        try {
            MarketingPixels::capi_lead($capi_eid, $capi_total,
                DB::val("SELECT moneda FROM empresas WHERE id=?", [$capi_eid]) ?: 'MXN');
        } catch (\Throwable $e) {
            error_log('CAPI lead error: ' . $e->getMessage());
        }
    });

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
        // Push + email DIFERIDOS — misma razon que en la rama de aceptar:
        // el cliente no espera los avisos, y un fallo de correo ya no puede
        // provocar rollback ni 500 sobre un rechazo que si se guardo.
        if ($ncfg_r['cotizacion_rechazada']) {
            $nr_eid    = EMPRESA_ID;
            $nr_cotid  = (int)$cot_id;
            $nr_titulo = (string)($cot['titulo'] ?? '');
            $nr_motivo = (string)$motivo;
            register_shutdown_function(function () use ($nr_eid, $nr_cotid, $nr_titulo, $nr_motivo) {
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                try {
                    PushNotification::enviar_a_empresa(
                        $nr_eid,
                        'cotizacion_rechazada',
                        'Cotización rechazada',
                        'La cotización "' . $nr_titulo . '" fue rechazada' . ($nr_motivo !== '' ? ': ' . $nr_motivo : ''),
                        ['cotizacion_id' => $nr_cotid, 'url' => '/cotizaciones/' . $nr_cotid]
                    );
                } catch (\Throwable $e) {
                    error_log('Push rechazada error: ' . $e->getMessage());
                }
                try {
                    $empresa_mail = DB::row("SELECT nombre, notif_email FROM empresas WHERE id=?", [$nr_eid]);
                    $notif_email  = $empresa_mail['notif_email'] ?? '';
                    if ($notif_email) {
                        Mailer::enviar_cotizacion_rechazada(
                            $notif_email,
                            $empresa_mail['nombre'] ?? '',
                            $nr_titulo,
                            $nr_motivo
                        );
                    }
                } catch (\Throwable $e) {
                    error_log('Email rechazada error: ' . $e->getMessage());
                }
            });
        } // cierre if ncfg cotizacion_rechazada
    } catch (Exception $e) {
        DB::rollback();
        if (DEBUG) throw $e;
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error al procesar']); exit;
    }

    // CAPI: enviar QuoteRejected server-side ($empresa_id era indefinido → nunca corría)
    // CAPI DIFERIDO — misma razon que en la rama de aceptar.
    $capi_eid_r = EMPRESA_ID;
    register_shutdown_function(function () use ($capi_eid_r) {
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        try {
            MarketingPixels::capi_rechazar($capi_eid_r);
        } catch (\Throwable $e) {
            error_log('CAPI rechazar error: ' . $e->getMessage());
        }
    });

    echo json_encode(['ok'=>true,'estado'=>'rechazada']); exit;
}
