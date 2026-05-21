<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Incluir la conexión a la base de datos
$pdo = require_once __DIR__ . '/../config/database.php';

// Obtener los datos enviados desde el frontend
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'No se recibieron datos']);
    exit;
}

$id_bus = isset($input['id_bus']) ? intval($input['id_bus']) : 1; // Temporal: 1
$latitud = isset($input['latitud']) ? floatval($input['latitud']) : null;
$longitud = isset($input['longitud']) ? floatval($input['longitud']) : null;
$velocidad = isset($input['velocidad']) ? floatval($input['velocidad']) : 0;
$ruta_nombre = isset($input['ruta_nombre']) ? $input['ruta_nombre'] : '';
$direccion = isset($input['direccion']) ? $input['direccion'] : '';
$punto_actual = isset($input['punto_actual']) ? intval($input['punto_actual']) : 0;
$total_puntos = isset($input['total_puntos']) ? intval($input['total_puntos']) : 0;

// Validar datos
if ($latitud === null || $longitud === null) {
    echo json_encode(['error' => 'Faltan coordenadas']);
    exit;
}

try {
    // Insertar registro de tracking
    $sql = "INSERT INTO trackings (id_bus, latitud, longitud, velocidad, ultima_actualizacion) 
            VALUES (:id_bus, :latitud, :longitud, :velocidad, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_bus' => $id_bus,
        ':latitud' => $latitud,
        ':longitud' => $longitud,
        ':velocidad' => $velocidad
    ]);
    
    $tracking_id = $pdo->lastInsertId();
    
    // También guardar en una tabla de historial de tracking por ruta (opcional)
    $sqlLog = "INSERT INTO tracking_logs (id_bus, ruta_nombre, direccion, latitud, longitud, punto_actual, total_puntos, fecha_hora) 
               VALUES (:id_bus, :ruta_nombre, :direccion, :latitud, :longitud, :punto_actual, :total_puntos, NOW())";
    
    $stmtLog = $pdo->prepare($sqlLog);
    $stmtLog->execute([
        ':id_bus' => $id_bus,
        ':ruta_nombre' => $ruta_nombre,
        ':direccion' => $direccion,
        ':latitud' => $latitud,
        ':longitud' => $longitud,
        ':punto_actual' => $punto_actual,
        ':total_puntos' => $total_puntos
    ]);
    
    echo json_encode([
        'success' => true,
        'tracking_id' => $tracking_id,
        'message' => 'Tracking guardado correctamente'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error al guardar tracking: ' . $e->getMessage()
    ]);
}
?>