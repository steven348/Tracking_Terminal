<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

$root = dirname(__DIR__, 2);
$pdo = require_once $root . "/config/database.php";

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['id_usuario']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Contar trackings (con detección de columna)
$tracking_count = 0;
if ($usuario) {
    try {
        $columnsStmt = $pdo->query("SHOW COLUMNS FROM trackings");
        $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
        $possibleColumns = ['id_usuario', 'usuario_id', 'user_id', 'id_user', 'fk_usuario'];
        $userColumn = null;
        foreach ($possibleColumns as $col) {
            if (in_array($col, $columns)) {
                $userColumn = $col;
                break;
            }
        }
        if ($userColumn) {
            $trackStmt = $pdo->prepare("SELECT COUNT(*) FROM trackings WHERE $userColumn = ?");
            $trackStmt->execute([$_SESSION['id_usuario']]);
            $tracking_count = $trackStmt->fetchColumn();
        } else {
            $trackStmt = $pdo->prepare("SELECT COUNT(*) FROM trackings WHERE id_usuario = ?");
            $trackStmt->execute([$_SESSION['id_usuario']]);
            $tracking_count = $trackStmt->fetchColumn();
        }
    } catch (PDOException $e) {
        $tracking_count = 0;
        error_log("Error al contar trackings: " . $e->getMessage());
    }
}

