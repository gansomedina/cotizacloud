-- Migración: agregar vendedor_id a cotizaciones y permiso asignar_cotizaciones
-- Ejecutar en servidor: mysql -u root cotizacl_cotizacloud < migrations/add_vendedor_id.sql

-- 1. Campo vendedor_id en cotizaciones (default = usuario_id, quien la creó)
ALTER TABLE cotizaciones
  ADD COLUMN vendedor_id INT(10) UNSIGNED DEFAULT NULL AFTER usuario_id,
  ADD INDEX idx_vendedor_id (vendedor_id);

-- 2. Llenar vendedor_id con usuario_id para cotizaciones existentes
UPDATE cotizaciones SET vendedor_id = usuario_id WHERE vendedor_id IS NULL;

-- 3. Permiso para asignar cotizaciones a otros vendedores
ALTER TABLE usuarios
  ADD COLUMN puede_asignar_cotizaciones TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_capturar_pagos;
