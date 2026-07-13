-- Descuentos Inteligentes — feature independiente (NO toca descuento_auto,
-- cupones ni comisión). 2 tablas propias. Correr ANTES de desplegar el motor.
--
-- NOTA: si esta base YA se corrió en un servidor (sin cliente_lock/FK), NO
-- re-correr este archivo — usar migrations/add_di_cliente_lock.sql para
-- actualizar la tabla existente.
--
-- desc_int_config: 1 fila por empresa. Settings del admin (toggles + %) +
-- anclas estadísticas cacheadas (p75/p90/zonas, TTL 24h) para no escanear
-- ventas en cada apertura del slug.
CREATE TABLE IF NOT EXISTS desc_int_config (
    empresa_id    INT UNSIGNED NOT NULL PRIMARY KEY,
    r1_activa     TINYINT(1)   NOT NULL DEFAULT 0,
    r1_pct        DECIMAL(5,2) NOT NULL DEFAULT 0,
    r2_activa     TINYINT(1)   NOT NULL DEFAULT 0,
    r2_pct        DECIMAL(5,2) NOT NULL DEFAULT 0,
    -- anclas cacheadas (calculadas de ventas)
    n_ventas      INT UNSIGNED NOT NULL DEFAULT 0,
    p75           INT UNSIGNED NULL,
    p90           INT UNSIGNED NULL,
    dia_fin_vida  INT UNSIGNED NULL,
    dia_dead      INT UNSIGNED NULL,
    dia_techo     INT UNSIGNED NULL,
    anclas_at     DATETIME     NULL,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- desc_int_activaciones: log de descuentos que dispararon.
--   uk_cotizacion   → "una por cotización" (a prueba de doble-clic/carrera).
--   uk_cliente_vivo → "una VIVA por cliente": cliente_lock = cliente_id solo
--     mientras el DI está activo/utilizado; en vencido/cancelado pasa a NULL
--     (MySQL permite múltiples NULL) → un DI que expiró sin usarse o que el
--     admin quitó LIBERA el cupo, el cliente puede recibir otro después.
-- cliente_id NUNCA NULL (genéricos/NULL no disparan). FK a cotizaciones con
-- CASCADE para no dejar activaciones huérfanas al borrar una cotización.
CREATE TABLE IF NOT EXISTS desc_int_activaciones (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    cotizacion_id   INT UNSIGNED NOT NULL,
    cliente_id      INT UNSIGNED NOT NULL,
    cliente_lock    INT UNSIGNED GENERATED ALWAYS AS
                      (CASE WHEN estado IN ('activo','utilizado') THEN cliente_id ELSE NULL END) STORED,
    regla           TINYINT      NOT NULL,             -- 1 = recuperación · 2 = muerto
    pct             DECIMAL(5,2) NOT NULL,
    precio_original DECIMAL(14,2) NOT NULL,
    monto_desc      DECIMAL(14,2) NOT NULL,
    nuevo_total     DECIMAL(14,2) NOT NULL,
    edad_dias       INT UNSIGNED NOT NULL,
    dia_fin_vida    INT UNSIGNED NOT NULL,
    dia_dead        INT UNSIGNED NOT NULL,
    bucket_snapshot VARCHAR(40)  NULL,                 -- solo para análisis
    estado          ENUM('activo','vencido','utilizado','cancelado') NOT NULL DEFAULT 'activo',
    fecha_apertura  DATETIME     NOT NULL,             -- primera apertura elegible
    expira_at       DATETIME     NOT NULL,             -- fecha_apertura + 24h
    visitor_id      VARCHAR(120) NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cotizacion   (cotizacion_id),
    UNIQUE KEY uk_cliente_vivo (cliente_lock),
    KEY idx_estado_expira (estado, expira_at),
    FOREIGN KEY (empresa_id)    REFERENCES empresas(id)     ON DELETE CASCADE,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
