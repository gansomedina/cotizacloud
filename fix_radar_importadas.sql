-- ============================================================
-- FIX: Marcar cotizaciones importadas sin venta como 'borrador'
-- para limpiar el radar de la empresa 2.
--
-- Problema: 712+ cotizaciones importadas con estado 'enviada'
-- aparecen en el radar sin actividad real (sin quote_events).
--
-- Criterio: cotizaciones de empresa_id=2 con slug 'imp-v2-%'
-- que NO tienen venta asociada y NO están ya en 'convertida'.
-- ============================================================

-- 1. Preview: ver cuántas se van a actualizar
SELECT estado, COUNT(*) AS total
FROM cotizaciones
WHERE empresa_id = 2
  AND slug LIKE 'imp-v2-%'
  AND estado IN ('enviada', 'vista', 'aceptada', 'rechazada')
  AND id NOT IN (SELECT cotizacion_id FROM ventas WHERE cotizacion_id IS NOT NULL AND empresa_id = 2)
GROUP BY estado;

-- 2. Marcar como borrador
UPDATE cotizaciones
SET estado = 'borrador',
    radar_score = NULL,
    radar_bucket = NULL,
    radar_senales = NULL,
    radar_updated_at = NULL,
    updated_at = updated_at  -- preserve original updated_at
WHERE empresa_id = 2
  AND slug LIKE 'imp-v2-%'
  AND estado IN ('enviada', 'vista', 'aceptada', 'rechazada')
  AND id NOT IN (SELECT cotizacion_id FROM ventas WHERE cotizacion_id IS NOT NULL AND empresa_id = 2);

-- 3. Verificación
SELECT estado, COUNT(*) AS total
FROM cotizaciones
WHERE empresa_id = 2
  AND slug LIKE 'imp-v2-%'
GROUP BY estado
ORDER BY total DESC;
