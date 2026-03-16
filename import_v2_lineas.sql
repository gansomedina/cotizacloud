-- ============================================================
-- CotizaCloud v2: import cotizacion_lineas
-- Generated: 2026-03-16 18:16:23
-- Total: 1096 items from 820 cotizaciones
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- QUO-0111 / slug: imp-v2-quo-0111 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 90, 90
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-0111';

-- QUO-112 / slug: imp-v2-quo-112 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 175, 90, 15750
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-112';

-- QUO-113 / slug: imp-v2-quo-113 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC NINA (GRANDE)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 400, 90, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-113';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA CON CUBIERTA ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 300, 90, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-113';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'VESTIDOR REC PRINCIPAL', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅NO INCLUYE PUERTAS EN AREA DE CLOSET✅Torre de entrepaños (5).✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 220 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 400, 60, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-113';

-- QUO-114 / slug: imp-v2-quo-114 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-114';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VESTIDOR REC PPAL', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Base y maletero sin puertas✅Dos Torres Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-114';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Gabinetes de Lavanderia en Melamina. Incluye puertas, bisagras y jaladeras. Medida 150 cms x altura 120 cms x profundidad 45 cms. Incluye Repisas interiores (2).', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-114';

-- QUO-115 / slug: imp-v2-quo-115 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'ESCRITORIO', 'ESCRITORIO EN MELAMINA, INCLUYE: 2 CAJONERAS CON 3 CAJONES CADA UNO, NICHO PARA IMPRESORA CON 1 CAJON. CUBIERTA EN MELAMINA.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-115';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'LIBRERO', 'EN MELAMINA, INCLUYE GABINETES BASE CON PUERTAS, HERRAJES Y JALADERAS. NICHOS CON REPISAS (FORRO DE PAREDES). NO INCLUYE ILUMINACION. MEDIDAS LARGO 280 CMS X PROFUNDIDAD 45 CMS X ALTURA 256 CM.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-115';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MUEBLE DE TV', 'MUEBLE DE TV EN MELAMINA. INCLUYE GABINETE FLOTADO CON PUERTAS. NICHO DECORTATIVO. FALSO MURO EN MELAMINA. NO INCLUYE ILUMINACION. MEDIDA: HASTA 200 CMS LARGO', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-115';

-- QUO-116 / slug: imp-v2-quo-116 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-116';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE CAJONERA EXTRA', '✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).', NULL, 1, 3000, 3000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-116';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'TORRE REPISAS EXTRA', '✅Torre de Repisas (4 repisas) ancho máximo 60 cms.', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-116';

-- QUO-117 / slug: imp-v2-quo-117 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 410, 80, 32800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-117';

-- QUO-118 / slug: imp-v2-quo-118 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado con Torre de repisas y Nicho de TV diseno proporcionado por cliente ✅Torre Cajonera de 3 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 2.1, 13000, 27300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-118';

-- QUO-119 / slug: imp-v2-quo-119 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 185, 90, 16650
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-119';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 3', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 195, 90, 17550
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-119';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'VESTIDOR REC PPAL', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Base y Maletero (No incluye puertas (costo adicional)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 220 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 400, 65, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-119';

-- QUO-121 / slug: imp-v2-quo-121 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado (Si requiere pared lateral +$450)✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-121';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VESTIDOR EN U', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅No incluye puertas en maletero✅Torre Cajonera de 5 cajones ancho máximo 60 cms.✅Incluye Base y Maletero.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-121';

-- QUO-122 / slug: imp-v2-quo-122 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 3METROS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado ✅Torre Cajonera de 5 cajones ancho máximo 60 cms (cajon adicional en la misma torre +$500).✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-122';

-- QUO-123 / slug: imp-v2-quo-123 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms.✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-123';

-- QUO-124 / slug: imp-v2-quo-124 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms ✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-124';

-- QUO-125 / slug: imp-v2-quo-125 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-125';

-- QUO-126 / slug: imp-v2-quo-126 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-126';

-- QUO-127 / slug: imp-v2-quo-127 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23500, 23500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-127';

-- QUO-128 / slug: imp-v2-quo-128 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-128';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 1 (SIN PUERTAS)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅NO INCLUYE PUERTAS✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-128';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'VESTIDOR MELAMINA STANDARD (TIPO U)', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajoneras de 5 cajones ancho máximo 60 cmsNo incluye puertas.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-128';

-- QUO-129 / slug: imp-v2-quo-129 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-129';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-129';

-- QUO-130 / slug: imp-v2-quo-130 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 150 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-130';

-- QUO-131 / slug: imp-v2-quo-131 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-131';

-- QUO-132 / slug: imp-v2-quo-132 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 cajoneras de 1 cajon cu cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-132';

-- QUO-133 / slug: imp-v2-quo-133 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-133';

-- QUO-134 / slug: imp-v2-quo-134 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-134';

-- QUO-135 / slug: imp-v2-quo-135 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-135';

-- QUO-136 / slug: imp-v2-quo-136 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 211 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-136';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 299 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-136';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'VESTIDOR EN MELAMINA EN U', 'Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Putertas en Maletero✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Area de tubos colgadores.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-136';

-- QUO-137 / slug: imp-v2-quo-137 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET TIPO LIBRERO', 'Closet en Melamina Tipo Librero 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado y Escritorio✅Torre con repisas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 40500, 40500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-137';

-- QUO-138 / slug: imp-v2-quo-138 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-138';

-- QUO-139 / slug: imp-v2-quo-139 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-139';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MDF PINTADO PUERTA LISA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-139';

-- QUO-140 / slug: imp-v2-quo-140 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajonera de 4 cajones ancho máximo 60 cms cada torre✅Puertas Principales ancho máximo 60 cms solo en area de Tubos.✅No incluye Puertas en Maletero✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-140';

-- QUO-141 / slug: imp-v2-quo-141 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-141';

-- QUO-142 / slug: imp-v2-quo-142 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-142';

