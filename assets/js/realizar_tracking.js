// =====================================================
// VARIABLES GLOBALES
// =====================================================
var rutaLine          = null;
var busMarker         = null;
var destMarker        = null;
var selectedRoute     = null;
var selectedDirection = null;
var selectedRouteId   = null;
var mapInstance       = null;
var animationInterval = null;
var currentPointIndex = 0;
var currentCoords     = [];
var isTrackingActive  = false;
var lastSavedIndex    = 0;
var animationSpeed    = 800;
var isInitialized     = false;
var routeLayers       = [];
var routeEndpointMarkers = [];
var routeLabelMarkers = [];
var routeLayerMap     = {};
var routeColors       = {};

// CHAT - Variables
var chatMessagesArray = [];

// Mapeo para mostrar textos amigables en el Select2
var terminalDisplayNames = {
    'CABAÑAS': 'Cabañas',
    'CUSCATLAN': 'Cuscatlán',
    'oriente': 'Oriente',
    'centro': 'Centro'
};

function getRouteColor(routeId) {
    if (routeColors[routeId]) return routeColors[routeId];
    var palette = ['#1abc9c', '#e67e22', '#9b59b6', '#3498db', '#e74c3c', '#f1c40f', '#2ecc71', '#34495e'];
    var color = palette[routeId % palette.length];
    routeColors[routeId] = color;
    return color;
}

function clearMap() {
    if (!mapInstance) return;
    if (rutaLine && mapInstance.hasLayer(rutaLine)) mapInstance.removeLayer(rutaLine);
    if (busMarker && mapInstance.hasLayer(busMarker)) mapInstance.removeLayer(busMarker);
    if (destMarker && mapInstance.hasLayer(destMarker)) mapInstance.removeLayer(destMarker);
    routeLayers.forEach(function(layer) {
        if (mapInstance.hasLayer(layer)) mapInstance.removeLayer(layer);
    });
    routeEndpointMarkers.forEach(function(marker) {
        if (mapInstance.hasLayer(marker)) mapInstance.removeLayer(marker);
    });
    routeLabelMarkers.forEach(function(marker) {
        if (mapInstance.hasLayer(marker)) mapInstance.removeLayer(marker);
    });
    routeLayers = [];
    routeEndpointMarkers = [];
    routeLabelMarkers = [];
    routeLayerMap = {};
    if (animationInterval) {
        clearInterval(animationInterval);
        animationInterval = null;
    }
    rutaLine = busMarker = destMarker = null;
}

function addRouteToMap(route, coords, routeIndex) {
    if (!coords || coords.length === 0 || !mapInstance) return;
    var color = getRouteColor(route.id_ruta || route.id);
    var line = L.polyline(coords, { color: color, weight: 4, opacity: 0.75 }).addTo(mapInstance);
    routeLayers.push(line);
    routeLayerMap[route.id_ruta || route.id] = line;

    var startIcon = L.divIcon({
        className: 'route-endpoint-marker route-start-marker',
        html: '<div class="route-endpoint-dot" style="background:' + color + '"></div>'
    });
    var endIcon = L.divIcon({
        className: 'route-endpoint-marker route-end-marker',
        html: '<div class="route-endpoint-dot" style="background:' + color + '"></div>'
    });

    var startMarker = L.marker(coords[0], { icon: startIcon }).addTo(mapInstance);
    var endMarker = L.marker(coords[coords.length - 1], { icon: endIcon }).addTo(mapInstance);
    routeEndpointMarkers.push(startMarker, endMarker);

    var midpointIndex = Math.floor(coords.length / 2);
    var labelLatLng = coords[midpointIndex];
    var offset = (routeIndex % 2 === 0) ? -20 : 20;
    var labelIcon = L.divIcon({
        className: 'route-number-label',
        html: '<div class="route-number-badge" style="background:' + color + '">' + (route.nombre_ruta || route.nombre) + '</div>',
        iconAnchor: [0, offset]
    });
    var labelMarker = L.marker(labelLatLng, { icon: labelIcon, interactive: false }).addTo(mapInstance);
    routeLabelMarkers.push(labelMarker);
}

