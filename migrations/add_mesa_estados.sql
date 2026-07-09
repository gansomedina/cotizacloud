-- Mesa de Trabajo: toques/desenlaces declarados (INSERT-ONLY, historia completa)
CREATE TABLE IF NOT EXISTS mesa_estados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    estado VARCHAR(30) NOT NULL,
    razon VARCHAR(30) NULL,
    bucket_snapshot VARCHAR(40) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_cot_time (cotizacion_id, created_at),
    KEY idx_user_time (usuario_id, created_at),
    KEY idx_emp_time (empresa_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
