<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Seguimiento Pedido #{{ $order->id }} - Bari Tienda</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Pusher & Echo (Se cargan globalmente via Vite en app.js) --}}

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background: #FDFDFC;
        }

        .tracking-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 32px 16px 60px;
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .tracking-header h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .tracking-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Timeline */
        .timeline {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            position: relative;
            padding: 0 8px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 32px;
            right: 32px;
            height: 3px;
            background: #e5e7eb;
            z-index: 0;
        }

        .timeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .timeline-dot {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            margin-bottom: 8px;
            transition: all 0.4s ease;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .timeline-step.active .timeline-dot {
            background: linear-gradient(135deg, #ff3366, #ff6b6b);
            box-shadow: 0 4px 16px rgba(255, 51, 102, 0.4);
            transform: scale(1.15);
        }

        .timeline-step.completed .timeline-dot {
            background: #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        .timeline-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #9ca3af;
            text-align: center;
            max-width: 80px;
            line-height: 1.3;
        }

        .timeline-step.active .timeline-label,
        .timeline-step.completed .timeline-label {
            color: #1f2937;
        }

        /* Map */
        .map-wrapper {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            margin-bottom: 28px;
            border: 2px solid #f3f4f6;
            position: relative;
            z-index: 1;
        }

        #trackingMap {
            width: 100%;
            height: 400px;
        }

        /* Info cards */
        .info-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .info-card {
            background: #fff;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #f3f4f6;
        }

        .info-card .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .info-card h3 {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .info-card p {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .rider-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes riderPulse {
            0%, 100% { box-shadow: 0 4px 16px rgba(255,51,102,0.5); }
            50% { box-shadow: 0 4px 24px rgba(255,51,102,0.8); }
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            margin-bottom: 60px;
            color: #7e22ce;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 640px) {
            #trackingMap { height: 300px; }
            .info-cards { grid-template-columns: 1fr; }
            .tracking-header h1 { font-size: 1.3rem; }
        }
    </style>
</head>

<body class="antialiased">

    @include('partials.header')

    <div class="tracking-container">
        {{-- Header --}}
        <div class="tracking-header">
            <h1>📦 Seguimiento del Pedido #{{ $order->id }}</h1>
            <p>Realizaste tu compra en <strong>{{ $vendor->name ?? 'Comercio' }}</strong></p>
        </div>

        {{-- Timeline --}}
        @php
            $statusMap = [
                'pending'    => 0,
                'assigned'   => 1,
                'processing' => 2,
                'shipped'    => 3,
                'completed'  => 4,
            ];
            $currentStep = $statusMap[$order->status] ?? 0;
        @endphp
        <div class="timeline" id="orderTimeline">
            @foreach ([
                ['icon' => '📝', 'label' => 'Recibido'],
                ['icon' => '👤', 'label' => 'Asignado'],
                ['icon' => '🍳', 'label' => 'Preparando'],
                ['icon' => '🚴', 'label' => 'En Camino'],
                ['icon' => '✅', 'label' => 'Entregado'],
            ] as $index => $step)
                <div class="timeline-step {{ $index < $currentStep ? 'completed' : ($index === $currentStep ? 'active' : '') }}">
                    <div class="timeline-dot">{{ $step['icon'] }}</div>
                    <div class="timeline-label">{{ $step['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Map --}}
        <div class="map-wrapper">
            <div id="trackingMap"></div>
        </div>

        {{-- Info Cards --}}
        <div class="info-cards">
            <div class="info-card">
                <div class="icon-circle" style="background: #f0fdf4;">🏪</div>
                <h3>Comercio</h3>
                <p>{{ $vendor->name ?? 'Comercio' }}</p>
                <p style="font-size:0.75rem;color:#6b7280;font-weight:400;margin-top:2px;">{{ $vendor->address ?? '' }}</p>
            </div>
            <div class="info-card">
                <div class="icon-circle" style="background: #fef2f2;">🏠</div>
                <h3>Tu Dirección</h3>
                <p>{{ $order->address ?? 'Sin dirección' }}</p>
            </div>
            @if($rider)
            <div class="info-card">
                <div class="icon-circle" style="background: #fdf4ff;">🚴</div>
                <h3>Repartidor</h3>
                <p>{{ $rider->name ?? 'Por asignar' }}</p>
                <p class="rider-pulse" id="riderStatus" style="font-size:0.75rem;color:#10b981;font-weight:500;margin-top:2px;">
                    {{ in_array($order->status, ['shipped']) ? '📡 En movimiento' : 'Esperando...' }}
                </p>
            </div>
            @endif
            <div class="info-card">
                <div class="icon-circle" style="background: #eff6ff;">💰</div>
                <h3>Total</h3>
                <p>${{ number_format($order->total, 0, ',', '.') }}</p>
            </div>
        </div>

        <a href="{{ route('home') }}" class="back-link">← Volver a la tienda</a>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Datos del pedido ---
        const orderId = {{ $order->id }};
        const vendorLat = {{ $vendor->latitude ?? 'null' }};
        const vendorLng = {{ $vendor->longitude ?? 'null' }};
        const customerLat = {{ $order->latitude ?? 'null' }};
        const customerLng = {{ $order->longitude ?? 'null' }};
        const riderLat = {{ $rider->latitude ?? 'null' }};
        const riderLng = {{ $rider->longitude ?? 'null' }};

        // Centro del mapa: prioridad rider > vendor > customer > Bariloche
        const centerLat = riderLat || vendorLat || customerLat || -41.1335;
        const centerLng = riderLng || vendorLng || customerLng || -71.3103;

        // --- Inicializar Mapa ---
        const map = L.map('trackingMap', { zoomControl: true }).setView([centerLat, centerLng], 14);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // --- Iconos Custom ---
        const vendorIcon = L.divIcon({
            html: `<div style="position:relative;width:40px;height:50px;display:flex;flex-direction:column;align-items:center;">
                <div style="background:#111827;color:white;padding:6px;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:18px;border:3px solid white;box-shadow:0 3px 8px rgba(0,0,0,0.3);z-index:2;">🏪</div>
                <div style="width:3px;height:10px;background:#111827;margin-top:-3px;z-index:1;"></div>
            </div>`,
            className: '',
            iconSize: [40, 50],
            iconAnchor: [20, 50]
        });

        const customerIcon = L.divIcon({
            html: `<div style="position:relative;width:40px;height:50px;display:flex;flex-direction:column;align-items:center;">
                <div style="background:#ff3366;color:white;padding:6px;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:18px;border:3px solid white;box-shadow:0 3px 8px rgba(0,0,0,0.3);z-index:2;">🏠</div>
                <div style="width:3px;height:10px;background:#ff3366;margin-top:-3px;z-index:1;"></div>
            </div>`,
            className: '',
            iconSize: [40, 50],
            iconAnchor: [20, 50]
        });

        const riderIcon = L.divIcon({
            html: `<div style="position:relative;width:44px;height:54px;display:flex;flex-direction:column;align-items:center;">
                <div style="background:linear-gradient(135deg,#ff3366,#ff6b6b);color:white;padding:6px;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;font-size:20px;border:3px solid white;box-shadow:0 4px 16px rgba(255,51,102,0.5);z-index:2;animation:riderPulse 2s infinite;">🚴</div>
                <div style="width:3px;height:10px;background:#ff3366;margin-top:-3px;z-index:1;"></div>
            </div>`,
            className: '',
            iconSize: [44, 54],
            iconAnchor: [22, 54]
        });

        // --- Agregar marcadores ---
        const bounds = [];

        if (vendorLat && vendorLng) {
            L.marker([vendorLat, vendorLng], { icon: vendorIcon })
                .addTo(map)
                .bindTooltip('{{ $vendor->name ?? "Comercio" }}', { permanent: false, direction: 'top', offset: [0, -50] });
            bounds.push([vendorLat, vendorLng]);
        }

        if (customerLat && customerLng) {
            L.marker([customerLat, customerLng], { icon: customerIcon })
                .addTo(map)
                .bindTooltip('Tu domicilio', { permanent: false, direction: 'top', offset: [0, -50] });
            bounds.push([customerLat, customerLng]);
        }

        // Rider marker (se moverá en tiempo real)
        let riderMarker = null;
        if (riderLat && riderLng) {
            riderMarker = L.marker([riderLat, riderLng], { icon: riderIcon })
                .addTo(map)
                .bindTooltip('Repartidor', { permanent: false, direction: 'top', offset: [0, -54] });
            bounds.push([riderLat, riderLng]);
        }

        // Ajustar vista a los marcadores
        if (bounds.length >= 2) {
            map.fitBounds(bounds, { padding: [50, 50] });
        } else if (bounds.length === 1) {
            map.setView(bounds[0], 15);
        }

        // --- Animación suave del marcador del rider ---
        function animateMarker(marker, newLatLng, duration) {
            if (!marker) return;
            const start = marker.getLatLng();
            const startTime = performance.now();

            function step(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const ease = progress < 0.5
                    ? 4 * progress * progress * progress
                    : 1 - Math.pow(-2 * progress + 2, 3) / 2;

                const lat = start.lat + (newLatLng[0] - start.lat) * ease;
                const lng = start.lng + (newLatLng[1] - start.lng) * ease;
                marker.setLatLng([lat, lng]);

                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }
            requestAnimationFrame(step);
        }

        // --- WebSocket: escuchar la ubicación del rider en tiempo real (canal público) ---
        // Usamos window.Echo que ya fue configurado en resources/js/bootstrap.js via Vite
        
        // Canal PÚBLICO (no requiere autenticación — accesible para clientes invitados)
        window.Echo.channel(`order-tracking.${orderId}`)
            .listen('.rider-location-updated', (data) => {
                console.log('🚴 Rider location updated:', data);

                const newLat = data.latitude;
                const newLng = data.longitude;

                if (!riderMarker) {
                    riderMarker = L.marker([newLat, newLng], { icon: riderIcon })
                        .addTo(map)
                        .bindTooltip('Repartidor', { permanent: false, direction: 'top', offset: [0, -54] });
                } else {
                    animateMarker(riderMarker, [newLat, newLng], 1000);
                }

                const statusEl = document.getElementById('riderStatus');
                if (statusEl) {
                    statusEl.textContent = '📡 En movimiento';
                    statusEl.style.color = '#10b981';
                }
            });

        // También escuchar cambios de estado de la orden en el mismo canal
        window.Echo.channel(`order-tracking.${orderId}`)
            .listen('.order-status-updated', (data) => {
                console.log('📦 Order status updated:', data);
                updateTimeline(data.new_status);
                // En lugar de recargar la página completa, hacemos un fetch silencioso
                // y reemplazamos solo las tarjetas de información para mostrar al repartidor
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Actualizar la sección de tarjetas
                        const newCards = doc.querySelector('.info-cards');
                        if (newCards) {
                            document.querySelector('.info-cards').innerHTML = newCards.innerHTML;
                        }
                        
                        // Si el pedido pasó a completado o cancelado, el repartidor ya no enviará ubicación
                        if (['completed', 'cancelled'].includes(data.new_status)) {
                            const statusEl = document.getElementById('riderStatus');
                            if (statusEl) {
                                statusEl.textContent = 'Pedido finalizado';
                                statusEl.style.color = '#6b7280';
                                statusEl.classList.remove('rider-pulse');
                            }
                        }
                    });
            });

        function updateTimeline(newStatus) {
            const statusMap = {
                'pending': 0,
                'assigned': 1,
                'processing': 2,
                'shipped': 3,
                'completed': 4,
            };
            const step = statusMap[newStatus] ?? 0;
            const timelineSteps = document.querySelectorAll('#orderTimeline .timeline-step');

            timelineSteps.forEach((el, index) => {
                el.classList.remove('active', 'completed');
                if (index < step) {
                    el.classList.add('completed');
                } else if (index === step) {
                    el.classList.add('active');
                }
            });
        }

        // --- Fallback: polling cada 30s para actualizar estado si WebSocket falla ---
        setInterval(async () => {
            try {
                const resp = await fetch(window.location.href, { headers: { 'Accept': 'text/html' } });
                // No podemos parsear JSON fácilmente, pero la reconexión del WebSocket debería manejar las actualizaciones.
                // Este intervalo sirve principalmente para reconectar el mapa si la página estuvo en segundo plano.
                map.invalidateSize();
            } catch(e) { /* silent */ }
        }, 30000);
    });
    </script>
</body>

</html>
