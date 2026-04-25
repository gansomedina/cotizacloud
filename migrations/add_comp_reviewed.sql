CREATE TABLE IF NOT EXISTS radar_comp_reviewed (
    empresa_id INT UNSIGNED NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    valor VARCHAR(64) NOT NULL,
    reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (empresa_id, tipo, valor)
);
