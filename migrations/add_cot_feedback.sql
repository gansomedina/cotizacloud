-- Sistema de feedback del cliente sobre la atención del asesor
-- Plan: Free + Business (NO Pro). Default off por empresa hasta que la prendan.

ALTER TABLE empresas
    ADD COLUMN feedback_activo TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN feedback_pregunta VARCHAR(255) NOT NULL DEFAULT '¿Qué tan satisfecho estás con la atención recibida?',
    ADD COLUMN feedback_label_comentario VARCHAR(255) NOT NULL DEFAULT 'Cuéntanos brevemente qué podemos mejorar en tu atención',
    ADD COLUMN feedback_agradecimiento VARCHAR(255) NOT NULL DEFAULT 'Tu opinión nos ayuda a mejorar como te atendemos';

CREATE TABLE IF NOT EXISTS cot_feedbacks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id INT UNSIGNED NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    vendedor_id INT UNSIGNED NULL,
    stars TINYINT UNSIGNED NOT NULL,
    comentario TEXT NULL,
    visitor_id VARCHAR(64) NULL,
    device_sig VARCHAR(20) NULL,
    ip VARCHAR(45) NULL,
    ua VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cot_unica (cotizacion_id),
    KEY idx_empresa_fecha (empresa_id, created_at),
    KEY idx_vendedor (vendedor_id),
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT chk_stars CHECK (stars BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
