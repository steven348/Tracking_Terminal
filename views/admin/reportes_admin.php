<?php ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>BUSITO SV · Reportes</title>

<!-- Bootstrap 4 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Animate CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>

<!-- Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

:root{
    --bg-main:#031b34;
    --bg-sidebar:#021529;
    --card-bg:#11263d;
    --card-hover:#16314d;
    --cyan:#08d4e8;
    --cyan-dark:#0a7f96;
    --text-main:#ffffff;
    --text-muted:#8ea9c1;
    --border-color:rgba(255,255,255,0.08);
}

/* ===== RESET ===== */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html,body{
    width:100%;
    min-height:100%;
    overflow-x:hidden;
}

body{
    background:var(--bg-main);
    font-family:'Segoe UI',sans-serif;
    color:var(--text-main);
}

/* ===== WRAPPER ===== */

.wrapper{
    display:flex;
    min-height:100vh;
    width:100%;
}

/* ===== SIDEBAR ===== */

.sidebar,
#sidebar,
aside{
    width:290px;
    min-width:290px;
    max-width:290px;
    position:fixed;
    top:0;
    left:0;
    height:100vh;
    z-index:1000;
    overflow-y:auto;
    background:linear-gradient(
        180deg,
        #021529 0%,
        #031b34 100%
    );
}

/* ===== MAIN ===== */

main{
    flex:1;
    margin-left:290px;
    width:calc(100% - 290px);
    padding:30px;
    background:var(--bg-main);
    overflow-x:hidden;
}

/* ===== TITULOS ===== */

h2,h4{
    color:white!important;
    font-weight:700;
}

/* ===== CARDS ===== */

.stat-card,
.chart-card,
.table-card{
    background:var(--card-bg);
    border:1px solid var(--border-color);
    border-radius:24px;
    padding:22px;
    transition:.3s ease;
    overflow:hidden;
}

.stat-card:hover,
.chart-card:hover,
.table-card:hover{
    background:var(--card-hover);
    transform:translateY(-3px);
}

/* ===== KPIs ===== */

.stat-title{
    color:var(--text-muted);
    font-size:.75rem;
    text-transform:uppercase;
    letter-spacing:1px;
    margin-bottom:10px;
}

.stat-value{
    font-size:2.3rem;
    font-weight:700;
    color:var(--cyan);
}

.trend,
.trend-down{
    color:var(--text-muted)!important;
    font-size:.8rem;
}

/* ===== TABLAS ===== */

.table{
    color:white;
}

.table th{
    color:var(--cyan);
    border-top:none!important;
    border-bottom:1px solid var(--border-color)!important;
    font-size:.82rem;
}

.table td{
    border-color:var(--border-color)!important;
    color:white;
}

.table-hover tbody tr:hover{
    background:rgba(8,212,232,.05);
}

/* ===== BADGES ===== */

.badge-status{
    background:rgba(8,212,232,.12);
    color:var(--cyan);
    border-radius:30px;
    padding:.35rem .8rem;
    font-size:.72rem;
}

.badge-demora{
    background:rgba(8,212,232,.18);
    color:var(--cyan);
}

/* ===== BOTONES ===== */

.btn-refresh{
    background:linear-gradient(
        90deg,
        var(--cyan),
        var(--cyan-dark)
    );
    border:none;
    border-radius:14px;
    color:white;
    padding:.6rem 1.2rem;
    font-weight:600;
}

.btn-export{
    background:rgba(255,255,255,.04);
    border:1px solid var(--border-color);
    color:white;
    border-radius:12px;
    padding:.45rem 1rem;
}

.btn-export:hover{
    background:rgba(8,212,232,.1);
    border-color:var(--cyan);
    color:var(--cyan);
}

/* ===== INPUTS ===== */

.form-control{
    background:#0d2238!important;
    border:1px solid var(--border-color)!important;
    color:white!important;
    border-radius:12px!important;
}

.form-control:focus{
    background:#0d2238!important;
    border-color:var(--cyan)!important;
    box-shadow:none!important;
}

/* ===== CHART ===== */

.chart-container{
    position:relative;
    width:100%;
    height:350px;
}

canvas{
    width:100%!important;
    height:100%!important;
}

/* ===== TEXT ===== */

.sync-text{
    color:var(--text-muted);
    font-size:.8rem;
}

/* ===== SCROLL ===== */

::-webkit-scrollbar{
    width:8px;
}

::-webkit-scrollbar-thumb{
    background:var(--cyan-dark);
    border-radius:20px;
}

::-webkit-scrollbar-track{
    background:var(--bg-sidebar);
}

/* ===== RESPONSIVE ===== */

@media(max-width:991px){

    .sidebar,
    #sidebar,
    aside{
        width:260px;
        min-width:260px;
    }

    main{
        margin-left:260px;
        width:calc(100% - 260px);
    }
}

