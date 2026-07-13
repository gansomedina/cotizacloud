-- Corrige el candado de cliente de Descuentos Inteligentes.
-- Correr SOLO en servidores donde add_descuentos_inteligentes.sql YA se corrió
-- con la versión vieja (UNIQUE uk_cliente(cliente_id) permanente).
--
-- Antes: UNIQUE(cliente_id) bloqueaba al cliente DE POR VIDA, incluso si el DI
--   venció sin usarse o el admin lo quitó (estado cancelado).
-- Ahora: cliente_lock = cliente_id solo mientras el DI está activo/utilizado;
--   vencido/cancelado → NULL (múltiples NULL permitidos) → el cupo se libera.
--
-- MySQL/MariaDB hace DDL auto-commit: si el 2º ALTER (FK) fallara por filas
-- huérfanas preexistentes, el 1º ya quedó aplicado (el candado correcto). En
-- ese caso, limpiar huérfanos y reintentar solo el ADD FOREIGN KEY.

ALTER TABLE desc_int_activaciones
    DROP INDEX uk_cliente,
    ADD COLUMN cliente_lock INT UNSIGNED GENERATED ALWAYS AS
        (CASE WHEN estado IN ('activo','utilizado') THEN cliente_id ELSE NULL END) STORED
        AFTER cliente_id,
    ADD UNIQUE KEY uk_cliente_vivo (cliente_lock);

-- FK a cotizaciones para que borrar una cotización limpie su activación
-- (evita filas huérfanas que perpetuaban el candado).
ALTER TABLE desc_int_activaciones
    ADD FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE;
