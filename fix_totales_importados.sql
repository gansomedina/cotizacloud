-- ============================================================
--  FIX importación: totales inflados + ventas duplicadas
-- ============================================================

-- ════════════════════════════════════════════════════════════
--  PROBLEMA 1: Ventas importadas que duplican ventas reales
--  (mismo cliente + mismo monto = misma venta)
-- ════════════════════════════════════════════════════════════

-- 1A. DIAGNÓSTICO: Ver ventas importadas que duplican reales
SELECT
    vi.id   AS imp_id,   vi.numero AS imp_folio,   vi.total AS imp_total,  vi.created_at AS imp_fecha,
    vr.id   AS real_id,  vr.numero AS real_folio,   vr.total AS real_total, vr.created_at AS real_fecha,
    cl.nombre AS cliente
FROM ventas vi
INNER JOIN ventas vr
    ON  vr.empresa_id = vi.empresa_id
    AND vr.cliente_id = vi.cliente_id
    AND vr.total      = vi.total
    AND vr.id        != vi.id
    AND vr.slug NOT LIKE 'imp-vta-%'
LEFT JOIN clientes cl ON cl.id = vi.cliente_id
WHERE vi.empresa_id = 2
  AND vi.slug LIKE 'imp-vta-%'
  AND vi.estado != 'cancelada'
ORDER BY vi.created_at DESC;

-- 1B. FIX: Cancelar ventas importadas que son duplicados de reales
--     (no eliminar, cancelar para mantener registro)
-- NOTA: Descomentar después de revisar el diagnóstico 1A

-- UPDATE ventas vi
-- INNER JOIN ventas vr
--     ON  vr.empresa_id = vi.empresa_id
--     AND vr.cliente_id = vi.cliente_id
--     AND vr.total      = vi.total
--     AND vr.id        != vi.id
--     AND vr.slug NOT LIKE 'imp-vta-%'
-- SET vi.estado = 'cancelada',
--     vi.cancelado_at = NOW(),
--     vi.cancelado_motivo = 'Duplicado de venta real: ' || vr.numero,
--     vi.updated_at = NOW()
-- WHERE vi.empresa_id = 2
--   AND vi.slug LIKE 'imp-vta-%'
--   AND vi.estado != 'cancelada';

-- ════════════════════════════════════════════════════════════
--  PROBLEMA 2: Totales de cotizaciones no cuadran con líneas
-- ════════════════════════════════════════════════════════════

-- 2A. DIAGNÓSTICO: Cotizaciones importadas con total ≠ suma de líneas
SELECT c.id, c.numero, c.titulo,
       c.total AS total_bd,
       COALESCE(sl.sum_lineas, 0) AS total_lineas,
       ROUND(c.total - COALESCE(sl.sum_lineas, 0), 2) AS diferencia,
       c.impuesto_pct, c.impuesto_modo
FROM cotizaciones c
LEFT JOIN (
    SELECT cotizacion_id, SUM(subtotal) AS sum_lineas
    FROM cotizacion_lineas
    GROUP BY cotizacion_id
) sl ON sl.cotizacion_id = c.id
WHERE c.empresa_id = 2
  AND c.slug LIKE 'imp-quo-%'
  AND ABS(c.total - COALESCE(sl.sum_lineas, 0)) > 0.01
ORDER BY ABS(c.total - COALESCE(sl.sum_lineas, 0)) DESC;

-- 2B. FIX COTIZACIONES: Recalcular subtotal y total desde líneas
-- NOTA: Descomentar después de revisar el diagnóstico 2A

-- UPDATE cotizaciones c
-- INNER JOIN (
--     SELECT cotizacion_id, SUM(subtotal) AS sum_lineas
--     FROM cotizacion_lineas
--     GROUP BY cotizacion_id
-- ) sl ON sl.cotizacion_id = c.id
-- SET c.subtotal = sl.sum_lineas,
--     c.total = CASE
--         WHEN c.impuesto_modo = 'sumado' THEN ROUND(sl.sum_lineas * (1 + c.impuesto_pct / 100), 2)
--         ELSE sl.sum_lineas
--     END,
--     c.impuesto_amt = CASE
--         WHEN c.impuesto_modo = 'sumado' THEN ROUND(sl.sum_lineas * c.impuesto_pct / 100, 2)
--         WHEN c.impuesto_modo = 'incluido' THEN ROUND(sl.sum_lineas - sl.sum_lineas / (1 + c.impuesto_pct / 100), 2)
--         ELSE 0
--     END,
--     c.updated_at = NOW()
-- WHERE c.empresa_id = 2
--   AND c.slug LIKE 'imp-quo-%'
--   AND ABS(c.total - sl.sum_lineas) > 0.01;

-- 2C. FIX VENTAS IMPORTADAS: Sincronizar total con cotización corregida
-- NOTA: Ejecutar DESPUÉS del 2B

-- UPDATE ventas v
-- INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
-- SET v.total = c.total,
--     v.pagado = c.total,
--     v.saldo = 0,
--     v.updated_at = NOW()
-- WHERE v.empresa_id = 2
--   AND v.slug LIKE 'imp-vta-%'
--   AND v.estado != 'cancelada';

-- ════════════════════════════════════════════════════════════
--  VERIFICACIÓN FINAL
-- ════════════════════════════════════════════════════════════

-- SELECT v.id, v.numero, v.total, v.estado,
--        c.total AS cot_total,
--        COALESCE(sl.sum_lineas, 0) AS lineas_total,
--        cl.nombre AS cliente
-- FROM ventas v
-- LEFT JOIN cotizaciones c ON c.id = v.cotizacion_id
-- LEFT JOIN clientes cl ON cl.id = v.cliente_id
-- LEFT JOIN (
--     SELECT cotizacion_id, SUM(subtotal) AS sum_lineas
--     FROM cotizacion_lineas
--     GROUP BY cotizacion_id
-- ) sl ON sl.cotizacion_id = c.id
-- WHERE v.empresa_id = 2
--   AND v.created_at BETWEEN '2026-03-01' AND '2026-03-31 23:59:59'
-- ORDER BY v.created_at DESC;
