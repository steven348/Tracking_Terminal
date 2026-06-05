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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Busito SV - Ver Trackings</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/tracking_styles.css">

    <style>
        /* =====================================================
           RESET Y ESTILOS GLOBALES
        ===================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0f1a;
            overflow-x: hidden;
        }

        /* =====================================================
           TOP NAVIGATION
        ===================================================== */
        .top-nav {
            background: linear-gradient(135deg, #0a1a2a, #06121e);
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #1a3449;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top-nav .logo {
            font-size: 1.4rem;
            font-weight: 800;
            color: #00f3ff;
            text-shadow: 0 0 10px rgba(0, 243, 255, 0.5);
        }

        .top-nav .logo i {
            margin-right: 8px;
        }

        .top-nav .nav-icons a {
            color: #8fa0b0;
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .top-nav .nav-icons a:hover {
            color: #00f3ff;
            transform: scale(1.1);
        }

        /* =====================================================
           DEPARTMENTS VIEW
        ===================================================== */
        .view-segment {
            width: 100%;
            min-height: calc(100vh - 60px);
        }

        .split-workspace {
            display: flex;
            gap: 0;
            height: calc(100vh - 60px);
        }

        .left-workspace {
            flex: 2;
            padding: 24px;
            overflow-y: auto;
            background: #0d1424;
        }

        .right-workspace {
            flex: 1;
            background: #0a1220;
            border-left: 1px solid #1a3449;
            overflow-y: auto;
        }

        .workspace-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .workspace-title i {
            color: #00f3ff;
        }

        /* Grid de departamentos */
        .departments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .dept-card {
            background: linear-gradient(135deg, #112331, #0a1a2a);
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #1a3449;
        }

        .dept-card:hover {
            transform: translateY(-5px);
            border-color: #00f3ff;
            box-shadow: 0 8px 25px rgba(0, 243, 255, 0.15);
        }

        .dept-card.active {
            border-color: #00f3ff;
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.3);
        }

        .card-thumbnail {
            width: 100%;
            height: 120px;
            overflow: hidden;
        }

        .card-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .dept-card:hover .card-thumbnail img {
            transform: scale(1.05);
        }

        .card-content-block {
            padding: 12px;
            text-align: center;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: 0.5px;
        }

        /* =====================================================
           GRID DE RUTAS - RESPONSIVE
        ===================================================== */
        .routes-grid-layout {
            width: 100%;
            padding: 0 10px;
        }

        .routes-grid-wrapper {
            display: grid !important;
            grid-template-columns: repeat(6, 1fr) !important;
            gap: 12px !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 10px 0 !important;
        }

        .route-square {
            background: linear-gradient(135deg, #112331, #0a1a2a);
            border: 1px solid #1a3449;
            border-radius: 10px;
            padding: 12px 6px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex !important;
            align-items: center;
            justify-content: center;
            min-height: 55px;
            width: 100%;
            box-sizing: border-box;
        }

        .route-square:hover {
            background: #1a3449;
            border-color: #00f3ff;
            transform: translateY(-2px);
        }

        .route-square.active {
            background: linear-gradient(135deg, #00c2c7, #0099a0);
            border-color: #fff;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 194, 199, 0.3);
        }

        /* =====================================================
           MAPA Y SIDEBAR
        ===================================================== */
        #trackingMapView {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background-color: #08121e;
            display: none;
        }

        #trackingMapView.active {
            display: flex !important;
        }

        .admin-sidebar {
            width: 380px;
            min-width: 280px;
            background: linear-gradient(180deg, #0a1a2a, #06121e);
            border-right: 1px solid #1a3449;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: transform 0.3s ease;
            z-index: 100;
            position: relative;
            overflow-y: auto;
        }

        .map-viewport {
            flex: 1;
            height: 100vh !important;
            min-width: 300px;
            position: relative;
            background: #08121e;
        }

        #liveMapContainer {
            width: 100% !important;
            height: 100% !important;
            position: relative !important;
            background: #08121e;
        }

        #map-tracking-engine {
            width: 100% !important;
            height: 100% !important;
            position: absolute !important;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #08121e;
        }

        .leaflet-container {
            background: #08121e !important;
            width: 100% !important;
            height: 100% !important;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid #1a3449;
        }

        .logo {
            font-size: 1.2rem;
            font-weight: 800;
            color: #00f3ff;
        }

        .logo i {
            margin-right: 8px;
        }

        .btn-back-icon {
            color: #8fa0b0;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-back-icon:hover {
            color: #00f3ff;
            transform: translateX(-3px);
        }

        .btn-toggle-sidebar {
            background: none;
            border: none;
            color: #8fa0b0;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-toggle-sidebar:hover {
            color: #00f3ff;
        }

        .btn-open-sidebar {
            position: fixed;
            left: 16px;
            top: 16px;
            background: #112331;
            border: 1px solid #00f3ff;
            border-radius: 8px;
            padding: 10px 14px;
            color: #00f3ff;
            cursor: pointer;
            z-index: 200;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            display: none;
        }

        .btn-open-sidebar:hover {
            background: #00f3ff;
            color: #112331;
        }

        /* =====================================================
           PANEL DE TRACKING EN VIVO
        ===================================================== */
        .live-tracking-panel {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .live-stats {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .live-card {
            background: #112331;
            border-radius: 12px;
            padding: 14px;
            transition: all 0.3s ease;
            border: 1px solid #1a3449;
        }

        .live-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 243, 255, 0.1);
            border-color: #00f3ff;
        }

        .live-card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .live-card-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #8fa0b0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .live-card-value {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        .live-card-sub {
            font-size: 0.7rem;
            color: #8fa0b0;
            margin-top: 4px;
        }

        .text-cyan {
            color: #00f3ff;
        }

        .text-orange {
            color: #ff9f43;
        }

        /* =====================================================
           CHAT EN VIVO
        ===================================================== */
        .live-chat-section {
            margin-top: auto;
            border-top: 1px solid #1a3449;
            padding-top: 12px;
            display: flex;
            flex-direction: column;
        }

        .live-chat-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #8fa0b0;
            margin-bottom: 10px;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-box {
            background: #0a1220;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 280px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .chat-message {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .chat-message.conductor .chat-avatar {
            background: #00c2c7;
        }

        .chat-message.conductor .chat-bubble {
            background: rgba(0, 194, 199, 0.15);
            border-left: 2px solid #00c2c7;
        }

        .chat-avatar {
            width: 32px;
            height: 32px;
            background: #1a3449;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            flex-shrink: 0;
        }

        .chat-body {
            flex: 1;
        }

        .chat-meta {
            display: flex;
            gap: 8px;
            margin-bottom: 4px;
            font-size: 10px;
        }

        .chat-user {
            color: #8fa0b0;
            font-weight: 500;
        }

        .chat-time {
            color: #5a6e7e;
        }

        .chat-bubble {
            background: #112331;
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #e0e0e0;
            word-wrap: break-word;
            line-height: 1.4;
        }

        .chat-input-container {
            display: flex;
            padding: 10px;
            background: #0a1220;
            border-top: 1px solid #1a3449;
            gap: 8px;
        }

        .chat-input {
            flex: 1;
            background: #112331;
            border: 1px solid #1a3449;
            border-radius: 20px;
            padding: 8px 15px;
            color: #fff;
            font-size: 12px;
            outline: none;
            transition: all 0.3s ease;
        }

        .chat-input:focus {
            border-color: #00f3ff;
        }

        .chat-input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .chat-send-btn {
            background: #00c2c7;
            border: none;
            border-radius: 50%;
            width: 34px;
            height: 34px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-send-btn:hover:not(:disabled) {
            background: #0099a0;
            transform: scale(1.05);
        }

        .chat-send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .chat-system-msg {
            text-align: center;
            padding: 8px;
            font-size: 11px;
            color: #5a6e7e;
            font-style: italic;
        }

        /* =====================================================
           MODAL DE CONFIRMACIÓN
        ===================================================== */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .custom-modal-overlay.active {
            display: flex;
        }

        .modal-card {
            background: linear-gradient(135deg, #112331, #0a1a2a);
            border: 1px solid #00f3ff;
            border-radius: 20px;
            padding: 28px;
            max-width: 380px;
            width: 90%;
            text-align: center;
        }

        .modal-header-icon {
            width: 60px;
            height: 60px;
            background: rgba(0, 243, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .modal-header-icon i {
            font-size: 28px;
            color: #00f3ff;
        }

        .modal-heading {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 12px;
        }

        .modal-description {
            font-size: 0.85rem;
            color: #8fa0b0;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .highlight-text {
            color: #00f3ff;
            font-weight: 600;
        }

        .modal-footer-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-modal {
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-modal-secondary {
            background: #1a3449;
            color: #8fa0b0;
        }

        .btn-modal-secondary:hover {
            background: #2a455e;
            color: #fff;
        }

        .btn-modal-primary {
            background: linear-gradient(135deg, #00c2c7, #0099a0);
            color: #fff;
        }

        .btn-modal-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 194, 199, 0.4);
        }

        /* =====================================================
           ESTILOS DEL BUS EN MOVIMIENTO
        ===================================================== */
        .custom-bus-marker {
            background: none !important;
            border: none !important;
        }

        .bus-marker-wrapper {
            position: relative;
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #112331, #0a1a2a);
            border: 2.5px solid #00f3ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00f3ff;
            font-size: 20px;
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.6);
            transition: transform 0.2s ease;
            cursor: pointer;
        }

        .bus-marker-wrapper i {
            animation: busShake 0.5s ease-in-out infinite;
        }

        @keyframes busShake {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(2px); }
        }

        .bus-label-tooltip {
            position: absolute;
            top: -32px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.95);
            border: 1px solid #00f3ff;
            color: #00f3ff;
            padding: 3px 10px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 20px;
            white-space: nowrap;
            font-family: monospace;
            letter-spacing: 0.5px;
            z-index: 10;
        }

        .bus-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0, 243, 255, 0.3);
            border-radius: 50%;
            animation: busPulse 1.5s infinite;
            z-index: -1;
        }

        @keyframes busPulse {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(2); opacity: 0; }
        }

        /* =====================================================
           ESTILOS DEL USUARIO CON AVATAR
        ===================================================== */
        .custom-user-marker {
            background: none !important;
            border: none !important;
        }

        .user-location-marker {
            position: relative;
            width: 44px;
            height: 44px;
            cursor: pointer;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border: 2px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: white;
            box-shadow: 0 0 15px rgba(46, 204, 113, 0.8);
            z-index: 2;
            position: relative;
            text-transform: uppercase;
        }

        .user-label {
            position: absolute;
            bottom: -26px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: #2ecc71;
            padding: 3px 10px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 20px;
            white-space: nowrap;
            font-family: monospace;
            border: 1px solid #2ecc71;
            z-index: 1;
        }

        .pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            background: #2ecc71;
            border-radius: 50%;
            opacity: 0.4;
            transform: translate(-50%, -50%);
            animation: userPulse 2s infinite;
            z-index: 0;
        }

        @keyframes userPulse {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.5; }
            100% { transform: translate(-50%, -50%) scale(2.2); opacity: 0; }
        }

        /* =====================================================
           TARJETA DE DISTANCIA
        ===================================================== */
        #distanciaAlBus {
            background: linear-gradient(135deg, #0a1a2a, #06121e);
            border-left: 3px solid #2ecc71;
        }

        #distanciaAlBus .live-card-value {
            font-size: 1.3rem !important;
            font-weight: 800;
            color: #2ecc71;
        }

        /* =====================================================
           UTILITIES
        ===================================================== */
        .no-routes-msg, .empty-state-container {
            text-align: center;
            padding: 40px;
            color: #5a6e7e;
            font-size: 0.85rem;
        }

        .error-state {
            text-align: center;
            padding: 40px;
            color: #e74c3c;
        }

        .loading-routes {
            text-align: center;
            padding: 40px;
            color: #fff;
        }

        .loading-routes i {
            color: #00f3ff;
            margin-bottom: 10px;
        }

        .info-circle-icon {
            font-size: 2rem;
            color: #1a3449;
            margin-bottom: 10px;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0a1220;
        }

        ::-webkit-scrollbar-thumb {
            background: #1a3449;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #00f3ff;
        }

        /* =====================================================
           RESPONSIVE - MÓVIL
        ===================================================== */
        
        @media (max-width: 992px) {
            .routes-grid-wrapper {
                grid-template-columns: repeat(4, 1fr) !important;
            }
        }

        @media (max-width: 768px) {
            .split-workspace {
                flex-direction: column !important;
                height: auto !important;
            }
            
            .left-workspace {
                flex: none !important;
                padding: 16px !important;
                max-height: 50vh !important;
                overflow-y: auto !important;
            }
            
            .right-workspace {
                flex: none !important;
                border-left: none !important;
                border-top: 1px solid #1a3449 !important;
                padding: 16px !important;
                max-height: 50vh !important;
                overflow-y: auto !important;
            }
            
            .workspace-title {
                font-size: 1.2rem !important;
                margin-bottom: 16px !important;
            }
            
            .departments-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px !important;
            }
            
            .card-thumbnail {
                height: 100px !important;
            }
            
            .card-title {
                font-size: 0.85rem !important;
            }
            
            .routes-grid-wrapper {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 8px !important;
            }
            
            .route-square {
                padding: 8px 4px !important;
                font-size: 0.7rem !important;
                min-height: 45px !important;
            }
            
            .top-nav {
                padding: 8px 16px !important;
            }
            
            .top-nav .logo {
                font-size: 1.1rem !important;
            }
            
            .top-nav .nav-icons a {
                font-size: 1rem !important;
            }
            
            .admin-sidebar {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 280px !important;
                height: 100vh !important;
                z-index: 2000 !important;
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease !important;
                box-shadow: none !important;
            }
            
            .admin-sidebar.show {
                transform: translateX(0) !important;
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.5) !important;
            }
            
            .btn-open-sidebar {
                display: flex !important;
                position: fixed !important;
                top: 70px !important;
                left: 12px !important;
                z-index: 1000 !important;
                width: 40px !important;
                height: 40px !important;
                background: rgba(17, 35, 49, 0.95) !important;
                border: 1px solid #00f3ff !important;
                border-radius: 50% !important;
                color: #00f3ff !important;
                font-size: 1rem !important;
                backdrop-filter: blur(8px) !important;
            }
            
            .btn-toggle-sidebar {
                display: none !important;
            }
            
            .sidebar-header {
                padding: 12px !important;
            }
            
            .sidebar-header .logo {
                font-size: 1rem !important;
            }
            
            .live-tracking-panel {
                padding: 12px !important;
                gap: 12px !important;
            }
            
            .live-stats {
                gap: 8px !important;
            }
            
            .live-card {
                padding: 10px !important;
            }
            
            .live-card-value {
                font-size: 0.9rem !important;
            }
            
            .chat-box {
                height: 220px !important;
            }
            
            .map-viewport {
                width: 100% !important;
                min-width: auto !important;
            }
            
            .map-overlay-top {
                top: 12px !important;
                left: 12px !important;
                gap: 8px !important;
            }
            
            .status-pill, .time-pill {
                padding: 6px 12px !important;
                font-size: 0.7rem !important;
            }
            
            .modal-card {
                padding: 20px !important;
                margin: 16px !important;
            }
            
            .modal-heading {
                font-size: 1.1rem !important;
            }
            
            .modal-description {
                font-size: 0.8rem !important;
            }
            
            .btn-modal {
                padding: 8px 16px !important;
                font-size: 0.8rem !important;
            }
            
            .bus-marker-wrapper {
                width: 36px !important;
                height: 36px !important;
                font-size: 16px !important;
            }
            
            .user-location-marker {
                width: 36px !important;
                height: 36px !important;
            }
            
            .user-avatar {
                width: 32px !important;
                height: 32px !important;
                font-size: 14px !important;
            }
            
            .bus-label-tooltip, .user-label {
                font-size: 9px !important;
                padding: 2px 6px !important;
                top: -28px !important;
            }
        }
        
        @media (max-width: 480px) {
            .departments-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px !important;
            }
            
            .routes-grid-wrapper {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            
            .admin-sidebar {
                width: 100% !important;
            }
            
            .btn-open-sidebar {
                top: 65px !important;
                left: 10px !important;
                width: 36px !important;
                height: 36px !important;
                font-size: 0.9rem !important;
            }
            
            .chat-box {
                height: 200px !important;
            }
            
            .chat-bubble {
                font-size: 0.7rem !important;
                padding: 6px 10px !important;
            }
        }
        
        @media (max-width: 768px) and (orientation: landscape) {
            .admin-sidebar {
                width: 260px !important;
            }
            
            .chat-box {
                height: 180px !important;
            }
            
            .live-stats {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
            }
            
            .left-workspace, .right-workspace {
                max-height: 60vh !important;
            }
        }
    </style>
