// /TRACKING_TERMINAL/assets/js/perfil.js

document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos del modal
    const modal = document.getElementById('editModal');
    const editBtn = document.getElementById('editProfileBtn');
    const closeBtn = document.querySelector('.modal-close');
    const cancelBtn = document.getElementById('modalCancelBtn');
    const editForm = document.getElementById('editProfileForm');
    const loadingModal = document.getElementById('loadingModal');
    
    // Variables para la vista previa
    let currentAvatarSrc = null;
    
    // Abrir modal
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Cerrar modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Vista previa de imagen en el modal
    const modalProfileInput = document.getElementById('modal_profile_image');
    const modalAvatarImg = document.getElementById('modalAvatar');
    const modalAvatarInitials = document.getElementById('modalAvatarInitials');
    
    if (modalProfileInput) {
        modalProfileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (modalAvatarImg) {
                        modalAvatarImg.src = e.target.result;
                    } else if (modalAvatarInitials) {
                        // Reemplazar iniciales por imagen
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = 'Foto de perfil';
                        newImg.className = 'modal-avatar';
                        newImg.id = 'modalAvatar';
                        modalAvatarInitials.parentNode.replaceChild(newImg, modalAvatarInitials);
                    }
                };
                
                reader.readAsDataURL(file);
                
                // Validar tipo de archivo
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    alert('Solo se permiten formatos JPG y PNG');
                    this.value = '';
                    return;
                }
                
                // Validar tamaño (2MB máximo)
                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen no debe superar los 2MB');
                    this.value = '';
                    return;
                }
            }
        });
    }
    
    // Enviar formulario vía AJAX
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Mostrar loading
            loadingModal.style.display = 'flex';
            
            // Recoger datos del formulario
            const formData = new FormData();
            formData.append('update_profile', '1');
            formData.append('nombre_completo', document.getElementById('modal_nombre').value);
            formData.append('nombre_usuario', document.getElementById('modal_usuario').value);
            formData.append('correo', document.getElementById('modal_correo').value);
            formData.append('telefono', document.getElementById('modal_telefono').value);
            
            // Si hay imagen seleccionada, incluirla
            const modalFileInput = document.getElementById('modal_profile_image');
            if (modalFileInput.files.length > 0) {
                formData.append('profile_image', modalFileInput.files[0]);
                formData.append('upload_photo', '1');
            }
            
            // Enviar AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                // Recargar la página para ver los cambios
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                loadingModal.style.display = 'none';
                alert('Error al guardar los cambios');
            });
        });
    }
    
    // Vista previa de la imagen de perfil (cámara en el header)
    const profileInput = document.getElementById('profile_image');
    const uploadBtn = document.getElementById('uploadPhotoBtn');
    const avatarImg = document.getElementById('profileAvatar');
    const avatarInitials = document.querySelector('.avatar-initials');
    
    if (profileInput) {
        profileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (avatarImg) {
                        avatarImg.src = e.target.result;
                    } else if (avatarInitials) {
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = 'Foto de perfil';
                        newImg.className = 'avatar';
                        newImg.id = 'profileAvatar';
                        avatarInitials.parentNode.replaceChild(newImg, avatarInitials);
                    }
                };
                
                reader.readAsDataURL(file);
                
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    alert('Solo se permiten formatos JPG y PNG');
                    this.value = '';
                    return;
                }
                
                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen no debe superar los 2MB');
                    this.value = '';
                    return;
                }
                
                if (uploadBtn) {
                    uploadBtn.click();
                }
            }
        });
    }
    
    // Ocultar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert) alert.style.display = 'none';
                }, 300);
            });
        }, 5000);
    }
    
    // Confirmación antes de cerrar sesión
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                e.preventDefault();
            }
        });
    }
    
    console.log('Perfil.js cargado correctamente ✅');
});