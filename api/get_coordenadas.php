<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo = require_once __DIR__ . '/../config/database.php';

$id_ruta = isset($_GET['id_ruta']) ? intval($_GET['id_ruta']) : 0;

if ($id_ruta <= 0) {
    echo json_encode(['error' => 'ID de ruta no válido']);
    exit;
}

// IMPORTANTE: Ordenar por 'orden' ASC para mantener el sentido original de la ruta
$sql = "SELECT latitud, longitud 
        FROM puntos_ruta 
        WHERE id_ruta = :id_ruta 
        ORDER BY orden ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_ruta' => $id_ruta]);
$coordenadas = $stmt->fetchAll();

$coordsArray = [];
foreach ($coordenadas as $coord) {
    $coordsArray[] = [floatval($coord['latitud']), floatval($coord['longitud'])];
}

echo json_encode($coordsArray);
?>