-- Bonus por tasa de cierre muy por encima del histórico del vendedor.
-- 3x el histórico → +4, 4x o más → +8 (un solo tier, no acumulable).
ALTER TABLE usuario_score
  ADD COLUMN bonus_cierre INT UNSIGNED NOT NULL DEFAULT 0 AFTER ticket_promedio;
