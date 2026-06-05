import Echo from 'laravel-echo';

const vendorId = window.vendorNotificationUserId;

if (vendorId) {
    const bell = document.createElement('audio');
    bell.src = '/sounds/bell.wav';
    bell.preload = 'auto';
    bell.volume = 0.85;
    bell.setAttribute('aria-hidden', 'true');
    bell.style.display = 'none';

    const appendBell = () => {
        if (!document.body.contains(bell)) {
            document.body.appendChild(bell);
        }
    };

    if (document.body) {
        appendBell();
    } else {
        document.addEventListener('DOMContentLoaded', appendBell, { once: true });
    }

    let audioUnlocked = false;
    let pendingRing = false;

    function unlockBellAudio() {
        if (audioUnlocked) {
            return;
        }

        bell.play().then(() => {
            bell.pause();
            bell.currentTime = 0;
            audioUnlocked = true;

            if (pendingRing) {
                pendingRing = false;
                ringBell();
            }
        }).catch(() => {});
    }

    function ringBell() {
        if (!audioUnlocked) {
            pendingRing = true;
            return;
        }

        bell.currentTime = 0;
        bell.play().catch(() => {});
    }

    ['pointerdown', 'click', 'keydown', 'touchstart'].forEach((eventName) => {
        document.addEventListener(eventName, unlockBellAudio, { capture: true, passive: true });
    });

    const reverbKey = window.vendorNotificationBroadcastKey;
    const reverbPort = window.vendorNotificationBroadcastPort;

    console.log('🎧 Vendor notification init:', {
        vendorId,
        reverbKey: reverbKey ? '✅ SET' : '❌ MISSING',
        reverbPort,
        hostname: window.location.hostname,
    });

    if (reverbKey) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        console.log('🔌 Creating Echo instance with config:', {
            broadcaster: 'reverb',
            key: reverbKey.substring(0, 8) + '...',
            wsHost: window.location.hostname,
            wssPort: reverbPort ?? 443,
            url: `wss://${window.location.hostname}:${reverbPort ?? 443}/app`,
        });

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: window.location.hostname,
            wsPort: window.location.protocol === 'https:' ? 443 : 80,
            wssPort: 443,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
            },
        });

        console.log('✅ Echo initialized for vendor:', vendorId, 'on channel:', `vendor.${vendorId}`);

        // Check connection state
        setTimeout(() => {
            console.log('📡 Echo connector ready:', window.Echo.connector);
            console.log('📡 Echo connector socket:', window.Echo.connector?.socket);
            if (window.Echo.connector?.socket) {
                console.log('🔌 Socket connected:', window.Echo.connector.socket.connected);
                console.log('🔌 Socket ID:', window.Echo.connector.socket.id);
            } else {
                console.error('❌ Socket NOT available - WebSocket connection failed');
            }
        }, 1000);

        // Listen for order-assigned events on vendor channel
        console.log('👂 Subscribing to channel:', `vendor.${vendorId}`);
        const channel = window.Echo.private(`vendor.${vendorId}`);
        
        console.log('📡 Channel object created:', channel);
        
        channel.listen('.order-assigned', (data) => {
                console.log('🔔 Order assigned event received on channel vendor.' + vendorId + ':', data);
                ringBell();
                
                // Trigger table refresh in Filament
                if (window.Livewire) {
                    console.log('📊 Dispatching refresh-orders-table event');
                    window.Livewire.dispatch('refresh-orders-table', { order_id: data.order_id });
                } else {
                    console.error('❌ Livewire not found!');
                }
            })
            .error((error) => {
                console.error('❌ Channel subscription error:', error);
            });
        
        console.log('✅ Event listener attached to vendor.' + vendorId);
    } else {
        console.error('❌ Reverb key not found, broadcasting disabled');
    }
}
