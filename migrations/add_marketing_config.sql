-- Migración: tabla marketing_config para pixels de tracking
CREATE TABLE IF NOT EXISTS `marketing_config` (
    `empresa_id` INT UNSIGNED NOT NULL,
    `pixel_meta` VARCHAR(20) DEFAULT NULL COMMENT 'Meta/Facebook Pixel ID',
    `pixel_ga4` VARCHAR(20) DEFAULT NULL COMMENT 'Google Analytics 4 Measurement ID',
    `pixel_gads_id` VARCHAR(20) DEFAULT NULL COMMENT 'Google Ads Conversion ID',
    `pixel_gads_label` VARCHAR(30) DEFAULT NULL COMMENT 'Google Ads Conversion Label',
    `pixel_tiktok` VARCHAR(30) DEFAULT NULL COMMENT 'TikTok Pixel ID',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`empresa_id`),
    CONSTRAINT `fk_marketing_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
