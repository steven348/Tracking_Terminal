(function() {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('loginBtn');
    const video = document.getElementById('bgVideo');
    const soundToggle = document.getElementById('soundToggle');
    
    // --- CONTROL DE VIDEO: asegurar reproducción y manejo de errores ---
    if (video) {
        // Forzar reproducción incluso en móviles (autoplay con muted es soportado)
        video.muted = true;
        video.play().catch(error => {
            console.log("Autoplay bloqueado por el navegador, pero se intentará con interacción del usuario.");
        });
        
        // Si el video tiene problemas de carga, mostrar alternativa silenciosa
        video.addEventListener('error', function(e) {
            console.warn("Error cargando el video. Verifica la ruta: /TRACKING_TERMINAL/assets/img/video/busespelea.mp4");
            const videoContainer = document.querySelector('.video-background');
            if (videoContainer) {
                videoContainer.style.backgroundColor = '#071A2D';
            }
        });
        
        // Loop: si por algún motivo termina, reiniciar
        video.addEventListener('ended', function() {
            video.currentTime = 0;
            video.play().catch(e => console.log("Replay automático falló"));
        });
    }
    
    // --- Botón para activar/desactivar sonido ---
    if (soundToggle && video) {
        let isMuted = true;
        soundToggle.addEventListener('click', function() {
            if (isMuted) {
                video.muted = false;
                soundToggle.innerHTML = '<i class="fas fa-volume-up"></i>';
                isMuted = false;
                showTemporaryMessage('🔊 Audio activado', 1500);
            } else {
                video.muted = true;
                soundToggle.innerHTML = '<i class="fas fa-volume-mute"></i>';
                isMuted = true;
                showTemporaryMessage('🔇 Audio silenciado', 1500);
            }
        });
    }
    
    // Función auxiliar para mensajes temporales
    function showTemporaryMessage(msg, duration) {
        const msgDiv = document.createElement('div');
        msgDiv.textContent = msg;
        msgDiv.style.position = 'fixed';
        msgDiv.style.bottom = '80px';
        msgDiv.style.right = '20px';
        msgDiv.style.backgroundColor = '#1C1C1C';
        msgDiv.style.color = '#00C2C7';
        msgDiv.style.padding = '6px 14px';
        msgDiv.style.borderRadius = '30px';
        msgDiv.style.fontSize = '0.75rem';
        msgDiv.style.fontWeight = '500';
        msgDiv.style.zIndex = '999';
        msgDiv.style.backdropFilter = 'blur(8px)';
        msgDiv.style.border = '1px solid #3AD1D6';
        msgDiv.style.fontFamily = 'Inter, sans-serif';
        msgDiv.style.opacity = '0';
        msgDiv.style.transition = 'opacity 0.2s';
        document.body.appendChild(msgDiv);
        setTimeout(() => { msgDiv.style.opacity = '1'; }, 10);
        setTimeout(() => {
            msgDiv.style.opacity = '0';
            setTimeout(() => msgDiv.remove(), 300);
        }, duration);
    }
    
    // --- Validación del formulario ---
    if (form) {
        form.addEventListener('submit', function(e) {
            if (btn.classList.contains('loading')) return;
            
            const usuario = document.getElementById('usuario');
            const password = document.getElementById('password');
            let hasError = false;
            
            if (!usuario.value.trim()) {
                showFieldError(usuario);
                hasError = true;
            }
            
            if (!password.value.trim()) {
                showFieldError(password);
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
                showWarningMessage('⚠️ Complete usuario y contraseña');
                return;
            }
            
            // Mostrar estado de carga
            btn.classList.add('loading');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Validando acceso...';
            
            // Timeout para evitar loading infinito si el servidor no responde
            setTimeout(() => {
                if (btn.classList.contains('loading')) {
                    btn.classList.remove('loading');
                    btn.innerHTML = originalHtml;
                    showTemporaryMessage('⚠️ El servidor tarda en responder, verifica tu conexión', 3000);
                }
            }, 10000);
        });
    }
    
    function showFieldError(inputElement) {
        inputElement.style.borderColor = '#ff8a7a';
        inputElement.style.transition = 'border-color 0.1s';
        setTimeout(() => {
            inputElement.style.borderColor = '';
        }, 1500);
    }
    
    function showWarningMessage(message) {
        const warnMsg = document.createElement('div');
        warnMsg.textContent = message;
        warnMsg.style.position = 'fixed';
        warnMsg.style.bottom = '30px';
        warnMsg.style.left = '50%';
        warnMsg.style.transform = 'translateX(-50%)';
        warnMsg.style.backgroundColor = '#1C1C1C';
        warnMsg.style.color = '#00C2C7';
        warnMsg.style.padding = '10px 20px';
        warnMsg.style.borderRadius = '40px';
        warnMsg.style.fontSize = '0.85rem';
        warnMsg.style.fontWeight = '500';
        warnMsg.style.zIndex = '999';
        warnMsg.style.backdropFilter = 'blur(8px)';
        warnMsg.style.border = '1px solid #3AD1D6';
        warnMsg.style.fontFamily = 'Inter, sans-serif';
        warnMsg.style.boxShadow = '0 4px 15px rgba(0,0,0,0.3)';
        document.body.appendChild(warnMsg);
        
        setTimeout(() => {
            warnMsg.style.opacity = '0';
            warnMsg.style.transition = 'opacity 0.3s ease';
            setTimeout(() => warnMsg.remove(), 300);
        }, 2800);
    }
    
    // Microinteracciones: efecto de escala al enfocar
    const inputFields = document.querySelectorAll('.input-field');
    inputFields.forEach(field => {
        const input = field.querySelector('input');
        if (input) {
            input.addEventListener('focus', () => {
                field.style.transform = 'scale(1.01)';
                field.style.transition = 'transform 0.2s ease';
            });
            input.addEventListener('blur', () => {
                field.style.transform = 'scale(1)';
            });
        }
    });
    
    // Toggle de visibilidad de contraseña (mejora UX)
    const passwordField = document.getElementById('password');
    if (passwordField) {
        const parentDiv = passwordField.closest('.input-field');
        const toggleIcon = document.createElement('i');
        toggleIcon.className = 'fas fa-eye-slash';
        toggleIcon.style.position = 'absolute';
        toggleIcon.style.right = '16px';
        toggleIcon.style.left = 'auto';
        toggleIcon.style.top = '50%';
        toggleIcon.style.transform = 'translateY(-50%)';
        toggleIcon.style.cursor = 'pointer';
        toggleIcon.style.color = '#00C2C7';
        toggleIcon.style.fontSize = '1rem';
        toggleIcon.style.zIndex = '3';
        toggleIcon.style.opacity = '0.7';
        toggleIcon.style.transition = 'opacity 0.2s';
        
        toggleIcon.addEventListener('mouseenter', () => {
            toggleIcon.style.opacity = '1';
        });
        toggleIcon.addEventListener('mouseleave', () => {
            toggleIcon.style.opacity = '0.7';
        });
        
        toggleIcon.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        parentDiv.appendChild(toggleIcon);
        passwordField.style.paddingRight = '2.8rem';
    }
    
    // Limpiar borde de error al escribir
    const allInputs = document.querySelectorAll('.input-field input');
    allInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.style.borderColor = '';
        });
    });
    
    // Manejo de error de carga del logo
    const logoImg = document.getElementById('systemLogo');
    if (logoImg) {
        logoImg.addEventListener('error', function() {
            console.warn('Logo no encontrado en la ruta especificada');
            this.style.display = 'none';
        });
    }
    
    // Detectar si el dispositivo es táctil
    if ('ontouchstart' in window) {
        const tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(el => el.removeAttribute('title'));
    }
})();