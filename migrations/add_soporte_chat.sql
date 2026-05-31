-- ============================================================
--  Chat de soporte casero — admin de empresa ↔ superadmin
--  Correr en producción ANTES de desplegar el código.
-- ============================================================

CREATE TABLE IF NOT EXISTS soporte_conversaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  estado ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  ultimo_mensaje_at DATETIME NULL,
  no_leidos_agente  INT UNSIGNED NOT NULL DEFAULT 0,
  no_leidos_usuario INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_emp_estado (empresa_id, estado),
  KEY idx_usuario (usuario_id),
  KEY idx_estado_ultimo (estado, ultimo_mensaje_at),
  CONSTRAINT fk_soporte_conv_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
  CONSTRAINT fk_soporte_conv_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS soporte_mensajes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversacion_id INT UNSIGNED NOT NULL,
  autor ENUM('usuario','agente') NOT NULL,
  cuerpo TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_conv (conversacion_id, id),
  CONSTRAINT fk_soporte_msg_conv FOREIGN KEY (conversacion_id) REFERENCES soporte_conversaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
