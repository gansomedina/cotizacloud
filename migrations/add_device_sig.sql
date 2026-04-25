-- Device signature para descarte del Radar
-- Hash de señales de hardware/preferencias del dispositivo
ALTER TABLE quote_sessions ADD COLUMN device_sig VARCHAR(20) NULL AFTER visitor_id;
ALTER TABLE quote_events ADD COLUMN device_sig VARCHAR(20) NULL AFTER visitor_id;

CREATE INDEX idx_qs_device_sig ON quote_sessions (device_sig);
CREATE INDEX idx_qe_device_sig ON quote_events (device_sig);
