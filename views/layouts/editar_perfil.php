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

$error_msg = "";

// Procesar actualización de perfil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_completo = $_POST['nombre_completo'];
    $nombre_usuario = $_POST['nombre_usuario'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    
    try {
        $update = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, nombre_usuario = ?, correo = ?, telefono = ? WHERE id_usuario = ?");
        if ($update->execute([$nombre_completo, $nombre_usuario, $correo, $telefono, $_SESSION['id_usuario']])) {
            $_SESSION['usuario_alias'] = $nombre_usuario;
            
            // Si hay foto, procesarla
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES['profile_image'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                if (in_array($file['type'], $allowed_types) && $file['size'] <= 2 * 1024 * 1024) {
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
                        
                        $updatePhoto = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?");
                        $updatePhoto->execute([$new_filename, $_SESSION['id_usuario']]);
                        $_SESSION['foto'] = $new_filename;
                    }
                }
            }
            
            // Redirigir al perfil después de guardar
            header("Location: /TRACKING_TERMINAL/views/layouts/perfil.php?success=1");
            exit();
        } else {
            $error_msg = "Error al actualizar el perfil";
        }
    } catch (PDOException $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | Busito SV Tracking Terminal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/perfil.css">
    <script src="/TRACKING_TERMINAL/assets/js/editar_perfil.js" defer></script>
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
                <div class="nav-item" data-url="/TRACKING_TERMINAL/views/layouts/menu_general.php">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </div>
                <div class="nav-item" data-url="/TRACKING_TERMINAL/views/layouts/perfil.php">
                    <i class="fas fa-user-circle"></i>
                    <span>Ver Perfil</span>
                </div>
                <div class="nav-item active" data-url="/TRACKING_TERMINAL/views/layouts/editar_perfil.php">
                    <i class="fas fa-user-edit"></i>
                    <span>Editar Perfil</span>
                </div>
            </nav>
        </aside>
        
        <!-- Contenido principal -->
        <main class="main-content">
            <div class="edit-profile-container">
                <div class="edit-card">
                    <div class="edit-header">
                        <h2><i class="fas fa-user-edit"></i> Editar Perfil</h2>
                        <p>Actualiza tus datos personales</p>
                    </div>
                    
                    <?php if ($error_msg): ?>
                        <div class="alert alert-error" style="margin: 20px 30px;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="editProfileForm">
                        <div class="edit-avatar-section">
                            <div class="edit-avatar-container">
                                <?php if ($usuario['foto_perfil'] && $usuario['foto_perfil'] != 'default.png'): ?>
                                    <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $usuario['foto_perfil']; ?>" alt="Foto de perfil" class="edit-avatar" id="editAvatar">
                                <?php else: ?>
                                    <div class="edit-avatar-initials" style="background-color: <?php echo $bg_color; ?>;" id="editAvatarInitials">
                                        <?php echo $iniciales; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <label for="profile_image" class="edit-camera-label">
                                <i class="fas fa-camera"></i> Cambiar foto
                            </label>
                            <input type="file" name="profile_image" id="profile_image" accept="image/jpeg, image/png, image/jpg" style="display: none;">
                            <p class="edit-hint">Formatos: JPG, PNG. Máx. 2MB</p>
                        </div>
                        
                        <div class="edit-form">
                            <div class="edit-field">
                                <label for="nombre_completo">
                                    <i class="fas fa-user"></i> Nombre completo
                                </label>
                                <input type="text" name="nombre_completo" id="nombre_completo" 
                                       value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" 
                                       placeholder="Ej: Juan Pérez" required>
                            </div>
                            
                            <div class="edit-field">
                                <label for="nombre_usuario">
                                    <i class="fas fa-at"></i> Nombre de usuario
                                </label>
                                <input type="text" name="nombre_usuario" id="nombre_usuario" 
                                       value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" 
                                       placeholder="Ej: juanperez" required>
                                <small class="field-hint">Este será tu identificador único en el sistema</small>
                            </div>
                            
                            <div class="edit-field">
                                <label for="correo">
                                    <i class="fas fa-envelope"></i> Correo electrónico
                                </label>
                                <input type="email" name="correo" id="correo" 
                                       value="<?php echo htmlspecialchars($usuario['correo']); ?>" 
                                       placeholder="Ej: juan@ejemplo.com" required>
                            </div>
                            
                            <div class="edit-field">
                                <label for="telefono">
                                    <i class="fas fa-phone"></i> Teléfono
                                </label>
                                <input type="tel" name="telefono" id="telefono" 
                                       value="<?php echo htmlspecialchars($usuario['telefono']); ?>" 
                                       placeholder="Ej: +503 1234 5678">
                                <small class="field-hint">Formato internacional recomendado</small>
                            </div>
                            
                            <div class="edit-buttons">
                                <button type="submit" class="save-btn">
                                    <i class="fas fa-save"></i> Guardar cambios
                                </button>
                                <a href="/TRACKING_TERMINAL/views/layouts/perfil.php" class="cancel-edit-btn" id="cancelEditBtn">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
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