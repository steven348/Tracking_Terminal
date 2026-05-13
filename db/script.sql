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

-- INSERCIÓN DE DATOS INICIALES BÁSICOS
INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Usuario');
