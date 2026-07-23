-- ============================================================
-- Fase B (23-jul-2026): trial de 30 días al registrarse.
--
-- es_trial=1   → SOLO lo pone el registro (flag EXPLÍCITO). Al vencer
--                sin pago: degradación suave a free. Lo limpian: pagar
--                (MP return / cron), activación manual del superadmin,
--                o la propia degradación.
-- trial_usado=1 → la prueba venció sin pago: bloquea SOLO crear
--                cotizaciones nuevas (acceso, abonos y datos vivos).
--                Lo limpian: pagar o activación manual del superadmin.
--
-- Los clientes de pago MANUAL/transferencia jamás tienen es_trial=1 →
-- conservan el flujo original (pantalla de licencia + grace + cron).
--
-- NOTA: trial_info() auto-migra ambas columnas en runtime — este
-- archivo documenta el ALTER para correrlo explícito en el server.
-- ============================================================
ALTER TABLE empresas ADD COLUMN es_trial TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE empresas ADD COLUMN trial_usado TINYINT(1) NOT NULL DEFAULT 0;
