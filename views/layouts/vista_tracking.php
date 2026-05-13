<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Busito SV – Ver Trackings</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="../../assets/css/vista_tracking.css">
</head>
<body>

<!-- NAVBAR -->
<nav>
  <div class="nav-brand">
    <div class="logo-wrap">🚌</div>
    <h1>BUSITO <span>SV</span></h1>
  </div>
  <div class="nav-right">
    <div class="user-pill">
      <div class="avatar">👤</div>
      <span>Usuario</span>
    </div>
    <button class="btn-logout">CERRAR SESIÓN</button>
  </div>
</nav>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <a>Inicio</a> › <span>Ver Trackings</span>
</div>

<!-- TITLE -->
<div class="page-header">
  <h2>VER TRACKINGS</h2>
  <div class="badge-live">EN VIVO</div>
</div>

<!-- SEARCH -->
<div class="search-section">
  <div class="search-wrap">
    <span class="s-icon">🔍</span>
    <input type="text" id="searchInput" placeholder="Buscar ruta, terminal o destino…" oninput="filterCards()"/>
    <span class="s-badge" id="countBadge">5 activas</span>
  </div>
</div>

<!-- FAVORITES -->
<div class="fav-section">
  <div class="section-label">Rutas Frecuentes</div>
  <div class="fav-chips">
    <div class="fav-chip" onclick="filterByFav(this,'R-101')"><span class="dot" style="background:#22c55e"></span>R-101 · Soyapango</div>
    <div class="fav-chip" onclick="filterByFav(this,'R-44')"><span class="dot" style="background:#f59e0b"></span>R-44 · Centro</div>
    <div class="fav-chip" onclick="filterByFav(this,'R-9')"><span class="dot" style="background:#22c55e"></span>R-9 · Mejicanos</div>
    <div class="fav-chip" onclick="filterByFav(this,'R-52')"><span class="dot" style="background:#ef4444"></span>R-52 · Apopa</div>
  </div>
</div>

<!-- MAIN -->
<div class="main-layout">

  <!-- CARDS -->
  <div class="cards-col">
    <div class="section-label" style="margin-bottom:12px;">Trackings Activos</div>
    <div class="cards-list" id="cardsList"></div>
  </div>

  <!-- MAP -->
  <div class="map-panel">
    <div class="map-header">
      <div class="map-title">
        🗺️ Preview del Mapa
        <span class="map-route-badge" id="mapRouteBadge" style="display:none"></span>
      </div>
      <span class="map-hint" id="mapHint">Selecciona una ruta para ver el recorrido</span>
    </div>
    <div id="map"></div>
    <div class="legend">
      <div class="legend-item"><div class="legend-dot" style="background:#22c55e"></div>A tiempo</div>
      <div class="legend-item"><div class="legend-dot" style="background:#f59e0b"></div>Retraso leve</div>
      <div class="legend-item"><div class="legend-dot" style="background:#ef4444"></div>Retrasado</div>
      <div class="legend-item"><div class="legend-dot" style="background:var(--accent2)"></div>Ruta activa</div>
    </div>
  </div>

</div>

<script>
// ── DATA ──────────────────────────────────────────────────────────────────────
const routes = [
  {
    id: 'R-101', name: 'Soyapango – Terminal Oriente', terminal: 'T. Oriente',
    user: 'Carlos M.', initials: 'CM', eta: 8, status: 'ok', statusLabel: 'A tiempo',
    color: '#22c55e',
    polyline: [
      [13.6929, -89.1885],[13.6970, -89.1950],[13.7000, -89.2020],
      [13.7040, -89.2100],[13.7080, -89.2150],[13.7110, -89.2190],
      [13.7130, -89.2230],[13.7160, -89.2260],[13.7192, -89.2290]
    ]
  },
  {
    id: 'R-44', name: 'Centro Histórico – Mercado Central', terminal: 'T. Occidente',
    user: 'Ana L.', initials: 'AL', eta: 14, status: 'warn', statusLabel: 'Retraso leve',
    color: '#f59e0b',
    polyline: [
      [13.6989, -89.1910],[13.6960, -89.1970],[13.6940, -89.2040],
      [13.6920, -89.2120],[13.6910, -89.2190],[13.6900, -89.2240]
    ]
  },
  {
    id: 'R-9', name: 'Mejicanos – Plaza Morazán', terminal: 'T. Norte',
    user: 'Roberto H.', initials: 'RH', eta: 5, status: 'ok', statusLabel: 'Llegando',
    color: '#22c55e',
    polyline: [
      [13.7250, -89.2100],[13.7200, -89.2130],[13.7160, -89.2160],
      [13.7120, -89.2190],[13.7080, -89.2220],[13.7040, -89.2250],
      [13.7010, -89.2270]
    ]
  },
  {
    id: 'R-52', name: 'Apopa – Terminal Norte', terminal: 'T. Norte',
    user: 'María G.', initials: 'MG', eta: 22, status: 'late', statusLabel: 'Retrasado',
    color: '#ef4444',
    polyline: [
      [13.8000, -89.1900],[13.7900, -89.1930],[13.7800, -89.1960],
      [13.7700, -89.1990],[13.7600, -89.2010],[13.7500, -89.2030],
      [13.7400, -89.2060],[13.7300, -89.2090]
    ]
  },
  {
    id: 'R-77', name: 'Santa Ana – San Salvador', terminal: 'T. Occidente',
    user: 'Diego P.', initials: 'DP', eta: 31, status: 'warn', statusLabel: 'Retraso leve',
    color: '#f59e0b',
    polyline: [
      [13.9978, -89.5597],[13.9700, -89.5300],[13.9400, -89.5000],
      [13.9100, -89.4600],[13.8800, -89.4200],[13.8500, -89.3800],
      [13.8100, -89.3400],[13.7700, -89.3000],[13.7400, -89.2700],[13.7200, -89.2300]
    ]
  }
];

