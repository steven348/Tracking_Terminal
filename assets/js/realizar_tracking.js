document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicializar mapa
    const map = L.map('map-tracking', { zoomControl: false }).setView([13.6929, -89.2182], 13);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const colorPicker = document.getElementById('routeColor');
    const sidebarIndicator = document.getElementById('sidebar-route-indicator');
    let rutaLine, busMarker, destMarker;

    // Ruta de ejemplo: San Salvador -> Santa Ana
    const trayectoria = [
        [13.6929, -89.2182],
        [13.7150, -89.3500],
        [13.9778, -89.5639]
    ];

    // Definición de Iconos
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

    function renderMapa(color) {
        // Limpiar para refrescar
        if (rutaLine) map.removeLayer(rutaLine);
        if (busMarker) map.removeLayer(busMarker);
        if (destMarker) map.removeLayer(destMarker);

        // Línea de seguimiento (estilo tracking)
        rutaLine = L.polyline(trayectoria, {
            color: color,
            weight: 5,
            opacity: 0.7,
            dashArray: '8, 12'
        }).addTo(map);

        // Icono de Destino final
        destMarker = L.marker(trayectoria[trayectoria.length - 1], { icon: destIcon }).addTo(map);

        // Icono del Busito en su ubicación actual (punto medio en este ejemplo)
        busMarker = L.marker(trayectoria[1], { icon: busIcon }).addTo(map);
        
        map.fitBounds(rutaLine.getBounds(), { padding: [40, 40] });
    }

    // Inicializar vista
    renderMapa(colorPicker.value);

    // Cambio de color en tiempo real
    colorPicker.addEventListener('input', (e) => {
        const val = e.target.value;
        renderMapa(val);
        // Sincronizar con el marcador de la sidebar
        sidebarIndicator.style.background = val;
    });

    // Reloj
    setInterval(() => {
        document.getElementById('liveClock').innerText = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }, 1000);
});