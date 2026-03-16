-- ============================================================
--  LIMPIAR TODA LA DATA DE EMPRESA 2
--  (cotizaciones, ventas, pagos, clientes, eventos, etc.)
--  NO elimina la empresa ni los usuarios del sistema.
-- ============================================================

-- PRIMERO: Diagnóstico — cuántos registros se van a borrar
SELECT 'quote_events' AS tabla, COUNT(*) AS registros FROM quote_events WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2)
UNION ALL SELECT 'quote_sessions', COUNT(*) FROM quote_sessions WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2)
UNION ALL SELECT 'cotizacion_log', COUNT(*) FROM cotizacion_log WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2)
UNION ALL SELECT 'cotizacion_archivos', COUNT(*) FROM cotizacion_archivos WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2)
UNION ALL SELECT 'cotizacion_lineas', COUNT(*) FROM cotizacion_lineas WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2)
UNION ALL SELECT 'venta_log', COUNT(*) FROM venta_log WHERE empresa_id = 2
UNION ALL SELECT 'recibos', COUNT(*) FROM recibos WHERE empresa_id = 2
UNION ALL SELECT 'gastos_venta', COUNT(*) FROM gastos_venta WHERE empresa_id = 2
UNION ALL SELECT 'ventas', COUNT(*) FROM ventas WHERE empresa_id = 2
UNION ALL SELECT 'cotizaciones', COUNT(*) FROM cotizaciones WHERE empresa_id = 2
UNION ALL SELECT 'articulos', COUNT(*) FROM articulos WHERE empresa_id = 2
UNION ALL SELECT 'clientes', COUNT(*) FROM clientes WHERE empresa_id = 2
UNION ALL SELECT 'folios', COUNT(*) FROM folios WHERE empresa_id = 2;

-- ============================================================
--  BORRAR TODO (descomentar cuando estés listo)
-- ============================================================

-- SET FOREIGN_KEY_CHECKS = 0;

-- -- 1. Eventos y sesiones de cotizaciones
-- DELETE FROM quote_events WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2);
-- DELETE FROM quote_sessions WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2);

-- -- 2. Logs
-- DELETE FROM cotizacion_log WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2);
-- DELETE FROM venta_log WHERE empresa_id = 2;

-- -- 3. Archivos y líneas de cotización
-- DELETE FROM cotizacion_archivos WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2);
-- DELETE FROM cotizacion_lineas WHERE cotizacion_id IN (SELECT id FROM cotizaciones WHERE empresa_id = 2);

-- -- 4. Pagos y gastos
-- DELETE FROM recibos WHERE empresa_id = 2;
-- DELETE FROM gastos_venta WHERE empresa_id = 2;

-- -- 5. Ventas y cotizaciones
-- DELETE FROM ventas WHERE empresa_id = 2;
-- DELETE FROM cotizaciones WHERE empresa_id = 2;

-- -- 6. Catálogos
-- DELETE FROM articulos WHERE empresa_id = 2;
-- DELETE FROM clientes WHERE empresa_id = 2;

-- -- 7. Folios (reiniciar numeración)
-- DELETE FROM folios WHERE empresa_id = 2;

-- SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  VERIFICAR que quedó limpio
-- ============================================================
-- SELECT 'cotizaciones' AS tabla, COUNT(*) AS registros FROM cotizaciones WHERE empresa_id = 2
-- UNION ALL SELECT 'ventas', COUNT(*) FROM ventas WHERE empresa_id = 2
-- UNION ALL SELECT 'clientes', COUNT(*) FROM clientes WHERE empresa_id = 2
-- UNION ALL SELECT 'articulos', COUNT(*) FROM articulos WHERE empresa_id = 2
-- UNION ALL SELECT 'recibos', COUNT(*) FROM recibos WHERE empresa_id = 2;
