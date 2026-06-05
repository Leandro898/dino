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

    if (reverbKey) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: window.location.hostname,
            wsPort: window.vendorNotificationBroadcastPort ?? 443,
            wssPort: window.vendorNotificationBroadcastPort ?? 443,
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

        console.log('Echo initialized for vendor:', vendorId, 'on channel:', `vendor.${vendorId}`);

        window.Echo.private(`vendor.${vendorId}`)
            .listen('.new-order', (data) => {
                console.log('🔔 Event received on channel vendor.' + vendorId + ':', data);
                ringBell();
            });
    } else {
        console.warn('Reverb key not found, broadcasting disabled');
    }
}
