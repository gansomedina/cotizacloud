-- ============================================================
--  REIMPORTACIÓN LIMPIA - Empresa 2
--  Ejecutar DESPUÉS de limpiar_empresa2.sql
--
--  Orden:
--    PASO 1: source import_all_cotizaciones.sql  (830 cotizaciones)
--    PASO 2: source import_lineas.sql            (1106 líneas)
--    PASO 3: Ejecutar ESTE archivo               (fix totales + ventas)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ════════════════════════════════════════════════════════════
--  PASO 3A: Recalcular totales de cotizaciones desde líneas
--  El total de WordPress no cuadra con la suma de las líneas.
--  El total correcto es: SUM(cotizacion_lineas.subtotal)
-- ════════════════════════════════════════════════════════════

-- Diagnóstico: cuántas cotizaciones tienen líneas vs no
SELECT
    'con_lineas' AS tipo,
    COUNT(*) AS cantidad
FROM cotizaciones c
WHERE c.empresa_id = 2 AND c.slug LIKE 'imp-quo-%'
  AND EXISTS (SELECT 1 FROM cotizacion_lineas cl WHERE cl.cotizacion_id = c.id)
UNION ALL
SELECT
    'sin_lineas',
    COUNT(*)
FROM cotizaciones c
WHERE c.empresa_id = 2 AND c.slug LIKE 'imp-quo-%'
  AND NOT EXISTS (SELECT 1 FROM cotizacion_lineas cl WHERE cl.cotizacion_id = c.id);

-- Recalcular: total = SUM(lineas.subtotal) para cotizaciones QUE TIENEN líneas
UPDATE cotizaciones c
INNER JOIN (
    SELECT cotizacion_id, SUM(subtotal) AS sum_lineas
    FROM cotizacion_lineas
    GROUP BY cotizacion_id
) sl ON sl.cotizacion_id = c.id
SET c.subtotal = sl.sum_lineas,
    c.total = CASE
        WHEN c.impuesto_modo = 'sumado' THEN ROUND(sl.sum_lineas * (1 + c.impuesto_pct / 100), 2)
        WHEN c.impuesto_modo = 'incluido' THEN sl.sum_lineas
        ELSE sl.sum_lineas
    END,
    c.impuesto_amt = CASE
        WHEN c.impuesto_modo = 'sumado' THEN ROUND(sl.sum_lineas * c.impuesto_pct / 100, 2)
        WHEN c.impuesto_modo = 'incluido' THEN ROUND(sl.sum_lineas - sl.sum_lineas / (1 + c.impuesto_pct / 100), 2)
        ELSE 0
    END,
    c.updated_at = NOW()
WHERE c.empresa_id = 2
  AND c.slug LIKE 'imp-quo-%';

-- Verificar: top 10 diferencias más grandes (deberían ser 0 ahora)
SELECT c.id, c.numero, c.total AS total_nuevo,
       COALESCE(sl.sum_lineas, 0) AS sum_lineas,
       ROUND(c.total - COALESCE(sl.sum_lineas, 0), 2) AS diferencia
FROM cotizaciones c
LEFT JOIN (
    SELECT cotizacion_id, SUM(subtotal) AS sum_lineas
    FROM cotizacion_lineas
    GROUP BY cotizacion_id
) sl ON sl.cotizacion_id = c.id
WHERE c.empresa_id = 2
  AND c.slug LIKE 'imp-quo-%'
  AND ABS(c.total - COALESCE(sl.sum_lineas, 0)) > 0.01
ORDER BY ABS(c.total - COALESCE(sl.sum_lineas, 0)) DESC
LIMIT 10;

-- ════════════════════════════════════════════════════════════
--  PASO 3B: Crear ventas para cotizaciones aceptadas
--  Ahora con los totales correctos
-- ════════════════════════════════════════════════════════════

INSERT INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id,
                    numero, titulo, slug, token,
                    total, pagado, saldo, estado,
                    created_at, updated_at)
SELECT
    c.empresa_id,
    c.id,
    c.cliente_id,
    c.usuario_id,
    CONCAT('VTA-IMP-', c.id),
    c.titulo,
    CONCAT('imp-vta-', c.id),
    SHA2(CONCAT('cotizacloud-import-vta-', c.id), 256),
    c.total,          -- ahora viene de SUM(lineas), no de WordPress
    c.total,
    0.00,
    'pagada',
    c.created_at,
    c.created_at
FROM cotizaciones c
WHERE c.empresa_id = 2
  AND c.estado = 'aceptada'
  AND c.slug LIKE 'imp-quo-%'
  AND NOT EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = c.id);

-- Ligar líneas a la venta
UPDATE cotizacion_lineas cl
INNER JOIN ventas v ON v.cotizacion_id = cl.cotizacion_id
SET cl.venta_id = v.id
WHERE v.empresa_id = 2
  AND v.slug LIKE 'imp-vta-%'
  AND cl.venta_id IS NULL;

-- Marcar cotizaciones como convertidas
UPDATE cotizaciones c
INNER JOIN ventas v ON v.cotizacion_id = c.id
SET c.estado = 'convertida', c.updated_at = NOW()
WHERE c.empresa_id = 2
  AND c.estado = 'aceptada'
  AND c.slug LIKE 'imp-quo-%'
  AND v.slug LIKE 'imp-vta-%';

-- Actualizar folio de ventas
INSERT INTO folios (empresa_id, tipo, anio, ultimo)
SELECT 2, 'VTA', YEAR(NOW()),
       (SELECT COUNT(*) FROM ventas WHERE empresa_id = 2)
ON DUPLICATE KEY UPDATE ultimo = GREATEST(ultimo,
       (SELECT COUNT(*) FROM ventas WHERE empresa_id = 2));

SET FOREIGN_KEY_CHECKS = 1;

-- ════════════════════════════════════════════════════════════
--  VERIFICACIÓN FINAL
-- ════════════════════════════════════════════════════════════
SELECT 'cotizaciones' AS tabla, COUNT(*) AS total FROM cotizaciones WHERE empresa_id = 2
UNION ALL SELECT 'cotizacion_lineas', COUNT(*) FROM cotizacion_lineas cl INNER JOIN cotizaciones c ON c.id = cl.cotizacion_id WHERE c.empresa_id = 2
UNION ALL SELECT 'ventas', COUNT(*) FROM ventas WHERE empresa_id = 2;

-- Ventas con sus totales (muestra las primeras 10)
SELECT v.numero, v.total, c.numero AS cotizacion,
       COALESCE(sl.sum_lineas, 0) AS sum_lineas,
       v.total - COALESCE(sl.sum_lineas, 0) AS diferencia
FROM ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
LEFT JOIN (
    SELECT cotizacion_id, SUM(subtotal) AS sum_lineas
    FROM cotizacion_lineas
    GROUP BY cotizacion_id
) sl ON sl.cotizacion_id = c.id
WHERE v.empresa_id = 2
ORDER BY v.created_at DESC
LIMIT 15;
