-- Device signature para descarte del Radar
-- Hash de señales de hardware/preferencias del dispositivo
ALTER TABLE quote_sessions ADD COLUMN device_sig VARCHAR(20) NULL AFTER visitor_id;
ALTER TABLE quote_events ADD COLUMN device_sig VARCHAR(20) NULL AFTER visitor_id;
ALTER TABLE user_sessions ADD COLUMN device_sig VARCHAR(20) NULL AFTER user_agent;

CREATE INDEX idx_qs_device_sig ON quote_sessions (device_sig);
CREATE INDEX idx_qe_device_sig ON quote_events (device_sig);
CREATE INDEX idx_us_device_sig ON user_sessions (device_sig);
