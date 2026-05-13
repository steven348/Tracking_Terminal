<?php
session_start();

// Si no hay sesión, mandarlo al login para proteger la ruta
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

// Usamos el nombre de usuario (el alias) que guardamos en la sesión al loguearnos
// Nota: Asegúrate que en tu AuthController guardaste $_SESSION['usuario_alias'] = $u['nombre_usuario'];
$alias = $_SESSION['usuario_alias'] ?? $_SESSION['nombre']; 
$foto_perfil = $_SESSION['foto']; 

// Lógica para las iniciales basada en el nombre de usuario (nickname)
// Si el nombre de usuario es "DiegoC", las iniciales serán "DI"
$iniciales = strtoupper(substr($alias, 0, 2));

// Color de fondo aleatorio basado en el alias
$colores = ['#2ecc71', '#3498db', '#9b59b6', '#e67e22', '#e74c3c', '#1abc9c', '#f1c40f'];
$color_index = ord($alias[0]) % count($colores);
$bg_color = $colores[$color_index];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Busito SV | Terminal de Control y Monitoreo</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/menu_general.css">
    
    <style>
        .avatar-initials {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 15px;
            background-color: <?php echo $bg_color; ?>;
            border: 2px solid rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar__left">
        <img src="/TRACKING_TERMINAL/assets/img/logobus.png" alt="Busito SV Logo" class="navbar__logo" onerror="this.style.display='none'">
        <h2 class="navbar__title">BUSITO <span>SV</span></h2>
    </div>
    
    <div class="navbar__right">
        <div class="navbar__profile">
            <?php if ($foto_perfil && $foto_perfil != 'default.png'): ?>
                <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $foto_perfil; ?>" alt="Perfil" class="navbar__avatar">
            <?php else: ?>
                <div class="avatar-initials"><?php echo $iniciales; ?></div>
            <?php endif; ?>
            
            <span class="navbar__username"><?php echo htmlspecialchars($alias); ?></span>
        </div>
        <button class="navbar__logout" id="logoutBtn" onclick="window.location.href='/TRACKING_TERMINAL/views/logout.php'">
            CERRAR SESIÓN
        </button>
    </div>
</nav>

<div class="cards-container">
    <div class="card">
        <div class="card__header">
            <div class="card__watermark" data-watermark="Track"></div>
            <span class="card__price">Tiempo Real</span>
            <h1 class="card__title">REALIZAR TRACKING</h1>
            <p class="card__subtitle">
                Iniciar el Tracking en tiempo real de una unidad en ruta.<br>
                Geolocalización precisa, historial de rutas y alertas inteligentes.
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
    
    <div class="card">
        <div class="card__header">
            <div class="card__watermark" data-watermark="View"></div>
            <span class="card__price">Historial</span>
            <h1 class="card__title">VER TRACKINGS</h1>
            <p class="card__subtitle">
                Consultar los trackings y estado actual de todas las unidades activas.<br>
                Detalles y análisis de operación.
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

    <?php if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1): ?>
    <div class="card card--admin">
        <div class="card__header">
            <div class="card__watermark" data-watermark="Admin"></div>
            <span class="card__price">Privado</span>
            <h1 class="card__title">PANEL ADMIN</h1>
            <p class="card__subtitle">
                Gestión de usuarios y control total de la plataforma Busito SV.
            </p>
        </div>
        <div class="card__body">
            <div class="card__image-wrapper card__image-wrapper--admin">
                <img src="/TRACKING_TERMINAL/assets/img/admin.png" alt="Panel Admin" class="card__image card__image--admin">
            </div>
            <a href="vista_admin.php" class="card__action">ACCEDER AL PANEL →</a>
            <span class="card__category">ADMINISTRACIÓN</span>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="/TRACKING_TERMINAL/assets/js/menu_general.js"></script>
</body>
</html>