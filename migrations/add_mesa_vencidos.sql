-- Ciclo de seguimiento Fase B (docs/mesa_seguimiento_diseno.md):
-- un row por cotización por DÍA en que estuvo con el seguimiento vencido.
-- Lo escribe Mesa::armar (INSERT IGNORE idempotente, backfill de la racha
-- actual capado a 15 días). Alimenta: la huella "estuvo vencida Nd" (Fase B)
-- y el castigo directo del score -2/-5/-8 (Fase C, suma por asesor en la
-- ventana rolling de 15 días — tocar detiene la acumulación, no la borra;
-- los días viejos salen solos de la ventana).
-- Correr ANTES de desplegar la Fase B.
CREATE TABLE IF NOT EXISTS mesa_vencidos (
    cotizacion_id INT UNSIGNED NOT NULL,
    usuario_id    INT UNSIGNED NOT NULL,   -- asesor dueño al momento
    empresa_id    INT UNSIGNED NOT NULL,
    fecha         DATE NOT NULL,
    PRIMARY KEY (cotizacion_id, fecha),
    KEY idx_emp_user_fecha (empresa_id, usuario_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
