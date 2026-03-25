-- Migración: agregar notif_config JSON para toggles de notificaciones
-- Reemplaza los booleanos individuales notif_email_acepta / notif_email_rechaza

ALTER TABLE `empresas`
  ADD COLUMN `notif_config` JSON DEFAULT NULL
  AFTER `notif_email_rechaza`;
