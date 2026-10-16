-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-10-2026 a las 20:42:42
-- Versión del servidor: 10.4.24-MariaDB
-- Versión de PHP: 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mazal_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','editor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'editor',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password_hash`, `role`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Site Admin', 'admin@example.com', '$2b$12$FDfCqOVVMwIQtqV3U09QWOJPOtC0C5lTHAUgaBJ5goFgeTZ0lZbt.', 'super_admin', 1, '2026-10-16 14:44:38', '2026-05-28 19:55:41', '2026-10-16 17:44:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `image_filename`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ferretería', 'ferreteria', 'FERRETERIA.png', 1, 1, '2026-05-28 19:46:57', '2026-06-01 21:10:42'),
(2, 'Herrajes', 'herrajes', 'HERRAJES.png', 2, 1, '2026-05-28 19:46:57', '2026-06-01 21:10:42'),
(3, 'Albañilería', 'albanileria', 'ALBANILERIA.png', 3, 1, '2026-05-28 19:46:57', '2026-06-01 21:10:42'),
(4, 'Sanitarios', 'sanitarios', 'SANITARIOS.png', 4, 1, '2026-05-28 19:46:57', '2026-06-01 21:10:42'),
(5, 'Bulonería', 'buloneria', 'BULONERIA.png', 5, 1, '2026-05-28 19:46:57', '2026-06-01 21:10:42'),
(6, 'Jardinería', 'jardineria', 'JARDINERIA.png', 6, 1, '2026-05-28 19:46:57', '2026-06-01 21:10:42'),
(7, 'Tejidos', 'tejidos', 'TEJIDOS.png', 7, 1, '2026-05-28 19:46:57', '2026-06-01 21:10:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `product_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of measures: ["10cm", "20cm", "30cm"]' CHECK (json_valid(`sizes`)),
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'unit, kg, m, pair, dozen, etc.',
  `quantity_per_bulk` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '50 units, 20 kilograms, etc.',
  `image_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `category_id`, `product_code`, `name`, `slug`, `description`, `sizes`, `unit`, `quantity_per_bulk`, `image_filename`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'ANTPRO', 'Anteojos de protección', 'anteojos-de-proteccion-antpro', 'Color negro y transparente. Hechos en policarbonato.', NULL, 'unidad', '240 unidades', 'ANTPRO.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(2, 1, 'APLISI', 'Aplicador de silicona', 'aplicador-de-silicona-aplisi', 'Sin cremallera', NULL, 'unidad', '50 unidades', 'APLISI.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(3, 1, 'ARCOEX', 'Arco sierra extensible', 'arco-sierra-extensible-arcoex', 'Con hoja', NULL, 'unidad', '60 unidades', 'ARCOEX.png', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(4, 1, 'ARCOFI', 'Arco sierra fijo', 'arco-sierra-fijo-arcofi', 'Anatómico plastico mang/pistola con hoja', NULL, 'unidad', '60 unidades', 'ARCOFI.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(5, 1, 'ARCOJ', 'Arco sierra junior', 'arco-sierra-junior-arcoj', 'Con hoja', NULL, 'unidad', '240 unidades', 'ARCOJ.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(6, 1, 'BARRIFI', 'Barrita de silicona', 'barrita-de-silicona-barrifi', '30 cm de largo', '[\"fina\",\"gruesa\"]', 'kilogramo', '20 kilogramos', 'BARRIFI.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(7, 2, 'BICAZ', 'Bisagra cazoleta', 'bisagra-cazoleta-bicaz', 'Codo 0', '[\"chica\",\"grande\"]', 'unidad', '250 unidades', 'BICAZ.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(8, 2, 'BIDA', 'Bisagra doble acción', 'bisagra-doble-accion-bida', NULL, '[\"75 mm\",\"102 mm\"]', 'par', '25 pares', 'BIDA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(9, 2, 'BILIB', 'Bisagra libro - 5005', 'bisagra-libro-5005-bilib', 'Hierro bronceado', '[\"1´- 25 mm\",\"1 1/2´- 38 mm\",\"2´- 51 mm\",\"2 1/2´- 63 mm\",\"3´- 76 mm\",\"3 1/2´- 89 mm\",\"4´- 101 mm\"]', 'docena de par', '10 docenas de par', 'BILIB.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(10, 2, 'BILIP', 'Bisagra libro - 5005', 'bisagra-libro-5005-bilip', 'Hierro pulido', '[\"1´- 25 mm\",\"1 1/2´- 38 mm\",\"2´- 51 mm\",\"2 1/2´- 63 mm\",\"3´- 76 mm\",\"3 1/2´- 89 mm\",\"4´- 101 mm\"]', 'docena de par', '10 docenas de par', 'BILIP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(11, 2, 'BILIBR', 'Bisagra libro reforzada - 5005', 'bisagra-libro-reforzada-5005-bilibr', 'Hierro bronceado reforzado', '[\"1 1/2´- 38 mm\",\"2´- 51 mm\",\"2 1/2´- 63 mm\",\"3´- 76 mm\",\"3 1/2´- 89 mm\",\"4´- 101 mm\"]', 'docena de par', '10 docenas de par', 'BILIB.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(12, 2, 'BILIPR', 'Bisagra libro reforzada - 5005', 'bisagra-libro-reforzada-5005-bilipr', 'Hierro pulido reforzado', '[\"1 1/2´- 38 mm\",\"2´- 51 mm\",\"2 1/2´- 63 mm\",\"3´- 76 mm\",\"3 1/2´- 89 mm\",\"4´- 101 mm\"]', 'docena de par', '10 docenas de par', 'BILIP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(13, 2, 'BISM', 'Bisagra mosquera', 'bisagra-mosquera-bism', 'charol; zincada', NULL, 'par', '120 pares', 'BISM.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(14, 2, 'BITP', 'Bisagra T', 'bisagra-t-bitp', 'Hierro pulido', '[\"3´- 76 mm\",\"4´- 101 mm\",\"5´- 127 mm\",\"6´- 152\",\"4 mm\",\"8´- 203\"]', 'docena de par', '8 docenas de par', 'BITP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(15, 1, 'CADBA', 'Cadena banderola', 'cadena-banderola-cadba', 'simple; doble', '[\"Madeja por 25 m\"]', 'metro', 'Caja por 15 madejas', 'CADBA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(16, 1, 'CADB', 'Cadena bolita; terminal y unión', 'cadena-bolita-terminal-y-union-cadb', 'La cadena se vende por 15 m y las terminales y uniones por 100 unidades.', '[\"Bronce\",\"niquel\"]', 'unit', NULL, 'CADB.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(17, 1, 'CADN', 'Cadena nudo', 'cadena-nudo-cadn', 'galvanizada', '[\"13\",\"15\",\"17\",\"18\"]', '25 m', NULL, 'CADN.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(18, 1, 'CADPL', 'Cadena eslabón plástica 8 mm', 'cadena-eslabon-plastica-8-mm-cadpl', 'Carretel rojo y blanco; Eslabón de 8 mm;', NULL, 'carretel', '8 carreteles', 'CADPL.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(19, 1, 'CADPP', 'Cadena paseo perro', 'cadena-paseo-perro-cadpp', '1;25 m de largo', '[\"2 mm\",\"2.5 mm\",\"3 mm\",\"3.5 mm\",\"4 mm\"]', 'unit', NULL, 'CADPP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(20, 1, 'CADZ', 'Cadena patente zincada', 'cadena-patente-zincada-cadz', 'Balde por 12;5 kg', '[\"Nº 30 (3 mm)\",\"Nº 35 (3.5 mm)\",\"Nº 40 (4 mm)\",\"Nº 45 (4.5 mm)\",\"Nº 50 (5 mm)\",\"Nº 55 (5.5 mm)\",\"Nº 60 (6 mm)\",\"Nº 70 (7 mm)\",\"Nº 80 (8 mm)\",\"Nº 90 (9 mm)\",\"Nº 100 (10 mm)\",\"Nº 110 (11 mm)\"]', 'balde', NULL, 'CADZ.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(21, 1, 'COLPP', 'Collar paseo perro', 'collar-paseo-perro-colpp', NULL, '[\"2 mm x 36 cm\",\"3 mm x 62 cm\",\"4 mm x 72 cm\",\"2;5 mm x 47 cm\",\"3;5 mm x 72 cm\"]', 'docena', '10 docenas', 'COLPP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(22, 3, 'CALI', 'Calibre metálico', 'calibre-metalico-cali', 'con freno', '[\"150 mm\"]', 'unidad', '50 unidades', 'CALI.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(23, 2, 'CANDB', 'Candado de hierro bronceado', 'candado-de-hierro-bronceado-candb', 'Con cilindro de bronce y 3 llaves', '[\"25\",\"32\",\"38\",\"50\",\"63\"]', 'unidad', '120 unidades', 'CANDB.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(24, 4, 'CANIEM', 'Canilla esférica metálica', 'canilla-esferica-metalica-caniem', NULL, '[\"1/2´\",\"3/4´\"]', 'unidad', '100 unidades', 'CANIEM.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(25, 3, 'CEPAPC', 'Cepillo acero', 'cepillo-acero-cepapc', 'de punta curva', NULL, 'unidad', '24 unidades', 'CEPAPC.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(26, 2, 'CERRMUE', 'Cerradura para mueble', 'cerradura-para-mueble-cerrmue', 'con 2 llaves', NULL, 'unidad', '12 unidades', 'CERRMUE.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(27, 2, 'CIERRAP', 'Cierrapuertas a resorte', 'cierrapuertas-a-resorte-cierrap', NULL, NULL, 'unidad', '100 unidades', 'CIERRAP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(28, 1, 'CINAN', 'Cinta antideslizante', 'cinta-antideslizante-cinan', 'Transparente o negra', '[\"5 m\",\"18 m\"]', 'unidad', '60 unidades', 'CINAN.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(29, 4, 'CINTEF', 'Cinta de teflón', 'cinta-de-teflon-cintef', NULL, '[\"1/2\",\"3/4\"]', 'unidad', '1000 unidades', 'CINTEF.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(30, 2, 'CORM', 'Corredera metálica', 'corredera-metalica-corm', 'Blanca o negra', '[\"300 mm\",\"350 mm\",\"400 mm\",\"450 mm\",\"500 mm\",\"550 mm\",\"600 mm\"]', 'par', '25 pares', 'CORM.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(31, 1, 'CUTMET', 'Cuchilla cutter metálico común', 'cuchilla-cutter-metalico-comun-cutmet', NULL, NULL, 'unidad', '240 unidades', 'CUTMET.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(32, 1, 'CUTMETP', 'Cuchilla cutter metálico profesional', 'cuchilla-cutter-metalico-profesional-cutmetp', NULL, NULL, 'unidad', '240 unidades', 'CUTMETP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(33, 1, 'CUTPLAS', 'Cuchilla cutter PVC', 'cuchilla-cutter-pvc-cutplas', NULL, NULL, 'unidad', '720 unidades', 'CUTPLAS.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(34, 3, 'DISFLA', 'Disco flap', 'disco-flap-disfla', NULL, '[\"4 1/2 x 40\",\"4 1/2 x 60\",\"4 1/2 x 80\",\"4 1/2 x 120\"]', 'unidad', '100 unidades', 'DISFLA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(35, 3, 'ESCMET', 'Escuadra metálica milimétrica', 'escuadra-metalica-milimetrica-escmet', NULL, '[\"30 cm\"]', 'unidad', '120 unidades', 'ESCMET.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(36, 3, 'ESP', 'Espátula', 'espatula-esp', 'Mando de madera', '[\"1´\",\"1 1/2´\",\"2´\",\"2 1/2´\",\"3´\",\"3 1/2´\",\"4´\"]', 'unidad', '1000 unidades', 'ESP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(37, 5, 'FAR', 'Farfalla', 'farfalla-far', 'Tornillo volcable', '[\"1/8 x 76 mm\",\"3/16 x 76 mm\"]', 'unidad', '1000 unidades', 'FAR.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(38, 2, 'GANARO', 'Gancho aro', 'gancho-aro-ganaro', 'reforzado', NULL, 'unidad', '200 unidades', 'GANARO.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(39, 2, 'GANHAM', 'Gancho hamaca', 'gancho-hamaca-ganham', 'reforzado', NULL, 'unidad', '200 unidades', 'GANHAM.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(40, 5, 'PITRGA', 'Pitón reforzado con gancho', 'piton-reforzado-con-gancho-pitrga', NULL, NULL, 'unidad', '200 unidades', 'PITRGA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(41, 5, 'PITRAR', 'Piton reforzado con argolla', 'piton-reforzado-con-argolla-pitrar', NULL, NULL, 'unidad', '200 unidades', 'PITRAR.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(42, 1, 'GUACUE', 'Guantes cuero y lona', 'guantes-cuero-y-lona-guacue', 'Reforzados', NULL, 'par', '120 pares', 'GUACUE.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(43, 1, 'GUALAT', 'Guantes de latex', 'guantes-de-latex-gualat', NULL, '[\"10 - XL\",\"9 1/2 - L\"]', 'par', '120 pares', 'GUALAT.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(44, 6, 'JARADM', 'Adaptador macho', 'adaptador-macho-jaradm', 'Importado de Taiwan. Excelente calidad.', '[\"1/2\"]', 'unidad', '300 unidades', 'JARADM.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(45, 6, 'JARC', 'Conector reforzado', 'conector-reforzado-jarc', 'Importado de Taiwan. Excelente calidad.', '[\"1/2\",\"3/4\"]', 'unidad', '500 unidades', 'JARC.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(46, 6, 'JARJM', 'Rociador jabalina metálica', 'rociador-jabalina-metalica-jarjm', '2 direcciones. Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '500 unidades', 'JARJM.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(47, 6, 'JARJMC', 'Cabezal jabalina metálica', 'cabezal-jabalina-metalica-jarjmc', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '500 unidades', 'JARJMC.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(48, 6, 'JARJMP', 'Pinche jabalina metálica', 'pinche-jabalina-metalica-jarjmp', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '500 unidades', 'JARJMP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(49, 6, 'JARJP', 'Rociador jabalina plástica', 'rociador-jabalina-plastica-jarjp', '2 direcciones. Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '500 unidades', 'JARJP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(50, 6, 'JARJPC', 'Cabezal jabalina plástica', 'cabezal-jabalina-plastica-jarjpc', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '500 unidades', 'JARJPC.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(51, 6, 'JARJPP', 'Pinche jabalina plástica', 'pinche-jabalina-plastica-jarjpp', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '500 unidades', 'JARJPP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(52, 6, 'JARLR', 'Lanza reforzada', 'lanza-reforzada-jarlr', 'Alto impacto. Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '500 unidades', 'JARLR.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(53, 6, 'JARMA', 'Manga canilla rosca', 'manga-canilla-rosca-jarma', 'Importado de Taiwan. Excelente calidad.', '[\"1\",\"3/4\"]', 'unidad', '300 unidades', 'JARMA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(54, 6, 'JARPI6', 'Pistola regulador 6 posiciones', 'pistola-regulador-6-posiciones-jarpi6', 'Super reforzada. Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '50 uniades', 'JARPI6.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(55, 6, 'JARPI6E', 'Pistola regulador 6 posiciones económica', 'pistola-regulador-6-posiciones-economica-jarpi6e', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARPI6E.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(56, 6, 'JARPIER', 'Pistola reforzada ergonométrica', 'pistola-reforzada-ergonometrica-jarpier', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARPIER.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(57, 6, 'JARPIME', 'Pistola metálica alto impacto', 'pistola-metalica-alto-impacto-jarpime', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARPIME.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(58, 6, 'JARPIPL', 'Pistola plástica alto impacto', 'pistola-plastica-alto-impacto-jarpipl', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARPIPL.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(59, 6, 'JARPIPLR', 'Pistola plástica reforzada', 'pistola-plastica-reforzada-jarpiplr', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARPIPLR.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(60, 6, 'JARROB2', 'Rociador base redonda', 'rociador-base-redonda-jarrob2', '2 direcciones. Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARROB2.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(61, 6, 'JARROB3', 'Rociador base redonda', 'rociador-base-redonda-jarrob3', '3 direcciones. Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARROB3.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(62, 6, 'JARROB8', 'Rociador base redonda', 'rociador-base-redonda-jarrob8', '8 posiciones. Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARROB8.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(63, 6, 'JARROLL', 'Rociador lluvia con válvula', 'rociador-lluvia-con-valvula-jarroll', 'Importado de Taiwan. Excelente calidad.', NULL, 'unidad', '100 unidades', 'JARROLL.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(64, 6, 'JARUM', 'Unión manguera reforzada', 'union-manguera-reforzada-jarum', 'Importado de Taiwan. Excelente calidad.', '[\"1/2\",\"3/4\"]', 'unidad', '500 unidades', 'JARUM.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(65, 6, 'JAREMA', 'Enrollador manguera', 'enrollador-manguera-jarema', NULL, NULL, 'unidad', '6 unidades', 'JAREMA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(66, 6, 'JAREMAR', 'Enrollador manguera con ruedas', 'enrollador-manguera-con-ruedas-jaremar', NULL, NULL, 'unidad', '6 unidades', 'JAREMAR.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(67, 3, 'LIMA', 'Lima para motosierra', 'lima-para-motosierra-lima', NULL, '[\"3/16\",\"5/32\",\"7/32\"]', 'unidad', '720 unidades', 'LIMA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(68, 6, 'LONCO', 'Lona para cerco plastificada', 'lona-para-cerco-plastificada-lonco', 'Con ojal', '[\"1 m\",\"1.5 m\",\"2 m\"]', 'metro', NULL, 'LONCO.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(69, 6, 'LONSO', 'Lona para cerco plastificada', 'lona-para-cerco-plastificada-lonso', 'Sin ojal', '[\"1 m\",\"1,5 m\",\"2 m\"]', 'metro', NULL, 'LONSO.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(70, 2, 'MANBA', 'Manija baúl', 'manija-baul-manba', 'zincada', '[\"75 mm\",\"88 mm\",\"100 mm\"]', 'unidad', '120 unidades', 'MANBA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(71, 2, 'MANPUE', 'Manija puente', 'manija-puente-manpue', 'de chapa', '[\"3´\",\"4´\",\"5´\",\"6´\"]', 'unidad', '100 docenas', 'MANPUE.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(72, 1, 'MECFAR', 'Mechas para farol', 'mechas-para-farol-mecfar', 'Reforzada', NULL, 'docena', '50 docenas', 'MECFAR.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(73, 2, 'MOSCRD', 'Mosquetón a botón cromado', 'mosqueton-a-boton-cromado-moscrd', 'Doble', '[\"Nº 6 (9 cm)\",\"Nº 7 (10 cm)\",\"Nº 8 (11 cm)\"]', 'unidad', '500 unidades', 'MOSCRD.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(74, 2, 'MOSCRS', 'Mosquetón a botón cromado', 'mosqueton-a-boton-cromado-moscrs', 'Simple', '[\"Nº 1\",\"Nº 2\",\"Nº 3\",\"Nº 4\",\"Nº 5\"]', 'unidad', '500 unidades', 'MOSCRS.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(75, 2, 'MOSORO', 'Mosquetón a botón oro', 'mosqueton-a-boton-oro-mosoro', 'Simple', '[\"Nº 1\",\"Nº 2\",\"Nº 3\",\"Nº 4\",\"Nº 5\"]', 'unidad', '500 unidades', 'MOSORO.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(76, 2, 'MOSLEN', 'Mosquetón lengüeta', 'mosqueton-lengueta-moslen', NULL, '[\"70 mm\",\"80 mm\",\"90 mm\",\"100 mm\",\"120 mm\"]', 'unidad', '250 unidades', 'MOSLEN.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(77, 3, 'NIVH', 'Nivel de aluminio', 'nivel-de-aluminio-nivh', 'Tipo H', '[\"30 cm\",\"40 cm\",\"50 cm\"]', 'unidad', '60 unidades', 'NIVH.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(78, 3, 'NIVP', 'Nivel de aluminio', 'nivel-de-aluminio-nivp', 'Profesional', '[\"30 cm\",\"40 cm\",\"50 cm\"]', 'unidad', '60 unidades', 'NIVP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(79, 3, 'NIVTG', 'Nivel de aluminio', 'nivel-de-aluminio-nivtg', 'Triple gota', '[\"30 cm\",\"40 cm\",\"50 cm\"]', 'unidad', '60 unidades', 'NIVTG.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(80, 3, 'NIVCER', 'Arco nivelador', 'arco-nivelador-nivcer', NULL, '[\"1 mm\",\"1.5 mm\",\"2 mm\"]', 'bolsa', '60 bolsas', 'NIVCER.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(81, 3, 'NIVPOR', 'Cuña nivelador', 'cuna-nivelador-nivpor', NULL, NULL, 'bolsa', '40 bolsas', 'NIVPOR.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(82, 3, 'SEPCER', 'Separador de cerámicos', 'separador-de-ceramicos-sepcer', NULL, '[\"1.5 mm\",\"2 mm\",\"3 mm\",\"4 mm\"]', 'bolsa', '500 bolsas', 'SEPCER.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(83, 2, 'PASC', 'Pasador con portacandados', 'pasador-con-portacandados-pasc', 'Hierro zincado', '[\"100 mm\",\"150 mm\",\"200 mm\"]', 'unidad', '120 unidades', 'PASC.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(84, 2, 'PASG', 'Pasador galvanizado', 'pasador-galvanizado-pasg', NULL, '[\"2´\",\"2 1/2´\",\"3´\",\"4´\"]', 'unidad', '240 unidades', 'PASG.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(85, 1, 'PERAC', 'Percha autoadhesiva cuadrada', 'percha-autoadhesiva-cuadrada-perac', NULL, '[\"33 x 33\"]', 'blister', '240 blister', 'PERAC.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(86, 1, 'PERAO', 'Percha autoadhesiva oval', 'percha-autoadhesiva-oval-perao', NULL, '[\"25 x 38\",\"42 x 57\"]', 'blister', '240 blister', 'PERAO.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(87, 1, 'PERAR', 'Percha autoadhesiva rectanguar', 'percha-autoadhesiva-rectanguar-perar', NULL, '[\"50 x 34\"]', 'blister', '240 blister', 'PERAR.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(88, 1, 'PEROD', 'Percha doble', 'percha-doble-perod', 'Dos ganchos', '[\"oro\",\"plata\"]', 'caja', '250 unidades', 'PEROD.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(89, 1, 'PEROG', 'Percha grande', 'percha-grande-perog', NULL, '[\"oro\",\"plata\"]', 'caja', '500 unidades', 'PEROG.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(90, 1, 'PEROS', 'Percha simple', 'percha-simple-peros', 'Un gancho', '[\"oro\",\"plata\"]', 'caja', '1000 unidades', 'PEROS.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(91, 1, 'PERVEN', 'Percha ventosa', 'percha-ventosa-perven', NULL, NULL, 'bolsa', '25 bolsas', 'PERVEN.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(92, 1, 'PIEDAF', 'Piedra de afilar', 'piedra-de-afilar-piedaf', 'Doble faz', NULL, 'unidad', '50 unidades', 'PIEDAF.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(93, 3, 'PINB', 'Pincel cerda blanca', 'pincel-cerda-blanca-pinb', NULL, '[\"1/2´\",\"3/4´\",\"1´\",\"1 1/2\",\"2´\",\"2 1/2´\",\"3´\"]', 'unidad', '20 docenas', 'PINB.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(94, 1, 'PINZPE', 'Pinza portaelectrodos', 'pinza-portaelectrodos-pinzpe', NULL, '[\"300 amp\",\"500 amp\"]', 'unidad', '50 unidades', 'PINZPE.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(95, 3, 'PINZNIV', 'Pinza niveladora', 'pinza-niveladora-pinzniv', 'Para porcelanato', NULL, 'unidad', '20 unidades', 'PINZNIV.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(96, 2, 'PISGA', 'Pistón a gas', 'piston-a-gas-pisga', NULL, '[\"80 Nwt\",\"100 Nwt\"]', 'unidad', NULL, 'PISGA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(97, 5, 'PITB', 'Pitón con tope pvc', 'piton-con-tope-pvc-pitb', 'Blanco', '[\"5/8´\",\"3/4´\",\"1´\",\"1 1/4´\",\"1 1/2´\"]', 'bolsa', NULL, 'PITB.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(98, 5, 'PITEB', 'Clavo escuadra con tope pvc', 'clavo-escuadra-con-tope-pvc-piteb', 'Blanco', '[\"5/8´\",\"3/4´\",\"1´\",\"1 1/4´\",\"1 1/2´\"]', 'bolsa', NULL, 'PITEB.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(99, 5, 'PITEO', 'Clavo escuadra con tope oro', 'clavo-escuadra-con-tope-oro-piteo', NULL, '[\"5/8´\",\"3/4´\",\"1/2´\",\"1´\",\"1 1/4´\"]', 'bolsa', NULL, 'PITEO.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(100, 5, 'PITO', 'Pitón con tope oro', 'piton-con-tope-oro-pito', NULL, '[\"5/8´\",\"3/4´\",\"1/2´\",\"1´\",\"1 1/4´\"]', 'bolsa', NULL, 'PITO.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(101, 5, 'PITAAT', 'Pitón anclaje abierto con tope', 'piton-anclaje-abierto-con-tope-pitaat', NULL, '[\"5\",\"6\",\"8\",\"10\"]', 'caja', NULL, 'PITAAT.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(102, 5, 'PITACT', 'Pitón anclaje cerrado con tope', 'piton-anclaje-cerrado-con-tope-pitact', NULL, '[\"5\",\"6\",\"8\",\"10\"]', 'caja', NULL, 'PITACT.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(103, 5, 'PITAET', 'Pitón anclaje escuadra con tope', 'piton-anclaje-escuadra-con-tope-pitaet', NULL, '[\"5\",\"6\",\"8\",\"10\"]', 'caja', NULL, 'PITAET.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(104, 5, 'PITAA', 'Pitón anclaje abierto sin tope', 'piton-anclaje-abierto-sin-tope-pitaa', NULL, '[\"5\",\"6\",\"8\",\"10\"]', 'caja', NULL, 'PITAA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(105, 5, 'PITAC', 'Pitón anclaje cerrado sin tope', 'piton-anclaje-cerrado-sin-tope-pitac', NULL, '[\"5\",\"6\",\"8\",\"10\"]', 'caja', NULL, 'PITAC.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(106, 5, 'PITAE', 'Pitón anclaje escuadra sin tope', 'piton-anclaje-escuadra-sin-tope-pitae', NULL, '[\"5\",\"6\",\"8\",\"10\"]', 'caja', NULL, 'PITAE.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(107, 3, 'PLOCH', 'Plomada trazadra chocla', 'plomada-trazadra-chocla-ploch', 'Con polvo', NULL, 'unidad', '48 unidades', 'PLOCH.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(108, 3, 'PLOP', 'Plomada pintada', 'plomada-pintada-plop', NULL, '[\"200 g\",\"300 g\",\"400 g\",\"500 g\"]', 'unidad', '60 unidades', 'PLOP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(109, 2, 'PORCA', 'Portacandados', 'portacandados-porca', NULL, '[\"2´- 51 mm\",\"2 1/2´- 63 mm\",\"3´- 76 mm\",\"4´- 102 mm\",\"5´- 125 mm\"]', 'docena', '50 docenas', 'PORCA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(110, 1, 'PREG', 'Prensa G', 'prensa-g-preg', NULL, '[\"3´\",\"4´\",\"5´\",\"6´\"]', 'unidad', '120 unidades', 'PREG.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(111, 6, 'PULV', 'Pulverizador', 'pulverizador-pulv', 'Primera calidad', '[\"1 litro\",\"1.5 litros\",\"2 litros\"]', 'unidad', '20 unidades', 'PULV.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(112, 2, 'RETPA', 'Retén puerta pared', 'reten-puerta-pared-retpa', 'de PVC blanco con tornillos y tarugos', NULL, 'unidad', '400 unidades', 'RETPA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(113, 2, 'RETPI', 'Retén puerta piso', 'reten-puerta-piso-retpi', 'de PVC blanco con tornillos y tarugos', NULL, 'unidad', '400 unidades', 'RETPI.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(114, 2, 'RUEGG', 'Rueda  de goma', 'rueda-de-goma-ruegg', 'base giratoria', '[\"25 mm\",\"32 mm\",\"40 mm\",\"50 mm\",\"65 mm\",\"76 mm\"]', 'unidad', '100 unidades', 'RUEGG.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(115, 2, 'RUEPG', 'Rueda plástica', 'rueda-plastica-ruepg', 'base giratoria', '[\"32 mm\",\"40 mm\",\"50 mm\"]', 'unidad', '100 unidades', 'RUEPG.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(116, 2, 'RUEMB', 'Rueda mueble', 'rueda-mueble-ruemb', 'con base', '[\"40 mm\",\"50 mm\"]', 'unidad', '400 unidades', 'RUEMB.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(117, 2, 'RUEME', 'Rueda mueble', 'rueda-mueble-rueme', 'con espiga', '[\"40 mm\",\"50 mm\"]', 'unidad', '400 unidades', 'RUEME.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(118, 2, 'RUEMP', 'Rueda mueble', 'rueda-mueble-ruemp', 'con perno', '[\"40 mm x 1/4\",\"40 mm x 5/16\",\"40 mm x 3/8\",\"50 mm x 5/16\",\"50 mm x 3/8\"]', 'unidad', '400 unidades', 'RUEMP.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(119, 2, 'RUECA', 'Rueda Camera', 'rueda-camera-rueca', NULL, '[\"25 mm\",\"32 mm\"]', 'unidad', '1000 unidades', 'RUECA.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(120, 3, 'RUEN', 'Rueda neumática', 'rueda-neumatica-ruen', 'inflable', '[\"250 x 80 x 16\",\"360 x 90 x 16\",\"390 x 90 x 16\"]', 'unidad', '10 unidades', 'RUEN.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(121, 2, 'SOPE', 'Soprte para estantes', 'soprte-para-estantes-sope', 'Blanco o charol', '[\"75 mm x 100 mm\",\"100 mm x 125 mm\",\"125 mm x 150 mm\",\"150 mm x 200 mm\",\"200 mm x 250 mm\",\"250 mm x 300 mm\",\"300 mm x 350 mm\"]', 'unidad', '144 unidades', 'SOPE.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(122, 1, 'TAC', 'Tachas', 'tachas-tac', 'Bronceadas o niqueladas', NULL, 'caja', '50 cajas', 'TAC.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(123, 7, 'TEJAL', 'Tejido mosquitero', 'tejido-mosquitero-tejal', 'Aluminio. Rollo por 30 m', '[\"0.80 m\",\"1.00 m\",\"1.20 m\"]', 'rollo', NULL, 'TEJAL.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(124, 7, 'TEJCPAN', 'Tejido cerramiento de alambre plastificado', 'tejido-cerramiento-de-alambre-plastificado-tejcpan', 'Nogro o verde; Rollo por 25 m', '[\"1/2´x 1/2´x 0.915 m\",\"1/2´x 1/2´x 1.291 m\",\"1´x 1´x 0.915 m\",\"1´x 1´x 1.291 m\"]', 'rollo', NULL, 'TEJCPAN.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(125, 7, 'TEJHEX', 'Tejido hexagonal', 'tejido-hexagonal-tejhex', 'Galvanizado. Rollo por 25 m', '[\"13 x 23 x 0.80 m\",\"13 x 23 x 1.00 m\",\"13 x 23 x 1.20 m\",\"19 x 22 x 0.80 m\",\"19 x 22 x 1.00 m\",\"19 x 22 x 1.20 m\",\"25 x 21 x 1.00 m\",\"25 x 21 x 1.20 m\"]', 'rollo', NULL, 'TEJHEX.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(126, 7, 'TEJFI', 'Tejido mosquitero', 'tejido-mosquitero-tejfi', 'Fibra de vidrio \"Fiberglass\"; Rollo por 30 m', '[\"0.80 m\",\"1.00 m\",\"1.20 m\",\"1.50 m\"]', 'rollo', NULL, 'TEJFI.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(127, 7, 'TEJMN', 'Tejido malla plástica de señalización', 'tejido-malla-plastica-de-senalizacion-tejmn', 'Color naranja. Rollo por 45 m', '[\"1 m\"]', 'rollo', NULL, 'TEJMN.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(128, 7, 'TEJMS', 'Tejido malla soldada', 'tejido-malla-soldada-tejms', 'Rollo 25 m', '[\"15 x 15 x 1.00 m (1.25 mm)\",\"25 x 25 x 1.00 m (2.10 mm)\"]', 'rollo', NULL, 'TEJMS.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(129, 7, 'TEJPLG', 'Tejido mosquitero', 'tejido-mosquitero-tejplg', 'Plástico gris o verde. Rollo por 30 m', '[\"0.80 m\",\"1.00 m\",\"1.20 m\"]', 'rollo', NULL, 'TEJPLG.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(130, 2, 'VAIMAG', 'Vaivén magnético', 'vaiven-magnetico-vaimag', 'Blanco o marrón', NULL, 'caja', '1800 unidades', 'VAIMAG.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(131, 2, 'VAIROD', 'Vaivén a rodillo', 'vaiven-a-rodillo-vairod', 'Hierro bgalvanizado o bronceado', NULL, 'caja', '1000 unidades', 'VAIROD.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57'),
(132, 4, 'VALEM', 'Válvula esférica de metal', 'valvula-esferica-de-metal-valem', NULL, '[\"1´\",\"1/2´\",\"3/4´\"]', 'unidad', '60 unidades', 'VALEM.PNG', 1, '2026-05-28 19:46:57', '2026-05-28 19:46:57');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admins_email` (`email`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_name` (`name`),
  ADD UNIQUE KEY `uq_categories_slug` (`slug`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_product_code` (`product_code`),
  ADD UNIQUE KEY `uq_products_slug` (`slug`),
  ADD KEY `idx_products_category_id` (`category_id`),
  ADD KEY `idx_products_name` (`name`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