function drawTerminalRoutes(routes) {
    if (!Array.isArray(routes) || routes.length === 0) return;
    clearMap();
    var promises = routes.map(function(route, index) {
        return loadRouteCoordinates(route.id_ruta || route.id)
            .then(function(coords) {
                addRouteToMap(route, coords, index);
                return route;
            })
            .catch(function(error) {
                console.error('Error cargando coordenadas de ruta ' + route.id_ruta, error);
                return null;
            });
    });
    Promise.all(promises).then(function(loadedRoutes) {
        var validLayers = routeLayers.filter(function(layer) { return layer instanceof L.Polyline; });
        if (validLayers.length > 0) {
            var group = L.featureGroup(validLayers);
            mapInstance.fitBounds(group.getBounds(), { padding: [40, 40] });
        }
    });
}

function zoomToRoute(routeId) {
    var layer = routeLayerMap[routeId];
    if (layer && mapInstance) {
        mapInstance.fitBounds(layer.getBounds(), { padding: [40, 40] });
    }
}

function showOnlySelectedRoute(routeId) {
    if (!mapInstance) return;
    routeLayers.forEach(function(layer) {
        if (mapInstance.hasLayer(layer)) {
            mapInstance.removeLayer(layer);
        }
    });
    routeLayers = [];
    routeEndpointMarkers.forEach(function(marker) {
        if (mapInstance.hasLayer(marker)) {
            mapInstance.removeLayer(marker);
        }
    });
    routeLabelMarkers.forEach(function(marker) {
        if (mapInstance.hasLayer(marker)) {
            mapInstance.removeLayer(marker);
        }
    });
    routeEndpointMarkers = [];
    routeLabelMarkers = [];
    if (routeLayerMap[routeId]) {
        loadRouteCoordinates(routeId)
            .then(function(coords) {
                if (!coords || coords.length === 0) return;
                var color = getRouteColor(routeId);
                var line = L.polyline(coords, { color: color, weight: 4, opacity: 0.75 }).addTo(mapInstance);
                routeLayers.push(line);
                routeLayerMap[routeId] = line;
                var startIcon = L.divIcon({
                    className: 'route-endpoint-marker route-start-marker',
                    html: '<div class="route-endpoint-dot" style="background:' + color + '"></div>'
                });
                var endIcon = L.divIcon({
                    className: 'route-endpoint-marker route-end-marker',
                    html: '<div class="route-endpoint-dot" style="background:' + color + '"></div>'
                });
                var startMarker = L.marker(coords[0], { icon: startIcon }).addTo(mapInstance);
                var endMarker = L.marker(coords[coords.length - 1], { icon: endIcon }).addTo(mapInstance);
                routeEndpointMarkers.push(startMarker, endMarker);
                var labelIcon = L.divIcon({
                    className: 'route-number-label',
                    html: '<div class="route-number-badge" style="background:' + color + '">' + (selectedRoute.nombre || '') + '</div>',
                    iconAnchor: [0, -20]
                });
                var midpointIndex = Math.floor(coords.length / 2);
                var labelMarker = L.marker(coords[midpointIndex], { icon: labelIcon, interactive: false }).addTo(mapInstance);
                routeLabelMarkers.push(labelMarker);
                mapInstance.fitBounds(line.getBounds(), { padding: [40, 40] });
            })
            .catch(function(error) {
                console.error('Error cargando coordenadas:', error);
            });
    }
}

// =====================================================
// FUNCIONES DEL CHAT (sin base de datos)
// =====================================================

