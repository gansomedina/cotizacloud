-- ============================================================
--  FIX: Fechas reales de ventas de marzo 2026
--  Las ventas se importaron con la fecha de la cotización,
--  no con la fecha real de la venta/aceptación.
-- ============================================================

-- Diagnóstico: ver las ventas que vamos a corregir
SELECT v.numero, v.total, c.titulo,
       v.created_at AS fecha_actual,
       c.numero AS cotizacion
FROM ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
WHERE v.empresa_id = 2
  AND c.slug IN (
    'imp-quo-944-1266',   -- Mario Ibarra
    'imp-quo-899-1202',   -- Jesús Parra
    'imp-quo-934-1254',   -- Noemí Valle
    'imp-quo-925-1237',   -- Estreberto
    'imp-quo-924-1236',   -- Miguel Cruz
    'imp-quo-610-808'     -- Natalia Aranda
  )
ORDER BY v.created_at DESC;

-- FIX: Actualizar fechas de venta a las fechas reales
-- Mario Ibarra → 13 de marzo
UPDATE ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
SET v.created_at = '2026-03-13 12:00:00', v.updated_at = '2026-03-13 12:00:00'
WHERE v.empresa_id = 2 AND c.slug = 'imp-quo-944-1266';

-- Jesús Parra → 10 de marzo
UPDATE ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
SET v.created_at = '2026-03-10 12:00:00', v.updated_at = '2026-03-10 12:00:00'
WHERE v.empresa_id = 2 AND c.slug = 'imp-quo-899-1202';

-- Noemí Valle → 7 de marzo (ya está bien pero confirmamos)
UPDATE ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
SET v.created_at = '2026-03-07 12:00:00', v.updated_at = '2026-03-07 12:00:00'
WHERE v.empresa_id = 2 AND c.slug = 'imp-quo-934-1254';

-- Estreberto → 5 de marzo
UPDATE ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
SET v.created_at = '2026-03-05 12:00:00', v.updated_at = '2026-03-05 12:00:00'
WHERE v.empresa_id = 2 AND c.slug = 'imp-quo-925-1237';

-- Miguel Cruz → 4 de marzo
UPDATE ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
SET v.created_at = '2026-03-04 12:00:00', v.updated_at = '2026-03-04 12:00:00'
WHERE v.empresa_id = 2 AND c.slug = 'imp-quo-924-1236';

-- Natalia Aranda → 2 de marzo
UPDATE ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
SET v.created_at = '2026-03-02 12:00:00', v.updated_at = '2026-03-02 12:00:00'
WHERE v.empresa_id = 2 AND c.slug = 'imp-quo-610-808';

-- Verificar: ventas de marzo
SELECT v.numero, v.total, c.titulo, v.created_at
FROM ventas v
INNER JOIN cotizaciones c ON c.id = v.cotizacion_id
WHERE v.empresa_id = 2
  AND v.created_at BETWEEN '2026-03-01' AND '2026-03-31 23:59:59'
ORDER BY v.created_at DESC;
