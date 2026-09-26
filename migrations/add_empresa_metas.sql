-- ============================================================
-- Metas de la empresa como dato fijo de CotizaCloud AI
-- Diseño: docs/metas_cotizacloud_ai.md
-- CORRER ANTES DE DESPLEGAR core/MetasEmpresa.php
-- (Si no se corre, la clase no truena: devuelve 'sin_metas' y deja
--  una sola línea '[Metas]' en el log.)
-- ============================================================

-- Lo que la empresa DECLARA por mes. Un mes sin fila hereda la del último
-- mes capturado antes que él (solo hacia atrás).
CREATE TABLE IF NOT EXISTS empresa_metas_mes (
  empresa_id     INT UNSIGNED NOT NULL,
  anio           SMALLINT UNSIGNED NOT NULL,
  mes            TINYINT UNSIGNED NOT NULL,
  equilibrio     DECIMAL(14,2) NOT NULL,
  meta_pesimista DECIMAL(14,2) NOT NULL,
  meta_optimista DECIMAL(14,2) NOT NULL,
  moneda         CHAR(3) NOT NULL DEFAULT 'MXN',
  capturado_por  INT UNSIGNED NULL,
  capturado_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (empresa_id, anio, mes),
  CONSTRAINT fk_metas_mes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasa de conversión deseada (fija, no por mes). NULL = no declarada ≠ 0.
ALTER TABLE empresas
  ADD COLUMN tasa_conv_meta DECIMAL(5,2) NULL,
  ADD COLUMN tasa_conv_meta_desde DATETIME NULL;

-- Memoria de la histéresis: el último nivel mostrado por ventana.
-- periodo: 'YYYY-MM' en la ventana 'mes' (otro mes = sin estado previo, así
-- el cierre de septiembre no se arrastra al 1 de octubre); 'rolling' en 'd30'.
CREATE TABLE IF NOT EXISTS empresa_metas_estado (
  empresa_id     INT UNSIGNED NOT NULL,
  ventana        ENUM('mes','d30') NOT NULL,
  periodo        CHAR(7) NOT NULL,
  nivel          VARCHAR(20) NOT NULL,
  nivel_anterior VARCHAR(20) NULL,
  cambiado_at    DATETIME NOT NULL,
  PRIMARY KEY (empresa_id, ventana),
  CONSTRAINT fk_metas_estado_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