function getCurrentTime() {
    var now = new Date();
    var h = now.getHours();
    var m = String(now.getMinutes()).padStart(2, '0');
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return h + ':' + m + ' ' + ampm;
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function guardarMensajesEnSession() {
    if (selectedRoute && selectedDirection) {
        var key = 'chat_tracking_' + selectedRoute.id + '_' + selectedDirection;
        sessionStorage.setItem(key, JSON.stringify(chatMessagesArray));
        console.log('Mensajes guardados en sessionStorage:', chatMessagesArray.length);
    }
}

function cargarMensajesDeSession() {
    if (!selectedRoute || !selectedDirection) return;
    
    var key = 'chat_tracking_' + selectedRoute.id + '_' + selectedDirection;
    var savedMessages = sessionStorage.getItem(key);
    
    if (savedMessages) {
        try {
            chatMessagesArray = JSON.parse(savedMessages);
            console.log('Mensajes cargados de sessionStorage:', chatMessagesArray.length);
            
            var chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.innerHTML = '';
                chatMessagesArray.forEach(function(msg) {
                    agregarMensajeAlChat(msg.nombre, msg.mensaje, msg.hora);
                });
            }
        } catch(e) {
            console.error('Error al cargar mensajes:', e);
        }
    } else {
        chatMessagesArray = [];
        var chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.innerHTML = '';
        }
    }
}

function limpiarMensajesDeSession() {
    if (selectedRoute && selectedDirection) {
        var key = 'chat_tracking_' + selectedRoute.id + '_' + selectedDirection;
        sessionStorage.removeItem(key);
        console.log('Mensajes eliminados de sessionStorage');
    }
    chatMessagesArray = [];
}

function agregarMensajeAlChat(nombre, mensaje, hora) {
    var chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    var esMismoUsuario = (nombre === usuarioActual.nombre);
    var avatar = nombre.charAt(0).toUpperCase();
    
    var div = document.createElement('div');
    div.className = 'chat-message' + (esMismoUsuario ? ' self' : '');
    div.innerHTML =
        '<div class="chat-avatar">' + avatar + '</div>' +
        '<div class="chat-content">' +
            '<div class="chat-user">' + nombre + ' · ' + hora + '</div>' +
            '<div class="chat-bubble">' + escapeHtml(mensaje) + '</div>' +
        '</div>';
    
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function enviarMensaje() {
    var chatInput = document.getElementById('chatInput');
    if (!chatInput) return;
    
    var texto = chatInput.value.trim();
    if (!texto) return;
    
    var hora = getCurrentTime();
    var nombre = usuarioActual.nombre;
    
    chatMessagesArray.push({
        nombre: nombre,
        mensaje: texto,
        hora: hora,
        timestamp: new Date().getTime()
    });
    
    agregarMensajeAlChat(nombre, texto, hora);
    guardarMensajesEnSession();
    
    chatInput.value = '';
    chatInput.focus();
}

function iniciarChat() {
    chatMessagesArray = [];
    cargarMensajesDeSession();
    
    var chatMessages = document.getElementById('chatMessages');
    if (chatMessages && chatMessagesArray.length === 0) {
        var div = document.createElement('div');
        div.className = 'chat-system-msg';
        div.innerHTML = '<span>✅ Chat iniciado - Ruta ' + selectedRoute.nombre + ' - ' + selectedDirection + '</span>';
        chatMessages.appendChild(div);
        
        chatMessagesArray.push({
            nombre: 'Sistema',
            mensaje: 'Chat iniciado - Ruta ' + selectedRoute.nombre + ' - ' + selectedDirection,
            hora: getCurrentTime(),
            timestamp: new Date().getTime(),
            esSistema: true
        });
        guardarMensajesEnSession();
    }
}

function finalizarChat() {
    limpiarMensajesDeSession();
    chatMessagesArray = [];
    
    var chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.innerHTML = '';
        var div = document.createElement('div');
        div.className = 'chat-system-msg';
        div.innerHTML = '<span>⏹️ Chat finalizado - Tracking terminado</span>';
        chatMessages.appendChild(div);
    }
}

