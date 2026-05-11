<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Busito SV | Registro de Usuario</title>
    <!-- Fuentes y estilos base -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- CSS principal para registro -->
    <link rel="stylesheet" href="/TRACKING_TERMINAL/assets/css/register.css">
</head>
<body>

    <!-- FONDO FLAT -->
    <div class="flat-background"></div>
    <div class="bg-pattern"></div>
    
    <!-- Decoraciones planas -->
    <div class="deco-rect deco-rect-1"></div>
    <div class="deco-rect deco-rect-2"></div>
    <div class="deco-rect deco-rect-3"></div>
    
    <div class="register-wrapper">
        <!-- TARJETA PLANA DE REGISTRO -->
        <div class="flat-card">
            <!-- LOGO Y TEXTO ALINEADOS HORIZONTALMENTE -->
            <div class="brand-horizontal">
                <div class="logo-container">
                    <img src="/TRACKING_TERMINAL/assets/img/logobus.png" alt="Busito SV Logo" class="logo-img">
                </div>
                <div class="brand-text">
                    <h1>Busito <span>SV</span></h1>
                    <p><i class="fas fa-user-plus"></i> Crea tu cuenta y comienza a gestionar</p>
                </div>
            </div>

            <form class="register-form" action="register_process.php" method="POST" enctype="multipart/form-data" id="registerForm">
                <!-- SECCIÓN: FOTO DE PERFIL -->
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

                <!-- Campo: Nombre completo -->
                <div class="input-field">
                    <i class="fas fa-user-circle"></i>
                    <input type="text" name="nombre_completo" id="nombre_completo" autocomplete="name" placeholder=" ">
                    <label for="nombre_completo">Nombre completo</label>
                </div>

                <!-- Campo: Correo electrónico -->
                <div class="input-field">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" autocomplete="email" placeholder=" ">
                    <label for="email">Correo electrónico</label>
                </div>

                <!-- Campo: Teléfono -->
                <div class="input-field">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="telefono" id="telefono" autocomplete="tel" placeholder=" ">
                    <label for="telefono">Teléfono</label>
                </div>

                <!-- Campo: Usuario -->
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" name="usuario" id="usuario" autocomplete="username" placeholder=" ">
                    <label for="usuario">Usuario</label>
                </div>

                <!-- Campo: Contraseña -->
                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" autocomplete="new-password" placeholder=" ">
                    <label for="password">Contraseña</label>
                </div>

                <!-- Campo: Confirmar contraseña -->
                <div class="input-field">
                    <i class="fas fa-check-circle"></i>
                    <input type="password" name="confirm_password" id="confirm_password" autocomplete="off" placeholder=" ">
                    <label for="confirm_password">Confirmar contraseña</label>
                </div>

                <!-- Términos y condiciones -->
                <div class="terms-field">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terminos" id="terminos">
                        <span class="checkmark"></span>
                        <span class="terms-text">Acepto los <a href="#">términos y condiciones</a> y la <a href="#">política de privacidad</a></span>
                    </label>
                </div>

                <!-- Botón de registro -->
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    <span>Registrarme</span>
                </button>
                
                <!-- Enlace para volver al login -->
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