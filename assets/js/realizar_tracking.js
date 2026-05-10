document.addEventListener('DOMContentLoaded', () => {

    // =====================================================
    // MAPA
    // =====================================================

    const map = L.map('map-tracking', {
        zoomControl: false
    }).setView([13.6929, -89.2182], 8);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // =====================================================
    // ELEMENTOS DOM
    // =====================================================

    const terminalSelect = document.getElementById('terminalSelect');
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
    // ICONOS
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
    // DATOS DE TERMINALES Y RUTAS
    // =====================================================

    // =====================================================
// DATOS DE TERMINALES Y RUTAS
// =====================================================

const terminalRoutes = {

    occidente: [
        {
            id: 'OCC-201',
            nombre: 'Santa Ana',
            estado: 'ON TIME',
            coords: [
                [13.6929, -89.2182],
                [13.7150, -89.3500],
                [13.9778, -89.5639]
            ]
        },

        {
            id: 'OCC-205',
            nombre: 'Sonsonate',
            estado: '+12M',
            coords: [
                [13.6929, -89.2182],
                [13.7400, -89.4500],
                [13.7189, -89.7242]
            ]
        }
    ],

    oriente: [
        {
            id: 'ORI-101',
            nombre: 'San Miguel',
            estado: 'ON TIME',
            coords: [
                [13.6929, -89.2182],
                [13.5500, -88.7000],
                [13.4833, -88.1833]
            ]
        },

        {
            id: 'ORI-102',
            nombre: 'Usulután',
            estado: '+5M',
            coords: [
                [13.6929, -89.2182],
                [13.5000, -88.6000],
                [13.3500, -88.4500]
            ]
        }
    ]
};

    // =====================================================
// CARGAR RUTAS DINÁMICAS (CORREGIDO)
// =====================================================
function loadRoutes(terminal) {

// Si no hay terminal seleccionada, limpiar y salir
    if (!terminal || terminal === "") {
        routesContainer.innerHTML = '<p style="color:var(--text-muted); text-align:center; font-size:0.85rem; margin-top:20px;">Selecciona una terminal para ver las rutas</p>';
        routeCount.textContent = '0 routes';
        return;
    }

    // 1. Limpieza radical del estado
    routesContainer.innerHTML = '';
    selectedRoute = null; 
    startTrackingBtn.disabled = true; // Se fuerza el bloqueo del botón
    clearMap(); 

    const routes = terminalRoutes[terminal] || [];
    routeCount.textContent = `${routes.length} routes`;

    routes.forEach((route) => {
        const routeItem = document.createElement('div');
        routeItem.className = 'route-item';
        
        routeItem.innerHTML = `
            <div class="route-marker" style="background:${colorPicker.value};"></div>
            <div class="route-details">
                <span class="code text-white">${route.id}</span>
                <span class="dest text-light">${route.nombre}</span>
            </div>
            <span class="badge on-time">${route.estado}</span>
        `;

        // 2. Evento de selección estrictamente manual
        routeItem.addEventListener('click', () => {
            // Eliminar estado activo de cualquier otra ruta
            document.querySelectorAll('.route-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Asignar selección a la ruta actual
            routeItem.classList.add('active');
            selectedRoute = route;
            
            // Validar
            validateTracking();
        });

        routesContainer.appendChild(routeItem);
    });
}

// =====================================================
// VALIDAR BOTÓN (CORREGIDO)
// =====================================================
function validateTracking() {
    // El select siempre tiene valor, la única condición real es selectedRoute
    if (selectedRoute) {
        startTrackingBtn.disabled = false;
    } else {
        startTrackingBtn.disabled = true;
    }
}

    // =====================================================
    // LIMPIAR MAPA
    // =====================================================

    function clearMap() {

    if (rutaLine && map.hasLayer(rutaLine)) {
        map.removeLayer(rutaLine);
    }

    if (busMarker && map.hasLayer(busMarker)) {
        map.removeLayer(busMarker);
    }

    if (destMarker && map.hasLayer(destMarker)) {
        map.removeLayer(destMarker);
    }
}

    // =====================================================
    // DIBUJAR TRACKING
    // =====================================================

    function renderTracking() {

        if (!selectedRoute) return;

        clearMap();

        const routeCoords = selectedRoute.coords;

        // Línea de tracking
        rutaLine = L.polyline(routeCoords, {
            color: colorPicker.value,
            weight: 5,
            opacity: 0.8,
            dashArray: '8, 12'
        }).addTo(map);

        // Destino final
        destMarker = L.marker(
            routeCoords[routeCoords.length - 1],
            { icon: destIcon }
        ).addTo(map);

        // Busito
        busMarker = L.marker(
            routeCoords[1],
            { icon: busIcon }
        ).addTo(map);

        map.fitBounds(
            rutaLine.getBounds(),
            {
                padding: [40, 40]
            }
        );
    }

    // =====================================================
    // EVENTO CAMBIO TERMINAL
    // =====================================================

    terminalSelect.addEventListener('change', () => {

        loadRoutes(terminalSelect.value);

    });

    // =====================================================
// EVENTO BOTÓN TRACKING (CORREGIDO)
// =====================================================
startTrackingBtn.addEventListener('click', () => {
    // 1. Validar que haya una ruta seleccionada
    if (!selectedRoute) return;

    // 2. Seleccionamos los elementos de la UI
    const statusDot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');
    const statusPill = document.getElementById('statusPillContainer');

    // 3. Cambiar visualmente el indicador a "Live"
    statusDot.classList.add('active'); // Activa el color verde y la animación CSS
    statusText.textContent = 'Live Tracking';
    
    // Cambiamos el borde del pill a verde para que haga juego
    if (statusPill) {
        statusPill.style.borderColor = '#2ecc71';
    }

    // 4. Ejecutar el dibujo en el mapa
    renderTracking();

    console.log("Tracking iniciado correctamente para:", selectedRoute.nombre);
});

    // =====================================================
    // CAMBIO DE COLOR EN TIEMPO REAL
    // =====================================================

    colorPicker.addEventListener('input', () => {

        // Actualizar colores visuales de rutas
        document.querySelectorAll('.route-marker')
            .forEach(marker => {
                marker.style.background = colorPicker.value;
            });

        // Redibujar si ya hay ruta activa
        if (selectedRoute && rutaLine) {
            renderTracking();
        }
    });

    // =====================================================
    // RELOJ
    // =====================================================

    function updateClock() {

        document.getElementById('liveClock').innerText =
            new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
    }

    setInterval(updateClock, 1000);

    updateClock();

    // =====================================================
    // INICIALIZACIÓN
    // =====================================================

    console.log("SCRIPT FUNCIONANDO");


   // Eliminamos la carga automática. 
// Ahora el contenedor mostrará un mensaje inicial o estará vacío.
routesContainer.innerHTML = '<p style="color:var(--text-muted); text-align:center; font-size:0.85rem; margin-top:20px;">Selecciona una terminal para ver las rutas</p>';
routeCount.textContent = '0 routes';

});