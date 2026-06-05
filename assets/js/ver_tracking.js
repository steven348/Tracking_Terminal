/**
 * Busito SV - Controlador de Monitoreo y Telemetría
 * Versión con Chat en Vivo y Bus Móvil - CORREGIDO MAPA
 */

$(document).ready(function() {
    // Estado local de la aplicación
    let rutaSeleccionadaCtx = null;
    let busMarker = null;
    let userMarker = null;
    let mapaInstance = null;
    let trackingPollingInterval = null;
    let chatPollingInterval = null;
    let lastChatId = 0;
    let currentTrackingData = null;
    let routeCoordinates = [];
    let currentBusIndex = 0;
    let busAnimationInterval = null;
    var animationSpeed = 800;
    let chatActivo = false;
    let trackingActivo = false;

    // =====================================================
    // LISTA DE DEPARTAMENTOS CON SUS IMÁGENES
    // =====================================================
    
    const departamentos = [
        { nombre: 'CABAÑAS', imagen: 'Cabañas.jpg' },
        { nombre: 'SAN SALVADOR', imagen: 'San Salvador.jpg' },
        { nombre: 'SAN MIGUEL', imagen: 'San Miguel.jpg' },
        { nombre: 'SANTA ANA', imagen: 'Santa Ana.jpg' },
        { nombre: 'CUSCATLAN', imagen: 'Cuscatlán.jpg' },
        { nombre: 'LA LIBERTAD', imagen: 'La Libertad.jpg' },
        { nombre: 'AHUACHAPAN', imagen: 'Ahuachapán.jpg' },
        { nombre: 'SONSONATE', imagen: 'Sonsonate.jpg' },
        { nombre: 'CHALATENANGO', imagen: 'Chalatenango.jpg' },
        { nombre: 'LA PAZ', imagen: 'La Paz.jpg' },
        { nombre: 'SAN VICENTE', imagen: 'San Vicente.jpg' },
        { nombre: 'USULUTAN', imagen: 'Usulután.jpg' },
        { nombre: 'MORAZAN', imagen: 'Morazán.png' },
        { nombre: 'LA UNION', imagen: 'La Unión.jpg' }
    ];
    
    function cargarDepartamentos() {
        const grid = $('#departmentsGrid');
        grid.empty();
        
        departamentos.forEach(dept => {
            const imgPath = `/TRACKING_TERMINAL/assets/img/departments/${dept.imagen}`;
            
            const card = $(`
                <div class="dept-card" data-dept="${dept.nombre}">
                    <div class="card-thumbnail">
                        <img src="${imgPath}" alt="${dept.nombre}" onerror="this.src='https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=500'">
                    </div>
                    <div class="card-content-block">
                        <h2 class="card-title">${dept.nombre}</h2>
                    </div>
                </div>
            `);
            grid.append(card);
        });
        
        $('.dept-card').on('click', function() {
            $('.dept-card').removeClass('active');
            $(this).addClass('active');
            const departamento = $(this).data('dept');
            $('#selectedDeptTitle').text("RUTAS DE " + departamento);
            cargarRutasDelDepartamento(departamento);
        });
    }
    
    // =====================================================
    // GEOLOCALIZACIÓN DEL USUARIO
    // =====================================================
    
    function iniciarGeolocalizacionUsuario() {
        if (!navigator.geolocation) {
            console.warn('Geolocalización no soportada');
            return;
        }
        
        var options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 5000
        };
        
        navigator.geolocation.watchPosition(
            function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                actualizarUbicacionUsuario(lat, lng);
                calcularDistanciaAlBus(lat, lng);
            },
            function(error) {
                console.error('Error de geolocalización:', error);
            },
            options
        );
    }
    
    function actualizarUbicacionUsuario(lat, lng) {
        if (!mapaInstance) return;
        
        var userAvatar = usuarioActual?.avatar || '👤';
        var userName = usuarioActual?.nombre || 'Tú';
        
        if (!userMarker) {
            var userIcon = L.divIcon({
                html: `
                    <div class="user-location-marker">
                        <div class="user-avatar">${escapeHtml(userAvatar)}</div>
                        <div class="pulse-ring"></div>
                        <div class="user-label">${escapeHtml(userName)}</div>
                    </div>
                `,
                className: 'custom-user-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });
            
            userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(mapaInstance);
            userMarker.bindPopup(`
                <div style="color:#fff;">
                    <b>📍 Tu ubicación</b><br>
                    <span style="color:#2ecc71;">${escapeHtml(userName)}</span>
                </div>
            `);
        } else {
            userMarker.setLatLng([lat, lng]);
        }
    }
    
    function calcularDistanciaAlBus(latUsuario, lngUsuario) {
        if (!currentTrackingData) return;
        
        var latBus = parseFloat(currentTrackingData.latitud);
        var lngBus = parseFloat(currentTrackingData.longitud);
        var distancia = calcularDistancia(latUsuario, lngUsuario, latBus, lngBus);
        
        $('#distanciaAlBus').remove();
        $('.live-stats').append(`
            <div class="live-card" id="distanciaAlBus">
                <div class="live-card-top">
                    <span class="live-card-label">Distancia al bus</span>
                    <i class="fas fa-ruler"></i>
                </div>
                <div class="live-card-value" style="font-size: 1.1rem;">
                    ${distancia < 1000 ? Math.round(distancia) + ' m' : (distancia / 1000).toFixed(1) + ' km'}
                </div>
                <div class="live-card-sub">
                    ${distancia < 100 ? '🚌 ¡El bus está muy cerca!' : distancia < 500 ? '👀 El bus está a la vista' : '📍 El bus viene en camino'}
                </div>
            </div>
        `);
    }
    
    function calcularDistancia(lat1, lon1, lat2, lon2) {
        var R = 6371000;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLon = (lon2 - lon1) * Math.PI / 180;
        var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    // =====================================================
    // ANIMACIÓN DEL BUS
    // =====================================================
    
    function actualizarMarcadorBus(posicion, angulo = 0) {
        if (!mapaInstance) return;
        
        var lat = posicion[0];
        var lng = posicion[1];
        
        var busIcon = L.divIcon({
            html: `
                <div class="bus-marker-wrapper" style="transform: rotate(${angulo}deg);">
                    <i class="fas fa-bus"></i>
                    <div class="bus-label-tooltip">Bus en vivo</div>
                    <div class="bus-pulse"></div>
                </div>
            `,
            className: 'custom-bus-marker',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        
        if (!busMarker) {
            busMarker = L.marker([lat, lng], { icon: busIcon }).addTo(mapaInstance);
            busMarker.bindPopup(`
                <div style="color:#fff;">
                    <b>🚌 Unidad en movimiento</b><br>
                    <span style="color:#00f3ff;">Ruta: ${rutaSeleccionadaCtx?.nombre || 'En curso'}</span>
                </div>
            `);
        } else {
            busMarker.setLatLng([lat, lng]);
            busMarker.setIcon(busIcon);
        }
    }
    
    function calcularAngulo(puntoActual, puntoSiguiente) {
        var lat1 = puntoActual[0] * Math.PI / 180;
        var lat2 = puntoSiguiente[0] * Math.PI / 180;
        var lng1 = puntoActual[1] * Math.PI / 180;
        var lng2 = puntoSiguiente[1] * Math.PI / 180;
        var dLng = lng2 - lng1;
        var y = Math.sin(dLng) * Math.cos(lat2);
        var x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLng);
        return Math.atan2(y, x) * 180 / Math.PI;
    }
    
    // =====================================================
    // TRACKING EN TIEMPO REAL
    // =====================================================
    
    function iniciarTrackingReal(idRuta) {
        if (trackingPollingInterval) clearInterval(trackingPollingInterval);
        trackingActivo = true;
        cargarTrackingActual(idRuta);
        
        trackingPollingInterval = setInterval(function() {
            cargarTrackingActual(idRuta);
        }, 3000);
    }
    
    function cargarTrackingActual(idRuta) {
        var url = '/TRACKING_TERMINAL/api/get_active_tracking.php?id_ruta=' + idRuta;
        
        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.tracking) {
                    if (!trackingActivo) {
                        trackingActivo = true;
                        if (rutaSeleccionadaCtx) {
                            iniciarChat(rutaSeleccionadaCtx.id, 'IDA');
                        }
                    }
                    
                    var nuevoTracking = data.tracking;
                    $('#trackingStatus').html('<i class="fas fa-circle" style="font-size: 0.6rem; color: #2ecc71;"></i> Tracking activo');
                    $('#trackingStatus').css('color', '#2ecc71');
                    $('#chatStatus').html('<i class="fas fa-circle" style="color: #2ecc71;"></i> Conectado');
                    $('#chatStatus').css('color', '#2ecc71');
                    
                    var now = new Date();
                    $('#lastUpdate').text(now.toLocaleTimeString());
                    
                    if (!currentTrackingData) {
                        currentTrackingData = nuevoTracking;
                        
                        if (routeCoordinates.length > 0) {
                            var puntoActual = parseInt(currentTrackingData.punto_actual) || 0;
                            if (puntoActual < routeCoordinates.length) {
                                currentBusIndex = puntoActual;
                                actualizarMarcadorBus(routeCoordinates[currentBusIndex]);
                            }
                        } else {
                            actualizarMarcadorBus([parseFloat(nuevoTracking.latitud), parseFloat(nuevoTracking.longitud)]);
                        }
                        
                        var progreso = (currentTrackingData.punto_actual / currentTrackingData.total_puntos) * 100;
                        $('#trackingRouteTitle').html(`
                            <i class="fas fa-bus text-cyan"></i> 
                            ${currentTrackingData.ruta_nombre} 
                            <span style="font-size: 0.7rem;">(${currentTrackingData.direccion})</span>
                            <div style="font-size: 0.7rem; color: #2ecc71;">Progreso: ${Math.floor(progreso)}%</div>
                        `);
                        $('#trackingRouteStatus').text('En vivo - Conectado');
                        $('#progressCard').show();
                        $('#progressValue').text(Math.floor(progreso) + '%');
                        $('#progressDetail').text('Recorrido en progreso');
                        
                        if (userMarker) {
                            var userLatLng = userMarker.getLatLng();
                            calcularDistanciaAlBus(userLatLng.lat, userLatLng.lng);
                        }
                    }
                } else {
                    if (trackingActivo) {
                        trackingActivo = false;
                        finalizarChatPorFinTracking();
                    }
                    
                    $('#trackingStatus').html('<i class="fas fa-circle" style="font-size: 0.6rem; color: #ff9f43;"></i> Esperando bus...');
                    $('#trackingStatus').css('color', '#ff9f43');
                    $('#trackingRouteTitle').html(`
                        <i class="fas fa-clock text-orange"></i> 
                        Sin tracking activo
                    `);
                    $('#trackingRouteStatus').text('Esperando que el conductor inicie el recorrido...');
                    $('#progressCard').hide();
                    
                    if (busMarker && mapaInstance) {
                        busMarker.remove();
                        busMarker = null;
                    }
                }
            })
            .catch(function(error) {
                console.error('Error cargando tracking:', error);
                if (trackingActivo) {
                    trackingActivo = false;
                    finalizarChatPorFinTracking();
                }
            });
    }
    
    // =====================================================
    // CHAT EN VIVO
    // =====================================================
    
    function iniciarChat(idRuta, direccion) {
        if (chatPollingInterval) clearInterval(chatPollingInterval);
        lastChatId = 0;
        chatActivo = true;
        
        $('#chatInput').prop('disabled', false);
        $('#sendMessageBtn').prop('disabled', false);
        $('#chatMessages').html('<div class="chat-system-msg">💬 Chat en vivo conectado - Puedes enviar mensajes al conductor</div>');
        $('#chatStatus').html('<i class="fas fa-circle" style="color: #2ecc71;"></i> Conectado');
        $('#chatStatus').css('color', '#2ecc71');
        
        cargarMensajesChat(idRuta, direccion);
        
        chatPollingInterval = setInterval(function() {
            cargarMensajesChat(idRuta, direccion);
        }, 2000);
    }
    
    function cargarMensajesChat(idRuta, direccion) {
        if (!chatActivo) return;
        
        var url = '/TRACKING_TERMINAL/api/chat_sync.php?id_ruta=' + idRuta + 
                  '&direccion=' + encodeURIComponent(direccion) + 
                  '&last_id=' + lastChatId;
        
        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.mensajes && data.mensajes.length > 0) {
                    data.mensajes.forEach(function(msg) {
                        agregarMensajeChat(msg.nombre, msg.mensaje, msg.timestamp, msg.tipo);
                        if (msg.id > lastChatId) lastChatId = msg.id;
                    });
                }
            })
            .catch(function(error) {
                console.error('Error cargando mensajes:', error);
            });
    }
    
    function enviarMensaje(idRuta, direccion, mensaje) {
        if (!chatActivo) return;
        
        var data = {
            id_ruta: idRuta,
            direccion: direccion,
            nombre: usuarioActual?.nombre || 'Usuario',
            mensaje: mensaje,
            tipo: 'usuario'
        };
        
        fetch('/TRACKING_TERMINAL/api/chat_sync.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success) {
                cargarMensajesChat(idRuta, direccion);
            }
        })
        .catch(function(error) {
            console.error('Error enviando mensaje:', error);
        });
    }
    
    function agregarMensajeChat(nombre, mensaje, timestamp, tipo) {
        var $chatMessages = $('#chatMessages');
        var hora = timestamp ? new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        var esConductor = (tipo === 'conductor');
        var avatar = nombre.charAt(0).toUpperCase();
        
        var bubbleHTML = `
            <div class="chat-message ${esConductor ? 'conductor' : ''}">
                <div class="chat-avatar">${escapeHtml(avatar)}</div>
                <div class="chat-body">
                    <div class="chat-meta">
                        <span class="chat-user">${escapeHtml(nombre)} ${esConductor ? '🚌' : '👤'}</span>
                        <span class="chat-time">${hora}</span>
                    </div>
                    <div class="chat-bubble">${escapeHtml(mensaje)}</div>
                </div>
            </div>
        `;
        
        $chatMessages.append(bubbleHTML);
        $chatMessages.scrollTop($chatMessages[0].scrollHeight);
    }
    
    function finalizarChat() {
        chatActivo = false;
        if (chatPollingInterval) {
            clearInterval(chatPollingInterval);
            chatPollingInterval = null;
        }
        $('#chatInput').prop('disabled', true);
        $('#sendMessageBtn').prop('disabled', true);
        $('#chatMessages').html('<div class="chat-system-msg">⏹️ Chat finalizado - Tracking terminado</div>');
        $('#chatStatus').html('<i class="fas fa-circle" style="color: #ff9f43;"></i> Desconectado');
        $('#chatStatus').css('color', '#ff9f43');
    }
    
    function finalizarChatPorFinTracking() {
        chatActivo = false;
        if (chatPollingInterval) {
            clearInterval(chatPollingInterval);
            chatPollingInterval = null;
        }
        $('#chatInput').prop('disabled', true);
        $('#sendMessageBtn').prop('disabled', true);
        $('#chatMessages').html('<div class="chat-system-msg">⏹️ El conductor ha finalizado el tracking - Chat desconectado</div>');
        $('#chatStatus').html('<i class="fas fa-circle" style="color: #ff9f43;"></i> Desconectado');
        $('#chatStatus').css('color', '#ff9f43');
        
        if (busMarker && mapaInstance) {
            busMarker.remove();
            busMarker = null;
        }
        
        currentTrackingData = null;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // =====================================================
    // RUTAS POR DEPARTAMENTO - 6 COLUMNAS
    // =====================================================
    
    function cargarRutasDelDepartamento(departamento) {
        var container = document.getElementById('routesContainer');
        if (!container) return;
        
        container.innerHTML = `<div class="loading-routes"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Cargando rutas...</p></div>`;
        
        var url = '/TRACKING_TERMINAL/api/get_rutas.php?departamento=' + encodeURIComponent(departamento);
        
        fetch(url)
            .then(response => response.json())
            .then(routes => {
                if (!routes || routes.length === 0) {
                    container.innerHTML = `<div class="no-routes-msg">No hay rutas disponibles</div>`;
                    return;
                }
                
                container.innerHTML = '';
                
                var gridWrapper = document.createElement('div');
                gridWrapper.className = 'routes-grid-wrapper';
                gridWrapper.style.display = 'grid';
                gridWrapper.style.gridTemplateColumns = 'repeat(6, 1fr)';
                gridWrapper.style.gap = '12px';
                gridWrapper.style.padding = '10px 0';
                gridWrapper.style.width = '100%';
                
                routes.forEach(route => {
                    var sq = document.createElement('div');
                    sq.className = 'route-square';
                    sq.textContent = route.nombre_ruta;
                    sq.title = `${route.origen || 'Origen'} → ${route.destino || 'Destino'}`;
                    sq.dataset.id = route.id_ruta;
                    sq.dataset.nombre = route.nombre_ruta;
                    sq.dataset.origen = route.origen || 'Origen';
                    sq.dataset.destino = route.destino || 'Destino';
                    
                    sq.addEventListener('click', function() {
                        document.querySelectorAll('.route-square').forEach(function(el) {
                            el.classList.remove('active');
                        });
                        this.classList.add('active');
                        
                        rutaSeleccionadaCtx = {
                            id: this.dataset.id,
                            nombre: this.dataset.nombre,
                            origen: this.dataset.origen,
                            destino: this.dataset.destino
                        };
                        
                        $('#modalRouteName').text(rutaSeleccionadaCtx.nombre);
                        $('#confirmTrackingModal').fadeIn(200).css('display', 'flex');
                    });
                    
                    gridWrapper.appendChild(sq);
                });
                
                container.appendChild(gridWrapper);
            })
            .catch(error => {
                console.error('Error cargando rutas:', error);
                container.innerHTML = `<div class="error-state">Error al cargar las rutas</div>`;
            });
    }
    
    // =====================================================
    // INICIALIZACIÓN DEL MAPA - CORREGIDA DEFINITIVA
    // =====================================================
    
    function inicializarMapaMonitoreo(idRuta) {
        console.log('Inicializando mapa para ruta:', idRuta);
        
        if (trackingPollingInterval) clearInterval(trackingPollingInterval);
        if (busAnimationInterval) clearInterval(busAnimationInterval);
        
        if (busMarker && mapaInstance) {
            try { busMarker.remove(); } catch(e) {}
            busMarker = null;
        }
        if (userMarker && mapaInstance) {
            try { userMarker.remove(); } catch(e) {}
            userMarker = null;
        }
        currentTrackingData = null;
        trackingActivo = false;
        
        var trackingMapView = document.getElementById('trackingMapView');
        var liveMapContainer = document.getElementById('liveMapContainer');
        
        if (!trackingMapView) {
            console.error('No se encuentra trackingMapView');
            return;
        }
        
        if (!liveMapContainer) {
            console.error('No se encuentra liveMapContainer');
            return;
        }
        
        trackingMapView.style.display = 'flex';
        trackingMapView.style.position = 'fixed';
        trackingMapView.style.top = '0';
        trackingMapView.style.left = '0';
        trackingMapView.style.width = '100%';
        trackingMapView.style.height = '100%';
        trackingMapView.style.zIndex = '9999';
        trackingMapView.style.backgroundColor = '#08121e';
        
        liveMapContainer.innerHTML = '';
        liveMapContainer.style.width = '100%';
        liveMapContainer.style.height = '100%';
        liveMapContainer.style.position = 'relative';
        liveMapContainer.style.backgroundColor = '#08121e';
        
        var mapDiv = document.createElement('div');
        mapDiv.id = 'map-tracking-engine';
        mapDiv.style.width = '100%';
        mapDiv.style.height = '100%';
        mapDiv.style.position = 'absolute';
        mapDiv.style.top = '0';
        mapDiv.style.left = '0';
        mapDiv.style.right = '0';
        mapDiv.style.bottom = '0';
        mapDiv.style.backgroundColor = '#08121e';
        liveMapContainer.appendChild(mapDiv);
        
        document.body.style.overflow = 'hidden';
        
        setTimeout(function() {
            console.log('Creando instancia de Leaflet map');
            
            try {
                if (mapaInstance) {
                    try { mapaInstance.remove(); } catch(e) {}
                    mapaInstance = null;
                }
                
                var mapa = L.map('map-tracking-engine', {
                    center: [13.7942, -88.8965],
                    zoom: 13,
                    zoomControl: true,
                    fadeAnimation: true,
                    zoomAnimation: true
                });
                
                mapaInstance = mapa;
                
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    subdomains: 'abcd',
                    maxZoom: 20,
                    minZoom: 6
                }).addTo(mapa);
                
                setTimeout(function() {
                    if (mapaInstance) {
                        mapaInstance.invalidateSize();
                        console.log('Mapa invalidado 1');
                    }
                }, 100);
                
                setTimeout(function() {
                    if (mapaInstance) {
                        mapaInstance.invalidateSize();
                        console.log('Mapa invalidado 2');
                    }
                }, 300);
                
                setTimeout(function() {
                    if (mapaInstance) {
                        mapaInstance.invalidateSize();
                        console.log('Mapa invalidado 3');
                    }
                }, 600);
                
                var urlGeo = '/TRACKING_TERMINAL/api/get_coordenadas.php?id_ruta=' + encodeURIComponent(idRuta);
                
                fetch(urlGeo)
                    .then(function(response) { 
                        if (!response.ok) throw new Error('Error en la respuesta del servidor');
                        return response.json(); 
                    })
                    .then(function(data) {
                        if (!data || data.length === 0) {
                            console.error('No se encontraron coordenadas para la ruta');
                            return;
                        }
                        
                        console.log('Coordenadas cargadas:', data.length, 'puntos');
                        routeCoordinates = data;
                        
                        var rutaPolyline = L.polyline(data, {
                            color: '#00C2C7',
                            weight: 5,
                            opacity: 0.85
                        }).addTo(mapa);
                        
                        mapa.fitBounds(rutaPolyline.getBounds(), { padding: [40, 40] });
                        
                        iniciarTrackingReal(idRuta);
                        iniciarGeolocalizacionUsuario();
                    })
                    .catch(function(error) {
                        console.error('Error cargando geometría:', error);
                    });
                    
            } catch(e) {
                console.error('Error al crear el mapa:', e);
            }
        }, 200);
    }
    
    // =====================================================
    // EVENTOS DE UI
    // =====================================================
    
    $('#btnCancelTracking').on('click', function() {
        $('#confirmTrackingModal').fadeOut(150);
        $('.route-square').removeClass('active');
    });
    
    $('#btnConfirmTracking').on('click', function() {
        if (!rutaSeleccionadaCtx) return;
        
        console.log('Confirmando tracking para ruta:', rutaSeleccionadaCtx.id);
        
        $('#confirmTrackingModal').fadeOut(150);
        $('#departmentsView').hide();
        
        $('#trackingMapView').css('display', 'flex');
        $('#trackingMapView').fadeIn(300);
        
        if ($(window).width() > 768) {
            $('#adminSidebar').removeClass('show');
            $('#openSidebar').hide();
        } else {
            $('#adminSidebar').addClass('show');
            $('#openSidebar').hide();
        }
        
        iniciarChat(rutaSeleccionadaCtx.id, 'IDA');
        inicializarMapaMonitoreo(rutaSeleccionadaCtx.id);
    });
    
    $('#btnBackToGrid').on('click', function() {
        if (trackingPollingInterval) clearInterval(trackingPollingInterval);
        if (busAnimationInterval) clearInterval(busAnimationInterval);
        
        finalizarChat();
        
        $('#trackingMapView').hide();
        $('#departmentsView').fadeIn(300);
        rutaSeleccionadaCtx = null;
        
        if (mapaInstance) {
            try { mapaInstance.remove(); } catch(e) {}
            mapaInstance = null;
        }
        currentTrackingData = null;
        routeCoordinates = [];
        busMarker = null;
        userMarker = null;
        trackingActivo = false;
        
        document.body.style.overflow = '';
    });
    
    function procesarEnvioMensaje() {
        var $input = $('#chatInput');
        var mensajeTexto = $input.val().trim();
        if (!mensajeTexto || !rutaSeleccionadaCtx || !chatActivo) return;
        
        enviarMensaje(rutaSeleccionadaCtx.id, 'IDA', mensajeTexto);
        $input.val('');
    }
    
    $('#sendMessageBtn').on('click', procesarEnvioMensaje);
    $('#chatInput').on('keypress', function(e) {
        if (e.which === 13) procesarEnvioMensaje();
    });
    
    $('#openSidebar').on('click', function() {
        $('#adminSidebar').addClass('show');
        $(this).hide();
    });
    
    $('#toggleSidebar').on('click', function() {
        $('#adminSidebar').removeClass('show');
        $('#openSidebar').show();
    });
    
    $(document).on('click', '#map-tracking-engine', function() {
        if ($(window).width() <= 768) {
            $('#adminSidebar').removeClass('show');
            $('#openSidebar').show();
        }
    });
    
    $(window).on('resize', function() {
        if (mapaInstance) {
            setTimeout(function() {
                mapaInstance.invalidateSize();
                console.log('Mapa redimensionado');
            }, 200);
        }
    });
    
    cargarDepartamentos();
    console.log('Ver tracking inicializado correctamente');
});