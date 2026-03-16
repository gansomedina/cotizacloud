-- ============================================================
-- CotizaCloud v2: import 6 NO MATCH ventas
-- These invoices had no matching cotización in the original import.
-- Step 1: Create cotizaciones (estado=convertida)
-- Step 2: Insert ventas linked to them
-- ============================================================

-- ---------- 1. Cotizaciones ----------

-- INV-0085: Aracely Romero – REC1
INSERT IGNORE INTO cotizaciones (numero, empresa_id, cliente_id, usuario_id, cupon_id, titulo, slug, token, descripcion, notas_internas, notas_cliente, subtotal, cupon_pct, cupon_codigo, cupon_amt, impuesto_pct, impuesto_modo, impuesto_amt, total, estado, motivo_rechazo, enviada_at, vista_at, accion_at, aceptada_at, rechazada_at, rechazada_motivo, valida_hasta, ultima_vista_at, created_at, updated_at, visitas, descuento_auto_activo, descuento_auto_pct, descuento_auto_dias, descuento_auto_expira, descuento_auto_amt, cupon_monto) VALUES ('QUO-NM-085', 2, 4, 2, NULL, 'Aracely Romero Avenida 5 #218 6624307579 – REC1', 'imp-v2-quo-nm-085', SHA2('cotizacloud-nm-085', 256), NULL, NULL, NULL, 21000, 0, NULL, 0, 0, 'ninguno', 0, 21000, 'convertida', NULL, '2025-09-09 12:00:00', '2025-09-09 12:00:00', '2025-09-09 12:00:00', '2025-09-09 12:00:00', NULL, NULL, NULL, NULL, '2025-09-09 12:00:00', '2025-09-09 12:00:00', 0, 0, 0, 3, NULL, 0, 0);

-- INV-0084: Aracely Romero – ppal
INSERT IGNORE INTO cotizaciones (numero, empresa_id, cliente_id, usuario_id, cupon_id, titulo, slug, token, descripcion, notas_internas, notas_cliente, subtotal, cupon_pct, cupon_codigo, cupon_amt, impuesto_pct, impuesto_modo, impuesto_amt, total, estado, motivo_rechazo, enviada_at, vista_at, accion_at, aceptada_at, rechazada_at, rechazada_motivo, valida_hasta, ultima_vista_at, created_at, updated_at, visitas, descuento_auto_activo, descuento_auto_pct, descuento_auto_dias, descuento_auto_expira, descuento_auto_amt, cupon_monto) VALUES ('QUO-NM-084', 2, 4, 2, NULL, 'Aracely Romero Avenida 5 218 6624307579 – ppal', 'imp-v2-quo-nm-084', SHA2('cotizacloud-nm-084', 256), NULL, NULL, NULL, 21300, 0, NULL, 0, 0, 'ninguno', 0, 21300, 'convertida', NULL, '2025-09-08 12:00:00', '2025-09-08 12:00:00', '2025-09-08 12:00:00', '2025-09-08 12:00:00', NULL, NULL, NULL, NULL, '2025-09-08 12:00:00', '2025-09-08 12:00:00', 0, 0, 0, 3, NULL, 0, 0);

-- INV-0083: Aracely Romero – PRNCPLWLKIN
INSERT IGNORE INTO cotizaciones (numero, empresa_id, cliente_id, usuario_id, cupon_id, titulo, slug, token, descripcion, notas_internas, notas_cliente, subtotal, cupon_pct, cupon_codigo, cupon_amt, impuesto_pct, impuesto_modo, impuesto_amt, total, estado, motivo_rechazo, enviada_at, vista_at, accion_at, aceptada_at, rechazada_at, rechazada_motivo, valida_hasta, ultima_vista_at, created_at, updated_at, visitas, descuento_auto_activo, descuento_auto_pct, descuento_auto_dias, descuento_auto_expira, descuento_auto_amt, cupon_monto) VALUES ('QUO-NM-083', 2, 4, 2, NULL, 'Aracely Romero Avenida 5 #218 6624307579 – PRNCPLWLKIN', 'imp-v2-quo-nm-083', SHA2('cotizacloud-nm-083', 256), NULL, NULL, NULL, 27500, 0, NULL, 0, 0, 'ninguno', 0, 27500, 'convertida', NULL, '2025-09-08 12:00:00', '2025-09-08 12:00:00', '2025-09-08 12:00:00', '2025-09-08 12:00:00', NULL, NULL, NULL, NULL, '2025-09-08 12:00:00', '2025-09-08 12:00:00', 0, 0, 0, 3, NULL, 0, 0);

