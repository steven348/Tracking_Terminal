// 1. Aseguramos la existencia de las variables globales de inmediato en memoria
window.mapMain = window.mapMain || null;
window.currentRouteLine = window.currentRouteLine || null;

// Usamos una función autoejecutable (IIFE) para aislar el resto de variables locales (latSV, unidades, etc.)
(() => {
    // Verificar si el contenedor del mapa realmente existe en el DOM actual
    const mapContainer = document.getElementById('map-osm');
    
    if (!mapContainer) {
        console.warn("⚠️ [Busito SV] Contenedor '#map-osm' no encontrado en esta vista. Se aborta la inicialización del mapa de forma limpia.");
        return;
    }

    try {
        // 2. Coordenadas de El Salvador (Centro)
        const latSV = 13.794185;
        const lngSV = -88.89653;

        // 3. INICIALIZAR MAPA GLOBAL (Inmediato, sin esperar eventos externos)
        window.mapMain = L.map('map-osm').setView([latSV, lngSV], 9);

        // 4. Capa oscura de OpenStreetMap (CartoDB Dark)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; Busito SV 2026',
            maxZoom: 19
        }).addTo(window.mapMain);


        console.log("🚀 [Busito SV] 'window.mapMain' se ha registrado globalmente de forma exitosa.");

    } catch (error) {
        console.error("❌ Error crítico al inicializar Leaflet en script_trackings.js:", error);
    }
})();