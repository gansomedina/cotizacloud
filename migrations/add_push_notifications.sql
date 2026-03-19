-- ============================================================
--  Push Notifications — Tablas para dispositivos y notificaciones
-- ============================================================

-- Tokens de dispositivos (APNs / FCM)
CREATE TABLE IF NOT EXISTS `dispositivos_push` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `empresa_id`  INT UNSIGNED NOT NULL,
    `usuario_id`  INT UNSIGNED NOT NULL,
    `token`       VARCHAR(512) NOT NULL,
    `plataforma`  ENUM('ios','android','web') NOT NULL DEFAULT 'ios',
    `activo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_token` (`token`),
    KEY `idx_empresa_usuario` (`empresa_id`, `usuario_id`),
    KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Log de notificaciones enviadas
CREATE TABLE IF NOT EXISTS `notificaciones_push` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `empresa_id`  INT UNSIGNED NOT NULL,
    `usuario_id`  INT UNSIGNED NULL,
    `dispositivo_id` INT UNSIGNED NULL,
    `tipo`        VARCHAR(50) NOT NULL COMMENT 'cotizacion_aceptada, cotizacion_rechazada, etc.',
    `titulo`      VARCHAR(255) NOT NULL,
    `cuerpo`      TEXT NOT NULL,
    `datos`       JSON NULL COMMENT 'payload extra (cotizacion_id, venta_id, etc.)',
    `enviada`     TINYINT(1) NOT NULL DEFAULT 0,
    `error`       TEXT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_empresa` (`empresa_id`),
    KEY `idx_tipo` (`tipo`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
