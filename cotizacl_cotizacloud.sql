-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 13, 2026 at 01:11 AM
-- Server version: 10.5.26-MariaDB
-- PHP Version: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cotizacl_cotizacloud`
--

-- --------------------------------------------------------

--
-- Table structure for table `articulos`
--

CREATE TABLE `articulos` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `sku` varchar(60) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` longtext DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unidad` varchar(30) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `orden` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articulos`
--

INSERT INTO `articulos` (`id`, `empresa_id`, `sku`, `titulo`, `descripcion`, `precio`, `unidad`, `activo`, `created_at`, `updated_at`, `orden`) VALUES
(1, 2, NULL, 'CLOSET MELAMINA STADARD', 'Closet en Melamina 👗👔👜 Catálogo Standard.\nIncluye:✅Closet Empotrado</P><P>✅Torre Cajonera de 5 cajones ancho máximo 60 cms\n✅Puertas Principales ancho máximo 60 cms.\n✅Puertas en Maletero ancho máximo 60 cms.\n✅Closet Profundidad Standard 62 cms máximo.\n✅Altura máxima 270 cms.\n✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.\n✅Base y Zoclo (no se ve el piso).\n✅No incluye forro de muros.', 9600.00, NULL, 1, '2026-03-10 14:34:55', '2026-03-10 14:34:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `categorias_costos`
--

CREATE TABLE `categorias_costos` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6b7280',
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorias_costos`
--

INSERT INTO `categorias_costos` (`id`, `empresa_id`, `nombre`, `color`, `activa`, `created_at`) VALUES
(1, 2, 'Material extra', '#3b82f6', 1, '2026-03-10 13:13:59'),
(2, 2, 'Mano de obra', '#10b981', 1, '2026-03-10 13:13:59'),
(3, 2, 'Transporte', '#8b5cf6', 1, '2026-03-10 13:13:59'),
(4, 2, 'Instalación', '#f59e0b', 1, '2026-03-10 13:13:59'),
(5, 2, 'Garantía / servicio', '#06b6d4', 1, '2026-03-10 13:13:59'),
(6, 2, 'Material', '#f97316', 1, '2026-03-11 23:05:32');

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `direccion` varchar(300) DEFAULT NULL,
  `nota` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `empresa_id`, `nombre`, `telefono`, `email`, `ciudad`, `notas`, `activo`, `created_at`, `updated_at`, `usuario_id`, `direccion`, `nota`) VALUES
(1, 2, 'Alfonso Medina', '6621421859', NULL, NULL, NULL, 1, '2026-03-10 16:02:08', '2026-03-10 16:02:08', 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `numero` varchar(30) DEFAULT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `cupon_id` int(10) UNSIGNED DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `token` char(64) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `notas_internas` text DEFAULT NULL,
  `notas_cliente` text DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cupon_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cupon_codigo` varchar(60) DEFAULT NULL,
  `cupon_amt` decimal(12,2) NOT NULL DEFAULT 0.00,
  `impuesto_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `impuesto_modo` enum('ninguno','suma','incluido') NOT NULL DEFAULT 'ninguno',
  `impuesto_amt` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('borrador','enviada','vista','aceptada','rechazada','aceptada_cliente','rechazada_cliente','convertida') NOT NULL DEFAULT 'borrador',
  `motivo_rechazo` varchar(255) DEFAULT NULL,
  `enviada_at` datetime DEFAULT NULL,
  `vista_at` datetime DEFAULT NULL,
  `accion_at` datetime DEFAULT NULL,
  `aceptada_at` datetime DEFAULT NULL,
  `rechazada_at` datetime DEFAULT NULL,
  `rechazada_motivo` varchar(255) DEFAULT NULL,
  `valida_hasta` datetime DEFAULT NULL,
  `ultima_vista_at` datetime DEFAULT NULL,
  `radar_bucket` varchar(40) DEFAULT NULL,
  `radar_score` tinyint(3) UNSIGNED DEFAULT NULL,
  `radar_senales` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`radar_senales`)),
  `radar_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `visitas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `descuento_auto_activo` tinyint(1) NOT NULL DEFAULT 0,
  `descuento_auto_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `descuento_auto_dias` smallint(6) NOT NULL DEFAULT 3,
  `descuento_auto_expira` datetime DEFAULT NULL,
  `descuento_auto_amt` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cupon_monto` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cotizaciones`
--

INSERT INTO `cotizaciones` (`id`, `numero`, `empresa_id`, `cliente_id`, `usuario_id`, `cupon_id`, `titulo`, `slug`, `token`, `descripcion`, `notas_internas`, `notas_cliente`, `subtotal`, `cupon_pct`, `cupon_codigo`, `cupon_amt`, `impuesto_pct`, `impuesto_modo`, `impuesto_amt`, `total`, `estado`, `motivo_rechazo`, `enviada_at`, `vista_at`, `accion_at`, `aceptada_at`, `rechazada_at`, `rechazada_motivo`, `valida_hasta`, `ultima_vista_at`, `radar_bucket`, `radar_score`, `radar_senales`, `radar_updated_at`, `created_at`, `updated_at`, `visitas`, `descuento_auto_activo`, `descuento_auto_pct`, `descuento_auto_dias`, `descuento_auto_expira`, `descuento_auto_amt`, `cupon_monto`) VALUES
(13, 'COT-2026-0004', 2, 1, 2, NULL, 'Ramón 417', 'ramon-417', '20a6ab67ddbcf1cba989d062695e586b5029701c4090100ae61c1789f83904c9', NULL, '', '', 19300.00, 0.00, NULL, 0.00, 16.00, 'ninguno', 0.00, 18432.00, 'aceptada', NULL, '2026-03-10 21:13:18', '2026-03-10 22:11:33', '2026-03-11 14:16:35', NULL, NULL, NULL, '2026-04-10 00:00:00', '2026-03-11 15:20:14', 'validando_precio', 19, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_rev\":{\"pts\":8,\"desc\":\"Volvi\\u00f3 a revisar totales\"},\"cupon\":{\"pts\":6,\"desc\":\"Intent\\u00f3 aplicar cup\\u00f3n\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"reciente\":{\"pts\":12,\"desc\":\"Visit\\u00f3 hace menos de 1h\"}},\"buckets\":[\"probable_cierre\",\"validando_precio\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":1,\"views48\":1,\"span48h\":\"0h\",\"pss\":8.5,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-11 01:21:40', '2026-03-10 21:13:18', '2026-03-11 22:29:56', 26, 0, 0.00, 3, '2026-03-14 10:00:47', 0.00, 0.00),
(14, 'COT-2026-0005', 2, 1, 2, NULL, 'Cocina L', 'cocina-l', '1b45574a43dc66b90f17f0c655e39d271369ec344b50a8d6733392e0bf007e89', NULL, '', '', 9800.00, 0.00, NULL, 0.00, 16.00, 'ninguno', 0.00, 9800.00, 'enviada', NULL, '2026-03-12 14:47:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 14:47:50', '2026-03-12 14:48:33', 0, 0, 0.00, 3, NULL, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `cotizacion_archivos`
--

CREATE TABLE `cotizacion_archivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `mime_type` varchar(80) DEFAULT NULL,
  `tamano_bytes` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cotizacion_lineas`
--

CREATE TABLE `cotizacion_lineas` (
  `id` int(10) UNSIGNED NOT NULL,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `articulo_id` int(10) UNSIGNED DEFAULT NULL,
  `orden` smallint(6) NOT NULL DEFAULT 0,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `sku` varchar(60) DEFAULT NULL,
  `cantidad` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `precio_unit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cotizacion_lineas`
--

INSERT INTO `cotizacion_lineas` (`id`, `cotizacion_id`, `articulo_id`, `orden`, `titulo`, `descripcion`, `sku`, `cantidad`, `precio_unit`, `subtotal`) VALUES
(36, 13, NULL, 1, 'CLOSET MELAMINA STADARD', 'Closet en Melamina 👗👔👜 Catálogo Standard.\nIncluye:\n✅Closet Empotrado\n✅Torre Cajonera de 5 cajones ancho máximo 60 cms\n✅Puertas Principales ancho máximo 60 cms.\n✅Puertas en Maletero ancho máximo 60 cms.\n✅Closet Profundidad Standard 62 cms máximo.\n✅Altura máxima 270 cms.\n✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.\n✅Base y Zoclo (no se ve el piso).\n✅No incluye forro de muros.', '', 2.0000, 9650.00, 19300.00),
(38, 14, 1, 1, 'CLOSET MELAMINA STADARD', 'Closet en Melamina 👗👔👜 Catálogo Standard.\nIncluye:✅Closet Empotrado</P><P>✅Torre Cajonera de 5 cajones ancho máximo 60 cms\n✅Puertas Principales ancho máximo 60 cms.\n✅Puertas en Maletero ancho máximo 60 cms.\n✅Closet Profundidad Standard 62 cms máximo.\n✅Altura máxima 270 cms.\n✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.\n✅Base y Zoclo (no se ve el piso).\n✅No incluye forro de muros.', '', 1.0000, 9800.00, 9800.00);

-- --------------------------------------------------------

--
-- Table structure for table `cotizacion_log`
--

CREATE TABLE `cotizacion_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `evento` varchar(80) NOT NULL,
  `detalle` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `accion` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cotizacion_log`
--

INSERT INTO `cotizacion_log` (`id`, `cotizacion_id`, `usuario_id`, `evento`, `detalle`, `ip`, `created_at`, `accion`) VALUES
(4, 13, 2, '', NULL, '187.245.114.71', '2026-03-10 21:13:18', 'enviada'),
(5, 13, 2, '', NULL, '187.245.114.71', '2026-03-11 12:41:20', 'editada'),
(6, 13, 2, '', NULL, '187.245.114.71', '2026-03-11 12:41:31', 'editada'),
(7, 13, 2, '', NULL, '187.245.114.71', '2026-03-11 13:00:47', 'editada'),
(8, 13, NULL, 'aceptada', 'Aceptada por: Alfonso Medina | Total: $18,432.00', NULL, '2026-03-11 14:16:35', NULL),
(9, 14, 2, '', NULL, '189.173.176.164', '2026-03-12 14:47:50', 'enviada'),
(10, 14, 2, '', NULL, '189.173.176.164', '2026-03-12 14:48:33', 'editada');

-- --------------------------------------------------------

--
-- Table structure for table `cupones`
--

CREATE TABLE `cupones` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(60) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `usos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `usos_max` int(10) UNSIGNED DEFAULT NULL,
  `vencimiento_tipo` enum('nunca','fecha_fija','dias_cotizacion') NOT NULL DEFAULT 'nunca',
  `vencimiento_dias` smallint(6) DEFAULT NULL,
  `vencimiento_fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cupones`
--

INSERT INTO `cupones` (`id`, `empresa_id`, `codigo`, `descripcion`, `porcentaje`, `activo`, `created_at`, `usos`, `usos_max`, `vencimiento_tipo`, `vencimiento_dias`, `vencimiento_fecha`) VALUES
(1, 2, 'MELAMINA', NULL, 4.00, 1, '2026-03-10 14:35:16', 0, NULL, 'nunca', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `empresas`
--

CREATE TABLE `empresas` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(60) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'MXN',
  `impuesto_modo` enum('ninguno','suma','incluido') NOT NULL DEFAULT 'ninguno',
  `impuesto_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `impuesto_nombre` varchar(20) NOT NULL DEFAULT 'IVA',
  `texto_bienvenida` text DEFAULT NULL,
  `texto_aceptar` text DEFAULT NULL,
  `texto_rechazar` text DEFAULT NULL,
  `texto_recibo` text DEFAULT NULL,
  `adc_activo` tinyint(1) NOT NULL DEFAULT 0,
  `adc_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `adc_horas` smallint(6) NOT NULL DEFAULT 72,
  `adc_texto` varchar(255) DEFAULT NULL,
  `radar_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`radar_config`)),
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `direccion` varchar(500) DEFAULT NULL,
  `rfc` varchar(20) DEFAULT NULL,
  `website` varchar(120) DEFAULT NULL,
  `notif_email` varchar(255) DEFAULT NULL,
  `notif_email_acepta` tinyint(1) NOT NULL DEFAULT 0,
  `notif_email_rechaza` tinyint(1) NOT NULL DEFAULT 0,
  `cot_vigencia_dias` smallint(6) NOT NULL DEFAULT 30,
  `allow_precio_edit` tinyint(1) NOT NULL DEFAULT 1,
  `cot_msg_acepta` text DEFAULT NULL,
  `cot_msg_rechaza` text DEFAULT NULL,
  `cot_terminos` text DEFAULT NULL,
  `cot_footer` text DEFAULT NULL,
  `vta_terminos` text DEFAULT NULL,
  `vta_footer` text DEFAULT NULL,
  `cot_prefijo` varchar(10) NOT NULL DEFAULT 'COT'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `empresas`
--

INSERT INTO `empresas` (`id`, `slug`, `nombre`, `logo_url`, `email`, `telefono`, `ciudad`, `moneda`, `impuesto_modo`, `impuesto_pct`, `impuesto_nombre`, `texto_bienvenida`, `texto_aceptar`, `texto_rechazar`, `texto_recibo`, `adc_activo`, `adc_pct`, `adc_horas`, `adc_texto`, `radar_config`, `activa`, `created_at`, `updated_at`, `direccion`, `rfc`, `website`, `notif_email`, `notif_email_acepta`, `notif_email_rechaza`, `cot_vigencia_dias`, `allow_precio_edit`, `cot_msg_acepta`, `cot_msg_rechaza`, `cot_terminos`, `cot_footer`, `vta_terminos`, `vta_footer`, `cot_prefijo`) VALUES
(2, 'closetfactory', 'Closet Factory Hermosillo', NULL, 'info@closetfactory.com.mx', '6624550498', 'Hermosillo', 'MXN', 'ninguno', 16.00, 'IVA', NULL, NULL, NULL, NULL, 0, 0.00, 72, NULL, NULL, 1, '2026-03-10 13:13:59', '2026-03-10 14:33:21', '', '', 'closetfactory.com.mx', 'info@closetfactory.com.mx', 1, 1, 30, 1, 'Gracias por su compra, sera contactado por el asesor', 'Una pena, esperamos verlo pronto', '', '', '', '', 'COT');

-- --------------------------------------------------------

--
-- Table structure for table `folios`
--

CREATE TABLE `folios` (
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `anio` smallint(6) NOT NULL,
  `ultimo` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `folios`
--

INSERT INTO `folios` (`empresa_id`, `tipo`, `anio`, `ultimo`) VALUES
(2, 'COT', 2026, 5);

-- --------------------------------------------------------

--
-- Table structure for table `gastos_venta`
--

CREATE TABLE `gastos_venta` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `venta_id` int(10) UNSIGNED NOT NULL,
  `categoria_id` int(10) UNSIGNED DEFAULT NULL,
  `concepto` varchar(255) NOT NULL,
  `importe` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `nota` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gastos_venta`
--

INSERT INTO `gastos_venta` (`id`, `empresa_id`, `venta_id`, `categoria_id`, `concepto`, `importe`, `fecha`, `nota`, `created_at`) VALUES
(1, 2, 1, 1, 'tubos', 100.00, '2026-03-11', '', '2026-03-11 22:48:58'),
(2, 2, 1, 2, '2 horas', 500.00, '2026-03-11', '', '2026-03-11 22:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `quote_events`
--

CREATE TABLE `quote_events` (
  `id` int(10) UNSIGNED NOT NULL,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `visitor_id` varchar(64) DEFAULT NULL,
  `session_id` varchar(36) DEFAULT NULL,
  `page_id` varchar(36) DEFAULT NULL,
  `tipo` varchar(60) NOT NULL,
  `max_scroll` tinyint(3) UNSIGNED DEFAULT NULL,
  `open_ms` int(10) UNSIGNED DEFAULT NULL,
  `visible_ms` int(10) UNSIGNED DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `ua` varchar(255) DEFAULT NULL,
  `ts_unix` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quote_events`
--

INSERT INTO `quote_events` (`id`, `cotizacion_id`, `visitor_id`, `session_id`, `page_id`, `tipo`, `max_scroll`, `open_ms`, `visible_ms`, `ip`, `ua`, `ts_unix`, `created_at`) VALUES
(1, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5a0e8f48-fc91-42c7-bf7e-0d6ca46a5948', '8c22979a-73c1-4b0f-8174-15a041ebe4ce', 'quote_open', 0, 10, 10, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773195093, '2026-03-10 22:11:33'),
(2, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5a0e8f48-fc91-42c7-bf7e-0d6ca46a5948', '8c22979a-73c1-4b0f-8174-15a041ebe4ce', 'promo_timer_present', 0, 11, 11, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773195093, '2026-03-10 22:11:33'),
(3, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5a0e8f48-fc91-42c7-bf7e-0d6ca46a5948', '8c22979a-73c1-4b0f-8174-15a041ebe4ce', 'section_view_totals', 5, 1059, 1059, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773195094, '2026-03-10 22:11:34'),
(4, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5a0e8f48-fc91-42c7-bf7e-0d6ca46a5948', '8c22979a-73c1-4b0f-8174-15a041ebe4ce', 'quote_price_review_loop', 21, 1093, 1093, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773195094, '2026-03-10 22:11:34'),
(5, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5a0e8f48-fc91-42c7-bf7e-0d6ca46a5948', '8c22979a-73c1-4b0f-8174-15a041ebe4ce', 'quote_scroll', 53, 1177, 1177, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773195094, '2026-03-10 22:11:34'),
(6, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5a0e8f48-fc91-42c7-bf7e-0d6ca46a5948', '8c22979a-73c1-4b0f-8174-15a041ebe4ce', 'quote_scroll', 93, 1243, 1243, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773195094, '2026-03-10 22:11:34'),
(7, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5a0e8f48-fc91-42c7-bf7e-0d6ca46a5948', '8c22979a-73c1-4b0f-8174-15a041ebe4ce', 'quote_close', 100, 15629, 15629, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773195109, '2026-03-10 22:11:49'),
(8, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'quote_open', 0, 10, 10, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773202846, '2026-03-11 00:20:46'),
(9, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'promo_timer_present', 0, 12, 12, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773202846, '2026-03-11 00:20:46'),
(10, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'section_view_totals', 6, 1373, 1373, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773202847, '2026-03-11 00:20:47'),
(11, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'quote_price_review_loop', 17, 1407, 1407, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773202847, '2026-03-11 00:20:47'),
(12, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'quote_scroll', 50, 1557, 1557, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773202848, '2026-03-11 00:20:48'),
(13, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'quote_scroll', 90, 1790, 1790, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773202848, '2026-03-11 00:20:48'),
(14, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'section_revisit_totals', 100, 5459, 5459, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773202851, '2026-03-11 00:20:51'),
(15, 13, '528751ce-1117-4625-b269-a107c7170a36', 'ec4f10d8-46fd-4ed3-b4ea-9f87660f0330', '3f6e4b0b-8c7b-4e26-b00e-47f31108101b', 'promo_timer_present', 0, 16, 16, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773202877, '2026-03-11 00:21:17'),
(16, 13, '528751ce-1117-4625-b269-a107c7170a36', 'ec4f10d8-46fd-4ed3-b4ea-9f87660f0330', '3f6e4b0b-8c7b-4e26-b00e-47f31108101b', 'quote_open', 0, 13, 13, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773202877, '2026-03-11 00:21:17'),
(17, 13, '528751ce-1117-4625-b269-a107c7170a36', '7df92615-2ac0-458a-9490-36b3fc0391ea', '33728cc7-fe06-413f-a62f-217d5644c55f', 'quote_open', 0, 14, 14, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773202887, '2026-03-11 00:21:27'),
(18, 13, '528751ce-1117-4625-b269-a107c7170a36', '7df92615-2ac0-458a-9490-36b3fc0391ea', '33728cc7-fe06-413f-a62f-217d5644c55f', 'promo_timer_present', 0, 14, 14, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773202887, '2026-03-11 00:21:27'),
(19, 13, '528751ce-1117-4625-b269-a107c7170a36', '7df92615-2ac0-458a-9490-36b3fc0391ea', '33728cc7-fe06-413f-a62f-217d5644c55f', 'section_view_totals', 47, 1614, 1614, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773202889, '2026-03-11 00:21:29'),
(20, 13, '528751ce-1117-4625-b269-a107c7170a36', '7df92615-2ac0-458a-9490-36b3fc0391ea', '33728cc7-fe06-413f-a62f-217d5644c55f', 'quote_scroll', 51, 1647, 1647, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773202889, '2026-03-11 00:21:29'),
(21, 13, '528751ce-1117-4625-b269-a107c7170a36', '7df92615-2ac0-458a-9490-36b3fc0391ea', '33728cc7-fe06-413f-a62f-217d5644c55f', 'quote_price_review_loop', 51, 1648, 1648, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773202889, '2026-03-11 00:21:29'),
(22, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'section_revisit_totals', 100, 355347, 19503, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773203202, '2026-03-11 00:26:42'),
(23, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'section_revisit_totals', 100, 1124004, 26071, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773203970, '2026-03-11 00:39:30'),
(24, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', 'quote_close', 100, 1899015, 39996, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204745, '2026-03-11 00:52:25'),
(25, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '7ee2d042-aa7e-4812-9a10-2b59d4defd1a', 'promo_timer_present', 0, 7, 7, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204746, '2026-03-11 00:52:26'),
(26, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '7ee2d042-aa7e-4812-9a10-2b59d4defd1a', 'quote_open', 0, 6, 6, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204746, '2026-03-11 00:52:26'),
(27, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '7ee2d042-aa7e-4812-9a10-2b59d4defd1a', 'section_view_totals', 6, 1523, 1523, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204747, '2026-03-11 00:52:27'),
(28, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '7ee2d042-aa7e-4812-9a10-2b59d4defd1a', 'quote_price_review_loop', 10, 1557, 1557, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204747, '2026-03-11 00:52:27'),
(29, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '7ee2d042-aa7e-4812-9a10-2b59d4defd1a', 'quote_scroll', 53, 3490, 3490, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204749, '2026-03-11 00:52:29'),
(30, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '7ee2d042-aa7e-4812-9a10-2b59d4defd1a', 'quote_scroll', 91, 3690, 3690, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204749, '2026-03-11 00:52:29'),
(31, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '7ee2d042-aa7e-4812-9a10-2b59d4defd1a', 'quote_close', 100, 13842, 13842, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204759, '2026-03-11 00:52:39'),
(32, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', 'da675140-98f4-4d16-9d06-c6fbd32081d8', 'quote_open', 0, 5, 5, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204760, '2026-03-11 00:52:40'),
(33, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', 'da675140-98f4-4d16-9d06-c6fbd32081d8', 'promo_timer_present', 0, 6, 6, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204760, '2026-03-11 00:52:40'),
(34, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', 'da675140-98f4-4d16-9d06-c6fbd32081d8', 'quote_close', 0, 5740, 5740, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773204765, '2026-03-11 00:52:45'),
(35, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', 'e023772e-c1f5-4931-890f-f90b61bb3eb2', 'quote_open', 0, 13, 13, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204784, '2026-03-11 00:53:04'),
(36, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', 'e023772e-c1f5-4931-890f-f90b61bb3eb2', 'promo_timer_present', 0, 13, 13, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204784, '2026-03-11 00:53:04'),
(37, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', 'e023772e-c1f5-4931-890f-f90b61bb3eb2', 'section_view_totals', 45, 1797, 1797, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204786, '2026-03-11 00:53:06'),
(38, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', 'e023772e-c1f5-4931-890f-f90b61bb3eb2', 'quote_price_review_loop', 49, 1964, 1964, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204786, '2026-03-11 00:53:06'),
(39, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', 'e023772e-c1f5-4931-890f-f90b61bb3eb2', 'quote_scroll', 51, 1976, 1976, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204786, '2026-03-11 00:53:06'),
(40, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', 'e023772e-c1f5-4931-890f-f90b61bb3eb2', 'quote_scroll', 90, 2826, 2826, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204787, '2026-03-11 00:53:07'),
(41, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', 'e023772e-c1f5-4931-890f-f90b61bb3eb2', 'section_revisit_totals', 100, 4285, 4285, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204788, '2026-03-11 00:53:08'),
(42, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', 'e023772e-c1f5-4931-890f-f90b61bb3eb2', 'quote_close', 100, 6774, 6774, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204791, '2026-03-11 00:53:11'),
(43, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', '9e7457af-7248-42e8-b427-18c8d211e858', 'quote_open', 0, 13, 13, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204791, '2026-03-11 00:53:11'),
(44, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', '9e7457af-7248-42e8-b427-18c8d211e858', 'promo_timer_present', 0, 14, 14, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204791, '2026-03-11 00:53:11'),
(45, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', '9e7457af-7248-42e8-b427-18c8d211e858', 'quote_scroll', 100, 33, 33, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204791, '2026-03-11 00:53:11'),
(46, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', '9e7457af-7248-42e8-b427-18c8d211e858', 'quote_scroll', 100, 32, 32, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204791, '2026-03-11 00:53:11'),
(47, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', '9e7457af-7248-42e8-b427-18c8d211e858', 'section_view_totals', 100, 1164, 1164, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204792, '2026-03-11 00:53:12'),
(48, 13, '528751ce-1117-4625-b269-a107c7170a36', '21c2b86a-66c7-4f6f-9f8e-a56eefbd9c2f', '9e7457af-7248-42e8-b427-18c8d211e858', 'quote_price_review_loop', 100, 1264, 1264, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204792, '2026-03-11 00:53:12'),
(49, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', '3d044798-d523-40ee-9991-3c120c8b0fc5', 'quote_open', 0, 4, 4, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204798, '2026-03-11 00:53:18'),
(50, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', '3d044798-d523-40ee-9991-3c120c8b0fc5', 'promo_timer_present', 0, 5, 5, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204798, '2026-03-11 00:53:18'),
(51, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', '3d044798-d523-40ee-9991-3c120c8b0fc5', 'quote_close', 0, 2073, 2073, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204800, '2026-03-11 00:53:20'),
(52, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', 'd802dce9-f16f-4635-9db5-f10ed0a18c41', 'quote_open', 0, 0, 0, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204800, '2026-03-11 00:53:20'),
(53, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', 'd802dce9-f16f-4635-9db5-f10ed0a18c41', 'promo_timer_present', 0, 0, 0, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204800, '2026-03-11 00:53:20'),
(54, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', 'd802dce9-f16f-4635-9db5-f10ed0a18c41', 'section_view_totals', 48, 1338, 1338, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204801, '2026-03-11 00:53:21'),
(55, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', 'd802dce9-f16f-4635-9db5-f10ed0a18c41', 'quote_price_review_loop', 49, 1388, 1388, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204801, '2026-03-11 00:53:21'),
(56, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', 'd802dce9-f16f-4635-9db5-f10ed0a18c41', 'quote_scroll', 50, 1405, 1405, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204801, '2026-03-11 00:53:21'),
(57, 13, '528751ce-1117-4625-b269-a107c7170a36', 'c14d23eb-be0e-4524-b731-f25172acb0b7', 'd802dce9-f16f-4635-9db5-f10ed0a18c41', 'quote_scroll', 90, 3434, 3434, '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1773204803, '2026-03-11 00:53:23'),
(58, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', 'c235c0a8-6768-44b5-a4b1-63210a8cc9d4', 'promo_timer_present', 0, 11, 11, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205441, '2026-03-11 01:04:01'),
(59, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', 'c235c0a8-6768-44b5-a4b1-63210a8cc9d4', 'quote_open', 0, 10, 10, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205441, '2026-03-11 01:04:01'),
(60, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', 'c235c0a8-6768-44b5-a4b1-63210a8cc9d4', 'quote_close', 0, 1796, 1796, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205443, '2026-03-11 01:04:03'),
(61, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '700f6a7f-30c6-4363-8038-9a7253e5df2f', 'quote_open', 0, 5, 5, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205443, '2026-03-11 01:04:03'),
(62, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '700f6a7f-30c6-4363-8038-9a7253e5df2f', 'promo_timer_present', 0, 6, 6, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205443, '2026-03-11 01:04:03'),
(63, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '700f6a7f-30c6-4363-8038-9a7253e5df2f', 'quote_close', 0, 689, 689, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205443, '2026-03-11 01:04:03'),
(64, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', 'f05dbd73-d9b2-455e-910b-a23bc24fa0d1', 'quote_open', 0, 5, 5, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205444, '2026-03-11 01:04:04'),
(65, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', 'f05dbd73-d9b2-455e-910b-a23bc24fa0d1', 'promo_timer_present', 0, 7, 7, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205444, '2026-03-11 01:04:04'),
(66, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', 'f05dbd73-d9b2-455e-910b-a23bc24fa0d1', 'quote_close', 0, 4362, 2232, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205448, '2026-03-11 01:04:08'),
(67, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '29d1d295-1f20-4da7-8e85-25bbeb059803', 'quote_open', 0, 5, 5, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205448, '2026-03-11 01:04:08'),
(68, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '29d1d295-1f20-4da7-8e85-25bbeb059803', 'promo_timer_present', 0, 6, 6, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205448, '2026-03-11 01:04:08'),
(69, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '29d1d295-1f20-4da7-8e85-25bbeb059803', 'quote_close', 0, 536, 536, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205449, '2026-03-11 01:04:09'),
(70, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '3bf05602-8668-4723-b629-d32cc5742b72', 'quote_open', 0, 5, 5, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205449, '2026-03-11 01:04:09'),
(71, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '3bf05602-8668-4723-b629-d32cc5742b72', 'promo_timer_present', 0, 6, 6, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205449, '2026-03-11 01:04:09'),
(72, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '3bf05602-8668-4723-b629-d32cc5742b72', 'quote_close', 0, 482, 482, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205449, '2026-03-11 01:04:09'),
(73, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '64bb2d00-3e07-4ad2-a026-cbd74192814a', 'quote_open', 0, 6, 6, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205449, '2026-03-11 01:04:09'),
(74, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '64bb2d00-3e07-4ad2-a026-cbd74192814a', 'promo_timer_present', 0, 6, 6, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205449, '2026-03-11 01:04:09'),
(75, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '64bb2d00-3e07-4ad2-a026-cbd74192814a', 'quote_close', 0, 347, 347, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205450, '2026-03-11 01:04:10'),
(76, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '1e275327-568c-455d-8a53-333f35b2a703', 'quote_open', 0, 5, 5, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205450, '2026-03-11 01:04:10'),
(77, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '06a5fed6-596d-4e7f-b5ce-e65fe2847d14', '1e275327-568c-455d-8a53-333f35b2a703', 'promo_timer_present', 0, 6, 6, '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205450, '2026-03-11 01:04:10'),
(78, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', '594c28b8-b29e-40de-b3b8-b27968e3d1f2', '9faa9222-78d3-4dd6-b0a3-5c3dacaf7476', 'promo_timer_present', 0, 31, 31, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773206488, '2026-03-11 01:21:28'),
(79, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', '594c28b8-b29e-40de-b3b8-b27968e3d1f2', '9faa9222-78d3-4dd6-b0a3-5c3dacaf7476', 'quote_open', 0, 30, 30, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773206488, '2026-03-11 01:21:28'),
(80, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', '594c28b8-b29e-40de-b3b8-b27968e3d1f2', '9faa9222-78d3-4dd6-b0a3-5c3dacaf7476', 'section_view_totals', 48, 4511, 4511, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773206493, '2026-03-11 01:21:33'),
(81, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', '594c28b8-b29e-40de-b3b8-b27968e3d1f2', '9faa9222-78d3-4dd6-b0a3-5c3dacaf7476', 'quote_scroll', 51, 4536, 4536, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773206493, '2026-03-11 01:21:33'),
(82, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', '594c28b8-b29e-40de-b3b8-b27968e3d1f2', '9faa9222-78d3-4dd6-b0a3-5c3dacaf7476', 'quote_price_review_loop', 54, 4561, 4561, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773206493, '2026-03-11 01:21:33'),
(83, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', '594c28b8-b29e-40de-b3b8-b27968e3d1f2', '9faa9222-78d3-4dd6-b0a3-5c3dacaf7476', 'coupon_validate_click', 89, 8665, 8665, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773206497, '2026-03-11 01:21:37'),
(84, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', '594c28b8-b29e-40de-b3b8-b27968e3d1f2', '9faa9222-78d3-4dd6-b0a3-5c3dacaf7476', 'quote_scroll', 91, 9650, 9650, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773206498, '2026-03-11 01:21:38'),
(85, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', '594c28b8-b29e-40de-b3b8-b27968e3d1f2', '9faa9222-78d3-4dd6-b0a3-5c3dacaf7476', 'section_revisit_totals', 100, 11373, 11373, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773206500, '2026-03-11 01:21:40'),
(86, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', 'f37d27d6-2e2a-4bd9-a6ba-f509f20bb88a', 'c7771e2b-96fa-45ec-8fa6-565d5324b6b0', 'promo_timer_present', 0, 38, 38, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773256816, '2026-03-11 15:20:16'),
(87, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', 'f37d27d6-2e2a-4bd9-a6ba-f509f20bb88a', 'c7771e2b-96fa-45ec-8fa6-565d5324b6b0', 'quote_open', 0, 37, 37, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773256816, '2026-03-11 15:20:16'),
(88, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', 'f37d27d6-2e2a-4bd9-a6ba-f509f20bb88a', 'c7771e2b-96fa-45ec-8fa6-565d5324b6b0', 'quote_close', 3, 15209, 15209, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773256831, '2026-03-11 15:20:31');

-- --------------------------------------------------------

--
-- Table structure for table `quote_sessions`
--

CREATE TABLE `quote_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `visitor_id` varchar(64) DEFAULT NULL,
  `session_id` varchar(36) DEFAULT NULL,
  `page_id` varchar(36) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(300) DEFAULT NULL,
  `scroll_max` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `visible_ms` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `open_ms` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `es_interno` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quote_sessions`
--

INSERT INTO `quote_sessions` (`id`, `cotizacion_id`, `visitor_id`, `session_id`, `page_id`, `ip`, `user_agent`, `scroll_max`, `visible_ms`, `open_ms`, `activa`, `created_at`, `updated_at`, `es_interno`) VALUES
(1, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5a0e8f48-fc91-42c7-bf7e-0d6ca46a5948', '8c22979a-73c1-4b0f-8174-15a041ebe4ce', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 100, 15629, 0, 1, '2026-03-10 22:11:33', '2026-03-10 22:11:49', 0),
(2, 13, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', '5de02afd-f92f-4fc8-8b71-e040f33471b1', '513d6f0e-9ece-4c3c-9ce5-5ff852b1539d', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 100, 39996, 0, 1, '2026-03-11 00:20:46', '2026-03-11 01:04:10', 0),
(3, 13, NULL, NULL, NULL, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 100, 11373, 0, 1, '2026-03-11 01:21:27', '2026-03-11 01:21:40', 0),
(4, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', NULL, NULL, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 3, 15209, 0, 1, '2026-03-11 15:20:14', '2026-03-11 15:20:31', 0);

-- --------------------------------------------------------

--
-- Table structure for table `radar_fit_calibracion`
--

CREATE TABLE `radar_fit_calibracion` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `global_rate` decimal(6,4) NOT NULL DEFAULT 0.0815,
  `rate_sess_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rate_sess_json`)),
  `rate_ips_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rate_ips_json`)),
  `rate_gap_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rate_gap_json`)),
  `bandas_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bandas_json`)),
  `cotizaciones` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ventas_cerradas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `radar_ips_internas`
--

CREATE TABLE `radar_ips_internas` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) NOT NULL,
  `etiqueta` varchar(60) DEFAULT NULL,
  `fuente` varchar(30) NOT NULL DEFAULT 'manual',
  `aprendida_ts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `radar_ips_internas`
--

INSERT INTO `radar_ips_internas` (`id`, `empresa_id`, `ip`, `etiqueta`, `fuente`, `aprendida_ts`, `created_at`) VALUES
(4, 2, '187.245.114.71', NULL, 'radar_open', 1773376241, '2026-03-11 01:07:20'),
(52, 2, '189.173.176.164', NULL, 'radar_open', 1773341272, '2026-03-12 14:47:52');

-- --------------------------------------------------------

--
-- Table structure for table `radar_visitors_internos`
--

CREATE TABLE `radar_visitors_internos` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `visitor_id` varchar(64) NOT NULL,
  `source` varchar(30) NOT NULL DEFAULT 'internal_user',
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `first_seen` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_seen` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `radar_visitors_internos`
--

INSERT INTO `radar_visitors_internos` (`id`, `empresa_id`, `visitor_id`, `source`, `usuario_id`, `ip`, `label`, `first_seen`, `last_seen`, `created_at`) VALUES
(1, 2, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', 'internal_user', 2, '187.245.114.71', 'info@closetfactory.com.mx | 187.245.114.71 | Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205640, 1773324974, '2026-03-11 01:07:20'),
(4, 2, '528751ce-1117-4625-b269-a107c7170a36', 'internal_user', 2, '187.245.114.71', 'info@closetfactory.com.mx | 187.245.114.71 | Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mob', 1773205656, 1773373233, '2026-03-11 01:07:36'),
(7, 2, 'a0e04919-570e-44c3-b8df-ab976a0c9344', 'internal_ip', NULL, '187.245.114.71', '187.245.114.71 | Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E1', 1773206237, 1773206237, '2026-03-11 01:17:17'),
(35, 2, 'a442f3a9-37bd-424c-a4f9-fff9337d741a', 'login', 2, '187.245.114.71', 'info@closetfactory.com.mx | 187.245.114.71 | Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6.1 Safari/605.1.15', 1773285507, 1773285507, '2026-03-11 23:18:27');

-- --------------------------------------------------------

--
-- Table structure for table `recibos`
--

CREATE TABLE `recibos` (
  `id` int(10) UNSIGNED NOT NULL,
  `venta_id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `numero` varchar(30) NOT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `token` char(64) NOT NULL,
  `cancelado` tinyint(1) NOT NULL DEFAULT 0,
  `cancelado_at` datetime DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `forma_pago` varchar(60) DEFAULT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `cancelado_por_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recibos`
--

INSERT INTO `recibos` (`id`, `venta_id`, `empresa_id`, `numero`, `concepto`, `monto`, `fecha`, `token`, `cancelado`, `cancelado_at`, `notas`, `created_at`, `forma_pago`, `usuario_id`, `cancelado_por_id`) VALUES
(1, 1, 2, 'REC-2026-0001', 'anticipo', 100.00, '2026-03-11', '94adf3e87b84801439b4996bbbaa08906276b90f5b7e1eff0e17892b8b034b7c', 0, NULL, NULL, '2026-03-11 19:11:52', NULL, NULL, NULL),
(2, 1, 2, 'REC-2026-0002', 'pago 2', 200.00, '2026-03-11', '53811f5bfa57a5156f463c82de60d7e70e10bb52e4a5a9288b5192dbf83739cc', 1, '2026-03-11 19:37:02', 'Transferencia [Cancelado: error]', '2026-03-11 19:25:07', NULL, NULL, NULL),
(3, 1, 2, 'REC-2026-0003', 'pago', 50.00, '2026-03-11', '0d580274a502305fbb7f9ac42d71150733dbb9f07c6da74e5aaf201221fe1b85', 1, '2026-03-11 22:24:27', ' [Cancelado: error2]', '2026-03-11 22:20:54', NULL, NULL, NULL),
(4, 1, 2, 'REC-2026-0004', 'pago', 50.00, '2026-03-11', '2573cbbcdfd2b3fe7279021e15ca3472fd58ab7c11a1bd0727c719a762f34a91', 1, '2026-03-11 22:24:03', ' [Cancelado: error]', '2026-03-11 22:21:02', NULL, NULL, NULL),
(5, 1, 2, 'REC-2026-0005', 'prueba', 30.00, '2026-03-11', 'b133ab99821a5797adafce8968cbe6a3514876057d219f4616b42e982c527c3f', 0, NULL, 'Transferencia', '2026-03-11 22:25:29', NULL, NULL, NULL),
(6, 1, 2, 'REC-2026-0006', 'pago', 30.00, '2026-03-11', '691fa5da6e7086a3400747461c6831e2a7ffaef06225245a3908a422dead6590', 1, '2026-03-11 22:29:04', 'Transferencia [Cancelado: error]', '2026-03-11 22:28:55', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `token` char(64) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(300) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `usuario_id`, `empresa_id`, `token`, `ip`, `user_agent`, `created_at`, `expires_at`) VALUES
(1, 2, 2, '3a060727fc93e82a7a3c31efa54daa7657ce15bb8167238187cbdd04662c1d4f', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-10 13:51:10', '2026-03-10 18:51:10'),
(2, 2, 2, '72a487abb0e0eb4d65bf2aba5fba75d269390f53c30d9b4eb6f81b41f2dbbe2c', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6.1 Safari/605.1.15', '2026-03-10 13:58:33', '2026-03-10 18:58:33'),
(3, 2, 2, '9fb70fe90d38b18f92a7fa320ad68b7d435fb8e344cd0b9d351dadc866c52524', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-10 14:00:23', '2026-03-10 19:00:23'),
(4, 2, 2, 'f5b5e2655ddce115fc14d24f153f64532c264aae1c89910d9f1788ccac0a6e87', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-10 14:06:02', '2026-03-10 19:06:02'),
(5, 2, 2, '80a76f210f6fbce6826fe2dda7a763a4b4d413d8cca187e7351d239b3b84761c', '201.162.168.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-10 16:00:48', '2026-03-10 21:00:48'),
(6, 2, 2, '40fb590dc10f3208831be10107eb7f61c1aa33dabc8a46227e1c17b01a8c4812', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-10 20:46:11', '2026-03-11 01:46:11'),
(7, 2, 2, '5effa9f531a428ab0ff6f31c61ecb51b81d4d00ee858a5c741e5848b4a03172d', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-10 21:11:58', '2026-03-11 02:11:58'),
(8, 2, 2, '18ce9ef44d5e9a00972b9b8da5c012090c87a8d6a589233669be8bdb1b1b7d11', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-11 10:25:39', '2026-03-11 15:25:39'),
(9, 2, 2, 'eccff85ec2680ec560836460be8e4ed04ceaed4ebd1a3d5e31c7ae9f8c4f0d73', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-11 10:32:09', '2026-03-11 15:32:09'),
(10, 2, 2, '7426d9bc687a6fc8f9f07c2c0df9b98964c60e9e00afceab8f7b7ed996d8e2a7', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-11 17:16:38', '2026-03-11 22:16:38'),
(11, 2, 2, 'cf88f792045aaafbe1248b02a34656348a8bf1d51e254d2425502627ec915790', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-11 19:39:47', '2026-03-12 00:39:47'),
(12, 2, 2, '60421a9ea88d13409835ebb966ba7364dcd7ca3a937eb198ce152e92ce8acd90', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-11 22:19:12', '2026-03-12 03:19:12'),
(13, 2, 2, 'e4f45390ef0d8c92dbcafc4429556157210f09e78381f1e92cafd331cb26296f', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6.1 Safari/605.1.15', '2026-03-11 23:18:27', '2026-03-12 04:18:27'),
(14, 2, 2, '890e0add4dc124eca2df0c602e434cded92745410b526d36686dfcfa1ecd3183', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-12 02:11:22', '2026-03-12 07:11:22'),
(15, 2, 2, '1b2d91de38fd1a9661412df04512d112224a92e4f794cef71502e44ea06a47d1', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-12 10:16:14', '2026-03-12 15:16:14'),
(16, 2, 2, '7e86900f78e49c065c69151ec40d7f01055fffad27152533f1821beb1ffd596b', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-12 12:48:32', '2026-03-12 17:48:32'),
(17, 2, 2, 'a36f9c73dec3f3176eb70c3d365818b93862984b6646aa80e996c322d146e395', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-12 17:52:40', '2026-03-12 22:52:40'),
(18, 2, 2, '2b0e13cc320a62efc5bd6f71f8db7422c4b09fe616c2f8a9c9ced2170a6001b9', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-12 23:40:33', '2026-03-13 04:40:33');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `usuario` varchar(60) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','asesor') NOT NULL DEFAULT 'asesor',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `puede_editar_precios` tinyint(1) NOT NULL DEFAULT 1,
  `puede_aplicar_descuentos` tinyint(1) NOT NULL DEFAULT 1,
  `puede_ver_todas_cots` tinyint(1) NOT NULL DEFAULT 0,
  `puede_ver_todas_ventas` tinyint(1) NOT NULL DEFAULT 0,
  `puede_eliminar_items_venta` tinyint(1) NOT NULL DEFAULT 0,
  `puede_cancelar_recibos` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `empresa_id`, `nombre`, `email`, `usuario`, `password_hash`, `rol`, `activo`, `ultimo_login`, `created_at`, `puede_editar_precios`, `puede_aplicar_descuentos`, `puede_ver_todas_cots`, `puede_ver_todas_ventas`, `puede_eliminar_items_venta`, `puede_cancelar_recibos`) VALUES
(2, 2, 'Jose Medina', 'info@closetfactory.com.mx', NULL, '$2y$12$At01O.B7RKzWOtEt/UtP1ucW1G53Stl37YVjwGhsSpoiIUKhp/Obm', 'admin', 1, '2026-03-12 23:40:33', '2026-03-10 13:13:59', 1, 1, 1, 1, 1, 1),
(3, 2, 'Admin Closet', 'closet@closetfactory.com', NULL, '$2y$12$LK9Ux4u6r3vFqmBnZJE7Dewe0rDJfX8nBxpXpZlHx3Nc9a2QEO6GW', 'admin', 1, NULL, '2026-03-11 22:14:54', 1, 1, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ventas`
--

CREATE TABLE `ventas` (
  `id` int(10) UNSIGNED NOT NULL,
  `numero` varchar(30) DEFAULT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `token` char(64) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `pagado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento_manual_amt` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pendiente','parcial','pagada','entregada','cancelada') NOT NULL DEFAULT 'pendiente',
  `cancelado_at` datetime DEFAULT NULL,
  `cancelado_motivo` varchar(255) DEFAULT NULL,
  `cancelado_por_id` int(10) UNSIGNED DEFAULT NULL,
  `entregado_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `descuento_auto_amt` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cupon_monto` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`id`, `numero`, `empresa_id`, `cotizacion_id`, `cliente_id`, `usuario_id`, `titulo`, `slug`, `token`, `total`, `pagado`, `saldo`, `descuento_manual_amt`, `estado`, `cancelado_at`, `cancelado_motivo`, `cancelado_por_id`, `entregado_at`, `created_at`, `updated_at`, `descuento_auto_amt`, `cupon_monto`) VALUES
(1, 'VTA-2026-0001', 2, 13, 1, NULL, 'Ramón 417', 'ramon-417', 'a36b4c8a1cbde092603f2ce0460f4985e445efbb423ccaa13976c9b36bf3d092', 19300.00, 130.00, 19170.00, 150.00, 'parcial', NULL, NULL, NULL, NULL, '2026-03-11 14:16:35', '2026-03-11 22:29:56', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `venta_log`
--

CREATE TABLE `venta_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `venta_id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `evento` varchar(80) NOT NULL,
  `detalle` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `venta_log`
--

INSERT INTO `venta_log` (`id`, `venta_id`, `empresa_id`, `usuario_id`, `evento`, `detalle`, `created_at`) VALUES
(1, 1, 2, 2, 'cotizacion_guardada', '1 artículo(s)', '2026-03-11 22:19:27'),
(2, 1, 2, 2, 'cotizacion_guardada', '1 artículo(s) · Descuento $150.00', '2026-03-11 22:19:54'),
(3, 1, 2, 2, 'cotizacion_guardada', '1 artículo(s)', '2026-03-11 22:20:04'),
(4, 1, 2, 2, 'abono_registrado', '$50.00 · pago', '2026-03-11 22:20:54'),
(5, 1, 2, 2, 'abono_registrado', '$50.00 · pago', '2026-03-11 22:21:02'),
(6, 1, 2, 2, 'descuento_agregado', '$300.00', '2026-03-11 22:23:39'),
(7, 1, 2, 2, 'descuento_eliminado', 'Era $300.00', '2026-03-11 22:23:52'),
(8, 1, 2, 2, 'abono_registrado', '$30.00 · prueba', '2026-03-11 22:25:29'),
(9, 1, 2, 2, 'abono_registrado', '$30.00 · pago · Transferencia', '2026-03-11 22:28:55'),
(10, 1, 2, 2, 'abono_cancelado', 'Recibo REC-2026-0006 · -$30.00 · error', '2026-03-11 22:29:04'),
(11, 1, 2, 2, 'descuento_agregado', '$500.00', '2026-03-11 22:29:19'),
(12, 1, 2, 2, 'descuento_agregado', '$500.00 → $600.00', '2026-03-11 22:29:40'),
(13, 1, 2, 2, 'descuento_eliminado', 'Era $600.00', '2026-03-11 22:29:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_art_empresa` (`empresa_id`,`activo`);

--
-- Indexes for table `categorias_costos`
--
ALTER TABLE `categorias_costos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cat_nombre` (`empresa_id`,`nombre`),
  ADD KEY `idx_cat_empresa` (`empresa_id`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cl_empresa` (`empresa_id`,`activo`);

--
-- Indexes for table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `uq_slug` (`empresa_id`,`slug`),
  ADD KEY `idx_cot_empresa_estado` (`empresa_id`,`estado`,`created_at`),
  ADD KEY `idx_cot_ultima_vista` (`empresa_id`,`ultima_vista_at`),
  ADD KEY `idx_cot_bucket` (`empresa_id`,`radar_bucket`),
  ADD KEY `idx_cot_token` (`token`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `cupon_id` (`cupon_id`);

--
-- Indexes for table `cotizacion_archivos`
--
ALTER TABLE `cotizacion_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`);

--
-- Indexes for table `cotizacion_lineas`
--
ALTER TABLE `cotizacion_lineas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`),
  ADD KEY `articulo_id` (`articulo_id`);

--
-- Indexes for table `cotizacion_log`
--
ALTER TABLE `cotizacion_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cot_log` (`cotizacion_id`,`created_at`);

--
-- Indexes for table `cupones`
--
ALTER TABLE `cupones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cupon` (`empresa_id`,`codigo`);

--
-- Indexes for table `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `folios`
--
ALTER TABLE `folios`
  ADD PRIMARY KEY (`empresa_id`,`tipo`,`anio`);

--
-- Indexes for table `gastos_venta`
--
ALTER TABLE `gastos_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gv_venta` (`venta_id`),
  ADD KEY `idx_gv_empresa` (`empresa_id`,`fecha`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indexes for table `quote_events`
--
ALTER TABLE `quote_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qe_cotizacion` (`cotizacion_id`,`tipo`),
  ADD KEY `idx_qe_visitor` (`visitor_id`),
  ADD KEY `idx_qe_ts` (`ts_unix`);

--
-- Indexes for table `quote_sessions`
--
ALTER TABLE `quote_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qs_cotizacion` (`cotizacion_id`,`activa`,`updated_at`),
  ADD KEY `idx_qs_visitor` (`visitor_id`);

--
-- Indexes for table `radar_fit_calibracion`
--
ALTER TABLE `radar_fit_calibracion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rfc_empresa` (`empresa_id`,`activa`);

--
-- Indexes for table `radar_ips_internas`
--
ALTER TABLE `radar_ips_internas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ip` (`empresa_id`,`ip`),
  ADD KEY `idx_ip_empresa` (`empresa_id`);

--
-- Indexes for table `radar_visitors_internos`
--
ALTER TABLE `radar_visitors_internos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rv_empresa_visitor` (`empresa_id`,`visitor_id`),
  ADD KEY `idx_rv_empresa` (`empresa_id`),
  ADD KEY `idx_rv_visitor` (`visitor_id`);

--
-- Indexes for table `recibos`
--
ALTER TABLE `recibos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_recibo_venta` (`venta_id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_us_token` (`token`),
  ADD KEY `idx_us_usuario` (`usuario_id`),
  ADD KEY `idx_us_expires` (`expires_at`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usu_email` (`empresa_id`,`email`),
  ADD KEY `idx_usu_empresa` (`empresa_id`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cotizacion_id` (`cotizacion_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `uq_vta_slug` (`empresa_id`,`slug`),
  ADD KEY `idx_vta_empresa` (`empresa_id`,`estado`,`created_at`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `venta_log`
--
ALTER TABLE `venta_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venta_log` (`venta_id`,`created_at`),
  ADD KEY `idx_venta_log_emp` (`empresa_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articulos`
--
ALTER TABLE `articulos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categorias_costos`
--
ALTER TABLE `categorias_costos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `cotizacion_archivos`
--
ALTER TABLE `cotizacion_archivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cotizacion_lineas`
--
ALTER TABLE `cotizacion_lineas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `cotizacion_log`
--
ALTER TABLE `cotizacion_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cupones`
--
ALTER TABLE `cupones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gastos_venta`
--
ALTER TABLE `gastos_venta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quote_events`
--
ALTER TABLE `quote_events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `quote_sessions`
--
ALTER TABLE `quote_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `radar_fit_calibracion`
--
ALTER TABLE `radar_fit_calibracion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `radar_ips_internas`
--
ALTER TABLE `radar_ips_internas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `radar_visitors_internos`
--
ALTER TABLE `radar_visitors_internos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `recibos`
--
ALTER TABLE `recibos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `venta_log`
--
ALTER TABLE `venta_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articulos`
--
ALTER TABLE `articulos`
  ADD CONSTRAINT `articulos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categorias_costos`
--
ALTER TABLE `categorias_costos`
  ADD CONSTRAINT `categorias_costos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cotizaciones_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cotizaciones_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `cotizaciones_ibfk_4` FOREIGN KEY (`cupon_id`) REFERENCES `cupones` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cotizacion_archivos`
--
ALTER TABLE `cotizacion_archivos`
  ADD CONSTRAINT `cotizacion_archivos_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cotizacion_lineas`
--
ALTER TABLE `cotizacion_lineas`
  ADD CONSTRAINT `cotizacion_lineas_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cotizacion_lineas_ibfk_2` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cotizacion_log`
--
ALTER TABLE `cotizacion_log`
  ADD CONSTRAINT `cotizacion_log_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cupones`
--
ALTER TABLE `cupones`
  ADD CONSTRAINT `cupones_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `folios`
--
ALTER TABLE `folios`
  ADD CONSTRAINT `folios_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gastos_venta`
--
ALTER TABLE `gastos_venta`
  ADD CONSTRAINT `gastos_venta_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gastos_venta_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gastos_venta_ibfk_3` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_costos` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quote_events`
--
ALTER TABLE `quote_events`
  ADD CONSTRAINT `quote_events_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quote_sessions`
--
ALTER TABLE `quote_sessions`
  ADD CONSTRAINT `quote_sessions_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `radar_fit_calibracion`
--
ALTER TABLE `radar_fit_calibracion`
  ADD CONSTRAINT `radar_fit_calibracion_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `radar_ips_internas`
--
ALTER TABLE `radar_ips_internas`
  ADD CONSTRAINT `radar_ips_internas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recibos`
--
ALTER TABLE `recibos`
  ADD CONSTRAINT `recibos_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recibos_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_sessions_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  ADD CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ventas_ibfk_4` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `venta_log`
--
ALTER TABLE `venta_log`
  ADD CONSTRAINT `venta_log_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `venta_log_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
