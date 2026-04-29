-- Persistencia del split de Activación: operativa + lectura tips
ALTER TABLE usuario_score
    ADD COLUMN s_activacion_op DECIMAL(5,3) NOT NULL DEFAULT 0.000 AFTER s_activacion,
    ADD COLUMN tips_score DECIMAL(3,2) NOT NULL DEFAULT 1.00 AFTER s_activacion_op,
    ADD COLUMN dias_lectura TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER tips_score;
