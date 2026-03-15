-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 15, 2026 at 03:07 PM
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
(1, 2, 'Alfonso Medina', '6621421859', NULL, NULL, NULL, 1, '2026-03-10 16:02:08', '2026-03-10 16:02:08', 2, NULL, NULL),
(2, 2, '001 - Publico General', '001', NULL, NULL, NULL, 1, '2026-03-15 14:14:58', '2026-03-15 14:14:58', 2, NULL, NULL);

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
(13, 'COT-2026-0004', 2, 1, 2, NULL, 'Ramón 417', 'ramon-417', '20a6ab67ddbcf1cba989d062695e586b5029701c4090100ae61c1789f83904c9', NULL, '', '', 19300.00, 0.00, NULL, 0.00, 16.00, 'ninguno', 0.00, 18432.00, 'aceptada', NULL, '2026-03-10 21:13:18', '2026-03-10 22:11:33', '2026-03-11 14:16:35', NULL, NULL, NULL, '2026-04-10 00:00:00', '2026-03-11 15:20:14', NULL, 5, '{\"senales\":{\"sesiones\":{\"pts\":16,\"desc\":\"2 visitas \\u00fanicas\"},\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_rev\":{\"pts\":8,\"desc\":\"Volvi\\u00f3 a revisar totales\"},\"cupon\":{\"pts\":6,\"desc\":\"Intent\\u00f3 aplicar cup\\u00f3n\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"aceptada\":{\"pts\":0,\"desc\":\"Cotizaci\\u00f3n aceptada\"}},\"buckets\":[],\"debug\":{\"sessions\":2,\"uniq_ips\":1,\"gap_days\":0,\"guest\":2,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":9.5,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-10 21:13:18', '2026-03-15 15:05:30', 26, 0, 0.00, 3, '2026-03-14 10:00:47', 0.00, 0.00),
(14, 'COT-2026-0005', 2, 1, 2, NULL, 'Cocina L', 'cocina-l', '1b45574a43dc66b90f17f0c655e39d271369ec344b50a8d6733392e0bf007e89', NULL, '', '', 9800.00, 0.00, NULL, 0.00, 16.00, 'ninguno', 0.00, 9800.00, 'enviada', NULL, '2026-03-12 14:47:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '{\"senales\":[],\"buckets\":[],\"debug\":[]}', '2026-03-15 15:05:30', '2026-03-12 14:47:50', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(15, 'COT-INV-0151', 2, 2, 2, NULL, 'Jesús Parra  6421143689', 'imp-inv-1267', '7bbe493ec22669025301905444039a6b610193e3c92d4a1753ec84e81784aeb6', 'Importada desde Sliced Invoices. ID original: inv-1267', NULL, NULL, 66000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 66000.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-10 12:50:40', '2026-03-15 14:37:36', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(16, 'COT-INV-0149', 2, 2, 2, NULL, 'Estreberto Grijalva Arvizu Calle Petrarca Número 3, Lomas del Sur 5216622562170', 'imp-inv-1251', '0a99f9cd56a3d979b6132e612d1b990198ebaaad6eacf818e1d346c8f657c129', 'Importada desde Sliced Invoices. ID original: inv-1251', NULL, NULL, 21400.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 21400.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-05 14:44:52', '2026-03-15 14:37:37', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(17, 'COT-INV-0147', 2, 2, 2, NULL, 'Natalia Aranda 5216621718828', 'imp-inv-1239', '0fc49055cfb29c89f9038d462e83322794536ab7ec23638ed050198ae6e81d36', 'Importada desde Sliced Invoices. ID original: inv-1239', NULL, NULL, 16800.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 16800.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-02 10:51:39', '2026-03-08 11:52:48', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(18, 'COT-INV-0146', 2, 2, 2, NULL, 'Ana Lourdes León Campillo 108 6624223314', 'imp-inv-1232', '591caf6376c209efefe0834e5d5ffdc296c3b228ff46e60dc15a0674a567952a', 'Importada desde Sliced Invoices. ID original: inv-1232', NULL, NULL, 18600.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 18600.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-28 10:23:21', '2026-03-15 14:37:37', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(19, 'COT-INV-0145', 2, 2, 2, NULL, 'Ana Luisa Romo Apasible 24, Nueva Galicia 6622332809', 'imp-inv-1227', 'bacc68bdb99cd2f1ea362f8a93a5694ff33b5a24686483c187453292edb1c958', 'Importada desde Sliced Invoices. ID original: inv-1227', NULL, NULL, 21000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 21000.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-25 16:38:45', '2026-02-25 16:38:45', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(20, 'COT-INV-0144', 2, 2, 2, NULL, 'Beatriz Guerrero Jimenez 5216622251632 Zonata 25 , Agaves', 'imp-inv-1226', '9ebfa4ac27ded135dc62a8112f20fb5b19cbad5ab4f593f11fab9cbeeb406fed', 'Importada desde Sliced Invoices. ID original: inv-1226', NULL, NULL, 16400.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 16400.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-25 15:18:27', '2026-03-08 11:52:57', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(21, 'COT-INV-0143', 2, 2, 2, NULL, 'Ruth Isela Salomón Alvarez Horizonte dorado #76 Fracc. El Encanto 6621733456', 'imp-inv-1225', 'a879aff67e46dc01b095badcee258b96e945d4f980c32ce014285b1620c15f5b', 'Importada desde Sliced Invoices. ID original: inv-1225', NULL, NULL, 15000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 15000.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-25 11:20:13', '2026-03-15 14:37:37', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(22, 'COT-INV-0142', 2, 2, 2, NULL, 'Karla Muñoz Miguel Alemán #24, Colonia ISSSTE 6623425929', 'imp-inv-1210', 'cd568f33e5ebd5693b7a981e9d8ee986708511eba0f19f616dbffb695561ece2', 'Importada desde Sliced Invoices. ID original: inv-1210', NULL, NULL, 19500.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 19500.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-17 17:06:31', '2026-03-15 14:37:37', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(23, 'COT-INV-0141', 2, 2, 2, NULL, 'Erik Fregoso Privada sahara #10 colonia las Lomas sección almendros 6451105652', 'imp-inv-1198', '694470b5380f3a8cfce86331c5238a6b405eae978dbc628c79dccd1e502515e7', 'Importada desde Sliced Invoices. ID original: inv-1198', NULL, NULL, 50500.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 50500.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-14 12:54:04', '2026-03-15 14:37:37', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(24, 'COT-INV-0140', 2, 2, 2, NULL, 'Estreberto Grijalva Arvizu Calle Petrarca Número 3, Lomas del Sur 5216622562170', 'imp-inv-1197', '77de08487fe4d0a911f0e1b25b28487be63aef2a3980aabda97f0c8fc382ad3b', 'Importada desde Sliced Invoices. ID original: inv-1197', NULL, NULL, 73600.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 73600.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-14 12:12:51', '2026-03-15 14:37:37', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(25, 'COT-INV-0139', 2, 2, 2, NULL, 'Alberto Álvarez Privada Carsoli 11, Villa Bonita 6628474329', 'imp-inv-1193', 'c490ebd7c8404870b48f742f4211a9f6513541c671c0266ce646727c1afa3283', 'Importada desde Sliced Invoices. ID original: inv-1193', NULL, NULL, 38500.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 38500.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-13 15:23:36', '2026-03-15 14:37:37', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(26, 'QUO-665', 2, 2, 2, NULL, 'Lydia Morales Cuarta privada perimetral #7 colonia modelo 6621141452', 'imp-quo-665-882', 'a5a4aaa565db2c9a6d67f0cbd51fa8149d1a3b06c43c6ea31053e21d2d8d3fda', 'Importada desde Sliced Invoices. ID original: 882', NULL, NULL, 32500.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 32500.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'validando_precio', 41, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"validando_precio\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":1,\"span48h\":\"0h\",\"pss\":5.5,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2025-11-11 09:57:23', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(27, 'QUO-801', 2, 2, 2, NULL, 'David Corrales 6624124711', 'imp-quo-801-1059', '8f98841a4d3aef2ddf3a1d2f5c879257ca22ec34285e4bef8acd0afb098f52d8', 'Importada desde Sliced Invoices. ID original: 1059', NULL, NULL, 20500.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 20500.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'validando_precio', 41, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"validando_precio\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":1,\"span48h\":\"0h\",\"pss\":5.5,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-01-07 11:43:07', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(28, 'QUO-924', 2, 2, 2, NULL, 'Miguel Cruz Fraccionamiento Monteregio, Vernaccia 36 6621609041', 'imp-quo-924-1236', '3c6adbb6b15b5849bc334047e554019bb07229cc355bd7886a487bf6fdad1472', 'Importada desde Sliced Invoices. ID original: 1236', NULL, NULL, 48800.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 48800.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-02 09:56:43', '2026-03-02 09:59:17', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(29, 'QUO-930', 2, 2, 2, NULL, 'alex Alborada mza 164 lote 07 Villalcazar residencial 6622763219', 'imp-quo-930-1246', '2b317af4862108eacb7adbe878b12e6ccd48a640e939b891f715224056e10c8f', 'Importada desde Sliced Invoices. ID original: 1246', NULL, NULL, 84000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 84000.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hesitacion', 6, '{\"senales\":{\"sesiones\":{\"pts\":16,\"desc\":\"2 visitas \\u00fanicas\"},\"multi_ip\":{\"pts\":12,\"desc\":\"2 personas distintas\"},\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_rev\":{\"pts\":8,\"desc\":\"Volvi\\u00f3 a revisar totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"mv_price\":{\"pts\":8,\"desc\":\"Varias personas revisaron precio\"}},\"buckets\":[\"hesitacion\"],\"debug\":{\"sessions\":2,\"uniq_ips\":2,\"gap_days\":0,\"guest\":2,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":9.25,\"ev_uniq_v\":2,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-03 10:06:06', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(30, 'QUO-932', 2, 2, 2, NULL, 'Jessica Flores 6622564080', 'imp-quo-932-1250', '4573bf1992f86456ac2c39db38bcf3c7f1ba51c78e273f71638957e5e56f2697', 'Importada desde Sliced Invoices. ID original: 1250', NULL, NULL, 32000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 32000.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'prediccion_alta', 39, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"prediccion_alta\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":6,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-05 12:59:20', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(31, 'QUO-934', 2, 2, 2, NULL, 'Noemí Valle del marqués calle Tezcatlipoca 6624485651', 'imp-quo-934-1254', '7fc54b8733e2807d4483f510bad93da82816bca3917fbdcf358b6df1f2422a32', 'Importada desde Sliced Invoices. ID original: 1254', NULL, NULL, 25400.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 25400.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-07 10:34:25', '2026-03-15 14:37:36', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(32, 'QUO-935', 2, 2, 2, NULL, 'Cintya Sánchez  Romances norte 40 paseo del cid 5216622268213', 'imp-quo-935-1255', '5f4060c4351277efeeb790d806a6956db93f4d6136aba48cbdf775e92a6cc2eb', 'Importada desde Sliced Invoices. ID original: 1255', NULL, NULL, 26000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 26000.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'prediccion_alta', 39, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"prediccion_alta\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":6,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-07 10:40:07', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(33, 'QUO-936', 2, 2, 2, NULL, 'Gerardo Garcés colonia Urbi villa del cedro Izote 69 6621747661', 'imp-quo-936-1256', 'b3116934b0087f13c950a859f5a086d43a74a50d6aecd0cfd93899dc2db3463c', 'Importada desde Sliced Invoices. ID original: 1256', NULL, NULL, 39000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 39000.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'prediccion_alta', 40, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_rev\":{\"pts\":8,\"desc\":\"Volvi\\u00f3 a revisar totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"prediccion_alta\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":8,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-07 10:42:48', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(34, 'QUO-937', 2, 2, 2, NULL, 'Elsa Margarita Aguayo Trujilllon Paseo de los Álamos 160 6621370244', 'imp-quo-937-1258', '3ff21543b9c800e0b22e951527408af222cf6e6a76c58f6701c66a70394a09db', 'Importada desde Sliced Invoices. ID original: 1258', NULL, NULL, 64000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 64000.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hesitacion', 6, '{\"senales\":{\"sesiones\":{\"pts\":16,\"desc\":\"2 visitas \\u00fanicas\"},\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_rev\":{\"pts\":8,\"desc\":\"Volvi\\u00f3 a revisar totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"mv_price\":{\"pts\":8,\"desc\":\"Varias personas revisaron precio\"}},\"buckets\":[\"hesitacion\"],\"debug\":{\"sessions\":2,\"uniq_ips\":1,\"gap_days\":0,\"guest\":2,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":9.25,\"ev_uniq_v\":2,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-09 09:03:56', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(35, 'QUO-938', 2, 2, 2, NULL, 'Fernando Quiroz Avenida Benjamín Muñoz 219, Balderrama 6681946622', 'imp-quo-938-1259', 'e2ae214649fa64e2a17dadeee752d91ee656ee359ce1f0b4dd541909a5413be8', 'Importada desde Sliced Invoices. ID original: 1259', NULL, NULL, 24600.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 24600.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'prediccion_alta', 40, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_rev\":{\"pts\":8,\"desc\":\"Volvi\\u00f3 a revisar totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"prediccion_alta\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":8,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-09 09:54:59', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(36, 'QUO-939', 2, 2, 2, NULL, 'Merari 6621122269', 'imp-quo-939-1260', 'a5a638658764b1aff6fbd83658e665f23492f9b53c5bd0f714b46400e0027537', 'Importada desde Sliced Invoices. ID original: 1260', NULL, NULL, 76700.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 76700.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hesitacion', 6, '{\"senales\":{\"sesiones\":{\"pts\":16,\"desc\":\"2 visitas \\u00fanicas\"},\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"mv_price\":{\"pts\":8,\"desc\":\"Varias personas revisaron precio\"}},\"buckets\":[\"hesitacion\"],\"debug\":{\"sessions\":2,\"uniq_ips\":1,\"gap_days\":0,\"guest\":2,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":7.25,\"ev_uniq_v\":2,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-09 10:33:44', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(37, 'QUO-940', 2, 2, 2, NULL, 'sergio soria 5216623270652', 'imp-quo-940-1261', 'bcb9faeb2e87828a6206dd50fd7d8dee8248a3519f2453e5717ad5d84c3401ac', 'Importada desde Sliced Invoices. ID original: 1261', NULL, NULL, 75200.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 75200.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hesitacion', 6, '{\"senales\":{\"sesiones\":{\"pts\":16,\"desc\":\"2 visitas \\u00fanicas\"},\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_rev\":{\"pts\":8,\"desc\":\"Volvi\\u00f3 a revisar totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"mv_price\":{\"pts\":8,\"desc\":\"Varias personas revisaron precio\"}},\"buckets\":[\"hesitacion\"],\"debug\":{\"sessions\":2,\"uniq_ips\":1,\"gap_days\":2,\"guest\":2,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":9.25,\"ev_uniq_v\":2,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-09 16:04:37', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(38, 'QUO-941', 2, 2, 2, NULL, 'Citlali Sierra 6621490992', 'imp-quo-941-1262', 'cca72bb6416c8b2af70fe9b4d4e89e98d5d6dbd39b6dda6aa921a4f2a54c070e', 'Importada desde Sliced Invoices. ID original: 1262', NULL, NULL, 34000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 34000.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'prediccion_alta', 39, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"prediccion_alta\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":5.5,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-09 17:54:09', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(39, 'QUO-942', 2, 2, 2, NULL, 'Manuel Torres Altaria Residencial, Coto vizenzio #107 5213312284191', 'imp-quo-942-1264', 'ef85773eed7d400d643689b592cd8e59aa7f5e6e0fae971a92b346835d295434', 'Importada desde Sliced Invoices. ID original: 1264', NULL, NULL, 62000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 62000.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hesitacion', 6, '{\"senales\":{\"sesiones\":{\"pts\":16,\"desc\":\"2 visitas \\u00fanicas\"},\"multi_ip\":{\"pts\":12,\"desc\":\"2 personas distintas\"},\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_rev\":{\"pts\":8,\"desc\":\"Volvi\\u00f3 a revisar totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"mv_price\":{\"pts\":8,\"desc\":\"Varias personas revisaron precio\"}},\"buckets\":[\"hesitacion\"],\"debug\":{\"sessions\":2,\"uniq_ips\":2,\"gap_days\":0,\"guest\":2,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":8.75,\"ev_uniq_v\":2,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-10 09:57:55', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(40, 'QUO-943', 2, 2, 2, NULL, 'Raquel García Jordán Calle tres #234 5216621575753', 'imp-quo-943-1265', 'f421d097316fc32e5086194233f304ae2e1e8d6c22fa2e5a100cbf828cab458c', 'Importada desde Sliced Invoices. ID original: 1265', NULL, NULL, 17000.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 17000.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hesitacion', 6, '{\"senales\":{\"sesiones\":{\"pts\":16,\"desc\":\"2 visitas \\u00fanicas\"},\"multi_ip\":{\"pts\":12,\"desc\":\"2 personas distintas\"},\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"mv_price\":{\"pts\":8,\"desc\":\"Varias personas revisaron precio\"}},\"buckets\":[\"hesitacion\"],\"debug\":{\"sessions\":2,\"uniq_ips\":2,\"gap_days\":1,\"guest\":2,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":7.25,\"ev_uniq_v\":2,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-10 12:11:36', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(41, 'QUO-944', 2, 2, 2, NULL, 'Mario Ibarra M. Calle Arboretum #13, Residencial Bonaterra 5216621915913', 'imp-quo-944-1266', 'c0048167354f6abb1978f8bdc9cf7cffb10c46663850d4119535474be7b675dd', 'Importada desde Sliced Invoices. ID original: 1266', NULL, NULL, 67700.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 67700.00, 'convertida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-10 12:19:03', '2026-03-13 14:56:46', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(42, 'QUO-945', 2, 2, 2, NULL, 'sofia acevedo alpina #30, monterosa residencial 6621611050', 'imp-quo-945-1269', '3dd9a3ebdf59c9984928cbfe4bfb007654ea70491bc2ba4b60b241d2430b8883', 'Importada desde Sliced Invoices. ID original: 1269', NULL, NULL, 53500.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 53500.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'prediccion_alta', 39, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"prediccion_alta\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":6,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-11 11:27:11', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(43, 'QUO-946', 2, 2, 2, NULL, 'Fernanda Díaz Cerrada cruceiros, colonia Stanza florenS Stanza florenza 6444084388', 'imp-quo-946-1270', '560dcaae363d5ad806d0fd6cb7dfa0a2b301f6d40fd1845b1369bd2af08ea703', 'Importada desde Sliced Invoices. ID original: 1270', NULL, NULL, 16500.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 16500.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'validando_precio', 39, '{\"senales\":{\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"validando_precio\",\"prediccion_alta\"],\"debug\":{\"sessions\":1,\"uniq_ips\":1,\"gap_days\":null,\"guest\":1,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":6,\"ev_uniq_v\":1,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-12 08:57:21', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00),
(44, 'QUO-947', 2, 2, 2, NULL, 'alexis 5216625260182', 'imp-quo-947-1271', '24efdff3fd1dcb6f5edbbadbc5b2c1fc6f73aa8a22fa0c35e0dcd611042eee82', 'Importada desde Sliced Invoices. ID original: 1271', NULL, NULL, 16500.00, 0.00, NULL, 0.00, 0.00, 'ninguno', 0.00, 16500.00, 'enviada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'validando_precio', 40, '{\"senales\":{\"sesiones\":{\"pts\":24,\"desc\":\"3 visitas \\u00fanicas\"},\"multi_ip\":{\"pts\":12,\"desc\":\"2 personas distintas\"},\"price_loop\":{\"pts\":10,\"desc\":\"Revis\\u00f3 precio varias veces\"},\"tot_view\":{\"pts\":4,\"desc\":\"Revis\\u00f3 secci\\u00f3n de totales\"},\"sv_price\":{\"pts\":8,\"desc\":\"Misma persona enfocada en precio\"},\"mv_price\":{\"pts\":8,\"desc\":\"Varias personas revisaron precio\"},\"fit\":{\"pts\":0,\"desc\":\"FIT 35% \\u2014 patr\\u00f3n de cierre alto\"}},\"buckets\":[\"validando_precio\",\"prediccion_alta\",\"multi_persona\",\"hesitacion\"],\"debug\":{\"sessions\":3,\"uniq_ips\":2,\"gap_days\":0,\"guest\":3,\"views24\":0,\"views48\":0,\"span48h\":\"0h\",\"pss\":7.25,\"ev_uniq_v\":3,\"modo\":\"medio\"}}', '2026-03-15 15:05:30', '2026-03-12 10:50:06', '2026-03-15 15:05:30', 0, 0, 0.00, 3, NULL, 0.00, 0.00);

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
  `venta_id` int(10) UNSIGNED DEFAULT NULL,
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

INSERT INTO `cotizacion_lineas` (`id`, `cotizacion_id`, `venta_id`, `articulo_id`, `orden`, `titulo`, `descripcion`, `sku`, `cantidad`, `precio_unit`, `subtotal`) VALUES
(36, 13, NULL, NULL, 1, 'CLOSET MELAMINA STADARD', 'Closet en Melamina 👗👔👜 Catálogo Standard.\nIncluye:\n✅Closet Empotrado\n✅Torre Cajonera de 5 cajones ancho máximo 60 cms\n✅Puertas Principales ancho máximo 60 cms.\n✅Puertas en Maletero ancho máximo 60 cms.\n✅Closet Profundidad Standard 62 cms máximo.\n✅Altura máxima 270 cms.\n✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.\n✅Base y Zoclo (no se ve el piso).\n✅No incluye forro de muros.', '', 2.0000, 9650.00, 19300.00),
(38, 14, NULL, 1, 1, 'CLOSET MELAMINA STADARD', 'Closet en Melamina 👗👔👜 Catálogo Standard.\nIncluye:✅Closet Empotrado</P><P>✅Torre Cajonera de 5 cajones ancho máximo 60 cms\n✅Puertas Principales ancho máximo 60 cms.\n✅Puertas en Maletero ancho máximo 60 cms.\n✅Closet Profundidad Standard 62 cms máximo.\n✅Altura máxima 270 cms.\n✅Bisagras Reforzadas, Tubos y Jaladera estilo Contemporaneo.\n✅Base y Zoclo (no se ve el piso).\n✅No incluye forro de muros.', '', 1.0000, 9800.00, 9800.00),
(39, 15, NULL, NULL, 1, 'JesÃºs Parra  6421143689', 'Importado desde Sliced Invoices', NULL, 1.0000, 66000.00, 66000.00),
(40, 16, NULL, NULL, 1, 'Estreberto Grijalva Arvizu Calle Petrarca NÃºmero 3, Lomas del Sur 5216622562170', 'Importado desde Sliced Invoices', NULL, 1.0000, 21400.00, 21400.00),
(41, 17, NULL, NULL, 1, 'Natalia Aranda 5216621718828', 'Importado desde Sliced Invoices', NULL, 1.0000, 16800.00, 16800.00),
(42, 18, NULL, NULL, 1, 'Ana Lourdes LeÃ³n Campillo 108 6624223314', 'Importado desde Sliced Invoices', NULL, 1.0000, 18600.00, 18600.00),
(43, 19, NULL, NULL, 1, 'Ana Luisa Romo Apasible 24, Nueva Galicia 6622332809', 'Importado desde Sliced Invoices', NULL, 1.0000, 21000.00, 21000.00),
(44, 20, NULL, NULL, 1, 'Beatriz Guerrero Jimenez 5216622251632 Zonata 25 , Agaves', 'Importado desde Sliced Invoices', NULL, 1.0000, 16400.00, 16400.00),
(45, 21, NULL, NULL, 1, 'Ruth Isela SalomÃ³n Alvarez Horizonte dorado #76 Fracc. El Encanto 6621733456', 'Importado desde Sliced Invoices', NULL, 1.0000, 15000.00, 15000.00),
(46, 22, NULL, NULL, 1, 'Karla MuÃ±oz Miguel AlemÃ¡n #24, Colonia ISSSTE 6623425929', 'Importado desde Sliced Invoices', NULL, 1.0000, 19500.00, 19500.00),
(47, 23, NULL, NULL, 1, 'Erik Fregoso Privada sahara #10 colonia las Lomas secciÃ³n almendros 6451105652', 'Importado desde Sliced Invoices', NULL, 1.0000, 50500.00, 50500.00),
(48, 24, NULL, NULL, 1, 'Estreberto Grijalva Arvizu Calle Petrarca NÃºmero 3, Lomas del Sur 5216622562170', 'Importado desde Sliced Invoices', NULL, 1.0000, 73600.00, 73600.00),
(49, 25, NULL, NULL, 1, 'Alberto Ãlvarez Privada Carsoli 11, Villa Bonita 6628474329', 'Importado desde Sliced Invoices', NULL, 1.0000, 38500.00, 38500.00),
(50, 26, NULL, NULL, 1, 'Lydia Morales Cuarta privada perimetral #7 colonia modelo 6621141452', 'Importado desde Sliced Invoices', NULL, 1.0000, 32500.00, 32500.00),
(51, 27, NULL, NULL, 1, 'David Corrales 6624124711', 'Importado desde Sliced Invoices', NULL, 1.0000, 20500.00, 20500.00),
(52, 28, NULL, NULL, 1, 'Miguel Cruz Fraccionamiento Monteregio, Vernaccia 36 6621609041', 'Importado desde Sliced Invoices', NULL, 1.0000, 48800.00, 48800.00),
(53, 29, NULL, NULL, 1, 'alex Alborada mza 164 lote 07 Villalcazar residencial 6622763219', 'Importado desde Sliced Invoices', NULL, 1.0000, 84000.00, 84000.00),
(54, 30, NULL, NULL, 1, 'Jessica Flores 6622564080', 'Importado desde Sliced Invoices', NULL, 1.0000, 32000.00, 32000.00),
(55, 31, NULL, NULL, 1, 'NoemÃ­ Valle del marquÃ©s calle Tezcatlipoca 6624485651', 'Importado desde Sliced Invoices', NULL, 1.0000, 25400.00, 25400.00),
(56, 32, NULL, NULL, 1, 'Cintya SÃ¡nchez  Romances norte 40 paseo del cid 5216622268213', 'Importado desde Sliced Invoices', NULL, 1.0000, 26000.00, 26000.00),
(57, 33, NULL, NULL, 1, 'Gerardo GarcÃ©s colonia Urbi villa del cedro Izote 69 6621747661', 'Importado desde Sliced Invoices', NULL, 1.0000, 39000.00, 39000.00),
(58, 34, NULL, NULL, 1, 'Elsa Margarita Aguayo Trujilllon Paseo de los Ãlamos 160 6621370244', 'Importado desde Sliced Invoices', NULL, 1.0000, 64000.00, 64000.00),
(59, 35, NULL, NULL, 1, 'Fernando Quiroz Avenida BenjamÃ­n MuÃ±oz 219, Balderrama 6681946622', 'Importado desde Sliced Invoices', NULL, 1.0000, 24600.00, 24600.00),
(60, 36, NULL, NULL, 1, 'Merari 6621122269', 'Importado desde Sliced Invoices', NULL, 1.0000, 76700.00, 76700.00),
(61, 37, NULL, NULL, 1, 'sergio soria 5216623270652', 'Importado desde Sliced Invoices', NULL, 1.0000, 75200.00, 75200.00),
(62, 38, NULL, NULL, 1, 'Citlali Sierra 6621490992', 'Importado desde Sliced Invoices', NULL, 1.0000, 34000.00, 34000.00),
(63, 39, NULL, NULL, 1, 'Manuel Torres Altaria Residencial, Coto vizenzio #107 5213312284191', 'Importado desde Sliced Invoices', NULL, 1.0000, 62000.00, 62000.00),
(64, 40, NULL, NULL, 1, 'Raquel GarcÃ­a JordÃ¡n Calle tres #234 5216621575753', 'Importado desde Sliced Invoices', NULL, 1.0000, 17000.00, 17000.00),
(65, 41, NULL, NULL, 1, 'Mario Ibarra M. Calle Arboretum #13, Residencial Bonaterra 5216621915913', 'Importado desde Sliced Invoices', NULL, 1.0000, 67700.00, 67700.00),
(66, 42, NULL, NULL, 1, 'sofia acevedo alpina #30, monterosa residencial 6621611050', 'Importado desde Sliced Invoices', NULL, 1.0000, 53500.00, 53500.00),
(67, 43, NULL, NULL, 1, 'Fernanda DÃ­az Cerrada cruceiros, colonia Stanza florenS Stanza florenza 6444084388', 'Importado desde Sliced Invoices', NULL, 1.0000, 16500.00, 16500.00),
(68, 44, NULL, NULL, 1, 'alexis 5216625260182', 'Importado desde Sliced Invoices', NULL, 1.0000, 16500.00, 16500.00);

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
(10, 14, 2, '', NULL, '189.173.176.164', '2026-03-12 14:48:33', 'editada'),
(11, 15, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0151', NULL, '2026-03-10 12:50:40', NULL),
(12, 16, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0149', NULL, '2026-03-05 14:44:52', NULL),
(13, 17, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0147', NULL, '2026-03-02 10:51:39', NULL),
(14, 18, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0146', NULL, '2026-02-28 10:23:21', NULL),
(15, 19, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0145', NULL, '2026-02-25 16:38:45', NULL),
(16, 20, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0144', NULL, '2026-02-25 15:18:27', NULL),
(17, 21, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0143', NULL, '2026-02-25 11:20:13', NULL),
(18, 22, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0142', NULL, '2026-02-17 17:06:31', NULL),
(19, 23, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0141', NULL, '2026-02-14 12:54:04', NULL),
(20, 24, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0140', NULL, '2026-02-14 12:12:51', NULL),
(21, 25, 2, 'importada', 'Importada desde Sliced Invoices — COT-INV-0139', NULL, '2026-02-13 15:23:36', NULL),
(22, 26, 2, 'importada', 'Importada desde Sliced Invoices — QUO-665', NULL, '2025-11-11 09:57:23', NULL),
(23, 27, 2, 'importada', 'Importada desde Sliced Invoices — QUO-801', NULL, '2026-01-07 11:43:07', NULL),
(24, 28, 2, 'importada', 'Importada desde Sliced Invoices — QUO-924', NULL, '2026-03-02 09:56:43', NULL),
(25, 29, 2, 'importada', 'Importada desde Sliced Invoices — QUO-930', NULL, '2026-03-03 10:06:06', NULL),
(26, 30, 2, 'importada', 'Importada desde Sliced Invoices — QUO-932', NULL, '2026-03-05 12:59:20', NULL),
(27, 31, 2, 'importada', 'Importada desde Sliced Invoices — QUO-934', NULL, '2026-03-07 10:34:25', NULL),
(28, 32, 2, 'importada', 'Importada desde Sliced Invoices — QUO-935', NULL, '2026-03-07 10:40:07', NULL),
(29, 33, 2, 'importada', 'Importada desde Sliced Invoices — QUO-936', NULL, '2026-03-07 10:42:48', NULL),
(30, 34, 2, 'importada', 'Importada desde Sliced Invoices — QUO-937', NULL, '2026-03-09 09:03:56', NULL),
(31, 35, 2, 'importada', 'Importada desde Sliced Invoices — QUO-938', NULL, '2026-03-09 09:54:59', NULL),
(32, 36, 2, 'importada', 'Importada desde Sliced Invoices — QUO-939', NULL, '2026-03-09 10:33:44', NULL),
(33, 37, 2, 'importada', 'Importada desde Sliced Invoices — QUO-940', NULL, '2026-03-09 16:04:37', NULL),
(34, 38, 2, 'importada', 'Importada desde Sliced Invoices — QUO-941', NULL, '2026-03-09 17:54:09', NULL),
(35, 39, 2, 'importada', 'Importada desde Sliced Invoices — QUO-942', NULL, '2026-03-10 09:57:55', NULL),
(36, 40, 2, 'importada', 'Importada desde Sliced Invoices — QUO-943', NULL, '2026-03-10 12:11:36', NULL),
(37, 41, 2, 'importada', 'Importada desde Sliced Invoices — QUO-944', NULL, '2026-03-10 12:19:03', NULL),
(38, 42, 2, 'importada', 'Importada desde Sliced Invoices — QUO-945', NULL, '2026-03-11 11:27:11', NULL),
(39, 43, 2, 'importada', 'Importada desde Sliced Invoices — QUO-946', NULL, '2026-03-12 08:57:21', NULL),
(40, 44, 2, 'importada', 'Importada desde Sliced Invoices — QUO-947', NULL, '2026-03-12 10:50:06', NULL);

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
  `cot_prefijo` varchar(10) NOT NULL DEFAULT 'COT',
  `vta_prefijo` varchar(10) NOT NULL DEFAULT 'VTA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `empresas`
--

INSERT INTO `empresas` (`id`, `slug`, `nombre`, `logo_url`, `email`, `telefono`, `ciudad`, `moneda`, `impuesto_modo`, `impuesto_pct`, `impuesto_nombre`, `texto_bienvenida`, `texto_aceptar`, `texto_rechazar`, `texto_recibo`, `adc_activo`, `adc_pct`, `adc_horas`, `adc_texto`, `radar_config`, `activa`, `created_at`, `updated_at`, `direccion`, `rfc`, `website`, `notif_email`, `notif_email_acepta`, `notif_email_rechaza`, `cot_vigencia_dias`, `allow_precio_edit`, `cot_msg_acepta`, `cot_msg_rechaza`, `cot_terminos`, `cot_footer`, `vta_terminos`, `vta_footer`, `cot_prefijo`, `vta_prefijo`) VALUES
(2, 'closetfactory', 'Closet Factory Hermosillo', '/uploads/logos/logo_2_1773429109.png', 'info@closetfactory.com.mx', '6624550498', 'Hermosillo', 'MXN', 'ninguno', 16.00, 'IVA', NULL, NULL, NULL, NULL, 0, 0.00, 72, NULL, '{\"sensibilidad\":\"medio\",\"calibracion_auto\":true,\"excluir_internos\":true,\"filtrar_bots\":true,\"deduplicar_30min\":true}', 1, '2026-03-10 13:13:59', '2026-03-15 14:45:00', '', '', 'closetfactory.com.mx', 'info@closetfactory.com.mx', 1, 1, 30, 1, 'Gracias por su compra, sera contactado por el asesor', 'Una pena, esperamos verlo pronto', 'CONSULTA NUESTRO CATALOGO DE MELAMINAS AQUI:\n-Anticipo 50%.\n-Tiempo de Fabricación 1 a 10 días hábiles (a partir de confirmar el diseño) y 1-2 días de Instalación.\n-Instalamos de Lunes a Viernes en horario de 10am a 6pm.\n-Garantía 2 años contra desperfectos.', 'closetfactory.com.mx', '', '', 'COT', 'VTA');

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
(88, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', 'f37d27d6-2e2a-4bd9-a6ba-f509f20bb88a', 'c7771e2b-96fa-45ec-8fa6-565d5324b6b0', 'quote_close', 3, 15209, 15209, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 1773256831, '2026-03-11 15:20:31'),
(89, 30, 'c9127944-a581-45d4-b36e-1e6745c42578', 'c9127944-a581-45d4-b36e-1e6745c42578', 'd74d196f-a0ae-4af1-a134-35ccb7135f92', 'quote_close', 92, 1464249, 13483, 'ÈD', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1772901460, '2026-03-07 09:37:40'),
(90, 30, 'c9127944-a581-45d4-b36e-1e6745c42578', 'c9127944-a581-45d4-b36e-1e6745c42578', '65d05d90-3efb-4d7f-b018-3cbf150ce9ae', 'quote_open', 0, 14, 14, 'ÈD', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1772904522, '2026-03-07 10:28:42'),
(91, 30, 'c9127944-a581-45d4-b36e-1e6745c42578', 'c9127944-a581-45d4-b36e-1e6745c42578', '65d05d90-3efb-4d7f-b018-3cbf150ce9ae', 'quote_scroll', 50, 2783, 2783, 'ÈD', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1772904523, '2026-03-07 10:28:43'),
(92, 30, 'c9127944-a581-45d4-b36e-1e6745c42578', 'c9127944-a581-45d4-b36e-1e6745c42578', '65d05d90-3efb-4d7f-b018-3cbf150ce9ae', 'section_view_totals', 58, 2934, 2934, 'ÈD', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1772904523, '2026-03-07 10:28:43'),
(93, 30, 'c9127944-a581-45d4-b36e-1e6745c42578', 'c9127944-a581-45d4-b36e-1e6745c42578', '65d05d90-3efb-4d7f-b018-3cbf150ce9ae', 'quote_price_review_loop', 59, 2967, 2967, 'ÈD', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1772904523, '2026-03-07 10:28:43'),
(94, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '21febe0e-b4a3-4821-9390-35c6bb97a5ec', 'quote_open', 0, 45, 45, '¾7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1772905765, '2026-03-07 10:49:25'),
(95, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '21febe0e-b4a3-4821-9390-35c6bb97a5ec', 'quote_scroll', 51, 7021, 7021, '¾7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1772905773, '2026-03-07 10:49:33'),
(96, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '21febe0e-b4a3-4821-9390-35c6bb97a5ec', 'section_view_totals', 59, 7098, 7098, '¾7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1772905773, '2026-03-07 10:49:33'),
(97, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '21febe0e-b4a3-4821-9390-35c6bb97a5ec', 'quote_price_review_loop', 61, 7118, 7118, '¾7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1772905773, '2026-03-07 10:49:33'),
(98, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '21febe0e-b4a3-4821-9390-35c6bb97a5ec', 'quote_scroll', 91, 10925, 10925, '¾7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1772905776, '2026-03-07 10:49:36'),
(99, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'b41012fe-78c3-4aab-85c5-67a23e5019d7', 'quote_open', 0, 43, 43, 'ÈDl', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772906950, '2026-03-07 11:09:10'),
(100, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'b41012fe-78c3-4aab-85c5-67a23e5019d7', 'section_view_totals', 59, 2922, 2922, 'ÈDl', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772906953, '2026-03-07 11:09:13'),
(101, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'b41012fe-78c3-4aab-85c5-67a23e5019d7', 'quote_scroll', 50, 2705, 2705, 'ÈDl', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772906953, '2026-03-07 11:09:13'),
(102, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'b41012fe-78c3-4aab-85c5-67a23e5019d7', 'quote_price_review_loop', 60, 2955, 2955, 'ÈDl', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772906953, '2026-03-07 11:09:13'),
(103, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'b41012fe-78c3-4aab-85c5-67a23e5019d7', 'section_revisit_totals', 80, 160318, 64639, 'ÈDl', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772907112, '2026-03-07 11:11:52'),
(104, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'b41012fe-78c3-4aab-85c5-67a23e5019d7', 'section_revisit_totals', 80, 172110, 76431, 'ÈDl', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772907122, '2026-03-07 11:12:02'),
(105, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'b41012fe-78c3-4aab-85c5-67a23e5019d7', 'quote_scroll', 93, 177949, 82270, 'ÈDl', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772907128, '2026-03-07 11:12:08'),
(106, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'b41012fe-78c3-4aab-85c5-67a23e5019d7', 'section_revisit_totals', 100, 178123, 82444, 'ÈDl', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772907128, '2026-03-07 11:12:08'),
(107, 30, 'c9127944-a581-45d4-b36e-1e6745c42578', 'c9127944-a581-45d4-b36e-1e6745c42578', '4a558dc8-8c72-4cf6-9031-5aaa46c878d0', 'quote_open', 0, 9, 9, 'ÈD', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1772912178, '2026-03-07 12:36:18'),
(108, 30, 'c9127944-a581-45d4-b36e-1e6745c42578', 'c9127944-a581-45d4-b36e-1e6745c42578', '4a558dc8-8c72-4cf6-9031-5aaa46c878d0', 'quote_close', 7, 4256, 4256, 'ÈD', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 1772912182, '2026-03-07 12:36:22'),
(109, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', '503f6393-4380-4848-9f0b-4d5fd99d68f2', 'quote_open', 0, 36, 0, '»öfa', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1772922643, '2026-03-07 15:30:43'),
(110, 31, 'ad84fc6a-b403-4e00-8665-5e9bd227535c', 'ad84fc6a-b403-4e00-8665-5e9bd227535c', '91c9fbe2-1e45-4243-8628-887c88209e3a', 'quote_open', 0, 75, 75, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 Safari/604.1 [FBAN/FBIOS;FBAV/550.0.0.34.65;FBBV/890804754;FBDV/iPhone14,7;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5', 1772927467, '2026-03-07 16:51:07'),
(111, 31, 'ad84fc6a-b403-4e00-8665-5e9bd227535c', 'ad84fc6a-b403-4e00-8665-5e9bd227535c', '91c9fbe2-1e45-4243-8628-887c88209e3a', 'quote_scroll', 50, 7298, 7298, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 Safari/604.1 [FBAN/FBIOS;FBAV/550.0.0.34.65;FBBV/890804754;FBDV/iPhone14,7;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5', 1772927474, '2026-03-07 16:51:14'),
(112, 31, 'ad84fc6a-b403-4e00-8665-5e9bd227535c', 'ad84fc6a-b403-4e00-8665-5e9bd227535c', '91c9fbe2-1e45-4243-8628-887c88209e3a', 'section_view_totals', 67, 86101, 19746, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 Safari/604.1 [FBAN/FBIOS;FBAV/550.0.0.34.65;FBBV/890804754;FBDV/iPhone14,7;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5', 1772927553, '2026-03-07 16:52:33'),
(113, 31, 'ad84fc6a-b403-4e00-8665-5e9bd227535c', 'ad84fc6a-b403-4e00-8665-5e9bd227535c', '91c9fbe2-1e45-4243-8628-887c88209e3a', 'quote_price_review_loop', 75, 86201, 19846, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 Safari/604.1 [FBAN/FBIOS;FBAV/550.0.0.34.65;FBBV/890804754;FBDV/iPhone14,7;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5', 1772927553, '2026-03-07 16:52:33'),
(114, 31, 'ad84fc6a-b403-4e00-8665-5e9bd227535c', 'ad84fc6a-b403-4e00-8665-5e9bd227535c', '91c9fbe2-1e45-4243-8628-887c88209e3a', 'quote_scroll', 91, 86433, 20078, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 Safari/604.1 [FBAN/FBIOS;FBAV/550.0.0.34.65;FBBV/890804754;FBDV/iPhone14,7;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5', 1772927553, '2026-03-07 16:52:33'),
(115, 31, 'ad84fc6a-b403-4e00-8665-5e9bd227535c', 'ad84fc6a-b403-4e00-8665-5e9bd227535c', '91c9fbe2-1e45-4243-8628-887c88209e3a', 'section_revisit_totals', 100, 101310, 34955, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 Safari/604.1 [FBAN/FBIOS;FBAV/550.0.0.34.65;FBBV/890804754;FBDV/iPhone14,7;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5', 1772927568, '2026-03-07 16:52:48'),
(116, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '26c869c4-1f17-4e37-a449-0788993220a1', 'quote_open', 0, 37, 37, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075564, '2026-03-09 09:59:24'),
(117, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '26c869c4-1f17-4e37-a449-0788993220a1', 'quote_scroll', 50, 27807, 27807, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075592, '2026-03-09 09:59:52'),
(118, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '26c869c4-1f17-4e37-a449-0788993220a1', 'quote_price_review_loop', 63, 39341, 39341, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075603, '2026-03-09 10:00:03'),
(119, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '26c869c4-1f17-4e37-a449-0788993220a1', 'section_view_totals', 60, 39275, 39275, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075603, '2026-03-09 10:00:03'),
(120, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '26c869c4-1f17-4e37-a449-0788993220a1', 'quote_scroll', 90, 44627, 44627, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075609, '2026-03-09 10:00:09'),
(121, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '26c869c4-1f17-4e37-a449-0788993220a1', 'quote_close', 100, 47641, 47640, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075611, '2026-03-09 10:00:11'),
(122, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '26c869c4-1f17-4e37-a449-0788993220a1', 'section_revisit_totals', 100, 99281, 47679, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075664, '2026-03-09 10:01:04'),
(123, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '26c869c4-1f17-4e37-a449-0788993220a1', 'section_revisit_totals', 100, 190861, 123782, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075756, '2026-03-09 10:02:36'),
(124, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'fbd23af1-dfe7-4a5a-8464-6502fc91a235', 'quote_open', 0, 1, 1, '±æAd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773075769, '2026-03-09 10:02:49'),
(125, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'quote_open', 0, 56, 55, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078435, '2026-03-09 10:47:15'),
(126, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'quote_scroll', 50, 42237, 42236, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078478, '2026-03-09 10:47:58'),
(127, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'quote_price_review_loop', 74, 84523, 84522, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078522, '2026-03-09 10:48:42'),
(128, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'section_view_totals', 74, 84423, 84422, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078522, '2026-03-09 10:48:42'),
(129, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'quote_scroll', 90, 104160, 104159, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078539, '2026-03-09 10:48:59'),
(130, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'section_revisit_totals', 100, 107322, 107321, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078542, '2026-03-09 10:49:02'),
(131, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'section_revisit_totals', 100, 116339, 116338, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078551, '2026-03-09 10:49:11'),
(132, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'section_revisit_totals', 100, 129324, 129323, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078564, '2026-03-09 10:49:24'),
(133, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '05c705ca-d5c9-43e8-8d68-a98b1f40fd74', 'quote_close', 100, 556095, 131827, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078991, '2026-03-09 10:56:31'),
(134, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', 'cc130da8-265b-4b99-9fb2-0b38d3982ae8', 'quote_open', 0, 8, 8, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773078991, '2026-03-09 10:56:31'),
(135, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', 'cc130da8-265b-4b99-9fb2-0b38d3982ae8', 'quote_scroll', 50, 18931, 18931, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773079010, '2026-03-09 10:56:50'),
(136, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', 'cc130da8-265b-4b99-9fb2-0b38d3982ae8', 'section_view_totals', 74, 20832, 20832, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773079012, '2026-03-09 10:56:52'),
(137, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', 'cc130da8-265b-4b99-9fb2-0b38d3982ae8', 'quote_price_review_loop', 76, 20899, 20899, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773079012, '2026-03-09 10:56:52'),
(138, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', 'cc130da8-265b-4b99-9fb2-0b38d3982ae8', 'section_revisit_totals', 76, 52696, 52696, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773079044, '2026-03-09 10:57:24'),
(139, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '5c59d295-2af7-49d7-9d81-a31eb563f71d', 'quote_open', 0, 20, 20, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773079758, '2026-03-09 11:09:18'),
(140, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '5c59d295-2af7-49d7-9d81-a31eb563f71d', 'quote_scroll', 50, 14274, 14274, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773079772, '2026-03-09 11:09:32'),
(141, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '5c59d295-2af7-49d7-9d81-a31eb563f71d', 'section_view_totals', 72, 15945, 15945, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773079773, '2026-03-09 11:09:33'),
(142, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '5c59d295-2af7-49d7-9d81-a31eb563f71d', 'quote_price_review_loop', 72, 15978, 15978, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773079773, '2026-03-09 11:09:33'),
(143, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '5c59d295-2af7-49d7-9d81-a31eb563f71d', 'quote_close', 84, 238754, 21182, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773079996, '2026-03-09 11:13:16'),
(144, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '6c935ef2-227e-4acc-b45e-476e5bcb0d40', 'quote_open', 0, 0, 0, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773079996, '2026-03-09 11:13:16'),
(145, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '6c935ef2-227e-4acc-b45e-476e5bcb0d40', 'quote_scroll', 50, 7972, 7972, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773080004, '2026-03-09 11:13:24');
INSERT INTO `quote_events` (`id`, `cotizacion_id`, `visitor_id`, `session_id`, `page_id`, `tipo`, `max_scroll`, `open_ms`, `visible_ms`, `ip`, `ua`, `ts_unix`, `created_at`) VALUES
(146, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '6c935ef2-227e-4acc-b45e-476e5bcb0d40', 'quote_price_review_loop', 72, 21547, 21547, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773080018, '2026-03-09 11:13:38'),
(147, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', '6c935ef2-227e-4acc-b45e-476e5bcb0d40', 'section_view_totals', 71, 21448, 21448, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773080018, '2026-03-09 11:13:38'),
(148, 36, '532466a6-6d84-4961-8a0b-7933efd55daa', '532466a6-6d84-4961-8a0b-7933efd55daa', '42b935bd-6700-40df-b646-1183fe8316a3', 'quote_open', 0, 49, 49, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773086956, '2026-03-09 13:09:16'),
(149, 36, '532466a6-6d84-4961-8a0b-7933efd55daa', '532466a6-6d84-4961-8a0b-7933efd55daa', '42b935bd-6700-40df-b646-1183fe8316a3', 'quote_scroll', 50, 9521, 9521, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773086965, '2026-03-09 13:09:25'),
(150, 36, '532466a6-6d84-4961-8a0b-7933efd55daa', '532466a6-6d84-4961-8a0b-7933efd55daa', '42b935bd-6700-40df-b646-1183fe8316a3', 'quote_close', 52, 10581, 10581, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773086966, '2026-03-09 13:09:26'),
(151, 36, '532466a6-6d84-4961-8a0b-7933efd55daa', '532466a6-6d84-4961-8a0b-7933efd55daa', 'ef184e06-fa0f-4184-ad99-719852812561', 'quote_open', 0, 0, 0, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773086966, '2026-03-09 13:09:26'),
(152, 36, '532466a6-6d84-4961-8a0b-7933efd55daa', '532466a6-6d84-4961-8a0b-7933efd55daa', 'ef184e06-fa0f-4184-ad99-719852812561', 'section_view_totals', 73, 397, 397, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773086967, '2026-03-09 13:09:27'),
(153, 36, '532466a6-6d84-4961-8a0b-7933efd55daa', '532466a6-6d84-4961-8a0b-7933efd55daa', 'ef184e06-fa0f-4184-ad99-719852812561', 'quote_scroll', 73, 395, 395, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773086967, '2026-03-09 13:09:27'),
(154, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', 'd6842a54-2b2c-4013-a00a-e2abcc89dc0f', 'quote_open', 0, 0, 0, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773095405, '2026-03-09 15:30:05'),
(155, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', 'cc130da8-265b-4b99-9fb2-0b38d3982ae8', 'quote_close', 78, 16414428, 74837, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773095405, '2026-03-09 15:30:05'),
(156, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', 'd6842a54-2b2c-4013-a00a-e2abcc89dc0f', 'quote_scroll', 50, 50069, 50069, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773095457, '2026-03-09 15:30:57'),
(157, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', 'd6842a54-2b2c-4013-a00a-e2abcc89dc0f', 'section_view_totals', 77, 50769, 50769, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773095457, '2026-03-09 15:30:57'),
(158, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', 'd6842a54-2b2c-4013-a00a-e2abcc89dc0f', 'quote_scroll', 90, 51052, 51052, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773095457, '2026-03-09 15:30:57'),
(159, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '79f95caa-e9e8-4b4b-bf42-221813249dd1', 'quote_open', 0, 55, 55, 'ÈD£É', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773098297, '2026-03-09 16:18:17'),
(160, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '79f95caa-e9e8-4b4b-bf42-221813249dd1', 'quote_scroll', 50, 18144, 13214, 'ÈD£É', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773098316, '2026-03-09 16:18:36'),
(161, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '79f95caa-e9e8-4b4b-bf42-221813249dd1', 'section_view_totals', 76, 19836, 14906, 'ÈD£É', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773098317, '2026-03-09 16:18:37'),
(162, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '79f95caa-e9e8-4b4b-bf42-221813249dd1', 'quote_price_review_loop', 76, 19919, 14989, 'ÈD£É', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773098317, '2026-03-09 16:18:37'),
(163, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '79f95caa-e9e8-4b4b-bf42-221813249dd1', 'quote_scroll', 90, 34407, 29477, 'ÈD£É', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773098332, '2026-03-09 16:18:52'),
(164, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '79f95caa-e9e8-4b4b-bf42-221813249dd1', 'section_revisit_totals', 100, 67910, 62980, 'ÈD£É', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773098365, '2026-03-09 16:19:25'),
(165, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', '2df79e44-00ea-4ca8-85ec-6907d7248dd8', 'quote_open', 0, 11, 10, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773099687, '2026-03-09 16:41:27'),
(166, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', '2df79e44-00ea-4ca8-85ec-6907d7248dd8', 'quote_scroll', 50, 9478, 9477, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773099697, '2026-03-09 16:41:37'),
(167, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', '2df79e44-00ea-4ca8-85ec-6907d7248dd8', 'quote_price_review_loop', 77, 13921, 13920, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773099701, '2026-03-09 16:41:41'),
(168, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', '2df79e44-00ea-4ca8-85ec-6907d7248dd8', 'section_view_totals', 75, 13856, 13855, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773099701, '2026-03-09 16:41:41'),
(169, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', '2df79e44-00ea-4ca8-85ec-6907d7248dd8', 'quote_scroll', 90, 14459, 14458, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773099702, '2026-03-09 16:41:42'),
(170, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', '2df79e44-00ea-4ca8-85ec-6907d7248dd8', 'section_revisit_totals', 100, 15888, 15887, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773099703, '2026-03-09 16:41:43'),
(171, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', '2df79e44-00ea-4ca8-85ec-6907d7248dd8', 'quote_close', 100, 33734, 33733, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 1773099721, '2026-03-09 16:42:01'),
(172, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '30bd33e0-3490-4bb3-bf01-17561356cfe0', 'quote_open', 0, 58, 0, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103873, '2026-03-09 17:51:13'),
(173, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '30bd33e0-3490-4bb3-bf01-17561356cfe0', 'section_view_totals', 88, 104, 0, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103873, '2026-03-09 17:51:13'),
(174, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '30bd33e0-3490-4bb3-bf01-17561356cfe0', 'quote_scroll', 88, 104, 0, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103873, '2026-03-09 17:51:13'),
(175, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '0e7351d8-d2e3-45f1-b8ba-56897901208a', 'quote_open', 0, 3, 3, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103874, '2026-03-09 17:51:14'),
(176, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '0e7351d8-d2e3-45f1-b8ba-56897901208a', 'quote_scroll', 50, 17192, 17192, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103891, '2026-03-09 17:51:31'),
(177, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '0e7351d8-d2e3-45f1-b8ba-56897901208a', 'section_view_totals', 76, 38486, 38486, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103913, '2026-03-09 17:51:53'),
(178, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '0e7351d8-d2e3-45f1-b8ba-56897901208a', 'quote_price_review_loop', 76, 38553, 38553, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103913, '2026-03-09 17:51:53'),
(179, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '0e7351d8-d2e3-45f1-b8ba-56897901208a', 'quote_scroll', 90, 67443, 67443, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103942, '2026-03-09 17:52:22'),
(180, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '0e7351d8-d2e3-45f1-b8ba-56897901208a', 'quote_close', 100, 82602, 82602, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.38 Mobile/15E148 Safari/604.1', 1773103957, '2026-03-09 17:52:37'),
(181, 38, 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', '3a81e83a-c299-42f2-b8fd-78530a69d233', 'quote_open', 0, 56, 57, 'B°', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773105858, '2026-03-09 18:24:18'),
(182, 38, 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', '3a81e83a-c299-42f2-b8fd-78530a69d233', 'section_view_totals', 47, 4285, 4285, 'B°', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773105862, '2026-03-09 18:24:22'),
(183, 38, 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', '3a81e83a-c299-42f2-b8fd-78530a69d233', 'quote_scroll', 59, 65860, 65860, 'B°', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773105925, '2026-03-09 18:25:25'),
(184, 38, 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', '3a81e83a-c299-42f2-b8fd-78530a69d233', 'quote_scroll', 95, 66551, 66551, 'B°', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773105925, '2026-03-09 18:25:25'),
(185, 38, 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', '3a81e83a-c299-42f2-b8fd-78530a69d233', 'quote_price_review_loop', 71, 66418, 66418, 'B°', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773105925, '2026-03-09 18:25:25'),
(186, 38, 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', '3a81e83a-c299-42f2-b8fd-78530a69d233', 'quote_close', 95, 73148, 73148, 'B°', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773105931, '2026-03-09 18:25:31'),
(187, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '9a6ff243-4c5c-4863-9796-07f079e3a833', 'quote_open', 0, 3, 3, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116483, '2026-03-09 21:21:23'),
(188, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '9a6ff243-4c5c-4863-9796-07f079e3a833', 'quote_scroll', 50, 3343, 3343, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116486, '2026-03-09 21:21:26'),
(189, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '9a6ff243-4c5c-4863-9796-07f079e3a833', 'section_view_totals', 57, 58751, 57556, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116543, '2026-03-09 21:22:23'),
(190, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '9a6ff243-4c5c-4863-9796-07f079e3a833', 'quote_price_review_loop', 58, 58784, 57589, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116543, '2026-03-09 21:22:23'),
(191, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '9a6ff243-4c5c-4863-9796-07f079e3a833', 'quote_scroll', 96, 62052, 60857, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116545, '2026-03-09 21:22:25'),
(192, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'a0556390-5fea-4bb4-9527-06bb38a4df36', 'quote_open', 0, 18, 18, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116952, '2026-03-09 21:29:12'),
(193, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'a0556390-5fea-4bb4-9527-06bb38a4df36', 'section_view_totals', 57, 16562, 16562, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116969, '2026-03-09 21:29:29'),
(194, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'a0556390-5fea-4bb4-9527-06bb38a4df36', 'quote_scroll', 50, 16498, 16498, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116969, '2026-03-09 21:29:29'),
(195, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'a0556390-5fea-4bb4-9527-06bb38a4df36', 'quote_price_review_loop', 60, 16595, 16595, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116969, '2026-03-09 21:29:29'),
(196, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'a0556390-5fea-4bb4-9527-06bb38a4df36', 'section_revisit_totals', 70, 17887, 17887, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773116970, '2026-03-09 21:29:30'),
(197, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'a0556390-5fea-4bb4-9527-06bb38a4df36', 'section_revisit_totals', 70, 110219, 85911, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773117063, '2026-03-09 21:31:03'),
(198, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '463e870b-8ff5-4aad-8bd5-c72d5b3cef3e', 'section_view_totals', 81, 38, 38, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773118705, '2026-03-09 21:58:25'),
(199, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '463e870b-8ff5-4aad-8bd5-c72d5b3cef3e', 'quote_scroll', 81, 37, 37, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773118705, '2026-03-09 21:58:25'),
(200, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', '463e870b-8ff5-4aad-8bd5-c72d5b3cef3e', 'quote_open', 0, 5, 5, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773118705, '2026-03-09 21:58:25'),
(201, 39, 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'b7b43a83-e99c-4a25-af75-08f25ea88412', 'quote_open', 0, 38, 38, 'ÈD½Î', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773162882, '2026-03-10 10:14:42'),
(202, 39, 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'b7b43a83-e99c-4a25-af75-08f25ea88412', 'quote_scroll', 50, 4820, 4820, 'ÈD½Î', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773162887, '2026-03-10 10:14:47'),
(203, 39, 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'b7b43a83-e99c-4a25-af75-08f25ea88412', 'section_view_totals', 67, 9421, 9421, 'ÈD½Î', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773162891, '2026-03-10 10:14:51'),
(204, 39, 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'b7b43a83-e99c-4a25-af75-08f25ea88412', 'quote_price_review_loop', 68, 9443, 9443, 'ÈD½Î', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773162891, '2026-03-10 10:14:51'),
(205, 39, '462a6861-0ad5-47be-a2b6-64b318a94149', '462a6861-0ad5-47be-a2b6-64b318a94149', '8c0fde95-fda7-4d2a-bc79-4ca6a4718ba7', 'quote_open', 0, 132, 132, '»õà', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773162963, '2026-03-10 10:16:03'),
(206, 39, '462a6861-0ad5-47be-a2b6-64b318a94149', '462a6861-0ad5-47be-a2b6-64b318a94149', '8c0fde95-fda7-4d2a-bc79-4ca6a4718ba7', 'quote_scroll', 61, 22818, 22818, '»õà', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773162985, '2026-03-10 10:16:25'),
(207, 39, '462a6861-0ad5-47be-a2b6-64b318a94149', '462a6861-0ad5-47be-a2b6-64b318a94149', '8c0fde95-fda7-4d2a-bc79-4ca6a4718ba7', 'section_view_totals', 61, 22820, 22820, '»õà', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773162985, '2026-03-10 10:16:25'),
(208, 39, '462a6861-0ad5-47be-a2b6-64b318a94149', '462a6861-0ad5-47be-a2b6-64b318a94149', '8c0fde95-fda7-4d2a-bc79-4ca6a4718ba7', 'quote_price_review_loop', 89, 22851, 22851, '»õà', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773162985, '2026-03-10 10:16:25'),
(209, 39, '462a6861-0ad5-47be-a2b6-64b318a94149', '462a6861-0ad5-47be-a2b6-64b318a94149', '8c0fde95-fda7-4d2a-bc79-4ca6a4718ba7', 'quote_scroll', 96, 22869, 22869, '»õà', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773162985, '2026-03-10 10:16:25'),
(210, 39, '462a6861-0ad5-47be-a2b6-64b318a94149', '462a6861-0ad5-47be-a2b6-64b318a94149', '8c0fde95-fda7-4d2a-bc79-4ca6a4718ba7', 'section_revisit_totals', 100, 125486, 125486, '»õà', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773163089, '2026-03-10 10:18:09'),
(211, 39, '462a6861-0ad5-47be-a2b6-64b318a94149', '462a6861-0ad5-47be-a2b6-64b318a94149', '8c0fde95-fda7-4d2a-bc79-4ca6a4718ba7', 'quote_close', 100, 134058, 134058, '»õà', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773163096, '2026-03-10 10:18:16'),
(212, 28, '25c35c6c-e73e-4083-9cf8-d518c61324c6', '25c35c6c-e73e-4083-9cf8-d518c61324c6', '709f5580-8ad2-40c5-ab19-d694459ae4f7', 'quote_open', 0, 26, 26, '½­0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1773168703, '2026-03-10 11:51:43'),
(213, 28, '25c35c6c-e73e-4083-9cf8-d518c61324c6', '25c35c6c-e73e-4083-9cf8-d518c61324c6', '709f5580-8ad2-40c5-ab19-d694459ae4f7', 'quote_scroll', 51, 15797, 15797, '½­0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1773168719, '2026-03-10 11:51:59'),
(214, 28, '25c35c6c-e73e-4083-9cf8-d518c61324c6', '25c35c6c-e73e-4083-9cf8-d518c61324c6', '709f5580-8ad2-40c5-ab19-d694459ae4f7', 'section_view_totals', 68, 15938, 15938, '½­0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1773168719, '2026-03-10 11:51:59'),
(215, 28, '25c35c6c-e73e-4083-9cf8-d518c61324c6', '25c35c6c-e73e-4083-9cf8-d518c61324c6', '709f5580-8ad2-40c5-ab19-d694459ae4f7', 'quote_price_review_loop', 69, 15955, 15955, '½­0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1773168719, '2026-03-10 11:51:59'),
(216, 28, '25c35c6c-e73e-4083-9cf8-d518c61324c6', '25c35c6c-e73e-4083-9cf8-d518c61324c6', '709f5580-8ad2-40c5-ab19-d694459ae4f7', 'quote_scroll', 90, 17221, 17221, '½­0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1773168720, '2026-03-10 11:52:00'),
(217, 28, '25c35c6c-e73e-4083-9cf8-d518c61324c6', '25c35c6c-e73e-4083-9cf8-d518c61324c6', '709f5580-8ad2-40c5-ab19-d694459ae4f7', 'quote_close', 100, 620140, 620140, '½­0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1773169325, '2026-03-10 12:02:05'),
(218, 28, 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', '079b7f98-d562-4f4e-b438-0c1978d769a4', 'quote_open', 0, 9, 9, 'B°£', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773169790, '2026-03-10 12:09:50'),
(219, 28, 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', '079b7f98-d562-4f4e-b438-0c1978d769a4', 'quote_scroll', 50, 4100, 4100, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773169794, '2026-03-10 12:09:54'),
(220, 28, 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', '079b7f98-d562-4f4e-b438-0c1978d769a4', 'section_view_totals', 78, 5939, 5939, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773169795, '2026-03-10 12:09:55'),
(221, 28, 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', '079b7f98-d562-4f4e-b438-0c1978d769a4', 'quote_price_review_loop', 82, 5989, 5989, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773169795, '2026-03-10 12:09:55'),
(222, 28, 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', '079b7f98-d562-4f4e-b438-0c1978d769a4', 'quote_scroll', 90, 6114, 6114, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773169795, '2026-03-10 12:09:55'),
(223, 41, 'b39f1d84-91a6-4013-a49f-c83966037a22', 'b39f1d84-91a6-4013-a49f-c83966037a22', 'd694f07b-aeb1-4047-823f-597dab552fc5', 'quote_open', 0, 1, 1, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175166, '2026-03-10 13:39:26'),
(224, 41, 'b39f1d84-91a6-4013-a49f-c83966037a22', 'b39f1d84-91a6-4013-a49f-c83966037a22', 'd694f07b-aeb1-4047-823f-597dab552fc5', 'quote_scroll', 50, 5657, 5657, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175172, '2026-03-10 13:39:32'),
(225, 41, 'b39f1d84-91a6-4013-a49f-c83966037a22', 'b39f1d84-91a6-4013-a49f-c83966037a22', 'd694f07b-aeb1-4047-823f-597dab552fc5', 'section_view_totals', 76, 7508, 7508, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175174, '2026-03-10 13:39:34'),
(226, 41, 'b39f1d84-91a6-4013-a49f-c83966037a22', 'b39f1d84-91a6-4013-a49f-c83966037a22', 'd694f07b-aeb1-4047-823f-597dab552fc5', 'quote_price_review_loop', 77, 7540, 7540, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175174, '2026-03-10 13:39:34'),
(227, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '871ed7f6-8f0e-4b6e-8326-00e929de5653', 'quote_open', 0, 1, 1, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175197, '2026-03-10 13:39:57'),
(228, 41, 'b39f1d84-91a6-4013-a49f-c83966037a22', 'b39f1d84-91a6-4013-a49f-c83966037a22', 'd694f07b-aeb1-4047-823f-597dab552fc5', 'quote_close', 82, 30685, 19313, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175197, '2026-03-10 13:39:57'),
(229, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '871ed7f6-8f0e-4b6e-8326-00e929de5653', 'quote_scroll', 50, 17483, 17483, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175215, '2026-03-10 13:40:15'),
(230, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '871ed7f6-8f0e-4b6e-8326-00e929de5653', 'section_view_totals', 76, 24251, 24251, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175222, '2026-03-10 13:40:22'),
(231, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '871ed7f6-8f0e-4b6e-8326-00e929de5653', 'quote_price_review_loop', 78, 24284, 24284, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175222, '2026-03-10 13:40:22'),
(232, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '871ed7f6-8f0e-4b6e-8326-00e929de5653', 'quote_scroll', 90, 28420, 28420, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175226, '2026-03-10 13:40:26'),
(233, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '871ed7f6-8f0e-4b6e-8326-00e929de5653', 'section_revisit_totals', 97, 62207, 62207, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773175260, '2026-03-10 13:41:00'),
(234, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'e81e8f38-592e-46af-ace0-0597e36c53ea', 'quote_open', 0, 18, 18, '½­Ü', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773175407, '2026-03-10 13:43:27'),
(235, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'e81e8f38-592e-46af-ace0-0597e36c53ea', 'quote_scroll', 50, 6310, 6310, '½­Ü', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773175414, '2026-03-10 13:43:34'),
(236, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'e81e8f38-592e-46af-ace0-0597e36c53ea', 'section_view_totals', 59, 6694, 6694, '½­Ü', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773175414, '2026-03-10 13:43:34'),
(237, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'e81e8f38-592e-46af-ace0-0597e36c53ea', 'quote_price_review_loop', 63, 6726, 6726, '½­Ü', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773175414, '2026-03-10 13:43:34'),
(238, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'e81e8f38-592e-46af-ace0-0597e36c53ea', 'quote_scroll', 90, 6943, 6943, '½­Ü', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773175414, '2026-03-10 13:43:34'),
(239, 41, '284a0b27-df72-4456-aee3-77b6b33b2e69', '284a0b27-df72-4456-aee3-77b6b33b2e69', '8de49566-769b-45b2-be53-49c92b067d01', 'quote_open', 0, 31, 31, 'É¢¨f', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176113, '2026-03-10 13:55:13'),
(240, 41, '284a0b27-df72-4456-aee3-77b6b33b2e69', '284a0b27-df72-4456-aee3-77b6b33b2e69', '8de49566-769b-45b2-be53-49c92b067d01', 'quote_price_review_loop', 78, 2758, 2758, 'É¢¨f', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176116, '2026-03-10 13:55:16'),
(241, 41, '284a0b27-df72-4456-aee3-77b6b33b2e69', '284a0b27-df72-4456-aee3-77b6b33b2e69', '8de49566-769b-45b2-be53-49c92b067d01', 'section_view_totals', 76, 2725, 2725, 'É¢¨f', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176116, '2026-03-10 13:55:16'),
(242, 41, '284a0b27-df72-4456-aee3-77b6b33b2e69', '284a0b27-df72-4456-aee3-77b6b33b2e69', '8de49566-769b-45b2-be53-49c92b067d01', 'quote_scroll', 90, 3125, 3125, 'É¢¨f', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176116, '2026-03-10 13:55:16'),
(243, 29, '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '088b8509-10c7-429f-ac58-abc2498f3d87', 'quote_open', 0, 61, 61, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176733, '2026-03-10 14:05:33'),
(244, 29, '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '088b8509-10c7-429f-ac58-abc2498f3d87', 'quote_scroll', 50, 21609, 21609, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176755, '2026-03-10 14:05:55'),
(245, 29, '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '088b8509-10c7-429f-ac58-abc2498f3d87', 'section_view_totals', 77, 23741, 23741, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176757, '2026-03-10 14:05:57'),
(246, 29, '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '088b8509-10c7-429f-ac58-abc2498f3d87', 'quote_price_review_loop', 78, 23770, 23770, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176757, '2026-03-10 14:05:57'),
(247, 29, '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '088b8509-10c7-429f-ac58-abc2498f3d87', 'quote_scroll', 90, 27091, 27091, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176760, '2026-03-10 14:06:00'),
(248, 29, '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '088b8509-10c7-429f-ac58-abc2498f3d87', 'section_revisit_totals', 100, 50940, 50940, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773176784, '2026-03-10 14:06:24'),
(249, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '871ed7f6-8f0e-4b6e-8326-00e929de5653', 'quote_close', 97, 1817524, 78788, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773177015, '2026-03-10 14:10:15'),
(250, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', 'bdd5e10f-6186-4acd-8727-ed4bdc3d196a', 'quote_open', 0, 0, 0, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773177015, '2026-03-10 14:10:15'),
(251, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', 'bdd5e10f-6186-4acd-8727-ed4bdc3d196a', 'quote_scroll', 50, 11827, 11827, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773177027, '2026-03-10 14:10:27'),
(252, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', 'bdd5e10f-6186-4acd-8727-ed4bdc3d196a', 'quote_price_review_loop', 77, 15465, 15465, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773177030, '2026-03-10 14:10:30'),
(253, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', 'bdd5e10f-6186-4acd-8727-ed4bdc3d196a', 'section_view_totals', 76, 15432, 15432, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773177030, '2026-03-10 14:10:30'),
(254, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', 'bdd5e10f-6186-4acd-8727-ed4bdc3d196a', 'section_revisit_totals', 85, 67968, 67968, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773177084, '2026-03-10 14:11:24'),
(255, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '4ca12548-d691-4ff0-a91d-5c5c72cf4bc1', 'quote_open', 0, 5, 5, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773177774, '2026-03-10 14:22:54'),
(256, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '4ca12548-d691-4ff0-a91d-5c5c72cf4bc1', 'quote_scroll', 54, 14546, 14546, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773177789, '2026-03-10 14:23:09'),
(257, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '4ca12548-d691-4ff0-a91d-5c5c72cf4bc1', 'section_view_totals', 77, 14881, 14881, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773177789, '2026-03-10 14:23:09'),
(258, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '4ca12548-d691-4ff0-a91d-5c5c72cf4bc1', 'quote_price_review_loop', 77, 14978, 14978, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773177789, '2026-03-10 14:23:09'),
(259, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '4ca12548-d691-4ff0-a91d-5c5c72cf4bc1', 'quote_scroll', 90, 15246, 15246, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773177790, '2026-03-10 14:23:10'),
(260, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', 'bdd5e10f-6186-4acd-8727-ed4bdc3d196a', 'section_revisit_totals', 85, 1469125, 74159, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773178485, '2026-03-10 14:34:45'),
(261, 28, 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'f32088b8-ecc1-4109-932c-8acb64b39cc1', 'section_view_totals', 92, 105, 105, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773188226, '2026-03-10 17:17:06'),
(262, 28, 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'f32088b8-ecc1-4109-932c-8acb64b39cc1', 'quote_open', 0, 50, 50, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773188226, '2026-03-10 17:17:06'),
(263, 28, 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'f32088b8-ecc1-4109-932c-8acb64b39cc1', 'quote_scroll', 92, 105, 105, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773188226, '2026-03-10 17:17:06'),
(264, 28, 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'f32088b8-ecc1-4109-932c-8acb64b39cc1', 'quote_scroll', 92, 105, 105, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773188226, '2026-03-10 17:17:06'),
(265, 28, 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'f32088b8-ecc1-4109-932c-8acb64b39cc1', 'quote_close', 92, 2039, 2039, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773188227, '2026-03-10 17:17:07'),
(266, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '3f616bdc-f00b-4658-bbac-3e8de23fb277', 'quote_open', 0, 37, 0, '½­Ü', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773200593, '2026-03-10 20:43:13'),
(267, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '7e00a39c-ab86-4a6c-8735-e5dbb8ba0f5e', 'quote_open', 0, 60, 60, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773241394, '2026-03-11 08:03:14'),
(268, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', '7e00a39c-ab86-4a6c-8735-e5dbb8ba0f5e', 'quote_close', 0, 7513, 7513, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773241399, '2026-03-11 08:03:19'),
(269, 41, '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '887e2884-8ef2-456c-b4e6-a72b74a311b4', 'quote_open', 0, 74, 74, 'ÈD¸­', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773251344, '2026-03-11 10:49:04'),
(270, 41, '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '887e2884-8ef2-456c-b4e6-a72b74a311b4', 'quote_scroll', 50, 7834, 7834, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773251352, '2026-03-11 10:49:12'),
(271, 41, '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '887e2884-8ef2-456c-b4e6-a72b74a311b4', 'section_view_totals', 76, 8991, 8991, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773251353, '2026-03-11 10:49:13'),
(272, 41, '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '887e2884-8ef2-456c-b4e6-a72b74a311b4', 'quote_price_review_loop', 78, 9015, 9015, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773251353, '2026-03-11 10:49:13'),
(273, 41, '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '887e2884-8ef2-456c-b4e6-a72b74a311b4', 'quote_scroll', 90, 9602, 9602, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773251354, '2026-03-11 10:49:14'),
(274, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'f76eb62a-b243-48e7-a638-0d9528225a96', 'quote_open', 0, 55, 55, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254197, '2026-03-11 11:36:37'),
(275, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'f76eb62a-b243-48e7-a638-0d9528225a96', 'quote_scroll', 50, 3523, 3523, 'ÈD¸/', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254200, '2026-03-11 11:36:40'),
(276, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'f76eb62a-b243-48e7-a638-0d9528225a96', 'quote_price_review_loop', 63, 5585, 5585, 'ÈD¸/', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254202, '2026-03-11 11:36:42'),
(277, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'f76eb62a-b243-48e7-a638-0d9528225a96', 'section_view_totals', 60, 5552, 5552, 'ÈD¸/', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254202, '2026-03-11 11:36:42'),
(278, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'f76eb62a-b243-48e7-a638-0d9528225a96', 'quote_close', 80, 211784, 10002, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254409, '2026-03-11 11:40:09'),
(279, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '4f1b824b-e61c-4d82-a232-ec68eb44e932', 'quote_open', 0, 0, 0, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254409, '2026-03-11 11:40:09'),
(280, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '4f1b824b-e61c-4d82-a232-ec68eb44e932', 'quote_scroll', 51, 3836, 3836, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254412, '2026-03-11 11:40:12'),
(281, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '4f1b824b-e61c-4d82-a232-ec68eb44e932', 'section_view_totals', 62, 8144, 8144, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254417, '2026-03-11 11:40:17'),
(282, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '4f1b824b-e61c-4d82-a232-ec68eb44e932', 'quote_price_review_loop', 65, 8193, 8193, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254417, '2026-03-11 11:40:17'),
(283, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '4f1b824b-e61c-4d82-a232-ec68eb44e932', 'quote_close', 84, 359690, 25464, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254768, '2026-03-11 11:46:08'),
(284, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '8c439e8b-57ef-4d56-984b-47531edbd1c5', 'quote_open', 0, 0, 0, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254768, '2026-03-11 11:46:08'),
(285, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '8c439e8b-57ef-4d56-984b-47531edbd1c5', 'quote_scroll', 50, 2454, 2454, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254771, '2026-03-11 11:46:11'),
(286, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '8c439e8b-57ef-4d56-984b-47531edbd1c5', 'section_view_totals', 64, 2868, 2868, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254771, '2026-03-11 11:46:11'),
(287, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', '8c439e8b-57ef-4d56-984b-47531edbd1c5', 'quote_price_review_loop', 65, 2901, 2901, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773254771, '2026-03-11 11:46:11'),
(288, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'cc870213-d83a-4d83-ae56-65cb2caf7390', 'quote_scroll', 100, 109, 109, 'ÈD½y', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773263264, '2026-03-11 14:07:44'),
(289, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'cc870213-d83a-4d83-ae56-65cb2caf7390', 'quote_open', 0, 58, 58, 'ÈD½y', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773263264, '2026-03-11 14:07:44');
INSERT INTO `quote_events` (`id`, `cotizacion_id`, `visitor_id`, `session_id`, `page_id`, `tipo`, `max_scroll`, `open_ms`, `visible_ms`, `ip`, `ua`, `ts_unix`, `created_at`) VALUES
(290, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'cc870213-d83a-4d83-ae56-65cb2caf7390', 'quote_scroll', 100, 109, 109, 'ÈD½y', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773263264, '2026-03-11 14:07:44'),
(291, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'cc870213-d83a-4d83-ae56-65cb2caf7390', 'section_view_totals', 100, 110, 110, 'ÈD½y', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773263264, '2026-03-11 14:07:44'),
(292, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '06cd9440-27b1-4736-99f5-806dd2060a9e', 'quote_open', 0, 40, 40, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773263895, '2026-03-11 14:18:15'),
(293, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '06cd9440-27b1-4736-99f5-806dd2060a9e', 'quote_scroll', 50, 1652, 1652, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773263896, '2026-03-11 14:18:16'),
(294, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '06cd9440-27b1-4736-99f5-806dd2060a9e', 'section_view_totals', 59, 1986, 1986, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773263897, '2026-03-11 14:18:17'),
(295, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '06cd9440-27b1-4736-99f5-806dd2060a9e', 'quote_price_review_loop', 61, 2053, 2053, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773263897, '2026-03-11 14:18:17'),
(296, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '06cd9440-27b1-4736-99f5-806dd2060a9e', 'quote_scroll', 90, 5573, 5573, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773263901, '2026-03-11 14:18:21'),
(297, 40, '99a65291-b1e7-482c-bb6c-090a08115462', '99a65291-b1e7-482c-bb6c-090a08115462', '63de3e85-2276-4519-af5d-431c041ccf5d', 'quote_open', 0, 40, 0, '½­', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', 1773265631, '2026-03-11 14:47:11'),
(298, 40, '99a65291-b1e7-482c-bb6c-090a08115462', '99a65291-b1e7-482c-bb6c-090a08115462', '63de3e85-2276-4519-af5d-431c041ccf5d', 'section_view_totals', 48, 79011, 1594, '½­', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', 1773265712, '2026-03-11 14:48:32'),
(299, 40, '99a65291-b1e7-482c-bb6c-090a08115462', '99a65291-b1e7-482c-bb6c-090a08115462', '63de3e85-2276-4519-af5d-431c041ccf5d', 'quote_price_review_loop', 49, 79027, 1610, '½­', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', 1773265712, '2026-03-11 14:48:32'),
(300, 40, '99a65291-b1e7-482c-bb6c-090a08115462', '99a65291-b1e7-482c-bb6c-090a08115462', '63de3e85-2276-4519-af5d-431c041ccf5d', 'quote_scroll', 50, 79035, 1618, '½­', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', 1773265712, '2026-03-11 14:48:32'),
(301, 40, '99a65291-b1e7-482c-bb6c-090a08115462', '99a65291-b1e7-482c-bb6c-090a08115462', '63de3e85-2276-4519-af5d-431c041ccf5d', 'quote_scroll', 90, 79561, 2144, '½­', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', 1773265712, '2026-03-11 14:48:32'),
(302, 40, '99a65291-b1e7-482c-bb6c-090a08115462', '99a65291-b1e7-482c-bb6c-090a08115462', '63de3e85-2276-4519-af5d-431c041ccf5d', 'quote_close', 100, 82825, 5408, '½­', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', 1773265713, '2026-03-11 14:48:33'),
(303, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'cf6a6034-1505-46a5-ab85-627fd8b4bab9', 'quote_open', 0, 45, 44, 'ÈD¸­', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773272647, '2026-03-11 16:44:07'),
(304, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'cf6a6034-1505-46a5-ab85-627fd8b4bab9', 'quote_scroll', 50, 4503, 4502, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773272652, '2026-03-11 16:44:12'),
(305, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'cf6a6034-1505-46a5-ab85-627fd8b4bab9', 'quote_close', 68, 1141982, 27018, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773273789, '2026-03-11 17:03:09'),
(306, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'b9bfc4e5-d529-4cc3-9221-96857d3290f6', 'quote_open', 0, 0, 0, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773273789, '2026-03-11 17:03:09'),
(307, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'e60e985f-07b5-49ee-a0bd-aa12f1089ae0', 'quote_open', 0, 0, 0, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773273815, '2026-03-11 17:03:35'),
(308, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'b9bfc4e5-d529-4cc3-9221-96857d3290f6', 'quote_close', 0, 25904, 16908, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773273815, '2026-03-11 17:03:35'),
(309, 41, 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'febc7dc5-ce3b-414a-9084-bdb05cac9b39', 'quote_open', 0, 47, 46, 'ÈD¸.', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773273852, '2026-03-11 17:04:12'),
(310, 41, 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'febc7dc5-ce3b-414a-9084-bdb05cac9b39', 'quote_scroll', 52, 4727, 4726, 'ÈD¸°', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773273857, '2026-03-11 17:04:17'),
(311, 41, 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'febc7dc5-ce3b-414a-9084-bdb05cac9b39', 'section_view_totals', 75, 5282, 5281, 'ÈD¸°', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773273857, '2026-03-11 17:04:17'),
(312, 41, 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'febc7dc5-ce3b-414a-9084-bdb05cac9b39', 'quote_price_review_loop', 76, 5323, 5322, 'ÈD¸°', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773273857, '2026-03-11 17:04:17'),
(313, 41, 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'febc7dc5-ce3b-414a-9084-bdb05cac9b39', 'quote_scroll', 90, 6000, 5999, 'ÈD¸°', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773273858, '2026-03-11 17:04:18'),
(314, 41, 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'febc7dc5-ce3b-414a-9084-bdb05cac9b39', 'quote_close', 96, 11345, 10522, 'ÈD¸.', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773273863, '2026-03-11 17:04:23'),
(315, 41, '7ae17fce-3180-491a-aede-2e87aca47d9a', '7ae17fce-3180-491a-aede-2e87aca47d9a', 'c42f0d71-1288-4507-86c3-3ca82646e360', 'quote_open', 0, 0, 0, 'ÈD¸.', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773273863, '2026-03-11 17:04:23'),
(316, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'edee23af-5044-4d4d-b543-ca6c0c03112b', 'quote_open', 0, 28, 27, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773273878, '2026-03-11 17:04:38'),
(317, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'edee23af-5044-4d4d-b543-ca6c0c03112b', 'quote_scroll', 50, 41737, 41736, 'ÈD¸­', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773273920, '2026-03-11 17:05:20'),
(318, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'edee23af-5044-4d4d-b543-ca6c0c03112b', 'section_view_totals', 76, 51150, 51149, 'ÈD¸­', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773273930, '2026-03-11 17:05:30'),
(319, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'edee23af-5044-4d4d-b543-ca6c0c03112b', 'quote_price_review_loop', 77, 51183, 51182, 'ÈD¸­', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773273930, '2026-03-11 17:05:30'),
(320, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'edee23af-5044-4d4d-b543-ca6c0c03112b', 'quote_scroll', 90, 52679, 52678, 'ÈD¸­', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773273931, '2026-03-11 17:05:31'),
(321, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'edee23af-5044-4d4d-b543-ca6c0c03112b', 'section_revisit_totals', 91, 113789, 113788, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773273994, '2026-03-11 17:06:34'),
(322, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'edee23af-5044-4d4d-b543-ca6c0c03112b', 'quote_close', 93, 195781, 138119, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773274074, '2026-03-11 17:07:54'),
(323, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', '2cc16067-773a-4e48-bc01-9c3a9a557b5d', 'quote_open', 0, 8, 8, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773274074, '2026-03-11 17:07:54'),
(324, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '6d7a02b2-ea9b-48c3-a7f0-3c2f8fa53f91', 'quote_open', 0, 24, 24, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274660, '2026-03-11 17:17:40'),
(325, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '6d7a02b2-ea9b-48c3-a7f0-3c2f8fa53f91', 'quote_scroll', 50, 54998, 54998, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274716, '2026-03-11 17:18:36'),
(326, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '6d7a02b2-ea9b-48c3-a7f0-3c2f8fa53f91', 'section_view_totals', 76, 64465, 64465, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274724, '2026-03-11 17:18:44'),
(327, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '6d7a02b2-ea9b-48c3-a7f0-3c2f8fa53f91', 'quote_price_review_loop', 76, 64486, 64486, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274724, '2026-03-11 17:18:44'),
(328, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '6d7a02b2-ea9b-48c3-a7f0-3c2f8fa53f91', 'quote_scroll', 90, 67341, 67341, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274727, '2026-03-11 17:18:47'),
(329, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'quote_open', 0, 20, 20, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274753, '2026-03-11 17:19:13'),
(330, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'quote_scroll', 50, 9772, 9772, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274763, '2026-03-11 17:19:23'),
(331, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'section_view_totals', 76, 28529, 28529, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274781, '2026-03-11 17:19:41'),
(332, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'quote_price_review_loop', 76, 28552, 28552, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274781, '2026-03-11 17:19:41'),
(333, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'section_revisit_totals', 85, 47781, 47781, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274801, '2026-03-11 17:20:01'),
(334, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'section_revisit_totals', 90, 53785, 53785, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274807, '2026-03-11 17:20:07'),
(335, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'quote_scroll', 90, 53687, 53687, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274807, '2026-03-11 17:20:07'),
(336, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'section_revisit_totals', 100, 71705, 71705, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274824, '2026-03-11 17:20:24'),
(337, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'section_revisit_totals', 100, 79117, 79117, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274833, '2026-03-11 17:20:33'),
(338, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '550b98d6-8281-42f5-80f3-2b57f9dc646b', 'section_revisit_totals', 100, 99442, 99442, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773274852, '2026-03-11 17:20:52'),
(339, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '23b86b5b-3c6a-4a4b-bad7-6eae57972dc4', 'quote_open', 0, 21, 21, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275032, '2026-03-11 17:23:52'),
(340, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', 'ede1c068-2dcf-4c83-88b9-695092d12789', 'quote_open', 0, 18, 18, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275039, '2026-03-11 17:23:59'),
(341, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', 'ede1c068-2dcf-4c83-88b9-695092d12789', 'quote_scroll', 50, 36918, 36918, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275076, '2026-03-11 17:24:36'),
(342, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', 'ede1c068-2dcf-4c83-88b9-695092d12789', 'section_view_totals', 74, 48832, 48832, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275088, '2026-03-11 17:24:48'),
(343, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', 'ede1c068-2dcf-4c83-88b9-695092d12789', 'quote_price_review_loop', 76, 53792, 53792, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275093, '2026-03-11 17:24:53'),
(344, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', 'ede1c068-2dcf-4c83-88b9-695092d12789', 'section_revisit_totals', 82, 71634, 71634, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275110, '2026-03-11 17:25:10'),
(345, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '01ee9132-2899-4c32-b922-c0cfb37f4a14', 'quote_open', 0, 24, 24, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275127, '2026-03-11 17:25:27'),
(346, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '01ee9132-2899-4c32-b922-c0cfb37f4a14', 'quote_scroll', 50, 2014, 2014, 'ÈD¸', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275129, '2026-03-11 17:25:29'),
(347, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '01ee9132-2899-4c32-b922-c0cfb37f4a14', 'section_view_totals', 76, 11947, 11947, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275139, '2026-03-11 17:25:39'),
(348, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', '01ee9132-2899-4c32-b922-c0cfb37f4a14', 'quote_price_review_loop', 76, 11966, 11967, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773275139, '2026-03-11 17:25:39'),
(349, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', '2cc16067-773a-4e48-bc01-9c3a9a557b5d', 'quote_close', 0, 1104081, 2708, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773275178, '2026-03-11 17:26:18'),
(350, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', '7294ad95-b858-487c-ad7f-b7a5e7757bd1', 'quote_open', 0, 0, 0, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773275178, '2026-03-11 17:26:18'),
(351, 37, '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '6ae7ca6a-9d1c-4cd0-8a7a-af40012af76e', 'quote_open', 0, 52, 52, '»öb7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Safari/605.1.15', 1773275937, '2026-03-11 17:38:57'),
(352, 37, '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '6ae7ca6a-9d1c-4cd0-8a7a-af40012af76e', 'quote_scroll', 51, 3028, 3028, '»öb7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Safari/605.1.15', 1773275940, '2026-03-11 17:39:00'),
(353, 37, '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '6ae7ca6a-9d1c-4cd0-8a7a-af40012af76e', 'section_view_totals', 64, 4333, 4333, '»öb7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Safari/605.1.15', 1773275941, '2026-03-11 17:39:01'),
(354, 37, '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '6ae7ca6a-9d1c-4cd0-8a7a-af40012af76e', 'quote_price_review_loop', 65, 4363, 4363, '»öb7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Safari/605.1.15', 1773275941, '2026-03-11 17:39:01'),
(355, 37, '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '6ae7ca6a-9d1c-4cd0-8a7a-af40012af76e', 'quote_close', 71, 22536, 22536, '»öb7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Safari/605.1.15', 1773275960, '2026-03-11 17:39:20'),
(356, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', '7294ad95-b858-487c-ad7f-b7a5e7757bd1', 'quote_close', 0, 2623531, 6912, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773277803, '2026-03-11 18:10:03'),
(357, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '28197c6f-873e-4053-a43b-68c71d046b48', 'quote_open', 0, 5, 5, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773282991, '2026-03-11 19:36:31'),
(358, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '28197c6f-873e-4053-a43b-68c71d046b48', 'quote_scroll', 50, 15900, 15900, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283008, '2026-03-11 19:36:48'),
(359, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '28197c6f-873e-4053-a43b-68c71d046b48', 'section_view_totals', 77, 16701, 16701, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283008, '2026-03-11 19:36:48'),
(360, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '28197c6f-873e-4053-a43b-68c71d046b48', 'quote_price_review_loop', 78, 16735, 16735, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283008, '2026-03-11 19:36:48'),
(361, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '8eabf95b-1101-4840-a992-546fcf4ee387', 'section_view_totals', 100, 50, 50, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283012, '2026-03-11 19:36:52'),
(362, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '8eabf95b-1101-4840-a992-546fcf4ee387', 'quote_open', 0, 9, 9, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283012, '2026-03-11 19:36:52'),
(363, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '8eabf95b-1101-4840-a992-546fcf4ee387', 'quote_scroll', 100, 49, 49, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283012, '2026-03-11 19:36:52'),
(364, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '28197c6f-873e-4053-a43b-68c71d046b48', 'quote_close', 78, 20137, 20137, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283012, '2026-03-11 19:36:52'),
(365, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '8eabf95b-1101-4840-a992-546fcf4ee387', 'quote_scroll', 100, 49, 49, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283012, '2026-03-11 19:36:52'),
(366, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '8eabf95b-1101-4840-a992-546fcf4ee387', 'quote_price_review_loop', 100, 1132, 1132, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283013, '2026-03-11 19:36:53'),
(367, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '8eabf95b-1101-4840-a992-546fcf4ee387', 'section_revisit_totals', 100, 31148, 31148, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283044, '2026-03-11 19:37:24'),
(368, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'd0de2117-e346-446e-bbd9-a775bafca2d6', 'quote_open', 0, 10, 10, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283052, '2026-03-11 19:37:32'),
(369, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', '8eabf95b-1101-4840-a992-546fcf4ee387', 'quote_close', 100, 40263, 40263, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283052, '2026-03-11 19:37:32'),
(370, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'd0de2117-e346-446e-bbd9-a775bafca2d6', 'quote_scroll', 50, 1824, 1824, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283054, '2026-03-11 19:37:34'),
(371, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'd0de2117-e346-446e-bbd9-a775bafca2d6', 'section_view_totals', 78, 2808, 2808, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283055, '2026-03-11 19:37:35'),
(372, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'd0de2117-e346-446e-bbd9-a775bafca2d6', 'quote_price_review_loop', 81, 2841, 2841, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283055, '2026-03-11 19:37:35'),
(373, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'd0de2117-e346-446e-bbd9-a775bafca2d6', 'quote_scroll', 90, 2943, 2943, 'ÈD½ú', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773283055, '2026-03-11 19:37:35'),
(374, 41, '7ae17fce-3180-491a-aede-2e87aca47d9a', '7ae17fce-3180-491a-aede-2e87aca47d9a', '154eb2f3-91f2-42c7-b9ed-17b65c5aaeb5', 'quote_open', 0, 48, 47, '½­º', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773298689, '2026-03-11 23:58:09'),
(375, 41, 'bd3f24dd-a511-4b35-a0ee-ad0713c39acb', 'bd3f24dd-a511-4b35-a0ee-ad0713c39acb', '886255f4-b6e0-453d-87f3-6bde4121c678', 'quote_open', 0, 0, 0, '½­º', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773298696, '2026-03-11 23:58:16'),
(376, 41, 'bd3f24dd-a511-4b35-a0ee-ad0713c39acb', 'bd3f24dd-a511-4b35-a0ee-ad0713c39acb', '886255f4-b6e0-453d-87f3-6bde4121c678', 'quote_close', 3, 6077, 6077, '½­º', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773298702, '2026-03-11 23:58:22'),
(377, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', '33a5caa3-75e3-4c34-93b4-85ebbebbe38c', 'quote_open', 0, 15, 15, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331487, '2026-03-12 09:04:47'),
(378, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', '33a5caa3-75e3-4c34-93b4-85ebbebbe38c', 'quote_scroll', 50, 9751, 9751, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331497, '2026-03-12 09:04:57'),
(379, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', '33a5caa3-75e3-4c34-93b4-85ebbebbe38c', 'section_view_totals', 63, 12980, 12980, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331500, '2026-03-12 09:05:00'),
(380, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', '33a5caa3-75e3-4c34-93b4-85ebbebbe38c', 'quote_price_review_loop', 64, 13001, 13001, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331500, '2026-03-12 09:05:00'),
(381, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', '33a5caa3-75e3-4c34-93b4-85ebbebbe38c', 'quote_scroll', 90, 15004, 15004, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331502, '2026-03-12 09:05:02'),
(382, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', '33a5caa3-75e3-4c34-93b4-85ebbebbe38c', 'quote_close', 96, 18625, 17973, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331506, '2026-03-12 09:05:06'),
(383, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', 'bcee6907-146a-4da8-b46f-acb5137ded91', 'quote_open', 0, 15, 15, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331848, '2026-03-12 09:10:48'),
(384, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', 'bcee6907-146a-4da8-b46f-acb5137ded91', 'quote_scroll', 50, 11479, 11479, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331859, '2026-03-12 09:10:59'),
(385, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', 'bcee6907-146a-4da8-b46f-acb5137ded91', 'quote_price_review_loop', 64, 25915, 25915, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331874, '2026-03-12 09:11:14'),
(386, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', 'bcee6907-146a-4da8-b46f-acb5137ded91', 'section_view_totals', 63, 25893, 25893, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331874, '2026-03-12 09:11:14'),
(387, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', 'bcee6907-146a-4da8-b46f-acb5137ded91', 'quote_close', 85, 27127, 26482, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 1773331875, '2026-03-12 09:11:15'),
(388, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '2de9e971-123b-4d3f-87e8-700ed5fe9e37', 'section_view_totals', 71, 98, 0, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773332382, '2026-03-12 09:19:42'),
(389, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '2de9e971-123b-4d3f-87e8-700ed5fe9e37', 'quote_open', 0, 68, 0, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773332382, '2026-03-12 09:19:42'),
(390, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '2de9e971-123b-4d3f-87e8-700ed5fe9e37', 'quote_scroll', 71, 97, 0, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773332382, '2026-03-12 09:19:42'),
(391, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'a5e0dd55-fd9b-44bb-9157-d64ee5bdcf72', 'quote_open', 0, 3, 3, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773332382, '2026-03-12 09:19:42'),
(392, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'a5e0dd55-fd9b-44bb-9157-d64ee5bdcf72', 'quote_scroll', 50, 1505, 1505, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773332384, '2026-03-12 09:19:44'),
(393, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'a5e0dd55-fd9b-44bb-9157-d64ee5bdcf72', 'section_view_totals', 61, 1839, 1839, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773332384, '2026-03-12 09:19:44'),
(394, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'a5e0dd55-fd9b-44bb-9157-d64ee5bdcf72', 'quote_price_review_loop', 64, 1888, 1888, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773332384, '2026-03-12 09:19:44'),
(395, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', 'a5e0dd55-fd9b-44bb-9157-d64ee5bdcf72', 'quote_scroll', 90, 2221, 2221, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773332384, '2026-03-12 09:19:44'),
(396, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', 'e7323b9f-3259-441d-adc0-8d15a727ad47', 'quote_open', 0, 4, 4, 'ÈD½E', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 1773333718, '2026-03-12 09:41:58'),
(397, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', '0dd85ec3-813f-46a8-b2a5-fad77f5100fa', 'quote_open', 0, 1, 1, 'ÈDß', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773335931, '2026-03-12 10:18:51'),
(398, 44, '70702ce3-4db2-4bf1-abd8-20820b90017d', '70702ce3-4db2-4bf1-abd8-20820b90017d', 'd61c26d9-658a-4baf-8183-ee5c51825132', 'quote_open', 0, 48, 47, 'É¯Ò[', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773337939, '2026-03-12 10:52:19'),
(399, 44, '70702ce3-4db2-4bf1-abd8-20820b90017d', '70702ce3-4db2-4bf1-abd8-20820b90017d', 'd61c26d9-658a-4baf-8183-ee5c51825132', 'quote_close', 13, 565137, 37011, 'É¯Ò[', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773338504, '2026-03-12 11:01:44'),
(400, 44, '70702ce3-4db2-4bf1-abd8-20820b90017d', '70702ce3-4db2-4bf1-abd8-20820b90017d', 'a424e6cc-8eaa-4372-a7d0-cfc632c2884a', 'quote_open', 0, 0, 0, 'É¯Ò[', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773338504, '2026-03-12 11:01:44'),
(401, 44, '70702ce3-4db2-4bf1-abd8-20820b90017d', '70702ce3-4db2-4bf1-abd8-20820b90017d', 'a424e6cc-8eaa-4372-a7d0-cfc632c2884a', 'quote_scroll', 50, 75655, 7557, 'É¯Ò[', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773338581, '2026-03-12 11:03:01'),
(402, 44, '70702ce3-4db2-4bf1-abd8-20820b90017d', '70702ce3-4db2-4bf1-abd8-20820b90017d', 'a424e6cc-8eaa-4372-a7d0-cfc632c2884a', 'section_view_totals', 59, 79843, 11745, 'É¯Ò[', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773338584, '2026-03-12 11:03:04'),
(403, 44, '70702ce3-4db2-4bf1-abd8-20820b90017d', '70702ce3-4db2-4bf1-abd8-20820b90017d', 'a424e6cc-8eaa-4372-a7d0-cfc632c2884a', 'quote_price_review_loop', 61, 79876, 11778, 'É¯Ò[', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773338584, '2026-03-12 11:03:04'),
(404, 44, '70702ce3-4db2-4bf1-abd8-20820b90017d', '70702ce3-4db2-4bf1-abd8-20820b90017d', 'a424e6cc-8eaa-4372-a7d0-cfc632c2884a', 'quote_scroll', 90, 86548, 18450, 'É¯Ò[', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773338590, '2026-03-12 11:03:10'),
(405, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'f110218e-ea61-4693-91fa-ac20e00b0e56', 'quote_open', 0, 45, 44, 'ÈDß', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773339059, '2026-03-12 11:10:59'),
(406, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'f110218e-ea61-4693-91fa-ac20e00b0e56', 'quote_scroll', 50, 2626, 2625, 'ÈDß', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773339062, '2026-03-12 11:11:02'),
(407, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'f110218e-ea61-4693-91fa-ac20e00b0e56', 'quote_price_review_loop', 76, 3746, 3745, 'ÈDß', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773339062, '2026-03-12 11:11:02'),
(408, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'f110218e-ea61-4693-91fa-ac20e00b0e56', 'section_view_totals', 76, 3714, 3713, 'ÈDß', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773339062, '2026-03-12 11:11:02'),
(409, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '94011b89-30ff-4172-9d6c-79a2b50bda02', 'quote_open', 0, 22, 22, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773346622, '2026-03-12 13:17:02'),
(410, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '94011b89-30ff-4172-9d6c-79a2b50bda02', 'quote_scroll', 50, 3431, 3431, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773346625, '2026-03-12 13:17:05'),
(411, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '94011b89-30ff-4172-9d6c-79a2b50bda02', 'quote_price_review_loop', 77, 4668, 4668, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773346626, '2026-03-12 13:17:06'),
(412, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '94011b89-30ff-4172-9d6c-79a2b50bda02', 'section_view_totals', 76, 4602, 4602, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773346626, '2026-03-12 13:17:06'),
(413, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '94011b89-30ff-4172-9d6c-79a2b50bda02', 'quote_scroll', 90, 6480, 6480, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773346628, '2026-03-12 13:17:08'),
(414, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', '7cb75764-995a-47a8-9e60-e3a72120532d', 'quote_open', 0, 47, 0, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1773354315, '2026-03-12 15:25:15'),
(415, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '90f9800b-76b9-400a-95d4-1104abf008a9', 'quote_open', 0, 22, 0, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773355269, '2026-03-12 15:41:09'),
(416, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '90f9800b-76b9-400a-95d4-1104abf008a9', 'quote_scroll', 60, 38, 0, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773355269, '2026-03-12 15:41:09'),
(417, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'a0000fd6-15cb-4539-abb2-a600bf3d2df0', 'quote_open', 0, 101, 101, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773358580, '2026-03-12 16:36:20'),
(418, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'a0000fd6-15cb-4539-abb2-a600bf3d2df0', 'quote_scroll', 50, 14851, 14851, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773358594, '2026-03-12 16:36:34'),
(419, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'a0000fd6-15cb-4539-abb2-a600bf3d2df0', 'quote_price_review_loop', 79, 52423, 52423, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773358632, '2026-03-12 16:37:12'),
(420, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'a0000fd6-15cb-4539-abb2-a600bf3d2df0', 'section_view_totals', 78, 52375, 52375, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773358632, '2026-03-12 16:37:12'),
(421, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'a0000fd6-15cb-4539-abb2-a600bf3d2df0', 'quote_scroll', 90, 54460, 54460, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773358634, '2026-03-12 16:37:14'),
(422, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', '092815da-513b-441a-9653-ff7deca67f32', 'quote_open', 0, 54, 54, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773367600, '2026-03-12 19:06:40'),
(423, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'abd28253-e185-4bd3-a432-0453e5d41d3d', 'quote_open', 0, 23, 23, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773367657, '2026-03-12 19:07:37'),
(424, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', '092815da-513b-441a-9653-ff7deca67f32', 'quote_close', 44, 55411, 55411, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773367657, '2026-03-12 19:07:37'),
(425, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '3f37d60c-6b02-4926-91b8-111d6c60fa83', 'quote_open', 0, 16, 16, '»;Æ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773367731, '2026-03-12 19:08:51'),
(426, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '3f37d60c-6b02-4926-91b8-111d6c60fa83', 'quote_scroll', 50, 2807, 2807, '»;Æ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773367734, '2026-03-12 19:08:54'),
(427, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '3f37d60c-6b02-4926-91b8-111d6c60fa83', 'section_view_totals', 76, 3761, 3761, '»;Æ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773367735, '2026-03-12 19:08:55'),
(428, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '3f37d60c-6b02-4926-91b8-111d6c60fa83', 'quote_price_review_loop', 78, 3794, 3794, '»;Æ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773367735, '2026-03-12 19:08:55'),
(429, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '3f37d60c-6b02-4926-91b8-111d6c60fa83', 'quote_scroll', 90, 4128, 4128, '»;Æ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773367735, '2026-03-12 19:08:55'),
(430, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '5852505f-5573-4645-9da2-35f38f5ff46e', 'quote_open', 0, 43, 43, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773369034, '2026-03-12 19:30:34'),
(431, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '5852505f-5573-4645-9da2-35f38f5ff46e', 'quote_close', 0, 2226, 2226, 'ÈD½', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773369035, '2026-03-12 19:30:35'),
(432, 44, '953ab7d3-e182-4a73-8877-3cdb26e32a4b', '953ab7d3-e182-4a73-8877-3cdb26e32a4b', 'ec7c88cf-08f7-4705-994b-28f25b872584', 'quote_open', 0, 158, 0, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773373601, '2026-03-12 20:46:41'),
(433, 44, '953ab7d3-e182-4a73-8877-3cdb26e32a4b', '953ab7d3-e182-4a73-8877-3cdb26e32a4b', 'ec7c88cf-08f7-4705-994b-28f25b872584', 'quote_scroll', 72, 400, 0, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773373601, '2026-03-12 20:46:41');
INSERT INTO `quote_events` (`id`, `cotizacion_id`, `visitor_id`, `session_id`, `page_id`, `tipo`, `max_scroll`, `open_ms`, `visible_ms`, `ip`, `ua`, `ts_unix`, `created_at`) VALUES
(434, 44, '953ab7d3-e182-4a73-8877-3cdb26e32a4b', '953ab7d3-e182-4a73-8877-3cdb26e32a4b', 'ec7c88cf-08f7-4705-994b-28f25b872584', 'section_view_totals', 72, 400, 0, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773373601, '2026-03-12 20:46:41'),
(435, 44, 'c8d20029-2b8c-4102-86a8-e910ba827070', 'c8d20029-2b8c-4102-86a8-e910ba827070', '30a0784e-df2d-45a1-a1bc-9cb52e4ad97a', 'section_view_totals', 72, 48, 48, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773375817, '2026-03-12 21:23:37'),
(436, 44, 'c8d20029-2b8c-4102-86a8-e910ba827070', 'c8d20029-2b8c-4102-86a8-e910ba827070', '30a0784e-df2d-45a1-a1bc-9cb52e4ad97a', 'quote_scroll', 72, 48, 48, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773375817, '2026-03-12 21:23:37'),
(437, 44, 'c8d20029-2b8c-4102-86a8-e910ba827070', 'c8d20029-2b8c-4102-86a8-e910ba827070', '30a0784e-df2d-45a1-a1bc-9cb52e4ad97a', 'quote_open', 0, 11, 11, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773375817, '2026-03-12 21:23:37'),
(438, 44, 'c8d20029-2b8c-4102-86a8-e910ba827070', 'c8d20029-2b8c-4102-86a8-e910ba827070', '30a0784e-df2d-45a1-a1bc-9cb52e4ad97a', 'quote_close', 72, 1114, 1114, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1773375818, '2026-03-12 21:23:38'),
(439, 26, '31fb86d3-d884-411d-8912-9ab4fa18f368', '31fb86d3-d884-411d-8912-9ab4fa18f368', '27815eca-eb90-413f-a32f-9d69e55d6490', 'quote_open', 0, 43, 43, '½­ff', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773422394, '2026-03-13 10:19:54'),
(440, 26, '31fb86d3-d884-411d-8912-9ab4fa18f368', '31fb86d3-d884-411d-8912-9ab4fa18f368', '27815eca-eb90-413f-a32f-9d69e55d6490', 'quote_scroll', 51, 16636, 16636, '½­ff', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773422411, '2026-03-13 10:20:11'),
(441, 26, '31fb86d3-d884-411d-8912-9ab4fa18f368', '31fb86d3-d884-411d-8912-9ab4fa18f368', '27815eca-eb90-413f-a32f-9d69e55d6490', 'section_view_totals', 56, 16721, 16721, '½­ff', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773422411, '2026-03-13 10:20:11'),
(442, 26, '31fb86d3-d884-411d-8912-9ab4fa18f368', '31fb86d3-d884-411d-8912-9ab4fa18f368', '27815eca-eb90-413f-a32f-9d69e55d6490', 'quote_price_review_loop', 59, 16753, 16753, '½­ff', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773422411, '2026-03-13 10:20:11'),
(443, 26, '31fb86d3-d884-411d-8912-9ab4fa18f368', '31fb86d3-d884-411d-8912-9ab4fa18f368', '27815eca-eb90-413f-a32f-9d69e55d6490', 'quote_scroll', 90, 22168, 22168, '½­ff', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1773422416, '2026-03-13 10:20:16'),
(444, 27, '90737b18-cf92-4e18-9670-8d91342731d1', '90737b18-cf92-4e18-9670-8d91342731d1', '5df3613c-3761-4300-883f-44c6c7a8c710', 'quote_open', 0, 23, 23, '»ÙâR', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/552.0.0.24.108;FBBV/903536508;FBDV/iPhone18,2;FBMD/iPhone;FBSN/iOS;FBSV/26.3.1;FBSS/3;FBCR/;FBID/phone;FBLC/es_LA;FBOP/80]', 1773435966, '2026-03-13 14:06:06'),
(445, 27, '90737b18-cf92-4e18-9670-8d91342731d1', '90737b18-cf92-4e18-9670-8d91342731d1', '5df3613c-3761-4300-883f-44c6c7a8c710', 'quote_scroll', 50, 4171, 4171, 'Jôd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/552.0.0.24.108;FBBV/903536508;FBDV/iPhone18,2;FBMD/iPhone;FBSN/iOS;FBSV/26.3.1;FBSS/3;FBCR/;FBID/phone;FBLC/es_LA;FBOP/80]', 1773435971, '2026-03-13 14:06:11'),
(446, 27, '90737b18-cf92-4e18-9670-8d91342731d1', '90737b18-cf92-4e18-9670-8d91342731d1', '5df3613c-3761-4300-883f-44c6c7a8c710', 'section_view_totals', 60, 5239, 5239, 'Jôd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/552.0.0.24.108;FBBV/903536508;FBDV/iPhone18,2;FBMD/iPhone;FBSN/iOS;FBSV/26.3.1;FBSS/3;FBCR/;FBID/phone;FBLC/es_LA;FBOP/80]', 1773435971, '2026-03-13 14:06:11'),
(447, 27, '90737b18-cf92-4e18-9670-8d91342731d1', '90737b18-cf92-4e18-9670-8d91342731d1', '5df3613c-3761-4300-883f-44c6c7a8c710', 'quote_price_review_loop', 61, 5273, 5273, 'Jôd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/552.0.0.24.108;FBBV/903536508;FBDV/iPhone18,2;FBMD/iPhone;FBSN/iOS;FBSV/26.3.1;FBSS/3;FBCR/;FBID/phone;FBLC/es_LA;FBOP/80]', 1773435972, '2026-03-13 14:06:12'),
(448, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '169986de-055a-465a-9aed-d97c0069f591', 'quote_open', 0, 13, 13, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773443782, '2026-03-13 16:16:22'),
(449, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '169986de-055a-465a-9aed-d97c0069f591', 'quote_scroll', 50, 2750, 2750, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773443785, '2026-03-13 16:16:25'),
(450, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '169986de-055a-465a-9aed-d97c0069f591', 'section_view_totals', 76, 4280, 4280, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773443787, '2026-03-13 16:16:27'),
(451, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '169986de-055a-465a-9aed-d97c0069f591', 'quote_price_review_loop', 77, 4313, 4313, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773443787, '2026-03-13 16:16:27'),
(452, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '169986de-055a-465a-9aed-d97c0069f591', 'quote_scroll', 90, 5709, 5709, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773443788, '2026-03-13 16:16:28'),
(453, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '47747f49-3131-415e-bca5-d4c749c6231e', 'quote_open', 0, 50, 0, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773444457, '2026-03-13 16:27:37'),
(454, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '4c181ede-fd9c-4dae-b4a7-86e8dc05d0a2', 'quote_open', 0, 5, 5, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773444457, '2026-03-13 16:27:37'),
(455, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '4c181ede-fd9c-4dae-b4a7-86e8dc05d0a2', 'quote_scroll', 50, 5931, 5931, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773444464, '2026-03-13 16:27:44'),
(456, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '4c181ede-fd9c-4dae-b4a7-86e8dc05d0a2', 'section_view_totals', 76, 24016, 24016, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773444482, '2026-03-13 16:28:02'),
(457, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '4c181ede-fd9c-4dae-b4a7-86e8dc05d0a2', 'quote_price_review_loop', 76, 24132, 24132, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773444482, '2026-03-13 16:28:02'),
(458, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '4c181ede-fd9c-4dae-b4a7-86e8dc05d0a2', 'quote_scroll', 90, 33079, 33079, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773444491, '2026-03-13 16:28:11'),
(459, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '5ae3590c-f18a-4f4e-8aa5-5c25d967b69b', 'quote_open', 0, 31, 0, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773460652, '2026-03-13 20:57:32'),
(460, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '5ae3590c-f18a-4f4e-8aa5-5c25d967b69b', 'quote_scroll', 77, 94, 0, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773460652, '2026-03-13 20:57:32'),
(461, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '5ae3590c-f18a-4f4e-8aa5-5c25d967b69b', 'section_view_totals', 77, 94, 0, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 1773460652, '2026-03-13 20:57:32'),
(462, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', '2cf071f4-7547-45fe-af66-540ad910b6ca', 'quote_open', 0, 2, 1, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773527252, '2026-03-14 15:27:32'),
(463, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', '5f944d1c-bbff-4e3a-ac9d-810f87fecaad', 'quote_open', 0, 72, 71, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773531926, '2026-03-14 16:45:26'),
(464, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', '5f944d1c-bbff-4e3a-ac9d-810f87fecaad', 'quote_close', 0, 8596, 8595, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 1773531934, '2026-03-14 16:45:34'),
(465, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '36a94123-836e-48eb-8d3b-a9f5d0e960a9', 'quote_open', 0, 45, 45, '»ö`I', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773537636, '2026-03-14 18:20:36'),
(466, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '36a94123-836e-48eb-8d3b-a9f5d0e960a9', 'quote_scroll', 50, 3258, 3258, '»ö`I', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773537639, '2026-03-14 18:20:39'),
(467, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '36a94123-836e-48eb-8d3b-a9f5d0e960a9', 'section_view_totals', 60, 3647, 3647, '»ö`I', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773537640, '2026-03-14 18:20:40'),
(468, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '36a94123-836e-48eb-8d3b-a9f5d0e960a9', 'quote_price_review_loop', 60, 3669, 3669, '»ö`I', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773537640, '2026-03-14 18:20:40'),
(469, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '36a94123-836e-48eb-8d3b-a9f5d0e960a9', 'quote_scroll', 90, 124091, 9260, '»ö`I', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1773537761, '2026-03-14 18:22:41');

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
(4, 13, 'f056f525-0930-4e15-b31b-14ae9cd3be3e', NULL, NULL, '201.162.169.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/148.0 Mobile/15E148 Safari/604.1', 3, 15209, 0, 1, '2026-03-11 15:20:14', '2026-03-11 15:20:31', 0),
(5, 30, 'c9127944-a581-45d4-b36e-1e6745c42578', 'c9127944-a581-45d4-b36e-1e6745c42578', NULL, 'ÈD', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', 92, 13483, 1464249, 0, '2026-03-07 16:37:40', '2026-03-07 19:36:22', 0),
(6, 32, '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', '62dfc03a-dfd8-4397-8a1a-ddd8a964e304', NULL, '»ö`I', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 91, 10925, 124091, 0, '2026-03-07 17:49:25', '2026-03-15 01:22:41', 0),
(7, 33, 'ae9d001d-a414-4151-9801-fc23ef2024c0', 'ae9d001d-a414-4151-9801-fc23ef2024c0', NULL, '»öfa', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 100, 82444, 178123, 0, '2026-03-07 18:09:10', '2026-03-07 22:30:43', 0),
(8, 31, 'ad84fc6a-b403-4e00-8665-5e9bd227535c', 'ad84fc6a-b403-4e00-8665-5e9bd227535c', NULL, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 Safari/604.1 [FBAN/FBIOS;FBAV/550.0.0.34.65;FBBV/890804754;FBDV/iPhone14,7;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5', 100, 34955, 101310, 0, '2026-03-07 23:51:07', '2026-03-07 23:52:48', 0),
(9, 35, 'daf80f72-cd18-462e-92ce-e5856ca1082c', 'daf80f72-cd18-462e-92ce-e5856ca1082c', NULL, '±æi1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 100, 123782, 190861, 0, '2026-03-09 16:59:24', '2026-03-10 04:58:25', 0),
(10, 34, '0c38d2b1-4769-40d5-bbbf-9889c3361e59', '0c38d2b1-4769-40d5-bbbf-9889c3361e59', NULL, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 100, 131827, 16414428, 0, '2026-03-09 17:47:15', '2026-03-09 22:30:05', 0),
(11, 36, '1b1c8643-3144-4b08-88e1-80f3bf080af3', '1b1c8643-3144-4b08-88e1-80f3bf080af3', NULL, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 84, 21547, 238754, 0, '2026-03-09 18:09:18', '2026-03-09 18:13:38', 0),
(12, 36, '532466a6-6d84-4961-8a0b-7933efd55daa', '532466a6-6d84-4961-8a0b-7933efd55daa', NULL, '½­±S', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 73, 10581, 10581, 0, '2026-03-09 20:09:16', '2026-03-09 20:09:27', 0),
(13, 34, 'ae053f14-505f-4406-a37a-e91841aed2c8', 'ae053f14-505f-4406-a37a-e91841aed2c8', NULL, '»¼O', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 100, 51052, 51052, 0, '2026-03-09 22:30:05', '2026-03-09 23:42:01', 0),
(14, 37, '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', '61f00c2c-cba5-4a8c-867b-59dcbea4a69d', NULL, '»öb7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1', 100, 82602, 82602, 0, '2026-03-09 23:18:17', '2026-03-14 03:57:32', 0),
(15, 38, 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', 'cbc9b4d6-87d3-4561-a942-0c1bf8f7991a', NULL, 'B°', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 95, 73148, 73148, 0, '2026-03-10 01:24:18', '2026-03-10 01:25:31', 0),
(16, 39, 'a6a0f399-9dd0-450a-809d-73342ec4612f', 'a6a0f399-9dd0-450a-809d-73342ec4612f', NULL, 'ÈD½Î', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 68, 9443, 9443, 0, '2026-03-10 17:14:42', '2026-03-10 17:14:51', 0),
(17, 39, '462a6861-0ad5-47be-a2b6-64b318a94149', '462a6861-0ad5-47be-a2b6-64b318a94149', NULL, '»õà', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 100, 134058, 134058, 0, '2026-03-10 17:16:03', '2026-03-10 17:18:16', 0),
(18, 28, '25c35c6c-e73e-4083-9cf8-d518c61324c6', '25c35c6c-e73e-4083-9cf8-d518c61324c6', NULL, '½­0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 100, 620140, 620140, 0, '2026-03-10 18:51:43', '2026-03-10 19:02:05', 0),
(19, 28, 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', 'b4b9dbe8-0d1f-437d-8209-121488e35bd0', NULL, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 90, 6114, 6114, 0, '2026-03-10 19:09:50', '2026-03-10 19:09:55', 0),
(20, 41, 'b39f1d84-91a6-4013-a49f-c83966037a22', 'b39f1d84-91a6-4013-a49f-c83966037a22', NULL, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 82, 19313, 30685, 0, '2026-03-10 20:39:26', '2026-03-10 20:39:57', 0),
(21, 41, '3740c37c-8a91-4764-b45a-25ad246fa4e0', '3740c37c-8a91-4764-b45a-25ad246fa4e0', NULL, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 97, 78788, 1817524, 0, '2026-03-10 20:39:57', '2026-03-11 15:03:19', 0),
(22, 40, '05a4485e-7751-4618-9720-fcb32525ed40', '05a4485e-7751-4618-9720-fcb32525ed40', NULL, '½­oi', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 90, 6943, 6943, 0, '2026-03-10 20:43:27', '2026-03-12 22:25:15', 0),
(23, 41, '284a0b27-df72-4456-aee3-77b6b33b2e69', '284a0b27-df72-4456-aee3-77b6b33b2e69', NULL, 'É¢¨f', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 90, 3125, 3125, 0, '2026-03-10 20:55:13', '2026-03-10 20:55:16', 0),
(24, 29, '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', '6e61b1c2-2569-413b-ba62-dcd3ea9a5e3c', NULL, '½­Ó\Z', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 100, 50940, 50940, 0, '2026-03-10 21:05:33', '2026-03-10 21:06:24', 0),
(25, 29, '231e2a96-de8b-4303-8657-693986b32c1a', '231e2a96-de8b-4303-8657-693986b32c1a', NULL, 'ÈD½E', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 100, 40263, 40263, 0, '2026-03-10 21:22:54', '2026-03-12 16:41:58', 0),
(26, 28, 'ab0e6dc0-4db3-406e-882a-bd568a374b51', 'ab0e6dc0-4db3-406e-882a-bd568a374b51', NULL, 'ÉÈ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 92, 2039, 2039, 0, '2026-03-11 00:17:06', '2026-03-11 00:17:07', 0),
(27, 41, '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', '1781ea63-82d4-42b8-b7e5-8c2871a9f4b6', NULL, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 90, 9602, 9602, 0, '2026-03-11 17:49:04', '2026-03-11 17:49:14', 0),
(28, 42, 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', 'b5ea32b4-7717-45cb-88b7-f5ceb4be4249', NULL, 'ÈD¸´', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 84, 25464, 359690, 0, '2026-03-11 18:36:37', '2026-03-11 18:46:11', 0),
(29, 40, '99a65291-b1e7-482c-bb6c-090a08115462', '99a65291-b1e7-482c-bb6c-090a08115462', NULL, '½­', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', 100, 5408, 82825, 0, '2026-03-11 21:47:11', '2026-03-11 21:48:33', 0),
(30, 41, 'ef990009-0aed-4ec3-92d5-96578a7e961c', 'ef990009-0aed-4ec3-92d5-96578a7e961c', NULL, '±ðÑÉ', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 90, 55411, 1141982, 0, '2026-03-11 23:44:07', '2026-03-14 23:45:34', 0),
(31, 41, 'c7400243-717f-41d9-8e8a-d2c97804aed3', 'c7400243-717f-41d9-8e8a-d2c97804aed3', NULL, 'ÈD¸.', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 96, 10522, 11345, 0, '2026-03-12 00:04:12', '2026-03-12 00:04:23', 0),
(32, 41, '7ae17fce-3180-491a-aede-2e87aca47d9a', '7ae17fce-3180-491a-aede-2e87aca47d9a', NULL, '½­º', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 0, 47, 48, 0, '2026-03-12 00:04:23', '2026-03-12 06:58:09', 0),
(33, 41, 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', 'ca20a25f-cdec-4ab4-93c5-f4c53fb3c1a5', NULL, 'ÈD¸', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 93, 138119, 2623531, 0, '2026-03-12 00:04:38', '2026-03-12 01:10:03', 0),
(34, 41, '1a415168-4682-497f-8b05-c4055c066b7b', '1a415168-4682-497f-8b05-c4055c066b7b', NULL, 'ÈD¸¤', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 100, 99442, 99442, 0, '2026-03-12 00:17:40', '2026-03-12 00:25:39', 0),
(35, 37, '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', '55bc21fb-8c98-4bc1-b5ac-8fa46caf46f4', NULL, '»öb7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Safari/605.1.15', 71, 22536, 22536, 0, '2026-03-12 00:38:57', '2026-03-12 00:39:20', 0),
(36, 41, 'bd3f24dd-a511-4b35-a0ee-ad0713c39acb', 'bd3f24dd-a511-4b35-a0ee-ad0713c39acb', NULL, '½­º', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 3, 6077, 6077, 0, '2026-03-12 06:58:16', '2026-03-12 06:58:22', 0),
(37, 43, '48cfc077-c2ae-4813-9dc4-1f284304acec', '48cfc077-c2ae-4813-9dc4-1f284304acec', NULL, '»õb`', 'Mozilla/5.0 (Linux; Android 16; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/551.0.0.48.62;]', 96, 26482, 27127, 0, '2026-03-12 16:04:47', '2026-03-12 16:11:15', 0),
(38, 44, '70702ce3-4db2-4bf1-abd8-20820b90017d', '70702ce3-4db2-4bf1-abd8-20820b90017d', NULL, 'É¯Ò[', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 90, 37011, 565137, 0, '2026-03-12 17:52:19', '2026-03-12 18:03:10', 0),
(39, 44, '953ab7d3-e182-4a73-8877-3cdb26e32a4b', '953ab7d3-e182-4a73-8877-3cdb26e32a4b', NULL, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 72, 0, 400, 0, '2026-03-13 03:46:41', '2026-03-13 03:46:41', 0),
(40, 44, 'c8d20029-2b8c-4102-86a8-e910ba827070', 'c8d20029-2b8c-4102-86a8-e910ba827070', NULL, '»õcë', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 72, 1114, 1114, 0, '2026-03-13 04:23:37', '2026-03-13 04:23:38', 0),
(41, 26, '31fb86d3-d884-411d-8912-9ab4fa18f368', '31fb86d3-d884-411d-8912-9ab4fa18f368', NULL, '½­ff', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 90, 22168, 22168, 0, '2026-03-13 17:19:54', '2026-03-13 17:20:16', 0),
(42, 27, '90737b18-cf92-4e18-9670-8d91342731d1', '90737b18-cf92-4e18-9670-8d91342731d1', NULL, 'Jôd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/552.0.0.24.108;FBBV/903536508;FBDV/iPhone18,2;FBMD/iPhone;FBSN/iOS;FBSV/26.3.1;FBSS/3;FBCR/;FBID/phone;FBLC/es_LA;FBOP/80]', 61, 5273, 5273, 0, '2026-03-13 21:06:06', '2026-03-13 21:06:12', 0);

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

--
-- Dumping data for table `radar_fit_calibracion`
--

INSERT INTO `radar_fit_calibracion` (`id`, `empresa_id`, `activa`, `global_rate`, `rate_sess_json`, `rate_ips_json`, `rate_gap_json`, `bandas_json`, `cotizaciones`, `ventas_cerradas`, `created_at`) VALUES
(1, 2, 1, 0.4688, '{\"3-4\":0.6667,\"1\":0.5455,\"2\":0,\"8-12\":1}', '{\"2\":0.3333,\"1\":0.48,\"4+\":1}', '{\"1-3d\":0.5,\"sin\":0.4615}', '[{\"min\":0,\"max\":50000,\"label\":\"$0\\u201350K\",\"total\":22,\"cerradas\":11,\"tasa_cierre\":0.5},{\"min\":50000,\"max\":100000,\"label\":\"$50K\\u2013100K\",\"total\":10,\"cerradas\":4,\"tasa_cierre\":0.4},{\"min\":100000,\"max\":200000,\"label\":\"$100K\\u2013200K\",\"total\":0,\"cerradas\":0,\"tasa_cierre\":0.46875},{\"min\":200000,\"max\":500000,\"label\":\"$200K\\u2013500K\",\"total\":0,\"cerradas\":0,\"tasa_cierre\":0.46875},{\"min\":500000,\"max\":null,\"label\":\"$500K+\",\"total\":0,\"cerradas\":0,\"tasa_cierre\":0.46875}]', 32, 15, '2026-03-15 15:05:15');

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
(4, 2, '187.245.114.71', NULL, 'radar_open', 1773601598, '2026-03-11 01:07:20'),
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
(1, 2, '39262b57-5156-4ac3-905a-23e2c9aa7c1c', 'internal_user', 2, '187.245.114.71', 'info@closetfactory.com.mx | 187.245.114.71 | Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', 1773205640, 1773596437, '2026-03-11 01:07:20'),
(4, 2, '528751ce-1117-4625-b269-a107c7170a36', 'internal_user', 2, '187.245.114.71', 'info@closetfactory.com.mx | 187.245.114.71 | Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mob', 1773205656, 1773424257, '2026-03-11 01:07:36'),
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
  `tipo` enum('abono','cancelacion') NOT NULL DEFAULT 'abono',
  `pagado_antes` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo_despues` decimal(12,2) NOT NULL DEFAULT 0.00,
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

INSERT INTO `recibos` (`id`, `venta_id`, `empresa_id`, `numero`, `concepto`, `monto`, `tipo`, `pagado_antes`, `saldo_despues`, `fecha`, `token`, `cancelado`, `cancelado_at`, `notas`, `created_at`, `forma_pago`, `usuario_id`, `cancelado_por_id`) VALUES
(1, 1, 2, 'REC-2026-0001', 'anticipo', 100.00, 'abono', 0.00, 0.00, '2026-03-11', '94adf3e87b84801439b4996bbbaa08906276b90f5b7e1eff0e17892b8b034b7c', 0, NULL, NULL, '2026-03-11 19:11:52', NULL, NULL, NULL),
(2, 1, 2, 'REC-2026-0002', 'pago 2', 200.00, 'abono', 0.00, 0.00, '2026-03-11', '53811f5bfa57a5156f463c82de60d7e70e10bb52e4a5a9288b5192dbf83739cc', 1, '2026-03-11 19:37:02', 'Transferencia [Cancelado: error]', '2026-03-11 19:25:07', NULL, NULL, NULL),
(3, 1, 2, 'REC-2026-0003', 'pago', 50.00, 'abono', 0.00, 0.00, '2026-03-11', '0d580274a502305fbb7f9ac42d71150733dbb9f07c6da74e5aaf201221fe1b85', 1, '2026-03-11 22:24:27', ' [Cancelado: error2]', '2026-03-11 22:20:54', NULL, NULL, NULL),
(4, 1, 2, 'REC-2026-0004', 'pago', 50.00, 'abono', 0.00, 0.00, '2026-03-11', '2573cbbcdfd2b3fe7279021e15ca3472fd58ab7c11a1bd0727c719a762f34a91', 1, '2026-03-11 22:24:03', ' [Cancelado: error]', '2026-03-11 22:21:02', NULL, NULL, NULL),
(5, 1, 2, 'REC-2026-0005', 'prueba', 30.00, 'abono', 0.00, 0.00, '2026-03-11', 'b133ab99821a5797adafce8968cbe6a3514876057d219f4616b42e982c527c3f', 0, NULL, 'Transferencia', '2026-03-11 22:25:29', NULL, NULL, NULL),
(6, 1, 2, 'REC-2026-0006', 'pago', 30.00, 'abono', 0.00, 0.00, '2026-03-11', '691fa5da6e7086a3400747461c6831e2a7ffaef06225245a3908a422dead6590', 1, '2026-03-11 22:29:04', 'Transferencia [Cancelado: error]', '2026-03-11 22:28:55', NULL, NULL, NULL),
(7, 15, 2, 'REC-IMP-001', 'Pago total importado desde Sliced Invoices', 38500.00, 'abono', 0.00, 0.00, '2026-02-13', '5b5b477b7dd901756234d8f3be910e18b330e2ab8b3484184e8470c1eb761226', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-02-13 15:23:36', NULL, NULL, NULL),
(8, 14, 2, 'REC-IMP-002', 'Pago total importado desde Sliced Invoices', 73600.00, 'abono', 0.00, 0.00, '2026-02-14', '38310e73fd88278c29dfe668467593032ead253641c8f33304f04e2c020fdbbf', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-02-14 12:12:51', NULL, NULL, NULL),
(9, 13, 2, 'REC-IMP-003', 'Pago total importado desde Sliced Invoices', 50500.00, 'abono', 0.00, 0.00, '2026-02-14', 'd2d5e6301594ec0fd2219897dd56e1a1b6d5e2c94b1c3102a3756968fbd6982c', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-02-14 12:54:04', NULL, NULL, NULL),
(10, 12, 2, 'REC-IMP-004', 'Pago total importado desde Sliced Invoices', 19500.00, 'abono', 0.00, 0.00, '2026-02-17', '9f669e820115c15012c15053f64c814476ef1738b9caecb6441e647b4c528202', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-02-17 17:06:31', NULL, NULL, NULL),
(11, 11, 2, 'REC-IMP-005', 'Pago total importado desde Sliced Invoices', 15000.00, 'abono', 0.00, 0.00, '2026-02-25', '24ed8efe2542a3d1eb00a32fbe0218cb15a7ac74be556da70da8359c0d0a9862', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-02-25 11:20:13', NULL, NULL, NULL),
(12, 10, 2, 'REC-IMP-006', 'Pago total importado desde Sliced Invoices', 16400.00, 'abono', 0.00, 0.00, '2026-02-25', '7bfa1aa9203b66beb2478f6393d5bbef3cbf8b690662ca4f0c57739b75b4711c', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-02-25 15:18:27', NULL, NULL, NULL),
(13, 9, 2, 'REC-IMP-007', 'Pago total importado desde Sliced Invoices', 21000.00, 'abono', 0.00, 0.00, '2026-02-25', '79412903f467aadc90cd7ac100f056cb9c38f8676fcf47cd947f4b919aed6d10', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-02-25 16:38:45', NULL, NULL, NULL),
(14, 8, 2, 'REC-IMP-008', 'Pago total importado desde Sliced Invoices', 18600.00, 'abono', 0.00, 0.00, '2026-02-28', '78297f9f4dfc1a5c4a58f21f29ce92c4d3b3f943a3a608da65fcbb2aabe0484c', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-02-28 10:23:21', NULL, NULL, NULL),
(15, 7, 2, 'REC-IMP-009', 'Pago total importado desde Sliced Invoices', 16800.00, 'abono', 0.00, 0.00, '2026-03-02', '8e6098e7ea7a0e92a4b8889af7ba793beb758791ee080f9630af2f91938aa436', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-03-02 10:51:39', NULL, NULL, NULL),
(16, 6, 2, 'REC-IMP-010', 'Pago total importado desde Sliced Invoices', 48800.00, 'abono', 0.00, 0.00, '2026-03-04', '122b124a8f7e6f8610aa86154409580de1bd754804cd970a15f190579530f65d', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-03-04 13:59:56', NULL, NULL, NULL),
(17, 5, 2, 'REC-IMP-011', 'Pago total importado desde Sliced Invoices', 21400.00, 'abono', 0.00, 0.00, '2026-03-05', '1719bd869a36547f9d82062de1b77b7101ee650085e2f78f21f29b31fde680a1', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-03-05 14:44:52', NULL, NULL, NULL),
(18, 4, 2, 'REC-IMP-012', 'Pago total importado desde Sliced Invoices', 25400.00, 'abono', 0.00, 0.00, '2026-03-07', '21a13217bd9833b192beef5b55b81fdab98873467c5d4e0bc65fb3ca22ca4576', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-03-07 16:52:59', NULL, NULL, NULL),
(19, 3, 2, 'REC-IMP-013', 'Pago total importado desde Sliced Invoices', 66000.00, 'abono', 0.00, 0.00, '2026-03-10', '413dae8c61f7729ed835579ec19f9e95f05934df94768311b38194e68ffbf8ca', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-03-10 12:50:40', NULL, NULL, NULL),
(20, 2, 2, 'REC-IMP-014', 'Pago total importado desde Sliced Invoices', 67700.00, 'abono', 0.00, 0.00, '2026-03-13', '420a865802ee24fc1eb76893e48764c650cdaa0ff110ad240f502628eaa703d1', 0, NULL, 'Generado en migración desde Sliced Invoices', '2026-03-13 14:58:43', NULL, NULL, NULL);

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
(18, 2, 2, '2b0e13cc320a62efc5bd6f71f8db7422c4b09fe616c2f8a9c9ced2170a6001b9', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-12 23:40:33', '2026-03-13 04:40:33'),
(19, 2, 2, 'f15b45f244b6d0474ec66a3c2d7c54ca00dbcaa6cb047c284490d2134b56d4de', '187.245.114.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1', '2026-03-13 13:50:57', '2026-03-13 18:50:57'),
(20, 2, 2, '77dd5ec2eee8eced9b9b104c1d9988ac7275d2f430f63d0ff9cd2c6509a09e5b', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-13 14:42:15', '2026-03-13 19:42:15'),
(21, 2, 2, 'ca2d80b32372593635755fc8261b294cffc6232407d4ccf5252cd817ec62bad4', '187.245.114.71', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-15 13:28:22', '2026-03-15 18:28:22');

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
(2, 2, 'Jose Medina', 'info@closetfactory.com.mx', NULL, '$2y$12$At01O.B7RKzWOtEt/UtP1ucW1G53Stl37YVjwGhsSpoiIUKhp/Obm', 'admin', 1, '2026-03-15 13:28:22', '2026-03-10 13:13:59', 1, 1, 1, 1, 1, 1),
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
  `cupon_monto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notas_internas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`id`, `numero`, `empresa_id`, `cotizacion_id`, `cliente_id`, `usuario_id`, `titulo`, `slug`, `token`, `total`, `pagado`, `saldo`, `descuento_manual_amt`, `estado`, `cancelado_at`, `cancelado_motivo`, `cancelado_por_id`, `entregado_at`, `created_at`, `updated_at`, `descuento_auto_amt`, `cupon_monto`, `notas_internas`) VALUES
(1, 'VTA-2026-0001', 2, 13, 1, NULL, 'Ramón 417', 'ramon-417', 'a36b4c8a1cbde092603f2ce0460f4985e445efbb423ccaa13976c9b36bf3d092', 19300.00, 130.00, 19170.00, 150.00, 'parcial', NULL, NULL, NULL, NULL, '2026-03-11 14:16:35', '2026-03-11 22:29:56', 0.00, 0.00, NULL),
(2, NULL, 2, 41, 2, 2, 'Mario Ibarra M. Calle Arboretum #13, Residencial Bonaterra 5216621915913', 'vta-imp-inv-1275', 'b5ed1ebec247a8a0e009d30235afc9407ae1e3d027f9f85dbacdcd3ebe072fe8', 67700.00, 67700.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-03-13 14:58:43', '2026-03-13 14:58:43', 0.00, 0.00, NULL),
(3, NULL, 2, 15, 2, 2, 'Jesús Parra  6421143689', 'vta-imp-inv-1267', 'de5a0397184d2a98d8ff8d23e59cb38876a85b148085246b17c699532a2d5f28', 66000.00, 66000.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-03-10 12:50:40', '2026-03-15 14:37:36', 0.00, 0.00, NULL),
(4, NULL, 2, 31, 2, 2, 'Noemí Valle del marqués calle Tezcatlipoca 6624485651', 'vta-imp-inv-1257', '9f1c0b31a7eaf1310dbf9a39164a660fbc30998017ce7c6d46a567127b35f456', 25400.00, 25400.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-03-07 16:52:59', '2026-03-15 14:37:37', 0.00, 0.00, NULL),
(5, NULL, 2, 16, 2, 2, 'Estreberto Grijalva Arvizu Calle Petrarca Número 3, Lomas del Sur 5216622562170', 'vta-imp-inv-1251', '8aeda2e78dc162e9888084ffb0d30e7a7b3b916aa6077cf762eb772ecb4c69ba', 21400.00, 21400.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-03-05 14:44:52', '2026-03-15 14:37:37', 0.00, 0.00, NULL),
(6, NULL, 2, 28, 2, 2, 'Miguel Cruz Fraccionamiento Monteregio, Vernaccia 36 6621609041', 'vta-imp-inv-1249', '9f5c29023bdc2ea6ca64d6850e32759055c9324c6ecbb5a642417a08f3e82784', 48800.00, 48800.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-03-04 13:59:56', '2026-03-04 13:59:56', 0.00, 0.00, NULL),
(7, NULL, 2, 17, 2, 2, 'Natalia Aranda 5216621718828', 'vta-imp-inv-1239', '06c6485485dd077b9d1ad65e5ccffc9c3385e0fbc40f05e58238381cb4ff89bc', 16800.00, 16800.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-03-02 10:51:39', '2026-03-08 11:52:48', 0.00, 0.00, NULL),
(8, NULL, 2, 18, 2, 2, 'Ana Lourdes León Campillo 108 6624223314', 'vta-imp-inv-1232', '7d11bb5c01c5744d28fff1bfbba681a02060747e02e4d700f7c9116bb82f404f', 18600.00, 18600.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-02-28 10:23:21', '2026-03-15 14:37:37', 0.00, 0.00, NULL),
(9, NULL, 2, 19, 2, 2, 'Ana Luisa Romo Apasible 24, Nueva Galicia 6622332809', 'vta-imp-inv-1227', '30afc1cfdb1516075f8561cba683e440d2d84fce097fa533e3d084d770711ee9', 21000.00, 21000.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-02-25 16:38:45', '2026-02-25 16:38:45', 0.00, 0.00, NULL),
(10, NULL, 2, 20, 2, 2, 'Beatriz Guerrero Jimenez 5216622251632 Zonata 25 , Agaves', 'vta-imp-inv-1226', '1338fb8baaa7dface4672422373e8f21f82d8c1a8b37ac42eae85c0e30d29422', 16400.00, 16400.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-02-25 15:18:27', '2026-03-08 11:52:57', 0.00, 0.00, NULL),
(11, NULL, 2, 21, 2, 2, 'Ruth Isela Salomón Alvarez Horizonte dorado #76 Fracc. El Encanto 6621733456', 'vta-imp-inv-1225', '95c2499c98cbea381a4b6189b0584d13e7223316f3b8997133d6e44c5e30529e', 15000.00, 15000.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-02-25 11:20:13', '2026-03-15 14:37:37', 0.00, 0.00, NULL),
(12, NULL, 2, 22, 2, 2, 'Karla Muñoz Miguel Alemán #24, Colonia ISSSTE 6623425929', 'vta-imp-inv-1210', '5e286006d13e019d927acf218433a4a815e0fce61a2f7642eeac9aa477ea3f5e', 19500.00, 19500.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-02-17 17:06:31', '2026-03-15 14:37:37', 0.00, 0.00, NULL),
(13, NULL, 2, 23, 2, 2, 'Erik Fregoso Privada sahara #10 colonia las Lomas sección almendros 6451105652', 'vta-imp-inv-1198', 'e1ec9194ea1a5ec0ef15cb0f121e57e555a1a61ebe6ab7a768d8fafa1203b0a7', 50500.00, 50500.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-02-14 12:54:04', '2026-03-15 14:37:37', 0.00, 0.00, NULL),
(14, NULL, 2, 24, 2, 2, 'Estreberto Grijalva Arvizu Calle Petrarca Número 3, Lomas del Sur 5216622562170', 'vta-imp-inv-1197', '417aafe0dacf0e14b142d2e7df2065af4801d29437303bbc7a2d48df198f12d2', 73600.00, 73600.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-02-14 12:12:51', '2026-03-15 14:37:37', 0.00, 0.00, NULL),
(15, NULL, 2, 25, 2, 2, 'Alberto Álvarez Privada Carsoli 11, Villa Bonita 6628474329', 'vta-imp-inv-1193', '0c16f1a37fc78e4f01293040342b8e40030ea37d57e220abab611a18d969aef7', 38500.00, 38500.00, 0.00, 0.00, 'pagada', NULL, NULL, NULL, NULL, '2026-02-13 15:23:36', '2026-03-15 14:37:37', 0.00, 0.00, NULL);

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
  ADD KEY `articulo_id` (`articulo_id`),
  ADD KEY `venta_id` (`venta_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `cotizacion_archivos`
--
ALTER TABLE `cotizacion_archivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cotizacion_lineas`
--
ALTER TABLE `cotizacion_lineas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `cotizacion_log`
--
ALTER TABLE `cotizacion_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=470;

--
-- AUTO_INCREMENT for table `quote_sessions`
--
ALTER TABLE `quote_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `radar_fit_calibracion`
--
ALTER TABLE `radar_fit_calibracion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `radar_ips_internas`
--
ALTER TABLE `radar_ips_internas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `radar_visitors_internos`
--
ALTER TABLE `radar_visitors_internos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `recibos`
--
ALTER TABLE `recibos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  ADD CONSTRAINT `cotizacion_lineas_ibfk_2` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cotizacion_lineas_ibfk_3` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE SET NULL;

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
