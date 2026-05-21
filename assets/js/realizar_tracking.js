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

// Mapeo para mostrar textos amigables en el Select2
var terminalDisplayNames = {
    'CABAÑAS': 'Cabañas',
    'CUSCATLAN': 'Cuscatlán',
    'oriente': 'Oriente',
    'centro': 'Centro'
};

// =====================================================
// FUNCIÓN: GUARDAR TRACKING EN LA BD
// =====================================================
function saveTrackingToDatabase(lat, lng, currentPoint, totalPoints) {
    if (!isTrackingActive) return;
    if (!selectedRoute) return;
    
    var data = {
        id_bus: 1, // Temporal, luego se asignará según la ruta
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
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(result) {
        if (result.success) {
            console.log('📌 Tracking guardado - Punto:', currentPoint, '| Lat:', lat, '| Lng:', lng);
        } else {
            console.error('❌ Error al guardar tracking:', result.error);
        }
    })
    .catch(function(error) {
        console.error('❌ Error de red al guardar tracking:', error);
    });
}

// =====================================================
// FUNCIÓN: CARGAR RUTAS DESDE LA BD
// =====================================================
function loadRoutes(terminal) {
    console.log('loadRoutes llamado con terminal:', terminal);
    
    var container = document.getElementById('routesContainer');
    var countEl = document.getElementById('routeCount');
    var startBtn = document.getElementById('startTrackingBtn');
    var directionPanel = document.getElementById('directionPanel');

    if (!container) {
        console.error('No se encuentra el contenedor de rutas');
        return;
    }

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
    console.log('Fetching:', url);
    
    fetch(url)
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(function(routes) {
            console.log('Rutas recibidas:', routes);
            
            if (countEl) countEl.textContent = routes.length + ' routes';

            if (!routes || routes.length === 0) {
                container.innerHTML = '<p class="no-routes-msg">Sin rutas disponibles para este departamento</p>';
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
                    if (routeInfo) routeInfo.style.display = 'none';
                    
                    var infoOrigen = document.getElementById('infoOrigen');
                    var infoDestino = document.getElementById('infoDestino');
                    if (infoOrigen) infoOrigen.textContent = '—';
                    if (infoDestino) infoDestino.textContent = '—';
                    
                    console.log('Ruta seleccionada:', selectedRoute);
                });

                container.appendChild(sq);
            });
        })
        .catch(function(error) {
            console.error('Error al cargar rutas:', error);
            container.innerHTML = '<p class="no-routes-msg">Error al cargar las rutas: ' + error.message + '</p>';
        });
}

// =====================================================
// FUNCIÓN: ACTUALIZAR INFO DE DIRECCIÓN
// =====================================================
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
    console.log('Dirección seleccionada:', direction);
}

// =====================================================
// FUNCIÓN: OBTENER COORDENADAS
// =====================================================
function loadRouteCoordinates(routeId, direction) {
    return fetch('/TRACKING_TERMINAL/api/get_coordenadas.php?id_ruta=' + routeId)
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            return response.json();
        })
        .then(function(coords) {
            if (direction === 'REGRESO') {
                return coords.reverse();
            }
            return coords;
        });
}

// =====================================================
// FUNCIÓN: LIMPIAR MAPA
// =====================================================
function clearMap() {
    if (!mapInstance) return;
    if (rutaLine && mapInstance.hasLayer(rutaLine)) mapInstance.removeLayer(rutaLine);
    if (busMarker && mapInstance.hasLayer(busMarker)) mapInstance.removeLayer(busMarker);
    if (destMarker && mapInstance.hasLayer(destMarker)) mapInstance.removeLayer(destMarker);
    if (animationInterval) {
        clearInterval(animationInterval);
        animationInterval = null;
    }
    rutaLine = busMarker = destMarker = null;
}

