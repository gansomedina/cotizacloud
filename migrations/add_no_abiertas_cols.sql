ALTER TABLE usuario_score
  ADD COLUMN no_abiertas_5d INT UNSIGNED NOT NULL DEFAULT 0 AFTER cot_dormidas,
  ADD COLUMN pen_no_abiertas DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER no_abiertas_5d;
