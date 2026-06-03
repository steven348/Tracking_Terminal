<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

$alias       = $_SESSION['usuario_alias'] ?? $_SESSION['nombre'];
$foto_perfil = $_SESSION['foto'];
$iniciales   = strtoupper(substr($alias, 0, 2));
$colores     = ['#2ecc71', '#3498db', '#9b59b6', '#e67e22', '#e74c3c', '#1abc9c', '#f1c40f'];
$bg_color    = $colores[ord($alias[0]) % count($colores)];
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
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }

        /* Navbar fija */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 9999;
            height: 56px;
        }

        /* Contenedor de vistas debajo de la navbar */
        #app-container {
            position: fixed;
            top: 56px; left: 0; right: 0; bottom: 0;
        }

        /* Vista menú (tarjetas) */
        #view-menu {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            overflow-y: auto;
            background: #071A2D;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        /* Iframes */
        .view-frame {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            border: none;
            background: #071A2D;
        }

        /* Control de visibilidad */
        .vista-oculta  { display: none !important; }
        .vista-activa  { display: block !important; }
        #view-menu.vista-activa { display: flex !important; }

        /* Indicador tracking activo en navbar */
        .tracking-pill {
            display: none;
            align-items: center;
            gap: 7px;
            padding: 5px 13px;
            border-radius: 30px;
            background: rgba(46,204,113,0.12);
            border: 1px solid rgba(46,204,113,0.35);
            color: #2ecc71;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            margin-right: 10px;
            transition: background 0.2s;
        }
        .tracking-pill.visible { display: flex; }
        .tracking-pill:hover   { background: rgba(46,204,113,0.22); }

        .tracking-pill .tp-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #2ecc71;
            box-shadow: 0 0 8px #2ecc71;
            animation: tp-pulse 1.5s infinite;
        }

        @keyframes tp-pulse {
            0%,100% { transform: scale(1);    opacity: 1; }
            50%     { transform: scale(1.35); opacity: 0.5; }
        }

        .avatar-initials {
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 800; font-size: 15px;
            background-color: <?php echo $bg_color; ?>;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .navbar__profile { cursor: pointer; transition: opacity 0.2s; }
        .navbar__profile:hover { opacity: 0.8; }
        .navbar__title { cursor: pointer; }
    </style>
</head>
<body>

<!-- ══ NAVBAR FIJA ══ -->
<nav class="navbar">
    <div class="navbar__left">
        <img src="/TRACKING_TERMINAL/assets/img/logobus.png" alt="Logo"
             class="navbar__logo" onerror="this.style.display='none'">
        <h2 class="navbar__title" onclick="mostrarVista('menu')">
            BUSITO <span>SV</span>
        </h2>
    </div>

    <div class="navbar__right">

        <!-- Indicador tracking activo — aparece solo cuando hay tracking corriendo -->
        <div class="tracking-pill" id="trackingPill"
             onclick="mostrarVista('tracking')"
             title="Tracking activo — clic para volver">
            <span class="tp-dot"></span>
            TRACKING ACTIVO
        </div>

        <div class="navbar__profile" onclick="mostrarVista('perfil')">
            <?php if ($foto_perfil && $foto_perfil != 'default.png'): ?>
                <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $foto_perfil; ?>"
                     alt="Perfil" class="navbar__avatar">
            <?php else: ?>
                <div class="avatar-initials"><?php echo $iniciales; ?></div>
            <?php endif; ?>
            <span class="navbar__username"><?php echo htmlspecialchars($alias); ?></span>
        </div>

        <button class="navbar__logout" onclick="handleLogout()">
            CERRAR SESIÓN
        </button>
    </div>
</nav>

<!-- ══ CONTENEDOR DE VISTAS ══ -->
<div id="app-container">

    <!-- MENÚ (tarjetas) — siempre en el DOM, nunca en iframe -->
    <div id="view-menu" class="vista-activa">
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
                        <img src="/TRACKING_TERMINAL/assets/img/tracking.png"
                             alt="Realizar Tracking"
                             class="card__image card__image--tracking">
                    </div>
                    <button class="card__action" onclick="mostrarVista('tracking')">
                        INICIAR SEGUIMIENTO →
                    </button>
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
                        <img src="/TRACKING_TERMINAL/assets/img/mano.png"
                             alt="Ver Trackings"
                             class="card__image card__image--vertracking">
                    </div>
                    <button class="card__action" onclick="mostrarVista('ver_trackings')">
                        VER TRACKINGS →
                    </button>
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
                        <img src="/TRACKING_TERMINAL/assets/img/admin.png"
                             alt="Panel Admin"
                             class="card__image card__image--admin">
                    </div>
                    <button class="card__action" onclick="mostrarVista('admin')">
                        ACCEDER AL PANEL →
                    </button>
                    <span class="card__category">ADMINISTRACIÓN</span>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!--
        TRACKING — iframe que NUNCA se destruye una vez cargado.
        El tracking sigue corriendo aunque cambies de vista.
    -->
    <iframe id="view-tracking"
            class="view-frame vista-oculta"
            src=""
            data-src="/TRACKING_TERMINAL/views/layouts/realizar_tracking.php">
    </iframe>

    <!-- VER TRACKINGS -->
    <iframe id="view-ver_trackings"
            class="view-frame vista-oculta"
            src=""
            data-src="/TRACKING_TERMINAL/views/layouts/ver_trackings.php">
    </iframe>

    <!-- PERFIL -->
    <iframe id="view-perfil"
            class="view-frame vista-oculta"
            src=""
            data-src="/TRACKING_TERMINAL/views/layouts/perfil.php">
    </iframe>

    <?php if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1): ?>
    <!-- ADMIN -->
    <iframe id="view-admin"
            class="view-frame vista-oculta"
            src=""
            data-src="/TRACKING_TERMINAL/views/admin/vista_admin.php">
    </iframe>
    <?php endif; ?>

