<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>BUSITO SV · Dashboard Azul & Negro</title>
    <!-- Bootstrap 4 + Animate.css + Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- jQuery, Popper, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <?php include 'slider_admin.php'; ?>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #071A2D;
            color: #FFFFFF;
            overflow-x: hidden;
        }

        /* =========================
        MAIN CONTENT
        ========================= */
        .main-content {
            margin-left: 280px;
            padding: 24px 32px;
            transition: margin-left 0.3s;
            background: #071A2D;
        }

        /* =========================
        TARJETAS
        ========================= */
        .card-dashboard {
            background: #0E2F4F;
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 28px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
            transition: all 0.25s ease;
            padding: 1.25rem;
        }

        .metric-card {
            background: linear-gradient(135deg, #0A2A44 0%, #123C5D 100%);
            border-radius: 28px;
            padding: 1.2rem;
            border: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.25s ease, border-color 0.25s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        }

        .metric-card:hover {
            transform: translateY(-4px);
            border-color: #00C2C7;
            box-shadow: 0 14px 28px rgba(0,194,199,0.15);
        }

        .metric-info h4 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8EB5D1;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .metric-info .value {
            font-size: 2.2rem;
            font-weight: 800;
            color: #FFFFFF;
        }

        /* =========================
        ICONOS
        ========================= */
        .metric-icon {
            background: rgba(255,255,255,0.08);
             width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #00C2C7;
            font-size: 1.5rem;
            border: 1px solid rgba(255,255,255,0.06);
        }

        /* =========================
        PERFIL
        ========================= */
        profile-dark-card {
            background: linear-gradient(135deg, #0A2A44 0%, #123C5D 100%);
            border-radius: 32px;
            padding: 12px 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.35);
        }

        .avatar-circle {
            background: linear-gradient(145deg, #00C2C7, #0E5F8C);
            width: 56px;
            height: 56px;
            border-radius: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.5rem;
            color: #FFFFFF;
            box-shadow: 0 6px 16px rgba(0,194,199,0.25);
        }

        .profile-info p {
            margin: 0;
            font-size: 0.8rem;
            color: #C7DFF1;
        }

        /* =========================
          BADGES
        ========================= */
        .tracking-zero-badge {
            background: rgba(255,255,255,0.06);
            padding: 8px 18px;
            border-radius: 60px;
            font-weight: 700;
            color: #00C2C7;
            border: 1px solid rgba(0,194,199,0.25);
        }   

        .badge-route-active {
            background: rgba(0,194,199,0.12);
            color: #00C2C7;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-delay {
            background: rgba(255,80,80,0.15);
            color: #FF9A9A;
        }

        /* =========================
        BOTONES
        ========================= */
        .btn-outline-accent {
            border: 1px solid #00C2C7;
            background: transparent;
            border-radius: 40px;
            padding: 6px 18px;
            color: #00C2C7;
            font-weight: 600;
         transition: all 0.25s ease;
        }

        .btn-outline-accent:hover {
            background: rgba(0,194,199,0.12);
            color: #FFFFFF;
            border-color: #00C2C7;
            box-shadow: 0 6px 18px rgba(0,194,199,0.18);
        }

        .btn-back-menu {
            background: transparent;
            border: none;
            color: #00C2C7;
            font-weight: 600;
        }

        /* =========================
        TABLAS
        ========================= */
        .table-dark-custom {
            background: #0E2F4F;
            color: #FFFFFF;
            border-radius: 18px;
            overflow: hidden;
        }

        .table-dark-custom th,
        .table-dark-custom td {
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 12px 6px;
        }

        .table-dark-custom th {
            color: #8EB5D1;
            font-weight: 600;
        }

        /* =========================
        LISTA TRACKINGS
        ========================= */
        .tracking-list-custom {
            list-style: none;
            padding: 0;
        }

        .tracking-list-custom li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .bus-dot {
            width: 10px;
            height: 10px;
            background: #00C2C7;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,194,199,0.4);
        }

        .bus-dot.delayed {
            background: #0E5F8C;
        }

        /* =========================
        RESPONSIVE
        ========================= */
        @media (max-width: 768px) {

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .menu-toggle-btn {
                display: inline-block;
                background: #0E2F4F;
                border: 1px solid rgba(255,255,255,0.08);
                color: white;
                padding: 8px 12px;
                border-radius: 16px;
            }
        }

            .menu-toggle-btn {
                display: none;
            }

        /* =========================
        OVERLAY
        ========================= */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(2,17,33,0.75);
            z-index: 1040;
            display: none;
        }

        .overlay.active {
         display: block;
        }

        /* =========================
        TEXTOS
        ========================= */
        a,
        .nav-link-custom {
            cursor: pointer;
        }

        .animate__animated {
            animation-duration: 0.6s;
        }

        .text-warning {
            color: #00C2C7 !important;
        }

        .text-muted {
            color: #8EB5D1 !important;
        }

        h5,
        .h5 {
            color: #FFFFFF;
        }

        small,
        .small {
            color: #B7D0E3;
        }

        .btn-outline-accent:active,
        .btn-outline-accent:focus {
            box-shadow: none;
        }
       
        
    </style>
</head>
<body>



<main class="main-content">
    <!-- Botón menú móvil -->
    <button class="menu-toggle-btn mb-3" id="mobileMenuBtn"><i class="fas fa-bars"></i> Menú</button>

    <!-- Perfil estilo imagen (Carlos Mejía) -->
    <div class="profile-dark-card animate__animated animate__fadeInUp mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle">CA</div>
            <div class="profile-info">
                <h5 class="mb-0">Carlos Mejía</h5>
                <p>portero@gmail.com · @Charly</p>
                <p><i class="fas fa-phone-alt"></i> 3232-3222</p>
                <small class="text-muted"><i class="far fa-calendar-alt"></i> Miembro desde: 15/05/2026</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3 mt-2 mt-sm-0">
            <div class="tracking-zero-badge">
                <i class="fas fa-location-dot"></i> TRACKINGS REALIZADOS: 0
            </div>
            <button class="btn-outline-accent" id="editProfileBtn"><i class="fas fa-pen"></i> Editar perfil</button>
        </div>
    </div>

    <!-- Métricas principales -->
    <div class="row mb-4 animate__animated animate__fadeInUp">
        <div class="col-md-3 col-6 mb-3">
            <div class="metric-card">
                <div class="metric-info">
                    <h4>Unidades Activas</h4>
                    <div class="value">24</div>
                    <span class="small text-success"><i class="fas fa-arrow-up"></i> +3</span>
                </div>
                <div class="metric-icon"><i class="fas fa-bus"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="metric-card">
                <div class="metric-info">
                    <h4>Trackings Hoy</h4>
                    <div class="value">187</div>
                    <span class="small" style="color:#0A66FF;">+12% vs ayer</span>
                </div>
                <div class="metric-icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="metric-card">
                <div class="metric-info">
                    <h4>Usuarios</h4>
                    <div class="value">1,482</div>
                    <span class="small text-muted">+120 semana</span>
                </div>
                <div class="metric-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="metric-card">
                <div class="metric-info">
                    <h4>Rutas Activas</h4>
                    <div class="value">12</div>
                    <span class="small">cobertura 94%</span>
                </div>
                <div class="metric-icon"><i class="fas fa-map-marked-alt"></i></div>
            </div>
        </div>
    </div>

    <!-- Primera fila: Gráfico y seguimiento (sin mapa) -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card-dashboard animate__animated animate__fadeInLeft h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-simple" style="color:#0A66FF;"></i> Trackings por hora (hoy)</h5>
                    <i class="fas fa-chart-line text-muted"></i>
                </div>
                <canvas id="trackingChart" style="max-height: 240px; width:100%"></canvas>
                <div class="mt-3 text-center">
                    <button class="btn-outline-accent btn-sm" id="viewReportBtn"><i class="fas fa-file-alt"></i> Reporte detallado</button>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card-dashboard animate__animated animate__fadeInRight h-100">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0"><i class="fas fa-radar" style="color:#0A66FF;"></i> Seguimiento en tiempo real</h5>
                    <span class="badge-route-active">Live</span>
                </div>
                <ul class="tracking-list-custom">
                    <li>
                        <div class="bus-dot"></div>
                        <div class="flex-grow-1"><strong>Bus UNITRANS 023</strong><br><small class="text-muted">Ruta 101 · Centro Histórico</small></div>
                        <small>hace 2 min</small>
                    </li>
                    <li>
                        <div class="bus-dot delayed"></div>
                        <div class="flex-grow-1"><strong>Bus ECOBUS 047</strong><br><small class="text-muted">Ruta 310 · Retraso moderado</small></div>
                        <small>hace 8 min</small>
                    </li>
                    <li>
                        <div class="bus-dot"></div>
                        <div class="flex-grow-1"><strong>Bus RÁPIDO 102</strong><br><small class="text-muted">Ruta 205 · Llegada 5 min</small></div>
                        <small>hace 1 min</small>
                    </li>
                    <li>
                        <div class="bus-dot"></div>
                        <div class="flex-grow-1"><strong>Bus METRO 089</strong><br><small class="text-muted">Ruta 450 · Terminal Aeropuerto</small></div>
                        <small>ahora</small>
                    </li>
                </ul>
                <div class="mt-2 text-end">
                    <a href="#" class="small" style="color:#0A66FF;" id="viewAllUnitsLink"><i class="fas fa-map-marked-alt"></i> Ver todas las unidades</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de rutas operativas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-dashboard animate__animated animate__fadeInUp">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0"><i class="fas fa-route" style="color:#0A66FF;"></i> Rutas operativas · Hoy</h5>
                    <span class="badge-route-active">Actualizado</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark-custom w-100">
                        <thead>
                            <tr><th>Ruta</th><th>Horario</th><th>Estado</th><th>Conductor</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>RUTA 101 - Centro</td><td>06:00 - 20:00</td><td><span class="badge-route-active">Activa</span></td><td>Luis Martínez</td></tr>
                            <tr><td>RUTA 205 - Santa Tecla</td><td>05:30 - 21:30</td><td><span class="badge-route-active">Activa</span></td><td>Ana Flores</td></tr>
                            <tr><td>RUTA 310 - Soyapango</td><td>06:15 - 19:45</td><td><span class="badge-route-active badge-delay">Demora</span></td><td>Carlos Méndez</td></tr>
                            <tr><td>RUTA 450 - Aeropuerto</td><td>04:50 - 22:10</td><td><span class="badge-route-active">Activa</span></td><td>Rosa Campos</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted"><i class="far fa-clock"></i> Última sincronización hace 2 min</small>
                    <button id="backToMenuBtn" class="btn-back-menu"><i class="fas fa-arrow-left"></i> Regresar al Menú</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Inicializar gráfico (Chart.js) con color azul
    let chartInstance = null;
    function initChart() {
        const ctx = document.getElementById('trackingChart').getContext('2d');
        if (chartInstance) chartInstance.destroy();
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['6am', '8am', '10am', '12pm', '2pm', '4pm', '6pm', '8pm'],
                datasets: [{
                    label: 'Trackings GPS',
                    data: [42, 68, 84, 72, 95, 112, 88, 57],
                    backgroundColor: '#0A66FF',
                    borderRadius: 12,
                    barPercentage: 0.65,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { labels: { color: '#FFFFFF', font: { size: 11 } } },
                    tooltip: { backgroundColor: '#111111', titleColor: '#0A66FF' }
                },
                scales: {
                    y: { grid: { color: '#222222' }, ticks: { color: '#CCCCCC' }, title: { display: true, text: 'Registros', color: '#FFFFFF' } },
                    x: { ticks: { color: '#FFFFFF' } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initChart();

        // Sidebar mobile logic
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mobileBtn = document.getElementById('mobileMenuBtn');
        function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
        function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('active'); }
        if (mobileBtn) {
            mobileBtn.addEventListener('click', () => {
                if (sidebar.classList.contains('open')) closeSidebar();
                else openSidebar();
            });
        }
        if (overlay) overlay.addEventListener('click', closeSidebar);

        // Navegación entre secciones (simulación con mensajes y cambio activo)
        const navLinks = document.querySelectorAll('.nav-link-custom');
        const handleNavClick = (e) => {
            const target = e.currentTarget;
            const section = target.getAttribute('data-section');
            navLinks.forEach(link => link.classList.remove('active'));
            target.classList.add('active');
            // Feedback visual moderno
            const notif = document.createElement('div');
            notif.className = 'animate__animated animate__fadeInDown';
            notif.style.position = 'fixed';
            notif.style.bottom = '20px';
            notif.style.right = '20px';
            notif.style.backgroundColor = '#111111';
            notif.style.borderLeft = `4px solid #0A66FF`;
            notif.style.padding = '12px 20px';
            notif.style.borderRadius = '20px';
            notif.style.color = 'white';
            notif.style.zIndex = '1050';
            notif.style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
            notif.innerText = `🔍 Visualizando módulo: ${section.charAt(0).toUpperCase() + section.slice(1)} · Datos actualizados.`;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 2000);
            if (window.innerWidth < 768) closeSidebar();
        };
        navLinks.forEach(link => link.addEventListener('click', handleNavClick));

        // Editar perfil
        const editBtn = document.getElementById('editProfileBtn');
        if (editBtn) {
            editBtn.addEventListener('click', () => {
                alert("✏️ Editar perfil de Carlos Mejía\nCorreo: portero@gmail.com\nTeléfono: 3232-3222\nFuncionalidad completa en próxima versión.");
            });
        }
        // Botón reporte
        const reportBtn = document.getElementById('viewReportBtn');
        if (reportBtn) {
            reportBtn.addEventListener('click', () => {
                alert("📊 Generando reporte ejecutivo de trackings y rendimiento de flota BUSITO SV.");
            });
        }
        // Regresar al menú (reset a dashboard)
        const backMenu = document.getElementById('backToMenuBtn');
        if (backMenu) {
            backMenu.addEventListener('click', () => {
                navLinks.forEach(link => link.classList.remove('active'));
                const dashLink = document.querySelector('.nav-link-custom[data-section="dashboard"]');
                if (dashLink) dashLink.classList.add('active');
                const notif = document.createElement('div');
                notif.className = 'animate__animated animate__fadeInUp';
                notif.style.position = 'fixed';
                notif.style.bottom = '20px';
                notif.style.left = '20px';
                notif.style.backgroundColor = '#111111';
                notif.style.color = '#0A66FF';
                notif.style.padding = '10px 20px';
                notif.style.borderRadius = '30px';
                notif.style.fontSize = '0.8rem';
                notif.style.border = '1px solid #0A66FF';
                notif.innerHTML = '<i class="fas fa-home"></i> Regresaste al Dashboard principal';
                document.body.appendChild(notif);
                setTimeout(() => notif.remove(), 2000);
            });
        }
        document.getElementById('viewAllUnitsLink')?.addEventListener('click', (e) => {
            e.preventDefault();
            alert("🚍 Vista de todas las unidades: 24 buses activos, 12 rutas en tiempo real. Próximamente mapa interactivo.");
        });
    });
    // Ajuste de gráfico si hay resize
    window.addEventListener('resize', () => { if (chartInstance) chartInstance.resize(); });
</script>
</body>
</html>