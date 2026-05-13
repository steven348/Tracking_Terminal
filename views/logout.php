<?php
// /TRACKING_TERMINAL/views/logout.php

session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Borrar la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión en el servidor
session_destroy();

// IMPORTANTE: Al estar dentro de /views/, para volver al index 
// de la raíz necesitamos subir un nivel (../)
header("Location: ../index.php?logout=1");
exit();