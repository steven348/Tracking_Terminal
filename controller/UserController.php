<?php
// /TRACKING_TERMINAL/controller/UserController.php

require_once __DIR__ . '/../models/Usuario.php';

class UserController {
    private $modelo;
    
    public function __construct($db) { 
        $this->modelo = new Usuario($db); 
    }

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
        // Validar que los campos requeridos no estén vacíos
        if (empty($post['nombre_completo']) || empty($post['usuario']) || 
            empty($post['email']) || empty($post['password']) || empty($post['confirm_password'])) {
            return "Todos los campos son obligatorios";
        }
        
        // Validar que las contraseñas coincidan
        if ($post['password'] !== $post['confirm_password']) {
            return "Las contraseñas no coinciden";
        }
        
        // Validar longitud de contraseña
        if (strlen($post['password']) < 6) {
            return "La contraseña debe tener al menos 6 caracteres";
        }
        
        // Validar formato de email
        if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
            return "El correo electrónico no es válido";
        }
        
        // Manejo de la foto
        $foto = "default.png";
        
        // Crear directorio si no existe
        $upload_dir = dirname(__DIR__) . '/assets/img/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (isset($files['profile_image']) && $files['profile_image']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $file_type = $files['profile_image']['type'];
            $file_size = $files['profile_image']['size'];
            $file_ext = strtolower(pathinfo($files['profile_image']['name'], PATHINFO_EXTENSION));
            
            // Validar tipo de archivo
            if (!in_array($file_type, $allowed_types)) {
                return "Formato no permitido. Solo JPG, JPEG o PNG";
            }
            
            // Validar tamaño (2MB máximo)
            if ($file_size > 2 * 1024 * 1024) {
                return "La imagen no debe superar los 2MB";
            }
            
            // Validar extensión
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            if (!in_array($file_ext, $allowed_ext)) {
                return "Extensión no permitida. Solo .jpg, .jpeg o .png";
            }
            
            // Generar nombre único para la foto
            $foto = "user_" . time() . "_" . uniqid() . "." . $file_ext;
            $ruta_destino = $upload_dir . $foto;
            
            // Mover el archivo
            if (!move_uploaded_file($files['profile_image']['tmp_name'], $ruta_destino)) {
                return "Error al subir la imagen. Verifique los permisos de la carpeta.";
            }
        } elseif (isset($files['profile_image']) && $files['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
            // Si hay un error diferente a "no file"
            return "Error al procesar la imagen. Código: " . $files['profile_image']['error'];
        }

        $datos = [
            'nom' => trim($post['nombre_completo']),
            'user' => trim($post['usuario']),
            'email' => trim($post['email']),
            'tel' => trim($post['telefono']),
            'pass' => password_hash($post['password'], PASSWORD_BCRYPT),
            'foto' => $foto
        ];

        $res = $this->modelo->registrar($datos);
        
        if ($res === true) {
            return "exito";
        }
        
        if ($res === "duplicado") {
            return "El nombre de usuario o correo ya están registrados.";
        }
        
        return "Error al procesar el registro. Por favor, intente más tarde.";
    }
}
?>