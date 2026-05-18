document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos del sidebar
    const sidebar = document.getElementById('sidebar');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const navItems = document.querySelectorAll('.nav-item');
    const confirmModal = document.getElementById('confirmModal');
    const confirmYes = document.getElementById('confirmYes');
    const confirmNo = document.getElementById('confirmNo');
    
    let pendingUrl = null;
    let hasChanges = false;
    
    // Funciones del sidebar
    function openSidebar() {
        sidebar.classList.add('open');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', openSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    // Detectar cambios (en perfil normal no hay cambios que detectar, pero mantenemos la estructura)
    function checkForChanges() {
        return false; // En la vista de perfil no hay formulario de edición
    }
    
    // Mostrar modal de confirmación
    function showConfirmModal(url) {
        pendingUrl = url;
        if (confirmModal) {
            confirmModal.style.display = 'flex';
        }
    }
    
    // Navegación del sidebar con confirmación
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            const url = this.getAttribute('data-url');
            if (url && url !== window.location.pathname) {
                if (checkForChanges()) {
                    e.preventDefault();
                    showConfirmModal(url);
                } else {
                    window.location.href = url;
                }
            }
        });
    });
    
    // Confirmar salida
    if (confirmYes) {
        confirmYes.addEventListener('click', function() {
            if (pendingUrl) {
                window.location.href = pendingUrl;
            }
            if (confirmModal) {
                confirmModal.style.display = 'none';
            }
        });
    }
    
    // Cancelar salida
    if (confirmNo) {
        confirmNo.addEventListener('click', function() {
            pendingUrl = null;
            if (confirmModal) {
                confirmModal.style.display = 'none';
            }
        });
    }
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(e) {
        if (e.target === confirmModal) {
            confirmModal.style.display = 'none';
            pendingUrl = null;
        }
    });
    
    // Cerrar sidebar al redimensionar a desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
    
    // Vista previa de la imagen de perfil
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
    
    // Mostrar mensaje de éxito si viene por URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success';
        successDiv.innerHTML = '<i class="fas fa-check-circle"></i> Perfil actualizado correctamente';
        const profileCard = document.querySelector('.profile-card');
        const profileInfo = document.querySelector('.profile-info');
        if (profileCard && profileInfo) {
            profileCard.insertBefore(successDiv, profileInfo);
            setTimeout(() => {
                successDiv.style.transition = 'opacity 0.3s ease';
                successDiv.style.opacity = '0';
                setTimeout(() => successDiv.remove(), 300);
            }, 5000);
        }
    }
    
    console.log('Perfil.js cargado correctamente ✅');
});