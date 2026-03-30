<?php
// ============================================================
//  Crea cuenta de prueba para Apple App Review
//  Ejecutar: php migrations/create_review_user.php
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
    echo "Eliminando y recreando...\n";
    $eid = (int)$existe[0]['id'];
    DB::execute("DELETE cl FROM cotizacion_lineas cl JOIN cotizaciones c ON c.id=cl.cotizacion_id WHERE c.empresa_id=?", [$eid]);
    DB::execute("DELETE FROM recibos WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM ventas WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM cotizaciones WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM clientes WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM articulos WHERE empresa_id=?", [$eid]);
    try { DB::execute("DELETE FROM categorias_costos WHERE empresa_id=?", [$eid]); } catch (\Throwable $e) {}
    try { DB::execute("DELETE FROM usuario_score WHERE empresa_id=?", [$eid]); } catch (\Throwable $e) {}
    DB::execute("DELETE FROM user_sessions WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM usuarios WHERE empresa_id=?", [$eid]);
    DB::execute("DELETE FROM empresas WHERE id=?", [$eid]);
}

try {
    DB::beginTransaction();

    // ═══ EMPRESA ═══
    $empresa_id = DB::insert(
        "INSERT INTO empresas (slug, nombre, moneda, impuesto_modo, impuesto_pct, activa, plan, plan_vence)
         VALUES (?, ?, 'MXN', 'suma', 16.00, 1, 'pro', DATE_ADD(NOW(), INTERVAL 1 YEAR))",
        [$slug, $empresa]
    );

    // ═══ USUARIO ADMIN ═══
    $user_id = DB::insert(
        "INSERT INTO usuarios (empresa_id, nombre, email, password_hash, rol, activo)
         VALUES (?, ?, ?, ?, 'admin', 1)",
        [$empresa_id, $nombre, $email, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])]
    );

    // ═══ ARTÍCULOS ═══
    $articulos = [
        ['Diseño de logotipo',               3500.00],
        ['Tarjetas de presentación',          1200.00],
        ['Página web básica (5 páginas)',    15000.00],
        ['Tienda en línea',                 28000.00],
        ['Mantenimiento web mensual',        2500.00],
        ['Campaña redes sociales (mensual)', 5000.00],
        ['Sesión fotográfica de productos',  4500.00],
        ['Video promocional (30 seg)',       8000.00],
    ];
    $art_ids = [];
    foreach ($articulos as [$art_titulo, $art_precio]) {
        $art_ids[] = DB::insert(
            "INSERT INTO articulos (empresa_id, titulo, precio, activo) VALUES (?, ?, ?, 1)",
            [$empresa_id, $art_titulo, $art_precio]
        );
    }

    // ═══ CLIENTES ═══
    $clientes = [
        ['María García López',     '614-555-0101'],
        ['Carlos Hernández Ruiz',  '614-555-0102'],
        ['Ana Martínez Soto',      '614-555-0103'],
        ['Roberto Díaz Morales',   '614-555-0104'],
        ['Laura Torres Vega',      '614-555-0105'],
    ];
    $cli_ids = [];
    foreach ($clientes as [$cli_nombre, $cli_tel]) {
        $cli_ids[] = DB::insert(
            "INSERT INTO clientes (empresa_id, usuario_id, nombre, telefono) VALUES (?, ?, ?, ?)",
            [$empresa_id, $user_id, $cli_nombre, $cli_tel]
        );
    }

    // ═══ COTIZACIONES ═══
    $token = fn() => bin2hex(random_bytes(16));
    $imp_modo = 'suma';
    $imp_pct  = 16.00;

    // COT-001: Enviada (María — diseño de marca)
    $s1 = 3500 + 1200; $i1 = round($s1 * 0.16, 2); $t1 = $s1 + $i1;
    $c1 = DB::insert(
        "INSERT INTO cotizaciones (empresa_id, usuario_id, cliente_id, numero, slug, token, titulo, subtotal, impuesto_modo, impuesto_pct, impuesto_amt, total, estado, created_at)
         VALUES (?, ?, ?, 'COT-2026-0001', ?, ?, 'Diseño de Marca Completo', ?, ?, ?, ?, ?, 'enviada', DATE_SUB(NOW(), INTERVAL 3 DAY))",
        [$empresa_id, $user_id, $cli_ids[0], 'diseno-marca-completo', $token(), $s1, $imp_modo, $imp_pct, $i1, $t1]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, orden, titulo, cantidad, precio_unit, subtotal) VALUES (?,?,1,?,1,3500,3500), (?,?,2,?,1,1200,1200)",
        [$c1, $art_ids[0], 'Diseño de logotipo', $c1, $art_ids[1], 'Tarjetas de presentación']);

    // COT-002: Vista (Carlos — página web)
    $s2 = 15000; $i2 = round($s2 * 0.16, 2); $t2 = $s2 + $i2;
    $c2 = DB::insert(
        "INSERT INTO cotizaciones (empresa_id, usuario_id, cliente_id, numero, slug, token, titulo, subtotal, impuesto_modo, impuesto_pct, impuesto_amt, total, estado, visitas, created_at, ultima_vista_at)
         VALUES (?, ?, ?, 'COT-2026-0002', ?, ?, 'Página Web Corporativa', ?, ?, ?, ?, ?, 'vista', 3, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY))",
        [$empresa_id, $user_id, $cli_ids[1], 'pagina-web-corporativa', $token(), $s2, $imp_modo, $imp_pct, $i2, $t2]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, orden, titulo, cantidad, precio_unit, subtotal) VALUES (?,?,1,?,1,15000,15000)",
        [$c2, $art_ids[2], 'Página web básica (5 páginas)']);

    // COT-003: Aceptada → Venta parcial (Ana — tienda + mantenimiento)
    $s3 = 28000 + 2500; $i3 = round($s3 * 0.16, 2); $t3 = $s3 + $i3;
    $c3 = DB::insert(
        "INSERT INTO cotizaciones (empresa_id, usuario_id, cliente_id, numero, slug, token, titulo, subtotal, impuesto_modo, impuesto_pct, impuesto_amt, total, estado, visitas, accion_at, created_at, ultima_vista_at)
         VALUES (?, ?, ?, 'COT-2026-0003', ?, ?, 'Tienda en Línea + Mantenimiento', ?, ?, ?, ?, ?, 'aceptada', 5, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY))",
        [$empresa_id, $user_id, $cli_ids[2], 'tienda-linea-mantenimiento', $token(), $s3, $imp_modo, $imp_pct, $i3, $t3]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, orden, titulo, cantidad, precio_unit, subtotal) VALUES (?,?,1,?,1,28000,28000), (?,?,2,?,1,2500,2500)",
        [$c3, $art_ids[3], 'Tienda en línea', $c3, $art_ids[4], 'Mantenimiento web mensual']);

    // Venta parcial (anticipo 50%)
    $anticipo = 15000;
    $tk1 = $token();
    $v1 = DB::insert(
        "INSERT INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, slug, token, titulo, total, pagado, saldo, estado, created_at)
         VALUES (?, ?, ?, ?, 'VTA-2026-0001', ?, ?, 'Tienda en Línea + Mantenimiento', ?, ?, ?, 'parcial', DATE_SUB(NOW(), INTERVAL 2 DAY))",
        [$empresa_id, $c3, $cli_ids[2], $user_id, 'tienda-linea-mantenimiento-vta', $tk1, $t3, $anticipo, $t3 - $anticipo]
    );
    DB::insert(
        "INSERT INTO recibos (empresa_id, venta_id, numero, token, monto, concepto, forma_pago, usuario_id, pagado_antes, saldo_despues, fecha, created_at)
         VALUES (?, ?, 'REC-2026-0001', ?, ?, 'Anticipo 50%', 'transferencia', ?, 0, ?, CURDATE(), DATE_SUB(NOW(), INTERVAL 1 DAY))",
        [$empresa_id, $v1, $token(), $anticipo, $user_id, $t3 - $anticipo]
    );

    // COT-004: Aceptada → Venta pagada (Roberto — campaña redes)
    $s4 = 5000; $i4 = round($s4 * 0.16, 2); $t4 = $s4 + $i4;
    $c4 = DB::insert(
        "INSERT INTO cotizaciones (empresa_id, usuario_id, cliente_id, numero, slug, token, titulo, subtotal, impuesto_modo, impuesto_pct, impuesto_amt, total, estado, visitas, accion_at, created_at, ultima_vista_at)
         VALUES (?, ?, ?, 'COT-2026-0004', ?, ?, 'Campaña Redes Sociales Marzo', ?, ?, ?, ?, ?, 'aceptada', 2, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY))",
        [$empresa_id, $user_id, $cli_ids[3], 'campana-redes-marzo', $token(), $s4, $imp_modo, $imp_pct, $i4, $t4]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, orden, titulo, cantidad, precio_unit, subtotal) VALUES (?,?,1,?,1,5000,5000)",
        [$c4, $art_ids[5], 'Campaña redes sociales (mensual)']);

    $tk2 = $token();
    $v2 = DB::insert(
        "INSERT INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, slug, token, titulo, total, pagado, saldo, estado, created_at)
         VALUES (?, ?, ?, ?, 'VTA-2026-0002', ?, ?, 'Campaña Redes Sociales Marzo', ?, ?, 0, 'pagada', DATE_SUB(NOW(), INTERVAL 7 DAY))",
        [$empresa_id, $c4, $cli_ids[3], $user_id, 'campana-redes-marzo-vta', $tk2, $t4, $t4]
    );
    DB::insert(
        "INSERT INTO recibos (empresa_id, venta_id, numero, token, monto, concepto, forma_pago, usuario_id, pagado_antes, saldo_despues, fecha, created_at)
         VALUES (?, ?, 'REC-2026-0002', ?, ?, 'Pago total', 'efectivo', ?, 0, 0, CURDATE(), DATE_SUB(NOW(), INTERVAL 7 DAY))",
        [$empresa_id, $v2, $token(), $t4, $user_id]
    );

    // COT-005: Enviada reciente (Laura — video + fotos)
    $s5 = 8000 + 4500; $i5 = round($s5 * 0.16, 2); $t5 = $s5 + $i5;
    $c5 = DB::insert(
        "INSERT INTO cotizaciones (empresa_id, usuario_id, cliente_id, numero, slug, token, titulo, subtotal, impuesto_modo, impuesto_pct, impuesto_amt, total, estado, created_at)
         VALUES (?, ?, ?, 'COT-2026-0005', ?, ?, 'Video Promocional + Fotos', ?, ?, ?, ?, ?, 'enviada', DATE_SUB(NOW(), INTERVAL 1 DAY))",
        [$empresa_id, $user_id, $cli_ids[4], 'video-promocional-fotos', $token(), $s5, $imp_modo, $imp_pct, $i5, $t5]
    );
    DB::execute("INSERT INTO cotizacion_lineas (cotizacion_id, articulo_id, orden, titulo, cantidad, precio_unit, subtotal) VALUES (?,?,1,?,1,8000,8000), (?,?,2,?,1,4500,4500)",
        [$c5, $art_ids[7], 'Video promocional (30 seg)', $c5, $art_ids[6], 'Sesión fotográfica de productos']);

    DB::commit();

    echo "\n✅ Cuenta de review creada:\n";
    echo "  URL:      https://{$slug}.cotiza.cloud/login\n";
    echo "  Email:    {$email}\n";
    echo "  Password: {$password}\n";
    echo "  Datos: 5 clientes, 8 artículos, 5 cotizaciones, 2 ventas\n\n";

} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
