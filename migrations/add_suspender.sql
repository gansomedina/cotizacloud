-- ============================================================
--  Migración: Sistema de suspensión de cotizaciones
--  Fecha: 2026-03-24
-- ============================================================

-- Columna en cotizaciones para marcar suspendida
ALTER TABLE cotizaciones
  ADD COLUMN suspendida TINYINT(1) NOT NULL DEFAULT 0 AFTER estado,
  ADD COLUMN suspendida_at DATETIME NULL DEFAULT NULL AFTER suspendida;

-- Columnas en empresas para auto-suspensión
ALTER TABLE empresas
  ADD COLUMN auto_suspender_activo TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN auto_suspender_dias SMALLINT NOT NULL DEFAULT 30;

-- Índice para queries rápidos
ALTER TABLE cotizaciones ADD INDEX idx_suspendida (empresa_id, suspendida);
