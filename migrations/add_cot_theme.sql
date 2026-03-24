-- Migración: Theme de color para cotización pública
ALTER TABLE empresas ADD COLUMN cot_theme VARCHAR(20) NOT NULL DEFAULT 'verde' AFTER cot_encabezado;
