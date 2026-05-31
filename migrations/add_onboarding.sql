-- ============================================================
--  Onboarding — wizard de bienvenida para empresas nuevas
--  Correr en producción ANTES de desplegar.
-- ============================================================

ALTER TABLE empresas ADD COLUMN onboarding_completo TINYINT(1) NOT NULL DEFAULT 0;

-- Las empresas que YA existen no deben ver el wizard
UPDATE empresas SET onboarding_completo = 1;
