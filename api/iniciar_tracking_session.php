<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo = require_once __DIR__ . '/../config/database.php';

// Leer datos POST
$input = json_decode(file_get_contents('php://input'), true);

// Log para depuración
error_log("=== INICIAR TRACKING SESSION ===");
error_log("Datos recibidos: " . print_r($input, true));

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos']);
    exit;
}

$id_bus = isset($input['id_bus']) ? intval($input['id_bus']) : 1;
$id_ruta = isset($input['id_ruta']) ? intval($input['id_ruta']) : null;
$nombre_ruta = isset($input['nombre_ruta']) ? $input['nombre_ruta'] : '';
$direccion = isset($input['direccion']) ? $input['direccion'] : '';
$terminal = isset($input['terminal']) ? $input['terminal'] : '';

error_log("ID Bus: $id_bus, ID Ruta: $id_ruta, Nombre: $nombre_ruta, Direccion: $direccion, Terminal: $terminal");

try {
    // Asegurar que el bus existe
    $checkBus = $pdo->prepare("SELECT id_bus FROM buses WHERE id_bus = :id_bus");
    $checkBus->execute([':id_bus' => $id_bus]);
    
    if (!$checkBus->fetch()) {
        error_log("Bus no existe, creando...");
        $insertBus = $pdo->prepare("INSERT INTO buses (id_bus, placa, numero_unidad, modelo, id_ruta, estado) 
                                    VALUES (:id_bus, :placa, :numero_unidad, :modelo, :id_ruta, 1)");
        $insertBus->execute([
            ':id_bus' => $id_bus,
            ':placa' => 'TEMP-' . str_pad($id_bus, 3, '0', STR_PAD_LEFT),
            ':numero_unidad' => 'Unidad ' . $id_bus,
            ':modelo' => 'Busito SV',
            ':id_ruta' => $id_ruta
        ]);
        error_log("Bus creado correctamente");
    }
    
    // Finalizar sesiones anteriores del mismo bus
    $sqlUpdate = "UPDATE tracking_sessions SET estado = 'finalizado', fecha_fin = NOW() 
                  WHERE id_bus = :id_bus AND estado = 'activo'";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([':id_bus' => $id_bus]);
    error_log("Sesiones anteriores finalizadas");
    
    // Crear nueva sesión
    $sql = "INSERT INTO tracking_sessions (id_bus, id_ruta, nombre_ruta, direccion, terminal, estado, fecha_inicio) 
            VALUES (:id_bus, :id_ruta, :nombre_ruta, :direccion, :terminal, 'activo', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_bus' => $id_bus,
        ':id_ruta' => $id_ruta,
        ':nombre_ruta' => $nombre_ruta,
        ':direccion' => $direccion,
        ':terminal' => $terminal
    ]);
    
    $id_session = $pdo->lastInsertId();
    error_log("Nueva sesión creada con ID: $id_session");
    
    echo json_encode([
        'success' => true,
        'id_session' => $id_session,
        'message' => 'Sesión de tracking iniciada'
    ]);
    
} catch (PDOException $e) {
    error_log("ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>