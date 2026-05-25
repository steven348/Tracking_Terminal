<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

$root = dirname(__DIR__, 2);
$pdo = require_once $root . "/config/database.php";

// Obtener datos actuales del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['id_usuario']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header("Location: /TRACKING_TERMINAL/index.php");
    exit();
}

$success_msg = "";
$error_msg = "";

// Procesar actualización de perfil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errores = [];
    if (empty($nombre_completo)) $errores[] = "El nombre completo es obligatorio";
    if (empty($correo)) $errores[] = "El correo es obligatorio";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "Formato de correo inválido";
    if (empty($nombre_usuario)) $errores[] = "El nombre de usuario es obligatorio";
    
    if (empty($errores)) {
        // Verificar si el correo ya existe en otro usuario
        $check = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?");
        $check->execute([$correo, $_SESSION['id_usuario']]);
        if ($check->fetch()) {
            $error_msg = "El correo ya está registrado por otro usuario";
        } else {
            // Verificar nombre de usuario único
            $checkUser = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? AND id_usuario != ?");
            $checkUser->execute([$nombre_usuario, $_SESSION['id_usuario']]);
            if ($checkUser->fetch()) {
                $error_msg = "El nombre de usuario ya está en uso";
            } else {
                // Actualizar datos
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, telefono = ?, correo = ?, nombre_usuario = ?, password = ? WHERE id_usuario = ?");
                    $ok = $update->execute([$nombre_completo, $telefono, $correo, $nombre_usuario, $hashed, $_SESSION['id_usuario']]);
                } else {
                    $update = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, telefono = ?, correo = ?, nombre_usuario = ? WHERE id_usuario = ?");
                    $ok = $update->execute([$nombre_completo, $telefono, $correo, $nombre_usuario, $_SESSION['id_usuario']]);
                }
                if ($ok) {
                    $success_msg = "Perfil actualizado correctamente";
                    // Actualizar sesión
                    $_SESSION['usuario_alias'] = $nombre_usuario;
                    // Recargar datos
                    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
                    $stmt->execute([$_SESSION['id_usuario']]);
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error_msg = "Error al actualizar los datos";
                }
            }
        }
    } else {
        $error_msg = implode(", ", $errores);
    }
}

// Iniciales para avatar
$alias = $_SESSION['usuario_alias'] ?? $usuario['nombre_usuario'];
$iniciales = strtoupper(substr($usuario['nombre_completo'], 0, 2));
$colores = ['#2ecc71', '#3498db', '#9b59b6', '#e67e22', '#e74c3c', '#1abc9c', '#f1c40f'];
$color_index = ord($alias[0]) % count($colores);
$bg_color = $colores[$color_index];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | Busito SV</title>
    <!-- Bootstrap 4 + Animate.css + Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/perfil.css">
    <style>
        /* Ajustes específicos para formulario de edición */
        .edit-form {
            padding: 1.5rem 2rem 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-group label i {
            color: #1a4a74;
            width: 20px;
        }
        .form-control {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #1a4a74;
            box-shadow: 0 0 0 3px rgba(26,74,116,0.1);
        }
        .btn-save {
            background: #1a4a74;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-save:hover {
            background: #0E2F4F;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,74,116,0.3);
        }
        .cancel-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: 0.2s;
        }
        .cancel-link:hover {
            color: #1a4a74;
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .edit-form {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-layout">
        <!-- Sidebar idéntico al de perfil -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo"><i class="fas fa-bus"></i> Busito SV</div>
                <div class="sidebar-subtitle">Tracking Terminal</div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item" data-url="/TRACKING_TERMINAL/views/layouts/menu_general.php">
                    <i class="fas fa-home"></i> <span>Inicio</span>
                </div>
                <div class="nav-item" data-url="/TRACKING_TERMINAL/views/layouts/perfil.php">
                    <i class="fas fa-user-circle"></i> <span>Ver Perfil</span>
                </div>
                <div class="nav-item active" data-url="/TRACKING_TERMINAL/views/layouts/editar_perfil.php">
                    <i class="fas fa-user-edit"></i> <span>Editar Perfil</span>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div class="profile-container">
                <div class="profile-card">
                    <!-- Cabecera azul con avatar centrado (igual que en perfil) -->
                    <div class="profile-header">
                        <div class="avatar-wrapper">
                            <?php if ($usuario['foto_perfil'] && $usuario['foto_perfil'] != 'default.png'): ?>
                                <img src="/TRACKING_TERMINAL/assets/img/profiles/<?php echo $usuario['foto_perfil']; ?>" class="avatar" id="avatarImg">
                            <?php else: ?>
                                <div class="avatar avatar-initials" style="background-color: <?php echo $bg_color; ?>;">
                                    <?php echo $iniciales; ?>
                                </div>
                            <?php endif; ?>
                            <!-- Botón para cambiar foto (opcional, puedes mantenerlo) -->
                            <label for="profile_image" class="camera-icon" style="cursor:pointer;">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>
                    </div>

                    <!-- Formulario para actualizar foto (oculto) -->
                    <form method="POST" enctype="multipart/form-data" id="photoForm" style="display:none;">
                        <input type="file" name="profile_image" id="profile_image" accept="image/jpeg, image/png">
                        <button type="submit" name="upload_photo" id="uploadPhotoBtn">Subir</button>
                    </form>

                    <!-- Información del usuario (solo texto) -->
                    <div class="profile-info">
                        <h2 class="user-name"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></h2>
                        <p class="user-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($usuario['correo']); ?></p>
                        <p class="user-role">@<?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
                    </div>

                    <!-- Alertas -->
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error_msg; ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de edición de datos -->
                    <form method="POST" action="" class="edit-form">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Nombre completo</label>
                            <input type="text" name="nombre_completo" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-at"></i> Nombre de usuario</label>
                            <input type="text" name="nombre_usuario" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Correo electrónico</label>
                            <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone-alt"></i> Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Nueva contraseña (dejar en blanco para no cambiar)</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="form-group d-flex justify-content-between align-items-center mt-4">
                            <a href="/TRACKING_TERMINAL/views/layouts/perfil.php" class="cancel-link"><i class="fas fa-arrow-left"></i> Volver al perfil</a>
                            <button type="submit" name="update_profile" class="btn-save"><i class="fas fa-save"></i> Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts (mismo que en perfil.php) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Móvil sidebar
        const menuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        menuBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Navegación sidebar
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                const url = this.dataset.url;
                if (url) window.location.href = url;
            });
        });

        // Subir foto (opcional, igual que en perfil)
        document.getElementById('profile_image')?.addEventListener('change', function(e) {
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
                    document.getElementById('uploadPhotoBtn')?.click();
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
    <?php
    // Procesar subida de foto si viene del formulario oculto
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_photo']) && isset($_FILES['profile_image'])) {
        // Código idéntico al de perfil.php para subir foto
        $file = $_FILES['profile_image'];
        if ($file['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file['type'], $allowed_types)) {
                $error_msg = "Formato no permitido. Use JPG o PNG";
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $error_msg = "La imagen no debe superar los 2MB";
            } else {
                $upload_dir = $root . '/assets/img/profiles/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
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
                        $success_msg = "Foto actualizada correctamente";
                        // Recargar datos
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
        // Redirigir para evitar reenvío del formulario
        echo '<script>window.location.href = window.location.pathname;</script>';
        exit();
    }
    ?>
</body>
</html>