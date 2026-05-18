-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS busito_sv;

-- Seleccionar la base de datos para trabajar en ella
USE busito_sv;

-- 1. Definición de Roles (Admin y Usuario)
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL
);

-- 2. Información de Usuarios y Perfil
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(200) NOT NULL,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT 'default.png',
    id_rol INT DEFAULT 2, -- Por defecto 'Usuario'
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

-- 3. Gestión de Rutas
CREATE TABLE rutas (
    id_ruta INT AUTO_INCREMENT PRIMARY KEY,
    nombre_ruta VARCHAR(100) NOT NULL, -- Ej: "Ruta 111 - Ilobasco"
    origen VARCHAR(100),
    destino VARCHAR(100),
    descripcion TEXT,
    estado BOOLEAN DEFAULT TRUE
);

-- 4. Puntos Geográficos de la Ruta (Para procesar el KML)
-- Aquí es donde se guardarán las coordenadas extraídas de tus archivos
CREATE TABLE puntos_ruta (
    id_punto INT AUTO_INCREMENT PRIMARY KEY,
    id_ruta INT,
    latitud DECIMAL(10, 8) NOT NULL,
    longitud DECIMAL(11, 8) NOT NULL,
    orden INT NOT NULL, -- Importante para dibujar la línea en el orden correcto
    FOREIGN KEY (id_ruta) REFERENCES rutas(id_ruta) ON DELETE CASCADE
);

-- 5. Unidades de Transporte (Buses)
CREATE TABLE buses (
    id_bus INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(20) UNIQUE NOT NULL,
    numero_unidad VARCHAR(20),
    modelo VARCHAR(50),
    id_ruta INT,
    FOREIGN KEY (id_ruta) REFERENCES rutas(id_ruta) ON DELETE SET NULL
);

-- 6. Registro de Tracking en Tiempo Real
CREATE TABLE trackings (
    id_tracking INT AUTO_INCREMENT PRIMARY KEY,
    id_bus INT,
    latitud DECIMAL(10, 8) NOT NULL,
    longitud DECIMAL(11, 8) NOT NULL,
    velocidad DECIMAL(5, 2), -- Opcional: km/h
    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_bus) REFERENCES buses(id_bus) ON DELETE CASCADE
);



-- INSERCIÓN DE DATOS INICIALES BÁSICOS
INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Usuario');
