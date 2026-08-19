-- ============================================================
--  plan_log — bitácora de movimientos de plan por empresa
--
--  CORRER EN PRODUCCIÓN ANTES DE DESPLEGAR EL CÓDIGO.
--  (Si se despliega primero, no pasa nada: el helper y la pantalla
--   toleran que la tabla no exista. Solo no se registra nada.)
--
--  Filosofía: la bitácora JAMÁS rechaza una escritura. Por eso no hay
--  ENUM, no hay tipo JSON y no hay FOREIGN KEY, y todo admite NULL
--  salvo empresa_id. Un dato raro se guarda feo; nunca revienta la
--  ruta del dinero (api/mp_return.php escribe dentro de transacción).
--
--  Volumen: los movimientos de plan son eventos raros (alta, pago,
--  renovación, degradación). NO necesita cron de purga como escudo_log.
-- ============================================================

CREATE TABLE IF NOT EXISTS `plan_log` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id`     INT UNSIGNED NOT NULL,

    -- Qué pasó. VARCHAR y no ENUM a propósito: un evento nuevo no
    -- contemplado debe guardarse tal cual, no ser rechazado.
    `evento`         VARCHAR(30)  NOT NULL DEFAULT '',
    `origen`         VARCHAR(24)  NOT NULL DEFAULT '',
    `motivo`         VARCHAR(40)      NULL,

    -- El dato que hoy no existe en ningún lado: de qué plan a qué plan.
    -- VARCHAR porque el ENUM de empresas.plan ya cambió dos veces y la
    -- bitácora tiene que poder guardar un valor que el ENUM de hoy ya
    -- no acepta. NULL = no registrado (nunca se adivina).
    `plan_anterior`  VARCHAR(20)      NULL,
    `plan_nuevo`     VARCHAR(20)      NULL,
    `vence_anterior` DATE             NULL,
    `vence_nuevo`    DATE             NULL,
    `dias`           SMALLINT         NULL,
    `ciclo`          VARCHAR(10)      NULL,
    `monto_mxn`      DECIMAL(10,2)    NULL,

    -- Quién. NULL en cron y webhook, donde no hay sesión.
    `usuario_id`     INT UNSIGNED     NULL,
    `usuario_nombre` VARCHAR(120)     NULL,
    `ip`             VARCHAR(45)      NULL,

    -- Referencia natural del hecho (mp_pay:123, sub:4). Indexada pero NO
    -- única: si un cobro se registra dos veces queremos VER las dos filas,
    -- no esconder el problema.
    `ref`            VARCHAR(100)     NULL,

    -- probado | inferido | acotado. Las escrituras vivas son siempre
    -- 'probado'. Solo el reconstructor histórico usa las otras dos.
    `confianza`      VARCHAR(10)  NOT NULL DEFAULT 'probado',

    -- Solo lo llena el reconstructor. UNIQUE admite N filas con NULL en
    -- MySQL/MariaDB, así que las escrituras vivas jamás chocan aquí; el
    -- backfill sí, y por eso es idempotente con INSERT IGNORE.
    `hecho_uid`      VARCHAR(120)     NULL,

    -- TEXT y no JSON: en MariaDB el tipo JSON arrastra un CHECK
    -- json_valid() que RECHAZARÍA un string malformado.
    `detalle`        TEXT             NULL,

    -- ocurrio_at = cuándo pasó (el backfill lo pone en el pasado).
    -- registrado_at = cuándo lo supimos.
    `ocurrio_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `registrado_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_hecho_uid`     (`hecho_uid`),
    KEY        `idx_empresa_time` (`empresa_id`, `ocurrio_at`),
    KEY        `idx_empresa_ref`  (`empresa_id`, `ref`),
    KEY        `idx_evento_time`  (`evento`, `ocurrio_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
