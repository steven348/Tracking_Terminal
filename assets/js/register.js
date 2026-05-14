// /TRACKING_TERMINAL/assets/js/register.js

// Vista previa de la imagen de perfil
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        const avatar = document.getElementById('profileAvatar');
        
        reader.onload = function(e) {
            // Limpiar el contenido actual
            avatar.innerHTML = '';
            // Crear la imagen de vista previa
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            avatar.appendChild(img);
        };
        
        reader.readAsDataURL(file);
        
        // Validar tamaño y tipo
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
            alert('Solo se permiten formatos JPG y PNG');
            this.value = '';
            // Restaurar icono por defecto
            avatar.innerHTML = '<i class="fas fa-user-circle"></i>';
            return;
        }
        
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen no debe superar los 2MB');
            this.value = '';
            avatar.innerHTML = '<i class="fas fa-user-circle"></i>';
            return;
        }
    }
});

// Validaciones del formulario
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const terminos = document.getElementById('terminos');
    const btnSubmit = document.getElementById('btnSubmit');
    
    // Validar contraseñas
    if (password !== confirm) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
    
    // Validar longitud de contraseña
    if (password.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return false;
    }
    
    // Validar términos y condiciones
    if (!terminos.checked) {
        e.preventDefault();
        alert('Debes aceptar los términos y condiciones');
        return false;
    }
    
    // Mostrar loading
    btnSubmit.classList.add('loading');
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Registrando...';
});

// Limpiar mensaje de error al empezar a escribir
const inputs = document.querySelectorAll('input');
inputs.forEach(input => {
    input.addEventListener('focus', function() {
        const errorDiv = document.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    });
});