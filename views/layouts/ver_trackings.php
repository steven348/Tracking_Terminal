<?php
session_start();
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Invitado';
$usuario_avatar = substr($usuario_nombre, 0, 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busito SV - Ver Trackings</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/Ver_tracking.css">
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/tracking_styles.css">

    <style>
    /* Ajustes estructurales de acoplamiento para heredar el layout admin */
    #trackingMapView.structural-map-workspace {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        background-color: #08121e;
    }

    #trackingMapView:not([style*="display: none"]) {
        display: flex !important;
    }

    .structural-map-workspace .admin-sidebar {
        height: 100vh !important;
        display: flex;
        flex-direction: column;
    }

    .structural-map-workspace .map-viewport {
        flex-grow: 1;
        height: 100vh !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden;
    }

    #liveMapContainer {
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }

    #liveMapContainer.leaflet-container {
        display: block !important;
        padding: 0 !important;
    }

    /* Keyframes para la animación de telemetría activa */
    @keyframes telem-pulse {
        0% { transform: scale(0.9); opacity: 0.4; }
        50% { transform: scale(1.3); opacity: 1; }
        100% { transform: scale(0.9); opacity: 0.4; }
    }
    .pulse-active-dots {
        animation: telem-pulse 2s infinite ease-in-out;
        color: #00f3ff !important;
        text-shadow: 0 0 8px #00f3ff;
    }
    .static-dot {
        color: #ff9f43 !important;
        text-shadow: 0 0 8px #ff9f43;
    }

    /* --- SISTEMA DE TARJETAS ULTRA-COMPACTO --- */
    .live-trips-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
        margin-top: 10px;
    }

    .trip-mini-card {
        background: #112331 !important;
        border: 1px solid #1a3449 !important;
        border-radius: 6px !important;
        padding: 12px !important;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-sizing: border-box;
        transition: all 0.2s ease;
    }

    .trip-mini-card.active {
        border-left: 3px solid #00f3ff !important;
    }

    .trip-mini-card.delayed {
        border-left: 3px solid #ff9f43 !important;
    }

    /* Fila Principal: Datos alineados en los extremos */
    .card-row-main {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .unit-meta-block {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .unit-name-title {
        font-size: 1rem;
        font-weight: 700;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .unit-name-title i {
        font-size: 0.65rem;
    }

    .unit-plate-sub {
        font-size: 0.8rem;
        color: #8fa0b0;
        padding-left: 14px;
    }

    .unit-time-eta {
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: -0.3px;
        text-align: right;
    }

    .text-cyan { color: #00f3ff !important; }
    .text-orange { color: #ff9f43 !important; }

    /* ==========================================================================
       NUEVOS ESTILOS DE CONTROL (EVITA HOVER MASIVO Y CORTES DE TEXTO)
       ========================================================================== */
    .unidades-ruta-wrapper {
        border: 1px solid #1a3449;
        border-radius: 8px;
        padding: 16px;
        background: rgba(11, 19, 31, 0.4); 
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-sizing: border-box;
    }

    .unidades-ruta-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: 0 4px; 
    }

    .unidades-ruta-header span {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #8fa0b0;
    }

    .unidades-ruta-header i {
        color: #8fa0b0;
        font-size: 0.9rem;
    }

    .trip-mini-card {
        cursor: pointer;
        transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    .trip-mini-card:hover {
        background: #162d42 !important; 
        border-color: rgba(0, 243, 255, 0.4) !important; 
        box-shadow: 0 4px 12px rgba(0, 243, 255, 0.08); 
    }

    /* --- ESTILOS DE LOS BUSITOS EN EL MAPA --- */
    .custom-bus-marker {
        background: none !important;
        border: none !important;
    }

    .bus-marker-wrapper {
        position: relative;
        width: 32px;
        height: 32px;
        background: #112331;
        border: 2px solid #00f3ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #00f3ff;
        box-shadow: 0 0 10px rgba(0, 243, 255, 0.6);
        font-size: 13px;
        transition: transform 0.2s ease;
    }

    /* Variación naranja para buses retrasados (delayed) */
    .bus-marker-wrapper.delayed {
        border-color: #ff9f43;
        color: #ff9f43;
        box-shadow: 0 0 10px rgba(255, 159, 67, 0.6);
    }

    /* Etiqueta flotante superior con el nombre de la unidad (U-08, etc) */
    .bus-label-tooltip {
        position: absolute;
        top: -24px;
        background: rgba(8, 18, 30, 0.9);
        border: 1px solid #1a3449;
        color: #ffffff;
        padding: 2px 6px;
        font-size: 9px;
        font-weight: 700;
        border-radius: 4px;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(0,0,0,0.5);
    }

    /* Control de distribución vertical estricto para evitar desbordamientos */
.live-tracking-panel {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 70px); /* Resta la altura del header del sidebar */
    overflow: hidden;
}

/* Permitir scroll solo en el listado si hay demasiados buses */
.unidades-ruta-wrapper {
    flex-grow: 1;
    overflow-y: auto;
}

/* El chat hereda el espacio completo del contenedor cuando se muestra */
#unitChatSection {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    overflow: hidden;
    margin-top: 5px;
}

#unitChatSection .chat-box {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    background: #112331;
    border: 1px solid #1a3449;
    border-radius: 6px;
    overflow: hidden;
    height: 100%;
}

#unitChatSection .chat-messages {
    flex-grow: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: calc(100vh - 280px); /* Clave para que no empuje el input hacia abajo */
}
    </style>
</head>
<body class="tracking-admin">

    <nav class="top-nav">
        <div class="logo">
            <i class="fas fa-bus"></i> <span>Busito SV</span>
        </div>
        <div class="nav-icons">
            <a href="../layouts/menu_general.php">
                <i class="fas fa-home fa-lg"></i>
            </a>
        </div>
    </nav>

    <div id="departmentsView" class="view-segment">
        <div class="split-workspace">
            
            <section class="left-workspace">
                <h2 class="workspace-title">
                    <i class="fas fa-map-marked-alt"></i>
                    Departamentos de El Salvador
                </h2>
                
                <div class="departments-grid">
                    <div class="dept-card" data-dept="CABAÑAS">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=500" alt="Cabañas">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">Cabañas</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="SAN SALVADOR">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&q=80&w=500" alt="San Salvador">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">San Salvador</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="SAN MIGUEL">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1563261271-e00994fa51d3?auto=format&fit=crop&q=80&w=500" alt="San Miguel">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">San Miguel</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="SANTA ANA">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1518241353330-0f7941c2d9b5?auto=format&fit=crop&q=80&w=500" alt="Santa Ana">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">Santa Ana</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="CUSCATLAN">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1557223562-6c77ef16210f?auto=format&fit=crop&q=80&w=500" alt="Cuscatlán">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">Cuscatlán</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="LA LIBERTAD">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=500" alt="La Libertad">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">La Libertad</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="AHUACHAPAN">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&q=80&w=500" alt="Ahuachapán">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">Ahuachapán</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="SONSONATE">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1519003722824-194d4455a60c?auto=format&fit=crop&q=80&w=500" alt="Sonsonate">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">Sonsonate</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="CHALATENANGO">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&q=80&w=500" alt="Chalatenango">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">Chalatenango</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="LA PAZ">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1548345680-f5475ea5df84?auto=format&fit=crop&q=80&w=500" alt="La Paz">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">La Paz</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="SAN VICENTE">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=80&w=500" alt="San Vicente">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">San Vicente</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="USULUTAN">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&q=80&w=500" alt="Usulután">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">Usulután</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="MORAZAN">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&q=80&w=500" alt="Morazán">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">Morazán</h2>
                        </div>
                    </div>

                    <div class="dept-card" data-dept="LA UNION">
                        <div class="card-thumbnail">
                            <img src="https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&q=80&w=500" alt="La Unión">
                        </div>
                        <div class="card-content-block">
                            <h2 class="card-title">La Unión</h2>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="right-workspace active-routes-list" style="padding: 20px;">
                <div class="list-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <span class="text-white" id="selectedDeptTitle" style="font-weight:700; font-size:1.1rem; text-transform: uppercase; letter-spacing: 0.5px;">RUTAS DISPONIBLES</span>
                </div>
                
                <div id="routesContainer" class="routes-grid-layout" style="width: 100%;">
                    <div class="empty-state-container">
                        <div class="info-circle-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <p>Selecciona un departamento para ver las rutas disponibles.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div id="trackingMapView" class="view-segment structural-map-workspace admin-layout" style="display: none;">
        
        <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="javascript:void(0);" id="btnBackToGrid" class="btn-back-icon"><i class="fas fa-arrow-left"></i></a>
        <div class="logo"><i class="fas fa-bus"></i><span>Busito SV</span></div>
        <button class="btn-toggle-sidebar" id="toggleSidebar"><i class="fas fa-chevron-left"></i></button>
    </div>

    <div class="live-tracking-panel">
        
        <div class="live-stats" style="grid-template-columns: 1fr; gap: 12px; margin-bottom: 12px;">
            <div class="live-card">
                <div class="live-card-top">
                    <span class="live-card-label">Monitoreando</span>
                    <i class="fas fa-road text-cyan"></i>
                </div>
                <div class="live-card-value" id="trackingRouteTitle" style="font-size: 1.15rem;">Ruta</div>
                <div class="live-card-sub" style="color: #2ecc71;">
                </div>
            </div>
        </div>

        <div class="unidades-ruta-wrapper" id="unidadesRutaWrapper">
            <div class="unidades-ruta-header">
                <span>Unidades en Ruta</span>
                <i class="fas fa-bus-alt"></i>
            </div>
            
            <div id="liveTripsContainer" class="live-trips-grid">
                <div class="trip-mini-card active" data-unit="U-08">
                    <div class="card-row-main">
                        <div class="unit-meta-block">
                            <span class="unit-name-title">
                                <i class="fas fa-circle pulse-active-dots"></i> U-08
                            </span>
                        </div>
                    </div>
                </div>
                <div class="trip-mini-card delayed" data-unit="U-12">
                    <div class="card-row-main">
                        <div class="unit-meta-block">
                            <span class="unit-name-title">
                                <i class="fas fa-circle static-dot"></i> U-12
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="live-chat-section" id="unitChatSection" style="display: none;">
            <div class="live-chat-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-comments"></i> Chat: <strong id="chatActiveUnitName">U-00</strong></span>
                <button id="btnCloseChat" style="background:none; border:none; color:#8fa0b0; cursor:pointer;"><i class="fas fa-times"></i></button>
            </div>
            <div class="chat-box">
                <div class="chat-messages" id="chatMessages">
                    </div>
                <div class="chat-input-container">
                    <input type="text" class="chat-input" id="chatInput" placeholder="Escribe un mensaje..." maxlength="200">
                    <button class="chat-send-btn" id="sendMessageBtn"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>

    </div>
</aside>

        <button class="btn-open-sidebar" id="openSidebar" style="display:none;"><i class="fas fa-bars"></i></button>

        <main class="map-viewport">
            <div id="liveMapContainer" class="map-engine-placeholder">
                <i class="fas fa-map-marked-alt fa-3x placeholder-icon"></i>
                <p>Cargando visor cartográfico...</p>
            </div>
        </main>
    </div>

    <div id="confirmTrackingModal" class="custom-modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-header-icon">
                <i class="fas fa-satellite-dish"></i>
            </div>
            <h3 class="modal-heading">¿Monitorear esta ruta?</h3>
            <p class="modal-description">¿Deseas verificar si existen trackeos disponibles en tiempo real para la <span id="modalRouteName" class="highlight-text"></span>?</p>
            <div class="modal-footer-actions">
                <button id="btnCancelTracking" class="btn-modal btn-modal-secondary">Cancelar</button>
                <button id="btnConfirmTracking" class="btn-modal btn-modal-primary">Ver Trackings</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="/TRACKING_TERMINAL/assets/js/ver_tracking.js"></script>
</body>
</html>