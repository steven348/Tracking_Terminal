<?php
session_start();
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Administrador';
$usuario_avatar = substr($usuario_nombre, 0, 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Busito SV - Panel de Tracking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/tracking_styles.css">
</head>
<body class="tracking-admin">

<div class="admin-layout" id="adminLayout">

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="../layouts/menu_general.php" class="btn-back-icon"><i class="fas fa-arrow-left"></i></a>
            <div class="logo"><i class="fas fa-bus"></i><span>Busito SV</span></div>
            <button class="btn-toggle-sidebar" id="toggleSidebar"><i class="fas fa-chevron-left"></i></button>
        </div>

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
                <div class="list-header"><span class="text-white">RUTAS ACTIVAS</span><span class="count" id="routeCount">0 routes</span></div>
                <div id="routesContainer"><p class="no-routes-msg">Selecciona una terminal para ver las rutas</p></div>
                
                <div id="directionPanel" style="display:none; margin-top: 15px;">
                    <label class="label-light">SELECCIONA DIRECCIÓN</label>
                    <div class="direction-buttons">
                        <button class="direction-btn" data-direction="IDA"><i class="fas fa-arrow-right"></i> IDA</button>
                        <button class="direction-btn" data-direction="REGRESO"><i class="fas fa-arrow-left"></i> REGRESO</button>
                    </div>
                    <div class="route-info" id="routeInfo" style="display:none; margin-top: 12px;">
                        <div class="info-row"><span class="info-label">Origen:</span><span class="info-value" id="infoOrigen">—</span></div>
                        <div class="info-row"><span class="info-label">Destino:</span><span class="info-value" id="infoDestino">—</span></div>
                    </div>
                </div>
                <button class="btn-start-tracking" id="startTrackingBtn" disabled><i class="fas fa-play"></i> Iniciar Tracking</button>
                <button type="button" id="gpsInfoBtn" class="btn-info-circle-tiny" title="Esta página usa ubicación GPS">
                    <i class="fas fa-exclamation"></i>
                </button>
            </div>
        </div>

        <div id="trackingLivePanel" style="display:none;">
            <div class="live-tracking-panel">
                <div class="live-stats">
                    <div class="live-card"><div class="live-card-top"><span class="live-card-label">Terminal</span><i class="fas fa-location-dot"></i></div><div class="live-card-value" id="liveTerminalName">—</div></div>
                    <div class="live-card"><div class="live-card-top"><span class="live-card-label">Ruta</span><i class="fas fa-road"></i></div><div class="live-card-value" id="liveRouteNumber">—</div><div class="live-card-sub" id="liveDirection">—</div></div>
                    <div class="live-card"><div class="live-card-top"><span class="live-card-label">Usuarios</span><i class="fas fa-users"></i></div><div class="live-card-value" id="usuariosViendo">12</div><div class="live-card-sub">Viendo en vivo</div></div>
                    <div class="live-card"><div class="live-card-top"><span class="live-card-label">Estado</span><i class="fas fa-signal"></i></div><div class="live-card-value" style="color:#2ecc71;font-size:1rem;">Activo</div></div>
                </div>
                <div class="live-chat-section">
                    <div class="live-chat-title"><i class="fas fa-comments"></i> Chat en Vivo</div>
                    <div class="chat-box">
                        <div class="chat-messages" id="chatMessages"></div>
                        <div class="chat-input-container">
                            <input type="text" class="chat-input" id="chatInput" placeholder="Escribe un mensaje..." maxlength="200">
                            <button class="chat-send-btn" id="sendMessageBtn"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn-finish-tracking" id="finishTrackingBtn"><i class="fas fa-stop-circle"></i> Finalizar Tracking</button>
        </div>

        <button class="info-pill-btn" id="openInfoModalBtn" title="Información importante de privacidad">
            <i class="fas fa-info-circle"></i>
        </button>
    </aside>

    <button class="btn-open-sidebar" id="openSidebar" style="display:none;"><i class="fas fa-bars"></i></button>

    <main class="map-viewport">
        <div class="map-overlay-top">
            <div class="status-pill" id="statusPillContainer"><span class="dot" id="statusDot"></span><span id="statusText">Inactive Tracking</span></div>
            <div class="time-pill" id="liveClock">--:--</div>
        </div>
        <div id="map-tracking"></div>
    </main>
</div>

<div class="info-modal-overlay" id="infoModal">
    <div class="info-modal-card">
        <div class="info-modal-header">
            <h3><i class="fas fa-shield-alt"></i> Advertencia de Privacidad</h3>
            <button class="info-modal-close" id="closeInfoModalBtn">&times;</button>
        </div>
        <div class="info-modal-body">
            <p class="highlight-text">
                Este sistema utiliza la geolocalización en tiempo real de tu dispositivo para trazar las rutas y realizar el tracking activo en el mapa.
            </p>
            
            <h4><i class="fas fa-exclamation-triangle"></i> Recomendaciones de uso:</h4>
            <ul>
                <li><strong>Uso Autorizado:</strong> Asegúrate de contar con los permisos correspondientes de la unidad de transporte antes de iniciar la transmisión.</li>
                <li><strong>Consumo de Batería:</strong> El uso continuo del GPS en segundo plano incrementa drásticamente el consumo de energía. Mantén el dispositivo conectado a una fuente de carga si es posible.</li>
                <li><strong>Cierre de Sesión Limpio:</strong> Al terminar el recorrido, presiona obligatoriamente <em>"Finalizar Tracking"</em> para detener la captura de coordenadas y apagar el consumo del GPS.</li>
            </ul>
        </div>
    </div>
</div>

<div class="info-modal-overlay" id="confirmFinishModal">
    <div class="info-modal-card format-confirm">
        <div class="info-modal-header">
            <h3><i class="fas fa-exclamation-circle text-danger"></i> ¿Finalizar Tracking?</h3>
            <button class="info-modal-close" id="closeConfirmFinishModalBtn">&times;</button>
        </div>
        <div class="info-modal-body">
            <p class="highlight-text">
                ¿Estás seguro de que deseas terminar el recorrido? Esta acción dará por finalizado tu tracking actual en tiempo real y dejarás de transmitir tu ubicación a los usuarios.
            </p>
            <div class="confirm-actions-wrapper">
                <button class="btn-modal-back" id="cancelFinishBtn">Volver al mapa</button>
                <button class="btn-modal-terminate" id="executeFinishBtn">Sí, Finalizar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
var usuarioActual = {
    id: <?php echo $usuario_id; ?>,
    nombre: '<?php echo $usuario_nombre; ?>',
    avatar: '<?php echo $usuario_avatar; ?>'
};
</script>

<script>
$(document).ready(function() {
    // Abrir ventana emergente
    $('#openInfoModalBtn').on('click', function(e) {
        e.preventDefault();
        $('#infoModal').addClass('show');
    });

    $('#closeInfoModalBtn').on('click', function() {
        $('#infoModal').removeClass('show');
    });

    $('#infoModal').on('click', function(e) {
        if ($(e.target).is('.info-modal-overlay')) {
            $(this).removeClass('show');
        }
    });

    var trackingConfirmado = false;

    document.getElementById('finishTrackingBtn').addEventListener('click', function(event) {
        if (!trackingConfirmado) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            $('#confirmFinishModal').addClass('show');
        } else {
            trackingConfirmado = false;
        }
    }, true);

    $('#cancelFinishBtn, #closeConfirmFinishModalBtn').on('click', function() {
        $('#confirmFinishModal').removeClass('show');
    });

    $('#confirmFinishModal').on('click', function(e) {
        if ($(e.target).is('.info-modal-overlay')) {
            $(this).removeClass('show');
        }
    });

    $('#executeFinishBtn').on('click', function() {
        trackingConfirmado = true;
        $('#confirmFinishModal').removeClass('show');
        document.getElementById('finishTrackingBtn').click();
    });

    // =====================================================
    // CONTROL DEL SIDEBAR RESPONSIVE
    // =====================================================
    var $adminSidebar = $('#adminSidebar');
    var $openSidebarBtn = $('#openSidebar');
    var $toggleSidebarBtn = $('#toggleSidebar');
    var $adminLayout = $('#adminLayout');
    
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    function updateSidebarState() {
        if (isMobile()) {
            $adminSidebar.removeClass('show');
            $openSidebarBtn.show();
            $toggleSidebarBtn.hide();
            $adminLayout.removeClass('sidebar-hidden');
        } else {
            $adminSidebar.removeClass('show');
            $openSidebarBtn.hide();
            $toggleSidebarBtn.show();
        }
    }
    
    $openSidebarBtn.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $adminSidebar.addClass('show');
        $(this).hide();
    });
    
    $toggleSidebarBtn.on('click', function(e) {
        e.preventDefault();
        if (!isMobile()) {
            $adminLayout.toggleClass('sidebar-hidden');
        } else {
            $adminSidebar.removeClass('show');
            $openSidebarBtn.show();
        }
    });
    
    $(document).on('click', function(e) {
        if (isMobile() && $adminSidebar.hasClass('show')) {
            if (!$adminSidebar.is(e.target) && $adminSidebar.has(e.target).length === 0 && !$openSidebarBtn.is(e.target)) {
                $adminSidebar.removeClass('show');
                $openSidebarBtn.show();
            }
        }
    });
    
    $(window).on('resize', function() {
        updateSidebarState();
        if (typeof mapInstance !== 'undefined' && mapInstance) {
            setTimeout(function() { mapInstance.invalidateSize(); }, 300);
        }
    });
    
    $('#map-tracking').on('click', function() {
        if (isMobile() && $adminSidebar.hasClass('show')) {
            $adminSidebar.removeClass('show');
            $openSidebarBtn.show();
        }
    });
    
    updateSidebarState();
});
</script>

<script src="/TRACKING_TERMINAL/assets/js/realizar_tracking.js"></script>
</body>
</html>