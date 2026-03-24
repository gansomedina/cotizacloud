-- Migración: Encabezado/saludo en cotizaciones públicas
ALTER TABLE empresas ADD COLUMN cot_encabezado TEXT NULL AFTER cot_footer;
