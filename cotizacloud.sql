-- ============================================================
--  cotiza.cloud — Schema completo v1.0
--  Instalación desde cero — ejecutar UNA sola vez
--  MySQL 8.0+ / MariaDB 10.5+
--
--  Orden correcto (respeta FKs):
--  1. empresas
--  2. usuarios, articulos, clientes, cupones
--  3. cotizaciones → cotizacion_lineas, cotizacion_archivos, cotizacion_log
--  4. ventas → recibos
--  5. costos → categorias_costos, gastos_venta
--  6. tracking → quote_sessions, quote_events
--  7. radar → radar_ips_internas, radar_visitors_internos, radar_fit_calibracion
--  8. auth → user_sessions
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
--  1. EMPRESAS
-- ============================================================
CREATE TABLE IF NOT EXISTS empresas (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug                VARCHAR(60)  UNIQUE NOT NULL,
    nombre              VARCHAR(120) NOT NULL,
    logo_url            VARCHAR(255) DEFAULT NULL,
    email               VARCHAR(120) DEFAULT NULL,
    telefono            VARCHAR(30)  DEFAULT NULL,
    ciudad              VARCHAR(80)  DEFAULT NULL,
    moneda              CHAR(3)      NOT NULL DEFAULT 'MXN',

    -- Impuesto
    impuesto_modo       ENUM('ninguno','suma','incluido') NOT NULL DEFAULT 'ninguno',
    impuesto_pct        DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    impuesto_nombre     VARCHAR(20)  NOT NULL DEFAULT 'IVA',

    -- Textos configurables del portal público
    texto_bienvenida    TEXT DEFAULT NULL,
    texto_aceptar       TEXT DEFAULT NULL,
    texto_rechazar      TEXT DEFAULT NULL,
    texto_recibo        TEXT DEFAULT NULL,

    -- Descuento automático en cotización
    adc_activo          TINYINT(1)   NOT NULL DEFAULT 0,
    adc_pct             DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    adc_horas           SMALLINT     NOT NULL DEFAULT 72,
    adc_texto           VARCHAR(255) DEFAULT NULL,

    -- Radar
    radar_config        JSON DEFAULT NULL,

    activa              TINYINT(1)   NOT NULL DEFAULT 1,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  2. USUARIOS
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    nombre          VARCHAR(120) NOT NULL,
    email           VARCHAR(120) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    rol             ENUM('admin','asesor') NOT NULL DEFAULT 'asesor',
    activo          TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login    DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usu_email (empresa_id, email),
    INDEX idx_usu_empresa (empresa_id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  3. SESIONES DE USUARIO (auth)
-- ============================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED NOT NULL,
    empresa_id  INT UNSIGNED NOT NULL,
    token       CHAR(64)     NOT NULL UNIQUE,
    ip          VARCHAR(45)  DEFAULT NULL,
    user_agent  VARCHAR(300) DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME     NOT NULL,
    INDEX idx_us_token    (token),
    INDEX idx_us_usuario  (usuario_id),
    INDEX idx_us_expires  (expires_at),
    FOREIGN KEY (usuario_id)  REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id)  REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  4. ARTÍCULOS (catálogo)
-- ============================================================
CREATE TABLE IF NOT EXISTS articulos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    sku             VARCHAR(60)  DEFAULT NULL,
    titulo          VARCHAR(255) NOT NULL,
    descripcion     LONGTEXT     DEFAULT NULL,
    precio          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    unidad          VARCHAR(30)  DEFAULT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_art_empresa (empresa_id, activo),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  5. CLIENTES
-- ============================================================
CREATE TABLE IF NOT EXISTS clientes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    nombre          VARCHAR(120) NOT NULL,
    telefono        VARCHAR(30)  NOT NULL,
    email           VARCHAR(120) DEFAULT NULL,
    ciudad          VARCHAR(80)  DEFAULT NULL,
    notas           TEXT         DEFAULT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cl_empresa (empresa_id, activo),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  6. CUPONES
-- ============================================================
CREATE TABLE IF NOT EXISTS cupones (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    codigo          VARCHAR(60)  NOT NULL,
    descripcion     VARCHAR(200) DEFAULT NULL,
    porcentaje      DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cupon (empresa_id, codigo),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  7. COTIZACIONES
-- ============================================================
CREATE TABLE IF NOT EXISTS cotizaciones (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    cliente_id      INT UNSIGNED DEFAULT NULL,
    usuario_id      INT UNSIGNED NOT NULL,
    cupon_id        INT UNSIGNED DEFAULT NULL,

    titulo          VARCHAR(255) NOT NULL,
    slug            VARCHAR(120) DEFAULT NULL,
    token           CHAR(64)     UNIQUE NOT NULL,
    descripcion     TEXT         DEFAULT NULL,
    notas_internas  TEXT         DEFAULT NULL,

    -- Totales calculados (se actualizan al guardar)
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    cupon_pct       DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    cupon_codigo    VARCHAR(60)   DEFAULT NULL,
    cupon_amt       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    impuesto_pct    DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    impuesto_modo   ENUM('ninguno','suma','incluido') NOT NULL DEFAULT 'ninguno',
    impuesto_amt    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total           DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    -- Estado
    estado          ENUM('borrador','enviada','vista','aceptada','rechazada',
                         'aceptada_cliente','rechazada_cliente','convertida')
                    NOT NULL DEFAULT 'borrador',

    -- Razón de rechazo
    motivo_rechazo  VARCHAR(255) DEFAULT NULL,

    -- Timestamps de flujo
    enviada_at      DATETIME DEFAULT NULL,
    vista_at        DATETIME DEFAULT NULL,
    accion_at       DATETIME DEFAULT NULL,
    ultima_vista_at DATETIME DEFAULT NULL,

    -- Radar
    radar_bucket        VARCHAR(40)  DEFAULT NULL,
    radar_score         TINYINT UNSIGNED DEFAULT NULL,
    radar_senales       JSON DEFAULT NULL,
    radar_updated_at    DATETIME DEFAULT NULL,

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_slug (empresa_id, slug),
    INDEX idx_cot_empresa_estado  (empresa_id, estado, created_at),
    INDEX idx_cot_ultima_vista    (empresa_id, ultima_vista_at),
    INDEX idx_cot_bucket          (empresa_id, radar_bucket),
    INDEX idx_cot_token           (token),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (cupon_id)   REFERENCES cupones(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  8. LÍNEAS DE COTIZACIÓN
-- ============================================================
CREATE TABLE IF NOT EXISTS cotizacion_lineas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id   INT UNSIGNED NOT NULL,
    articulo_id     INT UNSIGNED DEFAULT NULL,
    orden           SMALLINT     NOT NULL DEFAULT 0,
    titulo          VARCHAR(255) NOT NULL,
    descripcion     TEXT         DEFAULT NULL,
    sku             VARCHAR(60)  DEFAULT NULL,
    cantidad        DECIMAL(10,4) NOT NULL DEFAULT 1.0000,
    precio_unit     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (articulo_id)   REFERENCES articulos(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  9. ARCHIVOS ADJUNTOS DE COTIZACIÓN
-- ============================================================
CREATE TABLE IF NOT EXISTS cotizacion_archivos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id   INT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_archivo  VARCHAR(255) NOT NULL,
    mime_type       VARCHAR(80)  DEFAULT NULL,
    tamano_bytes    INT UNSIGNED DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  10. LOG DE COTIZACIÓN
-- ============================================================
CREATE TABLE IF NOT EXISTS cotizacion_log (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id   INT UNSIGNED NOT NULL,
    usuario_id      INT UNSIGNED DEFAULT NULL,
    evento          VARCHAR(80)  NOT NULL,
    -- creada, editada, enviada, vista, aceptada, rechazada, convertida, etc.
    detalle         TEXT         DEFAULT NULL,
    ip              VARCHAR(45)  DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cot_log (cotizacion_id, created_at),
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  11. VENTAS
-- ============================================================
CREATE TABLE IF NOT EXISTS ventas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    cotizacion_id   INT UNSIGNED UNIQUE NOT NULL,
    cliente_id      INT UNSIGNED DEFAULT NULL,
    usuario_id      INT UNSIGNED DEFAULT NULL,

    titulo          VARCHAR(255) NOT NULL,
    slug            VARCHAR(120) DEFAULT NULL,
    token           CHAR(64)     UNIQUE NOT NULL,

    total           DECIMAL(12,2) NOT NULL,
    pagado          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    saldo           DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    estado          ENUM('pendiente','parcial','pagada','entregada','cancelada')
                    NOT NULL DEFAULT 'pendiente',

    cancelado_at            DATETIME DEFAULT NULL,
    cancelado_motivo        VARCHAR(255) DEFAULT NULL,
    cancelado_por_id        INT UNSIGNED DEFAULT NULL,
    entregado_at            DATETIME DEFAULT NULL,

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_vta_slug (empresa_id, slug),
    INDEX idx_vta_empresa (empresa_id, estado, created_at),
    FOREIGN KEY (empresa_id)    REFERENCES empresas(id)     ON DELETE CASCADE,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id),
    FOREIGN KEY (cliente_id)    REFERENCES clientes(id)     ON DELETE SET NULL,
    FOREIGN KEY (usuario_id)    REFERENCES usuarios(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  12. RECIBOS DE PAGO
-- ============================================================
CREATE TABLE IF NOT EXISTS recibos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venta_id        INT UNSIGNED NOT NULL,
    empresa_id      INT UNSIGNED NOT NULL,
    numero          VARCHAR(30)  NOT NULL,
    concepto        VARCHAR(255) DEFAULT NULL,
    monto           DECIMAL(12,2) NOT NULL,
    fecha           DATE         NOT NULL,
    token           CHAR(64)     UNIQUE NOT NULL,
    cancelado       TINYINT(1)   NOT NULL DEFAULT 0,
    cancelado_at    DATETIME     DEFAULT NULL,
    notas           TEXT         DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recibo_venta (venta_id),
    FOREIGN KEY (venta_id)   REFERENCES ventas(id)   ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  13. COSTOS — CATEGORÍAS
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias_costos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    nombre          VARCHAR(80)  NOT NULL,
    color           VARCHAR(7)   NOT NULL DEFAULT '#6b7280',
    activa          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cat_nombre (empresa_id, nombre),
    INDEX idx_cat_empresa (empresa_id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  14. COSTOS — GASTOS POR VENTA
-- ============================================================
CREATE TABLE IF NOT EXISTS gastos_venta (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    venta_id        INT UNSIGNED NOT NULL,
    categoria_id    INT UNSIGNED DEFAULT NULL,
    concepto        VARCHAR(255) NOT NULL,
    importe         DECIMAL(12,2) NOT NULL,
    fecha           DATE         NOT NULL,
    nota            TEXT         DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gv_venta   (venta_id),
    INDEX idx_gv_empresa (empresa_id, fecha),
    FOREIGN KEY (venta_id)    REFERENCES ventas(id)            ON DELETE CASCADE,
    FOREIGN KEY (empresa_id)  REFERENCES empresas(id)          ON DELETE CASCADE,
    FOREIGN KEY (categoria_id)REFERENCES categorias_costos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  15. TRACKING — SESIONES DE COTIZACIÓN (portal público)
-- ============================================================
CREATE TABLE IF NOT EXISTS quote_sessions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id   INT UNSIGNED NOT NULL,
    visitor_id      VARCHAR(64)  DEFAULT NULL,
    session_id      VARCHAR(36)  DEFAULT NULL,
    page_id         VARCHAR(36)  DEFAULT NULL,
    ip              VARCHAR(45)  DEFAULT NULL,
    user_agent      VARCHAR(300) DEFAULT NULL,
    scroll_max      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    visible_ms      INT UNSIGNED NOT NULL DEFAULT 0,
    open_ms         INT UNSIGNED NOT NULL DEFAULT 0,
    activa          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_qs_cotizacion (cotizacion_id, activa, updated_at),
    INDEX idx_qs_visitor    (visitor_id),
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  16. TRACKING — EVENTOS JS DEL PORTAL PÚBLICO
--  Equivalente a sliced_quote_events del mu-plugin On Time
-- ============================================================
CREATE TABLE IF NOT EXISTS quote_events (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id   INT UNSIGNED NOT NULL,
    visitor_id      VARCHAR(64)  DEFAULT NULL,
    session_id      VARCHAR(36)  DEFAULT NULL,
    page_id         VARCHAR(36)  DEFAULT NULL,
    tipo            VARCHAR(60)  NOT NULL,
    -- Valores válidos: quote_open, quote_close, quote_scroll,
    -- coupon_validate_click, section_view_totals, section_revisit_totals,
    -- quote_price_review_loop, promo_timer_present,
    -- accept_open, accept_confirm, reject_open, reject_confirm,
    -- tab_d, tab_t, print, share_wa
    max_scroll      TINYINT UNSIGNED DEFAULT NULL,
    open_ms         INT UNSIGNED DEFAULT NULL,
    visible_ms      INT UNSIGNED DEFAULT NULL,
    ip              VARCHAR(45)  DEFAULT NULL,
    ua              VARCHAR(255) DEFAULT NULL,
    ts_unix         INT UNSIGNED DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_qe_cotizacion (cotizacion_id, tipo),
    INDEX idx_qe_visitor    (visitor_id),
    INDEX idx_qe_ts         (ts_unix),
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  17. RADAR — IPs INTERNAS
--  Equivalente a internal_ips.json del mu-plugin On Time
-- ============================================================
CREATE TABLE IF NOT EXISTS radar_ips_internas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    ip              VARCHAR(45)  NOT NULL,
    etiqueta        VARCHAR(60)  DEFAULT NULL,
    fuente          VARCHAR(30)  NOT NULL DEFAULT 'manual',
    -- 'manual' | 'radar_open' | 'internal_user'
    aprendida_ts    INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ip (empresa_id, ip),
    INDEX idx_ip_empresa (empresa_id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  18. RADAR — VISITORS INTERNOS
--  Equivalente a internal_visitors.json del mu-plugin On Time
-- ============================================================
CREATE TABLE IF NOT EXISTS radar_visitors_internos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    visitor_id      VARCHAR(64)  NOT NULL,
    source          VARCHAR(30)  NOT NULL DEFAULT 'internal_user',
    -- 'internal_user' | 'internal_ip' | 'manual'
    usuario_id      INT UNSIGNED DEFAULT NULL,
    ip              VARCHAR(45)  DEFAULT NULL,
    label           VARCHAR(255) DEFAULT NULL,
    first_seen      INT UNSIGNED NOT NULL DEFAULT 0,
    last_seen       INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rv_empresa_visitor (empresa_id, visitor_id),
    INDEX idx_rv_empresa (empresa_id),
    INDEX idx_rv_visitor (visitor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  19. RADAR — CALIBRACIÓN FIT POR EMPRESA
-- ============================================================
CREATE TABLE IF NOT EXISTS radar_fit_calibracion (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT UNSIGNED NOT NULL,
    activa          TINYINT(1)   NOT NULL DEFAULT 1,
    global_rate     DECIMAL(6,4) NOT NULL DEFAULT 0.0815,
    rate_sess_json  JSON         DEFAULT NULL,
    rate_ips_json   JSON DEFAULT NULL,
    rate_gap_json   JSON DEFAULT NULL,
    bandas_json     JSON DEFAULT NULL,
    cotizaciones    INT UNSIGNED NOT NULL DEFAULT 0,
    ventas_cerradas INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rfc_empresa (empresa_id, activa),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  FOREIGN KEY CHECKS ON
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  EMPRESA DEMO — closetfactory
--
--  PASO REQUERIDO antes de ejecutar este SQL:
--  Generar el hash de tu password con:
--    php -r "echo password_hash('TuPassword', PASSWORD_BCRYPT);"
--  Y sustituir la cadena HASH_AQUI en el INSERT de abajo.
--
--  Alternativa: crear el usuario desde el panel de registro
--  en /auth/registro después de instalar.
-- ============================================================
INSERT IGNORE INTO empresas (slug, nombre, email, telefono, ciudad, moneda, impuesto_modo)
VALUES ('closetfactory', 'Closet Factory', 'closet@closetfactory.com', '6621234567', 'Hermosillo', 'MXN', 'ninguno');

-- Reemplazar HASH_AQUI con: php -r "echo password_hash('TuPassword', PASSWORD_BCRYPT);"
INSERT IGNORE INTO usuarios (empresa_id, nombre, email, password_hash, rol)
VALUES (
    (SELECT id FROM empresas WHERE slug='closetfactory'),
    'Admin Closet',
    'closet@closetfactory.com',
    'HASH_AQUI',
    'admin'
);
