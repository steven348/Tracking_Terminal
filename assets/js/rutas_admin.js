document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadKmlForm');
    const kmlFile = document.getElementById('kmlFile');
    const progressModal = document.getElementById('progressModal');
    const progressBar = document.getElementById('progressBar');
    const rutasCount = document.getElementById('rutasCount');
    const puntosCount = document.getElementById('puntosCount');
    const progressPercent = document.getElementById('progressPercent');
    const progressStatus = document.getElementById('progressStatus');
    const progressButtons = document.getElementById('progressButtons');
    const btnOk = document.getElementById('btnOk');
    const refreshBtn = document.getElementById('refreshRoutes');
    
    const editRouteModal = document.getElementById('editRouteModal');
    const closeModal = document.querySelector('.close-modal');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const saveRouteBtn = document.getElementById('saveRouteBtn');
    
    let progressInterval = null;
    let currentRouteId = null;
    
    // ==================== FUNCIONES DE ALERTAS ====================
    
    function showToast(message, type = 'success', title = '') {
        const titles = {
            success: '¡Éxito!',
            error: '¡Error!',
            warning: '¡Atención!',
            info: 'Información'
        };
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        const toastContainer = document.getElementById('toastContainer') || (() => {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-alert';
            document.body.appendChild(container);
            return container;
        })();
        
        const toast = document.createElement('div');
        toast.className = `alert-custom alert-custom-${type}`;
        toast.innerHTML = `
            <div class="alert-icon"><i class="fas ${icons[type]}"></i></div>
            <div class="alert-content">
                <div class="alert-title">${title || titles[type]}</div>
                <div class="alert-message">${message}</div>
            </div>
            <button class="alert-close"><i class="fas fa-times"></i></button>
        `;
        
        toastContainer.appendChild(toast);
        
        const closeBtn = toast.querySelector('.alert-close');
        closeBtn.addEventListener('click', () => {
            toast.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        });
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }
    
    function showConfirmModal(options) {
        return new Promise((resolve) => {
            const modalContainer = document.getElementById('confirmModalContainer') || (() => {
                const container = document.createElement('div');
                container.id = 'confirmModalContainer';
                container.className = 'modal-confirm-custom';
                document.body.appendChild(container);
                return container;
            })();
            
            const iconClass = options.type === 'danger' ? 'fa-exclamation-triangle' : 
                             (options.type === 'warning' ? 'fa-exclamation-triangle' : 'fa-question-circle');
            const iconColor = options.type === 'danger' ? 'danger' : 
                             (options.type === 'warning' ? 'warning' : 'success');
            
            modalContainer.innerHTML = `
                <div class="modal-confirm-overlay"></div>
                <div class="modal-confirm-container">
                    <div class="modal-confirm-icon ${iconColor}">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <h3 class="modal-confirm-title">${options.title}</h3>
                    <p class="modal-confirm-message">${options.message}</p>
                    <div class="modal-confirm-buttons">
                        <button class="modal-confirm-btn modal-confirm-btn-cancel" id="confirmCancelBtn">${options.cancelText || 'Cancelar'}</button>
                        <button class="modal-confirm-btn modal-confirm-btn-confirm" id="confirmOkBtn">${options.confirmText || 'Aceptar'}</button>
                    </div>
                </div>
            `;
            
            modalContainer.style.display = 'flex';
            
            const confirmBtn = document.getElementById('confirmOkBtn');
            const cancelBtn = document.getElementById('confirmCancelBtn');
            const overlay = modalContainer.querySelector('.modal-confirm-overlay');
            
            const closeModal = () => {
                modalContainer.style.display = 'none';
                resolve(false);
            };
            
            const confirmAction = () => {
                modalContainer.style.display = 'none';
                resolve(true);
            };
            
            confirmBtn.addEventListener('click', confirmAction, { once: true });
            cancelBtn.addEventListener('click', closeModal, { once: true });
            overlay.addEventListener('click', closeModal, { once: true });
        });
    }
    
    // ==================== FUNCIONES PRINCIPALES ====================
    
    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const file = kmlFile.files[0];
        if (!file) {
            showToast('Por favor, selecciona un archivo KML', 'warning', 'Archivo no seleccionado');
            return;
        }
        
        progressModal.style.display = 'flex';
        progressButtons.style.display = 'none';
        progressStatus.style.display = 'flex';
        progressStatus.innerHTML = '<i class="fas fa-spinner fa-pulse"></i><span>Iniciando proceso...</span>';
        progressStatus.style.background = 'rgba(255,255,255,0.05)';
        progressStatus.style.color = 'white';
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';
        rutasCount.textContent = '0';
        puntosCount.textContent = '0';
        
        let simulatedProgress = 0;
        progressInterval = setInterval(() => {
            if (simulatedProgress < 90) {
                simulatedProgress += Math.random() * 10;
                if (simulatedProgress > 90) simulatedProgress = 90;
                progressBar.style.width = simulatedProgress + '%';
                progressPercent.textContent = Math.floor(simulatedProgress) + '%';
            }
        }, 500);
        
        const formData = new FormData();
        formData.append('kml_file', file);
        
        try {
            const response = await fetch('/TRACKING_TERMINAL/controller/process_kml.php', {
                method: 'POST',
                body: formData
            });
            
            clearInterval(progressInterval);
            
            const textResponse = await response.text();
            let result;
            try {
                result = JSON.parse(textResponse);
            } catch (e) {
                throw new Error('Respuesta inválida del servidor');
            }
            
            if (result.success) {
                progressBar.style.width = '100%';
                progressPercent.textContent = '100%';
                rutasCount.textContent = result.total_rutas;
                puntosCount.textContent = result.total_puntos;
                
                progressStatus.innerHTML = `
                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                    <span>¡Subida completa! Se procesaron ${result.total_rutas} rutas con ${result.total_puntos} puntos.</span>
                `;
                progressStatus.style.background = '#d1fae5';
                progressStatus.style.color = '#065f46';
                progressButtons.style.display = 'flex';
                
                showToast(`¡Subida completa! Se procesaron ${result.total_rutas} rutas con ${result.total_puntos} puntos.`, 'success', 'Importación exitosa');
                loadRoutes();
            } else {
                throw new Error(result.error || 'Error al procesar el archivo');
            }
        } catch (error) {
            clearInterval(progressInterval);
            progressStatus.innerHTML = `
                <i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i>
                <span>Error: ${error.message}</span>
            `;
            progressStatus.style.background = '#fee2e2';
            progressStatus.style.color = '#991b1b';
            
            showToast(error.message, 'error', 'Error en la importación');
            
            setTimeout(() => {
                progressModal.style.display = 'none';
            }, 4000);
        }
    });
    
    btnOk.addEventListener('click', function() {
        progressModal.style.display = 'none';
        kmlFile.value = '';
    });
    
    async function openEditRouteModal(id, isEditMode = true) {
        currentRouteId = id;
        editRouteModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        document.getElementById('modalLoading').style.display = 'block';
        document.getElementById('modalContent').style.display = 'none';
        
        try {
            const response = await fetch(`/TRACKING_TERMINAL/controller/routes_controller.php?action=get_one&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('edit_nombre_ruta').value = result.ruta.nombre_ruta || '';
                document.getElementById('edit_origen').value = result.ruta.origen || '';
                document.getElementById('edit_destino').value = result.ruta.destino || '';
                document.getElementById('edit_descripcion').value = result.ruta.descripcion || '';
                document.getElementById('edit_estado').value = result.ruta.estado ? '1' : '0';
                document.getElementById('total_puntos').value = result.ruta.total_puntos || 0;
                
                const busesSelect = document.getElementById('edit_buses_asignados');
                busesSelect.innerHTML = '';
                
                const busesAsignadosIds = new Set(result.buses_asignados.map(b => b.id_bus));
                
                if (result.todos_buses && result.todos_buses.length > 0) {
                    result.todos_buses.forEach(bus => {
                        const option = document.createElement('option');
                        option.value = bus.id_bus;
                        option.textContent = `${bus.placa} - ${bus.numero_unidad || 'Unidad sin número'} (${bus.modelo || 'Modelo no especificado'})`;
                        if (busesAsignadosIds.has(bus.id_bus)) {
                            option.selected = true;
                        }
                        busesSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No hay buses registrados';
                    option.disabled = true;
                    busesSelect.appendChild(option);
                }
                
                if (!isEditMode) {
                    document.querySelectorAll('#editRouteForm .form-control').forEach(input => {
                        input.disabled = true;
                    });
                    document.getElementById('saveRouteBtn').style.display = 'none';
                    document.getElementById('cancelEditBtn').innerHTML = '<i class="fas fa-times"></i> Cerrar';
                } else {
                    document.querySelectorAll('#editRouteForm .form-control').forEach(input => {
                        input.disabled = false;
                    });
                    document.getElementById('saveRouteBtn').style.display = 'flex';
                    document.getElementById('cancelEditBtn').innerHTML = '<i class="fas fa-times"></i> Cancelar';
                }
                
                document.getElementById('modalLoading').style.display = 'none';
                document.getElementById('modalContent').style.display = 'block';
            } else {
                throw new Error(result.error || 'Error al cargar la ruta');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast(error.message, 'error', 'Error al cargar');
            closeEditRouteModal();
        }
    }
    
    function closeEditRouteModal() {
        editRouteModal.style.display = 'none';
        document.body.style.overflow = '';
        currentRouteId = null;
        document.getElementById('modalLoading').style.display = 'block';
        document.getElementById('modalContent').style.display = 'none';
        document.getElementById('editRouteForm').reset();
        document.getElementById('saveRouteBtn').style.display = 'flex';
    }
    
    async function saveRouteChanges() {
        const busesSelect = document.getElementById('edit_buses_asignados');
        const selectedBuses = Array.from(busesSelect.selectedOptions).map(opt => parseInt(opt.value)).filter(v => !isNaN(v));
        
        const formData = {
            id_ruta: currentRouteId,
            nombre_ruta: document.getElementById('edit_nombre_ruta').value,
            origen: document.getElementById('edit_origen').value,
            destino: document.getElementById('edit_destino').value,
            descripcion: document.getElementById('edit_descripcion').value,
            estado: document.getElementById('edit_estado').value,
            buses_asignados: selectedBuses
        };
        
        if (!formData.nombre_ruta) {
            showToast('El nombre de la ruta es obligatorio', 'warning', 'Campo requerido');
            return;
        }
        
        const saveBtn = document.getElementById('saveRouteBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Guardando...';
        saveBtn.disabled = true;
        
        try {
            const response = await fetch('/TRACKING_TERMINAL/controller/update_route.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Ruta actualizada correctamente', 'success', '¡Guardado!');
                closeEditRouteModal();
                loadRoutes();
            } else {
                throw new Error(result.error || 'Error al actualizar la ruta');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast(error.message, 'error', 'Error al guardar');
        } finally {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    }
    
    async function loadRoutes() {
        try {
            const response = await fetch('/TRACKING_TERMINAL/controller/routes_controller.php?action=get_all');
            const textResponse = await response.text();
            
            let rutas;
            try {
                rutas = JSON.parse(textResponse);
            } catch (e) {
                console.error('Error parsing JSON:', textResponse);
                return;
            }
            
            if (rutas.error) {
                console.error('Error del servidor:', rutas.error);
                return;
            }
            
            const tbody = document.getElementById('routesTableBody');
            if (!tbody) return;
            
            if (!rutas.length || rutas.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="empty-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <p>No hay rutas registradas</p>
                            <small>Sube un archivo KML para comenzar</small>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = rutas.map(ruta => `
                <tr>
                    <td>${ruta.id_ruta}</td>
                    <td><strong>${escapeHtml(ruta.nombre_ruta)}</strong></td>
                    <td>${escapeHtml(ruta.origen || 'N/A')}</td>
                    <td>${escapeHtml(ruta.destino || 'N/A')}</td>
                    <td><span class="badge badge-buses"><i class="fas fa-bus"></i> ${ruta.total_buses || 0} buses</span></td>
                    <td><span class="badge ${ruta.estado ? 'badge-active' : 'badge-inactive'}">${ruta.estado ? 'Activa' : 'Inactiva'}</span></td>
                    <td class="action-buttons">
                        <button class="action-btn edit-route" data-id="${ruta.id_ruta}" title="Editar ruta">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn view-route" data-id="${ruta.id_ruta}" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn delete-route" data-id="${ruta.id_ruta}" title="Eliminar ruta">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            
            document.querySelectorAll('.edit-route').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    openEditRouteModal(id, true);
                });
            });
            
            document.querySelectorAll('.view-route').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    openEditRouteModal(id, false);
                });
            });
            
            document.querySelectorAll('.delete-route').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.getAttribute('data-id');
                    const confirmed = await showConfirmModal({
                        title: 'Eliminar Ruta',
                        message: '¿Estás seguro de que deseas eliminar esta ruta? Se perderán todos los datos asociados (puntos de ruta, asignaciones de buses).',
                        type: 'danger',
                        confirmText: 'Sí, eliminar',
                        cancelText: 'Cancelar'
                    });
                    
                    if (confirmed) {
                        try {
                            const response = await fetch('/TRACKING_TERMINAL/controller/delete_route.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: id })
                            });
                            const result = await response.json();
                            if (result.success) {
                                showToast('Ruta eliminada correctamente', 'success', 'Eliminada');
                                loadRoutes();
                            } else {
                                showToast(result.error, 'error', 'Error al eliminar');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            showToast('Error al eliminar la ruta', 'error', 'Error');
                        }
                    }
                });
            });
        } catch (error) {
            console.error('Error al cargar rutas:', error);
            showToast('Error al cargar las rutas', 'error', 'Error de carga');
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    if (refreshBtn) refreshBtn.addEventListener('click', loadRoutes);
    if (closeModal) closeModal.addEventListener('click', closeEditRouteModal);
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditRouteModal);
    if (saveRouteBtn) saveRouteBtn.addEventListener('click', saveRouteChanges);
    
    window.addEventListener('click', function(e) {
        if (e.target === editRouteModal) {
            closeEditRouteModal();
        }
        if (e.target === progressModal && progressButtons.style.display === 'flex') {
            progressModal.style.display = 'none';
        }
    });
    
    loadRoutes();
    console.log('Rutas Admin JS cargado correctamente ✅');
});