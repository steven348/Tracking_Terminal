

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busito SV - Panel de Tracking</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/tracking_styles.css">
</head>
<body class="tracking-admin">

<div class="admin-layout">
    <aside class="admin-sidebar">
        <a href="../layouts/menu_general.php" class="btn-back-icon">
            <i class="fas fa-arrow-left"></i>
        </a>
        
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-bus"></i> <span>Busito SV</span>
            </div>
        </div>

        <div class="control-group">
            <label class="label-light">SELECCIONA DEPARTAMENTO</label>
            <select id="terminalSelect" class="modern-select" style="width: 100%;">
                <option value="" disabled selected>Buscar departamento...</option>
                <option value="cabanas">cabanas</option>

            </select>
        </div>

        <div class="control-group">
            <label class="label-light">COLOR LINEA DE RUTA </label>
            <div class="color-picker-wrapper">
                <input type="color" id="routeColor" value="#00C2C7">
                <span class="text-white">Personalizar trazado</span>
            </div>
        </div>

        <div class="control-group" id="directionGroup" style="display: none;">
          <label class="label-light">SENTIDO DEL RECORRIDO</label>
          <select id="directionSelect" class="modern-select" style="width: 100%;">
        </select>
        </div>

        <div class="active-routes-list">
            <div class="list-header">
                <span class="text-white">RUTAS ACTIVAS</span>
                <span class="count" id="routeCount">0 routes</span>
            </div>
            <div id="routesContainer"></div>
            <button class="btn-start-tracking" id="startTrackingBtn" disabled>
                <i class="fas fa-play"></i> Iniciar Tracking
            </button>
        </div>
    </aside>

  <main class="map-viewport">
    <div class="map-overlay-top">
        <div id="statusPillContainer">
            <span class="dot" id="statusDot"></span> 
            <span id="statusText">Inactive Tracking</span>
            <span class="time-divider">|</span>
            <div class="time-pill" id="liveClock">--:-- --</div>
        </div>
    </div>
    <div id="map-tracking"></div>
</main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/Tracking_Terminal/assets/js/realizar_tracking.js"></script>

</body>
</html>