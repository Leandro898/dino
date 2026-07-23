<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#ffffff">
    <title>Bari Rider</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('delivery-manifest.json') }}?v=6">
    <link rel="icon" href="https://ui-avatars.com/api/?name=B&size=192&background=ffffff&color=7e22ce&bold=true">
    <link rel="apple-touch-icon" href="https://ui-avatars.com/api/?name=B&size=192&background=ffffff&color=7e22ce&bold=true">
    <!-- Pusher & Echo CDNs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #ff3366; /* Un color vibrante estilo PedidosYa/Rappi */
            --primary-hover: #e62e5c;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-sheet: #ffffff;
            --bg-body: #f3f4f6;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { 
            box-sizing: border-box; 
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        /* Custom Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 24px;
            width: 90%;
            max-width: 320px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: scale(0.95);
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .modal-overlay.show .modal-content {
            transform: scale(1);
        }
        .modal-content h3 {
            margin: 0 0 10px 0;
            font-size: 1.15rem;
            color: var(--text-main);
        }
        .modal-content p {
            margin: 0 0 24px 0;
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.4;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .modal-actions button {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        .btn-cancel {
            background: #f3f4f6;
            color: var(--text-main);
        }
        .btn-confirm {
            background: var(--primary);
            color: white;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            background-color: var(--bg-body);
            overflow: hidden; /* Prevent scrolling, act like an app */
        }

        /* Map Container */
        #map {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Top Bar */
        .top-bar {
            position: absolute;
            top: env(safe-area-inset-top, 20px);
            left: 16px;
            right: 16px;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
        }

        .icon-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: white;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-main);
            transition: transform 0.2s;
        }

        .icon-btn:active {
            transform: scale(0.95);
        }

        .status-badge {
            background: white;
            padding: 10px 20px;
            border-radius: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
            flex-direction: column;
            line-height: 1.2;
        }

        .status-badge span {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--text-muted);
            transition: background 0.3s;
        }

        .dot.active { background: var(--success); }
        .dot.warning { background: var(--warning); }
        .dot.danger { background: var(--danger); }

        .bottom-sheet {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-sheet);
            border-radius: 28px 28px 0 0;
            padding: 24px;
            z-index: 10;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            touch-action: none;
        }

        .sheet-handle {
            position: relative;
            width: 45px;
            height: 5px;
            background: #cbd5e1;
            border-radius: 3px;
            margin: -12px auto 8px auto;
            cursor: ns-resize;
            touch-action: none;
        }

        .sheet-handle::after {
            content: '';
            position: absolute;
            top: -20px;
            bottom: -20px;
            left: -50px;
            right: -50px;
            cursor: ns-resize;
        }

        .tooltip-vendor {
            background-color: #111827 !important;
            color: #ffffff !important;
            border: 1px solid #111827 !important;
            border-radius: 6px !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            padding: 3px 6px !important;
            box-shadow: 0 3px 6px rgba(0,0,0,0.16) !important;
        }
        .tooltip-vendor .leaflet-tooltip-tip {
            border-top-color: #111827 !important;
        }

        .tooltip-customer {
            background-color: #ff3366 !important;
            color: #ffffff !important;
            border: 1px solid #ff3366 !important;
            border-radius: 6px !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            padding: 3px 6px !important;
            box-shadow: 0 3px 6px rgba(0,0,0,0.16) !important;
        }
        .tooltip-customer .leaflet-tooltip-tip {
            border-top-color: #ff3366 !important;
        }

        .action-row {
            display: flex;
            gap: 12px;
        }

        .btn {
            flex: 1;
            padding: 16px;
            border-radius: 16px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 51, 102, 0.3);
        }

        .btn-primary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 6px rgba(255, 51, 102, 0.2);
        }

        .btn-secondary {
            background: #fce7f3; /* Light pink/primary tint */
            color: var(--primary);
        }

        .btn-danger {
            background: #fee2e2;
            color: var(--danger);
        }

        .info-card {
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }

        .info-card h3 {
            margin: 0 0 8px 0;
            font-size: 1.1rem;
            color: var(--text-main);
        }

        .info-card p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Install Banner */
        .install-banner {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 12px 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            display: none; /* Hidden by default */
        }
        
        .install-banner.visible {
            display: flex;
        }

        .install-banner p {
            margin: 0;
            font-size: 0.85rem;
            color: #b45309;
            font-weight: 500;
        }

        .btn-install {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* Drawer / Sidebar */
        .drawer-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 40;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .drawer-backdrop.active {
            opacity: 1;
            pointer-events: auto;
        }

        .drawer {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 80%;
            max-width: 320px;
            background: white;
            z-index: 50;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0,0,0,0.1);
        }
        .drawer.active {
            transform: translateX(0);
        }

        .drawer-header {
            background: linear-gradient(135deg, #e11d48, #be123c); /* Pinkish/red like PedidosYa */
            padding: 40px 20px 20px 20px;
            color: white;
            border-radius: 0 0 24px 24px;
        }
        .drawer-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .drawer-header p {
            margin: 4px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .drawer-menu {
            padding: 20px 10px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .drawer-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 12px;
            transition: background 0.2s;
        }
        .drawer-item:active {
            background: #f3f4f6;
        }
        .drawer-item svg {
            width: 24px; height: 24px;
            color: var(--text-muted);
        }
        .drawer-item.danger {
            color: var(--danger);
            margin-top: auto;
            border-top: 1px solid #f3f4f6;
            border-radius: 0;
            padding-top: 24px;
        }
        .drawer-item.danger svg {
            color: var(--danger);
        }

        /* Modal Settings */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 60;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content {
            background: white;
            width: 100%;
            max-width: 400px;
            border-radius: 24px;
            padding: 24px;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-overlay.active .modal-content {
            transform: scale(1);
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--text-main);
        }
        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
        }
        
        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 28px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .toggle-slider {
            background-color: var(--success);
        }
        input:checked + .toggle-slider:before {
            transform: translateX(22px);
        }

        /* PWA Full Screen Views */
        .pwa-view {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: var(--bg-body);
            z-index: 100;
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }
        .pwa-view.active {
            transform: translateX(0);
        }
        
        .pwa-header {
            background: white;
            padding: env(safe-area-inset-top, 16px) 16px 16px 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 2;
        }
        .pwa-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--text-main);
            flex: 1;
        }
        
        .pwa-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f9fafb;
        }
        
        /* Profile Info Box */
        .profile-box {
            background: white;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .profile-avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 16px auto;
        }
        .profile-box h3 { margin: 0 0 8px 0; font-size: 1.5rem; color: var(--text-main); }
        .profile-box p { margin: 0; color: var(--text-muted); font-size: 0.95rem; }

        /* Order Alert Modal */
        .order-alert {
            position: fixed;
            top: 20px;
            left: 16px;
            right: 16px;
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 100;
            transform: translateY(-150%);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            flex-direction: column;
            gap: 12px;
            border-left: 6px solid var(--primary);
        }

        .order-alert.show {
            transform: translateY(0);
        }

        .order-alert-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }

        .order-alert-desc {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.95rem;
        }

        /* Form hidden */
        #logout-form { display: none; }
    </style>