// ── MAP INIT ──────────────────────────────────────────────────────────────────
const map = L.map('map', { zoomControl: true }).setView([13.7192, -89.2290], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(map);

// Force dark style via CSS filter on tile layer
const style = document.createElement('style');
document.head.appendChild(style);

let activePolyline = null;
let busMarker = null;
let selectedCard = null;

// ── RENDER CARDS ──────────────────────────────────────────────────────────────
function renderCards(data) {
  const list = document.getElementById('cardsList');
  list.innerHTML = '';
  document.getElementById('countBadge').textContent = data.length + ' activas';

  data.forEach(r => {
    const card = document.createElement('div');
    card.className = 'route-card' + (selectedCard === r.id ? ' selected' : '');
    card.style.setProperty('--status-color', r.color);
    card.dataset.id = r.id;
    card.innerHTML = `
      <div class="card-top">
        <div>
          <div class="route-number">${r.id}</div>
          <div class="route-name">${r.name}</div>
        </div>
        <div class="status-badge status-${r.status}">${r.statusLabel}</div>
      </div>
      <div class="card-mid">
        <div class="user-info">
          <div class="mini-avatar">${r.initials}</div>
          <div class="user-name">Tracking por <strong>${r.user}</strong></div>
        </div>
      </div>
      <div class="card-bot">
        <div>
          <div class="eta-label">ETA ESTIMADO</div>
          <div class="eta-block">
            <span class="eta-value">${r.eta}</span>
            <span class="eta-unit">min</span>
          </div>
        </div>
        <div class="terminal-tag">Terminal: <span>${r.terminal}</span></div>
      </div>
    `;
    card.addEventListener('click', () => selectRoute(r));
    list.appendChild(card);
  });
}

// ── SELECT ROUTE ──────────────────────────────────────────────────────────────
function selectRoute(r) {
  selectedCard = r.id;
  renderCards(currentData);

  // Remove old
  if (activePolyline) map.removeLayer(activePolyline);
  if (busMarker) map.removeLayer(busMarker);

  // Draw polyline
  activePolyline = L.polyline(r.polyline, {
    color: r.color, weight: 5, opacity: .9,
    dashArray: r.status === 'late' ? '10 6' : null,
    lineCap: 'round', lineJoin: 'round'
  }).addTo(map);

  // Start dot
  L.circleMarker(r.polyline[0], {
    radius: 8, fillColor: '#fff', color: r.color,
    weight: 3, fillOpacity: 1
  }).addTo(map).bindPopup(`<b>Inicio</b><br>${r.name}`);

  // End dot
  L.circleMarker(r.polyline[r.polyline.length-1], {
    radius: 8, fillColor: r.color, color: '#fff',
    weight: 3, fillOpacity: 1
  }).addTo(map).bindPopup(`<b>Terminal</b><br>${r.terminal}`);

  // Bus marker (simulated midpoint)
  const mid = r.polyline[Math.floor(r.polyline.length / 2)];
  busMarker = L.divIcon({
    className: '',
    html: `<div style="
      background:${r.color}; color:#fff;
      border-radius:50%; width:30px; height:30px;
      display:flex; align-items:center; justify-content:center;
      font-size:16px; border:2px solid #fff;
      box-shadow:0 2px 8px #00000060;
    ">🚌</div>`,
    iconSize: [30,30], iconAnchor: [15,15]
  });
  L.marker(mid, { icon: busMarker }).addTo(map)
    .bindPopup(`<b>${r.id}</b><br>Trackeando: ${r.user}<br>ETA: ${r.eta} min`);

  map.fitBounds(activePolyline.getBounds(), { padding: [40,40] });

  // Header
  document.getElementById('mapRouteBadge').textContent = r.id;
  document.getElementById('mapRouteBadge').style.display = '';
  document.getElementById('mapHint').textContent = r.name;
}

// ── FILTER ────────────────────────────────────────────────────────────────────
let currentData = [...routes];

function filterCards() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  currentData = routes.filter(r =>
    r.id.toLowerCase().includes(q) ||
    r.name.toLowerCase().includes(q) ||
    r.terminal.toLowerCase().includes(q)
  );
  renderCards(currentData);
}

function filterByFav(el, id) {
  document.querySelectorAll('.fav-chip').forEach(c => c.classList.remove('active'));
  if (el.classList.contains('active')) {
    el.classList.remove('active');
    currentData = [...routes];
  } else {
    el.classList.add('active');
    currentData = routes.filter(r => r.id === id);
  }
  document.getElementById('searchInput').value = '';
  renderCards(currentData);
}

// ── INIT ──────────────────────────────────────────────────────────────────────
renderCards(routes);
</script>
</body>
</html>