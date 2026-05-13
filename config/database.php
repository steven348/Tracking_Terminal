<?php
// Configuración de la base de datos
$host     = "localhost";
$db_name  = "busito_sv"; // El nombre de la base de datos
$username = "root";      // Usuario por defecto de WampServer
$password = "";          // WampServer por defecto no tiene contraseña para root
$charset  = "utf8mb4";

try {
    // Configurar el DSN (Data Source Name)
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    
    // Opciones de PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza errores si algo falla
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve los datos como array asociativo
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Mejora la seguridad
    ];

    // Crear la instancia de conexión
    $pdo = new PDO($dsn, $username, $password, $options);

    // Si quieres probar si funciona (borrar después de probar)
    // echo "Conexión exitosa a busito_sv";

} catch (PDOException $e) {
    // Si hay un error, lo captura y lo muestra
    die("Error de conexión: " . $e->getMessage());
}

// Retornar la conexión para usarla en otros archivos
return $pdo;
?>