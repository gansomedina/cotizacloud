-- Motivo escrito del descarte, cuando el asesor elige "Otro".
--
-- POR QUÉ UNA COLUMNA NUEVA Y NO METERLO EN `razon`
-- `razon` es una LLAVE corta (VARCHAR(30)) con seis valores posibles, y hay
-- cuatro lugares que la comparan exacta:
--     core/RitmoReporte.php:208   $x['razon'] === 'precio'
--     core/RitmoReporte.php:212   $x['razon'] === 'despues'
--     core/RitmoReporte.php:377   isset($RZ[$c['razon']])
--     core/Mesa.php:242           razon <> 'auto'
-- Guardar ahí "otro: se mudó de ciudad" obligaba a ensancharla a VARCHAR(255)
-- —o sea, la misma migración— y convertía el campo llave en texto libre. Con
-- una columna aparte, `razon` se queda en 30 y esos cuatro lugares no se
-- enteran de nada.
--
-- Aditiva pura: nullable, sin default, sin índice. Ninguna consulta existente
-- la menciona, así que correrla no cambia el comportamiento de nada.

ALTER TABLE mesa_estados ADD COLUMN razon_texto VARCHAR(200) NULL AFTER razon;