// =====================================================
// FUNCIÓN: INICIAR NUEVO TRACKING
// =====================================================
function startNewTracking() {
    console.log('startNewTracking llamado');
    console.log('selectedRoute:', selectedRoute);
    console.log('selectedDirection:', selectedDirection);
    
    if (!selectedRoute || !selectedDirection) {
        console.log('Faltan datos para iniciar tracking');
        return;
    }
    
    if (isTrackingActive) {
        console.log('Ya hay tracking activo');
        return;
    }
    
    var termVal = $('#terminalSelect').val();
    var terminalDisplay = terminalDisplayNames[termVal] || termVal;
    
    var liveTerminalName = document.getElementById('liveTerminalName');
    var liveRouteNumber = document.getElementById('liveRouteNumber');
    var liveDirection = document.getElementById('liveDirection');
    
    if (liveTerminalName) liveTerminalName.textContent = terminalDisplay;
    if (liveRouteNumber) liveRouteNumber.textContent = selectedRoute.nombre;
    if (liveDirection) liveDirection.textContent = selectedDirection === 'IDA' ? 'IDA' : 'REGRESO';
    
    var trackingConfigPanel = document.getElementById('trackingConfigPanel');
    var trackingLivePanel = document.getElementById('trackingLivePanel');
    
    if (trackingConfigPanel) trackingConfigPanel.style.display = 'none';
    if (trackingLivePanel) trackingLivePanel.style.display = 'block';
    
    var statusDot = document.getElementById('statusDot');
    var statusText = document.getElementById('statusText');
    var statusPillContainer = document.getElementById('statusPillContainer');
    
    if (statusDot) statusDot.classList.add('active');
    if (statusText) statusText.textContent = 'Live Tracking';
    if (statusPillContainer) statusPillContainer.style.borderColor = '#2ecc71';
    
    var chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.innerHTML = '';
        var div = document.createElement('div');
        div.className = 'chat-system-msg';
        div.innerHTML = '<span>✅ Tracking iniciado · Ruta ' + selectedRoute.nombre + ' - ' + selectedDirection + '</span>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    currentPointIndex = 0;
    lastSavedIndex = 0;
    isTrackingActive = true;
    
    renderNewTracking();
}

