-- ============================================================
--  Migración: teléfono de empresa del cliente + lada país
--
--  Por qué:
--  1. `clientes.telefono` pasa a significar EL MÓVIL / WHATSAPP — es el que
--     usa el botón "Enviar WhatsApp" para armar el enlace wa.me. Los clientes
--     que ya existen se asumen móviles (decisión del CEO); el que no lo sea
--     cae solo en el camino seguro (abre WhatsApp sin destinatario).
--     `telefono_empresa` guarda el fijo/oficina, que antes no tenía dónde vivir.
--
--  2. `empresas.lada_pais` es el código de país con el que se normaliza el
--     teléfono AL MOMENTO de armar el enlace. Los teléfonos se siguen
--     guardando como la gente los escribe ("662 142 1858") — normalizar en la
--     base rompería la búsqueda por tecleo y la detección de duplicados, y
--     obligaría a migrar datos existentes. Ver tel_whatsapp() en Helpers.php.
--
--  Ambas son aditivas y con default seguro: el sistema funciona igual si no
--  se corren (el código tolera que las columnas no existan).
-- ============================================================

ALTER TABLE `clientes`
    ADD COLUMN `telefono_empresa` VARCHAR(30) NULL AFTER `telefono`;

ALTER TABLE `empresas`
    ADD COLUMN `lada_pais` VARCHAR(4) NOT NULL DEFAULT '52';
