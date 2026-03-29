<?php
// ============================================================
//  Crea cuenta de prueba para Apple App Review
//  Ejecutar en servidor: php migrations/create_review_user.php
//
//  Crea empresa demo con clientes, artículos, cotizaciones,
//  ventas y pagos para que Apple vea una app funcional.
// ============================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/DB.php';

$email    = 'review@cotiza.cloud';
$password = 'Review2026!';
$nombre   = 'Apple Review';
$slug     = 'apple-review';
$empresa  = 'Apple Review Demo';

// Verificar si ya existe
$existe = DB::query("SELECT id FROM empresas WHERE slug = ?", [$slug]);
if ($existe) {
    echo "La empresa '{$slug}' ya existe (id={$existe[0]['id']}).\n";
    echo "¿Desea eliminarla y recrearla? (s/n): ";
    $resp = trim(fgets(STDIN));
    if (strtolower($resp) !== 's') {
        echo "Abortando.\n";
        exit(0);
    }
    $eid = (int)$existe[0]['id'];
    // Eliminar en orden de dependencias
    DB::execute("DELETE FROM cotizacion_lineas WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id=?)", [$eid]);
    DB::execute("DELETE FROM ventas WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM cotizaciones WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM clientes WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM articulos WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM categorias_costos WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM user_sessions WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM usuarios WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM empresas WHERE id=?", [$eid]);
    echo "Empresa eliminada. Recreando...\n";
}

