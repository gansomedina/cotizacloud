-- ============================================================
--  Migración: evidencia de consentimiento legal (clickwrap)
--  Correr en producción ANTES de desplegar.
--
--  Soporta el registro defendible de aceptación de Términos y
--  Aviso de Privacidad conforme al Código de Comercio (arts.
--  89 bis, 90, 93, 93 bis): atribución + versión + hash + fecha.
-- ============================================================

-- Catálogo de versiones de documentos legales.
-- INMUTABLE: el contenido nunca se actualiza. Cada cambio de texto
-- crea una versión nueva (nueva fila), conservando el hash original.
CREATE TABLE IF NOT EXISTS `documento_versiones` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tipo`          ENUM('terminos','privacidad') NOT NULL,
    `version`       VARCHAR(20) NOT NULL,          -- etiqueta, ej. "2026-06-01"
    `contenido`     LONGTEXT NOT NULL,              -- copia EXACTA mostrada
    `hash_sha256`   CHAR(64) NOT NULL,              -- SHA-256 del contenido
    `vigente_desde` DATETIME NOT NULL,
    `creado_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tipo_version` (`tipo`, `version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro de aceptaciones (audit trail).
-- Un row por documento aceptado. Nunca se sobrescribe ni se borra.
CREATE TABLE IF NOT EXISTS `consentimientos` (
    `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`           INT UNSIGNED NULL,        -- usuario que aceptó (si existe)
    `empresa_id`           INT UNSIGNED NULL,        -- empresa asociada
    `email`                VARCHAR(255) NULL,        -- atribución (art. 90 CdC)
    `documento_version_id` INT UNSIGNED NOT NULL,    -- FK a la versión exacta
    `hash_sha256`          CHAR(64) NOT NULL,        -- hash al momento de aceptar
    `aceptado_at`          DATETIME(3) NOT NULL,     -- timestamp con milisegundos
    `ip`                   VARCHAR(45) NOT NULL,     -- IPv4/IPv6
    `user_agent`           VARCHAR(500) NOT NULL,
    `metodo`               ENUM('checkbox','firma','uso_continuado') NOT NULL DEFAULT 'checkbox',
    `accion`               VARCHAR(20) NOT NULL DEFAULT 'accept',
    `nom151_constancia`    VARCHAR(255) NULL,        -- hook: folio/ruta constancia PSC (futuro)
    `creado_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_usuario` (`usuario_id`),
    KEY `idx_empresa` (`empresa_id`),
    KEY `idx_email` (`email`),
    KEY `idx_version` (`documento_version_id`),
    CONSTRAINT `fk_consent_version`
        FOREIGN KEY (`documento_version_id`) REFERENCES `documento_versiones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
