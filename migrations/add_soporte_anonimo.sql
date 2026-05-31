-- ============================================================
--  Soporte chat — soporte para conversaciones ANÓNIMAS (landing)
--  Captura de leads: nombre + correo de prospectos sin login.
--  Correr en producción ANTES de desplegar el código del landing.
-- ============================================================

ALTER TABLE soporte_conversaciones
  MODIFY usuario_id INT UNSIGNED NULL,
  MODIFY empresa_id INT UNSIGNED NULL,
  ADD COLUMN origen ENUM('app','landing') NOT NULL DEFAULT 'app' AFTER estado,
  ADD COLUMN visitante_nombre VARCHAR(120) NULL AFTER origen,
  ADD COLUMN visitante_email  VARCHAR(160) NULL AFTER visitante_nombre,
  ADD COLUMN visitor_token    VARCHAR(64)  NULL AFTER visitante_email,
  ADD COLUMN ip               VARCHAR(45)  NULL AFTER visitor_token,
  ADD KEY idx_token (visitor_token);
