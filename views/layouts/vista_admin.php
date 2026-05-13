<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Busito SV – Panel Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../../assets/css/vista_admin.css">
</head>
<body>

<!-- SIDEBAR -->
<aside>
  <div class="sb-brand">
    <div class="sb-logo">🚌</div>
    <h1>BUSITO <span>SV</span></h1>
  </div>

  <div class="sb-admin-badge">
    <div class="sb-admin-avatar">👨‍💼</div>
    <div class="sb-admin-info">
      <div class="name">Admin</div>
      <div class="role">Administrador</div>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="sb-section">Principal</div>
    <div class="sb-item active" onclick="setView('dashboard',this)">
      <span class="icon">📊</span> Dashboard
    </div>
    <div class="sb-item" onclick="setView('usuarios',this)">
      <span class="icon">👥</span> Usuarios
      <span class="badge">3</span>
    </div>
    <div class="sb-item" onclick="setView('trackings',this)">
      <span class="icon">📍</span> Trackings Activos
      <span class="badge badge-ok">12</span>
    </div>
    <div class="sb-section">Sistema</div>
    <div class="sb-item" onclick="setView('reportes',this)">
      <span class="icon">📋</span> Reportes
    </div>
    <div class="sb-item" onclick="setView('rutas',this)">
      <span class="icon">🗺️</span> Gestión de Rutas
    </div>
    <div class="sb-item" onclick="setView('config',this)">
      <span class="icon">⚙️</span> Configuración
    </div>
  </nav>

  <div class="sb-bottom">
    <button class="sb-logout">🚪 Cerrar Sesión</button>
  </div>
</aside>

