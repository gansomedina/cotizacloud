-- Agregar línea secundaria opcional debajo de la pregunta principal
ALTER TABLE empresas
    ADD COLUMN feedback_subtitulo VARCHAR(255) NOT NULL DEFAULT '' AFTER feedback_pregunta;
