document.addEventListener('DOMContentLoaded', () => {
    // 1. Coordenadas de El Salvador (Centro)
    const latSV = 13.794185;
    const lngSV = -88.89653;

    // 2. Inicializar mapa en el div 'map-osm'
    const map = L.map('map-osm').setView([latSV, lngSV], 9);

    // 3. Capa oscura de OpenStreetMap (CartoDB Dark)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; Busito SV 2026',
        maxZoom: 19
    }).addTo(map);

    // 4. Marcadores de Unidades (Ejemplos)
    const unidades = [
        { nombre: "Unidad 101-B", pos: [13.6929, -89.2182], info: "Rumbo a San Salvador" },
        { nombre: "Unidad 29", pos: [13.7013, -89.1874], info: "En zona Metrocentro" }
    ];

    unidades.forEach(unit => {
        L.marker(unit.pos).addTo(map)
            .bindPopup(`<b>${unit.nombre}</b><br>${unit.info}`);
    });
});