-- ============================================================
--  Migración: Módulo Proveedores (Business)
--  Ejecutar en servidor: mysql cotizacl_cotizacloud < migrations/add_proveedores.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `proveedores` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id`  INT UNSIGNED NOT NULL,
  `nombre`      VARCHAR(150) NOT NULL,
  `contacto`    VARCHAR(150) DEFAULT NULL COMMENT 'Nombre de la persona de contacto',
  `telefono`    VARCHAR(30)  DEFAULT NULL,
  `email`       VARCHAR(150) DEFAULT NULL,
  `direccion`   VARCHAR(255) DEFAULT NULL,
  `nota`        TEXT         DEFAULT NULL,
  `activo`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_prov_empresa` (`empresa_id`, `activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Columna opcional en gastos_venta para ligar gasto a proveedor
ALTER TABLE `gastos_venta`
  ADD COLUMN `proveedor_id` INT UNSIGNED DEFAULT NULL AFTER `categoria_id`,
  ADD KEY `idx_gv_proveedor` (`proveedor_id`);
