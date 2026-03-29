-- Migración: agregar eng_pen_bajo_benchmark a usuario_score
ALTER TABLE `usuario_score`
  ADD COLUMN `eng_pen_bajo_benchmark` DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `eng_pen_enfriamiento`;
