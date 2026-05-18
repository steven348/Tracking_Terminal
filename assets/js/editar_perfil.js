document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos del sidebar
    const sidebar = document.getElementById('sidebar');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const navItems = document.querySelectorAll('.nav-item');
    const editForm = document.getElementById('editProfileForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const confirmModal = document.getElementById('confirmModal');
    const confirmYes = document.getElementById('confirmYes');
    const confirmNo = document.getElementById('confirmNo');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    
    let pendingUrl = null;
    let formHasChanges = false;
    
    // Obtener datos originales desde elementos data-* o desde valores iniciales
    let originalData = {};
    
    // Capturar valores originales al cargar la página
    function captureOriginalData() {
        originalData = {
            nombre_completo: document.getElementById('nombre_completo').value,
            nombre_usuario: document.getElementById('nombre_usuario').value,
            correo: document.getElementById('correo').value,
            telefono: document.getElementById('telefono').value
        };
    }
    
    // Detectar cambios en el formulario
    function detectChanges() {
        const currentNombre = document.getElementById('nombre_completo').value;
        const currentUsuario = document.getElementById('nombre_usuario').value;
        const currentCorreo = document.getElementById('correo').value;
        const currentTelefono = document.getElementById('telefono').value;
        
        formHasChanges = (
            currentNombre !== originalData.nombre_completo ||
            currentUsuario !== originalData.nombre_usuario ||
            currentCorreo !== originalData.correo ||
            currentTelefono !== originalData.telefono
        );
        
        return formHasChanges;
    }
    
    // Capturar valores originales al inicio
    captureOriginalData();
    
    // Escuchar cambios en los inputs
    const inputs = ['nombre_completo', 'nombre_usuario', 'correo', 'telefono'];
    inputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', detectChanges);
        }
    });
    
    // También detectar cambios en la foto de perfil
    const profileImageInput = document.getElementById('profile_image');
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function() {
            formHasChanges = true;
        });
    }
    
    // Función para mostrar modal de confirmación
    function showConfirmModal(url) {
        if (detectChanges()) {
            pendingUrl = url;
            if (confirmModal) {
                confirmModal.style.display = 'flex';
            }
        } else {
            window.location.href = url;
        }
    }
    
    // Navegación del sidebar con confirmación
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            const url = this.getAttribute('data-url');
            if (url && url !== window.location.pathname) {
                e.preventDefault();
                showConfirmModal(url);
            }
        });
    });
    
    // Botón cancelar
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            showConfirmModal(url);
        });
    }
    
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
    
    // Cerrar sidebar al redimensionar a desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
    
    // Vista previa de imagen
    const profileInput = document.getElementById('profile_image');
    const editAvatar = document.getElementById('editAvatar');
    const editAvatarInitials = document.getElementById('editAvatarInitials');
    
    if (profileInput) {
        profileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (editAvatar) {
                        editAvatar.src = e.target.result;
                    } else if (editAvatarInitials) {
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = 'Foto de perfil';
                        newImg.className = 'edit-avatar';
                        newImg.id = 'editAvatar';
                        editAvatarInitials.parentNode.replaceChild(newImg, editAvatarInitials);
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
                
                formHasChanges = true; // Marcar que hay cambios
            }
        });
    }
    
    // Mostrar loading al enviar formulario
    if (editForm) {
        editForm.addEventListener('submit', function() {
            if (loadingOverlay) {
                loadingOverlay.style.display = 'flex';
            }
        });
    }
    
    console.log('EditarPerfil.js cargado correctamente ✅');
});