</div>

<script src="/TRACKING_TERMINAL/assets/js/menu_general.js"></script>
<script>

var vistaActual  = 'menu';
var trackingVivo = false;

// ── CAMBIAR VISTA ──────────────────────────────────
function mostrarVista(nombre) {

    // Ocultar vista actual
    var actual = document.getElementById('view-' + vistaActual);
    if (actual) {
        actual.classList.remove('vista-activa');
        actual.classList.add('vista-oculta');
    }

    var siguiente = document.getElementById('view-' + nombre);
    if (!siguiente) return;

    // Lazy-load: cargar el iframe solo la primera vez
    if (siguiente.tagName === 'IFRAME' && !siguiente.getAttribute('src')) {
        siguiente.setAttribute('src', siguiente.getAttribute('data-src'));
    }

    siguiente.classList.remove('vista-oculta');
    siguiente.classList.add('vista-activa');
    vistaActual = nombre;

    // Forzar resize del mapa si se vuelve al tracking
    if (nombre === 'tracking') {
        setTimeout(function() {
            try {
                siguiente.contentWindow.dispatchEvent(new Event('resize'));
                if (siguiente.contentWindow.mapInstance) {
                    siguiente.contentWindow.mapInstance.invalidateSize();
                }
            } catch(e) {}
        }, 200);
    }
}

// ── MENSAJES DESDE LOS IFRAMES ─────────────────────
window.addEventListener('message', function(evt) {
    if (!evt.data || !evt.data.tipo) return;

    switch (evt.data.tipo) {

        case 'TRACKING_INICIADO':
            trackingVivo = true;
            document.getElementById('trackingPill').classList.add('visible');
            break;

        case 'TRACKING_FINALIZADO':
            trackingVivo = false;
            document.getElementById('trackingPill').classList.remove('visible');
            break;

        case 'IR_A_MENU':
            mostrarVista('menu');
            break;

        case 'IR_A_VISTA':
            if (evt.data.vista) mostrarVista(evt.data.vista);
            break;
    }
});

// ── LOGOUT ─────────────────────────────────────────
function handleLogout() {
    if (trackingVivo) {
        if (!confirm('Hay un tracking activo. ¿Seguro que deseas cerrar sesión?')) return;
    }
    window.location.href = '/TRACKING_TERMINAL/views/logout.php';
}

// Advertir si cierran la pestaña con tracking activo
window.addEventListener('beforeunload', function(e) {
    if (trackingVivo) {
        e.preventDefault();
        e.returnValue = '';
    }
});

</script>
</body>
</html>