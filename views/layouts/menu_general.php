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

<!-- BANNER PARALLAX PRINCIPAL (Hero) -->
<section class="parallax-hero" id="parallaxHero">
    <div class="parallax-layer layer-bg" data-speed="0.2"></div>
    <div class="parallax-layer layer-particles" data-speed="0.4"></div>
    
    <!-- Botón de cerrar sesión en esquina superior derecha -->
    <button class="logout-btn" id="logoutBtn" onclick="window.location.href='/Tracking_Terminal/index.php'">CERRAR SESIÓN</button>
    
    <div class="hero-content">
        <div class="hero-title-wrapper">
            <img src="/TRACKING_TERMINAL/assets/img/logobus.png" alt="Busito SV Logo" class="hero-logo" onerror="this.style.display='none'">
            <h1>BUSITO <span>SV</span></h1>
        </div>
    </div>
</section>

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
            <div class="card__image-wrapper">
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
            
            <h1 class="card__title">CREAR TRACKINGS</h1>
            <p class="card__subtitle">
                Consultar los trackings y estado actual de todas las unidades activas.<br>
                detalles, analisis y comentarios sobre el trackeo.
            </p>
        </div>
        
        <div class="card__body">
            <div class="card__image-wrapper">
                <img src="/TRACKING_TERMINAL/assets/img/mano.png" alt="Ver Trackings" class="card__image card__image--vertracking">
            </div>
            
            <a href="ver_trackings.php" class="card__action">VER TRACKINGS →</a>
            <span class="card__category">CONSULTA Y DETALLES</span>
        </div>
    </div>
</div>

<script src="/TRACKING_TERMINAL/assets/js/menu_general.js"></script>
</body>
</html>