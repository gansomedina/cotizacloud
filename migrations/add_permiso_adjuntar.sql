-- Permiso para agregar adjuntos a cotizaciones
ALTER TABLE usuarios ADD COLUMN puede_adjuntar TINYINT(1) NOT NULL DEFAULT 1;
