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
                    <option value="occidente">Cabañas</option>
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
                <button class="btn-start-tracking" id="startTrackingBtn" disabled>
                    <i class="fas fa-play"></i> Iniciar Tracking
                </button>
            </div>

        </div>

        <!-- PANEL TRACKING ACTIVO -->
        <div id="trackingLivePanel" style="display:none;">

            <div class="live-tracking-panel">

                

                <!-- TARJETAS -->
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
                        <div class="live-card-sub">En seguimiento</div>
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
<script src="/Tracking_Terminal/assets/js/realizar_tracking.js"></script>

<script>
$(document).ready(function () {

    /* ── SELECT2 ── */
    $('#terminalSelect').select2({
        placeholder: "Buscar terminal...",
        allowClear: false,
        width: '100%'
    });

    $('#terminalSelect').on('change', function () {
        var val = $(this).val();
        if (val) loadRoutes(val);
    });

    /* ── TOGGLE SIDEBAR ── */
    var layout    = document.getElementById('adminLayout');
    var toggleBtn = document.getElementById('toggleSidebar');
    var openBtn   = document.getElementById('openSidebar');

    toggleBtn.addEventListener('click', function () {
        layout.classList.add('sidebar-hidden');
        openBtn.style.display = 'flex';
    });

    openBtn.addEventListener('click', function () {
        layout.classList.remove('sidebar-hidden');
        openBtn.style.display = 'none';
    });

    /* ── CHAT ── */
    var chatMessages = document.getElementById('chatMessages');
    var chatInput    = document.getElementById('chatInput');
    var sendBtn      = document.getElementById('sendMessageBtn');

    function getTime() {
        var now  = new Date();
        var h    = now.getHours();
        var m    = String(now.getMinutes()).padStart(2, '0');
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ampm;
    }

    function addSystemMessage(text) {
        var div = document.createElement('div');
        div.className = 'chat-system-msg';
        div.innerHTML = '<span>' + text + '</span>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addOwnMessage(text) {
        var div = document.createElement('div');
        div.className = 'chat-message self';
        div.innerHTML =
            '<div class="chat-avatar">A</div>' +
            '<div class="chat-content">' +
                '<div class="chat-user">Administrador · ' + getTime() + '</div>' +
                '<div class="chat-bubble">' + text + '</div>' +
            '</div>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function sendMessage() {
        var text = chatInput.value.trim();
        if (!text) return;
        addOwnMessage(text);
        chatInput.value = '';
        chatInput.focus();
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') sendMessage();
    });

    /* ── INICIAR TRACKING ── */
    document.getElementById('startTrackingBtn').addEventListener('click', function () {

        if (!selectedRoute) return;

        var terminalLabel = {
            occidente: 'Cabañas',
            oriente:   'Oriente',
            centro:    'Centro'
        };

        var termVal = $('#terminalSelect').val();

        document.getElementById('liveTerminalName').textContent = terminalLabel[termVal] || termVal;
        document.getElementById('liveRouteNumber').textContent  = selectedRoute.id;

        document.getElementById('trackingConfigPanel').style.display = 'none';
        document.getElementById('trackingLivePanel').style.display   = 'block';

        document.getElementById('statusDot').classList.add('active');
        document.getElementById('statusText').textContent = 'Live Tracking';
        document.getElementById('statusPillContainer').style.borderColor = '#2ecc71';

        chatMessages.innerHTML = '';
        addSystemMessage('✅ Tracking iniciado · Ruta ' + selectedRoute.id);

        renderTracking();
    });

    /* ── FINALIZAR TRACKING ── */
    document.getElementById('finishTrackingBtn').addEventListener('click', function () {
        document.getElementById('trackingLivePanel').style.display   = 'none';
        document.getElementById('trackingConfigPanel').style.display = 'block';

        document.getElementById('statusDot').classList.remove('active');
        document.getElementById('statusText').textContent = 'Inactive Tracking';
        document.getElementById('statusPillContainer').style.borderColor = '';
    });

});
</script>

</body>
</html>