// =====================================================
// GUARDAR TRACKING EN BD
// =====================================================
function saveTrackingToDatabase(lat, lng, currentPoint, totalPoints) {
    if (!isTrackingActive) return;
    if (!selectedRoute) return;
    
    var data = {
        id_bus: 1,
        latitud: lat,
        longitud: lng,
        velocidad: 0,
        ruta_nombre: selectedRoute.nombre,
        direccion: selectedDirection,
        punto_actual: currentPoint,
        total_puntos: totalPoints
    };
    
    fetch('/TRACKING_TERMINAL/api/save_tracking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(response) { return response.json(); })
    .then(function(result) {
        if (result.success) {
            console.log('📌 Tracking guardado - Punto:', currentPoint);
        }
    })
    .catch(function(error) {
        console.error('Error al guardar tracking:', error);
    });
}

// =====================================================
// FUNCIONES DE RUTAS Y MAPA
// =====================================================
function loadRoutes(terminal) {
    console.log('loadRoutes llamado con terminal:', terminal);
    
    var container = document.getElementById('routesContainer');
    var countEl = document.getElementById('routeCount');
    var startBtn = document.getElementById('startTrackingBtn');
    var directionPanel = document.getElementById('directionPanel');

    if (!container) return;
    if (isTrackingActive) {
        console.log('Tracking activo, ignorando carga de rutas');
        return;
    }

    container.innerHTML = '<div class="loading-routes"><i class="fas fa-spinner fa-spin"></i> Cargando rutas...</div>';
    selectedRoute = null;
    selectedDirection = null;
    selectedRouteId = null;
    
    if (startBtn) startBtn.disabled = true;
    if (directionPanel) directionPanel.style.display = 'none';
    
    var routeInfo = document.getElementById('routeInfo');
    if (routeInfo) routeInfo.style.display = 'none';
    
    document.querySelectorAll('.direction-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    clearMap();

    if (!terminal) {
        container.innerHTML = '<p class="no-routes-msg">Selecciona una terminal</p>';
        if (countEl) countEl.textContent = '0 routes';
        return;
    }

    var url = '/TRACKING_TERMINAL/api/get_rutas.php?departamento=' + encodeURIComponent(terminal);
    fetch(url)
        .then(function(response) { return response.json(); })
        .then(function(routes) {
            if (countEl) countEl.textContent = routes.length + ' routes';
            if (!routes || routes.length === 0) {
                container.innerHTML = '<p class="no-routes-msg">Sin rutas disponibles</p>';
                return;
            }
            container.innerHTML = '';
            routes.forEach(function(route) {
                var sq = document.createElement('div');
                sq.className = 'route-square';
                sq.textContent = route.nombre_ruta;
                sq.title = (route.origen || '?') + ' → ' + (route.destino || '?');
                sq.dataset.id = route.id_ruta;
                sq.dataset.origen = route.origen || '';
                sq.dataset.destino = route.destino || '';
                sq.dataset.nombre = route.nombre_ruta;
                sq.addEventListener('click', function() {
                    if (isTrackingActive) return;
                    document.querySelectorAll('.route-square').forEach(function(el) {
                        el.classList.remove('active');
                    });
                    this.classList.add('active');
                    selectedRoute = {
                        id: parseInt(this.dataset.id),
                        nombre: this.dataset.nombre,
                        origen: this.dataset.origen,
                        destino: this.dataset.destino
                    };
                    selectedRouteId = selectedRoute.id;
                    selectedDirection = null;
                    document.querySelectorAll('.direction-btn').forEach(function(btn) {
                        btn.classList.remove('active');
                    });
                    if (startBtn) startBtn.disabled = true;
                    if (directionPanel) directionPanel.style.display = 'block';
                    if (routeInfo) routeInfo.style.display = 'block';
                    var infoOrigen = document.getElementById('infoOrigen');
                    var infoDestino = document.getElementById('infoDestino');
                    if (infoOrigen) infoOrigen.textContent = selectedRoute.origen || '—';
                    if (infoDestino) infoDestino.textContent = selectedRoute.destino || '—';
                    showOnlySelectedRoute(selectedRoute.id);
                });
                container.appendChild(sq);
            });
            drawTerminalRoutes(routes);
        })
        .catch(function(error) {
            console.error('Error al cargar rutas:', error);
            container.innerHTML = '<p class="no-routes-msg">Error al cargar las rutas</p>';
        });
}

function updateRouteInfo(direction) {
    if (!selectedRoute) return;
    var infoPanel = document.getElementById('routeInfo');
    var infoOrigen = document.getElementById('infoOrigen');
    var infoDestino = document.getElementById('infoDestino');
    if (direction === 'IDA') {
        if (infoOrigen) infoOrigen.textContent = selectedRoute.origen || 'No especificado';
        if (infoDestino) infoDestino.textContent = selectedRoute.destino || 'No especificado';
    } else {
        if (infoOrigen) infoOrigen.textContent = selectedRoute.destino || 'No especificado';
        if (infoDestino) infoDestino.textContent = selectedRoute.origen || 'No especificado';
    }
    if (infoPanel) infoPanel.style.display = 'block';
}

function loadRouteCoordinates(routeId, direction) {
    return fetch('/TRACKING_TERMINAL/api/get_coordenadas.php?id_ruta=' + routeId)
        .then(function(response) { return response.json(); })
        .then(function(coords) {
            if (direction === 'REGRESO') return coords.reverse();
            return coords;
        });
}

function renderNewTracking() {
    if (!selectedRoute || !selectedDirection || !mapInstance) return;
    clearMap();
    var color = document.getElementById('routeColor').value;
    var loadingMsg = L.popup().setLatLng(mapInstance.getCenter()).setContent('Cargando coordenadas...').openOn(mapInstance);
    loadRouteCoordinates(selectedRoute.id, selectedDirection)
        .then(function(coords) {
            if (loadingMsg) mapInstance.closePopup(loadingMsg);
            if (!coords || coords.length === 0) {
                isTrackingActive = false;
                return;
            }
            currentCoords = coords;
            currentPointIndex = 0;
            var busIcon = L.divIcon({ html: '<i class="fas fa-bus"></i>', className: 'bus-icon-div', iconSize: [35, 35], iconAnchor: [17, 17] });
            var destIcon = L.divIcon({ html: '<i class="fas fa-flag-checkered"></i>', className: 'dest-icon-div', iconSize: [30, 30], iconAnchor: [15, 30] });
            rutaLine = L.polyline(coords, { color: color, weight: 5, opacity: 0.8, dashArray: '8, 12' }).addTo(mapInstance);
            destMarker = L.marker(coords[coords.length - 1], { icon: destIcon }).addTo(mapInstance);
            busMarker = L.marker(coords[0], { icon: busIcon }).addTo(mapInstance);
            mapInstance.fitBounds(rutaLine.getBounds(), { padding: [40, 40] });
            if (animationInterval) clearInterval(animationInterval);
            animationInterval = setInterval(function() {
                if (currentPointIndex < currentCoords.length - 1) {
                    currentPointIndex++;
                    var lat = currentCoords[currentPointIndex][0];
                    var lng = currentCoords[currentPointIndex][1];
                    busMarker.setLatLng([lat, lng]);
                    saveTrackingToDatabase(lat, lng, currentPointIndex, currentCoords.length);
                    if (currentPointIndex % 5 === 0 || currentPointIndex === currentCoords.length - 1) {
                        saveTrackingState();
                    }
                } else {
                    clearInterval(animationInterval);
                    animationInterval = null;
                    var lastLat = currentCoords[currentCoords.length - 1][0];
                    var lastLng = currentCoords[currentCoords.length - 1][1];
                    saveTrackingToDatabase(lastLat, lastLng, currentCoords.length - 1, currentCoords.length);
                }
            }, animationSpeed);
            saveTrackingState();
        })
        .catch(function(error) {
            console.error('Error:', error);
            if (loadingMsg) mapInstance.closePopup(loadingMsg);
            isTrackingActive = false;
        });
}

function resumeAnimation() {
    if (!busMarker || !currentCoords || currentCoords.length === 0) return;
    if (animationInterval) clearInterval(animationInterval);
    if (currentPointIndex >= currentCoords.length - 1) return;
    animationInterval = setInterval(function() {
        if (currentPointIndex < currentCoords.length - 1) {
            currentPointIndex++;
            var lat = currentCoords[currentPointIndex][0];
            var lng = currentCoords[currentPointIndex][1];
            busMarker.setLatLng([lat, lng]);
            saveTrackingToDatabase(lat, lng, currentPointIndex, currentCoords.length);
            if (currentPointIndex % 5 === 0 || currentPointIndex === currentCoords.length - 1) {
                saveTrackingState();
            }
        } else {
            clearInterval(animationInterval);
            animationInterval = null;
        }
    }, animationSpeed);
}

function restoreTracking() {
    if (!selectedRoute || !selectedDirection || !mapInstance) return;
    var color = document.getElementById('routeColor').value;
    var loadingMsg = L.popup().setLatLng(mapInstance.getCenter()).setContent('Restaurando tracking...').openOn(mapInstance);
    loadRouteCoordinates(selectedRoute.id, selectedDirection)
        .then(function(coords) {
            if (loadingMsg) mapInstance.closePopup(loadingMsg);
            if (!coords || coords.length === 0) return;
            currentCoords = coords;
            if (currentPointIndex >= coords.length) currentPointIndex = coords.length - 1;
            var busIcon = L.divIcon({ html: '<i class="fas fa-bus"></i>', className: 'bus-icon-div', iconSize: [35, 35], iconAnchor: [17, 17] });
            var destIcon = L.divIcon({ html: '<i class="fas fa-flag-checkered"></i>', className: 'dest-icon-div', iconSize: [30, 30], iconAnchor: [15, 30] });
            rutaLine = L.polyline(coords, { color: color, weight: 5, opacity: 0.8, dashArray: '8, 12' }).addTo(mapInstance);
            destMarker = L.marker(coords[coords.length - 1], { icon: destIcon }).addTo(mapInstance);
            busMarker = L.marker(coords[currentPointIndex], { icon: busIcon }).addTo(mapInstance);
            mapInstance.fitBounds(rutaLine.getBounds(), { padding: [40, 40] });
            resumeAnimation();
            saveTrackingState();
        })
        .catch(function(error) {
            console.error('Error:', error);
            if (loadingMsg) mapInstance.closePopup(loadingMsg);
        });
}

function startNewTracking() {
    if (!selectedRoute || !selectedDirection) return;
    if (isTrackingActive) return;
    var termVal = $('#terminalSelect').val();
    var terminalDisplay = terminalDisplayNames[termVal] || termVal;
    document.getElementById('liveTerminalName').textContent = terminalDisplay;
    document.getElementById('liveRouteNumber').textContent = selectedRoute.nombre;
    document.getElementById('liveDirection').textContent = selectedDirection === 'IDA' ? 'IDA' : 'REGRESO';
    document.getElementById('trackingConfigPanel').style.display = 'none';
    document.getElementById('trackingLivePanel').style.display = 'block';
    document.getElementById('statusDot').classList.add('active');
    document.getElementById('statusText').textContent = 'Live Tracking';
    document.getElementById('statusPillContainer').style.borderColor = '#2ecc71';
    iniciarChat();
    currentPointIndex = 0;
    lastSavedIndex = 0;
    isTrackingActive = true;
    renderNewTracking();
}

function saveTrackingState() {
    if (!isTrackingActive || !selectedRoute) {
        localStorage.removeItem('busito_tracking_active');
        localStorage.removeItem('busito_tracking_state');
        return;
    }
    if (Math.abs(currentPointIndex - lastSavedIndex) < 5 && lastSavedIndex !== 0) return;
    var state = {
        active: true,
        terminal: $('#terminalSelect').val(),
        route: selectedRoute,
        direction: selectedDirection,
        routeColor: document.getElementById('routeColor').value,
        currentPointIndex: currentPointIndex,
        timestamp: new Date().getTime()
    };
    localStorage.setItem('busito_tracking_active', 'true');
    localStorage.setItem('busito_tracking_state', JSON.stringify(state));
    lastSavedIndex = currentPointIndex;
}

function clearTrackingState() {
    localStorage.removeItem('busito_tracking_active');
    localStorage.removeItem('busito_tracking_state');
    isTrackingActive = false;
}

window.finishTracking = function() {
    finalizarChat();
    if (animationInterval) clearInterval(animationInterval);
    clearTrackingState();
    isTrackingActive = false;
    selectedRoute = null;
    selectedDirection = null;
    selectedRouteId = null;
    currentPointIndex = 0;
    lastSavedIndex = 0;
    currentCoords = [];
    document.getElementById('trackingLivePanel').style.display = 'none';
    document.getElementById('trackingConfigPanel').style.display = 'block';
    document.getElementById('statusDot').classList.remove('active');
    document.getElementById('statusText').textContent = 'Inactive Tracking';
    document.getElementById('statusPillContainer').style.borderColor = '';
    clearMap();
    document.querySelectorAll('.route-square').forEach(function(el) { el.classList.remove('active'); });
    document.getElementById('directionPanel').style.display = 'none';
    document.getElementById('routeInfo').style.display = 'none';
    document.querySelectorAll('.direction-btn').forEach(function(btn) { btn.classList.remove('active'); });
    document.getElementById('startTrackingBtn').disabled = true;
    var currentTerminal = $('#terminalSelect').val();
    if (currentTerminal) loadRoutes(currentTerminal);
};

function loadTrackingState() {
    var isActive = localStorage.getItem('busito_tracking_active');
    if (isActive !== 'true') return false;
    var stateJson = localStorage.getItem('busito_tracking_state');
    if (!stateJson) return false;
    try {
        var state = JSON.parse(stateJson);
        var now = new Date().getTime();
        var elapsed = now - state.timestamp;
        if (elapsed > 30 * 60 * 1000) {
            clearTrackingState();
            return false;
        }
        if (state.terminal) $('#terminalSelect').val(state.terminal).trigger('change');
        document.getElementById('routeColor').value = state.routeColor;
        selectedRoute = state.route;
        selectedDirection = state.direction;
        selectedRouteId = state.route.id;
        currentPointIndex = state.currentPointIndex || 0;
        document.getElementById('directionPanel').style.display = 'block';
        document.querySelectorAll('.direction-btn').forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.dataset.direction === selectedDirection) btn.classList.add('active');
        });
        updateRouteInfo(selectedDirection);
        document.getElementById('trackingConfigPanel').style.display = 'none';
        document.getElementById('trackingLivePanel').style.display = 'block';
        document.getElementById('statusDot').classList.add('active');
        document.getElementById('statusText').textContent = 'Live Tracking';
        document.getElementById('statusPillContainer').style.borderColor = '#2ecc71';
        document.getElementById('liveTerminalName').textContent = terminalDisplayNames[state.terminal] || state.terminal;
        document.getElementById('liveRouteNumber').textContent = selectedRoute.nombre;
        document.getElementById('liveDirection').textContent = selectedDirection === 'IDA' ? 'IDA' : 'REGRESO';
        document.querySelectorAll('.route-square').forEach(function(el) {
            el.classList.remove('active');
            if (el.dataset.id == selectedRoute.id) el.classList.add('active');
        });
        isTrackingActive = true;
        cargarMensajesDeSession();
        restoreTracking();
        return true;
    } catch(e) {
        clearTrackingState();
        return false;
    }
}

