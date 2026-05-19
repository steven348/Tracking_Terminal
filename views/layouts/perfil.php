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
try {
    $tracking_count = 0;
} catch (Exception $e) {
    $tracking_count = 0;
}

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
    <div class="profile-container">
        
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

        <!-- Menú estilo app -->
        <div class="profile-menu">
            <button class="menu-btn" id="editProfileBtn">
                <i class="fas fa-user-edit"></i> Editar perfil
            </button>
            <button class="menu-btn" id="miembroDesdeBtn" onclick="return false;">
                <i class="fas fa-calendar-alt"></i> Miembro desde: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
            </button>
        </div>

        <!-- Botones de acción -->
        <div class="logout-section">
            <a href="/TRACKING_TERMINAL/views/layouts/menu_general.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Regresar al Menú
            </a>
        </div>
        
    </div>

    <!-- MODAL PARA EDITAR PERFIL -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Editar Perfil</h3>
                <span class="modal-close">&times;</span>
            </div>
            
            <form method="POST" id="editProfileForm">
                <!-- Foto de perfil en el modal -->
                <div class="modal-avatar-section">
                    <div class="modal-avatar-container">
                        <?php if ($usuario['foto_perfil'] && $usuario['foto_perfil'] != 'default.png'): ?>
                            <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $usuario['foto_perfil']; ?>" alt="Foto de perfil" class="modal-avatar" id="modalAvatar">
                        <?php else: ?>
                            <div class="modal-avatar-initials" style="background-color: <?php echo $bg_color; ?>;" id="modalAvatarInitials">
                                <?php echo $iniciales; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label for="modal_profile_image" class="modal-camera-icon">
                        <i class="fas fa-camera"></i> Cambiar foto
                    </label>
                    <input type="file" name="modal_profile_image" id="modal_profile_image" accept="image/jpeg, image/png, image/jpg" style="display: none;">
                    <p class="modal-hint">Formatos: JPG, PNG. Máx. 2MB</p>
                </div>
                
                <!-- Campos del formulario -->
                <div class="modal-field">
                    <label for="modal_nombre">
                        <i class="fas fa-user"></i> Nombre completo
                    </label>
                    <input type="text" name="nombre_completo" id="modal_nombre" 
                           value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" 
                           placeholder="Ej: Juan Pérez" required>
                </div>
                
                <div class="modal-field">
                    <label for="modal_usuario">
                        <i class="fas fa-at"></i> Nombre de usuario
                    </label>
                    <input type="text" name="nombre_usuario" id="modal_usuario" 
                           value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" 
                           placeholder="Ej: juanperez" required>
                    <small class="field-hint">Este será tu identificador único en el sistema</small>
                </div>
                
                <div class="modal-field">
                    <label for="modal_correo">
                        <i class="fas fa-envelope"></i> Correo electrónico
                    </label>
                    <input type="email" name="correo" id="modal_correo" 
                           value="<?php echo htmlspecialchars($usuario['correo']); ?>" 
                           placeholder="Ej: juan@ejemplo.com" required>
                </div>
                
                <div class="modal-field">
                    <label for="modal_telefono">
                        <i class="fas fa-phone"></i> Teléfono
                    </label>
                    <input type="tel" name="telefono" id="modal_telefono" 
                           value="<?php echo htmlspecialchars($usuario['telefono']); ?>" 
                           placeholder="Ej: +503 1234 5678">
                    <small class="field-hint">Formato internacional recomendado</small>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" name="update_profile" class="modal-save-btn">
                        <i class="fas fa-save"></i> Guardar cambios
                    </button>
                    <button type="button" class="modal-cancel-btn" id="modalCancelBtn">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal de carga -->
    <div id="loadingModal" class="modal loading-modal">
        <div class="loading-content">
            <i class="fas fa-spinner fa-pulse"></i>
            <p>Guardando cambios...</p>
        </div>
    </div>
</body>
</html>