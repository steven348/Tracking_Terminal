<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busito SV - Panel de Tracking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/tracking_styles.css">
</head>
<body class="tracking-admin">

<div class="admin-layout">
    <aside class="admin-sidebar">

     <!-- Botón Retroceso -->
<a href="../layouts/menu_general.php" class="btn-back-icon">
    <i class="fas fa-arrow-left"></i>
</a>
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-bus"></i> <span>Busito SV</span>
            </div>
        </div>

        <!-- Grupo de Selección con más margen -->
        <div class="control-group">
            <label class="label-light">SELECCIONA TERMINAL</label>
            <div class="custom-select">
                <i class="fas fa-location-dot"></i>
                <select id="terminalSelect">
                    <option value="" disabled selected>Seleccionar terminal...</option>
                    <option value="occidente">Occidente</option>
                    <option value="oriente">Oriente</option>
                </select>
            </div>
        </div>

        <!-- Grupo de Color con más margen -->
        <div class="control-group">
            <label class="label-light">COLOR LINEA DE RUTA </label>
            <div class="color-picker-wrapper">
                <input type="color" id="routeColor" value="#00C2C7">
                <span class="text-white">Personalizar trazado</span>
            </div>
        </div>

        <div class="active-routes-list">

    <div class="list-header">
        <span class="text-white">RUTAS ACTIVAS</span>
        <span class="count" id="routeCount">0 routes</span>
    </div>

    <!-- CONTENEDOR DINÁMICO -->
    <div id="routesContainer"></div>

    <button class="btn-start-tracking" id="startTrackingBtn" disabled>
        <i class="fas fa-play"></i>
        Iniciar Tracking
    </button>

</div>

        
    </aside>

    <main class="map-viewport">
        <div class="map-overlay-top">
            <div class="status-pill text-white" id="statusPillContainer">
    <span class="dot" id="statusDot"></span> 
    <span id="statusText">Inactive Tracking</span>
</div>
            <div class="time-pill text-white" id="liveClock">--:-- --</div>
        </div>
        <div id="map-tracking"></div>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/Tracking_Terminal/assets/js/realizar_tracking.js"></script>
</body>
</html>