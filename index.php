<?php
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdo = require_once "config/database.php";
    require_once "controller/UserController.php";
    $auth = new UserController($pdo);
    $error = $auth->login($_POST['usuario'], $_POST['password']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Busito SV | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>
    <div class="flat-background"></div>
    <div class="bg-pattern"></div>
    
    <div class="deco-rect deco-rect-1"></div>
    <div class="deco-rect deco-rect-2"></div>
    <div class="deco-rect deco-rect-3"></div>
    
    <div class="login-wrapper">
        <div class="flat-card">
            <div class="brand-horizontal">
                <div class="logo-container">
                    <img src="/TRACKING_TERMINAL/assets/img/logobus.png" alt="Busito SV Logo" class="logo-img" id="systemLogo">
                </div>
                <div class="brand-text">
                    <h1>Busito <span>SV</span></h1>
                    <p><i class="fas fa-map-marked-alt"></i> Gestión de rutas en tiempo real</p>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb; font-family: 'Inter'; font-size: 14px;">
                    <i class="fas fa-check-circle"></i> ¡Listo! Ahora puedes iniciar sesión.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #f5c6cb; font-family: 'Inter'; font-size: 14px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form class="login-form" action="index.php" method="POST" id="loginForm">
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" name="usuario" id="usuario" autocomplete="username" placeholder=" " required>
                    <label for="usuario">Usuario</label>
                </div>
                
                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" autocomplete="current-password" placeholder=" " required>
                    <label for="password">Contraseña</label>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Ingresar</span>
                </button>
                
                <div class="links-row">
                    <a href="/TRACKING_TERMINAL/views/register.php"><i class="fas fa-user-plus"></i> Registrate</a>
                    <a href="#"><i class="fas fa-key"></i> ¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>

    <div class="tracking-element">
        <i class="fas fa-satellite-dish"></i> TERMINAL ONLINE · SEGUIMIENTO ACTIVO
    </div>
</body>
</html>