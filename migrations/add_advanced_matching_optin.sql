-- Fase 2 del pixel de Meta: opt-in por empresa para Advanced Matching.
-- OFF por default → sin cambio para nadie hasta que la empresa lo prenda.
-- Correr ANTES de que un admin pueda activar el toggle.
ALTER TABLE marketing_config
    ADD COLUMN advanced_matching_optin TINYINT(1) NOT NULL DEFAULT 0 AFTER capi_token;
