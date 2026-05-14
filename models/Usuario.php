<?php
// /TRACKING_TERMINAL/models/Usuario.php

class Usuario {
    private $conn;
    private $table = "usuarios";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function buscarPorUsuario($usuario) {
        $query = "SELECT * FROM " . $this->table . " WHERE nombre_usuario = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function registrar($datos) {
        try {
            // Verificar si ya existe el usuario o correo
            $checkQuery = "SELECT id_usuario FROM " . $this->table . " WHERE nombre_usuario = ? OR correo = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$datos['user'], $datos['email']]);
            
            if ($checkStmt->rowCount() > 0) {
                return "duplicado";
            }
            
            // Insertar nuevo usuario
            $query = "INSERT INTO " . $this->table . " 
                      (nombre_completo, nombre_usuario, correo, telefono, password, foto_perfil, id_rol) 
                      VALUES (?, ?, ?, ?, ?, ?, 2)";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $datos['nom'],
                $datos['user'],
                $datos['email'],
                $datos['tel'],
                $datos['pass'],
                $datos['foto']
            ]);
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error en registro de usuario: " . $e->getMessage());
            return false;
        }
    }
    
    // Método adicional para obtener usuario por ID
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_usuario = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>