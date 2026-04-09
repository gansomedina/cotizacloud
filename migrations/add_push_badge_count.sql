-- Agregar contador de badge por dispositivo para push notifications
-- El badge incrementa con cada push enviado y se resetea cuando la app se abre
ALTER TABLE dispositivos_push ADD COLUMN badge_count INT UNSIGNED NOT NULL DEFAULT 0;
