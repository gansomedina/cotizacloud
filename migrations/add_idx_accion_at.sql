-- Índice para queries de ActividadScore que filtran por accion_at
ALTER TABLE cotizaciones ADD INDEX idx_cot_empresa_accion_at (empresa_id, estado, accion_at);
