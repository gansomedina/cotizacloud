-- Snapshot diario del score APC por asesor → base del promedio mensual real.
-- 1 fila por (usuario_id, fecha). Se escribe cuando un admin abre el dashboard
-- o el radar (ActividadScore::recalcular_empresa → snapshot_diario). Sin cron.
-- El promedio mensual = AVG(score) GROUP BY YEAR-MONTH sobre esta tabla.
CREATE TABLE IF NOT EXISTS score_diario (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT UNSIGNED NOT NULL,
    empresa_id    INT UNSIGNED NOT NULL,
    fecha         DATE NOT NULL,
    score         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    nivel         ENUM('bajo','regular','activo','top') NOT NULL DEFAULT 'bajo',
    s_activacion  DECIMAL(5,3) NOT NULL DEFAULT 0,
    s_seguimiento DECIMAL(5,3) NOT NULL DEFAULT 0,
    s_conversion  DECIMAL(5,3) NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_fecha (usuario_id, fecha),
    KEY idx_emp_fecha (empresa_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