try {
    DB::beginTransaction();

    // ═══ EMPRESA ═══
    $empresa_id = DB::insert(
        "INSERT INTO empresas
         (slug, nombre, moneda, impuesto_modo, impuesto_pct, activa, plan, plan_vence)
         VALUES (?, ?, 'MXN', 'suma', 16.00, 1, 'pro', DATE_ADD(NOW(), INTERVAL 1 YEAR))",
        [$slug, $empresa]
    );

    // ═══ USUARIO ADMIN ═══
    $user_id = DB::insert(
        "INSERT INTO usuarios
         (empresa_id, nombre, email, password_hash, rol, activo)
         VALUES (?, ?, ?, ?, 'admin', 1)",
        [$empresa_id, $nombre, $email, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])]
    );

    // ═══ ARTÍCULOS / CATÁLOGO ═══
    $articulos = [
        ['Diseño de logotipo',              3500.00,  'Diseño profesional de marca'],
        ['Diseño de tarjetas de presentación', 1200.00, 'Incluye diseño frente y vuelta'],
        ['Página web básica (5 páginas)',    15000.00, 'Sitio web responsive con formulario de contacto'],
        ['Tienda en línea',                 28000.00, 'E-commerce completo con pasarela de pago'],
        ['Mantenimiento web mensual',        2500.00, 'Hosting, actualizaciones y soporte'],
        ['Campaña redes sociales (mensual)', 5000.00, '20 publicaciones + reportes'],
        ['Sesión fotográfica de productos',  4500.00, '20 fotos editadas en alta resolución'],
        ['Video promocional (30 seg)',       8000.00, 'Grabación, edición y motion graphics'],
    ];
    $art_ids = [];
    foreach ($articulos as [$art_nombre, $art_precio, $art_desc]) {
        $art_ids[] = DB::insert(
            "INSERT INTO articulos (empresa_id, nombre, precio, descripcion, activo)
             VALUES (?, ?, ?, ?, 1)",
            [$empresa_id, $art_nombre, $art_precio, $art_desc]
        );
    }

    // ═══ CLIENTES ═══
    $clientes = [
        ['María García López',     'maria@ejemplo.com',    '614-555-0101'],
        ['Carlos Hernández Ruiz',  'carlos@ejemplo.com',   '614-555-0102'],
        ['Ana Martínez Soto',      'ana@ejemplo.com',      '614-555-0103'],
        ['Roberto Díaz Morales',   'roberto@ejemplo.com',  '614-555-0104'],
        ['Laura Torres Vega',      'laura@ejemplo.com',    '614-555-0105'],
    ];
    $cli_ids = [];
    foreach ($clientes as [$cli_nombre, $cli_email, $cli_tel]) {
        $cli_ids[] = DB::insert(
            "INSERT INTO clientes (empresa_id, nombre, email, telefono)
             VALUES (?, ?, ?, ?)",
            [$empresa_id, $cli_nombre, $cli_email, $cli_tel]
        );
    }

    // ═══ COTIZACIONES ═══

    // Cotización 1: Enviada (María — diseño de marca)
    $cot1_sub = 3500 + 1200; // logo + tarjetas
    $cot1_imp = round($cot1_sub * 0.16, 2);
    $cot1_tot = $cot1_sub + $cot1_imp;
    $cot1_id = DB::insert(
        "INSERT INTO cotizaciones
         (empresa_id, usuario_id, cliente_id, folio, slug, titulo, subtotal, impuesto, total, estado, created_at)
         VALUES (?, ?, ?, 'COT-001', ?, 'Diseño de Marca Completo', ?, ?, ?, 'enviada', DATE_SUB(NOW(), INTERVAL 3 DAY))",
        [$empresa_id, $user_id, $cli_ids[0], 'diseno-marca-completo', $cot1_sub, $cot1_imp, $cot1_tot]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, concepto, cantidad, precio, subtotal, posicion)
        VALUES (?,?,?,1,3500,3500,1), (?,?,?,1,1200,1200,2)",
        [$cot1_id, $art_ids[0], 'Diseño de logotipo', $cot1_id, $art_ids[1], 'Diseño de tarjetas de presentación']);

    // Cotización 2: Vista (Carlos — página web)
    $cot2_sub = 15000;
    $cot2_imp = round($cot2_sub * 0.16, 2);
    $cot2_tot = $cot2_sub + $cot2_imp;
    $cot2_id = DB::insert(
        "INSERT INTO cotizaciones
         (empresa_id, usuario_id, cliente_id, folio, slug, titulo, subtotal, impuesto, total, estado, visitas, created_at, ultima_vista_at)
         VALUES (?, ?, ?, 'COT-002', ?, 'Página Web Corporativa', ?, ?, ?, 'vista', 3, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY))",
        [$empresa_id, $user_id, $cli_ids[1], 'pagina-web-corporativa', $cot2_sub, $cot2_imp, $cot2_tot]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, concepto, cantidad, precio, subtotal, posicion)
        VALUES (?,?,?,1,15000,15000,1)",
        [$cot2_id, $art_ids[2], 'Página web básica (5 páginas)']);

    // Cotización 3: Aceptada → Venta (Ana — tienda en línea + mantenimiento)
    $cot3_sub = 28000 + 2500;
    $cot3_imp = round($cot3_sub * 0.16, 2);
    $cot3_tot = $cot3_sub + $cot3_imp;
    $cot3_id = DB::insert(
        "INSERT INTO cotizaciones
         (empresa_id, usuario_id, cliente_id, folio, slug, titulo, subtotal, impuesto, total, estado, visitas, accion_at, created_at, ultima_vista_at)
         VALUES (?, ?, ?, 'COT-003', ?, 'Tienda en Línea + Mantenimiento', ?, ?, ?, 'aceptada', 5, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY))",
        [$empresa_id, $user_id, $cli_ids[2], 'tienda-linea-mantenimiento', $cot3_sub, $cot3_imp, $cot3_tot]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, concepto, cantidad, precio, subtotal, posicion)
        VALUES (?,?,?,1,28000,28000,1), (?,?,?,1,2500,2500,2)",
        [$cot3_id, $art_ids[3], 'Tienda en línea', $cot3_id, $art_ids[4], 'Mantenimiento web mensual']);

    // Venta de cotización 3 (parcialmente pagada)
    $vta1_id = DB::insert(
        "INSERT INTO ventas
         (empresa_id, cotizacion_id, cliente_id, usuario_id, folio, slug, titulo, subtotal, impuesto, total, pagado, estado, created_at)
         VALUES (?, ?, ?, ?, 'VTA-001', ?, 'Tienda en Línea + Mantenimiento', ?, ?, ?, 15000, 'parcial', DATE_SUB(NOW(), INTERVAL 2 DAY))",
        [$empresa_id, $cot3_id, $cli_ids[2], $user_id, 'tienda-linea-mantenimiento-vta', $cot3_sub, $cot3_imp, $cot3_tot]
    );
    // Registrar abono
    DB::execute(
        "INSERT INTO abonos (venta_id, empresa_id, monto, metodo, concepto, created_at)
         VALUES (?, ?, 15000, 'transferencia', 'Anticipo 50%', DATE_SUB(NOW(), INTERVAL 1 DAY))",
        [$vta1_id, $empresa_id]
    );

    // Cotización 4: Aceptada → Venta pagada (Roberto — campaña redes)
    $cot4_sub = 5000;
    $cot4_imp = round($cot4_sub * 0.16, 2);
    $cot4_tot = $cot4_sub + $cot4_imp;
    $cot4_id = DB::insert(
        "INSERT INTO cotizaciones
         (empresa_id, usuario_id, cliente_id, folio, slug, titulo, subtotal, impuesto, total, estado, visitas, accion_at, created_at, ultima_vista_at)
         VALUES (?, ?, ?, 'COT-004', ?, 'Campaña Redes Sociales Marzo', ?, ?, ?, 'aceptada', 2, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY))",
        [$empresa_id, $user_id, $cli_ids[3], 'campana-redes-marzo', $cot4_sub, $cot4_imp, $cot4_tot]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, concepto, cantidad, precio, subtotal, posicion)
        VALUES (?,?,?,1,5000,5000,1)",
        [$cot4_id, $art_ids[5], 'Campaña redes sociales (mensual)']);

    $vta2_id = DB::insert(
        "INSERT INTO ventas
         (empresa_id, cotizacion_id, cliente_id, usuario_id, folio, slug, titulo, subtotal, impuesto, total, pagado, estado, created_at)
         VALUES (?, ?, ?, ?, 'VTA-002', ?, 'Campaña Redes Sociales Marzo', ?, ?, ?, ?, 'pagada', DATE_SUB(NOW(), INTERVAL 7 DAY))",
        [$empresa_id, $cot4_id, $cli_ids[3], $user_id, 'campana-redes-marzo-vta', $cot4_sub, $cot4_imp, $cot4_tot, $cot4_tot]
    );
    DB::execute(
        "INSERT INTO abonos (venta_id, empresa_id, monto, metodo, concepto, created_at)
         VALUES (?, ?, ?, 'efectivo', 'Pago total', DATE_SUB(NOW(), INTERVAL 7 DAY))",
        [$vta2_id, $empresa_id, $cot4_tot]
    );

    // Cotización 5: Enviada reciente (Laura — video)
    $cot5_sub = 8000 + 4500;
    $cot5_imp = round($cot5_sub * 0.16, 2);
    $cot5_tot = $cot5_sub + $cot5_imp;
    $cot5_id = DB::insert(
        "INSERT INTO cotizaciones
         (empresa_id, usuario_id, cliente_id, folio, slug, titulo, subtotal, impuesto, total, estado, created_at)
         VALUES (?, ?, ?, 'COT-005', ?, 'Video Promocional + Fotos', ?, ?, ?, 'enviada', DATE_SUB(NOW(), INTERVAL 1 DAY))",
        [$empresa_id, $user_id, $cli_ids[4], 'video-promocional-fotos', $cot5_sub, $cot5_imp, $cot5_tot]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, concepto, cantidad, precio, subtotal, posicion)
        VALUES (?,?,?,1,8000,8000,1), (?,?,?,1,4500,4500,2)",
        [$cot5_id, $art_ids[7], 'Video promocional (30 seg)', $cot5_id, $art_ids[6], 'Sesión fotográfica de productos']);

    // ═══ ACTUALIZAR FOLIO COUNTER ═══
    DB::execute(
        "INSERT INTO empresa_config (empresa_id, config_key, config_value) VALUES (?, 'folio_cot', '5'), (?, 'folio_vta', '2')
         ON DUPLICATE KEY UPDATE config_value=VALUES(config_value)",
        [$empresa_id, $empresa_id]
    );

    DB::commit();

    echo "\n✅ Cuenta de review creada exitosamente:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "  Empresa:  {$empresa} (slug: {$slug})\n";
    echo "  URL:      https://{$slug}.cotiza.cloud/login\n";
    echo "  Email:    {$email}\n";
    echo "  Password: {$password}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "  Datos creados:\n";
    echo "  • 5 clientes\n";
    echo "  • 8 artículos en catálogo\n";
    echo "  • 5 cotizaciones (2 enviadas, 1 vista, 2 aceptadas)\n";
    echo "  • 2 ventas (1 parcial con abono, 1 pagada)\n";
    echo "\n";

} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
