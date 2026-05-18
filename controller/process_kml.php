<?php
// Limpiar cualquier salida previa
if (ob_get_length()) ob_clean();
ob_start();

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla, solo en log

session_start();

// Función para enviar respuesta JSON
function sendJSON($data) {
    ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    sendJSON(['error' => 'No autorizado. Inicia sesión nuevamente.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(['error' => 'Método no permitido. Usa POST.']);
}

if (!isset($_FILES['kml_file']) || $_FILES['kml_file']['error'] === UPLOAD_ERR_NO_FILE) {
    sendJSON(['error' => 'No se recibió ningún archivo KML.']);
}

// Incluir conexión a BD
$root = dirname(__DIR__, 1);
$config_file = $root . "/config/database.php";

if (!file_exists($config_file)) {
    sendJSON(['error' => 'Archivo de configuración no encontrado: ' . $config_file]);
}

$pdo = require_once $config_file;

if (!$pdo) {
    sendJSON(['error' => 'Error de conexión a la base de datos.']);
}

$file = $_FILES['kml_file'];

// Validar archivo
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor.',
        UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario.',
        UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente.',
        UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal.',
        UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco.',
        UPLOAD_ERR_EXTENSION => 'Extensión de archivo no permitida.'
    ];
    $error_msg = $errors[$file['error']] ?? 'Error desconocido al subir el archivo.';
    sendJSON(['error' => $error_msg]);
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($extension !== 'kml') {
    sendJSON(['error' => 'El archivo debe ser de tipo KML. Extensión recibida: .' . $extension]);
}

// Leer y parsear KML
$kml_content = file_get_contents($file['tmp_name']);
if ($kml_content === false) {
    sendJSON(['error' => 'No se pudo leer el contenido del archivo.']);
}

// Usar DOMDocument para parsear XML
$dom = new DOMDocument();
$dom->loadXML($kml_content);
$xml = simplexml_import_dom($dom);

if (!$xml) {
    sendJSON(['error' => 'No se pudo parsear el archivo KML. El archivo podría estar corrupto.']);
}

// Registrar nombres de espacio de nombres
$xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

// Buscar todos los Placemark
$placemarks = $xml->xpath('//kml:Placemark');

if (empty($placemarks)) {
    sendJSON(['error' => 'No se encontraron rutas (Placemark) en el archivo KML.']);
}

$total_rutas = 0;
$total_puntos = 0;
$errores = [];

foreach ($placemarks as $placemark) {
    // Obtener nombre de la ruta
    $nombre_ruta = trim((string)$placemark->name);
    if (empty($nombre_ruta)) continue;
    
    // Obtener descripción
    $descripcion = trim((string)$placemark->description);
    
    // Buscar LineString
    $line_string = null;
    
    // Intentar diferentes formas de encontrar las coordenadas
    if ($placemark->LineString) {
        $line_string = $placemark->LineString;
    }
    
    if (!$line_string) {
        $found = $placemark->xpath('.//kml:LineString');
        if (!empty($found)) {
            $line_string = $found[0];
        }
    }
    
    if (!$line_string) {
        $errores[] = "Ruta '$nombre_ruta' no tiene coordenadas (LineString)";
        continue;
    }
    
    $coordenadas_str = trim((string)$line_string->coordinates);
    
    if (empty($coordenadas_str)) {
        $errores[] = "Ruta '$nombre_ruta' tiene coordenadas vacías";
        continue;
    }
    
    // Separar puntos (por espacio o nueva línea)
    $puntos = preg_split('/\s+/', $coordenadas_str);
    
    if (count($puntos) < 2) {
        $errores[] = "Ruta '$nombre_ruta' tiene menos de 2 puntos (necesario para una ruta)";
        continue;
    }
    
    try {
        // Insertar ruta
        $stmt = $pdo->prepare("INSERT INTO rutas (nombre_ruta, descripcion, estado) VALUES (?, ?, 1)");
        $stmt->execute([$nombre_ruta, $descripcion]);
        $id_ruta = $pdo->lastInsertId();
        $total_rutas++;
        
        $orden = 1;
        $puntos_insertados = 0;
        
        foreach ($puntos as $punto) {
            $punto = trim($punto);
            if (empty($punto)) continue;
            
            $coords = explode(',', $punto);
            if (count($coords) >= 2) {
                $longitud = floatval(trim($coords[0]));
                $latitud = floatval(trim($coords[1]));
                
                // Validar coordenadas
                if ($latitud >= -90 && $latitud <= 90 && $longitud >= -180 && $longitud <= 180) {
                    $stmt = $pdo->prepare("INSERT INTO puntos_ruta (id_ruta, latitud, longitud, orden) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$id_ruta, $latitud, $longitud, $orden])) {
                        $puntos_insertados++;
                        $total_puntos++;
                        $orden++;
                    }
                }
            }
        }
        
        if ($puntos_insertados === 0) {
            // Si no se insertaron puntos, eliminar la ruta
            $pdo->prepare("DELETE FROM rutas WHERE id_ruta = ?")->execute([$id_ruta]);
            $total_rutas--;
            $errores[] = "Ruta '$nombre_ruta' no tiene puntos válidos (coordenadas fuera de rango)";
        }
        
    } catch (PDOException $e) {
        $errores[] = "Error al insertar ruta '$nombre_ruta': " . $e->getMessage();
    }
}

// Enviar respuesta
if ($total_rutas > 0) {
    sendJSON([
        'success' => true,
        'message' => "Se procesaron $total_rutas rutas con $total_puntos puntos.",
        'total_rutas' => $total_rutas,
        'total_puntos' => $total_puntos,
        'errores' => $errores
    ]);
} else {
    sendJSON([
        'error' => 'No se pudo procesar ninguna ruta. ' . implode('; ', $errores)
    ]);
}
?>