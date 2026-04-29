-- Persistir el multiplicador de exploración ❓ del Radar en Seguimiento
ALTER TABLE usuario_score
    ADD COLUMN radar_why_score DECIMAL(3,2) NOT NULL DEFAULT 1.00 AFTER dias_lectura,
    ADD COLUMN calientes_exploradas TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER radar_why_score;
