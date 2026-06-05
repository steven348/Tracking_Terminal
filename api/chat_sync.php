<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$pdo = require_once __DIR__ . '/../config/database.php';
$method = $_SERVER['REQUEST_METHOD'];

// Crear tabla si no existe
$sql_create = "CREATE TABLE IF NOT EXISTS chat_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ruta INT NOT NULL,
    direccion VARCHAR(20),
    nombre VARCHAR(100),
    mensaje TEXT,
    tipo ENUM('conductor', 'usuario') DEFAULT 'usuario',
    timestamp DATETIME,
    INDEX idx_ruta_direccion (id_ruta, direccion)
)";
$pdo->exec($sql_create);

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id_ruta = $data['id_ruta'] ?? 0;
    $direccion = $data['direccion'] ?? '';
    $nombre = $data['nombre'] ?? 'Anónimo';
    $mensaje = $data['mensaje'] ?? '';
    $tipo = $data['tipo'] ?? 'usuario';
    
    if (!$id_ruta || !$mensaje) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit;
    }
    
    $sql = "INSERT INTO chat_tracking (id_ruta, direccion, nombre, mensaje, tipo, timestamp) 
            VALUES (:id_ruta, :direccion, :nombre, :mensaje, :tipo, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_ruta' => $id_ruta,
        ':direccion' => $direccion,
        ':nombre' => $nombre,
        ':mensaje' => $mensaje,
        ':tipo' => $tipo
    ]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    
} elseif ($method === 'GET') {
    $id_ruta = isset($_GET['id_ruta']) ? intval($_GET['id_ruta']) : 0;
    $direccion = isset($_GET['direccion']) ? $_GET['direccion'] : '';
    $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
    
    if (!$id_ruta) {
        echo json_encode(['error' => 'ID de ruta requerido']);
        exit;
    }
    
    $sql = "SELECT * FROM chat_tracking 
            WHERE id_ruta = :id_ruta 
            AND direccion = :direccion
            AND id > :last_id
            ORDER BY timestamp ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_ruta' => $id_ruta,
        ':direccion' => $direccion,
        ':last_id' => $last_id
    ]);
    
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'mensajes' => $mensajes,
        'last_id' => !empty($mensajes) ? end($mensajes)['id'] : $last_id
    ]);
}
?>