function initDirectionButtons() {
    var directionBtns = document.querySelectorAll('.direction-btn');
    var startBtn = document.getElementById('startTrackingBtn');
    directionBtns.forEach(function(btn) {
        var oldListener = btn._listener;
        if (oldListener) btn.removeEventListener('click', oldListener);
        var listener = function() {
            if (isTrackingActive) return;
            selectedDirection = this.dataset.direction;
            document.querySelectorAll('.direction-btn').forEach(function(el) { el.classList.remove('active'); });
            this.classList.add('active');
            updateRouteInfo(selectedDirection);
            if (startBtn) startBtn.disabled = false;
        };
        btn.addEventListener('click', listener);
        btn._listener = listener;
    });
}

function initSelect2() {
    var $terminalSelect = $('#terminalSelect');
    if (!$terminalSelect.length) return;
    if ($terminalSelect.data('select2')) $terminalSelect.select2('destroy');
    $terminalSelect.select2({
        placeholder: "Buscar terminal...",
        allowClear: false,
        width: '100%',
        minimumResultsForSearch: 0,
        templateResult: function(data) {
            if (!data.id) return data.text;
            return terminalDisplayNames[data.id] || data.text;
        },
        templateSelection: function(data) {
            if (!data.id) return data.text;
            return terminalDisplayNames[data.id] || data.text;
        }
    });
    function updateSelectHighlight() {
        var currentVal = $terminalSelect.val();
        var $selection = $terminalSelect.next('.select2').find('.select2-selection--single');
        if (currentVal && currentVal !== "") {
            $selection.addClass('select-has-value');
            var displayText = terminalDisplayNames[currentVal] || currentVal;
            var $rendered = $terminalSelect.next('.select2').find('.select2-selection__rendered');
            if ($rendered.length && $rendered.html() !== displayText) $rendered.html(displayText);
        } else {
            $selection.removeClass('select-has-value');
        }
    }
    $terminalSelect.on('select2:select', function(e) { updateSelectHighlight(); });
    $terminalSelect.off('change').on('change', function() {
        var val = $(this).val();
        updateSelectHighlight();
        if (val && !isTrackingActive) loadRoutes(val);
    });
    updateSelectHighlight();
}

