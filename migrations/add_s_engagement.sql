-- Migración: agregar s_engagement a usuario_score
ALTER TABLE `usuario_score`
  ADD COLUMN `s_engagement` DECIMAL(5,3) NOT NULL DEFAULT 0
    AFTER `s_activacion`;
