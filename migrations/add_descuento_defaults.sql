-- Defaults de descuento automático por empresa
ALTER TABLE empresas
  ADD COLUMN descuento_auto_pct_default DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN descuento_auto_dias_default SMALLINT NOT NULL DEFAULT 3;