// =====================================================
// RENDERIZAR NUEVO TRACKING
// =====================================================
function renderNewTracking() {
    if (!selectedRoute || !selectedDirection || !mapInstance) return;
    
    clearMap();
    
    var color = document.getElementById('routeColor').value;
    
    var loadingMsg = L.popup()
        .setLatLng(mapInstance.getCenter())
        .setContent('Cargando coordenadas de la ruta...')
        .openOn(mapInstance);
    
    loadRouteCoordinates(selectedRoute.id, selectedDirection)
        .then(function(coords) {
            if (loadingMsg) mapInstance.closePopup(loadingMsg);
            
            if (!coords || coords.length === 0) {
                console.error('No hay coordenadas para esta ruta');
                L.popup()
                    .setLatLng(mapInstance.getCenter())
                    .setContent('❌ No hay coordenadas disponibles para esta ruta')
                    .openOn(mapInstance);
                isTrackingActive = false;
                return;
            }
            
            currentCoords = coords;
            currentPointIndex = 0;
            
            var busIcon = L.divIcon({
                html: '<i class="fas fa-bus"></i>',
                className: 'bus-icon-div',
                iconSize: [35, 35],
                iconAnchor: [17, 17]
            });
            
            var destIcon = L.divIcon({
                html: '<i class="fas fa-flag-checkered"></i>',
                className: 'dest-icon-div',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });
            
            rutaLine = L.polyline(coords, {
                color: color,
                weight: 5,
                opacity: 0.8,
                dashArray: '8, 12'
            }).addTo(mapInstance);
            
            destMarker = L.marker(coords[coords.length - 1], { icon: destIcon }).addTo(mapInstance);
            busMarker = L.marker(coords[0], { icon: busIcon }).addTo(mapInstance);
            
            mapInstance.fitBounds(rutaLine.getBounds(), { padding: [40, 40] });
            
            if (animationInterval) clearInterval(animationInterval);
            
            animationInterval = setInterval(function() {
                if (currentPointIndex < currentCoords.length - 1) {
                    currentPointIndex++;
                    var currentLat = currentCoords[currentPointIndex][0];
                    var currentLng = currentCoords[currentPointIndex][1];
                    busMarker.setLatLng([currentLat, currentLng]);
                    
                    // GUARDAR TRACKING EN CADA MOVIMIENTO
                    saveTrackingToDatabase(currentLat, currentLng, currentPointIndex, currentCoords.length);
                    
                    if (currentPointIndex % 5 === 0 || currentPointIndex === currentCoords.length - 1) {
                        saveTrackingState();
                    }
                } else {
                    clearInterval(animationInterval);
                    animationInterval = null;
                    
                    // Guardar último punto
                    var lastLat = currentCoords[currentCoords.length - 1][0];
                    var lastLng = currentCoords[currentCoords.length - 1][1];
                    saveTrackingToDatabase(lastLat, lastLng, currentCoords.length - 1, currentCoords.length);
                    
                    var chatMessages = document.getElementById('chatMessages');
                    if (chatMessages) {
                        var div = document.createElement('div');
                        div.className = 'chat-system-msg';
                        div.innerHTML = '<span>🏁 El bus ha llegado a su destino</span>';
                        chatMessages.appendChild(div);
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                }
            }, animationSpeed);
            
            saveTrackingState();
        })
        .catch(function(error) {
            console.error('Error al cargar coordenadas:', error);
            if (loadingMsg) mapInstance.closePopup(loadingMsg);
            isTrackingActive = false;
        });
}

// =====================================================
// GUARDAR ESTADO EN LOCALSTORAGE
// =====================================================
function saveTrackingState() {
    if (!isTrackingActive || !selectedRoute) {
        localStorage.removeItem('busito_tracking_active');
        localStorage.removeItem('busito_tracking_state');
        return;
    }
    
    if (Math.abs(currentPointIndex - lastSavedIndex) < 5 && lastSavedIndex !== 0) {
        return;
    }
    
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
    
    console.log('Estado guardado - Punto:', currentPointIndex);
}

// =====================================================
// LIMPIAR ESTADO
// =====================================================
function clearTrackingState() {
    localStorage.removeItem('busito_tracking_active');
    localStorage.removeItem('busito_tracking_state');
    isTrackingActive = false;
}

// =====================================================
// FINALIZAR TRACKING (FUNCIÓN GLOBAL)
// =====================================================
window.finishTracking = function() {
    console.log('Finalizando tracking manualmente');
    
    if (animationInterval) {
        clearInterval(animationInterval);
        animationInterval = null;
    }
    
    clearTrackingState();
    
    isTrackingActive = false;
    selectedRoute = null;
    selectedDirection = null;
    selectedRouteId = null;
    currentPointIndex = 0;
    lastSavedIndex = 0;
    currentCoords = [];
    
    var trackingLivePanel = document.getElementById('trackingLivePanel');
    var trackingConfigPanel = document.getElementById('trackingConfigPanel');
    
    if (trackingLivePanel) trackingLivePanel.style.display = 'none';
    if (trackingConfigPanel) trackingConfigPanel.style.display = 'block';
    
    var statusDot = document.getElementById('statusDot');
    var statusText = document.getElementById('statusText');
    var statusPillContainer = document.getElementById('statusPillContainer');
    
    if (statusDot) statusDot.classList.remove('active');
    if (statusText) statusText.textContent = 'Inactive Tracking';
    if (statusPillContainer) statusPillContainer.style.borderColor = '';
    
    clearMap();
    
    document.querySelectorAll('.route-square').forEach(function(el) {
        el.classList.remove('active');
    });
    
    var directionPanel = document.getElementById('directionPanel');
    var routeInfo = document.getElementById('routeInfo');
    if (directionPanel) directionPanel.style.display = 'none';
    if (routeInfo) routeInfo.style.display = 'none';
    
    document.querySelectorAll('.direction-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    var startBtn = document.getElementById('startTrackingBtn');
    if (startBtn) startBtn.disabled = true;
    
    var currentTerminal = $('#terminalSelect').val();
    if (currentTerminal) {
        loadRoutes(currentTerminal);
    }
    
    var chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        var div = document.createElement('div');
        div.className = 'chat-system-msg';
        div.innerHTML = '<span>⏹️ Tracking finalizado por el administrador</span>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    console.log('Tracking finalizado correctamente');
};

// =====================================================
// INICIALIZACIÓN DE BOTONES DE DIRECCIÓN
// =====================================================
function initDirectionButtons() {
    var directionBtns = document.querySelectorAll('.direction-btn');
    var startBtn = document.getElementById('startTrackingBtn');
    
    directionBtns.forEach(function(btn) {
        var oldListener = btn._listener;
        if (oldListener) btn.removeEventListener('click', oldListener);
        
        var listener = function() {
            if (isTrackingActive) {
                console.log('Tracking activo, no se puede cambiar dirección');
                return;
            }
            
            var direction = this.dataset.direction;
            selectedDirection = direction;
            
            document.querySelectorAll('.direction-btn').forEach(function(el) {
                el.classList.remove('active');
            });
            this.classList.add('active');
            
            updateRouteInfo(direction);
            if (startBtn) startBtn.disabled = false;
        };
        
        btn.addEventListener('click', listener);
        btn._listener = listener;
    });
}

// =====================================================
// SELECT2 - CONFIGURACIÓN
// =====================================================
function initSelect2() {
    var $terminalSelect = $('#terminalSelect');
    
    if (!$terminalSelect.length) {
        console.error('No se encuentra el elemento #terminalSelect');
        return;
    }
    
    if ($terminalSelect.data('select2')) {
        $terminalSelect.select2('destroy');
    }
    
    $terminalSelect.select2({
        placeholder: "Buscar terminal...",
        allowClear: false,
        width: '100%',
        minimumResultsForSearch: 0,
        templateResult: function(data) {
            if (!data.id) return data.text;
            var displayText = terminalDisplayNames[data.id] || data.text;
            return $('<span>' + displayText + '</span>');
        },
        templateSelection: function(data) {
            if (!data.id) return data.text;
            return terminalDisplayNames[data.id] || data.text;
        }
    });
    
    function updateSelectHighlight() {
        var currentVal = $terminalSelect.val();
        var $select2Container = $terminalSelect.next('.select2');
        var $selection = $select2Container.find('.select2-selection--single');
        
        if (currentVal && currentVal !== "") {
            $selection.addClass('select-has-value');
            var displayText = terminalDisplayNames[currentVal] || currentVal;
            var $rendered = $select2Container.find('.select2-selection__rendered');
            if ($rendered.length && $rendered.html() !== displayText) {
                $rendered.html(displayText);
            }
        } else {
            $selection.removeClass('select-has-value');
        }
    }
    
    $terminalSelect.on('select2:select', function(e) {
        updateSelectHighlight();
        console.log('Seleccionado:', e.params.data.id);
    });
    
    $terminalSelect.off('change').on('change', function() {
        var val = $(this).val();
        updateSelectHighlight();
        if (val && !isTrackingActive) {
            loadRoutes(val);
        } else if (isTrackingActive) {
            console.log('Tracking activo, no se puede cambiar terminal');
        }
    });
    
    updateSelectHighlight();
}

// =====================================================
// INICIALIZACIÓN PRINCIPAL
// =====================================================
function initTrackingSystem() {
    if (isInitialized) {
        console.log('Sistema ya inicializado, omitiendo...');
        return;
    }
    
    console.log('Inicializando sistema de tracking...');
    
    if (!mapInstance) {
        mapInstance = L.map('map-tracking', { zoomControl: false })
                       .setView([13.6929, -89.2182], 8);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CartoDB'
        }).addTo(mapInstance);
    }
    
    initSelect2();
    
    var routeColor = document.getElementById('routeColor');
    if (routeColor) {
        routeColor.removeEventListener('input', routeColor._listener);
        routeColor.addEventListener('input', function() {
            if (selectedRoute && selectedDirection && rutaLine && !isTrackingActive) {
                rutaLine.setStyle({ color: this.value });
            }
        });
        routeColor._listener = routeColor;
    }
    
    var startBtn = document.getElementById('startTrackingBtn');
    if (startBtn) {
        startBtn.removeEventListener('click', startBtn._listener);
        startBtn.addEventListener('click', startNewTracking);
        startBtn._listener = startNewTracking;
    }
    
    var finishBtn = document.getElementById('finishTrackingBtn');
    if (finishBtn) {
        finishBtn.removeEventListener('click', finishBtn._listener);
        finishBtn.addEventListener('click', function() {
            window.finishTracking();
        });
        finishBtn._listener = finishBtn;
    }
    
    var layout = document.getElementById('adminLayout');
    var toggleBtn = document.getElementById('toggleSidebar');
    var openBtn = document.getElementById('openSidebar');
    
    if (toggleBtn) {
        toggleBtn.removeEventListener('click', toggleBtn._listener);
        toggleBtn.addEventListener('click', function() {
            if (layout) layout.classList.add('sidebar-hidden');
            if (openBtn) openBtn.style.display = 'flex';
        });
        toggleBtn._listener = toggleBtn;
    }
    
    if (openBtn) {
        openBtn.removeEventListener('click', openBtn._listener);
        openBtn.addEventListener('click', function() {
            if (layout) layout.classList.remove('sidebar-hidden');
            if (openBtn) openBtn.style.display = 'none';
        });
        openBtn._listener = openBtn;
    }
    
    // CHAT
    var chatInput = document.getElementById('chatInput');
    var sendBtn = document.getElementById('sendMessageBtn');
    
    function getTime() {
        var now = new Date();
        var h = now.getHours();
        var m = String(now.getMinutes()).padStart(2, '0');
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ampm;
    }
    
    function addOwnMessage(text) {
        var chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        var div = document.createElement('div');
        div.className = 'chat-message self';
        div.innerHTML =
            '<div class="chat-avatar">A</div>' +
            '<div class="chat-content">' +
                '<div class="chat-user">Administrador · ' + getTime() + '</div>' +
                '<div class="chat-bubble">' + text + '</div>' +
            '</div>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function sendMessage() {
        if (!chatInput) return;
        var text = chatInput.value.trim();
        if (!text) return;
        addOwnMessage(text);
        chatInput.value = '';
        chatInput.focus();
    }
    
    if (sendBtn) {
        sendBtn.removeEventListener('click', sendBtn._listener);
        sendBtn.addEventListener('click', sendMessage);
        sendBtn._listener = sendMessage;
    }
    
    if (chatInput) {
        chatInput.removeEventListener('keydown', chatInput._listener);
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') sendMessage();
        });
        chatInput._listener = chatInput;
    }
    
    function updateClock() {
        var clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.innerText = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
    
    initDirectionButtons();
    
    var restored = loadTrackingState();
    
    if (!restored) {
        console.log('No hay tracking activo para restaurar');
        var routesContainer = document.getElementById('routesContainer');
        if (routesContainer) {
            routesContainer.innerHTML = '<p class="no-routes-msg">Selecciona una terminal para ver las rutas</p>';
        }
        var routeCount = document.getElementById('routeCount');
        if (routeCount) routeCount.textContent = '0 routes';
    }
    
    window.removeEventListener('beforeunload', window._beforeUnloadListener);
    window._beforeUnloadListener = function() {
        if (isTrackingActive) {
            saveTrackingState();
        }
    };
    window.addEventListener('beforeunload', window._beforeUnloadListener);
    
    isInitialized = true;
    console.log('Tracking JS listo - Sistema inicializado');
}

