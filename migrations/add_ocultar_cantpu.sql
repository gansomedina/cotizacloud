-- Migración: ocultar cantidad y precio unitario
-- Nivel empresa: en vistas públicas (slugs)
ALTER TABLE `empresas`
  ADD COLUMN `ocultar_cant_pu` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'Ocultar cantidad y precio unitario en vistas públicas'
    AFTER `allow_precio_edit`;

-- Nivel usuario: en el editor interno
ALTER TABLE `usuarios`
  ADD COLUMN `puede_ver_cantidades` TINYINT(1) NOT NULL DEFAULT 1
    COMMENT 'Ver cantidad y precio unitario en el editor'
    AFTER `puede_editar_cotizaciones`;
