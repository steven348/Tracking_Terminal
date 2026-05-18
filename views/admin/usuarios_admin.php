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

        .table tbody tr{
            transition:.2s;
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
            background:#00C2C7;
            color:white;
            font-weight:800;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .user-name{
            font-weight:600;
            margin-bottom:2px;
            color:white;
        }

        .user-email{
            font-size:.8rem;
            color:white;
        }

        /* BADGES */
        .status-badge{
            padding:8px 14px;
            border-radius:50px;
            font-size:.75rem;
            font-weight:700;
        }

        .active{
            background:rgba(0,255,149,.15);
            color:#00ff95;
        }

        .inactive{
            background:rgba(255,80,80,.15);
            color:#ff7070;
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

            <button class="btn-add-user">
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
                        <div class="stat-value">248</div>
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
                        <div class="stat-value">198</div>
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
                        <div class="stat-value">12</div>
                    </div>

                    <div class="stat-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                </div>

            </div>

            <div class="stat-card">

                <div class="stat-top">

                    <div>
                        <div class="stat-title">Bloqueados</div>
                        <div class="stat-value">6</div>
                    </div>

                    <div class="stat-icon">
                        <i class="fa-solid fa-user-lock"></i>
                    </div>

                </div>

            </div>

        </div>

        <!-- TABLE -->
        <div class="table-card">

            <div class="table-header">

                <h4>Lista de Usuarios</h4>

                <div class="search-box">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" placeholder="Buscar usuario...">

                </div>

            </div>

            <div class="table-responsive">

                <table class="table">

                    <thead>

                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Último acceso</th>
                            <th>Acciones</th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td>

                                <div class="user-info">

                                    <div class="user-avatar">
                                        A
                                    </div>

                                    <div>
                                        <div class="user-name">Alexis Martínez</div>
                                        <div class="user-email">alexis@gmail.com</div>
                                    </div>

                                </div>

                            </td>

                            <td>Administrador</td>

                            <td>
                                <span class="status-badge active">
                                    Activo
                                </span>
                            </td>

                            <td>Hace 5 min</td>

                            <td>

                                <div class="action-buttons">

                                    <button class="btn-action btn-edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button class="btn-action btn-delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td>

                                <div class="user-info">

                                    <div class="user-avatar">
                                        M
                                    </div>

                                    <div>
                                        <div class="user-name">María López</div>
                                        <div class="user-email">maria@gmail.com</div>
                                    </div>

                                </div>

                            </td>

                            <td>Usuario</td>

                            <td>
                                <span class="status-badge active">
                                    Activo
                                </span>
                            </td>

                            <td>Hace 12 min</td>

                            <td>

                                <div class="action-buttons">

                                    <button class="btn-action btn-edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button class="btn-action btn-delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td>

                                <div class="user-info">

                                    <div class="user-avatar">
                                        C
                                    </div>

                                    <div>
                                        <div class="user-name">Carlos Reyes</div>
                                        <div class="user-email">carlos@gmail.com</div>
                                    </div>

                                </div>

                            </td>

                            <td>Supervisor</td>

                            <td>
                                <span class="status-badge inactive">
                                    Inactivo
                                </span>
                            </td>

                            <td>Hace 3 días</td>

                            <td>

                                <div class="action-buttons">

                                    <button class="btn-action btn-edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button class="btn-action btn-delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>

</html>