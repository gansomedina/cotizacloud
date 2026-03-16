-- ============================================================
--  Diagnóstico: ¿por qué reportes de marzo no cuadra con ventas?
--  Ejecutar en la BD para comparar datos
-- ============================================================

-- 1. Total ventas del listado /ventas (sin filtro de fecha, empresa 2)
SELECT 'TODAS las ventas (sin filtro fecha)' AS seccion,
       COUNT(*) AS num_ventas,
       SUM(total) AS sum_total,
       SUM(CASE WHEN estado='cancelada' THEN 1 ELSE 0 END) AS canceladas
FROM ventas WHERE empresa_id = 2;

-- 2. Ventas en marzo 2026 (lo que reportes debería mostrar)
SELECT 'Ventas MARZO 2026 (no canceladas)' AS seccion,
       COUNT(*) AS num_ventas,
       SUM(total) AS sum_total
FROM ventas
WHERE empresa_id = 2
  AND estado != 'cancelada'
  AND created_at BETWEEN '2026-03-01 00:00:00' AND '2026-03-31 23:59:59';

-- 3. Detalle de cada venta en marzo 2026
SELECT id, numero, titulo, total, pagado, saldo, estado,
       created_at,
       CASE WHEN slug LIKE 'imp-vta-%' THEN 'IMPORTADA' ELSE 'REAL' END AS origen
FROM ventas
WHERE empresa_id = 2
  AND created_at BETWEEN '2026-03-01 00:00:00' AND '2026-03-31 23:59:59'
ORDER BY created_at DESC;

-- 4. ¿Hay ventas duplicadas? (misma cotizacion_id)
SELECT cotizacion_id, COUNT(*) AS veces, GROUP_CONCAT(id) AS ventas_ids
FROM ventas
WHERE empresa_id = 2 AND cotizacion_id IS NOT NULL
GROUP BY cotizacion_id
HAVING COUNT(*) > 1;

-- 5. Comparar: cotizaciones aceptadas de marzo vs ventas de marzo
SELECT 'Cotizaciones aceptadas MARZO' AS seccion,
       COUNT(*) AS num, SUM(total) AS sum_total
FROM cotizaciones
WHERE empresa_id = 2
  AND estado IN ('aceptada','convertida')
  AND created_at BETWEEN '2026-03-01 00:00:00' AND '2026-03-31 23:59:59';
