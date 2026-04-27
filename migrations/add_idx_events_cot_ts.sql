CREATE INDEX idx_events_cot_ts ON quote_events(cotizacion_id, ts_unix DESC);
CREATE INDEX idx_qs_cot_created ON quote_sessions(cotizacion_id, created_at DESC);
