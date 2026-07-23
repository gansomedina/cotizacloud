-- ============================================================
-- Fase B (23-jul-2026): trial de 30 días al registrarse.
-- trial_usado=1 = la prueba venció sin pago → bloquea SOLO crear
-- cotizaciones nuevas (acceso, abonos y datos siguen vivos).
-- NOTA: trial_info() auto-migra esta columna en runtime — este
-- archivo documenta el ALTER para correrlo explícito en el server.
-- ============================================================
ALTER TABLE empresas ADD COLUMN trial_usado TINYINT(1) NOT NULL DEFAULT 0;