@media(max-width:768px){

    .wrapper{
        flex-direction:column;
    }

    .sidebar,
    #sidebar,
    aside{
        position:relative;
        width:100%;
        max-width:100%;
        min-width:100%;
        height:auto;
    }

    main{
        width:100%;
        margin-left:0;
        padding:15px;
    }

    .stat-value{
        font-size:1.8rem;
    }
}

</style>
</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <?php include 'slider_admin.php'; ?>

    <!-- MAIN -->
    <main>

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 animate__animated animate__fadeIn">

            <h2>
                <i class="fas fa-chart-line mr-2"></i>
                Reportes y análisis
            </h2>

            <div class="d-flex align-items-center">

                <input type="date" class="form-control mr-2">

                <input type="date" class="form-control mr-2">

                <button class="btn-refresh">
                    <i class="fas fa-sync-alt"></i>
                    Generar
                </button>

            </div>

        </div>

        <!-- KPIS -->
        <div class="row">

            <div class="col-md-3 mb-4">
                <div class="stat-card animate__animated animate__fadeInUp">
                    <div class="stat-title">
                        TRACKINGS TOTALES
                    </div>

                    <div class="stat-value">
                        1,284
                    </div>

                    <div class="trend">
                        +8% vs semana
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="stat-card animate__animated animate__fadeInUp">
                    <div class="stat-title">
                        PUNTUALIDAD PROM.
                    </div>

                    <div class="stat-value">
                        87.3%
                    </div>

                    <div class="trend">
                        objetivo 90%
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="stat-card animate__animated animate__fadeInUp">
                    <div class="stat-title">
                        ALERTAS
                    </div>

                    <div class="stat-value">
                        23
                    </div>

                    <div class="trend">
                        -5 vs anterior
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="stat-card animate__animated animate__fadeInUp">
                    <div class="stat-title">
                        RUTAS ACTIVAS
                    </div>

                    <div class="stat-value">
                        12
                    </div>

                    <div class="trend">
                        cobertura 94%
                    </div>
                </div>
            </div>

        </div>

        <!-- CHARTS -->
        <div class="row">

            <div class="col-lg-6 mb-4">

                <div class="chart-card animate__animated animate__fadeInLeft">

                    <h4 class="mb-4">
                        <i class="fas fa-chart-line mr-2"></i>
                        Trackings por hora
                    </h4>

                    <div class="chart-container">
                        <canvas id="hourlyChart"></canvas>
                    </div>

                </div>

            </div>

            <div class="col-lg-6 mb-4">

                <div class="chart-card animate__animated animate__fadeInRight">

                    <h4 class="mb-4">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Trackings diarios
                    </h4>

                    <div class="chart-container">
                        <canvas id="dailyChart"></canvas>
                    </div>

                </div>

            </div>

        </div>

        <!-- TABLA -->
        <div class="table-card animate__animated animate__fadeInUp">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h4>
                    <i class="fas fa-route mr-2"></i>
                    Rendimiento de rutas
                </h4>

                <button class="btn-export">
                    Exportar CSV
                </button>

            </div>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>
                        <tr>
                            <th>Ruta</th>
                            <th>Horario</th>
                            <th>Trackings</th>
                            <th>Retrasos</th>
                            <th>Puntualidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>Ruta 101</td>
                            <td>06:00 - 20:00</td>
                            <td>342</td>
                            <td>5</td>
                            <td><span class="badge-status">94%</span></td>
                            <td><span class="badge-status">Activa</span></td>
                        </tr>

                        <tr>
                            <td>Ruta 205</td>
                            <td>05:30 - 21:30</td>
                            <td>408</td>
                            <td>12</td>
                            <td><span class="badge-status">88%</span></td>
                            <td><span class="badge-demora">Demora</span></td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

<script>

Chart.defaults.color="#8ea9c1";
Chart.defaults.borderColor="rgba(255,255,255,.05)";

/* ===== CHART LINE ===== */

new Chart(
    document.getElementById('hourlyChart'),
    {
        type:'line',
        data:{
            labels:["6am","8am","10am","12pm","2pm","4pm","6pm","8pm"],
            datasets:[{
                label:'Registros',
                data:[18,62,84,73,68,95,112,43],
                borderColor:'#08d4e8',
                backgroundColor:'rgba(8,212,232,.08)',
                pointBackgroundColor:'#08d4e8',
                borderWidth:3,
                tension:.4,
                fill:true
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false
        }
    }
);

/* ===== BAR CHART ===== */

new Chart(
    document.getElementById('dailyChart'),
    {
        type:'bar',
        data:{
            labels:["15/05","16/05","17/05","18/05","19/05","20/05","21/05"],
            datasets:[{
                label:'Trackings diarios',
                data:[187,201,245,232,290,312,284],
                backgroundColor:'#08d4e8',
                borderRadius:10
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false
        }
    }
);

</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>