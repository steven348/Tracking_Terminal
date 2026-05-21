<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busito SV - Panel de Tracking</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/tracking_styles.css">
</head>
<body class="tracking-admin">

<div class="admin-layout" id="adminLayout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">

        <div class="sidebar-header">
            <a href="../layouts/menu_general.php" class="btn-back-icon">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="logo">
                <i class="fas fa-bus"></i>
                <span>Busito SV</span>
            </div>
            <button class="btn-toggle-sidebar" id="toggleSidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <!-- PANEL INICIAL -->
        <div id="trackingConfigPanel">

            <div class="control-group">
                <label class="label-light">SELECCIONA TERMINAL</label>
                <select id="terminalSelect" style="width:100%;">
                    <option value="" disabled selected>Buscar terminal...</option>
                    <option value="CABAÑAS">Cabañas</option>
                    <option value="CUSCATLAN">Cuscatlán</option>
                    <option value="oriente">Oriente</option>
                    <option value="centro">Centro</option>
                </select>
            </div>

            <div class="control-group">
                <label class="label-light">COLOR LINEA DE RUTA</label>
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
                <div id="routesContainer">
                    <p class="no-routes-msg">Selecciona una terminal para ver las rutas</p>
                </div>

                <!-- Panel de dirección (IDA/REGRESO) - oculto inicialmente -->
                <div id="directionPanel" style="display:none; margin-top: 15px;">
                    <label class="label-light">SELECCIONA DIRECCIÓN</label>
                    <div class="direction-buttons">
                        <button class="direction-btn" data-direction="IDA">
                            <i class="fas fa-arrow-right"></i> IDA
                        </button>
                        <button class="direction-btn" data-direction="REGRESO">
                            <i class="fas fa-arrow-left"></i> REGRESO
                        </button>
                    </div>
                    <div class="route-info" id="routeInfo" style="display:none; margin-top: 12px;">
                        <div class="info-row">
                            <span class="info-label">Origen:</span>
                            <span class="info-value" id="infoOrigen">—</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Destino:</span>
                            <span class="info-value" id="infoDestino">—</span>
                        </div>
                    </div>
                </div>

                <button class="btn-start-tracking" id="startTrackingBtn" disabled>
                    <i class="fas fa-play"></i> Iniciar Tracking
                </button>
            </div>

        </div>

        <!-- PANEL TRACKING ACTIVO -->
        <div id="trackingLivePanel" style="display:none;">

            <div class="live-tracking-panel">

                <div class="live-stats">
                    <div class="live-card">
                        <div class="live-card-top">
                            <span class="live-card-label">Terminal</span>
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <div class="live-card-value" id="liveTerminalName">—</div>
                    </div>

                    <div class="live-card">
                        <div class="live-card-top">
                            <span class="live-card-label">Ruta</span>
                            <i class="fas fa-road"></i>
                        </div>
                        <div class="live-card-value" id="liveRouteNumber">—</div>
                        <div class="live-card-sub" id="liveDirection">—</div>
                    </div>

                    <div class="live-card">
                        <div class="live-card-top">
                            <span class="live-card-label">Usuarios</span>
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="live-card-value" id="usuariosViendo">12</div>
                        <div class="live-card-sub">Viendo en vivo</div>
                    </div>

                    <div class="live-card">
                        <div class="live-card-top">
                            <span class="live-card-label">Estado</span>
                            <i class="fas fa-signal"></i>
                        </div>
                        <div class="live-card-value" style="color:#2ecc71;font-size:1rem;">Activo</div>
                    </div>

                </div>

                <!-- CHAT -->
                <div class="live-chat-section">
                    <div class="live-chat-title">
                        <i class="fas fa-comments"></i>
                        Chat en Vivo
                    </div>
                    <div class="chat-box">
                        <div class="chat-messages" id="chatMessages"></div>
                        <div class="chat-input-container">
                            <input type="text" class="chat-input" id="chatInput"
                                   placeholder="Escribe un mensaje..." maxlength="200">
                            <button class="chat-send-btn" id="sendMessageBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <button class="btn-finish-tracking" id="finishTrackingBtn">
                <i class="fas fa-stop-circle"></i>
                Finalizar Tracking
            </button>

        </div>

    </aside>

    <!-- BOTÓN ABRIR SIDEBAR -->
    <button class="btn-open-sidebar" id="openSidebar" style="display:none;">
        <i class="fas fa-bars"></i>
    </button>

    <!-- MAPA -->
    <main class="map-viewport">
        <div class="map-overlay-top">
            <div class="status-pill" id="statusPillContainer">
                <span class="dot" id="statusDot"></span>
                <span id="statusText">Inactive Tracking</span>
            </div>
            <div class="time-pill" id="liveClock">--:--</div>
        </div>
        <div id="map-tracking"></div>
    </main>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/TRACKING_TERMINAL/assets/js/realizar_tracking.js"></script>

</body>
</html>