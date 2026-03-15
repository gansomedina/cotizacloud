-- ============================================================
--  CotizaCloud — Importar ventas para cotizaciones aceptadas
--  Genera ventas como 'pagada' (total=pagado, saldo=0)
--  Marca las cotizaciones como 'convertida'
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Crear ventas para todas las cotizaciones aceptadas importadas
--    que aún no tengan venta
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
    c.total,
    c.total,          -- pagado = total (pagada completa)
    0.00,             -- saldo = 0
    'pagada',
    c.created_at,     -- misma fecha que la cotización
    c.created_at
FROM cotizaciones c
WHERE c.empresa_id = 2
  AND c.estado = 'aceptada'
  AND c.slug LIKE 'imp-quo-%'
  AND NOT EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = c.id);

-- 2. Ligar las líneas de cotización a la venta correspondiente
UPDATE cotizacion_lineas cl
INNER JOIN ventas v ON v.cotizacion_id = cl.cotizacion_id
SET cl.venta_id = v.id
WHERE v.empresa_id = 2
  AND v.slug LIKE 'imp-vta-%'
  AND cl.venta_id IS NULL;

-- 3. Marcar las cotizaciones como 'convertida'
UPDATE cotizaciones c
INNER JOIN ventas v ON v.cotizacion_id = c.id
SET c.estado = 'convertida', c.updated_at = NOW()
WHERE c.empresa_id = 2
  AND c.estado = 'aceptada'
  AND c.slug LIKE 'imp-quo-%'
  AND v.slug LIKE 'imp-vta-%';

-- 4. Actualizar el folio de ventas para que no choque con futuros folios
--    (cuenta cuántas ventas importadas hay y ajusta si es necesario)
INSERT INTO folios (empresa_id, tipo, anio, ultimo)
SELECT 2, 'VTA', YEAR(NOW()),
       (SELECT COUNT(*) FROM ventas WHERE empresa_id = 2)
ON DUPLICATE KEY UPDATE ultimo = GREATEST(ultimo,
       (SELECT COUNT(*) FROM ventas WHERE empresa_id = 2));

SET FOREIGN_KEY_CHECKS = 1;

-- Summary:
-- Creates ventas for all imported 'aceptada' cotizaciones
-- Sets them as 'pagada' with saldo=0
-- Links cotizacion_lineas to the new ventas
-- Marks source cotizaciones as 'convertida'
-- Updates folio counter to prevent number collisions
