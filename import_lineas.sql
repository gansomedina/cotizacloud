-- ============================================================
--  CotizaCloud — Import cotizacion_lineas from WordPress
--  Generated: 2026-03-15 20:29:00
--  Total: 1106 items from 827 cotizaciones
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Delete existing imported lines to avoid duplicates
DELETE cl FROM cotizacion_lineas cl
INNER JOIN cotizaciones c ON c.id = cl.cotizacion_id
WHERE c.empresa_id = 2 AND c.slug LIKE 'imp-quo-%';

-- WP ID: 8 / slug: imp-quo-111-8 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 90, 90
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-111-8';

-- WP ID: 10 / slug: imp-quo-112-10 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 175, 90, 15750
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-112-10';

-- WP ID: 17 / slug: imp-quo-113-17 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC NINA (GRANDE)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 400, 90, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-113-17';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA CON CUBIERTA ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 300, 90, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-113-17';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'VESTIDOR REC PRINCIPAL', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅NO INCLUYE PUERTAS EN AREA DE CLOSET✅Torre de entrepaños (5).✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 220 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 400, 60, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-113-17';

-- WP ID: 19 / slug: imp-quo-114-19 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-114-19';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VESTIDOR REC PPAL', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Base y maletero sin puertas✅Dos Torres Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-114-19';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Gabinetes de Lavanderia en Melamina. Incluye puertas, bisagras y jaladeras. Medida 150 cms x altura 120 cms x profundidad 45 cms. Incluye Repisas interiores (2).', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-114-19';

-- WP ID: 20 / slug: imp-quo-115-20 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'ESCRITORIO', 'ESCRITORIO EN MELAMINA, INCLUYE: 2 CAJONERAS CON 3 CAJONES CADA UNO, NICHO PARA IMPRESORA CON 1 CAJON. CUBIERTA EN MELAMINA.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-115-20';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'LIBRERO', 'EN MELAMINA, INCLUYE GABINETES BASE CON PUERTAS, HERRAJES Y JALADERAS. NICHOS CON REPISAS (FORRO DE PAREDES). NO INCLUYE ILUMINACION. MEDIDAS LARGO 280 CMS X PROFUNDIDAD 45 CMS X ALTURA 256 CM.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-115-20';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MUEBLE DE TV', 'MUEBLE DE TV EN MELAMINA. INCLUYE GABINETE FLOTADO CON PUERTAS. NICHO DECORTATIVO. FALSO MURO EN MELAMINA. NO INCLUYE ILUMINACION. MEDIDA: HASTA 200 CMS LARGO', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-115-20';

-- WP ID: 25 / slug: imp-quo-116-25 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-116-25';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE CAJONERA EXTRA', '✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).', NULL, 1, 3000, 3000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-116-25';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'TORRE REPISAS EXTRA', '✅Torre de Repisas (4 repisas) ancho máximo 60 cms.', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-116-25';

-- WP ID: 26 / slug: imp-quo-117-26 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 410, 80, 32800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-117-26';

-- WP ID: 27 / slug: imp-quo-118-27 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado con Torre de repisas y Nicho de TV diseno proporcionado por cliente ✅Torre Cajonera de 3 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 2.1, 13000, 27300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-118-27';

-- WP ID: 29 / slug: imp-quo-119-29 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 185, 90, 16650
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-119-29';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 3', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 195, 90, 17550
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-119-29';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'VESTIDOR REC PPAL', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Base y Maletero (No incluye puertas (costo adicional)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 220 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 400, 65, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-119-29';

-- WP ID: 54 / slug: imp-quo-121-54 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-121-54';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VESTIDOR EN U', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅No incluye puertas en maletero✅Torre Cajonera de 5 cajones ancho máximo 60 cms.✅Incluye Base y Maletero.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-121-54';

-- WP ID: 55 / slug: imp-quo-122-55 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 3METROS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado ✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-122-55';

-- WP ID: 58 / slug: imp-quo-123-58 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms.✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-123-58';

-- WP ID: 59 / slug: imp-quo-124-59 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms ✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-124-59';

-- WP ID: 60 / slug: imp-quo-125-60 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-125-60';

-- WP ID: 61 / slug: imp-quo-126-61 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-126-61';

-- WP ID: 62 / slug: imp-quo-127-62 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23500, 23500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-127-62';

-- WP ID: 63 / slug: imp-quo-128-63 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-128-63';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 1 (SIN PUERTAS)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅NO INCLUYE PUERTAS✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-128-63';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'VESTIDOR MELAMINA STANDARD (TIPO U)', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajoneras de 5 cajones ancho máximo 60 cmsNo incluye puertas.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-128-63';

-- WP ID: 64 / slug: imp-quo-129-64 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-129-64';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-129-64';

-- WP ID: 65 / slug: imp-quo-130-65 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 150 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-130-65';

-- WP ID: 66 / slug: imp-quo-131-66 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-131-66';

-- WP ID: 67 / slug: imp-quo-132-67 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 cajoneras de 1 cajon cu cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-132-67';

-- WP ID: 68 / slug: imp-quo-133-68 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-133-68';

-- WP ID: 69 / slug: imp-quo-134-69 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-134-69';

-- WP ID: 71 / slug: imp-quo-135-71 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-135-71';

-- WP ID: 72 / slug: imp-quo-136-72 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 211 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-136-72';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 299 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-136-72';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'VESTIDOR EN MELAMINA EN U', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Putertas en Maletero✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Area de tubos colgadores.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-136-72';

-- WP ID: 73 / slug: imp-quo-137-73 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET TIPO LIBRERO', 'Closet en Melamina Tipo Librero 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado y Escritorio✅Torre con repisas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 40500, 40500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-137-73';

-- WP ID: 74 / slug: imp-quo-138-74 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-138-74';

-- WP ID: 75 / slug: imp-quo-139-75 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-139-75';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MDF PINTADO PUERTA LISA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-139-75';

-- WP ID: 77 / slug: imp-quo-140-77 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajonera de 4 cajones ancho máximo 60 cms cada torre✅Puertas Principales ancho máximo 60 cms solo en area de Tubos.✅No incluye Puertas en Maletero✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-140-77';

-- WP ID: 79 / slug: imp-quo-141-79 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-141-79';

-- WP ID: 80 / slug: imp-quo-142-80 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-142-80';