-- INV-0022: Almar 29, La Coruna
INSERT IGNORE INTO cotizaciones (numero, empresa_id, cliente_id, usuario_id, cupon_id, titulo, slug, token, descripcion, notas_internas, notas_cliente, subtotal, cupon_pct, cupon_codigo, cupon_amt, impuesto_pct, impuesto_modo, impuesto_amt, total, estado, motivo_rechazo, enviada_at, vista_at, accion_at, aceptada_at, rechazada_at, rechazada_motivo, valida_hasta, ultima_vista_at, created_at, updated_at, visitas, descuento_auto_activo, descuento_auto_pct, descuento_auto_dias, descuento_auto_expira, descuento_auto_amt, cupon_monto) VALUES ('QUO-NM-022', 2, 4, 2, NULL, 'Almar 29, La Coruna', 'imp-v2-quo-nm-022', SHA2('cotizacloud-nm-022', 256), NULL, NULL, NULL, 30000, 0, NULL, 0, 0, 'ninguno', 0, 30000, 'convertida', NULL, '2024-07-27 12:00:00', '2024-07-27 12:00:00', '2024-07-27 12:00:00', '2024-07-27 12:00:00', NULL, NULL, NULL, NULL, '2024-07-27 12:00:00', '2024-07-27 12:00:00', 0, 0, 0, 3, NULL, 0, 0);

-- INV-0012: Ana Maria Puerta Real Closet 2
INSERT IGNORE INTO cotizaciones (numero, empresa_id, cliente_id, usuario_id, cupon_id, titulo, slug, token, descripcion, notas_internas, notas_cliente, subtotal, cupon_pct, cupon_codigo, cupon_amt, impuesto_pct, impuesto_modo, impuesto_amt, total, estado, motivo_rechazo, enviada_at, vista_at, accion_at, aceptada_at, rechazada_at, rechazada_motivo, valida_hasta, ultima_vista_at, created_at, updated_at, visitas, descuento_auto_activo, descuento_auto_pct, descuento_auto_dias, descuento_auto_expira, descuento_auto_amt, cupon_monto) VALUES ('QUO-NM-012', 2, 4, 2, NULL, 'Ana Maria Puerta Real Closet 2', 'imp-v2-quo-nm-012', SHA2('cotizacloud-nm-012', 256), NULL, NULL, NULL, 15000, 0, NULL, 0, 0, 'ninguno', 0, 15000, 'convertida', NULL, '2024-03-22 12:00:00', '2024-03-22 12:00:00', '2024-03-22 12:00:00', '2024-03-22 12:00:00', NULL, NULL, NULL, NULL, '2024-03-22 12:00:00', '2024-03-22 12:00:00', 0, 0, 0, 3, NULL, 0, 0);

-- INV-0011: Chilpancingo 1324
INSERT IGNORE INTO cotizaciones (numero, empresa_id, cliente_id, usuario_id, cupon_id, titulo, slug, token, descripcion, notas_internas, notas_cliente, subtotal, cupon_pct, cupon_codigo, cupon_amt, impuesto_pct, impuesto_modo, impuesto_amt, total, estado, motivo_rechazo, enviada_at, vista_at, accion_at, aceptada_at, rechazada_at, rechazada_motivo, valida_hasta, ultima_vista_at, created_at, updated_at, visitas, descuento_auto_activo, descuento_auto_pct, descuento_auto_dias, descuento_auto_expira, descuento_auto_amt, cupon_monto) VALUES ('QUO-NM-011', 2, 4, 2, NULL, 'Chilpancingo 1324', 'imp-v2-quo-nm-011', SHA2('cotizacloud-nm-011', 256), NULL, NULL, NULL, 18000, 0, NULL, 0, 0, 'ninguno', 0, 18000, 'convertida', NULL, '2024-03-20 12:00:00', '2024-03-20 12:00:00', '2024-03-20 12:00:00', '2024-03-20 12:00:00', NULL, NULL, NULL, NULL, '2024-03-20 12:00:00', '2024-03-20 12:00:00', 0, 0, 0, 3, NULL, 0, 0);

