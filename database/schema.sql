-- Sistema de Control de Carpetas Fiscales - Archivo Central
-- Ejecutar en phpMyAdmin o: mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS archivo_central
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE archivo_central;

CREATE TABLE IF NOT EXISTS fiscalias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(150) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS despachos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fiscalia_id INT NOT NULL,
  codigo VARCHAR(20) NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (fiscalia_id) REFERENCES fiscalias(id) ON DELETE RESTRICT,
  UNIQUE KEY uk_despacho (fiscalia_id, codigo)
);

CREATE TABLE IF NOT EXISTS carpetas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero_carpeta VARCHAR(50) NOT NULL UNIQUE,
  imputado VARCHAR(200) NOT NULL,
  agraviado VARCHAR(200) NOT NULL,
  delito VARCHAR(200) NOT NULL,
  fiscalia_id INT NOT NULL,
  despacho_id INT NOT NULL,
  fiscal_responsable VARCHAR(200) NOT NULL,
  folios INT NOT NULL DEFAULT 0,
  estado_correo VARCHAR(50) NOT NULL DEFAULT 'ARCHIVO',
  correo_electronico VARCHAR(150) NOT NULL,
  estado ENUM('ARCHIVO_CENTRAL', 'PRESTADA', 'DESARCHIVADA') NOT NULL DEFAULT 'ARCHIVO_CENTRAL',
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (fiscalia_id) REFERENCES fiscalias(id),
  FOREIGN KEY (despacho_id) REFERENCES despachos(id)
);

CREATE TABLE IF NOT EXISTS prestamos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  carpeta_id INT NOT NULL,
  solicitante VARCHAR(200) NOT NULL,
  fecha_prestamo DATE NOT NULL,
  motivo TEXT NOT NULL,
  fecha_devolucion DATE NULL,
  estado ENUM('ACTIVO', 'DEVUELTO') NOT NULL DEFAULT 'ACTIVO',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (carpeta_id) REFERENCES carpetas(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS desarchivamientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  carpeta_id INT NOT NULL UNIQUE,
  solicitante VARCHAR(200) NOT NULL,
  motivo TEXT NOT NULL,
  fecha_desarchivo DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (carpeta_id) REFERENCES carpetas(id)
);

CREATE TABLE IF NOT EXISTS historial_movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  carpeta_id INT NOT NULL,
  tipo_movimiento ENUM('INGRESO', 'PRESTAMO', 'DEVOLUCION', 'DESARCHIVAMIENTO', 'OTRO') NOT NULL,
  descripcion TEXT NOT NULL,
  fecha_movimiento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  referencia_id INT NULL,
  FOREIGN KEY (carpeta_id) REFERENCES carpetas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS correos_recordatorio (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prestamo_id INT NOT NULL,
  dias_transcurridos INT NOT NULL,
  enviado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (prestamo_id) REFERENCES prestamos(id) ON DELETE CASCADE,
  UNIQUE KEY uk_prestamo_dias (prestamo_id, dias_transcurridos)
);

-- Datos de ejemplo (Fiscalía y Despacho del PDF)
INSERT INTO fiscalias (codigo, nombre) VALUES
  ('4FPPC', 'FISCALÍA PROVINCIAL PENAL CORPORATIVA HUANCAYO')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO despachos (fiscalia_id, codigo, nombre)
SELECT f.id, 'HYO', 'DESPACHO HUANCAYO'
FROM fiscalias f WHERE f.codigo = '4FPPC'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
