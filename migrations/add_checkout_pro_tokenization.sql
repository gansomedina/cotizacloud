-- Migración: tokenización de tarjeta (Checkout Pro + cobros recurrentes)
-- Sustituye el flujo de Preapproval por Checkout Pro + /v1/payments con token guardado

ALTER TABLE suscripciones
    ADD COLUMN mp_customer_id    VARCHAR(100) NULL AFTER mp_preapproval_id,
    ADD COLUMN mp_card_id         VARCHAR(100) NULL AFTER mp_customer_id,
    ADD COLUMN mp_last_payment_id VARCHAR(100) NULL AFTER mp_card_id,
    ADD COLUMN card_last4         VARCHAR(4)   NULL AFTER mp_last_payment_id,
    ADD COLUMN card_brand         VARCHAR(30)  NULL AFTER card_last4,
    ADD COLUMN card_exp_month     TINYINT UNSIGNED NULL AFTER card_brand,
    ADD COLUMN card_exp_year      SMALLINT UNSIGNED NULL AFTER card_exp_month,
    ADD COLUMN proximo_cobro      DATE         NULL AFTER card_exp_year,
    ADD COLUMN intentos_cobro     TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER proximo_cobro,
    ADD COLUMN ultimo_intento     DATETIME     NULL AFTER intentos_cobro,
    ADD COLUMN ultimo_error       VARCHAR(255) NULL AFTER ultimo_intento,
    ADD INDEX idx_proximo_cobro (proximo_cobro, estado);
