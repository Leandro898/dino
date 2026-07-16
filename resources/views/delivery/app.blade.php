<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#ffffff">
    <title>Bari Rider</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('delivery-manifest.json') }}?v=2">
    <link rel="icon" href="{{ asset('images/og-image.png') }}">
    
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

        /* Bottom Sheet */
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
        }

        .sheet-handle {
            width: 40px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin: -10px auto 10px auto;
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

    <!-- Map Background -->
    <div id="map"></div>

    <!-- Top Floating Bar -->
    <div class="top-bar">
        <button class="icon-btn" onclick="document.getElementById('logout-form').submit();" title="Cerrar Sesión">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </button>

        <div class="status-badge" id="topStatusBadge">
            <span>Estado</span>
            <div class="status-indicator">
                <div class="dot" id="topStatusDot"></div>
                <div id="topStatusText">Desconectado</div>
            </div>
        </div>

        <button class="icon-btn" onclick="alert('Soporte en desarrollo');" title="Soporte">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </button>
    </div>

    <!-- Order Alert Popup -->
    <div class="order-alert" id="orderAlert">
        <h3 class="order-alert-title">¡Nuevo Pedido!</h3>
        <p class="order-alert-desc" id="orderAlertDesc">Pedido #123 de Juan Perez</p>
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
        // --- API & State ---
        const latestUrl = @json(route('delivery.orders.latest'));
        let isConnected = false;
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

        // Marker for rider location
        let riderMarker = L.marker([-41.1335, -71.3103]).addTo(map);

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

        // --- App Logic ---
        function getSavedOrderId() {
            const value = localStorage.getItem('delivery_last_order_id');
            return value ? parseInt(value, 10) : null;
        }

        function saveOrderId(orderId) {
            localStorage.setItem('delivery_last_order_id', String(orderId));
        }

        async function showOrderNotification(order) {
            orderAlertDesc.textContent = `Pedido #${order.id} - ${order.customer_name} - $${Number(order.total).toFixed(2)}`;
            orderAlert.classList.add('show');
            playAlertSound();
            
            if (navigator.vibrate) navigator.vibrate([200, 100, 200]);

            if (Notification.permission === 'granted') {
                new Notification('¡Nuevo Pedido!', {
                    body: `Pedido #${order.id} de ${order.customer_name}`,
                    icon: '{{ asset('images/og-image.png') }}'
                });
            }

            // Hide alert after 8 seconds
            setTimeout(() => {
                orderAlert.classList.remove('show');
            }, 8000);
        }

        async function fetchOrders() {
            if (!isConnected) return;
            
            try {
                const response = await fetch(latestUrl, { headers: { 'Accept': 'application/json' }});
                if (!response.ok) throw new Error('HTTP Error');
                const data = await response.json();

                if (!data.has_order) {
                    infoTitle.textContent = "Sin pedidos asignados";
                    infoDesc.innerHTML = "Esperando que se te asigne un pedido. Mantente en línea.";
                    localStorage.removeItem('delivery_last_order_id');
                    return;
                }

                saveOrderId(data.id);
                infoTitle.textContent = `Pedido asignado #${data.id}`;
                infoDesc.innerHTML = `
                    <strong>Cliente:</strong> ${data.customer_name}<br>
                    <strong>Dirección:</strong> ${data.address || 'Sin dirección'}<br>
                    <strong>Total:</strong> $${Number(data.total).toFixed(2)}<br>
                    <strong>Estado:</strong> <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">${data.status.toUpperCase()}</span>
                `;
            } catch (error) {
                console.error('Error fetching orders:', error);
                infoDesc.textContent = "Problemas de conexión al servidor.";
            }
        }

        async function toggleConnection(connect) {
            if (connect) {
                // Request permissions
                if ('Notification' in window) {
                    await Notification.requestPermission();
                }

                isConnected = true;
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
                        if (data.is_new_assignment) {
                            showOrderNotification({
                                id: data.order_id,
                                customer_name: data.customer_name,
                                total: data.total
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

        // Init
        updateInstallUI();
        
        // Register SW
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset('delivery-sw.js') }}?v=2').catch(err => console.warn(err));
        }

    </script>
</body>
</html>