-- ---------- 2. Ventas ----------

-- INV-0085: Aracely Romero – REC1
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at)
SELECT 2, c.id, 4, 2, 'INV-0085', 'Aracely Romero Avenida 5 #218 6624307579 – REC1', 'imp-v2-vta-inv-0085', 'd33e758769e159a984398355e392ffd16ece0a7d55d03ae2c65741d1167fa6f0', 21000, 21000, 0, 'pagada', '2025-09-09 12:00:00', '2025-09-09 12:00:00'
FROM cotizaciones c WHERE c.slug = 'imp-v2-quo-nm-085';

-- INV-0084: Aracely Romero – ppal
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at)
SELECT 2, c.id, 4, 2, 'INV-0084', 'Aracely Romero Avenida 5 218 6624307579 – ppal', 'imp-v2-vta-inv-0084', '0b3252729037d17f7bf8f8a1181a512356e145630b990f8ec206c4375ecd5588', 21300, 21300, 0, 'pagada', '2025-09-08 12:00:00', '2025-09-08 12:00:00'
FROM cotizaciones c WHERE c.slug = 'imp-v2-quo-nm-084';

-- INV-0083: Aracely Romero – PRNCPLWLKIN
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at)
SELECT 2, c.id, 4, 2, 'INV-0083', 'Aracely Romero Avenida 5 #218 6624307579 – PRNCPLWLKIN', 'imp-v2-vta-inv-0083', 'd56e90959ac58c140dc6874830af3fe29bc27d1185e14605d7e9c17521bcc6c8', 27500, 27500, 0, 'pagada', '2025-09-08 12:00:00', '2025-09-08 12:00:00'
FROM cotizaciones c WHERE c.slug = 'imp-v2-quo-nm-083';

-- INV-0022: Almar 29, La Coruna
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at)
SELECT 2, c.id, 4, 2, 'INV-0022', 'Almar 29, La Coruna', 'imp-v2-vta-inv-0022', '21c3dc26a1effc202761960d6e1a4487d4d78716934d881259112a00f453f495', 30000, 30000, 0, 'pagada', '2024-07-27 12:00:00', '2024-07-27 12:00:00'
FROM cotizaciones c WHERE c.slug = 'imp-v2-quo-nm-022';

-- INV-0012: Ana Maria Puerta Real Closet 2
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at)
SELECT 2, c.id, 4, 2, 'INV-0012', 'Ana Maria Puerta Real Closet 2', 'imp-v2-vta-inv-0012', '7f11d0b30efc11f5f0b26b74230a185ceca431c5d0d7490cbd8ba3401b6bfc7a', 15000, 15000, 0, 'pagada', '2024-03-22 12:00:00', '2024-03-22 12:00:00'
FROM cotizaciones c WHERE c.slug = 'imp-v2-quo-nm-012';

-- INV-0011: Chilpancingo 1324
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at)
SELECT 2, c.id, 4, 2, 'INV-0011', 'Chilpancingo 1324', 'imp-v2-vta-inv-0011', '7d1e43847f67cbc4ecbf7830e78cab1ce7b0af962125caafeec12e8e18d67606', 18000, 18000, 0, 'pagada', '2024-03-20 12:00:00', '2024-03-20 12:00:00'
FROM cotizaciones c WHERE c.slug = 'imp-v2-quo-nm-011';

-- ---------- Verificación ----------
SELECT 'Cotizaciones NM creadas' AS paso, COUNT(*) AS total FROM cotizaciones WHERE slug LIKE 'imp-v2-quo-nm-%';
SELECT 'Ventas NM creadas' AS paso, COUNT(*) AS total FROM ventas WHERE slug LIKE 'imp-v2-vta-inv-00%' AND cotizacion_id IS NOT NULL;
SELECT 'Total ventas' AS paso, COUNT(*) AS total FROM ventas WHERE empresa_id = 2 AND estado != 'cancelada';
SELECT 'Cotizaciones aceptada+convertida' AS paso, COUNT(*) AS total FROM cotizaciones WHERE empresa_id = 2 AND estado IN ('aceptada','convertida');
