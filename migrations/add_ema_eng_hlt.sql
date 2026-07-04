-- EMA viva para Engagement y Radar Health.
-- Permite leer TENDENCIA (sube/baja/estable) por dimensión en los tips del
-- termómetro (DiagnosticoTips), igual que act/seg/conv ya lo tienen.
-- Correr ANTES de desplegar el cambio en core/ActividadScore.php.

ALTER TABLE usuario_score
    ADD COLUMN ema_engagement   DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER ema_seguimiento,
    ADD COLUMN ema_radar_health DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER ema_engagement;
