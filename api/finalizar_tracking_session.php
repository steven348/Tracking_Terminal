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

try {
    $sql = "UPDATE tracking_sessions SET estado = 'finalizado', fecha_fin = NOW() 
            WHERE id_bus = :id_bus AND estado = 'activo'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_bus' => $id_bus]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Sesión de tracking finalizada'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>