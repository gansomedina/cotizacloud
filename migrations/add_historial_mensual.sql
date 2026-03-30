-- Tabla de historial mensual para datos importados
-- Plan Business: permite importar datos históricos sin ensuciar cotizaciones/ventas
CREATE TABLE IF NOT EXISTS `historial_mensual` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `empresa_id` INT UNSIGNED NOT NULL,
    `anio` SMALLINT UNSIGNED NOT NULL,
    `mes` TINYINT UNSIGNED NOT NULL,
    `cotizaciones_cantidad` INT UNSIGNED NOT NULL DEFAULT 0,
    `cotizaciones_monto` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `ventas_cantidad` INT UNSIGNED NOT NULL DEFAULT 0,
    `ventas_monto` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `tasa_cierre` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_empresa_anio_mes` (`empresa_id`, `anio`, `mes`),
    CONSTRAINT `fk_historial_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
