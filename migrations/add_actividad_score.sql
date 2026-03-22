-- Migración: tablas para termómetro de actividad
-- Ejecutar: mysql -u root cotizacl_cotizacloud < migrations/add_actividad_score.sql

-- 1. Log de actividad (señales crudas)
CREATE TABLE IF NOT EXISTS actividad_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT(10) UNSIGNED NOT NULL,
  empresa_id  INT(10) UNSIGNED NOT NULL,
  tipo        VARCHAR(30) NOT NULL,  -- radar_view, quote_view, client_view, login
  ref_id      INT(10) UNSIGNED DEFAULT NULL,  -- id de cotización/cliente si aplica
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_usuario_tipo_fecha (usuario_id, tipo, created_at),
  INDEX idx_empresa_fecha (empresa_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Score calculado por usuario (EMA + snapshot)
CREATE TABLE IF NOT EXISTS usuario_score (
  usuario_id      INT(10) UNSIGNED NOT NULL PRIMARY KEY,
  empresa_id      INT(10) UNSIGNED NOT NULL,
  score           TINYINT UNSIGNED NOT NULL DEFAULT 0,        -- 0-100
  nivel           ENUM('bajo','regular','activo','top') NOT NULL DEFAULT 'bajo',
  dias_activos    TINYINT UNSIGNED NOT NULL DEFAULT 0,        -- últimos 30d
  acciones        SMALLINT UNSIGNED NOT NULL DEFAULT 0,       -- últimos 30d
  conversiones    SMALLINT UNSIGNED NOT NULL DEFAULT 0,       -- últimos 30d
  carga_activa    SMALLINT UNSIGNED NOT NULL DEFAULT 0,       -- cotizaciones activas
  tasa_gestion    DECIMAL(6,3) NOT NULL DEFAULT 0,            -- acciones/carga/semana
  ema_gestion     DECIMAL(6,3) NOT NULL DEFAULT 0,            -- EMA de tasa_gestion
  ema_presencia   DECIMAL(6,3) NOT NULL DEFAULT 0,            -- EMA de presencia
  ema_conversion  DECIMAL(6,3) NOT NULL DEFAULT 0,            -- EMA de conversion
  momentum        DECIMAL(4,2) NOT NULL DEFAULT 1.00,         -- ratio actual/ema
  percentil       DECIMAL(4,2) NOT NULL DEFAULT 0.50,         -- posición en equipo
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_empresa (empresa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
