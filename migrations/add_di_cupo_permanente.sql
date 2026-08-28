-- Descuento Inteligente — el cupo del cliente se gasta al recibirlo.
--
-- REGLA (decisión CEO, 28-ago-2026): un cliente recibe UN descuento en su vida,
-- lo aproveche o no. Dejar pasar las 24 horas sin comprar NO le devuelve el
-- cupo — ya tuvo su oportunidad.
--
-- QUÉ ESTABA MAL
-- El candado soltaba el cupo al vencer:
--     CASE WHEN estado IN ('activo','utilizado') THEN cliente_id ELSE NULL END
-- 'vencido' no estaba en la lista, así que liberaba. Y sí se llega a 'vencido':
-- el lazy-expiry de DescuentoInteligente::vigente() lo marca cuando alguien
-- reabre la cotización. Resultado: el cliente cuya cotización vieja alguien
-- reabrió podía recibir un segundo descuento, y el que nadie reabrió no. Mismo
-- caso, distinto resultado, decidido por accidente.
--
-- QUÉ QUEDA
-- Solo 'cancelado' libera el cupo — y eso es una corrección explícita del
-- asesor (el enlace "quitar" de la venta), no el paso del tiempo.
--
-- ANTES DE CORRER: comprobar que ningún cliente tenga ya dos activaciones
-- vivas, o el ADD UNIQUE falla y la tabla se queda SIN candado:
--
--   SELECT empresa_id, cliente_id, COUNT(*) AS n
--     FROM desc_int_activaciones WHERE estado <> 'cancelado'
--    GROUP BY empresa_id, cliente_id HAVING n > 1;
--
-- Debe devolver 0 filas. tools/migrar_di_cupo.php hace esa comprobación solo y
-- aborta antes de tocar nada — usar ese, no este archivo a mano.

ALTER TABLE desc_int_activaciones DROP INDEX uk_cliente_vivo;

ALTER TABLE desc_int_activaciones
    MODIFY COLUMN cliente_lock INT UNSIGNED GENERATED ALWAYS AS
        (CASE WHEN estado <> 'cancelado' THEN cliente_id ELSE NULL END) STORED;

ALTER TABLE desc_int_activaciones
    ADD UNIQUE KEY uk_cliente_vivo (cliente_lock);
