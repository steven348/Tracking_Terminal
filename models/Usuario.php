<?php
class Usuario {
    private $pdo;
    public function __construct($db) { $this->pdo = $db; }

    public function registrar($datos) {
        try {
            $sql = "INSERT INTO usuarios (nombre_completo, nombre_usuario, correo, telefono, password, foto_perfil) 
                    VALUES (:nom, :user, :email, :tel, :pass, :foto)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            // Código 23000 es para violación de integridad (campos UNIQUE duplicados)
            if ($e->getCode() == 23000) {
                return "duplicado";
            }
            return false;
        }
    }

    public function buscarPorUsuario($user) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
        $stmt->execute([$user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}