-- Consolidar columnas duplicadas: rechazada_motivo → motivo_rechazo
-- El API escribe en motivo_rechazo, así que es la columna canónica

-- Copiar datos huérfanos de rechazada_motivo a motivo_rechazo (si hay)
UPDATE cotizaciones
SET motivo_rechazo = rechazada_motivo
WHERE motivo_rechazo IS NULL
  AND rechazada_motivo IS NOT NULL;

-- Eliminar columna duplicada
ALTER TABLE cotizaciones DROP COLUMN rechazada_motivo;