// =====================================================
// RESTAURAR TRACKING
// =====================================================
function loadTrackingState() {
    var isActive = localStorage.getItem('busito_tracking_active');
    
    if (isActive !== 'true') {
        console.log('No hay tracking activo guardado');
        return false;
    }
    
    var stateJson = localStorage.getItem('busito_tracking_state');
    if (!stateJson) return false;
    
    try {
        var state = JSON.parse(stateJson);
        
        var now = new Date().getTime();
        var elapsed = now - state.timestamp;
        var maxInactiveTime = 30 * 60 * 1000;
        
        if (elapsed > maxInactiveTime) {
            console.log('Tracking expirado por inactividad');
            clearTrackingState();
            return false;
        }
        
        console.log('Restaurando tracking desde punto:', state.currentPointIndex);
        
        if (state.terminal) {
            $('#terminalSelect').val(state.terminal).trigger('change');
        }
        
        var routeColor = document.getElementById('routeColor');
        if (routeColor) routeColor.value = state.routeColor;
        
        selectedRoute = state.route;
        selectedDirection = state.direction;
        selectedRouteId = state.route.id;
        currentPointIndex = state.currentPointIndex || 0;
        
        var directionPanel = document.getElementById('directionPanel');
        if (directionPanel) directionPanel.style.display = 'block';
        
        document.querySelectorAll('.direction-btn').forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.dataset.direction === selectedDirection) {
                btn.classList.add('active');
            }
        });
        
        updateRouteInfo(selectedDirection);
        
        var trackingConfigPanel = document.getElementById('trackingConfigPanel');
        var trackingLivePanel = document.getElementById('trackingLivePanel');
        
        if (trackingConfigPanel) trackingConfigPanel.style.display = 'none';
        if (trackingLivePanel) trackingLivePanel.style.display = 'block';
        
        var statusDot = document.getElementById('statusDot');
        var statusText = document.getElementById('statusText');
        var statusPillContainer = document.getElementById('statusPillContainer');
        
        if (statusDot) statusDot.classList.add('active');
        if (statusText) statusText.textContent = 'Live Tracking';
        if (statusPillContainer) statusPillContainer.style.borderColor = '#2ecc71';
        
        var liveTerminalName = document.getElementById('liveTerminalName');
        var liveRouteNumber = document.getElementById('liveRouteNumber');
        var liveDirection = document.getElementById('liveDirection');
        
        if (liveTerminalName) liveTerminalName.textContent = terminalDisplayNames[state.terminal] || state.terminal;
        if (liveRouteNumber) liveRouteNumber.textContent = selectedRoute.nombre;
        if (liveDirection) liveDirection.textContent = selectedDirection === 'IDA' ? 'IDA' : 'REGRESO';
        
        document.querySelectorAll('.route-square').forEach(function(el) {
            el.classList.remove('active');
            if (el.dataset.id == selectedRoute.id) {
                el.classList.add('active');
            }
        });
        
        isTrackingActive = true;
        
        var chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.innerHTML = '';
            var div = document.createElement('div');
            div.className = 'chat-system-msg';
            div.innerHTML = '<span>🔄 Tracking reconectado - Continuando desde punto ' + (currentPointIndex + 1) + '</span>';
            chatMessages.appendChild(div);
            
            var div2 = document.createElement('div');
            div2.className = 'chat-system-msg';
            div2.innerHTML = '<span>✅ Ruta ' + selectedRoute.nombre + ' - ' + selectedDirection + '</span>';
            chatMessages.appendChild(div2);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        restoreTracking();
        
        return true;
    } catch (e) {
        console.error('Error al restaurar tracking:', e);
        clearTrackingState();
        return false;
    }
}

