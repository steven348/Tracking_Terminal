// =====================================================
// VARIABLES GLOBALES
// =====================================================
var mapInstance = null;
var currentTracking = null;
var currentMarker = null;
var currentRouteLine = null;
var markerInterval = null;
var trackingsList = [];

// Coordenadas de El Salvador (centro aproximado)
var centroSV = [13.6929, -89.2182];

// =====================================================
// INICIALIZAR MAPA
// =====================================================
function initMap() {
    mapInstance = L.map('map-osm').setView(centroSV, 8);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CartoDB | Busito SV 2026',
        maxZoom: 19
    }).addTo(mapInstance);
}

// =====================================================
// CARGAR SOLO TRACKINGS CON SESIÓN ACTIVA
// =====================================================
function cargarTrackingsActivos() {
    var container = document.getElementById('routesContainer');
    if (!container) return;
    
    container.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Buscando trackings activos...</div>';
    
    fetch('/TRACKING_TERMINAL/api/get_tracking_activos.php')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar trackings</p></div>';
                return;
            }
            
            trackingsList = data.trackings || [];
            
            if (trackingsList.length === 0) {
                container.innerHTML = '<div class="empty-state">' +
                    '<i class="fas fa-bus"></i>' +
                    '<p>No hay trackings activos en este momento</p>' +
                    '<p style="font-size:0.8rem; margin-top:10px;">Inicia un tracking en "Realizar Tracking" para verlo aquí</p>' +
                    '</div>';
                document.getElementById('trackingCount').textContent = '0 activos';
                document.getElementById('trackingDetailsPanel').style.display = 'none';
                
                // Limpiar mapa si no hay trackings
                if (currentMarker) {
                    mapInstance.removeLayer(currentMarker);
                    currentMarker = null;
                }
                if (currentRouteLine) {
                    mapInstance.removeLayer(currentRouteLine);
                    currentRouteLine = null;
                }
                currentTracking = null;
                return;
            }
            
            document.getElementById('trackingCount').textContent = trackingsList.length + ' activos';
            renderTrackingsList(trackingsList);
            
            // Si hay trackings y no hay uno seleccionado, seleccionar el primero
            if (trackingsList.length > 0 && !currentTracking) {
                seleccionarTracking(trackingsList[0].id_session);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar trackings</p></div>';
        });
}

// =====================================================
// RENDERIZAR LISTA DE TRACKINGS ACTIVOS
// =====================================================
function renderTrackingsList(trackings) {
    var container = document.getElementById('routesContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    trackings.forEach(function(tracking) {
        var card = document.createElement('div');
        card.className = 'route-card compact';
        card.dataset.id = tracking.id_session;
        card.dataset.busId = tracking.id_bus;
        
        var segundos = tracking.segundos_sin_actualizar || 0;
        var minutos = Math.floor(segundos / 60);
        var segundosRestantes = segundos % 60;
        var tiempoTexto = segundos < 60 ? segundos + ' seg' : minutos + ' min ' + segundosRestantes + ' seg';
        
        var estadoClass = tracking.estado || 'on-time';
        var estadoTexto = tracking.estado_texto || 'Activo';
        
        var nombreRuta = tracking.nombre_ruta || 'Ruta en curso';
        var unidad = tracking.numero_unidad || 'Unidad ' + tracking.id_bus;
        var direccionIcon = tracking.direccion === 'IDA' ? 
            '<i class="fas fa-arrow-right" style="color: #00C2C7;"></i>' : 
            '<i class="fas fa-arrow-left" style="color: #00C2C7;"></i>';
        
        card.innerHTML = 
            '<div class="route-info">' +
                '<div class="route-icon"><i class="fas fa-bus"></i></div>' +
                '<div>' +
                    '<h4>' + nombreRuta + ' ' + direccionIcon + '</h4>' +
                    '<p>' + unidad + '</p>' +
                '</div>' +
            '</div>' +
            '<div class="route-time ' + estadoClass + '">' + tiempoTexto + '</div>' +
            '<div class="route-footer">' +
                '<span><i class="fas fa-map-marker-alt"></i> ' + tracking.terminal + '</span>' +
                '<span class="status-tag ' + estadoClass + '">' + estadoTexto + '</span>' +
            '</div>';
        
        card.addEventListener('click', function() {
            seleccionarTracking(tracking.id_session);
        });
        
        container.appendChild(card);
    });
}

// =====================================================
// SELECCIONAR TRACKING Y MOSTRAR DETALLE
// =====================================================
function seleccionarTracking(idSession) {
    // Marcar tarjeta como activa
    document.querySelectorAll('.route-card').forEach(function(card) {
        card.classList.remove('active');
        if (card.dataset.id == idSession) {
            card.classList.add('active');
        }
    });
    
    // Buscar el tracking en la lista
    var tracking = trackingsList.find(function(t) {
        return t.id_session == idSession;
    });
    
    if (tracking) {
        mostrarDetallesTracking(tracking);
        actualizarMapa(tracking);
    }
}

