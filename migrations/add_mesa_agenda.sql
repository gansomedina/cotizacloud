-- Agenda de seguimiento en la mesa: el asesor "parquea" una cotización con una
-- fecha probable (ej. "me entregan la casa en 2 meses"). Mientras está agendada
-- a futuro, sale de la mesa diaria y NO cuenta en el score; reaparece 7 días
-- antes de la fecha y vive su ventana normal (2×p75) re-anclada a esa fecha.
--   agenda_fecha = fecha probable objetivo (el ancla nuevo)
--   agenda_at    = cuándo se agendó (para: cooldown de 15 días y para que el
--                  feedback/postura VIEJO no cuente en la ronda nueva — solo el
--                  posterior a agenda_at marca la cotización como atendida)
ALTER TABLE cotizaciones
    ADD COLUMN agenda_fecha DATE     NULL DEFAULT NULL AFTER accion_at,
    ADD COLUMN agenda_at    DATETIME NULL DEFAULT NULL AFTER agenda_fecha;

CREATE INDEX idx_agenda_fecha ON cotizaciones (empresa_id, agenda_fecha);