// =====================================================
// RESTAURAR TRACKING EN EL MAPA
// =====================================================
function restoreTracking() {
    if (!selectedRoute || !selectedDirection || !mapInstance) return;
    
    var color = document.getElementById('routeColor').value;
    
    var loadingMsg = L.popup()
        .setLatLng(mapInstance.getCenter())
        .setContent('Restaurando tracking...')
        .openOn(mapInstance);
    
    loadRouteCoordinates(selectedRoute.id, selectedDirection)
        .then(function(coords) {
            if (loadingMsg) mapInstance.closePopup(loadingMsg);
            
            if (!coords || coords.length === 0) {
                console.error('No hay coordenadas para esta ruta');
                return;
            }
            
            currentCoords = coords;
            
            if (currentPointIndex >= coords.length) {
                currentPointIndex = coords.length - 1;
            }
            
            var busIcon = L.divIcon({
                html: '<i class="fas fa-bus"></i>',
                className: 'bus-icon-div',
                iconSize: [35, 35],
                iconAnchor: [17, 17]
            });
            
            var destIcon = L.divIcon({
                html: '<i class="fas fa-flag-checkered"></i>',
                className: 'dest-icon-div',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });
            
            rutaLine = L.polyline(coords, {
                color: color,
                weight: 5,
                opacity: 0.8,
                dashArray: '8, 12'
            }).addTo(mapInstance);
            
            destMarker = L.marker(coords[coords.length - 1], { icon: destIcon }).addTo(mapInstance);
            busMarker = L.marker(coords[currentPointIndex], { icon: busIcon }).addTo(mapInstance);
            
            mapInstance.fitBounds(rutaLine.getBounds(), { padding: [40, 40] });
            
            resumeAnimation();
            saveTrackingState();
        })
        .catch(function(error) {
            console.error('Error al cargar coordenadas:', error);
            if (loadingMsg) mapInstance.closePopup(loadingMsg);
        });
}

