-- Migración: tabla de feedback del Radar (Termómetro v5)
-- Registra interacción del vendedor con señales calientes
CREATE TABLE IF NOT EXISTS `radar_feedback` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cotizacion_id` INT UNSIGNED NOT NULL,
    `usuario_id` INT UNSIGNED NOT NULL,
    `empresa_id` INT UNSIGNED NOT NULL,
    `tipo` ENUM('con_interes','sin_interes') NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_cot_user` (`cotizacion_id`, `usuario_id`),
    KEY `idx_empresa` (`empresa_id`),
    FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
