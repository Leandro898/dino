<x-delivery-layout title="Bari Rider" themeColor="#ffffff" :useVite="false">
    @push('head')
    <link rel="manifest" href="{{ asset('delivery-manifest.json') }}?v=9">
    <!-- Pusher & Echo CDNs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endpush

    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/delivery-app.css') }}?v={{ time() }}">
    @endpush

    <!-- Componentes y Vistas Parciales Modularizadas -->
    @include('delivery.partials.modals')
    @include('delivery.partials.map')
    @include('delivery.partials.top-bar')
    @include('delivery.partials.bottom-sheet')
    @include('delivery.partials.drawer')
    @include('delivery.partials.profile-view')
    @include('delivery.partials.help-center-view')
    @include('delivery.partials.help-center-issues-view')
    @include('delivery.partials.support-view')
    @include('delivery.partials.settings-view')

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Configuración del Repartidor -->
    <script>
        window.BariDeliveryConfig = {
            userId: {{ auth()->id() ?? 'null' }},
            routes: {
                ordersLatest: @json(route('delivery.orders.latest')),
                locationUpdate: @json(route('delivery.location.update')),
                statusUpdate: @json(route('delivery.status.update')),
                supportMessages: "{{ route('delivery.support.messages') }}",
                supportSend: "{{ route('delivery.support.send') }}"
            },
            reverb: {
                key: '{{ config("broadcasting.connections.reverb.key") }}',
                wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                forceTLS: {{ config('broadcasting.connections.reverb.options.scheme') === 'https' ? 'true' : 'false' }}
            },
            assets: {
                ogImage: '{{ asset("images/og-image.png") }}',
                serviceWorker: '{{ asset("delivery-sw.js") }}?v=6'
            }
        };
    </script>
    
    <!-- Motor de la PWA del Repartidor -->
    <script src="{{ asset('js/delivery-app.js') }}?v={{ time() }}"></script>
</x-delivery-layout>
