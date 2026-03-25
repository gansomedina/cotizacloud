-- ============================================================
--  Migración: Verificación de email y recuperación de contraseña
--  Ejecutar en servidor: mysql -u root cotizacl_cotizacloud < add_email_verification.sql
-- ============================================================

-- Tokens de verificación de email al registrarse
CREATE TABLE IF NOT EXISTS email_verificacion (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(255) NOT NULL,
    empresa_id   INT DEFAULT NULL,
    codigo       VARCHAR(10)  NOT NULL,
    intentos     TINYINT      NOT NULL DEFAULT 0,
    verificado   TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at   DATETIME     NOT NULL,
    INDEX idx_email (email),
    INDEX idx_codigo (codigo),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tokens de recuperación de contraseña
CREATE TABLE IF NOT EXISTS password_resets (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT          NOT NULL,
    empresa_id   INT          NOT NULL,
    token        VARCHAR(128) NOT NULL,
    usado        TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at   DATETIME     NOT NULL,
    INDEX idx_token (token),
    INDEX idx_usuario (usuario_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar columna email_verificado a usuarios (default 1 para usuarios existentes)
ALTER TABLE usuarios ADD COLUMN email_verificado TINYINT(1) NOT NULL DEFAULT 1;
