<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id_ruta = $data['id_ruta'] ?? null;

if (!$id_ruta) {
    echo json_encode(['error' => 'ID de ruta no proporcionado']);
    exit();
}

$root = dirname(__DIR__, 1);
$pdo = require_once $root . "/config/database.php";

try {
    $pdo->beginTransaction();
    
    // Actualizar información básica de la ruta
    $stmt = $pdo->prepare("
        UPDATE rutas 
        SET nombre_ruta = ?, origen = ?, destino = ?, descripcion = ?, estado = ?
        WHERE id_ruta = ?
    ");
    $stmt->execute([
        $data['nombre_ruta'],
        $data['origen'],
        $data['destino'],
        $data['descripcion'],
        $data['estado'],
        $id_ruta
    ]);
    
    // Actualizar asignación de buses (primero desasignar todos)
    $stmt = $pdo->prepare("UPDATE buses SET id_ruta = NULL WHERE id_ruta = ?");
    $stmt->execute([$id_ruta]);
    
    // Asignar los buses seleccionados
    if (!empty($data['buses_asignados']) && is_array($data['buses_asignados'])) {
        $stmt = $pdo->prepare("UPDATE buses SET id_ruta = ? WHERE id_bus = ?");
        foreach ($data['buses_asignados'] as $id_bus) {
            $stmt->execute([$id_ruta, $id_bus]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Ruta actualizada correctamente']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>