-- Mesa → Termómetro (25% del Seguimiento) + rollout a asesores
-- mesa_activa: 0 = off · 1 = UI asesores (sin score) · 2 = UI + score 25%
-- Correr ANTES de desplegar el código que lo lee.
ALTER TABLE empresas ADD COLUMN mesa_activa TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE usuario_score
  ADD COLUMN mesa_pedidas   INT UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN mesa_atendidas INT UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN s_mesa         DECIMAL(3,2) NULL;
