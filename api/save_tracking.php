<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo = require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'No se recibieron datos']);
    exit;
}

$id_bus = isset($input['id_bus']) ? intval($input['id_bus']) : 1;
$latitud = isset($input['latitud']) ? floatval($input['latitud']) : null;
$longitud = isset($input['longitud']) ? floatval($input['longitud']) : null;
$velocidad = isset($input['velocidad']) ? floatval($input['velocidad']) : 0;
$ruta_nombre = isset($input['ruta_nombre']) ? $input['ruta_nombre'] : '';
$direccion = isset($input['direccion']) ? $input['direccion'] : '';
$punto_actual = isset($input['punto_actual']) ? intval($input['punto_actual']) : 0;
$total_puntos = isset($input['total_puntos']) ? intval($input['total_puntos']) : 0;

if ($latitud === null || $longitud === null) {
    echo json_encode(['error' => 'Faltan coordenadas']);
    exit;
}

try {
    $sql = "INSERT INTO trackings (id_bus, latitud, longitud, velocidad, ultima_actualizacion) 
            VALUES (:id_bus, :latitud, :longitud, :velocidad, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_bus' => $id_bus,
        ':latitud' => $latitud,
        ':longitud' => $longitud,
        ':velocidad' => $velocidad
    ]);
    
    echo json_encode([
        'success' => true,
        'tracking_id' => $pdo->lastInsertId(),
        'message' => 'Tracking guardado correctamente'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al guardar tracking: ' . $e->getMessage()]);
}
?>