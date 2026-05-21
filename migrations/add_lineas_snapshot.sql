-- Snapshot inmutable de las líneas + descuento de la cotización al cerrarse.
-- El slug de la cotización aceptada/convertida renderiza este snapshot, así
-- editar la venta (que comparte cotizacion_lineas) no altera el original.
-- IMPORTANTE: correr ANTES de desplegar el código.
ALTER TABLE cotizaciones ADD COLUMN lineas_snapshot LONGTEXT NULL;
