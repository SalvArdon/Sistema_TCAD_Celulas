-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-03-2026 a las 22:50:17
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tcad_celulas`
--
CREATE DATABASE IF NOT EXISTS `tcad_celulas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tcad_celulas`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas_servicio`
--

DROP TABLE IF EXISTS `areas_servicio`;
CREATE TABLE `areas_servicio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `lider_id` int(11) DEFAULT NULL,
  `cantidad_servidores` int(11) DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `areas_servicio`
--

INSERT INTO `areas_servicio` (`id`, `nombre`, `descripcion`, `lider_id`, `cantidad_servidores`, `activa`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 'Jovenes', 'Ministerio de jovenes', 2, 0, 1, '2026-03-09 23:14:35', '2026-03-09 23:16:09'),
(2, 'Multimedia', 'Produccion audiovisual y transmision', NULL, 0, 1, '2026-03-09 23:14:35', '2026-03-09 23:14:35'),
(3, 'Matrimonios', 'Ministerio para parejas', 3, 0, 1, '2026-03-09 23:14:35', '2026-03-09 23:16:09'),
(4, 'Mujeres', 'Ministerio femenino', NULL, 0, 1, '2026-03-09 23:14:35', '2026-03-09 23:14:35'),
(5, 'Trafico', 'Organizacion de eventos', NULL, 0, 1, '2026-03-09 23:14:35', '2026-03-09 23:14:35'),
(6, 'Protocolo', 'Celebraciones y ceremonias', NULL, 0, 1, '2026-03-09 23:14:35', '2026-03-09 23:14:35'),
(7, 'Hombres', 'Ministerio masculino', NULL, 0, 1, '2026-03-09 23:14:35', '2026-03-09 23:14:35'),
(8, 'Celulas Familiares', 'Red de celulas en los hogares', NULL, 0, 1, '2026-03-09 23:14:35', '2026-03-09 23:14:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
  `reunion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `asistio` tinyint(1) DEFAULT 1,
  `nombre_visitante` varchar(100) DEFAULT NULL,
  `telefono_visitante` varchar(20) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` enum('insertar','actualizar','eliminar','login','logout') NOT NULL,
  `tabla_afectada` varchar(100) NOT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `valor_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valor_anterior`)),
  `valor_nuevo` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valor_nuevo`)),
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `dispositivo` varchar(100) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `tabla_afectada`, `registro_id`, `valor_anterior`, `valor_nuevo`, `ip_usuario`, `user_agent`, `dispositivo`, `fecha_hora`) VALUES
(1, 1, 'insertar', 'usuarios', 2, NULL, NULL, '127.0.0.1', NULL, NULL, '2026-03-09 23:16:10'),
(2, 1, 'insertar', 'celulas', 1, NULL, NULL, '127.0.0.1', NULL, NULL, '2026-03-09 23:16:10'),
(3, 4, 'insertar', 'reuniones', 1, NULL, NULL, '192.168.1.100', NULL, NULL, '2026-03-09 23:16:10'),
(4, 6, 'actualizar', 'ofrendas', 1, NULL, NULL, '192.168.1.105', NULL, NULL, '2026-03-09 23:16:10'),
(5, 1, 'actualizar', 'ofrendas', 1, '{\"id\":1,\"reunion_id\":1,\"monto\":\"125.50\",\"moneda\":\"USD\",\"estado\":\"reportada\",\"lider_reporta_id\":4,\"usuario_recibe_id\":null,\"usuario_concilia_id\":null,\"fecha_reporte\":\"2026-03-08 23:16:09\",\"fecha_recepcion\":null,\"fecha_conciliacion\":null,\"notas\":null,\"descrepancia\":null,\"fecha_creacion\":\"2026-03-09 23:16:09\",\"fecha_modificacion\":\"2026-03-09 23:16:09\"}', '{\"estado\":\"recibida\",\"usuario_recibe_id\":1,\"fecha_recepcion\":\"2026-03-12 23:15:12\"}', '::1', NULL, NULL, '2026-03-12 23:15:12'),
(6, 1, 'actualizar', 'ofrendas', 1, '{\"id\":1,\"reunion_id\":1,\"monto\":\"125.50\",\"moneda\":\"USD\",\"estado\":\"recibida\",\"lider_reporta_id\":4,\"usuario_recibe_id\":1,\"usuario_concilia_id\":null,\"fecha_reporte\":\"2026-03-08 23:16:09\",\"fecha_recepcion\":\"2026-03-12 23:15:12\",\"fecha_conciliacion\":null,\"notas\":null,\"descrepancia\":null,\"fecha_creacion\":\"2026-03-09 23:16:09\",\"fecha_modificacion\":\"2026-03-12 23:15:12\"}', '{\"estado\":\"conciliada\",\"usuario_concilia_id\":1,\"fecha_conciliacion\":\"2026-03-12 23:15:21\"}', '::1', NULL, NULL, '2026-03-12 23:15:21'),
(7, 1, 'actualizar', 'ofrendas', 1, '{\"id\":1,\"reunion_id\":1,\"monto\":\"125.50\",\"moneda\":\"USD\",\"estado\":\"conciliada\",\"lider_reporta_id\":4,\"usuario_recibe_id\":1,\"usuario_concilia_id\":1,\"fecha_reporte\":\"2026-03-08 23:16:09\",\"fecha_recepcion\":\"2026-03-12 23:15:12\",\"fecha_conciliacion\":\"2026-03-12 23:15:21\",\"notas\":null,\"descrepancia\":null,\"fecha_creacion\":\"2026-03-09 23:16:09\",\"fecha_modificacion\":\"2026-03-12 23:15:21\"}', '{\"estado\":\"recibida\",\"usuario_recibe_id\":1,\"fecha_recepcion\":\"2026-03-12 23:17:00\"}', '::1', NULL, NULL, '2026-03-12 23:17:00'),
(8, 1, 'actualizar', 'ofrendas', 1, '{\"id\":1,\"reunion_id\":1,\"monto\":\"125.50\",\"moneda\":\"USD\",\"estado\":\"recibida\",\"lider_reporta_id\":4,\"usuario_recibe_id\":1,\"usuario_concilia_id\":1,\"fecha_reporte\":\"2026-03-08 23:16:09\",\"fecha_recepcion\":\"2026-03-12 23:17:00\",\"fecha_conciliacion\":\"2026-03-12 23:15:21\",\"notas\":null,\"descrepancia\":null,\"fecha_creacion\":\"2026-03-09 23:16:09\",\"fecha_modificacion\":\"2026-03-12 23:17:00\"}', '{\"estado\":\"reportada\"}', '::1', NULL, NULL, '2026-03-12 23:17:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `celulas`
--

DROP TABLE IF EXISTS `celulas`;
CREATE TABLE `celulas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `lider_id` int(11) NOT NULL,
  `lider_area_id` int(11) DEFAULT NULL,
  `anfitrion_id` int(11) DEFAULT NULL,
  `area_servicio_id` int(11) NOT NULL,
  `direccion` text NOT NULL,
  `zona` varchar(100) DEFAULT NULL,
  `coordenadas` varchar(50) DEFAULT NULL,
  `dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `estado` enum('activa','inactiva','pausada') DEFAULT 'activa',
  `cantidad_promedio_asistentes` int(11) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_cierre` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `celulas`
--

INSERT INTO `celulas` (`id`, `nombre`, `lider_id`, `lider_area_id`, `anfitrion_id`, `area_servicio_id`, `direccion`, `zona`, `coordenadas`, `dia_semana`, `hora_inicio`, `estado`, `cantidad_promedio_asistentes`, `fecha_creacion`, `fecha_modificacion`, `fecha_cierre`) VALUES
(1, 'Célula Centro', 4, 2, NULL, 1, 'Calle Principal 123, Centro', 'Centro', NULL, 'Lunes', '19:00:00', 'activa', 12, '2026-03-09 23:16:09', '2026-03-09 23:16:09', NULL),
(2, 'Célula San Benito', 5, NULL, NULL, 1, 'Avenida Independencia 456, San Benito', 'San Benito', NULL, 'Miercoles', '19:30:00', 'activa', 15, '2026-03-09 23:16:09', '2026-03-11 19:04:09', NULL),
(5, 'Celula Santa Tecla', 2, 9, 9, 1, 'Santa Tecla, Merliot', 'Santa Tecla', NULL, 'Viernes', '07:30:00', 'activa', 15, '2026-03-10 22:15:37', '2026-03-12 23:19:25', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

DROP TABLE IF EXISTS `configuracion_sistema`;
CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('texto','numero','booleano','json') DEFAULT 'texto',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `delegaciones`
--

DROP TABLE IF EXISTS `delegaciones`;
CREATE TABLE `delegaciones` (
  `id` int(11) NOT NULL,
  `usuario_delegador_id` int(11) NOT NULL,
  `usuario_delegado_id` int(11) NOT NULL,
  `area_servicio_id` int(11) DEFAULT NULL,
  `celula_id` int(11) DEFAULT NULL,
  `rol_nuevo` varchar(50) DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_delegacion` date DEFAULT curdate(),
  `fecha_termino` date DEFAULT NULL,
  `razon` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `delegaciones`
--

INSERT INTO `delegaciones` (`id`, `usuario_delegador_id`, `usuario_delegado_id`, `area_servicio_id`, `celula_id`, `rol_nuevo`, `activa`, `fecha_delegacion`, `fecha_termino`, `razon`, `observaciones`, `fecha_creacion`) VALUES
(1, 1, 2, 1, NULL, NULL, 1, '2026-03-09', NULL, 'Delegación de liderazgo del área Jóvenes', NULL, '2026-03-09 23:16:10'),
(2, 1, 3, 3, NULL, NULL, 1, '2026-03-09', NULL, 'Delegación de liderazgo del área Matrimonios', NULL, '2026-03-09 23:16:10'),
(3, 2, 4, 1, NULL, NULL, 1, '2026-03-09', NULL, 'María delega a Juan como líder de célula Centro', NULL, '2026-03-09 23:16:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_acceso`
--

DROP TABLE IF EXISTS `log_acceso`;
CREATE TABLE `log_acceso` (
  `id` int(11) NOT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `ip_direccion` varchar(45) DEFAULT NULL,
  `exitoso` tinyint(1) DEFAULT NULL,
  `razon_fallo` varchar(100) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `dispositivo` varchar(100) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `log_acceso`
--

INSERT INTO `log_acceso` (`id`, `correo`, `ip_direccion`, `exitoso`, `razon_fallo`, `user_agent`, `dispositivo`, `fecha_hora`) VALUES
(1, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT; Windows NT 10.0; es-SV) WindowsPowerShell/5.1.22621.6133', NULL, '2026-03-09 23:41:25'),
(2, 'pastor@iglesia.com', '::1', 0, 'Contraseña incorrecta', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.111.0 Chrome/142.0.7444.265 Electron/39.6.0 Safari/537.36', NULL, '2026-03-09 23:42:44'),
(3, 'pastor@iglesia.com', '::1', 0, 'Contraseña incorrecta', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-09 23:45:51'),
(4, 'pastor@iglesia.com', '::1', 0, 'Contraseña incorrecta', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-09 23:51:15'),
(5, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-09 23:51:32'),
(6, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 07:10:27'),
(7, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 07:10:31'),
(8, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 07:19:56'),
(9, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.111.0 Chrome/142.0.7444.265 Electron/39.6.0 Safari/537.36', NULL, '2026-03-10 08:04:42'),
(10, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 08:05:16'),
(11, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 08:05:20'),
(12, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 08:15:32'),
(13, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 08:15:34'),
(14, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 11:48:47'),
(15, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 11:48:50'),
(16, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 12:33:47'),
(17, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 12:54:01'),
(18, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 13:04:45'),
(19, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-10 21:01:43'),
(20, 'pastor@iglesia.com', '192.168.68.101', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-10 23:40:18'),
(21, 'pastor@iglesia.com', '192.168.68.101', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-10 23:40:23'),
(22, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-11 19:02:39'),
(23, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-11 19:02:44'),
(24, 'pastor@iglesia.com', '192.168.68.101', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-11 19:03:03'),
(25, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-11 21:23:22'),
(26, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-11 21:33:30'),
(27, 'pastor@iglesia.com', '192.168.68.101', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-11 22:30:27'),
(28, 'pastor@iglesia.com', '192.168.68.101', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-11 22:30:29'),
(29, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-12 06:51:15'),
(30, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-12 07:11:56'),
(31, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-12 10:21:28'),
(32, 'pastor@iglesia.com', '192.168.68.101', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-12 22:47:36'),
(33, 'pastor@iglesia.com', '192.168.68.101', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-12 22:47:38'),
(34, 'pastor@iglesia.com', '192.168.68.101', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-12 22:47:43'),
(35, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-12 22:55:20'),
(36, 'pastor@iglesia.com', '::1', 0, 'Contraseña incorrecta', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-12 23:45:56'),
(37, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-12 23:46:50'),
(38, 'pastor@iglesia.com', '192.168.68.103', 1, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-14 02:15:48'),
(39, 'pastor@iglesia.com', '::1', 1, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-17 14:42:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_estudio`
--

DROP TABLE IF EXISTS `materiales_estudio`;
CREATE TABLE `materiales_estudio` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `area_servicio_id` int(11) DEFAULT NULL,
  `celula_id` int(11) DEFAULT NULL,
  `tipo` enum('pdf','video','documento','presentacion','otro') NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `tamaño_bytes` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  `version_anterior_id` int(11) DEFAULT NULL,
  `subido_por_id` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_destino_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('delegacion','material','alerta_reporte','ofrenda_pendiente','otro') NOT NULL,
  `referencia_tabla` varchar(50) DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `enviada_email` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_lectura` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_destino_id`, `titulo`, `mensaje`, `tipo`, `referencia_tabla`, `referencia_id`, `leida`, `enviada_email`, `fecha_creacion`, `fecha_lectura`) VALUES
(1, 2, 'Nueva asignación', 'Has sido designado como Líder de Área Jóvenes', 'delegacion', NULL, NULL, 0, 0, '2026-03-09 23:16:10', NULL),
(2, 4, 'Recordatorio', 'Tu célula tiene reporte pendiente de hace 3 días', 'alerta_reporte', NULL, NULL, 0, 0, '2026-03-09 23:16:10', NULL),
(3, 6, 'Ofrenda pendiente', 'Hay una ofrenda sin confirmar recepción', 'ofrenda_pendiente', NULL, NULL, 0, 0, '2026-03-09 23:16:10', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofrendas`
--

DROP TABLE IF EXISTS `ofrendas`;
CREATE TABLE `ofrendas` (
  `id` int(11) NOT NULL,
  `reunion_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `moneda` varchar(3) DEFAULT 'USD',
  `estado` enum('reportada','recibida','conciliada') DEFAULT 'reportada',
  `lider_reporta_id` int(11) NOT NULL,
  `usuario_recibe_id` int(11) DEFAULT NULL,
  `usuario_concilia_id` int(11) DEFAULT NULL,
  `fecha_reporte` datetime DEFAULT current_timestamp(),
  `fecha_recepcion` datetime DEFAULT NULL,
  `fecha_conciliacion` datetime DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `descrepancia` decimal(10,2) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ofrendas`
--

INSERT INTO `ofrendas` (`id`, `reunion_id`, `monto`, `moneda`, `estado`, `lider_reporta_id`, `usuario_recibe_id`, `usuario_concilia_id`, `fecha_reporte`, `fecha_recepcion`, `fecha_conciliacion`, `notas`, `descrepancia`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 1, 125.50, 'USD', 'reportada', 4, 1, 1, '2026-03-08 23:16:09', '2026-03-12 23:17:00', '2026-03-12 23:15:21', NULL, NULL, '2026-03-09 23:16:09', '2026-03-12 23:17:06'),
(2, 2, 150.00, 'USD', 'conciliada', 5, 6, 6, '2026-03-06 23:16:09', '2026-03-06 23:16:09', '2026-03-07 23:16:09', NULL, NULL, '2026-03-09 23:16:09', '2026-03-09 23:16:09'),
(4, 4, 5.00, 'USD', 'reportada', 1, NULL, NULL, '2026-03-09 00:00:00', NULL, NULL, '', NULL, '2026-03-12 23:49:25', '2026-03-12 23:49:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reuniones`
--

DROP TABLE IF EXISTS `reuniones`;
CREATE TABLE `reuniones` (
  `id` int(11) NOT NULL,
  `celula_id` int(11) NOT NULL,
  `fecha_reunion` date NOT NULL,
  `realizada` tinyint(1) DEFAULT 1,
  `motivo_cancelacion` text DEFAULT NULL,
  `cantidad_asistentes` int(11) NOT NULL DEFAULT 0,
  `cantidad_nuevos` int(11) DEFAULT 0,
  `lider_reporta_id` int(11) NOT NULL,
  `fecha_reporte` datetime DEFAULT current_timestamp(),
  `ip_reporte` varchar(45) DEFAULT NULL,
  `dispositivo` varchar(100) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `temas_tratados` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reuniones`
--

INSERT INTO `reuniones` (`id`, `celula_id`, `fecha_reunion`, `realizada`, `motivo_cancelacion`, `cantidad_asistentes`, `cantidad_nuevos`, `lider_reporta_id`, `fecha_reporte`, `ip_reporte`, `dispositivo`, `comentarios`, `temas_tratados`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 1, '2026-03-08', 1, NULL, 12, 1, 4, '2026-03-11 19:35:35', NULL, NULL, 'Buena asistencia. Grupo muy participativo.', 'La Gracia de Dios - Efesios 2:8-9', '2026-03-09 23:16:09', '2026-03-11 19:35:35'),
(2, 2, '2026-03-06', 1, NULL, 14, 2, 5, '2026-03-09 23:16:09', NULL, NULL, 'Excelente célula. Muchas oraciones contestadas.', 'Fe y Obras - Santiago 2:26', '2026-03-09 23:16:09', '2026-03-09 23:16:09'),
(4, 5, '2026-03-06', 1, '', 15, 2, 1, '2026-03-12 10:36:40', '::1', NULL, 'ninguno', NULL, '2026-03-11 22:08:26', '2026-03-12 10:36:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel_acceso` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `nivel_acceso`, `activo`, `fecha_creacion`) VALUES
(1, 'pastor', 'Acceso total al sistema', 5, 1, '2026-03-09 23:14:35'),
(2, 'lider_area', 'Gestion de area de servicio', 3, 1, '2026-03-09 23:14:35'),
(3, 'lider_celula', 'Reporte de celula', 2, 1, '2026-03-09 23:14:35'),
(4, 'tesorero', 'Gestion de ofrendas', 4, 1, '2026-03-09 23:14:35'),
(5, 'servidor', 'Acceso limitado', 1, 1, '2026-03-09 23:14:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servidores`
--

DROP TABLE IF EXISTS `servidores`;
CREATE TABLE `servidores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `area_servicio_id` int(11) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `bautizado` tinyint(1) DEFAULT 0,
  `fecha_bautizo` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servidores`
--

INSERT INTO `servidores` (`id`, `usuario_id`, `area_servicio_id`, `cedula`, `genero`, `fecha_nacimiento`, `bautizado`, `fecha_bautizo`, `activo`, `fecha_ingreso`, `fecha_modificacion`) VALUES
(1, 7, 1, NULL, 'M', NULL, 1, NULL, 1, '2026-03-09 23:16:09', '2026-03-09 23:16:09'),
(2, 8, 3, NULL, 'F', NULL, 1, NULL, 1, '2026-03-09 23:16:09', '2026-03-09 23:16:09'),
(3, 9, 7, NULL, 'M', NULL, 1, NULL, 1, '2026-03-09 23:16:09', '2026-03-09 23:16:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `codigo_membresia` char(10) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `ip_registro` varchar(45) DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `correo`, `telefono`, `password_hash`, `rol_id`, `codigo_membresia`, `fecha_ingreso`, `activo`, `ultimo_acceso`, `ip_registro`, `intentos_fallidos`, `bloqueado_hasta`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 'Pastor Principal', 'pastor@iglesia.com', '+503 7123-4567', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 1, 'MEM000001', '2026-03-09', 1, '2026-03-17 14:42:48', '::1', 0, NULL, '2026-03-09 23:16:09', '2026-03-17 14:42:48'),
(2, 'María García - Líder Jóvenes', 'lider.jovenes@iglesia.com', '+503 7234-5678', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 2, 'MEM000002', '2026-03-09', 1, NULL, NULL, 0, NULL, '2026-03-09 23:16:09', '2026-03-09 23:40:06'),
(3, 'Carlos López - Líder Matrimonios', 'lider.matrimonios@iglesia.com', '+503 7345-6789', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 2, 'MEM000003', '2026-03-09', 1, NULL, NULL, 0, NULL, '2026-03-09 23:16:09', '2026-03-09 23:40:06'),
(4, 'Juan Pérez - Líder Célula Centro', 'juan.perez@iglesia.com', '+503 7456-7890', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 3, 'MEM000004', '2026-03-09', 1, NULL, NULL, 0, NULL, '2026-03-09 23:16:09', '2026-03-09 23:40:06'),
(5, 'Ana Martínez - Líder Célula San Benito', 'ana.martinez@iglesia.com', '+503 7567-8901', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 3, 'MEM000005', '2026-03-09', 1, NULL, NULL, 0, NULL, '2026-03-09 23:16:09', '2026-03-09 23:40:06'),
(6, 'David Rodríguez - Tesorero', 'tesorero@iglesia.com', '+503 7678-9012', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 4, 'MEM000006', '2026-03-09', 1, NULL, NULL, 0, NULL, '2026-03-09 23:16:09', '2026-03-09 23:40:06'),
(7, 'Roberto García', 'roberto@iglesia.com', '+503 7789-0123', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 5, 'MEM000007', '2026-03-09', 1, NULL, NULL, 0, NULL, '2026-03-09 23:16:09', '2026-03-09 23:40:06'),
(8, 'Marta Sánchez', 'marta@iglesia.com', '+503 7890-1234', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 5, 'MEM000008', '2026-03-09', 1, NULL, NULL, 0, NULL, '2026-03-09 23:16:09', '2026-03-09 23:40:06'),
(9, 'Pedro Flores', 'pedro@iglesia.com', '+503 7901-2345', '$argon2id$v=19$m=65536,t=4,p=3$RWFmNHh6MFF1V1ZCcFByNg$gNcfV7U4lmJESlQ5IsyNqqv0hE/5sxRzD6sDe0yJrrA', 5, 'MEM000009', '2026-03-09', 1, NULL, NULL, 0, NULL, '2026-03-09 23:16:09', '2026-03-09 23:40:06');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_celulas_detalle`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_celulas_detalle`;
CREATE TABLE `vw_celulas_detalle` (
`id` int(11)
,`nombre` varchar(100)
,`direccion` text
,`dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo')
,`hora_inicio` time
,`estado` enum('activa','inactiva','pausada')
,`cantidad_promedio_asistentes` int(11)
,`lider_nombre` varchar(150)
,`lider_telefono` varchar(20)
,`anfitrion_nombre` varchar(150)
,`area_servicio` varchar(100)
,`fecha_creacion` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_ofrendas_pendientes`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_ofrendas_pendientes`;
CREATE TABLE `vw_ofrendas_pendientes` (
`id` int(11)
,`fecha_reunion` date
,`celula` varchar(100)
,`monto` decimal(10,2)
,`estado` enum('reportada','recibida','conciliada')
,`lider` varchar(150)
,`telefono` varchar(20)
,`dias_pendiente` int(7)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_servidores_por_area`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_servidores_por_area`;
CREATE TABLE `vw_servidores_por_area` (
`id` int(11)
,`area` varchar(100)
,`cantidad_servidores` bigint(21)
,`lider_area` varchar(150)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_celulas_detalle`
--
DROP TABLE IF EXISTS `vw_celulas_detalle`;

DROP VIEW IF EXISTS `vw_celulas_detalle`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_celulas_detalle`  AS SELECT `c`.`id` AS `id`, `c`.`nombre` AS `nombre`, `c`.`direccion` AS `direccion`, `c`.`dia_semana` AS `dia_semana`, `c`.`hora_inicio` AS `hora_inicio`, `c`.`estado` AS `estado`, `c`.`cantidad_promedio_asistentes` AS `cantidad_promedio_asistentes`, `u_lider`.`nombre_completo` AS `lider_nombre`, `u_lider`.`telefono` AS `lider_telefono`, `u_anfitrion`.`nombre_completo` AS `anfitrion_nombre`, `a`.`nombre` AS `area_servicio`, `c`.`fecha_creacion` AS `fecha_creacion` FROM (((`celulas` `c` left join `usuarios` `u_lider` on(`c`.`lider_id` = `u_lider`.`id`)) left join `usuarios` `u_anfitrion` on(`c`.`anfitrion_id` = `u_anfitrion`.`id`)) left join `areas_servicio` `a` on(`c`.`area_servicio_id` = `a`.`id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_ofrendas_pendientes`
--
DROP TABLE IF EXISTS `vw_ofrendas_pendientes`;

DROP VIEW IF EXISTS `vw_ofrendas_pendientes`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_ofrendas_pendientes`  AS SELECT `o`.`id` AS `id`, `r`.`fecha_reunion` AS `fecha_reunion`, `c`.`nombre` AS `celula`, `o`.`monto` AS `monto`, `o`.`estado` AS `estado`, `u`.`nombre_completo` AS `lider`, `u`.`telefono` AS `telefono`, to_days(current_timestamp()) - to_days(`o`.`fecha_reporte`) AS `dias_pendiente` FROM (((`ofrendas` `o` join `reuniones` `r` on(`o`.`reunion_id` = `r`.`id`)) join `celulas` `c` on(`r`.`celula_id` = `c`.`id`)) join `usuarios` `u` on(`o`.`lider_reporta_id` = `u`.`id`)) WHERE `o`.`estado` <> 'conciliada' ORDER BY `o`.`fecha_reporte` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_servidores_por_area`
--
DROP TABLE IF EXISTS `vw_servidores_por_area`;

DROP VIEW IF EXISTS `vw_servidores_por_area`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_servidores_por_area`  AS SELECT `a`.`id` AS `id`, `a`.`nombre` AS `area`, count(`s`.`id`) AS `cantidad_servidores`, `u`.`nombre_completo` AS `lider_area` FROM ((`areas_servicio` `a` left join `servidores` `s` on(`a`.`id` = `s`.`area_servicio_id` and `s`.`activo` = 1)) left join `usuarios` `u` on(`a`.`lider_id` = `u`.`id`)) GROUP BY `a`.`id`, `a`.`nombre`, `u`.`nombre_completo` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `areas_servicio`
--
ALTER TABLE `areas_servicio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_lider` (`lider_id`),
  ADD KEY `idx_activa` (`activa`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reunion_usuario` (`reunion_id`,`usuario_id`),
  ADD KEY `idx_reunion` (`reunion_id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_tabla` (`tabla_afectada`),
  ADD KEY `idx_fecha` (`fecha_hora`);

--
-- Indices de la tabla `celulas`
--
ALTER TABLE `celulas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_lider` (`lider_id`),
  ADD KEY `idx_lider_area` (`lider_area_id`),
  ADD KEY `idx_area` (`area_servicio_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `anfitrion_id` (`anfitrion_id`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`),
  ADD UNIQUE KEY `uk_clave` (`clave`);

--
-- Indices de la tabla `delegaciones`
--
ALTER TABLE `delegaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_delegador` (`usuario_delegador_id`),
  ADD KEY `idx_delegado` (`usuario_delegado_id`),
  ADD KEY `idx_area` (`area_servicio_id`),
  ADD KEY `idx_celula` (`celula_id`);

--
-- Indices de la tabla `log_acceso`
--
ALTER TABLE `log_acceso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_exitoso` (`exitoso`),
  ADD KEY `idx_fecha` (`fecha_hora`);

--
-- Indices de la tabla `materiales_estudio`
--
ALTER TABLE `materiales_estudio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_area` (`area_servicio_id`),
  ADD KEY `idx_celula` (`celula_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_subido_por` (`subido_por_id`),
  ADD KEY `version_anterior_id` (`version_anterior_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_destino_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_leida` (`leida`);

--
-- Indices de la tabla `ofrendas`
--
ALTER TABLE `ofrendas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reunion_id` (`reunion_id`),
  ADD KEY `idx_reunion` (`reunion_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_lider_reporta` (`lider_reporta_id`),
  ADD KEY `idx_usuario_recibe` (`usuario_recibe_id`),
  ADD KEY `idx_conciliacion` (`usuario_concilia_id`);

--
-- Indices de la tabla `reuniones`
--
ALTER TABLE `reuniones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reunion_unica` (`celula_id`,`fecha_reunion`),
  ADD KEY `idx_celula` (`celula_id`),
  ADD KEY `idx_fecha` (`fecha_reunion`),
  ADD KEY `idx_lider_reporta` (`lider_reporta_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `uk_nombre` (`nombre`),
  ADD KEY `idx_nivel` (`nivel_acceso`);

--
-- Indices de la tabla `servidores`
--
ALTER TABLE `servidores`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `uk_usuario_area` (`usuario_id`,`area_servicio_id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `idx_area` (`area_servicio_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `codigo_membresia` (`codigo_membresia`),
  ADD KEY `idx_rol` (`rol_id`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_codigo_membresia` (`codigo_membresia`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `areas_servicio`
--
ALTER TABLE `areas_servicio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `celulas`
--
ALTER TABLE `celulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `delegaciones`
--
ALTER TABLE `delegaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `log_acceso`
--
ALTER TABLE `log_acceso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `materiales_estudio`
--
ALTER TABLE `materiales_estudio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ofrendas`
--
ALTER TABLE `ofrendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reuniones`
--
ALTER TABLE `reuniones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `servidores`
--
ALTER TABLE `servidores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `areas_servicio`
--
ALTER TABLE `areas_servicio`
  ADD CONSTRAINT `areas_servicio_ibfk_1` FOREIGN KEY (`lider_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`reunion_id`) REFERENCES `reuniones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `celulas`
--
ALTER TABLE `celulas`
  ADD CONSTRAINT `celulas_ibfk_1` FOREIGN KEY (`lider_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `celulas_ibfk_2` FOREIGN KEY (`lider_area_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `celulas_ibfk_3` FOREIGN KEY (`anfitrion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `celulas_ibfk_4` FOREIGN KEY (`area_servicio_id`) REFERENCES `areas_servicio` (`id`);

--
-- Filtros para la tabla `delegaciones`
--
ALTER TABLE `delegaciones`
  ADD CONSTRAINT `delegaciones_ibfk_1` FOREIGN KEY (`usuario_delegador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delegaciones_ibfk_2` FOREIGN KEY (`usuario_delegado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delegaciones_ibfk_3` FOREIGN KEY (`area_servicio_id`) REFERENCES `areas_servicio` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `delegaciones_ibfk_4` FOREIGN KEY (`celula_id`) REFERENCES `celulas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `materiales_estudio`
--
ALTER TABLE `materiales_estudio`
  ADD CONSTRAINT `materiales_estudio_ibfk_1` FOREIGN KEY (`area_servicio_id`) REFERENCES `areas_servicio` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materiales_estudio_ibfk_2` FOREIGN KEY (`celula_id`) REFERENCES `celulas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materiales_estudio_ibfk_3` FOREIGN KEY (`subido_por_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `materiales_estudio_ibfk_4` FOREIGN KEY (`version_anterior_id`) REFERENCES `materiales_estudio` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_destino_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ofrendas`
--
ALTER TABLE `ofrendas`
  ADD CONSTRAINT `ofrendas_ibfk_1` FOREIGN KEY (`reunion_id`) REFERENCES `reuniones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ofrendas_ibfk_2` FOREIGN KEY (`lider_reporta_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `ofrendas_ibfk_3` FOREIGN KEY (`usuario_recibe_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ofrendas_ibfk_4` FOREIGN KEY (`usuario_concilia_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reuniones`
--
ALTER TABLE `reuniones`
  ADD CONSTRAINT `reuniones_ibfk_1` FOREIGN KEY (`celula_id`) REFERENCES `celulas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reuniones_ibfk_2` FOREIGN KEY (`lider_reporta_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `servidores`
--
ALTER TABLE `servidores`
  ADD CONSTRAINT `servidores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `servidores_ibfk_2` FOREIGN KEY (`area_servicio_id`) REFERENCES `areas_servicio` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