// =====================================================
// REANUDAR ANIMACIÓN CON GUARDADO
// =====================================================
function resumeAnimation() {
    if (!busMarker || !currentCoords || currentCoords.length === 0) return;
    
    if (animationInterval) {
        clearInterval(animationInterval);
        animationInterval = null;
    }
    
    if (currentPointIndex >= currentCoords.length - 1) {
        console.log('Bus ya llegó al destino');
        var chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            var div = document.createElement('div');
            div.className = 'chat-system-msg';
            div.innerHTML = '<span>🏁 El bus ha llegado a su destino</span>';
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        return;
    }
    
    console.log('Reanudando animación desde punto:', currentPointIndex);
    
    animationInterval = setInterval(function() {
        if (currentPointIndex < currentCoords.length - 1) {
            currentPointIndex++;
            var currentLat = currentCoords[currentPointIndex][0];
            var currentLng = currentCoords[currentPointIndex][1];
            busMarker.setLatLng([currentLat, currentLng]);
            
            // GUARDAR TRACKING EN CADA MOVIMIENTO
            saveTrackingToDatabase(currentLat, currentLng, currentPointIndex, currentCoords.length);
            
            if (currentPointIndex % 5 === 0 || currentPointIndex === currentCoords.length - 1) {
                saveTrackingState();
            }
        } else {
            clearInterval(animationInterval);
            animationInterval = null;
            
            // Guardar último punto
            var lastLat = currentCoords[currentCoords.length - 1][0];
            var lastLng = currentCoords[currentCoords.length - 1][1];
            saveTrackingToDatabase(lastLat, lastLng, currentCoords.length - 1, currentCoords.length);
            
            var chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                var div = document.createElement('div');
                div.className = 'chat-system-msg';
                div.innerHTML = '<span>🏁 El bus ha llegado a su destino</span>';
                chatMessages.appendChild(div);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            saveTrackingState();
        }
    }, animationSpeed);
}

// =====================================================
// INICIO
// =====================================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTrackingSystem);
} else {
    initTrackingSystem();
}