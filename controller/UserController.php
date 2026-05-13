<?php
require_once __DIR__ . '/../models/Usuario.php';

class UserController {
    private $modelo;
    public function __construct($db) { $this->modelo = new Usuario($db); }

    public function login($user, $pass) {
        $u = $this->modelo->buscarPorUsuario($user);
        if ($u && password_verify($pass, $u['password'])) {
            session_start();
            // Tus datos actuales
            $_SESSION['id_usuario'] = $u['id_usuario'];
            $_SESSION['nombre'] = $u['nombre_completo'];
            $_SESSION['foto'] = $u['foto_perfil'];
            
            // NUEVOS DATOS PARA LA PERSONALIZACIÓN
            $_SESSION['usuario_alias'] = $u['nombre_usuario']; // Para el @NombreUsuario en el menú
            $_SESSION['id_rol'] = $u['id_rol'];               // Para saber si es Admin o no
            
            header("Location: views/layouts/menu_general.php");
            exit();
        }
        return "Usuario o contraseña incorrectos.";
    }

    public function registro($post, $files) {
        // Manejo de la foto
        $foto = "default.png";
        if (isset($files['profile_image']) && $files['profile_image']['error'] == 0) {
            $ext = pathinfo($files['profile_image']['name'], PATHINFO_EXTENSION);
            $foto = "user_" . time() . "." . $ext;
            
            // Verificamos que la carpeta exista antes de mover
            $ruta_destino = "../assets/img/profiles/" . $foto;
            move_uploaded_file($files['profile_image']['tmp_name'], $ruta_destino);
        }

        $datos = [
            'nom' => $post['nombre_completo'],
            'user' => $post['usuario'],
            'email' => $post['email'],
            'tel' => $post['telefono'],
            'pass' => password_hash($post['password'], PASSWORD_BCRYPT),
            'foto' => $foto
        ];

        $res = $this->modelo->registrar($datos);
        if ($res === true) return "exito";
        if ($res === "duplicado") return "El usuario o correo ya están registrados.";
        return "Error al procesar el registro.";
    }
}