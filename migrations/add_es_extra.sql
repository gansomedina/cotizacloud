-- Migración: marcar líneas como extra en cotizacion_lineas
-- DEFAULT 0 = todos los artículos existentes son regulares, cero impacto
ALTER TABLE `cotizacion_lineas`
  ADD COLUMN `es_extra` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '1=extra (se muestra en sección separada)'
    AFTER `subtotal`;
