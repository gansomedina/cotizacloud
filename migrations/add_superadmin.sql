-- Agregar rol superadmin al ENUM de usuarios
ALTER TABLE usuarios MODIFY COLUMN rol ENUM('admin','asesor','superadmin') NOT NULL DEFAULT 'asesor';

-- Crear empresa "sistema" para el superadmin (id fijo, no visible en listados normales)
INSERT INTO empresas (slug, nombre, moneda, impuesto_modo, activa)
VALUES ('_system', 'CotizaCloud Admin', 'MXN', 'ninguno', 0);

-- Crear usuario superadmin
-- Password: CotizaAdmin2026! (cambiar en producción)
INSERT INTO usuarios (empresa_id, nombre, usuario, email, password_hash, rol, activo,
    puede_editar_precios, puede_aplicar_descuentos, puede_ver_todas_cots,
    puede_ver_todas_ventas, puede_eliminar_items_venta, puede_cancelar_recibos, puede_capturar_pagos)
SELECT id, 'Super Admin', 'superadmin', 'admin@cotiza.cloud',
    '$2y$12$qsNf9cEYlmoAKEEegaY.xeC7JsZccq8EWWUM50kT12Z5ERvMSyvvm', 'superadmin', 1,
    1, 1, 1, 1, 1, 1, 1
FROM empresas WHERE slug = '_system';
