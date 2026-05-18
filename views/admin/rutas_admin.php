<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

$root = dirname(__DIR__, 2);
$pdo = require_once $root . "/config/database.php";

// Obtener todas las rutas con sus buses
$rutas_query = $pdo->query("
    SELECT r.*, COUNT(b.id_bus) as total_buses 
    FROM rutas r 
    LEFT JOIN buses b ON r.id_ruta = b.id_ruta 
    GROUP BY r.id_ruta 
    ORDER BY r.id_ruta DESC
");
$rutas = $rutas_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Rutas | Busito SV</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/rutas_admin.css">
</head>
<body>

    <!-- Incluir el sidebar del admin -->
    <?php include 'slider_admin.php'; ?>

    <!-- Contenido principal -->
    <div class="content-wrapper">
        <!-- Encabezado -->
        <div class="content-header">
            <h1><i class="fas fa-map"></i> Gestión de Rutas</h1>
            <p>Administra las rutas de buses del sistema</p>
        </div>

        <!-- Sección de carga de KML -->
        <div class="upload-section">
            <div class="upload-card">
                <div class="upload-icon">
                    <i class="fas fa-upload"></i>
                </div>
                <h3>Subir archivo KML</h3>
                <p>Selecciona un archivo KML para importar rutas al sistema</p>
                <form id="uploadKmlForm">
                    <input type="file" id="kmlFile" accept=".kml" required>
                    <button type="submit" id="uploadBtn">
                        <i class="fas fa-cloud-upload-alt"></i> Subir y Procesar
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabla de rutas -->
        <div class="routes-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Rutas Registradas</h2>
                <button class="refresh-btn" id="refreshRoutes">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
            
            <div class="table-container">
                <table class="routes-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Ruta</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Buses Asignados</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="routesTableBody">
                        <?php foreach ($rutas as $ruta): ?>
                        <tr>
                            <td><?php echo $ruta['id_ruta']; ?></td>
                            <td><strong><?php echo htmlspecialchars($ruta['nombre_ruta']); ?></strong></td>
                            <td><?php echo htmlspecialchars($ruta['origen'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($ruta['destino'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge badge-buses">
                                    <i class="fas fa-bus"></i> <?php echo $ruta['total_buses']; ?> buses
                                </span>
                             </div>
                             <div class="badge <?php echo $ruta['estado'] ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $ruta['estado'] ? 'Activa' : 'Inactiva'; ?>
                                </span>
                             </div>
                             <div class="action-buttons">
                                <button class="action-btn edit-route" data-id="<?php echo $ruta['id_ruta']; ?>" title="Editar ruta">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn view-route" data-id="<?php echo $ruta['id_ruta']; ?>" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn delete-route" data-id="<?php echo $ruta['id_ruta']; ?>" title="Eliminar ruta">
                                    <i class="fas fa-trash"></i>
                                </button>
                             </div>
                         </tr>
                        <?php endforeach; ?>
                        
                        <?php if (count($rutas) == 0): ?>
                         <tr>
                            <td colspan="7" class="empty-row">
                                <i class="fas fa-map-marker-alt"></i>
                                <p>No hay rutas registradas</p>
                                <small>Sube un archivo KML para comenzar</small>
                             </td>
                         </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de proceso (barra de progreso) -->
    <div id="progressModal" class="modal-process">
        <div class="modal-overlay"></div>
        <div class="progress-card">
            <div class="progress-header">
                <i class="fas fa-cogs"></i>
                <h2>Procesando archivo KML</h2>
                <p>Extrayendo información de rutas...</p>
            </div>
            
            <div class="progress-card__bar-container">
                <div class="progress-card__bar" id="progressBar"></div>
            </div>
            
            <div class="progress-stats">
                <div class="stat">
                    <span class="stat-label">Rutas encontradas:</span>
                    <span class="stat-value" id="rutasCount">0</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Puntos de ruta:</span>
                    <span class="stat-value" id="puntosCount">0</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Progreso:</span>
                    <span class="stat-value" id="progressPercent">0%</span>
                </div>
            </div>
            
            <div class="progress-status" id="progressStatus">
                <i class="fas fa-spinner fa-pulse"></i>
                <span>Iniciando proceso...</span>
            </div>
            
            <div class="progress-buttons" id="progressButtons" style="display: none;">
                <button class="btn-success" id="btnOk">
                    <i class="fas fa-check-circle"></i> OK - Finalizar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Edición de Ruta -->
    <div id="editRouteModal" class="modal-edit">
        <div class="modal-overlay"></div>
        <div class="modal-edit-content">
            <div class="modal-edit-header">
                <h3><i class="fas fa-edit"></i> Editar Ruta</h3>
                <span class="close-modal">&times;</span>
            </div>
            
            <div id="modalLoading" class="modal-loading">
                <i class="fas fa-spinner fa-pulse"></i>
                <p>Cargando información...</p>
            </div>
            
            <div id="modalContent" style="display: none;">
                <form id="editRouteForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-road"></i> Nombre de la Ruta</label>
                            <input type="text" id="edit_nombre_ruta" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row-2">
                        <div class="form-group">
                            <label><i class="fas fa-play-circle"></i> Origen</label>
                            <input type="text" id="edit_origen" class="form-control" placeholder="Ej: Terminal San Salvador">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-stop-circle"></i> Destino</label>
                            <input type="text" id="edit_destino" class="form-control" placeholder="Ej: Sensuntepeque">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Descripción</label>
                        <textarea id="edit_descripcion" class="form-control" rows="3" placeholder="Descripción de la ruta..."></textarea>
                    </div>
                    
                    <div class="form-row-2">
                        <div class="form-group">
                            <label><i class="fas fa-chart-line"></i> Estado</label>
                            <select id="edit_estado" class="form-control">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-map-pin"></i> Puntos en Ruta</label>
                            <input type="text" id="total_puntos" class="form-control" readonly disabled>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-bus"></i> Buses Asignados</label>
                        <select id="edit_buses_asignados" class="form-control" multiple size="5">
                            <!-- Las opciones se llenan dinámicamente -->
                        </select>
                        <small class="form-hint">Mantén presionada la tecla Ctrl (Cmd en Mac) para seleccionar múltiples buses</small>
                    </div>
                    
                    <div class="modal-edit-buttons">
                        <button type="button" id="saveRouteBtn" class="btn-save">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button type="button" id="cancelEditBtn" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="/TRACKING_TERMINAL/assets/js/rutas_admin.js"></script>
</body>
</html>