-- Módulo Inmuebles — tabla extensión de articulos + giro en empresas

ALTER TABLE empresas ADD COLUMN giro ENUM('servicios','inmuebles') NOT NULL DEFAULT 'servicios';

CREATE TABLE propiedades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    articulo_id INT UNSIGNED NOT NULL,
    tipo_operacion ENUM('venta','renta','renta_temporal') NOT NULL DEFAULT 'venta',
    tipo_propiedad ENUM('casa','departamento','terreno','local_comercial','oficina','bodega') NOT NULL DEFAULT 'casa',
    m2_terreno DECIMAL(8,2),
    m2_construccion DECIMAL(8,2),
    recamaras TINYINT UNSIGNED,
    banos DECIMAL(3,1),
    fotos JSON,
    FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_articulo (articulo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
