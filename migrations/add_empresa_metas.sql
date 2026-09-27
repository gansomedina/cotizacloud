-- ============================================================
-- Metas de la empresa como dato fijo de CotizaCloud AI
-- Diseño: docs/metas_cotizacloud_ai.md
-- Re-ejecutable (IF NOT EXISTS / IF EXISTS).
-- (Si no se corre, la clase no truena: devuelve 'sin_metas' y deja
--  una sola línea '[Metas]' en el log.)
-- ============================================================

-- UNA meta general de la empresa, igual para todos los meses (CEO, 27 sep
-- 2026). NULL = no capturada. meta_moneda: la moneda con que se capturó; si
-- la empresa cambia de moneda, la meta deja de compararse hasta recapturarla.
-- Tasa de conversión deseada: fija. NULL = no declarada ≠ 0.
ALTER TABLE empresas
  ADD COLUMN IF NOT EXISTS meta_equilibrio      DECIMAL(14,2) NULL,
  ADD COLUMN IF NOT EXISTS meta_pesimista       DECIMAL(14,2) NULL,
  ADD COLUMN IF NOT EXISTS meta_optimista       DECIMAL(14,2) NULL,
  ADD COLUMN IF NOT EXISTS meta_moneda          CHAR(3) NULL,
  ADD COLUMN IF NOT EXISTS meta_capturada_at    DATETIME NULL,
  ADD COLUMN IF NOT EXISTS meta_capturada_por   INT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS tasa_conv_meta       DECIMAL(5,2) NULL,
  ADD COLUMN IF NOT EXISTS tasa_conv_meta_desde DATETIME NULL;

-- La primera versión (27 sep) guardaba metas mes por mes. Se descartó: la
-- meta es general. La tabla se crea vacía y nunca tuvo uso real.
DROP TABLE IF EXISTS empresa_metas_mes;

-- Memoria de la histéresis: el último nivel mostrado por ventana.
-- periodo: 'YYYY-MM' en la ventana 'mes' (otro mes = sin estado previo, así
-- el cierre de septiembre no se arrastra al 1 de octubre); 'rolling' en 'd30'.
-- firma: huella de la meta con que se calculó; si el admin la edita, la
-- siguiente lectura es "primera" y no dispara una alerta falsa.
CREATE TABLE IF NOT EXISTS empresa_metas_estado (
  empresa_id     INT UNSIGNED NOT NULL,
  ventana        ENUM('mes','d30') NOT NULL,
  periodo        CHAR(7) NOT NULL,
  firma          CHAR(32) NOT NULL DEFAULT '',
  nivel          VARCHAR(20) NOT NULL,
  nivel_anterior VARCHAR(20) NULL,
  cambiado_at    DATETIME NOT NULL,
  PRIMARY KEY (empresa_id, ventana),
  CONSTRAINT fk_metas_estado_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
