-- Snapshot diario de la mesa por asesor. Se guarda 1 fila por (usuario, día)
-- cuando el score recalcula (al abrir el dashboard): cuántas cotizaciones de su
-- mesa visible estaban PEDIDAS y cuántas ATENDIDAS (feedback + postura) ESE día.
-- El score NO recalcula la cobertura sobre 15 días: promedia estos puntos diarios
-- ("reloj checador": de tus días, ¿en cuántos tuviste la mesa al día?).
CREATE TABLE IF NOT EXISTS mesa_diario (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED NOT NULL,
    empresa_id  INT UNSIGNED NOT NULL,
    fecha       DATE NOT NULL,
    pedidas     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    atendidas   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_fecha (usuario_id, fecha),
    KEY idx_emp_fecha (empresa_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