// =====================================================
// MOSTRAR DETALLES DEL TRACKING
// =====================================================
function mostrarDetallesTracking(tracking) {
    document.getElementById('detailUnidad').textContent = tracking.numero_unidad || 'Unidad ' + tracking.id_bus;
    document.getElementById('detailPlaca').textContent = tracking.placa || 'TEMP-001';
    document.getElementById('detailRuta').textContent = tracking.nombre_ruta || 'Ruta en curso';
    document.getElementById('detailOrigen').textContent = tracking.origen || 'No especificado';
    document.getElementById('detailDestino').textContent = tracking.destino || 'No especificado';
    document.getElementById('detailVelocidad').textContent = tracking.velocidad ? tracking.velocidad + ' km/h' : 'N/D';
    document.getElementById('detailTerminal').textContent = tracking.terminal || 'No especificada';
    document.getElementById('detailDireccion').innerHTML = tracking.direccion === 'IDA' ? 
        '<span style="color: #00C2C7;"><i class="fas fa-arrow-right"></i> IDA</span>' : 
        '<span style="color: #00C2C7;"><i class="fas fa-arrow-left"></i> REGRESO</span>';
    
    var segundos = tracking.segundos_sin_actualizar || 0;
    var minutos = Math.floor(segundos / 60);
    var segundosRestantes = segundos % 60;
    var tiempoTexto = segundos < 60 ? segundos + ' segundos' : minutos + ' minutos ' + segundosRestantes + ' segundos';
    document.getElementById('detailActualizacion').textContent = 'Hace ' + tiempoTexto;
    
    // Actualizar estado
    var estadoSpan = document.getElementById('detailEstado');
    var estadoClass = tracking.estado || 'on-time';
    var estadoTexto = tracking.estado_texto || 'Activo';
    estadoSpan.innerHTML = '<span class="status-tag ' + estadoClass + '">' + estadoTexto + '</span>';
    
    // Actualizar hora de inicio
    if (tracking.fecha_inicio) {
        var fechaInicio = new Date(tracking.fecha_inicio);
        var horaInicio = fechaInicio.getHours();
        var minInicio = String(fechaInicio.getMinutes()).padStart(2, '0');
        var ampm = horaInicio >= 12 ? 'PM' : 'AM';
        horaInicio = horaInicio % 12 || 12;
        document.getElementById('detailInicio').textContent = horaInicio + ':' + minInicio + ' ' + ampm;
    } else {
        document.getElementById('detailInicio').textContent = 'N/D';
    }
    
    // Mostrar panel de detalles
    document.getElementById('trackingDetailsPanel').style.display = 'block';
}

// =====================================================
// ACTUALIZAR MAPA CON LA UBICACIÓN DEL BUS
// =====================================================
function actualizarMapa(tracking) {
    if (!mapInstance) return;
    
    if (currentMarker) {
        mapInstance.removeLayer(currentMarker);
    }
    
    if (currentRouteLine) {
        mapInstance.removeLayer(currentRouteLine);
    }
    
    var lat = parseFloat(tracking.latitud);
    var lng = parseFloat(tracking.longitud);
    
    if (isNaN(lat) || isNaN(lng)) {
        // Si no hay ubicación, centrar en El Salvador
        mapInstance.setView(centroSV, 8);
        return;
    }
    
    var busIcon = L.divIcon({
        html: '<i class="fas fa-bus"></i>',
        className: 'bus-marker-icon',
        iconSize: [35, 35],
        iconAnchor: [17, 17]
    });
    
    currentMarker = L.marker([lat, lng], { icon: busIcon }).addTo(mapInstance);
    
    var direccionTexto = tracking.direccion === 'IDA' ? 'IDA →' : 'REGRESO ←';
    
    currentMarker.bindPopup(
        '<b>' + (tracking.numero_unidad || 'Unidad ' + tracking.id_bus) + '</b><br>' +
        'Ruta: ' + (tracking.nombre_ruta || 'En curso') + '<br>' +
        'Dirección: ' + direccionTexto + '<br>' +
        'Velocidad: ' + (tracking.velocidad || 'N/D') + ' km/h'
    ).openPopup();
    
    mapInstance.setView([lat, lng], 14);
}

// =====================================================
// ACTUALIZAR EN TIEMPO REAL (cada 5 segundos)
// =====================================================
function iniciarActualizacionEnVivo() {
    if (markerInterval) {
        clearInterval(markerInterval);
    }
    
    // Actualizar cada 5 segundos
    markerInterval = setInterval(function() {
        cargarTrackingsActivos();
    }, 5000);
}

// =====================================================
// MODAL DE INFORMACIÓN
// =====================================================
function initModal() {
    var modal = document.getElementById('infoModal');
    var openBtn = document.getElementById('infoModalBtn');
    var closeBtn = document.getElementById('infoModalClose');
    
    if (openBtn) {
        openBtn.addEventListener('click', function() {
            modal.classList.add('show');
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.classList.remove('show');
        });
    }
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });
}

// =====================================================
// INICIALIZACIÓN
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    cargarTrackingsActivos();
    iniciarActualizacionEnVivo();
    initModal();
    
    // Botones del hero
    var btnMap = document.querySelector('.btn-map');
    var btnRoutes = document.querySelector('.btn-routes');
    
    if (btnMap) {
        btnMap.addEventListener('click', function() {
            if (mapInstance) {
                mapInstance.setView(centroSV, 9);
            }
        });
    }
    
    if (btnRoutes) {
        btnRoutes.addEventListener('click', function() {
            document.querySelector('.feed-section').scrollIntoView({ behavior: 'smooth' });
        });
    }
    
    // Búsqueda de trackings
    var searchInput = document.getElementById('routeSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            var term = e.target.value.toLowerCase();
            var cards = document.querySelectorAll('.route-card');
            cards.forEach(function(card) {
                var text = card.textContent.toLowerCase();
                if (text.includes(term)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});