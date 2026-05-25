<?php
session_start();
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Usuario';
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busito SV - Ver Trackings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/Ver_tracking.css">
</head>
<body>

<div class="main-wrapper">
    <!-- Navbar Superior -->
    <nav class="top-nav">
        <div class="logo">
            <i class="fas fa-bus"></i> <span>Busito SV</span>
        </div>
        <div class="nav-icons">
            <a href="../layouts/menu_general.php" style="color: inherit; text-decoration: none;">
                <i class="fas fa-home"></i>
            </a>
            <i class="fas fa-bell"></i>
            <i class="fas fa-user-circle"></i>
            <span style="font-size: 0.8rem;"><?php echo htmlspecialchars($usuario_nombre); ?></span>
        </div>
    </nav>

    <!-- Banner Principal con Favoritos a la derecha -->
    <header class="hero-banner">
        <div class="hero-content">
            <div class="hero-left">
                <h1>Trackings en Vivo</h1>
                <p>Monitoreo en tiempo real de todas las unidades activas</p>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="routeSearch" placeholder="Buscar por ruta, unidad o terminal...">
                </div>
                <div class="hero-buttons">
                    <button class="btn-map"><i class="fas fa-map"></i> Ver Mapa Completo</button>
                    <button class="btn-routes"><i class="fas fa-route"></i> Ver Trackings Activos</button>
                </div>
            </div>
            
            <div class="hero-right">
                <div class="favorites-header">
                    <h3><i class="fas fa-star"></i> Favoritos</h3>
                    <a href="#" class="see-all">Editar</a>
                </div>
                <div class="favorites-tags">
                    <span class="tag"><i class="fas fa-bus"></i> Ruta 111 Cabañas</span>
                    <span class="tag"><i class="fas fa-building"></i> T. Cabañas</span>
                    <span class="tag"><i class="fas fa-bus"></i> Ruta 112 San Salvador</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Principal -->
    <div class="dashboard-grid">
        <!-- Sección Izquierda: Lista de Trackings Activos -->
        <section class="feed-section">
            <div class="section-header">
                <h3><i class="fas fa-location-dot"></i> Trackings Activos</h3>
                <span class="count-badge" id="trackingCount">0 activos</span>
            </div>
            <div class="routes-grid" id="routesContainer">
                <div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando trackings activos...</div>
            </div>
        </section>

        <!-- Sección Derecha: Mapa + Detalles -->
        <aside class="side-panel">
            <div class="section-header">
                <h3><i class="fas fa-map-marked-alt"></i> Mapa en Vivo</h3>
                <button class="info-pill-btn" id="infoModalBtn"><i class="fas fa-info"></i></button>
            </div>
            
            <div id="map-osm"></div>

            <!-- Panel de Detalles del Tracking -->
            <div class="tracking-details" id="trackingDetailsPanel" style="display: none;">
                <div class="details-header">
                    <h3><i class="fas fa-bus"></i> Detalles del Tracking</h3>
                    <span id="detailEstado"></span>
                </div>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">UNIDAD</span>
                        <span class="detail-value" id="detailUnidad">—</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">PLACA</span>
                        <span class="detail-value" id="detailPlaca">—</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">RUTA</span>
                        <span class="detail-value ruta" id="detailRuta">—</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">DIRECCIÓN</span>
                        <span class="detail-value" id="detailDireccion">—</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">TERMINAL</span>
                        <span class="detail-value" id="detailTerminal">—</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">INICIADO</span>
                        <span class="detail-value" id="detailInicio">—</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">VELOCIDAD</span>
                        <span class="detail-value" id="detailVelocidad">—</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">ÚLTIMA ACT.</span>
                        <span class="detail-value" id="detailActualizacion">—</span>
                    </div>
                </div>
                <div class="route-path">
                    <span class="detail-label">RECORRIDO</span>
                    <div class="route-path-icons">
                        <div class="path-icon"><i class="fas fa-play-circle"></i> <span id="detailOrigen">—</span></div>
                        <div class="path-line"></div>
                        <div class="path-icon"><i class="fas fa-flag-checkered"></i> <span id="detailDestino">—</span></div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Modal de Información -->
<div class="info-modal-overlay" id="infoModal">
    <div class="info-modal-card">
        <div class="info-modal-header">
            <h3><i class="fas fa-info-circle"></i> Acerca de Trackings</h3>
            <button class="info-modal-close" id="infoModalClose">&times;</button>
        </div>
        <div class="info-modal-body">
            <p>Los trackings muestran en tiempo real la ubicación de las unidades activas.</p>
            <h4><i class="fas fa-clock"></i> Actualización</h4>
            <p>Las posiciones se actualizan automáticamente cada 5 segundos mientras el tracking esté activo.</p>
            <h4><i class="fas fa-circle-info"></i> Estados</h4>
            <ul>
                <li><span style="color: #00C2C7;">● En vivo</span> - Actualización hace menos de 30 segundos</li>
                <li><span style="color: #f39c12;">● Próximo</span> - Actualización hace 30-60 segundos</li>
                <li><span style="color: #2ecc71;">● Activo</span> - Actualización hace 1-2 minutos</li>
            </ul>
            <h4><i class="fas fa-bus"></i> ¿Cómo aparecen aquí?</h4>
            <p>Solo aparecen los trackings que se están realizando activamente desde la sección <strong>"Realizar Tracking"</strong>. Al finalizar un tracking, desaparece automáticamente de esta lista.</p>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Pasar datos del usuario al JavaScript
var usuarioActual = {
    id: <?php echo $usuario_id; ?>,
    nombre: '<?php echo htmlspecialchars($usuario_nombre); ?>'
};
</script>
<script src="/TRACKING_TERMINAL/assets/js/script_trackings.js"></script>
</body>
</html>