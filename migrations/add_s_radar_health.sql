-- Migración: agregar s_radar_health a usuario_score
ALTER TABLE `usuario_score`
  ADD COLUMN `s_radar_health` DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `s_seguimiento`;