<!-- MAIN -->
<main>
  <div class="topbar">
    <div class="topbar-left">
      <h2 id="topTitle">Dashboard</h2>
      <p id="topSub">Panel de administración · Busito SV</p>
    </div>
    <div class="topbar-right">
      <div class="notif-btn">🔔<div class="notif-dot"></div></div>
      <button class="refresh-btn">↻ Actualizar</button>
    </div>
  </div>

  <div class="content">

    <!-- ── DASHBOARD VIEW ── -->
    <div class="view active" id="view-dashboard">

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card" style="--c:#2d9cdb">
          <div class="stat-icon">👥</div>
          <div class="stat-label">Usuarios Registrados</div>
          <div class="stat-val">1,248</div>
          <div class="stat-sub"><span class="chg chg-up">↑ 8%</span> este mes</div>
        </div>
        <div class="stat-card" style="--c:#22c55e">
          <div class="stat-icon">📍</div>
          <div class="stat-label">Trackings Activos</div>
          <div class="stat-val" id="activeStat">12</div>
          <div class="stat-sub"><span class="chg chg-up">↑ 3</span> en la última hora</div>
        </div>
        <div class="stat-card" style="--c:#f59e0b">
          <div class="stat-icon">🗺️</div>
          <div class="stat-label">Rutas Registradas</div>
          <div class="stat-val">47</div>
          <div class="stat-sub"><span class="chg chg-up">↑ 2</span> nuevas esta semana</div>
        </div>
        <div class="stat-card" style="--c:#ef4444">
          <div class="stat-icon">⚠️</div>
          <div class="stat-label">Alertas Pendientes</div>
          <div class="stat-val">3</div>
          <div class="stat-sub"><span class="chg chg-dn">↑ 3</span> sin resolver</div>
        </div>
      </div>

      <!-- ROW 1 -->
      <div class="grid-row">

        <!-- USERS TABLE -->
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">👥 Usuarios Recientes</div>
            <button class="panel-action" onclick="setView('usuarios',document.querySelector('[onclick*=usuarios]'))">Ver todos</button>
          </div>
          <div class="tbl-wrap">
            <table>
              <thead>
                <tr>
                  <th>Usuario</th><th>Rol</th><th>Estado</th><th>Trackings</th><th></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><div class="td-user"><div class="td-avatar" style="background:#2563eb">CM</div><div><div class="td-name">Carlos Méndez</div><div class="td-email">carlos@mail.com</div></div></div></td>
                  <td><span class="pill pill-ok">Usuario</span></td>
                  <td><span class="pill pill-ok">Activo</span></td>
                  <td>34</td>
                  <td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Ban</button></div></td>
                </tr>
                <tr>
                  <td><div class="td-user"><div class="td-avatar" style="background:#7c3aed">AL</div><div><div class="td-name">Ana López</div><div class="td-email">ana@mail.com</div></div></div></td>
                  <td><span class="pill pill-ok">Usuario</span></td>
                  <td><span class="pill pill-warn">Advertencia</span></td>
                  <td>12</td>
                  <td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Ban</button></div></td>
                </tr>
                <tr>
                  <td><div class="td-user"><div class="td-avatar" style="background:#059669">RH</div><div><div class="td-name">Roberto Hernández</div><div class="td-email">rh@mail.com</div></div></div></td>
                  <td><span class="pill pill-adm">Admin</span></td>
                  <td><span class="pill pill-ok">Activo</span></td>
                  <td>89</td>
                  <td><div class="act-btns"><button class="act-btn act-edit">Editar</button></div></td>
                </tr>
                <tr>
                  <td><div class="td-user"><div class="td-avatar" style="background:#dc2626">MG</div><div><div class="td-name">María García</div><div class="td-email">mg@mail.com</div></div></div></td>
                  <td><span class="pill pill-ok">Usuario</span></td>
                  <td><span class="pill pill-off">Inactivo</span></td>
                  <td>7</td>
                  <td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Ban</button></div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ACTIVE ROUTES -->
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">📍 Trackings en Vivo</div>
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--ok)">
              <span style="width:7px;height:7px;border-radius:50%;background:var(--ok);display:inline-block;animation:pulse 1.2s infinite"></span> En vivo
            </div>
          </div>
          <div class="routes-list" id="liveRoutes"></div>
        </div>

      </div>

      <!-- ROW 2 -->
      <div class="grid-row2">

        <!-- ACTIVITY -->
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">🕐 Actividad Reciente</div>
          </div>
          <div class="activity-list">
            <div class="act-item"><div class="act-dot" style="background:var(--ok)"></div><div><div class="act-text">Carlos M. inició tracking en <strong>R-101 Soyapango</strong></div><div class="act-time">hace 3 min</div></div></div>
            <div class="act-item"><div class="act-dot" style="background:var(--warn)"></div><div><div class="act-text">Ruta <strong>R-44</strong> reporta retraso de 8 minutos</div><div class="act-time">hace 11 min</div></div></div>
            <div class="act-item"><div class="act-dot" style="background:var(--accent2)"></div><div><div class="act-text">Nuevo usuario registrado: <strong>Diego Pérez</strong></div><div class="act-time">hace 22 min</div></div></div>
            <div class="act-item"><div class="act-dot" style="background:var(--late)"></div><div><div class="act-text">Ruta <strong>R-52 Apopa</strong> con retraso crítico (+20 min)</div><div class="act-time">hace 35 min</div></div></div>
            <div class="act-item"><div class="act-dot" style="background:var(--ok)"></div><div><div class="act-text">Ana L. finalizó tracking en <strong>R-9 Mejicanos</strong></div><div class="act-time">hace 48 min</div></div></div>
            <div class="act-item"><div class="act-dot" style="background:var(--muted)"></div><div><div class="act-text">Respaldo automático del sistema completado</div><div class="act-time">hace 1 h</div></div></div>
          </div>
        </div>

        <!-- QUICK SETTINGS -->
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">⚙️ Ajustes Rápidos</div>
            <button class="panel-action" onclick="setView('config',document.querySelector('[onclick*=config]'))">Ver todo</button>
          </div>
          <div class="settings-list">
            <div class="sett-row">
              <span class="sett-icon">🗺️</span>
              <div class="sett-info"><div class="sett-label">Tracking en tiempo real</div><div class="sett-sub">Actualización cada 10 s</div></div>
              <label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label>
            </div>
            <div class="sett-row">
              <span class="sett-icon">🔔</span>
              <div class="sett-info"><div class="sett-label">Alertas de retraso</div><div class="sett-sub">Notificar si demora > 15 min</div></div>
              <label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label>
            </div>
            <div class="sett-row">
              <span class="sett-icon">📧</span>
              <div class="sett-info"><div class="sett-label">Emails de sistema</div><div class="sett-sub">Reportes diarios</div></div>
              <label class="toggle"><input type="checkbox"/><span class="toggle-slider"></span></label>
            </div>
            <div class="sett-row">
              <span class="sett-icon">🌙</span>
              <div class="sett-info"><div class="sett-label">Modo mantenimiento</div><div class="sett-sub">Deshabilita acceso de usuarios</div></div>
              <label class="toggle"><input type="checkbox"/><span class="toggle-slider"></span></label>
            </div>
            <div class="sett-row">
              <span class="sett-icon">🔐</span>
              <div class="sett-info"><div class="sett-label">Registro abierto</div><div class="sett-sub">Nuevos usuarios pueden registrarse</div></div>
              <label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label>
            </div>
          </div>
        </div>

      </div>
    </div><!-- /dashboard -->

    <!-- ── USUARIOS VIEW ── -->
    <div class="view" id="view-usuarios">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">👥 Gestión de Usuarios</div>
          <button class="panel-action">+ Nuevo Usuario</button>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Trackings</th><th>Registro</th><th>Acciones</th></tr>
            </thead>
            <tbody>
              <tr><td><div class="td-user"><div class="td-avatar" style="background:#2563eb">CM</div><div><div class="td-name">Carlos Méndez</div><div class="td-email">carlos@mail.com</div></div></div></td><td><span class="pill pill-ok">Usuario</span></td><td><span class="pill pill-ok">Activo</span></td><td>34</td><td style="color:var(--muted);font-size:12px">12 Ene 2025</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Eliminar</button></div></td></tr>
              <tr><td><div class="td-user"><div class="td-avatar" style="background:#7c3aed">AL</div><div><div class="td-name">Ana López</div><div class="td-email">ana@mail.com</div></div></div></td><td><span class="pill pill-ok">Usuario</span></td><td><span class="pill pill-warn">Advertencia</span></td><td>12</td><td style="color:var(--muted);font-size:12px">3 Feb 2025</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Eliminar</button></div></td></tr>
              <tr><td><div class="td-user"><div class="td-avatar" style="background:#059669">RH</div><div><div class="td-name">Roberto Hernández</div><div class="td-email">rh@mail.com</div></div></div></td><td><span class="pill pill-adm">Admin</span></td><td><span class="pill pill-ok">Activo</span></td><td>89</td><td style="color:var(--muted);font-size:12px">1 Nov 2024</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button></div></td></tr>
              <tr><td><div class="td-user"><div class="td-avatar" style="background:#dc2626">MG</div><div><div class="td-name">María García</div><div class="td-email">mg@mail.com</div></div></div></td><td><span class="pill pill-ok">Usuario</span></td><td><span class="pill pill-off">Inactivo</span></td><td>7</td><td style="color:var(--muted);font-size:12px">20 Mar 2025</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Eliminar</button></div></td></tr>
              <tr><td><div class="td-user"><div class="td-avatar" style="background:#d97706">DP</div><div><div class="td-name">Diego Pérez</div><div class="td-email">dp@mail.com</div></div></div></td><td><span class="pill pill-ok">Usuario</span></td><td><span class="pill pill-ok">Activo</span></td><td>2</td><td style="color:var(--muted);font-size:12px">10 May 2026</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Eliminar</button></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── TRACKINGS VIEW ── -->
    <div class="view" id="view-trackings">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">📍 Trackings Activos</div>
          <div style="font-size:12px;color:var(--ok);display:flex;align-items:center;gap:5px">
            <span style="width:7px;height:7px;border-radius:50%;background:var(--ok);display:inline-block;animation:pulse 1.2s infinite"></span>12 en vivo
          </div>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr><th>Ruta</th><th>Usuario</th><th>Estado</th><th>ETA</th><th>Terminal</th><th>Inicio</th></tr>
            </thead>
            <tbody id="trackingTable"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── REPORTES VIEW ── -->
    <div class="view" id="view-reportes">
      <div class="stats-grid">
        <div class="stat-card" style="--c:#2d9cdb"><div class="stat-icon">📍</div><div class="stat-label">Trackings este mes</div><div class="stat-val">3,412</div><div class="stat-sub"><span class="chg chg-up">↑ 14%</span> vs mes anterior</div></div>
        <div class="stat-card" style="--c:#22c55e"><div class="stat-icon">✅</div><div class="stat-label">Trackings completados</div><div class="stat-val">3,389</div><div class="stat-sub"><span class="chg chg-up">99.2%</span> tasa de éxito</div></div>
        <div class="stat-card" style="--c:#f59e0b"><div class="stat-icon">⚠️</div><div class="stat-label">Rutas con retraso</div><div class="stat-val">87</div><div class="stat-sub"><span class="chg chg-dn">↑ 5%</span> vs semana anterior</div></div>
        <div class="stat-card" style="--c:#ef4444"><div class="stat-icon">🚫</div><div class="stat-label">Trackings cancelados</div><div class="stat-val">23</div><div class="stat-sub"><span class="chg">0.7%</span> del total</div></div>
      </div>
      <div class="panel">
        <div class="panel-head"><div class="panel-title">📋 Reportes Disponibles</div></div>
        <div class="settings-list">
          <div class="sett-row"><span class="sett-icon">📄</span><div class="sett-info"><div class="sett-label">Reporte mensual de rutas</div><div class="sett-sub">Mayo 2026 · PDF</div></div><button class="act-btn act-edit" style="padding:5px 14px">Descargar</button></div>
          <div class="sett-row"><span class="sett-icon">📊</span><div class="sett-info"><div class="sett-label">Estadísticas de usuarios</div><div class="sett-sub">Q1 2026 · Excel</div></div><button class="act-btn act-edit" style="padding:5px 14px">Descargar</button></div>
          <div class="sett-row"><span class="sett-icon">🗺️</span><div class="sett-info"><div class="sett-label">Análisis de rutas críticas</div><div class="sett-sub">Semana 19 · PDF</div></div><button class="act-btn act-edit" style="padding:5px 14px">Descargar</button></div>
        </div>
      </div>
    </div>

    <!-- ── RUTAS VIEW ── -->
    <div class="view" id="view-rutas">
      <div class="panel">
        <div class="panel-head"><div class="panel-title">🗺️ Gestión de Rutas</div><button class="panel-action">+ Nueva Ruta</button></div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>ID</th><th>Nombre</th><th>Terminal</th><th>Estado</th><th>Trackings hoy</th><th>Acciones</th></tr></thead>
            <tbody>
              <tr><td style="font-family:'Rajdhani',sans-serif;font-size:16px;color:var(--accent2)">R-101</td><td>Soyapango – Terminal Oriente</td><td>T. Oriente</td><td><span class="pill pill-ok">Activa</span></td><td>28</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Desactivar</button></div></td></tr>
              <tr><td style="font-family:'Rajdhani',sans-serif;font-size:16px;color:var(--accent2)">R-44</td><td>Centro Histórico – Mercado Central</td><td>T. Occidente</td><td><span class="pill pill-warn">Retraso</span></td><td>15</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Desactivar</button></div></td></tr>
              <tr><td style="font-family:'Rajdhani',sans-serif;font-size:16px;color:var(--accent2)">R-9</td><td>Mejicanos – Plaza Morazán</td><td>T. Norte</td><td><span class="pill pill-ok">Activa</span></td><td>41</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Desactivar</button></div></td></tr>
              <tr><td style="font-family:'Rajdhani',sans-serif;font-size:16px;color:var(--accent2)">R-52</td><td>Apopa – Terminal Norte</td><td>T. Norte</td><td><span class="pill pill-late" style="color:var(--late);background:#ef444412;border-color:var(--late)">Crítica</span></td><td>9</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Desactivar</button></div></td></tr>
              <tr><td style="font-family:'Rajdhani',sans-serif;font-size:16px;color:var(--accent2)">R-77</td><td>Santa Ana – San Salvador</td><td>T. Occidente</td><td><span class="pill pill-ok">Activa</span></td><td>7</td><td><div class="act-btns"><button class="act-btn act-edit">Editar</button><button class="act-btn act-del">Desactivar</button></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── CONFIG VIEW ── -->
    <div class="view" id="view-config">
      <div class="grid-row2">
        <div class="panel">
          <div class="panel-head"><div class="panel-title">⚙️ Configuración General</div></div>
          <div class="settings-list">
            <div class="sett-row"><span class="sett-icon">🗺️</span><div class="sett-info"><div class="sett-label">Tracking en tiempo real</div><div class="sett-sub">Actualización cada 10 s</div></div><label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label></div>
            <div class="sett-row"><span class="sett-icon">🔔</span><div class="sett-info"><div class="sett-label">Alertas de retraso</div><div class="sett-sub">Notificar si demora > 15 min</div></div><label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label></div>
            <div class="sett-row"><span class="sett-icon">📧</span><div class="sett-info"><div class="sett-label">Emails del sistema</div><div class="sett-sub">Reportes automáticos diarios</div></div><label class="toggle"><input type="checkbox"/><span class="toggle-slider"></span></label></div>
            <div class="sett-row"><span class="sett-icon">🌙</span><div class="sett-info"><div class="sett-label">Modo mantenimiento</div><div class="sett-sub">Deshabilita acceso de usuarios</div></div><label class="toggle"><input type="checkbox"/><span class="toggle-slider"></span></label></div>
            <div class="sett-row"><span class="sett-icon">🔐</span><div class="sett-info"><div class="sett-label">Registro abierto</div><div class="sett-sub">Nuevos usuarios pueden registrarse</div></div><label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label></div>
            <div class="sett-row"><span class="sett-icon">📊</span><div class="sett-info"><div class="sett-label">Modo analytics avanzado</div><div class="sett-sub">Recolectar datos de uso</div></div><label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label></div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-head"><div class="panel-title">🔐 Seguridad</div></div>
          <div class="settings-list">
            <div class="sett-row"><span class="sett-icon">🛡️</span><div class="sett-info"><div class="sett-label">Autenticación de 2 factores</div><div class="sett-sub">Requerir para admins</div></div><label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label></div>
            <div class="sett-row"><span class="sett-icon">⏱️</span><div class="sett-info"><div class="sett-label">Sesión automática</div><div class="sett-sub">Cerrar tras 30 min de inactividad</div></div><label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label></div>
            <div class="sett-row"><span class="sett-icon">📝</span><div class="sett-info"><div class="sett-label">Log de accesos</div><div class="sett-sub">Registrar todos los ingresos</div></div><label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label></div>
            <div class="sett-row"><span class="sett-icon">🚫</span><div class="sett-info"><div class="sett-label">Bloqueo por intentos</div><div class="sett-sub">Bloquear tras 5 intentos fallidos</div></div><label class="toggle"><input type="checkbox" checked/><span class="toggle-slider"></span></label></div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</main>

