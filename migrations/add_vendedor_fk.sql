-- Agregar foreign keys para vendedor_id en cotizaciones y ventas
-- ON DELETE SET NULL: si se borra un usuario, el vendedor queda NULL (no rompe datos)

ALTER TABLE cotizaciones
  ADD CONSTRAINT fk_cot_vendedor
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE ventas
  ADD CONSTRAINT fk_venta_vendedor
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE SET NULL;
