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
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-bus"></i> <span>DevCore Terminal</span>
            </div>
            <p class="subtitle">El Salvador Bus Network</p>
        </div>

        <!-- Grupo de Selección con más margen -->
        <div class="control-group">
            <label class="label-light">SELECT TERMINAL</label>
            <div class="custom-select">
                <i class="fas fa-location-dot"></i>
                <select id="terminalSelect">
                    <option value="occidente">Occidente</option>
                    <option value="oriente">Oriente</option>
                </select>
            </div>
        </div>

        <!-- Grupo de Color con más margen -->
        <div class="control-group">
            <label class="label-light">ROUTE LINE COLOR</label>
            <div class="color-picker-wrapper">
                <input type="color" id="routeColor" value="#00C2C7">
                <span class="text-white">Personalizar trazado</span>
            </div>
        </div>

        <!-- Lista de Rutas con separación mejorada -->
        <div class="active-routes-list">
            <div class="list-header">
                <span class="text-white">ACTIVE ROUTES</span>
                <span class="count">3 routes</span>
            </div>
            
            <div class="route-item active">
                <div class="route-marker" style="background: #00C2C7;"></div>
                <div class="route-details">
                    <span class="code text-white">OCC-201</span>
                    <span class="dest text-light">Santa Ana</span>
                </div>
                <span class="badge on-time">ON TIME</span>
            </div>

            <div class="route-item">
                <div class="route-marker" style="background: #3AD1D6;"></div>
                <div class="route-details">
                    <span class="code text-white">OCC-205</span>
                    <span class="dest text-light">Sonsonate</span>
                </div>
                <span class="badge delayed">+12M</span>
            </div>
        </div>

        <div class="sidebar-footer">
            <a href="../layouts/menu_general.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
        </div>
    </aside>

    <main class="map-viewport">
        <div class="map-overlay-top">
            <div class="status-pill text-white">
                <span class="dot pulse"></span> Live Tracking
            </div>
            <div class="time-pill text-white" id="liveClock">--:-- --</div>
        </div>
        <div id="map-tracking"></div>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/TRACKING_TERMINAL/assets/js/realizar_tracking.js"></script>
</body>
</html>