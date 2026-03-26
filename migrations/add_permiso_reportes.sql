-- Migración: permiso para ver reportes
ALTER TABLE `usuarios`
  ADD COLUMN `puede_ver_reportes` TINYINT(1) NOT NULL DEFAULT 1
    COMMENT 'Acceso al módulo Reportes'
    AFTER `puede_agregar_extras`;
