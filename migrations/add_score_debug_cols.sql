-- Migración: agregar radar_views y radar_benchmark a usuario_score
ALTER TABLE `usuario_score`
  ADD COLUMN `radar_views` INT NOT NULL DEFAULT 0 AFTER `senales_ignoradas`,
  ADD COLUMN `radar_benchmark` DECIMAL(6,1) NOT NULL DEFAULT 0 AFTER `radar_views`,
  ADD COLUMN `tasa_cierre` DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `radar_benchmark`,
  ADD COLUMN `ventas_sin_pago` INT NOT NULL DEFAULT 0 AFTER `tasa_cierre`;
