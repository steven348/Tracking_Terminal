<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busito SV - Ver Trackings</title>
    <!-- Iconos y Leaflet (OpenStreetMap) -->
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
        </div>
    </nav>

    <!-- Banner Principal -->
    <header class="hero-banner">
        <div class="hero-content">
            <h1>Busito SV El Salvador</h1>
            <p>Real-time bus tracking to help you get there faster</p>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="routeSearch" placeholder="Search routes, terminals or destination">
            </div>
            <div class="hero-buttons">
                <button class="btn-map"><i class="fas fa-map"></i> View Map</button>
                <button class="btn-routes"><i class="fas fa-route"></i> Routes</button>
            </div>
        </div>
    </header>

    <!-- Sección de Favoritos (Ahora arriba del Dashboard) -->
    <div class="content-container">
        <div class="section-header">
            <h3><i class="fas fa-star"></i> Favorites</h3>
            <a href="#" class="see-all">Edit</a>
        </div>
        <div class="favorites-tags">
            <span class="tag"><i class="fas fa-bus"></i> Ruta 101-B Centro</span>
            <span class="tag"><i class="fas fa-building"></i> T. Oriente Terminal</span>
            <span class="tag"><i class="fas fa-bus"></i> Ruta 29 Metrocentro</span>
        </div>
    </div>

    <!-- Dashboard Principal -->
    <div class="dashboard-grid">
        <!-- Sección Izquierda: Nearby -->
        <section class="feed-section">
            <div class="section-header">
                <h3><i class="fas fa-location-dot"></i> Nearby</h3>
                <a href="#" class="see-all">See all</a>
            </div>

            <div class="routes-grid">
                <div class="route-card">
                    <div class="route-info">
                        <div class="route-icon"><i class="fas fa-bus"></i></div>
                        <div><h4>Ruta 101-B</h4><p>Centro Histórico</p></div>
                    </div>
                    <div class="route-time arriving">3 min</div>
                    <div class="route-footer">
                        <span>Terminal de Oriente</span>
                        <span class="status-tag arriving">Arriving</span>
                    </div>
                </div>

                <div class="route-card">
                    <div class="route-info">
                        <div class="route-icon"><i class="fas fa-bus"></i></div>
                        <div><h4>Ruta 29</h4><p>Metrocentro</p></div>
                    </div>
                    <div class="route-time on-time">5 min</div>
                    <div class="route-footer">
                        <span>Blvd. de los Héroes</span>
                        <span class="status-tag on-time">On Time</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Derecha: Mapa OSM -->
        <aside class="side-panel">
            <div class="section-header">
                <h3><i class="fas fa-map-marked-alt"></i> Live Map</h3>
                <a href="#" class="see-all">Full screen</a>
            </div>
            
            <div id="map-osm"></div>
        </aside>
    </div>
</div>

<!-- Scripts de Mapa -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/TRACKING_TERMINAL/assets/js/script_trackings.js"></script>
</body>
</html>