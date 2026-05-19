<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$root = dirname(__DIR__, 1);
$pdo = require_once $root . "/config/database.php";

try {
    $query = $pdo->query("
        SELECT u.*, r.nombre_rol 
        FROM usuarios u 
        LEFT JOIN roles r ON u.id_rol = r.id_rol 
        ORDER BY u.id_usuario DESC
    ");
    $usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($usuarios);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>