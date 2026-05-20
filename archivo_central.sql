-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-05-2026 a las 17:23:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `archivo_central`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carpetas`
--

CREATE TABLE `carpetas` (
  `id` int(11) NOT NULL,
  `numero_carpeta` varchar(50) NOT NULL,
  `imputado` varchar(200) NOT NULL,
  `agraviado` varchar(200) NOT NULL,
  `delito` varchar(200) NOT NULL,
  `fiscalia_id` int(11) NOT NULL,
  `despacho_id` int(11) NOT NULL,
  `fiscal_responsable` varchar(200) NOT NULL,
  `folios` int(11) NOT NULL DEFAULT 0,
  `estado_correo` varchar(50) NOT NULL DEFAULT 'ARCHIVO',
  `correo_electronico` varchar(150) NOT NULL,
  `estado` enum('ARCHIVO_CENTRAL','PRESTADA','DESARCHIVADA') NOT NULL DEFAULT 'ARCHIVO_CENTRAL',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carpetas`
--

INSERT INTO `carpetas` (`id`, `numero_carpeta`, `imputado`, `agraviado`, `delito`, `fiscalia_id`, `despacho_id`, `fiscal_responsable`, `folios`, `estado_correo`, `correo_electronico`, `estado`, `fecha_registro`, `created_at`, `updated_at`) VALUES
(1, '12-231', 'diego', 'oscar', 'ascesinato', 1, 1, 'milagors', 200, 'ARCHIVO', '72545330@continental.edu.pe', 'PRESTADA', '2026-05-18 17:58:32', '2026-05-18 22:58:32', '2026-05-18 23:02:18'),
(2, '12-2451', 'Erika', 'Rosa', 'robo', 1, 1, 'milagors', 200, 'CONSENTIDO', 'diegoquispe@gmail.com', 'PRESTADA', '2026-05-18 18:07:51', '2026-05-18 23:07:51', '2026-05-18 23:08:03'),
(3, '12-82843', 'fernando', 'nadia', 'Estafa', 1, 1, 'Raul', 200, 'ARCHIVO', 'jndakjda@gmail.com', 'PRESTADA', '2026-05-18 18:10:33', '2026-05-18 23:10:33', '2026-05-18 23:10:59'),
(4, '12-32321', 'Edith', 'Alex', 'Manuntencion', 1, 1, 'Gilberto', 200, 'ARCHIVO', 'DIEGOQUISPEACOSTA@GMAIL.COM', 'ARCHIVO_CENTRAL', '2026-05-20 12:03:14', '2026-05-20 17:03:14', '2026-05-20 17:03:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correos_recordatorio`
--

CREATE TABLE `correos_recordatorio` (
  `id` int(11) NOT NULL,
  `prestamo_id` int(11) NOT NULL,
  `dias_transcurridos` int(11) NOT NULL,
  `enviado_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `desarchivamientos`
--

CREATE TABLE `desarchivamientos` (
  `id` int(11) NOT NULL,
  `carpeta_id` int(11) NOT NULL,
  `solicitante` varchar(200) NOT NULL,
  `motivo` text NOT NULL,
  `fecha_desarchivo` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `despachos`
--

CREATE TABLE `despachos` (
  `id` int(11) NOT NULL,
  `fiscalia_id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `despachos`
--

INSERT INTO `despachos` (`id`, `fiscalia_id`, `codigo`, `nombre`, `created_at`) VALUES
(1, 1, 'HYO', 'DESPACHO HUANCAYO', '2026-05-18 22:55:01'),
(2, 2, 'Huancayo', 'Robo', '2026-05-18 23:09:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fiscalias`
--

CREATE TABLE `fiscalias` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fiscalias`
--

INSERT INTO `fiscalias` (`id`, `codigo`, `nombre`, `created_at`) VALUES
(1, '4FPPC', 'FISCALÍA PROVINCIAL PENAL CORPORATIVA HUANCAYO', '2026-05-18 22:55:01'),
(2, '4ffdcs', 'diego', '2026-05-18 23:08:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_movimientos`
--

CREATE TABLE `historial_movimientos` (
  `id` int(11) NOT NULL,
  `carpeta_id` int(11) NOT NULL,
  `tipo_movimiento` enum('INGRESO','PRESTAMO','DEVOLUCION','DESARCHIVAMIENTO','OTRO') NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT current_timestamp(),
  `referencia_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_movimientos`
--

INSERT INTO `historial_movimientos` (`id`, `carpeta_id`, `tipo_movimiento`, `descripcion`, `fecha_movimiento`, `referencia_id`) VALUES
(1, 1, 'INGRESO', 'Carpeta ingresada al Archivo Central', '2026-05-18 17:58:32', 1),
(2, 1, 'PRESTAMO', 'Préstamo a malu. Motivo: CORRUPCION', '2026-05-18 18:02:18', 1),
(3, 2, 'INGRESO', 'Carpeta ingresada al Archivo Central', '2026-05-18 18:07:51', 2),
(4, 2, 'PRESTAMO', 'Préstamo a Piero. Motivo: hola', '2026-05-18 18:08:03', 2),
(5, 3, 'INGRESO', 'Carpeta ingresada al Archivo Central', '2026-05-18 18:10:33', 3),
(6, 3, 'PRESTAMO', 'Préstamo a Piero. Motivo: NO tener fondos', '2026-05-18 18:10:59', 3),
(7, 4, 'INGRESO', 'Carpeta ingresada al Archivo Central', '2026-05-20 12:03:14', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `carpeta_id` int(11) NOT NULL,
  `solicitante` varchar(200) NOT NULL,
  `fecha_prestamo` date NOT NULL,
  `motivo` text NOT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `estado` enum('ACTIVO','DEVUELTO') NOT NULL DEFAULT 'ACTIVO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id`, `carpeta_id`, `solicitante`, `fecha_prestamo`, `motivo`, `fecha_devolucion`, `estado`, `created_at`) VALUES
(1, 1, 'malu', '2026-05-18', 'CORRUPCION', NULL, 'ACTIVO', '2026-05-18 23:02:18'),
(2, 2, 'Piero', '2026-05-13', 'hola', NULL, 'ACTIVO', '2026-05-18 23:08:03'),
(3, 3, 'Piero', '2026-05-01', 'NO tener fondos', NULL, 'ACTIVO', '2026-05-18 23:10:59');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carpetas`
--
ALTER TABLE `carpetas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_carpeta` (`numero_carpeta`),
  ADD KEY `fiscalia_id` (`fiscalia_id`),
  ADD KEY `despacho_id` (`despacho_id`);

--
-- Indices de la tabla `correos_recordatorio`
--
ALTER TABLE `correos_recordatorio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_prestamo_dias` (`prestamo_id`,`dias_transcurridos`);

--
-- Indices de la tabla `desarchivamientos`
--
ALTER TABLE `desarchivamientos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carpeta_id` (`carpeta_id`);

--
-- Indices de la tabla `despachos`
--
ALTER TABLE `despachos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_despacho` (`fiscalia_id`,`codigo`);

--
-- Indices de la tabla `fiscalias`
--
ALTER TABLE `fiscalias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `historial_movimientos`
--
ALTER TABLE `historial_movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carpeta_id` (`carpeta_id`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carpeta_id` (`carpeta_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carpetas`
--
ALTER TABLE `carpetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `correos_recordatorio`
--
ALTER TABLE `correos_recordatorio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `desarchivamientos`
--
ALTER TABLE `desarchivamientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `despachos`
--
ALTER TABLE `despachos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `fiscalias`
--
ALTER TABLE `fiscalias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `historial_movimientos`
--
ALTER TABLE `historial_movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carpetas`
--
ALTER TABLE `carpetas`
  ADD CONSTRAINT `carpetas_ibfk_1` FOREIGN KEY (`fiscalia_id`) REFERENCES `fiscalias` (`id`),
  ADD CONSTRAINT `carpetas_ibfk_2` FOREIGN KEY (`despacho_id`) REFERENCES `despachos` (`id`);

--
-- Filtros para la tabla `correos_recordatorio`
--
ALTER TABLE `correos_recordatorio`
  ADD CONSTRAINT `correos_recordatorio_ibfk_1` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `desarchivamientos`
--
ALTER TABLE `desarchivamientos`
  ADD CONSTRAINT `desarchivamientos_ibfk_1` FOREIGN KEY (`carpeta_id`) REFERENCES `carpetas` (`id`);

--
-- Filtros para la tabla `despachos`
--
ALTER TABLE `despachos`
  ADD CONSTRAINT `despachos_ibfk_1` FOREIGN KEY (`fiscalia_id`) REFERENCES `fiscalias` (`id`);

--
-- Filtros para la tabla `historial_movimientos`
--
ALTER TABLE `historial_movimientos`
  ADD CONSTRAINT `historial_movimientos_ibfk_1` FOREIGN KEY (`carpeta_id`) REFERENCES `carpetas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`carpeta_id`) REFERENCES `carpetas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
