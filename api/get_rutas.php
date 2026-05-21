<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo = require_once __DIR__ . '/../config/database.php';

$departamento = isset($_GET['departamento']) ? $_GET['departamento'] : '';

$departamentoKeywords = [
    'CABAÑAS' => ['CABAÑAS', 'CABANAS', 'SENSUNTEPEQUE', 'ILOBASCO', 'SAN ISIDRO', 'VICTORIA', 'TEJUTEPEQUE'],
    'CUSCATLAN' => ['CUSCATLAN', 'CUSCATLÁN', 'COJUTEPEQUE', 'SAN PEDRO PERULAPÁN', 'TENANCINGO', 'SUCHITOTO', 'SAN RAFAEL CEDROS'],
    'oriente' => ['SAN MIGUEL', 'USULUTÁN', 'LA UNIÓN', 'MORAZÁN'],
    'centro' => ['SAN SALVADOR', 'SOYAPANGO', 'APOPA', 'MEJICANOS']
];

if (empty($departamento)) {
    $sql = "SELECT id_ruta, nombre_ruta, origen, destino, descripcion 
            FROM rutas WHERE estado = 1 ORDER BY nombre_ruta";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll());
    exit;
}

$keyword = isset($departamentoKeywords[$departamento]) ? $departamentoKeywords[$departamento] : [$departamento];

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

$sql = "SELECT id_ruta, nombre_ruta, origen, destino, descripcion 
        FROM rutas WHERE estado = 1 
        AND (" . implode(" OR ", $conditions) . ")
        GROUP BY REPLACE(REPLACE(nombre_ruta, ' IDA', ''), ' REGRESO', '')
        ORDER BY nombre_ruta";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());
?>