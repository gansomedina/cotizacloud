-- ============================================================
--  Diagnóstico: ventas importadas que duplican ventas reales
-- ============================================================

-- 1. Ventas importadas que tienen el mismo cliente y monto que una venta real
--    (probable duplicado)
SELECT
    vi.id AS imp_id, vi.numero AS imp_numero, vi.total AS imp_total,
    vi.created_at AS imp_fecha, vi.estado AS imp_estado,
    vr.id AS real_id, vr.numero AS real_numero, vr.total AS real_total,
    vr.created_at AS real_fecha,
    cl.nombre AS cliente
FROM ventas vi
INNER JOIN ventas vr
    ON vr.empresa_id = vi.empresa_id
    AND vr.cliente_id = vi.cliente_id
    AND vr.total = vi.total
    AND vr.id != vi.id
    AND vr.slug NOT LIKE 'imp-vta-%'
LEFT JOIN clientes cl ON cl.id = vi.cliente_id
WHERE vi.empresa_id = 2
  AND vi.slug LIKE 'imp-vta-%'
ORDER BY vi.created_at DESC;

-- 2. Todas las ventas importadas (para revisar cuáles son válidas)
SELECT id, numero, titulo, total, estado, created_at,
       cliente_id, cotizacion_id
FROM ventas
WHERE empresa_id = 2 AND slug LIKE 'imp-vta-%'
ORDER BY created_at DESC;

-- 3. Resumen: cuántas importadas vs reales
SELECT
    CASE WHEN slug LIKE 'imp-vta-%' THEN 'IMPORTADA' ELSE 'REAL' END AS tipo,
    COUNT(*) AS num_ventas,
    SUM(total) AS sum_total
FROM ventas
WHERE empresa_id = 2 AND estado != 'cancelada'
GROUP BY tipo;
