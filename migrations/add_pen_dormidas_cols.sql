ALTER TABLE usuario_score
  ADD COLUMN pen_dormidas DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER pen_no_abiertas,
  ADD COLUMN dias_activos_feature INT UNSIGNED NOT NULL DEFAULT 0 AFTER pen_dormidas;
