-- ============================================================
--  MercadoPago sync — Migración
--  Columna: empresas.ultima_sync_mp (timestamp de última sincronización con MP)
-- ============================================================

ALTER TABLE empresas ADD COLUMN ultima_sync_mp DATETIME NULL AFTER grace_hasta;
