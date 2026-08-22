<x-filament-panels::page>
    <div 
        x-data="riderMap(@js($riders))" 
        x-init="initMap()" 
        class="bg-white rounded-xl shadow-sm border border-gray-200" 
        style="height: 600px; width: 100%; position: relative; overflow: hidden; z-index: 1;"
        wire:ignore
    >
        <div id="rider-map-container" style="height: 100%; width: 100%;"></div>
    </div>

    @push('scripts')
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        .rider-marker-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rider-marker-inner {
            background-color: #8b5cf6;
            color: white;
            border: 2px solid white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }
        .rider-marker-tooltip {
            background: #111827;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 4px 8px;
            font-weight: 600;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .rider-marker-tooltip::before {
            border-top-color: #111827;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('riderMap', (initialRiders) => ({
                map: null,
                markers: {}, // { riderId: L.marker }
                riders: initialRiders,

                initMap() {
                    // Centro predeterminado en Bariloche
                    this.map = L.map('rider-map-container').setView([-41.1335, -71.3103], 13);
                    
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; OpenStreetMap &copy; CARTO',
                        subdomains: 'abcd',
                        maxZoom: 20
                    }).addTo(this.map);

                    // Add initial markers
                    this.riders.forEach(rider => {
                        this.addOrUpdateMarker(rider.id, rider.name, rider.lat, rider.lng);
                    });

                    const setupEcho = () => {
                        if (window.Echo) {
                            console.log('Echo is ready! Subscribing to map events...');
                            window.Echo.private('admin.map')
                                .listen('.global-rider-location-updated', (e) => {
                                    console.log('Rider location update received:', e);
                                    this.addOrUpdateMarker(e.riderId, e.riderName, e.latitude, e.longitude);
                                });

                            window.Echo.channel('orders')
                                .listen('.rider.status.updated', (e) => {
                                    console.log('Rider status update received:', e);
                                    if (!e.isOnline) {
                                        this.removeMarker(e.riderId);
                                    } else if (e.latitude && e.longitude && e.name) {
                                        this.addOrUpdateMarker(e.riderId, e.name, e.latitude, e.longitude);
                                    }
                                });
                        } else {
                            setTimeout(setupEcho, 500);
                        }
                    };
                    
                    setupEcho();
                },

                removeMarker(id) {
                    if (this.markers[id]) {
                        this.map.removeLayer(this.markers[id]);
                        delete this.markers[id];
                    }
                },

                addOrUpdateMarker(id, name, lat, lng) {
                    if (this.markers[id]) {
                        // Move existing marker smoothly
                        const newLatLng = new L.LatLng(lat, lng);
                        this.markers[id].setLatLng(newLatLng);
                    } else {
                        // Create new marker
                        const icon = L.divIcon({
                            html: `<div class="rider-marker-inner">${name.charAt(0).toUpperCase()}</div>`,
                            className: 'rider-marker-icon',
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
                        });

                        const marker = L.marker([lat, lng], { icon: icon }).addTo(this.map);
                        
                        marker.bindTooltip(name, {
                            permanent: true,
                            direction: 'top',
                            offset: [0, -16],
                            className: 'rider-marker-tooltip'
                        });

                        this.markers[id] = marker;
                    }
                }
            }));
        });
    </script>
    @endpush
</x-filament-panels::page>
