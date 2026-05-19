<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id_ruta = $data['id'] ?? null;

if (!$id_ruta) {
    echo json_encode(['error' => 'ID de ruta no proporcionado']);
    exit();
}

$root = dirname(__DIR__, 1);
$pdo = require_once $root . "/config/database.php";

try {
    $pdo->beginTransaction();
    
    // Eliminar puntos de la ruta
    $stmt = $pdo->prepare("DELETE FROM puntos_ruta WHERE id_ruta = ?");
    $stmt->execute([$id_ruta]);
    
    // Actualizar buses que tenían esta ruta
    $stmt = $pdo->prepare("UPDATE buses SET id_ruta = NULL WHERE id_ruta = ?");
    $stmt->execute([$id_ruta]);
    
    // Eliminar la ruta
    $stmt = $pdo->prepare("DELETE FROM rutas WHERE id_ruta = ?");
    $stmt->execute([$id_ruta]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>