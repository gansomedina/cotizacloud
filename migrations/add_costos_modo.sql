-- Migración: agregar costos_modo a empresas
-- Valores: 'venta' (costos por venta), 'empresa' (gastos generales), 'ambos' (Business)
-- Default: 'venta' (comportamiento actual)

ALTER TABLE `empresas`
  ADD COLUMN `costos_modo` ENUM('venta','empresa','ambos') NOT NULL DEFAULT 'venta'
  AFTER `plan_vence`;
