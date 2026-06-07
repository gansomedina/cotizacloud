-- ============================================================
--  Migración: plan Lite ($199/mes)
--  Agrega 'lite' al ENUM de planes, entre 'free' y 'pro'.
--  Correr en producción ANTES de desplegar el código.
--
--  IMPORTANTE: el auto-migrate de core/Helpers.php (trial_info)
--  ya quedó actualizado al MISMO ENUM de 4 valores en el mismo
--  commit, así que el MODIFY en runtime NO revierte este cambio.
-- ============================================================

ALTER TABLE empresas
  MODIFY COLUMN plan ENUM('free','lite','pro','business') NOT NULL DEFAULT 'free';
