-- ============================================================
--  Migración: Permisos de módulo por usuario (Business)
--  Ejecutar: mysql cotizacl_cotizacloud < migrations/add_permisos_modulos.sql
-- ============================================================

ALTER TABLE `usuarios`
  ADD COLUMN `puede_ver_costos`      TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Acceso al módulo Costos',
  ADD COLUMN `puede_ver_proveedores` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Acceso al módulo Proveedores';

-- ============================================================
--  Migración: Gastos generales (no ligados a una venta)
--  Permitir venta_id NULL en gastos_venta
-- ============================================================

ALTER TABLE `gastos_venta` MODIFY COLUMN `venta_id` INT UNSIGNED DEFAULT NULL;
