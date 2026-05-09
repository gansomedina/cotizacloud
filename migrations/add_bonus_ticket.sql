ALTER TABLE usuario_score
  ADD COLUMN bonus_ticket INT UNSIGNED NOT NULL DEFAULT 0 AFTER percentil,
  ADD COLUMN bonus_ticket_ventas INT UNSIGNED NOT NULL DEFAULT 0 AFTER bonus_ticket,
  ADD COLUMN ticket_promedio DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER bonus_ticket_ventas;
