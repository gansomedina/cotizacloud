-- Reporte del Director de Ventas por asesor (snapshot + rate-limit semanal).
-- Correr ANTES de desplegar. Ya ejecutada en producción (Contabo) el 6-ago-2026.
CREATE TABLE IF NOT EXISTS ritmo_reportes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id    INT UNSIGNED NOT NULL,
    asesor_id     INT UNSIGNED NOT NULL,      -- el vendedor evaluado
    generado_por  INT UNSIGNED NOT NULL,      -- admin/superadmin que lo generó
    rango_desde   DATE NOT NULL,
    rango_hasta   DATE NOT NULL,
    contenido     MEDIUMTEXT NOT NULL,        -- snapshot HTML del reporte
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_asesor (empresa_id, asesor_id, created_at),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