</head>
<body class="tracking-admin">

    <!-- Top Navigation -->
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

    <!-- Vista de Departamentos -->
    <div id="departmentsView" class="view-segment">
        <div class="split-workspace">
            <section class="left-workspace" style="overflow-y: auto;">
                <h2 class="workspace-title">
                    <i class="fas fa-map-marked-alt"></i>
                    Departamentos de El Salvador
                </h2>
                <div class="departments-grid" id="departmentsGrid">
                    <!-- Los departamentos se generarán con JavaScript -->
                </div>
            </section>

            <aside class="right-workspace" style="padding: 20px; overflow-y: auto;">
                <div class="list-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <span class="text-white" id="selectedDeptTitle" style="font-weight:700; font-size:1.1rem; text-transform: uppercase; letter-spacing: 0.5px;">RUTAS DISPONIBLES</span>
                </div>
                <div id="routesContainer" class="routes-grid-layout" style="width: 100%; overflow-y: auto;">
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

    <!-- Vista de Tracking en Vivo -->
    <div id="trackingMapView">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="javascript:void(0);" id="btnBackToGrid" class="btn-back-icon"><i class="fas fa-arrow-left"></i></a>
                <div class="logo"><i class="fas fa-bus"></i><span>Busito SV</span></div>
                <button class="btn-toggle-sidebar" id="toggleSidebar"><i class="fas fa-chevron-left"></i></button>
            </div>

            <div class="live-tracking-panel">
                <div class="live-stats">
                    <div class="live-card">
                        <div class="live-card-top">
                            <span class="live-card-label">MONITORANDO</span>
                            <i class="fas fa-road text-cyan"></i>
                        </div>
                        <div class="live-card-value" id="trackingRouteTitle" style="font-size: 1.15rem;">Selecciona una ruta</div>
                        <div class="live-card-sub" id="trackingRouteStatus">Esperando conexión...</div>
                    </div>
                </div>

                <div class="live-card" id="progressCard" style="display: none;">
                    <div class="live-card-top">
                        <span class="live-card-label">PROGRESO DEL VIAJE</span>
                        <i class="fas fa-chart-line text-cyan"></i>
                    </div>
                    <div class="live-card-value" id="progressValue">0%</div>
                    <div class="live-card-sub" id="progressDetail">Iniciando recorrido...</div>
                </div>

                <div class="live-card">
                    <div class="live-card-top">
                        <span class="live-card-label">ESTADO DEL TRACKING</span>
                        <i class="fas fa-signal"></i>
                    </div>
                    <div class="live-card-value" id="trackingStatus" style="color: #ff9f43; font-size: 0.9rem;">
                        <i class="fas fa-circle" style="font-size: 0.6rem;"></i> Esperando bus...
                    </div>
                    <div class="live-card-sub">Última actualización: <span id="lastUpdate">--:--:--</span></div>
                </div>

                <div class="live-chat-section">
                    <div class="live-chat-title">
                        <span><i class="fas fa-comments"></i> CHAT CON EL CONDUCTOR</span>
                        <span style="font-size: 10px; color: #ff9f43;" id="chatStatus"><i class="fas fa-circle"></i> Desconectado</span>
                    </div>
                    <div class="chat-box">
                        <div class="chat-messages" id="chatMessages">
                            <div class="chat-system-msg">💬 Selecciona una ruta para comenzar a chatear con el conductor</div>
                        </div>
                        <div class="chat-input-container">
                            <input type="text" class="chat-input" id="chatInput" placeholder="Escribe un mensaje para el conductor..." maxlength="200" disabled>
                            <button class="chat-send-btn" id="sendMessageBtn" disabled><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <button class="btn-open-sidebar" id="openSidebar"><i class="fas fa-bars"></i></button>

        <main class="map-viewport">
            <div id="liveMapContainer" style="width: 100%; height: 100%; position: relative; background: #08121e;">
                <!-- El mapa se creará dinámicamente aquí -->
            </div>
        </main>
    </div>

    <!-- Modal de Confirmación -->
    <div id="confirmTrackingModal" class="custom-modal-overlay">
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
    
    <script>
        var usuarioActual = {
            id: <?php echo $usuario_id; ?>,
            nombre: '<?php echo addslashes($usuario_nombre); ?>',
            avatar: '<?php echo $usuario_avatar; ?>'
        };
    </script>
    
    <script>
    $(document).ready(function() {
        // Control del sidebar responsive para ver_tracking
        var $adminSidebar = $('#adminSidebar');
        var $openSidebarBtn = $('#openSidebar');
        var $toggleSidebarBtn = $('#toggleSidebar');
        
        function isMobile() {
            return window.innerWidth <= 768;
        }
        
        function updateSidebarState() {
            if (isMobile()) {
                $adminSidebar.removeClass('show');
                $openSidebarBtn.show();
                $toggleSidebarBtn.hide();
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
                $('#trackingMapView').toggleClass('sidebar-hidden');
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
            if (typeof mapaInstance !== 'undefined' && mapaInstance) {
                setTimeout(function() { mapaInstance.invalidateSize(); }, 300);
            }
        });
        
        updateSidebarState();
        
        $(document).on('click', '#map-tracking-engine', function() {
            if (isMobile() && $adminSidebar.hasClass('show')) {
                $adminSidebar.removeClass('show');
                $openSidebarBtn.show();
            }
        });
        
        // Asegurar scroll en contenedores
        $('.right-workspace, .left-workspace, #routesContainer').css('overflow-y', 'auto');
    });
    </script>
    
    <script src="/TRACKING_TERMINAL/assets/js/ver_tracking.js"></script>
</body>
</html>