<script>
// ── LIVE ROUTES DATA ──────────────────────────────────────────────────────────
const liveRoutes = [
  {id:'R-101',name:'Soyapango – T. Oriente',user:'Carlos M.',eta:8,status:'ok',color:'#22c55e'},
  {id:'R-44', name:'Centro – Mercado Central',user:'Ana L.',eta:14,status:'warn',color:'#f59e0b'},
  {id:'R-9',  name:'Mejicanos – P. Morazán',user:'Roberto H.',eta:5,status:'ok',color:'#22c55e'},
  {id:'R-52', name:'Apopa – T. Norte',user:'María G.',eta:22,status:'late',color:'#ef4444'},
  {id:'R-77', name:'Santa Ana – San Salvador',user:'Diego P.',eta:31,status:'warn',color:'#f59e0b'},
];

const statusLabel = {ok:'A tiempo',warn:'Retraso leve',late:'Retrasado'};

// Render sidebar live routes
const liveEl = document.getElementById('liveRoutes');
liveRoutes.forEach(r => {
  liveEl.innerHTML += `
    <div class="route-row">
      <div class="rr-num">${r.id}</div>
      <div class="rr-info">
        <div class="rr-name">${r.name}</div>
        <div class="rr-meta">${r.user} · <span style="color:${r.color}">${statusLabel[r.status]}</span></div>
      </div>
      <div class="rr-right">
        <div class="rr-eta" style="color:${r.color}">${r.eta}</div>
        <div class="rr-eta-lbl">min ETA</div>
      </div>
    </div>`;
});

