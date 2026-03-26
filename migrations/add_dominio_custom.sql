-- Agregar columna dominio_custom a empresas
-- Permite que una empresa use su propio dominio para URLs públicas
-- Ejemplo: hmo.ontimecocinas.com en vez de hmo.cotiza.cloud
ALTER TABLE empresas ADD COLUMN dominio_custom VARCHAR(120) DEFAULT NULL AFTER website;

-- Índice único para búsqueda rápida por dominio entrante
ALTER TABLE empresas ADD UNIQUE INDEX idx_dominio_custom (dominio_custom);
