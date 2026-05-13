<?php
$error_reg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $root = dirname(__DIR__); 
    $pdo = require_once $root . "/config/database.php";
    require_once $root . "/controller/UserController.php";
    $auth = new UserController($pdo);
    
    if ($_POST['password'] === $_POST['confirm_password']) {
        $res = $auth->registro($_POST, $_FILES);
        if ($res === "exito") {
            header("Location: ../index.php?success=1");
            exit();
        } else {
            $error_reg = $res;
        }
    } else {
        $error_reg = "Las contraseñas no coinciden.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Busito SV | Registro de Usuario</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/register.css">
</head>
<body>
    <div class="flat-background"></div>
    <div class="bg-pattern"></div>
    
    <div class="deco-rect deco-rect-1"></div>
    <div class="deco-rect deco-rect-2"></div>
    <div class="deco-rect deco-rect-3"></div>
    
    <div class="register-wrapper">
        <div class="flat-card">
            <div class="brand-horizontal">
                <div class="logo-container">
                    <img src="/TRACKING_TERMINAL/assets/img/logobus.png" alt="Busito SV Logo" class="logo-img">
                </div>
                <div class="brand-text">
                    <h1>Busito <span>SV</span></h1>
                    <p><i class="fas fa-user-plus"></i> Crea tu cuenta y comienza a gestionar</p>
                </div>
            </div>

            <?php if ($error_reg): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #f5c6cb; font-family: 'Inter'; font-size: 14px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_reg; ?>
                </div>
            <?php endif; ?>

            <form class="register-form" action="register.php" method="POST" enctype="multipart/form-data" id="registerForm">
                <div class="profile-section">
                    <div class="profile-image-container">
                        <div class="profile-avatar" id="profileAvatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="profile-image-overlay">
                            <label for="profile_image" class="upload-label">
                                <i class="fas fa-camera"></i>
                                <span>Subir foto</span>
                            </label>
                        </div>
                    </div>
                    <input type="file" name="profile_image" id="profile_image" accept="image/jpeg, image/png, image/jpg" class="profile-input">
                    <p class="upload-hint"><i class="fas fa-info-circle"></i> Formatos permitidos: JPG, PNG. Máx. 2MB</p>
                </div>

                <div class="form-grid">
                    <div class="input-field">
                        <i class="fas fa-user-circle"></i>
                        <input type="text" name="nombre_completo" id="nombre_completo" required placeholder=" ">
                        <label for="nombre_completo">Nombre completo</label>
                    </div>

                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" required placeholder=" ">
                        <label for="email">Correo electrónico</label>
                    </div>

                    <div class="input-field">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="telefono" id="telefono" required placeholder=" ">
                        <label for="telefono">Teléfono</label>
                    </div>

                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="usuario" id="usuario" required placeholder=" ">
                        <label for="usuario">Nombre de usuario</label>
                    </div>

                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" required placeholder=" ">
                        <label for="password">Contraseña</label>
                    </div>

                    <div class="input-field">
                        <i class="fas fa-check-circle"></i>
                        <input type="password" name="confirm_password" id="confirm_password" required placeholder=" ">
                        <label for="confirm_password">Confirmar contraseña</label>
                    </div>

                    <div class="terms-field">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terminos" id="terminos" required>
                            <span class="checkmark"></span>
                            <span class="terms-text">Acepto los <a href="#">términos y condiciones</a></span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    <span>Registrarme</span>
                </button>
                
                <div class="login-link">
                    <a href="/TRACKING_TERMINAL/index.php">
                        <i class="fas fa-sign-in-alt"></i> ¿Ya tienes cuenta? Inicia sesión
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="tracking-element">
        <i class="fas fa-satellite-dish"></i> TERMINAL ONLINE · REGISTRO SEGURO
    </div>
</body>
</html>