// Render tracking table
const tt = document.getElementById('trackingTable');
liveRoutes.forEach(r => {
  tt.innerHTML += `
    <tr>
      <td style="font-family:'Rajdhani',sans-serif;font-size:16px;color:var(--accent2)">${r.id}</td>
      <td>${r.user}</td>
      <td><span class="pill pill-${r.status==='ok'?'ok':r.status==='warn'?'warn':'off'}" style="${r.status==='late'?'color:var(--late);background:#ef444412;border-color:var(--late)':''}">${statusLabel[r.status]}</span></td>
      <td><span style="font-family:'Rajdhani',sans-serif;font-size:16px;color:${r.color}">${r.eta} min</span></td>
      <td style="color:var(--muted);font-size:12px">T. Principal</td>
      <td style="color:var(--muted);font-size:12px">hace ${Math.floor(Math.random()*30)+2} min</td>
    </tr>`;
});

// ── NAVIGATION ────────────────────────────────────────────────────────────────
const titles = {dashboard:'Dashboard',usuarios:'Gestión de Usuarios',trackings:'Trackings Activos',reportes:'Reportes',rutas:'Gestión de Rutas',config:'Configuración'};

function setView(id, el) {
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.getElementById('view-'+id).classList.add('active');
  document.querySelectorAll('.sb-item').forEach(i => i.classList.remove('active'));
  if(el) el.classList.add('active');
  document.getElementById('topTitle').textContent = titles[id] || id;
}

// ── REFRESH BUTTON ────────────────────────────────────────────────────────────
document.querySelector('.refresh-btn').addEventListener('click', function(){
  this.textContent = '↻ Actualizando…';
  setTimeout(() => this.textContent = '↻ Actualizar', 1200);
});
</script>
</body>
</html>