-- Migración: APC v2.1 — Algoritmo de Productividad Comercial
-- Ejecutar: mysql -u root cotizacl_cotizacloud < migrations/upgrade_actividad_score_v2.sql

-- 1. Historial de transiciones de bucket (para medir movimiento frío→caliente)
CREATE TABLE IF NOT EXISTS bucket_transitions (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id   INT(10) UNSIGNED NOT NULL,
  empresa_id      INT(10) UNSIGNED NOT NULL,
  vendedor_id     INT(10) UNSIGNED DEFAULT NULL,
  bucket_anterior VARCHAR(40) DEFAULT NULL,
  bucket_nuevo    VARCHAR(40) DEFAULT NULL,
  radar_score_ant TINYINT UNSIGNED DEFAULT NULL,
  radar_score_new TINYINT UNSIGNED DEFAULT NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_vendedor_fecha (vendedor_id, created_at),
  INDEX idx_empresa_fecha (empresa_id, created_at),
  INDEX idx_cotizacion (cotizacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Ampliar usuario_score con nuevas métricas APC v2.1
ALTER TABLE usuario_score
  ADD COLUMN IF NOT EXISTS s_activacion    DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER percentil,
  ADD COLUMN IF NOT EXISTS s_seguimiento   DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER s_activacion,
  ADD COLUMN IF NOT EXISTS s_conversion    DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER s_seguimiento,
  ADD COLUMN IF NOT EXISTS penalizaciones  DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER s_conversion,
  ADD COLUMN IF NOT EXISTS bonuses         DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER penalizaciones,
  ADD COLUMN IF NOT EXISTS ema_activacion  DECIMAL(6,3) NOT NULL DEFAULT 0 AFTER ema_conversion,
  ADD COLUMN IF NOT EXISTS ema_seguimiento DECIMAL(6,3) NOT NULL DEFAULT 0 AFTER ema_activacion,
  ADD COLUMN IF NOT EXISTS cot_asignadas   SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER carga_activa,
  ADD COLUMN IF NOT EXISTS cot_vistas      SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER cot_asignadas,
  ADD COLUMN IF NOT EXISTS cot_dormidas    SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER cot_vistas,
  ADD COLUMN IF NOT EXISTS cierres_bucket  SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER cot_dormidas,
  ADD COLUMN IF NOT EXISTS cierres_sin_dto SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER cierres_bucket,
  ADD COLUMN IF NOT EXISTS transiciones_up SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER cierres_sin_dto,
  ADD COLUMN IF NOT EXISTS senales_ignoradas SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER transiciones_up;
