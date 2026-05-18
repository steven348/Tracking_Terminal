<!doctype html>
<html lang="en">
<head>
    <title>Busito SV - Admin Panel</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body {
            margin: 0;
            font-family: 'Inter', 'Roboto', sans-serif;
            background: #071A2D;
            color: #ffffff;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, #021121 0%, #0A2A44 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            box-sizing: border-box;
            box-shadow: 4px 0 20px rgba(0,0,0,0.35);
            border-right: 1px solid rgba(255,255,255,0.05);
            z-index: 1000;
            overflow-y: auto;
        }

        /* LOGO */
        .logo {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 40px;
            text-align: center;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 20%, #00C2C7 90%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* TITULOS */
        .menu-title {
            font-size: 12px;
            color: #7DA2BF;
            margin: 25px 0 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* ITEMS */
        .menu-item {
            display: block;
            width: calc(100% - 2px);
            padding: 12px 15px;
            border-radius: 14px;
            cursor: pointer;
            margin-bottom: 14px;
            transition: 0.3s ease;
            color: #E5F3FF;
            background: transparent;
            border: 1px solid transparent;
            text-decoration: none;
            font-size: 14px;
        }

        /* HOVER */
        .menu-item:hover {
            background: rgba(255,255,255,0.08);
            transform: translateX(5px);
            border-color: rgba(0,194,199,0.25);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* ACTIVO */
        .menu-item.active {
            background: linear-gradient(135deg, #00C2C7, #0E5F8C);
            color: #ffffff;
            box-shadow: 0 6px 18px rgba(0,194,199,0.35);
        }
        
        /* Iconos en menu */
        .menu-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        /* Scrollbar del sidebar */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #021121;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #00C2C7;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo">
        BUSITO <span>SV</span>
    </div>

    <a href="/TRACKING_TERMINAL/views/admin/vista_admin.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'vista_admin.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="/TRACKING_TERMINAL/views/admin/trakins_admin.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'trakins_admin.php' ? 'active' : ''; ?>">
        <i class="fas fa-map-marked-alt"></i> Trackings
    </a>

    <a href="/TRACKING_TERMINAL/views/admin/usuarios_admin.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'usuarios_admin.php' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Usuarios
    </a>

    <a href="/TRACKING_TERMINAL/views/admin/rutas_admin.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'rutas_admin.php' ? 'active' : ''; ?>">
        <i class="fas fa-map"></i> Rutas
    </a>

    <div class="menu-item">
        <i class="fas fa-file-alt"></i> Reportes
    </div>
    
    <div style="margin-top: 50px;">
        <a href="/TRACKING_TERMINAL/views/layouts/menu_general.php" class="menu-item">
            <i class="fas fa-arrow-left"></i> Volver al Menú
        </a>
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script>
    // Marcar el item activo según la URL actual
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname.split('/').pop();
        const menuItems = document.querySelectorAll('.menu-item');
        
        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && href.includes(currentPage)) {
                item.classList.add('active');
            }
        });
    });
</script>

</body>
</html>