function initTrackingSystem() {
    if (isInitialized) return;
    console.log('Inicializando sistema...');
    if (!mapInstance) {
        mapInstance = L.map('map-tracking', { zoomControl: false }).setView([13.6929, -89.2182], 8);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CartoDB'
        }).addTo(mapInstance);
    }
    initSelect2();
    document.getElementById('routeColor').addEventListener('input', function() {
        if (selectedRoute && selectedDirection && rutaLine && !isTrackingActive) {
            rutaLine.setStyle({ color: this.value });
        }
        if (selectedRoute && !selectedDirection && selectedRouteId && !isTrackingActive) {
            var selectedLayer = routeLayerMap[selectedRouteId];
            if (selectedLayer) {
                selectedLayer.setStyle({ color: this.value });
            }
        }
    });
    var gpsInfoBtn = document.getElementById('gpsInfoBtn');
    if (gpsInfoBtn) {
        gpsInfoBtn.addEventListener('click', function() {
            showToast('Ubicación GPS', 'Esta página usa ubicación GPS para mostrar las rutas y los puntos de origen y destino en el mapa. Asegúrate de dar permisos de ubicación si tu navegador lo solicita.');
        });
    }
    document.getElementById('startTrackingBtn').addEventListener('click', startNewTracking);
    function showToast(title, message) {
        var existing = document.getElementById('notificationToast');
        if (existing) existing.remove();
        var toast = document.createElement('div');
        toast.id = 'notificationToast';
        toast.className = 'notification-toast';
        toast.innerHTML = '<button class="toast-close" type="button">&times;</button>' +
            '<h4>' + title + '</h4>' +
            '<p>' + message + '</p>';
        document.body.appendChild(toast);
        toast.querySelector('.toast-close').addEventListener('click', function() {
            toast.remove();
        });
        setTimeout(function() {
            if (toast.parentNode) toast.remove();
        }, 7000);
    }
    document.getElementById('finishTrackingBtn').addEventListener('click', function() { window.finishTracking(); });
    var layout = document.getElementById('adminLayout');
    var toggleBtn = document.getElementById('toggleSidebar');
    var openBtn = document.getElementById('openSidebar');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (layout) layout.classList.add('sidebar-hidden');
            if (openBtn) openBtn.style.display = 'flex';
        });
    }
    if (openBtn) {
        openBtn.addEventListener('click', function() {
            if (layout) layout.classList.remove('sidebar-hidden');
            if (openBtn) openBtn.style.display = 'none';
        });
    }
    var chatInput = document.getElementById('chatInput');
    var sendBtn = document.getElementById('sendMessageBtn');
    if (sendBtn) {
        sendBtn.addEventListener('click', function() { enviarMensaje(); });
    }
    if (chatInput) {
        chatInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') enviarMensaje(); });
    }
    function updateClock() {
        var clockEl = document.getElementById('liveClock');
        if (clockEl) clockEl.innerText = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    setInterval(updateClock, 1000);
    updateClock();
    initDirectionButtons();
    var restored = loadTrackingState();
    if (!restored) {
        document.getElementById('routesContainer').innerHTML = '<p class="no-routes-msg">Selecciona una terminal para ver las rutas</p>';
        document.getElementById('routeCount').textContent = '0 routes';
    }
    window.addEventListener('beforeunload', function() { if (isTrackingActive) saveTrackingState(); });
    isInitialized = true;
    console.log('Sistema listo');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTrackingSystem);
} else {
    initTrackingSystem();
}
