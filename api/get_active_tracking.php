<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$pdo = require_once __DIR__ . '/../config/database.php';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id_ruta = isset($_GET['id_ruta']) ? intval($_GET['id_ruta']) : 0;
    
    if ($id_ruta <= 0) {
        echo json_encode(['error' => 'ID de ruta no válido']);
        exit;
    }
    
    $sql = "SELECT id_bus, id_ruta, latitud, longitud, ruta_nombre, direccion, punto_actual, total_puntos, timestamp 
            FROM tracking_buses 
            WHERE id_ruta = :id_ruta 
            ORDER BY timestamp DESC 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_ruta' => $id_ruta]);
    $tracking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tracking) {
        echo json_encode([
            'success' => true,
            'tracking' => $tracking
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No hay tracking activo para esta ruta'
        ]);
    }
    
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['error' => 'Datos inválidos']);
        exit;
    }
    
    $sql_check = "SELECT id FROM tracking_buses WHERE id_ruta = :id_ruta";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':id_ruta' => $data['id_ruta']]);
    $existe = $stmt_check->fetch();
    
    if ($existe) {
        $sql = "UPDATE tracking_buses 
                SET latitud = :latitud, 
                    longitud = :longitud, 
                    punto_actual = :punto_actual,
                    timestamp = NOW()
                WHERE id_ruta = :id_ruta";
    } else {
        $sql = "INSERT INTO tracking_buses (id_bus, id_ruta, latitud, longitud, ruta_nombre, direccion, punto_actual, total_puntos, timestamp) 
                VALUES (:id_bus, :id_ruta, :latitud, :longitud, :ruta_nombre, :direccion, :punto_actual, :total_puntos, NOW())";
    }
    
    $stmt = $pdo->prepare($sql);
    
    $params = [
        ':id_ruta' => $data['id_ruta'],
        ':latitud' => $data['latitud'],
        ':longitud' => $data['longitud'],
        ':punto_actual' => $data['punto_actual'] ?? 0
    ];
    
    if (!$existe) {
        $params[':id_bus'] = $data['id_bus'] ?? 1;
        $params[':ruta_nombre'] = $data['ruta_nombre'] ?? '';
        $params[':direccion'] = $data['direccion'] ?? 'IDA';
        $params[':total_puntos'] = $data['total_puntos'] ?? 1;
    }
    
    $result = $stmt->execute($params);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Tracking actualizado']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar']);
    }
    
} elseif ($method === 'DELETE') {
    // Limpiar todos los trackings activos
    $sql = "DELETE FROM tracking_buses";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Todos los trackings han sido eliminados']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al limpiar trackings']);
    }
    
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
?>