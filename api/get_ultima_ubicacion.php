<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo = require_once __DIR__ . '/../config/database.php';

$id_bus = isset($_GET['id_bus']) ? intval($_GET['id_bus']) : 0;

if ($id_bus <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de bus no válido']);
    exit;
}

try {
    $sql = "SELECT latitud, longitud, ultima_actualizacion, velocidad 
            FROM trackings 
            WHERE id_bus = :id_bus 
            ORDER BY ultima_actualizacion DESC 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_bus' => $id_bus]);
    $ubicacion = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'ubicacion' => $ubicacion
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>