if (!$usuario) {
    session_destroy();
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

// Iniciales y color de avatar
$alias = $_SESSION['usuario_alias'] ?? $usuario['nombre_usuario'];
$iniciales = strtoupper(substr($usuario['nombre_completo'], 0, 2));
$colores = ['#2ecc71', '#3498db', '#9b59b6', '#e67e22', '#e74c3c', '#1abc9c'];
$color_index = ord($alias[0]) % count($colores);
$bg_color = $colores[$color_index];

$success_msg = "";
$error_msg = "";

// Procesar foto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_photo']) && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    if ($file['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowed_types)) {
            $error_msg = "Formato no permitido. Use JPG o PNG";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error_msg = "La imagen no debe superar los 2MB";
        } else {
            $upload_dir = $root . '/assets/img/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $new_filename = "user_" . $_SESSION['id_usuario'] . "_" . time() . "." . $ext;
            $upload_path = $upload_dir . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                if ($usuario['foto_perfil'] != 'default.png' && file_exists($upload_dir . $usuario['foto_perfil'])) {
                    unlink($upload_dir . $usuario['foto_perfil']);
                }
                $update = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?");
                if ($update->execute([$new_filename, $_SESSION['id_usuario']])) {
                    $_SESSION['foto'] = $new_filename;
                    $success_msg = "¡Foto de perfil actualizada!";
                    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
                    $stmt->execute([$_SESSION['id_usuario']]);
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error_msg = "Error al guardar la foto";
                }
            } else {
                $error_msg = "Error al subir la imagen";
            }
        }
    } else {
        $error_msg = "Seleccione una imagen válida";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Mi Perfil | Busito SV</title>
    <!-- Dependencias -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tus estilos originales (sidebar, base) -->
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/perfil.css">


    <style>
      
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        /* Cabecera tipo LinkedIn: fondo gris claro */
        .profile-header {
            background: #e8ecf1; /* gris claro estilo LinkedIn */
            height: 100px;
            position: relative;
            border-radius: 12px 12px 0 0;
        }

        /* Bloque de presentación (foto + nombre) */
        .profile-presentation {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding: 0 2rem 2rem;
            margin-top: -50px; /* sube para solapar con la cabecera */
            margin-bottom: 1rem; /* separación del siguiente contenido */
            position: relative;
            z-index: 2;
        }

        .avatar-wrapper {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: white;
            padding: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            flex-shrink: 0;
            position: relative;
        }

        .avatar, .avatar-initials {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 52px;
            font-weight: 700;
            color: white;
        }

        .camera-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #1a4a74;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            border: 2px solid white;
            transition: background 0.2s;
        }
        .camera-icon:hover {
            background: #0E2F4F;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.2rem;
            line-height: 1.2;
        }

        .user-headline {
            font-size: 1rem;
            color: #334155;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .user-location {
            color: #64748b;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Botones de acción (Editar perfil) */
        .profile-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 0.8rem;
        }

        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1a4a74;
            color: white;
            padding: 8px 20px;
            font-weight: 600;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-edit:hover {
            background: #0E2F4F;
            color: white;
            text-decoration: none;
        }

        /* Secciones estilo tarjetas */
        .profile-section {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e0e0e0;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Estadísticas en fila horizontal */
        .stats-row {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: #f8fafc;
            padding: 1.2rem 1.5rem;
            border-radius: 8px;
            text-align: center;
            min-width: 130px;
            flex: 1;
            border: 1px solid #e2e8f0;
            transition: box-shadow 0.2s;
        }
        .stat-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-icon {
            font-size: 1.8rem;
            color: #1a4a74;
            margin-bottom: 0.4rem;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Información de contacto en grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: #e9f0f8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a4a74;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .info-content {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
        }

        /* Alertas */
        .alert {
            margin: 1rem 2rem 0;
            border-radius: 8px;
        }

        /* Responsive: apila foto y texto en móvil */
        @media (max-width: 768px) {
            .profile-presentation {
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin-top: -40px;
                gap: 1rem;
            }
            .avatar-wrapper {
                width: 110px;
                height: 110px;
            }
            .avatar-initials {
                font-size: 42px;
            }
            .user-name {
                font-size: 1.6rem;
            }
            .profile-actions {
                justify-content: center;
            }
            .stats-row {
                flex-direction: column;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Ajuste fino para mantener tu sidebar igual */
        .main-content {
            padding: 2rem;
        }
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-layout">
        <!-- Sidebar (se mantiene exactamente como tu diseño original) -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo"><i class="fas fa-bus"></i> Busito SV</div>
                <div class="sidebar-subtitle">Tracking Terminal</div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item" data-url="/TRACKING_TERMINAL/views/layouts/menu_general.php">
                    <i class="fas fa-home"></i> <span>Inicio</span>
                </div>
                <div class="nav-item active" data-url="/TRACKING_TERMINAL/views/layouts/perfil.php">
                    <i class="fas fa-user-circle"></i> <span>Ver Perfil</span>
                </div>
                <div class="nav-item" data-url="/TRACKING_TERMINAL/views/layouts/editar_perfil.php">
                    <i class="fas fa-user-edit"></i> <span>Editar Perfil</span>
                </div>
            </nav>
        </aside>

        <!-- Contenido principal con perfil estilo LinkedIn -->
        <main class="main-content">
            <div class="profile-container">
                <div class="profile-card">
                    <!-- Cabecera gris claro -->
                    <div class="profile-header"></div>

                    <!-- Bloque presentación: foto, nombre, titular, botones -->
                    <div class="profile-presentation">
                        <div class="avatar-wrapper">
                            <?php if ($usuario['foto_perfil'] && $usuario['foto_perfil'] != 'default.png'): ?>
                                <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $usuario['foto_perfil']; ?>" 
                                     class="avatar" id="profileAvatar"
                                     alt="Foto de perfil de <?php echo htmlspecialchars($usuario['nombre_completo']); ?>">
                            <?php else: ?>
                                <div class="avatar avatar-initials" style="background-color: <?php echo $bg_color; ?>;">
                                    <?php echo $iniciales; ?>
                                </div>
                            <?php endif; ?>
                            <label for="profile_image" class="camera-icon" title="Cambiar foto de perfil">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>

                        <div class="user-details">
                            <h1 class="user-name"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></h1>
                            <p class="user-headline">@<?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
                            <p class="user-location">
                                <i class="fas fa-map-marker-alt"></i> El Salvador
                            </p>
                            
                        </div>
                    </div>

                    <!-- Formulario de foto oculto -->
                    <form method="POST" enctype="multipart/form-data" id="photoForm" style="display:none;">
                        <input type="file" name="profile_image" id="profile_image" accept="image/jpeg, image/png, image/jpg">
                        <button type="submit" name="upload_photo" id="uploadPhotoBtn">Subir</button>
                    </form>

                    <!-- Alertas -->
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error_msg; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <!-- Sección de estadísticas -->
                    <div class="profile-section">
                        <h2 class="section-title"><i class="fas fa-chart-bar"></i> Actividad</h2>
                        <div class="stats-row">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-map-marked-alt"></i></div>
                                <div class="stat-number"><?php echo number_format($tracking_count); ?></div>
                                <div class="stat-label">Trackings realizados</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                                <div class="stat-number"><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></div>
                                <div class="stat-label">Miembro desde</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                                <div class="stat-number">Activo</div>
                                <div class="stat-label">Estado</div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de información de contacto -->
                    <div class="profile-section">
                        <h2 class="section-title"><i class="fas fa-address-card"></i> Información de contacto</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                                <div class="info-content">
                                    <span class="info-label">Correo electrónico</span>
                                    <span class="info-value"><?php echo htmlspecialchars($usuario['correo']); ?></span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                                <div class="info-content">
                                    <span class="info-label">Teléfono</span>
                                    <span class="info-value"><?php echo htmlspecialchars($usuario['telefono'] ?: 'No registrado'); ?></span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="fas fa-calendar-week"></i></div>
                                <div class="info-content">
                                    <span class="info-label">Fecha de registro</span>
                                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de confirmación (sin cambios) -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>¿Salir sin guardar?</h5>
                    <p>Tienes cambios sin guardar.</p>
                    <button class="btn btn-danger" id="confirmYes">Sí, salir</button>
                    <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner-border text-primary"></div>
            <p>Guardando cambios...</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar móvil
        const menuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        menuBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Cambio de foto automático
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const avatarWrapper = document.querySelector('.avatar-wrapper');
                    let avatarImg = avatarWrapper.querySelector('img');
                    if (avatarImg) {
                        avatarImg.src = event.target.result;
                    } else {
                        const initialsDiv = avatarWrapper.querySelector('.avatar-initials');
                        const newImg = document.createElement('img');
                        newImg.src = event.target.result;
                        newImg.className = 'avatar';
                        newImg.alt = 'Foto de perfil';
                        avatarWrapper.insertBefore(newImg, initialsDiv);
                        initialsDiv.remove();
                    }
                    document.getElementById('uploadPhotoBtn').click();
                };
                reader.readAsDataURL(file);
            }
        });

        // Navegación sidebar
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                const url = this.dataset.url;
                if (url) window.location.href = url;
            });
        });
    </script>
</body>
</html>