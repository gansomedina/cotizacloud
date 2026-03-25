-- Rate limiting por IP para login y registro
CREATE TABLE IF NOT EXISTS rate_limits (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip         VARCHAR(45)  NOT NULL,
    accion     VARCHAR(30)  NOT NULL,  -- 'login', 'registro'
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rate_ip_accion (ip, accion, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Limpiar registros viejos (>24h) periódicamente
-- Puedes correr esto con cron o manualmente:
-- DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
