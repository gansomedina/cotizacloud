-- ============================================================
--  Migración: score_historial
--  Snapshot mensual de scores para reportes comparativos
--  Guarda una foto por vendedor por mes (cierre de mes)
-- ============================================================

CREATE TABLE IF NOT EXISTS score_historial (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT UNSIGNED NOT NULL,
    empresa_id    INT UNSIGNED NOT NULL,
    periodo       CHAR(7)      NOT NULL COMMENT 'YYYY-MM',

    -- Score y nivel al cierre del mes
    score         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    nivel         ENUM('bajo','regular','activo','top') NOT NULL DEFAULT 'bajo',

    -- Dimensiones (0.000 - 1.000)
    s_activacion  DECIMAL(5,3) NOT NULL DEFAULT 0,
    s_seguimiento DECIMAL(5,3) NOT NULL DEFAULT 0,
    s_conversion  DECIMAL(5,3) NOT NULL DEFAULT 0,

    -- Métricas clave del mes
    cot_asignadas     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    cot_vistas        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    cot_dormidas      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    conversiones      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    cierres_bucket    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    cierres_sin_dto   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    transiciones_up   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    senales_ignoradas SMALLINT UNSIGNED NOT NULL DEFAULT 0,

    -- Penalizaciones y bonuses totales del mes
    penalizaciones DECIMAL(5,3) NOT NULL DEFAULT 0,
    bonuses        DECIMAL(5,3) NOT NULL DEFAULT 0,

    -- Momentum y percentil al cierre
    momentum  DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    percentil DECIMAL(4,2) NOT NULL DEFAULT 0.50,

    -- Posición en ranking del equipo ese mes
    ranking   TINYINT UNSIGNED DEFAULT NULL,
    team_size TINYINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Un solo registro por vendedor por mes
    UNIQUE KEY uq_usuario_periodo (usuario_id, periodo),
    INDEX idx_empresa_periodo (empresa_id, periodo),
    INDEX idx_periodo (periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
