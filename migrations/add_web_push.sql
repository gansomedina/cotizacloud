-- Migración: ampliar token para Web Push subscriptions (JSON largo)
-- y asegurar que 'web' esté en el ENUM de plataforma

-- Ampliar token a TEXT para soportar JSON de Web Push subscriptions
ALTER TABLE `dispositivos_push`
  MODIFY COLUMN `token` TEXT NOT NULL;

-- Recrear el UNIQUE index con prefix para TEXT
ALTER TABLE `dispositivos_push`
  DROP INDEX `uk_token`,
  ADD UNIQUE KEY `uk_token` (`token`(512));
