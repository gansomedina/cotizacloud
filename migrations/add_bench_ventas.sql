-- Migración: agregar bench_ventas y ventas_periodo a usuario_score (debug)
ALTER TABLE `usuario_score`
  ADD COLUMN `ventas_periodo` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `ventas_sin_pago`,
  ADD COLUMN `bench_ventas` DECIMAL(5,1) NOT NULL DEFAULT 0 AFTER `ventas_periodo`;
