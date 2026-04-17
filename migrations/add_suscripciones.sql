-- ============================================================
--  Suscripciones MercadoPago — Migración
--  Tablas: suscripciones, pagos_suscripcion
--  Columnas: empresas.grace_hasta
-- ============================================================

CREATE TABLE IF NOT EXISTS suscripciones (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    plan            ENUM('pro','business') NOT NULL,
    ciclo           ENUM('mensual','anual') NOT NULL,
    mp_preapproval_id VARCHAR(100) UNIQUE,
    estado          ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
    monto_mxn       DECIMAL(10,2) NOT NULL,
    cancel_al_vencer TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cancelled_at    DATETIME NULL,
    UNIQUE KEY uk_empresa (empresa_id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pagos_suscripcion (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    suscripcion_id  INT UNSIGNED NOT NULL,
    empresa_id      INT UNSIGNED NOT NULL,
    mp_payment_id   VARCHAR(100) UNIQUE NOT NULL,
    monto_mxn       DECIMAL(10,2) NOT NULL,
    estado          ENUM('approved','pending','rejected','refunded') NOT NULL,
    fecha_pago      DATETIME NOT NULL,
    detalle         JSON NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Columna grace_hasta en empresas (7 días de gracia tras fallo de pago)
ALTER TABLE empresas ADD COLUMN grace_hasta DATE NULL AFTER plan_vence;