</head>
<body>
    <!-- Custom Modal Overlay -->
    <div id="customModal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="modalTitle">Atención</h3>
            <p id="modalMessage">¿Estás seguro?</p>
            <div class="modal-actions">
                <button id="modalBtnCancel" class="btn-cancel">Cancelar</button>
                <button id="modalBtnConfirm" class="btn-confirm">Aceptar</button>
            </div>
        </div>
    </div>

    <!-- Map Background -->
    <div id="map"></div>

    <!-- Drawer / Sidebar -->
    <div class="drawer-backdrop" id="drawerBackdrop" onclick="history.back()"></div>
    <div class="drawer" id="sideDrawer">
        <div class="drawer-header">
            <h2>Hola, {{ auth()->user()->name ?? 'Rider' }} 👋</h2>
            <p>Bari Rider</p>
        </div>
        <div class="drawer-menu">
            <a href="javascript:void(0)" class="drawer-item" onclick="openView('profileView')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Perfil
            </a>
            <a href="javascript:void(0)" class="drawer-item" onclick="openView('supportView')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                Soporte
                <span id="supportDrawerBadge" style="display: none; margin-left: auto; width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></span>
            </a>
            <a href="javascript:void(0)" class="drawer-item" onclick="openView('settingsView')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Configuración
            </a>
            <a href="javascript:void(0)" class="drawer-item danger" onclick="document.getElementById('logout-form').submit();">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Cerrar sesión
            </a>
        </div>
    </div>

    <!-- Full Screen PWA View: Profile -->
    <div class="pwa-view" id="profileView">
        <div class="pwa-header">
            <button class="icon-btn" style="box-shadow:none; background:#f3f4f6;" onclick="closeView('profileView')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </button>
            <h2>Mi Perfil</h2>
        </div>
        <div class="pwa-content">
            <div class="profile-box">
                <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 1)) }}</div>
                <h3>{{ auth()->user()->name ?? 'Rider' }}</h3>
                <p>{{ auth()->user()->email ?? 'rider@bari.com' }}</p>
            </div>
            
            <div class="info-card" style="text-align: left; padding: 20px;">
                <h4 style="margin: 0 0 12px 0; font-size: 1.1rem; color: var(--text-main);">Información de Cuenta</h4>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">Rol</span>
                        <div style="font-weight: 500;">Repartidor Autorizado</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">Registrado el</span>
                        <div style="font-weight: 500;">{{ auth()->user()->created_at ? auth()->user()->created_at->format('d/m/Y') : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Screen PWA View: Settings -->
    <div class="pwa-view" id="settingsView">
        <div class="pwa-header">
            <button class="icon-btn" style="box-shadow:none; background:#f3f4f6;" onclick="closeView('settingsView')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </button>
            <h2>Configuración</h2>
        </div>
        <div class="pwa-content">
            <div class="info-card" style="text-align: left; padding: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h4 style="margin: 0 0 4px 0; font-size: 1.05rem;">Notificaciones Push</h4>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Recibir alertas de nuevos pedidos</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="pushToggle" onchange="togglePush(this)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <button class="btn btn-primary" style="width: 100%; box-shadow: none;" onclick="testNotification()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                Comprobar notificaciones
            </button>
        </div>
    </div>
    
    <!-- Full Screen PWA View: Support Chat -->
    <div class="pwa-view" id="supportView">
        <div class="pwa-header">
            <button class="icon-btn" style="box-shadow:none; background:#f3f4f6;" onclick="closeView('supportView')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </button>
            <h2>Soporte</h2>
        </div>
        <div class="pwa-content" style="display: flex; flex-direction: column; height: calc(100% - 76px); padding: 12px; background: #f9fafb;">
            <!-- Message Area -->
            <div id="supportChatMessages" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding-bottom: 12px; margin-bottom: 12px;">
                <!-- Messages will be injected here dynamically -->
            </div>
            
            <!-- Send Wrapper -->
            <div style="display: flex; gap: 8px; background: white; padding: 8px; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: env(safe-area-inset-bottom, 12px);">
                <input type="text" id="supportChatInput" placeholder="Escribe un mensaje..." style="flex: 1; border: none; outline: none; padding: 10px; font-size: 0.95rem; background: transparent;" onkeydown="if(event.key === 'Enter') sendSupportChat()">
                <button onclick="sendSupportChat()" style="background: var(--primary); border: none; border-radius: 12px; padding: 10px 16px; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Top Floating Bar -->
    <div class="top-bar">
        <button class="icon-btn" onclick="toggleDrawer()" title="Menú">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <div class="status-badge" id="topStatusBadge">
            <span>Estado</span>
            <div class="status-indicator">
                <div class="dot" id="topStatusDot"></div>
                <div id="topStatusText">Desconectado</div>
            </div>
        </div>

        <button class="icon-btn" onclick="openView('supportView')" title="Soporte" style="position: relative;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <span id="supportBadge" style="display: none; position: absolute; top: 2px; right: 2px; width: 10px; height: 10px; border-radius: 50%; background: var(--primary);"></span>
        </button>
    </div>

    <!-- Order Alert Popup -->
    <div class="order-alert" id="orderAlert" style="display: flex; flex-direction: column; gap: 10px;">
        <h3 class="order-alert-title" style="margin:0; font-size: 1.1rem; color: var(--primary);">¡Nuevo Pedido!</h3>
        <p class="order-alert-desc" id="orderAlertDesc" style="margin:0; font-size: 0.9rem; color: var(--text-main);">Pedido #123 de Juan Perez</p>
        <div style="display: flex; gap: 8px; width: 100%;">
            <button id="alertAcceptBtn" class="btn" style="flex: 1; padding: 10px; background: #22c55e; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; height: 36px; box-shadow: none;">
                Aceptar
            </button>
            <button id="alertRejectBtn" class="btn" style="flex: 1; padding: 10px; background: #fee2e2; color: #ef4444; border: none; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; height: 36px; box-shadow: none;">
                Rechazar
            </button>
        </div>
    </div>

    <!-- Bottom Action Sheet -->
    <div class="bottom-sheet">
        <div class="sheet-handle"></div>
        
        <div class="install-banner" id="installBanner">
            <p>Instala la app para mejor experiencia</p>
            <button class="btn-install" id="installAppBtn">Instalar</button>
        </div>

        <div class="info-card" id="infoCard">
            <h3 id="infoTitle">¡Hola, {{ auth()->user()->name ?? 'Rider' }}!</h3>
            <p id="infoDesc">Presiona Comenzar para recibir notificaciones de nuevos pedidos en tu zona.</p>
        </div>

        <div class="action-row" id="disconnectedActions">
            <button class="btn btn-primary" id="startBtn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                Comenzar
            </button>
        </div>

        <div class="action-row" id="connectedActions" style="display: none;">
            <button class="btn btn-danger" id="stopBtn">
                Detener
            </button>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST">
        @csrf
    </form>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        // --- Sidebar & Views ---
        function toggleDrawer() {
            const drawer = document.getElementById('sideDrawer');
            if (drawer.classList.contains('active')) {
                history.back(); // Triggers popstate to close
            } else {
                drawer.classList.add('active');
                document.getElementById('drawerBackdrop').classList.add('active');
                history.pushState({ modal: 'drawer' }, '', '#menu');
            }
        }

        function openView(id) {
            const drawer = document.getElementById('sideDrawer');
            if (drawer.classList.contains('active')) {
                drawer.classList.remove('active');
                document.getElementById('drawerBackdrop').classList.remove('active');
                history.replaceState({ modal: id }, '', '#' + id);
            } else {
                history.pushState({ modal: id }, '', '#' + id);
            }
            document.getElementById(id).classList.add('active');

            if (id === 'supportView') {
                openSupportChat();
            }
        }

        function closeView(id) {
            history.back(); // Triggers popstate to close
        }

        window.addEventListener('popstate', function(event) {
            // Close all full screen views
            document.querySelectorAll('.pwa-view').forEach(function(el) {
                el.classList.remove('active');
            });
            isSupportViewActive = false;
            // Close drawer
            document.getElementById('sideDrawer').classList.remove('active');
            document.getElementById('drawerBackdrop').classList.remove('active');

            // Restore state if we are going back to a nested view (e.g. from view -> menu)
            if (event.state && event.state.modal) {
                if (event.state.modal === 'drawer') {
                    document.getElementById('sideDrawer').classList.add('active');
                    document.getElementById('drawerBackdrop').classList.add('active');
                } else {
                    let view = document.getElementById(event.state.modal);
                    if (view) view.classList.add('active');
                }
            }
        });
        
        function togglePush(checkbox) {
            if(checkbox.checked) {
                if ('Notification' in window && Notification.permission !== 'granted') {
                    Notification.requestPermission();
                }
            }
        }

        function testNotification() {
            if (!('Notification' in window)) {
                alert("Tu navegador no soporta notificaciones push.");
                return;
            }

            if (Notification.permission === "granted") {
                if (navigator.serviceWorker) {
                    navigator.serviceWorker.ready.then(function(registration) {
                        registration.showNotification("¡Todo bien! 🎉", {
                            body: "Tus notificaciones están funcionando y deberías poder recibirlas.",
                            icon: "https://ui-avatars.com/api/?name=B&size=192&background=ffffff&color=7e22ce&bold=true",
                            vibrate: [200, 100, 200]
                        });
                    });
                } else {
                    new Notification("¡Todo bien! 🎉", {
                        body: "Tus notificaciones están funcionando y deberías poder recibirlas."
                    });
                }
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(function (permission) {
                    if (permission === "granted") {
                        testNotification();
                    } else {
                        alert("Necesitas aceptar los permisos para recibir alertas.");
                    }
                });
            } else {
                alert("Las notificaciones están bloqueadas. Debes activarlas desde la configuración de tu navegador.");
            }
        }

        // --- API & State ---
        const latestUrl = @json(route('delivery.orders.latest'));
        let isConnected = false;
        let isSupportViewActive = false;
        let baselineInitialized = false;
        let deferredPrompt = null;

        // --- WebSocket configuration (Laravel Echo + Reverb) ---
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') }}',
            wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
            wsPort: {{ config('broadcasting.connections.reverb.options.port') }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port') }},
            forceTLS: {{ config('broadcasting.connections.reverb.options.scheme') === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        });

        // Suscribirse al canal de soporte de manera global al cargar la página
        (function() {
            const userId = {{ auth()->id() }};
            window.Echo.channel(`support.${userId}`)
                .listen('.support-message.sent', (data) => {
                    console.log('Support message received:', data);
                    const currentUserId = {{ auth()->id() }};
                    if (data.senderId !== currentUserId) {
                        if (isSupportViewActive && document.getElementById('supportView').classList.contains('active')) {
                            fetchSupportMessages();
                            playAlertSound();
                        } else {
                            document.getElementById('supportBadge').style.display = 'block';
                            document.getElementById('supportDrawerBadge').style.display = 'block';
                            playAlertSound();
                        }
                    }
                });
        })();

        // --- DOM Elements ---
        const mapDiv = document.getElementById('map');
        const topStatusDot = document.getElementById('topStatusDot');
        const topStatusText = document.getElementById('topStatusText');
        const infoTitle = document.getElementById('infoTitle');
        const infoDesc = document.getElementById('infoDesc');
        const disconnectedActions = document.getElementById('disconnectedActions');
        const connectedActions = document.getElementById('connectedActions');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const installBanner = document.getElementById('installBanner');
        const installAppBtn = document.getElementById('installAppBtn');
        const orderAlert = document.getElementById('orderAlert');
        const orderAlertDesc = document.getElementById('orderAlertDesc');

        // --- Initialize Map ---
        // Coordenadas por defecto (Ej: Buenos Aires o la ciudad del negocio)
        // Puedes cambiar [-34.6037, -58.3816] por tus coordenadas reales.
        const map = L.map('map', { zoomControl: false }).setView([-41.1335, -71.3103], 13); // Bariloche coords approx
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        map.on('click', () => {
            const sheet = document.querySelector('.bottom-sheet');
            if (sheet && !sheet.classList.contains('collapsed')) {
                sheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                const maxTranslateY = sheet.offsetHeight - 60;
                sheet.classList.add('collapsed');
                sheet.style.transform = `translateY(${maxTranslateY}px)`;
            }
        });

        // Marker for rider location and custom icons
        let riderMarker = L.marker([-41.1335, -71.3103]).addTo(map);
        let vendorMarker = null;
        let customerMarker = null;
        let routeLine = null;

        const vendorIcon = L.divIcon({
            html: `<div style="position: relative; width: 36px; height: 46px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="background-color: #111827; color: white; padding: 6px; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 2.5px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.35); z-index: 2;">🏪</div>
                <div style="width: 3px; height: 10px; background-color: #111827; margin-top: -3px; z-index: 1; box-shadow: 1px 1px 3px rgba(0,0,0,0.25);"></div>
            </div>`,
            className: 'custom-div-icon',
            iconSize: [36, 46],
            iconAnchor: [18, 46]
        });

        const customerIcon = L.divIcon({
            html: `<div style="position: relative; width: 36px; height: 46px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="background-color: #ff3366; color: white; padding: 6px; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 2.5px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.35); z-index: 2;">🏠</div>
                <div style="width: 3px; height: 10px; background-color: #ff3366; margin-top: -3px; z-index: 1; box-shadow: 1px 1px 3px rgba(0,0,0,0.25);"></div>
            </div>`,
            className: 'custom-div-icon',
            iconSize: [36, 46],
            iconAnchor: [18, 46]
        });

        // Try to get actual location
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(position => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 15);
                riderMarker.setLatLng([lat, lng]);
            });
        }

        // --- PWA Installation ---
        function updateInstallUI() {
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            if (isStandalone) {
                installBanner.classList.remove('visible');
            } else if (deferredPrompt) {
                installBanner.classList.add('visible');
            }
        }

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredPrompt = event;
            updateInstallUI();
        });

        installAppBtn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const result = await deferredPrompt.userChoice;
            if (result.outcome === 'accepted') {
                installBanner.classList.remove('visible');
            }
            deferredPrompt = null;
        });

        // --- Audio Alert ---
        function playAlertSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = ctx.createOscillator();
                const gainNode = ctx.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, ctx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.2);
                gainNode.gain.setValueAtTime(0.2, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                oscillator.connect(gainNode);
                gainNode.connect(ctx.destination);
                oscillator.start();
                oscillator.stop(ctx.currentTime + 0.2);
            } catch (error) {
                console.warn('Audio play failed', error);
            }
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            if (!lat1 || !lon1 || !lat2 || !lon2) return null;
            const R = 6371; // Radius of the earth in km
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a =
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const d = R * c; // Distance in km
            return d;
        }

        function deg2rad(deg) {
            return deg * (Math.PI/180);
        }

        // --- App Logic ---
        function getSavedOrderId() {
            const value = localStorage.getItem('delivery_last_order_id');
            return value ? parseInt(value, 10) : null;
        }

        function saveOrderId(orderId) {
            localStorage.setItem('delivery_last_order_id', String(orderId));
        }

        async function showOrderNotification(order) {
            playAlertSound();
            
            if (navigator.vibrate) navigator.vibrate([200, 100, 200]);

            if (Notification.permission === 'granted') {
                new Notification(`¡Nuevo Pedido #${order.id}!`, {
                    body: `Retiro: ${order.vendor_name || 'Comercio'}\nEntrega: ${order.customer_name}\nCobrar: $${Number(order.total).toFixed(0)}`,
                    icon: '{{ asset('images/og-image.png') }}'
                });
            }
        }

        async function fetchOrders() {
            if (!isConnected) return;
            
            try {
                const response = await fetch(latestUrl, { headers: { 'Accept': 'application/json' }});
                if (!response.ok) throw new Error('HTTP Error');
                const data = await response.json();

                if (data.has_order) {
                    const sheetEl = document.querySelector('.bottom-sheet');
                    if (sheetEl && sheetEl.classList.contains('collapsed')) {
                        sheetEl.classList.remove('collapsed');
                        sheetEl.style.transform = 'translateY(0)';
                    }
                }

                if (!data.has_order) {
                    infoTitle.textContent = "Sin pedidos asignados";
                    infoDesc.innerHTML = "Esperando que se te asigne un pedido. Mantente en línea.";
                    localStorage.removeItem('delivery_last_order_id');
                    
                    if (vendorMarker) { map.removeLayer(vendorMarker); vendorMarker = null; }
                    if (customerMarker) { map.removeLayer(customerMarker); customerMarker = null; }
                    if (routeLine) { map.removeLayer(routeLine); routeLine = null; }

                    disconnectedActions.style.display = 'none';
                    connectedActions.style.display = 'flex';
                    return;
                }

                // Remove previous markers/routes
                if (vendorMarker) { map.removeLayer(vendorMarker); vendorMarker = null; }
                if (customerMarker) { map.removeLayer(customerMarker); customerMarker = null; }
                if (routeLine) { map.removeLayer(routeLine); routeLine = null; }

                const riderLatLng = riderMarker.getLatLng();
                const points = [];
                points.push([riderLatLng.lat, riderLatLng.lng]);

                // 1. Comercio
                if (data.vendor_latitude && data.vendor_longitude) {
                    vendorMarker = L.marker([data.vendor_latitude, data.vendor_longitude], { icon: vendorIcon })
                        .addTo(map)
                        .bindPopup(`<strong>Comercio:</strong> ${data.vendor_name}<br>${data.vendor_address}`)
                        .bindTooltip(`🏪 Retiro: ${data.vendor_name}`, {
                            permanent: true,
                            direction: 'top',
                            offset: [0, -42],
                            className: 'tooltip-vendor'
                        });
                    points.push([data.vendor_latitude, data.vendor_longitude]);
                }

                // 2. Cliente
                if (data.latitude && data.longitude) {
                    customerMarker = L.marker([data.latitude, data.longitude], { icon: customerIcon })
                        .addTo(map)
                        .bindPopup(`<strong>Cliente:</strong> ${data.customer_name}<br>${data.address}`)
                        .bindTooltip(`🏠 Entrega: ${data.customer_name}`, {
                            permanent: true,
                            direction: 'top',
                            offset: [0, -42],
                            className: 'tooltip-customer'
                        });
                    points.push([data.latitude, data.longitude]);
                }

                // 3. Zoom bounds
                if (points.length >= 2) {
                    const bounds = L.latLngBounds(points);
                    map.fitBounds(bounds, { padding: [50, 50] });
                }

                saveOrderId(data.id);

                if (!data.is_accepted) {
                    const riderLatLng = riderMarker.getLatLng();
                    const distToVendor = calculateDistance(riderLatLng.lat, riderLatLng.lng, data.vendor_latitude, data.vendor_longitude);
                    const distToCust = calculateDistance(data.vendor_latitude, data.vendor_longitude, data.latitude, data.longitude);

                    const distToVendorStr = distToVendor ? `${distToVendor.toFixed(2)} km` : '— km';
                    const distToCustStr = distToCust ? `${distToCust.toFixed(2)} km` : '— km';

                    // Payment method styling
                    let paymentMethodLabel = 'Pago Online';
                    if (data.payment_method === 'transferencia') {
                        paymentMethodLabel = 'Transferencia necesaria';
                    } else if (data.payment_method === 'mercadopago') {
                        paymentMethodLabel = 'Mercado Pago';
                    } else {
                        paymentMethodLabel = 'Efectivo necesario';
                    }

                    infoTitle.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <span class="badge" style="background: #f3f4f6; color: #4b5563; padding: 4px 10px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                ${paymentMethodLabel}
                            </span>
                            <button onclick="rejectCurrentOrder(${data.id})" style="background: #f3f4f6; border: none; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; color: #4b5563; cursor: pointer; transition: all 0.2s;">
                                ✕
                            </button>
                        </div>
                    `;

                    infoDesc.innerHTML = `
                        <!-- Timeline de Entrega estilo PedidosYa -->
                        <div style="position: relative; margin-top: 12px; padding-left: 28px;">
                            <!-- Línea vertical conectora -->
                            <div style="position: absolute; left: 12px; top: 20px; bottom: 20px; width: 2px; border-left: 2px dashed #d1d5db;"></div>
                            
                            <!-- Comercio (Retiro) -->
                            <div style="position: relative; margin-bottom: 16px;">
                                <div style="position: absolute; left: -28px; top: 1px; width: 24px; height: 24px; background: #111827; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; color: white;">🏪</div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <h4 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: #1f2937;">${data.vendor_name}</h4>
                                        <p style="margin: 1px 0 0 0; font-size: 0.78rem; color: #6b7280; line-height:1.2;">${data.vendor_address}</p>
                                    </div>
                                    <span style="font-size: 0.78rem; font-weight: 600; color: #4b5563; shrink-0; padding-left: 8px;">${distToVendorStr}</span>
                                </div>
                                <div style="margin-top: 4px;">
                                    <span style="display: inline-block; background: #f3f4f6; border: 1px solid #e5e7eb; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; color: #475569;">
                                        Retirar pedido #${data.id}
                                    </span>
                                </div>
                            </div>

                            <!-- Cliente (Entrega) -->
                            <div style="position: relative;">
                                <div style="position: absolute; left: -28px; top: 1px; width: 24px; height: 24px; background: #ff3366; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; color: white;">👤</div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <h4 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: #1f2937;">Entrega</h4>
                                        <p style="margin: 1px 0 0 0; font-size: 0.78rem; color: #6b7280; line-height:1.2;">${data.address || 'Sin dirección'}</p>
                                        <p style="margin: 1px 0 0 0; font-size: 0.72rem; color: #9ca3af;">Destinatario: ${data.customer_name}</p>
                                    </div>
                                    <span style="font-size: 0.78rem; font-weight: 600; color: #4b5563; shrink-0; padding-left: 8px;">${distToCustStr}</span>
                                </div>
                                <div style="margin-top: 4px;">
                                    <span style="display: inline-block; background: #fee2e2; border: 1px solid #fecaca; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; color: #ef4444;">
                                        Cobrar al usuario $${Number(data.total).toFixed(0)}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen y Botón de Aceptar -->
                        <div style="margin-top: 14px; border-top: 1px solid #e5e7eb; padding-top: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span style="font-size: 0.85rem; font-weight: 700; color: #475569;">Pagar por el pedido</span>
                                <span style="font-size: 1.05rem; font-weight: 800; color: #111827;">$${Number(data.total).toFixed(0)}</span>
                            </div>
                            <button onclick="acceptCurrentOrder(${data.id})" class="btn" style="width: 100%; height: 46px; background: #111827; color: white; border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center; gap: 6px;">
                                Aceptar pedido
                            </button>
                        </div>
                    `;
                    disconnectedActions.style.display = 'none';
                    connectedActions.style.display = 'none';
                    return;
                }

                infoTitle.textContent = `Pedido asignado #${data.id}`;
                infoDesc.innerHTML = `
                    <strong>Cliente:</strong> ${data.customer_name}<br>
                    <strong>Dirección:</strong> ${data.address || 'Sin dirección'}<br>
                    <strong>Total:</strong> $${Number(data.total).toFixed(0)}<br>
                    <strong>Estado:</strong> <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">${data.status.toUpperCase()}</span>
                    
                    ${(data.latitude && data.longitude) ? `
                        <div style="margin-top: 14px; display: flex; gap: 8px;">
                            <a href="https://www.google.com/maps/dir/?api=1&destination=${data.latitude},${data.longitude}&travelmode=driving" 
                               target="_blank" 
                               style="flex: 1; text-align: center; background: #22c55e; color: white; padding: 8px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                📍 Navegar GPS
                            </a>
                        </div>
                    ` : ''}
                    
                    ${['assigned', 'processing'].includes(data.status) ? `
                        <div style="margin-top: 14px;">
                            <button onclick="markPickedUp(${data.id})" class="btn" style="width: 100%; height: 46px; background: #f59e0b; color: white; border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center; gap: 6px;">
                                🛍️ Confirmar Retiro del Local
                            </button>
                        </div>
                    ` : ''}

                    ${data.status === 'shipped' ? `
                        <div style="margin-top: 14px;">
                            <button onclick="markDelivered(${data.id})" class="btn" style="width: 100%; height: 46px; background: #3b82f6; color: white; border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center; gap: 6px;">
                                ✅ Marcar como Entregado
                            </button>
                        </div>
                    ` : ''}
                `;

                disconnectedActions.style.display = 'none';
                connectedActions.style.display = 'flex';
            } catch (error) {
                console.error('Error fetching orders:', error);
                infoDesc.textContent = "Problemas de conexión al servidor.";
            }
        }

        function showCustomModal(title, message, showCancel = true) {
            return new Promise((resolve) => {
                const modal = document.getElementById('customModal');
                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalMessage').textContent = message;
                
                const btnCancel = document.getElementById('modalBtnCancel');
                const btnConfirm = document.getElementById('modalBtnConfirm');
                
                btnCancel.style.display = showCancel ? 'block' : 'none';
                
                const cleanup = () => {
                    modal.classList.remove('show');
                    btnCancel.onclick = null;
                    btnConfirm.onclick = null;
                };

                btnCancel.onclick = () => { cleanup(); resolve(false); };
                btnConfirm.onclick = () => { cleanup(); resolve(true); };
                
                // Trigger reflow
                void modal.offsetWidth;
                modal.classList.add('show');
            });
        }

        window.acceptCurrentOrder = async function(orderId) {
            try {
                const response = await fetch(`/repartidor/pedidos/${orderId}/aceptar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Error al aceptar');
                const data = await response.json();
                if (data.success) {
                    fetchOrders();
                } else {
                    await showCustomModal('Atención', data.error || 'No se pudo aceptar el pedido.', false);
                }
            } catch (error) {
                console.error(error);
                await showCustomModal('Error', 'Ocurrió un error al procesar la aceptación.', false);
            }
        };

        window.rejectCurrentOrder = async function(orderId) {
            const confirmed = await showCustomModal('Rechazar Pedido', '¿Estás seguro de que deseas rechazar este pedido?');
            if (!confirmed) return;
            try {
                const response = await fetch(`/repartidor/pedidos/${orderId}/rechazar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Error al rechazar');
                const data = await response.json();
                if (data.success) {
                    fetchOrders();
                } else {
                    await showCustomModal('Atención', data.error || 'No se pudo rechazar el pedido.', false);
                }
            } catch (error) {
                console.error(error);
                await showCustomModal('Error', 'Ocurrió un error al procesar el rechazo.', false);
            }
        };

        window.markPickedUp = async function(orderId) {
            const confirmed = await showCustomModal('Retiro del Local', '¿Confirmas que has retirado este pedido del local?');
            if (!confirmed) return;
            try {
                const response = await fetch(`/repartidor/pedidos/${orderId}/retirado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Error al confirmar retiro');
                const data = await response.json();
                if (data.success) {
                    fetchOrders();
                } else {
                    await showCustomModal('Atención', data.error || 'No se pudo confirmar el retiro.', false);
                }
            } catch (error) {
                console.error(error);
                await showCustomModal('Error', 'Ocurrió un error al confirmar el retiro.', false);
            }
        };

        window.markDelivered = async function(orderId) {
            const confirmed = await showCustomModal('Entregar Pedido', '¿Confirmas que has entregado este pedido al cliente?');
            if (!confirmed) return;
            try {
                const response = await fetch(`/repartidor/pedidos/${orderId}/entregado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Error al marcar entregado');
                const data = await response.json();
                if (data.success) {
                    fetchOrders();
                } else {
                    await showCustomModal('Atención', data.error || 'No se pudo marcar como entregado.', false);
                }
            } catch (error) {
                console.error(error);
                await showCustomModal('Error', 'Ocurrió un error al marcar como entregado.', false);
            }
        };

        async function toggleConnection(connect) {
            if (connect) {
                // Request permissions
                if ('Notification' in window) {
                    await Notification.requestPermission();
                }

                isConnected = true;
                localStorage.setItem('delivery_is_connected', 'true');
                topStatusDot.className = 'dot active';
                topStatusText.textContent = 'Conectado';
                infoTitle.textContent = 'Escuchando pedidos';
                infoDesc.textContent = 'Conectando con el servidor...';
                
                disconnectedActions.style.display = 'none';
                connectedActions.style.display = 'flex';

                baselineInitialized = false; // reset baseline
                fetchOrders();

                // Subscribe to rider private channel
                const userId = {{ auth()->id() }};
                window.Echo.private(`App.Models.User.${userId}`)
                    .listen('.order-updated-for-rider', (data) => {
                        console.log('Order updated/assigned received:', data);
                        
                        if (!isConnected) {
                            isConnected = true;
                            topStatusDot.className = 'dot active';
                            topStatusText.textContent = 'Conectado';
                            disconnectedActions.style.display = 'none';
                            connectedActions.style.display = 'flex';
                        }

                        if (data.is_new_assignment) {
                            showOrderNotification({
                                id: data.order_id,
                                customer_name: data.customer_name,
                                total: data.total,
                                vendor_name: data.vendor_name,
                                vendor_address: data.vendor_address,
                                customer_address: data.customer_address
                            });
                        }
                        fetchOrders();
                    })
                    .listen('.order-unassigned-from-rider', (data) => {
                        console.log('Order unassigned received:', data);
                        fetchOrders();
                    });

            } else {
                isConnected = false;
                localStorage.setItem('delivery_is_connected', 'false');
                
                // Unsubscribe from channel
                const userId = {{ auth()->id() }};
                window.Echo.leave(`App.Models.User.${userId}`);
                
                topStatusDot.className = 'dot';
                topStatusText.textContent = 'Desconectado';
                infoTitle.textContent = 'Estás desconectado';
                infoDesc.textContent = 'Presiona Comenzar para recibir notificaciones de nuevos pedidos.';
                
                disconnectedActions.style.display = 'flex';
                connectedActions.style.display = 'none';
            }
        }

        // --- Event Listeners ---
        startBtn.addEventListener('click', () => toggleConnection(true));
        stopBtn.addEventListener('click', () => toggleConnection(false));

        // --- GPS Tracking: enviar ubicación del rider cada 10 segundos ---
        let gpsTrackingInterval = null;
        const locationUpdateUrl = @json(route('delivery.location.update'));

        function startGpsTracking() {
            if (gpsTrackingInterval) return; // ya corriendo
            if (!('geolocation' in navigator)) {
                console.warn('Geolocation no disponible');
                return;
            }

            gpsTrackingInterval = setInterval(() => {
                if (!isConnected) return;
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        // Actualizar marcador del rider en el mapa local
                        riderMarker.setLatLng([lat, lng]);

                        // Enviar al servidor
                        fetch(locationUpdateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({ latitude: lat, longitude: lng })
                        }).catch(err => console.warn('Error enviando ubicación:', err));
                    },
                    (error) => {
                        console.warn('Error obteniendo GPS:', error.message);
                    },
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 5000 }
                );
            }, 10000); // cada 10 segundos

            // Enviar inmediatamente la primera vez
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    fetch(locationUpdateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ latitude: position.coords.latitude, longitude: position.coords.longitude })
                    }).catch(err => console.warn('Error enviando ubicación inicial:', err));
                },
                () => {},
                { enableHighAccuracy: true }
            );
        }

        function stopGpsTracking() {
            if (gpsTrackingInterval) {
                clearInterval(gpsTrackingInterval);
                gpsTrackingInterval = null;
            }
        }

        // Iniciar/detener GPS tracking según conexión
        const originalToggle = toggleConnection;
        toggleConnection = async function(connect) {
            await originalToggle(connect);
            if (connect) {
                startGpsTracking();
            } else {
                stopGpsTracking();
            }
        };

        // Drag/Collapse Logic for Bottom Sheet
        (function() {
            const sheet = document.querySelector('.bottom-sheet');
            const handle = document.querySelector('.sheet-handle');
            if (!sheet || !handle) return;

            let isDragging = false;
            let startY = 0;
            let startTranslateY = 0;

            function getTranslateY() {
                const style = window.getComputedStyle(sheet);
                const matrix = new WebKitCSSMatrix(style.transform);
                return matrix.m42 || 0;
            }

            handle.addEventListener('pointerdown', (e) => {
                isDragging = true;
                startY = e.clientY;
                startTranslateY = getTranslateY();
                sheet.style.transition = 'none';
                handle.setPointerCapture(e.pointerId);
            });

            handle.addEventListener('pointermove', (e) => {
                if (!isDragging) return;
                const deltaY = e.clientY - startY;
                let newTranslateY = startTranslateY + deltaY;

                const maxTranslateY = sheet.offsetHeight - 60;
                newTranslateY = Math.max(0, Math.min(newTranslateY, maxTranslateY));

                sheet.style.transform = `translateY(${newTranslateY}px)`;
            });

            handle.addEventListener('pointerup', (e) => {
                if (!isDragging) return;
                isDragging = false;
                handle.releasePointerCapture(e.pointerId);
                
                sheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                
                const finalTranslateY = getTranslateY();
                const maxTranslateY = sheet.offsetHeight - 60;
                
                if (finalTranslateY > maxTranslateY * 0.4) {
                    sheet.classList.add('collapsed');
                    sheet.style.transform = `translateY(${maxTranslateY}px)`;
                } else {
                    sheet.classList.remove('collapsed');
                    sheet.style.transform = 'translateY(0)';
                }
            });

            handle.addEventListener('click', (e) => {
                if (Math.abs(e.clientY - startY) > 5) return;
                
                sheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                const maxTranslateY = sheet.offsetHeight - 60;
                
                if (sheet.classList.contains('collapsed')) {
                    sheet.classList.remove('collapsed');
                    sheet.style.transform = 'translateY(0)';
                } else {
                    sheet.classList.add('collapsed');
                    sheet.style.transform = `translateY(${maxTranslateY}px)`;
                }
            });
        })();

        // --- Support Chat ---
        const supportMessagesUrl = "{{ route('delivery.support.messages') }}";
        const sendSupportMessageUrl = "{{ route('delivery.support.send') }}";

        async function openSupportChat() {
            isSupportViewActive = true;
            document.getElementById('supportBadge').style.display = 'none';
            document.getElementById('supportDrawerBadge').style.display = 'none';
            await fetchSupportMessages();
        }

        async function fetchSupportMessages() {
            try {
                const response = await fetch(supportMessagesUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Network error');
                const messages = await response.json();
                renderSupportMessages(messages);
            } catch (error) {
                console.error('Error fetching support messages:', error);
            }
        }

        function renderSupportMessages(messages) {
            const container = document.getElementById('supportChatMessages');
            container.innerHTML = '';

            if (messages.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-top: 40px; padding: 0 20px;">
                        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px auto; opacity: 0.5;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <p style="margin: 0 0 4px 0; font-weight: 500;">Soporte de Bari Tienda</p>
                        <p style="margin: 0; font-size: 0.8rem;">Escribe tu consulta y un administrador te responderá a la brevedad.</p>
                    </div>
                `;
                return;
            }

            const currentUserId = {{ auth()->id() }};
            messages.forEach(msg => {
                const isMe = msg.sender_id === currentUserId;
                const bubble = document.createElement('div');
                bubble.style.display = 'flex';
                bubble.style.justifyContent = isMe ? 'flex-end' : 'flex-start';
                
                const time = msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                
                bubble.innerHTML = `
                    <div style="max-w: 75%; background: ${isMe ? 'var(--primary)' : 'white'}; color: ${isMe ? 'white' : 'var(--text-main)'}; padding: 10px 14px; border-radius: 16px; border-bottom-${isMe ? 'right' : 'left'}-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border: ${isMe ? 'none' : '1px solid #f0f0f0'};">
                        <p style="margin: 0; font-size: 0.95rem; line-height: 1.4; word-break: break-word;">${escapeHtml(msg.message)}</p>
                        <span style="display: block; font-size: 0.75rem; text-align: right; opacity: 0.7; margin-top: 4px;">${time}</span>
                    </div>
                `;
                container.appendChild(bubble);
            });

            container.scrollTop = container.scrollHeight;
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        async function sendSupportChat() {
            const input = document.getElementById('supportChatInput');
            const message = input.value.trim();
            if (!message) return;

            input.value = '';

            try {
                const response = await fetch(sendSupportMessageUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message })
                });

                if (!response.ok) throw new Error('Send message failed');
                
                // Fetch and re-render
                await fetchSupportMessages();
            } catch (error) {
                console.error('Error sending message:', error);
                alert('No se pudo enviar el mensaje.');
            }
        }

        // Init
        updateInstallUI();
        
        if (localStorage.getItem('delivery_is_connected') === 'true') {
            toggleConnection(true);
        }
        
        // Register SW
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset('delivery-sw.js') }}?v=6').catch(err => console.warn(err));
        }

    </script>
</body>
</html>
