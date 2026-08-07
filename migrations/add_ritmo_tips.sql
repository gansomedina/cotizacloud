-- Memoria de rotación de tips de coaching por asesor (CotizaCloud AI).
-- Guarda qué técnica (handle) se le mostró a cada asesor y cuándo, para no
-- repetir hasta agotar las de su debilidad. Correr ANTES de desplegar.
CREATE TABLE IF NOT EXISTS ritmo_tips (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id  INT UNSIGNED NOT NULL,
    asesor_id   INT UNSIGNED NOT NULL,
    handle      VARCHAR(40) NOT NULL,     -- id de la técnica del catálogo
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_asesor (empresa_id, asesor_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
