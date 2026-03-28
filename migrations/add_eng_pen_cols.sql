-- Migración: agregar columnas de desglose de engagement a usuario_score
ALTER TABLE `usuario_score`
  ADD COLUMN `eng_pen_sin_pago` DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `s_engagement`,
  ADD COLUMN `eng_pen_descuento` DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `eng_pen_sin_pago`,
  ADD COLUMN `eng_pen_enfriamiento` DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `eng_pen_descuento`;
