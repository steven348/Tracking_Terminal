<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Busito SV | Terminal de Control y Monitoreo</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/menu_general.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar__left">
        <img src="/TRACKING_TERMINAL/assets/img/logobus.png" alt="Busito SV Logo" class="navbar__logo" onerror="this.style.display='none'">
        <h2 class="navbar__title">BUSITO <span>SV</span></h2>
    </div>
    
    <div class="navbar__right">
        <div class="navbar__profile">
            <img src="/TRACKING_TERMINAL/assets/img/Perfil/IconoPerfil.png" alt="Perfil" class="navbar__avatar">
            <span class="navbar__username">Usuario</span>
        </div>
        <button class="navbar__logout" id="logoutBtn" onclick="window.location.href='/Tracking_Terminal/index.php'">CERRAR SESIÓN</button>
    </div>
</nav>

<!-- TARJETAS UNA AL LADO DE LA OTRA -->
<div class="cards-container">
    <!-- Tarjeta: Realizar Tracking -->
    <div class="card">
        <div class="card__header">
            <div class="card__watermark" data-watermark="Track"></div>
            
            <span class="card__price">Tiempo Real</span>
            
            <h1 class="card__title">REALIZAR TRACKING</h1>
            <p class="card__subtitle">
                Iniciar el Tracking en tiempo real de una unidad en ruta.<br>
                Geolocalización precisa, historial de rutas y alertas inteligentes para una operación eficiente.
            </p>
        </div>
        
        <div class="card__body">
            <div class="card__image-wrapper card__image-wrapper--tracking">
                <img src="/TRACKING_TERMINAL/assets/img/tracking.png" alt="Realizar Tracking" class="card__image card__image--tracking">
            </div>
            
            <a href="realizar_tracking.php" class="card__action">INICIAR SEGUIMIENTO →</a>
            <span class="card__category">MONITOREO ACTIVO</span>
        </div>
    </div>
    
    <!-- Tarjeta: Ver Trackings -->
    <div class="card">
        <div class="card__header">
            <div class="card__watermark" data-watermark="View"></div>
            
            <span class="card__price">Historial</span>
            
            <h1 class="card__title">VER TRACKINGS</h1>
            <p class="card__subtitle">
                Consultar los trackings y estado actual de todas las unidades activas.<br>
                detalles, analisis y comentarios sobre el trackeo.
            </p>
        </div>
        
        <div class="card__body">
            <div class="card__image-wrapper card__image-wrapper--center">
                <img src="/TRACKING_TERMINAL/assets/img/mano.png" alt="Ver Trackings" class="card__image card__image--vertracking">
            </div>
            
            <a href="ver_trackings.php" class="card__action">VER TRACKINGS →</a>
            <span class="card__category">CONSULTA Y DETALLES</span>
        </div>
    </div>

    <!-- Tarjeta: Panel Admin (Solo Admin) -->
    <div class="card card--admin">
        <div class="card__header">
            <div class="card__watermark" data-watermark="Admin"></div>
            
            <span class="card__price">Privado</span>
            
            <h1 class="card__title">PANEL ADMIN</h1>
            <p class="card__subtitle">
                Acceso exclusivo para administradores.<br>
                Gestión de usuarios, configuración del sistema, reportes avanzados y control total de la plataforma.
            </p>
        </div>
        
        <div class="card__body">
            <div class="card__image-wrapper card__image-wrapper--admin">
                <img src="/TRACKING_TERMINAL/assets/img/admin.png" alt="Panel Admin" class="card__image card__image--admin">
            </div>
            
            <a href="panel_admin.php" class="card__action">ACCEDER AL PANEL →</a>
            <span class="card__category">ADMINISTRACIÓN</span>
        </div>
    </div>
</div>

<script src="/TRACKING_TERMINAL/assets/js/menu_general.js"></script>
</body>
</html>