-- WP ID: 81 / slug: imp-quo-143-81 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre de repisas (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-143-81';

-- WP ID: 82 / slug: imp-quo-144-82 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA TIPO VESTIDOR EN L 1.6 x 1.8', 'Closet tipo Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅No Incluye PuertasTorre Zapatera con repisas fijas (5) sin puerta, ancho maximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-144-82';

-- WP ID: 83 / slug: imp-quo-145-83 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2.47m', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22200, 22200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-145-83';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'ESCRITORIO EN MELAMINA', 'ESCRITORIO EN MELAMINA, CON 2 CAJONES. MEDIDA 120 CMS', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-145-83';

-- WP ID: 84 / slug: imp-quo-146-84 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-146-84';

-- WP ID: 87 / slug: imp-quo-147-87 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'ESCRITORIO', 'ESCRITORIO EN MELAMINA, INCLUYE: 2 CAJONERAS CON 3 CAJONES CADA UNO, NICHO PARA IMPRESORA CON 1 CAJON. CUBIERTA EN MELAMINA.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-147-87';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'LIBRERO', 'EN MELAMINA, INCLUYE GABINETES BASE CON PUERTAS, HERRAJES Y JALADERAS. NICHOS CON REPISAS (FORRO DE PAREDES). NO INCLUYE ILUMINACION. MEDIDAS LARGO 280 CMS X PROFUNDIDAD 45 CMS X ALTURA 256 CM.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-147-87';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MUEBLE DE TV', 'MUEBLE DE TV EN MELAMINA. INCLUYE GABINETE FLOTADO CON PUERTAS. NICHO DECORTATIVO. FALSO MURO EN MELAMINA. NO INCLUYE ILUMINACION. MEDIDA: HASTA 200 CMS LARGO', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-147-87';

-- WP ID: 88 / slug: imp-quo-148-88 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-148-88';

-- WP ID: 89 / slug: imp-quo-149-89 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE DE REPISAS FIJAS (5) (SIN PUERTA)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-149-89';

-- WP ID: 91 / slug: imp-quo-150-91 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17100, 17100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-150-91';

-- WP ID: 93 / slug: imp-quo-151-93 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-151-93';

-- WP ID: 94 / slug: imp-quo-152-94 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-152-94';

-- WP ID: 96 / slug: imp-quo-153-96 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-153-96';

-- WP ID: 97 / slug: imp-quo-154-97 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-154-97';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15900, 15900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-154-97';

-- WP ID: 98 / slug: imp-quo-155-98 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-155-98';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-155-98';

-- WP ID: 99 / slug: imp-quo-156-99 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2.79m', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-156-99';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 1.92m', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-156-99';

-- WP ID: 100 / slug: imp-quo-157-100 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-157-100';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA', 'Torre zapatera con repisas fijas (9). Ancho max 60. Prof max 60', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-157-100';

-- WP ID: 101 / slug: imp-quo-158-101 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET EN VENTANA', 'CLOSET EN MELAMINA, CON MODULO CAJONERA ABAJO VENTANA (5 CAJONES). INCLUYE PUERTAS LATERALES Y MALETERO CORRIDO. INCLUYE BASE Y MALETERO, NO INCLUYE FORRO DE MUROS INTERIORES. INCLUYE JALADERAS Y BISAGRAS.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-158-101';

-- WP ID: 102 / slug: imp-quo-159-102 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-159-102';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet EmpotradoNo incluye torre cajonera✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-159-102';

-- WP ID: 105 / slug: imp-quo-160-105 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-160-105';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'Cambio Puertas AltoBrillo', 'CAMBIO DE MATERIAL DE MELAMINA STANDARD A MELAMINA ALTO BRILLO BLANCO', NULL, 1, 8000, 8000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-160-105';

-- WP ID: 107 / slug: imp-quo-161-107 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-161-107';

-- WP ID: 108 / slug: imp-quo-162-108 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE ZAPATERA CON REPISAS FIJAS (5) SIN PUERTA, ANCHO MAXIMO 50 CMS. PROFUNDIDAD STANDARD 60 CMS.KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-162-108';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE TOCADOR', 'MUEBLE EN MELAMINA, CON CUBIERTA EN MELAMINA. INCLUYE 1 CAJONERA (1).', NULL, 1, 3500, 3500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-162-108';

-- WP ID: 109 / slug: imp-quo-163-109 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-163-109';

-- WP ID: 110 / slug: imp-quo-164-110 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-164-110';

-- WP ID: 111 / slug: imp-quo-165-111 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-165-111';

-- WP ID: 112 / slug: imp-quo-166-112 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cmsIncluye torre zapatera con repisas fijas (5) ancho maximo 60 cms.✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-166-112';

-- WP ID: 113 / slug: imp-quo-167-113 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-167-113';

-- WP ID: 116 / slug: imp-quo-168-116 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet EmpotradoNO INCLUYE: Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-168-116';

-- WP ID: 117 / slug: imp-quo-169-117 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet EmpotradoTorre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15900, 15900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-169-117';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'ESCRITORIO', '1 MODULO DE 1 PUERTA, EN MELAMINA INCLUYE CUBIERTA EN MELAMINA', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-169-117';

-- WP ID: 118 / slug: imp-quo-170-118 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-170-118';

-- WP ID: 119 / slug: imp-quo-171-119 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-171-119';

-- WP ID: 120 / slug: imp-quo-172-120 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-172-120';

-- WP ID: 122 / slug: imp-quo-173-122 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-173-122';

-- WP ID: 124 / slug: imp-quo-174-124 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-174-124';

-- WP ID: 125 / slug: imp-quo-175-125 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-175-125';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-175-125';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. CAJONERA DE 4 CAJONES.KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-175-125';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'NICHO', 'NICHO CON PUERTA, INCLUYE TORRE ZAPATERA (7) REPISAS FIJAS, BASTIDORES Y HERRAJES', NULL, 1, 7500, 7500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-175-125';

-- WP ID: 126 / slug: imp-quo-176-126 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-176-126';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-176-126';

-- WP ID: 127 / slug: imp-quo-177-127 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-177-127';

-- WP ID: 128 / slug: imp-quo-178-128 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con (6) repisas fijas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-178-128';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con (6) repisas fijasancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-178-128';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD REC 3', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con (6) repisas fijasancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-178-128';

-- WP ID: 132 / slug: imp-quo-179-132 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-179-132';

-- WP ID: 133 / slug: imp-quo-180-133 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-180-133';

-- WP ID: 134 / slug: imp-quo-181-134 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23500, 23500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-181-134';

-- WP ID: 137 / slug: imp-quo-182-137 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-182-137';

-- WP ID: 139 / slug: imp-quo-183-139 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31000, 31000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-183-139';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET PUERTAS MDF PINTADO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 37000, 37000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-183-139';

-- WP ID: 140 / slug: imp-quo-184-140 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20700, 20700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-184-140';

-- WP ID: 141 / slug: imp-quo-185-141 (6 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRE CON HASTA 5 CAJONES CU, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-185-141';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA', 'EN MELAMINA, TORRE ZAPATERA CON REPISAS FIJAS INTERIORES (6). INCLUYE PUERTA. NO INCLUYE PUERTA EN MALETERO', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-185-141';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-185-141';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA STANDARD 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-185-141';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'CLOSET MELAMINA STANDARD 274 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-185-141';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 5, 'PUERTAS EN MELAMINA', 'EN MELAMINA, INCLUYE PUERTAS, HERRAJES Y VISTAS.', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-185-141';

-- WP ID: 142 / slug: imp-quo-186-142 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-186-142';

-- WP ID: 143 / slug: imp-quo-187-143 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-187-143';

-- WP ID: 145 / slug: imp-quo-188-145 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-188-145';

-- WP ID: 146 / slug: imp-quo-189-146 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRE CON HASTA 5 CAJONES CU, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-189-146';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA ( EN AREA EXTRA )', 'EN MELAMINA, TORRE ZAPATERA CON REPISAS FIJAS INTERIORES (6). INCLUYE PUERTA. NO INCLUYE PUERTA EN MALETERO', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-189-146';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-189-146';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'PUERTAS', 'PUERTAS EN MELAMINA, INCLUYE BASTIDORES Y HERRAJES.', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-189-146';

-- WP ID: 149 / slug: imp-quo-190-149 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-190-149';

-- WP ID: 150 / slug: imp-quo-191-150 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Cajones✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12500, 12500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-191-150';

-- WP ID: 151 / slug: imp-quo-192-151 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Torre zapatera con repisas interiores fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-192-151';

-- WP ID: 152 / slug: imp-quo-193-152 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13000, 13000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-193-152';

-- WP ID: 153 / slug: imp-quo-194-153 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-194-153';

-- WP ID: 154 / slug: imp-quo-195-154 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-195-154';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-195-154';

-- WP ID: 156 / slug: imp-quo-196-156 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-196-156';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-196-156';

-- WP ID: 157 / slug: imp-quo-197-157 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-197-157';

-- WP ID: 159 / slug: imp-quo-198-159 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre de repisas fijas (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13000, 13000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-198-159';

-- WP ID: 160 / slug: imp-quo-199-160 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-199-160';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-199-160';

-- WP ID: 161 / slug: imp-quo-200-161 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-200-161';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE TV', 'EN MELAMINA CATALOGO STANDARD. MUEBLE DE TV SEGUN DISENO MEDIDA 213 CMS. INCLUYE GABINETES FLOTADOS. CUBIERTA EN MELAMINA, HERRAJES Y LAMBRIN DE 90 CMS X 270 ALTURA. MEDIDAS:', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-200-161';

-- WP ID: 163 / slug: imp-quo-201-163 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA + ESCRITORIO (DISENO PROPORCIONADO POR CLIENTE)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32500, 32500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-201-163';

-- WP ID: 164 / slug: imp-quo-202-164 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-202-164';

-- WP ID: 166 / slug: imp-quo-203-166 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-203-166';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-203-166';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-203-166';

-- WP ID: 167 / slug: imp-quo-204-167 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS (EN U)', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-204-167';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-204-167';

-- WP ID: 168 / slug: imp-quo-205-168 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-205-168';

-- WP ID: 169 / slug: imp-quo-206-169 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-206-169';

-- WP ID: 170 / slug: imp-quo-207-170 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-207-170';

-- WP ID: 171 / slug: imp-quo-208-171 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-208-171';

-- WP ID: 172 / slug: imp-quo-209-172 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-209-172';

-- WP ID: 174 / slug: imp-quo-210-174 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-210-174';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-210-174';

-- WP ID: 175 / slug: imp-quo-211-175 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-211-175';

-- WP ID: 176 / slug: imp-quo-212-176 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-212-176';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-212-176';

-- WP ID: 177 / slug: imp-quo-213-177 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-213-177';

-- WP ID: 178 / slug: imp-quo-214-178 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-214-178';

-- WP ID: 179 / slug: imp-quo-215-179 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15700, 15700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-215-179';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA', 'En Melamina: Torre zapatera con repisas fijas (5) con puerta y maletero con puerta ambos empotrados en nicho: 0.63m ancho x 2.56m alto', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-215-179';

-- WP ID: 180 / slug: imp-quo-216-180 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-216-180';

-- WP ID: 181 / slug: imp-quo-217-181 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-217-181';

-- WP ID: 183 / slug: imp-quo-218-183 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.Torre con repisas fijas✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-218-183';

-- WP ID: 184 / slug: imp-quo-219-184 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-219-184';

-- WP ID: 185 / slug: imp-quo-220-185 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-220-185';

-- WP ID: 187 / slug: imp-quo-221-187 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-221-187';

-- WP ID: 190 / slug: imp-quo-222-190 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-222-190';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-222-190';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-222-190';

-- WP ID: 191 / slug: imp-quo-223-191 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-223-191';

-- WP ID: 192 / slug: imp-quo-224-192 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-224-192';

-- WP ID: 193 / slug: imp-quo-225-193 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2.4M', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-225-193';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 2.1M', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-225-193';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'PUERTAS EN NICHO', 'EN MELAMINA: 0.74m ancho x 2.10m alto . INCLUYE HERRAJES, BISAGRAS, JALADERAS Y 2 REPISAS FIJAS', NULL, 1, 7000, 7000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-225-193';

-- WP ID: 194 / slug: imp-quo-226-194 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-226-194';

-- WP ID: 195 / slug: imp-quo-227-195 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-227-195';

-- WP ID: 196 / slug: imp-quo-228-196 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-228-196';

-- WP ID: 197 / slug: imp-quo-229-197 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-229-197';

-- WP ID: 199 / slug: imp-quo-230-199 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-230-199';

-- WP ID: 200 / slug: imp-quo-231-200 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅NO INCLUYE MALETERO, ALTURA 180 CMS✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-231-200';

-- WP ID: 201 / slug: imp-quo-232-201 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-232-201';

-- WP ID: 202 / slug: imp-quo-233-202 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Torre zapatera con repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-233-202';

-- WP ID: 203 / slug: imp-quo-234-203 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅DOS Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-234-203';

-- WP ID: 204 / slug: imp-quo-235-204 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-235-204';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-235-204';

-- WP ID: 206 / slug: imp-quo-236-206 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-236-206';

-- WP ID: 208 / slug: imp-quo-237-208 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. INCLUYE REPISAS SEGUN DISENOKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-237-208';

-- WP ID: 210 / slug: imp-quo-238-210 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-238-210';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA (SIN TORRE CAJONERA)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-238-210';

-- WP ID: 211 / slug: imp-quo-239-211 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-239-211';

-- WP ID: 212 / slug: imp-quo-240-212 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + ESCRITORIO + NICHO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cmsEscritorio en Melamina + Nicho con repisas segun diseno✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-240-212';

-- WP ID: 213 / slug: imp-quo-241-213 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 4 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-241-213';

-- WP ID: 214 / slug: imp-quo-242-214 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MFD PINTADO (PUERTAS LISA)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-242-214';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-242-214';

-- WP ID: 216 / slug: imp-quo-243-216 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-243-216';

-- WP ID: 217 / slug: imp-quo-244-217 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-244-217';

-- WP ID: 218 / slug: imp-quo-245-218 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 1.9M', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-245-218';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 2.7M', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-245-218';

-- WP ID: 219 / slug: imp-quo-246-219 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-246-219';

-- WP ID: 221 / slug: imp-quo-247-221 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-247-221';

-- WP ID: 223 / slug: imp-quo-248-223 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA VERSION STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-248-223';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA DISENO POR EL CLIENTE', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Torre zapatera con repisas extraibles (6) perfil metabox✅Escritorio con 2 cajonenes metabox.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 37000, 37000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-248-223';

-- WP ID: 224 / slug: imp-quo-249-224 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 2 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-249-224';

-- WP ID: 225 / slug: imp-quo-250-225 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-250-225';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-250-225';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-250-225';

-- WP ID: 226 / slug: imp-quo-251-226 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA REPISAS, Y DOS BUROS CAJONERAS (DISENO PROPORCIONADO POR CLIENTE). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-251-226';

-- WP ID: 227 / slug: imp-quo-252-227 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-252-227';

-- WP ID: 230 / slug: imp-quo-253-230 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-253-230';

-- WP ID: 231 / slug: imp-quo-254-231 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-254-231';

-- WP ID: 232 / slug: imp-quo-255-232 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-255-232';

-- WP ID: 234 / slug: imp-quo-256-234 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE CON REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-256-234';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE CON REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-256-234';

-- WP ID: 235 / slug: imp-quo-257-235 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.Incluye cuatro repisas zapateras✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19800, 19800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-257-235';

-- WP ID: 236 / slug: imp-quo-258-236 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC PPAL', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-258-236';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC PPAL', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-258-236';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-258-236';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA STANDARD REC 3', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-258-236';

-- WP ID: 237 / slug: imp-quo-259-237 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-259-237';

-- WP ID: 238 / slug: imp-quo-260-238 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅DOS Torres Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-260-238';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-260-238';

-- WP ID: 239 / slug: imp-quo-261-239 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-261-239';

-- WP ID: 241 / slug: imp-quo-262-241 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-262-241';

-- WP ID: 244 / slug: imp-quo-263-244 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-263-244';

-- WP ID: 245 / slug: imp-quo-264-245 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-264-245';

-- WP ID: 248 / slug: imp-quo-265-248 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-265-248';

-- WP ID: 249 / slug: imp-quo-266-249 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-266-249';

-- WP ID: 250 / slug: imp-quo-267-250 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN L', 'FRENTES, PUERTAS EN MALETERO DOS TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-267-250';

-- WP ID: 251 / slug: imp-quo-268-251 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15200, 15200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-268-251';

-- WP ID: 254 / slug: imp-quo-269-254 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-269-254';

-- WP ID: 255 / slug: imp-quo-270-255 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-270-255';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-270-255';

-- WP ID: 256 / slug: imp-quo-271-256 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con repisas fijas (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-271-256';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-271-256';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS MALETERO', 'FRENTES, NO INCLUYE PUERTAS MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-271-256';

-- WP ID: 257 / slug: imp-quo-272-257 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-272-257';

-- WP ID: 259 / slug: imp-quo-273-259 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-273-259';

-- WP ID: 262 / slug: imp-quo-274-262 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-274-262';

-- WP ID: 264 / slug: imp-quo-275-264 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-275-264';

-- WP ID: 265 / slug: imp-quo-276-265 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-276-265';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13000, 13000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-276-265';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'TORRE DE REPISAS', 'TORRE DE REPISAS FIJAS (5) EN MELAMINA, MEDIDA: 160 CMS X 240 ALTURA', NULL, 1, 10000, 10000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-276-265';

-- WP ID: 266 / slug: imp-quo-277-266 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-277-266';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-277-266';

-- WP ID: 267 / slug: imp-quo-278-267 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-278-267';

-- WP ID: 269 / slug: imp-quo-279-269 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-279-269';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-279-269';

-- WP ID: 270 / slug: imp-quo-280-270 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET PUERTAS MDF CON MOLDURAS', 'Closet interior Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-280-270';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-280-270';

-- WP ID: 271 / slug: imp-quo-281-271 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-281-271';

-- WP ID: 272 / slug: imp-quo-282-272 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-282-272';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-282-272';

-- WP ID: 273 / slug: imp-quo-283-273 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-283-273';

-- WP ID: 275 / slug: imp-quo-284-275 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-284-275';

-- WP ID: 276 / slug: imp-quo-285-276 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms, incluye 1 repisa zapatera✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23500, 23500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-285-276';

-- WP ID: 277 / slug: imp-quo-286-277 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-286-277';

-- WP ID: 280 / slug: imp-quo-287-280 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 53000, 53000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-287-280';

-- WP ID: 281 / slug: imp-quo-288-281 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-288-281';

-- WP ID: 282 / slug: imp-quo-289-282 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-289-282';

-- WP ID: 284 / slug: imp-quo-290-284 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Torre Zapatera con repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-290-284';

-- WP ID: 286 / slug: imp-quo-291-286 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 200 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17800, 17800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-291-286';

-- WP ID: 287 / slug: imp-quo-292-287 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-292-287';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-292-287';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE ZAPATERA CON REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-292-287';

-- WP ID: 288 / slug: imp-quo-293-288 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA CON ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 2 cajones ancho máximo 60 cms + Escritorio con 2 cajones✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-293-288';

-- WP ID: 289 / slug: imp-quo-294-289 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-294-289';

-- WP ID: 292 / slug: imp-quo-295-292 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 2.10m x 1.47m', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-295-292';

-- WP ID: 293 / slug: imp-quo-296-293 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-296-293';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-296-293';

-- WP ID: 295 / slug: imp-quo-297-295 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'ZAPATERA', 'ZAPATERA EN MELAMINA, MEDIDAS: 0.50m ancho x 2.34m alto, INCLUYE 7 REPISAS FIJAS.', NULL, 1, 8000, 8000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-297-295';

-- WP ID: 296 / slug: imp-quo-298-296 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-298-296';

-- WP ID: 297 / slug: imp-quo-299-297 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-299-297';

-- WP ID: 298 / slug: imp-quo-300-298 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-300-298';

-- WP ID: 299 / slug: imp-quo-301-299 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 120 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11500, 11500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-301-299';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 140 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-301-299';

-- WP ID: 300 / slug: imp-quo-302-300 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-302-300';

-- WP ID: 301 / slug: imp-quo-303-301 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-303-301';

-- WP ID: 304 / slug: imp-quo-304-304 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-304-304';

-- WP ID: 305 / slug: imp-quo-305-305 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅NO INCLUYE: Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10000, 10000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-305-305';

-- WP ID: 306 / slug: imp-quo-306-306 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-306-306';

-- WP ID: 311 / slug: imp-quo-307-311 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-307-311';

-- WP ID: 313 / slug: imp-quo-308-313 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-308-313';

-- WP ID: 315 / slug: imp-quo-309-315 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-309-315';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-309-315';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-309-315';

-- WP ID: 316 / slug: imp-quo-310-316 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA 120 cm sin torre cajonera', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10000, 10000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-310-316';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14900, 14900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-310-316';

-- WP ID: 317 / slug: imp-quo-311-317 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-311-317';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-311-317';

-- WP ID: 318 / slug: imp-quo-312-318 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-312-318';

-- WP ID: 320 / slug: imp-quo-313-320 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-313-320';

-- WP ID: 321 / slug: imp-quo-314-321 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-314-321';

-- WP ID: 322 / slug: imp-quo-315-322 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-315-322';

-- WP ID: 323 / slug: imp-quo-316-323 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-316-323';

-- WP ID: 324 / slug: imp-quo-317-324 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-317-324';

-- WP ID: 326 / slug: imp-quo-318-326 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-318-326';

-- WP ID: 327 / slug: imp-quo-319-327 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA CON NICHO TV', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajoneras de 5 cajones ancho máximo 60 cms + Nicho TV segun diseno proporcionado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-319-327';

-- WP ID: 328 / slug: imp-quo-320-328 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-320-328';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-320-328';

-- WP ID: 329 / slug: imp-quo-321-329 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-321-329';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-321-329';

-- WP ID: 332 / slug: imp-quo-322-332 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-322-332';

-- WP ID: 333 / slug: imp-quo-323-333 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-323-333';

-- WP ID: 334 / slug: imp-quo-324-334 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-324-334';

-- WP ID: 335 / slug: imp-quo-325-335 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-325-335';

-- WP ID: 336 / slug: imp-quo-326-336 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 2X2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30500, 30500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-326-336';

-- WP ID: 337 / slug: imp-quo-327-337 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-327-337';

-- WP ID: 338 / slug: imp-quo-328-338 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-328-338';

-- WP ID: 340 / slug: imp-quo-329-340 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-329-340';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE ZAPATERA CON REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 35700, 35700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-329-340';

-- WP ID: 341 / slug: imp-quo-330-341 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-330-341';

-- WP ID: 342 / slug: imp-quo-331-342 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-331-342';

-- WP ID: 343 / slug: imp-quo-332-343 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-332-343';

-- WP ID: 344 / slug: imp-quo-333-344 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-333-344';

-- WP ID: 347 / slug: imp-quo-334-347 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18900, 18900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-334-347';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-334-347';

-- WP ID: 348 / slug: imp-quo-335-348 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18900, 18900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-335-348';

-- WP ID: 414 / slug: imp-quo-336-414 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-336-414';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-336-414';

-- WP ID: 415 / slug: imp-quo-337-415 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-337-415';

-- WP ID: 418 / slug: imp-quo-338-418 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-338-418';

-- WP ID: 419 / slug: imp-quo-339-419 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-339-419';

-- WP ID: 420 / slug: imp-quo-340-420 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-340-420';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-340-420';

-- WP ID: 422 / slug: imp-quo-341-422 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)ESCRITORIO TIPO VANITY CON 2 CAJONES KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 59000, 59000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-341-422';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)ESCRITORIO TIPO VANITY CON 2 CAJONES KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32500, 32500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-341-422';

-- WP ID: 423 / slug: imp-quo-342-423 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-342-423';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-342-423';

-- WP ID: 425 / slug: imp-quo-343-425 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-343-425';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS en l', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-343-425';

-- WP ID: 427 / slug: imp-quo-344-427 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12900, 12900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-344-427';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-344-427';

-- WP ID: 428 / slug: imp-quo-345-428 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-345-428';

-- WP ID: 429 / slug: imp-quo-346-429 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-346-429';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-346-429';

-- WP ID: 430 / slug: imp-quo-347-430 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-347-430';

-- WP ID: 431 / slug: imp-quo-348-431 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15600, 15600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-348-431';

-- WP ID: 432 / slug: imp-quo-349-432 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-349-432';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-349-432';

-- WP ID: 434 / slug: imp-quo-350-434 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-350-434';

-- WP ID: 435 / slug: imp-quo-351-435 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-351-435';

-- WP ID: 436 / slug: imp-quo-352-436 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-352-436';

-- WP ID: 437 / slug: imp-quo-353-437 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-353-437';

-- WP ID: 438 / slug: imp-quo-354-438 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-354-438';

-- WP ID: 439 / slug: imp-quo-355-439 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO)DOS TORRES CON 5 CAJONES CU Y PUERTA COSMETIQUERA CON REPISA FIJA INTERIOR (1). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 30500, 30500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-355-439';

-- WP ID: 443 / slug: imp-quo-356-443 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12500, 12500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-356-443';

-- WP ID: 444 / slug: imp-quo-357-444 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-357-444';

-- WP ID: 445 / slug: imp-quo-358-445 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre de repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅NO INCLUYE: Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-358-445';

-- WP ID: 446 / slug: imp-quo-359-446 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-359-446';

-- WP ID: 448 / slug: imp-quo-360-448 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-360-448';

-- WP ID: 449 / slug: imp-quo-361-449 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-361-449';

-- WP ID: 450 / slug: imp-quo-362-450 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-362-450';

-- WP ID: 453 / slug: imp-quo-363-453 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-363-453';

-- WP ID: 454 / slug: imp-quo-364-454 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + VANITY (SIN CAJONES)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-364-454';

-- WP ID: 455 / slug: imp-quo-365-455 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-365-455';

-- WP ID: 456 / slug: imp-quo-366-456 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-366-456';

-- WP ID: 459 / slug: imp-quo-367-459 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17100, 17100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-367-459';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-367-459';

-- WP ID: 460 / slug: imp-quo-368-460 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 288 cm x 334 cm', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms. + ALTURA EXTRA✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-368-460';

-- WP ID: 461 / slug: imp-quo-369-461 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-369-461';

-- WP ID: 463 / slug: imp-quo-370-463 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-370-463';

-- WP ID: 464 / slug: imp-quo-371-464 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-371-464';

-- WP ID: 465 / slug: imp-quo-372-465 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-372-465';

-- WP ID: 467 / slug: imp-quo-373-467 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17800, 17800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-373-467';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-373-467';

-- WP ID: 468 / slug: imp-quo-374-468 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17800, 17800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-374-468';

-- WP ID: 469 / slug: imp-quo-375-469 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-375-469';

-- WP ID: 470 / slug: imp-quo-376-470 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-376-470';

-- WP ID: 471 / slug: imp-quo-377-471 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-377-471';

-- WP ID: 472 / slug: imp-quo-378-472 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21500, 21500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-378-472';

-- WP ID: 474 / slug: imp-quo-379-474 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-379-474';

-- WP ID: 475 / slug: imp-quo-380-475 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-380-475';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres de Repisas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-380-475';

-- WP ID: 476 / slug: imp-quo-381-476 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'OPCION 1) REC PPAL: CLOSET MELAMINA WALK IN VESTIDOR (SIN PUERTAS MALETERO)', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE CON REPISAS (ZAPATERA PROFUNDIDAD 30 CMS). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-381-476';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'OPCION 2) REC PPAL: CLOSET MELAMINA WALK IN VESTIDOR (CON PUERTAS MALETERO)', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE CON REPISAS (ZAPATERA PROFUNDIDAD 30 CMS).KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 46000, 46000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-381-476';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'REC 2: CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-381-476';

-- WP ID: 477 / slug: imp-quo-84-477 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-84-477';

-- WP ID: 478 / slug: imp-quo-383-478 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-383-478';

-- WP ID: 479 / slug: imp-quo-384-479 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15200, 15200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-384-479';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15200, 15200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-384-479';

-- WP ID: 480 / slug: imp-quo-385-480 (7 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-385-480';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.Escritorio en melamina segun diseno, incluye 2 cajones + repisas flotadas✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-385-480';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. DOS TORRES CON REPISAS FIJAS SEGUN DISENONO INCLUYE ESPEJOSKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 54000, 54000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-385-480';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'FLETE INSTALACION', 'Flete de Instalacion Foraneo 4 dias: Incluye 3 noches de hotel + alimentos para 1 equipo carpinteros (4 dias) + traslados Hermosillo-Guaymas', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-385-480';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'FORRO MURO AREA ESCRITORIO REC ULISESJR', 'En melamina std. forro trasero de muro no incluye adicionales, solo en area de Escritorio', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-385-480';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 5, 'LAVANDERIA', 'En melamina std. Gabinetes superiores de lavanderia, incluye gabinetes con puertas y repisas interior (2). Medida a desarrollar 270 cms. Profundidad de los gabinetes 45 cms y altura maxima 100 cms.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-385-480';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 6, 'PUERTAS ESCALERA', 'En melamina std. Puertas de hueco de escalera, incluye 2 puertas, bastidores en melamina, herrajes y vistas.', NULL, 1, 5000, 5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-385-480';

-- WP ID: 483 / slug: imp-quo-386-483 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 120 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet con 1 pared color✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11500, 11500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-386-483';

-- WP ID: 484 / slug: imp-quo-387-484 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-387-484';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'RESPALDO Y BUROS', 'En melamina standard. Respaldo de melamina hasta 120 cms (ancho del panel) incluye 2 buros tipo escritorio con 1 cajon cada uno.', NULL, 1, 7800, 7800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-387-484';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'LAMBRIN MELAMINA', 'Lambrin en melamina para respaldo de cama. Tiras de 6 cms. altura 120 cms.', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-387-484';

-- WP ID: 488 / slug: imp-quo-388-488 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA 2 TORRES', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅4 Repisas Zapateras inferiores (2 por lado)✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-388-488';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, '2 CAJONES EXTRA', '2 cajones extra (se instalan 1 en cada torre)', NULL, 1, 800, 800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-388-488';

-- WP ID: 489 / slug: imp-quo-389-489 (7 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-389-489';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.Escritorio en melamina segun diseno, incluye 2 cajones + repisas flotadas✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-389-489';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. DOS TORRES CON REPISAS FIJAS SEGUN DISENONO INCLUYE ESPEJOSKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 54000, 54000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-389-489';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'FLETE INSTALACION', 'Flete de Instalacion Foraneo 4 dias: Incluye 3 noches de hotel + alimentos para 1 equipo carpinteros (4 dias) + traslados Hermosillo-Guaymas', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-389-489';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'FORRO MURO AREA ESCRITORIO REC ULISESJR', 'En melamina std. forro trasero de muro no incluye adicionales, solo en area de Escritorio', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-389-489';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 5, 'LAVANDERIA', 'En melamina std. Gabinetes superiores de lavanderia, incluye gabinetes con puertas y repisas interior (2). Medida a desarrollar 270 cms. Profundidad de los gabinetes 45 cms y altura maxima 100 cms.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-389-489';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 6, 'PUERTAS ESCALERA', 'En melamina std. Puertas de hueco de escalera, incluye 2 puertas, bastidores en melamina, herrajes y vistas.', NULL, 1, 5000, 5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-389-489';

-- WP ID: 493 / slug: imp-quo-390-493 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-390-493';

-- WP ID: 494 / slug: imp-quo-391-494 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-391-494';

-- WP ID: 496 / slug: imp-quo-392-496 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-392-496';

-- WP ID: 497 / slug: imp-quo-393-497 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-393-497';

-- WP ID: 498 / slug: imp-quo-394-498 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-394-498';

-- WP ID: 499 / slug: imp-quo-395-499 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-395-499';

-- WP ID: 500 / slug: imp-quo-396-500 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19800, 19800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-396-500';

-- WP ID: 503 / slug: imp-quo-397-503 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-397-503';

-- WP ID: 504 / slug: imp-quo-398-504 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN U', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE ZAPATERA CON REPISAS FIJAS (5). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 59000, 59000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-398-504';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. + TORRE ZAPATERA CON REPISAS FIJAS (5) KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 45000, 45000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-398-504';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD + VANITY (2 CAJONES)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-398-504';

-- WP ID: 505 / slug: imp-quo-399-505 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15900, 15900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-399-505';

-- WP ID: 512 / slug: imp-quo-400-512 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15900, 15900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-400-512';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE REPISAS', 'EN MELAMINA STD. TORRE DE REPISAS FIJAS SEGUN DISENO', NULL, 1, 3000, 3000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-400-512';

-- WP ID: 514 / slug: imp-quo-402-514 (8 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-402-514';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-402-514';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 54000, 54000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-402-514';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'FLETE INSTALACION', 'Flete de Instalacion Foraneo 4 dias: Incluye 3 noches de hotel + alimentos para 1 equipo carpinteros (4 dias) + traslados Hermosillo-Guaymas', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-402-514';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'FORRO MURO AREA ESCRITORIO REC ULISESJR', 'En melamina std. forro trasero de muro no incluye adicionales, solo en area de Escritorio', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-402-514';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 5, 'PUERTAS ESCALERA', 'En melamina std. Puertas de hueco de escalera, incluye 2 puertas, bastidores en melamina, herrajes y vistas.', NULL, 1, 5000, 5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-402-514';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 6, 'DESCUENTO', 'DESCUENTO AL CONTRATO', NULL, 1, -5000, -5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-402-514';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 7, 'LAVANDERIA', 'En melamina std. Gabinetes superiores de lavanderia, incluye gabinetes con puertas y repisas interior (2). Medida a desarrollar 270 cms. Profundidad de los gabinetes 45 cms y altura maxima 100 cms.', NULL, 1, 10500, 10500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-402-514';

-- WP ID: 516 / slug: imp-quo-403-516 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-403-516';

-- WP ID: 517 / slug: imp-quo-404-517 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-404-517';

-- WP ID: 520 / slug: imp-quo-405-520 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-405-520';

-- WP ID: 523 / slug: imp-quo-406-523 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA (segun diseño) (UN CLOSET 226CMS)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho 75 cms✅Puertas Principales ancho 75 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Incluye repisas zapateras segun diseño y nicho en area central✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-406-523';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE A MEDIDA', 'EN MELAMINA STANDARD, MUEBLE A MEDIDA (AREA SUPERIOR DE PUERTA)', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-406-523';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'FLETE INSTALACION (3 CLOSETS)', 'FLETE DE INSTALACION: HERMOSILL-GUAYMAS, INCLUYE TRASLADOS, HOSPEDAJE Y ALIMENTOS PARA 3 DIAS.', NULL, 1, 7000, 7000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-406-523';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA STANDARD (UN CLOSET 226CMS)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-406-523';

-- WP ID: 524 / slug: imp-quo-407-524 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-407-524';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA DISENO CLIENTE', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera horizontal de 6 cajones, ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms + Repisa interior zapatera.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-407-524';

-- WP ID: 525 / slug: imp-quo-408-525 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-408-525';

-- WP ID: 526 / slug: imp-quo-409-526 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 297 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26400, 26400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-409-526';

-- WP ID: 527 / slug: imp-quo-410-527 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-410-527';

-- WP ID: 528 / slug: imp-quo-411-528 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Escritorio con 2 cajones horizontal✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-411-528';

-- WP ID: 529 / slug: imp-quo-412-529 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajonera de 5 cajones (cu) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-412-529';

-- WP ID: 530 / slug: imp-quo-413-530 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10500, 10500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-413-530';

-- WP ID: 531 / slug: imp-quo-414-531 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 38500, 38500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-414-531';

-- WP ID: 533 / slug: imp-quo-415-533 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'PUERTAS CLOSET', 'EN MELAMINA STD, PUERTAS DE CLOSET, INCLUYE BASTIDORES EN MELAMINA, Y HERRAJES.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-415-533';

-- WP ID: 535 / slug: imp-quo-416-535 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-416-535';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14800, 14800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-416-535';

-- WP ID: 536 / slug: imp-quo-417-536 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-417-536';

-- WP ID: 537 / slug: imp-quo-418-537 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 90, 90
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-418-537';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-418-537';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-418-537';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-418-537';

-- WP ID: 538 / slug: imp-quo-419-538 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD (LINEAL)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-419-538';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD (EN U)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 49500, 49500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-419-538';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 49000, 49000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-419-538';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 37000, 37000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-419-538';

-- WP ID: 540 / slug: imp-quo-420-540 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300CMS X H298', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 298 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-420-540';

-- WP ID: 541 / slug: imp-quo-421-541 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 170 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-421-541';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 180 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-421-541';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'FLETE INSTALACION (2 CLOSETS)', 'Flete de Instalacion Foraneo 2 dias: Incluye 1 noche de hotel + alimentos para 1 equipo carpinteros (2 dias) + traslados Hermosillo-Guaymas', NULL, 1, 4500, 4500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-421-541';

-- WP ID: 543 / slug: imp-quo-422-543 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-422-543';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-422-543';

-- WP ID: 544 / slug: imp-quo-423-544 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15600, 15600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-423-544';

-- WP ID: 546 / slug: imp-quo-424-546 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-424-546';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'GABINETES SUPERIORES COCINA', 'EN MELAMINA STANDARD, GABIENTES SUPERIORES DE COCINA.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-424-546';

-- WP ID: 547 / slug: imp-quo-425-547 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'MUEBLE DE BAÑO 119CMS', 'NO INCLUYE CUBIERTA - EN MELAMINA STD. MUEBLE DE BAÑO. INCLUYE MODULOS DE PUERTA, Y 1 MODULO DE 1 CAJONERA (3) CAJONES. INCLUYE HERRAJES.', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-425-547';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE DE BAÑO 100CMS', 'NO INCLUYE CUBIERTA - EN MELAMINA STD. MUEBLE DE BAÑO. INCLUYE MODULOS DE PUERTA, Y 1 MODULO DE 1 CAJONERA (3) CAJONES. INCLUYE HERRAJES.', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-425-547';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MUEBLE DE BAÑO 200CMS', 'NO INCLUYE CUBIERTA - EN MELAMINA STD. MUEBLE DE BAÑO. INCLUYE MODULOS DE PUERTA, Y 2 MODULOS DE 1 CAJONERA (3) CAJONES. INCLUYE HERRAJES.', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-425-547';

-- WP ID: 550 / slug: imp-quo-426-550 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16300, 16300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-426-550';

-- WP ID: 551 / slug: imp-quo-427-551 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-427-551';

-- WP ID: 553 / slug: imp-quo-428-553 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-428-553';

-- WP ID: 554 / slug: imp-quo-429-554 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 350 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-429-554';

-- WP ID: 555 / slug: imp-quo-430-555 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard 3.18 cm de ancho por 3.20 de altura. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-430-555';

-- WP ID: 556 / slug: imp-quo-431-556 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-431-556';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-431-556';

-- WP ID: 557 / slug: imp-quo-432-557 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE DE REPISAS FIJAS (5). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-432-557';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-432-557';

-- WP ID: 558 / slug: imp-quo-433-558 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-433-558';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'ESCRITORIO CON LIBRERO', 'EN MELAMINA STD. MEDIDAS: 1.13 mts de ancho X 2.70 mt de alto, 51 cm de fondo.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-433-558';

-- WP ID: 559 / slug: imp-quo-434-559 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + AREA TV', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torre Cajonera de 3 cajones ancho máximo 60 cms + Area TV con muro color calacatta✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31500, 31500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-434-559';

-- WP ID: 560 / slug: imp-quo-435-560 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-435-560';

-- WP ID: 561 / slug: imp-quo-436-561 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-436-561';

-- WP ID: 563 / slug: imp-quo-437-563 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-437-563';

-- WP ID: 564 / slug: imp-quo-438-564 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 4 metros', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-438-564';

-- WP ID: 565 / slug: imp-quo-439-565 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-439-565';

-- WP ID: 566 / slug: imp-quo-440-566 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-440-566';

-- WP ID: 567 / slug: imp-quo-441-567 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-441-567';

-- WP ID: 568 / slug: imp-quo-442-568 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-442-568';

-- WP ID: 569 / slug: imp-quo-443-569 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-443-569';

-- WP ID: 570 / slug: imp-quo-444-570 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-444-570';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-444-570';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-444-570';

-- WP ID: 571 / slug: imp-quo-445-571 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-445-571';

-- WP ID: 572 / slug: imp-quo-446-572 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-446-572';

-- WP ID: 573 / slug: imp-quo-447-573 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-447-573';

-- WP ID: 574 / slug: imp-quo-448-574 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-448-574';

-- WP ID: 575 / slug: imp-quo-449-575 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-449-575';

-- WP ID: 576 / slug: imp-quo-450-576 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-450-576';

-- WP ID: 577 / slug: imp-quo-451-577 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-451-577';

-- WP ID: 578 / slug: imp-quo-452-578 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-452-578';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-452-578';

-- WP ID: 579 / slug: imp-quo-453-579 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-453-579';

-- WP ID: 581 / slug: imp-quo-454-581 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-454-581';

-- WP ID: 582 / slug: imp-quo-455-582 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-455-582';

-- WP ID: 583 / slug: imp-quo-456-583 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-456-583';

-- WP ID: 584 / slug: imp-quo-457-584 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-457-584';

-- WP ID: 585 / slug: imp-quo-458-585 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 160 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-458-585';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 160 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-458-585';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD 90 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-458-585';

-- WP ID: 586 / slug: imp-quo-459-586 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-459-586';

-- WP ID: 587 / slug: imp-quo-460-587 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-460-587';

-- WP ID: 588 / slug: imp-quo-461-588 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-461-588';

-- WP ID: 589 / slug: imp-quo-462-589 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 150 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-462-589';

-- WP ID: 590 / slug: imp-quo-463-590 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-463-590';

-- WP ID: 591 / slug: imp-quo-464-591 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 1.8 X 3.6', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-464-591';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-464-591';

-- WP ID: 592 / slug: imp-quo-465-592 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-465-592';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10500, 10500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-465-592';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10500, 10500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-465-592';

-- WP ID: 593 / slug: imp-quo-466-593 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-466-593';

-- WP ID: 594 / slug: imp-quo-467-594 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-467-594';

-- WP ID: 596 / slug: imp-quo-468-596 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-468-596';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-468-596';

-- WP ID: 601 / slug: imp-quo-470-601 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-470-601';

-- WP ID: 602 / slug: imp-quo-471-602 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-471-602';

-- WP ID: 603 / slug: imp-quo-472-603 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA VENTANA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera horizontal de 8 cajones (ancho máximo 60 cms) ✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-472-603';

-- WP ID: 604 / slug: imp-quo-473-604 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-473-604';

-- WP ID: 605 / slug: imp-quo-474-605 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-474-605';

-- WP ID: 606 / slug: imp-quo-475-606 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-475-606';

-- WP ID: 607 / slug: imp-quo-476-607 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-476-607';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-476-607';

-- WP ID: 608 / slug: imp-quo-477-608 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-477-608';

-- WP ID: 609 / slug: imp-quo-478-609 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-478-609';

-- WP ID: 610 / slug: imp-quo-479-610 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-479-610';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-479-610';

-- WP ID: 611 / slug: imp-quo-480-611 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-480-611';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-480-611';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'FLETE FORANEO', 'INSTALACION FORANEA 2 DIAS: GASTROS DE TRASLADO Y HOSPEDAJE', NULL, 1, 6000, 6000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-480-611';

-- WP ID: 612 / slug: imp-quo-481-612 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-481-612';

-- WP ID: 613 / slug: imp-quo-482-613 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre con repisas fijas ancho máximo 60 cms + area de nichos al centro segun diseno✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-482-613';

-- WP ID: 614 / slug: imp-quo-483-614 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos torres de repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-483-614';

-- WP ID: 615 / slug: imp-quo-484-615 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE DE REPISAS FIJAS (5). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-484-615';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-484-615';

-- WP ID: 616 / slug: imp-quo-485-616 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + 2 repisas zapateras.✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17100, 17100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-485-616';

-- WP ID: 617 / slug: imp-quo-486-617 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-486-617';

-- WP ID: 618 / slug: imp-quo-487-618 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31500, 31500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-487-618';

-- WP ID: 620 / slug: imp-quo-488-620 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-488-620';

-- WP ID: 621 / slug: imp-quo-489-621 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-489-621';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-489-621';

-- WP ID: 622 / slug: imp-quo-490-622 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISENO + AREA VENTANA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-490-622';

-- WP ID: 624 / slug: imp-quo-491-624 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 130cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13900, 13900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-491-624';

-- WP ID: 625 / slug: imp-quo-492-625 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 285', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-492-625';

-- WP ID: 626 / slug: imp-quo-493-626 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS (230 x 217 Altura 310)', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. INCLUYE REPISAS EN SEPARACION DE TUBOS COLGADORES. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-493-626';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR (230 x 217 Altura 310)', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. INCLUYE REPISAS EN SEPARACION DE TUBOS COLGADORES. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44500, 44500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-493-626';

-- WP ID: 627 / slug: imp-quo-494-627 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-494-627';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 300cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-494-627';

-- WP ID: 628 / slug: imp-quo-495-628 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 190cm', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-495-628';

-- WP ID: 629 / slug: imp-quo-496-629 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-496-629';

-- WP ID: 630 / slug: imp-quo-497-630 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 130 x h350', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15200, 15200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-497-630';

-- WP ID: 631 / slug: imp-quo-498-631 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-498-631';

-- WP ID: 633 / slug: imp-quo-499-633 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22800, 22800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-499-633';

-- WP ID: 635 / slug: imp-quo-500-635 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 130cm', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-500-635';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 100 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-500-635';

-- WP ID: 636 / slug: imp-quo-501-636 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA + NICHO TV 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajonera de 4 cajones ancho máximo 60 cms + NICHO TV✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-501-636';

-- WP ID: 638 / slug: imp-quo-502-638 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 33200, 33200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-502-638';

-- WP ID: 639 / slug: imp-quo-503-639 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 108cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-503-639';

-- WP ID: 640 / slug: imp-quo-504-640 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-504-640';

-- WP ID: 641 / slug: imp-quo-505-641 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-505-641';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-505-641';

-- WP ID: 642 / slug: imp-quo-506-642 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-506-642';

-- WP ID: 643 / slug: imp-quo-507-643 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 268cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-507-643';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA CON REPISAS', 'EN MELAMINA STD. TORRE ZAPATERA CON REPISAS FIJAS (MEDIDA DE ANCHO 60 CMS, ALTURA DE CLOSET). OTRAS MEDIDAS SE COTIZA POR SEPARADO', NULL, 1, 5500, 5500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-507-643';

-- WP ID: 644 / slug: imp-quo-508-644 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-508-644';

-- WP ID: 645 / slug: imp-quo-509-645 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 200CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-509-645';

-- WP ID: 646 / slug: imp-quo-510-646 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19900, 19900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-510-646';

-- WP ID: 648 / slug: imp-quo-511-648 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-511-648';

-- WP ID: 649 / slug: imp-quo-512-649 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-512-649';

-- WP ID: 661 / slug: imp-quo-513-661 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-513-661';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15600, 15600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-513-661';

-- WP ID: 670 / slug: imp-quo-514-670 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-514-670';

-- WP ID: 671 / slug: imp-quo-515-671 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 180cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-515-671';

-- WP ID: 677 / slug: imp-quo-521-677 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS 300x250x300', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE DE REPISAS FIJAS (5). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 42500, 42500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-521-677';

-- WP ID: 678 / slug: imp-quo-522-678 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-522-678';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'GABINETE EXTRA ARRIBA DE PUERTA', 'ANCHO: 90 CMS ALTURA 50 CMS', NULL, 1, 3000, 3000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-522-678';

-- WP ID: 680 / slug: imp-quo-523-680 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2 - 230CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-523-680';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 3 - 240CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-523-680';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD 4 - 247 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-523-680';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE CON REPISAS FIJAS (5) KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-523-680';

-- WP ID: 681 / slug: imp-quo-524-681 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-524-681';

-- WP ID: 682 / slug: imp-quo-525-682 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'PAR DE BUROS RECAMARA - 50 x 35 x 60', 'PAR DE BUROS RECAMARA ANCHO 50 CMS ALTURA 60 CMS FONDO 35 CMS', NULL, 1, 4500, 4500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-525-682';

-- WP ID: 684 / slug: imp-quo-526-684 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-526-684';

-- WP ID: 687 / slug: imp-quo-527-687 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-527-687';

-- WP ID: 688 / slug: imp-quo-528-688 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-528-688';

-- WP ID: 690 / slug: imp-quo-529-690 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-529-690';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA + CAJONES + NICHO DE TV', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-529-690';

-- WP ID: 691 / slug: imp-quo-530-691 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-530-691';

-- WP ID: 692 / slug: imp-quo-531-692 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'REC 1: CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS (lineal 280 cms)', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. + UNA TORRE HORIZONTAL CON CAJONES (6) + NICHOS INFERIORESKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 31500, 31500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-531-692';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'REC 2: VESTIDOR CON AREA DE TV (280 CMS x h210)', 'FRENTES, PUERTAS Y 1 TORRE CAJONERA + TORRE COLGADORA CON PUERTAS (DE 60 CMS DE ANCHO CADA TORRE). INCLUYE MELAMINA SUPERIOR (TIPO MALETERO). ESCRITORIO CON TRES CAJONES METABOX. BASE, ZOCLO (NO INCLUYE FORRADO DE FONDOS EN PAREDES) KIT DE HERRAJES, RIELES DE EXTENSION 18\" Y BISAGRAS REFORZADAS, JALADERAS DE NUESTRO CATALOGO, TUBOS Y SOPORTES. PROFUNDIDAD STANDARD 60 CMS Y ANCHO MAXIMO DE CADA MODULO 60 CMS. ELEMENTOS ADICIONALES, TORRES, REPISAS, DIVISORES, SE COTIZAN POR SEPARADO. CAMBIOS Y AJUSTES ALTERAN EL PRECIO COTIZADO.', NULL, 4, 27000, 108000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-531-692';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'FLETE DE INSTALACIÓN', 'INCLUYE GASTOS DE FLETE E INSTALACION FORANEO.', NULL, 1, 8000, 8000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-531-692';

-- WP ID: 694 / slug: imp-quo-532-694 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-532-694';

-- WP ID: 695 / slug: imp-quo-533-695 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-533-695';

-- WP ID: 696 / slug: imp-quo-534-696 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-534-696';

-- WP ID: 697 / slug: imp-quo-535-697 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura de 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-535-697';

-- WP ID: 698 / slug: imp-quo-536-698 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 211 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-536-698';

-- WP ID: 699 / slug: imp-quo-537-699 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-537-699';

-- WP ID: 700 / slug: imp-quo-538-700 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-538-700';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD -175 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-538-700';

-- WP ID: 701 / slug: imp-quo-539-701 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + torre zapatera con repisas fijas (5)', NULL, 1, 27400, 27400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-539-701';

-- WP ID: 702 / slug: imp-quo-540-702 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 160 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-540-702';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 160 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-540-702';

-- WP ID: 703 / slug: imp-quo-541-703 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 173', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-541-703';

-- WP ID: 705 / slug: imp-quo-542-705 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-542-705';

-- WP ID: 707 / slug: imp-quo-543-707 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 3 - 240CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-543-707';

-- WP ID: 709 / slug: imp-quo-544-709 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-544-709';

-- WP ID: 710 / slug: imp-quo-545-710 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -155 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-545-710';

-- WP ID: 711 / slug: imp-quo-546-711 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS SIN MALETERO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-546-711';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-546-711';

-- WP ID: 712 / slug: imp-quo-547-712 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 260 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-547-712';

-- WP ID: 715 / slug: imp-quo-548-715 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-548-715';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 200cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-548-715';

-- WP ID: 716 / slug: imp-quo-549-716 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-549-716';

-- WP ID: 718 / slug: imp-quo-550-718 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-550-718';

-- WP ID: 719 / slug: imp-quo-551-719 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-551-719';

-- WP ID: 720 / slug: imp-quo-552-720 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 178 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-552-720';

-- WP ID: 725 / slug: imp-quo-170-725 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2 - 230CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas CIERRE LENTO (8), Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. +', NULL, 1, 21300, 21300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-170-725';

-- WP ID: 728 / slug: imp-quo-554-728 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE CON REPISAS FIJAS (5) KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-554-728';

-- WP ID: 735 / slug: imp-quo-555-735 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-555-735';

-- WP ID: 736 / slug: imp-quo-556-736 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-556-736';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-556-736';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-556-736';

-- WP ID: 737 / slug: imp-quo-557-737 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 415 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Hasta 2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-557-737';

-- WP ID: 739 / slug: imp-quo-558-739 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 214 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-558-739';

-- WP ID: 740 / slug: imp-quo-559-740 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. +Modulo de cajones (2) +Tocador con cajones (2) - NO incluye espejo', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-559-740';

-- WP ID: 743 / slug: imp-quo-561-743 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-561-743';

-- WP ID: 744 / slug: imp-quo-562-744 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-562-744';

-- WP ID: 745 / slug: imp-quo-563-745 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-563-745';

-- WP ID: 746 / slug: imp-quo-564-746 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-564-746';

-- WP ID: 747 / slug: imp-quo-565-747 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-565-747';

-- WP ID: 748 / slug: imp-quo-566-748 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-566-748';

-- WP ID: 750 / slug: imp-quo-567-750 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 310 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-567-750';

-- WP ID: 751 / slug: imp-quo-568-751 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -330 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-568-751';

-- WP ID: 752 / slug: imp-quo-569-752 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 299 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-569-752';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-569-752';

-- WP ID: 755 / slug: imp-quo-570-755 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -330 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Entrepaño extra en perfumero + Repizas Zapateras (3)', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-570-755';

-- WP ID: 756 / slug: imp-quo-571-756 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-571-756';

-- WP ID: 759 / slug: imp-quo-572-759 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-572-759';

-- WP ID: 761 / slug: imp-quo-573-761 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 315 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-573-761';

-- WP ID: 762 / slug: imp-quo-574-762 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-574-762';

-- WP ID: 764 / slug: imp-quo-576-764 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-576-764';

-- WP ID: 767 / slug: imp-quo-577-767 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 252 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-577-767';

-- WP ID: 768 / slug: imp-quo-578-768 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-578-768';

-- WP ID: 769 / slug: imp-quo-579-769 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-579-769';

-- WP ID: 770 / slug: imp-quo-580-770 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR CON PUERTAS 130 X 270 X 130', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 4 CAJONES CU. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 45000, 45000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-580-770';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MODULO RETRACTIL (2)', 'DOS MODULOS TIPO ESPECIERO RETRACTIL (ACOMODO VERTICAL)', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-580-770';

-- WP ID: 771 / slug: imp-quo-581-771 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-581-771';

-- WP ID: 772 / slug: imp-quo-582-772 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-582-772';

-- WP ID: 773 / slug: imp-quo-583-773 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-583-773';

-- WP ID: 775 / slug: imp-quo-584-775 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 191 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-584-775';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 173 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-584-775';

-- WP ID: 776 / slug: imp-quo-585-776 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 283 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-585-776';

-- WP ID: 777 / slug: imp-quo-586-777 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 120 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-586-777';

-- WP ID: 778 / slug: imp-quo-587-778 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN L 235 X 270 - SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Torre zapatera con puerta', NULL, 1, 35, 35
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-587-778';

-- WP ID: 780 / slug: imp-quo-589-780 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 230 EN PARALELO', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) HASTA 2 TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29900, 29900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-589-780';

-- WP ID: 784 / slug: imp-quo-590-784 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 177 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-590-784';

-- WP ID: 787 / slug: imp-quo-591-787 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vertical Extra', NULL, 1, 21900, 21900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-591-787';

-- WP ID: 789 / slug: imp-quo-593-789 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-593-789';

-- WP ID: 791 / slug: imp-quo-594-791 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR CON PUERTAS EN MALETERO - 232 x 250', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + PUERTAS EN MALETEROS', NULL, 1, 49500, 49500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-594-791';

-- WP ID: 792 / slug: imp-quo-595-792 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 208 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-595-792';

-- WP ID: 793 / slug: imp-quo-596-793 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN PARALELO - 300 CMS Y 300 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE ZAPATERA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38500, 38500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-596-793';

-- WP ID: 794 / slug: imp-quo-597-794 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 217 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-597-794';

-- WP ID: 795 / slug: imp-quo-598-795 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 147 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13800, 13800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-598-795';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 256 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-598-795';

-- WP ID: 797 / slug: imp-quo-599-797 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 226 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-599-797';

-- WP ID: 798 / slug: imp-quo-600-798 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-600-798';

-- WP ID: 799 / slug: imp-quo-601-799 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-601-799';

-- WP ID: 800 / slug: imp-quo-602-800 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN L - 174 X 153 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-602-800';

-- WP ID: 801 / slug: imp-quo-603-801 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, '1. CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U 441 X 223', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE ZAPATERA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + TORRE CAJONERA EXTRA ($3,500) + TORRE DE CHAROLAS (8) RETRACTILES ($8,500)', NULL, 1, 60000, 60000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-603-801';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, '2. CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN L 292 X 312', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + TORRE de charolas (6) extraibles ($6,500)', NULL, 1, 43500, 43500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-603-801';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, '3. CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U 397 X 270', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE ZAPATERA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + TORRE CAJONERA EXTRA ($3,500) + TORRE DE CHAROLAS (8) RETRACTILES ($8,500)', NULL, 1, 77000, 77000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-603-801';

-- WP ID: 802 / slug: imp-quo-604-802 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-604-802';

-- WP ID: 803 / slug: imp-quo-605-803 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN L + PUERTAS EN MALETERO', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. +TORRE ZAPATERA INDEPENDIENTE (+$4,500)', NULL, 1, 53500, 53500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-605-803';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR EN L SIN PUERTAS EN MALETERO', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. +TORRE ZAPATERA INDEPENDIENTE (+$4,500)', NULL, 1, 43000, 43000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-605-803';

-- WP ID: 804 / slug: imp-quo-606-804 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET TRIPLAY DE CEDRO STANDARD - 178 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-606-804';

-- WP ID: 805 / slug: imp-quo-607-805 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-607-805';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-607-805';

-- WP ID: 806 / slug: imp-quo-608-806 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-608-806';

-- WP ID: 807 / slug: imp-quo-609-807 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 2 Torres con 3 cajones de cada lado + 1 Repisa', NULL, 1, 36500, 36500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-609-807';

-- WP ID: 808 / slug: imp-quo-610-808 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-610-808';

-- WP ID: 809 / slug: imp-quo-611-809 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-611-809';

-- WP ID: 811 / slug: imp-quo-612-811 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 247 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura segun diseño (310 cms) .✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vista superior a medida + Bisagras Cierre Lento (8)', NULL, 1, 25800, 25800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-612-811';

-- WP ID: 812 / slug: imp-quo-613-812 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 168 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-613-812';

-- WP ID: 813 / slug: imp-quo-614-813 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 270 x 178', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-614-813';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR CON PUERTAS - 270 X 178', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE ZAPATERA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 41000, 41000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-614-813';

-- WP ID: 815 / slug: imp-quo-615-815 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 400 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vanity o Tocador + Nicho de TV + Cajones + Torre Zapatera', NULL, 1, 55500, 55500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-615-815';

-- WP ID: 816 / slug: imp-quo-616-816 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L - 185 X 160', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-616-816';

-- WP ID: 817 / slug: imp-quo-617-817 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Torre Zapatera ✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30500, 30500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-617-817';

-- WP ID: 818 / slug: imp-quo-618-818 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-618-818';

-- WP ID: 819 / slug: imp-quo-619-819 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 167 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-619-819';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 167 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-619-819';

-- WP ID: 820 / slug: imp-quo-620-820 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-620-820';

-- WP ID: 822 / slug: imp-quo-621-822 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-621-822';

-- WP ID: 823 / slug: imp-quo-622-823 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 192 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-622-823';

-- WP ID: 824 / slug: imp-quo-623-824 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - en L 200 x 400 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones Y Torre De Hasta 5 Repisas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 47000, 47000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-623-824';

-- WP ID: 825 / slug: imp-quo-624-825 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 276 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-624-825';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 171 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-624-825';

-- WP ID: 826 / slug: imp-quo-625-826 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 174 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-625-826';

-- WP ID: 829 / slug: imp-quo-626-829 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-626-829';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 174 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-626-829';

-- WP ID: 830 / slug: imp-quo-627-830 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-627-830';

-- WP ID: 832 / slug: imp-quo-628-832 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-628-832';

-- WP ID: 833 / slug: imp-quo-629-833 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 324 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Torre Zapatera Con Puerta + Escritorio + Nicho Decorativo + Lambrin', NULL, 1, 44000, 44000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-629-833';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS LINEAL - 250 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 REPISAS. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + MODULO DE 6 CAJONES', NULL, 1, 25700, 25700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-629-833';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MUEBLE DE BAÑO SEGUN DISEÑO - 147 CMS', 'NO INCLUYE CUBIERTA - PUERTAS, HERRAJES, JALADERAS A ELEGIR', NULL, 1, 8350, 8350
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-629-833';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'MUEBLE DE BAÑO SEGUN DISEÑO - 109 CMS', 'NO INCLUYE CUBIERTA - PUERTAS, HERRAJES, JALADERAS A ELEGIR', NULL, 1, 6200, 6200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-629-833';

-- WP ID: 834 / slug: imp-quo-630-834 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -161 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-630-834';

-- WP ID: 836 / slug: imp-quo-631-836 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 187 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-631-836';

-- WP ID: 837 / slug: imp-quo-632-837 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 207 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 2 Torres Con Entrepaños', NULL, 1, 27300, 27300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-632-837';

-- WP ID: 838 / slug: imp-quo-633-838 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 140 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13800, 13800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-633-838';

-- WP ID: 841 / slug: imp-quo-634-841 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-634-841';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 175CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-634-841';

-- WP ID: 843 / slug: imp-quo-635-843 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-635-843';

-- WP ID: 845 / slug: imp-quo-636-845 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-636-845';

-- WP ID: 847 / slug: imp-quo-637-847 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 195 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-637-847';

-- WP ID: 848 / slug: imp-quo-638-848 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN L - 178 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Torre de Entrepaños', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-638-848';

-- WP ID: 849 / slug: imp-quo-639-849 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-639-849';

-- WP ID: 850 / slug: imp-quo-640-850 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-640-850';

-- WP ID: 851 / slug: imp-quo-641-851 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-641-851';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA SIN PUERTAS SEGUN DISEÑO - 280 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32500, 32500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-641-851';

-- WP ID: 853 / slug: imp-quo-642-853 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -239 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-642-853';

-- WP ID: 854 / slug: imp-quo-643-854 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-643-854';

-- WP ID: 855 / slug: imp-quo-644-855 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-644-855';

-- WP ID: 856 / slug: imp-quo-645-856 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA + NICHO TV 350CMS', 'FRENTES, PUERTAS Y DOS TORRES CON HASTA 5 CAJONES + NICHO TV. BASE, ZOCLO Y MALETERO (NO INCLUYE FORRADO DE FONDOS EN PAREDES) KIT DE HERRAJES, RIELES DE EXTENSION 18\" Y BISAGRAS REFORZADAS DE CIERRE LENTO, JALADERAS DE NUESTRO CATALOGO, TUBOS Y SOPORTES. PROFUNDIDAD STANDARD 60 CMS Y ANCHO MAXIMO DE CADA MODULO 60 CMS. ELEMENTOS ADICIONALES, TORRES, REPISAS, DIVISORES, SE COTIZAN POR SEPARADO. CAMBIOS Y AJUSTES ALTERAN EL PRECIO COTIZADO. + ENTREPAÑOS (2)', NULL, 1, 40500, 40500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-645-856';

-- WP ID: 857 / slug: imp-quo-646-857 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 282 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-646-857';

-- WP ID: 859 / slug: imp-quo-647-859 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 246 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-647-859';

-- WP ID: 860 / slug: imp-quo-216-860 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 298 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 2 Repisas Zapateras', NULL, 1, 27200, 27200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-216-860';

-- WP ID: 861 / slug: imp-quo-649-861 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 229 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-649-861';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-649-861';

-- WP ID: 862 / slug: imp-quo-650-862 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 244 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22100, 22100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-650-862';

-- WP ID: 864 / slug: imp-quo-651-864 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR - 240 x 220 x 315 CMS', 'FRENTES, PUERTAS EN MALETERO DOS TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 59500, 59500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-651-864';

-- WP ID: 865 / slug: imp-quo-652-865 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 155 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-652-865';

-- WP ID: 866 / slug: imp-quo-653-866 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 262 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-653-866';

-- WP ID: 867 / slug: imp-quo-654-867 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 260 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-654-867';

-- WP ID: 869 / slug: imp-quo-655-869 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-655-869';

-- WP ID: 870 / slug: imp-quo-656-870 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS en L - 229 x 227', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-656-870';

-- WP ID: 871 / slug: imp-quo-657-871 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 187 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-657-871';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 90 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-657-871';

-- WP ID: 872 / slug: imp-quo-658-872 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-658-872';

-- WP ID: 873 / slug: imp-quo-659-873 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 193 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-659-873';

-- WP ID: 874 / slug: imp-quo-660-874 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-660-874';

-- WP ID: 876 / slug: imp-quo-661-876 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-661-876';

-- WP ID: 879 / slug: imp-quo-662-879 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-662-879';

-- WP ID: 880 / slug: imp-quo-663-880 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-663-880';

-- WP ID: 881 / slug: imp-quo-664-881 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-664-881';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-664-881';

-- WP ID: 882 / slug: imp-quo-665-882 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 380 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32500, 32500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-665-882';

-- WP ID: 883 / slug: imp-quo-666-883 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-666-883';

-- WP ID: 884 / slug: imp-quo-667-884 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-667-884';

-- WP ID: 885 / slug: imp-quo-668-885 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-668-885';

-- WP ID: 886 / slug: imp-quo-669-886 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-669-886';

-- WP ID: 887 / slug: imp-quo-670-887 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-670-887';

-- WP ID: 888 / slug: imp-quo-671-888 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-671-888';

-- WP ID: 889 / slug: imp-quo-672-889 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-672-889';

-- WP ID: 890 / slug: imp-quo-673-890 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 332 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-673-890';

-- WP ID: 891 / slug: imp-quo-674-891 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 187 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-674-891';

-- WP ID: 892 / slug: imp-quo-675-892 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 153 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. +TORRE ZAPATERA FIJA (8)', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-675-892';

-- WP ID: 893 / slug: imp-quo-676-893 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 264 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-676-893';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 215 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-676-893';

-- WP ID: 894 / slug: imp-quo-677-894 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + TOCADOR O VANITY', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-677-894';

-- WP ID: 895 / slug: imp-quo-678-895 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-678-895';

-- WP ID: 896 / slug: imp-quo-679-896 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-679-896';

-- WP ID: 897 / slug: imp-quo-680-897 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 173 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-680-897';

-- WP ID: 898 / slug: imp-quo-681-898 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 134 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-681-898';

-- WP ID: 899 / slug: imp-quo-682-899 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 295 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-682-899';

-- WP ID: 900 / slug: imp-quo-683-900 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 245 CMS + 149 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-683-900';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 315 CMS + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-683-900';

-- WP ID: 901 / slug: imp-quo-684-901 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 125 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-684-901';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 125 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-684-901';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 125 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-684-901';

-- WP ID: 902 / slug: imp-quo-685-902 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 125 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-685-902';

-- WP ID: 903 / slug: imp-quo-686-903 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN U - 300 x 238 x 402 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 71400, 71400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-686-903';

-- WP ID: 904 / slug: imp-quo-687-904 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16560, 16560
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-687-904';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15640, 15640
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-687-904';

-- WP ID: 906 / slug: imp-quo-688-906 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 217 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 1 Torre Extra de Hasta 5 Cajones', NULL, 1, 24200, 24200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-688-906';

-- WP ID: 907 / slug: imp-quo-689-907 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 184 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-689-907';

-- WP ID: 908 / slug: imp-quo-690-908 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 290 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-690-908';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 290 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-690-908';

-- WP ID: 909 / slug: imp-quo-691-909 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L - 200 X 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Torre Zapatera', NULL, 1, 29600, 29600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-691-909';

-- WP ID: 910 / slug: imp-quo-692-910 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-692-910';

-- WP ID: 911 / slug: imp-quo-693-911 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 350 CMS SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-693-911';

-- WP ID: 912 / slug: imp-quo-694-912 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 350 CMS SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 33200, 33200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-694-912';

-- WP ID: 913 / slug: imp-quo-695-913 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 130 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-695-913';

-- WP ID: 914 / slug: imp-quo-696-914 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-696-914';

-- WP ID: 915 / slug: imp-quo-697-915 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 475 CMS SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Nicho de TV al Centro', NULL, 1, 45100, 45100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-697-915';

-- WP ID: 916 / slug: imp-quo-698-916 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-698-916';

-- WP ID: 917 / slug: imp-quo-699-917 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 173 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-699-917';

-- WP ID: 918 / slug: imp-quo-700-918 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-700-918';

-- WP ID: 921 / slug: imp-quo-701-921 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-701-921';

-- WP ID: 922 / slug: imp-quo-702-922 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + VANITY - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vanity Con 2 Cajones', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-702-922';

-- WP ID: 923 / slug: imp-quo-703-923 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 213 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-703-923';

-- WP ID: 924 / slug: imp-quo-704-924 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-704-924';

-- WP ID: 925 / slug: imp-quo-705-925 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SIN MALETERO - 263 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 210 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-705-925';

-- WP ID: 926 / slug: imp-quo-706-926 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN U 200 X 300 X 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 43000, 43000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-706-926';

-- WP ID: 933 / slug: imp-quo-243-933 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 298 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27200, 27200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-243-933';

-- WP ID: 939 / slug: imp-quo-708-939 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-708-939';

-- WP ID: 943 / slug: imp-quo-709-943 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 303 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-709-943';

-- WP ID: 944 / slug: imp-quo-710-944 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + VANITY - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-710-944';

-- WP ID: 945 / slug: imp-quo-711-945 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 305 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms c/u ✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31500, 31500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-711-945';

-- WP ID: 946 / slug: imp-quo-712-946 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 348 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29900, 29900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-712-946';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'EXTRAS SEGUN DISEÑO', '+ TORRE DE 5 CAJONES ($4,500) + TORRE ZAPATERA ($3,500) + KIT DE ILUMINACIÓN LED EN TORRE ZAPATERA ($4,500)', NULL, 1, 12500, 12500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-712-946';

-- WP ID: 947 / slug: imp-quo-713-947 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-713-947';

-- WP ID: 949 / slug: imp-quo-714-949 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - EN L 235 X 270', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35500, 35500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-714-949';

-- WP ID: 950 / slug: imp-quo-715-950 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 188 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-715-950';

-- WP ID: 951 / slug: imp-quo-716-951 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-716-951';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21300, 21300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-716-951';

-- WP ID: 952 / slug: imp-quo-717-952 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-717-952';

-- WP ID: 953 / slug: imp-quo-718-953 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas CIERRE LENTO, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.]', NULL, 1, 15600, 15600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-718-953';

-- WP ID: 954 / slug: imp-quo-719-954 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 174 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-719-954';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 174 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-719-954';

-- WP ID: 955 / slug: imp-quo-720-955 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-720-955';

-- WP ID: 956 / slug: imp-quo-721-956 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 212 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19600, 19600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-721-956';

-- WP ID: 957 / slug: imp-quo-722-957 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-722-957';

-- WP ID: 958 / slug: imp-quo-723-958 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 183 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-723-958';

-- WP ID: 960 / slug: imp-quo-256-960 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 247 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vista superior a medida + Bisagras Cierre Lento (8)', NULL, 1, 25800, 25800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-256-960';

-- WP ID: 965 / slug: imp-quo-725-965 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 247 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25800, 25800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-725-965';

-- WP ID: 968 / slug: imp-quo-726-968 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-726-968';

-- WP ID: 969 / slug: imp-quo-727-969 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L - 200 x 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25700, 25700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-727-969';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-727-969';

-- WP ID: 971 / slug: imp-quo-728-971 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-728-971';

-- WP ID: 972 / slug: imp-quo-729-972 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 193 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-729-972';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-729-972';

-- WP ID: 973 / slug: imp-quo-730-973 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 278 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-730-973';

-- WP ID: 974 / slug: imp-quo-731-974 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-731-974';

-- WP ID: 976 / slug: imp-quo-732-976 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-732-976';

-- WP ID: 977 / slug: imp-quo-733-977 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 382 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31100, 31100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-733-977';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN U - 249 x 199 x 249 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 56900, 56900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-733-977';

-- WP ID: 979 / slug: imp-quo-734-979 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 194 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-734-979';

-- WP ID: 980 / slug: imp-quo-735-980 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-735-980';

-- WP ID: 981 / slug: imp-quo-736-981 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 265 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-736-981';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 215 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20100, 20100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-736-981';

-- WP ID: 982 / slug: imp-quo-737-982 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-737-982';

-- WP ID: 983 / slug: imp-quo-738-983 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 238 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-738-983';

-- WP ID: 984 / slug: imp-quo-739-984 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-739-984';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 330 CMS SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-739-984';

-- WP ID: 986 / slug: imp-quo-740-986 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 275 x 156', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39200, 39200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-740-986';

-- WP ID: 987 / slug: imp-quo-741-987 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-741-987';

-- WP ID: 988 / slug: imp-quo-742-988 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-742-988';

-- WP ID: 989 / slug: imp-quo-743-989 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 215 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 3 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-743-989';

-- WP ID: 990 / slug: imp-quo-744-990 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-744-990';

-- WP ID: 992 / slug: imp-quo-745-992 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 177 CMS - ALEXIS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 4 Repisas Zapateras + 1 Repisa Extra en Perfumero', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-745-992';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VESTIDOR MELAMINA EN PARALELO - 220 CMS - KEILA', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES + UNA TORRE DE CON ENTREPAÑO, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. PUERTAS EN MALETERO ($16,000.00) TORRE EXTRA DE CAJONES ($4,500.00)', NULL, 1, 48000, 48000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-745-992';

-- WP ID: 994 / slug: imp-quo-746-994 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-746-994';

-- WP ID: 995 / slug: imp-quo-747-995 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 308 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-747-995';

-- WP ID: 996 / slug: imp-quo-748-996 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-748-996';

-- WP ID: 997 / slug: imp-quo-749-997 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 397 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 34200, 34200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-749-997';

-- WP ID: 998 / slug: imp-quo-750-998 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-750-998';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 154 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-750-998';

-- WP ID: 999 / slug: imp-quo-751-999 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 350 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29900, 29900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-751-999';

-- WP ID: 1000 / slug: imp-quo-752-1000 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-752-1000';

-- WP ID: 1001 / slug: imp-quo-753-1001 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 188 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-753-1001';

-- WP ID: 1002 / slug: imp-quo-754-1002 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN LINEA - 200 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES , PUERTA COSMETIQUERA Y REPISA INTERIOR. + UNA TORRE CON ENTREPAÑOSKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-754-1002';

-- WP ID: 1004 / slug: imp-quo-755-1004 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-755-1004';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-755-1004';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-755-1004';

-- WP ID: 1005 / slug: imp-quo-756-1005 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-756-1005';

-- WP ID: 1006 / slug: imp-quo-757-1006 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L - 115 X 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-757-1006';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-757-1006';

-- WP ID: 1008 / slug: imp-quo-758-1008 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-758-1008';

-- WP ID: 1009 / slug: imp-quo-759-1009 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 164 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-759-1009';

-- WP ID: 1011 / slug: imp-quo-760-1011 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN L 230 X 220', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44500, 44500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-760-1011';

-- WP ID: 1012 / slug: imp-quo-761-1012 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 172 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16100, 16100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-761-1012';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE A MEDIDA - 107 CMS', 'MUEBLE DE BAÑO - PARA LAVAMANOS', NULL, 1, 5900, 5900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-761-1012';

-- WP ID: 1013 / slug: imp-quo-762-1013 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 195 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-762-1013';

-- WP ID: 1014 / slug: imp-quo-763-1014 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-763-1014';

-- WP ID: 1015 / slug: imp-quo-764-1015 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -239 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-764-1015';

-- WP ID: 1017 / slug: imp-quo-765-1017 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres de 5 repisas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31000, 31000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-765-1017';

-- WP ID: 1018 / slug: imp-quo-766-1018 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-766-1018';

-- WP ID: 1019 / slug: imp-quo-767-1019 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 335 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-767-1019';

-- WP ID: 1020 / slug: imp-quo-768-1020 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 164 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-768-1020';

-- WP ID: 1021 / slug: imp-quo-769-1021 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD SEGUN DISEÑO - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32200, 32200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-769-1021';

-- WP ID: 1022 / slug: imp-quo-770-1022 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN L 230 X 220', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44500, 44500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-770-1022';

-- WP ID: 1023 / slug: imp-quo-771-1023 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 145 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13800, 13800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-771-1023';

-- WP ID: 1024 / slug: imp-quo-772-1024 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-772-1024';

-- WP ID: 1025 / slug: imp-quo-773-1025 (5 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA REC 2 - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 330 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-773-1025';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 3 - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 330 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-773-1025';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD REC VISITA - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 330 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-773-1025';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA WALK IN VESTIDOR EN L REC PRINCIPAL- 270 CMS X 285 CMS', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 55250, 55250
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-773-1025';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'CLOSET MELAMINA WALK IN VESTIDOR EN L REC 5 - 270 CMS X 285 CMS', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 55250, 55250
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-773-1025';

-- WP ID: 1026 / slug: imp-quo-774-1026 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 218 CMS + 95 CMS MALETERO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-774-1026';

-- WP ID: 1027 / slug: imp-quo-775-1027 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-775-1027';

-- WP ID: 1028 / slug: imp-quo-776-1028 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 380 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32800, 32800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-776-1028';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 239 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-776-1028';

-- WP ID: 1029 / slug: imp-quo-777-1029 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-777-1029';

-- WP ID: 1030 / slug: imp-quo-778-1030 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones + Torre zapatera ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-778-1030';

-- WP ID: 1031 / slug: imp-quo-779-1031 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-779-1031';

-- WP ID: 1032 / slug: imp-quo-780-1032 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-780-1032';

-- WP ID: 1033 / slug: imp-quo-781-1033 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 275 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-781-1033';

-- WP ID: 1034 / slug: imp-quo-782-1034 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 246 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-782-1034';

-- WP ID: 1035 / slug: imp-quo-783-1035 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 330 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-783-1035';

-- WP ID: 1039 / slug: imp-quo-784-1039 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-784-1039';

-- WP ID: 1040 / slug: imp-quo-785-1040 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-785-1040';

-- WP ID: 1041 / slug: imp-quo-786-1041 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS LINEAL - 220 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-786-1041';

-- WP ID: 1042 / slug: imp-quo-787-1042 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-787-1042';

-- WP ID: 1043 / slug: imp-quo-788-1043 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Repisa extra en perfumero ($250)', NULL, 1, 15250, 15250
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-788-1043';

-- WP ID: 1045 / slug: imp-quo-789-1045 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 276 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 290 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-789-1045';

-- WP ID: 1046 / slug: imp-quo-790-1046 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-790-1046';

-- WP ID: 1047 / slug: imp-quo-791-1047 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 235 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-791-1047';

-- WP ID: 1048 / slug: imp-quo-792-1048 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -. 199 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-792-1048';

-- WP ID: 1049 / slug: imp-quo-793-1049 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-793-1049';

-- WP ID: 1051 / slug: imp-quo-794-1051 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD- 145 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13800, 13800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-794-1051';

-- WP ID: 1052 / slug: imp-quo-795-1052 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 206 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18600, 18600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-795-1052';

-- WP ID: 1053 / slug: imp-quo-796-1053 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS + VANITY 360 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + VANITY O TOCADOR CON DOS CAJONES ($5,000)', NULL, 1, 32800, 32800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-796-1053';

-- WP ID: 1054 / slug: imp-quo-797-1054 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 171 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-797-1054';

-- WP ID: 1055 / slug: imp-quo-798-1055 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 130 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-798-1055';

-- WP ID: 1056 / slug: imp-quo-799-1056 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 130 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Modulo de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 2, 14250, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-799-1056';

-- WP ID: 1057 / slug: imp-quo-800-1057 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-800-1057';

-- WP ID: 1059 / slug: imp-quo-801-1059 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 222 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-801-1059';

-- WP ID: 1060 / slug: imp-quo-802-1060 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 188 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-802-1060';

-- WP ID: 1061 / slug: imp-quo-803-1061 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 100 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-803-1061';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-803-1061';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19600, 19600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-803-1061';

-- WP ID: 1062 / slug: imp-quo-804-1062 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-804-1062';

-- WP ID: 1064 / slug: imp-quo-805-1064 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE DE REPISAS FIJAS (5) SIN PUERTA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-805-1064';

-- WP ID: 1065 / slug: imp-quo-806-1065 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-806-1065';

-- WP ID: 1066 / slug: imp-quo-807-1066 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-807-1066';

-- WP ID: 1068 / slug: imp-quo-808-1068 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 179 x 211 x 181 + 211 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. + UNA TORRE DE REPISAS FIJAS (5) SIN PUERTA KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 50800, 50800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-808-1068';

-- WP ID: 1070 / slug: imp-quo-809-1070 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 245 CMS', 'FRENTES, PUERTAS Y UNA TORRE CON HASTA 3 CAJONES, NICHOS. BASE, ZOCLO Y MALETERO (NO INCLUYE FORRADO DE FONDOS EN PAREDES) KIT DE HERRAJES, RIELES DE EXTENSION 18\" Y BISAGRAS REFORZADAS, JALADERAS DE NUESTRO CATALOGO, TUBOS Y SOPORTES. PROFUNDIDAD STANDARD 60 CMS Y ANCHO MAXIMO DE CADA MODULO 60 CMS. ELEMENTOS ADICIONALES, TORRES, REPISAS, DIVISORES, SE COTIZAN POR SEPARADO. CAMBIOS Y AJUSTES ALTERAN EL PRECIO COTIZADO.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-809-1070';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 272 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones + Torre de repisas fijas (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 1 Repisa en area de tubo ($350)', NULL, 1, 27850, 27850
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-809-1070';

-- WP ID: 1072 / slug: imp-quo-810-1072 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD- 194 CMS + CAJON EXTRA + 1 PUERTA CADA LADO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18350, 18350
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-810-1072';

-- WP ID: 1077 / slug: imp-quo-302-1077 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 245 CMS', 'FRENTES, PUERTAS Y UNA TORRE CON HASTA 3 CAJONES, NICHOS. BASE, ZOCLO Y MALETERO (NO INCLUYE FORRADO DE FONDOS EN PAREDES) KIT DE HERRAJES, RIELES DE EXTENSION 18\" Y BISAGRAS REFORZADAS, JALADERAS DE NUESTRO CATALOGO, TUBOS Y SOPORTES. PROFUNDIDAD STANDARD 60 CMS Y ANCHO MAXIMO DE CADA MODULO 60 CMS. ELEMENTOS ADICIONALES, TORRES, REPISAS, DIVISORES, SE COTIZAN POR SEPARADO. CAMBIOS Y AJUSTES ALTERAN EL PRECIO COTIZADO.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-302-1077';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 272 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye: ✅Closet Empotrado ✅Torre Cajonera de 5 cajones + Torre de repisas fijas (5) ancho máximo 60 cms ✅Puertas Principales ancho máximo 60 cms. ✅Puertas en Maletero ancho máximo 60 cms. ✅Closet Profundidad Standard 62 cms máximo. ✅Altura máxima 270 cms. ✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo. ✅Base y Zoclo (no se ve el piso). ✅No incluye forro de muros. + 1 Repisa en area de tubo ($350)', NULL, 1, 27850, 27850
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-302-1077';

-- WP ID: 1095 / slug: imp-quo-813-1095 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN L 180 X 200 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 24400, 24400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-813-1095';

-- WP ID: 1096 / slug: imp-quo-814-1096 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-814-1096';

-- WP ID: 1097 / slug: imp-quo-815-1097 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 244 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-815-1097';

-- WP ID: 1098 / slug: imp-quo-816-1098 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 298 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-816-1098';

-- WP ID: 1099 / slug: imp-quo-817-1099 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-817-1099';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 185 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-817-1099';

-- WP ID: 1100 / slug: imp-quo-818-1100 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 294 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-818-1100';

-- WP ID: 1101 / slug: imp-quo-819-1101 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 290 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-819-1101';

-- WP ID: 1102 / slug: imp-quo-820-1102 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-820-1102';

-- WP ID: 1103 / slug: imp-quo-821-1103 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-821-1103';

-- WP ID: 1105 / slug: imp-quo-822-1105 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-822-1105';

-- WP ID: 1106 / slug: imp-quo-823-1106 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-823-1106';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-823-1106';

-- WP ID: 1107 / slug: imp-quo-824-1107 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-824-1107';

-- WP ID: 1108 / slug: imp-quo-825-1108 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 126 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-825-1108';

-- WP ID: 1109 / slug: imp-quo-826-1109 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-826-1109';

-- WP ID: 1110 / slug: imp-quo-827-1110 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 430 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 37600, 37600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-827-1110';

-- WP ID: 1111 / slug: imp-quo-828-1111 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 195 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-828-1111';

-- WP ID: 1112 / slug: imp-quo-829-1112 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44600, 44600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-829-1112';

-- WP ID: 1114 / slug: imp-quo-830-1114 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-830-1114';

-- WP ID: 1115 / slug: imp-quo-831-1115 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 278 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-831-1115';

-- WP ID: 1116 / slug: imp-quo-832-1116 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-832-1116';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L 214 X 163 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28600, 28600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-832-1116';

-- WP ID: 1117 / slug: imp-quo-833-1117 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16400, 16400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-833-1117';

-- WP ID: 1119 / slug: imp-quo-834-1119 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 288 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-834-1119';

-- WP ID: 1121 / slug: imp-quo-835-1121 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-835-1121';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE DE ENTREPAÑOS EXTRA CON PUERTA', '', NULL, 1, 3500, 3500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-835-1121';

-- WP ID: 1122 / slug: imp-quo-836-1122 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-836-1122';

-- WP ID: 1123 / slug: imp-quo-837-1123 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21400, 21400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-837-1123';

-- WP ID: 1126 / slug: imp-quo-838-1126 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-838-1126';

-- WP ID: 1127 / slug: imp-quo-839-1127 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-839-1127';

-- WP ID: 1129 / slug: imp-quo-840-1129 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 275 x 352 x 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 60000, 60000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-840-1129';

-- WP ID: 1130 / slug: imp-quo-841-1130 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 232 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21600, 21600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-841-1130';

-- WP ID: 1131 / slug: imp-quo-842-1131 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 181 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-842-1131';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 181 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-842-1131';

-- WP ID: 1132 / slug: imp-quo-843-1132 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 4 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅3 Entrepaños en total', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-843-1132';

-- WP ID: 1133 / slug: imp-quo-844-1133 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 256 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23400, 23400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-844-1133';

-- WP ID: 1134 / slug: imp-quo-845-1134 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15400, 15400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-845-1134';

-- WP ID: 1135 / slug: imp-quo-846-1135 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ 1 Repiza Zapatera ($400.00)', NULL, 1, 16400, 16400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-846-1135';

-- WP ID: 1137 / slug: imp-quo-847-1137 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14600, 14600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-847-1137';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 184 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-847-1137';

-- WP ID: 1138 / slug: imp-quo-848-1138 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18200, 18200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-848-1138';

-- WP ID: 1139 / slug: imp-quo-849-1139 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 268 CMS+ VANITY CON PLAFON Y 1 SPOT', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-849-1139';

-- WP ID: 1140 / slug: imp-quo-850-1140 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22400, 22400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-850-1140';

-- WP ID: 1141 / slug: imp-quo-851-1141 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 140 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14800, 14800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-851-1141';

-- WP ID: 1142 / slug: imp-quo-852-1142 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-852-1142';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-852-1142';

-- WP ID: 1143 / slug: imp-quo-853-1143 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-853-1143';

-- WP ID: 1144 / slug: imp-quo-854-1144 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 245 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22400, 22400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-854-1144';

-- WP ID: 1145 / slug: imp-quo-855-1145 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-855-1145';

-- WP ID: 1147 / slug: imp-quo-856-1147 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR CON PUERTAS EN MALETERO - 327 x 190', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44600, 44600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-856-1147';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN MALETERO - - 327 x 190', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-856-1147';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 162 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-856-1147';

-- WP ID: 1148 / slug: imp-quo-857-1148 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 164 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-857-1148';

-- WP ID: 1149 / slug: imp-quo-858-1149 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN L - 260 X 160 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-858-1149';

-- WP ID: 1150 / slug: imp-quo-859-1150 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 390 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 40000, 40000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-859-1150';

-- WP ID: 1151 / slug: imp-quo-860-1151 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-860-1151';

-- WP ID: 1152 / slug: imp-quo-861-1152 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN PARALELO - 450 CMS C/PARED', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 74000, 74000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-861-1152';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN PARALELO - 450 CMS C/PARED', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE DE ENTREPAÑOS (5) KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 62000, 62000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-861-1152';

-- WP ID: 1153 / slug: imp-quo-862-1153 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17200, 17200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-862-1153';

-- WP ID: 1154 / slug: imp-quo-863-1154 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 267 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-863-1154';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE DE ENTREPAÑOS EXTRA CON PUERTA', 'MAXIMO 60 CMS DE ANCHO', NULL, 1, 4400, 4400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-863-1154';

-- WP ID: 1156 / slug: imp-quo-864-1156 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-864-1156';

-- WP ID: 1157 / slug: imp-quo-865-1157 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO CON NICHO DE TV - 262 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅ 2 Torres de 5 Entrepaños ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-865-1157';

-- WP ID: 1159 / slug: imp-quo-866-1159 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 225 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-866-1159';

-- WP ID: 1160 / slug: imp-quo-867-1160 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 295 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26400, 26400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-867-1160';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA SEGUN DISEÑO - 295 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅Area de escritorio o vanity con cajones (2)', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-867-1160';

-- WP ID: 1161 / slug: imp-quo-868-1161 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre de entrepaños (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas Cierre Lento, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19600, 19600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-868-1161';

-- WP ID: 1162 / slug: imp-quo-869-1162 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-869-1162';

-- WP ID: 1165 / slug: imp-quo-871-1165 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 148 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-871-1165';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 149 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-871-1165';

-- WP ID: 1166 / slug: imp-quo-872-1166 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-872-1166';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L - 100 X 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-872-1166';

-- WP ID: 1167 / slug: imp-quo-873-1167 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20400, 20400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-873-1167';

-- WP ID: 1168 / slug: imp-quo-874-1168 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 330 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-874-1168';

-- WP ID: 1170 / slug: imp-quo-875-1170 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 193 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18600, 18600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-875-1170';

-- WP ID: 1171 / slug: imp-quo-876-1171 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 194 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18200, 18200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-876-1171';

-- WP ID: 1172 / slug: imp-quo-877-1172 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-877-1172';

-- WP ID: 1173 / slug: imp-quo-878-1173 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 338 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29600, 29600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-878-1173';

-- WP ID: 1174 / slug: imp-quo-879-1174 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20800, 20800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-879-1174';

-- WP ID: 1175 / slug: imp-quo-880-1175 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-880-1175';

-- WP ID: 1177 / slug: imp-quo-881-1177 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-881-1177';

-- WP ID: 1178 / slug: imp-quo-882-1178 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 3 cajones c/u ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Nicho de TV de 55 Pulgadas✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30200, 30200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-882-1178';

-- WP ID: 1179 / slug: imp-quo-883-1179 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 132 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-883-1179';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VANITY', 'VANITY CON HASTA 2 CAJONES', NULL, 1, 5000, 5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-883-1179';

-- WP ID: 1180 / slug: imp-quo-884-1180 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -244 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅+ 2 Repizas Zapateras ✅No incluye forro de muros.', NULL, 1, 22800, 22800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-884-1180';

-- WP ID: 1181 / slug: imp-quo-885-1181 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-885-1181';

-- WP ID: 1182 / slug: imp-quo-886-1182 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18400, 18400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-886-1182';

-- WP ID: 1183 / slug: imp-quo-887-1183 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-887-1183';

-- WP ID: 1184 / slug: imp-quo-888-1184 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17200, 17200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-888-1184';

-- WP ID: 1185 / slug: imp-quo-889-1185 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 100 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-889-1185';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 100 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-889-1185';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-889-1185';

-- WP ID: 1186 / slug: imp-quo-890-1186 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-890-1186';

-- WP ID: 1187 / slug: imp-quo-891-1187 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 210 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + 2 CAJONES EXTRAS', NULL, 1, 17800, 17800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-891-1187';

-- WP ID: 1189 / slug: imp-quo-892-1189 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-892-1189';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 500 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 43000, 43000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-892-1189';

-- WP ID: 1192 / slug: imp-quo-893-1192 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 271 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Modulo Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ 2 Torres Cajoneras con Puerta de Ancho Maximo 60 cms ($8,000.00).', NULL, 1, 33000, 33000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-893-1192';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 177 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-893-1192';

-- WP ID: 1195 / slug: imp-quo-894-1195 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 213 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19700, 19700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-894-1195';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 204 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18600, 18600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-894-1195';

-- WP ID: 1196 / slug: imp-quo-895-1196 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 120 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14600, 14600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-895-1196';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MALETERO ARRIBA DE PUERTA', '', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-895-1196';

-- WP ID: 1199 / slug: imp-quo-896-1199 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-896-1199';

-- WP ID: 1200 / slug: imp-quo-897-1200 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 148 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-897-1200';

-- WP ID: 1201 / slug: imp-quo-898-1201 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-898-1201';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-898-1201';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR EN L - 270 X 160', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 45000, 45000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-898-1201';

-- WP ID: 1202 / slug: imp-quo-899-1202 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 205 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18750, 18750
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-899-1202';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 205 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18750, 18750
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-899-1202';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 220 X 110 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-899-1202';

-- WP ID: 1203 / slug: imp-quo-900-1203 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 272 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ 5 Repisas Zapateras ($450.00 c/u)', NULL, 1, 26750, 26750
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-900-1203';

-- WP ID: 1204 / slug: imp-quo-901-1204 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 248 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22600, 22600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-901-1204';

-- WP ID: 1206 / slug: imp-quo-902-1206 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ Nicho de TV', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-902-1206';

-- WP ID: 1207 / slug: imp-quo-903-1207 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 350 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅Torre de Entrepaños C/Puerta ancho máximo 60 cms. ✅ 3 Repisas Zapateras', NULL, 1, 49000, 49000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-903-1207';

-- WP ID: 1208 / slug: imp-quo-904-1208 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-904-1208';

-- WP ID: 1209 / slug: imp-quo-905-1209 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - ANCHO 315 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ Nicho Ventana', NULL, 1, 30600, 30600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-905-1209';

-- WP ID: 1211 / slug: imp-quo-906-1211 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-906-1211';

-- WP ID: 1212 / slug: imp-quo-907-1212 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA - ANCHO 80 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 4 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-907-1212';

-- WP ID: 1213 / slug: imp-quo-908-1213 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-908-1213';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN L - 300 X 300 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 49500, 49500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-908-1213';

-- WP ID: 1214 / slug: imp-quo-909-1214 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR - 120 CMS', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-909-1214';

-- WP ID: 1215 / slug: imp-quo-910-1215 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-910-1215';

-- WP ID: 1216 / slug: imp-quo-911-1216 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18600, 18600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-911-1216';

-- WP ID: 1217 / slug: imp-quo-912-1217 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 192 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 3 cajones c/u ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅4 Repizas Zapateras Extras', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-912-1217';

-- WP ID: 1218 / slug: imp-quo-913-1218 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-913-1218';

-- WP ID: 1219 / slug: imp-quo-914-1219 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-914-1219';

-- WP ID: 1220 / slug: imp-quo-915-1220 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-915-1220';

-- WP ID: 1221 / slug: imp-quo-916-1221 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 146 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15400, 15400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-916-1221';

-- WP ID: 1223 / slug: imp-quo-917-1223 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 204 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-917-1223';

-- WP ID: 1224 / slug: imp-quo-918-1224 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SEGUN DISEÑO - 300 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRES CON HASTA 4 CAJONES C/U, + UNA TORRE ZAPATERA O DE ENTREPAÑOS (5) . KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 31000, 31000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-918-1224';

-- WP ID: 1228 / slug: imp-quo-919-1228 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 186 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18450, 18450
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-919-1228';

-- WP ID: 1229 / slug: imp-quo-920-1229 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-920-1229';

-- WP ID: 1231 / slug: imp-quo-921-1231 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 157 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-921-1231';

-- WP ID: 1233 / slug: imp-quo-922-1233 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-922-1233';

-- WP ID: 1234 / slug: imp-quo-923-1234 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 166 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-923-1234';

-- WP ID: 1236 / slug: imp-quo-924-1236 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 171 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-924-1236';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16400, 16400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-924-1236';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 172 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-924-1236';

-- WP ID: 1237 / slug: imp-quo-925-1237 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21400, 21400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-925-1237';

-- WP ID: 1241 / slug: imp-quo-926-1241 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-926-1241';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-926-1241';

-- WP ID: 1242 / slug: imp-quo-927-1242 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Torre de Entrepaños ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21400, 21400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-927-1242';

-- WP ID: 1244 / slug: imp-quo-928-1244 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19400, 19400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-928-1244';

-- WP ID: 1245 / slug: imp-quo-929-1245 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26600, 26600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-929-1245';

-- WP ID: 1246 / slug: imp-quo-930-1246 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN L - 439 X 104 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-930-1246';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 213 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-930-1246';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21500, 21500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-930-1246';

-- WP ID: 1248 / slug: imp-quo-931-1248 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 130 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-931-1248';

-- WP ID: 1250 / slug: imp-quo-932-1250 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U - 150 X 250 X 110 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-932-1250';

-- WP ID: 1253 / slug: imp-quo-933-1253 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 288 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de hasta 4 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅Nicho de TV', NULL, 1, 39500, 39500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-933-1253';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'cajonera EXTRA', '', NULL, 1, 1500, 1500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-933-1253';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'lambrín', '', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-933-1253';

-- WP ID: 1254 / slug: imp-quo-934-1254 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25400, 25400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-934-1254';

-- WP ID: 1255 / slug: imp-quo-935-1255 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 290 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-935-1255';

-- WP ID: 1256 / slug: imp-quo-936-1256 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 200 X 274', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-936-1256';

-- WP ID: 1258 / slug: imp-quo-937-1258 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 364 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅Torre de Entrepaños ancho máximo 60 cms', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-937-1258';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS LINEAL - 310 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE DE ENTREPAÑOS FIJOS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-937-1258';

-- WP ID: 1259 / slug: imp-quo-938-1259 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24600, 24600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-938-1259';

-- WP ID: 1260 / slug: imp-quo-939-1260 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'PUERTAS EN MALETERO', '', NULL, 1, 11400, 11400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-939-1260';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U - 176 X 281 X 176 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE ZAPATERA O DE ENTREPAÑOS FIJOS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 40800, 40800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-939-1260';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 256 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-939-1260';

-- WP ID: 1261 / slug: imp-quo-940-1261 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 168 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-940-1261';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 169 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-940-1261';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR EN L - 186 X 181 cms', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES C/U, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-940-1261';

-- WP ID: 1262 / slug: imp-quo-941-1262 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 350 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 370 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-941-1262';

-- WP ID: 1264 / slug: imp-quo-942-1264 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 434 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-942-1264';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE DE ENTREPAÑOS CON PUERTA DE CRISTAL', 'ANCHO MAXIMO 60 CMS / ALTURA MAXIMA 200 CMS', NULL, 1, 10000, 10000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-942-1264';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'ESCRITORIO FLOTANTE CON CAJONES', '2 CAJONES DE ANCHO MAXIMO 60 CMS C/U', NULL, 1, 7000, 7000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-942-1264';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'LAMBRIN', '', NULL, 1, 7000, 7000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-942-1264';

-- WP ID: 1265 / slug: imp-quo-943-1265 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-943-1265';

-- WP ID: 1266 / slug: imp-quo-944-1266 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-944-1266';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 183 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-944-1266';

-- WP ID: 1269 / slug: imp-quo-945-1269 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN PARALELO - 290 X 215 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones c/u ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-945-1269';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE LAVANDERIA - 289 CMS', 'GABINETES SUPERIORES DE LAVANDERIA PROFUNDIDAD DE 45 CMS, INCLUYE 2 REPISAS INTERIORES', NULL, 1, 11500, 11500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-945-1269';

-- WP ID: 1270 / slug: imp-quo-946-1270 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 162 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-946-1270';

-- WP ID: 1271 / slug: imp-quo-947-1271 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-947-1271';

-- WP ID: 1272 / slug: imp-quo-948-1272 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 161 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-948-1272';

-- WP ID: 1273 / slug: imp-quo-949-1273 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 167 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-949-1273';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VANITY CON HASTA 5 CAJONES', '', NULL, 1, 7500, 7500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-949-1273';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MALETERO ARRIBA DE VENTANA', '', NULL, 1, 3700, 3700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-949-1273';

-- WP ID: 1282 / slug: imp-quo-950-1282 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 225 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20600, 20600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-quo-950-1282';

SET FOREIGN_KEY_CHECKS = 1;

-- Summary: 1106 items from 827 cotizaciones
-- SKIPPED (no slug match): WP IDs 741
