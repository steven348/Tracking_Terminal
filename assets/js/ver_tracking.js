/**
 * Busito SV - Controlador de Monitoreo y Telemetría
 * Versión Refactorizada y Blindada - Enfoque Cartográfico Dinámico
 */

// Respaldo global para evitar colapso por ReferenceError si no se inyecta desde PHP
window.usuarioActual = window.usuarioActual || { nombre: "Invitado", avatar: "I" };

$(document).ready(function() {
    // Estado local de la aplicación
    let rutaSeleccionadaCtx = null;
    let busMarkers = []; 
    let unidadSeleccionadaChat = null; // Control de UI de mensajería
    
    // VARIABLES DE CONTROL CARTOGRÁFICO DE ENFOQUE (NUEVAS)
    let unidadEnFoque = null;          // Identifica qué bus se está siguiendo en exclusiva
    let mapaInstance = null;           // Guarda la referencia del mapa de Leaflet
    let rutaPolylineInstance = null;   // Guarda la referencia de la línea geométrica

    /* ==========================================================================
       1. CAPA DE NAVEGACIÓN Y DEPARTAMENTOS
       ========================================================================== */
    $('.dept-card').on('click', function() {
        $('.dept-card').removeClass('active');
        $(this).addClass('active');
        
        var departamentoSeleccionado = $(this).data('dept');
        $('#selectedDeptTitle').text("RUTAS DE " + departamentoSeleccionado);
        
        cargarRutasDelDepartamento(departamentoSeleccionado);
    });

    function cargarRutasDelDepartamento(departamento) {
        var container = document.getElementById('routesContainer');
        if (!container) return;

        container.innerHTML = `
            <div class="loading-routes" style="color:#fff; text-align:center; padding:40px;">
                <i class="fas fa-spinner fa-spin fa-2x" style="color:#00C2C7; margin-bottom:10px;"></i>
                <p style="font-size:0.9rem; color:#ccc;">Buscando rutas autorizadas...</p>
            </div>
        `;

        var url = '/TRACKING_TERMINAL/api/get_rutas.php?departamento=' + encodeURIComponent(departamento);
        
        fetch(url)
            .then(response => { 
                if (!response.ok) throw new Error('Error en respuesta del servidor');
                return response.json(); 
            })
            .then(routes => {
                if (!routes || routes.length === 0) {
                    container.innerHTML = `
                        <div class="no-routes-msg" style="color:#888; text-align:center; padding:40px;">
                            <i class="fas fa-folder-open" style="display:block; margin-bottom:10px; font-size:1.8rem; color:#555;"></i>
                            <p>No hay rutas asignadas a este departamento todavía.</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = '';
                var gridWrapper = document.createElement('div');
                gridWrapper.style.display = 'grid';
                gridWrapper.style.gridTemplateColumns = 'repeat(auto-fill, minmax(80px, 1fr))';
                gridWrapper.style.gap = '12px';
                gridWrapper.style.padding = '10px 0';

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
                        $('.route-square').removeClass('active');
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
                console.error('Error crítico al conectar con la API de rutas:', error);
                container.innerHTML = `
                    <div class="error-state" style="color:#e74c3c; text-align:center; padding:40px;">
                        <i class="fas fa-exclamation-circle fa-2x" style="margin-bottom:10px;"></i>
                        <p>Error de conexión con el servidor de datos.</p>
                    </div>
                `;
            });
    }

    /* ==========================================================================
       2. CONTROLADORES DEL MODAL INTERMEDIO
       ========================================================================== */
    $('#btnCancelTracking').on('click', function() {
        $('#confirmTrackingModal').fadeOut(150);
        $('.route-square').removeClass('active');
    });

    $('#btnConfirmTracking').on('click', function() {
        if (!rutaSeleccionadaCtx) return;

        $('#confirmTrackingModal').fadeOut(150);
        $('#departmentsView').hide();
        $('#trackingMapView').fadeIn(300);
        $('#trackingRouteTitle').html(`<i class="fas fa-bus text-cyan"></i> ${rutaSeleccionadaCtx.nombre}`);
        
        inicializarMapaMonitoreo(rutaSeleccionadaCtx.id);
    });

    $('#btnBackToGrid').on('click', function() {
        if (window.trackingSimuladorInterval) clearInterval(window.trackingSimuladorInterval);
        $('#trackingMapView').hide();
        $('#departmentsView').fadeIn(300);
        $('.route-square').removeClass('active');
        rutaSeleccionadaCtx = null;
        mapaInstance = null;
        rutaPolylineInstance = null;
        unidadEnFoque = null;
    });

    /* ==========================================================================
       3. GEOMETRÍA Y TELEMETRÍA (LEAFLET CORREGIDO Y ENFOCADO)
       ========================================================================== */
    function inicializarMapaMonitoreo(idRuta) {
        if (window.trackingSimuladorInterval) clearInterval(window.trackingSimuladorInterval);
        
        busMarkers.forEach(marker => marker.remove());
        busMarkers = [];
        unidadEnFoque = null; // Reiniciamos enfoque al cargar nueva ruta

        $('#liveMapContainer').html('<div id="map-tracking-engine" style="width:100%; height:100%;"></div>');
        if (typeof L === 'undefined') return;

        var mapa = L.map('map-tracking-engine').setView([13.7942, -88.8965], 9);
        mapaInstance = mapa; // Compartimos la instancia a nivel de contexto superior
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            maxZoom: 20
        }).addTo(mapa);

        setTimeout(() => { mapa.invalidateSize(); }, 350);

        var urlGeo = '/TRACKING_TERMINAL/api/get_coordenadas.php?id_ruta=' + encodeURIComponent(idRuta);
        
        fetch(urlGeo)
            .then(response => {
                if (!response.ok) throw new Error('Error en geometría');
                return response.json();
            })
            .then(data => {
                if (!data || data.length === 0) return;

                var rutaPolyline = L.polyline(data, {
                    color: '#00C2C7',
                    weight: 5,
                    opacity: 0.85
                }).addTo(mapa);
                
                rutaPolylineInstance = rutaPolyline; // Guardamos instancia para re-encuadres

                mapa.fitBounds(rutaPolyline.getBounds(), { padding: [40, 40] });

                let poolBusesDinamicos = [];
                let origenReal = rutaSeleccionadaCtx ? rutaSeleccionadaCtx.origen : "Origen";
                let destinoReal = rutaSeleccionadaCtx ? rutaSeleccionadaCtx.destino : "Destino";

                function calcularRumbo(p1, p2) {
                    if (!p1 || !p2) return 0;
                    let lat1 = p1[0] * Math.PI / 180, lat2 = p2[0] * Math.PI / 180;
                    let dLon = (p2[1] - p1[1]) * Math.PI / 180;
                    let y = Math.sin(dLon) * Math.cos(lat2);
                    let x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);
                    return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
                }

                $('.trip-mini-card').each(function() {
                    let $card = $(this);
                    let unidad = $card.find('.unit-name-title').text().replace(/[\n\t]/g, '').trim(); 
                    let esRetrasado = $card.hasClass('delayed');
                    let $labelTrayecto = $card.find('.unit-route-trajectory');
                    
                    if ($labelTrayecto.length === 0) {
                        $card.find('.unit-name-title').after(`
                            <div class="unit-route-trajectory" style="color: #8fa0b0; font-size: 11px; margin-top: 4px; font-weight: 500; line-height: 1.3;">
                                Calculando...
                            </div>
                        `);
                        $labelTrayecto = $card.find('.unit-route-trajectory');
                    }
                    
                    let sentido = "IDA", progresoActual = 0;
                    if (unidad === "U-08") { sentido = "IDA"; progresoActual = 15; }
                    else if (unidad === "U-12") { sentido = "REGRESO"; progresoActual = 85; }
                    else if (unidad === "U-03") { sentido = "REGRESO"; progresoActual = 50; }

                    let indiceInicial = Math.floor((progresoActual / 100) * (data.length - 1));
                    let marker = L.marker(data[indiceInicial]).addTo(mapa);

                    poolBusesDinamicos.push({
                        unidad: unidad,
                        esRetrasado: esRetrasado,
                        sentido: sentido,
                        progreso: progresoActual,
                        marker: marker,
                        domLabel: $labelTrayecto,
                        isRendered: true // Bandera interna para controlar adición/remoción del canvas
                    });

                    busMarkers.push(marker);
                });

                window.trackingSimuladorInterval = setInterval(() => {
                    poolBusesDinamicos.forEach(bus => {
                        // 1. CÁLCULO DE MOVIMIENTO CONSTANTE (Sigue calculándose tras bambalinas)
                        if (bus.sentido === "IDA") {
                            bus.progreso += 0.04;
                            if (bus.progreso > 100) bus.progreso = 0;
                        } else {
                            bus.progreso -= 0.04;
                            if (bus.progreso < 0) bus.progreso = 100;
                        }

                        let nuevoIndice = Math.floor((bus.progreso / 100) * (data.length - 1));
                        let coordActual = data[nuevoIndice];
                        let coordSiguiente = bus.sentido === "IDA"
                            ? data[Math.min(nuevoIndice + 1, data.length - 1)]
                            : data[Math.max(nuevoIndice - 1, 0)];

                        let anguloDireccion = calcularRumbo(coordActual, coordSiguiente);
                        let progresoVisual = bus.sentido === "IDA" ? bus.progreso : (100 - bus.progreso);
                        let textoDestino = bus.sentido === "IDA" ? `${origenReal} ➔ ${destinoReal}` : `${destinoReal} ➔ ${origenReal}`;

                        if (bus.domLabel) bus.domLabel.text(textoDestino);

                        // 2. FILTRADO CARTOGRÁFICO: CONTROL DE ICONOS SEGÚN ENFOQUE
                        if (unidadEnFoque !== null) {
                            // Si esta unidad NO es la seleccionada, se borra del mapa
                            if (bus.unidad !== unidadEnFoque) {
                                if (bus.isRendered) {
                                    bus.marker.remove();
                                    bus.isRendered = false;
                                }
                                return; // Frenamos la actualización del marcador oculto
                            }
                        } else {
                            // Si no hay enfoque, nos aseguramos de re-inyectarlo si fue extraído
                            if (!bus.isRendered) {
                                bus.marker.addTo(mapaInstance);
                                bus.isRendered = true;
                            }
                        }

                        // 3. ACTUALIZACIÓN GRÁFICA DEL MARCADOR VIVO
                        if (bus.marker && coordActual) {
                            bus.marker.setLatLng(coordActual);
                            let claseEstilo = bus.esRetrasado ? 'bus-marker-wrapper delayed' : 'bus-marker-wrapper';
                            
                            let nuevoIcono = L.divIcon({
                                html: `
                                    <div class="${claseEstilo}">
                                        <div class="bus-direction-pointer" style="transform: rotate(${anguloDireDirection = anguloDireccion}deg);"></div>
                                        <i class="fas fa-bus"></i>
                                        <div class="bus-label-tooltip">${bus.unidad}</div>
                                    </div>
                                `,
                                className: 'custom-bus-marker',
                                iconSize: [32, 32],
                                iconAnchor: [16, 16]
                            });
                            
                            bus.marker.setIcon(nuevoIcono);
                            bus.marker.bindPopup(`
                                <div style="color:#fff; font-family:'Inter',sans-serif; font-size:11px; line-height: 1.4;">
                                    <b style="color:${bus.esRetrasado ? '#ff9f43' : '#00f3ff'}; font-size:13px;">Unidad: ${bus.unidad}</b><br>
                                    <span style="color:#8fa0b0;">Rumbo: <b style="color:#fff;">${textoDestino}</b></span><br>
                                    <span style="color:#ccc;">Progreso: ${Math.floor(progresoVisual)}%</span>
                                </div>
                            `);

                            // 4. SEGUIMIENTO DINÁMICO DE CÁMARA (ZOOM Y CENTRADO EN VIVO)
                            if (unidadEnFoque !== null && bus.unidad === unidadEnFoque) {
                                // panTo desplaza la cámara de forma fluida sin tirones agresivos
                                mapaInstance.panTo(coordActual, { animate: true, duration: 0.2});
                            }
                        }
                    });
                }, 200);
            })
            .catch(error => console.error('Error en motor de mapas:', error));
    }

    /* ==========================================================================
       4. CONTROL INTERNO DE MENSAJERÍA Y CHAT (ENFOQUE Y DESENFOQUE)
       ========================================================================== */
    $(document).on('click', '.trip-mini-card', function(e) {
        e.preventDefault();
        var unitName = $(this).find('.unit-name-title').text().trim();
        unidadSeleccionadaChat = unitName;
        
        // ACTIVACIÓN DE SEGUIMIENTO EXCLUSIVO
        unidadEnFoque = unitName; 
        
        // Animamos la cámara de inmediato a un zoom cerrado (Ej: nivel 16 para ver calles detalladas)
        if (mapaInstance) {
            // Buscamos la posición actual exacta del marcador enfocado para inicializar el zoom de forma limpia
            busMarkers.forEach(m => {
                let iconHTML = m.getIcon().options.html;
                if (iconHTML && iconHTML.includes(unitName)) {
                    mapaInstance.setView(m.getLatLng(), 16, { animate: true, duration: 1 });
                }
            });
        }

        $('#unidadesRutaWrapper').hide(); 
        $('#chatActiveUnitName').text(unitName); 
        $('#chatMessages').empty(); 
        $('#unitChatSection').fadeIn(250); 
        $('#chatInput').focus();
    });

    // ESTO SUCEDE CUANDO EL USUARIO DA CLIC EN LA "X" PARA VOLVER A VER TODAS LAS UNIDADES
    $('#btnCloseChat').on('click', function() {
        $('#unitChatSection').hide();
        $('#unidadesRutaWrapper').fadeIn(200);
        unidadSeleccionadaChat = null;
        
        // DESENFOQUE Y RESTAURACIÓN GENERAL
        unidadEnFoque = null; 
        
        // Regresa la cámara al encuadre perfecto que cubre toda la ruta seleccionada
        if (mapaInstance && rutaPolylineInstance) {
            mapaInstance.fitBounds(rutaPolylineInstance.getBounds(), { padding: [40, 40], animate: true, duration: 1 });
        }
    });

    $('#sendMessageBtn').on('click', function(e) {
        e.preventDefault();
        procesarEnvioMensajePasajero();
    });

    $('#chatInput').on('keypress', function(e) {
        if (e.which === 13) { 
            e.preventDefault();
            procesarEnvioMensajePasajero();
        }
    });

    function procesarEnvioMensajePasajero() {
        var $input = $('#chatInput');
        var mensajeTexto = $input.val().trim();

        if (!mensajeTexto || !unidadSeleccionadaChat) return;

        var fecha = new Date();
        var horaFormateada = fecha.getHours().toString().padStart(2, '0') + ':' + fecha.getMinutes().toString().padStart(2, '0');

        var bubbleHTML = `
            <div class="chat-message self">
                <div class="chat-avatar">${window.usuarioActual.avatar}</div>
                <div class="chat-body">
                    <div class="chat-meta">
                        <span class="chat-user">${window.usuarioActual.nombre}</span>
                        <span class="chat-time">${horaFormateada}</span>
                    </div>
                    <div class="chat-bubble">${mensajeTexto}</div>
                </div>
            </div>
        `;

        var $msgContainer = $('#chatMessages');
        $msgContainer.append(bubbleHTML);
        $msgContainer.scrollTop($msgContainer[0].scrollHeight);
        $input.val('').focus();
    }
});