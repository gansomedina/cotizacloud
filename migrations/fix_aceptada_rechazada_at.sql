-- Fix: poblar aceptada_at y rechazada_at para cotizaciones existentes
-- que tienen accion_at pero no tienen estos timestamps específicos

UPDATE cotizaciones
SET aceptada_at = accion_at
WHERE estado IN ('aceptada', 'convertida')
  AND aceptada_at IS NULL
  AND accion_at IS NOT NULL;

UPDATE cotizaciones
SET rechazada_at = accion_at
WHERE estado = 'rechazada'
  AND rechazada_at IS NULL
  AND accion_at IS NOT NULL;
