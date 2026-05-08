<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Busito SV | Login</title>
    <!-- Fuentes y estilos base -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- CSS principal -->
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/login.css">
</head>
<body>

    <!-- VIDEO DE FONDO CON FILTRO AZUL ARMONIOSO -->
    <div class="video-background">
        <video autoplay muted loop playsinline id="bgVideo">
            <source src="/TRACKING_TERMINAL/assets/img/video/busespelea.mp4" type="video/mp4">
            Tu navegador no soporta videos HTML5.
        </video>
        <div class="video-overlay"></div>
        <div class="video-accent"></div>
        <!-- Botón de control de sonido -->
        <button class="sound-toggle" id="soundToggle" aria-label="Activar/desactivar sonido">
            <i class="fas fa-volume-mute"></i>
        </button>
    </div>

    <!-- Decoración adicional -->
    <div class="deco-dot deco-dot-1"></div>
    <div class="deco-dot deco-dot-2"></div>
    <div class="deco-dot deco-dot-3"></div>
    
    <div class="login-wrapper">
        <div class="glass-card">
            <!-- LOGO Y TEXTO ALINEADOS HORIZONTALMENTE -->
            <div class="brand-horizontal">
                <div class="logo-container">
                    <img src="/TRACKING_TERMINAL/assets/img/logobus.png" alt="Busito SV Logo" class="logo-img" id="systemLogo">
                </div>
                <div class="brand-text">
                    <h1>Busito <span>SV</span></h1>
                    <p><i class="fas fa-map-marked-alt"></i> Gestión de rutas en tiempo real</p>
                </div>
            </div>

            <form class="login-form" action="login_process.php" method="POST" id="loginForm">
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" name="usuario" id="usuario" autocomplete="username" placeholder=" ">
                    <label for="usuario">Usuario</label>
                </div>
                
                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" autocomplete="current-password" placeholder=" ">
                    <label for="password">Contraseña</label>
                </div>

                <button type="button" class="btn-login" id="loginBtn" onclick="location.href='/TRACKING_TERMINAL/views/layouts/menu_general.php'">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Ingresar</span>
                </button>
                
                <div class="links-row">
                    <a href="#"><i class="fas fa-user-plus"></i> Registrate</a>
                    <a href="#"><i class="fas fa-key"></i> ¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>

    <div class="tracking-element">
        <i class="fas fa-satellite-dish"></i> TERMINAL ONLINE · SEGUIMIENTO ACTIVO
    </div>

</body>
</html>