<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Incluir la conexión a la base de datos
$pdo = require_once __DIR__ . '/../config/database.php';

// Obtener el departamento desde la petición GET
$departamento = isset($_GET['departamento']) ? $_GET['departamento'] : '';

// Palabras clave por departamento
$departamentoKeywords = [
    'CABAÑAS' => ['CABAÑAS', 'CABANAS', 'SENSUNTEPEQUE', 'ILOBASCO', 'SAN ISIDRO', 'VICTORIA', 'TEJUTEPEQUE'],
    'CUSCATLAN' => ['CUSCATLAN', 'CUSCATLÁN', 'COJUTEPEQUE', 'SAN PEDRO PERULAPÁN', 'TENANCINGO', 'SUCHITOTO', 'SAN RAFAEL CEDROS'],
    'oriente' => ['SAN MIGUEL', 'USULUTÁN', 'LA UNIÓN', 'MORAZÁN'],
    'centro' => ['SAN SALVADOR', 'SOYAPANGO', 'APOPA', 'MEJICANOS']
];

// Si no se especifica departamento, devolver todas las rutas
if (empty($departamento)) {
    // Agrupar por nombre base (sin IDA/REGRESO)
    $sql = "SELECT id_ruta, nombre_ruta, origen, destino, descripcion 
            FROM rutas 
            WHERE estado = 1 
            AND (nombre_ruta NOT LIKE '%REGRESO%' OR nombre_ruta NOT LIKE '%REGRESO%')
            GROUP BY REPLACE(REPLACE(nombre_ruta, ' IDA', ''), ' REGRESO', '')
            ORDER BY nombre_ruta";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rutas = $stmt->fetchAll();
    
    echo json_encode($rutas);
    exit;
}

$keyword = isset($departamentoKeywords[$departamento]) ? $departamentoKeywords[$departamento] : [$departamento];

// Crear condiciones para buscar en origen, destino o descripción
$conditions = [];
$params = [];

foreach ($keyword as $index => $kw) {
    $paramOrigen = ":origen_$index";
    $paramDestino = ":destino_$index";
    $paramDesc = ":desc_$index";
    $conditions[] = "(UPPER(origen) LIKE $paramOrigen OR UPPER(destino) LIKE $paramDestino OR UPPER(descripcion) LIKE $paramDesc)";
    $params[$paramOrigen] = '%' . $kw . '%';
    $params[$paramDestino] = '%' . $kw . '%';
    $params[$paramDesc] = '%' . $kw . '%';
}

// Seleccionar rutas únicas (una por número/base, sin duplicar IDA/REGRESO)
$sql = "SELECT id_ruta, nombre_ruta, origen, destino, descripcion 
        FROM rutas 
        WHERE estado = 1 
        AND (" . implode(" OR ", $conditions) . ")
        AND (nombre_ruta NOT LIKE '%REGRESO%' OR nombre_ruta NOT LIKE '%REGRESO%')
        GROUP BY REPLACE(REPLACE(nombre_ruta, ' IDA', ''), ' REGRESO', '')
        ORDER BY nombre_ruta";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rutas = $stmt->fetchAll();

echo json_encode($rutas);
?>