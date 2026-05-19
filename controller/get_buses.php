<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$root = dirname(__DIR__, 1);
$pdo = require_once $root . "/config/database.php";

try {
    // Obtener todos los buses
    $query = $pdo->query("
        SELECT b.*, r.nombre_ruta as ruta_asignada 
        FROM buses b 
        LEFT JOIN rutas r ON b.id_ruta = r.id_ruta 
        ORDER BY b.id_bus
    ");
    $buses = $query->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($buses);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>