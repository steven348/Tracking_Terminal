<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

$root = dirname(__DIR__, 2);
$pdo = require_once $root . "/config/database.php";

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['id_usuario']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Contar trackings realizados por el usuario
$tracking_count = 0;

// Si no existe el usuario, cerrar sesión
if (!$usuario) {
    session_destroy();
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

// Iniciales para el avatar
$alias = $_SESSION['usuario_alias'] ?? $usuario['nombre_usuario'];
$iniciales = strtoupper(substr($usuario['nombre_completo'], 0, 2));
$colores = ['#2ecc71', '#3498db', '#9b59b6', '#e67e22', '#e74c3c', '#1abc9c', '#f1c40f'];
$color_index = ord($alias[0]) % count($colores);
$bg_color = $colores[$color_index];

$success_msg = "";
$error_msg = "";

// Procesar cambio de foto
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
                    $success_msg = "Foto de perfil actualizada correctamente";
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
        $error_msg = "Seleccione una imagen";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | Busito SV Tracking Terminal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/perfil.css">
    <script src="/TRACKING_TERMINAL/assets/js/perfil.js" defer></script>
</head>
<body>
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-bus"></i> Busito SV
                </div>
                <div class="sidebar-subtitle">Tracking Terminal</div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item" data-url="/TRACKING_TERMINAL/views/layouts/menu_general.php" data-changes="false">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </div>
                <div class="nav-item active" data-url="/TRACKING_TERMINAL/views/layouts/perfil.php" data-changes="false">
                    <i class="fas fa-user-circle"></i>
                    <span>Ver Perfil</span>
                </div>
                <div class="nav-item" data-url="/TRACKING_TERMINAL/views/layouts/editar_perfil.php" data-changes="false">
                    <i class="fas fa-user-edit"></i>
                    <span>Editar Perfil</span>
                </div>
            </nav>
        </aside>
        
        <!-- Contenido principal -->
        <main class="main-content">
            <div class="profile-container">
                <div class="profile-card">
                    <!-- Encabezado con fondo -->
                    <div class="profile-header">
                        <!-- Avatar -->
                        <?php if ($usuario['foto_perfil'] && $usuario['foto_perfil'] != 'default.png'): ?>
                            <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $usuario['foto_perfil']; ?>" alt="Foto de perfil" class="avatar" id="profileAvatar">
                        <?php else: ?>
                            <div class="avatar avatar-initials" style="background-color: <?php echo $bg_color; ?>;">
                                <?php echo $iniciales; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Botón para cambiar foto -->
                        <label for="profile_image" class="camera-icon">
                            <i class="fas fa-camera"></i>
                        </label>
                        <form method="POST" enctype="multipart/form-data" class="photo-form" id="photoForm">
                            <input type="file" name="profile_image" id="profile_image" accept="image/jpeg, image/png, image/jpg" style="display: none;">
                            <button type="submit" name="upload_photo" id="uploadPhotoBtn" style="display: none;">Subir</button>
                        </form>
                    </div>

                    <!-- Datos principales -->
                    <div class="profile-info">
                        <h2 class="user-name"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></h2>
                        <p class="user-email"><?php echo htmlspecialchars($usuario['correo']); ?></p>
                        <p class="user-role">@<?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
                        <p class="user-phone"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($usuario['telefono'] ?: 'No registrado'); ?></p>
                    </div>

                    <!-- Mostrar mensajes de alerta -->
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_msg): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Estadísticas -->
                    <div class="stats-card">
                        <div class="stat-item">
                            <i class="fas fa-map-marked-alt"></i>
                            <div>
                                <h3>Trackings Realizados</h3>
                                <p class="stat-number"><?php echo $tracking_count; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Solo el botón de miembro desde -->
                    <div class="profile-menu">
                        <button class="menu-btn" id="miembroDesdeBtn" onclick="return false;">
                            <i class="fas fa-calendar-alt"></i> Miembro desde: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de confirmación -->
    <div id="confirmModal" class="modal-confirm">
        <div class="modal-confirm-content">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>¿Salir sin guardar?</h3>
            <p>Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?</p>
            <div class="modal-confirm-buttons">
                <button class="confirm-yes" id="confirmYes">Sí, salir</button>
                <button class="confirm-no" id="confirmNo">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <i class="fas fa-spinner fa-pulse"></i>
            <p>Guardando cambios...</p>
        </div>
    </div>
</body>
</html>