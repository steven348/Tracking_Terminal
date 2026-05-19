<!doctype html>
<html lang="es">

<head>
    <title>Trackings - Panel Administrador</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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

        .main-content{
            margin-left:280px;
            min-height:100vh;
            padding:25px;
            background:#071A2D;
        }

        .dashboard-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
            flex-wrap:wrap;
            gap:20px;
        }

        .dashboard-title h2{
            font-size:2rem;
            font-weight:800;
            margin-bottom:8px;
        }

        .dashboard-title p{
            color:white;
            margin:0;
        }

        .btn-action-primary{
            background:#00C2C7;
            border:none;
            color:white;
            padding:14px 22px;
            border-radius:16px;
            font-weight:700;
            transition:.3s;
        }

        .btn-action-primary:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 20px rgba(0,194,199,.3);
        }

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
            color:white;
            font-size:.9rem;
        }

        .stat-value{
            font-size:2rem;
            font-weight:800;
        }

        .stat-card p{
            color:white;
        }

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
            color:white;
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

        .table{
            color:white;
            margin:0;
        }

        .table thead th{
            border:none;
            background:#021121;
            color:white;
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

        .status-badge{
            padding:8px 14px;
            border-radius:50px;
            font-size:.75rem;
            font-weight:700;
        }

        .status-live{
            background:rgba(0,255,149,.15);
            color:#00ff95;
        }

        .status-delayed{
            background:rgba(255,80,80,.15);
            color:#ff7070;
        }

        .status-paused{
            background:rgba(255,255,255,.08);
            color:#ffffff;
        }

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
            color:white;
        }

        .btn-sync{
            background:#123C5D;
            color:white;
        }

        .btn-view{
            background:rgba(0,194,199,.12);
            color:white;
        }

        .btn-action:hover{
            transform:scale(1.05);
        }

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

    <?php include 'slider_admin.php'; ?>

    <div class="main-content">

        <div class="dashboard-header">
            <div class="dashboard-title">
                <h2>Trackings en Tiempo Real</h2>
                <p>Monitoreo de unidades, rutas y alertas activas en la plataforma.</p>
            </div>
            <button class="btn-action-primary">
                <i class="fa-solid fa-arrow-rotate-right"></i>
                Actualizar datos
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-title">Trackings Activos</div>
                        <div class="stat-value">32</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-bus"></i>
                    </div>
                </div>
                <p style="color:white; font-size:.9rem; margin:0;">Unidades con GPS conectado y reporte reciente.</p>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-title">Rutas en curso</div>
                        <div class="stat-value">12</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-route"></i>
                    </div>
                </div>
                <p style="color:white; font-size:.9rem; margin:0;">Recorridos activos con seguimiento en tiempo real.</p>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-title">Unidades GPS</div>
                        <div class="stat-value">24</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-location-crosshairs"></i>
                    </div>
                </div>
                <p style="color:white; font-size:.9rem; margin:0;">Vehículos con localización activa en el último minuto.</p>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-title">Alertas nuevas</div>
                        <div class="stat-value">4</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
                <p style="color:white; font-size:.9rem; margin:0;">Eventos de retrasos, desvíos o incidencias recientes.</p>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h4>Lista de Trackings</h4>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Buscar unidad, ruta o estado...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Unidad</th>
                            <th>Ruta</th>
                            <th>Estado</th>
                            <th>Última posición</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>BUS 023</td>
                            <td>Ruta 101 - Centro</td>
                            <td><span class="status-badge status-live">En ruta</span></td>
                            <td>13.6992, -89.1913</td>
                            <td>Hace 1 min</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>
                                    <button class="btn-action btn-sync" title="Forzar refresco"><i class="fa-solid fa-arrows-rotate"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>ECOP 047</td>
                            <td>Ruta 310 - Soyapango</td>
                            <td><span class="status-badge status-delayed">Retrasado</span></td>
                            <td>13.6850, -89.1697</td>
                            <td>Hace 4 min</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>
                                    <button class="btn-action btn-sync" title="Forzar refresco"><i class="fa-solid fa-arrows-rotate"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>METR 089</td>
                            <td>Ruta 450 - Aeropuerto</td>
                            <td><span class="status-badge status-live">En ruta</span></td>
                            <td>13.7143, -89.2215</td>
                            <td>Hace 2 min</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>
                                    <button class="btn-action btn-sync" title="Forzar refresco"><i class="fa-solid fa-arrows-rotate"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>RÁP 102</td>
                            <td>Ruta 205 - Santa Tecla</td>
                            <td><span class="status-badge status-paused">Pausado</span></td>
                            <td>13.6878, -89.2189</td>
                            <td>Hace 8 min</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>
                                    <button class="btn-action btn-sync" title="Forzar refresco"><i class="fa-solid fa-arrows-rotate"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>
