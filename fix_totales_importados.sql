-- ============================================================
--  FIX: Recalcular totales de cotizaciones y ventas importadas
--  desde las líneas reales (cotizacion_lineas.subtotal)
-- ============================================================

-- 1. DIAGNÓSTICO: Ver las diferencias antes de actualizar
SELECT c.id, c.numero, c.total AS total_actual,
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

-- ============================================================
-- 2. FIX COTIZACIONES: Actualizar subtotal y total desde líneas
-- ============================================================
-- NOTA: Ejecutar primero el SELECT de arriba para revisar.
--       Luego descomentar los UPDATEs de abajo.

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

-- ============================================================
-- 3. FIX VENTAS: Actualizar total, pagado, saldo desde cotización
-- ============================================================

-- UPDATE ventas v
-- INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
-- SET v.total = c.total,
--     v.pagado = c.total,
--     v.saldo = 0,
--     v.updated_at = NOW()
-- WHERE v.empresa_id = 2
--   AND v.slug LIKE 'imp-vta-%';

-- ============================================================
-- 4. VERIFICAR después del fix
-- ============================================================
-- SELECT v.id, v.numero, v.total, c.total AS cot_total,
--        COALESCE(sl.sum_lineas, 0) AS lineas_total
-- FROM ventas v
-- INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
-- LEFT JOIN (
--     SELECT cotizacion_id, SUM(subtotal) AS sum_lineas
--     FROM cotizacion_lineas
--     GROUP BY cotizacion_id
-- ) sl ON sl.cotizacion_id = c.id
-- WHERE v.empresa_id = 2
--   AND v.slug LIKE 'imp-vta-%'
-- ORDER BY v.created_at DESC;
