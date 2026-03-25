-- ============================================================
--  Índices de rendimiento para escalar a 200+ empresas
--  Ejecutar en producción: mysql -u root cotizacl_cotizacloud < migrations/add_performance_indexes.sql
-- ============================================================

-- 1. quote_events: El Radar filtra por cotizacion_id + ts_unix (rango 150 días)
--    Índice actual (cotizacion_id, tipo) NO cubre el filtro por ts_unix
ALTER TABLE quote_events
  ADD INDEX idx_qe_cot_ts (cotizacion_id, ts_unix);

-- 2. quote_sessions: El Radar hace ORDER BY created_at por cotización
--    Índice actual (cotizacion_id, activa, updated_at) no sirve para ORDER BY created_at
ALTER TABLE quote_sessions
  ADD INDEX idx_qs_cot_created (cotizacion_id, created_at);

-- 3. gastos_venta: Los reportes hacen JOIN por (venta_id) + GROUP BY venta_id
--    con filtro empresa_id — el compuesto acelera el subquery de reportes
ALTER TABLE gastos_venta
  ADD INDEX idx_gv_venta_empresa (venta_id, empresa_id, importe);

-- 4. user_sessions: La limpieza borra WHERE expires_at < NOW()
--    Ya tiene idx_us_expires — OK, pero agregar empresa_id para queries de sesión
--    (skip — ya cubierto)

-- 5. actividad_log: Consultas de termómetro filtran por empresa_id + tipo + rango de fecha
--    idx_empresa_fecha existe pero no incluye tipo
ALTER TABLE actividad_log
  ADD INDEX idx_al_empresa_tipo_fecha (empresa_id, tipo, created_at);

-- 6. cotizaciones: Radar recalcula WHERE empresa_id=? AND estado IN (...) AND suspendida=0
--    Índice compuesto que cubre los 3 filtros juntos
ALTER TABLE cotizaciones
  ADD INDEX idx_cot_radar (empresa_id, suspendida, estado, id);
