-- ============================================================
--  Migración: APC v3.1 → v3.2
--  - Agregar nivel 'nuevo' para período de gracia (7 días)
--  - Tabla score_historial para snapshots mensuales
-- ============================================================

-- Agregar 'nuevo' al ENUM de nivel en usuario_score
ALTER TABLE usuario_score
  MODIFY COLUMN nivel ENUM('nuevo','bajo','regular','activo','top') NOT NULL DEFAULT 'bajo';

-- Agregar 'nuevo' al ENUM de nivel en score_historial (si existe)
ALTER TABLE score_historial
  MODIFY COLUMN nivel ENUM('nuevo','bajo','regular','activo','top') NOT NULL DEFAULT 'bajo';
