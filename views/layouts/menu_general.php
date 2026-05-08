<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busito SV - Tracking Terminal</title>
    <!-- Vinculación del archivo CSS -->
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/menu_general.css">
</head>
<body>

    <div class="container">
        <header class="header">
            <h1>BUSITO <span>SV</span></h1>
            <p>Terminal de Control y Monitoreo</p>
        </header>

        <nav class="menu-grid">
            <!-- Opción: Realizar Tracking -->
            <a href="realizar_tracking.php" class="card">
                <div class="icon-box">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </div>
                <h2>Realizar Tracking</h2>
                <p>Iniciar el seguimiento en tiempo real de una unidad en ruta.</p>
            </a>

            <!-- Opción: Ver Trackings -->
            <a href="ver_trackings.php" class="card">
                <div class="icon-box">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 14h4v-4H4v4zm0 5h4v-4H4v4zM4 9h4V5H4v4zm5 5h12v-4H9v4zm0 5h12v-4H9v4zM9 5v4h12V5H9z"/>
                    </svg>
                </div>
                <h2>Ver Trackings</h2>
                <p>Consultar el historial y estado actual de todas las unidades activas.</p>
            </a>
        </nav>

        <footer class="footer">
            &copy; 2026 DevCore - Busito SV System
        </footer>
    </div>

</body>
</html>