-- QUO-143 / slug: imp-v2-quo-143 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre de repisas (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-143';

-- QUO-144 / slug: imp-v2-quo-144 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA TIPO VESTIDOR EN L 1.6 x 1.8', 'Closet tipo Vestidor en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅No Incluye PuertasTorre Zapatera con repisas fijas (5) sin puerta, ancho maximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-144';

-- QUO-145 / slug: imp-v2-quo-145 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2.47m', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22200, 22200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-145';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'ESCRITORIO EN MELAMINA', 'ESCRITORIO EN MELAMINA, CON 2 CAJONES. MEDIDA 120 CMS', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-145';

-- QUO-146 / slug: imp-v2-quo-146 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-146';

-- QUO-147 / slug: imp-v2-quo-147 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'ESCRITORIO', 'ESCRITORIO EN MELAMINA, INCLUYE: 2 CAJONERAS CON 3 CAJONES CADA UNO, NICHO PARA IMPRESORA CON 1 CAJON. CUBIERTA EN MELAMINA.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-147';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'LIBRERO', 'EN MELAMINA, INCLUYE GABINETES BASE CON PUERTAS, HERRAJES Y JALADERAS. NICHOS CON REPISAS (FORRO DE PAREDES). NO INCLUYE ILUMINACION. MEDIDAS LARGO 280 CMS X PROFUNDIDAD 45 CMS X ALTURA 256 CM.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-147';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MUEBLE DE TV', 'MUEBLE DE TV EN MELAMINA. INCLUYE GABINETE FLOTADO CON PUERTAS. NICHO DECORTATIVO. FALSO MURO EN MELAMINA. NO INCLUYE ILUMINACION. MEDIDA: HASTA 200 CMS LARGO', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-147';

-- QUO-148 / slug: imp-v2-quo-148 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-148';

-- QUO-149 / slug: imp-v2-quo-149 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE DE REPISAS FIJAS (5) (SIN PUERTA)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-149';

-- QUO-150 / slug: imp-v2-quo-150 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17100, 17100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-150';

-- QUO-151 / slug: imp-v2-quo-151 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-151';

-- QUO-152 / slug: imp-v2-quo-152 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-152';

-- QUO-153 / slug: imp-v2-quo-153 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-153';

-- QUO-154 / slug: imp-v2-quo-154 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-154';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15900, 15900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-154';

-- QUO-155 / slug: imp-v2-quo-155 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-155';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-155';

-- QUO-156 / slug: imp-v2-quo-156 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2.79m', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-156';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 1.92m', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-156';

-- QUO-157 / slug: imp-v2-quo-157 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-157';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA', 'Torre zapatera con repisas fijas (9). Ancho max 60. Prof max 60', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-157';

-- QUO-158 / slug: imp-v2-quo-158 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET EN VENTANA', 'CLOSET EN MELAMINA, CON MODULO CAJONERA ABAJO VENTANA (5 CAJONES). INCLUYE PUERTAS LATERALES Y MALETERO CORRIDO. INCLUYE BASE Y MALETERO, NO INCLUYE FORRO DE MUROS INTERIORES. INCLUYE JALADERAS Y BISAGRAS.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-158';

-- QUO-159 / slug: imp-v2-quo-159 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-159';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet EmpotradoNo incluye torre cajonera✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-159';

-- QUO-160 / slug: imp-v2-quo-160 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-160';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'Cambio Puertas AltoBrillo', 'CAMBIO DE MATERIAL DE MELAMINA STANDARD A MELAMINA ALTO BRILLO BLANCO', NULL, 1, 8000, 8000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-160';

-- QUO-161 / slug: imp-v2-quo-161 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-161';

-- QUO-162 / slug: imp-v2-quo-162 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE ZAPATERA CON REPISAS FIJAS (5) SIN PUERTA, ANCHO MAXIMO 50 CMS. PROFUNDIDAD STANDARD 60 CMS.KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-162';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE TOCADOR', 'MUEBLE EN MELAMINA, CON CUBIERTA EN MELAMINA. INCLUYE 1 CAJONERA (1).', NULL, 1, 3500, 3500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-162';

-- QUO-163 / slug: imp-v2-quo-163 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-163';

-- QUO-164 / slug: imp-v2-quo-164 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-164';

-- QUO-165 / slug: imp-v2-quo-165 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-165';

-- QUO-166 / slug: imp-v2-quo-166 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cmsIncluye torre zapatera con repisas fijas (5) ancho maximo 60 cms.✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-166';

-- QUO-167 / slug: imp-v2-quo-167 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-167';

-- QUO-168 / slug: imp-v2-quo-168 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet EmpotradoNO INCLUYE: Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-168';

-- QUO-169 / slug: imp-v2-quo-169 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet EmpotradoTorre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15900, 15900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-169';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'ESCRITORIO', '1 MODULO DE 1 PUERTA, EN MELAMINA INCLUYE CUBIERTA EN MELAMINA', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-169';

-- QUO-170 / slug: imp-v2-quo-170 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-170';

-- QUO-171 / slug: imp-v2-quo-171 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-171';

-- QUO-172 / slug: imp-v2-quo-172 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-172';

-- QUO-173 / slug: imp-v2-quo-173 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-173';

-- QUO-174 / slug: imp-v2-quo-174 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-174';

-- QUO-175 / slug: imp-v2-quo-175 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-175';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-175';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. CAJONERA DE 4 CAJONES.KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-175';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'NICHO', 'NICHO CON PUERTA, INCLUYE TORRE ZAPATERA (7) REPISAS FIJAS, BASTIDORES Y HERRAJES', NULL, 1, 7500, 7500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-175';

-- QUO-176 / slug: imp-v2-quo-176 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-176';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-176';

-- QUO-177 / slug: imp-v2-quo-177 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-177';

-- QUO-178 / slug: imp-v2-quo-178 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con (6) repisas fijas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-178';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con (6) repisas fijasancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-178';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD REC 3', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con (6) repisas fijasancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-178';

-- QUO-179 / slug: imp-v2-quo-179 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-179';

-- QUO-180 / slug: imp-v2-quo-180 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-180';

-- QUO-181 / slug: imp-v2-quo-181 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23500, 23500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-181';

-- QUO-182 / slug: imp-v2-quo-182 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-182';

-- QUO-183 / slug: imp-v2-quo-183 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31000, 31000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-183';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET PUERTAS MDF PINTADO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 37000, 37000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-183';

-- QUO-184 / slug: imp-v2-quo-184 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20700, 20700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-184';

-- QUO-185 / slug: imp-v2-quo-185 (6 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRE CON HASTA 5 CAJONES CU, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-185';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA', 'EN MELAMINA, TORRE ZAPATERA CON REPISAS FIJAS INTERIORES (6). INCLUYE PUERTA. NO INCLUYE PUERTA EN MALETERO', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-185';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-185';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA STANDARD 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-185';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'CLOSET MELAMINA STANDARD 274 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-185';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 5, 'PUERTAS EN MELAMINA', 'EN MELAMINA, INCLUYE PUERTAS, HERRAJES Y VISTAS.', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-185';

-- QUO-186 / slug: imp-v2-quo-186 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-186';

-- QUO-187 / slug: imp-v2-quo-187 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-187';

-- QUO-188 / slug: imp-v2-quo-188 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-188';

-- QUO-189 / slug: imp-v2-quo-189 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRE CON HASTA 5 CAJONES CU, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-189';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA ( EN AREA EXTRA )', 'EN MELAMINA, TORRE ZAPATERA CON REPISAS FIJAS INTERIORES (6). INCLUYE PUERTA. NO INCLUYE PUERTA EN MALETERO', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-189';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-189';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'PUERTAS', 'PUERTAS EN MELAMINA, INCLUYE BASTIDORES Y HERRAJES.', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-189';

-- QUO-190 / slug: imp-v2-quo-190 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-190';

-- QUO-191 / slug: imp-v2-quo-191 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Cajones✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12500, 12500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-191';

-- QUO-192 / slug: imp-v2-quo-192 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Torre zapatera con repisas interiores fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-192';

-- QUO-193 / slug: imp-v2-quo-193 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13000, 13000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-193';

-- QUO-194 / slug: imp-v2-quo-194 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-194';

-- QUO-195 / slug: imp-v2-quo-195 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-195';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-195';

-- QUO-196 / slug: imp-v2-quo-196 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-196';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-196';

-- QUO-197 / slug: imp-v2-quo-197 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-197';

-- QUO-198 / slug: imp-v2-quo-198 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre de repisas fijas (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13000, 13000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-198';

-- QUO-199 / slug: imp-v2-quo-199 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-199';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-199';

-- QUO-200 / slug: imp-v2-quo-200 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-200';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE TV', 'EN MELAMINA CATALOGO STANDARD. MUEBLE DE TV SEGUN DISENO MEDIDA 213 CMS. INCLUYE GABINETES FLOTADOS. CUBIERTA EN MELAMINA, HERRAJES Y LAMBRIN DE 90 CMS X 270 ALTURA. MEDIDAS:', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-200';

-- QUO-201 / slug: imp-v2-quo-201 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA + ESCRITORIO (DISENO PROPORCIONADO POR CLIENTE)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32500, 32500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-201';

-- QUO-202 / slug: imp-v2-quo-202 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-202';

-- QUO-203 / slug: imp-v2-quo-203 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-203';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-203';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-203';

-- QUO-204 / slug: imp-v2-quo-204 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS (EN U)', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-204';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-204';

-- QUO-205 / slug: imp-v2-quo-205 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-205';

-- QUO-206 / slug: imp-v2-quo-206 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-206';

-- QUO-207 / slug: imp-v2-quo-207 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-207';

-- QUO-208 / slug: imp-v2-quo-208 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-208';

-- QUO-209 / slug: imp-v2-quo-209 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-209';

-- QUO-210 / slug: imp-v2-quo-210 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-210';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-210';

-- QUO-211 / slug: imp-v2-quo-211 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-211';

-- QUO-212 / slug: imp-v2-quo-212 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-212';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-212';

-- QUO-213 / slug: imp-v2-quo-213 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-213';

-- QUO-214 / slug: imp-v2-quo-214 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-214';

-- QUO-215 / slug: imp-v2-quo-215 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15700, 15700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-215';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA', 'En Melamina: Torre zapatera con repisas fijas (5) con puerta y maletero con puerta ambos empotrados en nicho: 0.63m ancho x 2.56m alto', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-215';

-- QUO-216 / slug: imp-v2-quo-216 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-216';

-- QUO-217 / slug: imp-v2-quo-217 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-217';

-- QUO-218 / slug: imp-v2-quo-218 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.Torre con repisas fijas✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-218';

-- QUO-219 / slug: imp-v2-quo-219 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-219';

-- QUO-220 / slug: imp-v2-quo-220 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-220';

-- QUO-221 / slug: imp-v2-quo-221 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-221';

-- QUO-222 / slug: imp-v2-quo-222 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-222';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-222';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-222';

-- QUO-223 / slug: imp-v2-quo-223 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-223';

-- QUO-224 / slug: imp-v2-quo-224 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-224';

-- QUO-225 / slug: imp-v2-quo-225 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2.4M', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-225';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 2.1M', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-225';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'PUERTAS EN NICHO', 'EN MELAMINA: 0.74m ancho x 2.10m alto . INCLUYE HERRAJES, BISAGRAS, JALADERAS Y 2 REPISAS FIJAS', NULL, 1, 7000, 7000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-225';

-- QUO-226 / slug: imp-v2-quo-226 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-226';

-- QUO-227 / slug: imp-v2-quo-227 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-227';

-- QUO-228 / slug: imp-v2-quo-228 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-228';

-- QUO-229 / slug: imp-v2-quo-229 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-229';

-- QUO-230 / slug: imp-v2-quo-230 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-230';

-- QUO-231 / slug: imp-v2-quo-231 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅NO INCLUYE MALETERO, ALTURA 180 CMS✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-231';

-- QUO-232 / slug: imp-v2-quo-232 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-232';

-- QUO-233 / slug: imp-v2-quo-233 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Torre zapatera con repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-233';

-- QUO-234 / slug: imp-v2-quo-234 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅DOS Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-234';

-- QUO-235 / slug: imp-v2-quo-235 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-235';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-235';

-- QUO-236 / slug: imp-v2-quo-236 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-236';

-- QUO-237 / slug: imp-v2-quo-237 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. INCLUYE REPISAS SEGUN DISENOKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-237';

-- QUO-238 / slug: imp-v2-quo-238 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-238';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA (SIN TORRE CAJONERA)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-238';

-- QUO-239 / slug: imp-v2-quo-239 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-239';

-- QUO-240 / slug: imp-v2-quo-240 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + ESCRITORIO + NICHO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cmsEscritorio en Melamina + Nicho con repisas segun diseno✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-240';

-- QUO-241 / slug: imp-v2-quo-241 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 4 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-241';

-- QUO-242 / slug: imp-v2-quo-242 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MFD PINTADO (PUERTAS LISA)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-242';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-242';

-- QUO-243 / slug: imp-v2-quo-243 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-243';

-- QUO-244 / slug: imp-v2-quo-244 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-244';

-- QUO-245 / slug: imp-v2-quo-245 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 1.9M', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-245';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 2.7M', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-245';

-- QUO-246 / slug: imp-v2-quo-246 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-246';

-- QUO-247 / slug: imp-v2-quo-247 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-247';

-- QUO-248 / slug: imp-v2-quo-248 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA VERSION STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-248';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA DISENO POR EL CLIENTE', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Torre zapatera con repisas extraibles (6) perfil metabox✅Escritorio con 2 cajonenes metabox.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 37000, 37000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-248';

-- QUO-249 / slug: imp-v2-quo-249 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 2 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-249';

-- QUO-250 / slug: imp-v2-quo-250 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-250';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-250';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-250';

-- QUO-251 / slug: imp-v2-quo-251 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA REPISAS, Y DOS BUROS CAJONERAS (DISENO PROPORCIONADO POR CLIENTE). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-251';

-- QUO-252 / slug: imp-v2-quo-252 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-252';

-- QUO-253 / slug: imp-v2-quo-253 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-253';

-- QUO-254 / slug: imp-v2-quo-254 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-254';

-- QUO-255 / slug: imp-v2-quo-255 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-255';

-- QUO-256 / slug: imp-v2-quo-256 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE CON REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-256';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE CON REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-256';

-- QUO-257 / slug: imp-v2-quo-257 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.Incluye cuatro repisas zapateras✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19800, 19800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-257';

-- QUO-258 / slug: imp-v2-quo-258 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC PPAL', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-258';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC PPAL', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-258';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-258';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA STANDARD REC 3', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-258';

-- QUO-259 / slug: imp-v2-quo-259 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-259';

-- QUO-260 / slug: imp-v2-quo-260 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅DOS Torres Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-260';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-260';

-- QUO-261 / slug: imp-v2-quo-261 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-261';

-- QUO-262 / slug: imp-v2-quo-262 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-262';

-- QUO-263 / slug: imp-v2-quo-263 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-263';

-- QUO-264 / slug: imp-v2-quo-264 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-264';

-- QUO-265 / slug: imp-v2-quo-265 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-265';

-- QUO-266 / slug: imp-v2-quo-266 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-266';

-- QUO-267 / slug: imp-v2-quo-267 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN L', 'FRENTES, PUERTAS EN MALETERO DOS TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-267';

-- QUO-268 / slug: imp-v2-quo-268 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15200, 15200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-268';

-- QUO-269 / slug: imp-v2-quo-269 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-269';

-- QUO-270 / slug: imp-v2-quo-270 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-270';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-270';

-- QUO-271 / slug: imp-v2-quo-271 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre zapatera con repisas fijas (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-271';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-271';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS MALETERO', 'FRENTES, NO INCLUYE PUERTAS MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-271';

-- QUO-272 / slug: imp-v2-quo-272 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-272';

-- QUO-273 / slug: imp-v2-quo-273 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-273';

-- QUO-274 / slug: imp-v2-quo-274 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-274';

-- QUO-275 / slug: imp-v2-quo-275 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-275';

-- QUO-276 / slug: imp-v2-quo-276 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-276';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13000, 13000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-276';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'TORRE DE REPISAS', 'TORRE DE REPISAS FIJAS (5) EN MELAMINA, MEDIDA: 160 CMS X 240 ALTURA', NULL, 1, 10000, 10000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-276';

-- QUO-277 / slug: imp-v2-quo-277 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-277';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-277';

-- QUO-278 / slug: imp-v2-quo-278 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-278';

-- QUO-279 / slug: imp-v2-quo-279 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-279';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-279';

-- QUO-280 / slug: imp-v2-quo-280 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET PUERTAS MDF CON MOLDURAS', 'Closet interior Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-280';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-280';

-- QUO-281 / slug: imp-v2-quo-281 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-281';

-- QUO-282 / slug: imp-v2-quo-282 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-282';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-282';

-- QUO-283 / slug: imp-v2-quo-283 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-283';

-- QUO-284 / slug: imp-v2-quo-284 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-284';

-- QUO-285 / slug: imp-v2-quo-285 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms, incluye 1 repisa zapatera✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23500, 23500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-285';

-- QUO-286 / slug: imp-v2-quo-286 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-286';

-- QUO-287 / slug: imp-v2-quo-287 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 53000, 53000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-287';

-- QUO-288 / slug: imp-v2-quo-288 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-288';

-- QUO-289 / slug: imp-v2-quo-289 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-289';

-- QUO-290 / slug: imp-v2-quo-290 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Torre Zapatera con repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-290';

-- QUO-291 / slug: imp-v2-quo-291 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 200 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17800, 17800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-291';

-- QUO-292 / slug: imp-v2-quo-292 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-292';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-292';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE ZAPATERA CON REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-292';

-- QUO-293 / slug: imp-v2-quo-293 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA CON ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 2 cajones ancho máximo 60 cms + Escritorio con 2 cajones✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-293';

-- QUO-294 / slug: imp-v2-quo-294 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-294';

-- QUO-295 / slug: imp-v2-quo-295 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 2.10m x 1.47m', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-295';

-- QUO-296 / slug: imp-v2-quo-296 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-296';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-296';

-- QUO-297 / slug: imp-v2-quo-297 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'ZAPATERA', 'ZAPATERA EN MELAMINA, MEDIDAS: 0.50m ancho x 2.34m alto, INCLUYE 7 REPISAS FIJAS.', NULL, 1, 8000, 8000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-297';

-- QUO-298 / slug: imp-v2-quo-298 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-298';

-- QUO-299 / slug: imp-v2-quo-299 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-299';

-- QUO-300 / slug: imp-v2-quo-300 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-300';

-- QUO-301 / slug: imp-v2-quo-301 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 120 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11500, 11500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-301';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 140 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-301';

-- QUO-302 / slug: imp-v2-quo-302 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-302';

-- QUO-303 / slug: imp-v2-quo-303 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-303';

-- QUO-304 / slug: imp-v2-quo-304 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-304';

-- QUO-305 / slug: imp-v2-quo-305 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅NO INCLUYE: Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10000, 10000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-305';

-- QUO-306 / slug: imp-v2-quo-306 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-306';

-- QUO-307 / slug: imp-v2-quo-307 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-307';

-- QUO-308 / slug: imp-v2-quo-308 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-308';

-- QUO-309 / slug: imp-v2-quo-309 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-309';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-309';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-309';

-- QUO-310 / slug: imp-v2-quo-310 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA 120 cm sin torre cajonera', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10000, 10000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-310';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14900, 14900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-310';

-- QUO-311 / slug: imp-v2-quo-311 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-311';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-311';

-- QUO-312 / slug: imp-v2-quo-312 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-312';

-- QUO-313 / slug: imp-v2-quo-313 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-313';

-- QUO-314 / slug: imp-v2-quo-314 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-314';

-- QUO-315 / slug: imp-v2-quo-315 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-315';

-- QUO-316 / slug: imp-v2-quo-316 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-316';

-- QUO-317 / slug: imp-v2-quo-317 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-317';

-- QUO-318 / slug: imp-v2-quo-318 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-318';

-- QUO-319 / slug: imp-v2-quo-319 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA CON NICHO TV', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajoneras de 5 cajones ancho máximo 60 cms + Nicho TV segun diseno proporcionado✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-319';

-- QUO-320 / slug: imp-v2-quo-320 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-320';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-320';

-- QUO-321 / slug: imp-v2-quo-321 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-321';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-321';

-- QUO-322 / slug: imp-v2-quo-322 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-322';

-- QUO-323 / slug: imp-v2-quo-323 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-323';

-- QUO-324 / slug: imp-v2-quo-324 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-324';

-- QUO-325 / slug: imp-v2-quo-325 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-325';

-- QUO-326 / slug: imp-v2-quo-326 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 2X2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30500, 30500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-326';

-- QUO-327 / slug: imp-v2-quo-327 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-327';

-- QUO-328 / slug: imp-v2-quo-328 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-328';

-- QUO-329 / slug: imp-v2-quo-329 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-329';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE ZAPATERA CON REPISAS FIJAS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 35700, 35700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-329';

-- QUO-330 / slug: imp-v2-quo-330 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-330';

-- QUO-331 / slug: imp-v2-quo-331 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-331';

-- QUO-332 / slug: imp-v2-quo-332 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-332';

-- QUO-333 / slug: imp-v2-quo-333 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-333';

-- QUO-334 / slug: imp-v2-quo-334 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18900, 18900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-334';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-334';

-- QUO-335 / slug: imp-v2-quo-335 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18900, 18900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-335';

-- QUO-336 / slug: imp-v2-quo-336 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-336';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-336';

-- QUO-337 / slug: imp-v2-quo-337 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-337';

-- QUO-338 / slug: imp-v2-quo-338 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-338';

-- QUO-339 / slug: imp-v2-quo-339 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-339';

-- QUO-340 / slug: imp-v2-quo-340 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-340';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-340';

-- QUO-341 / slug: imp-v2-quo-341 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)ESCRITORIO TIPO VANITY CON 2 CAJONES KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 59000, 59000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-341';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. TORRE DE REPISAS FIJAS (5)ESCRITORIO TIPO VANITY CON 2 CAJONES KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32500, 32500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-341';

-- QUO-342 / slug: imp-v2-quo-342 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-342';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-342';

-- QUO-343 / slug: imp-v2-quo-343 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-343';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS en l', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-343';

-- QUO-344 / slug: imp-v2-quo-344 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12900, 12900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-344';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-344';

-- QUO-345 / slug: imp-v2-quo-345 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-345';

-- QUO-346 / slug: imp-v2-quo-346 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-346';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-346';

-- QUO-347 / slug: imp-v2-quo-347 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-347';

-- QUO-348 / slug: imp-v2-quo-348 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15600, 15600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-348';

-- QUO-349 / slug: imp-v2-quo-349 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-349';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-349';

-- QUO-350 / slug: imp-v2-quo-350 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-350';

-- QUO-351 / slug: imp-v2-quo-351 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-351';

-- QUO-352 / slug: imp-v2-quo-352 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-352';

-- QUO-353 / slug: imp-v2-quo-353 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-353';

-- QUO-354 / slug: imp-v2-quo-354 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-354';

-- QUO-355 / slug: imp-v2-quo-355 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO)DOS TORRES CON 5 CAJONES CU Y PUERTA COSMETIQUERA CON REPISA FIJA INTERIOR (1). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 30500, 30500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-355';

-- QUO-356 / slug: imp-v2-quo-356 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12500, 12500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-356';

-- QUO-357 / slug: imp-v2-quo-357 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-357';

-- QUO-358 / slug: imp-v2-quo-358 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre de repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅NO INCLUYE: Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-358';

-- QUO-359 / slug: imp-v2-quo-359 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-359';

-- QUO-360 / slug: imp-v2-quo-360 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-360';

-- QUO-361 / slug: imp-v2-quo-361 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-361';

-- QUO-362 / slug: imp-v2-quo-362 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-362';

-- QUO-363 / slug: imp-v2-quo-363 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-363';

-- QUO-364 / slug: imp-v2-quo-364 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + VANITY (SIN CAJONES)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-364';

-- QUO-365 / slug: imp-v2-quo-365 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-365';

-- QUO-366 / slug: imp-v2-quo-366 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-366';

-- QUO-367 / slug: imp-v2-quo-367 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17100, 17100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-367';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-367';

-- QUO-368 / slug: imp-v2-quo-368 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 288 cm x 334 cm', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms. + ALTURA EXTRA✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-368';

-- QUO-369 / slug: imp-v2-quo-369 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-369';

-- QUO-370 / slug: imp-v2-quo-370 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-370';

-- QUO-371 / slug: imp-v2-quo-371 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-371';

-- QUO-372 / slug: imp-v2-quo-372 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-372';

-- QUO-373 / slug: imp-v2-quo-373 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17800, 17800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-373';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-373';

-- QUO-374 / slug: imp-v2-quo-374 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17800, 17800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-374';

-- QUO-375 / slug: imp-v2-quo-375 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-375';

-- QUO-376 / slug: imp-v2-quo-376 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-376';

-- QUO-377 / slug: imp-v2-quo-377 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-377';

-- QUO-378 / slug: imp-v2-quo-378 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21500, 21500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-378';

-- QUO-379 / slug: imp-v2-quo-379 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-379';

-- QUO-380 / slug: imp-v2-quo-380 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-380';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres de Repisas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-380';

-- QUO-381 / slug: imp-v2-quo-381 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'OPCION 1) REC PPAL: CLOSET MELAMINA WALK IN VESTIDOR (SIN PUERTAS MALETERO)', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE CON REPISAS (ZAPATERA PROFUNDIDAD 30 CMS). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-381';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'OPCION 2) REC PPAL: CLOSET MELAMINA WALK IN VESTIDOR (CON PUERTAS MALETERO)', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE CON REPISAS (ZAPATERA PROFUNDIDAD 30 CMS).KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 46000, 46000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-381';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'REC 2: CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-381';

-- QUO-383 / slug: imp-v2-quo-383 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-383';

-- QUO-384 / slug: imp-v2-quo-384 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15200, 15200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-384';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15200, 15200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-384';

-- QUO-385 / slug: imp-v2-quo-385 (7 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-385';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.Escritorio en melamina segun diseno, incluye 2 cajones + repisas flotadas✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-385';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. DOS TORRES CON REPISAS FIJAS SEGUN DISENONO INCLUYE ESPEJOSKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 54000, 54000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-385';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'FLETE INSTALACION', 'Flete de Instalacion Foraneo 4 dias: Incluye 3 noches de hotel + alimentos para 1 equipo carpinteros (4 dias) + traslados Hermosillo-Guaymas', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-385';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'FORRO MURO AREA ESCRITORIO REC ULISESJR', 'En melamina std. forro trasero de muro no incluye adicionales, solo en area de Escritorio', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-385';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 5, 'LAVANDERIA', 'En melamina std. Gabinetes superiores de lavanderia, incluye gabinetes con puertas y repisas interior (2). Medida a desarrollar 270 cms. Profundidad de los gabinetes 45 cms y altura maxima 100 cms.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-385';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 6, 'PUERTAS ESCALERA', 'En melamina std. Puertas de hueco de escalera, incluye 2 puertas, bastidores en melamina, herrajes y vistas.', NULL, 1, 5000, 5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-385';

-- QUO-386 / slug: imp-v2-quo-386 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 120 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet con 1 pared color✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11500, 11500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-386';

-- QUO-387 / slug: imp-v2-quo-387 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-387';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'RESPALDO Y BUROS', 'En melamina standard. Respaldo de melamina hasta 120 cms (ancho del panel) incluye 2 buros tipo escritorio con 1 cajon cada uno.', NULL, 1, 7800, 7800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-387';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'LAMBRIN MELAMINA', 'Lambrin en melamina para respaldo de cama. Tiras de 6 cms. altura 120 cms.', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-387';

-- QUO-388 / slug: imp-v2-quo-388 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA 2 TORRES', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅4 Repisas Zapateras inferiores (2 por lado)✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-388';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, '2 CAJONES EXTRA', '2 cajones extra (se instalan 1 en cada torre)', NULL, 1, 800, 800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-388';

-- QUO-389 / slug: imp-v2-quo-389 (7 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-389';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.Escritorio en melamina segun diseno, incluye 2 cajones + repisas flotadas✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-389';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. DOS TORRES CON REPISAS FIJAS SEGUN DISENONO INCLUYE ESPEJOSKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 54000, 54000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-389';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'FLETE INSTALACION', 'Flete de Instalacion Foraneo 4 dias: Incluye 3 noches de hotel + alimentos para 1 equipo carpinteros (4 dias) + traslados Hermosillo-Guaymas', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-389';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'FORRO MURO AREA ESCRITORIO REC ULISESJR', 'En melamina std. forro trasero de muro no incluye adicionales, solo en area de Escritorio', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-389';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 5, 'LAVANDERIA', 'En melamina std. Gabinetes superiores de lavanderia, incluye gabinetes con puertas y repisas interior (2). Medida a desarrollar 270 cms. Profundidad de los gabinetes 45 cms y altura maxima 100 cms.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-389';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 6, 'PUERTAS ESCALERA', 'En melamina std. Puertas de hueco de escalera, incluye 2 puertas, bastidores en melamina, herrajes y vistas.', NULL, 1, 5000, 5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-389';

-- QUO-390 / slug: imp-v2-quo-390 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-390';

-- QUO-391 / slug: imp-v2-quo-391 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-391';

-- QUO-392 / slug: imp-v2-quo-392 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-392';

-- QUO-393 / slug: imp-v2-quo-393 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-393';

-- QUO-394 / slug: imp-v2-quo-394 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-394';

-- QUO-395 / slug: imp-v2-quo-395 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-395';

-- QUO-396 / slug: imp-v2-quo-396 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19800, 19800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-396';

-- QUO-397 / slug: imp-v2-quo-397 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-397';

-- QUO-398 / slug: imp-v2-quo-398 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN U', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE ZAPATERA CON REPISAS FIJAS (5). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 59000, 59000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-398';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. + TORRE ZAPATERA CON REPISAS FIJAS (5) KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 45000, 45000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-398';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD + VANITY (2 CAJONES)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-398';

-- QUO-399 / slug: imp-v2-quo-399 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15900, 15900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-399';

-- QUO-400 / slug: imp-v2-quo-400 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15900, 15900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-400';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE REPISAS', 'EN MELAMINA STD. TORRE DE REPISAS FIJAS SEGUN DISENO', NULL, 1, 3000, 3000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-400';

-- QUO-402 / slug: imp-v2-quo-402 (8 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-402';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-402';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 54000, 54000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-402';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'FLETE INSTALACION', 'Flete de Instalacion Foraneo 4 dias: Incluye 3 noches de hotel + alimentos para 1 equipo carpinteros (4 dias) + traslados Hermosillo-Guaymas', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-402';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'FORRO MURO AREA ESCRITORIO REC ULISESJR', 'En melamina std. forro trasero de muro no incluye adicionales, solo en area de Escritorio', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-402';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 5, 'PUERTAS ESCALERA', 'En melamina std. Puertas de hueco de escalera, incluye 2 puertas, bastidores en melamina, herrajes y vistas.', NULL, 1, 5000, 5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-402';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 6, 'DESCUENTO', 'DESCUENTO AL CONTRATO', NULL, 1, -5000, -5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-402';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 7, 'LAVANDERIA', 'En melamina std. Gabinetes superiores de lavanderia, incluye gabinetes con puertas y repisas interior (2). Medida a desarrollar 270 cms. Profundidad de los gabinetes 45 cms y altura maxima 100 cms.', NULL, 1, 10500, 10500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-402';

-- QUO-403 / slug: imp-v2-quo-403 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-403';

-- QUO-404 / slug: imp-v2-quo-404 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-404';

-- QUO-405 / slug: imp-v2-quo-405 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-405';

-- QUO-406 / slug: imp-v2-quo-406 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA (segun diseño) (UN CLOSET 226CMS)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho 75 cms✅Puertas Principales ancho 75 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Incluye repisas zapateras segun diseño y nicho en area central✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-406';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE A MEDIDA', 'EN MELAMINA STANDARD, MUEBLE A MEDIDA (AREA SUPERIOR DE PUERTA)', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-406';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'FLETE INSTALACION (3 CLOSETS)', 'FLETE DE INSTALACION: HERMOSILL-GUAYMAS, INCLUYE TRASLADOS, HOSPEDAJE Y ALIMENTOS PARA 3 DIAS.', NULL, 1, 7000, 7000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-406';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA STANDARD (UN CLOSET 226CMS)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-406';

-- QUO-407 / slug: imp-v2-quo-407 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-407';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA DISENO CLIENTE', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera horizontal de 6 cajones, ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms + Repisa interior zapatera.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-407';

-- QUO-408 / slug: imp-v2-quo-408 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-408';

-- QUO-409 / slug: imp-v2-quo-409 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 297 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26400, 26400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-409';

-- QUO-410 / slug: imp-v2-quo-410 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-410';

-- QUO-411 / slug: imp-v2-quo-411 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Escritorio con 2 cajones horizontal✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-411';

-- QUO-412 / slug: imp-v2-quo-412 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajonera de 5 cajones (cu) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-412';

-- QUO-413 / slug: imp-v2-quo-413 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10500, 10500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-413';

-- QUO-414 / slug: imp-v2-quo-414 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 38500, 38500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-414';

-- QUO-415 / slug: imp-v2-quo-415 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'PUERTAS CLOSET', 'EN MELAMINA STD, PUERTAS DE CLOSET, INCLUYE BASTIDORES EN MELAMINA, Y HERRAJES.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-415';

-- QUO-416 / slug: imp-v2-quo-416 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15300, 15300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-416';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14800, 14800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-416';

-- QUO-417 / slug: imp-v2-quo-417 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-417';

-- QUO-418 / slug: imp-v2-quo-418 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 90, 90
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-418';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-418';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-418';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-418';

-- QUO-419 / slug: imp-v2-quo-419 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD (LINEAL)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-419';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD (EN U)', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 49500, 49500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-419';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 49000, 49000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-419';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 37000, 37000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-419';

-- QUO-420 / slug: imp-v2-quo-420 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300CMS X H298', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 298 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-420';

-- QUO-421 / slug: imp-v2-quo-421 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 170 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-421';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 180 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-421';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'FLETE INSTALACION (2 CLOSETS)', 'Flete de Instalacion Foraneo 2 dias: Incluye 1 noche de hotel + alimentos para 1 equipo carpinteros (2 dias) + traslados Hermosillo-Guaymas', NULL, 1, 4500, 4500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-421';

-- QUO-422 / slug: imp-v2-quo-422 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-422';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-422';

-- QUO-423 / slug: imp-v2-quo-423 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15600, 15600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-423';

-- QUO-424 / slug: imp-v2-quo-424 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-424';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'GABINETES SUPERIORES COCINA', 'EN MELAMINA STANDARD, GABIENTES SUPERIORES DE COCINA.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-424';

-- QUO-425 / slug: imp-v2-quo-425 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'MUEBLE DE BAÑO 119CMS', 'NO INCLUYE CUBIERTA - EN MELAMINA STD. MUEBLE DE BAÑO. INCLUYE MODULOS DE PUERTA, Y 1 MODULO DE 1 CAJONERA (3) CAJONES. INCLUYE HERRAJES.', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-425';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE DE BAÑO 100CMS', 'NO INCLUYE CUBIERTA - EN MELAMINA STD. MUEBLE DE BAÑO. INCLUYE MODULOS DE PUERTA, Y 1 MODULO DE 1 CAJONERA (3) CAJONES. INCLUYE HERRAJES.', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-425';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MUEBLE DE BAÑO 200CMS', 'NO INCLUYE CUBIERTA - EN MELAMINA STD. MUEBLE DE BAÑO. INCLUYE MODULOS DE PUERTA, Y 2 MODULOS DE 1 CAJONERA (3) CAJONES. INCLUYE HERRAJES.', NULL, 1, 9000, 9000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-425';

-- QUO-426 / slug: imp-v2-quo-426 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16300, 16300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-426';

-- QUO-427 / slug: imp-v2-quo-427 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-427';

-- QUO-428 / slug: imp-v2-quo-428 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-428';

-- QUO-429 / slug: imp-v2-quo-429 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 350 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-429';

-- QUO-430 / slug: imp-v2-quo-430 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard 3.18 cm de ancho por 3.20 de altura. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-430';

-- QUO-431 / slug: imp-v2-quo-431 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-431';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-431';

-- QUO-432 / slug: imp-v2-quo-432 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE DE REPISAS FIJAS (5). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-432';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-432';

-- QUO-433 / slug: imp-v2-quo-433 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-433';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'ESCRITORIO CON LIBRERO', 'EN MELAMINA STD. MEDIDAS: 1.13 mts de ancho X 2.70 mt de alto, 51 cm de fondo.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-433';

-- QUO-434 / slug: imp-v2-quo-434 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + AREA TV', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torre Cajonera de 3 cajones ancho máximo 60 cms + Area TV con muro color calacatta✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31500, 31500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-434';

-- QUO-435 / slug: imp-v2-quo-435 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-435';

-- QUO-436 / slug: imp-v2-quo-436 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-436';

-- QUO-437 / slug: imp-v2-quo-437 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-437';

-- QUO-438 / slug: imp-v2-quo-438 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 4 metros', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-438';

-- QUO-439 / slug: imp-v2-quo-439 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-439';

-- QUO-440 / slug: imp-v2-quo-440 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-440';

-- QUO-441 / slug: imp-v2-quo-441 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-441';

-- QUO-442 / slug: imp-v2-quo-442 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-442';

-- QUO-443 / slug: imp-v2-quo-443 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-443';

-- QUO-444 / slug: imp-v2-quo-444 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-444';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-444';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-444';

-- QUO-445 / slug: imp-v2-quo-445 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-445';

-- QUO-446 / slug: imp-v2-quo-446 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-446';

-- QUO-447 / slug: imp-v2-quo-447 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-447';

-- QUO-448 / slug: imp-v2-quo-448 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-448';

-- QUO-449 / slug: imp-v2-quo-449 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-449';

-- QUO-450 / slug: imp-v2-quo-450 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-450';

-- QUO-451 / slug: imp-v2-quo-451 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-451';

-- QUO-452 / slug: imp-v2-quo-452 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-452';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-452';

-- QUO-453 / slug: imp-v2-quo-453 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-453';

-- QUO-454 / slug: imp-v2-quo-454 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-454';

-- QUO-455 / slug: imp-v2-quo-455 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-455';

-- QUO-456 / slug: imp-v2-quo-456 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-456';

-- QUO-457 / slug: imp-v2-quo-457 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-457';

-- QUO-458 / slug: imp-v2-quo-458 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 160 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-458';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 160 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-458';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD 90 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 11000, 11000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-458';

-- QUO-459 / slug: imp-v2-quo-459 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-459';

-- QUO-460 / slug: imp-v2-quo-460 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-460';

-- QUO-461 / slug: imp-v2-quo-461 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-461';

-- QUO-462 / slug: imp-v2-quo-462 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 150 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-462';

-- QUO-463 / slug: imp-v2-quo-463 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-463';

-- QUO-464 / slug: imp-v2-quo-464 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 1.8 X 3.6', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-464';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-464';

-- QUO-465 / slug: imp-v2-quo-465 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-465';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10500, 10500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-465';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 10500, 10500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-465';

-- QUO-466 / slug: imp-v2-quo-466 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-466';

-- QUO-467 / slug: imp-v2-quo-467 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-467';

-- QUO-468 / slug: imp-v2-quo-468 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-468';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-468';

-- QUO-470 / slug: imp-v2-quo-470 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-470';

-- QUO-471 / slug: imp-v2-quo-471 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-471';

-- QUO-472 / slug: imp-v2-quo-472 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA VENTANA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera horizontal de 8 cajones (ancho máximo 60 cms) ✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-472';

-- QUO-473 / slug: imp-v2-quo-473 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-473';

-- QUO-474 / slug: imp-v2-quo-474 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-474';

-- QUO-475 / slug: imp-v2-quo-475 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-475';

-- QUO-476 / slug: imp-v2-quo-476 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-476';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-476';

-- QUO-477 / slug: imp-v2-quo-477 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-477';

-- QUO-478 / slug: imp-v2-quo-478 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-478';

-- QUO-479 / slug: imp-v2-quo-479 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-479';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-479';

-- QUO-480 / slug: imp-v2-quo-480 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-480';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-480';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'FLETE FORANEO', 'INSTALACION FORANEA 2 DIAS: GASTROS DE TRASLADO Y HOSPEDAJE', NULL, 1, 6000, 6000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-480';

-- QUO-481 / slug: imp-v2-quo-481 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-481';

-- QUO-482 / slug: imp-v2-quo-482 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre con repisas fijas ancho máximo 60 cms + area de nichos al centro segun diseno✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-482';

-- QUO-483 / slug: imp-v2-quo-483 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos torres de repisas fijas (5)✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-483';

-- QUO-484 / slug: imp-v2-quo-484 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE DE REPISAS FIJAS (5). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-484';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-484';

-- QUO-485 / slug: imp-v2-quo-485 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + 2 repisas zapateras.✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17100, 17100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-485';

-- QUO-486 / slug: imp-v2-quo-486 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-486';

-- QUO-487 / slug: imp-v2-quo-487 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31500, 31500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-487';

-- QUO-488 / slug: imp-v2-quo-488 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-488';

-- QUO-489 / slug: imp-v2-quo-489 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD REC 1', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-489';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 2', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-489';

-- QUO-490 / slug: imp-v2-quo-490 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISENO + AREA VENTANA', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-490';

-- QUO-491 / slug: imp-v2-quo-491 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 130cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13900, 13900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-491';

-- QUO-492 / slug: imp-v2-quo-492 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 285', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-492';

-- QUO-493 / slug: imp-v2-quo-493 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS (230 x 217 Altura 310)', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. INCLUYE REPISAS EN SEPARACION DE TUBOS COLGADORES. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-493';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR (230 x 217 Altura 310)', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. INCLUYE REPISAS EN SEPARACION DE TUBOS COLGADORES. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44500, 44500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-493';

-- QUO-494 / slug: imp-v2-quo-494 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-494';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 300cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-494';

-- QUO-495 / slug: imp-v2-quo-495 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 190cm', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-495';

-- QUO-496 / slug: imp-v2-quo-496 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-496';

-- QUO-497 / slug: imp-v2-quo-497 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 130 x h350', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15200, 15200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-497';

-- QUO-498 / slug: imp-v2-quo-498 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-498';

-- QUO-499 / slug: imp-v2-quo-499 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22800, 22800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-499';

-- QUO-500 / slug: imp-v2-quo-500 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 130cm', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-500';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 100 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 12000, 12000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-500';

-- QUO-501 / slug: imp-v2-quo-501 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA + NICHO TV 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Dos Torres Cajonera de 4 cajones ancho máximo 60 cms + NICHO TV✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-501';

-- QUO-502 / slug: imp-v2-quo-502 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 33200, 33200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-502';

-- QUO-503 / slug: imp-v2-quo-503 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 108cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-503';

-- QUO-504 / slug: imp-v2-quo-504 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-504';

-- QUO-505 / slug: imp-v2-quo-505 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-505';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-505';

-- QUO-506 / slug: imp-v2-quo-506 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-506';

-- QUO-507 / slug: imp-v2-quo-507 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 268cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-507';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE ZAPATERA CON REPISAS', 'EN MELAMINA STD. TORRE ZAPATERA CON REPISAS FIJAS (MEDIDA DE ANCHO 60 CMS, ALTURA DE CLOSET). OTRAS MEDIDAS SE COTIZA POR SEPARADO', NULL, 1, 5500, 5500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-507';

-- QUO-508 / slug: imp-v2-quo-508 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-508';

-- QUO-509 / slug: imp-v2-quo-509 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 200CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-509';

-- QUO-510 / slug: imp-v2-quo-510 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19900, 19900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-510';

-- QUO-511 / slug: imp-v2-quo-511 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-511';

-- QUO-512 / slug: imp-v2-quo-512 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25000, 25000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-512';

-- QUO-513 / slug: imp-v2-quo-513 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-513';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15600, 15600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-513';

-- QUO-514 / slug: imp-v2-quo-514 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-514';

-- QUO-515 / slug: imp-v2-quo-515 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 180cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-515';

-- QUO-521 / slug: imp-v2-quo-521 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS 300x250x300', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE DE REPISAS FIJAS (5). KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 42500, 42500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-521';

-- QUO-522 / slug: imp-v2-quo-522 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-522';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'GABINETE EXTRA ARRIBA DE PUERTA', 'ANCHO: 90 CMS ALTURA 50 CMS', NULL, 1, 3000, 3000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-522';

-- QUO-523 / slug: imp-v2-quo-523 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 2 - 230CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-523';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 3 - 240CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-523';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD 4 - 247 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-523';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE CON REPISAS FIJAS (5) KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-523';

-- QUO-524 / slug: imp-v2-quo-524 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-524';

-- QUO-525 / slug: imp-v2-quo-525 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'PAR DE BUROS RECAMARA - 50 x 35 x 60', 'PAR DE BUROS RECAMARA ANCHO 50 CMS ALTURA 60 CMS FONDO 35 CMS', NULL, 1, 4500, 4500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-525';

-- QUO-526 / slug: imp-v2-quo-526 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-526';

-- QUO-527 / slug: imp-v2-quo-527 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-527';

-- QUO-528 / slug: imp-v2-quo-528 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-528';

-- QUO-529 / slug: imp-v2-quo-529 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-529';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA + CAJONES + NICHO DE TV', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-529';

-- QUO-530 / slug: imp-v2-quo-530 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-530';

-- QUO-531 / slug: imp-v2-quo-531 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'REC 1: CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS (lineal 280 cms)', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. + UNA TORRE HORIZONTAL CON CAJONES (6) + NICHOS INFERIORESKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 31500, 31500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-531';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'REC 2: VESTIDOR CON AREA DE TV (280 CMS x h210)', 'FRENTES, PUERTAS Y 1 TORRE CAJONERA + TORRE COLGADORA CON PUERTAS (DE 60 CMS DE ANCHO CADA TORRE). INCLUYE MELAMINA SUPERIOR (TIPO MALETERO). ESCRITORIO CON TRES CAJONES METABOX. BASE, ZOCLO (NO INCLUYE FORRADO DE FONDOS EN PAREDES) KIT DE HERRAJES, RIELES DE EXTENSION 18\" Y BISAGRAS REFORZADAS, JALADERAS DE NUESTRO CATALOGO, TUBOS Y SOPORTES. PROFUNDIDAD STANDARD 60 CMS Y ANCHO MAXIMO DE CADA MODULO 60 CMS. ELEMENTOS ADICIONALES, TORRES, REPISAS, DIVISORES, SE COTIZAN POR SEPARADO. CAMBIOS Y AJUSTES ALTERAN EL PRECIO COTIZADO.', NULL, 4, 27000, 108000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-531';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'FLETE DE INSTALACIÓN', 'INCLUYE GASTOS DE FLETE E INSTALACION FORANEO.', NULL, 1, 8000, 8000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-531';

-- QUO-532 / slug: imp-v2-quo-532 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-532';

-- QUO-533 / slug: imp-v2-quo-533 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-533';

-- QUO-534 / slug: imp-v2-quo-534 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-534';

-- QUO-535 / slug: imp-v2-quo-535 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura de 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-535';

-- QUO-536 / slug: imp-v2-quo-536 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 211 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-536';

-- QUO-537 / slug: imp-v2-quo-537 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-537';

-- QUO-538 / slug: imp-v2-quo-538 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-538';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD -175 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-538';

-- QUO-539 / slug: imp-v2-quo-539 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + torre zapatera con repisas fijas (5)', NULL, 1, 27400, 27400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-539';

-- QUO-540 / slug: imp-v2-quo-540 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 160 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-540';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD 160 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-540';

-- QUO-541 / slug: imp-v2-quo-541 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 173', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-541';

-- QUO-542 / slug: imp-v2-quo-542 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-542';

-- QUO-543 / slug: imp-v2-quo-543 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 3 - 240CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-543';

-- QUO-544 / slug: imp-v2-quo-544 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-544';

-- QUO-545 / slug: imp-v2-quo-545 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -155 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-545';

-- QUO-546 / slug: imp-v2-quo-546 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS SIN MALETERO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-546';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-546';

-- QUO-547 / slug: imp-v2-quo-547 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 260 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-547';

-- QUO-548 / slug: imp-v2-quo-548 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-548';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 200cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-548';

-- QUO-549 / slug: imp-v2-quo-549 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-549';

-- QUO-550 / slug: imp-v2-quo-550 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-550';

-- QUO-551 / slug: imp-v2-quo-551 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-551';

-- QUO-552 / slug: imp-v2-quo-552 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 178 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-552';

-- QUO-554 / slug: imp-v2-quo-554 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE CON REPISAS FIJAS (5) KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-554';

-- QUO-555 / slug: imp-v2-quo-555 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-555';

-- QUO-556 / slug: imp-v2-quo-556 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-556';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-556';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-556';

-- QUO-557 / slug: imp-v2-quo-557 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 415 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Hasta 2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-557';

-- QUO-558 / slug: imp-v2-quo-558 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 214 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-558';

-- QUO-559 / slug: imp-v2-quo-559 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. +Modulo de cajones (2) +Tocador con cajones (2) - NO incluye espejo', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-559';

-- QUO-561 / slug: imp-v2-quo-561 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-561';

-- QUO-562 / slug: imp-v2-quo-562 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-562';

-- QUO-563 / slug: imp-v2-quo-563 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-563';

-- QUO-564 / slug: imp-v2-quo-564 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-564';

-- QUO-565 / slug: imp-v2-quo-565 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-565';

-- QUO-566 / slug: imp-v2-quo-566 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-566';

-- QUO-567 / slug: imp-v2-quo-567 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 310 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-567';

-- QUO-568 / slug: imp-v2-quo-568 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -330 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-568';

-- QUO-569 / slug: imp-v2-quo-569 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 299 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-569';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-569';

-- QUO-570 / slug: imp-v2-quo-570 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -330 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Entrepaño extra en perfumero + Repizas Zapateras (3)', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-570';

-- QUO-571 / slug: imp-v2-quo-571 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-571';

-- QUO-572 / slug: imp-v2-quo-572 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-572';

-- QUO-573 / slug: imp-v2-quo-573 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 315 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-573';

-- QUO-574 / slug: imp-v2-quo-574 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-574';

-- QUO-576 / slug: imp-v2-quo-576 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-576';

-- QUO-577 / slug: imp-v2-quo-577 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 252 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-577';

-- QUO-578 / slug: imp-v2-quo-578 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-578';

-- QUO-579 / slug: imp-v2-quo-579 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-579';

-- QUO-580 / slug: imp-v2-quo-580 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR CON PUERTAS 130 X 270 X 130', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 4 CAJONES CU. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 45000, 45000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-580';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MODULO RETRACTIL (2)', 'DOS MODULOS TIPO ESPECIERO RETRACTIL (ACOMODO VERTICAL)', NULL, 1, 6500, 6500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-580';

-- QUO-581 / slug: imp-v2-quo-581 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-581';

-- QUO-582 / slug: imp-v2-quo-582 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-582';

-- QUO-583 / slug: imp-v2-quo-583 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-583';

-- QUO-584 / slug: imp-v2-quo-584 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 191 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-584';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 173 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-584';

-- QUO-585 / slug: imp-v2-quo-585 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 283 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-585';

-- QUO-586 / slug: imp-v2-quo-586 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 120 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-586';

-- QUO-587 / slug: imp-v2-quo-587 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN L 235 X 270 - SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Torre zapatera con puerta', NULL, 1, 35, 35
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-587';

-- QUO-589 / slug: imp-v2-quo-589 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 230 EN PARALELO', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) HASTA 2 TORRES CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29900, 29900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-589';

-- QUO-590 / slug: imp-v2-quo-590 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 177 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-590';

-- QUO-591 / slug: imp-v2-quo-591 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vertical Extra', NULL, 1, 21900, 21900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-591';

-- QUO-593 / slug: imp-v2-quo-593 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-593';

-- QUO-594 / slug: imp-v2-quo-594 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR CON PUERTAS EN MALETERO - 232 x 250', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + PUERTAS EN MALETEROS', NULL, 1, 49500, 49500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-594';

-- QUO-595 / slug: imp-v2-quo-595 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 208 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-595';

-- QUO-596 / slug: imp-v2-quo-596 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN PARALELO - 300 CMS Y 300 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE ZAPATERA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38500, 38500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-596';

-- QUO-597 / slug: imp-v2-quo-597 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 217 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-597';

-- QUO-598 / slug: imp-v2-quo-598 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 147 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13800, 13800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-598';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 256 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-598';

-- QUO-599 / slug: imp-v2-quo-599 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 226 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-599';

-- QUO-600 / slug: imp-v2-quo-600 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-600';

-- QUO-601 / slug: imp-v2-quo-601 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-601';

-- QUO-602 / slug: imp-v2-quo-602 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN L - 174 X 153 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-602';

-- QUO-603 / slug: imp-v2-quo-603 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, '1. CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U 441 X 223', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE ZAPATERA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + TORRE CAJONERA EXTRA ($3,500) + TORRE DE CHAROLAS (8) RETRACTILES ($8,500)', NULL, 1, 60000, 60000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-603';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, '2. CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN L 292 X 312', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + TORRE de charolas (6) extraibles ($6,500)', NULL, 1, 43500, 43500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-603';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, '3. CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U 397 X 270', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE ZAPATERA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + TORRE CAJONERA EXTRA ($3,500) + TORRE DE CHAROLAS (8) RETRACTILES ($8,500)', NULL, 1, 77000, 77000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-603';

-- QUO-604 / slug: imp-v2-quo-604 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-604';

-- QUO-605 / slug: imp-v2-quo-605 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN L + PUERTAS EN MALETERO', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. +TORRE ZAPATERA INDEPENDIENTE (+$4,500)', NULL, 1, 53500, 53500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-605';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR EN L SIN PUERTAS EN MALETERO', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. +TORRE ZAPATERA INDEPENDIENTE (+$4,500)', NULL, 1, 43000, 43000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-605';

-- QUO-606 / slug: imp-v2-quo-606 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET TRIPLAY DE CEDRO STANDARD - 178 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-606';

-- QUO-607 / slug: imp-v2-quo-607 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-607';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-607';

-- QUO-608 / slug: imp-v2-quo-608 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-608';

-- QUO-609 / slug: imp-v2-quo-609 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 2 Torres con 3 cajones de cada lado + 1 Repisa', NULL, 1, 36500, 36500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-609';

-- QUO-610 / slug: imp-v2-quo-610 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-610';

-- QUO-611 / slug: imp-v2-quo-611 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-611';

-- QUO-612 / slug: imp-v2-quo-612 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 247 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura segun diseño (310 cms) .✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vista superior a medida + Bisagras Cierre Lento (8)', NULL, 1, 25800, 25800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-612';

-- QUO-613 / slug: imp-v2-quo-613 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 168 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-613';

-- QUO-614 / slug: imp-v2-quo-614 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 270 x 178', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 27500, 27500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-614';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR CON PUERTAS - 270 X 178', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + TORRE ZAPATERA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 41000, 41000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-614';

-- QUO-615 / slug: imp-v2-quo-615 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 400 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vanity o Tocador + Nicho de TV + Cajones + Torre Zapatera', NULL, 1, 55500, 55500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-615';

-- QUO-616 / slug: imp-v2-quo-616 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L - 185 X 160', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-616';

-- QUO-617 / slug: imp-v2-quo-617 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms + Torre Zapatera ✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30500, 30500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-617';

-- QUO-618 / slug: imp-v2-quo-618 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-618';

-- QUO-619 / slug: imp-v2-quo-619 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 167 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-619';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 167 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-619';

-- QUO-620 / slug: imp-v2-quo-620 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-620';

-- QUO-621 / slug: imp-v2-quo-621 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-621';

-- QUO-622 / slug: imp-v2-quo-622 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 192 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-622';

-- QUO-623 / slug: imp-v2-quo-623 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - en L 200 x 400 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones Y Torre De Hasta 5 Repisas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 47000, 47000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-623';

-- QUO-624 / slug: imp-v2-quo-624 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 276 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-624';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 171 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-624';

-- QUO-625 / slug: imp-v2-quo-625 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 174 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-625';

-- QUO-626 / slug: imp-v2-quo-626 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-626';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 174 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-626';

-- QUO-627 / slug: imp-v2-quo-627 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-627';

-- QUO-628 / slug: imp-v2-quo-628 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-628';

-- QUO-629 / slug: imp-v2-quo-629 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 324 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Torre Zapatera Con Puerta + Escritorio + Nicho Decorativo + Lambrin', NULL, 1, 44000, 44000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-629';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS LINEAL - 250 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 REPISAS. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + MODULO DE 6 CAJONES', NULL, 1, 25700, 25700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-629';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MUEBLE DE BAÑO SEGUN DISEÑO - 147 CMS', 'NO INCLUYE CUBIERTA - PUERTAS, HERRAJES, JALADERAS A ELEGIR', NULL, 1, 8350, 8350
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-629';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'MUEBLE DE BAÑO SEGUN DISEÑO - 109 CMS', 'NO INCLUYE CUBIERTA - PUERTAS, HERRAJES, JALADERAS A ELEGIR', NULL, 1, 6200, 6200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-629';

-- QUO-630 / slug: imp-v2-quo-630 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -161 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-630';

-- QUO-631 / slug: imp-v2-quo-631 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 187 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-631';

-- QUO-632 / slug: imp-v2-quo-632 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 207 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 2 Torres Con Entrepaños', NULL, 1, 27300, 27300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-632';

-- QUO-633 / slug: imp-v2-quo-633 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 140 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13800, 13800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-633';

-- QUO-634 / slug: imp-v2-quo-634 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-634';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 175CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-634';

-- QUO-635 / slug: imp-v2-quo-635 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-635';

-- QUO-636 / slug: imp-v2-quo-636 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-636';

-- QUO-637 / slug: imp-v2-quo-637 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 195 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-637';

-- QUO-638 / slug: imp-v2-quo-638 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN L - 178 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Torre de Entrepaños', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-638';

-- QUO-639 / slug: imp-v2-quo-639 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-639';

-- QUO-640 / slug: imp-v2-quo-640 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-640';

-- QUO-641 / slug: imp-v2-quo-641 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-641';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA SIN PUERTAS SEGUN DISEÑO - 280 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32500, 32500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-641';

-- QUO-642 / slug: imp-v2-quo-642 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -239 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-642';

-- QUO-643 / slug: imp-v2-quo-643 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-643';

-- QUO-644 / slug: imp-v2-quo-644 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-644';

-- QUO-645 / slug: imp-v2-quo-645 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA + NICHO TV 350CMS', 'FRENTES, PUERTAS Y DOS TORRES CON HASTA 5 CAJONES + NICHO TV. BASE, ZOCLO Y MALETERO (NO INCLUYE FORRADO DE FONDOS EN PAREDES) KIT DE HERRAJES, RIELES DE EXTENSION 18\" Y BISAGRAS REFORZADAS DE CIERRE LENTO, JALADERAS DE NUESTRO CATALOGO, TUBOS Y SOPORTES. PROFUNDIDAD STANDARD 60 CMS Y ANCHO MAXIMO DE CADA MODULO 60 CMS. ELEMENTOS ADICIONALES, TORRES, REPISAS, DIVISORES, SE COTIZAN POR SEPARADO. CAMBIOS Y AJUSTES ALTERAN EL PRECIO COTIZADO. + ENTREPAÑOS (2)', NULL, 1, 40500, 40500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-645';

-- QUO-646 / slug: imp-v2-quo-646 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 282 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-646';

-- QUO-647 / slug: imp-v2-quo-647 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 246 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-647';

-- QUO-649 / slug: imp-v2-quo-649 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 229 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-649';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-649';

-- QUO-650 / slug: imp-v2-quo-650 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 244 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22100, 22100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-650';

-- QUO-651 / slug: imp-v2-quo-651 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR - 240 x 220 x 315 CMS', 'FRENTES, PUERTAS EN MALETERO DOS TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 59500, 59500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-651';

-- QUO-652 / slug: imp-v2-quo-652 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 155 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-652';

-- QUO-653 / slug: imp-v2-quo-653 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 262 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-653';

-- QUO-654 / slug: imp-v2-quo-654 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 260 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-654';

-- QUO-655 / slug: imp-v2-quo-655 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-655';

-- QUO-656 / slug: imp-v2-quo-656 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS en L - 229 x 227', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-656';

-- QUO-657 / slug: imp-v2-quo-657 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 187 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-657';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 90 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-657';

-- QUO-658 / slug: imp-v2-quo-658 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-658';

-- QUO-659 / slug: imp-v2-quo-659 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 193 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-659';

-- QUO-660 / slug: imp-v2-quo-660 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-660';

-- QUO-661 / slug: imp-v2-quo-661 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-661';

-- QUO-662 / slug: imp-v2-quo-662 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-662';

-- QUO-663 / slug: imp-v2-quo-663 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-663';

-- QUO-664 / slug: imp-v2-quo-664 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-664';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-664';

-- QUO-665 / slug: imp-v2-quo-665 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 380 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32500, 32500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-665';

-- QUO-666 / slug: imp-v2-quo-666 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-666';

-- QUO-667 / slug: imp-v2-quo-667 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-667';

-- QUO-668 / slug: imp-v2-quo-668 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-668';

-- QUO-669 / slug: imp-v2-quo-669 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-669';

-- QUO-670 / slug: imp-v2-quo-670 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-670';

-- QUO-671 / slug: imp-v2-quo-671 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-671';

-- QUO-672 / slug: imp-v2-quo-672 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-672';

-- QUO-673 / slug: imp-v2-quo-673 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 332 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-673';

-- QUO-674 / slug: imp-v2-quo-674 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 187 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-674';

-- QUO-675 / slug: imp-v2-quo-675 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 153 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. +TORRE ZAPATERA FIJA (8)', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-675';

-- QUO-676 / slug: imp-v2-quo-676 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 264 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-676';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 215 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-676';

-- QUO-677 / slug: imp-v2-quo-677 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + TOCADOR O VANITY', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-677';

-- QUO-678 / slug: imp-v2-quo-678 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-678';

-- QUO-679 / slug: imp-v2-quo-679 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-679';

-- QUO-680 / slug: imp-v2-quo-680 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 173 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-680';

-- QUO-681 / slug: imp-v2-quo-681 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 134 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-681';

-- QUO-682 / slug: imp-v2-quo-682 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 295 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-682';

-- QUO-683 / slug: imp-v2-quo-683 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 245 CMS + 149 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-683';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 315 CMS + ESCRITORIO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-683';

-- QUO-684 / slug: imp-v2-quo-684 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 125 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-684';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 125 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-684';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 125 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-684';

-- QUO-685 / slug: imp-v2-quo-685 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 125 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-685';

-- QUO-686 / slug: imp-v2-quo-686 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN U - 300 x 238 x 402 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 71400, 71400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-686';

-- QUO-687 / slug: imp-v2-quo-687 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16560, 16560
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-687';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15640, 15640
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-687';

-- QUO-688 / slug: imp-v2-quo-688 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 217 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 1 Torre Extra de Hasta 5 Cajones', NULL, 1, 24200, 24200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-688';

-- QUO-689 / slug: imp-v2-quo-689 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 184 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-689';

-- QUO-690 / slug: imp-v2-quo-690 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 290 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-690';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 290 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-690';

-- QUO-691 / slug: imp-v2-quo-691 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L - 200 X 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Torre Zapatera', NULL, 1, 29600, 29600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-691';

-- QUO-692 / slug: imp-v2-quo-692 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-692';

-- QUO-693 / slug: imp-v2-quo-693 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 350 CMS SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-693';

-- QUO-694 / slug: imp-v2-quo-694 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 350 CMS SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 33200, 33200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-694';

-- QUO-695 / slug: imp-v2-quo-695 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 130 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-695';

-- QUO-696 / slug: imp-v2-quo-696 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-696';

-- QUO-697 / slug: imp-v2-quo-697 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 475 CMS SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Nicho de TV al Centro', NULL, 1, 45100, 45100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-697';

-- QUO-698 / slug: imp-v2-quo-698 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-698';

-- QUO-699 / slug: imp-v2-quo-699 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 173 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-699';

-- QUO-700 / slug: imp-v2-quo-700 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-700';

-- QUO-701 / slug: imp-v2-quo-701 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-701';

-- QUO-702 / slug: imp-v2-quo-702 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + VANITY - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Vanity Con 2 Cajones', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-702';

-- QUO-703 / slug: imp-v2-quo-703 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 213 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-703';

-- QUO-704 / slug: imp-v2-quo-704 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-704';

-- QUO-705 / slug: imp-v2-quo-705 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SIN MALETERO - 263 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 210 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-705';

-- QUO-706 / slug: imp-v2-quo-706 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN U 200 X 300 X 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 43000, 43000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-706';

-- QUO-708 / slug: imp-v2-quo-708 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-708';

-- QUO-709 / slug: imp-v2-quo-709 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 303 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-709';

-- QUO-710 / slug: imp-v2-quo-710 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD + VANITY - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-710';

-- QUO-711 / slug: imp-v2-quo-711 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 305 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms c/u ✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31500, 31500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-711';

-- QUO-712 / slug: imp-v2-quo-712 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 348 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29900, 29900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-712';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'EXTRAS SEGUN DISEÑO', '+ TORRE DE 5 CAJONES ($4,500) + TORRE ZAPATERA ($3,500) + KIT DE ILUMINACIÓN LED EN TORRE ZAPATERA ($4,500)', NULL, 1, 12500, 12500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-712';

-- QUO-713 / slug: imp-v2-quo-713 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-713';

-- QUO-714 / slug: imp-v2-quo-714 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - EN L 235 X 270', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35500, 35500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-714';

-- QUO-715 / slug: imp-v2-quo-715 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 188 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-715';

-- QUO-716 / slug: imp-v2-quo-716 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-716';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21300, 21300
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-716';

-- QUO-717 / slug: imp-v2-quo-717 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-717';

-- QUO-718 / slug: imp-v2-quo-718 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas CIERRE LENTO, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.]', NULL, 1, 15600, 15600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-718';

-- QUO-719 / slug: imp-v2-quo-719 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 174 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-719';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 174 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-719';

-- QUO-720 / slug: imp-v2-quo-720 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-720';

-- QUO-721 / slug: imp-v2-quo-721 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 212 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19600, 19600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-721';

-- QUO-722 / slug: imp-v2-quo-722 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-722';

-- QUO-723 / slug: imp-v2-quo-723 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 183 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-723';

-- QUO-725 / slug: imp-v2-quo-725 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 247 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25800, 25800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-725';

-- QUO-726 / slug: imp-v2-quo-726 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-726';

-- QUO-727 / slug: imp-v2-quo-727 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L - 200 x 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25700, 25700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-727';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-727';

-- QUO-728 / slug: imp-v2-quo-728 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-728';

-- QUO-729 / slug: imp-v2-quo-729 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 193 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-729';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-729';

-- QUO-730 / slug: imp-v2-quo-730 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 278 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-730';

-- QUO-731 / slug: imp-v2-quo-731 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-731';

-- QUO-732 / slug: imp-v2-quo-732 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-732';

-- QUO-733 / slug: imp-v2-quo-733 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 382 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31100, 31100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-733';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN U - 249 x 199 x 249 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 56900, 56900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-733';

-- QUO-734 / slug: imp-v2-quo-734 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 194 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-734';

-- QUO-735 / slug: imp-v2-quo-735 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-735';

-- QUO-736 / slug: imp-v2-quo-736 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 265 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-736';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 215 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20100, 20100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-736';

-- QUO-737 / slug: imp-v2-quo-737 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-737';

-- QUO-738 / slug: imp-v2-quo-738 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 238 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-738';

-- QUO-739 / slug: imp-v2-quo-739 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-739';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 330 CMS SEGUN DISEÑO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-739';

-- QUO-740 / slug: imp-v2-quo-740 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 275 x 156', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39200, 39200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-740';

-- QUO-741 / slug: imp-v2-quo-741 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-741';

-- QUO-742 / slug: imp-v2-quo-742 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-742';

-- QUO-743 / slug: imp-v2-quo-743 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 215 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 3 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-743';

-- QUO-744 / slug: imp-v2-quo-744 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-744';

-- QUO-745 / slug: imp-v2-quo-745 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 177 CMS - ALEXIS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 4 Repisas Zapateras + 1 Repisa Extra en Perfumero', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-745';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VESTIDOR MELAMINA EN PARALELO - 220 CMS - KEILA', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES + UNA TORRE DE CON ENTREPAÑO, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. PUERTAS EN MALETERO ($16,000.00) TORRE EXTRA DE CAJONES ($4,500.00)', NULL, 1, 48000, 48000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-745';

-- QUO-746 / slug: imp-v2-quo-746 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-746';

-- QUO-747 / slug: imp-v2-quo-747 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 308 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-747';

-- QUO-748 / slug: imp-v2-quo-748 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-748';

-- QUO-749 / slug: imp-v2-quo-749 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 397 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 34200, 34200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-749';

-- QUO-750 / slug: imp-v2-quo-750 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-750';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 154 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-750';

-- QUO-751 / slug: imp-v2-quo-751 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 350 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29900, 29900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-751';

-- QUO-752 / slug: imp-v2-quo-752 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-752';

-- QUO-753 / slug: imp-v2-quo-753 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 188 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-753';

-- QUO-754 / slug: imp-v2-quo-754 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN LINEA - 200 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES , PUERTA COSMETIQUERA Y REPISA INTERIOR. + UNA TORRE CON ENTREPAÑOSKIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-754';

-- QUO-755 / slug: imp-v2-quo-755 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-755';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-755';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-755';

-- QUO-756 / slug: imp-v2-quo-756 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 210 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-756';

-- QUO-757 / slug: imp-v2-quo-757 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L - 115 X 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-757';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-757';

-- QUO-758 / slug: imp-v2-quo-758 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-758';

-- QUO-759 / slug: imp-v2-quo-759 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 164 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-759';

-- QUO-760 / slug: imp-v2-quo-760 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN L 230 X 220', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44500, 44500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-760';

-- QUO-761 / slug: imp-v2-quo-761 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 172 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16100, 16100
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-761';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE A MEDIDA - 107 CMS', 'MUEBLE DE BAÑO - PARA LAVAMANOS', NULL, 1, 5900, 5900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-761';

-- QUO-762 / slug: imp-v2-quo-762 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 195 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-762';

-- QUO-763 / slug: imp-v2-quo-763 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-763';

-- QUO-764 / slug: imp-v2-quo-764 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -239 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-764';

-- QUO-765 / slug: imp-v2-quo-765 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres de 5 repisas ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 31000, 31000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-765';

-- QUO-766 / slug: imp-v2-quo-766 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-766';

-- QUO-767 / slug: imp-v2-quo-767 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 335 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-767';

-- QUO-768 / slug: imp-v2-quo-768 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 164 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-768';

-- QUO-769 / slug: imp-v2-quo-769 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD SEGUN DISEÑO - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32200, 32200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-769';

-- QUO-770 / slug: imp-v2-quo-770 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR EN L 230 X 220', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44500, 44500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-770';

-- QUO-771 / slug: imp-v2-quo-771 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 145 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13800, 13800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-771';

-- QUO-772 / slug: imp-v2-quo-772 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-772';

-- QUO-773 / slug: imp-v2-quo-773 (5 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA REC 2 - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 330 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-773';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD REC 3 - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 330 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-773';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD REC VISITA - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 330 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-773';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'CLOSET MELAMINA WALK IN VESTIDOR EN L REC PRINCIPAL- 270 CMS X 285 CMS', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 55250, 55250
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-773';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 4, 'CLOSET MELAMINA WALK IN VESTIDOR EN L REC 5 - 270 CMS X 285 CMS', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 55250, 55250
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-773';

-- QUO-774 / slug: imp-v2-quo-774 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 218 CMS + 95 CMS MALETERO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-774';

-- QUO-775 / slug: imp-v2-quo-775 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-775';

-- QUO-776 / slug: imp-v2-quo-776 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 380 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 32800, 32800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-776';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 239 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-776';

-- QUO-777 / slug: imp-v2-quo-777 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-777';

-- QUO-778 / slug: imp-v2-quo-778 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones + Torre zapatera ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-778';

-- QUO-779 / slug: imp-v2-quo-779 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-779';

-- QUO-780 / slug: imp-v2-quo-780 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-780';

-- QUO-781 / slug: imp-v2-quo-781 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 275 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-781';

-- QUO-782 / slug: imp-v2-quo-782 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 246 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-782';

-- QUO-783 / slug: imp-v2-quo-783 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 330 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-783';

-- QUO-784 / slug: imp-v2-quo-784 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-784';

-- QUO-785 / slug: imp-v2-quo-785 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-785';

-- QUO-786 / slug: imp-v2-quo-786 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS LINEAL - 220 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-786';

-- QUO-787 / slug: imp-v2-quo-787 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-787';

-- QUO-788 / slug: imp-v2-quo-788 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + Repisa extra en perfumero ($250)', NULL, 1, 15250, 15250
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-788';

-- QUO-789 / slug: imp-v2-quo-789 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 276 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 290 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26500, 26500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-789';

-- QUO-790 / slug: imp-v2-quo-790 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-790';

-- QUO-791 / slug: imp-v2-quo-791 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 235 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-791';

-- QUO-792 / slug: imp-v2-quo-792 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -. 199 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-792';

-- QUO-793 / slug: imp-v2-quo-793 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-793';

-- QUO-794 / slug: imp-v2-quo-794 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD- 145 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13800, 13800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-794';

-- QUO-795 / slug: imp-v2-quo-795 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 206 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18600, 18600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-795';

-- QUO-796 / slug: imp-v2-quo-796 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS + VANITY 360 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + VANITY O TOCADOR CON DOS CAJONES ($5,000)', NULL, 1, 32800, 32800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-796';

-- QUO-797 / slug: imp-v2-quo-797 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 171 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-797';

-- QUO-798 / slug: imp-v2-quo-798 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 130 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-798';

-- QUO-799 / slug: imp-v2-quo-799 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 130 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Modulo de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 2, 14250, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-799';

-- QUO-800 / slug: imp-v2-quo-800 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-800';

-- QUO-801 / slug: imp-v2-quo-801 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 222 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-801';

-- QUO-802 / slug: imp-v2-quo-802 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 188 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18500, 18500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-802';

-- QUO-803 / slug: imp-v2-quo-803 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 100 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-803';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16900, 16900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-803';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19600, 19600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-803';

-- QUO-804 / slug: imp-v2-quo-804 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-804';

-- QUO-805 / slug: imp-v2-quo-805 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR + UNA TORRE DE REPISAS FIJAS (5) SIN PUERTA. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-805';

-- QUO-806 / slug: imp-v2-quo-806 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-806';

-- QUO-807 / slug: imp-v2-quo-807 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-807';

-- QUO-808 / slug: imp-v2-quo-808 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 179 x 211 x 181 + 211 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. + UNA TORRE DE REPISAS FIJAS (5) SIN PUERTA KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 50800, 50800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-808';

-- QUO-809 / slug: imp-v2-quo-809 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 245 CMS', 'FRENTES, PUERTAS Y UNA TORRE CON HASTA 3 CAJONES, NICHOS. BASE, ZOCLO Y MALETERO (NO INCLUYE FORRADO DE FONDOS EN PAREDES) KIT DE HERRAJES, RIELES DE EXTENSION 18\" Y BISAGRAS REFORZADAS, JALADERAS DE NUESTRO CATALOGO, TUBOS Y SOPORTES. PROFUNDIDAD STANDARD 60 CMS Y ANCHO MAXIMO DE CADA MODULO 60 CMS. ELEMENTOS ADICIONALES, TORRES, REPISAS, DIVISORES, SE COTIZAN POR SEPARADO. CAMBIOS Y AJUSTES ALTERAN EL PRECIO COTIZADO.', NULL, 1, 29500, 29500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-809';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 272 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones + Torre de repisas fijas (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros. + 1 Repisa en area de tubo ($350)', NULL, 1, 27850, 27850
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-809';

-- QUO-810 / slug: imp-v2-quo-810 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD- 194 CMS + CAJON EXTRA + 1 PUERTA CADA LADO', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18350, 18350
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-810';

-- QUO-813 / slug: imp-v2-quo-813 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN L 180 X 200 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 24400, 24400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-813';

-- QUO-814 / slug: imp-v2-quo-814 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23900, 23900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-814';

-- QUO-815 / slug: imp-v2-quo-815 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 244 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-815';

-- QUO-816 / slug: imp-v2-quo-816 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 298 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-816';

-- QUO-817 / slug: imp-v2-quo-817 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-817';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 185 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-817';

-- QUO-818 / slug: imp-v2-quo-818 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 294 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-818';

-- QUO-819 / slug: imp-v2-quo-819 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 290 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23000, 23000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-819';

-- QUO-820 / slug: imp-v2-quo-820 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-820';

-- QUO-821 / slug: imp-v2-quo-821 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-821';

-- QUO-822 / slug: imp-v2-quo-822 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-822';

-- QUO-823 / slug: imp-v2-quo-823 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-823';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-823';

-- QUO-824 / slug: imp-v2-quo-824 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14000, 14000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-824';

-- QUO-825 / slug: imp-v2-quo-825 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 126 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-825';

-- QUO-826 / slug: imp-v2-quo-826 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-826';

-- QUO-827 / slug: imp-v2-quo-827 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 430 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 37600, 37600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-827';

-- QUO-828 / slug: imp-v2-quo-828 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 195 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17900, 17900
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-828';

-- QUO-829 / slug: imp-v2-quo-829 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44600, 44600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-829';

-- QUO-830 / slug: imp-v2-quo-830 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-830';

-- QUO-831 / slug: imp-v2-quo-831 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 278 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-831';

-- QUO-832 / slug: imp-v2-quo-832 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-832';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L 214 X 163 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 28600, 28600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-832';

-- QUO-833 / slug: imp-v2-quo-833 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16400, 16400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-833';

-- QUO-834 / slug: imp-v2-quo-834 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 288 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25500, 25500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-834';

-- QUO-835 / slug: imp-v2-quo-835 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-835';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE DE ENTREPAÑOS EXTRA CON PUERTA', '', NULL, 1, 3500, 3500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-835';

-- QUO-836 / slug: imp-v2-quo-836 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-836';

-- QUO-837 / slug: imp-v2-quo-837 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21400, 21400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-837';

-- QUO-838 / slug: imp-v2-quo-838 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-838';

-- QUO-839 / slug: imp-v2-quo-839 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-839';

-- QUO-840 / slug: imp-v2-quo-840 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 275 x 352 x 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 60000, 60000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-840';

-- QUO-841 / slug: imp-v2-quo-841 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 232 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21600, 21600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-841';

-- QUO-842 / slug: imp-v2-quo-842 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 181 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-842';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 181 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-842';

-- QUO-843 / slug: imp-v2-quo-843 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 4 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅3 Entrepaños en total', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-843';

-- QUO-844 / slug: imp-v2-quo-844 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 256 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 23400, 23400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-844';

-- QUO-845 / slug: imp-v2-quo-845 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15400, 15400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-845';

-- QUO-846 / slug: imp-v2-quo-846 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ 1 Repiza Zapatera ($400.00)', NULL, 1, 16400, 16400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-846';

-- QUO-847 / slug: imp-v2-quo-847 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14600, 14600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-847';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 184 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-847';

-- QUO-848 / slug: imp-v2-quo-848 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18200, 18200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-848';

-- QUO-849 / slug: imp-v2-quo-849 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 268 CMS+ VANITY CON PLAFON Y 1 SPOT', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-849';

-- QUO-850 / slug: imp-v2-quo-850 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22400, 22400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-850';

-- QUO-851 / slug: imp-v2-quo-851 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 140 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14800, 14800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-851';

-- QUO-852 / slug: imp-v2-quo-852 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-852';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-852';

-- QUO-853 / slug: imp-v2-quo-853 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22500, 22500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-853';

-- QUO-854 / slug: imp-v2-quo-854 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 245 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22400, 22400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-854';

-- QUO-855 / slug: imp-v2-quo-855 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-855';

-- QUO-856 / slug: imp-v2-quo-856 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR CON PUERTAS EN MALETERO - 327 x 190', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 44600, 44600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-856';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN MALETERO - - 327 x 190', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-856';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 162 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-856';

-- QUO-857 / slug: imp-v2-quo-857 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 164 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19500, 19500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-857';

-- QUO-858 / slug: imp-v2-quo-858 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN L - 260 X 160 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-858';

-- QUO-859 / slug: imp-v2-quo-859 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 390 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 40000, 40000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-859';

-- QUO-860 / slug: imp-v2-quo-860 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-860';

-- QUO-861 / slug: imp-v2-quo-861 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN PARALELO - 450 CMS C/PARED', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 74000, 74000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-861';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN PARALELO - 450 CMS C/PARED', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE DE ENTREPAÑOS (5) KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 62000, 62000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-861';

-- QUO-862 / slug: imp-v2-quo-862 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17200, 17200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-862';

-- QUO-863 / slug: imp-v2-quo-863 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 267 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24000, 24000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-863';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE DE ENTREPAÑOS EXTRA CON PUERTA', 'MAXIMO 60 CMS DE ANCHO', NULL, 1, 4400, 4400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-863';

-- QUO-864 / slug: imp-v2-quo-864 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-864';

-- QUO-865 / slug: imp-v2-quo-865 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO CON NICHO DE TV - 262 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅ 2 Torres de 5 Entrepaños ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 35000, 35000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-865';

-- QUO-866 / slug: imp-v2-quo-866 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 225 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21000, 21000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-866';

-- QUO-867 / slug: imp-v2-quo-867 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 295 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26400, 26400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-867';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA SEGUN DISEÑO - 295 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅Area de escritorio o vanity con cajones (2)', NULL, 1, 29000, 29000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-867';

-- QUO-868 / slug: imp-v2-quo-868 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre de entrepaños (5) ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas Cierre Lento, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19600, 19600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-868';

-- QUO-869 / slug: imp-v2-quo-869 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 185 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-869';

-- QUO-871 / slug: imp-v2-quo-871 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 148 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-871';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 149 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-871';

-- QUO-872 / slug: imp-v2-quo-872 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-872';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD EN L - 100 X 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-872';

-- QUO-873 / slug: imp-v2-quo-873 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20400, 20400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-873';

-- QUO-874 / slug: imp-v2-quo-874 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 330 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30000, 30000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-874';

-- QUO-875 / slug: imp-v2-quo-875 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 193 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18600, 18600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-875';

-- QUO-876 / slug: imp-v2-quo-876 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 194 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18200, 18200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-876';

-- QUO-877 / slug: imp-v2-quo-877 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-877';

-- QUO-878 / slug: imp-v2-quo-878 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 338 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 29600, 29600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-878';

-- QUO-879 / slug: imp-v2-quo-879 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20800, 20800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-879';

-- QUO-880 / slug: imp-v2-quo-880 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-880';

-- QUO-881 / slug: imp-v2-quo-881 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-881';

-- QUO-882 / slug: imp-v2-quo-882 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 270 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 3 cajones c/u ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Nicho de TV de 55 Pulgadas✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 30200, 30200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-882';

-- QUO-883 / slug: imp-v2-quo-883 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 132 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-883';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VANITY', 'VANITY CON HASTA 2 CAJONES', NULL, 1, 5000, 5000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-883';

-- QUO-884 / slug: imp-v2-quo-884 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD -244 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅+ 2 Repizas Zapateras ✅No incluye forro de muros.', NULL, 1, 22800, 22800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-884';

-- QUO-885 / slug: imp-v2-quo-885 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22000, 22000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-885';

-- QUO-886 / slug: imp-v2-quo-886 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18400, 18400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-886';

-- QUO-887 / slug: imp-v2-quo-887 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-887';

-- QUO-888 / slug: imp-v2-quo-888 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17200, 17200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-888';

-- QUO-889 / slug: imp-v2-quo-889 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 100 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-889';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 100 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14500, 14500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-889';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-889';

-- QUO-890 / slug: imp-v2-quo-890 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20000, 20000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-890';

-- QUO-891 / slug: imp-v2-quo-891 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 210 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO. + 2 CAJONES EXTRAS', NULL, 1, 17800, 17800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-891';

-- QUO-892 / slug: imp-v2-quo-892 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-892';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 500 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 43000, 43000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-892';

-- QUO-893 / slug: imp-v2-quo-893 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 271 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Modulo Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ 2 Torres Cajoneras con Puerta de Ancho Maximo 60 cms ($8,000.00).', NULL, 1, 33000, 33000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-893';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 177 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-893';

-- QUO-894 / slug: imp-v2-quo-894 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 213 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19700, 19700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-894';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 204 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18600, 18600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-894';

-- QUO-895 / slug: imp-v2-quo-895 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 120 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 14600, 14600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-895';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MALETERO ARRIBA DE PUERTA', '', NULL, 1, 4000, 4000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-895';

-- QUO-896 / slug: imp-v2-quo-896 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19000, 19000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-896';

-- QUO-897 / slug: imp-v2-quo-897 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 148 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-897';

-- QUO-898 / slug: imp-v2-quo-898 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-898';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17500, 17500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-898';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR EN L - 270 X 160', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 45000, 45000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-898';

-- QUO-899 / slug: imp-v2-quo-899 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 205 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18750, 18750
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-899';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 205 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18750, 18750
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-899';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS - 220 X 110 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28500, 28500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-899';

-- QUO-900 / slug: imp-v2-quo-900 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 272 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ 5 Repisas Zapateras ($450.00 c/u)', NULL, 1, 26750, 26750
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-900';

-- QUO-901 / slug: imp-v2-quo-901 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 248 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 22600, 22600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-901';

-- QUO-902 / slug: imp-v2-quo-902 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ Nicho de TV', NULL, 1, 27000, 27000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-902';

-- QUO-903 / slug: imp-v2-quo-903 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 350 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅Torre de Entrepaños C/Puerta ancho máximo 60 cms. ✅ 3 Repisas Zapateras', NULL, 1, 49000, 49000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-903';

-- QUO-904 / slug: imp-v2-quo-904 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-904';

-- QUO-905 / slug: imp-v2-quo-905 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - ANCHO 315 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅+ Nicho Ventana', NULL, 1, 30600, 30600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-905';

-- QUO-906 / slug: imp-v2-quo-906 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-906';

-- QUO-907 / slug: imp-v2-quo-907 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA - ANCHO 80 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 4 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-907';

-- QUO-908 / slug: imp-v2-quo-908 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 165 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-908';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN L - 300 X 300 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 49500, 49500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-908';

-- QUO-909 / slug: imp-v2-quo-909 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR - 120 CMS', 'FRENTES, PUERTAS EN MALETERO UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-909';

-- QUO-910 / slug: imp-v2-quo-910 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 160 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-910';

-- QUO-911 / slug: imp-v2-quo-911 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18600, 18600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-911';

-- QUO-912 / slug: imp-v2-quo-912 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 192 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 3 cajones c/u ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅4 Repizas Zapateras Extras', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-912';

-- QUO-913 / slug: imp-v2-quo-913 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-913';

-- QUO-914 / slug: imp-v2-quo-914 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 190 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18000, 18000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-914';

-- QUO-915 / slug: imp-v2-quo-915 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-915';

-- QUO-916 / slug: imp-v2-quo-916 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 146 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15400, 15400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-916';

-- QUO-917 / slug: imp-v2-quo-917 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 204 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 13500, 13500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-917';

-- QUO-918 / slug: imp-v2-quo-918 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SEGUN DISEÑO - 300 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) DOS TORRES CON HASTA 4 CAJONES C/U, + UNA TORRE ZAPATERA O DE ENTREPAÑOS (5) . KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 31000, 31000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-918';

-- QUO-919 / slug: imp-v2-quo-919 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 186 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 6 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 18450, 18450
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-919';

-- QUO-920 / slug: imp-v2-quo-920 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17400, 17400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-920';

-- QUO-921 / slug: imp-v2-quo-921 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 157 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-921';

-- QUO-922 / slug: imp-v2-quo-922 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-922';

-- QUO-923 / slug: imp-v2-quo-923 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 166 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16000, 16000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-923';

-- QUO-924 / slug: imp-v2-quo-924 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 171 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-924';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 179 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16400, 16400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-924';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 172 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16200, 16200
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-924';

-- QUO-925 / slug: imp-v2-quo-925 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 240 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21400, 21400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-925';

-- QUO-926 / slug: imp-v2-quo-926 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 220 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-926';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 150 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15500, 15500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-926';

-- QUO-927 / slug: imp-v2-quo-927 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 170 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Torre de Entrepaños ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21400, 21400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-927';

-- QUO-928 / slug: imp-v2-quo-928 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 200 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 19400, 19400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-928';

-- QUO-929 / slug: imp-v2-quo-929 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA - 300 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26600, 26600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-929';

-- QUO-930 / slug: imp-v2-quo-930 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN L - 439 X 104 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-930';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 213 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20500, 20500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-930';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 230 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 21500, 21500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-930';

-- QUO-931 / slug: imp-v2-quo-931 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 130 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 15000, 15000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-931';

-- QUO-932 / slug: imp-v2-quo-932 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U - 150 X 250 X 110 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 32000, 32000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-932';

-- QUO-933 / slug: imp-v2-quo-933 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA SEGUN DISEÑO - 288 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de hasta 4 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅Nicho de TV', NULL, 1, 39500, 39500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-933';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'cajonera EXTRA', '', NULL, 1, 1500, 1500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-933';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'lambrín', '', NULL, 1, 2500, 2500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-933';

-- QUO-934 / slug: imp-v2-quo-934 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 280 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 25400, 25400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-934';

-- QUO-935 / slug: imp-v2-quo-935 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 290 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 26000, 26000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-935';

-- QUO-936 / slug: imp-v2-quo-936 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD EN L 200 X 274', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 39000, 39000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-936';

-- QUO-937 / slug: imp-v2-quo-937 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 364 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.✅Torre de Entrepaños ancho máximo 60 cms', NULL, 1, 36000, 36000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-937';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS LINEAL - 310 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE DE ENTREPAÑOS FIJOS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 28000, 28000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-937';

-- QUO-938 / slug: imp-v2-quo-938 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 250 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24600, 24600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-938';

-- QUO-939 / slug: imp-v2-quo-939 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'PUERTAS EN MALETERO', '', NULL, 1, 11400, 11400
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-939';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA WALK IN VESTIDOR SIN PUERTAS EN U - 176 X 281 X 176 CMS', 'FRENTES, BASES Y CARGADORES. (SIN PUERTAS EN MALETERO) UNA TORRE CON HASTA 5 CAJONES, PUERTA COSMETIQUERA Y REPISA INTERIOR. UNA TORRE ZAPATERA O DE ENTREPAÑOS FIJOS (5)KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 40800, 40800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-939';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA STANDARD - 256 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 300 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 24500, 24500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-939';

-- QUO-940 / slug: imp-v2-quo-940 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 168 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-940';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 169 cms', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-940';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'CLOSET MELAMINA WALK IN VESTIDOR EN L - 186 X 181 cms', 'FRENTES, PUERTAS EN MALETERO DOS TORRES CON HASTA 5 CAJONES C/U, PUERTA COSMETIQUERA Y REPISA INTERIOR. KIT DE HERRAJES Y BISAGRAS BIDIRECCIONALES, RIELES DE EXTENSION 18\" REFORZADOS, JALADERAS DE NUESTRO CATALOGO. PROFUNDIDAD STANDARD 60 CMS APROX. CAJON ADICIONAL $450. REPISAS O DISEÑO ADICIONALES, TORRES ADICIONALES, NO INCLUYE FORRO DE AREA DE PARED, SE COBRAN POR SEPARADO.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-940';

-- QUO-941 / slug: imp-v2-quo-941 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 350 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 370 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 34000, 34000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-941';

-- QUO-942 / slug: imp-v2-quo-942 (4 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 434 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 38000, 38000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-942';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'TORRE DE ENTREPAÑOS CON PUERTA DE CRISTAL', 'ANCHO MAXIMO 60 CMS / ALTURA MAXIMA 200 CMS', NULL, 1, 10000, 10000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-942';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'ESCRITORIO FLOTANTE CON CAJONES', '2 CAJONES DE ANCHO MAXIMO 60 CMS C/U', NULL, 1, 7000, 7000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-942';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 3, 'LAMBRIN', '', NULL, 1, 7000, 7000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-942';

-- QUO-943 / slug: imp-v2-quo-943 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 180 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 17000, 17000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-943';

-- QUO-944 / slug: imp-v2-quo-944 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 182 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-944';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'CLOSET MELAMINA STANDARD - 183 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16600, 16600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-944';

-- QUO-945 / slug: imp-v2-quo-945 (2 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA EN PARALELO - 290 X 215 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅2 Torres Cajoneras de 5 cajones c/u ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 42000, 42000
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-945';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'MUEBLE LAVANDERIA - 289 CMS', 'GABINETES SUPERIORES DE LAVANDERIA PROFUNDIDAD DE 45 CMS, INCLUYE 2 REPISAS INTERIORES', NULL, 1, 11500, 11500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-945';

-- QUO-946 / slug: imp-v2-quo-946 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 162 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-946';

-- QUO-947 / slug: imp-v2-quo-947 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 175 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-947';

-- QUO-948 / slug: imp-v2-quo-948 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 161 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16500, 16500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-948';

-- QUO-949 / slug: imp-v2-quo-949 (3 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 167 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 16800, 16800
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-949';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 1, 'VANITY CON HASTA 5 CAJONES', '', NULL, 1, 7500, 7500
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-949';
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 2, 'MALETERO ARRIBA DE VENTANA', '', NULL, 1, 3700, 3700
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-949';

-- QUO-950 / slug: imp-v2-quo-950 (1 items)
INSERT INTO cotizacion_lineas (cotizacion_id, venta_id, articulo_id, orden, titulo, descripcion, sku, cantidad, precio_unit, subtotal)
SELECT c.id, NULL, NULL, 0, 'CLOSET MELAMINA STANDARD - 225 CMS', 'Closet en Melamina 👗👔👜 Catálogo Standard. Incluye:✅Closet Empotrado✅Torre Cajonera de 5 cajones ancho máximo 60 cms✅Puertas Principales ancho máximo 60 cms.✅Puertas en Maletero ancho máximo 60 cms.✅Closet Profundidad Standard 62 cms máximo.✅Altura máxima 270 cms.✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.✅Base y Zoclo (no se ve el piso).✅No incluye forro de muros.', NULL, 1, 20600, 20600
FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-950';

SET FOREIGN_KEY_CHECKS = 1;
