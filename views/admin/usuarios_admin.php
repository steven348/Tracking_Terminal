<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

$root = dirname(__DIR__, 2);
$pdo = require_once $root . "/config/database.php";

// Obtener estadísticas de usuarios (sin usar columna estado)
$stats_query = $pdo->query("
    SELECT 
        COUNT(*) as total_usuarios,
        SUM(CASE WHEN id_rol = 1 THEN 1 ELSE 0 END) as total_administradores,
        SUM(CASE WHEN id_rol = 2 THEN 1 ELSE 0 END) as total_usuarios
    FROM usuarios
");
$stats = $stats_query->fetch(PDO::FETCH_ASSOC);

// Agregar usuarios activos como todos (si no hay columna estado)
$stats['usuarios_activos'] = $stats['total_usuarios'];
$stats['usuarios_inactivos'] = 0;

// Obtener lista de usuarios
$usuarios_query = $pdo->query("
    SELECT u.*, r.nombre_rol 
    FROM usuarios u 
    LEFT JOIN roles r ON u.id_rol = r.id_rol 
    ORDER BY u.id_usuario DESC
");
$usuarios = $usuarios_query->fetchAll(PDO::FETCH_ASSOC);

// Colores para los avatares
$colores = ['#2ecc71', '#3498db', '#9b59b6', '#e67e22', '#e74c3c', '#1abc9c', '#f1c40f', '#00C2C7'];
?>

<!doctype html>
<html lang="es">

<head>

    <title>Usuarios - Panel Administrador</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap -->
    <link rel="stylesheet"
        href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Inter',sans-serif;
            background:#071A2D;
            color:white;
            overflow-x:hidden;
        }

        /* =========================
           MAIN CONTENT
        ========================= */
        .main-content{
            margin-left:280px;
            min-height:100vh;
            padding:25px;
            background:#071A2D;
        }

        /* =========================
           HEADER
        ========================= */
        .dashboard-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
        }

        .dashboard-title h2{
            font-size:2rem;
            font-weight:800;
            margin-bottom:5px;
        }

        .dashboard-title p{
            color:#9FC4DD;
            margin:0;
        }

        .btn-add-user{
            background:#00C2C7;
            border:none;
            color:white;
            padding:14px 22px;
            border-radius:16px;
            font-weight:700;
            transition:.3s;
            cursor:pointer;
        }

        .btn-add-user:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 20px rgba(0,194,199,.3);
        }

        /* =========================
           CARDS
        ========================= */
        .stats-grid{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:20px;
            margin-bottom:30px;
        }

        .stat-card{
            background:#0E2F4F;
            border-radius:24px;
            padding:24px;
            border:1px solid rgba(255,255,255,.05);
            transition:.3s;
        }

        .stat-card:hover{
            transform:translateY(-4px);
        }

        .stat-top{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }

        .stat-icon{
            width:55px;
            height:55px;
            border-radius:18px;
            background:#123C5D;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#00C2C7;
            font-size:1.3rem;
        }

        .stat-title{
            color:#9FC4DD;
            font-size:.9rem;
        }

        .stat-value{
            font-size:2rem;
            font-weight:800;
        }

        /* =========================
           TABLE CARD
        ========================= */
        .table-card{
            background:#0E2F4F;
            border-radius:28px;
            padding:25px;
            border:1px solid rgba(255,255,255,.05);
        }

        .table-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
            flex-wrap:wrap;
            gap:15px;
        }

        .table-header h4{
            margin:0;
            font-weight:700;
        }

        .search-box{
            position:relative;
        }

        .search-box input{
            background:#123C5D;
            border:none;
            border-radius:14px;
            padding:12px 16px 12px 45px;
            color:white;
            min-width:280px;
            outline:none;
        }

        .search-box input::placeholder{
            color:#9FC4DD;
        }

        .search-box i{
            position:absolute;
            left:15px;
            top:50%;
            transform:translateY(-50%);
            color:#9FC4DD;
        }

        /* =========================
           TABLE
        ========================= */
        .table{
            color:white;
            margin:0;
        }

        .table thead th{
            border:none;
            background:#021121;
            color:#9FC4DD;
            padding:18px;
            font-size:.85rem;
            text-transform:uppercase;
            letter-spacing:.5px;
        }

        .table tbody td{
            border-top:1px solid rgba(255,255,255,.05);
            padding:18px;
            vertical-align:middle;
            color:white;
        }

        .table tbody tr:hover{
            background:rgba(255,255,255,.03);
        }

        /* USER INFO */
        .user-info{
            display:flex;
            align-items:center;
            gap:12px;
        }

        .user-avatar{
            width:45px;
            height:45px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:800;
            font-size:1.2rem;
            color:white;
        }

        .user-avatar img{
            width:100%;
            height:100%;
            border-radius:50%;
            object-fit:cover;
        }

        .user-name{
            font-weight:600;
            margin-bottom:2px;
            color:white;
        }

        .user-email{
            font-size:.8rem;
            color:#9FC4DD;
        }

        /* BADGES */
        .status-badge{
            padding:8px 14px;
            border-radius:50px;
            font-size:.75rem;
            font-weight:700;
            display:inline-block;
        }

        .status-active{
            background:rgba(0,255,149,.15);
            color:#00ff95;
        }

        .status-inactive{
            background:rgba(255,80,80,.15);
            color:#ff7070;
        }

        .role-badge{
            padding:6px 12px;
            border-radius:50px;
            font-size:.7rem;
            font-weight:600;
            display:inline-block;
        }

        .role-admin{
            background:rgba(0,194,199,.15);
            color:#00C2C7;
        }

        .role-user{
            background:rgba(255,255,255,.1);
            color:#9FC4DD;
        }

        /* ACTIONS */
        .action-buttons{
            display:flex;
            gap:10px;
        }

        .btn-action{
            width:38px;
            height:38px;
            border:none;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            transition:.3s;
            cursor:pointer;
        }

        .btn-edit{
            background:#123C5D;
            color:#00C2C7;
        }

        .btn-delete{
            background:rgba(255,80,80,.15);
            color:#ff7070;
        }

        .btn-action:hover{
            transform:scale(1.05);
        }

        /* PAGINACION */
        .pagination{
            margin-top:25px;
            display:flex;
            justify-content:center;
            gap:8px;
            flex-wrap:wrap;
        }

        .page-btn{
            background:#123C5D;
            border:none;
            padding:8px 14px;
            border-radius:10px;
            color:white;
            cursor:pointer;
            transition:.2s;
        }

        .page-btn:hover{
            background:#00C2C7;
        }

        .page-btn.active{
            background:#00C2C7;
        }

        /* FILTROS */
        .filter-buttons{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .filter-btn{
            background:#123C5D;
            border:none;
            padding:8px 16px;
            border-radius:12px;
            color:#9FC4DD;
            cursor:pointer;
            transition:.2s;
        }

        .filter-btn.active{
            background:#00C2C7;
            color:white;
        }

        .filter-btn:hover{
            background:rgba(0,194,199,.5);
            color:white;
        }

        /* =========================
           RESPONSIVE
        ========================= */
        @media(max-width:1200px){
            .stats-grid{
                grid-template-columns:repeat(2,1fr);
            }
        }

        @media(max-width:768px){
            .main-content{
                margin-left:0;
                padding:15px;
            }
            .stats-grid{
                grid-template-columns:1fr;
            }
            .dashboard-header{
                flex-direction:column;
                align-items:flex-start;
                gap:15px;
            }
            .table-header{
                flex-direction:column;
                align-items:stretch;
            }
            .search-box input{
                width:100%;
                min-width:100%;
            }
            .table-responsive{
                overflow-x:auto;
            }
            .filter-buttons{
                justify-content:center;
            }
            .action-buttons{
                flex-direction:column;
                gap:5px;
            }
        }

    </style>

</head>

<body>

    <!-- SLIDER -->
    <?php include 'slider_admin.php'; ?>

    <!-- MAIN -->
    <div class="main-content">

        <!-- HEADER -->
        <div class="dashboard-header">

            <div class="dashboard-title">
                <h2>Gestión de Usuarios</h2>
                <p>Administración completa de usuarios registrados</p>
            </div>

            <button class="btn-add-user" id="btnAddUser">
                <i class="fa-solid fa-user-plus"></i>
                Nuevo Usuario
            </button>

        </div>

        <!-- STATS -->
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-title">Usuarios Totales</div>
                        <div class="stat-value" id="totalUsuarios"><?php echo $stats['total_usuarios'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-title">Usuarios Activos</div>
                        <div class="stat-value" id="usuariosActivos"><?php echo $stats['usuarios_activos'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-title">Administradores</div>
                        <div class="stat-value" id="totalAdministradores"><?php echo $stats['total_administradores'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-title">Usuarios Normales</div>
                        <div class="stat-value" id="totalUsuariosNormales"><?php echo $stats['total_usuarios'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- TABLE -->
        <div class="table-card">

            <div class="table-header">

                <h4>Lista de Usuarios</h4>

                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">Todos</button>
                    <button class="filter-btn" data-filter="admin">Administradores</button>
                    <button class="filter-btn" data-filter="user">Usuarios</button>
                </div>

                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Buscar usuario...">
                </div>

            </div>

            <div class="table-responsive">

                <table class="table" id="usersTable">

                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Teléfono</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody id="usersTableBody">
                        <?php foreach ($usuarios as $usuario): 
                            $inicial = strtoupper(substr($usuario['nombre_completo'], 0, 2));
                            $color_index = ord($usuario['nombre_usuario'][0]) % count($colores);
                            $avatar_color = $colores[$color_index];
                        ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <?php if ($usuario['foto_perfil'] && $usuario['foto_perfil'] != 'default.png'): ?>
                                        <div class="user-avatar">
                                            <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $usuario['foto_perfil']; ?>" alt="Avatar">
                                        </div>
                                    <?php else: ?>
                                        <div class="user-avatar" style="background-color: <?php echo $avatar_color; ?>;">
                                            <?php echo $inicial; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="user-name"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars($usuario['correo']); ?></div>
                                    </div>
                                </div>
                             </div>
                            <td>
                                <span class="role-badge <?php echo $usuario['id_rol'] == 1 ? 'role-admin' : 'role-user'; ?>">
                                    <i class="fa-solid <?php echo $usuario['id_rol'] == 1 ? 'fa-shield-halved' : 'fa-user'; ?>"></i>
                                    <?php echo htmlspecialchars($usuario['nombre_rol']); ?>
                                </span>
                             </div>
                            <td><?php echo htmlspecialchars($usuario['telefono'] ?? 'No registrado'); ?>?</div>
                            <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>?</div>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" data-id="<?php echo $usuario['id_usuario']; ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn-action btn-delete" data-id="<?php echo $usuario['id_usuario']; ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                             </div>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (count($usuarios) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:60px;">
                                <i class="fa-solid fa-users-slash" style="font-size:48px; color:#9FC4DD; margin-bottom:15px; display:block;"></i>
                                <p>No hay usuarios registrados</p>
                             </div>
                        </tr>
                        <?php endif; ?>
                    </tbody>

                <table>

            </div>

            <!-- Paginación -->
            <div class="pagination" id="pagination"></div>

        </div>

    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    
    <script>
        // Datos de usuarios desde PHP (pasados a JSON)
        const usuariosData = <?php echo json_encode($usuarios); ?>;
        const colores = <?php echo json_encode($colores); ?>;
        
        let currentFilter = 'all';
        let currentSearch = '';
        let currentPage = 1;
        const itemsPerPage = 10;
        
        // Función para obtener iniciales
        function getInitials(nombre) {
            if (!nombre) return '??';
            const partes = nombre.trim().split(' ');
            if (partes.length >= 2) {
                return (partes[0][0] + partes[1][0]).toUpperCase();
            }
            return nombre.substring(0, 2).toUpperCase();
        }
        
        // Función para obtener color del avatar
        function getAvatarColor(usuario) {
            if (!usuario.nombre_usuario) return '#00C2C7';
            const index = usuario.nombre_usuario.charCodeAt(0) % colores.length;
            return colores[index];
        }
        
        // Función para filtrar usuarios
        function filterUsers() {
            let filtered = [...usuariosData];
            
            // Filtrar por rol
            if (currentFilter === 'admin') {
                filtered = filtered.filter(u => u.id_rol == 1);
            } else if (currentFilter === 'user') {
                filtered = filtered.filter(u => u.id_rol == 2);
            }
            
            // Filtrar por búsqueda
            if (currentSearch) {
                const searchLower = currentSearch.toLowerCase();
                filtered = filtered.filter(u => 
                    u.nombre_completo.toLowerCase().includes(searchLower) ||
                    u.correo.toLowerCase().includes(searchLower) ||
                    (u.nombre_usuario && u.nombre_usuario.toLowerCase().includes(searchLower)) ||
                    (u.telefono && u.telefono.includes(searchLower))
                );
            }
            
            return filtered;
        }
        
        // Función para renderizar tabla
        function renderTable() {
            const filtered = filterUsers();
            const totalPages = Math.ceil(filtered.length / itemsPerPage);
            const start = (currentPage - 1) * itemsPerPage;
            const paginated = filtered.slice(start, start + itemsPerPage);
            
            const tbody = document.getElementById('usersTableBody');
            
            if (paginated.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align:center; padding:60px;">
                            <i class="fa-solid fa-users-slash" style="font-size:48px; color:#9FC4DD; margin-bottom:15px; display:block;"></i>
                            <p>No hay usuarios que coincidan con la búsqueda</p>
                        </td>
                    </tr>
                `;
                document.getElementById('pagination').innerHTML = '';
                return;
            }
            
            tbody.innerHTML = paginated.map(usuario => {
                const inicial = getInitials(usuario.nombre_completo);
                const avatarColor = getAvatarColor(usuario);
                const fechaRegistro = usuario.fecha_registro ? new Date(usuario.fecha_registro).toLocaleDateString('es-ES') : 'No disponible';
                
                const hasPhoto = usuario.foto_perfil && usuario.foto_perfil !== 'default.png';
                
                return `
                    <tr>
                        <td>
                            <div class="user-info">
                                ${hasPhoto ? 
                                    `<div class="user-avatar"><img src="/TRACKING_TERMINAL/assets/img/profiles/${usuario.foto_perfil}" alt="Avatar"></div>` :
                                    `<div class="user-avatar" style="background-color: ${avatarColor};">${inicial}</div>`
                                }
                                <div>
                                    <div class="user-name">${escapeHtml(usuario.nombre_completo)}</div>
                                    <div class="user-email">${escapeHtml(usuario.correo)}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge ${usuario.id_rol == 1 ? 'role-admin' : 'role-user'}">
                                <i class="fa-solid ${usuario.id_rol == 1 ? 'fa-shield-halved' : 'fa-user'}"></i>
                                ${usuario.id_rol == 1 ? 'Administrador' : 'Usuario'}
                            </span>
                        </td>
                        <td>${escapeHtml(usuario.telefono || 'No registrado')}</td>
                        <td>${fechaRegistro}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" data-id="${usuario.id_usuario}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn-action btn-delete" data-id="${usuario.id_usuario}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            renderPagination(totalPages);
            attachButtonEvents();
        }
        
        // Función para renderizar paginación
        function renderPagination(totalPages) {
            const paginationDiv = document.getElementById('pagination');
            if (totalPages <= 1) {
                paginationDiv.innerHTML = '';
                return;
            }
            
            let html = '';
            for (let i = 1; i <= totalPages; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
            }
            paginationDiv.innerHTML = html;
            
            document.querySelectorAll('.page-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentPage = parseInt(this.getAttribute('data-page'));
                    renderTable();
                });
            });
        }
        
        // Función para escapar HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Eventos de botones
        function attachButtonEvents() {
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    alert(`Funcionalidad de edición para usuario ID: ${id} (Próximamente)`);
                });
            });
            
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                        alert(`Eliminar usuario ID: ${id} (Próximamente)`);
                    }
                });
            });
        }
        
        // Eventos de filtros
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter');
                currentPage = 1;
                renderTable();
            });
        });
        
        // Evento de búsqueda
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                currentSearch = this.value;
                currentPage = 1;
                renderTable();
            });
        }
        
        // Botón agregar usuario
        const btnAddUser = document.getElementById('btnAddUser');
        if (btnAddUser) {
            btnAddUser.addEventListener('click', function() {
                alert('Funcionalidad para agregar nuevo usuario (Próximamente)');
            });
        }
        
        // Renderizar tabla al cargar
        renderTable();
    </script>

</body>

</html>