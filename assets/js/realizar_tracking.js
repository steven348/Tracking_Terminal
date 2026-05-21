document.addEventListener('DOMContentLoaded', () => {

    // =====================================================
    // INICIALIZACIÓN SELECTORES SELECT2
    // =====================================================
    $('#terminalSelect').select2({
        placeholder: "Buscar terminal...",
        allowClear: false,
        width: '100%'
    });

    // Inicializar el nuevo selector de dirección
    $('#directionSelect').select2({
        minimumResultsForSearch: Infinity,
        width: '100%'
    });

    // Escucha de cambios en terminal
    $('#terminalSelect').on('change', function () {
        loadRoutes($(this).val());
    });

    // Escucha de cambios en dirección (actualiza mapa al cambiar sentido)
    $('#directionSelect').on('change', function () {
        if (selectedRoute && rutaLine) {
            renderTracking();
        }
    });

    // Refuerzo visual para menús desplegables de Select2
    $(document).on('select2:open', function() {
        setTimeout(function() {
            $('.select2-results__option[aria-selected="true"]')
                .css({ 'background-color': '#00C2C7', 'color': '#071A2D' });
        }, 10);
    });

    // =====================================================
    // CONFIGURACIÓN DEL MAPA
    // =====================================================
    const map = L.map('map-tracking', {
        zoomControl: false
    }).setView([13.6929, -89.2182], 8);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // =====================================================
    // ELEMENTOS DEL DOM
    // =====================================================
    const colorPicker = document.getElementById('routeColor');
    const routesContainer = document.getElementById('routesContainer');
    const routeCount = document.getElementById('routeCount');
    const startTrackingBtn = document.getElementById('startTrackingBtn');

    // =====================================================
    // VARIABLES DE ESTADO
    // =====================================================
    let rutaLine = null;
    let busMarker = null;
    let destMarker = null;
    let selectedRoute = null;

    // =====================================================
    // ICONOS DE LEAFLET
    // =====================================================
    const busIcon = L.divIcon({
        html: '<i class="fas fa-bus"></i>',
        className: 'bus-icon-div',
        iconSize: [35, 35],
        iconAnchor: [17, 17]
    });

    const destIcon = L.divIcon({
        html: '<i class="fas fa-location-dot"></i>',
        className: 'dest-icon-div',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    // =====================================================
    // BASE DE DATOS LOCAL
    // =====================================================
    const terminalRoutes = {
        cabanas: [
            {
                id: 'CAB-111',
                nombre: 'Ilobasco - Terminal de Oriente',
                jsonPath: '/tracking_terminal/assets/rutas/111.json',
            },
            {
                id: 'CAB-111-1',
                nombre: 'Ilobasco - Terminal de Oriente',
                jsonPath: '/tracking_terminal/assets/rutas/111-1.json',
            },
            {
                id: 'CAB-112',
                nombre: 'Sensuntepeque - Terminal de Oriente',
                jsonPath: '/tracking_terminal/assets/rutas/112.json',
            },
            {
                id: 'CAB-112-A',
                nombre: 'Sensuntepeque - Terminal de Oriente',
                jsonPath: '/tracking_terminal/assets/rutas/112-A.json',
            }
        ],
        oriente: [
            {
                id: 'ORI-101',
                nombre: 'San Miguel - Terminal de Oriente',
                coords: [
                    [13.6929, -89.2182],
                    [13.5500, -88.7000],
                    [13.4833, -88.1833]
                ]
            },
            {
                id: 'ORI-102',
                nombre: 'Usulután - Terminal de Oriente',
                coords: [
                    [13.6929, -89.2182],
                    [13.5000, -88.6000],
                    [13.3500, -88.4500]
                ]
            }
        ]
    };

    // =====================================================
    // CONTROLADOR DE INTERFAZ
    // =====================================================
    function loadRoutes(terminal) {
        if (!terminal || terminal === "") {
            routesContainer.innerHTML = '<p class="no-routes-msg">Selecciona una terminal para ver las rutas</p>';
            routeCount.textContent = '0 routes';
            return;
        }

        routesContainer.innerHTML = '';
        selectedRoute = null; 
        startTrackingBtn.disabled = true;
        $('#directionGroup').hide(); 
        clearMap(); 

        const routes = terminalRoutes[terminal] || [];
        routeCount.textContent = `${routes.length} routes`;

        if (routes.length === 0) {
            routesContainer.innerHTML = '<p class="no-routes-msg">No hay rutas registradas para esta terminal</p>';
            return;
        }

        routes.forEach((route) => {
            const routeSquare = document.createElement('div');
            routeSquare.className = 'route-square';
            
            const routeNumber = route.id.substring(route.id.indexOf('-') + 1);
            routeSquare.textContent = routeNumber;

            routeSquare.addEventListener('click', () => {
                document.querySelectorAll('.route-square').forEach(item => {
                    item.classList.remove('active');
                });
                
                routeSquare.classList.add('active');
                selectedRoute = route;
                
                configureDirectionOptions(route.nombre);
                validateTracking();
            });

            routesContainer.appendChild(routeSquare);
        });
    }

    function configureDirectionOptions(routeName) {
        const parts = routeName.split(' - ');
        const origin = parts[0] || 'Punto A';
        const destination = parts[1] || 'Punto B';

        const directionSelect = $('#directionSelect');
        directionSelect.empty();
        
        // Mantener las etiquetas UI semánticas e intactas
        directionSelect.append(new Option(`Ida: ${origin} ➔ ${destination}`, 'ida'));
        directionSelect.append(new Option(`Regreso: ${destination} ➔ ${origin}`, 'vuelta'));
        
        directionSelect.trigger('change');
        $('#directionGroup').fadeIn(200); 
    }

    function validateTracking() {
        startTrackingBtn.disabled = !selectedRoute;
    }

    function clearMap() {
        if (rutaLine && map.hasLayer(rutaLine)) map.removeLayer(rutaLine);
        if (busMarker && map.hasLayer(busMarker)) map.removeLayer(busMarker);
        if (destMarker && map.hasLayer(destMarker)) map.removeLayer(destMarker);
    }

    // =====================================================
    // RENDERIZADO CON CORRECCIÓN DE SENTIDO GEOGRÁFICO
    // =====================================================
    async function renderTracking() {
        if (!selectedRoute) return;

        clearMap();
        const chosenDirection = document.getElementById('directionSelect').value;

        // Caso A: Coordenadas locales estáticas (Oriente)
        if (selectedRoute.coords) {
            let coordenadasListas = [...selectedRoute.coords];
            
            // Corrección: Como el array por defecto empieza en San Salvador (Terminal),
            // si el usuario quiere ir de 'ida' (Municipio -> Terminal), hay que invertirlo.
            if (chosenDirection === 'ida') {
                coordenadasListas.reverse(); 
            }

            rutaLine = L.polyline(coordenadasListas, {
                color: colorPicker.value,
                weight: 5,
                opacity: 0.8,
                dashArray: '8, 12'
            }).addTo(map);

            busMarker = L.marker(coordenadasListas[0], { icon: busIcon }).addTo(map);
            destMarker = L.marker(coordenadasListas[coordenadasListas.length - 1], { icon: destIcon }).addTo(map);
            
            map.fitBounds(rutaLine.getBounds(), { padding: [40, 40] });
            animateBus(busMarker, coordenadasListas, 0, 80);
            return;
        }

        // Caso B: Archivos GeoJSON remotos (Cabañas)
        if (selectedRoute.jsonPath) {
            try {
                const response = await fetch(selectedRoute.jsonPath);
                if (!response.ok) throw new Error("No se pudo cargar el archivo JSON.");
                
                const geoData = await response.json();
                const coordenadasSucias = geoData.features[0].geometry.coordinates;
                
                // Mapear de [Lng, Lat] a [Lat, Lng]
                let coordenadasListas = coordenadasSucias.map(punto => [punto[1], punto[0]]);

                // Corrección idéntica: Invertimos el archivo para cumplir con la Ida (Municipio -> Terminal)
                if (chosenDirection === 'ida') {
                    coordenadasListas.reverse(); 
                }

                rutaLine = L.polyline(coordenadasListas, {
                    color: colorPicker.value,
                    weight: 5,
                    opacity: 0.8,
                    dashArray: '8, 12'
                }).addTo(map);

                busMarker = L.marker(coordenadasListas[0], { icon: busIcon }).addTo(map);
                destMarker = L.marker(coordenadasListas[coordenadasListas.length - 1], { icon: destIcon }).addTo(map);
                
                map.fitBounds(rutaLine.getBounds(), { padding: [40, 40] });
                animateBus(busMarker, coordenadasListas, 0, 80);

            } catch (error) {
                console.error("Error cargando el mapa de ruta:", error);
                alert("Error crítico al leer las coordenadas del archivo JSON.");
            }
        }
    }

    // =====================================================
    // ANIMACIÓN CONTINUA DEL BUSITO
    // =====================================================
    function animateBus(marker, coords, index, speed) {
        if (index >= coords.length - 1) {
            console.log("Llegada a destino exitosa");
            return; 
        }
        const end = coords[index + 1];
        marker.setLatLng(end);

        setTimeout(() => {
            if (busMarker === marker) { 
                animateBus(marker, coords, index + 1, speed);
            }
        }, speed);
    }

    // =====================================================
    // ACCIÓN: DISPARAR TRACKING EN VIVO
    // =====================================================
    startTrackingBtn.addEventListener('click', () => {
        if (!selectedRoute) return;

        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        const statusPill = document.getElementById('statusPillContainer');

        if (statusDot) statusDot.classList.add('active');
        if (statusText) statusText.textContent = 'Live Tracking';
        if (statusPill) statusPill.style.borderColor = '#2ecc71';

        renderTracking();
    });

    colorPicker.addEventListener('input', () => {
        if (selectedRoute && rutaLine) {
            rutaLine.setStyle({ color: colorPicker.value });
        }
    });

    // =====================================================
    // CONTROL DEL RELOJ
    // =====================================================
    function updateClock() {
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.innerText = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    routesContainer.innerHTML = '<p class="no-routes-msg">Selecciona una terminal para ver las rutas</p>';
    routeCount.textContent = '0 routes';
});