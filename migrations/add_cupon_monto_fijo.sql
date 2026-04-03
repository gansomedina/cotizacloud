-- Agregar monto fijo a cupones (alternativa a porcentaje)
ALTER TABLE cupones ADD COLUMN monto_fijo DECIMAL(12,2) DEFAULT NULL AFTER porcentaje;
