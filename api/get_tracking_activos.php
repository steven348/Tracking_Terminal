<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo = require_once __DIR__ . '/../config/database.php';

error_log("=== GET TRACKING ACTIVOS ===");

try {
    // Obtener SOLO trackings con sesión activa
    $sql = "SELECT 
                ts.id_session,
                ts.id_bus,
                ts.id_ruta,
                ts.nombre_ruta,
                ts.direccion,
                ts.terminal,
                ts.fecha_inicio,
                t.latitud,
                t.longitud,
                t.velocidad,
                t.ultima_actualizacion,
                COALESCE(b.placa, 'TEMP-001') as placa,
                COALESCE(b.numero_unidad, CONCAT('Unidad ', ts.id_bus)) as numero_unidad,
                COALESCE(r.origen, 'En tránsito') as origen,
                COALESCE(r.destino, 'Destino') as destino,
                TIMESTAMPDIFF(SECOND, t.ultima_actualizacion, NOW()) as segundos_sin_actualizar
            FROM tracking_sessions ts
            LEFT JOIN trackings t ON ts.id_bus = t.id_bus 
                AND t.ultima_actualizacion >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)
            LEFT JOIN buses b ON ts.id_bus = b.id_bus
            LEFT JOIN rutas r ON ts.id_ruta = r.id_ruta
            WHERE ts.estado = 'activo'
            ORDER BY ts.fecha_inicio DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $trackings = $stmt->fetchAll();
    
    error_log("Total de sesiones activas encontradas: " . count($trackings));
    
    $resultados = [];
    foreach ($trackings as $tracking) {
        error_log("Procesando sesión ID: " . $tracking['id_session'] . ", tiene ubicación: " . ($tracking['latitud'] ? 'SI' : 'NO'));
        
        // Si no tiene ubicación reciente, mostrar igual pero con estado "Sin ubicación"
        if ($tracking['latitud'] === null) {
            $resultados[] = [
                'id_session' => $tracking['id_session'],
                'id_bus' => $tracking['id_bus'],
                'latitud' => null,
                'longitud' => null,
                'velocidad' => 0,
                'ultima_actualizacion' => null,
                'segundos_sin_actualizar' => 999,
                'estado' => 'waiting',
                'estado_texto' => 'Esperando ubicación',
                'placa' => $tracking['placa'],
                'numero_unidad' => $tracking['numero_unidad'],
                'nombre_ruta' => $tracking['nombre_ruta'] ?? 'Ruta en curso',
                'direccion' => $tracking['direccion'],
                'terminal' => $tracking['terminal'],
                'origen' => $tracking['origen'],
                'destino' => $tracking['destino'],
                'fecha_inicio' => $tracking['fecha_inicio']
            ];
            continue;
        }
        
        $segundos = intval($tracking['segundos_sin_actualizar']);
        
        if ($segundos <= 30) {
            $estado = 'live';
            $estadoTexto = 'En vivo';
        } elseif ($segundos <= 60) {
            $estado = 'arriving';
            $estadoTexto = 'Próximo';
        } else {
            $estado = 'on-time';
            $estadoTexto = 'Activo';
        }
        
        $resultados[] = [
            'id_session' => $tracking['id_session'],
            'id_bus' => $tracking['id_bus'],
            'latitud' => $tracking['latitud'],
            'longitud' => $tracking['longitud'],
            'velocidad' => $tracking['velocidad'],
            'ultima_actualizacion' => $tracking['ultima_actualizacion'],
            'segundos_sin_actualizar' => $segundos,
            'estado' => $estado,
            'estado_texto' => $estadoTexto,
            'placa' => $tracking['placa'],
            'numero_unidad' => $tracking['numero_unidad'],
            'nombre_ruta' => $tracking['nombre_ruta'] ?? 'Ruta en curso',
            'direccion' => $tracking['direccion'],
            'terminal' => $tracking['terminal'],
            'origen' => $tracking['origen'],
            'destino' => $tracking['destino'],
            'fecha_inicio' => $tracking['fecha_inicio']
        ];
    }
    
    error_log("Total de resultados a enviar: " . count($resultados));
    
    echo json_encode([
        'success' => true,
        'trackings' => $resultados,
        'total' => count($resultados)
    ]);
    
} catch (PDOException $e) {
    error_log("ERROR SQL: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'trackings' => []]);
}
?>