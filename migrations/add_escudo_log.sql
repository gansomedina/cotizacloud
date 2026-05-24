-- ============================================================
--  Migración: escudo_log
--  Tabla de auditoría para decisiones del Escudo Radar (cotizacion.php).
--  Permite diagnosticar por qué una visita fue filtrada como interno
--  o pasó como cliente — útil cuando aparecen leaks inesperados.
--
--  Recomendado: cron diario que borra rows > 30 días para mantener tamaño.
-- ============================================================

CREATE TABLE IF NOT EXISTS `escudo_log` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cotizacion_id`     INT UNSIGNED NOT NULL,
    `empresa_id`        INT UNSIGNED NOT NULL,
    `decision`          VARCHAR(40) NOT NULL,
        -- valores típicos:
        -- 'capa_0_logueado'   — Auth::id() válido + empresa match
        -- 'capa_1_vid_interno' — cz_vid en radar_visitors_internos
        -- 'capa_3_bot'        — IP prefix en lista de bots
        -- 'cliente_real'      — pasó todas las capas, sesión contada
    `visitor_id`        VARCHAR(64)  NULL,
    `ip`                VARCHAR(45)  NULL,
    `user_agent`        VARCHAR(300) NULL,
    `device_sig`        VARCHAR(120) NULL,
    `cookies_presentes` VARCHAR(100) NULL,
        -- lista CSV de cookies relevantes recibidas, ej: "cz_vid,cza_session"
    `referer_host`      VARCHAR(255) NULL,
        -- solo el host del referer (sin path) por privacidad
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cot_decision`  (`cotizacion_id`, `decision`, `created_at`),
    KEY `idx_decision_time` (`decision`, `created_at`),
    KEY `idx_empresa_time`  (`empresa_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
