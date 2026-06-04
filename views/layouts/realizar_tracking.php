<?php
session_start();
$usuario_id     = isset($_SESSION['id_usuario'])    ? $_SESSION['id_usuario']    : 1;
$usuario_nombre = isset($_SESSION['usuario_alias']) ? $_SESSION['usuario_alias'] : (isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Administrador');
$usuario_avatar = strtoupper(substr($usuario_nombre, 0, 1));
?>
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

    <!-- ══ SIDEBAR ══ -->
    <aside class="admin-sidebar" id="adminSidebar">

        <div class="sidebar-header">
            <a href="#" class="btn-back-icon" id="btnVolver">
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

        <!-- PANEL CONFIGURACIÓN -->
        <div id="trackingConfigPanel">

            <div class="control-group">
                <label class="label-light">Terminal</label>
                <select id="terminalSelect" style="width:100%;">
                    <option value="" disabled selected>Buscar terminal...</option>
                    <option value="CABAÑAS">Cabañas</option>
                    <option value="CUSCATLAN">Cuscatlán</option>
                    <option value="oriente">Oriente</option>
                    <option value="centro">Centro</option>
                </select>
            </div>

            <div class="control-group">
                <label class="label-light">Color de ruta</label>
                <div class="color-picker-wrapper">
                    <input type="color" id="routeColor" value="#00C2C7">
                    <span class="text-white" style="font-size:0.82rem;">Personalizar trazado</span>
                </div>
            </div>

            <div class="active-routes-list">
                <div class="list-header">
                    <span class="text-white" style="font-size:0.8rem;font-weight:700;">RUTAS ACTIVAS</span>
                    <span class="count" id="routeCount">0 routes</span>
                </div>

                <div id="routesContainer">
                    <p class="no-routes-msg">Selecciona una terminal para ver las rutas</p>
                </div>

                <!-- Panel dirección IDA/REGRESO -->
                <div id="directionPanel" style="display:block; margin-top:10px;">
                    <label class="label-light">Dirección</label>
                    <div class="direction-buttons">
                        <button class="direction-btn" data-direction="IDA" disabled>
                            <i class="fas fa-arrow-right"></i> IDA
                        </button>
                        <button class="direction-btn" data-direction="REGRESO" disabled>
                            <i class="fas fa-arrow-left"></i> REGRESO
                        </button>
                    </div>
                    <div class="route-info" id="routeInfo" style="display:none;">
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

                <!-- Botón rutas cercanas -->
                <button class="btn-ubicacion" id="btnRutasCercanas">
                    <i class="fas fa-location-crosshairs"></i>
                    Ver rutas cercanas a mí
                </button>

                <button class="btn-start-tracking" id="startTrackingBtn" disabled>
                    <i class="fas fa-play"></i> Iniciar Tracking
                </button>
            </div>

        </div>

        <!-- PANEL TRACKING ACTIVO -->
        <div id="trackingLivePanel" style="display:none;">
            <div class="live-tracking-panel">

                <div class="live-panel-header">
                    <div class="live-panel-title">
                        <i class="fas fa-route"></i> Panel en Vivo
                    </div>
                    <div class="live-badge">
                        <span class="pulse-dot"></span> LIVE
                    </div>
                </div>

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
                    </div>
                    <div class="live-card">
                        <div class="live-card-top">
                            <span class="live-card-label">Estado</span>
                            <i class="fas fa-signal"></i>
                        </div>
                        <div class="live-card-value" style="color:var(--success);font-size:0.82rem;">Activo</div>
                    </div>
                </div>

                <div class="live-chat-section">
                    <div class="live-chat-title">
                        <i class="fas fa-comments"></i> Chat en Vivo
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
                <i class="fas fa-stop-circle"></i> Finalizar Tracking
            </button>
        </div>

        <!-- Botón info privacidad -->
        <button class="info-pill-btn" id="openInfoModalBtn">
            <i class="fas fa-shield-alt"></i>
            Advertencia de privacidad
        </button>

    </aside>

    <button class="btn-open-sidebar" id="openSidebar" style="display:none;">
        <i class="fas fa-bars"></i>
    </button>

    <!-- ══ MAPA ══ -->
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

<!-- ══ MODAL INFO PRIVACIDAD ══ -->
<div class="info-modal-overlay" id="infoModal">
    <div class="info-modal-card">
        <div class="info-modal-header">
            <h3><i class="fas fa-shield-alt"></i> Advertencia de Privacidad</h3>
            <button class="info-modal-close" id="closeInfoModalBtn">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <div class="info-modal-body">
            <p class="highlight-text">
                Este sistema utiliza la geolocalización en tiempo real de tu dispositivo para trazar las rutas y realizar el tracking activo en el mapa.
            </p>
            <h4><i class="fas fa-triangle-exclamation"></i> Recomendaciones de uso</h4>
            <ul>
                <li><strong>Uso Autorizado:</strong> Asegúrate de contar con los permisos correspondientes antes de iniciar la transmisión.</li>
                <li><strong>Consumo de Batería:</strong> El uso continuo del GPS incrementa el consumo de energía. Mantén el dispositivo cargado si es posible.</li>
                <li><strong>Cierre Limpio:</strong> Al terminar, presiona <em>"Finalizar Tracking"</em> para detener la captura de coordenadas.</li>
            </ul>
        </div>
    </div>
</div>

<!-- ══ MODAL CONFIRMAR FINALIZAR ══ -->
<div class="info-modal-overlay" id="confirmFinishModal">
    <div class="info-modal-card format-confirm">
        <div class="info-modal-header">
            <h3><i class="fas fa-circle-exclamation" style="color:var(--danger);"></i> ¿Finalizar Tracking?</h3>
            <button class="info-modal-close" id="closeConfirmFinishModalBtn">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <div class="info-modal-body">
            <p class="highlight-text">
                ¿Estás seguro? Esta acción finalizará el tracking en tiempo real y dejarás de transmitir tu ubicación.
            </p>
            <div class="confirm-actions-wrapper">
                <button class="btn-modal-back" id="cancelFinishBtn">
                    Volver al mapa
                </button>
                <button class="btn-modal-terminate" id="executeFinishBtn">
                    <i class="fas fa-stop-circle"></i> Sí, Finalizar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL PEDIR UBICACIÓN ══ -->
<div id="modalUbicacion" style="display:none;">
    <div class="modal-destino-overlay"></div>
    <div class="modal-destino-box">
        <div class="modal-destino-icon" style="border-color:#3498db;">
            <i class="fas fa-location-crosshairs" style="color:#3498db;"></i>
        </div>
        <h2 class="modal-destino-title">Acceso a ubicación</h2>
        <p class="modal-destino-msg">
            Para mostrarte las rutas más cercanas,<br>
            necesitamos acceder a tu ubicación.
        </p>
        <button class="modal-destino-btn" id="btnPermitirUbicacion"
                style="background:linear-gradient(135deg,#3498db,#5dade2);">
            <i class="fas fa-location-dot"></i> Permitir ubicación
        </button>
        <button class="btn-omitir-ubicacion" id="btnOmitirUbicacion" style="margin-top:10px;">
            Omitir por ahora
        </button>
    </div>
</div>

<!-- ══ MODAL DESTINO ALCANZADO ══ -->
<div id="modalDestino" style="display:none;">
    <div class="modal-destino-overlay"></div>
    <div class="modal-destino-box">
        <div class="modal-destino-icon">
            <i class="fas fa-flag-checkered"></i>
        </div>
        <h2 class="modal-destino-title">¡Destino alcanzado!</h2>
        <p class="modal-destino-msg">
            La unidad ha llegado a su destino.<br>
            El tracking ha finalizado automáticamente.
        </p>
        <button class="modal-destino-btn" id="btnCerrarModalDestino">
            <i class="fas fa-check"></i> Aceptar y finalizar
        </button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
var usuarioActual = {
    id:     <?php echo $usuario_id; ?>,
    nombre: '<?php echo addslashes($usuario_nombre); ?>',
    avatar: '<?php echo $usuario_avatar; ?>'
};
</script>

<script src="/TRACKING_TERMINAL/assets/js/realizar_tracking.js"></script>

<script>
$(document).ready(function () {

    /* ── SELECT2 ── */
    $('#terminalSelect').select2({ placeholder: "Buscar terminal...", allowClear: false, width: '100%' });
    $('#terminalSelect').on('change', function () {
        var val = $(this).val();
        if (val) loadRoutes(val);
    });

    /* ── TOGGLE SIDEBAR ── */
    var layout    = document.getElementById('adminLayout');
    var toggleBtn = document.getElementById('toggleSidebar');
    var openBtn   = document.getElementById('openSidebar');
    toggleBtn.addEventListener('click', function () { layout.classList.add('sidebar-hidden');    openBtn.style.display = 'flex'; });
    openBtn.addEventListener('click',   function () { layout.classList.remove('sidebar-hidden'); openBtn.style.display = 'none'; });

    /* ── MODAL INFO PRIVACIDAD ── */
    $('#openInfoModalBtn').on('mouseenter', function() {
        clearTimeout(window._infoTimer);
        $('#infoModal').addClass('show');
    });

    $('#openInfoModalBtn').on('mouseleave', function() {
        window._infoTimer = setTimeout(function() {
            $('#infoModal').removeClass('show');
        }, 250);
    });

    $('.info-modal-card').on('mouseenter', function() {
        clearTimeout(window._infoTimer);
    });

    $('.info-modal-card').on('mouseleave', function() {
        window._infoTimer = setTimeout(function() {
            $('#infoModal').removeClass('show');
        }, 250);
    });

    $('#closeInfoModalBtn').on('click', function() { $('#infoModal').removeClass('show'); });
    $('#infoModal').on('click', function(e) { if ($(e.target).is('.info-modal-overlay')) $(this).removeClass('show'); });

    /* ── MODAL CONFIRMAR FINALIZAR ── */
    document.getElementById('finishTrackingBtn').addEventListener('click', function() {
        $('#confirmFinishModal').addClass('show');
    });

    $('#cancelFinishBtn, #closeConfirmFinishModalBtn').on('click', function() {
        $('#confirmFinishModal').removeClass('show');
    });

    $('#confirmFinishModal').on('click', function(e) {
        if ($(e.target).is('.info-modal-overlay')) $(this).removeClass('show');
    });

    $('#executeFinishBtn').on('click', function() {
        $('#confirmFinishModal').removeClass('show');
        resetearUI();
    });

    /* ── CHAT ── */
    var chatMessages = document.getElementById('chatMessages');
    var chatInput    = document.getElementById('chatInput');
    var ultimoEmisor = null;

    function getTime() {
        var now = new Date(), h = now.getHours(), m = String(now.getMinutes()).padStart(2,'0');
        return h + ':' + m;
    }

    function addSystemMessage(text) {
        ultimoEmisor = 'system';
        var div = document.createElement('div');
        div.className = 'chat-system-msg';
        div.innerHTML = '<span>' + text + '</span>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addOwnMessage(text) {
        var mismo = (ultimoEmisor === 'self');
        ultimoEmisor = 'self';
        var div = document.createElement('div');
        div.className = 'chat-message self';
        if (mismo) {
            div.innerHTML =
                '<div class="chat-avatar invisible"></div>' +
                '<div class="chat-content"><div class="chat-bubble">' + text + '</div></div>';
        } else {
            div.innerHTML =
                '<div class="chat-avatar">' + usuarioActual.avatar + '</div>' +
                '<div class="chat-content">' +
                    '<div class="chat-user">' + usuarioActual.nombre + ' · ' + getTime() + '</div>' +
                    '<div class="chat-bubble">' + text + '</div>' +
                '</div>';
        }
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

    document.getElementById('sendMessageBtn').addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') sendMessage(); });

    function avisar(tipo) {
        try { window.parent.postMessage({ tipo: tipo }, '*'); } catch(e) {}
    }

    function resetearUI() {
        document.getElementById('trackingLivePanel').style.display   = 'none';
        document.getElementById('trackingConfigPanel').style.display = 'block';
        document.getElementById('statusDot').classList.remove('active');
        document.getElementById('statusText').textContent            = 'Inactive Tracking';
        document.getElementById('statusPillContainer').style.borderColor = '';
        if (typeof detenerAnimacion === 'function') detenerAnimacion();
        if (typeof clearMap === 'function') clearMap();
        avisar('TRACKING_FINALIZADO');
    }

    document.getElementById('startTrackingBtn').addEventListener('click', function () {
        if (typeof selectedRoute === 'undefined' || !selectedRoute) return;
        var labels  = { 'CABAÑAS': 'Cabañas', 'CUSCATLAN': 'Cuscatlán', oriente: 'Oriente', centro: 'Centro' };
        var termVal = $('#terminalSelect').val();

        document.getElementById('liveTerminalName').textContent     = labels[termVal] || termVal || '—';
        document.getElementById('liveRouteNumber').textContent      = selectedRoute.id;
        document.getElementById('trackingConfigPanel').style.display = 'none';
        document.getElementById('trackingLivePanel').style.display   = 'block';
        document.getElementById('statusDot').classList.add('active');
        document.getElementById('statusText').textContent            = 'Live Tracking';
        document.getElementById('statusPillContainer').style.borderColor = 'var(--success)';

        document.getElementById('liveDirection').textContent = selectedDirection || '—';
        ultimoEmisor           = null;
        chatMessages.innerHTML = '';
        addSystemMessage('✅ Tracking iniciado · Ruta ' + selectedRoute.id + ' — ' + selectedDirection);
        avisar('TRACKING_INICIADO');
        if (typeof renderTracking === 'function') renderTracking();
    });

    document.getElementById('btnCerrarModalDestino').addEventListener('click', function () {
        document.getElementById('modalDestino').style.display = 'none';
        resetearUI();
    });

    document.getElementById('btnRutasCercanas').addEventListener('click', function () {
        if (typeof toggleRutasCercanas === 'function') toggleRutasCercanas();
    });

    document.getElementById('btnVolver').addEventListener('click', function(e) {
        e.preventDefault();
        try {
            if (window.parent !== window) {
                window.parent.postMessage({ tipo: 'IR_A_MENU' }, '*');
            } else {
                window.location.href = '../layouts/menu_general.php';
            }
        } catch(err) {
            window.location.href = '../layouts/menu_general.php';
        }
    });

    setTimeout(function() {
        document.getElementById('modalUbicacion').style.display = 'flex';
    }, 600);

    document.getElementById('btnPermitirUbicacion').addEventListener('click', function() {
        document.getElementById('modalUbicacion').style.display = 'none';
        if (typeof solicitarUbicacion === 'function') {
            solicitarUbicacion(function(ok) {
                if (ok && typeof mostrarPanelRutasCercanas === 'function') mostrarPanelRutasCercanas();
            });
        }
    });

    document.getElementById('btnOmitirUbicacion').addEventListener('click', function() {
        document.getElementById('modalUbicacion').style.display = 'none';
    });

});
</script>

</body>
</html>