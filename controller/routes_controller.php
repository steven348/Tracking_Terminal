<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$root = dirname(__DIR__, 1);
$pdo = require_once $root . "/config/database.php";

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_all':
            // Obtener todas las rutas
            $query = $pdo->query("
                SELECT r.*, COUNT(b.id_bus) as total_buses 
                FROM rutas r 
                LEFT JOIN buses b ON r.id_ruta = b.id_ruta 
                GROUP BY r.id_ruta 
                ORDER BY r.id_ruta DESC
            ");
            $rutas = $query->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rutas);
            break;
            
        case 'get_one':
            // Obtener una ruta específica
            $id_ruta = $_GET['id'] ?? null;
            if (!$id_ruta) {
                echo json_encode(['error' => 'ID de ruta no proporcionado']);
                exit();
            }
            
            // Obtener información de la ruta
            $stmt = $pdo->prepare("
                SELECT r.*, 
                       (SELECT COUNT(*) FROM puntos_ruta WHERE id_ruta = r.id_ruta) as total_puntos
                FROM rutas r 
                WHERE r.id_ruta = ?
            ");
            $stmt->execute([$id_ruta]);
            $ruta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Obtener buses asignados a esta ruta
            $stmt = $pdo->prepare("
                SELECT * FROM buses WHERE id_ruta = ? ORDER BY numero_unidad
            ");
            $stmt->execute([$id_ruta]);
            $buses_asignados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener todos los buses disponibles
            $stmt = $pdo->prepare("
                SELECT * FROM buses ORDER BY numero_unidad
            ");
            $stmt->execute();
            $todos_buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'ruta' => $ruta,
                'buses_asignados' => $buses_asignados,
                'todos_buses' => $todos_buses
            ]);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no especificada']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>