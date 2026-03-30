-- Agregar Facebook Conversions API Token a marketing_config
ALTER TABLE marketing_config
  ADD COLUMN capi_token VARCHAR(255) DEFAULT NULL COMMENT 'Meta Conversions API Access Token'
  AFTER pixel_meta;
