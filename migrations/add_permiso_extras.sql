-- Migración: permiso para agregar extras en ventas
ALTER TABLE `usuarios`
  ADD COLUMN `puede_agregar_extras` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'Puede agregar artículos extra a ventas'
    AFTER `puede_ver_cantidades`;
