-- Migración: agregar permisos de crear/editar cotizaciones
ALTER TABLE `usuarios`
  ADD COLUMN `puede_crear_cotizaciones` TINYINT(1) NOT NULL DEFAULT 1
    COMMENT 'Puede crear nuevas cotizaciones'
    AFTER `puede_ver_proveedores`,
  ADD COLUMN `puede_editar_cotizaciones` TINYINT(1) NOT NULL DEFAULT 1
    COMMENT 'Puede editar cotizaciones existentes'
    AFTER `puede_crear_cotizaciones`;
