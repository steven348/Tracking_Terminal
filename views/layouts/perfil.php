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
$colores = ['#2ecc71', '#3498db', '#9b59b6', '#e67e22', '#e74c3c', '#1abc9c', '#f1c40f'];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | Busito SV</title>
    <!-- Bootstrap 4 + Animate.css + Font Awesome + Google Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/perfil.css">
</head>
<body>
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-layout">
        <!-- Sidebar (mismo estilo original) -->
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

        <!-- Contenido principal -->
        <main class="main-content">
            <div class="profile-container">
                <div class="profile-card">
                    <!-- Cabecera azul con avatar centrado sobresaliendo -->
                    <div class="profile-header">
                        <div class="avatar-wrapper">
                            <?php if ($usuario['foto_perfil'] && $usuario['foto_perfil'] != 'default.png'): ?>
                                <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $usuario['foto_perfil']; ?>" class="avatar" id="profileAvatar">
                            <?php else: ?>
                                <div class="avatar avatar-initials" style="background-color: <?php echo $bg_color; ?>;">
                                    <?php echo $iniciales; ?>
                                </div>
                            <?php endif; ?>
                            <label for="profile_image" class="camera-icon">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>
                    </div>

                    <form method="POST" enctype="multipart/form-data" id="photoForm" style="display:none;">
                        <input type="file" name="profile_image" id="profile_image" accept="image/jpeg, image/png, image/jpg">
                        <button type="submit" name="upload_photo" id="uploadPhotoBtn">Subir</button>
                    </form>

                    <!-- Información del usuario -->
                    <div class="profile-info">
                        <h2 class="user-name"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></h2>
                        <p class="user-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($usuario['correo']); ?></p>
                        <p class="user-role">@<?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
                        <p class="user-phone"><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($usuario['telefono'] ?: 'No registrado'); ?></p>
                    </div>

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

                    <!-- Estadísticas en tarjetas modernas -->
                    <div class="stats-grid">
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

                    <!-- Información detallada en lista -->
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-id-card"></i></div>
                            <div class="info-label">ID Usuario</div>
                            <div class="info-value">#<?php echo $usuario['id_usuario']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-calendar-week"></i></div>
                            <div class="info-label">Registro</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></div>
                        </div>
                    </div>

                    <div class="profile-menu">
                        <a href="/TRACKING_TERMINAL/views/layouts/editar_perfil.php" class="btn-edit">
                            <i class="fas fa-user-edit"></i> Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal y loading (opcional, igual que antes) -->
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

    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner-border text-primary"></div>
            <p>Guardando cambios...</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Móvil: mostrar/ocultar sidebar
        const menuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        menuBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Vista previa y auto-subida de foto
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const avatarImg = document.querySelector('.avatar');
                    if (avatarImg && avatarImg.tagName === 'IMG') {
                        avatarImg.src = event.target.result;
                    } else {
                        const wrapper = document.querySelector('.avatar-wrapper');
                        const oldDiv = document.querySelector('.avatar-initials');
                        const newImg = document.createElement('img');
                        newImg.src = event.target.result;
                        newImg.className = 'avatar';
                        wrapper.insertBefore(newImg, oldDiv);
                        oldDiv.remove();
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